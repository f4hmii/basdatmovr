<?php
session_start();
include '../db_connection.php';

// Keamanan: Pastikan hanya seller yang bisa mengakses halaman ini
if (!isset($_SESSION['pengguna_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../pages/login.php");
    exit();
}

$seller_id = $_SESSION['pengguna_id'];

// Logika untuk UPDATE data saat form di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_akun'])) {
        $nama_pengguna = $_POST['nama_pengguna'];
        $email = $_POST['email'];
        $nomor_telepon = $_POST['nomor_telepon'];
        $stmt_user = $conn->prepare("UPDATE pengguna SET nama_pengguna = ?, email = ?, nomor_telepon = ? WHERE pengguna_id = ?");
        $stmt_user->bind_param("sssi", $nama_pengguna, $email, $nomor_telepon, $seller_id);
        $stmt_user->execute();
        $stmt_user->close();
    }
    if (isset($_POST['update_toko'])) {
        $nama_toko = $_POST['nama_toko'];
        $alamat_toko = $_POST['alamat_toko'];
        $stmt_check = $conn->prepare("SELECT pengguna_id FROM seller WHERE pengguna_id = ?");
        $stmt_check->bind_param("i", $seller_id);
        $stmt_check->execute();
        $seller_exists = $stmt_check->get_result()->num_rows > 0;
        $stmt_check->close();
        if ($seller_exists) {
            $stmt_toko = $conn->prepare("UPDATE seller SET nama_toko = ?, alamat_toko = ? WHERE pengguna_id = ?");
            $stmt_toko->bind_param("ssi", $nama_toko, $alamat_toko, $seller_id);
        } else {
            $stmt_toko = $conn->prepare("INSERT INTO seller (pengguna_id, nama_toko, alamat_toko) VALUES (?, ?, ?)");
            $stmt_toko->bind_param("iss", $seller_id, $nama_toko, $alamat_toko);
        }
        $stmt_toko->execute();
        $stmt_toko->close();
    }
    header("Location: profile.php?status=updated");
    exit();
}

// Mengambil semua data yang dibutuhkan
// 1. Data Pengguna & Toko
$stmt_data = $conn->prepare("SELECT p.username, p.nama_pengguna, p.email, p.nomor_telepon, s.nama_toko, s.alamat_toko FROM pengguna p LEFT JOIN seller s ON p.pengguna_id = s.pengguna_id WHERE p.pengguna_id = ?");
$stmt_data->bind_param("i", $seller_id);
$stmt_data->execute();
$seller_data = $stmt_data->get_result()->fetch_assoc();
$stmt_data->close();

// 2. Statistik
$total_produk = $conn->query("SELECT COUNT(*) as total FROM produk WHERE seller_id = $seller_id")->fetch_assoc()['total'];
$produk_terjual_query = $conn->query("SELECT SUM(td.quantity) as total FROM pembayaran p JOIN transaksi t ON p.transaksi_id = t.transaksi_id JOIN transaksi_detail td ON t.transaksi_id = td.transaksi_id JOIN produk pr ON td.produk_id = pr.produk_id WHERE pr.seller_id = $seller_id AND p.status_pembayaran = 'confirmed'");
$produk_terjual = $produk_terjual_query->fetch_assoc()['total'] ?? 0;

// 3. Daftar Produk
$stmt_produk = $conn->prepare("SELECT * FROM produk WHERE seller_id = ? ORDER BY produk_id DESC");
$stmt_produk->bind_param("i", $seller_id);
$stmt_produk->execute();
$result_produk = $stmt_produk->get_result();

include '../view/header.php';
?>

<div class="container mx-auto p-6 bg-gray-50 min-h-screen">
    <h1 class="text-3xl font-bold mb-4 text-gray-800">Dashboard Penjual</h1>
    

    <div class="mb-4 border-b border-gray-200">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="sellerTab" role="tablist">
            <li class="mr-2" role="presentation">
                <button class="tab-btn inline-block p-4 border-b-2 rounded-t-lg" type="button" role="tab" data-tab-target="#profil">Profil & Statistik</button>
            </li>
            <li class="mr-2" role="presentation">
                <button class="tab-btn inline-block p-4 border-b-2 rounded-t-lg" type="button" role="tab" data-tab-target="#produk">Kelola Produk</button>
            </li>
        </ul>
    </div>

    <div id="sellerTabContent">
        <div class="tab-content" id="profil" role="tabpanel">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-lg flex items-center">
                    <div class="w-24 h-24 bg-indigo-100 rounded-full flex items-center justify-center mr-6">
                        <i class="fas fa-store text-4xl text-indigo-500"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($seller_data['nama_toko'] ?? 'Nama Toko Belum Diatur') ?></h2>
                        <p class="text-gray-600">Oleh: <?= htmlspecialchars($seller_data['nama_pengguna']) ?> (@<?= htmlspecialchars($seller_data['username']) ?>)</p>
                        <p class="text-sm text-gray-500 mt-2"><i class="fas fa-map-marker-alt fa-fw mr-2"></i><?= htmlspecialchars($seller_data['alamat_toko'] ?? 'Alamat toko belum diatur') ?></p>
                        <p class="text-sm text-gray-500"><i class="fas fa-envelope fa-fw mr-2"></i><?= htmlspecialchars($seller_data['email']) ?></p>
                        <p class="text-sm text-gray-500"><i class="fas fa-phone fa-fw mr-2"></i><?= htmlspecialchars($seller_data['nomor_telepon']) ?></p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h3 class="font-bold text-lg mb-4">Ringkasan Toko</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center"><span class="text-gray-600">Total Produk</span><span class="font-bold text-xl text-blue-600"><?= $total_produk ?></span></div>
                        <div class="flex justify-between items-center"><span class="text-gray-600">Produk Terjual</span><span class="font-bold text-xl text-green-600"><?= intval($produk_terjual) ?></span></div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h3 class="font-bold text-lg mb-4">Edit Informasi Akun</h3>
                    <form action="profile.php" method="POST" class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-600">Nama Lengkap</label>
                            <input type="text" name="nama_pengguna" value="<?= htmlspecialchars($seller_data['nama_pengguna']) ?>" class="w-full border rounded-md p-2 mt-1 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($seller_data['email']) ?>" class="w-full border rounded-md p-2 mt-1 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Nomor Telepon</label>
                            <input type="text" name="nomor_telepon" value="<?= htmlspecialchars($seller_data['nomor_telepon']) ?>" class="w-full border rounded-md p-2 mt-1 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <button type="submit" name="update_akun" class="w-full bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-indigo-700">Simpan Akun</button>
                    </form>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h3 class="font-bold text-lg mb-4">Edit Informasi Toko</h3>
                    <form action="profile.php" method="POST" class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-600">Nama Toko</label>
                            <input type="text" name="nama_toko" value="<?= htmlspecialchars($seller_data['nama_toko'] ?? '') ?>" class="w-full border rounded-md p-2 mt-1 focus:ring-2 focus:ring-green-500">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Alamat Toko</label>
                            <textarea name="alamat_toko" class="w-full border rounded-md p-2 mt-1 h-24 focus:ring-2 focus:ring-green-500"><?= htmlspecialchars($seller_data['alamat_toko'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" name="update_toko" class="w-full bg-green-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-green-700">Simpan Info Toko</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="tab-content hidden" id="produk" role="tabpanel">
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-lg">Daftar Produk Anda</h3>
                    <a href="tambah.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex items-center transition-colors">
                        <i class="fas fa-plus mr-2"></i>Tambah Produk Baru
                    </a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    <?php if ($result_produk->num_rows > 0): ?>
                        <?php while($produk = $result_produk->fetch_assoc()): ?>
                        <div class="border rounded-lg overflow-hidden flex flex-col shadow-sm hover:shadow-xl transition-shadow">
                            <img src="../uploads/<?= htmlspecialchars($produk['foto_url']) ?>" class="w-full h-48 object-cover">
                            <div class="p-4 flex flex-col flex-grow">
                                <h4 class="font-bold text-md truncate" title="<?= htmlspecialchars($produk['nama_produk']) ?>"><?= htmlspecialchars($produk['nama_produk']) ?></h4>
                                <p class="text-green-600 font-semibold text-lg">Rp<?= number_format($produk['harga'],0,',','.') ?></p>
                                <p class="text-sm text-gray-500">Stok Total: <?= $produk['stock'] ?></p>
                                <?php
                                    $status_text = 'Menunggu Verifikasi'; $status_color = 'bg-yellow-100 text-yellow-800';
                                    if ($produk['verified'] == 1) { $status_text = 'Disetujui'; $status_color = 'bg-green-100 text-green-800'; } 
                                    elseif ($produk['verified'] == -1) { $status_text = 'Ditolak'; $status_color = 'bg-red-100 text-red-800'; }
                                ?>
                                <span class="text-xs font-semibold px-2 py-1 rounded-full <?= $status_color ?> self-start mt-2"><?= $status_text ?></span>
                                <div class="mt-auto pt-4 border-t mt-4 flex justify-end space-x-2">
                                    <a href="edit.php?produk_id=<?= $produk['produk_id'] ?>" class="text-sm bg-yellow-400 hover:bg-yellow-500 text-black font-semibold py-1 px-3 rounded-md transition-colors">Edit</a>
                                    <a href="hapus.php?produk_id=<?= $produk['produk_id'] ?>" onclick="return confirm('Yakin ingin menghapus produk ini?')" class="text-sm bg-red-600 hover:bg-red-700 text-white font-semibold py-1 px-3 rounded-md transition-colors">Hapus</a>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="md:col-span-3 text-center text-gray-500 py-8">Anda belum memiliki produk. <a href="tambah.php" class="text-blue-600 hover:underline">Tambah produk pertama Anda!</a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const tabs = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    function switchTab(targetId) {
        tabContents.forEach(content => {
            if (content.id === targetId) {
                content.classList.remove('hidden');
            } else {
                content.classList.add('hidden');
            }
        });

        tabs.forEach(tab => {
            if (tab.getAttribute('data-tab-target') === '#' + targetId) {
                tab.classList.add('border-blue-600', 'text-blue-600');
                tab.classList.remove('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300');
            } else {
                tab.classList.remove('border-blue-600', 'text-blue-600');
                tab.classList.add('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300');
            }
        });
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetId = this.getAttribute('data-tab-target').substring(1);
            switchTab(targetId);
        });
    });

    // Tampilkan tab pertama secara default
    switchTab('profil');
});
</script>

<?php include '../view/footer.php'; ?>