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
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `created_at`) VALUES
(24, 'Approver', '2026-03-09 23:05:22'),
(25, 'Primary Setup', '2026-03-09 23:05:22'),
(26, 'Secondary Setup', '2026-03-09 23:05:22'),
(27, 'Accounting Supervisor', '2026-03-09 23:05:22'),
(28, 'Administrator', '2026-03-09 23:05:22'),
(29, 'Department Head', '2026-03-09 23:05:22'),
(30, 'Employee Requester', '2026-03-09 23:05:22'),
(31, 'Finance Director', '2026-03-09 23:05:22'),
(32, 'Human Resources Admin', '2026-03-09 23:05:22'),
(33, 'Payroll Admin', '2026-03-09 23:05:22'),
(34, 'SC Access', '2026-03-09 23:05:22'),
(35, 'SIC Access', '2026-03-09 23:05:22'),
(36, 'TL Access', '2026-03-09 23:05:22'),
(37, 'Checker', '2026-03-09 23:05:22'),
(38, 'HR Admin', '2026-03-09 23:05:22'),
(39, 'Requester', '2026-03-09 23:05:22'),
(40, 'Viewer', '2026-03-09 23:05:22'),
(41, 'HR Administrator', '2026-03-09 23:05:22'),
(42, 'HR Staff', '2026-03-09 23:05:22'),
(43, 'Payroll Administrator', '2026-03-09 23:05:22'),
(44, 'Program Head', '2026-03-09 23:05:22'),
(45, 'Second in Command', '2026-03-09 23:05:22'),
(46, 'Security Officer', '2026-03-09 23:05:22'),
(47, 'Head Admin', '2026-03-09 23:05:22'),
(48, 'Accountant', '2026-03-09 23:05:22'),
(49, 'Employee', '2026-03-09 23:05:22'),
(50, 'Records', '2026-03-09 23:05:22'),
(51, 'Recruitment', '2026-03-09 23:05:22'),
(52, 'Credit Card Processor', '2026-03-09 23:05:22'),
(53, 'MIS Importation', '2026-03-09 23:05:22'),
(54, 'PI Auditor', '2026-03-09 23:05:22'),
(55, 'PI Checker', '2026-03-09 23:05:22'),
(56, 'PI Creator', '2026-03-09 23:05:22'),
(57, 'Sales Director', '2026-03-09 23:05:22'),
(58, 'Accountable Asset Server', '2026-03-09 23:05:22'),
(59, 'Audit Admin', '2026-03-09 23:05:22'),
(60, 'Finance Admin', '2026-03-09 23:05:22'),
(61, 'Purchaser', '2026-03-09 23:05:22'),
(62, 'System Admin', '2026-03-09 23:05:22'),
(63, 'Academic Head', '2026-03-09 23:05:22'),
(64, 'Faculty Class Schedule Admin', '2026-03-09 23:05:22'),
(65, 'Instructor', '2026-03-09 23:05:22'),
(66, 'Lab Monitoring', '2026-03-09 23:05:22'),
(67, 'Locker Monitoring', '2026-03-09 23:05:22'),
(68, 'School President', '2026-03-09 23:05:22'),
(69, 'Timesheet Tardiness Monitoring', '2026-03-09 23:05:22'),
(70, 'Outlet', '2026-03-09 23:05:22'),
(71, 'EC Access', '2026-03-09 23:05:22'),
(72, 'Finance Access', '2026-03-09 23:05:22'),
(73, 'Marketing Admin', '2026-03-09 23:05:22'),
(103, '1 TEST ro', '2026-04-16 02:40:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
