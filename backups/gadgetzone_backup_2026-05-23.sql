-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: gadgetzone
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
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(10) NOT NULL DEFAULT '?',
  `order_num` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'فيتامينات','veterinary-treatments','💊',1),(2,'Supplements & Nutrition','supplements-nutrition','🌿',2),(3,'Horseshoes','horseshoes','🧲',3),(4,'Horse & Rider Equipment','horse-rider-equipment','🐎',4),(5,'Veterinary Consumables','veterinary-consumables','🩺',5),(6,'Horse Feed & Fodder','horse-feed-fodder','🌾',6);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,1,1,149999.00),(2,2,2,1,28.50),(3,2,7,1,24.50),(4,3,1,1,45.00),(5,4,1,1,45.00),(6,4,4,1,22.00),(7,5,3,1,18.75),(8,5,9,1,65.00),(9,6,13,1,15.00),(10,7,1,1,45.00),(15,10,1,1,45.00),(18,12,18,1,22.00),(19,13,2,1,28.50),(22,15,18,1,22.00),(23,16,18,1,22.00),(26,18,1,1,45.00);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `order_number` varchar(60) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `payment_method` varchar(40) NOT NULL,
  `shipping_address` text NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,1,'GZ-6A0E1228DC66D',149999.00,'delivered','cod','Super Admin, Al Hajj Hassan At Tunesi, Amman — Phone: 0781102072','','2026-05-20 19:57:28'),(2,1,'EVS-6A0F6FECBAC2B',203.00,'delivered','visa','Super Admin, Amman, Amman — Phone: (+962)781102072','','2026-05-21 20:49:48'),(3,1,'EVS-6A10A4326672D',195.00,'cancelled','cod','Super Admin, Amman, Amman — Phone: (+962)781102072','','2026-05-22 18:45:06'),(4,1,'EVS-6A10A563B51A5',217.00,'pending','cod','Super Admin, amman, amman — Phone: 0781102072','','2026-05-22 18:50:11'),(5,1,'EVS-6A10A93DCD3E7',233.75,'delivered','cod','Diya Dibsi, Al Hajj Hassan At Tunesi, Amman — Phone: 0781102072','','2026-05-22 19:06:37'),(6,1,'EVS-6A10ADEBBD94C',165.00,'delivered','cod','Super Admin, Amman, Amman — Phone: (+962)781102072','','2026-05-22 19:26:35'),(7,1,'EVS-6A10AEC6B76B4',195.00,'pending','cod','Diya Dibsi, Al Hajj Hassan At Tunesi, Amman — Phone: 0781102072','','2026-05-22 19:30:14'),(10,4,'EVS-6A10C454DD077',195.00,'pending','cod','Deyaa Aldebsi, amman, amman — Phone: 0781102072','','2026-05-22 21:02:12'),(12,4,'EVS-6A10C4C4BC759',172.00,'pending','cod','Deyaa Aldebsi, Al Hajj Hassan At Tunesi, Amman — Phone: 0781102072','','2026-05-22 21:04:04'),(13,4,'EVS-6A10C4D89390A',178.50,'pending','visa','Deyaa Aldebsi, Al Hajj Hassan At Tunesi, Amman — Phone: 0781102072','','2026-05-22 21:04:24'),(15,4,'EVS-6A10E1F54F014',172.00,'pending','cod','Deyaa Aldebsi, Al Hajj Hassan At Tunesi, Amman — Phone: 0781102072','','2026-05-22 23:08:37'),(16,4,'EVS-6A10E392E05E5',172.00,'pending','cod','Deyaa Aldebsi, Al Hajj Hassan At Tunesi, Amman — Phone: 0781102072','','2026-05-22 23:15:30'),(18,4,'EVS-6A10E51638268',195.00,'pending','cod','Deyaa Aldebsi, Al Hajj Hassan At Tunesi, Amman — Phone: 0781102072','','2026-05-22 23:21:58');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `subcategory_id` int(11) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `old_price` decimal(10,2) DEFAULT NULL,
  `image_url` varchar(500) NOT NULL DEFAULT '',
  `badge` enum('NEW','HOT','SALE','') DEFAULT '',
  `stock` int(11) NOT NULL DEFAULT 100,
  `featured` tinyint(1) DEFAULT 0,
  `limited_time_offer` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `fk_subcat` (`subcategory_id`),
  CONSTRAINT `fk_subcat` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,1,'فيتامين','penicillin-g-injectable','Broad-spectrum antibiotic for bacterial infections in horses. 100mL vial.',45.00,52.00,'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=600&q=80','HOT',0,1,1,'2026-05-21 18:12:41'),(2,1,2,'Phenylbutazone Paste','phenylbutazone-paste','Anti-inflammatory and analgesic paste for musculoskeletal pain in horses.',28.50,NULL,'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=600&q=80','NEW',80,1,0,'2026-05-21 18:12:41'),(3,1,4,'Ivermectin Dewormer Syringe','ivermectin-dewormer-syringe','Effective dewormer for strongyles, pinworms, and bots. Easy-dose syringe.',18.75,22.00,'https://images.unsplash.com/photo-1631549916768-4119b2e5f926?w=600&q=80','SALE',120,1,0,'2026-05-21 18:12:41'),(4,1,3,'Praziquantel Tapeworm Tabs','praziquantel-tapeworm-tabs','Highly effective against equine tapeworms. Palatable tablet form.',22.00,NULL,'https://images.unsplash.com/photo-1587854692152-cbe660dbde88?w=600&q=80','',60,0,0,'2026-05-21 18:12:41'),(5,2,7,'BioTin Hoof Supplement','biotin-hoof-supplement','High-potency biotin formula for stronger, healthier hooves. 3kg bucket.',55.00,65.00,'https://images.unsplash.com/photo-1535914254981-b5012eebbd15?w=600&q=80','HOT',40,1,0,'2026-05-21 18:12:41'),(6,2,12,'Glucosamine Joint Support','glucosamine-joint-support','Premium joint supplement with glucosamine, chondroitin & MSM for horses.',72.00,NULL,'https://images.unsplash.com/photo-1550831107-1553da8c8464?w=600&q=80','NEW',35,1,0,'2026-05-21 18:12:41'),(7,2,9,'Electrolyte & Muscle Paste','electrolyte-muscle-paste','Replenishes electrolytes and supports muscle recovery after intense exercise.',24.50,29.00,'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=600&q=80','SALE',90,0,0,'2026-05-21 18:12:41'),(8,3,14,'Steel Horseshoes Set (4 pcs)','steel-horseshoes-set','Standard forged steel horseshoes. Available in sizes 0-6. Set of 4.',38.00,NULL,'https://images.unsplash.com/photo-1553799775-b81cabe299c9?w=600&q=80','',200,0,0,'2026-05-21 18:12:41'),(9,3,16,'Aluminum Therapeutic Shoe','aluminum-therapeutic-shoe','Lightweight aluminum shoe designed for horses with laminitis or navicular disease.',65.00,75.00,'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&q=80','NEW',30,1,0,'2026-05-21 18:12:41'),(10,3,17,'Professional Farrier Hammer','professional-farrier-hammer','Balanced steel farrier hammer with hickory handle. Essential for horseshoeing.',42.00,NULL,'https://images.unsplash.com/photo-1504222490345-c075b626c559?w=600&q=80','',25,0,0,'2026-05-21 18:12:41'),(11,4,18,'Professional Riding Helmet','professional-riding-helmet','CE certified equestrian helmet with ventilation system. Adjustable fit.',85.00,99.00,'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=600&q=80','SALE',45,1,0,'2026-05-21 18:12:41'),(12,4,20,'Premium Horse Halter & Leadrope','premium-horse-halter-leadrope','Durable nylon halter with matching 3m lead rope. Brass fittings.',2000.00,3000.00,'/GadgetZone/uploads/products/product_6a0f5ec65f1f99.92353805.jpeg','NEW',70,0,0,'2026-05-21 18:12:41'),(13,5,NULL,'Sterile Disposable Syringes 10mL','sterile-syringes-10ml','Box of 100 sterile single-use syringes with Luer-lock tip.',15.00,NULL,'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=600&q=80','',500,0,1,'2026-05-21 18:12:41'),(14,5,NULL,'Veterinary Exam Gloves (Box/100)','vet-exam-gloves-box','Powder-free nitrile gloves, ideal for equine examinations. Box of 100.',12.00,15.00,'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=600&q=80','SALE',300,0,0,'2026-05-21 18:12:41'),(15,6,NULL,'Premium Alfalfa Hay (25kg)','premium-alfalfa-hay-25kg','High-quality sun-dried alfalfa hay, rich in protein and calcium.',18.00,NULL,'https://images.unsplash.com/photo-1500595046743-cd271d694d30?w=600&q=80','',150,0,0,'2026-05-21 18:12:41'),(16,6,NULL,'Racehorse Performance Pellets','racehorse-performance-pellets','High-energy performance feed for racehorses and sport horses. 30kg bag.',48.00,55.00,'https://images.unsplash.com/photo-1500595046743-cd271d694d30?w=600&q=80','HOT',60,1,0,'2026-05-21 18:12:41'),(18,2,7,'تجريبي','-','',22.00,222.00,'/GadgetZone/uploads/products/product_6a10bb4e94a580.80878640.jpeg','NEW',100,0,0,'2026-05-22 20:23:44');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'active_currency','JOD','2026-05-20 19:51:59'),(3,'stripe_publishable_key','','2026-05-20 19:44:05'),(4,'stripe_secret_key','','2026-05-20 19:44:05'),(5,'stripe_webhook_secret','','2026-05-20 19:44:05'),(17,'google_client_id','1073045990424-lneuit7b0f2em5su0j5mojphc6ind019.apps.googleusercontent.com','2026-05-22 20:45:16'),(51,'notification_email','daldebsi@gmail.com','2026-05-22 23:05:40'),(52,'sender_email','daldebsi@gmail.com','2026-05-22 23:05:40'),(53,'smtp_host','smtp.gmail.com','2026-05-22 23:05:40'),(54,'smtp_port','587','2026-05-22 23:05:40'),(55,'smtp_user','daldebsi@gmail.com','2026-05-22 23:05:40'),(56,'smtp_secure','tls','2026-05-22 23:05:40'),(67,'smtp_pass','jqvc yrtf xywd fumy','2026-05-22 23:15:17');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subcategories`
--

DROP TABLE IF EXISTS `subcategories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subcategories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(20) DEFAULT '?',
  `order_num` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `subcategories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subcategories`
--

LOCK TABLES `subcategories` WRITE;
/*!40000 ALTER TABLE `subcategories` DISABLE KEYS */;
INSERT INTO `subcategories` VALUES (1,1,'Antibiotics','antibiotics','💊',1,'2026-05-21 18:12:41'),(2,1,'Painkillers','painkillers','💉',2,'2026-05-21 18:12:41'),(3,1,'Antiparasitics','antiparasitics','🔬',3,'2026-05-21 18:12:41'),(4,1,'Deworming Syrups','deworming-syrups','🧪',4,'2026-05-21 18:12:41'),(5,1,'Supplements & Vitamins','supplements-vitamins','🌡️',5,'2026-05-21 18:12:41'),(6,1,'Insecticides','insecticides','🛡️',6,'2026-05-21 18:12:41'),(7,2,'Hoof Care','hoof-care','🐴',1,'2026-05-21 18:12:41'),(8,2,'Digestive System Care','digestive-system-care','🌿',2,'2026-05-21 18:12:41'),(9,2,'Muscle Care','muscle-care','💪',3,'2026-05-21 18:12:41'),(10,2,'Postpartum & Foaling Care','postpartum-foaling-care','🐣',4,'2026-05-21 18:12:41'),(11,2,'Respiratory System Care','respiratory-system-care','🫁',5,'2026-05-21 18:12:41'),(12,2,'Joint & Mobility Care','joint-mobility-care','🦴',6,'2026-05-21 18:12:41'),(13,2,'Bone & Joint Care','bone-joint-care','🦷',7,'2026-05-21 18:12:41'),(14,3,'Standard Shoes','standard-shoes','🧲',1,'2026-05-21 18:12:41'),(15,3,'Nails','nails','📌',2,'2026-05-21 18:12:41'),(16,3,'Therapeutic Shoes','therapeutic-shoes','⚕️',3,'2026-05-21 18:12:41'),(17,3,'Horseshoeing Tools','horseshoeing-tools','🔨',4,'2026-05-21 18:12:41'),(18,4,'Rider Accessories','rider-accessories','🏇',1,'2026-05-21 18:12:41'),(19,4,'Training Equipment','training-equipment','🎯',2,'2026-05-21 18:12:41'),(20,4,'Stable Supplies','stable-supplies','🏠',3,'2026-05-21 18:12:41');
/*!40000 ALTER TABLE `subcategories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(80) NOT NULL,
  `last_name` varchar(80) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expires` datetime DEFAULT NULL,
  `role` enum('member','admin','super_admin') DEFAULT 'member',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `google_id` (`google_id`),
  UNIQUE KEY `reset_token` (`reset_token`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Super','Admin','admin@gadgetzone.com','$2y$10$IHDQwagf1JxAncjpyZj5oefo9utzJ7AQG0bez9kMXj2aEexbnIFBa',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'super_admin','2026-05-20 19:38:14'),(2,'diya','Dibsi','daldebsi@gmail.comd','$2y$10$0jyMX29E3MRplvIh.2pbBupV634FBY9lRbQh14YlKCSa5rfIG3cna',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'member','2026-05-21 19:53:16'),(3,'Ahmad','Al-Faris','ahmad@vet.com','$2y$10$i61YHygOIknUL88H4MMcnOOYJttq2MdkOBmnKG4WBtJI0YVZgJZrC',NULL,NULL,NULL,NULL,'google_id_ahmad_112233',NULL,NULL,'member','2026-05-22 20:25:12'),(4,'Deyaa','Aldebsi','daldebsi@gmail.com','$2y$10$9kjz7cLh8/Itcvgw5S9wjejIWU2yP4FWpRd5nz.c7A4rqn/f/wt52',NULL,NULL,NULL,NULL,'115589778196640299669',NULL,NULL,'super_admin','2026-05-22 20:53:45');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-23 14:47:57
