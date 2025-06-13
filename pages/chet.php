<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Chat Pembeli & Penjual</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f0f0f0;
      padding: 20px;
    }
    .chat-container {
    max-width: 600px;
    margin: auto;
    display: flex;
    flex-direction: column;
    height: 90vh; 
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}
    .chat-bubble {
      padding: 10px 15px;
    margin: 10px 10px;
    border-radius: 20px;
    max-width: 70%;
    clear: both;
    position: relative;
    }
	.chat-messages {
    flex: 1; 
    overflow-y: auto;
    padding: 10px;
    background: #f0f0f0;
}

    .from-buyer {
      background-color:rgb(180, 186, 196);
      float: right;
      text-align: right;
    }
    .from-seller {
      background-color: #eeeeee;
      float: left;
      text-align: left;
    }
    .time {
      font-size: 10px;
      color: #888;
      margin-top: 5px;
    }
    form {
    display: flex;
    gap: 10px;
    padding: 10px;
    background: #fff;
    border-top: 1px solid #ccc;
}
    input[type="text"], input[type="number"], select {
      flex: 1;
      padding: 10px;
      border-radius: 10px;
      border: 1px solid #ccc;
    }
    button {
      padding: 10px 20px;
      border: none;
      background-color:rgb(74, 81, 74);
      color: white;
      border-radius: 10px;
      cursor: pointer;
    }
    .action-buttons {
      margin-top: 5px;
    }
    .action-buttons a {
      margin-right: 10px;
      text-decoration: none;
      color: white;
      background:rgb(169, 182, 170);
      padding: 5px 10px;
      border-radius: 5px;
    }
    .reject {
      background: #f44336;
    }
  </style>
</head>
<body>
<div class="chat-container">
    <!-- Bagian Pesan -->
    <div class="chat-messages">
        <?php
        if (!empty($_SESSION['chat'])) {
            foreach ($_SESSION['chat'] as $index => $c) {
                $class = $c['sender'] === 'pembeli' ? 'from-buyer' : 'from-seller';
                echo "<div class='chat-bubble $class'>";
                echo htmlspecialchars($c['message']);
                echo "<div class='time'>{$c['time']}</div>";

                // Kalau ini pesan tawaran dan masih pending
                if ($c['type'] === 'tawar' && $c['status'] === 'pending' && $c['sender'] === 'pembeli') {
                    echo "<div class='action-buttons'>";
                    echo "<a href='chat_handler.php?action=accept&index=$index'>Terima</a>";
                    echo "<a href='chat_handler.php?action=reject&index=$index' class='reject'>Tolak</a>";
                    echo "</div>";
                }
                // Kalau tawaran sudah diterima atau ditolak
                if ($c['type'] === 'tawar' && $c['status'] !== 'pending') {
                    echo "<div class='time'>Status: " . strtoupper($c['status']) . "</div>";
                }

                echo "</div>";
            }
        } else {
            echo "<p>Belum ada chat.</p>";
        }
        ?>
    </div>

    <!-- Form Input -->
   <form action="chat_handler.php" method="post">
  
  <input type="hidden" name="sender" value="pembeli">

  <select name="type" required onchange="toggleTawar(this.value)">
    <option value="biasa">Pesan Biasa</option>
    <option value="tawar">Tawaran</option>
  </select>

  <input type="text" name="message" id="pesanInput" placeholder="Ketik pesan..." required>
  <input type="number" name="tawar_harga" id="hargaInput" placeholder="Masukkan Harga Tawar" style="display:none;">
  <button type="submit">Kirim</button>
</form>

</div>
<script>
function toggleTawar(value) {
  if (value === 'tawar') {
    document.getElementById('pesanInput').style.display = 'none';
    document.getElementById('hargaInput').style.display = 'block';
    document.getElementById('hargaInput').required = true;
    document.getElementById('pesanInput').required = false;
  } else {
    document.getElementById('pesanInput').style.display = 'block';
    document.getElementById('hargaInput').style.display = 'none';
    document.getElementById('hargaInput').required = false;
    document.getElementById('pesanInput').required = true;
  }
}
</script>

</body>
</html>
