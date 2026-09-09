-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 09, 2026 at 07:25 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lx_accounting`
--

-- --------------------------------------------------------

--
-- Table structure for table `acc_bank_accounts`
--

CREATE TABLE `acc_bank_accounts` (
  `bank_id` int(10) UNSIGNED NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `ifsc_code` varchar(25) NOT NULL,
  `account_type` enum('CURRENT','SAVINGS','OVERDRAFT','CASH') DEFAULT 'CURRENT',
  `current_balance` decimal(14,2) DEFAULT 0.00,
  `status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `acc_bank_accounts`
--

INSERT INTO `acc_bank_accounts` (`bank_id`, `account_name`, `bank_name`, `account_number`, `ifsc_code`, `account_type`, `current_balance`, `status`) VALUES
(1, 'LX INDIA', 'IDFC BANK', '14445600001', 'IDFC001400', 'CURRENT', 150000.00, 1),
(2, 'Primary operations', 'HDFC BANK', '14445600002', 'IDFC001401', 'CURRENT', 50000.00, 0),
(3, 'CASH', 'CASH', '455456', '56456', 'CASH', 0.00, 1),
(4, 'RBL LEVELX', 'RBL', '452136588', 'SBIN542684', 'CURRENT', 5000.00, 1),
(5, 'fedaral bank ', 'zzzzz', 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz', 'sssssssssssssssssssssssss', 'SAVINGS', 999999999999.99, 0),
(6, 'zyah bank ', 'hdfc bank ', '777777777777777777', '44444444444', 'SAVINGS', 1000.00, 0),
(7, 'IndusInd Bank', 'IndusInd Bank', '502011111111111111', 'IND458964', 'CURRENT', 56223.00, 1),
(8, 'Al Rajhi Bank', 'Al Rajhi Bank', '1444560000255656', 'IDFC0014015', 'SAVINGS', 1000.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `acc_categories`
--

CREATE TABLE `acc_categories` (
  `category_id` int(10) UNSIGNED NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `acc_categories`
--

INSERT INTO `acc_categories` (`category_id`, `category_name`, `description`, `status`, `created_at`) VALUES
(1, 'Electronics', 'Electronic devices and gadgets', 1, '2026-08-30 08:36:07'),
(2, 'Apparel & Clothing', 'Fashion, clothing, and garments', 1, '2026-08-30 08:36:07'),
(3, 'Food & Beverages', 'Grocery and packaged food items', 1, '2026-08-30 08:36:07'),
(4, 'Platform Services', 'Commission and platform service fees', 1, '2026-08-30 08:36:07'),
(5, 'Other Services', 'Cleaning, Electrical, plumbing', 1, '2026-08-31 00:13:43'),
(6, 'Home Decor', 'DECOR ITEMS', 1, '2026-09-04 12:31:42'),
(7, 'website building', 'website building xxxxxxxxxxxxxx', 1, '2026-09-05 11:46:52'),
(8, 'website building', 'website building', 1, '2026-09-05 11:48:22'),
(9, 'Home Essentials', 'Home Essentials', 1, '2026-09-08 09:57:29'),
(10, 'Bags', 'Bags', 1, '2026-09-08 10:53:48');

-- --------------------------------------------------------

--
-- Table structure for table `acc_credit_notes`
--

CREATE TABLE `acc_credit_notes` (
  `credit_note_id` int(10) UNSIGNED NOT NULL,
  `credit_note_number` varchar(50) NOT NULL,
  `original_invoice_id` int(10) UNSIGNED DEFAULT NULL,
  `invoice_id` int(10) UNSIGNED DEFAULT NULL,
  `party_id` int(10) UNSIGNED NOT NULL,
  `credit_note_date` date DEFAULT curdate(),
  `place_of_supply` varchar(100) DEFAULT NULL,
  `is_interstate` tinyint(1) DEFAULT 0,
  `taxable_amount` decimal(12,2) DEFAULT 0.00,
  `discount_amount` decimal(12,2) DEFAULT 0.00,
  `credit_date` date NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `tax_amount` decimal(12,2) NOT NULL,
  `cgst_amount` decimal(12,2) DEFAULT 0.00,
  `sgst_amount` decimal(12,2) DEFAULT 0.00,
  `igst_amount` decimal(12,2) DEFAULT 0.00,
  `round_off` decimal(8,2) DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL,
  `adjusted_amount` decimal(12,2) DEFAULT 0.00,
  `status` enum('OPEN','ADJUSTED','PARTIALLY_ADJUSTED') DEFAULT 'OPEN',
  `notes` text DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `acc_credit_notes`
--

INSERT INTO `acc_credit_notes` (`credit_note_id`, `credit_note_number`, `original_invoice_id`, `invoice_id`, `party_id`, `credit_note_date`, `place_of_supply`, `is_interstate`, `taxable_amount`, `discount_amount`, `credit_date`, `subtotal`, `tax_amount`, `cgst_amount`, `sgst_amount`, `igst_amount`, `round_off`, `total_amount`, `adjusted_amount`, `status`, `notes`, `reason`, `created_at`) VALUES
(1, 'LXCR2025-26/2000', 2, NULL, 38, '2026-09-08', 'Kerala', 0, 1500.00, 0.00, '0000-00-00', 0.00, 135.00, 67.50, 67.50, 0.00, 0.00, 1635.00, 1635.00, 'ADJUSTED', '', NULL, '2026-09-08 12:14:04'),
(2, 'LXCR2025-26/2001', 1, NULL, 38, '2026-09-08', 'Kerala', 0, 917.44, 0.00, '0000-00-00', 0.00, 82.57, 41.29, 41.29, 0.00, -0.01, 1000.00, 1000.00, 'ADJUSTED', '', NULL, '2026-09-08 12:17:42'),
(3, 'LXCR2025-26/2002', 3, NULL, 38, '2026-09-08', 'Kerala', 0, 2752.30, 0.00, '0000-00-00', 0.00, 247.71, 123.86, 123.86, 0.00, -0.01, 3000.00, 3000.00, 'ADJUSTED', '', NULL, '2026-09-08 12:26:06'),
(4, 'LXCR2025-26/2003', 5, NULL, 38, '2026-09-08', 'Kerala', 0, 458.72, 0.00, '0000-00-00', 0.00, 41.28, 20.64, 20.64, 0.00, 0.00, 500.00, 200.00, 'PARTIALLY_ADJUSTED', '', NULL, '2026-09-08 12:51:58'),
(5, 'LXCR2025-26/2004', 8, NULL, 38, '2026-09-08', 'Kerala', 0, 847.46, 0.00, '2026-09-08', 847.46, 152.54, 76.27, 76.27, 0.00, 0.00, 1000.00, 500.00, 'PARTIALLY_ADJUSTED', '', NULL, '2026-09-08 14:08:33'),
(6, 'LXCR2025-26/2005', 9, NULL, 38, '2026-09-08', 'Kerala', 0, 4587.15, 0.00, '2026-09-08', 4587.15, 412.84, 206.42, 206.42, 0.00, 0.01, 5000.00, 5000.00, 'ADJUSTED', '', NULL, '2026-09-08 14:29:41'),
(7, 'LXCR2025-26/2006', 10, NULL, 39, '2026-09-08', 'Karnataka', 1, 500.00, 0.00, '2026-09-08', 500.00, 45.00, 0.00, 0.00, 45.00, 0.00, 545.00, 545.00, 'ADJUSTED', '', NULL, '2026-09-08 14:57:38'),
(8, 'LXCR2025-26/2007', 10, NULL, 39, '2026-09-08', 'Karnataka', 1, 500.00, 0.00, '2026-09-08', 500.00, 45.00, 0.00, 0.00, 45.00, 0.00, 545.00, 545.00, 'ADJUSTED', '', NULL, '2026-09-08 14:59:36'),
(9, 'LXCR2025-26/2008', 14, NULL, 38, '2026-09-08', 'Kerala', 0, 1834.86, 0.00, '2026-09-08', 1834.86, 165.14, 82.57, 82.57, 0.00, 0.00, 2000.00, 2000.00, 'ADJUSTED', '', NULL, '2026-09-08 20:31:56');

-- --------------------------------------------------------

--
-- Table structure for table `acc_credit_note_items`
--

CREATE TABLE `acc_credit_note_items` (
  `item_id` int(10) UNSIGNED NOT NULL,
  `credit_note_id` int(10) UNSIGNED NOT NULL,
  `item_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit` varchar(20) DEFAULT 'PCS',
  `unit_price` decimal(12,2) NOT NULL,
  `discount_percent` decimal(5,2) DEFAULT 0.00,
  `discount_amount` decimal(12,2) DEFAULT 0.00,
  `taxable_amount` decimal(12,2) DEFAULT 0.00,
  `tax_rate` decimal(5,2) NOT NULL,
  `tax_amount` decimal(12,2) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `acc_credit_note_items`
--

INSERT INTO `acc_credit_note_items` (`item_id`, `credit_note_id`, `item_name`, `description`, `quantity`, `unit`, `unit_price`, `discount_percent`, `discount_amount`, `taxable_amount`, `tax_rate`, `tax_amount`, `total_amount`) VALUES
(1, 1, 'Keyboard', 'Logitech Keyboard', 3.00, 'PCS', 500.00, 0.00, 0.00, 0.00, 9.00, 135.00, 1635.00),
(2, 2, 'Keyboard', 'Logitech Keyboard', 2.00, 'PCS', 458.72, 0.00, 0.00, 0.00, 9.00, 82.57, 1000.01),
(3, 3, 'Keyboard', 'Logitech Keyboard', 5.00, 'PCS', 550.46, 0.00, 0.00, 0.00, 9.00, 247.71, 3000.01),
(4, 4, 'Keyboard', 'Logitech Keyboard', 1.00, 'PCS', 458.72, 0.00, 0.00, 0.00, 9.00, 41.28, 500.00),
(5, 5, 'Mouse', 'Mouse', 1.00, 'PCS', 847.46, 0.00, 0.00, 0.00, 18.00, 152.54, 1000.00),
(6, 6, 'Keyboard', 'Logitech Keyboard', 5.00, 'PCS', 917.43, 0.00, 0.00, 0.00, 9.00, 412.84, 4999.99),
(7, 7, 'Keyboard', 'Logitech Keyboard', 1.00, 'PCS', 500.00, 0.00, 0.00, 0.00, 9.00, 45.00, 545.00),
(8, 8, 'Keyboard', 'Logitech Keyboard', 1.00, 'PCS', 500.00, 0.00, 0.00, 0.00, 9.00, 45.00, 545.00),
(9, 9, 'mouse', 'mouse', 1.00, 'PCS', 1834.86, 0.00, 0.00, 0.00, 9.00, 165.14, 2000.00);

-- --------------------------------------------------------

--
-- Table structure for table `acc_currencies`
--

CREATE TABLE `acc_currencies` (
  `currency_id` int(10) UNSIGNED NOT NULL,
  `currency_code` varchar(10) NOT NULL,
  `currency_name` varchar(50) NOT NULL,
  `symbol` varchar(10) NOT NULL,
  `exchange_rate` decimal(12,4) DEFAULT 1.0000,
  `is_default` tinyint(1) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `acc_currencies`
--

INSERT INTO `acc_currencies` (`currency_id`, `currency_code`, `currency_name`, `symbol`, `exchange_rate`, `is_default`, `status`, `created_at`) VALUES
(1, 'INR', 'Indian Rupee', '₹', 1.0000, 1, 1, '2026-08-30 08:36:08'),
(2, 'USD', 'US Dollar', '$', 0.0120, 0, 1, '2026-08-30 08:36:08'),
(3, 'EUR', 'Euro', '€', 0.0110, 0, 0, '2026-08-30 08:36:08'),
(4, 'AED', 'UAE Dirham', 'د.إ', 0.0440, 0, 0, '2026-08-30 08:36:08'),
(5, 'GBP', 'British Pound', '£', 0.0095, 0, 0, '2026-08-30 08:36:08');

-- --------------------------------------------------------

--
-- Table structure for table `acc_invoices`
--

CREATE TABLE `acc_invoices` (
  `invoice_id` int(10) UNSIGNED NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `party_id` int(10) UNSIGNED NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `cgst_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `sgst_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `igst_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('DRAFT','FINALIZED','PAID','CANCELLED') DEFAULT 'FINALIZED',
  `created_at` datetime DEFAULT current_timestamp(),
  `place_of_supply` varchar(100) DEFAULT NULL,
  `is_interstate` tinyint(1) DEFAULT 0,
  `taxable_amount` decimal(12,2) DEFAULT 0.00,
  `discount_amount` decimal(12,2) DEFAULT 0.00,
  `round_off` decimal(8,2) DEFAULT 0.00,
  `advance_linked_amount` decimal(12,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `acc_invoices`
--

INSERT INTO `acc_invoices` (`invoice_id`, `invoice_number`, `party_id`, `invoice_date`, `due_date`, `subtotal`, `tax_amount`, `cgst_amount`, `sgst_amount`, `igst_amount`, `total_amount`, `paid_amount`, `status`, `created_at`, `place_of_supply`, `is_interstate`, `taxable_amount`, `discount_amount`, `round_off`, `advance_linked_amount`, `notes`) VALUES
(1, 'LXIN2025-26/1000', 38, '2026-09-08', '2026-09-23', 0.00, 82.57, 41.28, 41.28, 0.00, 1000.00, 1000.00, 'PAID', '2026-09-08 12:11:05', 'Kerala', 0, 917.43, 0.00, 0.00, 0.00, 'Thank you for your business! All disputes subject to local jurisdiction.'),
(2, 'LXIN2025-26/1001', 38, '2026-09-08', '2026-09-23', 0.00, 135.00, 67.50, 67.50, 0.00, 1635.00, 635.00, 'PAID', '2026-09-08 12:12:36', 'Kerala', 0, 1500.00, 0.00, 0.00, 1000.00, 'Thank you for your business! All disputes subject to local jurisdiction.'),
(3, 'LXIN2025-26/1002', 38, '2026-09-08', '2026-09-23', 0.00, 247.71, 123.86, 123.86, 0.00, 3000.00, 0.00, 'PAID', '2026-09-08 12:24:56', 'Kerala', 0, 2752.29, 0.00, 0.00, 3000.00, 'Thank you for your business! All disputes subject to local jurisdiction.'),
(4, 'LXIN2025-26/1003', 38, '2026-09-08', '2026-09-23', 0.00, 165.14, 82.57, 82.57, 0.00, 2000.00, 1000.00, 'PAID', '2026-09-08 12:36:44', 'Kerala', 0, 1834.86, 0.00, 0.00, 1000.00, 'Thank you for your business! All disputes subject to local jurisdiction.'),
(5, 'LXIN2025-26/1004', 38, '2026-09-08', '2026-09-23', 0.00, 41.28, 20.64, 20.64, 0.00, 500.00, 500.00, 'PAID', '2026-09-08 12:51:35', 'Kerala', 0, 458.72, 0.00, 0.00, 0.00, 'Thank you for your business! All disputes subject to local jurisdiction.'),
(6, 'LXIN2025-26/1005', 38, '2026-09-08', '2026-09-23', 0.00, 41.28, 20.64, 20.64, 0.00, 500.00, 0.00, 'PAID', '2026-09-08 12:57:09', 'Kerala', 0, 458.72, 0.00, 0.00, 500.00, 'Thank you for your business! All disputes subject to local jurisdiction.'),
(7, 'LXIN2025-26/1006', 38, '2026-09-08', '2026-09-23', 0.00, 41.28, 20.64, 20.64, 0.00, 500.00, 500.00, 'PAID', '2026-09-08 13:01:11', 'Kerala', 0, 458.72, 24.14, 0.00, 0.00, 'Thank you for your business! All disputes subject to local jurisdiction.'),
(8, 'LXIN2025-26/1007', 38, '2026-09-08', '2026-09-23', 0.00, 152.54, 76.27, 76.27, 0.00, 1000.00, 1000.00, 'PAID', '2026-09-08 14:07:37', 'Kerala', 0, 847.46, 0.00, 0.00, 0.00, 'Thank you for your business! All disputes subject to local jurisdiction.'),
(9, 'LXIN2025-26/1008', 38, '2026-09-08', '2026-09-23', 0.00, 412.84, 206.42, 206.42, 0.00, 5000.00, 5000.00, 'PAID', '2026-09-08 14:28:42', 'Kerala', 0, 4587.16, 0.00, 0.00, 0.00, 'Thank you for your business! All disputes subject to local jurisdiction.'),
(10, 'LXIN2025-26/1009', 39, '2026-09-08', '2026-09-23', 0.00, 45.00, 0.00, 0.00, 45.00, 545.00, 545.00, 'PAID', '2026-09-08 14:49:25', 'Karnataka', 1, 500.00, 0.00, 0.00, 0.00, 'Thank you for your business! All disputes subject to local jurisdiction.'),
(11, 'LXIN2025-26/1010', 39, '2026-09-08', '2026-09-23', 0.00, 180.00, 0.00, 0.00, 180.00, 1180.00, 1180.00, 'PAID', '2026-09-08 15:02:09', 'Karnataka', 1, 1000.00, 0.00, 0.00, 0.00, 'Thank you for your business! All disputes subject to local jurisdiction.'),
(12, 'LXIN2025-26/1011', 38, '2026-09-08', '2026-09-23', 0.00, 1125.00, 562.50, 562.50, 0.00, 8625.00, 5625.00, '', '2026-09-08 19:46:11', 'Kerala', 0, 7500.00, 0.00, 0.00, 0.00, 'Thank you for your business! All disputes subject to local jurisdiction.'),
(13, 'LXIN2025-26/1012', 38, '2026-09-08', '2026-09-23', 0.00, 412.84, 206.42, 206.42, 0.00, 5000.00, 0.00, '', '2026-09-08 19:47:35', 'Kerala', 0, 4587.16, 0.00, 0.00, 0.00, 'Thank you for your business! All disputes subject to local jurisdiction.'),
(14, 'LXIN2025-26/1013', 38, '2026-09-08', '2026-09-23', 0.00, 165.14, 82.57, 82.57, 0.00, 2000.00, 2000.00, 'PAID', '2026-09-08 20:31:36', 'Kerala', 0, 1834.86, 0.00, 0.00, 0.00, 'Thank you for your business! All disputes subject to local jurisdiction.'),
(15, 'LXIN2025-26/1014', 40, '2026-09-08', '2026-09-23', 0.00, 330.28, 0.00, 0.00, 330.28, 4000.00, 4000.00, 'PAID', '2026-09-08 22:18:14', 'Delhi', 1, 3669.72, 0.00, 0.00, 0.00, 'Thank you for your business! All disputes subject to local jurisdiction.'),
(16, 'LXIN2025-26/1015', 41, '2026-09-09', '2026-09-24', 0.00, 22881.36, 0.00, 0.00, 22881.36, 150000.00, 85000.00, 'PAID', '2026-09-09 10:48:29', 'Delhi', 1, 127118.64, 12.71, 0.00, 65000.00, 'Thank you for your business! All disputes subject to local jurisdiction.');

-- --------------------------------------------------------

--
-- Table structure for table `acc_invoice_items`
--

CREATE TABLE `acc_invoice_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `invoice_id` int(10) UNSIGNED NOT NULL,
  `item_id` int(10) UNSIGNED DEFAULT NULL,
  `item_name` varchar(200) NOT NULL,
  `hsn_sac` varchar(20) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `tax_rate` decimal(5,2) NOT NULL,
  `tax_amount` decimal(12,2) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL,
  `description` text DEFAULT NULL,
  `unit` varchar(20) DEFAULT 'PCS',
  `discount_percent` decimal(5,2) DEFAULT 0.00,
  `discount_amount` decimal(12,2) DEFAULT 0.00,
  `taxable_amount` decimal(12,2) DEFAULT 0.00,
  `cgst_rate` decimal(5,2) DEFAULT 0.00,
  `cgst_amount` decimal(12,2) DEFAULT 0.00,
  `sgst_rate` decimal(5,2) DEFAULT 0.00,
  `sgst_amount` decimal(12,2) DEFAULT 0.00,
  `igst_rate` decimal(5,2) DEFAULT 0.00,
  `igst_amount` decimal(12,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `acc_invoice_items`
--

INSERT INTO `acc_invoice_items` (`id`, `invoice_id`, `item_id`, `item_name`, `hsn_sac`, `quantity`, `unit_price`, `tax_rate`, `tax_amount`, `total_amount`, `total`, `description`, `unit`, `discount_percent`, `discount_amount`, `taxable_amount`, `cgst_rate`, `cgst_amount`, `sgst_rate`, `sgst_amount`, `igst_rate`, `igst_amount`) VALUES
(1, 1, NULL, 'Keyboard', NULL, 2.00, 458.72, 9.00, 82.57, 1000.00, 0.00, 'Logitech Keyboard', 'PCS', 0.00, 0.00, 917.43, 4.50, 41.28, 4.50, 41.28, 18.00, 0.00),
(2, 2, NULL, 'Keyboard', NULL, 3.00, 500.00, 9.00, 135.00, 1635.00, 0.00, 'Logitech Keyboard', 'PCS', 0.00, 0.00, 1500.00, 4.50, 67.50, 4.50, 67.50, 18.00, 0.00),
(3, 3, NULL, 'Keyboard', NULL, 5.00, 550.46, 9.00, 247.71, 3000.00, 0.00, 'Logitech Keyboard', 'PCS', 0.00, 0.00, 2752.29, 4.50, 123.86, 4.50, 123.86, 18.00, 0.00),
(4, 4, NULL, 'Keyboard', NULL, 1.00, 1834.86, 9.00, 165.14, 2000.00, 0.00, 'Logitech Keyboard', 'PCS', 0.00, 0.00, 1834.86, 4.50, 82.57, 4.50, 82.57, 18.00, 0.00),
(5, 5, NULL, 'Keyboard', NULL, 1.00, 458.72, 9.00, 41.28, 500.00, 0.00, 'Logitech Keyboard', 'PCS', 0.00, 0.00, 458.72, 4.50, 20.64, 4.50, 20.64, 18.00, 0.00),
(6, 6, NULL, 'Keyboard', NULL, 1.00, 458.72, 9.00, 41.28, 500.00, 0.00, 'Logitech Keyboard', 'PCS', 0.00, 0.00, 458.72, 4.50, 20.64, 4.50, 20.64, 18.00, 0.00),
(7, 7, NULL, 'Keyboard', NULL, 1.00, 482.86, 9.00, 41.28, 500.00, 0.00, 'Logitech Keyboard', 'PCS', 5.00, 24.14, 458.72, 4.50, 20.64, 4.50, 20.64, 18.00, 0.00),
(8, 8, NULL, 'Mouse', NULL, 1.00, 847.46, 18.00, 152.54, 1000.00, 0.00, 'Mouse', 'PCS', 0.00, 0.00, 847.46, 9.00, 76.27, 9.00, 76.27, 18.00, 0.00),
(9, 9, NULL, 'Keyboard', NULL, 5.00, 917.43, 9.00, 412.84, 5000.00, 0.00, 'Logitech Keyboard', 'PCS', 0.00, 0.00, 4587.16, 4.50, 206.42, 4.50, 206.42, 18.00, 0.00),
(10, 10, NULL, 'Keyboard', NULL, 1.00, 500.00, 9.00, 45.00, 545.00, 0.00, 'Logitech Keyboard', 'PCS', 0.00, 0.00, 500.00, 9.00, 0.00, 9.00, 0.00, 9.00, 45.00),
(11, 11, NULL, 'Mouse', NULL, 1.00, 1000.00, 18.00, 180.00, 1180.00, 0.00, 'Mouse', 'PCS', 0.00, 0.00, 1000.00, 9.00, 0.00, 9.00, 0.00, 18.00, 180.00),
(12, 12, NULL, 'Mouse', NULL, 5.00, 1000.00, 18.00, 900.00, 5900.00, 0.00, 'Mouse', 'PCS', 0.00, 0.00, 5000.00, 9.00, 450.00, 9.00, 450.00, 18.00, 0.00),
(13, 12, NULL, 'Keyboard', NULL, 5.00, 500.00, 9.00, 225.00, 2725.00, 0.00, 'Logitech Keyboard', 'PCS', 0.00, 0.00, 2500.00, 4.50, 112.50, 4.50, 112.50, 18.00, 0.00),
(14, 13, NULL, 'Keyboard', NULL, 2.00, 2293.58, 9.00, 412.84, 5000.00, 0.00, 'Logitech Keyboard', 'PCS', 0.00, 0.00, 4587.16, 4.50, 206.42, 4.50, 206.42, 18.00, 0.00),
(15, 14, NULL, 'mouse', NULL, 1.00, 1834.86, 9.00, 165.14, 2000.00, 0.00, 'mouse', 'PCS', 0.00, 0.00, 1834.86, 4.50, 82.57, 4.50, 82.57, 18.00, 0.00),
(16, 15, NULL, 'Keyboard', NULL, 1.00, 3669.72, 9.00, 330.28, 4000.00, 0.00, 'Logitech Keyboard', 'PCS', 0.00, 0.00, 3669.72, 9.00, 0.00, 9.00, 0.00, 9.00, 330.28),
(17, 16, NULL, 'Website Development', NULL, 1.00, 127131.36, 18.00, 22881.36, 150000.00, 0.00, '', 'SRV', 0.01, 12.71, 127118.64, 9.00, 0.00, 9.00, 0.00, 18.00, 22881.36);

-- --------------------------------------------------------

--
-- Table structure for table `acc_items`
--

CREATE TABLE `acc_items` (
  `item_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `item_type` enum('PRODUCT','SERVICE') NOT NULL DEFAULT 'PRODUCT',
  `sku` varchar(100) DEFAULT NULL,
  `hsn_sac` varchar(20) DEFAULT NULL,
  `unit` varchar(20) DEFAULT 'Nos',
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `opening_stock` decimal(12,2) NOT NULL DEFAULT 0.00,
  `current_stock` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 18.00,
  `status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `acc_items`
--

INSERT INTO `acc_items` (`item_id`, `name`, `description`, `category_id`, `item_type`, `sku`, `hsn_sac`, `unit`, `price`, `opening_stock`, `current_stock`, `tax_rate`, `status`) VALUES
(1, 'Mouse', 'Mouse', 1, 'PRODUCT', 'SS-001-TX', '4848', 'PCS', 1000.00, 15.00, 7.00, 18.00, 1),
(2, 'Keyboard', 'Logitech Keyboard', 1, 'PRODUCT', 'SS-001-TT', '778528', 'PCS', 500.00, 15.00, -13.00, 9.00, 1),
(3, 'mouse', 'mouse', 1, 'PRODUCT', 'SS-001-TX', '778524', 'PCS', 1000.00, 25.00, 17.00, 9.00, 1),
(4, 'Website Development', '', 7, 'SERVICE', '1ADC166', '45861', 'SRV', 1000.00, 0.00, 0.00, 18.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `acc_parties`
--

CREATE TABLE `acc_parties` (
  `party_id` int(10) UNSIGNED NOT NULL,
  `party_type` enum('SELLER','CUSTOMER','VENDOR') NOT NULL,
  `source_seller_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'FK to mock_sellers or Yo!Kart seller table',
  `name` varchar(150) NOT NULL,
  `business_name` varchar(150) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `state` varchar(50) NOT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `gstin` varchar(20) DEFAULT NULL,
  `pan` varchar(20) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `acc_parties`
--

INSERT INTO `acc_parties` (`party_id`, `party_type`, `source_seller_id`, `name`, `business_name`, `email`, `phone`, `city`, `address`, `shipping_address`, `state`, `pincode`, `gstin`, `pan`, `status`, `created_at`) VALUES
(1, 'SELLER', 1, 'Rahul Sharma', 'TechZone Electronics', 'rahul@techzone.com', '9876543210', NULL, NULL, NULL, 'Kerala', NULL, '32AAAAA0000A1Z5', NULL, 1, '2026-08-28 21:16:32'),
(2, 'SELLER', 2, 'Priya Patel', 'StyleCraft Apparel', 'priya@stylecraft.com', '9876543211', NULL, NULL, NULL, 'Maharashtra', NULL, '27BBBBB1111B1Z2', NULL, 1, '2026-08-28 21:16:32'),
(38, 'CUSTOMER', NULL, 'Ozil', 'Acme Corp', 'ozil@gmail.com', '6664545454', 'KERALA', 'KERALA', 'KERALA', 'Kerala', '665561', '2DD5SASSS545545', 'SASAS522E5', 1, '2026-09-08 12:07:22'),
(39, 'VENDOR', NULL, 'Sravan ', 'ZOY associates', 'zz@gmail.com', '7894556666', 'NK', '123, MG Road\r\nIndiranagar\r\nBengaluru, Karnataka – 560038\r\nIndia', '123, MG Road\r\nIndiranagar\r\nBengaluru, Karnataka – 560038\r\nIndia', 'Karnataka', '987455', '32aaaaaaaaaaaaa', 'aaaaaaaaaa', 1, '2026-09-08 14:39:26'),
(40, 'CUSTOMER', NULL, 'Hussain', 'Beta Logistics', 'hussain@yahoo.com', '5564544654', 'Agra', 'Building No:452, Ramji street, flat no:3, Near Post office, Residential area, Agra, Agra', 'Building No:452, Ramji street, flat no:3, Near Post office, Residential area, Agra, Agra', 'Delhi', '878484', '2DD5SASSS575545', 'SASAS522D5', 1, '2026-09-08 22:17:43'),
(41, 'CUSTOMER', NULL, 'Sai Krishna', '', 'Saikrishna@gmail.com', '7581654452', 'Dwaraka', 'sector22, Dwaraka', 'sector22, Dwaraka', 'Delhi', '562313', '', '', 1, '2026-09-09 10:40:41'),
(42, 'CUSTOMER', NULL, 'Donald J Trump', 'Pentagon ltd', 'donaldjtrump@gmail.com', '9875201022', 'Agra', 'Agra', 'Agra', 'Delhi', '690201', '', 'RTS556121A', 1, '2026-09-09 10:45:30');

-- --------------------------------------------------------

--
-- Table structure for table `acc_payments`
--

CREATE TABLE `acc_payments` (
  `payment_id` int(10) UNSIGNED NOT NULL,
  `payment_type` enum('PAY_IN','PAY_OUT') NOT NULL,
  `party_id` int(10) UNSIGNED NOT NULL,
  `invoice_id` int(10) UNSIGNED DEFAULT NULL,
  `bank_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `payment_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('COMPLETED','CANCELLED') DEFAULT 'COMPLETED',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `acc_payments`
--

INSERT INTO `acc_payments` (`payment_id`, `payment_type`, `party_id`, `invoice_id`, `bank_id`, `amount`, `payment_method`, `reference_number`, `payment_date`, `notes`, `status`, `created_at`) VALUES
(1, 'PAY_IN', 38, NULL, 1, 1000.00, 'Bank Account', 'DIRECT-PAY-LXIN2025-26/1000', '2026-09-08', 'Direct payment on Invoice LXIN2025-26/1000', 'COMPLETED', '2026-09-08 12:11:05'),
(2, 'PAY_IN', 38, NULL, 1, 5000.00, 'Bank Transfer', 'LXPI2026-25/3000', '2026-09-08', '', 'COMPLETED', '2026-09-08 12:11:34'),
(3, 'PAY_IN', 38, NULL, 1, 635.00, 'Bank Account', 'DIRECT-PAY-LXIN2025-26/1001', '2026-09-08', 'Direct payment on Invoice LXIN2025-26/1001', 'COMPLETED', '2026-09-08 12:12:36'),
(4, 'PAY_OUT', 38, NULL, 1, 1635.00, 'Bank Account', 'DIRECT-PAY-LXCR2025-26/2000', '2026-09-08', 'Direct payout on Credit Note LXCR2025-26/2000', 'COMPLETED', '2026-09-08 12:14:04'),
(5, 'PAY_OUT', 38, NULL, 1, 1000.00, 'Bank Account', 'DIRECT-PAY-LXCR2025-26/2001', '2026-09-08', 'Direct payout on Credit Note LXCR2025-26/2001', 'COMPLETED', '2026-09-08 12:17:42'),
(6, 'PAY_OUT', 38, NULL, 1, 2000.00, 'Bank Transfer', 'LXPO2025-26/4000', '2026-09-08', '', 'COMPLETED', '2026-09-08 12:24:16'),
(7, 'PAY_OUT', 38, NULL, 1, 1000.00, 'Bank Transfer', 'LXPO2025-26/4001', '2026-09-08', '', 'COMPLETED', '2026-09-08 12:26:50'),
(8, 'PAY_IN', 38, NULL, 8, 1000.00, 'Bank Account', 'DIRECT-PAY-LXIN2025-26/1003', '2026-09-08', 'Direct payment on Invoice LXIN2025-26/1003', 'COMPLETED', '2026-09-08 12:36:44'),
(9, 'PAY_IN', 38, NULL, 1, 500.00, 'Bank Account', 'DIRECT-PAY-LXIN2025-26/1004', '2026-09-08', 'Direct payment on Invoice LXIN2025-26/1004', 'COMPLETED', '2026-09-08 12:51:35'),
(10, 'PAY_OUT', 38, NULL, 1, 200.00, 'Bank Account', 'DIRECT-PAY-LXCR2025-26/2003', '2026-09-08', 'Direct payout on Credit Note LXCR2025-26/2003', 'COMPLETED', '2026-09-08 12:51:58'),
(11, 'PAY_IN', 38, NULL, 3, 500.00, 'Bank Transfer', 'LXPI2026-25/3001', '2026-09-08', '', 'COMPLETED', '2026-09-08 12:55:14'),
(12, 'PAY_IN', 38, NULL, 1, 500.00, 'Bank Account', 'DIRECT-PAY-LXIN2025-26/1006', '2026-09-08', 'Direct payment on Invoice LXIN2025-26/1006', 'COMPLETED', '2026-09-08 13:01:11'),
(13, 'PAY_IN', 38, NULL, 1, 1000.00, 'Bank Account', 'DIRECT-PAY-LXIN2025-26/1007', '2026-09-08', 'Direct payment on Invoice LXIN2025-26/1007', 'COMPLETED', '2026-09-08 14:07:37'),
(14, 'PAY_OUT', 38, NULL, 1, 500.00, 'Bank Account', 'DIRECT-PAY-LXCR2025-26/2004', '2026-09-08', 'Direct payout on Credit Note LXCR2025-26/2004', 'COMPLETED', '2026-09-08 14:08:33'),
(15, 'PAY_IN', 38, NULL, 8, 5000.00, 'Bank Account', 'DIRECT-PAY-LXIN2025-26/1008', '2026-09-08', 'Direct payment on Invoice LXIN2025-26/1008', 'COMPLETED', '2026-09-08 14:28:42'),
(16, 'PAY_OUT', 38, NULL, 8, 5000.00, 'Bank Account', 'DIRECT-PAY-LXCR2025-26/2005', '2026-09-08', 'Direct payout on Credit Note LXCR2025-26/2005', 'COMPLETED', '2026-09-08 14:29:41'),
(17, 'PAY_IN', 39, NULL, 1, 200.00, 'Bank Account', 'DIRECT-PAY-LXIN2025-26/1009', '2026-09-08', 'Direct payment on Invoice LXIN2025-26/1009', 'COMPLETED', '2026-09-08 14:49:25'),
(18, 'PAY_IN', 39, NULL, 3, 45.00, 'Cash', 'DIRECT-PAY-LXIN2025-26/1009', '2026-09-08', 'Direct payment on Invoice LXIN2025-26/1009', 'COMPLETED', '2026-09-08 14:49:25'),
(19, 'PAY_IN', 39, NULL, 1, 300.00, 'Cash', 'LXPI2026-25/3002', '2026-09-08', '', 'CANCELLED', '2026-09-08 14:50:51'),
(20, 'PAY_IN', 39, NULL, 1, 300.00, 'Bank Transfer', 'LXPI2026-25/3003', '2026-09-08', '', 'COMPLETED', '2026-09-08 14:56:34'),
(21, 'PAY_OUT', 39, NULL, 1, 300.00, 'Bank Account', 'DIRECT-PAY-LXCR2025-26/2006', '2026-09-08', 'Direct payout on Credit Note LXCR2025-26/2006', 'COMPLETED', '2026-09-08 14:57:38'),
(22, 'PAY_OUT', 39, NULL, 1, 545.00, 'Bank Account', 'DIRECT-PAY-LXCR2025-26/2007', '2026-09-08', 'Direct payout on Credit Note LXCR2025-26/2007', 'COMPLETED', '2026-09-08 14:59:36'),
(23, 'PAY_IN', 39, NULL, 1, 180.00, 'Bank Account', 'DIRECT-PAY-LXIN2025-26/1010', '2026-09-08', 'Direct payment on Invoice LXIN2025-26/1010', 'COMPLETED', '2026-09-08 15:02:09'),
(24, 'PAY_IN', 39, NULL, 1, 1000.00, 'Bank Transfer', 'LXPI2026-25/3004', '2026-09-08', '', 'COMPLETED', '2026-09-08 15:06:27'),
(25, 'PAY_IN', 38, NULL, 1, 5625.00, 'Bank Account', 'DIRECT-PAY-LXIN2025-26/1011', '2026-09-08', 'Direct payment on Invoice LXIN2025-26/1011', 'COMPLETED', '2026-09-08 19:46:11'),
(26, 'PAY_IN', 39, NULL, 1, 5000.00, 'Bank Transfer', 'LXPI2026-25/3005', '2026-09-08', '', 'COMPLETED', '2026-09-08 20:09:03'),
(27, 'PAY_IN', 38, NULL, 1, 2000.00, 'Bank Account', 'DIRECT-PAY-LXIN2025-26/1013', '2026-09-08', 'Direct payment on Invoice LXIN2025-26/1013', 'COMPLETED', '2026-09-08 20:31:36'),
(28, 'PAY_OUT', 38, NULL, 1, 1000.00, 'Bank Account', 'DIRECT-PAY-LXCR2025-26/2008', '2026-09-08', 'Direct payout on Credit Note LXCR2025-26/2008', 'COMPLETED', '2026-09-08 20:31:56'),
(29, 'PAY_OUT', 38, NULL, 1, 1000.00, 'Bank Transfer', 'LXPO2025-26/4002', '2026-09-08', '', 'COMPLETED', '2026-09-08 20:37:51'),
(30, 'PAY_OUT', 39, NULL, 1, 5000.00, 'Bank Transfer', 'LXPO2025-26/4003', '2026-09-08', '', 'COMPLETED', '2026-09-08 20:38:52'),
(31, 'PAY_IN', 38, NULL, 1, 5000.00, 'Bank Transfer', 'LXPI2026-25/3006', '2026-09-08', '', 'COMPLETED', '2026-09-08 20:43:09'),
(32, 'PAY_IN', 40, NULL, 1, 2000.00, 'Bank Account', 'DIRECT-PAY-LXIN2025-26/1014', '2026-09-08', 'Direct payment on Invoice LXIN2025-26/1014', 'COMPLETED', '2026-09-08 22:18:14'),
(33, 'PAY_IN', 40, NULL, 1, 2000.00, 'Bank Transfer', 'LXPI2026-25/3007', '2026-09-08', '', 'COMPLETED', '2026-09-08 23:18:23'),
(34, 'PAY_IN', 41, NULL, 4, 35000.00, 'Bank Transfer', 'LXPI2026-25/3008', '2026-09-09', 'Advance', 'COMPLETED', '2026-09-09 10:41:24'),
(35, 'PAY_IN', 41, NULL, 7, 30000.00, 'Bank Transfer', 'LXPI2026-25/3009', '2026-09-09', '', 'COMPLETED', '2026-09-09 10:41:52'),
(36, 'PAY_IN', 41, NULL, 3, 20000.00, 'Bank Account', 'DIRECT-PAY-LXIN2025-26/1015', '2026-09-09', 'Direct payment on Invoice LXIN2025-26/1015', 'COMPLETED', '2026-09-09 10:48:29'),
(37, 'PAY_IN', 41, NULL, 7, 50000.00, 'Bank Transfer', 'LXPI2026-25/3010', '2026-09-09', '', 'COMPLETED', '2026-09-09 10:52:31'),
(38, 'PAY_IN', 41, NULL, 3, 15000.00, 'Bank Transfer', 'LXPI2026-25/3011', '2026-09-09', '', 'COMPLETED', '2026-09-09 10:53:35');

-- --------------------------------------------------------

--
-- Table structure for table `acc_payment_allocations`
--

CREATE TABLE `acc_payment_allocations` (
  `allocation_id` int(10) UNSIGNED NOT NULL,
  `payment_id` int(10) UNSIGNED NOT NULL,
  `invoice_id` int(10) UNSIGNED DEFAULT NULL,
  `credit_note_id` int(10) UNSIGNED DEFAULT NULL,
  `linked_payment_id` int(10) UNSIGNED DEFAULT NULL,
  `allocated_amount` decimal(12,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `acc_payment_allocations`
--

INSERT INTO `acc_payment_allocations` (`allocation_id`, `payment_id`, `invoice_id`, `credit_note_id`, `linked_payment_id`, `allocated_amount`, `created_at`) VALUES
(1, 1, 1, NULL, NULL, 1000.00, '2026-09-08 12:11:05'),
(2, 3, 2, NULL, NULL, 635.00, '2026-09-08 12:12:36'),
(3, 2, 2, NULL, NULL, 1000.00, '2026-09-08 12:12:36'),
(4, 4, NULL, 1, NULL, 1635.00, '2026-09-08 12:14:04'),
(5, 5, NULL, 2, NULL, 1000.00, '2026-09-08 12:17:42'),
(6, 2, 3, NULL, NULL, 3000.00, '2026-09-08 12:24:56'),
(7, 6, NULL, 3, NULL, 2000.00, '2026-09-08 12:26:06'),
(8, 7, NULL, 3, NULL, 1000.00, '2026-09-08 12:26:50'),
(9, 8, 4, NULL, NULL, 1000.00, '2026-09-08 12:36:44'),
(10, 2, 4, NULL, NULL, 1000.00, '2026-09-08 12:36:44'),
(11, 9, 5, NULL, NULL, 500.00, '2026-09-08 12:51:35'),
(12, 10, NULL, 4, NULL, 200.00, '2026-09-08 12:51:58'),
(13, 11, 6, NULL, NULL, 500.00, '2026-09-08 12:57:09'),
(14, 12, 7, NULL, NULL, 500.00, '2026-09-08 13:01:11'),
(15, 13, 8, NULL, NULL, 1000.00, '2026-09-08 14:07:37'),
(16, 14, NULL, 5, NULL, 500.00, '2026-09-08 14:08:33'),
(17, 15, 9, NULL, NULL, 5000.00, '2026-09-08 14:28:42'),
(18, 16, NULL, 6, NULL, 5000.00, '2026-09-08 14:29:41'),
(19, 17, 10, NULL, NULL, 200.00, '2026-09-08 14:49:25'),
(20, 18, 10, NULL, NULL, 45.00, '2026-09-08 14:49:25'),
(21, 20, 10, NULL, NULL, 300.00, '2026-09-08 14:56:34'),
(22, 21, NULL, 7, NULL, 300.00, '2026-09-08 14:57:38'),
(23, 22, NULL, 8, NULL, 545.00, '2026-09-08 14:59:36'),
(24, 23, 11, NULL, NULL, 180.00, '2026-09-08 15:02:09'),
(25, 24, 11, NULL, NULL, 1000.00, '2026-09-08 15:06:27'),
(26, 25, 12, NULL, NULL, 5625.00, '2026-09-08 19:46:11'),
(27, 27, 14, NULL, NULL, 2000.00, '2026-09-08 20:31:36'),
(28, 28, NULL, 9, NULL, 1000.00, '2026-09-08 20:31:56'),
(29, 29, NULL, 9, NULL, 1000.00, '2026-09-08 20:37:51'),
(30, 30, NULL, 7, NULL, 245.00, '2026-09-08 20:38:52'),
(31, 30, NULL, NULL, 26, 5000.00, '2026-09-08 20:38:52'),
(32, 32, 15, NULL, NULL, 2000.00, '2026-09-08 22:18:14'),
(33, 33, 15, NULL, NULL, 2000.00, '2026-09-08 23:18:23'),
(34, 36, 16, NULL, NULL, 20000.00, '2026-09-09 10:48:29'),
(35, 34, 16, NULL, NULL, 35000.00, '2026-09-09 10:48:29'),
(36, 35, 16, NULL, NULL, 30000.00, '2026-09-09 10:48:29'),
(37, 37, 16, NULL, NULL, 50000.00, '2026-09-09 10:52:31'),
(38, 38, 16, NULL, NULL, 15000.00, '2026-09-09 10:53:35');

-- --------------------------------------------------------

--
-- Table structure for table `acc_settings`
--

CREATE TABLE `acc_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `acc_settings`
--

INSERT INTO `acc_settings` (`setting_key`, `setting_value`) VALUES
('company_address', 'LX Tower, Technopark, Phase 3, Kerala, India'),
('company_email', 'billing@saud.com'),
('company_gstin', '32AACCL1234F1Z1'),
('company_logo', 'company_logo_1788451817.png'),
('company_name', 'Saud\'s Marketplace Pvt Ltd '),
('company_phone', '+91 9876543210'),
('company_state', 'Kerala'),
('credit_note_next_number', '2009'),
('credit_note_prefix', 'LXCR2025-26/'),
('default_currency', 'INR'),
('digital_signature', ''),
('financial_year', '2026-2027'),
('invoice_footer_notes', 'Thank you for your business! All disputes subject to local jurisdiction.'),
('invoice_next_number', '1016'),
('invoice_prefix', 'LXIN2025-26/'),
('invoice_template', 'classic_gst'),
('payin_next_number', '3012'),
('payin_prefix', 'LXPI2026-25/'),
('payout_next_number', '4004'),
('payout_prefix', 'LXPO2025-26/'),
('pay_in_next_number', '1002'),
('pay_in_prefix', 'REC-'),
('pay_out_next_number', '1001'),
('pay_out_prefix', 'PAY-'),
('timezone', 'Asia/Kolkata');

-- --------------------------------------------------------

--
-- Table structure for table `acc_settlements`
--

CREATE TABLE `acc_settlements` (
  `settlement_id` int(10) UNSIGNED NOT NULL,
  `settlement_reference` varchar(50) NOT NULL,
  `seller_id` int(10) UNSIGNED NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `gross_sales` decimal(12,2) NOT NULL,
  `commission_amount` decimal(12,2) NOT NULL,
  `tax_on_commission` decimal(12,2) NOT NULL,
  `net_payable` decimal(12,2) NOT NULL,
  `status` enum('PENDING','PAID','CANCELLED') DEFAULT 'PENDING',
  `payout_date` date DEFAULT NULL,
  `bank_ref` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `acc_settlements`
--

INSERT INTO `acc_settlements` (`settlement_id`, `settlement_reference`, `seller_id`, `period_start`, `period_end`, `gross_sales`, `commission_amount`, `tax_on_commission`, `net_payable`, `status`, `payout_date`, `bank_ref`, `created_at`) VALUES
(1, 'SETTL-1-20260908151217', 1, '2026-08-01', '2026-08-31', 23500.00, 2350.00, 423.00, 20727.00, 'PENDING', NULL, NULL, '2026-09-08 15:12:17');

-- --------------------------------------------------------

--
-- Table structure for table `acc_states`
--

CREATE TABLE `acc_states` (
  `state_code` varchar(2) NOT NULL,
  `state_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `acc_states`
--

INSERT INTO `acc_states` (`state_code`, `state_name`) VALUES
('01', 'Jammu & Kashmir'),
('02', 'Himachal Pradesh'),
('03', 'Punjab'),
('04', 'Chandigarh'),
('05', 'Uttarakhand'),
('06', 'Haryana'),
('07', 'Delhi'),
('08', 'Rajasthan'),
('09', 'Uttar Pradesh'),
('10', 'Bihar'),
('11', 'Sikkim'),
('12', 'Arunachal Pradesh'),
('13', 'Nagaland'),
('14', 'Manipur'),
('15', 'Mizoram'),
('16', 'Tripura'),
('17', 'Meghalaya'),
('18', 'Assam'),
('19', 'West Bengal'),
('20', 'Jharkhand'),
('21', 'Odisha'),
('22', 'Chhattisgarh'),
('23', 'Madhya Pradesh'),
('24', 'Gujarat'),
('26', 'Dadra & Nagar Haveli and Daman & Diu'),
('27', 'Maharashtra'),
('29', 'Karnataka'),
('30', 'Goa'),
('31', 'Lakshadweep'),
('32', 'Kerala'),
('33', 'Tamil Nadu'),
('34', 'Puducherry'),
('35', 'Andaman & Nicobar Islands'),
('36', 'Telangana'),
('37', 'Andhra Pradesh'),
('38', 'Ladakh'),
('97', 'Other Territory');

-- --------------------------------------------------------

--
-- Table structure for table `acc_taxes`
--

CREATE TABLE `acc_taxes` (
  `tax_id` int(10) UNSIGNED NOT NULL,
  `tax_name` varchar(50) NOT NULL,
  `tax_rate` decimal(5,2) NOT NULL,
  `cgst_rate` decimal(5,2) DEFAULT 0.00,
  `sgst_rate` decimal(5,2) DEFAULT 0.00,
  `igst_rate` decimal(5,2) DEFAULT 0.00,
  `status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `acc_taxes`
--

INSERT INTO `acc_taxes` (`tax_id`, `tax_name`, `tax_rate`, `cgst_rate`, `sgst_rate`, `igst_rate`, `status`) VALUES
(1, 'GST 0%', 0.00, 0.00, 0.00, 0.00, 1),
(2, 'GST 5%', 5.00, 2.50, 2.50, 5.00, 0),
(3, 'GST 12%', 12.00, 6.00, 6.00, 12.00, 0),
(4, 'GST 18%', 18.00, 9.00, 9.00, 18.00, 0),
(5, 'GST 28%', 28.00, 14.00, 14.00, 28.00, 1),
(6, 'GST 30%', 30.00, 15.00, 15.00, 30.00, 1),
(7, 'GST 9%', 9.00, 4.50, 4.50, 9.00, 1),
(8, 'GST 18%', 18.00, 9.00, 9.00, 18.00, 1),
(9, 'GST 3%', 3.00, 1.50, 1.50, 3.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `acc_units`
--

CREATE TABLE `acc_units` (
  `unit_id` int(10) UNSIGNED NOT NULL,
  `unit_name` varchar(50) NOT NULL,
  `unit_symbol` varchar(10) NOT NULL,
  `unit_type` varchar(50) DEFAULT 'Quantity',
  `status` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `acc_units`
--

INSERT INTO `acc_units` (`unit_id`, `unit_name`, `unit_symbol`, `unit_type`, `status`, `created_at`) VALUES
(1, 'Pieces', 'PCS', 'Quantity', 1, '2026-08-30 08:20:36'),
(2, 'Numbers', 'NOS', 'Quantity', 1, '2026-08-30 08:20:36'),
(3, 'Kilogram', 'KG', 'Weight', 1, '2026-08-30 08:20:36'),
(4, 'Meter', 'MTR', 'Quantity', 1, '2026-08-30 08:20:36'),
(5, 'Box', 'BOX', 'Quantity', 1, '2026-08-30 08:20:36'),
(6, 'Service', 'SRV', 'Service', 1, '2026-08-30 08:20:36'),
(7, 'Time', 'HR', 'Time', 1, '2026-08-31 00:02:49');

-- --------------------------------------------------------

--
-- Table structure for table `mock_orders`
--

CREATE TABLE `mock_orders` (
  `order_id` int(10) UNSIGNED NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `seller_id` int(10) UNSIGNED NOT NULL,
  `order_date` date NOT NULL,
  `gross_amount` decimal(12,2) NOT NULL,
  `order_status` enum('Completed','Refunded','Cancelled') DEFAULT 'Completed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mock_orders`
--

INSERT INTO `mock_orders` (`order_id`, `order_number`, `seller_id`, `order_date`, `gross_amount`, `order_status`) VALUES
(1, 'ORD-2026-001', 1, '2026-08-01', 15000.00, 'Completed'),
(2, 'ORD-2026-002', 1, '2026-08-05', 8500.00, 'Completed'),
(3, 'ORD-2026-003', 2, '2026-08-10', 22000.00, 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `mock_sellers`
--

CREATE TABLE `mock_sellers` (
  `seller_id` int(10) UNSIGNED NOT NULL,
  `store_name` varchar(150) NOT NULL,
  `owner_name` varchar(150) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `gstin` varchar(20) DEFAULT NULL,
  `state` varchar(50) NOT NULL,
  `commission_rate` decimal(5,2) DEFAULT 10.00,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mock_sellers`
--

INSERT INTO `mock_sellers` (`seller_id`, `store_name`, `owner_name`, `email`, `phone`, `gstin`, `state`, `commission_rate`, `created_at`) VALUES
(1, 'TechZone Electronics', 'Rahul Sharma', 'rahul@techzone.com', '9876543210', '32AAAAA0000A1Z5', 'Kerala', 10.00, '2026-08-28 20:30:15'),
(2, 'StyleCraft Apparel', 'Priya Patel', 'priya@stylecraft.com', '9876543211', '27BBBBB1111B1Z2', 'Maharashtra', 12.50, '2026-08-28 20:30:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `acc_bank_accounts`
--
ALTER TABLE `acc_bank_accounts`
  ADD PRIMARY KEY (`bank_id`);

--
-- Indexes for table `acc_categories`
--
ALTER TABLE `acc_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `acc_credit_notes`
--
ALTER TABLE `acc_credit_notes`
  ADD PRIMARY KEY (`credit_note_id`),
  ADD UNIQUE KEY `credit_note_number` (`credit_note_number`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `party_id` (`party_id`);

--
-- Indexes for table `acc_credit_note_items`
--
ALTER TABLE `acc_credit_note_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `credit_note_id` (`credit_note_id`);

--
-- Indexes for table `acc_currencies`
--
ALTER TABLE `acc_currencies`
  ADD PRIMARY KEY (`currency_id`);

--
-- Indexes for table `acc_invoices`
--
ALTER TABLE `acc_invoices`
  ADD PRIMARY KEY (`invoice_id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `party_id` (`party_id`);

--
-- Indexes for table `acc_invoice_items`
--
ALTER TABLE `acc_invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `acc_items`
--
ALTER TABLE `acc_items`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `acc_parties`
--
ALTER TABLE `acc_parties`
  ADD PRIMARY KEY (`party_id`);

--
-- Indexes for table `acc_payments`
--
ALTER TABLE `acc_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `party_id` (`party_id`),
  ADD KEY `bank_id` (`bank_id`);

--
-- Indexes for table `acc_payment_allocations`
--
ALTER TABLE `acc_payment_allocations`
  ADD PRIMARY KEY (`allocation_id`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `acc_settings`
--
ALTER TABLE `acc_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `acc_settlements`
--
ALTER TABLE `acc_settlements`
  ADD PRIMARY KEY (`settlement_id`),
  ADD UNIQUE KEY `settlement_reference` (`settlement_reference`);

--
-- Indexes for table `acc_states`
--
ALTER TABLE `acc_states`
  ADD PRIMARY KEY (`state_code`);

--
-- Indexes for table `acc_taxes`
--
ALTER TABLE `acc_taxes`
  ADD PRIMARY KEY (`tax_id`);

--
-- Indexes for table `acc_units`
--
ALTER TABLE `acc_units`
  ADD PRIMARY KEY (`unit_id`);

--
-- Indexes for table `mock_orders`
--
ALTER TABLE `mock_orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `mock_sellers`
--
ALTER TABLE `mock_sellers`
  ADD PRIMARY KEY (`seller_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `acc_bank_accounts`
--
ALTER TABLE `acc_bank_accounts`
  MODIFY `bank_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `acc_categories`
--
ALTER TABLE `acc_categories`
  MODIFY `category_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `acc_credit_notes`
--
ALTER TABLE `acc_credit_notes`
  MODIFY `credit_note_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `acc_credit_note_items`
--
ALTER TABLE `acc_credit_note_items`
  MODIFY `item_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `acc_currencies`
--
ALTER TABLE `acc_currencies`
  MODIFY `currency_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `acc_invoices`
--
ALTER TABLE `acc_invoices`
  MODIFY `invoice_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `acc_invoice_items`
--
ALTER TABLE `acc_invoice_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `acc_items`
--
ALTER TABLE `acc_items`
  MODIFY `item_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `acc_parties`
--
ALTER TABLE `acc_parties`
  MODIFY `party_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `acc_payments`
--
ALTER TABLE `acc_payments`
  MODIFY `payment_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `acc_payment_allocations`
--
ALTER TABLE `acc_payment_allocations`
  MODIFY `allocation_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `acc_settlements`
--
ALTER TABLE `acc_settlements`
  MODIFY `settlement_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `acc_taxes`
--
ALTER TABLE `acc_taxes`
  MODIFY `tax_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `acc_units`
--
ALTER TABLE `acc_units`
  MODIFY `unit_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `mock_orders`
--
ALTER TABLE `mock_orders`
  MODIFY `order_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `mock_sellers`
--
ALTER TABLE `mock_sellers`
  MODIFY `seller_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `acc_credit_notes`
--
ALTER TABLE `acc_credit_notes`
  ADD CONSTRAINT `acc_credit_notes_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `acc_invoices` (`invoice_id`),
  ADD CONSTRAINT `acc_credit_notes_ibfk_2` FOREIGN KEY (`party_id`) REFERENCES `acc_parties` (`party_id`);

--
-- Constraints for table `acc_credit_note_items`
--
ALTER TABLE `acc_credit_note_items`
  ADD CONSTRAINT `acc_credit_note_items_ibfk_1` FOREIGN KEY (`credit_note_id`) REFERENCES `acc_credit_notes` (`credit_note_id`) ON DELETE CASCADE;

--
-- Constraints for table `acc_invoices`
--
ALTER TABLE `acc_invoices`
  ADD CONSTRAINT `acc_invoices_ibfk_1` FOREIGN KEY (`party_id`) REFERENCES `acc_parties` (`party_id`);

--
-- Constraints for table `acc_invoice_items`
--
ALTER TABLE `acc_invoice_items`
  ADD CONSTRAINT `acc_invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `acc_invoices` (`invoice_id`) ON DELETE CASCADE;

--
-- Constraints for table `acc_payments`
--
ALTER TABLE `acc_payments`
  ADD CONSTRAINT `acc_payments_ibfk_1` FOREIGN KEY (`party_id`) REFERENCES `acc_parties` (`party_id`),
  ADD CONSTRAINT `acc_payments_ibfk_2` FOREIGN KEY (`bank_id`) REFERENCES `acc_bank_accounts` (`bank_id`);

--
-- Constraints for table `acc_payment_allocations`
--
ALTER TABLE `acc_payment_allocations`
  ADD CONSTRAINT `acc_payment_allocations_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `acc_payments` (`payment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `acc_payment_allocations_ibfk_2` FOREIGN KEY (`invoice_id`) REFERENCES `acc_invoices` (`invoice_id`) ON DELETE CASCADE;

--
-- Constraints for table `mock_orders`
--
ALTER TABLE `mock_orders`
  ADD CONSTRAINT `mock_orders_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `mock_sellers` (`seller_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
