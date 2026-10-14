-- ==========================================================
-- Database: `smart_store_credit_db`
-- Project: Smart Store Credit and Customer Dues Management System
-- ==========================================================

CREATE DATABASE IF NOT EXISTS `smart_store_credit_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `smart_store_credit_db`;

-- 1. Store Administrator Profile & Security
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `store_name` VARCHAR(150) NOT NULL DEFAULT 'Al-Noor General Store',
  `full_name` VARCHAR(120) NOT NULL DEFAULT 'Store Admin',
  `email` VARCHAR(120) NOT NULL DEFAULT 'admin@smartstorecredit.com',
  `phone` VARCHAR(25) NOT NULL DEFAULT '03001234567',
  `username` VARCHAR(80) NOT NULL UNIQUE DEFAULT 'admin',
  `password` VARCHAR(255) NOT NULL,
  `profile_image` VARCHAR(255) DEFAULT '',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Customer Ledger Directory
CREATE TABLE IF NOT EXISTS `customers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(120) NOT NULL,
  `phone` VARCHAR(25) NOT NULL,
  `address` VARCHAR(255) DEFAULT '',
  `balance` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('Settled', 'Due', 'Partial') DEFAULT 'Settled',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Transactions & Ledger Audit Trail
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT NOT NULL,
  `type` ENUM('credit_given', 'payment_received') NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `previous_balance` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `current_balance` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `note` VARCHAR(255) DEFAULT '',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default Administrator Account (Default password: 123admin)
INSERT INTO `admins` (`id`, `store_name`, `full_name`, `email`, `phone`, `username`, `password`, `profile_image`)
VALUES (1, 'Al-Noor General Store', 'Areeba (Store Administrator)', 'admin@smartstorecredit.com', '03001234567', 'admin', '$2y$10$vI8aWBnW3fID.ZQ4/zo1G.q1l5Qk4Y1jM/b5r8sZ9K4Qz5B3mE4z2', '')
ON DUPLICATE KEY UPDATE `store_name`=VALUES(`store_name`);

-- Demonstration Customer Records
INSERT INTO `customers` (`id`, `name`, `phone`, `address`, `balance`, `status`) VALUES
(1, 'Ali Raza', '03123445678', 'Main Market, Clock Tower', 0.00, 'Settled'),
(2, 'Ahmad Bilal', '03245495550', 'Civil Lines, Faisalabad', 2450.00, 'Due'),
(3, 'Usman Tariq', '03009876543', 'Samanabad Main Bazar', 500.00, 'Partial'),
(4, 'Bilal Khan', '03331122334', 'D-Ground Commercial Area', 1000.00, 'Due')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- Demonstration Ledger Entries
INSERT INTO `transactions` (`customer_id`, `type`, `amount`, `previous_balance`, `current_balance`, `note`, `created_at`) VALUES
(4, 'credit_given', 1000.00, 0.00, 1000.00, 'Grocery items taken on store credit', NOW() - INTERVAL 3 DAY),
(2, 'credit_given', 3000.00, 0.00, 3000.00, 'Monthly household ration on credit', NOW() - INTERVAL 4 DAY),
(2, 'payment_received', 550.00, 3000.00, 2450.00, 'Partial cash repayment received', NOW() - INTERVAL 1 DAY),
(3, 'credit_given', 1500.00, 0.00, 1500.00, 'Store retail supplies on credit', NOW() - INTERVAL 6 DAY),
(3, 'payment_received', 1000.00, 1500.00, 500.00, 'Half installment repayment received', NOW() - INTERVAL 2 DAY);
