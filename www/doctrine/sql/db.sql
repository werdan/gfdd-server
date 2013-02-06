-- MySQL dump 10.13  Distrib 5.1.61, for debian-linux-gnu (i486)
--
-- Host: localhost    Database: gfdd
-- ------------------------------------------------------
-- Server version	5.1.61-2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `invitation_abuse`
--

DROP TABLE IF EXISTS `invitation_abuse`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invitation_abuse` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invitations_id` bigint(20) unsigned NOT NULL,
  `initiateid` bigint(20) unsigned DEFAULT NULL,
  `timestampcreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `latitude` decimal(8,6) NOT NULL,
  `longitude` decimal(8,6) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invitations_id` (`invitations_id`),
  KEY `initiateid` (`initiateid`),
  CONSTRAINT `invitation_abuse_ibfk_1` FOREIGN KEY (`invitations_id`) REFERENCES `invitations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `invitation_abuse_ibfk_2` FOREIGN KEY (`initiateid`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invitation_abuse`
--

LOCK TABLES `invitation_abuse` WRITE;
/*!40000 ALTER TABLE `invitation_abuse` DISABLE KEYS */;
/*!40000 ALTER TABLE `invitation_abuse` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invitations`
--

DROP TABLE IF EXISTS `invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invitations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `timestampcreated` int(12) unsigned NOT NULL,
  `inviterid` bigint(20) unsigned NOT NULL,
  `inviteeid` bigint(20) unsigned NOT NULL,
  `placeid` bigint(20) unsigned DEFAULT NULL,
  `timeshift` int(3) DEFAULT NULL,
  `finaltime` int(12) unsigned DEFAULT NULL,
  `useridproposedtimeplace` bigint(20) unsigned NOT NULL,
  `accepted` int(1) unsigned DEFAULT NULL,
  `rejected` int(1) unsigned DEFAULT NULL,
  `agreed` int(1) unsigned DEFAULT NULL,
  `inviterislate` int(1) unsigned DEFAULT NULL,
  `inviteeislate` int(1) unsigned DEFAULT NULL,
  `inviteecheckedin` int(1) unsigned DEFAULT NULL,
  `invitercheckedin` int(1) unsigned DEFAULT NULL,
  `abused` int(1) DEFAULT NULL,
  `cancelled` int(1) DEFAULT NULL COMMENT 'Отмена по таймауту',
  `finished` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inviterid_idx` (`inviterid`),
  KEY `inviteeid_idx` (`inviteeid`),
  KEY `placeid_idx` (`placeid`),
  CONSTRAINT `invitations_ibfk_1` FOREIGN KEY (`inviterid`) REFERENCES `users` (`id`),
  CONSTRAINT `invitations_ibfk_2` FOREIGN KEY (`inviteeid`) REFERENCES `users` (`id`),
  CONSTRAINT `invitations_ibfk_3` FOREIGN KEY (`placeid`) REFERENCES `places` (`id`),
  CONSTRAINT `invitations_placeid_places_id` FOREIGN KEY (`placeid`) REFERENCES `places` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=191 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invitations`
--

LOCK TABLES `invitations` WRITE;
/*!40000 ALTER TABLE `invitations` DISABLE KEYS */;
/*!40000 ALTER TABLE `invitations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `messageType` varchar(255) NOT NULL,
  `messageText` text NOT NULL,
  `senderid` bigint(20) unsigned NOT NULL,
  `recipientid` bigint(20) unsigned NOT NULL,
  `createdattimestamp` int(12) unsigned DEFAULT NULL,
  `readtimestamp` int(12) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `senderid_idx` (`senderid`),
  KEY `recipientid_idx` (`recipientid`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`senderid`) REFERENCES `users` (`id`),
  CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`recipientid`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `places`
--

DROP TABLE IF EXISTS `places`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `places` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `type_id` int(5) DEFAULT NULL,
  `city_id` int(5) NOT NULL,
  `address` text NOT NULL,
  `comments` text,
  `latitude` decimal(8,6) DEFAULT NULL,
  `longitude` decimal(8,6) DEFAULT NULL,
  `priority` int(2) unsigned DEFAULT '0',
  `selected` int(1) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type_id` (`type_id`),
  KEY `city_id` (`city_id`),
  CONSTRAINT `places_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `ref_place_types` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `places_ibfk_2` FOREIGN KEY (`city_id`) REFERENCES `ref_cities` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ref_cities`
--

DROP TABLE IF EXISTS `ref_cities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_cities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Справочник городов';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_cities`
--

LOCK TABLES `ref_cities` WRITE;
/*!40000 ALTER TABLE `ref_cities` DISABLE KEYS */;
INSERT INTO `ref_cities` VALUES (2,'Ð”Ð¾Ð½ÐµÑ†Ðº'),(1,'Москва');
/*!40000 ALTER TABLE `ref_cities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_place_types`
--

DROP TABLE IF EXISTS `ref_place_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ref_place_types` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Справочник типов заведений';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_place_types`
--

LOCK TABLES `ref_place_types` WRITE;
/*!40000 ALTER TABLE `ref_place_types` DISABLE KEYS */;
INSERT INTO `ref_place_types` VALUES (1,'Coffee');
/*!40000 ALTER TABLE `ref_place_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` text,
  `age` int(11) NOT NULL,
  `sex` varchar(1) NOT NULL,
  `lookingfor` varchar(2) NOT NULL,
  `country` varchar(2) NOT NULL,
  `provider` varchar(255) NOT NULL,
  `phoneid` varchar(255) NOT NULL,
  `secretkey` varchar(32) DEFAULT NULL,
  `photofilename` varchar(255) NOT NULL,
  `lastrequesttimestamp` int(12) DEFAULT NULL,
  `latitude` decimal(8,6) DEFAULT NULL,
  `longitude` decimal(8,6) DEFAULT NULL,
  `invitationid` bigint(20) unsigned DEFAULT NULL,
  `checkin` int(1) unsigned DEFAULT NULL,
  `clientOs` enum('ios','android') DEFAULT NULL,
  `restrictbefore` int(11) DEFAULT NULL COMMENT 'Ограничить доступ пользователю до наступления указанного времени',
  `extendedRange` int(1) unsigned DEFAULT NULL COMMENT 'Расширенный диапазон поиска',
  `rangeTimestamp` int(16) DEFAULT NULL COMMENT 'Время включения расширенного диапазона',
  `etag` varchar(40) DEFAULT NULL COMMENT 'Хеш фотографии',
  PRIMARY KEY (`id`),
  KEY `invitationid_idx` (`invitationid`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`invitationid`) REFERENCES `invitations` (`id`),
  CONSTRAINT `users_invitationid_invitations_id` FOREIGN KEY (`invitationid`) REFERENCES `invitations` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1170 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

--
-- Table structure for table `setting`
--

CREATE TABLE `setting` (
  `id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR( 125 ) NOT NULL ,
  `value` VARCHAR( 125 ) NULL ,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM ;

--
-- Dumping data for table `setting`
--

INSERT INTO `settings` (`id`, `name`, `value`) VALUES
(1, 'bot_on', '1'),
(2, 'bot_online_count', '10');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-04-17 21:53:54
