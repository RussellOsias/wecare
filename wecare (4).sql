-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 27, 2025 at 08:27 PM
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
(1, 3, '123@gmail.com', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(2, 3, '123@gmail.com', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(3, 3, '123@gmail.com', '0000-00-00 00:00:00', '2025-02-27 15:17:10'),
(4, 3, '123@gmail.com', '2025-02-27 15:17:14', '2025-02-27 15:19:14'),
(5, 3, '123@gmail.com', '2025-02-27 15:19:17', '2025-02-27 09:21:21'),
(6, 3, '123@gmail.com', '2025-02-27 22:21:28', '2025-02-27 09:21:33'),
(7, 3, '123@gmail.com', '2025-02-28 02:05:59', '2025-02-28 02:17:28'),
(8, 3, '123@gmail.com', '2025-02-28 02:17:31', '2025-02-28 02:21:07'),
(9, 3, '123@gmail.com', '2025-02-28 02:24:52', '2025-02-28 02:26:36'),
(10, 3, '123@gmail.com', '2025-02-28 02:27:12', '2025-02-28 03:08:10'),
(11, 2, 'djrussellosias@gmail.com', '2025-02-28 03:04:51', NULL),
(12, 3, '123@gmail.com', '2025-02-28 03:08:28', NULL),
(13, 2, 'djrussellosias@gmail.com', '2025-02-28 03:13:42', NULL);

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

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `image_path`, `reaction`) VALUES
(1, 3, 3, '123121', '2025-02-27 19:02:41', NULL, 'none'),
(2, 3, 2, 'hi', '2025-02-27 19:05:06', NULL, 'none'),
(3, 2, 3, 'kupal', '2025-02-27 19:05:11', NULL, 'none'),
(4, 2, 3, '12312312123312', '2025-02-27 19:06:28', NULL, 'none'),
(5, 2, 3, 'asddasad', '2025-02-27 19:06:41', NULL, 'none'),
(6, 3, 2, 'asdsa', '2025-02-27 19:06:47', NULL, 'none'),
(7, 3, 3, '123', '2025-02-27 19:13:07', '../uploads/67c0b943203d0_pic.jpg', 'none'),
(8, 2, 3, '', '2025-02-27 19:18:55', '../uploads/67c0ba9f0a9b8_pic.jpg', 'none'),
(9, 2, 3, '', '2025-02-27 19:23:53', '../uploads/67c0bbc9309c7_2025-02-23 19-48-01.mp4', 'none'),
(10, 2, 3, '', '2025-02-27 19:24:26', '../uploads/67c0bbeaa8175_20250227-1924-15.0791146.mp4', 'none');

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
(2, 'Mark ', 'asd', 'asdadasd', 'djrussellosias@gmail.com', '$2y$10$gpL553RmuU5B9krgiqL14.qb6NiA8t2UMGROiQECtOg1QMqHDGSzm', 123, '123', '93f0676ca4e59bb931509624eb4e193200905a960e0877e12d18d9fade536835', '2025-03-01 03:13:42', NULL, NULL, 'admin', 'assets/images/profiles/67c0ba8612b9e_1360821.jpeg'),
(3, 'Russell ', 'Bate', 'Osias', '123@gmail.com', '$2y$10$0BqvO0VPQKVNw3MkaMc5Nue.xWYK/r/BWxGinhe/3xld4rlKFlu/K', 123, '123123132', '108322204135d03d4f73916515b840d5686483f2d6c9e515fbeea97a4748a766', '2025-03-01 03:08:28', NULL, NULL, 'admin', 'assets/images/profiles/67c0b3b7b8dd9_pic.jpg'),
(5, 'Russell', '123', '123', 'osiasrussell@gmail.com', '$2y$10$uzwmXx6gmxdYVImUUN4KGOFU9ZKJ.pkJIb99fOr0MdsCUxniC0l6a', 123, '123', NULL, NULL, NULL, NULL, 'resident', NULL);

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
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
