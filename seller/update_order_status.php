<?php
session_start();
include '../db_connection.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../pages/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaksi_id = intval($_POST['transaksi_id'] ?? 0);
    $new_status = $_POST['new_status'] ?? '';
    $seller_id = intval($_SESSION['id']);

    // Validasi input
    if ($transaksi_id <= 0 || !in_array($new_status, ['shipped', 'completed', 'cancelled'])) {
        $_SESSION['message'] = "Aksi atau ID transaksi tidak valid.";
        $_SESSION['message_type'] = "error";
        header("Location: kelola_pesanan.php");
        exit;
    }

    // Pastikan transaksi ini benar-benar milik seller ini dan statusnya memungkinkan untuk diubah
    // Anda perlu memastikan bahwa transaksi_detail ini mengandung produk dari seller_id yang sedang login.
    // Query ini akan memastikan bahwa setidaknya ADA SATU produk di transaksi ini yang dimiliki oleh seller yang sedang login
    $check_ownership_query = $conn->prepare("
        SELECT COUNT(td.detail_id) AS owned_products
        FROM transaksi_detail td
        JOIN produk p ON td.produk_id = p.produk_id
        WHERE td.transaksi_id = ? AND p.seller_id = ?
    ");
    $check_ownership_query->bind_param("ii", $transaksi_id, $seller_id);
    $check_ownership_query->execute();
    $ownership_result = $check_ownership_query->get_result()->fetch_assoc();
    $check_ownership_query->close();

    if ($ownership_result['owned_products'] > 0) {
        // Update status transaksi
        $update_stmt = $conn->prepare("UPDATE transaksi SET status_transaksi = ? WHERE transaksi_id = ?");
        $update_stmt->bind_param("si", $new_status, $transaksi_id);

        if ($update_stmt->execute()) {
            $_SESSION['message'] = "Status pesanan #{$transaksi_id} berhasil diperbarui menjadi '{$new_status}'.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Gagal memperbarui status pesanan: " . $conn->error;
            $_SESSION['message_type'] = "error";
        }
        $update_stmt->close();
    } else {
        $_SESSION['message'] = "Anda tidak memiliki izin untuk memperbarui pesanan ini.";
        $_SESSION['message_type'] = "error";
    }
} else {
    $_SESSION['message'] = "Metode request tidak diizinkan.";
    $_SESSION['message_type'] = "error";
}

header("Location: kelola_pesanan.php");
exit;
?>