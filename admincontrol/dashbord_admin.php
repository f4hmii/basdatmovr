<?php
// File: admincontrol/dashbord_admin.php (Versi Final - Navigasi Langsung)
session_start();
require '../db_connection.php';

if (!isset($_SESSION['pengguna_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit;
}

$result = $conn->query("SELECT
  (SELECT COUNT(*) FROM pengguna) AS jumlah_pengguna,
  (SELECT COUNT(*) FROM produk WHERE verified = 1) AS jumlah_barang,
  (SELECT COUNT(*) FROM pembayaran WHERE status_pembayaran = 'pending') AS jumlah_pembayaran_pending,
  (SELECT COUNT(*) FROM produk WHERE verified = 0) AS jumlah_verifikasi_produk");
  
$row = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style> .body-flex-container { display: flex; min-height: 100vh; } </style>
</head>
<body class="body-flex-container bg-light">

<?php include 'sidebar.php'; ?>

<main class="flex-grow-1 p-4">
    <h1 class="mb-4">Dashboard</h1>
    <p>Selamat datang kembali, <?= htmlspecialchars($_SESSION['username']); ?>!</p>
    <div class="row">
        <div class="col-md-3 mb-4"><div class="card text-white bg-primary"><div class="card-body fs-2 fw-bold"><?= $row['jumlah_pengguna'] ?? 0 ?></div><div class="card-footer">Total Pengguna</div></div></div>
        <div class="col-md-3 mb-4"><div class="card text-white bg-success"><div class="card-body fs-2 fw-bold"><?= $row['jumlah_barang'] ?? 0 ?></div><div class="card-footer">Produk Dijual</div></div></div>
        <div class="col-md-3 mb-4"><div class="card text-white bg-warning"><div class="card-body fs-2 fw-bold"><?= $row['jumlah_pembayaran_pending'] ?? 0 ?></div><div class="card-footer">Pembayaran Pending</div></div></div>
        <div class="col-md-3 mb-4"><div class="card text-white bg-danger"><div class="card-body fs-2 fw-bold"><?= $row['jumlah_verifikasi_produk'] ?? 0 ?></div><div class="card-footer">Verifikasi Produk</div></div></div>
    </div>
</main>

</body>
</html>