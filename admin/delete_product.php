<?php
session_start();
require __DIR__ . '/../config/db.php';

// Check if user is admin
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$id = (int)$_GET['id'];

try {
    // Delete the product
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([$id]);
    
    // Redirect back to dashboard with success message
    $_SESSION['success'] = 'Product deleted successfully';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error deleting product';
}

header('Location: dashboard.php');
exit; 