<?php
session_start();
// PERBAIKAN: Menggunakan file koneksi terpusat agar konsisten
include '../db_connection.php';

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Koneksi sudah tersedia dari file include di atas

    // Ambil data dari form dan bersihkan
    $name = htmlspecialchars(trim($_POST['name']));
    $username_post = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password_post = password_hash($_POST['password'], PASSWORD_DEFAULT); // Enkripsi password

    // Validasi dasar
    if (empty($name) || empty($username_post) || empty($email) || empty($_POST['password'])) {
        $error = "Semua kolom wajib diisi.";
    } else {
        // Cek apakah email sudah terdaftar
        $stmt_email = $conn->prepare("SELECT pengguna_id FROM pengguna WHERE email = ?");
        $stmt_email->bind_param("s", $email);
        $stmt_email->execute();
        $stmt_email->store_result();

        // Cek apakah username sudah terdaftar
        $stmt_user = $conn->prepare("SELECT pengguna_id FROM pengguna WHERE username = ?");
        $stmt_user->bind_param("s", $username_post);
        $stmt_user->execute();
        $stmt_user->store_result();

        if ($stmt_email->num_rows > 0) {
            $error = "Email sudah terdaftar. Silakan gunakan email lain.";
        } elseif ($stmt_user->num_rows > 0) {
            $error = "Username sudah digunakan. Silakan pilih username lain.";
        } else {
            //masukkan data ke database
            $sql = "INSERT INTO pengguna (nama_pengguna, username, email, sandi) VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql);
            $stmt_insert->bind_param("ssss", $name, $username_post, $email, $password_post);

            if ($stmt_insert->execute()) {
                $success = "Akun berhasil dibuat! Anda akan dialihkan ke halaman login dalam 3 detik.";
                header("refresh:3;url=login.php");
            } else {
                $error = "Gagal menyimpan data: " . $stmt_insert->error;
            }
            $stmt_insert->close();
        }
        $stmt_email->close();
        $stmt_user->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Create Account - MOVR</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="flex items-center justify-center min-h-screen bg-cover bg-center" style="background-image: url('../img/greg.jpg');">
        <div class="w-full max-w-sm bg-gray-800 bg-opacity-80 backdrop-blur-sm p-6 shadow-lg rounded-xl">
            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold text-red-500">MOVR</h1>
                <h2 class="text-xl text-gray-300">Create Account</h2>
            </div>

            <?php if ($success): ?>
                <div class="mb-4 p-3 bg-green-500 text-white rounded text-center text-sm"><?= htmlspecialchars($success) ?></div>
            <?php elseif ($error): ?>
                <div class="mb-4 p-3 bg-red-700 text-white rounded text-center text-sm"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (empty($success)): // Sembunyikan form jika pendaftaran berhasil ?>
            <form method="POST" action="register.php">
                <div class="mb-4">
                    <label for="name" class="block text-sm text-gray-300">Name</label>
                    <input type="text" id="name" name="name" required placeholder="Enter your name" class="w-full p-2 mt-1 rounded-lg bg-gray-200 text-gray-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div class="mb-4">
                    <label for="username" class="block text-sm text-gray-300">Username</label>
                    <input type="text" id="username" name="username" required placeholder="Enter your username" class="w-full p-2 mt-1 rounded-lg bg-gray-200 text-gray-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-sm text-gray-300">Email</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email" class="w-full p-2 mt-1 rounded-lg bg-gray-200 text-gray-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-sm text-gray-300">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password" class="w-full p-2 mt-1 rounded-lg bg-gray-200 text-gray-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 rounded-lg transition duration-200">Create Account</button>
                
                <div class="text-sm text-gray-400 mt-6 text-center">
                    <a href="login.php" class="hover:text-white">Already have an account? Login</a>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>