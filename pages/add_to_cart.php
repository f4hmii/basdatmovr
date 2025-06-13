<?php
session_start();
include '../db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produk_id = intval($_POST['produk_id'] ?? 0);
    $nama_produk = $_POST['nama_produk'] ?? '';
    $harga = floatval($_POST['harga'] ?? 0);
    $color = $_POST['color'] ?? 'default';
    $size = $_POST['size'] ?? 'M';
    $quantity = intval($_POST['quantity'] ?? 1);
    $pengguna_id = $_SESSION['id'] ?? null;

    if (!$pengguna_id) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
        exit;
    }

    if (!$produk_id || !$nama_produk || !$harga) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Data produk tidak lengkap']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO cart (produk_id, pengguna_id, nama_produk, harga, color, size, quantity, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iisdssi", $produk_id, $pengguna_id, $nama_produk, $harga, $color, $size, $quantity);

   if ($stmt->execute()) {
    header('Location: detail.php?id=' . $produk_id . '&added=1');
    exit;

    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan ke keranjang: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
}
