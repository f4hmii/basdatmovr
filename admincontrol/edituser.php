<?php
// File: admincontrol/edituser.php
session_start();
include '../db_connection.php';

// Keamanan
if (!isset($_SESSION['pengguna_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403); die("Akses ditolak.");
}
if (!isset($_GET['pengguna_id']) || !is_numeric($_GET['pengguna_id'])) {
    die("ID pengguna tidak valid.");
}

$id = intval($_GET['pengguna_id']);
$error_msg = '';
$success_msg = '';

// Logika untuk memproses form update
if (isset($_POST['update'])) {
    $username = htmlspecialchars($_POST['username']);
    $nama_pengguna = htmlspecialchars($_POST['nama_pengguna']);
    $email = htmlspecialchars($_POST['email']);
    $telepon = htmlspecialchars($_POST['telepon']);
    $new_role = $_POST['role'];
    $current_role = $_POST['current_role'];

    $conn->begin_transaction();
    try {
        $stmt_update_user = $conn->prepare("UPDATE pengguna SET username = ?, nama_pengguna = ?, email = ?, nomor_telepon = ? WHERE pengguna_id = ?");
        $stmt_update_user->bind_param("ssssi", $username, $nama_pengguna, $email, $telepon, $id);
        $stmt_update_user->execute();
        $stmt_update_user->close();

        if ($new_role !== $current_role) {
            if ($new_role === 'seller' && $current_role === 'buyer') {
                $stmt_add_seller = $conn->prepare("INSERT INTO seller (pengguna_id, nama_toko) VALUES (?, ?)");
                $nama_toko_default = "Toko " . $nama_pengguna;
                $stmt_add_seller->bind_param("is", $id, $nama_toko_default);
                $stmt_add_seller->execute();
                $stmt_add_seller->close();
            } elseif ($new_role === 'buyer' && $current_role === 'seller') {
                $stmt_del_seller = $conn->prepare("DELETE FROM seller WHERE pengguna_id = ?");
                $stmt_del_seller->bind_param("i", $id);
                $stmt_del_seller->execute();
                $stmt_del_seller->close();
            }
        }
        $conn->commit();
        $success_msg = "Data pengguna berhasil diperbarui.";
        echo '<p class="alert alert-success">Berhasil! Kembali ke daftar pengguna...</p>';
        echo '<script>setTimeout(() => { document.querySelector("a[data-page=\'kelola_user.php\']").click(); }, 1500);</script>';
    } catch (Exception $e) {
        $conn->rollback();
        $error_msg = "Gagal update: " . $e->getMessage();
    }
}

// Ambil data terbaru untuk ditampilkan di form
$stmt_get = $conn->prepare("
    SELECT p.pengguna_id, p.username, p.nama_pengguna, p.email, p.nomor_telepon,
        CASE WHEN a.admin_id IS NOT NULL THEN 'admin' WHEN s.seller_id IS NOT NULL THEN 'seller' ELSE 'buyer' END AS role_pengguna
    FROM pengguna p
    LEFT JOIN admin a ON p.pengguna_id = a.pengguna_id
    LEFT JOIN seller s ON p.pengguna_id = s.pengguna_id
    WHERE p.pengguna_id = ?
");
$stmt_get->bind_param("i", $id);
$stmt_get->execute();
$result = $stmt_get->get_result();
if ($result->num_rows === 0) die("Pengguna tidak ditemukan.");
$data = $result->fetch_assoc();
$stmt_get->close();
?>
<h2 class="mb-4">Edit Pengguna: <?= htmlspecialchars($data['username']) ?></h2>

<?php if ($success_msg) echo "<div class='alert alert-success'>$success_msg</div>"; ?>
<?php if ($error_msg) echo "<div class='alert alert-danger'>$error_msg</div>"; ?>

<?php if (empty($success_msg)): ?>
<form method="POST">
    <input type="hidden" name="current_role" value="<?= htmlspecialchars($data['role_pengguna']) ?>">
    <div class="mb-3"><label>Username</label><input type="text" name="username" value="<?= htmlspecialchars($data['username']) ?>" class="form-control"></div>
    <div class="mb-3"><label>Nama Pengguna</label><input type="text" name="nama_pengguna" value="<?= htmlspecialchars($data['nama_pengguna']) ?>" class="form-control"></div>
    <div class="mb-3"><label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($data['email']) ?>" class="form-control"></div>
    <div class="mb-3"><label>Telepon</label><input type="text" name="telepon" value="<?= htmlspecialchars($data['nomor_telepon']) ?>" class="form-control"></div>
    <div class="mb-3">
        <label>Role</label>
        <select name="role" class="form-control" <?= $data['role_pengguna'] == 'admin' ? 'disabled' : '' ?>>
            <option value="seller" <?= $data['role_pengguna'] == 'seller' ? 'selected' : '' ?>>Seller</option>
            <option value="buyer" <?= $data['role_pengguna'] == 'buyer' ? 'selected' : '' ?>>Buyer</option>
        </select>
        <?php if ($data['role_pengguna'] == 'admin') echo '<small class="form-text text-muted">Role Admin tidak dapat diubah.</small>'; ?>
    </div>
    <button type="submit" class="btn btn-primary" name="update" <?= $data['role_pengguna'] == 'admin' ? 'disabled' : '' ?>>Update</button>
    <a href="#" class="btn btn-secondary sidebar-link" data-page="kelola_user.php">Kembali</a>
</form>
<?php endif; ?>