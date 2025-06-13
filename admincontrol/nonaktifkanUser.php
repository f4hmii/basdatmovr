<?php
session_start();
include '../db_connection.php';

// 1. Pengecekan Keamanan Wajib! Hanya admin yang bisa mengakses.
if (!isset($_SESSION['pengguna_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../pages/login.php');
    exit;
}
if (!isset($_GET['pengguna_id']) || !is_numeric($_GET['pengguna_id'])) {
    die("Error: ID pengguna tidak valid.");
}

$id_user_dihapus = intval($_GET['pengguna_id']);
$id_admin_login = $_SESSION['pengguna_id'];

// Mencegah admin menghapus akunnya sendiri
if ($id_user_dihapus === $id_admin_login) {
    die("Error: Anda tidak dapat menghapus akun Anda sendiri.");
}

// === Mulai Transaksi Database untuk memastikan semua data terkait terhapus ===
$conn->begin_transaction();

try {
    // Urutan penghapusan sangat penting untuk menghindari error foreign key.
    // Hapus dari tabel anak terlebih dahulu.

    // 1. Hapus dari 'admin' (jika pengguna adalah admin lain)
    $stmt_admin = $conn->prepare("DELETE FROM admin WHERE pengguna_id = ?");
    $stmt_admin->bind_param("i", $id_user_dihapus);
    $stmt_admin->execute();
    $stmt_admin->close();

    // 2. Hapus dari 'seller' (jika pengguna adalah seller)
    // PERINGATAN: Ini juga akan memicu penghapusan produk milik seller ini jika constraint diatur dengan benar.
    // Kita akan hapus produknya secara manual untuk memastikan.
    $stmt_seller = $conn->prepare("DELETE FROM seller WHERE pengguna_id = ?");
    $stmt_seller->bind_param("i", $id_user_dihapus);
    $stmt_seller->execute();
    $stmt_seller->close();
    
    // 3. Hapus semua data yang terkait langsung dengan pengguna
    $tables_to_delete_from = [
        'cart', 
        'favorit', 
        'alamat_pengiriman', 
        'ulasan', // Menghapus ulasan yang diberikan oleh user ini
        'chat',
        // 'transaksi' dan 'pembayaran' mungkin lebih baik dipertahankan untuk arsip, 
        // tapi untuk hard delete, kita hapus juga.
        'pembayaran', // Hapus pembayaran dulu karena berelasi dengan transaksi
        'transaksi'
    ];

    foreach ($tables_to_delete_from as $table) {
        // Kolom foreign key bisa 'pengguna_id' atau 'buyer_id'
        $fk_column = ($table === 'ulasan' || $table === 'pesanan') ? 'buyer_id' : 'pengguna_id';
        
        $stmt_child = $conn->prepare("DELETE FROM $table WHERE $fk_column = ?");
        $stmt_child->bind_param("i", $id_user_dihapus);
        $stmt_child->execute();
        $stmt_child->close();
    }

    // 4. Hapus semua produk milik pengguna ini (jika dia seller)
    $stmt_produk = $conn->prepare("DELETE FROM produk WHERE seller_id = ?");
    $stmt_produk->bind_param("i", $id_user_dihapus);
    $stmt_produk->execute();
    $stmt_produk->close();

    // 5. Terakhir, hapus pengguna dari tabel utama 'pengguna'
    $stmt_user = $conn->prepare("DELETE FROM pengguna WHERE pengguna_id = ?");
    $stmt_user->bind_param("i", $id_user_dihapus);
    $stmt_user->execute();
    $stmt_user->close();

    // Jika semua query berhasil, simpan perubahan
    $conn->commit();

    // Redirect kembali ke halaman kelola user
    header("Location: dashbord_admin.php#kelola_user");
    exit();

} catch (Exception $e) {
    // Jika ada satu saja query yang gagal, batalkan semua perubahan
    $conn->rollback();
    die("Gagal menghapus pengguna secara menyeluruh. Error: " . $e->getMessage());
}
?>