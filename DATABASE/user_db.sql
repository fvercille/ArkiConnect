-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 27, 2025 at 04:37 AM
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
-- Database: `user_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `log_login_attempt` (IN `p_user_id` INT, IN `p_email` VARCHAR(255), IN `p_method` VARCHAR(20), IN `p_ip` VARCHAR(45), IN `p_user_agent` TEXT, IN `p_status` VARCHAR(10), IN `p_failure_reason` VARCHAR(255))   BEGIN
    INSERT INTO login_history 
    (user_id, email, login_method, ip_address, user_agent, status, failure_reason)
    VALUES 
    (p_user_id, p_email, p_method, p_ip, p_user_agent, p_status, p_failure_reason);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `update_last_login` (IN `p_user_id` INT)   BEGIN
    UPDATE users 
    SET last_login = NOW() 
    WHERE id = p_user_id;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `active_users`
-- (See below for the actual view)
--
CREATE TABLE `active_users` (
`id` int(11)
,`fullname` varchar(255)
,`studentnum` varchar(50)
,`email` varchar(255)
,`login_method` varchar(8)
,`created_at` timestamp
,`last_login` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` enum('general','academic','event','urgent') DEFAULT 'general',
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `is_published` tinyint(1) DEFAULT 1,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `category`, `priority`, `is_published`, `published_at`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Welcome to Arki Connect', 'Welcome to the new Arki Connect platform! Stay connected with your architecture community.', 'general', 'high', 1, '2025-09-29 09:16:37', 1, '2025-09-29 09:16:37', '2025-09-29 09:16:37'),
(2, 'Upcoming Events', 'Check out our events calendar for exciting activities this semester!', 'event', 'medium', 1, '2025-09-29 09:16:37', 1, '2025-09-29 09:16:37', '2025-09-29 09:16:37');

--
-- Triggers `announcements`
--
DELIMITER $$
CREATE TRIGGER `set_announcement_published_date` BEFORE UPDATE ON `announcements` FOR EACH ROW BEGIN
    IF NEW.is_published = 1 AND OLD.is_published = 0 AND NEW.published_at IS NULL THEN
        SET NEW.published_at = NOW();
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `email_verifications`
--

CREATE TABLE `email_verifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_verifications`
--

INSERT INTO `email_verifications` (`id`, `user_id`, `token`, `expires_at`, `used`, `created_at`) VALUES
(2, 18, 'ed53b2d4387f27afa1e986d1b2e220a098c8006170e57d27f0f8fbddda24a223', '2025-10-12 13:09:00', 1, '2025-10-12 12:58:31');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `event_time` time DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `registrants` int(11) DEFAULT 0,
  `organizer` varchar(255) DEFAULT NULL,
  `status` enum('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `event_time`, `start_time`, `end_time`, `location`, `image_path`, `pdf_path`, `registrants`, `organizer`, `status`, `created_by`, `created_at`, `updated_at`, `approved_by`, `approved_at`, `rejection_reason`) VALUES
(1, 'Architecture Workshop', 'Learn about modern architecture principles', '2025-10-15', '14:00:00', '14:00:00', NULL, 'Room 301', NULL, NULL, 0, 'Architecture Department', 'upcoming', 1, '2025-09-29 09:16:37', '2025-10-24 05:22:36', NULL, NULL, NULL),
(2, 'Design Competition', 'Annual design competition for students', '2025-11-20', '09:00:00', '09:00:00', NULL, 'Main Hall', NULL, NULL, 0, 'Student Council', 'upcoming', 1, '2025-09-29 09:16:37', '2025-10-24 05:22:36', NULL, NULL, NULL),
(9, 'Try', 'Try', '2025-12-12', '12:00:00', NULL, NULL, 'TBD', '', '', 0, 'Admin', 'upcoming', 11, '2025-10-26 20:12:46', '2025-10-26 20:12:46', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `event_registrations`
--

CREATE TABLE `event_registrations` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('confirmed','cancelled','attended') DEFAULT 'confirmed',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_registrations`
--

INSERT INTO `event_registrations` (`id`, `event_id`, `user_id`, `registration_date`, `status`, `notes`) VALUES
(1, 2, 18, '2025-10-24 05:33:42', 'confirmed', NULL),
(5, 2, 15, '2025-10-26 19:34:59', 'confirmed', NULL),
(9, 2, 11, '2025-10-26 20:08:43', 'confirmed', NULL),
(10, 9, 11, '2025-10-27 03:04:34', 'confirmed', NULL),
(11, 9, 15, '2025-10-27 03:30:12', 'confirmed', NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `event_registrations_view`
-- (See below for the actual view)
--
CREATE TABLE `event_registrations_view` (
`registration_id` int(11)
,`event_id` int(11)
,`event_title` varchar(255)
,`event_date` date
,`start_time` time
,`user_id` int(11)
,`fullname` varchar(255)
,`email` varchar(255)
,`studentnum` varchar(50)
,`registration_date` timestamp
,`status` enum('confirmed','cancelled','attended')
);

-- --------------------------------------------------------

--
-- Table structure for table `login_history`
--

CREATE TABLE `login_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `login_method` enum('standard','google') NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `status` enum('success','failed') NOT NULL,
  `failure_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `recipient_id` int(11) DEFAULT NULL,
  `notification_type` varchar(50) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `event_id`, `user_id`, `recipient_id`, `notification_type`, `message`, `created_at`, `is_read`) VALUES
(3, 2, 11, 1, 'registration', 'Admin User registered for Design Competition', '2025-10-26 20:08:45', 0),
(4, 9, 11, 11, 'registration', 'Admin User registered for Try', '2025-10-27 03:04:36', 0),
(5, 9, 15, 11, 'registration', 'ASAPHIL Representative registered for Try', '2025-10-27 03:30:14', 0);

-- --------------------------------------------------------

--
-- Table structure for table `org_reports`
--

CREATE TABLE `org_reports` (
  `id` int(11) NOT NULL,
  `org_name` varchar(255) NOT NULL,
  `report_title` varchar(255) NOT NULL,
  `date_submitted` date NOT NULL,
  `status` enum('Pending Review','Approved','Finalized') DEFAULT 'Pending Review',
  `attendees` int(11) DEFAULT NULL,
  `budget` decimal(10,2) DEFAULT NULL,
  `summary` text DEFAULT NULL,
  `org_rep_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `used`, `created_at`) VALUES
(1, 'kayenixol@gmail.com', 'e222d68554039dfb2528d01c4cc909ef5a97753bd0fff65a1ce2ca72b5aa787c', '2025-10-12 11:06:16', 0, '2025-10-12 10:06:16'),
(2, 'mkanguinto@tip.edu.ph', '67eff792d7efdad16879bedd44dd2112bc2c9feae29feb15e1c0f7817e4a8946', '2025-10-12 13:09:54', 1, '2025-10-12 13:09:31');

-- --------------------------------------------------------

--
-- Stand-in structure for view `recent_announcements`
-- (See below for the actual view)
--
CREATE TABLE `recent_announcements` (
`id` int(11)
,`title` varchar(255)
,`content` text
,`category` enum('general','academic','event','urgent')
,`priority` enum('low','medium','high')
,`is_published` tinyint(1)
,`published_at` timestamp
,`created_by` int(11)
,`created_at` timestamp
,`updated_at` timestamp
,`creator_name` varchar(255)
);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` text NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `upcoming_events`
-- (See below for the actual view)
--
CREATE TABLE `upcoming_events` (
`id` int(11)
,`title` varchar(255)
,`description` text
,`event_date` date
,`event_time` time
,`location` varchar(255)
,`organizer` varchar(255)
,`status` enum('upcoming','ongoing','completed','cancelled')
,`created_by` int(11)
,`created_at` timestamp
,`updated_at` timestamp
,`creator_name` varchar(255)
);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `studentnum` varchar(50) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL COMMENT 'NULL for Google OAuth users',
  `google_id` varchar(255) DEFAULT NULL COMMENT 'Google OAuth ID',
  `profile_picture` varchar(500) DEFAULT NULL COMMENT 'URL to profile picture',
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `email_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `role` enum('admin','org_rep','student') NOT NULL DEFAULT 'student'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `studentnum`, `email`, `password`, `google_id`, `profile_picture`, `status`, `email_verified`, `created_at`, `updated_at`, `last_login`, `role`) VALUES
(1, 'Admin User', '2024000001', 'admin@arkiconnect.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'active', 1, '2025-09-29 09:16:37', '2025-10-12 05:40:00', '2025-10-12 05:40:00', 'student'),
(3, 'Fraise Abe', '2024000003', 'mfabe@tip.edu.ph', NULL, '123456789012345678901', NULL, 'active', 1, '2025-09-29 09:16:37', '2025-09-29 09:18:25', NULL, 'student'),
(5, 'Fraise Abe', '2410415', 'mfvabe@tip.edu.ph', '$2y$10$68WTCiuC9yPTZ7d6gTk5o./LTzbhdWkDomQ4z/ii7S78/n0OSIqU2', NULL, NULL, 'active', 0, '2025-10-11 06:50:07', '2025-10-11 06:52:36', '2025-10-11 06:52:36', 'student'),
(7, 'Fraise Vercille', '2410516', 'fraisevercille@gmail.com', '$2y$10$FCcxbHfOm2TRuD7hpisxKOejgwSvEdwWZUCw8SL/a2.kMh5Uuyaz.', NULL, NULL, 'active', 0, '2025-10-11 10:21:20', '2025-10-11 10:21:20', NULL, 'student'),
(11, 'Admin User', NULL, 'admin@example.com', '$2y$10$E.i9A6nhpzAJsv3OG02qouNp7Q5CMGjWA/FOmUziiBZV74Hw8ZdKu', NULL, NULL, 'active', 1, '2025-10-11 15:19:32', '2025-10-11 15:19:32', NULL, 'admin'),
(15, 'ASAPHIL Representative', NULL, 'asaphil@tip.edu.ph', '$2y$10$6R5SivVnjj0ybjlTAnJlmeGwsPJOWAw1dEJtvPlBMOJvyEntoCVJC', NULL, NULL, 'active', 1, '2025-10-11 15:33:20', '2025-10-11 15:33:20', NULL, 'org_rep'),
(18, 'Kaye Ann Nicole J. Guinto', '2410221', 'mkanguinto@tip.edu.ph', '$2y$10$m0buTrh.9/NtRiDN16WYc.iwnbdpsLRssFdSpH4.LaskILT0Xfgda', NULL, NULL, 'active', 1, '2025-10-12 12:58:31', '2025-10-12 13:09:54', NULL, 'student');

-- --------------------------------------------------------

--
-- Structure for view `active_users`
--
DROP TABLE IF EXISTS `active_users`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `active_users`  AS SELECT `users`.`id` AS `id`, `users`.`fullname` AS `fullname`, `users`.`studentnum` AS `studentnum`, `users`.`email` AS `email`, CASE WHEN `users`.`google_id` is not null THEN 'Google' ELSE 'Standard' END AS `login_method`, `users`.`created_at` AS `created_at`, `users`.`last_login` AS `last_login` FROM `users` WHERE `users`.`status` = 'active' ;

-- --------------------------------------------------------

--
-- Structure for view `event_registrations_view`
--
DROP TABLE IF EXISTS `event_registrations_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `event_registrations_view`  AS SELECT `er`.`id` AS `registration_id`, `er`.`event_id` AS `event_id`, `e`.`title` AS `event_title`, `e`.`event_date` AS `event_date`, `e`.`start_time` AS `start_time`, `er`.`user_id` AS `user_id`, `u`.`fullname` AS `fullname`, `u`.`email` AS `email`, `u`.`studentnum` AS `studentnum`, `er`.`registration_date` AS `registration_date`, `er`.`status` AS `status` FROM ((`event_registrations` `er` join `events` `e` on(`er`.`event_id` = `e`.`id`)) join `users` `u` on(`er`.`user_id` = `u`.`id`)) ORDER BY `er`.`registration_date` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `recent_announcements`
--
DROP TABLE IF EXISTS `recent_announcements`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `recent_announcements`  AS SELECT `a`.`id` AS `id`, `a`.`title` AS `title`, `a`.`content` AS `content`, `a`.`category` AS `category`, `a`.`priority` AS `priority`, `a`.`is_published` AS `is_published`, `a`.`published_at` AS `published_at`, `a`.`created_by` AS `created_by`, `a`.`created_at` AS `created_at`, `a`.`updated_at` AS `updated_at`, `u`.`fullname` AS `creator_name` FROM (`announcements` `a` left join `users` `u` on(`a`.`created_by` = `u`.`id`)) WHERE `a`.`is_published` = 1 ORDER BY `a`.`published_at` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `upcoming_events`
--
DROP TABLE IF EXISTS `upcoming_events`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `upcoming_events`  AS SELECT `e`.`id` AS `id`, `e`.`title` AS `title`, `e`.`description` AS `description`, `e`.`event_date` AS `event_date`, `e`.`event_time` AS `event_time`, `e`.`location` AS `location`, `e`.`organizer` AS `organizer`, `e`.`status` AS `status`, `e`.`created_by` AS `created_by`, `e`.`created_at` AS `created_at`, `e`.`updated_at` AS `updated_at`, `u`.`fullname` AS `creator_name` FROM (`events` `e` left join `users` `u` on(`e`.`created_by` = `u`.`id`)) WHERE `e`.`status` = 'upcoming' AND `e`.`event_date` >= curdate() ORDER BY `e`.`event_date` ASC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_announcement_creator` (`created_by`),
  ADD KEY `idx_published` (`is_published`),
  ADD KEY `idx_category` (`category`);

--
-- Indexes for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_event_creator` (`created_by`),
  ADD KEY `idx_event_date` (`event_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_registration` (`event_id`,`user_id`),
  ADD KEY `idx_event_id` (`event_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_registration_date` (`registration_date`);

--
-- Indexes for table `login_history`
--
ALTER TABLE `login_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_login_user` (`user_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `org_reports`
--
ALTER TABLE `org_reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_session_user` (`user_id`),
  ADD KEY `idx_last_activity` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `studentnum` (`studentnum`),
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD KEY `idx_studentnum` (`studentnum`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_google_id` (`google_id`),
  ADD KEY `idx_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `event_registrations`
--
ALTER TABLE `event_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `login_history`
--
ALTER TABLE `login_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `org_reports`
--
ALTER TABLE `org_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `fk_announcement_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD CONSTRAINT `email_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `fk_event_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD CONSTRAINT `fk_registration_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_registration_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `login_history`
--
ALTER TABLE `login_history`
  ADD CONSTRAINT `fk_login_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `fk_session_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


