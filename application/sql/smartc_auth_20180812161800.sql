-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.19-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             9.5.0.5196
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for smartc_auth
DROP DATABASE IF EXISTS `smartc_auth`;
CREATE DATABASE IF NOT EXISTS `smartc_auth` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `smartc_auth`;

-- Dumping structure for table smartc_auth.api_keys
DROP TABLE IF EXISTS `api_keys`;
CREATE TABLE IF NOT EXISTS `api_keys` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `key` varchar(40) NOT NULL,
  `level` int(2) unsigned NOT NULL,
  `ignore_limits` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_private_key` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ip_addresses` varchar(50) NOT NULL,
  `date_created` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_user_id` (`user_id`),
  CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- Dumping data for table smartc_auth.api_keys: ~2 rows (approximately)
DELETE FROM `api_keys`;
/*!40000 ALTER TABLE `api_keys` DISABLE KEYS */;
INSERT INTO `api_keys` (`id`, `user_id`, `key`, `level`, `ignore_limits`, `is_private_key`, `ip_addresses`, `date_created`) VALUES
	(1, 1, '49wmdh3mfpgkswccgo00kosocc0sccog0scockow', 1, 0, 0, '::1', 150123),
	(6, 1, 's8ggwoo8owwo4wkogw8cgkgoo4sow88kc4808wss', 1, 1, 0, '', 1532347030);
/*!40000 ALTER TABLE `api_keys` ENABLE KEYS */;

-- Dumping structure for table smartc_auth.groups
DROP TABLE IF EXISTS `groups`;
CREATE TABLE IF NOT EXISTS `groups` (
  `group_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) NOT NULL,
  `group_description` text NOT NULL,
  `created_on` int(10) unsigned NOT NULL,
  PRIMARY KEY (`group_id`),
  KEY `name` (`group_name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- Dumping data for table smartc_auth.groups: ~2 rows (approximately)
DELETE FROM `groups`;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` (`group_id`, `group_name`, `group_description`, `created_on`) VALUES
	(1, 'admin', 'Super User', 0),
	(2, 'user', 'User', 0);
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;

-- Dumping structure for table smartc_auth.group_permissions
DROP TABLE IF EXISTS `group_permissions`;
CREATE TABLE IF NOT EXISTS `group_permissions` (
  `group_id` bigint(20) unsigned NOT NULL,
  `permission_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`group_id`,`permission_id`),
  KEY `fk_permission_id` (`permission_id`),
  CONSTRAINT `fk_group_id` FOREIGN KEY (`group_id`) REFERENCES `groups` (`group_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_permission_id` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table smartc_auth.group_permissions: ~6 rows (approximately)
DELETE FROM `group_permissions`;
/*!40000 ALTER TABLE `group_permissions` DISABLE KEYS */;
INSERT INTO `group_permissions` (`group_id`, `permission_id`) VALUES
	(1, 1),
	(1, 2),
	(1, 3),
	(1, 4),
	(2, 2),
	(2, 3);
/*!40000 ALTER TABLE `group_permissions` ENABLE KEYS */;

-- Dumping structure for table smartc_auth.login_attempts
DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `attempt_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `identity` varchar(255) NOT NULL,
  `time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`attempt_id`),
  KEY `ip_address` (`ip_address`),
  KEY `identity` (`identity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table smartc_auth.login_attempts: ~0 rows (approximately)
DELETE FROM `login_attempts`;
/*!40000 ALTER TABLE `login_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `login_attempts` ENABLE KEYS */;

-- Dumping structure for table smartc_auth.permissions
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE IF NOT EXISTS `permissions` (
  `permission_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `permission_name` varchar(255) NOT NULL,
  `permission_description` text NOT NULL,
  `created_on` int(10) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`),
  UNIQUE KEY `permission_name` (`permission_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- Dumping data for table smartc_auth.permissions: ~5 rows (approximately)
DELETE FROM `permissions`;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` (`permission_id`, `permission_name`, `permission_description`, `created_on`) VALUES
	(1, 'view_user_data', '', 0),
	(2, 'change_user_data', '', 0),
	(3, 'delete_user_data', '', 0),
	(4, 'manage_user_permissions', '', 0),
	(5, 'view_only', '', 0);
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;

-- Dumping structure for table smartc_auth.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_on` int(10) unsigned NOT NULL,
  `activation_code` varchar(128) NOT NULL,
  `forgot_password_code` varchar(128) NOT NULL,
  `status` tinyint(2) NOT NULL,
  `fullname` text NOT NULL,
  `identity_number` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `gender` varchar(24) NOT NULL,
  `religion` varchar(20) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `birthday` date NOT NULL,
  `photo_file_path` varchar(255) NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- Dumping data for table smartc_auth.users: ~1 rows (approximately)
DELETE FROM `users`;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `created_on`, `activation_code`, `forgot_password_code`, `status`, `fullname`, `identity_number`, `address`, `gender`, `religion`, `phone`, `birthday`, `photo_file_path`) VALUES
	(1, 'admin', 'demo@localhost.com', '$2y$10$VyCQTvBy.pUMecq7VASww.pDfEVs2YTrxCpK4EFprqCa7ih0SAGCu', 1517734607, '0', 'ffe2f357af5f8139b225df2e7fc75fceeae61fb718eca042738395c79cac80d0', 1, 'Super Admin', '', '', '0', '', '', '2018-04-04', '');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

-- Dumping structure for table smartc_auth.user_groups
DROP TABLE IF EXISTS `user_groups`;
CREATE TABLE IF NOT EXISTS `user_groups` (
  `user_id` bigint(20) unsigned NOT NULL,
  `group_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`group_id`),
  KEY `fk_user_groups_user_id` (`user_id`),
  KEY `fk_user_groups_group_id` (`group_id`),
  CONSTRAINT `fk_user_groups_group_id` FOREIGN KEY (`group_id`) REFERENCES `groups` (`group_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_user_groups_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table smartc_auth.user_groups: ~1 rows (approximately)
DELETE FROM `user_groups`;
/*!40000 ALTER TABLE `user_groups` DISABLE KEYS */;
INSERT INTO `user_groups` (`user_id`, `group_id`) VALUES
	(1, 1);
/*!40000 ALTER TABLE `user_groups` ENABLE KEYS */;

-- Dumping structure for table smartc_auth.user_logins
DROP TABLE IF EXISTS `user_logins`;
CREATE TABLE IF NOT EXISTS `user_logins` (
  `login_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `identifier` varchar(64) NOT NULL,
  `token` varchar(255) NOT NULL,
  `ip_address` varchar(64) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `platform` varchar(255) NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `expiration_time` bigint(20) unsigned NOT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`login_id`),
  KEY `fk_user_logins_user_id` (`user_id`),
  CONSTRAINT `fk_user_logins_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- Dumping data for table smartc_auth.user_logins: ~3 rows (approximately)
DELETE FROM `user_logins`;
/*!40000 ALTER TABLE `user_logins` DISABLE KEYS */;
INSERT INTO `user_logins` (`login_id`, `user_id`, `identifier`, `token`, `ip_address`, `user_agent`, `platform`, `time`, `expiration_time`, `status`) VALUES
	(1, 1, '$2y$10$tcY8mN3Y4zz9l67cHhB9cOw610wsRxXizOpD93ZkGHcOg0phIvLdW', '$2y$10$xsjuQf3UtYzINPTFef3ndeV/rSs5v1RfVzQdlsLKQgHAkyswYaFK6', '::1', 'Chrome:66.0.3359.139', 'Windows 7', 1525693770, 1525700970, 0),
	(2, 1, '$2y$10$dHSdubbh0eDm//zs1CO4duu9z.hiL/ipMhrkNzMuRmOH79xmiyema', '$2y$10$FFXgVAbHcZaXsZrqk6qPZOmQyrRAvlN44iZZPODzjNQKhZ5r3DyUe', '::1', 'Chrome:66.0.3359.139', 'Windows 7', 1526543126, 1557323142, 1),
	(3, 1, '$2y$10$p7AjZsxubIEHuF5sxlNeAOIOjLLjEmt8GjPzeKh/QMx4ptvWQfysy', '$2y$10$5SKo6gRryugabat7qRZIrOjGdbnZ7F86dS5o3ISpspJqoDw1UNOle', '::1', 'Chrome:67.0.3396.99', 'Windows 7', 1531294375, 1531301575, 0);
/*!40000 ALTER TABLE `user_logins` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
