
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `animals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `animals` (
  `id` int NOT NULL AUTO_INCREMENT,
  `owner_id` int NOT NULL,
  `species_id` int NOT NULL,
  `identification_code` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `breed` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sex` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `birth_date` date DEFAULT NULL,
  `color` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `identification_code` (`identification_code`),
  KEY `fk_animals_owner` (`owner_id`),
  KEY `fk_animals_species` (`species_id`),
  CONSTRAINT `fk_animals_owner` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`),
  CONSTRAINT `fk_animals_species` FOREIGN KEY (`species_id`) REFERENCES `species` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `animals` WRITE;
/*!40000 ALTER TABLE `animals` DISABLE KEYS */;
INSERT INTO `animals` VALUES (1,1,1,'ANI-001','Toby','Labrador','Macho','2021-05-10','Dorado','','activo','2026-06-12 17:35:05',NULL),(2,2,2,'ANI-002','Luna','Criolla','Hembra','2022-02-15','Negro','','activo','2026-06-12 17:35:05',NULL),(3,1,3,'ANI-003','Rayo','Criollo','Macho','2019-11-20','Café','','activo','2026-06-12 17:35:05',NULL);
/*!40000 ALTER TABLE `animals` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Medicamentos','Farmacos y tratamientos','active','2026-06-12 17:35:05','2026-06-12 23:29:53'),(2,'Vacunas','Insumos de vacunación','active','2026-06-12 17:35:05','2026-06-12 23:29:48'),(3,'Insumos','Elementos generales de bodega','active','2026-06-12 17:35:05',NULL);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `inventory_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_movements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `movement_type` enum('input','output') COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL,
  `before_stock` int NOT NULL,
  `after_stock` int NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `reference` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_movements_product` (`product_id`),
  KEY `fk_movements_user` (`user_id`),
  CONSTRAINT `fk_movements_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `fk_movements_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `inventory_movements` WRITE;
/*!40000 ALTER TABLE `inventory_movements` DISABLE KEYS */;
INSERT INTO `inventory_movements` VALUES (1,1,3,'output',2,12,10,'Uso en tratamiento','TRX-001','2026-06-12 17:35:05'),(2,3,3,'input',50,150,200,'Ingreso de bodega','BOD-010','2026-06-12 17:35:05'),(3,1,NULL,'input',1,12,13,'si','aaa','2026-06-12 18:10:32');
/*!40000 ALTER TABLE `inventory_movements` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `observations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `observations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `animal_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `observation_date` date NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_observations_animal` (`animal_id`),
  KEY `fk_observations_user` (`user_id`),
  CONSTRAINT `fk_observations_animal` FOREIGN KEY (`animal_id`) REFERENCES `animals` (`id`),
  CONSTRAINT `fk_observations_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `observations` WRITE;
/*!40000 ALTER TABLE `observations` DISABLE KEYS */;
INSERT INTO `observations` VALUES (2,2,2,'2026-06-03','Presenta apetito normal y peso estable.','2026-06-12 17:35:05');
/*!40000 ALTER TABLE `observations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `owners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `owners` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document` (`document`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `owners` WRITE;
/*!40000 ALTER TABLE `owners` DISABLE KEYS */;
INSERT INTO `owners` VALUES (1,'María López','1002003001','3001112233','maria@example.com','Calle 12 # 34-56','2026-06-12 17:35:05'),(2,'Carlos Pérez','1002003002','3004445566','carlos@example.com','Carrera 45 # 67-89','2026-06-12 17:35:05');
/*!40000 ALTER TABLE `owners` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `animal_group` enum('domestic','wild','farm') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'domestic',
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `unit_cost` decimal(10,2) NOT NULL DEFAULT '0.00',
  `sale_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `current_stock` int NOT NULL DEFAULT '0',
  `stock_min` int NOT NULL DEFAULT '0',
  `stock_max` int NOT NULL DEFAULT '0',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `fk_products_categories` (`category_id`),
  CONSTRAINT `fk_products_categories` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,'domestic','MED-001','Antibiotico 500mg','Tratamiento antibacteriano',12000.00,18000.00,13,5,40,'active','2026-06-12 17:35:05','2026-06-12 23:23:26'),(2,2,'domestic','VAC-001','Vacuna Rabia','Aplicación anual',25000.00,38000.00,4,5,30,'active','2026-06-12 17:35:05','2026-06-12 23:23:35'),(3,3,'domestic','INS-001','Jeringa 5ml','Insumo descartable',500.00,1000.00,150,50,500,'active','2026-06-12 17:35:05','2026-06-12 17:50:26'),(4,1,'domestic','MED-004','Unguento Cicatrizante','Apoyo para heridas superficiales',7000.00,12000.00,14,5,40,'active','2026-06-12 17:50:26','2026-06-12 23:23:59'),(5,1,'domestic','MED-005','Antiparasitario Interno','Control de parasitos intestinales',9000.00,15000.00,16,6,40,'active','2026-06-12 17:50:26','2026-06-12 23:24:08'),(6,2,'domestic','VAC-004','Vacuna Polivalente Canina','Protección general para perros',28000.00,42000.00,7,4,30,'active','2026-06-12 17:50:26','2026-06-12 23:24:17'),(7,3,'domestic','INS-004','Suero Oral Veterinario','Apoyo en recuperación',6000.00,11000.00,25,10,60,'active','2026-06-12 17:50:26','2026-06-12 23:24:32'),(8,1,'domestic','DOM-001','Champu Dermatologico','Higiene para piel sensible en mascotas',14000.00,22000.00,22,8,50,'active','2026-06-12 17:56:24','2026-06-12 23:24:25'),(9,3,'domestic','DOM-002','Cepillo Deslanador','Mantenimiento del pelaje en perros y gatos',12000.00,19000.00,18,6,40,'active','2026-06-12 17:56:24',NULL),(10,3,'domestic','DOM-003','Arena Aglutinante','Higiene para gatos domesticos',10000.00,16000.00,40,15,80,'active','2026-06-12 17:56:24','2026-06-12 23:24:42'),(11,3,'domestic','DOM-004','Collar Antipulgas','Control externo de pulgas y garrapatas',26000.00,39000.00,16,5,35,'active','2026-06-12 17:56:24',NULL),(12,1,'domestic','DOM-005','Analgesico Oral Canino','Alivio de dolor moderado bajo supervisión veterinaria',18000.00,27000.00,14,5,30,'active','2026-06-12 17:56:24','2026-06-12 23:24:54'),(13,2,'domestic','DOM-006','Vacuna Pentavalente','Protección preventiva para cachorros',26000.00,40000.00,10,4,25,'active','2026-06-12 17:56:24','2026-06-12 23:28:22'),(14,3,'domestic','DOM-007','Snacks de entrenamiento','Premios de recompensa para obediencia',9000.00,15000.00,55,20,120,'active','2026-06-12 17:56:24',NULL),(15,3,'domestic','DOM-008','Correa reflectiva','Paseos nocturnos y control',17000.00,26000.00,19,6,45,'active','2026-06-12 17:56:24',NULL),(16,1,'domestic','DOM-009','Limpiador ótico','Aseo de oidos en perros y gatos',11000.00,17000.00,24,8,50,'active','2026-06-12 17:56:24','2026-06-12 23:28:06'),(17,3,'domestic','DOM-010','Pañales para mascotas','Apoyo en incontinencia o viajes',13000.00,20000.00,20,6,40,'active','2026-06-12 17:56:24','2026-06-12 23:28:32'),(18,1,'domestic','DOM-011','Suspensión antiparasitaria','Desparasitación interna de rutina',15000.00,23000.00,17,6,35,'active','2026-06-12 17:56:24','2026-06-12 23:27:40'),(19,3,'domestic','DOM-012','Bebedero portatil','Hidratación en salidas y viajes',8000.00,13000.00,30,10,70,'active','2026-06-12 17:56:24','2026-06-12 23:27:53'),(20,3,'domestic','DOM-013','Cama ortopedica','Descanso para mascotas convalecientes',45000.00,68000.00,8,3,15,'active','2026-06-12 17:56:24','2026-06-12 23:27:28'),(21,1,'domestic','DOM-014','Spray cicatrizante','Apoyo en heridas superficiales de mascotas',16000.00,24000.00,13,5,30,'active','2026-06-12 17:56:24',NULL),(22,3,'farm','FAR-001','Suplemento Mineral Bovino','Aporte nutricional para ganado',23000.00,34000.00,28,10,70,'active','2026-06-12 17:56:24',NULL),(23,1,'farm','FAR-002','Pomada antiseptica equina','Limpieza y recuperación de piel en caballos',19000.00,28000.00,12,4,25,'active','2026-06-12 17:56:24','2026-06-12 23:27:18'),(24,2,'farm','FAR-003','Vacuna Brucelosis','Prevencion sanitaria en bovinos',36000.00,54000.00,9,3,20,'active','2026-06-12 17:56:24','2026-06-12 23:27:07'),(25,3,'farm','FAR-004','Sales de rehidratacion bovina','Recuperación de electrolitos',20000.00,30000.00,21,8,50,'active','2026-06-12 17:56:24','2026-06-12 23:27:00'),(26,1,'farm','FAR-005','Antibiótico inyectable bovino','Tratamiento de infecciones del hato',31000.00,46000.00,15,5,30,'active','2026-06-12 17:56:24','2026-06-12 23:26:30'),(27,3,'farm','FAR-006','Jeringa automatica','Aplicación de medicamentos en granja',7000.00,11000.00,60,20,120,'active','2026-06-12 17:56:24','2026-06-12 23:26:49'),(28,2,'farm','FAR-007','Vacuna Clostridial','Prevención en bovinos y ovinos',34000.00,50000.00,11,4,25,'active','2026-06-12 17:56:24','2026-06-12 23:26:41'),(29,3,'farm','FAR-008','Cinta de marcaje','Identificación de animales de granja',3000.00,6000.00,95,30,200,'active','2026-06-12 17:56:24','2026-06-12 23:26:21'),(30,1,'farm','FAR-009','Antiinflamatorio equino','Control de dolor en caballos',29000.00,43000.00,10,4,20,'active','2026-06-12 17:56:24',NULL),(31,3,'farm','FAR-010','Guantes de ordeño','Higiene en sala de ordeño',6000.00,9000.00,75,25,150,'active','2026-06-12 17:56:24','2026-06-12 23:25:53'),(32,1,'farm','FAR-011','Antiparasitario ovino','Desparasitación para ovejas y cabras',24000.00,35000.00,20,7,40,'active','2026-06-12 17:56:24','2026-06-12 23:26:04'),(33,3,'farm','FAR-012','Rastrillo de limpieza','Limpieza de corrales',18000.00,26000.00,25,8,60,'active','2026-06-12 17:56:24',NULL),(34,2,'farm','FAR-013','Vacuna Leptospira','Prevención en ganado',33000.00,49000.00,14,5,30,'active','2026-06-12 17:56:24','2026-06-12 23:26:12'),(35,1,'wild','WLD-001','Antiseptico de campo','Limpieza externa en fauna silvestre',19000.00,29000.00,10,4,25,'active','2026-06-12 17:56:24','2026-06-12 23:25:38'),(36,1,'wild','WLD-002','Suero rehidratante fauna','Apoyo en deshidratación de fauna',16000.00,24000.00,12,4,30,'active','2026-06-12 17:56:24','2026-06-12 23:25:30'),(37,3,'wild','WLD-003','Caja de transporte segura','Traslado temporal de fauna',65000.00,92000.00,5,2,10,'active','2026-06-12 17:56:24',NULL),(38,3,'wild','WLD-004','Manta termica','Control termico en rescate',24000.00,36000.00,7,3,15,'active','2026-06-12 17:56:24','2026-06-12 23:25:22'),(39,1,'wild','WLD-005','Antibiotico de amplio espectro','Apoyo clinico en rescate veterinario',27000.00,41000.00,8,3,20,'active','2026-06-12 17:56:24','2026-06-12 23:25:12'),(40,3,'wild','WLD-006','Guantes de captura','Manipulación segura',8000.00,12000.00,18,6,40,'active','2026-06-12 17:56:24','2026-06-12 23:25:02'),(41,2,'wild','WLD-007','Vacuna canina silvestre','Prevención en perros de trabajo y rescate',33000.00,49000.00,6,2,15,'active','2026-06-12 17:56:24','2026-06-12 23:23:12'),(42,3,'wild','WLD-008','Red de contención','Captura segura de animales',55000.00,78000.00,4,1,10,'active','2026-06-12 17:56:24','2026-06-12 23:23:03'),(43,1,'wild','WLD-009','Unguento cicatrizante fauna','Heridas superficiales',12000.00,18000.00,9,3,20,'active','2026-06-12 17:56:24','2026-06-12 23:22:55'),(44,3,'wild','WLD-010','Bascula portatil','Pesaje en campo',95000.00,130000.00,3,1,8,'active','2026-06-12 17:56:24','2026-06-12 23:22:47'),(45,2,'wild','WLD-011','Vacuna antirrabica de campo','Prevención para fauna en control',37000.00,54000.00,5,2,12,'active','2026-06-12 17:56:24','2026-06-12 23:22:39'),(46,3,'wild','WLD-012','Linterna de inspección','Revisión nocturna o de campo',14000.00,22000.00,20,8,50,'active','2026-06-12 17:56:24','2026-06-12 23:22:29'),(47,1,'wild','WLD-013','Suero oral fauna pequeña','Recuperación de liquidos',13000.00,18000.00,11,4,25,'active','2026-06-12 17:56:24','2026-06-12 23:22:19');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Administrador','Acceso total al sistema','2026-06-12 18:22:57'),(2,'Veterinario','Gestión clínica de animales','2026-06-12 17:35:05'),(3,'Bodega','Control de inventario','2026-06-12 17:35:05');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `species`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `species` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `species` WRITE;
/*!40000 ALTER TABLE `species` DISABLE KEYS */;
INSERT INTO `species` VALUES (1,'Perro','2026-06-12 17:35:05'),(2,'Gato','2026-06-12 17:35:05'),(3,'Caballo','2026-06-12 17:35:05');
/*!40000 ALTER TABLE `species` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `stock_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_alerts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `alert_type` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_resolved` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `resolved_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_alerts_product` (`product_id`),
  CONSTRAINT `fk_alerts_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `stock_alerts` WRITE;
/*!40000 ALTER TABLE `stock_alerts` DISABLE KEYS */;
INSERT INTO `stock_alerts` VALUES (1,2,'low_stock','Stock mínimo alcanzado para Vacuna Rabia',0,'2026-06-12 17:35:05',NULL);
/*!40000 ALTER TABLE `stock_alerts` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `treatments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `treatments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `animal_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `treatment_date` date NOT NULL,
  `diagnosis` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `procedure_desc` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `medication` text COLLATE utf8mb4_unicode_ci,
  `dosage` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `next_control_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_treatments_animal` (`animal_id`),
  KEY `fk_treatments_user` (`user_id`),
  CONSTRAINT `fk_treatments_animal` FOREIGN KEY (`animal_id`) REFERENCES `animals` (`id`),
  CONSTRAINT `fk_treatments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `treatments` WRITE;
/*!40000 ALTER TABLE `treatments` DISABLE KEYS */;
/*!40000 ALTER TABLE `treatments` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_id` int NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_users_roles` (`role_id`),
  CONSTRAINT `fk_users_roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,'Administrador General','admin@laudi.test','$2y$12$JSuxryQmPJMp0cfn9CMNdOP/lPThzRDwH0vaRlU14WA7ips0KWH7e','active','2026-09-13 18:29:47','2026-06-12 18:22:57','2026-09-13 23:29:47'),(2,2,'Juan Gomez','juan@laudi.test','$2y$12$JSuxryQmPJMp0cfn9CMNdOP/lPThzRDwH0vaRlU14WA7ips0KWH7e','active',NULL,'2026-06-12 17:35:05','2026-06-12 18:19:24'),(3,3,'Sebastian Sierra','sierra@laudi.test','$2y$12$WZO1V1i3EzZojA4r9Lff3uyCIbbzWeNCrgfvTQ0oF8MpOtB3Yq.BG','active','2026-06-12 13:20:59','2026-06-12 17:35:05','2026-06-12 18:20:59');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `vaccines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vaccines` (
  `id` int NOT NULL AUTO_INCREMENT,
  `animal_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `vaccine_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `application_date` date NOT NULL,
  `next_due_date` date DEFAULT NULL,
  `batch` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_vaccines_animal` (`animal_id`),
  KEY `fk_vaccines_user` (`user_id`),
  CONSTRAINT `fk_vaccines_animal` FOREIGN KEY (`animal_id`) REFERENCES `animals` (`id`),
  CONSTRAINT `fk_vaccines_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `vaccines` WRITE;
/*!40000 ALTER TABLE `vaccines` DISABLE KEYS */;
INSERT INTO `vaccines` VALUES (2,1,2,'Moquillo','2026-06-05','2027-06-05','MQ-2026-02','Esquema completo','2026-06-12 17:35:05');
/*!40000 ALTER TABLE `vaccines` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `vw_medical_history`;
/*!50001 DROP VIEW IF EXISTS `vw_medical_history`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vw_medical_history` AS SELECT 
 1 AS `animal_id`,
 1 AS `animal_name`,
 1 AS `record_type`,
 1 AS `record_id`,
 1 AS `record_date`,
 1 AS `title`,
 1 AS `details`,
 1 AS `extra_details`*/;
SET character_set_client = @saved_cs_client;
/*!50001 DROP VIEW IF EXISTS `vw_medical_history`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = cp850 */;
/*!50001 SET character_set_results     = cp850 */;
/*!50001 SET collation_connection      = cp850_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 SQL SECURITY DEFINER */
/*!50001 VIEW `vw_medical_history` AS select `a`.`id` AS `animal_id`,`a`.`name` AS `animal_name`,'treatment' AS `record_type`,`t`.`id` AS `record_id`,`t`.`treatment_date` AS `record_date`,`t`.`diagnosis` AS `title`,`t`.`procedure_desc` AS `details`,`t`.`notes` AS `extra_details` from (`animals` `a` join `treatments` `t` on((`t`.`animal_id` = `a`.`id`))) union all select `a`.`id` AS `animal_id`,`a`.`name` AS `animal_name`,'vaccine' AS `record_type`,`v`.`id` AS `record_id`,`v`.`application_date` AS `record_date`,`v`.`vaccine_name` AS `title`,`v`.`notes` AS `details`,`v`.`batch` AS `extra_details` from (`animals` `a` join `vaccines` `v` on((`v`.`animal_id` = `a`.`id`))) union all select `a`.`id` AS `animal_id`,`a`.`name` AS `animal_name`,'observation' AS `record_type`,`o`.`id` AS `record_id`,`o`.`observation_date` AS `record_date`,'Observación clínica' AS `title`,`o`.`description` AS `details`,NULL AS `extra_details` from (`animals` `a` join `observations` `o` on((`o`.`animal_id` = `a`.`id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `action` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_id` int DEFAULT NULL,
  `details` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_audit_user` (`user_id`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

