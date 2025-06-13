<?php
include 'db_connection.php';


$search = isset($_GET['query']) ? trim($_GET['query']) : '';

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hasil Pencarian Produk</title>
</head>
<body>

<!-- Form Pencarian -->
<form method="GET" action="search.php">
    <input type="text" name="query" placeholder="Cari produk..." value="<?= htmlspecialchars($search); ?>" required>
    <button type="submit">Cari</button>
</form>

<hr>

<?php

if ($search !== '') {
 
    $search = $koneksi->real_escape_string($search);

  
    $sql = "SELECT * FROM produk 
            WHERE nama_produk LIKE '%$search%' 
            OR deskripsi LIKE '%$search%' 
            OR harga LIKE '%$search%'";

    $result = $koneksi->query($sql);

    echo "<h2>Hasil Pencarian untuk: <em>" . htmlspecialchars($search) . "</em></h2>";

    if ($result && $result->num_rows > 0) {
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>";
            echo "<strong>" . htmlspecialchars($row['nama_produk']) . "</strong> - ";
            echo "Rp" . number_format($row['harga'], 0, ',', '.');
            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Tidak ada produk ditemukan.</p>";
    }
}
?>

</body>
</html>
