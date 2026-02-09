-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 31, 2025 at 08:09 PM
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
-- Database: `register_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('user','admin','committee') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `created_at`) VALUES
(5, 'IDUM', '25f9e794323b453885f5181f1b624d0b', 'amtoofarmama@gmail.com', 'user', '2025-04-15 08:09:49'),
(6, 'Natnarin', 'e10adc3949ba59abbe56e057f20f883e', 'jw@gmail.com', 'admin', '2025-04-15 08:15:26'),
(8, 'committee_user', 'hashed_password', 'committee@example.com', 'committee', '2025-04-15 15:30:15'),
(13, 'OJ', '$2y$10$gvmOOmzspOxLebj4wYHZb.yHgHyqdN4zYG8TdHGeJ6z.iUY2qHLyi', 'jojodum@hmail.com', 'admin', '2025-04-15 15:54:36'),
(14, 'jweiman', '$2y$10$9tpc8XjoRGHy0K6x8Vl.QOdu.3eXNvxFLEZ3/TtedXYdP5ZHYFjda', 'jwei04893@gmail.com', 'committee', '2025-04-15 16:11:35'),
(24, 'user1', '$2y$10$Hdgl7qcXbpbyfYjLaZbwfuX.23zHPr62WyMOrOgjtbt7WxJmQk5F6', 'user1@gmail.com', 'user', '2025-05-14 14:06:08'),
(25, 'user2', '$2y$10$ZpswVVC.DK/HOSrdd6OgF.ssSADlJxm0QuFrMbNFBRn6KQWVA73WC', 'user2@gmail.com', 'user', '2025-05-14 14:19:00'),
(26, 'user3', '$2y$10$saAYZlO5iJdqmttMZdNhpesae5bNKdgUwptS/tE/uy79LVe7YuQo6', 'user3@gmail.com', 'user', '2025-05-14 14:27:11'),
(27, 'admin', '$2y$10$RTLQ8kDkCXd5Wg41Jq0jruYCCQnX9pgOzG.rSy/mlt/2h2q2daOCi', 'admin@gmail.com', 'admin', '2025-05-14 14:32:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
