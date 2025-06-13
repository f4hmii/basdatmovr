<?php
// File: pages/login.php (Final)
session_start();
include '../db_connection.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $sandi = $_POST['sandi'];

    if (empty($username) || empty($sandi)) {
        $error = "Username dan password tidak boleh kosong.";
    } else {
        $stmt = $conn->prepare("SELECT pengguna_id, nama_pengguna, sandi FROM pengguna WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($sandi, $user['sandi'])) {
                $_SESSION['pengguna_id'] = $user['pengguna_id'];
                $_SESSION['nama_pengguna'] = $user['nama_pengguna'];
                $_SESSION['username'] = $username;

                $pengguna_id = $user['pengguna_id'];

                // 1. Cek apakah admin
                $stmt_admin = $conn->prepare("SELECT admin_id FROM admin WHERE pengguna_id = ?");
                $stmt_admin->bind_param("i", $pengguna_id);
                $stmt_admin->execute();
                $result_admin = $stmt_admin->get_result();
                if ($result_admin->num_rows > 0) {
                    $_SESSION['role'] = 'admin';
                    header("Location: ../admincontrol/dashbord_admin.php");
                    exit();
                }
                $stmt_admin->close();

                // 2. Jika bukan admin, cek apakah seller
                $stmt_seller = $conn->prepare("SELECT seller_id FROM seller WHERE pengguna_id = ?");
                $stmt_seller->bind_param("i", $pengguna_id);
                $stmt_seller->execute();
                $result_seller = $stmt_seller->get_result();
                if ($result_seller->num_rows > 0) {
                    $_SESSION['role'] = 'seller';
                    header("Location: ../seller/produk.php");
                    exit();
                }
                $stmt_seller->close();
                
                // 3. Jika bukan keduanya, maka dia adalah buyer
                $_SESSION['role'] = 'buyer';
                header("Location: ../index.php");
                exit();

            } else {
                $error = "Username atau password salah.";
            }
        } else {
            $error = "Username atau password salah.";
        }
        $stmt->close();
    }
}
// Koneksi ditutup jika ada proses POST, jika tidak, biarkan untuk penggunaan lain
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MOVR</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-md bg-white rounded-lg shadow-md p-8">
        <h2 class="text-2xl font-bold text-center mb-6">Login</h2>
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>
        <form action="login.php" method="post">
            <div class="mb-4">
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                <input type="text" name="username" id="username" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-6">
                <label for="sandi" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" name="sandi" id="sandi" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Sign In
                </button>
                <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="register.php">
                    Belum punya akun? Register
                </a>
            </div>
        </form>
    </div>
</body>
</html>