-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 26, 2025 at 11:08 AM
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
(18, 7, 'markjohnjopia1@gmail.com', '2025-03-08 09:15:39', NULL),
(19, 18, 'osiasrussell@gmail.com', '2025-04-26 10:04:45', NULL);

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
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `resident_id`, `title`, `description`, `status`, `priority`, `assigned_officer_id`, `created_at`, `updated_at`) VALUES
(1, 19, 'Garbage Collection Issue', 'There is no garbage collection in our area for the past week. Please address this issue urgently.', 'in_progress', 'medium', 20, '2025-04-26 08:31:46', '2025-04-26 09:03:10');

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
  `complaint_id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `priority` enum('low','medium','high') DEFAULT NULL,
  `assigned_officer_id` int(11) DEFAULT NULL,
  `resolved_by` int(11) NOT NULL,
  `resolution_notes` text NOT NULL,
  `personnel_involved` text DEFAULT NULL,
  `resolved_at` timestamp NOT NULL DEFAULT current_timestamp()
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

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(200) NOT NULL,
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
(7, 'Admin', 'Admin', 'Admin', 'markjohnjopia1@gmail.com', '$2y$10$z19ZVmVj87vJb7xorHPdNOhkw18aPP1rIQPii1qJmsAT/Fu0XqKOC', 2147483647, 'Brgy. Sinawal', '3e35ec1eed5462800e1b821376ae4aa92645080a437ccb8c0ac4f02605d50554', '2025-03-09 09:15:39', NULL, NULL, 'admin', 'assets/images/profiles/67c9bdce140fd_Screenshot 2024-09-18 172413.png'),
(18, 'Russell', 'B', 'Osias', 'osiasrussell@gmail.com', '$2y$10$h.nLmVH7Zc3LU3pOJt4fZetJ0LTIwcekyV5dj6G2gdLDPGRLIFIT.', 2147483647, 'Gensan', 'd72e010f524152c197e47a4bb1c41ed6a0bbfd66d8c38c6c0f597d604fbb3741', '2025-04-27 10:04:45', NULL, NULL, 'admin', 'assets/images/profiles/680c93a82c10c_download (2).jpg'),
(19, 'Sample resident', 'sample', 'sample', 'sampleresident@gmail.com', '$2y$10$NAXDkEReF850pdDUXT5ZG.GRFBk3oAue2gnsgj0gWwxqlb8qvltzG', 14523412, '12312312', NULL, NULL, NULL, NULL, 'resident', NULL),
(20, 'SAMPLE OFFICERS', 'ASDA', 'ASDADAS', 'officer@gmail.com', '$2y$10$iCuQxcFWofJezsH9cIvOjOMuCsx.vxTeTBaEWfo1B1MgIOsSFqNbK', 12312312, '1241241', NULL, NULL, NULL, NULL, 'officer', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resident_id` (`resident_id`),
  ADD KEY `assigned_officer_id` (`assigned_officer_id`);

--
-- Indexes for table `complaint_resolutions`
--
ALTER TABLE `complaint_resolutions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `complaint_id` (`complaint_id`),
  ADD KEY `resolved_by` (`resolved_by`);

--
-- Indexes for table `history_complaints`
--
ALTER TABLE `history_complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resident_id` (`resident_id`),
  ADD KEY `assigned_officer_id` (`assigned_officer_id`),
  ADD KEY `resolved_by` (`resolved_by`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `officers_assigned`
--
ALTER TABLE `officers_assigned`
  ADD PRIMARY KEY (`id`),
  ADD KEY `complaint_id` (`complaint_id`),
  ADD KEY `officer_id` (`officer_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `complaint_resolutions`
--
ALTER TABLE `complaint_resolutions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `history_complaints`
--
ALTER TABLE `history_complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `officers_assigned`
--
ALTER TABLE `officers_assigned`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `complaints_ibfk_2` FOREIGN KEY (`assigned_officer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `complaint_resolutions`
--
ALTER TABLE `complaint_resolutions`
  ADD CONSTRAINT `complaint_resolutions_ibfk_1` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`),
  ADD CONSTRAINT `complaint_resolutions_ibfk_2` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `history_complaints`
--
ALTER TABLE `history_complaints`
  ADD CONSTRAINT `history_complaints_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `history_complaints_ibfk_2` FOREIGN KEY (`assigned_officer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `history_complaints_ibfk_3` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `officers_assigned`
--
ALTER TABLE `officers_assigned`
  ADD CONSTRAINT `officers_assigned_ibfk_1` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`),
  ADD CONSTRAINT `officers_assigned_ibfk_2` FOREIGN KEY (`officer_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
