<?php
include '../db_connection.php';
// Handle tambah kategori


if (isset($_POST['tambah_kategori'])) {
    $nama_kategori = trim($_POST['nama_kategori']);

    if (!empty($nama_kategori)) {
        // Cek apakah kategori sudah ada
        $cek = $conn->prepare("SELECT 1 FROM kategori WHERE nama_kategori = ?");
        $cek->bind_param("s", $nama_kategori);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows > 0) {
            $pesan = "<div class='alert alert-warning mb-2'>Kategori <b>$nama_kategori</b> sudah ada!</div>";
        } else {
            $stmt = $conn->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
            $stmt->bind_param("s", $nama_kategori);
        if ($stmt->execute()) {
            $pesan = "<div class='alert alert-success mb-2'>Kategori <b>$nama_kategori</b> berhasil ditambah!</div>";
            // Tidak ada header redirect!
            } else {
                $pesan = "<div class='alert alert-danger mb-2'>Gagal menambah kategori: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
        $cek->close();
    }
    session_start();
    $_SESSION['pesan'] = "Kategori <b>$nama_kategori</b> berhasil ditambah!";
    header("Location: dashbord_admin.php#kelola_kategori");
    exit;
}

?>