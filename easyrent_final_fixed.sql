-- Fixed SQL Dump for EasyRent Database
-- Key fixes:
-- 1. Added PRIMARY KEY and AUTO_INCREMENT to all CREATE TABLE statements
-- 2. Fixed duplicate primary keys in apartment_types and property_types
-- 3. Fixed apartment_invitations id field type
-- 4. Removed duplicate entries
-- 5. Fixed foreign key reference from apartments to properties

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

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs` 
--
ALTER TABLE `activity_logs` 
  ADD KEY `idx_action_ip_created` (`action`,`ip_address`,`created_at`),
  ADD KEY `idx_user_action_created` (`user_id`,`action`,`created_at`);

--
-- Indexes for table `apartments` 
--
ALTER TABLE `apartments` 
  ADD KEY `apartments_property_id_foreign` (`property_id`),
  ADD KEY `idx_property_id` (`property_id`);

--
-- Indexes for table `properties` 
--
ALTER TABLE `properties` 
  ADD UNIQUE KEY `idx_property_id` (`property_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs` 
--
ALTER TABLE `activity_logs` 
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `apartments` 
--
ALTER TABLE `apartments` 
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `apartment_invitations` 
--
ALTER TABLE `apartment_invitations` 
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `apartment_types` 
--
ALTER TABLE `apartment_types` 
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `properties` 
--
ALTER TABLE `properties` 
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `property_types` 
--
ALTER TABLE `property_types` 
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
