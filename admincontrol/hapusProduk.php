<?php
session_start();
include '../db_connection.php';

// Pastikan hanya admin yang bisa mengakses halaman ini
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: kelola_produk.php");
    exit;
}

$produk_id = intval($_GET['id']);

// Mulai transaksi database
$conn->begin_transaction();

try {
    // 1. Ambil path foto utama untuk dihapus dari server nanti
    $stmt_foto = $conn->prepare("SELECT foto_url FROM produk WHERE produk_id = ?");
    $stmt_foto->bind_param("i", $produk_id);
    $stmt_foto->execute();
    $result_foto = $stmt_foto->get_result();
    $foto_utama = $result_foto->fetch_assoc()['foto_url'] ?? null;
    $stmt_foto->close();

    // Ambil path foto detail
    $stmt_fotos_detail = $conn->prepare("SELECT foto_path FROM produk_foto_detail WHERE produk_id = ?");
    $stmt_fotos_detail->bind_param("i", $produk_id);
    $stmt_fotos_detail->execute();
    $result_fotos_detail = $stmt_fotos_detail->get_result();
    $fotos_detail = [];
    while($row = $result_fotos_detail->fetch_assoc()) {
        $fotos_detail[] = $row['foto_path'];
    }
    $stmt_fotos_detail->close();

    // 2. Hapus dari tabel-tabel anak
    $tables_to_delete_from = [
        'cart', 'favorit', 'chat', 'ulasan', 'transaksi_detail'
    ];
    foreach ($tables_to_delete_from as $table) {
        $stmt_child = $conn->prepare("DELETE FROM $table WHERE produk_id = ?");
        $stmt_child->bind_param("i", $produk_id);
        $stmt_child->execute();
        $stmt_child->close();
    }
    
    // Catatan: produk_foto_detail dan produk_varian akan terhapus otomatis
    // karena foreign key constraint `ON DELETE CASCADE` di database.
    // Jika tidak ada constraint, Anda harus menghapusnya manual di sini.
    // Contoh:
    // $conn->query("DELETE FROM produk_foto_detail WHERE produk_id = $produk_id");
    // $conn->query("DELETE FROM produk_varian WHERE produk_id = $produk_id");


    // 3. Hapus produk dari tabel utama `produk`
    $stmt_produk = $conn->prepare("DELETE FROM produk WHERE produk_id = ?");
    $stmt_produk->bind_param("i", $produk_id);
    $stmt_produk->execute();
    $stmt_produk->close();
    
    // Commit transaksi jika semua berhasil
    $conn->commit();

    // 4. Hapus file foto dari server
    if ($foto_utama && file_exists("../uploads/" . $foto_utama)) {
        unlink("../uploads/" . $foto_utama);
    }
    foreach ($fotos_detail as $foto) {
        if ($foto && file_exists("../uploads/" . $foto)) {
            unlink("../uploads/" . $foto);
        }
    }

    header("Location: kelola_produk.php?status=deleted");

} catch (Exception $e) {
    $conn->rollback();
    header("Location: kelola_produk.php?error=delete_failed");
}

$conn->close();
exit;
?>