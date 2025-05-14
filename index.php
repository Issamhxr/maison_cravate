<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/config/config.php';

// Fetch 4 featured ties (most recent)
$stmt = $pdo->query('SELECT * FROM products ORDER BY id DESC LIMIT 4');
$featured = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="jumbotron">
  <div class="text-center">
    <h1 class="display-4 mt-4">Welcome to Maison de la Cravate Algerie</h1>
    <a class="btn btn-lg mt-4" href="../maison_cravate/products/list.php">Shop Now</a>
  </div>
</div>

<h2 class="mt-5 mb-4">Featured Ties</h2>
<div class="row products">
  <?php foreach ($featured as $tie): ?>
    <div class="col-md-3 mb-4">
      <div class="card h-100 shadow-sm">
        <img src="<?php echo url('assets/img/' . htmlspecialchars($tie['image'])); ?>" class="card-img-top"
          alt="<?= htmlspecialchars($tie['name']) ?>">
        <div class="card-body d-flex flex-column">
          <h5 class="card-title"><?= htmlspecialchars($tie['name']) ?></h5>
          <p class="card-text"><?= number_format($tie['price'], 2) ?> DZD</p>
          <a href="<?php echo url('products/detail.php?id=' . $tie['id']); ?>" class="btn btn-primary mt-auto">View
            Details</a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>