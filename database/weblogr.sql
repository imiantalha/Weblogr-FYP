-- Weblogr database bootstrap
-- Fresh local-development schema. No application/sample data is included.
-- Import this file into phpMyAdmin to create a clean database.
-- Generated for the current Weblogr schema on 2026-08-21.

CREATE DATABASE IF NOT EXISTS `weblogr` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `weblogr`;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `moderation_logs`, `reports`, `notifications`, `followers`, `comments`, `draft_posts`, `profile`, `blogs`, `users`;

CREATE TABLE `users` (
  `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fullname` VARCHAR(100) NOT NULL,
  `username` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `otp` VARCHAR(10) NOT NULL DEFAULT '',
  `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `user_type` ENUM('Common user','Admin') NOT NULL DEFAULT 'Common user',
  `google_id` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`user_id`), UNIQUE KEY `uq_users_username` (`username`), UNIQUE KEY `uq_users_email` (`email`), UNIQUE KEY `uq_users_google_id` (`google_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `blogs` (
  `blog_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `created_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `description` TEXT DEFAULT NULL,
  `category` VARCHAR(50) NOT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `likes` INT UNSIGNED NOT NULL DEFAULT 0,
  `user_id` INT UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`blog_id`), KEY `idx_blogs_user_date` (`user_id`,`created_date`), KEY `idx_blogs_category_date` (`category`,`created_date`),
  CONSTRAINT `fk_blogs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `draft_posts` (
  `draft_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `created_date` DATE NOT NULL,
  `description` TEXT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `category` VARCHAR(50) NOT NULL,
  `user_id` INT UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`draft_id`), KEY `idx_drafts_user_date` (`user_id`,`created_date`),
  CONSTRAINT `fk_drafts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `comments` (
  `comment_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `blog_id` INT UNSIGNED DEFAULT NULL,
  `commenter_id` INT UNSIGNED DEFAULT NULL,
  `comment_text` TEXT DEFAULT NULL,
  `likes` INT UNSIGNED NOT NULL DEFAULT 0,
  `comment_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`comment_id`), KEY `idx_comments_blog_date` (`blog_id`,`comment_date`), KEY `idx_comments_commenter` (`commenter_id`),
  CONSTRAINT `fk_comments_blog` FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`blog_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_comments_user` FOREIGN KEY (`commenter_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `followers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `blogger_id` INT UNSIGNED DEFAULT NULL,
  `follower_id` INT UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`), UNIQUE KEY `uq_followers_pair` (`blogger_id`,`follower_id`), KEY `idx_followers_blogger` (`blogger_id`), KEY `idx_followers_follower` (`follower_id`),
  CONSTRAINT `fk_followers_blogger` FOREIGN KEY (`blogger_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_followers_follower` FOREIGN KEY (`follower_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `content` VARCHAR(255) DEFAULT NULL,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`), KEY `idx_notifications_user_read` (`user_id`,`is_read`,`id`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `profile` (
  `profile_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `full_name` VARCHAR(100) DEFAULT NULL,
  `profile_picture` VARCHAR(255) DEFAULT NULL,
  `bio` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`profile_id`), UNIQUE KEY `uq_profile_user` (`user_id`),
  CONSTRAINT `fk_profile_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `reports` (
  `report_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `blog_id` INT UNSIGNED NOT NULL,
  `reporter_id` INT UNSIGNED NOT NULL,
  `reason` VARCHAR(100) NOT NULL,
  `details` TEXT DEFAULT NULL,
  `status` ENUM('pending','reviewed','dismissed') NOT NULL DEFAULT 'pending',
  `reviewed_by` INT UNSIGNED DEFAULT NULL,
  `reviewed_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`report_id`), KEY `idx_reports_status_created` (`status`,`created_at`), KEY `idx_reports_blog` (`blog_id`), KEY `idx_reports_reporter` (`reporter_id`),
  CONSTRAINT `fk_reports_blog` FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`blog_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_reports_reporter` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_reports_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `moderation_logs` (
  `log_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` INT UNSIGNED NOT NULL,
  `report_id` INT UNSIGNED DEFAULT NULL,
  `blog_id` INT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(50) NOT NULL,
  `notes` VARCHAR(500) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`), KEY `idx_moderation_admin_created` (`admin_id`,`created_at`), KEY `idx_moderation_report` (`report_id`), KEY `idx_moderation_blog` (`blog_id`),
  CONSTRAINT `fk_moderation_admin` FOREIGN KEY (`admin_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_moderation_report` FOREIGN KEY (`report_id`) REFERENCES `reports` (`report_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_moderation_blog` FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`blog_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Intentionally no INSERT statements: the application starts with an empty database.
-- Create the first account through the normal registration flow or Google authentication.
