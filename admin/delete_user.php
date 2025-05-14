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
    // First check if user has any orders
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE user_id = ?');
    $stmt->execute([$id]);
    $orderCount = $stmt->fetchColumn();

    if ($orderCount > 0) {
        $_SESSION['error'] = 'Cannot delete user with existing orders';
        header('Location: dashboard.php');
        exit;
    }

    // Delete the user
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ? AND role = "client"');
    $stmt->execute([$id]);
    
    // Redirect back to dashboard with success message
    $_SESSION['success'] = 'User deleted successfully';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error deleting user';
}

header('Location: dashboard.php');
exit; 