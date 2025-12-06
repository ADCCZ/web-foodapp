-- ========================================
-- Food App - Instalacni SQL skript
-- ========================================
-- Tento soubor vytvori databazi a naplni ji testovacimi daty
--
-- Pouziti:
-- 1. Vytvorte databazi 'foodapp' v phpMyAdmin (nebo: CREATE DATABASE foodapp;)
-- 2. Importujte tento soubor do databaze
-- ========================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- ========================================
-- Tabulka: users
-- ========================================

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `jmeno` varchar(100) NOT NULL,
  `role` enum('konzument','dodavatel','admin') NOT NULL DEFAULT 'konzument',
  `is_approved` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  KEY `role` (`role`),
  KEY `is_approved` (`is_approved`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- Tabulka: products
-- ========================================

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`product_id`),
  KEY `supplier_id` (`supplier_id`),
  CONSTRAINT `fk_products_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- Tabulka: orders
-- ========================================

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `status` enum('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending',
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  KEY `customer_id` (`customer_id`),
  KEY `status` (`status`),
  CONSTRAINT `fk_orders_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- Tabulka: order_items
-- ========================================

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`order_item_id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- TESTOVACI DATA
-- ========================================

-- ----------------------------------------
-- Uzivatele (heslo pro vsechny: heslo123)
-- ----------------------------------------
-- Bcrypt hash pro "heslo123": $2y$10$7QaWf3USCidc2qcsUx5F4e/2GIXmUA1f9k.Eg8U7OwPW8.nvVjDOi

INSERT INTO `users` (`user_id`, `email`, `password`, `jmeno`, `role`, `is_approved`, `created_at`) VALUES
(1, 'admin@test.cz', '$2y$10$7QaWf3USCidc2qcsUx5F4e/2GIXmUA1f9k.Eg8U7OwPW8.nvVjDOi', 'Administrátor Systému', 'admin', 1, NOW()),
(2, 'dodavatel@test.cz', '$2y$10$7QaWf3USCidc2qcsUx5F4e/2GIXmUA1f9k.Eg8U7OwPW8.nvVjDOi', 'Pizza House', 'dodavatel', 1, NOW()),
(3, 'dodavatel2@test.cz', '$2y$10$7QaWf3USCidc2qcsUx5F4e/2GIXmUA1f9k.Eg8U7OwPW8.nvVjDOi', 'Burger King', 'dodavatel', 1, NOW()),
(4, 'dodavatel3@test.cz', '$2y$10$7QaWf3USCidc2qcsUx5F4e/2GIXmUA1f9k.Eg8U7OwPW8.nvVjDOi', 'Sushi Bar', 'dodavatel', 0, NOW()),
(5, 'zakaznik@test.cz', '$2y$10$7QaWf3USCidc2qcsUx5F4e/2GIXmUA1f9k.Eg8U7OwPW8.nvVjDOi', 'Jan Novák', 'konzument', 1, NOW()),
(6, 'zakaznik2@test.cz', '$2y$10$7QaWf3USCidc2qcsUx5F4e/2GIXmUA1f9k.Eg8U7OwPW8.nvVjDOi', 'Marie Svobodová', 'konzument', 1, NOW());

-- ----------------------------------------
-- Produkty
-- ----------------------------------------

INSERT INTO `products` (`product_id`, `supplier_id`, `name`, `description`, `price`, `image`, `created_at`) VALUES
-- Produkty od Pizza House (user_id=2)
(1, 2, 'Pizza Margherita', 'Klasická pizza s rajčatovou omáčkou, mozzarellou a čerstvou bazalkou', 159.00, 'pizza_margherita.jpg', NOW()),
(2, 2, 'Pizza Salámová', 'Pizza s rajčatovou omáčkou, mozzarellou a pikantním salámem', 189.00, 'pizza_salamova.jpg', NOW()),
(3, 2, 'Pizza Hawai', 'Pizza s rajčatovou omáčkou, mozzarellou, šunkou a ananasem', 179.00, 'pizza_hawai.jpg', NOW()),
(4, 2, 'Coca-Cola 0.5l', 'Osvěžující nápoj', 39.00, 'coca_cola.jpg', NOW()),

-- Produkty od Burger King (user_id=3)
(5, 3, 'Cheeseburger Classic', 'Hovězí burger s čedarem, kyselou okurkou a omáčkou', 99.00, 'cheeseburger.jpg', NOW()),
(6, 3, 'Double Bacon Burger', 'Dvojitý burger s beconem a čedarovou omáčkou', 149.00, 'double_bacon.jpg', NOW()),
(7, 3, 'Crispy Chicken Burger', 'Křupavý kuřecí burger s coleslaw a majonézou', 119.00, 'chicken_burger.jpg', NOW()),
(8, 3, 'Hranolky velké', 'Zlatavé hranolky', 59.00, 'hranolky.jpg', NOW()),

-- Produkty od Sushi Bar (user_id=4, NESCHVALENY dodavatel)
(9, 4, 'Sushi set 24 ks', 'Mix nigiri a maki rolek', 399.00, 'sushi_set.jpg', NOW()),
(10, 4, 'California roll', '8 ks California roll s lososem a avokádem', 159.00, 'california_roll.jpg', NOW());

-- ----------------------------------------
-- Objednavky
-- ----------------------------------------

INSERT INTO `orders` (`order_id`, `customer_id`, `status`, `total_price`, `created_at`) VALUES
-- Objednavky Jana Novaka (user_id=5)
(1, 5, 'completed', 387.00, '2024-11-15 12:30:00'),
(2, 5, 'processing', 149.00, '2024-11-18 18:45:00'),

-- Objednavky Marie Svobodove (user_id=6)
(3, 6, 'completed', 337.00, '2024-11-16 14:20:00'),
(4, 6, 'pending', 218.00, '2024-11-19 10:15:00');

-- ----------------------------------------
-- Polozky objednavek
-- ----------------------------------------

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
-- Objednavka #1 (Jan Novak, completed)
(1, 1, 1, 2, 159.00),  -- 2x Pizza Margherita
(2, 1, 4, 1, 39.00),   -- 1x Coca-Cola
(3, 1, 8, 1, 59.00),   -- 1x Hranolky

-- Objednavka #2 (Jan Novak, processing)
(4, 2, 6, 1, 149.00),  -- 1x Double Bacon Burger

-- Objednavka #3 (Marie Svobodova, completed)
(5, 3, 2, 1, 189.00),  -- 1x Pizza Salamova
(6, 3, 7, 1, 119.00),  -- 1x Crispy Chicken Burger
(7, 3, 4, 1, 39.00),   -- 1x Coca-Cola

-- Objednavka #4 (Marie Svobodova, pending)
(8, 4, 3, 1, 179.00),  -- 1x Pizza Hawai
(9, 4, 4, 1, 39.00);   -- 1x Coca-Cola

COMMIT;

-- ========================================
-- KONEC INSTALACNIHO SKRIPTU
-- ========================================
--
-- Vychozi prihlasovaci udaje:
--
-- Admin:      admin@test.cz / heslo123
-- Dodavatel:  dodavatel@test.cz / heslo123
-- Dodavatel:  dodavatel2@test.cz / heslo123
-- Zakaznik:   zakaznik@test.cz / heslo123
-- Zakaznik:   zakaznik2@test.cz / heslo123
--
-- ========================================
