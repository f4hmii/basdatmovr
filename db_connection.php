<?php
// db_connection.php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bsdtmvr"; // Menggunakan nama database yang sudah Anda konfirmasi

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Mengatur charset ke utf8mb4 untuk mendukung karakter emoji dan lainnya
$conn->set_charset("utf8mb4");
?>