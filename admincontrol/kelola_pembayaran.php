<?php
// File: admincontrol/kelola_pembayaran.php
session_start();
include '../db_connection.php';

// Pastikan hanya admin yang bisa mengakses halaman ini
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit;
}

// Logika untuk konfirmasi pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['konfirmasi'])) {
    $pembayaran_id = $_POST['pembayaran_id'];

    // Update status pembayaran menjadi 'confirmed'
    $stmt = $conn->prepare("UPDATE pembayaran SET status_pembayaran = 'confirmed' WHERE pembayaran_id = ? AND status_pembayaran = 'pending'");
    $stmt->bind_param("i", $pembayaran_id);
    
    if ($stmt->execute()) {
        header("Location: kelola_pembayaran.php?status=confirmed");
    } else {
        header("Location: kelola_pembayaran.php?status=error");
    }
    $stmt->close();
    exit;
}

// Ambil semua data pembayaran
$sql = "
    SELECT 
        p.pembayaran_id,
        p.transaksi_id,
        p.tanggal_pembayaran,
        p.jumlah_pembayaran,
        p.status_pembayaran,
        p.bukti_pembayaran,
        t.pengguna_id,
        u.username,
        mp.nama_bank,
        mp.metode
    FROM pembayaran p
    JOIN transaksi t ON p.transaksi_id = t.transaksi_id
    JOIN pengguna u ON t.pengguna_id = u.pengguna_id
    JOIN metode_pembayaran mp ON p.metode_id = mp.metode_id
    ORDER BY p.tanggal_pembayaran DESC
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pembayaran - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

<div class="container mx-auto p-6">
    <div class="bg-white p-8 rounded-lg shadow-lg">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-money-check-alt mr-2"></i>Kelola Pembayaran
            </h1>
            <a href="dashbord_admin.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex items-center transition-colors shadow-md">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Dashboard
            </a>
        </div>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'confirmed'): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">Pembayaran berhasil dikonfirmasi.</span>
            </div>
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="min-w-full leading-normal">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wider">Pembayaran</th>
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wider">Pengguna</th>
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wider">Metode</th>
                        <th class="py-3 px-5 text-center text-xs font-semibold uppercase tracking-wider">Bukti</th>
                        <th class="py-3 px-5 text-center text-xs font-semibold uppercase tracking-wider">Status</th>
                        <th class="py-3 px-5 text-center text-xs font-semibold uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50 border-b border-gray-200">
                            <td class="py-4 px-5">
                                <p class="text-gray-900 whitespace-no-wrap font-semibold">ID: <?= htmlspecialchars($row['pembayaran_id']) ?></p>
                                <p class="text-gray-600 whitespace-no-wrap text-xs"><?= htmlspecialchars(date('d M Y, H:i', strtotime($row['tanggal_pembayaran']))) ?></p>
                            </td>
                            <td class="py-4 px-5">
                                <p class="text-gray-900 whitespace-no-wrap font-semibold"><?= htmlspecialchars($row['username']) ?></p>
                                <p class="text-gray-600 whitespace-no-wrap text-xs">User ID: <?= htmlspecialchars($row['pengguna_id']) ?></p>
                            </td>
                            <td class="py-4 px-5">
                                <p class="text-gray-900 whitespace-no-wrap font-semibold"><?= htmlspecialchars($row['nama_bank']) ?> - <?= htmlspecialchars($row['metode']) ?></p>
                                <p class="text-gray-600 whitespace-no-wrap font-bold">Rp <?= htmlspecialchars(number_format($row['jumlah_pembayaran'], 0, ',', '.')) ?></p>
                            </td>
                            <td class="py-4 px-5 text-center">
                                <?php if (!empty($row['bukti_pembayaran'])): ?>
                                    <a href="../uploads/bukti_pembayaran/<?= htmlspecialchars($row['bukti_pembayaran']) ?>" target="_blank" 
                                       class="bg-blue-100 hover:bg-blue-200 text-blue-700 text-xs font-semibold py-1 px-3 rounded-full transition-colors">
                                        Lihat Bukti
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-400 italic text-xs">Tidak Ada</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-5 text-center">
                                <?php
                                    $status_class = '';
                                    switch ($row['status_pembayaran']) {
                                        case 'pending': $status_class = 'bg-yellow-200 text-yellow-800'; break;
                                        case 'confirmed': $status_class = 'bg-green-200 text-green-800'; break;
                                        case 'rejected': $status_class = 'bg-red-200 text-red-800'; break;
                                    }
                                ?>
                                <span class="relative inline-block px-3 py-1 font-semibold leading-tight rounded-full <?= $status_class ?>">
                                    <?= htmlspecialchars(ucfirst($row['status_pembayaran'])) ?>
                                </span>
                            </td>
                            <td class="py-4 px-5 text-center">
                                <?php if ($row['status_pembayaran'] == 'pending'): ?>
                                    <form action="kelola_pembayaran.php" method="POST" onsubmit="return confirm('Anda yakin ingin mengonfirmasi pembayaran ini?');">
                                        <input type="hidden" name="pembayaran_id" value="<?= $row['pembayaran_id'] ?>">
                                        <button type="submit" name="konfirmasi" class="bg-green-500 hover:bg-green-600 text-white font-semibold text-xs py-2 px-4 rounded-md transition-colors shadow flex items-center justify-center mx-auto">
                                            <i class="fas fa-check mr-1"></i> Konfirmasi
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-10 text-gray-500">Tidak ada data pembayaran yang perlu dikelola.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
<?php
$conn->close();
?>