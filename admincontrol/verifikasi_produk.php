<?php
// File: admincontrol/verifikasi_produk.php (Versi Final - Navigasi Langsung)
session_start();
require '../db_connection.php';

if (!isset($_SESSION['pengguna_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit;
}

$sql = "SELECT p.*, s.nama_toko FROM produk p LEFT JOIN seller s ON p.seller_id = s.pengguna_id WHERE p.verified = 0 ORDER BY p.produk_id ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Verifikasi Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style> .body-flex-container { display: flex; min-height: 100vh; } </style>
</head>
<body class="body-flex-container bg-light">

<?php include 'sidebar.php'; // Menyertakan sidebar di setiap halaman ?>

<main class="flex-grow-1 p-4">
    <h1 class="mb-4">Produk Menunggu Verifikasi</h1>
    <div class="table-responsive bg-white p-3 rounded shadow-sm">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr><th>ID</th><th>Gambar</th><th>Nama Produk</th><th>Toko</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['produk_id'] ?></td>
                        <td><img src="../uploads/<?= htmlspecialchars($row['foto_url']) ?>" width="80" class="img-thumbnail"></td>
                        <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                        <td><?= htmlspecialchars($row['nama_toko'] ?? 'N/A') ?></td>
                        <td>
                            <a href="verifikasi_detail.php?produk_id=<?= $row['produk_id'] ?>" class="btn btn-info btn-sm">Lihat Detail</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">Tidak ada produk yang perlu diverifikasi saat ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>