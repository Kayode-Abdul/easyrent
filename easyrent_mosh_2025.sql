-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 22, 2025 at 12:35 AM
-- Server version: 5.7.41-log
-- PHP Version: 8.1.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `easyrent_mosh_2025`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`, `updated_at`) VALUES
(1, 220035, 'profile_update', 'User updated their profile.', '127.0.0.1', '2025-11-27 09:15:50', '2025-11-27 09:15:50');

-- --------------------------------------------------------

--
-- Table structure for table `agent_ratings`
--

CREATE TABLE `agent_ratings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `agent_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `property_id` bigint(20) UNSIGNED DEFAULT NULL,
  `rating` tinyint(4) NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `amenities`
--

CREATE TABLE `amenities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `apartments`
--

CREATE TABLE `apartments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `property_id` bigint(20) UNSIGNED NOT NULL,
  `apartment_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `range_start` datetime DEFAULT NULL,
  `range_end` datetime DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `occupied` tinyint(1) NOT NULL DEFAULT '0',
  `apartment_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `apartments`
--

INSERT INTO `apartments` (`id`, `property_id`, `apartment_type`, `tenant_id`, `user_id`, `range_start`, `range_end`, `amount`, `occupied`, `apartment_id`, `created_at`, `updated_at`) VALUES
(1, 7370411, '2-Bedroom', 556462, 556462, '2025-10-24 16:04:38', '2026-10-24 16:04:38', NULL, 1, 7957916, '2025-09-17 16:49:19', NULL),
(2, 5418884, 'Penthouse', 556462, 216127, '2025-09-18 11:44:58', '2026-09-18 11:44:58', 1200000.00, 1, 4125970, '2025-09-18 10:44:58', NULL),
(3, 9694919, '2-Bedroom', 474793, 556462, '2025-12-09 17:18:26', '2025-12-09 17:18:26', NULL, 0, 6621490, '2025-09-24 08:37:36', NULL),
(4, 7370411, '3-Bedroom', 583698, 380161, '2025-09-25 12:40:35', '2026-09-25 12:40:35', 3400000.00, 1, 7637369, '2025-09-25 11:40:35', NULL),
(5, 7939612, '3-Bedroom', 380161, 556462, '2025-09-25 16:30:08', '2026-09-25 16:30:08', 1200000.00, 1, 5299442, '2025-09-25 15:30:08', NULL),
(6, 7939612, 'Penthouse', 380161, 556462, '2025-09-25 16:59:18', '2026-03-25 16:59:18', 800000.00, 1, 7312358, '2025-09-25 15:59:18', NULL),
(7, 7939612, 'Studio', 220035, 556462, '2025-09-29 23:39:10', '2026-03-29 23:39:10', 800000.00, 1, 6628559, '2025-09-29 22:39:10', NULL),
(8, 5762120, '3-Bedroom', 380161, 220035, '2025-09-30 09:26:25', '2026-09-30 09:26:25', 3400000.00, 1, 4250569, '2025-09-30 08:26:25', NULL),
(9, 3048478, 'Duplex', 474793, 380161, '2025-09-30 10:00:15', '2026-09-30 10:00:15', 1200000.00, 1, 9288868, '2025-09-30 09:00:15', NULL),
(10, 5762120, 'Duplex', 556462, 220035, '2025-09-30 16:18:52', '2026-09-30 16:18:52', 34000000.00, 1, 7214513, '2025-09-30 15:18:52', NULL),
(11, 7939612, 'Penthouse', 256829, 556462, '2025-10-02 08:50:15', '2026-10-02 08:50:15', 800000.00, 1, 5819400, '2025-10-02 07:50:15', NULL),
(12, 5762120, '2-Bedroom', 216127, 220035, '2025-10-03 09:04:18', '2026-10-03 09:04:18', 1200000.00, 1, 3426235, '2025-10-03 08:04:18', NULL),
(13, 8922302, '3-Bedroom', 583698, 216127, '2025-10-03 11:14:42', '2026-10-03 11:14:42', 2200000.00, 1, 1069481, '2025-10-03 10:14:42', NULL),
(14, 7939612, '1-Bedroom', 216127, 556462, '2025-10-03 15:28:05', '2026-10-03 15:28:05', 1200000.00, 1, 9833314, '2025-10-03 14:28:05', NULL),
(15, 999001, 'Test Apartment', 999001, 999002, '2025-10-09 16:51:01', '2026-10-09 16:51:01', 1000.00, 1, 999001, NULL, NULL),
(17, 9694919, 'Duplex', 582102, 556462, '2025-10-24 11:48:02', '2026-10-24 11:48:02', 4000000.00, 1, 9474919, '2025-10-24 10:48:02', NULL),
(18, 9694919, '1-Bedroom', 103440, 556462, '2025-10-24 12:09:27', '2026-10-24 12:09:27', 1200000.00, 1, 6160954, '2025-10-24 11:09:27', NULL),
(19, 3118957, 'Shop Unit', 256829, 556462, '2025-11-27 16:25:26', '2026-11-27 16:25:26', 800000.00, 1, 5787925, '2025-11-27 15:25:26', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `audit_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` bigint(20) DEFAULT NULL,
  `audit_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `performed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `benefactors`
--

CREATE TABLE `benefactors` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `relationship_type` enum('employer','parent','guardian','sponsor','organization','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `type` enum('registered','guest') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'guest',
  `is_registered` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `benefactor_payments`
--

CREATE TABLE `benefactor_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `benefactor_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `property_id` bigint(20) UNSIGNED DEFAULT NULL,
  `apartment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `proforma_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_type` enum('one_time','recurring') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'one_time',
  `status` enum('pending','completed','failed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `is_paused` tinyint(1) NOT NULL DEFAULT '0',
  `frequency` enum('monthly','quarterly','annually') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `next_payment_date` date DEFAULT NULL,
  `payment_day_of_month` int(11) DEFAULT NULL COMMENT 'Day of month for recurring payments (1-31)',
  `payment_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_metadata` text COLLATE utf8mb4_unicode_ci,
  `paid_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `paused_at` timestamp NULL DEFAULT NULL,
  `pause_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog`
--

CREATE TABLE `blog` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `topic` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `topic_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` text COLLATE utf8mb4_unicode_ci,
  `cover_photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Admin',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `hide` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog`
--

INSERT INTO `blog` (`id`, `topic`, `topic_url`, `content`, `excerpt`, `cover_photo`, `author`, `published`, `date`, `hide`, `created_at`, `updated_at`) VALUES
(1, 'Property Management Made Easy with EasyRent', 'property-management-made-easy-with-easyrent', 'Managing properties has never been easier. With EasyRent, landlords can automate rent collection, track payments, and manage tenants all from one dashboard. Our platform provides automated rent reminders via email, WhatsApp, and robocalls, ensuring you never miss a payment again. The integrated payment gateway makes it simple for tenants to pay their rent online, and you receive instant e-receipts for every transaction.', 'Managing properties has never been easier. With EasyRent, landlords can automate rent collection, track payments, and manage tenants all from one dash...', '/assets/images/image_1.jpg', 'Admin', 1, '2025-11-11 16:19:35', NULL, '2025-11-11 15:19:35', '2025-11-11 15:19:35'),
(2, 'Automated Rent Collection: The Future of Property Management', 'automated-rent-collection-the-future-of-property-management', 'Gone are the days of chasing tenants for rent payments. EasyRent\'s automated rent collection system sends timely reminders and provides multiple payment options for tenants. Our system generates e-invoices automatically and processes payments through secure gateways. Property managers can view all rent statuses in real-time and receive notifications for successful payments or overdue accounts.', 'Gone are the days of chasing tenants for rent payments. EasyRent\'s automated rent collection system sends timely reminders and provides multiple payme...', '/assets/images/image_2.jpg', 'Admin', 1, '2025-11-11 16:19:35', NULL, '2025-11-11 15:19:35', '2025-11-11 15:19:35'),
(3, 'Building Better Tenant Relationships', 'building-better-tenant-relationships', 'Strong tenant relationships are the foundation of successful property management. EasyRent helps facilitate better communication between landlords and tenants through our integrated messaging system. Tenants can easily report maintenance issues, make payment inquiries, and receive important updates. This transparency builds trust and reduces conflicts, leading to longer tenancy periods and better property care.', 'Strong tenant relationships are the foundation of successful property management. EasyRent helps facilitate better communication between landlords and...', '/assets/images/image_3.jpg', 'Admin', 1, '2025-11-11 16:19:35', NULL, '2025-11-11 15:19:35', '2025-11-11 15:19:35'),
(4, 'Start Your Side Hustle: Earn Through Property Referrals', 'start-your-side-hustle-earn-through-property-referrals', 'Did you know you can earn money by introducing landlords to EasyRent? Our referral program allows tenants, property managers, and affiliates to earn commissions for every landlord they bring to the platform. You earn a percentage of every rent payment processed through the platform for up to 5 years. It\'s a great way to start a side business while helping property owners discover better management solutions.', 'Did you know you can earn money by introducing landlords to EasyRent? Our referral program allows tenants, property managers, and affiliates to earn c...', '/assets/images/image_4.jpg', 'Admin', 1, '2025-11-11 16:19:35', NULL, '2025-11-11 15:19:35', '2025-11-11 15:19:35');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `property_id` bigint(20) UNSIGNED NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `amount` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commission_payments`
--

CREATE TABLE `commission_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `marketer_id` bigint(20) UNSIGNED NOT NULL,
  `referral_chain_id` bigint(20) UNSIGNED DEFAULT NULL,
  `commission_tier` enum('super_marketer','marketer','regional_manager') COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_payment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `regional_rate_applied` decimal(5,4) NOT NULL,
  `region` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_reference` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('bank_transfer','mobile_money','check') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bank_transfer',
  `payment_status` enum('pending','processing','completed','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_date` timestamp NULL DEFAULT NULL,
  `processed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `referral_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `payment_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scheduled_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `processing_started_at` timestamp NULL DEFAULT NULL,
  `processing_time_minutes` decimal(8,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commission_rates`
--

CREATE TABLE `commission_rates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `region` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `property_management_status` enum('managed','unmanaged') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unmanaged',
  `hierarchy_status` enum('with_super_marketer','without_super_marketer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'without_super_marketer',
  `super_marketer_rate` decimal(5,3) DEFAULT NULL,
  `marketer_rate` decimal(5,3) DEFAULT NULL,
  `regional_manager_rate` decimal(5,3) DEFAULT NULL,
  `company_rate` decimal(5,3) DEFAULT NULL,
  `total_commission_rate` decimal(5,3) NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `commission_percentage` decimal(5,4) NOT NULL,
  `effective_from` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `effective_until` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `commission_rates`
--

INSERT INTO `commission_rates` (`id`, `region`, `property_management_status`, `hierarchy_status`, `super_marketer_rate`, `marketer_rate`, `regional_manager_rate`, `company_rate`, `total_commission_rate`, `description`, `last_updated_at`, `updated_by`, `role_id`, `commission_percentage`, `effective_from`, `effective_until`, `created_by`, `is_active`, `created_at`, `updated_at`) VALUES
(3, 'default', 'unmanaged', 'without_super_marketer', NULL, 1.500, 0.250, 3.250, 5.000, 'Unmanaged properties without Super Marketer', '2025-09-15 15:44:14', NULL, 1, 5.0000, '2025-09-15 15:44:14', NULL, 1, 1, '2025-09-15 15:44:14', '2025-09-15 15:44:14'),
(4, 'default', 'unmanaged', 'with_super_marketer', 0.500, 1.000, 0.250, 3.250, 5.000, 'Unmanaged properties with Super Marketer', '2025-09-15 15:44:14', NULL, 1, 5.0000, '2025-09-15 15:44:14', NULL, 1, 1, '2025-09-15 15:44:14', '2025-09-15 15:44:14'),
(5, 'default', 'managed', 'without_super_marketer', NULL, 0.750, 0.100, 1.650, 2.500, 'Managed properties without Super Marketer', '2025-09-15 15:44:14', NULL, 1, 2.5000, '2025-09-15 15:44:14', NULL, 1, 1, '2025-09-15 15:44:14', '2025-09-15 15:44:14'),
(6, 'default', 'managed', 'with_super_marketer', 0.250, 0.500, 0.100, 1.650, 2.500, 'Managed properties with Super Marketer', '2025-09-15 15:44:14', NULL, 1, 2.5000, '2025-09-15 15:44:14', NULL, 1, 1, '2025-09-15 15:44:14', '2025-09-15 15:44:14'),
(7, 'lagos', 'unmanaged', 'without_super_marketer', NULL, 1.500, 0.250, 3.250, 5.000, 'Unmanaged properties without Super Marketer', '2025-09-15 15:44:14', NULL, 1, 5.0000, '2025-09-15 15:44:14', NULL, 1, 1, '2025-09-15 15:44:14', '2025-09-15 15:44:14'),
(8, 'lagos', 'unmanaged', 'with_super_marketer', 0.500, 1.000, 0.250, 3.250, 5.000, 'Unmanaged properties with Super Marketer', '2025-09-15 15:44:14', NULL, 1, 5.0000, '2025-09-15 15:44:14', NULL, 1, 1, '2025-09-15 15:44:14', '2025-09-15 15:44:14'),
(9, 'lagos', 'managed', 'without_super_marketer', NULL, 0.750, 0.100, 1.650, 2.500, 'Managed properties without Super Marketer', '2025-09-15 15:44:14', NULL, 1, 2.5000, '2025-09-15 15:44:14', NULL, 1, 1, '2025-09-15 15:44:14', '2025-09-15 15:44:14'),
(10, 'lagos', 'managed', 'with_super_marketer', 0.250, 0.500, 0.100, 1.650, 2.500, 'Managed properties with Super Marketer', '2025-09-15 15:44:14', NULL, 1, 2.5000, '2025-09-15 15:44:14', NULL, 1, 1, '2025-09-15 15:44:14', '2025-09-15 15:44:14'),
(11, 'abuja', 'unmanaged', 'without_super_marketer', NULL, 1.500, 0.250, 3.250, 5.000, 'Unmanaged properties without Super Marketer', '2025-09-15 15:44:14', NULL, 1, 5.0000, '2025-09-15 15:44:14', NULL, 1, 1, '2025-09-15 15:44:14', '2025-09-15 15:44:14'),
(12, 'abuja', 'unmanaged', 'with_super_marketer', 0.500, 1.000, 0.250, 3.250, 5.000, 'Unmanaged properties with Super Marketer', '2025-09-15 15:44:14', NULL, 1, 5.0000, '2025-09-15 15:44:14', NULL, 1, 1, '2025-09-15 15:44:14', '2025-09-15 15:44:14'),
(13, 'abuja', 'managed', 'without_super_marketer', NULL, 0.750, 0.100, 1.650, 2.500, 'Managed properties without Super Marketer', '2025-09-18 15:58:21', 556462, 1, 2.5000, '2025-09-18 16:58:21', NULL, 1, 1, '2025-09-15 15:44:14', '2025-09-18 15:58:21'),
(14, 'abuja', 'managed', 'with_super_marketer', 0.250, 0.500, 0.100, 1.650, 2.500, 'Managed properties with Super Marketer', '2025-09-15 15:44:14', NULL, 1, 2.5000, '2025-09-15 15:44:14', NULL, 1, 1, '2025-09-15 15:44:14', '2025-09-15 15:44:14'),
(15, 'kano', 'unmanaged', 'without_super_marketer', NULL, 1.500, 0.250, 3.250, 5.000, 'Unmanaged properties without Super Marketer', '2025-09-15 15:44:14', NULL, 1, 5.0000, '2025-09-15 15:44:14', NULL, 1, 1, '2025-09-15 15:44:14', '2025-09-15 15:44:14'),
(16, 'kano', 'unmanaged', 'with_super_marketer', 0.500, 1.000, 0.250, 3.250, 5.000, 'Unmanaged properties with Super Marketer', '2025-09-15 15:44:14', NULL, 1, 5.0000, '2025-09-15 15:44:14', NULL, 1, 1, '2025-09-15 15:44:14', '2025-09-15 15:44:14'),
(17, 'kano', 'managed', 'without_super_marketer', NULL, 0.750, 0.100, 1.650, 2.500, 'Managed properties without Super Marketer', '2025-09-15 15:44:14', NULL, 1, 2.5000, '2025-09-15 15:44:14', NULL, 1, 1, '2025-09-15 15:44:14', '2025-09-15 15:44:14'),
(18, 'kano', 'managed', 'with_super_marketer', 0.250, 0.500, 0.100, 1.650, 2.500, 'Managed properties with Super Marketer', '2025-09-15 15:44:14', NULL, 1, 2.5000, '2025-09-15 15:44:14', NULL, 1, 1, '2025-09-15 15:44:14', '2025-09-15 15:44:14'),
(19, 'port_harcourt', 'unmanaged', 'without_super_marketer', NULL, 1.500, 0.250, 3.250, 5.000, 'Unmanaged properties without Super Marketer', '2025-09-15 15:44:14', NULL, 1, 5.0000, '2025-09-15 15:44:14', NULL, 1, 1, '2025-09-15 15:44:14', '2025-09-15 15:44:14'),
(20, 'port_harcourt', 'unmanaged', 'with_super_marketer', 0.500, 1.000, 0.250, 3.250, 5.000, 'Unmanaged properties with Super Marketer', '2025-09-15 15:44:14', NULL, 1, 5.0000, '2025-09-15 15:44:14', NULL, 1, 1, '2025-09-15 15:44:14', '2025-09-15 15:44:14'),
(21, 'port_harcourt', 'managed', 'without_super_marketer', NULL, 0.750, 0.100, 1.650, 2.500, 'Managed properties without Super Marketer', '2025-09-15 15:44:14', NULL, 1, 2.5000, '2025-09-15 15:44:14', NULL, 1, 1, '2025-09-15 15:44:14', '2025-09-15 15:44:14'),
(22, 'port_harcourt', 'managed', 'with_super_marketer', 0.250, 0.500, 0.100, 1.650, 2.500, 'Managed properties with Super Marketer', '2025-09-15 15:44:14', NULL, 1, 2.5000, '2025-09-15 15:44:14', NULL, 1, 1, '2025-09-15 15:44:14', '2025-09-15 15:44:14'),
(23, 'ibadan', 'unmanaged', 'without_super_marketer', NULL, 1.500, 0.250, 3.250, 5.000, 'Unmanaged properties without Super Marketer', '2025-09-15 15:44:14', NULL, 1, 5.0000, '2025-09-15 15:44:14', NULL, 1, 1, '2025-09-15 15:44:14', '2025-09-15 15:44:14'),
(24, 'ibadan', 'unmanaged', 'with_super_marketer', 0.500, 1.000, 0.250, 3.250, 5.000, 'Unmanaged properties with Super Marketer', '2025-09-15 15:44:14', NULL, 1, 5.0000, '2025-09-15 15:44:14', NULL, 1, 1, '2025-09-15 15:44:14', '2025-09-15 15:44:14'),
(25, 'ibadan', 'managed', 'without_super_marketer', NULL, 0.750, 0.100, 1.650, 2.500, 'Managed properties without Super Marketer', '2025-09-15 15:44:14', NULL, 1, 2.5000, '2025-09-15 15:44:14', NULL, 1, 1, '2025-09-15 15:44:14', '2025-09-15 15:44:14'),
(26, 'ibadan', 'managed', 'with_super_marketer', 0.250, 0.500, 0.100, 1.650, 2.500, 'Managed properties with Super Marketer', '2025-09-15 15:44:14', NULL, 1, 2.5000, '2025-09-15 15:44:14', NULL, 1, 1, '2025-09-15 15:44:14', '2025-09-15 15:44:14');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `marketer_profiles`
--

CREATE TABLE `marketer_profiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `business_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `years_of_experience` int(11) DEFAULT NULL,
  `preferred_commission_rate` decimal(5,2) DEFAULT NULL,
  `marketing_channels` text COLLATE utf8mb4_unicode_ci,
  `target_regions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `kyc_status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `kyc_documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_media_handles` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_referrals` int(11) NOT NULL DEFAULT '0',
  `total_commission_earned` decimal(10,2) NOT NULL DEFAULT '0.00',
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sender_id` bigint(20) UNSIGNED NOT NULL,
  `receiver_id` bigint(20) UNSIGNED NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `subject`, `body`, `is_read`, `created_at`, `updated_at`) VALUES
(10, 380161, 583698, 'Rent Proforma', 'A new proforma receipt has been sent to you by simpson.\n\nDuration: 12 months\n\nYou can  <a href=\"http://localhost:8000/proforma/view/21\">view the proforma</a>', 1, '2025-09-25 16:50:00', '2025-09-25 16:52:33'),
(11, 556462, 220035, 'Rent Proforma', 'A new proforma receipt has been sent to you by kagoose.\n\nDuration: 12 months\n\nYou can  <a href=\"http://localhost:8000/proforma/view/23\">view the proforma</a>', 1, '2025-09-29 22:40:07', '2025-09-29 22:44:52'),
(12, 556462, 220035, 'Your Studio Rent', 'Proforma for your Studio rent. please check and click the pay button to make your payment', 1, '2025-09-29 22:42:51', '2025-09-29 22:44:31'),
(13, 220035, 380161, 'proforma created', 'I have created and sent a proforma to You. check your message', 0, '2025-09-30 08:30:43', '2025-09-30 08:30:43'),
(14, 220035, 380161, 'Rent Proforma', 'A new proforma receipt has been sent to you by jango.\n\nDuration: 12 months\n\nYou can  <a href=\"http://localhost:8000/proforma/view/25\">view the proforma</a>', 1, '2025-09-30 08:38:16', '2025-09-30 08:39:25'),
(15, 380161, 474793, 'Rent Proforma', 'A new proforma receipt has been sent to you by simpson.\n\nDuration: 12 months\n\nYou can  <a href=\"http://localhost:8000/proforma/view/27\">view the proforma</a>', 1, '2025-09-30 09:00:55', '2025-09-30 09:01:36'),
(16, 220035, 556462, 'Rent Proforma', 'A new proforma receipt has been sent to you by jango.\n\nDuration: 12 months\n\nYou can  <a href=\"http://localhost:8000/proforma/view/29\">view the proforma</a>', 1, '2025-09-30 15:19:58', '2025-09-30 15:21:01'),
(17, 556462, 220035, 'Proforma Rejected', 'Your proforma receipt for property has been rejected by the tenant.', 1, '2025-09-30 16:13:18', '2025-10-02 12:54:02'),
(18, 556462, 256829, 'Rent Proforma', 'A new proforma receipt has been sent to you by kagoose.\n\nDuration: 12 months\n\nYou can  <a href=\"http://localhost:8000/proforma/view/31\">view the proforma</a>', 1, '2025-10-02 07:51:46', '2025-10-02 07:52:24'),
(19, 256829, 556462, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 1, '2025-10-02 07:57:37', '2025-10-02 08:01:07'),
(20, 220035, 556462, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 1, '2025-10-02 12:54:41', '2025-10-03 08:00:22'),
(21, 220035, 216127, 'Rent Proforma', 'A new proforma receipt has been sent to you by jango.\n\nDuration: 12 months\n\nYou can  <a href=\"http://localhost:8000/proforma/view/33\">view the proforma</a>', 1, '2025-10-03 08:05:25', '2025-10-03 08:06:15'),
(22, 216127, 220035, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 1, '2025-10-03 08:06:47', '2025-10-03 08:58:13'),
(23, 220035, 216127, 'Rent Proforma', 'A new proforma receipt has been sent to you by jango.\n\nDuration: 12 months\n\nYou can  <a href=\"http://localhost:8000/proforma/view/34\">view the proforma</a>', 1, '2025-10-03 09:01:08', '2025-10-03 09:02:00'),
(24, 216127, 220035, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 1, '2025-10-03 09:02:13', '2025-10-03 10:19:09'),
(25, 216127, 583698, 'Rent Proforma', 'A new proforma receipt has been sent to you by jimali.\n\nDuration: 12 months\n\nYou can  <a href=\"http://localhost:8000/proforma/view/36\">view the proforma</a>', 1, '2025-10-03 10:15:55', '2025-10-03 10:19:51'),
(26, 583698, 216127, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 0, '2025-10-03 10:20:11', '2025-10-03 10:20:11'),
(27, 556462, 216127, 'Rent Proforma', 'A new proforma receipt has been sent to you by kagoose.\n\nDuration: 12 months\n\nYou can  <a href=\"http://localhost:8000/proforma/view/38\">view the proforma</a>', 1, '2025-10-03 14:29:10', '2025-10-03 14:29:53'),
(28, 216127, 556462, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 1, '2025-10-03 14:30:05', '2025-10-06 12:29:22'),
(29, 216127, 556462, 'Rent Proforma', 'A new proforma receipt has been sent to you by jimali.\n\nDuration: 12 months\n\nYou can  <a href=\"http://localhost:8000/proforma/view/39\">view the proforma</a>', 1, '2025-10-03 14:57:56', '2025-10-03 14:58:44'),
(30, 556462, 216127, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 1, '2025-10-03 14:58:49', '2025-10-03 15:02:58'),
(31, 556462, 474793, 'Rent Proforma', 'A new proforma receipt has been sent to you by kagoose.\n\nDuration: 12 months\n\nYou can  <a href=\"http://localhost:8000/proforma/view/40\">view the proforma</a>', 1, '2025-10-07 07:58:55', '2025-10-07 07:59:39'),
(32, 474793, 556462, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 1, '2025-10-07 08:00:39', '2025-10-07 11:31:07'),
(33, 216127, 583698, 'Rent Proforma', 'A new proforma receipt has been sent to you by jimali.\n\nDuration: 12 months\n\nYou can  <a href=\"http://localhost:8000/proforma/view/41\">view the proforma</a>', 1, '2025-10-07 08:11:44', '2025-10-07 08:12:55'),
(34, 583698, 216127, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 0, '2025-10-07 08:13:15', '2025-10-07 08:13:15'),
(35, 556462, 474793, 'Rent Proforma', 'A new proforma receipt has been sent to you by kagoose.\n\nDuration: 12 months\n\nYou can  <a href=\"http://localhost:8000/proforma/view/42\">view the proforma</a>', 1, '2025-10-07 11:31:55', '2025-10-07 11:33:03'),
(36, 474793, 556462, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 1, '2025-10-07 11:33:09', '2025-10-07 12:16:28'),
(37, 556462, 216127, 'Rent Proforma', 'A new proforma receipt has been sent to you by kagoose.\n\nDuration: 12 months\n\nYou can  <a href=\"http://localhost:8000/proforma/view/43\">view the proforma</a>', 1, '2025-10-07 12:17:02', '2025-10-07 12:19:05'),
(38, 556462, 216127, 'Rent Proforma', 'A new proforma receipt has been sent to you by kagoose.\n\nDuration: 12 months\n\nYou can  <a href=\"http://localhost:8000/proforma/view/44\">view the proforma</a>', 1, '2025-10-07 12:17:41', '2025-10-07 12:18:20'),
(39, 216127, 556462, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 1, '2025-10-07 12:18:29', '2025-11-12 16:08:06'),
(40, 216127, 556462, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 1, '2025-10-07 12:19:11', '2025-11-13 16:13:49'),
(41, 556462, 474793, 'Rent Proforma', 'A new proforma receipt has been sent to you by kagoose.\n\nDuration: 12 months\n\nYou can  <a href=\"http://localhost:8000/proforma/view/45\">view the proforma</a>', 1, '2025-10-07 14:41:55', '2025-10-07 14:42:45'),
(42, 474793, 556462, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 0, '2025-10-07 14:42:52', '2025-10-07 14:42:52'),
(43, 556462, 256829, 'Rent Proforma', 'A new proforma receipt has been sent to you by kagoose.\n\nDuration: 12 months\n\nYou can  <a href=\"http://localhost:8000/proforma/view/46\">view the proforma</a>', 1, '2025-10-07 14:52:49', '2025-10-07 14:53:50'),
(44, 256829, 556462, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 0, '2025-10-07 14:53:56', '2025-10-07 14:53:56'),
(45, 556462, 380161, 'Rent Proforma', 'A new proforma receipt has been sent to you by kagoose.\n\nDuration: 12 months\n\nYou can  <a href=\"http://localhost:8000/proforma/view/47\">view the proforma</a>', 1, '2025-10-07 15:00:19', '2025-10-07 15:02:01'),
(46, 380161, 556462, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 0, '2025-10-07 15:02:09', '2025-10-07 15:02:09'),
(47, 380161, 583698, 'Rent Proforma', 'A new proforma receipt has been sent to you by simpson.\n\nDuration: 12 months\n\nYou can  <a href=\"http://localhost:8000/proforma/view/48\">view the proforma</a>', 1, '2025-10-07 15:04:21', '2025-10-07 15:05:30'),
(48, 583698, 380161, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 0, '2025-10-07 15:05:43', '2025-10-07 15:05:43'),
(49, 380161, 256829, 'Rent Proforma', 'A new proforma receipt has been sent to you by simpson.\n\nDuration: 12 months\n\nYou can  <a href=\"http://localhost:8000/proforma/view/49\">view the proforma</a>', 1, '2025-10-07 15:21:43', '2025-10-07 15:23:43'),
(50, 256829, 380161, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 0, '2025-10-07 15:24:40', '2025-10-07 15:24:40'),
(51, 556462, 474793, 'Rent Proforma', 'A new proforma receipt has been sent to you by kagoose.\n\nDuration: 12 months\n\nYou can  <a href=\"http://localhost:8000/proforma/view/50\">view the proforma</a>', 1, '2025-10-07 15:42:12', '2025-10-07 15:43:01'),
(52, 474793, 556462, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 1, '2025-10-07 15:43:31', '2025-10-09 15:54:41'),
(53, 474793, 556462, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 0, '2025-10-07 15:46:57', '2025-10-07 15:46:57'),
(54, 556462, 474793, 'Rent Proforma', 'A new proforma receipt has been sent to you by kagoose.\n\nDuration: 12 months\n\nYou can  <a href=\"http://localhost:8000/proforma/view/51\">view the proforma</a>', 1, '2025-10-07 15:52:43', '2025-10-07 15:53:33'),
(55, 474793, 556462, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 0, '2025-10-07 15:53:40', '2025-10-07 15:53:40'),
(56, 556462, 220035, 'Rent Proforma', 'A new proforma receipt has been sent to you by kagoose.\n\nDuration: 12 months\n\nYou can  <a href=\"http://localhost:8000/proforma/view/52\">view the proforma</a>', 1, '2025-10-08 07:55:26', '2025-10-08 07:56:19'),
(57, 220035, 556462, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 1, '2025-10-08 07:56:29', '2025-10-09 15:54:00'),
(58, 556462, 256829, 'Rent Proforma', 'A new proforma receipt has been sent to you by kagoose.\n\nDuration: 12 months\n\nYou can  <a href=\"http://localhost:8000/proforma/view/53\">view the proforma</a>', 1, '2025-10-08 12:42:22', '2025-10-08 12:43:12'),
(59, 256829, 556462, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 0, '2025-10-08 12:43:20', '2025-10-08 12:43:20'),
(60, 556462, 220035, 'Rent Proforma', 'A new proforma receipt has been sent to you by kagoose.\n\nDuration: 12 months\n\nYou can  <a href=\"http://localhost:8000/proforma/view/55\">view the proforma</a>', 1, '2025-10-08 16:49:14', '2025-10-08 16:50:11'),
(61, 220035, 556462, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 1, '2025-10-08 16:50:18', '2025-11-20 09:29:36'),
(62, 556462, 474793, 'Rent Proforma', 'A new proforma receipt has been sent to you by kagoose.\n\nDuration: 12 months\n\nYou can  <a href=\"http://localhost:8000/proforma/view/59\">view the proforma</a>', 1, '2025-10-09 16:00:09', '2025-10-09 16:02:29'),
(63, 474793, 556462, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 1, '2025-10-09 16:03:13', '2025-10-09 17:14:02'),
(64, 474793, 474793, 'Rent Proforma', 'A new proforma receipt has been sent to you by khemox.\n\nDuration: 12 months\n\nYou can  <a href=\"http://localhost:8000/proforma/view/60\">view the proforma</a>', 1, '2025-10-09 16:57:52', '2025-10-09 16:58:09'),
(65, 474793, 474793, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 1, '2025-10-09 16:58:20', '2025-10-10 10:47:23'),
(66, 556462, 474793, 'Rent Proforma', 'A new proforma receipt has been sent to you by kagoose.\n\nDuration: 12 months\n\nYou can  <a href=\"http://localhost:8000/proforma/view/61\">view the proforma</a>', 1, '2025-10-10 10:42:42', '2025-10-10 10:43:20'),
(67, 474793, 556462, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 1, '2025-10-10 10:44:04', '2025-10-10 12:48:05'),
(68, 556462, 582102, 'Rent Proforma', 'A new proforma receipt has been sent to you by kagoose.\n\nDuration: 12 months\n\nYou can  <a  class=\"btn btn-primary\" href=\"http://localhost:8000/proforma/view/63\">view the proforma</a>', 1, '2025-10-24 10:49:08', '2025-10-24 10:50:07'),
(69, 582102, 556462, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 1, '2025-10-24 10:50:28', '2025-10-24 14:37:51'),
(70, 556462, 103440, 'Rent Proforma', 'A new proforma receipt has been sent to you by kagoose.\n\nDuration: 12 months\n\nYou can  <a  class=\"btn btn-primary\" href=\"http://localhost:8000/proforma/view/65\">view the proforma</a>', 1, '2025-10-24 11:09:45', '2025-10-24 11:10:31'),
(71, 103440, 556462, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 1, '2025-10-24 11:10:35', '2025-10-24 12:08:30'),
(72, 556462, 103440, 'Rent Proforma', 'A new proforma receipt has been sent to you by kagoose.\n\nDuration: 12 months\n\nYou can  <a  class=\"btn btn-primary\" href=\"http://localhost:8000/proforma/view/66\">view the proforma</a>', 1, '2025-10-24 12:39:58', '2025-10-24 12:40:44'),
(73, 103440, 556462, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 1, '2025-10-24 12:40:52', '2025-10-24 14:37:25'),
(74, 556462, 582102, 'Rent Proforma', 'A new proforma receipt has been sent to you by kagoose.\n\nDuration: 12 months\n\nYou can  <a  class=\"btn btn-primary\" href=\"http://localhost:8000/proforma/view/67\">view the proforma</a>', 1, '2025-10-24 14:40:58', '2025-10-24 14:41:31'),
(75, 582102, 556462, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 1, '2025-10-24 14:41:38', '2025-11-20 08:28:59'),
(76, 556462, 582102, 'Rent Proforma', 'A new proforma receipt has been sent to you by kagoose.\n\nDuration: 12 months\n\nYou can  <a  class=\"btn btn-primary\" href=\"http://localhost:8000/proforma/view/68\">view the proforma</a>', 1, '2025-10-24 14:45:42', '2025-10-24 14:46:24'),
(77, 582102, 556462, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 1, '2025-10-24 14:46:29', '2025-11-18 12:00:18'),
(78, 556462, 220035, 'Rent Proforma', 'A new proforma receipt has been sent to you by kagoose.\n\nDuration: 12 months\n\nYou can  <a  class=\"btn btn-primary\" href=\"http://localhost:8000/proforma/view/71\">view the proforma</a>', 1, '2025-11-12 16:12:37', '2025-11-12 16:13:21'),
(79, 220035, 556462, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 1, '2025-11-12 16:13:36', '2025-11-13 13:32:21'),
(80, 556462, 220035, 'Rent Proforma', 'A new proforma receipt has been sent to you by kagoose.\n\nDuration: 12 months\n\nYou can  <a  class=\"btn btn-primary\" href=\"http://localhost:8000/proforma/view/72\">view the proforma</a>', 1, '2025-11-18 12:03:24', '2025-11-18 12:05:30'),
(81, 220035, 556462, 'Proforma Accepted', 'Your proforma receipt for property has been accepted by the tenant.', 1, '2025-11-18 12:05:47', '2025-11-18 13:42:15');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2024_01_09_000000_create_properties_table', 1),
(6, '2024_01_09_000001_create_amenities_table', 1),
(7, '2024_01_09_000003_create_reviews_table', 1),
(8, '2024_01_09_000004_create_apartments_table', 1),
(9, '2024_01_09_000005_create_property_amenity_table', 1),
(10, '2025_06_02_000000_create_profoma_receipts_table', 1),
(11, '2025_06_10_000001_add_duration_to_profoma_receipt_table', 1),
(12, '2025_06_13_100000_create_activity_logs_table', 1),
(13, '2025_06_20_000000_create_messages_table', 1),
(14, '2025_06_30_152936_create_referrals_table', 1),
(15, '2025_07_01_000000_create_agent_ratings_table', 1),
(16, '2025_07_10_000000_create_payments_table', 1),
(17, '2025_07_22_092855_create_audit_logs_table', 1),
(18, '2025_07_22_153857_enhance_users_table_for_marketers', 1),
(19, '2025_07_22_153925_enhance_referrals_table_for_tracking', 1),
(20, '2025_07_22_154316_create_marketer_profiles_table', 1),
(21, '2025_07_22_154345_create_referral_campaigns_table', 1),
(22, '2025_07_22_154543_create_referral_rewards_table', 1),
(23, '2025_07_22_154803_create_commission_payments_table', 1),
(24, '2025_07_31_000000_create_bookings_table', 1),
(25, '2025_07_31_130000_add_clicks_to_referral_campaigns_table', 1),
(26, '2025_07_31_140000_add_conversions_to_referral_campaigns_table', 1),
(27, '2025_08_05_000000_create_roles_and_role_user_tables', 1),
(28, '2025_07_31_120000_create_role_user_table', 2),
(29, '2025_07_31_130000_add_reward_level_to_referral_rewards_table', 2),
(30, '2025_08_05_000000_add_role_and_region_to_users_table', 2),
(31, '2025_08_05_100000_add_region_to_users_table', 3),
(32, '2025_08_05_160731_remove_role_from_users_table', 3),
(33, '2025_08_13_000001_add_details_to_profoma_receipts_table', 4),
(34, '2025_08_19_000000_fix_role_user_table_user_id_reference', 5),
(35, '2025_08_26_130505_add_suspension_fields_to_properties_table', 6),
(36, '2025_08_27_100000_fix_role_user_user_id_data_type_safely', 7),
(37, '2025_08_29_133403_create_role_user_table_if_not_exists', 8),
(38, '2025_08_29_151200_alter_roles_add_missing_columns_if_not_exists', 9),
(39, '2025_09_02_100000_create_regional_scopes_table', 10),
(40, '2025_11_09_000005_extend_commission_payments_for_hierarchy', 11),
(41, '2025_11_09_000007_fix_referrals_primary_key', 12),
(42, '2025_11_09_000006_add_fraud_detection_fields', 13),
(43, '2025_09_12_153148_create_payment_tracking_table', 14),
(44, '2025_09_12_153307_add_processing_time_fields_to_commission_payments', 15),
(45, '2025_09_12_154759_add_monitoring_fields_to_audit_logs', 16),
(46, '2023_08_28_000000_create_role_change_notifications_table', 1),
(47, '2025_11_09_000001_create_commission_rates_table', 1),
(48, '2025_09_15_162433_update_commission_rates_for_property_management_structure', 1),
(49, '2025_09_17_000001_fix_messages_id_auto_increment', 17),
(50, '2025_09_19_000000_create_role_assignment_audits_table', 18),
(51, '2025_08_26_130153_add_suspension_fields_to_properties_table', 19),
(52, '2025_10_02_083941_add_amount_to_profoma_receipt_table', 20),
(53, '2025_10_09_123900_alter_payments_id_auto_increment', 21),
(54, '2025_11_11_161325_create_blog_table', 22),
(55, '2025_01_24_000000_update_payments_status_enum', 23),
(56, '2025_11_17_140322_create_benefactors_table', 24),
(57, '2025_11_17_140345_create_benefactor_payments_table', 25),
(58, '2025_11_17_140418_create_payment_invitations_table', 26),
(60, '2025_11_18_140304_add_phase1_features_to_benefactor_tables', 27),
(61, '2025_11_18_163107_add_proforma_link_to_payment_invitations_and_payments', 28),
(62, '2025_11_19_161940_fix_payment_invitations_foreign_keys', 29),
(64, '2025_11_25_094813_add_new_property_types_and_attributes', 30);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`email`, `token`, `created_at`) VALUES
('sherifat2@foundrepublic.com', '$2y$10$i27i01vKNuTsJtvPnfSq8O0akqM5VRA5M9RvgTeqWKL4ycM14ZdqC', '2025-08-28 10:57:07'),
('moshoodkayodeabdul@gmail.com', '$2y$10$rcYnAW6wDixJO2CCgmtFfex2NpRiRlSfwAZfyiDxoFE6KImSh9vV.', '2025-11-11 20:39:09');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `landlord_id` bigint(20) UNSIGNED NOT NULL,
  `apartment_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `duration` int(11) NOT NULL COMMENT 'Duration in months',
  `status` enum('pending','completed','success','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `payment_method` enum('card','bank_transfer','ussd') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `paid_at` timestamp NULL DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `transaction_id`, `tenant_id`, `landlord_id`, `apartment_id`, `amount`, `duration`, `status`, `payment_method`, `payment_reference`, `payment_meta`, `paid_at`, `due_date`, `created_at`, `updated_at`) VALUES
(2, 'ORAHkhChr6l09VXyFWGs0PFHp', 474793, 556462, 6621490, 1206000.00, 12, 'completed', 'card', 'ORAHkhChr6l09VXyFWGs0PFHp', NULL, '2025-10-09 16:03:29', NULL, '2025-10-09 16:03:29', '2025-10-09 16:03:29'),
(3, 'callback_sim_1761321878', 556462, 556462, 7957916, 80000.00, 12, 'completed', 'card', 'callback_sim_1761321878', NULL, '2025-10-24 15:04:38', NULL, '2025-10-24 15:04:38', '2025-10-24 15:04:38');

-- --------------------------------------------------------

--
-- Table structure for table `payment_invitations`
--

CREATE TABLE `payment_invitations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `benefactor_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `benefactor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `proforma_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','accepted','expired','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approval_status` enum('pending_approval','approved','declined') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending_approval',
  `expires_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `declined_at` timestamp NULL DEFAULT NULL,
  `decline_reason` text COLLATE utf8mb4_unicode_ci,
  `invoice_details` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_invitations`
--

INSERT INTO `payment_invitations` (`id`, `tenant_id`, `benefactor_email`, `benefactor_id`, `proforma_id`, `amount`, `token`, `status`, `approval_status`, `expires_at`, `accepted_at`, `approved_at`, `declined_at`, `decline_reason`, `invoice_details`, `created_at`, `updated_at`) VALUES
(4, 220035, 'moshoodkayodeabdul@gmail.com', NULL, 72, 1450000.00, 'K0k2mEOkQlE33TTvOqICTbTK2NiFsARrMlMwCV4fo66Mkcmsb2z8tPq90uqC96pS', 'pending', 'pending_approval', '2025-11-26 15:35:35', NULL, NULL, NULL, NULL, '{\"property_id\":null,\"apartment_id\":6628559,\"proforma_id\":\"72\",\"message\":null,\"tenant_name\":\"jango alinko\"}', '2025-11-19 15:35:35', '2025-11-19 15:35:35'),
(5, 220035, 'moshoodkayodeabdul@gmail.com', NULL, 72, 1450000.00, 'tRMpewTViZYtXmMsDjrzt2BnElkKupqkTamUQyONFvhoxQekImXYuwDVEBCxpWDP', 'pending', 'pending_approval', '2025-11-26 15:37:12', NULL, NULL, NULL, NULL, '{\"property_id\":null,\"apartment_id\":6628559,\"proforma_id\":\"72\",\"message\":null,\"tenant_name\":\"jango alinko\"}', '2025-11-19 15:37:12', '2025-11-19 15:37:12'),
(6, 220035, 'moshoodkayodeabdul@gmail.com', NULL, 72, 1450000.00, 'w13fbsZITjdz9E5FyTOW2g5WSrzWOZrP69LfaBtJxQPMUzmsbXVoPMfiIyjNkfnU', 'pending', 'pending_approval', '2025-11-26 17:02:16', NULL, NULL, NULL, NULL, '{\"property_id\":null,\"apartment_id\":6628559,\"proforma_id\":\"72\",\"message\":null,\"tenant_name\":\"jango alinko\"}', '2025-11-19 17:02:16', '2025-11-19 17:02:16'),
(7, 220035, 'moshoodkayodeabdul@gmail.com', NULL, 72, 1450000.00, 'cOHwY5qWxgnruf5eUgbunnGPlM22vIKZtvsELWNOEBYRV5kb1sGwCx7ulc3kKTXc', 'pending', 'pending_approval', '2025-11-26 17:11:08', NULL, NULL, NULL, NULL, '{\"property_id\":null,\"apartment_id\":6628559,\"proforma_id\":\"72\",\"message\":null,\"tenant_name\":\"jango alinko\"}', '2025-11-19 17:11:08', '2025-11-19 17:11:08');

-- --------------------------------------------------------

--
-- Table structure for table `payment_tracking`
--

CREATE TABLE `payment_tracking` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `payment_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `tracked_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `performance_logs`
--

CREATE TABLE `performance_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `method` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `route_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `controller_action` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_code` int(11) NOT NULL,
  `execution_time` decimal(8,2) NOT NULL,
  `memory_usage` bigint(20) NOT NULL,
  `query_count` int(11) NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `profoma_receipt`
--

CREATE TABLE `profoma_receipt` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apartment_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `security_deposit` decimal(15,2) DEFAULT NULL,
  `water` decimal(15,2) DEFAULT NULL,
  `internet` decimal(15,2) DEFAULT NULL,
  `generator` decimal(15,2) DEFAULT NULL,
  `other_charges_desc` text COLLATE utf8mb4_unicode_ci,
  `other_charges_amount` decimal(15,2) DEFAULT NULL,
  `total` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profoma_receipt`
--

INSERT INTO `profoma_receipt` (`id`, `user_id`, `tenant_id`, `status`, `transaction_id`, `apartment_id`, `amount`, `duration`, `security_deposit`, `water`, `internet`, `generator`, `other_charges_desc`, `other_charges_amount`, `total`, `created_at`, `updated_at`) VALUES
(22, 556462, 220035, 3, '6628559', 6628559, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-29 22:39:10', '2025-09-29 22:39:10'),
(23, 556462, 220035, 2, '9014759', 6628559, NULL, 12, 6800.00, NULL, NULL, NULL, NULL, NULL, 9606800.00, '2025-09-29 22:40:07', '2025-10-02 12:54:41'),
(24, 220035, 380161, 3, '4250569', 4250569, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-30 08:26:25', '2025-09-30 08:26:25'),
(25, 220035, 380161, 2, '8887821', 4250569, NULL, 12, NULL, NULL, 80000.00, NULL, 'You promised me #20000', 80000.03, 40960000.03, '2025-09-30 08:38:15', '2025-09-30 08:38:15'),
(26, 380161, 474793, 3, '9288868', 9288868, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-30 09:00:15', '2025-09-30 09:00:15'),
(27, 380161, 474793, 2, '6085761', 9288868, NULL, 12, NULL, NULL, 80000.00, NULL, NULL, NULL, 14480000.00, '2025-09-30 09:00:55', '2025-09-30 09:00:55'),
(28, 220035, 556462, 3, '7214513', 7214513, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-30 15:18:52', '2025-09-30 15:18:52'),
(29, 220035, 556462, 0, '6351459', 7214513, NULL, 12, NULL, NULL, NULL, NULL, 'monthly charges for light', 550000.00, 34550000.00, '2025-09-30 15:19:58', '2025-09-30 15:52:03'),
(30, 556462, 256829, 3, '5819400', 5819400, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-02 07:50:15', '2025-10-02 07:50:15'),
(31, 556462, 256829, 2, '1303269', 5819400, 800000.00, 12, NULL, 400000.00, NULL, NULL, NULL, NULL, 1200000.00, '2025-10-02 07:51:46', '2025-10-02 07:57:37'),
(32, 220035, 216127, 3, '3426235', 3426235, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-03 08:04:18', '2025-10-03 08:04:18'),
(33, 220035, 216127, 2, '3638057', 3426235, 1200000.00, 12, 4500.00, NULL, NULL, NULL, 'Note: the security deposit is a every month payment', NULL, 1204500.00, '2025-10-03 08:05:25', '2025-10-03 08:06:47'),
(34, 220035, 216127, 2, '7471880', 3426235, 1200000.00, 12, 50000.00, NULL, NULL, NULL, NULL, NULL, 1250000.00, '2025-10-03 09:01:08', '2025-10-03 09:02:13'),
(35, 216127, 583698, 3, '1069481', 1069481, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-03 10:14:42', '2025-10-03 10:14:42'),
(36, 216127, 583698, 2, '5673840', 1069481, 2200000.00, 12, 9000.00, NULL, NULL, NULL, 'Note: secuerity deposit charges occur every month', NULL, 2209000.00, '2025-10-03 10:15:55', '2025-10-03 10:20:11'),
(37, 556462, 216127, 3, '9833314', 9833314, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-03 14:28:05', '2025-10-03 14:28:05'),
(38, 556462, 216127, 2, '7312273', 9833314, 1200000.00, 12, 10500.00, NULL, NULL, NULL, NULL, NULL, 1210500.00, '2025-10-03 14:29:10', '2025-10-03 14:30:05'),
(39, 216127, 556462, 2, '5395185', 4125970, 1200000.00, 12, 10000.00, NULL, NULL, NULL, NULL, NULL, 1210000.00, '2025-10-03 14:57:56', '2025-10-03 14:58:49'),
(40, 556462, 474793, 2, '9274513', 6621490, 2000000.00, 12, NULL, NULL, NULL, 55000.00, 'Note: Generator payment is charged every month', NULL, 2055000.00, '2025-10-07 07:58:55', '2025-10-07 08:00:39'),
(41, 216127, 583698, 2, '4986947', 1069481, 4100000.00, 12, NULL, NULL, NULL, 2000000.00, NULL, NULL, 6100000.00, '2025-10-07 08:11:44', '2025-10-07 08:13:15'),
(42, 556462, 474793, 2, '5293828', 6621490, 1200000.00, 12, NULL, NULL, NULL, NULL, NULL, NULL, 1200000.00, '2025-10-07 11:31:55', '2025-10-07 11:33:09'),
(43, 556462, 216127, 2, '4912224', 9833314, 1200000.00, 12, NULL, NULL, NULL, NULL, NULL, NULL, 1200000.00, '2025-10-07 12:17:02', '2025-10-07 12:19:11'),
(44, 556462, 216127, 2, '1367765', 9833314, 1200000.00, 12, NULL, NULL, NULL, 77000.00, NULL, NULL, 1277000.00, '2025-10-07 12:17:41', '2025-10-07 12:18:29'),
(45, 556462, 474793, 2, '2178350', 6621490, 1600000.00, 12, NULL, NULL, NULL, NULL, NULL, NULL, 1600000.00, '2025-10-07 14:41:55', '2025-10-07 14:42:52'),
(46, 556462, 256829, 3, '8916749', 5819400, 1200000.00, 12, NULL, 60000.00, NULL, NULL, NULL, NULL, 1260000.00, '2025-10-07 14:52:49', '2025-10-07 14:53:56'),
(47, 556462, 380161, 2, '8695733', 5299442, 1200000.00, 12, NULL, NULL, NULL, NULL, NULL, NULL, 1200000.00, '2025-10-07 15:00:19', '2025-10-07 15:02:09'),
(48, 380161, 583698, 2, '6856128', 7637369, 3400000.00, 12, NULL, NULL, NULL, NULL, NULL, NULL, 3400000.00, '2025-10-07 15:04:21', '2025-10-07 15:05:43'),
(49, 380161, 256829, 2, '4937439', 7957916, 7000000.00, 12, NULL, NULL, NULL, NULL, NULL, NULL, 7000000.00, '2025-10-07 15:21:43', '2025-10-07 15:24:40'),
(50, 556462, 474793, 2, '1708448', 6621490, 1200000.00, 12, NULL, NULL, NULL, NULL, 'drainage(100000) + diesel(50,000)', 150000.00, 1350000.00, '2025-10-07 15:42:12', '2025-10-07 15:43:31'),
(51, 556462, 474793, 2, '1343777', 6621490, 1400000.00, 12, NULL, NULL, NULL, NULL, NULL, NULL, 1400000.00, '2025-10-07 15:52:43', '2025-10-07 15:53:40'),
(52, 556462, 220035, 2, '8958118', 6628559, 1200000.00, 12, 15000.00, NULL, NULL, NULL, NULL, NULL, 1215000.00, '2025-10-08 07:55:26', '2025-10-08 07:56:29'),
(53, 556462, 256829, 2, '8479932', 5819400, 1000000.00, 12, NULL, NULL, NULL, NULL, NULL, NULL, 1000000.00, '2025-10-08 12:42:22', '2025-10-08 12:43:20'),
(54, 999002, 999001, 2, 'test_reference_123', 999001, 1000.00, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-08 16:32:56', '2025-10-08 16:32:56'),
(55, 556462, 220035, 2, '1768761', 6628559, 800000.00, 12, NULL, NULL, NULL, NULL, NULL, NULL, 800000.00, '2025-10-08 16:49:14', '2025-10-08 16:50:18'),
(56, 999002, 999001, 2, 'test_ABC123', 999001, 1000.00, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-08 17:52:05', '2025-10-08 17:52:05'),
(57, 999002, 999001, 2, 'test_FIXDB001', 999001, 1000.00, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-09 11:38:49', '2025-10-09 11:38:49'),
(58, 999002, 999001, 2, 'test_FIXDB002', 999001, 1000.00, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-09 15:51:01', '2025-10-09 15:51:01'),
(59, 556462, 474793, 2, '4036010', 6621490, 1200000.00, 12, 6000.00, NULL, NULL, NULL, NULL, NULL, 1206000.00, '2025-10-09 16:00:09', '2025-10-09 16:03:13'),
(60, 474793, 474793, 2, '9504676', 6621490, 1200000.00, 12, NULL, NULL, NULL, NULL, NULL, NULL, 1200000.00, '2025-10-09 16:57:52', '2025-10-09 16:58:20'),
(61, 556462, 474793, 2, '5265231', 6621490, 4000000.00, 12, NULL, NULL, NULL, NULL, NULL, NULL, 4000000.00, '2025-10-10 10:42:42', '2025-10-10 10:44:04'),
(62, 556462, 582102, 3, '9474919', 9474919, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-24 10:48:02', '2025-10-24 10:48:02'),
(63, 556462, 582102, 2, '1590666', 9474919, 4000000.00, 12, 5000.00, NULL, NULL, NULL, NULL, NULL, 4005000.00, '2025-10-24 10:49:08', '2025-10-24 10:50:28'),
(64, 556462, 103440, 3, '6160954', 6160954, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-24 11:09:27', '2025-10-24 11:09:27'),
(65, 556462, 103440, 2, '6358425', 6160954, 1200000.00, 12, 9000.00, NULL, NULL, NULL, NULL, NULL, 1209000.00, '2025-10-24 11:09:45', '2025-10-24 11:10:35'),
(66, 556462, 103440, 2, '5567168', 6160954, 2000000.00, 12, NULL, 150000.00, NULL, NULL, NULL, NULL, 2150000.00, '2025-10-24 12:39:58', '2025-10-24 12:40:52'),
(67, 556462, 582102, 2, '1482950', 9474919, 4000000.00, 12, NULL, NULL, NULL, NULL, NULL, NULL, 4000000.00, '2025-10-24 14:40:58', '2025-10-24 14:41:38'),
(68, 556462, 582102, 2, '9681065', 9474919, 4000000.00, 12, NULL, NULL, NULL, NULL, NULL, NULL, 4000000.00, '2025-10-24 14:45:42', '2025-10-24 14:46:29'),
(69, 556462, 556462, 2, 'proforma_test_1761321878', 7957916, 60000.00, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-24 15:04:38', '2025-10-24 15:04:38'),
(70, 556462, 556462, 1, 'callback_sim_1761321878', 7957916, 80000.00, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-24 15:04:38', '2025-10-24 15:04:38'),
(71, 556462, 220035, 2, '6869943', 6628559, 1800000.00, 12, NULL, NULL, NULL, NULL, NULL, NULL, 1800000.00, '2025-11-12 16:12:37', '2025-11-12 16:13:36'),
(72, 556462, 220035, 2, '7662326', 6628559, 950000.00, 12, 500000.00, NULL, NULL, NULL, NULL, NULL, 1450000.00, '2025-11-18 12:03:24', '2025-11-18 12:05:47'),
(73, 556462, 256829, 3, '5787925', 5787925, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-27 15:25:26', '2025-11-27 15:25:26');

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `prop_id` bigint(20) UNSIGNED NOT NULL,
  `prop_type` tinyint(3) UNSIGNED NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lga` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `suspension_reason` text COLLATE utf8mb4_unicode_ci,
  `suspended_at` timestamp NULL DEFAULT NULL,
  `suspended_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reactivated_at` timestamp NULL DEFAULT NULL,
  `reactivated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `no_of_apartment` int(10) UNSIGNED DEFAULT NULL,
  `size_value` decimal(10,2) DEFAULT NULL COMMENT 'Size in square meters, acres, etc.',
  `size_unit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'sqm, sqft, acres, hectares',
  `agent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `user_id`, `prop_id`, `prop_type`, `address`, `state`, `lga`, `status`, `suspension_reason`, `suspended_at`, `suspended_by`, `reactivated_at`, `reactivated_by`, `no_of_apartment`, `size_value`, `size_unit`, `agent_id`, `created_at`, `updated_at`) VALUES
(7, 380161, 3048478, 2, '9 point road, Ikeja', 'Lagos', 'Ikeja', 'available', NULL, NULL, NULL, NULL, NULL, 3, NULL, NULL, NULL, '2025-09-12 16:41:10', NULL),
(8, 556462, 7939612, 2, '9 point road, Apapa', 'Lagos', 'Apapa', 'available', NULL, NULL, NULL, NULL, NULL, 2, NULL, NULL, 583698, '2025-09-12 16:56:56', NULL),
(9, 380161, 7370411, 2, '33 Adegoke Street', 'Anambra', 'Awka North', 'available', NULL, NULL, NULL, NULL, NULL, 3, NULL, NULL, 583698, '2025-09-12 17:45:39', NULL),
(10, 216127, 5418884, 1, '3 Nsala akpo Street', 'Abia', 'Isiala Ngwa South', 'available', NULL, NULL, NULL, NULL, NULL, 9, NULL, NULL, NULL, '2025-09-18 09:32:02', NULL),
(11, 556462, 9694919, 2, '63 rillow street, Ipaja', 'Kwara', 'Ilorin West', 'available', NULL, NULL, NULL, NULL, NULL, 8, NULL, NULL, 583698, '2025-09-19 13:33:49', NULL),
(12, 220035, 5762120, 2, '64 fulleni ako street', 'Kogi', 'Ajaokuta', 'available', NULL, NULL, NULL, NULL, NULL, 6, NULL, NULL, NULL, '2025-09-23 15:55:57', NULL),
(13, 216127, 8922302, 1, 'rgtgtg\r\ngyhyhth', 'Adamawa', 'Gayuk', 'available', NULL, NULL, NULL, NULL, NULL, 3, NULL, NULL, NULL, '2025-10-03 10:13:20', NULL),
(14, 556462, 3118957, 4, '33 Adegoke Street', 'Adamawa', 'Ganye', 'available', NULL, NULL, NULL, NULL, NULL, 3, 150.00, 'sqft', NULL, '2025-11-25 13:29:14', NULL),
(15, 476489, 3027209, 4, '9 point road, Apapa', 'Enugu', 'Enugu East', 'available', NULL, NULL, NULL, NULL, NULL, 2, NULL, 'sqm', NULL, '2025-11-28 13:51:21', NULL);

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
  `id` bigint(20) UNSIGNED NOT NULL,
  `property_id` bigint(20) UNSIGNED NOT NULL COMMENT 'References prop_id in properties table',
  `attribute_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attribute_value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `referrer_id` bigint(20) UNSIGNED NOT NULL,
  `referred_id` bigint(20) UNSIGNED NOT NULL,
  `referral_level` tinyint(4) NOT NULL DEFAULT '1',
  `parent_referral_id` bigint(20) UNSIGNED DEFAULT NULL,
  `commission_tier` enum('super_marketer','marketer','direct') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'direct',
  `regional_rate_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `is_flagged` tinyint(1) NOT NULL DEFAULT '0',
  `fraud_indicators` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `fraud_checked_at` timestamp NULL DEFAULT NULL,
  `authenticity_verified` tinyint(1) NOT NULL DEFAULT '0',
  `referral_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referral_status` enum('pending','active','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `property_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `commission_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `commission_status` enum('pending','approved','paid','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `conversion_date` timestamp NULL DEFAULT NULL,
  `campaign_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referral_source` enum('link','qr_code','direct') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'link',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tracking_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `referrals`
--

INSERT INTO `referrals` (`id`, `referrer_id`, `referred_id`, `referral_level`, `parent_referral_id`, `commission_tier`, `regional_rate_snapshot`, `is_flagged`, `fraud_indicators`, `fraud_checked_at`, `authenticity_verified`, `referral_code`, `referral_status`, `property_id`, `commission_amount`, `commission_status`, `conversion_date`, `campaign_id`, `referral_source`, `ip_address`, `user_agent`, `tracking_data`, `created_at`, `updated_at`) VALUES
(5, 556462, 380161, 1, NULL, 'direct', NULL, 0, NULL, NULL, 0, NULL, 'pending', NULL, 0.00, 'pending', NULL, NULL, 'link', NULL, NULL, NULL, '2025-09-12 12:19:44', '2025-09-12 12:19:44'),
(6, 380161, 256829, 1, NULL, 'direct', NULL, 0, NULL, NULL, 0, NULL, 'pending', NULL, 0.00, 'pending', NULL, NULL, 'link', NULL, NULL, NULL, '2025-09-18 09:10:01', '2025-09-18 09:10:01'),
(7, 380161, 216127, 1, NULL, 'direct', NULL, 0, NULL, NULL, 0, NULL, 'pending', NULL, 0.00, 'pending', NULL, NULL, 'link', NULL, NULL, NULL, '2025-09-18 09:26:53', '2025-09-18 09:26:53');

-- --------------------------------------------------------

--
-- Table structure for table `referral_campaigns`
--

CREATE TABLE `referral_campaigns` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `marketer_id` bigint(20) UNSIGNED NOT NULL,
  `clicks` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `conversions` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `campaign_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `campaign_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qr_code_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_audience` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','paused','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `clicks_count` int(11) NOT NULL DEFAULT '0',
  `conversions_count` int(11) NOT NULL DEFAULT '0',
  `total_commission` decimal(10,2) NOT NULL DEFAULT '0.00',
  `description` text COLLATE utf8mb4_unicode_ci,
  `tracking_params` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `performance_metrics` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referral_chains`
--

CREATE TABLE `referral_chains` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `super_marketer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `marketer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `landlord_id` bigint(20) UNSIGNED NOT NULL,
  `chain_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','completed','broken','suspended') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `commission_breakdown` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `total_commission_percentage` decimal(5,4) DEFAULT NULL,
  `region` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activated_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referral_rewards`
--

CREATE TABLE `referral_rewards` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `marketer_id` bigint(20) UNSIGNED NOT NULL,
  `indirect_referrer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `referral_id` bigint(20) UNSIGNED NOT NULL,
  `reward_type` enum('commission','bonus','milestone') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'commission',
  `reward_level` enum('direct','indirect') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'direct',
  `amount` decimal(10,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','approved','paid','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `processed_at` timestamp NULL DEFAULT NULL,
  `processed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `payment_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reward_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `regional_scopes`
--

CREATE TABLE `regional_scopes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `scope_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'state',
  `scope_value` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `regional_scopes`
--

INSERT INTO `regional_scopes` (`id`, `user_id`, `scope_type`, `scope_value`, `created_at`, `updated_at`) VALUES
(6, 380161, 'state', 'Lagos', '2025-09-12 14:24:35', '2025-09-12 14:24:35'),
(7, 380161, 'lga', 'Lagos::Apapa', '2025-09-12 14:24:35', '2025-09-12 14:24:35'),
(8, 380161, 'lga', 'Lagos::Ikeja', '2025-09-12 16:07:04', '2025-09-12 16:07:04'),
(9, 380161, 'lga', 'Lagos::Victoria Island', '2025-09-12 16:07:04', '2025-09-12 16:07:04'),
(10, 380161, 'state', 'Abuja', '2025-09-12 16:07:04', '2025-09-12 16:07:04'),
(11, 220035, 'state', 'Adamawa', '2025-09-23 15:28:06', '2025-09-23 15:28:06'),
(12, 220035, 'lga', 'Adamawa::Fufure', '2025-09-23 15:28:06', '2025-09-23 15:28:06'),
(13, 216127, 'state', 'Kwara', '2025-09-23 15:29:21', '2025-09-23 15:29:21'),
(14, 556462, 'state', 'Kogi', '2025-09-23 15:52:45', '2025-09-23 15:52:45'),
(15, 583698, 'state', 'Enugu', '2025-09-24 09:58:31', '2025-09-24 09:58:31'),
(16, 583698, 'state', 'Abuja', '2025-11-28 09:05:28', '2025-11-28 09:05:28'),
(17, 583698, 'state', 'Oyo', '2025-11-28 09:05:28', '2025-11-28 09:05:28');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `property_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `is_active`, `permissions`, `created_at`, `updated_at`) VALUES
(1, 'tenant', 'Tenant', 'Rents properties', 1, NULL, '2025-08-29 15:26:53', '2025-08-29 15:26:53'),
(2, 'landlord', 'Landlord', 'Property owner', 1, NULL, '2025-08-29 15:26:53', '2025-08-29 15:26:53'),
(3, 'marketer', 'Marketer', 'Handles marketing tasks', 1, NULL, '2025-08-29 15:26:53', '2025-08-29 15:26:53'),
(4, 'super_marketer', 'Super Marketer', 'Top-tier marketer who can refer other marketers', 1, '[\"refer_marketers\",\"view_referral_analytics\",\"manage_referral_campaigns\",\"view_commission_breakdown\"]', '2025-09-12 08:34:40', '2025-09-12 08:34:40'),
(5, 'Artisan', 'Artisan', NULL, 1, NULL, '2025-08-05 17:31:30', '2025-08-05 17:31:30'),
(6, 'property_manager', 'Property Manager', 'Manages properties', 1, NULL, '2025-08-29 15:26:53', '2025-08-29 15:26:53'),
(7, 'admin', 'Administrator', 'Full system access', 1, NULL, '2025-08-29 15:26:53', '2025-08-29 15:26:53'),
(8, 'Verified_Property_Manager', 'Verified Property Manager', 'Recognised by the company', 1, NULL, '2025-08-13 12:21:42', '2025-08-13 12:21:42'),
(9, 'regional_manager', 'Regional Manager', 'Manages region-specific operations', 1, NULL, '2025-08-29 15:26:53', '2025-08-29 15:26:53');

-- --------------------------------------------------------

--
-- Table structure for table `role_assignment_audits`
--

CREATE TABLE `role_assignment_audits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `actor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `legacy_role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_assignment_audits`
--

INSERT INTO `role_assignment_audits` (`id`, `actor_id`, `user_id`, `role_id`, `legacy_role`, `action`, `reason`, `meta`, `created_at`, `updated_at`) VALUES
(1, 556462, 220035, 9, NULL, 'assigned', 'Modern role assignment', NULL, '2025-09-23 15:28:06', '2025-09-23 15:28:06'),
(2, 556462, 216127, 9, NULL, 'assigned', 'Modern role assignment', NULL, '2025-09-23 15:29:21', '2025-09-23 15:29:21'),
(3, 556462, 256829, 8, NULL, 'assigned', 'Modern role assignment', NULL, '2025-09-23 15:34:48', '2025-09-23 15:34:48'),
(4, 556462, 556462, 9, NULL, 'assigned', 'Modern role assignment', NULL, '2025-09-23 15:52:45', '2025-09-23 15:52:45'),
(5, 556462, 556462, 9, NULL, 'assigned', 'Modern role assignment', NULL, '2025-09-24 08:17:42', '2025-09-24 08:17:42'),
(6, 556462, 583698, 9, NULL, 'assigned', 'Modern role assignment', NULL, '2025-09-24 09:58:31', '2025-09-24 09:58:31');

-- --------------------------------------------------------

--
-- Table structure for table `role_change_notifications`
--

CREATE TABLE `role_change_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `old_role` int(11) NOT NULL,
  `new_role` int(11) NOT NULL,
  `timestamp` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_user`
--

CREATE TABLE `role_user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_user`
--

INSERT INTO `role_user` (`id`, `user_id`, `role_id`, `created_at`, `updated_at`) VALUES
(19, 380161, 8, NULL, NULL),
(20, 380161, 9, NULL, NULL),
(21, 556462, 8, NULL, NULL),
(25, 220035, 9, NULL, NULL),
(26, 216127, 9, NULL, NULL),
(28, 556462, 9, NULL, NULL),
(29, 583698, 9, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` int(11) NOT NULL,
  `occupation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lga` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `region` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `flagged_for_review` tinyint(1) NOT NULL DEFAULT '0',
  `flag_reasons` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `flagged_at` timestamp NULL DEFAULT NULL,
  `fraud_risk_score` int(11) NOT NULL DEFAULT '0',
  `last_fraud_check` timestamp NULL DEFAULT NULL,
  `admin` tinyint(1) DEFAULT NULL,
  `marketer_status` enum('pending','active','suspended','inactive') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `commission_rate` decimal(5,2) DEFAULT NULL,
  `bank_account_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bvn` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referral_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_id`, `first_name`, `last_name`, `username`, `email`, `photo`, `role`, `occupation`, `phone`, `address`, `state`, `lga`, `region`, `flagged_for_review`, `flag_reasons`, `flagged_at`, `fraud_risk_score`, `last_fraud_check`, `admin`, `marketer_status`, `commission_rate`, `bank_account_name`, `bank_account_number`, `bank_name`, `bvn`, `referral_code`, `date_created`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(15, 556462, 'Kayode', 'Abdullahi', 'kagoose', 'moshoodkayodeabdul@gmail.com', NULL, 6, 'Plumber', '08052345312', '9 point road, Apapa', 'Lagos', 'Apapa', NULL, 0, NULL, NULL, 0, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 10:14:44', '2025-10-31 03:46:23', '$2y$10$2Gqp0eGWVGppE6dxmPxxnOaTYq/H9/jD24y8WrGDS3ieQganCJw0K', NULL, '2025-09-12 09:14:44', '2025-10-31 03:46:23'),
(16, 380161, 'samson', 'simmy', 'simpson', 'simpson@easyrent.com', 'assets/photos/user_1757683184_68c41df019a1d.jpg', 8, 'Mechanic', '08034099844', '33 Adegoke Street', 'Lagos', 'Surulere', NULL, 0, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-12 10:19:44', NULL, '$2y$10$HiKknrPUnzoF8DIIf5uxdOaS0WknEgv2HZ68eP1Xg948M3Y2xzqbm', NULL, '2025-09-12 09:19:44', '2025-09-12 11:05:10'),
(17, 474793, 'khemo', 'hammer', 'khemox', 'tenant1@easyrent.com', NULL, 3, 'Mechanic', '08034099844', '33 Abode Street', 'Lagos', 'Lagos Island', NULL, 0, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-16 11:36:00', NULL, '$2y$10$5N47cYHUi7uB9vy4afi7GetbwdmBqvJvTcfUbC3cY4ujEgzs2pAeO', NULL, '2025-09-16 10:36:00', '2025-09-16 10:36:00'),
(18, 256829, 'ismail', 'kashi', 'ismo', 'ismo@easyrent.com', NULL, 2, 'Mechanic', '08047892343', '9 saint obi curve avenue', 'Lagos', 'Ikeja', NULL, 0, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-18 07:10:01', NULL, '$2y$10$DHt1a./4qDiyMMK2R.tyUO8gjfPmPNwOOuNI/ZzC87du2YE3a0huK', NULL, '2025-09-18 06:10:01', '2025-09-18 06:10:01'),
(19, 216127, 'jimoh', 'ali', 'jimali', 'jimoh@easyrent.com', NULL, 2, 'Mechanic', '08190477894', '9  alimosho', 'Lagos', 'Badagry', NULL, 0, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-18 07:26:53', '2025-10-31 03:46:20', '$2y$10$JDpOvOed3bkgkm2eniXWvuJ7ktgHb5gesXK.tnBTtB3p2y6L7IcnS', NULL, '2025-09-18 06:26:53', '2025-09-18 06:26:53'),
(20, 220035, 'jango', 'alinko', 'jango', 'jango@easyrent.com', NULL, 5, 'Plumber', '08034094444', '3 smart icon Street', 'Akwa Ibom', 'Ikot Ekpene', NULL, 0, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-23 13:13:49', '2025-10-31 03:46:25', '$2y$10$LE97Vjk1DJmIRaay6wrVWuDQAuqxAHSiB3VImK12h9U5y0.OHNXr6', NULL, '2025-09-23 12:13:49', '2025-09-23 12:13:49'),
(21, 583698, 'chima', 'chima', 'daDaoc', 'chima@easyrent.com', NULL, 6, 'Doctor', '080523444444', '33 Adegoke Street', 'Enugu', 'Awgu', NULL, 0, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-24 07:56:42', '2025-11-04 10:17:59', '$2y$10$01O0k7S9fXRHBmbeS/FNxeAKMOdxQ/t9HGuoI1uXLATh0zS8F1fB.', 'LRCRhBy5sFzZjPf5ynIuLdO7eFc7hat18I3wMsJ9eXqPIuyLMO2uYyo6iy70', '2025-09-24 06:56:42', '2025-09-24 06:56:42'),
(22, 699232, 'Kayode', 'olaiya', 'admin', 'moshoodkayodeabdul@ail.com', NULL, 1, 'Electrician', '08052345312', '9 point road, Apapa', 'Lagos', 'Amuwo-Odofin', NULL, 0, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-08 14:56:59', NULL, '$2y$10$nkGlEtj00mVuZHIyY81P1OZV8zLyy7s9f1bnyErWxhJFmuzPpoyOm', NULL, '2025-10-08 11:56:59', '2025-10-08 11:56:59'),
(23, 788310, 'Kayode', 'Adebisi', 'admon', 'adidas@easyrent.com', NULL, 6, 'Project Manager', '08052345312', '49 point road, Ipaja', 'Lagos', 'Amuwo-Odofin', NULL, 0, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-08 15:15:59', NULL, '$2y$10$lirWuTWkDf9yH6dmvXZDxe5Z0cSVKyj.pX/5kodn80FQG746lw.Ee', NULL, '2025-10-08 12:15:59', '2025-10-08 12:15:59'),
(24, 450418, 'Michael', 'Onyeiwu', 'Mikel', 'onyeiwumichael77@gmail.com', NULL, 1, 'Entrepreneur', '09113696648', '6 Balogun Close', 'Lagos', 'Surulere', NULL, 0, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-10 07:50:00', NULL, '$2y$10$kgAwsLPb9F5PdE2z77.e6uPjRfRnJOGlXPESxBs8fPnL4HubVUepK', NULL, '2025-10-10 04:50:00', '2025-10-10 04:50:00'),
(25, 587365, 'Kingsley', 'Slayer', 'King', 'king@sley.com', 'assets/photos/user_1760118602_68e9474a82b82.jpg', 3, 'Engineer', '08097654345', '9 point road, Apapa', 'Lagos', 'Ikorodu', NULL, 0, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-10 14:50:05', NULL, '$2y$10$hd9gL4rvgd35GkV1ocwVT.cEpUQpK/Ea3HzU1LqPNNN6/GaWkqtk.', NULL, '2025-10-10 11:50:05', '2025-10-10 11:50:05'),
(26, 622356, 'stanley', 'chima', 'stanchim', 'stanley@easyrent.com', 'assets/photos/user_1761306519_68fb67971758b.png', 1, 'Electrician', '09078654567', '3 trinity road', 'Lagos', 'Ajeromi-Ifelodun', NULL, 0, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-24 08:48:39', NULL, '$2y$10$g2BFlj2UzSxbwh7qS2KdSOm5NtZMnUxDuatG6ac1PeZWHeC9NDMcq', NULL, '2025-10-24 05:48:39', '2025-10-24 05:48:39'),
(27, 192304, 'Dennis', 'Ogi', 'Underlord', 'dennisogi15@gmail.com', NULL, 2, 'Software developer', '09057272461', '13 Franklin street', 'Lagos', 'Surulere', NULL, 0, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-29 14:51:08', NULL, '$2y$10$V9QWjKihypYHKrm.GdH.dO.GM3KsOCxtibiMaSMh5T1TPVcAqGTNy', NULL, '2025-10-29 11:51:08', '2025-10-29 11:51:08'),
(28, 404124, 'Kayode', 'Abdullahi', 'kanayo', 'kagoose2002@gmail.com', NULL, 6, 'Project Manager', '08052345555', '9 point road, Apapa', 'Lagos', 'Amuwo-Odofin', NULL, 0, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-31 06:56:56', NULL, '$2y$10$JIpzKJ32JCKnzJ2Ivrynn.9EZfxKChw40YQ8UGfr8djvFSbniRFTG', NULL, '2025-10-31 03:56:55', '2025-10-31 03:56:55'),
(29, 116587, 'CHIMAROKE', 'UDEICHI', 'DR UDEICHI', 'chimarhotex@gmail.com', NULL, 1, NULL, '08086675053', '12b Ukpor Crescent, Independence Layout, Independence Layout', 'Enugu', 'Enugu North', NULL, 0, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-04 13:10:30', '2025-11-04 10:17:59', '$2y$10$kVekRlHNuzDFKXwtEsp5B.zLWoj5ZiXt8r6Vx/oIqhPICzPq9ZdNa', NULL, '2025-11-04 10:10:30', '2025-11-04 10:17:59'),
(30, 476489, 'ktester', 'nexttest', 'Mr Tester', 'tester@easyrent.africa', NULL, 2, 'Actor/Actress', '08052345300', '9 point road, Ishegun', 'Enugu', 'Enugu East', NULL, 0, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-28 16:32:55', '2025-11-20 10:17:59', '$2y$10$xYQTMtNsnjUWJz68r0Q98OL.f80hlAD3yH/t4k7du.v0MaASwLjnC', NULL, '2025-11-28 13:32:55', '2025-11-28 13:32:55'),
(31, 525723, 'Komolafe', 'Idris', 'Kommon', 'kommon@gmail.com', NULL, 2, NULL, NULL, NULL, 'Lagos', 'Alimosho', NULL, 0, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-02 14:13:03', NULL, '$2y$10$DvXx2aMG7yRjj3HjTUE0JeDH.x7ZzMDPu.4ilBGhre98wxkO6Zn6.', NULL, '2025-12-02 11:13:03', '2025-12-02 11:13:03'),
(32, 184926, 'Komolafe', 'Idris', 'Komon', 'komon@gmail.com', NULL, 2, 'Project Manager', '08052345312', '9 point road, Apapa', 'Lagos', 'Alimosho', NULL, 0, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-02 14:14:29', NULL, '$2y$10$01gbfDt/520EKWnHNkKL5uEZk0Plrn9b2UcKAWQ5yIe3Jg5vmYZsa', NULL, '2025-12-02 11:14:29', '2025-12-02 11:14:29'),
(33, 290574, 'kareem', 'abdul', 'kabdul', 'kareem@easyrent.africa', NULL, 2, 'Project Manager', '08052345333', '9 point road, Apapa', 'Lagos', 'Amuwo-Odofin', NULL, 0, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-02 15:52:49', '2025-12-01 10:17:59', '$2y$10$wmdE5qAswa6FDdT.TwGkDOjykhwkTY3vkVUicodqxhVsPdBObN7u2', NULL, '2025-12-02 12:52:49', '2025-12-02 12:58:02'),
(34, 503545, 'Kayode', 'Abiodun', 'abey', 'moshoodabdul@gmail.com', NULL, 2, 'Project Manager', '08052345345', '9 point road, Apapa', 'Lagos', 'Lagos Island', NULL, 0, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-04 11:58:17', NULL, '$2y$10$7WI4fVTvMmTlf1pOlkTdWOniG29zcJZaxS1w/681amayZOAW1iKMy', NULL, '2025-12-04 08:58:17', '2025-12-04 08:58:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users_flagged_for_review_index` (`flagged_for_review`),
  ADD KEY `users_fraud_risk_score_index` (`fraud_risk_score`),
  ADD KEY `users_user_id_index` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
