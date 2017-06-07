-- Adminer 4.3.1 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

USE `test_log`;

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `bag`;
CREATE TABLE `bag` (
  `id` varchar(30) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `ip` varchar(100) DEFAULT NULL,
  `userId` varchar(30) DEFAULT NULL,
  `date` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `item`;
CREATE TABLE `item` (
  `id` varchar(30) NOT NULL,
  `caller` text NOT NULL,
  `trace` text NOT NULL,
  `message` text,
  `level` int(11) NOT NULL,
  `object` text,
  `bagId` varchar(30) NOT NULL,
  `date` int(11) NOT NULL,
  `order` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order` (`order`),
  KEY `bagId` (`bagId`),
  CONSTRAINT `item_ibfk_1` FOREIGN KEY (`bagId`) REFERENCES `bag` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- 2017-06-07 06:22:12