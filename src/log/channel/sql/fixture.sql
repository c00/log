-- NOTE, this is not proper SQL, you need to replace {{PREFIX}} with whatever. This is done automatically by the
-- Database::setupTables() function

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `{{PREFIX}}bag`;
CREATE TABLE `{{PREFIX}}bag` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `url` varchar(512) DEFAULT NULL,
  `ip` varchar(100) DEFAULT NULL,
  `verb` varchar(10) DEFAULT NULL,
  `userId` varchar(30) DEFAULT NULL,
  `date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `{{PREFIX}}item`;
CREATE TABLE `{{PREFIX}}item` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `caller` text NOT NULL,
  `trace` text NOT NULL,
  `tag` varchar(100) NOT NULL,
  `message` text,
  `level` int(11) NOT NULL,
  `object` text,
  `bagId` int NOT NULL,
  `date` int(11) NOT NULL,
  KEY `bagId` (`bagId`),
  CONSTRAINT `{{PREFIX}}item_ibfk_1` FOREIGN KEY (`bagId`) REFERENCES `{{PREFIX}}bag` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;