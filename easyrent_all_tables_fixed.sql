-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 21, 2025 at 11:33 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `easyrent`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`, `updated_at`) VALUES
(1, 993033, 'profile_update', 'User updated their profile.', '127.0.0.1', '2025-12-17 10:38:13', '2025-12-17 10:38:13');

-- --------------------------------------------------------

--
-- Table structure for table `agent_ratings`
--

CREATE TABLE `agent_ratings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `agent_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `property_id` bigint(20) UNSIGNED DEFAULT NULL,
  `rating` tinyint(4) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `amenities`
--

CREATE TABLE `amenities` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `apartments`
--

CREATE TABLE `apartments` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) UNSIGNED NOT NULL,
  `apartment_type` varchar(255) DEFAULT NULL,
  `apartment_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `duration` decimal(8,4) DEFAULT NULL,
  `range_start` datetime DEFAULT NULL,
  `range_end` datetime DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `pricing_type` enum('total','monthly') NOT NULL DEFAULT 'total',
  `price_configuration` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`price_configuration`)),
  `supported_rental_types` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON array of supported rental types: hourly, daily, weekly, monthly, yearly' CHECK (json_valid(`supported_rental_types`)),
  `hourly_rate` decimal(10,2) DEFAULT NULL,
  `daily_rate` decimal(10,2) DEFAULT NULL,
  `weekly_rate` decimal(10,2) DEFAULT NULL,
  `monthly_rate` decimal(10,2) DEFAULT NULL,
  `yearly_rate` decimal(10,2) DEFAULT NULL,
  `default_rental_type` enum('hourly','daily','weekly','monthly','yearly') NOT NULL DEFAULT 'monthly',
  `occupied` tinyint(1) NOT NULL DEFAULT 0,
  `apartment_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `apartments`
--

INSERT INTO `apartments` (`id`, `property_id`, `apartment_type`, `apartment_type_id`, `tenant_id`, `user_id`, `duration`, `range_start`, `range_end`, `amount`, `pricing_type`, `price_configuration`, `supported_rental_types`, `hourly_rate`, `daily_rate`, `weekly_rate`, `monthly_rate`, `yearly_rate`, `default_rental_type`, `occupied`, `apartment_id`, `created_at`, `updated_at`) VALUES
(17, 4353881, '2-Bedroom', NULL, 869157, 993033, NULL, '2025-12-28 00:00:00', '2026-12-28 00:00:00', 1800000.00, 'total', NULL, '[\"monthly\"]', NULL, 50000.00, NULL, 1000000.00, NULL, 'monthly', 1, 1558236, '2025-12-17 10:47:55', NULL),
(18, 9533782, '4-Bedroom', NULL, NULL, 993033, NULL, NULL, NULL, 900000.00, 'total', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'monthly', 0, 7826796, '2025-12-17 12:02:49', NULL),
(19, 9533782, '4-Bedroom', NULL, 869157, 993033, NULL, '2025-12-17 14:27:47', '2026-12-17 14:27:47', 1800000.00, 'total', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'monthly', 1, 1314527, '2025-12-17 13:27:47', NULL),
(20, 9533782, '2-Bedroom', NULL, NULL, 993033, NULL, NULL, NULL, 1800000.00, 'total', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'monthly', 0, 1159283, '2025-12-17 14:02:48', NULL),
(21, 9533782, '2-Bedroom', NULL, NULL, 993033, NULL, '2025-12-28 00:00:00', '2026-12-28 00:00:00', 189999.00, 'total', NULL, '[\"monthly\"]', NULL, NULL, NULL, 189999.00, NULL, 'monthly', 0, 4338954, '2025-12-18 00:48:42', NULL),
(30, 5484710, '3-Bedroom', NULL, 869157, 993033, NULL, '2025-12-28 00:00:00', '2026-12-28 00:00:00', 900000.00, 'total', NULL, '[\"monthly\",\"yearly\",\"quarterly\",\"semi_annually\",\"bi_annually\"]', NULL, NULL, NULL, NULL, NULL, 'yearly', 1, 5936714, '2025-12-20 21:16:33', NULL),
(31, 5484710, 'Penthouse', NULL, 869157, 993033, NULL, '2025-12-28 00:00:00', '2026-12-28 00:00:00', 1200000.00, 'total', NULL, '[\"yearly\",\"monthly\"]', NULL, NULL, NULL, 100000.00, 1200000.00, 'yearly', 1, 5571100, '2025-12-21 08:39:47', NULL),
(32, 9533782, '3-Bedroom', NULL, NULL, 993033, NULL, '2025-12-22 00:00:00', '2026-01-22 00:00:00', 1200000.00, 'total', NULL, '[\"monthly\",\"quarterly\",\"semi_annually\",\"yearly\",\"bi_annually\"]', NULL, NULL, NULL, 1200000.00, NULL, 'monthly', 0, 7945305, '2025-12-21 10:26:39', NULL),
(33, 9533782, '2-Bedroom', NULL, NULL, 993033, NULL, '2025-12-21 00:00:00', '2025-12-28 00:00:00', 189999.00, 'total', NULL, '[\"weekly\",\"monthly\"]', NULL, NULL, 189999.00, 822695.67, NULL, 'weekly', 0, 1157153, '2025-12-21 11:12:51', NULL),
(34, 9533782, '3-Bedroom', NULL, 869157, 993033, NULL, '2025-12-22 00:00:00', '2025-12-29 00:00:00', 2100000.00, 'total', NULL, '[\"hourly\",\"daily\"]', 2100000.00, 50400000.00, NULL, NULL, NULL, 'hourly', 1, 3582811, '2025-12-21 11:18:02', NULL),
(35, 9533782, '3-Bedroom', NULL, NULL, 993033, 0.2500, '2025-12-21 00:00:00', '2025-12-28 00:00:00', 90000.00, 'total', NULL, '[\"weekly\",\"monthly\"]', NULL, NULL, 90000.00, 389700.00, NULL, 'weekly', 1, 9173307, '2025-12-21 11:26:23', NULL),
(36, 6476849, '3-Bedroom', NULL, NULL, 578063, 12.0000, '2025-12-22 00:00:00', '2026-12-22 00:00:00', 2200000.00, 'total', NULL, '[\"yearly\",\"monthly\"]', NULL, NULL, NULL, 183333.33, 2200000.00, 'yearly', 0, 7616591, '2025-12-21 12:11:41', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `apartment_invitations`
--

CREATE TABLE `apartment_invitations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `apartment_id` bigint(20) UNSIGNED NOT NULL,
  `landlord_id` bigint(20) UNSIGNED NOT NULL,
  `invitation_token` varchar(64) NOT NULL,
  `invitation_url` text DEFAULT NULL,
  `status` enum('active','used','expired','cancelled') NOT NULL DEFAULT 'active',
  `expires_at` timestamp NULL DEFAULT NULL,
  `prospect_email` varchar(255) DEFAULT NULL,
  `prospect_phone` varchar(20) DEFAULT NULL,
  `prospect_name` varchar(255) DEFAULT NULL,
  `tenant_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `viewed_at` timestamp NULL DEFAULT NULL,
  `payment_initiated_at` timestamp NULL DEFAULT NULL,
  `payment_completed_at` timestamp NULL DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT NULL,
  `payment_reference` varchar(255) DEFAULT NULL,
  `lease_duration` int(11) DEFAULT NULL,
  `move_in_date` date DEFAULT NULL,
  `session_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`session_data`)),
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `authentication_required` tinyint(1) NOT NULL DEFAULT 0,
  `registration_source` varchar(255) DEFAULT NULL,
  `referral_source` varchar(255) DEFAULT NULL,
  `session_expires_at` timestamp NULL DEFAULT NULL,
  `access_count` int(11) NOT NULL DEFAULT 0,
  `last_accessed_at` timestamp NULL DEFAULT NULL,
  `last_accessed_ip` varchar(45) DEFAULT NULL,
  `security_hash` varchar(255) DEFAULT NULL,
  `rate_limit_count` int(11) NOT NULL DEFAULT 0,
  `rate_limit_reset_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `apartment_invitations`
--

INSERT INTO `apartment_invitations` (`id`, `apartment_id`, `landlord_id`, `invitation_token`, `invitation_url`, `status`, `expires_at`, `prospect_email`, `prospect_phone`, `prospect_name`, `tenant_user_id`, `viewed_at`, `payment_initiated_at`, `payment_completed_at`, `total_amount`, `payment_reference`, `lease_duration`, `move_in_date`, `session_data`, `metadata`, `authentication_required`, `registration_source`, `referral_source`, `session_expires_at`, `access_count`, `last_accessed_at`, `last_accessed_ip`, `security_hash`, `rate_limit_count`, `rate_limit_reset_at`, `created_at`, `updated_at`) VALUES
(1, 1565025, 993033, '0ab4e7472e771054c0dd6cb5ea18ae710ba0591f947d6611bc9e54eb397f7a9b', NULL, 'active', '2026-01-16 10:12:34', NULL, NULL, NULL, NULL, '2025-12-17 10:12:51', NULL, NULL, 1800000.00, NULL, 12, '2025-12-24', NULL, NULL, 0, NULL, NULL, NULL, 2, '2025-12-17 11:10:30', '127.0.0.1', '$2y$10$pO8wTNuSZRQBiVnr6TeGEOByyXe7wiWenOtgjLoTh9eRDnxKd4HcG', 2, '2025-12-17 11:12:34', '2025-12-17 10:12:34', '2025-12-17 11:10:30'),
(2, 1558236, 993033, '70be2cd3560e4041cca408d3ff108c7ba06ce98ec0a0ac048c4ccc57d0063cb1', NULL, 'cancelled', '2026-01-16 10:48:47', NULL, NULL, NULL, NULL, '2025-12-17 10:49:03', NULL, NULL, 1800000.00, NULL, 6, '2025-12-24', NULL, NULL, 0, NULL, NULL, NULL, 1, '2025-12-17 10:49:03', '127.0.0.1', '$2y$10$x0yF1g4DuCUHncI.u.t16et6FruZrn3/5ZP8eohWNn2tRYYaQ7zZK', 1, '2025-12-17 11:48:47', '2025-12-17 10:48:47', '2025-12-17 11:11:22'),
(3, 1558236, 993033, 'f040a192b02d8a257b094e75f1b7ca0badd33b397b25dcb9d398b17e565ca830', NULL, 'cancelled', '2026-01-16 11:11:22', NULL, NULL, NULL, NULL, '2025-12-17 11:11:35', NULL, NULL, 1800000.00, NULL, 6, '2025-12-24', NULL, NULL, 0, NULL, NULL, NULL, 1, '2025-12-17 11:11:35', '127.0.0.1', '$2y$10$FW67MeGRrGg0p0FtrH3/8uwl5prWOEjo6pjjwmU0POT2mnxiW1Upe', 1, '2025-12-17 12:11:22', '2025-12-17 11:11:22', '2025-12-21 17:31:28'),
(4, 7826796, 993033, '37c4ca9eb73511b36e326da0aee1a052bbeeee7e5873eee8fdfe6ac075c19741', NULL, 'cancelled', '2026-01-16 12:02:54', 'kagoor@easyrent.com', NULL, 'kayode abdul', 808169, '2025-12-17 12:03:27', '2025-12-17 12:03:56', NULL, 900000.00, NULL, 12, '2025-12-24', NULL, NULL, 0, NULL, NULL, NULL, 1, '2025-12-17 12:03:27', '127.0.0.1', '$2y$10$/f07TpsyLRBU6csRatJ.oefOs.WK0z.00jI5RGaoecfGpYS0DMZ5O', 1, '2025-12-17 13:02:54', '2025-12-17 12:02:54', '2025-12-17 12:14:45'),
(5, 7826796, 993033, 'b6d8b6c6f7237148cb7f410a287c897fda7ef10ae67e47807a799ddd120bfcfd', NULL, 'cancelled', '2026-01-16 12:14:45', NULL, NULL, NULL, NULL, '2025-12-17 12:15:04', NULL, NULL, 900000.00, NULL, 12, '2025-12-24', NULL, NULL, 0, NULL, NULL, NULL, 1, '2025-12-17 12:15:04', '127.0.0.1', '$2y$10$WxaaFaYd4.jTIrImo4y2GeIhYR8HO.zUwHBTcB9j.jR7ZEJ3ALRNG', 1, '2025-12-17 13:14:45', '2025-12-17 12:14:45', '2025-12-17 13:12:47'),
(6, 7826796, 993033, 'ecd10415df7fdae3bdf09995eb8620f159904cdc287c87c0ec265619a5153c9d', NULL, 'cancelled', '2026-01-16 13:12:47', NULL, NULL, NULL, NULL, '2025-12-17 13:13:09', NULL, NULL, 900000.00, NULL, 12, '2025-12-24', NULL, NULL, 0, NULL, NULL, NULL, 1, '2025-12-17 13:13:09', '127.0.0.1', '$2y$10$Y/QLg.Nk8iD13z1rTkfULerF00S/epMNAfAYG6YUbLUp9o5WBh0LG', 1, '2025-12-17 14:12:47', '2025-12-17 13:12:47', '2025-12-17 14:03:00'),
(7, 7826796, 993033, '8b8169d9341eed946f0ca557325e5bf0ef49a62cf61a2f035b5d611f28330586', NULL, 'cancelled', '2026-01-16 14:03:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, '$2y$10$Qb7QTXsNrrvbGARMRnkyl.4j1p36fEc4RkbISyqOWF9Q.S7FX4XO.', 0, '2025-12-17 15:03:00', '2025-12-17 14:03:00', '2025-12-17 15:54:04'),
(8, 7826796, 993033, '9dca93f89e1b577317eaf27d65df422750b64c2ade5f1eafcf531536d3a45fe3', NULL, 'cancelled', '2026-01-16 15:54:04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, '$2y$10$ouD6xyFF8PKzI.RHfohDeeMqoNSc7ETY7ATv2c5Y.UFV3.uKhJHk.', 0, '2025-12-17 16:54:05', '2025-12-17 15:54:05', '2025-12-17 16:16:39'),
(9, 1159283, 993033, '7e00bf915b30524b27be979e6e495b51961cea3f1995a322c75233e1e22ba4aa', NULL, 'cancelled', '2026-01-16 15:54:33', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, '$2y$10$A1431d89tH4p/4Cs8G28GOnjT69VybfWwOmb5a1Ah9YZmZj/4AEiO', 0, '2025-12-17 16:54:33', '2025-12-17 15:54:33', '2025-12-18 00:34:46'),
(10, 7826796, 993033, '2e1f0fb21d49ecdf5ae53731951eadc7e1aab153117a4a7175207771c6289ffa', NULL, 'cancelled', '2026-01-16 16:16:39', NULL, NULL, NULL, NULL, '2025-12-17 16:17:09', NULL, NULL, 900000.00, NULL, 12, '2026-02-28', NULL, NULL, 0, NULL, NULL, NULL, 1, '2025-12-17 16:17:09', '127.0.0.1', '$2y$10$stZQv5E34HUNRuq1mMJvl.vQWm2knR9J41QFOsXfi.LXk0Hz/ZL0i', 1, '2025-12-17 17:16:39', '2025-12-17 16:16:39', '2025-12-21 18:24:36'),
(11, 1159283, 993033, 'c5a04aac528d61fe00961d2e0a11362249c8418a646d60abe5be0b0914f81b17', NULL, 'cancelled', '2026-01-17 00:34:46', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, '$2y$10$IJVAISS0ujI6JZZvrwMkcOfjpKOXezfyuR9ozuDv8CwkxlNkn0JrK', 0, '2025-12-18 01:34:46', '2025-12-18 00:34:46', '2025-12-20 00:08:16'),
(12, 1159283, 993033, 'a7eb28c5e119d53b3f15216934bb6ddfc5869beddff23db314c9131b2d9ca46a', NULL, 'cancelled', '2026-01-19 00:08:16', NULL, NULL, NULL, NULL, '2025-12-20 00:08:40', NULL, NULL, 1800000.00, NULL, 12, '2025-12-27', NULL, NULL, 0, NULL, NULL, NULL, 1, '2025-12-20 00:08:40', '127.0.0.1', '$2y$10$fbWrDQriq7acQWVl5oHC2.Y0NxXkslcjgRrPSf1fXneRO3ACumJQK', 1, '2025-12-20 01:08:16', '2025-12-20 00:08:16', '2025-12-20 01:09:54'),
(13, 1159283, 993033, '4b68e8f192e1503f033c8934c901c25059928887d3c62389bdcb744fa587a05d', NULL, 'cancelled', '2026-01-19 01:09:53', 'smith@easyrent.africa', '08123435674', 'kenny abdul', 869157, '2025-12-20 01:11:49', '2025-12-20 01:12:02', NULL, 1800000.00, NULL, 12, '2025-12-27', NULL, NULL, 0, NULL, NULL, NULL, 1, '2025-12-20 01:11:49', '127.0.0.1', '$2y$10$dR04wfZOt2Bs912JoSFWiecOC1ZhSk/rRwQfjkmhFwHtOOs6FdbCe', 1, '2025-12-20 02:09:54', '2025-12-20 01:09:54', '2025-12-20 10:31:39'),
(14, 1159283, 993033, '75047fdd36977a6101e7c9d36a9d00c69491b510a55cec2a8a5bf8ddfb826526', NULL, 'cancelled', '2026-01-19 10:31:39', NULL, NULL, NULL, NULL, '2025-12-20 10:31:53', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 1, '2025-12-20 10:31:53', '127.0.0.1', '$2y$10$6zBSiu9ettWDSA4y/k6z3eQ7ArigDwTWn9C9mCWFIVweFa9HcNlmO', 1, '2025-12-20 11:31:39', '2025-12-20 10:31:39', '2025-12-21 17:13:54'),
(15, 5936714, 993033, '71d391a9414658a7fa2e6f05e6f882574a33e50c61f1e1dbb571147ea9c8cf62', NULL, 'cancelled', '2026-01-19 21:16:43', NULL, NULL, NULL, NULL, '2025-12-20 21:16:58', NULL, NULL, 900000.00, NULL, 6, '2025-12-27', NULL, NULL, 0, NULL, NULL, NULL, 1, '2025-12-20 21:16:58', '127.0.0.1', '$2y$10$kvbRgwyla0GZkWtTwg54b.2xfBT57fl6xE2yq1ITbiYjOLNZAZrlG', 1, '2025-12-20 22:16:43', '2025-12-20 21:16:43', '2025-12-20 23:22:14'),
(16, 5936714, 993033, '6a05b8d4ec18d416cc5269bafc29fc4ccb106b69fd8140b952fd154b7d2b59d2', NULL, 'cancelled', '2026-01-19 23:22:14', 'kingTusk@easyrent.africa', NULL, 'kayode abdul', 578063, '2025-12-20 23:22:32', '2025-12-20 23:23:18', NULL, 900000.00, NULL, 12, '2025-12-28', NULL, NULL, 0, NULL, NULL, NULL, 4, '2025-12-20 23:22:55', '127.0.0.1', '$2y$10$hlCzRPushFDr34duFb3yCe.Cu0CvqqPKRdbWirzGCml.10Hz4eZq.', 4, '2025-12-21 00:22:14', '2025-12-20 23:22:14', '2025-12-20 23:39:04'),
(17, 5936714, 993033, '22fd5d472839f89e87b147ae823effa95b3725fc2c19c7c1772934e50725d2cd', NULL, 'cancelled', '2026-01-19 23:39:04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, '$2y$10$AQ9oqfR9TrsPe.M3dj7hie0qcSxMRrVKQmU3ObsI33vSHt.eXivdq', 0, '2025-12-21 00:39:04', '2025-12-20 23:39:04', '2025-12-20 23:49:18'),
(18, 5936714, 993033, 'c2b8446c167dedcbff4a10d6613271a2f6052b4865b03a083ea2b3132e6dfa34', NULL, 'cancelled', '2026-01-19 23:49:18', 'kingTusk@easyrent.africa', NULL, 'kayode abdul', 578063, '2025-12-20 23:58:23', '2025-12-20 23:58:51', NULL, 900000.00, NULL, 12, '2025-12-28', NULL, NULL, 0, NULL, NULL, NULL, 6, '2025-12-20 23:58:23', '127.0.0.1', '$2y$10$cRf4Yco3v4Y/H6YeWdN4O.QQFnnrUg0awBsJWYoy0IT4beJm1hpgu', 6, '2025-12-21 00:49:18', '2025-12-20 23:49:18', '2025-12-21 08:09:05'),
(19, 4338954, 993033, 'cd70e96c427a9e8ab716c6ed8a10f1764380bafc1685f2ca724c4b530858bfc7', NULL, 'cancelled', '2026-01-20 07:40:15', 'kingTusk@easyrent.africa', NULL, 'kayode abdul', 578063, '2025-12-21 07:40:25', '2025-12-21 07:40:33', NULL, 189999.00, NULL, 12, '2025-12-28', NULL, NULL, 0, NULL, NULL, NULL, 1, '2025-12-21 07:40:25', '127.0.0.1', '$2y$10$2KQ8nFzLNCctRIoVcQRSVeagskOm.YSu53CujwyxLP7tkH82sWZRy', 1, '2025-12-21 08:40:15', '2025-12-21 07:40:15', '2025-12-21 10:31:13'),
(20, 5936714, 993033, '26d2cadbcfc3ef730b85c8c1a0c59eb5051724f812c5ad03048ea3a5b4ec4872', NULL, 'cancelled', '2026-01-20 08:09:05', 'kingTusk@easyrent.africa', NULL, 'kayode abdul', 578063, '2025-12-21 08:13:49', '2025-12-21 08:11:44', NULL, 900000.00, NULL, 12, '2025-12-28', NULL, NULL, 0, NULL, NULL, NULL, 2, '2025-12-21 08:13:49', '127.0.0.1', '$2y$10$8Gafamy0.0dfxCvwhFm.NudqCtjaBbh/NpVzI13p.lqO7xvpmn9Ya', 2, '2025-12-21 09:09:05', '2025-12-21 08:09:05', '2025-12-21 18:08:36'),
(21, 7945305, 993033, '3ed259163ab330211a5fe2a6df91536a76838986f3bacf9676b47b1a793f7238', NULL, 'active', '2026-01-20 10:30:31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, '$2y$10$Bv4FSXUBfhf60Xotkx8YWOITusZXz276Zeq832BJsqO1U/9qBwL/C', 0, '2025-12-21 11:30:31', '2025-12-21 10:30:31', '2025-12-21 10:30:31'),
(22, 4338954, 993033, 'e680ef2b3b75243316d4a33a160ec3154c4dd5eca0788be3d9741a661eacc346', NULL, 'active', '2026-01-20 10:31:13', NULL, NULL, NULL, NULL, '2025-12-21 11:31:32', NULL, NULL, 189999.00, NULL, 1, '2025-12-28', NULL, NULL, 0, NULL, NULL, NULL, 4, '2025-12-21 11:31:32', '127.0.0.1', '$2y$10$v8nHs5h5F7uY5wAH/.o00.mQ0HCtO5SdaqhJvfiDknIauylSqtmo.', 4, '2025-12-21 11:31:13', '2025-12-21 10:31:13', '2025-12-21 11:34:52'),
(23, 1157153, 993033, '34e3bbff97c2f54cd3249305855c33c47f4bd78c3b183cfe8f1cdf7d44457e8e', NULL, 'cancelled', '2026-01-20 11:31:24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, '$2y$10$Q00zM4FxFGfuyvK6wV0J7.4UCmduDKELpk9ujrLzlYYiryFozwPZm', 0, '2025-12-21 12:31:24', '2025-12-21 11:31:24', '2025-12-21 16:49:30'),
(24, 9173307, 993033, '87acad8338d7110ed8be6d6d2c9f1d6926ab50fa76bfe749f10da9d6115a0e5c', NULL, 'cancelled', '2026-01-20 11:45:21', NULL, NULL, NULL, NULL, '2025-12-21 11:48:47', NULL, NULL, 90000.00, NULL, 12, '2025-12-30', NULL, NULL, 0, NULL, NULL, NULL, 2, '2025-12-21 11:48:47', '127.0.0.1', '$2y$10$51eYWbg4V836DsbE5ZR.Wesa/AWro4Bs2c2rvHN/wE./nhTkHOVhm', 2, '2025-12-21 12:45:21', '2025-12-21 11:45:21', '2025-12-21 12:15:15'),
(25, 7616591, 578063, '7d0847620022fef2c8f8b58daa8c2d29767f088a24ee557fa865030b8190ceae', NULL, 'active', '2026-01-20 12:11:47', 'moshoodkayodeabdul@gmail.com', NULL, 'kayode abdullahi', 993033, '2025-12-21 12:11:59', '2025-12-21 12:12:25', NULL, 2200000.00, NULL, 12, '2025-12-28', NULL, NULL, 0, NULL, NULL, NULL, 1, '2025-12-21 12:11:59', '127.0.0.1', '$2y$10$ZNsEOWP.XgTsXfKZYmHPSeNAL2iEkkhLbSwdmAhnylnkFnS3igEGm', 1, '2025-12-21 13:11:47', '2025-12-21 12:11:47', '2025-12-21 12:12:25'),
(26, 9173307, 993033, 'a25e102e44141b7ce6699e5c405ba4a74ee7b8069686057291e8d831a0d1bfa7', NULL, 'cancelled', '2026-01-20 12:15:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, '$2y$10$6fwaVCL0Ad.U5ITUTJYSLenVz1idqRVc.P4mLk46MZTUR/QFoIH12', 0, '2025-12-21 13:15:15', '2025-12-21 12:15:15', '2025-12-21 12:15:22'),
(27, 9173307, 993033, 'c3ce6310606b118c2eeb8de069ff23d786c1e9849fda107bda33c087161c18fd', NULL, 'cancelled', '2026-01-20 12:15:22', NULL, NULL, NULL, NULL, '2025-12-21 12:15:38', NULL, NULL, 90000.00, NULL, 12, '2025-12-28', NULL, NULL, 1, 'easyrent_invitation', NULL, NULL, 1, '2025-12-21 12:15:38', '127.0.0.1', '$2y$10$JJu4hnI8f6wnIAKFXIylsugUY5lWf.0Ki4deKtokJj2TyTApqbH9S', 1, '2025-12-21 13:15:22', '2025-12-21 12:15:22', '2025-12-21 20:12:55'),
(28, 1157153, 993033, 'fec3f6582cab6cb03f36362153f27f198a9a4307e3969a6de8cdd348757c5fbe', NULL, 'active', '2026-01-20 16:49:30', 'smith@easyrent.africa', '08123435674', 'kenny abdul', 869157, '2025-12-21 17:05:01', '2025-12-21 17:05:31', NULL, 189999.00, NULL, 12, '2025-12-28', '{\"authenticated_user_id\":869157,\"transferred_at\":\"2025-12-21T18:05:01.852743Z\",\"expires_at\":\"2025-12-22T18:05:01.859998Z\"}', NULL, 1, 'easyrent_invitation', NULL, '2025-12-22 17:05:01', 2, '2025-12-21 17:05:01', '127.0.0.1', '$2y$10$BHar6CC7wyk660jUSgSv9.m5aGBqR3qK/PZ19Xmf9q2PDwKusVT..', 2, '2025-12-21 17:49:30', '2025-12-21 16:49:30', '2025-12-21 17:05:31'),
(29, 1159283, 993033, 'fdb35cefdd898042d2b696c2bba91340d5e03859375d455ef3121604288c05c9', NULL, 'active', '2026-01-20 17:13:54', 'kagoor@easyrent.com', NULL, 'kayode abdul', 808169, '2025-12-21 17:16:56', NULL, NULL, 1800000.00, NULL, 12, '2025-12-28', '{\"authenticated_user_id\":808169,\"transferred_at\":\"2025-12-21T18:16:56.769944Z\",\"expires_at\":\"2025-12-22T18:16:56.789504Z\"}', NULL, 1, 'easyrent_invitation', NULL, '2025-12-22 17:16:56', 2, '2025-12-21 17:16:56', '127.0.0.1', '$2y$10$ug2mPPG.yUVeDpYobR0cFecmoMlnUkF33KXWaJkRnBi1t2CMR5uuK', 2, '2025-12-21 18:13:54', '2025-12-21 17:13:54', '2025-12-21 17:16:56'),
(30, 1558236, 993033, '2179be6015f8a31b07660e13af12c21404e0d8cdfcd51423f0592c1b778f36e7', NULL, 'cancelled', '2026-01-20 17:31:28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, '$2y$10$clMRP50F4Xwe7TZvI090tuJ2.WL48tfoP6I/71ZQQeQ7.4.IbA3VW', 0, '2025-12-21 18:31:28', '2025-12-21 17:31:28', '2025-12-21 17:31:45'),
(31, 1558236, 993033, '18ee195ed1a1d0753203edbdc1bc30be84acd9e25769dedd14398a80badcde4d', NULL, 'cancelled', '2026-01-20 17:31:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, '$2y$10$hN9ZG8fPIJdsB0ff1RAB8uxUDaUo4C1Kafpa0cAYljqF4LkCzWceG', 0, '2025-12-21 18:31:45', '2025-12-21 17:31:45', '2025-12-21 17:32:03'),
(32, 1558236, 993033, '8903c84180f60cd602849c43331a4386e32c3e18264835b0e7a239b20a7cc074', NULL, 'cancelled', '2026-01-20 17:32:03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, '$2y$10$PFXj/wa2QdVnNuoCxMUodO4HKcKFHZ5SFuXQwdWTvKSrDaHknhO.6', 0, '2025-12-21 18:32:03', '2025-12-21 17:32:03', '2025-12-21 17:32:38'),
(33, 1558236, 993033, 'b52c493f8e00b14aa69de3ba25cf0e2a668594357ee14b7fce92fccd26250803', NULL, 'cancelled', '2026-01-20 17:32:38', 'smith@easyrent.africa', '08123435674', 'kenny abdul', 869157, '2025-12-21 17:35:19', '2025-12-21 17:35:29', NULL, 1800000.00, NULL, 12, '2025-12-28', NULL, NULL, 0, NULL, NULL, NULL, 2, '2025-12-21 17:35:19', '127.0.0.1', '$2y$10$JewLhRw5iZfgFcG/L3.zMOCxyWhNdHqi8L0mfa83dEn/zpzDEA6US', 2, '2025-12-21 18:32:38', '2025-12-21 17:32:38', '2025-12-21 17:39:12'),
(34, 1558236, 993033, 'a91ed8cf6a62fe0bb04c8c0b15ed18cd5b914c4c037657ea9db9a3d43870971c', NULL, 'cancelled', '2026-01-20 17:39:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, '$2y$10$QFk209CAlzLby164AivjmO5RAt3oseoBjZdZpBbRrJz3260Ws2Tc2', 0, '2025-12-21 18:39:12', '2025-12-21 17:39:13', '2025-12-21 17:39:53'),
(35, 1558236, 993033, '57da843e4934fc3121561acbc3fec010f2ef23e88b5a8f95c2b5adc48eb4ac9c', NULL, 'cancelled', '2026-01-20 17:39:53', 'smith@easyrent.africa', '08123435674', 'kenny abdul', 869157, '2025-12-21 17:40:04', '2025-12-21 17:40:16', NULL, 1800000.00, NULL, 12, '2025-12-28', NULL, NULL, 0, NULL, NULL, NULL, 1, '2025-12-21 17:40:04', '127.0.0.1', '$2y$10$tK2q4YMlqtoyR233.i2UCO8CysIGFqURtHNhUCgpTwyv1TbZdYd/W', 1, '2025-12-21 18:39:53', '2025-12-21 17:39:53', '2025-12-21 17:45:14'),
(36, 1558236, 993033, 'ce09fb0e9ed2dc709d35d4ea69e497adef486375da41c032ecebbad62fa1ac18', NULL, 'cancelled', '2026-01-20 17:45:14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, '$2y$10$ZBPEMTs46gB73ao3uYoEauWFxPQL1a6vW7eo1ULLXJfutfNyuQ1WO', 0, '2025-12-21 18:45:15', '2025-12-21 17:45:15', '2025-12-21 17:45:28'),
(37, 1558236, 993033, '6360a1b7cdbbd7eaadb41bd036727b686d4bba9b2eb3f914493b4bbdf25bd934', NULL, 'cancelled', '2026-01-20 17:45:28', 'smith@easyrent.africa', '08123435674', 'kenny abdul', 869157, '2025-12-21 17:45:40', '2025-12-21 17:45:49', NULL, 1800000.00, NULL, 12, '2025-12-28', NULL, NULL, 0, NULL, NULL, NULL, 1, '2025-12-21 17:45:40', '127.0.0.1', '$2y$10$gYJwKaCWUMXM5/3jiMCf6uS4RDOjEYC4guImFz3Gf9OBeSBz7SR.i', 1, '2025-12-21 18:45:28', '2025-12-21 17:45:28', '2025-12-21 17:54:15'),
(38, 1558236, 993033, '7ea3e4777f45ee59ca8b716134e6a7c6dbaf4c9f0d9939d753b0a76ec33f7579', NULL, 'cancelled', '2026-01-20 17:54:15', NULL, NULL, NULL, NULL, '2025-12-21 17:54:26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 1, '2025-12-21 17:54:26', '127.0.0.1', '$2y$10$XwM.cvKwStJWrXEz8Ms2S.JJHh1w6zxRJNz81PZ8/KiqL80sAKQ3G', 1, '2025-12-21 18:54:15', '2025-12-21 17:54:15', '2025-12-21 17:56:39'),
(39, 1558236, 993033, 'ae7aede5f94afd0b96806a2641e2ae710f65a972c09dcf3d48e23c5a1ffaf2b6', NULL, 'cancelled', '2026-01-20 17:56:39', 'smith@easyrent.africa', '08123435674', 'kenny abdul', 869157, '2025-12-21 17:56:47', '2025-12-21 17:56:53', NULL, 1800000.00, NULL, 12, '2025-12-28', NULL, NULL, 0, NULL, NULL, NULL, 1, '2025-12-21 17:56:47', '127.0.0.1', '$2y$10$0jNgILwT2T4K/5ve4d9Zy.VAQK7x7XIIXJbNwjfQ6klJtwrPe/j5.', 1, '2025-12-21 18:56:39', '2025-12-21 17:56:39', '2025-12-21 17:59:38'),
(40, 1558236, 993033, '6a98a44a35fbb5b99d4f97306343e892dc5479e7c1df878977c46f98a83ef5f0', NULL, 'active', '2026-01-20 17:59:38', 'smith@easyrent.africa', '08123435674', 'kenny abdul', 869157, '2025-12-21 18:01:07', '2025-12-21 18:01:17', NULL, 1800000.00, NULL, 12, '2025-12-28', NULL, NULL, 0, NULL, NULL, NULL, 3, '2025-12-21 18:01:07', '127.0.0.1', '$2y$10$cdztxu1ld9Brio6ggFyEi.W22796b8/bpPtQvA0Kjjms2CeIPhvlq', 3, '2025-12-21 18:59:38', '2025-12-21 17:59:38', '2025-12-21 18:01:17'),
(41, 5936714, 993033, '8bea001ec6288d4dd9ca4aba849a6f259590203e9b711ee92dfb622af147a9e7', NULL, 'active', '2026-01-20 18:08:36', 'smith@easyrent.africa', '08123435674', 'kenny abdul', 869157, '2025-12-21 18:12:18', '2025-12-21 18:10:46', NULL, 900000.00, NULL, 12, '2025-12-28', NULL, NULL, 0, NULL, NULL, NULL, 5, '2025-12-21 18:12:18', '127.0.0.1', '$2y$10$iHNq7Hi9Lo8coA.WOEZnye0ZtdSpTgQcHTtb.GSiS0b0z2s39H9qG', 5, '2025-12-21 19:08:36', '2025-12-21 18:08:36', '2025-12-21 18:12:18'),
(42, 5571100, 993033, '5a3bba64ca2f5a57be31111dc283aecb59f26f02579a72cdc2f65d9a523ce124', NULL, 'active', '2026-01-20 18:18:19', 'smith@easyrent.africa', '08123435674', 'kenny abdul', 869157, '2025-12-21 18:18:28', '2025-12-21 18:18:34', NULL, 1200000.00, NULL, 12, '2025-12-28', NULL, NULL, 0, NULL, NULL, NULL, 1, '2025-12-21 18:18:28', '127.0.0.1', '$2y$10$r5nQqbu.x2E11qJYPdYRI.ZvvN41oT4HaPFcqLhhmUFmeNKESZArK', 1, '2025-12-21 19:18:19', '2025-12-21 18:18:19', '2025-12-21 18:18:34'),
(43, 7826796, 993033, '3ca8fc0f26e8996a60b7b46f4045c851b1f7a7b621fc16f2db01e262eb21e6a0', NULL, 'cancelled', '2026-01-20 18:24:36', NULL, NULL, NULL, NULL, '2025-12-21 18:24:53', NULL, NULL, 900000.00, NULL, 12, '2025-12-28', NULL, NULL, 1, 'easyrent_invitation', NULL, NULL, 1, '2025-12-21 18:24:53', '127.0.0.1', '$2y$10$BLA1Xc3UUYgOS10m/zjb.uq7o5.kpMwjviSQcJ4e4lhjxd/zd6ZGO', 1, '2025-12-21 19:24:36', '2025-12-21 18:24:36', '2025-12-21 20:36:15'),
(44, 9173307, 993033, 'f105412baa03cac0c859d48e0eec74f793e3e978e7dfb84d01a7d22dce9130d9', NULL, 'active', '2026-01-20 20:12:55', 'smith@easyrent.africa', '08123435674', 'kenny abdul', 869157, '2025-12-21 20:24:52', '2025-12-21 20:25:09', NULL, 90000.00, NULL, 12, '2025-12-28', '{\"authenticated_user_id\":869157,\"transferred_at\":\"2025-12-21T21:24:52.699728Z\",\"expires_at\":\"2025-12-22T21:24:52.707438Z\"}', NULL, 1, 'easyrent_invitation', NULL, '2025-12-22 20:24:52', 5, '2025-12-21 20:24:52', '127.0.0.1', '$2y$10$NytFPlyseuQTO9a1yAIbj.7kU9olDZ2FqOolgiYIZFWTqCwqjIzuC', 5, '2025-12-21 21:12:55', '2025-12-21 20:12:55', '2025-12-21 20:25:09'),
(45, 7826796, 993033, '5934cd4a1eca8c5a82d6f1c26042e81f810fb81a06b5940f19057b02dfb0331b', NULL, 'active', '2026-01-20 20:36:15', NULL, NULL, NULL, NULL, '2025-12-21 20:38:14', NULL, NULL, 900000.00, NULL, 12, '2025-12-28', NULL, NULL, 0, NULL, NULL, NULL, 2, '2025-12-21 20:38:14', '127.0.0.1', '$2y$10$.0qmbZ7dRwlTvJJMwGAC4u8X7jdXgcEN2td8L/pSjAuja.heeE2kO', 2, '2025-12-21 21:36:15', '2025-12-21 20:36:15', '2025-12-21 20:38:19');

--
-- Triggers `apartment_invitations`
--
DELIMITER $$
CREATE TRIGGER `cleanup_expired_invitation_session` AFTER UPDATE ON `apartment_invitations` FOR EACH ROW BEGIN
                -- If invitation status changed to expired, clean up session data
                IF NEW.status = "expired" AND OLD.status != "expired" THEN
                    UPDATE apartment_invitations 
                    SET session_data = NULL, 
                        session_expires_at = NULL,
                        updated_at = NOW()
                    WHERE id = NEW.id AND session_data IS NOT NULL;
                END IF;
                
                -- If invitation is used (payment completed), clean up session data
                IF NEW.status = "used" AND OLD.status != "used" THEN
                    UPDATE apartment_invitations 
                    SET session_data = NULL, 
                        session_expires_at = NULL,
                        updated_at = NOW()
                    WHERE id = NEW.id AND session_data IS NOT NULL;
                END IF;
            END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `log_suspicious_invitation_activity` AFTER UPDATE ON `apartment_invitations` FOR EACH ROW BEGIN
                -- Log when rate limit is exceeded
                IF NEW.rate_limit_count >= 50 AND OLD.rate_limit_count < 50 THEN
                    INSERT INTO activity_logs (user_id, action, description, ip_address, created_at, updated_at)
                    VALUES (NULL, "security_alert", 
                           CONCAT("Rate limit exceeded for invitation ID: ", NEW.id, " from IP: ", NEW.last_accessed_ip),
                           NEW.last_accessed_ip, NOW(), NOW());
                END IF;
                
                -- Log when access count is unusually high
                IF NEW.access_count >= 100 AND OLD.access_count < 100 THEN
                    INSERT INTO activity_logs (user_id, action, description, ip_address, created_at, updated_at)
                    VALUES (NULL, "security_alert", 
                           CONCAT("High access count (", NEW.access_count, ") for invitation ID: ", NEW.id),
                           NEW.last_accessed_ip, NOW(), NOW());
                END IF;
            END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_apartment_occupancy` AFTER UPDATE ON `apartment_invitations` FOR EACH ROW BEGIN
                -- When payment is completed, mark apartment as occupied
                IF NEW.payment_completed_at IS NOT NULL AND OLD.payment_completed_at IS NULL THEN
                    UPDATE apartments 
                    SET occupied = 1, 
                        updated_at = NOW()
                    WHERE id = NEW.apartment_id;
                END IF;
            END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `apartment_types`
--

CREATE TABLE `apartment_types` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `apartment_types`
--

INSERT INTO `apartment_types` (`id`, `name`, `category`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Studio', 'residential', 'Single room apartment', 1, '2025-12-16 13:14:05', '2025-12-16 13:14:05');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `audit_type` varchar(255) DEFAULT NULL,
  `reference_type` varchar(255) DEFAULT NULL,
  `reference_id` bigint(20) DEFAULT NULL,
  `audit_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`audit_data`)),
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `model_type` varchar(255) DEFAULT NULL,
  `model_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` text NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(255) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `performed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `audit_type`, `reference_type`, `reference_id`, `audit_data`, `user_id`, `action`, `model_type`, `model_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `performed_at`, `created_at`, `updated_at`) VALUES
(1, 'migration', 'apartments', 7, '{\"property_id\":1,\"apartment_id\":6527215,\"issue_type\":\"wrong_field_reference\"}', NULL, 'orphaned_record_identified', 'App\\Models\\Apartment', 7, 'Orphaned apartment record identified during foreign key migration: wrong_field_reference', '{\"property_id\":1,\"apartment_id\":6527215}', '{\"correct_property_id\":9533782}', NULL, 'Migration Script', '2025-12-17 09:39:50', '2025-12-17 09:39:50', '2025-12-17 09:39:50'),
(2, 'migration', 'apartments', 7, '{\"property_id\":1,\"apartment_id\":6527215,\"issue_type\":\"wrong_field_reference\"}', NULL, 'orphaned_record_identified', 'App\\Models\\Apartment', 7, 'Orphaned apartment record identified during foreign key migration: wrong_field_reference', '{\"property_id\":1,\"apartment_id\":6527215}', '{\"correct_property_id\":9533782}', NULL, 'Migration Script', '2025-12-17 09:39:50', '2025-12-17 09:39:50', '2025-12-17 09:39:50'),
(3, 'migration', 'apartments', 8, '{\"property_id\":1,\"apartment_id\":2505112,\"issue_type\":\"wrong_field_reference\"}', NULL, 'orphaned_record_identified', 'App\\Models\\Apartment', 8, 'Orphaned apartment record identified during foreign key migration: wrong_field_reference', '{\"property_id\":1,\"apartment_id\":2505112}', '{\"correct_property_id\":9533782}', NULL, 'Migration Script', '2025-12-17 09:39:50', '2025-12-17 09:39:50', '2025-12-17 09:39:50'),
(4, 'migration', 'apartments', 8, '{\"property_id\":1,\"apartment_id\":2505112,\"issue_type\":\"wrong_field_reference\"}', NULL, 'orphaned_record_identified', 'App\\Models\\Apartment', 8, 'Orphaned apartment record identified during foreign key migration: wrong_field_reference', '{\"property_id\":1,\"apartment_id\":2505112}', '{\"correct_property_id\":9533782}', NULL, 'Migration Script', '2025-12-17 09:39:50', '2025-12-17 09:39:50', '2025-12-17 09:39:50'),
(5, 'migration', 'apartments', 9, '{\"property_id\":1,\"apartment_id\":5593016,\"issue_type\":\"wrong_field_reference\"}', NULL, 'orphaned_record_identified', 'App\\Models\\Apartment', 9, 'Orphaned apartment record identified during foreign key migration: wrong_field_reference', '{\"property_id\":1,\"apartment_id\":5593016}', '{\"correct_property_id\":9533782}', NULL, 'Migration Script', '2025-12-17 09:39:50', '2025-12-17 09:39:50', '2025-12-17 09:39:50'),
(6, 'migration', 'apartments', 9, '{\"property_id\":1,\"apartment_id\":5593016,\"issue_type\":\"wrong_field_reference\"}', NULL, 'orphaned_record_identified', 'App\\Models\\Apartment', 9, 'Orphaned apartment record identified during foreign key migration: wrong_field_reference', '{\"property_id\":1,\"apartment_id\":5593016}', '{\"correct_property_id\":9533782}', NULL, 'Migration Script', '2025-12-17 09:39:50', '2025-12-17 09:39:50', '2025-12-17 09:39:50'),
(7, 'migration', 'apartments', 1, '{\"property_id\":1416028,\"apartment_id\":7589367,\"issue_type\":\"truly_orphaned\"}', NULL, 'orphaned_record_identified', 'App\\Models\\Apartment', 1, 'Orphaned apartment record identified during foreign key migration: truly_orphaned', '{\"property_id\":1416028,\"apartment_id\":7589367}', NULL, NULL, 'Migration Script', '2025-12-17 09:39:50', '2025-12-17 09:39:50', '2025-12-17 09:39:50'),
(8, 'migration', 'apartments', 2, '{\"property_id\":1416028,\"apartment_id\":4991441,\"issue_type\":\"truly_orphaned\"}', NULL, 'orphaned_record_identified', 'App\\Models\\Apartment', 2, 'Orphaned apartment record identified during foreign key migration: truly_orphaned', '{\"property_id\":1416028,\"apartment_id\":4991441}', NULL, NULL, 'Migration Script', '2025-12-17 09:39:50', '2025-12-17 09:39:50', '2025-12-17 09:39:50'),
(9, 'migration', 'apartments', 3, '{\"property_id\":1416028,\"apartment_id\":7589367,\"issue_type\":\"truly_orphaned\"}', NULL, 'orphaned_record_identified', 'App\\Models\\Apartment', 3, 'Orphaned apartment record identified during foreign key migration: truly_orphaned', '{\"property_id\":1416028,\"apartment_id\":7589367}', NULL, NULL, 'Migration Script', '2025-12-17 09:39:50', '2025-12-17 09:39:50', '2025-12-17 09:39:50'),
(10, 'migration', 'apartments', 4, '{\"property_id\":1416028,\"apartment_id\":4991441,\"issue_type\":\"truly_orphaned\"}', NULL, 'orphaned_record_identified', 'App\\Models\\Apartment', 4, 'Orphaned apartment record identified during foreign key migration: truly_orphaned', '{\"property_id\":1416028,\"apartment_id\":4991441}', NULL, NULL, 'Migration Script', '2025-12-17 09:39:50', '2025-12-17 09:39:50', '2025-12-17 09:39:50'),
(11, 'migration', 'apartments', 7, '{\"old_property_id\":1,\"new_property_id\":9533782}', NULL, 'property_id_corrected', 'App\\Models\\Apartment', 7, 'Corrected apartment property_id from 1 to 9533782', '{\"property_id\":1}', '{\"property_id\":9533782}', NULL, 'Migration Script', '2025-12-17 09:39:50', '2025-12-17 09:39:50', '2025-12-17 09:39:50'),
(12, 'migration', 'apartments', 7, '{\"old_property_id\":1,\"new_property_id\":9533782}', NULL, 'property_id_corrected', 'App\\Models\\Apartment', 7, 'Corrected apartment property_id from 1 to 9533782', '{\"property_id\":1}', '{\"property_id\":9533782}', NULL, 'Migration Script', '2025-12-17 09:39:50', '2025-12-17 09:39:50', '2025-12-17 09:39:50'),
(13, 'migration', 'apartments', 8, '{\"old_property_id\":1,\"new_property_id\":9533782}', NULL, 'property_id_corrected', 'App\\Models\\Apartment', 8, 'Corrected apartment property_id from 1 to 9533782', '{\"property_id\":1}', '{\"property_id\":9533782}', NULL, 'Migration Script', '2025-12-17 09:39:50', '2025-12-17 09:39:50', '2025-12-17 09:39:50'),
(14, 'migration', 'apartments', 8, '{\"old_property_id\":1,\"new_property_id\":9533782}', NULL, 'property_id_corrected', 'App\\Models\\Apartment', 8, 'Corrected apartment property_id from 1 to 9533782', '{\"property_id\":1}', '{\"property_id\":9533782}', NULL, 'Migration Script', '2025-12-17 09:39:50', '2025-12-17 09:39:50', '2025-12-17 09:39:50'),
(15, 'migration', 'apartments', 9, '{\"old_property_id\":1,\"new_property_id\":9533782}', NULL, 'property_id_corrected', 'App\\Models\\Apartment', 9, 'Corrected apartment property_id from 1 to 9533782', '{\"property_id\":1}', '{\"property_id\":9533782}', NULL, 'Migration Script', '2025-12-17 09:39:50', '2025-12-17 09:39:50', '2025-12-17 09:39:50'),
(16, 'migration', 'apartments', 9, '{\"old_property_id\":1,\"new_property_id\":9533782}', NULL, 'property_id_corrected', 'App\\Models\\Apartment', 9, 'Corrected apartment property_id from 1 to 9533782', '{\"property_id\":1}', '{\"property_id\":9533782}', NULL, 'Migration Script', '2025-12-17 09:39:50', '2025-12-17 09:39:50', '2025-12-17 09:39:50'),
(17, 'migration', 'apartments', 1, '{\"property_id\":1416028,\"apartment_id\":7589367,\"reason\":\"truly_orphaned\"}', NULL, 'orphaned_record_deleted', 'App\\Models\\Apartment', 1, 'Deleted orphaned apartment 1 with invalid property_id 1416028', '{\"property_id\":1416028,\"apartment_id\":7589367}', NULL, NULL, 'Migration Script', '2025-12-17 09:39:50', '2025-12-17 09:39:50', '2025-12-17 09:39:50'),
(18, 'migration', 'apartments', 2, '{\"property_id\":1416028,\"apartment_id\":4991441,\"reason\":\"truly_orphaned\"}', NULL, 'orphaned_record_deleted', 'App\\Models\\Apartment', 2, 'Deleted orphaned apartment 2 with invalid property_id 1416028', '{\"property_id\":1416028,\"apartment_id\":4991441}', NULL, NULL, 'Migration Script', '2025-12-17 09:39:50', '2025-12-17 09:39:50', '2025-12-17 09:39:50'),
(19, 'migration', 'apartments', 3, '{\"property_id\":1416028,\"apartment_id\":7589367,\"reason\":\"truly_orphaned\"}', NULL, 'orphaned_record_deleted', 'App\\Models\\Apartment', 3, 'Deleted orphaned apartment 3 with invalid property_id 1416028', '{\"property_id\":1416028,\"apartment_id\":7589367}', NULL, NULL, 'Migration Script', '2025-12-17 09:39:50', '2025-12-17 09:39:50', '2025-12-17 09:39:50'),
(20, 'migration', 'apartments', 4, '{\"property_id\":1416028,\"apartment_id\":4991441,\"reason\":\"truly_orphaned\"}', NULL, 'orphaned_record_deleted', 'App\\Models\\Apartment', 4, 'Deleted orphaned apartment 4 with invalid property_id 1416028', '{\"property_id\":1416028,\"apartment_id\":4991441}', NULL, NULL, 'Migration Script', '2025-12-17 09:39:50', '2025-12-17 09:39:50', '2025-12-17 09:39:50');

-- --------------------------------------------------------

--
-- Table structure for table `benefactors`
--

CREATE TABLE `benefactors` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `relationship_type` enum('employer','parent','guardian','sponsor','organization','other') NOT NULL DEFAULT 'other',
  `type` enum('registered','guest') NOT NULL DEFAULT 'guest',
  `is_registered` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `benefactor_payments`
--

CREATE TABLE `benefactor_payments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `benefactor_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `property_id` bigint(20) UNSIGNED DEFAULT NULL,
  `apartment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `proforma_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_type` enum('one_time','recurring') NOT NULL DEFAULT 'one_time',
  `status` enum('pending','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
  `is_paused` tinyint(1) NOT NULL DEFAULT 0,
  `frequency` enum('monthly','quarterly','annually') DEFAULT NULL,
  `next_payment_date` date DEFAULT NULL,
  `payment_day_of_month` int(11) DEFAULT NULL COMMENT 'Day of month for recurring payments (1-31)',
  `payment_reference` varchar(255) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `payment_metadata` text DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `paused_at` timestamp NULL DEFAULT NULL,
  `pause_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog`
--

CREATE TABLE `blog` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `topic` varchar(255) NOT NULL,
  `topic_url` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `excerpt` text DEFAULT NULL,
  `cover_photo` varchar(255) DEFAULT NULL,
  `author` varchar(255) NOT NULL DEFAULT 'Admin',
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `hide` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commission_payments`
--

CREATE TABLE `commission_payments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketer_id` bigint(20) UNSIGNED NOT NULL,
  `referral_chain_id` bigint(20) UNSIGNED DEFAULT NULL,
  `commission_tier` enum('super_marketer','marketer','regional_manager') NOT NULL,
  `parent_payment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `regional_rate_applied` decimal(5,4) NOT NULL,
  `region` varchar(100) DEFAULT NULL,
  `payment_reference` varchar(100) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('bank_transfer','mobile_money','check') NOT NULL DEFAULT 'bank_transfer',
  `payment_status` enum('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
  `payment_date` timestamp NULL DEFAULT NULL,
  `processed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `referral_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`referral_ids`)),
  `payment_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_details`)),
  `notes` text DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `scheduled_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `processing_started_at` timestamp NULL DEFAULT NULL,
  `processing_time_minutes` decimal(8,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commission_rates`
--

CREATE TABLE `commission_rates` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `region` varchar(100) NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `commission_percentage` decimal(5,4) NOT NULL,
  `property_management_status` enum('managed','unmanaged') NOT NULL DEFAULT 'unmanaged',
  `hierarchy_status` enum('with_super_marketer','without_super_marketer') NOT NULL DEFAULT 'without_super_marketer',
  `super_marketer_rate` decimal(5,3) DEFAULT NULL,
  `marketer_rate` decimal(5,3) DEFAULT NULL,
  `regional_manager_rate` decimal(5,3) DEFAULT NULL,
  `company_rate` decimal(5,3) DEFAULT NULL,
  `total_commission_rate` decimal(5,3) NOT NULL DEFAULT 0.000,
  `description` varchar(255) DEFAULT NULL,
  `effective_from` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `effective_until` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `last_updated_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `commission_rates`
--

INSERT INTO `commission_rates` (`id`, `region`, `role_id`, `commission_percentage`, `property_management_status`, `hierarchy_status`, `super_marketer_rate`, `marketer_rate`, `regional_manager_rate`, `company_rate`, `total_commission_rate`, `description`, `effective_from`, `effective_until`, `created_by`, `updated_by`, `last_updated_at`, `is_active`, `created_at`, `updated_at`) VALUES
(3, 'default', 1, 5.0000, 'unmanaged', 'without_super_marketer', NULL, NULL, NULL, NULL, 0.000, NULL, '2025-12-07 02:59:38', NULL, 340336, NULL, NULL, 1, '2025-09-15 13:44:14', '2025-12-07 01:59:38'),
(4, 'default', 1, 5.0000, 'unmanaged', 'without_super_marketer', NULL, NULL, NULL, NULL, 0.000, NULL, '2025-12-07 02:59:38', NULL, 340336, NULL, NULL, 1, '2025-09-15 13:44:14', '2025-12-07 01:59:38'),
(5, 'default', 1, 2.5000, 'unmanaged', 'without_super_marketer', NULL, NULL, NULL, NULL, 0.000, NULL, '2025-12-07 02:59:38', NULL, 340336, NULL, NULL, 1, '2025-09-15 13:44:14', '2025-12-07 01:59:38'),
(6, 'default', 1, 2.5000, 'unmanaged', 'without_super_marketer', NULL, NULL, NULL, NULL, 0.000, NULL, '2025-12-07 02:59:38', NULL, 340336, NULL, NULL, 1, '2025-09-15 13:44:14', '2025-12-07 01:59:38'),
(7, 'lagos', 1, 5.0000, 'unmanaged', 'without_super_marketer', NULL, NULL, NULL, NULL, 0.000, NULL, '2025-12-07 02:59:38', NULL, 340336, NULL, NULL, 1, '2025-09-15 13:44:14', '2025-12-07 01:59:38'),
(8, 'lagos', 1, 5.0000, 'unmanaged', 'without_super_marketer', NULL, NULL, NULL, NULL, 0.000, NULL, '2025-12-07 02:59:38', NULL, 340336, NULL, NULL, 1, '2025-09-15 13:44:14', '2025-12-07 01:59:38'),
(9, 'lagos', 1, 2.5000, 'unmanaged', 'without_super_marketer', NULL, NULL, NULL, NULL, 0.000, NULL, '2025-12-07 02:59:38', NULL, 340336, NULL, NULL, 1, '2025-09-15 13:44:14', '2025-12-07 01:59:38'),
(10, 'lagos', 1, 2.5000, 'unmanaged', 'without_super_marketer', NULL, NULL, NULL, NULL, 0.000, NULL, '2025-12-07 02:59:38', NULL, 340336, NULL, NULL, 1, '2025-09-15 13:44:14', '2025-12-07 01:59:38'),
(11, 'abuja', 1, 5.0000, 'unmanaged', 'without_super_marketer', NULL, NULL, NULL, NULL, 0.000, NULL, '2025-12-07 02:59:38', NULL, 340336, NULL, NULL, 1, '2025-09-15 13:44:14', '2025-12-07 01:59:38'),
(12, 'abuja', 1, 5.0000, 'unmanaged', 'without_super_marketer', NULL, NULL, NULL, NULL, 0.000, NULL, '2025-12-07 02:59:38', NULL, 340336, NULL, NULL, 1, '2025-09-15 13:44:14', '2025-12-07 01:59:38'),
(13, 'abuja', 1, 2.5000, 'unmanaged', 'without_super_marketer', NULL, NULL, NULL, NULL, 0.000, NULL, '2025-12-07 02:59:38', NULL, 340336, NULL, NULL, 1, '2025-09-15 13:44:14', '2025-12-07 01:59:38'),
(14, 'abuja', 1, 2.5000, 'unmanaged', 'without_super_marketer', NULL, NULL, NULL, NULL, 0.000, NULL, '2025-12-07 02:59:38', NULL, 340336, NULL, NULL, 1, '2025-09-15 13:44:14', '2025-12-07 01:59:38'),
(15, 'kano', 1, 5.0000, 'unmanaged', 'without_super_marketer', NULL, NULL, NULL, NULL, 0.000, NULL, '2025-12-07 02:59:38', NULL, 340336, NULL, NULL, 1, '2025-09-15 13:44:14', '2025-12-07 01:59:38'),
(16, 'kano', 1, 5.0000, 'unmanaged', 'without_super_marketer', NULL, NULL, NULL, NULL, 0.000, NULL, '2025-12-07 02:59:38', NULL, 340336, NULL, NULL, 1, '2025-09-15 13:44:14', '2025-12-07 01:59:38'),
(17, 'kano', 1, 2.5000, 'unmanaged', 'without_super_marketer', NULL, NULL, NULL, NULL, 0.000, NULL, '2025-12-07 02:59:38', NULL, 340336, NULL, NULL, 1, '2025-09-15 13:44:14', '2025-12-07 01:59:38'),
(18, 'kano', 1, 2.5000, 'unmanaged', 'without_super_marketer', NULL, NULL, NULL, NULL, 0.000, NULL, '2025-12-07 02:59:38', NULL, 340336, NULL, NULL, 1, '2025-09-15 13:44:14', '2025-12-07 01:59:38'),
(19, 'port_harcourt', 1, 5.0000, 'unmanaged', 'without_super_marketer', NULL, NULL, NULL, NULL, 0.000, NULL, '2025-12-07 02:59:38', NULL, 340336, NULL, NULL, 1, '2025-09-15 13:44:14', '2025-12-07 01:59:38'),
(20, 'port_harcourt', 1, 5.0000, 'unmanaged', 'without_super_marketer', NULL, NULL, NULL, NULL, 0.000, NULL, '2025-12-07 02:59:38', NULL, 340336, NULL, NULL, 1, '2025-09-15 13:44:14', '2025-12-07 01:59:38'),
(21, 'port_harcourt', 1, 2.5000, 'unmanaged', 'without_super_marketer', NULL, NULL, NULL, NULL, 0.000, NULL, '2025-12-07 02:59:38', NULL, 340336, NULL, NULL, 1, '2025-09-15 13:44:14', '2025-12-07 01:59:38'),
(22, 'port_harcourt', 1, 2.5000, 'unmanaged', 'without_super_marketer', NULL, NULL, NULL, NULL, 0.000, NULL, '2025-12-07 02:59:38', NULL, 340336, NULL, NULL, 1, '2025-09-15 13:44:14', '2025-12-07 01:59:38'),
(23, 'ibadan', 1, 5.0000, 'unmanaged', 'without_super_marketer', NULL, NULL, NULL, NULL, 0.000, NULL, '2025-12-07 02:59:38', NULL, 340336, NULL, NULL, 1, '2025-09-15 13:44:14', '2025-12-07 01:59:38'),
(24, 'ibadan', 1, 5.0000, 'unmanaged', 'without_super_marketer', NULL, NULL, NULL, NULL, 0.000, NULL, '2025-12-07 02:59:38', NULL, 340336, NULL, NULL, 1, '2025-09-15 13:44:14', '2025-12-07 01:59:38'),
(25, 'ibadan', 1, 2.5000, 'unmanaged', 'without_super_marketer', NULL, NULL, NULL, NULL, 0.000, NULL, '2025-12-07 02:59:38', NULL, 340336, NULL, NULL, 1, '2025-09-15 13:44:14', '2025-12-07 01:59:38'),
(26, 'ibadan', 1, 2.5000, 'unmanaged', 'without_super_marketer', NULL, NULL, NULL, NULL, 0.000, NULL, '2025-12-07 02:59:38', NULL, 340336, NULL, NULL, 1, '2025-09-15 13:44:14', '2025-12-07 01:59:38');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `complaint_number` varchar(20) NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `landlord_id` bigint(20) UNSIGNED NOT NULL,
  `apartment_id` bigint(20) UNSIGNED NOT NULL,
  `property_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `status` enum('open','in_progress','resolved','closed','escalated') NOT NULL DEFAULT 'open',
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `resolution_notes` text DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `complaint_number`, `tenant_id`, `landlord_id`, `apartment_id`, `property_id`, `category_id`, `title`, `description`, `priority`, `status`, `assigned_to`, `resolution_notes`, `resolved_at`, `resolved_by`, `metadata`, `created_at`, `updated_at`) VALUES
(1, 'CMP-2025-0001', 869157, 993033, 1314527, 9533782, 2, 'broken toilet', 'the toilet is licking and it has a malfunctioning flush', 'urgent', 'open', NULL, NULL, NULL, NULL, NULL, '2025-12-17 15:01:12', '2025-12-17 15:01:12');

-- --------------------------------------------------------

--
-- Table structure for table `complaint_attachments`
--

CREATE TABLE `complaint_attachments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `complaint_id` bigint(20) UNSIGNED NOT NULL,
  `uploaded_by` bigint(20) UNSIGNED NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_size` int(10) UNSIGNED NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_hash` varchar(64) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaint_categories`
--

CREATE TABLE `complaint_categories` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `priority_level` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `estimated_resolution_hours` int(11) NOT NULL DEFAULT 24,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `complaint_categories`
--

INSERT INTO `complaint_categories` (`id`, `name`, `description`, `icon`, `priority_level`, `estimated_resolution_hours`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Electrical Issues', 'Power outages, faulty wiring, electrical appliance problems', 'nc-icon nc-bulb-63', 'high', 12, 1, '2025-12-17 08:47:56', '2025-12-17 08:47:56'),
(2, 'Plumbing Problems', 'Leaks, blocked drains, water pressure issues, toilet problems', 'nc-icon nc-tap-01', 'high', 8, 1, '2025-12-17 08:47:56', '2025-12-17 08:47:56'),
(3, 'Heating/Cooling', 'Air conditioning, heating system, ventilation issues', 'nc-icon nc-air-baloon', 'medium', 24, 1, '2025-12-17 08:47:56', '2025-12-17 08:47:56'),
(4, 'Security Concerns', 'Broken locks, security system issues, safety concerns', 'nc-icon nc-lock-circle-open', 'urgent', 4, 1, '2025-12-17 08:47:56', '2025-12-17 08:47:56'),
(5, 'Noise Complaints', 'Excessive noise from neighbors or external sources', 'nc-icon nc-sound-wave', 'medium', 48, 1, '2025-12-17 08:47:56', '2025-12-17 08:47:56'),
(6, 'Maintenance Request', 'General maintenance, repairs, and upkeep requests', 'nc-icon nc-settings-tool-66', 'low', 72, 1, '2025-12-17 08:47:56', '2025-12-17 08:47:56'),
(7, 'Pest Control', 'Insects, rodents, or other pest-related issues', 'nc-icon nc-bug-2', 'medium', 24, 1, '2025-12-17 08:47:56', '2025-12-17 08:47:56'),
(8, 'Appliance Issues', 'Refrigerator, washing machine, oven, and other appliance problems', 'nc-icon nc-tv-2', 'medium', 48, 1, '2025-12-17 08:47:56', '2025-12-17 08:47:56'),
(9, 'Structural Problems', 'Cracks, leaks, foundation issues, structural damage', 'nc-icon nc-istanbul', 'urgent', 6, 1, '2025-12-17 08:47:56', '2025-12-17 08:47:56'),
(10, 'Cleanliness Issues', 'Common area cleanliness, garbage collection, sanitation', 'nc-icon nc-basket', 'low', 24, 1, '2025-12-17 08:47:56', '2025-12-17 08:47:56'),
(11, 'Internet/Utilities', 'Internet connectivity, cable TV, utility service issues', 'nc-icon nc-wifi-router', 'medium', 48, 1, '2025-12-17 08:47:56', '2025-12-17 08:47:56'),
(12, 'Other', 'Any other issues not covered by the above categories', 'nc-icon nc-chat-33', 'low', 48, 1, '2025-12-17 08:47:56', '2025-12-17 08:47:56');

-- --------------------------------------------------------

--
-- Table structure for table `complaint_updates`
--

CREATE TABLE `complaint_updates` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `complaint_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `update_type` enum('comment','status_change','assignment','escalation','priority_change') NOT NULL DEFAULT 'comment',
  `message` text NOT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  `is_internal` tinyint(1) NOT NULL DEFAULT 0,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `complaint_updates`
--

INSERT INTO `complaint_updates` (`id`, `complaint_id`, `user_id`, `update_type`, `message`, `old_value`, `new_value`, `is_internal`, `metadata`, `created_at`, `updated_at`) VALUES
(1, 1, 993033, 'status_change', 'Status changed from open to open', 'open', 'open', 0, NULL, '2025-12-18 01:09:55', '2025-12-18 01:09:55'),
(2, 1, 993033, 'comment', 'i dont understand, i bought the toilet new and few months that you moved in, you are saying the toilet is licking and bad flush. I dont have money for that', NULL, NULL, 0, NULL, '2025-12-19 08:58:47', '2025-12-19 08:58:47');

-- --------------------------------------------------------

--
-- Table structure for table `database_maintenance_logs`
--

CREATE TABLE `database_maintenance_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `operation_type` varchar(50) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `description` text NOT NULL,
  `operation_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`operation_details`)),
  `records_affected` int(11) NOT NULL DEFAULT 0,
  `execution_time_seconds` decimal(8,3) DEFAULT NULL,
  `status` enum('started','completed','failed','cancelled') NOT NULL DEFAULT 'started',
  `error_message` text DEFAULT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `database_maintenance_logs`
--

INSERT INTO `database_maintenance_logs` (`id`, `operation_type`, `table_name`, `description`, `operation_details`, `records_affected`, `execution_time_seconds`, `status`, `error_message`, `started_at`, `completed_at`, `created_at`, `updated_at`) VALUES
(1, 'schema_optimization', 'apartment_invitations', 'Initial database schema optimization for EasyRent Link Authentication System', '{\"indexes_added\":7,\"constraints_added\":6,\"triggers_added\":3,\"procedures_added\":3,\"views_added\":2}', 0, NULL, 'completed', NULL, '2025-12-16 13:14:04', '2025-12-16 13:14:04', '2025-12-16 13:14:04', '2025-12-16 13:14:04'),
(1, 'schema_optimization', 'apartment_invitations', 'Initial database schema optimization for EasyRent Link Authentication System', '{\"indexes_added\":7,\"constraints_added\":6,\"triggers_added\":3,\"procedures_added\":3,\"views_added\":2}', 0, NULL, 'completed', NULL, '2025-12-16 13:14:04', '2025-12-16 13:14:04', '2025-12-16 13:14:04', '2025-12-16 13:14:04');

-- --------------------------------------------------------

--
-- Table structure for table `durations`
--

CREATE TABLE `durations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `duration_months` decimal(8,4) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `display_format` varchar(50) DEFAULT NULL,
  `calculation_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`calculation_rules`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `durations`
--

INSERT INTO `durations` (`id`, `code`, `name`, `description`, `duration_months`, `is_active`, `sort_order`, `display_format`, `calculation_rules`, `created_at`, `updated_at`) VALUES
(1, 'hourly', 'Hourly', 'Per hour rental', 0.0400, 1, 1, 'per hour', '{\"multiplier\":1,\"base_type\":\"hourly\"}', '2025-12-21 08:24:00', '2025-12-21 08:24:00'),
(2, 'daily', 'Daily', 'Per day rental', 0.0300, 1, 2, 'per day', '{\"multiplier\":1,\"base_type\":\"daily\"}', '2025-12-21 08:24:00', '2025-12-21 08:24:00'),
(3, 'weekly', 'Weekly', 'Per week rental', 0.2500, 1, 3, 'per week', '{\"multiplier\":7,\"base_type\":\"daily\"}', '2025-12-21 08:24:01', '2025-12-21 08:24:01'),
(4, 'monthly', 'Monthly', 'Per month rental', 1.0000, 1, 4, 'per month', '{\"multiplier\":1,\"base_type\":\"monthly\"}', '2025-12-21 08:24:01', '2025-12-21 08:24:01'),
(5, 'quarterly', 'Quarterly', 'Per quarter (3 months) rental', 3.0000, 1, 5, 'per quarter', '{\"multiplier\":3,\"base_type\":\"monthly\"}', '2025-12-21 08:24:01', '2025-12-21 08:24:01'),
(6, 'semi_annually', 'Semi-Annual', 'Per 6 months rental', 6.0000, 1, 6, 'per 6 months', '{\"multiplier\":6,\"base_type\":\"monthly\"}', '2025-12-21 08:24:01', '2025-12-21 08:24:01'),
(7, 'annually', 'Annual', 'Per year rental', 12.0000, 1, 7, 'per year', '{\"multiplier\":12,\"base_type\":\"monthly\"}', '2025-12-21 08:24:01', '2025-12-21 08:24:01'),
(8, 'bi_annually', 'Bi-Annual', 'Per 24 months rental', 24.0000, 1, 8, 'per 24 months', '{\"multiplier\":24,\"base_type\":\"monthly\"}', '2025-12-21 08:24:01', '2025-12-21 08:24:01');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `invitation_analytics`
-- (See below for the actual view)
--
CREATE TABLE `invitation_analytics` (
`date` date
,`total_created` bigint(21)
,`total_used` bigint(21)
,`total_expired` bigint(21)
,`total_viewed` bigint(21)
,`total_payment_initiated` bigint(21)
,`total_payment_completed` bigint(21)
,`avg_access_count` decimal(14,4)
,`max_access_count` int(11)
);

-- --------------------------------------------------------

--
-- Table structure for table `invitation_analytics_cache`
--

CREATE TABLE `invitation_analytics_cache` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cache_date` date NOT NULL,
  `metric_type` varchar(50) NOT NULL,
  `analytics_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`analytics_data`)),
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `invitation_conversion_funnel`
-- (See below for the actual view)
--
CREATE TABLE `invitation_conversion_funnel` (
`landlord_id` bigint(20) unsigned
,`landlord_name` varchar(255)
,`landlord_email` varchar(255)
,`total_invitations` bigint(21)
,`viewed_invitations` bigint(21)
,`payment_initiated` bigint(21)
,`payment_completed` bigint(21)
,`view_rate_percent` decimal(26,2)
,`initiation_rate_percent` decimal(26,2)
,`completion_rate_percent` decimal(26,2)
,`overall_conversion_rate` decimal(26,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `invitation_performance_metrics`
--

CREATE TABLE `invitation_performance_metrics` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `metric_date` date NOT NULL,
  `total_invitations_created` int(11) NOT NULL DEFAULT 0,
  `total_invitations_viewed` int(11) NOT NULL DEFAULT 0,
  `total_payments_initiated` int(11) NOT NULL DEFAULT 0,
  `total_payments_completed` int(11) NOT NULL DEFAULT 0,
  `total_sessions_created` int(11) NOT NULL DEFAULT 0,
  `total_sessions_expired` int(11) NOT NULL DEFAULT 0,
  `total_rate_limit_hits` int(11) NOT NULL DEFAULT 0,
  `total_security_blocks` int(11) NOT NULL DEFAULT 0,
  `avg_access_count` decimal(8,2) NOT NULL DEFAULT 0.00,
  `conversion_rate_view_to_payment` decimal(5,2) NOT NULL DEFAULT 0.00,
  `conversion_rate_initiate_to_complete` decimal(5,2) NOT NULL DEFAULT 0.00,
  `avg_session_duration_minutes` int(11) NOT NULL DEFAULT 0,
  `hourly_distribution` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`hourly_distribution`)),
  `top_accessing_ips` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`top_accessing_ips`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `invitation_security_monitoring`
-- (See below for the actual view)
--
CREATE TABLE `invitation_security_monitoring` (
`id` int(20)
,`invitation_token` varchar(64)
,`apartment_id` bigint(20) unsigned
,`access_count` int(11)
,`rate_limit_count` int(11)
,`last_accessed_ip` varchar(45)
,`last_accessed_at` timestamp
,`created_at` timestamp
,`security_status` varchar(18)
,`hours_since_creation` bigint(21)
,`minutes_since_last_access` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `landlord_invitation_dashboard`
-- (See below for the actual view)
--
CREATE TABLE `landlord_invitation_dashboard` (
`landlord_id` bigint(20) unsigned
,`landlord_name` varchar(255)
,`landlord_email` varchar(255)
,`total_invitations` bigint(21)
,`active_invitations` bigint(21)
,`used_invitations` bigint(21)
,`expired_invitations` bigint(21)
,`viewed_invitations` bigint(21)
,`completed_payments` bigint(21)
,`total_revenue` decimal(34,2)
,`avg_access_count` decimal(14,4)
,`last_invitation_created` timestamp
,`last_activity` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `marketer_profiles`
--

CREATE TABLE `marketer_profiles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `business_name` varchar(255) DEFAULT NULL,
  `business_type` varchar(100) DEFAULT NULL,
  `years_of_experience` int(11) DEFAULT NULL,
  `preferred_commission_rate` decimal(5,2) DEFAULT NULL,
  `marketing_channels` text DEFAULT NULL,
  `target_regions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`target_regions`)),
  `kyc_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `kyc_documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`kyc_documents`)),
  `bio` text DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `social_media_handles` varchar(255) DEFAULT NULL,
  `total_referrals` int(11) NOT NULL DEFAULT 0,
  `total_commission_earned` decimal(10,2) NOT NULL DEFAULT 0.00,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sender_id` bigint(20) UNSIGNED NOT NULL,
  `receiver_id` bigint(20) UNSIGNED NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `body` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `subject`, `body`, `is_read`, `created_at`, `updated_at`) VALUES
(1, 993033, 869157, 'Rent Proforma', 'A new proforma receipt has been sent to you by kayoux.\n\nDuration: 12 months\n\nYou can  <a class=\"btn btn-primary\" href=\"http://127.0.0.1:8000/proforma/view/2\">view the proforma</a>', 1, '2025-12-17 13:30:31', '2025-12-17 13:30:43'),
(2, 993033, 869157, 'Rent Proforma', 'A new proforma receipt has been sent to you by kayoux.\n\nDuration: 12 months\n\nYou can  <a class=\"btn btn-primary\" href=\"http://127.0.0.1:8000/proforma/view/2\">view the proforma</a>', 1, '2025-12-17 14:42:29', '2025-12-17 14:43:11'),
(3, 869157, 993033, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 1, '2025-12-19 09:48:29', '2025-12-19 22:01:47'),
(4, 869157, 993033, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 1, '2025-12-19 09:49:56', '2025-12-19 21:47:49');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2023_08_28_000000_create_role_change_notifications_table', 1),
(6, '2024_01_09_000000_create_properties_table', 1),
(7, '2024_01_09_000001_create_amenities_table', 1),
(8, '2024_01_09_000003_create_reviews_table', 1),
(9, '2024_01_09_000004_create_apartments_table', 1),
(10, '2024_01_09_000005_create_property_amenity_table', 1),
(11, '2025_06_02_000000_create_profoma_receipts_table', 1),
(12, '2025_06_10_000001_add_duration_to_profoma_receipt_table', 1),
(13, '2025_06_13_100000_create_activity_logs_table', 1),
(14, '2025_06_20_000000_create_messages_table', 1),
(15, '2025_06_30_152936_create_referrals_table', 1),
(16, '2025_07_01_000000_create_agent_ratings_table', 1),
(17, '2025_07_10_000000_create_payments_table', 1),
(18, '2025_07_22_092855_create_audit_logs_table', 1),
(19, '2025_07_22_153857_enhance_users_table_for_marketers', 1),
(20, '2025_07_22_153925_enhance_referrals_table_for_tracking', 1),
(21, '2025_07_22_154316_create_marketer_profiles_table', 1),
(22, '2025_07_22_154345_create_referral_campaigns_table', 1),
(23, '2025_07_22_154543_create_referral_rewards_table', 1),
(24, '2025_07_22_154803_create_commission_payments_table', 1),
(25, '2025_07_31_120000_create_role_user_table', 1),
(26, '2025_07_31_130000_add_clicks_to_referral_campaigns_table', 1),
(27, '2025_07_31_130000_add_reward_level_to_referral_rewards_table', 1),
(28, '2025_07_31_140000_add_conversions_to_referral_campaigns_table', 1),
(29, '2025_08_05_000000_add_role_and_region_to_users_table', 1),
(30, '2025_08_05_000000_create_roles_and_role_user_tables', 1),
(31, '2025_08_05_100000_add_region_to_users_table', 1),
(32, '2025_08_05_160731_remove_role_from_users_table', 1),
(33, '2025_08_13_000001_add_details_to_profoma_receipts_table', 1),
(34, '2025_08_19_000000_fix_role_user_table_user_id_reference', 1),
(35, '2025_08_26_130153_add_suspension_fields_to_properties_table', 1),
(36, '2025_08_26_130505_add_suspension_fields_to_properties_table', 1),
(37, '2025_08_26_135000_add_property_id_to_referrals_table', 1),
(38, '2025_08_26_163405_add_property_id_to_referrals_table', 1),
(39, '2025_08_26_163803_add_property_id_to_referrals_table', 1),
(40, '2025_08_27_000000_fix_role_user_user_id_data_type', 1),
(41, '2025_08_27_100000_fix_role_user_user_id_data_type_safely', 1),
(42, '2025_08_27_165303_create_roles_table', 1),
(43, '2025_08_27_165404_create_role_user_table', 1),
(44, '2025_08_28_000000_create_role_change_notifications_table', 1),
(45, '2025_08_29_133403_create_role_user_table_if_not_exists', 1),
(46, '2025_08_29_151200_alter_roles_add_missing_columns_if_not_exists', 1),
(47, '2025_09_02_100000_create_regional_scopes_table', 1),
(48, '2025_09_02_170500_alter_role_user_add_missing_columns_if_not_exists', 1),
(49, '2025_09_09_000000_create_regional_scopes_table', 1),
(50, '2025_09_10_000001_add_reward_meta_columns', 1),
(51, '2025_09_10_120000_add_status_columns_to_properties_table', 1),
(52, '2025_09_10_123000_create_regional_scopes_table', 1),
(53, '2025_09_12_153148_create_payment_tracking_table', 1),
(54, '2025_09_12_153307_add_processing_time_fields_to_commission_payments', 1),
(55, '2025_09_12_153451_add_fraud_detection_fields_to_users', 1),
(56, '2025_09_12_153529_add_flagged_field_to_referrals', 1),
(57, '2025_09_12_154759_add_monitoring_fields_to_audit_logs', 1),
(58, '2025_09_12_154850_create_performance_logs_table', 1),
(59, '2025_09_15_162433_update_commission_rates_for_property_management_structure', 1),
(60, '2025_09_17_000001_fix_messages_id_auto_increment', 1),
(61, '2025_09_19_000000_create_role_assignment_audits_table', 1),
(62, '2025_10_02_083941_add_amount_to_profoma_receipt_table', 1),
(63, '2025_10_09_123900_alter_payments_id_auto_increment', 1),
(64, '2025_11_09_000001_create_commission_rates_table', 1),
(65, '2025_11_09_000002_add_super_marketer_role', 1),
(66, '2025_11_09_000003_extend_referrals_table_for_hierarchy', 1),
(67, '2025_11_09_000004_create_referral_chains_table', 1),
(68, '2025_11_09_000005_extend_commission_payments_for_hierarchy', 1),
(69, '2025_11_09_000006_add_fraud_detection_fields', 1),
(70, '2025_11_09_000007_fix_referrals_primary_key', 1),
(71, '2025_11_11_161325_create_blog_table', 1),
(72, '2025_11_17_140322_create_benefactors_table', 1),
(73, '2025_11_17_140345_create_benefactor_payments_table', 1),
(74, '2025_11_17_140418_create_payment_invitations_table', 1),
(75, '2025_11_18_140304_add_phase1_features_to_benefactor_tables', 1),
(76, '2025_11_18_163107_add_proforma_link_to_payment_invitations_and_payments', 1),
(77, '2025_11_19_161940_fix_payment_invitations_foreign_keys', 1),
(78, '2025_11_20_144128_fix_benefactors_user_id_foreign_key', 1),
(79, '2025_11_25_094813_add_new_property_types_and_attributes', 1),
(80, '2025_12_02_225625_fix_referral_campaigns_id_autoincrement', 1),
(81, '2025_12_02_235859_create_apartment_invitations_table', 1),
(82, '2025_12_03_005437_add_session_fields_to_apartment_invitations_table', 1),
(83, '2025_12_03_111035_add_registration_source_to_users_table', 1),
(84, '2025_12_03_120231_add_security_tracking_fields_to_apartment_invitations_table', 1),
(85, '2025_12_03_150015_fix_apartment_invitations_foreign_key', 1),
(86, '2025_12_03_155010_add_referred_by_to_users_table', 1),
(87, '2025_12_04_120000_optimize_apartment_invitations_performance', 1),
(88, '2025_12_04_120001_add_database_cleanup_procedures', 1),
(89, '2025_12_04_120002_optimize_foreign_keys_and_constraints', 1),
(90, '2025_12_04_120003_add_database_maintenance_tables', 1),
(91, '2025_12_04_120004_verify_database_optimizations', 1),
(92, '2025_12_05_120000_finalize_easyrent_link_authentication_schema', 1),
(93, '2025_12_05_130000_add_final_performance_indexes', 1),
(94, '2025_12_05_140000_create_property_and_apartment_types_tables', 1),
(95, '2025_12_06_001638_migrate_apartment_types_to_ids', 1),
(96, '2025_12_06_081544_update_property_foreign_keys_to_use_id', 1),
(97, '2025_12_06_082932_rename_prop_id_to_property_id_in_properties_table', 1),
(98, '2025_12_06_225053_add_missing_columns_to_commission_rates_table', 1),
(99, '2025_12_07_040000_ensure_property_id_column_exists', 1),
(100, '2025_12_07_050000_drop_bookings_table', 1),
(101, '2025_12_10_150035_make_tenant_id_nullable_in_payments_table', 1),
(102, '2025_12_10_160000_fix_tenant_id_nullable_payments', 1),
(103, '2025_12_12_100938_fix_apartment_type_id_mapping', 1),
(104, '2025_12_15_055139_add_pricing_configuration_to_apartments_table', 1),
(105, '2025_12_15_061759_add_calculation_fields_to_profoma_receipts_table', 1),
(106, '2025_12_15_070000_migrate_existing_payment_calculation_data', 1),
(128, '2025_12_17_102458_make_all_id_columns_auto_increment', 2),
(129, '2025_12_16_120000_fix_apartments_property_id_foreign_key', 3),
(130, '2025_12_17_120642_add_rental_duration_support_to_apartments_table', 4),
(131, '2025_12_17_150000_fix_payment_invitations_benefactor_email_nullable', 5),
(132, '2025_12_19_232009_fix_payment_method_column_enum_values', 6),
(133, '2025_12_21_100000_create_durations_table', 7),
(134, '2025_12_21_132000_add_duration_to_apartments_table', 8);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `transaction_id` varchar(255) NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `landlord_id` bigint(20) UNSIGNED NOT NULL,
  `apartment_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `duration` int(11) NOT NULL COMMENT 'Duration in months',
  `status` enum('pending','completed','success','failed') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT 'card',
  `payment_reference` varchar(255) DEFAULT NULL,
  `payment_meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_meta`)),
  `paid_at` timestamp NULL DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `transaction_id`, `tenant_id`, `landlord_id`, `apartment_id`, `amount`, `duration`, `status`, `payment_method`, `payment_reference`, `payment_meta`, `paid_at`, `due_date`, `created_at`, `updated_at`) VALUES
(1, 'ER-INV-GUEST-20251217111353-9D4A914E', NULL, 993033, 1565025, 21600000.00, 12, 'completed', 'card', 'easyrent_0ab4e7472e771054c0dd6cb5ea18ae710ba0591f947d6611bc9e54eb397f7a9b', '{\"invitation_token\":\"0ab4e7472e771054c0dd6cb5ea18ae710ba0591f947d6611bc9e54eb397f7a9b\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-24\",\"application_timestamp\":\"2025-12-17T11:13:53.313557Z\",\"ip_address\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\",\"apartment_id\":1565025,\"landlord_id\":993033,\"total_amount\":1800000},\"created_via\":\"invitation_guest_flow\",\"move_in_date\":\"2025-12-24\"}', NULL, NULL, '2025-12-17 10:13:53', '2025-12-19 10:07:17'),
(2, 'ER-INV-GUEST-20251217114921-2A570E02', NULL, 993033, 1558236, 10800000.00, 6, 'completed', 'card', 'easyrent_70be2cd3560e4041cca408d3ff108c7ba06ce98ec0a0ac048c4ccc57d0063cb1', '{\"invitation_token\":\"70be2cd3560e4041cca408d3ff108c7ba06ce98ec0a0ac048c4ccc57d0063cb1\",\"application_data\":{\"duration\":\"6\",\"move_in_date\":\"2025-12-24\",\"application_timestamp\":\"2025-12-17T11:49:21.031153Z\",\"ip_address\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\",\"apartment_id\":1558236,\"landlord_id\":993033,\"total_amount\":1800000},\"created_via\":\"invitation_guest_flow\",\"move_in_date\":\"2025-12-24\"}', '2025-12-19 10:10:23', NULL, '2025-12-17 10:49:21', '2025-12-19 10:10:23'),
(3, 'ER-INV-GUEST-20251217121148-CAC47F7F', NULL, 993033, 1558236, 10800000.00, 6, 'completed', 'card', 'easyrent_f040a192b02d8a257b094e75f1b7ca0badd33b397b25dcb9d398b17e565ca830', '{\"invitation_token\":\"f040a192b02d8a257b094e75f1b7ca0badd33b397b25dcb9d398b17e565ca830\",\"application_data\":{\"duration\":\"6\",\"move_in_date\":\"2025-12-24\",\"application_timestamp\":\"2025-12-17T12:11:48.959072Z\",\"ip_address\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\",\"apartment_id\":1558236,\"landlord_id\":993033,\"total_amount\":1800000},\"created_via\":\"invitation_guest_flow\",\"move_in_date\":\"2025-12-24\"}', '2025-12-19 10:10:23', NULL, '2025-12-17 11:11:48', '2025-12-19 10:10:23'),
(4, 'ER-INV-20251217130356-218D878A', 808169, 993033, 7826796, 10800000.00, 12, 'completed', 'card', 'easyrent_37c4ca9eb73511b36e326da0aee1a052bbeeee7e5873eee8fdfe6ac075c19741', '{\"invitation_token\":\"37c4ca9eb73511b36e326da0aee1a052bbeeee7e5873eee8fdfe6ac075c19741\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-24\",\"total_amount\":900000},\"created_via\":\"invitation_flow\",\"move_in_date\":\"2025-12-24\"}', '2025-12-19 10:10:23', NULL, '2025-12-17 12:03:56', '2025-12-19 10:10:23'),
(5, 'ER-INV-GUEST-20251217131541-E1204A85', NULL, 993033, 7826796, 10800000.00, 12, 'completed', 'card', 'easyrent_b6d8b6c6f7237148cb7f410a287c897fda7ef10ae67e47807a799ddd120bfcfd', '{\"invitation_token\":\"b6d8b6c6f7237148cb7f410a287c897fda7ef10ae67e47807a799ddd120bfcfd\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-24\",\"application_timestamp\":\"2025-12-17T13:15:41.810405Z\",\"ip_address\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\",\"apartment_id\":7826796,\"landlord_id\":993033,\"total_amount\":900000},\"created_via\":\"invitation_guest_flow\",\"move_in_date\":\"2025-12-24\"}', '2025-12-19 10:10:23', NULL, '2025-12-17 12:15:41', '2025-12-19 10:10:23'),
(6, 'ER-INV-GUEST-20251217141339-90C68FE2', NULL, 993033, 7826796, 10800000.00, 12, 'completed', 'card', 'easyrent_ecd10415df7fdae3bdf09995eb8620f159904cdc287c87c0ec265619a5153c9d', '{\"invitation_token\":\"ecd10415df7fdae3bdf09995eb8620f159904cdc287c87c0ec265619a5153c9d\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-24\",\"application_timestamp\":\"2025-12-17T14:13:39.455299Z\",\"ip_address\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\",\"apartment_id\":7826796,\"landlord_id\":993033,\"total_amount\":900000},\"created_via\":\"invitation_guest_flow\",\"move_in_date\":\"2025-12-24\"}', '2025-12-19 10:10:23', NULL, '2025-12-17 13:13:39', '2025-12-19 10:10:23'),
(7, 'ER-INV-GUEST-20251217171731-5C909EB4', NULL, 993033, 7826796, 10800000.00, 12, 'completed', 'card', 'easyrent_2e1f0fb21d49ecdf5ae53731951eadc7e1aab153117a4a7175207771c6289ffa', '{\"invitation_token\":\"2e1f0fb21d49ecdf5ae53731951eadc7e1aab153117a4a7175207771c6289ffa\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2026-02-28\",\"application_timestamp\":\"2025-12-17T17:17:31.330840Z\",\"ip_address\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit\\/605.1.15 (KHTML, like Gecko) Version\\/17.6 Safari\\/605.1.15\",\"apartment_id\":7826796,\"landlord_id\":993033,\"total_amount\":900000},\"created_via\":\"invitation_guest_flow\",\"move_in_date\":\"2026-02-28\"}', '2025-12-19 10:10:23', NULL, '2025-12-17 16:17:31', '2025-12-19 10:10:23'),
(25, 'callback_test_1766189387', 869157, 993033, 1314527, 0.00, 12, 'completed', 'bank', 'callback_test_1766189387', '\"{\\\"paystack_data\\\":{\\\"status\\\":\\\"success\\\",\\\"reference\\\":\\\"callback_test_1766189387\\\",\\\"amount\\\":0,\\\"channel\\\":\\\"bank\\\",\\\"gateway_response\\\":\\\"Successful\\\",\\\"paid_at\\\":\\\"2025-12-20T00:09:47.472602Z\\\",\\\"metadata\\\":{\\\"proforma_id\\\":3}},\\\"callback_test\\\":true}\"', '2025-12-19 23:09:47', NULL, '2025-12-19 23:09:47', '2025-12-19 23:09:47'),
(42, 'test_proforma_fix_1766191794', 869157, 993033, 1314527, 2000000.00, 12, 'completed', 'bank', 'test_proforma_fix_1766191794', '\"{\\\"test_payment\\\":true,\\\"proforma_id\\\":2,\\\"paystack_channel\\\":\\\"bank\\\",\\\"actual_paid_amount\\\":\\\"2000000.00\\\",\\\"amount_source\\\":\\\"actual_payment\\\"}\"', '2025-12-19 23:49:54', NULL, '2025-12-19 23:49:54', '2025-12-19 23:49:54'),
(43, 'callback_test_1766191794', 869157, 993033, 1314527, 2000000.00, 12, 'completed', 'bank', 'callback_test_1766191794', '\"{\\\"paystack_data\\\":{\\\"status\\\":\\\"success\\\",\\\"reference\\\":\\\"callback_test_1766191794\\\",\\\"amount\\\":200000000,\\\"channel\\\":\\\"bank\\\",\\\"gateway_response\\\":\\\"Successful\\\",\\\"paid_at\\\":\\\"2025-12-20T00:49:54.138468Z\\\",\\\"metadata\\\":{\\\"proforma_id\\\":4}},\\\"callback_test\\\":true}\"', '2025-12-19 23:49:54', NULL, '2025-12-19 23:49:54', '2025-12-19 23:49:54'),
(44, 'easyrent_test_1766192013', NULL, 993033, 1558236, 1800000.00, 6, 'completed', 'card', 'easyrent_test_1766192013', '\"{\\\"invitation_token\\\":\\\"70be2cd3560e4041cca408d3ff108c7ba06ce98ec0a0ac048c4ccc57d0063cb1\\\",\\\"invitation_id\\\":2,\\\"easyrent_link\\\":true,\\\"calculation_method\\\":\\\"total_price_no_multiplication\\\",\\\"lease_duration\\\":6,\\\"apartment_pricing_type\\\":\\\"total\\\",\\\"paystack_data\\\":{\\\"status\\\":\\\"success\\\",\\\"reference\\\":\\\"easyrent_test_1766192013\\\",\\\"amount\\\":180000000,\\\"channel\\\":\\\"card\\\",\\\"gateway_response\\\":\\\"Successful\\\",\\\"paid_at\\\":\\\"2025-12-20T00:53:33.523511Z\\\",\\\"metadata\\\":{\\\"invitation_token\\\":\\\"70be2cd3560e4041cca408d3ff108c7ba06ce98ec0a0ac048c4ccc57d0063cb1\\\"}},\\\"callback_processed\\\":true}\"', '2025-12-19 23:53:33', NULL, '2025-12-19 23:53:33', '2025-12-19 23:53:33'),
(45, 'easyrent_test_1766192085', NULL, 993033, 1558236, 1800000.00, 6, 'completed', 'card', 'easyrent_test_1766192085', '\"{\\\"invitation_token\\\":\\\"70be2cd3560e4041cca408d3ff108c7ba06ce98ec0a0ac048c4ccc57d0063cb1\\\",\\\"invitation_id\\\":2,\\\"easyrent_link\\\":true,\\\"calculation_method\\\":\\\"total_price_no_multiplication\\\",\\\"lease_duration\\\":6,\\\"apartment_pricing_type\\\":\\\"total\\\",\\\"paystack_data\\\":{\\\"status\\\":\\\"success\\\",\\\"reference\\\":\\\"easyrent_test_1766192085\\\",\\\"amount\\\":180000000,\\\"channel\\\":\\\"card\\\",\\\"gateway_response\\\":\\\"Successful\\\",\\\"paid_at\\\":\\\"2025-12-20T00:54:45.260941Z\\\",\\\"metadata\\\":{\\\"invitation_token\\\":\\\"70be2cd3560e4041cca408d3ff108c7ba06ce98ec0a0ac048c4ccc57d0063cb1\\\"}},\\\"callback_processed\\\":true}\"', '2025-12-19 23:54:45', NULL, '2025-12-19 23:54:45', '2025-12-19 23:54:45'),
(46, 'ER-INV-GUEST-20251220010934-7D2B0A5A', NULL, 993033, 1159283, 21600000.00, 12, 'pending', 'card', 'easyrent_a7eb28c5e119d53b3f15216934bb6ddfc5869beddff23db314c9131b2d9ca46a', '{\"invitation_token\":\"a7eb28c5e119d53b3f15216934bb6ddfc5869beddff23db314c9131b2d9ca46a\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-27\",\"application_timestamp\":\"2025-12-20T01:09:34.729452Z\",\"ip_address\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit\\/605.1.15 (KHTML, like Gecko) Version\\/17.6 Safari\\/605.1.15\",\"apartment_id\":1159283,\"landlord_id\":993033,\"total_amount\":1800000},\"created_via\":\"invitation_guest_flow\",\"move_in_date\":\"2025-12-27\"}', NULL, NULL, '2025-12-20 00:09:34', '2025-12-20 00:09:34'),
(47, 'ER-INV-20251220021202-4A931926', 869157, 993033, 1159283, 21600000.00, 12, 'pending', 'card', 'easyrent_4b68e8f192e1503f033c8934c901c25059928887d3c62389bdcb744fa587a05d', '{\"invitation_token\":\"4b68e8f192e1503f033c8934c901c25059928887d3c62389bdcb744fa587a05d\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-27\",\"total_amount\":1800000},\"created_via\":\"invitation_flow\",\"move_in_date\":\"2025-12-27\"}', NULL, NULL, '2025-12-20 01:12:02', '2025-12-20 01:12:02'),
(48, 'ER-INV-GUEST-20251220221758-5890E775', NULL, 993033, 5936714, 5400000.00, 6, 'pending', 'card', 'easyrent_71d391a9414658a7fa2e6f05e6f882574a33e50c61f1e1dbb571147ea9c8cf62', '{\"invitation_token\":\"71d391a9414658a7fa2e6f05e6f882574a33e50c61f1e1dbb571147ea9c8cf62\",\"application_data\":{\"duration\":\"6\",\"move_in_date\":\"2025-12-27\",\"application_timestamp\":\"2025-12-20T22:17:58.573897Z\",\"ip_address\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\",\"apartment_id\":5936714,\"landlord_id\":993033,\"total_amount\":900000},\"created_via\":\"invitation_guest_flow\",\"move_in_date\":\"2025-12-27\"}', NULL, NULL, '2025-12-20 21:17:58', '2025-12-20 21:17:58'),
(49, 'ER-INV-20251221002318-CA352539', 578063, 993033, 5936714, 10800000.00, 12, 'pending', 'card', 'easyrent_6a05b8d4ec18d416cc5269bafc29fc4ccb106b69fd8140b952fd154b7d2b59d2', '{\"invitation_token\":\"6a05b8d4ec18d416cc5269bafc29fc4ccb106b69fd8140b952fd154b7d2b59d2\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-28\",\"total_amount\":900000},\"created_via\":\"invitation_flow\",\"move_in_date\":\"2025-12-28\"}', NULL, NULL, '2025-12-20 23:23:18', '2025-12-20 23:23:18'),
(50, 'ER-INV-20251221005851-C4BACC5A', 578063, 993033, 5936714, 10800000.00, 12, 'pending', 'card', 'easyrent_c2b8446c167dedcbff4a10d6613271a2f6052b4865b03a083ea2b3132e6dfa34', '{\"invitation_token\":\"c2b8446c167dedcbff4a10d6613271a2f6052b4865b03a083ea2b3132e6dfa34\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-28\",\"total_amount\":900000},\"created_via\":\"invitation_flow\",\"move_in_date\":\"2025-12-28\"}', NULL, NULL, '2025-12-20 23:58:51', '2025-12-20 23:58:51'),
(51, 'ER-INV-20251221084033-0B98EC45', 578063, 993033, 4338954, 2279988.00, 12, 'pending', 'card', 'easyrent_cd70e96c427a9e8ab716c6ed8a10f1764380bafc1685f2ca724c4b530858bfc7', '{\"invitation_token\":\"cd70e96c427a9e8ab716c6ed8a10f1764380bafc1685f2ca724c4b530858bfc7\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-28\",\"total_amount\":189999},\"created_via\":\"invitation_flow\",\"move_in_date\":\"2025-12-28\"}', NULL, NULL, '2025-12-21 07:40:33', '2025-12-21 07:40:33'),
(52, 'ER-INV-20251221091144-C15664C9', 578063, 993033, 5936714, 10800000.00, 12, 'pending', 'card', 'easyrent_26d2cadbcfc3ef730b85c8c1a0c59eb5051724f812c5ad03048ea3a5b4ec4872', '{\"invitation_token\":\"26d2cadbcfc3ef730b85c8c1a0c59eb5051724f812c5ad03048ea3a5b4ec4872\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-28\",\"total_amount\":900000},\"created_via\":\"invitation_flow\",\"move_in_date\":\"2025-12-28\"}', NULL, NULL, '2025-12-21 08:11:44', '2025-12-21 08:11:44'),
(53, 'ER-INV-GUEST-20251221091400-13502A2A', NULL, 993033, 5936714, 10800000.00, 12, 'pending', 'card', 'easyrent_26d2cadbcfc3ef730b85c8c1a0c59eb5051724f812c5ad03048ea3a5b4ec4872', '{\"invitation_token\":\"26d2cadbcfc3ef730b85c8c1a0c59eb5051724f812c5ad03048ea3a5b4ec4872\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-28\",\"application_timestamp\":\"2025-12-21T09:14:00.692997Z\",\"ip_address\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\",\"apartment_id\":5936714,\"landlord_id\":993033,\"total_amount\":900000},\"created_via\":\"invitation_guest_flow\",\"move_in_date\":\"2025-12-28\"}', NULL, NULL, '2025-12-21 08:14:00', '2025-12-21 08:14:00'),
(54, 'ER-INV-GUEST-20251221123452-C545B6A9', NULL, 993033, 4338954, 189999.00, 1, 'pending', 'card', 'easyrent_e680ef2b3b75243316d4a33a160ec3154c4dd5eca0788be3d9741a661eacc346', '{\"invitation_token\":\"e680ef2b3b75243316d4a33a160ec3154c4dd5eca0788be3d9741a661eacc346\",\"application_data\":{\"duration\":\"1\",\"move_in_date\":\"2025-12-28\",\"application_timestamp\":\"2025-12-21T12:34:52.602791Z\",\"ip_address\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\",\"apartment_id\":4338954,\"landlord_id\":993033,\"total_amount\":189999},\"created_via\":\"invitation_guest_flow\",\"move_in_date\":\"2025-12-28\"}', NULL, NULL, '2025-12-21 11:34:52', '2025-12-21 11:34:52'),
(55, 'ER-INV-GUEST-20251221124659-2CF12B3E', NULL, 993033, 9173307, 1080000.00, 12, 'pending', 'card', 'easyrent_87acad8338d7110ed8be6d6d2c9f1d6926ab50fa76bfe749f10da9d6115a0e5c', '{\"invitation_token\":\"87acad8338d7110ed8be6d6d2c9f1d6926ab50fa76bfe749f10da9d6115a0e5c\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-30\",\"application_timestamp\":\"2025-12-21T12:46:59.363455Z\",\"ip_address\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\",\"apartment_id\":9173307,\"landlord_id\":993033,\"total_amount\":90000},\"created_via\":\"invitation_guest_flow\",\"move_in_date\":\"2025-12-30\"}', NULL, NULL, '2025-12-21 11:46:59', '2025-12-21 11:46:59'),
(56, 'ER-INV-20251221131225-D4E32C55', 993033, 578063, 7616591, 26400000.00, 12, 'pending', 'card', 'easyrent_7d0847620022fef2c8f8b58daa8c2d29767f088a24ee557fa865030b8190ceae', '{\"invitation_token\":\"7d0847620022fef2c8f8b58daa8c2d29767f088a24ee557fa865030b8190ceae\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-28\",\"total_amount\":2200000},\"created_via\":\"invitation_flow\",\"move_in_date\":\"2025-12-28\"}', NULL, NULL, '2025-12-21 12:12:25', '2025-12-21 12:12:25'),
(57, 'easyrent_1766322754684_340728', 993033, 578063, 7616591, 2200000.00, 12, 'failed', 'bank', 'easyrent_7d0847620022fef2c8f8b58daa8c2d29767f088a24ee557fa865030b8190ceae', '{\"invitation_token\":\"7d0847620022fef2c8f8b58daa8c2d29767f088a24ee557fa865030b8190ceae\",\"transaction_type\":\"apartment_invitation_payment\",\"payment_source\":\"paystack_callback\",\"paystack_amount_kobo\":220000000,\"paystack_data\":{\"channel\":\"bank\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T13:06:10.000Z\"},\"processed_via\":\"invitation_flow\",\"payment_details\":{\"id\":5658904226,\"domain\":\"test\",\"status\":\"success\",\"reference\":\"easyrent_1766322754684_340728\",\"receipt_number\":null,\"amount\":220000000,\"message\":\"madePayment\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T13:06:10.000Z\",\"created_at\":\"2025-12-21T13:05:43.000Z\",\"channel\":\"bank\",\"currency\":\"NGN\",\"ip_address\":\"41.58.203.180\",\"metadata\":{\"invitation_token\":\"7d0847620022fef2c8f8b58daa8c2d29767f088a24ee557fa865030b8190ceae\",\"apartment_id\":7616591,\"tenant_id\":993033,\"landlord_id\":578063,\"payment_method\":\"card\",\"transaction_type\":\"apartment_invitation_payment\",\"referrer\":\"http:\\/\\/127.0.0.1:8000\\/apartment\\/invite\\/7d0847620022fef2c8f8b58daa8c2d29767f088a24ee557fa865030b8190ceae\\/payment\\/56\"},\"log\":{\"start_time\":1766322757,\"time_spent\":27,\"attempts\":1,\"errors\":0,\"success\":true,\"mobile\":false,\"input\":[],\"history\":[{\"type\":\"pending\",\"message\":\"Payment in progress with bank\",\"time\":1},{\"type\":\"action\",\"message\":\"Set payment method to: bank_transfer\",\"time\":2},{\"type\":\"action\",\"message\":\"Set payment method to: bank\",\"time\":3},{\"type\":\"input\",\"message\":\"Filled this field: account number\",\"time\":4},{\"type\":\"action\",\"message\":\"Attempted to pay with bank account\",\"time\":4},{\"type\":\"auth\",\"message\":\"Authentication Required: birthday\",\"time\":4},{\"type\":\"auth\",\"message\":\"Authentication Required: registration_token\",\"time\":16},{\"type\":\"auth\",\"message\":\"Authentication Required: payment_token\",\"time\":22},{\"type\":\"success\",\"message\":\"Successfully paid with bank account\",\"time\":27}]},\"fees\":200000,\"fees_split\":null,\"authorization\":{\"authorization_code\":\"AUTH_n6bitpeer0\",\"bin\":\"000XXX\",\"last4\":\"X000\",\"exp_month\":\"12\",\"exp_year\":\"9999\",\"channel\":\"bank\",\"card_type\":\"\",\"bank\":\"Zenith Bank\",\"country_code\":\"NG\",\"brand\":\"Zenith Emandate\",\"reusable\":false,\"signature\":null,\"account_name\":null,\"receiver_bank_account_number\":null,\"receiver_bank\":null},\"customer\":{\"id\":309920106,\"first_name\":\"\",\"last_name\":\"\",\"email\":\"moshoodkayodeabdul@gmail.com\",\"customer_code\":\"CUS_dtu32c9ip8ale76\",\"phone\":\"\",\"metadata\":null,\"risk_action\":\"default\",\"international_format_phone\":null},\"plan\":null,\"split\":[],\"order_id\":null,\"paidAt\":\"2025-12-21T13:06:10.000Z\",\"createdAt\":\"2025-12-21T13:05:43.000Z\",\"requested_amount\":220000000,\"pos_transaction_data\":null,\"source\":null,\"fees_breakdown\":null,\"connect\":null,\"transaction_date\":\"2025-12-21T13:05:43.000Z\",\"plan_object\":[],\"subaccount\":[]},\"failure_reason\":\"SQLSTATE[HY000]: General error: 1442 Can\'t update table \'apartment_invitations\' in stored function\\/trigger because it is already used by statement which invoked this stored function\\/trigger (SQL: update `apartment_invitations` set `status` = used, `payment_completed_at` = 2025-12-21 13:13:05, `apartment_invitations`.`updated_at` = 2025-12-21 13:13:05 where `id` = 25)\",\"failed_at\":\"2025-12-21T13:13:05.519985Z\",\"state_preserved\":true}', '2025-12-21 12:13:05', NULL, '2025-12-21 12:13:05', '2025-12-21 12:13:05'),
(58, 'ER-INV-GUEST-20251221131541-698B3FB8', NULL, 993033, 9173307, 1080000.00, 12, 'pending', 'card', 'easyrent_c3ce6310606b118c2eeb8de069ff23d786c1e9849fda107bda33c087161c18fd', '{\"invitation_token\":\"c3ce6310606b118c2eeb8de069ff23d786c1e9849fda107bda33c087161c18fd\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-28\",\"application_timestamp\":\"2025-12-21T13:15:41.711817Z\",\"ip_address\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\",\"apartment_id\":9173307,\"landlord_id\":993033,\"total_amount\":90000},\"created_via\":\"invitation_guest_flow\",\"move_in_date\":\"2025-12-28\"}', NULL, NULL, '2025-12-21 12:15:41', '2025-12-21 12:15:41'),
(59, 'easyrent_1766322947124_568364', NULL, 993033, 9173307, 90000.00, 12, 'completed', 'bank', 'easyrent_c3ce6310606b118c2eeb8de069ff23d786c1e9849fda107bda33c087161c18fd', '{\"invitation_token\":\"c3ce6310606b118c2eeb8de069ff23d786c1e9849fda107bda33c087161c18fd\",\"transaction_type\":\"apartment_invitation_payment\",\"payment_source\":\"paystack_callback\",\"paystack_amount_kobo\":9000000,\"paystack_data\":{\"channel\":\"bank\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T13:09:29.000Z\"},\"processed_via\":\"invitation_flow\",\"payment_details\":{\"id\":5658911494,\"domain\":\"test\",\"status\":\"success\",\"reference\":\"easyrent_1766322947124_568364\",\"receipt_number\":null,\"amount\":9000000,\"message\":\"madePayment\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T13:09:29.000Z\",\"created_at\":\"2025-12-21T13:09:10.000Z\",\"channel\":\"bank\",\"currency\":\"NGN\",\"ip_address\":\"41.58.203.180\",\"metadata\":{\"invitation_token\":\"c3ce6310606b118c2eeb8de069ff23d786c1e9849fda107bda33c087161c18fd\",\"apartment_id\":9173307,\"tenant_id\":\"\",\"landlord_id\":993033,\"payment_method\":\"card\",\"transaction_type\":\"apartment_invitation_payment\",\"referrer\":\"http:\\/\\/127.0.0.1:8000\\/apartment\\/invite\\/c3ce6310606b118c2eeb8de069ff23d786c1e9849fda107bda33c087161c18fd\\/payment\\/58\"},\"log\":{\"start_time\":1766322964,\"time_spent\":19,\"attempts\":1,\"errors\":0,\"success\":true,\"mobile\":false,\"input\":[],\"history\":[{\"type\":\"input\",\"message\":\"Filled this field: account number\",\"time\":2},{\"type\":\"action\",\"message\":\"Attempted to pay with bank account\",\"time\":2},{\"type\":\"auth\",\"message\":\"Authentication Required: birthday\",\"time\":3},{\"type\":\"auth\",\"message\":\"Authentication Required: registration_token\",\"time\":9},{\"type\":\"auth\",\"message\":\"Authentication Required: payment_token\",\"time\":14},{\"type\":\"success\",\"message\":\"Successfully paid with bank account\",\"time\":19}]},\"fees\":145000,\"fees_split\":null,\"authorization\":{\"authorization_code\":\"AUTH_guqos7lqb4\",\"bin\":\"000XXX\",\"last4\":\"X000\",\"exp_month\":\"12\",\"exp_year\":\"9999\",\"channel\":\"bank\",\"card_type\":\"\",\"bank\":\"Zenith Bank\",\"country_code\":\"NG\",\"brand\":\"Zenith Emandate\",\"reusable\":false,\"signature\":null,\"account_name\":null,\"receiver_bank_account_number\":null,\"receiver_bank\":null},\"customer\":{\"id\":327314722,\"first_name\":\"\",\"last_name\":\"\",\"email\":\"kingsleyer@geem.com\",\"customer_code\":\"CUS_wj8pkcflz3xpcn7\",\"phone\":\"\",\"metadata\":null,\"risk_action\":\"default\",\"international_format_phone\":null},\"plan\":null,\"split\":[],\"order_id\":null,\"paidAt\":\"2025-12-21T13:09:29.000Z\",\"createdAt\":\"2025-12-21T13:09:10.000Z\",\"requested_amount\":9000000,\"pos_transaction_data\":null,\"source\":null,\"fees_breakdown\":null,\"connect\":null,\"transaction_date\":\"2025-12-21T13:09:10.000Z\",\"plan_object\":[],\"subaccount\":[]}}', '2025-12-21 12:16:24', NULL, '2025-12-21 12:16:24', '2025-12-21 12:16:24'),
(60, 'ER-INV-GUEST-20251221175916-432739DE', NULL, 993033, 1157153, 2279988.00, 12, 'pending', 'card', 'easyrent_fec3f6582cab6cb03f36362153f27f198a9a4307e3969a6de8cdd348757c5fbe', '{\"invitation_token\":\"fec3f6582cab6cb03f36362153f27f198a9a4307e3969a6de8cdd348757c5fbe\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-28\",\"application_timestamp\":\"2025-12-21T17:59:16.764903Z\",\"ip_address\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\",\"apartment_id\":1157153,\"landlord_id\":993033,\"total_amount\":189999},\"created_via\":\"invitation_guest_flow\",\"move_in_date\":\"2025-12-28\"}', NULL, NULL, '2025-12-21 16:59:16', '2025-12-21 16:59:16'),
(61, 'easyrent_1766340002095_679616', NULL, 993033, 1157153, 189999.00, 12, 'completed', 'bank', 'easyrent_fec3f6582cab6cb03f36362153f27f198a9a4307e3969a6de8cdd348757c5fbe', '{\"invitation_token\":\"fec3f6582cab6cb03f36362153f27f198a9a4307e3969a6de8cdd348757c5fbe\",\"transaction_type\":\"apartment_invitation_payment\",\"payment_source\":\"paystack_callback\",\"paystack_amount_kobo\":18999900,\"paystack_data\":{\"channel\":\"bank\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T17:54:11.000Z\"},\"processed_via\":\"invitation_flow\",\"payment_details\":{\"id\":5659495154,\"domain\":\"test\",\"status\":\"success\",\"reference\":\"easyrent_1766340002095_679616\",\"receipt_number\":null,\"amount\":18999900,\"message\":\"madePayment\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T17:54:11.000Z\",\"created_at\":\"2025-12-21T17:53:32.000Z\",\"channel\":\"bank\",\"currency\":\"NGN\",\"ip_address\":\"41.58.202.213\",\"metadata\":{\"invitation_token\":\"fec3f6582cab6cb03f36362153f27f198a9a4307e3969a6de8cdd348757c5fbe\",\"apartment_id\":1157153,\"tenant_id\":\"\",\"landlord_id\":993033,\"payment_method\":\"card\",\"transaction_type\":\"apartment_invitation_payment\",\"referrer\":\"http:\\/\\/127.0.0.1:8000\\/apartment\\/invite\\/fec3f6582cab6cb03f36362153f27f198a9a4307e3969a6de8cdd348757c5fbe\\/payment\\/60\"},\"log\":{\"start_time\":1766340030,\"time_spent\":38,\"attempts\":1,\"errors\":0,\"success\":true,\"mobile\":false,\"input\":[],\"history\":[{\"type\":\"input\",\"message\":\"Filled this field: account number\",\"time\":11},{\"type\":\"action\",\"message\":\"Attempted to pay with bank account\",\"time\":11},{\"type\":\"auth\",\"message\":\"Authentication Required: birthday\",\"time\":12},{\"type\":\"auth\",\"message\":\"Authentication Required: registration_token\",\"time\":26},{\"type\":\"auth\",\"message\":\"Authentication Required: payment_token\",\"time\":33},{\"type\":\"success\",\"message\":\"Successfully paid with bank account\",\"time\":38}]},\"fees\":200000,\"fees_split\":null,\"authorization\":{\"authorization_code\":\"AUTH_3y8k1t6gs7\",\"bin\":\"000XXX\",\"last4\":\"X000\",\"exp_month\":\"12\",\"exp_year\":\"9999\",\"channel\":\"bank\",\"card_type\":\"\",\"bank\":\"Zenith Bank\",\"country_code\":\"NG\",\"brand\":\"Zenith Emandate\",\"reusable\":false,\"signature\":null,\"account_name\":null,\"receiver_bank_account_number\":null,\"receiver_bank\":null},\"customer\":{\"id\":327361536,\"first_name\":\"\",\"last_name\":\"\",\"email\":\"kayode@gmail.com\",\"customer_code\":\"CUS_i5zf9kkdmujvfv1\",\"phone\":\"\",\"metadata\":null,\"risk_action\":\"default\",\"international_format_phone\":null},\"plan\":null,\"split\":[],\"order_id\":null,\"paidAt\":\"2025-12-21T17:54:11.000Z\",\"createdAt\":\"2025-12-21T17:53:32.000Z\",\"requested_amount\":18999900,\"pos_transaction_data\":null,\"source\":null,\"fees_breakdown\":null,\"connect\":null,\"transaction_date\":\"2025-12-21T17:53:32.000Z\",\"plan_object\":[],\"subaccount\":[]}}', '2025-12-21 17:01:10', NULL, '2025-12-21 17:01:10', '2025-12-21 17:01:10'),
(62, 'ER-INV-20251221180531-34D73CBF', 869157, 993033, 1157153, 2279988.00, 12, 'pending', 'card', 'easyrent_fec3f6582cab6cb03f36362153f27f198a9a4307e3969a6de8cdd348757c5fbe', '{\"invitation_token\":\"fec3f6582cab6cb03f36362153f27f198a9a4307e3969a6de8cdd348757c5fbe\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-28\",\"total_amount\":189999},\"created_via\":\"invitation_flow\",\"move_in_date\":\"2025-12-28\"}', NULL, NULL, '2025-12-21 17:05:31', '2025-12-21 17:05:31'),
(63, 'easyrent_1766340340492_897912', 869157, 993033, 1157153, 189999.00, 12, 'failed', 'bank', 'easyrent_fec3f6582cab6cb03f36362153f27f198a9a4307e3969a6de8cdd348757c5fbe', '{\"invitation_token\":\"fec3f6582cab6cb03f36362153f27f198a9a4307e3969a6de8cdd348757c5fbe\",\"transaction_type\":\"apartment_invitation_payment\",\"payment_source\":\"paystack_callback\",\"paystack_amount_kobo\":18999900,\"paystack_data\":{\"channel\":\"bank\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T17:59:16.000Z\"},\"processed_via\":\"invitation_flow\",\"payment_details\":{\"id\":5659506116,\"domain\":\"test\",\"status\":\"success\",\"reference\":\"easyrent_1766340340492_897912\",\"receipt_number\":null,\"amount\":18999900,\"message\":\"madePayment\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T17:59:16.000Z\",\"created_at\":\"2025-12-21T17:58:44.000Z\",\"channel\":\"bank\",\"currency\":\"NGN\",\"ip_address\":\"41.58.202.213\",\"metadata\":{\"invitation_token\":\"fec3f6582cab6cb03f36362153f27f198a9a4307e3969a6de8cdd348757c5fbe\",\"apartment_id\":1157153,\"tenant_id\":869157,\"landlord_id\":993033,\"payment_method\":\"card\",\"transaction_type\":\"apartment_invitation_payment\",\"referrer\":\"http:\\/\\/127.0.0.1:8000\\/apartment\\/invite\\/fec3f6582cab6cb03f36362153f27f198a9a4307e3969a6de8cdd348757c5fbe\\/payment\\/62\"},\"log\":{\"start_time\":1766340342,\"time_spent\":32,\"attempts\":1,\"errors\":0,\"success\":true,\"mobile\":false,\"input\":[],\"history\":[{\"type\":\"input\",\"message\":\"Filled this field: account number\",\"time\":8},{\"type\":\"action\",\"message\":\"Attempted to pay with bank account\",\"time\":8},{\"type\":\"auth\",\"message\":\"Authentication Required: birthday\",\"time\":8},{\"type\":\"auth\",\"message\":\"Authentication Required: registration_token\",\"time\":20},{\"type\":\"auth\",\"message\":\"Authentication Required: payment_token\",\"time\":27},{\"type\":\"success\",\"message\":\"Successfully paid with bank account\",\"time\":32}]},\"fees\":200000,\"fees_split\":null,\"authorization\":{\"authorization_code\":\"AUTH_1c3msea2gt\",\"bin\":\"000XXX\",\"last4\":\"X000\",\"exp_month\":\"12\",\"exp_year\":\"9999\",\"channel\":\"bank\",\"card_type\":\"\",\"bank\":\"Zenith Bank\",\"country_code\":\"NG\",\"brand\":\"Zenith Emandate\",\"reusable\":false,\"signature\":null,\"account_name\":null,\"receiver_bank_account_number\":null,\"receiver_bank\":null},\"customer\":{\"id\":326905959,\"first_name\":\"\",\"last_name\":\"\",\"email\":\"smith@easyrent.africa\",\"customer_code\":\"CUS_ac67t1yj7ih3ei7\",\"phone\":\"\",\"metadata\":null,\"risk_action\":\"default\",\"international_format_phone\":null},\"plan\":null,\"split\":[],\"order_id\":null,\"paidAt\":\"2025-12-21T17:59:16.000Z\",\"createdAt\":\"2025-12-21T17:58:44.000Z\",\"requested_amount\":18999900,\"pos_transaction_data\":null,\"source\":null,\"fees_breakdown\":null,\"connect\":null,\"transaction_date\":\"2025-12-21T17:58:44.000Z\",\"plan_object\":[],\"subaccount\":[]},\"failure_reason\":\"SQLSTATE[HY000]: General error: 1442 Can\'t update table \'apartment_invitations\' in stored function\\/trigger because it is already used by statement which invoked this stored function\\/trigger (SQL: update `apartment_invitations` set `status` = used, `payment_completed_at` = 2025-12-21 18:06:15, `apartment_invitations`.`updated_at` = 2025-12-21 18:06:15 where `id` = 28)\",\"failed_at\":\"2025-12-21T18:06:15.531523Z\",\"state_preserved\":true}', '2025-12-21 17:06:15', NULL, '2025-12-21 17:06:15', '2025-12-21 17:06:15'),
(64, 'ER-INV-GUEST-20251221181426-71038632', NULL, 993033, 1159283, 21600000.00, 12, 'pending', 'card', 'easyrent_fdb35cefdd898042d2b696c2bba91340d5e03859375d455ef3121604288c05c9', '{\"invitation_token\":\"fdb35cefdd898042d2b696c2bba91340d5e03859375d455ef3121604288c05c9\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-28\",\"application_timestamp\":\"2025-12-21T18:14:26.486509Z\",\"ip_address\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\",\"apartment_id\":1159283,\"landlord_id\":993033,\"total_amount\":1800000},\"created_via\":\"invitation_guest_flow\",\"move_in_date\":\"2025-12-28\"}', NULL, NULL, '2025-12-21 17:14:26', '2025-12-21 17:14:26'),
(65, 'easyrent_1766340876238_785531', NULL, 993033, 1159283, 1800000.00, 12, 'completed', 'bank', 'easyrent_fdb35cefdd898042d2b696c2bba91340d5e03859375d455ef3121604288c05c9', '{\"invitation_token\":\"fdb35cefdd898042d2b696c2bba91340d5e03859375d455ef3121604288c05c9\",\"transaction_type\":\"apartment_invitation_payment\",\"payment_source\":\"paystack_callback\",\"paystack_amount_kobo\":180000000,\"paystack_data\":{\"channel\":\"bank\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T18:08:26.000Z\"},\"processed_via\":\"invitation_flow\",\"payment_details\":{\"id\":5659527883,\"domain\":\"test\",\"status\":\"success\",\"reference\":\"easyrent_1766340876238_785531\",\"receipt_number\":null,\"amount\":180000000,\"message\":\"madePayment\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T18:08:26.000Z\",\"created_at\":\"2025-12-21T18:07:57.000Z\",\"channel\":\"bank\",\"currency\":\"NGN\",\"ip_address\":\"41.58.202.213\",\"metadata\":{\"invitation_token\":\"fdb35cefdd898042d2b696c2bba91340d5e03859375d455ef3121604288c05c9\",\"apartment_id\":1159283,\"tenant_id\":\"\",\"landlord_id\":993033,\"payment_method\":\"card\",\"transaction_type\":\"apartment_invitation_payment\",\"referrer\":\"http:\\/\\/127.0.0.1:8000\\/apartment\\/invite\\/fdb35cefdd898042d2b696c2bba91340d5e03859375d455ef3121604288c05c9\\/payment\\/64\"},\"log\":{\"start_time\":1766340895,\"time_spent\":29,\"attempts\":1,\"errors\":0,\"success\":true,\"mobile\":false,\"input\":[],\"history\":[{\"type\":\"input\",\"message\":\"Filled this field: account number\",\"time\":2},{\"type\":\"action\",\"message\":\"Attempted to pay with bank account\",\"time\":2},{\"type\":\"auth\",\"message\":\"Authentication Required: birthday\",\"time\":2},{\"type\":\"auth\",\"message\":\"Authentication Required: registration_token\",\"time\":13},{\"type\":\"auth\",\"message\":\"Authentication Required: payment_token\",\"time\":23},{\"type\":\"success\",\"message\":\"Successfully paid with bank account\",\"time\":29}]},\"fees\":200000,\"fees_split\":null,\"authorization\":{\"authorization_code\":\"AUTH_nnjfvt3rho\",\"bin\":\"000XXX\",\"last4\":\"X000\",\"exp_month\":\"12\",\"exp_year\":\"9999\",\"channel\":\"bank\",\"card_type\":\"\",\"bank\":\"Zenith Bank\",\"country_code\":\"NG\",\"brand\":\"Zenith Emandate\",\"reusable\":false,\"signature\":null,\"account_name\":null,\"receiver_bank_account_number\":null,\"receiver_bank\":null},\"customer\":{\"id\":327363706,\"first_name\":\"\",\"last_name\":\"\",\"email\":\"kendo@easyrent.africa\",\"customer_code\":\"CUS_df2me4lb47e1ery\",\"phone\":\"\",\"metadata\":null,\"risk_action\":\"default\",\"international_format_phone\":null},\"plan\":null,\"split\":[],\"order_id\":null,\"paidAt\":\"2025-12-21T18:08:26.000Z\",\"createdAt\":\"2025-12-21T18:07:57.000Z\",\"requested_amount\":180000000,\"pos_transaction_data\":null,\"source\":null,\"fees_breakdown\":null,\"connect\":null,\"transaction_date\":\"2025-12-21T18:07:57.000Z\",\"plan_object\":[],\"subaccount\":[]}}', '2025-12-21 17:15:25', NULL, '2025-12-21 17:15:25', '2025-12-21 17:15:25'),
(66, 'ER-INV-20251221183354-BD99551C', 869157, 993033, 1558236, 21600000.00, 12, 'pending', 'card', 'easyrent_b52c493f8e00b14aa69de3ba25cf0e2a668594357ee14b7fce92fccd26250803', '{\"invitation_token\":\"b52c493f8e00b14aa69de3ba25cf0e2a668594357ee14b7fce92fccd26250803\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-28\",\"total_amount\":1800000},\"created_via\":\"invitation_flow\",\"move_in_date\":\"2025-12-28\"}', NULL, NULL, '2025-12-21 17:33:54', '2025-12-21 17:33:54'),
(67, 'easyrent_1766342044403_600851', 869157, 993033, 1558236, 1800000.00, 12, 'failed', 'bank', 'easyrent_b52c493f8e00b14aa69de3ba25cf0e2a668594357ee14b7fce92fccd26250803', '{\"invitation_token\":\"b52c493f8e00b14aa69de3ba25cf0e2a668594357ee14b7fce92fccd26250803\",\"transaction_type\":\"apartment_invitation_payment\",\"payment_source\":\"paystack_callback\",\"paystack_amount_kobo\":180000000,\"paystack_data\":{\"channel\":\"bank\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T18:27:40.000Z\"},\"processed_via\":\"invitation_flow\",\"payment_details\":{\"id\":5659571230,\"domain\":\"test\",\"status\":\"success\",\"reference\":\"easyrent_1766342044403_600851\",\"receipt_number\":null,\"amount\":180000000,\"message\":\"madePayment\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T18:27:40.000Z\",\"created_at\":\"2025-12-21T18:27:09.000Z\",\"channel\":\"bank\",\"currency\":\"NGN\",\"ip_address\":\"41.58.202.213\",\"metadata\":{\"invitation_token\":\"b52c493f8e00b14aa69de3ba25cf0e2a668594357ee14b7fce92fccd26250803\",\"apartment_id\":1558236,\"tenant_id\":869157,\"landlord_id\":993033,\"payment_method\":\"card\",\"transaction_type\":\"apartment_invitation_payment\",\"referrer\":\"http:\\/\\/127.0.0.1:8000\\/apartment\\/invite\\/b52c493f8e00b14aa69de3ba25cf0e2a668594357ee14b7fce92fccd26250803\\/payment\\/66\"},\"log\":{\"start_time\":1766342046,\"time_spent\":32,\"attempts\":1,\"errors\":0,\"success\":true,\"mobile\":false,\"input\":[],\"history\":[{\"type\":\"input\",\"message\":\"Filled this field: account number\",\"time\":2},{\"type\":\"action\",\"message\":\"Attempted to pay with bank account\",\"time\":3},{\"type\":\"auth\",\"message\":\"Authentication Required: birthday\",\"time\":3},{\"type\":\"auth\",\"message\":\"Authentication Required: registration_token\",\"time\":19},{\"type\":\"auth\",\"message\":\"Authentication Required: payment_token\",\"time\":26},{\"type\":\"success\",\"message\":\"Successfully paid with bank account\",\"time\":32}]},\"fees\":200000,\"fees_split\":null,\"authorization\":{\"authorization_code\":\"AUTH_1c3msea2gt\",\"bin\":\"000XXX\",\"last4\":\"X000\",\"exp_month\":\"12\",\"exp_year\":\"9999\",\"channel\":\"bank\",\"card_type\":\"\",\"bank\":\"Zenith Bank\",\"country_code\":\"NG\",\"brand\":\"Zenith Emandate\",\"reusable\":false,\"signature\":null,\"account_name\":null,\"receiver_bank_account_number\":null,\"receiver_bank\":null},\"customer\":{\"id\":326905959,\"first_name\":\"\",\"last_name\":\"\",\"email\":\"smith@easyrent.africa\",\"customer_code\":\"CUS_ac67t1yj7ih3ei7\",\"phone\":\"\",\"metadata\":null,\"risk_action\":\"default\",\"international_format_phone\":null},\"plan\":null,\"split\":[],\"order_id\":null,\"paidAt\":\"2025-12-21T18:27:40.000Z\",\"createdAt\":\"2025-12-21T18:27:09.000Z\",\"requested_amount\":180000000,\"pos_transaction_data\":null,\"source\":null,\"fees_breakdown\":null,\"connect\":null,\"transaction_date\":\"2025-12-21T18:27:09.000Z\",\"plan_object\":[],\"subaccount\":[]},\"failure_reason\":\"SQLSTATE[HY000]: General error: 1442 Can\'t update table \'apartment_invitations\' in stored function\\/trigger because it is already used by statement which invoked this stored function\\/trigger (SQL: update `apartment_invitations` set `status` = used, `payment_completed_at` = 2025-12-21 18:34:39, `apartment_invitations`.`updated_at` = 2025-12-21 18:34:39 where `id` = 33)\",\"failed_at\":\"2025-12-21T18:34:39.431111Z\",\"state_preserved\":true}', '2025-12-21 17:34:39', NULL, '2025-12-21 17:34:39', '2025-12-21 17:34:39'),
(68, 'ER-INV-20251221183529-DEBAD148', 869157, 993033, 1558236, 21600000.00, 12, 'pending', 'card', 'easyrent_b52c493f8e00b14aa69de3ba25cf0e2a668594357ee14b7fce92fccd26250803', '{\"invitation_token\":\"b52c493f8e00b14aa69de3ba25cf0e2a668594357ee14b7fce92fccd26250803\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-28\",\"total_amount\":1800000},\"created_via\":\"invitation_flow\",\"move_in_date\":\"2025-12-28\"}', NULL, NULL, '2025-12-21 17:35:29', '2025-12-21 17:35:29'),
(69, 'easyrent_1766342141359_574595', 869157, 993033, 1558236, 1800000.00, 12, 'failed', 'bank', 'easyrent_b52c493f8e00b14aa69de3ba25cf0e2a668594357ee14b7fce92fccd26250803', '{\"invitation_token\":\"b52c493f8e00b14aa69de3ba25cf0e2a668594357ee14b7fce92fccd26250803\",\"transaction_type\":\"apartment_invitation_payment\",\"payment_source\":\"paystack_callback\",\"paystack_amount_kobo\":180000000,\"paystack_data\":{\"channel\":\"bank\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T18:29:12.000Z\"},\"processed_via\":\"invitation_flow\",\"payment_details\":{\"id\":5659574538,\"domain\":\"test\",\"status\":\"success\",\"reference\":\"easyrent_1766342141359_574595\",\"receipt_number\":null,\"amount\":180000000,\"message\":\"madePayment\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T18:29:12.000Z\",\"created_at\":\"2025-12-21T18:28:45.000Z\",\"channel\":\"bank\",\"currency\":\"NGN\",\"ip_address\":\"41.58.202.213\",\"metadata\":{\"invitation_token\":\"b52c493f8e00b14aa69de3ba25cf0e2a668594357ee14b7fce92fccd26250803\",\"apartment_id\":1558236,\"tenant_id\":869157,\"landlord_id\":993033,\"payment_method\":\"card\",\"transaction_type\":\"apartment_invitation_payment\",\"referrer\":\"http:\\/\\/127.0.0.1:8000\\/apartment\\/invite\\/b52c493f8e00b14aa69de3ba25cf0e2a668594357ee14b7fce92fccd26250803\\/payment\\/68\"},\"log\":{\"start_time\":1766342143,\"time_spent\":26,\"attempts\":1,\"errors\":0,\"success\":true,\"mobile\":false,\"input\":[],\"history\":[{\"type\":\"input\",\"message\":\"Filled this field: account number\",\"time\":1},{\"type\":\"action\",\"message\":\"Attempted to pay with bank account\",\"time\":1},{\"type\":\"auth\",\"message\":\"Authentication Required: birthday\",\"time\":2},{\"type\":\"auth\",\"message\":\"Authentication Required: registration_token\",\"time\":12},{\"type\":\"auth\",\"message\":\"Authentication Required: payment_token\",\"time\":19},{\"type\":\"success\",\"message\":\"Successfully paid with bank account\",\"time\":26}]},\"fees\":200000,\"fees_split\":null,\"authorization\":{\"authorization_code\":\"AUTH_1c3msea2gt\",\"bin\":\"000XXX\",\"last4\":\"X000\",\"exp_month\":\"12\",\"exp_year\":\"9999\",\"channel\":\"bank\",\"card_type\":\"\",\"bank\":\"Zenith Bank\",\"country_code\":\"NG\",\"brand\":\"Zenith Emandate\",\"reusable\":false,\"signature\":null,\"account_name\":null,\"receiver_bank_account_number\":null,\"receiver_bank\":null},\"customer\":{\"id\":326905959,\"first_name\":\"\",\"last_name\":\"\",\"email\":\"smith@easyrent.africa\",\"customer_code\":\"CUS_ac67t1yj7ih3ei7\",\"phone\":\"\",\"metadata\":null,\"risk_action\":\"default\",\"international_format_phone\":null},\"plan\":null,\"split\":[],\"order_id\":null,\"paidAt\":\"2025-12-21T18:29:12.000Z\",\"createdAt\":\"2025-12-21T18:28:45.000Z\",\"requested_amount\":180000000,\"pos_transaction_data\":null,\"source\":null,\"fees_breakdown\":null,\"connect\":null,\"transaction_date\":\"2025-12-21T18:28:45.000Z\",\"plan_object\":[],\"subaccount\":[]},\"failure_reason\":\"SQLSTATE[HY000]: General error: 1442 Can\'t update table \'apartment_invitations\' in stored function\\/trigger because it is already used by statement which invoked this stored function\\/trigger (SQL: update `apartment_invitations` set `status` = used, `payment_completed_at` = 2025-12-21 18:36:10, `apartment_invitations`.`updated_at` = 2025-12-21 18:36:10 where `id` = 33)\",\"failed_at\":\"2025-12-21T18:36:10.961801Z\",\"state_preserved\":true}', '2025-12-21 17:36:10', NULL, '2025-12-21 17:36:10', '2025-12-21 17:36:10'),
(70, 'ER-INV-20251221184016-99B58342', 869157, 993033, 1558236, 21600000.00, 12, 'pending', 'card', 'easyrent_57da843e4934fc3121561acbc3fec010f2ef23e88b5a8f95c2b5adc48eb4ac9c', '{\"invitation_token\":\"57da843e4934fc3121561acbc3fec010f2ef23e88b5a8f95c2b5adc48eb4ac9c\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-28\",\"total_amount\":1800000},\"created_via\":\"invitation_flow\",\"move_in_date\":\"2025-12-28\"}', NULL, NULL, '2025-12-21 17:40:16', '2025-12-21 17:40:16'),
(71, 'easyrent_1766342437816_443930', 869157, 993033, 1558236, 1800000.00, 12, 'failed', 'bank', 'easyrent_57da843e4934fc3121561acbc3fec010f2ef23e88b5a8f95c2b5adc48eb4ac9c', '{\"invitation_token\":\"57da843e4934fc3121561acbc3fec010f2ef23e88b5a8f95c2b5adc48eb4ac9c\",\"transaction_type\":\"apartment_invitation_payment\",\"payment_source\":\"paystack_callback\",\"paystack_amount_kobo\":180000000,\"paystack_data\":{\"channel\":\"bank\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T18:34:05.000Z\"},\"processed_via\":\"invitation_flow\",\"payment_details\":{\"id\":5659585580,\"domain\":\"test\",\"status\":\"success\",\"reference\":\"easyrent_1766342437816_443930\",\"receipt_number\":null,\"amount\":180000000,\"message\":\"madePayment\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T18:34:05.000Z\",\"created_at\":\"2025-12-21T18:33:42.000Z\",\"channel\":\"bank\",\"currency\":\"NGN\",\"ip_address\":\"41.58.202.213\",\"metadata\":{\"invitation_token\":\"57da843e4934fc3121561acbc3fec010f2ef23e88b5a8f95c2b5adc48eb4ac9c\",\"apartment_id\":1558236,\"tenant_id\":869157,\"landlord_id\":993033,\"payment_method\":\"card\",\"transaction_type\":\"apartment_invitation_payment\",\"referrer\":\"http:\\/\\/127.0.0.1:8000\\/apartment\\/invite\\/57da843e4934fc3121561acbc3fec010f2ef23e88b5a8f95c2b5adc48eb4ac9c\\/payment\\/70\"},\"log\":{\"start_time\":1766342439,\"time_spent\":24,\"attempts\":1,\"errors\":0,\"success\":true,\"mobile\":false,\"input\":[],\"history\":[{\"type\":\"input\",\"message\":\"Filled this field: account number\",\"time\":2},{\"type\":\"action\",\"message\":\"Attempted to pay with bank account\",\"time\":2},{\"type\":\"auth\",\"message\":\"Authentication Required: birthday\",\"time\":2},{\"type\":\"auth\",\"message\":\"Authentication Required: registration_token\",\"time\":11},{\"type\":\"auth\",\"message\":\"Authentication Required: payment_token\",\"time\":19},{\"type\":\"success\",\"message\":\"Successfully paid with bank account\",\"time\":24}]},\"fees\":200000,\"fees_split\":null,\"authorization\":{\"authorization_code\":\"AUTH_1c3msea2gt\",\"bin\":\"000XXX\",\"last4\":\"X000\",\"exp_month\":\"12\",\"exp_year\":\"9999\",\"channel\":\"bank\",\"card_type\":\"\",\"bank\":\"Zenith Bank\",\"country_code\":\"NG\",\"brand\":\"Zenith Emandate\",\"reusable\":false,\"signature\":null,\"account_name\":null,\"receiver_bank_account_number\":null,\"receiver_bank\":null},\"customer\":{\"id\":326905959,\"first_name\":\"\",\"last_name\":\"\",\"email\":\"smith@easyrent.africa\",\"customer_code\":\"CUS_ac67t1yj7ih3ei7\",\"phone\":\"\",\"metadata\":null,\"risk_action\":\"default\",\"international_format_phone\":null},\"plan\":null,\"split\":[],\"order_id\":null,\"paidAt\":\"2025-12-21T18:34:05.000Z\",\"createdAt\":\"2025-12-21T18:33:42.000Z\",\"requested_amount\":180000000,\"pos_transaction_data\":null,\"source\":null,\"fees_breakdown\":null,\"connect\":null,\"transaction_date\":\"2025-12-21T18:33:42.000Z\",\"plan_object\":[],\"subaccount\":[]},\"failure_reason\":\"SQLSTATE[HY000]: General error: 1442 Can\'t update table \'apartment_invitations\' in stored function\\/trigger because it is already used by statement which invoked this stored function\\/trigger (SQL: update `apartment_invitations` set `status` = used, `payment_completed_at` = 2025-12-21 18:41:04, `apartment_invitations`.`updated_at` = 2025-12-21 18:41:04 where `id` = 35)\",\"failed_at\":\"2025-12-21T18:41:04.627784Z\",\"state_preserved\":true}', '2025-12-21 17:41:04', NULL, '2025-12-21 17:41:04', '2025-12-21 17:41:04'),
(72, 'ER-INV-20251221184549-862EB4E3', 869157, 993033, 1558236, 21600000.00, 12, 'pending', 'card', 'easyrent_6360a1b7cdbbd7eaadb41bd036727b686d4bba9b2eb3f914493b4bbdf25bd934', '{\"invitation_token\":\"6360a1b7cdbbd7eaadb41bd036727b686d4bba9b2eb3f914493b4bbdf25bd934\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-28\",\"total_amount\":1800000},\"created_via\":\"invitation_flow\",\"move_in_date\":\"2025-12-28\"}', NULL, NULL, '2025-12-21 17:45:49', '2025-12-21 17:45:49');
INSERT INTO `payments` (`id`, `transaction_id`, `tenant_id`, `landlord_id`, `apartment_id`, `amount`, `duration`, `status`, `payment_method`, `payment_reference`, `payment_meta`, `paid_at`, `due_date`, `created_at`, `updated_at`) VALUES
(73, 'easyrent_1766342759187_932334', 869157, 993033, 1558236, 1800000.00, 12, 'failed', 'bank', 'easyrent_6360a1b7cdbbd7eaadb41bd036727b686d4bba9b2eb3f914493b4bbdf25bd934', '{\"invitation_token\":\"6360a1b7cdbbd7eaadb41bd036727b686d4bba9b2eb3f914493b4bbdf25bd934\",\"transaction_type\":\"apartment_invitation_payment\",\"payment_source\":\"paystack_callback\",\"paystack_amount_kobo\":180000000,\"paystack_data\":{\"channel\":\"bank\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T18:39:28.000Z\"},\"processed_via\":\"invitation_flow\",\"payment_details\":{\"id\":5659598008,\"domain\":\"test\",\"status\":\"success\",\"reference\":\"easyrent_1766342759187_932334\",\"receipt_number\":null,\"amount\":180000000,\"message\":\"madePayment\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T18:39:28.000Z\",\"created_at\":\"2025-12-21T18:39:03.000Z\",\"channel\":\"bank\",\"currency\":\"NGN\",\"ip_address\":\"41.58.202.213\",\"metadata\":{\"invitation_token\":\"6360a1b7cdbbd7eaadb41bd036727b686d4bba9b2eb3f914493b4bbdf25bd934\",\"apartment_id\":1558236,\"tenant_id\":869157,\"landlord_id\":993033,\"payment_method\":\"card\",\"transaction_type\":\"apartment_invitation_payment\",\"referrer\":\"http:\\/\\/127.0.0.1:8000\\/apartment\\/invite\\/6360a1b7cdbbd7eaadb41bd036727b686d4bba9b2eb3f914493b4bbdf25bd934\\/payment\\/72\"},\"log\":{\"start_time\":1766342761,\"time_spent\":24,\"attempts\":1,\"errors\":0,\"success\":true,\"mobile\":false,\"input\":[],\"history\":[{\"type\":\"input\",\"message\":\"Filled this field: account number\",\"time\":2},{\"type\":\"action\",\"message\":\"Attempted to pay with bank account\",\"time\":2},{\"type\":\"auth\",\"message\":\"Authentication Required: birthday\",\"time\":2},{\"type\":\"auth\",\"message\":\"Authentication Required: registration_token\",\"time\":11},{\"type\":\"auth\",\"message\":\"Authentication Required: payment_token\",\"time\":17},{\"type\":\"success\",\"message\":\"Successfully paid with bank account\",\"time\":24}]},\"fees\":200000,\"fees_split\":null,\"authorization\":{\"authorization_code\":\"AUTH_1c3msea2gt\",\"bin\":\"000XXX\",\"last4\":\"X000\",\"exp_month\":\"12\",\"exp_year\":\"9999\",\"channel\":\"bank\",\"card_type\":\"\",\"bank\":\"Zenith Bank\",\"country_code\":\"NG\",\"brand\":\"Zenith Emandate\",\"reusable\":false,\"signature\":null,\"account_name\":null,\"receiver_bank_account_number\":null,\"receiver_bank\":null},\"customer\":{\"id\":326905959,\"first_name\":\"\",\"last_name\":\"\",\"email\":\"smith@easyrent.africa\",\"customer_code\":\"CUS_ac67t1yj7ih3ei7\",\"phone\":\"\",\"metadata\":null,\"risk_action\":\"default\",\"international_format_phone\":null},\"plan\":null,\"split\":[],\"order_id\":null,\"paidAt\":\"2025-12-21T18:39:28.000Z\",\"createdAt\":\"2025-12-21T18:39:03.000Z\",\"requested_amount\":180000000,\"pos_transaction_data\":null,\"source\":null,\"fees_breakdown\":null,\"connect\":null,\"transaction_date\":\"2025-12-21T18:39:03.000Z\",\"plan_object\":[],\"subaccount\":[]},\"failure_reason\":\"SQLSTATE[HY000]: General error: 1442 Can\'t update table \'apartment_invitations\' in stored function\\/trigger because it is already used by statement which invoked this stored function\\/trigger (SQL: update `apartment_invitations` set `status` = used, `payment_completed_at` = 2025-12-21 18:46:26, `apartment_invitations`.`updated_at` = 2025-12-21 18:46:26 where `id` = 37)\",\"failed_at\":\"2025-12-21T18:46:26.972655Z\",\"state_preserved\":true}', '2025-12-21 17:46:26', NULL, '2025-12-21 17:46:26', '2025-12-21 17:46:26'),
(74, 'ER-INV-20251221185653-E95E90A9', 869157, 993033, 1558236, 21600000.00, 12, 'pending', 'card', 'easyrent_ae7aede5f94afd0b96806a2641e2ae710f65a972c09dcf3d48e23c5a1ffaf2b6', '{\"invitation_token\":\"ae7aede5f94afd0b96806a2641e2ae710f65a972c09dcf3d48e23c5a1ffaf2b6\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-28\",\"total_amount\":1800000},\"created_via\":\"invitation_flow\",\"move_in_date\":\"2025-12-28\"}', NULL, NULL, '2025-12-21 17:56:53', '2025-12-21 17:56:53'),
(75, 'easyrent_1766343420091_63073', 869157, 993033, 1558236, 1800000.00, 12, 'failed', 'bank', 'easyrent_ae7aede5f94afd0b96806a2641e2ae710f65a972c09dcf3d48e23c5a1ffaf2b6', '{\"invitation_token\":\"ae7aede5f94afd0b96806a2641e2ae710f65a972c09dcf3d48e23c5a1ffaf2b6\",\"transaction_type\":\"apartment_invitation_payment\",\"payment_source\":\"paystack_callback\",\"paystack_amount_kobo\":180000000,\"paystack_data\":{\"channel\":\"bank\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T18:50:30.000Z\"},\"processed_via\":\"invitation_flow\",\"payment_details\":{\"id\":5659622592,\"domain\":\"test\",\"status\":\"success\",\"reference\":\"easyrent_1766343420091_63073\",\"receipt_number\":null,\"amount\":180000000,\"message\":\"madePayment\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T18:50:30.000Z\",\"created_at\":\"2025-12-21T18:50:05.000Z\",\"channel\":\"bank\",\"currency\":\"NGN\",\"ip_address\":\"41.58.202.213\",\"metadata\":{\"invitation_token\":\"ae7aede5f94afd0b96806a2641e2ae710f65a972c09dcf3d48e23c5a1ffaf2b6\",\"apartment_id\":1558236,\"tenant_id\":869157,\"landlord_id\":993033,\"payment_method\":\"card\",\"transaction_type\":\"apartment_invitation_payment\",\"referrer\":\"http:\\/\\/127.0.0.1:8000\\/apartment\\/invite\\/ae7aede5f94afd0b96806a2641e2ae710f65a972c09dcf3d48e23c5a1ffaf2b6\\/payment\\/74\"},\"log\":{\"start_time\":1766343422,\"time_spent\":26,\"attempts\":1,\"errors\":0,\"success\":true,\"mobile\":false,\"input\":[],\"history\":[{\"type\":\"input\",\"message\":\"Filled this field: account number\",\"time\":4},{\"type\":\"action\",\"message\":\"Attempted to pay with bank account\",\"time\":4},{\"type\":\"auth\",\"message\":\"Authentication Required: birthday\",\"time\":9},{\"type\":\"auth\",\"message\":\"Authentication Required: registration_token\",\"time\":17},{\"type\":\"auth\",\"message\":\"Authentication Required: payment_token\",\"time\":21},{\"type\":\"success\",\"message\":\"Successfully paid with bank account\",\"time\":26}]},\"fees\":200000,\"fees_split\":null,\"authorization\":{\"authorization_code\":\"AUTH_1c3msea2gt\",\"bin\":\"000XXX\",\"last4\":\"X000\",\"exp_month\":\"12\",\"exp_year\":\"9999\",\"channel\":\"bank\",\"card_type\":\"\",\"bank\":\"Zenith Bank\",\"country_code\":\"NG\",\"brand\":\"Zenith Emandate\",\"reusable\":false,\"signature\":null,\"account_name\":null,\"receiver_bank_account_number\":null,\"receiver_bank\":null},\"customer\":{\"id\":326905959,\"first_name\":\"\",\"last_name\":\"\",\"email\":\"smith@easyrent.africa\",\"customer_code\":\"CUS_ac67t1yj7ih3ei7\",\"phone\":\"\",\"metadata\":null,\"risk_action\":\"default\",\"international_format_phone\":null},\"plan\":null,\"split\":[],\"order_id\":null,\"paidAt\":\"2025-12-21T18:50:30.000Z\",\"createdAt\":\"2025-12-21T18:50:05.000Z\",\"requested_amount\":180000000,\"pos_transaction_data\":null,\"source\":null,\"fees_breakdown\":null,\"connect\":null,\"transaction_date\":\"2025-12-21T18:50:05.000Z\",\"plan_object\":[],\"subaccount\":[]},\"failure_reason\":\"SQLSTATE[HY000]: General error: 1442 Can\'t update table \'apartment_invitations\' in stored function\\/trigger because it is already used by statement which invoked this stored function\\/trigger (SQL: update `apartment_invitations` set `status` = used, `payment_completed_at` = 2025-12-21 18:57:29, `apartment_invitations`.`updated_at` = 2025-12-21 18:57:29 where `id` = 39)\",\"failed_at\":\"2025-12-21T18:57:29.673688Z\",\"state_preserved\":true}', '2025-12-21 17:57:29', NULL, '2025-12-21 17:57:29', '2025-12-21 17:57:29'),
(76, 'ER-INV-20251221185951-587DC0E4', 869157, 993033, 1558236, 21600000.00, 12, 'pending', 'card', 'easyrent_6a98a44a35fbb5b99d4f97306343e892dc5479e7c1df878977c46f98a83ef5f0', '{\"invitation_token\":\"6a98a44a35fbb5b99d4f97306343e892dc5479e7c1df878977c46f98a83ef5f0\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-28\",\"total_amount\":1800000},\"created_via\":\"invitation_flow\",\"move_in_date\":\"2025-12-28\"}', NULL, NULL, '2025-12-21 17:59:51', '2025-12-21 17:59:51'),
(77, 'easyrent_1766343597619_300784', 869157, 993033, 1558236, 1800000.00, 12, 'completed', 'bank', 'easyrent_6a98a44a35fbb5b99d4f97306343e892dc5479e7c1df878977c46f98a83ef5f0', '{\"invitation_token\":\"6a98a44a35fbb5b99d4f97306343e892dc5479e7c1df878977c46f98a83ef5f0\",\"transaction_type\":\"apartment_invitation_payment\",\"payment_source\":\"paystack_callback\",\"paystack_amount_kobo\":180000000,\"paystack_data\":{\"channel\":\"bank\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T18:53:26.000Z\"},\"processed_via\":\"invitation_flow\",\"payment_details\":{\"id\":5659629081,\"domain\":\"test\",\"status\":\"success\",\"reference\":\"easyrent_1766343597619_300784\",\"receipt_number\":null,\"amount\":180000000,\"message\":\"madePayment\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T18:53:26.000Z\",\"created_at\":\"2025-12-21T18:53:02.000Z\",\"channel\":\"bank\",\"currency\":\"NGN\",\"ip_address\":\"41.58.202.213\",\"metadata\":{\"invitation_token\":\"6a98a44a35fbb5b99d4f97306343e892dc5479e7c1df878977c46f98a83ef5f0\",\"apartment_id\":1558236,\"tenant_id\":869157,\"landlord_id\":993033,\"payment_method\":\"card\",\"transaction_type\":\"apartment_invitation_payment\",\"referrer\":\"http:\\/\\/127.0.0.1:8000\\/apartment\\/invite\\/6a98a44a35fbb5b99d4f97306343e892dc5479e7c1df878977c46f98a83ef5f0\\/payment\\/76\"},\"log\":{\"start_time\":1766343600,\"time_spent\":23,\"attempts\":1,\"errors\":0,\"success\":true,\"mobile\":false,\"input\":[],\"history\":[{\"type\":\"input\",\"message\":\"Filled this field: account number\",\"time\":1},{\"type\":\"action\",\"message\":\"Attempted to pay with bank account\",\"time\":1},{\"type\":\"auth\",\"message\":\"Authentication Required: birthday\",\"time\":2},{\"type\":\"auth\",\"message\":\"Authentication Required: registration_token\",\"time\":14},{\"type\":\"auth\",\"message\":\"Authentication Required: payment_token\",\"time\":19},{\"type\":\"success\",\"message\":\"Successfully paid with bank account\",\"time\":23}]},\"fees\":200000,\"fees_split\":null,\"authorization\":{\"authorization_code\":\"AUTH_1c3msea2gt\",\"bin\":\"000XXX\",\"last4\":\"X000\",\"exp_month\":\"12\",\"exp_year\":\"9999\",\"channel\":\"bank\",\"card_type\":\"\",\"bank\":\"Zenith Bank\",\"country_code\":\"NG\",\"brand\":\"Zenith Emandate\",\"reusable\":false,\"signature\":null,\"account_name\":null,\"receiver_bank_account_number\":null,\"receiver_bank\":null},\"customer\":{\"id\":326905959,\"first_name\":\"\",\"last_name\":\"\",\"email\":\"smith@easyrent.africa\",\"customer_code\":\"CUS_ac67t1yj7ih3ei7\",\"phone\":\"\",\"metadata\":null,\"risk_action\":\"default\",\"international_format_phone\":null},\"plan\":null,\"split\":[],\"order_id\":null,\"paidAt\":\"2025-12-21T18:53:26.000Z\",\"createdAt\":\"2025-12-21T18:53:02.000Z\",\"requested_amount\":180000000,\"pos_transaction_data\":null,\"source\":null,\"fees_breakdown\":null,\"connect\":null,\"transaction_date\":\"2025-12-21T18:53:02.000Z\",\"plan_object\":[],\"subaccount\":[]}}', '2025-12-21 18:00:24', NULL, '2025-12-21 18:00:24', '2025-12-21 18:00:24'),
(78, 'ER-INV-20251221190032-01AE6C8E', 869157, 993033, 1558236, 21600000.00, 12, 'pending', 'card', 'easyrent_6a98a44a35fbb5b99d4f97306343e892dc5479e7c1df878977c46f98a83ef5f0', '{\"invitation_token\":\"6a98a44a35fbb5b99d4f97306343e892dc5479e7c1df878977c46f98a83ef5f0\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-28\",\"total_amount\":1800000},\"created_via\":\"invitation_flow\",\"move_in_date\":\"2025-12-28\"}', NULL, NULL, '2025-12-21 18:00:32', '2025-12-21 18:00:32'),
(79, 'easyrent_1766343639425_816841', 869157, 993033, 1558236, 1800000.00, 12, 'completed', 'bank', 'easyrent_6a98a44a35fbb5b99d4f97306343e892dc5479e7c1df878977c46f98a83ef5f0', '{\"invitation_token\":\"6a98a44a35fbb5b99d4f97306343e892dc5479e7c1df878977c46f98a83ef5f0\",\"transaction_type\":\"apartment_invitation_payment\",\"payment_source\":\"paystack_callback\",\"paystack_amount_kobo\":180000000,\"paystack_data\":{\"channel\":\"bank\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T18:54:08.000Z\"},\"processed_via\":\"invitation_flow\",\"payment_details\":{\"id\":5659630600,\"domain\":\"test\",\"status\":\"success\",\"reference\":\"easyrent_1766343639425_816841\",\"receipt_number\":null,\"amount\":180000000,\"message\":\"madePayment\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T18:54:08.000Z\",\"created_at\":\"2025-12-21T18:53:43.000Z\",\"channel\":\"bank\",\"currency\":\"NGN\",\"ip_address\":\"41.58.202.213\",\"metadata\":{\"invitation_token\":\"6a98a44a35fbb5b99d4f97306343e892dc5479e7c1df878977c46f98a83ef5f0\",\"apartment_id\":1558236,\"tenant_id\":869157,\"landlord_id\":993033,\"payment_method\":\"card\",\"transaction_type\":\"apartment_invitation_payment\",\"referrer\":\"http:\\/\\/127.0.0.1:8000\\/apartment\\/invite\\/6a98a44a35fbb5b99d4f97306343e892dc5479e7c1df878977c46f98a83ef5f0\\/payment\\/78\"},\"log\":{\"start_time\":1766343641,\"time_spent\":24,\"attempts\":1,\"errors\":0,\"success\":true,\"mobile\":false,\"input\":[],\"history\":[{\"type\":\"input\",\"message\":\"Filled this field: account number\",\"time\":2},{\"type\":\"action\",\"message\":\"Attempted to pay with bank account\",\"time\":2},{\"type\":\"auth\",\"message\":\"Authentication Required: birthday\",\"time\":2},{\"type\":\"auth\",\"message\":\"Authentication Required: registration_token\",\"time\":14},{\"type\":\"auth\",\"message\":\"Authentication Required: payment_token\",\"time\":20},{\"type\":\"success\",\"message\":\"Successfully paid with bank account\",\"time\":24}]},\"fees\":200000,\"fees_split\":null,\"authorization\":{\"authorization_code\":\"AUTH_1c3msea2gt\",\"bin\":\"000XXX\",\"last4\":\"X000\",\"exp_month\":\"12\",\"exp_year\":\"9999\",\"channel\":\"bank\",\"card_type\":\"\",\"bank\":\"Zenith Bank\",\"country_code\":\"NG\",\"brand\":\"Zenith Emandate\",\"reusable\":false,\"signature\":null,\"account_name\":null,\"receiver_bank_account_number\":null,\"receiver_bank\":null},\"customer\":{\"id\":326905959,\"first_name\":\"\",\"last_name\":\"\",\"email\":\"smith@easyrent.africa\",\"customer_code\":\"CUS_ac67t1yj7ih3ei7\",\"phone\":\"\",\"metadata\":null,\"risk_action\":\"default\",\"international_format_phone\":null},\"plan\":null,\"split\":[],\"order_id\":null,\"paidAt\":\"2025-12-21T18:54:08.000Z\",\"createdAt\":\"2025-12-21T18:53:43.000Z\",\"requested_amount\":180000000,\"pos_transaction_data\":null,\"source\":null,\"fees_breakdown\":null,\"connect\":null,\"transaction_date\":\"2025-12-21T18:53:43.000Z\",\"plan_object\":[],\"subaccount\":[]}}', '2025-12-21 18:01:06', NULL, '2025-12-21 18:01:06', '2025-12-21 18:01:06'),
(80, 'ER-INV-20251221190117-65549936', 869157, 993033, 1558236, 21600000.00, 12, 'pending', 'card', 'easyrent_6a98a44a35fbb5b99d4f97306343e892dc5479e7c1df878977c46f98a83ef5f0', '{\"invitation_token\":\"6a98a44a35fbb5b99d4f97306343e892dc5479e7c1df878977c46f98a83ef5f0\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-28\",\"total_amount\":1800000},\"created_via\":\"invitation_flow\",\"move_in_date\":\"2025-12-28\"}', NULL, NULL, '2025-12-21 18:01:17', '2025-12-21 18:01:17'),
(81, 'ER-INV-20251221190855-70A2A6A1', 869157, 993033, 5936714, 10800000.00, 12, 'pending', 'card', 'easyrent_8bea001ec6288d4dd9ca4aba849a6f259590203e9b711ee92dfb622af147a9e7', '{\"invitation_token\":\"8bea001ec6288d4dd9ca4aba849a6f259590203e9b711ee92dfb622af147a9e7\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-28\",\"total_amount\":900000},\"created_via\":\"invitation_flow\",\"move_in_date\":\"2025-12-28\"}', NULL, NULL, '2025-12-21 18:08:55', '2025-12-21 18:08:55'),
(82, 'easyrent_1766344142173_274663', 869157, 993033, 5936714, 900000.00, 12, 'completed', 'bank', 'easyrent_8bea001ec6288d4dd9ca4aba849a6f259590203e9b711ee92dfb622af147a9e7', '{\"invitation_token\":\"8bea001ec6288d4dd9ca4aba849a6f259590203e9b711ee92dfb622af147a9e7\",\"transaction_type\":\"apartment_invitation_payment\",\"payment_source\":\"paystack_callback\",\"paystack_amount_kobo\":90000000,\"paystack_data\":{\"channel\":\"bank\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T19:02:29.000Z\"},\"processed_via\":\"invitation_flow\",\"payment_details\":{\"id\":5659648087,\"domain\":\"test\",\"status\":\"success\",\"reference\":\"easyrent_1766344142173_274663\",\"receipt_number\":null,\"amount\":90000000,\"message\":\"madePayment\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T19:02:29.000Z\",\"created_at\":\"2025-12-21T19:02:06.000Z\",\"channel\":\"bank\",\"currency\":\"NGN\",\"ip_address\":\"41.58.202.213\",\"metadata\":{\"invitation_token\":\"8bea001ec6288d4dd9ca4aba849a6f259590203e9b711ee92dfb622af147a9e7\",\"apartment_id\":5936714,\"tenant_id\":869157,\"landlord_id\":993033,\"payment_method\":\"card\",\"transaction_type\":\"apartment_invitation_payment\",\"referrer\":\"http:\\/\\/127.0.0.1:8000\\/apartment\\/invite\\/8bea001ec6288d4dd9ca4aba849a6f259590203e9b711ee92dfb622af147a9e7\\/payment\\/81\"},\"log\":{\"start_time\":1766344144,\"time_spent\":23,\"attempts\":1,\"errors\":0,\"success\":true,\"mobile\":false,\"input\":[],\"history\":[{\"type\":\"input\",\"message\":\"Filled this field: account number\",\"time\":2},{\"type\":\"action\",\"message\":\"Attempted to pay with bank account\",\"time\":2},{\"type\":\"auth\",\"message\":\"Authentication Required: birthday\",\"time\":2},{\"type\":\"auth\",\"message\":\"Authentication Required: registration_token\",\"time\":11},{\"type\":\"auth\",\"message\":\"Authentication Required: payment_token\",\"time\":17},{\"type\":\"success\",\"message\":\"Successfully paid with bank account\",\"time\":23}]},\"fees\":200000,\"fees_split\":null,\"authorization\":{\"authorization_code\":\"AUTH_1c3msea2gt\",\"bin\":\"000XXX\",\"last4\":\"X000\",\"exp_month\":\"12\",\"exp_year\":\"9999\",\"channel\":\"bank\",\"card_type\":\"\",\"bank\":\"Zenith Bank\",\"country_code\":\"NG\",\"brand\":\"Zenith Emandate\",\"reusable\":false,\"signature\":null,\"account_name\":null,\"receiver_bank_account_number\":null,\"receiver_bank\":null},\"customer\":{\"id\":326905959,\"first_name\":\"\",\"last_name\":\"\",\"email\":\"smith@easyrent.africa\",\"customer_code\":\"CUS_ac67t1yj7ih3ei7\",\"phone\":\"\",\"metadata\":null,\"risk_action\":\"default\",\"international_format_phone\":null},\"plan\":null,\"split\":[],\"order_id\":null,\"paidAt\":\"2025-12-21T19:02:29.000Z\",\"createdAt\":\"2025-12-21T19:02:06.000Z\",\"requested_amount\":90000000,\"pos_transaction_data\":null,\"source\":null,\"fees_breakdown\":null,\"connect\":null,\"transaction_date\":\"2025-12-21T19:02:06.000Z\",\"plan_object\":[],\"subaccount\":[]}}', '2025-12-21 18:09:28', NULL, '2025-12-21 18:09:28', '2025-12-21 18:09:28'),
(83, 'ER-INV-20251221190958-FFCDF315', 869157, 993033, 5936714, 10800000.00, 12, 'pending', 'card', 'easyrent_8bea001ec6288d4dd9ca4aba849a6f259590203e9b711ee92dfb622af147a9e7', '{\"invitation_token\":\"8bea001ec6288d4dd9ca4aba849a6f259590203e9b711ee92dfb622af147a9e7\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-28\",\"total_amount\":900000},\"created_via\":\"invitation_flow\",\"move_in_date\":\"2025-12-28\"}', NULL, NULL, '2025-12-21 18:09:58', '2025-12-21 18:09:58'),
(84, 'easyrent_1766344204656_204444', 869157, 993033, 5936714, 900000.00, 12, 'completed', 'bank', 'easyrent_8bea001ec6288d4dd9ca4aba849a6f259590203e9b711ee92dfb622af147a9e7', '{\"invitation_token\":\"8bea001ec6288d4dd9ca4aba849a6f259590203e9b711ee92dfb622af147a9e7\",\"transaction_type\":\"apartment_invitation_payment\",\"payment_source\":\"paystack_callback\",\"paystack_amount_kobo\":90000000,\"paystack_data\":{\"channel\":\"bank\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T19:03:34.000Z\"},\"processed_via\":\"invitation_flow\",\"payment_details\":{\"id\":5659650395,\"domain\":\"test\",\"status\":\"success\",\"reference\":\"easyrent_1766344204656_204444\",\"receipt_number\":null,\"amount\":90000000,\"message\":\"madePayment\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T19:03:34.000Z\",\"created_at\":\"2025-12-21T19:03:09.000Z\",\"channel\":\"bank\",\"currency\":\"NGN\",\"ip_address\":\"41.58.202.213\",\"metadata\":{\"invitation_token\":\"8bea001ec6288d4dd9ca4aba849a6f259590203e9b711ee92dfb622af147a9e7\",\"apartment_id\":5936714,\"tenant_id\":869157,\"landlord_id\":993033,\"payment_method\":\"card\",\"transaction_type\":\"apartment_invitation_payment\",\"referrer\":\"http:\\/\\/127.0.0.1:8000\\/apartment\\/invite\\/8bea001ec6288d4dd9ca4aba849a6f259590203e9b711ee92dfb622af147a9e7\\/payment\\/83\"},\"log\":{\"start_time\":1766344206,\"time_spent\":25,\"attempts\":1,\"errors\":0,\"success\":true,\"mobile\":false,\"input\":[],\"history\":[{\"type\":\"input\",\"message\":\"Filled this field: account number\",\"time\":6},{\"type\":\"action\",\"message\":\"Attempted to pay with bank account\",\"time\":6},{\"type\":\"auth\",\"message\":\"Authentication Required: birthday\",\"time\":6},{\"type\":\"auth\",\"message\":\"Authentication Required: registration_token\",\"time\":14},{\"type\":\"auth\",\"message\":\"Authentication Required: payment_token\",\"time\":20},{\"type\":\"success\",\"message\":\"Successfully paid with bank account\",\"time\":25}]},\"fees\":200000,\"fees_split\":null,\"authorization\":{\"authorization_code\":\"AUTH_1c3msea2gt\",\"bin\":\"000XXX\",\"last4\":\"X000\",\"exp_month\":\"12\",\"exp_year\":\"9999\",\"channel\":\"bank\",\"card_type\":\"\",\"bank\":\"Zenith Bank\",\"country_code\":\"NG\",\"brand\":\"Zenith Emandate\",\"reusable\":false,\"signature\":null,\"account_name\":null,\"receiver_bank_account_number\":null,\"receiver_bank\":null},\"customer\":{\"id\":326905959,\"first_name\":\"\",\"last_name\":\"\",\"email\":\"smith@easyrent.africa\",\"customer_code\":\"CUS_ac67t1yj7ih3ei7\",\"phone\":\"\",\"metadata\":null,\"risk_action\":\"default\",\"international_format_phone\":null},\"plan\":null,\"split\":[],\"order_id\":null,\"paidAt\":\"2025-12-21T19:03:34.000Z\",\"createdAt\":\"2025-12-21T19:03:09.000Z\",\"requested_amount\":90000000,\"pos_transaction_data\":null,\"source\":null,\"fees_breakdown\":null,\"connect\":null,\"transaction_date\":\"2025-12-21T19:03:09.000Z\",\"plan_object\":[],\"subaccount\":[]}}', '2025-12-21 18:10:33', NULL, '2025-12-21 18:10:33', '2025-12-21 18:10:33'),
(85, 'ER-INV-20251221191046-5C676FB9', 869157, 993033, 5936714, 10800000.00, 12, 'pending', 'card', 'easyrent_8bea001ec6288d4dd9ca4aba849a6f259590203e9b711ee92dfb622af147a9e7', '{\"invitation_token\":\"8bea001ec6288d4dd9ca4aba849a6f259590203e9b711ee92dfb622af147a9e7\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-28\",\"total_amount\":900000},\"created_via\":\"invitation_flow\",\"move_in_date\":\"2025-12-28\"}', NULL, NULL, '2025-12-21 18:10:46', '2025-12-21 18:10:46'),
(86, 'easyrent_1766344313670_747757', 869157, 993033, 5936714, 900000.00, 12, 'completed', 'bank', 'easyrent_8bea001ec6288d4dd9ca4aba849a6f259590203e9b711ee92dfb622af147a9e7', '{\"invitation_token\":\"8bea001ec6288d4dd9ca4aba849a6f259590203e9b711ee92dfb622af147a9e7\",\"transaction_type\":\"apartment_invitation_payment\",\"payment_source\":\"paystack_callback\",\"paystack_amount_kobo\":90000000,\"paystack_data\":{\"channel\":\"bank\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T19:05:20.000Z\"},\"processed_via\":\"invitation_flow\",\"payment_details\":{\"id\":5659654898,\"domain\":\"test\",\"status\":\"success\",\"reference\":\"easyrent_1766344313670_747757\",\"receipt_number\":null,\"amount\":90000000,\"message\":\"madePayment\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T19:05:20.000Z\",\"created_at\":\"2025-12-21T19:04:58.000Z\",\"channel\":\"bank\",\"currency\":\"NGN\",\"ip_address\":\"41.58.202.213\",\"metadata\":{\"invitation_token\":\"8bea001ec6288d4dd9ca4aba849a6f259590203e9b711ee92dfb622af147a9e7\",\"apartment_id\":5936714,\"tenant_id\":869157,\"landlord_id\":993033,\"payment_method\":\"card\",\"transaction_type\":\"apartment_invitation_payment\",\"referrer\":\"http:\\/\\/127.0.0.1:8000\\/apartment\\/invite\\/8bea001ec6288d4dd9ca4aba849a6f259590203e9b711ee92dfb622af147a9e7\\/payment\\/85\"},\"log\":{\"start_time\":1766344315,\"time_spent\":22,\"attempts\":1,\"errors\":0,\"success\":true,\"mobile\":false,\"input\":[],\"history\":[{\"type\":\"input\",\"message\":\"Filled this field: account number\",\"time\":2},{\"type\":\"action\",\"message\":\"Attempted to pay with bank account\",\"time\":2},{\"type\":\"auth\",\"message\":\"Authentication Required: birthday\",\"time\":2},{\"type\":\"auth\",\"message\":\"Authentication Required: registration_token\",\"time\":12},{\"type\":\"auth\",\"message\":\"Authentication Required: payment_token\",\"time\":17},{\"type\":\"success\",\"message\":\"Successfully paid with bank account\",\"time\":22}]},\"fees\":200000,\"fees_split\":null,\"authorization\":{\"authorization_code\":\"AUTH_1c3msea2gt\",\"bin\":\"000XXX\",\"last4\":\"X000\",\"exp_month\":\"12\",\"exp_year\":\"9999\",\"channel\":\"bank\",\"card_type\":\"\",\"bank\":\"Zenith Bank\",\"country_code\":\"NG\",\"brand\":\"Zenith Emandate\",\"reusable\":false,\"signature\":null,\"account_name\":null,\"receiver_bank_account_number\":null,\"receiver_bank\":null},\"customer\":{\"id\":326905959,\"first_name\":\"\",\"last_name\":\"\",\"email\":\"smith@easyrent.africa\",\"customer_code\":\"CUS_ac67t1yj7ih3ei7\",\"phone\":\"\",\"metadata\":null,\"risk_action\":\"default\",\"international_format_phone\":null},\"plan\":null,\"split\":[],\"order_id\":null,\"paidAt\":\"2025-12-21T19:05:20.000Z\",\"createdAt\":\"2025-12-21T19:04:58.000Z\",\"requested_amount\":90000000,\"pos_transaction_data\":null,\"source\":null,\"fees_breakdown\":null,\"connect\":null,\"transaction_date\":\"2025-12-21T19:04:58.000Z\",\"plan_object\":[],\"subaccount\":[]}}', '2025-12-21 18:12:18', NULL, '2025-12-21 18:12:18', '2025-12-21 18:12:18'),
(87, 'ER-INV-20251221191834-94DFD879', 869157, 993033, 5571100, 14400000.00, 12, 'pending', 'card', 'easyrent_5a3bba64ca2f5a57be31111dc283aecb59f26f02579a72cdc2f65d9a523ce124', '{\"invitation_token\":\"5a3bba64ca2f5a57be31111dc283aecb59f26f02579a72cdc2f65d9a523ce124\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-28\",\"total_amount\":1200000},\"created_via\":\"invitation_flow\",\"move_in_date\":\"2025-12-28\"}', NULL, NULL, '2025-12-21 18:18:34', '2025-12-21 18:18:34'),
(88, 'easyrent_1766344721812_215315', 869157, 993033, 5571100, 1200000.00, 12, 'completed', 'bank', 'easyrent_5a3bba64ca2f5a57be31111dc283aecb59f26f02579a72cdc2f65d9a523ce124', '{\"invitation_token\":\"5a3bba64ca2f5a57be31111dc283aecb59f26f02579a72cdc2f65d9a523ce124\",\"transaction_type\":\"apartment_invitation_payment\",\"payment_source\":\"paystack_callback\",\"paystack_amount_kobo\":120000000,\"paystack_data\":{\"channel\":\"bank\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T19:12:05.000Z\"},\"processed_via\":\"invitation_flow\",\"payment_details\":{\"id\":5659669273,\"domain\":\"test\",\"status\":\"success\",\"reference\":\"easyrent_1766344721812_215315\",\"receipt_number\":null,\"amount\":120000000,\"message\":\"madePayment\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T19:12:05.000Z\",\"created_at\":\"2025-12-21T19:11:46.000Z\",\"channel\":\"bank\",\"currency\":\"NGN\",\"ip_address\":\"41.58.202.213\",\"metadata\":{\"invitation_token\":\"5a3bba64ca2f5a57be31111dc283aecb59f26f02579a72cdc2f65d9a523ce124\",\"apartment_id\":5571100,\"tenant_id\":869157,\"landlord_id\":993033,\"payment_method\":\"card\",\"transaction_type\":\"apartment_invitation_payment\",\"referrer\":\"http:\\/\\/127.0.0.1:8000\\/apartment\\/invite\\/5a3bba64ca2f5a57be31111dc283aecb59f26f02579a72cdc2f65d9a523ce124\\/payment\\/87\"},\"log\":{\"start_time\":1766344724,\"time_spent\":18,\"attempts\":1,\"errors\":0,\"success\":true,\"mobile\":false,\"input\":[],\"history\":[{\"type\":\"input\",\"message\":\"Filled this field: account number\",\"time\":1},{\"type\":\"action\",\"message\":\"Attempted to pay with bank account\",\"time\":1},{\"type\":\"auth\",\"message\":\"Authentication Required: birthday\",\"time\":2},{\"type\":\"auth\",\"message\":\"Authentication Required: registration_token\",\"time\":9},{\"type\":\"auth\",\"message\":\"Authentication Required: payment_token\",\"time\":14},{\"type\":\"success\",\"message\":\"Successfully paid with bank account\",\"time\":18}]},\"fees\":200000,\"fees_split\":null,\"authorization\":{\"authorization_code\":\"AUTH_1c3msea2gt\",\"bin\":\"000XXX\",\"last4\":\"X000\",\"exp_month\":\"12\",\"exp_year\":\"9999\",\"channel\":\"bank\",\"card_type\":\"\",\"bank\":\"Zenith Bank\",\"country_code\":\"NG\",\"brand\":\"Zenith Emandate\",\"reusable\":false,\"signature\":null,\"account_name\":null,\"receiver_bank_account_number\":null,\"receiver_bank\":null},\"customer\":{\"id\":326905959,\"first_name\":\"\",\"last_name\":\"\",\"email\":\"smith@easyrent.africa\",\"customer_code\":\"CUS_ac67t1yj7ih3ei7\",\"phone\":\"\",\"metadata\":null,\"risk_action\":\"default\",\"international_format_phone\":null},\"plan\":null,\"split\":[],\"order_id\":null,\"paidAt\":\"2025-12-21T19:12:05.000Z\",\"createdAt\":\"2025-12-21T19:11:46.000Z\",\"requested_amount\":120000000,\"pos_transaction_data\":null,\"source\":null,\"fees_breakdown\":null,\"connect\":null,\"transaction_date\":\"2025-12-21T19:11:46.000Z\",\"plan_object\":[],\"subaccount\":[]}}', '2025-12-21 18:19:04', NULL, '2025-12-21 18:19:04', '2025-12-21 18:19:04'),
(89, 'easyrent_1766345339474_107074', NULL, 993033, 7826796, 900000.00, 12, 'completed', 'bank', 'easyrent_3ca8fc0f26e8996a60b7b46f4045c851b1f7a7b621fc16f2db01e262eb21e6a0', '{\"invitation_token\":\"3ca8fc0f26e8996a60b7b46f4045c851b1f7a7b621fc16f2db01e262eb21e6a0\",\"transaction_type\":\"apartment_invitation_payment\",\"payment_source\":\"paystack_callback\",\"paystack_amount_kobo\":90000000,\"paystack_data\":{\"channel\":\"bank\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T19:22:39.000Z\"},\"processed_via\":\"invitation_flow\",\"payment_details\":{\"id\":5659690472,\"domain\":\"test\",\"status\":\"success\",\"reference\":\"easyrent_1766345339474_107074\",\"receipt_number\":null,\"amount\":90000000,\"message\":\"madePayment\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T19:22:39.000Z\",\"created_at\":\"2025-12-21T19:22:19.000Z\",\"channel\":\"bank\",\"currency\":\"NGN\",\"ip_address\":\"41.58.202.213\",\"metadata\":{\"invitation_token\":\"3ca8fc0f26e8996a60b7b46f4045c851b1f7a7b621fc16f2db01e262eb21e6a0\",\"apartment_id\":7826796,\"tenant_id\":\"\",\"landlord_id\":993033,\"payment_method\":\"card\",\"transaction_type\":\"apartment_invitation_payment\",\"referrer\":\"http:\\/\\/127.0.0.1:8000\\/apartment\\/invite\\/3ca8fc0f26e8996a60b7b46f4045c851b1f7a7b621fc16f2db01e262eb21e6a0\\/payment\"},\"log\":{\"start_time\":1766345356,\"time_spent\":21,\"attempts\":1,\"errors\":0,\"success\":true,\"mobile\":false,\"input\":[],\"history\":[{\"type\":\"input\",\"message\":\"Filled this field: account number\",\"time\":2},{\"type\":\"action\",\"message\":\"Attempted to pay with bank account\",\"time\":2},{\"type\":\"auth\",\"message\":\"Authentication Required: birthday\",\"time\":2},{\"type\":\"auth\",\"message\":\"Authentication Required: registration_token\",\"time\":9},{\"type\":\"auth\",\"message\":\"Authentication Required: payment_token\",\"time\":14},{\"type\":\"success\",\"message\":\"Successfully paid with bank account\",\"time\":21}]},\"fees\":200000,\"fees_split\":null,\"authorization\":{\"authorization_code\":\"AUTH_1ak1fz05t4\",\"bin\":\"000XXX\",\"last4\":\"X000\",\"exp_month\":\"12\",\"exp_year\":\"9999\",\"channel\":\"bank\",\"card_type\":\"\",\"bank\":\"Zenith Bank\",\"country_code\":\"NG\",\"brand\":\"Zenith Emandate\",\"reusable\":false,\"signature\":null,\"account_name\":null,\"receiver_bank_account_number\":null,\"receiver_bank\":null},\"customer\":{\"id\":327375193,\"first_name\":\"\",\"last_name\":\"\",\"email\":\"kinni@easyrent.africa\",\"customer_code\":\"CUS_hwe1h453ucjb1gh\",\"phone\":\"\",\"metadata\":null,\"risk_action\":\"default\",\"international_format_phone\":null},\"plan\":null,\"split\":[],\"order_id\":null,\"paidAt\":\"2025-12-21T19:22:39.000Z\",\"createdAt\":\"2025-12-21T19:22:19.000Z\",\"requested_amount\":90000000,\"pos_transaction_data\":null,\"source\":null,\"fees_breakdown\":null,\"connect\":null,\"transaction_date\":\"2025-12-21T19:22:19.000Z\",\"plan_object\":[],\"subaccount\":[]}}', '2025-12-21 18:29:38', NULL, '2025-12-21 18:29:38', '2025-12-21 18:29:38'),
(90, 'easyrent_1766351613115_803359', NULL, 993033, 9173307, 90000.00, 12, 'completed', 'bank', 'easyrent_f105412baa03cac0c859d48e0eec74f793e3e978e7dfb84d01a7d22dce9130d9', '{\"invitation_token\":\"f105412baa03cac0c859d48e0eec74f793e3e978e7dfb84d01a7d22dce9130d9\",\"transaction_type\":\"apartment_invitation_payment\",\"payment_source\":\"paystack_callback\",\"paystack_amount_kobo\":9000000,\"paystack_data\":{\"channel\":\"bank\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T21:07:40.000Z\"},\"processed_via\":\"invitation_flow\",\"payment_details\":{\"id\":5659881270,\"domain\":\"test\",\"status\":\"success\",\"reference\":\"easyrent_1766351613115_803359\",\"receipt_number\":null,\"amount\":9000000,\"message\":\"madePayment\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T21:07:40.000Z\",\"created_at\":\"2025-12-21T21:07:14.000Z\",\"channel\":\"bank\",\"currency\":\"NGN\",\"ip_address\":\"41.58.202.213\",\"metadata\":{\"invitation_token\":\"f105412baa03cac0c859d48e0eec74f793e3e978e7dfb84d01a7d22dce9130d9\",\"apartment_id\":9173307,\"tenant_id\":\"\",\"landlord_id\":993033,\"payment_method\":\"card\",\"transaction_type\":\"apartment_invitation_payment\",\"referrer\":\"http:\\/\\/127.0.0.1:8000\\/apartment\\/invite\\/f105412baa03cac0c859d48e0eec74f793e3e978e7dfb84d01a7d22dce9130d9\\/payment\"},\"log\":{\"start_time\":1766351651,\"time_spent\":26,\"attempts\":1,\"errors\":0,\"success\":true,\"mobile\":false,\"input\":[],\"history\":[{\"type\":\"input\",\"message\":\"Filled this field: account number\",\"time\":3},{\"type\":\"action\",\"message\":\"Attempted to pay with bank account\",\"time\":3},{\"type\":\"auth\",\"message\":\"Authentication Required: birthday\",\"time\":3},{\"type\":\"auth\",\"message\":\"Authentication Required: registration_token\",\"time\":14},{\"type\":\"auth\",\"message\":\"Authentication Required: payment_token\",\"time\":20},{\"type\":\"success\",\"message\":\"Successfully paid with bank account\",\"time\":26}]},\"fees\":145000,\"fees_split\":null,\"authorization\":{\"authorization_code\":\"AUTH_1c3msea2gt\",\"bin\":\"000XXX\",\"last4\":\"X000\",\"exp_month\":\"12\",\"exp_year\":\"9999\",\"channel\":\"bank\",\"card_type\":\"\",\"bank\":\"Zenith Bank\",\"country_code\":\"NG\",\"brand\":\"Zenith Emandate\",\"reusable\":false,\"signature\":null,\"account_name\":null,\"receiver_bank_account_number\":null,\"receiver_bank\":null},\"customer\":{\"id\":326905959,\"first_name\":\"\",\"last_name\":\"\",\"email\":\"smith@easyrent.africa\",\"customer_code\":\"CUS_ac67t1yj7ih3ei7\",\"phone\":\"\",\"metadata\":null,\"risk_action\":\"default\",\"international_format_phone\":null},\"plan\":null,\"split\":[],\"order_id\":null,\"paidAt\":\"2025-12-21T21:07:40.000Z\",\"createdAt\":\"2025-12-21T21:07:14.000Z\",\"requested_amount\":9000000,\"pos_transaction_data\":null,\"source\":null,\"fees_breakdown\":null,\"connect\":null,\"transaction_date\":\"2025-12-21T21:07:14.000Z\",\"plan_object\":[],\"subaccount\":[]}}', '2025-12-21 20:14:39', NULL, '2025-12-21 20:14:39', '2025-12-21 20:14:39'),
(91, 'ER-INV-20251221211616-EC111F9C', 869157, 993033, 9173307, 1080000.00, 12, 'pending', 'card', 'easyrent_f105412baa03cac0c859d48e0eec74f793e3e978e7dfb84d01a7d22dce9130d9', '{\"invitation_token\":\"f105412baa03cac0c859d48e0eec74f793e3e978e7dfb84d01a7d22dce9130d9\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-28\",\"total_amount\":90000},\"created_via\":\"invitation_flow\",\"move_in_date\":\"2025-12-28\"}', NULL, NULL, '2025-12-21 20:16:16', '2025-12-21 20:16:16'),
(92, 'ER-INV-20251221211738-EFCA5E29', 869157, 993033, 9173307, 1080000.00, 12, 'pending', 'card', 'easyrent_f105412baa03cac0c859d48e0eec74f793e3e978e7dfb84d01a7d22dce9130d9', '{\"invitation_token\":\"f105412baa03cac0c859d48e0eec74f793e3e978e7dfb84d01a7d22dce9130d9\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-28\",\"total_amount\":90000},\"created_via\":\"invitation_flow\",\"move_in_date\":\"2025-12-28\"}', NULL, NULL, '2025-12-21 20:17:38', '2025-12-21 20:17:38'),
(93, 'easyrent_1766352122369_997299', NULL, 993033, 9173307, 90000.00, 12, 'completed', 'bank', 'easyrent_f105412baa03cac0c859d48e0eec74f793e3e978e7dfb84d01a7d22dce9130d9', '{\"invitation_token\":\"f105412baa03cac0c859d48e0eec74f793e3e978e7dfb84d01a7d22dce9130d9\",\"transaction_type\":\"apartment_invitation_payment\",\"payment_source\":\"paystack_callback\",\"paystack_amount_kobo\":9000000,\"paystack_data\":{\"channel\":\"bank\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T21:15:34.000Z\"},\"processed_via\":\"invitation_flow\",\"payment_details\":{\"id\":5659900241,\"domain\":\"test\",\"status\":\"success\",\"reference\":\"easyrent_1766352122369_997299\",\"receipt_number\":null,\"amount\":9000000,\"message\":\"madePayment\",\"gateway_response\":\"Approved\",\"paid_at\":\"2025-12-21T21:15:34.000Z\",\"created_at\":\"2025-12-21T21:15:07.000Z\",\"channel\":\"bank\",\"currency\":\"NGN\",\"ip_address\":\"41.58.202.213\",\"metadata\":{\"invitation_token\":\"f105412baa03cac0c859d48e0eec74f793e3e978e7dfb84d01a7d22dce9130d9\",\"apartment_id\":9173307,\"tenant_id\":\"\",\"landlord_id\":993033,\"payment_method\":\"card\",\"transaction_type\":\"apartment_invitation_payment\",\"referrer\":\"http:\\/\\/127.0.0.1:8000\\/apartment\\/invite\\/f105412baa03cac0c859d48e0eec74f793e3e978e7dfb84d01a7d22dce9130d9\\/payment\"},\"log\":{\"start_time\":1766352124,\"time_spent\":27,\"attempts\":1,\"errors\":0,\"success\":true,\"mobile\":false,\"input\":[],\"history\":[{\"type\":\"input\",\"message\":\"Filled this field: account number\",\"time\":2},{\"type\":\"action\",\"message\":\"Attempted to pay with bank account\",\"time\":2},{\"type\":\"auth\",\"message\":\"Authentication Required: birthday\",\"time\":4},{\"type\":\"auth\",\"message\":\"Authentication Required: registration_token\",\"time\":15},{\"type\":\"auth\",\"message\":\"Authentication Required: payment_token\",\"time\":21},{\"type\":\"success\",\"message\":\"Successfully paid with bank account\",\"time\":27}]},\"fees\":145000,\"fees_split\":null,\"authorization\":{\"authorization_code\":\"AUTH_1c3msea2gt\",\"bin\":\"000XXX\",\"last4\":\"X000\",\"exp_month\":\"12\",\"exp_year\":\"9999\",\"channel\":\"bank\",\"card_type\":\"\",\"bank\":\"Zenith Bank\",\"country_code\":\"NG\",\"brand\":\"Zenith Emandate\",\"reusable\":false,\"signature\":null,\"account_name\":null,\"receiver_bank_account_number\":null,\"receiver_bank\":null},\"customer\":{\"id\":326905959,\"first_name\":\"\",\"last_name\":\"\",\"email\":\"smith@easyrent.africa\",\"customer_code\":\"CUS_ac67t1yj7ih3ei7\",\"phone\":\"\",\"metadata\":null,\"risk_action\":\"default\",\"international_format_phone\":null},\"plan\":null,\"split\":[],\"order_id\":null,\"paidAt\":\"2025-12-21T21:15:34.000Z\",\"createdAt\":\"2025-12-21T21:15:07.000Z\",\"requested_amount\":9000000,\"pos_transaction_data\":null,\"source\":null,\"fees_breakdown\":null,\"connect\":null,\"transaction_date\":\"2025-12-21T21:15:07.000Z\",\"plan_object\":[],\"subaccount\":[]}}', '2025-12-21 20:22:33', NULL, '2025-12-21 20:22:33', '2025-12-21 20:22:33'),
(94, 'ER-INV-20251221212509-E02EB047', 869157, 993033, 9173307, 1080000.00, 12, 'pending', 'card', 'easyrent_f105412baa03cac0c859d48e0eec74f793e3e978e7dfb84d01a7d22dce9130d9', '{\"invitation_token\":\"f105412baa03cac0c859d48e0eec74f793e3e978e7dfb84d01a7d22dce9130d9\",\"application_data\":{\"duration\":\"12\",\"move_in_date\":\"2025-12-28\",\"total_amount\":90000},\"created_via\":\"invitation_flow\",\"move_in_date\":\"2025-12-28\"}', NULL, NULL, '2025-12-21 20:25:09', '2025-12-21 20:25:09');

-- --------------------------------------------------------

--
-- Table structure for table `payment_invitations`
--

CREATE TABLE `payment_invitations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `benefactor_email` varchar(255) DEFAULT NULL,
  `benefactor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `proforma_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `token` varchar(255) NOT NULL,
  `status` enum('pending','accepted','expired','cancelled') NOT NULL DEFAULT 'pending',
  `approval_status` enum('pending_approval','approved','declined') NOT NULL DEFAULT 'pending_approval',
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `accepted_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `declined_at` timestamp NULL DEFAULT NULL,
  `decline_reason` text DEFAULT NULL,
  `invoice_details` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_invitations`
--

INSERT INTO `payment_invitations` (`id`, `tenant_id`, `benefactor_email`, `benefactor_id`, `proforma_id`, `amount`, `token`, `status`, `approval_status`, `expires_at`, `accepted_at`, `approved_at`, `declined_at`, `decline_reason`, `invoice_details`, `created_at`, `updated_at`) VALUES
(1, 869157, NULL, NULL, 2, 2000000.00, '4l104QVonaHk1JFgZemyTFQENqLIIEoPcBqB5Du4r9raJy0Rt7d4jsGtZp6mRuhk', 'pending', 'approved', '2025-12-17 16:05:55', NULL, '2025-12-17 15:05:55', NULL, NULL, '{\"property_id\":9533782,\"apartment_id\":19,\"proforma_id\":\"2\",\"tenant_name\":\"kenny abdul\",\"sharing_method\":\"link\"}', '2025-12-17 15:03:35', '2025-12-17 15:05:55'),
(2, 869157, NULL, NULL, 2, 2000000.00, 'pdWTo3q42Q6K9NXK0JTIP2a25hL1smWWnCBlomGvpeLtA39BWtV5pDpz6j4FXYXm', 'pending', 'pending_approval', '2025-12-24 15:19:14', NULL, NULL, NULL, NULL, '{\"property_id\":9533782,\"apartment_id\":19,\"proforma_id\":\"2\",\"tenant_name\":\"kenny abdul\",\"sharing_method\":\"link\"}', '2025-12-17 15:19:14', '2025-12-17 15:19:14'),
(3, 869157, NULL, NULL, 2, 2000000.00, 'H8v1HbeSqDBJrStVC24c2xEH01UBIwCz6UGynfEw2rgHMma9uJbnW7ZQtDcQsEgP', 'pending', 'pending_approval', '2025-12-24 15:19:25', NULL, NULL, NULL, NULL, '{\"property_id\":9533782,\"apartment_id\":19,\"proforma_id\":\"2\",\"tenant_name\":\"kenny abdul\",\"sharing_method\":\"link\"}', '2025-12-17 15:19:25', '2025-12-17 15:19:25'),
(4, 869157, NULL, NULL, 2, 2000000.00, '4V8ponyI6m5hqusMhHiDjpUSC0Fg5riltiCPcN738wDmVECYDgyiCHttuoedHKFz', 'pending', 'pending_approval', '2025-12-24 15:19:30', NULL, NULL, NULL, NULL, '{\"property_id\":9533782,\"apartment_id\":19,\"proforma_id\":\"2\",\"tenant_name\":\"kenny abdul\",\"sharing_method\":\"link\"}', '2025-12-17 15:19:30', '2025-12-17 15:19:30'),
(5, 869157, NULL, NULL, 2, 2000000.00, '2fKrWsLZZXonwExCNVzRzWTY83U3UuY54C8bpIZiByLVcF6jS6WyY7ktkiaqkLjB', 'pending', 'pending_approval', '2025-12-24 15:19:34', NULL, NULL, NULL, NULL, '{\"property_id\":9533782,\"apartment_id\":19,\"proforma_id\":\"2\",\"tenant_name\":\"kenny abdul\",\"sharing_method\":\"link\"}', '2025-12-17 15:19:34', '2025-12-17 15:19:34'),
(6, 869157, NULL, NULL, 2, 2000000.00, 'F9G84DQ0e2cSRu1P8s90PSBDK4pUpUVN0VZkTPIjoybwLWvW0tSgGUnCOBQNlJt6', 'pending', 'pending_approval', '2025-12-24 15:19:47', NULL, NULL, NULL, NULL, '{\"property_id\":9533782,\"apartment_id\":19,\"proforma_id\":\"2\",\"tenant_name\":\"kenny abdul\",\"sharing_method\":\"link\"}', '2025-12-17 15:19:47', '2025-12-17 15:19:47'),
(7, 869157, NULL, NULL, 2, 2000000.00, 'VdgfNuXKD2MDRxIWcGgH5IkNdHTOCMYPSPujcyPx7gjfCLTfXtRTfhbRmzCBG6XQ', 'pending', 'pending_approval', '2025-12-24 15:21:02', NULL, NULL, NULL, NULL, '{\"property_id\":9533782,\"apartment_id\":19,\"proforma_id\":\"2\",\"tenant_name\":\"kenny abdul\",\"sharing_method\":\"link\"}', '2025-12-17 15:21:02', '2025-12-17 15:21:02'),
(8, 869157, NULL, NULL, 2, 2000000.00, 'dEY4RWyZgrHfhHXysNOANX8Ox1XrHO4ZyVncYQrBY5AiKAC0gS1Snl7ph0N2akgx', 'pending', 'pending_approval', '2025-12-24 15:30:15', NULL, NULL, NULL, NULL, '{\"property_id\":9533782,\"apartment_id\":19,\"proforma_id\":\"2\",\"tenant_name\":\"kenny abdul\",\"sharing_method\":\"link\"}', '2025-12-17 15:30:15', '2025-12-17 15:30:15'),
(9, 869157, NULL, NULL, 2, 2000000.00, 'bF4UROqkuujrLT3AiFcWmcVSxygp10knoSPuFkAt06W7BFkjI1nkDs2xcZlJmT89', 'pending', 'pending_approval', '2025-12-24 15:43:50', NULL, NULL, NULL, NULL, '{\"property_id\":9533782,\"apartment_id\":19,\"proforma_id\":\"2\",\"tenant_name\":\"kenny abdul\",\"sharing_method\":\"link\"}', '2025-12-17 15:43:50', '2025-12-17 15:43:50'),
(10, 869157, NULL, NULL, 2, 2000000.00, 'sFwDr8AWOQG3jNTo4jkFrH9n9JsMTk10NMEfQZM45OMGbC3kCgYm50YtMrd3FC30', 'pending', 'pending_approval', '2025-12-24 15:43:59', NULL, NULL, NULL, NULL, '{\"property_id\":9533782,\"apartment_id\":19,\"proforma_id\":\"2\",\"tenant_name\":\"kenny abdul\",\"sharing_method\":\"link\"}', '2025-12-17 15:43:59', '2025-12-17 15:43:59'),
(11, 869157, NULL, NULL, 2, 2000000.00, 'aKGJB4MJCZrfYNShXldeJlQ2YZdGScRLopyrwbqWGD0BlGFeq8qYULSZMWHtGB8q', 'pending', 'pending_approval', '2025-12-24 15:44:07', NULL, NULL, NULL, NULL, '{\"property_id\":9533782,\"apartment_id\":19,\"proforma_id\":\"2\",\"tenant_name\":\"kenny abdul\",\"sharing_method\":\"link\"}', '2025-12-17 15:44:07', '2025-12-17 15:44:07'),
(12, 869157, NULL, NULL, 2, 2000000.00, 'Ui2hOgiUzeiaEWBfiueHyRWfuMoBWJPzf4oKNaCJQXrGnLW5H7t8FkeSF95jjZns', 'pending', 'pending_approval', '2025-12-24 15:44:49', NULL, NULL, NULL, NULL, '{\"property_id\":9533782,\"apartment_id\":19,\"proforma_id\":\"2\",\"tenant_name\":\"kenny abdul\",\"sharing_method\":\"link\"}', '2025-12-17 15:44:49', '2025-12-17 15:44:49'),
(13, 869157, NULL, NULL, 2, 2000000.00, 'ouct6Oe6fhZUSpBPgmybXCbY8w2XFeSLy2UWCtMwMPRwLOEyZvW1hyLIIbKgZIgH', 'pending', 'pending_approval', '2025-12-24 15:45:23', NULL, NULL, NULL, NULL, '{\"property_id\":9533782,\"apartment_id\":19,\"proforma_id\":\"2\",\"tenant_name\":\"kenny abdul\",\"sharing_method\":\"link\"}', '2025-12-17 15:45:23', '2025-12-17 15:45:23'),
(14, 869157, NULL, NULL, 2, 2000000.00, 'AaqiPdPvrIlXq0Ex5lLy88jmSO26cSUFFe7n4zxXVycje93oolG3Rc2XagXnmyTr', 'pending', 'pending_approval', '2025-12-24 15:45:36', NULL, NULL, NULL, NULL, '{\"property_id\":9533782,\"apartment_id\":19,\"proforma_id\":\"2\",\"tenant_name\":\"kenny abdul\",\"sharing_method\":\"link\"}', '2025-12-17 15:45:36', '2025-12-17 15:45:36'),
(15, 869157, NULL, NULL, 2, 2000000.00, 'a79W52ChYoH1iLaERnGPWUHSVlw5g8ePJ53Ljl8oUbtsko1A6t0dzUbGInVjJaPr', 'pending', 'pending_approval', '2025-12-24 15:51:08', NULL, NULL, NULL, NULL, '{\"property_id\":9533782,\"apartment_id\":19,\"proforma_id\":\"2\",\"tenant_name\":\"kenny abdul\",\"sharing_method\":\"link\"}', '2025-12-17 15:51:08', '2025-12-17 15:51:08'),
(16, 869157, 'kagoose2002@gmail.com', NULL, 2, 2000000.00, '4XxKPnp5JR2n3NBqTORVII16kaGMJIUirAaJBzQHovYOkt18wfcUiyknzb0ALfLM', 'pending', 'pending_approval', '2025-12-24 15:51:22', NULL, NULL, NULL, NULL, '{\"property_id\":9533782,\"apartment_id\":19,\"proforma_id\":\"2\",\"message\":null,\"tenant_name\":\"kenny abdul\"}', '2025-12-17 15:51:22', '2025-12-17 15:51:22'),
(17, 869157, NULL, NULL, 2, 2000000.00, 'LL8Bk0RpfnrTQ08sG5LNNlu7o0pCGHsZQfkI6bnPJkuGyyK74PJIJnNlc7w6Kanc', 'pending', 'pending_approval', '2025-12-24 15:51:35', NULL, NULL, NULL, NULL, '{\"property_id\":9533782,\"apartment_id\":19,\"proforma_id\":\"2\",\"tenant_name\":\"kenny abdul\",\"sharing_method\":\"link\"}', '2025-12-17 15:51:35', '2025-12-17 15:51:35'),
(18, 869157, NULL, NULL, 2, 2000000.00, 'FBgRh7a1rxfMsHc1g4KlK9DvZd2Gw3IcfLU6iyCK4SjARH0es0T3mr9aDNeneqql', 'pending', 'pending_approval', '2025-12-24 15:56:29', NULL, NULL, NULL, NULL, '{\"property_id\":9533782,\"apartment_id\":19,\"proforma_id\":\"2\",\"tenant_name\":\"kenny abdul\",\"sharing_method\":\"link\"}', '2025-12-17 15:56:29', '2025-12-17 15:56:29'),
(19, 869157, NULL, NULL, 2, 2000000.00, 'GVS4ORGU4pV4TsfznzkaakphhfDhlB41xbMD842KXl9Ign9csxQNIR92lLgtRlBm', 'pending', 'pending_approval', '2025-12-24 16:07:05', NULL, NULL, NULL, NULL, '{\"property_id\":9533782,\"apartment_id\":19,\"proforma_id\":\"2\",\"tenant_name\":\"kenny abdul\",\"sharing_method\":\"link\"}', '2025-12-17 16:07:05', '2025-12-17 16:07:05'),
(20, 869157, NULL, NULL, 2, 2000000.00, '1m55CnvAJSkhTtEHgivFO1sfBw9JvKhfausHxPwhKX9OsYrD7n16gxGhviWlg4Is', 'pending', 'pending_approval', '2025-12-24 16:07:16', NULL, NULL, NULL, NULL, '{\"property_id\":9533782,\"apartment_id\":19,\"proforma_id\":\"2\",\"tenant_name\":\"kenny abdul\",\"sharing_method\":\"link\"}', '2025-12-17 16:07:16', '2025-12-17 16:07:16'),
(21, 869157, NULL, NULL, 2, 2000000.00, 'GsDkJ0xslcOfoEMeSkOwE7VRP259IYGotfORwcFgtQS1Js4kBw4kGrtLjrxvEJdv', 'pending', 'pending_approval', '2025-12-24 16:07:23', NULL, NULL, NULL, NULL, '{\"property_id\":9533782,\"apartment_id\":19,\"proforma_id\":\"2\",\"tenant_name\":\"kenny abdul\",\"sharing_method\":\"link\"}', '2025-12-17 16:07:23', '2025-12-17 16:07:23'),
(22, 869157, NULL, NULL, 2, 2000000.00, 'Pq83FQuAAqm6Ic2AXuZGWN3C391Qk1CAC9eg9zomfcOibwzxtKJWTQZ10mOYvMMc', 'pending', 'pending_approval', '2025-12-24 16:07:37', NULL, NULL, NULL, NULL, '{\"property_id\":9533782,\"apartment_id\":19,\"proforma_id\":\"2\",\"tenant_name\":\"kenny abdul\",\"sharing_method\":\"link\"}', '2025-12-17 16:07:37', '2025-12-17 16:07:37'),
(23, 869157, NULL, NULL, 2, 2000000.00, '8w6tsoEeiwrRi0Wd3xyW3p7Ixo3sO64NW0djl0eajYXFSINNm2qvFsM5TH3lnfBU', 'pending', 'pending_approval', '2025-12-24 16:10:12', NULL, NULL, NULL, NULL, '{\"property_id\":9533782,\"apartment_id\":19,\"proforma_id\":\"2\",\"tenant_name\":\"kenny abdul\",\"sharing_method\":\"link\"}', '2025-12-17 16:10:12', '2025-12-17 16:10:12'),
(24, 869157, NULL, NULL, 2, 2000000.00, 'rwXHZhKTl3MyrcEg3Mx8qMi8ww45vYjdp6WJ16epifNYCiLupvJ6Ljajysw5Iw1v', 'pending', 'pending_approval', '2025-12-24 16:11:09', NULL, NULL, NULL, NULL, '{\"property_id\":9533782,\"apartment_id\":19,\"proforma_id\":\"2\",\"tenant_name\":\"kenny abdul\",\"sharing_method\":\"link\"}', '2025-12-17 16:11:09', '2025-12-17 16:11:09'),
(25, 869157, NULL, NULL, 2, 2000000.00, 'BYnwI1CewBHuyiB8xqPeomxEAOL7yTp7QzobbV8giWlDgIWSrIRj0silwNz05wKK', 'pending', 'pending_approval', '2025-12-24 16:12:06', NULL, NULL, NULL, NULL, '{\"property_id\":9533782,\"apartment_id\":19,\"proforma_id\":\"2\",\"tenant_name\":\"kenny abdul\",\"sharing_method\":\"link\"}', '2025-12-17 16:12:06', '2025-12-17 16:12:06'),
(26, 869157, NULL, NULL, 2, 2000000.00, '0aPmwdI1w95bBEgrn9ZXafff6fCHlZyIoD8CLGC2fXXQlIwSQTjqqyCFdySsSNov', 'pending', 'pending_approval', '2025-12-24 16:30:32', NULL, NULL, NULL, NULL, '{\"property_id\":9533782,\"apartment_id\":19,\"proforma_id\":\"2\",\"tenant_name\":\"kenny abdul\",\"sharing_method\":\"link\"}', '2025-12-17 16:30:32', '2025-12-17 16:30:32'),
(27, 869157, NULL, NULL, 2, 2000000.00, 'Dq3npNqBGvCNtpqIgrhaP6a2DxLe8HFUK8HCXL7Ek7bil24ElQwrbWTgkkX8KvA1', 'pending', 'pending_approval', '2025-12-26 09:48:00', NULL, NULL, NULL, NULL, '{\"property_id\":9533782,\"apartment_id\":19,\"proforma_id\":\"2\",\"tenant_name\":\"kenny abdul\",\"sharing_method\":\"link\"}', '2025-12-19 09:48:00', '2025-12-19 09:48:00'),
(28, 869157, NULL, NULL, 2, 2000000.00, 'WQ80WGj69Pth2HAPa5JzIUIUylotfsbw2V6m2ozHPBIp3GnZfF5Kk94g4P2Sl8v7', 'pending', 'pending_approval', '2025-12-26 09:49:45', NULL, NULL, NULL, NULL, '{\"property_id\":9533782,\"apartment_id\":19,\"proforma_id\":\"2\",\"tenant_name\":\"kenny abdul\",\"sharing_method\":\"link\"}', '2025-12-19 09:49:45', '2025-12-19 09:49:45');

-- --------------------------------------------------------

--
-- Table structure for table `payment_tracking`
--

CREATE TABLE `payment_tracking` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `payment_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(50) NOT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `tracked_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `performance_logs`
--

CREATE TABLE `performance_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `method` varchar(10) NOT NULL,
  `url` varchar(500) NOT NULL,
  `route_name` varchar(255) DEFAULT NULL,
  `controller_action` varchar(255) DEFAULT NULL,
  `status_code` int(11) NOT NULL,
  `execution_time` decimal(8,2) NOT NULL,
  `memory_usage` bigint(20) NOT NULL,
  `query_count` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `profoma_receipt`
--

CREATE TABLE `profoma_receipt` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `transaction_id` varchar(255) NOT NULL,
  `apartment_id` bigint(20) UNSIGNED NOT NULL,
  `calculation_method` varchar(255) DEFAULT NULL,
  `calculation_log` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`calculation_log`)),
  `amount` decimal(10,2) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `security_deposit` decimal(15,2) DEFAULT NULL,
  `water` decimal(15,2) DEFAULT NULL,
  `internet` decimal(15,2) DEFAULT NULL,
  `generator` decimal(15,2) DEFAULT NULL,
  `other_charges_desc` text DEFAULT NULL,
  `other_charges_amount` decimal(15,2) DEFAULT NULL,
  `total` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profoma_receipt`
--

INSERT INTO `profoma_receipt` (`id`, `user_id`, `tenant_id`, `status`, `transaction_id`, `apartment_id`, `calculation_method`, `calculation_log`, `amount`, `duration`, `security_deposit`, `water`, `internet`, `generator`, `other_charges_desc`, `other_charges_amount`, `total`, `created_at`, `updated_at`) VALUES
(1, 993033, 869157, 3, '1314527', 1314527, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-17 13:27:47', '2025-12-17 13:27:47'),
(2, 993033, 869157, 1, '1408804', 19, 'total_price_no_multiplication_with_additional_charges', '[{\"step\":\"input_validation\",\"calculation_id\":\"calc_6942cf65080df\",\"apartment_price\":2000000,\"rental_duration\":12,\"original_pricing_type\":\"total\",\"normalized_pricing_type\":\"total\",\"fallback_applied\":false,\"timestamp\":\"2025-12-17T15:42:29.033962Z\"},{\"step\":\"total_pricing_calculation\",\"method\":\"apartment_price_as_total\",\"apartment_price\":2000000,\"rental_duration\":12,\"note\":\"Using apartment price as total amount without multiplication\"},{\"step\":\"final_result\",\"total_amount\":2000000,\"calculation_method\":\"total_price_no_multiplication\",\"precision_applied\":true,\"bounds_validated\":true,\"timestamp\":\"2025-12-17T15:42:29.034032Z\"},{\"step\":\"final_total_with_charges\",\"base_amount\":2000000,\"additional_charges_total\":0,\"final_total\":2000000,\"timestamp\":\"2025-12-17T15:42:29.045846Z\"}]', 2000000.00, 12, NULL, NULL, NULL, NULL, NULL, NULL, 2000000.00, '2025-12-17 13:30:31', '2025-12-19 09:48:29'),
(3, 993033, 869157, 2, 'callback_test_1766189387', 1314527, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-19 23:09:47', '2025-12-19 23:09:47'),
(4, 993033, 869157, 2, 'callback_test_1766191794', 19, NULL, NULL, 2000000.00, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-19 23:49:54', '2025-12-19 23:49:54'),
(5, 993033, 869157, 3, '3582811', 3582811, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-21 11:18:02', '2025-12-21 11:18:02'),
(6, 993033, 869157, 3, '9173307', 9173307, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-21 11:26:23', '2025-12-21 11:26:23');

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `property_id` bigint(20) UNSIGNED NOT NULL,
  `prop_type` tinyint(3) UNSIGNED NOT NULL,
  `address` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `lga` varchar(255) NOT NULL,
  `no_of_apartment` int(10) UNSIGNED DEFAULT NULL,
  `size_value` decimal(10,2) DEFAULT NULL COMMENT 'Size in square meters, acres, etc.',
  `size_unit` varchar(20) DEFAULT NULL COMMENT 'sqm, sqft, acres, hectares',
  `agent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'available',
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `suspension_reason` text DEFAULT NULL,
  `suspended_at` timestamp NULL DEFAULT NULL,
  `suspended_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reactivated_at` timestamp NULL DEFAULT NULL,
  `reactivated_by` bigint(20) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `user_id`, `property_id`, `prop_type`, `address`, `state`, `lga`, `no_of_apartment`, `size_value`, `size_unit`, `agent_id`, `created_at`, `updated_at`, `status`, `approved_at`, `rejected_at`, `suspension_reason`, `suspended_at`, `suspended_by`, `reactivated_at`, `reactivated_by`) VALUES
(1, 993033, 9533782, 2, '9 point road apapa lagos', 'Lagos', 'Apapa', 3, NULL, 'sqm', NULL, '2025-12-16 13:16:30', '2025-12-16 13:16:30', 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 993033, 5484710, 1, '5 oremeji close agboju amuwo lagos', 'Lagos', 'Surulere', 1, NULL, 'sqm', NULL, '2025-12-17 10:42:38', '2025-12-17 10:42:38', 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 993033, 4353881, 2, 'realest cord, albert mac street', 'Lagos', 'Ibeju-Lekki', 3, NULL, 'sqm', NULL, '2025-12-17 10:47:15', '2025-12-17 10:47:15', 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 993033, 1149630, 1, 'bdbfdn nfnfdnf gn ffnfdnnnndn', 'Adamawa', 'Fufure', 4, NULL, 'sqm', NULL, '2025-12-18 00:51:02', '2025-12-18 00:51:02', 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 999003, 999004, 1, 'Test Property for Rental Durations - Test Address', 'Lagos', 'Ikeja', 10, NULL, NULL, NULL, '2025-12-20 19:35:03', '2025-12-20 19:35:03', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 578063, 6476849, 1, '9 palmleave down ilebu', 'Lagos', 'Ikorodu', 3, NULL, 'sqm', NULL, '2025-12-21 12:11:01', '2025-12-21 12:11:01', 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `property_amenity`
--

CREATE TABLE `property_amenity` (
  `property_id` bigint(20) UNSIGNED NOT NULL,
  `amenity_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property_attributes`
--

CREATE TABLE `property_attributes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) UNSIGNED NOT NULL COMMENT 'References prop_id in properties table',
  `attribute_key` varchar(100) NOT NULL,
  `attribute_value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property_types`
--

CREATE TABLE `property_types` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `property_types`
--

INSERT INTO `property_types` (`id`, `name`, `category`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Mansion', 'residential', 'Large residential property', 1, '2025-12-16 13:14:05', '2025-12-16 13:14:05');

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `referrer_id` bigint(20) UNSIGNED NOT NULL,
  `referred_id` bigint(20) UNSIGNED NOT NULL,
  `referral_level` tinyint(4) NOT NULL DEFAULT 1,
  `parent_referral_id` bigint(20) UNSIGNED DEFAULT NULL,
  `commission_tier` enum('super_marketer','marketer','direct') NOT NULL DEFAULT 'direct',
  `regional_rate_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`regional_rate_snapshot`)),
  `referral_code` varchar(50) DEFAULT NULL,
  `referral_status` enum('pending','active','completed','cancelled') NOT NULL DEFAULT 'pending',
  `property_id` bigint(20) UNSIGNED DEFAULT NULL,
  `commission_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `commission_status` enum('pending','approved','paid','cancelled') NOT NULL DEFAULT 'pending',
  `conversion_date` timestamp NULL DEFAULT NULL,
  `campaign_id` varchar(50) DEFAULT NULL,
  `referral_source` enum('link','qr_code','direct') NOT NULL DEFAULT 'link',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `tracking_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tracking_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_flagged` tinyint(1) NOT NULL DEFAULT 0,
  `fraud_indicators` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`fraud_indicators`)),
  `fraud_checked_at` timestamp NULL DEFAULT NULL,
  `authenticity_verified` tinyint(1) NOT NULL DEFAULT 0,
  `flag_reasons` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`flag_reasons`)),
  `flagged_at` timestamp NULL DEFAULT NULL
,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referral_campaigns`
--

CREATE TABLE `referral_campaigns` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketer_id` bigint(20) UNSIGNED NOT NULL,
  `campaign_name` varchar(255) NOT NULL,
  `campaign_code` varchar(50) NOT NULL,
  `qr_code_path` varchar(500) DEFAULT NULL,
  `target_audience` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','paused','completed','cancelled') NOT NULL DEFAULT 'active',
  `clicks_count` int(11) NOT NULL DEFAULT 0,
  `conversions_count` int(11) NOT NULL DEFAULT 0,
  `total_commission` decimal(10,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `tracking_params` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tracking_params`)),
  `performance_metrics` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`performance_metrics`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referral_chains`
--

CREATE TABLE `referral_chains` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `super_marketer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `marketer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `landlord_id` bigint(20) UNSIGNED NOT NULL,
  `chain_hash` varchar(64) NOT NULL,
  `status` enum('active','completed','broken','suspended') NOT NULL DEFAULT 'active',
  `commission_breakdown` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`commission_breakdown`)),
  `total_commission_percentage` decimal(5,4) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `activated_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referral_rewards`
--

CREATE TABLE `referral_rewards` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketer_id` bigint(20) UNSIGNED NOT NULL,
  `referral_id` bigint(20) UNSIGNED NOT NULL,
  `reward_type` enum('commission','bonus','milestone') NOT NULL DEFAULT 'commission',
  `amount` decimal(10,2) NOT NULL,
  `reward_level` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','approved','paid','cancelled') NOT NULL DEFAULT 'pending',
  `processed_at` timestamp NULL DEFAULT NULL,
  `processed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `payment_reference` varchar(255) DEFAULT NULL,
  `reward_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`reward_details`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `regional_scopes`
--

CREATE TABLE `regional_scopes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `scope_type` varchar(20) NOT NULL DEFAULT 'state',
  `scope_value` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `regional_scopes`
--

INSERT INTO `regional_scopes` (`id`, `user_id`, `scope_type`, `scope_value`, `created_at`, `updated_at`) VALUES
(1, 993033, 'state', 'Lagos', '2025-12-17 10:20:53', '2025-12-17 10:20:53');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `property_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`))
,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`, `display_name`, `description`, `is_active`, `permissions`) VALUES
(1, 'tenant', '2025-08-29 13:26:53', '2025-08-29 13:26:53', 'Tenant', 'Rents properties', 1, NULL),
(2, 'landlord', '2025-08-29 13:26:53', '2025-08-29 13:26:53', 'Landlord', 'Property owner', 1, NULL),
(3, 'marketer', '2025-08-29 13:26:53', '2025-08-29 13:26:53', 'Marketer', 'Handles marketing tasks', 1, NULL),
(4, 'super_marketer', '2025-09-12 06:34:40', '2025-09-12 06:34:40', 'Super Marketer', 'Top-tier marketer who can refer other marketers', 1, '[\"refer_marketers\",\"view_referral_analytics\",\"manage_referral_campaigns\",\"view_commission_breakdown\"]'),
(5, 'Artisan', '2025-08-05 15:31:30', '2025-08-05 15:31:30', 'Artisan', NULL, 1, NULL),
(6, 'property_manager', '2025-08-29 13:26:53', '2025-08-29 13:26:53', 'Property Manager', 'Manages properties', 1, NULL),
(7, 'admin', '2025-08-29 13:26:53', '2025-08-29 13:26:53', 'Administrator', 'Full system access', 1, NULL),
(8, 'Verified_Property_Manager', '2025-08-13 10:21:42', '2025-08-13 10:21:42', 'Verified Property Manager', 'Recognised by the company', 1, NULL),
(9, 'regional_manager', '2025-08-29 13:26:53', '2025-08-29 13:26:53', 'Regional Manager', 'Manages region-specific operations', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `role_assignment_audits`
--

CREATE TABLE `role_assignment_audits` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `actor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `legacy_role` varchar(255) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `reason` text DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_assignment_audits`
--

INSERT INTO `role_assignment_audits` (`id`, `actor_id`, `user_id`, `role_id`, `legacy_role`, `action`, `reason`, `meta`, `created_at`, `updated_at`) VALUES
(1, 993033, 993033, 8, NULL, 'assigned', 'Modern role assignment', NULL, '2025-12-16 14:40:39', '2025-12-16 14:40:39'),
(2, 993033, 993033, 9, NULL, 'assigned', 'Modern role assignment', NULL, '2025-12-17 10:20:53', '2025-12-17 10:20:53');

-- --------------------------------------------------------

--
-- Table structure for table `role_change_notifications`
--

CREATE TABLE `role_change_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` bigint(20) UNSIGNED NOT NULL COMMENT 'The ID of the admin who made the change',
  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT 'The ID of the user whose role was changed',
  `old_role` int(11) NOT NULL COMMENT 'The previous role ID',
  `new_role` int(11) NOT NULL COMMENT 'The new role ID',
  `ip_address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_user`
--

CREATE TABLE `role_user` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_user`
--

INSERT INTO `role_user` (`id`, `user_id`, `role_id`, `created_at`, `updated_at`) VALUES
(1, 993033, 8, NULL, NULL),
(2, 993033, 9, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `session_cleanup_history`
--

CREATE TABLE `session_cleanup_history` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cleanup_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expired_sessions_found` int(11) NOT NULL DEFAULT 0,
  `sessions_cleaned` int(11) NOT NULL DEFAULT 0,
  `invitations_expired` int(11) NOT NULL DEFAULT 0,
  `rate_limits_reset` int(11) NOT NULL DEFAULT 0,
  `cleanup_duration_seconds` decimal(8,3) NOT NULL DEFAULT 0.000,
  `cleanup_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`cleanup_details`)),
  `cleanup_type` enum('scheduled','manual','triggered') NOT NULL DEFAULT 'scheduled',
  `initiated_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `system_performance_overview`
-- (See below for the actual view)
--
CREATE TABLE `system_performance_overview` (
`date` date
,`daily_invitations` bigint(21)
,`daily_views` bigint(21)
,`daily_completions` bigint(21)
,`avg_daily_access` decimal(14,4)
,`high_access_invitations` bigint(21)
,`rate_limited_invitations` bigint(21)
,`unique_ips` bigint(21)
,`avg_time_to_view_minutes` decimal(24,4)
,`avg_time_to_payment_minutes` decimal(24,4)
);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `registration_source` varchar(255) DEFAULT NULL COMMENT 'Source of user registration: direct, easyrent_invitation, etc.',
  `referred_by` bigint(20) UNSIGNED DEFAULT NULL,
  `role` int(11) NOT NULL,
  `occupation` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `lga` varchar(255) DEFAULT NULL,
  `region` varchar(255) DEFAULT NULL,
  `admin` tinyint(1) DEFAULT NULL,
  `marketer_status` enum('pending','active','suspended','inactive') DEFAULT NULL,
  `commission_rate` decimal(5,2) DEFAULT NULL,
  `bank_account_name` varchar(255) DEFAULT NULL,
  `bank_account_number` varchar(50) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `bvn` varchar(11) DEFAULT NULL,
  `referral_code` varchar(20) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `flagged_for_review` tinyint(1) NOT NULL DEFAULT 0,
  `flag_reasons` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`flag_reasons`)),
  `flagged_at` timestamp NULL DEFAULT NULL,
  `fraud_risk_score` int(11) NOT NULL DEFAULT 0,
  `last_fraud_check` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_id`, `first_name`, `last_name`, `username`, `email`, `photo`, `registration_source`, `referred_by`, `role`, `occupation`, `phone`, `address`, `state`, `lga`, `region`, `admin`, `marketer_status`, `commission_rate`, `bank_account_name`, `bank_account_number`, `bank_name`, `bvn`, `referral_code`, `date_created`, `email_verified_at`, `flagged_for_review`, `flag_reasons`, `flagged_at`, `fraud_risk_score`, `last_fraud_check`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(8, 993033, 'kayode', 'abdullahi', 'kayoux', 'moshoodkayodeabdul@gmail.com', NULL, 'direct', NULL, 2, NULL, NULL, NULL, 'Lagos', 'Surulere', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 13:15:11', '2025-12-16 13:15:12', 0, NULL, NULL, 0, NULL, '$2y$10$/DcDU8KqHeqyl07dNkQdj.EeBt8ji2eb.JWultjuaS3pKJp46phTS', NULL, '2025-12-16 12:15:11', '2025-12-17 10:38:13'),
(9, 808169, 'kayode', 'abdul', 'kayouxxx', 'kagoor@easyrent.com', NULL, 'direct', NULL, 1, NULL, NULL, NULL, 'Lagos', 'Badagry', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-17 12:26:27', '2025-12-17 12:26:28', 0, NULL, NULL, 0, NULL, '$2y$10$i5ZXZXzcXfUr8Sw9TONMpeHFuNLsMV.Zr0RLKAIkQkVtvmw2hsqFS', NULL, '2025-12-17 11:26:27', '2025-12-17 11:26:27'),
(10, 869157, 'kenny', 'abdul', 'kaymoux', 'smith@easyrent.africa', NULL, 'direct', NULL, 1, 'carpenter', '08123435674', 'realway estate, garki abuja', 'FCT', 'Gwagwalada', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-17 14:18:09', '2025-12-17 14:18:10', 0, NULL, NULL, 0, NULL, '$2y$10$mLzEtpVFw1SaQ3HpZ8/ygetvPlUNKBHGehLGdrodZeZxF52air2dC', NULL, '2025-12-17 13:18:09', '2025-12-17 13:18:09'),
(11, 723417, 'smart', 'john', 'smith', 'smartjohn@smile.com', NULL, 'direct', NULL, 2, NULL, NULL, NULL, 'Adamawa', 'Ganye', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-17 17:21:22', '2025-12-17 17:21:23', 0, NULL, NULL, 0, NULL, '$2y$10$KEGoLGroFPBasAFSxmqlvuBJx9ADt5XQoqRY7Z8qu7631lyuE1Dni', NULL, '2025-12-17 16:21:22', '2025-12-17 16:21:22'),
(12, 426402, 'ismo', 'danfo', 'dance', 'ismo@gmail.com', NULL, 'direct', NULL, 1, NULL, NULL, NULL, 'Borno', 'Bayo', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-20 01:18:53', '2025-12-20 01:18:55', 0, NULL, NULL, 0, NULL, '$2y$10$3TVqFpiT2OaMJNU0B1ORt.I.Hxc2PEaGBXdclinJ7/v46sUZIwc/O', NULL, '2025-12-20 00:18:53', '2025-12-20 00:18:53'),
(13, 999003, 'Test', 'Landlord', 'testlandlord', 'test.landlord@example.com', NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-20 11:46:43', NULL, 0, NULL, NULL, 0, NULL, '$2y$10$gp5KsTA1UbqBODnMuBfxyO9jvXmOd0I86L85hfOSApBctSLrr7zYm', NULL, '2025-12-20 10:46:43', '2025-12-20 10:46:43'),
(14, 999003, 'Test', 'Landlord', 'testlandlord', 'test@example.com', NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-20 14:45:23', NULL, 0, NULL, NULL, 0, NULL, '$2y$10$/ylRDtNxYJMl8Usmvr0OiOnIgYJKBcWCVC5lfw0wCUtobxJR4d9bC', NULL, '2025-12-20 13:45:23', '2025-12-20 13:45:23'),
(15, 578063, 'kayode', 'abdul', 'kaboom', 'kingTusk@easyrent.africa', NULL, 'direct', NULL, 2, NULL, NULL, NULL, 'Lagos', 'Alimosho', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-20 22:26:44', '2025-12-20 22:26:45', 0, NULL, NULL, 0, NULL, '$2y$10$9r7AmOJaGbLv5VR8kVAcz.rjbX.j8btbMIMZdY10mOBOBEu29ZOaC', NULL, '2025-12-20 21:26:44', '2025-12-20 21:26:44'),
(16, 654965, 'andrew', 'doe', 'andoe', 'andrew@easyrent.africa', NULL, 'direct', NULL, 1, NULL, NULL, NULL, 'Adamawa', 'Gayuk', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-21 12:37:39', '2025-12-21 12:37:40', 0, NULL, NULL, 0, NULL, '$2y$10$XmSYLxnugCYQ3M2qCjiqluXkFdb/nFyUBbcyCnjxdPwfyjhQsaBnS', NULL, '2025-12-21 11:37:39', '2025-12-21 11:37:39'),
(17, 383708, 'kareem', 'abdullahi', 'kaabd', 'kareem@easyrent.africa', NULL, 'easyrent_invitation', NULL, 1, NULL, NULL, NULL, 'Abia', 'Ikwuano', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-21 13:17:56', '2025-12-21 13:17:57', 0, NULL, NULL, 0, NULL, '$2y$10$Nk0xDJRcNaWMDOzERq930.6rTMXrB9pJXWlzg2MwuT1aOIO3Oce0.', NULL, '2025-12-21 12:17:56', '2025-12-21 12:17:56'),
(18, 131348, 'kehinde', 'abdul', 'kinni', 'kinni@easyrent.africa', NULL, 'easyrent_invitation', NULL, 1, NULL, NULL, NULL, 'Lagos', 'Apapa', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-21 19:31:56', '2025-12-21 19:31:57', 0, NULL, NULL, 0, NULL, '$2y$10$4pzKl/wa5J/1U3pkIPtMGuCsWUWKK.NkJPq/m6noMhQU0/rJl1zLS', NULL, '2025-12-21 18:31:56', '2025-12-21 18:31:56');

-- --------------------------------------------------------

--
-- Structure for view `invitation_analytics`
--
DROP TABLE IF EXISTS `invitation_analytics`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `invitation_analytics`  AS SELECT cast(`apartment_invitations`.`created_at` as date) AS `date`, count(0) AS `total_created`, count(case when `apartment_invitations`.`status` = 'used' then 1 end) AS `total_used`, count(case when `apartment_invitations`.`status` = 'expired' then 1 end) AS `total_expired`, count(case when `apartment_invitations`.`viewed_at` is not null then 1 end) AS `total_viewed`, count(case when `apartment_invitations`.`payment_initiated_at` is not null then 1 end) AS `total_payment_initiated`, count(case when `apartment_invitations`.`payment_completed_at` is not null then 1 end) AS `total_payment_completed`, avg(`apartment_invitations`.`access_count`) AS `avg_access_count`, max(`apartment_invitations`.`access_count`) AS `max_access_count` FROM `apartment_invitations` WHERE `apartment_invitations`.`created_at` >= current_timestamp() - interval 90 day GROUP BY cast(`apartment_invitations`.`created_at` as date) ORDER BY cast(`apartment_invitations`.`created_at` as date) DESC ;

-- --------------------------------------------------------

--
-- Structure for view `invitation_conversion_funnel`
--
DROP TABLE IF EXISTS `invitation_conversion_funnel`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `invitation_conversion_funnel`  AS SELECT `ai`.`landlord_id` AS `landlord_id`, `u`.`first_name` AS `landlord_name`, `u`.`email` AS `landlord_email`, count(0) AS `total_invitations`, count(case when `ai`.`viewed_at` is not null then 1 end) AS `viewed_invitations`, count(case when `ai`.`payment_initiated_at` is not null then 1 end) AS `payment_initiated`, count(case when `ai`.`payment_completed_at` is not null then 1 end) AS `payment_completed`, round(count(case when `ai`.`viewed_at` is not null then 1 end) * 100.0 / count(0),2) AS `view_rate_percent`, round(count(case when `ai`.`payment_initiated_at` is not null then 1 end) * 100.0 / nullif(count(case when `ai`.`viewed_at` is not null then 1 end),0),2) AS `initiation_rate_percent`, round(count(case when `ai`.`payment_completed_at` is not null then 1 end) * 100.0 / nullif(count(case when `ai`.`payment_initiated_at` is not null then 1 end),0),2) AS `completion_rate_percent`, round(count(case when `ai`.`payment_completed_at` is not null then 1 end) * 100.0 / count(0),2) AS `overall_conversion_rate` FROM (`apartment_invitations` `ai` join `users` `u` on(`ai`.`landlord_id` = `u`.`user_id`)) WHERE `ai`.`created_at` >= current_timestamp() - interval 90 day GROUP BY `ai`.`landlord_id`, `u`.`first_name`, `u`.`email` HAVING `total_invitations` > 0 ORDER BY round(count(case when `ai`.`payment_completed_at` is not null then 1 end) * 100.0 / count(0),2) DESC, count(0) DESC ;

-- --------------------------------------------------------

--
-- Structure for view `invitation_security_monitoring`
--
DROP TABLE IF EXISTS `invitation_security_monitoring`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `invitation_security_monitoring`  AS SELECT `ai`.`id` AS `id`, `ai`.`invitation_token` AS `invitation_token`, `ai`.`apartment_id` AS `apartment_id`, `ai`.`access_count` AS `access_count`, `ai`.`rate_limit_count` AS `rate_limit_count`, `ai`.`last_accessed_ip` AS `last_accessed_ip`, `ai`.`last_accessed_at` AS `last_accessed_at`, `ai`.`created_at` AS `created_at`, CASE WHEN `ai`.`access_count` > 100 THEN 'high_access' WHEN `ai`.`rate_limit_count` >= 45 THEN 'rate_limit_warning' WHEN `ai`.`access_count` > 50 THEN 'moderate_access' ELSE 'normal' END AS `security_status`, timestampdiff(HOUR,`ai`.`created_at`,`ai`.`last_accessed_at`) AS `hours_since_creation`, timestampdiff(MINUTE,`ai`.`last_accessed_at`,current_timestamp()) AS `minutes_since_last_access` FROM `apartment_invitations` AS `ai` WHERE `ai`.`status` = 'active' AND `ai`.`access_count` > 0 ORDER BY CASE WHEN `ai`.`access_count` > 100 THEN 1 WHEN `ai`.`rate_limit_count` >= 45 THEN 2 WHEN `ai`.`access_count` > 50 THEN 3 ELSE 4 END ASC, `ai`.`access_count` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `landlord_invitation_dashboard`
--
DROP TABLE IF EXISTS `landlord_invitation_dashboard`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `landlord_invitation_dashboard`  AS SELECT `ai`.`landlord_id` AS `landlord_id`, `u`.`first_name` AS `landlord_name`, `u`.`email` AS `landlord_email`, count(0) AS `total_invitations`, count(case when `ai`.`status` = 'active' then 1 end) AS `active_invitations`, count(case when `ai`.`status` = 'used' then 1 end) AS `used_invitations`, count(case when `ai`.`status` = 'expired' then 1 end) AS `expired_invitations`, count(case when `ai`.`viewed_at` is not null then 1 end) AS `viewed_invitations`, count(case when `ai`.`payment_completed_at` is not null then 1 end) AS `completed_payments`, sum(`ai`.`total_amount`) AS `total_revenue`, avg(`ai`.`access_count`) AS `avg_access_count`, max(`ai`.`created_at`) AS `last_invitation_created`, max(`ai`.`last_accessed_at`) AS `last_activity` FROM (`apartment_invitations` `ai` join `users` `u` on(`ai`.`landlord_id` = `u`.`user_id`)) WHERE `ai`.`created_at` >= current_timestamp() - interval 90 day GROUP BY `ai`.`landlord_id`, `u`.`first_name`, `u`.`email` ORDER BY count(0) DESC, max(`ai`.`last_accessed_at`) DESC ;

-- --------------------------------------------------------

--
-- Structure for view `system_performance_overview`
--
DROP TABLE IF EXISTS `system_performance_overview`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `system_performance_overview`  AS SELECT cast(`ai`.`created_at` as date) AS `date`, count(0) AS `daily_invitations`, count(case when `ai`.`viewed_at` is not null then 1 end) AS `daily_views`, count(case when `ai`.`payment_completed_at` is not null then 1 end) AS `daily_completions`, avg(`ai`.`access_count`) AS `avg_daily_access`, count(case when `ai`.`access_count` > 50 then 1 end) AS `high_access_invitations`, count(case when `ai`.`rate_limit_count` > 0 then 1 end) AS `rate_limited_invitations`, count(distinct `ai`.`last_accessed_ip`) AS `unique_ips`, avg(timestampdiff(MINUTE,`ai`.`created_at`,`ai`.`viewed_at`)) AS `avg_time_to_view_minutes`, avg(timestampdiff(MINUTE,`ai`.`viewed_at`,`ai`.`payment_completed_at`)) AS `avg_time_to_payment_minutes` FROM `apartment_invitations` AS `ai` WHERE `ai`.`created_at` >= current_timestamp() - interval 30 day GROUP BY cast(`ai`.`created_at` as date) ORDER BY cast(`ai`.`created_at` as date) DESC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
  ADD KEY `idx_action_ip_created` (`action`,`ip_address`,`created_at`),
  ADD KEY `idx_user_action_created` (`user_id`,`action`,`created_at`);

--
-- Indexes for table `agent_ratings`
--
  ADD KEY `agent_ratings_agent_id_foreign` (`agent_id`),
  ADD KEY `agent_ratings_user_id_foreign` (`user_id`),
  ADD KEY `agent_ratings_property_id_foreign` (`property_id`);

--
-- Indexes for table `amenities`
--
ALTER TABLE `amenities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `apartments`
--
  ADD KEY `apartments_property_id_foreign` (`property_id`),
  ADD KEY `apartments_default_rental_type_index` (`default_rental_type`),
  ADD KEY `apartments_duration_index` (`duration`);

--
-- Indexes for table `apartment_invitations`
--
  ADD UNIQUE KEY `apartment_invitations_invitation_token_unique` (`invitation_token`),
  ADD KEY `apartment_invitations_invitation_token_index` (`invitation_token`),
  ADD KEY `apartment_invitations_apartment_id_index` (`apartment_id`),
  ADD KEY `apartment_invitations_status_index` (`status`),
  ADD KEY `apartment_invitations_expires_at_index` (`expires_at`),
  ADD KEY `apartment_invitations_landlord_id_index` (`landlord_id`),
  ADD KEY `apartment_invitations_authentication_required_index` (`authentication_required`),
  ADD KEY `apartment_invitations_registration_source_index` (`registration_source`),
  ADD KEY `apartment_invitations_session_expires_at_index` (`session_expires_at`),
  ADD KEY `apartment_invitations_access_count_index` (`access_count`),
  ADD KEY `apartment_invitations_last_accessed_at_index` (`last_accessed_at`),
  ADD KEY `apartment_invitations_last_accessed_ip_index` (`last_accessed_ip`),
  ADD KEY `apartment_invitations_rate_limit_count_index` (`rate_limit_count`),
  ADD KEY `apartment_invitations_rate_limit_reset_at_index` (`rate_limit_reset_at`),
  ADD KEY `idx_landlord_active_invitations` (`landlord_id`,`status`,`expires_at`),
  ADD KEY `idx_apartment_status_expiry` (`apartment_id`,`status`,`expires_at`),
  ADD KEY `idx_session_cleanup` (`session_expires_at`,`status`),
  ADD KEY `idx_security_tracking` (`last_accessed_ip`,`last_accessed_at`),
  ADD KEY `idx_tenant_payments` (`tenant_user_id`,`payment_completed_at`),
  ADD KEY `idx_analytics_reporting` (`created_at`,`status`,`landlord_id`),
  ADD KEY `idx_expiry_cleanup` (`status`,`expires_at`,`updated_at`),
  ADD KEY `idx_session_cleanup_efficient` (`session_expires_at`),
  ADD KEY `idx_batch_expiration` (`status`,`expires_at`,`updated_at`),
  ADD KEY `idx_rate_limit_cleanup` (`rate_limit_reset_at`,`rate_limit_count`),
  ADD KEY `idx_token_apartment_landlord` (`invitation_token`,`apartment_id`,`landlord_id`),
  ADD KEY `idx_referral_tracking` (`referral_source`,`payment_completed_at`),
  ADD KEY `idx_payment_reference` (`payment_reference`),
  ADD KEY `idx_dashboard_queries` (`landlord_id`,`created_at`,`status`,`viewed_at`),
  ADD KEY `idx_landlord_dashboard_full` (`landlord_id`,`status`,`created_at`,`expires_at`),
  ADD KEY `idx_payment_processing` (`apartment_id`,`tenant_user_id`,`payment_completed_at`),
  ADD KEY `idx_security_analytics` (`last_accessed_ip`,`access_count`,`created_at`),
  ADD KEY `idx_session_management` (`session_expires_at`,`authentication_required`,`status`);

--
-- Indexes for table `audit_logs`
--
  ADD KEY `audit_logs_user_id_performed_at_index` (`user_id`,`performed_at`),
  ADD KEY `audit_logs_model_type_model_id_index` (`model_type`,`model_id`),
  ADD KEY `audit_logs_performed_at_index` (`performed_at`),
  ADD KEY `audit_logs_audit_type_index` (`audit_type`),
  ADD KEY `audit_logs_reference_type_reference_id_index` (`reference_type`,`reference_id`);

--
-- Indexes for table `benefactors`
--
  ADD KEY `benefactors_email_type_index` (`email`,`type`),
  ADD KEY `benefactors_email_index` (`email`),
  ADD KEY `benefactors_user_id_foreign` (`user_id`);

--
-- Indexes for table `benefactor_payments`
--
  ADD UNIQUE KEY `benefactor_payments_payment_reference_unique` (`payment_reference`),
  ADD KEY `benefactor_payments_property_id_foreign` (`property_id`),
  ADD KEY `benefactor_payments_apartment_id_foreign` (`apartment_id`),
  ADD KEY `benefactor_payments_benefactor_id_status_index` (`benefactor_id`,`status`),
  ADD KEY `benefactor_payments_tenant_id_payment_type_index` (`tenant_id`,`payment_type`),
  ADD KEY `benefactor_payments_payment_reference_index` (`payment_reference`),
  ADD KEY `benefactor_payments_proforma_id_index` (`proforma_id`),
  ADD KEY `benefactor_payments_tenant_id_index` (`tenant_id`);

--
-- Indexes for table `blog`
--
  ADD UNIQUE KEY `blog_topic_url_unique` (`topic_url`),
  ADD KEY `blog_published_date_index` (`published`,`date`),
  ADD KEY `blog_topic_url_index` (`topic_url`);

--
-- Indexes for table `commission_payments`
--
  ADD UNIQUE KEY `commission_payments_payment_reference_unique` (`payment_reference`),
  ADD KEY `commission_payments_processed_by_foreign` (`processed_by`),
  ADD KEY `commission_payments_payment_status_index` (`payment_status`),
  ADD KEY `commission_payments_payment_method_index` (`payment_method`),
  ADD KEY `commission_payments_marketer_id_payment_status_index` (`marketer_id`,`payment_status`),
  ADD KEY `commission_payments_payment_date_index` (`payment_date`),
  ADD KEY `commission_payments_scheduled_date_index` (`scheduled_date`),
  ADD KEY `idx_commission_tier` (`commission_tier`),
  ADD KEY `idx_referral_chain` (`referral_chain_id`),
  ADD KEY `idx_parent_payment` (`parent_payment_id`),
  ADD KEY `idx_payment_region` (`region`);

--
-- Indexes for table `commission_rates`
--
  ADD KEY `idx_region_role` (`region`,`role_id`),
  ADD KEY `idx_effective_dates` (`effective_from`,`effective_until`),
  ADD KEY `idx_active_rates` (`is_active`,`effective_from`),
  ADD KEY `commission_rates_role_id_foreign` (`role_id`),
  ADD KEY `commission_rates_created_by_foreign` (`created_by`),
  ADD KEY `commission_rates_region_index` (`region`);

--
-- Indexes for table `complaints`
--
  ADD UNIQUE KEY `complaints_complaint_number_unique` (`complaint_number`),
  ADD KEY `complaints_tenant_id_status_index` (`tenant_id`,`status`),
  ADD KEY `complaints_landlord_id_status_index` (`landlord_id`,`status`),
  ADD KEY `complaints_status_priority_index` (`status`,`priority`),
  ADD KEY `complaints_created_at_index` (`created_at`);

--
-- Indexes for table `complaint_attachments`
--
  ADD KEY `complaint_attachments_complaint_id_created_at_index` (`complaint_id`,`created_at`),
  ADD KEY `complaint_attachments_uploaded_by_index` (`uploaded_by`);

--
-- Indexes for table `complaint_categories`
--
  ADD KEY `complaint_categories_is_active_priority_level_index` (`is_active`,`priority_level`);

--
-- Indexes for table `complaint_updates`
--
  ADD KEY `complaint_updates_complaint_id_created_at_index` (`complaint_id`,`created_at`),
  ADD KEY `complaint_updates_user_id_created_at_index` (`user_id`,`created_at`);

--
-- Indexes for table `durations`
--
  ADD UNIQUE KEY `durations_code_unique` (`code`),
  ADD KEY `durations_is_active_sort_order_index` (`is_active`,`sort_order`),
  ADD KEY `durations_code_index` (`code`);

--
-- Indexes for table `failed_jobs`
--
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `invitation_analytics_cache`
--
  ADD UNIQUE KEY `invitation_analytics_cache_cache_date_metric_type_unique` (`cache_date`,`metric_type`),
  ADD KEY `invitation_analytics_cache_cache_date_index` (`cache_date`),
  ADD KEY `invitation_analytics_cache_metric_type_index` (`metric_type`),
  ADD KEY `invitation_analytics_cache_last_updated_index` (`last_updated`);

--
-- Indexes for table `invitation_performance_metrics`
--
  ADD UNIQUE KEY `invitation_performance_metrics_metric_date_unique` (`metric_date`),
  ADD KEY `invitation_performance_metrics_metric_date_index` (`metric_date`),
  ADD KEY `idx_conversion_rate` (`conversion_rate_view_to_payment`);

--
-- Indexes for table `marketer_profiles`
--
  ADD KEY `marketer_profiles_user_id_foreign` (`user_id`),
  ADD KEY `marketer_profiles_kyc_status_index` (`kyc_status`),
  ADD KEY `marketer_profiles_verified_at_index` (`verified_at`);

--
-- Indexes for table `messages`
--
  ADD KEY `messages_sender_id_foreign` (`sender_id`),
  ADD KEY `messages_receiver_id_foreign` (`receiver_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `payments`
--
  ADD UNIQUE KEY `payments_transaction_id_unique` (`transaction_id`),
  ADD KEY `payments_landlord_id_foreign` (`landlord_id`),
  ADD KEY `idx_apartment_status` (`apartment_id`,`status`),
  ADD KEY `idx_paid_at_status` (`paid_at`,`status`),
  ADD KEY `payments_tenant_id_foreign` (`tenant_id`);

--
-- Indexes for table `payment_invitations`
--
  ADD UNIQUE KEY `payment_invitations_token_unique` (`token`),
  ADD KEY `payment_invitations_benefactor_id_foreign` (`benefactor_id`),
  ADD KEY `payment_invitations_token_status_index` (`token`,`status`),
  ADD KEY `payment_invitations_benefactor_email_status_index` (`benefactor_email`,`status`),
  ADD KEY `payment_invitations_benefactor_email_index` (`benefactor_email`),
  ADD KEY `payment_invitations_proforma_id_index` (`proforma_id`),
  ADD KEY `payment_invitations_tenant_id_index` (`tenant_id`);

--
-- Indexes for table `payment_tracking`
--
ALTER TABLE `payment_tracking`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `performance_logs`
--
ALTER TABLE `performance_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `profoma_receipt`
--
ALTER TABLE `profoma_receipt`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `properties`
--
  ADD KEY `idx_property_id` (`property_id`);

--
-- Indexes for table `property_attributes`
--
ALTER TABLE `property_attributes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `referral_campaigns`
--
ALTER TABLE `referral_campaigns`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `referral_chains`
--
ALTER TABLE `referral_chains`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `referral_rewards`
--
ALTER TABLE `referral_rewards`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `regional_scopes`
--
ALTER TABLE `regional_scopes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_assignment_audits`
--
ALTER TABLE `role_assignment_audits`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_change_notifications`
--
ALTER TABLE `role_change_notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_user`
--
ALTER TABLE `role_user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `session_cleanup_history`
--
ALTER TABLE `session_cleanup_history`
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
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `agent_ratings`
--
ALTER TABLE `agent_ratings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `amenities`
--
ALTER TABLE `amenities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `apartments`
--
ALTER TABLE `apartments`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `apartment_invitations`
--
ALTER TABLE `apartment_invitations`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `benefactors`
--
ALTER TABLE `benefactors`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `benefactor_payments`
--
ALTER TABLE `benefactor_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blog`
--
ALTER TABLE `blog`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `commission_payments`
--
ALTER TABLE `commission_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `commission_rates`
--
ALTER TABLE `commission_rates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `complaint_attachments`
--
ALTER TABLE `complaint_attachments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `complaint_categories`
--
ALTER TABLE `complaint_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `complaint_updates`
--
ALTER TABLE `complaint_updates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `durations`
--
ALTER TABLE `durations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `invitation_analytics_cache`
--
ALTER TABLE `invitation_analytics_cache`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invitation_performance_metrics`
--
ALTER TABLE `invitation_performance_metrics`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `marketer_profiles`
--
ALTER TABLE `marketer_profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `payment_invitations`
--
ALTER TABLE `payment_invitations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `payment_tracking`
--
ALTER TABLE `payment_tracking`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `performance_logs`
--
ALTER TABLE `performance_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `profoma_receipt`
--
ALTER TABLE `profoma_receipt`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `property_attributes`
--
ALTER TABLE `property_attributes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referral_campaigns`
--
ALTER TABLE `referral_campaigns`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referral_chains`
--
ALTER TABLE `referral_chains`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referral_rewards`
--
ALTER TABLE `referral_rewards`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `regional_scopes`
--
ALTER TABLE `regional_scopes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `role_assignment_audits`
--
ALTER TABLE `role_assignment_audits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `role_change_notifications`
--
ALTER TABLE `role_change_notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role_user`
--
ALTER TABLE `role_user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `session_cleanup_history`
--
ALTER TABLE `session_cleanup_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `apartments`
--
ALTER TABLE `apartments`
  ADD CONSTRAINT `apartments_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`property_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
