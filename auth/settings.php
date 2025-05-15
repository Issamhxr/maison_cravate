<?php
session_start();
require __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['new_email']) || isset($_POST['new_password']))) {
    $newEmail = isset($_POST['new_email']) ? trim($_POST['new_email']) : null;
    $oldPassword = isset($_POST['old_password']) ? trim($_POST['old_password']) : null;
    $newPassword = isset($_POST['new_password']) ? trim($_POST['new_password']) : null;

    try {
        if ($newPassword) {
            // Verify old password
            $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ? AND role = "client"');
            $stmt->execute([$_SESSION['user']['id']]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($oldPassword, $user['password'])) {
                $_SESSION['error'] = "Old password is incorrect.";
                header('Location: settings.php');
                exit;
            }

            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ? AND role = "client"');
            $stmt->execute([$hashedPassword, $_SESSION['user']['id']]);
            $_SESSION['success'] = "Password updated successfully.";
        }

        if ($newEmail) {
            $stmt = $pdo->prepare('UPDATE users SET email = ? WHERE id = ? AND role = "client"');
            $stmt->execute([$newEmail, $_SESSION['user']['id']]);
            $_SESSION['user']['email'] = $newEmail;
            $_SESSION['success'] = "Email updated successfully.";
        }

        header('Location: settings.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "An error occurred: " . $e->getMessage();
        header('Location: settings.php');
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container mt-5">
    <h2>User Settings</h2>

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

    <form method="post" action="settings.php" class="mt-4">
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

<?php include __DIR__ . '/../includes/footer.php'; ?>