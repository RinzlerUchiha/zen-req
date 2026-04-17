-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 16, 2026 at 10:41 AM
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
-- Database: `reqhub`
--

-- --------------------------------------------------------

--
-- Table structure for table `systems`
--

CREATE TABLE `systems` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `systems`
--

INSERT INTO `systems` (`id`, `name`, `created_at`) VALUES
(10, 'HRIS SYSTEM', '2026-02-13 09:52:18'),
(11, 'Annual Budget System', '2026-03-04 02:40:09'),
(12, 'Authority To Deduct', '2026-03-04 02:40:09'),
(13, 'Client Assistance Program', '2026-03-04 02:40:09'),
(14, 'Employee Clearance Form', '2026-03-04 02:40:09'),
(15, 'Electronic Daily Time Record System', '2026-03-04 02:40:09'),
(16, 'Finance Information System', '2026-03-04 02:40:09'),
(17, 'Financial Statements', '2026-03-04 02:40:09'),
(18, 'Productivity Incentive', '2026-03-04 02:40:09'),
(19, 'Requisition and Purchasing System', '2026-03-04 02:40:09'),
(20, 'Students Information Systems', '2026-03-04 02:40:09'),
(21, 'SJI Information System', '2026-03-04 02:40:09'),
(22, 'Sophia Events', '2026-03-04 02:40:09'),
(23, 'Voucher Monitoring System', '2026-03-04 02:40:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `systems`
--
ALTER TABLE `systems`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `systems`
--
ALTER TABLE `systems`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
