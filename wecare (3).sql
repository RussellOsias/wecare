-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 27, 2025 at 03:21 PM
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
(6, 3, '123@gmail.com', '2025-02-27 22:21:28', '2025-02-27 09:21:33');

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
  `role` enum('admin','resident','officer') DEFAULT 'resident'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `middle_name`, `last_name`, `email`, `password`, `phone_number`, `address`, `session_token`, `token_expiry`, `reset_token`, `reset_token_expiry`, `role`) VALUES
(2, 'Mark ', 'asd', 'asdadasd', 'djrussellosias@gmail.com', '$2y$10$gpL553RmuU5B9krgiqL14.qb6NiA8t2UMGROiQECtOg1QMqHDGSzm', 123, '123', 'd9547809705cac16cad95296792889bc69927603c854cabb063c627080395f5b', '2025-02-28 15:05:41', NULL, NULL, 'admin'),
(3, '123123', '1231312', '12312', '123@gmail.com', '$2y$10$QQ4cqURHbYrHDlEJb8zFZ.kHCpJSSXnpk7wNKR2nkS2nLCdvZ12/6', 123, '123', NULL, NULL, NULL, NULL, 'admin'),
(5, 'Russell', '123', '123', 'osiasrussell@gmail.com', '$2y$10$uzwmXx6gmxdYVImUUN4KGOFU9ZKJ.pkJIb99fOr0MdsCUxniC0l6a', 123, '123', NULL, NULL, NULL, NULL, 'resident');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
