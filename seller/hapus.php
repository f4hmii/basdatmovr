<?php
session_start();
include '../db_connection.php';

// FIX 1: Menambahkan Pengecekan Keamanan (Wajib!)
// Pastikan pengguna login, adalah seorang seller, dan produk_id valid.
if (!isset($_SESSION['pengguna_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    // Jika tidak, tendang ke halaman login
    header('Location: ../pages/login.php');
    exit;
}
if (!isset($_GET['produk_id']) || !is_numeric($_GET['produk_id'])) {
    die("Error: ID produk tidak valid.");
}

$seller_id = $_SESSION['pengguna_id'];
$produk_id = intval($_GET['produk_id']);

// === Mulai Transaksi Database ===
$conn->begin_transaction();

try {
    // FIX 2: Pengecekan Kepemilikan Produk (Sangat Penting!)
    // Pastikan seller ini benar-benar pemilik produk yang akan dihapus.
    $stmt_check = $conn->prepare("SELECT seller_id FROM produk WHERE produk_id = ?");
    $stmt_check->bind_param("i", $produk_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows === 0) {
        throw new Exception("Produk tidak ditemukan.");
    }
    
    $product_owner = $result_check->fetch_assoc()['seller_id'];
    if ($product_owner !== $seller_id) {
        // Jika bukan pemiliknya, gagalkan operasi.
        throw new Exception("Anda tidak memiliki izin untuk menghapus produk ini.");
    }
    $stmt_check->close();

    // FIX 3: Menghapus data dari tabel-tabel anak terlebih dahulu untuk menghindari error foreign key.
    
    // Hapus dari cart
    $stmt_cart = $conn->prepare("DELETE FROM cart WHERE produk_id = ?");
    $stmt_cart->bind_param("i", $produk_id);
    $stmt_cart->execute();
    $stmt_cart->close();
    
    // Hapus dari favorit
    $stmt_fav = $conn->prepare("DELETE FROM favorit WHERE produk_id = ?");
    $stmt_fav->bind_param("i", $produk_id);
    $stmt_fav->execute();
    $stmt_fav->close();
    
    // Hapus dari chat (jika ada)
    $stmt_chat = $conn->prepare("DELETE FROM chat WHERE produk_id = ?");
    $stmt_chat->bind_param("i", $produk_id);
    $stmt_chat->execute();
    $stmt_chat->close();

    // Hapus foto-foto detail (Tabel ini sudah ON DELETE CASCADE, tapi lebih aman dihapus manual juga)
    // Sebenarnya langkah ini tidak wajib jika ON DELETE CASCADE sudah pasti aktif.
    $stmt_foto = $conn->prepare("DELETE FROM produk_foto_detail WHERE produk_id = ?");
    $stmt_foto->bind_param("i", $produk_id);
    $stmt_foto->execute();
    $stmt_foto->close();

    // FIX 4: Menghapus query ke tabel `produk_size` yang tidak ada.
    
    // PERINGATAN: Menghapus dari detail transaksi akan menghilangkan riwayat.
    // Namun ini diperlukan untuk hard delete jika tidak ada ON DELETE SET NULL/CASCADE
    $stmt_trans_detail = $conn->prepare("DELETE FROM transaksi_detail WHERE produk_id = ?");
    $stmt_trans_detail->bind_param("i", $produk_id);
    $stmt_trans_detail->execute();
    $stmt_trans_detail->close();


    // Terakhir, hapus produk utama dari tabel `produk`
    $stmt_produk = $conn->prepare("DELETE FROM produk WHERE produk_id = ?");
    $stmt_produk->bind_param("i", $produk_id);
    $stmt_produk->execute();
    $stmt_produk->close();

    // Jika semua berhasil, commit transaksi
    $conn->commit();
    
    // Arahkan kembali ke halaman produk
    header("Location: produk.php?status=deleted");
    exit();

} catch (Exception $e) {
    // Jika ada error, batalkan semua perubahan
    $conn->rollback();
    die("Gagal menghapus produk: " . $e->getMessage());
}

?>