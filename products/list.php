<?php
// File: /maison_cravate/products/list.php
// Displays all ties available in the catalog

require __DIR__ . '/../config/db.php';

// Fetch all products
$stmt = $pdo->query('SELECT * FROM products ORDER BY id DESC');
$products = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="container mt-5">
  <h2 class="mb-4">Our Ties Collection</h2>
  <div class="row">
    <?php foreach ($products as $product): ?>
      <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">
          <img 
            src="/maison_cravate/assets/img/<?= htmlspecialchars($product['image']) ?>" 
            class="card-img-top" 
            alt="<?= htmlspecialchars($product['name']) ?>"
          >
          <div class="card-body d-flex flex-column">
            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
            <p class="card-text"><?= number_format($product['price'], 2) ?> DZD</p>
            <a 
              href="/maison_cravate/products/detail.php?id=<?= $product['id'] ?>" 
              class="btn btn-primary mt-auto"
            >
              View Details
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
