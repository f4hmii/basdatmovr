<?php
session_start();
include "../db_connection.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$nama_pengguna = trim($_POST['nama_pengguna'] ?? '');

if ($nama_pengguna === '') {
    echo "Nama pengguna tidak boleh kosong.";
    exit;
}

$query = "UPDATE pengguna SET nama_pengguna = ? WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $nama_pengguna, $username);

if ($stmt->execute()) {
    header("Location: profil.php?status=success");
    exit;
} else {
    echo "Gagal memperbarui profil: " . $conn->error;
}
