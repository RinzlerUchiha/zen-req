-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 16, 2026 at 10:57 AM
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
-- Table structure for table `actions`
--

CREATE TABLE `actions` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `actions`
--

INSERT INTO `actions` (`id`, `name`, `created_at`) VALUES
(59, 'Add Comments', '2026-03-09 23:01:59'),
(60, 'Add Remarks', '2026-03-09 23:01:59'),
(61, 'Delete Comment', '2026-03-09 23:01:59'),
(62, 'Delete Remarks', '2026-03-09 23:01:59'),
(63, 'Edit Remarks', '2026-03-09 23:01:59'),
(64, 'Resolve Comment', '2026-03-09 23:01:59'),
(65, 'Archive Budget', '2026-03-09 23:01:59'),
(66, 'Create Budget', '2026-03-09 23:01:59'),
(67, 'Encode', '2026-03-09 23:01:59'),
(68, 'Add Budget Item', '2026-03-09 23:01:59'),
(69, 'Edit Budget item', '2026-03-09 23:01:59'),
(70, 'Remove Budget Item', '2026-03-09 23:01:59'),
(71, 'Mark file as approved', '2026-03-09 23:01:59'),
(72, 'Mark file as archived', '2026-03-09 23:01:59'),
(73, 'Mark file as completed', '2026-03-09 23:01:59'),
(74, 'Mark file as done', '2026-03-09 23:01:59'),
(75, 'Mark file as draft', '2026-03-09 23:01:59'),
(76, 'Mark file as others', '2026-03-09 23:01:59'),
(77, 'Mark file as reviewed', '2026-03-09 23:01:59'),
(78, 'View', '2026-03-09 23:01:59'),
(79, 'View per Company', '2026-03-09 23:01:59'),
(80, 'Delete', '2026-03-09 23:01:59'),
(81, 'Edit', '2026-03-09 23:01:59'),
(82, 'View Own', '2026-03-09 23:01:59'),
(83, 'Add', '2026-03-09 23:01:59'),
(84, 'Print', '2026-03-09 23:01:59'),
(85, 'Comment on ATD Reque', '2026-03-09 23:01:59'),
(86, 'Reply on ATD Request', '2026-03-09 23:01:59'),
(87, 'Review ATD Request', '2026-03-09 23:01:59'),
(88, 'View All', '2026-03-09 23:01:59'),
(89, 'Activate', '2026-03-09 23:01:59'),
(90, 'Deactivate', '2026-03-09 23:01:59'),
(91, 'Check ATD request', '2026-03-09 23:01:59'),
(92, 'View per Department', '2026-03-09 23:01:59'),
(93, 'Approve ATD Request', '2026-03-09 23:01:59'),
(94, 'Confirm ATD Request', '2026-03-09 23:01:59'),
(95, 'Reprint CAP', '2026-03-09 23:01:59'),
(96, 'Reprint Transaction', '2026-03-09 23:01:59'),
(97, 'Close Transaction', '2026-03-09 23:01:59'),
(98, 'Payment', '2026-03-09 23:01:59'),
(99, 'View Menu', '2026-03-09 23:01:59'),
(100, 'Transfer In', '2026-03-09 23:01:59'),
(101, 'Transfer Out', '2026-03-09 23:01:59'),
(102, 'Release', '2026-03-09 23:01:59'),
(103, 'Compute', '2026-03-09 23:01:59'),
(104, 'Clear', '2026-03-09 23:01:59'),
(105, 'Print DC', '2026-03-09 23:01:59'),
(106, 'Print FC', '2026-03-09 23:01:59'),
(107, 'Approve', '2026-03-09 23:01:59'),
(108, 'Cancel', '2026-03-09 23:01:59'),
(109, 'Change Date', '2026-03-09 23:01:59'),
(110, 'Batch Add', '2026-03-09 23:01:59'),
(111, 'Batch Delete', '2026-03-09 23:01:59'),
(112, 'Batch Edit', '2026-03-09 23:01:59'),
(113, 'Batch View', '2026-03-09 23:01:59'),
(114, 'Export Reports', '2026-03-09 23:01:59'),
(115, 'Generate Reports All', '2026-03-09 23:01:59'),
(116, 'Generate Reports by Dept', '2026-03-09 23:01:59'),
(117, 'Disapprove', '2026-03-09 23:01:59'),
(118, 'View By Dept', '2026-03-09 23:01:59'),
(119, 'N', '2026-03-09 23:01:59'),
(120, 'View Home', '2026-03-09 23:01:59'),
(121, 'View Imports', '2026-03-09 23:01:59'),
(122, 'View Income Statement', '2026-03-09 23:01:59'),
(123, 'View Maintenance', '2026-03-09 23:01:59'),
(124, 'View Detailed Summary', '2026-03-09 23:01:59'),
(125, 'Benefits', '2026-03-09 23:01:59'),
(126, 'HDMF', '2026-03-09 23:01:59'),
(127, 'PHIC', '2026-03-09 23:01:59'),
(128, 'Salary', '2026-03-09 23:01:59'),
(129, 'SSS', '2026-03-09 23:01:59'),
(130, 'Withholding Tax', '2026-03-09 23:01:59'),
(131, 'Direct Add', '2026-03-09 23:01:59'),
(132, 'Direct Delete', '2026-03-09 23:01:59'),
(133, 'Direct Edit', '2026-03-09 23:01:59'),
(134, 'Review', '2026-03-09 23:01:59'),
(135, 'Viewer', '2026-03-09 23:01:59'),
(136, 'Company Settings', '2026-03-09 23:01:59'),
(137, 'Demo Teaching Settings', '2026-03-09 23:01:59'),
(138, 'Department Settings', '2026-03-09 23:01:59'),
(139, 'Education Settings', '2026-03-09 23:01:59'),
(140, 'Employment Settings', '2026-03-09 23:01:59'),
(141, 'Group Role Settings', '2026-03-09 23:01:59'),
(142, 'Individual Role Settings', '2026-03-09 23:01:59'),
(143, 'Job Settings', '2026-03-09 23:01:59'),
(144, 'Modules Settings', '2026-03-09 23:01:59'),
(145, 'Outlet Settings', '2026-03-09 23:01:59'),
(146, 'Section Settings', '2026-03-09 23:01:59'),
(147, 'Time Off Settings', '2026-03-09 23:01:59'),
(148, 'User Settings', '2026-03-09 23:01:59'),
(149, 'Request to Add', '2026-03-09 23:01:59'),
(150, 'Request to Delete', '2026-03-09 23:01:59'),
(151, 'Request to Edit', '2026-03-09 23:01:59'),
(152, 'Generate', '2026-03-09 23:01:59'),
(153, 'View All Request', '2026-03-09 23:01:59'),
(154, 'View by Department', '2026-03-09 23:01:59'),
(155, 'View by Company', '2026-03-09 23:01:59'),
(156, 'Override', '2026-03-09 23:01:59'),
(157, 'Back', '2026-03-09 23:01:59'),
(158, 'Export Report', '2026-03-09 23:01:59'),
(159, 'Create Class', '2026-03-09 23:01:59'),
(160, 'Enroll Students', '2026-03-09 23:01:59'),
(161, 'Disapprove Timesheet', '2026-03-09 23:01:59'),
(162, 'Review Timesheet', '2026-03-09 23:01:59'),
(163, 'View Timesheet', '2026-03-09 23:01:59'),
(164, 'Sub Teaching', '2026-03-09 23:01:59'),
(165, 'Time In', '2026-03-09 23:01:59'),
(166, 'Time Out', '2026-03-09 23:01:59'),
(167, 'Add Timesheet Remarks', '2026-03-09 23:01:59'),
(168, 'Check Timesheet', '2026-03-09 23:01:59'),
(169, 'Generate Timesheet', '2026-03-09 23:01:59'),
(170, 'Post Timesheet', '2026-03-09 23:01:59'),
(171, 'Request for Approval Tab', '2026-03-09 23:01:59'),
(172, 'Timesheet Approved Tab', '2026-03-09 23:01:59'),
(173, 'Timesheet Draft Tab', '2026-03-09 23:01:59'),
(174, 'Timesheet Post Tab', '2026-03-09 23:01:59'),
(175, 'Timesheet Review Tab', '2026-03-09 23:01:59'),
(176, 'Timesheet Summary Tab', '2026-03-09 23:01:59'),
(177, 'Change PC', '2026-03-09 23:01:59'),
(178, 'Utilization', '2026-03-09 23:01:59'),
(179, 'Extract', '2026-03-09 23:01:59'),
(180, 'View per Outlet', '2026-03-09 23:01:59');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `actions`
--
ALTER TABLE `actions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `actions`
--
ALTER TABLE `actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=201;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
