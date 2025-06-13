<?php
session_start();
include '../db_connection.php';
include '../view/header.php';

// Keamanan: Pastikan hanya seller yang bisa mengakses.
if (!isset($_SESSION['pengguna_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../pages/login.php");
    exit;
}

$seller_id = $_SESSION['pengguna_id'];
// Ambil data untuk dropdown form
$categories = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori");
$colors = $conn->query("SELECT * FROM warna ORDER BY nama_warna");
$sizes = $conn->query("SELECT * FROM ukuran ORDER BY ukuran_id");

$error_msg = '';
$success_msg = '';

// Proses form jika di-submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_produk = trim($_POST['nama_produk']);
    $deskripsi = trim($_POST['deskripsi']);
    $harga = $_POST['harga'];
    $kategori_id = $_POST['kategori_id'];
    $kondisi = $_POST['kondisi'];
    $variants = $_POST['variants'] ?? [];
    
    // Hitung total stok dari semua varian yang valid
    $total_stock = 0;
    $valid_variants = [];
    foreach ($variants as $variant) {
        if (!empty($variant['size']) && !empty($variant['color']) && !empty($variant['stock'])) {
            $total_stock += intval($variant['stock']);
            $valid_variants[] = $variant;
        }
    }

    $foto_url = '';
    // Validasi & Upload Foto Utama
    if (isset($_FILES['foto_utama']) && $_FILES['foto_utama']['error'] == 0) {
        $target_dir = "../uploads/";
        $file_extension = strtolower(pathinfo($_FILES['foto_utama']['name'], PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($file_extension, $allowed_types)) {
            $foto_url = "produk_" . time() . '_' . bin2hex(random_bytes(8)) . '.' . $file_extension;
            if (!move_uploaded_file($_FILES['foto_utama']['tmp_name'], $target_dir . $foto_url)) {
                $error_msg = "Gagal mengupload foto utama.";
                $foto_url = ''; // Kosongkan jika gagal upload
            }
        } else {
            $error_msg = "Format foto utama tidak valid. Gunakan jpg, jpeg, png, atau gif.";
        }
    } else {
        $error_msg = "Foto utama wajib diisi.";
    }

   
    if (empty($error_msg)) {
        $conn->begin_transaction();
        try {
            // Insert data produk utama
            $stmt_produk = $conn->prepare("INSERT INTO produk (nama_produk, deskripsi, harga, stock, seller_id, kategori_id, kondisi, foto_url, verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)");
            $stmt_produk->bind_param("ssdiisss", $nama_produk, $deskripsi, $harga, $total_stock, $seller_id, $kategori_id, $kondisi, $foto_url);
            if (!$stmt_produk->execute()) throw new Exception("Gagal menyimpan data produk utama.");
            $produk_id = $stmt_produk->insert_id;
            $stmt_produk->close();
            
            // Insert data varian produk
            if (!empty($valid_variants)) {
                $stmt_varian = $conn->prepare("INSERT INTO produk_varian (produk_id, ukuran_id, warna_id, stock) VALUES (?, ?, ?, ?)");
                foreach ($valid_variants as $variant) {
                    $stmt_varian->bind_param("iiii", $produk_id, $variant['size'], $variant['color'], $variant['stock']);
                    if (!$stmt_varian->execute()) throw new Exception("Gagal menyimpan data varian.");
                }
                $stmt_varian->close();
            }

            // Proses upload foto detail
            if (isset($_FILES['foto_detail']) && count($_FILES['foto_detail']['name']) > 0) {
                $stmt_foto_detail = $conn->prepare("INSERT INTO produk_foto_detail (produk_id, foto_path) VALUES (?, ?)");
                foreach ($_FILES['foto_detail']['name'] as $key => $name) {
                    if ($_FILES['foto_detail']['error'][$key] == 0) {
                        $detail_file_ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                        if (in_array($detail_file_ext, $allowed_types)) {
                            $detail_foto_path = "detail_" . $produk_id . "_" . time() . '_' . $key . '.' . $detail_file_ext;
                            if(move_uploaded_file($_FILES['foto_detail']['tmp_name'][$key], $target_dir . $detail_foto_path)) {
                                $stmt_foto_detail->bind_param("is", $produk_id, $detail_foto_path);
                                if (!$stmt_foto_detail->execute()) throw new Exception("Gagal menyimpan foto detail.");
                            }
                        }
                    }
                }
                $stmt_foto_detail->close();
            }

            $conn->commit();
            $success_msg = "Produk berhasil ditambahkan! Admin akan segera memverifikasi produk Anda.";
        } catch (Exception $e) {
            $conn->rollback();
            $error_msg = "Terjadi kegagalan: " . $e->getMessage();
            if (!empty($foto_url) && file_exists($target_dir . $foto_url)) {
                unlink($target_dir . $foto_url);
            }
        }
    }
}
?>

<div class="container mx-auto mt-10 p-5">
    <div class="bg-white p-8 rounded-lg shadow-lg max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Tambah Produk Baru</h1>
            <a href="profile.php" class="text-blue-600 hover:underline">&larr; Kembali ke Profil</a>
        </div>
        
        <?php if ($error_msg): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert"><p><?= $error_msg ?></p></div>
        <?php endif; ?>
        <?php if ($success_msg): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p><?= $success_msg ?></p>
            <a href="profile.php" class="font-bold hover:underline mt-2 inline-block">Lihat di Dashboard Profil Anda &rarr;</a>
        </div>
        <?php endif; ?>

        <form action="tambah.php" method="POST" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Nama Produk</label>
                        <input type="text" name="nama_produk" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Deskripsi</label>
                        <textarea name="deskripsi" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 h-32 focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Kategori</label>
                            <select name="kategori_id" class="shadow border rounded w-full py-2 px-3 text-gray-700" required>
                                <?php $categories->data_seek(0); while ($cat = $categories->fetch_assoc()): ?>
                                <option value="<?= $cat['kategori_id'] ?>"><?= htmlspecialchars($cat['nama_kategori']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Kondisi</label>
                            <select name="kondisi" class="shadow border rounded w-full py-2 px-3 text-gray-700" required>
                                <option value="Baru">Baru</option>
                                <option value="Bekas">Bekas</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Harga (Rp)</label>
                        <input type="number" name="harga" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required min="0">
                    </div>
                </div>
                <div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Foto Utama (Wajib)</label>
                        <input type="file" name="foto_utama" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required accept="image/*">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Foto Detail (Opsional)</label>
                        <input type="file" name="foto_detail[]" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" multiple accept="image/*">
                    </div>
                    <div id="variants-container" class="mb-4 border-t pt-4">
                        <h3 class="text-lg font-bold mb-2">Varian & Stok</h3>
                        <div class="variant-item grid grid-cols-11 gap-2 items-center mb-2">
                            <div class="col-span-4"><select name="variants[0][size]" class="w-full border rounded p-2 text-sm"><option value="">Ukuran</option><?php $sizes->data_seek(0); while($s = $sizes->fetch_assoc()): ?><option value="<?= $s['ukuran_id'] ?>"><?= $s['nama_ukuran'] ?></option><?php endwhile; ?></select></div>
                            <div class="col-span-4"><select name="variants[0][color]" class="w-full border rounded p-2 text-sm"><option value="">Warna</option><?php $colors->data_seek(0); while($c = $colors->fetch_assoc()): ?><option value="<?= $c['warna_id'] ?>"><?= $c['nama_warna'] ?></option><?php endwhile; ?></select></div>
                            <div class="col-span-3"><input type="number" name="variants[0][stock]" class="w-full border rounded p-2 text-sm" placeholder="Stok" min="0"></div>
                        </div>
                    </div>
                    <button type="button" id="add-variant" class="text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded w-full">Tambah Varian Lain</button>
                </div>
            </div>
            <div class="flex items-center justify-end mt-6 border-t pt-6">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-800 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:shadow-outline transition-colors">
                    Simpan Produk
                </button>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('add-variant').addEventListener('click', function() {
    const container = document.getElementById('variants-container');
    const index = container.getElementsByClassName('variant-item').length;
    const newItem = document.createElement('div');
    newItem.className = 'variant-item grid grid-cols-11 gap-2 items-center mb-2';
    const sizeOptions = container.querySelector('select[name^="variants[0][size]"]').innerHTML;
    const colorOptions = container.querySelector('select[name^="variants[0][color]"]').innerHTML;
    newItem.innerHTML = `
        <div class="col-span-4"><select name="variants[${index}][size]" class="w-full border rounded p-2 text-sm">${sizeOptions}</select></div>
        <div class="col-span-4"><select name="variants[${index}][color]" class="w-full border rounded p-2 text-sm">${colorOptions}</select></div>
        <div class="col-span-2"><input type="number" name="variants[${index}][stock]" class="w-full border rounded p-2 text-sm" placeholder="Stok" min="0"></div>
        <div class="col-span-1 text-right"><button type="button" class="remove-variant text-red-500 hover:text-red-700 text-xl font-bold">&times;</button></div>
    `;
    container.appendChild(newItem);
});
document.getElementById('variants-container').addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('remove-variant')) {
        e.target.closest('.variant-item').remove();
    }
});
</script>
<?php include '../view/footer.php'; ?>