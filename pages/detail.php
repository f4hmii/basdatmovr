<?php
include "../view/header.php";
include '../db_connection.php';

// Ambil ID produk dari URL
if (!isset($_GET['id'])) {
    echo "Produk tidak ditemukan.";
    exit;
}

$produk_id = intval($_GET['id']);

// Ambil data produk dari database
$stmt = $conn->prepare("SELECT * FROM produk WHERE produk_id = ?");
$stmt->bind_param("i", $produk_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    echo "Produk tidak ditemukan.";
    exit;
}

// Assign data produk
$product_name = $product['nama_produk'];
$price = $product['harga'];
$stock = $product['stock'];
$deskripsi = $product['deskripsi'];
$gambarUtama = $product['foto_url'];
$kondisi = $product['kondisi'] ?? '';

// stok warna dan ukuran dari database (stok terbaru)
$color_stock = json_decode($product['color_stock'], true) ?: [];
$size_stock = json_decode($product['size_stock'], true) ?: [];

// Ambil gambar detail dari tabel produk_foto_detail
$gambarLain = [];
$stmt2 = $conn->prepare("SELECT foto_path FROM produk_foto_detail WHERE produk_id = ?");
$stmt2->bind_param("i", $produk_id);
$stmt2->execute();
$result2 = $stmt2->get_result();
while ($row = $result2->fetch_assoc()) {
    $gambarLain[] = $row['foto_path'];
}
$stmt2->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product_name) ?> - Detail Produk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .thumbnail {
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color 0.3s;
        }

        .thumbnail:hover {
            border-color: #4A90E2;
        }

        .thumbnail.active {
            border-color: #1D4ED8;
        }
    </style>
</head>

<body class="bg-gray-50">

    <div class="max-w-6xl mx-auto p-6 grid grid-cols-1 md:grid-cols-2 gap-10">
        <!-- Gambar Produk -->
        <div>
            <img id="gambarUtama" class="w-full rounded-lg shadow-md mb-4"
                src="../uploads/<?= htmlspecialchars($gambarUtama) ?>"
                alt="Gambar Utama"
                onerror="this.onerror=null; this.src='../uploads/image-not-found.png';" />

            <div class="grid grid-cols-4 gap-4">
                <?php if (!empty($gambarUtama)): ?>
                    <img src="../uploads/<?= htmlspecialchars($gambarUtama) ?>"
                        class="thumbnail active rounded-lg shadow-md"
                        onclick="changeImage(this)" alt="Thumbnail Utama" />
                <?php endif; ?>

                <?php foreach ($gambarLain as $gambar): ?>
                    <img src="../uploads/<?= htmlspecialchars($gambar) ?>"
                        class="thumbnail rounded-lg shadow-md"
                        onclick="changeImage(this)" alt="Thumbnail Detail" />
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Informasi Produk -->
        <div>
            <h2 class="text-4xl font-bold mb-2"><?= htmlspecialchars($product_name) ?></h2>

            <p class="text-lg text-gray-700 mb-1"><strong>Kondisi:</strong> <?= htmlspecialchars($kondisi) ?></p>

            <p class="text-3xl font-bold text-red-500 mb-4">Rp <?= number_format($price, 0, ',', '.') ?></p>

            <!-- Pilihan Warna -->
            <div class="mb-4">
                <label for="colorSelect" class="block font-semibold mb-1">Warna:</label>
                <select id="colorSelect" name="color" required
                    class="w-48 border border-gray-300 rounded px-3 py-2">
                    <option value="">-- Pilih Warna --</option>
                    <?php foreach ($color_stock as $color => $stok): ?>
                        <option value="<?= htmlspecialchars($color) ?>" <?= ($stok <= 0) ? 'disabled' : '' ?>>
                            <?= htmlspecialchars($color) ?> (Stok: <?= intval($stok) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Pilihan Ukuran -->
            <div class="mb-4">
                <label for="sizeSelect" class="block font-semibold mb-1">Ukuran:</label>
                <select id="sizeSelect" name="size" required
                    class="w-48 border border-gray-300 rounded px-3 py-2">
                    <option value="">-- Pilih Ukuran --</option>
                    <?php foreach ($size_stock as $size => $stok): ?>
                        <option value="<?= htmlspecialchars($size) ?>" <?= ($stok <= 0) ? 'disabled' : '' ?>>
                            <?= htmlspecialchars($size) ?> (Stok: <?= intval($stok) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Stok Keseluruhan dan Quantity -->
            <div class="mb-4">
                <label class="block text-gray-700 mb-1">Stok: <span id="stock"><?= $stock ?></span></label>
                <input type="number" id="quantity" name="quantity" min="1" max="<?= $stock ?>" value="1"
                    class="w-24 border px-2 py-1 rounded-md" />
            </div>

            <!-- Form Tambah ke Keranjang -->
            <form action="add_to_cart.php" method="post" class="mt-4" id="addToCartForm">
                <input type="hidden" name="produk_id" value="<?= $produk_id ?>">
                <input type="hidden" name="nama_produk" value="<?= htmlspecialchars($product_name) ?>">
                <input type="hidden" name="harga" value="<?= $price ?>">
                <input type="hidden" name="size" id="sizeInput" required>
                <input type="hidden" name="color" id="colorInput" required>
                <input type="hidden" name="quantity" id="hiddenQuantity" value="1" required>
                <button type="submit"
                    class="mt-4 bg-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition">
                    Add to Cart
                </button>
            </form>

            <!-- Deskripsi Produk -->
            <div class="mt-8">
                <h3 class="text-2xl font-semibold mb-2">Deskripsi Produk</h3>
                <p class="text-gray-700 leading-relaxed"><?= nl2br(htmlspecialchars($deskripsi)) ?></p>
            </div>
        </div>
    </div>

    <footer class="bg-gray-800 text-white mt-12">
        <div class="container mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="py-8">
                <h3 class="text-lg font-semibold mb-4">About Us</h3>
                <p class="text-gray-400">We are a leading sportswear brand committed to providing high-quality
                    products
                    for athletes and fitness enthusiasts.</p>
                <div class="mt-4">
                    <a class="text-gray-400 hover:text-white" href="#"><i class="fab fa-facebook-f"></i></a>
                    <a class="ml-4 text-gray-400 hover:text-white" href="#"><i class="fab fa-twitter"></i></a>
                    <a class="ml-4 text-gray-400 hover:text-white" href="#"><i class="fab fa-instagram"></i></a>
                    <a class="ml-4 text-gray-400 hover:text-white" href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="py-8">
                <h3 class="text-lg font-semibold mb-4">Customer Service</h3>
                <ul class="text-gray-400">
                    <li class="mb-2"><a class="hover:text-white" href="#">Contact Us</a></li>
                    <li class="mb-2"><a class="hover:text-white" href="#">Order Tracking</a></li>
                    <li class="mb-2"><a class="hover:text-white" href="#">Returns & Exchanges</a></li>
                    <li class="mb-2"><a class="hover:text-white" href="#">Shipping & Delivery</a></li>
                    <li class="mb-2"><a class="hover:text-white" href="#">FAQs</a></li>
                </ul>
            </div>
            <div class="py-8">
                <h3 class="text-lg font-semibold mb-4">Newsletter</h3>
                <p class="text-gray-400">Subscribe to get the latest information on new products and upcoming sales.
                </p>
                <form class="mt-4">
                    <input class="w-full p-2 rounded-lg text-gray-900" placeholder="Enter your email" type="email" />
                    <button class="mt-2 w-full bg-red-600 p-2 rounded-lg hover:bg-red-700"
                        type="submit">Subscribe</button>
                </form>
            </div>
            <div class="mt-8 text-center text-gray-400">
                <p>©️ 2023 Movr. All rights reserved.</p>
            </div>
    </footer>

    <script>
        function changeImage(element) {
            document.getElementById('gambarUtama').src = element.src;
            document.querySelectorAll('.thumbnail').forEach(img => img.classList.remove('active'));
            element.classList.add('active');
        }

        const form = document.getElementById('addToCartForm');
        const sizeSelect = document.getElementById('sizeSelect');
        const colorSelect = document.getElementById('colorSelect');
        const sizeInput = document.getElementById('sizeInput');
        const colorInput = document.getElementById('colorInput');
        const quantityInput = document.getElementById('quantity');
        const hiddenQuantity = document.getElementById('hiddenQuantity');

        sizeSelect.addEventListener('change', () => {
            sizeInput.value = sizeSelect.value;
        });
        colorSelect.addEventListener('change', () => {
            colorInput.value = colorSelect.value;
        });
        quantityInput.addEventListener('input', () => {
            hiddenQuantity.value = quantityInput.value;
        });

        form.addEventListener('submit', e => {
            if (!sizeInput.value || !colorInput.value || !hiddenQuantity.value) {
                e.preventDefault();
                alert('Mohon pilih warna, ukuran, dan jumlah sebelum menambahkan ke keranjang.');
            }
        });
    </script>

</body>

</html>