<?php
session_start();
include '../db_connection.php';
include "../view/header.php";

// FIX 1: Pengecekan session menggunakan 'pengguna_id'
if (!isset($_SESSION['pengguna_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../pages/login.php');
    exit;
}
$seller_id = $_SESSION['pengguna_id'];

// FIX 2: Menggunakan prepared statement untuk keamanan
// FIX 3: Menambahkan kolom 'verified' untuk ditampilkan
$stmt = $conn->prepare("SELECT produk_id, nama_produk, deskripsi, harga, stock, foto_url, verified FROM produk WHERE seller_id = ? ORDER BY produk_id DESC");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <title>Data Produk Saya</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">Produk Saya</h1>
    <a href="tambah.php" class="inline-block mb-8 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition">Tambah Produk Baru</a>

    <?php if ($result->num_rows === 0): ?>
        <p class="text-gray-600">Anda belum memiliki produk. Silakan tambahkan produk baru.</p>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden flex flex-col">
                    <img src="../uploads/<?= htmlspecialchars($row['foto_url']) ?>" alt="<?= htmlspecialchars($row['nama_produk']) ?>" class="w-full h-48 object-cover" />
                    
                    <div class="p-4 flex flex-col flex-grow">
                        <div class="flex justify-between items-start mb-2">
                            <h2 class="font-bold text-lg text-gray-800 truncate pr-2" title="<?= htmlspecialchars($row['nama_produk']) ?>"><?= htmlspecialchars($row['nama_produk']) ?></h2>
                            
                            <?php
                                $status_text = 'Menunggu';
                                $status_color = 'bg-yellow-400 text-yellow-800';
                                if ($row['verified'] == 1) {
                                    $status_text = 'Disetujui';
                                    $status_color = 'bg-green-400 text-green-800';
                                } elseif ($row['verified'] == -1) {
                                    $status_text = 'Ditolak';
                                    $status_color = 'bg-red-400 text-red-800';
                                }
                            ?>
                            <span class="text-xs font-semibold px-2 py-1 rounded-full <?= $status_color ?>"><?= $status_text ?></span>
                        </div>

                        <p class="text-sm text-gray-600 mb-4 line-clamp-3 flex-grow"><?= htmlspecialchars($row['deskripsi']) ?></p>

                        <div class="mb-2">
                            <strong class="text-gray-700">Harga:</strong>
                            <span class="text-lg font-semibold text-green-600">Rp <?= number_format($row['harga'], 0, ',', '.') ?></span>
                        </div>

                        <div class="mb-4">
                            <strong class="text-gray-700">Total Stok:</strong>
                            <span class="font-medium"><?= intval($row['stock']) ?></span>
                        </div>
                        
                        <div class="mt-auto flex justify-between pt-4 border-t">
                            <a href="edit.php?produk_id=<?= $row['produk_id'] ?>" class="px-4 py-2 bg-yellow-400 rounded-md hover:bg-yellow-500 text-black text-sm font-semibold transition">Edit</a>
                            <a href="hapus.php?produk_id=<?= $row['produk_id'] ?>" onclick="return confirm('Yakin mau hapus produk ini?')" class="px-4 py-2 bg-red-600 rounded-md hover:bg-red-700 text-white text-sm font-semibold transition">Hapus</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>
<?php
$stmt->close();
$conn->close();
include "../view/footer.php";
?>
</body>
</html>