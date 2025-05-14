
CREATE DATABASE `maison_cravate_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `maison_cravate_db`;

-- ======================================
-- Tables
-- ======================================

-- Users table
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','client') NOT NULL DEFAULT 'client'
);

-- Products (ties) table
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `material` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `price` DECIMAL(10,2) NOT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `image` VARCHAR(255) DEFAULT NULL
);

-- Cart table (session-less, optional for persistence)
CREATE TABLE `cart` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
);

/*
-- Orders table
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `total` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('pending','validated','cancelled') NOT NULL DEFAULT 'pending',
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

-- Order items detail
CREATE TABLE `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
);
*/

CREATE TABLE `orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) DEFAULT NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `address` VARCHAR(255) NOT NULL,
  `wilaya` VARCHAR(255) NOT NULL,
  `total` DECIMAL(10,2) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `order_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `quantity` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Cancelled orders history
CREATE TABLE `cancelled_orders_history` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `cancellation_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
);

-- ======================================
-- Sample admin user (password = 'admin123')
-- ======================================
INSERT INTO `users` (`username`,`email`,`password`,`role`)
VALUES (
  'admin',
  'admin@maisoncravate.dz',
  '$2b$12$zayo524CouGfYDKL4Fgcy.gpyMaztS7G4z294HrDF4rM7uPi1E5xq',
  'admin'
);

-- ======================================
-- Stored Procedures
-- ======================================
DELIMITER $$

CREATE PROCEDURE `ShowOrderDetails`(IN p_order_id INT)
BEGIN
  -- List each item in the order
  SELECT 
    oi.product_id,
    p.name,
    oi.quantity,
    oi.price,
    (oi.quantity * oi.price) AS line_total
  FROM `order_items` AS oi
  JOIN `products`      AS p  ON oi.product_id = p.id
  WHERE oi.order_id = p_order_id;

  -- Show total amount
  SELECT 
    SUM(quantity * price) AS total_amount
  FROM `order_items`
  WHERE order_id = p_order_id;
END $$

CREATE PROCEDURE `FinalizeOrder`(IN p_user_id INT)
BEGIN
  DECLARE v_order_id INT;
  DECLARE v_total DECIMAL(12,2);

  -- Create new order
  INSERT INTO `orders` (user_id, total, status)
    VALUES (p_user_id, 0, 'validated');
  SET v_order_id = LAST_INSERT_ID();

  -- Move items from cart table into order_items
  INSERT INTO `order_items` (order_id, product_id, quantity, price)
    SELECT v_order_id, c.product_id, c.quantity, p.price
    FROM `cart` AS c
    JOIN `products` AS p ON c.product_id = p.id
    WHERE c.user_id = p_user_id;

  -- Calculate order total
  SELECT SUM(p.price * c.quantity) INTO v_total
    FROM `cart` AS c
    JOIN `products` AS p ON c.product_id = p.id
    WHERE c.user_id = p_user_id;

  -- Update order with correct total
  UPDATE `orders`
    SET total = v_total
    WHERE id = v_order_id;

  -- Clear the cart
  DELETE FROM `cart` WHERE user_id = p_user_id;
END $$

CREATE PROCEDURE `GetUserOrdersHistory`(IN p_user_id INT)
BEGIN
  SELECT * 
    FROM `orders`
   WHERE user_id = p_user_id
   ORDER BY created_at DESC;
END $$

DELIMITER ;

-- ======================================
-- Triggers
-- ======================================
DELIMITER $$

-- After inserting into order_items, decrement stock
CREATE TRIGGER `after_order_items_insert`
AFTER INSERT ON `order_items`
FOR EACH ROW
BEGIN
  UPDATE `products`
    SET stock = stock - NEW.quantity
  WHERE id = NEW.product_id;
END $$

-- Prevent adding to cart more than available stock
CREATE TRIGGER `prevent_over_order`
BEFORE INSERT ON `cart`
FOR EACH ROW
BEGIN
  DECLARE v_stock INT;
  SELECT stock INTO v_stock FROM `products` WHERE id = NEW.product_id;
  IF NEW.quantity > v_stock THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Insufficient stock for this product.';
  END IF;
END $$

-- If an order is cancelled, restore stock and log history
CREATE TRIGGER `after_orders_update`
AFTER UPDATE ON `orders`
FOR EACH ROW
BEGIN
  IF OLD.status <> 'cancelled' AND NEW.status = 'cancelled' THEN
    -- Restore stock
    UPDATE `products` p
      JOIN `order_items` oi ON p.id = oi.product_id
     SET p.stock = p.stock + oi.quantity
     WHERE oi.order_id = NEW.id;

    -- Archive cancellation
    INSERT INTO `cancelled_orders_history` (order_id, user_id)
      VALUES (NEW.id, NEW.user_id);
  END IF;
END $$

DELIMITER ;



ALTER TABLE orders
MODIFY COLUMN user_id INT(11) NULL;