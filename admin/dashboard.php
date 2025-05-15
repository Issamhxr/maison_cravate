<?php
session_start();
require __DIR__ . '/../config/db.php';
if (!isset($_SESSION['admin'])) {
  header('Location: login.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
  $orderId = (int) $_POST['order_id'];
  $status = $_POST['status'];

  // Validate status
  $validStatuses = ['pending', 'validated', 'done'];
  if (in_array($status, $validStatuses)) {
    $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $stmt->execute([$status, $orderId]);
    $_SESSION['success'] = "Order #$orderId status updated to $status.";
  } else {
    $_SESSION['error'] = "Invalid status selected.";
  }

  header('Location: dashboard.php?tab=orders');
  exit;
}

// Handle order deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_order_id'])) {
  $orderId = (int) $_POST['delete_order_id'];
  try {
    // Delete order items first (to maintain referential integrity)
    $stmt = $pdo->prepare('DELETE FROM order_items WHERE order_id = ?');
    $stmt->execute([$orderId]);
    // Delete the order itself
    $stmt = $pdo->prepare('DELETE FROM orders WHERE id = ?');
    $stmt->execute([$orderId]);
    $_SESSION['success'] = "Order #$orderId deleted successfully.";
  } catch (PDOException $e) {
    $_SESSION['error'] = "Failed to delete order: " . $e->getMessage();
  }
  header('Location: dashboard.php?tab=orders');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['new_email']) || isset($_POST['new_password']))) {
  $newEmail = isset($_POST['new_email']) ? trim($_POST['new_email']) : null;
  $oldPassword = isset($_POST['old_password']) ? trim($_POST['old_password']) : null;
  $newPassword = isset($_POST['new_password']) ? trim($_POST['new_password']) : null;

  try {
    if ($newPassword) {
      // Verify old password
      $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ? AND role = "admin"');
      $stmt->execute([$_SESSION['admin']['id']]);
      $admin = $stmt->fetch();

      if (!$admin || !password_verify($oldPassword, $admin['password'])) {
        $_SESSION['error'] = "Old password is incorrect.";
        header('Location: dashboard.php?tab=settings');
        exit;
      }

      // Update password
      $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
      $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ? AND role = "admin"');
      $stmt->execute([$hashedPassword, $_SESSION['admin']['id']]);
      $_SESSION['success'] = "Password updated successfully.";
    }

    if ($newEmail) {
      $stmt = $pdo->prepare('UPDATE users SET email = ? WHERE id = ? AND role = "admin"');
      $stmt->execute([$newEmail, $_SESSION['admin']['id']]);
      $_SESSION['admin']['email'] = $newEmail;
      $_SESSION['success'] = "Email updated successfully.";
    }

    header('Location: dashboard.php?tab=settings');
    exit;
  } catch (PDOException $e) {
    $_SESSION['error'] = "An error occurred: " . $e->getMessage();
    header('Location: dashboard.php?tab=settings');
    exit;
  }
}

// Determine active tab
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'products';

// Fetch stats
$totalProducts = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalOrders = $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$totalClients = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'client'")->fetchColumn();
$totalRevenue = $pdo->query('SELECT SUM(total) FROM orders WHERE status IN ( "done")')->fetchColumn() ?: 0;

// Fetch all products
$stmt = $pdo->query('SELECT * FROM products ORDER BY id DESC');
$products = $stmt->fetchAll();

// Fetch all orders (one row per order, with total quantity of items, and first product name/image)
$orders = $pdo->query('SELECT o.*, u.username, o.phone, o.wilaya, o.address, o.total, o.status, o.created_at, (SELECT SUM(quantity) FROM order_items WHERE order_id = o.id) AS total_quantity, (SELECT p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = o.id LIMIT 1) AS product_name, (SELECT p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = o.id LIMIT 1) AS product_image FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC')->fetchAll();

// Fetch all users
$users = $pdo->query("SELECT * FROM users WHERE role = 'client' ORDER BY id DESC")->fetchAll();

// Ensure proper layout structure for sticky footer
?>
<div class="wrapper">
  <?php include __DIR__ . '/../includes/header.php'; ?>

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
        <button class="nav-link <?= $activeTab === 'products' ? 'active' : '' ?>" id="products-tab" data-bs-toggle="tab"
          data-bs-target="#products" type="button" role="tab">Products</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link <?= $activeTab === 'orders' ? 'active' : '' ?>" id="orders-tab" data-bs-toggle="tab"
          data-bs-target="#orders" type="button" role="tab">Orders</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link <?= $activeTab === 'users' ? 'active' : '' ?>" id="users-tab" data-bs-toggle="tab"
          data-bs-target="#users" type="button" role="tab">Users</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link <?= $activeTab === 'settings' ? 'active' : '' ?>" id="settings-tab" data-bs-toggle="tab"
          data-bs-target="#settings" type="button" role="tab">Settings</button>
      </li>
    </ul>
    <div class="tab-content" id="admintabsContent">
      <!-- Products Tab -->
      <div class="tab-pane fade <?= $activeTab === 'products' ? 'show active' : '' ?>" id="products" role="tabpanel">
        <a href="add_product.php" class="btn btn-success mb-3">Add New Tie</a>
        <div class="table-responsive">
          <table class="table table-bordered table-hover align-middle text-center">
            <thead class="table-dark">
              <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($products as $p): ?>
                <tr>
                  <td><strong><?= $p['id'] ?></strong></td>
                  <td><img src="/maison_cravate/assets/img/<?= htmlspecialchars($p['image']) ?>" alt="Product Image"
                      style="width: 50px; height: auto;"></td>
                  <td><?= htmlspecialchars($p['name']) ?></td>
                  <td><strong><?= number_format($p['price'], 2) ?> DZD</strong></td>
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
      <div class="tab-pane fade <?= $activeTab === 'orders' ? 'show active' : '' ?>" id="orders" role="tabpanel">
        <div class="table-responsive">
          <table class="table table-bordered table-hover align-middle text-center">
            <thead class="table-dark">
              <tr>
                <th>Order #</th>
                <th>Client Name</th>
                <th>Phone</th>
                <th>Wilaya</th>
                <th>Address</th>
                <th>Quantity</th>
                <th>Total (DZD)</th>
                <th>Product</th>
                <th>Image</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($orders as $o): ?>
                <tr>
                  <td><strong><?= $o['id'] ?></strong></td>
                  <td><?= htmlspecialchars($o['username']) ?></td>
                  <td><?= htmlspecialchars($o['phone']) ?></td>
                  <td><?= htmlspecialchars($o['wilaya']) ?></td>
                  <td><?= htmlspecialchars($o['address']) ?></td>
                  <td><?= $o['total_quantity'] ?></td>
                  <td><strong><?= number_format($o['total'], 2) ?></strong></td>
                  <td><?= htmlspecialchars($o['product_name']) ?></td>
                  <td><img src="/maison_cravate/assets/img/<?= htmlspecialchars($o['product_image']) ?>"
                      alt="Product Image" style="width: 50px; height: auto;"></td>
                  <td>
                    <form method="post" style="display: inline-block;">
                      <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                      <select name="status" class="form-select form-select-sm" style="width: auto; display: inline-block;"
                        onchange="this.form.submit()">
                        <option value="pending" <?= $o['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="validated" <?= $o['status'] === 'validated' ? 'selected' : '' ?>>Validated</option>
                        <option value="done" <?= $o['status'] === 'done' ? 'selected' : '' ?>>Done</option>
                      </select>
                    </form>
                  </td>
                  <td><?= date('Y-m-d H:i', strtotime($o['created_at'])) ?></td>
                  <td>
                    <form method="post" onsubmit="return confirm('Are you sure you want to delete this order?');"
                      style="display:inline-block;">
                      <input type="hidden" name="delete_order_id" value="<?= $o['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Users Tab -->
      <div class="tab-pane fade <?= $activeTab === 'users' ? 'show active' : '' ?>" id="users" role="tabpanel">
        <div class="table-responsive">
          <table class="table table-bordered table-hover align-middle text-center">
            <thead class="table-dark">
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
                  <td><strong><?= $u['id'] ?></strong></td>
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

      <!-- Settings Tab -->
      <div class="tab-pane fade <?= $activeTab === 'settings' ? 'show active' : '' ?>" id="settings" role="tabpanel">
        <h3>Admin Settings</h3>
        <form method="post" action="dashboard.php?tab=settings" class="mt-4">
          <div class="mb-3">
            <label for="newEmail" class="form-label">New Email</label>
            <input type="email" class="form-control" id="newEmail" name="new_email" placeholder="Enter new email">
          </div>
          <div class="mb-3">
            <label for="oldPassword" class="form-label">Old Password</label>
            <input type="password" class="form-control" id="oldPassword" name="old_password"
              placeholder="Enter old password" required>
          </div>
          <div class="mb-3">
            <label for="newPassword" class="form-label">New Password</label>
            <input type="password" class="form-control" id="newPassword" name="new_password"
              placeholder="Enter new password" required>
          </div>
          <button type="submit" class="btn btn-primary">Update</button>
        </form>
      </div>
    </div>
  </div>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>