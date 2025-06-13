<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $foto = $_FILES['foto']['name'];
    $tmp = $_FILES['foto']['tmp_name'];
    $size = $_FILES['foto']['size'];
    $type = $_FILES['foto']['type'];

    // Batasan maksimal file: 2MB
    $max_size = 2 * 1024 * 1024; // 2MB dalam byte

    // Format file yang diizinkan
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];

    if (!in_array($type, $allowed_types)) {
        echo "Format file tidak didukung! (harus jpg, jpeg, png, atau webp)";
        exit;
    }

    if ($size > $max_size) {
        echo "Ukuran file terlalu besar! Maksimal 2MB.";
        exit;
    }

    // Bikin nama unik supaya tidak tabrakan
    $nama_baru = uniqid() . '_' . $foto;
    $path = "../uploads/" . $nama_baru;

    if (move_uploaded_file($tmp, $path)) {
        echo "Upload berhasil! Gambar disimpan sebagai: " . $nama_baru;
    } else {
        echo "Gagal mengupload gambar!";
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="foto" required>
    <button type="submit">Upload</button>
</form>
