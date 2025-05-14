<?php
session_start();
include __DIR__ . '/../includes/header.php';
?>

<div class="container mt-5">
  <div class="alert alert-success">
    <h4 class="alert-heading">Thank you!</h4>
    <p>Your order has been successfully placed.</p>
    <hr>
    <a href="/maison_cravate/orders/history.php" class="btn btn-primary">View Order History</a>
    <a href="/maison_cravate/products/list.php" class="btn btn-secondary">Continue Shopping</a>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
