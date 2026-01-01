-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 24, 2025 at 07:23 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mrnathanenglish`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `role` enum('super_admin','admin') DEFAULT 'admin',
  `is_active` tinyint(1) DEFAULT '1',
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_admin_user` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `user_id`, `username`, `email`, `role`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 10, 'Marc', 'falouche456@gmail.com', 'admin', 1, '2025-12-20 10:01:10', '2025-12-21 10:01:50', '2025-12-21 10:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
CREATE TABLE IF NOT EXISTS `courses` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'L''identifiant du cours',
  `id_trainer` int NOT NULL COMMENT 'L''identifiant du formateur',
  `title_course` varchar(255) NOT NULL COMMENT 'Le titre du cours',
  `description_course` varchar(255) NOT NULL COMMENT 'La description du cours',
  `profile_picture` varchar(255) NOT NULL COMMENT 'La photo de profil du cours',
  `time_course` int NOT NULL COMMENT 'Le temps requis pour terminer le cours',
  `validation_period` int NOT NULL COMMENT 'Le temps de validation du cours',
  `price_course` int NOT NULL COMMENT 'Le prix du cours',
  `language_taught` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'La langue enseignée(anglais/espagnol)',
  `learner_level` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Le niveau du cours (débutant / intermédiarie)',
  `status_course` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `course_rate` float NOT NULL COMMENT 'Note d''un cours',
  `is_free` tinyint(1) NOT NULL,
  `course_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date de création du cours',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses_content`
--

DROP TABLE IF EXISTS `courses_content`;
CREATE TABLE IF NOT EXISTS `courses_content` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'L''identifiant du cours',
  `id_course` int NOT NULL COMMENT 'L''identifiant d''un cours',
  `title_lesson` varchar(255) NOT NULL COMMENT 'Le titre de la leçon',
  `content_lesson` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Le contenu de la leçon',
  `video_lesson` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'La vidéo de la leçon',
  `lesson_order` int NOT NULL COMMENT 'L''order de la leçon',
  `duration_lesson` varchar(255) NOT NULL COMMENT 'La durée de la leçon',
  `is_free` tinyint(1) NOT NULL COMMENT 'Disponibilité de la leçon',
  `status_lesson` varchar(255) NOT NULL COMMENT 'Visibilité de la leçon (publiée / archivée / brouillon)',
  `view_count` int NOT NULL COMMENT 'Le nombre de fois où le cours a été vu',
  `updated_date` date NOT NULL COMMENT 'Dernière mise à jour de la leçon',
  `created_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `draft`
--

DROP TABLE IF EXISTS `draft`;
CREATE TABLE IF NOT EXISTS `draft` (
  `id` int NOT NULL AUTO_INCREMENT,
  `trainer_id` int DEFAULT NULL COMMENT 'L''id du formateur',
  `draft_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Les données du cours enregistré au brouillon',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'La date de création du brouillon',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Dernière mise à jour du brouillon',
  PRIMARY KEY (`id`),
  KEY `idx_updated_at` (`updated_at`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `draft`
--

INSERT INTO `draft` (`id`, `trainer_id`, `draft_data`, `created_at`, `updated_at`) VALUES
(15, 10, '{\"course_infos\":{\"title_course\":\"Apprendre l\'anglais des affaires\",\"description_course\":\"L\'anglais des affaires est un domaine dans lequel excéllent plusieurs personnes qui parlent la langue\",\"language_taught\":\"anglais\",\"learner_level\":\"intermédiaire\",\"time_course\":980,\"validation_period\":200,\"price_course\":15000,\"is_free\":1,\"publish_now\":0,\"profile_picture\":\"/uploads/courses/course_draft_694b93e55acde.png\"},\"modules\":[]}', '2025-12-24 07:14:13', '2025-12-24 07:19:01'),
(16, 10, '{\"course_infos\":{\"title_course\":\"\",\"description_course\":\"\",\"language_taught\":\"\",\"learner_level\":\"\",\"time_course\":null,\"validation_period\":null,\"price_course\":0,\"is_free\":0,\"publish_now\":0,\"profile_picture\":null},\"modules\":[]}', '2025-12-24 07:14:33', '2025-12-24 07:14:33'),
(17, 10, '{\"course_infos\":{\"title_course\":\"\",\"description_course\":\"\",\"language_taught\":\"\",\"learner_level\":\"\",\"time_course\":null,\"validation_period\":null,\"price_course\":0,\"is_free\":0,\"publish_now\":0,\"profile_picture\":null},\"modules\":[]}', '2025-12-24 07:15:00', '2025-12-24 07:15:00'),
(18, 10, '{\"course_infos\":{\"title_course\":\"\",\"description_course\":\"\",\"language_taught\":\"\",\"learner_level\":\"\",\"time_course\":null,\"validation_period\":null,\"price_course\":0,\"is_free\":0,\"publish_now\":0,\"profile_picture\":null},\"modules\":[]}', '2025-12-24 07:16:30', '2025-12-24 07:16:30'),
(19, 10, '{\"course_infos\":{\"title_course\":\"\",\"description_course\":\"\",\"language_taught\":\"\",\"learner_level\":\"\",\"time_course\":null,\"validation_period\":null,\"price_course\":0,\"is_free\":0,\"publish_now\":0,\"profile_picture\":null},\"modules\":[]}', '2025-12-24 07:20:23', '2025-12-24 07:20:23');

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

DROP TABLE IF EXISTS `lessons`;
CREATE TABLE IF NOT EXISTS `lessons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `module_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `content` longtext,
  `video_url` varchar(500) DEFAULT NULL,
  `duration` int DEFAULT NULL,
  `is_free` tinyint(1) DEFAULT '0',
  `order_index` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_module_order` (`module_id`,`order_index`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
CREATE TABLE IF NOT EXISTS `modules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `course_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `order_index` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_course_order` (`course_id`,`order_index`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
CREATE TABLE IF NOT EXISTS `subscriptions` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `id_user` bigint NOT NULL,
  `type` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'XAF',
  `pawa_pay_deposit_id` varchar(128) DEFAULT NULL,
  `pawa_pay_status` varchar(50) DEFAULT NULL,
  `pawa_pay_correlation_id` varchar(128) DEFAULT NULL,
  `pawa_pay_mobile_number` varchar(20) DEFAULT NULL,
  `pawa_pay_country_code` char(2) DEFAULT NULL,
  `pawa_pay_operator` varchar(50) DEFAULT NULL,
  `pawa_pay_response_raw` json DEFAULT NULL,
  `billing_period` enum('month','year') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `next_billing_date` date DEFAULT NULL,
  `status` enum('pending','active','expired','canceled','failed') DEFAULT 'pending',
  `canceled_at` datetime DEFAULT NULL,
  `ended_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_status` (`id_user`,`status`),
  KEY `idx_pawapay_deposit` (`pawa_pay_deposit_id`),
  KEY `idx_end_date` (`end_date`),
  KEY `idx_next_billing` (`next_billing_date`),
  KEY `idx_status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `id_user`, `type`, `amount`, `currency`, `pawa_pay_deposit_id`, `pawa_pay_status`, `pawa_pay_correlation_id`, `pawa_pay_mobile_number`, `pawa_pay_country_code`, `pawa_pay_operator`, `pawa_pay_response_raw`, `billing_period`, `start_date`, `end_date`, `next_billing_date`, `status`, `canceled_at`, `ended_at`, `created_at`, `updated_at`) VALUES
(1, 10, 'premium', 5000.00, 'XAF', 'dep_ga1234567890abcdef', 'SUCCESSFUL', 'corr_sub_20251214_001', '+24177123456', 'GA', 'AirtelMoney', '{\"amount\": \"5000\", \"msisdn\": \"+24177123456\", \"status\": \"SUCCESSFUL\", \"country\": \"GA\", \"currency\": \"XAF\", \"operator\": \"AirtelMoney\", \"depositId\": \"dep_ga1234567890abcdef\", \"timestamp\": \"2025-12-14T14:32:10Z\"}', 'month', '2025-12-14', '2026-01-14', '2026-01-14', 'active', NULL, NULL, '2025-12-14 14:35:00', '2025-12-14 02:34:34');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fullname` varchar(150) NOT NULL,
  `email` varchar(191) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `confirmation_code` varchar(6) NOT NULL,
  `is_confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `reset_link` text,
  `reset_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `reset_expires_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `username`, `password`, `confirmation_code`, `is_confirmed`, `reset_link`, `reset_token`, `reset_expires_at`, `created_at`) VALUES
(8, 'MAKOSSO Camille Samirah', 'natflix75@gmail.com', 'Samirah', '$2y$10$v5aOuRwXcDyUOvrTIEDJcuJVg7.kK5dB2I.s7q7nJcCzRH768cy6a', '', 1, NULL, '4e8f8f7894dc366c5264baf8c1122c5c4e5e43dafa9af9b9c10ca051470aa5c1', '2025-10-19 13:33:29', '2025-09-26 10:42:51'),
(3, 'IBALA BISSELO Hulda', 'nathanfaitdesreves@gmail.com', 'Hulda Christ Girelle', '$2y$10$BDkxsKxVi9IgenkRAd7paetcVcoRE4y3/CdcCQ9QHoUFOHTBtrfEK', '', 1, NULL, '594b3b33dfd0638a0f052181ea15d15f6f1aebfdd7fb54bd3b7dfa21bd2b5c76', '2025-11-19 16:53:37', '2025-09-25 23:49:09'),
(14, 'MOUSSAVOU Roger', 'masterphpcode@gmail.com', 'Roger', '$2y$10$NfA52svmd7hkwo1mbUPgxeqx1JjkkToy94y4nBIav/XIHvle0lDyW', '', 1, NULL, NULL, NULL, '2025-11-19 16:43:31'),
(10, 'Marc', 'falouche456@gmail.com', 'Marc Falouche', '$2y$10$DsPVJYRT8C7SbthsF9APqew0vgbyxzKYogolS.wQpSOQoyMk.XITy', '', 1, NULL, NULL, NULL, '2025-11-05 07:37:01'),
(11, 'IBALA BISSELO Hulda Christ Girelle', 'nathanlartiste6@gmail.com', 'IBALA BISSELO Hulda', '$2y$10$amtek71r.URIGYOyo9eJ7.DlIPLNsIOFNj.2CNkNZ3vFeRs5872.u', '', 1, NULL, NULL, '2025-11-16 21:48:15', '2025-11-16 20:48:15');

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

DROP TABLE IF EXISTS `user_profiles`;
CREATE TABLE IF NOT EXISTS `user_profiles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `profile_picture` varchar(255) DEFAULT 'default.png',
  `birth_date` date DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `english_level` varchar(50) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `bio` text,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`id`, `user_id`, `profile_picture`, `birth_date`, `country`, `english_level`, `phone_number`, `bio`, `updated_at`) VALUES
(5, 6, 'default.png', '2015-12-16', 'GA', 'advanced', '074854565', '123', '2025-12-16 14:27:52'),
(4, 5, 'default.png', '2016-12-08', 'US', 'beginner', '074548575', 'qsqsqs', '2025-12-16 14:27:40'),
(2, 3, 'photo.jpeg', '1966-05-18', 'GA', 'advanced', '076524587', 'sdsdfds', '2025-09-26 02:11:32'),
(6, 7, 'default.png', '2017-12-27', 'GA', 'intermediate', '076857878', '065458545', '2025-12-16 14:28:11'),
(7, 8, 'profile_8_1758884747.jpg', '2004-02-06', 'GA', 'intermediate', '076854687', 'aaaaaaaaaa', '2025-09-26 11:06:03'),
(8, 9, 'profile_9_1760877607.jpeg', '2005-03-27', 'GA', 'beginner', '074825725', 'J\'aime la langue anglais et je suis déterminé à apprendre.', '2025-10-19 12:41:38'),
(9, 10, 'profile_10_1762328406.jpg', '1988-02-07', 'GA', 'intermediate', '074825725', 'J\'aime l\'anglais, c\'est tout ce que je peux dire.', '2025-11-05 07:43:13'),
(10, 11, 'profile_11_1763326677.jpg', '2005-03-27', 'GA', 'beginner', '076339688', 'Je ne sais pas parler anglais.\r\nJ\'aimerais apprendre et c\'est pourquoi j\'ai décidé de m\'inscrire.', '2025-11-16 20:58:44'),
(11, 13, 'profile_13_1763570258.jpg', '2005-02-05', 'GA', 'beginner', '076858787', 'I want to learn!', '2025-11-19 16:38:03'),
(12, 14, 'profile_14_1763570701.png', '1998-05-06', 'GA', 'beginner', '076854587', 'J\'aime la langue', '2025-11-19 16:45:22');

-- --------------------------------------------------------

--
-- Table structure for table `user_remember_tokens`
--

DROP TABLE IF EXISTS `user_remember_tokens`;
CREATE TABLE IF NOT EXISTS `user_remember_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) NOT NULL,
  `device` varchar(100) NOT NULL,
  `browser` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_token` (`token`(250))
) ENGINE=MyISAM AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_remember_tokens`
--

INSERT INTO `user_remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`, `ip_address`, `device`, `browser`) VALUES
(12, 4, 'b25c5a5825cc80fa124bae3fb701bb57409b72880a43db8e5c7246b84c5251af', '2025-10-26 10:12:59', '2025-09-26 12:12:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Sa', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
