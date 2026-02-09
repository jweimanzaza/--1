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
-- Table structure for table `scholarships`
--

CREATE TABLE `scholarships` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'เปิดรับสมัคร',
  `opening_date` datetime DEFAULT NULL,
  `closing_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `scholarships`
--

INSERT INTO `scholarships` (`id`, `name`, `description`, `created_at`, `status`, `opening_date`, `closing_date`) VALUES
(1, 'ทุนการศึกษาสำหรับนักศึกษาที่มีผลการเรียนดี', 'ทุนการศึกษาสำหรับนักศึกษาที่มีผลการเรียนดี', '2025-04-15 07:37:21', 'เปิดรับสมัคร', '2025-05-01 00:00:00', '2025-05-31 23:59:00'),
(2, 'ทุนการศึกษาสำหรับนักศึกษาที่มีความสามารถพิเศษ', 'ทุนการศึกษาสำหรับนักศึกษาที่มีความสามารถพิเศษ', '2025-04-15 07:37:21', 'เปิดรับสมัคร', '2025-06-01 00:00:00', '2025-06-30 23:59:00'),
(3, 'ทุนการศึกษาสำหรับนักศึกษาที่ขาดแคลนทุนทรัพย์', 'ทุนการศึกษาสำหรับนักศึกษาที่ขาดแคลนทุนทรัพย์', '2025-04-15 07:37:21', 'เปิดรับสมัคร', '2025-07-01 00:00:00', '2025-07-31 23:59:59'),
(33, 'ทุนการศึกษาสำหรับนักศึกษาที่ขาดแคลนทุนทรัพย์ ปี21', 'ทุนการศึกษาสำหรับนักศึกษาที่ขาดแคลนทุนทรัพย์', '2025-05-14 17:19:20', 'เปิดรับสมัคร', '2021-01-15 00:19:00', '2025-05-31 00:19:00'),
(34, 'ทุนการศึกษาสำหรับนักศึกษาที่มีผลการเรียนดี ปี21', 'ทุนการศึกษาสำหรับนักศึกษาที่มีผลการเรียนดี', '2025-05-14 17:19:48', 'เปิดรับสมัคร', '2021-01-15 00:19:00', '2025-05-31 00:19:00'),
(35, 'ทุนการศึกษาสำหรับนักศึกษาที่มีความสามารถพิเศษ ปี21', 'ทุนการศึกษาสำหรับนักศึกษาที่มีความสามารถพิเศษ', '2025-05-14 17:20:02', 'เปิดรับสมัคร', '2021-01-15 00:19:00', '2025-05-31 00:20:00'),
(36, 'ทุนการศึกษาสำหรับนักศึกษาที่มีผลการเรียนดี ปี23', 'ทุนการศึกษาสำหรับนักศึกษาที่มีผลการเรียนดี', '2025-05-14 17:20:20', 'เปิดรับสมัคร', '2023-06-15 00:20:00', '2025-05-31 00:20:00'),
(37, 'ทุนการศึกษาสำหรับนักศึกษาที่ขาดแคลนทุนทรัพย์ ปี23', 'ทุนการศึกษาสำหรับนักศึกษาที่ขาดแคลนทุนทรัพย์', '2025-05-14 17:20:40', 'ปิดรับสมัคร', '2023-01-15 00:20:00', '2025-05-15 00:20:00'),
(38, 'ทุนการศึกษาสำหรับนักศึกษาที่มีความสามารถพิเศษ ปี23', 'ทุนการศึกษาสำหรับนักศึกษาที่มีความสามารถพิเศษ', '2025-05-14 17:21:33', 'ปิดรับสมัคร', '2023-01-15 00:21:00', '2025-05-15 00:21:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `scholarships`
--
ALTER TABLE `scholarships`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `scholarships`
--
ALTER TABLE `scholarships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
