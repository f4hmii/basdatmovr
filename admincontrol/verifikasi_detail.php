<?php
// File: admincontrol/verifikasi_detail.php
session_start();
include '../db_connection.php';

if (!isset($_SESSION['pengguna_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403); die("Akses ditolak.");
}
$produk_id = intval($_GET['produk_id'] ?? 0);
if ($produk_id <= 0) die("ID produk tidak valid.");

// Ambil data produk utama
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
$produk = $result_produk->fetch_assoc();
$stmt_produk->close();

// Ambil foto-foto detail
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
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Verifikasi Produk #<?= $produk['produk_id'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .thumbnail {
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color 0.2s;
        }
        .thumbnail:hover, .thumbnail.active {
            border-color: #4f46e5; /* indigo-600 */
        }
    </style>
</head>
<body class="bg-gray-100">

<div class="container mx-auto p-6">
    <div class="bg-white p-8 rounded-lg shadow-lg">
        
        <div class="flex justify-between items-center pb-4 border-b">
            <h1 class="text-2xl font-bold text-gray-800">Detail & Verifikasi Produk #<?= $produk['produk_id'] ?></h1>
            <a href="#" class="sidebar-link bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg flex items-center transition-colors text-sm" data-page="verifikasi_produk.php">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 mt-6">
            
            <div class="lg:col-span-2">
                <div class="mb-4">
                    <img id="main-image" src="../uploads/<?= htmlspecialchars($produk['foto_url']) ?>" alt="Foto Utama" class="w-full h-auto object-cover rounded-lg border shadow-md">
                </div>
                <div class="grid grid-cols-4 gap-2">
                    <div>
                        <img src="../uploads/<?= htmlspecialchars($produk['foto_url']) ?>" alt="Thumbnail 1" class="thumbnail active w-full h-24 object-cover rounded-md border-2" onclick="changeMainImage(this)">
                    </div>
                    <?php foreach($foto_detail_list as $foto): ?>
                    <div>
                        <img src="../uploads/<?= htmlspecialchars($foto) ?>" alt="Thumbnail Detail" class="thumbnail w-full h-24 object-cover rounded-md" onclick="changeMainImage(this)">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="lg:col-span-3">
                <h2 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($produk['nama_produk']) ?></h2>
                <div class="flex items-center space-x-4 mt-2 mb-4">
                    <span class="text-sm text-gray-500">Toko: <span class="font-semibold text-gray-700"><?= htmlspecialchars($produk['nama_toko'] ?? 'N/A') ?></span></span>
                    <span class="text-sm text-gray-500">Kategori: <span class="font-semibold text-gray-700"><?= htmlspecialchars($produk['nama_kategori'] ?? 'N/A') ?></span></span>
                    <span class="text-sm font-semibold px-2 py-1 bg-blue-100 text-blue-800 rounded-full"><?= htmlspecialchars($produk['kondisi']) ?></span>
                </div>
                
                <p class="text-3xl font-bold text-indigo-600 mb-4">Rp <?= number_format($produk['harga'], 0, ',', '.') ?></p>

                <div class="border-t pt-4">
                    <h4 class="font-bold text-lg mb-2 text-gray-800">Deskripsi</h4>
                    <p class="text-gray-600 whitespace-pre-wrap"><?= htmlspecialchars($produk['deskripsi']) ?></p>
                </div>
                
                <div class="mt-4 border-t pt-4">
                    <h4 class="font-bold text-lg mb-2 text-gray-800">Stok & Varian</h4>
                    <p class="text-gray-600">Stok Total: <span class="font-bold text-xl"><?= $produk['stock'] ?></span> unit</p>
                    </div>
            </div>
        </div>
        
        <div class="mt-8 pt-6 border-t">
            <?php if ($produk['verified'] == 0): ?>
                <div class="flex justify-end space-x-4">
                    <a href="verifikasi_aksi.php?produk_id=<?= $produk['produk_id'] ?>&aksi=tolak" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-lg transition-colors shadow-md flex items-center" onclick="return confirm('Anda yakin ingin MENOLAK produk ini?')">
                        <i class="fas fa-times mr-2"></i> Tolak
                    </a>
                    <a href="verifikasi_aksi.php?produk_id=<?= $produk['produk_id'] ?>&aksi=setuju" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg transition-colors shadow-md flex items-center" onclick="return confirm('Anda yakin ingin MENYETUJUI produk ini?')">
                        <i class="fas fa-check mr-2"></i> Setujui
                    </a>
                </div>
            <?php else: ?>
                <?php
                    $status_text = 'Produk telah Disetujui';
                    $status_class = 'bg-green-100 text-green-800';
                    if ($produk['verified'] == -1) {
                        $status_text = 'Produk telah Ditolak';
                        $status_class = 'bg-red-100 text-red-800';
                    }
                ?>
                <div class="p-4 rounded-md <?= $status_class ?> text-center font-semibold">
                    <?= $status_text ?> pada <?= date('d M Y', strtotime($produk['penyimpanan_waktu_data'])) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function changeMainImage(thumbnailElement) {
        // Ganti gambar utama
        document.getElementById('main-image').src = thumbnailElement.src;

        // Atur style border untuk thumbnail
        document.querySelectorAll('.thumbnail').forEach(el => el.classList.remove('active'));
        thumbnailElement.classList.add('active');
    }
</script>

</body>
</html>