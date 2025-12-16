mysqldump: [Warning] Using a password on the command line interface can be insecure.
-- MySQL dump 10.13  Distrib 8.0.42, for Win64 (x86_64)
--
-- Host: localhost    Database: witms_db
-- ------------------------------------------------------
-- Server version	8.0.42

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

--
-- Table structure for table `accounts_payable`
--

DROP TABLE IF EXISTS `accounts_payable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accounts_payable` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `supplier_id` int unsigned NOT NULL,
  `purchase_order_id` int unsigned DEFAULT NULL COMMENT 'Related purchase order',
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `paid_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `balance` decimal(15,2) NOT NULL,
  `status` enum('Pending','Partially Paid','Paid','Overdue','Cancelled') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Pending',
  `approved_by` int unsigned DEFAULT NULL,
  `approved_date` datetime DEFAULT NULL,
  `notes` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `supplier_id` (`supplier_id`),
  KEY `purchase_order_id` (`purchase_order_id`),
  KEY `approved_by` (`approved_by`),
  CONSTRAINT `accounts_payable_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `accounts_payable_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `accounts_payable_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounts_payable`
--

LOCK TABLES `accounts_payable` WRITE;
/*!40000 ALTER TABLE `accounts_payable` DISABLE KEYS */;
/*!40000 ALTER TABLE `accounts_payable` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accounts_receivable`
--

DROP TABLE IF EXISTS `accounts_receivable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accounts_receivable` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `client_id` int unsigned NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `received_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `balance` decimal(15,2) NOT NULL,
  `status` enum('Pending','Partially Paid','Paid','Overdue','Cancelled') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Pending',
  `issued_by` int unsigned NOT NULL,
  `notes` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `client_id` (`client_id`),
  KEY `issued_by` (`issued_by`),
  CONSTRAINT `accounts_receivable_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `accounts_receivable_issued_by_foreign` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounts_receivable`
--

LOCK TABLES `accounts_receivable` WRITE;
/*!40000 ALTER TABLE `accounts_receivable` DISABLE KEYS */;
/*!40000 ALTER TABLE `accounts_receivable` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Action performed (Create, Update, Delete, etc.)',
  `table_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Table affected',
  `record_id` int unsigned DEFAULT NULL COMMENT 'ID of affected record',
  `old_values` text COLLATE utf8mb4_general_ci COMMENT 'JSON of old values',
  `new_values` text COLLATE utf8mb4_general_ci COMMENT 'JSON of new values',
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `table_name` (`table_name`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `contact_person` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_general_ci,
  `payment_terms` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tax_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `warehouse_id` int unsigned DEFAULT NULL COMMENT 'Foreign key to warehouses table (null for Central Office)',
  `department_head` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `department_head_user_id` int unsigned DEFAULT NULL COMMENT 'Foreign key to users table',
  `contact_email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contact_phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `warehouse_id` (`warehouse_id`),
  KEY `departments_department_head_user_id_foreign` (`department_head_user_id`),
  CONSTRAINT `departments_department_head_user_id_foreign` FOREIGN KEY (`department_head_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `departments_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,'Executive','Top management and executive decision makers',NULL,NULL,NULL,'executive@webuild.com','+63-2-8888-1000',1,'2025-12-17 00:38:14','2025-12-17 00:38:14'),(2,'Finance','Accounting, financial management, AP/AR operations',NULL,NULL,NULL,'finance@webuild.com','+63-2-8888-1001',1,'2025-12-17 00:38:14','2025-12-17 00:38:14'),(3,'Procurement','Purchasing, supplier management, and procurement operations',NULL,NULL,NULL,'procurement@webuild.com','+63-2-8888-1002',1,'2025-12-17 00:38:14','2025-12-17 00:38:14'),(4,'Information Technology','IT infrastructure, system administration, and technical support',NULL,NULL,NULL,'it@webuild.com','+63-2-8888-1003',1,'2025-12-17 00:38:14','2025-12-17 00:38:14'),(5,'Quality Control','Inventory auditing and quality assurance',NULL,NULL,NULL,'qc@webuild.com','+63-2-8888-1004',1,'2025-12-17 00:38:14','2025-12-17 00:38:14'),(6,'Warehouse Operations','Warehouse operations and inventory management',NULL,NULL,NULL,'warehouse@webuild.com','+63-2-8888-2000',1,'2025-12-17 00:38:14','2025-12-17 00:38:14');
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `material_id` int unsigned NOT NULL,
  `warehouse_id` int unsigned NOT NULL,
  `batch_number` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Batch/lot number for tracking',
  `quantity` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Current quantity in stock',
  `reserved_quantity` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Quantity reserved for projects',
  `available_quantity` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Available quantity (quantity - reserved)',
  `location_in_warehouse` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Specific location (e.g., Aisle A, Rack 5)',
  `expiration_date` date DEFAULT NULL COMMENT 'Expiration date for perishable items',
  `last_counted_date` date DEFAULT NULL COMMENT 'Last physical count date',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_warehouse_id_foreign` (`warehouse_id`),
  KEY `material_id_warehouse_id` (`material_id`,`warehouse_id`),
  KEY `batch_number` (`batch_number`),
  CONSTRAINT `inventory_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `inventory_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory`
--

LOCK TABLES `inventory` WRITE;
/*!40000 ALTER TABLE `inventory` DISABLE KEYS */;
INSERT INTO `inventory` VALUES (1,1,1,'BATCH-20251217-916',194.00,41.00,153.00,'Aisle A, Rack 8',NULL,'2025-12-17','2025-12-17 00:38:15','2025-12-17 00:38:15'),(2,1,2,'BATCH-20251217-089',97.00,2.00,95.00,'Aisle C, Rack 5',NULL,'2025-12-17','2025-12-17 00:38:15','2025-12-17 00:38:15'),(3,1,3,'BATCH-20251217-894',240.00,40.00,200.00,'Aisle F, Rack 9',NULL,'2025-12-17','2025-12-17 00:38:15','2025-12-17 00:38:15'),(4,2,1,'BATCH-20251217-888',388.00,27.00,361.00,'Aisle B, Rack 10',NULL,'2025-12-17','2025-12-17 00:38:15','2025-12-17 00:38:15'),(5,2,2,'BATCH-20251217-205',112.00,31.00,81.00,'Aisle B, Rack 9',NULL,'2025-12-17','2025-12-17 00:38:15','2025-12-17 00:38:15'),(6,2,3,'BATCH-20251217-688',337.00,40.00,297.00,'Aisle A, Rack 8',NULL,'2025-12-17','2025-12-17 00:38:15','2025-12-17 00:38:15');
/*!40000 ALTER TABLE `inventory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_audit_details`
--

DROP TABLE IF EXISTS `inventory_audit_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_audit_details` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `audit_id` int unsigned NOT NULL,
  `material_id` int unsigned NOT NULL,
  `batch_number` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `system_quantity` decimal(15,2) NOT NULL COMMENT 'Quantity per system records',
  `physical_quantity` decimal(15,2) NOT NULL COMMENT 'Actual counted quantity',
  `variance` decimal(15,2) NOT NULL COMMENT 'Difference between physical and system',
  `variance_value` decimal(15,2) DEFAULT NULL COMMENT 'Monetary value of variance',
  `remarks` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_id` (`audit_id`),
  KEY `material_id` (`material_id`),
  CONSTRAINT `inventory_audit_details_audit_id_foreign` FOREIGN KEY (`audit_id`) REFERENCES `inventory_audits` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `inventory_audit_details_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_audit_details`
--

LOCK TABLES `inventory_audit_details` WRITE;
/*!40000 ALTER TABLE `inventory_audit_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_audit_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_audits`
--

DROP TABLE IF EXISTS `inventory_audits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_audits` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `audit_number` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `warehouse_id` int unsigned NOT NULL,
  `audit_date` date NOT NULL,
  `audited_by` int unsigned NOT NULL,
  `status` enum('In Progress','Completed','Reviewed') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'In Progress',
  `notes` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `audit_number` (`audit_number`),
  KEY `warehouse_id` (`warehouse_id`),
  KEY `audited_by` (`audited_by`),
  CONSTRAINT `inventory_audits_audited_by_foreign` FOREIGN KEY (`audited_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `inventory_audits_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_audits`
--

LOCK TABLES `inventory_audits` WRITE;
/*!40000 ALTER TABLE `inventory_audits` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_audits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `material_categories`
--

DROP TABLE IF EXISTS `material_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `material_categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Category name',
  `code` varchar(20) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Category code',
  `description` text COLLATE utf8mb4_general_ci COMMENT 'Category description',
  `parent_id` int unsigned DEFAULT NULL COMMENT 'Parent category for hierarchical structure',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `material_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `material_categories` (`id`) ON DELETE CASCADE ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `material_categories`
--

LOCK TABLES `material_categories` WRITE;
/*!40000 ALTER TABLE `material_categories` DISABLE KEYS */;
INSERT INTO `material_categories` VALUES (1,'Steel','STEEL','Steel products and rebars',NULL,1,'2025-12-17 00:38:15','2025-12-17 00:38:15'),(2,'Cement','CEM','Cement and concrete products',NULL,1,'2025-12-17 00:38:15','2025-12-17 00:38:15'),(3,'Aggregates','AGG','Sand, gravel, and aggregates',NULL,1,'2025-12-17 00:38:15','2025-12-17 00:38:15'),(4,'Wood Products','WOOD','Plywood and lumber',NULL,1,'2025-12-17 00:38:15','2025-12-17 00:38:15'),(5,'Paints','PAINT','Paints and coatings',NULL,1,'2025-12-17 00:38:15','2025-12-17 00:38:15'),(6,'Electrical','ELEC','Electrical supplies',NULL,1,'2025-12-17 00:38:15','2025-12-17 00:38:15'),(7,'Plumbing','PLUMB','Plumbing supplies',NULL,1,'2025-12-17 00:38:15','2025-12-17 00:38:15'),(8,'Hardware','HARD','Hardware and tools',NULL,1,'2025-12-17 00:38:15','2025-12-17 00:38:15');
/*!40000 ALTER TABLE `material_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `materials`
--

DROP TABLE IF EXISTS `materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `materials` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Material name',
  `code` varchar(50) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Unique material code',
  `qrcode` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'QR code',
  `category_id` int unsigned NOT NULL COMMENT 'Foreign key to material_categories',
  `unit_id` int unsigned NOT NULL COMMENT 'Foreign key to units_of_measure',
  `description` text COLLATE utf8mb4_general_ci COMMENT 'Material description',
  `reorder_level` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Minimum stock level before reorder',
  `reorder_quantity` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Quantity to order when restocking',
  `unit_cost` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Standard unit cost',
  `is_perishable` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Whether material has expiration date',
  `shelf_life_days` int DEFAULT NULL COMMENT 'Shelf life in days if perishable',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `qrcode` (`qrcode`),
  KEY `category_id` (`category_id`),
  KEY `unit_id` (`unit_id`),
  CONSTRAINT `materials_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `material_categories` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `materials_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units_of_measure` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `materials`
--

LOCK TABLES `materials` WRITE;
/*!40000 ALTER TABLE `materials` DISABLE KEYS */;
INSERT INTO `materials` VALUES (1,'Steel Rebar 12mm','STEEL-12MM-001',NULL,1,1,'High-grade steel rebar for construction',100.00,500.00,250.50,0,NULL,1,'2025-12-17 00:38:15','2025-12-17 00:38:15'),(2,'Cement Portland Type 1','CEM-PT1-001',NULL,2,2,'Portland Type 1 Cement - 40kg bags',200.00,1000.00,245.00,0,NULL,1,'2025-12-17 00:38:15','2025-12-17 00:38:15');
/*!40000 ALTER TABLE `materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `class` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `group` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `namespace` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `time` int NOT NULL,
  `batch` int unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2025-11-09-140000','App\\Database\\Migrations\\CreateRolesTable','default','App',1765903022,1),(2,'2025-11-09-140050','App\\Database\\Migrations\\CreateWarehouseLocationsTable','default','App',1765903022,1),(3,'2025-11-09-149980','App\\Database\\Migrations\\CreateWarehousesTable','default','App',1765903022,1),(4,'2025-11-09-149990','App\\Database\\Migrations\\CreateDepartmentsTable','default','App',1765903023,1),(5,'2025-11-09-150001','App\\Database\\Migrations\\CreateUsersTable','default','App',1765903023,1),(6,'2025-11-09-150040','App\\Database\\Migrations\\AddForeignKeyToWarehouseTable','default','App',1765903023,1),(7,'2025-11-09-152001','App\\Database\\Migrations\\CreateMaterialCategoriesTable','default','App',1765903023,1),(8,'2025-11-09-152011','App\\Database\\Migrations\\CreateUnitsOfMeasureTable','default','App',1765903023,1),(9,'2025-11-09-152018','App\\Database\\Migrations\\CreateMaterialsTable','default','App',1765903024,1),(10,'2025-11-09-152024','App\\Database\\Migrations\\CreateSuppliersTable','default','App',1765903024,1),(11,'2025-11-09-152030','App\\Database\\Migrations\\CreateInventoryTable','default','App',1765903024,1),(12,'2025-11-09-152036','App\\Database\\Migrations\\CreatePurchaseOrdersTable','default','App',1765903024,1),(13,'2025-11-09-152041','App\\Database\\Migrations\\CreatePurchaseOrderItemsTable','default','App',1765903024,1),(14,'2025-11-09-152047','App\\Database\\Migrations\\CreateStockMovementsTable','default','App',1765903024,1),(15,'2025-11-09-152052','App\\Database\\Migrations\\CreateInventoryAuditsTable','default','App',1765903024,1),(16,'2025-11-09-152057','App\\Database\\Migrations\\CreateInventoryAuditDetailsTable','default','App',1765903024,1),(17,'2025-11-09-152102','App\\Database\\Migrations\\CreateAccountsPayableTable','default','App',1765903025,1),(18,'2025-11-09-152108','App\\Database\\Migrations\\CreatePaymentsTable','default','App',1765903025,1),(19,'2025-11-09-152115','App\\Database\\Migrations\\CreateClientsTable','default','App',1765903025,1),(20,'2025-11-09-152120','App\\Database\\Migrations\\CreateAccountsReceivableTable','default','App',1765903025,1),(21,'2025-11-09-152126','App\\Database\\Migrations\\CreateReceiptsTable','default','App',1765903025,1),(22,'2025-11-09-152133','App\\Database\\Migrations\\CreateAuditLogsTable','default','App',1765903025,1),(23,'2025-11-09-152140','App\\Database\\Migrations\\CreateStockAlertsTable','default','App',1765903025,1),(24,'2025-11-09-152150','App\\Database\\Migrations\\CreateWorkAssignmentsTable','default','App',1765903025,1),(25,'2025-11-10-100000','App\\Database\\Migrations\\CreateUserWarehouseAssignmentsTable','default','App',1765903026,1),(26,'2025-12-17-030000','App\\Database\\Migrations\\AddDeletedAtToWarehousesTable','default','App',1765910936,2),(27,'2025-12-17-040000','App\\Database\\Migrations\\ChangeDepartmentHeadToUserId','default','App',1765911713,3);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `payment_number` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `accounts_payable_id` int unsigned NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` enum('Cash','Check','Bank Transfer','Credit Card') COLLATE utf8mb4_general_ci NOT NULL,
  `reference_number` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Check number, transaction ID, etc.',
  `processed_by` int unsigned NOT NULL,
  `notes` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_number` (`payment_number`),
  KEY `accounts_payable_id` (`accounts_payable_id`),
  KEY `processed_by` (`processed_by`),
  CONSTRAINT `payments_accounts_payable_id_foreign` FOREIGN KEY (`accounts_payable_id`) REFERENCES `accounts_payable` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `payments_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_order_items`
--

DROP TABLE IF EXISTS `purchase_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_order_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_id` int unsigned NOT NULL,
  `material_id` int unsigned NOT NULL,
  `quantity_ordered` decimal(15,2) NOT NULL,
  `quantity_received` decimal(15,2) NOT NULL DEFAULT '0.00',
  `unit_price` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_order_id` (`purchase_order_id`),
  KEY `material_id` (`material_id`),
  CONSTRAINT `purchase_order_items_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `purchase_order_items_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_order_items`
--

LOCK TABLES `purchase_order_items` WRITE;
/*!40000 ALTER TABLE `purchase_order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_orders`
--

DROP TABLE IF EXISTS `purchase_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_orders` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `po_number` varchar(50) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Purchase order number',
  `supplier_id` int unsigned NOT NULL,
  `warehouse_id` int unsigned NOT NULL COMMENT 'Destination warehouse',
  `requested_by` int unsigned NOT NULL COMMENT 'User who requested the PO',
  `approved_by` int unsigned DEFAULT NULL COMMENT 'User who approved the PO',
  `order_date` date NOT NULL COMMENT 'Date of order',
  `expected_delivery_date` date DEFAULT NULL COMMENT 'Expected delivery date',
  `actual_delivery_date` date DEFAULT NULL COMMENT 'Actual delivery date',
  `status` enum('Draft','Pending Approval','Approved','Ordered','Partially Received','Received','Cancelled') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Draft',
  `total_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `po_number` (`po_number`),
  KEY `supplier_id` (`supplier_id`),
  KEY `warehouse_id` (`warehouse_id`),
  KEY `requested_by` (`requested_by`),
  KEY `approved_by` (`approved_by`),
  CONSTRAINT `purchase_orders_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `purchase_orders_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `purchase_orders_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `purchase_orders_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_orders`
--

LOCK TABLES `purchase_orders` WRITE;
/*!40000 ALTER TABLE `purchase_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `receipts`
--

DROP TABLE IF EXISTS `receipts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `receipts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `receipt_number` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `accounts_receivable_id` int unsigned NOT NULL,
  `receipt_date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` enum('Cash','Check','Bank Transfer','Credit Card') COLLATE utf8mb4_general_ci NOT NULL,
  `reference_number` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `processed_by` int unsigned NOT NULL,
  `notes` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `receipt_number` (`receipt_number`),
  KEY `accounts_receivable_id` (`accounts_receivable_id`),
  KEY `processed_by` (`processed_by`),
  CONSTRAINT `receipts_accounts_receivable_id_foreign` FOREIGN KEY (`accounts_receivable_id`) REFERENCES `accounts_receivable` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `receipts_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `receipts`
--

LOCK TABLES `receipts` WRITE;
/*!40000 ALTER TABLE `receipts` DISABLE KEYS */;
/*!40000 ALTER TABLE `receipts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Role name',
  `description` text COLLATE utf8mb4_general_ci COMMENT 'Role description',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Whether the role is active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Warehouse Manager','Manages warehouse operations',1,NULL,NULL),(2,'Warehouse Staff','Performs warehouse tasks',1,NULL,NULL),(3,'Inventory Auditor','Audits inventory records',1,NULL,NULL),(4,'Procurement Officer','Handles procurement processes',1,NULL,NULL),(5,'Accounts Payable Clerk','Manages accounts payable',1,NULL,NULL),(6,'Accounts Receivable Clerk','Manages accounts receivable',1,NULL,NULL),(7,'IT Administrator','Manages IT systems and infrastructure',1,NULL,NULL),(8,'Top Management','Executive level management',1,NULL,NULL);
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_alerts`
--

DROP TABLE IF EXISTS `stock_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_alerts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `material_id` int unsigned NOT NULL,
  `warehouse_id` int unsigned NOT NULL,
  `alert_type` enum('Low Stock','Out of Stock','Expiring Soon','Expired') COLLATE utf8mb4_general_ci NOT NULL,
  `current_quantity` decimal(15,2) NOT NULL,
  `threshold_quantity` decimal(15,2) DEFAULT NULL,
  `is_resolved` tinyint(1) NOT NULL DEFAULT '0',
  `resolved_at` datetime DEFAULT NULL,
  `resolved_by` int unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_alerts_warehouse_id_foreign` (`warehouse_id`),
  KEY `stock_alerts_resolved_by_foreign` (`resolved_by`),
  KEY `material_id_warehouse_id` (`material_id`,`warehouse_id`),
  KEY `is_resolved` (`is_resolved`),
  CONSTRAINT `stock_alerts_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stock_alerts_resolved_by_foreign` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `stock_alerts_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_alerts`
--

LOCK TABLES `stock_alerts` WRITE;
/*!40000 ALTER TABLE `stock_alerts` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_alerts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_movements` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `reference_number` varchar(50) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Unique movement reference',
  `material_id` int unsigned NOT NULL,
  `from_warehouse_id` int unsigned DEFAULT NULL COMMENT 'Source warehouse (null for new stock)',
  `to_warehouse_id` int unsigned DEFAULT NULL COMMENT 'Destination warehouse (null for stock out)',
  `movement_type` enum('Receipt','Transfer','Issue','Adjustment','Return') COLLATE utf8mb4_general_ci NOT NULL,
  `quantity` decimal(15,2) NOT NULL,
  `batch_number` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `movement_date` datetime NOT NULL,
  `performed_by` int unsigned NOT NULL COMMENT 'User who performed the movement',
  `approved_by` int unsigned DEFAULT NULL COMMENT 'User who approved the movement',
  `reference_id` int unsigned DEFAULT NULL COMMENT 'Reference to related record (PO, project, etc.)',
  `reference_type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Type of reference (purchase_order, project, etc.)',
  `notes` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference_number` (`reference_number`),
  KEY `stock_movements_approved_by_foreign` (`approved_by`),
  KEY `material_id` (`material_id`),
  KEY `from_warehouse_id` (`from_warehouse_id`),
  KEY `to_warehouse_id` (`to_warehouse_id`),
  KEY `performed_by` (`performed_by`),
  CONSTRAINT `stock_movements_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `stock_movements_from_warehouse_id_foreign` FOREIGN KEY (`from_warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `stock_movements_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `stock_movements_performed_by_foreign` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `stock_movements_to_warehouse_id_foreign` FOREIGN KEY (`to_warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_movements`
--

LOCK TABLES `stock_movements` WRITE;
/*!40000 ALTER TABLE `stock_movements` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `suppliers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Supplier company name',
  `code` varchar(50) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Supplier code',
  `contact_person` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Primary contact person',
  `email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_general_ci,
  `payment_terms` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Payment terms (e.g., Net 30, Net 60)',
  `tax_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Tax identification number',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `units_of_measure`
--

DROP TABLE IF EXISTS `units_of_measure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `units_of_measure` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Unit name (e.g., pieces, meters, liters)',
  `abbreviation` varchar(10) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Unit abbreviation (e.g., pcs, m, L)',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `abbreviation` (`abbreviation`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `units_of_measure`
--

LOCK TABLES `units_of_measure` WRITE;
/*!40000 ALTER TABLE `units_of_measure` DISABLE KEYS */;
INSERT INTO `units_of_measure` VALUES (1,'Pieces','pcs',1,'2025-12-17 00:38:15','2025-12-17 00:38:15'),(2,'Bags','bag',1,'2025-12-17 00:38:15','2025-12-17 00:38:15'),(3,'Cubic Meters','m3',1,'2025-12-17 00:38:15','2025-12-17 00:38:15'),(4,'Sheets','sheet',1,'2025-12-17 00:38:15','2025-12-17 00:38:15'),(5,'Gallons','gal',1,'2025-12-17 00:38:15','2025-12-17 00:38:15'),(6,'Meters','m',1,'2025-12-17 00:38:15','2025-12-17 00:38:15'),(7,'Kilograms','kg',1,'2025-12-17 00:38:15','2025-12-17 00:38:15'),(8,'Liters','L',1,'2025-12-17 00:38:15','2025-12-17 00:38:15'),(9,'Boxes','box',1,'2025-12-17 00:38:15','2025-12-17 00:38:15'),(10,'Rolls','roll',1,'2025-12-17 00:38:15','2025-12-17 00:38:15');
/*!40000 ALTER TABLE `units_of_measure` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_warehouse_assignments`
--

DROP TABLE IF EXISTS `user_warehouse_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_warehouse_assignments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL COMMENT 'Foreign key to users table',
  `warehouse_id` int unsigned NOT NULL COMMENT 'Foreign key to warehouses table',
  `role_id` int unsigned DEFAULT NULL COMMENT 'Optional: Specific role for this warehouse assignment',
  `is_primary` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'True if this is the primary warehouse for this user',
  `assigned_by` int unsigned DEFAULT NULL COMMENT 'User who assigned this warehouse (usually Warehouse Manager or IT Admin)',
  `assigned_at` datetime DEFAULT NULL COMMENT 'Date when assignment was made',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Whether this assignment is currently active',
  `notes` text COLLATE utf8mb4_general_ci COMMENT 'Optional notes about this assignment',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id_warehouse_id` (`user_id`,`warehouse_id`),
  KEY `user_warehouse_assignments_role_id_foreign` (`role_id`),
  KEY `user_warehouse_assignments_assigned_by_foreign` (`assigned_by`),
  KEY `user_id_is_active` (`user_id`,`is_active`),
  KEY `warehouse_id_is_active` (`warehouse_id`,`is_active`),
  CONSTRAINT `user_warehouse_assignments_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `user_warehouse_assignments_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `user_warehouse_assignments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_warehouse_assignments_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_warehouse_assignments`
--

LOCK TABLES `user_warehouse_assignments` WRITE;
/*!40000 ALTER TABLE `user_warehouse_assignments` DISABLE KEYS */;
INSERT INTO `user_warehouse_assignments` VALUES (1,2,1,2,0,1,'2025-12-17 02:49:40',1,'123','2025-12-17 01:32:34','2025-12-17 02:49:40'),(2,3,1,1,1,1,'2025-12-17 01:33:11',0,'dito ka','2025-12-17 01:33:11','2025-12-17 02:17:13'),(3,5,1,3,0,1,'2025-12-17 01:43:25',0,'123','2025-12-17 01:43:25','2025-12-17 01:48:37'),(4,8,3,5,1,1,'2025-12-17 01:44:17',0,'sa','2025-12-17 01:44:17','2025-12-17 01:49:00'),(5,8,1,5,0,1,'2025-12-17 01:52:33',0,'123','2025-12-17 01:52:33','2025-12-17 02:17:05');
/*!40000 ALTER TABLE `user_warehouse_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'User email address',
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Hashed password',
  `role_id` int unsigned NOT NULL COMMENT 'Foreign key to roles table',
  `department_id` int unsigned DEFAULT NULL COMMENT 'Foreign key to departments table',
  `first_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'User first name',
  `middle_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'User middle name',
  `last_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'User last name',
  `phone_number` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'User contact number',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'User account status',
  `last_login` datetime DEFAULT NULL COMMENT 'Last login timestamp',
  `email_verified_at` datetime DEFAULT NULL COMMENT 'Email verification timestamp',
  `reset_token` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Password reset token',
  `reset_token_expires` datetime DEFAULT NULL COMMENT 'Reset token expiration timestamp',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  KEY `department_id` (`department_id`),
  KEY `reset_token` (`reset_token`),
  CONSTRAINT `users_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'marjovicalejado123@gmail.com','$2y$10$40JMRBqD1srL23fJZTJNEew8kpmtaL53ISOAqDWGqA9wMYCg6vSXm',7,4,'Marjovic','Prato','Alejado','639391520886',1,'2025-12-17 00:38:35','2025-12-17 00:38:14',NULL,NULL,'2025-12-17 00:38:14','2025-12-17 00:38:35'),(2,'aslainiemaruhom@gmail.com','$2y$10$4oBFStp.qa3F/ARV9i/fh.CNyGeBI6ByR1DkpGxRYdW.TTUcd0sDG',2,6,'Aslainie','Lampac','Maruhom','639172345678',1,NULL,'2025-12-17 00:38:14',NULL,NULL,'2025-12-17 00:38:14','2025-12-17 00:38:14'),(3,'cyrylljoyalejado@gmail.com','$2y$10$/znglw6wFSMhfs1TqevhUeTYM0J9JzLa5ZmCnSJKA5mo.M41fWGT.',1,6,'Cyryll Joy','','Alejado','639171234567',1,NULL,'2025-12-17 00:38:14',NULL,NULL,'2025-12-17 00:38:14','2025-12-17 00:38:14'),(4,'maryjoyalejado@gmail.com','$2y$10$C1IwPJnnjQE5OdSQND/T.emYSu41IanNLvq.i3HCubpHtVSbgC1YK',4,3,'Mary Joy','','Alejado','639174567890',1,NULL,'2025-12-17 00:38:14',NULL,NULL,'2025-12-17 00:38:15','2025-12-17 00:38:15'),(5,'viverleialejado@gmail.com','$2y$10$BDgkaZlkH/cekcjCXgUGb.OOv/98gJo7EIT32o2MoDFo7RD1B2gc2',3,5,'Viver Lei','','Alejado','639173456789',1,NULL,'2025-12-17 00:38:14',NULL,NULL,'2025-12-17 00:38:15','2025-12-17 00:38:15'),(6,'victoralejado@gmail.com','$2y$10$U6P2ZDqlfH8Kp.VLeTfEVOt2VjIsUpaAZh9AppEj.e/ucHvuSlFSe',8,1,'Victor','Tabaniera','Alejado','639178901234',1,NULL,'2025-12-17 00:38:14',NULL,NULL,'2025-12-17 00:38:15','2025-12-17 00:38:15'),(7,'virginialejado@gmail.com','$2y$10$zOyTmheK7IU1bbYIK43hYOVrH71k//ZK97490RpE6r03VyGRTzziS',6,2,'Virginia','Prato','Alejado','639176789012',1,NULL,'2025-12-17 00:38:14',NULL,NULL,'2025-12-17 00:38:15','2025-12-17 00:38:15'),(8,'ammarthefilipino@gmail.com','$2y$10$31uJKmDOugjp2StjJpQBuuGggN9jDi4E1a7GeFD.RuTE1W64ZR0Oe',5,2,'Ammar','The','Filipino','639175678901',1,NULL,'2025-12-17 00:38:14',NULL,NULL,'2025-12-17 00:38:15','2025-12-17 00:50:47'),(9,'gahiko@gmail.com','$2y$10$9DbHeiMPYakROCB8ROruz.NeHW.NNTcBl/m/dQbH.b957hB5okcL6',1,1,'Gahi','ko','Mar','09391520884',1,NULL,NULL,NULL,NULL,'2025-12-17 02:13:54','2025-12-17 02:13:54');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `warehouse_locations`
--

DROP TABLE IF EXISTS `warehouse_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `warehouse_locations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `street_address` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `barangay` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `province` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `region` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `postal_code` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Philippines',
  `latitude` decimal(10,8) DEFAULT NULL COMMENT 'Optional: For Google Maps direct plotting (recommended for performance)',
  `longitude` decimal(11,8) DEFAULT NULL COMMENT 'Optional: For Google Maps direct plotting (recommended for performance)',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `warehouse_locations`
--

LOCK TABLES `warehouse_locations` WRITE;
/*!40000 ALTER TABLE `warehouse_locations` DISABLE KEYS */;
INSERT INTO `warehouse_locations` VALUES (1,'Km. 14 West Service Road','Barangay 178','Pasay City','Metro Manila','National Capital Region (NCR)','1300','Philippines',14.53780000,121.00140000,'2025-12-17 00:38:15','2025-12-17 01:34:14'),(2,'National Highway','Barangay Fatima','General Santos City','South Cotabato','Region XII (SOCCSKSARGEN)','9500','Philippines',NULL,NULL,'2025-12-17 00:38:15','2025-12-17 00:38:15'),(3,'Km. 8 McArthur Highway','Barangay Panacan','Davao City','Davao del Sur','Region XI (Davao Region)','8000','Philippines',7.07310000,125.61280000,'2025-12-17 00:38:15','2025-12-17 00:38:15'),(4,'Intramuros','Intramuros','Manila','Metro Manila','National Capital Region (NCR)','1000','Philippines',14.59950000,120.98420000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(5,'Diliman','Diliman','Quezon City','Metro Manila','National Capital Region (NCR)','1100','Philippines',14.67600000,121.04370000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(6,'Poblacion','Poblacion','Makati','Metro Manila','National Capital Region (NCR)','1200','Philippines',14.55470000,121.02440000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(7,'West Service Road','Barangay 178','Pasay','Metro Manila','National Capital Region (NCR)','1300','Philippines',14.53780000,121.00140000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(8,'BGC','Fort Bonifacio','Taguig','Metro Manila','National Capital Region (NCR)','1630','Philippines',14.51760000,121.05090000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(9,'Ortigas Avenue','Ortigas Center','Mandaluyong','Metro Manila','National Capital Region (NCR)','1550','Philippines',14.57940000,121.03590000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(10,'Ortigas Avenue','Ortigas Center','Pasig','Metro Manila','National Capital Region (NCR)','1600','Philippines',14.57640000,121.08510000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(11,'Rizal Avenue Extension','Caloocan City','Caloocan','Metro Manila','National Capital Region (NCR)','1400','Philippines',14.65480000,120.98430000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(12,'Alabang-Zapote Road','Alabang','Las Piñas','Metro Manila','National Capital Region (NCR)','1740','Philippines',14.44960000,120.98260000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(13,'Alabang-Zapote Road','Alabang','Muntinlupa','Metro Manila','National Capital Region (NCR)','1770','Philippines',14.40810000,121.04550000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(14,'Sumulong Highway','Marikina Heights','Marikina','Metro Manila','National Capital Region (NCR)','1800','Philippines',14.65070000,121.10290000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(15,'Ninoy Aquino Avenue','Baclaran','Parañaque','Metro Manila','National Capital Region (NCR)','1700','Philippines',14.47930000,121.01980000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(16,'MacArthur Highway','Valenzuela City','Valenzuela','Metro Manila','National Capital Region (NCR)','1440','Philippines',14.70040000,120.98390000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(17,'Letre Road','Malabon City','Malabon','Metro Manila','National Capital Region (NCR)','1470','Philippines',14.66550000,120.95690000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(18,'Navotas Boulevard','Navotas City','Navotas','Metro Manila','National Capital Region (NCR)','1485','Philippines',14.66670000,120.94170000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(19,'N. Domingo Street','Greenhills','San Juan','Metro Manila','National Capital Region (NCR)','1500','Philippines',14.60190000,121.03550000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(20,'Carlatan','Carlatan','San Fernando','La Union','Region I (Ilocos Region)','2500','Philippines',16.61640000,120.31580000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(21,'Crisologo Street','Vigan City','Vigan','Ilocos Sur','Region I (Ilocos Region)','2700','Philippines',17.57480000,120.38690000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(22,'Rizal Avenue','Laoag City','Laoag','Ilocos Norte','Region I (Ilocos Region)','2900','Philippines',18.19780000,120.59570000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(23,'Perez Boulevard','Dagupan City','Dagupan','Pangasinan','Region I (Ilocos Region)','2400','Philippines',16.04390000,120.33270000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(24,'Rizal Street','Tuguegarao City','Tuguegarao','Cagayan','Region II (Cagayan Valley)','3500','Philippines',17.61330000,121.72690000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(25,'Maharlika Highway','Santiago City','Santiago','Isabela','Region II (Cagayan Valley)','3311','Philippines',16.68810000,121.54870000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(26,'National Highway','Ilagan City','Ilagan','Isabela','Region II (Cagayan Valley)','3300','Philippines',17.14850000,121.88920000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(27,'MacArthur Highway','Angeles City','Angeles','Pampanga','Region III (Central Luzon)','2009','Philippines',15.14500000,120.58470000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(28,'Jose Abad Santos Avenue','San Fernando City','San Fernando','Pampanga','Region III (Central Luzon)','2000','Philippines',15.03190000,120.68950000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(29,'Rizal Avenue','Olongapo City','Olongapo','Zambales','Region III (Central Luzon)','2200','Philippines',14.82920000,120.28280000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(30,'Plaza Rizal','Malolos City','Malolos','Bulacan','Region III (Central Luzon)','3000','Philippines',14.84440000,120.81040000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(31,'Maharlika Highway','Cabanatuan City','Cabanatuan','Nueva Ecija','Region III (Central Luzon)','3100','Philippines',15.48690000,120.96750000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(32,'Romulo Boulevard','Tarlac City','Tarlac','Tarlac','Region III (Central Luzon)','2300','Philippines',15.48690000,120.59010000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(33,'National Highway','Calamba City','Calamba','Laguna','Region IV-A (CALABARZON)','4027','Philippines',14.21170000,121.16560000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(34,'Rizal Avenue','San Pablo City','San Pablo','Laguna','Region IV-A (CALABARZON)','4000','Philippines',14.07030000,121.32560000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(35,'P. Burgos Street','Batangas City','Batangas','Batangas','Region IV-A (CALABARZON)','4200','Philippines',13.75650000,121.05830000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(36,'J.P. Laurel Highway','Lipa City','Lipa','Batangas','Region IV-A (CALABARZON)','4217','Philippines',13.94110000,121.16320000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(37,'P. Burgos Street','Cavite City','Cavite','Cavite','Region IV-A (CALABARZON)','4100','Philippines',14.47910000,120.89700000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(38,'Aguinaldo Highway','Tagaytay City','Tagaytay','Cavite','Region IV-A (CALABARZON)','4120','Philippines',14.10000000,120.93330000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(39,'Sumulong Highway','Antipolo City','Antipolo','Rizal','Region IV-A (CALABARZON)','1870','Philippines',14.62550000,121.12450000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(40,'Maharlika Highway','Lucena City','Lucena','Quezon','Region IV-A (CALABARZON)','4301','Philippines',13.93140000,121.61740000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(41,'Rizal Avenue','Puerto Princesa City','Puerto Princesa','Palawan','Region IV-B (MIMAROPA)','5300','Philippines',9.73920000,118.73530000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(42,'J.P. Rizal Street','Calapan City','Calapan','Oriental Mindoro','Region IV-B (MIMAROPA)','5200','Philippines',13.41170000,121.18030000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(43,'Rizal Street','Romblon','Romblon','Romblon','Region IV-B (MIMAROPA)','5500','Philippines',12.57530000,122.27080000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(44,'Magsaysay Avenue','Naga City','Naga','Camarines Sur','Region V (Bicol Region)','4400','Philippines',13.61920000,123.18140000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(45,'Rizal Street','Legazpi City','Legazpi','Albay','Region V (Bicol Region)','4500','Philippines',13.13900000,123.73380000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(46,'Rizal Street','Sorsogon City','Sorsogon','Sorsogon','Region V (Bicol Region)','4700','Philippines',12.97000000,124.00610000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(47,'San Francisco Street','Iriga City','Iriga','Camarines Sur','Region V (Bicol Region)','4431','Philippines',13.42000000,123.41000000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(48,'J.M. Basa Street','Iloilo City','Iloilo','Iloilo','Region VI (Western Visayas)','5000','Philippines',10.72020000,122.56210000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(49,'Lacson Street','Bacolod City','Bacolod','Negros Occidental','Region VI (Western Visayas)','6100','Philippines',10.67690000,122.95030000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(50,'Rizal Street','Roxas City','Roxas','Capiz','Region VI (Western Visayas)','5800','Philippines',11.58530000,122.75110000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(51,'Rizal Street','Kalibo','Kalibo','Aklan','Region VI (Western Visayas)','5600','Philippines',11.70640000,122.36440000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(52,'Colon Street','Cebu City','Cebu','Cebu','Region VII (Central Visayas)','6000','Philippines',10.31570000,123.88540000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(53,'Mactan Airport Road','Lapu-Lapu City','Lapu-Lapu','Cebu','Region VII (Central Visayas)','6015','Philippines',10.31030000,123.94940000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(54,'A.C. Cortes Avenue','Mandaue City','Mandaue','Cebu','Region VII (Central Visayas)','6014','Philippines',10.33330000,123.93330000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(55,'Carlos P. Garcia Avenue','Tagbilaran City','Tagbilaran','Bohol','Region VII (Central Visayas)','6300','Philippines',9.66440000,123.85220000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(56,'Rizal Boulevard','Dumaguete City','Dumaguete','Negros Oriental','Region VII (Central Visayas)','6200','Philippines',9.30770000,123.30540000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(57,'Real Street','Tacloban City','Tacloban','Leyte','Region VIII (Eastern Visayas)','6500','Philippines',11.24440000,125.00390000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(58,'Luna Street','Ormoc City','Ormoc','Leyte','Region VIII (Eastern Visayas)','6541','Philippines',11.00640000,124.60750000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(59,'Rizal Street','Calbayog City','Calbayog','Samar','Region VIII (Eastern Visayas)','6710','Philippines',12.06670000,124.60000000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(60,'Pasonanca Road','Zamboanga City','Zamboanga','Zamboanga del Sur','Region IX (Zamboanga Peninsula)','7000','Philippines',6.92140000,122.07900000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(61,'Rizal Avenue','Dipolog City','Dipolog','Zamboanga del Norte','Region IX (Zamboanga Peninsula)','7100','Philippines',8.58810000,123.34140000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(62,'Rizal Avenue','Pagadian City','Pagadian','Zamboanga del Sur','Region IX (Zamboanga Peninsula)','7016','Philippines',7.82580000,123.43700000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(63,'Velez Street','Cagayan de Oro City','Cagayan de Oro','Misamis Oriental','Region X (Northern Mindanao)','9000','Philippines',8.45420000,124.63190000,'2025-12-17 02:31:42','2025-12-17 02:31:42'),(64,'Roxas Avenue','Iligan City','Iligan','Lanao del Norte','Region X (Northern Mindanao)','9200','Philippines',8.22800000,124.24520000,'2025-12-17 02:31:43','2025-12-17 02:31:43'),(65,'Rizal Avenue','Ozamiz City','Ozamiz','Misamis Occidental','Region X (Northern Mindanao)','7200','Philippines',8.14810000,123.84050000,'2025-12-17 02:31:43','2025-12-17 02:31:43'),(66,'Rizal Street','Gingoog City','Gingoog','Misamis Oriental','Region X (Northern Mindanao)','9014','Philippines',8.81670000,125.10000000,'2025-12-17 02:31:43','2025-12-17 02:31:43'),(67,'McArthur Highway','Davao City','Davao','Davao del Sur','Region XI (Davao Region)','8000','Philippines',7.07310000,125.61280000,'2025-12-17 02:31:43','2025-12-17 02:31:43'),(68,'Apokon Road','Tagum City','Tagum','Davao del Norte','Region XI (Davao Region)','8100','Philippines',7.44780000,125.80780000,'2025-12-17 02:31:43','2025-12-17 02:31:43'),(69,'Rizal Avenue','Digos City','Digos','Davao del Sur','Region XI (Davao Region)','8002','Philippines',6.74970000,125.35700000,'2025-12-17 02:31:43','2025-12-17 02:31:43'),(70,'Rizal Street','Mati City','Mati','Davao Oriental','Region XI (Davao Region)','8200','Philippines',6.95280000,126.21670000,'2025-12-17 02:31:43','2025-12-17 02:31:43'),(71,'National Highway','General Santos City','General Santos','South Cotabato','Region XII (SOCCSKSARGEN)','9500','Philippines',6.11280000,125.17170000,'2025-12-17 02:31:43','2025-12-17 02:31:43'),(72,'National Highway','Koronadal City','Koronadal','South Cotabato','Region XII (SOCCSKSARGEN)','9506','Philippines',6.50310000,124.84690000,'2025-12-17 02:31:43','2025-12-17 02:31:43'),(73,'Sinsuat Avenue','Cotabato City','Cotabato','Maguindanao','Region XII (SOCCSKSARGEN)','9600','Philippines',7.20440000,124.24640000,'2025-12-17 02:31:43','2025-12-17 02:31:43'),(74,'National Highway','Tacurong City','Tacurong','Sultan Kudarat','Region XII (SOCCSKSARGEN)','9800','Philippines',6.68810000,124.67390000,'2025-12-17 02:31:43','2025-12-17 02:31:43'),(75,'J.C. Aquino Avenue','Butuan City','Butuan','Agusan del Norte','Region XIII (Caraga)','8600','Philippines',8.94920000,125.54360000,'2025-12-17 02:31:43','2025-12-17 02:31:43'),(76,'Rizal Street','Surigao City','Surigao','Surigao del Norte','Region XIII (Caraga)','8400','Philippines',9.78530000,125.49500000,'2025-12-17 02:31:43','2025-12-17 02:31:43'),(77,'Rizal Street','Tandag City','Tandag','Surigao del Sur','Region XIII (Caraga)','8300','Philippines',9.07830000,126.19860000,'2025-12-17 02:31:43','2025-12-17 02:31:43'),(78,'Rizal Street','Marawi City','Marawi','Lanao del Sur','Bangsamoro Autonomous Region in Muslim Mindanao (BARMM)','9700','Philippines',7.99860000,124.29280000,'2025-12-17 02:31:43','2025-12-17 02:31:43'),(79,'Rizal Street','Lamitan City','Lamitan','Basilan','Bangsamoro Autonomous Region in Muslim Mindanao (BARMM)','7302','Philippines',6.65000000,122.13330000,'2025-12-17 02:31:43','2025-12-17 02:31:43'),(80,'Session Road','Baguio City','Baguio','Benguet','Cordillera Administrative Region (CAR)','2600','Philippines',16.40230000,120.59600000,'2025-12-17 02:31:43','2025-12-17 02:31:43'),(81,'Km. 5','La Trinidad','La Trinidad','Benguet','Cordillera Administrative Region (CAR)','2601','Philippines',16.46440000,120.58750000,'2025-12-17 02:31:43','2025-12-17 02:31:43'),(82,'Ortigas Avenue Extension','Cainta','Cainta','Rizal','Region IV-A (CALABARZON)','1900','Philippines',14.57860000,121.12220000,'2025-12-17 02:31:43','2025-12-17 02:31:43'),(83,'National Highway','San Pedro','San Pedro','Laguna','Region IV-A (CALABARZON)','4023','Philippines',14.35890000,121.05780000,'2025-12-17 02:31:43','2025-12-17 02:31:43'),(84,'National Highway','Biñan','Biñan','Laguna','Region IV-A (CALABARZON)','4024','Philippines',14.33330000,121.08330000,'2025-12-17 02:31:43','2025-12-17 02:31:43'),(85,'National Highway','Santa Rosa','Santa Rosa','Laguna','Region IV-A (CALABARZON)','4026','Philippines',14.31670000,121.11110000,'2025-12-17 02:31:43','2025-12-17 02:31:43'),(86,'Aguinaldo Highway','Bacoor','Bacoor','Cavite','Region IV-A (CALABARZON)','4102','Philippines',14.45940000,120.92690000,'2025-12-17 02:31:43','2025-12-17 02:31:43'),(87,'Aguinaldo Highway','Imus','Imus','Cavite','Region IV-A (CALABARZON)','4103','Philippines',14.42970000,120.93690000,'2025-12-17 02:31:43','2025-12-17 02:31:43'),(88,'Aguinaldo Highway','Dasmariñas','Dasmariñas','Cavite','Region IV-A (CALABARZON)','4114','Philippines',14.32940000,120.93690000,'2025-12-17 02:31:43','2025-12-17 02:31:43'),(89,'Purok Magiting','Buayan','General Santos City','South Cotabato','Region XII (SOCCSKSARGEN)','9500','Philippines',NULL,NULL,'2025-12-17 02:36:38','2025-12-17 02:36:38');
/*!40000 ALTER TABLE `warehouse_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `warehouses`
--

DROP TABLE IF EXISTS `warehouses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `warehouses` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Warehouse name',
  `code` varchar(20) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Warehouse code identifier',
  `warehouse_location_id` int unsigned DEFAULT NULL COMMENT 'Foreign key to warehouse_locations table',
  `capacity` decimal(15,2) DEFAULT NULL COMMENT 'Storage capacity in square meters',
  `manager_id` int unsigned DEFAULT NULL COMMENT 'Foreign key to users table',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Warehouse operational status',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL COMMENT 'Soft delete timestamp',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `manager_id` (`manager_id`),
  KEY `warehouse_location_id` (`warehouse_location_id`),
  CONSTRAINT `warehouses_manager_fk` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `warehouses_warehouse_location_id_foreign` FOREIGN KEY (`warehouse_location_id`) REFERENCES `warehouse_locations` (`id`) ON DELETE CASCADE ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `warehouses`
--

LOCK TABLES `warehouses` WRITE;
/*!40000 ALTER TABLE `warehouses` DISABLE KEYS */;
INSERT INTO `warehouses` VALUES (1,'Warehouse A','WH-001',1,5000.00,NULL,1,'2025-12-17 00:38:15','2025-12-17 00:38:15',NULL),(2,'Warehouse B','WH-002',2,3000.00,NULL,1,'2025-12-17 00:38:15','2025-12-17 00:38:15',NULL),(3,'Warehouse C','WH-003',3,2000.00,NULL,1,'2025-12-17 00:38:15','2025-12-17 00:38:15',NULL);
/*!40000 ALTER TABLE `warehouses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `work_assignments`
--

DROP TABLE IF EXISTS `work_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `work_assignments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `task_type` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `task_description` text COLLATE utf8mb4_general_ci,
  `warehouse_id` int unsigned DEFAULT NULL COMMENT 'Optional: Specific warehouse for this task (if task is warehouse-specific)',
  `location` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Physical location within warehouse or general location',
  `priority` enum('low','medium','high','urgent') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'medium',
  `deadline` datetime DEFAULT NULL,
  `status` enum('pending','in_progress','completed') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `assigned_by` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `work_assignments_user_id_foreign` (`user_id`),
  KEY `work_assignments_assigned_by_foreign` (`assigned_by`),
  KEY `work_assignments_warehouse_id_foreign` (`warehouse_id`),
  CONSTRAINT `work_assignments_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `work_assignments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `work_assignments_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `work_assignments`
--

LOCK TABLES `work_assignments` WRITE;
/*!40000 ALTER TABLE `work_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `work_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'witms_db'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-17  3:16:30
