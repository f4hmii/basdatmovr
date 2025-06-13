<?php
// File: admincontrol/tes_lengkap.php
// Tes Mandiri untuk Mengambil Produk Verifikasi

echo "<h1>Tes Mandiri Query Verifikasi</h1><hr>";

// Detail koneksi ditulis langsung di sini untuk isolasi masalah
$host = "localhost";
$username = "root";
$password = "";
$dbname = "basdat";

// Membuat koneksi
$conn = new mysqli($host, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("<h2>Koneksi Gagal: " . $conn->connect_error . "</h2><p>Pastikan server database (MySQL/MariaDB) di XAMPP Anda sudah berjalan.</p>");
}
echo "<p style='color:green; font-weight:bold;'>Koneksi ke database 'basdat' berhasil.</p>";

// Query yang sama persis dengan yang ada di halaman verifikasi
$sql = "SELECT produk_id, nama_produk, seller_id, verified FROM produk WHERE verified = 0";
echo "<p>Mencoba menjalankan Query: <code>" . htmlspecialchars($sql) . "</code></p>";

$result = $conn->query($sql);

if ($result) {
    echo "<p style='color:green; font-weight:bold;'>Query Berhasil Dijalankan.</p>";
    echo "<p>Jumlah produk 'menunggu verifikasi' yang ditemukan: <strong>" . $result->num_rows . "</strong></p><hr>";

    if ($result->num_rows > 0) {
        echo "<h3>Data Produk yang Ditemukan:</h3>";
        echo "<table border='1' cellpadding='5' cellspacing='0'>
                <tr style='background:#eee;'>
                    <th>produk_id</th>
                    <th>nama_produk</th>
                    <th>seller_id</th>
                    <th>verified</th>
                </tr>";
        // Tampilkan hasil
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['produk_id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['nama_produk']) . "</td>";
            echo "<td>" . $row['seller_id'] . "</td>";
            echo "<td><strong>" . $row['verified'] . "</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p><strong>Tidak ada produk di database yang memiliki status 'verified = 0'.</strong> Jika Anda yakin sudah menambahkan produk, ini berarti proses di 'tambah.php' gagal menyimpan `verified` sebagai 0.</p>";
    }
} else {
    echo "<p style='color:red; font-weight:bold;'>Query GAGAL Dijalankan.</p>";
    echo "<p>Error dari Database: " . $conn->error . "</p>";
}

$conn->close();
?>