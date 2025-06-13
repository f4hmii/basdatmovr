<?php
session_start();
include '../db_connection.php';
// Kita tidak perlu menyertakan header.php di sini karena file ini adalah halaman utuh

// FIX 1: Pengecekan session menggunakan 'pengguna_id' dan validasi peran 'seller'
if (!isset($_SESSION['pengguna_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../pages/login.php");
    exit;
}

$pengguna_id = $_SESSION['pengguna_id'];
$username = $_SESSION['username'] ?? 'Guest';
$role = $_SESSION['role'] ?? 'N/A';

// --- Ambil Data Pengguna & Seller ---

// FIX 2: Mengambil data dari tabel `pengguna` (kolom 'alamat' dihapus)
$user_data = null;
$stmt_user = $conn->prepare("SELECT nama_pengguna, email, nomor_telepon FROM pengguna WHERE pengguna_id = ?");
$stmt_user->bind_param("i", $pengguna_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
if ($result_user->num_rows > 0) {
    $user_data = $result_user->fetch_assoc();
}
$stmt_user->close();

// FIX 3: Menambahkan query untuk mengambil data dari tabel `seller`
$seller_data = null;
$stmt_seller = $conn->prepare("SELECT nama_toko, alamat_toko FROM seller WHERE pengguna_id = ?");
$stmt_seller->bind_param("i", $pengguna_id);
$stmt_seller->execute();
$result_seller = $stmt_seller->get_result();
if ($result_seller->num_rows > 0) {
    $seller_data = $result_seller->fetch_assoc();
}
$stmt_seller->close();


// --- Statistik Dashboard ---

// FIX 4: Logika "Pesanan Perlu Diproses" disesuaikan.
// Kita anggap pesanan yang perlu diproses adalah yang status pembayarannya 'dibayar' (dari tabel pembayaran)
// dan belum ada status pengiriman (karena kolomnya tidak ada).
$orders_to_process = 0;
$stmt_orders = $conn->prepare("
    SELECT COUNT(DISTINCT p.pesanan_id) as total
    FROM pembayaran p
    JOIN transaksi t ON p.pesanan_id = t.transaksi_id
    JOIN transaksi_detail td ON t.transaksi_id = td.transaksi_id
    JOIN produk pr ON td.produk_id = pr.produk_id
    WHERE pr.seller_id = ? AND p.status_pembayaran = 'confirmed'
");
$stmt_orders->bind_param("i", $pengguna_id);
$stmt_orders->execute();
$orders_to_process = $stmt_orders->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_orders->close();


// 2. Total Produk yang Sudah Dibayar - Query ini sudah cukup baik, kita pertahankan
$products_paid = 0;
$stmt_products_paid = $conn->prepare("
    SELECT COUNT(DISTINCT td.produk_id) AS total_produk_dibayar
    FROM pembayaran p
    JOIN transaksi t ON p.pesanan_id = t.transaksi_id
    JOIN transaksi_detail td ON t.transaksi_id = td.transaksi_id
    JOIN produk pr ON td.produk_id = pr.produk_id
    WHERE pr.seller_id = ? AND p.status_pembayaran = 'confirmed'
");
$stmt_products_paid->bind_param("i", $pengguna_id);
$stmt_products_paid->execute();
$products_paid = $stmt_products_paid->get_result()->fetch_assoc()['total_produk_dibayar'] ?? 0;
$stmt_products_paid->close();


// 5. Total Produk Seller - Query ini sudah benar
$total_products_by_seller = 0;
$stmt_seller_products = $conn->prepare("SELECT COUNT(*) AS total FROM produk WHERE seller_id = ?");
$stmt_seller_products->bind_param("i", $pengguna_id);
$stmt_seller_products->execute();
$total_products_by_seller = $stmt_seller_products->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_seller_products->close();

// FIX 5: Query Produk Ditolak disesuaikan (kolom 'pesan_admin' tidak ada)
$produk_rejected = [];
$stmt_rejected = $conn->prepare("SELECT nama_produk FROM produk WHERE seller_id = ? AND verified = -1");
$stmt_rejected->bind_param("i", $pengguna_id);
$stmt_rejected->execute();
$result_rejected = $stmt_rejected->get_result();
while ($row = $result_rejected->fetch_assoc()) {
    $produk_rejected[] = $row;
}
$stmt_rejected->close();

// Placeholder untuk fitur masa depan
$total_returns = 0;
$reviews_to_reply = 0;
$wallet_balance = "Rp0";

// Sertakan header setelah semua logika PHP selesai
include "../view/header.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <title>Dashboard Seller</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
</head>
<body class="bg-gray-50 text-gray-900 font-sans">
    <main class="flex flex-col lg:flex-row max-w-7xl mx-auto mt-6 px-4 sm:px-6 lg:px-8 gap-8">
        
        <aside class="bg-white w-full lg:w-64 lg:flex-shrink-0 p-6 rounded-lg shadow-sm">
            <div class="flex items-center space-x-4">
                <i class="fas fa-user-circle text-4xl text-gray-500"></i>
                <div>
                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($username) ?></p>
                    <p class="text-sm text-gray-500 capitalize"><?= htmlspecialchars($role) ?></p>
                </div>
            </div>
            <hr class="my-6 border-gray-200"/>
            <div class="flex flex-col space-y-2 text-gray-600">
                <p class="font-bold text-gray-800 text-sm mb-2">MENU SELLER</p>
                <a class="flex items-center space-x-3 hover:bg-gray-100 p-2 rounded-md" href="profile.php">
                    <i class="fas fa-id-card w-5"></i><span>Profil Toko</span>
                </a>
                <a class="flex items-center space-x-3 hover:bg-gray-100 p-2 rounded-md" href="produk.php">
                    <i class="fas fa-box-open w-5"></i><span>Produk Saya</span>
                </a>
                <a class="flex items-center space-x-3 hover:bg-gray-100 p-2 rounded-md" href="kelola_pesanan.php">
                    <i class="fas fa-list-alt w-5"></i><span>Kelola Pesanan</span>
                </a>
            </div>
        </aside>
        
        <section class="flex-1 flex flex-col space-y-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-lg shadow-sm flex flex-col items-center justify-center text-center">
                    <div class="text-3xl font-bold text-blue-600"><?= $orders_to_process ?></div>
                    <p class="text-xs text-gray-500 mt-1">Pesanan Perlu Diproses</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm flex flex-col items-center justify-center text-center">
                    <div class="text-3xl font-bold text-green-600"><?= $products_paid ?></div>
                    <p class="text-xs text-gray-500 mt-1">Produk Terjual</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm flex flex-col items-center justify-center text-center">
                    <div class="text-3xl font-bold text-red-600"><?= count($produk_rejected) ?></div>
                    <p class="text-xs text-gray-500 mt-1">Produk Ditolak</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm flex flex-col items-center justify-center text-center">
                    <div class="text-3xl font-bold text-gray-800"><?= $total_products_by_seller ?></div>
                    <p class="text-xs text-gray-500 mt-1">Total Produk Aktif</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-3 mb-4">Informasi Toko & Kontak</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-500">Nama Toko</p>
                        <p class="font-medium text-gray-700"><?= htmlspecialchars($seller_data['nama_toko'] ?? 'Belum diatur') ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Alamat Toko</p>
                        <p class="font-medium text-gray-700"><?= htmlspecialchars($seller_data['alamat_toko'] ?? 'Belum diatur') ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Nama Kontak</p>
                        <p class="font-medium text-gray-700"><?= htmlspecialchars($user_data['nama_pengguna'] ?? '') ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="font-medium text-gray-700"><?= htmlspecialchars($user_data['email'] ?? '') ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Nomor Telepon</p>
                        <p class="font-medium text-gray-700"><?= htmlspecialchars($user_data['nomor_telepon'] ?? 'Belum diatur') ?></p>
                    </div>
                </div>
                </div>

            <?php if (count($produk_rejected) > 0): ?>
                <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                    <h3 class="text-md font-semibold text-red-800 mb-2">Detail Produk Ditolak</h3>
                    <ul class="list-disc pl-5 text-sm text-red-700">
                        <?php foreach ($produk_rejected as $pr): ?>
                            <li><?= htmlspecialchars($pr['nama_produk']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </section>
    </main>

</body>
</html>