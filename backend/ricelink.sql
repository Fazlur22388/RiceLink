-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 30, 2025 at 05:09 PM
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
-- Database: `ricelink`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookmarks`
--

CREATE TABLE `bookmarks` (
  `id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `buyers`
--

CREATE TABLE `buyers` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `shop_name` text NOT NULL,
  `shop_address` varchar(255) NOT NULL,
  `phone` int(10) NOT NULL,
  `whatsapp` int(10) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` text NOT NULL,
  `buyer_type` enum('Trader','Mill','Retailer','Other') NOT NULL,
  `profile_pic` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buyers`
--

INSERT INTO `buyers` (`id`, `name`, `shop_name`, `shop_address`, `phone`, `whatsapp`, `email`, `password`, `buyer_type`, `profile_pic`, `created_at`) VALUES
(1, 'shamri', 'S traders', 'kadugannawa', 754412365, 745587469, 'shamri44@gmial.com', '$2y$10$zcYSIRvjvmPJLw1MXBz9zeC4qOwUZtdAp95ITEq.dmxU32KafFPjq', 'Trader', '1748357392_2.jpg', '2025-05-27 14:49:52');

-- --------------------------------------------------------

--
-- Table structure for table `farmers`
--

CREATE TABLE `farmers` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `dob` date NOT NULL,
  `nic` int(50) NOT NULL,
  `Phone` int(10) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(225) NOT NULL DEFAULT current_timestamp(),
  `address` varchar(225) NOT NULL,
  `district` varchar(225) NOT NULL,
  `city` varchar(225) NOT NULL,
  `postal_code` int(100) NOT NULL,
  `farm_type` varchar(225) NOT NULL,
  `experience` varchar(100) NOT NULL,
  `land_size` varchar(100) NOT NULL,
  `membership` varchar(100) NOT NULL,
  `profile_picture` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `farmers`
--

INSERT INTO `farmers` (`id`, `username`, `first_name`, `last_name`, `dob`, `nic`, `Phone`, `email`, `password`, `address`, `district`, `city`, `postal_code`, `farm_type`, `experience`, `land_size`, `membership`, `profile_picture`, `created_at`) VALUES
(4, 'john', 'john', 'peter', '1998-06-09', 2039457365, 722348975, 'john@gmail.com', '$2y$10$AfWKnqCA/Pwrd2YAuISHxuVlRQdoIs89FRh0GWDco2GzGC.f5sMu2', 'srilanka', 'kandy', 'kandy', 20475, 'rice', '2', '32', 'Yes', 'uploads/685ea757c1036_bg2.jpg', '2025-06-27 10:44:47'),
(5, 'sara', 'fathi', 'sara', '1990-06-10', 123456, 722348975, 'sara@gmail.com', '$2y$10$RGW18cxalpGVa3A2c/eljOEnk9s.D8ssxbbMSDm0eck0t/0L0CVUm', 'srilanka', 'kandy', 'kandy', 20475, 'rice', '2', '32', 'No', 'uploads/685eae5adfaf5_blanket3.jpeg', '2025-06-27 11:14:42'),
(6, 'sara', 'fathi', 'sara', '1990-06-10', 123456, 722348975, 'sara11232@gmail.com', '$2y$10$kkPrXFOJF3cjg9r6lEJTt.qEzrdCSP8wg2b4LXrRcC/t8.7ERIDs6', 'np, Sri lanka', 'kandy', 'nawalapitya', 20650, 'rice', '12', '12', 'No', 'uploads/685eaeca88aa4_blanket1.jpg', '2025-06-27 11:16:34'),
(9, 'mimi', 'mimi', 'momo', '1990-06-04', 345678, 722348975, 'mimi@gmail.com', '$2y$10$cYHNxCoSCZfy5/DFQe8iEuunj7tpDFbyCOZN9l4FTcu9oi8oJsLv2', 'np, Sri lanka', 'kandy', 'gampola', 20650, 'rice', '12', '12', '1', 'uploads/686243669ff14_5.jpg', '2025-06-30 04:27:26'),
(10, 'Jeni', 'Jeni', 'Robert', '2000-06-04', 1234567, 722348975, 'jeni@gmail.com', '$2y$10$I6Nj27.GuPtH0p4hykkuaOldYbnmBRCWfiFqzkNRZo8izIAY.ufuW', '120, nawalapitiya', 'central', 'kandy', 20650, 'rice', '12', '12', 'Yes', 'uploads/68629a253b39c_8.jpg', '2025-06-30 10:37:33'),
(11, 'nikitha', 'nikitha', 'shree', '2000-06-12', 123456789, 722348975, 'nikitha@gmail.com', '$2y$10$/mkV/m2.vWZrqwkXVBHKZOg9i2IOcOBd.qxqA.9TuJxRhbl4DEx.S', '120, nawalapitiya', 'kandy', 'kandy', 20650, 'rice', '3', '6', '1', '1751295409_1.jpg', '2025-06-30 10:47:42');

-- --------------------------------------------------------

--
-- Table structure for table `listings`
--

CREATE TABLE `listings` (
  `id` int(11) NOT NULL,
  `farmer_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `rice_type` varchar(100) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `price_per_kg` decimal(10,2) NOT NULL,
  `harvest_date` date NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `farm_address` varchar(225) NOT NULL,
  `description` varchar(225) NOT NULL,
  `created_at` date NOT NULL,
  `updated_at` date NOT NULL DEFAULT current_timestamp(),
  `is_available` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `listings`
--

INSERT INTO `listings` (`id`, `farmer_id`, `title`, `rice_type`, `quantity`, `price_per_kg`, `harvest_date`, `image_url`, `farm_address`, `description`, `created_at`, `updated_at`, `is_available`) VALUES
(7, 9, 'samba Rice', 'Nadu', 345.00, 124.00, '2025-06-06', 'uploads/img_68627601dd2854.47925654.jpg', 'kandy', 'rdfthy', '2025-06-30', '2025-06-30', 1),
(8, 11, 'white samba Rice', 'Samba', 345.00, 124.00, '2025-06-06', 'uploads/img_68629e1db279b9.07934001.jpg', 'kandy', 'gh', '2025-06-30', '2025-06-30', 1);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `farmer_id` int(11) NOT NULL,
  `listing_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `sent_by` enum('farmer','buyer') NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `buyer_listing` (`buyer_id`,`listing_id`),
  ADD KEY `listing_id` (`listing_id`);

--
-- Indexes for table `buyers`
--
ALTER TABLE `buyers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `farmers`
--
ALTER TABLE `farmers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `listings`
--
ALTER TABLE `listings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `farmer_id` (`farmer_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `farmer_id` (`farmer_id`),
  ADD KEY `listing_id` (`listing_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookmarks`
--
ALTER TABLE `bookmarks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `buyers`
--
ALTER TABLE `buyers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `farmers`
--
ALTER TABLE `farmers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `listings`
--
ALTER TABLE `listings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD CONSTRAINT `bookmarks_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `buyers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookmarks_ibfk_2` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `listings`
--
ALTER TABLE `listings`
  ADD CONSTRAINT `listings_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `buyers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
