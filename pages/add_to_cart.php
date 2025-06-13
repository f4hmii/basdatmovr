<?php
session_start();
include '../db_connection.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Terjadi kesalahan.'];

// Pastikan pengguna sudah login
if (!isset($_SESSION['pengguna_id'])) {
    $response['message'] = 'Anda harus login terlebih dahulu.';
    echo json_encode($response);
    exit;
}

$pengguna_id = $_SESSION['pengguna_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produk_id = $_POST['produk_id'] ?? null;
    $nama_produk = $_POST['nama_produk'] ?? '';
    $harga = $_POST['harga'] ?? 0;
    $size = $_POST['size'] ?? '';
    $color = $_POST['color'] ?? '';
    $quantity = $_POST['quantity'] ?? 1;
    // PERBAIKAN: Ambil penanda/flag "has_variants" dari form
    $has_variants = $_POST['has_variants'] ?? '0';

    if (empty($produk_id) || empty($nama_produk) || empty($harga) || empty($quantity)) {
        $response['message'] = 'Data produk tidak lengkap.';
        echo json_encode($response);
        exit;
    }
    
    // PERBAIKAN: Validasi varian hanya jika produk memang memilikinya
    if ($has_variants === '1') {
        if (empty($size) || empty($color)) {
            $response['message'] = 'Silakan pilih ukuran dan warna.';
            echo json_encode($response);
            exit;
        }
    }

    // Cek apakah item dengan varian yang sama sudah ada di keranjang
    $stmt_check = $conn->prepare("SELECT cart_id, quantity FROM cart WHERE pengguna_id = ? AND produk_id = ? AND size = ? AND color = ?");
    $stmt_check->bind_param("iiss", $pengguna_id, $produk_id, $size, $color);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Jika sudah ada, update quantity-nya
        $existing_item = $result_check->fetch_assoc();
        $new_quantity = $existing_item['quantity'] + $quantity;
        
        $stmt_update = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
        $stmt_update->bind_param("ii", $new_quantity, $existing_item['cart_id']);
        if ($stmt_update->execute()) {
            $response['success'] = true;
            $response['message'] = 'Jumlah produk di keranjang berhasil diperbarui!';
        } else {
            $response['message'] = 'Gagal memperbarui keranjang.';
        }
        $stmt_update->close();
    } else {
        // Jika belum ada, tambahkan sebagai item baru
        $stmt_insert = $conn->prepare("INSERT INTO cart (pengguna_id, produk_id, nama_produk, harga, size, color, quantity, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt_insert->bind_param("iisdsi", $pengguna_id, $produk_id, $nama_produk, $harga, $size, $color, $quantity);
        
        if ($stmt_insert->execute()) {
            $response['success'] = true;
            $response['message'] = 'Produk berhasil ditambahkan ke keranjang!';
        } else {
            $response['message'] = 'Gagal menambahkan produk ke keranjang.';
        }
        $stmt_insert->close();
    }

    $stmt_check->close();
} else {
    $response['message'] = 'Metode request tidak valid.';
}

$conn->close();
echo json_encode($response);
?>