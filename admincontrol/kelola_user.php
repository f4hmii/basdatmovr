<?php
// File: admincontrol/kelola_user.php
session_start();
include '../db_connection.php';

// Keamanan: Pastikan hanya admin yang bisa mengakses.
if (!isset($_SESSION['pengguna_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    die("Akses ditolak.");
}

// Query untuk mengambil semua pengguna beserta perannya secara dinamis
$sql = "
    SELECT 
        p.pengguna_id, p.username, p.nama_pengguna, p.email, p.nomor_telepon,
        CASE
            WHEN a.admin_id IS NOT NULL THEN 'Admin'
            WHEN s.seller_id IS NOT NULL THEN 'Seller'
            ELSE 'Buyer'
        END AS role_pengguna
    FROM pengguna p
    LEFT JOIN admin a ON p.pengguna_id = a.pengguna_id
    LEFT JOIN seller s ON p.pengguna_id = s.pengguna_id
    ORDER BY p.pengguna_id DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>
<h2 class="mb-4">Kelola Pengguna</h2>
<div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Nama Pengguna</th>
                <th>Email</th>
                <th>Telepon</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['pengguna_id'] ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['nama_pengguna']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['nomor_telepon']) ?></td>
                    <td>
                        <?php 
                            $role_class = ($row['role_pengguna'] == 'Admin') ? 'bg-danger' : (($row['role_pengguna'] == 'Seller') ? 'bg-success' : 'bg-info');
                        ?>
                        <span class="badge <?= $role_class ?>"><?= htmlspecialchars($row['role_pengguna']) ?></span>
                    </td>
                    <td>
                        <?php if ($row['role_pengguna'] !== 'Admin' || $row['pengguna_id'] === $_SESSION['pengguna_id']): ?>
                            <a href="#" data-page="edituser.php?pengguna_id=<?= $row['pengguna_id'] ?>" class="btn btn-warning btn-sm sidebar-link">Edit</a>
                            <?php if ($row['pengguna_id'] !== $_SESSION['pengguna_id']): ?>
                                <a href="hapusUser.php?pengguna_id=<?= $row['pengguna_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('PERINGATAN! Menghapus user ini akan menghapus SEMUA DATA terkait (produk, transaksi, dll) secara permanen. Anda yakin?')">Hapus</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center">Tidak ada data pengguna.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>