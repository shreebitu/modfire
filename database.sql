CREATE DATABASE IF NOT EXISTS shreebitu_playstore;
USE shreebitu_playstore;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL UNIQUE,
    `email` varchar(100) NOT NULL UNIQUE,
    `password` varchar(255) NOT NULL,
    `role` enum('user','admin') DEFAULT 'user',
    `shadow_banned` tinyint(1) DEFAULT 0,
    `session_token` varchar(255) DEFAULT NULL,
    `last_active` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL UNIQUE,
    `slug` varchar(50) NOT NULL UNIQUE,
    `description` text DEFAULT NULL,
    `icon` varchar(50) DEFAULT 'folder',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Apps Table
CREATE TABLE IF NOT EXISTS `apps` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `description` text NOT NULL,
    `logo` varchar(255) NOT NULL,
    `apk_link` varchar(255) NOT NULL,
    `category` varchar(50) DEFAULT 'General',
    `user_id` int(11) NOT NULL,
    `file_size` varchar(100) DEFAULT NULL,
    `os_compatible` varchar(100) DEFAULT NULL,
    `language` varchar(100) DEFAULT NULL,
    `license_type` varchar(100) DEFAULT NULL,
    `developer` varchar(100) DEFAULT NULL,
    `status` enum('pending','approved','rejected') DEFAULT 'pending',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Settings Table
CREATE TABLE IF NOT EXISTS `settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `setting_key` varchar(100) NOT NULL UNIQUE,
    `setting_value` text DEFAULT NULL,
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Downloads Log
CREATE TABLE IF NOT EXISTS `downloads_log` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `app_id` int(11) NOT NULL,
    `user_id` int(11) DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `downloaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    FOREIGN KEY (`app_id`) REFERENCES `apps` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Reports Table
CREATE TABLE IF NOT EXISTS `reports` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `app_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `reason` text NOT NULL,
    `status` enum('pending','resolved','dismissed') DEFAULT 'pending',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    FOREIGN KEY (`app_id`) REFERENCES `apps` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Favorites Table
CREATE TABLE IF NOT EXISTS `favorites` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `app_id` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `user_app` (`user_id`,`app_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`app_id`) REFERENCES `apps` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Notifications Table
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `message` text NOT NULL,
    `is_read` tinyint(1) DEFAULT 0,
    `type` varchar(20) DEFAULT 'system',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Activity Logs Table
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `action` varchar(100) NOT NULL,
    `details` text DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Blocked IPs Table
CREATE TABLE IF NOT EXISTS `blocked_ips` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `ip_address` varchar(45) NOT NULL UNIQUE,
    `reason` text DEFAULT NULL,
    `blocked_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. Reviews Table
CREATE TABLE IF NOT EXISTS `reviews` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `app_id` int(11) NOT NULL,
    `rating` int(11) NOT NULL,
    `comment` text NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`app_id`) REFERENCES `apps` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default Data
INSERT IGNORE INTO `categories` (`name`, `slug`, `description`, `icon`) VALUES 
('Apps', 'apk', 'Android applications and tools', 'android'),
('Notes', 'pdf', 'Educational notes and PDF documents', 'description'),
('Presentations', 'ppt', 'PowerPoint and slide presentations', 'present_to_all'),
('Windows', 'windows', 'Windows software and utilities', 'desktop_windows'),
('Tools', 'tools', 'Utility tools', 'build'),
('Games', 'games', 'Video games', 'sports_esports'),
('Social', 'social', 'Social media apps', 'share'),
('Productivity', 'productivity', 'Productivity apps', 'assignment');

-- Default admin password is 'admin123'
INSERT IGNORE INTO `users` (`username`, `email`, `password`, `role`) VALUES 
('admin', 'admin@shreebitu.in', '$2y$10$e.w2pZ8k9uF4yY.rU6.2/eu6C1v6pC0x2W6G6.P8H1E4v6qR2/F2q', 'admin');
