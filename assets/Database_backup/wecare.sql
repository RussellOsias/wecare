-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 10, 2025 at 02:28 PM
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
-- Database: `wecare`
--
CREATE DATABASE IF NOT EXISTS `wecare` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `wecare`;

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_logs`
--

CREATE TABLE `admin_activity_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `activity_type` varchar(50) NOT NULL,
  `action` varchar(255) NOT NULL,
  `user_affected_id` int(11) DEFAULT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_activity_logs`
--

INSERT INTO `admin_activity_logs` (`id`, `admin_id`, `activity_type`, `action`, `user_affected_id`, `timestamp`) VALUES
(5, 21, 'user_update', 'Mark John Jopia updated user Mark John Jopia: Role changed from \'admin\' to \'resident\'', 37, '2025-05-01 16:19:12'),
(6, 21, 'profile_update', 'Mark Jopia updated their profile: First name changed from \'Mark John\' to \'Mark\', Phone number changed from \'2147483647\' to \'09514810354\', Address changed from \'Brgy. Sinawal GSC\' to \'Lansang Vill., Brgy. Sinawal GSC\'', 21, '2025-05-01 16:30:21'),
(11, 21, 'complaint_priority', 'Mark Jopia changed priority from low to medium for complaint #8', 33, '2025-05-01 16:54:49'),
(12, 21, 'complaint_assignment', 'Mark Jopia assigned officer SAMPLE OFFICERS ASDADAS to complaint #4', 19, '2025-05-01 16:58:29'),
(13, 21, 'user_creation', 'Mark Jopia created new admin: Mark John Jopia (cocnamabawan@gmail.com)', 40, '2025-05-01 17:00:37'),
(15, 21, 'user_deletion', 'Mark Jopia deleted resident account: Mark John Jopia (resident1@gmail.com)', 34, '2025-05-01 17:06:37');

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `login_time` datetime NOT NULL,
  `logout_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_logs`
--

INSERT INTO `admin_logs` (`id`, `user_id`, `email`, `login_time`, `logout_time`) VALUES
(1, 21, 'markjohnjopia1@gmail.com', '2025-05-01 07:32:04', '2025-05-01 13:35:35'),
(2, 36, 'cocnambawan@gmail.com', '2025-05-01 07:36:43', '2025-05-01 13:39:48'),
(3, 21, 'markjohnjopia1@gmail.com', '2025-05-01 07:40:28', '2025-05-01 13:59:20'),
(4, 21, 'markjohnjopia1@gmail.com', '2025-05-01 08:14:37', '2025-05-01 15:08:39'),
(5, 21, 'markjohnjopia1@gmail.com', '2025-05-01 09:10:33', '2025-05-01 22:22:27'),
(6, 21, 'markjohnjopia1@gmail.com', '2025-05-10 12:25:52', '2025-05-10 19:30:42'),
(7, 21, 'markjohnjopia1@gmail.com', '2025-05-10 14:07:21', '2025-05-10 20:07:49'),
(8, 21, 'markjohnjopia1@gmail.com', '2025-05-10 14:10:23', '2025-05-10 20:21:21'),
(9, 21, 'markjohnjopia1@gmail.com', '2025-05-10 14:24:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('pending','in_progress','resolved') DEFAULT 'pending',
  `priority` enum('low','medium','high') DEFAULT NULL,
  `assigned_officer_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `assigned_personnel` varchar(255) DEFAULT NULL,
  `resolution_notes` text DEFAULT NULL,
  `resolved_by` int(11) DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `resident_id`, `title`, `description`, `status`, `priority`, `assigned_officer_id`, `created_at`, `updated_at`, `assigned_personnel`, `resolution_notes`, `resolved_by`, `resolved_at`) VALUES
(1, 19, 'Garbage Collection Issue', 'There is no garbage collection in our area for the past week. Please address this issue urgently.', 'in_progress', 'low', 20, '2025-04-26 08:31:46', '2025-05-01 08:48:42', NULL, NULL, NULL, NULL),
(4, 19, 'Water Supply Issue', 'Resident reported a disruption in the water supply in the area.', 'in_progress', 'low', 20, '2025-04-26 16:33:43', '2025-05-01 08:53:33', NULL, NULL, NULL, NULL),
(8, 33, 'Hhh', 'gggg', 'in_progress', 'medium', 20, '2025-04-29 20:20:00', '2025-05-01 08:54:49', NULL, NULL, NULL, NULL);

--
-- Triggers `complaints`
--
DELIMITER $$
CREATE TRIGGER `after_complaint_assignment` AFTER UPDATE ON `complaints` FOR EACH ROW BEGIN
    -- Check if the `assigned_officer_id` is updated and not NULL
    IF NEW.assigned_officer_id IS NOT NULL AND NEW.assigned_officer_id <> OLD.assigned_officer_id THEN
        -- Insert the assignment into the `officers_assigned` table
        INSERT INTO officers_assigned (complaint_id, officer_id, assigned_at)
        VALUES (NEW.id, NEW.assigned_officer_id, NOW());
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_complaint_creation` AFTER INSERT ON `complaints` FOR EACH ROW BEGIN
    -- Check if the complaint is created with an assigned officer
    IF NEW.assigned_officer_id IS NOT NULL THEN
        -- Insert the assignment into the `officers_assigned` table
        INSERT INTO officers_assigned (complaint_id, officer_id, assigned_at)
        VALUES (NEW.id, NEW.assigned_officer_id, NOW());
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `complaint_resolutions`
--

CREATE TABLE `complaint_resolutions` (
  `id` int(11) NOT NULL,
  `complaint_id` int(11) NOT NULL,
  `resolved_by` int(11) NOT NULL,
  `resolution_notes` text NOT NULL,
  `personnel_involved` text DEFAULT NULL,
  `resolved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `history_complaints`
--

CREATE TABLE `history_complaints` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `status` enum('pending','in_progress','resolved') NOT NULL,
  `created_at` datetime NOT NULL,
  `description` text NOT NULL,
  `priority` enum('low','medium','high') DEFAULT NULL,
  `resident_id` int(11) NOT NULL,
  `assigned_officer_id` int(11) NOT NULL,
  `assigned_personnel` varchar(255) DEFAULT NULL,
  `resolution_notes` text DEFAULT NULL,
  `resolved_by` int(11) NOT NULL,
  `resolved_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `history_complaints`
--

INSERT INTO `history_complaints` (`id`, `title`, `status`, `created_at`, `description`, `priority`, `resident_id`, `assigned_officer_id`, `assigned_personnel`, `resolution_notes`, `resolved_by`, `resolved_at`) VALUES
(7, 'Street Light Not Working', 'resolved', '2025-04-27 15:48:23', 'The street light near Block B has not been functioning for the last 3 days.', 'high', 19, 20, 'nigger', '', 20, '2025-04-28 12:22:11'),
(8, 'Garbage Collection Delay', 'resolved', '2025-04-27 15:48:23', 'The garbage collection in our neighborhood has been delayed for more than a week, causing an accumulation of waste.', 'medium', 19, 20, 'sds', '', 20, '2025-04-28 12:46:27'),
(9, 'Water Supply Disruption', 'resolved', '2025-04-27 23:38:24', 'There is no water supply in our area for the past two days. Please address this issue urgently.', 'high', 21, 20, 'sds', '', 20, '2025-04-28 12:21:54'),
(10, 'Noise Complaint', 'resolved', '2025-04-28 22:32:03', 'There is excessive noise in the neighborhood from late-night parties. Please address this issue urgently.', 'high', 21, 20, 'Russell Osias', '', 20, '2025-04-28 14:32:55'),
(11, 'Mga adik check', 'resolved', '2025-04-28 22:57:21', 'Daghan adik yawa sigeg foil method.', 'high', 21, 20, 'Russell Osias\nMark John Jopia', '', 20, '2025-04-28 15:04:52'),
(0, '2 Am Videoke', 'resolved', '2025-04-30 03:04:19', 'umay sa 2 am videoke ataya', 'low', 1, 20, 'goku', '', 20, '2025-04-29 19:49:13'),
(5, 'Ddd', 'resolved', '2025-04-30 03:54:16', 'dddd', 'low', 33, 20, 'janjan', '', 20, '2025-04-29 19:56:05');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `attempt_time` datetime NOT NULL DEFAULT current_timestamp(),
  `successful` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_path` varchar(255) DEFAULT NULL,
  `reaction` enum('like','heart','none') DEFAULT 'none'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 21, 'Security Alert: 3 failed login attempts detected at May 10, 2025, 8:22 pm', 1, '2025-05-10 12:22:22');

-- --------------------------------------------------------

--
-- Table structure for table `officers_assigned`
--

CREATE TABLE `officers_assigned` (
  `id` int(11) NOT NULL,
  `complaint_id` int(11) NOT NULL,
  `officer_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `officers_assigned`
--

INSERT INTO `officers_assigned` (`id`, `complaint_id`, `officer_id`, `assigned_at`) VALUES
(1, 1, 20, '2025-04-26 08:31:46'),
(2, 4, 20, '2025-04-26 16:33:43');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(200) NOT NULL,
  `middle_name` varchar(200) NOT NULL,
  `last_name` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL,
  `phone_number` int(200) NOT NULL,
  `address` varchar(200) NOT NULL,
  `session_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `role` enum('admin','resident','officer') DEFAULT 'resident',
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `middle_name`, `last_name`, `email`, `password`, `phone_number`, `address`, `session_token`, `token_expiry`, `reset_token`, `reset_token_expiry`, `role`, `profile_picture`) VALUES
(18, 'Russell', 'B', 'Osias', 'osiasrussell@gmail.com', '$2y$10$h.nLmVH7Zc3LU3pOJt4fZetJ0LTIwcekyV5dj6G2gdLDPGRLIFIT.', 2147483647, 'Gensan', NULL, NULL, NULL, NULL, 'admin', 'assets/images/profiles/680c93a82c10c_download (2).jpg'),
(19, 'Sample resident', 'sample', 'sample', 'sampleresident@gmail.com', '$2y$10$NAXDkEReF850pdDUXT5ZG.GRFBk3oAue2gnsgj0gWwxqlb8qvltzG', 14523412, '12312312', NULL, NULL, NULL, NULL, 'resident', NULL),
(20, 'SAMPLE OFFICERS', 'ASDA', 'ASDADAS', 'officer@gmail.com', '$2y$10$iCuQxcFWofJezsH9cIvOjOMuCsx.vxTeTBaEWfo1B1MgIOsSFqNbK', 12312312, '1241241', NULL, NULL, NULL, NULL, 'officer', NULL),
(21, 'Mark', 'Rama', 'Jopia', 'markjohnjopia1@gmail.com', '$2y$10$TwL8AJoDiOJgYsJCwJe.hObBJaWADapFff5yoZlGzNm8HPiJd2S7K', 2147483647, 'Lansang Vill., Brgy. Sinawal GSC', '2e6464b5e3a09478c653310702b3df21867204c7dffeab348565a909bc10971a', '2025-05-11 14:24:16', NULL, NULL, 'admin', 'assets/images/profiles/6813241e477d8.png'),
(33, 'Sample', '', 'Resident', 'resident@gmail.com', '$2y$10$AnBZBRuzkudzE/uskxEyWe1WmVorOpMaXk1D7pEIrA6Wp/7VMtbgu', 2147483647, 'Sample Adress', NULL, NULL, NULL, NULL, 'resident', NULL),
(35, 'Mark John', '', 'Jopia', 'resident2@gmail.com', '$2y$10$k1L9Q3mIyYOSTHTNa28Vr.rxu7Krym/cJdNK7Hx6oYU9SEcrnvy3K', 2147483647, 'sinawal', NULL, NULL, NULL, NULL, 'resident', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `activity_type` (`activity_type`),
  ADD KEY `user_affected_id` (`user_affected_id`);

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
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
-- AUTO_INCREMENT for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
