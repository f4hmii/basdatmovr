<?php
// File: admincontrol/kelola_kategori.php
session_start();
include '../db_connection.php';

if (!isset($_SESSION['pengguna_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403); die("Akses ditolak.");
}

$error_msg = '';
$success_msg = '';

// Logika untuk Tambah Kategori
if (isset($_POST['tambah'])) {
    $nama_kategori = htmlspecialchars($_POST['nama_kategori']);
    if (!empty($nama_kategori)) {
        $stmt_check = $conn->prepare("SELECT kategori_id FROM kategori WHERE nama_kategori = ?");
        $stmt_check->bind_param("s", $nama_kategori);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows > 0) {
            $error_msg = "Kategori sudah ada.";
        } else {
            $stmt_insert = $conn->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
            $stmt_insert->bind_param("s", $nama_kategori);
            if ($stmt_insert->execute()) $success_msg = "Kategori berhasil ditambahkan.";
            else $error_msg = "Gagal menambah kategori.";
            $stmt_insert->close();
        }
        $stmt_check->close();
    } else {
        $error_msg = "Nama kategori tidak boleh kosong.";
    }
}

// Logika untuk Hapus Kategori
if (isset($_GET['hapus_id'])) {
    $id_hapus = intval($_GET['hapus_id']);
    // Peringatan: Menghapus kategori bisa menyebabkan produk kehilangan kategorinya.
    // Sebaiknya berikan peringatan atau cegah penghapusan jika ada produk yang menggunakan kategori ini.
    $stmt_hapus = $conn->prepare("DELETE FROM kategori WHERE kategori_id = ?");
    $stmt_hapus->bind_param("i", $id_hapus);
    if ($stmt_hapus->execute()) $success_msg = "Kategori berhasil dihapus.";
    else $error_msg = "Gagal menghapus kategori.";
    $stmt_hapus->close();
}

// Ambil semua kategori untuk ditampilkan
$result = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori");
?>
<h2 class="mb-4">Kelola Kategori</h2>

<?php if ($success_msg) echo "<div class='alert alert-success'>$success_msg</div>"; ?>
<?php if ($error_msg) echo "<div class='alert alert-danger'>$error_msg</div>"; ?>

<div class="row">
    <div class="col-md-8">
        <h5>Daftar Kategori</h5>
        <table class="table table-bordered table-striped">
            <thead class="table-dark"><tr><th>ID</th><th>Nama Kategori</th><th>Aksi</th></tr></thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['kategori_id'] ?></td>
                        <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                        <td><a href="dashbord_admin.php#kelola_kategori&hapus_id=<?= $row['kategori_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus kategori ini?')">Hapus</a></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="text-center">Belum ada kategori.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="col-md-4">
        <h5>Tambah Kategori Baru</h5>
        <form method="POST" action="dashbord_admin.php#kelola_kategori">
            <div class="mb-3">
                <label>Nama Kategori</label>
                <input type="text" name="nama_kategori" class="form-control" required>
            </div>
            <button type="submit" name="tambah" class="btn btn-primary">Tambah</button>
        </form>
    </div>
</div>