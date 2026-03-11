-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 10, 2026 at 10:38 AM
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
-- Table structure for table `chats`
--

CREATE TABLE `chats` (
  `id` int(11) NOT NULL,
  `request_id` int(11) DEFAULT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(129, 1, 'New access request for HRIS has been submitted.', 1, '2026-02-09 09:35:24'),
(130, 1, 'New access request for HRIS has been submitted.', 1, '2026-02-10 07:23:55');

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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `denied_by` int(11) DEFAULT NULL,
  `denied_at` datetime DEFAULT NULL,
  `served_at` datetime DEFAULT NULL,
  `served_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `requestor_id`, `system_name`, `access_type`, `remove_from`, `description`, `status`, `admin_status`, `created_at`, `updated_at`, `approved_by`, `approved_at`, `denied_by`, `denied_at`, `served_at`, `served_by`) VALUES
(90, 1, 'HRIS', 'Requestor', NULL, '', 'approved', 'pending', '2026-02-09 02:19:39', '2026-02-09 15:10:39', 1, '2026-02-09 15:10:39', NULL, NULL, '2026-02-09 10:48:59', 1),
(92, 1, 'HRIS', 'Approver', NULL, '', 'approved', 'pending', '2026-02-09 02:43:26', '2026-02-09 15:12:34', 1, '2026-02-09 15:12:34', 0, '2026-02-09 15:10:43', '2026-02-09 10:49:05', 1),
(93, 1, 'HRIS', 'Requestor', NULL, '', 'approved', 'pending', '2026-02-09 02:45:31', '2026-02-09 15:13:14', 1, '2026-02-09 15:13:14', NULL, NULL, NULL, NULL),
(94, 1, 'HRIS', 'Requestor', NULL, '', 'approved', 'pending', '2026-02-09 02:45:56', '2026-02-09 16:02:33', 1, '2026-02-09 16:02:33', 0, '2026-02-09 15:10:45', NULL, NULL),
(95, 1, 'HRIS', 'Requestor', NULL, '', 'denied', 'pending', '2026-02-09 05:08:32', '2026-02-09 16:12:31', 1, '2026-02-09 15:10:45', 0, '2026-02-09 16:12:31', NULL, NULL),
(96, 1, 'HRIS', 'Requestor', NULL, '1231231231', 'approved', 'pending', '2026-02-09 07:44:47', '2026-02-09 17:07:52', 1, '2026-02-09 17:07:52', NULL, NULL, NULL, NULL),
(97, 1, 'ZenHub', 'Requestor', NULL, '', 'pending', 'pending', '2026-02-09 07:58:22', '2026-02-09 15:58:22', NULL, NULL, NULL, NULL, NULL, NULL),
(98, 1, 'HRIS', 'Requestor', NULL, '', 'pending', 'pending', '2026-02-09 09:35:24', '2026-02-09 17:35:24', NULL, NULL, NULL, NULL, NULL, NULL),
(99, 1, 'HRIS', 'Requestor', NULL, 'notif', 'pending', 'pending', '2026-02-10 07:23:55', '2026-02-10 15:23:55', NULL, NULL, NULL, NULL, NULL, NULL);

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
