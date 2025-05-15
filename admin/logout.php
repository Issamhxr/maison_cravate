<?php
session_start();
unset($_SESSION['admin']);
session_write_close();

header('Location: /maison_cravate/index.php');
exit;
?>