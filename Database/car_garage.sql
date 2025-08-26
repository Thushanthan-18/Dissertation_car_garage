-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 26, 2025 at 02:50 AM
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
-- Database: `car_garage`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `admin_id` int(11) NOT NULL,
  `staff_id` varchar(10) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','manager','staff') NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `phone` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`admin_id`, `staff_id`, `username`, `email`, `password_hash`, `role`, `created_at`, `phone`) VALUES
(1, 'ST1234', 'thushanthan', 'admin@example.com', '$2y$10$GTJbgbx5Ymbi4jlT79ucJe1WlC74TtUgquUlNUqKYInVULaQ.HEEO', 'admin', '2025-08-13 01:06:38', '07123456789'),
(2, 'ST1818', 'ST18', 'st1818@example.com', '$2y$10$RPTgboy/L9T/Qoamc3ZLxe8ifLoCQmQjywq5dlPBUT69XlkP4ef5y', 'admin', '2025-08-13 03:39:47', '07123456789');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `service_id` int(10) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `status` enum('Pending','Approved','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `customer_id`, `service_id`, `date`, `time`, `status`, `created_at`) VALUES
(1, 2, 1, '2025-08-23', '10:00:00', 'Pending', '2025-08-23 17:13:51'),
(2, 3, 2, '2025-08-24', '14:30:00', 'Approved', '2025-08-23 17:13:51'),
(3, 2, 3, '2025-08-25', '09:15:00', 'Completed', '2025-08-23 17:13:51'),
(4, 5, 4, '2025-08-26', '16:30:00', 'Pending', '2025-08-25 11:28:21'),
(5, 4, 4, '2025-08-26', '14:04:00', 'Pending', '2025-08-25 22:04:10');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `message`, `created_at`) VALUES
(1, 'Thushanthan Shanthakumar', 'thushanthan2518@gmail.com', 'testing', '2025-08-14 10:32:54'),
(2, 'Stacy Lobo', 'thush18@gmail.com', 'Testing', '2025-08-14 22:50:56'),
(3, 'Thushanthan Shanthakumar', 'thushanthan2518@gmail.com', 'testing', '2025-08-15 13:10:00');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `price` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `price`, `created_at`) VALUES
(2, 'Oil Change', 'Standard Oil & Filter', '39.99', '2025-08-23 17:07:18'),
(3, 'MOT Test', 'Annual MOT Test', '49.99', '2025-08-23 17:07:18'),
(4, 'Brake Service', 'Inspection of the brakes', '50.00', '2025-08-23 17:07:18'),
(5, 'Brake pads', 'Replacement of front or rear brake pads including fitting.', '99', '2025-08-25 23:23:22'),
(6, 'Battery Supplied & Fitted', 'New battery supplied and fitted with warranty.', '120', '2025-08-25 23:23:22'),
(7, 'Clutch replacement', 'Full clutch replacement. Price on application.', 'POA', '2025-08-25 23:23:22'),
(8, 'Diagnostic Scan', 'OBD-II diagnostic scan with full report.', '£49', '2025-08-25 23:23:22'),
(9, 'Advanced electrical tracing', 'Detailed tracing of electrical faults (per hour).', '69', '2025-08-25 23:23:22');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','customer') NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `phone` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `role`, `created_at`, `phone`) VALUES
(1, 'pn256', 'bafflingcandy@gmail.com', '$2y$10$5YWzrTqzbbtlBIOoR17iC..tzVTO9AZdp/SkL8OVOE3t4axZXOkiK', 'customer', '2025-08-10 19:23:36', '09113527810'),
(3, 'pratyu101', 'thushanthan2518@gmail.com', '$2y$10$F2V2FmIZAFQULyMC/U07TeIjCrgIWbWLwp6zbBVPYQuTKTL2aLv06', 'customer', '2025-08-12 23:26:29', '07456888139'),
(4, 'Thush', 'thush18@gmail.com', '$2y$10$H3P6Id6xt2TpHqxhxF1sgu4YnhZFxUBM2/C9YvruyH9Z3PtpWiLsW', 'customer', '2025-08-13 14:36:05', '07534773214'),
(5, 'Johnjohn', 'john@testing.com', '$2y$10$.CodjdBWLovi7slFYyJpnufUIZPRYKIg/Wdc7S34OUcLpQQZb034.', 'customer', '2025-08-25 12:25:05', '07534667865');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `staff_id` (`staff_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_service` (`service_id`),
  ADD KEY `idx_date` (`date`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
