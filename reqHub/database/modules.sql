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
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `name`, `created_at`) VALUES
(34, 'Annotations', '2026-03-09 23:04:58'),
(35, 'Budget File', '2026-03-09 23:04:58'),
(36, 'Budget Item', '2026-03-09 23:04:58'),
(37, 'Budget Status', '2026-03-09 23:04:58'),
(38, 'Approved', '2026-03-09 23:04:58'),
(39, 'Checked', '2026-03-09 23:04:58'),
(40, 'Dashboard', '2026-03-09 23:04:58'),
(41, 'Draft', '2026-03-09 23:04:58'),
(42, 'Employee Request', '2026-03-09 23:04:58'),
(43, 'Pending', '2026-03-09 23:04:58'),
(44, 'Request', '2026-03-09 23:04:58'),
(45, 'Request Process', '2026-03-09 23:04:58'),
(46, 'Reviewed', '2026-03-09 23:04:58'),
(47, 'Accounting Request', '2026-03-09 23:04:58'),
(48, 'Assignatory', '2026-03-09 23:04:58'),
(49, 'ATD Type', '2026-03-09 23:04:58'),
(50, 'Attachment', '2026-03-09 23:04:58'),
(51, 'Category', '2026-03-09 23:04:58'),
(52, 'Clarify', '2026-03-09 23:04:58'),
(53, 'Company', '2026-03-09 23:04:58'),
(54, 'Confirmed', '2026-03-09 23:04:58'),
(55, 'Fully Paid', '2026-03-09 23:04:58'),
(56, 'Human Resource Request', '2026-03-09 23:04:58'),
(57, 'Item', '2026-03-09 23:04:58'),
(58, 'Maintenance', '2026-03-09 23:04:58'),
(59, 'Request ATD', '2026-03-09 23:04:58'),
(60, 'System Logs', '2026-03-09 23:04:58'),
(61, 'Unprocessed', '2026-03-09 23:04:58'),
(62, 'User', '2026-03-09 23:04:58'),
(63, 'Verified', '2026-03-09 23:04:58'),
(64, 'CAP Details', '2026-03-09 23:04:58'),
(65, 'Forfeited', '2026-03-09 23:04:58'),
(66, 'Interest Rate Mn', '2026-03-09 23:04:58'),
(67, 'Payment', '2026-03-09 23:04:58'),
(68, 'Payment Term Mn', '2026-03-09 23:04:58'),
(69, 'View Menu', '2026-03-09 23:04:58'),
(70, 'Archive', '2026-03-09 23:04:58'),
(71, 'System Log', '2026-03-09 23:04:58'),
(72, 'Print', '2026-03-09 23:04:58'),
(73, 'Requirements', '2026-03-09 23:04:58'),
(74, 'Separation Type', '2026-03-09 23:04:58'),
(75, 'Batch Gatepass', '2026-03-09 23:04:58'),
(76, 'eDTR Breakdown', '2026-03-09 23:04:58'),
(77, 'eDTR Cut-Off Reports', '2026-03-09 23:04:58'),
(78, 'eDTR Information', '2026-03-09 23:04:58'),
(79, 'eDTR Inputs', '2026-03-09 23:04:58'),
(80, 'Facetime', '2026-03-09 23:04:58'),
(81, 'Fingerprint Details', '2026-03-09 23:04:58'),
(82, 'Gatepass', '2026-03-09 23:04:58'),
(83, 'Overtime', '2026-03-09 23:04:58'),
(84, '(SIS unnamed module)', '2026-03-09 23:04:58'),
(85, 'Home', '2026-03-09 23:04:58'),
(86, 'Imports', '2026-03-09 23:04:58'),
(87, 'Income Statements', '2026-03-09 23:04:58'),
(88, 'Reports', '2026-03-09 23:04:58'),
(89, 'Announcement', '2026-03-09 23:04:58'),
(90, 'Compensation & Benefits', '2026-03-09 23:04:58'),
(91, 'Education & Background', '2026-03-09 23:04:58'),
(92, 'Employee Application Profile', '2026-03-09 23:04:58'),
(93, 'Employee Education & Background', '2026-03-09 23:04:58'),
(94, 'Employee Engagement Index', '2026-03-09 23:04:58'),
(95, 'Employee Job Information', '2026-03-09 23:04:58'),
(96, 'Employee List', '2026-03-09 23:04:58'),
(97, 'Employee Personal Information', '2026-03-09 23:04:58'),
(98, 'Exit Interview', '2026-03-09 23:04:58'),
(99, 'Feedback', '2026-03-09 23:04:58'),
(100, 'Grievance', '2026-03-09 23:04:58'),
(101, 'Holiday', '2026-03-09 23:04:58'),
(102, 'Info Update Request', '2026-03-09 23:04:58'),
(103, 'Job Information', '2026-03-09 23:04:58'),
(104, 'Job Opening', '2026-03-09 23:04:58'),
(105, 'Kamustahan', '2026-03-09 23:04:58'),
(106, 'Organization Chart', '2026-03-09 23:04:58'),
(107, 'Payroll', '2026-03-09 23:04:58'),
(108, 'Performance Appraisal', '2026-03-09 23:04:58'),
(109, 'Personal Information', '2026-03-09 23:04:58'),
(110, 'Personality Test', '2026-03-09 23:04:58'),
(111, 'Personnel Request', '2026-03-09 23:04:58'),
(112, 'Phone Contract', '2026-03-09 23:04:58'),
(113, 'Post', '2026-03-09 23:04:58'),
(114, 'Reminder', '2026-03-09 23:04:58'),
(115, 'Settings', '2026-03-09 23:04:58'),
(116, 'Time Off', '2026-03-09 23:04:58'),
(117, 'Training', '2026-03-09 23:04:58'),
(118, 'About the company', '2026-03-09 23:04:58'),
(119, 'Adjustments Mn', '2026-03-09 23:04:58'),
(120, 'Collection Rate', '2026-03-09 23:04:58'),
(121, 'Distribution', '2026-03-09 23:04:58'),
(122, 'EC Breakdown', '2026-03-09 23:04:58'),
(123, 'Employee Information', '2026-03-09 23:04:58'),
(124, 'Generate Menu', '2026-03-09 23:04:58'),
(125, 'Guidelines', '2026-03-09 23:04:58'),
(126, 'Import Menu', '2026-03-09 23:04:58'),
(127, 'Input Menu', '2026-03-09 23:04:58'),
(128, 'Manpower Movement', '2026-03-09 23:04:58'),
(129, 'MBTC', '2026-03-09 23:04:58'),
(130, 'MBTC Maintenance', '2026-03-09 23:04:58'),
(131, 'Needs Explanation', '2026-03-09 23:04:58'),
(132, 'Paymaster', '2026-03-09 23:04:58'),
(133, 'PI Creation', '2026-03-09 23:04:58'),
(134, 'PI Credit Card', '2026-03-09 23:04:58'),
(135, 'PI Entry', '2026-03-09 23:04:58'),
(136, 'PI Entry Maintenance', '2026-03-09 23:04:58'),
(137, 'PI Incentives Import', '2026-03-09 23:04:58'),
(138, 'PI SO Maintenance', '2026-03-09 23:04:58'),
(139, 'PI TL & ASH KPI', '2026-03-09 23:04:58'),
(140, 'PI TL&ASH Assigned Area', '2026-03-09 23:04:58'),
(141, 'Report', '2026-03-09 23:04:58'),
(142, 'Tardiness', '2026-03-09 23:04:58'),
(143, 'Target', '2026-03-09 23:04:58'),
(144, 'TL Trainee Maintenance', '2026-03-09 23:04:58'),
(145, 'View Approved', '2026-03-09 23:04:58'),
(146, 'View Audited', '2026-03-09 23:04:58'),
(147, 'View Checked', '2026-03-09 23:04:58'),
(148, 'View Draft', '2026-03-09 23:04:58'),
(149, 'View Noted', '2026-03-09 23:04:58'),
(150, 'View Request', '2026-03-09 23:04:58'),
(151, 'Viewing menu', '2026-03-09 23:04:58'),
(152, 'Accountable Asset Request', '2026-03-09 23:04:58'),
(153, 'Approved Acct Request', '2026-03-09 23:04:58'),
(154, 'Approved Item Request', '2026-03-09 23:04:58'),
(155, 'Cancelled Acct Request', '2026-03-09 23:04:58'),
(156, 'Cancelled Item Request', '2026-03-09 23:04:58'),
(157, 'Checked Item Request', '2026-03-09 23:04:58'),
(158, 'Closed Acct Request', '2026-03-09 23:04:58'),
(159, 'Closed Item Request', '2026-03-09 23:04:58'),
(160, 'Item List', '2026-03-09 23:04:58'),
(161, 'Item Request', '2026-03-09 23:04:58'),
(162, 'Needs Explanation Acct Request', '2026-03-09 23:04:58'),
(163, 'Needs Explanation Item Request', '2026-03-09 23:04:58'),
(164, 'Pending Acct Request', '2026-03-09 23:04:58'),
(165, 'Pending Item Request', '2026-03-09 23:04:58'),
(166, 'Reviewed Item Request', '2026-03-09 23:04:58'),
(167, 'Served Acct Request', '2026-03-09 23:04:58'),
(168, 'Served Item Request', '2026-03-09 23:04:58'),
(169, 'Cancelled PO', '2026-03-09 23:04:58'),
(170, 'Closed PO', '2026-03-09 23:04:58'),
(171, 'Draft PO', '2026-03-09 23:04:58'),
(172, 'Needs Explanation PO', '2026-03-09 23:04:58'),
(173, 'Open PO', '2026-03-09 23:04:58'),
(174, 'Pending PO', '2026-03-09 23:04:58'),
(175, 'Purchase Order', '2026-03-09 23:04:58'),
(176, 'Academic Head Dashboard', '2026-03-09 23:04:58'),
(177, 'Academic Head Intervention Report', '2026-03-09 23:04:58'),
(178, 'Class', '2026-03-09 23:04:58'),
(179, 'Class Attendance Submission Report', '2026-03-09 23:04:58'),
(180, 'Department', '2026-03-09 23:04:58'),
(181, 'Faculty Dashboard', '2026-03-09 23:04:58'),
(182, 'Faculty Report', '2026-03-09 23:04:58'),
(183, 'Faculty Schedule', '2026-03-09 23:04:58'),
(184, 'Instructor', '2026-03-09 23:04:58'),
(185, 'PH/AH Timesheet Dashboard', '2026-03-09 23:04:58'),
(186, 'Program', '2026-03-09 23:04:58'),
(187, 'Reports Menu', '2026-03-09 23:04:58'),
(188, 'Schedules', '2026-03-09 23:04:58'),
(189, 'Section', '2026-03-09 23:04:58'),
(190, 'Student', '2026-03-09 23:04:58'),
(191, 'Student Schedules', '2026-03-09 23:04:58'),
(192, 'Subject', '2026-03-09 23:04:58'),
(193, 'Timesheet', '2026-03-09 23:04:58'),
(194, 'View Class Schedule', '2026-03-09 23:04:58'),
(195, 'Admin Dashboard', '2026-03-09 23:04:58'),
(196, 'Faculty Tardiness', '2026-03-09 23:04:58'),
(197, 'Faculty Timesheet Dashboard', '2026-03-09 23:04:58'),
(198, 'Timesheet Dashboard', '2026-03-09 23:04:58'),
(199, 'Timesheet Tabs', '2026-03-09 23:04:58'),
(200, 'Utilization', '2026-03-09 23:04:58'),
(201, 'EA Dashboard', '2026-03-09 23:04:58'),
(202, 'Coordinator Submission Details', '2026-03-09 23:04:58'),
(203, 'My Schedules', '2026-03-09 23:04:58'),
(204, 'Student Details Report', '2026-03-09 23:04:58'),
(205, 'Lab Dashboard', '2026-03-09 23:04:58'),
(206, 'Lab Report', '2026-03-09 23:04:58'),
(207, 'Locker Dashboard', '2026-03-09 23:04:58'),
(208, 'Locker Logs', '2026-03-09 23:04:58'),
(209, 'Locker Maintenance', '2026-03-09 23:04:58'),
(210, 'Locker Reports', '2026-03-09 23:04:58'),
(211, 'Locker Requisition', '2026-03-09 23:04:58'),
(212, 'Locker School Year', '2026-03-09 23:04:58'),
(213, 'Locker Semester', '2026-03-09 23:04:58'),
(214, 'Adviser Report', '2026-03-09 23:04:58'),
(215, 'PH Attendance Submission Details', '2026-03-09 23:04:58'),
(216, 'Program Head Dashboard', '2026-03-09 23:04:58'),
(217, 'Program Head Reports', '2026-03-09 23:04:58'),
(218, 'School President Dashboard', '2026-03-09 23:04:58'),
(219, 'Generate Paymaster', '2026-03-09 23:04:58'),
(220, 'Targets', '2026-03-09 23:04:58'),
(221, 'Walk-in', '2026-03-09 23:04:58'),
(222, 'Event', '2026-03-09 23:04:58'),
(223, 'Participant', '2026-03-09 23:04:58'),
(224, 'Unlisted Participant', '2026-03-09 23:04:58'),
(225, 'Charts', '2026-03-09 23:04:58'),
(226, 'Critical Level', '2026-03-09 23:04:58'),
(227, 'Deactivate GC', '2026-03-09 23:04:58'),
(228, 'Deactivation Maintenance', '2026-03-09 23:04:58'),
(229, 'Prospect Clients Mktg', '2026-03-09 23:04:58'),
(230, 'Redemption Template', '2026-03-09 23:04:58'),
(231, 'Released Voucher', '2026-03-09 23:04:58'),
(232, 'Supermarket', '2026-03-09 23:04:58'),
(233, 'Transfer In', '2026-03-09 23:04:58'),
(234, 'Transfer Out', '2026-03-09 23:04:58'),
(235, 'Voucher', '2026-03-09 23:04:58'),
(236, 'Voucher Deactivation', '2026-03-09 23:04:58'),
(237, 'Voucher Details', '2026-03-09 23:04:58'),
(238, 'Voucher Distribution', '2026-03-09 23:04:58'),
(239, 'Voucher Inventory', '2026-03-09 23:04:58'),
(240, 'Voucher Releasing', '2026-03-09 23:04:58'),
(241, 'Alert', '2026-03-09 23:04:58'),
(242, 'Menu', '2026-03-09 23:04:58'),
(243, 'Partial Releasing', '2026-03-09 23:04:58'),
(244, 'Voucher Monitoring', '2026-03-09 23:04:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=302;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
