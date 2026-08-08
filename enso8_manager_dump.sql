-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: enso8_manager
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `enso8_assets`
--

DROP TABLE IF EXISTS `enso8_assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enso8_assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `type` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `thumbnail_path` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enso8_assets`
--

LOCK TABLES `enso8_assets` WRITE;
/*!40000 ALTER TABLE `enso8_assets` DISABLE KEYS */;
INSERT INTO `enso8_assets` VALUES (1,1,'Boat','vehicle','hjchj,l;.fyujlcdtjsytkmudsjnzarnzstrgtgssrenzarfjnzratgjnsjtrgjs','uploads/assets/1785363845_ba345afb40ecf9fdd104.png','2026-07-29 22:24:05','2026-07-29 22:24:05');
/*!40000 ALTER TABLE `enso8_assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enso8_clients`
--

DROP TABLE IF EXISTS `enso8_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enso8_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_name` varchar(255) NOT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enso8_clients`
--

LOCK TABLES `enso8_clients` WRITE;
/*!40000 ALTER TABLE `enso8_clients` DISABLE KEYS */;
INSERT INTO `enso8_clients` VALUES (1,'JBL','Aadi','aadi@jbl.in','12345678','2026-07-29 22:02:36','2026-07-29 22:02:36');
/*!40000 ALTER TABLE `enso8_clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enso8_collaborators`
--

DROP TABLE IF EXISTS `enso8_collaborators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enso8_collaborators` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_name` varchar(255) NOT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enso8_collaborators`
--

LOCK TABLES `enso8_collaborators` WRITE;
/*!40000 ALTER TABLE `enso8_collaborators` DISABLE KEYS */;
/*!40000 ALTER TABLE `enso8_collaborators` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enso8_holidays`
--

DROP TABLE IF EXISTS `enso8_holidays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enso8_holidays` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `holiday_date` date NOT NULL,
  `holiday_type` varchar(20) DEFAULT 'public',
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enso8_holidays`
--

LOCK TABLES `enso8_holidays` WRITE;
/*!40000 ALTER TABLE `enso8_holidays` DISABLE KEYS */;
/*!40000 ALTER TABLE `enso8_holidays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enso8_jobs`
--

DROP TABLE IF EXISTS `enso8_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enso8_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stage_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `priority` varchar(50) NOT NULL DEFAULT 'normal',
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enso8_jobs`
--

LOCK TABLES `enso8_jobs` WRITE;
/*!40000 ALTER TABLE `enso8_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `enso8_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enso8_migrations`
--

DROP TABLE IF EXISTS `enso8_migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enso8_migrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enso8_migrations`
--

LOCK TABLES `enso8_migrations` WRITE;
/*!40000 ALTER TABLE `enso8_migrations` DISABLE KEYS */;
INSERT INTO `enso8_migrations` VALUES (1,'2026-07-29-211525','App\\Database\\Migrations\\CreateClientsTable','default','App',1785792751,1),(2,'2026-07-29-211525','App\\Database\\Migrations\\CreateProjectsTable','default','App',1785792751,1),(3,'2026-07-29-211525','App\\Database\\Migrations\\CreateUsersTable','default','App',1785792751,1),(4,'2026-07-29-211526','App\\Database\\Migrations\\CreateJobsTable','default','App',1785792751,1),(5,'2026-07-29-211526','App\\Database\\Migrations\\CreateStagesTable','default','App',1785792751,1),(6,'2026-07-29-215408','App\\Database\\Migrations\\CreateProjectTypesTable','default','App',1785792751,1),(7,'2026-07-29-215409','App\\Database\\Migrations\\CreateCollaboratorsTable','default','App',1785792751,1),(8,'2026-07-29-221727','App\\Database\\Migrations\\CreateSequencesTable','default','App',1785792751,1),(9,'2026-07-29-221727','App\\Database\\Migrations\\CreateShotsTable','default','App',1785792751,1),(10,'2026-07-29-221728','App\\Database\\Migrations\\CreateAssetsTable','default','App',1785792751,1),(11,'2026-07-29-221728','App\\Database\\Migrations\\CreateTaskTypesTable','default','App',1785792751,1),(12,'2026-07-29-221728','App\\Database\\Migrations\\CreateTasksTable','default','App',1785792751,1),(13,'2026-07-29-230500','App\\Database\\Migrations\\CreateSettingsTable','default','App',1785792751,1),(14,'2026-08-02-002741','App\\Database\\Migrations\\CreateTaskBenchmarksTable','default','App',1785792751,1),(15,'2026-08-02-002744','App\\Database\\Migrations\\AddComplexityToTasks','default','App',1785792751,1),(16,'2026-08-02-002748','App\\Database\\Migrations\\AddExperienceLevelToUsers','default','App',1785792751,1),(17,'2026-08-02-091400','App\\Database\\Migrations\\CreateReviewsTable','default','App',1785792791,2),(18,'2026-08-02-091401','App\\Database\\Migrations\\CreateReviewFilesTable','default','App',1785792791,2),(19,'2026-08-02-091402','App\\Database\\Migrations\\CreateReviewCommentsTable','default','App',1785792791,2),(20,'2026-08-02-120000','App\\Database\\Migrations\\UpdateReviewCommentsForResolutions','default','App',1785792791,2),(21,'2026-08-02-202500','App\\Database\\Migrations\\AddSchedulerFieldsToTasks','default','App',1785792791,2),(22,'2026-08-04-000000','App\\Database\\Migrations\\AddMissingMySQLColumns','default','App',1785804580,3),(23,'2026-08-04-000001','App\\Database\\Migrations\\AddMissingMySQLTables','default','App',1785808587,4);
/*!40000 ALTER TABLE `enso8_migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enso8_notifications`
--

DROP TABLE IF EXISTS `enso8_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enso8_notifications` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enso8_notifications`
--

LOCK TABLES `enso8_notifications` WRITE;
/*!40000 ALTER TABLE `enso8_notifications` DISABLE KEYS */;
INSERT INTO `enso8_notifications` VALUES (1,2,'task_assigned','Task Assigned (Retroactive)','You have been assigned to FX for Shot SH001 in JBL Sound Festive.','/user/dashboard',0,'2026-08-03 11:28:57','2026-08-03 11:28:57'),(2,2,'task_assigned','Task Assigned (Retroactive)','You have been assigned to Compositing for Shot SH002 in JBL Sound Festive.','/user/dashboard',0,'2026-08-03 11:28:57','2026-08-03 11:28:57'),(3,2,'task_assigned','Task Assigned (Retroactive)','You have been assigned to Compositing for Shot SH003 in JBL Sound Festive.','/user/dashboard',1,'2026-08-03 11:28:57','2026-08-03 11:28:57'),(4,2,'task_assigned','Task Assigned (Retroactive)','You have been assigned to FX for Shot SH001 in JBL Sound Festive.','/user/dashboard',1,'2026-08-03 11:30:19','2026-08-03 11:30:19'),(5,2,'task_assigned','Task Assigned (Retroactive)','You have been assigned to Compositing for Shot SH002 in JBL Sound Festive.','/user/dashboard',1,'2026-08-03 11:30:19','2026-08-03 11:30:19'),(6,2,'task_assigned','Task Assigned (Retroactive)','You have been assigned to Compositing for Shot SH003 in JBL Sound Festive.','/user/dashboard',1,'2026-08-03 11:30:19','2026-08-03 11:30:19'),(7,1,'review_submitted','Review Submitted (Retroactive)','Adith C Satheesh submitted V01 for Shot SH002 (Compositing).','/admin/reviews/player/3',1,'2026-08-03 11:30:19','2026-08-03 11:30:19'),(8,1,'review_submitted','Review Submitted (Retroactive)','Adith C Satheesh submitted V03 for Shot SH003 (Compositing).','/admin/reviews/player/4',1,'2026-08-03 11:30:19','2026-08-03 11:30:19'),(9,2,'task_assigned','New Task Assigned','You have been assigned to Compositing for Shot SH001 in JBL Sound Festive.','/user/dashboard',1,'2026-08-03 11:53:45','2026-08-03 11:53:45'),(10,1,'review_submitted','Review Submitted','Adith C Satheesh submitted V01 for SH001 (Compositing).','/admin/reviews/player/5',1,'2026-08-03 12:02:03','2026-08-03 12:02:03'),(11,2,'review_status','Review Revision Needed','Your review (V01) for Shot SH001 (Compositing) was marked as Revision Needed.','/user/dashboard',0,'2026-08-03 12:07:25','2026-08-03 12:07:25'),(12,2,'task_assigned','New Task Assigned','You have been assigned to Motion graphics for Shot SH002 in JBL Sound Festive.','/user/dashboard',0,'2026-08-03 13:15:59','2026-08-03 13:15:59'),(13,3,'task_assigned','New Task Assigned','You have been assigned to Animation for Shot SH002 in JBL Sound Festive.','/user/dashboard',0,'2026-08-03 13:16:32','2026-08-03 13:16:32'),(14,2,'task_assigned','New Task Assigned','You have been assigned to Lighting for Shot SH002 in JBL Sound Festive.','/user/dashboard',0,'2026-08-03 13:18:39','2026-08-03 13:18:39'),(15,2,'task_assigned','New Task Assigned','*Project:* JBL Sound Festive\n*Target:* SC01 / SH003\n*Task:* Lighting\n*Complexity:* Medium\n*Est. Time:* 6.25 hrs\n*Due Date:* Not set','/user/dashboard',0,'2026-08-03 13:49:05','2026-08-03 13:49:05');
/*!40000 ALTER TABLE `enso8_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enso8_project_phases`
--

DROP TABLE IF EXISTS `enso8_project_phases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enso8_project_phases` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `color` varchar(20) DEFAULT '#8b5cf6',
  `sort_order` int(11) DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enso8_project_phases`
--

LOCK TABLES `enso8_project_phases` WRITE;
/*!40000 ALTER TABLE `enso8_project_phases` DISABLE KEYS */;
INSERT INTO `enso8_project_phases` VALUES (1,1,'Planning','#64748b',1,NULL,NULL,'2026-08-02 23:16:50'),(2,1,'Pre-Production','#f59e0b',2,NULL,NULL,'2026-08-02 23:16:50'),(3,1,'Production','#3b82f6',3,NULL,NULL,'2026-08-02 23:16:50'),(4,1,'Post','#8b5cf6',4,NULL,NULL,'2026-08-02 23:16:50'),(5,1,'Delivery','#22c55e',5,NULL,NULL,'2026-08-02 23:16:50');
/*!40000 ALTER TABLE `enso8_project_phases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enso8_project_types`
--

DROP TABLE IF EXISTS `enso8_project_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enso8_project_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enso8_project_types`
--

LOCK TABLES `enso8_project_types` WRITE;
/*!40000 ALTER TABLE `enso8_project_types` DISABLE KEYS */;
INSERT INTO `enso8_project_types` VALUES (1,'Commercial','2026-07-29 21:54:56','2026-07-29 21:54:56'),(2,'Episodic','2026-07-29 21:54:56','2026-07-29 21:54:56'),(3,'Youtube Explainers','2026-07-29 21:54:56','2026-07-29 21:54:56'),(4,'Feature film','2026-07-29 21:54:56','2026-07-29 21:54:56'),(5,'Short film','2026-07-29 21:54:56','2026-07-29 21:54:56');
/*!40000 ALTER TABLE `enso8_project_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enso8_projects`
--

DROP TABLE IF EXISTS `enso8_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enso8_projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `project_code` varchar(50) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `collaborator_id` int(11) DEFAULT NULL,
  `project_type_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `start_date` date DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `priority` varchar(50) NOT NULL DEFAULT 'normal',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `fps` int(11) DEFAULT 24,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_code` (`project_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enso8_projects`
--

LOCK TABLES `enso8_projects` WRITE;
/*!40000 ALTER TABLE `enso8_projects` DISABLE KEYS */;
INSERT INTO `enso8_projects` VALUES (1,'JBL Sound Festive','JBL-AD-01',1,NULL,1,'active','2026-07-19','2026-07-31','normal','2026-07-29 22:03:45','2026-07-29 22:03:45',24);
/*!40000 ALTER TABLE `enso8_projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enso8_review_comments`
--

DROP TABLE IF EXISTS `enso8_review_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enso8_review_comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `review_id` int(11) unsigned NOT NULL,
  `user_id` int(11) NOT NULL,
  `timecode` float DEFAULT NULL,
  `comment_text` text DEFAULT NULL,
  `canvas_data` longtext DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `resolution_status` varchar(50) NOT NULL DEFAULT 'pending',
  `resolution_comment` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `enso8_review_comments_review_id_foreign` (`review_id`),
  KEY `enso8_review_comments_user_id_foreign` (`user_id`),
  CONSTRAINT `enso8_review_comments_review_id_foreign` FOREIGN KEY (`review_id`) REFERENCES `enso8_reviews` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `enso8_review_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `enso8_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enso8_review_comments`
--

LOCK TABLES `enso8_review_comments` WRITE;
/*!40000 ALTER TABLE `enso8_review_comments` DISABLE KEYS */;
INSERT INTO `enso8_review_comments` VALUES (6,1,1,1.56394,'jcfjmcmcgmh','{\"version\":\"5.3.0\",\"objects\":[{\"type\":\"path\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":838.01,\"top\":498,\"width\":155,\"height\":155,\"fill\":null,\"stroke\":\"#ef4444\",\"strokeWidth\":4,\"strokeDashArray\":null,\"strokeLineCap\":\"round\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"round\",\"strokeUniform\":false,\"strokeMiterLimit\":10,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"path\":[[\"M\",966.0114733899379,506.9999627314412],[\"Q\",966.0074733899379,506.9999627314412,966.0074733899379,506.4999627644332],[\"Q\",966.0074733899379,505.99996279742516,960.507430839581,505.99996279742516],[\"Q\",955.0073882892243,505.99996279742516,950.0073496070818,507.99996266545725],[\"Q\",945.0073109249392,509.99996253348934,936.0072412970827,512.4999623685294],[\"Q\",927.0071716692262,514.9999622035696,915.0070788320841,519.4999619066418],[\"Q\",903.0069859949419,523.9999616097141,892.0069008942284,529.4999612468023],[\"Q\",881.0068157935148,534.9999608838906,871.0067384292297,540.999960487987],[\"Q\",861.0066610649446,546.9999600920833,856.0066223828021,551.4999597951555],[\"Q\",851.0065837006596,555.9999594982278,847.5065566231598,560.999959168308],[\"Q\",844.00652954566,565.9999588383882,842.5065179410173,573.4999583435086],[\"Q\",841.0065063363745,580.999957848629,840.5065024681603,584.4999576176851],[\"Q\",840.006498599946,587.9999573867414,840.006498599946,592.9999570568216],[\"Q\",840.006498599946,597.9999567269019,840.5065024681603,603.9999563309982],[\"Q\",841.0065063363745,609.9999559350945,846.006545018517,614.9999556051748],[\"Q\",851.0065837006596,619.999955275255,855.5066185145879,623.9999550113192],[\"Q\",860.0066533285161,627.9999547473834,866.0066997470872,631.9999544834477],[\"Q\",872.0067461656582,635.9999542195119,881.0068157935148,639.9999539555761],[\"Q\",890.0068854213714,643.9999536916403,898.0069473127994,647.9999534277044],[\"Q\",906.0070092042275,651.9999531637687,919.0071097777982,653.4999530647929],[\"Q\",932.0072103513687,654.9999529658169,939.5072683745825,654.9999529658169],[\"Q\",947.0073263977963,654.9999529658169,953.5073766845816,653.4999530647929],[\"Q\",960.0074269713668,651.9999531637687,967.5074849945806,644.9999536256564],[\"Q\",975.0075430177944,637.999954087544,978.5075700952942,629.9999546154156],[\"Q\",982.007597172794,621.9999551432871,986.0076281185081,613.9999556711587],[\"Q\",990.0076590642221,605.9999561990303,992.0076745370791,598.4999566939099],[\"Q\",994.0076900099361,590.9999571887895,994.5076938781503,582.499957749653],[\"Q\",995.0076977463647,573.9999583105166,995.0076977463647,563.4999590033481],[\"Q\",995.0076977463647,552.9999596961795,994.5076938781503,543.999960290035],[\"Q\",994.0076900099361,534.9999608838906,992.0076745370791,530.4999611808184],[\"Q\",990.0076590642221,525.9999614777462,987.0076358549366,519.9999618736499],[\"Q\",984.0076126456511,513.9999622695535,982.007597172794,510.49996250049736],[\"Q\",980.007581699937,506.9999627314412,977.5075623588657,505.99996279742516],[\"Q\",975.0075430177944,504.9999628634091,972.5075236767232,503.499962962385],[\"Q\",970.0075043356519,501.9999630613609,967.0074811263664,500.99996312734487],[\"Q\",964.0074579170808,499.9999631933288,962.5074463124381,499.9999631933288],[\"Q\",961.0074347077954,499.9999631933288,959.0074192349384,500.49996316033685],[\"Q\",957.0074037620814,500.99996312734487,954.5073844210101,501.9999630613609],[\"Q\",952.0073650799388,502.99996299537696,947.5073302660105,504.9999628634091],[\"L\",943.0032954520823,507.0039627314412]]},{\"type\":\"i-text\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":1017.01,\"top\":538,\"width\":129.79,\"height\":27.12,\"fill\":\"#ef4444\",\"stroke\":null,\"strokeWidth\":1,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"fontFamily\":\"Inter, sans-serif\",\"fontWeight\":600,\"fontSize\":24,\"text\":\"fvjmdcjmc,\",\"underline\":false,\"overline\":false,\"linethrough\":false,\"textAlign\":\"left\",\"fontStyle\":\"normal\",\"lineHeight\":1.16,\"textBackgroundColor\":\"\",\"charSpacing\":0,\"styles\":[],\"direction\":\"ltr\",\"path\":null,\"pathStartOffset\":0,\"pathSide\":\"left\",\"pathAlign\":\"baseline\"}]}','2026-08-02 06:46:38',NULL,'done','',NULL),(7,1,1,5.55174,'hxghmxhfgzf','{\"version\":\"5.3.0\",\"objects\":[{\"type\":\"rect\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":698.01,\"top\":314,\"width\":469,\"height\":312,\"fill\":\"transparent\",\"stroke\":\"#ef4444\",\"strokeWidth\":3,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"rx\":0,\"ry\":0},{\"type\":\"i-text\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":1212.01,\"top\":481,\"width\":86.34,\"height\":27.12,\"fill\":\"#ef4444\",\"stroke\":null,\"strokeWidth\":1,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"fontFamily\":\"Inter, sans-serif\",\"fontWeight\":600,\"fontSize\":24,\"text\":\"change\",\"underline\":false,\"overline\":false,\"linethrough\":false,\"textAlign\":\"left\",\"fontStyle\":\"normal\",\"lineHeight\":1.16,\"textBackgroundColor\":\"\",\"charSpacing\":0,\"styles\":[],\"direction\":\"ltr\",\"path\":null,\"pathStartOffset\":0,\"pathSide\":\"left\",\"pathAlign\":\"baseline\"},{\"type\":\"image\",\"version\":\"5.3.0\",\"originX\":\"center\",\"originY\":\"center\",\"left\":1301.47,\"top\":438.71,\"width\":1920,\"height\":1080,\"fill\":\"rgb(0,0,0)\",\"stroke\":null,\"strokeWidth\":0,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":0.12,\"scaleY\":0.12,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"cropX\":0,\"cropY\":0,\"src\":\"http://ensoflow.io/media/serve/references/1785653236_f29316c4e2b44c591829.png\",\"crossOrigin\":null,\"filters\":[]}]}','2026-08-02 06:47:26',NULL,'ignored','yfrdydsky7tsks',NULL),(8,1,1,2.9026,'','{\"version\":\"5.3.0\",\"objects\":[{\"type\":\"rect\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":967.01,\"top\":517,\"width\":320,\"height\":169,\"fill\":\"transparent\",\"stroke\":\"#ef4444\",\"strokeWidth\":3,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"rx\":0,\"ry\":0},{\"type\":\"i-text\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":1344.01,\"top\":545,\"width\":181.91,\"height\":27.12,\"fill\":\"#ef4444\",\"stroke\":null,\"strokeWidth\":1,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"fontFamily\":\"Inter, sans-serif\",\"fontWeight\":600,\"fontSize\":24,\"text\":\"jvjahxkchkWAD\",\"underline\":false,\"overline\":false,\"linethrough\":false,\"textAlign\":\"left\",\"fontStyle\":\"normal\",\"lineHeight\":1.16,\"textBackgroundColor\":\"\",\"charSpacing\":0,\"styles\":[],\"direction\":\"ltr\",\"path\":null,\"pathStartOffset\":0,\"pathSide\":\"left\",\"pathAlign\":\"baseline\"}]}','2026-08-02 06:51:20',NULL,'done','',NULL),(9,1,2,2.9026,'why','{\"version\":\"5.3.0\",\"objects\":[{\"type\":\"path\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":1191.01,\"top\":628,\"width\":162.01,\"height\":18.01,\"fill\":null,\"stroke\":\"#ef4444\",\"strokeWidth\":4,\"strokeDashArray\":null,\"strokeLineCap\":\"round\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"round\",\"strokeUniform\":false,\"strokeMiterLimit\":10,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"path\":[[\"M\",1193.005229559209,648.0064932002651],[\"Q\",1193.009229559209,648.0024932002651,1195.009245032066,648.0024932002651],[\"Q\",1197.009260504923,648.0024932002651,1205.5093262645655,646.5024874201379],[\"Q\",1214.0093920242077,645.0024816400106,1227.0094925977783,644.0024777865924],[\"Q\",1240.0095931713488,643.0024739331743,1256.5097208224192,640.5024642996289],[\"Q\",1273.0098484734897,638.0024546660834,1291.509991597417,636.002446959247],[\"Q\",1310.0101347213445,634.0024392524107,1323.5102391631294,633.0024353989925],[\"Q\",1337.0103436049142,632.0024315455744,1346.0104132327706,631.0024276921562],[\"L\",1355.0144828606271,629.998423838738]]},{\"type\":\"path\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":1331.01,\"top\":614,\"width\":31,\"height\":30,\"fill\":null,\"stroke\":\"#ef4444\",\"strokeWidth\":4,\"strokeDashArray\":null,\"strokeLineCap\":\"round\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"round\",\"strokeUniform\":false,\"strokeMiterLimit\":10,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"path\":[[\"M\",1333.0063126592001,618.0023775977199],[\"Q\",1333.0103126592,618.0023775977199,1333.0103126592,617.5023756710109],[\"Q\",1333.0103126592,617.0023737443017,1334.0103203956287,617.0023737443017],[\"Q\",1335.010328132057,617.0023737443017,1336.0103358684855,616.5023718175926],[\"Q\",1337.0103436049142,616.0023698908835,1337.5103474731284,616.0023698908835],[\"Q\",1338.0103513413426,616.0023698908835,1340.0103668141996,616.0023698908835],[\"Q\",1342.0103822870567,616.0023698908835,1343.0103900234851,616.0023698908835],[\"Q\",1344.0103977599135,616.0023698908835,1345.0104054963422,617.0023737443017],[\"Q\",1346.0104132327706,618.0023775977199,1348.5104325738419,619.002381451138],[\"Q\",1351.0104519149131,620.0023853045562,1352.5104635195557,620.5023872312654],[\"Q\",1354.0104751241986,621.0023891579744,1355.5104867288414,621.5023910846835],[\"Q\",1357.0104983334843,622.0023930113927,1359.0105138063414,622.5023949381017],[\"Q\",1361.0105292791982,623.0023968648107,1362.5105408838408,623.5023987915199],[\"Q\",1364.0105524884837,624.002400718229,1364.0105524884837,624.502402644938],[\"Q\",1364.0105524884837,625.0024045716472,1363.5105486202694,625.5024064983562],[\"Q\",1363.0105447520552,626.0024084250653,1362.0105370156266,629.0024199853199],[\"Q\",1361.0105292791982,632.0024315455744,1357.5105022016983,634.5024411791198],[\"Q\",1354.0104751241986,637.0024508126652,1352.5104635195557,639.0024585195015],[\"Q\",1351.0104519149131,641.0024662263379,1349.0104364420563,643.5024758598834],[\"L\",1347.0064209691993,646.0064854934288]]},{\"type\":\"i-text\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":1395.01,\"top\":616,\"width\":48.57,\"height\":27.12,\"fill\":\"#ef4444\",\"stroke\":null,\"strokeWidth\":1,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1.04,\"scaleY\":1.04,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"fontFamily\":\"Inter, sans-serif\",\"fontWeight\":600,\"fontSize\":24,\"text\":\"why\",\"underline\":false,\"overline\":false,\"linethrough\":false,\"textAlign\":\"left\",\"fontStyle\":\"normal\",\"lineHeight\":1.16,\"textBackgroundColor\":\"\",\"charSpacing\":0,\"styles\":[],\"direction\":\"ltr\",\"path\":null,\"pathStartOffset\":0,\"pathSide\":\"left\",\"pathAlign\":\"baseline\"}]}','2026-08-02 13:30:16','2026-08-02 14:15:58','done','',8),(10,1,2,7,'remove lotus','{\"version\":\"5.3.0\",\"objects\":[{\"type\":\"path\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":1280.01,\"top\":482,\"width\":128.74,\"height\":113,\"fill\":null,\"stroke\":\"#ef4444\",\"strokeWidth\":4,\"strokeDashArray\":null,\"strokeLineCap\":\"round\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"round\",\"strokeUniform\":false,\"strokeMiterLimit\":10,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"path\":[[\"M\",1404.014861945624,484.0018612396845],[\"Q\",1404.0108619456241,484.0018612396845,1401.0108387363384,484.0018612396845],[\"Q\",1398.010815527053,484.0018612396845,1393.0107768449104,484.0018612396845],[\"Q\",1388.010738162768,484.0018612396845,1384.010707217054,484.5018631663936],[\"Q\",1380.01067627134,485.0018650931027,1374.010629852769,488.0018766533572],[\"Q\",1368.0105834341978,491.0018882136117,1359.5105176745556,496.0019074807026],[\"Q\",1351.0104519149131,501.0019267477935,1339.5103629459854,508.5019556484298],[\"Q\",1328.0102739770575,516.0019845490662,1317.510192744558,523.5020134497024],[\"Q\",1307.0101115120588,531.0020423503387,1298.5100457524165,538.502071250975],[\"Q\",1290.0099799927743,546.0021001516113,1286.5099529152744,551.5021213454113],[\"Q\",1283.0099258377747,557.0021425392113,1282.5099219695603,559.5021521727567],[\"Q\",1282.009918101346,562.0021618063022,1282.009918101346,562.5021637330112],[\"Q\",1282.009918101346,563.0021656597203,1282.009918101346,564.0021695131385],[\"Q\",1282.009918101346,565.0021733665567,1283.509929705989,567.5021830001021],[\"Q\",1285.0099413106318,570.0021926336475,1289.5099761245601,572.502202267193],[\"Q\",1294.0100109384882,575.0022119007384,1300.5100612252736,579.0022273144111],[\"Q\",1307.0101115120588,583.0022427280838,1316.5101850081296,587.0022581417566],[\"Q\",1326.0102585042005,591.0022735554292,1334.0103203956287,592.5022793355564],[\"Q\",1342.0103822870567,594.0022851156837,1349.5104403102705,595.502290895811],[\"Q\",1357.0104983334843,597.0022966759383,1362.0105370156268,597.0022966759383],[\"Q\",1367.0105756977694,597.0022966759383,1380.510680139554,594.0022851156837],[\"Q\",1394.010784581339,591.0022735554292,1398.5108193952674,588.0022619951746],[\"Q\",1403.0108542091955,585.0022504349201,1406.5108812866952,580.5022330945384],[\"Q\",1410.010908364195,576.0022157541566,1409.0109006277667,574.0022080473202],[\"Q\",1408.010892891338,572.0022003404839,1408.010892891338,567.5021830001021],[\"Q\",1408.010892891338,563.0021656597203,1409.0109006277667,560.0021540994658],[\"Q\",1410.010908364195,557.0021425392113,1410.5109122324093,554.0021309789568],[\"Q\",1411.0109161006235,551.0021194187023,1407.5108890231238,545.5020982249023],[\"Q\",1404.0108619456241,540.0020770311023,1400.01083099991,535.5020596907204],[\"Q\",1396.010800054196,531.0020423503387,1393.5107807131246,527.502028863375],[\"Q\",1391.0107613720534,524.0020153764115,1387.5107342945537,522.5020095962842],[\"Q\",1384.010707217054,521.002003816157,1379.5106724031257,518.5019941826115],[\"Q\",1375.0106375891974,516.0019845490662,1369.5105950388406,513.5019749155207],[\"Q\",1364.0105524884837,511.00196528197523,1357.010498333484,508.50195564842977],[\"Q\",1350.0104441784847,506.00194601488437,1345.5104093645564,504.001938308048],[\"Q\",1341.010374550628,502.00193060121165,1338.0103513413426,501.50192867450255],[\"Q\",1335.010328132057,501.0019267477935,1334.0103203956287,501.0019267477935],[\"L\",1333.0063126592001,501.0019267477935]]},{\"type\":\"i-text\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":1433.01,\"top\":547,\"width\":86.81,\"height\":27.12,\"fill\":\"#ef4444\",\"stroke\":null,\"strokeWidth\":1,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"fontFamily\":\"Inter, sans-serif\",\"fontWeight\":600,\"fontSize\":24,\"text\":\"remove\",\"underline\":false,\"overline\":false,\"linethrough\":false,\"textAlign\":\"left\",\"fontStyle\":\"normal\",\"lineHeight\":1.16,\"textBackgroundColor\":\"\",\"charSpacing\":0,\"styles\":[],\"direction\":\"ltr\",\"path\":null,\"pathStartOffset\":0,\"pathSide\":\"left\",\"pathAlign\":\"baseline\"}]}','2026-08-02 14:17:20',NULL,'done','',NULL),(11,5,1,3.05327,'remove the wrinkles','{\"version\":\"5.3.0\",\"objects\":[{\"type\":\"path\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":718.01,\"top\":464.01,\"width\":180,\"height\":261.88,\"fill\":null,\"stroke\":\"#ef4444\",\"strokeWidth\":4,\"strokeDashArray\":null,\"strokeLineCap\":\"round\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"round\",\"strokeUniform\":false,\"strokeMiterLimit\":10,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"path\":[[\"M\",881.0108157935148,481.0111775916075],[\"Q\",881.0068157935148,481.0071775916075,879.506804188872,479.50715519636935],[\"Q\",878.0067925842293,478.00713280113126,872.5067500338725,476.00710294081375],[\"Q\",867.0067074835157,474.00707308049624,864.5066881424444,472.5070506852581],[\"Q\",862.0066688013732,471.00702829001995,854.006606909945,469.00699842970243],[\"Q\",846.006545018517,467.0069685693849,840.5065024681603,466.50696110430556],[\"Q\",835.0064599178035,466.0069536392262,831.0064289720895,466.0069536392262],[\"Q\",827.0063980263755,466.0069536392262,821.5063554760186,468.0069834995437],[\"Q\",816.0063129256619,470.0070133598612,809.0062587706623,472.00704322017873],[\"Q\",802.0062046156627,474.00707308049624,799.0061814063772,475.50709547573433],[\"Q\",796.0061581970917,477.0071178709725,794.0061427242347,478.5071402662106],[\"Q\",792.0061272513776,480.0071626614488,785.006073096378,481.5071850566869],[\"Q\",778.0060189413786,483.007207451925,773.005980259236,489.00729703287755],[\"Q\",768.0059415770935,495.0073866138301,765.0059183678079,498.0074314043063],[\"Q\",762.0058951585224,501.0074761947826,758.5058680810226,507.00756577573515],[\"Q\",755.0058410035228,513.0076553566877,753.5058293988801,517.5077225424021],[\"Q\",752.0058177942374,522.0077897281166,749.5057984531661,527.0078643789103],[\"Q\",747.0057791120948,532.0079390297041,745.0057636392378,539.5080510058947],[\"Q\",743.0057481663808,547.0081629820854,739.505721088881,557.5083197487523],[\"Q\",736.0056940113813,568.0084765154193,733.50567467031,583.0087004678006],[\"Q\",731.0056553292387,598.008924420182,727.0056243835247,610.5091110471665],[\"Q\",723.0055934378107,623.0092976741508,722.0055857013822,629.5093947201829],[\"Q\",721.0055779649537,636.0094917662148,720.5055740967393,641.0095664170085],[\"Q\",720.0055702285251,646.0096410678022,720.0055702285251,653.0097455789136],[\"Q\",720.0055702285251,660.0098500900249,720.5055740967393,663.5099023455805],[\"Q\",721.0055779649537,667.0099546011362,722.0055857013822,670.0099993916124],[\"Q\",723.0055934378107,673.0100441820887,724.0056011742392,675.5100815074856],[\"Q\",725.0056089106677,678.0101188328824,726.5056205153105,680.0101486932],[\"Q\",728.0056321199532,682.0101785535176,730.0056475928102,684.010208413835],[\"Q\",732.0056630656672,686.0102382741526,741.0057326935238,694.510365180502],[\"Q\",750.0058023213803,703.0104920868514,756.5058526081657,709.010581667804],[\"Q\",763.005902894951,715.0106712487565,768.0059415770935,719.5107384344708],[\"Q\",773.005980259236,724.0108056201852,777.5060150731642,725.5108280154234],[\"Q\",782.0060498870926,727.0108504106615,782.5060537553068,727.5108578757408],[\"Q\",783.0060576235211,728.0108653408203,788.5061001738779,727.5108578757408],[\"Q\",794.0061427242347,727.0108504106615,802.506208483877,724.0108056201852],[\"Q\",811.0062742435193,721.010760829709,816.5063167938761,717.5107085741533],[\"Q\",822.0063593442329,714.0106563185977,824.0063748170899,712.5106339233596],[\"Q\",826.0063902899469,711.0106115281214,829.0064134992324,708.0105667376451],[\"Q\",832.006436708518,705.0105219471689,836.006467654232,700.0104472963751],[\"Q\",840.006498599946,695.0103726455814,847.5065566231598,688.5102755995495],[\"Q\",855.0066146463736,682.0101785535176,862.5066726695874,673.5100516471681],[\"Q\",870.0067306928012,665.0099247408186,876.0067771113722,656.5097978344693],[\"Q\",882.0068235299433,648.0096709281198,885.0068467392289,641.5095738820878],[\"Q\",888.0068699485143,635.0094768360559,890.0068854213714,628.0093723249447],[\"Q\",892.0069008942284,621.0092678138334,894.5069202352996,611.5091259773252],[\"Q\",897.0069395763709,602.008984140817,898.5069511810136,596.0088945598645],[\"Q\",900.0069627856565,590.0088049789119,900.0069627856565,583.50870793288],[\"Q\",900.0069627856565,577.0086108868481,899.0069550492279,572.5085437011337],[\"Q\",898.0069473127994,568.0084765154193,897.5069434445852,564.5084242598637],[\"Q\",897.0069395763709,561.0083720043081,895.5069279717281,557.5083197487525],[\"Q\",894.0069163670854,554.0082674931967,893.5069124988711,551.0082227027204],[\"Q\",893.0069086306569,548.0081779122442,893.0069086306569,545.5081405868473],[\"Q\",893.0069086306569,543.0081032614504,892.0069008942284,540.5080659360535],[\"Q\",891.0068931577998,538.0080286106567,889.5068815531571,536.5080062154185],[\"Q\",888.0068699485143,535.0079838201804,886.5068583438716,533.0079539598628],[\"Q\",885.0068467392289,531.0079240995453,884.5068428710147,529.5079017043072],[\"Q\",884.0068390028003,528.007879309069,882.0068235299434,526.5078569138309],[\"Q\",880.0068080570863,525.0078345185927,879.0068003206578,524.5078270535134],[\"Q\",878.0067925842293,524.007819588434,876.0067771113722,523.0078046582753],[\"L\",874.0027616385153,522.0037897281165]]},{\"type\":\"path\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":860,\"top\":484,\"width\":21.01,\"height\":32.01,\"fill\":null,\"stroke\":\"#ef4444\",\"strokeWidth\":4,\"strokeDashArray\":null,\"strokeLineCap\":\"round\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"round\",\"strokeUniform\":false,\"strokeMiterLimit\":10,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"path\":[[\"M\",862.0026688013731,486.0032522424013],[\"Q\",862.0066688013732,486.0072522424013,864.0066842742301,489.50730449795697],[\"Q\",866.0066997470872,493.00735675351257,869.0067229563726,497.507423939227],[\"Q\",872.0067461656582,502.0074911249414,873.506757770301,504.0075209852589],[\"Q\",875.0067693749438,506.0075508455764,879.0068003206578,512.007640426529],[\"L\",883.0108312663718,518.0117300074816]]},{\"type\":\"i-text\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":896.01,\"top\":638.01,\"width\":234.41,\"height\":27.12,\"fill\":\"#ef4444\",\"stroke\":null,\"strokeWidth\":1,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"fontFamily\":\"Inter, sans-serif\",\"fontWeight\":600,\"fontSize\":24,\"text\":\"remove the wrinkles\",\"underline\":false,\"overline\":false,\"linethrough\":false,\"textAlign\":\"left\",\"fontStyle\":\"normal\",\"lineHeight\":1.16,\"textBackgroundColor\":\"\",\"charSpacing\":0,\"styles\":[],\"direction\":\"ltr\",\"path\":null,\"pathStartOffset\":0,\"pathSide\":\"left\",\"pathAlign\":\"baseline\"}]}','2026-08-03 12:06:29',NULL,'pending',NULL,NULL);
/*!40000 ALTER TABLE `enso8_review_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enso8_review_files`
--

DROP TABLE IF EXISTS `enso8_review_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enso8_review_files` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `review_id` int(11) unsigned NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `proxy_path` varchar(255) DEFAULT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `enso8_review_files_review_id_foreign` (`review_id`),
  CONSTRAINT `enso8_review_files_review_id_foreign` FOREIGN KEY (`review_id`) REFERENCES `enso8_reviews` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enso8_review_files`
--

LOCK TABLES `enso8_review_files` WRITE;
/*!40000 ALTER TABLE `enso8_review_files` DISABLE KEYS */;
INSERT INTO `enso8_review_files` VALUES (1,1,'JBL-AD_SH001_Comp_ACS_Opt01_V02.mp4','JBL-AD-01/shots/SC01/SH001/compositing/reviews/V02/JBL-AD_SH001_Comp_ACS_Opt01_V02.mp4','video',13335413,'2026-08-02 05:13:30','2026-08-02 05:13:30'),(2,2,'JBL-AD_SH001_Comp_ACS_Opt01_V03.mp4','JBL-AD-01/shots/SC01/SH001/compositing/reviews/V03/JBL-AD_SH001_Comp_ACS_Opt01_V03.mp4','video',14743406,'2026-08-02 14:26:42','2026-08-03 10:17:08'),(3,3,'JBL-AD_SH002_Comp_ACS_Opt01_V03.mp4','JBL-AD-01/shots/SC01/SH002/compositing/reviews/V01/JBL-AD_SH002_Comp_ACS_Opt01_V03.mp4','video',21720261,'2026-08-02 22:51:12','2026-08-03 10:17:59'),(4,4,'JBL-AD_SH003_Comp_ACS_Opt01_V03.mp4','JBL-AD-01/shots/SC01/SH003/compositing/reviews/V03/JBL-AD_SH003_Comp_ACS_Opt01_V03.mp4','video',5855813,'2026-08-03 11:04:58','2026-08-03 11:04:58'),(5,5,'JBL-AD_SH001_Comp_ACS_Opt01_V01.mp4','JBL-AD-01/shots/SC02/SH001/compositing/reviews/V01/JBL-AD_SH001_Comp_ACS_Opt01_V01.mp4','video',19843836,'2026-08-03 12:02:03','2026-08-03 12:02:03');
/*!40000 ALTER TABLE `enso8_review_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enso8_reviews`
--

DROP TABLE IF EXISTS `enso8_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enso8_reviews` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `shot_id` int(11) DEFAULT NULL,
  `vfx_task_assignment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `version_string` varchar(50) DEFAULT NULL,
  `version_number` int(11) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `artist_notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `enso8_reviews_project_id_foreign` (`project_id`),
  KEY `enso8_reviews_vfx_task_assignment_id_foreign` (`vfx_task_assignment_id`),
  KEY `enso8_reviews_user_id_foreign` (`user_id`),
  CONSTRAINT `enso8_reviews_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `enso8_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `enso8_reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `enso8_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `enso8_reviews_vfx_task_assignment_id_foreign` FOREIGN KEY (`vfx_task_assignment_id`) REFERENCES `enso8_tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enso8_reviews`
--

LOCK TABLES `enso8_reviews` WRITE;
/*!40000 ALTER TABLE `enso8_reviews` DISABLE KEYS */;
INSERT INTO `enso8_reviews` VALUES (1,1,1,4,2,'V02',2,'revision_needed',' j, c,jhckg yxculyd cuxcduj sxyktrzasy rsxutdclx dlutsx ytux stkx su ktx ty x kyi txsyki skusytsxkyi ytdckygd cukfudctug','2026-08-02 05:13:30','2026-08-02 06:53:52'),(2,1,1,4,2,'V03',3,'approved','New corrections have admited','2026-08-02 14:26:42','2026-08-02 14:32:32'),(3,1,2,7,2,'V01',1,'pending','dtng nxfykmxrdjnrfjxmkryg hrddnhdrddxgrf fjng mkjtgj xgtjtgxjntg xmjkmmkykmc','2026-08-02 22:51:12','2026-08-02 22:51:12'),(4,1,3,8,2,'V03',3,'pending','Final version have been added, fixing all listed correction from internal and client','2026-08-03 11:04:58','2026-08-03 11:04:58'),(5,1,4,9,2,'V01',1,'revision_needed','kjwbfgcikVFJKV kjbgfikebfvfbigi jhvfcvkivkefvesff','2026-08-03 12:02:03','2026-08-03 12:07:25');
/*!40000 ALTER TABLE `enso8_reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enso8_sequences`
--

DROP TABLE IF EXISTS `enso8_sequences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enso8_sequences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enso8_sequences`
--

LOCK TABLES `enso8_sequences` WRITE;
/*!40000 ALTER TABLE `enso8_sequences` DISABLE KEYS */;
INSERT INTO `enso8_sequences` VALUES (1,1,'SC01','','2026-07-29 22:22:00','2026-07-29 22:22:00'),(2,1,'SC02','Durgapooja and Diwali','2026-08-02 23:20:25','2026-08-02 23:20:25');
/*!40000 ALTER TABLE `enso8_sequences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enso8_settings`
--

DROP TABLE IF EXISTS `enso8_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enso8_settings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enso8_settings`
--

LOCK TABLES `enso8_settings` WRITE;
/*!40000 ALTER TABLE `enso8_settings` DISABLE KEYS */;
INSERT INTO `enso8_settings` VALUES (1,'production_drive_path','F:\\ENSO8\\JBL-AD-01','2026-07-29 23:05:25','2026-07-29 23:27:12');
/*!40000 ALTER TABLE `enso8_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enso8_shots`
--

DROP TABLE IF EXISTS `enso8_shots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enso8_shots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `sequence_id` int(11) DEFAULT NULL,
  `shot_number` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `thumbnail_path` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `fps` int(11) DEFAULT 24,
  `frame_count` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enso8_shots`
--

LOCK TABLES `enso8_shots` WRITE;
/*!40000 ALTER TABLE `enso8_shots` DISABLE KEYS */;
INSERT INTO `enso8_shots` VALUES (1,1,1,'SH001','Onam \r\nElements to use: Boat, Coconut trees and water.\r\nProducts: Flip 7 & Clip 5 \r\nAction: Boat comes in with the front part sticking out of the screen. Small wave or movement dramatically lifts both the speakers upward with a water splash. The speakers with droplets suspends mid air and changes colour. \r\nTransition: The water droplets can transition into the petals, or the petals can come in and rotate around the products, changing the background and the products. \r\n','uploads/shots/1785714881_99f809e7c4557f50505f.png','2026-07-29 22:22:59','2026-08-02 23:54:41',NULL,190),(2,1,1,'SH002','Ganesh Chaturthi \r\nElements to use: Yellow marigold flower petals, Dhol tasha drums and tutari with silhouette characters in the background \r\nProduct: JBL Live Beam 4  \r\nAction: The petals and the the product fall into place with the silhouette characters in the bg with small movement. The earbuds will be rotating around the case to give the 3D effect. Light streaks move with the earbud movement. Product colours would be changing here.  \r\nTransition: The light streaks/ribbons starts transitioning to the next scene \r\n','uploads/shots/1785714961_3aa9d08a5b91055e4ebf.png','2026-07-29 22:33:32','2026-08-02 23:56:01',NULL,350),(3,1,1,'SH003','Sounds Festive \r\nAll products lined up with the ‘JBL Sounds Festive’ unit (Unit will be shared by the client) \r\nBackground colour can be suggested. \r\n','uploads/shots/1785715004_e7775c183741373c2a07.png','2026-08-02 23:56:44','2026-08-02 23:56:44',NULL,50),(4,1,2,'SH001','Durga Puja \r\nElements to use: Shuili flowers, Hands (not deity hands), Dhunuchi and Dhak drum silhouette characters behind, and kaash phool\r\nProduct: JBL Live 780NC Headphones (Start with orange colour variant) \r\nAction: The headphones falls into a hand and the hand brings it out of screen and splits into multiple hands with all the headphones colours.\r\nTransition: Light streaks can move and form a round shape, similar to the chakra firework movement and transition into the round light inside the partybox. \r\n','uploads/shots/1785715121_51732337989e3bdaf1b3.png','2026-08-02 23:58:41','2026-08-02 23:58:41',NULL,100),(5,1,2,'SH002','Diwali \r\nElements to use: Diwali lights and fireworks. JBL mics and guitar to be placed - showing that these can be connected. \r\nProduct: Partybox 720 & Partybox Encore 2 Plus  \r\nAction: The light and speakers to stand out of screen. Lights inside the partybox would be moving to the music.  \r\nTransition: Can suggest a transition here to the next screen which will have all the products lined up with the ‘JBL Sounds Festive’ unit \r\n','uploads/shots/1785715158_99ef8fbdd888ce3a2952.png','2026-08-02 23:59:18','2026-08-02 23:59:18',NULL,100),(6,1,2,'SH003','Sounds Festive \r\nAll products lined up with the ‘JBL Sounds Festive’ unit (Unit will be shared by the client) \r\nBackground colour can be suggested. \r\n','uploads/shots/1785715198_d84967cb219e0d6d55f9.png','2026-08-02 23:59:58','2026-08-03 00:00:07',NULL,50);
/*!40000 ALTER TABLE `enso8_shots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enso8_stages`
--

DROP TABLE IF EXISTS `enso8_stages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enso8_stages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `order_index` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enso8_stages`
--

LOCK TABLES `enso8_stages` WRITE;
/*!40000 ALTER TABLE `enso8_stages` DISABLE KEYS */;
/*!40000 ALTER TABLE `enso8_stages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enso8_task_benchmarks`
--

DROP TABLE IF EXISTS `enso8_task_benchmarks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enso8_task_benchmarks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `task_type_id` int(11) NOT NULL,
  `simple_hours` double NOT NULL DEFAULT 0,
  `medium_hours` double NOT NULL DEFAULT 0,
  `complex_hours` double NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enso8_task_benchmarks`
--

LOCK TABLES `enso8_task_benchmarks` WRITE;
/*!40000 ALTER TABLE `enso8_task_benchmarks` DISABLE KEYS */;
INSERT INTO `enso8_task_benchmarks` VALUES (1,1,1,1,3,6,NULL,NULL),(2,1,2,1,3,6,NULL,NULL),(3,1,3,1,3,6,NULL,NULL),(4,1,4,1,3,6,NULL,NULL),(5,1,5,1,3,6,NULL,NULL),(6,1,6,1,3,6,NULL,NULL),(7,1,7,1,3,6,NULL,NULL),(8,1,8,1,3,6,NULL,NULL),(9,1,9,1,3,6,NULL,NULL),(10,1,10,1,3,6,NULL,NULL),(11,1,11,1,3,6,NULL,NULL),(12,1,12,1,3,6,NULL,NULL),(13,1,13,1,3,6,NULL,NULL),(14,1,14,1,3,6,NULL,NULL);
/*!40000 ALTER TABLE `enso8_task_benchmarks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enso8_task_types`
--

DROP TABLE IF EXISTS `enso8_task_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enso8_task_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `benchmark_hours_per_second` decimal(10,4) DEFAULT 0.0000,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enso8_task_types`
--

LOCK TABLES `enso8_task_types` WRITE;
/*!40000 ALTER TABLE `enso8_task_types` DISABLE KEYS */;
INSERT INTO `enso8_task_types` VALUES (1,'Modeling','asset','2026-07-29 22:18:24','2026-07-29 22:18:24',0.5000),(2,'Sculpting','asset','2026-07-29 22:18:24','2026-07-29 22:18:24',0.5000),(3,'Texturing','asset','2026-07-29 22:18:24','2026-07-29 22:18:24',0.5000),(4,'Shading','asset','2026-07-29 22:18:24','2026-07-29 22:18:24',0.5000),(5,'Rigging','asset','2026-07-29 22:18:24','2026-07-29 22:18:24',0.5000),(6,'Grooming','asset','2026-07-29 22:18:24','2026-07-29 22:18:24',0.5000),(7,'Lookdev','asset','2026-07-29 22:18:24','2026-07-29 22:18:24',0.5000),(8,'Animation','shot','2026-07-29 22:18:24','2026-07-29 22:18:24',0.5000),(9,'Layout','shot','2026-07-29 22:18:24','2026-07-29 22:18:24',0.5000),(10,'Lighting','shot','2026-07-29 22:18:24','2026-07-29 22:18:24',0.5000),(11,'Compositing','shot','2026-07-29 22:18:24','2026-07-29 22:18:24',0.5000),(12,'Simulation','shot','2026-07-29 22:18:24','2026-07-29 22:18:24',0.5000),(13,'FX','shot','2026-07-29 22:18:24','2026-07-29 22:18:24',0.5000),(14,'Motion graphics','shot','2026-07-29 22:18:24','2026-07-29 22:18:24',0.5000);
/*!40000 ALTER TABLE `enso8_task_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enso8_tasks`
--

DROP TABLE IF EXISTS `enso8_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enso8_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `shot_id` int(11) DEFAULT NULL,
  `asset_id` int(11) DEFAULT NULL,
  `task_type_id` int(11) NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `complexity` varchar(50) DEFAULT 'Medium',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `estimated_hours` float DEFAULT 8,
  `dependencies` text DEFAULT NULL,
  `priority_percentage` int(3) DEFAULT 50,
  `is_undocked` tinyint(1) DEFAULT 0,
  `due_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `fps` int(11) DEFAULT NULL,
  `frame_count` int(11) DEFAULT NULL,
  `phase_id` int(11) DEFAULT NULL,
  `gantt_row` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enso8_tasks`
--

LOCK TABLES `enso8_tasks` WRITE;
/*!40000 ALTER TABLE `enso8_tasks` DISABLE KEYS */;
INSERT INTO `enso8_tasks` VALUES (1,1,1,NULL,8,3,'completed','2026-07-29 22:31:57','2026-08-02 01:51:33','Medium','2026-08-04 05:00:00','2026-08-07 05:00:00',19,NULL,50,1,NULL,NULL,NULL,NULL,NULL,1),(2,1,1,NULL,9,NULL,'pending','2026-07-29 22:32:03','2026-08-02 01:23:49','Medium','2026-08-03 12:00:00','2026-08-06 12:00:00',12.5,NULL,50,1,NULL,NULL,NULL,NULL,NULL,0),(3,1,1,NULL,10,2,'completed','2026-07-29 22:32:10','2026-08-02 01:41:37','Medium','2026-08-04 05:00:00','2026-08-08 05:00:00',23.75,NULL,50,1,NULL,NULL,NULL,NULL,NULL,2),(4,1,1,NULL,11,2,'completed','2026-07-29 22:32:17','2026-08-02 14:32:32','Complex','2026-08-04 16:00:00','2026-08-07 01:00:00',47.5,NULL,50,1,NULL,NULL,NULL,NULL,NULL,5),(5,1,1,NULL,12,NULL,'pending','2026-07-29 22:32:23','2026-08-02 01:23:49','Medium','2026-08-04 16:00:00','2026-08-06 15:00:00',12.5,NULL,50,1,NULL,NULL,NULL,NULL,NULL,4),(6,1,1,NULL,13,2,'in_progress','2026-07-29 22:32:28','2026-08-02 21:44:51','Medium','2026-08-04 16:00:00','2026-08-07 15:00:00',23.75,NULL,50,1,NULL,NULL,NULL,NULL,NULL,3),(7,1,2,NULL,11,2,'ready_for_review','2026-08-02 22:50:17','2026-08-02 22:50:50','Medium',NULL,NULL,12.5,NULL,50,0,NULL,NULL,NULL,NULL,NULL,0),(8,1,3,NULL,11,2,'ready_for_review','2026-08-03 10:58:28','2026-08-03 11:04:01','Medium',NULL,NULL,6.25,NULL,50,0,NULL,NULL,NULL,NULL,NULL,0),(9,1,4,NULL,11,2,'revision_needed','2026-08-03 11:53:45','2026-08-03 12:07:25','Medium',NULL,NULL,12.5,NULL,50,0,NULL,NULL,NULL,NULL,NULL,0),(10,1,2,NULL,14,2,'pending','2026-08-03 13:15:58','2026-08-03 13:15:58','Medium',NULL,NULL,43.75,NULL,50,0,NULL,NULL,NULL,NULL,NULL,0),(11,1,2,NULL,8,3,'pending','2026-08-03 13:16:32','2026-08-03 13:16:32','Medium',NULL,NULL,35,NULL,50,0,NULL,NULL,NULL,NULL,NULL,0),(12,1,2,NULL,10,2,'pending','2026-08-03 13:18:38','2026-08-03 13:18:38','Medium',NULL,NULL,43.75,NULL,50,0,NULL,NULL,NULL,NULL,NULL,0),(13,1,3,NULL,10,2,'pending','2026-08-03 13:49:05','2026-08-03 13:49:05','Medium',NULL,NULL,6.25,NULL,50,0,NULL,NULL,NULL,NULL,NULL,0);
/*!40000 ALTER TABLE `enso8_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enso8_users`
--

DROP TABLE IF EXISTS `enso8_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enso8_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `global_role` varchar(50) NOT NULL DEFAULT 'Internal Artist',
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `experience_level` varchar(50) DEFAULT 'Mid',
  `weekly_hours` int(11) DEFAULT 40,
  `telegram_chat_id` varchar(255) DEFAULT NULL,
  `telegram_link_code` varchar(50) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enso8_users`
--

LOCK TABLES `enso8_users` WRITE;
/*!40000 ALTER TABLE `enso8_users` DISABLE KEYS */;
INSERT INTO `enso8_users` VALUES (1,'System Admin','admin@enso8.com','$2y$10$M4UGPWML9gCJ6XZuvKa8fuLvPG1sN6Rh8dxwLKiAS2GHG.Fv890ni','admin','active','2026-07-29 21:54:56','2026-07-29 21:54:56','Mid',40,'1588987513','connect_fd4e3faf_1',NULL),(2,'Adith C Satheesh','adith@enso8.com','$2y$10$8pCaynhU9chnXYYLdmme0OaheSpJ3Bguhx.6FrfT8DMoHoxgBQAha','artist','active','2026-07-29 22:43:11','2026-07-29 22:43:11','Mid',40,'1588987513',NULL,NULL),(3,'Amal Krishna','amal@enso8.com','$2y$10$BhoLBdmV6Ul1PsZdkv6lxO3sIiBeoZHYII.VUxNE8ZVTbISazACBC','artist','active','2026-08-02 01:46:48','2026-08-02 01:46:48','Senior',40,NULL,NULL,NULL),(4,'aadi','aadi@jbl.in','$2y$10$jaGlzmFR8wt35kyT2.QiyuM.kstwEq45UJC1IV5gqahrQ1KvI41Ea','client','active','2026-08-03 17:31:14','2026-08-03 17:31:14','Mid',40,NULL,NULL,1);
/*!40000 ALTER TABLE `enso8_users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-08 13:03:35
