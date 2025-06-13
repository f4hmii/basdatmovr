<?php
session_start();
include '../db_connection.php';

// FIX 1: Pengecekan session diubah ke 'pengguna_id' dan role 'seller'
if (!isset($_SESSION['pengguna_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'seller') {
    header("Location: ../pages/login.php");
    exit;
}
$pengguna_id = $_SESSION['pengguna_id'];

$error_msg = '';
$success_msg = '';

if (isset($_POST['simpan'])) {
    // Mengambil dan membersihkan data dari form
    $nama = htmlspecialchars($_POST['nama']);
    $deskripsi = htmlspecialchars($_POST['deskripsi']);
    $harga = floatval($_POST['harga']);
    $kategori_id = intval($_POST['kategori_id']);
    $kondisi = htmlspecialchars($_POST['kondisi']);
    $stok_keseluruhan = intval($_POST['stok_keseluruhan']);
    
    // DIHAPUS: Logika untuk memproses stok warna dan ukuran karena kolom tidak ada di DB

    $upload_dir = "../uploads/";
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
    $new_filename = '';

    // Validasi dan upload gambar utama
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $gambar = $_FILES['gambar']['name'];
        $tmp_name = $_FILES['gambar']['tmp_name'];
        $file_ext = strtolower(pathinfo($gambar, PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = time() . '_' . uniqid() . '.' . $file_ext;
            $target_file = $upload_dir . $new_filename;
            if (!move_uploaded_file($tmp_name, $target_file)) {
                $error_msg = "Gagal mengupload gambar utama.";
            }
        } else {
            $error_msg = "Format gambar utama tidak diizinkan. Gunakan jpg, jpeg, png, atau gif.";
        }
    } else {
        $error_msg = "Gambar utama wajib diunggah.";
    }

    // Lanjutkan hanya jika tidak ada error upload
    if (empty($error_msg)) {
        // FIX 2: Query INSERT ditulis ulang sepenuhnya menggunakan prepared statement
        $sql = "INSERT INTO produk (nama_produk, deskripsi, stock, harga, foto_url, seller_id, kategori_id, kondisi, verified) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)";
        
        $stmt = $conn->prepare($sql);
        // Tipe data: s=string, i=integer, d=double
        $stmt->bind_param('ssidsiis', $nama, $deskripsi, $stok_keseluruhan, $harga, $new_filename, $pengguna_id, $kategori_id, $kondisi);

        if ($stmt->execute()) {
            $produk_id = $stmt->insert_id;

            // Upload foto detail produk jika ada
            if (!empty($_FILES['foto_detail']['name'][0])) {
                foreach ($_FILES['foto_detail']['name'] as $key => $filename) {
                    if ($_FILES['foto_detail']['error'][$key] === UPLOAD_ERR_OK) {
                        $tmp_name_detail = $_FILES['foto_detail']['tmp_name'][$key];
                        $file_ext_detail = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                        if (in_array($file_ext_detail, $allowed_ext)) {
                            $new_file_detail = time() . '_' . uniqid() . '_detail.' . $file_ext_detail;
                            $target_file_detail = $upload_dir . $new_file_detail;

                            if (move_uploaded_file($tmp_name_detail, $target_file_detail)) {
                                $stmt2 = $conn->prepare("INSERT INTO produk_foto_detail (produk_id, foto_path) VALUES (?, ?)");
                                $stmt2->bind_param("is", $produk_id, $new_file_detail);
                                $stmt2->execute();
                                $stmt2->close();
                            }
                        }
                    }
                }
            }

            $success_msg = "Produk berhasil ditambahkan dan menunggu verifikasi admin.";
            // Menggunakan meta refresh untuk redirect setelah menampilkan pesan
            echo '<meta http-equiv="refresh" content="2;url=produk.php">';
        } else {
            $error_msg = "Gagal menyimpan produk: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tambah Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h2 class="mb-4">Tambah Produk Baru</h2>

            <?php if ($success_msg): ?>
                <div class="alert alert-success"><?= $success_msg ?></div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="alert alert-danger"><?= $error_msg ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Nama Produk</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="4" required></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Harga</label>
                        <input type="number" name="harga" class="form-control" placeholder="Contoh: 50000" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Stok Keseluruhan</label>
                        <input type="number" name="stok_keseluruhan" class="form-control" min="0" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Gambar Utama (Wajib)</label>
                        <input type="file" name="gambar" class="form-control" accept="image/*" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Gambar Detail (Opsional)</label>
                        <input type="file" name="foto_detail[]" class="form-control" accept="image/*" multiple>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="kategori_id" class="form-control" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php
                            $kategori_result = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori");
                            while ($row = $kategori_result->fetch_assoc()) {
                                echo "<option value='{$row['kategori_id']}'>{$row['nama_kategori']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kondisi Produk</label>
                        <select name="kondisi" class="form-control" required>
                            <option value="">-- Pilih Kondisi --</option>
                            <option value="Baru">Baru</option>
                            <option value="Bekas">Bekas</option>
                        </select>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary" name="simpan">Simpan Produk</button>
                    <a href="produk.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>