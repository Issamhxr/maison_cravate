<?php
session_start();
require __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);
  $pass = $_POST['password'];

  $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND role = ?');
  $stmt->execute([$email, 'client']);
  $user = $stmt->fetch();

  if ($user && password_verify($pass, $user['password'])) {
    $_SESSION['user'] = $user;
    header('Location: /maison_cravate/index.php');
    exit;
  } else {
    $error = 'Invalid credentials';
  }
}

include __DIR__ . '/../includes/header.php';
?>

<main>
  <div class="container mt-5" style="max-width:400px;">
    <h2>Client Login</h2>
    <?php if (isset($error)): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <form method="post">
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" id="email" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" name="password" id="password" class="form-control" required>
      </div>
      <button class="btn btn-primary">Login</button>
      <a href="/maison_cravate/auth/register.php" class="btn btn-link">Register</a>
    </form>
  </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>