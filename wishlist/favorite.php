<?php
session_start();
require_once '../db_connection.php';

if (!isset($_SESSION['pengguna_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

$pengguna_id = $_SESSION['pengguna_id'];
$produk_id = $_POST['produk_id'] ?? null;

if ($produk_id && is_numeric($produk_id)) {
    $check = $conn->prepare("SELECT * FROM favorit WHERE pengguna_id = ? AND produk_id = ?");
    $check->bind_param("ii", $pengguna_id, $produk_id);
    $check->execute();
    $checkResult = $check->get_result();

    if ($checkResult->num_rows === 0) {
        $insert = $conn->prepare("INSERT INTO favorit (pengguna_id, produk_id, quantity) VALUES (?, ?, 1)");
        $insert->bind_param("ii", $pengguna_id, $produk_id);
        $insert->execute();
    }
}

$query = $conn->prepare("
    SELECT p.produk_id, p.nama_produk, p.harga, p.foto_url, p.deskripsi
    FROM favorit f
    JOIN produk p ON f.produk_id = p.produk_id
    WHERE f.pengguna_id = ?
");
$query->bind_param("i", $pengguna_id);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Produk Favorit</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        .line-clamp-2 {
            overflow: hidden;
            display: -webkit-box;
            display: box;
            -webkit-box-orient: vertical;
            box-orient: vertical;
            -webkit-line-clamp: 2;
            line-clamp: 2;
        }
    </style>
</head>

<body class="bg-gray-100 p-6">
    <h1 class="text-3xl font-bold mb-6">Produk Favorit Anda</h1>

    <div class="mt-6 mb-6">
        <a href="../index.php" class="inline-block px-4 py-2 bg-black text-white rounded hover:bg-gray-500">
            Kembali ke Beranda
        </a>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php while ($product = $result->fetch_assoc()): ?>
                <div class="relative w-full max-w-sm bg-gray-800 border border-gray-200 rounded-lg shadow-sm">
                    <a href="../detail.php?id=<?= $product['produk_id'] ?>">
                        <img class="p-6 rounded-t-lg mx-auto max-h-48 object-contain"
                            src="../uploads/<?= htmlspecialchars($product['foto_url']) ?>"
                            alt="<?= htmlspecialchars($product['nama_produk']) ?>"
                            onerror="this.onerror=null; this.src='../uploads/image-not-found.png';">
                    </a>

                    <div class="px-5 pb-5">
                        <a href="../detail.php?id=<?= $product['produk_id'] ?>">
                            <h5 class="text-xl font-semibold tracking-tight text-white"><?= $product['nama_produk'] ?></h5>
                        </a>
                        <p class="text-sm text-gray-400 mt-1 mb-2 line-clamp-2"><?= $product['deskripsi'] ?></p>

                        <div class="flex items-center justify-between mt-4 mb-3">
                            <span class="text-2xl font-bold text-white">Rp<?= number_format($product['harga'], 0, ',', '.') ?></span>
                        </div>

                        <div class="flex flex-col gap-2">
                            <form action="delete_favorite.php" method="POST" onsubmit="return confirm('Yakin ingin menghapus produk ini dari favorit?');">
                                <input type="hidden" name="produk_id" value="<?= $product['produk_id'] ?>">
                                <button type="submit"
                                    class="w-full text-center text-white bg-red-600 hover:bg-red-700 font-medium rounded-lg text-sm px-5 py-2.5">
                                    Hapus dari Favorit
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="text-gray-600 text-lg">Belum ada produk favorit.</p>
    <?php endif; ?>

    <script>
        feather.replace();
    </script>
</body>

</html>