<?php
// File: admincontrol/hapusUser.php
session_start();
include '../db_connection.php';

// Keamanan: Hanya admin yang bisa menghapus.
if (!isset($_SESSION['pengguna_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../pages/login.php');
    exit;
}
if (!isset($_GET['pengguna_id']) || !is_numeric($_GET['pengguna_id'])) {
    die("Error: ID pengguna tidak valid.");
}

$id_user_dihapus = intval($_GET['pengguna_id']);
$id_admin_login = $_SESSION['pengguna_id'];

// Keamanan: Mencegah admin menghapus akunnya sendiri.
if ($id_user_dihapus === $id_admin_login) {
    die("Error: Anda tidak dapat menghapus akun Anda sendiri.");
}

$conn->begin_transaction();
try {
    // Hapus dari tabel-tabel anak terlebih dahulu
    $tables_to_delete_from = ['admin', 'seller', 'cart', 'favorit', 'alamat_pengiriman', 'ulasan', 'chat', 'pembayaran', 'transaksi'];
    foreach ($tables_to_delete_from as $table) {
        $fk_column = 'pengguna_id';
        if ($table === 'ulasan') $fk_column = 'buyer_id';
        if ($table === 'pembayaran') $fk_column = 'pesanan_id'; // Ini asumsi, mungkin perlu disesuaikan
        if ($table === 'transaksi') $fk_column = 'pengguna_id'; 
        
        $stmt_child = $conn->prepare("DELETE FROM $table WHERE $fk_column = ?");
        $stmt_child->bind_param("i", $id_user_dihapus);
        $stmt_child->execute();
        $stmt_child->close();
    }

    // Hapus produk yang dijual oleh user ini
    $stmt_produk = $conn->prepare("DELETE FROM produk WHERE seller_id = ?");
    $stmt_produk->bind_param("i", $id_user_dihapus);
    $stmt_produk->execute();
    $stmt_produk->close();

    // Terakhir, hapus pengguna dari tabel utama
    $stmt_user = $conn->prepare("DELETE FROM pengguna WHERE pengguna_id = ?");
    $stmt_user->bind_param("i", $id_user_dihapus);
    $stmt_user->execute();
    $stmt_user->close();
    
    $conn->commit();
    
    // Set pesan sukses dan redirect
    $_SESSION['success_message'] = "Pengguna dan semua data terkait berhasil dihapus permanen.";

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error_message'] = "Gagal menghapus pengguna: " . $e->getMessage();
}
header("Location: dashbord_admin.php#kelola_user");
exit();
?>