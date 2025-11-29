DROP DATABASE IF EXISTS `stok_app`;
CREATE DATABASE `stok_app` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `stok_app`;

CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `name` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);

CREATE TABLE `categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);

CREATE TABLE `products` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int unsigned DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `sku` varchar(120) DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT 0,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reorder_point` int NOT NULL DEFAULT 5,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_fk` (`category_id`),
  CONSTRAINT `products_category_fk` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
);

CREATE TABLE `stock_entries` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int unsigned NOT NULL,
  `quantity` int NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `user_id` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `entry_product_fk` (`product_id`),
  KEY `entry_user_fk` (`user_id`),
  CONSTRAINT `entry_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `entry_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
);

CREATE TABLE `stock_exits` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int unsigned NOT NULL,
  `quantity` int NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `user_id` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `exit_product_fk` (`product_id`),
  KEY `exit_user_fk` (`user_id`),
  CONSTRAINT `exit_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exit_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
);

INSERT INTO `users` (`username`, `password`, `name`) VALUES
('admin', '$2y$10$olJufNKwFdY6al/5StRXEeU60igi7A0XKKixG.vjOKkFNMfDZ9ehO', 'Sistem Yöneticisi');

INSERT INTO `categories` (`name`, `description`) VALUES
('Elektronik', 'Tarayıcı, sensör ve otomasyon ekipmanları'),
('Ofis Sarf', 'Günlük operasyon sarf malzemeleri'),
('Paketleme', 'Koli, poşet ve paketleme ekipmanları');

INSERT INTO `products` (`category_id`, `name`, `sku`, `quantity`, `unit_price`, `reorder_point`) VALUES
(1, 'Barkod Okuyucu', 'ELEC-BC-001', 35, 89.99, 10),
(1, 'Endüstriyel Sensör', 'ELEC-SN-020', 18, 129.50, 5),
(2, 'Termal Rulo Kağıt', 'OFF-TP-100', 120, 2.60, 30),
(2, 'Kargo Etiketi', 'OFF-SL-055', 80, 5.20, 25),
(3, 'Orta Boy Koli', 'PKG-MD-210', 45, 1.15, 20);

INSERT INTO `stock_entries` (`product_id`, `quantity`, `note`, `user_id`) VALUES
(1, 20, 'İlk stok yüklemesi', 1),
(3, 100, 'Toplu sipariş alındı', 1),
(5, 50, 'Ambalaj stoğu yenileme', 1);

INSERT INTO `stock_exits` (`product_id`, `quantity`, `note`, `user_id`) VALUES
(3, 30, 'Depo A sevkiyatı', 1),
(4, 15, 'Hafta 32 sevkiyat kullanımı', 1);

