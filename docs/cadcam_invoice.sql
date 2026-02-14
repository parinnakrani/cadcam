-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 12, 2026 at 08:56 PM
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
-- Database: `cadcam_invoice`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = Global',
  `account_code` varchar(20) NOT NULL,
  `account_name` varchar(200) NOT NULL,
  `business_name` varchar(255) DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact_person_name` varchar(100) DEFAULT NULL,
  `mobile_number` varchar(10) NOT NULL,
  `email_address` varchar(100) DEFAULT NULL,
  `billing_address_line1` varchar(255) NOT NULL,
  `billing_address_line2` varchar(255) DEFAULT NULL,
  `billing_city` varchar(100) NOT NULL,
  `billing_state_id` int(10) UNSIGNED NOT NULL,
  `billing_pincode` varchar(6) NOT NULL,
  `shipping_address_line1` varchar(255) DEFAULT NULL,
  `shipping_address_line2` varchar(255) DEFAULT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `shipping_state_id` int(10) UNSIGNED DEFAULT NULL,
  `shipping_pincode` varchar(6) DEFAULT NULL,
  `same_as_billing` tinyint(1) DEFAULT 1,
  `gst_number` varchar(15) DEFAULT NULL,
  `pan_number` varchar(10) DEFAULT NULL,
  `opening_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `opening_balance_type` enum('Debit','Credit') DEFAULT 'Debit',
  `current_balance` decimal(15,2) DEFAULT 0.00,
  `opening_balance_date` date DEFAULT NULL,
  `credit_limit` decimal(15,2) DEFAULT NULL,
  `payment_terms` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `account_group` varchar(100) DEFAULT NULL,
  `wax_price_per_gram` decimal(10,2) DEFAULT NULL COMMENT 'Customer-specific wax price',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `company_id`, `account_code`, `account_name`, `business_name`, `contact_person`, `mobile`, `email`, `contact_person_name`, `mobile_number`, `email_address`, `billing_address_line1`, `billing_address_line2`, `billing_city`, `billing_state_id`, `billing_pincode`, `shipping_address_line1`, `shipping_address_line2`, `shipping_city`, `shipping_state_id`, `shipping_pincode`, `same_as_billing`, `gst_number`, `pan_number`, `opening_balance`, `opening_balance_type`, `current_balance`, `opening_balance_date`, `credit_limit`, `payment_terms`, `notes`, `account_group`, `wax_price_per_gram`, `is_active`, `created_at`, `updated_at`, `is_deleted`) VALUES
(3, 1, 'ACC-0001', 'Parin', 'Code Nine', '', '9586969009', '', NULL, '', NULL, 'Minibazaar', '', 'Surat', 1, '395006', '', '', '', NULL, '', 0, '', '', 0.00, 'Debit', 0.00, NULL, NULL, '', '', NULL, NULL, 1, '2026-02-11 19:19:38', '2026-02-11 19:19:38', 0);

-- --------------------------------------------------------

--
-- Table structure for table `account_groups`
--

CREATE TABLE `account_groups` (
  `id` int(11) UNSIGNED NOT NULL,
  `company_id` int(11) UNSIGNED NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `parent_id` int(11) UNSIGNED DEFAULT NULL,
  `type` enum('Asset','Liability','Income','Expense') NOT NULL DEFAULT 'Asset',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `module` varchar(50) NOT NULL,
  `action_type` enum('create','update','delete','view','print','export','login','logout') NOT NULL,
  `record_type` varchar(50) DEFAULT NULL,
  `record_id` int(10) UNSIGNED DEFAULT NULL,
  `before_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`before_data`)),
  `after_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`after_data`)),
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cash_customers`
--

CREATE TABLE `cash_customers` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = Global',
  `customer_name` varchar(200) NOT NULL,
  `mobile_number` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `is_deleted` tinyint(1) DEFAULT 0,
  `mobile` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address_line1` varchar(255) DEFAULT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state_id` int(11) UNSIGNED DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `challans`
--

CREATE TABLE `challans` (
  `id` int(11) UNSIGNED NOT NULL,
  `company_id` int(11) UNSIGNED NOT NULL,
  `challan_number` varchar(50) NOT NULL,
  `challan_date` date NOT NULL,
  `challan_type` enum('Rhodium','Meena','Wax') NOT NULL,
  `customer_type` enum('Account','Cash') NOT NULL,
  `account_id` int(11) UNSIGNED DEFAULT NULL,
  `cash_customer_id` int(11) UNSIGNED DEFAULT NULL,
  `challan_status` enum('Draft','Pending','In Progress','Completed','Invoiced') NOT NULL DEFAULT 'Draft',
  `total_weight` decimal(10,3) NOT NULL DEFAULT 0.000,
  `subtotal_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_percent` decimal(5,2) DEFAULT NULL,
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `invoice_generated` tinyint(1) NOT NULL DEFAULT 0,
  `invoice_id` int(11) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `created_by` int(11) UNSIGNED NOT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;

--
-- Dumping data for table `challans`
--

INSERT INTO `challans` (`id`, `company_id`, `challan_number`, `challan_date`, `challan_type`, `customer_type`, `account_id`, `cash_customer_id`, `challan_status`, `total_weight`, `subtotal_amount`, `tax_percent`, `tax_amount`, `total_amount`, `invoice_generated`, `invoice_id`, `notes`, `delivery_date`, `created_by`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 1, 'CH-0003', '2026-02-12', 'Rhodium', 'Account', 3, NULL, 'Draft', 0.000, 66.00, NULL, 11.88, 77.88, 0, NULL, 'Notes here', '0000-00-00', 2, 0, '2026-02-11 19:30:20', '2026-02-11 20:32:26'),
(2, 1, 'CH-0004', '2026-02-12', 'Rhodium', 'Account', 3, NULL, 'Draft', 0.000, 252.50, 18.00, 45.45, 297.95, 0, NULL, '', '0000-00-00', 2, 0, '2026-02-11 20:44:56', '2026-02-11 20:44:56'),
(3, 1, 'CH-0005', '2026-02-12', 'Rhodium', 'Account', 3, NULL, 'Draft', 0.000, 85.00, 18.00, 15.30, 100.30, 0, NULL, '', '0000-00-00', 2, 0, '2026-02-11 20:48:10', '2026-02-11 20:48:10');

-- --------------------------------------------------------

--
-- Table structure for table `challan_lines`
--

CREATE TABLE `challan_lines` (
  `id` int(10) UNSIGNED NOT NULL,
  `challan_id` int(10) UNSIGNED NOT NULL,
  `line_number` int(11) NOT NULL,
  `product_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of product IDs' CHECK (json_valid(`product_ids`)),
  `product_name` varchar(255) DEFAULT NULL COMMENT 'For Wax challan file upload',
  `process_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of process IDs' CHECK (json_valid(`process_ids`)),
  `process_prices` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Snapshot of process prices at creation' CHECK (json_valid(`process_prices`)),
  `quantity` int(11) NOT NULL DEFAULT 1,
  `weight` decimal(10,3) NOT NULL DEFAULT 0.000,
  `rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `image_path` varchar(255) DEFAULT NULL,
  `gold_weight` decimal(10,3) DEFAULT NULL,
  `gold_fine_weight` decimal(10,3) DEFAULT NULL,
  `gold_purity` varchar(20) DEFAULT NULL,
  `line_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `challan_lines`
--

INSERT INTO `challan_lines` (`id`, `challan_id`, `line_number`, `product_ids`, `product_name`, `process_ids`, `process_prices`, `quantity`, `weight`, `rate`, `amount`, `image_path`, `gold_weight`, `gold_fine_weight`, `gold_purity`, `line_notes`, `created_at`, `updated_at`) VALUES
(5, 1, 1, '[\"1\"]', 'Ring', '[\"2\"]', NULL, 1, 2.000, 25.00, 50.00, NULL, 0.000, NULL, '', NULL, '2026-02-11 20:32:26', '2026-02-11 20:32:26'),
(6, 1, 2, '[\"3\",\"2\"]', 'Earring, Pendant', '[\"1\",\"3\"]', NULL, 1, 0.200, 80.00, 16.00, NULL, 0.000, NULL, '', NULL, '2026-02-11 20:32:26', '2026-02-11 20:32:26'),
(7, 2, 1, '[\"2\"]', 'Pendant', '[\"3\"]', '[]', 1, 2.000, 20.00, 40.00, NULL, NULL, NULL, NULL, NULL, '2026-02-11 20:44:56', '2026-02-11 20:44:56'),
(8, 2, 2, '[\"1\",\"4\"]', 'Ring, Necklace', '[\"1\",\"2\"]', '[]', 1, 2.500, 85.00, 212.50, NULL, NULL, NULL, NULL, NULL, '2026-02-11 20:44:56', '2026-02-11 20:44:56'),
(9, 3, 1, '[\"2\",\"3\"]', 'Pendant, Earring', '[\"1\",\"2\"]', '[]', 1, 1.000, 85.00, 85.00, NULL, NULL, NULL, NULL, NULL, '2026-02-11 20:48:10', '2026-02-11 20:48:10');

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_name` varchar(200) NOT NULL,
  `business_legal_name` varchar(200) NOT NULL,
  `business_type` enum('Gold Manufacturing','Rhodium Processing','Meena Processing','Wax Manufacturing') NOT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state_id` int(10) UNSIGNED NOT NULL,
  `pincode` varchar(6) NOT NULL,
  `contact_person_name` varchar(100) NOT NULL,
  `contact_email` varchar(100) NOT NULL,
  `contact_phone` varchar(10) NOT NULL,
  `gst_number` varchar(15) DEFAULT NULL,
  `pan_number` varchar(10) DEFAULT NULL,
  `company_logo` varchar(255) DEFAULT NULL,
  `invoice_prefix` varchar(20) NOT NULL DEFAULT 'INV-',
  `challan_prefix` varchar(20) NOT NULL DEFAULT 'CH-',
  `default_tax_rate` decimal(5,2) NOT NULL DEFAULT 3.00,
  `minimum_wax_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `financial_year_start_month` tinyint(4) NOT NULL DEFAULT 4,
  `date_format` varchar(20) NOT NULL DEFAULT 'Y-m-d',
  `timezone` varchar(50) NOT NULL DEFAULT 'Asia/Kolkata',
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `last_invoice_number` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `last_challan_number` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `company_name`, `business_legal_name`, `business_type`, `address_line1`, `address_line2`, `city`, `state_id`, `pincode`, `contact_person_name`, `contact_email`, `contact_phone`, `gst_number`, `pan_number`, `company_logo`, `invoice_prefix`, `challan_prefix`, `default_tax_rate`, `minimum_wax_price`, `financial_year_start_month`, `date_format`, `timezone`, `status`, `last_invoice_number`, `last_challan_number`, `created_at`, `updated_at`, `is_deleted`) VALUES
(1, 'System Administrator', 'System Administrator', 'Gold Manufacturing', 'System HQ', NULL, 'System City', 1, '000000', 'System Admin', 'admin@gmail.com', '9999999999', NULL, NULL, NULL, 'SYS-', 'CH-', 18.00, 0.00, 4, 'Y-m-d', 'Asia/Kolkata', 'Active', 0, 5, '2026-02-08 16:31:03', '2026-02-11 20:48:10', 0);

-- --------------------------------------------------------

--
-- Table structure for table `company_settings`
--

CREATE TABLE `company_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','json') NOT NULL DEFAULT 'text',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

CREATE TABLE `deliveries` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `invoice_id` int(10) UNSIGNED NOT NULL,
  `assigned_to` int(10) UNSIGNED NOT NULL,
  `assigned_by` int(10) UNSIGNED NOT NULL,
  `assigned_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `expected_delivery_date` date NOT NULL,
  `actual_delivery_date` date DEFAULT NULL,
  `delivery_status` enum('Assigned','In Transit','Delivered','Failed') NOT NULL DEFAULT 'Assigned',
  `delivery_address` text NOT NULL,
  `customer_contact_mobile` varchar(10) NOT NULL,
  `delivery_notes` text DEFAULT NULL,
  `delivery_proof_photo` varchar(255) DEFAULT NULL,
  `delivered_timestamp` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gold_rates`
--

CREATE TABLE `gold_rates` (
  `id` int(11) UNSIGNED NOT NULL,
  `company_id` int(11) UNSIGNED NOT NULL,
  `rate_date` date NOT NULL,
  `metal_type` enum('22K') NOT NULL,
  `rate_per_gram` decimal(10,2) NOT NULL,
  `created_by` int(11) UNSIGNED DEFAULT NULL,
  `updated_by` int(11) UNSIGNED DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gold_rates`
--

INSERT INTO `gold_rates` (`id`, `company_id`, `rate_date`, `metal_type`, `rate_per_gram`, `created_by`, `updated_by`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-02-11', '22K', 15000.00, 2, NULL, 0, '2026-02-11 10:54:41', '2026-02-11 10:54:41');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `invoice_type` enum('Accounts Invoice','Cash Invoice','Wax Invoice') NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `account_id` int(10) UNSIGNED DEFAULT NULL,
  `cash_customer_id` int(10) UNSIGNED DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `challan_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of linked challan IDs' CHECK (json_valid(`challan_ids`)),
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 3.00,
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `cgst_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `sgst_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `igst_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_paid` decimal(15,2) NOT NULL DEFAULT 0.00,
  `amount_due` decimal(15,2) NOT NULL DEFAULT 0.00,
  `invoice_status` enum('Draft','Posted','Partially Paid','Paid','Delivered','Closed') NOT NULL DEFAULT 'Draft',
  `payment_status` enum('Pending','Partial Paid','Paid') NOT NULL DEFAULT 'Pending',
  `gold_adjustment_applied` tinyint(1) NOT NULL DEFAULT 0,
  `gold_adjustment_date` timestamp NULL DEFAULT NULL,
  `gold_adjustment_amount` decimal(15,2) DEFAULT NULL,
  `gold_rate_used` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `terms_conditions` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_lines`
--

CREATE TABLE `invoice_lines` (
  `id` int(10) UNSIGNED NOT NULL,
  `invoice_id` int(10) UNSIGNED NOT NULL,
  `line_number` int(11) NOT NULL,
  `source_challan_id` int(10) UNSIGNED DEFAULT NULL,
  `source_challan_line_id` int(10) UNSIGNED DEFAULT NULL,
  `product_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of product IDs' CHECK (json_valid(`product_ids`)),
  `product_name` varchar(255) DEFAULT NULL,
  `process_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of process IDs' CHECK (json_valid(`process_ids`)),
  `process_prices` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Snapshot of process prices' CHECK (json_valid(`process_prices`)),
  `quantity` int(11) NOT NULL DEFAULT 1,
  `weight` decimal(10,3) NOT NULL DEFAULT 0.000,
  `rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `gold_weight` decimal(10,3) DEFAULT NULL,
  `gold_fine_weight` decimal(10,3) DEFAULT NULL,
  `gold_purity` varchar(20) DEFAULT NULL,
  `original_gold_weight` decimal(10,3) DEFAULT NULL COMMENT 'Before adjustment',
  `adjusted_gold_weight` decimal(10,3) DEFAULT NULL COMMENT 'After adjustment',
  `gold_adjustment_amount` decimal(15,2) DEFAULT NULL COMMENT 'Per line adjustment',
  `line_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ledger_entries`
--

CREATE TABLE `ledger_entries` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `account_id` int(10) UNSIGNED DEFAULT NULL,
  `cash_customer_id` int(10) UNSIGNED DEFAULT NULL,
  `entry_date` date NOT NULL,
  `reference_type` enum('opening_balance','invoice','payment','gold_adjustment') NOT NULL,
  `reference_id` int(10) UNSIGNED DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `debit_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `credit_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `balance_after` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(2, '20260208221500', 'App\\Database\\Migrations\\AddRememberTokenToUsers', 'default', 'App', 1770572554, 1),
(14, '2026-01-01-000006', 'App\\Database\\Migrations\\CreateGoldRatesTable', 'default', 'App', 1770751266, 2),
(15, '2026-01-01-000007', 'App\\Database\\Migrations\\CreateProductCategoriesTable', 'default', 'App', 1770751266, 2),
(16, '2026-01-01-000008', 'App\\Database\\Migrations\\CreateProductsTable', 'default', 'App', 1770751266, 2),
(17, '2026-01-01-000009', 'App\\Database\\Migrations\\CreateProcessesTable', 'default', 'App', 1770751266, 2),
(18, '20260211000000', 'App\\Database\\Migrations\\CreateStatesTable', 'default', 'App', 1770751266, 2),
(19, '20260211003812', 'App\\Database\\Migrations\\CreateAccountsTables', 'default', 'App', 1770751266, 2),
(20, '20260211005500', 'App\\Database\\Migrations\\CreateCashCustomersTable', 'default', 'App', 1770751476, 3),
(21, '20260211013500', 'App\\Database\\Migrations\\FixAccountsTable', 'default', 'App', 1770753480, 4),
(22, '20260211014000', 'App\\Database\\Migrations\\FixAccountsTableRest', 'default', 'App', 1770753609, 5),
(23, '20260211014500', 'App\\Database\\Migrations\\FixCashCustomersTable', 'default', 'App', 1770754553, 6),
(24, '20260211015300', 'App\\Database\\Migrations\\FixCashCustomersTableAddress', 'default', 'App', 1770755018, 7),
(25, '20260211015600', 'App\\Database\\Migrations\\FixCashCustomersTableAddressLine2', 'default', 'App', 1770755036, 8),
(26, '2026-01-01-000012', 'App\\Database\\Migrations\\CreateChallansTable', 'default', 'App', 1770833690, 9),
(27, '2026-02-11-203826', 'App\\Database\\Migrations\\AddTaxPercentToChallans', 'default', 'App', 1770842330, 10);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `invoice_id` int(10) UNSIGNED NOT NULL,
  `payment_date` date NOT NULL,
  `payment_amount` decimal(15,2) NOT NULL,
  `payment_mode` enum('Cash','Cheque','Bank Transfer','UPI','Card','Other') NOT NULL,
  `cheque_number` varchar(50) DEFAULT NULL,
  `cheque_date` date DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `transaction_reference` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `received_by` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `processes`
--

CREATE TABLE `processes` (
  `id` int(11) UNSIGNED NOT NULL,
  `company_id` int(11) UNSIGNED NOT NULL,
  `process_code` varchar(50) NOT NULL,
  `process_name` varchar(255) NOT NULL,
  `process_type` enum('Rhodium','Meena','Wax','Polish','Coating','Other') NOT NULL DEFAULT 'Other',
  `description` text DEFAULT NULL,
  `rate_per_unit` decimal(10,2) NOT NULL,
  `unit_of_measure` varchar(20) NOT NULL DEFAULT 'PCS',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `processes`
--

INSERT INTO `processes` (`id`, `company_id`, `process_code`, `process_name`, `process_type`, `description`, `rate_per_unit`, `unit_of_measure`, `is_active`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 1, 'RB', 'Rhodium Black', 'Rhodium', '', 60.00, 'GRAM', 1, 0, '2026-02-11 19:01:53', '2026-02-11 19:01:53'),
(2, 1, 'RP', 'Rhodium Pink', 'Rhodium', '', 25.00, 'PCS', 1, 0, '2026-02-11 19:02:08', '2026-02-11 19:02:08'),
(3, 1, 'RW', 'Rhodium White', 'Rhodium', '', 20.00, 'PCS', 1, 0, '2026-02-11 19:02:36', '2026-02-11 19:02:36');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) UNSIGNED NOT NULL,
  `company_id` int(11) UNSIGNED NOT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `hsn_code` varchar(20) DEFAULT NULL,
  `unit_of_measure` varchar(20) NOT NULL DEFAULT 'PCS',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `company_id`, `category_id`, `product_code`, `product_name`, `description`, `image_path`, `hsn_code`, `unit_of_measure`, `is_active`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'RING001', 'Ring', '', NULL, '', 'PCS', 1, 0, '2026-02-11 19:00:26', '2026-02-11 19:00:26'),
(2, 1, 1, 'PEN001', 'Pendant', '', NULL, '', 'PCS', 1, 0, '2026-02-11 19:00:38', '2026-02-11 19:00:38'),
(3, 1, 1, 'EAR001', 'Earring', '', 'uploads/products/1770901221_d2e1aff5be6425ec83e8.jpg', '', 'PCS', 1, 0, '2026-02-11 19:01:15', '2026-02-12 13:00:21'),
(4, 1, 1, 'NECK001', 'Necklace', '', NULL, '', 'PCS', 1, 0, '2026-02-11 19:01:28', '2026-02-11 19:01:28');

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `id` int(11) UNSIGNED NOT NULL,
  `company_id` int(11) UNSIGNED NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `category_code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`id`, `company_id`, `category_name`, `category_code`, `description`, `display_order`, `is_active`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 1, 'Jewellery', 'C001', '', 0, 1, 0, '2026-02-11 19:00:05', '2026-02-11 19:00:05');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = System role',
  `role_name` varchar(100) NOT NULL,
  `role_description` text DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Array of permission codes' CHECK (json_valid(`permissions`)),
  `is_system_role` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `company_id`, `role_name`, `role_description`, `permissions`, `is_system_role`, `is_active`, `created_at`, `updated_at`, `is_deleted`) VALUES
(1, 0, 'Super Administrator', 'Full system access with global privileges', '[\"*\"]', 1, 1, '2026-02-08 16:27:09', '2026-02-08 16:27:09', 0),
(2, 0, 'Company Administrator', 'Complete control over company specific data and settings', '[\"company.manage\",\"users.manage\",\"roles.manage\",\"challans.*\",\"invoices.*\",\"payments.*\",\"reports.*\",\"masters.*\",\"deliveries.*\",\"settings.manage\"]', 1, 1, '2026-02-08 16:27:09', '2026-02-08 16:27:09', 0),
(3, 0, 'Billing Manager', 'Manages invoicing, challans, and basic reporting', '[\"challans.*\",\"invoices.*\",\"reports.ledger\",\"reports.outstanding\",\"customers.view\",\"masters.view\"]', 1, 1, '2026-02-08 16:27:09', '2026-02-08 16:27:09', 0),
(4, 0, 'Accounts Manager', 'Focus on payments, accounting reports, and customer finances', '[\"payments.*\",\"reports.*\",\"invoices.view\",\"challans.view\",\"customers.*\"]', 1, 1, '2026-02-08 16:27:09', '2026-02-08 16:27:09', 0),
(5, 0, 'Delivery Personnel', 'Access to assigned deliveries and invoice viewing', '[\"deliveries.view_assigned\",\"deliveries.mark_complete\",\"invoices.view_assigned\"]', 1, 1, '2026-02-08 16:27:09', '2026-02-08 16:27:09', 0),
(6, 0, 'Report Viewer', 'Read-only access to reports and core data', '[\"reports.view_all\",\"invoices.view\",\"challans.view\",\"customers.view\"]', 1, 1, '2026-02-08 16:27:09', '2026-02-08 16:27:09', 0);

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE `states` (
  `id` int(10) UNSIGNED NOT NULL,
  `state_name` varchar(100) NOT NULL,
  `state_code` varchar(10) NOT NULL,
  `country` varchar(50) NOT NULL DEFAULT 'India',
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `states`
--

INSERT INTO `states` (`id`, `state_name`, `state_code`, `country`, `is_active`) VALUES
(1, 'GUJARAT', 'GJ', 'India', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `mobile_number` varchar(10) DEFAULT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `remember_expires_at` datetime DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `adhar_card_number` varchar(12) DEFAULT NULL,
  `date_of_joining` date DEFAULT NULL,
  `employment_status` enum('Active','Inactive','Suspended') NOT NULL DEFAULT 'Active',
  `failed_login_attempts` tinyint(4) NOT NULL DEFAULT 0,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `company_id`, `username`, `email`, `password_hash`, `full_name`, `mobile_number`, `remember_token`, `remember_expires_at`, `profile_photo`, `adhar_card_number`, `date_of_joining`, `employment_status`, `failed_login_attempts`, `last_login_at`, `last_login_ip`, `created_at`, `updated_at`, `is_deleted`) VALUES
(2, 1, 'superadmin', 'admin@gmail.com', '$2y$10$3lD2hiugSlildxvjTeH9bue.5rQEqtidB6krrpytoJk4hpXfKZ/WC', 'System Administrator', '9999999999', '24b19a219c1dc6d90d989a06c15b4c37', '2026-03-10 23:24:12', NULL, NULL, NULL, 'Active', 0, '2026-02-12 12:53:58', NULL, '2026-02-08 16:31:03', '2026-02-12 12:53:58', 0),
(4, 1, 'parinpatel', 'parinwork@gmail.com', '$2y$10$TBdnobbaIGwuvdZuPhT5yu7U1hVKWCcVbpSpmwMNhINEdFYkUsiNa', 'Parin Patel', '9586969009', NULL, NULL, NULL, NULL, NULL, 'Active', 0, '2026-02-10 20:12:17', NULL, '2026-02-10 20:11:17', '2026-02-10 20:12:17', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_by` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`id`, `user_id`, `role_id`, `assigned_at`, `assigned_by`) VALUES
(1, 2, 1, '2026-02-08 16:31:03', 2),
(5, 4, 3, '2026-02-10 20:11:17', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_company_account_code` (`company_id`,`account_code`),
  ADD KEY `idx_company_id` (`company_id`),
  ADD KEY `idx_account_name` (`account_name`),
  ADD KEY `idx_mobile` (`mobile_number`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_deleted` (`is_deleted`),
  ADD KEY `fk_accounts_billing_state` (`billing_state_id`),
  ADD KEY `fk_accounts_shipping_state` (`shipping_state_id`);

--
-- Indexes for table `account_groups`
--
ALTER TABLE `account_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_groups_company_id_foreign` (`company_id`),
  ADD KEY `account_groups_parent_id_foreign` (`parent_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_company_id` (`company_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_module` (`module`),
  ADD KEY `idx_action_type` (`action_type`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `cash_customers`
--
ALTER TABLE `cash_customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_company_name_mobile` (`company_id`,`customer_name`,`mobile_number`),
  ADD KEY `idx_company_id` (`company_id`),
  ADD KEY `idx_mobile` (`mobile_number`),
  ADD KEY `idx_name` (`customer_name`);

--
-- Indexes for table `challans`
--
ALTER TABLE `challans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_company_challan_number` (`company_id`,`challan_number`),
  ADD KEY `fk_challans_created_by` (`created_by`),
  ADD KEY `idx_challans_company_id` (`company_id`),
  ADD KEY `idx_challans_account_id` (`account_id`),
  ADD KEY `idx_challans_cash_customer_id` (`cash_customer_id`),
  ADD KEY `idx_challans_status` (`challan_status`),
  ADD KEY `idx_challans_type` (`challan_type`),
  ADD KEY `idx_challans_date` (`challan_date`),
  ADD KEY `idx_challans_invoice_id` (`invoice_id`);

--
-- Indexes for table `challan_lines`
--
ALTER TABLE `challan_lines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_challan_id` (`challan_id`),
  ADD KEY `idx_line_number` (`line_number`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_company_name` (`company_name`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_deleted` (`is_deleted`);

--
-- Indexes for table `company_settings`
--
ALTER TABLE `company_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_company_setting` (`company_id`,`setting_key`),
  ADD KEY `idx_company_id` (`company_id`);

--
-- Indexes for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_company_id` (`company_id`),
  ADD KEY `idx_invoice_id` (`invoice_id`),
  ADD KEY `idx_assigned_to` (`assigned_to`),
  ADD KEY `idx_delivery_status` (`delivery_status`),
  ADD KEY `idx_expected_date` (`expected_delivery_date`),
  ADD KEY `idx_deleted` (`is_deleted`),
  ADD KEY `fk_deliveries_assigned_by` (`assigned_by`);

--
-- Indexes for table `gold_rates`
--
ALTER TABLE `gold_rates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `company_id_rate_date_metal_type` (`company_id`,`rate_date`,`metal_type`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `rate_date` (`rate_date`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_company_invoice_number` (`company_id`,`invoice_number`),
  ADD KEY `idx_company_id` (`company_id`),
  ADD KEY `idx_invoice_date` (`invoice_date`),
  ADD KEY `idx_account_id` (`account_id`),
  ADD KEY `idx_cash_customer_id` (`cash_customer_id`),
  ADD KEY `idx_invoice_status` (`invoice_status`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_amount_due` (`amount_due`),
  ADD KEY `idx_deleted` (`is_deleted`),
  ADD KEY `fk_invoices_created_by` (`created_by`),
  ADD KEY `idx_invoices_company_status` (`company_id`,`invoice_status`),
  ADD KEY `idx_invoices_company_payment` (`company_id`,`payment_status`);

--
-- Indexes for table `invoice_lines`
--
ALTER TABLE `invoice_lines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice_id` (`invoice_id`),
  ADD KEY `idx_line_number` (`line_number`),
  ADD KEY `idx_source_challan` (`source_challan_id`);

--
-- Indexes for table `ledger_entries`
--
ALTER TABLE `ledger_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_company_id` (`company_id`),
  ADD KEY `idx_account_id` (`account_id`),
  ADD KEY `idx_cash_customer_id` (`cash_customer_id`),
  ADD KEY `idx_entry_date` (`entry_date`),
  ADD KEY `idx_reference_type` (`reference_type`),
  ADD KEY `idx_ledger_company_account_date` (`company_id`,`account_id`,`entry_date`),
  ADD KEY `idx_ledger_company_cash_date` (`company_id`,`cash_customer_id`,`entry_date`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_company_id` (`company_id`),
  ADD KEY `idx_invoice_id` (`invoice_id`),
  ADD KEY `idx_payment_date` (`payment_date`),
  ADD KEY `idx_payment_mode` (`payment_mode`),
  ADD KEY `idx_deleted` (`is_deleted`),
  ADD KEY `fk_payments_received_by` (`received_by`),
  ADD KEY `idx_payments_company_date` (`company_id`,`payment_date`);

--
-- Indexes for table `processes`
--
ALTER TABLE `processes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `company_id_process_code` (`company_id`,`process_code`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `process_name` (`process_name`),
  ADD KEY `process_type` (`process_type`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `company_id_product_code` (`company_id`,`product_code`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `product_name` (`product_name`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `company_id_category_code` (`company_id`,`category_code`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `category_name` (`category_name`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_company_id` (`company_id`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_deleted` (`is_deleted`);

--
-- Indexes for table `states`
--
ALTER TABLE `states`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_state_code` (`state_code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_username` (`username`),
  ADD UNIQUE KEY `uk_email` (`email`),
  ADD KEY `idx_company_id` (`company_id`),
  ADD KEY `idx_employment_status` (`employment_status`),
  ADD KEY `idx_deleted` (`is_deleted`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_user_role` (`user_id`,`role_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `account_groups`
--
ALTER TABLE `account_groups`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cash_customers`
--
ALTER TABLE `cash_customers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `challans`
--
ALTER TABLE `challans`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `challan_lines`
--
ALTER TABLE `challan_lines`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `company_settings`
--
ALTER TABLE `company_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gold_rates`
--
ALTER TABLE `gold_rates`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_lines`
--
ALTER TABLE `invoice_lines`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ledger_entries`
--
ALTER TABLE `ledger_entries`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `processes`
--
ALTER TABLE `processes`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `states`
--
ALTER TABLE `states`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `fk_accounts_billing_state` FOREIGN KEY (`billing_state_id`) REFERENCES `states` (`id`),
  ADD CONSTRAINT `fk_accounts_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `fk_accounts_shipping_state` FOREIGN KEY (`shipping_state_id`) REFERENCES `states` (`id`);

--
-- Constraints for table `account_groups`
--
ALTER TABLE `account_groups`
  ADD CONSTRAINT `account_groups_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_groups_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `account_groups` (`id`) ON DELETE CASCADE ON UPDATE SET NULL;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `challans`
--
ALTER TABLE `challans`
  ADD CONSTRAINT `fk_challans_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`),
  ADD CONSTRAINT `fk_challans_cash_customer` FOREIGN KEY (`cash_customer_id`) REFERENCES `cash_customers` (`id`),
  ADD CONSTRAINT `fk_challans_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_challans_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `challan_lines`
--
ALTER TABLE `challan_lines`
  ADD CONSTRAINT `fk_challan_lines_challan` FOREIGN KEY (`challan_id`) REFERENCES `challans` (`id`);

--
-- Constraints for table `company_settings`
--
ALTER TABLE `company_settings`
  ADD CONSTRAINT `fk_settings_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`);

--
-- Constraints for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `fk_deliveries_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_deliveries_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_deliveries_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `fk_deliveries_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`);

--
-- Constraints for table `gold_rates`
--
ALTER TABLE `gold_rates`
  ADD CONSTRAINT `gold_rates_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `fk_invoices_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`),
  ADD CONSTRAINT `fk_invoices_cash_customer` FOREIGN KEY (`cash_customer_id`) REFERENCES `cash_customers` (`id`),
  ADD CONSTRAINT `fk_invoices_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `fk_invoices_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `invoice_lines`
--
ALTER TABLE `invoice_lines`
  ADD CONSTRAINT `fk_invoice_lines_challan` FOREIGN KEY (`source_challan_id`) REFERENCES `challans` (`id`),
  ADD CONSTRAINT `fk_invoice_lines_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`);

--
-- Constraints for table `ledger_entries`
--
ALTER TABLE `ledger_entries`
  ADD CONSTRAINT `fk_ledger_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`),
  ADD CONSTRAINT `fk_ledger_cash_customer` FOREIGN KEY (`cash_customer_id`) REFERENCES `cash_customers` (`id`),
  ADD CONSTRAINT `fk_ledger_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `fk_payments_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`),
  ADD CONSTRAINT `fk_payments_received_by` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `processes`
--
ALTER TABLE `processes`
  ADD CONSTRAINT `processes_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD CONSTRAINT `product_categories_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`);

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `fk_user_roles_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `fk_user_roles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
