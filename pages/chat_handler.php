<?php
session_start();


$sender = $_POST['sender'] ?? '';
$message = $_POST['message'] ?? '';
$type = $_POST['type'] ?? 'biasa'; 
$tawar_harga = $_POST['tawar_harga'] ?? null;
$action = $_GET['action'] ?? null;
$index = $_GET['index'] ?? null;

// Jika kirim tawaran baru
if ($sender && ($message || $tawar_harga)) {
    $chatItem = [
        'sender' => $sender,
        'message' => $type === 'tawar' ? 'Menawar Rp ' . number_format($tawar_harga) : $message,
        'time' => date('H:i'),
        'type' => $type,
        'status' => $type === 'tawar' ? 'pending' : null,
        'harga' => $tawar_harga
    ];

    $_SESSION['chat'][] = $chatItem;
}

// Jika ada aksi "terima" atau "tolak" tawaran
if ($action && $index !== null && isset($_SESSION['chat'][$index])) {
    if ($_SESSION['chat'][$index]['type'] === 'tawar') {
        if ($action === 'accept') {
            $_SESSION['chat'][$index]['status'] = 'diterima';
            
            $_SESSION['chat'][] = [
                'sender' => 'penjual',
                'message' => 'Tawaran Rp ' . number_format($_SESSION['chat'][$index]['harga']) . ' diterima.',
                'time' => date('H:i'),
                'type' => 'biasa'
            ];
        } elseif ($action === 'reject') {
            $_SESSION['chat'][$index]['status'] = 'ditolak';
            $_SESSION['chat'][] = [
                'sender' => 'penjual',
                'message' => 'Tawaran Rp ' . number_format($_SESSION['chat'][$index]['harga']) . ' ditolak.',
                'time' => date('H:i'),
                'type' => 'biasa'
            ];
        }
    }
}


header('Location: chet.php');
exit;
?>
    