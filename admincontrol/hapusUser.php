<?php
session_start();
include '../db_connection.php';

// Cek apakah admin yang login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: kelola_user.php");
    exit;
}

$id_user_dihapus = intval($_GET['id']);
$id_admin_login = $_SESSION['pengguna_id'];

// Admin tidak bisa menghapus dirinya sendiri
if ($id_user_dihapus === $id_admin_login) {
    header("Location: kelola_user.php?error=cannot_delete_self");
    exit;
}

// Mulai transaksi database
$conn->begin_transaction();

try {
    // 1. Ambil semua transaksi_id milik pengguna yang akan dihapus
    $stmt_transaksi_ids = $conn->prepare("SELECT transaksi_id FROM transaksi WHERE pengguna_id = ?");
    $stmt_transaksi_ids->bind_param("i", $id_user_dihapus);
    $stmt_transaksi_ids->execute();
    $result_transaksi = $stmt_transaksi_ids->get_result();
    $transaksi_ids = [];
    while ($row = $result_transaksi->fetch_assoc()) {
        $transaksi_ids[] = $row['transaksi_id'];
    }
    $stmt_transaksi_ids->close();

    // 2. Hapus dari tabel anak yang memiliki relasi ke transaksi
    // Hapus dari `pembayaran` terlebih dahulu jika ada transaksi
    if (!empty($transaksi_ids)) {
        $in_clause_pembayaran = implode(',', array_fill(0, count($transaksi_ids), '?'));
        $types_pembayaran = str_repeat('i', count($transaksi_ids));
        $stmt_pembayaran = $conn->prepare("DELETE FROM pembayaran WHERE transaksi_id IN ($in_clause_pembayaran)");
        $stmt_pembayaran->bind_param($types_pembayaran, ...$transaksi_ids);
        $stmt_pembayaran->execute();
        $stmt_pembayaran->close();
    }
    
    // Hapus juga dari `transaksi_detail`
    if (!empty($transaksi_ids)) {
        $in_clause_detail = implode(',', array_fill(0, count($transaksi_ids), '?'));
        $types_detail = str_repeat('i', count($transaksi_ids));
        $stmt_transaksi_detail = $conn->prepare("DELETE FROM transaksi_detail WHERE transaksi_id IN ($in_clause_detail)");
        $stmt_transaksi_detail->bind_param($types_detail, ...$transaksi_ids);
        $stmt_transaksi_detail->execute();
        $stmt_transaksi_detail->close();
    }

    // 3. Hapus dari tabel-tabel anak lain yang berelasi langsung dengan pengguna_id
    // Tabel 'pembayaran' dihapus dari daftar ini karena sudah ditangani di atas
    $tables_to_delete_from = [
        'admin', 'seller', 'cart', 'favorit', 
        'alamat_pengiriman', 'ulasan', 'chat', 'transaksi'
    ];

    foreach ($tables_to_delete_from as $table) {
        $fk_column = 'pengguna_id';
        if ($table === 'ulasan') {
            $fk_column = 'buyer_id';
        }
        
        $stmt_child = $conn->prepare("DELETE FROM $table WHERE $fk_column = ?");
        $stmt_child->bind_param("i", $id_user_dihapus);
        $stmt_child->execute();
        $stmt_child->close();
    }

    // 4. Setelah semua data terkait terhapus, hapus pengguna dari tabel utama `pengguna`
    $stmt_pengguna = $conn->prepare("DELETE FROM pengguna WHERE pengguna_id = ?");
    $stmt_pengguna->bind_param("i", $id_user_dihapus);
    $stmt_pengguna->execute();
    $stmt_pengguna->close();

    // Jika semua query berhasil, commit transaksi
    $conn->commit();
    header("Location: kelola_user.php?status=deleted");

} catch (Exception $e) {
    // Jika terjadi error, rollback semua perubahan
    $conn->rollback();
    // Tampilkan error atau redirect ke halaman error
    // header("Location: kelola_user.php?error=" . urlencode($e->getMessage()));
    header("Location: kelola_user.php?error=delete_failed");
}

$conn->close();
exit;
?>