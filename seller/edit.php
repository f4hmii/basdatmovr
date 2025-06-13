<?php
session_start();
include '../db_connection.php';

// FIX 1: Pengecekan Keamanan (Wajib!)
// Pastikan pengguna login, adalah seller, dan produk_id valid.
if (!isset($_SESSION['pengguna_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../pages/login.php');
    exit;
}
if (!isset($_GET['produk_id']) || !is_numeric($_GET['produk_id'])) {
    die("Error: ID produk tidak valid.");
}

$seller_id = $_SESSION['pengguna_id'];
$produk_id = intval($_GET['produk_id']);
$error_msg = '';
$success_msg = '';

// FIX 2: Pengecekan Kepemilikan Produk
// Ambil data produk HANYA jika produk tersebut milik seller yang sedang login.
$stmt = $conn->prepare("SELECT * FROM produk WHERE produk_id = ? AND seller_id = ?");
$stmt->bind_param("ii", $produk_id, $seller_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Jika produk tidak ditemukan atau bukan milik seller ini, hentikan.
    die("Produk tidak ditemukan atau Anda tidak memiliki izin untuk mengedit produk ini.");
}
$data = $result->fetch_assoc();
$stmt->close();


// Logika untuk memproses form update
if (isset($_POST['update'])) {
    // Mengambil dan membersihkan data dari form
    $nama = htmlspecialchars($_POST['nama']);
    $deskripsi = htmlspecialchars($_POST['deskripsi']);
    $stok = intval($_POST['stok']);
    $harga = floatval($_POST['harga']);
    $kategori_id = intval($_POST['kategori_id']);
    $kondisi = htmlspecialchars($_POST['kondisi']);
    
    $gambar_final = $data['foto_url']; // Defaultnya adalah gambar lama

    // Cek jika ada file gambar baru yang di-upload
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $upload_folder = "../uploads/";
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        
        $gambar = $_FILES['gambar']['name'];
        $tmp = $_FILES['gambar']['tmp_name'];
        $ext = strtolower(pathinfo($gambar, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed_ext)) {
            $new_gambar = time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($tmp, $upload_folder . $new_gambar)) {
                // Hapus gambar lama jika upload yang baru berhasil
                if (!empty($data['foto_url']) && file_exists($upload_folder . $data['foto_url'])) {
                    unlink($upload_folder . $data['foto_url']);
                }
                $gambar_final = $new_gambar; // Gunakan nama file gambar yang baru
            } else {
                $error_msg = "Gagal mengupload gambar baru.";
            }
        } else {
            $error_msg = "Format gambar tidak diizinkan.";
        }
    }

    // Lanjutkan update hanya jika tidak ada error
    if (empty($error_msg)) {
        // FIX 3: Query UPDATE ditulis ulang menggunakan prepared statement
        $sql_update = "UPDATE produk SET 
                        nama_produk = ?, 
                        deskripsi = ?, 
                        stock = ?, 
                        harga = ?, 
                        foto_url = ?,
                        kategori_id = ?,
                        kondisi = ?
                      WHERE produk_id = ? AND seller_id = ?";
        
        $stmt_update = $conn->prepare($sql_update);
        // Tipe data: s=string, i=integer, d=double
        $stmt_update->bind_param("ssidsisii", $nama, $deskripsi, $stok, $harga, $gambar_final, $kategori_id, $kondisi, $produk_id, $seller_id);

        if ($stmt_update->execute()) {
            $success_msg = "Produk berhasil diperbarui.";
            // Refresh data untuk ditampilkan di form
            $stmt_refresh = $conn->prepare("SELECT * FROM produk WHERE produk_id = ? AND seller_id = ?");
            $stmt_refresh->bind_param("ii", $produk_id, $seller_id);
            $stmt_refresh->execute();
            $data = $stmt_refresh->get_result()->fetch_assoc();
            $stmt_refresh->close();
            
            echo '<meta http-equiv="refresh" content="2;url=produk.php">';
        } else {
            $error_msg = "Gagal update produk: " . $conn->error;
        }
        $stmt_update->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h2 class="mb-4">Edit Produk: <?= htmlspecialchars($data['nama_produk']) ?></h2>
            
            <?php if ($success_msg): ?>
                <div class="alert alert-success"><?= $success_msg ?></div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="alert alert-danger"><?= $error_msg ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Nama Produk</label>
                    <input type="text" name="nama" value="<?= htmlspecialchars($data['nama_produk']) ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="4" required><?= htmlspecialchars($data['deskripsi']) ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Harga</label>
                        <input type="number" name="harga" value="<?= floatval($data['harga']) ?>" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Stok</label>
                        <input type="number" name="stok" value="<?= intval($data['stock']) ?>" class="form-control" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="kategori_id" class="form-control" required>
                            <?php
                            $kategori_result = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori");
                            while ($kat_row = $kategori_result->fetch_assoc()): ?>
                                <option value="<?= $kat_row['kategori_id'] ?>" <?= ($data['kategori_id'] == $kat_row['kategori_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kat_row['nama_kategori']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kondisi Produk</label>
                        <select name="kondisi" class="form-control" required>
                            <option value="Baru" <?= ($data['kondisi'] == 'Baru') ? 'selected' : '' ?>>Baru</option>
                            <option value="Bekas" <?= ($data['kondisi'] == 'Bekas') ? 'selected' : '' ?>>Bekas</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ganti Gambar Utama (Opsional)</label><br>
                    <img src="../uploads/<?= htmlspecialchars($data['foto_url']) ?>" width="100" class="img-thumbnail mb-2"><br>
                    <input type="file" name="gambar" class="form-control">
                    <small class="form-text text-muted">Kosongkan jika tidak ingin mengganti gambar.</small>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary" name="update">Update Produk</button>
                    <a href="produk.php" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>