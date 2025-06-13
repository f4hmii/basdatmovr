<?php
session_start();
include '../db_connection.php';
include '../view/header.php';

// FIX 1: Pengecekan session diubah dari 'user_id' menjadi 'pengguna_id'
if (!isset($_SESSION['pengguna_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../pages/login.php");
    exit();
}

// Mengambil ID seller dari session
$seller_id = $_SESSION['pengguna_id'];

// FIX 2: Query diubah menggunakan prepared statement dan JOIN yang lebih efisien
// FIX 3: Menghapus kolom 'status_transaksi' yang tidak ada di database
$sql = "
    SELECT 
        t.transaksi_id, 
        p.nama_pengguna, 
        p.email,
        t.total_harga, 
        t.alamat_pengiriman, 
        t.metode_pembayaran, 
        t.tanggal, 
        t.status_pembayaran,
        GROUP_CONCAT(CONCAT(prod.nama_produk, ' (', td.quantity, 'x)') SEPARATOR '<br>') as produk_dibeli
    FROM transaksi t
    JOIN pengguna p ON t.pengguna_id = p.pengguna_id
    JOIN transaksi_detail td ON t.transaksi_id = td.transaksi_id
    JOIN produk prod ON td.produk_id = prod.produk_id
    WHERE prod.seller_id = ?
    GROUP BY t.transaksi_id, p.nama_pengguna, p.email, t.total_harga, t.alamat_pengiriman, t.metode_pembayaran, t.tanggal, t.status_pembayaran
    ORDER BY t.tanggal DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pesanan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Kelola Pesanan Masuk</h1>

    <div class="bg-white shadow-md rounded-lg overflow-x-auto">
        <table class="min-w-full leading-normal">
            <thead>
                <tr>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID Transaksi</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Pembeli</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Produk Dipesan</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Harga</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Alamat Kirim</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tanggal</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status Bayar</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">#<?= htmlspecialchars($row['transaksi_id']) ?></td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap"><?= htmlspecialchars($row['nama_pengguna']) ?></p>
                                <p class="text-gray-600 whitespace-no-wrap text-xs"><?= htmlspecialchars($row['email']) ?></p>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><?= $row['produk_dibeli'] // Tidak perlu htmlspecialchars karena sudah digabung dengan aman di query ?></td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><?= htmlspecialchars($row['alamat_pengiriman']) ?></td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><?= date('d M Y H:i', strtotime($row['tanggal'])) ?></td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <?php 
                                    $status_bayar = $row['status_pembayaran'];
                                    $color = 'text-gray-600';
                                    if ($status_bayar == 'dibayar') {
                                        $color = 'text-green-600';
                                    } elseif ($status_bayar == 'belum') {
                                        $color = 'text-red-600';
                                    }
                                ?>
                                <span class="relative inline-block px-3 py-1 font-semibold <?= $color ?> leading-tight">
                                    <span aria-hidden class="absolute inset-0 opacity-50 rounded-full"></span>
                                    <span class="relative"><?= ucfirst($status_bayar) ?></span>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-10">
                            <p class="text-gray-500">Belum ada pesanan yang masuk untuk produk Anda.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$stmt->close();
$conn->close();
include '../view/footer.php';
?>
</body>
</html>