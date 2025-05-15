<?php
session_start();
require __DIR__ . '/../config/db.php';

// No login required for checkout

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
if (empty($cart)) {
  header('Location: /maison_cravate/products/list.php');
  exit;
}

// If user is logged in, use their ID; otherwise, set to null for guest
$userId = isset($_SESSION['user']) ? $_SESSION['user']['id'] : null;

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $phone = trim($_POST['phone']);
  $address = trim($_POST['address']);
  $wilaya = trim($_POST['wilaya']);

  // Validate inputs (add more validation as needed)
  if (empty($name) || empty($phone) || empty($address) || empty($wilaya)) {
    $error = "Please fill in all required fields.";
  } else {
    try {
      // Calculate total
      $total = 0;
      foreach ($cart as $productId => $quantity) {
        $stmt = $pdo->prepare('SELECT price FROM products WHERE id = ?');
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        $total += $product['price'] * $quantity;
      }

      // Insert order into database
      // If $userId is null, use 0 instead
      $userIdForOrder = ($userId !== null) ? $userId : null;

      // Debugging: Output the value of $userIdForOrder
      echo "User ID for Order: " . $userIdForOrder . "<br>";

      $stmt = $pdo->prepare('INSERT INTO orders (user_id, name, phone, address, wilaya, total, created_at, status) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)');
      $stmt->execute([$userIdForOrder, $name, $phone, $address, $wilaya, $total, 'pending']);
      $orderId = $pdo->lastInsertId();

      // Insert order items
      foreach ($cart as $productId => $quantity) {
        $stmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)');
        $stmt->execute([$orderId, $productId, $quantity]);
      }

      // Clear session cart
      unset($_SESSION['cart']);

      // Redirect to success page
      header('Location: /maison_cravate/orders/success.php');
      exit;

    } catch (PDOException $e) {
      $error = "An error occurred: " . $e->getMessage();
      echo "Error: " . $error . "<br>"; // Output the error message
    }
  }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="checkout-container">
  <div class="checkout-card">
    <h2 class="checkout-title">Guest Checkout</h2>
    <p class="checkout-desc">Please fill in your details to complete your order. No account required.</p>
    <?php if (isset($error)): ?>
      <div class="alert alert-danger"
        style="margin-bottom: 15px; padding: 10px; border-radius: 5px; background-color: #f8d7da; color: #721c24;">
        <?= $error ?>
      </div>
    <?php endif; ?>
    <form class="checkout-form" method="post" style="display: flex; flex-direction: column; gap: 15px;">
      <div class="form-group" style="display: flex; flex-direction: column;">
        <label for="name" style="margin-bottom: 5px; font-weight: bold;">Full Name</label>
        <input type="text" id="name" name="name" required
          style="padding: 10px; border: 1px solid #ccc; border-radius: 5px;" />
      </div>
      <div class="form-group" style="display: flex; flex-direction: column;">
        <label for="phone" style="margin-bottom: 5px; font-weight: bold;">Phone Number</label>
        <input type="tel" id="phone" name="phone" pattern="[0-9]{10}" required placeholder="Enter 10-digit phone number"
          style="padding: 10px; border: 1px solid #ccc; border-radius: 5px;" />
      </div>
      <div class="form-group" style="display: flex; flex-direction: column;">
        <label for="address" style="margin-bottom: 5px; font-weight: bold;">Address</label>
        <input type="text" id="address" name="address" required
          style="padding: 10px; border: 1px solid #ccc; border-radius: 5px;" />
      </div>
      <div class="form-group" style="display: flex; flex-direction: column;">
        <label for="wilaya" style="margin-bottom: 5px; font-weight: bold;">Wilaya</label>
        <select id="wilaya" name="wilaya" required style="padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
          <option value="">Choose your Wilaya</option>
          <option value="Adrar">Adrar</option>
          <option value="Chlef">Chlef</option>
          <option value="Laghouat">Laghouat</option>
          <option value="Oum El Bouaghi">Oum El Bouaghi</option>
          <option value="Batna">Batna</option>
          <option value="Bejaia">Bejaia</option>
          <option value="Biskra">Biskra</option>
          <option value="Bechar">Bechar</option>
          <option value="Blida">Blida</option>
          <option value="Bouira">Bouira</option>
          <option value="Tamanrasset">Tamanrasset</option>
          <option value="Tebessa">Tebessa</option>
          <option value="Tlemcen">Tlemcen</option>
          <option value="Tiaret">Tiaret</option>
          <option value="Tizi Ouzou">Tizi Ouzou</option>
          <option value="Algiers">Algiers</option>
          <option value="Djelfa">Djelfa</option>
          <option value="Jijel">Jijel</option>
          <option value="Setif">Setif</option>
          <option value="Saida">Saida</option>
          <option value="Skikda">Skikda</option>
          <option value="Sidi Bel Abbes">Sidi Bel Abbes</option>
          <option value="Annaba">Annaba</option>
          <option value="Guelma">Guelma</option>
          <option value="Constantine">Constantine</option>
          <option value="Medea">Medea</option>
          <option value="Mostaganem">Mostaganem</option>
          <option value="MSila">MSila</option>
          <option value="Mascara">Mascara</option>
          <option value="Ouargla">Ouargla</option>
          <option value="Oran">Oran</option>
          <option value="El Bayadh">El Bayadh</option>
          <option value="Illizi">Illizi</option>
          <option value="Bordj Bou Arreridj">Bordj Bou Arreridj</option>
          <option value="Boumerdes">Boumerdes</option>
          <option value="El Tarf">El Tarf</option>
          <option value="Tindouf">Tindouf</option>
          <option value="Tissemsilt">Tissemsilt</option>
          <option value="El Oued">El Oued</option>
          <option value="Khenchela">Khenchela</option>
          <option value="Souk Ahras">Souk Ahras</option>
          <option value="Tipaza">Tipaza</option>
          <option value="Mila">Mila</option>
          <option value="Ain Defla">Ain Defla</option>
          <option value="Naama">Naama</option>
          <option value="Ain Temouchent">Ain Temouchent</option>
          <option value="Ghardaia">Ghardaia</option>
          <option value="Relizane">Relizane</option>
        </select>
      </div>
      <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ccc;" />
      <button type="submit" class="checkout-btn"
        style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">Place
        Order as Guest</button>
    </form>
  </div>
</div>

<?php
include __DIR__ . '/../includes/footer.php';

if ($userId === null) {
  // The order is placed as a guest
} else {
  // Call stored procedure (must exist in your DB)
  $stmt = $pdo->prepare('CALL FinalizeOrder(?)');
  $stmt->execute([$userId]);
  // Clear session cart
  unset($_SESSION['cart']);
  // Redirect to success page
  header('Location: /maison_cravate/orders/success.php');
  exit;
}
?>