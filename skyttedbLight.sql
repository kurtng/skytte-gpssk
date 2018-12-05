-- CREATE DATABASE /*!32312 IF NOT EXISTS*/ `gunnar` /*!40100 DEFAULT CHARACTER SET latin1 */;

-- USE `gunnar`;

-- CREATE USER 'skyskol.com/mysq'@'localhost' IDENTIFIED BY '875CD99826627B93FAFD164FEA4C71';

-- GRANT ALL ON *.* TO 'skyskol.com/mysq'@'localhost';

-- Table structure for table `tbl_Pistol_Dibs_Payment`
  DROP TABLE IF EXISTS `tbl_Pistol_Dibs_Payment`;
  CREATE TABLE `tbl_Pistol_Dibs_Payment` (
    `Id` bigint(20) NOT NULL auto_increment,
    `TransactionId` varchar(40) NOT NULL,
    `StatusCode` int(11) NOT NULL  ,
    `PayDate` timestamp NOT NULL default CURRENT_TIMESTAMP ,
    `OrderId` varchar(100) NOT NULL  ,
    `GunCard` varchar(20) NOT NULL  ,
    `Amount` int(11) NOT NULL  ,
    `ApprovalCode` varchar(20) NOT NULL  ,
    `PayType` varchar(20) NOT NULL,
    `CompetitionId` int(11) NOT NULL  ,
    `ShotId` int(11) NOT NULL  ,
    PRIMARY KEY  (`Id`), UNIQUE (`OrderId`), UNIQUE (`TransactionId`)
  ) ENGINE=MyISAM AUTO_INCREMENT=102 DEFAULT CHARSET=latin1;


-- Table structure for table `tbl_Pistol_Club`
DROP TABLE IF EXISTS `tbl_Pistol_Club`;
CREATE TABLE `tbl_Pistol_Club` (
  `Id` bigint(20) NOT NULL auto_increment,
  `Name` varchar(100) NOT NULL,
  `CreateDate` timestamp NOT NULL default CURRENT_TIMESTAMP ,
  PRIMARY KEY  (`Id`),
  UNIQUE KEY `i1` (`Name`)
) ENGINE=MyISAM AUTO_INCREMENT=102 DEFAULT CHARSET=latin1;

INSERT INTO `tbl_Pistol_Club` VALUES (1,'AlingsÃ¥s','2008-05-14 17:16:21'),(2,'Arvika PK','2008-05-14 17:16:21'),(3,'Bengtsfors PSK','2008-05-14 17:16:21'),(4,'BjÃ¶rlanda SKF','2008-05-14 17:16:21'),(5,'BorÃ¥s PS','2008-05-14 17:16:21'),(6,'BorÃ¥spolisen','2008-05-14 17:16:21'),(7,'Bredared SF','2008-05-14 17:16:21'),(8,'Bredareds SSK','2008-05-14 17:16:21'),(9,'Broaryds PK','2008-05-14 17:16:21'),(10,'Dals PSKF','2008-05-14 17:16:21'),(11,'DonsÃ¶ PF','2008-05-14 17:16:21'),(12,'Eds PSK','2008-05-14 17:16:21'),(13,'F14 Skf','2008-05-14 17:16:21'),(14,'F7 PK','2008-05-14 17:16:21'),(15,'FOK BorÃ¥s','2008-05-14 17:16:21'),(16,'Fagersta PSK','2008-05-14 17:16:21'),(17,'Falkenbergs PK','2008-05-14 17:16:21'),(18,'FÃ¤rjenÃ¤s PK','2008-05-14 17:16:21'),(19,'Gamla Surte Skyttar','2008-05-14 17:16:21'),(20,'Gbg HemvÃ¤rnsbef PK','2008-05-14 17:16:21'),(21,'GrÃ¤storps PSK','2008-05-14 17:16:21'),(22,'GÃ¶teborgs PSSK','2008-05-14 17:16:21'),(23,'GÃ¶teborgs SKS','2008-05-14 17:16:21'),(24,'Habo PK','2008-05-14 17:16:21'),(25,'Hagfors Uddeholm Pk','2008-05-14 17:16:21'),(26,'Halmstads sport','2008-05-14 17:16:21'),(27,'Harplinge PK','2008-05-14 17:16:21'),(28,'Horns PSS','2008-05-14 17:16:21'),(29,'HÃ¤rlanda PK','2008-05-14 17:16:21'),(30,'HÃ¤rryda PSS','2008-05-14 17:16:21'),(31,'HÃ¶rsholm PF','2008-05-14 17:16:21'),(32,'I 15','2008-05-14 17:16:21'),(33,'I 16 SKF','2008-05-14 17:16:21'),(34,'JBF','2008-05-14 17:16:21'),(35,'JÃ¤mjÃ¶ SPK','2008-05-14 17:16:21'),(36,'JÃ¶nkÃ¶pings PK','2008-05-14 17:16:21'),(37,'KA2 PSK','2008-05-14 17:16:21'),(38,'Karlstad PSK','2008-05-14 17:16:21'),(39,'Kullens PK','2008-05-14 17:16:21'),(40,'Kumla SS','2008-05-14 17:16:21'),(41,'Kungsbacka-Wiske SPSK','2008-05-14 17:16:21'),(42,'Kungshamn PSK','2008-05-14 17:16:21'),(43,'Kvibergs PK','2008-05-14 17:16:21'),(44,'Laholms PSK','2008-05-14 17:16:21'),(45,'Lerums JoSSK','2008-05-14 17:16:21'),(46,'LidkÃ¶pings PF','2008-05-14 17:16:21'),(47,'Lilla Edets PK','2008-05-14 17:16:21'),(48,'Lysekil PSK','2008-05-14 17:16:21'),(49,'LÃ¶dÃ¶se Pk','2008-05-14 17:16:21'),(50,'Mariestads PK','2008-05-14 17:16:21'),(51,'Marinens SKF','2008-05-14 17:16:21'),(52,'Markaryds SPK','2008-05-14 17:16:21'),(53,'Marks PK','2008-05-14 17:16:21'),(54,'Melleruds PK','2008-05-14 17:16:21'),(55,'Munkfors PK','2008-05-14 17:16:21'),(56,'MÃ¥lÃ¶ga Skyttegille','2008-05-14 17:16:21'),(57,'MÃ¶lndals PK','2008-05-14 17:16:21'),(58,'MÃ¶lndals SF','2008-05-14 17:16:21'),(59,'P4 IF','2008-05-14 17:16:21'),(60,'Polisen','2008-05-14 17:16:21'),(61,'RoasjÃ¶ SKF','2008-05-14 17:16:21'),(62,'SJ PK LuleÃ¥','2008-05-14 17:16:21'),(63,'Saab PK','2008-05-14 17:16:21'),(64,'Sjoormens PK','2008-05-14 17:16:21'),(65,'Sjuntorps PSK','2008-05-14 17:16:21'),(66,'Skepplanda SKF','2008-05-14 17:16:21'),(67,'SkÃ¶vde PK','2008-05-14 17:16:21'),(68,'Sollebrunns PK','2008-05-14 17:16:21'),(69,'StarrkÃ¤rrs SF','2008-05-14 17:16:21'),(70,'Stenungsund','2008-05-14 17:16:21'),(71,'StrÃ¤ngnÃ¤s PK','2008-05-14 17:16:21'),(72,'Surte-Bohus SSG','2008-05-14 17:16:21'),(73,'SÃ¤ffle PK','2008-05-14 17:16:21'),(74,'SÃ¤tila PK','2008-05-14 17:16:21'),(75,'SÃ¤ve PSK','2008-05-14 17:16:21'),(76,'SÃ¶dra Dahls PK','2008-05-14 17:16:21'),(77,'SÃ¶dra Kinds FBU','2008-05-14 17:16:21'),(78,'SÃ¶rbygdens PK','2008-05-14 17:16:21'),(79,'Telegrafverket PK','2008-05-14 17:16:21'),(80,'Tidaholms PSK','2008-05-14 17:16:21'),(81,'TrollhÃ¤ttans PK','2008-05-14 17:16:21'),(82,'Tullens PK','2008-05-14 17:16:21'),(83,'Uddevalla PSF','2008-05-14 17:16:21'),(84,'Ulricehamns PK','2008-05-14 17:16:21'),(85,'Unnaryds PSK','2008-05-14 17:16:21'),(86,'Varbergs PK','2008-05-14 17:16:21'),(87,'VargÃ¶ns PK','2008-05-14 17:16:21'),(88,'Vedums PSK','2008-05-14 17:16:21'),(89,'Vilhelmina PK','2008-05-14 17:16:21'),(90,'Volvo PK','2008-05-14 17:16:21'),(91,'VÃ¤stbo PK','2008-05-14 17:16:21'),(92,'VÃ¤xjÃ¶ PK','2008-05-14 17:16:21'),(93,'VÃ¥rgÃ¥rda PSK','2008-05-14 17:16:21'),(94,'Ã„ngelholms PK','2008-05-14 17:16:21'),(95,'Ã…mÃ¥ls PK','2008-05-14 17:16:21'),(96,'Ã–ckerÃ¶ SF','2008-05-14 17:16:21'),(97,'Ã–jaby SF','2008-05-14 17:16:21'),(98,'Ã–rebro PSSK','2008-05-14 17:16:21'),(99,'Ã–rgryte PK','2008-05-14 17:16:21'),(100,'Ã–stersunds PK','2008-05-14 17:16:21'),(101,'Ã–verby PK','2008-05-14 17:16:21');
-- CREATE DEFINER=`www`@`%` TRIGGER `tr_club_ins` BEFORE INSERT ON `tbl_Pistol_Club` FOR EACH ROW set NEW.CreateDate = now() ;



-- Table structure for table `tbl_Pistol_Competition`
DROP TABLE IF EXISTS `tbl_Pistol_Competition`;
CREATE TABLE `tbl_Pistol_Competition` (
  `Id` bigint(20) NOT NULL auto_increment,
  `Name` varchar(100) default NULL,
  `StartDate` date default NULL,
  `EndDate` date default NULL,
  `Location` varchar(100) default NULL,
  `HostClubId` bigint(20) default NULL,
  `MaxPatrolSize` int(11) NOT NULL,
  `Status` int(11) NOT NULL,
  `ScoreType` char(1) default NULL,
  `OnlineBetalning` CHAR(1) NULL DEFAULT NULL,
  `Masterskap` CHAR(1) NULL DEFAULT NULL,
  PRIMARY KEY  (`Id`),
  UNIQUE KEY `i2` (`Name`,`StartDate`),
  KEY `i1` (`StartDate`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;

-- ALTER TABLE `gunnar`.`tbl_Pistol_Competition` ADD COLUMN `Masterskap` CHAR(1) NULL DEFAULT NULL  AFTER `ScoreType` ;
-- ALTER TABLE `gokhan`.`tbl_Pistol_Competition` ADD COLUMN `OnlineBetalning` CHAR(1) NULL DEFAULT NULL  AFTER `ScoreType` ;

-- Table structure for table `tbl_Pistol_CompetitionDay`
DROP TABLE IF EXISTS `tbl_Pistol_CompetitionDay`;
CREATE TABLE `tbl_Pistol_CompetitionDay` (
  `Id` bigint(20) NOT NULL auto_increment,
  `CompetitionId` bigint(20) default NULL,
  `DayNo` smallint(6) NOT NULL,
  `FirstStart` varchar(5) default NULL,
  `LastStart` varchar(5) default NULL,
  `MaxStation` int(11) default NULL,
  `PatrolSpace` int(11) default NULL,
  PRIMARY KEY  (`Id`),
  UNIQUE KEY `i1` (`CompetitionId`,`DayNo`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=latin1;

-- Table structure for table `tbl_Pistol_Entry`
DROP TABLE IF EXISTS `tbl_Pistol_Entry`;
CREATE TABLE `tbl_Pistol_Entry` (
  `Id` bigint(20) NOT NULL auto_increment,
  `ShotId` bigint(20) default NULL,
  `GunClassificationId` bigint(20) default NULL,
  `ShotClassId` bigint(20) default NULL,
  `RegisterDate` datetime NOT NULL,
  `Status` char(1) default NULL,
  `PatrolId` bigint(20) default NULL,
  `PayDate` datetime default NULL,
  `TeamId` bigint(20) default NULL,
  PRIMARY KEY  (`Id`),
  UNIQUE KEY `i1` (`PatrolId`,`ShotId`),
  UNIQUE KEY `i2` (`ShotId`,`PatrolId`)
) ENGINE=MyISAM AUTO_INCREMENT=376 DEFAULT CHARSET=latin1;

ALTER TABLE `tbl_Pistol_Entry` ADD COLUMN `StaPlats` int DEFAULT 0 AFTER `TeamId` ;
ALTER TABLE `tbl_Pistol_Entry` ADD COLUMN `BokadAvShotId` int DEFAULT 0 AFTER `StaPlats`;

-- CREATE DEFINER=`root`@`localhost` TRIGGER `tr_entry_ins` BEFORE INSERT ON `tbl_Pistol_Entry` FOR EACH ROW set NEW.RegisterDate = now() ;

-- Table structure for table `tbl_Pistol_EntryPatrol`
DROP TABLE IF EXISTS `tbl_Pistol_EntryPatrol`;
CREATE TABLE `tbl_Pistol_EntryPatrol` (
  `Id` bigint(20) NOT NULL auto_increment,
  `EntryId` bigint(20) default NULL,
  `PatrolId` bigint(20) default NULL,
  PRIMARY KEY  (`Id`),
  UNIQUE KEY `i1` (`EntryId`,`PatrolId`),
  UNIQUE KEY `i2` (`PatrolId`,`EntryId`)
) ENGINE=MyISAM AUTO_INCREMENT=279 DEFAULT CHARSET=latin1;

-- Table structure for table `tbl_Pistol_FailedLogons`
DROP TABLE IF EXISTS `tbl_Pistol_FailedLogons`;
CREATE TABLE `tbl_Pistol_FailedLogons` (
  `Id` bigint(20) NOT NULL auto_increment,
  `GunCard` varchar(10) NOT NULL,
  `LogonDate` timestamp NOT NULL default CURRENT_TIMESTAMP ,
  PRIMARY KEY  (`Id`),
  KEY `i1` (`GunCard`,`LogonDate`),
  KEY `i2` (`LogonDate`)
) ENGINE=MyISAM AUTO_INCREMENT=162 DEFAULT CHARSET=latin1;

-- CREATE DEFINER=`www`@`%` TRIGGER `tr_failedlogons_ins` BEFORE INSERT ON `tbl_Pistol_FailedLogons` FOR EACH ROW set NEW.LogonDate = now();

-- Table structure for table `tbl_Pistol_GunClassification`
DROP TABLE IF EXISTS `tbl_Pistol_GunClassification`;
CREATE TABLE `tbl_Pistol_GunClassification` (
  `Id` bigint(20) NOT NULL auto_increment,
  `Grade` varchar(5) NOT NULL,
  `Description` varchar(100) default NULL,
  PRIMARY KEY  (`Id`),
  UNIQUE KEY `i1` (`Grade`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

INSERT INTO `tbl_Pistol_GunClassification` VALUES (1,'A','Grovkalibrig pistol'),(2,'B','Grovkalibrig pistol special'),(3,'C','Finkalibrig pistol (22)'),(4,'R','Revolver'),(5,'S','Snubb');


-- Table structure for table `tbl_Pistol_Logons`
DROP TABLE IF EXISTS `tbl_Pistol_Logons`;
CREATE TABLE `tbl_Pistol_Logons` (
  `Id` bigint(20) NOT NULL auto_increment,
  `ShotId` bigint(20) NOT NULL,
  `LogonDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`Id`),
  KEY `i1` (`ShotId`,`LogonDate`),
  KEY `i2` (`LogonDate`)
) ENGINE=MyISAM AUTO_INCREMENT=395 DEFAULT CHARSET=latin1;

-- CREATE DEFINER=`www`@`%` TRIGGER `tr_logons_ins` BEFORE INSERT ON `tbl_Pistol_Logons` FOR EACH ROW set NEW.LogonDate = now() ;

-- Table structure for table `tbl_Pistol_MedalGroup`
DROP TABLE IF EXISTS `tbl_Pistol_MedalGroup`;
CREATE TABLE `tbl_Pistol_MedalGroup` (
  `Id` bigint(20) NOT NULL auto_increment,
  `Name` varchar(100) NOT NULL,
  PRIMARY KEY  (`Id`),
  UNIQUE KEY `i1` (`Name`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

INSERT INTO `tbl_Pistol_MedalGroup` VALUES (1,'C'),(2,'A'),(3,'B'),(4,'R');

-- Table structure for table `tbl_Pistol_MedalGroupMember`
DROP TABLE IF EXISTS `tbl_Pistol_MedalGroupMember`;
CREATE TABLE `tbl_Pistol_MedalGroupMember` (
  `Id` bigint(20) NOT NULL auto_increment,
  `MedalGroupId` bigint(20) default NULL,
  `ShotClassId` bigint(20) default NULL,
  PRIMARY KEY  (`Id`),
  UNIQUE KEY `i1` (`ShotClassId`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=latin1;

INSERT INTO `tbl_Pistol_MedalGroupMember` VALUES (1,1,1),(2,1,2),(3,1,3),(4,1,4),(5,1,5),(6,1,6),(7,1,7),(8,1,8),(9,1,9),(10,2,10),(11,2,11),(12,2,12),(13,3,13),(14,3,14),(15,3,15),(16,4,16),(17,4,17),(18,4,18);

-- Table structure for table `tbl_Pistol_Patrol`
DROP TABLE IF EXISTS `tbl_Pistol_Patrol`;
CREATE TABLE `tbl_Pistol_Patrol` (
  `Id` bigint(20) NOT NULL auto_increment,
  `CompetitionDayId` bigint(20) default NULL,
  `SortOrder` smallint(6) NOT NULL,
  `Description` varchar(100) default NULL,
  `StartTime` datetime default NULL,
  `Hidden` TINYINT(1)  NULL DEFAULT false,
  PRIMARY KEY  (`Id`),
  UNIQUE KEY `i1` (`CompetitionDayId`,`SortOrder`)
) ENGINE=MyISAM AUTO_INCREMENT=367 DEFAULT CHARSET=latin1;


-- Table structure for table `tbl_Pistol_PatrolGun`
DROP TABLE IF EXISTS `tbl_Pistol_PatrolGun`;
CREATE TABLE `tbl_Pistol_PatrolGun` (
  `Id` bigint(20) NOT NULL auto_increment,
  `PatrolId` bigint(20) default NULL,
  `GunClassId` bigint(20) default NULL,
  PRIMARY KEY  (`Id`),
  UNIQUE KEY `i1` (`PatrolId`,`GunClassId`)
) ENGINE=MyISAM AUTO_INCREMENT=714 DEFAULT CHARSET=latin1;

-- Table structure for table `tbl_Pistol_Schedule`
DROP TABLE IF EXISTS `tbl_Pistol_Schedule`;
CREATE TABLE `tbl_Pistol_Schedule` (
  `Id` bigint(20) NOT NULL auto_increment,
  `CompetitionDayId` bigint(20) default NULL,
  `StartTime` datetime NOT NULL,
  `PatrolId` bigint(20) default NULL,
  `Station` smallint(6) default NULL,
  PRIMARY KEY  (`Id`),
  UNIQUE KEY `i1` (`CompetitionDayId`,`PatrolId`,`Station`),
  KEY `i2` (`PatrolId`,`StartTime`)
) ENGINE=MyISAM AUTO_INCREMENT=2893 DEFAULT CHARSET=latin1;


-- Table structure for table `tbl_Pistol_Score`
DROP TABLE IF EXISTS `tbl_Pistol_Score`;
CREATE TABLE `tbl_Pistol_Score` (
  `Id` bigint(20) NOT NULL auto_increment,
  `CompetitionDayId` bigint(20) default NULL,
  `StationId` smallint(6) NOT NULL,
  `Hits` int(11) NOT NULL,
  `Targets` int(11) NOT NULL,
  `Points` int(11) NOT NULL,
  `RegisterDate` datetime NOT NULL,
  `EntryId` bigint(20) default NULL,
  `CompetitionId` bigint(20) default NULL,
  PRIMARY KEY  (`Id`),
  UNIQUE KEY `i1` (`EntryId`,`CompetitionDayId`,`StationId`)
) ENGINE=MyISAM AUTO_INCREMENT=1710 DEFAULT CHARSET=latin1;

-- ALTER TABLE `gunnar`.`tbl_Pistol_Score` ADD COLUMN `CompetitionId` BIGINT(20) NULL DEFAULT NULL  AFTER `EntryId` ;

-- CREATE DEFINER=`root`@`localhost` TRIGGER `tr_score_ins` BEFORE INSERT ON `tbl_Pistol_Score` FOR EACH ROW set NEW.RegisterDate = now() ;
-- CREATE DEFINER=`root`@`localhost` TRIGGER `tr_score_upd` BEFORE UPDATE ON `tbl_Pistol_Score` FOR EACH ROW set NEW.RegisterDate = now() ;

-- Table structure for table `tbl_Pistol_Shot`
DROP TABLE IF EXISTS `tbl_Pistol_Shot`;
CREATE TABLE `tbl_Pistol_Shot` (
  `Id` bigint(20) NOT NULL auto_increment,
  `FirstName` varchar(20) default NULL,
  `LastName` varchar(40) default NULL,
  `ClubId` bigint(20) default NULL,
  `GunCard` varchar(10) default NULL,
  `Email` varchar(60) default NULL,
  `Password` varchar(100) default NULL,
  `UserType` varchar(10) default NULL,
  `CreateDate` timestamp NOT NULL default current_timestamp,
  `Status` varchar(10) default NULL,
  PRIMARY KEY  (`Id`),
  UNIQUE KEY `i3` (`GunCard`),
  KEY `i1` (`LastName`,`FirstName`),
  KEY `i2` (`ClubId`,`LastName`)
) ENGINE=MyISAM AUTO_INCREMENT=99 DEFAULT CHARSET=latin1;

INSERT INTO `tbl_Pistol_Shot` VALUES (1,'Ulf','ThÃ¶rnblad',95,'1','ulf@sqwarkbox.com','ebfc7910077770c8340f63cd2dca2ac1f120444f','ADMIN','2008-05-14 18:03:50','ACTIVE'),(2,'Pierre','Lindqvist',22,'24924','pierre@movieline.se','b7c125e801d26b62a5ceca00179f82788a7ac7c4','ADMIN','2008-05-14 18:07:08','ACTIVE'),(3,'Lars','Lundmark',22,'1657','lars@lundmark.cc','d2c88b2aa103acf3d69891df0b6fb8a7d95d86ee','ADMIN','2008-05-15 17:50:30','ACTIVE'),(4,'Smith','Wesson',22,'666666','pierre@gpssk.com','fa376e383626491fb6f3b6b5c06b1c208bba702b','USER','2008-05-15 19:17:01','ACTIVE'),(5,'Svenne','Duva',1,'100','ulf@sqwarkbox.com','84f82634d5cf289f2b248ac3d9a296291e41cbf3','USER','2008-06-10 01:02:39','ACTIVE'),(6,'HelÃ©ne','Loberg',60,'18623','helene.loberg@vgregion.se','925a290882138da39ff6372761b695e80bd28364','USER','2008-06-26 09:36:17','ACTIVE'),(7,'Thomas','BÃ¤ckstrÃ¶m',58,'1212','a@a.com','3d4f2bf07dc1be38b20cd6e46949a1071f9d0e3d','USER','2008-07-01 12:54:07','ACTIVE'),(8,'Annika ','Gabrielsson',58,'1122','aa@a.com','3d4f2bf07dc1be38b20cd6e46949a1071f9d0e3d','USER','2008-07-01 12:55:29','ACTIVE'),(9,'Britt','Karlsson',99,'12','b@b.com','3d4f2bf07dc1be38b20cd6e46949a1071f9d0e3d','USER','2008-07-01 13:00:51','ACTIVE'),(10,'Navid','Bagheri',22,'0101','l@l.com','3d4f2bf07dc1be38b20cd6e46949a1071f9d0e3d','USER','2008-07-01 13:02:21','ACTIVE'),(11,'Camilla','Knutsson',58,'20921','camilla.knutsson1@comhem.se','897cba9cc449b54ba3c94d26b8905bb3be72167a','USER','2008-09-12 16:17:04','ACTIVE'),(12,'tvÃ¥','tvÃ¥an',1,'2','helene_loberg@hotmail.com','5554e95a04453eae2af34eb2be4d9cba9f94665b','USER','2008-10-07 08:46:36','ACTIVE'),(13,'tre','trean',1,'3','helene_loberg@hotmail.com','a18f3b5412b05ce83a4781917e326f1e7c4cfab0','USER','2008-10-07 08:49:21','ACTIVE'),(14,'fyra','fyran',1,'4','helene_loberg@hotmail.com','a2bbc15b3c901c5d1199bf3c635502f8dca1f70c','USER','2008-10-07 08:51:38','ACTIVE'),(15,'Urban','KÃ¤llestig',22,'5702','urban@gpssk.com','c085563abbcde81cc1e9f36a46ac99a960db26f9','ADMIN','2008-12-29 12:10:48','ACTIVE'),(16,'Anna-Lena','Stenfeldt',22,'28388','anna-lena.stenfeldt@bredband.net','b7c125e801d26b62a5ceca00179f82788a7ac7c4','USER','2009-01-17 19:13:15','ACTIVE'),(63,'Arne','Friberg',22,'020','arne.v.friberg@telia.com','37076a00c0e8dcafd1e0bda94793dc1a8a999bca','USER','2010-04-08 10:10:39','ACTIVE'),(62,'Nader','Nazari',22,'12880','nadersweden@yahoo.se','dc07c6cba562f7c42cb1c564a364883cac195c9c','USER','2010-04-07 09:33:02','ACTIVE'),(61,'GÃ¶khan','Kurt',22,'29544','kurtng@hotmail.com','c859a69ae064a49e815e17dc33335035ca8fdb4a','USER','2010-04-06 14:25:50','ACTIVE'),(60,'Barbro','Wegerstam',22,'1472317','barbro@wegerstam.se','89724b0e5a92bb1388fa52ee2b259851635ed454','USER','2010-04-06 14:07:53','ACTIVE'),(59,'asd','asd',1,'123','a@a.com','85136c79cbf9fe36bb9d05d0639c70c265c18d37','USER','2010-04-06 06:33:37','ACTIVE'),(58,'Ã…ke','LewensjÃ¶',22,'12720','ake.lewensjo@telia.com','7eaa29e9c7daf389818b0191571a7714911784d4','USER','2010-04-05 14:20:50','ACTIVE'),(57,'Tommy','Sellering',22,'M270966TOM','tsellering@hotmail.com','4e8f7377720f4ca2824090330c8610d5f85a6a9f','USER','2010-04-05 09:13:27','ACTIVE'),(56,'erik','gamarra',23,'0000','kn.erik@hotmail.com','b06b81531cc8e9b5f25902187e9d748a3f3fd853','USER','2010-04-01 06:18:01','ACTIVE'),(55,'Alexander','Vaynshteyn',22,'29545','alexander_stein689@hotmail.com','3f5962c46facf3d7f0e7a3d7bacd1dbd2e1129eb','USER','2010-04-01 05:58:59','ACTIVE'),(54,'Mattias','Gustafsson',22,'29548','s00magus@tele2.se','46c8c1efbfa67679e9546ef7e717de0a64fc639b','USER','2010-03-31 10:15:53','ACTIVE'),(53,'Tim','BÃ¥genholm',22,'30116','tim.bagenholm@night.se','76c15e6c9ac7e63202739b4283c504d186baae39','USER','2010-03-31 05:24:03','ACTIVE'),(52,'Pertti','Karjalainen',22,'28390','pk-ha@hotmail.com','2e9bb42c3de7d98f6a72abd2a57c80c99f2d64fe','USER','2010-03-31 04:37:26','ACTIVE'),(51,'abc','abc',22,'abc','a@a.com','f8c1d87006fbf7e5cc4b026c3138bc046883dc71','USER','2010-03-31 03:31:44','ACTIVE'),(50,'Ronny','Larsson',22,'1486221','ronny_larsson@hotmail.com','1c071514bdf51007fda9d16a6c371a04b5437318','USER','2010-03-31 03:25:47','ACTIVE'),(49,'Bedjet','IsÃ©ni',22,'30118','bedjet.iseni@omegapoint.se','ff004cfe54b2ca6d9ad6a0a149c3893a71813b59','USER','2010-03-30 15:54:29','ACTIVE'),(48,'Christoffer','Meyer',22,'1486928','cm@innovest.se','182b6cb552e016337805988bc1ee0f6aefa1f7a0','USER','2010-03-30 15:37:27','ACTIVE'),(47,'Jonas','Eriksson',91,'05031','jonerik@telia.com','b73856d9eef18150915ab6366fe3b155a02b9ac6','ADMIN','2010-03-27 10:33:30','ACTIVE'),(44,'Ulf','Hansson',29,'24287','ulf.hansson@v-tab.se','214c418002e37328fa4269e3a4c952adc6e79ea4','ADMIN','2009-04-14 17:49:45','ACTIVE'),(45,'Andreas','Klementsson',22,'14426','andreas@klementsson.se','bc0fd96aa487df4cb5a4f51eb86acdac00afbefa','ADMIN','2009-04-29 16:08:30','ACTIVE'),(46,'Claes','Linder',58,'3813','claesghl@yahoo.se','907a1177cdf5dbc33b9b493e03ab26be0045629d','ADMIN','2010-03-21 05:39:41','ACTIVE'),(64,'Christian','Holmgren',22,'3263','christianholmgren12@gmail.com','59c0912941dbfa0d5d7bde2f782a8f303c586d1c','USER','2010-04-09 04:41:00','ACTIVE'),(65,'Christer','Beckman',22,'26317','Christer.beckman@3mail.se','ff00fa5d784ed1757f2ec5979ca1da50821cc61d','OPER','2010-04-10 02:02:46','ACTIVE'),(66,'Ralph','HÃ¤ggedahl',22,'123456','ralph.haggedahl@comhem.se','32502e080ad1a739c78e34295bf14d457e3a9608','USER','2010-04-10 02:10:37','ACTIVE'),(67,'Ingemar','Wahlberg',22,'24314','ingemar.wahlberg@bredband.net','d76c0786b339c2b2567bdaf99858d035b8804ba8','USER','2010-04-10 02:26:41','ACTIVE'),(68,'Mia','Karlman',22,'29055','mkarlman@volvocars.com','57ca2dad17817a05249a192cc18ef326772df0d4','USER','2010-04-10 02:28:37','ACTIVE'),(69,'Ronny','Larsson',22,'1234567','lars@skyskol.com','d2c88b2aa103acf3d69891df0b6fb8a7d95d86ee','USER','2010-04-10 02:30:10','ACTIVE'),(70,'Astor','Fritzon',22,'654321','pzh157c@telia.com','160ea291432224d3fbf77e865579f83d9cc5739a','USER','2010-04-10 02:36:42','ACTIVE'),(71,'Tommy','Sellering',22,'086886','tsellering@hotmail.com','8b89cc0a3710e0bed7f548bf447d24881251b117','USER','2010-04-10 02:42:27','ACTIVE'),(72,'Dominique','Gasc',22,'4321','lars@skyskol.com','d2c88b2aa103acf3d69891df0b6fb8a7d95d86ee','USER','2010-04-10 02:46:40','ACTIVE'),(73,'Sanna','Zetterberg',22,'21281','sanna.zetterberg@gmail.com','32502e080ad1a739c78e34295bf14d457e3a9608','USER','2010-04-10 03:05:06','ACTIVE'),(74,'Lars','Persson',22,'987','lars@skyskol.com','d2c88b2aa103acf3d69891df0b6fb8a7d95d86ee','USER','2010-04-10 04:58:59','ACTIVE'),(75,'Carl-Erik','Lissfors',22,'5708','celissfors@bredband2.com','8a2275f2aaba24900a62d746a355bf36aa413570','USER','2010-06-16 17:05:58','ACTIVE'),(76,'Peter','Larsson',90,'19699','pelar@home.se','84c43305b6a78500e7ccb4165936378d4b831a76','USER','2010-06-20 15:03:03','ACTIVE'),(77,'Navid','Bagheri',22,'5712','nbagheri@naviton.se','3e5e06ddd784102da0afff1a585625653269c265','USER','2010-06-20 17:51:29','ACTIVE'),(78,'Tony','Forslund',90,'17563','tfracing@spray.se','deaf9369612366b797c1825fcd5769bc9a6bcf85','USER','2010-06-20 20:33:07','ACTIVE'),(79,'Mikael','Jonasson',79,'16687','mikael.jonasson@edb.com','3b0a5a5c7771f2d87fb321f74877dfe4ffa92908','USER','2010-06-20 20:58:07','ACTIVE'),(80,'Bengt','Dahl',90,'7726','bengt.dahl@comhem.se','5c8a7a129de8b649e9a0cbfbb7e9cec37a6efcb6','USER','2010-06-20 21:50:37','ACTIVE'),(81,'daniel','johansson',90,'28550','daniel@johansson.com','23af1bb7463f46d948bc17f3900a5cbe512922a1','USER','2010-06-20 21:58:14','ACTIVE'),(82,'Urban','WaldenstrÃ¶m',58,'602','urban.waldenstrom@bredband.net','633a74f5d793800c6e2802a1bf2f0da8529143a3','USER','2010-06-20 23:19:09','ACTIVE'),(83,'Johan','Lindquist',58,'23174','spam@smilfinken.net','5f75fbcccdb0071a07ea6c8008c858fe0e3e64cc','USER','2010-06-21 01:04:41','ACTIVE'),(84,'Sune','Johnson',22,'05140','sune.johnson@hotmail.com','ee5251e111bc059272d06c7722a6ba2198155ebd','USER','2010-06-21 15:22:07','ACTIVE'),(85,'Thomas','Dannberg',22,'8670','t.dannberg@gmail.com','ee5251e111bc059272d06c7722a6ba2198155ebd','USER','2010-06-21 20:57:14','ACTIVE'),(86,'Oskar','Ekblad',30,'26707','familjen.ekblad@telia.com','083c270078073f7366519ef56092e9480ba373b1','USER','2010-06-21 21:08:38','ACTIVE'),(87,'Anton ','Ekblad',30,'26905','familjen.ekblad@telia.com','f644e9c09f48319e45e5e78ad9d148f4f9a4e2bf','USER','2010-06-21 21:15:21','ACTIVE'),(88,'Rolf','Ekblad',30,'27297','familjen.ekblad@telia.com','3c17b33673640dca980458a2d13b3833ea1dcb99','USER','2010-06-21 21:17:37','ACTIVE'),(89,'David','Svensson',30,'26706','christer.svensson@reab.se','c6d85de9b8c079438674a1d7ec8ed504d6efa7da','USER','2010-06-21 22:31:40','ACTIVE'),(90,'Christer','Svensson',30,'26583','christer.svensson@reab.se','c6d85de9b8c079438674a1d7ec8ed504d6efa7da','USER','2010-06-21 22:38:15','ACTIVE'),(91,'Ulf','Sandahl',58,'3812','ulf.sandahl2@comhem.se','7c4a8d09ca3762af61e59520943dc26494f8941b','USER','2010-06-22 16:18:37','ACTIVE'),(92,'Slavoljub','Mrdenovic',22,'12712','a@a.com','7c4a8d09ca3762af61e59520943dc26494f8941b','USER','2010-06-22 16:30:42','ACTIVE'),(93,'Patrik','Fritzson',22,'583','patrik.fritzson@spray.se','7c4a8d09ca3762af61e59520943dc26494f8941b','USER','2010-06-22 17:24:58','ACTIVE'),(94,'Lars','Regner',22,'22461','l.r.regner@telia.com','9df0d271aaa770f00fef80ebb07e1f552c193d74','USER','2010-06-22 18:00:56','ACTIVE'),(95,'Ronny','Meijer',22,'16983','ronny.meijer@emerson.com','cd51452db9fa7bf1d9abfceb3f25a2af3d1898be','USER','2010-06-22 18:04:23','ACTIVE'),(96,'Dominique','Gasc',22,'13467','a@a.se','7c4a8d09ca3762af61e59520943dc26494f8941b','USER','2010-06-22 18:30:26','ACTIVE'),(97,'Blagoja','Bogoevski',22,'16976','bblagoja@netatonce.net','31c73562bdb0ecf52a9b15546dd31b9c69a87d1b','USER','2010-06-22 18:38:25','ACTIVE'),(98,'Camilla ','Ersdal',22,'1000000','l@l.com','1f8ac10f23c5b5bc1167bda84b833e5c057a77d2','USER','2010-06-22 19:43:01','ACTIVE');
-- CREATE DEFINER=`www`@`%` TRIGGER `tr_shot_ins` BEFORE INSERT ON `tbl_Pistol_Shot` FOR EACH ROW set NEW.CreateDate = now() ;

-- Table structure for table `tbl_Pistol_ShotClass`
DROP TABLE IF EXISTS `tbl_Pistol_ShotClass`;
CREATE TABLE `tbl_Pistol_ShotClass` (
  `Id` bigint(20) NOT NULL auto_increment,
  `Name` varchar(20) default NULL,
  `Description` varchar(20) default NULL,
  `GunClassificationId` bigint(20) default NULL,
  `Masterskap` CHAR(1) NULL DEFAULT NULL,
  PRIMARY KEY  (`Id`),
  UNIQUE KEY `i1` (`Name`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;

-- ALTER TABLE `gunnar`.`tbl_Pistol_ShotClass` ADD COLUMN `Masterskap` CHAR(1) NULL DEFAULT NULL  AFTER `GunClassificationId` ;

INSERT INTO `tbl_Pistol_ShotClass` VALUES (1,'C1',NULL,3, NULL),(2,'C2',NULL,3,NULL),(3,'C3',NULL,3,NULL),(4,'D1',NULL,3,NULL),(5,'D2',NULL,3,NULL),(6,'D3',NULL,3,NULL),(7,'VY',NULL,3,NULL),(8,'VÃ„',NULL,3,NULL),(9,'JUN',NULL,3,NULL),(10,'A1',NULL,1,NULL),(11,'A2',NULL,1,NULL),(12,'A3',NULL,1,NULL),(13,'B1',NULL,2,NULL),(14,'B2',NULL,2,NULL),(15,'B3',NULL,2,NULL),(16,'R1',NULL,4,NULL),(17,'R2',NULL,4,NULL),(18,'R3',NULL,4,NULL),(19,'S',NULL,5,NULL);
UPDATE `tbl_Pistol_ShotClass` SET `Masterskap`='Y' WHERE `Id`='3';
UPDATE `tbl_Pistol_ShotClass` SET `Masterskap`='Y' WHERE `Id`='6';
UPDATE `tbl_Pistol_ShotClass` SET `Masterskap`='Y' WHERE `Id`='12';
UPDATE `tbl_Pistol_ShotClass` SET `Masterskap`='Y' WHERE `Id`='15';
UPDATE `tbl_Pistol_ShotClass` SET `Masterskap`='Y' WHERE `Id`='18';

-- Table structure for table `tbl_Pistol_Station`
DROP TABLE IF EXISTS `tbl_Pistol_Station`;
CREATE TABLE `tbl_Pistol_Station` (
  `Id` bigint(20) NOT NULL auto_increment,
  `CompetitionDayId` bigint(20) default NULL,
  `SortOrder` smallint(6) NOT NULL,
  `PatrolSpace` smallint(6) default NULL,
  PRIMARY KEY  (`Id`),
  UNIQUE KEY `i1` (`CompetitionDayId`,`SortOrder`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;


-- Table structure for table `tbl_Pistol_Team`
DROP TABLE IF EXISTS `tbl_Pistol_Team`;
CREATE TABLE `tbl_Pistol_Team` (
  `Id` bigint(20) NOT NULL auto_increment,
  `Name` varchar(50) NOT NULL,
  `CompetitionDayId` bigint(20) default NULL,
  `GunClassId` bigint(20) default NULL,
  `ClubId` bigint(20) default NULL,
  PRIMARY KEY  (`Id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;



-- Table structure for table `tbl_Pistol_TeamMember`
DROP TABLE IF EXISTS `tbl_Pistol_TeamMember`;
CREATE TABLE `tbl_Pistol_TeamMember` (
  `Id` bigint(20) NOT NULL auto_increment,
  `TeamId` bigint(20) default NULL,
  `EntryId` bigint(20) default NULL,
  PRIMARY KEY  (`Id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `tbl_Pistol_Payment`;
CREATE TABLE `tbl_Pistol_Payment` (
  `Id` bigint(20) NOT NULL auto_increment,
  `EntryId` bigint(20) NOT NULL,
  `TransactionId` varchar(40) NOT NULL  ,
  `StatusCode` int(11) NOT NULL  ,
  PRIMARY KEY  (`Id`)
) ENGINE=MyISAM AUTO_INCREMENT=102 DEFAULT CHARSET=latin1;

--ALTER TABLE `tbl_Pistol_Payment` ADD COLUMN `StatusCode` int(11) NULL DEFAULT 0  AFTER `TransactionId` ;
