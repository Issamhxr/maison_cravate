<?php
session_start();
include __DIR__ . '/../includes/header.php';
require __DIR__ . '/../config/db.php'; // Ensure the database connection is included

// Fetch 4 random products for recommendations
$stmt = $pdo->query('SELECT * FROM products ORDER BY RAND() LIMIT 4');
$recommendations = $stmt->fetchAll();
?>

<div class="container mt-5 pt-5">
  <div class="alert alert-success text-center">
    <h4 class="alert-heading">Thank you!</h4>
    <p>Your order has been successfully placed.</p>
    <hr>
    <div class="d-flex justify-content-center gap-2">
      <a href="/maison_cravate/orders/history.php" class="btn btn-primary">View Order History</a>
      <a href="/maison_cravate/products/list.php" class="btn btn-secondary">Continue Shopping</a>
    </div>
  </div>
</div>

<!-- Add sound effect for order confirmation -->
<audio id="order-success-sound" src="/assets/success.mp3" preload="auto"></audio>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var audio = document.getElementById('order-success-sound');
    if (audio) {
      // Try to play after a short delay for better browser compatibility
      setTimeout(function () {
        audio.play().catch(function (e) { /* ignore autoplay errors */ });
      }, 300);
    }
  });
</script>

<div class="container mt-5">
  <h3 class="mb-4 text-center">You may also like</h3>
  <div class="row products">
    <?php foreach ($recommendations as $product): ?>
      <div class="col-md-3 col-sm-6 mb-4">
        <div class="card h-100 shadow-sm">
          <img src="/maison_cravate/assets/img/<?= htmlspecialchars($product['image']) ?>" class="card-img-top"
            alt="<?= htmlspecialchars($product['name']) ?>">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title text-center"><?= htmlspecialchars($product['name']) ?></h5>
            <p class="card-text text-center font-weight-bold text-primary">
              <?= number_format($product['price'], 2) ?> DZD
            </p>
            <a href="/maison_cravate/products/detail.php?id=<?= $product['id'] ?>"
              class="btn btn-outline-primary mt-auto">
              View Details
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>