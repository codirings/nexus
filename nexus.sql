-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 12, 2026 at 03:06 PM
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
-- Database: `nexus`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `address` varchar(300) NOT NULL,
  `city` varchar(100) NOT NULL,
  `province` varchar(100) DEFAULT NULL,
  `phone` varchar(30) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `address`, `city`, `province`, `phone`, `is_default`, `created_at`) VALUES
(2, 4, 'CN Tower', 'Toronto', 'Canada', '09569881497', 1, '2026-05-12 09:05:46');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(80) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `created_at`) VALUES
(2, 'admin', '$2y$10$rdHTC3t2HQYiFeqhSIbZqu3SjzXPrcfiQPvqGOa5hRLyfddsEoDuq', '2026-05-11 13:35:47');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `variant_id` int(10) UNSIGNED DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `variant_id`, `quantity`, `added_at`) VALUES
(9, 3, 34, NULL, 1, '2026-05-12 08:31:29'),
(10, 4, 163, NULL, 1, '2026-05-12 13:03:31'),
(11, 4, 165, NULL, 1, '2026-05-12 13:03:31'),
(12, 4, 164, NULL, 1, '2026-05-12 13:03:31');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(3, 'accessories'),
(1, 'phones'),
(4, 'PowerCase Apple'),
(8, 'PowerCase iPad'),
(11, 'PowerCase Lenovo'),
(6, 'PowerCase Oppo'),
(7, 'PowerCase Realme'),
(5, 'PowerCase Samsung'),
(9, 'PowerCase Samsung Tab'),
(10, 'PowerCase Surface'),
(12, 'PowerCase Xiaomi'),
(13, 'Screen Protector Apple'),
(16, 'Screen Protector iPad'),
(15, 'Screen Protector Oppo Realme'),
(18, 'Screen Protector Others'),
(14, 'Screen Protector Samsung'),
(17, 'Screen Protector Tab'),
(2, 'tablets');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `address_id` int(10) UNSIGNED DEFAULT NULL,
  `payment_method_id` int(10) UNSIGNED DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `shipping` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','processing','shipped','completed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED DEFAULT NULL,
  `variant_id` int(10) UNSIGNED DEFAULT NULL,
  `product_name` varchar(200) NOT NULL,
  `variant_name` varchar(150) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(30) NOT NULL,
  `label` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `code`, `label`) VALUES
(1, 'cod', 'Cash on Delivery'),
(2, 'gcash', 'GCash'),
(3, 'maya', 'Maya');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image` varchar(300) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category_id`, `price`, `stock`, `image`, `created_at`) VALUES
(15, 'NEXUS PowerCase iPhone 17', 4, 57990.00, 100, NULL, '2026-05-12 08:26:39'),
(16, 'NEXUS PowerCase iPhone 17 Pro', 4, 79990.00, 100, NULL, '2026-05-12 08:26:39'),
(17, 'NEXUS PowerCase iPhone 17 Pro Max', 4, 86990.00, 100, NULL, '2026-05-12 08:26:39'),
(18, 'NEXUS PowerCase iPhone 17 Air', 4, 72990.00, 100, NULL, '2026-05-12 08:26:39'),
(19, 'NEXUS PowerCase iPhone 17e', 4, 44990.00, 100, NULL, '2026-05-12 08:26:39'),
(20, 'NEXUS PowerCase iPhone 16', 4, 49990.00, 100, NULL, '2026-05-12 08:26:39'),
(21, 'NEXUS PowerCase iPhone 16 Plus', 4, 54990.00, 100, NULL, '2026-05-12 08:26:39'),
(22, 'NEXUS PowerCase iPhone 16 Pro', 4, 62490.00, 100, NULL, '2026-05-12 08:26:39'),
(23, 'NEXUS PowerCase iPhone 16 Pro Max', 4, 70990.00, 100, NULL, '2026-05-12 08:26:39'),
(24, 'NEXUS PowerCase iPhone 16e', 4, 32990.00, 100, NULL, '2026-05-12 08:26:39'),
(25, 'NEXUS PowerCase iPhone 15', 4, 42990.00, 100, NULL, '2026-05-12 08:26:39'),
(26, 'NEXUS PowerCase iPhone 15 Plus', 4, 49990.00, 100, NULL, '2026-05-12 08:26:39'),
(27, 'NEXUS PowerCase iPhone 15 Pro', 4, 54990.00, 100, NULL, '2026-05-12 08:26:39'),
(28, 'NEXUS PowerCase iPhone 15 Pro Max', 4, 62990.00, 100, NULL, '2026-05-12 08:26:39'),
(29, 'NEXUS PowerCase iPhone 15e', 4, 28990.00, 100, NULL, '2026-05-12 08:26:39'),
(30, 'NEXUS PowerCase iPhone 14', 4, 36990.00, 100, NULL, '2026-05-12 08:26:39'),
(31, 'NEXUS PowerCase iPhone 14 Plus', 4, 42990.00, 100, NULL, '2026-05-12 08:26:39'),
(32, 'NEXUS PowerCase iPhone 14 Pro', 4, 48990.00, 100, NULL, '2026-05-12 08:26:39'),
(33, 'NEXUS PowerCase iPhone 14 Pro Max', 4, 55990.00, 100, NULL, '2026-05-12 08:26:39'),
(34, 'NEXUS PowerCase iPhone 14e', 4, 24990.00, 100, NULL, '2026-05-12 08:26:39'),
(35, 'NEXUS PowerCase iPhone 13', 4, 30990.00, 100, NULL, '2026-05-12 08:26:39'),
(36, 'NEXUS PowerCase iPhone 13 Mini', 4, 28990.00, 100, NULL, '2026-05-12 08:26:39'),
(37, 'NEXUS PowerCase iPhone 13 Pro', 4, 38990.00, 100, NULL, '2026-05-12 08:26:39'),
(38, 'NEXUS PowerCase iPhone 13 Pro Max', 4, 44990.00, 100, NULL, '2026-05-12 08:26:39'),
(39, 'NEXUS PowerCase iPhone 13e', 4, 20990.00, 100, NULL, '2026-05-12 08:26:39'),
(40, 'NEXUS PowerCase iPhone 12', 4, 24990.00, 100, NULL, '2026-05-12 08:26:39'),
(41, 'NEXUS PowerCase iPhone 12 Mini', 4, 21990.00, 100, NULL, '2026-05-12 08:26:39'),
(42, 'NEXUS PowerCase iPhone 12 Pro', 4, 32990.00, 100, NULL, '2026-05-12 08:26:39'),
(43, 'NEXUS PowerCase iPhone 12 Pro Max', 4, 37990.00, 100, NULL, '2026-05-12 08:26:39'),
(44, 'NEXUS PowerCase iPhone 12e', 4, 18990.00, 100, NULL, '2026-05-12 08:26:39'),
(45, 'NEXUS PowerCase iPhone 11', 4, 19990.00, 100, NULL, '2026-05-12 08:26:39'),
(46, 'NEXUS PowerCase iPhone 11 Pro', 4, 24990.00, 100, NULL, '2026-05-12 08:26:39'),
(47, 'NEXUS PowerCase iPhone 11 Pro Max', 4, 28990.00, 100, NULL, '2026-05-12 08:26:39'),
(48, 'NEXUS PowerCase iPhone 11e', 4, 14990.00, 100, NULL, '2026-05-12 08:26:39'),
(49, 'NEXUS PowerCase iPhone X Series', 4, 12500.00, 50, NULL, '2026-05-12 08:26:39'),
(50, 'NEXUS PowerCase iPhone 8 Series', 4, 8500.00, 50, NULL, '2026-05-12 08:26:39'),
(51, 'NEXUS PowerCase iPhone 7 Series', 4, 6500.00, 50, NULL, '2026-05-12 08:26:39'),
(52, 'NEXUS PowerCase iPhone 6 Series', 4, 4500.00, 50, NULL, '2026-05-12 08:26:39'),
(53, 'NEXUS PowerCase iPhone SE Series', 4, 23990.00, 50, NULL, '2026-05-12 08:26:39'),
(54, 'NEXUS PowerCase iPhone 5 Series', 4, 2500.00, 50, NULL, '2026-05-12 08:26:39'),
(55, 'NEXUS PowerCase Galaxy S26 Ultra', 5, 121990.00, 100, NULL, '2026-05-12 08:26:39'),
(56, 'NEXUS PowerCase Galaxy S26+', 5, 88990.00, 100, NULL, '2026-05-12 08:26:39'),
(57, 'NEXUS PowerCase Galaxy S26', 5, 58990.00, 100, NULL, '2026-05-12 08:26:39'),
(58, 'NEXUS PowerCase Galaxy S25 Ultra', 5, 84990.00, 100, NULL, '2026-05-12 08:26:39'),
(59, 'NEXUS PowerCase Galaxy S25+', 5, 68990.00, 100, NULL, '2026-05-12 08:26:39'),
(60, 'NEXUS PowerCase Galaxy S25', 5, 53990.00, 100, NULL, '2026-05-12 08:26:39'),
(61, 'NEXUS PowerCase Galaxy S24 Ultra', 5, 64990.00, 100, NULL, '2026-05-12 08:26:39'),
(62, 'NEXUS PowerCase Galaxy S24+', 5, 52990.00, 100, NULL, '2026-05-12 08:26:39'),
(63, 'NEXUS PowerCase Galaxy S24', 5, 44990.00, 100, NULL, '2026-05-12 08:26:39'),
(64, 'NEXUS PowerCase Galaxy Z Fold 8', 5, 115990.00, 100, NULL, '2026-05-12 08:26:39'),
(65, 'NEXUS PowerCase Galaxy Z Fold 7', 5, 98990.00, 100, NULL, '2026-05-12 08:26:39'),
(66, 'NEXUS PowerCase Galaxy Z Flip 8', 5, 64990.00, 100, NULL, '2026-05-12 08:26:39'),
(67, 'NEXUS PowerCase Galaxy Z Flip 7', 5, 52990.00, 100, NULL, '2026-05-12 08:26:39'),
(68, 'NEXUS PowerCase Galaxy A76', 5, 32990.00, 100, NULL, '2026-05-12 08:26:39'),
(69, 'NEXUS PowerCase Galaxy A56', 5, 24990.00, 100, NULL, '2026-05-12 08:26:39'),
(70, 'NEXUS PowerCase Galaxy A36', 5, 18990.00, 100, NULL, '2026-05-12 08:26:39'),
(71, 'NEXUS PowerCase Galaxy A26', 5, 14990.00, 100, NULL, '2026-05-12 08:26:39'),
(72, 'NEXUS PowerCase Galaxy A16', 5, 10990.00, 100, NULL, '2026-05-12 08:26:39'),
(73, 'NEXUS PowerCase Galaxy M56', 5, 22990.00, 100, NULL, '2026-05-12 08:26:39'),
(74, 'NEXUS PowerCase Galaxy M36', 5, 16990.00, 100, NULL, '2026-05-12 08:26:39'),
(75, 'NEXUS PowerCase Galaxy M16', 5, 9990.00, 100, NULL, '2026-05-12 08:26:39'),
(76, 'NEXUS PowerCase Galaxy S23 Series', 5, 32990.00, 50, NULL, '2026-05-12 08:26:39'),
(77, 'NEXUS PowerCase Galaxy S22 Series', 5, 24990.00, 50, NULL, '2026-05-12 08:26:39'),
(78, 'NEXUS PowerCase Galaxy Note 20 Series', 5, 18990.00, 50, NULL, '2026-05-12 08:26:39'),
(79, 'NEXUS PowerCase Oppo Find X9 Ultra', 6, 76990.00, 100, NULL, '2026-05-12 08:26:39'),
(80, 'NEXUS PowerCase Oppo Find X9s / Pro', 6, 64990.00, 100, NULL, '2026-05-12 08:26:39'),
(81, 'NEXUS PowerCase Oppo Find N6', 6, 88990.00, 100, NULL, '2026-05-12 08:26:39'),
(82, 'NEXUS PowerCase Oppo Find N5 Flip', 6, 52990.00, 100, NULL, '2026-05-12 08:26:39'),
(83, 'NEXUS PowerCase Oppo Find X8 Series', 6, 54990.00, 100, NULL, '2026-05-12 08:26:39'),
(84, 'NEXUS PowerCase Oppo Reno 16 Pro+', 6, 42990.00, 100, NULL, '2026-05-12 08:26:40'),
(85, 'NEXUS PowerCase Oppo Reno 16 Pro', 6, 36990.00, 100, NULL, '2026-05-12 08:26:40'),
(86, 'NEXUS PowerCase Oppo Reno 16', 6, 28990.00, 100, NULL, '2026-05-12 08:26:40'),
(87, 'NEXUS PowerCase Oppo Reno 15 Pro 5G', 6, 32990.00, 100, NULL, '2026-05-12 08:26:40'),
(88, 'NEXUS PowerCase Oppo Reno 15F', 6, 18990.00, 100, NULL, '2026-05-12 08:26:40'),
(89, 'NEXUS PowerCase Oppo F33 Pro 5G', 6, 24990.00, 100, NULL, '2026-05-12 08:26:40'),
(90, 'NEXUS PowerCase Oppo F33 5G', 6, 19990.00, 100, NULL, '2026-05-12 08:26:40'),
(91, 'NEXUS PowerCase Oppo F27 Pro+ 5G', 6, 21990.00, 100, NULL, '2026-05-12 08:26:40'),
(92, 'NEXUS PowerCase Oppo A6 Pro 5G', 6, 15990.00, 100, NULL, '2026-05-12 08:26:40'),
(93, 'NEXUS PowerCase Oppo A6 5G', 6, 12990.00, 100, NULL, '2026-05-12 08:26:40'),
(94, 'NEXUS PowerCase Oppo A6s 5G', 6, 10990.00, 100, NULL, '2026-05-12 08:26:40'),
(95, 'NEXUS PowerCase Oppo A5 Pro', 6, 13990.00, 100, NULL, '2026-05-12 08:26:40'),
(96, 'NEXUS PowerCase Oppo A3 Pro', 6, 11990.00, 100, NULL, '2026-05-12 08:26:40'),
(97, 'NEXUS PowerCase Oppo A60', 6, 8990.00, 100, NULL, '2026-05-12 08:26:40'),
(98, 'NEXUS PowerCase Realme GT 8 Pro', 7, 51990.00, 100, NULL, '2026-05-12 08:26:40'),
(99, 'NEXUS PowerCase Realme GT 7 Pro 5G', 7, 37999.00, 100, NULL, '2026-05-12 08:26:40'),
(100, 'NEXUS PowerCase Realme GT 7T', 7, 29999.00, 100, NULL, '2026-05-12 08:26:40'),
(101, 'NEXUS PowerCase Realme GT Neo 8', 7, 34990.00, 100, NULL, '2026-05-12 08:26:40'),
(102, 'NEXUS PowerCase Realme 16 Pro+ 5G', 7, 30990.00, 100, NULL, '2026-05-12 08:26:40'),
(103, 'NEXUS PowerCase Realme 16 Pro 5G', 7, 26990.00, 100, NULL, '2026-05-12 08:26:40'),
(104, 'NEXUS PowerCase Realme 16 5G', 7, 19990.00, 100, NULL, '2026-05-12 08:26:40'),
(105, 'NEXUS PowerCase Realme 15 Pro 5G', 7, 27999.00, 100, NULL, '2026-05-12 08:26:40'),
(106, 'NEXUS PowerCase Realme 15T 5G', 7, 16999.00, 100, NULL, '2026-05-12 08:26:40'),
(107, 'NEXUS PowerCase Realme C85 5G', 7, 12990.00, 100, NULL, '2026-05-12 08:26:40'),
(108, 'NEXUS PowerCase Realme C85 Pro', 7, 14990.00, 100, NULL, '2026-05-12 08:26:40'),
(109, 'NEXUS PowerCase Realme C75', 7, 8999.00, 100, NULL, '2026-05-12 08:26:40'),
(110, 'NEXUS PowerCase Realme C71 4G', 7, 6990.00, 100, NULL, '2026-05-12 08:26:40'),
(111, 'NEXUS PowerCase iPad Pro 13-inch (M4)', 8, 94990.00, 100, NULL, '2026-05-12 08:26:40'),
(112, 'NEXUS PowerCase iPad Pro 11-inch (M4)', 8, 72990.00, 100, NULL, '2026-05-12 08:26:40'),
(113, 'NEXUS PowerCase iPad Pro 12.9-inch (6th Gen)', 8, 68990.00, 100, NULL, '2026-05-12 08:26:40'),
(114, 'NEXUS PowerCase iPad Pro 11-inch (4th Gen)', 8, 48990.00, 100, NULL, '2026-05-12 08:26:40'),
(115, 'NEXUS PowerCase iPad Air 13-inch (M2)', 8, 50990.00, 100, NULL, '2026-05-12 08:26:40'),
(116, 'NEXUS PowerCase iPad Air 11-inch (M2)', 8, 39990.00, 100, NULL, '2026-05-12 08:26:40'),
(117, 'NEXUS PowerCase iPad Air (5th Generation)', 8, 32990.00, 100, NULL, '2026-05-12 08:26:40'),
(118, 'NEXUS PowerCase iPad (10th Generation)', 8, 20990.00, 100, NULL, '2026-05-12 08:26:40'),
(119, 'NEXUS PowerCase iPad (9th Generation)', 8, 15990.00, 100, NULL, '2026-05-12 08:26:40'),
(120, 'NEXUS PowerCase iPad mini (6th Generation)', 8, 29990.00, 100, NULL, '2026-05-12 08:26:40'),
(121, 'NEXUS PowerCase iPad mini (5th Generation)', 8, 18990.00, 100, NULL, '2026-05-12 08:26:40'),
(122, 'NEXUS PowerCase Galaxy Tab S9 Ultra', 9, 78990.00, 100, NULL, '2026-05-12 08:26:40'),
(123, 'NEXUS PowerCase Galaxy Tab S9+ / S9', 9, 56990.00, 100, NULL, '2026-05-12 08:26:40'),
(124, 'NEXUS PowerCase Galaxy Tab S8 Ultra', 9, 52990.00, 100, NULL, '2026-05-12 08:26:40'),
(125, 'NEXUS PowerCase Galaxy Tab S8+ / S8', 9, 38990.00, 100, NULL, '2026-05-12 08:26:40'),
(126, 'NEXUS PowerCase Galaxy Tab S7 / S7 FE', 9, 28990.00, 100, NULL, '2026-05-12 08:26:40'),
(127, 'NEXUS PowerCase Galaxy Tab S6 Lite (2024)', 9, 18990.00, 100, NULL, '2026-05-12 08:26:40'),
(128, 'NEXUS PowerCase Galaxy Tab A9+ / A9', 9, 10990.00, 100, NULL, '2026-05-12 08:26:40'),
(129, 'NEXUS PowerCase Galaxy Tab A8 10.5', 9, 12990.00, 100, NULL, '2026-05-12 08:26:40'),
(130, 'NEXUS PowerCase Galaxy Tab A7 Lite', 9, 7990.00, 100, NULL, '2026-05-12 08:26:40'),
(131, 'NEXUS PowerCase Galaxy Tab Active5', 9, 26990.00, 100, NULL, '2026-05-12 08:26:40'),
(132, 'NEXUS PowerCase Galaxy Tab Active4 Pro', 9, 32990.00, 100, NULL, '2026-05-12 08:26:40'),
(133, 'NEXUS PowerCase Surface Pro 11th Edition', 10, 86990.00, 100, NULL, '2026-05-12 08:26:40'),
(134, 'NEXUS PowerCase Surface Pro 10 (Business)', 10, 74990.00, 100, NULL, '2026-05-12 08:26:40'),
(135, 'NEXUS PowerCase Surface Pro 9 / 9 5G', 10, 58990.00, 100, NULL, '2026-05-12 08:26:40'),
(136, 'NEXUS PowerCase Surface Pro 8', 10, 42990.00, 100, NULL, '2026-05-12 08:26:40'),
(137, 'NEXUS PowerCase Surface Pro 7+ / 7', 10, 24990.00, 100, NULL, '2026-05-12 08:26:40'),
(138, 'NEXUS PowerCase Surface Pro X', 10, 32990.00, 100, NULL, '2026-05-12 08:26:40'),
(139, 'NEXUS PowerCase Surface Go 4', 10, 34990.00, 100, NULL, '2026-05-12 08:26:40'),
(140, 'NEXUS PowerCase Surface Go 3', 10, 22990.00, 100, NULL, '2026-05-12 08:26:40'),
(141, 'NEXUS PowerCase Surface Laptop Studio 2', 10, 134990.00, 100, NULL, '2026-05-12 08:26:40'),
(142, 'NEXUS PowerCase Surface Laptop Go 3', 10, 46990.00, 100, NULL, '2026-05-12 08:26:40'),
(143, 'NEXUS PowerCase Lenovo Tab Extreme', 11, 72990.00, 100, NULL, '2026-05-12 08:26:40'),
(144, 'NEXUS PowerCase Lenovo Legion Tab (Gen 2)', 11, 26990.00, 100, NULL, '2026-05-12 08:26:40'),
(145, 'NEXUS PowerCase Lenovo Yoga Tab 13', 11, 38990.00, 100, NULL, '2026-05-12 08:26:40'),
(146, 'NEXUS PowerCase Lenovo Yoga Tab 11', 11, 19990.00, 100, NULL, '2026-05-12 08:26:40'),
(147, 'NEXUS PowerCase Lenovo Tab P12', 11, 22990.00, 100, NULL, '2026-05-12 08:26:40'),
(148, 'NEXUS PowerCase Lenovo Tab P11 Gen 2', 11, 18990.00, 100, NULL, '2026-05-12 08:26:40'),
(149, 'NEXUS PowerCase Lenovo Tab P11 Pro Gen 2', 11, 28990.00, 100, NULL, '2026-05-12 08:26:40'),
(150, 'NEXUS PowerCase Lenovo Tab M11', 11, 11990.00, 100, NULL, '2026-05-12 08:26:40'),
(151, 'NEXUS PowerCase Lenovo Tab M10 5G', 11, 14990.00, 100, NULL, '2026-05-12 08:26:40'),
(152, 'NEXUS PowerCase Lenovo Tab M10 Plus (3rd Gen)', 11, 12990.00, 100, NULL, '2026-05-12 08:26:40'),
(153, 'NEXUS PowerCase Lenovo Tab M9', 11, 7990.00, 100, NULL, '2026-05-12 08:26:40'),
(154, 'NEXUS PowerCase Xiaomi Pad 6S Pro 12.4', 12, 34990.00, 100, NULL, '2026-05-12 08:26:40'),
(155, 'NEXUS PowerCase Xiaomi Pad 6 Max 14', 12, 42990.00, 100, NULL, '2026-05-12 08:26:40'),
(156, 'NEXUS PowerCase Xiaomi Pad 6 Pro', 12, 28990.00, 100, NULL, '2026-05-12 08:26:40'),
(157, 'NEXUS PowerCase Xiaomi Pad 6', 12, 17990.00, 100, NULL, '2026-05-12 08:26:40'),
(158, 'NEXUS PowerCase Xiaomi Pad 5 Pro 12.4', 12, 24990.00, 100, NULL, '2026-05-12 08:26:40'),
(159, 'NEXUS PowerCase Xiaomi Pad 5', 12, 14990.00, 100, NULL, '2026-05-12 08:26:40'),
(160, 'NEXUS PowerCase Redmi Pad Pro 5G', 12, 16990.00, 100, NULL, '2026-05-12 08:26:40'),
(161, 'NEXUS PowerCase Redmi Pad SE', 12, 8990.00, 100, NULL, '2026-05-12 08:26:40'),
(162, 'NEXUS PowerCase Redmi Pad (2022)', 12, 10990.00, 100, NULL, '2026-05-12 08:26:40'),
(163, 'NEXUS Durable Cord', 3, 399.00, 200, NULL, '2026-05-12 08:26:40'),
(164, 'NEXUS Power Bank', 3, 2999.00, 150, NULL, '2026-05-12 08:26:40'),
(165, 'NEXUS Earbuds', 3, 999.00, 150, NULL, '2026-05-12 08:26:40'),
(166, 'NEXUS Screen Protector iPhone 17 Series (All Models)', 13, 44990.00, 100, NULL, '2026-05-12 08:26:40'),
(167, 'NEXUS Screen Protector iPhone 16 Series (All Models)', 13, 32990.00, 100, NULL, '2026-05-12 08:26:40'),
(168, 'NEXUS Screen Protector iPhone 15 Series (All Models)', 13, 28990.00, 100, NULL, '2026-05-12 08:26:40'),
(169, 'NEXUS Screen Protector iPhone 14 Series (All Models)', 13, 24990.00, 100, NULL, '2026-05-12 08:26:40'),
(170, 'NEXUS Screen Protector iPhone 13 Series (All Models)', 13, 20990.00, 100, NULL, '2026-05-12 08:26:40'),
(171, 'NEXUS Screen Protector iPhone 12 Series (All Models)', 13, 18990.00, 100, NULL, '2026-05-12 08:26:40'),
(172, 'NEXUS Screen Protector iPhone 11 Series (All Models)', 13, 14990.00, 100, NULL, '2026-05-12 08:26:40'),
(173, 'NEXUS Screen Protector iPhone X/8/7/6/SE Series', 13, 2500.00, 50, NULL, '2026-05-12 08:26:40'),
(174, 'NEXUS Screen Protector Galaxy S26 Ultra/+/Std', 14, 58990.00, 100, NULL, '2026-05-12 08:26:40'),
(175, 'NEXUS Screen Protector Galaxy S25 Ultra/+/Std', 14, 53990.00, 100, NULL, '2026-05-12 08:26:40'),
(176, 'NEXUS Screen Protector Galaxy S24 Ultra/+/Std', 14, 44990.00, 100, NULL, '2026-05-12 08:26:40'),
(177, 'NEXUS Screen Protector Galaxy Z Fold/Flip 8 & 7', 14, 52990.00, 100, NULL, '2026-05-12 08:26:40'),
(178, 'NEXUS Screen Protector Galaxy A76/A56/A36/A26/A16', 14, 10990.00, 100, NULL, '2026-05-12 08:26:40'),
(179, 'NEXUS Screen Protector Galaxy M56/M36/M16', 14, 9990.00, 100, NULL, '2026-05-12 08:26:40'),
(180, 'NEXUS Screen Protector Galaxy S23/S22/Note 20', 14, 18990.00, 50, NULL, '2026-05-12 08:26:40'),
(181, 'NEXUS Screen Protector Oppo Find X9/N6/X8 Series', 15, 52990.00, 100, NULL, '2026-05-12 08:26:40'),
(182, 'NEXUS Screen Protector Oppo Reno 16/15 Series', 15, 18990.00, 100, NULL, '2026-05-12 08:26:40'),
(183, 'NEXUS Screen Protector Oppo F33/F27/A6/A5 Series', 15, 8990.00, 100, NULL, '2026-05-12 08:26:40'),
(184, 'NEXUS Screen Protector Realme GT 8/7/Neo Series', 15, 34990.00, 100, NULL, '2026-05-12 08:26:40'),
(185, 'NEXUS Screen Protector Realme 16/15 Number Series', 15, 16999.00, 100, NULL, '2026-05-12 08:26:40'),
(186, 'NEXUS Screen Protector Realme C85/C75/C71 Series', 15, 6990.00, 100, NULL, '2026-05-12 08:26:40'),
(187, 'NEXUS Screen Protector iPad Pro 13/11-inch (M4)', 16, 72990.00, 100, NULL, '2026-05-12 08:26:40'),
(188, 'NEXUS Screen Protector iPad Pro 12.9 (6th Gen)', 16, 68990.00, 100, NULL, '2026-05-12 08:26:40'),
(189, 'NEXUS Screen Protector iPad Air 13/11-inch (M2)', 16, 39990.00, 100, NULL, '2026-05-12 08:26:40'),
(190, 'NEXUS Screen Protector iPad 10th/9th Gen', 16, 15990.00, 100, NULL, '2026-05-12 08:26:40'),
(191, 'NEXUS Screen Protector iPad mini 6th/5th Gen', 16, 18990.00, 100, NULL, '2026-05-12 08:26:40'),
(192, 'NEXUS Screen Protector Galaxy Tab S9 Ultra/+/Std', 17, 56990.00, 100, NULL, '2026-05-12 08:26:40'),
(193, 'NEXUS Screen Protector Galaxy Tab S8 Ultra/+/Std', 17, 38990.00, 100, NULL, '2026-05-12 08:26:40'),
(194, 'NEXUS Screen Protector Galaxy Tab S7/FE/S6 Lite', 17, 18990.00, 100, NULL, '2026-05-12 08:26:40'),
(195, 'NEXUS Screen Protector Galaxy Tab A9/A8/A7/Active', 17, 7990.00, 100, NULL, '2026-05-12 08:26:40'),
(196, 'NEXUS Screen Protector Surface Pro 11/10/9/8/7/X', 18, 24990.00, 100, NULL, '2026-05-12 08:26:40'),
(197, 'NEXUS Screen Protector Surface Go 4/3', 18, 22990.00, 100, NULL, '2026-05-12 08:26:40'),
(198, 'NEXUS Screen Protector Lenovo Tab Extreme/Legion', 18, 26990.00, 100, NULL, '2026-05-12 08:26:40'),
(199, 'NEXUS Screen Protector Lenovo Yoga/P12/P11/M11/M10', 18, 7990.00, 100, NULL, '2026-05-12 08:26:40'),
(200, 'NEXUS Screen Protector Xiaomi Pad 6S Pro/6 Max/6/5', 18, 14990.00, 100, NULL, '2026-05-12 08:26:40'),
(201, 'NEXUS Screen Protector Redmi Pad Pro/SE', 18, 8990.00, 100, NULL, '2026-05-12 08:26:40');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `price` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_log`
--

CREATE TABLE `stock_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED DEFAULT NULL,
  `change_qty` int(11) NOT NULL,
  `reason` enum('manual','adjustment','sale','return') NOT NULL DEFAULT 'manual',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_log`
--

INSERT INTO `stock_log` (`id`, `product_id`, `order_id`, `change_qty`, `reason`, `created_at`) VALUES
(12, 15, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(13, 16, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(14, 17, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(15, 18, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(16, 19, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(17, 20, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(18, 21, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(19, 22, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(20, 23, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(21, 24, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(22, 25, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(23, 26, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(24, 27, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(25, 28, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(26, 29, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(27, 30, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(28, 31, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(29, 32, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(30, 33, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(31, 34, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(32, 35, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(33, 36, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(34, 37, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(35, 38, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(36, 39, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(37, 40, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(38, 41, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(39, 42, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(40, 43, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(41, 44, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(42, 45, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(43, 46, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(44, 47, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(45, 48, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(46, 49, NULL, 50, 'manual', '2026-05-12 08:26:40'),
(47, 50, NULL, 50, 'manual', '2026-05-12 08:26:40'),
(48, 51, NULL, 50, 'manual', '2026-05-12 08:26:40'),
(49, 52, NULL, 50, 'manual', '2026-05-12 08:26:40'),
(50, 53, NULL, 50, 'manual', '2026-05-12 08:26:40'),
(51, 54, NULL, 50, 'manual', '2026-05-12 08:26:40'),
(52, 55, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(53, 56, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(54, 57, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(55, 58, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(56, 59, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(57, 60, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(58, 61, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(59, 62, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(60, 63, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(61, 64, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(62, 65, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(63, 66, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(64, 67, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(65, 68, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(66, 69, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(67, 70, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(68, 71, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(69, 72, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(70, 73, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(71, 74, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(72, 75, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(73, 76, NULL, 50, 'manual', '2026-05-12 08:26:40'),
(74, 77, NULL, 50, 'manual', '2026-05-12 08:26:40'),
(75, 78, NULL, 50, 'manual', '2026-05-12 08:26:40'),
(76, 79, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(77, 80, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(78, 81, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(79, 82, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(80, 83, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(81, 84, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(82, 85, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(83, 86, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(84, 87, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(85, 88, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(86, 89, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(87, 90, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(88, 91, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(89, 92, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(90, 93, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(91, 94, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(92, 95, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(93, 96, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(94, 97, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(95, 98, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(96, 99, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(97, 100, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(98, 101, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(99, 102, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(100, 103, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(101, 104, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(102, 105, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(103, 106, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(104, 107, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(105, 108, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(106, 109, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(107, 110, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(108, 111, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(109, 112, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(110, 113, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(111, 114, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(112, 115, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(113, 116, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(114, 117, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(115, 118, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(116, 119, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(117, 120, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(118, 121, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(119, 122, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(120, 123, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(121, 124, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(122, 125, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(123, 126, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(124, 127, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(125, 128, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(126, 129, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(127, 130, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(128, 131, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(129, 132, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(130, 133, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(131, 134, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(132, 135, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(133, 136, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(134, 137, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(135, 138, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(136, 139, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(137, 140, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(138, 141, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(139, 142, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(140, 143, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(141, 144, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(142, 145, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(143, 146, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(144, 147, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(145, 148, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(146, 149, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(147, 150, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(148, 151, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(149, 152, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(150, 153, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(151, 154, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(152, 155, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(153, 156, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(154, 157, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(155, 158, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(156, 159, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(157, 160, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(158, 161, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(159, 162, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(160, 163, NULL, 200, 'manual', '2026-05-12 08:26:40'),
(161, 164, NULL, 150, 'manual', '2026-05-12 08:26:40'),
(162, 165, NULL, 150, 'manual', '2026-05-12 08:26:40'),
(163, 166, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(164, 167, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(165, 168, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(166, 169, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(167, 170, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(168, 171, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(169, 172, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(170, 173, NULL, 50, 'manual', '2026-05-12 08:26:40'),
(171, 174, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(172, 175, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(173, 176, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(174, 177, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(175, 178, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(176, 179, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(177, 180, NULL, 50, 'manual', '2026-05-12 08:26:40'),
(178, 181, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(179, 182, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(180, 183, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(181, 184, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(182, 185, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(183, 186, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(184, 187, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(185, 188, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(186, 189, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(187, 190, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(188, 191, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(189, 192, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(190, 193, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(191, 194, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(192, 195, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(193, 196, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(194, 197, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(195, 198, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(196, 199, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(197, 200, NULL, 100, 'manual', '2026-05-12 08:26:40'),
(198, 201, NULL, 100, 'manual', '2026-05-12 08:26:40');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `fullname` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `created_at`) VALUES
(2, 'Test User', 'test@nexusmail.com', '$2y$10$1VIQYxhwJw95e4pG5rgwD.xMI2x1IriEiTdn6ZkdTjRBTaIrgBjEC', '2026-05-12 05:47:13'),
(3, 'Reily', 'testemail@nexus.com', '$2y$10$5IM/4C7.BxoYZ.Lr0YNkdOGgzj1DKhTiHK2B.REK3rHDCBdOGtu.q', '2026-05-12 08:31:29'),
(4, 'radski', 'radski@gmail.com', '$2y$10$9rSZ0JfiPSdLJGJMMY5IyOn5dvjuush7nD9Jb9XQKorz5fldIyC2W', '2026-05-12 09:05:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `address_id` (`address_id`),
  ADD KEY `payment_method_id` (`payment_method_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `stock_log`
--
ALTER TABLE `stock_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=203;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `stock_log`
--
ALTER TABLE `stock_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=199;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `stock_log`
--
ALTER TABLE `stock_log`
  ADD CONSTRAINT `stock_log_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_log_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
