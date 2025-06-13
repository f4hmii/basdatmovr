<?php
session_start();
include '../db_connection.php';
include '../view/header.php';

// Ambil ID produk dari URL
if (!isset($_GET['id'])) {
    echo "<p>Produk tidak ditemukan.</p>";
    exit;
}
$produk_id = intval($_GET['id']);

// Ambil detail produk
$stmt = $conn->prepare("
    SELECT p.*, s.nama_toko, k.nama_kategori 
    FROM produk p 
    LEFT JOIN seller s ON p.seller_id = s.pengguna_id 
    JOIN kategori k ON p.kategori_id = k.kategori_id 
    WHERE p.produk_id = ?
");
$stmt->bind_param("i", $produk_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    echo "<p>Produk tidak ditemukan.</p>";
    exit;
}

$kondisi = $product['kondisi'] ?? '';

// Ambil foto-foto detail produk
$stmt_fotos = $conn->prepare("SELECT foto_path FROM produk_foto_detail WHERE produk_id = ?");
$stmt_fotos->bind_param("i", $produk_id);
$stmt_fotos->execute();
$result_fotos = $stmt_fotos->get_result();
$detail_fotos = [];
while ($row_foto = $result_fotos->fetch_assoc()) {
    $detail_fotos[] = $row_foto['foto_path'];
}
$stmt_fotos->close();

// Ambil varian (warna, ukuran, stok) dari tabel produk_varian
$variants = [];
$stmt_variants = $conn->prepare("
    SELECT pv.stock, w.nama_warna, u.nama_ukuran
    FROM produk_varian pv
    JOIN warna w ON pv.warna_id = w.warna_id
    JOIN ukuran u ON pv.ukuran_id = u.ukuran_id
    WHERE pv.produk_id = ? AND pv.stock > 0
");
$stmt_variants->bind_param("i", $produk_id);
$stmt_variants->execute();
$result_variants = $stmt_variants->get_result();

$available_colors = [];
$available_sizes = [];
$variant_stock = [];

while ($variant = $result_variants->fetch_assoc()) {
    $color = $variant['nama_warna'];
    $size = $variant['nama_ukuran'];
    $stock = $variant['stock'];

    if (!in_array($color, $available_colors)) {
        $available_colors[] = $color;
    }
    if (!in_array($size, $available_sizes)) {
        $available_sizes[] = $size;
    }
    // Buat map untuk cek stok saat user memilih
    $variant_stock[$size][$color] = $stock;
}
$stmt_variants->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['nama_produk']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .radio-btn-label {
            border: 2px solid #e2e8f0;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }
        .radio-btn:checked + .radio-btn-label {
            border-color: #4f46e5;
            background-color: #e0e7ff;
            color: #4f46e5;
        }
        .disabled-label {
            cursor: not-allowed;
            background-color: #f7fafc;
            color: #a0aec0;
            border-color: #e2e8f0;
        }
    </style>
</head>
<body class="bg-gray-100">

<div class="container mx-auto mt-10 p-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div>
            <div class="bg-white rounded-lg shadow-lg p-4">
                <img id="mainImage" class="w-full h-96 object-cover rounded-lg" src="../uploads/<?= htmlspecialchars($product['foto_url']) ?>" alt="Main Product Image">
            </div>
            <div class="flex space-x-2 mt-4">
                <img class="w-20 h-20 object-cover rounded-md cursor-pointer border-2 border-indigo-500" src="../uploads/<?= htmlspecialchars($product['foto_url']) ?>" onclick="changeImage(this.src)">
                <?php foreach ($detail_fotos as $foto): ?>
                <img class="w-20 h-20 object-cover rounded-md cursor-pointer border-2" src="../uploads/<?= htmlspecialchars($foto) ?>" onclick="changeImage(this.src)">
                <?php endforeach; ?>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-3xl font-bold text-gray-800"><?= htmlspecialchars($product['nama_produk']) ?></h1>
            <p class="text-sm text-gray-500 mt-2">Kategori: <a href="#" class="text-indigo-600"><?= htmlspecialchars($product['nama_kategori']) ?></a></p>
            <p class="text-sm text-gray-500">Toko: <a href="#" class="text-indigo-600"><?= htmlspecialchars($product['nama_toko'] ?? 'N/A') ?></a></p>
            
            <div class="mt-4 flex items-center">
                <span class="text-3xl font-bold text-indigo-600">Rp <?= htmlspecialchars(number_format($product['harga'], 0, ',', '.')) ?></span>
                <span class="ml-4 px-2 py-1 bg-gray-200 text-gray-800 text-sm font-semibold rounded"><?= htmlspecialchars($kondisi) ?></span>
            </div>
            
            <form id="addToCartForm" class="mt-6">
                <input type="hidden" name="produk_id" value="<?= $product['produk_id'] ?>">
                <input type="hidden" name="nama_produk" value="<?= htmlspecialchars($product['nama_produk']) ?>">
                <input type="hidden" name="harga" value="<?= $product['harga'] ?>">

                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Warna</h3>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($available_colors as $color): ?>
                            <input type="radio" name="color" id="color-<?= htmlspecialchars($color) ?>" value="<?= htmlspecialchars($color) ?>" class="hidden radio-btn">
                            <label for="color-<?= htmlspecialchars($color) ?>" class="radio-btn-label"><?= htmlspecialchars($color) ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Ukuran</h3>
                    <div class="flex flex-wrap gap-2">
                         <?php foreach ($available_sizes as $size): ?>
                            <input type="radio" name="size" id="size-<?= htmlspecialchars($size) ?>" value="<?= htmlspecialchars($size) ?>" class="hidden radio-btn">
                            <label for="size-<?= htmlspecialchars($size) ?>" class="radio-btn-label"><?= htmlspecialchars($size) ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Kuantitas</h3>
                    <div class="flex items-center">
                        <button type="button" id="decrement" class="bg-gray-300 text-gray-700 rounded-l-md px-4 py-2 hover:bg-gray-400">-</button>
                        <input type="number" name="quantity" id="quantity" class="w-16 text-center border-t border-b" value="1" min="1">
                        <button type="button" id="increment" class="bg-gray-300 text-gray-700 rounded-r-md px-4 py-2 hover:bg-gray-400">+</button>
                    </div>
                    <p id="stockInfo" class="text-sm text-gray-500 mt-2">Pilih warna dan ukuran untuk melihat stok.</p>
                </div>

                <div class="flex space-x-4">
                    <button type="submit" class="flex-1 bg-indigo-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-indigo-700 transition-colors">
                        Tambah ke Keranjang
                    </button>
                </div>
            </form>
            
            <div class="mt-8">
                <h3 class="text-xl font-bold text-gray-800 border-b pb-2 mb-4">Deskripsi Produk</h3>
                <p class="text-gray-600 whitespace-pre-wrap"><?= nl2br(htmlspecialchars($product['deskripsi'])) ?></p>
            </div>
        </div>
    </div>
</div>

<div id="toast-success" class="hidden fixed top-5 right-5 flex items-center w-full max-w-xs p-4 mb-4 text-gray-500 bg-white rounded-lg shadow" role="alert">
    <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
    </div>
    <div id="toast-message" class="ml-3 text-sm font-normal">Item ditambahkan.</div>
</div>

<script>
    function changeImage(src) {
        document.getElementById('mainImage').src = src;
        // Optional: Highlight thumbnail
        document.querySelectorAll('.flex.space-x-2.mt-4 img').forEach(img => {
            img.classList.remove('border-indigo-500');
            if (img.src === src) {
                img.classList.add('border-indigo-500');
            }
        });
    }

    const variantStock = <?= json_encode($variant_stock) ?>;
    const colorRadios = document.querySelectorAll('input[name="color"]');
    const sizeRadios = document.querySelectorAll('input[name="size"]');
    const stockInfo = document.getElementById('stockInfo');
    const quantityInput = document.getElementById('quantity');
    const incrementBtn = document.getElementById('increment');
    const decrementBtn = document.getElementById('decrement');

    function updateStockInfo() {
        const selectedColor = document.querySelector('input[name="color"]:checked');
        const selectedSize = document.querySelector('input[name="size"]:checked');
        
        if (selectedColor && selectedSize) {
            const color = selectedColor.value;
            const size = selectedSize.value;
            const stock = variantStock[size] && variantStock[size][color] ? variantStock[size][color] : 0;
            
            stockInfo.textContent = `Stok tersedia: ${stock}`;
            quantityInput.max = stock;
            if (quantityInput.value > stock) {
                quantityInput.value = stock > 0 ? stock : 1;
            }
        } else {
            stockInfo.textContent = 'Pilih warna dan ukuran untuk melihat stok.';
        }
    }
    
    colorRadios.forEach(radio => radio.addEventListener('change', updateStockInfo));
    sizeRadios.forEach(radio => radio.addEventListener('change', updateStockInfo));

    incrementBtn.addEventListener('click', () => {
        let currentVal = parseInt(quantityInput.value);
        let maxVal = parseInt(quantityInput.max) || 1;
        if (currentVal < maxVal) {
            quantityInput.value = currentVal + 1;
        }
    });

    decrementBtn.addEventListener('click', () => {
        let currentVal = parseInt(quantityInput.value);
        if (currentVal > 1) {
            quantityInput.value = currentVal - 1;
        }
    });

    document.getElementById('addToCartForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('add_to_cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const toast = document.getElementById('toast-success');
            const messageDiv = document.getElementById('toast-message');
            messageDiv.textContent = data.message;
            toast.classList.remove('hidden');
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 3000);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan, silakan coba lagi.');
        });
    });

</script>

</body>
</html>

<?php
include '../view/footer.php';
$conn->close();
?>