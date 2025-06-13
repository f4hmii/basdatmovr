<?php
include '../db_connection.php';

if (isset($_GET['kategori_id']) && is_numeric($_GET['kategori_id'])) {
    $id = intval($_GET['kategori_id']);
    // Hapus kategori (pastikan tidak ada produk yang masih pakai kategori ini)
    $stmt = $conn->prepare("DELETE FROM kategori WHERE kategori_id = ?");
    $stmt->bind_param("i", $id);
     if ($stmt->execute()) {
           header("Location: dashbord_admin.php#kelola_kategori");
            exit();
        } else {
            echo "Gagal menghapus user.";
        }

        $stmt->close();
    } else {
        echo "ID user tidak ditemukan atau tidak valid.";
    }
?>