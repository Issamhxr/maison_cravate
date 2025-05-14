<?php
// File: /maison_cravate/cart/view.php
// Displays contents of the shopping cart

session_start();
require __DIR__ . '/../config/db.php';

// Ensure the session is started and the cart is available
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];  // Initialize the cart session if not already set
}

// Fetch cart from session
$cart = $_SESSION['cart'];

// If cart is empty, show message
if (empty($cart)) {
    include __DIR__ . '/../includes/header.php';
    echo '<div class="container mt-5"><div class="alert alert-info">Your cart is empty.</div>';
    echo '<a href="/maison_cravate/products/list.php" class="btn btn-primary">Continue Shopping</a></div>';
    include __DIR__ . '/../includes/footer.php';
    exit;
}

// Build a list of product IDs to fetch details in one query
$ids = implode(',', array_keys($cart));
$stmt = $pdo->query("SELECT * FROM products WHERE id IN ($ids)");
$products = $stmt->fetchAll();

// Map products by ID for easy lookup
$byId = [];
foreach ($products as $p) {
    $byId[$p['id']] = $p;
}

// Handle removals/updates via GET params (optional)
if (isset($_GET['remove'])) {
    $rid = (int) $_GET['remove'];
    unset($_SESSION['cart'][$rid]);
    header('Location: view.php');
    exit;
}
if (isset($_POST['update'])) {
    foreach ($_POST['qty'] as $pid => $q) {
        $q = max(1, (int)$q);
        if (isset($_SESSION['cart'][$pid])) {
            $_SESSION['cart'][$pid] = $q;
        }
    }
    header('Location: view.php');
    exit;
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container mt-5">
  <h2>Your Shopping Cart</h2>
  <form method="post" action="view.php">
    <table class="table table-bordered mt-4">
      <thead class="table-light">
        <tr>
          <th>Product</th>
          <th>Price (DZD)</th>
          <th>Quantity</th>
          <th>Subtotal (DZD)</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $total = 0;
        foreach ($cart as $pid => $qty):
            if (isset($byId[$pid])) {
                $p = $byId[$pid];
                $sub = $p['price'] * $qty;
                $total += $sub;
        ?>
        <tr>
          <td><?= htmlspecialchars($p['name']) ?></td>
          <td><?= number_format($p['price'], 2) ?></td>
          <td>
            <input type="number" name="qty[<?= $pid ?>]" value="<?= $qty ?>" min="1" max="<?= $p['stock'] ?>" class="form-control" style="width:80px;">
          </td>
          <td><?= number_format($sub, 2) ?></td>
          <td>
            <a href="view.php?remove=<?= $pid ?>" class="btn btn-sm btn-danger">
              <i class="fas fa-trash"></i>
            </a>
          </td>
        </tr>
        <?php 
            }
        endforeach; 
        ?>
        <tr>
          <td colspan="3" class="text-end"><strong>Total:</strong></td>
          <td colspan="2"><strong><?= number_format($total, 2) ?> DZD</strong></td>
        </tr>
      </tbody>
    </table>

    <div class="d-flex justify-content-between">
      <button type="submit" name="update" class="btn btn-secondary">Update Cart</button>
      <a href="/maison_cravate/cart/checkout.php" class="btn btn-success">Proceed to Checkout</a>
    </div>
  </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
