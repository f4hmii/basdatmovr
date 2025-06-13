<?php
session_start();
if (isset($_SESSION['role'])) {
  if ($_SESSION['role'] === 'admin') {
    // Kalau admin coba akses index.php, langsung redirect ke dashboard admin
    header("Location: admincontrol/dashbord_admin.php");
    exit();
  }
  // buyer dan seller tetap bisa lanjut akses index.php
}
include "view/header.php";
include 'db_connection.php';



// Ambil data dari tabel produk yang sudah diverifikasi oleh admin
$query = "SELECT * FROM produk WHERE verified = 1";
$result = $conn->query($query);

// Gabungkan produk dengan ukuran (size)
$products = [];
while ($row = $result->fetch_assoc()) {
  $products[] = $row;
}

?>

<html>

<head>
  <title>
    Web Page
  </title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&amp;display=swap" rel="stylesheet" />
  <script src="https://unpkg.com/feather-icons"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"   />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="index.css">

</head>

<body>

  <body>


    <div class="carousel">
      <button class="carousel-btn prev-btn fa-solid fa-chevron-left">
        <!-- <i class="fa-solid fa-chevron-left"></i> -->
      </button>
      <div class="carousel-slides">

        <div class="slide">
          <img src="https://static.pullandbear.net/assets/public/f00e/9258/1e274fc08986/138edf1fb9f2/newin/newin.jpg?ts=1747733387345&w=2940&f=auto"
            alt="">
        </div>
        <div class="slide">
          <img src="https://imagedeleg1.lacoste.com/dw/image/v2/BGSW_PRD/on/demandware.static/-/Library-Sites-LacosteContent/default/dw711667ac/images/2025/homepage/2025-02-13/STARTERFDesk_0004s_0006_Sweatshirt1_Mixte_5760x2382.png?imwidth=1905&impolicy=custom"
            alt="">
        </div>
        <div class="slide">
          <img src="https://im.uniqlo.com/global-cms/spa/resbe03cca45cd933a1782c54b147379638fr.jpg"
            alt="">
        </div>
        <div class="slide">
          <img src="https://2xu.com/cdn/shop/files/Fast_Track_Wide_Promo_Banner_2400x970_958e2235-1297-47e2-b18a-10b58a5c2f1c.jpg?v=1747886566" alt="">
        </div>

      </div>
      <button class="carousel-btn next-btn fa-solid fa-chevron-right">

      </button>
    </div>

    <header>
      <h1>Fashion Sale Collection</h1>
    </header>
    <br>

    <div class="container">
      <!-- Card 1 -->



      <div class="container" id="product-list">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 p-6">
          <?php foreach ($products as $product): ?>
            <div class="relative w-full max-w-sm bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
              <!-- Icon Love (favorite) -->
              <form method="POST" action="wishlist/favorite.php" class="absolute top-3 right-3">
                <input type="hidden" name="produk_id" value="<?= $product['produk_id'] ?>">
                <button type="submit" class="text-gray-500 hover:text-red-500">
                  <i data-feather="heart" class="w-5 h-5"></i>
                </button>
              </form>


              <a href="pages/detail.php?id=<?= $product['produk_id'] ?>">
                <img class="p-6 rounded-t-lg mx-auto max-h-48 object-contain" src="uploads/<?= $product['foto_url'] ?>" alt="<?= $product['nama_produk'] ?>" />
              </a>

              <div class="px-5 pb-5">
                <a href="pages/detail.php?id=<?= $product['produk_id'] ?>">
                  <h5 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white"><?= $product['nama_produk'] ?></h5>
                </a>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mb-2 truncate whitespace-nowrap overflow-hidden"><?= $product['deskripsi'] ?></p>


                <div class="flex items-center justify-between mt-4 mb-3">
                  <span class="text-2xl font-bold text-white">Rp<?= number_format($product['harga'], 0, ',', '.') ?></span>
                  <form class="add-to-cart-form inline-block" data-produk-id="<?= $product['produk_id'] ?>">
                    <input type="hidden" name="produk_id" value="<?= $product['produk_id'] ?>">
                    <input type="hidden" name="nama_produk" value="<?= htmlspecialchars($product['nama_produk']) ?>">
                    <input type="hidden" name="harga" value="<?= $product['harga'] ?>">
                    <input type="hidden" name="color" value="default_color_here">
                    <input type="hidden" name="size" value="default_size_here">
                    <input type="hidden" name="quantity" value="1">
                  </form>

                </div>

                <a href="checkout.php?id=<?= $product['produk_id'] ?>" class="block w-full text-center text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:focus:ring-green-800">
                  Checkout Sekarang
                </a>
              </div>
            </div>
          <?php endforeach; ?>
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
      feather.replace();
    </script>
    <script>
      const carouselSlides = document.querySelector('.carousel-slides');
      const slides = document.querySelectorAll('.slide');
      const slide = document.querySelectorAll('.slides');
      const prevBtn = document.querySelector('.prev-btn');
      const nextBtn = document.querySelector('.next-btn');

      let currentIndex = 0;

      // Function to update carousel position
      function updateCarousel() {
        const offset = -currentIndex * 100;
        carouselSlides.style.transform = `translateX(${offset}%)`;
      }

      // Go to the previous slide
      prevBtn.addEventListener('click', () => {
        currentIndex = (currentIndex - 1 + slides.length) % slides.length;
        updateCarousel();
        resetAutoSlide();
      });


      // Go to the next slide
      nextBtn.addEventListener('click', () => {
        currentIndex = (currentIndex + 1) % slides.length;
        updateCarousel();
        resetAutoSlide();
      });


      function startAutoSlide() {
        autoSlideInterval = setInterval(() => {
          currentIndex = (currentIndex + 1) % slides.length;
          updateCarousel();
        }, 3000);
      }

      function resetAutoSlide() {
        clearInterval(autoSlideInterval);
        startAutoSlide();
      }

      startAutoSlide();
    </script>
    <script>
      document.querySelectorAll('.add-to-cart-form').forEach(form => {
        form.addEventListener('submit', async (e) => {
          e.preventDefault(); // cegah reload halaman

          const formData = new FormData(form);
          try {
            const res = await fetch('add_to_cart.php', {
              method: 'POST',
              body: formData
            });

            const data = await res.json();

            if (data.success) {
              showToast(data.message, 'success');
            } else {
              showToast(data.message || 'Terjadi kesalahan', 'error');
            }
          } catch (error) {
            showToast('Gagal menghubungi server', 'error');
          }
        });
      });

      function showToast(message, type) {
        const toast = document.createElement('div');
        toast.textContent = message;
        toast.className = `fixed bottom-5 right-5 px-5 py-3 rounded shadow-lg text-white font-semibold transition-opacity duration-300
      ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}`;
        document.body.appendChild(toast);
        setTimeout(() => {
          toast.style.opacity = '0';
          setTimeout(() => toast.remove(), 300);
        }, 3000);
      }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>


    <script src="https://unpkg.com/feather-icons"></script>
    <script>
      feather.replace();
    </script>
  </body>

</html>