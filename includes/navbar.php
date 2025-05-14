<?php
// File: /maison_cravate/includes/navbar.php
// Responsive Bootstrap navbar with dynamic login/cart links and improved styling
?>
<nav class="navbar navbar-expand-md navbar-light bg-light shadow-sm">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="/maison_cravate/index.php">
   <img src="/maison_cravate/assets/img/tiehouse_logo_fullwidth.png" alt="TieHouse" height="50"  style="object-fit: contain;">


    
    </a>
    <button 
      class="navbar-toggler" 
      type="button" 
      data-bs-toggle="collapse" 
      data-bs-target="#navbarNav" 
      aria-controls="navbarNav" 
      aria-expanded="false" 
      aria-label="Toggle navigation"
    >
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto mb-2 mb-md-0 gap-1">
        <li class="nav-item">
          <a class="nav-link" href="/maison_cravate/index.php">
            <i class="fas fa-home"></i> Home
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/maison_cravate/products/list.php">
            <i class="fas fa-user-tie"></i> Ties
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/maison_cravate/cart/view.php">
            <i class="fas fa-shopping-cart"></i> Cart
            <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
              <span class="badge bg-secondary ms-1"><?= count($_SESSION['cart']) ?></span>
            <?php endif; ?>
          </a>
        </li>
        <?php if (isset($_SESSION['user'])): ?>
          <li class="nav-item">
            <a class="nav-link" href="/maison_cravate/auth/logout.php">
              <i class="fas fa-sign-out-alt"></i> Logout
            </a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="/maison_cravate/auth/login.php">
              <i class="fas fa-sign-in-alt"></i> Login
            </a>
          </li>
        <?php endif; ?>
        <?php if (isset($_SESSION['admin'])): ?>
          <li class="nav-item">
            <a class="nav-link" href="/maison_cravate/admin/dashboard.php">
              <i class="fas fa-user-shield"></i> Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/maison_cravate/admin/logout.php">
              <i class="fas fa-sign-out-alt"></i> Admin Logout
            </a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="/maison_cravate/admin/login.php">
              <i class="fas fa-user-lock"></i> Admin Login
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<style>
  @media (max-width: 767.98px) {
    .navbar-nav .nav-link {
      padding-top: 0.75rem;
      padding-bottom: 0.75rem;
      font-size: 1.1rem;
    }
    .navbar-brand span {
      font-size: 1.1rem;
    }
  }
  .navbar-nav .nav-link i {
    margin-right: 4px;
  }
</style>