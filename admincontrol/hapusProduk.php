<?php
include '../db_connection.php';
if (isset($_GET['pengguna_id']) && is_numeric($_GET['pengguna_id'])) {
    $id = intval($_GET['pengguna_id']);
    $conn->query("UPDATE pengguna SET status_aktif = 0 WHERE pengguna_id = $id");
}
header("Location: kelola_user.php");
exit;
?>