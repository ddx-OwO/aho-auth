-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.36-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             9.5.0.5196
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for smartc_auth
CREATE DATABASE IF NOT EXISTS `smartc_auth` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `smartc_auth`;

-- Dumping structure for table smartc_auth.group
CREATE TABLE IF NOT EXISTS `group` (
  `group_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`group_id`),
  KEY `group_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- Dumping data for table smartc_auth.group: ~2 rows (approximately)
DELETE FROM `group`;
/*!40000 ALTER TABLE `group` DISABLE KEYS */;
INSERT INTO `group` (`group_id`, `name`, `description`, `created_at`, `updated_at`) VALUES
	(1, 'admin', 'Super User', '2018-12-12 05:17:35', '2018-12-12 05:17:35'),
	(2, 'user', 'User', '2018-12-12 05:17:35', '2018-12-12 05:17:35');
/*!40000 ALTER TABLE `group` ENABLE KEYS */;

-- Dumping structure for table smartc_auth.group_permission
CREATE TABLE IF NOT EXISTS `group_permission` (
  `group_id` bigint(20) unsigned NOT NULL,
  `permission_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`group_id`,`permission_id`),
  KEY `INDEX` (`permission_id`,`group_id`),
  CONSTRAINT `fk_group_id` FOREIGN KEY (`group_id`) REFERENCES `group` (`group_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_permission_id` FOREIGN KEY (`permission_id`) REFERENCES `permission` (`permission_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table smartc_auth.group_permission: ~14 rows (approximately)
DELETE FROM `group_permission`;
/*!40000 ALTER TABLE `group_permission` DISABLE KEYS */;
INSERT INTO `group_permission` (`group_id`, `permission_id`) VALUES
	(1, 1),
	(1, 2),
	(1, 3),
	(1, 4),
	(1, 5),
	(1, 6),
	(1, 7),
	(1, 8),
	(1, 9),
	(1, 10),
	(1, 11),
	(1, 12),
	(1, 14),
	(2, 1),
	(2, 2);
/*!40000 ALTER TABLE `group_permission` ENABLE KEYS */;

-- Dumping structure for table smartc_auth.login_attempt
CREATE TABLE IF NOT EXISTS `login_attempt` (
  `attempt_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `platform` varchar(64) NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`attempt_id`),
  KEY `ip_address` (`ip_address`),
  KEY `fk_attempts_user_id` (`user_id`),
  CONSTRAINT `fk_attempts_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table smartc_auth.login_attempt: ~0 rows (approximately)
DELETE FROM `login_attempt`;
/*!40000 ALTER TABLE `login_attempt` DISABLE KEYS */;
/*!40000 ALTER TABLE `login_attempt` ENABLE KEYS */;

-- Dumping structure for table smartc_auth.permission
CREATE TABLE IF NOT EXISTS `permission` (
  `permission_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`permission_id`),
  KEY `permission_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;

-- Dumping data for table smartc_auth.permission: ~16 rows (approximately)
DELETE FROM `permission`;
/*!40000 ALTER TABLE `permission` DISABLE KEYS */;
INSERT INTO `permission` (`permission_id`, `name`, `description`, `created_at`, `updated_at`) VALUES
	(1, 'user_read', '', '2019-01-28 00:28:08', '2019-01-31 00:26:36'),
	(2, 'user_update', '', '2019-01-28 00:28:05', '2019-01-31 00:26:41'),
	(3, 'user_delete', '', '2019-01-28 00:28:02', '2019-01-31 00:26:45'),
	(4, 'permission_read', '', '2019-01-28 00:27:58', '2019-01-31 00:26:51'),
	(5, 'permission_update', '', '2019-01-28 00:27:53', '2019-01-31 00:26:57'),
	(6, 'permission_delete', '', '2019-01-28 00:28:46', '2019-01-31 00:27:06'),
	(7, 'group_read', '', '2019-01-28 00:29:35', '2019-01-31 00:27:12'),
	(8, 'group_update', '', '2019-01-28 00:30:07', '2019-01-31 00:27:17'),
	(9, 'group_delete', '', '2019-01-28 00:31:32', '2019-01-31 00:27:22'),
	(10, 'user_create', '', '2019-01-28 00:31:55', '2019-01-31 00:27:28'),
	(11, 'permission_create', '', '2019-01-28 00:34:14', '2019-01-31 00:27:34'),
	(12, 'group_create', '', '2019-01-28 00:34:29', '2019-01-31 00:27:39'),
	(14, 'group_permission_manage', '', '2019-01-28 00:35:37', '2019-01-31 00:29:17'),
	(15, 'login_log_read', '', '2019-01-28 00:38:58', '2019-01-31 00:27:53'),
	(16, 'login_attempt_log_read', '', '2019-01-28 00:39:43', '2019-01-31 00:28:17'),
	(17, 'user_read_sensitive_data', '', '2019-01-31 00:29:47', '2019-01-31 00:29:47'),
	(18, 'login_revoke_self', '', '2019-01-31 00:31:09', '2019-01-31 00:31:09'),
	(19, 'login_revoke_super', '', '2019-01-31 00:31:42', '2019-01-31 00:31:42');
/*!40000 ALTER TABLE `permission` ENABLE KEYS */;

-- Dumping structure for table smartc_auth.user
CREATE TABLE IF NOT EXISTS `user` (
  `user_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `activation_code` varchar(128) DEFAULT NULL,
  `forgot_password_code` varchar(128) DEFAULT NULL,
  `status` tinyint(1) NOT NULL,
  `fullname` text NOT NULL,
  `identity_number` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `gender` tinyint(4) NOT NULL DEFAULT '0',
  `religion` varchar(20) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `birthday` date NOT NULL,
  `photo_file_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- Dumping data for table smartc_auth.user: ~2 rows (approximately)
DELETE FROM `user`;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` (`user_id`, `username`, `email`, `password`, `activation_code`, `forgot_password_code`, `status`, `fullname`, `identity_number`, `address`, `gender`, `religion`, `phone`, `birthday`, `photo_file_path`, `created_at`, `updated_at`) VALUES
	(1, 'admin', 'demo@localhost.com', '$2y$10$9pwjiH37FhLoVBwm3YTyZuVKSI7vtdU7sZZvAWBnrfqJfOMb5xTOi', 'NULL', 'NULL', 1, 'Super Admin', '', '', 0, '', '', '2018-04-04', '', '2018-12-12 01:12:29', '2018-12-12 01:12:45'),
	(2, 'user2', 'user2@localhost', '$2y$10$vJR54ZobnetIshAzv6KdIuCU.oMXOFTTY3956v.NlbHyabNWwqNhu', 'NULL', 'NULL', 1, '', '', '', 0, '', '', '2018-08-18', '', '2018-12-12 01:12:29', '2018-12-12 01:12:45'),
	(3, 'user3', 'user3@localhost', '$2y$10$vJR54ZobnetIshAzv6KdIuCU.oMXOFTTY3956v.NlbHyabNWwqNhu', 'NULL', 'NULL', 1, '', '', '', 0, '', '', '2018-08-18', '', '2018-12-12 01:12:29', '2018-12-12 01:12:45');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;

-- Dumping structure for table smartc_auth.user_group
CREATE TABLE IF NOT EXISTS `user_group` (
  `user_id` bigint(20) unsigned NOT NULL,
  `group_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`group_id`),
  KEY `INDEX` (`group_id`,`user_id`),
  CONSTRAINT `fk_user_groups_group_id` FOREIGN KEY (`group_id`) REFERENCES `group` (`group_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_user_groups_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table smartc_auth.user_group: ~4 rows (approximately)
DELETE FROM `user_group`;
/*!40000 ALTER TABLE `user_group` DISABLE KEYS */;
INSERT INTO `user_group` (`user_id`, `group_id`) VALUES
	(1, 1),
	(1, 2),
	(2, 1),
	(3, 1);
/*!40000 ALTER TABLE `user_group` ENABLE KEYS */;

-- Dumping structure for table smartc_auth.user_login
CREATE TABLE IF NOT EXISTS `user_login` (
  `login_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `refresh_token` varchar(255) NOT NULL,
  `ip_address` varchar(64) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `platform` varchar(64) NOT NULL,
  `revoked` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `login_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expires_in` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`login_id`),
  KEY `fk_user_logins_user_id` (`user_id`),
  KEY `token` (`refresh_token`),
  CONSTRAINT `fk_user_logins_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table smartc_auth.user_login: ~0 rows (approximately)
DELETE FROM `user_login`;
/*!40000 ALTER TABLE `user_login` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_login` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
