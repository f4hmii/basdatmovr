<?php
session_start();
include '../db_connection.php';

// FIX 1: Menambahkan Pengecekan Keamanan (Wajib!)
if (!isset($_SESSION['pengguna_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit;
}

$error_msg = '';
$success_msg = '';

// Memproses form jika disubmit
if (isset($_POST['submit'])) {
    $nama_kategori = htmlspecialchars($_POST['nama_kategori']);

    if (empty($nama_kategori)) {
        $error_msg = "Nama kategori tidak boleh kosong.";
    } else {
        // FIX 2: Query diamankan dengan prepared statement
        $stmt_check = $conn->prepare("SELECT kategori_id FROM kategori WHERE nama_kategori = ?");
        $stmt_check->bind_param("s", $nama_kategori);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $error_msg = "Kategori dengan nama tersebut sudah ada.";
        } else {
            $stmt_insert = $conn->prepare("INSERT INTO kategori(nama_kategori) VALUES (?)");
            $stmt_insert->bind_param("s", $nama_kategori);

            if ($stmt_insert->execute()) {
                $success_msg = "Kategori baru berhasil ditambahkan.";
            } else {
                $error_msg = "Gagal menambahkan kategori: " . $conn->error;
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tambah Kategori</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <h2 class="mb-4">Tambah Kategori Baru</h2>

            <?php if ($success_msg): ?>
                <div class="alert alert-success"><?= $success_msg ?></div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="alert alert-danger"><?= $error_msg ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-3">
                    <label for="nama_kategori" class="form-label">Nama Kategori</label>
                    <input type="text" id="nama_kategori" name="nama_kategori" class="form-control" required>
                </div>
                <button type="submit" name="submit" class="btn btn-primary">Tambah</button>
                <a href="dashbord_admin.php#kelola_kategori" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</body>
</html>