<?php
session_start();
require __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user'])) {
  header('Location: /maison_cravate/auth/login.php');
  exit;
}

$userId = $_SESSION['user']['id'];
$stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="container mt-5 pt-5">
  <h2 class="text-center mb-4">Your Order History</h2>
  <?php if (empty($orders)): ?>
    <div class="alert alert-info text-center">You have no past orders.</div>
  <?php else: ?>
    <table class="table table-bordered table-hover mt-3">
      <thead class="table-dark">
        <tr>
          <th>Order #</th>
          <th>Date</th>
          <th>Total (DZD)</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td><?= $o['id'] ?></td>
            <td><?= date('Y-m-d H:i', strtotime($o['created_at'])) ?></td>
            <td><?= number_format($o['total'], 2) ?></td>
            <td><span class="badge bg-success text-uppercase"><?= ucfirst($o['status']) ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>