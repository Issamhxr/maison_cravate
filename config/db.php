<?php
// File: /maison_cravate/config/db.php
// PDO connection + session start

$host = 'localhost'; // or use the MySQL host shown in Hostinger hPanel (often 'localhost')
$db = 'maison_cravate_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES => false,
];

try {
  $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
  exit('Database Connection Failed: ' . $e->getMessage());
}


//Issam,54321 maison_cravate