-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 12, 2022 at 01:26 PM
-- Server version: 10.4.20-MariaDB
-- PHP Version: 8.0.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `wordpress`
--

-- --------------------------------------------------------

--
-- Table structure for table `wp_sl_simlab_alat`
--

CREATE TABLE `wp_sl_simlab_alat` (
  `id` int(11) NOT NULL,
  `Nama_Alat` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Merk` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Qty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wp_sl_simlab_bahan`
--

CREATE TABLE `wp_sl_simlab_bahan` (
  `id` int(11) NOT NULL,
  `Nama_Bahan` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Jumlah` decimal(10,5) NOT NULL,
  `Satuan` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Merk` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Serial` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Exp` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Letak` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wp_sl_simlab_logbook_alat`
--

CREATE TABLE `wp_sl_simlab_logbook_alat` (
  `id` int(11) NOT NULL,
  `id_alat` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `start_date` bigint(20) NOT NULL,
  `end_date` bigint(20) NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wp_sl_simlab_logbook_bahan`
--

CREATE TABLE `wp_sl_simlab_logbook_bahan` (
  `id` int(11) NOT NULL,
  `id_bahan` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `qty` decimal(10,5) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wp_sl_simlab_status`
--

CREATE TABLE `wp_sl_simlab_status` (
  `id` int(11) NOT NULL,
  `name` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wp_sl_simlab_status`
--

INSERT INTO `wp_sl_simlab_status` (`id`, `name`) VALUES
(1, 'Ongoing'),
(2, 'Completed'),
(3, 'Pending'),
(4, 'Rejected'),
(5, 'Accepted');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `wp_sl_simlab_alat`
--
ALTER TABLE `wp_sl_simlab_alat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wp_sl_simlab_bahan`
--
ALTER TABLE `wp_sl_simlab_bahan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wp_sl_simlab_logbook_alat`
--
ALTER TABLE `wp_sl_simlab_logbook_alat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status` (`status`),
  ADD KEY `id_alat` (`id_alat`);

--
-- Indexes for table `wp_sl_simlab_logbook_bahan`
--
ALTER TABLE `wp_sl_simlab_logbook_bahan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `id_alat` (`id_bahan`);

--
-- Indexes for table `wp_sl_simlab_status`
--
ALTER TABLE `wp_sl_simlab_status`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `wp_sl_simlab_alat`
--
ALTER TABLE `wp_sl_simlab_alat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wp_sl_simlab_bahan`
--
ALTER TABLE `wp_sl_simlab_bahan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wp_sl_simlab_logbook_alat`
--
ALTER TABLE `wp_sl_simlab_logbook_alat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wp_sl_simlab_logbook_bahan`
--
ALTER TABLE `wp_sl_simlab_logbook_bahan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wp_sl_simlab_status`
--
ALTER TABLE `wp_sl_simlab_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
