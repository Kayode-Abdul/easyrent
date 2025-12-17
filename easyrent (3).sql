-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 16, 2025 at 05:59 PM
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
-- Stand-in structure for view `active_invitation_details`
-- (See below for the actual view)
--
CREATE TABLE `active_invitation_details` (
);

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `amenities`
--

CREATE TABLE `amenities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
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
  `apartment_type` varchar(255) DEFAULT NULL,
  `apartment_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `range_start` datetime DEFAULT NULL,
  `range_end` datetime DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `pricing_type` enum('total','monthly') NOT NULL DEFAULT 'total',
  `price_configuration` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`price_configuration`)),
  `occupied` tinyint(1) NOT NULL DEFAULT 0,
  `apartment_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `apartments`
--

INSERT INTO `apartments` (`id`, `property_id`, `apartment_type`, `apartment_type_id`, `tenant_id`, `user_id`, `range_start`, `range_end`, `amount`, `pricing_type`, `price_configuration`, `occupied`, `apartment_id`, `created_at`, `updated_at`) VALUES
(10, 2, 'Store Unit', NULL, NULL, 993033, NULL, NULL, 4000000.00, 'total', NULL, 0, 7589367, '2025-12-16 15:43:24', NULL),
(11, 2, '2-Bedroom', NULL, NULL, 993033, NULL, NULL, 1200000.00, 'total', NULL, 0, 4991441, '2025-12-16 15:49:27', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `apartment_invitations`
--

CREATE TABLE `apartment_invitations` (
  `id` bigint(20) UNSIGNED NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL
) ;

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
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `apartment_types`
--

INSERT INTO `apartment_types` (`id`, `name`, `category`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Studio', 'residential', 'Single room apartment', 1, '2025-12-16 13:14:05', '2025-12-16 13:14:05'),
(2, '1 Bedroom', 'residential', 'One bedroom apartment', 1, '2025-12-16 13:14:05', '2025-12-16 13:14:05'),
(3, '2 Bedroom', 'residential', 'Two bedroom apartment', 1, '2025-12-16 13:14:05', '2025-12-16 13:14:05'),
(4, '3 Bedroom', 'residential', 'Three bedroom apartment', 1, '2025-12-16 13:14:05', '2025-12-16 13:14:05'),
(5, '4 Bedroom', 'residential', 'Four bedroom apartment', 1, '2025-12-16 13:14:05', '2025-12-16 13:14:05'),
(6, 'Penthouse', 'residential', 'Luxury top-floor apartment', 1, '2025-12-16 13:14:05', '2025-12-16 13:14:05'),
(7, 'Duplex Unit', 'residential', 'Two-level apartment unit', 1, '2025-12-16 13:14:05', '2025-12-16 13:14:05'),
(8, 'Shop Unit', 'commercial', 'Small retail unit', 1, '2025-12-16 13:14:05', '2025-12-16 13:14:05'),
(9, 'Store Unit', 'commercial', 'Retail store unit', 1, '2025-12-16 13:14:05', '2025-12-16 13:14:05'),
(10, 'Office Unit', 'commercial', 'Office space unit', 1, '2025-12-16 13:14:05', '2025-12-16 13:14:05'),
(11, 'Restaurant Unit', 'commercial', 'Restaurant space unit', 1, '2025-12-16 13:14:05', '2025-12-16 13:14:05'),
(12, 'Warehouse Unit', 'commercial', 'Storage unit', 1, '2025-12-16 13:14:05', '2025-12-16 13:14:05'),
(13, 'Showroom', 'commercial', 'Display showroom unit', 1, '2025-12-16 13:14:05', '2025-12-16 13:14:05'),
(14, 'Storage Unit', 'other', 'Storage space', 1, '2025-12-16 13:14:05', '2025-12-16 13:14:05'),
(15, 'Parking Space', 'other', 'Vehicle parking space', 1, '2025-12-16 13:14:05', '2025-12-16 13:14:05'),
(16, 'Other', 'other', 'Other type of unit', 1, '2025-12-16 13:14:05', '2025-12-16 13:14:05');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `benefactors`
--

CREATE TABLE `benefactors` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `relationship_type` enum('employer','parent','guardian','sponsor','organization','other') NOT NULL DEFAULT 'other',
  `type` enum('registered','guest') NOT NULL DEFAULT 'guest',
  `is_registered` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog`
--

CREATE TABLE `blog` (
  `id` bigint(20) UNSIGNED NOT NULL,
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
  `processing_time_minutes` decimal(8,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commission_rates`
--

CREATE TABLE `commission_rates` (
  `id` bigint(20) UNSIGNED NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `database_maintenance_logs`
--

CREATE TABLE `database_maintenance_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `database_maintenance_logs`
--

INSERT INTO `database_maintenance_logs` (`id`, `operation_type`, `table_name`, `description`, `operation_details`, `records_affected`, `execution_time_seconds`, `status`, `error_message`, `started_at`, `completed_at`, `created_at`, `updated_at`) VALUES
(1, 'schema_optimization', 'apartment_invitations', 'Initial database schema optimization for EasyRent Link Authentication System', '{\"indexes_added\":7,\"constraints_added\":6,\"triggers_added\":3,\"procedures_added\":3,\"views_added\":2}', 0, NULL, 'completed', NULL, '2025-12-16 13:14:04', '2025-12-16 13:14:04', '2025-12-16 13:14:04', '2025-12-16 13:14:04'),
(2, 'verification', NULL, 'Database optimization verification completed', '{\"indexes_verified\":10,\"views_verified\":2,\"tables_verified\":3,\"missing_indexes\":[]}', 0, NULL, 'completed', NULL, '2025-12-16 13:14:04', '2025-12-16 13:14:04', '2025-12-16 13:14:04', '2025-12-16 13:14:04'),
(3, 'schema_finalization', 'apartment_invitations', 'Finalized EasyRent Link Authentication System database schema with all optimizations', '{\"additional_fields_added\":4,\"performance_indexes_added\":4,\"database_views_created\":2,\"stored_procedures_created\":0,\"data_constraints_added\":4,\"schema_version\":\"1.0.0\"}', 0, NULL, 'completed', NULL, '2025-12-16 13:14:05', '2025-12-16 13:14:05', '2025-12-16 13:14:05', '2025-12-16 13:14:05'),
(4, 'final_optimization', 'multiple_tables', 'Applied final performance optimizations for EasyRent Link Authentication System', '{\"performance_indexes_added\":12,\"database_views_created\":2,\"database_functions_created\":0,\"analytics_cache_table_created\":true,\"optimization_level\":\"production_ready\"}', 0, NULL, 'completed', NULL, '2025-12-16 13:14:05', '2025-12-16 13:14:05', '2025-12-16 13:14:05', '2025-12-16 13:14:05');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
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
  `id` bigint(20) UNSIGNED NOT NULL,
  `cache_date` date NOT NULL,
  `metric_type` varchar(50) NOT NULL,
  `analytics_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`analytics_data`)),
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
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
  `id` bigint(20) UNSIGNED NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `invitation_security_monitoring`
-- (See below for the actual view)
--
CREATE TABLE `invitation_security_monitoring` (
`id` bigint(20) unsigned
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
  `id` bigint(20) UNSIGNED NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sender_id` bigint(20) UNSIGNED NOT NULL,
  `receiver_id` bigint(20) UNSIGNED NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `body` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
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
(106, '2025_12_15_070000_migrate_existing_payment_calculation_data', 1);

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
  `id` bigint(20) UNSIGNED NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `landlord_id` bigint(20) UNSIGNED NOT NULL,
  `apartment_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `duration` int(11) NOT NULL COMMENT 'Duration in months',
  `status` enum('pending','completed','success','failed') NOT NULL DEFAULT 'pending',
  `payment_method` enum('card','bank_transfer','ussd') DEFAULT NULL,
  `payment_reference` varchar(255) DEFAULT NULL,
  `payment_meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_meta`)),
  `paid_at` timestamp NULL DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_invitations`
--

CREATE TABLE `payment_invitations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `benefactor_email` varchar(255) NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_tracking`
--

CREATE TABLE `payment_tracking` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `payment_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(50) NOT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `tracked_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` bigint(20) UNSIGNED NOT NULL,
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
  `reactivated_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `user_id`, `property_id`, `prop_type`, `address`, `state`, `lga`, `no_of_apartment`, `size_value`, `size_unit`, `agent_id`, `created_at`, `updated_at`, `status`, `approved_at`, `rejected_at`, `suspension_reason`, `suspended_at`, `suspended_by`, `reactivated_at`, `reactivated_by`) VALUES
(1, 993033, 9533782, 2, '9 point road apapa lagos', 'Lagos', 'Apapa', 3, NULL, 'sqm', NULL, '2025-12-16 13:16:30', '2025-12-16 13:16:30', 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 993033, 1416028, 1, '33 Adegoke Street', 'Abia', 'Arochukwu', 3, NULL, 'sqm', NULL, '2025-12-16 15:41:50', '2025-12-16 15:41:50', 'available', NULL, NULL, NULL, NULL, NULL, NULL, NULL);

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
  `attribute_key` varchar(100) NOT NULL,
  `attribute_value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property_types`
--

CREATE TABLE `property_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `property_types`
--

INSERT INTO `property_types` (`id`, `name`, `category`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Mansion', 'residential', 'Large residential property', 1, '2025-12-16 13:14:05', '2025-12-16 13:14:05'),
(2, 'Duplex', 'residential', 'Two-unit residential property', 1, '2025-12-16 13:14:05', '2025-12-16 13:14:05'),
(3, 'Flat', 'residential', 'Apartment building', 1, '2025-12-16 13:14:05', '2025-12-16 13:14:05'),
(4, 'Terrace', 'residential', 'Terraced house', 1, '2025-12-16 13:14:05', '2025-12-16 13:14:05'),
(5, 'Warehouse', 'commercial', 'Storage and distribution facility', 1, '2025-12-16 13:14:05', '2025-12-16 13:14:05'),
(6, 'Land', 'land', 'Undeveloped land', 1, '2025-12-16 13:14:05', '2025-12-16 13:14:05'),
(7, 'Farm', 'land', 'Agricultural land', 1, '2025-12-16 13:14:05', '2025-12-16 13:14:05'),
(8, 'Store', 'commercial', 'Retail store', 1, '2025-12-16 13:14:05', '2025-12-16 13:14:05'),
(9, 'Shop', 'commercial', 'Small retail shop', 1, '2025-12-16 13:14:05', '2025-12-16 13:14:05');

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` bigint(20) UNSIGNED NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referral_campaigns`
--

CREATE TABLE `referral_campaigns` (
  `id` bigint(20) UNSIGNED NOT NULL,
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
  `chain_hash` varchar(64) NOT NULL,
  `status` enum('active','completed','broken','suspended') NOT NULL DEFAULT 'active',
  `commission_breakdown` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`commission_breakdown`)),
  `total_commission_percentage` decimal(5,4) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `regional_scopes`
--

CREATE TABLE `regional_scopes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `scope_type` varchar(20) NOT NULL DEFAULT 'state',
  `scope_value` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `property_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`, `display_name`, `description`, `is_active`, `permissions`) VALUES
(1, 'tenant', '2025-08-29 14:26:53', '2025-08-29 14:26:53', 'Tenant', 'Rents properties', 1, NULL),
(2, 'landlord', '2025-08-29 14:26:53', '2025-08-29 14:26:53', 'Landlord', 'Property owner', 1, NULL),
(3, 'marketer', '2025-08-29 14:26:53', '2025-08-29 14:26:53', 'Marketer', 'Handles marketing tasks', 1, NULL),
(4, 'super_marketer', '2025-09-12 07:34:40', '2025-09-12 07:34:40', 'Super Marketer', 'Top-tier marketer who can refer other marketers', 1, '[\"refer_marketers\",\"view_referral_analytics\",\"manage_referral_campaigns\",\"view_commission_breakdown\"]'),
(5, 'Artisan', '2025-08-05 16:31:30', '2025-08-05 16:31:30', 'Artisan', NULL, 1, NULL),
(6, 'property_manager', '2025-08-29 14:26:53', '2025-08-29 14:26:53', 'Property Manager', 'Manages properties', 1, NULL),
(7, 'admin', '2025-08-29 14:26:53', '2025-08-29 14:26:53', 'Administrator', 'Full system access', 1, NULL),
(8, 'Verified_Property_Manager', '2025-08-13 11:21:42', '2025-08-13 11:21:42', 'Verified Property Manager', 'Recognised by the company', 1, NULL),
(9, 'regional_manager', '2025-08-29 14:26:53', '2025-08-29 14:26:53', 'Regional Manager', 'Manages region-specific operations', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `role_assignment_audits`
--

CREATE TABLE `role_assignment_audits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `actor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `legacy_role` varchar(255) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `reason` text DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_assignment_audits`
--

INSERT INTO `role_assignment_audits` (`id`, `actor_id`, `user_id`, `role_id`, `legacy_role`, `action`, `reason`, `meta`, `created_at`, `updated_at`) VALUES
(1, 993033, 993033, 8, NULL, 'assigned', 'Modern role assignment', NULL, '2025-12-16 15:40:39', '2025-12-16 15:40:39');

-- --------------------------------------------------------

--
-- Table structure for table `role_change_notifications`
--

CREATE TABLE `role_change_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED NOT NULL COMMENT 'The ID of the admin who made the change',
  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT 'The ID of the user whose role was changed',
  `old_role` int(11) NOT NULL COMMENT 'The previous role ID',
  `new_role` int(11) NOT NULL COMMENT 'The new role ID',
  `ip_address` varchar(255) DEFAULT NULL,
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
(1, 993033, 8, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `session_cleanup_history`
--

CREATE TABLE `session_cleanup_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
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
  `id` bigint(20) UNSIGNED NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_id`, `first_name`, `last_name`, `username`, `email`, `photo`, `registration_source`, `referred_by`, `role`, `occupation`, `phone`, `address`, `state`, `lga`, `region`, `admin`, `marketer_status`, `commission_rate`, `bank_account_name`, `bank_account_number`, `bank_name`, `bvn`, `referral_code`, `date_created`, `email_verified_at`, `flagged_for_review`, `flag_reasons`, `flagged_at`, `fraud_risk_score`, `last_fraud_check`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(8, 993033, 'kayode', 'abdul', 'kayoux', 'moshoodkayodeabdul@gmail.com', NULL, 'direct', NULL, 2, NULL, NULL, NULL, 'Lagos', 'Surulere', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-16 14:15:11', '2025-12-16 14:15:12', 0, NULL, NULL, 0, NULL, '$2y$10$/DcDU8KqHeqyl07dNkQdj.EeBt8ji2eb.JWultjuaS3pKJp46phTS', NULL, '2025-12-16 13:15:11', '2025-12-16 13:15:11');

-- --------------------------------------------------------

--
-- Structure for view `active_invitation_details`
--
DROP TABLE IF EXISTS `active_invitation_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `active_invitation_details`  AS SELECT `ai`.`id` AS `id`, `ai`.`invitation_token` AS `invitation_token`, `ai`.`apartment_id` AS `apartment_id`, `ai`.`landlord_id` AS `landlord_id`, `ai`.`status` AS `status`, `ai`.`expires_at` AS `expires_at`, `ai`.`access_count` AS `access_count`, `ai`.`last_accessed_at` AS `last_accessed_at`, `ai`.`session_expires_at` AS `session_expires_at`, `ai`.`created_at` AS `created_at`, `a`.`amount` AS `apartment_amount`, `a`.`occupied` AS `apartment_occupied`, `p`.`prop_type` AS `property_type`, `p`.`address` AS `property_address`, `p`.`state` AS `property_state`, `u`.`first_name` AS `landlord_first_name`, `u`.`last_name` AS `landlord_last_name`, `u`.`email` AS `landlord_email` FROM (((`apartment_invitations` `ai` join `apartments` `a` on(`ai`.`apartment_id` = `a`.`id`)) join `properties` `p` on(`a`.`property_id` = `p`.`prop_id`)) join `users` `u` on(`ai`.`landlord_id` = `u`.`user_id`)) WHERE `ai`.`status` = 'active' AND (`ai`.`expires_at` is null OR `ai`.`expires_at` > current_timestamp()) ;

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
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_action_ip_created` (`action`,`ip_address`,`created_at`),
  ADD KEY `idx_user_action_created` (`user_id`,`action`,`created_at`);

--
-- Indexes for table `agent_ratings`
--
ALTER TABLE `agent_ratings`
  ADD PRIMARY KEY (`id`),
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
ALTER TABLE `apartments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `apartments_apartment_id_unique` (`apartment_id`),
  ADD KEY `apartments_tenant_id_foreign` (`tenant_id`),
  ADD KEY `apartments_user_id_foreign` (`user_id`),
  ADD KEY `idx_property_occupied` (`property_id`,`occupied`),
  ADD KEY `idx_amount_occupied` (`amount`,`occupied`),
  ADD KEY `apartments_apartment_type_id_index` (`apartment_type_id`),
  ADD KEY `apartments_pricing_type_index` (`pricing_type`);

--
-- Indexes for table `apartment_invitations`
--
ALTER TABLE `apartment_invitations`
  ADD PRIMARY KEY (`id`),
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
-- Indexes for table `apartment_types`
--
ALTER TABLE `apartment_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_logs_user_id_performed_at_index` (`user_id`,`performed_at`),
  ADD KEY `audit_logs_model_type_model_id_index` (`model_type`,`model_id`),
  ADD KEY `audit_logs_performed_at_index` (`performed_at`),
  ADD KEY `audit_logs_audit_type_index` (`audit_type`),
  ADD KEY `audit_logs_reference_type_reference_id_index` (`reference_type`,`reference_id`);

--
-- Indexes for table `benefactors`
--
ALTER TABLE `benefactors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `benefactors_email_type_index` (`email`,`type`),
  ADD KEY `benefactors_email_index` (`email`),
  ADD KEY `benefactors_user_id_foreign` (`user_id`);

--
-- Indexes for table `benefactor_payments`
--
ALTER TABLE `benefactor_payments`
  ADD PRIMARY KEY (`id`),
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
ALTER TABLE `blog`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `blog_topic_url_unique` (`topic_url`),
  ADD KEY `blog_published_date_index` (`published`,`date`),
  ADD KEY `blog_topic_url_index` (`topic_url`);

--
-- Indexes for table `commission_payments`
--
ALTER TABLE `commission_payments`
  ADD PRIMARY KEY (`id`),
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
ALTER TABLE `commission_rates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_region_role` (`region`,`role_id`),
  ADD KEY `idx_effective_dates` (`effective_from`,`effective_until`),
  ADD KEY `idx_active_rates` (`is_active`,`effective_from`),
  ADD KEY `commission_rates_role_id_foreign` (`role_id`),
  ADD KEY `commission_rates_created_by_foreign` (`created_by`),
  ADD KEY `commission_rates_region_index` (`region`);

--
-- Indexes for table `database_maintenance_logs`
--
ALTER TABLE `database_maintenance_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `database_maintenance_logs_operation_type_started_at_index` (`operation_type`,`started_at`),
  ADD KEY `database_maintenance_logs_table_name_status_index` (`table_name`,`status`),
  ADD KEY `database_maintenance_logs_status_index` (`status`),
  ADD KEY `database_maintenance_logs_started_at_index` (`started_at`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `invitation_analytics_cache`
--
ALTER TABLE `invitation_analytics_cache`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invitation_analytics_cache_cache_date_metric_type_unique` (`cache_date`,`metric_type`),
  ADD KEY `invitation_analytics_cache_cache_date_index` (`cache_date`),
  ADD KEY `invitation_analytics_cache_metric_type_index` (`metric_type`),
  ADD KEY `invitation_analytics_cache_last_updated_index` (`last_updated`);

--
-- Indexes for table `invitation_performance_metrics`
--
ALTER TABLE `invitation_performance_metrics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invitation_performance_metrics_metric_date_unique` (`metric_date`),
  ADD KEY `invitation_performance_metrics_metric_date_index` (`metric_date`),
  ADD KEY `idx_conversion_rate` (`conversion_rate_view_to_payment`);

--
-- Indexes for table `marketer_profiles`
--
ALTER TABLE `marketer_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `marketer_profiles_user_id_foreign` (`user_id`),
  ADD KEY `marketer_profiles_kyc_status_index` (`kyc_status`),
  ADD KEY `marketer_profiles_verified_at_index` (`verified_at`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
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
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payments_transaction_id_unique` (`transaction_id`),
  ADD KEY `payments_landlord_id_foreign` (`landlord_id`),
  ADD KEY `idx_apartment_status` (`apartment_id`,`status`),
  ADD KEY `idx_paid_at_status` (`paid_at`,`status`),
  ADD KEY `payments_tenant_id_foreign` (`tenant_id`);

--
-- Indexes for table `payment_invitations`
--
ALTER TABLE `payment_invitations`
  ADD PRIMARY KEY (`id`),
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
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_tracking_payment_id_status_index` (`payment_id`,`status`),
  ADD KEY `payment_tracking_tracked_at_index` (`tracked_at`);

--
-- Indexes for table `performance_logs`
--
ALTER TABLE `performance_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `performance_logs_execution_time_index` (`execution_time`),
  ADD KEY `performance_logs_created_at_index` (`created_at`),
  ADD KEY `performance_logs_status_code_index` (`status_code`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `profoma_receipt`
--
ALTER TABLE `profoma_receipt`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `profoma_receipt_transaction_id_unique` (`transaction_id`),
  ADD KEY `profoma_receipt_user_id_foreign` (`user_id`),
  ADD KEY `profoma_receipt_tenant_id_foreign` (`tenant_id`),
  ADD KEY `profoma_receipt_apartment_id_foreign` (`apartment_id`),
  ADD KEY `profoma_receipt_calculation_method_index` (`calculation_method`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `properties_prop_id_unique` (`property_id`),
  ADD KEY `properties_user_id_foreign` (`user_id`),
  ADD KEY `properties_agent_id_foreign` (`agent_id`),
  ADD KEY `properties_suspended_by_foreign` (`suspended_by`),
  ADD KEY `properties_reactivated_by_foreign` (`reactivated_by`);

--
-- Indexes for table `property_amenity`
--
ALTER TABLE `property_amenity`
  ADD PRIMARY KEY (`property_id`,`amenity_id`),
  ADD KEY `property_amenity_amenity_id_foreign` (`amenity_id`);

--
-- Indexes for table `property_attributes`
--
ALTER TABLE `property_attributes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_attributes_property_id_attribute_key_index` (`property_id`,`attribute_key`);

--
-- Indexes for table `property_types`
--
ALTER TABLE `property_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `referrals_commission_status_index` (`commission_status`),
  ADD KEY `referrals_campaign_id_index` (`campaign_id`),
  ADD KEY `referrals_referral_source_index` (`referral_source`),
  ADD KEY `referrals_conversion_date_index` (`conversion_date`),
  ADD KEY `referrals_property_id_foreign` (`property_id`),
  ADD KEY `referrals_is_flagged_index` (`is_flagged`),
  ADD KEY `referrals_flagged_at_index` (`flagged_at`),
  ADD KEY `idx_referral_level` (`referral_level`),
  ADD KEY `idx_parent_referral` (`parent_referral_id`),
  ADD KEY `idx_commission_tier` (`commission_tier`),
  ADD KEY `idx_referral_status` (`referral_status`),
  ADD KEY `idx_referral_code` (`referral_code`),
  ADD KEY `idx_referrer_referral_status` (`referrer_id`,`referral_status`),
  ADD KEY `idx_referred_created` (`referred_id`,`created_at`);

--
-- Indexes for table `referral_campaigns`
--
ALTER TABLE `referral_campaigns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `referral_campaigns_campaign_code_unique` (`campaign_code`),
  ADD KEY `referral_campaigns_campaign_code_index` (`campaign_code`),
  ADD KEY `referral_campaigns_status_index` (`status`),
  ADD KEY `referral_campaigns_marketer_id_status_index` (`marketer_id`,`status`),
  ADD KEY `referral_campaigns_start_date_end_date_index` (`start_date`,`end_date`);

--
-- Indexes for table `referral_chains`
--
ALTER TABLE `referral_chains`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `referral_chains_chain_hash_unique` (`chain_hash`),
  ADD UNIQUE KEY `unique_referral_chain` (`super_marketer_id`,`marketer_id`,`landlord_id`),
  ADD KEY `idx_super_marketer` (`super_marketer_id`),
  ADD KEY `idx_marketer` (`marketer_id`),
  ADD KEY `idx_landlord` (`landlord_id`),
  ADD KEY `idx_chain_status` (`status`),
  ADD KEY `idx_chain_region` (`region`),
  ADD KEY `idx_active_chains` (`status`,`activated_at`);

--
-- Indexes for table `referral_rewards`
--
ALTER TABLE `referral_rewards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `referral_rewards_referral_id_foreign` (`referral_id`),
  ADD KEY `referral_rewards_processed_by_foreign` (`processed_by`),
  ADD KEY `referral_rewards_status_index` (`status`),
  ADD KEY `referral_rewards_reward_type_index` (`reward_type`),
  ADD KEY `referral_rewards_marketer_id_status_index` (`marketer_id`,`status`),
  ADD KEY `referral_rewards_processed_at_index` (`processed_at`);

--
-- Indexes for table `regional_scopes`
--
ALTER TABLE `regional_scopes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `regional_scope_unique` (`user_id`,`scope_type`,`scope_value`),
  ADD KEY `regional_scopes_user_id_scope_type_index` (`user_id`,`scope_type`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reviews_property_id_foreign` (`property_id`),
  ADD KEY `reviews_user_id_foreign` (`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`);

--
-- Indexes for table `role_assignment_audits`
--
ALTER TABLE `role_assignment_audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_assignment_audits_user_id_index` (`user_id`),
  ADD KEY `role_assignment_audits_role_id_index` (`role_id`);

--
-- Indexes for table `role_change_notifications`
--
ALTER TABLE `role_change_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_change_notifications_admin_id_foreign` (`admin_id`),
  ADD KEY `role_change_notifications_user_id_foreign` (`user_id`);

--
-- Indexes for table `role_user`
--
ALTER TABLE `role_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_user_user_id_role_id_unique` (`user_id`,`role_id`),
  ADD KEY `role_user_role_id_foreign` (`role_id`),
  ADD KEY `role_user_user_id_index` (`user_id`);

--
-- Indexes for table `session_cleanup_history`
--
ALTER TABLE `session_cleanup_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `session_cleanup_history_cleanup_date_index` (`cleanup_date`),
  ADD KEY `session_cleanup_history_cleanup_type_index` (`cleanup_type`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_user_id_unique` (`user_id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_referral_code_unique` (`referral_code`),
  ADD KEY `users_role_marketer_status_index` (`role`,`marketer_status`),
  ADD KEY `users_referral_code_index` (`referral_code`),
  ADD KEY `users_flagged_for_review_index` (`flagged_for_review`),
  ADD KEY `users_flagged_at_index` (`flagged_at`),
  ADD KEY `users_registration_source_index` (`registration_source`),
  ADD KEY `users_referred_by_index` (`referred_by`),
  ADD KEY `idx_registration_source` (`registration_source`),
  ADD KEY `idx_referred_by` (`referred_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `apartment_invitations`
--
ALTER TABLE `apartment_invitations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `apartment_types`
--
ALTER TABLE `apartment_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `database_maintenance_logs`
--
ALTER TABLE `database_maintenance_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_invitations`
--
ALTER TABLE `payment_invitations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `profoma_receipt`
--
ALTER TABLE `profoma_receipt`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `property_attributes`
--
ALTER TABLE `property_attributes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `property_types`
--
ALTER TABLE `property_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `role_change_notifications`
--
ALTER TABLE `role_change_notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role_user`
--
ALTER TABLE `role_user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `session_cleanup_history`
--
ALTER TABLE `session_cleanup_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `agent_ratings`
--
ALTER TABLE `agent_ratings`
  ADD CONSTRAINT `agent_ratings_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `agent_ratings_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`property_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `agent_ratings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `apartments`
--
ALTER TABLE `apartments`
  ADD CONSTRAINT `apartments_apartment_type_id_foreign` FOREIGN KEY (`apartment_type_id`) REFERENCES `apartment_types` (`id`),
  ADD CONSTRAINT `apartments_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `apartments_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `apartments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `apartment_invitations`
--
ALTER TABLE `apartment_invitations`
  ADD CONSTRAINT `apartment_invitations_apartment_id_foreign` FOREIGN KEY (`apartment_id`) REFERENCES `apartments` (`apartment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `apartment_invitations_landlord_id_foreign` FOREIGN KEY (`landlord_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `apartment_invitations_tenant_user_id_foreign` FOREIGN KEY (`tenant_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `benefactors`
--
ALTER TABLE `benefactors`
  ADD CONSTRAINT `benefactors_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `benefactor_payments`
--
ALTER TABLE `benefactor_payments`
  ADD CONSTRAINT `benefactor_payments_apartment_id_foreign` FOREIGN KEY (`apartment_id`) REFERENCES `apartments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `benefactor_payments_benefactor_id_foreign` FOREIGN KEY (`benefactor_id`) REFERENCES `benefactors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `benefactor_payments_proforma_id_foreign` FOREIGN KEY (`proforma_id`) REFERENCES `profoma_receipt` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `benefactor_payments_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `commission_payments`
--
ALTER TABLE `commission_payments`
  ADD CONSTRAINT `commission_payments_marketer_id_foreign` FOREIGN KEY (`marketer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `commission_payments_parent_payment_id_foreign` FOREIGN KEY (`parent_payment_id`) REFERENCES `commission_payments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `commission_payments_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `commission_payments_referral_chain_id_foreign` FOREIGN KEY (`referral_chain_id`) REFERENCES `referral_chains` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `commission_rates`
--
ALTER TABLE `commission_rates`
  ADD CONSTRAINT `commission_rates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `commission_rates_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `marketer_profiles`
--
ALTER TABLE `marketer_profiles`
  ADD CONSTRAINT `marketer_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_receiver_id_foreign` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_apartment_id_foreign` FOREIGN KEY (`apartment_id`) REFERENCES `apartments` (`apartment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_landlord_id_foreign` FOREIGN KEY (`landlord_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_invitations`
--
ALTER TABLE `payment_invitations`
  ADD CONSTRAINT `payment_invitations_benefactor_id_foreign` FOREIGN KEY (`benefactor_id`) REFERENCES `benefactors` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payment_invitations_proforma_id_foreign` FOREIGN KEY (`proforma_id`) REFERENCES `profoma_receipt` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_tracking`
--
ALTER TABLE `payment_tracking`
  ADD CONSTRAINT `payment_tracking_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `commission_payments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `profoma_receipt`
--
ALTER TABLE `profoma_receipt`
  ADD CONSTRAINT `profoma_receipt_apartment_id_foreign` FOREIGN KEY (`apartment_id`) REFERENCES `apartments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `profoma_receipt_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `profoma_receipt_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `properties`
--
ALTER TABLE `properties`
  ADD CONSTRAINT `properties_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `properties_reactivated_by_foreign` FOREIGN KEY (`reactivated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `properties_suspended_by_foreign` FOREIGN KEY (`suspended_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `properties_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `property_amenity`
--
ALTER TABLE `property_amenity`
  ADD CONSTRAINT `property_amenity_amenity_id_foreign` FOREIGN KEY (`amenity_id`) REFERENCES `amenities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `property_amenity_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_attributes`
--
ALTER TABLE `property_attributes`
  ADD CONSTRAINT `property_attributes_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `referrals`
--
ALTER TABLE `referrals`
  ADD CONSTRAINT `referrals_parent_referral_id_foreign` FOREIGN KEY (`parent_referral_id`) REFERENCES `referrals` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `referrals_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`property_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `referrals_referred_id_foreign` FOREIGN KEY (`referred_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `referrals_referrer_id_foreign` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `referral_campaigns`
--
ALTER TABLE `referral_campaigns`
  ADD CONSTRAINT `referral_campaigns_marketer_id_foreign` FOREIGN KEY (`marketer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `referral_chains`
--
ALTER TABLE `referral_chains`
  ADD CONSTRAINT `referral_chains_landlord_id_foreign` FOREIGN KEY (`landlord_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `referral_chains_marketer_id_foreign` FOREIGN KEY (`marketer_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `referral_chains_super_marketer_id_foreign` FOREIGN KEY (`super_marketer_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `referral_rewards`
--
ALTER TABLE `referral_rewards`
  ADD CONSTRAINT `referral_rewards_marketer_id_foreign` FOREIGN KEY (`marketer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `referral_rewards_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `referral_rewards_referral_id_foreign` FOREIGN KEY (`referral_id`) REFERENCES `referrals` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `regional_scopes`
--
ALTER TABLE `regional_scopes`
  ADD CONSTRAINT `regional_scopes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_change_notifications`
--
ALTER TABLE `role_change_notifications`
  ADD CONSTRAINT `role_change_notifications_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_change_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `role_user`
--
ALTER TABLE `role_user`
  ADD CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
