<?php
session_start();
require_once '../db_connection.php';

if (!isset($_SESSION['pengguna_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

$pengguna_id = $_SESSION['pengguna_id'];
$produk_id = $_POST['produk_id'] ?? null;

if (!$produk_id || !is_numeric($produk_id)) {
    die("ID produk tidak valid.");
}

// Cek apakah produk sudah ada di favorit
$check = $conn->prepare("SELECT 1 FROM favorit WHERE pengguna_id = ? AND produk_id = ?");
$check->bind_param("ii", $pengguna_id, $produk_id);
$check->execute();
$checkResult = $check->get_result();

if ($checkResult->num_rows === 0) {
    // Tambahkan ke favorit
    $insert = $conn->prepare("INSERT INTO favorit (pengguna_id, produk_id, quantity) VALUES (?, ?, 1)");
    $insert->bind_param("ii", $pengguna_id, $produk_id);
    $insert->execute();
}

// Redirect kembali ke halaman utama (atau ke favorite.php jika diinginkan)
header("Location: ../favorite.php");
exit();
