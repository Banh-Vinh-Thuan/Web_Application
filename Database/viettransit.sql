-- MySQL dump 10.13  Distrib 8.0.42, for Win64 (x86_64)
--
-- Host: localhost    Database: travelscapes
-- ------------------------------------------------------
-- Server version	9.2.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin_login`
--

DROP TABLE IF EXISTS `admin_login`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_login` (
  `srno` int NOT NULL AUTO_INCREMENT,
  `Admin_Name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Admin_Password` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`srno`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_login`
--

LOCK TABLES `admin_login` WRITE;
/*!40000 ALTER TABLE `admin_login` DISABLE KEYS */;
INSERT INTO `admin_login` VALUES (1,'Admin','Admin'),(2,'Tejas','Tejas'),(3,'atharva','atharva'),(4,'yash','Yash');
/*!40000 ALTER TABLE `admin_login` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cities`
--

DROP TABLE IF EXISTS `cities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cities` (
  `cityid` int NOT NULL AUTO_INCREMENT,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `region` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`cityid`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cities`
--

LOCK TABLES `cities` WRITE;
/*!40000 ALTER TABLE `cities` DISABLE KEYS */;
INSERT INTO `cities` VALUES (10,'Tay Bac','North West'),(11,'Ho Chi Minh','South East'),(12,'Nha Trang','Central '),(13,'Hue','Central '),(14,'Phu Yen','Central '),(15,'Da Lat','Central '),(16,'Phu Quoc','Mekong Delta'),(17,'Hoi An','Central '),(18,'Ha Giang','North West');
/*!40000 ALTER TABLE `cities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `favorites`
--

DROP TABLE IF EXISTS `favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `favorites` (
  `favorite_id` int NOT NULL AUTO_INCREMENT,
  `usersid` int NOT NULL,
  `cityid` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`favorite_id`),
  KEY `usersid` (`usersid`),
  KEY `cityid` (`cityid`),
  CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`usersid`) REFERENCES `login` (`usersid`) ON DELETE CASCADE,
  CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`cityid`) REFERENCES `cities` (`cityid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `favorites`
--

LOCK TABLES `favorites` WRITE;
/*!40000 ALTER TABLE `favorites` DISABLE KEYS */;
INSERT INTO `favorites` VALUES (20,1,10,'2025-05-13 16:38:50'),(21,2,11,'2025-05-14 13:27:39');
/*!40000 ALTER TABLE `favorites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hotel_bookings`
--

DROP TABLE IF EXISTS `hotel_bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hotel_bookings` (
  `booking_id` int NOT NULL AUTO_INCREMENT,
  `userid` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `cityid` int NOT NULL,
  `city_name` varchar(100) NOT NULL,
  `hotelid` int NOT NULL,
  `hotel_name` varchar(100) NOT NULL,
  `tourists` int NOT NULL DEFAULT '1',
  `check_in_date` date DEFAULT NULL,
  `check_out_date` date DEFAULT NULL,
  `room_type` varchar(50) NOT NULL DEFAULT 'Standard',
  `contact` varchar(20) NOT NULL,
  `cost_per_day` int NOT NULL,
  `total_amount` int NOT NULL,
  `order_id` varchar(50) DEFAULT NULL,
  `booking_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `number_of_rooms` int NOT NULL DEFAULT '1',
  `payment_status` varchar(20) DEFAULT 'pending',
  PRIMARY KEY (`booking_id`),
  KEY `userid` (`userid`),
  KEY `cityid` (`cityid`),
  KEY `hotelid` (`hotelid`),
  CONSTRAINT `hotel_bookings_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `login` (`usersid`),
  CONSTRAINT `hotel_bookings_ibfk_2` FOREIGN KEY (`cityid`) REFERENCES `cities` (`cityid`),
  CONSTRAINT `hotel_bookings_ibfk_3` FOREIGN KEY (`hotelid`) REFERENCES `hotels` (`hotelid`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hotel_bookings`
--

LOCK TABLES `hotel_bookings` WRITE;
/*!40000 ALTER TABLE `hotel_bookings` DISABLE KEYS */;
INSERT INTO `hotel_bookings` VALUES (41,2,'test2','test2@test.com',12,'Nha Trang',21,'LYN Panorama Nha Trang Condotel',1,'2025-05-20','2025-05-21','Standard','0784285198',1679999,1679999,NULL,'2025-05-20 15:47:10',1,'pending'),(47,1,'test','test@test.com',11,'Ho Chi Minh',11,'Ming Ngoc Hotel',1,'2025-05-21','2025-05-22','Standard','0129334308',1607800,1607800,NULL,'2025-05-21 03:15:57',1,'completed'),(48,1,'test','test@test.com',10,'Tay Bac',7,'Anh Hong Hotel',1,'2025-05-21','2025-05-22','Standard','0816228857',1485000,1485000,NULL,'2025-05-21 04:11:14',1,'completed'),(49,1,'test','test@test.com',11,'Ho Chi Minh',12,'Nori Homestay',1,'2025-05-21','2025-05-22','Standard','0389189189',1250504,1250504,NULL,'2025-05-21 04:46:08',1,'completed'),(50,1,'test','test@test.com',12,'Nha Trang',22,'Panorama Beachfront',1,'2025-05-21','2025-05-22','Standard','0389189189',1200000,1200000,NULL,'2025-05-21 05:59:39',1,'completed');
/*!40000 ALTER TABLE `hotel_bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hotels`
--

DROP TABLE IF EXISTS `hotels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hotels` (
  `hotelid` int NOT NULL AUTO_INCREMENT,
  `hotel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `cityid` int DEFAULT NULL,
  `cost` bigint DEFAULT NULL,
  `amenities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `ratings` int DEFAULT NULL,
  PRIMARY KEY (`hotelid`),
  KEY `hotels_ibfk_1` (`cityid`),
  CONSTRAINT `hotels_ibfk_1` FOREIGN KEY (`cityid`) REFERENCES `cities` (`cityid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hotels`
--

LOCK TABLES `hotels` WRITE;
/*!40000 ALTER TABLE `hotels` DISABLE KEYS */;
INSERT INTO `hotels` VALUES (1,'An Loc Hotel',10,2079000,'Wifi, Parking',4),(2,'Thanh Nam Hotel',10,1200000,'Wifi, Parking, Bar',3),(3,'Frontier Hostel & Tours',10,1290000,'Wifi, Parking, Breakfast, Bar',4),(4,'Thinh Vuong Hotel',10,2100000,'Wifi, Parking, Airport, Breakfast',3),(5,'Hoang Lien Hotel',10,2250000,'Wifi, Parking, Pool',4),(6,'Muong Thanh Hotel',10,3231900,'Wifi, Parking, Breakfast, Pool',4),(7,'Anh Hong Hotel',10,1485000,'Parking, Bar, Breakfast',5),(8,'Hoa Sua Hotel',10,1530000,'Parking',4),(9,'Hoang Ha River Town',10,1155000,'Wifi, Parking',4),(10,'Gem Market Guest House',10,900000,'Wifi, Parking, Breakfast, Pool, Airport',4),(11,'Ming Ngoc Hotel',11,1607800,'Wifi, Parking',2),(12,'Nori Homestay',11,1250504,'Wifi',4),(13,'Vintage Park View Hotel',11,1044000,'Wifi, Parking, Breakfast, Airport, Spa',4),(14,'Quy Hung Hotel',11,1320000,'Wifi, Airport',4),(15,'Niecy Apartment',11,1572278,'Wifi, Parking',4),(16,'Anpha Apartment Hotel',11,2970000,'Wifi, Parking, Breakfast, Airport, Spa, Bar',3),(17,'S79 Boutique Hotel',11,1228125,'Wifi, Parking',4),(18,'Chez Mimosa',11,2316600,'Wifi, Breakfast',3),(19,'The Luxury MAYS Hotel',11,1134000,'Wifi, Parking, Airport, Spa',5),(20,'The Alpina Hstaad',11,1053000,'Wifi, Airport',5),(21,'LYN Panorama Nha Trang Condotel',12,1679999,'Wifi, Airport',5),(22,'Panorama Beachfront',12,1200000,'Wifi, Airport',4),(23,'Hermes',12,639998,'Wifi, Airport, Parking',4),(24,'MySea Panorama',12,880000,'Wifi, Airport, Parking, Breakfast',4),(25,'OCEANFRONT SUITES',12,1125000,'Wifi, Airport, Parking',5),(26,'Senia Hotel',12,875000,'Wifi, Airport, Pool',5),(27,'AQua Hotel',12,450000,'Wifi, Airport',5),(28,'Yen Indochine',12,554400,'Wifi, Airport, Pool, Parking',5),(29,'Lucky Sun Hotel',12,958850,'Airport, Pool, Bar, Breakfast',5),(30,'Sun Kiss Hotel',12,885600,'Wifi, Airport, Pool, Bar, Breakfast',5),(31,'VinaSpa Hotel',13,719397,'Wifi, Parking, Breakfast, Spa, Airport',2),(32,'BIG Hotel',13,585906,'Wifi, Airport, Parking',5),(33,'Cosy Homestay',13,306000,'Wifi, Airport, Parking',3),(34,'Gardenia Hue Hotel',13,624000,'Wifi, Airport, Parking, Breakfast',5),(35,'Dory Homestay',13,324000,'Wifi, Parking',5),(36,'Moonlight Boutique Hotel',13,520200,'Wifi, Parking, Airport, Pool',5),(37,'Charm House Hue',13,495000,'Wifi, Airport, Parking, Breakfast',5),(38,'The Scarlett Boutique Hotel',13,906873,'Wifi, Airport, Parking, Breakfast',5),(39,'Son Ca',13,1655000,'Wifi, Airport, Parking',5),(40,'Sunny B Hotel',13,1350000,'Wifi, Airport, Parking',5),(41,'Novus Sol Hotel Phu Quoc',16,1550000,'Wifi, Parking',5),(42,'The Pier Phu Quoc Resort',16,2380000,'Wifi, Pool, Parking',4),(43,'Sole Casa Phu Quoc',16,1825000,'Wifi, Tivi',4),(44,'Luna Sol Villas',16,2277000,'Wifi, Tivi, Parking, Bar',5),(45,'Casepia',16,1683000,'Wifi, Tivi, Bar',4),(46,'Azure Premium Apartments',16,956000,'Tivi, Bar',4),(47,'Mira Home',16,1008000,'Tivi, Bar',3),(48,'Rova Hotel',16,374000,'Wifi, Bar',3),(49,'Oscar Seaview Apartment',16,1600000,'Wifi, Pool, Tivi, Bar',4),(50,'Maris Beach Hotel Phu Quoc',16,720000,'Wifi, Parking, Pool, Breakfast',4),(51,'Thanh Premier Dalat',15,1400000,'Wifi, Parking, Airport',4),(52,'BIDV Central Dalat Hotel',15,1639000,'Wifi, Parking, Breakfast, Airport',5),(53,'Greenview City Hotel',15,1954000,'Wifi, Tivi, Parking, Airport',5),(54,'Nature Hotel City Center',15,1161000,'Wifi, Tivi, Parking, Airport',4),(55,'Dalat Wind Hotel',15,678000,'Wifi, Tivi, Bar, Parking',3),(56,'Phuong Vy Luxuty Hotel',15,1395000,'Wifi, Tivi, Parking',4),(57,'Dalat Hills Apartment',15,650000,'Wifi, Tivi, Parking, Airport',3),(58,'Nature Green Hotel',15,1842000,'Wifi, Tivi, Parking, Breakfast',4),(59,'Carnival Hotel',15,1200000,'Wifi, Tivi, Parking, Breakfast, Bar',5),(60,'Thanh Thanh Hotel',15,1100000,'Wifi, Tivi, Parking, Airport',4),(61,'Handstay Nghinh Phong',14,1300000,'Wifi, Tivi, Parking, Bar',4),(62,'Apec Mandala Phu Yen',14,1135000,'Wifi, Tivi, Parking, Bar',4),(63,'Palm Beach Hotel Phu Yen',14,1590000,'Wifi, Tivi, Parking, Bar, Breakfast, Pool',5),(64,'PAMY Homestay Phu Yen',14,1245389,'Wifi, Tivi, Parking, Bar',4),(65,'Coconut Hotel Phú Yên',14,1734201,'Wifi, Parking, Bar, Breakfast, Pool',5),(66,'Paradise Hotel & Homestay',14,788563,'Wifi, Parking',3),(67,'Sala Tuy Hoa Beach Hotel',14,1029447,'Wifi, Pool, Bar, Parking',5),(68,'Dreamville Beach Homestay',14,1593820,'Wifi, Tivi, Parking, Breakfast, Pool',5),(69,'Daisy House',14,686117,'Wifi, Parking',4),(70,'Ivory Phu Yen Hotel',14,957000,'Pool, Parking, Breakfast',5),(71,'Du De Homestay',17,720000,'Wifi, Parking, Breakfast, Airport',4),(72,'Renaissance Hoi An Resort & Spa',17,2100000,'Wifi, Parking, Breakfast, Airport, Bar',5),(73,'Moire Hoi An',17,3973000,'Wifi, Parking, Breakfast, Airport, Bar, Pool',5),(74,'Elites Riverside Hotel & Spa Hoi An',17,2167000,'Wifi, Breakfast, Airport, Bar, Pool',4),(75,'December Hoi An Villa',17,4000000,'Wifi, Breakfast, Airport, Bar, Pool, Parking',5),(76,'Ocean Breeze Villa',17,1300000,'Wifi, Breakfast, Airport, Pool, Parking',4),(77,'Serene Nature Hotel & Spa',17,3150000,'Wifi, Breakfast, Airport, Bar, Pool, Parking',5),(78,'Babylon Hoi An Central Villa',17,1550000,'Wifi, Breakfast, Airport, Pool, Parking',4),(79,'Truc Loc Villa',17,1085000,'Wifi, Breakfast, Pool, Parking',4),(80,'An Bang Vu Nhi Homestay',17,2095251,'Wifi, Breakfast, Airport, Parking',4),(81,'Nhà Lá Homestay',18,1250000,'Wifi, Breakfast, Parking',4),(82,'Four Points by Sheraton Ha Giang',18,1425000,'Breakfast, Pool, Parking',4),(83,'H\'mong Village Resort Ha Giang',18,1057540,'Wifi, Breakfast, Pool, Parking',5),(84,'Lotus Premium Lodge - Group Tours',18,1170000,'Wifi, Breakfast, Pool, Parking, Airport, Bar',5),(85,'Yen Bien Luxury Hotel',18,2450000,'Breakfast, Pool, Parking, Airport, Bar',5),(86,'Silk River Hotel Ha Giang',18,1035000,'Wifi, Breakfast, Pool, Parking, Bar',4),(87,'Garden Hotel II',18,1093950,'Wifi, Breakfast, Parking, Airport, Bar',4),(88,'Sun Hà Giang Hotel',18,600000,'Wifi, Breakfast, Parking',4),(89,'Sky Bay Lodge',18,970000,'Wifi, Breakfast, Pool, Parking, Bar',5),(90,'Ha Giang Xanh Retreat',18,935000,'Wifi, Breakfast, Pool, Parking, Airport, Bar',5);
/*!40000 ALTER TABLE `hotels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login`
--

DROP TABLE IF EXISTS `login`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `login` (
  `usersid` int NOT NULL AUTO_INCREMENT,
  `usersEmail` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `usersuid` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `userspwd` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`usersid`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login`
--

LOCK TABLES `login` WRITE;
/*!40000 ALTER TABLE `login` DISABLE KEYS */;
INSERT INTO `login` VALUES (1,'test@test.com','test','$2y$10$flekLyq.6a/gn12Xsdm0v.SZZCrx3/.Pm3M3Ucvel8MwCzReorRsq'),(2,'test2@test.com','test2','$2y$10$uqx8Jk9IDXZ8q1x4JFPR5OYiaCKYTquxsXH6A0OM7hTXkpEKXzjJG'),(3,'test3@test.com','test3','$2y$10$OGLEA.ETOj1DB0fRhjjuduKb4OJ75.WB5wgs5UVUBPjTMbcoUVVgy'),(5,'test4@test.com','test4','$2y$10$ikw.LpmUCDlMTuK2qdUyNuueZOn2Zrqkg4WLEuUaqDFLYh4YjrIlC'),(7,'test@123.com','socola12345','$2y$12$Ma8zP5fyLq7KKXOaYoHkc.a6PtOTR22UKyUpYer0X6Z33VmvCjG2e');
/*!40000 ALTER TABLE `login` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tour_bookings`
--

DROP TABLE IF EXISTS `tour_bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tour_bookings` (
  `booking_id` int NOT NULL AUTO_INCREMENT,
  `userid` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `cityid` int NOT NULL,
  `city_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `tourid` int NOT NULL,
  `tour_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `tourists` int NOT NULL DEFAULT '1',
  `tour_date` date NOT NULL,
  `contact` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `price_per_person` int NOT NULL,
  `total_amount` int NOT NULL,
  `booking_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `order_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_status` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'pending',
  PRIMARY KEY (`booking_id`),
  KEY `userid` (`userid`),
  KEY `cityid` (`cityid`),
  KEY `tourid` (`tourid`),
  CONSTRAINT `tour_bookings_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `login` (`usersid`),
  CONSTRAINT `tour_bookings_ibfk_2` FOREIGN KEY (`cityid`) REFERENCES `cities` (`cityid`),
  CONSTRAINT `tour_bookings_ibfk_3` FOREIGN KEY (`tourid`) REFERENCES `tours` (`tourid`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tour_bookings`
--

LOCK TABLES `tour_bookings` WRITE;
/*!40000 ALTER TABLE `tour_bookings` DISABLE KEYS */;
INSERT INTO `tour_bookings` VALUES (23,1,'test','test@test.com',11,'Ho Chi Minh',8,'Mekong Delta Tour Departing from Ho Chi Minh City',1,'2025-05-21','0389189189',1050000,1050000,'2025-05-21 06:58:51','MOMO1747810731','completed');
/*!40000 ALTER TABLE `tour_bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tours`
--

DROP TABLE IF EXISTS `tours`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tours` (
  `tourid` int NOT NULL AUTO_INCREMENT,
  `cityid` int NOT NULL,
  `tour_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `duration_days` int NOT NULL,
  `price_per_person` int NOT NULL,
  `season` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`tourid`),
  KEY `cityid` (`cityid`),
  CONSTRAINT `tours_ibfk_1` FOREIGN KEY (`cityid`) REFERENCES `cities` (`cityid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tours`
--

LOCK TABLES `tours` WRITE;
/*!40000 ALTER TABLE `tours` DISABLE KEYS */;
INSERT INTO `tours` VALUES (1,10,'Northwest in Rice Harvest Season - Nghia Lo - Mu Cang Chai - Sapa - Fansipan - Lai Chau - Dien Bien - Moc Chau','Marvel at golden terraced rice fields during the stunning harvest season in Northern Vietnam.\r\nVisit remote highland regions including Nghia Lo, Mu Cang Chai, and Lai Chau for authentic local experiences.\r\nConquer the peak of Fansipan – the \"Roof of Indochina\" – by cable car or trekking.\r\nDiscover the cultural richness of ethnic minorities such as H’mong, Thai, and Dao.\r\nRelax in the cool climate and natural beauty of Moc Chau Plateau and Dien Bien countryside.',6,12179000,'Spring','2025-05-16 11:15:15'),(2,10,'Hung Temple - Nghia Lo - Tu Le - Mu Cang Chai - Sapa - Fansipan - Dien Bien - Moc Chau','Visit the sacred Hung Temple, an important spiritual and cultural site in Vietnamese history.\r\nAdmire the golden rice terraces in Tu Le and Mu Cang Chai during the harvest season.\r\nConquer Fansipan – the Roof of Indochina – with a spectacular cable car ride.\r\nExplore Sapa’s unique ethnic culture and highland charm.\r\nEnjoy a scenic and insightful journey through Dien Bien and the peaceful Moc Chau Plateau.\r\nSmall group tour with convenient departures from Ho Chi Minh City.',4,13590000,'Fall','2025-05-16 11:16:25'),(3,10,'Sapa - Fansipan - Y Ty - Bat Xat Rice Terraces','Admire the magnificent terraced fields of Y Ty and Bat Xat, one of the most beautiful highland regions in Vietnam.\r\nConquer the Roof of Indochina – Fansipan – by cable car with stunning panoramic views.\r\nImmerse yourself in the unique culture and lifestyle of the H\'Mong and Ha Nhi ethnic groups.',3,9405000,'Summer','2025-05-16 11:16:59'),(4,10,'Moc Chau - Pha Din Pass - Son La - Dien Bien','Admire the poetic beauty of Moc Chau Plateau with green tea hills and blooming flower valleys.\r\nPass through the legendary Pha Din Pass – one of Vietnam’s four great mountain passes.\r\nExplore the historical battlefield of Dien Bien Phu and the local ethnic cultures.',5,10545000,'Summer','2025-05-16 11:17:39'),(5,11,'Discover Ho Chi Minh - Cu Chi Tunnels - Mekong Delta','Dive into Vietnam’s history at the Cu Chi Tunnels\r\nExperience the vibrant local life of the Mekong Delta\r\nExplore iconic landmarks of Ho Chi Minh City\r\nEnjoy comfortable travel and hotel accommodations\r\nPerfect for small groups with daily departures',4,4590000,'Spring','2025-05-16 11:20:26'),(6,11,'Ho Chi Minh City Street Food and Culture Walk','Discover Saigon\'s iconic landmarks over three days of exploration.\r\nExperience the majestic architecture of Duc Ba Church and the vibrant Nguyen Hue Street.\r\nEnjoy panoramic city views from Landmark 81 and the bustling nightlife of Bui Vien Walking Street.\r\nExplore the historical significance of Dinh Doc Lap.',1,890000,'Fall','2025-05-16 11:21:16'),(7,11,'Museum and Contemporary Art Tour in Ho Chi Minh','Immerse yourself in Vietnam\'s art and culture scene\r\nVisit top museums and contemporary art galleries\r\nExpert guides offer in-depth cultural insights\r\nComfortable bus transport and hotel accommodation\r\nIdeal for small groups on weekday getaways',2,980000,'Winter','2025-05-16 11:21:47'),(8,11,'Mekong Delta Tour Departing from Ho Chi Minh City','Escape the city for a day and experience the peaceful life of the Mekong Delta.\r\nEnjoy scenic boat rides along winding rivers and lush canals.\r\nVisit traditional villages, workshops, and tropical fruit orchards.\r\nSample delicious local cuisine and interact with friendly locals.\r\nConvenient daily departures with comfortable bus and boat transport.',1,1050000,'Summer','2025-05-16 11:22:36'),(9,12,'Nha Trang - Thap Ba Ponagar - Nhu Tien Beach - VinWonders - Bau Truc Pottery Village','Discover Nha Trang’s rich cultural heritage and stunning landscapes.\r\nExperience luxurious stays at a 4-star hotel with premium amenities.\r\nVisit the iconic Tháp Bà Ponagar, relax at Nhũ Tiên Beach, and enjoy thrilling rides at VinWonders.',3,2390000,'Winter','2025-05-16 11:25:35'),(10,12,'Nha Trang - Pottery & Seashell Workshop, Ancient Nha Trang & More','Visit Bau Truc Pottery Village, one of Southeast Asia’s oldest pottery villages.\r\nExplore the rich cultural heritage of ancient Nha Trang and Thap Ba Ponagar.\r\nUnwind at the pristine Nhu Tien Beach and experience family-friendly fun at VinWonders.\r\nCreate your own souvenirs during a unique Pottery & Seashell Workshop Experience.\r\nStay at a comfortable 4-star hotel and enjoy a well-curated travel experience departing from Da Nang.',3,2690000,'Fall','2025-05-16 11:26:29'),(11,12,'Nha Trang - Hon Mun Island Snorkeling - Institute of Oceanography - Vinpearl Cable Car - Long Son Pagoda - Local Seafood Feast','Snorkel among vibrant coral reefs at the famous Hon Mun Island.\r\nVisit Vietnam’s oldest marine research center – the Institute of Oceanography.\r\nTake a scenic ride on the Vinpearl Cable Car across Nha Trang Bay.\r\nAdmire the architecture and spiritual ambiance of Long Son Pagoda.\r\nIndulge in a delicious local seafood feast by the beach.\r\nRelax in comfort at a 4-star beachfront hotel with stunning ocean views.\r\nConvenient departure from Ho Chi Minh City by flight.',3,3190000,'Summer','2025-05-16 11:27:15'),(12,12,'Nha Trang - Yang Bay Eco Park - Mud Bath Experience - Dam Market - Thap Ba Ponagar - Coastal Road Discovery','Discover the natural beauty and cultural charm of Yang Bay Eco Park.\r\nRelax and rejuvenate with a traditional Nha Trang mud bath experience.\r\nVisit Thap Ba Ponagar, a historical Cham temple complex.\r\nExplore Dam Market, a vibrant hub for local specialties and souvenirs.\r\nEnjoy a scenic drive along Nha Trang’s picturesque coastal roads.\r\nStay at a comfortable 3-star hotel conveniently located near the beach.\r\nEasy and quick flight departure from Ho Chi Minh City.',3,2890000,'Fall','2025-05-16 11:27:43'),(13,13,'Da Nang - Phong Nha Cave - La Vang - Hue - Ba Na Hills - Hoi An','Explore the natural beauty of Phong Nha Cave and the sacred La Vang Pilgrimage Center.\r\nDiscover the historical essence of Hue and the ancient charm of Hoi An.\r\nExperience the stunning Ba Na Hills and the iconic Golden Bridge over a 5-day immersive journey.',5,6990000,'Summer','2025-05-16 11:29:11'),(14,13,'Hanoi - Sapa - Fansipan - Ha Long - Ninh Binh - Da Nang - Son Tra - Ba Na Hills - Golden Bridge - Hoi An - La Vang - Hue - Phong Nha','Comprehensive journey through Vietnam’s cultural and natural highlights.\r\nConquer Fansipan, the highest peak in Indochina.\r\nMarvel at Ha Long Bay, the Golden Bridge, and Hoi An’s ancient charm.',10,23590000,'Summer','2025-05-16 11:31:01'),(15,13,'Hue Cultural Discovery – Imperial Citadel – Thien Mu Pagoda – Perfume River – Truong Tien Bridge – Local Cuisine','Immerse yourself in the cultural richness of Hue, Vietnam’s former imperial capital.\r\nExplore the historic Imperial Citadel and iconic Thien Mu Pagoda.\r\nEnjoy a scenic boat ride along the poetic Perfume River.\r\nWalk across the symbolic Truong Tien Bridge and admire its French architecture.\r\nSavor authentic Hue cuisine with unique local flavors.\r\nComfortable travel with flights departing from Can Tho.',4,7590000,'Summer','2025-05-16 11:32:05'),(16,13,'Hanoi - Overnight Train to Hue - Imperial City - Khai Dinh Tomb - Dong Ba Market - Relaxing Cruise on Perfume River','Experience a scenic overnight train journey from Hanoi to Hue.\r\nExplore the majestic Imperial City and the ornate Khai Dinh Tomb.\r\nShop like a local at the bustling Dong Ba Market.\r\nRelax with a peaceful boat cruise along the poetic Perfume River.\r\nStay at a comfortable 3-star hotel in the heart of Hue.\r\nConvenient departure from Hanoi with train travel included.',4,5290000,'Summer','2025-05-16 11:33:15'),(17,14,'Phu Yen Gems: Da Dia Reef - Bai Xep - Mang Lang Church - Nhan Tower','Marvel at the breathtaking Da Dia Reef with its rare hexagonal rock formations.\r\nRelax on the peaceful golden sands of Bai Xep beach.\r\nVisit the historic Mang Lang Church, one of Vietnam’s oldest Catholic sites.\r\nExplore the ancient Nhan Tower with panoramic views of Tuy Hoa city.\r\nEnjoy a scenic train ride along Vietnam’s stunning coastal railway.',4,5990000,'Summer','2025-05-16 11:35:21'),(18,14,'Phu Yen - Mui Dien Sunrise - Vung Ro Bay - Bai Mon Beach - Local Seafood Tasting','Witness a stunning sunrise at Mui Dien – the easternmost point of Vietnam.\r\nCruise through the scenic Vung Ro Bay, rich in natural beauty and historical significance.\r\nUnwind on the pristine sands of Bai Mon Beach with turquoise waters and gentle waves.\r\nSavor authentic local seafood fresh from the coast.\r\nStay in a luxurious 4-star beach resort for ultimate relaxation and comfort.\r\nConvenient flight departure from Ho Chi Minh City for easy travel access.',4,7790000,'Summer','2025-05-16 11:36:00'),(19,14,'Phu Yen - Quy Nhon - Ky Co - Bai Xep - Ganh Da Dia','Discover two coastal gems of Central Vietnam: Phu Yen and Quy Nhon.\r\nRelax on the picturesque beaches of Ky Co and Bai Xep with crystal-clear waters.\r\nMarvel at the extraordinary hexagonal rock formations of Ganh Da Dia.\r\nEnjoy authentic Central Vietnamese cuisine and fresh seafood.\r\nComfortable stay in quality hotels along the coast.\r\nConvenient flight departure from Hanoi for a seamless travel experience.\r\n',4,8490000,'Summer','2025-05-16 11:36:42'),(20,14,'Phu Yen - Tuy Hoa - Ganh Da Dia - Vung Ro Bay - Mang Lang Church','Explore the serene beauty of Phu Yen and the coastal charm of Tuy Hoa city.\r\nVisit Ganh Da Dia, a rare geological wonder with unique hexagonal rock columns.\r\nCruise through Vung Ro Bay, a site rich in both history and natural beauty.\r\nDiscover Mang Lang Church, one of Vietnam’s oldest Catholic landmarks.\r\nExperience an unforgettable train journey along the scenic coastline of Central Vietnam.\r\nDeparture from Da Nang with all transportation and accommodation arranged for comfort.',5,6690000,'Summer','2025-05-16 11:37:19'),(21,15,'Dalat Dreamy Getaway: Valley of Love - Datanla Waterfall - Langbiang Mountain - Local Flower Gardens','Experience the cool, romantic atmosphere of Dalat – Vietnam’s “City of Eternal Spring.”\r\nVisit iconic landmarks such as Xuan Huong Lake, Valley of Love, and Dalat Flower Park.\r\nExplore the unique architecture of Linh Phuoc Pagoda and Bao Dai Palace.\r\nDiscover French colonial heritage and colorful flower gardens throughout the city.\r\nEnjoy a convenient and comfortable trip with departure from Ho Chi Minh City.',4,5290000,'Summer','2025-05-16 11:40:23'),(22,15,'Dalat Nature & Culture: Tuyen Lam Lake - Truc Lam Zen Monastery - Clay Tunnel - Night Market','Experience the cool, romantic atmosphere of Dalat – Vietnam’s “City of Eternal Spring.”\r\nVisit iconic landmarks such as Xuan Huong Lake, Valley of Love, and Dalat Flower Park.\r\nExplore the unique architecture of Linh Phuoc Pagoda and Bao Dai Palace.\r\nDiscover French colonial heritage and colorful flower gardens throughout the city.\r\nEnjoy a convenient and comfortable trip with departure from Ho Chi Minh City.',4,5690000,'Summer','2025-05-16 11:41:08'),(23,15,'Dalat Flower & Highlands Adventure: Valley of Love - Langbiang Peak - Dalat Railway - Hydrangea Garden','Explore Dalat\'s most iconic flower-themed attractions and highland adventures.\r\nStroll through the romantic Valley of Love and colorful Hydrangea Garden.\r\nConquer Langbiang Peak for breathtaking panoramic views of Dalat and the highlands.\r\nEnjoy a nostalgic ride on the historic Dalat Railway through scenic countryside.\r\nConvenient flight departure from Da Nang and quality hotel accommodation included.',4,6290000,'Summer','2025-05-16 11:41:56'),(24,15,'Dalat Chill & Relax: Tuyen Lam Lake - Clay Tunnel - Fresh Garden - Coffee Farm Experience','Relax and unwind in Dalat’s serene natural settings and charming countryside.\r\nEnjoy peaceful strolls around Tuyen Lam Lake and explore the artistic Clay Tunnel.\r\nVisit Fresh Garden, a beautiful flower and vegetable garden showcasing Dalat’s agriculture.\r\nExperience an authentic coffee farm tour to learn about local coffee cultivation and tasting.\r\nConvenient bus departure from Ho Chi Minh City with comfortable hotel accommodation included.',4,5790000,'Summer','2025-05-16 11:42:25'),(25,16,'Phu Quoc Paradise Escape: Sao Beach - Vinpearl Safari - Night Market - Pepper Farm','Relax on the pristine Sao Beach, known for its white sand and clear turquoise waters.\r\nExperience the exciting Vinpearl Safari, home to diverse wildlife in natural habitats.\r\nExplore the vibrant Phu Quoc Night Market with delicious local food and unique souvenirs.\r\nVisit a traditional Pepper Farm to learn about one of Phu Quoc’s famous agricultural products.\r\nEnjoy a comfortable trip with direct flight departure from Ho Chi Minh City and quality hotel accommodations.',4,6290000,'Summer','2025-05-16 11:43:51'),(26,16,'Phu Quoc Island Adventure: Phu Quoc National Park - Fish Sauce Factory - Coconut Prison - Sunset Beach','Explore the lush landscapes of Phu Quoc National Park, home to diverse flora and fauna.\r\nVisit a traditional Fish Sauce Factory and learn about this iconic local product’s production.\r\nDiscover the history of Coconut Prison, a significant site from the Vietnam War era.\r\nRelax and enjoy the stunning views at Sunset Beach, a perfect spot for evening tranquility.\r\nEnjoy a comfortable trip with direct flight departure from Ho Chi Minh City and quality hotel accommodations.',4,5790000,'Summer','2025-05-16 11:44:19'),(27,16,'Phu Quoc Island Serenity: Sao Beach - Pepper Farm - Night Market - Sunset at Dinh Cau','Relax on the pristine Sao Beach, famous for its white sand and crystal-clear waters.\r\nVisit a local Pepper Farm and learn about the cultivation of Phu Quoc’s famous pepper.\r\nExplore the bustling Phu Quoc Night Market with a variety of local foods and unique souvenirs.\r\nEnjoy a serene sunset at Dinh Cau Night Market, a scenic and cultural highlight of the island.\r\nTravel comfortably with direct flight departure from Da Nang and stay in quality hotel accommodations.',4,6490000,'Summer','2025-05-16 11:44:59'),(28,16,'Phu Quoc Relax & Discover: Ong Lang Beach - Pepper Farm - Fish Sauce Village - Night Market','Relax on the serene Ong Lang Beach, known for its peaceful atmosphere and clear waters.\r\nVisit a local Pepper Farm to learn about the cultivation of Phu Quoc’s famous pepper.\r\nExplore the traditional Fish Sauce Village and discover how this iconic product is made.\r\nExperience the lively Phu Quoc Night Market with delicious local food and unique souvenirs.\r\nEnjoy a comfortable trip with convenient bus departure from Hanoi and quality hotel accommodations.',4,6390000,'Summer','2025-05-16 11:45:28'),(29,17,'Hoi An Ancient Town Discovery: Old Quarter - Japanese Bridge - Riverside Market - Lantern Festival','Wander through the charming streets of Hoi An Ancient Town, a UNESCO World Heritage Site rich in history and culture.\r\nVisit the iconic Japanese Covered Bridge, a symbol of Hoi An\'s architectural beauty and cultural fusion.\r\nExplore the vibrant Riverside Market, filled with local delicacies, handmade goods, and bustling daily life.\r\nExperience the magical Lantern Festival, where colorful lanterns light up the old town and riverbanks.\r\nEnjoy a relaxing journey with bus transportation from Da Nang and comfortable hotel accommodations.',4,6490000,'Summer','2025-05-16 11:46:33'),(30,17,'Hoi An Cultural Experience: Ancient Town - Riverside Market - Traditional Craft Villages - Lantern Festival','Discover the charm of Hoi An Ancient Town, a UNESCO World Heritage Site with centuries-old architecture and vibrant local life.\r\nExperience the lively Riverside Market, where you can shop for local specialties and enjoy authentic street food.\r\nExplore traditional craft villages to learn about lantern-making, pottery, and other unique Vietnamese artisanal traditions.\r\nImmerse yourself in the magical Lantern Festival, with colorful lanterns illuminating the historic town and riverbanks.\r\nTravel comfortably from Hanoi by bus and enjoy cozy accommodations throughout your journey.',4,6390000,'Summer','2025-05-16 11:47:02'),(31,17,'Hoi An Heritage & Beach: Ancient Town - My Son Sanctuary - An Bang Beach - Traditional Cooking Class','Discover the historic charm of Hoi An Ancient Town, a UNESCO World Heritage Site with preserved architecture and rich cultural heritage.\r\nVisit the sacred My Son Sanctuary, an ancient Champa temple complex nestled in the lush mountains of Quang Nam.\r\nRelax at An Bang Beach, one of the most beautiful beaches in Vietnam with soft white sand and clear blue waters.\r\nParticipate in a traditional Vietnamese cooking class and learn to prepare local dishes with fresh ingredients.\r\nTravel conveniently from Hanoi by bus and enjoy a comfortable stay in quality hotel accommodations.',4,6290000,'Summer','2025-05-16 11:47:38'),(32,17,'Hoi An Nature & Culture: Cam Thanh Village - Thu Bon River Cruise - Marble Mountains - Local Handicraft Workshops','Explore the peaceful Cam Thanh Village and enjoy a unique basket boat ride through nipa palm forests.\r\nCruise along the Thu Bon River and admire the scenic countryside and daily river life of the locals.\r\nDiscover the spiritual Marble Mountains with its ancient caves, temples, and panoramic coastal views.\r\nEngage in local handicraft workshops to learn traditional skills like lantern-making and wood carving.\r\nDepart conveniently from Da Nang by bus and stay in well-appointed hotel accommodations for a balanced nature and cultural retreat.',4,6150000,'Summer','2025-05-16 11:48:02'),(33,18,'Ha Giang Adventure & Culture: Ma Pi Leng Pass - Dong Van Plateau - Lung Cu Flag Tower - Ethnic Villages','Explore the breathtaking Ma Pi Leng Pass, one of Vietnam’s most spectacular mountain passes with stunning views.\r\nDiscover the unique landscape of Dong Van Karst Plateau, a UNESCO Global Geopark.\r\nVisit the historic Lung Cu Flag Tower, symbolizing the northernmost point of Vietnam.\r\nImmerse yourself in the vibrant culture of local ethnic villages, experiencing traditional lifestyles and customs.\r\nEnjoy a convenient and comfortable journey with departure from Hanoi, staying in quality hotels along the way.',4,6890000,'Summer','2025-05-16 11:50:47'),(34,18,'Ha Giang Explorer: Quan Ba Heaven Gate - Dong Van Karst Plateau - Meo Vac - Local Homestay Experience','Marvel at the stunning views from Quan Ba Heaven Gate, known as the \"Gateway to Heaven\" in Ha Giang.\r\nExplore the unique and breathtaking Dong Van Karst Plateau, a UNESCO Global Geopark.\r\nVisit Meo Vac, a picturesque town surrounded by dramatic limestone mountains and deep valleys.\r\nExperience authentic local culture with an overnight stay at a traditional homestay, interacting with ethnic minorities.\r\nEnjoy a comfortable and well-organized journey departing from Can Tho, with a mix of hotel and homestay accommodations.',4,6990000,'Summer','2025-05-16 11:51:27'),(35,18,'Ha Giang Nature & Culture: Lung Cu Flagpole - Nho Que River Boat Ride - Ma Pi Leng Pass - Minority Villages','Visit Lung Cu Flagpole, the iconic northernmost point of Vietnam with panoramic mountain views.\r\nEnjoy a scenic boat ride along the crystal-clear Nho Que River, winding through breathtaking limestone cliffs.\r\nExperience the dramatic landscapes of Ma Pi Leng Pass, one of Vietnam’s most spectacular mountain roads.\r\nDiscover the rich traditions and vibrant culture of local ethnic minority villages through homestay and community visits.\r\nTravel comfortably with a well-organized itinerary departing from Hanoi, staying in quality hotels and homestays.',4,6190000,'Summer','2025-05-16 11:52:02'),(36,18,'Ha Giang Nature & Culture: Dong Van Karst Plateau - Lung Cam Cultural Village - Ma Pi Leng Panorama - Local Craft Workshops','Explore the spectacular Dong Van Karst Plateau, a UNESCO Global Geopark with dramatic limestone formations.\r\nVisit Lung Cam Cultural Village to experience traditional H’Mong architecture and authentic local lifestyles.\r\nAdmire the panoramic views from Ma Pi Leng Pass, one of the most iconic mountain passes in Vietnam.\r\nEngage in hands-on experiences at local craft workshops, learning about weaving, embroidery, and ethnic artistry.\r\nEnjoy a comfortable and enriching trip with departure from Ho Chi Minh City, staying in a mix of hotels and homestays.',4,7150000,'Summer','2025-05-16 11:52:29');
/*!40000 ALTER TABLE `tours` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-05-21 18:03:28
