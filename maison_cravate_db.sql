
-- Tables
-- ======================================

-- Users table
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'client') NOT NULL DEFAULT 'client'
);

-- Products (ties) table
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `material` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `price` DECIMAL(10, 2) NOT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `image` VARCHAR(255) DEFAULT NULL
);

-- Cart table 
CREATE TABLE `cart` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
);



CREATE TABLE `orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) DEFAULT NULL,
  `name` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(15) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `address` VARCHAR(255) NOT NULL,
  `wilaya` VARCHAR(255) NOT NULL,
  `total` DECIMAL(10, 2) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=INNODB DEFAULT CHARSET=UTF8MB4;

CREATE TABLE `order_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `quantity` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=INNODB DEFAULT CHARSET=UTF8MB4;


-- Cancelled orders history
CREATE TABLE `cancelled_orders_history` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `cancellation_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
);


INSERT INTO `users` (
  `username`,
  `email`,
  `password`,
  `role`
) VALUES (
  'admin',
  'admin@maisoncravate.dz',
  '$2b$12$zayo524CouGfYDKL4Fgcy.gpyMaztS7G4z294HrDF4rM7uPi1E5xq', -- Sample admin user (password = 'admin123')
  'admin'
);


-- Stored Procedures
-- ======================================
DELIMITER $$

CREATE PROCEDURE `SHOWORDERDETAILS`(
  IN P_ORDER_ID INT
)
BEGIN
 
  -- List each item in the order
  SELECT
    OI.PRODUCT_ID,
    P.NAME,
    OI.QUANTITY,
    OI.PRICE,
    (OI.QUANTITY * OI.PRICE) AS LINE_TOTAL
  FROM
    `order_items` AS OI
    JOIN `products` AS P
    ON OI.PRODUCT_ID = P.ID
  WHERE
    OI.ORDER_ID = P_ORDER_ID;
 
  -- Show total amount
  SELECT
    SUM(QUANTITY * PRICE) AS TOTAL_AMOUNT
  FROM
    `order_items`
  WHERE
    ORDER_ID = P_ORDER_ID;
END $$

CREATE PROCEDURE `FINALIZEORDER`(IN P_USER_ID INT)
BEGIN
  DECLARE V_ORDER_ID INT;
  DECLARE V_TOTAL DECIMAL(12, 2);
 
  -- Create new order
  INSERT INTO `orders` (USER_ID, TOTAL, STATUS) VALUES (P_USER_ID, 0, 'validated');
  SET V_ORDER_ID = LAST_INSERT_ID();
 
  -- Move items from cart table into order_items
  INSERT INTO `order_items` (ORDER_ID, PRODUCT_ID, QUANTITY, PRICE)
  SELECT
    V_ORDER_ID,
    C.PRODUCT_ID,
    C.QUANTITY,
    P.PRICE
  FROM
    `cart` AS C
    JOIN `products` AS P
    ON C.PRODUCT_ID = P.ID
  WHERE
    C.USER_ID = P_USER_ID;
 
  -- Calculate order total
  SELECT SUM(P.PRICE * C.QUANTITY) INTO V_TOTAL
  FROM `cart` AS C
  JOIN `products` AS P
  ON C.PRODUCT_ID = P.ID
  WHERE C.USER_ID = P_USER_ID;
 
  -- Update order with correct total
  UPDATE `orders` SET TOTAL = V_TOTAL WHERE ID = V_ORDER_ID;
 
  -- Clear the cart
  DELETE FROM `cart` WHERE USER_ID = P_USER_ID;
END $$

CREATE PROCEDURE `GETUSERORDERSHISTORY`(IN P_USER_ID INT)
BEGIN
  SELECT * FROM `orders` WHERE USER_ID = P_USER_ID ORDER BY CREATED_AT DESC;
END $$

DELIMITER ;

-- ======================================
-- Triggers
-- ======================================
DELIMITER $$
 -- After inserting into order_items, decrement stock
CREATE TRIGGER `AFTER_ORDER_ITEMS_INSERT` AFTER INSERT ON `order_items` FOR EACH ROW
BEGIN
  UPDATE `products` SET STOCK = STOCK - NEW.QUANTITY WHERE ID = NEW.PRODUCT_ID;
END $$
 -- Prevent adding to cart more than available stock
CREATE TRIGGER `PREVENT_OVER_ORDER` BEFORE INSERT ON `cart` FOR EACH ROW
BEGIN
  DECLARE V_STOCK INT;
  SELECT STOCK INTO V_STOCK FROM `products` WHERE ID = NEW.PRODUCT_ID;
  IF NEW.QUANTITY > V_STOCK THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Insufficient stock for this product.';
  END IF;
END $$
 -- If an order is cancelled, restore stock and log history
CREATE TRIGGER `AFTER_ORDERS_UPDATE` AFTER UPDATE ON `orders` FOR EACH ROW
BEGIN
  IF OLD.STATUS <> 'cancelled' AND NEW.STATUS = 'cancelled' THEN
    -- Restore stock
    UPDATE `products` P
    JOIN `order_items` OI ON P.ID = OI.PRODUCT_ID
    SET P.STOCK = P.STOCK + OI.QUANTITY
    WHERE OI.ORDER_ID = NEW.ID;
 
    -- Archive cancellation
    INSERT INTO `cancelled_orders_history` (ORDER_ID, USER_ID) VALUES (NEW.ID, NEW.USER_ID);
  END IF;
END $$

DELIMITER ;
ALTER TABLE orders MODIFY COLUMN USER_ID INT(11) NULL;