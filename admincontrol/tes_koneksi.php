<?php
// File: admincontrol/tes_koneksi.php

echo "<h1>Hasil Tes Path Koneksi</h1>";
echo "<hr>";

// Path yang kita coba panggil dari dalam folder 'admincontrol'
$path_ke_koneksi = '../db_connection.php';

// Menampilkan direktori kerja saat ini untuk diagnosis
$direktori_sekarang = getcwd();
echo "<p>File `tes_koneksi.php` ini sedang berjalan dari direktori: <br><code>" . $direktori_sekarang . "</code></p>";

// Mencoba mencari file koneksi menggunakan path relatif
echo "<p>Mencoba mencari file koneksi dengan path: <code>" . $path_ke_koneksi . "</code></p>";


// Tes menggunakan fungsi file_exists()
if (file_exists($path_ke_koneksi)) {
    echo "<h2 style='color:green;'>HASIL TES: SUKSES! ✅</h2>";
    echo "<p>File <code>db_connection.php</code> berhasil ditemukan dari dalam folder `admincontrol`.</p>";
    echo "<p>Ini berarti tidak ada masalah sama sekali dengan path atau struktur folder Anda. Masalahnya pasti sangat sepele di tempat lain. Mari kita coba perbaiki file verifikasi sekali lagi dengan kode yang paling sederhana.</p>";
} else {
    echo "<h2 style='color:red;'>HASIL TES: GAGAL! ❌</h2>";
    echo "<p>File <code>db_connection.php</code> <strong>TIDAK BISA DITEMUKAN</strong> menggunakan path '../'.</p>";
    echo "<p><b>INI ADALAH AKAR MASALAH ANDA.</b> Pastikan struktur folder Anda adalah <code>/testing_tubesWEB/db_connection.php</code> dan <code>/testing_tubesWEB/admincontrol/tes_koneksi.php</code>.</p>";
}

?>