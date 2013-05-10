-- MySQL dump 10.11
--
-- Host: 10.0.0.162    Database: cloud_log_db
-- ------------------------------------------------------
-- Server version	5.0.81-log

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
-- Table structure for table `log_service_speed`
--

DROP TABLE IF EXISTS `log_service_speed`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `log_service_speed` (
  `service_id` int(11) NOT NULL default '0',
  `idc` varchar(10) collate utf8_unicode_ci NOT NULL default '',
  `log_time` datetime NOT NULL default '2010-09-01 00:00:00',
  `millisecond_90` int(11) NOT NULL default '0',
  `percent_100` int(11) NOT NULL default '0',
  UNIQUE KEY `service_id` (`service_id`,`idc`,`log_time`),
  KEY `log_time` (`log_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `status_logs`
--

DROP TABLE IF EXISTS `status_logs`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `status_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `log_idc` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'idc10',
  `log_host` int(11) NOT NULL DEFAULT '-1',
  `log_type` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `log_name` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `log_value` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `log_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `target_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_1` (`log_idc`,`log_host`,`log_type`,`log_name`,`target_id`,`log_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET character_set_client = @saved_cs_client;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2011-03-31  1:50:04
