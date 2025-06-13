<?php
// Proses form jika ada data dikirim
$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Koneksi ke database - SUDAH DIPERBAIKI
    $host = "localhost";
    $username = "root";
    $password = "";
    $dbname = "basdat"; // Nama database disesuaikan dengan file basdat.sql

    $conn = new mysqli($host, $username, $password, $dbname);

    // Cek koneksi
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Ambil data dari form
    $name = $_POST['name'];
    $username_post = $_POST['username']; // Ganti nama variabel agar tidak bentrok dengan variabel koneksi
    $email = $_POST['email'];
    $password_post = password_hash($_POST['password'], PASSWORD_DEFAULT); // Ganti nama variabel

    // Cek apakah email sudah terdaftar
    $cek_email = $conn->prepare("SELECT pengguna_id FROM pengguna WHERE email = ?");
    $cek_email->bind_param("s", $email);
    $cek_email->execute();
    $cek_email->store_result();

    // Cek apakah username sudah terdaftar
    $cek_user = $conn->prepare("SELECT pengguna_id FROM pengguna WHERE username = ?");
    $cek_user->bind_param("s", $username_post);
    $cek_user->execute();
    $cek_user->store_result();

    if ($cek_email->num_rows > 0) {
        $error = "Email sudah terdaftar.";
    } elseif ($cek_user->num_rows > 0) {
        $error = "Username sudah digunakan.";
    } else {
        // Menyimpan data ke dalam database - SUDAH DIPERBAIKI
        // Menghilangkan kolom 'role' karena tidak ada di tabel `pengguna` baru
        $sql = "INSERT INTO pengguna (nama_pengguna, username, email, sandi) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        // Binding parameter disesuaikan (4 parameter string)
        $stmt->bind_param("ssss", $name, $username_post, $email, $password_post);

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        if ($stmt->execute()) {
            $success = "Akun berhasil dibuat. Anda akan dialihkan ke halaman login.";
            header("refresh:3;url=login.php"); // Ganti dengan URL login kamu
        } else {
            $error = "Gagal menyimpan data: " . $stmt->error;
        }

        $stmt->close();
    }

    $cek_email->close();
    $cek_user->close();
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
    <div class="flex items-center justify-center min-h-screen bg-cover bg-center" style="background-image: url('imgproduk/tangga.jpg');">
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

            <form method="POST" action="register.php">
                <div class="mb-4">
                    <label for="name" class="block text-sm text-gray-300">Name</label>
                    <input type="text" id="name" name="name" required placeholder="Enter your name" class="w-full p-2 rounded-lg bg-gray-200 text-gray-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div class="mb-4">
                    <label for="username" class="block text-sm text-gray-300">Username</label>
                    <input type="text" id="username" name="username" required placeholder="Enter your username" class="w-full p-2 rounded-lg bg-gray-200 text-gray-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-sm text-gray-300">Email</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email" class="w-full p-2 rounded-lg bg-gray-200 text-gray-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-sm text-gray-300">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password" class="w-full p-2 rounded-lg bg-gray-200 text-gray-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 rounded-lg transition duration-200">Create Account</button>
                
                <div class="text-sm text-gray-400 mt-6 text-center">
                    <a href="login.php" class="hover:text-white">Already have an account? Login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>