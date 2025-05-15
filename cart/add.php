<?php
// File: /maison_cravate/cart/add.php
// Handles adding a tie to the user's session-based cart

session_start();
require __DIR__ . '/../config/db.php';

// Ensure request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /maison_cravate/products/list.php');
    exit;
}

// Retrieve and validate inputs
$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT, [
    'options' => ['default' => 1, 'min_range' => 1]
]);

if (!$productId || $quantity < 1) {
    header('Location: /maison_cravate/products/detail.php?id=' . $productId);
    exit;
}

// Validate product existence in the database
$stmt = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: /maison_cravate/products/list.php');
    exit;
}

// Check stock availability
if ($quantity > $product['stock']) {
    header('Location: /maison_cravate/products/detail.php?id=' . $productId . '&error=stock');
    exit;
}

// Initialize cart in session if not present
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add or update quantity in the cart
if (isset($_SESSION['cart'][$productId])) {
    $_SESSION['cart'][$productId] += $quantity;
} else {
    $_SESSION['cart'][$productId] = $quantity;
}

// Redirect to cart view
header('Location: /maison_cravate/cart/view.php');
exit;
?>