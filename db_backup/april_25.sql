-- MySQL dump 10.13  Distrib 5.1.66, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: captcha
-- ------------------------------------------------------
-- Server version	5.1.66-0+squeeze1

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
-- Table structure for table `pop_config`
--

DROP TABLE IF EXISTS `pop_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pop_config` (
  `table_name` varchar(30) NOT NULL DEFAULT '',
  `equil_size` int(11) DEFAULT NULL,
  `pop_size` int(11) DEFAULT NULL,
  `mut_rate` float DEFAULT NULL,
  `cross_rate` float DEFAULT NULL,
  `dying_rate` float DEFAULT NULL,
  PRIMARY KEY (`table_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pop_config`
--

LOCK TABLES `pop_config` WRITE;
/*!40000 ALTER TABLE `pop_config` DISABLE KEYS */;
INSERT INTO `pop_config` VALUES ('layer0',2,5,0.1,0.8,1),('layer1',2,5,0.1,0.8,1),('layer2',2,5,0.1,0.8,1),('layer3',2,3,0.1,0.8,1),('layer4',2,5,0.1,0.8,1);
/*!40000 ALTER TABLE `pop_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recaptcha`
--

DROP TABLE IF EXISTS `recaptcha`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recaptcha` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `text` varchar(30) DEFAULT NULL,
  `mturk` varchar(30) DEFAULT NULL,
  `mturk_time` int(11) DEFAULT NULL,
  `antigate` varchar(30) DEFAULT NULL,
  `fitness` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=51 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recaptcha`
--

LOCK TABLES `recaptcha` WRITE;
/*!40000 ALTER TABLE `recaptcha` DISABLE KEYS */;
INSERT INTO `recaptcha` VALUES (1,'reese avsita','~~~~~~~~~~',-1,'reese aysita',12),(2,'stictsu retractor','suctsuretractor',9134,'sdctsu retractor',2),(3,'prisoners nsaimmi','prisonersnsainnmi',30411,'prisoners nsaimmi',3),(4,'tiomare fine','tiomare fine',11997,'tioMare Fine',0),(5,'cares eprehir','cares eprehir',8913,'cares eprehir',0),(6,'was pmiddr','was pmiddr',5333,'was pmiddr',0),(7,'against keentsr','against keentsr',6745,'against keentsr',0),(8,'scityri herfelf','scityri herfekf',10564,'scityri herfelf',1),(9,'erebras saybrook','erebras saybrook',8861,'erebras saybrook',0),(10,'dedyvad move','dedyvad move',6060,'dedyvad move',0),(11,'iseaim offices','iseaim offices',5899,'iseaim offices',0),(12,'especially enasgai','especially enasgai',6363,'especially enasgai',0),(13,'tesidedo both','tesidedo both',6250,'tesidedo both',0),(14,'ryoutpr that','ryoutpr that',8281,'ryoutpr that',0),(15,'larksas array','larkscs array',7236,'larkscs array',0),(16,'defcribe dspubje','defcribe dspubje',5965,'defcribe dspubje',0),(17,'huaeure utterly','huaeure utterly',4957,'HUAEURe utterly',0),(18,'naturally flaxpres','naturally flaxpres',6437,'naturally flaxpres',0),(19,'sefirpo were','sefirpo were',5770,'sefirpo were',0),(20,'ostelet pada','ostelet pada',5524,'ostelet pada',0),(21,'oyarhom and','oyarhom and',4333,'oyarhom and',0),(22,'lfkbal the','lfkbal the',5395,'ifkbalTHE',2),(23,'may rivainf','may rivainf',4549,'may rivainf',0),(24,'theological nsedcci','theological nsedcci',6365,'theological nsedCci',0),(25,'taimodi escaped','taimodi escaped',6126,'taimodiescaped',1),(26,'student amminet','student amminet',4281,'studentamminet',1),(27,'regebs encourage','regebs encourage',4677,'Regebs encourage',0),(28,'hecart sure','hecart sure',4475,'hecart sure',0),(29,'gosvsi principal','gosysi principal',6444,'gosysi principal',0),(30,'effect gaidlne','effect gaidine',4759,'effect gaidlne',1),(31,'ketsting prize','ketsting prize',4830,'ketsting Prize',0),(32,'sufficient yaccomp','sufficient yaccomp',6392,'sufficient',8),(33,'understand eurismae','understand eurismae',6006,'understand eurism8e',1),(34,'general peditr','general peditr',4081,'general pediTr',0),(35,'security prohom','security prohom',6352,'security proHom',0),(36,'nlidan dutch','nlidan dutch',5591,'nlidanDuctch',2),(37,'make deboraa','make deborda',5053,'makedeborda',1),(38,'founded papownl','founded papownl',5102,'founded papownl',0),(39,'california treatsom','califormia treatsom',7461,'California Treatsom',1),(40,'gewledj meaning','gewledj meaning',7104,'gewledJ meaning',0),(41,'speakti all','speakti all',3206,'speakti all',0),(42,'ervedcz suppofition','ervedcz suppofition',7658,'ervedcz suppofition',0),(43,'areas acchem','areas acchem',4643,'areas acchem',0),(44,'sitfut and','sitfut and',4306,'sitfut and',0),(45,'hesntt compere','hesntt compere',4344,'hesntt compere',0),(46,'must dlownia','must dlownla',4299,'must dlownla',0),(47,'was epaston','was epaston',4046,'was epaston',0),(48,'which dempago','was epaston',9070,'which dempago',9),(49,'fruit eessget','fruit eessget',4970,'fruit eessget',0),(50,'deguhes caught','deguhey caught',5249,'deguheycaught',1);
/*!40000 ALTER TABLE `recaptcha` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-04-26  0:15:25
