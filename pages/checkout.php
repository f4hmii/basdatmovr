<?php
session_start();
include '../db_connection.php';
include "../view/header.php";

if (!isset($_SESSION['id'])) {
    die("Silakan login terlebih dahulu.");
}
$pengguna_id = intval($_SESSION['id']);

// Hapus alamat
if (isset($_GET['delete_alamat'])) {
    $alamat_id = intval($_GET['delete_alamat']);
    $stmtDelete = $conn->prepare("DELETE FROM alamat_pengiriman WHERE id = ? AND pengguna_id = ?");
    $stmtDelete->bind_param("ii", $alamat_id, $pengguna_id);
    $stmtDelete->execute();
    header("Location: checkout.php");
    exit;
}

// Ambil data cart user
$stmt = $conn->prepare("
    SELECT c.*, p.nama_produk, p.foto_url, p.harga 
    FROM cart c 
    JOIN produk p ON c.produk_id = p.produk_id 
    WHERE c.pengguna_id = ?
");
$stmt->bind_param("i", $pengguna_id);
$stmt->execute();
$result = $stmt->get_result();

// Ambil daftar metode pembayaran
$stmt = $conn->prepare("SELECT * FROM metode_pembayaran");
$stmt->execute();
$resultMetode = $stmt->get_result();


// Ambil alamat pengguna dari database
$stmtAlamat = $conn->prepare("SELECT id, alamat FROM alamat_pengiriman WHERE pengguna_id = ?");
$stmtAlamat->bind_param("i", $pengguna_id);
$stmtAlamat->execute();
$resultAlamat = $stmtAlamat->get_result();

$alamat_tersimpan = [];
while ($row = $resultAlamat->fetch_assoc()) {
    $alamat_tersimpan[] = $row;
}

$items = [];
$totalHarga = 0;
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
    $totalHarga += $row['harga'] * $row['quantity'];
}

$checkoutSukses = false;
$transaksi_id = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && count($items) > 0) {
    $alamat_option = $_POST['alamat_option'] ?? 'existing';
    $alamat_pengiriman = '';
    $metode_pembayaran = $_POST['metode_pembayaran'] ?? 0;

    if ($alamat_option === 'new') {
        $alamat_baru = trim($_POST['alamat_baru'] ?? '');
        if (empty($alamat_baru)) {
            $error = "Alamat baru tidak boleh kosong.";
        } else {
            $stmtInsertAlamat = $conn->prepare("INSERT INTO alamat_pengiriman (pengguna_id, alamat) VALUES (?, ?)");
            $stmtInsertAlamat->bind_param("is", $pengguna_id, $alamat_baru);
            if ($stmtInsertAlamat->execute()) {
                $alamat_pengiriman = $alamat_baru;
            } else {
                $error = "Gagal menyimpan alamat baru.";
            }
        }
    } else {
        $alamat_id_terpilih = intval($_POST['alamat_terpilih'] ?? 0);
        $stmtAmbilAlamat = $conn->prepare("SELECT alamat FROM alamat_pengiriman WHERE id = ? AND pengguna_id = ?");
        $stmtAmbilAlamat->bind_param("ii", $alamat_id_terpilih, $pengguna_id);
        $stmtAmbilAlamat->execute();
        $resAlamatTerpilih = $stmtAmbilAlamat->get_result();
        $rowAlamatTerpilih = $resAlamatTerpilih->fetch_assoc();

        if ($rowAlamatTerpilih) {
            $alamat_pengiriman = $rowAlamatTerpilih['alamat'];
        } else {
            $error = "Alamat pengiriman tidak valid.";
        }
    }

    if (empty($alamat_pengiriman) || empty($metode_pembayaran)) {
        $error = "Silakan isi alamat pengiriman dan metode pembayaran.";
    }

    if (!isset($error)) {

        //tambah produk bukti pembayaran
        $upload_dir = "../uploads/";
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        // Upload bukti pembayaran
        $gambar     = $_FILES['bukti_pembayaran']['name'];
        $tmp_name   = $_FILES['bukti_pembayaran']['tmp_name'];
        $file_ext   = strtolower(pathinfo($gambar, PATHINFO_EXTENSION));
        $new_filename = '';

        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = "bukti_pembayaran_" . time() . '_' . uniqid() . '.' . $file_ext;
            $target_file = $upload_dir . $new_filename;
            if (!move_uploaded_file($tmp_name, $target_file)) {
                echo "<div class='alert alert-danger'>Gagal mengupload bukti pembayaran.</div>";
                exit;
            }
        } else {
            echo $gambar;
            echo $tmp_name;
            var_dump($allowed_ext);
            echo "<div class='alert alert-warning'>Format bukti pembayaran tidak diizinkan.</div>";
            exit;
        }
        // selesai

        $conn->begin_transaction();
        try {
            $stmtInsertTransaksi = $conn->prepare("INSERT INTO transaksi (pengguna_id, alamat_pengiriman, metode_pembayaran, total_harga, tanggal) VALUES (?, ?, ?, ?, NOW())");
            $stmtInsertTransaksi->bind_param("issd", $pengguna_id, $alamat_pengiriman, $metode_pembayaran, $totalHarga);
            if (!$stmtInsertTransaksi->execute()) throw new Exception("Gagal menyimpan transaksi.");
            $transaksi_id = $stmtInsertTransaksi->insert_id;

            $stmtInsertDetail = $conn->prepare("INSERT INTO transaksi_detail (transaksi_id, produk_id, quantity, harga) VALUES (?, ?, ?, ?)");
            foreach ($items as $item) {
                $stmtInsertDetail->bind_param("iiid", $transaksi_id, $item['produk_id'], $item['quantity'], $item['harga']);
                if (!$stmtInsertDetail->execute()) throw new Exception("Gagal menyimpan detail transaksi.");
            }

            // proses insert bukti pembayaran
            $status_pembayaran = "pending"; // 1 untuk status pending
            $stmtInsertPembayaran = $conn->prepare("INSERT INTO pembayaran (pesanan_id, metode_pembayaran, tanggal_pembayaran, jumlah_pembayaran, status_pembayaran, bukti_pembayaran) VALUES (?, ?,NOW(),?,?, ?)");
            $stmtInsertPembayaran->bind_param("iidss", $transaksi_id, $metode_pembayaran, $totalHarga, $status_pembayaran, $new_filename);
            if (!$stmtInsertPembayaran->execute()) throw new Exception("Gagal menyimpan pembayaran.");
            $pembayaran_id =  $stmtInsertPembayaran->insert_id;
            // selesai

            $stmtClearCart = $conn->prepare("DELETE FROM cart WHERE pengguna_id = ?");
            $stmtClearCart->bind_param("i", $pengguna_id);
            if (!$stmtClearCart->execute()) throw new Exception("Gagal mengosongkan keranjang.");

            $conn->commit();
            $checkoutSukses = true;
        } catch (Exception $e) {
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto mt-10 bg-white p-6 rounded shadow">
        <h2 class="text-2xl font-semibold mb-4">Checkout</h2>

        <?php if (isset($error)): ?>
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($checkoutSukses): ?>
            <div class="text-center">
                <h3 class="text-2xl font-semibold mb-2">Checkout Berhasil!</h3>
                <p>Nomor Transaksi: <strong>#<?= htmlspecialchars($transaksi_id) ?></strong></p>
                <a href="../index.php" class="mt-4 inline-block px-4 py-2 bg-black text-white rounded hover:bg-gray-700">Kembali ke Beranda</a>
            </div>
        <?php elseif (count($items) > 0): ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="block font-medium mb-2">Alamat Pengiriman</label>

                    <label class="flex items-center mb-2">
                        <input type="radio" name="alamat_option" value="existing" checked onclick="toggleAlamatBaru(false)">
                        <span class="ml-2">Pilih dari alamat tersimpan</span>
                    </label>

                    <div class="ml-6 border p-2 bg-gray-50 rounded">
                        <?php if (count($alamat_tersimpan) > 0): ?>
                            <?php foreach ($alamat_tersimpan as $alamat): ?>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="flex items-center gap-2">
                                        <input type="radio" name="alamat_terpilih" value="<?= $alamat['id'] ?>">
                                        <span><?= htmlspecialchars($alamat['alamat']) ?></span>
                                    </label>
                                    <a href="?delete_alamat=<?= $alamat['id'] ?>" onclick="return confirm('Hapus alamat ini?')" class="text-red-500 text-sm hover:underline">Hapus</a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-sm text-gray-600">Tidak ada alamat tersimpan.</p>
                        <?php endif; ?>
                    </div>

                    <label class="flex items-center mt-3 mb-1">
                        <input type="radio" name="alamat_option" value="new" onclick="toggleAlamatBaru(true)">
                        <span class="ml-2">Tambah alamat baru</span>
                    </label>
                    <textarea name="alamat_baru" id="alamat_baru" disabled class="w-full border rounded p-2" rows="3"></textarea>
                </div>

                <!-- metode pembayaran-->
                <div class="mb-4">
                    <label class="block font-medium mb-2">Metode Pembayaran</label>
                    <select name="metode_pembayaran" id="metode_pembayaran" required class="w-full border rounded p-2" onchange="tampilkanOpsi()">
                        <option value="">Pilih Metode</option>
                        <?php while ($rowMetode = $resultMetode->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($rowMetode['id']) ?>"><?= htmlspecialchars($rowMetode['nama_bank']) . ' - ' . htmlspecialchars($rowMetode['metode']) . ' | ' .  htmlspecialchars($rowMetode['no_akun']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
          

                <div id="bukti_pembayaran" class="mb-4">
                    <label class="block font-medium mb-2">Bukti Pembayaran</label>
                    <input type="file" name="bukti_pembayaran" class="form-control block w-full text-medium text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:placeholder-gray-400" required>
                </div>

                <?php foreach ($items as $item): ?>
                    <div class="flex justify-between items-center py-3 border-b">
                        <div class="flex items-center gap-4">
                            <img src="../uploads/<?= htmlspecialchars($item['foto_url']) ?>" class="w-16 h-16 rounded object-cover" alt="<?= htmlspecialchars($item['nama_produk']) ?>">
                            <div>
                                <p class="font-semibold"><?= htmlspecialchars($item['nama_produk']) ?></p>
                                <p class="text-sm text-gray-600">Ukuran: <?= htmlspecialchars($item['size']) ?> | Warna: <?= htmlspecialchars($item['color']) ?> | Qty: <?= $item['quantity'] ?></p>
                            </div>
                        </div>
                        <div class="font-semibold text-gray-800">Rp <?= number_format($item['harga'] * $item['quantity'], 0, ',', '.') ?></div>
                    </div>
                <?php endforeach; ?>

                <div class="flex justify-between mt-6 text-lg font-semibold">
                    <span>Total:</span>
                    <span>Rp <?= number_format($totalHarga, 0, ',', '.') ?></span>
                </div>

                <button type="submit" class="mt-6 w-full bg-red-500 text-white py-3 rounded hover:bg-black">Bayar Sekarang</button>
            </form>
        <?php else: ?>
            <p class="text-gray-600">Tidak ada produk di keranjang.</p>
        <?php endif; ?>
    </div>

    <script>
        function toggleAlamatBaru(enable) {
            const textarea = document.getElementById("alamat_baru");
            textarea.disabled = !enable;
            if (!enable) textarea.value = '';
        }

        function tampilkanOpsi() {
            const metode = document.getElementById("metode_pembayaran").value;
            document.getElementById("opsi_transfer").classList.toggle("hidden", metode !== "Transfer Bank");
            document.getElementById("opsi_ewallet").classList.toggle("hidden", metode !== "E-Wallet");
        }
    </script>
</body>

</html>