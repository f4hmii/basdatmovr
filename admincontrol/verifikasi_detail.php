<?php
// File: admincontrol/verifikasi_detail.php
session_start();
include '../db_connection.php';

if (!isset($_SESSION['pengguna_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403); die("Akses ditolak.");
}
$produk_id = intval($_GET['produk_id'] ?? 0);
if ($produk_id <= 0) die("ID produk tidak valid.");

$stmt_produk = $conn->prepare("
    SELECT p.*, s.nama_toko, k.nama_kategori
    FROM produk p
    LEFT JOIN seller s ON p.seller_id = s.pengguna_id
    LEFT JOIN kategori k ON p.kategori_id = k.kategori_id
    WHERE p.produk_id = ?
");
$stmt_produk->bind_param("i", $produk_id);
$stmt_produk->execute();
$result_produk = $stmt_produk->get_result();
if ($result_produk->num_rows === 0) die("Produk tidak ditemukan.");
$row = $result_produk->fetch_assoc();
$stmt_produk->close();

$stmt_fotos = $conn->prepare("SELECT foto_path FROM produk_foto_detail WHERE produk_id = ?");
$stmt_fotos->bind_param("i", $produk_id);
$stmt_fotos->execute();
$result_fotos = $stmt_fotos->get_result();
$foto_detail_list = [];
while ($foto = $result_fotos->fetch_assoc()) {
    $foto_detail_list[] = $foto['foto_path'];
}
$stmt_fotos->close();
?>
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Detail Produk #<?= $row['produk_id'] ?></h4>
        <a href="#" class="btn btn-secondary sidebar-link" data-page="verifikasi_produk.php">Kembali</a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-5">
                <h5 class="mb-3">Galeri Foto</h5>
                <div class="mb-3"><label class="form-label fw-bold">Foto Utama</label><img src="../uploads/<?= htmlspecialchars($row['foto_url']) ?>" class="img-fluid rounded border" /></div>
                <?php if (!empty($foto_detail_list)): ?>
                <div class="mb-3">
                    <label class="form-label fw-bold">Foto Detail</label>
                    <div class="row g-2">
                    <?php foreach($foto_detail_list as $foto_detail): ?>
                        <div class="col-4"><img src="../uploads/<?= htmlspecialchars($foto_detail) ?>" class="img-fluid rounded border" /></div>
                    <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="col-md-7">
                <h5 class="mb-3">Informasi Produk</h5>
                <table class="table table-bordered table-striped">
                    <tr><th style="width: 30%;">Nama Produk</th><td><?= htmlspecialchars($row['nama_produk']) ?></td></tr>
                    <tr><th>Toko Penjual</th><td><?= htmlspecialchars($row['nama_toko'] ?? 'N/A') ?></td></tr>
                    <tr><th>Kategori</th><td><?= htmlspecialchars($row['nama_kategori'] ?? 'N/A') ?></td></tr>
                    <tr><th>Kondisi</th><td><?= htmlspecialchars($row['kondisi']) ?></td></tr>
                    <tr><th>Deskripsi</th><td><?= nl2br(htmlspecialchars($row['deskripsi'])) ?></td></tr>
                    <tr><th>Stok</th><td><?= $row['stock'] ?></td></tr>
                    <tr><th>Harga</th><td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="card-footer text-end bg-light">
        <?php if ($row['verified'] == 0): ?>
            <a href="verifikasi_aksi.php?produk_id=<?= $row['produk_id'] ?>&aksi=setuju" class="btn btn-success" onclick="return confirm('Anda yakin ingin MENYETUJUI produk ini?')">Setujui</a>
            <a href="verifikasi_aksi.php?produk_id=<?= $row['produk_id'] ?>&aksi=tolak" class="btn btn-danger" onclick="return confirm('Anda yakin ingin MENOLAK produk ini?')">Tolak</a>
        <?php else: ?>
            <p class="mb-0 text-muted">Tindakan sudah diambil untuk produk ini.</p>
        <?php endif; ?>
    </div>
</div>