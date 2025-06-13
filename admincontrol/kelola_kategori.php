<?php
// File: admincontrol/kelola_kategori.php
session_start();
include '../db_connection.php';

if (!isset($_SESSION['pengguna_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403); die("Akses ditolak.");
}

$error_msg = '';
$success_msg = '';

// Logika untuk Tambah Kategori
if (isset($_POST['tambah'])) {
    $nama_kategori = htmlspecialchars(trim($_POST['nama_kategori']));
    if (!empty($nama_kategori)) {
        $stmt_check = $conn->prepare("SELECT kategori_id FROM kategori WHERE nama_kategori = ?");
        $stmt_check->bind_param("s", $nama_kategori);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows > 0) {
            $error_msg = "Kategori dengan nama tersebut sudah ada.";
        } else {
            $stmt_insert = $conn->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
            $stmt_insert->bind_param("s", $nama_kategori);
            if ($stmt_insert->execute()) {
                $success_msg = "Kategori baru berhasil ditambahkan.";
            } else {
                $error_msg = "Gagal menambah kategori ke database.";
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    } else {
        $error_msg = "Nama kategori tidak boleh kosong.";
    }
}

// Logika untuk Hapus Kategori
if (isset($_GET['hapus_id'])) {
    $id_hapus = intval($_GET['hapus_id']);
    
   
    $stmt_hapus = $conn->prepare("DELETE FROM kategori WHERE kategori_id = ?");
    $stmt_hapus->bind_param("i", $id_hapus);
    if ($stmt_hapus->execute()) {
        $success_msg = "Kategori berhasil dihapus.";
    } else {
        $error_msg = "Gagal menghapus kategori.";
    }
    $stmt_hapus->close();
}

// Ambil semua kategori untuk ditampilkan
$result = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

<div class="container mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            <i class="fas fa-tags mr-2"></i>Kelola Kategori
        </h1>
        <a href="dashbord_admin.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex items-center transition-colors shadow-md">
            <i class="fas fa-arrow-left mr-2"></i>
            Kembali ke Dashboard
        </a>
    </div>

    <?php if ($success_msg): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p><?= $success_msg ?></p>
        </div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p><?= $error_msg ?></p>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-lg shadow-lg h-full">
                <h2 class="text-xl font-bold mb-4 text-gray-800">Tambah Kategori Baru</h2>
                <form method="POST" action="dashbord_admin.php#kelola_kategori">
                    <div class="mb-4">
                        <label for="nama_kategori" class="block text-gray-700 text-sm font-bold mb-2">Nama Kategori</label>
                        <input type="text" name="nama_kategori" id="nama_kategori" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <button type="submit" name="tambah" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline transition-colors">
                        <i class="fas fa-plus mr-2"></i>Tambah Kategori
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-xl font-bold mb-4 text-gray-800">Daftar Kategori</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr class="bg-gray-800 text-white uppercase text-sm">
                                <th class="px-5 py-3 border-b-2 border-gray-200 text-left">ID</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 text-left">Nama Kategori</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700">
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 border-b">
                                    <td class="px-5 py-4 font-bold"><?= $row['kategori_id'] ?></td>
                                    <td class="px-5 py-4"><?= htmlspecialchars($row['nama_kategori']) ?></td>
                                    <td class="px-5 py-4 text-center">
                                        <a href="dashbord_admin.php?page=kelola_kategori.php&hapus_id=<?= $row['kategori_id'] ?>" 
                                           class="bg-red-600 hover:bg-red-700 text-white font-semibold text-xs py-1 px-3 rounded-md transition-colors shadow" 
                                           onclick="return confirm('Anda yakin ingin menghapus kategori \'<?= htmlspecialchars($row['nama_kategori']) ?>\'?')">
                                           Hapus
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center py-6">Belum ada kategori.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>