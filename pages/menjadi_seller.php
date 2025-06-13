<?php
session_start();
require '../db_connection.php'; // Mengambil koneksi database

// 1. Pengecekan Keamanan: Pastikan pengguna sudah login
if (!isset($_SESSION['pengguna_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Pengecekan Keamanan: Pastikan peran pengguna adalah 'buyer'
if ($_SESSION['role'] !== 'buyer') {
    // Jika bukan buyer (mungkin sudah jadi seller atau admin), tampilkan pesan dan hentikan.
    $pesan_halaman = "Anda sudah terdaftar sebagai penjual atau tidak memiliki akses ke halaman ini.";
    include '../view/header.php';
    echo "<div class='container mx-auto my-10 p-5'><div class='bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative' role='alert'>$pesan_halaman</div></div>";
    include '../view/footer.php';
    exit();
}

$pengguna_id = $_SESSION['pengguna_id'];
$error = '';
$success = '';

// 3. Proses Form Jika di-Submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_toko = htmlspecialchars(trim($_POST['nama_toko']));
    $alamat_toko = htmlspecialchars(trim($_POST['alamat_toko']));

    if (empty($nama_toko) || empty($alamat_toko)) {
        $error = "Nama toko dan alamat tidak boleh kosong.";
    } else {
        // Query untuk memasukkan data ke tabel seller
        $stmt = $conn->prepare("INSERT INTO seller (pengguna_id, nama_toko, alamat_toko) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $pengguna_id, $nama_toko, $alamat_toko);

        if ($stmt->execute()) {
            // Jika berhasil, perbarui peran di session
            $_SESSION['role'] = 'seller';
            $success = "Selamat! Anda berhasil menjadi seller. Anda akan diarahkan ke halaman profil seller dalam 3 detik.";
            // Arahkan ke profil seller setelah beberapa detik
            header("refresh:3;url=../seller/profile.php");
        } else {
            $error = "Terjadi kesalahan. Gagal mendaftar sebagai seller.";
        }
        $stmt->close();
    }
}

// Sertakan header
include '../view/header.php';
?>

<div class="container mx-auto my-10 p-5">
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Daftar Menjadi Seller</h2>

        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $success; ?></span>
            </div>
        <?php elseif ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <?php if (empty($success)): // Sembunyikan form jika sudah berhasil ?>
        <form action="menjadi_seller.php" method="post">
            <div class="mb-4">
                <label for="nama_toko" class="block text-gray-700 text-sm font-bold mb-2">Nama Toko Anda</label>
                <input type="text" name="nama_toko" id="nama_toko" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>

            <div class="mb-6">
                <label for="alamat_toko" class="block text-gray-700 text-sm font-bold mb-2">Alamat Toko / Alamat Pickup</label>
                <textarea name="alamat_toko" id="alamat_toko" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required></textarea>
                <p class="text-xs text-gray-600 mt-1">Masukkan alamat lengkap untuk keperluan pengiriman.</p>
            </div>

            <div class="flex items-center justify-end">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Kirim Pendaftaran
                </button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php
// Sertakan footer
include '../view/footer.php';

// PERBAIKAN: Koneksi ditutup di paling akhir setelah semua selesai.
$conn->close();
?>