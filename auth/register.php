<?php
session_start();
require __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $confirm = $_POST['confirm'];

  if ($password !== $confirm) {
    $error = "Passwords do not match.";
  } else {
    // Check unique email
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
      $error = "Email already registered.";
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
      $stmt->execute([$name, $email, $hash, 'client']);
      header('Location: login.php');
      exit;
    }
  }
}

include __DIR__ . '/../includes/header.php';
?>

<main>
  <div class="container mt-5" style="max-width:400px;">
    <h2>Register</h2>
    <?php if (isset($error)): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <form method="post">
      <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="confirm" class="form-control" required>
      </div>
      <button class="btn btn-success">Register</button>
    </form>
  </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>