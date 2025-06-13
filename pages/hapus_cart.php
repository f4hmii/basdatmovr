<?php
session_start();
include '../db_connection.php';

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

$pengguna_id = intval($_SESSION['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_id = intval($_POST['cart_id'] ?? 0);

    if ($cart_id > 0) {
        $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND pengguna_id = ?");
        $stmt->bind_param("ii", $cart_id, $pengguna_id);
        $stmt->execute();

        $stmt->close();
    }
}

header("Location: cart.php");
exit;
