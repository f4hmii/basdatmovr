<?php
// File: admincontrol/kelola_produk.php
session_start();
include '../db_connection.php';

if (!isset($_SESSION['pengguna_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403); die("Akses ditolak.");
}

$sql = "
    SELECT p.produk_id, p.foto_url, s.nama_toko, p.nama_produk, p.stock, p.harga, p.verified
    FROM produk p
    LEFT JOIN seller s ON p.seller_id = s.pengguna_id
    ORDER BY p.produk_id DESC
";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Semua Produk - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

<div class="container mx-auto p-6">
    <div class="bg-white p-8 rounded-lg shadow-lg">
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-box-open mr-2"></i>Kelola Semua Produk
            </h1>
            <a href="dashbord_admin.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex items-center transition-colors shadow-md">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Dashboard
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr class="bg-gray-800 text-white uppercase text-sm">
                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left">Produk</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left">Harga</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 text-center">Stok</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 text-center">Status Verifikasi</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-20 h-20">
                                        <img class="w-full h-full rounded-md object-cover" src="../uploads/<?= htmlspecialchars($row['foto_url']) ?>" alt="<?= htmlspecialchars($row['nama_produk']) ?>" />
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-gray-900 whitespace-no-wrap font-semibold">
                                            <?= htmlspecialchars($row['nama_produk']) ?>
                                        </p>
                                        <p class="text-gray-600 whitespace-no-wrap text-xs">
                                            Toko: <?= htmlspecialchars($row['nama_toko'] ?? 'N/A') ?>
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap font-semibold">Rp<?= number_format($row['harga'], 0, ',', '.') ?></p>
                            </td>
                            <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm text-center">
                                <p class="text-gray-900 whitespace-no-wrap"><?= $row['stock'] ?></p>
                            </td>
                            <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm text-center">
                                <?php
                                    $status_text = 'Menunggu'; 
                                    $status_class = 'bg-yellow-200 text-yellow-800';
                                    if ($row['verified'] == 1) { 
                                        $status_text = 'Disetujui'; 
                                        $status_class = 'bg-green-200 text-green-800'; 
                                    } elseif ($row['verified'] == -1) { 
                                        $status_text = 'Ditolak'; 
                                        $status_class = 'bg-red-200 text-red-800'; 
                                    }
                                ?>
                                <span class="relative inline-block px-3 py-1 font-semibold leading-tight rounded-full <?= $status_class ?>">
                                    <?= $status_text ?>
                                </span>
                            </td>
                            <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm text-center">
                                <a href="#" data-page="verifikasi_detail.php?produk_id=<?= $row['produk_id'] ?>" class="sidebar-link bg-indigo-500 hover:bg-indigo-600 text-white font-semibold text-xs py-2 px-4 rounded-md transition-colors shadow">
                                    <i class="fas fa-eye mr-1"></i> Detail
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-10 text-gray-500">Tidak ada produk untuk dikelola.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>