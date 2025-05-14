<?php
// (Simple front-end only; you can hook up mail later)
include __DIR__ . '/includes/header.php';
?>

<div class="container mt-5" style="max-width:600px;">
  <h2>Contact Us</h2>
  <form method="post" action="#">
    <div class="mb-3">
      <label class="form-label">Your Name</label>
      <input type="text" name="name" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Your Email</label>
      <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Message</label>
      <textarea name="message" rows="5" class="form-control" required></textarea>
    </div>
    <button class="btn btn-primary">Send Message</button>
  </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
