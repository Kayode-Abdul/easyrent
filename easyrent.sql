-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 04, 2025 at 08:14 PM
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
  `apartment_id` bigint(20) UNSIGNED NOT NULL,
  `property_id` bigint(20) UNSIGNED NOT NULL,
  `apartment_type` varchar(255) DEFAULT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `range_start` datetime DEFAULT NULL,
  `range_end` datetime DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `occupied` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `apartments`
--

INSERT INTO `apartments` (`id`, `apartment_id`, `property_id`, `apartment_type`, `tenant_id`, `user_id`, `range_start`, `range_end`, `amount`, `occupied`, `created_at`, `updated_at`) VALUES
(19, 569087650, 4949905, '3-Bedroom', 569877, 748908, '2025-06-03 17:43:05', '2025-12-03 17:43:05', 34000000.00, 1, '2025-06-03 16:43:05', NULL),
(20, 840422638, 7196049, '2-Bedroom', 569877, 748908, '2025-06-04 08:42:09', '2025-12-04 08:42:09', 1200000.00, 1, '2025-06-04 07:42:09', NULL),
(21, 1414683, 4949905, '2-Bedroom', 104610, 748908, '2025-06-04 09:12:03', '2025-09-04 09:12:03', 1200000.00, 1, '2025-06-04 08:12:03', NULL),
(22, 8489487, 4949905, '2-Bedroom', 569877, 748908, '2025-06-04 09:15:43', '2026-06-04 09:15:43', 1200000.00, 1, '2025-06-04 08:15:43', NULL),
(23, 8423201, 4111160, '1-Bedroom', 569877, 748908, '2025-06-04 09:24:25', '2025-09-04 09:24:25', 34000000.00, 1, '2025-06-04 08:24:25', NULL),
(24, 1233376, 1385466, '2-Bedroom', 956963, 748908, '2025-06-04 12:38:09', '2026-06-04 12:38:09', 3400000.00, 1, '2025-06-04 11:38:09', NULL);

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
(5, '2024_01_09_000000_create_properties_table', 1),
(6, '2024_01_09_000001_create_amenities_table', 1),
(7, '2024_01_09_000003_create_reviews_table', 1),
(8, '2024_01_09_000004_create_apartments_table', 1),
(9, '2024_01_09_000005_create_property_amenity_table', 1),
(10, '2025_06_02_000000_create_profoma_receipts_table', 1),
(11, '2025_06_03_000001_add_apartment_id_to_apartments_table', 2);

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
  `apartment_id` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profoma_receipt`
--

INSERT INTO `profoma_receipt` (`id`, `user_id`, `tenant_id`, `status`, `transaction_id`, `apartment_id`, `created_at`, `updated_at`) VALUES
(6, 748908, 569877, 3, '498180123', '498180123', '2025-06-03 14:02:08', '2025-06-03 14:02:08'),
(7, 748908, 569877, 3, '455498149', '455498149', '2025-06-03 14:02:44', '2025-06-03 14:02:44'),
(8, 748908, 569877, 3, '312702488', '312702488', '2025-06-03 14:10:35', '2025-06-03 14:10:35'),
(9, 748908, 569877, 3, '633087204', '633087204', '2025-06-03 14:13:18', '2025-06-03 14:13:18'),
(10, 748908, 569877, 3, '207768290', '207768290', '2025-06-03 14:15:03', '2025-06-03 14:15:03'),
(11, 748908, 569877, 3, '430456288', '430456288', '2025-06-03 16:27:40', '2025-06-03 16:27:40'),
(12, 748908, 569877, 3, '360016668', '360016668', '2025-06-03 16:36:50', '2025-06-03 16:36:50'),
(13, 748908, 569877, 3, '543248387', '543248387', '2025-06-03 16:39:50', '2025-06-03 16:39:50'),
(14, 748908, 569877, 3, '670304876', '670304876', '2025-06-03 16:43:05', '2025-06-03 16:43:05'),
(15, 748908, 569877, 3, '840422638', '840422638', '2025-06-04 07:42:09', '2025-06-04 07:42:09'),
(16, 748908, 104610, 3, '1414683', '1414683', '2025-06-04 08:12:03', '2025-06-04 08:12:03'),
(17, 748908, 569877, 3, '8489487', '8489487', '2025-06-04 08:15:43', '2025-06-04 08:15:43'),
(18, 748908, 569877, 3, '8423201', '8423201', '2025-06-04 08:24:25', '2025-06-04 08:24:25'),
(19, 748908, 956963, 2, '1233376', '1233376', '2025-06-04 11:38:09', '2025-06-04 16:27:35');

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `prop_id` bigint(20) UNSIGNED NOT NULL,
  `prop_type` tinyint(3) UNSIGNED NOT NULL,
  `address` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `lga` varchar(255) NOT NULL,
  `date_created` timestamp NULL DEFAULT NULL,
  `no_of_apartment` int(10) UNSIGNED DEFAULT NULL,
  `agent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `user_id`, `prop_id`, `prop_type`, `address`, `state`, `lga`, `date_created`, `no_of_apartment`, `agent_id`, `created_at`, `updated_at`) VALUES
(2, 748908, 4111160, 4, '33 Kayode kagoose estate Street', 'Lagos', 'Ibeju-Lekki', '2025-06-03 04:39:27', 6, 956963, NULL, NULL),
(3, 748908, 6969118, 1, 'malondo house, beside uba barracks', 'Adamawa', 'Madagali', '2025-06-03 08:41:10', 2, NULL, NULL, NULL),
(7, 748908, 6388345, 2, '45 Admiralty Street Lagos', 'Akwa Ibom', 'Eket', NULL, 7, NULL, NULL, NULL),
(9, 748908, 4949905, 2, '33 Adegoke Street', 'Akwa Ibom', 'Essien Udim', NULL, 3, NULL, NULL, NULL),
(10, 748908, 7196049, 4, '9 point road, Apapa', 'Akwa Ibom', 'Eket', NULL, 2, NULL, '2025-06-03 16:36:11', NULL),
(11, 748908, 1385466, 2, '33 Adegoke Street', 'Anambra', 'Awka South', NULL, 3, NULL, '2025-06-04 10:00:50', NULL);

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
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` int(11) NOT NULL,
  `occupation` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `lga` varchar(255) DEFAULT NULL,
  `admin` tinyint(1) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_id`, `first_name`, `last_name`, `username`, `email`, `role`, `occupation`, `phone`, `address`, `state`, `lga`, `admin`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 748908, 'Kayode', 'Abdullahi', 'kagoose', 'moshoodkayodeabdul@gmail.com', 1, 'Project Manager', '08052345312', '9 point road, Apapa', 'Lagos', 'Apapa', 1, NULL, '$2y$10$73IRYy4/DyniwfxSWGFAv.KRblqDPdRL0SUu92EUcsMAQjUdeDaSq', NULL, '2025-06-02 16:05:23', '2025-06-02 16:05:23'),
(2, 104610, 'esien', 'malware', 'esmal', 'esien@malware.net', 2, 'Electrician', '08034099844', '33 Adegoke Street', 'Lagos', 'Alimosho', 0, NULL, '$2y$10$qiXv0tchaGrt94Yq3sHAq.dobkZHIBoW9CmBaee1NcekIkBkFxhi6', NULL, '2025-06-02 16:28:35', '2025-06-02 16:28:35'),
(3, 956963, 'asfgg', 'gegeg', 'Mma', 'gygg@rr.ffr', 4, 'Project Manager', '08052345313', 'rgtgtg', 'Adamawa', 'Ganye', 0, NULL, '$2y$10$Km0V5/E04osj0oJU7VzpKOPV83nFYPLBpJwBXXuN8.BanmOh8a7rS', NULL, '2025-06-02 16:31:52', '2025-06-02 16:31:52'),
(4, 569877, 'micheal', 'george', 'migeo', 'george@geg.box', 1, 'Plumber', '08052345312', '9 side walk road, Ipaja', 'Kwara', 'Edu', 0, NULL, '$2y$10$dw6OQGXIROcI6t6EvjpFVOZMbK6IjFMaLixwulESQHp3kNYSiPiYa', NULL, '2025-06-02 20:28:31', '2025-06-02 20:28:31'),
(5, 488810, 'musa', 'saheed', 'musha', 'musah@yahoo.com', 2, 'Electrician', '08034099844', '8 Abebi Street', 'Lagos', 'Shomolu', 0, NULL, '$2y$10$xUALrwBAZEOOm.OnqvdKY./muj4vekPR1vpK1DW0gDmgW0OVd4ofm', NULL, '2025-06-03 08:34:59', '2025-06-03 08:34:59');

--
-- Indexes for dumped tables
--

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
  ADD KEY `apartments_tenant_id_foreign` (`tenant_id`),
  ADD KEY `apartments_user_id_foreign` (`user_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

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
  ADD KEY `profoma_receipt_apartment_id_foreign` (`apartment_id`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `properties_prop_id_unique` (`prop_id`),
  ADD KEY `properties_user_id_foreign` (`user_id`),
  ADD KEY `properties_agent_id_foreign` (`agent_id`);

--
-- Indexes for table `property_amenity`
--
ALTER TABLE `property_amenity`
  ADD PRIMARY KEY (`property_id`,`amenity_id`),
  ADD KEY `property_amenity_amenity_id_foreign` (`amenity_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reviews_property_id_foreign` (`property_id`),
  ADD KEY `reviews_user_id_foreign` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_user_id_unique` (`user_id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `amenities`
--
ALTER TABLE `amenities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `apartments`
--
ALTER TABLE `apartments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `profoma_receipt`
--
ALTER TABLE `profoma_receipt`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `apartments`
--
ALTER TABLE `apartments`
  ADD CONSTRAINT `apartments_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `apartments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `profoma_receipt`
--
ALTER TABLE `profoma_receipt`
  ADD CONSTRAINT `profoma_receipt_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `profoma_receipt_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `properties`
--
ALTER TABLE `properties`
  ADD CONSTRAINT `properties_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `properties_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `property_amenity`
--
ALTER TABLE `property_amenity`
  ADD CONSTRAINT `property_amenity_amenity_id_foreign` FOREIGN KEY (`amenity_id`) REFERENCES `amenities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `property_amenity_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
