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
-- Table structure for table `student_profiles`
--

CREATE TABLE `student_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `year_level` int(11) NOT NULL,
  `birthdate` date DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `religion` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `sub_district` varchar(100) DEFAULT NULL,
  `road` varchar(100) DEFAULT NULL,
  `village` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `father_name` varchar(255) DEFAULT NULL,
  `father_surname` varchar(255) DEFAULT NULL,
  `father_age` int(11) DEFAULT NULL,
  `father_job` varchar(255) DEFAULT NULL,
  `father_phone` varchar(20) DEFAULT NULL,
  `father_income` decimal(10,2) DEFAULT NULL,
  `mother_name` varchar(255) DEFAULT NULL,
  `mother_surname` varchar(255) DEFAULT NULL,
  `mother_age` int(11) DEFAULT NULL,
  `mother_phone` varchar(20) DEFAULT NULL,
  `mother_job` varchar(255) DEFAULT NULL,
  `mother_income` varchar(50) DEFAULT NULL,
  `siblings` int(11) DEFAULT NULL,
  `family_members` int(11) DEFAULT NULL,
  `gpa` decimal(4,2) DEFAULT NULL,
  `scholarship_reason` text DEFAULT NULL,
  `scholarship_history` enum('ไม่เคย','เคย') DEFAULT NULL,
  `parent_status` enum('อยู่ด้วยกัน','แยกกันอยู่ด้วยความจำเป็นด้านอาชีพ','หย่าขาดจากกัน','แยกกันอยู่ด้วยสาเหตุอื่น ๆ') DEFAULT NULL,
  `center` varchar(255) DEFAULT NULL,
  `branch` varchar(255) DEFAULT NULL,
  `age` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `student_profiles`
--

INSERT INTO `student_profiles` (`id`, `user_id`, `first_name`, `last_name`, `student_id`, `year_level`, `birthdate`, `nationality`, `religion`, `phone`, `address`, `province`, `district`, `sub_district`, `road`, `village`, `image`, `father_name`, `father_surname`, `father_age`, `father_job`, `father_phone`, `father_income`, `mother_name`, `mother_surname`, `mother_age`, `mother_phone`, `mother_job`, `mother_income`, `siblings`, `family_members`, `gpa`, `scholarship_reason`, `scholarship_history`, `parent_status`, `center`, `branch`, `age`) VALUES
(12, 25, 'คมกฤติ', 'แช่ตั้ง', '6511011940004', 3, NULL, NULL, NULL, '0909538865', '7/11 ม.1 ต.ทุ่งยาว', 'ตรัง', 'ปะเหลียน', 'ทุ่งยาว', '-', '-', NULL, 'สมปอง', 'ปองศักษ์', 50, 'ธุระกิจส่วนตัว', '0677978646', 10000.00, 'รักทิกาล', 'ดึกมาก', 50, '0873584344', 'ราชการ', '15000', NULL, NULL, NULL, NULL, NULL, 'อยู่ด้วยกัน', 'วิทยาศาสตร์เทคโนโลยี', 'เทคโนโลยีสารสนเทศ', NULL),
(13, 26, 'ศีรินทร์', 'เลิศวัฒนนนท์', '6511011940041', 3, NULL, NULL, NULL, '0632268689', '75/17 หมู่ 4 ', 'นนทบุรี', 'บางใหญ่', 'บางใหญ่', '-', '-', NULL, 'มารุต', 'คำหอม', 53, 'รับราชการ', '0632268459', 18000.00, 'วารุณี', 'มานีเถอะ', 51, '0895471236', 'รับราชการ', '17000', NULL, NULL, NULL, NULL, NULL, 'อยู่ด้วยกัน', 'วิทยาศาสตร์เทคโนโลยี', 'เทคโนโลยีสารสนเทศ', NULL),
(15, 24, 'กดกด', 'กดกด', '458584584', 5, NULL, 'กดกดก', 'กดกด', '185080', 'กดกด', 'กดกด', 'กดกด', 'กดก', 'กดกด', 'กดกด', NULL, 'กดกด', '0', 5, 'กดกด', '8080', 45.00, 'กดกด', 'กดกด', 65, '80', 'กดกด', '5', 5, 5, 1.00, 'กดกด', 'เคย', 'อยู่ด้วยกัน', 'กดกด', 'กดกด', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `student_profiles`
--
ALTER TABLE `student_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD CONSTRAINT `student_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
