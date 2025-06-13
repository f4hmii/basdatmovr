<?php
// File: admincontrol/kelola_pembayaran.php
session_start();
include '../db_connection.php';

if (!isset($_SESSION['pengguna_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403); die("Akses ditolak.");
}

// Logika untuk konfirmasi pembayaran
if (isset($_GET['konfirmasi_id'])) {
    $id_konfirmasi = intval($_GET['konfirmasi_id']);
    $stmt = $conn->prepare("UPDATE pembayaran SET status_pembayaran = 'confirmed' WHERE pembayaran_id = ? AND status_pembayaran = 'pending'");
    $stmt->bind_param("i", $id_konfirmasi);
    $stmt->execute();
    $stmt->close();
    header("Location: dashbord_admin.php#kelola_pembayaran"); // Redirect untuk refresh
    exit;
}

// Ambil semua data pembayaran
$sql = "
    SELECT p.*, t.pengguna_id, u.username, mp.nama_bank
    FROM pembayaran p
    JOIN transaksi t ON p.pesanan_id = t.transaksi_id
    JOIN pengguna u ON t.pengguna_id = u.pengguna_id
    JOIN metode_pembayaran mp ON p.metode_pembayaran = mp.id
    ORDER BY p.tanggal_pembayaran DESC
";
$result = $conn->query($sql);
?>
<h2 class="mb-4">Kelola Pembayaran</h2>
<div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID Bayar</th>
                <th>ID Pesanan</th>
                <th>Pengguna</th>
                <th>Metode</th>
                <th>Jumlah</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['pembayaran_id'] ?></td>
                    <td><?= $row['pesanan_id'] ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['nama_bank']) ?></td>
                    <td>Rp<?= number_format($row['jumlah_pembayaran'], 0, ',', '.') ?></td>
                    <td><?= date('d M Y H:i', strtotime($row['tanggal_pembayaran'])) ?></td>
                    <td>
                        <?php
                            $status_class = ($row['status_pembayaran'] == 'confirmed') ? 'bg-success' : (($row['status_pembayaran'] == 'pending') ? 'bg-warning text-dark' : 'bg-danger');
                        ?>
                        <span class="badge <?= $status_class ?>"><?= ucfirst($row['status_pembayaran']) ?></span>
                    </td>
                    <td>
                        <?php if ($row['status_pembayaran'] == 'pending'): ?>
                            <a href="dashbord_admin.php#kelola_pembayaran&konfirmasi_id=<?= $row['pembayaran_id'] ?>" class="btn btn-success btn-sm" onclick="return confirm('Konfirmasi pembayaran ini?')">Konfirmasi</a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8" class="text-center">Tidak ada data pembayaran.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>