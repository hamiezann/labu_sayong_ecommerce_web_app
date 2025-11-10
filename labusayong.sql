-- --------------------------------------------------------
-- LABU SAYONG DATABASE STRUCTURE (UPDATED)
-- --------------------------------------------------------

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS labu_sayong_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE labu_sayong_db;

-- --------------------------------------------------------
-- USERS TABLE
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `FullName` VARCHAR(255) NOT NULL,
  `Email` VARCHAR(200) NOT NULL UNIQUE,
  `address` VARCHAR(100) DEFAULT NULL,
  `phone` INT DEFAULT NULL ,
  `Password` TEXT NOT NULL,
  `Role` ENUM('admin','staff','customer') NOT NULL DEFAULT 'customer',
  `Image` TEXT,
  `CreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- PRODUCTS TABLE
-- --------------------------------------------------------
CREATE TABLE `products` (
  `product_id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `price` DECIMAL(10,2) NOT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `image` TEXT,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- ORDERS TABLE
-- --------------------------------------------------------
CREATE TABLE `orders` (
  `order_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `staff_id` INT DEFAULT NULL,
  `order_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('Pending','Processing','Shipped','Completed','Cancelled') DEFAULT 'Pending',
  `payment_method` VARCHAR(50) DEFAULT NULL,
  `shipping_address` TEXT DEFAULT NULL,
  `subtotal` DECIMAL(10,2) DEFAULT 0,
  `shipping_fee` DECIMAL(10,2) DEFAULT 0,
  `total_price` DECIMAL(10,2) DEFAULT 0,
  `notes` TEXT DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
  FOREIGN KEY (`staff_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- --------------------------------------------------------
-- ORDER ITEMS TABLE
-- --------------------------------------------------------
CREATE TABLE `order_items` (
  `order_item_id` INT NOT NULL AUTO_INCREMENT,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `variant_id` INT DEFAULT NULL,
  `quantity` INT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `color` VARCHAR(50) DEFAULT NULL,
  `size` VARCHAR(50) DEFAULT NULL,
  `pattern` VARCHAR(50) DEFAULT NULL,
  `total` DECIMAL(10,2) GENERATED ALWAYS AS (`price` * `quantity`) STORED,
  PRIMARY KEY (`order_item_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`order_id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- RECOMMENDATIONS TABLE
-- --------------------------------------------------------
CREATE TABLE `recommendations` (
  `recommendation_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `budget_min` DECIMAL(10,2),
  `budget_max` DECIMAL(10,2),
  `recommended_products` TEXT,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`recommendation_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- WISHLIST TABLE
-- --------------------------------------------------------
CREATE TABLE `wishlist` (
  `wishlist_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`wishlist_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- LOGS TABLE
-- --------------------------------------------------------
CREATE TABLE `logs` (
  `log_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT,
  `action` VARCHAR(255),
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- CHAT TABLE (User Inquiries)
-- --------------------------------------------------------
-- CREATE TABLE `chats` (
--   `chat_id` INT NOT NULL AUTO_INCREMENT,
--   `sender_id` INT NOT NULL,
--   `receiver_id` INT NOT NULL,
--   `message` TEXT NOT NULL,
--   `is_read` BOOLEAN DEFAULT FALSE,
--   `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
--   PRIMARY KEY (`chat_id`),
--   FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
--   FOREIGN KEY (`receiver_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `chats` (
  `chat_id` INT NOT NULL AUTO_INCREMENT,
  `session_id` INT NOT NULL,
  `sender_id` INT NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` BOOLEAN DEFAULT FALSE,
  `is_read_by_staff` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`chat_id`),
  FOREIGN KEY (`session_id`) REFERENCES `chat_sessions`(`session_id`) ON DELETE CASCADE,
  FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `product_variants` (
  `variant_id` INT NOT NULL AUTO_INCREMENT,
  `product_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`variant_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `variant_options` (
  `option_id` INT NOT NULL AUTO_INCREMENT,
  `variant_id` INT NOT NULL,
  `option_name` VARCHAR(100) NOT NULL,
  `option_value` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`option_id`),
  FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`variant_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `chat_sessions` (
  `session_id` INT NOT NULL AUTO_INCREMENT,
  `product_id` INT NOT NULL,
  `customer_id` INT NOT NULL,
  `assigned_staff_id` INT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`session_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`product_id`) ON DELETE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`assigned_staff_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


COMMIT;

-- --------------------------------------------------------
-- SAMPLE DUMMY USERS
-- --------------------------------------------------------
-- INSERT INTO `users` (`FullName`, `Email`, `Password`, `Role`, `Image`) VALUES
-- ('Admin Labu', 'admin@labusayong.com', MD5('admin123'), 'admin', 'uploads/admin.jpg'),
-- ('Ahmad Zaki', 'staff1@labusayong.com', MD5('zaki123'), 'staff', 'uploads/staff1.jpg'),
-- ('Nur Aina', 'staff2@labusayong.com', MD5('aina123'), 'staff', 'uploads/staff2.jpg'),
-- ('Muhammad Iqmal', 'iqmal@customer.com', MD5('cust123'), 'customer', 'uploads/user1.jpg');
