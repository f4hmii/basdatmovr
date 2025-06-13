<?php
session_start();
include '../db_connection.php';

// Pastikan hanya seller yang bisa mengakses halaman ini
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../pages/login.php");
    exit();
}

$seller_id = $_SESSION['pengguna_id'];

// Mengambil pesanan yang mengandung produk dari seller yang sedang login
$sql = "
    SELECT 
        t.transaksi_id, 
        p.nama_pengguna AS nama_pembeli, 
        t.total_harga, 
        t.tanggal, 
        pb.status_pembayaran, -- Mengambil status dari tabel pembayaran
        GROUP_CONCAT(CONCAT(prod.nama_produk, ' (', td.quantity, 'x)') SEPARATOR '<br>') as produk_dibeli,
        ap.alamat, ap.kecamatan, ap.kabupaten_kota, ap.provinsi, ap.kode_pos
    FROM transaksi t
    JOIN pengguna p ON t.pengguna_id = p.pengguna_id
    JOIN transaksi_detail td ON t.transaksi_id = td.transaksi_id
    JOIN produk prod ON td.produk_id = prod.produk_id
    LEFT JOIN alamat_pengiriman ap ON t.alamat_id = ap.alamat_id
    LEFT JOIN pembayaran pb ON t.transaksi_id = pb.transaksi_id -- JOIN ke tabel pembayaran
    WHERE prod.seller_id = ?
    GROUP BY t.transaksi_id, p.nama_pengguna, t.total_harga, t.tanggal, pb.status_pembayaran
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Seller MOVR</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <?php include '../view/header.php'; ?>

    <div class="container mx-auto mt-10 p-4">
        <h1 class="text-3xl font-bold mb-6">Kelola Pesanan Masuk</h1>

        <div class="bg-white shadow-md rounded-lg overflow-x-auto">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr class="bg-gray-800 text-white uppercase text-sm">
                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left">ID Transaksi</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left">Tanggal</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left">Pembeli</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left">Produk</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left">Total</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left">Alamat Pengiriman</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 text-center">Status Pembayaran</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap"><?= htmlspecialchars($row['transaksi_id']) ?></p>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap"><?= htmlspecialchars(date("d M Y, H:i", strtotime($row['tanggal']))) ?></p>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap"><?= htmlspecialchars($row['nama_pembeli']) ?></p>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <div class="text-gray-900 whitespace-no-wrap"><?= $row['produk_dibeli'] ?></div>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap font-semibold">Rp <?= htmlspecialchars(number_format($row['total_harga'], 0, ',', '.')) ?></p>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap">
                                        <?= htmlspecialchars($row['alamat'] . ', ' . $row['kecamatan'] . ', ' . $row['kabupaten_kota'] . ', ' . $row['provinsi'] . ' ' . $row['kode_pos']) ?>
                                    </p>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                                    <?php
                                        $status_bayar = $row['status_pembayaran'] ?? 'pending';
                                        $color = 'bg-gray-200 text-gray-800';
                                        if ($status_bayar == 'confirmed') {
                                            $color = 'bg-green-200 text-green-800';
                                        } elseif ($status_bayar == 'pending') {
                                            $color = 'bg-yellow-200 text-yellow-800';
                                        } elseif ($status_bayar == 'rejected') {
                                            $color = 'bg-red-200 text-red-800';
                                        }
                                    ?>
                                    <span class="relative inline-block px-3 py-1 font-semibold leading-tight rounded-full <?= $color ?>">
                                        <?= htmlspecialchars(ucfirst($status_bayar)) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-10">
                                <p class="text-gray-500">Belum ada pesanan yang masuk.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include '../view/footer.php'; ?>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>