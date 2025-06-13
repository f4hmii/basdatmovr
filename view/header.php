<?php
// session_start() diasumsikan sudah dipanggil di halaman utama.

// FIX: baseURL diperbarui sesuai permintaan Anda
$baseURL = "http://localhost/testing_tubesWEB"; 

$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MOVR</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet" />
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= $baseURL ?>/view/header.css">
</head>

<body>
    <div class="navbar">
        <div class="logo">
            <h1>MOVR</h1>
        </div>

        <ul>
            <li><a href="<?= $baseURL ?>/index.php">Home</a></li>
            <li><a href="<?= $baseURL ?>/aboutfairuz.html">About</a></li>
            <li><a href="<?= $baseURL ?>/index.php">Produk</a></li>
            <li><a href="<?= $baseURL ?>/announcement.html">Announcement</a></li>
            <li><a href="<?= $baseURL ?>/pages/sale.php">Sale</a></li>
            <li><a href="<?= $baseURL ?>/servicefairuz.html">Service</a></li>

            <li class="category-dropdown">
                <div class="category-dropdown-toggle" onclick="toggleCategoryDropdown()">
                    <a href="#">Category</a>
                </div>
                <div class="category-dropdown-menu" id="categoryDropdown">
                    <a href="<?= $baseURL ?>/view/kategori.php?kategori=baju">Baju</a>
                    <a href="<?= $baseURL ?>/view/kategori.php?kategori=celana">Celana</a>
                    <a href="<?= $baseURL ?>/view/kategori.php?kategori=sepatu">Sepatu</a>
                    <a href="<?= $baseURL ?>/view/kategori.php?kategori=aksesoris">Aksesoris</a>
                </div>
            </li>
        </ul>

        <form method="GET" action="<?= $baseURL ?>/pages/search.php" class="search-form">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="query" placeholder="Cari produk" required>
            </div>
        </form>

        <div class="icon-wrapper">
            <a href="<?= $baseURL ?>/wishlist/favorite.php" title="Favorit" style="margin-right: 10px;"><i data-feather="heart"></i></a>
            <a href="<?= $baseURL ?>/pages/chet.php" title="Chat" style="margin-right: 10px;"><i data-feather="message-circle"></i></a>
            <a href="<?= $baseURL ?>/pages/cart.php" title="Keranjang" style="margin-right: 10px;"><i data-feather="shopping-cart"></i></a>

            <?php if (isset($_SESSION['username'])): ?>
                <div class="user-dropdown">
                    <div class="user-dropdown-toggle" onclick="toggleUserDropdown()">
                        <i data-feather="user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </div>
                    <div class="user-dropdown-menu" id="userDropdown">
                        <?php 
                        if ($role === 'seller'): ?>
                            <a href="<?= $baseURL ?>/seller/profile.php">Informasi Akun</a>
                            <a href="<?= $baseURL ?>/seller/produk.php">Kontrol Produk</a>
                        <?php elseif ($role === 'buyer'): ?>
                            <a href="<?= $baseURL ?>/buyer/profil.php">Informasi Akun</a>
                            <a href="<?= $baseURL ?>/pages/menjadi_seller.php" class="text-blue-600 font-semibold">Jadi Seller</a>
                        <?php elseif ($role === 'admin'): ?>
                            <a href="<?= $baseURL ?>/admincontrol/dashbord_admin.php">Dashboard Admin</a>
                        <?php endif; ?>
                        
                        <a href="#" onclick="confirmLogout()">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= $baseURL ?>/pages/login.php">
                    <i data-feather="log-in"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        feather.replace();
        
        function toggleUserDropdown() {
            const dropdown = document.getElementById("userDropdown");
            dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
        }

        function toggleCategoryDropdown() {
            const dropdown = document.getElementById("categoryDropdown");
            dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
        }

        window.addEventListener("click", function(e) {
            const userToggle = document.querySelector(".user-dropdown-toggle");
            const userMenu = document.getElementById("userDropdown");
            const catToggle = document.querySelector(".category-dropdown-toggle");
            const catMenu = document.getElementById("categoryDropdown");

            if (userToggle && userMenu && !userToggle.contains(e.target) && !userMenu.contains(e.target)) {
                userMenu.style.display = "none";
            }
            if (catToggle && catMenu && !catToggle.contains(e.target) && !catMenu.contains(e.target)) {
                catMenu.style.display = "none";
            }
        });

        function confirmLogout() {
            const yakin = confirm("Apakah Anda yakin ingin logout?");
            if (yakin) {
                window.location.href = "<?= $baseURL ?>/pages/logout.php";
            }
        }
    </script>
</body>
</html>