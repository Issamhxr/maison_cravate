<?php
session_start();
require __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND role = ?');
    $stmt->execute([$email, 'admin']);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin'] = $admin;
        header('Location: dashboard.php');
        exit;
    } else {
      
        $error = "Invalid admin credentials.";
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container mt-5" style="max-width:400px;">
  <h2>Admin Login</h2>
  <?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>
  <form method="post">
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <button class="btn btn-primary">Login</button>
  </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
