-- MySQL dump 10.9
--
-- Host: localhost    Database: contas
-- ------------------------------------------------------
-- Server version	4.1.12

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `contas`
--

DROP TABLE IF EXISTS `contas`;
CREATE TABLE `contas` (
  `conta_id` int(3) NOT NULL default '0',
  `conta_num` char(30) NOT NULL default '',
  `conta_nome` char(30) NOT NULL default '',
  `tipo_id` int(2) default NULL,
  `conta_nib` char(24) default NULL,
  `conta_abertura` date default NULL,
  `conta_fecho` date default NULL,
  `activa` int(1) NOT NULL default '0',
  PRIMARY KEY  (`conta_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4_general_ci;

--
-- Dumping data for table `contas`
--


/*!40000 ALTER TABLE `contas` DISABLE KEYS */;
LOCK TABLES `contas` WRITE;
INSERT INTO `contas` VALUES (1,'','Caixa (Euro)',2,'','1990-01-01','1990-01-01',1);
UNLOCK TABLES;
/*!40000 ALTER TABLE `contas` ENABLE KEYS */;

--
-- Table structure for table `defaults`
--

DROP TABLE IF EXISTS `defaults`;
CREATE TABLE `defaults` (
  `id` int(1) NOT NULL default '0',
  `tipo_mov` int(3) default NULL,
  `conta_id` int(3) default NULL,
  `moeda_mov` char(3) default NULL,
  `data` date default NULL,
  `deb_cred` enum('1','-1') default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4_general_ci;

--
-- Dumping data for table `defaults`
--


/*!40000 ALTER TABLE `defaults` DISABLE KEYS */;
LOCK TABLES `defaults` WRITE;
INSERT INTO `defaults` VALUES (1,1,1,'EUR','2005-09-30','1');
UNLOCK TABLES;
/*!40000 ALTER TABLE `defaults` ENABLE KEYS */;

--
-- Table structure for table `moedas`
--

DROP TABLE IF EXISTS `moedas`;
CREATE TABLE `moedas` (
  `id` int(4) NOT NULL DEFAULT 0,
  `code` char(3) NOT NULL default '',
  `description` char(30) default NULL,
  `exchangeRate` float(8,6) default NULL,
  PRIMARY KEY  (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4_general_ci;

--
-- Dumping data for table `moedas`
--


/*!40000 ALTER TABLE `moedas` DISABLE KEYS */;
LOCK TABLES `moedas` WRITE;
INSERT INTO `moedas` VALUES (0, 'EUR','Euro', 1.000000);
UNLOCK TABLES;
/*!40000 ALTER TABLE `moedas` ENABLE KEYS */;

--
-- Table structure for table `movimentos`
--

DROP TABLE IF EXISTS `movimentos`;
CREATE TABLE `movimentos` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `entry_date` date DEFAULT NULL,
  `categoryId` int(3) DEFAULT NULL,
  `account_id` int(3) DEFAULT NULL,
  `currency_id` char(3) NOT NULL DEFAULT 'EUR',
  `direction` tinyint(1) NOT NULL DEFAULT 1,
  `currencyAmount` float(10,2) DEFAULT NULL,
  `euroAmount` float(10,2) DEFAULT NULL,
  `exchange_rate` float(9,4) NOT NULL DEFAULT 1.0000,
  `a_pagar` tinyint(1) NOT NULL DEFAULT 0,
  `com_talao` tinyint(1) NOT NULL DEFAULT 0,
  `remarks` char(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4_general_ci;

--
-- Table structure for table `tipo_contas`
--

DROP TABLE IF EXISTS `tipo_contas`;
CREATE TABLE `tipo_contas` (
  `tipo_id` int(2) NOT NULL DEFAULT 0,
  `tipo_desc` char(30) DEFAULT NULL,
  `savings` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`tipo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4_general_ci;

--
-- Dumping data for table `tipo_contas`
--


/*!40000 ALTER TABLE `tipo_contas` DISABLE KEYS */;
LOCK TABLES `tipo_contas` WRITE;
INSERT INTO `tipo_contas` VALUES (1,'Conta ficticia',0),(2,'Conta bancaria',0);
UNLOCK TABLES;
/*!40000 ALTER TABLE `tipo_contas` ENABLE KEYS */;

--
-- Table structure for table `tipo_mov`
--

DROP TABLE IF EXISTS `tipo_mov`;
CREATE TABLE `tipo_mov` (
  `tipo_id` int(3) NOT NULL DEFAULT 0,
  `parent_id` int(3) DEFAULT NULL,
  `tipo_desc` char(50) DEFAULT NULL,
  `active` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`tipo_id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `parent_id` FOREIGN KEY (`parent_id`) REFERENCES `tipo_mov` (`tipo_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4_general_ci;

--
-- Dumping data for table `tipo_mov`
--


/*!40000 ALTER TABLE `tipo_mov` DISABLE KEYS */;
LOCK TABLES `tipo_mov` WRITE;
INSERT INTO `tipo_mov` VALUES (0,NULL,'Sem categoria',1),(1,0,'Saldo inicial',1);
UNLOCK TABLES;
/*!40000 ALTER TABLE `tipo_mov` ENABLE KEYS */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
