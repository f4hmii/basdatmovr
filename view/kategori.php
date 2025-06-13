<?php
session_start();
include "header.php";
include '../db_connection.php';
$kategori = isset($_GET['kategori']) ? $_GET['kategori'] : 'semua';

if ($kategori == 'semua') {
    $query = "SELECT * FROM produk";
} else {
    $ambilKategori = mysqli_query($conn, "SELECT kategori_id FROM kategori WHERE nama_kategori = '$kategori'");
    $dataKategori = mysqli_fetch_assoc($ambilKategori);
    $idKategori = $dataKategori['kategori_id'];

    $query = "SELECT * FROM produk WHERE kategori_id = '$idKategori'";
}

$result = mysqli_query($conn, $query);

// Ambil semua data produk ke array supaya bisa pakai foreach
$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Produk - <?= htmlspecialchars($kategori) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Feather Icons untuk icon hati -->
    <script src="https://unpkg.com/feather-icons"></script>
</head>

<body class="bg-gray-100">

    <h1 class="text-3xl font-bold text-center my-8">Produk Kategori: <?= htmlspecialchars($kategori) ?></h1>

    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 p-6" id="product-list">

            <?php foreach ($products as $product): 
                $foto = !empty($product['foto_url']) ? '../uploads/' . htmlspecialchars($product['foto_url']) : 'gambar/default.jpg';
            ?>
                <div
                    class="relative w-full max-w-sm bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    
                    <!-- Icon Love (favorite) -->
                    <form method="POST" action="wishlist/favorite.php" class="absolute top-3 right-3">
                        <input type="hidden" name="produk_id" value="<?= $product['produk_id'] ?>">
                        <button type="submit" class="text-gray-500 hover:text-red-500" aria-label="Add to wishlist">
                            <i data-feather="heart" class="w-5 h-5"></i>
                        </button>
                    </form>

                    <a href="../detail.php?id=<?= $product['produk_id'] ?>">
                        <img class="p-6 rounded-t-lg mx-auto max-h-48 object-contain" src="<?= $foto ?>"
                            alt="<?= htmlspecialchars($product['nama_produk']) ?>" />
                    </a>

                    <div class="px-5 pb-5">
                        <a href="../detail.php?id=<?= $product['produk_id'] ?>">
                            <h5
                                class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white truncate hover:text-blue-600"
                                title="<?= htmlspecialchars($product['nama_produk']) ?>">
                                <?= htmlspecialchars($product['nama_produk']) ?>
                            </h5>
                        </a>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mb-2 truncate whitespace-nowrap overflow-hidden"
                            title="<?= htmlspecialchars($product['deskripsi']) ?>">
                            <?= htmlspecialchars($product['deskripsi']) ?>
                        </p>

                        <div class="flex items-center justify-between mt-4 mb-3">
                            <span
                                class="text-2xl font-bold text-gray-900 dark:text-white">Rp<?= number_format($product['harga'], 0, ',', '.') ?></span>
                            <a href="add_to_cart.php?id=<?= $product['produk_id'] ?>"
                                class="text-white bg-black hover:bg-gray-700 focus:ring-4 focus:ring-gray-500 font-medium rounded-lg text-sm px-4 py-2 dark:focus:ring-gray-500"
                                aria-label="Add <?= htmlspecialchars($product['nama_produk']) ?> to cart">
                                Add to Cart
                            </a>
                        </div>

                        <a href="checkout.php?id=<?= $product['produk_id'] ?>"
                            class="block w-full text-center text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:focus:ring-green-800"
                            aria-label="Checkout <?= htmlspecialchars($product['nama_produk']) ?> sekarang">
                            Checkout Sekarang
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    </div>

    <script>
        // Init feather icons
        feather.replace()
    </script>

</body>

</html>
