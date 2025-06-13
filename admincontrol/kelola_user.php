<?php
// File: admincontrol/kelola_user.php
session_start();
include '../db_connection.php';

// Keamanan: Pastikan hanya admin yang bisa mengakses.
if (!isset($_SESSION['pengguna_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    die("Akses ditolak. Silakan login sebagai admin.");
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

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

<div class="container mx-auto p-6">
    <div class="bg-white p-8 rounded-lg shadow-lg">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-users mr-2"></i>Kelola Pengguna
            </h1>
             <a href="dashbord_admin.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex items-center transition-colors shadow-md">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Dashboard
            </a>
        </div>

        <?php if (isset($_GET['status'])): ?>
        <div class="mb-4 p-4 rounded-md 
            <?php echo $_GET['status'] == 'deleted' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>"
            role="alert">
            <?php
                if ($_GET['status'] == 'deleted') echo 'Pengguna berhasil dihapus.';
                if ($_GET['status'] == 'delete_failed') echo 'Gagal menghapus pengguna.';
                if ($_GET['error'] == 'cannot_delete_self') echo 'Anda tidak dapat menghapus akun Anda sendiri.';
            ?>
        </div>
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr class="bg-gray-800 text-white uppercase text-sm">
                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left">ID</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left">Profil</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left">Kontak</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 text-center">Role</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap font-bold"><?= $row['pengguna_id'] ?></p>
                            </td>
                            <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap font-semibold"><?= htmlspecialchars($row['nama_pengguna']) ?></p>
                                <p class="text-gray-600 whitespace-no-wrap">@<?= htmlspecialchars($row['username']) ?></p>
                            </td>
                            <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap"><?= htmlspecialchars($row['email']) ?></p>
                                <p class="text-gray-600 whitespace-no-wrap"><?= htmlspecialchars($row['nomor_telepon']) ?></p>
                            </td>
                            <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm text-center">
                                <?php 
                                    $role_class = '';
                                    if ($row['role_pengguna'] == 'Admin') {
                                        $role_class = 'bg-red-200 text-red-800';
                                    } elseif ($row['role_pengguna'] == 'Seller') {
                                        $role_class = 'bg-green-200 text-green-800';
                                    } else {
                                        $role_class = 'bg-blue-200 text-blue-800';
                                    }
                                ?>
                                <span class="relative inline-block px-3 py-1 font-semibold leading-tight rounded-full <?= $role_class ?>">
                                    <?= htmlspecialchars($row['role_pengguna']) ?>
                                </span>
                            </td>
                            <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm text-center">
                                <?php if ($row['pengguna_id'] !== $_SESSION['pengguna_id']): ?>
                                    <a href="#" data-page="edituser.php?pengguna_id=<?= $row['pengguna_id'] ?>" class="sidebar-link text-yellow-600 hover:text-yellow-900 mr-3" title="Edit">
                                        <i class="fas fa-pencil-alt"></i> Edit
                                    </a>
                                    <a href="hapusUser.php?id=<?= $row['pengguna_id'] ?>" class="text-red-600 hover:text-red-900" 
                                       onclick="return confirm('PERINGATAN!\nMenghapus pengguna ini akan menghapus SEMUA DATA terkait (produk, transaksi, dll) secara permanen.\n\nAnda yakin ingin melanjutkan?')" title="Hapus">
                                        <i class="fas fa-trash-alt"></i> Hapus
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-500 italic text-xs">(Akun Anda)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-10 text-gray-500">Tidak ada data pengguna.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>