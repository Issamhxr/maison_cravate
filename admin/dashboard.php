<?php
session_start();
require __DIR__ . '/../config/db.php';
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// Fetch stats
$totalProducts = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalOrders   = $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$totalClients  = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'client'")->fetchColumn();
$totalRevenue  = $pdo->query('SELECT SUM(total) FROM orders WHERE status = "validated"')->fetchColumn() ?: 0;

// Fetch all products
$stmt = $pdo->query('SELECT * FROM products ORDER BY id DESC');
$products = $stmt->fetchAll();

// Fetch all orders with client info
$orders = $pdo->query('SELECT o.*, u.email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC')->fetchAll();

// Fetch all users
$users = $pdo->query("SELECT * FROM users WHERE role = 'client' ORDER BY id DESC")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="container mt-5">
  <h2>Admin Dashboard</h2>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= $_SESSION['success'] ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= $_SESSION['error'] ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <!-- Stats Cards -->
  <div class="row mb-4 g-2">
    <div class="col-6 col-md-3 mb-2">
      <div class="card text-center shadow-sm h-100">
        <div class="card-body">
          <h5 class="card-title">Products</h5>
          <p class="display-6 fw-bold"><?= $totalProducts ?></p>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3 mb-2">
      <div class="card text-center shadow-sm h-100">
        <div class="card-body">
          <h5 class="card-title">Orders</h5>
          <p class="display-6 fw-bold"><?= $totalOrders ?></p>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3 mb-2">
      <div class="card text-center shadow-sm h-100">
        <div class="card-body">
          <h5 class="card-title">Clients</h5>
          <p class="display-6 fw-bold"><?= $totalClients ?></p>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3 mb-2">
      <div class="card text-center shadow-sm h-100">
        <div class="card-body">
          <h5 class="card-title">Revenue (DZD)</h5>
          <p class="display-6 fw-bold"><?= number_format($totalRevenue, 2) ?></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabs -->
  <ul class="nav nav-tabs mb-4 flex-wrap" id="admintabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="products-tab" data-bs-toggle="tab" data-bs-target="#products" type="button" role="tab">Products</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab">Orders</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">Users</button>
    </li>
  </ul>
  <div class="tab-content" id="admintabsContent">
    <!-- Products Tab -->
    <div class="tab-pane fade show active" id="products" role="tabpanel">
      <a href="add_product.php" class="btn btn-success mb-3">Add New Tie</a>
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>ID</th><th>Name</th><th>Price</th><th>Stock</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
              <td><?= $p['id'] ?></td>
              <td><?= htmlspecialchars($p['name']) ?></td>
              <td><?= number_format($p['price'],2) ?> DZD</td>
              <td><?= $p['stock'] ?></td>
              <td>
                <a href="add_product.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                <button type="button" class="btn btn-sm btn-danger" 
                        onclick="if(confirm('Are you sure you want to delete this product?')) window.location.href='delete_product.php?id=<?= $p['id'] ?>'">
                  Delete
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <!-- Orders Tab -->
    <div class="tab-pane fade" id="orders" role="tabpanel">
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>Order #</th>
              <th>Client Email</th>
              <th>Total (DZD)</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $o): ?>
            <tr>
              <td><?= $o['id'] ?></td>
              <td><?= htmlspecialchars($o['email']) ?></td>
              <td><?= number_format($o['total'],2) ?></td>
              <td><?= ucfirst($o['status']) ?></td>
              <td><?= date('Y-m-d H:i', strtotime($o['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    
    <!-- Users Tab -->
    <div class="tab-pane fade" id="users" role="tabpanel">
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Email</th>
              <th>Name</th>
              <th>Phone</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
              <td><?= $u['id'] ?></td>
              <td><?= htmlspecialchars($u['email']) ?></td>
              <td><?= isset($u['username']) ? htmlspecialchars($u['username']) : '-' ?></td>
              <td><?= isset($u['phone']) ? htmlspecialchars($u['phone']) : '-' ?></td>
              <td>
                <button type="button" class="btn btn-sm btn-danger" 
                        onclick="if(confirm('Are you sure you want to delete this user?')) window.location.href='delete_user.php?id=<?= $u['id'] ?>'">
                  Delete
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>