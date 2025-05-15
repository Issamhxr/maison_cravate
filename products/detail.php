<?php
// File: /maison_cravate/products/detail.php
// Displays a single tie's details and allows adding to cart

require __DIR__ . '/../config/db.php';

// Validate product ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header('Location: /maison_cravate/products/list.php');
  exit;
}
$productId = (int) $_GET['id'];

// Fetch product info
$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
  echo "<div class='container mt-5'><div class='alert alert-danger'>Product not found.</div></div>";
  include __DIR__ . '/../includes/footer.php';
  exit;
}
?>

<div class="wrapper">
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="container mt-5 pt-5">
    <div class="row">
      <div class="col-md-6">
        <img src="/maison_cravate/assets/img/<?= htmlspecialchars($product['image']) ?>"
          class="img-fluid rounded shadow-sm" alt="<?= htmlspecialchars($product['name']) ?>">
      </div>
      <div class="col-md-6">
        <h2 class="text-primary mb-3"><?= htmlspecialchars($product['name']) ?></h2>
        <p class="text-muted mb-2">Material: <?= htmlspecialchars($product['material']) ?></p>
        <h4 class="text-success mb-4">Price: <?= number_format($product['price'], 2) ?> DZD</h4>
        <p class="mb-4">Description: <?= nl2br(htmlspecialchars($product['description'])) ?></p>

        <?php if ($product['stock'] > 0): ?>
          <form action="/maison_cravate/cart/add.php" method="post" class="mt-4">
            <input type="hidden" name="product_id" value="<?= $productId ?>">
            <div class="mb-3">
              <label for="qty" class="form-label">Quantity</label>
              <input type="number" id="qty" name="quantity" class="form-control" value="1" min="1"
                max="<?= $product['stock'] ?>">
            </div>
            <button type="submit" class="btn btn-success btn-lg w-100">
              <i class="fas fa-cart-plus"></i> Add to Cart
            </button>
          </form>
        <?php else: ?>
          <div class="alert alert-warning mt-4">Out of stock.</div>
        <?php endif; ?>

        <a href="/maison_cravate/products/list.php" class="btn btn-link mt-3">
          &laquo; Back to collection
        </a>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>