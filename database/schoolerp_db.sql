-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 21, 2025 at 08:21 AM
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
-- Database: `schoolerp_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_calendar`
--

CREATE TABLE `academic_calendar` (
  `id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `event_title` varchar(255) NOT NULL,
  `event_type` enum('Holiday','Exam','Event','Meeting','Other') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `academic_sessions`
--

CREATE TABLE `academic_sessions` (
  `id` int(11) NOT NULL,
  `session_name` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `academic_sessions`
--

INSERT INTO `academic_sessions` (`id`, `session_name`, `start_date`, `end_date`, `is_active`, `created_at`) VALUES
(1, '2025-2026', '2025-01-01', '2025-12-31', 1, '2025-11-13 07:26:12');

-- --------------------------------------------------------

--
-- Table structure for table `actions`
--

CREATE TABLE `actions` (
  `id` int(11) NOT NULL,
  `action_key` varchar(50) NOT NULL COMMENT 'Unique identifier (e.g., create, view, update, delete)',
  `action_name` varchar(100) NOT NULL COMMENT 'Display name (e.g., Create, View, Update)',
  `action_description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0 COMMENT 'Order for display in admin interface',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `actions`
--

INSERT INTO `actions` (`id`, `action_key`, `action_name`, `action_description`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'create', 'Create', 'Create new records', 1, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(2, 'view', 'View', 'View and read records', 2, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(3, 'update', 'Update', 'Edit and modify existing records', 3, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(4, 'delete', 'Delete', 'Delete records', 4, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(5, 'approve', 'Approve', 'Approve requests and applications', 5, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(6, 'reject', 'Reject', 'Reject requests and applications', 6, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(7, 'export', 'Export', 'Export data to files (PDF, Excel, CSV)', 7, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(8, 'print', 'Print', 'Print documents and reports', 8, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(9, 'import', 'Import', 'Import data from files', 9, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(10, 'manage', 'Manage', 'Full management access (all actions)', 10, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16');

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `module`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 07:26:23'),
(2, 1, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 06:12:34'),
(3, 1, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 14:41:03'),
(4, 1, 'Add Class', 'Academics', 'Added class: CA202', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 15:49:35'),
(5, 1, 'Add Section', 'Academics', 'Added section: CA202', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 15:54:11'),
(6, 1, 'Add Student', 'Students', 'Added student: Abdulkadir Ibrahim (ID: STU000001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 15:56:12'),
(7, 1, 'Update Student', 'Students', 'Updated student: Abdulkadir Ibrahim (ID: STU000001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 15:59:56'),
(8, 1, 'Add Book', 'Library', 'Added book: tarbiyo (tarbiyo)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 16:10:39'),
(9, 1, 'Mark Attendance', 'Attendance', 'Marked attendance for 1 students on 2025-11-22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 16:15:18'),
(10, 1, 'Mark Attendance', 'Attendance', 'Marked attendance for 1 students on 2025-11-22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 16:15:24'),
(11, 1, 'Mark Attendance', 'Attendance', 'Marked attendance for 1 students on 2025-11-22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 16:15:28'),
(12, 1, 'Mark Attendance', 'Attendance', 'Marked attendance for 1 students on 2025-11-22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 16:15:33'),
(13, 1, 'Mark Attendance', 'Attendance', 'Marked attendance for 1 students on 2025-11-22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 16:15:35'),
(14, 1, 'Mark Attendance', 'Attendance', 'Marked attendance for 1 students on 2025-11-22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 16:15:39'),
(15, 1, 'Generate Invoice', 'Fees', 'Generated invoice: INV000001', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 16:24:21'),
(16, 1, 'Add Subject', 'Academics', 'Added subject: Math (MATH101)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 16:52:48'),
(17, 1, 'Promote Students', 'Students', 'Promoted 1 students', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 16:57:26'),
(18, 1, 'Create Application', 'Admissions', 'Created application: APP000001', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 17:13:06'),
(19, 1, 'Update Application Status', 'Admissions', 'Updated application APP000001 to status: Accepted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 17:21:51'),
(20, 1, 'Enroll Student', 'Admissions', 'Enrolled student from application: Abdulkadir Uukow (App No: APP000001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 17:23:59'),
(21, 1, 'Record Payment', 'Fees', 'Recorded payment: RCT000001 for invoice: INV000001', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 17:26:33'),
(22, 1, 'Add Fee Structure', 'Fees', 'Added fee structure for class ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 17:35:02'),
(23, 1, 'Issue Book', 'Library', 'Issued book: tarbiyo to student ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 18:09:26'),
(24, 1, 'Return Book', 'Library', 'Returned book issue ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 18:09:40'),
(25, 1, 'Issue Book', 'Library', 'Issued book: tarbiyo to student ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 18:11:44'),
(26, 1, 'Return Book', 'Library', 'Returned book issue ID: 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 18:12:04'),
(27, 1, 'Add Hostel', 'Facilities', 'Added hostel: test', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 18:22:07'),
(28, 1, 'Add Subject', 'Academics', 'Added subject: شمغثالي لعيغفعصلاي (125)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 18:40:33'),
(29, 1, 'Delete Subject', 'Academics', 'Deleted subject ID: 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 18:40:44'),
(30, 1, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 19:07:31'),
(31, 1, 'Reset User Password', 'Users', 'Reset password for user: Admin. Email sent: Yes', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-11-22 19:20:34'),
(32, 1, 'Update User', 'Users', 'Updated user: Admin (ID: 1)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 19:23:07'),
(33, 2, 'Register', 'Authentication', 'New user registered', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 19:45:03'),
(34, 1, 'Add User', 'Users', 'Created user: teacher', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 19:45:03'),
(35, 1, 'Add Staff', 'HR', 'Added staff: Abdulkadir Uukow (STF000001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 19:48:21'),
(36, 1, 'Reset User Password', 'Users', 'Reset password for user: teacher. Email sent: Yes', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 19:49:49'),
(37, 2, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-22 19:50:26'),
(38, 2, 'Mark Attendance', 'Attendance', 'Marked attendance for 1 students on 2025-11-22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-22 19:51:25'),
(39, 1, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 06:55:47'),
(40, 2, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-23 06:56:25'),
(41, 1, 'Update Class', 'Academics', 'Updated class: CA202', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 07:01:21'),
(42, 1, 'Add Assignment', 'Academics', 'Assigned Math to CA202 - Teacher: Abdulkadir Uukow', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 07:05:04'),
(43, 1, 'Update Subject', 'Academics', 'Updated subject: Math (MATH101)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 07:10:19'),
(44, 1, 'Add Timetable Period', 'Academics', 'Added period for Saturday', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-11-23 07:12:45'),
(45, 2, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 07:15:49'),
(46, 1, 'Toggle User Status', 'Users', 'Toggled status for user ID: 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 07:19:29'),
(47, 1, 'Toggle User Status', 'Users', 'Toggled status for user ID: 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 07:19:33'),
(48, 1, 'Create Lesson Plan', 'Academics', 'Created lesson plan: aaaaaaaaaa', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-11-23 08:11:35'),
(49, 1, 'Update Lesson Plan', 'Academics', 'Updated lesson plan: aaaaaaaaaa', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-11-23 08:18:05'),
(50, 1, 'Update Lesson Plan', 'Academics', 'Updated lesson plan: test', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-11-23 08:18:20'),
(51, 1, 'Update Lesson Plan', 'Academics', 'Updated lesson plan: testing', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-11-23 08:19:03'),
(52, 1, 'Update Lesson Plan', 'Academics', 'Updated lesson plan: testing', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 08:25:59'),
(53, 1, 'Update User', 'Users', 'Updated user: teacher (ID: 2)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 08:36:04'),
(54, 1, 'Toggle User Verification', 'Users', 'verified user ID: 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 08:36:27'),
(55, 1, 'Create Lesson Plan', 'Academics', 'Created lesson plan: test', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 08:51:27'),
(56, 1, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 07:53:19'),
(57, 1, 'Mark Attendance', 'Teacher Portal', 'Marked attendance for 1 students on 2025-11-30 for class ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 07:54:22'),
(58, 1, 'Mark Attendance', 'Teacher Portal', 'Marked attendance for 1 students on 2025-11-30 for class ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 07:54:59'),
(59, 1, 'Send Admission Notification', 'Admissions', 'Sent notification for application: APP000001', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 08:15:25'),
(60, 1, 'Schedule Interview', 'Admissions', 'Scheduled interview for application ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 08:16:44'),
(61, 1, 'Send Admission Notification', 'Admissions', 'Sent notification for application: APP000001', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 08:16:50'),
(62, 1, 'Update Application Status', 'Admissions', 'Updated application APP000001 to status: Accepted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 08:17:37'),
(63, 1, 'Update Application Status', 'Admissions', 'Updated application APP000001 to status: Accepted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 08:17:44'),
(64, 1, 'Enroll Student', 'Admissions', 'Enrolled student from application: Abdulkadir Uukow (App No: APP000001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 08:17:49'),
(65, 1, 'Promote Students', 'Students', 'Promoted 1 students', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 08:22:27'),
(66, 1, 'Create Exam', 'Exams', 'Created exam: Shift One', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 08:24:04'),
(67, 1, 'Add Exam Schedule', 'Exams', 'Added subject to exam schedule', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 08:25:03'),
(68, 1, 'Enter Exam Marks', 'Exams', 'Entered marks for 3 students', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 08:26:19'),
(69, 1, 'Enter Exam Marks', 'Exams', 'Entered marks for 3 students', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 08:26:56'),
(70, 2, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-30 08:33:30'),
(71, 1, 'Update Lesson Plan', 'Academics', 'Updated lesson plan: test', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 09:26:30'),
(72, 1, 'Update User', 'Users', 'Updated user: teacher (ID: 2)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 09:30:18'),
(73, 1, 'Link Staff to User', 'Users', 'Linked staff ID 1 to user: teacher (ID: 2)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 09:32:22'),
(74, 1, 'Update User', 'Users', 'Updated user: teacher (ID: 2)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 09:32:22'),
(75, 3, 'Register', 'Authentication', 'New user registered', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 09:33:26'),
(76, 1, 'Add User', 'Users', 'Created user: hagad', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 09:33:26'),
(77, 1, 'Update User', 'Users', 'Updated user: hagad (ID: 3)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 09:34:21'),
(78, 1, 'Add Staff', 'HR', 'Added staff: Hagad Xiis (STF000002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 09:35:16'),
(79, 1, 'Link Staff to User', 'Users', 'Linked staff ID 2 to user: hagad (ID: 3)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 09:35:31'),
(80, 1, 'Update User', 'Users', 'Updated user: hagad (ID: 3)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 09:35:31'),
(81, 3, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 09:35:37'),
(82, 1, 'Add Subject', 'Academics', 'Added subject: English (Basic English)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 14:26:46'),
(83, 1, 'Add Assignment', 'Academics', 'Assigned English to CA202 - Teacher: Hagad Xiis', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 14:27:10'),
(84, 1, 'Add Timetable Period', 'Academics', 'Added period for Monday', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 14:28:58'),
(85, 1, 'Assign Sections', 'Students', 'Assigned 1 students to section: CA202', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 14:39:32'),
(86, 1, 'Assign Sections', 'Students', 'Assigned 3 students to section: CA202', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 14:39:51'),
(87, NULL, 'Register', 'Authentication', 'New user registered', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 15:41:39'),
(88, 1, 'Add User', 'Users', 'Created user: abdi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 15:41:39'),
(89, 3, 'Logout', 'Authentication', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 15:42:41'),
(90, 1, 'Update User', 'Users', 'Updated user: abdi (ID: 4)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 15:44:23'),
(91, NULL, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 15:44:29'),
(92, 1, 'Update Student', 'Students', 'Updated student: Abdulkadir Ibrahim (ID: STU000001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 15:56:13'),
(93, 1, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 07:15:35'),
(94, NULL, 'Logout', 'Authentication', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 07:27:36'),
(95, NULL, 'Student Login', 'Authentication', 'Student logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 07:27:48'),
(96, NULL, 'Student Logout', 'Authentication', 'Student logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 07:27:59'),
(97, 1, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 07:28:37'),
(98, 1, 'Logout', 'Authentication', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 07:28:41'),
(99, 1, 'Toggle User Status', 'Users', 'Toggled status for user ID: 4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 07:33:16'),
(100, 1, 'Delete User', 'Users', 'Deleted user: abdi (musalsalo23@gmail.com)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 07:33:23'),
(101, 1, 'Enable Student Portal', 'Students', 'Enabled portal access for student: Abdulkadir Ibrahim (ID: STU000001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 07:33:41'),
(102, 1, 'Reset Student Password', 'Students', 'Reset password for student: Abdulkadir Ibrahim (Username: stu000001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 07:38:50'),
(103, 5, 'Student Login', 'Authentication', 'Student logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 07:43:49'),
(104, 5, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 07:44:01'),
(105, 5, 'Logout', 'Authentication', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 07:44:01'),
(106, 5, 'Student Login', 'Authentication', 'Student logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 07:44:47'),
(107, 1, 'Update Student', 'Students', 'Updated student: Abdulkadir Uukow (ID: STU000002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 07:46:20'),
(108, 2, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-12-03 07:47:55'),
(109, 2, 'Mark Attendance', 'Teacher Portal', 'Marked attendance for 3 students on 2025-12-03 for class ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-12-03 07:48:18'),
(110, 2, 'Mark Attendance', 'Attendance', 'Marked attendance for 3 students on 2025-12-03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-12-03 07:48:56'),
(111, 2, 'Enter Exam Marks', 'Exams', 'Entered marks for 3 students', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-12-03 07:50:13'),
(112, 2, 'Enter Exam Marks', 'Exams', 'Entered marks for 3 students', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-12-03 07:51:07'),
(113, 1, 'Enable Student Portal', 'Students', 'Enabled portal access for student: Abdulkadir Uukow (ID: STU000002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 07:52:00'),
(114, 1, 'Reset User Password', 'Users', 'Reset password for user: stu000002. Email sent: Yes', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 07:53:27'),
(115, 6, 'Student Login', 'Authentication', 'Student logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 07:53:34'),
(116, 1, 'Issue Book', 'Library', 'Issued book: tarbiyo to student ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 07:56:34'),
(117, 1, 'Issue Book', 'Library', 'Issued book: tarbiyo to student ID: 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 07:56:44'),
(118, 1, 'Issue Book', 'Library', 'Issued book: tarbiyo to student ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 07:57:02'),
(119, 1, 'Return Book', 'Library', 'Returned book issue ID: 3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 07:57:36'),
(120, 1, 'Return Book', 'Library', 'Returned book issue ID: 4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 07:57:45'),
(121, 1, 'Return Book', 'Library', 'Returned book issue ID: 5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 07:57:48'),
(122, 1, 'Add Event', 'Events', 'Created event: Teachers on 2025-12-03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 08:02:10'),
(123, 2, 'Create Announcement', 'Communication', 'Created announcement: teachers', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-12-03 08:03:50'),
(124, 1, 'Create Announcement', 'Communication', 'Created announcement: teszt', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 08:04:28'),
(125, 1, 'Delete Announcement', 'Communication', 'Deleted announcement ID: 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 08:04:53'),
(126, 1, 'Send Message', 'Communication', 'Sent message to user ID: 5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 08:05:29'),
(127, 5, 'Create Support Ticket', 'Support', 'Created ticket: TKT000001', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 08:06:07'),
(128, 1, 'Assign Ticket', 'Support', 'Assigned ticket TKT000001 to user ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 08:09:47'),
(129, 5, 'Ticket Reply', 'Support', 'Added reply to ticket: TKT000001', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 08:10:18'),
(130, 1, 'Ticket Reply', 'Support', 'Added reply to ticket: TKT000001', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 08:10:31'),
(131, 1, 'Mark Attendance', 'Teacher Portal', 'Marked attendance for 3 students on 2025-12-03 for class ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 14:14:54'),
(132, 1, 'Mark Attendance', 'Teacher Portal', 'Marked attendance for 3 students on 2025-12-03 for class ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-03 14:15:05'),
(133, 1, 'Mark Attendance', 'Teacher Portal', 'Marked attendance for 3 students on 2025-12-03 for class ID: 1', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-12-03 14:15:13'),
(134, 1, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-13 09:56:20'),
(135, 1, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-13 13:00:04'),
(136, 1, 'Reset User Password', 'Users', 'Reset password for user: stu000001. Email sent: Yes', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-13 13:01:42'),
(137, 5, 'Student Login', 'Authentication', 'Student logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-12-13 13:01:49'),
(138, 1, 'Add Fee Structure', 'Fees', 'Added fee structure for class ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-13 13:02:55'),
(139, 2, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-13 13:05:19'),
(140, 1, 'Mark Attendance', 'Teacher Portal', 'Marked attendance for 3 students on 2025-12-13 for class ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-13 13:06:51'),
(141, 1, 'Mark Attendance', 'Teacher Portal', 'Marked attendance for 3 students on 2025-12-13 for class ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-13 13:08:44'),
(142, 1, 'Mark Attendance', 'Teacher Portal', 'Marked attendance for 3 students on 2025-12-13 for class ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-13 13:08:53'),
(143, 1, 'Mark Attendance', 'Teacher Portal', 'Marked attendance for 3 students on 2025-12-13 for class ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-13 13:09:52'),
(144, 1, 'Delete Fee Structure', 'Fees', 'Deleted fee structure ID: 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-13 13:31:23'),
(145, 1, 'Add Fee Structure', 'Fees', 'Added fee structure for class ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-13 13:31:47'),
(146, 1, 'Assign Monthly Fees', 'Fees', 'Assigned fees for month: 2025-12. Students: 3 assigned, 0 skipped', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-12-13 13:39:11'),
(147, 1, 'Record Flexible Payment', 'Fees', 'Recorded payment: RCT000002 for student ID: 1. Amount: $60.00', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-12-13 13:46:41'),
(148, 1, 'Send Bulk Reminders', 'Fees', 'Sent reminders to 0 defaulters', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-13 13:53:06'),
(149, 1, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 06:53:10'),
(150, 1, 'Add Branch', 'Branches', 'Created branch: Wadajir Main Branch (WDJ)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 06:55:11'),
(151, 1, 'Add Class', 'Academics', 'Added class: Fasalka 1aad', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 06:59:01'),
(152, 1, 'Add Class', 'Academics', 'Added class: Fasalka 2aad', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:00:13'),
(153, 1, 'Add Class', 'Academics', 'Added class: Fasalka 3aad', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:01:27'),
(154, 1, 'Add Class', 'Academics', 'Added class: Fasalka 4aad', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:01:55'),
(155, 1, 'Add Section', 'Academics', 'Added section: A', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:03:04'),
(156, 1, 'Add Section', 'Academics', 'Added section: B', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:03:08'),
(157, 1, 'Add Section', 'Academics', 'Added section: C', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:03:13'),
(158, 1, 'Add Section', 'Academics', 'Added section: A', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:03:24'),
(159, 1, 'Add Section', 'Academics', 'Added section: B', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:03:31'),
(160, 1, 'Add Section', 'Academics', 'Added section: C', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:03:35'),
(161, 1, 'Add Section', 'Academics', 'Added section: A', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:03:42'),
(162, 1, 'Add Section', 'Academics', 'Added section: B', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:03:46'),
(163, 1, 'Add Section', 'Academics', 'Added section: C', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:03:50'),
(164, 1, 'Add Section', 'Academics', 'Added section: A', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:03:58'),
(165, 1, 'Add Section', 'Academics', 'Added section: B', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:04:02'),
(166, 1, 'Add Section', 'Academics', 'Added section: C', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:04:08'),
(167, 1, 'Add Section', 'Academics', 'Added section: D', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:04:28'),
(168, 1, 'Create Application', 'Admissions', 'Created application: APP000002', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:06:18'),
(169, 1, 'Update Application Status', 'Admissions', 'Updated application APP000002 to status: Accepted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:06:49'),
(170, 1, 'Enroll Student', 'Admissions', 'Enrolled student from application: Aisha Ahmed (App No: APP000002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:07:04'),
(171, 1, 'Create Application', 'Admissions', 'Created application: APP000003', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:08:50'),
(172, 1, 'Update Application Status', 'Admissions', 'Updated application APP000003 to status: Accepted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:08:59'),
(173, 1, 'Update Application Status', 'Admissions', 'Updated application APP000003 to status: Accepted', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:09:05'),
(174, 1, 'Enroll Student', 'Admissions', 'Enrolled student from application: Abdullahi Yusuf Osman (App No: APP000003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:28:47'),
(175, 1, 'Add Student', 'Students', 'Added student: Hafsa Ali (ID: STU000006)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:31:19'),
(176, 1, 'Update Student', 'Students', 'Updated student: Abdullahi Yusuf Osman (ID: STU000005)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:32:35'),
(177, 1, 'Update Student', 'Students', 'Updated student: Aisha Ahmed (ID: STU000004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:32:52'),
(178, 1, 'Add Student', 'Students', 'Added student: Farah Ali (ID: STU000007)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:34:10'),
(179, 1, 'Assign Sections', 'Students', 'Assigned 2 students to section: A', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:35:08'),
(180, 1, 'Promote Students', 'Students', 'Promoted 2 students', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:37:36'),
(181, 1, 'Add Student', 'Students', 'Added student: Farhan Ali (ID: STU000008)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:40:38'),
(182, 1, 'Add Subject', 'Academics', 'Added subject: Biology (Biology)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:43:32'),
(183, 1, 'Add Subject', 'Academics', 'Added subject: Physics (Physics)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:43:43'),
(184, 1, 'Add Subject', 'Academics', 'Added subject: Chemistry (Chemistry)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:43:54'),
(185, 1, 'Add Subject', 'Academics', 'Added subject: History (History)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:44:03'),
(186, 1, 'Add Subject', 'Academics', 'Added subject: Geography (Geography)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:44:12'),
(187, 1, 'Update Subject', 'Academics', 'Updated subject: Biology (Biology)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:44:21'),
(188, 1, 'Update Subject', 'Academics', 'Updated subject: English (Basic English)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:44:30'),
(189, 1, 'Update Subject', 'Academics', 'Updated subject: Math (MATH101)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:44:40'),
(190, 1, 'Add Subject', 'Academics', 'Added subject: Computer Science (Computer Science)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:45:11'),
(191, 1, 'Add Subject', 'Academics', 'Added subject: Art &amp; Design (Art &amp; Design)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:45:33'),
(192, 1, 'Add Subject', 'Academics', 'Added subject: Economics (Economics)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:45:47'),
(193, 1, 'Update Subject', 'Academics', 'Updated subject: Art amp Design (Art amp Design)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 07:47:27'),
(194, 1, 'Update Staff', 'HR', 'Updated staff: Abdulkadir Uukow (ID: STF000001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-14 08:50:30'),
(195, 1, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 09:21:49'),
(196, 2, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-16 09:55:35'),
(197, 2, 'Mark Attendance', 'Teacher Portal', 'Marked attendance for 40 students on 2025-12-16 for class ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-16 10:12:09'),
(198, 1, 'Mark Attendance', 'Attendance', 'Marked attendance for 40 students on 2025-12-16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 10:13:07'),
(199, 1, 'Add Timetable Period', 'Academics', 'Added period for Tuesday', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-16 10:25:35'),
(200, 2, 'Mark Attendance', 'Teacher Portal', 'Marked attendance for 40 students on 2025-12-16 for class ID: 1, subject ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-16 11:06:40'),
(201, 2, 'Mark Attendance', 'Teacher Portal', 'Marked attendance for 40 students on 2025-12-16 for class ID: 1, subject ID: 1 (Replaced 40 previous records)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-16 11:07:17'),
(202, 1, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 06:39:59'),
(203, 2, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-17 06:53:29'),
(204, 3, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 06:54:33'),
(205, 5, 'Password Reset Request', 'Authentication', 'Password reset requested', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 07:17:20'),
(206, 5, 'Student Login', 'Authentication', 'Student logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 07:19:01'),
(207, 3, 'Create Lesson Plan', 'Academics', 'Created lesson plan: Basic English', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 07:46:56'),
(208, 1, 'Update Lesson Plan', 'Academics', 'Updated lesson plan: Basic English', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 08:29:09'),
(209, 3, 'Update Lesson Plan', 'Academics', 'Updated lesson plan: Basic English', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 08:29:27'),
(210, 1, 'Create Exam', 'Exams', 'Created exam: Second', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 08:31:38'),
(211, 1, 'Add Exam Schedule', 'Exams', 'Added subject to exam schedule', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 08:32:56'),
(212, 3, 'Enter Exam Marks', 'Exams', 'Entered marks for 40 students', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 08:34:09'),
(213, 3, 'Enter Exam Marks', 'Exams', 'Entered marks for 40 students', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 08:35:09'),
(214, 1, 'Create Grading Scheme', 'Certificates', 'Created grading scheme: Standard 4.0 Scale', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 10:22:26'),
(215, 1, 'Update Grading Scheme', 'Certificates', 'Updated grading scheme: Standard 4.0 Scale', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 10:24:27'),
(216, 1, 'Update Grading Scheme', 'Certificates', 'Updated grading scheme: Standard 4.0 Scale', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 10:24:47'),
(217, 1, 'Activate Grading Scheme', 'Certificates', 'Activated grading scheme: Standard 4.0 Scale', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 10:27:35'),
(218, 1, 'Set Default Grading Scheme', 'Certificates', 'Set grading scheme as default: Standard 4.0 Scale', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 10:28:30'),
(219, 1, 'Create Certificate Template', 'Certificates', 'Created template: test', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 10:38:12'),
(220, 1, 'Update Certificate Template', 'Certificates', 'Updated template: test', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 10:39:51'),
(221, 1, 'Update Certificate Template', 'Certificates', 'Updated template: test', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 10:48:08'),
(222, 1, 'Generate Transcript', 'Certificates', 'Generated transcript TR-2025-00001 for student STUA3DD5749', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 10:53:07');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `module`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(223, 1, 'Generate Transcript', 'Certificates', 'Generated transcript TR-2025-00002 for student STU000002', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 10:59:24'),
(224, 1, 'Generate Transcript', 'Certificates', 'Generated transcript TR-2025-00003 for student STU000002', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 12:45:44'),
(225, 1, 'Update Grading Scheme', 'Certificates', 'Updated grading scheme: Standard 4.0 Scale', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 12:53:30'),
(226, 1, 'Activate Grading Scheme', 'Certificates', 'Activated grading scheme: Standard 4.0 Scale', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 12:54:46'),
(227, 1, 'Generate Certificate', 'Certificates', 'Generated certificate COMP-2025-0001 for student ID STU000001', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 12:55:23'),
(228, 1, 'Generate Certificate', 'Certificates', 'Generated certificate COMP-2025-0002 for student ID STU000001', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 12:55:56'),
(229, 1, 'Revoke Certificate', 'Certificates', 'Revoked certificate COMP-2025-0002 for student STU000001', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 13:05:09'),
(230, 1, 'Update Certificate Template', 'Certificates', 'Updated template: test', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 13:22:03'),
(231, 1, 'Generate Transcript', 'Certificates', 'Generated transcript TR-2025-00004 for student STU000002', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 13:22:41'),
(232, 1, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 18:14:52'),
(233, 1, 'Enter Exam Marks', 'Exams', 'Entered marks for 40 students', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 18:32:14'),
(234, 1, 'Enter Exam Marks', 'Exams', 'Entered marks for 40 students', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 18:32:46'),
(235, 1, 'Enter Exam Marks', 'Exams', 'Entered marks for 40 students', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 18:32:56'),
(236, 1, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 12:58:52'),
(237, 2, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-19 13:24:25'),
(238, 1, 'Assign Monthly Fees', 'Fees', 'Assigned fees for month: 2025-12. Students: 37 assigned, 3 skipped', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 13:40:44'),
(239, 1, 'Record Flexible Payment', 'Fees', 'Recorded payment: RCT000003 for student ID: 2. Amount: $35.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 13:43:58'),
(240, 1, 'Process Payroll', 'HR', 'Processed payroll for staff ID: 1', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2025-12-19 13:51:05'),
(241, 1, 'Record Payment', 'HR', 'Recorded payment for payroll ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 13:56:00'),
(242, 1, 'Delete Payment', 'HR', 'Deleted payment ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 13:56:09'),
(243, 1, 'Apply Leave', 'HR', 'Applied for leave - Staff ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 13:59:33'),
(244, 1, 'Update Leave Status', 'HR', 'Updated leave ID: 1 to Approved', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:02:04'),
(245, 1, 'Mark Staff Attendance', 'Attendance', 'Marked attendance for 12 staff on 2025-12-19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:04:21'),
(246, 1, 'Generate Custom Report', 'Reports', 'Generated report: Monthly', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:07:52'),
(247, 1, 'Generate Custom Report', 'Reports', 'Generated report: ffdfd', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:08:06'),
(248, 1, 'Generate Custom Report', 'Reports', 'Generated report: ffdfd', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:08:07'),
(249, 1, 'Generate Custom Report', 'Reports', 'Generated report: ffdfd', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:08:07'),
(250, 1, 'Generate Custom Report', 'Reports', 'Generated report: ffdfd', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:08:08'),
(251, 1, 'Send SMS', 'Communication', 'Sent SMS to 553 recipients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:11:01'),
(252, 1, 'Send Email', 'Communication', 'Sent email to 11 recipients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:16:27'),
(253, 1, 'Process Payroll', 'HR', 'Processed payroll for staff ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:51:35'),
(254, 1, 'Delete Payment', 'HR', 'Deleted payment ID: 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:54:18'),
(255, 1, 'Process Payroll (All Staff)', 'HR', 'Processed payroll for 0 staff members for 2025-12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:54:29'),
(256, 1, 'Update Staff', 'HR', 'Updated staff: Abdulkadir Uukow (ID: STF000001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:57:58'),
(257, 1, 'Process Payroll', 'HR', 'Processed payroll for staff ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:58:29'),
(258, 1, 'Delete Payment', 'HR', 'Deleted payment ID: 3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:59:11'),
(259, 1, 'Process Payroll (All Staff)', 'HR', 'Processed payroll for 1 staff members for 2025-12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:59:19'),
(260, 1, 'Record Payment', 'HR', 'Recorded payment for payroll ID: 4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 14:59:57'),
(261, 1, 'Delete Payment', 'HR', 'Deleted payment ID: 4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:00:44'),
(262, 1, 'Process Payroll (All Staff)', 'HR', 'Processed payroll for 1 staff members for 2025-12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:01:38'),
(263, 1, 'Record Payment & Expense', 'HR', 'Recorded payment and expense for payroll ID: 5 - $250.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:01:51'),
(264, 1, 'Record Payment', 'HR', 'Recorded payment for payroll ID: 5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:01:51'),
(265, 1, 'Approve Expense', 'Fees', 'Approved expense ID: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:02:07'),
(266, 1, 'Record Flexible Payment & Income', 'Fees', 'Recorded payment and income: RCT000004 - $2.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:21:33'),
(267, 1, 'Record Flexible Payment', 'Fees', 'Recorded payment: RCT000004 for student ID: 2. Amount: $2.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:21:33'),
(268, 1, 'Reset User Password', 'Users', 'Reset password for user: stu000002. Email sent: Yes', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:23:25'),
(269, 6, 'Student Login', 'Authentication', 'Student logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 15:23:33'),
(270, 1, 'Add Timetable Period', 'Academics', 'Added period for Monday', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 17:10:24'),
(271, 1, 'Update Staff', 'HR', 'Updated staff: Sarah Johnson (ID: STF000003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 17:19:04'),
(272, 1, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 05:53:56'),
(273, 6, 'Student Login', 'Authentication', 'Student logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-20 06:07:02'),
(274, 1, 'Add Student', 'Students', 'Added student: Zamzam sharif (ID: STU001162)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 06:14:35'),
(275, 1, 'Update Student', 'Students', 'Updated student: Abdulkadir Uukow (ID: STU000002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 06:17:58'),
(276, 1, 'Assign Monthly Fees', 'Fees', 'Assigned fees for month: 2025-11. Students: 40 assigned, 0 skipped', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2025-12-20 06:22:27'),
(277, 1, 'Record Flexible Payment & Income', 'Fees', 'Recorded payment and income: RCT000008 - $71.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 06:36:29'),
(278, 1, 'Record Flexible Payment', 'Fees', 'Recorded payment: RCT000008 for student ID: 2. Amount: $71.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 06:36:29'),
(279, 1, 'Add Class', 'Academics', 'Added class: Class 2025', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 07:03:19'),
(280, 1, 'Add Section', 'Academics', 'Added section: Grad', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 07:03:40'),
(281, 1, 'Promote Students', 'Students', 'Promoted 5 students', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 07:04:53'),
(282, 1, 'Graduate Class', 'Academics', 'Graduated class: Class 2025 (ID: 6) - 5 students affected', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 07:05:26'),
(283, 1, 'Generate Certificate', 'Certificates', 'Generated certificate GRAD-2025-0001 for student ID STU000002', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 07:54:55'),
(284, 1, 'Generate Certificate', 'Certificates', 'Generated certificate GRAD-2025-0002 for student ID STU03235800', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 07:54:55'),
(285, 1, 'Generate Certificate', 'Certificates', 'Generated certificate GRAD-2025-0003 for student ID STU75F73FFA', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 07:54:55'),
(286, 1, 'Generate Certificate', 'Certificates', 'Generated certificate GRAD-2025-0004 for student ID STU4126323C', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 07:54:55'),
(287, 1, 'Generate Certificate', 'Certificates', 'Generated certificate GRAD-2025-0005 for student ID STUF161FF31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 07:54:55'),
(288, 2, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 09:51:32'),
(289, 6, 'Create Support Ticket', 'Support', 'Created ticket: TKT000002', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-20 09:56:02'),
(290, 1, 'Ticket Reply', 'Support', 'Added reply to ticket: TKT000002', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 09:56:19'),
(291, 1, 'Update Ticket Status', 'Support', 'Updated ticket TKT000002 status to: Open', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 09:56:47'),
(292, 1, 'Assign Ticket', 'Support', 'Assigned ticket TKT000002 to user ID: 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 09:56:59'),
(293, 1, 'Assign Ticket', 'Support', 'Assigned ticket TKT000002 to user ID: 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 09:57:12'),
(294, 1, 'Assign Ticket', 'Support', 'Assigned ticket TKT000002 to user ID: 3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 09:57:22'),
(295, 6, 'Ticket Reply', 'Support', 'Added reply to ticket: TKT000002', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-20 09:57:51'),
(296, 1, 'Ticket Reply', 'Support', 'Added reply to ticket: TKT000002', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 09:58:10'),
(297, 6, 'Ticket Reply', 'Support', 'Added reply to ticket: TKT000002', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-20 09:58:23'),
(298, 1, 'Assign Ticket', 'Support', 'Assigned ticket TKT000002 to user ID: 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 09:58:31'),
(299, 1, 'Update Ticket Status', 'Support', 'Updated ticket TKT000002 status to: Closed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 09:58:49'),
(300, 2, 'Create Support Ticket', 'Support', 'Created ticket: TKT000003', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 09:59:58'),
(301, 1, 'Ticket Reply', 'Support', 'Added reply to ticket: TKT000003', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 10:00:30'),
(302, 2, 'Ticket Reply', 'Support', 'Added reply to ticket: TKT000003', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 10:00:45'),
(303, 1, 'Ticket Reply', 'Support', 'Added reply to ticket: TKT000003', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 10:01:10'),
(304, 2, 'Ticket Reply', 'Support', 'Added reply to ticket: TKT000003', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 10:01:29'),
(305, 1, 'Update Ticket Status', 'Support', 'Updated ticket TKT000003 status to: Closed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 10:01:40'),
(306, 6, 'Student Login', 'Authentication', 'Student logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-20 10:12:25'),
(307, 1, 'Update Settings', 'Settings', 'Updated 7 system settings', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2025-12-20 11:17:41'),
(308, 1, 'Update Settings', 'Settings', 'Updated 7 system settings', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2025-12-20 11:18:09'),
(309, 1, 'Update Settings', 'Settings', 'Updated 7 system settings', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2025-12-20 11:19:31'),
(310, 1, 'Upload Logo', 'Settings', 'Uploaded new logo', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2025-12-20 11:22:21'),
(311, 1, 'Upload Favicon', 'Settings', 'Uploaded new favicon', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2025-12-20 11:22:30'),
(312, 1, 'Update Settings', 'Settings', 'Updated 7 system settings', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2025-12-20 11:22:35'),
(313, 1, 'Upload Logo', 'Settings', 'Uploaded new logo', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 11:25:17'),
(314, 1, 'Update Settings', 'Settings', 'Updated 7 system settings', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 11:25:23'),
(315, 1, 'Update Settings', 'Settings', 'Updated 7 system settings', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 11:26:03'),
(316, 2, 'Logout', 'Authentication', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 11:26:16'),
(317, 1, 'Upload Logo', 'Settings', 'Uploaded new logo', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 11:26:25'),
(318, 1, 'Update Settings', 'Settings', 'Updated 7 system settings', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 11:26:29'),
(319, 1, 'Upload Favicon', 'Settings', 'Uploaded new favicon', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 11:29:11'),
(320, 1, 'Update Settings', 'Settings', 'Updated 7 system settings', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 11:29:16'),
(321, 1, 'Update Settings', 'Settings', 'Updated 10 system settings', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 11:29:52'),
(322, 1, 'Update Settings', 'Settings', 'Updated 10 system settings', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 11:30:15'),
(323, 1162, 'Register', 'Authentication', 'New user registered', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:19:30'),
(324, 1, 'Add User', 'Users', 'Created user: uukow', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:19:30'),
(325, 1, 'Reset User Password', 'Users', 'Reset password for user: uukow. Email sent: Yes', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:20:58'),
(326, 1162, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:21:04'),
(327, 1162, 'Graduate Class', 'Academics', 'Graduated class: Fasalka 1aad (ID: 2) - 160 students affected', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:24:08'),
(328, 1, 'Update User', 'Users', 'Updated user: uukow (ID: 1162)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:25:23'),
(329, 1162, 'Logout', 'Authentication', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:25:35'),
(330, 1162, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:25:41'),
(331, 1, 'Update User', 'Users', 'Updated user: uukow (ID: 1162)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:29:44'),
(332, 1162, 'Logout', 'Authentication', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:29:57'),
(333, 1162, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:30:04'),
(334, 1162, 'Logout', 'Authentication', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:32:17'),
(335, 1, 'Update User', 'Users', 'Updated user: uukow (ID: 1162)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:32:53'),
(336, 1162, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:33:02'),
(337, 1162, 'Logout', 'Authentication', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:33:24'),
(338, 1, 'Update User', 'Users', 'Updated user: uukow (ID: 1162)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:33:49'),
(339, 1162, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:34:00'),
(340, 1, 'Update Student', 'Students', 'Updated student: Abdulkadir Ibrahim (ID: STU000001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:34:35'),
(341, 1162, 'Logout', 'Authentication', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:34:44'),
(342, 1162, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:34:51'),
(343, 1, 'Update Student', 'Students', 'Updated student: Abdulkadir Ibrahim (ID: STU000001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:40:17'),
(344, 1162, 'Logout', 'Authentication', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:40:24'),
(345, 1162, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:40:31'),
(346, 1, 'Update Student', 'Students', 'Updated student: Abdulkadir Ibrahim (ID: STU000001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:40:40'),
(347, 1, 'Update Student', 'Students', 'Updated student: Abdulkadir Ibrahim (ID: STU000001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:40:55'),
(348, 1, 'Update Student', 'Students', 'Updated student: Abdulkadir Ibrahim (ID: STU000001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:45:12'),
(349, 1, 'Update Student', 'Students', 'Updated student: Abdulkadir Ibrahim (ID: STU000001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:45:37'),
(350, 1, 'Update Student', 'Students', 'Updated student: Abdulkadir Ibrahim (ID: STU000001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:49:43'),
(351, 1, 'Update Student', 'Students', 'Updated student: Abdulkadir Ibrahim (ID: STU000001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:49:57'),
(352, 1, 'Add Student', 'Students', 'Added student: qof dheer (ID: STU001163)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:50:57'),
(353, 1162, 'Logout', 'Authentication', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:51:03'),
(354, 1162, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:51:12'),
(355, 1, 'Update User', 'Users', 'Updated user: uukow (ID: 1162)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:59:02'),
(356, 1162, 'Logout', 'Authentication', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:59:12'),
(357, 1162, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:59:18'),
(358, 1, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 07:06:33'),
(359, 1, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 07:11:07'),
(360, 1, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 07:14:20'),
(361, 1, 'Login', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 07:17:40'),
(362, 1, 'Create Backup', 'Settings', 'Created backup: backup_2025-12-21_101811.sql', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 07:18:11'),
(363, 1, 'Delete Backup', 'Settings', 'Deleted backup: backup_2025-12-21_101811.sql', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 07:19:11'),
(364, 1, 'Create Backup', 'Settings', 'Created backup: backup_2025-12-21_101929.sql', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 07:19:29'),
(365, 1, 'Download Backup', 'Settings', 'Downloaded backup: backup_2025-12-21_101929.sql', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 07:21:15');

-- --------------------------------------------------------

--
-- Table structure for table `admission_applications`
--

CREATE TABLE `admission_applications` (
  `id` int(11) NOT NULL,
  `application_no` varchar(50) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `date_of_birth` date NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `parent_name` varchar(200) NOT NULL,
  `parent_phone` varchar(50) NOT NULL,
  `parent_email` varchar(100) DEFAULT NULL,
  `previous_school` varchar(255) DEFAULT NULL,
  `documents_submitted` text DEFAULT NULL,
  `status` enum('Pending','Under Review','Interview Scheduled','Accepted','Rejected','Enrolled') DEFAULT 'Pending',
  `interview_date` datetime DEFAULT NULL,
  `interview_notes` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admission_applications`
--

INSERT INTO `admission_applications` (`id`, `application_no`, `branch_id`, `session_id`, `class_id`, `first_name`, `last_name`, `gender`, `date_of_birth`, `email`, `phone`, `address`, `parent_name`, `parent_phone`, `parent_email`, `previous_school`, `documents_submitted`, `status`, `interview_date`, `interview_notes`, `rejection_reason`, `reviewed_by`, `applied_at`, `updated_at`) VALUES
(1, 'APP000001', 1, 1, 1, 'Abdulkadir', 'Uukow', 'Male', '2002-06-04', '', '', '', 'Abdulkadir Uukow', '615031623', '', '', NULL, 'Enrolled', '2025-11-30 11:16:00', '', NULL, 1, '2025-11-22 17:13:06', '2025-11-30 08:17:49'),
(2, 'APP000002', 2, 1, 2, 'Aisha', 'Ahmed', 'Female', '2002-02-15', 'aishaabintu@gmail.com', '613976162', 'Korontada', 'Ahmed Hassan', '615714745544', '', '', NULL, 'Enrolled', NULL, 'good', NULL, 1, '2025-12-14 07:06:18', '2025-12-14 07:07:04'),
(3, 'APP000003', 2, 1, 2, 'Abdullahi Yusuf', 'Osman', 'Male', '2001-02-13', '', '', '', 'Yusuf Osman', '61414555552', '', '', NULL, 'Enrolled', NULL, '', NULL, 1, '2025-12-14 07:08:50', '2025-12-14 07:28:47');

-- --------------------------------------------------------

--
-- Table structure for table `alumni`
--

CREATE TABLE `alumni` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `graduation_year` year(4) NOT NULL,
  `current_profession` varchar(255) DEFAULT NULL,
  `current_employer` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `achievements` text DEFAULT NULL,
  `willing_to_mentor` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alumni_donations`
--

CREATE TABLE `alumni_donations` (
  `id` int(11) NOT NULL,
  `alumni_id` int(11) NOT NULL,
  `donation_amount` decimal(15,2) NOT NULL,
  `donation_date` date NOT NULL,
  `donation_purpose` varchar(255) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `target_audience` enum('All','Students','Teachers','Parents','Staff','Specific Class','Specific Branch') NOT NULL,
  `target_ids` text DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_urgent` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `target_audience`, `target_ids`, `branch_id`, `start_date`, `end_date`, `is_urgent`, `created_by`, `created_at`) VALUES
(1, 'teachers', 'teacher kulan', 'Teachers', NULL, 1, NULL, '2025-12-04', 1, 2, '2025-12-03 08:03:50');

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` int(11) NOT NULL,
  `asset_name` varchar(255) NOT NULL,
  `asset_code` varchar(50) NOT NULL,
  `asset_type` varchar(100) NOT NULL,
  `purchase_date` date NOT NULL,
  `purchase_price` decimal(15,2) NOT NULL,
  `current_value` decimal(15,2) DEFAULT NULL,
  `depreciation_rate` decimal(5,2) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `condition` enum('Excellent','Good','Fair','Poor','Damaged') DEFAULT 'Good',
  `branch_id` int(11) DEFAULT NULL,
  `warranty_expiry` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `total_marks` decimal(10,2) DEFAULT 100.00,
  `due_date` datetime NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assignment_submissions`
--

CREATE TABLE `assignment_submissions` (
  `id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `submission_text` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `marks_obtained` decimal(10,2) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `graded_by` int(11) DEFAULT NULL,
  `graded_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `backup_history`
--

CREATE TABLE `backup_history` (
  `id` int(11) NOT NULL,
  `backup_name` varchar(255) NOT NULL,
  `backup_path` varchar(255) NOT NULL,
  `backup_size` bigint(20) DEFAULT NULL,
  `backup_type` enum('Full','Partial') DEFAULT 'Full',
  `status` enum('Success','Failed','In Progress') DEFAULT 'Success',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `backup_history`
--

INSERT INTO `backup_history` (`id`, `backup_name`, `backup_path`, `backup_size`, `backup_type`, `status`, `created_by`, `created_at`) VALUES
(2, 'backup_2025-12-21_101929.sql', 'backup_2025-12-21_101929.sql', 844389, 'Full', 'Success', 1, '2025-12-21 07:19:29');

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `branch_name` varchar(255) NOT NULL,
  `branch_code` varchar(50) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `established_date` date DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `branch_name`, `branch_code`, `address`, `phone`, `email`, `manager_id`, `is_active`, `established_date`, `logo`, `created_at`, `updated_at`) VALUES
(1, 'Awdheegle', 'MAIN', 'Main Campus', NULL, NULL, NULL, 1, NULL, NULL, '2025-11-13 07:26:12', '2025-11-13 07:26:12'),
(2, 'Wadajir Main Branch', 'WDJ', 'Degmada Wadajir', '+252615031623', 'branch@uukowtech.com', 0, 1, '2025-12-01', 'uploads/branches/693e5f4f15080_1765695311.png', '2025-12-14 06:55:11', '2025-12-14 06:55:11');

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `id` int(11) NOT NULL,
  `certificate_number` varchar(100) NOT NULL,
  `verification_code` varchar(64) NOT NULL,
  `template_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `session_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `certificate_type` varchar(50) NOT NULL,
  `issue_date` date NOT NULL,
  `valid_until` date DEFAULT NULL,
  `academic_data` longtext DEFAULT NULL,
  `gpa` decimal(3,2) DEFAULT NULL,
  `cgpa` decimal(3,2) DEFAULT NULL,
  `attendance_percentage` decimal(5,2) DEFAULT NULL,
  `class_rank` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` enum('issued','reissued','revoked') DEFAULT 'issued',
  `issued_by` int(11) DEFAULT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `revoked_by` int(11) DEFAULT NULL,
  `revoke_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`id`, `certificate_number`, `verification_code`, `template_id`, `student_id`, `session_id`, `class_id`, `certificate_type`, `issue_date`, `valid_until`, `academic_data`, `gpa`, `cgpa`, `attendance_percentage`, `class_rank`, `remarks`, `status`, `issued_by`, `revoked_at`, `revoked_by`, `revoke_reason`, `created_at`, `updated_at`) VALUES
(1, 'COMP-2025-0001', '44e1ccd015e37103', 2, 1, 1, 1, '0', '2025-12-17', NULL, '{\"subjects\":[{\"subject_name\":\"English\",\"subject_code\":\"Basic English\",\"percentage\":25,\"grade\":\"D\",\"grade_points\":\"0.00\",\"marks_obtained\":5,\"total_marks\":20},{\"subject_name\":\"Math\",\"subject_code\":\"MATH101\",\"percentage\":100,\"grade\":\"A+\",\"grade_points\":\"4.00\",\"marks_obtained\":30,\"total_marks\":30}],\"gpa\":2,\"cgpa\":2,\"total_subjects\":2,\"overall_percentage\":70,\"total_marks\":50,\"obtained_marks\":35}', 2.00, 2.00, NULL, NULL, '', 'issued', 1, NULL, NULL, NULL, '2025-12-17 12:55:23', '2025-12-17 12:55:23'),
(3, 'GRAD-2025-0001', '2d81c69e4e27d479', 5, 2, 1, 6, '0', '2025-12-20', NULL, '{\"subjects\":[],\"gpa\":0,\"cgpa\":0,\"total_subjects\":0,\"overall_percentage\":0,\"total_marks\":0,\"obtained_marks\":0}', 0.00, 0.00, NULL, NULL, '', 'issued', 1, NULL, NULL, NULL, '2025-12-20 07:54:55', '2025-12-20 07:54:55'),
(4, 'GRAD-2025-0002', 'a3c6d8264eb64196', 5, 644, 1, 6, '0', '2025-12-20', NULL, '{\"subjects\":[],\"gpa\":0,\"cgpa\":0,\"total_subjects\":0,\"overall_percentage\":0,\"total_marks\":0,\"obtained_marks\":0}', 0.00, 0.00, NULL, NULL, '', 'issued', 1, NULL, NULL, NULL, '2025-12-20 07:54:55', '2025-12-20 07:54:55'),
(5, 'GRAD-2025-0003', 'f3f81922c3e167af', 5, 637, 1, 6, '0', '2025-12-20', NULL, '{\"subjects\":[],\"gpa\":0,\"cgpa\":0,\"total_subjects\":0,\"overall_percentage\":0,\"total_marks\":0,\"obtained_marks\":0}', 0.00, 0.00, NULL, NULL, '', 'issued', 1, NULL, NULL, NULL, '2025-12-20 07:54:55', '2025-12-20 07:54:55'),
(6, 'GRAD-2025-0004', 'b2ac0833d830e965', 5, 611, 1, 6, '0', '2025-12-20', NULL, '{\"subjects\":[],\"gpa\":0,\"cgpa\":0,\"total_subjects\":0,\"overall_percentage\":0,\"total_marks\":0,\"obtained_marks\":0}', 0.00, 0.00, NULL, NULL, '', 'issued', 1, NULL, NULL, NULL, '2025-12-20 07:54:55', '2025-12-20 07:54:55'),
(7, 'GRAD-2025-0005', 'd3b52a1d4d78382d', 5, 617, 1, 6, '0', '2025-12-20', NULL, '{\"subjects\":[],\"gpa\":0,\"cgpa\":0,\"total_subjects\":0,\"overall_percentage\":0,\"total_marks\":0,\"obtained_marks\":0}', 0.00, 0.00, NULL, NULL, '', 'issued', 1, NULL, NULL, NULL, '2025-12-20 07:54:55', '2025-12-20 07:54:55');

-- --------------------------------------------------------

--
-- Table structure for table `certificate_templates`
--

CREATE TABLE `certificate_templates` (
  `id` int(11) NOT NULL,
  `template_name` varchar(150) NOT NULL,
  `template_type` enum('Completion','Promotion','Graduation','Character','Transcript','Custom') NOT NULL DEFAULT 'Custom',
  `certificate_type` varchar(50) NOT NULL DEFAULT 'custom',
  `code` varchar(50) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `page_orientation` varchar(20) NOT NULL DEFAULT 'landscape',
  `page_size` varchar(20) NOT NULL DEFAULT 'A4',
  `is_active` tinyint(1) DEFAULT 1,
  `layout_html` longtext DEFAULT NULL,
  `placeholders_json` text DEFAULT NULL,
  `header_logo_path` varchar(255) DEFAULT NULL,
  `background_image_path` varchar(255) DEFAULT NULL,
  `header_html` longtext DEFAULT NULL,
  `body_html` longtext DEFAULT NULL,
  `footer_html` longtext DEFAULT NULL,
  `signatory_1_name` varchar(150) DEFAULT NULL,
  `signatory_1_title` varchar(150) DEFAULT NULL,
  `signatory_1_signature_path` varchar(255) DEFAULT NULL,
  `signature_1_label` varchar(150) DEFAULT NULL,
  `signatory_2_name` varchar(150) DEFAULT NULL,
  `signatory_2_title` varchar(150) DEFAULT NULL,
  `signatory_2_signature_path` varchar(255) DEFAULT NULL,
  `signature_2_label` varchar(150) DEFAULT NULL,
  `issue_date_label` varchar(100) DEFAULT 'Date of Issue',
  `serial_label` varchar(100) DEFAULT 'Certificate No.',
  `qr_label` varchar(100) DEFAULT 'Verification QR',
  `include_qr_code` tinyint(1) NOT NULL DEFAULT 1,
  `include_watermark` tinyint(1) NOT NULL DEFAULT 0,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `certificate_templates`
--

INSERT INTO `certificate_templates` (`id`, `template_name`, `template_type`, `certificate_type`, `code`, `branch_id`, `page_orientation`, `page_size`, `is_active`, `layout_html`, `placeholders_json`, `header_logo_path`, `background_image_path`, `header_html`, `body_html`, `footer_html`, `signatory_1_name`, `signatory_1_title`, `signatory_1_signature_path`, `signature_1_label`, `signatory_2_name`, `signatory_2_title`, `signatory_2_signature_path`, `signature_2_label`, `issue_date_label`, `serial_label`, `qr_label`, `include_qr_code`, `include_watermark`, `is_default`, `created_by`, `created_at`, `updated_at`) VALUES
(2, 'test', 'Custom', 'completion', '', 2, 'landscape', 'A4', 1, NULL, NULL, NULL, NULL, '', '\n                                    <!-- Elements will be added here via drag and drop -->\n                                    <div id=\"templateContent\"></div>\n                                <div class=\"\"    ><h1 style=\"margin:0; font-size: 36px; font-weight: bold;\">Certificate of Completion</h1>\n        <div class=\"\">\n            <button class=\"btn btn-sm btn-primary\"  title=\"Select\">\n                <i class=\"ri-cursor-line\"></i>\n            </button>\n            <button class=\"btn btn-sm btn-danger\"  title=\"Delete\">\n                <i class=\"ri-delete-bin-line\"></i>\n            </button>\n        </div>\n    </div><div class=\"\"    ><p style=\"margin:0; font-size: 16px;\">This is to certify that</p>\n        <div class=\"\">\n            <button class=\"btn btn-sm btn-primary\"  title=\"Select\">\n                <i class=\"ri-cursor-line\"></i>\n            </button>\n            <button class=\"btn btn-sm btn-danger\"  title=\"Delete\">\n                <i class=\"ri-delete-bin-line\"></i>\n            </button>\n        </div>\n    </div><div class=\"\"    ><p style=\"margin:0; font-size: 24px; font-weight: bold; color: #0d6efd;\"><strong>{{STUDENT_NAME}}</strong></p>\n        <div class=\"\">\n            <button class=\"btn btn-sm btn-primary\"  title=\"Select\">\n                <i class=\"ri-cursor-line\"></i>\n            </button>\n            <button class=\"btn btn-sm btn-danger\"  title=\"Delete\">\n                <i class=\"ri-delete-bin-line\"></i>\n            </button>\n        </div>\n    </div><div class=\"\"    ><p style=\"margin:0; font-size: 14px;\">Certificate No.: <strong>{{CERTIFICATE_ID}}</strong></p>\n        <div class=\"\">\n            <button class=\"btn btn-sm btn-primary\"  title=\"Select\">\n                <i class=\"ri-cursor-line\"></i>\n            </button>\n            <button class=\"btn btn-sm btn-danger\"  title=\"Delete\">\n                <i class=\"ri-delete-bin-line\"></i>\n            </button>\n        </div>\n    </div><div class=\"\"    ><hr style=\"margin: 20px 0; border: none; border-top: 2px solid #000;\">\n        <div class=\"\">\n            <button class=\"btn btn-sm btn-primary\"  title=\"Select\">\n                <i class=\"ri-cursor-line\"></i>\n            </button>\n            <button class=\"btn btn-sm btn-danger\"  title=\"Delete\">\n                <i class=\"ri-delete-bin-line\"></i>\n            </button>\n        </div>\n    </div><div class=\"\"    ><p style=\"margin:0; font-size: 16px;\">This Certificate of Completion is proudly presented to [Recipient Name] in recognition of having successfully completed the [Training Program Name] on [Date]. Your commitment to professional development has enabled you to acquire valuable skills in [Subject Area]</p>\n        <div class=\"\">\n            <button class=\"btn btn-sm btn-primary\"  title=\"Select\">\n                <i class=\"ri-cursor-line\"></i>\n            </button>\n            <button class=\"btn btn-sm btn-danger\"  title=\"Delete\">\n                <i class=\"ri-delete-bin-line\"></i>\n            </button>\n        </div>\n    </div><div class=\"\"    ><p style=\"margin:0; font-size: 14px;\">Date: <strong>{{DATE}}</strong></p>\n        <div class=\"\">\n            <button class=\"btn btn-sm btn-primary\"  title=\"Select\">\n                <i class=\"ri-cursor-line\"></i>\n            </button>\n            <button class=\"btn btn-sm btn-danger\"  title=\"Delete\">\n                <i class=\"ri-delete-bin-line\"></i>\n            </button>\n        </div>\n    </div><div class=\"\"    ><div style=\"border-top: 2px solid #000; display: inline-block; padding: 10px 30px; min-width: 150px; text-align: center;\"><strong>Principal</strong></div>\n        <div class=\"\">\n            <button class=\"btn btn-sm btn-primary\"  title=\"Select\">\n                <i class=\"ri-cursor-line\"></i>\n            </button>\n            <button class=\"btn btn-sm btn-danger\"  title=\"Delete\">\n                <i class=\"ri-delete-bin-line\"></i>\n            </button>\n        </div>\n    </div>', '', NULL, NULL, NULL, 'Principal', NULL, NULL, NULL, 'Registrar', 'Date of Issue', 'Certificate No.', 'Verification QR', 1, 0, 0, 1, '2025-12-17 10:38:12', '2025-12-17 13:22:03'),
(5, 'Elegant Graduation Certificate', 'Custom', 'graduation', 'ELEGANTGRADUATIONCER_1_1766216725', NULL, 'landscape', 'A4', 1, NULL, NULL, NULL, NULL, '<div style=\"text-align: center; margin-bottom: 40px; padding-top: 20px;\">\r\n    <div style=\"border-top: 4px solid #1a5490; border-bottom: 4px solid #1a5490; padding: 15px 0; margin-bottom: 15px;\">\r\n        <h1 style=\"font-family: \'Playfair Display\', \'Times New Roman\', serif; font-size: 64px; font-weight: 700; color: #1a5490; margin: 0; letter-spacing: 3px; text-transform: uppercase;\">CERTIFICATE</h1>\r\n    </div>\r\n    <h2 style=\"font-family: \'Georgia\', serif; font-size: 28px; font-weight: 400; color: #2c3e50; margin: 10px 0 0 0; font-style: italic; letter-spacing: 2px;\">OF GRADUATION</h2>\r\n    <div style=\"margin-top: 20px;\">\r\n        <p style=\"font-size: 18px; color: #7f8c8d; margin: 0;\">{{SCHOOL_NAME}}</p>\r\n    </div>\r\n</div>', '<div style=\"text-align: center; padding: 40px 20px;\">\r\n    <p style=\"font-size: 20px; color: #34495e; margin-bottom: 30px; font-family: \'Georgia\', serif; line-height: 1.8;\">\r\n        This is to certify that\r\n    </p>\r\n    <div style=\"margin: 30px 0; padding: 20px 0; border-top: 3px solid #d4af37; border-bottom: 3px solid #d4af37;\">\r\n        <h3 style=\"font-family: \'Playfair Display\', serif; font-size: 42px; font-weight: 700; color: #1a5490; margin: 0; letter-spacing: 2px;\">{{STUDENT_NAME}}</h3>\r\n    </div>\r\n    <p style=\"font-size: 18px; color: #34495e; margin: 30px 0; font-family: \'Georgia\', serif; line-height: 1.8;\">\r\n        has successfully completed the requirements for <strong style=\"color: #1a5490;\">{{CLASS}}</strong><br>\r\n        during the academic session <strong style=\"color: #1a5490;\">{{SESSION}}</strong><br>\r\n        and is hereby awarded this Certificate of Graduation.\r\n    </p>\r\n    <div style=\"margin-top: 40px; padding: 15px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-left: 4px solid #1a5490;\">\r\n        <p style=\"font-size: 14px; color: #495057; margin: 5px 0;\">\r\n            <strong>Certificate Number:</strong> {{CERTIFICATE_ID}}\r\n        </p>\r\n        <p style=\"font-size: 14px; color: #495057; margin: 5px 0;\">\r\n            <strong>Date of Issue:</strong> {{DATE}}\r\n        </p>\r\n    </div>\r\n</div>', '<div style=\"display: flex; justify-content: space-between; align-items: flex-end; margin-top: 60px; padding: 0 40px;\">\r\n    <div style=\"text-align: center; flex: 1;\">\r\n        <div style=\"border-top: 2px solid #1a5490; width: 200px; margin: 0 auto 10px; padding-top: 8px;\"></div>\r\n        <p style=\"font-weight: 600; font-size: 16px; color: #1a5490; margin: 5px 0;\">{{PRINCIPAL_SIGNATURE}}</p>\r\n        <p style=\"font-size: 12px; color: #7f8c8d; text-transform: uppercase; margin: 0;\">Principal</p>\r\n    </div>\r\n    <div style=\"text-align: center; flex: 1;\">\r\n        <div style=\"border-top: 2px solid #1a5490; width: 200px; margin: 0 auto 10px; padding-top: 8px;\"></div>\r\n        <p style=\"font-weight: 600; font-size: 16px; color: #1a5490; margin: 5px 0;\">{{REGISTRAR_SIGNATURE}}</p>\r\n        <p style=\"font-size: 12px; color: #7f8c8d; text-transform: uppercase; margin: 0;\">Registrar</p>\r\n    </div>\r\n</div>\r\n{{QR_CODE}}', NULL, NULL, NULL, 'Principal', NULL, NULL, NULL, 'Registrar', 'Date of Issue', 'Certificate No.', 'Verification QR', 1, 1, 0, 1, '2025-12-20 07:45:25', '2025-12-20 07:45:25'),
(6, 'Classic Completion Certificate', 'Custom', 'completion', 'CLASSICCOMPLETIONCER_2_1766216725', NULL, 'portrait', 'A4', 1, NULL, NULL, NULL, NULL, '<div style=\"text-align: center; margin-bottom: 50px; position: relative;\">\r\n    <div style=\"position: absolute; top: 0; left: 50%; transform: translateX(-50%); width: 100px; height: 100px; border: 5px solid #d4af37; border-radius: 50%; background: rgba(212, 175, 55, 0.1);\"></div>\r\n    <h1 style=\"font-family: \'Old Standard TT\', \'Times New Roman\', serif; font-size: 56px; font-weight: 700; color: #2c3e50; margin: 60px 0 15px 0; letter-spacing: 4px; text-transform: uppercase;\">CERTIFICATE</h1>\r\n    <h2 style=\"font-family: \'Crimson Text\', serif; font-size: 32px; font-weight: 400; color: #7f8c8d; margin: 0; font-style: italic;\">OF COMPLETION</h2>\r\n    <div style=\"margin-top: 25px;\">\r\n        <p style=\"font-size: 16px; color: #95a5a6; letter-spacing: 1px;\">{{SCHOOL_NAME}}</p>\r\n    </div>\r\n</div>', '<div style=\"text-align: center; padding: 30px 40px; font-family: \'Crimson Text\', \'Georgia\', serif;\">\r\n    <p style=\"font-size: 22px; color: #34495e; margin-bottom: 25px; line-height: 2;\">\r\n        This is to certify that\r\n    </p>\r\n    <div style=\"margin: 25px 0; padding: 25px 0;\">\r\n        <h3 style=\"font-family: \'Playfair Display\', serif; font-size: 48px; font-weight: 700; color: #1a5490; margin: 0; letter-spacing: 1px; text-decoration: underline; text-decoration-color: #d4af37; text-decoration-thickness: 3px;\">{{STUDENT_NAME}}</h3>\r\n    </div>\r\n    <p style=\"font-size: 19px; color: #2c3e50; margin: 30px 0; line-height: 2;\">\r\n        has successfully completed the course requirements for<br>\r\n        <strong style=\"color: #1a5490; font-size: 22px;\">{{CLASS}}</strong><br>\r\n        in the academic session <strong style=\"color: #1a5490;\">{{SESSION}}</strong>\r\n    </p>\r\n    <div style=\"margin-top: 35px; padding: 20px; background: #f8f9fa; border-radius: 8px; border-left: 5px solid #d4af37;\">\r\n        <p style=\"font-size: 15px; color: #495057; margin: 8px 0;\">\r\n            <strong>Certificate ID:</strong> {{CERTIFICATE_ID}}\r\n        </p>\r\n        <p style=\"font-size: 15px; color: #495057; margin: 8px 0;\">\r\n            <strong>Issued on:</strong> {{DATE}}\r\n        </p>\r\n    </div>\r\n</div>', '<div style=\"display: flex; justify-content: space-between; margin-top: 80px; padding: 0 50px;\">\r\n    <div style=\"text-align: center; flex: 1;\">\r\n        <div style=\"border-top: 3px solid #2c3e50; width: 180px; margin: 0 auto 12px; padding-top: 10px;\"></div>\r\n        <p style=\"font-weight: 700; font-size: 18px; color: #2c3e50; margin: 8px 0;\">{{PRINCIPAL_SIGNATURE}}</p>\r\n        <p style=\"font-size: 13px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; margin: 0;\">Principal</p>\r\n    </div>\r\n    <div style=\"text-align: center; flex: 1;\">\r\n        <div style=\"border-top: 3px solid #2c3e50; width: 180px; margin: 0 auto 12px; padding-top: 10px;\"></div>\r\n        <p style=\"font-weight: 700; font-size: 18px; color: #2c3e50; margin: 8px 0;\">{{REGISTRAR_SIGNATURE}}</p>\r\n        <p style=\"font-size: 13px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; margin: 0;\">Registrar</p>\r\n    </div>\r\n</div>\r\n<div style=\"text-align: center; margin-top: 30px;\">{{QR_CODE}}</div>', NULL, NULL, NULL, 'Principal', NULL, NULL, NULL, 'Registrar', 'Date of Issue', 'Certificate No.', 'Verification QR', 1, 0, 1, 1, '2025-12-20 07:45:25', '2025-12-20 07:45:25'),
(7, 'Modern Achievement Certificate', 'Custom', 'achievement', 'MODERNACHIEVEMENTCER_3_1766216725', NULL, 'landscape', 'A4', 1, NULL, NULL, NULL, NULL, '<div style=\"text-align: center; margin-bottom: 35px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px 20px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);\">\r\n    <h1 style=\"font-family: \'Montserrat\', \'Arial\', sans-serif; font-size: 58px; font-weight: 800; color: #ffffff; margin: 0; letter-spacing: 2px; text-transform: uppercase; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);\">ACHIEVEMENT</h1>\r\n    <h2 style=\"font-family: \'Montserrat\', sans-serif; font-size: 24px; font-weight: 300; color: #f0f0f0; margin: 10px 0 0 0; letter-spacing: 3px;\">CERTIFICATE</h2>\r\n    <p style=\"font-size: 16px; color: #e0e0e0; margin-top: 15px; letter-spacing: 1px;\">{{SCHOOL_NAME}}</p>\r\n</div>', '<div style=\"text-align: center; padding: 35px 25px;\">\r\n    <p style=\"font-size: 20px; color: #2c3e50; margin-bottom: 25px; font-family: \'Open Sans\', sans-serif; font-weight: 300;\">\r\n        This certificate is proudly presented to\r\n    </p>\r\n    <div style=\"margin: 30px 0; padding: 25px; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); border-radius: 15px; box-shadow: 0 3px 10px rgba(0,0,0,0.1);\">\r\n        <h3 style=\"font-family: \'Montserrat\', sans-serif; font-size: 44px; font-weight: 700; color: #667eea; margin: 0; letter-spacing: 1px;\">{{STUDENT_NAME}}</h3>\r\n    </div>\r\n    <p style=\"font-size: 18px; color: #34495e; margin: 30px 0; font-family: \'Open Sans\', sans-serif; line-height: 1.8;\">\r\n        in recognition of outstanding achievement and excellence in<br>\r\n        <strong style=\"color: #667eea; font-size: 20px;\">{{CLASS}}</strong><br>\r\n        during the <strong style=\"color: #667eea;\">{{SESSION}}</strong> academic year\r\n    </p>\r\n    <div style=\"margin-top: 35px; display: inline-block; padding: 15px 30px; background: #667eea; border-radius: 25px; color: white;\">\r\n        <p style=\"font-size: 14px; margin: 5px 0; font-weight: 600;\">Certificate No: {{CERTIFICATE_ID}}</p>\r\n        <p style=\"font-size: 14px; margin: 5px 0; font-weight: 600;\">Date: {{DATE}}</p>\r\n    </div>\r\n</div>', '<div style=\"display: flex; justify-content: space-between; margin-top: 50px; padding: 0 30px;\">\r\n    <div style=\"text-align: center; flex: 1;\">\r\n        <div style=\"border-top: 2px solid #667eea; width: 180px; margin: 0 auto 10px; padding-top: 8px;\"></div>\r\n        <p style=\"font-weight: 600; font-size: 16px; color: #667eea; margin: 5px 0;\">{{PRINCIPAL_SIGNATURE}}</p>\r\n        <p style=\"font-size: 12px; color: #7f8c8d; text-transform: uppercase; margin: 0;\">Principal</p>\r\n    </div>\r\n    <div style=\"text-align: center; flex: 1;\">\r\n        <div style=\"border-top: 2px solid #667eea; width: 180px; margin: 0 auto 10px; padding-top: 8px;\"></div>\r\n        <p style=\"font-weight: 600; font-size: 16px; color: #667eea; margin: 5px 0;\">{{REGISTRAR_SIGNATURE}}</p>\r\n        <p style=\"font-size: 12px; color: #7f8c8d; text-transform: uppercase; margin: 0;\">Registrar</p>\r\n    </div>\r\n</div>\r\n<div style=\"text-align: center; margin-top: 25px;\">{{QR_CODE}}</div>', NULL, NULL, NULL, 'Principal', NULL, NULL, NULL, 'Registrar', 'Date of Issue', 'Certificate No.', 'Verification QR', 1, 0, 0, 1, '2025-12-20 07:45:25', '2025-12-20 07:45:25'),
(8, 'Traditional Promotion Certificate', 'Custom', 'promotion', 'TRADITIONALPROMOTION_4_1766216725', NULL, 'portrait', 'A4', 1, NULL, NULL, NULL, NULL, '<div style=\"text-align: center; margin-bottom: 45px; position: relative;\">\r\n    <div style=\"display: flex; justify-content: center; align-items: center; margin-bottom: 20px;\">\r\n        <div style=\"width: 60px; height: 60px; border: 4px solid #c0392b; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: rgba(192, 57, 43, 0.1);\">\r\n            <span style=\"font-size: 30px; color: #c0392b;\">✓</span>\r\n        </div>\r\n    </div>\r\n    <h1 style=\"font-family: \'Merriweather\', \'Times New Roman\', serif; font-size: 52px; font-weight: 900; color: #c0392b; margin: 15px 0; letter-spacing: 3px; text-transform: uppercase;\">PROMOTION</h1>\r\n    <h2 style=\"font-family: \'Merriweather\', serif; font-size: 28px; font-weight: 400; color: #7f8c8d; margin: 0; font-style: italic;\">CERTIFICATE</h2>\r\n    <div style=\"margin-top: 20px; padding-top: 15px; border-top: 2px solid #ecf0f1;\">\r\n        <p style=\"font-size: 15px; color: #95a5a6; letter-spacing: 1.5px;\">{{SCHOOL_NAME}}</p>\r\n    </div>\r\n</div>', '<div style=\"text-align: center; padding: 35px 45px; font-family: \'Merriweather\', \'Georgia\', serif;\">\r\n    <p style=\"font-size: 21px; color: #2c3e50; margin-bottom: 30px; line-height: 2;\">\r\n        This is to certify that\r\n    </p>\r\n    <div style=\"margin: 30px 0; padding: 30px 0; border-top: 4px double #c0392b; border-bottom: 4px double #c0392b;\">\r\n        <h3 style=\"font-family: \'Merriweather\', serif; font-size: 46px; font-weight: 700; color: #c0392b; margin: 0; letter-spacing: 1px;\">{{STUDENT_NAME}}</h3>\r\n    </div>\r\n    <p style=\"font-size: 19px; color: #34495e; margin: 35px 0; line-height: 2;\">\r\n        has been promoted from <strong style=\"color: #c0392b;\">{{CLASS}}</strong><br>\r\n        for the academic session <strong style=\"color: #c0392b;\">{{SESSION}}</strong><br>\r\n        in recognition of satisfactory academic performance and conduct.\r\n    </p>\r\n    <div style=\"margin-top: 40px; padding: 18px; background: #fff5f5; border: 2px solid #c0392b; border-radius: 8px;\">\r\n        <p style=\"font-size: 14px; color: #2c3e50; margin: 6px 0;\">\r\n            <strong>Promotion Certificate No:</strong> {{CERTIFICATE_ID}}\r\n        </p>\r\n        <p style=\"font-size: 14px; color: #2c3e50; margin: 6px 0;\">\r\n            <strong>Date of Promotion:</strong> {{DATE}}\r\n        </p>\r\n    </div>\r\n</div>', '<div style=\"display: flex; justify-content: space-between; margin-top: 70px; padding: 0 55px;\">\r\n    <div style=\"text-align: center; flex: 1;\">\r\n        <div style=\"border-top: 3px solid #c0392b; width: 170px; margin: 0 auto 12px; padding-top: 10px;\"></div>\r\n        <p style=\"font-weight: 700; font-size: 17px; color: #c0392b; margin: 8px 0;\">{{PRINCIPAL_SIGNATURE}}</p>\r\n        <p style=\"font-size: 12px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; margin: 0;\">Principal</p>\r\n    </div>\r\n    <div style=\"text-align: center; flex: 1;\">\r\n        <div style=\"border-top: 3px solid #c0392b; width: 170px; margin: 0 auto 12px; padding-top: 10px;\"></div>\r\n        <p style=\"font-weight: 700; font-size: 17px; color: #c0392b; margin: 8px 0;\">{{REGISTRAR_SIGNATURE}}</p>\r\n        <p style=\"font-size: 12px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; margin: 0;\">Registrar</p>\r\n    </div>\r\n</div>\r\n<div style=\"text-align: center; margin-top: 25px;\">{{QR_CODE}}</div>', NULL, NULL, NULL, 'Principal', NULL, NULL, NULL, 'Registrar', 'Date of Issue', 'Certificate No.', 'Verification QR', 1, 1, 0, 1, '2025-12-20 07:45:25', '2025-12-20 07:45:25'),
(9, 'Elegant Character Certificate', 'Custom', 'character', 'ELEGANTCHARACTERCERT_5_1766216725', NULL, 'portrait', 'A4', 1, NULL, NULL, NULL, NULL, '<div style=\"text-align: center; margin-bottom: 50px;\">\r\n    <div style=\"border: 6px double #27ae60; padding: 25px; margin-bottom: 20px; display: inline-block;\">\r\n        <h1 style=\"font-family: \'Lora\', \'Times New Roman\', serif; font-size: 50px; font-weight: 700; color: #27ae60; margin: 0; letter-spacing: 4px; text-transform: uppercase;\">CHARACTER</h1>\r\n    </div>\r\n    <h2 style=\"font-family: \'Lora\', serif; font-size: 30px; font-weight: 400; color: #2c3e50; margin: 15px 0 0 0; font-style: italic;\">CERTIFICATE</h2>\r\n    <div style=\"margin-top: 25px;\">\r\n        <p style=\"font-size: 16px; color: #7f8c8d; letter-spacing: 2px;\">{{SCHOOL_NAME}}</p>\r\n    </div>\r\n</div>', '<div style=\"text-align: center; padding: 30px 40px; font-family: \'Lora\', \'Georgia\', serif;\">\r\n    <p style=\"font-size: 20px; color: #2c3e50; margin-bottom: 25px; line-height: 2;\">\r\n        This is to certify that\r\n    </p>\r\n    <div style=\"margin: 25px 0; padding: 20px 0;\">\r\n        <h3 style=\"font-family: \'Lora\', serif; font-size: 44px; font-weight: 700; color: #27ae60; margin: 0; letter-spacing: 1px;\">{{STUDENT_NAME}}</h3>\r\n        <p style=\"font-size: 16px; color: #7f8c8d; margin: 10px 0 0 0;\">Student ID: {{STUDENT_ID}}</p>\r\n    </div>\r\n    <p style=\"font-size: 18px; color: #34495e; margin: 30px 0; line-height: 2;\">\r\n        was a student of this institution in <strong style=\"color: #27ae60;\">{{CLASS}}</strong><br>\r\n        during the academic session <strong style=\"color: #27ae60;\">{{SESSION}}</strong><br>\r\n        and has shown exemplary character, conduct, and behavior throughout the period.\r\n    </p>\r\n    <div style=\"margin-top: 35px; padding: 20px; background: #e8f8f5; border-left: 5px solid #27ae60; border-radius: 5px;\">\r\n        <p style=\"font-size: 15px; color: #2c3e50; margin: 8px 0;\">\r\n            <strong>Certificate Number:</strong> {{CERTIFICATE_ID}}\r\n        </p>\r\n        <p style=\"font-size: 15px; color: #2c3e50; margin: 8px 0;\">\r\n            <strong>Date of Issue:</strong> {{DATE}}\r\n        </p>\r\n    </div>\r\n</div>', '<div style=\"display: flex; justify-content: space-between; margin-top: 75px; padding: 0 50px;\">\r\n    <div style=\"text-align: center; flex: 1;\">\r\n        <div style=\"border-top: 3px solid #27ae60; width: 175px; margin: 0 auto 12px; padding-top: 10px;\"></div>\r\n        <p style=\"font-weight: 700; font-size: 17px; color: #27ae60; margin: 8px 0;\">{{PRINCIPAL_SIGNATURE}}</p>\r\n        <p style=\"font-size: 12px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; margin: 0;\">Principal</p>\r\n    </div>\r\n    <div style=\"text-align: center; flex: 1;\">\r\n        <div style=\"border-top: 3px solid #27ae60; width: 175px; margin: 0 auto 12px; padding-top: 10px;\"></div>\r\n        <p style=\"font-weight: 700; font-size: 17px; color: #27ae60; margin: 8px 0;\">{{REGISTRAR_SIGNATURE}}</p>\r\n        <p style=\"font-size: 12px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; margin: 0;\">Registrar</p>\r\n    </div>\r\n</div>\r\n<div style=\"text-align: center; margin-top: 30px;\">{{QR_CODE}}</div>', NULL, NULL, NULL, 'Principal', NULL, NULL, NULL, 'Registrar', 'Date of Issue', 'Certificate No.', 'Verification QR', 1, 0, 0, 1, '2025-12-20 07:45:25', '2025-12-20 07:45:25'),
(10, 'Vibrant Participation Certificate', 'Custom', 'participation', 'VIBRANTPARTICIPATION_6_1766216725', NULL, 'landscape', 'A4', 1, NULL, NULL, NULL, NULL, '<div style=\"text-align: center; margin-bottom: 30px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 25px 15px; border-radius: 12px; box-shadow: 0 5px 20px rgba(245, 87, 108, 0.3);\">\r\n    <h1 style=\"font-family: \'Poppins\', \'Arial\', sans-serif; font-size: 54px; font-weight: 800; color: #ffffff; margin: 0; letter-spacing: 2px; text-transform: uppercase; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);\">PARTICIPATION</h1>\r\n    <h2 style=\"font-family: \'Poppins\', sans-serif; font-size: 22px; font-weight: 300; color: #ffffff; margin: 8px 0 0 0; letter-spacing: 2px;\">CERTIFICATE</h2>\r\n    <p style=\"font-size: 15px; color: #ffffff; margin-top: 12px; opacity: 0.95; letter-spacing: 1px;\">{{SCHOOL_NAME}}</p>\r\n</div>', '<div style=\"text-align: center; padding: 30px 20px;\">\r\n    <p style=\"font-size: 19px; color: #2c3e50; margin-bottom: 22px; font-family: \'Poppins\', sans-serif; font-weight: 400;\">\r\n        This certificate is awarded to\r\n    </p>\r\n    <div style=\"margin: 25px 0; padding: 22px; background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%); border-radius: 12px; box-shadow: 0 3px 12px rgba(0,0,0,0.1);\">\r\n        <h3 style=\"font-family: \'Poppins\', sans-serif; font-size: 40px; font-weight: 700; color: #2d3436; margin: 0; letter-spacing: 1px;\">{{STUDENT_NAME}}</h3>\r\n    </div>\r\n    <p style=\"font-size: 17px; color: #34495e; margin: 28px 0; font-family: \'Poppins\', sans-serif; line-height: 1.8;\">\r\n        for active participation and valuable contribution in<br>\r\n        <strong style=\"color: #f5576c; font-size: 19px;\">{{CLASS}}</strong><br>\r\n        during the <strong style=\"color: #f5576c;\">{{SESSION}}</strong> academic session\r\n    </p>\r\n    <div style=\"margin-top: 30px; display: inline-block; padding: 12px 28px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 20px; color: white; box-shadow: 0 3px 10px rgba(245, 87, 108, 0.3);\">\r\n        <p style=\"font-size: 13px; margin: 4px 0; font-weight: 600;\">Cert. No: {{CERTIFICATE_ID}} | Date: {{DATE}}</p>\r\n    </div>\r\n</div>', '<div style=\"display: flex; justify-content: space-between; margin-top: 45px; padding: 0 25px;\">\r\n    <div style=\"text-align: center; flex: 1;\">\r\n        <div style=\"border-top: 2px solid #f5576c; width: 170px; margin: 0 auto 9px; padding-top: 7px;\"></div>\r\n        <p style=\"font-weight: 600; font-size: 15px; color: #f5576c; margin: 5px 0;\">{{PRINCIPAL_SIGNATURE}}</p>\r\n        <p style=\"font-size: 11px; color: #7f8c8d; text-transform: uppercase; margin: 0;\">Principal</p>\r\n    </div>\r\n    <div style=\"text-align: center; flex: 1;\">\r\n        <div style=\"border-top: 2px solid #f5576c; width: 170px; margin: 0 auto 9px; padding-top: 7px;\"></div>\r\n        <p style=\"font-weight: 600; font-size: 15px; color: #f5576c; margin: 5px 0;\">{{REGISTRAR_SIGNATURE}}</p>\r\n        <p style=\"font-size: 11px; color: #7f8c8d; text-transform: uppercase; margin: 0;\">Registrar</p>\r\n    </div>\r\n</div>\r\n<div style=\"text-align: center; margin-top: 20px;\">{{QR_CODE}}</div>', NULL, NULL, NULL, 'Principal', NULL, NULL, NULL, 'Registrar', 'Date of Issue', 'Certificate No.', 'Verification QR', 1, 0, 0, 1, '2025-12-20 07:45:25', '2025-12-20 07:45:25'),
(11, 'Premium Graduation Certificate', 'Custom', 'graduation', 'PREMIUMGRADUATIONCER_7_1766216725', NULL, 'landscape', 'A4', 1, NULL, NULL, NULL, NULL, '<div style=\"text-align: center; margin-bottom: 40px; position: relative;\">\r\n    <div style=\"position: absolute; top: -10px; left: 50%; transform: translateX(-50%); width: 120px; height: 120px; border: 6px solid #8b4513; border-radius: 50%; background: linear-gradient(135deg, #daa520 0%, #b8860b 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(139, 69, 19, 0.3);\">\r\n        <span style=\"font-size: 50px; color: #ffffff; font-weight: bold;\">🎓</span>\r\n    </div>\r\n    <h1 style=\"font-family: \'Cinzel\', \'Times New Roman\', serif; font-size: 62px; font-weight: 700; color: #8b4513; margin: 70px 0 12px 0; letter-spacing: 4px; text-transform: uppercase;\">GRADUATION</h1>\r\n    <h2 style=\"font-family: \'Cinzel\', serif; font-size: 26px; font-weight: 400; color: #a0522d; margin: 0; font-style: italic; letter-spacing: 2px;\">CERTIFICATE OF EXCELLENCE</h2>\r\n    <div style=\"margin-top: 20px; padding-top: 15px; border-top: 3px solid #daa520;\">\r\n        <p style=\"font-size: 17px; color: #8b7355; letter-spacing: 1.5px; font-weight: 500;\">{{SCHOOL_NAME}}</p>\r\n    </div>\r\n</div>', '<div style=\"text-align: center; padding: 35px 30px; font-family: \'Cinzel\', \'Georgia\', serif;\">\r\n    <p style=\"font-size: 21px; color: #2c3e50; margin-bottom: 28px; line-height: 1.9;\">\r\n        This is to certify that\r\n    </p>\r\n    <div style=\"margin: 28px 0; padding: 28px 0; border-top: 5px solid #daa520; border-bottom: 5px solid #daa520; background: linear-gradient(to bottom, rgba(218, 165, 32, 0.05), transparent);\">\r\n        <h3 style=\"font-family: \'Cinzel\', serif; font-size: 46px; font-weight: 700; color: #8b4513; margin: 0; letter-spacing: 2px;\">{{STUDENT_NAME}}</h3>\r\n    </div>\r\n    <p style=\"font-size: 19px; color: #34495e; margin: 32px 0; line-height: 1.9;\">\r\n        has successfully completed all requirements and graduated from<br>\r\n        <strong style=\"color: #8b4513; font-size: 22px;\">{{CLASS}}</strong><br>\r\n        with distinction during the <strong style=\"color: #8b4513;\">{{SESSION}}</strong> academic year.\r\n    </p>\r\n    <div style=\"margin-top: 38px; padding: 18px; background: #faf8f3; border: 3px solid #daa520; border-radius: 8px; display: inline-block;\">\r\n        <p style=\"font-size: 14px; color: #2c3e50; margin: 6px 0; font-weight: 600;\">\r\n            <strong>Graduation Certificate No:</strong> {{CERTIFICATE_ID}}\r\n        </p>\r\n        <p style=\"font-size: 14px; color: #2c3e50; margin: 6px 0; font-weight: 600;\">\r\n            <strong>Date of Graduation:</strong> {{DATE}}\r\n        </p>\r\n    </div>\r\n</div>', '<div style=\"display: flex; justify-content: space-between; margin-top: 55px; padding: 0 35px;\">\r\n    <div style=\"text-align: center; flex: 1;\">\r\n        <div style=\"border-top: 3px solid #8b4513; width: 190px; margin: 0 auto 11px; padding-top: 9px;\"></div>\r\n        <p style=\"font-weight: 700; font-size: 17px; color: #8b4513; margin: 7px 0;\">{{PRINCIPAL_SIGNATURE}}</p>\r\n        <p style=\"font-size: 12px; color: #8b7355; text-transform: uppercase; letter-spacing: 1px; margin: 0; font-weight: 500;\">Principal</p>\r\n    </div>\r\n    <div style=\"text-align: center; flex: 1;\">\r\n        <div style=\"border-top: 3px solid #8b4513; width: 190px; margin: 0 auto 11px; padding-top: 9px;\"></div>\r\n        <p style=\"font-weight: 700; font-size: 17px; color: #8b4513; margin: 7px 0;\">{{REGISTRAR_SIGNATURE}}</p>\r\n        <p style=\"font-size: 12px; color: #8b7355; text-transform: uppercase; letter-spacing: 1px; margin: 0; font-weight: 500;\">Registrar</p>\r\n    </div>\r\n</div>\r\n<div style=\"text-align: center; margin-top: 25px;\">{{QR_CODE}}</div>', NULL, NULL, NULL, 'Principal', NULL, NULL, NULL, 'Registrar', 'Date of Issue', 'Certificate No.', 'Verification QR', 1, 1, 0, 1, '2025-12-20 07:45:25', '2025-12-20 07:45:25'),
(12, 'Minimalist Completion Certificate', 'Custom', 'completion', 'MINIMALISTCOMPLETION_8_1766216725', NULL, 'portrait', 'A4', 1, NULL, NULL, NULL, NULL, '<div style=\"text-align: center; margin-bottom: 55px; padding-top: 30px;\">\r\n    <div style=\"width: 80px; height: 4px; background: #3498db; margin: 0 auto 25px;\"></div>\r\n    <h1 style=\"font-family: \'Raleway\', \'Arial\', sans-serif; font-size: 48px; font-weight: 300; color: #2c3e50; margin: 0; letter-spacing: 8px; text-transform: uppercase;\">CERTIFICATE</h1>\r\n    <h2 style=\"font-family: \'Raleway\', sans-serif; font-size: 24px; font-weight: 300; color: #7f8c8d; margin: 12px 0 0 0; letter-spacing: 4px;\">OF COMPLETION</h2>\r\n    <div style=\"width: 80px; height: 4px; background: #3498db; margin: 25px auto 0;\"></div>\r\n    <p style=\"font-size: 14px; color: #95a5a6; margin-top: 20px; letter-spacing: 2px; font-weight: 300;\">{{SCHOOL_NAME}}</p>\r\n</div>', '<div style=\"text-align: center; padding: 40px 50px; font-family: \'Raleway\', sans-serif;\">\r\n    <p style=\"font-size: 20px; color: #34495e; margin-bottom: 30px; font-weight: 300; line-height: 2;\">\r\n        This is to certify that\r\n    </p>\r\n    <div style=\"margin: 30px 0; padding: 25px 0;\">\r\n        <h3 style=\"font-family: \'Raleway\', sans-serif; font-size: 42px; font-weight: 400; color: #3498db; margin: 0; letter-spacing: 2px;\">{{STUDENT_NAME}}</h3>\r\n    </div>\r\n    <p style=\"font-size: 18px; color: #2c3e50; margin: 35px 0; font-weight: 300; line-height: 2;\">\r\n        has successfully completed<br>\r\n        <strong style=\"color: #3498db; font-weight: 500;\">{{CLASS}}</strong><br>\r\n        for the academic session <strong style=\"color: #3498db; font-weight: 500;\">{{SESSION}}</strong>\r\n    </p>\r\n    <div style=\"margin-top: 40px; padding: 15px; border: 1px solid #ecf0f1; background: #f8f9fa;\">\r\n        <p style=\"font-size: 13px; color: #7f8c8d; margin: 5px 0; font-weight: 300;\">\r\n            Certificate ID: <strong style=\"color: #2c3e50;\">{{CERTIFICATE_ID}}</strong>\r\n        </p>\r\n        <p style=\"font-size: 13px; color: #7f8c8d; margin: 5px 0; font-weight: 300;\">\r\n            Issued: <strong style=\"color: #2c3e50;\">{{DATE}}</strong>\r\n        </p>\r\n    </div>\r\n</div>', '<div style=\"display: flex; justify-content: space-between; margin-top: 85px; padding: 0 60px;\">\r\n    <div style=\"text-align: center; flex: 1;\">\r\n        <div style=\"border-top: 1px solid #3498db; width: 160px; margin: 0 auto 10px; padding-top: 8px;\"></div>\r\n        <p style=\"font-weight: 500; font-size: 16px; color: #3498db; margin: 6px 0;\">{{PRINCIPAL_SIGNATURE}}</p>\r\n        <p style=\"font-size: 11px; color: #95a5a6; text-transform: uppercase; letter-spacing: 1px; margin: 0; font-weight: 300;\">Principal</p>\r\n    </div>\r\n    <div style=\"text-align: center; flex: 1;\">\r\n        <div style=\"border-top: 1px solid #3498db; width: 160px; margin: 0 auto 10px; padding-top: 8px;\"></div>\r\n        <p style=\"font-weight: 500; font-size: 16px; color: #3498db; margin: 6px 0;\">{{REGISTRAR_SIGNATURE}}</p>\r\n        <p style=\"font-size: 11px; color: #95a5a6; text-transform: uppercase; letter-spacing: 1px; margin: 0; font-weight: 300;\">Registrar</p>\r\n    </div>\r\n</div>\r\n<div style=\"text-align: center; margin-top: 30px;\">{{QR_CODE}}</div>', NULL, NULL, NULL, 'Principal', NULL, NULL, NULL, 'Registrar', 'Date of Issue', 'Certificate No.', 'Verification QR', 1, 0, 0, 1, '2025-12-20 07:45:25', '2025-12-20 07:45:25'),
(13, 'Royal Achievement Certificate', 'Custom', 'achievement', 'ROYALACHIEVEMENTCERT_9_1766216725', NULL, 'landscape', 'A4', 1, NULL, NULL, NULL, NULL, '<div style=\"text-align: center; margin-bottom: 35px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); padding: 28px 18px; border-radius: 15px; box-shadow: 0 6px 25px rgba(30, 60, 114, 0.4); position: relative; overflow: hidden;\">\r\n    <div style=\"position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; border: 3px solid rgba(255,255,255,0.1); border-radius: 50%;\"></div>\r\n    <div style=\"position: absolute; bottom: -30px; left: -30px; width: 150px; height: 150px; border: 3px solid rgba(255,255,255,0.1); border-radius: 50%;\"></div>\r\n    <h1 style=\"font-family: \'Bebas Neue\', \'Arial\', sans-serif; font-size: 60px; font-weight: 400; color: #ffffff; margin: 0; letter-spacing: 4px; text-transform: uppercase; position: relative; z-index: 1; text-shadow: 2px 2px 8px rgba(0,0,0,0.3);\">ACHIEVEMENT</h1>\r\n    <h2 style=\"font-family: \'Bebas Neue\', sans-serif; font-size: 26px; font-weight: 400; color: #e8f4f8; margin: 8px 0 0 0; letter-spacing: 3px; position: relative; z-index: 1;\">CERTIFICATE OF EXCELLENCE</h2>\r\n    <p style=\"font-size: 15px; color: #b8d4e3; margin-top: 15px; letter-spacing: 1.5px; position: relative; z-index: 1;\">{{SCHOOL_NAME}}</p>\r\n</div>', '<div style=\"text-align: center; padding: 32px 22px;\">\r\n    <p style=\"font-size: 20px; color: #2c3e50; margin-bottom: 26px; font-family: \'Roboto\', sans-serif; font-weight: 300;\">\r\n        This certificate is presented to\r\n    </p>\r\n    <div style=\"margin: 28px 0; padding: 26px; background: linear-gradient(135deg, #f0f4f8 0%, #d6e4f0 100%); border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: 2px solid #2a5298;\">\r\n        <h3 style=\"font-family: \'Bebas Neue\', sans-serif; font-size: 48px; font-weight: 400; color: #1e3c72; margin: 0; letter-spacing: 2px;\">{{STUDENT_NAME}}</h3>\r\n    </div>\r\n    <p style=\"font-size: 18px; color: #34495e; margin: 30px 0; font-family: \'Roboto\', sans-serif; line-height: 1.9; font-weight: 300;\">\r\n        in recognition of exceptional achievement and outstanding performance in<br>\r\n        <strong style=\"color: #1e3c72; font-size: 20px; font-weight: 500;\">{{CLASS}}</strong><br>\r\n        during the <strong style=\"color: #1e3c72; font-weight: 500;\">{{SESSION}}</strong> academic year\r\n    </p>\r\n    <div style=\"margin-top: 32px; display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 22px; color: white; box-shadow: 0 4px 15px rgba(30, 60, 114, 0.3);\">\r\n        <p style=\"font-size: 13px; margin: 5px 0; font-weight: 500;\">Certificate No: {{CERTIFICATE_ID}} | Date: {{DATE}}</p>\r\n    </div>\r\n</div>', '<div style=\"display: flex; justify-content: space-between; margin-top: 48px; padding: 0 28px;\">\r\n    <div style=\"text-align: center; flex: 1;\">\r\n        <div style=\"border-top: 2px solid #1e3c72; width: 175px; margin: 0 auto 10px; padding-top: 8px;\"></div>\r\n        <p style=\"font-weight: 600; font-size: 16px; color: #1e3c72; margin: 5px 0;\">{{PRINCIPAL_SIGNATURE}}</p>\r\n        <p style=\"font-size: 11px; color: #7f8c8d; text-transform: uppercase; margin: 0;\">Principal</p>\r\n    </div>\r\n    <div style=\"text-align: center; flex: 1;\">\r\n        <div style=\"border-top: 2px solid #1e3c72; width: 175px; margin: 0 auto 10px; padding-top: 8px;\"></div>\r\n        <p style=\"font-weight: 600; font-size: 16px; color: #1e3c72; margin: 5px 0;\">{{REGISTRAR_SIGNATURE}}</p>\r\n        <p style=\"font-size: 11px; color: #7f8c8d; text-transform: uppercase; margin: 0;\">Registrar</p>\r\n    </div>\r\n</div>\r\n<div style=\"text-align: center; margin-top: 22px;\">{{QR_CODE}}</div>', NULL, NULL, NULL, 'Principal', NULL, NULL, NULL, 'Registrar', 'Date of Issue', 'Certificate No.', 'Verification QR', 1, 0, 0, 1, '2025-12-20 07:45:25', '2025-12-20 07:45:25'),
(14, 'Classic Custom Certificate', 'Custom', 'custom', 'CLASSICCUSTOMCERTIFI_10_1766216725', NULL, 'portrait', 'A4', 1, NULL, NULL, NULL, NULL, '<div style=\"text-align: center; margin-bottom: 48px; position: relative;\">\r\n    <div style=\"display: flex; justify-content: center; align-items: center; margin-bottom: 18px;\">\r\n        <div style=\"flex: 1; height: 2px; background: linear-gradient(to right, transparent, #d4af37, transparent);\"></div>\r\n        <div style=\"width: 70px; height: 70px; border: 4px solid #d4af37; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 20px; background: rgba(212, 175, 55, 0.1);\">\r\n            <span style=\"font-size: 32px; color: #d4af37;\">★</span>\r\n        </div>\r\n        <div style=\"flex: 1; height: 2px; background: linear-gradient(to left, transparent, #d4af37, transparent);\"></div>\r\n    </div>\r\n    <h1 style=\"font-family: \'Libre Baskerville\', \'Times New Roman\', serif; font-size: 54px; font-weight: 700; color: #2c3e50; margin: 0; letter-spacing: 3px; text-transform: uppercase;\">CERTIFICATE</h1>\r\n    <h2 style=\"font-family: \'Libre Baskerville\', serif; font-size: 26px; font-weight: 400; color: #7f8c8d; margin: 12px 0 0 0; font-style: italic;\">OF MERIT</h2>\r\n    <div style=\"margin-top: 22px; padding-top: 18px; border-top: 2px solid #ecf0f1;\">\r\n        <p style=\"font-size: 15px; color: #95a5a6; letter-spacing: 1.5px;\">{{SCHOOL_NAME}}</p>\r\n    </div>\r\n</div>', '<div style=\"text-align: center; padding: 32px 42px; font-family: \'Libre Baskerville\', \'Georgia\', serif;\">\r\n    <p style=\"font-size: 21px; color: #2c3e50; margin-bottom: 28px; line-height: 2;\">\r\n        This is to certify that\r\n    </p>\r\n    <div style=\"margin: 28px 0; padding: 24px 0; border-top: 4px double #d4af37; border-bottom: 4px double #d4af37;\">\r\n        <h3 style=\"font-family: \'Libre Baskerville\', serif; font-size: 45px; font-weight: 700; color: #2c3e50; margin: 0; letter-spacing: 1px;\">{{STUDENT_NAME}}</h3>\r\n    </div>\r\n    <p style=\"font-size: 19px; color: #34495e; margin: 32px 0; line-height: 2;\">\r\n        has demonstrated excellence and commitment in<br>\r\n        <strong style=\"color: #d4af37; font-size: 21px;\">{{CLASS}}</strong><br>\r\n        throughout the academic session <strong style=\"color: #d4af37;\">{{SESSION}}</strong>\r\n    </p>\r\n    <div style=\"margin-top: 38px; padding: 17px; background: #fef9e7; border: 2px solid #d4af37; border-radius: 6px;\">\r\n        <p style=\"font-size: 14px; color: #2c3e50; margin: 6px 0;\">\r\n            <strong>Certificate Number:</strong> {{CERTIFICATE_ID}}\r\n        </p>\r\n        <p style=\"font-size: 14px; color: #2c3e50; margin: 6px 0;\">\r\n            <strong>Date of Issue:</strong> {{DATE}}\r\n        </p>\r\n    </div>\r\n</div>', '<div style=\"display: flex; justify-content: space-between; margin-top: 78px; padding: 0 52px;\">\r\n    <div style=\"text-align: center; flex: 1;\">\r\n        <div style=\"border-top: 3px solid #d4af37; width: 168px; margin: 0 auto 11px; padding-top: 9px;\"></div>\r\n        <p style=\"font-weight: 700; font-size: 17px; color: #2c3e50; margin: 7px 0;\">{{PRINCIPAL_SIGNATURE}}</p>\r\n        <p style=\"font-size: 12px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; margin: 0;\">Principal</p>\r\n    </div>\r\n    <div style=\"text-align: center; flex: 1;\">\r\n        <div style=\"border-top: 3px solid #d4af37; width: 168px; margin: 0 auto 11px; padding-top: 9px;\"></div>\r\n        <p style=\"font-weight: 700; font-size: 17px; color: #2c3e50; margin: 7px 0;\">{{REGISTRAR_SIGNATURE}}</p>\r\n        <p style=\"font-size: 12px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; margin: 0;\">Registrar</p>\r\n    </div>\r\n</div>\r\n<div style=\"text-align: center; margin-top: 28px;\">{{QR_CODE}}</div>', NULL, NULL, NULL, 'Principal', NULL, NULL, NULL, 'Registrar', 'Date of Issue', 'Certificate No.', 'Verification QR', 1, 1, 0, 1, '2025-12-20 07:45:25', '2025-12-20 07:45:25');

-- --------------------------------------------------------

--
-- Table structure for table `chart_of_accounts`
--

CREATE TABLE `chart_of_accounts` (
  `id` int(11) NOT NULL,
  `account_code` varchar(50) NOT NULL,
  `account_name` varchar(255) NOT NULL,
  `account_type` enum('Asset','Liability','Equity','Income','Expense') NOT NULL,
  `parent_account_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `class_name` varchar(100) NOT NULL,
  `class_code` varchar(50) NOT NULL,
  `class_order` int(11) DEFAULT 0,
  `branch_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `graduation_status` enum('Active','Graduated') DEFAULT 'Active',
  `graduated_at` datetime DEFAULT NULL,
  `graduated_by` int(11) DEFAULT NULL,
  `graduation_remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Classes table with graduation status tracking';

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `class_name`, `class_code`, `class_order`, `branch_id`, `description`, `is_active`, `graduation_status`, `graduated_at`, `graduated_by`, `graduation_remarks`, `created_at`) VALUES
(1, 'CA202', 'CA202', 30, 1, 'Testing class', 1, 'Active', NULL, NULL, NULL, '2025-11-22 15:49:35'),
(2, 'Fasalka 1aad', '1aad', 0, 2, 'Waa fasalka Koowaad', 1, 'Graduated', '2025-12-21 09:24:08', 1162, '', '2025-12-14 06:59:01'),
(3, 'Fasalka 2aad', '2aad', 1, 2, 'Fasalka labaad', 1, 'Active', NULL, NULL, NULL, '2025-12-14 07:00:13'),
(4, 'Fasalka 3aad', '3aad', 2, 2, 'Fasalka 3aad', 1, 'Active', NULL, NULL, NULL, '2025-12-14 07:01:27'),
(5, 'Fasalka 4aad', '4aad', 3, 2, 'Fasalka afaraad', 1, 'Active', NULL, NULL, NULL, '2025-12-14 07:01:55'),
(6, 'Class 2025', 'Class 2025', 5, 1, 'test', 1, 'Graduated', '2025-12-20 10:05:26', 1, '', '2025-12-20 07:03:19');

-- --------------------------------------------------------

--
-- Table structure for table `class_graduation_logs`
--

CREATE TABLE `class_graduation_logs` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `action` enum('Graduated','Reopened') NOT NULL,
  `students_affected` int(11) DEFAULT 0,
  `performed_by` int(11) NOT NULL,
  `remarks` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Audit log for class graduation actions';

--
-- Dumping data for table `class_graduation_logs`
--

INSERT INTO `class_graduation_logs` (`id`, `class_id`, `action`, `students_affected`, `performed_by`, `remarks`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 6, 'Graduated', 5, 1, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-20 07:05:26'),
(2, 2, 'Graduated', 160, 1162, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 06:24:08');

-- --------------------------------------------------------

--
-- Table structure for table `class_subjects`
--

CREATE TABLE `class_subjects` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `session_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `class_subjects`
--

INSERT INTO `class_subjects` (`id`, `class_id`, `subject_id`, `teacher_id`, `session_id`) VALUES
(1, 1, 1, 1, 1),
(2, 1, 3, 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `communication_logs`
--

CREATE TABLE `communication_logs` (
  `id` int(11) NOT NULL,
  `communication_type` enum('SMS','Email','WhatsApp','Push') NOT NULL,
  `recipient` varchar(255) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('Pending','Sent','Failed','Delivered') DEFAULT 'Pending',
  `error_message` text DEFAULT NULL,
  `sent_by` int(11) DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `communication_logs`
--

INSERT INTO `communication_logs` (`id`, `communication_type`, `recipient`, `subject`, `message`, `status`, `error_message`, `sent_by`, `sent_at`) VALUES
(1, 'Email', 'hagadxiis@gmail.com', 'Password Reset - School ERP System', '\r\n    <html>\r\n    <head>\r\n        <style>\r\n            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }\r\n            .container { max-width: 600px; margin: 0 auto; padding: 20px; }\r\n            .header { background: linear-gradient(135deg, #fa5c7c 0%, #ff6b9d 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }\r\n            .content { background: #f8f9fa; padding: 30px; }\r\n            .credentials { background: white; padding: 25px; border-left: 4px solid #fa5c7c; margin: 20px 0; border-radius: 4px; }\r\n            .password-box { background: #fff3cd; border: 2px dashed #ffc107; padding: 15px; margin: 15px 0; text-align: center; border-radius: 4px; }\r\n            .password-text { font-size: 24px; font-weight: bold; color: #856404; letter-spacing: 2px; font-family: \'Courier New\', monospace; }\r\n            .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; }\r\n            .button { display: inline-block; padding: 12px 30px; background: #fa5c7c; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }\r\n            .footer { text-align: center; padding: 20px; color: #999; font-size: 12px; }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\'container\'>\r\n            <div class=\'header\'>\r\n                <h1>🔒 Password Reset</h1>\r\n            </div>\r\n            <div class=\'content\'>\r\n                <p>Hello <strong>Admin</strong>,</p>\r\n                <p>Your password has been reset by an administrator. Please use the new password below to log in to your account:</p>\r\n                \r\n                <div class=\'credentials\'>\r\n                    <p><strong>Username:</strong> Admin</p>\r\n                    <div class=\'password-box\'>\r\n                        <p style=\'margin: 0 0 10px 0; color: #856404;\'><strong>Your New Password:</strong></p>\r\n                        <div class=\'password-text\'>b14dbff6d6d3</div>\r\n                    </div>\r\n                    <p style=\'margin-top: 15px;\'><strong>Login URL:</strong> <a href=\'http://localhost/schoolerp/\'>http://localhost/schoolerp/</a></p>\r\n                </div>\r\n                \r\n                <div class=\'warning\'>\r\n                    <p style=\'margin: 0;\'><strong>⚠️ Security Notice:</strong></p>\r\n                    <p style=\'margin: 10px 0 0 0;\'>For your security, please change this password immediately after logging in. Do not share this password with anyone.</p>\r\n                </div>\r\n                \r\n                <p style=\'text-align: center;\'>\r\n                    <a href=\'http://localhost/schoolerp/\' class=\'button\'>Login to Your Account</a>\r\n                </p>\r\n                \r\n                <p>If you did not request this password reset, please contact us immediately at info@uukowtech.com</p>\r\n                <p>Best regards,<br><strong>School ERP System Team</strong></p>\r\n            </div>\r\n            <div class=\'footer\'>\r\n                <p>&copy; 2025 School ERP System. All rights reserved.</p>\r\n                <p>This is an automated email. Please do not reply to this message.</p>\r\n            </div>\r\n        </div>\r\n    </body>\r\n    </html>\r\n    ', 'Sent', NULL, 1, '2025-11-22 19:20:34'),
(2, 'Email', 'uukoowtxt@gmail.com', 'Password Reset - School ERP System', '\r\n    <html>\r\n    <head>\r\n        <style>\r\n            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }\r\n            .container { max-width: 600px; margin: 0 auto; padding: 20px; }\r\n            .header { background: linear-gradient(135deg, #fa5c7c 0%, #ff6b9d 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }\r\n            .content { background: #f8f9fa; padding: 30px; }\r\n            .credentials { background: white; padding: 25px; border-left: 4px solid #fa5c7c; margin: 20px 0; border-radius: 4px; }\r\n            .password-box { background: #fff3cd; border: 2px dashed #ffc107; padding: 15px; margin: 15px 0; text-align: center; border-radius: 4px; }\r\n            .password-text { font-size: 24px; font-weight: bold; color: #856404; letter-spacing: 2px; font-family: \'Courier New\', monospace; }\r\n            .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; }\r\n            .button { display: inline-block; padding: 12px 30px; background: #fa5c7c; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }\r\n            .footer { text-align: center; padding: 20px; color: #999; font-size: 12px; }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\'container\'>\r\n            <div class=\'header\'>\r\n                <h1>🔒 Password Reset</h1>\r\n            </div>\r\n            <div class=\'content\'>\r\n                <p>Hello <strong>teacher</strong>,</p>\r\n                <p>Your password has been reset by an administrator. Please use the new password below to log in to your account:</p>\r\n                \r\n                <div class=\'credentials\'>\r\n                    <p><strong>Username:</strong> teacher</p>\r\n                    <div class=\'password-box\'>\r\n                        <p style=\'margin: 0 0 10px 0; color: #856404;\'><strong>Your New Password:</strong></p>\r\n                        <div class=\'password-text\'>48c4d4ad0a30</div>\r\n                    </div>\r\n                    <p style=\'margin-top: 15px;\'><strong>Login URL:</strong> <a href=\'http://localhost/schoolerp/\'>http://localhost/schoolerp/</a></p>\r\n                </div>\r\n                \r\n                <div class=\'warning\'>\r\n                    <p style=\'margin: 0;\'><strong>⚠️ Security Notice:</strong></p>\r\n                    <p style=\'margin: 10px 0 0 0;\'>For your security, please change this password immediately after logging in. Do not share this password with anyone.</p>\r\n                </div>\r\n                \r\n                <p style=\'text-align: center;\'>\r\n                    <a href=\'http://localhost/schoolerp/\' class=\'button\'>Login to Your Account</a>\r\n                </p>\r\n                \r\n                <p>If you did not request this password reset, please contact us immediately at info@uukowtech.com</p>\r\n                <p>Best regards,<br><strong>School ERP System Team</strong></p>\r\n            </div>\r\n            <div class=\'footer\'>\r\n                <p>&copy; 2025 School ERP System. All rights reserved.</p>\r\n                <p>This is an automated email. Please do not reply to this message.</p>\r\n            </div>\r\n        </div>\r\n    </body>\r\n    </html>\r\n    ', 'Sent', NULL, 1, '2025-11-22 19:49:49'),
(3, 'Email', '', 'Admission Acceptance', 'Dear Abdulkadir Uukow, Your admission application (No: APP000001) for Abdulkadir Uukow has been accepted. Please visit the school to complete enrollment. Contact: info@uukowtech.com', 'Sent', NULL, 1, '2025-11-30 08:15:25'),
(4, 'Email', '', 'Admission Acceptance', 'Dear Abdulkadir Uukow, Your admission application (No: APP000001) for Abdulkadir Uukow has been accepted. Please visit the school to complete enrollment. Contact: info@uukowtech.com', 'Sent', NULL, 1, '2025-11-30 08:16:50'),
(5, 'Email', 'info@uukowtech.com', 'Password Reset - School ERP System', '\r\n    <html>\r\n    <head>\r\n        <style>\r\n            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }\r\n            .container { max-width: 600px; margin: 0 auto; padding: 20px; }\r\n            .header { background: linear-gradient(135deg, #fa5c7c 0%, #ff6b9d 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }\r\n            .content { background: #f8f9fa; padding: 30px; }\r\n            .credentials { background: white; padding: 25px; border-left: 4px solid #fa5c7c; margin: 20px 0; border-radius: 4px; }\r\n            .password-box { background: #fff3cd; border: 2px dashed #ffc107; padding: 15px; margin: 15px 0; text-align: center; border-radius: 4px; }\r\n            .password-text { font-size: 24px; font-weight: bold; color: #856404; letter-spacing: 2px; font-family: \'Courier New\', monospace; }\r\n            .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; }\r\n            .button { display: inline-block; padding: 12px 30px; background: #fa5c7c; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }\r\n            .footer { text-align: center; padding: 20px; color: #999; font-size: 12px; }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\'container\'>\r\n            <div class=\'header\'>\r\n                <h1>🔒 Password Reset</h1>\r\n            </div>\r\n            <div class=\'content\'>\r\n                <p>Hello <strong>stu000002</strong>,</p>\r\n                <p>Your password has been reset by an administrator. Please use the new password below to log in to your account:</p>\r\n                \r\n                <div class=\'credentials\'>\r\n                    <p><strong>Username:</strong> stu000002</p>\r\n                    <div class=\'password-box\'>\r\n                        <p style=\'margin: 0 0 10px 0; color: #856404;\'><strong>Your New Password:</strong></p>\r\n                        <div class=\'password-text\'>c9fac14f8962</div>\r\n                    </div>\r\n                    <p style=\'margin-top: 15px;\'><strong>Login URL:</strong> <a href=\'http://localhost/schoolerp/\'>http://localhost/schoolerp/</a></p>\r\n                </div>\r\n                \r\n                <div class=\'warning\'>\r\n                    <p style=\'margin: 0;\'><strong>⚠️ Security Notice:</strong></p>\r\n                    <p style=\'margin: 10px 0 0 0;\'>For your security, please change this password immediately after logging in. Do not share this password with anyone.</p>\r\n                </div>\r\n                \r\n                <p style=\'text-align: center;\'>\r\n                    <a href=\'http://localhost/schoolerp/\' class=\'button\'>Login to Your Account</a>\r\n                </p>\r\n                \r\n                <p>If you did not request this password reset, please contact us immediately at info@uukowtech.com</p>\r\n                <p>Best regards,<br><strong>School ERP System Team</strong></p>\r\n            </div>\r\n            <div class=\'footer\'>\r\n                <p>&copy; 2025 School ERP System. All rights reserved.</p>\r\n                <p>This is an automated email. Please do not reply to this message.</p>\r\n            </div>\r\n        </div>\r\n    </body>\r\n    </html>\r\n    ', 'Sent', NULL, 1, '2025-12-03 07:53:27'),
(6, 'Email', 'musalsalo23@gmail.com', 'Password Reset - School ERP System', '\r\n    <html>\r\n    <head>\r\n        <style>\r\n            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }\r\n            .container { max-width: 600px; margin: 0 auto; padding: 20px; }\r\n            .header { background: linear-gradient(135deg, #fa5c7c 0%, #ff6b9d 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }\r\n            .content { background: #f8f9fa; padding: 30px; }\r\n            .credentials { background: white; padding: 25px; border-left: 4px solid #fa5c7c; margin: 20px 0; border-radius: 4px; }\r\n            .password-box { background: #fff3cd; border: 2px dashed #ffc107; padding: 15px; margin: 15px 0; text-align: center; border-radius: 4px; }\r\n            .password-text { font-size: 24px; font-weight: bold; color: #856404; letter-spacing: 2px; font-family: \'Courier New\', monospace; }\r\n            .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; }\r\n            .button { display: inline-block; padding: 12px 30px; background: #fa5c7c; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }\r\n            .footer { text-align: center; padding: 20px; color: #999; font-size: 12px; }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\'container\'>\r\n            <div class=\'header\'>\r\n                <h1>🔒 Password Reset</h1>\r\n            </div>\r\n            <div class=\'content\'>\r\n                <p>Hello <strong>stu000001</strong>,</p>\r\n                <p>Your password has been reset by an administrator. Please use the new password below to log in to your account:</p>\r\n                \r\n                <div class=\'credentials\'>\r\n                    <p><strong>Username:</strong> stu000001</p>\r\n                    <div class=\'password-box\'>\r\n                        <p style=\'margin: 0 0 10px 0; color: #856404;\'><strong>Your New Password:</strong></p>\r\n                        <div class=\'password-text\'>0dfe6e7885eb</div>\r\n                    </div>\r\n                    <p style=\'margin-top: 15px;\'><strong>Login URL:</strong> <a href=\'http://localhost/schoolerp/\'>http://localhost/schoolerp/</a></p>\r\n                </div>\r\n                \r\n                <div class=\'warning\'>\r\n                    <p style=\'margin: 0;\'><strong>⚠️ Security Notice:</strong></p>\r\n                    <p style=\'margin: 10px 0 0 0;\'>For your security, please change this password immediately after logging in. Do not share this password with anyone.</p>\r\n                </div>\r\n                \r\n                <p style=\'text-align: center;\'>\r\n                    <a href=\'http://localhost/schoolerp/\' class=\'button\'>Login to Your Account</a>\r\n                </p>\r\n                \r\n                <p>If you did not request this password reset, please contact us immediately at info@uukowtech.com</p>\r\n                <p>Best regards,<br><strong>School ERP System Team</strong></p>\r\n            </div>\r\n            <div class=\'footer\'>\r\n                <p>&copy; 2025 School ERP System. All rights reserved.</p>\r\n                <p>This is an automated email. Please do not reply to this message.</p>\r\n            </div>\r\n        </div>\r\n    </body>\r\n    </html>\r\n    ', 'Sent', NULL, 1, '2025-12-13 13:01:42'),
(7, 'SMS', '613976162', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(8, 'SMS', '+252650615724', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(9, 'SMS', '+252668227872', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(10, 'SMS', '+252614841019', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(11, 'SMS', '+252905914898', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(12, 'SMS', '+252635133531', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(13, 'SMS', '+252914956053', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(14, 'SMS', '+252901152383', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(15, 'SMS', '+252615680391', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(16, 'SMS', '+252657467244', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(17, 'SMS', '+252661320883', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(18, 'SMS', '+252624803402', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(19, 'SMS', '+252654736287', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(20, 'SMS', '+252909418587', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(21, 'SMS', '+252638662702', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(22, 'SMS', '+252614916676', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(23, 'SMS', '+252632145293', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(24, 'SMS', '+252625176617', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(25, 'SMS', '+252904001506', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(26, 'SMS', '+252657910856', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(27, 'SMS', '+252668163858', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(28, 'SMS', '+252914709864', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(29, 'SMS', '+252612197583', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(30, 'SMS', '+252656173481', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(31, 'SMS', '+252634250394', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(32, 'SMS', '+252656113190', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(33, 'SMS', '+252908005951', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(34, 'SMS', '+252629049513', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(35, 'SMS', '+252666437265', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(36, 'SMS', '+252616322858', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(37, 'SMS', '+252901799447', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(38, 'SMS', '+252917098809', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(39, 'SMS', '+252659791975', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(40, 'SMS', '+252612865055', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(41, 'SMS', '+252908244853', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(42, 'SMS', '+252629362506', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(43, 'SMS', '+252618797973', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(44, 'SMS', '+252618565238', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(45, 'SMS', '+252631883934', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(46, 'SMS', '+252906940106', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(47, 'SMS', '+252616784005', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(48, 'SMS', '+252631362531', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(49, 'SMS', '+252623129212', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(50, 'SMS', '+252655569810', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(51, 'SMS', '+252616911808', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(52, 'SMS', '+252626135408', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(53, 'SMS', '+252660486056', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(54, 'SMS', '+252659627859', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(55, 'SMS', '+252615936093', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(56, 'SMS', '+252612116751', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(57, 'SMS', '+252629990789', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(58, 'SMS', '+252639775487', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(59, 'SMS', '+252612142989', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(60, 'SMS', '+252919991206', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(61, 'SMS', '+252902956690', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(62, 'SMS', '+252911472245', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(63, 'SMS', '+252910234679', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(64, 'SMS', '+252658279997', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(65, 'SMS', '+252614672156', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(66, 'SMS', '+252908904576', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(67, 'SMS', '+252668854348', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(68, 'SMS', '+252614242930', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(69, 'SMS', '+252614027308', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(70, 'SMS', '+252919128015', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(71, 'SMS', '+252617725265', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(72, 'SMS', '+252668482537', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(73, 'SMS', '+252655885572', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(74, 'SMS', '+252910335034', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(75, 'SMS', '+252668364825', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(76, 'SMS', '+252611724134', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(77, 'SMS', '+252916319576', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(78, 'SMS', '+252624087463', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(79, 'SMS', '+252661775145', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(80, 'SMS', '+252626813730', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(81, 'SMS', '+252633203661', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(82, 'SMS', '+252905515466', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(83, 'SMS', '+252902683487', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(84, 'SMS', '+252662418482', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(85, 'SMS', '+252654233030', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(86, 'SMS', '+252623590399', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(87, 'SMS', '+252912039263', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(88, 'SMS', '+252635776698', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(89, 'SMS', '+252658768915', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(90, 'SMS', '+252636455291', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(91, 'SMS', '+252632101020', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(92, 'SMS', '+252906626921', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(93, 'SMS', '+252664694037', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(94, 'SMS', '+252907487494', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(95, 'SMS', '+252630887369', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(96, 'SMS', '+252905489752', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(97, 'SMS', '+252639347655', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(98, 'SMS', '+252621707656', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(99, 'SMS', '+252638213983', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(100, 'SMS', '+252917167976', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(101, 'SMS', '+252651577088', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(102, 'SMS', '+252622279890', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(103, 'SMS', '+252622376274', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(104, 'SMS', '+252611918675', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(105, 'SMS', '+252902563506', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(106, 'SMS', '+252912109157', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(107, 'SMS', '+252626917473', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(108, 'SMS', '+252668755840', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(109, 'SMS', '+252650290628', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(110, 'SMS', '+252612830035', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(111, 'SMS', '+252628959123', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(112, 'SMS', '+252633089591', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(113, 'SMS', '+252666276539', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(114, 'SMS', '+252636195874', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(115, 'SMS', '+252617040164', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(116, 'SMS', '+252665562884', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(117, 'SMS', '+252658947464', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(118, 'SMS', '+252919261743', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(119, 'SMS', '+252906706663', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(120, 'SMS', '+252654771973', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(121, 'SMS', '+252917477180', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(122, 'SMS', '+252906104209', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(123, 'SMS', '+252611682239', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(124, 'SMS', '+252901578942', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(125, 'SMS', '+252659357986', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(126, 'SMS', '+252626861119', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(127, 'SMS', '+252626669685', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(128, 'SMS', '+252666445288', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(129, 'SMS', '+252906741522', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(130, 'SMS', '+252619949078', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(131, 'SMS', '+252628742128', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(132, 'SMS', '+252916452268', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(133, 'SMS', '+252900343403', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(134, 'SMS', '+252905036465', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(135, 'SMS', '+252637577869', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(136, 'SMS', '+252620026167', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(137, 'SMS', '+252658408490', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(138, 'SMS', '+252911169091', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(139, 'SMS', '+252666875722', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(140, 'SMS', '+252615925231', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(141, 'SMS', '+252914295978', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(142, 'SMS', '+252654539038', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(143, 'SMS', '+252637416960', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(144, 'SMS', '+252662916429', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(145, 'SMS', '+252667454897', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(146, 'SMS', '+252661080670', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(147, 'SMS', '+252633070882', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(148, 'SMS', '+252635387952', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(149, 'SMS', '+252635167162', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(150, 'SMS', '+252621162724', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(151, 'SMS', '+252907664261', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(152, 'SMS', '+252918294810', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(153, 'SMS', '+252636205066', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(154, 'SMS', '+252633750395', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(155, 'SMS', '+252636941330', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(156, 'SMS', '+252634718888', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(157, 'SMS', '+252669246193', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(158, 'SMS', '+252909938173', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(159, 'SMS', '+252665246061', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(160, 'SMS', '+252908855084', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(161, 'SMS', '+252627248541', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(162, 'SMS', '+252611641824', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(163, 'SMS', '+252660306930', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(164, 'SMS', '+252615034208', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(165, 'SMS', '+252619593495', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(166, 'SMS', '+252623950198', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:10:59'),
(167, 'SMS', '+252634129595', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(168, 'SMS', '+252913413064', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(169, 'SMS', '+252909037874', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(170, 'SMS', '+252654142936', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(171, 'SMS', '+252615582998', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(172, 'SMS', '+252904051881', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(173, 'SMS', '+252619061975', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(174, 'SMS', '+252639525685', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(175, 'SMS', '+252650399679', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(176, 'SMS', '+252903424183', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(177, 'SMS', '+252665066448', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(178, 'SMS', '+252633182747', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(179, 'SMS', '+252903908702', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(180, 'SMS', '+252656591937', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(181, 'SMS', '+252916212194', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(182, 'SMS', '+252627084769', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(183, 'SMS', '+252658019379', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(184, 'SMS', '+252656868544', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(185, 'SMS', '+252618185278', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(186, 'SMS', '+252666351200', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(187, 'SMS', '+252653536006', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(188, 'SMS', '+252664505813', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(189, 'SMS', '+252632337357', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(190, 'SMS', '+252653204470', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(191, 'SMS', '+252661143088', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(192, 'SMS', '+252909666502', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(193, 'SMS', '+252910675307', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(194, 'SMS', '+252623575840', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(195, 'SMS', '+252911729141', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(196, 'SMS', '+252659104589', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(197, 'SMS', '+252903840648', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(198, 'SMS', '+252665917584', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(199, 'SMS', '+252665937272', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(200, 'SMS', '+252652392656', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(201, 'SMS', '+252915103309', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(202, 'SMS', '+252618693236', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(203, 'SMS', '+252658977716', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(204, 'SMS', '+252668565042', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(205, 'SMS', '+252624038325', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(206, 'SMS', '+252617372607', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(207, 'SMS', '+252919216286', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(208, 'SMS', '+252907262976', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(209, 'SMS', '+252615584383', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(210, 'SMS', '+252651396968', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(211, 'SMS', '+252634403477', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(212, 'SMS', '+252618578957', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(213, 'SMS', '+252906838540', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(214, 'SMS', '+252662988358', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(215, 'SMS', '+252654839462', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(216, 'SMS', '+252659785533', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(217, 'SMS', '+252613677447', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(218, 'SMS', '+252614365700', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(219, 'SMS', '+252617949552', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(220, 'SMS', '+252910118245', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(221, 'SMS', '+252610092679', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(222, 'SMS', '+252632059910', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(223, 'SMS', '+252913276623', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(224, 'SMS', '+252665918346', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(225, 'SMS', '+252639414847', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(226, 'SMS', '+252625672889', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(227, 'SMS', '+252909378524', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(228, 'SMS', '+252913674454', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(229, 'SMS', '+252626023687', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(230, 'SMS', '+252903163126', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(231, 'SMS', '+252620536624', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(232, 'SMS', '+252919911090', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(233, 'SMS', '+252653982356', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(234, 'SMS', '+252909617734', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(235, 'SMS', '+252668620603', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(236, 'SMS', '+252633577873', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(237, 'SMS', '+252666469371', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(238, 'SMS', '+252626995936', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(239, 'SMS', '+252610883358', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(240, 'SMS', '+252638219923', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(241, 'SMS', '+252632232144', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(242, 'SMS', '+252903942733', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(243, 'SMS', '+252612544409', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(244, 'SMS', '+252614587525', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(245, 'SMS', '+252615549908', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(246, 'SMS', '+252903328014', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(247, 'SMS', '+252903889777', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(248, 'SMS', '+252630217393', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(249, 'SMS', '+252662900849', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(250, 'SMS', '+252636354198', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(251, 'SMS', '+252626432492', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(252, 'SMS', '+252913647342', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(253, 'SMS', '+252668244565', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(254, 'SMS', '+252615123672', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(255, 'SMS', '+252625627091', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(256, 'SMS', '+252627496671', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(257, 'SMS', '+252631785006', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(258, 'SMS', '+252639686134', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(259, 'SMS', '+252663430004', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(260, 'SMS', '+252653045954', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(261, 'SMS', '+252622486221', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(262, 'SMS', '+252624311620', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(263, 'SMS', '+252910923753', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(264, 'SMS', '+252622243321', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(265, 'SMS', '+252907145906', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(266, 'SMS', '+252631874967', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(267, 'SMS', '+252619757298', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(268, 'SMS', '+252614211393', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(269, 'SMS', '+252655740755', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(270, 'SMS', '+252904681729', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(271, 'SMS', '+252913061753', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(272, 'SMS', '+252635842833', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(273, 'SMS', '+252914831502', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(274, 'SMS', '+252668077782', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(275, 'SMS', '+252630233100', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(276, 'SMS', '+252617008162', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(277, 'SMS', '+252669124099', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(278, 'SMS', '+252624515960', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(279, 'SMS', '+252659675843', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(280, 'SMS', '+252665638572', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(281, 'SMS', '+252660987510', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(282, 'SMS', '+252915256062', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(283, 'SMS', '+252917837328', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(284, 'SMS', '+252666717649', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(285, 'SMS', '+252630489373', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(286, 'SMS', '+252633444607', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(287, 'SMS', '+252916737763', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(288, 'SMS', '+252667049675', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(289, 'SMS', '+252614704013', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(290, 'SMS', '+252667035136', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(291, 'SMS', '+252903035940', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(292, 'SMS', '+252627651916', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(293, 'SMS', '+252653407673', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(294, 'SMS', '+252617968934', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(295, 'SMS', '+252908919020', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(296, 'SMS', '+252632263309', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(297, 'SMS', '+252906481810', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(298, 'SMS', '+252624850741', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(299, 'SMS', '+252664892704', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(300, 'SMS', '+252916226191', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(301, 'SMS', '+252668711300', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(302, 'SMS', '+252913366212', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(303, 'SMS', '+252902095879', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(304, 'SMS', '+252651102668', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(305, 'SMS', '+252665924907', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(306, 'SMS', '+252633409110', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(307, 'SMS', '+252632503019', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(308, 'SMS', '+252628560412', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(309, 'SMS', '+252664567564', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(310, 'SMS', '+252915321874', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(311, 'SMS', '+252610901110', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(312, 'SMS', '+252636773002', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(313, 'SMS', '+252655555408', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(314, 'SMS', '+252915624821', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(315, 'SMS', '+252621477988', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(316, 'SMS', '+252662281551', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(317, 'SMS', '+252635695175', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(318, 'SMS', '+252658300288', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(319, 'SMS', '+252652015734', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(320, 'SMS', '+252912381894', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(321, 'SMS', '+252908289213', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(322, 'SMS', '+252668927643', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(323, 'SMS', '+252612641835', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(324, 'SMS', '+252618681392', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(325, 'SMS', '+252669308047', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(326, 'SMS', '+252911809281', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(327, 'SMS', '+252651073068', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(328, 'SMS', '+252611120594', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(329, 'SMS', '+252616910279', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(330, 'SMS', '+252630088865', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(331, 'SMS', '+252912078578', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(332, 'SMS', '+252666762998', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(333, 'SMS', '+252912857168', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(334, 'SMS', '+252914225620', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(335, 'SMS', '+252909213942', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(336, 'SMS', '+252917516066', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(337, 'SMS', '+252660500872', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(338, 'SMS', '+252617997534', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(339, 'SMS', '+252626946393', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(340, 'SMS', '+252907555885', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(341, 'SMS', '+252631239453', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(342, 'SMS', '+252659597369', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(343, 'SMS', '+252911450493', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(344, 'SMS', '+252625559326', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(345, 'SMS', '+252616334550', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(346, 'SMS', '+252905121488', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(347, 'SMS', '+252635979310', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(348, 'SMS', '+252624817546', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(349, 'SMS', '+252900710085', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(350, 'SMS', '+252620888788', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(351, 'SMS', '+252667123349', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(352, 'SMS', '+252909926285', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(353, 'SMS', '+252909279664', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(354, 'SMS', '+252637272361', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(355, 'SMS', '+252628372932', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(356, 'SMS', '+252651333588', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(357, 'SMS', '+252905711658', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(358, 'SMS', '+252662578508', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(359, 'SMS', '+252669260461', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(360, 'SMS', '+252907959221', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(361, 'SMS', '+252611755293', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(362, 'SMS', '+252655759757', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(363, 'SMS', '+252627450753', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(364, 'SMS', '+252611108812', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(365, 'SMS', '+252903733760', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(366, 'SMS', '+252612494976', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(367, 'SMS', '+252900549271', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(368, 'SMS', '+252612527344', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(369, 'SMS', '+252666282652', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(370, 'SMS', '+252913354575', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(371, 'SMS', '+252664330496', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(372, 'SMS', '+252903848170', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(373, 'SMS', '+252910293389', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(374, 'SMS', '+252623672407', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(375, 'SMS', '+252623004633', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(376, 'SMS', '+252620453582', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(377, 'SMS', '+252901020917', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(378, 'SMS', '+252639031071', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(379, 'SMS', '+252625236571', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(380, 'SMS', '+252907749437', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(381, 'SMS', '+252635088510', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(382, 'SMS', '+252619208264', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(383, 'SMS', '+252611765997', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(384, 'SMS', '+252622202235', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(385, 'SMS', '+252904606332', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(386, 'SMS', '+252906864102', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(387, 'SMS', '+252651780208', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(388, 'SMS', '+252669757964', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(389, 'SMS', '+252918323323', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(390, 'SMS', '+252651399092', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(391, 'SMS', '+252919544965', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(392, 'SMS', '+252910300626', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(393, 'SMS', '+252636608305', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(394, 'SMS', '+252919725865', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(395, 'SMS', '+252667513067', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(396, 'SMS', '+252918746730', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(397, 'SMS', '+252660542226', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(398, 'SMS', '+252900054183', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(399, 'SMS', '+252668423496', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(400, 'SMS', '+252908630453', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(401, 'SMS', '+252637964608', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(402, 'SMS', '+252627744817', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(403, 'SMS', '+252915551352', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(404, 'SMS', '+252901463719', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(405, 'SMS', '+252652144357', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(406, 'SMS', '+252638504065', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(407, 'SMS', '+252906105399', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(408, 'SMS', '+252910450012', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(409, 'SMS', '+252632601308', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(410, 'SMS', '+252901834631', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(411, 'SMS', '+252912228113', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(412, 'SMS', '+252654276019', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(413, 'SMS', '+252623595782', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(414, 'SMS', '+252612846884', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(415, 'SMS', '+252615820698', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(416, 'SMS', '+252902140843', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(417, 'SMS', '+252918798011', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(418, 'SMS', '+252614502513', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(419, 'SMS', '+252910048570', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(420, 'SMS', '+252613337995', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(421, 'SMS', '+252624614430', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(422, 'SMS', '+252611885880', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(423, 'SMS', '+252903885854', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(424, 'SMS', '+252618915413', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(425, 'SMS', '+252918448533', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(426, 'SMS', '+252668678489', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(427, 'SMS', '+252906705766', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(428, 'SMS', '+252657132564', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:00'),
(429, 'SMS', '+252659261078', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(430, 'SMS', '+252651846540', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(431, 'SMS', '+252611923634', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(432, 'SMS', '+252667161219', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(433, 'SMS', '+252655289761', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01');
INSERT INTO `communication_logs` (`id`, `communication_type`, `recipient`, `subject`, `message`, `status`, `error_message`, `sent_by`, `sent_at`) VALUES
(434, 'SMS', '+252614380282', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(435, 'SMS', '+252668023788', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(436, 'SMS', '+252668109632', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(437, 'SMS', '+252665978086', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(438, 'SMS', '+252903410292', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(439, 'SMS', '+252652156294', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(440, 'SMS', '+252615659543', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(441, 'SMS', '+252618365009', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(442, 'SMS', '+252666864154', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(443, 'SMS', '+252629651618', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(444, 'SMS', '+252916327618', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(445, 'SMS', '+252909321189', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(446, 'SMS', '+252911012375', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(447, 'SMS', '+252913686046', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(448, 'SMS', '+252650176058', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(449, 'SMS', '+252663617741', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(450, 'SMS', '+252625211787', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(451, 'SMS', '+252906946797', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(452, 'SMS', '+252656321299', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(453, 'SMS', '+252662154267', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(454, 'SMS', '+252613987335', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(455, 'SMS', '+252629570788', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(456, 'SMS', '+252912263347', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(457, 'SMS', '+252659740207', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(458, 'SMS', '+252657596791', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(459, 'SMS', '+252635385413', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(460, 'SMS', '+252631590605', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(461, 'SMS', '+252905104792', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(462, 'SMS', '+252657264282', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(463, 'SMS', '+252624185885', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(464, 'SMS', '+252666441058', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(465, 'SMS', '+252903106124', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(466, 'SMS', '+252620232896', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(467, 'SMS', '+252907553580', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(468, 'SMS', '+252906048142', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(469, 'SMS', '+252905765442', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(470, 'SMS', '+252654439317', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(471, 'SMS', '+252652641192', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(472, 'SMS', '+252903951751', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(473, 'SMS', '+252666054114', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(474, 'SMS', '+252612812608', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(475, 'SMS', '+252638752393', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(476, 'SMS', '+252622147475', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(477, 'SMS', '+252651390017', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(478, 'SMS', '+252667656195', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(479, 'SMS', '+252906096487', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(480, 'SMS', '+252636289904', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(481, 'SMS', '+252664076405', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(482, 'SMS', '+252658403372', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(483, 'SMS', '+252614673760', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(484, 'SMS', '+252617984407', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(485, 'SMS', '+252909173979', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(486, 'SMS', '+252619270149', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(487, 'SMS', '+252614478022', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(488, 'SMS', '+252615761561', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(489, 'SMS', '+252916441407', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(490, 'SMS', '+252631543077', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(491, 'SMS', '+252915798234', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(492, 'SMS', '+252619007594', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(493, 'SMS', '+252635660789', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(494, 'SMS', '+252624438282', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(495, 'SMS', '+252650592583', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(496, 'SMS', '+252630806208', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(497, 'SMS', '+252634340874', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(498, 'SMS', '+252654057284', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(499, 'SMS', '+252916436656', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(500, 'SMS', '+252619141341', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(501, 'SMS', '+252630708406', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(502, 'SMS', '+252617386170', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(503, 'SMS', '+252915481686', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(504, 'SMS', '+252913044385', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(505, 'SMS', '+252633399099', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(506, 'SMS', '+252611456989', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(507, 'SMS', '+252658285871', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(508, 'SMS', '+252623008602', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(509, 'SMS', '+252662611654', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(510, 'SMS', '+252900943593', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(511, 'SMS', '+252636584043', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(512, 'SMS', '+252637654900', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(513, 'SMS', '+252660211323', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(514, 'SMS', '+252626290465', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(515, 'SMS', '+252664798056', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(516, 'SMS', '+252658574493', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(517, 'SMS', '+252610040285', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(518, 'SMS', '+252658334410', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(519, 'SMS', '+252911718860', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(520, 'SMS', '+252657882730', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(521, 'SMS', '+252617444344', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(522, 'SMS', '+252661256797', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(523, 'SMS', '+252914705540', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(524, 'SMS', '+252913975610', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(525, 'SMS', '+252613033609', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(526, 'SMS', '+252622283481', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(527, 'SMS', '+252915404815', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(528, 'SMS', '+252650093346', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(529, 'SMS', '+252663532752', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(530, 'SMS', '+252629080984', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(531, 'SMS', '+252657520585', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(532, 'SMS', '+252634518931', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(533, 'SMS', '+252635861232', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(534, 'SMS', '+252661648989', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(535, 'SMS', '+252910675874', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(536, 'SMS', '+252910460765', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(537, 'SMS', '+252621707294', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(538, 'SMS', '+252614765518', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(539, 'SMS', '+252637415621', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(540, 'SMS', '+252629506534', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(541, 'SMS', '+252622333500', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(542, 'SMS', '+252623293818', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(543, 'SMS', '+252665920798', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(544, 'SMS', '+252659881131', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(545, 'SMS', '+252612890974', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(546, 'SMS', '+252919069321', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(547, 'SMS', '+252662869936', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(548, 'SMS', '+252901844263', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(549, 'SMS', '+252621070696', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(550, 'SMS', '+252664962905', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(551, 'SMS', '+252915350928', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(552, 'SMS', '+252908006225', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(553, 'SMS', '+252624253024', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(554, 'SMS', '+252656765547', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(555, 'SMS', '+252911537144', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(556, 'SMS', '+252914542340', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(557, 'SMS', '+252635040588', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(558, 'SMS', '+252613514336', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(559, 'SMS', '+252907976662', NULL, 'bixi', 'Sent', NULL, 1, '2025-12-19 14:11:01'),
(560, 'Email', 'abdihagad@gmail.com', 'Test', '\r\n    <!DOCTYPE html>\r\n    <html lang=\"en\">\r\n    <head>\r\n        <meta charset=\"UTF-8\">\r\n        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n        <title>Test</title>\r\n        <style>\r\n            * {\r\n                margin: 0;\r\n                padding: 0;\r\n                box-sizing: border-box;\r\n            }\r\n            body {\r\n                font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif;\r\n                line-height: 1.6;\r\n                color: #333333;\r\n                background-color: #f4f4f4;\r\n                padding: 20px;\r\n            }\r\n            .email-wrapper {\r\n                max-width: 600px;\r\n                margin: 0 auto;\r\n                background-color: #ffffff;\r\n                border-radius: 10px;\r\n                overflow: hidden;\r\n                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);\r\n            }\r\n            .email-header {\r\n                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\r\n                color: #ffffff;\r\n                padding: 40px 30px;\r\n                text-align: center;\r\n            }\r\n            .email-header h1 {\r\n                font-size: 28px;\r\n                font-weight: 600;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-header .logo {\r\n                font-size: 24px;\r\n                font-weight: bold;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-body {\r\n                padding: 40px 30px;\r\n                background-color: #ffffff;\r\n            }\r\n            .email-greeting {\r\n                font-size: 18px;\r\n                color: #333333;\r\n                margin-bottom: 20px;\r\n            }\r\n            .email-content {\r\n                font-size: 16px;\r\n                color: #555555;\r\n                line-height: 1.8;\r\n                margin-bottom: 30px;\r\n            }\r\n            .email-content p {\r\n                margin-bottom: 15px;\r\n            }\r\n            .email-content a {\r\n                color: #667eea;\r\n                text-decoration: none;\r\n            }\r\n            .email-content a:hover {\r\n                text-decoration: underline;\r\n            }\r\n            .email-divider {\r\n                height: 2px;\r\n                background: linear-gradient(90deg, transparent, #e0e0e0, transparent);\r\n                margin: 30px 0;\r\n            }\r\n            .email-footer {\r\n                background-color: #f8f9fa;\r\n                padding: 30px;\r\n                text-align: center;\r\n                border-top: 1px solid #e0e0e0;\r\n            }\r\n            .email-footer p {\r\n                font-size: 14px;\r\n                color: #666666;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-footer .school-name {\r\n                font-weight: 600;\r\n                color: #333333;\r\n                font-size: 16px;\r\n                margin-bottom: 5px;\r\n            }\r\n            .email-footer .contact-info {\r\n                font-size: 13px;\r\n                color: #888888;\r\n            }\r\n            .email-footer a {\r\n                color: #667eea;\r\n                text-decoration: none;\r\n            }\r\n            .button {\r\n                display: inline-block;\r\n                padding: 12px 30px;\r\n                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\r\n                color: #ffffff !important;\r\n                text-decoration: none;\r\n                border-radius: 5px;\r\n                font-weight: 600;\r\n                margin: 20px 0;\r\n                text-align: center;\r\n            }\r\n            .button:hover {\r\n                opacity: 0.9;\r\n                text-decoration: none;\r\n            }\r\n            @media only screen and (max-width: 600px) {\r\n                .email-wrapper {\r\n                    width: 100% !important;\r\n                }\r\n                .email-header, .email-body, .email-footer {\r\n                    padding: 20px !important;\r\n                }\r\n                .email-header h1 {\r\n                    font-size: 24px !important;\r\n                }\r\n            }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\"email-wrapper\">\r\n            <div class=\"email-header\">\r\n                <div class=\"logo\">Awdheegle</div>\r\n                <h1>Test</h1>\r\n            </div>\r\n            \r\n            <div class=\"email-body\">\r\n                <div class=\"email-greeting\">\r\n                    Hello Hagad Xiis,\r\n                </div>\r\n                \r\n                <div class=\"email-content\">\r\n                    Test From TacliinHub System\r\n                </div>\r\n            </div>\r\n            \r\n            <div class=\"email-divider\"></div>\r\n            \r\n            <div class=\"email-footer\">\r\n                <p class=\"school-name\">Awdheegle</p>\r\n                <p class=\"contact-info\">\r\n                    Email: <a href=\"mailto:abdihagad@gmail.com\">abdihagad@gmail.com</a>\r\n                </p>\r\n                <p class=\"contact-info\" style=\"margin-top: 20px; font-size: 12px; color: #999999;\">\r\n                    &copy; 2025 Awdheegle. All rights reserved.<br>\r\n                    This is an automated email. Please do not reply directly to this message.\r\n                </p>\r\n            </div>\r\n        </div>\r\n    </body>\r\n    </html>', 'Sent', NULL, 1, '2025-12-19 14:15:46'),
(561, 'Email', 'sarah.johnson@school.edu', 'Test', '\r\n    <!DOCTYPE html>\r\n    <html lang=\"en\">\r\n    <head>\r\n        <meta charset=\"UTF-8\">\r\n        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n        <title>Test</title>\r\n        <style>\r\n            * {\r\n                margin: 0;\r\n                padding: 0;\r\n                box-sizing: border-box;\r\n            }\r\n            body {\r\n                font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif;\r\n                line-height: 1.6;\r\n                color: #333333;\r\n                background-color: #f4f4f4;\r\n                padding: 20px;\r\n            }\r\n            .email-wrapper {\r\n                max-width: 600px;\r\n                margin: 0 auto;\r\n                background-color: #ffffff;\r\n                border-radius: 10px;\r\n                overflow: hidden;\r\n                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);\r\n            }\r\n            .email-header {\r\n                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\r\n                color: #ffffff;\r\n                padding: 40px 30px;\r\n                text-align: center;\r\n            }\r\n            .email-header h1 {\r\n                font-size: 28px;\r\n                font-weight: 600;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-header .logo {\r\n                font-size: 24px;\r\n                font-weight: bold;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-body {\r\n                padding: 40px 30px;\r\n                background-color: #ffffff;\r\n            }\r\n            .email-greeting {\r\n                font-size: 18px;\r\n                color: #333333;\r\n                margin-bottom: 20px;\r\n            }\r\n            .email-content {\r\n                font-size: 16px;\r\n                color: #555555;\r\n                line-height: 1.8;\r\n                margin-bottom: 30px;\r\n            }\r\n            .email-content p {\r\n                margin-bottom: 15px;\r\n            }\r\n            .email-content a {\r\n                color: #667eea;\r\n                text-decoration: none;\r\n            }\r\n            .email-content a:hover {\r\n                text-decoration: underline;\r\n            }\r\n            .email-divider {\r\n                height: 2px;\r\n                background: linear-gradient(90deg, transparent, #e0e0e0, transparent);\r\n                margin: 30px 0;\r\n            }\r\n            .email-footer {\r\n                background-color: #f8f9fa;\r\n                padding: 30px;\r\n                text-align: center;\r\n                border-top: 1px solid #e0e0e0;\r\n            }\r\n            .email-footer p {\r\n                font-size: 14px;\r\n                color: #666666;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-footer .school-name {\r\n                font-weight: 600;\r\n                color: #333333;\r\n                font-size: 16px;\r\n                margin-bottom: 5px;\r\n            }\r\n            .email-footer .contact-info {\r\n                font-size: 13px;\r\n                color: #888888;\r\n            }\r\n            .email-footer a {\r\n                color: #667eea;\r\n                text-decoration: none;\r\n            }\r\n            .button {\r\n                display: inline-block;\r\n                padding: 12px 30px;\r\n                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\r\n                color: #ffffff !important;\r\n                text-decoration: none;\r\n                border-radius: 5px;\r\n                font-weight: 600;\r\n                margin: 20px 0;\r\n                text-align: center;\r\n            }\r\n            .button:hover {\r\n                opacity: 0.9;\r\n                text-decoration: none;\r\n            }\r\n            @media only screen and (max-width: 600px) {\r\n                .email-wrapper {\r\n                    width: 100% !important;\r\n                }\r\n                .email-header, .email-body, .email-footer {\r\n                    padding: 20px !important;\r\n                }\r\n                .email-header h1 {\r\n                    font-size: 24px !important;\r\n                }\r\n            }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\"email-wrapper\">\r\n            <div class=\"email-header\">\r\n                <div class=\"logo\">Awdheegle</div>\r\n                <h1>Test</h1>\r\n            </div>\r\n            \r\n            <div class=\"email-body\">\r\n                <div class=\"email-greeting\">\r\n                    Hello Sarah Johnson,\r\n                </div>\r\n                \r\n                <div class=\"email-content\">\r\n                    Test From TacliinHub System\r\n                </div>\r\n            </div>\r\n            \r\n            <div class=\"email-divider\"></div>\r\n            \r\n            <div class=\"email-footer\">\r\n                <p class=\"school-name\">Awdheegle</p>\r\n                <p class=\"contact-info\">\r\n                    Email: <a href=\"mailto:abdihagad@gmail.com\">abdihagad@gmail.com</a>\r\n                </p>\r\n                <p class=\"contact-info\" style=\"margin-top: 20px; font-size: 12px; color: #999999;\">\r\n                    &copy; 2025 Awdheegle. All rights reserved.<br>\r\n                    This is an automated email. Please do not reply directly to this message.\r\n                </p>\r\n            </div>\r\n        </div>\r\n    </body>\r\n    </html>', 'Sent', NULL, 1, '2025-12-19 14:15:50'),
(562, 'Email', 'michael.chen@school.edu', 'Test', '\r\n    <!DOCTYPE html>\r\n    <html lang=\"en\">\r\n    <head>\r\n        <meta charset=\"UTF-8\">\r\n        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n        <title>Test</title>\r\n        <style>\r\n            * {\r\n                margin: 0;\r\n                padding: 0;\r\n                box-sizing: border-box;\r\n            }\r\n            body {\r\n                font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif;\r\n                line-height: 1.6;\r\n                color: #333333;\r\n                background-color: #f4f4f4;\r\n                padding: 20px;\r\n            }\r\n            .email-wrapper {\r\n                max-width: 600px;\r\n                margin: 0 auto;\r\n                background-color: #ffffff;\r\n                border-radius: 10px;\r\n                overflow: hidden;\r\n                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);\r\n            }\r\n            .email-header {\r\n                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\r\n                color: #ffffff;\r\n                padding: 40px 30px;\r\n                text-align: center;\r\n            }\r\n            .email-header h1 {\r\n                font-size: 28px;\r\n                font-weight: 600;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-header .logo {\r\n                font-size: 24px;\r\n                font-weight: bold;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-body {\r\n                padding: 40px 30px;\r\n                background-color: #ffffff;\r\n            }\r\n            .email-greeting {\r\n                font-size: 18px;\r\n                color: #333333;\r\n                margin-bottom: 20px;\r\n            }\r\n            .email-content {\r\n                font-size: 16px;\r\n                color: #555555;\r\n                line-height: 1.8;\r\n                margin-bottom: 30px;\r\n            }\r\n            .email-content p {\r\n                margin-bottom: 15px;\r\n            }\r\n            .email-content a {\r\n                color: #667eea;\r\n                text-decoration: none;\r\n            }\r\n            .email-content a:hover {\r\n                text-decoration: underline;\r\n            }\r\n            .email-divider {\r\n                height: 2px;\r\n                background: linear-gradient(90deg, transparent, #e0e0e0, transparent);\r\n                margin: 30px 0;\r\n            }\r\n            .email-footer {\r\n                background-color: #f8f9fa;\r\n                padding: 30px;\r\n                text-align: center;\r\n                border-top: 1px solid #e0e0e0;\r\n            }\r\n            .email-footer p {\r\n                font-size: 14px;\r\n                color: #666666;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-footer .school-name {\r\n                font-weight: 600;\r\n                color: #333333;\r\n                font-size: 16px;\r\n                margin-bottom: 5px;\r\n            }\r\n            .email-footer .contact-info {\r\n                font-size: 13px;\r\n                color: #888888;\r\n            }\r\n            .email-footer a {\r\n                color: #667eea;\r\n                text-decoration: none;\r\n            }\r\n            .button {\r\n                display: inline-block;\r\n                padding: 12px 30px;\r\n                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\r\n                color: #ffffff !important;\r\n                text-decoration: none;\r\n                border-radius: 5px;\r\n                font-weight: 600;\r\n                margin: 20px 0;\r\n                text-align: center;\r\n            }\r\n            .button:hover {\r\n                opacity: 0.9;\r\n                text-decoration: none;\r\n            }\r\n            @media only screen and (max-width: 600px) {\r\n                .email-wrapper {\r\n                    width: 100% !important;\r\n                }\r\n                .email-header, .email-body, .email-footer {\r\n                    padding: 20px !important;\r\n                }\r\n                .email-header h1 {\r\n                    font-size: 24px !important;\r\n                }\r\n            }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\"email-wrapper\">\r\n            <div class=\"email-header\">\r\n                <div class=\"logo\">Awdheegle</div>\r\n                <h1>Test</h1>\r\n            </div>\r\n            \r\n            <div class=\"email-body\">\r\n                <div class=\"email-greeting\">\r\n                    Hello Michael Chen,\r\n                </div>\r\n                \r\n                <div class=\"email-content\">\r\n                    Test From TacliinHub System\r\n                </div>\r\n            </div>\r\n            \r\n            <div class=\"email-divider\"></div>\r\n            \r\n            <div class=\"email-footer\">\r\n                <p class=\"school-name\">Awdheegle</p>\r\n                <p class=\"contact-info\">\r\n                    Email: <a href=\"mailto:abdihagad@gmail.com\">abdihagad@gmail.com</a>\r\n                </p>\r\n                <p class=\"contact-info\" style=\"margin-top: 20px; font-size: 12px; color: #999999;\">\r\n                    &copy; 2025 Awdheegle. All rights reserved.<br>\r\n                    This is an automated email. Please do not reply directly to this message.\r\n                </p>\r\n            </div>\r\n        </div>\r\n    </body>\r\n    </html>', 'Sent', NULL, 1, '2025-12-19 14:15:54'),
(563, 'Email', 'emily.rodriguez@school.edu', 'Test', '\r\n    <!DOCTYPE html>\r\n    <html lang=\"en\">\r\n    <head>\r\n        <meta charset=\"UTF-8\">\r\n        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n        <title>Test</title>\r\n        <style>\r\n            * {\r\n                margin: 0;\r\n                padding: 0;\r\n                box-sizing: border-box;\r\n            }\r\n            body {\r\n                font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif;\r\n                line-height: 1.6;\r\n                color: #333333;\r\n                background-color: #f4f4f4;\r\n                padding: 20px;\r\n            }\r\n            .email-wrapper {\r\n                max-width: 600px;\r\n                margin: 0 auto;\r\n                background-color: #ffffff;\r\n                border-radius: 10px;\r\n                overflow: hidden;\r\n                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);\r\n            }\r\n            .email-header {\r\n                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\r\n                color: #ffffff;\r\n                padding: 40px 30px;\r\n                text-align: center;\r\n            }\r\n            .email-header h1 {\r\n                font-size: 28px;\r\n                font-weight: 600;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-header .logo {\r\n                font-size: 24px;\r\n                font-weight: bold;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-body {\r\n                padding: 40px 30px;\r\n                background-color: #ffffff;\r\n            }\r\n            .email-greeting {\r\n                font-size: 18px;\r\n                color: #333333;\r\n                margin-bottom: 20px;\r\n            }\r\n            .email-content {\r\n                font-size: 16px;\r\n                color: #555555;\r\n                line-height: 1.8;\r\n                margin-bottom: 30px;\r\n            }\r\n            .email-content p {\r\n                margin-bottom: 15px;\r\n            }\r\n            .email-content a {\r\n                color: #667eea;\r\n                text-decoration: none;\r\n            }\r\n            .email-content a:hover {\r\n                text-decoration: underline;\r\n            }\r\n            .email-divider {\r\n                height: 2px;\r\n                background: linear-gradient(90deg, transparent, #e0e0e0, transparent);\r\n                margin: 30px 0;\r\n            }\r\n            .email-footer {\r\n                background-color: #f8f9fa;\r\n                padding: 30px;\r\n                text-align: center;\r\n                border-top: 1px solid #e0e0e0;\r\n            }\r\n            .email-footer p {\r\n                font-size: 14px;\r\n                color: #666666;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-footer .school-name {\r\n                font-weight: 600;\r\n                color: #333333;\r\n                font-size: 16px;\r\n                margin-bottom: 5px;\r\n            }\r\n            .email-footer .contact-info {\r\n                font-size: 13px;\r\n                color: #888888;\r\n            }\r\n            .email-footer a {\r\n                color: #667eea;\r\n                text-decoration: none;\r\n            }\r\n            .button {\r\n                display: inline-block;\r\n                padding: 12px 30px;\r\n                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\r\n                color: #ffffff !important;\r\n                text-decoration: none;\r\n                border-radius: 5px;\r\n                font-weight: 600;\r\n                margin: 20px 0;\r\n                text-align: center;\r\n            }\r\n            .button:hover {\r\n                opacity: 0.9;\r\n                text-decoration: none;\r\n            }\r\n            @media only screen and (max-width: 600px) {\r\n                .email-wrapper {\r\n                    width: 100% !important;\r\n                }\r\n                .email-header, .email-body, .email-footer {\r\n                    padding: 20px !important;\r\n                }\r\n                .email-header h1 {\r\n                    font-size: 24px !important;\r\n                }\r\n            }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\"email-wrapper\">\r\n            <div class=\"email-header\">\r\n                <div class=\"logo\">Awdheegle</div>\r\n                <h1>Test</h1>\r\n            </div>\r\n            \r\n            <div class=\"email-body\">\r\n                <div class=\"email-greeting\">\r\n                    Hello Emily Rodriguez,\r\n                </div>\r\n                \r\n                <div class=\"email-content\">\r\n                    Test From TacliinHub System\r\n                </div>\r\n            </div>\r\n            \r\n            <div class=\"email-divider\"></div>\r\n            \r\n            <div class=\"email-footer\">\r\n                <p class=\"school-name\">Awdheegle</p>\r\n                <p class=\"contact-info\">\r\n                    Email: <a href=\"mailto:abdihagad@gmail.com\">abdihagad@gmail.com</a>\r\n                </p>\r\n                <p class=\"contact-info\" style=\"margin-top: 20px; font-size: 12px; color: #999999;\">\r\n                    &copy; 2025 Awdheegle. All rights reserved.<br>\r\n                    This is an automated email. Please do not reply directly to this message.\r\n                </p>\r\n            </div>\r\n        </div>\r\n    </body>\r\n    </html>', 'Sent', NULL, 1, '2025-12-19 14:15:58'),
(564, 'Email', 'david.williams@school.edu', 'Test', '\r\n    <!DOCTYPE html>\r\n    <html lang=\"en\">\r\n    <head>\r\n        <meta charset=\"UTF-8\">\r\n        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n        <title>Test</title>\r\n        <style>\r\n            * {\r\n                margin: 0;\r\n                padding: 0;\r\n                box-sizing: border-box;\r\n            }\r\n            body {\r\n                font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif;\r\n                line-height: 1.6;\r\n                color: #333333;\r\n                background-color: #f4f4f4;\r\n                padding: 20px;\r\n            }\r\n            .email-wrapper {\r\n                max-width: 600px;\r\n                margin: 0 auto;\r\n                background-color: #ffffff;\r\n                border-radius: 10px;\r\n                overflow: hidden;\r\n                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);\r\n            }\r\n            .email-header {\r\n                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\r\n                color: #ffffff;\r\n                padding: 40px 30px;\r\n                text-align: center;\r\n            }\r\n            .email-header h1 {\r\n                font-size: 28px;\r\n                font-weight: 600;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-header .logo {\r\n                font-size: 24px;\r\n                font-weight: bold;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-body {\r\n                padding: 40px 30px;\r\n                background-color: #ffffff;\r\n            }\r\n            .email-greeting {\r\n                font-size: 18px;\r\n                color: #333333;\r\n                margin-bottom: 20px;\r\n            }\r\n            .email-content {\r\n                font-size: 16px;\r\n                color: #555555;\r\n                line-height: 1.8;\r\n                margin-bottom: 30px;\r\n            }\r\n            .email-content p {\r\n                margin-bottom: 15px;\r\n            }\r\n            .email-content a {\r\n                color: #667eea;\r\n                text-decoration: none;\r\n            }\r\n            .email-content a:hover {\r\n                text-decoration: underline;\r\n            }\r\n            .email-divider {\r\n                height: 2px;\r\n                background: linear-gradient(90deg, transparent, #e0e0e0, transparent);\r\n                margin: 30px 0;\r\n            }\r\n            .email-footer {\r\n                background-color: #f8f9fa;\r\n                padding: 30px;\r\n                text-align: center;\r\n                border-top: 1px solid #e0e0e0;\r\n            }\r\n            .email-footer p {\r\n                font-size: 14px;\r\n                color: #666666;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-footer .school-name {\r\n                font-weight: 600;\r\n                color: #333333;\r\n                font-size: 16px;\r\n                margin-bottom: 5px;\r\n            }\r\n            .email-footer .contact-info {\r\n                font-size: 13px;\r\n                color: #888888;\r\n            }\r\n            .email-footer a {\r\n                color: #667eea;\r\n                text-decoration: none;\r\n            }\r\n            .button {\r\n                display: inline-block;\r\n                padding: 12px 30px;\r\n                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\r\n                color: #ffffff !important;\r\n                text-decoration: none;\r\n                border-radius: 5px;\r\n                font-weight: 600;\r\n                margin: 20px 0;\r\n                text-align: center;\r\n            }\r\n            .button:hover {\r\n                opacity: 0.9;\r\n                text-decoration: none;\r\n            }\r\n            @media only screen and (max-width: 600px) {\r\n                .email-wrapper {\r\n                    width: 100% !important;\r\n                }\r\n                .email-header, .email-body, .email-footer {\r\n                    padding: 20px !important;\r\n                }\r\n                .email-header h1 {\r\n                    font-size: 24px !important;\r\n                }\r\n            }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\"email-wrapper\">\r\n            <div class=\"email-header\">\r\n                <div class=\"logo\">Awdheegle</div>\r\n                <h1>Test</h1>\r\n            </div>\r\n            \r\n            <div class=\"email-body\">\r\n                <div class=\"email-greeting\">\r\n                    Hello David Williams,\r\n                </div>\r\n                \r\n                <div class=\"email-content\">\r\n                    Test From TacliinHub System\r\n                </div>\r\n            </div>\r\n            \r\n            <div class=\"email-divider\"></div>\r\n            \r\n            <div class=\"email-footer\">\r\n                <p class=\"school-name\">Awdheegle</p>\r\n                <p class=\"contact-info\">\r\n                    Email: <a href=\"mailto:abdihagad@gmail.com\">abdihagad@gmail.com</a>\r\n                </p>\r\n                <p class=\"contact-info\" style=\"margin-top: 20px; font-size: 12px; color: #999999;\">\r\n                    &copy; 2025 Awdheegle. All rights reserved.<br>\r\n                    This is an automated email. Please do not reply directly to this message.\r\n                </p>\r\n            </div>\r\n        </div>\r\n    </body>\r\n    </html>', 'Sent', NULL, 1, '2025-12-19 14:16:03'),
(565, 'Email', 'jessica.brown@school.edu', 'Test', '\r\n    <!DOCTYPE html>\r\n    <html lang=\"en\">\r\n    <head>\r\n        <meta charset=\"UTF-8\">\r\n        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n        <title>Test</title>\r\n        <style>\r\n            * {\r\n                margin: 0;\r\n                padding: 0;\r\n                box-sizing: border-box;\r\n            }\r\n            body {\r\n                font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif;\r\n                line-height: 1.6;\r\n                color: #333333;\r\n                background-color: #f4f4f4;\r\n                padding: 20px;\r\n            }\r\n            .email-wrapper {\r\n                max-width: 600px;\r\n                margin: 0 auto;\r\n                background-color: #ffffff;\r\n                border-radius: 10px;\r\n                overflow: hidden;\r\n                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);\r\n            }\r\n            .email-header {\r\n                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\r\n                color: #ffffff;\r\n                padding: 40px 30px;\r\n                text-align: center;\r\n            }\r\n            .email-header h1 {\r\n                font-size: 28px;\r\n                font-weight: 600;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-header .logo {\r\n                font-size: 24px;\r\n                font-weight: bold;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-body {\r\n                padding: 40px 30px;\r\n                background-color: #ffffff;\r\n            }\r\n            .email-greeting {\r\n                font-size: 18px;\r\n                color: #333333;\r\n                margin-bottom: 20px;\r\n            }\r\n            .email-content {\r\n                font-size: 16px;\r\n                color: #555555;\r\n                line-height: 1.8;\r\n                margin-bottom: 30px;\r\n            }\r\n            .email-content p {\r\n                margin-bottom: 15px;\r\n            }\r\n            .email-content a {\r\n                color: #667eea;\r\n                text-decoration: none;\r\n            }\r\n            .email-content a:hover {\r\n                text-decoration: underline;\r\n            }\r\n            .email-divider {\r\n                height: 2px;\r\n                background: linear-gradient(90deg, transparent, #e0e0e0, transparent);\r\n                margin: 30px 0;\r\n            }\r\n            .email-footer {\r\n                background-color: #f8f9fa;\r\n                padding: 30px;\r\n                text-align: center;\r\n                border-top: 1px solid #e0e0e0;\r\n            }\r\n            .email-footer p {\r\n                font-size: 14px;\r\n                color: #666666;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-footer .school-name {\r\n                font-weight: 600;\r\n                color: #333333;\r\n                font-size: 16px;\r\n                margin-bottom: 5px;\r\n            }\r\n            .email-footer .contact-info {\r\n                font-size: 13px;\r\n                color: #888888;\r\n            }\r\n            .email-footer a {\r\n                color: #667eea;\r\n                text-decoration: none;\r\n            }\r\n            .button {\r\n                display: inline-block;\r\n                padding: 12px 30px;\r\n                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\r\n                color: #ffffff !important;\r\n                text-decoration: none;\r\n                border-radius: 5px;\r\n                font-weight: 600;\r\n                margin: 20px 0;\r\n                text-align: center;\r\n            }\r\n            .button:hover {\r\n                opacity: 0.9;\r\n                text-decoration: none;\r\n            }\r\n            @media only screen and (max-width: 600px) {\r\n                .email-wrapper {\r\n                    width: 100% !important;\r\n                }\r\n                .email-header, .email-body, .email-footer {\r\n                    padding: 20px !important;\r\n                }\r\n                .email-header h1 {\r\n                    font-size: 24px !important;\r\n                }\r\n            }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\"email-wrapper\">\r\n            <div class=\"email-header\">\r\n                <div class=\"logo\">Awdheegle</div>\r\n                <h1>Test</h1>\r\n            </div>\r\n            \r\n            <div class=\"email-body\">\r\n                <div class=\"email-greeting\">\r\n                    Hello Jessica Brown,\r\n                </div>\r\n                \r\n                <div class=\"email-content\">\r\n                    Test From TacliinHub System\r\n                </div>\r\n            </div>\r\n            \r\n            <div class=\"email-divider\"></div>\r\n            \r\n            <div class=\"email-footer\">\r\n                <p class=\"school-name\">Awdheegle</p>\r\n                <p class=\"contact-info\">\r\n                    Email: <a href=\"mailto:abdihagad@gmail.com\">abdihagad@gmail.com</a>\r\n                </p>\r\n                <p class=\"contact-info\" style=\"margin-top: 20px; font-size: 12px; color: #999999;\">\r\n                    &copy; 2025 Awdheegle. All rights reserved.<br>\r\n                    This is an automated email. Please do not reply directly to this message.\r\n                </p>\r\n            </div>\r\n        </div>\r\n    </body>\r\n    </html>', 'Sent', NULL, 1, '2025-12-19 14:16:06');
INSERT INTO `communication_logs` (`id`, `communication_type`, `recipient`, `subject`, `message`, `status`, `error_message`, `sent_by`, `sent_at`) VALUES
(566, 'Email', 'robert.taylor@school.edu', 'Test', '\r\n    <!DOCTYPE html>\r\n    <html lang=\"en\">\r\n    <head>\r\n        <meta charset=\"UTF-8\">\r\n        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n        <title>Test</title>\r\n        <style>\r\n            * {\r\n                margin: 0;\r\n                padding: 0;\r\n                box-sizing: border-box;\r\n            }\r\n            body {\r\n                font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif;\r\n                line-height: 1.6;\r\n                color: #333333;\r\n                background-color: #f4f4f4;\r\n                padding: 20px;\r\n            }\r\n            .email-wrapper {\r\n                max-width: 600px;\r\n                margin: 0 auto;\r\n                background-color: #ffffff;\r\n                border-radius: 10px;\r\n                overflow: hidden;\r\n                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);\r\n            }\r\n            .email-header {\r\n                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\r\n                color: #ffffff;\r\n                padding: 40px 30px;\r\n                text-align: center;\r\n            }\r\n            .email-header h1 {\r\n                font-size: 28px;\r\n                font-weight: 600;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-header .logo {\r\n                font-size: 24px;\r\n                font-weight: bold;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-body {\r\n                padding: 40px 30px;\r\n                background-color: #ffffff;\r\n            }\r\n            .email-greeting {\r\n                font-size: 18px;\r\n                color: #333333;\r\n                margin-bottom: 20px;\r\n            }\r\n            .email-content {\r\n                font-size: 16px;\r\n                color: #555555;\r\n                line-height: 1.8;\r\n                margin-bottom: 30px;\r\n            }\r\n            .email-content p {\r\n                margin-bottom: 15px;\r\n            }\r\n            .email-content a {\r\n                color: #667eea;\r\n                text-decoration: none;\r\n            }\r\n            .email-content a:hover {\r\n                text-decoration: underline;\r\n            }\r\n            .email-divider {\r\n                height: 2px;\r\n                background: linear-gradient(90deg, transparent, #e0e0e0, transparent);\r\n                margin: 30px 0;\r\n            }\r\n            .email-footer {\r\n                background-color: #f8f9fa;\r\n                padding: 30px;\r\n                text-align: center;\r\n                border-top: 1px solid #e0e0e0;\r\n            }\r\n            .email-footer p {\r\n                font-size: 14px;\r\n                color: #666666;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-footer .school-name {\r\n                font-weight: 600;\r\n                color: #333333;\r\n                font-size: 16px;\r\n                margin-bottom: 5px;\r\n            }\r\n            .email-footer .contact-info {\r\n                font-size: 13px;\r\n                color: #888888;\r\n            }\r\n            .email-footer a {\r\n                color: #667eea;\r\n                text-decoration: none;\r\n            }\r\n            .button {\r\n                display: inline-block;\r\n                padding: 12px 30px;\r\n                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\r\n                color: #ffffff !important;\r\n                text-decoration: none;\r\n                border-radius: 5px;\r\n                font-weight: 600;\r\n                margin: 20px 0;\r\n                text-align: center;\r\n            }\r\n            .button:hover {\r\n                opacity: 0.9;\r\n                text-decoration: none;\r\n            }\r\n            @media only screen and (max-width: 600px) {\r\n                .email-wrapper {\r\n                    width: 100% !important;\r\n                }\r\n                .email-header, .email-body, .email-footer {\r\n                    padding: 20px !important;\r\n                }\r\n                .email-header h1 {\r\n                    font-size: 24px !important;\r\n                }\r\n            }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\"email-wrapper\">\r\n            <div class=\"email-header\">\r\n                <div class=\"logo\">Awdheegle</div>\r\n                <h1>Test</h1>\r\n            </div>\r\n            \r\n            <div class=\"email-body\">\r\n                <div class=\"email-greeting\">\r\n                    Hello Robert Taylor,\r\n                </div>\r\n                \r\n                <div class=\"email-content\">\r\n                    Test From TacliinHub System\r\n                </div>\r\n            </div>\r\n            \r\n            <div class=\"email-divider\"></div>\r\n            \r\n            <div class=\"email-footer\">\r\n                <p class=\"school-name\">Awdheegle</p>\r\n                <p class=\"contact-info\">\r\n                    Email: <a href=\"mailto:abdihagad@gmail.com\">abdihagad@gmail.com</a>\r\n                </p>\r\n                <p class=\"contact-info\" style=\"margin-top: 20px; font-size: 12px; color: #999999;\">\r\n                    &copy; 2025 Awdheegle. All rights reserved.<br>\r\n                    This is an automated email. Please do not reply directly to this message.\r\n                </p>\r\n            </div>\r\n        </div>\r\n    </body>\r\n    </html>', 'Sent', NULL, 1, '2025-12-19 14:16:11'),
(567, 'Email', 'amanda.martinez@school.edu', 'Test', '\r\n    <!DOCTYPE html>\r\n    <html lang=\"en\">\r\n    <head>\r\n        <meta charset=\"UTF-8\">\r\n        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n        <title>Test</title>\r\n        <style>\r\n            * {\r\n                margin: 0;\r\n                padding: 0;\r\n                box-sizing: border-box;\r\n            }\r\n            body {\r\n                font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif;\r\n                line-height: 1.6;\r\n                color: #333333;\r\n                background-color: #f4f4f4;\r\n                padding: 20px;\r\n            }\r\n            .email-wrapper {\r\n                max-width: 600px;\r\n                margin: 0 auto;\r\n                background-color: #ffffff;\r\n                border-radius: 10px;\r\n                overflow: hidden;\r\n                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);\r\n            }\r\n            .email-header {\r\n                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\r\n                color: #ffffff;\r\n                padding: 40px 30px;\r\n                text-align: center;\r\n            }\r\n            .email-header h1 {\r\n                font-size: 28px;\r\n                font-weight: 600;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-header .logo {\r\n                font-size: 24px;\r\n                font-weight: bold;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-body {\r\n                padding: 40px 30px;\r\n                background-color: #ffffff;\r\n            }\r\n            .email-greeting {\r\n                font-size: 18px;\r\n                color: #333333;\r\n                margin-bottom: 20px;\r\n            }\r\n            .email-content {\r\n                font-size: 16px;\r\n                color: #555555;\r\n                line-height: 1.8;\r\n                margin-bottom: 30px;\r\n            }\r\n            .email-content p {\r\n                margin-bottom: 15px;\r\n            }\r\n            .email-content a {\r\n                color: #667eea;\r\n                text-decoration: none;\r\n            }\r\n            .email-content a:hover {\r\n                text-decoration: underline;\r\n            }\r\n            .email-divider {\r\n                height: 2px;\r\n                background: linear-gradient(90deg, transparent, #e0e0e0, transparent);\r\n                margin: 30px 0;\r\n            }\r\n            .email-footer {\r\n                background-color: #f8f9fa;\r\n                padding: 30px;\r\n                text-align: center;\r\n                border-top: 1px solid #e0e0e0;\r\n            }\r\n            .email-footer p {\r\n                font-size: 14px;\r\n                color: #666666;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-footer .school-name {\r\n                font-weight: 600;\r\n                color: #333333;\r\n                font-size: 16px;\r\n                margin-bottom: 5px;\r\n            }\r\n            .email-footer .contact-info {\r\n                font-size: 13px;\r\n                color: #888888;\r\n            }\r\n            .email-footer a {\r\n                color: #667eea;\r\n                text-decoration: none;\r\n            }\r\n            .button {\r\n                display: inline-block;\r\n                padding: 12px 30px;\r\n                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\r\n                color: #ffffff !important;\r\n                text-decoration: none;\r\n                border-radius: 5px;\r\n                font-weight: 600;\r\n                margin: 20px 0;\r\n                text-align: center;\r\n            }\r\n            .button:hover {\r\n                opacity: 0.9;\r\n                text-decoration: none;\r\n            }\r\n            @media only screen and (max-width: 600px) {\r\n                .email-wrapper {\r\n                    width: 100% !important;\r\n                }\r\n                .email-header, .email-body, .email-footer {\r\n                    padding: 20px !important;\r\n                }\r\n                .email-header h1 {\r\n                    font-size: 24px !important;\r\n                }\r\n            }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\"email-wrapper\">\r\n            <div class=\"email-header\">\r\n                <div class=\"logo\">Awdheegle</div>\r\n                <h1>Test</h1>\r\n            </div>\r\n            \r\n            <div class=\"email-body\">\r\n                <div class=\"email-greeting\">\r\n                    Hello Amanda Martinez,\r\n                </div>\r\n                \r\n                <div class=\"email-content\">\r\n                    Test From TacliinHub System\r\n                </div>\r\n            </div>\r\n            \r\n            <div class=\"email-divider\"></div>\r\n            \r\n            <div class=\"email-footer\">\r\n                <p class=\"school-name\">Awdheegle</p>\r\n                <p class=\"contact-info\">\r\n                    Email: <a href=\"mailto:abdihagad@gmail.com\">abdihagad@gmail.com</a>\r\n                </p>\r\n                <p class=\"contact-info\" style=\"margin-top: 20px; font-size: 12px; color: #999999;\">\r\n                    &copy; 2025 Awdheegle. All rights reserved.<br>\r\n                    This is an automated email. Please do not reply directly to this message.\r\n                </p>\r\n            </div>\r\n        </div>\r\n    </body>\r\n    </html>', 'Sent', NULL, 1, '2025-12-19 14:16:15'),
(568, 'Email', 'james.anderson@school.edu', 'Test', '\r\n    <!DOCTYPE html>\r\n    <html lang=\"en\">\r\n    <head>\r\n        <meta charset=\"UTF-8\">\r\n        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n        <title>Test</title>\r\n        <style>\r\n            * {\r\n                margin: 0;\r\n                padding: 0;\r\n                box-sizing: border-box;\r\n            }\r\n            body {\r\n                font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif;\r\n                line-height: 1.6;\r\n                color: #333333;\r\n                background-color: #f4f4f4;\r\n                padding: 20px;\r\n            }\r\n            .email-wrapper {\r\n                max-width: 600px;\r\n                margin: 0 auto;\r\n                background-color: #ffffff;\r\n                border-radius: 10px;\r\n                overflow: hidden;\r\n                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);\r\n            }\r\n            .email-header {\r\n                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\r\n                color: #ffffff;\r\n                padding: 40px 30px;\r\n                text-align: center;\r\n            }\r\n            .email-header h1 {\r\n                font-size: 28px;\r\n                font-weight: 600;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-header .logo {\r\n                font-size: 24px;\r\n                font-weight: bold;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-body {\r\n                padding: 40px 30px;\r\n                background-color: #ffffff;\r\n            }\r\n            .email-greeting {\r\n                font-size: 18px;\r\n                color: #333333;\r\n                margin-bottom: 20px;\r\n            }\r\n            .email-content {\r\n                font-size: 16px;\r\n                color: #555555;\r\n                line-height: 1.8;\r\n                margin-bottom: 30px;\r\n            }\r\n            .email-content p {\r\n                margin-bottom: 15px;\r\n            }\r\n            .email-content a {\r\n                color: #667eea;\r\n                text-decoration: none;\r\n            }\r\n            .email-content a:hover {\r\n                text-decoration: underline;\r\n            }\r\n            .email-divider {\r\n                height: 2px;\r\n                background: linear-gradient(90deg, transparent, #e0e0e0, transparent);\r\n                margin: 30px 0;\r\n            }\r\n            .email-footer {\r\n                background-color: #f8f9fa;\r\n                padding: 30px;\r\n                text-align: center;\r\n                border-top: 1px solid #e0e0e0;\r\n            }\r\n            .email-footer p {\r\n                font-size: 14px;\r\n                color: #666666;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-footer .school-name {\r\n                font-weight: 600;\r\n                color: #333333;\r\n                font-size: 16px;\r\n                margin-bottom: 5px;\r\n            }\r\n            .email-footer .contact-info {\r\n                font-size: 13px;\r\n                color: #888888;\r\n            }\r\n            .email-footer a {\r\n                color: #667eea;\r\n                text-decoration: none;\r\n            }\r\n            .button {\r\n                display: inline-block;\r\n                padding: 12px 30px;\r\n                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\r\n                color: #ffffff !important;\r\n                text-decoration: none;\r\n                border-radius: 5px;\r\n                font-weight: 600;\r\n                margin: 20px 0;\r\n                text-align: center;\r\n            }\r\n            .button:hover {\r\n                opacity: 0.9;\r\n                text-decoration: none;\r\n            }\r\n            @media only screen and (max-width: 600px) {\r\n                .email-wrapper {\r\n                    width: 100% !important;\r\n                }\r\n                .email-header, .email-body, .email-footer {\r\n                    padding: 20px !important;\r\n                }\r\n                .email-header h1 {\r\n                    font-size: 24px !important;\r\n                }\r\n            }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\"email-wrapper\">\r\n            <div class=\"email-header\">\r\n                <div class=\"logo\">Awdheegle</div>\r\n                <h1>Test</h1>\r\n            </div>\r\n            \r\n            <div class=\"email-body\">\r\n                <div class=\"email-greeting\">\r\n                    Hello James Anderson,\r\n                </div>\r\n                \r\n                <div class=\"email-content\">\r\n                    Test From TacliinHub System\r\n                </div>\r\n            </div>\r\n            \r\n            <div class=\"email-divider\"></div>\r\n            \r\n            <div class=\"email-footer\">\r\n                <p class=\"school-name\">Awdheegle</p>\r\n                <p class=\"contact-info\">\r\n                    Email: <a href=\"mailto:abdihagad@gmail.com\">abdihagad@gmail.com</a>\r\n                </p>\r\n                <p class=\"contact-info\" style=\"margin-top: 20px; font-size: 12px; color: #999999;\">\r\n                    &copy; 2025 Awdheegle. All rights reserved.<br>\r\n                    This is an automated email. Please do not reply directly to this message.\r\n                </p>\r\n            </div>\r\n        </div>\r\n    </body>\r\n    </html>', 'Sent', NULL, 1, '2025-12-19 14:16:18'),
(569, 'Email', 'lisa.wilson@school.edu', 'Test', '\r\n    <!DOCTYPE html>\r\n    <html lang=\"en\">\r\n    <head>\r\n        <meta charset=\"UTF-8\">\r\n        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n        <title>Test</title>\r\n        <style>\r\n            * {\r\n                margin: 0;\r\n                padding: 0;\r\n                box-sizing: border-box;\r\n            }\r\n            body {\r\n                font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif;\r\n                line-height: 1.6;\r\n                color: #333333;\r\n                background-color: #f4f4f4;\r\n                padding: 20px;\r\n            }\r\n            .email-wrapper {\r\n                max-width: 600px;\r\n                margin: 0 auto;\r\n                background-color: #ffffff;\r\n                border-radius: 10px;\r\n                overflow: hidden;\r\n                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);\r\n            }\r\n            .email-header {\r\n                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\r\n                color: #ffffff;\r\n                padding: 40px 30px;\r\n                text-align: center;\r\n            }\r\n            .email-header h1 {\r\n                font-size: 28px;\r\n                font-weight: 600;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-header .logo {\r\n                font-size: 24px;\r\n                font-weight: bold;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-body {\r\n                padding: 40px 30px;\r\n                background-color: #ffffff;\r\n            }\r\n            .email-greeting {\r\n                font-size: 18px;\r\n                color: #333333;\r\n                margin-bottom: 20px;\r\n            }\r\n            .email-content {\r\n                font-size: 16px;\r\n                color: #555555;\r\n                line-height: 1.8;\r\n                margin-bottom: 30px;\r\n            }\r\n            .email-content p {\r\n                margin-bottom: 15px;\r\n            }\r\n            .email-content a {\r\n                color: #667eea;\r\n                text-decoration: none;\r\n            }\r\n            .email-content a:hover {\r\n                text-decoration: underline;\r\n            }\r\n            .email-divider {\r\n                height: 2px;\r\n                background: linear-gradient(90deg, transparent, #e0e0e0, transparent);\r\n                margin: 30px 0;\r\n            }\r\n            .email-footer {\r\n                background-color: #f8f9fa;\r\n                padding: 30px;\r\n                text-align: center;\r\n                border-top: 1px solid #e0e0e0;\r\n            }\r\n            .email-footer p {\r\n                font-size: 14px;\r\n                color: #666666;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-footer .school-name {\r\n                font-weight: 600;\r\n                color: #333333;\r\n                font-size: 16px;\r\n                margin-bottom: 5px;\r\n            }\r\n            .email-footer .contact-info {\r\n                font-size: 13px;\r\n                color: #888888;\r\n            }\r\n            .email-footer a {\r\n                color: #667eea;\r\n                text-decoration: none;\r\n            }\r\n            .button {\r\n                display: inline-block;\r\n                padding: 12px 30px;\r\n                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\r\n                color: #ffffff !important;\r\n                text-decoration: none;\r\n                border-radius: 5px;\r\n                font-weight: 600;\r\n                margin: 20px 0;\r\n                text-align: center;\r\n            }\r\n            .button:hover {\r\n                opacity: 0.9;\r\n                text-decoration: none;\r\n            }\r\n            @media only screen and (max-width: 600px) {\r\n                .email-wrapper {\r\n                    width: 100% !important;\r\n                }\r\n                .email-header, .email-body, .email-footer {\r\n                    padding: 20px !important;\r\n                }\r\n                .email-header h1 {\r\n                    font-size: 24px !important;\r\n                }\r\n            }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\"email-wrapper\">\r\n            <div class=\"email-header\">\r\n                <div class=\"logo\">Awdheegle</div>\r\n                <h1>Test</h1>\r\n            </div>\r\n            \r\n            <div class=\"email-body\">\r\n                <div class=\"email-greeting\">\r\n                    Hello Lisa Wilson,\r\n                </div>\r\n                \r\n                <div class=\"email-content\">\r\n                    Test From TacliinHub System\r\n                </div>\r\n            </div>\r\n            \r\n            <div class=\"email-divider\"></div>\r\n            \r\n            <div class=\"email-footer\">\r\n                <p class=\"school-name\">Awdheegle</p>\r\n                <p class=\"contact-info\">\r\n                    Email: <a href=\"mailto:abdihagad@gmail.com\">abdihagad@gmail.com</a>\r\n                </p>\r\n                <p class=\"contact-info\" style=\"margin-top: 20px; font-size: 12px; color: #999999;\">\r\n                    &copy; 2025 Awdheegle. All rights reserved.<br>\r\n                    This is an automated email. Please do not reply directly to this message.\r\n                </p>\r\n            </div>\r\n        </div>\r\n    </body>\r\n    </html>', 'Sent', NULL, 1, '2025-12-19 14:16:23'),
(570, 'Email', 'christopher.moore@school.edu', 'Test', '\r\n    <!DOCTYPE html>\r\n    <html lang=\"en\">\r\n    <head>\r\n        <meta charset=\"UTF-8\">\r\n        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n        <title>Test</title>\r\n        <style>\r\n            * {\r\n                margin: 0;\r\n                padding: 0;\r\n                box-sizing: border-box;\r\n            }\r\n            body {\r\n                font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif;\r\n                line-height: 1.6;\r\n                color: #333333;\r\n                background-color: #f4f4f4;\r\n                padding: 20px;\r\n            }\r\n            .email-wrapper {\r\n                max-width: 600px;\r\n                margin: 0 auto;\r\n                background-color: #ffffff;\r\n                border-radius: 10px;\r\n                overflow: hidden;\r\n                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);\r\n            }\r\n            .email-header {\r\n                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\r\n                color: #ffffff;\r\n                padding: 40px 30px;\r\n                text-align: center;\r\n            }\r\n            .email-header h1 {\r\n                font-size: 28px;\r\n                font-weight: 600;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-header .logo {\r\n                font-size: 24px;\r\n                font-weight: bold;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-body {\r\n                padding: 40px 30px;\r\n                background-color: #ffffff;\r\n            }\r\n            .email-greeting {\r\n                font-size: 18px;\r\n                color: #333333;\r\n                margin-bottom: 20px;\r\n            }\r\n            .email-content {\r\n                font-size: 16px;\r\n                color: #555555;\r\n                line-height: 1.8;\r\n                margin-bottom: 30px;\r\n            }\r\n            .email-content p {\r\n                margin-bottom: 15px;\r\n            }\r\n            .email-content a {\r\n                color: #667eea;\r\n                text-decoration: none;\r\n            }\r\n            .email-content a:hover {\r\n                text-decoration: underline;\r\n            }\r\n            .email-divider {\r\n                height: 2px;\r\n                background: linear-gradient(90deg, transparent, #e0e0e0, transparent);\r\n                margin: 30px 0;\r\n            }\r\n            .email-footer {\r\n                background-color: #f8f9fa;\r\n                padding: 30px;\r\n                text-align: center;\r\n                border-top: 1px solid #e0e0e0;\r\n            }\r\n            .email-footer p {\r\n                font-size: 14px;\r\n                color: #666666;\r\n                margin-bottom: 10px;\r\n            }\r\n            .email-footer .school-name {\r\n                font-weight: 600;\r\n                color: #333333;\r\n                font-size: 16px;\r\n                margin-bottom: 5px;\r\n            }\r\n            .email-footer .contact-info {\r\n                font-size: 13px;\r\n                color: #888888;\r\n            }\r\n            .email-footer a {\r\n                color: #667eea;\r\n                text-decoration: none;\r\n            }\r\n            .button {\r\n                display: inline-block;\r\n                padding: 12px 30px;\r\n                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\r\n                color: #ffffff !important;\r\n                text-decoration: none;\r\n                border-radius: 5px;\r\n                font-weight: 600;\r\n                margin: 20px 0;\r\n                text-align: center;\r\n            }\r\n            .button:hover {\r\n                opacity: 0.9;\r\n                text-decoration: none;\r\n            }\r\n            @media only screen and (max-width: 600px) {\r\n                .email-wrapper {\r\n                    width: 100% !important;\r\n                }\r\n                .email-header, .email-body, .email-footer {\r\n                    padding: 20px !important;\r\n                }\r\n                .email-header h1 {\r\n                    font-size: 24px !important;\r\n                }\r\n            }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\"email-wrapper\">\r\n            <div class=\"email-header\">\r\n                <div class=\"logo\">Awdheegle</div>\r\n                <h1>Test</h1>\r\n            </div>\r\n            \r\n            <div class=\"email-body\">\r\n                <div class=\"email-greeting\">\r\n                    Hello Christopher Moore,\r\n                </div>\r\n                \r\n                <div class=\"email-content\">\r\n                    Test From TacliinHub System\r\n                </div>\r\n            </div>\r\n            \r\n            <div class=\"email-divider\"></div>\r\n            \r\n            <div class=\"email-footer\">\r\n                <p class=\"school-name\">Awdheegle</p>\r\n                <p class=\"contact-info\">\r\n                    Email: <a href=\"mailto:abdihagad@gmail.com\">abdihagad@gmail.com</a>\r\n                </p>\r\n                <p class=\"contact-info\" style=\"margin-top: 20px; font-size: 12px; color: #999999;\">\r\n                    &copy; 2025 Awdheegle. All rights reserved.<br>\r\n                    This is an automated email. Please do not reply directly to this message.\r\n                </p>\r\n            </div>\r\n        </div>\r\n    </body>\r\n    </html>', 'Sent', NULL, 1, '2025-12-19 14:16:27'),
(571, 'Email', 'info@uukowtech.com', 'Password Reset - School ERP System', '\r\n    <html>\r\n    <head>\r\n        <style>\r\n            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }\r\n            .container { max-width: 600px; margin: 0 auto; padding: 20px; }\r\n            .header { background: linear-gradient(135deg, #fa5c7c 0%, #ff6b9d 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }\r\n            .content { background: #f8f9fa; padding: 30px; }\r\n            .credentials { background: white; padding: 25px; border-left: 4px solid #fa5c7c; margin: 20px 0; border-radius: 4px; }\r\n            .password-box { background: #fff3cd; border: 2px dashed #ffc107; padding: 15px; margin: 15px 0; text-align: center; border-radius: 4px; }\r\n            .password-text { font-size: 24px; font-weight: bold; color: #856404; letter-spacing: 2px; font-family: \'Courier New\', monospace; }\r\n            .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; }\r\n            .button { display: inline-block; padding: 12px 30px; background: #fa5c7c; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }\r\n            .footer { text-align: center; padding: 20px; color: #999; font-size: 12px; }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\'container\'>\r\n            <div class=\'header\'>\r\n                <h1>🔒 Password Reset</h1>\r\n            </div>\r\n            <div class=\'content\'>\r\n                <p>Hello <strong>stu000002</strong>,</p>\r\n                <p>Your password has been reset by an administrator. Please use the new password below to log in to your account:</p>\r\n                \r\n                <div class=\'credentials\'>\r\n                    <p><strong>Username:</strong> stu000002</p>\r\n                    <div class=\'password-box\'>\r\n                        <p style=\'margin: 0 0 10px 0; color: #856404;\'><strong>Your New Password:</strong></p>\r\n                        <div class=\'password-text\'>46204cbc958b</div>\r\n                    </div>\r\n                    <p style=\'margin-top: 15px;\'><strong>Login URL:</strong> <a href=\'http://localhost/schoolerp/\'>http://localhost/schoolerp/</a></p>\r\n                </div>\r\n                \r\n                <div class=\'warning\'>\r\n                    <p style=\'margin: 0;\'><strong>⚠️ Security Notice:</strong></p>\r\n                    <p style=\'margin: 10px 0 0 0;\'>For your security, please change this password immediately after logging in. Do not share this password with anyone.</p>\r\n                </div>\r\n                \r\n                <p style=\'text-align: center;\'>\r\n                    <a href=\'http://localhost/schoolerp/\' class=\'button\'>Login to Your Account</a>\r\n                </p>\r\n                \r\n                <p>If you did not request this password reset, please contact us immediately at info@uukowtech.com</p>\r\n                <p>Best regards,<br><strong>School ERP System Team</strong></p>\r\n            </div>\r\n            <div class=\'footer\'>\r\n                <p>&copy; 2025 School ERP System. All rights reserved.</p>\r\n                <p>This is an automated email. Please do not reply to this message.</p>\r\n            </div>\r\n        </div>\r\n    </body>\r\n    </html>\r\n    ', 'Sent', NULL, 1, '2025-12-19 15:23:25'),
(572, 'Email', 'abdulkadiruukow@gmail.com', 'Password Reset - TacliinHub ERP System', '\r\n    <html>\r\n    <head>\r\n        <style>\r\n            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }\r\n            .container { max-width: 600px; margin: 0 auto; padding: 20px; }\r\n            .header { background: linear-gradient(135deg, #fa5c7c 0%, #ff6b9d 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }\r\n            .content { background: #f8f9fa; padding: 30px; }\r\n            .credentials { background: white; padding: 25px; border-left: 4px solid #fa5c7c; margin: 20px 0; border-radius: 4px; }\r\n            .password-box { background: #fff3cd; border: 2px dashed #ffc107; padding: 15px; margin: 15px 0; text-align: center; border-radius: 4px; }\r\n            .password-text { font-size: 24px; font-weight: bold; color: #856404; letter-spacing: 2px; font-family: \'Courier New\', monospace; }\r\n            .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; }\r\n            .button { display: inline-block; padding: 12px 30px; background: #fa5c7c; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }\r\n            .footer { text-align: center; padding: 20px; color: #999; font-size: 12px; }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\'container\'>\r\n            <div class=\'header\'>\r\n                <h1>🔒 Password Reset</h1>\r\n            </div>\r\n            <div class=\'content\'>\r\n                <p>Hello <strong>uukow</strong>,</p>\r\n                <p>Your password has been reset by an administrator. Please use the new password below to log in to your account:</p>\r\n                \r\n                <div class=\'credentials\'>\r\n                    <p><strong>Username:</strong> uukow</p>\r\n                    <div class=\'password-box\'>\r\n                        <p style=\'margin: 0 0 10px 0; color: #856404;\'><strong>Your New Password:</strong></p>\r\n                        <div class=\'password-text\'>1c75f021514e</div>\r\n                    </div>\r\n                    <p style=\'margin-top: 15px;\'><strong>Login URL:</strong> <a href=\'http://localhost/schoolerp/\'>http://localhost/schoolerp/</a></p>\r\n                </div>\r\n                \r\n                <div class=\'warning\'>\r\n                    <p style=\'margin: 0;\'><strong>⚠️ Security Notice:</strong></p>\r\n                    <p style=\'margin: 10px 0 0 0;\'>For your security, please change this password immediately after logging in. Do not share this password with anyone.</p>\r\n                </div>\r\n                \r\n                <p style=\'text-align: center;\'>\r\n                    <a href=\'http://localhost/schoolerp/\' class=\'button\'>Login to Your Account</a>\r\n                </p>\r\n                \r\n                <p>If you did not request this password reset, please contact us immediately at info@uukowtech.com</p>\r\n                <p>Best regards,<br><strong>TacliinHub ERP System Team</strong></p>\r\n            </div>\r\n            <div class=\'footer\'>\r\n                <p>&copy; 2025 TacliinHub ERP System. All rights reserved.</p>\r\n                <p>This is an automated email. Please do not reply to this message.</p>\r\n            </div>\r\n        </div>\r\n    </body>\r\n    </html>\r\n    ', 'Sent', NULL, 1, '2025-12-21 06:20:58');

-- --------------------------------------------------------

--
-- Table structure for table `curriculum`
--

CREATE TABLE `curriculum` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `syllabus` text DEFAULT NULL,
  `learning_objectives` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `event_title` varchar(255) NOT NULL,
  `event_description` text DEFAULT NULL,
  `event_type` varchar(100) DEFAULT NULL,
  `event_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `venue` varchar(255) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `target_audience` varchar(100) DEFAULT NULL,
  `organizer` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `event_title`, `event_description`, `event_type`, `event_date`, `start_time`, `end_time`, `venue`, `branch_id`, `target_audience`, `organizer`, `created_by`, `created_at`) VALUES
(1, 'Teachers', 'isku imaada', 'Academic', '2025-12-03', '00:01:00', '01:01:00', 'xafiiska', 1, NULL, NULL, 1, '2025-12-03 08:02:10');

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `id` int(11) NOT NULL,
  `exam_type_id` int(11) NOT NULL,
  `exam_name` varchar(255) NOT NULL,
  `class_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exams`
--

INSERT INTO `exams` (`id`, `exam_type_id`, `exam_name`, `class_id`, `session_id`, `start_date`, `end_date`, `description`, `created_at`) VALUES
(1, 4, 'Shift One', 1, 1, '2025-11-30', '2025-12-06', '', '2025-11-30 08:24:04'),
(2, 2, 'Second', 1, 1, '2025-12-16', '2025-12-17', '', '2025-12-17 08:31:38');

-- --------------------------------------------------------

--
-- Table structure for table `exam_schedule`
--

CREATE TABLE `exam_schedule` (
  `id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `exam_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `room_no` varchar(50) DEFAULT NULL,
  `total_marks` decimal(10,2) DEFAULT 100.00,
  `passing_marks` decimal(10,2) DEFAULT 40.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exam_schedule`
--

INSERT INTO `exam_schedule` (`id`, `exam_id`, `subject_id`, `exam_date`, `start_time`, `end_time`, `room_no`, `total_marks`, `passing_marks`) VALUES
(1, 1, 1, '2025-11-30', '01:26:00', '02:26:00', 'Hall 1', 30.00, 40.00),
(2, 1, 3, '2025-12-17', '11:32:00', '12:33:00', 'Hall A', 20.00, 40.00);

-- --------------------------------------------------------

--
-- Table structure for table `exam_types`
--

CREATE TABLE `exam_types` (
  `id` int(11) NOT NULL,
  `exam_name` varchar(100) NOT NULL,
  `exam_code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exam_types`
--

INSERT INTO `exam_types` (`id`, `exam_name`, `exam_code`, `description`) VALUES
(1, 'First Term', 'TERM1', NULL),
(2, 'Mid Term', 'MIDTERM', NULL),
(3, 'Second Term', 'TERM2', NULL),
(4, 'Final Exam', 'FINAL', NULL),
(5, 'Quiz', 'QUIZ', NULL),
(6, 'Assignment', 'ASSIGNMENT', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `expense_category` varchar(100) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `expense_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `receipt_file` varchar(255) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `branch_id`, `expense_category`, `amount`, `expense_date`, `description`, `payment_method`, `reference_no`, `receipt_file`, `approved_by`, `recorded_by`, `created_at`) VALUES
(1, 1, 'Staff Salaries', 250.00, '2025-12-19', 'Salary payment for Abdulkadir Uukow (Staff ID: STF000001) - December 2025', 'Mobile Money', 'PAY-5', NULL, 1, 1, '2025-12-19 15:01:51');

-- --------------------------------------------------------

--
-- Table structure for table `fee_invoices`
--

CREATE TABLE `fee_invoices` (
  `id` int(11) NOT NULL,
  `invoice_no` varchar(50) NOT NULL,
  `student_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `net_amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) DEFAULT 0.00,
  `due_amount` decimal(10,2) NOT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('Unpaid','Partially Paid','Paid','Overdue','Waived') DEFAULT 'Unpaid',
  `generated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_invoices`
--

INSERT INTO `fee_invoices` (`id`, `invoice_no`, `student_id`, `session_id`, `total_amount`, `discount`, `net_amount`, `paid_amount`, `due_amount`, `due_date`, `status`, `generated_by`, `created_at`, `updated_at`) VALUES
(1, 'INV000001', 1, 1, 25.00, 0.00, 25.00, 25.00, 0.00, '2025-11-22', 'Paid', 1, '2025-11-22 16:24:21', '2025-11-22 17:26:33'),
(2, 'FLEX-20251213-000002', 1, 1, 60.00, 0.00, 60.00, 60.00, 0.00, NULL, 'Paid', 1, '2025-12-13 13:46:41', '2025-12-13 13:46:41'),
(3, 'FLEX-20251219-000003', 2, 1, 35.00, 0.00, 35.00, 35.00, 0.00, NULL, 'Paid', 1, '2025-12-19 13:43:58', '2025-12-19 13:43:58'),
(7, 'FLEX-20251219-000004', 2, 1, 2.00, 0.00, 2.00, 2.00, 0.00, NULL, 'Paid', 1, '2025-12-19 15:21:33', '2025-12-19 15:21:33'),
(8, 'FLEX-20251220-000008', 2, 1, 71.00, 0.00, 71.00, 71.00, 0.00, NULL, 'Paid', 1, '2025-12-20 06:36:29', '2025-12-20 06:36:29');

-- --------------------------------------------------------

--
-- Table structure for table `fee_invoice_items`
--

CREATE TABLE `fee_invoice_items` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `fee_type_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_invoice_items`
--

INSERT INTO `fee_invoice_items` (`id`, `invoice_id`, `fee_type_id`, `amount`, `description`) VALUES
(1, 1, 2, 25.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `fee_payments`
--

CREATE TABLE `fee_payments` (
  `id` int(11) NOT NULL,
  `receipt_no` varchar(50) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('Cash','Bank Transfer','Credit Card','Debit Card','Online','EVC','Zaad','Mobile Money') NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `payment_date` date NOT NULL,
  `remarks` text DEFAULT NULL,
  `received_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_payments`
--

INSERT INTO `fee_payments` (`id`, `receipt_no`, `invoice_id`, `student_id`, `amount`, `payment_method`, `transaction_id`, `payment_date`, `remarks`, `received_by`, `created_at`) VALUES
(1, 'RCT000001', 1, 1, 25.00, 'EVC', '', '2025-11-22', '', 1, '2025-11-22 17:26:33'),
(2, 'RCT000002', 2, 1, 60.00, '', '', '2025-12-13', '', 1, '2025-12-13 13:46:41'),
(3, 'RCT000003', 3, 2, 35.00, '', '', '2025-12-19', '', 1, '2025-12-19 13:43:58'),
(7, 'RCT000004', 7, 2, 2.00, '', '', '2025-12-19', '', 1, '2025-12-19 15:21:33'),
(8, 'RCT000008', 8, 2, 71.00, '', '', '2025-12-20', '', 1, '2025-12-20 06:36:29');

-- --------------------------------------------------------

--
-- Table structure for table `fee_structures`
--

CREATE TABLE `fee_structures` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `fee_type_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `due_date` date DEFAULT NULL,
  `frequency` enum('One Time','Monthly','Quarterly','Annually') DEFAULT 'Monthly',
  `is_mandatory` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_structures`
--

INSERT INTO `fee_structures` (`id`, `class_id`, `fee_type_id`, `session_id`, `amount`, `due_date`, `frequency`, `is_mandatory`) VALUES
(1, 1, 2, 1, 25.00, '2025-11-30', 'One Time', 1),
(3, 1, 1, 1, 60.00, '2025-12-31', 'Monthly', 1);

-- --------------------------------------------------------

--
-- Table structure for table `fee_types`
--

CREATE TABLE `fee_types` (
  `id` int(11) NOT NULL,
  `fee_name` varchar(100) NOT NULL,
  `fee_code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_types`
--

INSERT INTO `fee_types` (`id`, `fee_name`, `fee_code`, `description`) VALUES
(1, 'Tuition Fee', 'TUITION', NULL),
(2, 'Admission Fee', 'ADMISSION', NULL),
(3, 'Exam Fee', 'EXAM', NULL),
(4, 'Library Fee', 'LIBRARY', NULL),
(5, 'Transport Fee', 'TRANSPORT', NULL),
(6, 'Hostel Fee', 'HOSTEL', NULL),
(7, 'Sports Fee', 'SPORTS', NULL),
(8, 'Lab Fee', 'LAB', NULL),
(9, 'Fine', 'FINE', NULL),
(10, 'Other', 'OTHER', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `grade_scale`
--

CREATE TABLE `grade_scale` (
  `id` int(11) NOT NULL,
  `grade_name` varchar(10) NOT NULL,
  `min_percentage` decimal(5,2) NOT NULL,
  `max_percentage` decimal(5,2) NOT NULL,
  `grade_point` decimal(3,2) NOT NULL,
  `description` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `grade_scale`
--

INSERT INTO `grade_scale` (`id`, `grade_name`, `min_percentage`, `max_percentage`, `grade_point`, `description`) VALUES
(1, 'A+', 90.00, 100.00, 4.00, 'Outstanding'),
(2, 'A', 80.00, 89.99, 3.70, 'Excellent'),
(3, 'B+', 75.00, 79.99, 3.30, 'Very Good'),
(4, 'B', 70.00, 74.99, 3.00, 'Good'),
(5, 'C+', 65.00, 69.99, 2.70, 'Above Average'),
(6, 'C', 60.00, 64.99, 2.30, 'Average'),
(7, 'D', 50.00, 59.99, 2.00, 'Below Average'),
(8, 'F', 0.00, 49.99, 0.00, 'Fail');

-- --------------------------------------------------------

--
-- Table structure for table `grading_scale_items`
--

CREATE TABLE `grading_scale_items` (
  `id` int(11) NOT NULL,
  `grading_scheme_id` int(11) NOT NULL,
  `grade_letter` varchar(10) NOT NULL,
  `min_percentage` decimal(5,2) NOT NULL,
  `max_percentage` decimal(5,2) NOT NULL,
  `grade_point` decimal(3,2) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `grading_scale_items`
--

INSERT INTO `grading_scale_items` (`id`, `grading_scheme_id`, `grade_letter`, `min_percentage`, `max_percentage`, `grade_point`, `description`, `display_order`, `created_at`) VALUES
(16, 1, 'A+', 90.00, 100.00, 4.00, 'Outstanding', 0, '2025-12-17 12:53:30'),
(17, 1, 'A', 80.00, 89.00, 3.50, 'Excellent', 1, '2025-12-17 12:53:30'),
(18, 1, 'B', 70.00, 79.00, 3.00, 'Very Good', 2, '2025-12-17 12:53:30'),
(19, 1, 'C', 60.00, 69.00, 2.00, 'Good', 3, '2025-12-17 12:53:30'),
(20, 1, 'D', 0.00, 59.00, 0.00, 'Fail', 4, '2025-12-17 12:53:30');

-- --------------------------------------------------------

--
-- Table structure for table `grading_schemes`
--

CREATE TABLE `grading_schemes` (
  `id` int(11) NOT NULL,
  `scheme_name` varchar(100) NOT NULL,
  `scale_type` varchar(20) NOT NULL DEFAULT 'percentage',
  `max_gpa` decimal(3,2) NOT NULL DEFAULT 4.00,
  `passing_percentage` decimal(5,2) NOT NULL DEFAULT 50.00,
  `description` text DEFAULT NULL,
  `session_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `grading_schemes`
--

INSERT INTO `grading_schemes` (`id`, `scheme_name`, `scale_type`, `max_gpa`, `passing_percentage`, `description`, `session_id`, `class_id`, `branch_id`, `is_default`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Standard 4.0 Scale', 'gpa', 4.00, 50.00, '0', 1, NULL, NULL, 1, 1, 1, '2025-12-17 10:22:26', '2025-12-17 12:54:46');

-- --------------------------------------------------------

--
-- Table structure for table `grading_scheme_items`
--

CREATE TABLE `grading_scheme_items` (
  `id` int(11) NOT NULL,
  `scheme_id` int(11) NOT NULL,
  `grade_name` varchar(10) NOT NULL,
  `min_percentage` decimal(5,2) NOT NULL,
  `max_percentage` decimal(5,2) NOT NULL,
  `grade_point` decimal(3,2) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hostels`
--

CREATE TABLE `hostels` (
  `id` int(11) NOT NULL,
  `hostel_name` varchar(255) NOT NULL,
  `hostel_type` enum('Boys','Girls','Mixed') NOT NULL,
  `address` text DEFAULT NULL,
  `warden_name` varchar(255) DEFAULT NULL,
  `warden_phone` varchar(50) DEFAULT NULL,
  `branch_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hostels`
--

INSERT INTO `hostels` (`id`, `hostel_name`, `hostel_type`, `address`, `warden_name`, `warden_phone`, `branch_id`, `created_at`) VALUES
(1, 'test', 'Boys', 'sad', '', '', 1, '2025-11-22 18:22:07');

-- --------------------------------------------------------

--
-- Table structure for table `hostel_allocations`
--

CREATE TABLE `hostel_allocations` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `hostel_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `bed_number` varchar(10) DEFAULT NULL,
  `allocation_date` date NOT NULL,
  `vacation_date` date DEFAULT NULL,
  `status` enum('Active','Vacated') DEFAULT 'Active',
  `fee_amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hostel_rooms`
--

CREATE TABLE `hostel_rooms` (
  `id` int(11) NOT NULL,
  `hostel_id` int(11) NOT NULL,
  `room_number` varchar(50) NOT NULL,
  `room_type` varchar(50) DEFAULT NULL,
  `capacity` int(11) DEFAULT 2,
  `occupied` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `income`
--

CREATE TABLE `income` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `income_category` varchar(100) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `income_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `income`
--

INSERT INTO `income` (`id`, `branch_id`, `income_category`, `amount`, `income_date`, `description`, `payment_method`, `reference_no`, `recorded_by`, `created_at`) VALUES
(1, 1, 'Tuition Fees', 2.00, '2025-12-19', 'Flexible fee payment - Receipt: RCT000004 - Student: Abdulkadir Uukow (ID: STU000002) - Allocated to 1 fee assignment(s)', 'Cash', 'PAY-RCT000004', 1, '2025-12-19 15:21:33'),
(2, 1, 'Tuition Fees', 71.00, '2025-12-20', 'Flexible fee payment - Receipt: RCT000008 - Student: Abdulkadir Uukow (ID: STU000002) - Allocated to 2 fee assignment(s)', 'EVC', 'PAY-RCT000008', 1, '2025-12-20 06:36:29');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_categories`
--

CREATE TABLE `inventory_categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_code` varchar(50) NOT NULL,
  `category_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 0,
  `unit` varchar(50) DEFAULT NULL,
  `min_stock_level` int(11) DEFAULT 10,
  `price_per_unit` decimal(10,2) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `journal_entries`
--

CREATE TABLE `journal_entries` (
  `id` int(11) NOT NULL,
  `entry_date` date NOT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `journal_entry_details`
--

CREATE TABLE `journal_entry_details` (
  `id` int(11) NOT NULL,
  `journal_entry_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `debit` decimal(15,2) DEFAULT 0.00,
  `credit` decimal(15,2) DEFAULT 0.00,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_applications`
--

CREATE TABLE `leave_applications` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `leave_type_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_days` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('Pending','Approved','Rejected','Cancelled') DEFAULT 'Pending',
  `approved_by` int(11) DEFAULT NULL,
  `approval_date` datetime DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_applications`
--

INSERT INTO `leave_applications` (`id`, `staff_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `approved_by`, `approval_date`, `rejection_reason`, `applied_at`) VALUES
(1, 1, 2, '2025-12-19', '2025-12-21', 3, 'sick', 'Approved', 1, '2025-12-19 17:02:04', NULL, '2025-12-19 13:59:33');

-- --------------------------------------------------------

--
-- Table structure for table `leave_types`
--

CREATE TABLE `leave_types` (
  `id` int(11) NOT NULL,
  `leave_name` varchar(100) NOT NULL,
  `leave_code` varchar(50) NOT NULL,
  `days_allowed` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_types`
--

INSERT INTO `leave_types` (`id`, `leave_name`, `leave_code`, `days_allowed`) VALUES
(1, 'Casual Leave', 'CL', 12),
(2, 'Sick Leave', 'SL', 15),
(3, 'Annual Leave', 'AL', 21),
(4, 'Maternity Leave', 'ML', 90),
(5, 'Paternity Leave', 'PL', 7);

-- --------------------------------------------------------

--
-- Table structure for table `lesson_plans`
--

CREATE TABLE `lesson_plans` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `lesson_title` varchar(255) NOT NULL,
  `lesson_date` date NOT NULL,
  `objectives` text DEFAULT NULL,
  `content` text DEFAULT NULL,
  `methodology` text DEFAULT NULL,
  `resources` text DEFAULT NULL,
  `assessment` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `status` enum('Draft','Published') DEFAULT 'Draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lesson_plans`
--

INSERT INTO `lesson_plans` (`id`, `teacher_id`, `class_id`, `subject_id`, `session_id`, `lesson_title`, `lesson_date`, `objectives`, `content`, `methodology`, `resources`, `assessment`, `file_path`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, 'testing', '2025-11-23', '', '', '', '', '', NULL, 'Published', '2025-11-23 08:11:35', '2025-11-23 08:19:03'),
(2, 1, 1, 1, 1, 'test', '2025-11-23', '', '', '', '', '', NULL, 'Published', '2025-11-23 08:51:27', '2025-11-30 09:26:30'),
(3, 2, 1, 3, 1, 'Basic English', '2025-12-17', '', '', '', '', '', NULL, 'Published', '2025-12-17 07:46:56', '2025-12-17 08:29:27');

-- --------------------------------------------------------

--
-- Table structure for table `library_books`
--

CREATE TABLE `library_books` (
  `id` int(11) NOT NULL,
  `book_title` varchar(255) NOT NULL,
  `book_code` varchar(50) NOT NULL,
  `isbn` varchar(50) DEFAULT NULL,
  `author` varchar(255) NOT NULL,
  `publisher` varchar(255) DEFAULT NULL,
  `publication_year` year(4) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `available_quantity` int(11) DEFAULT 1,
  `price` decimal(10,2) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `library_books`
--

INSERT INTO `library_books` (`id`, `book_title`, `book_code`, `isbn`, `author`, `publisher`, `publication_year`, `category`, `quantity`, `available_quantity`, `price`, `location`, `barcode`, `branch_id`, `added_at`) VALUES
(1, 'tarbiyo', 'tarbiyo', '116546', 'uukow', '', '0000', 'islamic', 5, 5, 25.00, '', NULL, 1, '2025-11-22 16:10:39');

-- --------------------------------------------------------

--
-- Table structure for table `library_issues`
--

CREATE TABLE `library_issues` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `status` enum('Issued','Returned','Overdue','Lost') DEFAULT 'Issued',
  `fine_amount` decimal(10,2) DEFAULT 0.00,
  `fine_paid` decimal(10,2) DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `issued_by` int(11) DEFAULT NULL,
  `returned_to` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `library_issues`
--

INSERT INTO `library_issues` (`id`, `book_id`, `student_id`, `issue_date`, `due_date`, `return_date`, `status`, `fine_amount`, `fine_paid`, `remarks`, `issued_by`, `returned_to`, `created_at`) VALUES
(1, 1, 1, '2025-11-22', '2025-12-06', '2025-11-22', 'Returned', 0.00, 0.00, '', 1, 1, '2025-11-22 18:09:26'),
(2, 1, 1, '2025-11-22', '2025-12-06', '2025-11-22', 'Returned', 0.00, 0.00, '', 1, 1, '2025-11-22 18:11:44'),
(3, 1, 1, '2025-12-03', '2025-12-17', '2025-12-03', 'Returned', 0.00, 0.00, '', 1, 1, '2025-12-03 07:56:34'),
(4, 1, 2, '2025-12-03', '2025-12-17', '2025-12-03', 'Returned', 0.00, 0.00, '', 1, 1, '2025-12-03 07:56:44'),
(5, 1, 1, '2025-12-03', '2025-12-17', '2025-12-03', 'Returned', 0.00, 0.00, '', 1, 1, '2025-12-03 07:57:02');

-- --------------------------------------------------------

--
-- Table structure for table `medical_records`
--

CREATE TABLE `medical_records` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `record_date` date NOT NULL,
  `symptoms` text DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `treatment` text DEFAULT NULL,
  `prescribed_medicines` text DEFAULT NULL,
  `doctor_name` varchar(255) DEFAULT NULL,
  `follow_up_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `parent_message_id` int(11) DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `subject`, `message`, `is_read`, `parent_message_id`, `sent_at`) VALUES
(1, 1, 5, 'waryaa', 'test', 0, NULL, '2025-12-03 08:05:29');

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` int(11) NOT NULL,
  `module_key` varchar(50) NOT NULL COMMENT 'Unique identifier (e.g., students, fees, exams)',
  `module_name` varchar(100) NOT NULL COMMENT 'Display name (e.g., Students Management)',
  `module_description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0 COMMENT 'Order for display in admin interface',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `module_key`, `module_name`, `module_description`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'dashboard', 'Dashboard', 'Main dashboard and analytics', 1, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(2, 'students', 'Students', 'Student information management', 2, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(3, 'admissions', 'Admissions', 'Admission and enrollment management', 3, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(4, 'academics', 'Academics', 'Classes, subjects, timetable, and academic management', 4, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(5, 'attendance', 'Attendance', 'Student and staff attendance tracking', 5, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(6, 'exams', 'Examinations', 'Exam scheduling, marks entry, and results', 6, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(7, 'fees', 'Fees & Finance', 'Fee management, payments, and financial operations', 7, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(8, 'library', 'Library', 'Library and book management', 8, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(9, 'transport', 'Transport', 'Transport and vehicle management', 9, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(10, 'hostel', 'Hostel', 'Hostel and accommodation management', 10, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(11, 'hr', 'HR & Payroll', 'Staff management, payroll, and HR operations', 11, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(12, 'lms', 'Learning Management', 'Study materials, assignments, and quizzes', 12, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(13, 'communication', 'Communication', 'SMS, email, announcements, and messaging', 13, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(14, 'events', 'Events & Calendar', 'Events, calendar, and academic calendar management', 14, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(15, 'certificates', 'Certificates', 'Certificate and document generation', 15, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(16, 'reports', 'Reports & Analytics', 'Reports, analytics, and data exports', 16, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(17, 'settings', 'Settings', 'System settings and configuration', 17, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(18, 'branches', 'Branches', 'Multi-branch management', 18, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16');

-- --------------------------------------------------------

--
-- Table structure for table `monthly_fee_assignments`
--

CREATE TABLE `monthly_fee_assignments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `fee_type_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `month` varchar(20) NOT NULL COMMENT 'Format: YYYY-MM',
  `amount` decimal(10,2) NOT NULL,
  `original_amount` decimal(10,2) DEFAULT NULL COMMENT 'Original fee amount before discount',
  `discount_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Discount amount applied',
  `discount_type` enum('Fixed','Percentage') DEFAULT NULL COMMENT 'Type of discount applied',
  `due_date` date DEFAULT NULL,
  `status` enum('Assigned','Paid','Partially Paid','Overdue','Waived') DEFAULT 'Assigned',
  `assigned_amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) DEFAULT 0.00,
  `due_amount` decimal(10,2) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL COMMENT 'Reference to fee_invoices if invoice generated',
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `monthly_fee_assignments`
--

INSERT INTO `monthly_fee_assignments` (`id`, `student_id`, `class_id`, `fee_type_id`, `session_id`, `month`, `amount`, `original_amount`, `discount_amount`, `discount_type`, `due_date`, `status`, `assigned_amount`, `paid_amount`, `due_amount`, `invoice_id`, `assigned_by`, `assigned_at`, `updated_at`) VALUES
(13, 1, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Paid', 60.00, 60.00, 0.00, NULL, 1, '2025-12-13 13:39:11', '2025-12-20 06:06:37'),
(14, 2, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Paid', 60.00, 60.00, 0.00, NULL, 1, '2025-12-13 13:39:11', '2025-12-20 06:36:29'),
(15, 3, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-13 13:39:11', '2025-12-20 06:06:37'),
(16, 610, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:43', '2025-12-20 06:06:37'),
(17, 611, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(18, 612, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(19, 613, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(20, 614, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(21, 615, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(22, 616, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(23, 617, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(24, 618, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(25, 619, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(26, 620, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(27, 621, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(28, 622, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(29, 623, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(30, 624, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(31, 625, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(32, 626, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(33, 627, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(34, 628, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(35, 629, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(36, 630, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(37, 631, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(38, 632, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(39, 633, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(40, 634, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(41, 635, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(42, 636, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(43, 637, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(44, 638, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(45, 639, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(46, 640, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(47, 641, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(48, 642, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(49, 643, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(50, 644, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(51, 645, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(52, 646, 1, 1, 1, '2025-12', 60.00, 60.00, 0.00, NULL, '2025-12-31', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-19 13:40:44', '2025-12-20 06:06:37'),
(53, 1, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(54, 2, 1, 1, 1, '2025-11', 48.00, 60.00, 12.00, 'Fixed', '0000-00-00', 'Paid', 48.00, 48.00, 0.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:36:29'),
(55, 3, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(56, 610, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(57, 611, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(58, 612, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(59, 613, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(60, 614, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(61, 615, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(62, 616, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(63, 617, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(64, 618, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(65, 619, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(66, 620, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(67, 621, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(68, 622, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(69, 623, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(70, 624, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(71, 625, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(72, 626, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(73, 627, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(74, 628, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(75, 629, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(76, 630, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(77, 631, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(78, 632, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(79, 633, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(80, 634, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(81, 635, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(82, 636, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(83, 637, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(84, 638, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(85, 639, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(86, 640, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(87, 641, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(88, 642, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(89, 643, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(90, 644, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(91, 645, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27'),
(92, 646, 1, 1, 1, '2025-11', 60.00, 60.00, 0.00, NULL, '0000-00-00', 'Assigned', 60.00, 0.00, 60.00, NULL, 1, '2025-12-20 06:22:27', '2025-12-20 06:22:27');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `notification_type` varchar(50) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parents`
--

CREATE TABLE `parents` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(50) NOT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `annual_income` decimal(15,2) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `parents`
--

INSERT INTO `parents` (`id`, `user_id`, `first_name`, `last_name`, `email`, `phone`, `occupation`, `annual_income`, `address`, `photo`, `created_at`) VALUES
(1, NULL, 'Abdulkadir Uukow', '', '', '615031623', NULL, NULL, '', NULL, '2025-11-22 17:23:59'),
(2, NULL, 'Abdulkadir Uukow', '', '', '615031623', NULL, NULL, '', NULL, '2025-11-30 08:17:49'),
(3, NULL, 'Ahmed Hassan', '', '', '615714745544', NULL, NULL, 'Korontada', NULL, '2025-12-14 07:07:04'),
(4, NULL, 'Yusuf Osman', '', '', '61414555552', NULL, NULL, '', NULL, '2025-12-14 07:28:47'),
(5, NULL, 'Hassan', 'Ali', '', '61542558524', NULL, NULL, '', NULL, '2025-12-14 07:31:19'),
(6, NULL, 'Ibrahim', 'Ali', '', '615214', NULL, NULL, '', NULL, '2025-12-14 07:34:10');

-- --------------------------------------------------------

--
-- Table structure for table `payment_allocations`
--

CREATE TABLE `payment_allocations` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `allocation_type` enum('Monthly Assignment','Advance Credit','Invoice') NOT NULL,
  `reference_id` int(11) NOT NULL COMMENT 'ID of monthly_assignment, advance_credit, or invoice',
  `amount` decimal(10,2) NOT NULL,
  `allocated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_allocations`
--

INSERT INTO `payment_allocations` (`id`, `payment_id`, `allocation_type`, `reference_id`, `amount`, `allocated_at`) VALUES
(1, 2, 'Monthly Assignment', 13, 60.00, '2025-12-13 13:46:41'),
(2, 3, 'Monthly Assignment', 14, 35.00, '2025-12-19 13:43:58'),
(6, 7, 'Monthly Assignment', 14, 2.00, '2025-12-19 15:21:33'),
(7, 8, 'Monthly Assignment', 54, 48.00, '2025-12-20 06:36:29'),
(8, 8, 'Monthly Assignment', 14, 23.00, '2025-12-20 06:36:29');

-- --------------------------------------------------------

--
-- Table structure for table `payroll_structures`
--

CREATE TABLE `payroll_structures` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `basic_salary` decimal(15,2) NOT NULL,
  `house_allowance` decimal(15,2) DEFAULT 0.00,
  `transport_allowance` decimal(15,2) DEFAULT 0.00,
  `medical_allowance` decimal(15,2) DEFAULT 0.00,
  `other_allowances` decimal(15,2) DEFAULT 0.00,
  `tax_deduction` decimal(15,2) DEFAULT 0.00,
  `other_deductions` decimal(15,2) DEFAULT 0.00,
  `effective_from` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payroll_structures`
--

INSERT INTO `payroll_structures` (`id`, `staff_id`, `basic_salary`, `house_allowance`, `transport_allowance`, `medical_allowance`, `other_allowances`, `tax_deduction`, `other_deductions`, `effective_from`, `created_at`) VALUES
(1, 1, 250.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '2025-11-22', '2025-12-19 14:57:58');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `permission_name` varchar(100) NOT NULL,
  `permission_key` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permission_audit_log`
--

CREATE TABLE `permission_audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'User who made the change',
  `target_type` enum('role','user') NOT NULL COMMENT 'What was changed: role or user',
  `target_id` int(11) NOT NULL COMMENT 'ID of role or user that was modified',
  `module_id` int(11) DEFAULT NULL,
  `action_id` int(11) DEFAULT NULL,
  `change_type` enum('grant','revoke','override_grant','override_revoke','override_remove') NOT NULL,
  `old_value` text DEFAULT NULL COMMENT 'Previous permission state (JSON)',
  `new_value` text DEFAULT NULL COMMENT 'New permission state (JSON)',
  `description` text DEFAULT NULL COMMENT 'Human-readable description of change',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` int(11) NOT NULL,
  `order_no` varchar(50) NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `order_date` date NOT NULL,
  `expected_delivery` date DEFAULT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `status` enum('Pending','Approved','Received','Cancelled') DEFAULT 'Pending',
  `branch_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `total_marks` decimal(10,2) DEFAULT 100.00,
  `passing_marks` decimal(10,2) DEFAULT 40.00,
  `duration_minutes` int(11) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `marks_obtained` decimal(10,2) DEFAULT NULL,
  `status` enum('In Progress','Submitted','Graded') DEFAULT 'In Progress'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `question_type` enum('Multiple Choice','True/False','Short Answer','Essay') DEFAULT 'Multiple Choice',
  `options` text DEFAULT NULL,
  `correct_answer` text DEFAULT NULL,
  `marks` decimal(10,2) DEFAULT 1.00,
  `order_no` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_cards`
--

CREATE TABLE `report_cards` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `total_marks` decimal(10,2) DEFAULT NULL,
  `obtained_marks` decimal(10,2) DEFAULT NULL,
  `percentage` decimal(5,2) DEFAULT NULL,
  `gpa` decimal(3,2) DEFAULT NULL,
  `grade` varchar(10) DEFAULT NULL,
  `rank` int(11) DEFAULT NULL,
  `attendance_percentage` decimal(5,2) DEFAULT NULL,
  `teacher_remarks` text DEFAULT NULL,
  `principal_remarks` text DEFAULT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `pdf_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `role_description` text DEFAULT NULL,
  `is_system_role` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `role_description`, `is_system_role`, `created_at`) VALUES
(1, 'Super Admin', 'Full system access', 1, '2025-11-13 07:25:21'),
(2, 'Admin', 'Branch administrator', 1, '2025-11-13 07:25:21'),
(3, 'Teacher', 'Teaching staff', 1, '2025-11-13 07:25:21'),
(4, 'Student', 'Student user', 1, '2025-11-13 07:25:21'),
(5, 'Parent', 'Parent/Guardian', 1, '2025-11-13 07:25:21'),
(6, 'Accountant', 'Finance management', 1, '2025-11-13 07:25:21'),
(7, 'Librarian', 'Library management', 1, '2025-11-13 07:25:21'),
(8, 'Receptionist', 'Front desk operations', 1, '2025-11-13 07:25:21');

-- --------------------------------------------------------

--
-- Table structure for table `role_action_permissions`
--

CREATE TABLE `role_action_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `action_id` int(11) NOT NULL,
  `granted` tinyint(1) DEFAULT 1 COMMENT '1 = granted, 0 = denied (for explicit denial)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_action_permissions`
--

INSERT INTO `role_action_permissions` (`id`, `role_id`, `module_id`, `action_id`, `granted`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(2, 1, 1, 2, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(3, 1, 1, 3, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(4, 1, 1, 4, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(5, 1, 1, 5, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(6, 1, 1, 6, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(7, 1, 1, 7, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(8, 1, 1, 8, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(9, 1, 1, 9, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(10, 1, 1, 10, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(11, 1, 2, 1, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(12, 1, 2, 2, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(13, 1, 2, 3, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(14, 1, 2, 4, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(15, 1, 2, 5, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(16, 1, 2, 6, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(17, 1, 2, 7, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(18, 1, 2, 8, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(19, 1, 2, 9, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(20, 1, 2, 10, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(21, 1, 3, 1, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(22, 1, 3, 2, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(23, 1, 3, 3, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(24, 1, 3, 4, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(25, 1, 3, 5, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(26, 1, 3, 6, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(27, 1, 3, 7, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(28, 1, 3, 8, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(29, 1, 3, 9, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(30, 1, 3, 10, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(31, 1, 4, 1, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(32, 1, 4, 2, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(33, 1, 4, 3, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(34, 1, 4, 4, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(35, 1, 4, 5, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(36, 1, 4, 6, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(37, 1, 4, 7, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(38, 1, 4, 8, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(39, 1, 4, 9, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(40, 1, 4, 10, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(41, 1, 5, 1, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(42, 1, 5, 2, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(43, 1, 5, 3, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(44, 1, 5, 4, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(45, 1, 5, 5, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(46, 1, 5, 6, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(47, 1, 5, 7, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(48, 1, 5, 8, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(49, 1, 5, 9, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(50, 1, 5, 10, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(51, 1, 6, 1, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(52, 1, 6, 2, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(53, 1, 6, 3, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(54, 1, 6, 4, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(55, 1, 6, 5, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(56, 1, 6, 6, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(57, 1, 6, 7, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(58, 1, 6, 8, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(59, 1, 6, 9, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(60, 1, 6, 10, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(61, 1, 7, 1, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(62, 1, 7, 2, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(63, 1, 7, 3, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(64, 1, 7, 4, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(65, 1, 7, 5, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(66, 1, 7, 6, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(67, 1, 7, 7, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(68, 1, 7, 8, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(69, 1, 7, 9, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(70, 1, 7, 10, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(71, 1, 8, 1, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(72, 1, 8, 2, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(73, 1, 8, 3, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(74, 1, 8, 4, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(75, 1, 8, 5, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(76, 1, 8, 6, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(77, 1, 8, 7, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(78, 1, 8, 8, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(79, 1, 8, 9, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(80, 1, 8, 10, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(81, 1, 9, 1, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(82, 1, 9, 2, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(83, 1, 9, 3, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(84, 1, 9, 4, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(85, 1, 9, 5, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(86, 1, 9, 6, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(87, 1, 9, 7, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(88, 1, 9, 8, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(89, 1, 9, 9, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(90, 1, 9, 10, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(91, 1, 10, 1, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(92, 1, 10, 2, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(93, 1, 10, 3, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(94, 1, 10, 4, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(95, 1, 10, 5, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(96, 1, 10, 6, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(97, 1, 10, 7, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(98, 1, 10, 8, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(99, 1, 10, 9, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(100, 1, 10, 10, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(101, 1, 11, 1, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(102, 1, 11, 2, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(103, 1, 11, 3, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(104, 1, 11, 4, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(105, 1, 11, 5, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(106, 1, 11, 6, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(107, 1, 11, 7, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(108, 1, 11, 8, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(109, 1, 11, 9, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(110, 1, 11, 10, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(111, 1, 12, 1, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(112, 1, 12, 2, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(113, 1, 12, 3, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(114, 1, 12, 4, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(115, 1, 12, 5, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(116, 1, 12, 6, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(117, 1, 12, 7, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(118, 1, 12, 8, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(119, 1, 12, 9, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(120, 1, 12, 10, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(121, 1, 13, 1, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(122, 1, 13, 2, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(123, 1, 13, 3, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(124, 1, 13, 4, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(125, 1, 13, 5, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(126, 1, 13, 6, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(127, 1, 13, 7, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(128, 1, 13, 8, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(129, 1, 13, 9, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(130, 1, 13, 10, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(131, 1, 14, 1, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(132, 1, 14, 2, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(133, 1, 14, 3, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(134, 1, 14, 4, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(135, 1, 14, 5, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(136, 1, 14, 6, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(137, 1, 14, 7, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(138, 1, 14, 8, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(139, 1, 14, 9, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(140, 1, 14, 10, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(141, 1, 15, 1, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(142, 1, 15, 2, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(143, 1, 15, 3, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(144, 1, 15, 4, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(145, 1, 15, 5, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(146, 1, 15, 6, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(147, 1, 15, 7, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(148, 1, 15, 8, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(149, 1, 15, 9, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(150, 1, 15, 10, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(151, 1, 16, 1, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(152, 1, 16, 2, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(153, 1, 16, 3, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(154, 1, 16, 4, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(155, 1, 16, 5, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(156, 1, 16, 6, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(157, 1, 16, 7, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(158, 1, 16, 8, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(159, 1, 16, 9, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(160, 1, 16, 10, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(161, 1, 17, 1, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(162, 1, 17, 2, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(163, 1, 17, 3, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(164, 1, 17, 4, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(165, 1, 17, 5, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(166, 1, 17, 6, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(167, 1, 17, 7, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(168, 1, 17, 8, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(169, 1, 17, 9, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(170, 1, 17, 10, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(171, 1, 18, 1, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(172, 1, 18, 2, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(173, 1, 18, 3, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(174, 1, 18, 4, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(175, 1, 18, 5, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(176, 1, 18, 6, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(177, 1, 18, 7, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(178, 1, 18, 8, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(179, 1, 18, 9, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16'),
(180, 1, 18, 10, 1, '2025-12-21 06:15:16', '2025-12-21 06:15:16');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salary_payments`
--

CREATE TABLE `salary_payments` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `payment_month` date NOT NULL,
  `basic_salary` decimal(15,2) NOT NULL,
  `allowances` decimal(15,2) DEFAULT 0.00,
  `deductions` decimal(15,2) DEFAULT 0.00,
  `net_salary` decimal(15,2) NOT NULL,
  `payment_date` date DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `payslip_path` varchar(255) DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `salary_payments`
--

INSERT INTO `salary_payments` (`id`, `staff_id`, `payment_month`, `basic_salary`, `allowances`, `deductions`, `net_salary`, `payment_date`, `payment_method`, `remarks`, `payslip_path`, `processed_by`, `created_at`) VALUES
(5, 1, '2025-12-01', 250.00, 0.00, 0.00, 250.00, '2025-12-19', 'Mobile Money', '', NULL, 1, '2025-12-19 15:01:38');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` int(11) NOT NULL,
  `section_name` varchar(50) NOT NULL,
  `class_id` int(11) NOT NULL,
  `capacity` int(11) DEFAULT 40,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `section_name`, `class_id`, `capacity`, `is_active`, `created_at`) VALUES
(1, 'CA202', 1, 40, 1, '2025-11-22 15:54:11'),
(2, 'A', 2, 40, 1, '2025-12-14 07:03:04'),
(3, 'B', 2, 40, 1, '2025-12-14 07:03:08'),
(4, 'C', 2, 40, 1, '2025-12-14 07:03:13'),
(5, 'A', 3, 40, 1, '2025-12-14 07:03:24'),
(6, 'B', 3, 40, 1, '2025-12-14 07:03:31'),
(7, 'C', 3, 40, 1, '2025-12-14 07:03:35'),
(8, 'A', 4, 40, 1, '2025-12-14 07:03:42'),
(9, 'B', 4, 40, 1, '2025-12-14 07:03:46'),
(10, 'C', 4, 40, 1, '2025-12-14 07:03:50'),
(11, 'A', 5, 40, 1, '2025-12-14 07:03:58'),
(12, 'B', 5, 40, 1, '2025-12-14 07:04:02'),
(13, 'C', 5, 40, 1, '2025-12-14 07:04:08'),
(14, 'D', 2, 40, 1, '2025-12-14 07:04:28'),
(15, 'Grad', 6, 5, 1, '2025-12-20 07:03:40');

-- --------------------------------------------------------

--
-- Table structure for table `settings_audit_log`
--

CREATE TABLE `settings_audit_log` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `staff_id` varchar(50) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `date_of_birth` date NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(50) NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `designation` varchar(100) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `qualification` varchar(255) DEFAULT NULL,
  `experience_years` int(11) DEFAULT NULL,
  `joining_date` date NOT NULL,
  `leaving_date` date DEFAULT NULL,
  `employment_type` enum('Full Time','Part Time','Contract','Temporary') DEFAULT 'Full Time',
  `status` enum('Active','Inactive','Resigned','Terminated') DEFAULT 'Active',
  `bank_account_no` varchar(100) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `emergency_contact` varchar(255) DEFAULT NULL,
  `emergency_phone` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `user_id`, `staff_id`, `branch_id`, `first_name`, `last_name`, `gender`, `date_of_birth`, `email`, `phone`, `address`, `city`, `state`, `postal_code`, `photo`, `designation`, `department`, `qualification`, `experience_years`, `joining_date`, `leaving_date`, `employment_type`, `status`, `bank_account_no`, `bank_name`, `emergency_contact`, `emergency_phone`, `created_at`, `updated_at`) VALUES
(1, 2, 'STF000001', 1, 'Abdulkadir', 'Uukow', 'Male', '2011-02-23', '', '615031623', '', '', '', '', NULL, 'Senior Teacher', '', '0', 0, '2025-11-22', '0000-00-00', 'Full Time', 'Active', '', '', '', '', '2025-11-22 19:48:21', '2025-12-14 08:50:30'),
(2, 3, 'STF000002', 1, 'Hagad', 'Xiis', 'Male', '2002-01-30', 'abdihagad@gmail.com', '615254445', NULL, NULL, NULL, NULL, NULL, 'Teacher', 'Academic', '0', 0, '2025-11-30', NULL, 'Full Time', 'Active', NULL, NULL, NULL, NULL, '2025-11-30 09:35:16', '2025-11-30 09:35:31'),
(3, NULL, 'STF000003', 2, 'Sarah', 'Johnson', 'Female', '1985-03-15', 'sarah.johnson@school.edu', '+1-555-0101', '', '', '', '', NULL, 'Senior Teacher', 'Mathematics', '0', 8, '2020-01-15', '0000-00-00', 'Full Time', 'Active', '', '', '', '', '2025-12-14 07:48:34', '2025-12-19 17:19:04'),
(4, NULL, 'STF000004', 1, 'Michael', 'Chen', 'Male', '1988-07-22', 'michael.chen@school.edu', '+1-555-0102', NULL, NULL, NULL, NULL, NULL, 'Teacher', 'Science', '0', 5, '2021-08-01', NULL, 'Full Time', 'Active', NULL, NULL, NULL, NULL, '2025-12-14 07:48:34', '2025-12-14 07:48:34'),
(5, NULL, 'STF000005', 1, 'Emily', 'Rodriguez', 'Female', '1990-11-08', 'emily.rodriguez@school.edu', '+1-555-0103', NULL, NULL, NULL, NULL, NULL, 'Teacher', 'English', '0', 4, '2022-02-10', NULL, 'Full Time', 'Active', NULL, NULL, NULL, NULL, '2025-12-14 07:48:35', '2025-12-14 07:48:35'),
(6, NULL, 'STF000006', 1, 'David', 'Williams', 'Male', '1987-05-30', 'david.williams@school.edu', '+1-555-0104', NULL, NULL, NULL, NULL, NULL, 'Senior Teacher', 'History', '0', 7, '2020-09-01', NULL, 'Full Time', 'Active', NULL, NULL, NULL, NULL, '2025-12-14 07:48:35', '2025-12-14 07:48:35'),
(7, NULL, 'STF000007', 1, 'Jessica', 'Brown', 'Female', '1992-01-18', 'jessica.brown@school.edu', '+1-555-0105', NULL, NULL, NULL, NULL, NULL, 'Teacher', 'Computer Science', '0', 3, '2023-01-15', NULL, 'Full Time', 'Active', NULL, NULL, NULL, NULL, '2025-12-14 07:48:35', '2025-12-14 07:48:35'),
(8, NULL, 'STF000008', 1, 'Robert', 'Taylor', 'Male', '1986-09-25', 'robert.taylor@school.edu', '+1-555-0106', NULL, NULL, NULL, NULL, NULL, 'Teacher', 'Physical Education', '0', 6, '2021-03-01', NULL, 'Full Time', 'Active', NULL, NULL, NULL, NULL, '2025-12-14 07:48:35', '2025-12-14 07:48:35'),
(9, NULL, 'STF000009', 1, 'Amanda', 'Martinez', 'Female', '1989-12-12', 'amanda.martinez@school.edu', '+1-555-0107', NULL, NULL, NULL, NULL, NULL, 'Teacher', 'Art', '0', 5, '2021-08-15', NULL, 'Full Time', 'Active', NULL, NULL, NULL, NULL, '2025-12-14 07:48:35', '2025-12-14 07:48:35'),
(10, NULL, 'STF000010', 1, 'James', 'Anderson', 'Male', '1984-06-20', 'james.anderson@school.edu', '+1-555-0108', NULL, NULL, NULL, NULL, NULL, 'Senior Teacher', 'Chemistry', '0', 10, '2019-01-10', NULL, 'Full Time', 'Active', NULL, NULL, NULL, NULL, '2025-12-14 07:48:35', '2025-12-14 07:48:35'),
(11, NULL, 'STF000011', 1, 'Lisa', 'Wilson', 'Female', '1991-04-05', 'lisa.wilson@school.edu', '+1-555-0109', NULL, NULL, NULL, NULL, NULL, 'Teacher', 'Biology', '0', 4, '2022-09-01', NULL, 'Full Time', 'Active', NULL, NULL, NULL, NULL, '2025-12-14 07:48:35', '2025-12-14 07:48:35'),
(12, NULL, 'STF000012', 1, 'Christopher', 'Moore', 'Male', '1988-08-14', 'christopher.moore@school.edu', '+1-555-0110', NULL, NULL, NULL, NULL, NULL, 'Teacher', 'Geography', '0', 5, '2021-07-01', NULL, 'Full Time', 'Active', NULL, NULL, NULL, NULL, '2025-12-14 07:48:35', '2025-12-14 07:48:35');

-- --------------------------------------------------------

--
-- Table structure for table `staff_attendance`
--

CREATE TABLE `staff_attendance` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `status` enum('Present','Absent','Late','Half Day','Leave') NOT NULL,
  `remarks` text DEFAULT NULL,
  `marked_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff_attendance`
--

INSERT INTO `staff_attendance` (`id`, `staff_id`, `attendance_date`, `check_in`, `check_out`, `status`, `remarks`, `marked_by`, `created_at`) VALUES
(1, 1, '2025-12-19', '08:03:00', '17:04:00', 'Present', '', 1, '2025-12-19 14:04:21'),
(2, 9, '2025-12-19', '00:00:00', '00:00:00', 'Absent', '', 1, '2025-12-19 14:04:21'),
(3, 12, '2025-12-19', '00:00:00', '00:00:00', 'Present', '', 1, '2025-12-19 14:04:21'),
(4, 6, '2025-12-19', '00:00:00', '00:00:00', 'Present', '', 1, '2025-12-19 14:04:21'),
(5, 5, '2025-12-19', '00:00:00', '00:00:00', 'Present', '', 1, '2025-12-19 14:04:21'),
(6, 2, '2025-12-19', '00:00:00', '00:00:00', 'Present', '', 1, '2025-12-19 14:04:21'),
(7, 10, '2025-12-19', '00:00:00', '00:00:00', 'Present', '', 1, '2025-12-19 14:04:21'),
(8, 7, '2025-12-19', '00:00:00', '00:00:00', 'Present', '', 1, '2025-12-19 14:04:21'),
(9, 11, '2025-12-19', '00:00:00', '00:00:00', 'Present', '', 1, '2025-12-19 14:04:21'),
(10, 4, '2025-12-19', '00:00:00', '00:00:00', 'Present', '', 1, '2025-12-19 14:04:21'),
(11, 8, '2025-12-19', '00:00:00', '00:00:00', 'Present', '', 1, '2025-12-19 14:04:21'),
(12, 3, '2025-12-19', '00:00:00', '00:00:00', 'Present', '', 1, '2025-12-19 14:04:21');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `student_id` varchar(50) NOT NULL,
  `admission_no` varchar(50) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `date_of_birth` date NOT NULL,
  `blood_group` varchar(10) DEFAULT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `admission_date` date NOT NULL,
  `current_class_id` int(11) DEFAULT NULL,
  `current_section_id` int(11) DEFAULT NULL,
  `status` enum('Active','Inactive','Graduated','Transferred','Expelled','Suspended') DEFAULT 'Active',
  `is_hostel` tinyint(1) DEFAULT 0,
  `is_transport` tinyint(1) DEFAULT 0,
  `previous_school` varchar(255) DEFAULT NULL,
  `medical_conditions` text DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `special_needs` text DEFAULT NULL,
  `discount_type` enum('Fixed','Percentage') DEFAULT NULL,
  `discount_value` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `student_id`, `admission_no`, `branch_id`, `first_name`, `last_name`, `middle_name`, `gender`, `date_of_birth`, `blood_group`, `religion`, `nationality`, `email`, `phone`, `address`, `city`, `state`, `postal_code`, `photo`, `barcode`, `qr_code`, `admission_date`, `current_class_id`, `current_section_id`, `status`, `is_hostel`, `is_transport`, `previous_school`, `medical_conditions`, `allergies`, `special_needs`, `discount_type`, `discount_value`, `created_at`, `updated_at`) VALUES
(1, 5, 'STU000001', 'ADM20250001', 1, 'Abdulkadir', 'Ibrahim', 'Abuukar', 'Male', '2005-06-23', 'O+', 'Somalia', 'Somali', '', '3222', '', '', '', '', '0', NULL, NULL, '0000-00-00', 1, 1, 'Active', 0, 0, '0', '', '', '', '', NULL, '2025-11-22 15:56:12', '2025-12-21 06:49:57'),
(2, 6, 'STU000002', 'ADM20250002', 1, 'Abdulkadir', 'Uukow', '', 'Male', '2002-06-04', '', '', '', 'info@uukowtech.com', '', '', '', '', '', NULL, NULL, NULL, '2025-11-22', 6, 15, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, 'Fixed', 12.00, '2025-11-22 17:23:59', '2025-12-20 07:05:26'),
(3, NULL, 'STU000003', 'ADM20250003', 1, 'Abdulkadir', 'Uukow', NULL, 'Male', '2002-06-04', NULL, NULL, NULL, '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-30', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-30 08:17:49', '2025-11-30 14:39:51'),
(4, NULL, 'STU000004', 'ADM20250004', 2, 'Aisha', 'Ahmed', '', 'Female', '2002-02-15', '', '', '', 'aishaabintu@gmail.com', '613976162', 'Korontada', '', '', '', NULL, NULL, NULL, '2025-12-14', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-14 07:07:04', '2025-12-14 07:37:36'),
(5, NULL, 'STU000005', 'ADM20250005', 2, 'Abdullahi Yusuf', 'Osman', 'Yusuf', 'Male', '2001-02-13', 'O+', '', '', '', '', '', '', '', '', NULL, NULL, NULL, '2025-12-14', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-14 07:28:47', '2025-12-14 07:37:36'),
(6, NULL, 'STU000006', 'ADM20250006', 2, 'Hafsa', 'Ali', 'Hassan', 'Female', '1999-10-23', 'O+', 'Muslim', 'Somali', '', '', '', '', '', '', NULL, NULL, NULL, '0000-00-00', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-14 07:31:19', '2025-12-21 06:24:08'),
(7, NULL, 'STU000007', 'ADM20250007', 2, 'Farah', 'Ali', 'Ibrahim', 'Male', '1993-05-10', 'O+', '', 'Somali', '', '', '', '', '', '', NULL, NULL, NULL, '0000-00-00', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-14 07:34:10', '2025-12-21 06:24:08'),
(8, NULL, 'STU000008', 'ADM20250008', 2, 'Farhan', 'Ali', 'Dahir', 'Male', '2007-05-23', 'B-', '', 'Somali', '', '', '', '', '', '', NULL, NULL, NULL, '0000-00-00', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-14 07:40:38', '2025-12-14 07:40:38'),
(610, 610, 'STU1F860A85', 'ADM1F860A85', 1, 'Mahad', 'Dirie', NULL, 'Female', '2013-06-26', NULL, NULL, 'Somali', NULL, '+252650615724', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:04', '2025-12-16 09:27:04'),
(611, 611, 'STU4126323C', 'ADM4126323C', 1, 'Najma', 'Ali', NULL, 'Female', '2008-09-24', NULL, NULL, 'Somali', NULL, '+252668227872', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 6, 15, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:04', '2025-12-20 07:05:26'),
(612, 612, 'STUF1664687', 'ADMF1664687', 1, 'Ayaan', 'Hassan', NULL, 'Female', '2008-01-05', NULL, NULL, 'Somali', NULL, '+252614841019', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:04', '2025-12-16 09:27:04'),
(613, 613, 'STU75E7653D', 'ADM75E7653D', 1, 'Farah', 'Nur', NULL, 'Male', '2015-08-11', NULL, NULL, 'Somali', NULL, '+252905914898', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:04', '2025-12-16 09:27:04'),
(614, 614, 'STU3A16A464', 'ADM3A16A464', 1, 'Layla', 'Warsame', NULL, 'Male', '2010-07-26', NULL, NULL, 'Somali', NULL, '+252635133531', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:04', '2025-12-16 09:27:04'),
(615, 615, 'STUAF089958', 'ADMAF089958', 1, 'Farah', 'Hassan', NULL, 'Female', '2006-02-28', NULL, NULL, 'Somali', NULL, '+252914956053', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:04', '2025-12-16 09:27:04'),
(616, 616, 'STU5D8DDBE5', 'ADM5D8DDBE5', 1, 'Ayaan', 'Guled', NULL, 'Male', '2008-08-23', NULL, NULL, 'Somali', NULL, '+252901152383', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:05', '2025-12-16 09:27:05'),
(617, 617, 'STUF161FF31', 'ADMF161FF31', 1, 'Najma', 'Nur', NULL, 'Male', '2008-03-02', NULL, NULL, 'Somali', NULL, '+252615680391', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 6, 15, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:05', '2025-12-20 07:05:26'),
(618, 618, 'STUFAB0549D', 'ADMFAB0549D', 1, 'Ayaan', 'Abdulle', NULL, 'Male', '2009-11-27', NULL, NULL, 'Somali', NULL, '+252657467244', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:05', '2025-12-16 09:27:05'),
(619, 619, 'STUDEA5D3FF', 'ADMDEA5D3FF', 1, 'Hassan', 'Dirie', NULL, 'Female', '2013-04-01', NULL, NULL, 'Somali', NULL, '+252661320883', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:05', '2025-12-16 09:27:05'),
(620, 620, 'STUA3DD5749', 'ADMA3DD5749', 1, 'Abdi', 'Abdulle', NULL, 'Male', '2008-12-31', NULL, NULL, 'Somali', NULL, '+252624803402', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:05', '2025-12-16 09:27:05'),
(621, 621, 'STUE65BBCF0', 'ADME65BBCF0', 1, 'Zamzam', 'Muse', NULL, 'Male', '2006-08-26', NULL, NULL, 'Somali', NULL, '+252654736287', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:05', '2025-12-16 09:27:05'),
(622, 622, 'STU225B073D', 'ADM225B073D', 1, 'Jama', 'Abdulle', NULL, 'Male', '2012-01-06', NULL, NULL, 'Somali', NULL, '+252909418587', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:05', '2025-12-16 09:27:05'),
(623, 623, 'STU7B163483', 'ADM7B163483', 1, 'Mukhtar', 'Muse', NULL, 'Male', '2006-11-06', NULL, NULL, 'Somali', NULL, '+252638662702', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:05', '2025-12-16 09:27:05'),
(624, 624, 'STU18002E2C', 'ADM18002E2C', 1, 'Asha', 'Warsame', NULL, 'Male', '2008-06-18', NULL, NULL, 'Somali', NULL, '+252614916676', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:05', '2025-12-16 09:27:05'),
(625, 625, 'STUAAC9DB6F', 'ADMAAC9DB6F', 1, 'Hodan', 'Yusuf', NULL, 'Male', '2009-11-16', NULL, NULL, 'Somali', NULL, '+252632145293', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:05', '2025-12-16 09:27:05'),
(626, 626, 'STU786D6029', 'ADM786D6029', 1, 'Mukhtar', 'Sheikh', NULL, 'Female', '2005-11-21', NULL, NULL, 'Somali', NULL, '+252625176617', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:05', '2025-12-16 09:27:05'),
(627, 627, 'STU4E0C994F', 'ADM4E0C994F', 1, 'Mukhtar', 'Farah', NULL, 'Female', '2009-02-20', NULL, NULL, 'Somali', NULL, '+252904001506', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:05', '2025-12-16 09:27:05'),
(628, 628, 'STU2E48DF6F', 'ADM2E48DF6F', 1, 'Khadra', 'Sheikh', NULL, 'Male', '2010-07-27', NULL, NULL, 'Somali', NULL, '+252657910856', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:05', '2025-12-16 09:27:05'),
(629, 629, 'STUAC2A3E10', 'ADMAC2A3E10', 1, 'Maryan', 'Adam', NULL, 'Male', '2013-04-11', NULL, NULL, 'Somali', NULL, '+252668163858', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:05', '2025-12-16 09:27:05'),
(630, 630, 'STU8A2856CC', 'ADM8A2856CC', 1, 'Mohamed', 'Farah', NULL, 'Female', '2011-07-19', NULL, NULL, 'Somali', NULL, '+252914709864', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:05', '2025-12-16 09:27:05'),
(631, 631, 'STUC014C538', 'ADMC014C538', 1, 'Safiya', 'Said', NULL, 'Male', '2008-11-26', NULL, NULL, 'Somali', NULL, '+252612197583', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(632, 632, 'STU47B93EBB', 'ADM47B93EBB', 1, 'Sahra', 'Yusuf', NULL, 'Male', '2013-08-25', NULL, NULL, 'Somali', NULL, '+252656173481', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(633, 633, 'STUB22700EE', 'ADMB22700EE', 1, 'Fartun', 'Hassan', NULL, 'Male', '2013-03-02', NULL, NULL, 'Somali', NULL, '+252634250394', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(634, 634, 'STU680EB337', 'ADM680EB337', 1, 'Sagal', 'Ali', NULL, 'Male', '2009-09-08', NULL, NULL, 'Somali', NULL, '+252656113190', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(635, 635, 'STU67F4AAA3', 'ADM67F4AAA3', 1, 'Hodan', 'Omar', NULL, 'Female', '2013-08-08', NULL, NULL, 'Somali', NULL, '+252908005951', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(636, 636, 'STU30414FFB', 'ADM30414FFB', 1, 'Hassan', 'Adam', NULL, 'Female', '2014-12-29', NULL, NULL, 'Somali', NULL, '+252629049513', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(637, 637, 'STU75F73FFA', 'ADM75F73FFA', 1, 'Mustafa', 'Muse', NULL, 'Male', '2009-03-03', NULL, NULL, 'Somali', NULL, '+252666437265', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 6, 15, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:06', '2025-12-20 07:05:26'),
(638, 638, 'STU3C91700E', 'ADM3C91700E', 1, 'Omar', 'Mohamed', NULL, 'Male', '2011-09-04', NULL, NULL, 'Somali', NULL, '+252616322858', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(639, 639, 'STU828986FA', 'ADM828986FA', 1, 'Ilhan', 'Mohamed', NULL, 'Male', '2012-10-10', NULL, NULL, 'Somali', NULL, '+252901799447', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(640, 640, 'STU0C3FE59B', 'ADM0C3FE59B', 1, 'Khadra', 'Sheikh', NULL, 'Female', '2006-01-14', NULL, NULL, 'Somali', NULL, '+252917098809', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(641, 641, 'STU9C6607A1', 'ADM9C6607A1', 1, 'Mohamed', 'Omar', NULL, 'Male', '2005-01-02', NULL, NULL, 'Somali', NULL, '+252659791975', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(642, 642, 'STU66367AD7', 'ADM66367AD7', 1, 'Nimco', 'Sheikh', NULL, 'Male', '2006-05-03', NULL, NULL, 'Somali', NULL, '+252612865055', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(643, 643, 'STUC0B5A0A4', 'ADMC0B5A0A4', 1, 'Ahmed', 'Jama', NULL, 'Female', '2011-03-31', NULL, NULL, 'Somali', NULL, '+252908244853', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(644, 644, 'STU03235800', 'ADM03235800', 1, 'Mustafa', 'Dirie', NULL, 'Female', '2005-02-12', NULL, NULL, 'Somali', NULL, '+252629362506', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 6, 15, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:06', '2025-12-20 07:05:26'),
(645, 645, 'STU5C11CCD7', 'ADM5C11CCD7', 1, 'Ilhan', 'Said', NULL, 'Male', '2006-11-11', NULL, NULL, 'Somali', NULL, '+252618797973', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(646, 646, 'STU2BCB290E', 'ADM2BCB290E', 1, 'Ahmed', 'Mohamed', NULL, 'Female', '2007-07-10', NULL, NULL, 'Somali', NULL, '+252618565238', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 1, 1, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(647, 647, 'STU84597B8F', 'ADM84597B8F', 2, 'Ilhan', 'Adam', NULL, 'Female', '2008-07-18', NULL, NULL, 'Somali', NULL, '+252631883934', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:07', '2025-12-21 06:24:08'),
(648, 648, 'STUE64AD219', 'ADME64AD219', 2, 'Ifrah', 'Abdulle', NULL, 'Male', '2007-09-01', NULL, NULL, 'Somali', NULL, '+252906940106', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:07', '2025-12-21 06:24:08'),
(649, 649, 'STU2EC09304', 'ADM2EC09304', 2, 'Hodan', 'Ali', NULL, 'Male', '2012-05-22', NULL, NULL, 'Somali', NULL, '+252616784005', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:07', '2025-12-21 06:24:08'),
(650, 650, 'STUB53FFA2D', 'ADMB53FFA2D', 2, 'Farah', 'Adam', NULL, 'Male', '2006-04-21', NULL, NULL, 'Somali', NULL, '+252631362531', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:07', '2025-12-21 06:24:08'),
(651, 651, 'STU4B25D25A', 'ADM4B25D25A', 2, 'Mahad', 'Yusuf', NULL, 'Female', '2013-07-09', NULL, NULL, 'Somali', NULL, '+252623129212', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:07', '2025-12-21 06:24:08'),
(652, 652, 'STU066263BC', 'ADM066263BC', 2, 'Maryan', 'Nur', NULL, 'Female', '2008-05-17', NULL, NULL, 'Somali', NULL, '+252655569810', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:07', '2025-12-21 06:24:08'),
(653, 653, 'STUDB4E2626', 'ADMDB4E2626', 2, 'Ilhan', 'Nur', NULL, 'Male', '2010-06-03', NULL, NULL, 'Somali', NULL, '+252616911808', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:07', '2025-12-21 06:24:08'),
(654, 654, 'STU15C657A3', 'ADM15C657A3', 2, 'Asha', 'Osman', NULL, 'Male', '2015-05-06', NULL, NULL, 'Somali', NULL, '+252626135408', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:07', '2025-12-21 06:24:08'),
(655, 655, 'STU49F0D0C1', 'ADM49F0D0C1', 2, 'Ayaan', 'Dirie', NULL, 'Female', '2010-02-19', NULL, NULL, 'Somali', NULL, '+252660486056', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:07', '2025-12-21 06:24:08'),
(656, 656, 'STUFCBB0C71', 'ADMFCBB0C71', 2, 'Safiya', 'Warsame', NULL, 'Female', '2010-08-17', NULL, NULL, 'Somali', NULL, '+252659627859', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:07', '2025-12-21 06:24:08'),
(657, 657, 'STU8878833A', 'ADM8878833A', 2, 'Rahma', 'Jama', NULL, 'Male', '2011-10-30', NULL, NULL, 'Somali', NULL, '+252615936093', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:07', '2025-12-21 06:24:08'),
(658, 658, 'STU7D7B6FE5', 'ADM7D7B6FE5', 2, 'Fartun', 'Warsame', NULL, 'Female', '2009-09-04', NULL, NULL, 'Somali', NULL, '+252612116751', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:07', '2025-12-21 06:24:08'),
(659, 659, 'STU793AE2A1', 'ADM793AE2A1', 2, 'Maryan', 'Abdullahi', NULL, 'Male', '2010-03-30', NULL, NULL, 'Somali', NULL, '+252629990789', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:07', '2025-12-21 06:24:08'),
(660, 660, 'STU8D0FF9F3', 'ADM8D0FF9F3', 2, 'Maryan', 'Hassan', NULL, 'Female', '2012-07-21', NULL, NULL, 'Somali', NULL, '+252639775487', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:07', '2025-12-21 06:24:08'),
(661, 661, 'STU0B2CF2A3', 'ADM0B2CF2A3', 2, 'Hodan', 'Farah', NULL, 'Female', '2006-10-28', NULL, NULL, 'Somali', NULL, '+252612142989', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:07', '2025-12-21 06:24:08'),
(662, 662, 'STU40B07A32', 'ADM40B07A32', 2, 'Zamzam', 'Abdullahi', NULL, 'Male', '2012-11-24', NULL, NULL, 'Somali', NULL, '+252919991206', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:08', '2025-12-21 06:24:08'),
(663, 663, 'STU1CFC3B65', 'ADM1CFC3B65', 2, 'Mohamed', 'Hassan', NULL, 'Female', '2013-09-11', NULL, NULL, 'Somali', NULL, '+252902956690', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:08', '2025-12-21 06:24:08'),
(664, 664, 'STUC5F7F9FD', 'ADMC5F7F9FD', 2, 'Najma', 'Guled', NULL, 'Male', '2005-07-29', NULL, NULL, 'Somali', NULL, '+252911472245', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:08', '2025-12-21 06:24:08'),
(665, 665, 'STU2BFFC7B5', 'ADM2BFFC7B5', 2, 'Hassan', 'Yusuf', NULL, 'Female', '2015-06-04', NULL, NULL, 'Somali', NULL, '+252910234679', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:08', '2025-12-21 06:24:08'),
(666, 666, 'STU8C2559CC', 'ADM8C2559CC', 2, 'Mukhtar', 'Roble', NULL, 'Female', '2013-06-18', NULL, NULL, 'Somali', NULL, '+252658279997', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:08', '2025-12-21 06:24:08'),
(667, 667, 'STU3F020C7E', 'ADM3F020C7E', 2, 'Ahmed', 'Muse', NULL, 'Male', '2015-07-02', NULL, NULL, 'Somali', NULL, '+252614672156', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:08', '2025-12-21 06:24:08'),
(668, 668, 'STU3DAB0BA7', 'ADM3DAB0BA7', 2, 'Hodan', 'Roble', NULL, 'Female', '2015-07-01', NULL, NULL, 'Somali', NULL, '+252908904576', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:08', '2025-12-21 06:24:08'),
(669, 669, 'STU59B3D8D8', 'ADM59B3D8D8', 2, 'Safiya', 'Ismail', NULL, 'Male', '2013-11-09', NULL, NULL, 'Somali', NULL, '+252668854348', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:08', '2025-12-21 06:24:08'),
(670, 670, 'STU55C73841', 'ADM55C73841', 2, 'Hassan', 'Yusuf', NULL, 'Female', '2014-05-10', NULL, NULL, 'Somali', NULL, '+252614242930', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:08', '2025-12-21 06:24:08'),
(671, 671, 'STUB76DB832', 'ADMB76DB832', 2, 'Mohamed', 'Ali', NULL, 'Female', '2011-12-10', NULL, NULL, 'Somali', NULL, '+252614027308', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:08', '2025-12-21 06:24:08'),
(672, 672, 'STUC8601A33', 'ADMC8601A33', 2, 'Farah', 'Osman', NULL, 'Female', '2008-04-18', NULL, NULL, 'Somali', NULL, '+252919128015', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:08', '2025-12-21 06:24:08'),
(673, 673, 'STU2446D65D', 'ADM2446D65D', 2, 'Ahmed', 'Mohamed', NULL, 'Female', '2008-05-08', NULL, NULL, 'Somali', NULL, '+252617725265', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:08', '2025-12-21 06:24:08'),
(674, 674, 'STU6718AEF1', 'ADM6718AEF1', 2, 'Nimco', 'Roble', NULL, 'Female', '2009-10-21', NULL, NULL, 'Somali', NULL, '+252668482537', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:08', '2025-12-21 06:24:08'),
(675, 675, 'STU1005A76C', 'ADM1005A76C', 2, 'Jama', 'Abdullahi', NULL, 'Male', '2011-05-29', NULL, NULL, 'Somali', NULL, '+252655885572', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:08', '2025-12-21 06:24:08'),
(676, 676, 'STU76FB918B', 'ADM76FB918B', 2, 'Hassan', 'Omar', NULL, 'Female', '2008-08-27', NULL, NULL, 'Somali', NULL, '+252910335034', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:08', '2025-12-21 06:24:08'),
(677, 677, 'STU0B71B4B6', 'ADM0B71B4B6', 2, 'Hassan', 'Omar', NULL, 'Female', '2010-06-29', NULL, NULL, 'Somali', NULL, '+252668364825', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:09', '2025-12-21 06:24:08'),
(678, 678, 'STUEB0373D1', 'ADMEB0373D1', 2, 'Ali', 'Nur', NULL, 'Male', '2008-06-07', NULL, NULL, 'Somali', NULL, '+252611724134', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:09', '2025-12-21 06:24:08'),
(679, 679, 'STU51E0DFC3', 'ADM51E0DFC3', 2, 'Hodan', 'Warsame', NULL, 'Female', '2006-11-15', NULL, NULL, 'Somali', NULL, '+252916319576', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:09', '2025-12-21 06:24:08'),
(680, 680, 'STUE5CA90AA', 'ADME5CA90AA', 2, 'Abdi', 'Mohamed', NULL, 'Female', '2012-09-11', NULL, NULL, 'Somali', NULL, '+252624087463', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:09', '2025-12-21 06:24:08'),
(681, 681, 'STU7A2604BD', 'ADM7A2604BD', 2, 'Ilhan', 'Ismail', NULL, 'Male', '2006-03-13', NULL, NULL, 'Somali', NULL, '+252661775145', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:09', '2025-12-21 06:24:08'),
(682, 682, 'STUEA81B13F', 'ADMEA81B13F', 2, 'Safiya', 'Abdullahi', NULL, 'Male', '2015-06-25', NULL, NULL, 'Somali', NULL, '+252626813730', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:09', '2025-12-21 06:24:08'),
(683, 683, 'STU1E951F73', 'ADM1E951F73', 2, 'Najma', 'Said', NULL, 'Male', '2008-12-31', NULL, NULL, 'Somali', NULL, '+252633203661', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:09', '2025-12-21 06:24:08'),
(684, 684, 'STU5B8B86AF', 'ADM5B8B86AF', 2, 'Hassan', 'Farah', NULL, 'Female', '2007-01-17', NULL, NULL, 'Somali', NULL, '+252905515466', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:09', '2025-12-21 06:24:08'),
(685, 685, 'STU678949B5', 'ADM678949B5', 2, 'Mustafa', 'Farah', NULL, 'Female', '2010-07-11', NULL, NULL, 'Somali', NULL, '+252902683487', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:09', '2025-12-21 06:24:08'),
(686, 686, 'STU18FEE089', 'ADM18FEE089', 2, 'Nimco', 'Adam', NULL, 'Female', '2014-08-17', NULL, NULL, 'Somali', NULL, '+252662418482', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 2, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:09', '2025-12-21 06:24:08'),
(687, 687, 'STUD5206121', 'ADMD5206121', 2, 'Mustafa', 'Said', NULL, 'Female', '2012-09-23', NULL, NULL, 'Somali', NULL, '+252654233030', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:09', '2025-12-21 06:24:08'),
(688, 688, 'STU0B028D5C', 'ADM0B028D5C', 2, 'Mahad', 'Omar', NULL, 'Male', '2013-12-04', NULL, NULL, 'Somali', NULL, '+252623590399', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:09', '2025-12-21 06:24:08'),
(689, 689, 'STU56D54B60', 'ADM56D54B60', 2, 'Abdi', 'Guled', NULL, 'Female', '2006-01-18', NULL, NULL, 'Somali', NULL, '+252912039263', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:09', '2025-12-21 06:24:08'),
(690, 690, 'STU61938DA0', 'ADM61938DA0', 2, 'Hassan', 'Dirie', NULL, 'Female', '2007-03-31', NULL, NULL, 'Somali', NULL, '+252635776698', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:09', '2025-12-21 06:24:08'),
(691, 691, 'STU10BEE633', 'ADM10BEE633', 2, 'Asha', 'Abdullahi', NULL, 'Female', '2009-07-20', NULL, NULL, 'Somali', NULL, '+252658768915', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:09', '2025-12-21 06:24:08'),
(692, 692, 'STU4F334732', 'ADM4F334732', 2, 'Mustafa', 'Said', NULL, 'Male', '2011-08-25', NULL, NULL, 'Somali', NULL, '+252636455291', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:09', '2025-12-21 06:24:08'),
(693, 693, 'STU7DD82AB9', 'ADM7DD82AB9', 2, 'Ilhan', 'Hassan', NULL, 'Female', '2009-10-28', NULL, NULL, 'Somali', NULL, '+252632101020', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:09', '2025-12-21 06:24:08'),
(694, 694, 'STU825726A9', 'ADM825726A9', 2, 'Abdi', 'Yusuf', NULL, 'Female', '2014-04-08', NULL, NULL, 'Somali', NULL, '+252906626921', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:10', '2025-12-21 06:24:08'),
(695, 695, 'STUC131D2F5', 'ADMC131D2F5', 2, 'Maryan', 'Farah', NULL, 'Female', '2010-05-23', NULL, NULL, 'Somali', NULL, '+252664694037', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:10', '2025-12-21 06:24:08'),
(696, 696, 'STU04B6CD0D', 'ADM04B6CD0D', 2, 'Ilhan', 'Omar', NULL, 'Female', '2006-04-16', NULL, NULL, 'Somali', NULL, '+252907487494', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:10', '2025-12-21 06:24:08'),
(697, 697, 'STU8456C653', 'ADM8456C653', 2, 'Fartun', 'Jama', NULL, 'Male', '2011-01-21', NULL, NULL, 'Somali', NULL, '+252630887369', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:10', '2025-12-21 06:24:08'),
(698, 698, 'STU08D2B5BE', 'ADM08D2B5BE', 2, 'Mahad', 'Abdulle', NULL, 'Female', '2013-12-07', NULL, NULL, 'Somali', NULL, '+252905489752', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:10', '2025-12-21 06:24:08'),
(699, 699, 'STU425836E5', 'ADM425836E5', 2, 'Mohamed', 'Yusuf', NULL, 'Male', '2012-01-30', NULL, NULL, 'Somali', NULL, '+252639347655', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:10', '2025-12-21 06:24:08'),
(700, 700, 'STU420983EA', 'ADM420983EA', 2, 'Sagal', 'Sheikh', NULL, 'Female', '2008-12-09', NULL, NULL, 'Somali', NULL, '+252621707656', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:10', '2025-12-21 06:24:08'),
(701, 701, 'STU4515AB2B', 'ADM4515AB2B', 2, 'Maryan', 'Farah', NULL, 'Male', '2015-01-03', NULL, NULL, 'Somali', NULL, '+252638213983', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:10', '2025-12-21 06:24:08'),
(702, 702, 'STU53F32970', 'ADM53F32970', 2, 'Layla', 'Ali', NULL, 'Male', '2009-01-20', NULL, NULL, 'Somali', NULL, '+252917167976', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:10', '2025-12-21 06:24:08'),
(703, 703, 'STU7DAF945E', 'ADM7DAF945E', 2, 'Hodan', 'Osman', NULL, 'Male', '2008-06-24', NULL, NULL, 'Somali', NULL, '+252651577088', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:10', '2025-12-21 06:24:08'),
(704, 704, 'STU5DC95AE9', 'ADM5DC95AE9', 2, 'Layla', 'Omar', NULL, 'Female', '2012-03-10', NULL, NULL, 'Somali', NULL, '+252622279890', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:10', '2025-12-21 06:24:08'),
(705, 705, 'STU7BDA1496', 'ADM7BDA1496', 2, 'Nimco', 'Abdullahi', NULL, 'Female', '2013-02-27', NULL, NULL, 'Somali', NULL, '+252622376274', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:10', '2025-12-21 06:24:08'),
(706, 706, 'STU1209B4A4', 'ADM1209B4A4', 2, 'Ibrahim', 'Yusuf', NULL, 'Female', '2008-10-26', NULL, NULL, 'Somali', NULL, '+252611918675', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:10', '2025-12-21 06:24:08'),
(707, 707, 'STU1BF7123A', 'ADM1BF7123A', 2, 'Hassan', 'Roble', NULL, 'Male', '2010-03-06', NULL, NULL, 'Somali', NULL, '+252902563506', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:10', '2025-12-21 06:24:08'),
(708, 708, 'STUAABD02E6', 'ADMAABD02E6', 2, 'Hodan', 'Warsame', NULL, 'Male', '2010-02-23', NULL, NULL, 'Somali', NULL, '+252912109157', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:10', '2025-12-21 06:24:08'),
(709, 709, 'STUC932E99C', 'ADMC932E99C', 2, 'Najma', 'Warsame', NULL, 'Female', '2006-07-28', NULL, NULL, 'Somali', NULL, '+252626917473', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:10', '2025-12-21 06:24:08'),
(710, 710, 'STUAF4A7ECD', 'ADMAF4A7ECD', 2, 'Zamzam', 'Roble', NULL, 'Male', '2010-03-05', NULL, NULL, 'Somali', NULL, '+252668755840', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:11', '2025-12-21 06:24:08'),
(711, 711, 'STU7F471247', 'ADM7F471247', 2, 'Asha', 'Jama', NULL, 'Male', '2007-05-03', NULL, NULL, 'Somali', NULL, '+252650290628', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:11', '2025-12-21 06:24:08'),
(712, 712, 'STUF247D5B2', 'ADMF247D5B2', 2, 'Ibrahim', 'Nur', NULL, 'Male', '2015-11-03', NULL, NULL, 'Somali', NULL, '+252612830035', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:11', '2025-12-21 06:24:08'),
(713, 713, 'STU1730FBC8', 'ADM1730FBC8', 2, 'Ali', 'Said', NULL, 'Female', '2014-11-06', NULL, NULL, 'Somali', NULL, '+252628959123', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:11', '2025-12-21 06:24:08'),
(714, 714, 'STU2FA0FFBC', 'ADM2FA0FFBC', 2, 'Maryan', 'Ali', NULL, 'Male', '2014-07-21', NULL, NULL, 'Somali', NULL, '+252633089591', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:11', '2025-12-21 06:24:08'),
(715, 715, 'STU97A04136', 'ADM97A04136', 2, 'Mahad', 'Guled', NULL, 'Female', '2015-02-23', NULL, NULL, 'Somali', NULL, '+252666276539', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:11', '2025-12-21 06:24:08'),
(716, 716, 'STU87040815', 'ADM87040815', 2, 'Ibrahim', 'Hassan', NULL, 'Male', '2006-01-03', NULL, NULL, 'Somali', NULL, '+252636195874', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:11', '2025-12-21 06:24:08'),
(717, 717, 'STUCB65B3D9', 'ADMCB65B3D9', 2, 'Ahmed', 'Roble', NULL, 'Female', '2015-03-08', NULL, NULL, 'Somali', NULL, '+252617040164', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:11', '2025-12-21 06:24:08'),
(718, 718, 'STUD22147A9', 'ADMD22147A9', 2, 'Sagal', 'Hassan', NULL, 'Female', '2008-08-26', NULL, NULL, 'Somali', NULL, '+252665562884', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:11', '2025-12-21 06:24:08'),
(719, 719, 'STUA29E2F85', 'ADMA29E2F85', 2, 'Ibrahim', 'Mohamed', NULL, 'Male', '2005-04-30', NULL, NULL, 'Somali', NULL, '+252658947464', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:11', '2025-12-21 06:24:08'),
(720, 720, 'STU6E953683', 'ADM6E953683', 2, 'Khadra', 'Nur', NULL, 'Male', '2006-03-04', NULL, NULL, 'Somali', NULL, '+252919261743', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:11', '2025-12-21 06:24:08'),
(721, 721, 'STUF9F8537B', 'ADMF9F8537B', 2, 'Fartun', 'Abdulle', NULL, 'Male', '2014-06-20', NULL, NULL, 'Somali', NULL, '+252906706663', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:11', '2025-12-21 06:24:08'),
(722, 722, 'STUF57347AA', 'ADMF57347AA', 2, 'Layla', 'Muse', NULL, 'Male', '2005-03-01', NULL, NULL, 'Somali', NULL, '+252654771973', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:11', '2025-12-21 06:24:08'),
(723, 723, 'STU09044BDA', 'ADM09044BDA', 2, 'Najma', 'Ismail', NULL, 'Female', '2014-02-14', NULL, NULL, 'Somali', NULL, '+252917477180', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:11', '2025-12-21 06:24:08'),
(724, 724, 'STUA336725A', 'ADMA336725A', 2, 'Maryan', 'Abdullahi', NULL, 'Female', '2008-06-27', NULL, NULL, 'Somali', NULL, '+252906104209', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 3, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:11', '2025-12-21 06:24:08'),
(725, 725, 'STUD296A227', 'ADMD296A227', 2, 'Maryan', 'Sheikh', NULL, 'Female', '2009-10-29', NULL, NULL, 'Somali', NULL, '+252611682239', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:11', '2025-12-21 06:24:08'),
(726, 726, 'STU6DD3EB68', 'ADM6DD3EB68', 2, 'Mustafa', 'Ismail', NULL, 'Male', '2010-12-25', NULL, NULL, 'Somali', NULL, '+252901578942', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:12', '2025-12-21 06:24:08'),
(727, 727, 'STUFA62A92D', 'ADMFA62A92D', 2, 'Omar', 'Muse', NULL, 'Female', '2010-05-16', NULL, NULL, 'Somali', NULL, '+252659357986', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:12', '2025-12-21 06:24:08'),
(728, 728, 'STU6F4A2EA7', 'ADM6F4A2EA7', 2, 'Layla', 'Sheikh', NULL, 'Male', '2011-06-28', NULL, NULL, 'Somali', NULL, '+252626861119', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:12', '2025-12-21 06:24:08'),
(729, 729, 'STU36BF9AF7', 'ADM36BF9AF7', 2, 'Layla', 'Yusuf', NULL, 'Female', '2012-08-09', NULL, NULL, 'Somali', NULL, '+252626669685', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:12', '2025-12-21 06:24:08'),
(730, 730, 'STU2304EA09', 'ADM2304EA09', 2, 'Layla', 'Omar', NULL, 'Female', '2007-03-24', NULL, NULL, 'Somali', NULL, '+252666445288', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:12', '2025-12-21 06:24:08'),
(731, 731, 'STUEE140399', 'ADMEE140399', 2, 'Maryan', 'Nur', NULL, 'Female', '2013-01-06', NULL, NULL, 'Somali', NULL, '+252906741522', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:12', '2025-12-21 06:24:08'),
(732, 732, 'STU8937EC85', 'ADM8937EC85', 2, 'Najma', 'Jama', NULL, 'Male', '2008-07-28', NULL, NULL, 'Somali', NULL, '+252619949078', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:12', '2025-12-21 06:24:08'),
(733, 733, 'STUBB5A9453', 'ADMBB5A9453', 2, 'Rahma', 'Warsame', NULL, 'Female', '2006-07-14', NULL, NULL, 'Somali', NULL, '+252628742128', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:12', '2025-12-21 06:24:08'),
(734, 734, 'STU358FB38D', 'ADM358FB38D', 2, 'Ilhan', 'Yusuf', NULL, 'Female', '2005-10-10', NULL, NULL, 'Somali', NULL, '+252916452268', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:12', '2025-12-21 06:24:08'),
(735, 735, 'STUBD54571F', 'ADMBD54571F', 2, 'Omar', 'Roble', NULL, 'Male', '2009-08-25', NULL, NULL, 'Somali', NULL, '+252900343403', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:12', '2025-12-21 06:24:08'),
(736, 736, 'STU83C3359F', 'ADM83C3359F', 2, 'Hassan', 'Mohamed', NULL, 'Male', '2013-01-19', NULL, NULL, 'Somali', NULL, '+252905036465', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:12', '2025-12-21 06:24:08'),
(737, 737, 'STUD0D15FA3', 'ADMD0D15FA3', 2, 'Rahma', 'Hassan', NULL, 'Female', '2005-11-08', NULL, NULL, 'Somali', NULL, '+252637577869', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:12', '2025-12-21 06:24:08'),
(738, 738, 'STU1D27008D', 'ADM1D27008D', 2, 'Mustafa', 'Muse', NULL, 'Female', '2010-01-19', NULL, NULL, 'Somali', NULL, '+252620026167', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:12', '2025-12-21 06:24:08'),
(739, 739, 'STUA6BDBF6B', 'ADMA6BDBF6B', 2, 'Mustafa', 'Adam', NULL, 'Female', '2014-08-31', NULL, NULL, 'Somali', NULL, '+252658408490', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:12', '2025-12-21 06:24:08'),
(740, 740, 'STUF5DD880B', 'ADMF5DD880B', 2, 'Jama', 'Said', NULL, 'Male', '2015-11-25', NULL, NULL, 'Somali', NULL, '+252911169091', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:12', '2025-12-21 06:24:08'),
(741, 741, 'STUF4977683', 'ADMF4977683', 2, 'Maryan', 'Osman', NULL, 'Female', '2011-04-29', NULL, NULL, 'Somali', NULL, '+252666875722', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:12', '2025-12-21 06:24:08'),
(742, 742, 'STU0D142F17', 'ADM0D142F17', 2, 'Khadra', 'Yusuf', NULL, 'Male', '2015-03-30', NULL, NULL, 'Somali', NULL, '+252615925231', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:13', '2025-12-21 06:24:08'),
(743, 743, 'STU8F7E9159', 'ADM8F7E9159', 2, 'Yusuf', 'Hassan', NULL, 'Male', '2005-02-15', NULL, NULL, 'Somali', NULL, '+252914295978', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:13', '2025-12-21 06:24:08'),
(744, 744, 'STUB3586E1B', 'ADMB3586E1B', 2, 'Mohamed', 'Said', NULL, 'Male', '2015-09-17', NULL, NULL, 'Somali', NULL, '+252654539038', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:13', '2025-12-21 06:24:08'),
(745, 745, 'STUBA3CC7B9', 'ADMBA3CC7B9', 2, 'Sahra', 'Jama', NULL, 'Female', '2010-08-01', NULL, NULL, 'Somali', NULL, '+252637416960', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:13', '2025-12-21 06:24:08'),
(746, 746, 'STU17AC25E8', 'ADM17AC25E8', 2, 'Omar', 'Jama', NULL, 'Female', '2007-02-26', NULL, NULL, 'Somali', NULL, '+252662916429', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:13', '2025-12-21 06:24:08'),
(747, 747, 'STU91F5C935', 'ADM91F5C935', 2, 'Mustafa', 'Omar', NULL, 'Male', '2013-09-02', NULL, NULL, 'Somali', NULL, '+252667454897', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:13', '2025-12-21 06:24:08'),
(748, 748, 'STU57EAE738', 'ADM57EAE738', 2, 'Hassan', 'Jama', NULL, 'Female', '2011-03-05', NULL, NULL, 'Somali', NULL, '+252661080670', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:13', '2025-12-21 06:24:08'),
(749, 749, 'STU144E621F', 'ADM144E621F', 2, 'Mahad', 'Osman', NULL, 'Female', '2006-12-27', NULL, NULL, 'Somali', NULL, '+252633070882', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:13', '2025-12-21 06:24:08'),
(750, 750, 'STU821623FE', 'ADM821623FE', 2, 'Nimco', 'Nur', NULL, 'Male', '2010-04-03', NULL, NULL, 'Somali', NULL, '+252635387952', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:13', '2025-12-21 06:24:08'),
(751, 751, 'STU8F0E2BEB', 'ADM8F0E2BEB', 2, 'Safiya', 'Sheikh', NULL, 'Male', '2013-12-22', NULL, NULL, 'Somali', NULL, '+252635167162', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:13', '2025-12-21 06:24:08'),
(752, 752, 'STU5828D11C', 'ADM5828D11C', 2, 'Ifrah', 'Jama', NULL, 'Male', '2011-12-29', NULL, NULL, 'Somali', NULL, '+252621162724', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:13', '2025-12-21 06:24:08'),
(753, 753, 'STU829A5CAA', 'ADM829A5CAA', 2, 'Zamzam', 'Roble', NULL, 'Male', '2013-06-05', NULL, NULL, 'Somali', NULL, '+252907664261', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:13', '2025-12-21 06:24:08');
INSERT INTO `students` (`id`, `user_id`, `student_id`, `admission_no`, `branch_id`, `first_name`, `last_name`, `middle_name`, `gender`, `date_of_birth`, `blood_group`, `religion`, `nationality`, `email`, `phone`, `address`, `city`, `state`, `postal_code`, `photo`, `barcode`, `qr_code`, `admission_date`, `current_class_id`, `current_section_id`, `status`, `is_hostel`, `is_transport`, `previous_school`, `medical_conditions`, `allergies`, `special_needs`, `discount_type`, `discount_value`, `created_at`, `updated_at`) VALUES
(754, 754, 'STU9AF09EE1', 'ADM9AF09EE1', 2, 'Layla', 'Ali', NULL, 'Male', '2006-06-04', NULL, NULL, 'Somali', NULL, '+252918294810', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:13', '2025-12-21 06:24:08'),
(755, 755, 'STUBDA98FD2', 'ADMBDA98FD2', 2, 'Ibrahim', 'Roble', NULL, 'Female', '2005-03-29', NULL, NULL, 'Somali', NULL, '+252636205066', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:13', '2025-12-21 06:24:08'),
(756, 756, 'STU69F6B589', 'ADM69F6B589', 2, 'Layla', 'Said', NULL, 'Female', '2015-11-02', NULL, NULL, 'Somali', NULL, '+252633750395', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:13', '2025-12-21 06:24:08'),
(757, 757, 'STU37330D42', 'ADM37330D42', 2, 'Layla', 'Dirie', NULL, 'Male', '2005-03-11', NULL, NULL, 'Somali', NULL, '+252636941330', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:14', '2025-12-21 06:24:08'),
(758, 758, 'STU8A866275', 'ADM8A866275', 2, 'Ilhan', 'Said', NULL, 'Male', '2012-07-29', NULL, NULL, 'Somali', NULL, '+252634718888', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:14', '2025-12-21 06:24:08'),
(759, 759, 'STU50678ED7', 'ADM50678ED7', 2, 'Omar', 'Hassan', NULL, 'Female', '2008-06-24', NULL, NULL, 'Somali', NULL, '+252669246193', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:14', '2025-12-21 06:24:08'),
(760, 760, 'STUF7D2A451', 'ADMF7D2A451', 2, 'Hodan', 'Hassan', NULL, 'Female', '2009-06-24', NULL, NULL, 'Somali', NULL, '+252909938173', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:14', '2025-12-21 06:24:08'),
(761, 761, 'STU86BC7AB0', 'ADM86BC7AB0', 2, 'Najma', 'Dirie', NULL, 'Male', '2011-01-03', NULL, NULL, 'Somali', NULL, '+252665246061', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:14', '2025-12-21 06:24:08'),
(762, 762, 'STUA5FFDDB9', 'ADMA5FFDDB9', 2, 'Nimco', 'Hassan', NULL, 'Female', '2013-07-14', NULL, NULL, 'Somali', NULL, '+252908855084', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:14', '2025-12-21 06:24:08'),
(763, 763, 'STUBAB73776', 'ADMBAB73776', 2, 'Omar', 'Osman', NULL, 'Male', '2013-02-14', NULL, NULL, 'Somali', NULL, '+252627248541', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:14', '2025-12-21 06:24:08'),
(764, 764, 'STU9F96CCED', 'ADM9F96CCED', 2, 'Hodan', 'Jama', NULL, 'Female', '2015-12-16', NULL, NULL, 'Somali', NULL, '+252611641824', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 4, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:14', '2025-12-21 06:24:08'),
(765, 765, 'STUD1B3D0F2', 'ADMD1B3D0F2', 2, 'Ali', 'Abdulle', NULL, 'Female', '2007-10-22', NULL, NULL, 'Somali', NULL, '+252660306930', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:14', '2025-12-21 06:24:08'),
(766, 766, 'STU638C9B92', 'ADM638C9B92', 2, 'Abdi', 'Said', NULL, 'Female', '2007-05-27', NULL, NULL, 'Somali', NULL, '+252615034208', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:14', '2025-12-21 06:24:08'),
(767, 767, 'STUDA252AFD', 'ADMDA252AFD', 2, 'Ali', 'Yusuf', NULL, 'Male', '2012-12-05', NULL, NULL, 'Somali', NULL, '+252619593495', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:14', '2025-12-21 06:24:08'),
(768, 768, 'STU0F4C3D40', 'ADM0F4C3D40', 2, 'Hodan', 'Said', NULL, 'Male', '2014-07-04', NULL, NULL, 'Somali', NULL, '+252623950198', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:14', '2025-12-21 06:24:08'),
(769, 769, 'STU1E852DF2', 'ADM1E852DF2', 2, 'Hodan', 'Muse', NULL, 'Female', '2007-10-24', NULL, NULL, 'Somali', NULL, '+252634129595', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:14', '2025-12-21 06:24:08'),
(770, 770, 'STUA5823BC5', 'ADMA5823BC5', 2, 'Ilhan', 'Omar', NULL, 'Male', '2007-10-30', NULL, NULL, 'Somali', NULL, '+252913413064', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:14', '2025-12-21 06:24:08'),
(771, 771, 'STU5D5F3EB0', 'ADM5D5F3EB0', 2, 'Rahma', 'Guled', NULL, 'Male', '2006-08-22', NULL, NULL, 'Somali', NULL, '+252909037874', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:14', '2025-12-21 06:24:08'),
(772, 772, 'STU3AF16CE0', 'ADM3AF16CE0', 2, 'Omar', 'Mohamed', NULL, 'Male', '2010-07-24', NULL, NULL, 'Somali', NULL, '+252654142936', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:14', '2025-12-21 06:24:08'),
(773, 773, 'STU5061794C', 'ADM5061794C', 2, 'Safiya', 'Ismail', NULL, 'Male', '2011-12-21', NULL, NULL, 'Somali', NULL, '+252615582998', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:15', '2025-12-21 06:24:08'),
(774, 774, 'STU0CC6427E', 'ADM0CC6427E', 2, 'Zamzam', 'Mohamed', NULL, 'Male', '2011-11-19', NULL, NULL, 'Somali', NULL, '+252904051881', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:15', '2025-12-21 06:24:08'),
(775, 775, 'STU3337FB9D', 'ADM3337FB9D', 2, 'Omar', 'Abdulle', NULL, 'Female', '2015-09-15', NULL, NULL, 'Somali', NULL, '+252619061975', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:15', '2025-12-21 06:24:08'),
(776, 776, 'STUABE16896', 'ADMABE16896', 2, 'Mohamed', 'Abdullahi', NULL, 'Male', '2011-10-16', NULL, NULL, 'Somali', NULL, '+252639525685', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:15', '2025-12-21 06:24:08'),
(777, 777, 'STU59418FC7', 'ADM59418FC7', 2, 'Zamzam', 'Warsame', NULL, 'Female', '2007-04-23', NULL, NULL, 'Somali', NULL, '+252650399679', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:15', '2025-12-21 06:24:08'),
(778, 778, 'STU47FFEFFD', 'ADM47FFEFFD', 2, 'Maryan', 'Ismail', NULL, 'Male', '2011-10-19', NULL, NULL, 'Somali', NULL, '+252903424183', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:15', '2025-12-21 06:24:08'),
(779, 779, 'STU1EDBFD24', 'ADM1EDBFD24', 2, 'Maryan', 'Ali', NULL, 'Male', '2009-03-04', NULL, NULL, 'Somali', NULL, '+252665066448', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:15', '2025-12-21 06:24:08'),
(780, 780, 'STUE5277AE6', 'ADME5277AE6', 2, 'Najma', 'Sheikh', NULL, 'Male', '2012-07-30', NULL, NULL, 'Somali', NULL, '+252633182747', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:15', '2025-12-21 06:24:08'),
(781, 781, 'STU2C61B522', 'ADM2C61B522', 2, 'Khadra', 'Ali', NULL, 'Female', '2013-04-27', NULL, NULL, 'Somali', NULL, '+252903908702', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:15', '2025-12-21 06:24:08'),
(782, 782, 'STU25F02F55', 'ADM25F02F55', 2, 'Farah', 'Omar', NULL, 'Female', '2011-04-20', NULL, NULL, 'Somali', NULL, '+252656591937', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:15', '2025-12-21 06:24:08'),
(783, 783, 'STUAF6F8585', 'ADMAF6F8585', 2, 'Mohamed', 'Muse', NULL, 'Female', '2011-03-12', NULL, NULL, 'Somali', NULL, '+252916212194', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:15', '2025-12-21 06:24:08'),
(784, 784, 'STU99FFDB61', 'ADM99FFDB61', 2, 'Ilhan', 'Hassan', NULL, 'Male', '2012-09-04', NULL, NULL, 'Somali', NULL, '+252627084769', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:15', '2025-12-21 06:24:08'),
(785, 785, 'STU08512FC0', 'ADM08512FC0', 2, 'Najma', 'Nur', NULL, 'Male', '2005-08-01', NULL, NULL, 'Somali', NULL, '+252658019379', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:15', '2025-12-21 06:24:08'),
(786, 786, 'STU56489AE1', 'ADM56489AE1', 2, 'Mukhtar', 'Dirie', NULL, 'Female', '2008-07-14', NULL, NULL, 'Somali', NULL, '+252656868544', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:15', '2025-12-21 06:24:08'),
(787, 787, 'STU83628BD6', 'ADM83628BD6', 2, 'Omar', 'Omar', NULL, 'Female', '2013-06-26', NULL, NULL, 'Somali', NULL, '+252618185278', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:15', '2025-12-21 06:24:08'),
(788, 788, 'STUF0F45A8E', 'ADMF0F45A8E', 2, 'Jama', 'Hassan', NULL, 'Female', '2015-02-07', NULL, NULL, 'Somali', NULL, '+252666351200', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:15', '2025-12-21 06:24:08'),
(789, 789, 'STU30876433', 'ADM30876433', 2, 'Khadra', 'Said', NULL, 'Female', '2007-01-24', NULL, NULL, 'Somali', NULL, '+252653536006', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:16', '2025-12-21 06:24:08'),
(790, 790, 'STU53CB6F57', 'ADM53CB6F57', 2, 'Zamzam', 'Adam', NULL, 'Female', '2009-07-20', NULL, NULL, 'Somali', NULL, '+252664505813', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:16', '2025-12-21 06:24:08'),
(791, 791, 'STUBA73D490', 'ADMBA73D490', 2, 'Ismail', 'Farah', NULL, 'Female', '2012-09-06', NULL, NULL, 'Somali', NULL, '+252632337357', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:16', '2025-12-21 06:24:08'),
(792, 792, 'STU3B5E38F8', 'ADM3B5E38F8', 2, 'Ahmed', 'Warsame', NULL, 'Female', '2014-06-29', NULL, NULL, 'Somali', NULL, '+252653204470', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:16', '2025-12-21 06:24:08'),
(793, 793, 'STU4DF0B2B7', 'ADM4DF0B2B7', 2, 'Farah', 'Osman', NULL, 'Female', '2012-09-28', NULL, NULL, 'Somali', NULL, '+252661143088', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:16', '2025-12-21 06:24:08'),
(794, 794, 'STU92BF5E63', 'ADM92BF5E63', 2, 'Farah', 'Farah', NULL, 'Female', '2008-03-19', NULL, NULL, 'Somali', NULL, '+252909666502', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:16', '2025-12-21 06:24:08'),
(795, 795, 'STU110E9DB3', 'ADM110E9DB3', 2, 'Safiya', 'Ismail', NULL, 'Male', '2011-04-16', NULL, NULL, 'Somali', NULL, '+252910675307', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:16', '2025-12-21 06:24:08'),
(796, 796, 'STU29F058D8', 'ADM29F058D8', 2, 'Safiya', 'Warsame', NULL, 'Male', '2009-01-27', NULL, NULL, 'Somali', NULL, '+252623575840', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:16', '2025-12-21 06:24:08'),
(797, 797, 'STU8EF8CA8F', 'ADM8EF8CA8F', 2, 'Najma', 'Dirie', NULL, 'Male', '2014-09-19', NULL, NULL, 'Somali', NULL, '+252911729141', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:16', '2025-12-21 06:24:08'),
(798, 798, 'STUEB5D1C7B', 'ADMEB5D1C7B', 2, 'Mohamed', 'Dirie', NULL, 'Female', '2006-03-09', NULL, NULL, 'Somali', NULL, '+252659104589', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:16', '2025-12-21 06:24:08'),
(799, 799, 'STU25362E50', 'ADM25362E50', 2, 'Mohamed', 'Warsame', NULL, 'Male', '2005-03-02', NULL, NULL, 'Somali', NULL, '+252903840648', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:16', '2025-12-21 06:24:08'),
(800, 800, 'STUD2B18F16', 'ADMD2B18F16', 2, 'Fartun', 'Adam', NULL, 'Male', '2011-08-03', NULL, NULL, 'Somali', NULL, '+252665917584', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:16', '2025-12-21 06:24:08'),
(801, 801, 'STUABF17483', 'ADMABF17483', 2, 'Mohamed', 'Ali', NULL, 'Male', '2007-07-07', NULL, NULL, 'Somali', NULL, '+252665937272', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:16', '2025-12-21 06:24:08'),
(802, 802, 'STUEC935629', 'ADMEC935629', 2, 'Khadra', 'Ali', NULL, 'Male', '2009-07-11', NULL, NULL, 'Somali', NULL, '+252652392656', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:16', '2025-12-21 06:24:08'),
(803, 803, 'STU8BB5A632', 'ADM8BB5A632', 2, 'Mohamed', 'Sheikh', NULL, 'Female', '2010-04-13', NULL, NULL, 'Somali', NULL, '+252915103309', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:16', '2025-12-21 06:24:08'),
(804, 804, 'STUC4CADA89', 'ADMC4CADA89', 2, 'Ismail', 'Abdulle', NULL, 'Male', '2013-11-18', NULL, NULL, 'Somali', NULL, '+252618693236', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 2, 14, 'Graduated', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:17', '2025-12-21 06:24:08'),
(805, 805, 'STUAD7D100F', 'ADMAD7D100F', 2, 'Ahmed', 'Nur', NULL, 'Female', '2006-02-09', NULL, NULL, 'Somali', NULL, '+252658977716', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(806, 806, 'STU17B0BFCF', 'ADM17B0BFCF', 2, 'Ayaan', 'Ali', NULL, 'Female', '2008-06-23', NULL, NULL, 'Somali', NULL, '+252668565042', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(807, 807, 'STUA700BAB8', 'ADMA700BAB8', 2, 'Fartun', 'Ali', NULL, 'Female', '2008-05-03', NULL, NULL, 'Somali', NULL, '+252624038325', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(808, 808, 'STU6703FDCA', 'ADM6703FDCA', 2, 'Maryan', 'Adam', NULL, 'Female', '2011-06-17', NULL, NULL, 'Somali', NULL, '+252617372607', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(809, 809, 'STU5DC9C748', 'ADM5DC9C748', 2, 'Rahma', 'Ali', NULL, 'Female', '2015-01-27', NULL, NULL, 'Somali', NULL, '+252919216286', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(810, 810, 'STUB7E4AC9D', 'ADMB7E4AC9D', 2, 'Ali', 'Warsame', NULL, 'Male', '2013-02-25', NULL, NULL, 'Somali', NULL, '+252907262976', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(811, 811, 'STUE3AE7702', 'ADME3AE7702', 2, 'Ibrahim', 'Said', NULL, 'Male', '2015-07-08', NULL, NULL, 'Somali', NULL, '+252615584383', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(812, 812, 'STU6ABD97E7', 'ADM6ABD97E7', 2, 'Maryan', 'Farah', NULL, 'Female', '2012-04-24', NULL, NULL, 'Somali', NULL, '+252651396968', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(813, 813, 'STU9B57C0D0', 'ADM9B57C0D0', 2, 'Jama', 'Nur', NULL, 'Male', '2014-02-26', NULL, NULL, 'Somali', NULL, '+252634403477', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(814, 814, 'STUC7F814C8', 'ADMC7F814C8', 2, 'Khadra', 'Farah', NULL, 'Male', '2014-12-01', NULL, NULL, 'Somali', NULL, '+252618578957', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(815, 815, 'STUBCB9CB45', 'ADMBCB9CB45', 2, 'Mukhtar', 'Ismail', NULL, 'Male', '2006-04-04', NULL, NULL, 'Somali', NULL, '+252906838540', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(816, 816, 'STU2229A2AE', 'ADM2229A2AE', 2, 'Abdi', 'Dirie', NULL, 'Male', '2005-09-26', NULL, NULL, 'Somali', NULL, '+252662988358', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(817, 817, 'STUDBB45A29', 'ADMDBB45A29', 2, 'Jama', 'Sheikh', NULL, 'Female', '2015-01-06', NULL, NULL, 'Somali', NULL, '+252654839462', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(818, 818, 'STUBA2F1C53', 'ADMBA2F1C53', 2, 'Ahmed', 'Warsame', NULL, 'Female', '2013-01-19', NULL, NULL, 'Somali', NULL, '+252659785533', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(819, 819, 'STU72AB51A4', 'ADM72AB51A4', 2, 'Zamzam', 'Abdulle', NULL, 'Female', '2005-03-24', NULL, NULL, 'Somali', NULL, '+252613677447', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(820, 820, 'STU018451DA', 'ADM018451DA', 2, 'Rahma', 'Abdulle', NULL, 'Male', '2009-10-01', NULL, NULL, 'Somali', NULL, '+252614365700', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18'),
(821, 821, 'STU716D0AF9', 'ADM716D0AF9', 2, 'Ilhan', 'Ismail', NULL, 'Male', '2008-08-29', NULL, NULL, 'Somali', NULL, '+252617949552', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18'),
(822, 822, 'STUAB877B0F', 'ADMAB877B0F', 2, 'Asha', 'Yusuf', NULL, 'Male', '2011-07-10', NULL, NULL, 'Somali', NULL, '+252910118245', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18'),
(823, 823, 'STU61E6DECE', 'ADM61E6DECE', 2, 'Fartun', 'Dirie', NULL, 'Male', '2015-08-20', NULL, NULL, 'Somali', NULL, '+252610092679', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18'),
(824, 824, 'STU5C366A8D', 'ADM5C366A8D', 2, 'Yusuf', 'Hassan', NULL, 'Female', '2012-10-28', NULL, NULL, 'Somali', NULL, '+252632059910', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18'),
(825, 825, 'STUD22BCF01', 'ADMD22BCF01', 2, 'Mustafa', 'Roble', NULL, 'Female', '2009-05-25', NULL, NULL, 'Somali', NULL, '+252913276623', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18'),
(826, 826, 'STU3A407567', 'ADM3A407567', 2, 'Khadra', 'Jama', NULL, 'Male', '2007-02-28', NULL, NULL, 'Somali', NULL, '+252665918346', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18'),
(827, 827, 'STU3E60FEF8', 'ADM3E60FEF8', 2, 'Ibrahim', 'Muse', NULL, 'Male', '2005-09-29', NULL, NULL, 'Somali', NULL, '+252639414847', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18'),
(828, 828, 'STU22CE6687', 'ADM22CE6687', 2, 'Ilhan', 'Omar', NULL, 'Female', '2011-10-18', NULL, NULL, 'Somali', NULL, '+252625672889', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18'),
(829, 829, 'STU667B603D', 'ADM667B603D', 2, 'Nimco', 'Hassan', NULL, 'Male', '2012-05-16', NULL, NULL, 'Somali', NULL, '+252909378524', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18'),
(830, 830, 'STUF498093C', 'ADMF498093C', 2, 'Rahma', 'Yusuf', NULL, 'Female', '2015-09-20', NULL, NULL, 'Somali', NULL, '+252913674454', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18'),
(831, 831, 'STUD54443F8', 'ADMD54443F8', 2, 'Mohamed', 'Dirie', NULL, 'Female', '2011-10-06', NULL, NULL, 'Somali', NULL, '+252626023687', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18'),
(832, 832, 'STU11091F8A', 'ADM11091F8A', 2, 'Safiya', 'Mohamed', NULL, 'Male', '2013-02-09', NULL, NULL, 'Somali', NULL, '+252903163126', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18'),
(833, 833, 'STU285E401E', 'ADM285E401E', 2, 'Layla', 'Guled', NULL, 'Male', '2011-12-22', NULL, NULL, 'Somali', NULL, '+252620536624', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18'),
(834, 834, 'STU72B63085', 'ADM72B63085', 2, 'Ali', 'Nur', NULL, 'Male', '2009-04-06', NULL, NULL, 'Somali', NULL, '+252919911090', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18'),
(835, 835, 'STUFD04B282', 'ADMFD04B282', 2, 'Khadra', 'Abdulle', NULL, 'Female', '2015-11-06', NULL, NULL, 'Somali', NULL, '+252653982356', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(836, 836, 'STU59CC1804', 'ADM59CC1804', 2, 'Ifrah', 'Ismail', NULL, 'Male', '2012-01-14', NULL, NULL, 'Somali', NULL, '+252909617734', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(837, 837, 'STUA834CE3E', 'ADMA834CE3E', 2, 'Mukhtar', 'Abdullahi', NULL, 'Female', '2005-03-28', NULL, NULL, 'Somali', NULL, '+252668620603', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(838, 838, 'STUEA0E941D', 'ADMEA0E941D', 2, 'Ali', 'Abdulle', NULL, 'Female', '2015-08-27', NULL, NULL, 'Somali', NULL, '+252633577873', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(839, 839, 'STU8AAF5189', 'ADM8AAF5189', 2, 'Omar', 'Mohamed', NULL, 'Female', '2015-04-14', NULL, NULL, 'Somali', NULL, '+252666469371', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(840, 840, 'STU6CB02FA8', 'ADM6CB02FA8', 2, 'Asha', 'Dirie', NULL, 'Male', '2006-10-18', NULL, NULL, 'Somali', NULL, '+252626995936', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(841, 841, 'STU2364050F', 'ADM2364050F', 2, 'Abdi', 'Farah', NULL, 'Male', '2010-04-18', NULL, NULL, 'Somali', NULL, '+252610883358', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(842, 842, 'STUD3195BA0', 'ADMD3195BA0', 2, 'Sagal', 'Mohamed', NULL, 'Female', '2005-03-17', NULL, NULL, 'Somali', NULL, '+252638219923', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 5, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(843, 843, 'STU0ED2E1AC', 'ADM0ED2E1AC', 2, 'Hassan', 'Mohamed', NULL, 'Male', '2006-02-26', NULL, NULL, 'Somali', NULL, '+252632232144', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(844, 844, 'STU8E3D4623', 'ADM8E3D4623', 2, 'Fartun', 'Ismail', NULL, 'Male', '2007-09-30', NULL, NULL, 'Somali', NULL, '+252903942733', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(845, 845, 'STUEF05AB3E', 'ADMEF05AB3E', 2, 'Sagal', 'Said', NULL, 'Male', '2006-06-19', NULL, NULL, 'Somali', NULL, '+252612544409', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(846, 846, 'STU80BE4F4C', 'ADM80BE4F4C', 2, 'Najma', 'Abdulle', NULL, 'Male', '2005-12-25', NULL, NULL, 'Somali', NULL, '+252614587525', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(847, 847, 'STU2C2F17FC', 'ADM2C2F17FC', 2, 'Mahad', 'Osman', NULL, 'Male', '2015-03-26', NULL, NULL, 'Somali', NULL, '+252615549908', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(848, 848, 'STU0D16DF14', 'ADM0D16DF14', 2, 'Yusuf', 'Abdulle', NULL, 'Female', '2006-09-07', NULL, NULL, 'Somali', NULL, '+252903328014', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(849, 849, 'STUAC48A19F', 'ADMAC48A19F', 2, 'Ahmed', 'Ali', NULL, 'Female', '2005-03-06', NULL, NULL, 'Somali', NULL, '+252903889777', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(850, 850, 'STU84776433', 'ADM84776433', 2, 'Asha', 'Yusuf', NULL, 'Male', '2013-11-11', NULL, NULL, 'Somali', NULL, '+252630217393', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(851, 851, 'STUABCFB35E', 'ADMABCFB35E', 2, 'Ayaan', 'Abdullahi', NULL, 'Female', '2006-12-18', NULL, NULL, 'Somali', NULL, '+252662900849', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(852, 852, 'STUDEFBB9D4', 'ADMDEFBB9D4', 2, 'Fartun', 'Hassan', NULL, 'Female', '2007-03-03', NULL, NULL, 'Somali', NULL, '+252636354198', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(853, 853, 'STU51CD8020', 'ADM51CD8020', 2, 'Mohamed', 'Hassan', NULL, 'Female', '2005-05-23', NULL, NULL, 'Somali', NULL, '+252626432492', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(854, 854, 'STUAF0AB189', 'ADMAF0AB189', 2, 'Ayaan', 'Muse', NULL, 'Female', '2014-01-13', NULL, NULL, 'Somali', NULL, '+252913647342', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(855, 855, 'STU7D305C52', 'ADM7D305C52', 2, 'Ismail', 'Nur', NULL, 'Male', '2015-08-08', NULL, NULL, 'Somali', NULL, '+252668244565', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(856, 856, 'STUEE87771C', 'ADMEE87771C', 2, 'Mukhtar', 'Roble', NULL, 'Female', '2006-05-30', NULL, NULL, 'Somali', NULL, '+252615123672', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(857, 857, 'STUADF82BBB', 'ADMADF82BBB', 2, 'Sagal', 'Omar', NULL, 'Male', '2009-12-19', NULL, NULL, 'Somali', NULL, '+252625627091', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(858, 858, 'STUE4AE6C8A', 'ADME4AE6C8A', 2, 'Ilhan', 'Nur', NULL, 'Male', '2015-02-24', NULL, NULL, 'Somali', NULL, '+252627496671', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(859, 859, 'STU07205FCF', 'ADM07205FCF', 2, 'Fartun', 'Warsame', NULL, 'Male', '2014-08-12', NULL, NULL, 'Somali', NULL, '+252631785006', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(860, 860, 'STUF14D01F4', 'ADMF14D01F4', 2, 'Layla', 'Sheikh', NULL, 'Female', '2008-09-06', NULL, NULL, 'Somali', NULL, '+252639686134', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(861, 861, 'STU7525A8F3', 'ADM7525A8F3', 2, 'Ali', 'Yusuf', NULL, 'Female', '2006-05-26', NULL, NULL, 'Somali', NULL, '+252663430004', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(862, 862, 'STUCA9D6385', 'ADMCA9D6385', 2, 'Ilhan', 'Abdulle', NULL, 'Male', '2011-09-27', NULL, NULL, 'Somali', NULL, '+252653045954', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(863, 863, 'STU01A906EC', 'ADM01A906EC', 2, 'Fartun', 'Warsame', NULL, 'Female', '2009-09-06', NULL, NULL, 'Somali', NULL, '+252622486221', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(864, 864, 'STUF3093A70', 'ADMF3093A70', 2, 'Hassan', 'Farah', NULL, 'Female', '2007-10-04', NULL, NULL, 'Somali', NULL, '+252624311620', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(865, 865, 'STU0E4352F0', 'ADM0E4352F0', 2, 'Ibrahim', 'Muse', NULL, 'Female', '2012-01-08', NULL, NULL, 'Somali', NULL, '+252910923753', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(866, 866, 'STUF93F32A9', 'ADMF93F32A9', 2, 'Rahma', 'Sheikh', NULL, 'Male', '2005-01-10', NULL, NULL, 'Somali', NULL, '+252622243321', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(867, 867, 'STUF2519B62', 'ADMF2519B62', 2, 'Mukhtar', 'Jama', NULL, 'Male', '2005-01-07', NULL, NULL, 'Somali', NULL, '+252907145906', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(868, 868, 'STU778183D5', 'ADM778183D5', 2, 'Ali', 'Warsame', NULL, 'Female', '2010-02-19', NULL, NULL, 'Somali', NULL, '+252631874967', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(869, 869, 'STU0B7DAB05', 'ADM0B7DAB05', 2, 'Sahra', 'Ali', NULL, 'Male', '2015-12-04', NULL, NULL, 'Somali', NULL, '+252619757298', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(870, 870, 'STU2C3AD472', 'ADM2C3AD472', 2, 'Omar', 'Ismail', NULL, 'Female', '2014-03-18', NULL, NULL, 'Somali', NULL, '+252614211393', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(871, 871, 'STUB36E7F28', 'ADMB36E7F28', 2, 'Sagal', 'Yusuf', NULL, 'Female', '2014-10-07', NULL, NULL, 'Somali', NULL, '+252655740755', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(872, 872, 'STU7F7D1FE1', 'ADM7F7D1FE1', 2, 'Mukhtar', 'Dirie', NULL, 'Male', '2007-07-16', NULL, NULL, 'Somali', NULL, '+252904681729', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(873, 873, 'STU0DFD1375', 'ADM0DFD1375', 2, 'Najma', 'Omar', NULL, 'Female', '2012-10-16', NULL, NULL, 'Somali', NULL, '+252913061753', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(874, 874, 'STU8446C9B9', 'ADM8446C9B9', 2, 'Safiya', 'Adam', NULL, 'Female', '2010-07-17', NULL, NULL, 'Somali', NULL, '+252635842833', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(875, 875, 'STUFC407089', 'ADMFC407089', 2, 'Yusuf', 'Hassan', NULL, 'Female', '2014-03-02', NULL, NULL, 'Somali', NULL, '+252914831502', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(876, 876, 'STU2D07F30D', 'ADM2D07F30D', 2, 'Omar', 'Abdullahi', NULL, 'Male', '2007-06-08', NULL, NULL, 'Somali', NULL, '+252668077782', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(877, 877, 'STU2C7179CB', 'ADM2C7179CB', 2, 'Maryan', 'Abdullahi', NULL, 'Female', '2014-11-04', NULL, NULL, 'Somali', NULL, '+252630233100', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(878, 878, 'STU0109FFAB', 'ADM0109FFAB', 2, 'Layla', 'Osman', NULL, 'Male', '2015-11-20', NULL, NULL, 'Somali', NULL, '+252617008162', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(879, 879, 'STU7347AA1A', 'ADM7347AA1A', 2, 'Fartun', 'Warsame', NULL, 'Male', '2006-03-23', NULL, NULL, 'Somali', NULL, '+252669124099', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(880, 880, 'STU238CF64E', 'ADM238CF64E', 2, 'Khadra', 'Said', NULL, 'Male', '2009-10-31', NULL, NULL, 'Somali', NULL, '+252624515960', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(881, 881, 'STU8472619C', 'ADM8472619C', 2, 'Ilhan', 'Roble', NULL, 'Female', '2015-08-06', NULL, NULL, 'Somali', NULL, '+252659675843', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(882, 882, 'STU28C89672', 'ADM28C89672', 2, 'Jama', 'Omar', NULL, 'Female', '2006-08-31', NULL, NULL, 'Somali', NULL, '+252665638572', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(883, 883, 'STUA9F7A814', 'ADMA9F7A814', 2, 'Ilhan', 'Jama', NULL, 'Female', '2011-03-10', NULL, NULL, 'Somali', NULL, '+252660987510', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(884, 884, 'STU7C844298', 'ADM7C844298', 2, 'Rahma', 'Abdulle', NULL, 'Female', '2007-03-21', NULL, NULL, 'Somali', NULL, '+252915256062', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(885, 885, 'STU75CDA249', 'ADM75CDA249', 2, 'Farah', 'Guled', NULL, 'Female', '2011-12-24', NULL, NULL, 'Somali', NULL, '+252917837328', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(886, 886, 'STU76A98343', 'ADM76A98343', 2, 'Ibrahim', 'Dirie', NULL, 'Female', '2011-04-24', NULL, NULL, 'Somali', NULL, '+252666717649', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(887, 887, 'STU19FAEB61', 'ADM19FAEB61', 2, 'Mustafa', 'Abdulle', NULL, 'Male', '2010-07-11', NULL, NULL, 'Somali', NULL, '+252630489373', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(888, 888, 'STU69B0BA5B', 'ADM69B0BA5B', 2, 'Ibrahim', 'Sheikh', NULL, 'Male', '2011-08-13', NULL, NULL, 'Somali', NULL, '+252633444607', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(889, 889, 'STUD16459F9', 'ADMD16459F9', 2, 'Rahma', 'Farah', NULL, 'Female', '2005-09-17', NULL, NULL, 'Somali', NULL, '+252916737763', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(890, 890, 'STU89CEC2E3', 'ADM89CEC2E3', 2, 'Mohamed', 'Sheikh', NULL, 'Male', '2013-12-03', NULL, NULL, 'Somali', NULL, '+252667049675', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(891, 891, 'STU7BF50D0B', 'ADM7BF50D0B', 2, 'Ismail', 'Mohamed', NULL, 'Male', '2005-07-23', NULL, NULL, 'Somali', NULL, '+252614704013', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(892, 892, 'STU6E1E45A0', 'ADM6E1E45A0', 2, 'Fartun', 'Ali', NULL, 'Female', '2009-01-03', NULL, NULL, 'Somali', NULL, '+252667035136', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(893, 893, 'STUEC6A6199', 'ADMEC6A6199', 2, 'Omar', 'Adam', NULL, 'Female', '2008-12-14', NULL, NULL, 'Somali', NULL, '+252903035940', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(894, 894, 'STUD3BA6EF7', 'ADMD3BA6EF7', 2, 'Mohamed', 'Adam', NULL, 'Female', '2007-12-13', NULL, NULL, 'Somali', NULL, '+252627651916', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(895, 895, 'STUFEDFF211', 'ADMFEDFF211', 2, 'Hassan', 'Osman', NULL, 'Male', '2009-08-28', NULL, NULL, 'Somali', NULL, '+252653407673', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(896, 896, 'STU8E7D270E', 'ADM8E7D270E', 2, 'Ahmed', 'Mohamed', NULL, 'Male', '2010-03-10', NULL, NULL, 'Somali', NULL, '+252617968934', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(897, 897, 'STU16FF1058', 'ADM16FF1058', 2, 'Rahma', 'Sheikh', NULL, 'Female', '2010-03-09', NULL, NULL, 'Somali', NULL, '+252908919020', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(898, 898, 'STU649B4898', 'ADM649B4898', 2, 'Nimco', 'Omar', NULL, 'Male', '2015-07-18', NULL, NULL, 'Somali', NULL, '+252632263309', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(899, 899, 'STUE6F8A632', 'ADME6F8A632', 2, 'Rahma', 'Abdullahi', NULL, 'Male', '2015-07-29', NULL, NULL, 'Somali', NULL, '+252906481810', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(900, 900, 'STUD4FD0DA1', 'ADMD4FD0DA1', 2, 'Sahra', 'Ismail', NULL, 'Male', '2006-12-18', NULL, NULL, 'Somali', NULL, '+252624850741', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(901, 901, 'STU889ECAFC', 'ADM889ECAFC', 2, 'Najma', 'Abdullahi', NULL, 'Female', '2015-04-25', NULL, NULL, 'Somali', NULL, '+252664892704', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(902, 902, 'STU4E9C28B0', 'ADM4E9C28B0', 2, 'Maryan', 'Said', NULL, 'Male', '2011-09-29', NULL, NULL, 'Somali', NULL, '+252916226191', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(903, 903, 'STU6FFB6330', 'ADM6FFB6330', 2, 'Farah', 'Said', NULL, 'Male', '2012-01-12', NULL, NULL, 'Somali', NULL, '+252668711300', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(904, 904, 'STU0B89BCD9', 'ADM0B89BCD9', 2, 'Zamzam', 'Guled', NULL, 'Female', '2007-07-27', NULL, NULL, 'Somali', NULL, '+252913366212', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(905, 905, 'STUEB5B1A56', 'ADMEB5B1A56', 2, 'Ayaan', 'Guled', NULL, 'Female', '2007-10-28', NULL, NULL, 'Somali', NULL, '+252902095879', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23');
INSERT INTO `students` (`id`, `user_id`, `student_id`, `admission_no`, `branch_id`, `first_name`, `last_name`, `middle_name`, `gender`, `date_of_birth`, `blood_group`, `religion`, `nationality`, `email`, `phone`, `address`, `city`, `state`, `postal_code`, `photo`, `barcode`, `qr_code`, `admission_date`, `current_class_id`, `current_section_id`, `status`, `is_hostel`, `is_transport`, `previous_school`, `medical_conditions`, `allergies`, `special_needs`, `discount_type`, `discount_value`, `created_at`, `updated_at`) VALUES
(906, 906, 'STU9E59A427', 'ADM9E59A427', 2, 'Safiya', 'Ali', NULL, 'Female', '2012-04-15', NULL, NULL, 'Somali', NULL, '+252651102668', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(907, 907, 'STUECE34542', 'ADMECE34542', 2, 'Ayaan', 'Osman', NULL, 'Female', '2006-03-27', NULL, NULL, 'Somali', NULL, '+252665924907', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(908, 908, 'STUBBDE91E4', 'ADMBBDE91E4', 2, 'Mustafa', 'Mohamed', NULL, 'Female', '2012-10-27', NULL, NULL, 'Somali', NULL, '+252633409110', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(909, 909, 'STU3D886029', 'ADM3D886029', 2, 'Zamzam', 'Mohamed', NULL, 'Male', '2015-01-03', NULL, NULL, 'Somali', NULL, '+252632503019', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(910, 910, 'STU9C9E8C84', 'ADM9C9E8C84', 2, 'Fartun', 'Abdulle', NULL, 'Female', '2015-09-09', NULL, NULL, 'Somali', NULL, '+252628560412', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(911, 911, 'STUD52016D3', 'ADMD52016D3', 2, 'Fartun', 'Ali', NULL, 'Female', '2009-10-20', NULL, NULL, 'Somali', NULL, '+252664567564', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(912, 912, 'STU496E44DE', 'ADM496E44DE', 2, 'Omar', 'Warsame', NULL, 'Female', '2013-01-22', NULL, NULL, 'Somali', NULL, '+252915321874', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(913, 913, 'STU473D956D', 'ADM473D956D', 2, 'Nimco', 'Nur', NULL, 'Female', '2012-10-06', NULL, NULL, 'Somali', NULL, '+252610901110', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(914, 914, 'STUB1E7567D', 'ADMB1E7567D', 2, 'Zamzam', 'Mohamed', NULL, 'Female', '2011-07-31', NULL, NULL, 'Somali', NULL, '+252636773002', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(915, 915, 'STUECF58E87', 'ADMECF58E87', 2, 'Rahma', 'Roble', NULL, 'Male', '2015-03-14', NULL, NULL, 'Somali', NULL, '+252655555408', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(916, 916, 'STU6799C9FF', 'ADM6799C9FF', 2, 'Mohamed', 'Ismail', NULL, 'Male', '2005-05-23', NULL, NULL, 'Somali', NULL, '+252915624821', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(917, 917, 'STUF63D870D', 'ADMF63D870D', 2, 'Ilhan', 'Farah', NULL, 'Female', '2015-07-01', NULL, NULL, 'Somali', NULL, '+252621477988', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(918, 918, 'STU2FD06342', 'ADM2FD06342', 2, 'Maryan', 'Hassan', NULL, 'Male', '2009-05-24', NULL, NULL, 'Somali', NULL, '+252662281551', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(919, 919, 'STU25DA927E', 'ADM25DA927E', 2, 'Nimco', 'Jama', NULL, 'Male', '2009-12-18', NULL, NULL, 'Somali', NULL, '+252635695175', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(920, 920, 'STUBE067471', 'ADMBE067471', 2, 'Asha', 'Farah', NULL, 'Female', '2010-05-06', NULL, NULL, 'Somali', NULL, '+252658300288', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(921, 921, 'STUC12DB5C5', 'ADMC12DB5C5', 2, 'Jama', 'Omar', NULL, 'Male', '2014-09-11', NULL, NULL, 'Somali', NULL, '+252652015734', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(922, 922, 'STUCBB89372', 'ADMCBB89372', 2, 'Sagal', 'Ismail', NULL, 'Male', '2005-07-28', NULL, NULL, 'Somali', NULL, '+252912381894', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 3, 7, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(923, 923, 'STU7F161B09', 'ADM7F161B09', 2, 'Ismail', 'Guled', NULL, 'Male', '2012-10-07', NULL, NULL, 'Somali', NULL, '+252908289213', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(924, 924, 'STUD07E0296', 'ADMD07E0296', 2, 'Khadra', 'Said', NULL, 'Female', '2014-07-30', NULL, NULL, 'Somali', NULL, '+252668927643', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(925, 925, 'STU858021CA', 'ADM858021CA', 2, 'Omar', 'Mohamed', NULL, 'Male', '2012-05-23', NULL, NULL, 'Somali', NULL, '+252612641835', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(926, 926, 'STU56FBDFD1', 'ADM56FBDFD1', 2, 'Ilhan', 'Sheikh', NULL, 'Female', '2013-08-26', NULL, NULL, 'Somali', NULL, '+252618681392', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(927, 927, 'STUC69AB440', 'ADMC69AB440', 2, 'Fartun', 'Muse', NULL, 'Male', '2010-09-26', NULL, NULL, 'Somali', NULL, '+252669308047', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(928, 928, 'STU9C0A34D7', 'ADM9C0A34D7', 2, 'Yusuf', 'Osman', NULL, 'Male', '2012-09-13', NULL, NULL, 'Somali', NULL, '+252911809281', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(929, 929, 'STUE087BC7B', 'ADME087BC7B', 2, 'Hodan', 'Nur', NULL, 'Female', '2006-02-12', NULL, NULL, 'Somali', NULL, '+252651073068', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(930, 930, 'STU14DA375E', 'ADM14DA375E', 2, 'Sagal', 'Abdullahi', NULL, 'Male', '2006-05-30', NULL, NULL, 'Somali', NULL, '+252611120594', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(931, 931, 'STU51DE8B8E', 'ADM51DE8B8E', 2, 'Ali', 'Farah', NULL, 'Male', '2013-05-24', NULL, NULL, 'Somali', NULL, '+252616910279', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(932, 932, 'STU24DCCC4A', 'ADM24DCCC4A', 2, 'Ifrah', 'Sheikh', NULL, 'Male', '2009-03-31', NULL, NULL, 'Somali', NULL, '+252630088865', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(933, 933, 'STU67946437', 'ADM67946437', 2, 'Ismail', 'Guled', NULL, 'Male', '2006-06-23', NULL, NULL, 'Somali', NULL, '+252912078578', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(934, 934, 'STUA770F306', 'ADMA770F306', 2, 'Ahmed', 'Adam', NULL, 'Male', '2009-03-06', NULL, NULL, 'Somali', NULL, '+252666762998', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(935, 935, 'STU315EA027', 'ADM315EA027', 2, 'Mustafa', 'Sheikh', NULL, 'Male', '2007-06-16', NULL, NULL, 'Somali', NULL, '+252912857168', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(936, 936, 'STU2B04E4EF', 'ADM2B04E4EF', 2, 'Ifrah', 'Nur', NULL, 'Male', '2014-01-14', NULL, NULL, 'Somali', NULL, '+252914225620', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(937, 937, 'STU0E3129B0', 'ADM0E3129B0', 2, 'Ayaan', 'Jama', NULL, 'Female', '2008-12-11', NULL, NULL, 'Somali', NULL, '+252909213942', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(938, 938, 'STU71C41096', 'ADM71C41096', 2, 'Asha', 'Omar', NULL, 'Female', '2015-10-28', NULL, NULL, 'Somali', NULL, '+252917516066', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(939, 939, 'STU938D0F4D', 'ADM938D0F4D', 2, 'Ayaan', 'Abdulle', NULL, 'Male', '2007-08-05', NULL, NULL, 'Somali', NULL, '+252660500872', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(940, 940, 'STU301B419F', 'ADM301B419F', 2, 'Ismail', 'Abdulle', NULL, 'Male', '2013-04-30', NULL, NULL, 'Somali', NULL, '+252617997534', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(941, 941, 'STU145A21D5', 'ADM145A21D5', 2, 'Mustafa', 'Guled', NULL, 'Male', '2010-03-03', NULL, NULL, 'Somali', NULL, '+252626946393', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(942, 942, 'STUF621A718', 'ADMF621A718', 2, 'Ali', 'Mohamed', NULL, 'Male', '2012-09-16', NULL, NULL, 'Somali', NULL, '+252907555885', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(943, 943, 'STU7EC1395A', 'ADM7EC1395A', 2, 'Ifrah', 'Jama', NULL, 'Male', '2013-03-17', NULL, NULL, 'Somali', NULL, '+252631239453', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(944, 944, 'STU2C6E22F2', 'ADM2C6E22F2', 2, 'Ahmed', 'Abdullahi', NULL, 'Female', '2005-11-29', NULL, NULL, 'Somali', NULL, '+252659597369', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(945, 945, 'STU32139B59', 'ADM32139B59', 2, 'Omar', 'Adam', NULL, 'Female', '2007-02-15', NULL, NULL, 'Somali', NULL, '+252911450493', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(946, 946, 'STU23274E66', 'ADM23274E66', 2, 'Ismail', 'Roble', NULL, 'Male', '2005-04-08', NULL, NULL, 'Somali', NULL, '+252625559326', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(947, 947, 'STU2604E495', 'ADM2604E495', 2, 'Layla', 'Guled', NULL, 'Female', '2007-04-21', NULL, NULL, 'Somali', NULL, '+252616334550', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(948, 948, 'STUADD9BE1E', 'ADMADD9BE1E', 2, 'Ilhan', 'Dirie', NULL, 'Female', '2015-06-23', NULL, NULL, 'Somali', NULL, '+252905121488', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(949, 949, 'STU302C1D21', 'ADM302C1D21', 2, 'Rahma', 'Dirie', NULL, 'Male', '2005-03-31', NULL, NULL, 'Somali', NULL, '+252635979310', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(950, 950, 'STU90214839', 'ADM90214839', 2, 'Ali', 'Sheikh', NULL, 'Female', '2009-12-06', NULL, NULL, 'Somali', NULL, '+252624817546', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(951, 951, 'STU15E205CA', 'ADM15E205CA', 2, 'Safiya', 'Nur', NULL, 'Male', '2011-10-04', NULL, NULL, 'Somali', NULL, '+252900710085', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(952, 952, 'STU88719FB2', 'ADM88719FB2', 2, 'Ibrahim', 'Farah', NULL, 'Female', '2007-09-07', NULL, NULL, 'Somali', NULL, '+252620888788', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(953, 953, 'STU2EE73021', 'ADM2EE73021', 2, 'Najma', 'Hassan', NULL, 'Female', '2010-12-02', NULL, NULL, 'Somali', NULL, '+252667123349', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(954, 954, 'STU7E483695', 'ADM7E483695', 2, 'Sahra', 'Yusuf', NULL, 'Female', '2013-07-31', NULL, NULL, 'Somali', NULL, '+252909926285', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(955, 955, 'STUD678165E', 'ADMD678165E', 2, 'Yusuf', 'Abdulle', NULL, 'Male', '2014-11-17', NULL, NULL, 'Somali', NULL, '+252909279664', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(956, 956, 'STUB7419FE1', 'ADMB7419FE1', 2, 'Khadra', 'Ismail', NULL, 'Male', '2011-11-30', NULL, NULL, 'Somali', NULL, '+252637272361', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(957, 957, 'STUDE644463', 'ADMDE644463', 2, 'Mustafa', 'Roble', NULL, 'Female', '2013-09-27', NULL, NULL, 'Somali', NULL, '+252628372932', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(958, 958, 'STU9BAC6744', 'ADM9BAC6744', 2, 'Ifrah', 'Guled', NULL, 'Male', '2013-01-19', NULL, NULL, 'Somali', NULL, '+252651333588', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(959, 959, 'STUB99268FF', 'ADMB99268FF', 2, 'Asha', 'Jama', NULL, 'Male', '2009-10-20', NULL, NULL, 'Somali', NULL, '+252905711658', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(960, 960, 'STUF7E33262', 'ADMF7E33262', 2, 'Ifrah', 'Muse', NULL, 'Male', '2006-08-06', NULL, NULL, 'Somali', NULL, '+252662578508', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(961, 961, 'STUF8DC7EA2', 'ADMF8DC7EA2', 2, 'Mohamed', 'Jama', NULL, 'Male', '2013-06-21', NULL, NULL, 'Somali', NULL, '+252669260461', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 8, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(962, 962, 'STUAA85C8B3', 'ADMAA85C8B3', 2, 'Mahad', 'Sheikh', NULL, 'Female', '2007-05-26', NULL, NULL, 'Somali', NULL, '+252907959221', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(963, 963, 'STU934290E6', 'ADM934290E6', 2, 'Jama', 'Hassan', NULL, 'Female', '2005-04-05', NULL, NULL, 'Somali', NULL, '+252611755293', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(964, 964, 'STUAE3226B8', 'ADMAE3226B8', 2, 'Najma', 'Dirie', NULL, 'Female', '2009-05-09', NULL, NULL, 'Somali', NULL, '+252655759757', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(965, 965, 'STU0364EFC8', 'ADM0364EFC8', 2, 'Khadra', 'Mohamed', NULL, 'Male', '2008-12-28', NULL, NULL, 'Somali', NULL, '+252627450753', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(966, 966, 'STU1A775624', 'ADM1A775624', 2, 'Mukhtar', 'Warsame', NULL, 'Male', '2006-07-16', NULL, NULL, 'Somali', NULL, '+252611108812', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(967, 967, 'STU91771479', 'ADM91771479', 2, 'Zamzam', 'Hassan', NULL, 'Male', '2010-06-15', NULL, NULL, 'Somali', NULL, '+252903733760', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(968, 968, 'STU1CEF2C5B', 'ADM1CEF2C5B', 2, 'Mustafa', 'Osman', NULL, 'Male', '2010-05-17', NULL, NULL, 'Somali', NULL, '+252612494976', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(969, 969, 'STU4C95900E', 'ADM4C95900E', 2, 'Mahad', 'Ismail', NULL, 'Female', '2007-02-20', NULL, NULL, 'Somali', NULL, '+252900549271', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(970, 970, 'STUE6E35C26', 'ADME6E35C26', 2, 'Fartun', 'Nur', NULL, 'Male', '2014-08-04', NULL, NULL, 'Somali', NULL, '+252612527344', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(971, 971, 'STU55A17C66', 'ADM55A17C66', 2, 'Fartun', 'Roble', NULL, 'Female', '2005-03-23', NULL, NULL, 'Somali', NULL, '+252666282652', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(972, 972, 'STU4C444758', 'ADM4C444758', 2, 'Rahma', 'Muse', NULL, 'Male', '2015-04-24', NULL, NULL, 'Somali', NULL, '+252913354575', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(973, 973, 'STU727867E8', 'ADM727867E8', 2, 'Khadra', 'Abdullahi', NULL, 'Female', '2013-12-10', NULL, NULL, 'Somali', NULL, '+252664330496', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(974, 974, 'STU5A3DABCB', 'ADM5A3DABCB', 2, 'Yusuf', 'Mohamed', NULL, 'Male', '2015-10-28', NULL, NULL, 'Somali', NULL, '+252903848170', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(975, 975, 'STU8FAF79F5', 'ADM8FAF79F5', 2, 'Safiya', 'Abdulle', NULL, 'Female', '2015-05-03', NULL, NULL, 'Somali', NULL, '+252910293389', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(976, 976, 'STU9027A0F5', 'ADM9027A0F5', 2, 'Maryan', 'Warsame', NULL, 'Female', '2007-01-22', NULL, NULL, 'Somali', NULL, '+252623672407', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(977, 977, 'STU1A21A41F', 'ADM1A21A41F', 2, 'Mahad', 'Roble', NULL, 'Male', '2012-02-24', NULL, NULL, 'Somali', NULL, '+252623004633', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(978, 978, 'STU0F1F269F', 'ADM0F1F269F', 2, 'Ibrahim', 'Farah', NULL, 'Male', '2008-01-02', NULL, NULL, 'Somali', NULL, '+252620453582', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(979, 979, 'STUF5B49724', 'ADMF5B49724', 2, 'Asha', 'Hassan', NULL, 'Male', '2006-08-14', NULL, NULL, 'Somali', NULL, '+252901020917', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(980, 980, 'STUCC713B2C', 'ADMCC713B2C', 2, 'Mahad', 'Warsame', NULL, 'Male', '2009-05-29', NULL, NULL, 'Somali', NULL, '+252639031071', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(981, 981, 'STU3AE49046', 'ADM3AE49046', 2, 'Zamzam', 'Guled', NULL, 'Female', '2014-06-29', NULL, NULL, 'Somali', NULL, '+252625236571', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(982, 982, 'STU335B1A3B', 'ADM335B1A3B', 2, 'Mahad', 'Dirie', NULL, 'Female', '2010-02-24', NULL, NULL, 'Somali', NULL, '+252907749437', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(983, 983, 'STUB237B49C', 'ADMB237B49C', 2, 'Hassan', 'Abdullahi', NULL, 'Male', '2015-02-01', NULL, NULL, 'Somali', NULL, '+252635088510', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(984, 984, 'STU50C381CB', 'ADM50C381CB', 2, 'Maryan', 'Sheikh', NULL, 'Female', '2009-05-31', NULL, NULL, 'Somali', NULL, '+252619208264', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(985, 985, 'STU7D887C46', 'ADM7D887C46', 2, 'Omar', 'Muse', NULL, 'Male', '2015-08-31', NULL, NULL, 'Somali', NULL, '+252611765997', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(986, 986, 'STU257CB2FA', 'ADM257CB2FA', 2, 'Fartun', 'Warsame', NULL, 'Female', '2009-03-26', NULL, NULL, 'Somali', NULL, '+252622202235', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(987, 987, 'STU713ECF39', 'ADM713ECF39', 2, 'Mohamed', 'Warsame', NULL, 'Female', '2009-03-05', NULL, NULL, 'Somali', NULL, '+252904606332', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(988, 988, 'STUE6090F5E', 'ADME6090F5E', 2, 'Ifrah', 'Ali', NULL, 'Female', '2009-05-25', NULL, NULL, 'Somali', NULL, '+252906864102', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(989, 989, 'STUDFF99A35', 'ADMDFF99A35', 2, 'Hodan', 'Dirie', NULL, 'Male', '2015-06-25', NULL, NULL, 'Somali', NULL, '+252651780208', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(990, 990, 'STU6D0F9BC9', 'ADM6D0F9BC9', 2, 'Najma', 'Adam', NULL, 'Male', '2013-07-21', NULL, NULL, 'Somali', NULL, '+252669757964', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(991, 991, 'STU638B4B1B', 'ADM638B4B1B', 2, 'Abdi', 'Guled', NULL, 'Male', '2011-01-18', NULL, NULL, 'Somali', NULL, '+252918323323', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(992, 992, 'STU151BAB6C', 'ADM151BAB6C', 2, 'Ismail', 'Nur', NULL, 'Female', '2009-07-03', NULL, NULL, 'Somali', NULL, '+252651399092', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(993, 993, 'STU4422033D', 'ADM4422033D', 2, 'Zamzam', 'Omar', NULL, 'Female', '2015-06-04', NULL, NULL, 'Somali', NULL, '+252919544965', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(994, 994, 'STUA1BCD194', 'ADMA1BCD194', 2, 'Mahad', 'Sheikh', NULL, 'Male', '2007-12-03', NULL, NULL, 'Somali', NULL, '+252910300626', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(995, 995, 'STU8D21EE54', 'ADM8D21EE54', 2, 'Fartun', 'Ismail', NULL, 'Male', '2013-07-30', NULL, NULL, 'Somali', NULL, '+252636608305', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(996, 996, 'STU397515DA', 'ADM397515DA', 2, 'Zamzam', 'Farah', NULL, 'Female', '2011-03-24', NULL, NULL, 'Somali', NULL, '+252919725865', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(997, 997, 'STUA7A75B8B', 'ADMA7A75B8B', 2, 'Asha', 'Farah', NULL, 'Female', '2012-12-31', NULL, NULL, 'Somali', NULL, '+252667513067', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(998, 998, 'STU9541B6B3', 'ADM9541B6B3', 2, 'Ali', 'Nur', NULL, 'Female', '2015-07-14', NULL, NULL, 'Somali', NULL, '+252918746730', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(999, 999, 'STUE2FEA4C2', 'ADME2FEA4C2', 2, 'Mukhtar', 'Abdullahi', NULL, 'Female', '2009-08-29', NULL, NULL, 'Somali', NULL, '+252660542226', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(1000, 1000, 'STUB9226546', 'ADMB9226546', 2, 'Sagal', 'Said', NULL, 'Female', '2010-08-04', NULL, NULL, 'Somali', NULL, '+252900054183', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(1001, 1001, 'STU2A5DCD85', 'ADM2A5DCD85', 2, 'Hassan', 'Abdulle', NULL, 'Male', '2009-02-21', NULL, NULL, 'Somali', NULL, '+252668423496', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 9, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(1002, 1002, 'STUC087E0E9', 'ADMC087E0E9', 2, 'Ali', 'Hassan', NULL, 'Female', '2012-11-26', NULL, NULL, 'Somali', NULL, '+252908630453', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(1003, 1003, 'STU1EA3C2A6', 'ADM1EA3C2A6', 2, 'Sagal', 'Omar', NULL, 'Male', '2015-11-16', NULL, NULL, 'Somali', NULL, '+252637964608', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(1004, 1004, 'STU72254880', 'ADM72254880', 2, 'Nimco', 'Osman', NULL, 'Male', '2012-05-15', NULL, NULL, 'Somali', NULL, '+252627744817', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(1005, 1005, 'STU72DC6E34', 'ADM72DC6E34', 2, 'Nimco', 'Yusuf', NULL, 'Male', '2010-06-30', NULL, NULL, 'Somali', NULL, '+252915551352', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1006, 1006, 'STU9F49C5C2', 'ADM9F49C5C2', 2, 'Najma', 'Dirie', NULL, 'Female', '2007-07-24', NULL, NULL, 'Somali', NULL, '+252901463719', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1007, 1007, 'STU872A94BB', 'ADM872A94BB', 2, 'Ilhan', 'Omar', NULL, 'Male', '2013-03-08', NULL, NULL, 'Somali', NULL, '+252652144357', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1008, 1008, 'STU2C370E3B', 'ADM2C370E3B', 2, 'Najma', 'Nur', NULL, 'Male', '2010-03-31', NULL, NULL, 'Somali', NULL, '+252638504065', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1009, 1009, 'STUBE2D5B99', 'ADMBE2D5B99', 2, 'Hodan', 'Jama', NULL, 'Male', '2008-09-24', NULL, NULL, 'Somali', NULL, '+252906105399', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1010, 1010, 'STU39670518', 'ADM39670518', 2, 'Abdi', 'Ismail', NULL, 'Female', '2007-02-04', NULL, NULL, 'Somali', NULL, '+252910450012', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1011, 1011, 'STU4C8ADD32', 'ADM4C8ADD32', 2, 'Hassan', 'Nur', NULL, 'Female', '2014-11-15', NULL, NULL, 'Somali', NULL, '+252632601308', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1012, 1012, 'STU6133C7D5', 'ADM6133C7D5', 2, 'Khadra', 'Adam', NULL, 'Male', '2005-04-03', NULL, NULL, 'Somali', NULL, '+252901834631', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1013, 1013, 'STUD30AD917', 'ADMD30AD917', 2, 'Sagal', 'Said', NULL, 'Male', '2007-02-19', NULL, NULL, 'Somali', NULL, '+252912228113', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1014, 1014, 'STU5856E250', 'ADM5856E250', 2, 'Ahmed', 'Ismail', NULL, 'Male', '2007-02-05', NULL, NULL, 'Somali', NULL, '+252654276019', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1015, 1015, 'STU36374686', 'ADM36374686', 2, 'Ismail', 'Nur', NULL, 'Female', '2014-01-31', NULL, NULL, 'Somali', NULL, '+252623595782', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1016, 1016, 'STU40EEFB49', 'ADM40EEFB49', 2, 'Ayaan', 'Roble', NULL, 'Female', '2005-11-29', NULL, NULL, 'Somali', NULL, '+252612846884', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1017, 1017, 'STU1053796B', 'ADM1053796B', 2, 'Nimco', 'Omar', NULL, 'Female', '2007-02-07', NULL, NULL, 'Somali', NULL, '+252615820698', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1018, 1018, 'STUF0AA4CD4', 'ADMF0AA4CD4', 2, 'Ahmed', 'Warsame', NULL, 'Female', '2015-04-03', NULL, NULL, 'Somali', NULL, '+252902140843', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1019, 1019, 'STU3402FEB5', 'ADM3402FEB5', 2, 'Hassan', 'Osman', NULL, 'Female', '2009-12-27', NULL, NULL, 'Somali', NULL, '+252918798011', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1020, 1020, 'STU4863AC48', 'ADM4863AC48', 2, 'Mohamed', 'Osman', NULL, 'Male', '2006-02-19', NULL, NULL, 'Somali', NULL, '+252614502513', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1021, 1021, 'STU8182A52B', 'ADM8182A52B', 2, 'Maryan', 'Hassan', NULL, 'Male', '2014-04-08', NULL, NULL, 'Somali', NULL, '+252910048570', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1022, 1022, 'STU5A2A5043', 'ADM5A2A5043', 2, 'Ismail', 'Omar', NULL, 'Female', '2013-08-07', NULL, NULL, 'Somali', NULL, '+252613337995', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1023, 1023, 'STU521C3199', 'ADM521C3199', 2, 'Jama', 'Farah', NULL, 'Female', '2007-10-17', NULL, NULL, 'Somali', NULL, '+252624614430', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1024, 1024, 'STUC63446BD', 'ADMC63446BD', 2, 'Ilhan', 'Abdulle', NULL, 'Male', '2014-12-15', NULL, NULL, 'Somali', NULL, '+252611885880', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1025, 1025, 'STU0818CCED', 'ADM0818CCED', 2, 'Mahad', 'Osman', NULL, 'Female', '2008-07-31', NULL, NULL, 'Somali', NULL, '+252903885854', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1026, 1026, 'STU57899258', 'ADM57899258', 2, 'Sahra', 'Yusuf', NULL, 'Female', '2014-07-21', NULL, NULL, 'Somali', NULL, '+252618915413', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1027, 1027, 'STUB33CBA1A', 'ADMB33CBA1A', 2, 'Farah', 'Said', NULL, 'Female', '2005-01-07', NULL, NULL, 'Somali', NULL, '+252918448533', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1028, 1028, 'STU67BB195A', 'ADM67BB195A', 2, 'Nimco', 'Sheikh', NULL, 'Male', '2015-05-17', NULL, NULL, 'Somali', NULL, '+252668678489', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1029, 1029, 'STU756CDD42', 'ADM756CDD42', 2, 'Najma', 'Hassan', NULL, 'Female', '2010-10-04', NULL, NULL, 'Somali', NULL, '+252906705766', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1030, 1030, 'STU54786ACA', 'ADM54786ACA', 2, 'Mahad', 'Adam', NULL, 'Male', '2014-08-27', NULL, NULL, 'Somali', NULL, '+252657132564', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1031, 1031, 'STUA31C624B', 'ADMA31C624B', 2, 'Sahra', 'Farah', NULL, 'Female', '2015-06-30', NULL, NULL, 'Somali', NULL, '+252659261078', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1032, 1032, 'STU65EB14F5', 'ADM65EB14F5', 2, 'Ayaan', 'Roble', NULL, 'Male', '2015-10-30', NULL, NULL, 'Somali', NULL, '+252651846540', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1033, 1033, 'STUF268B73A', 'ADMF268B73A', 2, 'Sagal', 'Dirie', NULL, 'Female', '2013-08-15', NULL, NULL, 'Somali', NULL, '+252611923634', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1034, 1034, 'STU0B686444', 'ADM0B686444', 2, 'Jama', 'Roble', NULL, 'Male', '2014-11-19', NULL, NULL, 'Somali', NULL, '+252667161219', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1035, 1035, 'STU11BE39F0', 'ADM11BE39F0', 2, 'Rahma', 'Mohamed', NULL, 'Female', '2010-06-02', NULL, NULL, 'Somali', NULL, '+252655289761', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32'),
(1036, 1036, 'STU9ACEFF2A', 'ADM9ACEFF2A', 2, 'Ali', 'Osman', NULL, 'Male', '2015-08-22', NULL, NULL, 'Somali', NULL, '+252614380282', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32'),
(1037, 1037, 'STUA9466205', 'ADMA9466205', 2, 'Mukhtar', 'Said', NULL, 'Male', '2010-05-21', NULL, NULL, 'Somali', NULL, '+252668023788', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32'),
(1038, 1038, 'STUF4796571', 'ADMF4796571', 2, 'Nimco', 'Abdullahi', NULL, 'Male', '2011-12-01', NULL, NULL, 'Somali', NULL, '+252668109632', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32'),
(1039, 1039, 'STUEFF6EDA3', 'ADMEFF6EDA3', 2, 'Ahmed', 'Farah', NULL, 'Female', '2015-12-24', NULL, NULL, 'Somali', NULL, '+252665978086', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32'),
(1040, 1040, 'STU8490DBC4', 'ADM8490DBC4', 2, 'Mukhtar', 'Roble', NULL, 'Female', '2009-12-12', NULL, NULL, 'Somali', NULL, '+252903410292', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32'),
(1041, 1041, 'STU156D12FF', 'ADM156D12FF', 2, 'Ayaan', 'Said', NULL, 'Male', '2014-09-08', NULL, NULL, 'Somali', NULL, '+252652156294', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 4, 10, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32'),
(1042, 1042, 'STUEB9D5743', 'ADMEB9D5743', 2, 'Ayaan', 'Dirie', NULL, 'Female', '2013-08-08', NULL, NULL, 'Somali', NULL, '+252615659543', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32'),
(1043, 1043, 'STUC2FF424D', 'ADMC2FF424D', 2, 'Mahad', 'Mohamed', NULL, 'Female', '2008-07-24', NULL, NULL, 'Somali', NULL, '+252618365009', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32'),
(1044, 1044, 'STU8CF7F794', 'ADM8CF7F794', 2, 'Zamzam', 'Hassan', NULL, 'Male', '2013-12-02', NULL, NULL, 'Somali', NULL, '+252666864154', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32'),
(1045, 1045, 'STU5185A8AD', 'ADM5185A8AD', 2, 'Ilhan', 'Roble', NULL, 'Male', '2010-08-06', NULL, NULL, 'Somali', NULL, '+252629651618', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32'),
(1046, 1046, 'STUD512DE1B', 'ADMD512DE1B', 2, 'Ayaan', 'Said', NULL, 'Male', '2007-08-04', NULL, NULL, 'Somali', NULL, '+252916327618', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32'),
(1047, 1047, 'STU0F673F44', 'ADM0F673F44', 2, 'Ayaan', 'Ali', NULL, 'Male', '2007-05-10', NULL, NULL, 'Somali', NULL, '+252909321189', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32'),
(1048, 1048, 'STU5215646E', 'ADM5215646E', 2, 'Maryan', 'Dirie', NULL, 'Female', '2006-02-11', NULL, NULL, 'Somali', NULL, '+252911012375', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32'),
(1049, 1049, 'STUE4C0AE8F', 'ADME4C0AE8F', 2, 'Fartun', 'Muse', NULL, 'Female', '2010-05-28', NULL, NULL, 'Somali', NULL, '+252913686046', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32'),
(1050, 1050, 'STU2C031DD5', 'ADM2C031DD5', 2, 'Najma', 'Muse', NULL, 'Female', '2011-08-06', NULL, NULL, 'Somali', NULL, '+252650176058', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1051, 1051, 'STU0D30F045', 'ADM0D30F045', 2, 'Layla', 'Abdulle', NULL, 'Male', '2009-10-24', NULL, NULL, 'Somali', NULL, '+252663617741', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1052, 1052, 'STU9DCF5076', 'ADM9DCF5076', 2, 'Ibrahim', 'Mohamed', NULL, 'Female', '2015-03-27', NULL, NULL, 'Somali', NULL, '+252625211787', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1053, 1053, 'STU226C3DC8', 'ADM226C3DC8', 2, 'Ayaan', 'Abdullahi', NULL, 'Female', '2010-07-31', NULL, NULL, 'Somali', NULL, '+252906946797', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1054, 1054, 'STU9685AF3D', 'ADM9685AF3D', 2, 'Ismail', 'Farah', NULL, 'Female', '2005-04-26', NULL, NULL, 'Somali', NULL, '+252656321299', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1055, 1055, 'STU896DFED7', 'ADM896DFED7', 2, 'Nimco', 'Said', NULL, 'Female', '2013-09-13', NULL, NULL, 'Somali', NULL, '+252662154267', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1056, 1056, 'STU568017DE', 'ADM568017DE', 2, 'Ibrahim', 'Farah', NULL, 'Female', '2008-11-06', NULL, NULL, 'Somali', NULL, '+252613987335', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1057, 1057, 'STU10564CDA', 'ADM10564CDA', 2, 'Mukhtar', 'Dirie', NULL, 'Male', '2012-01-28', NULL, NULL, 'Somali', NULL, '+252629570788', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33');
INSERT INTO `students` (`id`, `user_id`, `student_id`, `admission_no`, `branch_id`, `first_name`, `last_name`, `middle_name`, `gender`, `date_of_birth`, `blood_group`, `religion`, `nationality`, `email`, `phone`, `address`, `city`, `state`, `postal_code`, `photo`, `barcode`, `qr_code`, `admission_date`, `current_class_id`, `current_section_id`, `status`, `is_hostel`, `is_transport`, `previous_school`, `medical_conditions`, `allergies`, `special_needs`, `discount_type`, `discount_value`, `created_at`, `updated_at`) VALUES
(1058, 1058, 'STUDAFC47CF', 'ADMDAFC47CF', 2, 'Najma', 'Osman', NULL, 'Male', '2008-05-24', NULL, NULL, 'Somali', NULL, '+252912263347', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1059, 1059, 'STU0DD44A5B', 'ADM0DD44A5B', 2, 'Safiya', 'Mohamed', NULL, 'Male', '2013-11-14', NULL, NULL, 'Somali', NULL, '+252659740207', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1060, 1060, 'STU0100DE10', 'ADM0100DE10', 2, 'Mohamed', 'Osman', NULL, 'Male', '2010-07-27', NULL, NULL, 'Somali', NULL, '+252657596791', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1061, 1061, 'STU85CF3303', 'ADM85CF3303', 2, 'Rahma', 'Ali', NULL, 'Female', '2013-05-07', NULL, NULL, 'Somali', NULL, '+252635385413', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1062, 1062, 'STU474085C9', 'ADM474085C9', 2, 'Mohamed', 'Said', NULL, 'Male', '2006-12-23', NULL, NULL, 'Somali', NULL, '+252631590605', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1063, 1063, 'STUAA2D71F2', 'ADMAA2D71F2', 2, 'Safiya', 'Warsame', NULL, 'Female', '2010-04-04', NULL, NULL, 'Somali', NULL, '+252905104792', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1064, 1064, 'STU1F07B87B', 'ADM1F07B87B', 2, 'Sahra', 'Abdulle', NULL, 'Female', '2015-05-01', NULL, NULL, 'Somali', NULL, '+252657264282', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1065, 1065, 'STU63335CAB', 'ADM63335CAB', 2, 'Hodan', 'Jama', NULL, 'Male', '2007-08-31', NULL, NULL, 'Somali', NULL, '+252624185885', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1066, 1066, 'STU5F4957B5', 'ADM5F4957B5', 2, 'Rahma', 'Guled', NULL, 'Female', '2005-08-06', NULL, NULL, 'Somali', NULL, '+252666441058', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:34', '2025-12-16 09:27:34'),
(1067, 1067, 'STU15E076D9', 'ADM15E076D9', 2, 'Ahmed', 'Yusuf', NULL, 'Male', '2007-01-25', NULL, NULL, 'Somali', NULL, '+252903106124', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:34', '2025-12-16 09:27:34'),
(1068, 1068, 'STUAA652A34', 'ADMAA652A34', 2, 'Ayaan', 'Ali', NULL, 'Male', '2014-06-26', NULL, NULL, 'Somali', NULL, '+252620232896', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:34', '2025-12-16 09:27:34'),
(1069, 1069, 'STUC6090E90', 'ADMC6090E90', 2, 'Rahma', 'Abdullahi', NULL, 'Male', '2006-09-17', NULL, NULL, 'Somali', NULL, '+252907553580', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:34', '2025-12-16 09:27:34'),
(1070, 1070, 'STUB0506F7B', 'ADMB0506F7B', 2, 'Sagal', 'Warsame', NULL, 'Female', '2011-09-14', NULL, NULL, 'Somali', NULL, '+252906048142', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:34', '2025-12-16 09:27:34'),
(1071, 1071, 'STU122C6154', 'ADM122C6154', 2, 'Fartun', 'Nur', NULL, 'Male', '2012-08-07', NULL, NULL, 'Somali', NULL, '+252905765442', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:34', '2025-12-16 09:27:34'),
(1072, 1072, 'STU9286BB76', 'ADM9286BB76', 2, 'Asha', 'Said', NULL, 'Male', '2009-03-21', NULL, NULL, 'Somali', NULL, '+252654439317', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:34', '2025-12-16 09:27:34'),
(1073, 1073, 'STUEF3AFD2D', 'ADMEF3AFD2D', 2, 'Layla', 'Abdullahi', NULL, 'Male', '2010-10-04', NULL, NULL, 'Somali', NULL, '+252652641192', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:34', '2025-12-16 09:27:34'),
(1074, 1074, 'STUE9737B2B', 'ADME9737B2B', 2, 'Layla', 'Abdullahi', NULL, 'Female', '2015-01-24', NULL, NULL, 'Somali', NULL, '+252903951751', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:34', '2025-12-16 09:27:34'),
(1075, 1075, 'STU19C72E80', 'ADM19C72E80', 2, 'Zamzam', 'Guled', NULL, 'Female', '2012-12-21', NULL, NULL, 'Somali', NULL, '+252666054114', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:34', '2025-12-16 09:27:34'),
(1076, 1076, 'STU93701A63', 'ADM93701A63', 2, 'Yusuf', 'Mohamed', NULL, 'Male', '2013-12-16', NULL, NULL, 'Somali', NULL, '+252612812608', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:34', '2025-12-16 09:27:34'),
(1077, 1077, 'STU44DEA0E4', 'ADM44DEA0E4', 2, 'Farah', 'Ali', NULL, 'Female', '2010-10-19', NULL, NULL, 'Somali', NULL, '+252638752393', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:34', '2025-12-16 09:27:34'),
(1078, 1078, 'STU788856F1', 'ADM788856F1', 2, 'Hassan', 'Hassan', NULL, 'Male', '2009-09-01', NULL, NULL, 'Somali', NULL, '+252622147475', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:34', '2025-12-16 09:27:34'),
(1079, 1079, 'STU3BC05609', 'ADM3BC05609', 2, 'Sagal', 'Omar', NULL, 'Female', '2008-02-14', NULL, NULL, 'Somali', NULL, '+252651390017', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:34', '2025-12-16 09:27:34'),
(1080, 1080, 'STU09CCAFB5', 'ADM09CCAFB5', 2, 'Safiya', 'Hassan', NULL, 'Female', '2011-02-15', NULL, NULL, 'Somali', NULL, '+252667656195', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1081, 1081, 'STU4ADB607E', 'ADM4ADB607E', 2, 'Omar', 'Mohamed', NULL, 'Male', '2005-10-26', NULL, NULL, 'Somali', NULL, '+252906096487', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 11, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1082, 1082, 'STUD1BBCAF5', 'ADMD1BBCAF5', 2, 'Mahad', 'Roble', NULL, 'Female', '2012-01-17', NULL, NULL, 'Somali', NULL, '+252636289904', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1083, 1083, 'STU7898008C', 'ADM7898008C', 2, 'Omar', 'Omar', NULL, 'Female', '2015-12-14', NULL, NULL, 'Somali', NULL, '+252664076405', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1084, 1084, 'STU2897C81B', 'ADM2897C81B', 2, 'Safiya', 'Adam', NULL, 'Female', '2015-06-21', NULL, NULL, 'Somali', NULL, '+252658403372', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1085, 1085, 'STUC9DFA770', 'ADMC9DFA770', 2, 'Omar', 'Said', NULL, 'Female', '2012-03-10', NULL, NULL, 'Somali', NULL, '+252614673760', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1086, 1086, 'STU0CE5D9C7', 'ADM0CE5D9C7', 2, 'Ayaan', 'Ismail', NULL, 'Female', '2012-04-22', NULL, NULL, 'Somali', NULL, '+252617984407', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1087, 1087, 'STUC53C4CE5', 'ADMC53C4CE5', 2, 'Asha', 'Dirie', NULL, 'Male', '2008-10-20', NULL, NULL, 'Somali', NULL, '+252909173979', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1088, 1088, 'STU72CF4EA0', 'ADM72CF4EA0', 2, 'Mustafa', 'Ismail', NULL, 'Female', '2014-04-28', NULL, NULL, 'Somali', NULL, '+252619270149', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1089, 1089, 'STU376C6D1F', 'ADM376C6D1F', 2, 'Ali', 'Said', NULL, 'Male', '2014-03-02', NULL, NULL, 'Somali', NULL, '+252614478022', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1090, 1090, 'STUBC7DC6B0', 'ADMBC7DC6B0', 2, 'Mahad', 'Nur', NULL, 'Male', '2005-12-19', NULL, NULL, 'Somali', NULL, '+252615761561', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1091, 1091, 'STU606A5C77', 'ADM606A5C77', 2, 'Ismail', 'Ismail', NULL, 'Female', '2006-01-27', NULL, NULL, 'Somali', NULL, '+252916441407', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1092, 1092, 'STU28E0506E', 'ADM28E0506E', 2, 'Ayaan', 'Abdulle', NULL, 'Male', '2012-02-02', NULL, NULL, 'Somali', NULL, '+252631543077', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1093, 1093, 'STU823A264E', 'ADM823A264E', 2, 'Ayaan', 'Jama', NULL, 'Male', '2005-10-13', NULL, NULL, 'Somali', NULL, '+252915798234', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1094, 1094, 'STU63E9666D', 'ADM63E9666D', 2, 'Khadra', 'Roble', NULL, 'Male', '2010-05-06', NULL, NULL, 'Somali', NULL, '+252619007594', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1095, 1095, 'STUAF225C2D', 'ADMAF225C2D', 2, 'Najma', 'Ali', NULL, 'Female', '2005-10-22', NULL, NULL, 'Somali', NULL, '+252635660789', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1096, 1096, 'STUD379D01F', 'ADMD379D01F', 2, 'Ahmed', 'Adam', NULL, 'Female', '2009-01-27', NULL, NULL, 'Somali', NULL, '+252624438282', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1097, 1097, 'STUE2B65276', 'ADME2B65276', 2, 'Farah', 'Sheikh', NULL, 'Female', '2005-02-02', NULL, NULL, 'Somali', NULL, '+252650592583', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1098, 1098, 'STUA76D1BA4', 'ADMA76D1BA4', 2, 'Layla', 'Muse', NULL, 'Male', '2005-12-31', NULL, NULL, 'Somali', NULL, '+252630806208', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1099, 1099, 'STU43879334', 'ADM43879334', 2, 'Omar', 'Sheikh', NULL, 'Female', '2005-01-03', NULL, NULL, 'Somali', NULL, '+252634340874', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1100, 1100, 'STU4F05E461', 'ADM4F05E461', 2, 'Fartun', 'Muse', NULL, 'Female', '2006-07-26', NULL, NULL, 'Somali', NULL, '+252654057284', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1101, 1101, 'STU21B46ECD', 'ADM21B46ECD', 2, 'Layla', 'Omar', NULL, 'Female', '2005-05-27', NULL, NULL, 'Somali', NULL, '+252916436656', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1102, 1102, 'STU44F523EB', 'ADM44F523EB', 2, 'Yusuf', 'Yusuf', NULL, 'Male', '2006-12-06', NULL, NULL, 'Somali', NULL, '+252619141341', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1103, 1103, 'STU69EECD16', 'ADM69EECD16', 2, 'Zamzam', 'Farah', NULL, 'Male', '2010-11-19', NULL, NULL, 'Somali', NULL, '+252630708406', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1104, 1104, 'STU64A95F0A', 'ADM64A95F0A', 2, 'Jama', 'Hassan', NULL, 'Male', '2005-11-06', NULL, NULL, 'Somali', NULL, '+252617386170', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1105, 1105, 'STUD8FB4771', 'ADMD8FB4771', 2, 'Layla', 'Farah', NULL, 'Male', '2009-01-04', NULL, NULL, 'Somali', NULL, '+252915481686', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1106, 1106, 'STUE1981730', 'ADME1981730', 2, 'Nimco', 'Ismail', NULL, 'Male', '2009-09-25', NULL, NULL, 'Somali', NULL, '+252913044385', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1107, 1107, 'STUE778FDD8', 'ADME778FDD8', 2, 'Asha', 'Nur', NULL, 'Female', '2013-10-19', NULL, NULL, 'Somali', NULL, '+252633399099', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1108, 1108, 'STUD5B16ACD', 'ADMD5B16ACD', 2, 'Abdi', 'Dirie', NULL, 'Female', '2010-11-03', NULL, NULL, 'Somali', NULL, '+252611456989', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1109, 1109, 'STUFDE7948F', 'ADMFDE7948F', 2, 'Khadra', 'Ali', NULL, 'Male', '2015-06-15', NULL, NULL, 'Somali', NULL, '+252658285871', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1110, 1110, 'STU1055DBE6', 'ADM1055DBE6', 2, 'Abdi', 'Sheikh', NULL, 'Male', '2006-03-14', NULL, NULL, 'Somali', NULL, '+252623008602', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1111, 1111, 'STUD9900464', 'ADMD9900464', 2, 'Mustafa', 'Warsame', NULL, 'Male', '2008-07-25', NULL, NULL, 'Somali', NULL, '+252662611654', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1112, 1112, 'STU79BE1AFD', 'ADM79BE1AFD', 2, 'Farah', 'Abdullahi', NULL, 'Male', '2011-04-19', NULL, NULL, 'Somali', NULL, '+252900943593', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1113, 1113, 'STUF7AF2035', 'ADMF7AF2035', 2, 'Sahra', 'Ali', NULL, 'Male', '2011-06-11', NULL, NULL, 'Somali', NULL, '+252636584043', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1114, 1114, 'STUCB5E2713', 'ADMCB5E2713', 2, 'Maryan', 'Warsame', NULL, 'Male', '2005-02-09', NULL, NULL, 'Somali', NULL, '+252637654900', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1115, 1115, 'STUFC9EF216', 'ADMFC9EF216', 2, 'Omar', 'Adam', NULL, 'Female', '2014-07-18', NULL, NULL, 'Somali', NULL, '+252660211323', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1116, 1116, 'STU63B91DB6', 'ADM63B91DB6', 2, 'Mukhtar', 'Said', NULL, 'Male', '2009-03-23', NULL, NULL, 'Somali', NULL, '+252626290465', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1117, 1117, 'STU424201A1', 'ADM424201A1', 2, 'Mahad', 'Warsame', NULL, 'Female', '2012-05-17', NULL, NULL, 'Somali', NULL, '+252664798056', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1118, 1118, 'STU3E6ED761', 'ADM3E6ED761', 2, 'Ali', 'Roble', NULL, 'Female', '2014-07-02', NULL, NULL, 'Somali', NULL, '+252658574493', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1119, 1119, 'STUE26778B7', 'ADME26778B7', 2, 'Ilhan', 'Abdulle', NULL, 'Male', '2006-03-05', NULL, NULL, 'Somali', NULL, '+252610040285', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1120, 1120, 'STU616836EE', 'ADM616836EE', 2, 'Hodan', 'Said', NULL, 'Male', '2008-01-09', NULL, NULL, 'Somali', NULL, '+252658334410', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1121, 1121, 'STU992508D8', 'ADM992508D8', 2, 'Ilhan', 'Ali', NULL, 'Male', '2009-02-27', NULL, NULL, 'Somali', NULL, '+252911718860', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 12, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1122, 1122, 'STUCB0B62A1', 'ADMCB0B62A1', 2, 'Ifrah', 'Mohamed', NULL, 'Female', '2012-08-05', NULL, NULL, 'Somali', NULL, '+252657882730', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1123, 1123, 'STU4F0AF6DA', 'ADM4F0AF6DA', 2, 'Farah', 'Dirie', NULL, 'Male', '2009-07-02', NULL, NULL, 'Somali', NULL, '+252617444344', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1124, 1124, 'STU83B5E03D', 'ADM83B5E03D', 2, 'Hassan', 'Farah', NULL, 'Male', '2014-04-20', NULL, NULL, 'Somali', NULL, '+252661256797', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1125, 1125, 'STU7D247A40', 'ADM7D247A40', 2, 'Ayaan', 'Said', NULL, 'Male', '2010-04-17', NULL, NULL, 'Somali', NULL, '+252914705540', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1126, 1126, 'STU0439B384', 'ADM0439B384', 2, 'Maryan', 'Abdullahi', NULL, 'Female', '2014-12-14', NULL, NULL, 'Somali', NULL, '+252913975610', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1127, 1127, 'STUE1D5A3D7', 'ADME1D5A3D7', 2, 'Ismail', 'Adam', NULL, 'Male', '2012-04-05', NULL, NULL, 'Somali', NULL, '+252613033609', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1128, 1128, 'STU6072F957', 'ADM6072F957', 2, 'Ibrahim', 'Osman', NULL, 'Male', '2005-04-12', NULL, NULL, 'Somali', NULL, '+252622283481', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1129, 1129, 'STU9A3B45ED', 'ADM9A3B45ED', 2, 'Ayaan', 'Jama', NULL, 'Female', '2008-10-08', NULL, NULL, 'Somali', NULL, '+252915404815', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1130, 1130, 'STU89898E63', 'ADM89898E63', 2, 'Asha', 'Jama', NULL, 'Male', '2015-06-29', NULL, NULL, 'Somali', NULL, '+252650093346', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1131, 1131, 'STUBAD395B4', 'ADMBAD395B4', 2, 'Nimco', 'Jama', NULL, 'Male', '2012-08-14', NULL, NULL, 'Somali', NULL, '+252663532752', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1132, 1132, 'STUB210C33E', 'ADMB210C33E', 2, 'Ifrah', 'Said', NULL, 'Male', '2007-05-01', NULL, NULL, 'Somali', NULL, '+252629080984', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1133, 1133, 'STU7D035530', 'ADM7D035530', 2, 'Safiya', 'Roble', NULL, 'Male', '2009-08-22', NULL, NULL, 'Somali', NULL, '+252657520585', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1134, 1134, 'STU3BCE4911', 'ADM3BCE4911', 2, 'Ahmed', 'Sheikh', NULL, 'Male', '2015-11-28', NULL, NULL, 'Somali', NULL, '+252634518931', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1135, 1135, 'STU382B90D5', 'ADM382B90D5', 2, 'Hassan', 'Adam', NULL, 'Male', '2015-01-24', NULL, NULL, 'Somali', NULL, '+252635861232', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1136, 1136, 'STUFE4DE7A9', 'ADMFE4DE7A9', 2, 'Mukhtar', 'Adam', NULL, 'Male', '2007-08-17', NULL, NULL, 'Somali', NULL, '+252661648989', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1137, 1137, 'STU1F82A2C2', 'ADM1F82A2C2', 2, 'Fartun', 'Farah', NULL, 'Female', '2008-11-16', NULL, NULL, 'Somali', NULL, '+252910675874', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1138, 1138, 'STU00DBC190', 'ADM00DBC190', 2, 'Ibrahim', 'Said', NULL, 'Male', '2014-06-19', NULL, NULL, 'Somali', NULL, '+252910460765', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1139, 1139, 'STUD581FCDC', 'ADMD581FCDC', 2, 'Mahad', 'Abdulle', NULL, 'Female', '2008-12-07', NULL, NULL, 'Somali', NULL, '+252621707294', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1140, 1140, 'STU50E1201B', 'ADM50E1201B', 2, 'Najma', 'Ali', NULL, 'Male', '2006-12-29', NULL, NULL, 'Somali', NULL, '+252614765518', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1141, 1141, 'STU28130CF8', 'ADM28130CF8', 2, 'Mahad', 'Said', NULL, 'Male', '2011-01-21', NULL, NULL, 'Somali', NULL, '+252637415621', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1142, 1142, 'STU35453F15', 'ADM35453F15', 2, 'Rahma', 'Warsame', NULL, 'Female', '2010-06-21', NULL, NULL, 'Somali', NULL, '+252629506534', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1143, 1143, 'STUD85B126B', 'ADMD85B126B', 2, 'Omar', 'Abdullahi', NULL, 'Female', '2009-09-15', NULL, NULL, 'Somali', NULL, '+252622333500', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1144, 1144, 'STUAAB78A54', 'ADMAAB78A54', 2, 'Najma', 'Farah', NULL, 'Female', '2008-10-28', NULL, NULL, 'Somali', NULL, '+252623293818', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1145, 1145, 'STU089A7E97', 'ADM089A7E97', 2, 'Ismail', 'Roble', NULL, 'Male', '2010-05-24', NULL, NULL, 'Somali', NULL, '+252665920798', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1146, 1146, 'STU1F1089C1', 'ADM1F1089C1', 2, 'Layla', 'Jama', NULL, 'Male', '2013-12-07', NULL, NULL, 'Somali', NULL, '+252659881131', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1147, 1147, 'STU8D372C3E', 'ADM8D372C3E', 2, 'Sagal', 'Roble', NULL, 'Male', '2009-03-29', NULL, NULL, 'Somali', NULL, '+252612890974', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1148, 1148, 'STUF2B93C47', 'ADMF2B93C47', 2, 'Najma', 'Said', NULL, 'Female', '2010-12-16', NULL, NULL, 'Somali', NULL, '+252919069321', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1149, 1149, 'STU3D2F750A', 'ADM3D2F750A', 2, 'Najma', 'Guled', NULL, 'Male', '2007-07-07', NULL, NULL, 'Somali', NULL, '+252662869936', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1150, 1150, 'STUFA24B384', 'ADMFA24B384', 2, 'Sahra', 'Omar', NULL, 'Female', '2011-08-27', NULL, NULL, 'Somali', NULL, '+252901844263', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1151, 1151, 'STU8C8657A1', 'ADM8C8657A1', 2, 'Mukhtar', 'Ali', NULL, 'Male', '2012-08-12', NULL, NULL, 'Somali', NULL, '+252621070696', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1152, 1152, 'STU646CC4B0', 'ADM646CC4B0', 2, 'Najma', 'Ismail', NULL, 'Male', '2015-12-09', NULL, NULL, 'Somali', NULL, '+252664962905', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1153, 1153, 'STUE7FAE4D5', 'ADME7FAE4D5', 2, 'Khadra', 'Nur', NULL, 'Male', '2007-03-08', NULL, NULL, 'Somali', NULL, '+252915350928', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1154, 1154, 'STU88B487FE', 'ADM88B487FE', 2, 'Sagal', 'Muse', NULL, 'Male', '2015-06-11', NULL, NULL, 'Somali', NULL, '+252908006225', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1155, 1155, 'STU96BD9DBE', 'ADM96BD9DBE', 2, 'Mohamed', 'Warsame', NULL, 'Female', '2006-09-12', NULL, NULL, 'Somali', NULL, '+252624253024', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1156, 1156, 'STU427A25A3', 'ADM427A25A3', 2, 'Ibrahim', 'Abdullahi', NULL, 'Female', '2014-01-15', NULL, NULL, 'Somali', NULL, '+252656765547', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1157, 1157, 'STUE171C446', 'ADME171C446', 2, 'Ayaan', 'Hassan', NULL, 'Male', '2011-01-25', NULL, NULL, 'Somali', NULL, '+252911537144', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1158, 1158, 'STU17956C18', 'ADM17956C18', 2, 'Layla', 'Farah', NULL, 'Male', '2009-11-24', NULL, NULL, 'Somali', NULL, '+252914542340', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:40', '2025-12-16 09:27:40'),
(1159, 1159, 'STU86D9ED0A', 'ADM86D9ED0A', 2, 'Hodan', 'Adam', NULL, 'Male', '2005-05-01', NULL, NULL, 'Somali', NULL, '+252635040588', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:40', '2025-12-16 09:27:40'),
(1160, 1160, 'STU1569C73E', 'ADM1569C73E', 2, 'Ibrahim', 'Osman', NULL, 'Male', '2009-10-10', NULL, NULL, 'Somali', NULL, '+252613514336', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:40', '2025-12-16 09:27:40'),
(1161, 1161, 'STU4A1135D0', 'ADM4A1135D0', 2, 'Asha', 'Abdullahi', NULL, 'Male', '2013-09-19', NULL, NULL, 'Somali', NULL, '+252907976662', 'Mogadishu, Somalia', 'Mogadishu', 'Banadir', NULL, NULL, NULL, NULL, '2025-12-16', 5, 13, 'Active', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 09:27:40', '2025-12-16 09:27:40'),
(1162, NULL, 'STU001162', 'ADM20251162', 2, 'Zamzam', 'sharif', '', 'Female', '1995-10-17', '', '', 'Somali', '', '', '', '', '', '', NULL, NULL, NULL, '0000-00-00', 0, 0, 'Active', 0, 0, NULL, NULL, NULL, NULL, 'Fixed', 12.00, '2025-12-20 06:14:35', '2025-12-20 06:14:35'),
(1163, NULL, 'STU001163', 'ADM20251163', 2, 'qof', 'dheer', '', 'Male', '2025-12-01', '', '', 'Somali', '', '', '', '', '', '', NULL, NULL, NULL, '0000-00-00', 3, 6, 'Active', 0, 0, NULL, NULL, NULL, NULL, '', NULL, '2025-12-21 06:50:57', '2025-12-21 06:50:57');

-- --------------------------------------------------------

--
-- Table structure for table `student_advance_credits`
--

CREATE TABLE `student_advance_credits` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `payment_id` int(11) DEFAULT NULL COMMENT 'Reference to fee_payments if from payment',
  `amount` decimal(10,2) NOT NULL,
  `allocated_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Amount already allocated to fees',
  `available_amount` decimal(10,2) NOT NULL COMMENT 'Amount still available for allocation',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_attendance`
--

CREATE TABLE `student_attendance` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('Present','Absent','Late','Half Day','Leave') NOT NULL,
  `remarks` text DEFAULT NULL,
  `marked_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_attendance`
--

INSERT INTO `student_attendance` (`id`, `student_id`, `class_id`, `section_id`, `subject_id`, `attendance_date`, `status`, `remarks`, `marked_by`, `created_at`) VALUES
(1, 1, 1, 1, NULL, '2025-11-22', 'Absent', '', 2, '2025-11-22 16:15:18'),
(2, 1, 1, 1, NULL, '2025-11-30', 'Present', '', 1, '2025-11-30 07:54:22'),
(5, 1, 1, 1, NULL, '2025-12-03', 'Present', '', 1, '2025-12-03 07:48:18'),
(6, 2, 1, 1, NULL, '2025-12-03', 'Absent', '', 1, '2025-12-03 07:48:18'),
(7, 3, 1, 1, NULL, '2025-12-03', 'Late', '', 1, '2025-12-03 07:48:18'),
(8, 1, 1, 1, NULL, '2025-12-13', 'Absent', '', 1, '2025-12-13 13:06:51'),
(9, 2, 1, 1, NULL, '2025-12-13', 'Present', '', 1, '2025-12-13 13:06:51'),
(10, 3, 1, 1, NULL, '2025-12-13', 'Present', '', 1, '2025-12-13 13:06:51'),
(11, 620, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(12, 1, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(13, 2, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(14, 3, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(15, 643, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(16, 646, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(17, 624, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(18, 618, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(19, 616, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(20, 612, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(21, 615, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(22, 613, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(23, 633, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(24, 636, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(25, 619, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(26, 635, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(27, 625, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(28, 639, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(29, 645, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(30, 622, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(31, 628, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(32, 640, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(33, 614, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(34, 610, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(35, 629, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(36, 630, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(37, 641, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(38, 627, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(39, 623, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(40, 626, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(41, 644, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(42, 637, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(43, 611, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(44, 617, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(45, 642, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(46, 638, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(47, 631, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(48, 634, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(49, 632, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(50, 621, 1, 1, NULL, '2025-12-16', 'Present', '', 1, '2025-12-16 10:12:09'),
(98, 620, 1, 1, 1, '2025-12-16', 'Absent', '', 2, '2025-12-16 11:07:17'),
(99, 1, 1, 1, 1, '2025-12-16', 'Absent', '', 2, '2025-12-16 11:07:17'),
(100, 2, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(101, 3, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(102, 643, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(103, 646, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(104, 624, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(105, 618, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(106, 616, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(107, 612, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(108, 615, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(109, 613, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(110, 633, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(111, 636, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(112, 619, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(113, 635, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(114, 625, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(115, 639, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(116, 645, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(117, 622, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(118, 628, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(119, 640, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(120, 614, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(121, 610, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(122, 629, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(123, 630, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(124, 641, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(125, 627, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(126, 623, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(127, 626, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(128, 644, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(129, 637, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(130, 611, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(131, 617, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(132, 642, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(133, 638, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(134, 631, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(135, 634, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(136, 632, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17'),
(137, 621, 1, 1, 1, '2025-12-16', 'Present', '', 2, '2025-12-16 11:07:17');

-- --------------------------------------------------------

--
-- Table structure for table `student_certificates`
--

CREATE TABLE `student_certificates` (
  `id` int(11) NOT NULL,
  `certificate_no` varchar(100) NOT NULL,
  `verification_code` varchar(64) NOT NULL,
  `student_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  `certificate_type` enum('Completion','Promotion','Graduation','Character','Custom') NOT NULL,
  `session_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `issue_date` date NOT NULL,
  `status` enum('Issued','Reissued','Revoked') DEFAULT 'Issued',
  `revoked_reason` text DEFAULT NULL,
  `reissued_from_id` int(11) DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `qr_code_path` varchar(255) DEFAULT NULL,
  `meta_json` text DEFAULT NULL,
  `issued_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_documents`
--

CREATE TABLE `student_documents` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `document_type` varchar(100) NOT NULL,
  `document_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_fee_balance`
--

CREATE TABLE `student_fee_balance` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `total_assigned` decimal(10,2) DEFAULT 0.00,
  `total_paid` decimal(10,2) DEFAULT 0.00,
  `total_due` decimal(10,2) DEFAULT 0.00,
  `advance_credit` decimal(10,2) DEFAULT 0.00,
  `overdue_amount` decimal(10,2) DEFAULT 0.00,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_fee_balance`
--

INSERT INTO `student_fee_balance` (`id`, `student_id`, `session_id`, `total_assigned`, `total_paid`, `total_due`, `advance_credit`, `overdue_amount`, `last_updated`) VALUES
(1, 1, 1, 120.00, 60.00, 60.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(2, 2, 1, 108.00, 108.00, 0.00, 0.00, 0.00, '2025-12-20 06:36:29'),
(3, 3, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(5, 610, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(6, 611, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(7, 612, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(8, 613, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(9, 614, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(10, 615, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(11, 616, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(12, 617, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(13, 618, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(14, 619, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(15, 620, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(16, 621, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(17, 622, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(18, 623, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(19, 624, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(20, 625, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(21, 626, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(22, 627, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(23, 628, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(24, 629, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(25, 630, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(26, 631, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(27, 632, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(28, 633, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(29, 634, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(30, 635, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(31, 636, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(32, 637, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(33, 638, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(34, 639, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(35, 640, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(36, 641, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(37, 642, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(38, 643, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(39, 644, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(40, 645, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27'),
(41, 646, 1, 120.00, 0.00, 120.00, 0.00, 0.00, '2025-12-20 06:22:27');

-- --------------------------------------------------------

--
-- Table structure for table `student_fee_ledger`
--

CREATE TABLE `student_fee_ledger` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `transaction_type` enum('Assignment','Payment','Advance Credit','Adjustment','Refund') NOT NULL,
  `reference_id` int(11) DEFAULT NULL COMMENT 'ID of related record (assignment_id, payment_id, etc.)',
  `reference_type` varchar(50) DEFAULT NULL COMMENT 'Type: monthly_assignment, payment, advance, etc.',
  `month` varchar(20) DEFAULT NULL COMMENT 'For monthly fees: YYYY-MM',
  `debit_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Amount charged/assigned',
  `credit_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Amount paid/credited',
  `balance` decimal(10,2) NOT NULL COMMENT 'Running balance after this transaction',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_fee_ledger`
--

INSERT INTO `student_fee_ledger` (`id`, `student_id`, `session_id`, `transaction_type`, `reference_id`, `reference_type`, `month`, `debit_amount`, `credit_amount`, `balance`, `description`, `created_at`) VALUES
(1, 1, 1, 'Assignment', 13, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-13 13:39:11'),
(2, 2, 1, 'Assignment', 14, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-13 13:39:11'),
(3, 3, 1, 'Assignment', 15, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-13 13:39:11'),
(4, 1, 1, 'Payment', 2, 'payment', '2025-12', 0.00, 60.00, 0.00, 'Payment for December 2025 - Receipt: RCT000002', '2025-12-13 13:46:41'),
(5, 610, 1, 'Assignment', 16, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(6, 611, 1, 'Assignment', 17, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(7, 612, 1, 'Assignment', 18, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(8, 613, 1, 'Assignment', 19, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(9, 614, 1, 'Assignment', 20, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(10, 615, 1, 'Assignment', 21, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(11, 616, 1, 'Assignment', 22, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(12, 617, 1, 'Assignment', 23, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(13, 618, 1, 'Assignment', 24, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(14, 619, 1, 'Assignment', 25, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(15, 620, 1, 'Assignment', 26, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(16, 621, 1, 'Assignment', 27, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(17, 622, 1, 'Assignment', 28, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(18, 623, 1, 'Assignment', 29, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(19, 624, 1, 'Assignment', 30, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(20, 625, 1, 'Assignment', 31, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(21, 626, 1, 'Assignment', 32, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(22, 627, 1, 'Assignment', 33, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(23, 628, 1, 'Assignment', 34, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(24, 629, 1, 'Assignment', 35, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(25, 630, 1, 'Assignment', 36, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(26, 631, 1, 'Assignment', 37, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(27, 632, 1, 'Assignment', 38, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(28, 633, 1, 'Assignment', 39, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(29, 634, 1, 'Assignment', 40, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(30, 635, 1, 'Assignment', 41, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(31, 636, 1, 'Assignment', 42, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(32, 637, 1, 'Assignment', 43, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(33, 638, 1, 'Assignment', 44, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(34, 639, 1, 'Assignment', 45, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(35, 640, 1, 'Assignment', 46, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(36, 641, 1, 'Assignment', 47, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(37, 642, 1, 'Assignment', 48, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(38, 643, 1, 'Assignment', 49, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(39, 644, 1, 'Assignment', 50, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(40, 645, 1, 'Assignment', 51, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(41, 646, 1, 'Assignment', 52, 'monthly_assignment', '2025-12', 60.00, 0.00, 60.00, 'Monthly fee assignment for December 2025', '2025-12-19 13:40:44'),
(42, 2, 1, 'Payment', 3, 'payment', '2025-12', 0.00, 35.00, 25.00, 'Payment for December 2025 - Receipt: RCT000003', '2025-12-19 13:43:58'),
(46, 2, 1, 'Payment', 7, 'payment', '2025-12', 0.00, 2.00, 23.00, 'Payment for December 2025 - Receipt: RCT000004', '2025-12-19 15:21:33'),
(47, 1, 1, 'Assignment', 53, 'monthly_assignment', '2025-11', 60.00, 0.00, 60.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(48, 2, 1, 'Assignment', 54, 'monthly_assignment', '2025-11', 48.00, 0.00, 71.00, 'Monthly fee assignment for November 2025 (Original: $60.00, Discount: $12.00)', '2025-12-20 06:22:27'),
(49, 3, 1, 'Assignment', 55, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(50, 610, 1, 'Assignment', 56, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(51, 611, 1, 'Assignment', 57, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(52, 612, 1, 'Assignment', 58, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(53, 613, 1, 'Assignment', 59, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(54, 614, 1, 'Assignment', 60, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(55, 615, 1, 'Assignment', 61, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(56, 616, 1, 'Assignment', 62, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(57, 617, 1, 'Assignment', 63, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(58, 618, 1, 'Assignment', 64, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(59, 619, 1, 'Assignment', 65, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(60, 620, 1, 'Assignment', 66, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(61, 621, 1, 'Assignment', 67, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(62, 622, 1, 'Assignment', 68, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(63, 623, 1, 'Assignment', 69, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(64, 624, 1, 'Assignment', 70, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(65, 625, 1, 'Assignment', 71, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(66, 626, 1, 'Assignment', 72, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(67, 627, 1, 'Assignment', 73, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(68, 628, 1, 'Assignment', 74, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(69, 629, 1, 'Assignment', 75, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(70, 630, 1, 'Assignment', 76, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(71, 631, 1, 'Assignment', 77, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(72, 632, 1, 'Assignment', 78, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(73, 633, 1, 'Assignment', 79, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(74, 634, 1, 'Assignment', 80, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(75, 635, 1, 'Assignment', 81, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(76, 636, 1, 'Assignment', 82, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(77, 637, 1, 'Assignment', 83, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(78, 638, 1, 'Assignment', 84, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(79, 639, 1, 'Assignment', 85, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(80, 640, 1, 'Assignment', 86, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(81, 641, 1, 'Assignment', 87, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(82, 642, 1, 'Assignment', 88, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(83, 643, 1, 'Assignment', 89, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(84, 644, 1, 'Assignment', 90, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(85, 645, 1, 'Assignment', 91, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(86, 646, 1, 'Assignment', 92, 'monthly_assignment', '2025-11', 60.00, 0.00, 120.00, 'Monthly fee assignment for November 2025', '2025-12-20 06:22:27'),
(87, 2, 1, 'Payment', 8, 'payment', '2025-11', 0.00, 48.00, 23.00, 'Payment for November 2025 - Receipt: RCT000008', '2025-12-20 06:36:29'),
(88, 2, 1, 'Payment', 8, 'payment', '2025-12', 0.00, 23.00, 0.00, 'Payment for December 2025 - Receipt: RCT000008', '2025-12-20 06:36:29');

-- --------------------------------------------------------

--
-- Table structure for table `student_marks`
--

CREATE TABLE `student_marks` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `exam_schedule_id` int(11) NOT NULL,
  `marks_obtained` decimal(10,2) DEFAULT NULL,
  `is_absent` tinyint(1) DEFAULT 0,
  `remarks` text DEFAULT NULL,
  `entered_by` int(11) DEFAULT NULL,
  `entered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_marks`
--

INSERT INTO `student_marks` (`id`, `student_id`, `exam_schedule_id`, `marks_obtained`, `is_absent`, `remarks`, `entered_by`, `entered_at`, `updated_at`) VALUES
(1, 1, 1, 30.00, 0, '', 1, '2025-11-30 08:26:19', '2025-12-17 18:32:46'),
(2, 2, 1, 5.00, 0, '', 1, '2025-11-30 08:26:19', '2025-12-17 18:32:46'),
(3, 3, 1, 20.00, 0, '', 1, '2025-11-30 08:26:19', '2025-12-17 18:32:46'),
(4, 620, 2, NULL, 1, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:56'),
(5, 1, 2, 20.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(6, 2, 2, 13.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(7, 3, 2, 7.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(8, 643, 2, 11.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(9, 646, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(10, 624, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(11, 618, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(12, 616, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(13, 612, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(14, 615, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(15, 613, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(16, 633, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(17, 636, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(18, 619, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(19, 635, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(20, 625, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(21, 639, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(22, 645, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(23, 622, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(24, 628, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(25, 640, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(26, 614, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(27, 610, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(28, 629, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(29, 630, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(30, 641, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(31, 627, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(32, 623, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(33, 626, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(34, 644, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(35, 637, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(36, 611, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(37, 617, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(38, 642, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(39, 638, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(40, 631, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(41, 634, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(42, 632, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(43, 621, 2, 0.00, 0, '', 1, '2025-12-17 08:34:09', '2025-12-17 18:32:14'),
(44, 620, 1, NULL, 1, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(45, 643, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(46, 646, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(47, 624, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(48, 618, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(49, 616, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(50, 612, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(51, 615, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(52, 613, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(53, 633, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(54, 636, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(55, 619, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(56, 635, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(57, 625, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(58, 639, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(59, 645, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(60, 622, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(61, 628, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(62, 640, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(63, 614, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(64, 610, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(65, 629, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(66, 630, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(67, 641, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(68, 627, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(69, 623, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(70, 626, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(71, 644, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(72, 637, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(73, 611, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(74, 617, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(75, 642, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(76, 638, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(77, 631, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(78, 634, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(79, 632, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46'),
(80, 621, 1, 0.00, 0, '', 1, '2025-12-17 18:32:46', '2025-12-17 18:32:46');

-- --------------------------------------------------------

--
-- Table structure for table `student_parents`
--

CREATE TABLE `student_parents` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `relationship` enum('Father','Mother','Guardian','Other') NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `is_emergency_contact` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_parents`
--

INSERT INTO `student_parents` (`id`, `student_id`, `parent_id`, `relationship`, `is_primary`, `is_emergency_contact`) VALUES
(1, 2, 1, '', 1, 0),
(2, 3, 2, '', 1, 0),
(3, 4, 3, '', 1, 0),
(4, 5, 4, '', 1, 0),
(5, 6, 5, 'Father', 1, 0),
(6, 7, 6, 'Father', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `student_promotions`
--

CREATE TABLE `student_promotions` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `from_class_id` int(11) NOT NULL,
  `to_class_id` int(11) NOT NULL,
  `from_session_id` int(11) NOT NULL,
  `to_session_id` int(11) NOT NULL,
  `promotion_date` date NOT NULL,
  `status` enum('Promoted','Detained','Transferred') DEFAULT 'Promoted',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_promotions`
--

INSERT INTO `student_promotions` (`id`, `student_id`, `from_class_id`, `to_class_id`, `from_session_id`, `to_session_id`, `promotion_date`, `status`, `remarks`, `created_at`) VALUES
(1, 1, 1, 1, 1, 1, '2025-11-22', 'Promoted', NULL, '2025-11-22 16:57:26'),
(2, 1, 1, 1, 1, 0, '2025-11-30', 'Promoted', NULL, '2025-11-30 08:22:27'),
(3, 5, 2, 3, 1, 1, '2025-12-14', 'Promoted', NULL, '2025-12-14 07:37:36'),
(4, 4, 2, 3, 1, 1, '2025-12-14', 'Promoted', NULL, '2025-12-14 07:37:36'),
(5, 2, 1, 6, 1, 1, '2025-12-20', 'Promoted', NULL, '2025-12-20 07:04:53'),
(6, 644, 1, 6, 1, 1, '2025-12-20', 'Promoted', NULL, '2025-12-20 07:04:53'),
(7, 637, 1, 6, 1, 1, '2025-12-20', 'Promoted', NULL, '2025-12-20 07:04:53'),
(8, 611, 1, 6, 1, 1, '2025-12-20', 'Promoted', NULL, '2025-12-20 07:04:53'),
(9, 617, 1, 6, 1, 1, '2025-12-20', 'Promoted', NULL, '2025-12-20 07:04:53');

-- --------------------------------------------------------

--
-- Table structure for table `student_transcripts`
--

CREATE TABLE `student_transcripts` (
  `id` int(11) NOT NULL,
  `transcript_no` varchar(100) NOT NULL,
  `verification_code` varchar(64) NOT NULL,
  `student_id` int(11) NOT NULL,
  `from_session_id` int(11) DEFAULT NULL,
  `to_session_id` int(11) DEFAULT NULL,
  `program_class_id` int(11) DEFAULT NULL,
  `total_credits` decimal(6,2) DEFAULT NULL,
  `earned_credits` decimal(6,2) DEFAULT NULL,
  `gpa` decimal(3,2) DEFAULT NULL,
  `cgpa` decimal(3,2) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `summary_json` longtext DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `status` enum('Issued','Revoked') DEFAULT 'Issued',
  `issued_by` int(11) DEFAULT NULL,
  `issued_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_transfers`
--

CREATE TABLE `student_transfers` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `from_branch_id` int(11) NOT NULL,
  `to_branch_id` int(11) NOT NULL,
  `transfer_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `study_materials`
--

CREATE TABLE `study_materials` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `subject_code` varchar(50) NOT NULL,
  `subject_type` enum('Core','Elective','Optional') DEFAULT 'Core',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `subject_name`, `subject_code`, `subject_type`, `description`, `is_active`, `created_at`) VALUES
(1, 'Math', 'MATH101', 'Core', '', 1, '2025-11-22 16:52:48'),
(3, 'English', 'Basic English', 'Core', '', 1, '2025-11-30 14:26:46'),
(4, 'Biology', 'Biology', 'Core', '', 1, '2025-12-14 07:43:32'),
(5, 'Physics', 'Physics', 'Core', '', 1, '2025-12-14 07:43:43'),
(6, 'Chemistry', 'Chemistry', 'Core', '', 1, '2025-12-14 07:43:54'),
(7, 'History', 'History', 'Core', '', 1, '2025-12-14 07:44:03'),
(8, 'Geography', 'Geography', 'Core', '', 1, '2025-12-14 07:44:12'),
(9, 'Computer Science', 'Computer Science', 'Elective', '', 1, '2025-12-14 07:45:11'),
(10, 'Art amp Design', 'Art amp Design', 'Elective', '', 1, '2025-12-14 07:45:33'),
(11, 'Economics', 'Economics', 'Elective', '', 1, '2025-12-14 07:45:47');

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL,
  `ticket_no` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `priority` enum('Low','Medium','High','Critical') DEFAULT 'Medium',
  `subject` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('Open','In Progress','Resolved','Closed','Reopened') DEFAULT 'Open',
  `assigned_to` int(11) DEFAULT NULL,
  `resolution` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `support_tickets`
--

INSERT INTO `support_tickets` (`id`, `ticket_no`, `user_id`, `category`, `priority`, `subject`, `description`, `status`, `assigned_to`, `resolution`, `created_at`, `updated_at`) VALUES
(1, 'TKT000001', 5, 'Technical', 'Medium', 'test student 00001', 'test', 'Open', 1, NULL, '2025-12-03 08:06:07', '2025-12-03 08:10:31'),
(2, 'TKT000002', 6, 'Academic', 'Medium', 'codsi', 'arday dhameeyey baan ahay', 'Closed', 2, '', '2025-12-20 09:56:02', '2025-12-20 09:58:49'),
(3, 'TKT000003', 2, 'Financial', 'Medium', 'macalin ticket', 'fasax baan rabaa', 'Closed', NULL, '', '2025-12-20 09:59:58', '2025-12-20 10:01:40');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `school_name` varchar(255) NOT NULL,
  `system_short_name` varchar(50) DEFAULT NULL,
  `system_logo` varchar(255) DEFAULT NULL,
  `system_favicon` varchar(255) DEFAULT NULL,
  `school_name_somali` varchar(255) DEFAULT NULL,
  `school_logo` varchar(255) DEFAULT NULL,
  `school_email` varchar(100) DEFAULT NULL,
  `school_phone` varchar(50) DEFAULT NULL,
  `school_address` text DEFAULT NULL,
  `developer_name` varchar(255) DEFAULT 'Uukow Technology Solutions (UTech)',
  `license_text` text DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'USD',
  `currency_symbol` varchar(10) DEFAULT '$',
  `tuition_fee_behavior` enum('Monthly','Termly','Yearly','Custom') DEFAULT 'Monthly',
  `discount_enabled` tinyint(1) DEFAULT 1,
  `penalty_enabled` tinyint(1) DEFAULT 1,
  `penalty_rate` decimal(5,2) DEFAULT 0.00,
  `payroll_enabled` tinyint(1) DEFAULT 1,
  `tax_enabled` tinyint(1) DEFAULT 0,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `timezone` varchar(100) DEFAULT 'Africa/Mogadishu',
  `language` varchar(10) DEFAULT 'en',
  `theme` varchar(50) DEFAULT 'default',
  `date_format` varchar(20) DEFAULT 'd-m-Y',
  `time_format` varchar(20) DEFAULT 'H:i:s',
  `datetime_format` varchar(30) DEFAULT 'd-m-Y H:i:s',
  `pagination_limit` int(11) DEFAULT 25,
  `records_per_page` int(11) DEFAULT 25,
  `academic_year_start` date DEFAULT NULL,
  `academic_year_end` date DEFAULT NULL,
  `current_session` int(11) DEFAULT NULL,
  `grading_system` enum('Percentage','Letter','GPA','Points') DEFAULT 'Percentage',
  `gpa_scale` decimal(3,2) DEFAULT 4.00,
  `attendance_threshold` int(11) DEFAULT 75,
  `class_graduation_enabled` tinyint(1) DEFAULT 1,
  `smtp_host` varchar(255) DEFAULT NULL,
  `smtp_port` int(11) DEFAULT 587,
  `smtp_username` varchar(255) DEFAULT NULL,
  `smtp_password` varchar(255) DEFAULT NULL,
  `smtp_encryption` varchar(10) DEFAULT 'tls',
  `email_enabled` tinyint(1) DEFAULT 1,
  `sms_gateway` varchar(50) DEFAULT NULL,
  `sms_api_key` varchar(255) DEFAULT NULL,
  `whatsapp_api_key` varchar(255) DEFAULT NULL,
  `sms_enabled` tinyint(1) DEFAULT 0,
  `whatsapp_enabled` tinyint(1) DEFAULT 0,
  `notification_enabled` tinyint(1) DEFAULT 1,
  `notification_email` tinyint(1) DEFAULT 1,
  `notification_sms` tinyint(1) DEFAULT 0,
  `notification_whatsapp` tinyint(1) DEFAULT 0,
  `payment_gateway` varchar(50) DEFAULT NULL,
  `payment_api_key` varchar(255) DEFAULT NULL,
  `api_enabled` tinyint(1) DEFAULT 0,
  `api_key` varchar(255) DEFAULT NULL,
  `webhook_enabled` tinyint(1) DEFAULT 0,
  `webhook_url` varchar(500) DEFAULT NULL,
  `license_verification_enabled` tinyint(1) DEFAULT 0,
  `license_verification_endpoint` varchar(500) DEFAULT NULL,
  `license_key` varchar(255) DEFAULT NULL,
  `feature_lms` tinyint(1) DEFAULT 1,
  `feature_library` tinyint(1) DEFAULT 1,
  `feature_transport` tinyint(1) DEFAULT 1,
  `feature_hostel` tinyint(1) DEFAULT 0,
  `feature_certificates` tinyint(1) DEFAULT 1,
  `feature_events` tinyint(1) DEFAULT 1,
  `backup_enabled` tinyint(1) DEFAULT 1,
  `backup_frequency` enum('daily','weekly','monthly') DEFAULT 'weekly',
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `session_timeout` int(11) DEFAULT 3600,
  `password_min_length` int(11) DEFAULT 8,
  `password_require_uppercase` tinyint(1) DEFAULT 0,
  `password_require_lowercase` tinyint(1) DEFAULT 1,
  `password_require_number` tinyint(1) DEFAULT 1,
  `password_require_special` tinyint(1) DEFAULT 0,
  `max_login_attempts` int(11) DEFAULT 5,
  `account_lockout_time` int(11) DEFAULT 1800,
  `audit_logging_enabled` tinyint(1) DEFAULT 1,
  `default_role_id` int(11) DEFAULT NULL,
  `role_permissions_enabled` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `school_name`, `system_short_name`, `system_logo`, `system_favicon`, `school_name_somali`, `school_logo`, `school_email`, `school_phone`, `school_address`, `developer_name`, `license_text`, `currency`, `currency_symbol`, `tuition_fee_behavior`, `discount_enabled`, `penalty_enabled`, `penalty_rate`, `payroll_enabled`, `tax_enabled`, `tax_rate`, `timezone`, `language`, `theme`, `date_format`, `time_format`, `datetime_format`, `pagination_limit`, `records_per_page`, `academic_year_start`, `academic_year_end`, `current_session`, `grading_system`, `gpa_scale`, `attendance_threshold`, `class_graduation_enabled`, `smtp_host`, `smtp_port`, `smtp_username`, `smtp_password`, `smtp_encryption`, `email_enabled`, `sms_gateway`, `sms_api_key`, `whatsapp_api_key`, `sms_enabled`, `whatsapp_enabled`, `notification_enabled`, `notification_email`, `notification_sms`, `notification_whatsapp`, `payment_gateway`, `payment_api_key`, `api_enabled`, `api_key`, `webhook_enabled`, `webhook_url`, `license_verification_enabled`, `license_verification_endpoint`, `license_key`, `feature_lms`, `feature_library`, `feature_transport`, `feature_hostel`, `feature_certificates`, `feature_events`, `backup_enabled`, `backup_frequency`, `two_factor_enabled`, `session_timeout`, `password_min_length`, `password_require_uppercase`, `password_require_lowercase`, `password_require_number`, `password_require_special`, `max_login_attempts`, `account_lockout_time`, `audit_logging_enabled`, `default_role_id`, `role_permissions_enabled`, `created_at`, `updated_at`, `updated_by`) VALUES
(1, 'TacliinHub ERP System', 'TacliinHub', 'uploads/system/logo_1766229985_694687e1767e3.png', 'uploads/system/favicon_1766230151_69468887852f9.png', NULL, NULL, 'abdihagad@gmail.com', NULL, NULL, 'Uukow Technology Solutions (UTech)', NULL, 'USD', '$', 'Monthly', 1, 1, 0.00, 1, 0, 0.00, 'Africa/Mogadishu', 'en', 'default', 'd-m-Y', 'H:i:s', 'd-m-Y H:i:s', 25, 25, NULL, NULL, NULL, 'Percentage', 4.00, 75, 1, NULL, 587, NULL, NULL, 'tls', 1, NULL, NULL, NULL, 0, 0, 1, 1, 0, 0, NULL, NULL, 0, NULL, 0, NULL, 0, NULL, NULL, 1, 1, 1, 0, 1, 1, 1, 'weekly', 0, 3600, 8, 0, 1, 1, 0, 5, 1800, 1, NULL, 1, '2025-11-13 07:26:12', '2025-12-20 11:30:15', 1);

-- --------------------------------------------------------

--
-- Table structure for table `ticket_replies`
--

CREATE TABLE `ticket_replies` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ticket_replies`
--

INSERT INTO `ticket_replies` (`id`, `ticket_id`, `user_id`, `message`, `attachment`, `created_at`) VALUES
(1, 1, 5, 'mahadsanid', NULL, '2025-12-03 08:10:18'),
(2, 1, 1, 'adigaa mudan', NULL, '2025-12-03 08:10:31'),
(3, 2, 1, 'maxaad rabtaa', NULL, '2025-12-20 09:56:19'),
(4, 2, 6, 'waxaan u baahanahay in la ii xaliyo codsigayga', NULL, '2025-12-20 09:57:51'),
(5, 2, 1, 'muxuu yahay codsigaasna', NULL, '2025-12-20 09:58:10'),
(6, 2, 6, 'ma naqaano', NULL, '2025-12-20 09:58:23'),
(7, 3, 1, 'maxaa ku daaro', NULL, '2025-12-20 10:00:30'),
(8, 3, 2, 'xanuun baa i haayo maanta', NULL, '2025-12-20 10:00:45'),
(9, 3, 1, 'maanta lagaama maarmo fadlan imaaw xafiiska fasax ma jiree', NULL, '2025-12-20 10:01:10'),
(10, 3, 2, 'haye mahadsanid😊', NULL, '2025-12-20 10:01:29');

-- --------------------------------------------------------

--
-- Table structure for table `timetable`
--

CREATE TABLE `timetable` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `room_no` varchar(50) DEFAULT NULL,
  `session_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `timetable`
--

INSERT INTO `timetable` (`id`, `class_id`, `section_id`, `subject_id`, `teacher_id`, `day_of_week`, `start_time`, `end_time`, `room_no`, `session_id`, `created_at`) VALUES
(1, 1, 1, 1, 1, 'Saturday', '10:00:00', '12:00:00', 'Room 2', 1, '2025-11-23 07:12:45'),
(2, 1, 1, 3, 2, 'Monday', '06:29:00', '09:00:00', 'Hall 1', 1, '2025-11-30 14:28:58'),
(3, 1, 1, 1, 1, 'Tuesday', '13:25:00', '13:30:00', '1', 1, '2025-12-16 10:25:35'),
(4, 1, 1, 1, 1, 'Monday', '09:01:00', '10:30:00', 'Hall 1', 1, '2025-12-19 17:10:24');

-- --------------------------------------------------------

--
-- Table structure for table `transcripts`
--

CREATE TABLE `transcripts` (
  `id` int(11) NOT NULL,
  `transcript_number` varchar(100) NOT NULL,
  `student_id` int(11) NOT NULL,
  `grading_scheme_id` int(11) NOT NULL,
  `academic_data` longtext DEFAULT NULL,
  `total_credits` decimal(6,2) DEFAULT NULL,
  `cgpa` decimal(3,2) DEFAULT NULL,
  `overall_percentage` decimal(5,2) DEFAULT NULL,
  `generated_by` int(11) DEFAULT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('issued','revoked') DEFAULT 'issued',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transcripts`
--

INSERT INTO `transcripts` (`id`, `transcript_number`, `student_id`, `grading_scheme_id`, `academic_data`, `total_credits`, `cgpa`, `overall_percentage`, `generated_by`, `generated_at`, `status`, `updated_at`) VALUES
(1, 'TR-2025-00001', 620, 1, '[{\"session_id\":1,\"session_name\":\"2025-2026\",\"subjects\":[{\"subject_code\":\"Basic English\",\"subject_name\":\"English\",\"marks\":35,\"grade\":\"D\",\"grade_points\":\"0.00\",\"credits\":3}],\"gpa\":0,\"total_credits\":3}]', 3.00, 0.00, 0.00, 1, '2025-12-17 10:53:07', 'issued', '2025-12-17 10:53:07'),
(2, 'TR-2025-00002', 2, 1, '[{\"session_id\":1,\"session_name\":\"2025-2026\",\"subjects\":[{\"subject_code\":\"Basic English\",\"subject_name\":\"English\",\"marks\":65,\"grade\":\"C\",\"grade_points\":\"2.00\",\"credits\":3},{\"subject_code\":\"MATH101\",\"subject_name\":\"Math\",\"marks\":16.67,\"grade\":\"D\",\"grade_points\":\"0.00\",\"credits\":3}],\"gpa\":1,\"total_credits\":6}]', 6.00, 1.00, 25.00, 1, '2025-12-17 10:59:24', 'issued', '2025-12-17 10:59:24'),
(3, 'TR-2025-00003', 2, 1, '[{\"session_id\":1,\"session_name\":\"2025-2026\",\"subjects\":[{\"subject_code\":\"Basic English\",\"subject_name\":\"English\",\"marks\":65,\"grade\":\"C\",\"grade_points\":\"2.00\",\"credits\":3},{\"subject_code\":\"MATH101\",\"subject_name\":\"Math\",\"marks\":16.67,\"grade\":\"D\",\"grade_points\":\"0.00\",\"credits\":3}],\"gpa\":1,\"total_credits\":6}]', 6.00, 1.00, 25.00, 1, '2025-12-17 12:45:44', 'issued', '2025-12-17 12:45:44'),
(4, 'TR-2025-00004', 2, 1, '[{\"session_id\":1,\"session_name\":\"2025-2026\",\"subjects\":[{\"subject_code\":\"Basic English\",\"subject_name\":\"English\",\"marks\":65,\"grade\":\"C\",\"grade_points\":\"2.00\",\"credits\":3},{\"subject_code\":\"MATH101\",\"subject_name\":\"Math\",\"marks\":16.67,\"grade\":\"D\",\"grade_points\":\"0.00\",\"credits\":3}],\"gpa\":1,\"total_credits\":6}]', 6.00, 1.00, 25.00, 1, '2025-12-17 13:22:41', 'issued', '2025-12-17 13:22:41');

-- --------------------------------------------------------

--
-- Table structure for table `transport_assignments`
--

CREATE TABLE `transport_assignments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `route_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `pickup_point` varchar(255) DEFAULT NULL,
  `drop_point` varchar(255) DEFAULT NULL,
  `assignment_date` date NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transport_routes`
--

CREATE TABLE `transport_routes` (
  `id` int(11) NOT NULL,
  `route_name` varchar(255) NOT NULL,
  `route_code` varchar(50) NOT NULL,
  `start_point` varchar(255) NOT NULL,
  `end_point` varchar(255) NOT NULL,
  `stops` text DEFAULT NULL,
  `distance` decimal(10,2) DEFAULT NULL,
  `fare` decimal(10,2) DEFAULT NULL,
  `branch_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transport_vehicles`
--

CREATE TABLE `transport_vehicles` (
  `id` int(11) NOT NULL,
  `vehicle_number` varchar(50) NOT NULL,
  `vehicle_model` varchar(100) DEFAULT NULL,
  `vehicle_type` varchar(50) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `driver_name` varchar(255) DEFAULT NULL,
  `driver_phone` varchar(50) DEFAULT NULL,
  `driver_license` varchar(100) DEFAULT NULL,
  `insurance_details` text DEFAULT NULL,
  `registration_date` date DEFAULT NULL,
  `branch_id` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expire` datetime DEFAULT NULL,
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role_id`, `branch_id`, `is_active`, `is_verified`, `verification_token`, `reset_token`, `reset_token_expire`, `two_factor_secret`, `last_login`, `login_attempts`, `locked_until`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'hagadxiis@gmail.com', '$2y$10$KpBZxFs3r963yIPcFI4NA.OWN9rXH3IrzgM0b120crI2fP.2vqPJ.', 1, 1, 1, 1, NULL, NULL, NULL, NULL, '2025-12-21 10:17:40', 0, NULL, '2025-11-13 07:26:12', '2025-12-21 07:17:40'),
(2, 'teacher', 'uukoowtxt@gmail.com', '$2y$10$lkxU1pC0gZPAcMl8wLIkwOrySqlxA97I70oJIP9ar3do632tftV16', 3, 1, 1, 1, '927b2c6d50801bc6789ba4de38445652', NULL, NULL, NULL, '2025-12-20 12:51:32', 1, NULL, '2025-11-22 19:45:03', '2025-12-20 11:30:09'),
(3, 'hagad', 'abdihagad@gmail.com', '$2y$10$kFeYp0BVcGrP0zexY.XmuOdJ2rvtHS3T1aRO0Mu2w.ctB0XC2LWuq', 3, 1, 1, 0, '6e5c454c57f3831a160da9497ebaca75', NULL, NULL, NULL, '2025-12-17 09:54:33', 0, NULL, '2025-11-30 09:33:26', '2025-12-17 06:54:33'),
(5, 'stu000001', 'musalsalo23@gmail.com', '$2y$10$tZBS/EaYe9k1osL5mojPPemTisz6KlPJPIVj7GSwMcfrAEv4THuky', 4, 1, 1, 1, NULL, 'abefa00f7bb041067751aba685a31c6d', '2025-12-17 11:17:20', NULL, '2025-12-17 10:19:01', 1, NULL, '2025-12-03 07:33:41', '2025-12-19 15:22:33'),
(6, 'stu000002', 'info@uukowtech.com', '$2y$10$Ejf0oN.9qu4c8x.tcoVGbOYBTaWwU3SLWyJwYxYDX3RfOKnkLJWD.', 4, 1, 1, 1, NULL, NULL, NULL, NULL, '2025-12-20 13:12:25', 0, NULL, '2025-12-03 07:52:00', '2025-12-20 10:12:25'),
(610, 'stu_mahaddirie_06a9e', 'stu_mahaddirie_06a9e@example.som', '$2y$10$mZ5msYcl2eormRXzM1AjjecUJ9T.7q9IJ/PNhE4.ehBsvFVNsk2r2', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:04', '2025-12-16 09:27:04'),
(611, 'stu_najmaali_c2e11', 'stu_najmaali_c2e11@example.som', '$2y$10$rwFINqwqE0FkL6jug0HVMeh1UKCUJ7K54zKPNfodOxOxUgyadokSq', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:04', '2025-12-16 09:27:04'),
(612, 'stu_ayaanhassan_1520c', 'stu_ayaanhassan_1520c@example.som', '$2y$10$poPICys6RZxpyeOyfpirU.hoUGkbfYhxJZCAuYzfFUYbdWA3nPBVe', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:04', '2025-12-16 09:27:04'),
(613, 'stu_farahnur_b7d59', 'stu_farahnur_b7d59@example.som', '$2y$10$Y6PPdjxFK1tvS2DaXLPJOOZ2h3NJtrOnJiQHPQvSJPaFtDFYYqhq.', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:04', '2025-12-16 09:27:04'),
(614, 'stu_laylawarsame_6632f', 'stu_laylawarsame_6632f@example.som', '$2y$10$y1Lhg.ECu0LtX2uPhAq0.OgxQ3bAmSY131wBxiOXGNuwd2E5MioQy', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:04', '2025-12-16 09:27:04'),
(615, 'stu_farahhassan_9d06e', 'stu_farahhassan_9d06e@example.som', '$2y$10$3l66OFlXIZZg6Xc/u848ou3ZbNYjclIopya4wMbzAwyU01FEUc1Le', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:04', '2025-12-16 09:27:04'),
(616, 'stu_ayaanguled_17071', 'stu_ayaanguled_17071@example.som', '$2y$10$D8wRTkkNFxTdUEA7ynUuBOHWys200hkSEzoPazkho6hxzr3vznNem', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:05', '2025-12-16 09:27:05'),
(617, 'stu_najmanur_c2d13', 'stu_najmanur_c2d13@example.som', '$2y$10$Nyr56v99oY2tzcqcsU7XmebvRvN/X0PTSESYWVXAjUwOHynNSVtra', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:05', '2025-12-16 09:27:05'),
(618, 'stu_ayaanabdulle_bb7f5', 'stu_ayaanabdulle_bb7f5@example.som', '$2y$10$D/DCZFdXESGnbdn7FKV3GeYb0Q9Uh8/McM4XMGYYSa7ukE9K9NbQK', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:05', '2025-12-16 09:27:05'),
(619, 'stu_hassandirie_249c7', 'stu_hassandirie_249c7@example.som', '$2y$10$dFG/M9y4qEo1Zcn9VZ5SbOQev735DaRKj5dqu3cLGYXtWNUZbdu7a', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:05', '2025-12-16 09:27:05'),
(620, 'stu_abdiabdulle_543f0', 'stu_abdiabdulle_543f0@example.som', '$2y$10$mcM4RnTtt4mgZjIOoN9zJOEonJnFCB4L1Yrnc5HdJQUhNJeyflbNK', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:05', '2025-12-16 09:27:05'),
(621, 'stu_zamzammuse_e7d4f', 'stu_zamzammuse_e7d4f@example.som', '$2y$10$dtf3m3uC7iatO5BqZP2JC.AiWH5uWPrBEwpERn3nQLC/y0.Dic6/m', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:05', '2025-12-16 09:27:05'),
(622, 'stu_jamaabdulle_8512f', 'stu_jamaabdulle_8512f@example.som', '$2y$10$UWNtSvI4nXXnOF6z9lZ2hee59fB.ECWJs9QA23b2nD09u77EIVHSG', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:05', '2025-12-16 09:27:05'),
(623, 'stu_mukhtarmuse_609f0', 'stu_mukhtarmuse_609f0@example.som', '$2y$10$VSVl.4CqJg.2oXiAXyJ3Y.Nz6cBzRo5ZPTwOk0rRAcwhzcBte.XMi', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:05', '2025-12-16 09:27:05'),
(624, 'stu_ashawarsame_4a7c2', 'stu_ashawarsame_4a7c2@example.som', '$2y$10$TvYkDuezYGdFw.S5FCNsguN2fp/KkkE8s3lWY0Zkk8ZCM9qnx4zy6', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:05', '2025-12-16 09:27:05'),
(625, 'stu_hodanyusuf_be95f', 'stu_hodanyusuf_be95f@example.som', '$2y$10$A8fV0mEIluNk4QO2IA7k8.Y5B8NRBH9l2CAQCuUrMQKM/BcXGusb2', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:05', '2025-12-16 09:27:05'),
(626, 'stu_mukhtarsheikh_c4ed4', 'stu_mukhtarsheikh_c4ed4@example.som', '$2y$10$6UeKbrlg29Ug9lx6FvZIGurGUK7J3hx51Zlr/jmnMbrYxq6f5dPzO', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:05', '2025-12-16 09:27:05'),
(627, 'stu_mukhtarfarah_137e9', 'stu_mukhtarfarah_137e9@example.som', '$2y$10$9rdNZRRaEETR5.PTFCOlx.aBt97VfxwgnjoLn1NMY8r3udHEFXW2i', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:05', '2025-12-16 09:27:05'),
(628, 'stu_khadrasheikh_5d9ac', 'stu_khadrasheikh_5d9ac@example.som', '$2y$10$WuvCGSdkPeIpsBNrZbG3J.gCdBXCXLLqwwcXbFUrycAX4GzWcBa62', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:05', '2025-12-16 09:27:05'),
(629, 'stu_maryanadam_564b1', 'stu_maryanadam_564b1@example.som', '$2y$10$Li9DbyYZ4xRGiw//fhSm9uAwHO4qt2MVdbgyMJstzdEX0O.HCJr9K', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:05', '2025-12-16 09:27:05'),
(630, 'stu_mohamedfarah_9bd4e', 'stu_mohamedfarah_9bd4e@example.som', '$2y$10$VBScFtXg2.HZOC7Wb8WFguofyGLj8z4SDrmAvdlLVPBYiJPIHYZyq', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:05', '2025-12-16 09:27:05'),
(631, 'stu_safiyasaid_56c47', 'stu_safiyasaid_56c47@example.som', '$2y$10$gll6MuetcALyGQv/NNDKauMEUCeu/5JsHfYpYnfNeyLHA/gPn3jRq', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(632, 'stu_sahrayusuf_d0771', 'stu_sahrayusuf_d0771@example.som', '$2y$10$XqFnyA73HihmXtKIlMLy7ulCOb9PLDG5I.3w.U5b7tQ3Qtb7bxNVy', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(633, 'stu_fartunhassan_d8fc4', 'stu_fartunhassan_d8fc4@example.som', '$2y$10$8YZehromhNy0okU5LD7tR.i2Wh0v/H0NIpd3uFOgL4YKkXCy3RI2m', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(634, 'stu_sagalali_45f32', 'stu_sagalali_45f32@example.som', '$2y$10$2JlaxFV4RD71WEVvfDSAtuPm.0ttGzvLZm8Z1aK0ZS9PlKbHrxhWa', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(635, 'stu_hodanomar_13d38', 'stu_hodanomar_13d38@example.som', '$2y$10$xllKNQc2ENoPiVYqxbk7qOVFXus04G21aApXBnkkjbFzE2yVUiXtG', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(636, 'stu_hassanadam_37048', 'stu_hassanadam_37048@example.som', '$2y$10$NRgJQqDEgnBd95MWi8lXZ.ZmRBpdoU.mhFtMVZBdDBbozsEblUDyG', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(637, 'stu_mustafamuse_dd1e5', 'stu_mustafamuse_dd1e5@example.som', '$2y$10$cZDwb9RDB0u8vo55OCr69u53YnpOVDvgfGO8H.lSl9oC4SJV4Kn1i', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(638, 'stu_omarmohamed_3360c', 'stu_omarmohamed_3360c@example.som', '$2y$10$8m4BDtnyO.wtQ/sWe1WV0O1jl1a6tiC/.s2l.4pnQVNCnLdSik4pC', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(639, 'stu_ilhanmohamed_f5c9d', 'stu_ilhanmohamed_f5c9d@example.som', '$2y$10$diyDbgwhZjJfHsV/re.esudMYY67tYi45tqENFSkodI2Us/Do5I9O', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(640, 'stu_khadrasheikh_6c379', 'stu_khadrasheikh_6c379@example.som', '$2y$10$LpsobYYTwLlzyJ9sz.SBzeF0R1Pc8lUTxvtxlptwJW5WGbZDjTND.', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(641, 'stu_mohamedomar_c4bcf', 'stu_mohamedomar_c4bcf@example.som', '$2y$10$XMgnK.FpD.x37DUhNnqgLeENhnvZ0SQgZoRlozTFVtu0Cmou6k7HK', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(642, 'stu_nimcosheikh_4c783', 'stu_nimcosheikh_4c783@example.som', '$2y$10$gxlBgr2gprv3ejlSW7UdjeF81jFNxMimOR4oqRzY6p/rR5zMaGnE.', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(643, 'stu_ahmedjama_bef91', 'stu_ahmedjama_bef91@example.som', '$2y$10$nOlfHYQ6uSSwMby4WdjqWu1aYunbqtN/d9GKNcWfjuluqplpKD5MG', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(644, 'stu_mustafadirie_e22f0', 'stu_mustafadirie_e22f0@example.som', '$2y$10$vDwd8EbtAgwAgEp9fUWpt.bPsyHgX0N/hAFXKfWJgwYli29HHiT0m', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(645, 'stu_ilhansaid_b462e', 'stu_ilhansaid_b462e@example.som', '$2y$10$7arKXSAPX6.f6t6JeFBX5.KST1d9P0PnhGl94idRHGnUGMYSudVo6', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(646, 'stu_ahmedmohamed_829ab', 'stu_ahmedmohamed_829ab@example.som', '$2y$10$hsunrAL9qaCwh7F2AGJEb.qXQIph7kim9AXrt6ZsFLDeQk8X7jA1a', 4, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:06', '2025-12-16 09:27:06'),
(647, 'stu_ilhanadam_a3f7e', 'stu_ilhanadam_a3f7e@example.som', '$2y$10$e9CgKySFLSKIgSDN6UKjQOxHEupOhCWtNOCkBYAQR5QY.Hm7qNvea', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:07', '2025-12-16 09:27:07'),
(648, 'stu_ifrahabdulle_bb787', 'stu_ifrahabdulle_bb787@example.som', '$2y$10$bkx3qrteQJek8R8nkyCtZuBosnDpXzqZsUJ9TSP43zaWeslTCnnJG', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:07', '2025-12-16 09:27:07'),
(649, 'stu_hodanali_13874', 'stu_hodanali_13874@example.som', '$2y$10$BxdCIp.U9TclnpG70z1IYeQGr4fB8DQHOUgvcDIJdEekXk5tJw9sW', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:07', '2025-12-16 09:27:07'),
(650, 'stu_farahadam_455e2', 'stu_farahadam_455e2@example.som', '$2y$10$G9puvblDwXhfHTqeqD3IOuLt9BkSHDMCl4gH6TENgavmqR0VIRTmK', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:07', '2025-12-16 09:27:07'),
(651, 'stu_mahadyusuf_5a484', 'stu_mahadyusuf_5a484@example.som', '$2y$10$.RsbpV.jKIx7EK9npW/5Ju1nI/EOF9Axww23qMfSScG1I5ozGbuO.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:07', '2025-12-16 09:27:07'),
(652, 'stu_maryannur_6cf24', 'stu_maryannur_6cf24@example.som', '$2y$10$/JBBtxYkqb6AJxNyJtH4puSWTj5cYrlviH7a8cHi/RhT2wQOCDqky', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:07', '2025-12-16 09:27:07'),
(653, 'stu_ilhannur_134f2', 'stu_ilhannur_134f2@example.som', '$2y$10$yrw1D5wJn5btPNzHVzoeWem.yOuNG2VwGJ/wgfzE/P1q3QlRlAxuO', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:07', '2025-12-16 09:27:07'),
(654, 'stu_ashaosman_afae1', 'stu_ashaosman_afae1@example.som', '$2y$10$qyuK6sGhq2Fp4SOucPLsBeIsxhFx6uBe.aVlf24sJGtwYLucVx96q', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:07', '2025-12-16 09:27:07'),
(655, 'stu_ayaandirie_50c28', 'stu_ayaandirie_50c28@example.som', '$2y$10$xIuOYxuDUjv4l/LKxHgKruhifC9JQVuT/JfQzkCEd5ihPt8qf8wdm', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:07', '2025-12-16 09:27:07'),
(656, 'stu_safiyawarsame_2759f', 'stu_safiyawarsame_2759f@example.som', '$2y$10$uNdFmEltA0jNXfMxpa8nDeIcUHqYtmu9n6JPdcyoIaZV2dV6Zpxha', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:07', '2025-12-16 09:27:07'),
(657, 'stu_rahmajama_d9b60', 'stu_rahmajama_d9b60@example.som', '$2y$10$YdlZBq0oLKUnjrSnrN5iaebCwC/BbUYrXiLJOgfuB4nCQh0Akpsla', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:07', '2025-12-16 09:27:07'),
(658, 'stu_fartunwarsame_1e318', 'stu_fartunwarsame_1e318@example.som', '$2y$10$LaO2u3dfmFf1G1NAgqSLlO..5uNJDP6/TGq0CvRP1e9n/ZLIzSxKG', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:07', '2025-12-16 09:27:07'),
(659, 'stu_maryanabdullahi_5008e', 'stu_maryanabdullahi_5008e@example.som', '$2y$10$sJxYwFWGgEc8ODyvDEP0i./PpMBAQZIyNAwaZ4G9PrqeD6ZBwU/KS', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:07', '2025-12-16 09:27:07'),
(660, 'stu_maryanhassan_f106a', 'stu_maryanhassan_f106a@example.som', '$2y$10$pOc5Oyg6MLbr90P3924Qs.iOpSHK2GgtTBFWj4WvVj7RJP4EqD4Fa', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:07', '2025-12-16 09:27:07'),
(661, 'stu_hodanfarah_2e594', 'stu_hodanfarah_2e594@example.som', '$2y$10$wZgUYM.2FUfAtojhQdNUnefkD.I3ayDh3yZePP0K7NgdGD5GCX3rq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:07', '2025-12-16 09:27:07'),
(662, 'stu_zamzamabdullahi_1e026', 'stu_zamzamabdullahi_1e026@example.som', '$2y$10$Q60Rf2oE7DYEZmDoNJxGTOHLUI1CCwHEjfh/J2nQJWis0KWcS8TK6', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:08', '2025-12-16 09:27:08'),
(663, 'stu_mohamedhassan_b16eb', 'stu_mohamedhassan_b16eb@example.som', '$2y$10$actVzYuDA0RxlaOQbAKb5Ogn4Lvh4yznvy4PTHrTWKTlDln/FiBKW', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:08', '2025-12-16 09:27:08'),
(664, 'stu_najmaguled_7e1dc', 'stu_najmaguled_7e1dc@example.som', '$2y$10$51kXzCc/TeOmGvNo62iS7OYbihA1cJsFsur3epQnfjE/DIeEn8Vv.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:08', '2025-12-16 09:27:08'),
(665, 'stu_hassanyusuf_e4fed', 'stu_hassanyusuf_e4fed@example.som', '$2y$10$/Az9QXysaBaqI/I0eILVOekYsWzE8dtF/tHG7te9ahDM7EKZDnBaS', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:08', '2025-12-16 09:27:08'),
(666, 'stu_mukhtarroble_5af7f', 'stu_mukhtarroble_5af7f@example.som', '$2y$10$4Lw3c2CAVe9lCyFkJlfkbeF4sbvxTFyD1Xe5zR2paQyz.26PbnB0u', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:08', '2025-12-16 09:27:08'),
(667, 'stu_ahmedmuse_5c01d', 'stu_ahmedmuse_5c01d@example.som', '$2y$10$r64OExc.//BCp9qtxa0KP.4ZK5n0kqk1zzUIbuVjGZFSOGWJHwXnm', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:08', '2025-12-16 09:27:08'),
(668, 'stu_hodanroble_b8ba9', 'stu_hodanroble_b8ba9@example.som', '$2y$10$Vn22lLN75R/GTscJMIXWauITgIeeeWwkr.4FVp9gGnJy50DbRt/D6', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:08', '2025-12-16 09:27:08'),
(669, 'stu_safiyaismail_5dd8a', 'stu_safiyaismail_5dd8a@example.som', '$2y$10$DV0FaCqdqo7QAinPwvzBzO17BaLyxO2Af4T0.UtM8EuBCqtuGeB6a', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:08', '2025-12-16 09:27:08'),
(670, 'stu_hassanyusuf_2d69a', 'stu_hassanyusuf_2d69a@example.som', '$2y$10$.zAteC9SdBzSEPkCbpoAQ.5W9g5NOlr56v3mxSIm1zj4aX.C0s2h.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:08', '2025-12-16 09:27:08'),
(671, 'stu_mohamedali_e0fcc', 'stu_mohamedali_e0fcc@example.som', '$2y$10$dFNQKxamGmE1bNedns5iMO2yXkioliP8vYroGFm3plB71ODBcPnfu', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:08', '2025-12-16 09:27:08'),
(672, 'stu_farahosman_27e07', 'stu_farahosman_27e07@example.som', '$2y$10$WPbTdyUIRTk2/sLMzoZtTu.9k0lkCoMTHa6ZYQsziEhv1yUVOm0cy', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:08', '2025-12-16 09:27:08'),
(673, 'stu_ahmedmohamed_59b7b', 'stu_ahmedmohamed_59b7b@example.som', '$2y$10$QLUbzr127EYsUI4jL/d0judOlbXjakEDw58Xh4wxszePUeE8EBtl.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:08', '2025-12-16 09:27:08'),
(674, 'stu_nimcoroble_97925', 'stu_nimcoroble_97925@example.som', '$2y$10$uLbFVmuKJSGYandLeI6IneUPl8sPN4p41NDsyAz2EObZ0LLEHvntW', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:08', '2025-12-16 09:27:08'),
(675, 'stu_jamaabdullahi_d11ae', 'stu_jamaabdullahi_d11ae@example.som', '$2y$10$0acj17SWorKP8SpRViCZIuHk.vwSZGGjrLCB01gRTm5rWvLimxNDq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:08', '2025-12-16 09:27:08'),
(676, 'stu_hassanomar_756fc', 'stu_hassanomar_756fc@example.som', '$2y$10$g01qTudNxWg9P8iZslQjr.D0gkCuKFUWXF.bJYNXIzSOnAabIEpAm', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:08', '2025-12-16 09:27:08'),
(677, 'stu_hassanomar_adcc7', 'stu_hassanomar_adcc7@example.som', '$2y$10$4D2WghxZDIT5IsYelclsNOSzXLB2wXeyufR.e2vCRkd5F9oGMTuQy', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:09', '2025-12-16 09:27:09'),
(678, 'stu_alinur_919d8', 'stu_alinur_919d8@example.som', '$2y$10$qeGW75VHTjVgENuy0dU5pOhklujzrCyNX2upVkpa78x8fqlef7YV2', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:09', '2025-12-16 09:27:09'),
(679, 'stu_hodanwarsame_fb92f', 'stu_hodanwarsame_fb92f@example.som', '$2y$10$7/iYWaLpMe7CZLlUoI265.KvJXzCfZ1wCsyYUXAcGJT5/DCY/7N5e', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:09', '2025-12-16 09:27:09'),
(680, 'stu_abdimohamed_1d465', 'stu_abdimohamed_1d465@example.som', '$2y$10$3QRY/QyQjmSmrGx/iD109u9DwBSiYlBjClB74lv38EYB7TRVwjrf2', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:09', '2025-12-16 09:27:09'),
(681, 'stu_ilhanismail_fb930', 'stu_ilhanismail_fb930@example.som', '$2y$10$QSgPoChH0mJacRDzjc0zXe87b9gUVD1RKiHkz/ZeIwSAg75RrpmIm', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:09', '2025-12-16 09:27:09'),
(682, 'stu_safiyaabdullahi_69582', 'stu_safiyaabdullahi_69582@example.som', '$2y$10$RNc3XD/Qg6e3xsw1y0vineG/PpxK0XeszKLfjMU.D3zKjnIytfk7e', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:09', '2025-12-16 09:27:09'),
(683, 'stu_najmasaid_830f0', 'stu_najmasaid_830f0@example.som', '$2y$10$p3rfgPIJ4ZPtcGfSwtNm1.cwX2Q4uWPOIBmcx7I0PBE7GbVL861oW', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:09', '2025-12-16 09:27:09'),
(684, 'stu_hassanfarah_76888', 'stu_hassanfarah_76888@example.som', '$2y$10$IQyetpWJ5LtCbVjY46BCn.XbmqLNFwvMiZPMnxCSnDhRy3PtfTUHq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:09', '2025-12-16 09:27:09'),
(685, 'stu_mustafafarah_6916f', 'stu_mustafafarah_6916f@example.som', '$2y$10$sBApHGmnUACAVidz5iJ.YuzfZlJc9p786rwarVghHoWuCHxyDm3Ei', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:09', '2025-12-16 09:27:09'),
(686, 'stu_nimcoadam_708a2', 'stu_nimcoadam_708a2@example.som', '$2y$10$dXIX9KSOoNKOAHruRp4/c.UW3EgZnt5XErooOu2xPNi5Apn0x9ZS2', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:09', '2025-12-16 09:27:09'),
(687, 'stu_mustafasaid_828a1', 'stu_mustafasaid_828a1@example.som', '$2y$10$VZnU0wKibSdqB.N5q06V..Y1S6LbXO.jmPAXJpu2avM7PXd.Glx6a', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:09', '2025-12-16 09:27:09'),
(688, 'stu_mahadomar_10cf2', 'stu_mahadomar_10cf2@example.som', '$2y$10$gdXHPx4BdZ3QOb2qUmIM9OM2PycE8VfhuwGrIZNzkxd.fOyihd0p.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:09', '2025-12-16 09:27:09'),
(689, 'stu_abdiguled_3a6e0', 'stu_abdiguled_3a6e0@example.som', '$2y$10$F5l2y1YJiIzXg937rSukyukd7uMthdlCnU0pLnuYwdT2H3CJwdUVy', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:09', '2025-12-16 09:27:09'),
(690, 'stu_hassandirie_39c2c', 'stu_hassandirie_39c2c@example.som', '$2y$10$tf8sa9f/x5bo0Xz.se.mSORUYXTLyDhLDQyJ8aY.fKAlDa96SSVui', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:09', '2025-12-16 09:27:09'),
(691, 'stu_ashaabdullahi_d0cf1', 'stu_ashaabdullahi_d0cf1@example.som', '$2y$10$nEFQjB5Odwdy0usVKy3A9upeEMdv2JgBG/zSpQIwZcrk/KKHt9ASW', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:09', '2025-12-16 09:27:09'),
(692, 'stu_mustafasaid_db1c5', 'stu_mustafasaid_db1c5@example.som', '$2y$10$/7HvlXLFyvRgn/fupiD1yODSEjFpSEuzKRsXUc3hGHjGBlYXiAyP.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:09', '2025-12-16 09:27:09'),
(693, 'stu_ilhanhassan_1054e', 'stu_ilhanhassan_1054e@example.som', '$2y$10$Sm/9kB8OcIJu5H7aJM3FD.8Wgh7.TXAl6FLdeVSR9.EkoD1bvZdP.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:09', '2025-12-16 09:27:09'),
(694, 'stu_abdiyusuf_a24cb', 'stu_abdiyusuf_a24cb@example.som', '$2y$10$Ag3/CIZyI2JieZZVL8Gkk.iAY.S4LfeC3KHfPZ9OQ3Cr6mBxGSgOi', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:10', '2025-12-16 09:27:10'),
(695, 'stu_maryanfarah_37dae', 'stu_maryanfarah_37dae@example.som', '$2y$10$IZCc/WEWOoHlDYRo/HRWT.aBBslZbeyQ/KzYPH3i/FDN9WRWS57MK', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:10', '2025-12-16 09:27:10'),
(696, 'stu_ilhanomar_86a48', 'stu_ilhanomar_86a48@example.som', '$2y$10$uHxNDvF8Fat9O./RKnkX8.U8WzZrMq1sntN90k3qrlp7lr1FHE31e', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:10', '2025-12-16 09:27:10'),
(697, 'stu_fartunjama_007d7', 'stu_fartunjama_007d7@example.som', '$2y$10$UsigLMsdzrx6ZY5F7UoOc.NRlmpz4xhMR6SpSdpkuarZsVbjmpnli', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:10', '2025-12-16 09:27:10'),
(698, 'stu_mahadabdulle_87f0a', 'stu_mahadabdulle_87f0a@example.som', '$2y$10$pZXA6CGvu8yCn32Fx1ojKOpU5QnpN9a7b4YFaMk1jjTvX6z/qkgzS', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:10', '2025-12-16 09:27:10'),
(699, 'stu_mohamedyusuf_6ff8a', 'stu_mohamedyusuf_6ff8a@example.som', '$2y$10$FoSDEsDAFBzSnv9AsNCWh.ZHAbvHIjgcVUhUCn8E7DcXnvQZLwTA2', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:10', '2025-12-16 09:27:10'),
(700, 'stu_sagalsheikh_35678', 'stu_sagalsheikh_35678@example.som', '$2y$10$M4vkTBJkXj3fIazHCon4/ehn5vv4gMVI50vC5DyAcAKP2RdKMv52y', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:10', '2025-12-16 09:27:10'),
(701, 'stu_maryanfarah_9f8e6', 'stu_maryanfarah_9f8e6@example.som', '$2y$10$G48l1mYlsu0VSPWBHZIleubszaVzKiIJ6uYfq3FZ0rX1Mtx2.cqSC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:10', '2025-12-16 09:27:10'),
(702, 'stu_laylaali_71f77', 'stu_laylaali_71f77@example.som', '$2y$10$ccEhqn9zxRWFFICm/7W6weWSfblyhK.PE8UHBVTkj/CL7tBYnBLiy', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:10', '2025-12-16 09:27:10'),
(703, 'stu_hodanosman_c1cd1', 'stu_hodanosman_c1cd1@example.som', '$2y$10$aeX5itBaw4d1Vv7bI4pmueKzI46rKVoJEC0lfNLajeNRJgki.lhOS', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:10', '2025-12-16 09:27:10'),
(704, 'stu_laylaomar_715d1', 'stu_laylaomar_715d1@example.som', '$2y$10$193ejUv8YtbaImMhQbirYOvUtAYX7umTCIrng2urKQ1H4pjfl2/dS', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:10', '2025-12-16 09:27:10'),
(705, 'stu_nimcoabdullahi_7176a', 'stu_nimcoabdullahi_7176a@example.som', '$2y$10$OER.PAi8maX/tgaq7C06yOekJECbEQrh39B1xrGezRRi.RsCqREKa', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:10', '2025-12-16 09:27:10'),
(706, 'stu_ibrahimyusuf_e84f0', 'stu_ibrahimyusuf_e84f0@example.som', '$2y$10$EoKkpnuyMtPhyrrfE9oZ3ejV5MH3kmghyC/fbkvaBkHwrJlQTz9uO', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:10', '2025-12-16 09:27:10'),
(707, 'stu_hassanroble_1c6a8', 'stu_hassanroble_1c6a8@example.som', '$2y$10$vsy.zupy0IV/VeNHkwoASOkmtpcjeVHXlKWNbEMZo6.0nRa7FyQ.a', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:10', '2025-12-16 09:27:10'),
(708, 'stu_hodanwarsame_41cfd', 'stu_hodanwarsame_41cfd@example.som', '$2y$10$n9FJvPkazwDROnbk9bwEyeFWztPuFaP2s3p4cZTGyMfzx8oT/vPUq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:10', '2025-12-16 09:27:10'),
(709, 'stu_najmawarsame_e32eb', 'stu_najmawarsame_e32eb@example.som', '$2y$10$NPEViSs074QVW2WazZsIxOdZmyM0wtHOTfkCPyCCVbcx9Ri18Knay', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:10', '2025-12-16 09:27:10'),
(710, 'stu_zamzamroble_fcd2a', 'stu_zamzamroble_fcd2a@example.som', '$2y$10$9YpQKkDaxqOthsx8pk7lNe9LMwfJxFvT9u2RIxrbYlV4b9evrqBxa', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:11', '2025-12-16 09:27:11'),
(711, 'stu_ashajama_1e64b', 'stu_ashajama_1e64b@example.som', '$2y$10$W3ZoGlM3spL67lkq0Ld1sO5Pf0IXzejCnynp4Isz4DYt27SeA38oC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:11', '2025-12-16 09:27:11'),
(712, 'stu_ibrahimnur_55d67', 'stu_ibrahimnur_55d67@example.som', '$2y$10$pn1YP1BZqX2FYA8p0PlI9O1pLED6BfwpdeoBlu96//U4alzxj64Qu', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:11', '2025-12-16 09:27:11'),
(713, 'stu_alisaid_9bc40', 'stu_alisaid_9bc40@example.som', '$2y$10$4Qxd4PdM676SPC7ZV49o/uktLRIqtv80cxWeQdHvnhU3bsl5awyiK', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:11', '2025-12-16 09:27:11'),
(714, 'stu_maryanali_12a79', 'stu_maryanali_12a79@example.som', '$2y$10$xsL2F7pJMSDrZh6U7wmJjuW4bs5sFiS3DyP0V30ifL/y7j68df/ne', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:11', '2025-12-16 09:27:11'),
(715, 'stu_mahadguled_e1c0d', 'stu_mahadguled_e1c0d@example.som', '$2y$10$shIMAnQY7RAeixuN1kW9BuNowtWUB.YUv7ihMnozbZZWqyYvXsKDy', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:11', '2025-12-16 09:27:11'),
(716, 'stu_ibrahimhassan_cac9a', 'stu_ibrahimhassan_cac9a@example.som', '$2y$10$lnMA/xViQqQQwG01bf/pzuUdKvFGijTVpr2utHmWibW84UMk8FlN6', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:11', '2025-12-16 09:27:11'),
(717, 'stu_ahmedroble_645a5', 'stu_ahmedroble_645a5@example.som', '$2y$10$FGF5j7dbq3ZBjed/Dy4h9OfFQjrLHpI8bnokCHOOo/KcuWXwaEGXq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:11', '2025-12-16 09:27:11'),
(718, 'stu_sagalhassan_e51f3', 'stu_sagalhassan_e51f3@example.som', '$2y$10$f8h/PDFPYA22il/4C7h1xefTRzpldlEtz4.r2Jjyv8ptCvgG96WzS', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:11', '2025-12-16 09:27:11'),
(719, 'stu_ibrahimmohamed_d1f8d', 'stu_ibrahimmohamed_d1f8d@example.som', '$2y$10$9E/R3X4UrBf6T9lkUYXpa.V.7oD1ruahrg6/5mNBENRFkZ5HSA3V6', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:11', '2025-12-16 09:27:11'),
(720, 'stu_khadranur_ba448', 'stu_khadranur_ba448@example.som', '$2y$10$8WTG3D4qk/LTxTj38YNrb.eeYHwK71zsglElDAyBdCt8ydAc2c.KK', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:11', '2025-12-16 09:27:11'),
(721, 'stu_fartunabdulle_7ae65', 'stu_fartunabdulle_7ae65@example.som', '$2y$10$.uFrsAR.ZeP6YtmrIcE6xewqx4/1QtTsXg2jmvh.GpD8N5r/7.rIy', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:11', '2025-12-16 09:27:11'),
(722, 'stu_laylamuse_6e790', 'stu_laylamuse_6e790@example.som', '$2y$10$LjyI2qNXAv4t.iMEHwkrfuKXFcZV4ChvuAcQOQEufEJq.R4.sLWam', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:11', '2025-12-16 09:27:11'),
(723, 'stu_najmaismail_77017', 'stu_najmaismail_77017@example.som', '$2y$10$qMq3o5KMEPb5AsgiQF2yneIqQotAsy7rLIsRQk2b0AuLnMjHSpMPi', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:11', '2025-12-16 09:27:11'),
(724, 'stu_maryanabdullahi_91437', 'stu_maryanabdullahi_91437@example.som', '$2y$10$nCom3xrjvYmuudlExP0PweX4A3xPvRkLKTc87J.SKzOv1BJ1AseVW', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:11', '2025-12-16 09:27:11'),
(725, 'stu_maryansheikh_d300f', 'stu_maryansheikh_d300f@example.som', '$2y$10$XuWHTc5Gsrl3NPomf23GHevkeCUnDD0QXVStJC.sSGM9SDeBezVoe', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:11', '2025-12-16 09:27:11'),
(726, 'stu_mustafaismail_03072', 'stu_mustafaismail_03072@example.som', '$2y$10$KG1JYOUCX62aVWsn.0AtUevYdtUkp9QOLr24k8As9qNQGRjM8CKs2', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:12', '2025-12-16 09:27:12'),
(727, 'stu_omarmuse_6eb1b', 'stu_omarmuse_6eb1b@example.som', '$2y$10$bveNRWKXfchoDtMq.7a.H.p01OEeXlEi6KmrMZJ2Q5UhI1eUunW9O', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:12', '2025-12-16 09:27:12'),
(728, 'stu_laylasheikh_59fec', 'stu_laylasheikh_59fec@example.som', '$2y$10$qouZVIRw7ioU7XaHdZXJnOKml5bbXa.SN8ucRzETF7sy.GrQr/M36', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:12', '2025-12-16 09:27:12'),
(729, 'stu_laylayusuf_92a88', 'stu_laylayusuf_92a88@example.som', '$2y$10$W4qFN3a3u9MJMn74xMboS.Ys/e7kqPuZxC.gKxXJ9KNtXzHXdAqIy', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:12', '2025-12-16 09:27:12'),
(730, 'stu_laylaomar_48a1d', 'stu_laylaomar_48a1d@example.som', '$2y$10$eD5MupNviGzNUz86GrvrCeKuelK6XCVv8flfTtnzzJieGCqEbRSpy', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:12', '2025-12-16 09:27:12'),
(731, 'stu_maryannur_848af', 'stu_maryannur_848af@example.som', '$2y$10$Dg5Dd1EtFzRjzIEBHgT7Su/hyJ5mF96oV/RNNEzZDWkpnD9OY4qjG', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:12', '2025-12-16 09:27:12'),
(732, 'stu_najmajama_9cdc3', 'stu_najmajama_9cdc3@example.som', '$2y$10$.slSsban5cY1940d0tYNJe0ZDyFQfG0E.cO/paBk2.jUSXq.dLhea', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:12', '2025-12-16 09:27:12'),
(733, 'stu_rahmawarsame_47a13', 'stu_rahmawarsame_47a13@example.som', '$2y$10$UXf9D/M.Y6G4CbfRfXEGGeyOYbGbgYnrXeRl5T.xrPk2BMA/Fg6eO', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:12', '2025-12-16 09:27:12'),
(734, 'stu_ilhanyusuf_4c519', 'stu_ilhanyusuf_4c519@example.som', '$2y$10$MYc9pAX5UHkx1rd/xJFJ6.VibD5PJqNCcwoUwPO/hNp/Z3diL9I5y', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:12', '2025-12-16 09:27:12'),
(735, 'stu_omarroble_992d4', 'stu_omarroble_992d4@example.som', '$2y$10$.tiQAuQ2P1PmQ68E/RRLMuoFt3rWAXags2nyyUEwQMEb60dMBkBci', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:12', '2025-12-16 09:27:12'),
(736, 'stu_hassanmohamed_9d6fe', 'stu_hassanmohamed_9d6fe@example.som', '$2y$10$O.jjxsCJSDup34AOWibageq8S0ag.TP7WwNxnuQywYbMxg1u.pBlK', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:12', '2025-12-16 09:27:12'),
(737, 'stu_rahmahassan_2e7c3', 'stu_rahmahassan_2e7c3@example.som', '$2y$10$uXRZ37iUXCh4uRIYbygTSOKkFF8Eekv6qvqclWAVw6b8hA/vGquFC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:12', '2025-12-16 09:27:12'),
(738, 'stu_mustafamuse_cda09', 'stu_mustafamuse_cda09@example.som', '$2y$10$7wZYRya3lzKjnnwXyWOZgeaJgE6Au9WUTedNl9mIj3FGKabvmD9lC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:12', '2025-12-16 09:27:12'),
(739, 'stu_mustafaadam_8b2aa', 'stu_mustafaadam_8b2aa@example.som', '$2y$10$SGKACPpvryq5NZi9apckaOY7TN0RDmd0McLToHR63.saqLLKOlui.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:12', '2025-12-16 09:27:12'),
(740, 'stu_jamasaid_b8641', 'stu_jamasaid_b8641@example.som', '$2y$10$SQVVRe6r1Dp9D.q.sMDiBeMzWuNTJANPlMwDf8e9elLPkJWeZTtY.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:12', '2025-12-16 09:27:12'),
(741, 'stu_maryanosman_b6867', 'stu_maryanosman_b6867@example.som', '$2y$10$2n2GaMn0y/HvpxFvnvc.De/KV/d0BaFFqnCjGsaJQ1cgGLnvpaufe', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:12', '2025-12-16 09:27:12'),
(742, 'stu_khadrayusuf_08589', 'stu_khadrayusuf_08589@example.som', '$2y$10$MHqJ3sj9m.V1OHqhR/cLf.8fx4sMrcocltIWk2JOphYF.SoSWfhsu', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:13', '2025-12-16 09:27:13'),
(743, 'stu_yusufhassan_ef721', 'stu_yusufhassan_ef721@example.som', '$2y$10$V2EhiUtQ6gU7IBtWyqKFIe7JvwYuJRBkNuTzl5HAQLBMVWqsMKAv.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:13', '2025-12-16 09:27:13'),
(744, 'stu_mohamedsaid_3165e', 'stu_mohamedsaid_3165e@example.som', '$2y$10$L2LTClQXrNyaYDsp/.CxleSzALZCvy/w5ebEH/RXWlp65GhId3jaK', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:13', '2025-12-16 09:27:13'),
(745, 'stu_sahrajama_a410a', 'stu_sahrajama_a410a@example.som', '$2y$10$FC05htpTIoAP8q.f49eJfemODE4ghzhy8v93ojjRZMg.buAirUKMC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:13', '2025-12-16 09:27:13'),
(746, 'stu_omarjama_17525', 'stu_omarjama_17525@example.som', '$2y$10$JXrra2XH1cf9Dmhjv8M9/.RikYreHkv.5lCvxiaoG26D5SHjJKgk6', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:13', '2025-12-16 09:27:13'),
(747, 'stu_mustafaomar_2242d', 'stu_mustafaomar_2242d@example.som', '$2y$10$9v2hpBbRHjpe4FOUb1qjg.dZzNVw6BbhwEpWGtFyEy6vyB9wUElWK', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:13', '2025-12-16 09:27:13'),
(748, 'stu_hassanjama_f05d0', 'stu_hassanjama_f05d0@example.som', '$2y$10$V44TqjuaDiRxlcxVYeB0KuwI3Ig64Uh4lhN2BE4lfsR1k6e2ss/li', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:13', '2025-12-16 09:27:13'),
(749, 'stu_mahadosman_edb9a', 'stu_mahadosman_edb9a@example.som', '$2y$10$foKx6o/b4Wy.mF0btI6u7OjQrpnJPqG1MnpMZGZB2zYjNtBT5qwUa', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:13', '2025-12-16 09:27:13'),
(750, 'stu_nimconur_b0d3b', 'stu_nimconur_b0d3b@example.som', '$2y$10$ufnLi4klVjTgyTRxrx0Zr.AO8gzuyke0czZcKEpGj8hjE7zeESEai', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:13', '2025-12-16 09:27:13'),
(751, 'stu_safiyasheikh_6ed8b', 'stu_safiyasheikh_6ed8b@example.som', '$2y$10$awwpp8g.THUZ5kGz6Wy6EuhlyrOWMxhVp8xpITrTV0p7/Ir7DBuxG', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:13', '2025-12-16 09:27:13'),
(752, 'stu_ifrahjama_88228', 'stu_ifrahjama_88228@example.som', '$2y$10$oS1IIMrqHc560KgySbLwDuPSoV5hV0ANVhJzt1fXQGzHgZL2mGe9e', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:13', '2025-12-16 09:27:13'),
(753, 'stu_zamzamroble_0756f', 'stu_zamzamroble_0756f@example.som', '$2y$10$q6J7mboS7cO/0G2UVnMDgOKjvtG/fvUqtbkNNL1fT2OIGxX05sm9S', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:13', '2025-12-16 09:27:13'),
(754, 'stu_laylaali_40be2', 'stu_laylaali_40be2@example.som', '$2y$10$fZeRzepNlZmJvUTXg6MdY.XiqfMrFCTsTT75Mv.1WZiNA18SLZ9VG', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:13', '2025-12-16 09:27:13'),
(755, 'stu_ibrahimroble_d282b', 'stu_ibrahimroble_d282b@example.som', '$2y$10$6dyLgefYEmFv9jaI8E1k7OF.8jZ6uXZcBAfTElMnc75WOuG0AbUJm', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:13', '2025-12-16 09:27:13'),
(756, 'stu_laylasaid_d96f2', 'stu_laylasaid_d96f2@example.som', '$2y$10$o44u9qvHJlk6MHtkvDoLDeZ2If/NoV8YEmw7ahoSf9/GCSKamskvK', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:13', '2025-12-16 09:27:13'),
(757, 'stu_layladirie_d41e8', 'stu_layladirie_d41e8@example.som', '$2y$10$9dkID9dUxUcVpLHHB1tEDusgKr3KZkYPnKHyyJIGJCgordcxz.hwa', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:14', '2025-12-16 09:27:14'),
(758, 'stu_ilhansaid_b9de6', 'stu_ilhansaid_b9de6@example.som', '$2y$10$zqVveC5ORA1oBac7o6BZ7OmZAiraVtoYch5CjZd0Qrs2etVemZGkq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:14', '2025-12-16 09:27:14'),
(759, 'stu_omarhassan_b17ae', 'stu_omarhassan_b17ae@example.som', '$2y$10$AR1gWfYNZEfJtotLeLpgwe8kmqzmtXrej/oC.TrkvOKW8AWphWRmi', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:14', '2025-12-16 09:27:14'),
(760, 'stu_hodanhassan_1a01c', 'stu_hodanhassan_1a01c@example.som', '$2y$10$GSjezF3Xh8wnoa8R8.zMauWuF2NB.kfj507wnojTBGFHoHk5NjB82', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:14', '2025-12-16 09:27:14'),
(761, 'stu_najmadirie_cb5de', 'stu_najmadirie_cb5de@example.som', '$2y$10$VE/D2yN1MwetO7/EZAj/WOSlcgqQMjmO8PgtECdIYAyC/clMXUZ7i', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:14', '2025-12-16 09:27:14'),
(762, 'stu_nimcohassan_bcab6', 'stu_nimcohassan_bcab6@example.som', '$2y$10$Rm/YV820s2LKrCbfDhs6outjQhnIS/2M4KXjbbPebRfjWlJuxdYEC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:14', '2025-12-16 09:27:14'),
(763, 'stu_omarosman_f9ec0', 'stu_omarosman_f9ec0@example.som', '$2y$10$rLBjzYul/hF5EQQ3B5iRd.WrBQbxls8SS2ZelwHwprQoOex9o53pC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:14', '2025-12-16 09:27:14'),
(764, 'stu_hodanjama_2620e', 'stu_hodanjama_2620e@example.som', '$2y$10$m6uM3vWplSyFM3iFnAzxKe3NpfOKK0IU3FaWrJDJNyWSzCDmyf/SW', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:14', '2025-12-16 09:27:14'),
(765, 'stu_aliabdulle_5ddd9', 'stu_aliabdulle_5ddd9@example.som', '$2y$10$9YKYmusM6iWlCgpyhBN47.fI1ddBEDSEHzyxe4T.gn23K.yztt/5W', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:14', '2025-12-16 09:27:14'),
(766, 'stu_abdisaid_d6d9e', 'stu_abdisaid_d6d9e@example.som', '$2y$10$tL1G2qIGX0WF6KktvWwBaeVHAvi5XLGyU0iE2uKX.dfQftTQ1AhSS', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:14', '2025-12-16 09:27:14'),
(767, 'stu_aliyusuf_c8142', 'stu_aliyusuf_c8142@example.som', '$2y$10$o.t5HQpp.DhS1wXXI.X.weNY41RTe6hxTVP4apBclDvNImjfRPJzi', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:14', '2025-12-16 09:27:14'),
(768, 'stu_hodansaid_66afb', 'stu_hodansaid_66afb@example.som', '$2y$10$JTPvqw3iNIbkVF94XbS4Muu41ZOZV36.UvZw/1oub75MPb3XTKA8K', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:14', '2025-12-16 09:27:14'),
(769, 'stu_hodanmuse_18c3f', 'stu_hodanmuse_18c3f@example.som', '$2y$10$.ZNLoU2EZKMzyClKaOaBHe3g02su2OzTL7qrtjO2rtCUgdjU2kVRq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:14', '2025-12-16 09:27:14'),
(770, 'stu_ilhanomar_87fe7', 'stu_ilhanomar_87fe7@example.som', '$2y$10$6zbCkqzNFst0E6KIzZZvj.Es9K1U5TJiNogQckn3AP75pIERXz0Zm', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:14', '2025-12-16 09:27:14'),
(771, 'stu_rahmaguled_9485c', 'stu_rahmaguled_9485c@example.som', '$2y$10$tm.0ngS5XPjqceP0tIdmTev.KEqp2QnTAQUa8Y.MqC.dS6Ew85Ci.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:14', '2025-12-16 09:27:14'),
(772, 'stu_omarmohamed_0f7a0', 'stu_omarmohamed_0f7a0@example.som', '$2y$10$PekVag.0qAoeWzA81mh9jOEW85OIQwm24a80P6RFeXV0IDIF9/SdG', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:14', '2025-12-16 09:27:14'),
(773, 'stu_safiyaismail_04864', 'stu_safiyaismail_04864@example.som', '$2y$10$XxT/BVjjPY2TQtoLcbwK1e85AWAMs4IKzsMEVEtCtUk3pvmEN/bfm', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:15', '2025-12-16 09:27:15'),
(774, 'stu_zamzammohamed_2d4d6', 'stu_zamzammohamed_2d4d6@example.som', '$2y$10$rDraQWkXPCmfBLAJpF5XQOCozX0RREM2GfubNlh36wK2iupLY604a', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:15', '2025-12-16 09:27:15'),
(775, 'stu_omarabdulle_a0089', 'stu_omarabdulle_a0089@example.som', '$2y$10$b.oit97uJNFHXcuDLxN6J.7TnEHe5i1xVYHMY6UsudkUhDfJhXb/S', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:15', '2025-12-16 09:27:15'),
(776, 'stu_mohamedabdullahi_20ecc', 'stu_mohamedabdullahi_20ecc@example.som', '$2y$10$bzlFuiNSzULXT3Nj9T3VIuvj7fyc/Zv5gvQ2NdjL2wIlRnTtpjXx.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:15', '2025-12-16 09:27:15'),
(777, 'stu_zamzamwarsame_8946d', 'stu_zamzamwarsame_8946d@example.som', '$2y$10$Xtr16CGfXSKJ1zJK0pbKVufFFmEd0gwxUr9DZxe3SfYGajy1Ck2U6', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:15', '2025-12-16 09:27:15'),
(778, 'stu_maryanismail_7a452', 'stu_maryanismail_7a452@example.som', '$2y$10$xOqLvAfAgQxxJ6Ke7AJgAeBrwgbhj1RmZzej93IUScrE4aPw/zl/K', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:15', '2025-12-16 09:27:15'),
(779, 'stu_maryanali_33688', 'stu_maryanali_33688@example.som', '$2y$10$RvPMCSr3apI9cdDwjEjrH.HhmfAduOXpkRNlbFjoXFyJkJOvF2jDG', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:15', '2025-12-16 09:27:15'),
(780, 'stu_najmasheikh_f255b', 'stu_najmasheikh_f255b@example.som', '$2y$10$Z5XCHaikpmxtubEGORkX1.2DIe1nhqJOE9fF8r7GfxiLAe4hHluTK', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:15', '2025-12-16 09:27:15'),
(781, 'stu_khadraali_209e8', 'stu_khadraali_209e8@example.som', '$2y$10$v3.ElENBdbEqkJib6zv43OjcHEmbA7sy/JzG6ggVVisuy4fmVvxq2', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:15', '2025-12-16 09:27:15'),
(782, 'stu_farahomar_e96f7', 'stu_farahomar_e96f7@example.som', '$2y$10$aLYkdMAT7HmjHIFXCWE51.ipYY3uSUHkkayaGAAbu3OdOJPHSHUhC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:15', '2025-12-16 09:27:15'),
(783, 'stu_mohamedmuse_94742', 'stu_mohamedmuse_94742@example.som', '$2y$10$VWnUr2NqZc0I.GK8kSEI5O0Sdw78PVgO4EuA0kxv1KgLDEo0lDWJy', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:15', '2025-12-16 09:27:15'),
(784, 'stu_ilhanhassan_8dd6f', 'stu_ilhanhassan_8dd6f@example.som', '$2y$10$4pjScTFXIFVRk4G1ffj3K.Ud3upZogN1WgW.yNM7I4BLLxOFn2aza', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:15', '2025-12-16 09:27:15'),
(785, 'stu_najmanur_c04c5', 'stu_najmanur_c04c5@example.som', '$2y$10$PQMjMBDaWXjpzId9Z6bdxOm352.fAeiWVMSzd4sFce2preWyQxJGi', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:15', '2025-12-16 09:27:15'),
(786, 'stu_mukhtardirie_4beb8', 'stu_mukhtardirie_4beb8@example.som', '$2y$10$lRUMoJtcjc6mPAmcTF71..EtgEEM8BA0yBOwjZ8jHoc1DO7qiDC6i', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:15', '2025-12-16 09:27:15'),
(787, 'stu_omaromar_e4c79', 'stu_omaromar_e4c79@example.som', '$2y$10$bidhV1jwdtExCw/4Gdw3levKWcP6WUpgI6MOM7kl0/0jtHpq2qeS6', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:15', '2025-12-16 09:27:15'),
(788, 'stu_jamahassan_26042', 'stu_jamahassan_26042@example.som', '$2y$10$Npu287wG5efzFTko3BatNOpYvkTY/oWfWXxLorkWqf56lk3IHI6EG', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:15', '2025-12-16 09:27:15'),
(789, 'stu_khadrasaid_2f5c4', 'stu_khadrasaid_2f5c4@example.som', '$2y$10$v43DhGjkSMCKHO1F56i2H.5XLnhgLHKQJj0tN5V3NO3N/xLW6dshO', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:16', '2025-12-16 09:27:16'),
(790, 'stu_zamzamadam_f4f48', 'stu_zamzamadam_f4f48@example.som', '$2y$10$nq/lJGbP6dOuP8XG6Ms11OkFEZzwlan8sdl2.FZczLmfUQffI7Cya', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:16', '2025-12-16 09:27:16'),
(791, 'stu_ismailfarah_9846b', 'stu_ismailfarah_9846b@example.som', '$2y$10$VmTAdkedTaq3xrxjHWaFSOK7e.9zl8w8uaZTxfFHR6HK6cgqQ3nO.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:16', '2025-12-16 09:27:16'),
(792, 'stu_ahmedwarsame_8512e', 'stu_ahmedwarsame_8512e@example.som', '$2y$10$9CrB0eP9j.mkYGUd8IA6WeQRoXrpvpZwJDH24mMg/s8eetRlJBqTK', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:16', '2025-12-16 09:27:16'),
(793, 'stu_farahosman_d55ca', 'stu_farahosman_d55ca@example.som', '$2y$10$obzdJDMkZ2JSad4jLG8/bORWrz5oFs76bCB3KvnVvB7Ertf0FiSle', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:16', '2025-12-16 09:27:16'),
(794, 'stu_farahfarah_660c1', 'stu_farahfarah_660c1@example.som', '$2y$10$0gFNLShUUI29xvjj3ZlDbuNGsBGnj8vK9EqDK.JcZkOWNDwXhXY0.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:16', '2025-12-16 09:27:16'),
(795, 'stu_safiyaismail_bb4c8', 'stu_safiyaismail_bb4c8@example.som', '$2y$10$3QBQyO3k3sLon6L6TIFuneibfSDTSuhFGED8fMgi7rnUrOGHrhAHC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:16', '2025-12-16 09:27:16'),
(796, 'stu_safiyawarsame_e3e0c', 'stu_safiyawarsame_e3e0c@example.som', '$2y$10$FqJYGe2LEkFZU4qK306J7Ol2i2mg9c0TW5N2SeqCSMZsOkVzyOEHq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:16', '2025-12-16 09:27:16'),
(797, 'stu_najmadirie_d3cdd', 'stu_najmadirie_d3cdd@example.som', '$2y$10$n05Ry0Ntn05SuSk1QYzqte2cfTVFqYLN2uLC2886RyfJ.O.ptD4gq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:16', '2025-12-16 09:27:16'),
(798, 'stu_mohameddirie_73e87', 'stu_mohameddirie_73e87@example.som', '$2y$10$lxZ7QDhIAQlWsEh0hGg2kea.V1BUBKiZt1Y47RwpslRh8RPX2bMk6', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:16', '2025-12-16 09:27:16'),
(799, 'stu_mohamedwarsame_fc9ec', 'stu_mohamedwarsame_fc9ec@example.som', '$2y$10$yW6zDDlydwbdNYgWzohZMeLDoaPNrxoYGfc8Oy.BFenZdMxqdikki', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:16', '2025-12-16 09:27:16'),
(800, 'stu_fartunadam_8e9e7', 'stu_fartunadam_8e9e7@example.som', '$2y$10$4RZgEokHtGi/YRcIDYFIeebX3JwrT90yH4anNvG4hfcNnJgj/ps2S', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:16', '2025-12-16 09:27:16'),
(801, 'stu_mohamedali_1732c', 'stu_mohamedali_1732c@example.som', '$2y$10$lmAVQoci4mlWjxjD6gLXJeTI8OmX5Vp9fmRCk3wVHzVdmFZF/sOsm', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:16', '2025-12-16 09:27:16'),
(802, 'stu_khadraali_f247c', 'stu_khadraali_f247c@example.som', '$2y$10$kIIeW2LBEn0bYyo1JxO3VupFDmnWl0BRQz6MORjBIvZTi6ZFHXpn.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:16', '2025-12-16 09:27:16'),
(803, 'stu_mohamedsheikh_eee83', 'stu_mohamedsheikh_eee83@example.som', '$2y$10$WR0x9bdCTgBT2PW2l4pWPu3YG73sYK730ESPbG4cinMg8KZRjJ7Jm', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:16', '2025-12-16 09:27:16'),
(804, 'stu_ismailabdulle_98db7', 'stu_ismailabdulle_98db7@example.som', '$2y$10$X/ZgNAlu39HaS8Wt46nO8ez8l841FgVE5S66y9eLCV4nV9Zca6oEC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(805, 'stu_ahmednur_80ab2', 'stu_ahmednur_80ab2@example.som', '$2y$10$9DFmdpbknxI/MxlmOL4Qde98JhcCX9x19uJvfGau/Gg5FJVhlzDFW', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(806, 'stu_ayaanali_a5c08', 'stu_ayaanali_a5c08@example.som', '$2y$10$vtk8ioon6by6rkunrx1FjOBbXAmg2IljYOM4yRPXXCe7/fwdb6a0q', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(807, 'stu_fartunali_5435a', 'stu_fartunali_5435a@example.som', '$2y$10$Hw7gtsMKokcieOoiRWR2X.lvUy5XT2oj8MFlYpbQYYOw.REu8QFMC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(808, 'stu_maryanadam_c29fc', 'stu_maryanadam_c29fc@example.som', '$2y$10$/M8avylCODdc.1ndIfddLe3CTXJUykLzcMsT.C99CoRV0lJgo4ewq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(809, 'stu_rahmaali_f3ce9', 'stu_rahmaali_f3ce9@example.som', '$2y$10$8wRWTZjyVumx6PYMNHF/xOxDfw6x1lWkDITPRHCbeBXY/ZaDJH7/u', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(810, 'stu_aliwarsame_d86a1', 'stu_aliwarsame_d86a1@example.som', '$2y$10$JK7iEhmPWeF/Z1K/e671I.YOOd.Kyyi15RJ7UiN6Cr546DU0Z8N3G', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(811, 'stu_ibrahimsaid_8755c', 'stu_ibrahimsaid_8755c@example.som', '$2y$10$UIJ5UjZt4RY1uv300hQace4FatdFDt7VjW1lXu04aOfc.iIS0cNPC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(812, 'stu_maryanfarah_1fdc3', 'stu_maryanfarah_1fdc3@example.som', '$2y$10$BcX6ntERImKclOcKs3Z9KeO/uJJ42An7xcoAodgbn5XDY0IYJF.jW', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(813, 'stu_jamanur_0a4c0', 'stu_jamanur_0a4c0@example.som', '$2y$10$jljmHoNTpE9zYhIEXFc/1ORI66lTlYsfvYjhGamHoSq8liUXyOvL2', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(814, 'stu_khadrafarah_651d7', 'stu_khadrafarah_651d7@example.som', '$2y$10$m6SWZQwC0gSMax.R4pb6jevyVsb5mi77WfIxi95S6rx57hYidQcii', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(815, 'stu_mukhtarismail_c65fe', 'stu_mukhtarismail_c65fe@example.som', '$2y$10$ogUDhXQbxd6T1dZhsGcnWeVOmhK2GNW8blLQzt.TJ1U7UKk1aERtK', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(816, 'stu_abdidirie_03ce9', 'stu_abdidirie_03ce9@example.som', '$2y$10$Q6Tj7.OwDbWia1Pgzo/5PuOV8BjGEw17lHt/5wur/L8gym2hyXt7C', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(817, 'stu_jamasheikh_5c685', 'stu_jamasheikh_5c685@example.som', '$2y$10$dCZk23ued4ouOKTwKm6wW.VbCDN9GQrNJ8Y1Zy6kG5u84iTCQjCTy', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(818, 'stu_ahmedwarsame_bef72', 'stu_ahmedwarsame_bef72@example.som', '$2y$10$IPDsqL7Ju9Rnh/FtiJGfxOya5ZyzbUa5Wj8WPTY3cMC4V39n8NdFy', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(819, 'stu_zamzamabdulle_bed39', 'stu_zamzamabdulle_bed39@example.som', '$2y$10$vR7yChm.AV7PWmYEpZx1euOqPVrxltIccRa5G3EZ3y5kDy5ujWf0K', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:17', '2025-12-16 09:27:17'),
(820, 'stu_rahmaabdulle_1d15c', 'stu_rahmaabdulle_1d15c@example.som', '$2y$10$y6x5tLs6liDuIcVN2uwuquRe.FpjURoCtsZyfigIxeG2OtOIgqKVa', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18'),
(821, 'stu_ilhanismail_07dbb', 'stu_ilhanismail_07dbb@example.som', '$2y$10$bV5MdSM2FZycrXKfcwsCIOxpLcIDGAWvOY1T6dOX2owJczg6QOmaK', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18'),
(822, 'stu_ashayusuf_eee86', 'stu_ashayusuf_eee86@example.som', '$2y$10$7icjqo.1blUCK57bKOp38.3XkTXiUI2k59WqqigpSgtCVG2E.tcmO', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `role_id`, `branch_id`, `is_active`, `is_verified`, `verification_token`, `reset_token`, `reset_token_expire`, `two_factor_secret`, `last_login`, `login_attempts`, `locked_until`, `created_at`, `updated_at`) VALUES
(823, 'stu_fartundirie_3bb1d', 'stu_fartundirie_3bb1d@example.som', '$2y$10$cV6WgjOtb5ivyAxQ.YRT2O33b74bVFi2w3kgg5fsUyx5Jae7QyK4.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18'),
(824, 'stu_yusufhassan_e5f1b', 'stu_yusufhassan_e5f1b@example.som', '$2y$10$NVtz20LA9yXCo3xu3egPRelnZNb1x5gaBsRUs04o8i5WtpQUqIZ.e', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18'),
(825, 'stu_mustafaroble_89436', 'stu_mustafaroble_89436@example.som', '$2y$10$8yWVeUrttKIfY3JAwmtwl.fclhVRbGA0yMIH4bqbg26Qm6rhAs6hy', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18'),
(826, 'stu_khadrajama_f95df', 'stu_khadrajama_f95df@example.som', '$2y$10$YyMFOXLyK/lbYVQ8tJE8hejrLBd4sdtmyUTwTWQ9dcqVEK/JsTPzq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18'),
(827, 'stu_ibrahimmuse_d8c39', 'stu_ibrahimmuse_d8c39@example.som', '$2y$10$DWl8z0ERd4ycdb1OWf/0z.1.xx3QcKb0gNIyoToqVO0gSDpImSlAK', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18'),
(828, 'stu_ilhanomar_714f3', 'stu_ilhanomar_714f3@example.som', '$2y$10$D6f/CXKHmqTGXFJ12UMpae9YSbi3u5vruYv7pW3S4roArTy4kWbt.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18'),
(829, 'stu_nimcohassan_5e9ec', 'stu_nimcohassan_5e9ec@example.som', '$2y$10$5H5rDZ7G7Cklj5yPkhDPD.fhtdVnIGE4mxcm92X34tkAlkvdLBQrm', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18'),
(830, 'stu_rahmayusuf_b95e6', 'stu_rahmayusuf_b95e6@example.som', '$2y$10$Vb0K6OOC2VIj4sYp7FGuJO9ihp5or07aSRuW8dLqBLfBZTC.ybMvy', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18'),
(831, 'stu_mohameddirie_01647', 'stu_mohameddirie_01647@example.som', '$2y$10$C/k4MSnbWRvao7ea5aMcE.jNADu8ujb07HYw2mB.xwaMdCmjTXIGi', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18'),
(832, 'stu_safiyamohamed_2d52d', 'stu_safiyamohamed_2d52d@example.som', '$2y$10$DFFAoJL5gGMChp9vxNjWbuswXdTdWZMEvue0YjVSJCLF0ucbCWGqa', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18'),
(833, 'stu_laylaguled_10acc', 'stu_laylaguled_10acc@example.som', '$2y$10$7W1LGf2YS5YpEjw2BE1yAOdr439r/QQsBexAcgxS9qnss0I3YWYee', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18'),
(834, 'stu_alinur_739c6', 'stu_alinur_739c6@example.som', '$2y$10$19xTZKUbNFuaWkJfTBr6ROhA.rK2JndGI9R4ROt8zcpcYPAqTupdq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:18', '2025-12-16 09:27:18'),
(835, 'stu_khadraabdulle_d819f', 'stu_khadraabdulle_d819f@example.som', '$2y$10$3cPZNsYmlCqTqvlkDsHNrO.dmcJxCAwuBN2WGQ/tW88uv9wDOucme', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(836, 'stu_ifrahismail_b6462', 'stu_ifrahismail_b6462@example.som', '$2y$10$CAEpdtVXpi3ky5QTf406EuqSWbnjT9Mj59Jn9qr3dIqX1taWBr3h2', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(837, 'stu_mukhtarabdullahi_95f4b', 'stu_mukhtarabdullahi_95f4b@example.som', '$2y$10$3EQYPULIX2WL5IVTGLuVF.0VEOjj7.J9T/kwSyIdC6XshDwFy.9ES', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(838, 'stu_aliabdulle_5ccb1', 'stu_aliabdulle_5ccb1@example.som', '$2y$10$Zm3XPy0lRQEwOX1r8KP9TuU6gmbcMYFhJZmgkIkWxIq3q7zGAN91u', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(839, 'stu_omarmohamed_03540', 'stu_omarmohamed_03540@example.som', '$2y$10$iXw2lY8mxyeNDpdr/CCgrupJnxwI9tVo1pmruL5J1KCD1JoE8FiSq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(840, 'stu_ashadirie_3bd62', 'stu_ashadirie_3bd62@example.som', '$2y$10$PA8Y43/XMG5PlgBy/7vddO//wbcNHKOydUXIByLMHUvcmZUUqsfYO', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(841, 'stu_abdifarah_17b59', 'stu_abdifarah_17b59@example.som', '$2y$10$EYRamh7qoIqtMNidMlHSHuizcFdJcg3Yzu18djU4nD2DkUuvWEQlG', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(842, 'stu_sagalmohamed_9bfdb', 'stu_sagalmohamed_9bfdb@example.som', '$2y$10$MLWtxcDk40d.v7klImz59OWrgOySdqQVNF6aC6OmHEJGmQADXGHzq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(843, 'stu_hassanmohamed_6c5ab', 'stu_hassanmohamed_6c5ab@example.som', '$2y$10$MWK67D3uHn8WLiWOs/MZV.JeYYt/DG2EIWgSn69GHWmyPGQyNygg.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(844, 'stu_fartunismail_22eae', 'stu_fartunismail_22eae@example.som', '$2y$10$tbNhGKZt5KWt264LKpwUKuAOmpnkjRqPbe8BIen1RAqRT3TjuS6XS', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(845, 'stu_sagalsaid_a915b', 'stu_sagalsaid_a915b@example.som', '$2y$10$a06j7FWldj5Efx4mxurJ9..X/mFhsVkpLwVNx0qF8pyu11tA1P.0i', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(846, 'stu_najmaabdulle_3cfdd', 'stu_najmaabdulle_3cfdd@example.som', '$2y$10$lrQ5jK286pQDAYGGqMM9VuZJXTGT.tcb4Fwk3gelU8PXp5fz3Dg7C', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(847, 'stu_mahadosman_91638', 'stu_mahadosman_91638@example.som', '$2y$10$.KKOlgy413FmjVowuo8vqeScRar5XRuwxl8ExV8PhDn0k7aAT.tV.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(848, 'stu_yusufabdulle_5c7d2', 'stu_yusufabdulle_5c7d2@example.som', '$2y$10$vsLBg9oj83LKGatjleBUH.zLdakZn8JZR7nYNwMlkVqFbVEys2y2y', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(849, 'stu_ahmedali_954d3', 'stu_ahmedali_954d3@example.som', '$2y$10$M/FirvWWjNexgGKS4UuKfOug8jQAf6xjuMLh.tPRrzi55uZ3i6psG', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:19', '2025-12-16 09:27:19'),
(850, 'stu_ashayusuf_76383', 'stu_ashayusuf_76383@example.som', '$2y$10$.6TgKbeI/Ao88d8sAIESFe5Rv9k6VgX18wnSKlnlOtO0yULs4U3D.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(851, 'stu_ayaanabdullahi_4e562', 'stu_ayaanabdullahi_4e562@example.som', '$2y$10$Ry7oIhstALsqA/l2ATPvuero1FFxMxsk5FkzbAOYp8PEYDZ/MxJDe', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(852, 'stu_fartunhassan_b1699', 'stu_fartunhassan_b1699@example.som', '$2y$10$YidaUwBzb33Wg2uvXRx0yuIurr5M79LUBvmK9/xD8RJC6iWRW4YBe', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(853, 'stu_mohamedhassan_e82a7', 'stu_mohamedhassan_e82a7@example.som', '$2y$10$BD8oQXMKoLDf8BhYSzhpJeD1MgmlPn0QhWnu9YetrZxY3T243gd8a', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(854, 'stu_ayaanmuse_33454', 'stu_ayaanmuse_33454@example.som', '$2y$10$F.Pz9le1SC.Qa4cOK7oi6es9ZLwo.tEoZONhIUpNE6OJE8Y7avtn.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(855, 'stu_ismailnur_f7ea3', 'stu_ismailnur_f7ea3@example.som', '$2y$10$Z9HOXDaXnEkOdW6.tafJyuSWXsgeSjPmXna35nJSosedtAhW/G7I2', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(856, 'stu_mukhtarroble_43b17', 'stu_mukhtarroble_43b17@example.som', '$2y$10$YGmPr1fOSFNlVtRuHUaZ1eq5UeOaxBAh29AtxNa.B8Tt70IYTSIxe', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(857, 'stu_sagalomar_0005e', 'stu_sagalomar_0005e@example.som', '$2y$10$U3a.r1QTxK30iZzfMt9TveEums3tnYQv/CC70T5jN5wCZyLOOsmJO', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(858, 'stu_ilhannur_209de', 'stu_ilhannur_209de@example.som', '$2y$10$NCX5Uk7imD1d7m2nw4tVvOhBi4Rs8TPSDyQvPu.vb5BGvUCe/DIEu', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(859, 'stu_fartunwarsame_40cb0', 'stu_fartunwarsame_40cb0@example.som', '$2y$10$098ffjut7gLf5.zReQv3tO3smc.oGS/s4nOVgmJOSY90GjVx43x9a', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(860, 'stu_laylasheikh_6e217', 'stu_laylasheikh_6e217@example.som', '$2y$10$6zT6r.90oghUgXRN660Ioui6aVws1EU1zxir1il6nG3BqBT.KRn3G', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(861, 'stu_aliyusuf_d18d5', 'stu_aliyusuf_d18d5@example.som', '$2y$10$EPCJBgj/gaG2lhHTxciJs.Bn0DOlM07KHcwuaxzwaXmlI14ZiIx4.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(862, 'stu_ilhanabdulle_5292f', 'stu_ilhanabdulle_5292f@example.som', '$2y$10$.lP.eeeEOQfV1lAuxGAlj.LLN2h3tW4cYgHQLS/Emund8rAPThKdK', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(863, 'stu_fartunwarsame_aeebe', 'stu_fartunwarsame_aeebe@example.som', '$2y$10$R.wq1M1AKzgnmhtoSY58hurrzBOk0Zzm2zKpHoDdD0iOCv0J6vtv2', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(864, 'stu_hassanfarah_2a197', 'stu_hassanfarah_2a197@example.som', '$2y$10$meXT8Wh8bNriVAKsNdVoiO73FjNkol.UM1HinpVV8P0LXua5KM/cC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:20', '2025-12-16 09:27:20'),
(865, 'stu_ibrahimmuse_7620c', 'stu_ibrahimmuse_7620c@example.som', '$2y$10$DoOiofv9gKQky8YfNSVkYuPZ1/JhUo/aRmPHpsTp/CakZ8wGkJU5S', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(866, 'stu_rahmasheikh_f2072', 'stu_rahmasheikh_f2072@example.som', '$2y$10$3OvQwJz94IOnB3eH521FZ.mHm3x/HCVS3UATCvwCDawnGuZu2xhlO', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(867, 'stu_mukhtarjama_f5dee', 'stu_mukhtarjama_f5dee@example.som', '$2y$10$lbbXp4iyVSRhViM6nsMzcuRf1SAQrBqlchVP2FhqBfkFHmclAtS/O', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(868, 'stu_aliwarsame_e3af8', 'stu_aliwarsame_e3af8@example.som', '$2y$10$wWnm/NMJt7QRSkXV.P17KebSy9VAQ5QhRXipeT/YCegxfckkCzbw.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(869, 'stu_sahraali_f1e4f', 'stu_sahraali_f1e4f@example.som', '$2y$10$f4S0AKJW79BDlYwvREau2eQqVncPaLmzOO.t6.h5LCHVnT7otm2wS', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(870, 'stu_omarismail_a367d', 'stu_omarismail_a367d@example.som', '$2y$10$OMytLIh/A8nSPOQlNIQy8.juE2ntyXSJtVEaYQ3Bw/n0lfvtI5laW', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(871, 'stu_sagalyusuf_3e326', 'stu_sagalyusuf_3e326@example.som', '$2y$10$nTA1t.w0eAnkZCdb7VEYpuI.7yHDpPx0UIN1JOm7Qin6J6OIqZm6u', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(872, 'stu_mukhtardirie_87b85', 'stu_mukhtardirie_87b85@example.som', '$2y$10$ov./L6TtDPPhxFPsWHMnuu4g7uQElzyQL0CyorqaafMbNGnk5Dibu', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(873, 'stu_najmaomar_67616', 'stu_najmaomar_67616@example.som', '$2y$10$lvPRryQZkaX8XzDgPmp8KOnGSG.KQpQh3jS8ANBQ2crwTnR5uTx6m', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(874, 'stu_safiyaadam_8041b', 'stu_safiyaadam_8041b@example.som', '$2y$10$c0pD9fPcoWuTDbAHdiInjutxBomA3Dog1bRe7uEdhyxCCtj9/bQDa', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(875, 'stu_yusufhassan_b45e4', 'stu_yusufhassan_b45e4@example.som', '$2y$10$eKhW907buDj8IGC5MCWDNei.X673tzW.RPXkNxT5NNDTXmfOwv4mq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(876, 'stu_omarabdullahi_12504', 'stu_omarabdullahi_12504@example.som', '$2y$10$kRjSN6B4YD4eLvgB5FkcruLplVX9/oMfEc43ai52C.ZZpZipoxFKq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(877, 'stu_maryanabdullahi_adc89', 'stu_maryanabdullahi_adc89@example.som', '$2y$10$KxJE/n.O2Joz9haxbWC5SePDcxDPKbKqDRTzMWtPnVIRetFyEUY9S', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(878, 'stu_laylaosman_de14d', 'stu_laylaosman_de14d@example.som', '$2y$10$gOGFZSCNhKXujmySOZwnbuhByITO4f2/WKNNJlwmWfuPDBfVWwt.q', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(879, 'stu_fartunwarsame_c6bc2', 'stu_fartunwarsame_c6bc2@example.som', '$2y$10$eCkiPoTUPUBmkQ110rk8BuMQrdY/QqVBtrnZUQG3tBrTuyug8DN8m', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:21', '2025-12-16 09:27:21'),
(880, 'stu_khadrasaid_97244', 'stu_khadrasaid_97244@example.som', '$2y$10$dBLkfnQdOFClzr7GOc44nObJ7NU7Nbk.vft2TfFyDT88JllgNZxEK', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(881, 'stu_ilhanroble_f06ab', 'stu_ilhanroble_f06ab@example.som', '$2y$10$V1G.Gw.0eonmm.jjaUbTO.d/.MhfWKwJjxKlR78S.VUHCw0FYusGK', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(882, 'stu_jamaomar_6da80', 'stu_jamaomar_6da80@example.som', '$2y$10$aeKOZW15nXoc70a8Ncu83euaIe6OVt2se8a77y01UawpfFppJrZhy', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(883, 'stu_ilhanjama_54f20', 'stu_ilhanjama_54f20@example.som', '$2y$10$Wm/yv/vn.TpdFFwj9R9va.m70UwyuAHKSMoZxHivMxNbkQHtCk4gq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(884, 'stu_rahmaabdulle_b81c5', 'stu_rahmaabdulle_b81c5@example.som', '$2y$10$R94w8V6ZNYrHtoaNAXiLf.JKTHsQW11zLQakUD5.YNxMfUJzlS4Zm', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(885, 'stu_farahguled_ce49e', 'stu_farahguled_ce49e@example.som', '$2y$10$MgkOhyWm7uf1XU2mUCcpqudBtf2m2XDUPyT7rPAyiG6zEaZdq0FE.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(886, 'stu_ibrahimdirie_ea866', 'stu_ibrahimdirie_ea866@example.som', '$2y$10$lEr534AMrpgDOJPHOtX5q.rBfEKbEzHM54/8S4rFW2TrdiHIq5Ama', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(887, 'stu_mustafaabdulle_7e0f2', 'stu_mustafaabdulle_7e0f2@example.som', '$2y$10$O0IQXlNi81nbwNYjUZV.0OSNCDzQbTQ4fwsRkwGNL9o86x12uMxry', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(888, 'stu_ibrahimsheikh_f644b', 'stu_ibrahimsheikh_f644b@example.som', '$2y$10$QRZsB4bbtoTHopENuhunquP7k2hcf9.Zx4OLlAEtxPdzRa1K1CU8u', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(889, 'stu_rahmafarah_4865f', 'stu_rahmafarah_4865f@example.som', '$2y$10$MKD/0c0ZwSM2nVDInQqQAOsH/ZmjwJ3Ync3fV3xqk1dzeYuSZ2qtO', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(890, 'stu_mohamedsheikh_f3346', 'stu_mohamedsheikh_f3346@example.som', '$2y$10$xd84V/WqM8GzydW.VjBBE.1nKbosi1vgAn3CwYZK2JXvk7oeh8qgS', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(891, 'stu_ismailmohamed_db829', 'stu_ismailmohamed_db829@example.som', '$2y$10$Dh9gKyW.NW2mZ58bUqyZluTS8bi0sSQIYp/25o.4z0dH2gnWTvDfO', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(892, 'stu_fartunali_cd8e6', 'stu_fartunali_cd8e6@example.som', '$2y$10$R1KSQJJVrcK6s9Irw2dvS.4nhLxFX6hOeK7MfVji0liL6klD6Wvge', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(893, 'stu_omaradam_00b9b', 'stu_omaradam_00b9b@example.som', '$2y$10$k7a/veiDXVUaAUbu8S4D2eSN7ZRzPOOg1lAla9bnShGNr/aiSJj.S', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(894, 'stu_mohamedadam_fb195', 'stu_mohamedadam_fb195@example.som', '$2y$10$kArcVbQJThiW75OgOMHbA.BK5CUea509CCJtp88mlBOfaUkxl/Zwe', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:22', '2025-12-16 09:27:22'),
(895, 'stu_hassanosman_34fa3', 'stu_hassanosman_34fa3@example.som', '$2y$10$mWbSGVmdgf08aQAspezjLOo8JczpxdE9v9ZZinRKhcq/j4L6.ghHe', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(896, 'stu_ahmedmohamed_b07be', 'stu_ahmedmohamed_b07be@example.som', '$2y$10$iguPrSIUS2Q9vVaQY2ZV5ea.47ojirEpqRmdY3OUPER.hc6sxtrQq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(897, 'stu_rahmasheikh_a7428', 'stu_rahmasheikh_a7428@example.som', '$2y$10$CDZ3EZKdjpCuORxN.JAQ.e1BJAfTtPci5CGAKK07WlLDeGML/L0Ie', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(898, 'stu_nimcoomar_5bfb8', 'stu_nimcoomar_5bfb8@example.som', '$2y$10$BdWRlWDvQ4t2QOO.KiSTE.1qQsJOMErJ98004XlRfHcRL/ndLPX0u', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(899, 'stu_rahmaabdullahi_d6f85', 'stu_rahmaabdullahi_d6f85@example.som', '$2y$10$gLUOPywgv49JH9MMbGdZWOaEwgoFXEbfJRFHJm4nxbf/hDIlZFOCC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(900, 'stu_sahraismail_03b49', 'stu_sahraismail_03b49@example.som', '$2y$10$ptBAtiRuBFUvlTWEUFVBV.Pk8B/qMl.DCVauTbWPocuUrjsgn4f4.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(901, 'stu_najmaabdullahi_49d80', 'stu_najmaabdullahi_49d80@example.som', '$2y$10$4KaUmfDBmpLSvU2RbLFbGe.Nvxh7GWH.dvmOVUWWYEVpJItnyHgMC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(902, 'stu_maryansaid_f83a1', 'stu_maryansaid_f83a1@example.som', '$2y$10$93O9vL2jWoeyaKmNvhMZAu358YsisEDWV2vgfh1T45boMGj0y3Fd6', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(903, 'stu_farahsaid_1887e', 'stu_farahsaid_1887e@example.som', '$2y$10$NGB.nZFF/kse8CTqPpWS5e2cIvwBhFbcqk3MZCh/.c/kcrRrVLtRK', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(904, 'stu_zamzamguled_107a7', 'stu_zamzamguled_107a7@example.som', '$2y$10$vO1EGPeiUGwVu1kFUwGZXO/m5ckpsuyZ0FnqYof5XUN8lRRSoULzy', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(905, 'stu_ayaanguled_e3df8', 'stu_ayaanguled_e3df8@example.som', '$2y$10$87TuXmGpSROblKZCwesxT.lir29jdkF.Ndo4QdwghIrXBeIE9dCRG', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(906, 'stu_safiyaali_9f0f5', 'stu_safiyaali_9f0f5@example.som', '$2y$10$S/JSsLvWVxc6hNKAbat9EO9/ya9/nL9FuhEIft9fFEfHKlyG50K1a', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(907, 'stu_ayaanosman_4c2a3', 'stu_ayaanosman_4c2a3@example.som', '$2y$10$.le1vEhxQnu81mywANWHSe4QgT16EqQR6csS6/C.JrxKNoIh4z8aG', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(908, 'stu_mustafamohamed_aa394', 'stu_mustafamohamed_aa394@example.som', '$2y$10$02dNc.SQgVln2yCUAmo7au1FNTL8fkQv8hWFarRJpeTtYhjZXNszm', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(909, 'stu_zamzammohamed_1f654', 'stu_zamzammohamed_1f654@example.som', '$2y$10$bzgRcIYEtyqjfoHO4awAX.XY5vLaHLgTdgYALa7mnW28SDT5GbofC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(910, 'stu_fartunabdulle_8dff8', 'stu_fartunabdulle_8dff8@example.som', '$2y$10$mbedg8yMpwihr1ZUkuekfubwNWLIaBbVc20BeUHylAsE3bSYwW8/e', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:23', '2025-12-16 09:27:23'),
(911, 'stu_fartunali_a6e2a', 'stu_fartunali_a6e2a@example.som', '$2y$10$.Vt.8T/xLDxl3P8WwIAxsus9LebOuj0nNPKPHlXoW50FGCp8ATOc6', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(912, 'stu_omarwarsame_3e5c8', 'stu_omarwarsame_3e5c8@example.som', '$2y$10$jUEDhP8ixsvqCZGk3xspSO8NaYdRi3kPjx0EqjKs4fvi.xpmfSmM6', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(913, 'stu_nimconur_dc25d', 'stu_nimconur_dc25d@example.som', '$2y$10$qcApwickgWzOJfiVvSsaCOWCewVb2swUVCwcwP7vqJfDK6NDMHjW2', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(914, 'stu_zamzammohamed_11b2f', 'stu_zamzammohamed_11b2f@example.som', '$2y$10$bh5JkDzWoftJt.A4OQvFzOwWnCK0FUF45sCEPHAbZUPwjGrbNRGtS', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(915, 'stu_rahmaroble_16a89', 'stu_rahmaroble_16a89@example.som', '$2y$10$.Svp2hyIYzUYCs2lHTrGRutVcP0qLm64jjCyJT8k5bDF.ErtoCPI6', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(916, 'stu_mohamedismail_ae0f8', 'stu_mohamedismail_ae0f8@example.som', '$2y$10$30vYgXPFrk5VrmEntTuUSeag9Sxa7AQRa0Mv0QG78Ur0k7fCk.RNq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(917, 'stu_ilhanfarah_29d31', 'stu_ilhanfarah_29d31@example.som', '$2y$10$Wvdu.h7T39MYkr71H8nG0eL/ypRQ5toplcOyhbgEkmk.oHdXdv4/O', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(918, 'stu_maryanhassan_47048', 'stu_maryanhassan_47048@example.som', '$2y$10$Yo1Q3.YRvpitk7y9bplcQuetPUpt0Ed6eYPx9e1F/20fuYGOQiAOW', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(919, 'stu_nimcojama_bbc72', 'stu_nimcojama_bbc72@example.som', '$2y$10$K2BHM3WLu4pdfTFqBDnREOkwlLDVvIq2BXn6bHCXOkQNhSiWZczZW', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(920, 'stu_ashafarah_5d212', 'stu_ashafarah_5d212@example.som', '$2y$10$NoQWDflCI6fAV0liSjo11ueFbTKrb.rQRG1Jv3cSSKpzIFaDQCw9W', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(921, 'stu_jamaomar_de620', 'stu_jamaomar_de620@example.som', '$2y$10$Ev7xCkNUtUy4FNkMIfdxx.96bfGyx4zICpKB1dAqAEm8iYK3zMyH2', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(922, 'stu_sagalismail_a8700', 'stu_sagalismail_a8700@example.som', '$2y$10$28Z4Ma/ZUcu122R9F0QCGeTVy91zIduiPUbMzQsTSm0btdgjp0qW6', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(923, 'stu_ismailguled_7fcba', 'stu_ismailguled_7fcba@example.som', '$2y$10$yG.cN0N6/rWe1vlw8EOk5upHplD6DbnHOheJxiY2QfYFcRU.cAxZG', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(924, 'stu_khadrasaid_41de9', 'stu_khadrasaid_41de9@example.som', '$2y$10$6mCpQ3rXrZd8EhkbpToC8uRDsz6Ihg9t.AqT9gq/PeGpuSljdNRha', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(925, 'stu_omarmohamed_60d86', 'stu_omarmohamed_60d86@example.som', '$2y$10$YFnEzXnCimnycCtTr1PJB.NftWO2wkWDzLRYxrddUk.cpZCLr6Uci', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:24', '2025-12-16 09:27:24'),
(926, 'stu_ilhansheikh_bdf9a', 'stu_ilhansheikh_bdf9a@example.som', '$2y$10$KYchpswfrd1m3AI5R58aeO.nFM99P1cl/lN0Npisb8vrkRAdtrEne', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(927, 'stu_fartunmuse_6e8af', 'stu_fartunmuse_6e8af@example.som', '$2y$10$c/q8Q/5LqDr5opqnj92k8OxcZUBRViOF2FEd3vGVWhfu3BWfpTLFS', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(928, 'stu_yusufosman_b10fa', 'stu_yusufosman_b10fa@example.som', '$2y$10$e5VPSfgJTx/LK2gn1.A9Q.Dle1y2VM/vXrCjQd.yaOk4ct.U9hVUi', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(929, 'stu_hodannur_8c650', 'stu_hodannur_8c650@example.som', '$2y$10$7KEkllVs68KaWfOIZ2RfqOdLucpxxwNkQveAwfyRMD0UmpvZENmZe', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(930, 'stu_sagalabdullahi_3d5e4', 'stu_sagalabdullahi_3d5e4@example.som', '$2y$10$xsIr.0XQtuUEqDpj3XMfGO12nWGZwNuadU.h4DO7QjGkw.t2xokDq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(931, 'stu_alifarah_7aa18', 'stu_alifarah_7aa18@example.som', '$2y$10$zs7PE6mhHusl8jWW6PI3wOpCQuKKvf9EylS7eRLFTEt5qT7y8vHc2', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(932, 'stu_ifrahsheikh_bcb34', 'stu_ifrahsheikh_bcb34@example.som', '$2y$10$a73/dl/Em6fLRLaJlrydHeTYZ35z/yUePbbdWe2M8hvorp34EJ9SW', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(933, 'stu_ismailguled_aa564', 'stu_ismailguled_aa564@example.som', '$2y$10$ocSkTYXpG3n/i/.XbPTQQeOEnC3FrlzV3yaKH.7rz7j856QRyyBWe', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(934, 'stu_ahmedadam_bcef3', 'stu_ahmedadam_bcef3@example.som', '$2y$10$RwEpGg5Ugi21noJxam.GPOOlmw2Td7r5iblT.OwFOksejPjQDDZo.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(935, 'stu_mustafasheikh_0c50b', 'stu_mustafasheikh_0c50b@example.som', '$2y$10$UhT0NMZe2tNKgJkPiz8qO.C0yAfpqAvUcoXGWAap3WlXVVhYPxIqq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(936, 'stu_ifrahnur_fa5a2', 'stu_ifrahnur_fa5a2@example.som', '$2y$10$hX1POCUv2XrYfj6PgNzGT.IcdcrNtgdmvQVBRdRKisw/Fv7lNAeZy', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(937, 'stu_ayaanjama_e3f6e', 'stu_ayaanjama_e3f6e@example.som', '$2y$10$1.S7.Pbe4rcsBqdVqpZUq.j9MQOj58h0wEPiq02Yi1Bjx//pmw9ZO', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(938, 'stu_ashaomar_14c78', 'stu_ashaomar_14c78@example.som', '$2y$10$lW06N9zU7vsL06K2t35O6Or8Nb8ydMVDfMv/Pr6NAz3Q6tNvTRj3O', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(939, 'stu_ayaanabdulle_1867e', 'stu_ayaanabdulle_1867e@example.som', '$2y$10$X2p.8Dlk7DUdPkhtn4mTyuWpJssVWeKNrIHyPUez26zsaYa7kql9e', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(940, 'stu_ismailabdulle_e79fc', 'stu_ismailabdulle_e79fc@example.som', '$2y$10$Tm5KoTGR9ANKfqbZJDpe1uUIQhsOIF0OZ4VmmQPcfpMVBqU/k8Q8q', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:25', '2025-12-16 09:27:25'),
(941, 'stu_mustafaguled_08438', 'stu_mustafaguled_08438@example.som', '$2y$10$UJhF6FvyX3OqdPcwqPRG5.cfAl9borYAGrYyxrMODoKo42kx9lrZ.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(942, 'stu_alimohamed_1cfc1', 'stu_alimohamed_1cfc1@example.som', '$2y$10$8w5.txY3l/xj24E4x6KINePVflkpLCgh2xuQ8E1070PLwXfY5ood.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(943, 'stu_ifrahjama_ada7f', 'stu_ifrahjama_ada7f@example.som', '$2y$10$hrXg26WHsyCgEm3c0meQn.k0nAS63C7dIdOhsES5wXE9v/x1sgjXC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(944, 'stu_ahmedabdullahi_bbea6', 'stu_ahmedabdullahi_bbea6@example.som', '$2y$10$vZkz.DReg3y8iLYOdjWFEu4WWMP4yZHu4V31b2DJTKhWA37SmL9Pa', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(945, 'stu_omaradam_bf40d', 'stu_omaradam_bf40d@example.som', '$2y$10$Bts1zo3.OiLiwU24wWHV1.L.PVVZ1iw8dpQReY8CKdr/SPdOBH5Ei', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(946, 'stu_ismailroble_4e318', 'stu_ismailroble_4e318@example.som', '$2y$10$RZpIoOVV8TDD09dAxf9oiO4n9oDhtwe3RzRHmHjczB0g0WydDDjXm', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(947, 'stu_laylaguled_ee8a7', 'stu_laylaguled_ee8a7@example.som', '$2y$10$/YxQNrPbFSw6QRMll0Wh.e/HFGqhErNqTcZlaF6snEeIbMQCEPu6e', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(948, 'stu_ilhandirie_6de38', 'stu_ilhandirie_6de38@example.som', '$2y$10$ECEgNW0mM3EGbHc5WAya8./JrDwBrdxaECmNnZK/Ot9104VYyUzYW', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(949, 'stu_rahmadirie_17bd6', 'stu_rahmadirie_17bd6@example.som', '$2y$10$Uhnu.v6cMaB12dcN3tBKKO4SkKCDITKOPT5H97fz3ZgQL70DdEcIG', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(950, 'stu_alisheikh_60b35', 'stu_alisheikh_60b35@example.som', '$2y$10$jHjl2Jg1fnIcXAkM0OcBCOa70lL9/tJpD9l.C3GDwTTVdFt8ppwFW', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(951, 'stu_safiyanur_61bd4', 'stu_safiyanur_61bd4@example.som', '$2y$10$04NEsrmkl2tIgf6ZC4EV9e5YQSCUX97MYljlAxipQZLBwUJXyNP5a', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(952, 'stu_ibrahimfarah_9a81d', 'stu_ibrahimfarah_9a81d@example.som', '$2y$10$pM0iHzgEJ1I9nNrP3CmsKeD7t7BVADtgs7dAtZ0E4zBChtLrpPYuG', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(953, 'stu_najmahassan_0af24', 'stu_najmahassan_0af24@example.som', '$2y$10$uOHYWGemKWCrX3jn5lsMAeCb1DieOtw7r425NbBNVdMbwJKHWDsKm', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(954, 'stu_sahrayusuf_04e98', 'stu_sahrayusuf_04e98@example.som', '$2y$10$2fx.lsHpfaw70bhGkHuKkuAMApsW3.hKy1fhq91WAcKJgY23oNRKG', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(955, 'stu_yusufabdulle_7913e', 'stu_yusufabdulle_7913e@example.som', '$2y$10$Dm0cakQgHhbZjeQRKflyxO7PeH6Cd8rYPu5eWcKeFRK5Xz5QOb52K', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(956, 'stu_khadraismail_e9a43', 'stu_khadraismail_e9a43@example.som', '$2y$10$KjQisrGR4uc3qW1RPhhyGOOW0j2ymenCHoWNu1ZgY0jkUpmlBt0Fi', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:26', '2025-12-16 09:27:26'),
(957, 'stu_mustafaroble_0328f', 'stu_mustafaroble_0328f@example.som', '$2y$10$8YW258NCzlAF9exBl2rba.hjWHw6jVX/rNK1ShN1ZUGMExttMQPVG', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(958, 'stu_ifrahguled_5b10d', 'stu_ifrahguled_5b10d@example.som', '$2y$10$MJSmY.IrTTVq6JvvFEjAm.iJbMXayOkQaYX8RHWoXn9tuGE1RrAJq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(959, 'stu_ashajama_a80a3', 'stu_ashajama_a80a3@example.som', '$2y$10$Mp0/nZzqm.oSk6U9jg8UYOYAl8WpMUVlP9VVT9Ei/9b00Yeyg4UIa', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(960, 'stu_ifrahmuse_f16ed', 'stu_ifrahmuse_f16ed@example.som', '$2y$10$O2CpFYx9RdcS8eBitg80oOze.MteFzGFM9uZ/rFTeUMQ8l.ictFGa', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(961, 'stu_mohamedjama_fb787', 'stu_mohamedjama_fb787@example.som', '$2y$10$2gbtlikYmZ4bJjLcnLW3QeA8wDFprJrUDs7xOS5ozWo7a/uIx5RQG', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(962, 'stu_mahadsheikh_b70e0', 'stu_mahadsheikh_b70e0@example.som', '$2y$10$Ax35kfQXIwJGmrvg1T/eKucvca4Jg2XfpR7TA2jqZyeMVQhuzucSu', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(963, 'stu_jamahassan_1f86f', 'stu_jamahassan_1f86f@example.som', '$2y$10$xVQSJxf4XPt03tGZphbk0.GINJekjnvvdPbXNi2NUnchjXHMCRI6.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(964, 'stu_najmadirie_9affc', 'stu_najmadirie_9affc@example.som', '$2y$10$2yvZtkyIMDRpmeT2VdTqzON3lS9rxCN9Qp9EiDeLVe7YkBMbQCxZa', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(965, 'stu_khadramohamed_a93bb', 'stu_khadramohamed_a93bb@example.som', '$2y$10$dEO6c6aD6Exw.bgMRts9hO/sTZOD4ALujyphL8dTDGxsvHEEnN5Ka', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(966, 'stu_mukhtarwarsame_87adb', 'stu_mukhtarwarsame_87adb@example.som', '$2y$10$8uC4bnzMcA3I202/KrIFq.tqNWDzBZJtgf/7bsQJa7d2fugYGjBHW', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(967, 'stu_zamzamhassan_eaaad', 'stu_zamzamhassan_eaaad@example.som', '$2y$10$8gj6iDV1XZDHY9GCi1wrkOiUmVbmlvxJ5ccCmunzSm2M.dwaInreq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(968, 'stu_mustafaosman_ca7e1', 'stu_mustafaosman_ca7e1@example.som', '$2y$10$RZ0Zt6U.cQ7TtPgTQKd/fuVP3q1vM4i.C63dPTmTLTFMo9W3GjmBa', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(969, 'stu_mahadismail_98ff4', 'stu_mahadismail_98ff4@example.som', '$2y$10$Iv.PA0UltFKYMX5qc0pn4.5JJ8ljqNak0SwGMaKo7OEXhz7sn4stW', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(970, 'stu_fartunnur_16a6c', 'stu_fartunnur_16a6c@example.som', '$2y$10$RSAHHxgJKgW232oQ2.sEjOk7E2EddIYx/TWxafNLarbGeECqEwU/O', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(971, 'stu_fartunroble_7aefc', 'stu_fartunroble_7aefc@example.som', '$2y$10$j0xAk5H.AwycCB.wUxqLJ.i3SMlAppirbr4U/5qulS962cRvhjcte', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(972, 'stu_rahmamuse_3692e', 'stu_rahmamuse_3692e@example.som', '$2y$10$UWc4NA4z/cJL/bK6ekCx8OMqW01NaYnN.jcjtFp/sTBl/S8c8/N7u', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:27', '2025-12-16 09:27:27'),
(973, 'stu_khadraabdullahi_ba72a', 'stu_khadraabdullahi_ba72a@example.som', '$2y$10$euyUo/q8oRbyvDBwSf9R5Ovg8pvl8OcLdSV7fw7WaJ3TX.TYaNJWK', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(974, 'stu_yusufmohamed_df5c6', 'stu_yusufmohamed_df5c6@example.som', '$2y$10$oG399mDEGmbtCtZO98xLguzIeYaqkWwMPL2208/KWt95GFrn2t4sK', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(975, 'stu_safiyaabdulle_3dc2d', 'stu_safiyaabdulle_3dc2d@example.som', '$2y$10$rDbJrExK7Ggpd29MKEF4w.nVYC5scHJGPG9Sc.IaNvge/zEAxXN/q', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(976, 'stu_maryanwarsame_a260c', 'stu_maryanwarsame_a260c@example.som', '$2y$10$wAZcvz3QCJqVqCzcc0XyoeN8hjfl3Bn2MwMGM5y7OryMlbhs8CDn6', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(977, 'stu_mahadroble_6efa6', 'stu_mahadroble_6efa6@example.som', '$2y$10$3ju8Pce0cFiFhjxLsg0WG.NewEsv4fsxY.XSXpOLYikfb.NKugLbu', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(978, 'stu_ibrahimfarah_ef196', 'stu_ibrahimfarah_ef196@example.som', '$2y$10$vJKN68JGcFAfRfylVQV3MuxvRc80UzjhYoGY5osJK0XfBw3smWlsy', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(979, 'stu_ashahassan_29554', 'stu_ashahassan_29554@example.som', '$2y$10$H4sCxqAtwNkXVb3w6jgIFOEv4q00psKMUKt2QnoV4N/0t4/cHIP4a', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(980, 'stu_mahadwarsame_99779', 'stu_mahadwarsame_99779@example.som', '$2y$10$Act4oYtx/pbF6Mj4tkw78.eC4L67VAjOplfXhoGW2WFimRI7NGJPu', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(981, 'stu_zamzamguled_841ee', 'stu_zamzamguled_841ee@example.som', '$2y$10$/iBY0Qe.1zCPESbtSFFD/emSSEhFZyMNN7I/7xvw7/8.dAPWy9fHm', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(982, 'stu_mahaddirie_cf796', 'stu_mahaddirie_cf796@example.som', '$2y$10$JqszTjBGvdNgFzbqeI4.7O1ie1NF2.Nk81Q2i5E0vlwAYd6hLGgh.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(983, 'stu_hassanabdullahi_df809', 'stu_hassanabdullahi_df809@example.som', '$2y$10$mcnlBtAulgJlyiQC/gTFWOE9oXiaTumXRun7D2M.957Fk2ggw5Afu', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(984, 'stu_maryansheikh_4d05c', 'stu_maryansheikh_4d05c@example.som', '$2y$10$LyYanPfSSwgR3g175XZwyeqj4uFErYdh8u7vtNNlWxl8dlcdgJokq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(985, 'stu_omarmuse_18c8b', 'stu_omarmuse_18c8b@example.som', '$2y$10$ISYAFz/mEGV5lf/avkrJiejOM1QbwNc6LdH5Eiq5eSJU8AG6TDP.W', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(986, 'stu_fartunwarsame_b244d', 'stu_fartunwarsame_b244d@example.som', '$2y$10$zIU//V/YAOaLO3C8WEggO.aShbFvlpgmqJwKxQ2Mj7m0JcPsPt8fC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(987, 'stu_mohamedwarsame_4db8b', 'stu_mohamedwarsame_4db8b@example.som', '$2y$10$IVaVDjB7to35aw4TS6x4iuSrHvxd4..mSQ.ORF6j3gQsdVkdn1kay', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:28', '2025-12-16 09:27:28'),
(988, 'stu_ifrahali_ae0fd', 'stu_ifrahali_ae0fd@example.som', '$2y$10$3.YsTxhXng/tcdj5gIAQc.pUNcRMIJX1hhKhHGBZ7Q226UxCa6J0C', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(989, 'stu_hodandirie_eeeb3', 'stu_hodandirie_eeeb3@example.som', '$2y$10$Gl2DT89OaTTyX5m42xkLBu83ivchsNt1wigQ4rvJmqhp5T3GsIrr2', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(990, 'stu_najmaadam_548b9', 'stu_najmaadam_548b9@example.som', '$2y$10$YznpXENbTNB6/up46zAkke70hRpqVGY4Oat/Tql2pxxo91qlSvB/a', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(991, 'stu_abdiguled_4bb53', 'stu_abdiguled_4bb53@example.som', '$2y$10$3I8Jzt6a4ieNOsIphemQ3eTTkl4f3ZEZ6UHf1Isl8cVJvbIhGV9lK', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(992, 'stu_ismailnur_03865', 'stu_ismailnur_03865@example.som', '$2y$10$cigoRkFRqBiQ771/kYpN6OUeMHjI/l80dsPbPWeVdpzkP8iUMW4Vy', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(993, 'stu_zamzamomar_49b0c', 'stu_zamzamomar_49b0c@example.som', '$2y$10$5m8LyOTX6mpYcROgmK4x7eGoeR0jI1kjhMg10oy/vEGIzztaHyekm', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(994, 'stu_mahadsheikh_4d9d0', 'stu_mahadsheikh_4d9d0@example.som', '$2y$10$atjSrQk8hgYyTjT3gD93jekGQTG6JCkci/KUO6ePyLzSR59fdRaHG', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(995, 'stu_fartunismail_52fd7', 'stu_fartunismail_52fd7@example.som', '$2y$10$r2uAX6Su6M30Zs4buQaxCu1m9wyjLg7cDS./0fKRZqCCnAMJF8Lye', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(996, 'stu_zamzamfarah_eb12d', 'stu_zamzamfarah_eb12d@example.som', '$2y$10$FnzQE760RRx1vFKszcfWZOehYbmp3lxqTrrHfQLR4K7cv0l9qQs6q', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(997, 'stu_ashafarah_b786a', 'stu_ashafarah_b786a@example.som', '$2y$10$Wx2/RDOBGUe9wphxp9rbYuobD2EsBCaQr7W2qH6DBm.eTC7RpJLmG', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(998, 'stu_alinur_56e34', 'stu_alinur_56e34@example.som', '$2y$10$2BQpikYDpUEQd2n9fyy32.GGbSF5Iv9TIjbCzS9ylZeGpK64H3IA6', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(999, 'stu_mukhtarabdullahi_7d069', 'stu_mukhtarabdullahi_7d069@example.som', '$2y$10$4DR9HNRptfjma3mJcT.frO7Us1yaV1fA1yLNZ19yfn1K.LR/nwW5i', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(1000, 'stu_sagalsaid_11eca', 'stu_sagalsaid_11eca@example.som', '$2y$10$LRQtWdcVl0I8yY6hWovT0.24Rg4EdFpchR0QD.31RCTXVFFju8r4C', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(1001, 'stu_hassanabdulle_40354', 'stu_hassanabdulle_40354@example.som', '$2y$10$dILFukefqchNXnaSAmDTSes/4XIq5esX7rasK/P1m7xGzUIaZe9oS', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(1002, 'stu_alihassan_193a0', 'stu_alihassan_193a0@example.som', '$2y$10$iN8f/mGKJc85zm0KYkReweFXxsnuKEFLC6M5tvFQbHPDuwwurQc.2', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(1003, 'stu_sagalomar_64502', 'stu_sagalomar_64502@example.som', '$2y$10$SEzu4Gk8LTnoCQvr4SwKVeiUmpkd3IC8JQoFjWFJn/Gpl1RY0RjOi', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(1004, 'stu_nimcoosman_d0a04', 'stu_nimcoosman_d0a04@example.som', '$2y$10$0Hlgu21OYiOpFlDFxsYRC.G9wXfS1Mb9IP44.B7Vx6taNMeq1Slru', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:29', '2025-12-16 09:27:29'),
(1005, 'stu_nimcoyusuf_e1778', 'stu_nimcoyusuf_e1778@example.som', '$2y$10$k9mpif6kgrUJGGKDxNWI0Oa4ZlDi3lihRL6MVILypGqtd3jDVtpQi', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1006, 'stu_najmadirie_98258', 'stu_najmadirie_98258@example.som', '$2y$10$SHnN2e095XMPNFqTbtKQ7..9y9cOGRF/f7nAWD5ZrfDlq/EHU2vzu', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1007, 'stu_ilhanomar_3befa', 'stu_ilhanomar_3befa@example.som', '$2y$10$HCJIDCRgAerOnV/YeRtPx.LNCqFVWW8jt2TLayKlsLrjMYlMdbAqu', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1008, 'stu_najmanur_06b52', 'stu_najmanur_06b52@example.som', '$2y$10$0/MPZzGrrLS0YQGWv6M8/OTMwxN9UNtRUF9J2AitITGjriWjxXKuq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1009, 'stu_hodanjama_7af6c', 'stu_hodanjama_7af6c@example.som', '$2y$10$66PcbtF1a3PwyIlGWzfX4eSCNF6cFr/JcHFVvH3wWu9CYI4CTxFNa', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1010, 'stu_abdiismail_2c7fd', 'stu_abdiismail_2c7fd@example.som', '$2y$10$keLOlC8IPvvvcHJO3KU5V.CfvNx7UG48jjmzzv3G5ml3KrI7QuM6u', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1011, 'stu_hassannur_dad7c', 'stu_hassannur_dad7c@example.som', '$2y$10$bt7bHTe2oKmBu6cLu8Bhh.djU1.tRpYMLJhEI38Z6wRw2sJZaereS', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1012, 'stu_khadraadam_0f634', 'stu_khadraadam_0f634@example.som', '$2y$10$kNQbKMtLBPqKKjtS.BsACu6z05eHrh1lJDKLcz1tulywXeCPedXGq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1013, 'stu_sagalsaid_00362', 'stu_sagalsaid_00362@example.som', '$2y$10$n/Wx8Ni2Wv6mYYSupodMf.zLBI5qilu.ENQzg2bX.sLiki4EZfDmi', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1014, 'stu_ahmedismail_90aa5', 'stu_ahmedismail_90aa5@example.som', '$2y$10$uEsOj2xN72Q9zmLqN8/XcegZPkkmVUENYdW2vhiH83Y/iGmtUua2W', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1015, 'stu_ismailnur_96bb3', 'stu_ismailnur_96bb3@example.som', '$2y$10$pjTESZnEpzha6BpQ/R4xIOYQOAP2MkCE0ZrMRzefeDeJVcn9wSsB6', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1016, 'stu_ayaanroble_9033c', 'stu_ayaanroble_9033c@example.som', '$2y$10$lW2pNJL/RxSYWDvpEI5Nw.4ess7Iylfl6hgzg5rpfPrui.UQlFrC6', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1017, 'stu_nimcoomar_4ca45', 'stu_nimcoomar_4ca45@example.som', '$2y$10$TtGluPPLj6PpWtyfcFphbOFrZGBXK0i.diGh00K9nEF/5Lll2Clt6', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1018, 'stu_ahmedwarsame_80f7d', 'stu_ahmedwarsame_80f7d@example.som', '$2y$10$I1cGYbv6QVGynrXXO4XfseM5ciVad0Xnfv58AS5H6G7sYGJVnVohy', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1019, 'stu_hassanosman_cec07', 'stu_hassanosman_cec07@example.som', '$2y$10$PC9fpoU6Y4Wowh8K5NCnhO4sS.gaJ1kk5ey7GyBi14bBgijQQka0W', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:30', '2025-12-16 09:27:30'),
(1020, 'stu_mohamedosman_7e1cb', 'stu_mohamedosman_7e1cb@example.som', '$2y$10$nz4JVNX8LuyI39rVmRGiYuxKHXSV2OJd/0Gqp8ysQx27kZsFo5FB6', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1021, 'stu_maryanhassan_1c77f', 'stu_maryanhassan_1c77f@example.som', '$2y$10$BIVrZVkGl73cYluaNC55/OJM9ZNRKgvNgVf5EhnnsWilHjkbFn5DK', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1022, 'stu_ismailomar_aceed', 'stu_ismailomar_aceed@example.som', '$2y$10$mpvJuqerU5ZJsB1eZddmuOqYUWQWCqk/UF1/7ChNeXj6JGUcEpFU6', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1023, 'stu_jamafarah_a8678', 'stu_jamafarah_a8678@example.som', '$2y$10$xV0i6EJU3A4A0aL8T7wPNuFS1wnMaFMaIYoPw6q7YAwQSXcvYvCCS', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1024, 'stu_ilhanabdulle_afb00', 'stu_ilhanabdulle_afb00@example.som', '$2y$10$ECEcFjxJ/qo5gJo37MUN9OSPj5apLTsYAKq8HRhR9sWsSjzBtJMOu', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1025, 'stu_mahadosman_e0f46', 'stu_mahadosman_e0f46@example.som', '$2y$10$tqBx0UKekQUyhHn6sNp.cO2xlKacZf58M.D9u/aUNwwj/kr6oclEm', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1026, 'stu_sahrayusuf_795db', 'stu_sahrayusuf_795db@example.som', '$2y$10$T1X63APYlD9sr47rOBc8vO/vzwplIxwLroWFcSO3JX3aM4/u/kF6.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1027, 'stu_farahsaid_3726a', 'stu_farahsaid_3726a@example.som', '$2y$10$YJTBAJj2StX.MhlhdTtWuO8aahlG0ZzRzBuTggd9EAzA24zOMhXqa', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1028, 'stu_nimcosheikh_6b3f0', 'stu_nimcosheikh_6b3f0@example.som', '$2y$10$TO2iIK.gE/k4O3vOek13Ke1z743Hvtyrslpa3fy/.SxuQW08L0tMi', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1029, 'stu_najmahassan_f7817', 'stu_najmahassan_f7817@example.som', '$2y$10$VSd7RJPjK/XdYygzL1jZ1OyEqyU/pRxZMNjYya3iVgmGMoWlvr2ia', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1030, 'stu_mahadadam_c1e32', 'stu_mahadadam_c1e32@example.som', '$2y$10$B7qNbVVazE0av750ay5IE.6DXB2eOJ4Dz1CnTQj40PMJ1fGowoA6.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1031, 'stu_sahrafarah_29d2a', 'stu_sahrafarah_29d2a@example.som', '$2y$10$t2ud3gn1XJVYc/LgTRj8betfXo4Zd3WL/uIc7MjqdL8neTuTzkIwC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1032, 'stu_ayaanroble_b2118', 'stu_ayaanroble_b2118@example.som', '$2y$10$8d/V0ihN46SxK3E78KhrHuvPN06UcrIqOOy4HwvGUY6vJqVXpLbZa', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1033, 'stu_sagaldirie_89465', 'stu_sagaldirie_89465@example.som', '$2y$10$TM/gGpznGNXw6jTyMbW5MOz4A.T..rYDsqqf9diD703/wO3i7tbCm', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1034, 'stu_jamaroble_65d0c', 'stu_jamaroble_65d0c@example.som', '$2y$10$/OSuPxEmI0rW337wSHsdnuKZdUVsPoWeVSYP5OOoUGFwY/J/zCVdq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:31', '2025-12-16 09:27:31'),
(1035, 'stu_rahmamohamed_6d5dd', 'stu_rahmamohamed_6d5dd@example.som', '$2y$10$VVZMaIAT/w.xhrTmv21s8OFks9FiCD0rP6l4Wp0YKvo9oHIRgVD3K', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32'),
(1036, 'stu_aliosman_4573e', 'stu_aliosman_4573e@example.som', '$2y$10$u89GHYwy0FDCSYajOIBnVey.aNcgBb03xn/2p6jOckOmTm.jOkZ7K', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32'),
(1037, 'stu_mukhtarsaid_890c0', 'stu_mukhtarsaid_890c0@example.som', '$2y$10$jYRciVFqKdQna2afBC5qiOZP93j1sN/bl.c9AfE365.nI001Sjbzm', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32'),
(1038, 'stu_nimcoabdullahi_50384', 'stu_nimcoabdullahi_50384@example.som', '$2y$10$cAkMAwJvKG2tri3Hn5Qi6.trK.fLFcMqxHbmG0NdNH5xh5lz.7bXy', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32'),
(1039, 'stu_ahmedfarah_ae687', 'stu_ahmedfarah_ae687@example.som', '$2y$10$fINPbDU1GHUMVPZIFP2lC.Z9BjMCRLgTtfAkPu3wWcYEC5deNAAPO', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32'),
(1040, 'stu_mukhtarroble_b75c5', 'stu_mukhtarroble_b75c5@example.som', '$2y$10$qmC6PGkTP1OKjQ8bOH97Y.PaH1GVbmfXRrLPOGJmDK3ODbJfHPTtG', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `role_id`, `branch_id`, `is_active`, `is_verified`, `verification_token`, `reset_token`, `reset_token_expire`, `two_factor_secret`, `last_login`, `login_attempts`, `locked_until`, `created_at`, `updated_at`) VALUES
(1041, 'stu_ayaansaid_d1758', 'stu_ayaansaid_d1758@example.som', '$2y$10$xcJb7zu.cCEH9x36Wlrtt.sXrxYG9HCBvUFuPtpi2/bHaNCge.M.W', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32'),
(1042, 'stu_ayaandirie_0db76', 'stu_ayaandirie_0db76@example.som', '$2y$10$D199aHqA4SBSdHPPMJ4C/OZ2yd.AvJLa.Z7.OxkzUOPdsUKuFWpj.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32'),
(1043, 'stu_mahadmohamed_2ecd5', 'stu_mahadmohamed_2ecd5@example.som', '$2y$10$ngWBVi4lAEUn86BBpSjwWe27.6T7jkUbGcTAARtVzZYevA.AN.Roi', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32'),
(1044, 'stu_zamzamhassan_aee75', 'stu_zamzamhassan_aee75@example.som', '$2y$10$.LTq.bQbAVESOWBLtPuosOz7Gw4rbqnSBO427XMQDrxMfxM3JM4PC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32'),
(1045, 'stu_ilhanroble_23196', 'stu_ilhanroble_23196@example.som', '$2y$10$End0BhrCb4Q8SNLd/fuNPO6DeE/y81Rcjp.hHZJrzLokrbDpwfeoK', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32'),
(1046, 'stu_ayaansaid_72af9', 'stu_ayaansaid_72af9@example.som', '$2y$10$wHUqA4NNAzOzt3pEpADJeecSQkZsF15xEbELZ8tqLrLRlHqqwQSum', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32'),
(1047, 'stu_ayaanali_c2e61', 'stu_ayaanali_c2e61@example.som', '$2y$10$Qu4Bq6BiAVMv4EvKuSjz..XUJrEhvf5YQInaUEAT4UB0HT7SRuRZO', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32'),
(1048, 'stu_maryandirie_7e9ca', 'stu_maryandirie_7e9ca@example.som', '$2y$10$VaT.E5s3OA6afy3B5Qg9qeXG8zGczCq0OJPsdl51pa1tmBnBioBCq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32'),
(1049, 'stu_fartunmuse_31ebd', 'stu_fartunmuse_31ebd@example.som', '$2y$10$XOgUBRkDYSKRtU1YGIpa6.C3ujT38v.p5rmO7jRnUjQikTVlnYQOi', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:32', '2025-12-16 09:27:32'),
(1050, 'stu_najmamuse_bed99', 'stu_najmamuse_bed99@example.som', '$2y$10$UluRZLnm.hPmlpAwWNgx2etf6a55pyRnsT4Q.5gZjEVE4s.cglMqC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1051, 'stu_laylaabdulle_487e5', 'stu_laylaabdulle_487e5@example.som', '$2y$10$JmWbjq9O2HvDyKnt9pNHeOHhE/mKNheZSY8AqWnr8sq3yz0x9/EuS', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1052, 'stu_ibrahimmohamed_2700d', 'stu_ibrahimmohamed_2700d@example.som', '$2y$10$o.icHTGQIgCKgBQ5p2i32O/6rJFuk1syFAY8hddZLzuMUFU3QhEd6', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1053, 'stu_ayaanabdullahi_b5e7f', 'stu_ayaanabdullahi_b5e7f@example.som', '$2y$10$n0cdbGkqGXqRsHBOK8CTcepAtDQ.lARVlScS8pjQINnR6Rdvdm93a', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1054, 'stu_ismailfarah_58916', 'stu_ismailfarah_58916@example.som', '$2y$10$rGyPtAdPZRo2.JDpJu3X4Ojyb3.CLAtNTGG9nWupSl/Y.HvshYht6', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1055, 'stu_nimcosaid_b8708', 'stu_nimcosaid_b8708@example.som', '$2y$10$DPCl0R8NUxQXRw3aOLJSB.j3cmZZ5676PvjK1OWk.5s5Vmm6NMVyS', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1056, 'stu_ibrahimfarah_90542', 'stu_ibrahimfarah_90542@example.som', '$2y$10$MQZ1MJyRfTwWM/WQHtD6Z.JlL.vn5jtMXrlkryuhM8VK4HxxIoqbi', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1057, 'stu_mukhtardirie_3a7c0', 'stu_mukhtardirie_3a7c0@example.som', '$2y$10$BO69BoD.AtcD8cmKM5G.KuOOvJRyvqDELK3DYMdKADr8ImLsl59nu', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1058, 'stu_najmaosman_63765', 'stu_najmaosman_63765@example.som', '$2y$10$j4GaVmYf.xWolEaW/fS1tOamjgw18tHFQw1fsrCv8pRM53qvhQXmG', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1059, 'stu_safiyamohamed_d9d5e', 'stu_safiyamohamed_d9d5e@example.som', '$2y$10$U7t/6aNkd2sT/qTr2iDQseDpdj0UsCEuepXAYf58NvKHQNX45fg9.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1060, 'stu_mohamedosman_175b3', 'stu_mohamedosman_175b3@example.som', '$2y$10$iMC82wirkcXupSKZ9CleW.8wlMsaaU0zl6Gl.9HRmRG1kKBuX1iuK', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1061, 'stu_rahmaali_77d34', 'stu_rahmaali_77d34@example.som', '$2y$10$8guN8EUENoFWc18vw2UIhuQdb81jRbFvOP4IqGDDoDaQa3OXIwANG', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1062, 'stu_mohamedsaid_85d36', 'stu_mohamedsaid_85d36@example.som', '$2y$10$TbZv2rKI7DSOQZhClilFLeqGBd53j5/IhpEbdpIVd1EeeiKIfsq6a', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1063, 'stu_safiyawarsame_25d33', 'stu_safiyawarsame_25d33@example.som', '$2y$10$2x6m1YwoorRk/005vn8OgudYc5XHC5B7r8h0Ncr5z9jzJXnVGw.vW', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1064, 'stu_sahraabdulle_6a587', 'stu_sahraabdulle_6a587@example.som', '$2y$10$54J73s9w/c82LpqBq1ciXux0Vw1bPwwNnX/8nUZ4Yo1EvbPODsYfG', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1065, 'stu_hodanjama_dfa3a', 'stu_hodanjama_dfa3a@example.som', '$2y$10$qZgFPoksGCJgLz2IRYxKzO9xnxyQIN2qLQvt3mumfr7OXcY0jDlo2', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:33', '2025-12-16 09:27:33'),
(1066, 'stu_rahmaguled_343db', 'stu_rahmaguled_343db@example.som', '$2y$10$J4GJqhJVvqRwxEoB4CRexeIYLxwFgztRJ.oLOTl6vmJM5PeDzm2d.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:34', '2025-12-16 09:27:34'),
(1067, 'stu_ahmedyusuf_c814c', 'stu_ahmedyusuf_c814c@example.som', '$2y$10$YlDE6O5GxsCY/FbsBRSiVO5ZEqBKUN3fsKNw3Q2y3t0WbT4R0IbT2', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:34', '2025-12-16 09:27:34'),
(1068, 'stu_ayaanali_41691', 'stu_ayaanali_41691@example.som', '$2y$10$7LT/FNa0G3pOSDMYudNyTONhL7kbuYCrOhUQK2lnVtIkZ9qFooP7e', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:34', '2025-12-16 09:27:34'),
(1069, 'stu_rahmaabdullahi_50702', 'stu_rahmaabdullahi_50702@example.som', '$2y$10$e6yu/6pyGq7yb7dNESr6KO2QixHghh.VRcon3I66pKMXPkmHs3CoS', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:34', '2025-12-16 09:27:34'),
(1070, 'stu_sagalwarsame_8533e', 'stu_sagalwarsame_8533e@example.som', '$2y$10$G55EgeVVmA1/cAei7aR0nelx5DWUg4nj4wlZaC4vUxN9HytewrDAi', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:34', '2025-12-16 09:27:34'),
(1071, 'stu_fartunnur_9ed40', 'stu_fartunnur_9ed40@example.som', '$2y$10$Na3km4Pi81Yqu.Q6LvhsE.WuvtQIEEtL5G0HszUpdS7p8FUZ.tKVW', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:34', '2025-12-16 09:27:34'),
(1072, 'stu_ashasaid_5b17f', 'stu_ashasaid_5b17f@example.som', '$2y$10$TcLv728zMfj2GxUfSvorKeu3kSrYKw7Nks7hT34U0GHG9PQh9coka', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:34', '2025-12-16 09:27:34'),
(1073, 'stu_laylaabdullahi_034ad', 'stu_laylaabdullahi_034ad@example.som', '$2y$10$Zantiv2yFn9BmxridkWytOAqs4/gTo/5m0qlvCWcG99DtO4HWJ//S', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:34', '2025-12-16 09:27:34'),
(1074, 'stu_laylaabdullahi_05bbf', 'stu_laylaabdullahi_05bbf@example.som', '$2y$10$mo26Bt8yKFkKNTdHSIieFOETeiZ.Uvvf9QbFNcAgVhXIj65ve0xvq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:34', '2025-12-16 09:27:34'),
(1075, 'stu_zamzamguled_0f80c', 'stu_zamzamguled_0f80c@example.som', '$2y$10$DghbkOPKfutA02kC89/xV.8GDJteYRVlzlXxK8xpRieMwznOrpkum', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:34', '2025-12-16 09:27:34'),
(1076, 'stu_yusufmohamed_1567b', 'stu_yusufmohamed_1567b@example.som', '$2y$10$jGhjibjJJUCaqCQbEInzY.pOjYlTRYGoLIiLqb4RT48psS5vezyJa', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:34', '2025-12-16 09:27:34'),
(1077, 'stu_farahali_5c0ff', 'stu_farahali_5c0ff@example.som', '$2y$10$.ZlAC7sZjXiPEd8n33g/nOZ/sZvjjP3oMJt81/s1ZIwvNwNseVSqq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:34', '2025-12-16 09:27:34'),
(1078, 'stu_hassanhassan_ea3a5', 'stu_hassanhassan_ea3a5@example.som', '$2y$10$HJy3xjR6yK6F336bGFhGIOhWom/h9y4My.1MKmjZcw9iveTMytCYu', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:34', '2025-12-16 09:27:34'),
(1079, 'stu_sagalomar_9a46b', 'stu_sagalomar_9a46b@example.som', '$2y$10$Vnfokjr9KBpeRVW4L7cJp.j80raYUKfh4UzHZfRcBYcaLRPg4np9u', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:34', '2025-12-16 09:27:34'),
(1080, 'stu_safiyahassan_e77ba', 'stu_safiyahassan_e77ba@example.som', '$2y$10$GHSEELe6o77UOC/uGlo9c.hLSnQIaLoLVGcBpW7dKfF.WhA.iNM96', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1081, 'stu_omarmohamed_9036f', 'stu_omarmohamed_9036f@example.som', '$2y$10$8e3rzTgr4nQrtKKOf202K.EPJeShoJm5RV42uN8Po6nlnGFPgZAjO', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1082, 'stu_mahadroble_b22e2', 'stu_mahadroble_b22e2@example.som', '$2y$10$hVzqevOVpcfaJTaX7HxQwe3WpyZ91ZdspM5BPohNeVq736o7.H6gK', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1083, 'stu_omaromar_9ec62', 'stu_omaromar_9ec62@example.som', '$2y$10$5mfbc3QTIUTCf4pnfadIMeqwvnSxqg5mpOTPWL8B3l.nuLjayXixO', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1084, 'stu_safiyaadam_0be2c', 'stu_safiyaadam_0be2c@example.som', '$2y$10$0wQOu2WC6A2S8NVdtzWOG.dn0WkaA9WsytnO8sCT.TR4ccHW/GO4O', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1085, 'stu_omarsaid_d42f8', 'stu_omarsaid_d42f8@example.som', '$2y$10$G5Q3fal/mKXIFCcYZRLKNuD7JbJY8mNYpHKFiEj3MXjf37o6UOQqi', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1086, 'stu_ayaanismail_ab1e1', 'stu_ayaanismail_ab1e1@example.som', '$2y$10$H7JTHrCj/iMvEw2Ve5E5ku8D/ZsOwTPgyjbVisxKYV/oefZ3V2VDC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1087, 'stu_ashadirie_9ebfa', 'stu_ashadirie_9ebfa@example.som', '$2y$10$D2R5hGB1hItSL0xqVYmye.FTU3xLlPgWVFZsls2Zr1ypUG7st3X5u', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1088, 'stu_mustafaismail_d0717', 'stu_mustafaismail_d0717@example.som', '$2y$10$wPwMq/LOo9RzUsM1gamF..gMWypfd6zLb8zD79iv2QCzOVJH/AMMW', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1089, 'stu_alisaid_2e34e', 'stu_alisaid_2e34e@example.som', '$2y$10$VKTksc1Ja6oHvgFNdSQh/er85jOrkIPKvzqTCmtUIcj.lR1aS5kQq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1090, 'stu_mahadnur_c79d5', 'stu_mahadnur_c79d5@example.som', '$2y$10$nz1QYddivDMIwSAlV6sJ1ufT9AE3jWluTlNI9tZ9wUSYl3JetLovy', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1091, 'stu_ismailismail_99bf2', 'stu_ismailismail_99bf2@example.som', '$2y$10$KrUtbKNJkpY/Kd/nzgn0tOwCDRC/qt7i/JJFAiUxwSEBsaZKkBFfC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1092, 'stu_ayaanabdulle_aa843', 'stu_ayaanabdulle_aa843@example.som', '$2y$10$W2yqJHciYuzh3etSt8evDOLPaMnXzILdq2WWZK38A6A1jbUWkHKDS', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1093, 'stu_ayaanjama_98040', 'stu_ayaanjama_98040@example.som', '$2y$10$F5agv3TWe4/IDMIBLtHO.upMU0oLYeSQWfJv4Esy8630587m/wpTa', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1094, 'stu_khadraroble_d517f', 'stu_khadraroble_d517f@example.som', '$2y$10$RPkn8Y5GxRAY/yBgt3ZvJOLpHqGiqVTE5l0ccamRE3XhQbRHcqsOm', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1095, 'stu_najmaali_8e12c', 'stu_najmaali_8e12c@example.som', '$2y$10$f9wbGszc5yogQzqgWXuzQezt7JUaCrl5YBworSkuJ5ciFHlwxIlV2', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:35', '2025-12-16 09:27:35'),
(1096, 'stu_ahmedadam_647a4', 'stu_ahmedadam_647a4@example.som', '$2y$10$7Ej1iNS6UzkCiEmoRORCquvi4tSOCAiegBEloFjsHwAAKukJwvt92', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1097, 'stu_farahsheikh_e8f64', 'stu_farahsheikh_e8f64@example.som', '$2y$10$QYHwDHNHMzDTxgzKbhdCh.VG0pubbROV.NrEJe6H1tMwWrBpxQ2fi', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1098, 'stu_laylamuse_d8e4d', 'stu_laylamuse_d8e4d@example.som', '$2y$10$6o9njkod9HFXqthAkYRFruVxFBjmzBpkAmwSvFUbJPqvt0hZlqgxC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1099, 'stu_omarsheikh_bfe1f', 'stu_omarsheikh_bfe1f@example.som', '$2y$10$RVs7XN3KXrec.k3aooT2ueoHhEzRvz8NMBztq1megjaoFH8wZiRsG', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1100, 'stu_fartunmuse_ebbb7', 'stu_fartunmuse_ebbb7@example.som', '$2y$10$R/pp7Jj4G0F/MXbxEEcJ0u6RoiEh2r8Zi1QDBIDsKjJEXlx/1bNpS', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1101, 'stu_laylaomar_46922', 'stu_laylaomar_46922@example.som', '$2y$10$Ppzw4xqsAdx9z4QOkmSq.ewM2V7XJhtFrBRH.WOmMuUqAJf5pOQrC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1102, 'stu_yusufyusuf_b0093', 'stu_yusufyusuf_b0093@example.som', '$2y$10$qDDjpfkAiVDTmSHDgi2fZOJsoeOtemPeEiCrPxLoXxpyBKrEtv5Li', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1103, 'stu_zamzamfarah_a91d7', 'stu_zamzamfarah_a91d7@example.som', '$2y$10$dg44jMoOvm8KZXhtY0GnaueSQWrLoPseD/atERykOJC/YtGN2O75O', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1104, 'stu_jamahassan_c7b85', 'stu_jamahassan_c7b85@example.som', '$2y$10$Wx3DibDSlCfhwx2SYhsdhuy2SaLz/k1iLsC37UMjnoQ06FMuBY38y', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1105, 'stu_laylafarah_16d4e', 'stu_laylafarah_16d4e@example.som', '$2y$10$zAIVNjFDcQ2nPq1CQNUaEu31YSNh90jB/9cFNXyFVaHsNICTbs0ga', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1106, 'stu_nimcoismail_1ed61', 'stu_nimcoismail_1ed61@example.som', '$2y$10$ITqWn5.uWPNyHJPmYjOoCu/CoPMi101uliYHVn442eoPw2G3qbMQ6', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1107, 'stu_ashanur_7cf34', 'stu_ashanur_7cf34@example.som', '$2y$10$ZsSEeZN3uA7FDiuJ5gjKHu7xHk77VbGWOIEIeDZXi5ajIPVLOUunK', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1108, 'stu_abdidirie_668b3', 'stu_abdidirie_668b3@example.som', '$2y$10$qwzwfmZGTU/cpcY7.l2BoueuAGCNRWGjGUeG0cq7RU84VM8Nhzb.a', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1109, 'stu_khadraali_a3c24', 'stu_khadraali_a3c24@example.som', '$2y$10$f3z5SBCo0FFyP1b.iAZBSu8SE2/tIOFwsbq72V6MVqTRezrcI6bba', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1110, 'stu_abdisheikh_504fa', 'stu_abdisheikh_504fa@example.som', '$2y$10$2nDh/NN92FcOu6fCGiCMOOau/qzEtDameL6edsSr7DAs3nhMRl3G.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:36', '2025-12-16 09:27:36'),
(1111, 'stu_mustafawarsame_a720c', 'stu_mustafawarsame_a720c@example.som', '$2y$10$7jczUnnrfGFJ7mRAtGlDZOFZNswYAF0NYX.hYDjbIUdzxOjPq6x.6', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1112, 'stu_farahabdullahi_e1f12', 'stu_farahabdullahi_e1f12@example.som', '$2y$10$ZK4b17GBNUanw0YGvAXrwO86JziyAJmQpy2i32xZwqaVr2Jcd05nW', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1113, 'stu_sahraali_33b81', 'stu_sahraali_33b81@example.som', '$2y$10$N9w5HUtuUngGd4vxLgQKgun27oG/Sqo.ejqoctvZoTz97TmLNLRwS', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1114, 'stu_maryanwarsame_b33f7', 'stu_maryanwarsame_b33f7@example.som', '$2y$10$8PYv341gXPR4qITqVY99Geh3xZwfx41eZxI4bdveCqRtRxs6ECjzW', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1115, 'stu_omaradam_69d82', 'stu_omaradam_69d82@example.som', '$2y$10$MZSDCFi.IK4rwduBT/nGOOqPml.0YC6q1zPSQKxqAP9q5wAbmPoKy', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1116, 'stu_mukhtarsaid_62e59', 'stu_mukhtarsaid_62e59@example.som', '$2y$10$RYGD.TrZADPLF7uqIpIOqOkztSq07BdCgXY38uGNRdrdAhsZcix6.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1117, 'stu_mahadwarsame_19735', 'stu_mahadwarsame_19735@example.som', '$2y$10$EOmJGW0i9Sn5gLLHh8cgOugZUqvRjE14bd8rStXM7I1YJj8NE0zmW', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1118, 'stu_aliroble_b5de2', 'stu_aliroble_b5de2@example.som', '$2y$10$4JZZdA9nSorUdr5HGgPIjOgLbyDiuvsdyuB8/75fEpXxvBVFobTr6', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1119, 'stu_ilhanabdulle_60936', 'stu_ilhanabdulle_60936@example.som', '$2y$10$r/cUEjYgfp6UF1ASgjHyoO3xRQj4SeQ95oT2Iq08vxVb3GCTitz/C', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1120, 'stu_hodansaid_a8083', 'stu_hodansaid_a8083@example.som', '$2y$10$hAJSDtksNyh3D6cuVcUfNeSjpzcysOBzPN9AudW/69P53rGbpVpfW', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1121, 'stu_ilhanali_e6aec', 'stu_ilhanali_e6aec@example.som', '$2y$10$d0uUQ2WsAjG/sNFjytI77.RqdkBbhkOayEHEBOsXZ3.P0gqJl.arS', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1122, 'stu_ifrahmohamed_d0030', 'stu_ifrahmohamed_d0030@example.som', '$2y$10$sdsVCZz/r8ovZdpX0bGII.lNIsuq6y6kC1TxiyPtPHAM3A3W0RN3m', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1123, 'stu_farahdirie_c6223', 'stu_farahdirie_c6223@example.som', '$2y$10$CQRvYtaNpxKGv7vgc3bGS.WgvNHjkkgyM2olmISpiPxSp8x.jwPWC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1124, 'stu_hassanfarah_dcae7', 'stu_hassanfarah_dcae7@example.som', '$2y$10$Nt2tAr9FS0m6yZlm8E1MpeGN2XpGkfLXwS7M7HzLWMuPO0.sK9m.q', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1125, 'stu_ayaansaid_6b336', 'stu_ayaansaid_6b336@example.som', '$2y$10$zM.OQ.mqr0XpcbttF899o.0bio27uW7ScB/DOOzeD8VwwZwFTX8de', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:37', '2025-12-16 09:27:37'),
(1126, 'stu_maryanabdullahi_e5c78', 'stu_maryanabdullahi_e5c78@example.som', '$2y$10$rkdk2O2jikbyQh/CEVyJBu.Mdb4.XDpgoGNeC0czKDUOSJjYt/x8q', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1127, 'stu_ismailadam_26cdd', 'stu_ismailadam_26cdd@example.som', '$2y$10$j8LafDUdLgUkhI1bV3YF7e45Q2EGqkaqC2gSYW8hMuW5FKocpkPYu', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1128, 'stu_ibrahimosman_f74f2', 'stu_ibrahimosman_f74f2@example.som', '$2y$10$Hx120TEEHvWRPEyL1GyS3uInLtKmBSMFc0NW9WEppa7KqGwb9kcmG', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1129, 'stu_ayaanjama_bf152', 'stu_ayaanjama_bf152@example.som', '$2y$10$dOmU4NoIEJqhJZwWgN0fWu2ikRvfPo1l28HONGhM725lCcnwQ1fN6', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1130, 'stu_ashajama_c2352', 'stu_ashajama_c2352@example.som', '$2y$10$388isyHr9Y14jmgUsi.QXOSmlcoG18OflwaAbHJ6rvqpqd8ZxFAZm', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1131, 'stu_nimcojama_9ffb8', 'stu_nimcojama_9ffb8@example.som', '$2y$10$UuPy5at1DOGo0B1efhoI/uK8Hs2AH20zVmp5ZV2/YuN1Ayl0r0RR2', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1132, 'stu_ifrahsaid_4976e', 'stu_ifrahsaid_4976e@example.som', '$2y$10$qKRMVtp9enPmjysTrOTO.OXCwcjO3nHNmaGnWywC3R/mjjVTaUaT.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1133, 'stu_safiyaroble_f0533', 'stu_safiyaroble_f0533@example.som', '$2y$10$eTdL1dOJ9cfa0KJHw81fc.twjsebO1.E/hvK/w/toeW1lZrztOcCy', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1134, 'stu_ahmedsheikh_d38f7', 'stu_ahmedsheikh_d38f7@example.som', '$2y$10$5uCVEHrblxSaBgcTzBm0UuD2ZFzq4owYR1cJLmRNIaMueUJQrsbAy', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1135, 'stu_hassanadam_66082', 'stu_hassanadam_66082@example.som', '$2y$10$MfurA0Uc.f461OtUP9Qn1udygRUlPt51Ks69TxDSGKtojbiOix1Pa', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1136, 'stu_mukhtaradam_aa434', 'stu_mukhtaradam_aa434@example.som', '$2y$10$BFUtDIillSrJhmXwRlA1NuW1oCC.vOvmVJcShMcqt.OF7V6bJa/Ky', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1137, 'stu_fartunfarah_d6acd', 'stu_fartunfarah_d6acd@example.som', '$2y$10$TQ6wG88OE4l6YoKSIGrAHuIzYK6NOf0OmXg8dm27b3fXeorLJFDLC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1138, 'stu_ibrahimsaid_a177b', 'stu_ibrahimsaid_a177b@example.som', '$2y$10$em9bUyhwpptjUvawx2NsxuHZHIKEM4fuwAtabAJFlPsnSHLjM/hWa', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1139, 'stu_mahadabdulle_cbf6e', 'stu_mahadabdulle_cbf6e@example.som', '$2y$10$3An6nThpMNikVHEmAx8WPuiZGcXiXhnu5rBD40FI1qbfaYhTLc8kC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1140, 'stu_najmaali_16cd4', 'stu_najmaali_16cd4@example.som', '$2y$10$VH/6k8CxZUaUBPdpUqBGKuK5vN5rLxzFXAp5uBt3huUMHm80M6f.y', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1141, 'stu_mahadsaid_38b56', 'stu_mahadsaid_38b56@example.som', '$2y$10$qVXYWa3DRmR5buyXk0jw5ep76jXNw/c6VuOu4iXhLOuG2uXaz7192', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:38', '2025-12-16 09:27:38'),
(1142, 'stu_rahmawarsame_4014d', 'stu_rahmawarsame_4014d@example.som', '$2y$10$6dYzZ0jrApuQofP.J6sf9.vh8Lu8PA2NarSUC518sRNRvc1a8gOe.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1143, 'stu_omarabdullahi_a62fc', 'stu_omarabdullahi_a62fc@example.som', '$2y$10$H8UybhYriY2hrUYsFC4shO7HoN2.Fl3u9UR92F0A0OuRcj/gp3MyC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1144, 'stu_najmafarah_c6ec7', 'stu_najmafarah_c6ec7@example.som', '$2y$10$PKuaAy2P7uwj5C7Sfz1EOewlFc.FAgJXAy5MKA7jm1LxcP.gbPUDy', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1145, 'stu_ismailroble_c7d79', 'stu_ismailroble_c7d79@example.som', '$2y$10$/I240wuu/pWVy.hc/wfHie8KHdbUsGy6N5uK.WxFjwFffsWtAtI6.', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1146, 'stu_laylajama_0ae90', 'stu_laylajama_0ae90@example.som', '$2y$10$idby4lbnrXeyEfJKULEQ1.sV0.eV6aVTpu1lH0WYyJ/NjcgETLIfm', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1147, 'stu_sagalroble_d498c', 'stu_sagalroble_d498c@example.som', '$2y$10$gYEP8J85RjfSuCYRlpoJFeeUsNiZ6dwAGPtoBp.01c5E4LGMWK.vO', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1148, 'stu_najmasaid_b7b7c', 'stu_najmasaid_b7b7c@example.som', '$2y$10$Bu.ge6DzNApPPh6Brp8JCuEmLUt9XbCmL/3fNLE3f8fUQuV8zmoeO', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1149, 'stu_najmaguled_9efbe', 'stu_najmaguled_9efbe@example.som', '$2y$10$N//peCuIyvpDkO6y8ho.VOTm2Fe4QN6bC6f5BXezvUtDu2LVPL9/W', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1150, 'stu_sahraomar_e68ae', 'stu_sahraomar_e68ae@example.som', '$2y$10$UqaXdZ21vVHnqRBn5TpkGea6thFAmpy0O1wxSO9P5xaTl.xDTxExi', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1151, 'stu_mukhtarali_d88bd', 'stu_mukhtarali_d88bd@example.som', '$2y$10$TzThCIErR7zMrCu/bR3me.9HnhGP63UCLYVJR4bYjycZlalzhjXqO', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1152, 'stu_najmaismail_5355f', 'stu_najmaismail_5355f@example.som', '$2y$10$tJMB6m9zATEGFZ7g/8ArleEYgbWB.1YUGMz2iM.eWTUqdm46WlRzO', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1153, 'stu_khadranur_e074e', 'stu_khadranur_e074e@example.som', '$2y$10$vC8P6ZRKhZ0hvC5/3.ZqY.b5Rs3yRoJSfVqO5LcDw.sstfGXZxso2', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1154, 'stu_sagalmuse_725b8', 'stu_sagalmuse_725b8@example.som', '$2y$10$mje6UO96i63HuemKwbZq7uU8sU6TbflbG5./H1sSwjv3cMn9zSUZm', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1155, 'stu_mohamedwarsame_0c9aa', 'stu_mohamedwarsame_0c9aa@example.som', '$2y$10$JHSDR7EHtthe2Z7rCJK3xuf1ewGUrhzJBHyCdOJupA1TdhMKuCDFW', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1156, 'stu_ibrahimabdullahi_74e97', 'stu_ibrahimabdullahi_74e97@example.som', '$2y$10$Og3cekFPw8AoGAwmL5RvTeHoaYKpnkr5hwS9Ct6azUH3I6uCZYlVq', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1157, 'stu_ayaanhassan_fa292', 'stu_ayaanhassan_fa292@example.som', '$2y$10$ENQyVAxzre11Ql4QBLpKQ.tscCA400HsmYN83fDbnSBfGZdYOgHMC', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:39', '2025-12-16 09:27:39'),
(1158, 'stu_laylafarah_a7280', 'stu_laylafarah_a7280@example.som', '$2y$10$P9P3Qun3Tv7iYXVEZM2uHe6AJxzTslH5d27pKYTciL8Ehag9EPc4K', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:40', '2025-12-16 09:27:40'),
(1159, 'stu_hodanadam_84f1a', 'stu_hodanadam_84f1a@example.som', '$2y$10$04mxyK8MktgBur9ZBJU6CenSABIfxlXieVvK8ilK2NlGZKNpL/yJ2', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:40', '2025-12-16 09:27:40'),
(1160, 'stu_ibrahimosman_04378', 'stu_ibrahimosman_04378@example.som', '$2y$10$UG0txsr9NKRvXIVty1KdluKHoVU8RaMnQyvOc/lcKQ77VPDQ.VuVW', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:40', '2025-12-16 09:27:40'),
(1161, 'stu_ashaabdullahi_16b9d', 'stu_ashaabdullahi_16b9d@example.som', '$2y$10$t6iVuQb/WJQeYGNQGXPhu.fOoaFaYD794UN36RlnzJ1474GT/eYQu', 4, 2, 1, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-12-16 09:27:40', '2025-12-16 09:27:40'),
(1162, 'uukow', 'abdulkadiruukow@gmail.com', '$2y$10$SvJ4wC3lnApKVCED0CSZ5.g7w.Y878pIiuAZD5WAg1UC/FCKOS8.m', 2, 2, 1, 0, 'ed4687a44443311575150ec4e6d5f879', NULL, NULL, NULL, '2025-12-21 09:59:18', 0, NULL, '2025-12-21 06:19:30', '2025-12-21 06:59:18');

-- --------------------------------------------------------

--
-- Table structure for table `user_action_overrides`
--

CREATE TABLE `user_action_overrides` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `action_id` int(11) NOT NULL,
  `granted` tinyint(1) DEFAULT 1 COMMENT '1 = granted, 0 = denied',
  `override_type` enum('grant','deny') DEFAULT 'grant' COMMENT 'Type of override',
  `created_by` int(11) DEFAULT NULL COMMENT 'Admin who created this override',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_maintenance`
--

CREATE TABLE `vehicle_maintenance` (
  `id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `maintenance_type` varchar(100) NOT NULL,
  `maintenance_date` date NOT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `next_maintenance_date` date DEFAULT NULL,
  `performed_by` varchar(255) DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_calendar`
--
ALTER TABLE `academic_calendar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `academic_sessions`
--
ALTER TABLE `academic_sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `actions`
--
ALTER TABLE `actions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `action_key` (`action_key`),
  ADD KEY `is_active` (`is_active`);

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `admission_applications`
--
ALTER TABLE `admission_applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_no` (`application_no`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `alumni`
--
ALTER TABLE `alumni`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `alumni_donations`
--
ALTER TABLE `alumni_donations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alumni_id` (`alumni_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `asset_code` (`asset_code`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `assignment_student_unique` (`assignment_id`,`student_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `graded_by` (`graded_by`);

--
-- Indexes for table `backup_history`
--
ALTER TABLE `backup_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `branch_code` (`branch_code`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `certificate_number_unique` (`certificate_number`),
  ADD UNIQUE KEY `verification_code_unique` (`verification_code`),
  ADD KEY `template_id` (`template_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `issued_by` (`issued_by`),
  ADD KEY `revoked_by` (`revoked_by`);

--
-- Indexes for table `certificate_templates`
--
ALTER TABLE `certificate_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code_unique` (`code`),
  ADD KEY `template_type` (`template_type`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `chart_of_accounts`
--
ALTER TABLE `chart_of_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `account_code` (`account_code`),
  ADD KEY `parent_account_id` (`parent_account_id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `class_code_branch` (`class_code`,`branch_id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `idx_graduation_status` (`graduation_status`),
  ADD KEY `idx_graduated_at` (`graduated_at`),
  ADD KEY `classes_graduated_by_fk` (`graduated_by`);

--
-- Indexes for table `class_graduation_logs`
--
ALTER TABLE `class_graduation_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `performed_by` (`performed_by`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `class_subjects`
--
ALTER TABLE `class_subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `class_subject_session` (`class_id`,`subject_id`,`session_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `communication_logs`
--
ALTER TABLE `communication_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sent_by` (`sent_by`);

--
-- Indexes for table `curriculum`
--
ALTER TABLE `curriculum`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_type_id` (`exam_type_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `exam_schedule`
--
ALTER TABLE `exam_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_id` (`exam_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `exam_types`
--
ALTER TABLE `exam_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `exam_code` (`exam_code`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- Indexes for table `fee_invoices`
--
ALTER TABLE `fee_invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_no` (`invoice_no`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `generated_by` (`generated_by`);

--
-- Indexes for table `fee_invoice_items`
--
ALTER TABLE `fee_invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `fee_type_id` (`fee_type_id`);

--
-- Indexes for table `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `receipt_no` (`receipt_no`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `received_by` (`received_by`);

--
-- Indexes for table `fee_structures`
--
ALTER TABLE `fee_structures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `fee_type_id` (`fee_type_id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `fee_types`
--
ALTER TABLE `fee_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fee_code` (`fee_code`);

--
-- Indexes for table `grade_scale`
--
ALTER TABLE `grade_scale`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grading_scale_items`
--
ALTER TABLE `grading_scale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `grading_scheme_id` (`grading_scheme_id`);

--
-- Indexes for table `grading_schemes`
--
ALTER TABLE `grading_schemes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `grading_scheme_items`
--
ALTER TABLE `grading_scheme_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `scheme_id` (`scheme_id`);

--
-- Indexes for table `hostels`
--
ALTER TABLE `hostels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `hostel_allocations`
--
ALTER TABLE `hostel_allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `hostel_id` (`hostel_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `hostel_rooms`
--
ALTER TABLE `hostel_rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hostel_id` (`hostel_id`);

--
-- Indexes for table `income`
--
ALTER TABLE `income`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- Indexes for table `inventory_categories`
--
ALTER TABLE `inventory_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `item_code` (`item_code`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `journal_entries`
--
ALTER TABLE `journal_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `journal_entry_details`
--
ALTER TABLE `journal_entry_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `journal_entry_id` (`journal_entry_id`),
  ADD KEY `account_id` (`account_id`);

--
-- Indexes for table `leave_applications`
--
ALTER TABLE `leave_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_id` (`staff_id`),
  ADD KEY `leave_type_id` (`leave_type_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `leave_types`
--
ALTER TABLE `leave_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `leave_code` (`leave_code`);

--
-- Indexes for table `lesson_plans`
--
ALTER TABLE `lesson_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `library_books`
--
ALTER TABLE `library_books`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `book_code` (`book_code`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `library_issues`
--
ALTER TABLE `library_issues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `issued_by` (`issued_by`);

--
-- Indexes for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `parent_message_id` (`parent_message_id`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `module_key` (`module_key`),
  ADD KEY `is_active` (`is_active`);

--
-- Indexes for table `monthly_fee_assignments`
--
ALTER TABLE `monthly_fee_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_assignment` (`student_id`,`fee_type_id`,`month`,`session_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `fee_type_id` (`fee_type_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `month` (`month`),
  ADD KEY `status` (`status`),
  ADD KEY `monthly_fee_assignments_ibfk_6` (`assigned_by`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `parents`
--
ALTER TABLE `parents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payment_allocations`
--
ALTER TABLE `payment_allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `reference` (`allocation_type`,`reference_id`);

--
-- Indexes for table `payroll_structures`
--
ALTER TABLE `payroll_structures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permission_key` (`permission_key`);

--
-- Indexes for table `permission_audit_log`
--
ALTER TABLE `permission_audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `target_type_target_id` (`target_type`,`target_id`),
  ADD KEY `module_id` (`module_id`),
  ADD KEY `action_id` (`action_id`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `idx_audit_log_search` (`target_type`,`target_id`,`created_at`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_no` (`order_no`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `report_cards`
--
ALTER TABLE `report_cards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_exam_report` (`student_id`,`exam_id`),
  ADD KEY `exam_id` (`exam_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `role_action_permissions`
--
ALTER TABLE `role_action_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_module_action_unique` (`role_id`,`module_id`,`action_id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `module_id` (`module_id`),
  ADD KEY `action_id` (`action_id`),
  ADD KEY `idx_role_permissions_lookup` (`role_id`,`module_id`,`action_id`,`granted`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_permission_unique` (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `salary_payments`
--
ALTER TABLE `salary_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `staff_month_unique` (`staff_id`,`payment_month`),
  ADD KEY `processed_by` (`processed_by`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `settings_audit_log`
--
ALTER TABLE `settings_audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `setting_key` (`setting_key`),
  ADD KEY `changed_by` (`changed_by`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `staff_id` (`staff_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `staff_attendance`
--
ALTER TABLE `staff_attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `staff_date_unique` (`staff_id`,`attendance_date`),
  ADD KEY `marked_by` (`marked_by`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD UNIQUE KEY `admission_no` (`admission_no`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `current_class_id` (`current_class_id`),
  ADD KEY `idx_discount` (`discount_type`,`discount_value`);

--
-- Indexes for table `student_advance_credits`
--
ALTER TABLE `student_advance_credits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indexes for table `student_attendance`
--
ALTER TABLE `student_attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_date_subject_unique` (`student_id`,`attendance_date`,`subject_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `marked_by` (`marked_by`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `student_certificates`
--
ALTER TABLE `student_certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `certificate_no_unique` (`certificate_no`),
  ADD UNIQUE KEY `verification_code_unique` (`verification_code`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `template_id` (`template_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `issued_by` (`issued_by`),
  ADD KEY `reissued_from_id` (`reissued_from_id`);

--
-- Indexes for table `student_documents`
--
ALTER TABLE `student_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `student_fee_balance`
--
ALTER TABLE `student_fee_balance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_balance` (`student_id`,`session_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `student_fee_ledger`
--
ALTER TABLE `student_fee_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `month` (`month`),
  ADD KEY `transaction_type` (`transaction_type`),
  ADD KEY `reference` (`reference_id`,`reference_type`);

--
-- Indexes for table `student_marks`
--
ALTER TABLE `student_marks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_exam_unique` (`student_id`,`exam_schedule_id`),
  ADD KEY `exam_schedule_id` (`exam_schedule_id`),
  ADD KEY `entered_by` (`entered_by`);

--
-- Indexes for table `student_parents`
--
ALTER TABLE `student_parents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `student_promotions`
--
ALTER TABLE `student_promotions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `from_class_id` (`from_class_id`),
  ADD KEY `to_class_id` (`to_class_id`);

--
-- Indexes for table `student_transcripts`
--
ALTER TABLE `student_transcripts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transcript_no_unique` (`transcript_no`),
  ADD UNIQUE KEY `transcript_verification_code_unique` (`verification_code`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `from_session_id` (`from_session_id`),
  ADD KEY `to_session_id` (`to_session_id`),
  ADD KEY `program_class_id` (`program_class_id`),
  ADD KEY `issued_by` (`issued_by`);

--
-- Indexes for table `student_transfers`
--
ALTER TABLE `student_transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `from_branch_id` (`from_branch_id`),
  ADD KEY `to_branch_id` (`to_branch_id`);

--
-- Indexes for table `study_materials`
--
ALTER TABLE `study_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subject_code` (`subject_code`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticket_no` (`ticket_no`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ticket_replies`
--
ALTER TABLE `ticket_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `timetable`
--
ALTER TABLE `timetable`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `transcripts`
--
ALTER TABLE `transcripts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transcript_number_unique` (`transcript_number`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `grading_scheme_id` (`grading_scheme_id`),
  ADD KEY `generated_by` (`generated_by`);

--
-- Indexes for table `transport_assignments`
--
ALTER TABLE `transport_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `route_id` (`route_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `transport_routes`
--
ALTER TABLE `transport_routes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `route_code` (`route_code`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `transport_vehicles`
--
ALTER TABLE `transport_vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vehicle_number` (`vehicle_number`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `user_action_overrides`
--
ALTER TABLE `user_action_overrides`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_module_action_unique` (`user_id`,`module_id`,`action_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `module_id` (`module_id`),
  ADD KEY `action_id` (`action_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_user_overrides_lookup` (`user_id`,`module_id`,`action_id`,`granted`);

--
-- Indexes for table `vehicle_maintenance`
--
ALTER TABLE `vehicle_maintenance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_calendar`
--
ALTER TABLE `academic_calendar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `academic_sessions`
--
ALTER TABLE `academic_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `actions`
--
ALTER TABLE `actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=366;

--
-- AUTO_INCREMENT for table `admission_applications`
--
ALTER TABLE `admission_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `alumni`
--
ALTER TABLE `alumni`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `alumni_donations`
--
ALTER TABLE `alumni_donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `backup_history`
--
ALTER TABLE `backup_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `certificate_templates`
--
ALTER TABLE `certificate_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `chart_of_accounts`
--
ALTER TABLE `chart_of_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `class_graduation_logs`
--
ALTER TABLE `class_graduation_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `class_subjects`
--
ALTER TABLE `class_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `communication_logs`
--
ALTER TABLE `communication_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=573;

--
-- AUTO_INCREMENT for table `curriculum`
--
ALTER TABLE `curriculum`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `exam_schedule`
--
ALTER TABLE `exam_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `exam_types`
--
ALTER TABLE `exam_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fee_invoices`
--
ALTER TABLE `fee_invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `fee_invoice_items`
--
ALTER TABLE `fee_invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fee_payments`
--
ALTER TABLE `fee_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `fee_structures`
--
ALTER TABLE `fee_structures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `fee_types`
--
ALTER TABLE `fee_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `grade_scale`
--
ALTER TABLE `grade_scale`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `grading_scale_items`
--
ALTER TABLE `grading_scale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `grading_schemes`
--
ALTER TABLE `grading_schemes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `grading_scheme_items`
--
ALTER TABLE `grading_scheme_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hostels`
--
ALTER TABLE `hostels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `hostel_allocations`
--
ALTER TABLE `hostel_allocations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hostel_rooms`
--
ALTER TABLE `hostel_rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `income`
--
ALTER TABLE `income`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `inventory_categories`
--
ALTER TABLE `inventory_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `journal_entries`
--
ALTER TABLE `journal_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `journal_entry_details`
--
ALTER TABLE `journal_entry_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_applications`
--
ALTER TABLE `leave_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `leave_types`
--
ALTER TABLE `leave_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `lesson_plans`
--
ALTER TABLE `lesson_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `library_books`
--
ALTER TABLE `library_books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `library_issues`
--
ALTER TABLE `library_issues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `medical_records`
--
ALTER TABLE `medical_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `monthly_fee_assignments`
--
ALTER TABLE `monthly_fee_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parents`
--
ALTER TABLE `parents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `payment_allocations`
--
ALTER TABLE `payment_allocations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `payroll_structures`
--
ALTER TABLE `payroll_structures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permission_audit_log`
--
ALTER TABLE `permission_audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_cards`
--
ALTER TABLE `report_cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `role_action_permissions`
--
ALTER TABLE `role_action_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=256;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `salary_payments`
--
ALTER TABLE `salary_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `settings_audit_log`
--
ALTER TABLE `settings_audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `staff_attendance`
--
ALTER TABLE `staff_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1164;

--
-- AUTO_INCREMENT for table `student_advance_credits`
--
ALTER TABLE `student_advance_credits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_attendance`
--
ALTER TABLE `student_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;

--
-- AUTO_INCREMENT for table `student_certificates`
--
ALTER TABLE `student_certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_documents`
--
ALTER TABLE `student_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_fee_balance`
--
ALTER TABLE `student_fee_balance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `student_fee_ledger`
--
ALTER TABLE `student_fee_ledger`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `student_marks`
--
ALTER TABLE `student_marks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `student_parents`
--
ALTER TABLE `student_parents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `student_promotions`
--
ALTER TABLE `student_promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `student_transcripts`
--
ALTER TABLE `student_transcripts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_transfers`
--
ALTER TABLE `student_transfers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `study_materials`
--
ALTER TABLE `study_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ticket_replies`
--
ALTER TABLE `ticket_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `timetable`
--
ALTER TABLE `timetable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transcripts`
--
ALTER TABLE `transcripts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transport_assignments`
--
ALTER TABLE `transport_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transport_routes`
--
ALTER TABLE `transport_routes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transport_vehicles`
--
ALTER TABLE `transport_vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1163;

--
-- AUTO_INCREMENT for table `user_action_overrides`
--
ALTER TABLE `user_action_overrides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vehicle_maintenance`
--
ALTER TABLE `vehicle_maintenance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `academic_calendar`
--
ALTER TABLE `academic_calendar`
  ADD CONSTRAINT `academic_calendar_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `academic_calendar_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `admission_applications`
--
ALTER TABLE `admission_applications`
  ADD CONSTRAINT `admission_applications_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `admission_applications_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`id`);

--
-- Constraints for table `alumni`
--
ALTER TABLE `alumni`
  ADD CONSTRAINT `alumni_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `alumni_donations`
--
ALTER TABLE `alumni_donations`
  ADD CONSTRAINT `alumni_donations_ibfk_1` FOREIGN KEY (`alumni_id`) REFERENCES `alumni` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `announcements_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `assets_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignments_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignments_ibfk_3` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`id`),
  ADD CONSTRAINT `assignments_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  ADD CONSTRAINT `assignment_submissions_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignment_submissions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignment_submissions_ibfk_3` FOREIGN KEY (`graded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `backup_history`
--
ALTER TABLE `backup_history`
  ADD CONSTRAINT `backup_history_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `certificate_templates`
--
ALTER TABLE `certificate_templates`
  ADD CONSTRAINT `certificate_templates_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `chart_of_accounts`
--
ALTER TABLE `chart_of_accounts`
  ADD CONSTRAINT `chart_of_accounts_ibfk_1` FOREIGN KEY (`parent_account_id`) REFERENCES `chart_of_accounts` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_graduated_by_fk` FOREIGN KEY (`graduated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `class_graduation_logs`
--
ALTER TABLE `class_graduation_logs`
  ADD CONSTRAINT `class_graduation_logs_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_graduation_logs_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `class_subjects`
--
ALTER TABLE `class_subjects`
  ADD CONSTRAINT `class_subjects_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_subjects_ibfk_3` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`id`);

--
-- Constraints for table `communication_logs`
--
ALTER TABLE `communication_logs`
  ADD CONSTRAINT `communication_logs_ibfk_1` FOREIGN KEY (`sent_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `curriculum`
--
ALTER TABLE `curriculum`
  ADD CONSTRAINT `curriculum_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `curriculum_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `curriculum_ibfk_3` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`id`);

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `exams`
--
ALTER TABLE `exams`
  ADD CONSTRAINT `exams_ibfk_1` FOREIGN KEY (`exam_type_id`) REFERENCES `exam_types` (`id`),
  ADD CONSTRAINT `exams_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exams_ibfk_3` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`id`);

--
-- Constraints for table `exam_schedule`
--
ALTER TABLE `exam_schedule`
  ADD CONSTRAINT `exam_schedule_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_schedule_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `expenses_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `expenses_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `fee_invoices`
--
ALTER TABLE `fee_invoices`
  ADD CONSTRAINT `fee_invoices_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_invoices_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`id`),
  ADD CONSTRAINT `fee_invoices_ibfk_3` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `fee_invoice_items`
--
ALTER TABLE `fee_invoice_items`
  ADD CONSTRAINT `fee_invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `fee_invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_invoice_items_ibfk_2` FOREIGN KEY (`fee_type_id`) REFERENCES `fee_types` (`id`);

--
-- Constraints for table `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD CONSTRAINT `fee_payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `fee_invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_payments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_payments_ibfk_3` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `fee_structures`
--
ALTER TABLE `fee_structures`
  ADD CONSTRAINT `fee_structures_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_structures_ibfk_2` FOREIGN KEY (`fee_type_id`) REFERENCES `fee_types` (`id`),
  ADD CONSTRAINT `fee_structures_ibfk_3` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`id`);

--
-- Constraints for table `grading_scale_items`
--
ALTER TABLE `grading_scale_items`
  ADD CONSTRAINT `grading_scale_items_ibfk_1` FOREIGN KEY (`grading_scheme_id`) REFERENCES `grading_schemes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grading_schemes`
--
ALTER TABLE `grading_schemes`
  ADD CONSTRAINT `grading_schemes_class_fk` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `grading_schemes_session_fk` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `grading_scheme_items`
--
ALTER TABLE `grading_scheme_items`
  ADD CONSTRAINT `grading_scheme_items_ibfk_1` FOREIGN KEY (`scheme_id`) REFERENCES `grading_schemes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hostels`
--
ALTER TABLE `hostels`
  ADD CONSTRAINT `hostels_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hostel_allocations`
--
ALTER TABLE `hostel_allocations`
  ADD CONSTRAINT `hostel_allocations_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hostel_allocations_ibfk_2` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hostel_allocations_ibfk_3` FOREIGN KEY (`room_id`) REFERENCES `hostel_rooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hostel_rooms`
--
ALTER TABLE `hostel_rooms`
  ADD CONSTRAINT `hostel_rooms_ibfk_1` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `income`
--
ALTER TABLE `income`
  ADD CONSTRAINT `income_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `income_ibfk_2` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD CONSTRAINT `inventory_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `inventory_categories` (`id`),
  ADD CONSTRAINT `inventory_items_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `journal_entries`
--
ALTER TABLE `journal_entries`
  ADD CONSTRAINT `journal_entries_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `journal_entries_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `journal_entry_details`
--
ALTER TABLE `journal_entry_details`
  ADD CONSTRAINT `journal_entry_details_ibfk_1` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `journal_entry_details_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `chart_of_accounts` (`id`);

--
-- Constraints for table `leave_applications`
--
ALTER TABLE `leave_applications`
  ADD CONSTRAINT `leave_applications_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_applications_ibfk_2` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`),
  ADD CONSTRAINT `leave_applications_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lesson_plans`
--
ALTER TABLE `lesson_plans`
  ADD CONSTRAINT `lesson_plans_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lesson_plans_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lesson_plans_ibfk_3` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`id`);

--
-- Constraints for table `library_books`
--
ALTER TABLE `library_books`
  ADD CONSTRAINT `library_books_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `library_issues`
--
ALTER TABLE `library_issues`
  ADD CONSTRAINT `library_issues_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `library_books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `library_issues_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `library_issues_ibfk_3` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD CONSTRAINT `medical_records_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medical_records_ibfk_2` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`parent_message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `monthly_fee_assignments`
--
ALTER TABLE `monthly_fee_assignments`
  ADD CONSTRAINT `monthly_fee_assignments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `monthly_fee_assignments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `monthly_fee_assignments_ibfk_3` FOREIGN KEY (`fee_type_id`) REFERENCES `fee_types` (`id`),
  ADD CONSTRAINT `monthly_fee_assignments_ibfk_4` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`id`),
  ADD CONSTRAINT `monthly_fee_assignments_ibfk_5` FOREIGN KEY (`invoice_id`) REFERENCES `fee_invoices` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `monthly_fee_assignments_ibfk_6` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `parents`
--
ALTER TABLE `parents`
  ADD CONSTRAINT `parents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payment_allocations`
--
ALTER TABLE `payment_allocations`
  ADD CONSTRAINT `payment_allocations_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `fee_payments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payroll_structures`
--
ALTER TABLE `payroll_structures`
  ADD CONSTRAINT `payroll_structures_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `permission_audit_log`
--
ALTER TABLE `permission_audit_log`
  ADD CONSTRAINT `permission_audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `permission_audit_log_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `permission_audit_log_ibfk_3` FOREIGN KEY (`action_id`) REFERENCES `actions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_orders_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quizzes_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quizzes_ibfk_3` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`id`),
  ADD CONSTRAINT `quizzes_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `quiz_attempts_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_attempts_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `quiz_questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `report_cards`
--
ALTER TABLE `report_cards`
  ADD CONSTRAINT `report_cards_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_cards_ibfk_2` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_cards_ibfk_3` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `report_cards_ibfk_4` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`id`);

--
-- Constraints for table `role_action_permissions`
--
ALTER TABLE `role_action_permissions`
  ADD CONSTRAINT `role_action_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_action_permissions_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_action_permissions_ibfk_3` FOREIGN KEY (`action_id`) REFERENCES `actions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `salary_payments`
--
ALTER TABLE `salary_payments`
  ADD CONSTRAINT `salary_payments_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `salary_payments_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `sections_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `settings_audit_log`
--
ALTER TABLE `settings_audit_log`
  ADD CONSTRAINT `settings_audit_log_ibfk_1` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `staff_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff_attendance`
--
ALTER TABLE `staff_attendance`
  ADD CONSTRAINT `staff_attendance_ibfk_1` FOREIGN KEY (`marked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `students_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_advance_credits`
--
ALTER TABLE `student_advance_credits`
  ADD CONSTRAINT `student_advance_credits_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_advance_credits_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`id`),
  ADD CONSTRAINT `student_advance_credits_ibfk_3` FOREIGN KEY (`payment_id`) REFERENCES `fee_payments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `student_attendance`
--
ALTER TABLE `student_attendance`
  ADD CONSTRAINT `student_attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_attendance_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `student_attendance_ibfk_3` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`),
  ADD CONSTRAINT `student_attendance_ibfk_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_certificates`
--
ALTER TABLE `student_certificates`
  ADD CONSTRAINT `student_certificates_class_fk` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `student_certificates_issued_by_fk` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `student_certificates_reissued_from_fk` FOREIGN KEY (`reissued_from_id`) REFERENCES `student_certificates` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `student_certificates_session_fk` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `student_certificates_student_fk` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_certificates_template_fk` FOREIGN KEY (`template_id`) REFERENCES `certificate_templates` (`id`);

--
-- Constraints for table `student_documents`
--
ALTER TABLE `student_documents`
  ADD CONSTRAINT `student_documents_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_documents_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `student_fee_balance`
--
ALTER TABLE `student_fee_balance`
  ADD CONSTRAINT `student_fee_balance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_fee_balance_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`id`);

--
-- Constraints for table `student_fee_ledger`
--
ALTER TABLE `student_fee_ledger`
  ADD CONSTRAINT `student_fee_ledger_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_fee_ledger_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`id`);

--
-- Constraints for table `student_marks`
--
ALTER TABLE `student_marks`
  ADD CONSTRAINT `student_marks_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_marks_ibfk_2` FOREIGN KEY (`exam_schedule_id`) REFERENCES `exam_schedule` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_marks_ibfk_3` FOREIGN KEY (`entered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `student_parents`
--
ALTER TABLE `student_parents`
  ADD CONSTRAINT `student_parents_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_parents_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_promotions`
--
ALTER TABLE `student_promotions`
  ADD CONSTRAINT `student_promotions_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_promotions_ibfk_2` FOREIGN KEY (`from_class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `student_promotions_ibfk_3` FOREIGN KEY (`to_class_id`) REFERENCES `classes` (`id`);

--
-- Constraints for table `student_transcripts`
--
ALTER TABLE `student_transcripts`
  ADD CONSTRAINT `student_transcripts_from_session_fk` FOREIGN KEY (`from_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `student_transcripts_issued_by_fk` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `student_transcripts_program_class_fk` FOREIGN KEY (`program_class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `student_transcripts_student_fk` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_transcripts_to_session_fk` FOREIGN KEY (`to_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `student_transfers`
--
ALTER TABLE `student_transfers`
  ADD CONSTRAINT `student_transfers_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_transfers_ibfk_2` FOREIGN KEY (`from_branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `student_transfers_ibfk_3` FOREIGN KEY (`to_branch_id`) REFERENCES `branches` (`id`);

--
-- Constraints for table `study_materials`
--
ALTER TABLE `study_materials`
  ADD CONSTRAINT `study_materials_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `study_materials_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `study_materials_ibfk_3` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`id`),
  ADD CONSTRAINT `study_materials_ibfk_4` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `support_tickets_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ticket_replies`
--
ALTER TABLE `ticket_replies`
  ADD CONSTRAINT `ticket_replies_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_replies_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `timetable`
--
ALTER TABLE `timetable`
  ADD CONSTRAINT `timetable_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `timetable_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `timetable_ibfk_3` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `timetable_ibfk_4` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`id`);

--
-- Constraints for table `transport_assignments`
--
ALTER TABLE `transport_assignments`
  ADD CONSTRAINT `transport_assignments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transport_assignments_ibfk_2` FOREIGN KEY (`route_id`) REFERENCES `transport_routes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transport_assignments_ibfk_3` FOREIGN KEY (`vehicle_id`) REFERENCES `transport_vehicles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transport_routes`
--
ALTER TABLE `transport_routes`
  ADD CONSTRAINT `transport_routes_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transport_vehicles`
--
ALTER TABLE `transport_vehicles`
  ADD CONSTRAINT `transport_vehicles_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_action_overrides`
--
ALTER TABLE `user_action_overrides`
  ADD CONSTRAINT `user_action_overrides_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_action_overrides_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_action_overrides_ibfk_3` FOREIGN KEY (`action_id`) REFERENCES `actions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_action_overrides_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `vehicle_maintenance`
--
ALTER TABLE `vehicle_maintenance`
  ADD CONSTRAINT `vehicle_maintenance_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `transport_vehicles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vehicle_maintenance_ibfk_2` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
