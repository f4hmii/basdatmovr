<?php
// File: admincontrol/verifikasi_aksi.php
session_start();
include '../db_connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../pages/login.php"); exit;
}

$aksi = $_GET['aksi'] ?? '';
$produk_id = intval($_GET['produk_id'] ?? 0);

if ($produk_id <= 0 || !in_array($aksi, ['setuju', 'tolak'])) {
    $_SESSION['error_message'] = "Aksi atau ID produk tidak valid.";
    header("Location: dashbord_admin.php#verifikasi_produk"); exit;
}

// Set status verified berdasarkan aksi
$new_status = ($aksi === 'setuju') ? 1 : -1;

$stmt = $conn->prepare("UPDATE produk SET verified = ? WHERE produk_id = ?");
$stmt->bind_param("ii", $new_status, $produk_id);

if ($stmt->execute()) {
    $pesan_sukses = ($aksi === 'setuju') ? 'disetujui' : 'ditolak';
    $_SESSION['success_message'] = "Produk #" . $produk_id . " berhasil " . $pesan_sukses . ".";
} else {
    $_SESSION['error_message'] = "Gagal memproses produk: " . $conn->error;
}
$stmt->close();
header("Location: dashbord_admin.php#verifikasi_produk");
exit;
?>