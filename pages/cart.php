<?php
session_start();
include '../db_connection.php';
include "../view/header.php";

if (!isset($_SESSION['id'])) {
  die("Silakan login terlebih dahulu.");
}
$pengguna_id = intval($_SESSION['id']);

// Ambil data cart user beserta foto produk dan stock produk
$stmt = $conn->prepare("
    SELECT c.*, p.foto_url, p.stock, p.nama_produk, p.harga 
    FROM cart c
    JOIN produk p ON c.produk_id = p.produk_id
    WHERE c.pengguna_id = ?
    ORDER BY c.created_at DESC
");
$stmt->bind_param("i", $pengguna_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Keranjang Belanja</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
</head>

<body class="bg-gray-100 font-sans text-gray-900">

  <section class="max-w-7xl mx-auto bg-white mt-4 divide-y divide-gray-200 border border-gray-200 rounded-md shadow-lg">
    <h1 class="text-2xl font-semibold p-6">Keranjang Belanja Kamu</h1>

    <?php if ($result->num_rows > 0): ?>
      <?php $grandTotal = 0; ?>
      <?php while ($row = $result->fetch_assoc()):
        $total = $row['harga'] * $row['quantity'];
        $grandTotal += $total;
      ?>
        <article class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition duration-200" data-cart-id="<?= $row['cart_id'] ?>">
          <div class="flex items-center space-x-4">
            <img alt="<?= htmlspecialchars($row['nama_produk']) ?>" class="w-16 h-16 object-cover rounded flex-shrink-0" src="../uploads/<?= htmlspecialchars($row['foto_url']) ?>" />
            <div>
              <h3 class="font-semibold text-sm text-gray-900"><?= htmlspecialchars($row['nama_produk']) ?></h3>
              <p class="text-xs text-gray-500">
                Warna: <?= htmlspecialchars($row['color']) ?> | Ukuran: <?= htmlspecialchars($row['size']) ?>
              </p>
              <p class="text-xs text-gray-500">
                Jumlah: <span><?= $row['quantity'] ?></span>
              </p>
            </div>
          </div>
          <div class="flex items-center space-x-6">
            <div class="font-bold text-gray-900 text-sm subtotal" id="subtotal-<?= $row['cart_id'] ?>">
              Rp <?= number_format($total, 0, ',', '.') ?>
            </div>

            <form method="POST" action="hapus_cart.php" onsubmit="return confirm('Yakin ingin hapus produk ini?');" style="display:inline;">
              <input type="hidden" name="cart_id" value="<?= $row['cart_id'] ?>">
              <button type="submit" class="text-red-600 hover:text-red-800" aria-label="Hapus Produk">
                <i class="fas fa-trash-alt"></i>
              </button>
            </form>
          </div>
        </article>
      <?php endwhile; ?>

      <!-- Total belanja keseluruhan -->
      <div class="flex items-center justify-between px-6 py-3 border-t border-gray-200">
        <div class="text-gray-900 font-semibold text-lg">Total Belanja:</div>
        <div class="text-gray-900 font-bold text-lg" id="grandTotal">
          Rp <?= number_format($grandTotal, 0, ',', '.') ?>
        </div>
      </div>

      <div class="flex justify-end p-6">
        <form action="checkout.php" method="post" class="w-full max-w-xs">
          <button type="submit" class="w-full bg-gray-900 text-white font-semibold text-sm px-6 py-3 rounded hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-600 transition">
            Checkout Sekarang
          </button>
        </form>
      </div>

    <?php else: ?>
      <p class="text-gray-600 text-center p-6">Keranjang kamu kosong.</p>
    <?php endif; ?>
  </section>

</body>

</html>