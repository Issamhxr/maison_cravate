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
  echo '<div style="margin-top: 20px;">CART</div>';
  echo '<div class="container mt-5 "><div class="alert alert-info" >Your cart is empty.</div>';
  echo '<a href="/maison_cravate/products/list.php" class="btn btn-primary" style="margin-bottom: 1rem;">Continue Shopping</a>';
  if (isset($_SESSION['user'])) {
    echo '<a href="/maison_cravate/orders/history.php" class="btn btn-info" style="color: white; margin-top: 1rem;">View Order History</a>';
  }
  echo '</div>';
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
    $q = max(1, (int) $q);
    if (isset($_SESSION['cart'][$pid])) {
      $_SESSION['cart'][$pid] = $q;
    }
  }
  header('Location: view.php');
  exit;
}

include __DIR__ . '/../includes/header.php';
?>

<main>
  <div class="container mt-5">
    <h2 class="text-center mb-4">Your Shopping Cart</h2>
    <form method="post" action="view.php">
      <div style="overflow-x: auto;">
        <table class="table table-bordered table-hover align-middle text-center cart-table">
          <thead class="table-dark">
            <tr>
              <th>Image</th>
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
                  <td><img src="/maison_cravate/assets/img/<?= htmlspecialchars($p['image']) ?>" alt="Product Image"
                      style="width: 50px; height: auto;"></td>
                  <td><?= htmlspecialchars($p['name']) ?></td>
                  <td><?= number_format($p['price'], 2) ?></td>
                  <td>
                    <input type="number" name="qty[<?= $pid ?>]" value="<?= $qty ?>" min="1" max="<?= $p['stock'] ?>"
                      class="form-control" style="width:80px;">
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
              <td colspan="4" class="text-end"><strong>Total:</strong></td>
              <td colspan="2"><strong><?= number_format($total, 2) ?> DZD</strong></td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="d-flex flex-column flex-md-row justify-content-between cart-actions">
        <button type="submit" name="update" class="btn btn-secondary mb-2 mb-md-0">Update Cart</button>
        <a href="/maison_cravate/cart/checkout.php" class="btn btn-success">Proceed to Checkout</a>
      </div>
    </form>
    <div class="mt-4 d-flex flex-column flex-md-row justify-content-between">
      <a href="/maison_cravate/products/list.php" class="btn"
        style="background-color: #c79b48; color: white; margin-bottom: 1rem;">Continue Shopping</a>
    </div>
    <?php if (isset($_SESSION['user'])): ?>
      <div class="mt-4 d-flex flex-column flex-md-row justify-content-between">
        <a href="/maison_cravate/orders/history.php" class="btn btn-info" style="color: white;">View Order History</a>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>