<?php
session_start();
require __DIR__ . '/../config/db.php';
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// Initialize variables
$name = $desc = $material = $imgName = '';
$price = $stock = 0;
$editing = false;

// If editing, fetch product data
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $editing = true;
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if ($product) {
        $name = $product['name'];
        $desc = $product['description'];
        $material = $product['material'];
        $imgName = $product['image'];
        $price = $product['price'];
        $stock = $product['stock'];
    } else {
        header('Location: dashboard.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name']);
    $desc        = trim($_POST['description']);
    $price       = floatval($_POST['price']);
    $stock       = intval($_POST['stock']);
    $material    = trim($_POST['material']);
    $imgName     = $editing ? $imgName : '';
    $uploadDir   = __DIR__ . '/../assets/img/';

    // Handle image upload if a new image is provided
    if (isset($_FILES['image']) && $_FILES['image']['tmp_name']) {
        $imgName = $_FILES['image']['name'];
        $tmpPath = $_FILES['image']['tmp_name'];
        $targetPath = $uploadDir . basename($imgName);
        if (!move_uploaded_file($tmpPath, $targetPath)) {
            $error = "Image upload failed.";
        }
    }

    if (!isset($error)) {
        if ($editing) {
            // Update existing product
            $stmt = $pdo->prepare('UPDATE products SET name=?, description=?, price=?, stock=?, material=?, image=? WHERE id=?');
            $stmt->execute([$name, $desc, $price, $stock, $material, $imgName, $id]);
        } else {
            // Insert new product
            $stmt = $pdo->prepare(
              'INSERT INTO products (name, description, price, stock, material, image)
               VALUES (?,?,?,?,?,?)'
            );
            $stmt->execute([$name, $desc, $price, $stock, $material, $imgName]);
        }
        header('Location: dashboard.php');
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container mt-5" style="max-width:600px;">
  <h2><?= $editing ? 'Edit Tie' : 'Add New Tie' ?></h2>
  <?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>
  <form method="post" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label">Name</label>
      <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($name) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Material</label>
      <input type="text" name="material" class="form-control" required value="<?= htmlspecialchars($material) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control" rows="4" required><?= htmlspecialchars($desc) ?></textarea>
    </div>
    <div class="mb-3 row">
      <div class="col">
        <label class="form-label">Price (DZD)</label>
        <input type="number" step="0.01" name="price" class="form-control" required value="<?= htmlspecialchars($price) ?>">
      </div>
      <div class="col">
        <label class="form-label">Stock</label>
        <input type="number" name="stock" class="form-control" required value="<?= htmlspecialchars($stock) ?>">
      </div>
    </div>
    <div class="mb-3">
      <label class="form-label">Image <?= $editing && $imgName ? '(leave blank to keep current)' : '' ?></label>
      <input type="file" name="image" class="form-control" accept="image/*" <?= $editing ? '' : 'required' ?>>
      <?php if ($editing && $imgName): ?>
        <div class="mt-2"><img src="/maison_cravate/assets/img/<?= htmlspecialchars($imgName) ?>" alt="Current Image" height="60"></div>
      <?php endif; ?>
    </div>
    <button class="btn btn-success">Save Tie</button>
  </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
