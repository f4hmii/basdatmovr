<?php
session_start();
include '../db_connection.php';
include '../view/header.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION['pengguna_id'])) {
    header("Location: login.php");
    exit;
}

$pengguna_id = $_SESSION['pengguna_id'];

// Ambil item dari keranjang
$sql = "
    SELECT c.cart_id, c.produk_id, c.nama_produk, c.harga, c.size, c.color, c.quantity, p.foto_url, pv.stock 
    FROM cart c 
    JOIN produk p ON c.produk_id = p.produk_id
    LEFT JOIN produk_varian pv ON c.produk_id = pv.produk_id AND pv.ukuran_id = (SELECT ukuran_id FROM ukuran WHERE nama_ukuran = c.size) AND pv.warna_id = (SELECT warna_id FROM warna WHERE nama_warna = c.color)
    WHERE c.pengguna_id = ?
    ORDER BY c.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pengguna_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$totalHarga = 0;
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
    $totalHarga += $row['harga'] * $row['quantity'];
}

?>

<div class="container mx-auto mt-10 p-4">
    <h1 class="text-3xl font-bold mb-6">Keranjang Belanja Anda</h1>
    
    <?php if (count($items) > 0): ?>
        <div class="flex flex-col lg:flex-row gap-8">
            <div class="lg:w-3/4">
                <div class="bg-white shadow-md rounded-lg">
                    <div class="hidden lg:flex bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                        <div class="py-3 px-6 w-2/5 text-left">Produk</div>
                        <div class="py-3 px-6 w-1/5 text-center">Ukuran & Warna</div>
                        <div class="py-3 px-6 w-1/5 text-center">Kuantitas</div>
                        <div class="py-3 px-6 w-1/5 text-right">Subtotal</div>
                    </div>
                    <?php foreach ($items as $item): ?>
                        <div class="flex flex-col lg:flex-row items-center border-b border-gray-200 py-4 px-6">
                            <div class="flex items-center w-full lg:w-2/5 mb-4 lg:mb-0">
                                <img class="h-24 w-24 object-cover rounded mr-4" src="../uploads/<?= htmlspecialchars($item['foto_url']) ?>" alt="<?= htmlspecialchars($item['nama_produk']) ?>">
                                <div>
                                    <p class="font-semibold text-lg"><?= htmlspecialchars($item['nama_produk']) ?></p>
                                    <p class="text-gray-600">Rp <?= htmlspecialchars(number_format($item['harga'], 0, ',', '.')) ?></p>
                                    <a href="hapus_cart.php?cart_id=<?= $item['cart_id'] ?>" class="text-red-500 hover:text-red-700 text-sm mt-2 inline-block" onclick="return confirm('Yakin ingin menghapus item ini dari keranjang?')">Hapus</a>
                                </div>
                            </div>
                            <div class="w-full lg:w-1/5 text-center mb-2 lg:mb-0">
                                <p><span class="font-semibold">Ukuran:</span> <?= htmlspecialchars($item['size']) ?></p>
                                <p><span class="font-semibold">Warna:</span> <?= htmlspecialchars($item['color']) ?></p>
                            </div>
                            <div class="w-full lg:w-1/5 text-center mb-2 lg:mb-0">
                                <span class="px-3 py-1"><?= htmlspecialchars($item['quantity']) ?></span>
                            </div>
                            <div class="w-full lg:w-1/5 text-right font-semibold">
                                Rp <?= htmlspecialchars(number_format($item['harga'] * $item['quantity'], 0, ',', '.')) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="lg:w-1/4">
                <div class="bg-white shadow-md rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-4">Ringkasan Pesanan</h2>
                    <div class="flex justify-between mb-2">
                        <span>Subtotal</span>
                        <span>Rp <?= htmlspecialchars(number_format($totalHarga, 0, ',', '.')) ?></span>
                    </div>
                    <div class="flex justify-between mb-4">
                        <span>Ongkos Kirim</span>
                        <span class="text-green-500">Gratis</span>
                    </div>
                    <hr class="my-4">
                    <div class="flex justify-between font-bold text-lg">
                        <span>Total</span>
                        <span>Rp <?= htmlspecialchars(number_format($totalHarga, 0, ',', '.')) ?></span>
                    </div>
                    <a href="checkout.php">
                        <button class="w-full bg-indigo-600 text-white font-bold py-3 px-4 rounded-lg mt-6 hover:bg-indigo-700 transition-colors">
                            Lanjutkan ke Checkout
                        </button>
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white shadow-md rounded-lg p-10 text-center">
            <h2 class="text-2xl font-semibold mb-4">Keranjang Anda Kosong</h2>
            <p class="text-gray-600 mb-6">Sepertinya Anda belum menambahkan produk apapun ke keranjang.</p>
            <a href="../index.php" class="bg-indigo-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-indigo-700 transition-colors">
                Mulai Belanja
            </a>
        </div>
    <?php endif; ?>

</div>

<?php
include '../view/footer.php';
$stmt->close();
$conn->close();
?>