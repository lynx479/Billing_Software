-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 07, 2026 at 03:53 PM
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
(2, 'Primary operations', 'HDFC BANK', '14445600002', 'IDFC001401', 'CURRENT', 50000.00, 1),
(3, 'CASH', 'CASH', '455456', '56456', 'CASH', 0.00, 1),
(4, 'RBL LEVELX', 'RBL', '452136588', 'SBIN542684', 'CURRENT', 5000.00, 1),
(5, 'fedaral bank ', 'zzzzz', 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz', 'sssssssssssssssssssssssss', 'SAVINGS', 999999999999.99, 0),
(6, 'zyah bank ', 'hdfc bank ', '777777777777777777', '44444444444', 'SAVINGS', 10000000.00, 1);

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
(6, 'HOME DECOR', 'DECOR ITEMS', 1, '2026-09-04 12:31:42'),
(7, 'website building', 'website building xxxxxxxxxxxxxx', 1, '2026-09-05 11:46:52'),
(8, 'website building', 'website building', 1, '2026-09-05 11:48:22');

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
(1, 'LXIN-2026-09-1000', 30, '2026-09-06', '2026-09-21', 0.00, 1275.00, 0.00, 0.00, 1275.00, 10475.00, 0.00, 'PAID', '2026-09-06 10:30:13', 'Punjab', 1, 9200.00, 0.00, 0.00, 10475.00, 'Thank you for your business! All disputes subject to local jurisdiction.'),
(2, 'LXIN-2026-09-1001', 30, '2026-09-06', '2026-09-21', 0.00, 1240.00, 0.00, 0.00, 1240.00, 10000.00, 0.00, 'PAID', '2026-09-06 10:53:56', 'Punjab', 1, 9200.00, 0.00, -440.00, 10000.00, 'Thank you for your business! All disputes subject to local jurisdiction.'),
(3, 'LXIN-2026-09-1002', 30, '2026-09-06', '2026-09-21', 0.00, 180.00, 0.00, 0.00, 180.00, 1180.00, 835.00, 'PAID', '2026-09-06 10:58:37', 'Punjab', 1, 1000.00, 0.00, 0.00, 345.00, 'Thank you for your business! All disputes subject to local jurisdiction.'),
(4, 'LXIN-2026-09-1003', 30, '2026-09-06', '2026-09-21', 0.00, 1200.00, 0.00, 0.00, 1200.00, 11200.00, 2020.00, 'PAID', '2026-09-06 10:59:55', 'Punjab', 1, 10000.00, 0.00, 0.00, 9180.00, 'Thank you for your business! All disputes subject to local jurisdiction.');

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
(1, 1, NULL, 'keyboard', NULL, 1.00, 500.00, 12.00, 60.00, 560.00, 0.00, 'gaming keyboard', 'PCS', 0.00, 0.00, 500.00, 9.00, 0.00, 9.00, 0.00, 12.00, 60.00),
(2, 1, NULL, 'Monitor', NULL, 1.00, 6000.00, 18.00, 1080.00, 7080.00, 0.00, 'Sonic Monitor', 'PCS', 0.00, 0.00, 6000.00, 9.00, 0.00, 9.00, 0.00, 18.00, 1080.00),
(3, 1, NULL, 'washing', NULL, 1.00, 1500.00, 5.00, 75.00, 1575.00, 0.00, 'house washing', 'HR', 0.00, 0.00, 1500.00, 9.00, 0.00, 9.00, 0.00, 5.00, 75.00),
(4, 1, NULL, 'PC Repair', NULL, 1.00, 1200.00, 5.00, 60.00, 1260.00, 0.00, 'pc services', 'SRV', 0.00, 0.00, 1200.00, 9.00, 0.00, 9.00, 0.00, 5.00, 60.00),
(5, 2, NULL, 'Monitor', NULL, 1.00, 6000.00, 18.00, 1080.00, 7080.00, 0.00, 'Sonic Monitor', 'PCS', 0.00, 0.00, 6000.00, 9.00, 0.00, 9.00, 0.00, 18.00, 1080.00),
(6, 2, NULL, 'PC Repair', NULL, 1.00, 1200.00, 5.00, 60.00, 1260.00, 0.00, 'pc services', 'SRV', 0.00, 0.00, 1200.00, 9.00, 0.00, 9.00, 0.00, 5.00, 60.00),
(7, 2, NULL, 'Tp link router', NULL, 1.00, 2000.00, 5.00, 100.00, 2100.00, 0.00, '5 GHZ router', 'PCS', 0.00, 0.00, 2000.00, 9.00, 0.00, 9.00, 0.00, 5.00, 100.00),
(8, 3, NULL, 'mouse', NULL, 1.00, 1000.00, 18.00, 180.00, 1180.00, 0.00, 'gaming mouse', 'PCS', 0.00, 0.00, 1000.00, 9.00, 0.00, 9.00, 0.00, 18.00, 180.00),
(9, 4, NULL, 'website development', NULL, 1.00, 10000.00, 12.00, 1200.00, 11200.00, 0.00, 'website development', 'SRV', 0.00, 0.00, 10000.00, 9.00, 0.00, 9.00, 0.00, 12.00, 1200.00);

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
(1, 'mouse', 'gaming mouse', 1, 'PRODUCT', 'SS-001-TT', '778524', 'PCS', 1000.00, 100.00, -14.00, 18.00, 1),
(2, 'keyboard', 'gaming keyboard', 1, 'PRODUCT', 'SS-001-TD', '778525', 'PCS', 500.00, 100.00, -40.00, 12.00, 1),
(3, 'Tp link router', '5 GHZ router', 1, 'PRODUCT', 'SS-001-TZ', '778526', 'PCS', 2000.00, 100.00, 84.00, 5.00, 1),
(4, 'washing', 'house washing', 5, 'SERVICE', 'SS-001-TX', '778528', 'HR', 1500.00, 0.00, 0.00, 5.00, 1),
(5, 'PC Repair', 'pc services', 1, 'SERVICE', 'SS-001-TY', '77852678', 'SRV', 1200.00, 0.00, 0.00, 5.00, 1),
(6, 'Monitor', 'Sonic Monitor', 1, 'PRODUCT', 'SS-001-TTY', '7785252', 'PCS', 6000.00, 15.00, -1.00, 18.00, 1),
(7, 'website development', 'ecommerce website', 4, 'SERVICE', 'SS-001-TT', '555454', 'SRV', 10000.00, 0.00, 0.00, 18.00, 0),
(8, 'website development', 'website development', 4, 'SERVICE', 'SS-001-TT', '664646', 'SRV', 10000.00, 0.00, 0.00, 12.00, 1);

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
(27, 'CUSTOMER', NULL, 'prakash', 'Arohan Pvt Ltd', 'pra@mk', '+9444', 'Kochi', 'kochi', 'kochi', 'Kerala', '656', '2DD5SASSS53', 'SASAS522F', 1, '2026-09-02 19:43:35'),
(29, 'CUSTOMER', NULL, 'SAUD', 'apex pvt ltd', 'saud@saud', '+916545454', 'Banglore', 'Banglore', 'Banglore', 'Karnataka', '560541', '2DD5SASSS52', 'SASAS522E', 1, '2026-09-03 09:38:40'),
(30, 'CUSTOMER', NULL, 'Reshmi Murali', 'Reshmi Exports', 'reshmi@gmail.com', '9858478569', 'Mallu street', 'Reshmi Bhavan, Mallu street', 'Mangalaseri  Mallustreet', 'Punjab', '695121', '32ABAKL8745H6AM', 'ABAKL8745H', 1, '2026-09-03 15:40:38'),
(33, 'VENDOR', NULL, 'RAHUL', 'Acme Corp', 'RAHUL@JDF', '+99994', 'MEGHALAYA', 'MEGHALAYA', 'MEGHALAYA', 'Meghalaya', '646464', '2DD5SASSS57', 'SASAS522D', 1, '2026-09-04 23:16:57'),
(34, 'CUSTOMER', NULL, 'Jeslin santhosh ', 'ZAYH ', 'jjhh@hgmail.com', '7907051185', 'KAYAMKULAM ', '12, Green View LaneVyttila, Kochi Ernakulam, Kerala – 682019 India', '12, Green View LaneVyttila, Kochi Ernakulam, Kerala – 682019 India', 'Kerala', '682019 ', '32AAAAAAAAAAAAA', 'MKMASKLMAS', 1, '2026-09-05 11:14:51');

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

INSERT INTO `acc_payments` (`payment_id`, `payment_type`, `party_id`, `invoice_id`, `bank_id`, `amount`, `payment_method`, `reference_number`, `payment_date`, `notes`, `status`) VALUES
(1, 'PAY_IN', 30, NULL, 2, 50000.00, 'Bank Transfer', 'LXPI-2026-09-3000', '2026-09-06', '', 'COMPLETED'),
(2, 'PAY_IN', 30, NULL, 1, 835.00, 'Bank Account', 'DIRECT-PAY-LXIN-2026-09-1002', '2026-09-06', 'Direct payment on Invoice LXIN-2026-09-1002', 'COMPLETED'),
(3, 'PAY_IN', 30, NULL, 1, 2020.00, 'Bank Account', 'DIRECT-PAY-LXIN-2026-09-1003', '2026-09-06', 'Direct payment on Invoice LXIN-2026-09-1003', 'COMPLETED');

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
(1, 1, 1, NULL, NULL, 10475.00, '2026-09-06 10:30:13'),
(2, 1, 2, NULL, NULL, 10000.00, '2026-09-06 10:53:56'),
(3, 2, 3, NULL, NULL, 835.00, '2026-09-06 10:58:37'),
(4, 1, 3, NULL, NULL, 345.00, '2026-09-06 10:58:37'),
(5, 3, 4, NULL, NULL, 2020.00, '2026-09-06 10:59:55'),
(6, 1, 4, NULL, NULL, 9180.00, '2026-09-06 10:59:55');

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
('credit_note_next_number', '2000'),
('credit_note_prefix', 'LXCR-2026-09-'),
('default_currency', 'INR'),
('digital_signature', ''),
('financial_year', '2026-2027'),
('invoice_footer_notes', 'Thank you for your business! All disputes subject to local jurisdiction.'),
('invoice_next_number', '1004'),
('invoice_prefix', 'LXIN-2026-09-'),
('invoice_template', 'classic_gst'),
('payin_next_number', '3001'),
('payin_prefix', 'LXPI-2026-09-'),
('payout_next_number', '4000'),
('payout_prefix', 'LXPO-2026-09-'),
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
(8, 'GST 18%', 18.00, 9.00, 9.00, 18.00, 1);

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
  MODIFY `bank_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `acc_categories`
--
ALTER TABLE `acc_categories`
  MODIFY `category_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `acc_credit_notes`
--
ALTER TABLE `acc_credit_notes`
  MODIFY `credit_note_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `acc_credit_note_items`
--
ALTER TABLE `acc_credit_note_items`
  MODIFY `item_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `acc_currencies`
--
ALTER TABLE `acc_currencies`
  MODIFY `currency_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `acc_invoices`
--
ALTER TABLE `acc_invoices`
  MODIFY `invoice_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `acc_invoice_items`
--
ALTER TABLE `acc_invoice_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `acc_items`
--
ALTER TABLE `acc_items`
  MODIFY `item_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `acc_parties`
--
ALTER TABLE `acc_parties`
  MODIFY `party_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `acc_payments`
--
ALTER TABLE `acc_payments`
  MODIFY `payment_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `acc_payment_allocations`
--
ALTER TABLE `acc_payment_allocations`
  MODIFY `allocation_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `acc_settlements`
--
ALTER TABLE `acc_settlements`
  MODIFY `settlement_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `acc_taxes`
--
ALTER TABLE `acc_taxes`
  MODIFY `tax_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
