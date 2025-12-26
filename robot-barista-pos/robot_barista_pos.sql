-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 20, 2025 at 05:24 AM
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
-- Database: `robot_barista_pos`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Coffee', 'Hot and cold coffee beverages', 1, 1, '2025-11-23 11:18:02', '2025-11-23 11:18:02'),
(2, 'Tea', 'Various tea selections', 2, 1, '2025-11-23 11:18:02', '2025-11-23 11:18:02'),
(3, 'Drinks', 'Refreshing beverages', 3, 1, '2025-11-23 11:18:02', '2025-11-23 11:18:02'),
(4, 'Bakery', 'Fresh baked goods', 4, 1, '2025-11-23 11:18:02', '2025-11-23 11:18:02'),
(5, 'Snacks', 'Light snacks and treats', 5, 1, '2025-11-23 11:18:02', '2025-11-23 11:18:02');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(200) DEFAULT 'Walk-In Customer',
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `total_orders` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `phone`, `email`, `total_orders`, `created_at`) VALUES
(1, 'John Doe', '+855 12 345 678', 'john@example.com', 5, '2025-11-23 11:18:29'),
(2, 'Jane Smith', '+855 98 765 432', 'jane@example.com', 3, '2025-11-23 11:18:29'),
(3, 'Walk-In Customer', NULL, NULL, 100, '2025-11-23 11:18:29');

-- --------------------------------------------------------

--
-- Table structure for table `modifiers`
--

CREATE TABLE `modifiers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('size','topping','sugar','ice') NOT NULL,
  `price_usd` decimal(10,2) DEFAULT 0.00,
  `price_khr` decimal(10,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `modifiers`
--

INSERT INTO `modifiers` (`id`, `name`, `type`, `price_usd`, `price_khr`, `is_active`, `created_at`) VALUES
(1, 'Small', 'size', 0.00, 0.00, 1, '2025-11-23 11:18:02'),
(2, 'Medium', 'size', 0.50, 2050.00, 1, '2025-11-23 11:18:02'),
(3, 'Large', 'size', 1.00, 4100.00, 1, '2025-11-23 11:18:02'),
(4, 'No Sugar', 'sugar', 0.00, 0.00, 1, '2025-11-23 11:18:02'),
(5, 'Less Sugar', 'sugar', 0.00, 0.00, 1, '2025-11-23 11:18:02'),
(6, 'Normal Sugar', 'sugar', 0.00, 0.00, 1, '2025-11-23 11:18:02'),
(7, 'Extra Sugar', 'sugar', 0.00, 0.00, 1, '2025-11-23 11:18:02'),
(8, 'No Ice', 'ice', 0.00, 0.00, 1, '2025-11-23 11:18:02'),
(9, 'Less Ice', 'ice', 0.00, 0.00, 1, '2025-11-23 11:18:02'),
(10, 'Normal Ice', 'ice', 0.00, 0.00, 1, '2025-11-23 11:18:02'),
(11, 'Pearl', 'topping', 0.50, 2050.00, 1, '2025-11-23 11:18:02'),
(12, 'Jelly', 'topping', 0.50, 2050.00, 1, '2025-11-23 11:18:02'),
(13, 'Cream', 'topping', 0.75, 3075.00, 1, '2025-11-23 11:18:02');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(200) DEFAULT 'Walk-In Customer',
  `currency` enum('USD','KHR') DEFAULT 'USD',
  `subtotal` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('KHQR','Cash') NOT NULL,
  `payment_status` enum('Pending','Paid','Failed') DEFAULT 'Pending',
  `order_status` enum('Pending','Preparing','Ready','Completed','Cancelled') DEFAULT 'Pending',
  `receipt_printed` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `customer_id`, `customer_name`, `currency`, `subtotal`, `tax_amount`, `total_amount`, `payment_method`, `payment_status`, `order_status`, `receipt_printed`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'ORD-20241123-0001', 1, 'John Doe', 'USD', 10.00, 1.00, 11.00, 'KHQR', 'Paid', 'Completed', 0, NULL, '2025-11-23 10:18:29', '2025-11-23 11:18:29'),
(2, 'ORD-20241123-0002', 2, 'Jane Smith', 'USD', 15.50, 1.55, 17.05, 'Cash', 'Paid', 'Completed', 0, NULL, '2025-11-23 09:18:29', '2025-11-23 11:18:29'),
(3, 'ORD-20241123-0003', 3, 'Walk-In Customer', 'KHR', 41000.00, 4100.00, 45100.00, 'KHQR', 'Paid', 'Completed', 0, NULL, '2025-11-23 08:18:29', '2025-11-23 11:18:29'),
(4, 'ORD-20241122-0001', 3, 'Walk-In Customer', 'USD', 8.50, 0.85, 9.35, 'Cash', 'Paid', 'Completed', 0, NULL, '2025-11-22 11:18:29', '2025-11-23 11:18:29'),
(5, 'ORD-20241122-0002', 1, 'John Doe', 'USD', 12.00, 1.20, 13.20, 'KHQR', 'Paid', 'Completed', 0, NULL, '2025-11-22 11:18:29', '2025-11-23 11:18:29'),
(6, 'ORD-20251123-2431', NULL, 'Walk-In Customer', 'USD', 18.75, 1.88, 20.63, 'KHQR', 'Pending', 'Pending', 0, NULL, '2025-11-23 11:58:42', '2025-11-23 11:58:42'),
(7, 'ORD-20251123-7398', NULL, 'Walk-In Customer', 'USD', 2.75, 0.28, 3.03, 'KHQR', 'Pending', 'Pending', 0, NULL, '2025-11-23 12:45:11', '2025-11-23 12:45:11'),
(8, 'ORD-20251123-2884', NULL, 'Walk-In Customer', 'USD', 0.01, 0.00, 0.01, 'KHQR', 'Pending', 'Pending', 0, NULL, '2025-11-23 13:03:39', '2025-11-23 13:03:39'),
(9, 'ORD-20251123-8369', NULL, 'Walk-In Customer', 'KHR', 10.00, 0.80, 10.80, 'KHQR', 'Pending', 'Pending', 0, NULL, '2025-11-23 13:05:32', '2025-11-23 13:05:32'),
(10, 'ORD-20251123-6759', NULL, 'Walk-In Customer', 'KHR', 10.00, 0.80, 10.80, 'KHQR', '', 'Cancelled', 0, NULL, '2025-11-23 13:07:05', '2025-11-23 13:07:07'),
(11, 'ORD-20251123-3783', NULL, 'Walk-In Customer', 'USD', 0.01, 0.00, 0.01, 'KHQR', '', 'Cancelled', 0, NULL, '2025-11-23 13:07:17', '2025-11-23 13:07:20'),
(12, 'ORD-20251123-8635', NULL, 'Walk-In Customer', 'USD', 0.01, 0.00, 0.01, 'KHQR', '', 'Cancelled', 0, NULL, '2025-11-23 13:07:25', '2025-11-23 13:07:26'),
(13, 'ORD-20251123-5483', NULL, 'Walk-In Customer', 'USD', 2.75, 0.22, 2.97, 'KHQR', '', 'Cancelled', 0, NULL, '2025-11-23 13:07:29', '2025-11-23 13:07:30'),
(14, 'ORD-20251123-9577', NULL, 'Walk-In Customer', 'KHR', 100.00, 0.00, 100.00, 'KHQR', '', 'Cancelled', 0, NULL, '2025-11-23 13:11:05', '2025-11-23 13:11:07'),
(15, 'ORD-20251123-3414', NULL, 'Walk-In Customer', 'KHR', 100.00, 0.00, 100.00, 'KHQR', '', 'Cancelled', 0, NULL, '2025-11-23 13:11:11', '2025-11-23 13:11:52'),
(16, 'ORD-20251123-3262', NULL, 'Walk-In Customer', 'USD', 0.01, 0.00, 0.01, 'KHQR', 'Paid', 'Preparing', 0, NULL, '2025-11-23 13:16:47', '2025-11-23 13:17:02'),
(17, 'ORD-20251123-9030', NULL, 'Walk-In Customer', 'USD', 0.01, 0.00, 0.01, 'KHQR', '', 'Cancelled', 0, NULL, '2025-11-23 13:50:57', '2025-11-23 13:51:28'),
(18, 'ORD-20251123-2491', NULL, 'Walk-In Customer', 'USD', 0.01, 0.00, 0.01, 'KHQR', 'Paid', 'Preparing', 0, NULL, '2025-11-23 13:51:57', '2025-11-23 13:52:15'),
(19, 'ORD-20251123-1446', NULL, 'Walk-In Customer', 'USD', 0.01, 0.00, 0.01, 'KHQR', 'Paid', 'Preparing', 0, NULL, '2025-11-23 13:54:45', '2025-11-23 13:54:57'),
(20, 'ORD-20251123-4853', NULL, 'Walk-In Customer', 'USD', 0.01, 0.00, 0.01, 'KHQR', 'Paid', 'Preparing', 0, NULL, '2025-11-23 13:55:39', '2025-11-23 13:55:51'),
(21, 'ORD-20251123-3498', NULL, 'Walk-In Customer', 'USD', 0.01, 0.00, 0.01, 'KHQR', '', 'Cancelled', 0, NULL, '2025-11-23 14:05:57', '2025-11-23 14:05:58'),
(22, 'ORD-20251123-3878', NULL, 'Walk-In Customer', 'USD', 0.01, 0.00, 0.01, 'KHQR', 'Paid', 'Preparing', 0, NULL, '2025-11-23 14:06:09', '2025-11-23 14:06:38'),
(23, 'ORD-20251124-6639', NULL, 'Walk-In Customer', 'USD', 0.02, 0.00, 0.02, 'KHQR', '', 'Cancelled', 0, NULL, '2025-11-24 12:26:27', '2025-11-24 12:28:28'),
(24, 'ORD-20251124-2625', NULL, 'Walk-In Customer', 'USD', 0.01, 0.00, 0.01, 'KHQR', '', 'Cancelled', 0, NULL, '2025-11-24 12:31:49', '2025-11-24 12:31:52'),
(25, 'ORD-20251124-0408', NULL, 'Walk-In Customer', 'USD', 0.01, 0.00, 0.01, 'KHQR', '', 'Cancelled', 0, NULL, '2025-11-24 12:32:30', '2025-11-24 12:34:30'),
(26, 'ORD-20251124-5623', NULL, 'Walk-In Customer', 'USD', 0.01, 0.00, 0.01, 'KHQR', 'Pending', 'Pending', 0, NULL, '2025-11-24 12:35:38', '2025-11-24 12:35:38'),
(27, 'ORD-20251124-8520', NULL, 'Walk-In Customer', 'USD', 0.01, 0.00, 0.01, 'KHQR', '', 'Cancelled', 0, 'Customer paid in USD: $0.01 | MD5: f7f12ad154528fb44f0c6dc97c0f7890', '2025-11-24 12:48:34', '2025-11-24 12:48:38'),
(28, 'ORD-20251124-4279', NULL, 'Walk-In Customer', 'USD', 2.82, 0.00, 2.82, 'KHQR', '', 'Cancelled', 0, 'Customer paid in KHR: ៛11,275 (Exchange rate: 4,000) | MD5: 9f8f078664f16d93f68192a5baf728a4', '2025-11-24 12:49:00', '2025-11-24 12:49:01'),
(29, 'ORD-20251124-5036', NULL, 'Walk-In Customer', 'USD', 82.00, 0.00, 82.00, 'KHQR', '', 'Cancelled', 0, 'Customer paid in KHR: ៛8,200 (Exchange rate: 100) | MD5: 23475acd9b6f5b8a146509bba2ddc788', '2025-11-24 12:57:44', '2025-11-24 12:57:45'),
(30, 'ORD-20251124-4271', NULL, 'Walk-In Customer', 'USD', 82.00, 0.00, 82.00, 'KHQR', 'Pending', 'Pending', 0, 'Customer paid in KHR: ៛8,200 (Exchange rate: 100) | MD5: c4b93286b833c85ec316d7e2f2663c88', '2025-11-24 12:59:51', '2025-11-24 12:59:51'),
(31, 'ORD-20251124-6797', NULL, 'Walk-In Customer', 'USD', 0.05, 0.00, 0.05, 'KHQR', '', 'Cancelled', 0, 'Customer paid in KHR: ៛200 (Exchange rate: 4,000) | MD5: f611b155c48797c92a43e98bab438785', '2025-11-24 13:02:40', '2025-11-24 13:03:04'),
(32, 'ORD-20251124-8897', NULL, 'Walk-In Customer', 'USD', 0.10, 0.00, 0.10, 'KHQR', 'Paid', 'Preparing', 0, 'Customer paid in KHR: ៛100 (Exchange rate: 1,000) | MD5: e060f0885c3c5d2b2f4f826df7523fd7\nTransaction ID: N/A | Bank: N/A | Time: 2025-11-24 14:03:40', '2025-11-24 13:03:32', '2025-11-24 13:03:40'),
(33, 'ORD-20251125-4454', NULL, 'Walk-In Customer', 'USD', 0.10, 0.00, 0.10, 'KHQR', '', 'Cancelled', 0, 'Customer paid in USD: $0.10 | MD5: 46b783b9b51705693704650edc59eec2', '2025-11-25 11:40:06', '2025-11-25 11:40:10'),
(34, 'ORD-20251125-0289', NULL, 'Walk-In Customer', 'USD', 0.60, 0.00, 0.60, 'KHQR', '', 'Cancelled', 0, 'Customer paid in USD: $0.60 | MD5: a8e8dfced3ea664fcdc6ae691bfb7aa2', '2025-11-25 11:42:09', '2025-11-25 11:42:12'),
(35, 'ORD-20251202-8072', NULL, 'Walk-In Customer', 'USD', 10.00, 0.00, 10.00, 'KHQR', '', 'Cancelled', 0, 'Customer paid in USD: $10.00 | MD5: 74f8581e6d335675e370465e67fa271d', '2025-12-02 08:18:01', '2025-12-02 08:18:06'),
(36, 'ORD-20251202-6841', NULL, 'Walk-In Customer', 'USD', 10.00, 0.00, 10.00, 'KHQR', '', 'Cancelled', 0, 'Customer paid in USD: $10.00 | MD5: 188de3f297aed67f75f69c6171d48381', '2025-12-02 08:18:36', '2025-12-02 08:18:40'),
(37, 'ORD-20251202-3267', NULL, 'Walk-In Customer', 'USD', 10.00, 0.00, 10.00, 'KHQR', '', 'Cancelled', 0, 'Customer paid in KHR: ៛41,000 (Exchange rate: 4,100) | MD5: 7a1a28afe4c1a819137e2fcbc52cc8de', '2025-12-02 08:19:42', '2025-12-02 08:19:47'),
(38, 'ORD-20251202-6818', NULL, 'Walk-In Customer', 'USD', 0.01, 0.00, 0.01, 'KHQR', 'Paid', 'Preparing', 0, 'Customer paid in USD: $0.01 | MD5: f8c116848e806b93364dc8f7b005d8b3\nTransaction ID: N/A | Bank: N/A | Time: 2025-12-02 09:23:27', '2025-12-02 08:23:01', '2025-12-02 08:23:27'),
(39, 'ORD-20251215-7048', NULL, 'Walk-In Customer', 'USD', 0.01, 0.00, 0.01, 'KHQR', 'Paid', 'Preparing', 0, 'Customer paid in KHR: ៛100 (Exchange rate: 10,000) | MD5: 9a864848edf952bf2642c0300bad0d9b\nTransaction ID: N/A | Bank: N/A | Time: 2025-12-15 11:50:57', '2025-12-15 10:50:30', '2025-12-15 10:50:57'),
(40, 'ORD-20251215-9995', NULL, 'Walk-In Customer', 'USD', 0.01, 0.00, 0.01, 'KHQR', '', 'Cancelled', 0, 'Customer paid in KHR: ៛100 (Exchange rate: 10,000) | MD5: 9f0b48098a2710478cdf7774ebd2d4dc', '2025-12-15 11:21:11', '2025-12-15 11:22:02'),
(41, 'ORD-20251215-0374', NULL, 'Walk-In Customer', 'USD', 0.01, 0.00, 0.01, 'KHQR', '', 'Cancelled', 0, 'Customer paid in USD: $0.01 | MD5: 915bddebd8b675234d6c71e62a5f78c2', '2025-12-15 11:25:11', '2025-12-15 11:25:57'),
(42, 'ORD-20251215-6661', NULL, 'Walk-In Customer', 'USD', 0.01, 0.00, 0.01, 'KHQR', '', 'Cancelled', 0, 'Customer paid in USD: $0.01 | MD5: 1253d9665be004f16129d1903acdd7e2', '2025-12-15 11:30:43', '2025-12-15 11:30:46'),
(43, 'ORD-20251215-9919', NULL, 'seavpeav', 'USD', 0.01, 0.00, 0.01, 'KHQR', 'Paid', 'Preparing', 0, 'Customer paid in KHR: ៛100 (Exchange rate: 10,000) | MD5: 95f07859626ef1674262d92f4f7f7757\nTransaction ID: N/A | Bank: N/A | Time: 2025-12-15 13:06:08', '2025-12-15 12:05:45', '2025-12-15 12:06:08'),
(44, 'ORD-20251220-7592', NULL, 'Walk-In Customer', 'USD', 0.01, 0.00, 0.01, 'KHQR', '', 'Cancelled', 0, 'Customer paid in KHR: ៛100 (Exchange rate: 10,000) | MD5: 17300ce34bdf5202e21c17abb6fbbf08', '2025-12-20 04:04:09', '2025-12-20 04:04:10');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `modifiers_json` text DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `unit_price`, `modifiers_json`, `subtotal`) VALUES
(1, 1, 2, 'Cappuccino', 2, 3.50, '{\"size\":\"Medium\"}', 7.00),
(2, 1, 9, 'Croissant', 1, 2.50, '{}', 2.50),
(3, 2, 3, 'Latte', 2, 3.75, '{\"size\":\"Large\"}', 7.50),
(4, 2, 6, 'Milk Tea', 2, 3.00, '{\"size\":\"Medium\"}', 6.00),
(5, 3, 1, 'Espresso', 1, 10250.00, '{\"size\":\"Small\"}', 10250.00),
(6, 3, 11, 'Cookie', 2, 6150.00, '{}', 12300.00),
(7, 4, 4, 'Americano', 2, 2.75, '{\"size\":\"Medium\"}', 5.50),
(8, 5, 2, 'Cappuccino', 3, 3.50, '{\"size\":\"Large\"}', 10.50),
(9, 6, 4, 'Americano', 5, 3.75, '{\"size\":\"Large\"}', 18.75),
(10, 7, 4, 'Americano', 1, 2.75, '{\"size\":\"Small\"}', 2.75),
(11, 8, 4, 'Americano', 1, 0.01, '{\"size\":\"Small\"}', 0.01),
(12, 9, 4, 'Americano', 1, 10.00, '{\"size\":\"Small\"}', 10.00),
(13, 10, 4, 'Americano', 1, 10.00, '{\"size\":\"Small\"}', 10.00),
(14, 11, 4, 'Americano', 1, 0.01, '{\"size\":\"Small\"}', 0.01),
(15, 12, 4, 'Americano', 1, 0.01, '{\"size\":\"Small\"}', 0.01),
(16, 13, 19, 'Bagel', 1, 2.75, '{\"size\":\"Small\"}', 2.75),
(17, 14, 4, 'Americano', 1, 100.00, '{\"size\":\"Small\"}', 100.00),
(18, 15, 4, 'Americano', 1, 100.00, '{\"size\":\"Small\"}', 100.00),
(19, 16, 4, 'Americano', 1, 0.01, '{\"size\":\"Small\"}', 0.01),
(20, 17, 4, 'Americano', 1, 0.01, '{\"size\":\"Small\"}', 0.01),
(21, 18, 4, 'Americano', 1, 0.01, '{\"size\":\"Small\"}', 0.01),
(22, 19, 4, 'Americano', 1, 0.01, '{\"size\":\"Small\"}', 0.01),
(23, 20, 4, 'Americano', 1, 0.01, '{\"size\":\"Small\"}', 0.01),
(24, 21, 4, 'Americano', 1, 0.01, '{\"size\":\"Small\"}', 0.01),
(25, 22, 4, 'Americano', 1, 0.01, '{\"size\":\"Small\"}', 0.01),
(26, 23, 4, 'Americano', 2, 0.01, '{\"size\":\"Small\"}', 0.02),
(27, 24, 4, 'Americano', 1, 0.01, '{\"size\":\"Small\"}', 0.01),
(28, 25, 4, 'Americano', 1, 0.01, '{\"size\":\"Small\"}', 0.01),
(29, 26, 4, 'Americano', 1, 0.01, '{\"size\":\"Small\"}', 0.01),
(30, 27, 4, 'Americano', 1, 0.01, '{\"size\":\"Small\"}', 0.01),
(31, 28, 19, 'Bagel', 1, 2.82, '{\"size\":\"Small\"}', 2.82),
(32, 29, 12, 'Brownie', 1, 82.00, '{\"size\":\"Small\"}', 82.00),
(33, 30, 12, 'Brownie', 1, 82.00, '{\"size\":\"Small\"}', 82.00),
(34, 31, 4, 'Americano', 1, 0.05, '{\"size\":\"Small\"}', 0.05),
(35, 32, 4, 'Americano', 1, 0.10, '{\"size\":\"Small\"}', 0.10),
(36, 33, 4, 'Americano', 1, 0.10, '{\"size\":\"Small\"}', 0.10),
(37, 34, 4, 'Americano', 1, 0.60, '{\"size\":\"Medium\"}', 0.60),
(38, 35, 4, 'Americano', 1, 10.00, '{\"size\":\"Small\"}', 10.00),
(39, 36, 4, 'Americano', 1, 10.00, '{\"size\":\"Small\"}', 10.00),
(40, 37, 4, 'Americano', 1, 10.00, '{\"size\":\"Small\"}', 10.00),
(41, 38, 4, 'Americano', 1, 0.01, '{\"size\":\"Small\"}', 0.01),
(42, 39, 4, 'Americano', 1, 0.01, '{\"size\":\"Small\"}', 0.01),
(43, 40, 4, 'Americano', 1, 0.01, '{\"size\":\"Small\"}', 0.01),
(44, 41, 4, 'Americano', 1, 0.01, '{\"size\":\"Small\"}', 0.01),
(45, 42, 4, 'Americano', 1, 0.01, '{\"size\":\"Small\"}', 0.01),
(46, 43, 4, 'Americano', 1, 0.01, '{\"size\":\"Small\"}', 0.01),
(47, 44, 4, 'Americano', 1, 0.01, '{\"size\":\"Small\"}', 0.01);

-- --------------------------------------------------------

--
-- Table structure for table `print_logs`
--

CREATE TABLE `print_logs` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `print_type` enum('receipt','report') NOT NULL,
  `print_status` enum('success','failed') NOT NULL,
  `error_message` text DEFAULT NULL,
  `printed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `print_logs`
--

INSERT INTO `print_logs` (`id`, `order_id`, `print_type`, `print_status`, `error_message`, `printed_at`) VALUES
(1, 1, 'receipt', 'success', NULL, '2025-11-23 10:18:29'),
(2, 2, 'receipt', 'success', NULL, '2025-11-23 09:18:29'),
(3, 3, 'receipt', 'success', NULL, '2025-11-23 08:18:29');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price_usd` decimal(10,2) NOT NULL,
  `price_khr` decimal(10,2) NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `has_modifiers` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `image`, `price_usd`, `price_khr`, `is_available`, `has_modifiers`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'Espresso', 'Strong and bold coffee', '1763901074_d1973eff377fb3477c8be70b5151f87b.jpg', 2.50, 10250.00, 1, 1, 0, '2025-11-23 11:18:02', '2025-11-23 12:31:14'),
(2, 1, 'Cappuccino', 'Espresso with steamed milk foam', '1763901019_cappuccino.jpg', 3.50, 14350.00, 1, 1, 0, '2025-11-23 11:18:02', '2025-11-23 12:30:19'),
(3, 1, 'Latte', 'Smooth espresso with milk', '1763901136_latte.jpg', 3.75, 15375.00, 1, 1, 0, '2025-11-23 11:18:02', '2025-11-23 12:32:16'),
(4, 1, 'Americano', 'Espresso with hot water', '1763900997_americano.jpg', 0.01, 100.00, 1, 1, 0, '2025-11-23 11:18:02', '2025-12-15 12:18:03'),
(5, 2, 'Green Tea', 'Fresh green tea', '1763901109_0732b8984fc55601ebb42da28d87de9b.jpg', 2.00, 8200.00, 1, 1, 0, '2025-11-23 11:18:02', '2025-11-23 12:31:49'),
(6, 2, 'Milk Tea', 'Classic milk tea', '1763901156_milk-tea.jpg', 3.00, 12300.00, 1, 1, 0, '2025-11-23 11:18:02', '2025-11-23 12:32:36'),
(7, 3, 'Orange Juice', 'Freshly squeezed', '1763901197_orange-juice.jpg', 3.50, 14350.00, 1, 1, 0, '2025-11-23 11:18:02', '2025-11-23 12:33:17'),
(8, 3, 'Smoothie', 'Mixed fruit smoothie', '1763901206_smoothie.jpg', 4.00, 16400.00, 1, 1, 0, '2025-11-23 11:18:02', '2025-11-23 12:33:26'),
(9, 4, 'Croissant', 'Buttery croissant', '1763901039_761516543db4e3a85bbae9de0eeb0c75 (1).jpg', 2.50, 10250.00, 1, 1, 0, '2025-11-23 11:18:02', '2025-11-23 12:30:39'),
(10, 4, 'Muffin', 'Blueberry muffin', '1763901178_muffin.jpg', 2.25, 9225.00, 1, 1, 0, '2025-11-23 11:18:02', '2025-11-23 12:32:58'),
(11, 5, 'Cookie', 'Chocolate chip cookie', '1763901032_cookie.jpg', 1.50, 6150.00, 1, 1, 0, '2025-11-23 11:18:02', '2025-11-23 12:30:32'),
(12, 5, 'Brownie', 'Fudge brownie', '1763901011_brownie.jpg', 2.00, 8200.00, 1, 1, 0, '2025-11-23 11:18:02', '2025-11-23 12:30:11'),
(13, 1, 'Mocha', 'Chocolate espresso delight', '1763901168_0732b8984fc55601ebb42da28d87de9b.jpg', 4.00, 16400.00, 1, 1, 0, '2025-11-23 11:18:29', '2025-11-23 12:32:48'),
(14, 1, 'Flat White', 'Velvety smooth coffee', '1763901084_flat-white.jpg', 3.50, 14350.00, 1, 1, 0, '2025-11-23 11:18:29', '2025-11-23 12:31:24'),
(15, 2, 'Jasmine Tea', 'Fragrant jasmine tea', '1763901130_jasmine-tea.jpg', 2.50, 10250.00, 1, 1, 0, '2025-11-23 11:18:29', '2025-11-23 12:32:10'),
(16, 2, 'Oolong Tea', 'Traditional oolong', '1763901188_oolong-tea.jpg', 2.75, 11275.00, 1, 1, 0, '2025-11-23 11:18:29', '2025-11-23 12:33:08'),
(17, 3, 'Lemonade', 'Fresh lemon drink', '1763901148_lemonade.jpg', 2.50, 10250.00, 1, 1, 0, '2025-11-23 11:18:29', '2025-11-23 12:32:28'),
(18, 3, 'Iced Coffee', 'Cold brew coffee', '1763901121_iced-coffee.jpg', 3.25, 13325.00, 1, 1, 0, '2025-11-23 11:18:29', '2025-11-23 12:32:01'),
(19, 4, 'Bagel', 'Fresh bagel with cream cheese', '1763901005_bagel.jpg', 2.75, 11275.00, 1, 1, 0, '2025-11-23 11:18:29', '2025-11-23 12:30:05'),
(20, 4, 'Danish Pastry', 'Sweet danish pastry', '1763901047_danish.jpg', 2.50, 10250.00, 1, 1, 0, '2025-11-23 11:18:29', '2025-11-23 12:30:47'),
(21, 5, 'Chips', 'Crispy potato chips', '1763901025_chips.jpg', 1.25, 5125.00, 1, 1, 0, '2025-11-23 11:18:29', '2025-11-23 12:30:25'),
(22, 5, 'Granola Bar', 'Healthy granola bar', '1763901100_granola.jpg', 1.75, 7175.00, 1, 1, 0, '2025-11-23 11:18:29', '2025-11-23 12:31:40');

-- --------------------------------------------------------

--
-- Table structure for table `product_modifiers`
--

CREATE TABLE `product_modifiers` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `modifier_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'exchange_rate_usd_to_khr', '10000', '2025-12-15 10:50:16'),
(2, 'tax_percent', '0', '2025-11-23 13:10:53'),
(3, 'khqr_merchant_id', '', '2025-11-24 13:14:06'),
(4, 'khqr_bank_account', '', '2025-11-24 13:14:06'),
(5, 'khqr_merchant_name', '', '2025-12-15 10:59:27'),
(6, 'printer_ip', '', '2025-11-23 12:26:24'),
(7, 'printer_port', '', '2025-11-23 12:26:24'),
(8, 'business_name', 'Robot Barista Cafe', '2025-12-20 03:41:17'),
(9, 'business_address', 'Phnom Penh, Cambodia', '2025-11-23 11:18:02'),
(10, 'business_phone', '+855 12 345 678 ', '2025-12-15 10:58:14'),
(11, 'printer_enabled', '1', '2025-11-23 13:23:06'),
(12, 'printer_type', 'network', '2025-11-23 13:23:06'),
(13, 'printer_paper_width', '80', '2025-11-23 13:23:06'),
(14, 'ui_navbar_color', '#16a34a', '2025-12-20 03:38:02'),
(15, 'ui_bg_color', '#ffffff', '2025-12-20 03:40:44'),
(16, 'ui_primary_color', '#16a34a', '2025-12-20 03:38:02'),
(21, 'ui_bg_image', '', '2025-12-20 04:04:16');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(200) DEFAULT NULL,
  `role` enum('admin','manager','staff') DEFAULT 'staff',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `role`, `is_active`, `last_login`, `created_at`) VALUES
(1, 'admin', '$2y$10$92BH6laiQlPm1ppqxHfE0eFGQIdpvmDVvblZ16TfVl.YgmSfFnlXK', 'System Administrator', 'admin', 1, NULL, '2025-11-23 11:20:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_order` (`display_order`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `modifiers`
--
ALTER TABLE `modifiers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `idx_order_number` (`order_number`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `print_logs`
--
ALTER TABLE `print_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_available` (`is_available`);

--
-- Indexes for table `product_modifiers`
--
ALTER TABLE `product_modifiers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_product_modifier` (`product_id`,`modifier_id`),
  ADD KEY `modifier_id` (`modifier_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `modifiers`
--
ALTER TABLE `modifiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `print_logs`
--
ALTER TABLE `print_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `product_modifiers`
--
ALTER TABLE `product_modifiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `print_logs`
--
ALTER TABLE `print_logs`
  ADD CONSTRAINT `print_logs_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_modifiers`
--
ALTER TABLE `product_modifiers`
  ADD CONSTRAINT `product_modifiers_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_modifiers_ibfk_2` FOREIGN KEY (`modifier_id`) REFERENCES `modifiers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
