<?php
session_start();
require_once '../db_connection.php';

if (!isset($_SESSION['pengguna_id'])) {
    header("Location: ../login.php");
    exit();
}

$pengguna_id = $_SESSION['pengguna_id'];
$produk_id = $_POST['produk_id'] ?? null;

if ($produk_id && is_numeric($produk_id)) {
    $delete = $conn->prepare("DELETE FROM favorit WHERE pengguna_id = ? AND produk_id = ?");
    $delete->bind_param("ii", $pengguna_id, $produk_id);
    $delete->execute();
}

// Kembali ke halaman favorit
header("Location: ../wishlist/favorite.php");
exit();
