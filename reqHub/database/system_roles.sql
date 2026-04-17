-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 16, 2026 at 10:38 AM
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
-- Table structure for table `system_roles`
--

CREATE TABLE `system_roles` (
  `id` int(11) NOT NULL,
  `system_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_roles`
--

INSERT INTO `system_roles` (`id`, `system_id`, `role_id`, `created_at`) VALUES
(175, 11, 24, '2026-04-06 09:32:35'),
(176, 11, 25, '2026-04-06 09:32:36'),
(177, 11, 26, '2026-04-06 09:32:37'),
(178, 12, 27, '2026-04-06 09:32:50'),
(179, 12, 28, '2026-04-06 09:32:52'),
(180, 12, 29, '2026-04-06 09:33:12'),
(181, 12, 30, '2026-04-06 09:33:16'),
(182, 12, 31, '2026-04-06 09:33:17'),
(183, 12, 32, '2026-04-06 09:33:21'),
(184, 12, 33, '2026-04-06 09:33:24'),
(185, 13, 28, '2026-04-06 09:34:32'),
(186, 13, 34, '2026-04-06 09:34:51'),
(187, 13, 35, '2026-04-06 09:34:52'),
(188, 13, 36, '2026-04-06 09:34:53'),
(189, 14, 37, '2026-04-06 09:36:32'),
(190, 14, 38, '2026-04-06 09:36:32'),
(191, 14, 39, '2026-04-06 09:36:33'),
(192, 14, 40, '2026-04-06 09:36:34'),
(193, 15, 28, '2026-04-06 09:36:49'),
(194, 15, 29, '2026-04-06 09:37:10'),
(195, 15, 41, '2026-04-06 09:37:14'),
(196, 15, 42, '2026-04-06 09:37:16'),
(197, 15, 43, '2026-04-06 09:37:17'),
(198, 15, 44, '2026-04-06 09:37:18'),
(199, 15, 45, '2026-04-06 09:37:22'),
(200, 15, 46, '2026-04-06 09:37:23'),
(201, 16, 47, '2026-04-06 09:38:44'),
(202, 17, 48, '2026-04-06 09:39:14'),
(203, 10, 28, '2026-04-06 09:39:19'),
(204, 10, 29, '2026-04-06 09:39:39'),
(205, 10, 49, '2026-04-06 09:39:43'),
(206, 10, 50, '2026-04-06 09:39:44'),
(207, 10, 51, '2026-04-06 09:39:46'),
(216, 18, 28, '2026-04-06 09:44:20'),
(217, 18, 24, '2026-04-06 09:44:42'),
(218, 18, 52, '2026-04-06 09:44:43'),
(219, 18, 53, '2026-04-06 09:44:44'),
(220, 18, 54, '2026-04-06 09:44:44'),
(221, 18, 55, '2026-04-06 09:44:46'),
(222, 18, 56, '2026-04-06 09:44:46'),
(223, 18, 57, '2026-04-06 09:44:50'),
(224, 19, 58, '2026-04-06 09:46:16'),
(225, 19, 59, '2026-04-06 09:46:18'),
(226, 19, 29, '2026-04-06 09:46:20'),
(227, 19, 60, '2026-04-06 09:46:23'),
(228, 19, 47, '2026-04-06 09:46:25'),
(229, 19, 61, '2026-04-06 09:46:29'),
(230, 19, 39, '2026-04-06 09:46:30'),
(231, 19, 62, '2026-04-06 09:46:32'),
(232, 20, 63, '2026-04-06 09:48:01'),
(233, 20, 28, '2026-04-06 09:48:06'),
(234, 20, 64, '2026-04-06 09:48:27'),
(235, 20, 65, '2026-04-06 09:48:28'),
(236, 20, 66, '2026-04-06 09:48:31'),
(237, 20, 67, '2026-04-06 09:48:32'),
(238, 20, 44, '2026-04-06 09:48:33'),
(239, 20, 68, '2026-04-06 09:48:38'),
(240, 20, 69, '2026-04-06 09:48:38'),
(241, 21, 28, '2026-04-06 09:49:24'),
(242, 22, 28, '2026-04-06 09:50:28'),
(243, 22, 70, '2026-04-06 09:50:50'),
(244, 23, 28, '2026-04-06 09:55:25'),
(245, 23, 71, '2026-04-06 09:55:46'),
(246, 23, 72, '2026-04-06 09:55:47'),
(247, 23, 73, '2026-04-06 09:55:48'),
(248, 23, 36, '2026-04-06 09:55:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `system_roles`
--
ALTER TABLE `system_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_system_role` (`system_id`,`role_id`),
  ADD KEY `fk_system` (`system_id`),
  ADD KEY `fk_role` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `system_roles`
--
ALTER TABLE `system_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=267;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `system_roles`
--
ALTER TABLE `system_roles`
  ADD CONSTRAINT `fk_system_roles_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_system_roles_system` FOREIGN KEY (`system_id`) REFERENCES `systems` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
