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
<h2 class="mb-4">Kelola Semua Produk</h2>
<div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Gambar</th>
                <th>Toko</th>
                <th>Nama Produk</th>
                <th>Stok</th>
                <th>Harga</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['produk_id'] ?></td>
                    <td><img src="../uploads/<?= htmlspecialchars($row['foto_url']) ?>" width="80" class="img-thumbnail"></td>
                    <td><?= htmlspecialchars($row['nama_toko'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                    <td><?= $row['stock'] ?></td>
                    <td>Rp<?= number_format($row['harga'], 0, ',', '.') ?></td>
                    <td>
                        <?php
                            $status_text = 'Menunggu'; $status_class = 'bg-warning text-dark';
                            if ($row['verified'] == 1) { $status_text = 'Disetujui'; $status_class = 'bg-success text-white'; }
                            elseif ($row['verified'] == -1) { $status_text = 'Ditolak'; $status_class = 'bg-danger text-white'; }
                        ?>
                        <span class="badge <?= $status_class ?>"><?= $status_text ?></span>
                    </td>
                    <td>
                        <a href="#" data-page="verifikasi_detail.php?produk_id=<?= $row['produk_id'] ?>" class="btn btn-info btn-sm sidebar-link">Detail</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8" class="text-center">Tidak ada produk.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>