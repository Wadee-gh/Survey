-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 29, 2018 at 08:55 PM
-- Server version: 5.7.23-0ubuntu0.16.04.1
-- PHP Version: 7.0.32-0ubuntu0.16.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `survey`
--

-- --------------------------------------------------------

--
-- Table structure for table `lime_tos`
--

CREATE TABLE `lime_tos` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `tos` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `lime_tos`
--

INSERT INTO `lime_tos` (`id`, `company_id`, `tos`, `created_at`, `updated_at`) VALUES
(1, 1, 'test', '2018-09-26 18:30:00', '2018-09-29 06:23:00'),
(2, 2, 'test', '2018-09-26 18:30:00', '2018-09-29 06:12:42'),
(3, 3, 'test', '2018-09-29 06:24:48', '2018-09-29 06:24:48'),
(4, 4, 'test', '2018-09-29 06:53:04', '2018-09-29 06:54:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `lime_tos`
--
ALTER TABLE `lime_tos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `lime_tos`
--
ALTER TABLE `lime_tos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
