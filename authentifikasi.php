<?php
session_start();

function check_role_access(array $allowed_roles) {
    // Cek apakah user sudah login
    if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
        header('Location: login.php');
        exit;
    }

    // Cek apakah role user ada di daftar role yang diizinkan
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        header('HTTP/1.1 403 Forbidden');
        echo "<h1>403 Forbidden</h1>";
        echo "<p>Anda tidak memiliki izin untuk mengakses halaman ini.</p>";
        exit;
    }
}
