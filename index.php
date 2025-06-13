<?php
session_start();
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
  header("Location: admincontrol/dashbord_admin.php");
  exit();
}

include "view/header.php";
include 'db_connection.php';

// Ambil data dari tabel produk yang sudah diverifikasi oleh admin
$query = "SELECT * FROM produk WHERE verified = 1 ORDER BY produk_id DESC";
$result = $conn->query($query);

$products = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>MOVR - Selamat Datang</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <link rel="stylesheet" href="index.css">
</head>
<body class="bg-gray-100">

    <div class="carousel">
      <button class="carousel-btn prev-btn fa-solid fa-chevron-left"></button>
      <div class="carousel-slides">
        <div class="slide"><img src="https://static.pullandbear.net/assets/public/f00e/9258/1e274fc08986/138edf1fb9f2/newin/newin.jpg?ts=1747733387345&w=2940&f=auto" alt="Carousel Image 1"></div>
        <div class="slide"><img src="https://imagedeleg1.lacoste.com/dw/image/v2/BGSW_PRD/on/demandware.static/-/Library-Sites-LacosteContent/default/dw711667ac/images/2025/homepage/2025-02-13/STARTERFDesk_0004s_0006_Sweatshirt1_Mixte_5760x2382.png?imwidth=1905&impolicy=custom" alt="Carousel Image 2"></div>
        <div class="slide"><img src="https://im.uniqlo.com/global-cms/spa/resbe03cca45cd933a1782c54b147379638fr.jpg" alt="Carousel Image 3"></div>
        <div class="slide"><img src="https://2xu.com/cdn/shop/files/Fast_Track_Wide_Promo_Banner_2400x970_958e2235-1297-47e2-b18a-10b58a5c2f1c.jpg?v=1747886566" alt="Carousel Image 4"></div>
      </div>
      <button class="carousel-btn next-btn fa-solid fa-chevron-right"></button>
    </div>

    <header class="text-center my-8">
      <h1 class="text-3xl font-bold text-gray-800">Koleksi Terbaru</h1>
    </header>

    <main class="container mx-auto">
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 p-6">
        <?php foreach ($products as $product): ?>
          <div class="relative w-full max-w-sm bg-white border border-gray-200 rounded-lg shadow-sm flex flex-col">
            <form method="POST" action="wishlist/favorite.php" class="absolute top-3 right-3 z-10">
              <input type="hidden" name="produk_id" value="<?= $product['produk_id'] ?>">
              <button type="submit" class="p-2 bg-white rounded-full shadow-md text-gray-500 hover:text-red-500 focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-heart"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
              </button>
            </form>

            <a href="pages/detail.php?id=<?= $product['produk_id'] ?>">
              <img class="p-6 rounded-t-lg mx-auto h-48 object-contain" src="uploads/<?= htmlspecialchars($product['foto_url']) ?>" alt="<?= htmlspecialchars($product['nama_produk']) ?>" />
            </a>

            <div class="px-5 pb-5 flex flex-col flex-grow">
              <a href="pages/detail.php?id=<?= $product['produk_id'] ?>">
                <h5 class="text-xl font-semibold tracking-tight text-gray-900 truncate" title="<?= htmlspecialchars($product['nama_produk']) ?>"><?= htmlspecialchars($product['nama_produk']) ?></h5>
              </a>
              
              <div class="flex-grow mt-2">
                 <p class="text-sm text-gray-500 line-clamp-2"><?= htmlspecialchars($product['deskripsi']) ?></p>
              </div>

              <div class="flex items-center justify-between mt-4 mb-3">
                <span class="text-2xl font-bold text-gray-900">Rp<?= number_format($product['harga'], 0, ',', '.') ?></span>
              </div>
              
              <a href="pages/detail.php?id=<?= $product['produk_id'] ?>" class="block w-full text-center text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5">
                Lihat Detail
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </main>
    
    <div id="toast-container" class="fixed bottom-5 right-5 z-50"></div>

    <?php include "view/footer.php"; ?>

    <script>
      // Carousel Logic
      const carouselSlides = document.querySelector('.carousel-slides');
      const slides = document.querySelectorAll('.slide');
      const prevBtn = document.querySelector('.prev-btn');
      const nextBtn = document.querySelector('.next-btn');
      let currentIndex = 0;
      let autoSlideInterval;

      function updateCarousel() {
        const offset = -currentIndex * 100;
        carouselSlides.style.transform = `translateX(${offset}%)`;
      }

      function nextSlide() {
        currentIndex = (currentIndex + 1) % slides.length;
        updateCarousel();
      }

      prevBtn.addEventListener('click', () => {
        currentIndex = (currentIndex - 1 + slides.length) % slides.length;
        updateCarousel();
        resetAutoSlide();
      });

      nextBtn.addEventListener('click', () => {
        nextSlide();
        resetAutoSlide();
      });

      function startAutoSlide() {
        autoSlideInterval = setInterval(nextSlide, 3000);
      }

      function resetAutoSlide() {
        clearInterval(autoSlideInterval);
        startAutoSlide();
      }
      startAutoSlide();
    
      // Toast Logic (for favorite button)
      function showToast(message, type = 'success') {
          const toastContainer = document.getElementById('toast-container');
          const toast = document.createElement('div');
          toast.textContent = message;
          const bgColor = type === 'success' ? 'bg-green-600' : 'bg-red-600';
          toast.className = `px-5 py-3 rounded shadow-lg text-white font-semibold transition-opacity duration-300 ${bgColor}`;
          
          toastContainer.appendChild(toast);
          
          setTimeout(() => {
              toast.style.opacity = '0';
              setTimeout(() => toast.remove(), 300);
          }, 3000);
      }

      // Intercept favorite form submission
      document.querySelectorAll('form[action="wishlist/favorite.php"]').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(form);
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json(); // Mengasumsikan favorite.php akan merespon dengan JSON
                
                if(result.success) {
                    showToast(result.message || 'Berhasil ditambahkan ke favorit!', 'success');
                } else {
                    showToast(result.message || 'Gagal menambahkan ke favorit. Mungkin Anda harus login?', 'error');
                }

            } catch(error) {
                showToast('Gagal menghubungi server.', 'error');
            }
        });
      });
    </script>
</body>
</html>