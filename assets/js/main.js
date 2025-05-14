// File: /maison_cravate/assets/js/main.js
// ----------------------------------------
// Placeholder for any custom JS you need.
// For example: dynamic quantity limits,
// confirmation dialogs, AJAX cart updates, etc.

document.addEventListener('DOMContentLoaded', function() {
    // Example: confirm before removing item
    const removeButtons = document.querySelectorAll('.btn-danger[href*="remove"]');
    removeButtons.forEach(btn => {
      btn.addEventListener('click', function(e) {
        if (!confirm('Are you sure you want to remove this item from your cart?')) {
          e.preventDefault();
        }
      });
    });
  
    // You can add more interactivity here
  });
  