-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 28, 2026 at 08:33 AM
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
(3, 1, 'ACC-0001', 'Parin', 'Code Nine', '', '9586969009', '', NULL, '', NULL, 'Minibazaar', '', 'Surat', 1, '395006', '', '', '', NULL, '', 0, '', '', 0.00, 'Debit', 21769.67, NULL, NULL, '', '', NULL, NULL, 1, '2026-02-11 19:19:38', '2026-02-26 16:42:51', 0),
(4, 1, 'ACC-0002', 'Alpesh Pansheriya', '', '', '8000259032', 'alpesh@gmail.com', NULL, '', NULL, 'Jakatnaka', '', 'Surat', 1, '395006', 'Jakatnaka', '', 'Surat', 1, '395006', 1, '22AAAAA0000A1Z5', 'ABCDE1234F', 160.00, 'Credit', 1714.40, NULL, NULL, 'No payment terms', 'No notes', NULL, NULL, 1, '2026-02-20 17:07:43', '2026-02-26 16:51:29', 0);

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
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `module` varchar(100) DEFAULT NULL,
  `action_type` enum('create','update','delete','view','login','logout','print','export','switch_company','access_denied') DEFAULT NULL,
  `record_type` varchar(100) DEFAULT NULL,
  `record_id` int(10) UNSIGNED DEFAULT NULL,
  `before_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`before_data`)),
  `after_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`after_data`)),
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `module`, `action_type`, `record_type`, `record_id`, `before_data`, `after_data`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, NULL, NULL, 'Auth', 'login', 'User', 8, NULL, '{\"ip\":\"::1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-28 06:13:29'),
(2, NULL, NULL, 'Challan', '', 'Challan', 1, NULL, '{\"company_id\":\"1\",\"user_id\":\"8\",\"challan_number\":\"CH-0021\",\"challan_type\":\"Rhodium\",\"customer_type\":\"Account\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-28 06:25:53'),
(3, NULL, NULL, 'Invoice', 'create', 'Invoice', 1, NULL, '{\"invoice_type\":\"Cash Invoice\",\"invoice_date\":\"2026-02-28\",\"due_date\":\"\",\"account_id\":null,\"cash_customer_id\":\"1\",\"billing_address\":null,\"shipping_address\":null,\"reference_number\":null,\"tax_rate\":\"18\",\"notes\":null,\"terms_conditions\":\"\",\"company_id\":\"1\",\"created_by\":\"8\",\"updated_by\":\"8\",\"invoice_number\":\"SYS-0021\",\"subtotal\":169.49,\"tax_amount\":30.51,\"cgst_amount\":0,\"sgst_amount\":0,\"igst_amount\":30.51,\"grand_total\":200,\"payment_terms\":null,\"challan_ids\":null,\"total_paid\":0,\"amount_due\":200,\"payment_status\":\"Pending\",\"invoice_status\":\"Draft\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-28 06:44:26'),
(4, NULL, NULL, 'Invoice', 'create', 'Invoice', 3, NULL, '{\"invoice_type\":\"Cash Invoice\",\"invoice_date\":\"2026-02-28\",\"due_date\":\"\",\"account_id\":null,\"cash_customer_id\":\"2\",\"billing_address\":null,\"shipping_address\":null,\"reference_number\":null,\"tax_rate\":\"18\",\"notes\":null,\"terms_conditions\":\"\",\"company_id\":\"1\",\"created_by\":\"8\",\"updated_by\":\"8\",\"invoice_number\":\"SYS-0022\",\"subtotal\":406.78,\"tax_amount\":73.22,\"cgst_amount\":0,\"sgst_amount\":0,\"igst_amount\":73.22,\"grand_total\":480,\"payment_terms\":null,\"challan_ids\":null,\"total_paid\":0,\"amount_due\":480,\"payment_status\":\"Pending\",\"invoice_status\":\"Draft\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-28 06:56:22'),
(5, NULL, NULL, 'Invoice', 'update', 'Invoice', 3, NULL, '{\"invoice_date\":\"2026-02-28\",\"due_date\":\"\",\"billing_address\":\"\",\"shipping_address\":\"\",\"reference_number\":\"\",\"notes\":\"\",\"terms_conditions\":\"\",\"updated_by\":\"8\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-28 06:57:43');

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
  `notes` text DEFAULT NULL,
  `current_balance` decimal(15,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cash_customers`
--

INSERT INTO `cash_customers` (`id`, `company_id`, `customer_name`, `mobile_number`, `created_at`, `updated_at`, `is_active`, `is_deleted`, `mobile`, `email`, `address_line1`, `address_line2`, `city`, `state_id`, `pincode`, `notes`, `current_balance`) VALUES
(1, 1, 'Parin Patel', '9586969009', '2026-02-21 08:22:38', '2026-02-28 06:44:26', 1, 0, '9586969009', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 450.00),
(2, 1, 'Alpesh', '8000259032', '2026-02-23 18:11:37', '2026-02-28 06:57:43', 1, 0, '8000259032', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 7580.00),
(3, 1, 'Parin Nakranni', '8888887777', '2026-02-25 19:26:41', '2026-02-26 17:04:40', 1, 0, '8888887777', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(4, 1, 'Parin Nakrani', '4444444444', '2026-02-27 09:15:57', '2026-02-27 09:15:57', 1, 0, '4444444444', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 25.00);

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
(1, 1, 'CH-0021', '2026-02-28', 'Rhodium', 'Account', 3, NULL, 'Draft', 1.000, 60.00, 18.00, 0.00, 60.00, 0, NULL, '', '0000-00-00', 8, 0, '2026-02-28 06:25:53', '2026-02-28 06:25:53');

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
  `current_gold_price` decimal(10,2) DEFAULT NULL,
  `adjusted_gold_weight` decimal(10,3) DEFAULT NULL,
  `gold_adjustment_amount` decimal(15,2) DEFAULT NULL,
  `line_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `challan_lines`
--

INSERT INTO `challan_lines` (`id`, `challan_id`, `line_number`, `product_ids`, `product_name`, `process_ids`, `process_prices`, `quantity`, `weight`, `rate`, `amount`, `image_path`, `gold_weight`, `gold_fine_weight`, `gold_purity`, `current_gold_price`, `adjusted_gold_weight`, `gold_adjustment_amount`, `line_notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '[\"2\"]', 'Pendant', '[\"1\"]', '[{\"process_id\":\"1\",\"process_name\":\"Rhodium Black\",\"rate\":60}]', 1, 1.000, 60.00, 60.00, 'uploads/challan_images/1772259953_23dce7112d702f920568.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-28 06:25:53', '2026-02-28 06:25:53');

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
(1, 'System Administrator', 'System Administrator', 'Gold Manufacturing', 'System HQ', NULL, 'System City', 1, '000000', 'System Admin', 'admin@gmail.com', '9999999999', NULL, NULL, NULL, 'SYS-', 'CH-', 18.00, 0.00, 4, 'Y-m-d', 'Asia/Kolkata', 'Active', 22, 21, '2026-02-08 16:31:03', '2026-02-28 06:56:22', 0),
(2, 'Meena', 'Meena', 'Gold Manufacturing', 'System HQ', NULL, 'System City', 1, '000000', 'System Admin', 'meena@gmail.com', '8888888888', NULL, NULL, NULL, 'SYS-', 'MN-', 18.00, 0.00, 4, 'Y-m-d', 'Asia/Kolkata', 'Active', 13, 9, '2026-02-08 16:31:03', '2026-02-24 17:08:51', 0);

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
  `delivery_contact_name` varchar(100) DEFAULT NULL,
  `customer_contact_mobile` varchar(10) NOT NULL,
  `delivery_notes` text DEFAULT NULL,
  `failed_reason` text DEFAULT NULL,
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
  `metal_type` enum('22K','24K','Silver') NOT NULL,
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
(1, 1, '2026-02-21', '22K', 15000.00, 2, NULL, 0, '2026-02-21 08:20:14', '2026-02-21 08:20:14'),
(2, 1, '2026-02-23', '22K', 15000.00, 2, NULL, 0, '2026-02-23 17:52:27', '2026-02-23 17:52:27'),
(3, 1, '2026-02-24', '22K', 14000.00, 7, NULL, 0, '2026-02-24 17:12:54', '2026-02-24 17:12:54'),
(4, 2, '2026-02-24', '22K', 11000.00, 7, NULL, 0, '2026-02-24 17:13:07', '2026-02-24 17:13:07');

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

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `company_id`, `invoice_number`, `invoice_type`, `invoice_date`, `due_date`, `account_id`, `cash_customer_id`, `billing_address`, `shipping_address`, `reference_number`, `challan_ids`, `subtotal`, `tax_rate`, `tax_amount`, `cgst_amount`, `sgst_amount`, `igst_amount`, `grand_total`, `total_paid`, `amount_due`, `invoice_status`, `payment_status`, `gold_adjustment_applied`, `gold_adjustment_date`, `gold_adjustment_amount`, `gold_rate_used`, `notes`, `terms_conditions`, `created_by`, `created_at`, `updated_by`, `updated_at`, `is_deleted`) VALUES
(1, 1, 'SYS-0021', 'Cash Invoice', '2026-02-28', '0000-00-00', NULL, 1, NULL, NULL, NULL, NULL, 169.49, 18.00, 30.51, 15.26, 15.25, 0.00, 200.00, 0.00, 200.00, 'Draft', 'Pending', 0, NULL, NULL, NULL, NULL, '', 8, '2026-02-28 06:44:26', 8, '2026-02-28 06:44:26', 0),
(3, 1, 'SYS-0022', 'Cash Invoice', '2026-02-28', '0000-00-00', NULL, 2, '', '', '', NULL, 6423.73, 18.00, 1156.27, 578.14, 578.13, 0.00, 7580.00, 0.00, 7580.00, 'Draft', 'Pending', 0, NULL, NULL, NULL, '', '', 8, '2026-02-28 06:56:22', 8, '2026-02-28 06:57:43', 0);

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
  `image_path` varchar(500) DEFAULT NULL COMMENT 'Path to uploaded line image',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoice_lines`
--

INSERT INTO `invoice_lines` (`id`, `invoice_id`, `line_number`, `source_challan_id`, `source_challan_line_id`, `product_ids`, `product_name`, `process_ids`, `process_prices`, `quantity`, `weight`, `rate`, `amount`, `gold_weight`, `gold_fine_weight`, `gold_purity`, `original_gold_weight`, `adjusted_gold_weight`, `gold_adjustment_amount`, `line_notes`, `image_path`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, NULL, '[\"4\"]', NULL, '[\"8\"]', NULL, 1, 1.000, 200.00, 200.00, 0.000, NULL, NULL, 0.000, 0.000, 0.00, NULL, NULL, '2026-02-28 06:44:26', '2026-02-28 06:44:26'),
(4, 3, 1, NULL, NULL, '[\"3\"]', NULL, '[\"1\",\"7\"]', '[]', 1, 1.000, 80.00, 80.00, 0.000, NULL, '', 0.000, 0.000, 0.00, NULL, 'uploads/invoice_images/1772261782_0de4a094d73d8eafbe11.png', '2026-02-28 06:57:43', '2026-02-28 06:57:43'),
(5, 3, 2, NULL, NULL, '[\"1\"]', NULL, '[\"8\"]', '[]', 1, 2.500, 200.00, 7500.00, 3.000, NULL, '22K', 0.000, 0.500, 7000.00, NULL, 'uploads/invoice_images/1772261782_e1114a24838396b85072.jpg', '2026-02-28 06:57:43', '2026-02-28 06:57:43');

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

--
-- Dumping data for table `ledger_entries`
--

INSERT INTO `ledger_entries` (`id`, `company_id`, `account_id`, `cash_customer_id`, `entry_date`, `reference_type`, `reference_id`, `reference_number`, `description`, `debit_amount`, `credit_amount`, `balance_after`, `created_at`) VALUES
(1, 1, NULL, 1, '2026-02-28', 'invoice', 1, 'SYS-0021', 'Invoice Generated: SYS-0021', 200.00, 0.00, 450.00, '2026-02-28 06:44:26'),
(2, 1, NULL, 2, '2026-02-28', 'invoice', 3, 'SYS-0022', 'Invoice Generated: SYS-0022', 7580.00, 0.00, 7580.00, '2026-02-28 06:56:22');

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
(27, '2026-02-11-203826', 'App\\Database\\Migrations\\AddTaxPercentToChallans', 'default', 'App', 1770842330, 10),
(28, '2026-01-01-000014', 'AppDatabaseMigrationsCreateInvoicesTable', 'default', 'App', 1771071119, 1),
(29, '2026-01-01-000014', 'App\\Database\\Migrations\\CreateInvoicesTable', 'default', 'App', 1771071301, 11),
(30, '2026-01-01-000015', 'App\\Database\\Migrations\\CreateInvoiceLinesTable', 'default', 'App', 1771071301, 11),
(31, '2026-01-01-000016', 'App\\Database\\Migrations\\CreatePaymentsTable', 'default', 'App', 1771071327, 12),
(32, '2026-02-14-000001', 'App\\Database\\Migrations\\ModifyDeliveriesTable', 'default', 'App', 1771074966, 13),
(33, '2026-01-01-000017', 'App\\Database\\Migrations\\CreateLedgerEntriesTable', 'default', 'App', 1771163014, 14),
(34, '2026-01-01-000019', 'App\\Database\\Migrations\\CreateAuditLogsTable', 'default', 'App', 1771173026, 15),
(35, '2026-02-16-000001', 'App\\Database\\Migrations\\MakeAuditFKNullable', 'default', 'App', 1771182410, 16),
(36, '2026-02-19-000001', 'App\\Database\\Migrations\\AddGoldAdjustmentToChallanLines', 'default', 'App', 1771441631, 17),
(37, '2026-02-20-000001', 'App\\Database\\Migrations\\FixGoldRatesTable', 'default', 'App', 1771607637, 18),
(38, '2026-02-22-000001', 'App\\Database\\Migrations\\CreatePermissionsTable', 'default', 'App', 1771739036, 19),
(39, '2026-02-22-000002', 'App\\Database\\Migrations\\UpdateRolePermissionsRbac', 'default', 'App', 1771739036, 19),
(40, '2026-02-28-000001', 'App\\Database\\Migrations\\AddImagePathToInvoiceLines', 'default', 'App', 1772260241, 20);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `payment_number` varchar(50) NOT NULL,
  `invoice_id` int(10) UNSIGNED NOT NULL,
  `customer_type` enum('Account','Cash') NOT NULL,
  `account_id` int(10) UNSIGNED DEFAULT NULL,
  `cash_customer_id` int(10) UNSIGNED DEFAULT NULL,
  `payment_date` date NOT NULL,
  `payment_amount` decimal(15,2) NOT NULL,
  `payment_mode` enum('Cash','Cheque','Bank Transfer','UPI','Card','Other') NOT NULL,
  `cheque_number` varchar(50) DEFAULT NULL,
  `cheque_date` date DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `transaction_reference` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `received_by` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `permission` varchar(150) NOT NULL,
  `label` varchar(200) NOT NULL,
  `module` varchar(50) NOT NULL,
  `sub_module` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `permission`, `label`, `module`, `sub_module`, `action`, `sort_order`, `is_active`) VALUES
(1, 'invoices.all.list', 'All Invoices - List', 'invoices', 'all', 'list', 1, 1),
(2, 'invoices.all.view', 'All Invoices - View', 'invoices', 'all', 'view', 2, 1),
(3, 'invoices.all.create', 'All Invoices - Create', 'invoices', 'all', 'create', 3, 1),
(4, 'invoices.all.edit', 'All Invoices - Edit', 'invoices', 'all', 'edit', 4, 1),
(5, 'invoices.all.delete', 'All Invoices - Delete', 'invoices', 'all', 'delete', 5, 1),
(6, 'invoices.all.print', 'All Invoices - Print', 'invoices', 'all', 'print', 6, 1),
(7, 'invoices.all.status_change', 'All Invoices - Status Change', 'invoices', 'all', 'status_change', 7, 1),
(8, 'invoices.all.record_payment', 'All Invoices - Record Payment', 'invoices', 'all', 'record_payment', 8, 1),
(9, 'invoices.account.list', 'Account Invoice - List', 'invoices', 'account', 'list', 9, 1),
(10, 'invoices.account.view', 'Account Invoice - View', 'invoices', 'account', 'view', 10, 1),
(11, 'invoices.account.create', 'Account Invoice - Create', 'invoices', 'account', 'create', 11, 1),
(12, 'invoices.account.edit', 'Account Invoice - Edit', 'invoices', 'account', 'edit', 12, 1),
(13, 'invoices.account.delete', 'Account Invoice - Delete', 'invoices', 'account', 'delete', 13, 1),
(14, 'invoices.account.print', 'Account Invoice - Print', 'invoices', 'account', 'print', 14, 1),
(15, 'invoices.account.status_change', 'Account Invoice - Status Change', 'invoices', 'account', 'status_change', 15, 1),
(16, 'invoices.account.record_payment', 'Account Invoice - Record Payment', 'invoices', 'account', 'record_payment', 16, 1),
(17, 'invoices.cash.list', 'Cash Invoice - List', 'invoices', 'cash', 'list', 17, 1),
(18, 'invoices.cash.view', 'Cash Invoice - View', 'invoices', 'cash', 'view', 18, 1),
(19, 'invoices.cash.create', 'Cash Invoice - Create', 'invoices', 'cash', 'create', 19, 1),
(20, 'invoices.cash.edit', 'Cash Invoice - Edit', 'invoices', 'cash', 'edit', 20, 1),
(21, 'invoices.cash.delete', 'Cash Invoice - Delete', 'invoices', 'cash', 'delete', 21, 1),
(22, 'invoices.cash.print', 'Cash Invoice - Print', 'invoices', 'cash', 'print', 22, 1),
(23, 'invoices.cash.status_change', 'Cash Invoice - Status Change', 'invoices', 'cash', 'status_change', 23, 1),
(24, 'invoices.cash.record_payment', 'Cash Invoice - Record Payment', 'invoices', 'cash', 'record_payment', 24, 1),
(25, 'invoices.wax.list', 'Wax Invoice - List', 'invoices', 'wax', 'list', 25, 1),
(26, 'invoices.wax.view', 'Wax Invoice - View', 'invoices', 'wax', 'view', 26, 1),
(27, 'invoices.wax.create', 'Wax Invoice - Create', 'invoices', 'wax', 'create', 27, 1),
(28, 'invoices.wax.edit', 'Wax Invoice - Edit', 'invoices', 'wax', 'edit', 28, 1),
(29, 'invoices.wax.delete', 'Wax Invoice - Delete', 'invoices', 'wax', 'delete', 29, 1),
(30, 'invoices.wax.print', 'Wax Invoice - Print', 'invoices', 'wax', 'print', 30, 1),
(31, 'invoices.wax.status_change', 'Wax Invoice - Status Change', 'invoices', 'wax', 'status_change', 31, 1),
(32, 'invoices.wax.record_payment', 'Wax Invoice - Record Payment', 'invoices', 'wax', 'record_payment', 32, 1),
(33, 'challans.all.list', 'All Challans - List', 'challans', 'all', 'list', 33, 1),
(34, 'challans.all.view', 'All Challans - View', 'challans', 'all', 'view', 34, 1),
(35, 'challans.all.create', 'All Challans - Create', 'challans', 'all', 'create', 35, 1),
(36, 'challans.all.edit', 'All Challans - Edit', 'challans', 'all', 'edit', 36, 1),
(37, 'challans.all.delete', 'All Challans - Delete', 'challans', 'all', 'delete', 37, 1),
(38, 'challans.all.print', 'All Challans - Print', 'challans', 'all', 'print', 38, 1),
(39, 'challans.all.status_change', 'All Challans - Status Change', 'challans', 'all', 'status_change', 39, 1),
(40, 'challans.rhodium.list', 'Rhodium Challan - List', 'challans', 'rhodium', 'list', 40, 1),
(41, 'challans.rhodium.view', 'Rhodium Challan - View', 'challans', 'rhodium', 'view', 41, 1),
(42, 'challans.rhodium.create', 'Rhodium Challan - Create', 'challans', 'rhodium', 'create', 42, 1),
(43, 'challans.rhodium.edit', 'Rhodium Challan - Edit', 'challans', 'rhodium', 'edit', 43, 1),
(44, 'challans.rhodium.delete', 'Rhodium Challan - Delete', 'challans', 'rhodium', 'delete', 44, 1),
(45, 'challans.rhodium.print', 'Rhodium Challan - Print', 'challans', 'rhodium', 'print', 45, 1),
(46, 'challans.rhodium.status_change', 'Rhodium Challan - Status Change', 'challans', 'rhodium', 'status_change', 46, 1),
(47, 'challans.meena.list', 'Meena Challan - List', 'challans', 'meena', 'list', 47, 1),
(48, 'challans.meena.view', 'Meena Challan - View', 'challans', 'meena', 'view', 48, 1),
(49, 'challans.meena.create', 'Meena Challan - Create', 'challans', 'meena', 'create', 49, 1),
(50, 'challans.meena.edit', 'Meena Challan - Edit', 'challans', 'meena', 'edit', 50, 1),
(51, 'challans.meena.delete', 'Meena Challan - Delete', 'challans', 'meena', 'delete', 51, 1),
(52, 'challans.meena.print', 'Meena Challan - Print', 'challans', 'meena', 'print', 52, 1),
(53, 'challans.meena.status_change', 'Meena Challan - Status Change', 'challans', 'meena', 'status_change', 53, 1),
(54, 'challans.wax.list', 'Wax Challan - List', 'challans', 'wax', 'list', 54, 1),
(55, 'challans.wax.view', 'Wax Challan - View', 'challans', 'wax', 'view', 55, 1),
(56, 'challans.wax.create', 'Wax Challan - Create', 'challans', 'wax', 'create', 56, 1),
(57, 'challans.wax.edit', 'Wax Challan - Edit', 'challans', 'wax', 'edit', 57, 1),
(58, 'challans.wax.delete', 'Wax Challan - Delete', 'challans', 'wax', 'delete', 58, 1),
(59, 'challans.wax.print', 'Wax Challan - Print', 'challans', 'wax', 'print', 59, 1),
(60, 'challans.wax.status_change', 'Wax Challan - Status Change', 'challans', 'wax', 'status_change', 60, 1),
(61, 'payments.all.list', 'Payments - List', 'payments', 'all', 'list', 61, 1),
(62, 'payments.all.view', 'Payments - View', 'payments', 'all', 'view', 62, 1),
(63, 'payments.all.create', 'Payments - Create', 'payments', 'all', 'create', 63, 1),
(64, 'payments.all.delete', 'Payments - Delete', 'payments', 'all', 'delete', 64, 1),
(65, 'masters.gold_rates.list', 'Gold Rates - List', 'masters', 'gold_rates', 'list', 65, 1),
(66, 'masters.gold_rates.view', 'Gold Rates - View', 'masters', 'gold_rates', 'view', 66, 1),
(67, 'masters.gold_rates.create', 'Gold Rates - Create', 'masters', 'gold_rates', 'create', 67, 1),
(68, 'masters.gold_rates.edit', 'Gold Rates - Edit', 'masters', 'gold_rates', 'edit', 68, 1),
(69, 'masters.product_categories.list', 'Product Categories - List', 'masters', 'product_categories', 'list', 69, 1),
(70, 'masters.product_categories.view', 'Product Categories - View', 'masters', 'product_categories', 'view', 70, 1),
(71, 'masters.product_categories.create', 'Product Categories - Create', 'masters', 'product_categories', 'create', 71, 1),
(72, 'masters.product_categories.edit', 'Product Categories - Edit', 'masters', 'product_categories', 'edit', 72, 1),
(73, 'masters.product_categories.delete', 'Product Categories - Delete', 'masters', 'product_categories', 'delete', 73, 1),
(74, 'masters.products.list', 'Products - List', 'masters', 'products', 'list', 74, 1),
(75, 'masters.products.view', 'Products - View', 'masters', 'products', 'view', 75, 1),
(76, 'masters.products.create', 'Products - Create', 'masters', 'products', 'create', 76, 1),
(77, 'masters.products.edit', 'Products - Edit', 'masters', 'products', 'edit', 77, 1),
(78, 'masters.products.delete', 'Products - Delete', 'masters', 'products', 'delete', 78, 1),
(79, 'masters.processes.list', 'Processes - List', 'masters', 'processes', 'list', 79, 1),
(80, 'masters.processes.view', 'Processes - View', 'masters', 'processes', 'view', 80, 1),
(81, 'masters.processes.create', 'Processes - Create', 'masters', 'processes', 'create', 81, 1),
(82, 'masters.processes.edit', 'Processes - Edit', 'masters', 'processes', 'edit', 82, 1),
(83, 'masters.processes.delete', 'Processes - Delete', 'masters', 'processes', 'delete', 83, 1),
(84, 'customers.accounts.list', 'Account Customers - List', 'customers', 'accounts', 'list', 84, 1),
(85, 'customers.accounts.view', 'Account Customers - View', 'customers', 'accounts', 'view', 85, 1),
(86, 'customers.accounts.create', 'Account Customers - Create', 'customers', 'accounts', 'create', 86, 1),
(87, 'customers.accounts.edit', 'Account Customers - Edit', 'customers', 'accounts', 'edit', 87, 1),
(88, 'customers.accounts.delete', 'Account Customers - Delete', 'customers', 'accounts', 'delete', 88, 1),
(89, 'customers.accounts.view_ledger', 'Account Customers - View Ledger', 'customers', 'accounts', 'view_ledger', 89, 1),
(90, 'customers.cash_customers.list', 'Cash Customers - List', 'customers', 'cash_customers', 'list', 90, 1),
(91, 'customers.cash_customers.view', 'Cash Customers - View', 'customers', 'cash_customers', 'view', 91, 1),
(92, 'customers.cash_customers.create', 'Cash Customers - Create', 'customers', 'cash_customers', 'create', 92, 1),
(93, 'customers.cash_customers.edit', 'Cash Customers - Edit', 'customers', 'cash_customers', 'edit', 93, 1),
(94, 'customers.cash_customers.delete', 'Cash Customers - Delete', 'customers', 'cash_customers', 'delete', 94, 1),
(95, 'deliveries.all.list', 'All Deliveries - List', 'deliveries', 'all', 'list', 95, 1),
(96, 'deliveries.all.view', 'All Deliveries - View', 'deliveries', 'all', 'view', 96, 1),
(97, 'deliveries.all.create', 'All Deliveries - Create', 'deliveries', 'all', 'create', 97, 1),
(98, 'deliveries.all.start', 'All Deliveries - Start', 'deliveries', 'all', 'start', 98, 1),
(99, 'deliveries.all.complete', 'All Deliveries - Complete', 'deliveries', 'all', 'complete', 99, 1),
(100, 'deliveries.all.fail', 'All Deliveries - Fail', 'deliveries', 'all', 'fail', 100, 1),
(101, 'deliveries.assigned.list', 'Assigned Deliveries - List', 'deliveries', 'assigned', 'list', 101, 1),
(102, 'deliveries.assigned.view', 'Assigned Deliveries - View', 'deliveries', 'assigned', 'view', 102, 1),
(103, 'deliveries.assigned.start', 'Assigned Deliveries - Start', 'deliveries', 'assigned', 'start', 103, 1),
(104, 'deliveries.assigned.complete', 'Assigned Deliveries - Complete', 'deliveries', 'assigned', 'complete', 104, 1),
(105, 'deliveries.assigned.fail', 'Assigned Deliveries - Fail', 'deliveries', 'assigned', 'fail', 105, 1),
(106, 'reports.outstanding.list', 'Outstanding Report - List', 'reports', 'outstanding', 'list', 106, 1),
(107, 'reports.outstanding.export', 'Outstanding Report - Export', 'reports', 'outstanding', 'export', 107, 1),
(108, 'reports.receivables.list', 'Receivables Report - List', 'reports', 'receivables', 'list', 108, 1),
(109, 'reports.receivables.export', 'Receivables Report - Export', 'reports', 'receivables', 'export', 109, 1),
(110, 'reports.aging.list', 'Aging Report - List', 'reports', 'aging', 'list', 110, 1),
(111, 'reports.aging.export', 'Aging Report - Export', 'reports', 'aging', 'export', 111, 1),
(112, 'reports.monthly.list', 'Monthly Report - List', 'reports', 'monthly', 'list', 112, 1),
(113, 'reports.monthly.export', 'Monthly Report - Export', 'reports', 'monthly', 'export', 113, 1),
(114, 'ledgers.accounts.list', 'Account Ledgers - List', 'ledgers', 'accounts', 'list', 114, 1),
(115, 'ledgers.accounts.view', 'Account Ledgers - View', 'ledgers', 'accounts', 'view', 115, 1),
(116, 'ledgers.accounts.export', 'Account Ledgers - Export', 'ledgers', 'accounts', 'export', 116, 1),
(117, 'ledgers.cash_customers.list', 'Cash Customer Ledgers - List', 'ledgers', 'cash_customers', 'list', 117, 1),
(118, 'ledgers.cash_customers.view', 'Cash Customer Ledgers - View', 'ledgers', 'cash_customers', 'view', 118, 1),
(119, 'ledgers.cash_customers.export', 'Cash Customer Ledgers - Export', 'ledgers', 'cash_customers', 'export', 119, 1),
(120, 'ledgers.reminders.list', 'Reminders - List', 'ledgers', 'reminders', 'list', 120, 1),
(121, 'ledgers.reminders.send', 'Reminders - Send', 'ledgers', 'reminders', 'send', 121, 1),
(122, 'users.all.list', 'Users - List', 'users', 'all', 'list', 122, 1),
(123, 'users.all.view', 'Users - View', 'users', 'all', 'view', 123, 1),
(124, 'users.all.create', 'Users - Create', 'users', 'all', 'create', 124, 1),
(125, 'users.all.edit', 'Users - Edit', 'users', 'all', 'edit', 125, 1),
(126, 'users.all.delete', 'Users - Delete', 'users', 'all', 'delete', 126, 1),
(127, 'users.all.change_password', 'Users - Change Password', 'users', 'all', 'change_password', 127, 1),
(128, 'roles.all.list', 'Roles - List', 'roles', 'all', 'list', 128, 1),
(129, 'roles.all.view', 'Roles - View', 'roles', 'all', 'view', 129, 1),
(130, 'roles.all.create', 'Roles - Create', 'roles', 'all', 'create', 130, 1),
(131, 'roles.all.edit', 'Roles - Edit', 'roles', 'all', 'edit', 131, 1),
(132, 'roles.all.delete', 'Roles - Delete', 'roles', 'all', 'delete', 132, 1),
(133, 'roles.all.manage_permissions', 'Roles - Manage Permissions', 'roles', 'all', 'manage_permissions', 133, 1),
(134, 'audit.logs.list', 'Audit Logs - List', 'audit', 'logs', 'list', 134, 1),
(135, 'audit.logs.view', 'Audit Logs - View', 'audit', 'logs', 'view', 135, 1),
(136, 'settings.company.view', 'Company Settings - View', 'settings', 'company', 'view', 136, 1),
(137, 'settings.company.edit', 'Company Settings - Edit', 'settings', 'company', 'edit', 137, 1);

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
(3, 1, 'RW', 'Rhodium White', 'Rhodium', '', 20.00, 'PCS', 1, 0, '2026-02-11 19:02:36', '2026-02-11 19:02:36'),
(4, 2, 'RB', 'Rhodium Black', 'Rhodium', '', 60.00, 'GRAM', 1, 0, '2026-02-11 19:01:53', '2026-02-11 19:01:53'),
(5, 2, 'RP', 'Rhodium Pink', 'Rhodium', '', 25.00, 'PCS', 1, 0, '2026-02-11 19:02:08', '2026-02-11 19:02:08'),
(6, 2, 'RW', 'Rhodium White', 'Rhodium', '', 20.00, 'PCS', 1, 0, '2026-02-11 19:02:36', '2026-02-11 19:02:36'),
(7, 1, 'RG', 'Rhodium God', 'Rhodium', '', 20.00, 'GRAM', 1, 0, '2026-02-27 09:36:41', '2026-02-27 09:36:41'),
(8, 1, 'CR', 'Ceramic Rhodium', 'Rhodium', '', 200.00, 'GRAM', 1, 0, '2026-02-27 09:37:12', '2026-02-27 09:37:12'),
(9, 1, 'VP', 'Victorium Platinum', 'Rhodium', '', 60.00, 'GRAM', 1, 0, '2026-02-27 09:37:35', '2026-02-27 09:37:35'),
(10, 1, 'MP', 'Micro Platinum', 'Rhodium', '', 300.00, 'GRAM', 1, 0, '2026-02-27 09:37:57', '2026-02-27 09:37:57'),
(11, 2, 'RG', 'Rhodium God', 'Rhodium', '', 20.00, 'GRAM', 1, 0, '2026-02-27 09:36:41', '2026-02-27 09:36:41'),
(12, 2, 'CR', 'Ceramic Rhodium', 'Rhodium', '', 200.00, 'GRAM', 1, 0, '2026-02-27 09:37:12', '2026-02-27 09:37:12'),
(13, 2, 'VP', 'Victorium Platinum', 'Rhodium', '', 60.00, 'GRAM', 1, 0, '2026-02-27 09:37:35', '2026-02-27 09:37:35'),
(14, 2, 'MP', 'Micro Platinum', 'Rhodium', '', 300.00, 'GRAM', 1, 0, '2026-02-27 09:37:57', '2026-02-27 09:37:57');

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
(4, 1, 1, 'NECK001', 'Necklace', '', 'uploads/products/1771607177_98439e3b8d2d71cbb6a1.png', '', 'PCS', 1, 0, '2026-02-11 19:01:28', '2026-02-20 17:06:17'),
(6, 1, 1, 'B001', 'Bracelet', '', NULL, '', 'PCS', 1, 0, '2026-02-20 17:09:02', '2026-02-20 17:09:02'),
(7, 2, 1, 'RING001', 'Ring', '', NULL, '', 'PCS', 1, 0, '2026-02-11 19:00:26', '2026-02-11 19:00:26'),
(8, 2, 1, 'PEN001', 'Pendant', '', NULL, '', 'PCS', 1, 0, '2026-02-11 19:00:38', '2026-02-11 19:00:38'),
(9, 2, 1, 'EAR001', 'Earring', '', 'uploads/products/1770901221_d2e1aff5be6425ec83e8.jpg', '', 'PCS', 1, 0, '2026-02-11 19:01:15', '2026-02-12 13:00:21'),
(10, 2, 1, 'NECK001', 'Necklace', '', 'uploads/products/1771607177_98439e3b8d2d71cbb6a1.png', '', 'PCS', 1, 0, '2026-02-11 19:01:28', '2026-02-20 17:06:17'),
(11, 2, 1, 'B001', 'Bracelet', '', NULL, '', 'PCS', 1, 0, '2026-02-20 17:09:02', '2026-02-20 17:09:02');

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
(1, 1, 'Jewellery', 'C001', '', 0, 1, 0, '2026-02-11 19:00:05', '2026-02-11 19:00:05'),
(2, 1, 'Electronics', '002', '', 0, 1, 0, '2026-02-20 17:00:34', '2026-02-20 17:00:34');

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
(2, 0, 'Company Administrator', 'Complete control over company specific data and settings', '[\"invoices.*\",\"challans.*\",\"payments.*\",\"masters.*\",\"customers.*\",\"deliveries.*\",\"reports.*\",\"ledgers.*\",\"users.*\",\"roles.*\",\"audit.*\",\"settings.*\"]', 1, 1, '2026-02-08 16:27:09', '2026-02-22 05:43:56', 0),
(3, 0, 'Billing Manager', 'Manages invoicing, challans, and basic reporting', '[\"invoices.*\",\"challans.*\",\"payments.all.list\",\"payments.all.view\",\"payments.all.create\",\"reports.outstanding.list\",\"reports.receivables.list\",\"customers.accounts.list\",\"customers.accounts.view\",\"customers.cash_customers.list\",\"customers.cash_customers.view\",\"masters.products.list\",\"masters.products.view\",\"masters.processes.list\",\"masters.processes.view\",\"masters.gold_rates.list\",\"masters.gold_rates.view\",\"masters.product_categories.list\",\"masters.product_categories.view\"]', 1, 1, '2026-02-08 16:27:09', '2026-02-22 05:43:56', 0),
(4, 0, 'Accounts Manager', 'Focus on payments, accounting reports, and customer finances', '[\"payments.*\",\"reports.*\",\"ledgers.*\",\"invoices.all.list\",\"invoices.all.view\",\"invoices.all.print\",\"invoices.account.list\",\"invoices.account.view\",\"invoices.account.print\",\"invoices.account.record_payment\",\"challans.all.list\",\"challans.all.view\",\"challans.all.print\",\"customers.*\"]', 1, 1, '2026-02-08 16:27:09', '2026-02-22 05:43:56', 0),
(5, 0, 'Delivery Personnel', 'Access to assigned deliveries and invoice viewing', '[\"invoices.all.list\",\"invoices.all.view\",\"invoices.all.print\",\"deliveries.assigned.list\",\"deliveries.assigned.view\",\"deliveries.assigned.start\",\"deliveries.assigned.complete\",\"deliveries.assigned.fail\"]', 1, 1, '2026-02-08 16:27:09', '2026-02-22 05:43:56', 0),
(6, 0, 'Report Viewer', 'Read-only access to reports and core data', '[\"invoices.all.list\",\"invoices.all.view\",\"invoices.all.print\",\"challans.all.list\",\"challans.all.view\",\"challans.all.print\",\"reports.*\",\"ledgers.accounts.list\",\"ledgers.accounts.view\",\"ledgers.cash_customers.list\",\"ledgers.cash_customers.view\",\"customers.accounts.list\",\"customers.accounts.view\",\"customers.cash_customers.list\",\"customers.cash_customers.view\"]', 1, 1, '2026-02-08 16:27:09', '2026-02-22 05:43:56', 0),
(7, 1, 'Account Invoice Viewer', 'Can see All Invoices and Account Invoice menu. List, View, Print actions only. No status change or payment recording.', '[\"invoices.all.list\",\"invoices.all.view\",\"invoices.all.print\",\"invoices.account.list\",\"invoices.account.view\",\"invoices.account.print\"]', 0, 1, '2026-02-22 05:43:56', '2026-02-22 05:43:56', 0),
(8, 1, 'Cash Invoice Operator', 'Can see All Invoices and Cash Invoice menu. Has List, View, Print, Status Change, and Record Payment on Cash Invoices. List/View/Print only on All Invoices page.', '[\"invoices.all.list\",\"invoices.all.view\",\"invoices.all.print\",\"invoices.cash.list\",\"invoices.cash.view\",\"invoices.cash.print\",\"invoices.cash.status_change\",\"invoices.cash.record_payment\"]', 0, 1, '2026-02-22 05:43:56', '2026-02-22 05:43:56', 0),
(9, 1, 'Challan Viewer', 'Can see All Challans, Rhodium Challan, and Meena Challan menus. Has List/View/Print/Status Change on Rhodium and Meena. List/View/Print only on All Challans.', '[\"challans.all.list\",\"challans.all.view\",\"challans.all.print\",\"challans.rhodium.list\",\"challans.rhodium.view\",\"challans.rhodium.print\",\"challans.rhodium.status_change\",\"challans.meena.list\",\"challans.meena.view\",\"challans.meena.print\",\"challans.meena.status_change\"]', 0, 1, '2026-02-22 05:43:56', '2026-02-22 05:43:56', 0),
(11, 1, 'Operator', '', '[\"challans.all.list\",\"challans.all.view\",\"challans.meena.list\",\"challans.meena.view\",\"challans.meena.create\",\"challans.meena.edit\",\"challans.meena.delete\",\"challans.meena.print\",\"challans.meena.status_change\",\"challans.rhodium.list\",\"challans.rhodium.view\",\"challans.rhodium.create\",\"challans.rhodium.edit\",\"challans.rhodium.delete\",\"challans.rhodium.print\",\"challans.rhodium.status_change\",\"customers.accounts.list\",\"customers.accounts.view\",\"customers.accounts.create\",\"customers.accounts.edit\",\"customers.accounts.delete\",\"customers.accounts.view_ledger\",\"customers.cash_customers.list\",\"customers.cash_customers.view\",\"customers.cash_customers.create\",\"customers.cash_customers.edit\",\"customers.cash_customers.delete\",\"invoices.account.list\",\"invoices.account.view\",\"invoices.account.create\",\"invoices.account.edit\",\"invoices.account.delete\",\"invoices.account.print\",\"invoices.account.status_change\",\"invoices.account.record_payment\",\"invoices.cash.list\",\"invoices.cash.view\",\"invoices.cash.create\",\"invoices.cash.edit\",\"invoices.cash.delete\",\"invoices.cash.print\",\"invoices.cash.status_change\",\"invoices.cash.record_payment\",\"masters.gold_rates.list\",\"masters.gold_rates.view\",\"masters.gold_rates.create\",\"masters.gold_rates.edit\"]', 0, 1, '2026-02-22 06:11:11', '2026-02-27 16:26:01', 0);

-- --------------------------------------------------------

--
-- Table structure for table `roles_backup_rbac`
--

CREATE TABLE `roles_backup_rbac` (
  `id` int(10) UNSIGNED NOT NULL DEFAULT 0,
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
-- Dumping data for table `roles_backup_rbac`
--

INSERT INTO `roles_backup_rbac` (`id`, `company_id`, `role_name`, `role_description`, `permissions`, `is_system_role`, `is_active`, `created_at`, `updated_at`, `is_deleted`) VALUES
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
(2, 1, 'superadmin', 'admin@gmail.com', '$2y$10$3lD2hiugSlildxvjTeH9bue.5rQEqtidB6krrpytoJk4hpXfKZ/WC', 'System Administrator', '9999999999', NULL, NULL, NULL, NULL, NULL, 'Active', 0, '2026-02-28 05:58:51', NULL, '2026-02-08 16:31:03', '2026-02-28 05:58:51', 0),
(4, 1, 'parinpatel', 'parinwork@gmail.com', '$2y$10$TBdnobbaIGwuvdZuPhT5yu7U1hVKWCcVbpSpmwMNhINEdFYkUsiNa', 'Parin Patel', '9586969009', NULL, NULL, NULL, NULL, NULL, 'Active', 0, '2026-02-10 20:12:17', NULL, '2026-02-10 20:11:17', '2026-02-10 20:12:17', 0),
(5, 1, 'parindelivery', 'parindelivery@gmail.com', '$2y$10$JVSHEEiKOf62a3cxPmxjL.WmzMc.yCkRfH.oo/96cxRztNIcFiLA.', 'Parin Delivery', '9586969119', NULL, NULL, NULL, NULL, NULL, 'Active', 0, '2026-02-14 13:22:58', NULL, '2026-02-14 13:22:23', '2026-02-14 13:22:58', 0),
(6, 1, 'manager', 'manager@gmail.com', '$2y$10$k3x9VwI9Bl1uXw6GcxGlLeflSixKRBccf7dgccbbAzZjz1Ws.0W.y', 'Billing Manager', '8888888888', NULL, NULL, NULL, NULL, NULL, 'Active', 0, '2026-02-21 09:31:04', NULL, '2026-02-21 09:30:33', '2026-02-21 09:31:04', 0),
(7, 1, 'Cashier', 'cashier@gmail.com', '$2y$10$5URo0zwOZQ6Tdc2E7X/anuZCSbS7tDkGFmOVpX5whaU3F7SPPNn22', 'New', '9992223323', NULL, NULL, NULL, NULL, NULL, 'Active', 0, '2026-02-27 09:14:18', NULL, '2026-02-22 06:12:24', '2026-02-27 09:14:18', 0),
(8, 1, 'operator', 'operator@gmail.com', '$2y$10$JFKOOH1lQUHJtbyCHxr5Xunu0NOBV87jrkJMe3pwHTm.S9TRjRPcu', 'Operator', '9999999999', NULL, NULL, NULL, NULL, NULL, 'Active', 0, '2026-02-28 06:13:29', NULL, '2026-02-27 09:34:40', '2026-02-28 06:13:29', 0);

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
(5, 4, 3, '2026-02-10 20:11:17', NULL),
(6, 5, 5, '2026-02-14 13:22:23', NULL),
(7, 6, 3, '2026-02-21 09:30:33', NULL),
(8, 7, 11, '2026-02-22 06:12:24', NULL),
(9, 8, 11, '2026-02-27 09:34:40', NULL);

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
  ADD UNIQUE KEY `uk_company_payment_number` (`company_id`,`payment_number`),
  ADD KEY `payments_received_by_foreign` (`received_by`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `cash_customer_id` (`cash_customer_id`),
  ADD KEY `payment_date` (`payment_date`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_permission` (`permission`),
  ADD KEY `idx_module` (`module`),
  ADD KEY `idx_sub_module` (`sub_module`);

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `account_groups`
--
ALTER TABLE `account_groups`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cash_customers`
--
ALTER TABLE `cash_customers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `challans`
--
ALTER TABLE `challans`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `challan_lines`
--
ALTER TABLE `challan_lines`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `invoice_lines`
--
ALTER TABLE `invoice_lines`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `ledger_entries`
--
ALTER TABLE `ledger_entries`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;

--
-- AUTO_INCREMENT for table `processes`
--
ALTER TABLE `processes`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `states`
--
ALTER TABLE `states`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
  ADD CONSTRAINT `payments_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`),
  ADD CONSTRAINT `payments_cash_customer_id_foreign` FOREIGN KEY (`cash_customer_id`) REFERENCES `cash_customers` (`id`),
  ADD CONSTRAINT `payments_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `payments_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`),
  ADD CONSTRAINT `payments_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`);

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
