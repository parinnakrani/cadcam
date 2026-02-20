-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 20, 2026 at 02:00 PM
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
(3, 1, 'ACC-0001', 'Parin', 'Code Nine', '', '9586969009', '', NULL, '', NULL, 'Minibazaar', '', 'Surat', 1, '395006', '', '', '', NULL, '', 0, '', '', 0.00, 'Debit', 7640.00, NULL, NULL, '', '', NULL, NULL, 1, '2026-02-11 19:19:38', '2026-02-19 19:00:50', 0);

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
(3, NULL, NULL, 'Auth', 'login', 'User', 2, NULL, '{\"ip\":\"::1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-15 19:08:36'),
(4, NULL, NULL, 'Auth', 'login', 'User', 2, NULL, '{\"ip\":\"::1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 18:33:21'),
(5, NULL, NULL, 'Challan', '', 'Challan', 6, NULL, '{\"company_id\":\"1\",\"user_id\":\"2\",\"challan_number\":\"CH-0008\",\"challan_type\":\"Rhodium\",\"customer_type\":\"Account\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 18:38:33'),
(6, NULL, NULL, 'Challan', '', 'Challan', 7, NULL, '{\"company_id\":\"1\",\"user_id\":\"2\",\"challan_number\":\"CH-0009\",\"challan_type\":\"Rhodium\",\"customer_type\":\"Account\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 18:43:58'),
(7, NULL, NULL, 'Challan', '', 'Challan', 8, NULL, '{\"company_id\":\"1\",\"user_id\":\"2\",\"challan_number\":\"CH-0010\",\"challan_type\":\"Rhodium\",\"customer_type\":\"Account\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 19:22:06'),
(8, NULL, NULL, 'Challan', '', 'Challan', 8, '{\"id\":\"8\",\"company_id\":\"1\",\"challan_number\":\"CH-0010\",\"challan_date\":\"2026-02-19\",\"challan_type\":\"Rhodium\",\"customer_type\":\"Account\",\"account_id\":\"3\",\"cash_customer_id\":null,\"challan_status\":\"Draft\",\"total_weight\":\"0.000\",\"subtotal_amount\":\"167.50\",\"tax_percent\":\"18.00\",\"tax_amount\":\"0.00\",\"total_amount\":\"167.50\",\"invoice_generated\":\"0\",\"invoice_id\":null,\"notes\":\"Here  notes will be saved\",\"delivery_date\":\"0000-00-00\",\"created_by\":\"2\",\"is_deleted\":\"0\",\"created_at\":\"2026-02-19 00:52:06\",\"updated_at\":\"2026-02-19 00:52:06\"}', '{\"challan_date\":\"2026-02-19\",\"challan_type\":\"Rhodium\",\"customer_type\":\"Account\",\"account_id\":\"3\",\"notes\":\"Here  notes will be saved\",\"delivery_date\":\"\",\"cash_customer_id\":null,\"updated_at\":\"2026-02-19 00:54:46\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 19:24:46'),
(9, NULL, NULL, 'Challan', '', 'Challan', 8, NULL, '{\"challan_number\":\"CH-0010\",\"line_count\":2}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 19:24:46'),
(10, NULL, NULL, 'Challan', '', 'Challan', 8, '{\"id\":\"8\",\"company_id\":\"1\",\"challan_number\":\"CH-0010\",\"challan_date\":\"2026-02-19\",\"challan_type\":\"Rhodium\",\"customer_type\":\"Account\",\"account_id\":\"3\",\"cash_customer_id\":null,\"challan_status\":\"Draft\",\"total_weight\":\"1.900\",\"subtotal_amount\":\"130.00\",\"tax_percent\":\"18.00\",\"tax_amount\":\"0.00\",\"total_amount\":\"130.00\",\"invoice_generated\":\"0\",\"invoice_id\":null,\"notes\":\"Here  notes will be saved\",\"delivery_date\":\"0000-00-00\",\"created_by\":\"2\",\"is_deleted\":\"0\",\"created_at\":\"2026-02-19 00:52:06\",\"updated_at\":\"2026-02-19 00:54:46\"}', '{\"challan_date\":\"2026-02-19\",\"challan_type\":\"Rhodium\",\"customer_type\":\"Account\",\"account_id\":\"3\",\"notes\":\"Here  notes will be saved\",\"delivery_date\":\"\",\"cash_customer_id\":null,\"updated_at\":\"2026-02-19 01:15:22\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 19:45:22'),
(11, NULL, NULL, 'Challan', '', 'Challan', 8, NULL, '{\"challan_number\":\"CH-0010\",\"line_count\":2}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 19:45:22'),
(12, NULL, NULL, 'Challan', '', 'Challan', 8, '{\"id\":\"8\",\"company_id\":\"1\",\"challan_number\":\"CH-0010\",\"challan_date\":\"2026-02-19\",\"challan_type\":\"Rhodium\",\"customer_type\":\"Account\",\"account_id\":\"3\",\"cash_customer_id\":null,\"challan_status\":\"Draft\",\"total_weight\":\"3.000\",\"subtotal_amount\":\"22630.00\",\"tax_percent\":\"18.00\",\"tax_amount\":\"0.00\",\"total_amount\":\"22630.00\",\"invoice_generated\":\"0\",\"invoice_id\":null,\"notes\":\"Here  notes will be saved\",\"delivery_date\":\"0000-00-00\",\"created_by\":\"2\",\"is_deleted\":\"0\",\"created_at\":\"2026-02-19 00:52:06\",\"updated_at\":\"2026-02-19 01:15:22\"}', '{\"challan_date\":\"2026-02-19\",\"challan_type\":\"Rhodium\",\"customer_type\":\"Account\",\"account_id\":\"3\",\"notes\":\"Here  notes will be saved\",\"delivery_date\":\"\",\"cash_customer_id\":null,\"updated_at\":\"2026-02-19 01:15:46\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 19:45:46'),
(13, NULL, NULL, 'Challan', '', 'Challan', 8, NULL, '{\"challan_number\":\"CH-0010\",\"line_count\":2}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 19:45:46'),
(14, NULL, NULL, 'Auth', 'login', 'User', 2, NULL, '{\"ip\":\"::1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-19 16:56:34'),
(15, NULL, NULL, 'Invoice', 'create', 'Invoice', 26, NULL, '{\"invoice_type\":\"Cash Invoice\",\"invoice_date\":\"2026-02-19\",\"due_date\":\"2026-02-28\",\"account_id\":null,\"cash_customer_id\":\"7\",\"billing_address\":null,\"shipping_address\":null,\"reference_number\":\"#1\",\"tax_rate\":\"18\",\"notes\":\"Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here \",\"terms_conditions\":\"Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions \",\"company_id\":\"1\",\"created_by\":\"2\",\"updated_by\":\"2\",\"invoice_number\":\"SYS-0007\",\"subtotal\":156.78,\"tax_amount\":28.22,\"cgst_amount\":0,\"sgst_amount\":0,\"igst_amount\":28.22,\"grand_total\":185,\"payment_terms\":null,\"challan_ids\":null,\"total_paid\":0,\"amount_due\":185,\"payment_status\":\"Pending\",\"invoice_status\":\"Draft\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-19 18:49:24'),
(16, NULL, NULL, 'Challan', '', 'Challan', 7, '{\"status\":\"Draft\"}', '{\"status\":\"Pending\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-19 18:57:04'),
(17, NULL, NULL, 'Challan', '', 'Challan', 7, '{\"status\":\"Pending\"}', '{\"status\":\"In Progress\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-19 18:57:10'),
(18, NULL, NULL, 'Challan', '', 'Challan', 7, '{\"status\":\"In Progress\"}', '{\"status\":\"Completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-19 18:57:15'),
(19, NULL, NULL, 'Challan', '', 'Challan', 9, NULL, '{\"company_id\":\"1\",\"user_id\":\"2\",\"challan_number\":\"CH-0011\",\"challan_type\":\"Rhodium\",\"customer_type\":\"Account\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-19 18:59:09'),
(20, NULL, NULL, 'Challan', '', 'Challan', 9, '{\"id\":\"9\",\"company_id\":\"1\",\"challan_number\":\"CH-0011\",\"challan_date\":\"2026-02-20\",\"challan_type\":\"Rhodium\",\"customer_type\":\"Account\",\"account_id\":\"3\",\"cash_customer_id\":null,\"challan_status\":\"Draft\",\"total_weight\":\"0.000\",\"subtotal_amount\":\"110.00\",\"tax_percent\":\"18.00\",\"tax_amount\":\"0.00\",\"total_amount\":\"110.00\",\"invoice_generated\":\"0\",\"invoice_id\":null,\"notes\":\"\",\"delivery_date\":\"0000-00-00\",\"created_by\":\"2\",\"is_deleted\":\"0\",\"created_at\":\"2026-02-20 00:29:09\",\"updated_at\":\"2026-02-20 00:29:09\"}', '{\"challan_date\":\"2026-02-20\",\"challan_type\":\"Rhodium\",\"customer_type\":\"Account\",\"account_id\":\"3\",\"notes\":\"\",\"delivery_date\":\"\",\"cash_customer_id\":null,\"updated_at\":\"2026-02-20 00:30:11\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-19 19:00:11'),
(21, NULL, NULL, 'Challan', '', 'Challan', 9, NULL, '{\"challan_number\":\"CH-0011\",\"line_count\":2}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-19 19:00:11'),
(22, NULL, NULL, 'Challan', '', 'Challan', 9, '{\"status\":\"Draft\"}', '{\"status\":\"Pending\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-19 19:00:24'),
(23, NULL, NULL, 'Challan', '', 'Challan', 9, '{\"status\":\"Pending\"}', '{\"status\":\"In Progress\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-19 19:00:29'),
(24, NULL, NULL, 'Challan', '', 'Challan', 9, '{\"status\":\"In Progress\"}', '{\"status\":\"Completed\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-19 19:00:33'),
(25, NULL, NULL, 'Invoice', 'create', 'Invoice', 27, NULL, '{\"company_id\":\"1\",\"invoice_type\":\"Accounts Invoice\",\"invoice_date\":\"2026-02-20\",\"account_id\":\"3\",\"billing_address\":\"Minibazaar , Surat - 395006\",\"shipping_address\":null,\"notes\":null,\"payment_terms\":null,\"challan_ids\":\"[\\\"9\\\"]\",\"tax_rate\":18,\"created_by\":\"2\",\"updated_by\":\"2\",\"invoice_number\":\"SYS-0008\",\"subtotal\":6474.58,\"tax_amount\":1165.42,\"cgst_amount\":582.71,\"sgst_amount\":582.71,\"igst_amount\":0,\"grand_total\":7640,\"cash_customer_id\":null,\"reference_number\":null,\"total_paid\":0,\"amount_due\":7640,\"payment_status\":\"Pending\",\"invoice_status\":\"Draft\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-19 19:00:50');

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
(2, 0, 'Parin Patel', '9586969009', '2026-02-13 16:39:39', '2026-02-13 16:39:39', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(6, 0, 'Parin Nakrani', '9999999999', '2026-02-13 17:49:03', '2026-02-19 17:44:39', 1, 0, '9999999999', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(7, 1, 'Parin Patel', '9909998990', '2026-02-13 18:47:07', '2026-02-19 18:49:24', 1, 0, '9909998990', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 185.00),
(8, 1, 'Alpesh', '9090909099', '2026-02-13 20:51:46', '2026-02-13 20:51:46', 1, 0, '9090909099', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(9, 1, 'Parin', '9909998990', '2026-02-14 08:54:28', '2026-02-14 08:54:28', 1, 0, '9909998990', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(10, 1, 'Alpesh Pansheriya', '8000259032', '2026-02-14 09:11:57', '2026-02-14 09:11:57', 1, 0, '8000259032', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(11, 1, 'Alpesh', '9998887775', '2026-02-14 09:26:02', '2026-02-14 09:26:02', 1, 0, '9998887775', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00);

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
(1, 1, 'CH-0003', '2026-02-12', 'Rhodium', 'Account', 3, NULL, 'Invoiced', 0.000, 66.00, NULL, 11.88, 77.88, 1, 21, 'Notes here', '0000-00-00', 2, 0, '2026-02-11 19:30:20', '2026-02-14 11:03:07'),
(2, 1, 'CH-0004', '2026-02-12', 'Rhodium', 'Account', 3, NULL, 'Invoiced', 0.000, 252.50, 18.00, 45.45, 297.95, 1, 21, '', '0000-00-00', 2, 0, '2026-02-11 20:44:56', '2026-02-14 11:03:07'),
(3, 1, 'CH-0005', '2026-02-12', 'Rhodium', 'Account', 3, NULL, 'Completed', 0.000, 85.00, 18.00, 15.30, 100.30, 0, NULL, '', '0000-00-00', 2, 0, '2026-02-11 20:48:10', '2026-02-14 10:15:37'),
(4, 1, 'CH-0006', '2026-02-14', 'Rhodium', 'Account', 3, NULL, 'Completed', 0.000, 300.00, 18.00, 54.00, 354.00, 0, NULL, '', '0000-00-00', 2, 0, '2026-02-14 09:50:21', '2026-02-14 10:09:19'),
(6, 1, 'CH-0008', '2026-02-19', 'Rhodium', 'Account', 3, NULL, 'Draft', 0.000, 25.00, 18.00, 4.50, 29.50, 0, NULL, '', '0000-00-00', 2, 0, '2026-02-18 18:38:33', '2026-02-18 18:38:33'),
(7, 1, 'CH-0009', '2026-02-20', 'Rhodium', 'Account', 3, NULL, 'Completed', 0.000, 115.00, 18.00, 20.70, 135.70, 0, NULL, 'Here is your notes', '2026-02-28', 2, 0, '2026-02-18 18:43:58', '2026-02-19 18:57:15'),
(8, 1, 'CH-0010', '2026-02-19', 'Rhodium', 'Account', 3, NULL, 'Draft', 28.000, 367630.00, 18.00, 0.00, 367630.00, 0, NULL, 'Here  notes will be saved', '0000-00-00', 2, 0, '2026-02-18 19:22:06', '2026-02-18 19:45:46'),
(9, 1, 'CH-0011', '2026-02-20', 'Rhodium', 'Account', 3, NULL, 'Invoiced', 2.000, 7640.00, 18.00, 0.00, 7640.00, 1, 27, '', '0000-00-00', 2, 0, '2026-02-19 18:59:09', '2026-02-19 19:00:50');

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
(5, 1, 1, '[\"1\"]', 'Ring', '[\"2\"]', NULL, 1, 2.000, 25.00, 50.00, NULL, 0.000, NULL, '', NULL, NULL, NULL, NULL, '2026-02-11 20:32:26', '2026-02-11 20:32:26'),
(6, 1, 2, '[\"3\",\"2\"]', 'Earring, Pendant', '[\"1\",\"3\"]', NULL, 1, 0.200, 80.00, 16.00, NULL, 0.000, NULL, '', NULL, NULL, NULL, NULL, '2026-02-11 20:32:26', '2026-02-11 20:32:26'),
(7, 2, 1, '[\"2\"]', 'Pendant', '[\"3\"]', '[]', 1, 2.000, 20.00, 40.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-11 20:44:56', '2026-02-11 20:44:56'),
(8, 2, 2, '[\"1\",\"4\"]', 'Ring, Necklace', '[\"1\",\"2\"]', '[]', 1, 2.500, 85.00, 212.50, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-11 20:44:56', '2026-02-11 20:44:56'),
(9, 3, 1, '[\"2\",\"3\"]', 'Pendant, Earring', '[\"1\",\"2\"]', '[]', 1, 1.000, 85.00, 85.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-11 20:48:10', '2026-02-11 20:48:10'),
(10, 4, 1, '[\"2\"]', 'Pendant', '[\"1\"]', '[]', 1, 5.000, 60.00, 300.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-14 09:50:21', '2026-02-14 09:50:21'),
(12, 6, 1, '[\"1\"]', 'Ring', '[\"2\"]', '[]', 1, 1.000, 25.00, 25.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 18:38:33', '2026-02-18 18:38:33'),
(13, 7, 1, '[\"2\",\"3\"]', 'Pendant, Earring', '[\"1\",\"2\"]', '[]', 1, 1.000, 85.00, 85.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 18:43:58', '2026-02-18 18:43:58'),
(14, 7, 2, '[\"4\"]', 'Necklace', '[\"3\"]', '[]', 1, 1.500, 20.00, 30.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 18:43:58', '2026-02-18 18:43:58'),
(21, 8, 1, '[\"4\",\"2\"]', 'Necklace, Pendant', '[\"1\"]', '[{\"process_id\":1,\"process_name\":\"Rhodium Black\",\"rate\":60}]', 1, 1.500, 60.00, 22590.00, 'uploads/challan_images/1771442686_4c93d2c549c49150e58c.png', 3.000, NULL, '22K', 15000.00, 1.500, 22500.00, NULL, '2026-02-18 19:45:46', '2026-02-18 19:45:46'),
(22, 8, 2, '[\"1\"]', 'Ring', '[\"3\"]', '[{\"process_id\":3,\"process_name\":\"Rhodium White\",\"rate\":20}]', 1, 2.000, 20.00, 345040.00, 'uploads/challan_images/1771442686_e5c70877f84a0db905f9.png', 25.000, NULL, '22K', 15000.00, 23.000, 345000.00, NULL, '2026-02-18 19:45:46', '2026-02-18 19:45:46'),
(25, 9, 1, '[\"1\"]', 'Ring', '[\"1\"]', '[{\"process_id\":1,\"process_name\":\"Rhodium Black\",\"rate\":60}]', 1, 1.500, 60.00, 7590.00, '', 2.000, NULL, '22K', 15000.00, 0.500, 7500.00, NULL, '2026-02-19 19:00:11', '2026-02-19 19:00:11'),
(26, 9, 2, '[\"3\"]', 'Earring', '[\"2\"]', '[{\"process_id\":2,\"process_name\":\"Rhodium Pink\",\"rate\":25}]', 1, 2.000, 25.00, 50.00, '', 0.000, NULL, '', 0.00, 0.000, 0.00, NULL, '2026-02-19 19:00:11', '2026-02-19 19:00:11');

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
(1, 'System Administrator', 'System Administrator', 'Gold Manufacturing', 'System HQ', NULL, 'System City', 1, '000000', 'System Admin', 'admin@gmail.com', '9999999999', NULL, NULL, NULL, 'SYS-', 'CH-', 18.00, 0.00, 4, 'Y-m-d', 'Asia/Kolkata', 'Active', 8, 11, '2026-02-08 16:31:03', '2026-02-19 19:00:50', 0);

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

--
-- Dumping data for table `deliveries`
--

INSERT INTO `deliveries` (`id`, `company_id`, `invoice_id`, `assigned_to`, `assigned_by`, `assigned_date`, `expected_delivery_date`, `actual_delivery_date`, `delivery_status`, `delivery_address`, `delivery_contact_name`, `customer_contact_mobile`, `delivery_notes`, `failed_reason`, `delivery_proof_photo`, `delivered_timestamp`, `created_at`, `updated_at`, `is_deleted`) VALUES
(1, 1, 13, 5, 2, '2026-02-14 13:22:36', '2026-02-14', '2026-02-14', 'Delivered', 'Address Not Available', 'Alpesh Pansheriya', '8000259032', '', NULL, '1771076752_60a7bc7ec3b40e90f091.png', '2026-02-14 13:45:52', '2026-02-14 13:22:36', '2026-02-14 13:45:52', 0);

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

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `company_id`, `invoice_number`, `invoice_type`, `invoice_date`, `due_date`, `account_id`, `cash_customer_id`, `billing_address`, `shipping_address`, `reference_number`, `challan_ids`, `subtotal`, `tax_rate`, `tax_amount`, `cgst_amount`, `sgst_amount`, `igst_amount`, `grand_total`, `total_paid`, `amount_due`, `invoice_status`, `payment_status`, `gold_adjustment_applied`, `gold_adjustment_date`, `gold_adjustment_amount`, `gold_rate_used`, `notes`, `terms_conditions`, `created_by`, `created_at`, `updated_by`, `updated_at`, `is_deleted`) VALUES
(10, 1, 'SYS-0001', 'Cash Invoice', '2026-02-13', '0000-00-00', NULL, 6, NULL, NULL, NULL, NULL, 56.25, 3.00, 0.00, 0.00, 0.00, 1.69, 56.25, 0.00, 56.25, 'Posted', 'Pending', 0, NULL, NULL, NULL, NULL, '', 2, '2026-02-13 17:58:31', 2, '2026-02-14 12:40:00', 0),
(11, 1, 'SYS-0002', 'Cash Invoice', '2026-02-13', '0000-00-00', NULL, 7, '', '', '#10', NULL, 187.50, 3.00, 0.00, 0.00, 0.00, 2.18, 187.50, 0.00, 187.50, 'Posted', 'Pending', 0, NULL, NULL, NULL, 'Notes 1', 'Terms & Conditions goes here 1', 2, '2026-02-13 18:47:07', 2, '2026-02-14 12:40:04', 0),
(12, 1, 'SYS-0003', 'Cash Invoice', '2026-02-13', '0000-00-00', NULL, 8, NULL, NULL, NULL, NULL, 60.00, 3.00, 0.00, 0.00, 0.00, 1.80, 60.00, 0.00, 60.00, 'Posted', 'Pending', 0, NULL, NULL, NULL, NULL, '', 2, '2026-02-13 20:51:47', 2, '2026-02-14 12:40:08', 1),
(13, 1, 'SYS-0004', 'Cash Invoice', '2026-02-14', '2026-02-28', NULL, 10, NULL, NULL, '#11', NULL, 214.50, 3.00, 0.00, 0.00, 0.00, 6.44, 214.50, 214.50, 0.00, 'Delivered', 'Paid', 0, NULL, NULL, NULL, 'Pay your dues within 15 days of time', 'New Terms & Conditions here', 2, '2026-02-14 09:11:57', 2, '2026-02-14 13:45:52', 0),
(14, 1, 'SYS-0005', 'Cash Invoice', '2026-02-14', '0000-00-00', NULL, 11, NULL, NULL, NULL, NULL, 120.00, 18.00, 21.60, 10.80, 10.80, 0.00, 141.60, 0.00, 141.60, 'Posted', 'Pending', 0, NULL, NULL, NULL, NULL, '', 2, '2026-02-14 09:26:02', 2, '2026-02-14 12:40:16', 0),
(21, 1, 'SYS-0006', 'Accounts Invoice', '2026-02-14', NULL, 3, NULL, 'Minibazaar , Surat - 395006', NULL, NULL, '[\"1\",\"2\"]', 318.50, 18.00, 57.33, 28.67, 28.66, 0.00, 375.83, 0.00, 375.83, 'Posted', 'Pending', 0, NULL, NULL, NULL, NULL, NULL, 2, '2026-02-14 11:03:07', 2, '2026-02-14 12:40:19', 0),
(26, 1, 'SYS-0007', 'Cash Invoice', '2026-02-19', '2026-02-28', NULL, 7, NULL, NULL, '#1', NULL, 156.78, 18.00, 28.22, 14.11, 14.11, 0.00, 185.00, 0.00, 185.00, 'Draft', 'Pending', 0, NULL, NULL, NULL, 'Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here Notes goes here ', 'Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions Terms & Conditions ', 2, '2026-02-19 18:49:24', 2, '2026-02-19 18:49:24', 0),
(27, 1, 'SYS-0008', 'Accounts Invoice', '2026-02-20', NULL, 3, NULL, 'Minibazaar , Surat - 395006', NULL, NULL, '[\"9\"]', 6474.58, 18.00, 1165.42, 582.71, 582.71, 0.00, 7640.00, 0.00, 7640.00, 'Draft', 'Pending', 0, NULL, NULL, NULL, NULL, NULL, 2, '2026-02-19 19:00:50', 2, '2026-02-19 19:00:50', 0);

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

--
-- Dumping data for table `invoice_lines`
--

INSERT INTO `invoice_lines` (`id`, `invoice_id`, `line_number`, `source_challan_id`, `source_challan_line_id`, `product_ids`, `product_name`, `process_ids`, `process_prices`, `quantity`, `weight`, `rate`, `amount`, `gold_weight`, `gold_fine_weight`, `gold_purity`, `original_gold_weight`, `adjusted_gold_weight`, `gold_adjustment_amount`, `line_notes`, `created_at`, `updated_at`) VALUES
(1, 10, 1, NULL, NULL, '[\"4\"]', NULL, '[\"2\",\"3\"]', NULL, 1, 0.250, 45.00, 11.25, 0.000, NULL, NULL, 0.000, 0.000, 0.00, NULL, '2026-02-13 17:58:31', '2026-02-13 17:58:31'),
(2, 10, 2, NULL, NULL, '[\"3\",\"2\"]', NULL, '[\"1\"]', NULL, 1, 0.750, 60.00, 45.00, 0.000, NULL, NULL, 0.000, 0.000, 0.00, NULL, '2026-02-13 17:58:31', '2026-02-13 17:58:31'),
(6, 11, 1, NULL, NULL, '[\"4\"]', NULL, '[\"1\",\"2\"]', NULL, 1, 1.500, 85.00, 127.50, 0.000, NULL, NULL, 0.000, 0.000, 0.00, NULL, '2026-02-14 09:06:03', '2026-02-14 09:06:03'),
(7, 11, 2, NULL, NULL, '[\"3\",\"2\",\"1\"]', NULL, '[\"1\"]', NULL, 1, 1.000, 60.00, 60.00, 0.000, NULL, NULL, 0.000, 0.000, 0.00, NULL, '2026-02-14 09:06:03', '2026-02-14 09:06:03'),
(8, 13, 1, NULL, NULL, '[\"4\"]', NULL, '[\"2\",\"3\"]', NULL, 1, 1.700, 45.00, 76.50, 0.000, NULL, NULL, 0.000, 0.000, 0.00, NULL, '2026-02-14 09:11:57', '2026-02-14 09:11:57'),
(9, 13, 2, NULL, NULL, '[\"3\",\"1\"]', NULL, '[\"1\"]', NULL, 1, 2.300, 60.00, 138.00, 0.000, NULL, NULL, 0.000, 0.000, 0.00, NULL, '2026-02-14 09:11:57', '2026-02-14 09:11:57'),
(10, 14, 1, NULL, NULL, '[\"3\"]', NULL, '[\"1\"]', NULL, 1, 2.000, 60.00, 120.00, 0.000, NULL, NULL, 0.000, 0.000, 0.00, NULL, '2026-02-14 09:26:02', '2026-02-14 09:26:02'),
(11, 21, 1, 1, NULL, '[\"1\",\"3\",\"2\"]', NULL, '[\"2\",\"1\",\"3\"]', NULL, 2, 2.200, 105.00, 66.00, 0.000, NULL, '', 0.000, 0.000, 0.00, 'Consolidated from Challan CH-0003 (Lines: 5, 6)', '2026-02-14 11:03:07', '2026-02-14 11:03:07'),
(12, 21, 2, 2, NULL, '[\"2\",\"1\",\"4\"]', NULL, '[\"3\",\"1\",\"2\"]', NULL, 2, 4.500, 105.00, 252.50, 0.000, NULL, '', 0.000, 0.000, 0.00, 'Consolidated from Challan CH-0004 (Lines: 7, 8)', '2026-02-14 11:03:07', '2026-02-14 11:03:07'),
(21, 26, 1, NULL, NULL, '[\"3\"]', NULL, '[\"1\",\"3\"]', NULL, 1, 2.000, 80.00, 160.00, 0.000, NULL, NULL, 0.000, 0.000, 0.00, NULL, '2026-02-19 18:49:24', '2026-02-19 18:49:24'),
(22, 26, 2, NULL, NULL, '[\"4\"]', NULL, '[\"2\"]', NULL, 1, 1.000, 25.00, 25.00, 0.000, NULL, NULL, 0.000, 0.000, 0.00, NULL, '2026-02-19 18:49:24', '2026-02-19 18:49:24'),
(23, 27, 1, 9, NULL, '[\"1\",\"3\"]', NULL, '[\"1\",\"2\"]', NULL, 2, 3.500, 85.00, 7640.00, 2.000, NULL, '22K', 0.000, 0.000, 0.00, 'Consolidated from Challan CH-0011 (Lines: 25, 26)', '2026-02-19 19:00:50', '2026-02-19 19:00:50');

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
(5, 1, NULL, 7, '2026-02-19', 'invoice', 26, 'SYS-0007', 'Invoice Generated: SYS-0007', 185.00, 0.00, 185.00, '2026-02-19 18:49:24'),
(6, 1, 3, NULL, '2026-02-20', 'invoice', 27, 'SYS-0008', 'Invoice Generated: SYS-0008', 7640.00, 0.00, 7640.00, '2026-02-19 19:00:50');

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
(36, '2026-02-19-000001', 'App\\Database\\Migrations\\AddGoldAdjustmentToChallanLines', 'default', 'App', 1771441631, 17);

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

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `company_id`, `payment_number`, `invoice_id`, `customer_type`, `account_id`, `cash_customer_id`, `payment_date`, `payment_amount`, `payment_mode`, `cheque_number`, `cheque_date`, `bank_name`, `transaction_reference`, `notes`, `received_by`, `created_at`, `updated_at`, `is_deleted`) VALUES
(1, 1, 'PAY-0001', 13, 'Cash', NULL, 10, '2026-02-14', 100.00, 'Cash', '', '0000-00-00', '', '', '', 2, '2026-02-14 12:54:22', '2026-02-14 12:54:22', 0),
(2, 1, 'PAY-0002', 13, 'Cash', NULL, 10, '2026-02-14', 114.50, 'Cash', '', '0000-00-00', '', '', '', 2, '2026-02-14 12:55:31', '2026-02-14 12:56:33', 1),
(3, 1, 'PAY-0003', 13, 'Cash', NULL, 10, '2026-02-14', 114.50, 'Cash', '', '0000-00-00', '', '', '', 2, '2026-02-14 13:19:12', '2026-02-14 13:19:12', 0);

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
(2, 1, 'superadmin', 'admin@gmail.com', '$2y$10$3lD2hiugSlildxvjTeH9bue.5rQEqtidB6krrpytoJk4hpXfKZ/WC', 'System Administrator', '9999999999', NULL, NULL, NULL, NULL, NULL, 'Active', 0, '2026-02-19 16:56:34', NULL, '2026-02-08 16:31:03', '2026-02-19 16:56:34', 0),
(4, 1, 'parinpatel', 'parinwork@gmail.com', '$2y$10$TBdnobbaIGwuvdZuPhT5yu7U1hVKWCcVbpSpmwMNhINEdFYkUsiNa', 'Parin Patel', '9586969009', NULL, NULL, NULL, NULL, NULL, 'Active', 0, '2026-02-10 20:12:17', NULL, '2026-02-10 20:11:17', '2026-02-10 20:12:17', 0),
(5, 1, 'parindelivery', 'parindelivery@gmail.com', '$2y$10$JVSHEEiKOf62a3cxPmxjL.WmzMc.yCkRfH.oo/96cxRztNIcFiLA.', 'Parin Delivery', '9586969119', NULL, NULL, NULL, NULL, NULL, 'Active', 0, '2026-02-14 13:22:58', NULL, '2026-02-14 13:22:23', '2026-02-14 13:22:58', 0);

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
(6, 5, 5, '2026-02-14 13:22:23', NULL);

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
  ADD UNIQUE KEY `uk_company_payment_number` (`company_id`,`payment_number`),
  ADD KEY `payments_received_by_foreign` (`received_by`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `cash_customer_id` (`cash_customer_id`),
  ADD KEY `payment_date` (`payment_date`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `cash_customers`
--
ALTER TABLE `cash_customers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `challans`
--
ALTER TABLE `challans`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `challan_lines`
--
ALTER TABLE `challan_lines`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `gold_rates`
--
ALTER TABLE `gold_rates`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `invoice_lines`
--
ALTER TABLE `invoice_lines`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `ledger_entries`
--
ALTER TABLE `ledger_entries`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
