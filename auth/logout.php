<?php
session_start();
session_destroy();
header('Location: /maison_cravate/index.php');
exit;
?>
