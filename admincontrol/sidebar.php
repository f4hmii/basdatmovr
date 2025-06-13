<?php
// File: admincontrol/sidebar.php (Versi Final - Navigasi Langsung)

// Mengambil nama file yang sedang aktif untuk memberi tanda 'active' pada menu
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 280px; min-height: 100vh;">
    <a href="dashbord_admin.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span class="fs-4">Admin Panel</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="dashbord_admin.php" class="nav-link text-white <?= $current_page == 'dashbord_admin.php' ? 'active' : '' ?>">
                Dashboard
            </a>
        </li>
        <li>
            <a href="kelola_user.php" class="nav-link text-white <?= $current_page == 'kelola_user.php' ? 'active' : '' ?>">
                Kelola Pengguna
            </a>
        </li>
        <li>
            <a href="verifikasi_produk.php" class="nav-link text-white <?= $current_page == 'verifikasi_produk.php' ? 'active' : '' ?>">
                Verifikasi Produk
            </a>
        </li>
        <li>
            <a href="kelola_produk.php" class="nav-link text-white <?= $current_page == 'kelola_produk.php' ? 'active' : '' ?>">
                Semua Produk
            </a>
        </li>
        <li>
            <a href="kelola_pembayaran.php" class="nav-link text-white <?= $current_page == 'kelola_pembayaran.php' ? 'active' : '' ?>">
                Kelola Pembayaran
            </a>
        </li>
        <li>
            <a href="kelola_kategori.php" class="nav-link text-white <?= $current_page == 'kelola_kategori.php' ? 'active' : '' ?>">
                Kelola Kategori
            </a>
        </li>
    </ul>
    <hr>
    <div>
        <strong><?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></strong>
        <a class="btn btn-sm btn-danger mt-2" href="logout_admin.php">Logout</a>
    </div>
</div>