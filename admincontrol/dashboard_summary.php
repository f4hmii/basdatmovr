<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - MOVR</title>
    <!-- <link rel="stylesheet" href="../assets/style.css"> -->
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f5f5f5;
        }
        .sidebar {
            width: 220px;
            background-color: rgb(121, 122, 135);
            color: white;
            position: fixed;
            top: 0;
            bottom: 0;
            padding: 20px;
        }
        .sidebar h2 {
            font-size: 20px;
            margin-bottom: 30px;
        }
        .sidebar img {
            width: 130px;
            margin-bottom: 15px;
        }
        .sidebar a {
            display: block;
            padding: 10px;
            color: white;
            text-decoration: none;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .sidebar a:hover,
        .sidebar a.active {
            background-color: rgb(80, 81, 82);
        }
        .cards {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        .card {
            flex: 1;
            padding: 20px;
            border-radius: 10px;
            color: white;
            font-size: 18px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .card h3 {
            margin: 0 0 10px 0;
            font-size: 20px;
        }
        .bg-red {
            background-color: rgb(110, 110, 112);
        }
        .bg-orange {
            background-color: #ff8800;
        }
        .icon {
            font-size: 24px;
            margin-right: 10px;
            vertical-align: middle;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<?php
include '../db_connection.php';

// Ambil data summary dari database
$result = $conn->query("SELECT
  (SELECT COUNT(*) FROM pengguna) AS jumlah_pengguna,
  (SELECT COUNT(*) FROM produk WHERE verified = 1) AS jumlah_barang,
  (SELECT COUNT(*) FROM pembayaran WHERE status_pembayaran = 'pending') AS jumlah_pembayaran_pending,
  (SELECT COUNT(*) FROM produk WHERE verified = 0) AS jumlah_verifikasi_produk");

$row= $result->fetch_assoc();
$total_users = $row['jumlah_pengguna'] ?? 0;
$total_barang = $row['jumlah_barang'] ?? 0;
$pending_pembayaran = $row['jumlah_pembayaran_pending'] ?? 0;
$verifikasi_produk = $row['jumlah_verifikasi_produk'] ?? 0;

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!-- Main Content -->
<div class="main-dashboard">
    <h1>Dashboard</h1>

<div class="cards">
    <div class="card bg-red">
        <h3><span class="icon">👥</span>Total Users</h3>
        <p><?= $total_users ?></p>
    </div>
    <div class="card bg-red">
        <h3><span class="icon">🛒</span>Total Barang</h3>
        <p><?= $total_barang ?></p>
    </div>
    <div class="card bg-orange">
        <h3><span class="icon">⏳</span>Pembayaran Pending</h3>
        <p><?= $pending_pembayaran ?></p>
    </div>
    <div class="card bg-orange">
        <h3><span class="icon">⏳</span>Verifikasi Produk</h3>
        <p><?= $verifikasi_produk ?></p>
    </div>
</div>
</div>


</body>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const links = document.querySelectorAll(".sidebar-link");
    const mainContent = document.querySelector(".main-dashboard");

    // Fungsi memuat halaman via AJAX
    function loadPage(page, updateHash = true) {
        console.log("Memuat halaman:", page);

        fetch(page)
            .then(response => {
                if (!response.ok) throw new Error("Gagal memuat halaman");
                return response.text();
            })
            .then(html => {
                mainContent.innerHTML = html;
                if (updateHash && page !== "dashbord_admin.php") {
                    window.location.hash = page.replace('.php', '');
                } else if (!updateHash || page === "dashbord_admin.php") {
                    history.replaceState(null, '', window.location.pathname); // Hapus hash
                }
            })
            .catch(err => {
                mainContent.innerHTML = `<p style="color:red;">${err.message}</p>`;
            });
    }

    // Fungsi klik berdasarkan hash di URL
    function clickSidebarByHash() {
        const hash = window.location.hash.replace('#', '');
        if (hash) {
            const target = Array.from(links).find(l => {
                const page = l.getAttribute('data-page')?.replace('.php', '');
                return page === hash;
            });
            if (target) target.click();
        } else {
            // Default: load dashboard
            loadPage("dashbord_admin.php", false);
            links.forEach(l => l.classList.remove("active"));
            const dashboardLink = Array.from(links).find(l => l.getAttribute('data-page') === 'dashbord_admin.php');
            if (dashboardLink) dashboardLink.classList.add("active");
        }
    }

    // Event klik menu sidebar
    links.forEach(link => {
        link.addEventListener("click", function (e) {
            e.preventDefault();

            // Aktifkan link yang diklik
            links.forEach(l => l.classList.remove("active"));
            this.classList.add("active");

            const page = this.getAttribute("data-page");
            const isDashboard = page === "dashbord_admin.php";

            loadPage(page, !isDashboard);
        });
    });

    // Auto-click sidebar jika ada hash di URL
    clickSidebarByHash();   

    // Jika hash berubah (misal: setelah redirect), auto klik juga
    window.addEventListener('hashchange', clickSidebarByHash);
});
</script>

</html>
