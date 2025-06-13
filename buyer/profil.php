<?php
session_start();
include "../view/header.php";
include "../db_connection.php";

if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}

$username = $_SESSION['username'];

$query = "SELECT pengguna_id, nama_pengguna, email, role, username FROM pengguna WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

$alamat = "Belum ada alamat default";
$semua_alamat = [];
$status_message = "";

if ($result->num_rows > 0) {
  $user = $result->fetch_assoc();
  $pengguna_id = $user['pengguna_id'];

  // Tangani penghapusan alamat
  if (isset($_GET['delete'])) {
    $id_alamat = intval($_GET['delete']);
    $conn->query("DELETE FROM alamat_pengiriman WHERE id = $id_alamat AND pengguna_id = $pengguna_id");
    header("Location: profil.php?status=delete_success");
    exit;
  }

  //  jadikan default
  if (isset($_GET['set_default'])) {
    $id_alamat = intval($_GET['set_default']);
    $conn->query("UPDATE alamat_pengiriman SET is_default = 0 WHERE pengguna_id = $pengguna_id");
    $conn->query("UPDATE alamat_pengiriman SET is_default = 1 WHERE id = $id_alamat AND pengguna_id = $pengguna_id");
    header("Location: profil.php?status=default_success");
    exit;
  }

  //  edit alamat 
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_alamat_id'])) {
    $id_edit = intval($_POST['edit_alamat_id']);
    $alamat_baru = trim($_POST['alamat_edit']);
    if ($alamat_baru === "") {
      $status_message = "Alamat tidak boleh kosong.";
    } else {
      $stmt_edit = $conn->prepare("UPDATE alamat_pengiriman SET alamat = ? WHERE id = ? AND pengguna_id = ?");
      $stmt_edit->bind_param("sii", $alamat_baru, $id_edit, $pengguna_id);
      if ($stmt_edit->execute()) {
        header("Location: profil.php?status=edit_success");
        exit;
      } else {
        $status_message = "Gagal memperbarui alamat: " . $conn->error;
      }
    }
  }

  // Ambil alamat default terbaru
  $alamat_query = "SELECT alamat FROM alamat_pengiriman WHERE pengguna_id = ? AND is_default = 1 LIMIT 1";
  $stmt_alamat = $conn->prepare($alamat_query);
  $stmt_alamat->bind_param("i", $pengguna_id);
  $stmt_alamat->execute();
  $alamat_result = $stmt_alamat->get_result();
  if ($alamat_result->num_rows > 0) {
    $alamat = $alamat_result->fetch_assoc()['alamat'];
  }

  // Ambil semua alamat terbaru
  $semua_alamat_query = "SELECT id, alamat, is_default FROM alamat_pengiriman WHERE pengguna_id = ?";
  $stmt_semua = $conn->prepare($semua_alamat_query);
  $stmt_semua->bind_param("i", $pengguna_id);
  $stmt_semua->execute();
  $semua_result = $stmt_semua->get_result();
  while ($row = $semua_result->fetch_assoc()) {
    $semua_alamat[] = $row;
  }
} else {
  echo "User tidak ditemukan!";
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1" name="viewport" />
  <title>Profile Page</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
</head>

<body class="bg-white text-gray-900 font-sans">
  <!-- Main Content -->
  <main class="flex bg-white">
    <!-- Sidebar -->
    <aside class="bg-gray-100 w-64 p-6 space-y-8">
      <div class="flex items-center space-x-3 border-b border-gray-300 pb-4">
        <i class="fas fa-user-circle text-3xl text-gray-600"></i>
        <div>
          <p class="font-semibold text-gray-700 text-sm">
            <?= htmlspecialchars($user['username']) ?>
          </p>
          <p class="text-xs text-gray-400">Buyer</p>
        </div>
      </div>
      <nav class="space-y-6 text-xs text-gray-500">
        <div>
          <p class="font-semibold text-gray-700 mb-2">Akun Saya</p>
          <ul class="space-y-1">
            <li class="hover:text-gray-700 cursor-pointer">Profile</li>
            <li class="hover:text-gray-700 cursor-pointer">Alamat</li>
            <li class="hover:text-gray-700 cursor-pointer">Ubah Password</li>
          </ul>
        </div>
        <p class="font-semibold text-gray-700 cursor-pointer">Pesanan Saya</p>
        <p class="font-semibold text-gray-700 cursor-pointer">Notifikasi</p>
        <p class="font-semibold text-gray-700 cursor-pointer">Voucher</p>
      </nav>
    </aside>

    <!-- Content Area -->
    <section class="flex-1 p-10">
      <h1 class="font-extrabold text-2xl text-gray-700 mb-1">
        Profil <?= htmlspecialchars($user['nama_pengguna']) ?>
      </h1>
      <p class="text-xs text-gray-400 mb-8">Informasi detail akun kamu</p>

      <?php if (isset($_GET['status'])): ?>
        <?php if ($_GET['status'] === 'edit_success'): ?>
          <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm">
            Alamat berhasil diperbarui!
          </div>
        <?php elseif ($_GET['status'] === 'delete_success'): ?>
          <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm">
            Alamat berhasil dihapus!
          </div>
        <?php elseif ($_GET['status'] === 'default_success'): ?>
          <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm">
            Alamat default berhasil diubah!
          </div>
        <?php endif; ?>
      <?php endif; ?>

      <?php if ($status_message !== ""): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm">
          <?= htmlspecialchars($status_message) ?>
        </div>
      <?php endif; ?>

      <div class="flex space-x-10">
        <!-- Ganti form jadi div agar tidak ada submit -->
        <div class="flex flex-col space-y-6 w-2/3">
          <div class="grid grid-cols-3 items-center border-b border-gray-300 pb-6">
            <label class="text-xs text-gray-700">Username</label>
            <p class="col-span-2 font-semibold text-gray-700"><?= htmlspecialchars($user['username']) ?></p>
          </div>
          <div class="grid grid-cols-3 items-center border-b border-gray-300 pb-6">
            <label class="text-xs text-gray-700" for="nama_pengguna">Nama</label>
            <input id="nama_pengguna" name="nama_pengguna" value="<?= htmlspecialchars($user['nama_pengguna']) ?>" class="col-span-2 border border-gray-300 text-xs font-semibold px-2 py-1 focus:outline-none focus:ring-1 focus:ring-gray-600" placeholder="Nama Lengkap" type="text" disabled />
          </div>
          <div class="grid grid-cols-3 items-center border-b border-gray-300 pb-6">
            <label class="text-xs text-gray-700" for="email">Email</label>
            <p class="col-span-2 font-semibold text-gray-700"><?= htmlspecialchars($user['email']) ?></p>
          </div>
          <div class="grid grid-cols-3 items-start border-b border-gray-300 pb-6">
            <label class="text-xs text-gray-700" for="alamat">Alamat Default</label>
            <textarea id="alamat" name="alamat" rows="3" class="col-span-2 border border-gray-300 text-xs font-semibold px-2 py-1 focus:outline-none focus:ring-1 focus:ring-gray-600" readonly><?= htmlspecialchars($alamat) ?></textarea>
          </div>

          <!-- Semua alamat pengguna -->
          <div class="grid grid-cols-3 items-start border-b border-gray-300 pb-6">
            <label class="text-xs text-gray-700">Semua Alamat</label>
            <div class="col-span-2 space-y-4 text-xs text-gray-700">
              <?php if (count($semua_alamat) > 0): ?>
                <?php foreach ($semua_alamat as $item): ?>
                  <div class="p-2 border border-gray-300 rounded relative <?= $item['is_default'] ? 'bg-green-50' : '' ?>">
                    <form method="post" class="flex flex-col space-y-1" action="profil.php">
                      <textarea name="alamat_edit" class="w-full border px-2 py-1 text-xs" required><?= htmlspecialchars($item['alamat']) ?></textarea>
                      <input type="hidden" name="edit_alamat_id" value="<?= $item['id'] ?>">
                      <div class="flex space-x-2 pt-1">
                        <?php if (!$item['is_default']): ?>
                          <a href="?set_default=<?= $item['id'] ?>" class="text-blue-500 hover:underline">Jadikan Default</a>
                        <?php else: ?>
                          <span class="text-green-600 font-semibold">(Default)</span>
                        <?php endif; ?>
                        <button type="submit" class="text-yellow-600 hover:underline">Simpan</button>
                        <a href="?delete=<?= $item['id'] ?>" class="text-red-500 hover:underline" onclick="return confirm('Hapus alamat ini?')">Hapus</a>
                      </div>
                    </form>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p>Tidak ada alamat tersimpan.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Right image and button -->
        <div class="flex flex-col items-center justify-center border-l border-gray-300 pl-10 w-1/3 space-y-6">
          <div class="rounded-full border border-gray-400 p-4">
            <img alt="Profile picture" class="w-24 h-24 object-cover rounded-full" src="https://storage.googleapis.com/a1aa/image/2cb11cd7-b560-4ba4-cd56-c634ffaad324.jpg" />
          </div>
          <button class="w-40 border border-gray-400 text-xs py-2 rounded-md tracking-widest hover:bg-gray-100" type="button">
            Ubah Foto
          </button>
        </div>
      </div>
    </section>
  </main>

  <?php include "../view/footer.php"; ?>
</body>

</html>