-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: mysql
-- Generation Time: Jan 27, 2025 at 01:28 PM
-- Server version: 9.1.0
-- PHP Version: 8.2.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kreas`
--

-- --------------------------------------------------------

--
-- Table structure for table `tblOrder`
--

CREATE TABLE `tblOrder` (
  `id` int NOT NULL,
  `destination_country` varchar(250) NOT NULL,
  `sold_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblOrderProducts`
--

CREATE TABLE `tblOrderProducts` (
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblProduct`
--

CREATE TABLE `tblProduct` (
  `id` int NOT NULL,
  `name` varchar(250) NOT NULL,
  `saved_co2` int NOT NULL COMMENT 'in grams'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `viewOrderProducts`
-- (See below for the actual view)
--
CREATE TABLE `viewOrderProducts` (
`order_id` int
,`product_id` int
,`product_name` varchar(250)
,`quantity` int
,`saved_co2` int
,`destination_country` varchar(250)
,`sold_on` datetime
);

-- --------------------------------------------------------

--
-- Structure for view `viewOrderProducts`
--
DROP TABLE IF EXISTS `viewOrderProducts`;

CREATE ALGORITHM=UNDEFINED DEFINER=`kreas`@`%` SQL SECURITY DEFINER VIEW `viewOrderProducts`  AS SELECT `tblOrderProducts`.`order_id` AS `order_id`, `tblProduct`.`id` AS `product_id`, `tblProduct`.`name` AS `product_name`, `tblOrderProducts`.`quantity` AS `quantity`, `tblProduct`.`saved_co2` AS `saved_co2`, `tblOrder`.`destination_country` AS `destination_country`, `tblOrder`.`sold_on` AS `sold_on` FROM ((`tblOrderProducts` left join `tblProduct` on((`tblOrderProducts`.`product_id` = `tblProduct`.`id`))) left join `tblOrder` on((`tblOrderProducts`.`order_id` = `tblOrder`.`id`))) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tblOrder`
--
ALTER TABLE `tblOrder`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblOrderProducts`
--
ALTER TABLE `tblOrderProducts`
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `tblProduct`
--
ALTER TABLE `tblProduct`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tblOrder`
--
ALTER TABLE `tblOrder`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblProduct`
--
ALTER TABLE `tblProduct`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tblOrderProducts`
--
ALTER TABLE `tblOrderProducts`
  ADD CONSTRAINT `order_id` FOREIGN KEY (`order_id`) REFERENCES `tblOrder` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `product_id` FOREIGN KEY (`product_id`) REFERENCES `tblProduct` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
