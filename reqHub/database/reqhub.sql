-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 08, 2026 at 05:46 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

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
-- Table structure for table `chats`
--

CREATE TABLE `chats` (
  `id` int(11) NOT NULL,
  `request_id` int(11) DEFAULT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(34, 1, 'New access request for HRIS has been submitted.', 0, '2026-02-08 05:23:31'),
(35, 1, 'New access request for HRIS has been submitted.', 0, '2026-02-08 05:23:33'),
(36, 1, 'New access request for HRIS has been submitted.', 0, '2026-02-08 05:23:34'),
(37, 1, 'New access request for HRIS has been submitted.', 0, '2026-02-08 05:23:36'),
(38, 1, 'New access request for HRIS has been submitted.', 0, '2026-02-08 05:23:37'),
(39, 1, 'New access request for HRIS has been submitted.', 0, '2026-02-08 05:23:38'),
(40, 1, 'New access request for HRIS has been submitted.', 0, '2026-02-08 05:24:06'),
(41, 1, 'New access request for HRIS has been submitted.', 0, '2026-02-08 05:24:14'),
(42, 1, 'Your access request #23 has been approved.', 0, '2026-02-08 05:24:22'),
(43, 1, 'Your access request #25 has been approved.', 0, '2026-02-08 05:24:24'),
(44, 1, 'Your request 24 has been denied.', 0, '2026-02-08 05:24:40'),
(45, 1, 'Your request 26 has been denied.', 0, '2026-02-08 05:24:40'),
(46, 1, 'Your access request #27 has been approved.', 0, '2026-02-08 05:24:46'),
(47, 1, 'Your access request #28 has been approved.', 0, '2026-02-08 05:30:14'),
(48, 1, 'New access request for HRIS has been submitted.', 0, '2026-02-08 05:36:32'),
(49, 1, 'New access request for HRIS has been submitted.', 0, '2026-02-08 05:38:25'),
(50, 1, 'New access request for HRIS has been submitted.', 0, '2026-02-08 05:38:28'),
(51, 1, 'New access request for HRIS has been submitted.', 0, '2026-02-08 05:38:30'),
(52, 1, 'New access request for HRIS has been submitted.', 0, '2026-02-08 05:38:35'),
(53, 1, 'New access request for HRIS has been submitted.', 0, '2026-02-08 05:38:37'),
(54, 1, 'New access request for HRIS has been submitted.', 0, '2026-02-08 05:38:39'),
(55, 1, 'New access request for HRIS has been submitted.', 0, '2026-02-08 05:38:42'),
(56, 1, 'New access request for HRIS has been submitted.', 0, '2026-02-08 05:38:43'),
(57, 1, 'New access request for HRIS has been submitted.', 0, '2026-02-08 05:38:53'),
(58, 1, 'New access request for HRIS has been submitted.', 0, '2026-02-08 05:38:57'),
(59, 1, 'Your access request #32 has been approved.', 0, '2026-02-08 05:39:40'),
(60, 1, 'Your access request #33 has been approved.', 0, '2026-02-08 05:39:49'),
(61, 1, 'Your access request #34 has been approved.', 0, '2026-02-08 05:39:53'),
(62, 1, 'Your request 39 has been denied.', 0, '2026-02-08 05:45:36'),
(63, 1, 'Your request 40 has been denied.', 0, '2026-02-08 05:45:40'),
(64, 1, 'Your request 37 has been denied.', 0, '2026-02-08 05:53:38'),
(65, 1, 'Your request 38 has been denied.', 0, '2026-02-08 05:53:42'),
(66, 1, 'Your access request #39 has been approved.', 0, '2026-02-08 06:00:47'),
(67, 1, 'Your request 40 has been denied.', 0, '2026-02-08 06:00:56'),
(68, 1, 'Your request 41 has been denied.', 0, '2026-02-08 06:01:02'),
(69, 1, 'Your request 35 has been denied.', 0, '2026-02-08 06:07:21'),
(70, 1, 'Your request 41 has been denied.', 0, '2026-02-08 06:07:27'),
(71, 1, 'Your request 40 has been denied.', 0, '2026-02-08 06:11:18'),
(72, 1, 'Your request 39 has been denied.', 0, '2026-02-08 06:11:23'),
(73, 1, 'Your request 41 has been denied.', 0, '2026-02-08 06:11:49'),
(74, 1, 'Your access request #34 has been approved.', 0, '2026-02-08 06:11:56'),
(75, 1, 'Your access request #35 has been approved.', 0, '2026-02-08 06:11:56'),
(76, 1, 'Your access request #36 has been approved.', 0, '2026-02-08 06:11:56'),
(77, 1, 'Your access request #37 has been approved.', 0, '2026-02-08 06:11:56'),
(78, 1, 'Your access request #38 has been approved.', 0, '2026-02-08 06:11:56'),
(79, 1, 'Your access request #39 has been approved.', 0, '2026-02-08 06:11:56'),
(80, 1, 'Your access request #40 has been approved.', 0, '2026-02-08 06:11:57');

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int(11) NOT NULL,
  `requestor_id` int(11) DEFAULT NULL,
  `system_name` varchar(100) DEFAULT NULL,
  `access_type` varchar(50) DEFAULT NULL,
  `remove_from` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','approved','denied','served') DEFAULT 'pending',
  `admin_status` enum('pending','served') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `denied_by` int(11) DEFAULT NULL,
  `denied_at` datetime DEFAULT NULL,
  `served_by` int(11) DEFAULT NULL,
  `served_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `requestor_id`, `system_name`, `access_type`, `remove_from`, `description`, `status`, `admin_status`, `created_at`, `approved_by`, `approved_at`, `denied_by`, `denied_at`, `served_by`, `served_at`) VALUES
(34, 1, 'HRIS', 'Requestor', NULL, '13', 'approved', NULL, '2026-02-08 05:38:30', 1, '2026-02-08 14:11:56', 1, '2026-02-08 13:49:08', NULL, NULL),
(35, 1, 'HRIS', 'Requestor', NULL, '14', 'approved', 'served', '2026-02-08 05:38:35', 1, '2026-02-08 14:11:56', 0, '0000-00-00 00:00:00', 1, '2026-02-08 14:18:31'),
(36, 1, 'HRIS', 'Requestor', NULL, '15', 'approved', 'served', '2026-02-08 05:38:37', 1, '2026-02-08 14:11:56', 0, '2026-02-08 13:53:28', 1, '2026-02-08 14:28:44'),
(37, 1, 'HRIS', 'Requestor', NULL, '16', 'approved', 'served', '2026-02-08 05:38:39', 1, '2026-02-08 14:11:56', 0, '0000-00-00 00:00:00', 1, '2026-02-08 14:28:53'),
(38, 1, 'HRIS', 'Requestor', NULL, '17', 'approved', 'served', '2026-02-08 05:38:42', 1, '2026-02-08 14:11:56', 0, '0000-00-00 00:00:00', 1, '2026-02-08 14:28:56'),
(39, 1, 'HRIS', 'Requestor', NULL, '18', 'approved', 'served', '2026-02-08 05:38:43', 1, '2026-02-08 14:11:56', 0, '2026-02-08 14:11:23', 1, '2026-02-08 14:28:59'),
(40, 1, 'HRIS', 'Requestor', NULL, '19', 'approved', 'served', '2026-02-08 05:38:53', 1, '2026-02-08 14:11:57', 0, '2026-02-08 14:11:18', 1, '2026-02-08 14:29:01'),
(41, 1, 'HRIS', 'Requestor', NULL, '20', 'approved', 'served', '2026-02-08 05:38:57', NULL, NULL, 0, '2026-02-08 14:11:49', 1, '2026-02-08 14:29:07');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `role` enum('requestor','approver','admin') DEFAULT NULL,
  `system_assigned` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chats`
--
ALTER TABLE `chats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
