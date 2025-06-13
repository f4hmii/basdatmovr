<?php
session_start();
session_unset();
session_destroy();
header("Location: ../index.php"); // arahkan ke file di luar folder "pages"
exit;
?>
