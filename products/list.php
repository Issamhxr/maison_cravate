<?php
// File: /maison_cravate/products/list.php
// Displays all ties available in the catalog

require __DIR__ . '/../config/db.php';

// Multi-criteria search
$name = isset($_GET['name']) ? trim($_GET['name']) : '';
$minPrice = isset($_GET['min_price']) ? (float) $_GET['min_price'] : '';
$maxPrice = isset($_GET['max_price']) ? (float) $_GET['max_price'] : '';
$material = isset($_GET['material']) ? trim($_GET['material']) : '';

$where = [];
$params = [];
if ($name !== '') {
  $where[] = 'name LIKE ?';
  $params[] = "%$name%";
} else {
  if ($minPrice !== '') {
    $where[] = 'price >= ?';
    $params[] = $minPrice;
  }
  if ($maxPrice !== '') {
    $where[] = 'price <= ?';
    $params[] = $maxPrice;
  }
}
if ($material !== '') {
  $where[] = 'material LIKE ?';
  $params[] = "%$material%";
}

$sql = 'SELECT * FROM products';
if ($where) {
  $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Ensure proper layout structure for sticky footer
?>
<div class="wrapper">
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main>
    <div class="container mt-5">
      <h2 class="mb-4">Our Ties Collection</h2>
      <form method="get" class="row g-2 mb-4">
        <div class="col-md-3">
          <input type="text" name="name" class="form-control" placeholder="Name" value="<?= htmlspecialchars($name) ?>">
        </div>
        <div class="col-md-2">
          <input type="number" step="0.01" name="min_price" class="form-control" placeholder="Min Price"
            value="<?= htmlspecialchars($minPrice) ?>">
        </div>
        <div class="col-md-2">
          <input type="number" step="0.01" name="max_price" class="form-control" placeholder="Max Price"
            value="<?= htmlspecialchars($maxPrice) ?>">
        </div>
        <div class="col-md-3 d-none">
          <input type="text" name="material" class="form-control" placeholder="Material"
            value="<?= htmlspecialchars($material) ?>">
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">Search</button>
        </div>
      </form>
      <div class="row products">
        <?php foreach ($products as $product): ?>
          <div class="col-md-3 col-sm-6 mb-4">
            <div class="card h-100 shadow-sm">
              <img src="/maison_cravate/assets/img/<?= htmlspecialchars($product['image']) ?>" class="card-img-top"
                alt="<?= htmlspecialchars($product['name']) ?>">
              <div class="card-body d-flex flex-column">
                <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                <p class="card-text"><?= number_format($product['price'], 2) ?> DZD</p>
                <a href="/maison_cravate/products/detail.php?id=<?= $product['id'] ?>" class="btn btn-primary mt-auto">
                  View Details
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>