-- MySQL dump 10.13  Distrib 8.0.42, for Linux (x86_64)
--
-- Host: localhost    Database: gerenciador_finaceiro
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
-- Table structure for table `accounts`
--

DROP TABLE IF EXISTS `accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `bank_id` bigint unsigned NOT NULL,
  `type` int NOT NULL DEFAULT '1' COMMENT '1 = Conta Corrente, 2 = Poupança',
  `balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `balance_currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BRL',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `accounts_user_id_foreign` (`user_id`),
  KEY `accounts_bank_id_foreign` (`bank_id`),
  KEY `accounts_balance_balance_currency_index` (`balance`,`balance_currency`),
  CONSTRAINT `accounts_bank_id_foreign` FOREIGN KEY (`bank_id`) REFERENCES `banks` (`id`),
  CONSTRAINT `accounts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounts`
--

LOCK TABLES `accounts` WRITE;
/*!40000 ALTER TABLE `accounts` DISABLE KEYS */;
INSERT INTO `accounts` VALUES (1,1,3,1,0.00,'BRL','2025-05-16 15:48:30','2025-05-16 15:48:30'),(2,1,3,2,0.00,'BRL','2025-05-16 15:48:30','2025-05-16 15:48:30');
/*!40000 ALTER TABLE `accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `action_logs`
--

DROP TABLE IF EXISTS `action_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `action_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_id` bigint unsigned DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `action_logs`
--

LOCK TABLES `action_logs` WRITE;
/*!40000 ALTER TABLE `action_logs` DISABLE KEYS */;
INSERT INTO `action_logs` VALUES (1,'1','updated','App\\Models\\TransactionItem',15,'{\"id\": 15, \"amount\": \"298.00\", \"status\": \"PENDING\", \"due_date\": \"2025-05-17\", \"created_at\": \"2025-05-16T15:48:30.000000Z\", \"updated_at\": \"2025-05-16T15:48:30.000000Z\", \"payment_date\": null, \"transaction_id\": 15, \"installment_number\": 1}','{\"id\": 15, \"amount\": \"298.00\", \"status\": \"PAID\", \"due_date\": \"2025-05-17\", \"created_at\": \"2025-05-16 15:48:30\", \"updated_at\": \"2025-05-16 18:26:44\", \"payment_date\": \"2025-05-14\", \"transaction_id\": 15, \"installment_number\": 1}','Model App\\Models\\TransactionItem was updated','2025-05-16 18:26:44','2025-05-16 18:26:44'),(17,'1','updated','App\\Models\\TransactionItem',41,'{\"id\": 41, \"amount\": \"35.33\", \"status\": \"PAID\", \"due_date\": \"2025-05-03\", \"created_at\": \"2025-05-16T15:48:30.000000Z\", \"updated_at\": \"2025-05-16T19:32:16.000000Z\", \"payment_date\": \"2025-05-16\", \"transaction_id\": 38, \"installment_number\": 2}','{\"id\": 41, \"amount\": \"35.33\", \"status\": \"SCHEDULED\", \"due_date\": \"2025-05-03\", \"created_at\": \"2025-05-16 15:48:30\", \"updated_at\": \"2025-05-16 19:37:12\", \"payment_date\": \"2025-05-16\", \"transaction_id\": 38, \"installment_number\": 2}','Model App\\Models\\TransactionItem was updated','2025-05-16 19:37:12','2025-05-16 19:37:12'),(18,NULL,'updated','App\\Models\\TransactionItem',41,'{\"id\": 41, \"amount\": \"35.33\", \"status\": \"SCHEDULED\", \"due_date\": \"2025-05-03\", \"created_at\": \"2025-05-16T15:48:30.000000Z\", \"updated_at\": \"2025-05-16T19:37:12.000000Z\", \"payment_date\": \"2025-05-16\", \"transaction_id\": 38, \"installment_number\": 2}','{\"id\": 41, \"amount\": \"35.33\", \"status\": \"PAID\", \"due_date\": \"2025-05-03\", \"created_at\": \"2025-05-16 15:48:30\", \"updated_at\": \"2025-05-16 19:37:38\", \"payment_date\": \"2025-05-16T00:00:00.000000Z\", \"transaction_id\": 38, \"installment_number\": 2}','Model App\\Models\\TransactionItem was updated','2025-05-16 19:37:38','2025-05-16 19:37:38'),(19,NULL,'updated','App\\Models\\TransactionItem',41,'{\"id\": 41, \"amount\": \"35.33\", \"status\": \"SCHEDULED\", \"due_date\": \"2025-05-03\", \"created_at\": \"2025-05-16T15:48:30.000000Z\", \"updated_at\": \"2025-05-16T19:37:12.000000Z\", \"payment_date\": \"2025-05-16\", \"transaction_id\": 38, \"installment_number\": 2}','{\"id\": 41, \"amount\": \"35.33\", \"status\": \"PAID\", \"due_date\": \"2025-05-03\", \"created_at\": \"2025-05-16T15:48:30.000000Z\", \"updated_at\": \"2025-05-16T19:37:38.000000Z\", \"payment_date\": \"2025-05-16T00:00:00.000000Z\", \"transaction_id\": 38, \"installment_number\": 2}','(BOT) Transação ID 41 atualizada: status alterado de \'SCHEDULED\' para \'PAID\'; data de pagamento alterada de \'16/05/2025\' para \'16/05/2025\'.','2025-05-16 19:37:38','2025-05-16 19:37:38'),(20,'1','updated','App\\Models\\TransactionItem',41,'{\"id\": 41, \"amount\": \"35.33\", \"status\": \"PAID\", \"due_date\": \"2025-05-03\", \"created_at\": \"2025-05-16T15:48:30.000000Z\", \"updated_at\": \"2025-05-16T19:37:38.000000Z\", \"payment_date\": \"2025-05-16\", \"transaction_id\": 38, \"installment_number\": 2}','{\"id\": 41, \"amount\": \"35.33\", \"status\": \"DEBIT\", \"due_date\": \"2025-05-03\", \"created_at\": \"2025-05-16 15:48:30\", \"updated_at\": \"2025-05-16 19:41:01\", \"payment_date\": \"2025-05-16\", \"transaction_id\": 38, \"installment_number\": 2}','Model App\\Models\\TransactionItem was updated','2025-05-16 19:41:01','2025-05-16 19:41:01'),(21,NULL,'updated','App\\Models\\TransactionItem',41,'{\"id\": 41, \"amount\": \"35.33\", \"status\": \"DEBIT\", \"due_date\": \"2025-05-03\", \"created_at\": \"2025-05-16T15:48:30.000000Z\", \"updated_at\": \"2025-05-16T19:41:01.000000Z\", \"payment_date\": \"2025-05-16\", \"transaction_id\": 38, \"installment_number\": 2}','{\"id\": 41, \"amount\": \"35.33\", \"status\": \"PAID\", \"due_date\": \"2025-05-03\", \"created_at\": \"2025-05-16 15:48:30\", \"updated_at\": \"2025-05-16 19:41:19\", \"payment_date\": \"2025-05-16T00:00:00.000000Z\", \"transaction_id\": 38, \"installment_number\": 2}','Model App\\Models\\TransactionItem was updated','2025-05-16 19:41:19','2025-05-16 19:41:19'),(22,NULL,'updated','App\\Models\\TransactionItem',41,'{\"id\": 41, \"amount\": \"35.33\", \"status\": \"DEBIT\", \"due_date\": \"2025-05-03\", \"created_at\": \"2025-05-16T15:48:30.000000Z\", \"updated_at\": \"2025-05-16T19:41:01.000000Z\", \"payment_date\": \"2025-05-16\", \"transaction_id\": 38, \"installment_number\": 2}','{\"id\": 41, \"amount\": \"35.33\", \"status\": \"PAID\", \"due_date\": \"2025-05-03\", \"created_at\": \"2025-05-16T15:48:30.000000Z\", \"updated_at\": \"2025-05-16T19:41:19.000000Z\", \"payment_date\": \"2025-05-16T00:00:00.000000Z\", \"transaction_id\": 38, \"installment_number\": 2}','(BOT) Transação ID 41 atualizada: status alterado de \'DEBIT\' para \'PAID\'; data de pagamento alterada de \'16/05/2025\' para \'16/05/2025\'.','2025-05-16 19:41:19','2025-05-16 19:41:19'),(23,'1','updated','App\\Models\\TransactionItem',41,'{\"id\": 41, \"amount\": \"35.33\", \"status\": \"PAID\", \"due_date\": \"2025-05-03\", \"created_at\": \"2025-05-16T15:48:30.000000Z\", \"updated_at\": \"2025-05-16T19:41:19.000000Z\", \"payment_date\": \"2025-05-16\", \"transaction_id\": 38, \"installment_number\": 2}','{\"id\": 41, \"amount\": \"35.33\", \"status\": \"SCHEDULED\", \"due_date\": \"2025-05-03\", \"created_at\": \"2025-05-16 15:48:30\", \"updated_at\": \"2025-05-16 19:44:02\", \"payment_date\": \"2025-05-16\", \"transaction_id\": 38, \"installment_number\": 2}','Model App\\Models\\TransactionItem was updated','2025-05-16 19:44:02','2025-05-16 19:44:02'),(24,NULL,'updated','App\\Models\\TransactionItem',41,'{\"id\": 41, \"amount\": \"35.33\", \"status\": \"SCHEDULED\", \"due_date\": \"2025-05-03\", \"created_at\": \"2025-05-16T15:48:30.000000Z\", \"updated_at\": \"2025-05-16T19:44:02.000000Z\", \"payment_date\": \"2025-05-16\", \"transaction_id\": 38, \"installment_number\": 2}','{\"id\": 41, \"amount\": \"35.33\", \"status\": \"PAID\", \"due_date\": \"2025-05-03\", \"created_at\": \"2025-05-16 15:48:30\", \"updated_at\": \"2025-05-16 19:44:10\", \"payment_date\": \"2025-05-16T00:00:00.000000Z\", \"transaction_id\": 38, \"installment_number\": 2}','Model App\\Models\\TransactionItem was updated','2025-05-16 19:44:10','2025-05-16 19:44:10'),(25,NULL,'updated','App\\Models\\TransactionItem',41,'{\"id\": 41, \"amount\": \"35.33\", \"status\": \"SCHEDULED\", \"due_date\": \"2025-05-03\", \"created_at\": \"2025-05-16T15:48:30.000000Z\", \"updated_at\": \"2025-05-16T19:44:02.000000Z\", \"payment_date\": \"2025-05-16\", \"transaction_id\": 38, \"installment_number\": 2}','{\"id\": 41, \"amount\": \"35.33\", \"status\": \"PAID\", \"due_date\": \"2025-05-03\", \"created_at\": \"2025-05-16T15:48:30.000000Z\", \"updated_at\": \"2025-05-16T19:44:10.000000Z\", \"transaction\": {\"id\": 38, \"date\": \"2025-04-03\", \"type\": \"EXPENSE\", \"amount\": \"212.00\", \"method\": \"CARD\", \"card_id\": 1, \"user_id\": 1, \"account_id\": null, \"created_at\": \"2025-05-16T15:48:30.000000Z\", \"updated_at\": \"2025-05-16T15:48:30.000000Z\", \"category_id\": 8, \"description\": \"Seguro do carro\", \"is_recurring\": true, \"recurrence_interval\": 6}, \"payment_date\": \"2025-05-16T00:00:00.000000Z\", \"transaction_id\": 38, \"installment_number\": 2}','(BOT) Transação ID 41 atualizada: status alterado de \'SCHEDULED\' para \'PAID\'; data de pagamento alterada de \'16/05/2025\' para \'16/05/2025\'.','2025-05-16 19:44:10','2025-05-16 19:44:10'),(26,'1','updated','App\\Models\\TransactionItem',61,'{\"id\": 61, \"amount\": \"39.83\", \"status\": \"PENDING\", \"due_date\": \"2025-05-01\", \"created_at\": \"2025-05-16T15:48:30.000000Z\", \"updated_at\": \"2025-05-16T15:48:30.000000Z\", \"payment_date\": null, \"transaction_id\": 41, \"installment_number\": 5}','{\"id\": 61, \"amount\": \"39.83\", \"status\": \"SCHEDULED\", \"due_date\": \"2025-05-01\", \"created_at\": \"2025-05-16 15:48:30\", \"updated_at\": \"2025-05-19 13:58:31\", \"payment_date\": \"2025-05-21\", \"transaction_id\": 41, \"installment_number\": 5}','Model App\\Models\\TransactionItem was updated','2025-05-19 13:58:31','2025-05-19 13:58:31');
/*!40000 ALTER TABLE `action_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `banks`
--

DROP TABLE IF EXISTS `banks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `banks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banks`
--

LOCK TABLES `banks` WRITE;
/*!40000 ALTER TABLE `banks` DISABLE KEYS */;
INSERT INTO `banks` VALUES (1,'Banco do Brasil','977','2025-05-16 15:48:30','2025-05-16 15:48:30'),(2,'Bradesco','834','2025-05-16 15:48:30','2025-05-16 15:48:30'),(3,'Caixa Econômica','729','2025-05-16 15:48:30','2025-05-16 15:48:30');
/*!40000 ALTER TABLE `banks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `brand_cards`
--

DROP TABLE IF EXISTS `brand_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `brand_cards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brand_cards`
--

LOCK TABLES `brand_cards` WRITE;
/*!40000 ALTER TABLE `brand_cards` DISABLE KEYS */;
INSERT INTO `brand_cards` VALUES (1,'Visa','visa',NULL,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(2,'Mastercard','mastercard',NULL,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(3,'Elo','elo',NULL,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(4,'American Express','amex',NULL,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(5,'Hipercard','hipercard',NULL,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(6,'Diners Club','diners',NULL,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(7,'Discover','discover',NULL,'2025-05-16 15:48:30','2025-05-16 15:48:30');
/*!40000 ALTER TABLE `brand_cards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel_cache_livewire-rate-limiter:53df9078c25794ac8729cde92270b7d375e17de5','i:1;',1747410586),('laravel_cache_livewire-rate-limiter:53df9078c25794ac8729cde92270b7d375e17de5:timer','i:1747410586;',1747410586),('laravel_cache_livewire-rate-limiter:949d30ea22f107bd43d44ad537056dcc17429d84','i:1;',1747414691),('laravel_cache_livewire-rate-limiter:949d30ea22f107bd43d44ad537056dcc17429d84:timer','i:1747414691;',1747414691),('laravel_cache_spatie.permission.cache','a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:21:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:17:\"view transactions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:19:\"create transactions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:17:\"edit transactions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:19:\"delete transactions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:22:\"view transaction_items\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:24:\"create transaction_items\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:22:\"edit transaction_items\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:24:\"delete transaction_items\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:13:\"view accounts\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:15:\"create accounts\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:13:\"edit accounts\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:15:\"delete accounts\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:10:\"view users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:12:\"create users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:10:\"edit users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:12:\"delete users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:10:\"view roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:12:\"create roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:10:\"edit roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:19;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:12:\"delete roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:20;a:4:{s:1:\"a\";i:21;s:1:\"b\";s:16:\"access dashboard\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}}s:5:\"roles\";a:1:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:5:\"ADMIN\";s:1:\"c\";s:3:\"web\";}}}',1747741436);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cards`
--

DROP TABLE IF EXISTS `cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `bank_id` bigint unsigned NOT NULL,
  `brand_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `due_date` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `limit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cards_user_id_foreign` (`user_id`),
  KEY `cards_bank_id_foreign` (`bank_id`),
  KEY `cards_brand_id_foreign` (`brand_id`),
  CONSTRAINT `cards_bank_id_foreign` FOREIGN KEY (`bank_id`) REFERENCES `banks` (`id`),
  CONSTRAINT `cards_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brand_cards` (`id`),
  CONSTRAINT `cards_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cards`
--

LOCK TABLES `cards` WRITE;
/*!40000 ALTER TABLE `cards` DISABLE KEYS */;
INSERT INTO `cards` VALUES (1,1,2,1,'Inter One','1234 5678 9876 5432',NULL,'15',0.00,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(2,1,1,5,'Bradesco Exclusive','4321 8765 6789 1234',NULL,'10',0.00,'2025-05-16 15:48:30','2025-05-16 15:48:30');
/*!40000 ALTER TABLE `cards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (2,'Freelance','2025-05-16 15:48:30','2025-05-16 15:48:30'),(4,'Aluguel recebido','2025-05-16 15:48:30','2025-05-16 15:48:30'),(5,'Reembolso','2025-05-16 15:48:30','2025-05-16 15:48:30'),(6,'13º salário','2025-05-16 15:48:30','2025-05-16 15:48:30'),(7,'Bonificação','2025-05-16 15:48:30','2025-05-16 15:48:30'),(8,'Outras receitas','2025-05-16 15:48:30','2025-05-16 15:48:30'),(9,'Aluguel','2025-05-16 15:48:30','2025-05-16 15:48:30'),(10,'Supermercado','2025-05-16 15:48:30','2025-05-16 15:48:30'),(11,'Transporte','2025-05-16 15:48:30','2025-05-16 15:48:30'),(12,'Combustível','2025-05-16 15:48:30','2025-05-16 15:48:30'),(13,'Educação','2025-05-16 15:48:30','2025-05-16 15:48:30'),(14,'Luz','2025-05-16 15:48:30','2025-05-16 15:48:30'),(15,'Água','2025-05-16 15:48:30','2025-05-16 15:48:30'),(16,'Internet','2025-05-16 15:48:30','2025-05-16 15:48:30'),(17,'Telefone','2025-05-16 15:48:30','2025-05-16 15:48:30'),(18,'Cartão de crédito','2025-05-16 15:48:30','2025-05-16 15:48:30'),(19,'Lazer','2025-05-16 15:48:30','2025-05-16 15:48:30'),(20,'Viagem','2025-05-16 15:48:30','2025-05-16 15:48:30'),(21,'Saúde','2025-05-16 15:48:30','2025-05-16 15:48:30'),(22,'Farmácia','2025-05-16 15:48:30','2025-05-16 15:48:30'),(23,'Roupas','2025-05-16 15:48:30','2025-05-16 15:48:30'),(25,'Doações','2025-05-16 15:48:30','2025-05-16 15:48:30'),(26,'Outras despesas','2025-05-16 15:48:30','2025-05-16 15:48:30');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exports`
--

DROP TABLE IF EXISTS `exports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `completed_at` timestamp NULL DEFAULT NULL,
  `file_disk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exporter` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `processed_rows` int unsigned NOT NULL DEFAULT '0',
  `total_rows` int unsigned NOT NULL,
  `successful_rows` int unsigned NOT NULL DEFAULT '0',
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `exports_user_id_foreign` (`user_id`),
  CONSTRAINT `exports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exports`
--

LOCK TABLES `exports` WRITE;
/*!40000 ALTER TABLE `exports` DISABLE KEYS */;
INSERT INTO `exports` VALUES (1,'2025-05-17 14:46:43','local','export-1-transaction-items','App\\Filament\\Exports\\TransactionItemExporter',4,4,4,1,'2025-05-17 14:46:41','2025-05-17 14:46:43'),(2,'2025-05-17 14:51:49','local','export-2-transaction-items','App\\Filament\\Exports\\TransactionItemExporter',6,6,6,1,'2025-05-17 14:51:49','2025-05-17 14:51:49'),(3,'2025-05-17 14:58:20','local','export-3-transaction-items','App\\Filament\\Exports\\TransactionItemExporter',6,6,6,1,'2025-05-17 14:58:19','2025-05-17 14:58:20'),(4,'2025-05-17 14:59:08','local','export-4-transaction-items','App\\Filament\\Exports\\TransactionItemExporter',4,4,4,1,'2025-05-17 14:59:05','2025-05-17 14:59:08');
/*!40000 ALTER TABLE `exports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_import_rows`
--

DROP TABLE IF EXISTS `failed_import_rows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_import_rows` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `data` json NOT NULL,
  `import_id` bigint unsigned NOT NULL,
  `validation_error` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `failed_import_rows_import_id_foreign` (`import_id`),
  CONSTRAINT `failed_import_rows_import_id_foreign` FOREIGN KEY (`import_id`) REFERENCES `imports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_import_rows`
--

LOCK TABLES `failed_import_rows` WRITE;
/*!40000 ALTER TABLE `failed_import_rows` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_import_rows` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `imports`
--

DROP TABLE IF EXISTS `imports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `imports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `completed_at` timestamp NULL DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `importer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `processed_rows` int unsigned NOT NULL DEFAULT '0',
  `total_rows` int unsigned NOT NULL,
  `successful_rows` int unsigned NOT NULL DEFAULT '0',
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `imports_user_id_foreign` (`user_id`),
  CONSTRAINT `imports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `imports`
--

LOCK TABLES `imports` WRITE;
/*!40000 ALTER TABLE `imports` DISABLE KEYS */;
/*!40000 ALTER TABLE `imports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
INSERT INTO `job_batches` VALUES ('9eeeff29-bcf3-479a-bee8-404b8f9e482a','',2,0,0,'[]','a:2:{s:13:\"allowFailures\";b:1;s:7:\"finally\";a:1:{i:0;O:47:\"Laravel\\SerializableClosure\\SerializableClosure\":1:{s:12:\"serializable\";O:46:\"Laravel\\SerializableClosure\\Serializers\\Signed\":2:{s:12:\"serializable\";s:7636:\"O:46:\"Laravel\\SerializableClosure\\Serializers\\Native\":5:{s:3:\"use\";a:1:{s:4:\"next\";O:46:\"Filament\\Actions\\Exports\\Jobs\\ExportCompletion\":7:{s:11:\"\0*\0exporter\";O:44:\"App\\Filament\\Exports\\TransactionItemExporter\":3:{s:9:\"\0*\0export\";O:38:\"Filament\\Actions\\Exports\\Models\\Export\":32:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";N;s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:1;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:8:{s:7:\"user_id\";i:1;s:8:\"exporter\";s:44:\"App\\Filament\\Exports\\TransactionItemExporter\";s:10:\"total_rows\";i:4;s:9:\"file_disk\";s:5:\"local\";s:10:\"updated_at\";s:19:\"2025-05-17 14:46:41\";s:10:\"created_at\";s:19:\"2025-05-17 14:46:41\";s:2:\"id\";i:1;s:9:\"file_name\";s:26:\"export-1-transaction-items\";}s:11:\"\0*\0original\";a:8:{s:7:\"user_id\";i:1;s:8:\"exporter\";s:44:\"App\\Filament\\Exports\\TransactionItemExporter\";s:10:\"total_rows\";i:4;s:9:\"file_disk\";s:5:\"local\";s:10:\"updated_at\";s:19:\"2025-05-17 14:46:41\";s:10:\"created_at\";s:19:\"2025-05-17 14:46:41\";s:2:\"id\";i:1;s:9:\"file_name\";s:26:\"export-1-transaction-items\";}s:10:\"\0*\0changes\";a:1:{s:9:\"file_name\";s:26:\"export-1-transaction-items\";}s:8:\"\0*\0casts\";a:4:{s:12:\"completed_at\";s:9:\"timestamp\";s:14:\"processed_rows\";s:7:\"integer\";s:10:\"total_rows\";s:7:\"integer\";s:15:\"successful_rows\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:0:{}s:10:\"\0*\0guarded\";a:0:{}}s:12:\"\0*\0columnMap\";a:16:{s:2:\"id\";s:2:\"ID\";s:21:\"transaction.user.name\";s:8:\"Usuário\";s:25:\"transaction.category.name\";s:9:\"Categoria\";s:16:\"transaction.type\";s:4:\"Tipo\";s:23:\"transaction.description\";s:11:\"Descrição\";s:18:\"transaction.method\";s:18:\"Forma de pagamento\";s:24:\"transaction.account.name\";s:5:\"Conta\";s:21:\"transaction.card.name\";s:7:\"Cartão\";s:16:\"transaction.date\";s:19:\"Data da transação\";s:24:\"transaction.is_recurring\";s:10:\"Recorrente\";s:31:\"transaction.recurrence_interval\";s:20:\"Intervalo recorrente\";s:18:\"installment_number\";s:7:\"Parcela\";s:6:\"amount\";s:5:\"Valor\";s:8:\"due_date\";s:10:\"Vencimento\";s:12:\"payment_date\";s:9:\"Pagamento\";s:6:\"status\";s:6:\"Status\";}s:10:\"\0*\0options\";a:0:{}}s:9:\"\0*\0export\";O:45:\"Illuminate\\Contracts\\Database\\ModelIdentifier\":5:{s:5:\"class\";s:38:\"Filament\\Actions\\Exports\\Models\\Export\";s:2:\"id\";i:1;s:9:\"relations\";a:0:{}s:10:\"connection\";s:5:\"mysql\";s:15:\"collectionClass\";N;}s:12:\"\0*\0columnMap\";a:16:{s:2:\"id\";s:2:\"ID\";s:21:\"transaction.user.name\";s:8:\"Usuário\";s:25:\"transaction.category.name\";s:9:\"Categoria\";s:16:\"transaction.type\";s:4:\"Tipo\";s:23:\"transaction.description\";s:11:\"Descrição\";s:18:\"transaction.method\";s:18:\"Forma de pagamento\";s:24:\"transaction.account.name\";s:5:\"Conta\";s:21:\"transaction.card.name\";s:7:\"Cartão\";s:16:\"transaction.date\";s:19:\"Data da transação\";s:24:\"transaction.is_recurring\";s:10:\"Recorrente\";s:31:\"transaction.recurrence_interval\";s:20:\"Intervalo recorrente\";s:18:\"installment_number\";s:7:\"Parcela\";s:6:\"amount\";s:5:\"Valor\";s:8:\"due_date\";s:10:\"Vencimento\";s:12:\"payment_date\";s:9:\"Pagamento\";s:6:\"status\";s:6:\"Status\";}s:10:\"\0*\0formats\";a:2:{i:0;E:47:\"Filament\\Actions\\Exports\\Enums\\ExportFormat:Csv\";i:1;E:48:\"Filament\\Actions\\Exports\\Enums\\ExportFormat:Xlsx\";}s:10:\"\0*\0options\";a:0:{}s:7:\"chained\";a:1:{i:0;s:3469:\"O:44:\"Filament\\Actions\\Exports\\Jobs\\CreateXlsxFile\":4:{s:11:\"\0*\0exporter\";O:44:\"App\\Filament\\Exports\\TransactionItemExporter\":3:{s:9:\"\0*\0export\";O:38:\"Filament\\Actions\\Exports\\Models\\Export\":32:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";N;s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:1;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:8:{s:7:\"user_id\";i:1;s:8:\"exporter\";s:44:\"App\\Filament\\Exports\\TransactionItemExporter\";s:10:\"total_rows\";i:4;s:9:\"file_disk\";s:5:\"local\";s:10:\"updated_at\";s:19:\"2025-05-17 14:46:41\";s:10:\"created_at\";s:19:\"2025-05-17 14:46:41\";s:2:\"id\";i:1;s:9:\"file_name\";s:26:\"export-1-transaction-items\";}s:11:\"\0*\0original\";a:8:{s:7:\"user_id\";i:1;s:8:\"exporter\";s:44:\"App\\Filament\\Exports\\TransactionItemExporter\";s:10:\"total_rows\";i:4;s:9:\"file_disk\";s:5:\"local\";s:10:\"updated_at\";s:19:\"2025-05-17 14:46:41\";s:10:\"created_at\";s:19:\"2025-05-17 14:46:41\";s:2:\"id\";i:1;s:9:\"file_name\";s:26:\"export-1-transaction-items\";}s:10:\"\0*\0changes\";a:1:{s:9:\"file_name\";s:26:\"export-1-transaction-items\";}s:8:\"\0*\0casts\";a:4:{s:12:\"completed_at\";s:9:\"timestamp\";s:14:\"processed_rows\";s:7:\"integer\";s:10:\"total_rows\";s:7:\"integer\";s:15:\"successful_rows\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:0:{}s:10:\"\0*\0guarded\";a:0:{}}s:12:\"\0*\0columnMap\";a:16:{s:2:\"id\";s:2:\"ID\";s:21:\"transaction.user.name\";s:8:\"Usuário\";s:25:\"transaction.category.name\";s:9:\"Categoria\";s:16:\"transaction.type\";s:4:\"Tipo\";s:23:\"transaction.description\";s:11:\"Descrição\";s:18:\"transaction.method\";s:18:\"Forma de pagamento\";s:24:\"transaction.account.name\";s:5:\"Conta\";s:21:\"transaction.card.name\";s:7:\"Cartão\";s:16:\"transaction.date\";s:19:\"Data da transação\";s:24:\"transaction.is_recurring\";s:10:\"Recorrente\";s:31:\"transaction.recurrence_interval\";s:20:\"Intervalo recorrente\";s:18:\"installment_number\";s:7:\"Parcela\";s:6:\"amount\";s:5:\"Valor\";s:8:\"due_date\";s:10:\"Vencimento\";s:12:\"payment_date\";s:9:\"Pagamento\";s:6:\"status\";s:6:\"Status\";}s:10:\"\0*\0options\";a:0:{}}s:9:\"\0*\0export\";O:45:\"Illuminate\\Contracts\\Database\\ModelIdentifier\":5:{s:5:\"class\";s:38:\"Filament\\Actions\\Exports\\Models\\Export\";s:2:\"id\";i:1;s:9:\"relations\";a:0:{}s:10:\"connection\";s:5:\"mysql\";s:15:\"collectionClass\";N;}s:12:\"\0*\0columnMap\";a:16:{s:2:\"id\";s:2:\"ID\";s:21:\"transaction.user.name\";s:8:\"Usuário\";s:25:\"transaction.category.name\";s:9:\"Categoria\";s:16:\"transaction.type\";s:4:\"Tipo\";s:23:\"transaction.description\";s:11:\"Descrição\";s:18:\"transaction.method\";s:18:\"Forma de pagamento\";s:24:\"transaction.account.name\";s:5:\"Conta\";s:21:\"transaction.card.name\";s:7:\"Cartão\";s:16:\"transaction.date\";s:19:\"Data da transação\";s:24:\"transaction.is_recurring\";s:10:\"Recorrente\";s:31:\"transaction.recurrence_interval\";s:20:\"Intervalo recorrente\";s:18:\"installment_number\";s:7:\"Parcela\";s:6:\"amount\";s:5:\"Valor\";s:8:\"due_date\";s:10:\"Vencimento\";s:12:\"payment_date\";s:9:\"Pagamento\";s:6:\"status\";s:6:\"Status\";}s:10:\"\0*\0options\";a:0:{}}\";}s:19:\"chainCatchCallbacks\";a:0:{}}}s:8:\"function\";s:266:\"function (\\Illuminate\\Bus\\Batch $batch) use ($next) {\n                if (! $batch->cancelled()) {\n                    \\Illuminate\\Container\\Container::getInstance()->make(\\Illuminate\\Contracts\\Bus\\Dispatcher::class)->dispatch($next);\n                }\n            }\";s:5:\"scope\";s:27:\"Illuminate\\Bus\\ChainedBatch\";s:4:\"this\";N;s:4:\"self\";s:32:\"00000000000009810000000000000000\";}\";s:4:\"hash\";s:44:\"avt0KAQvFUzip4UtACTIxXCdGHAmUOxYeTObAu7pph8=\";}}}}',NULL,1747493203,1747493203),('9eef00fd-5a5f-4e8f-bbe7-ffbe433e9b20','',2,0,0,'[]','a:2:{s:13:\"allowFailures\";b:1;s:7:\"finally\";a:1:{i:0;O:47:\"Laravel\\SerializableClosure\\SerializableClosure\":1:{s:12:\"serializable\";O:46:\"Laravel\\SerializableClosure\\Serializers\\Signed\":2:{s:12:\"serializable\";s:7636:\"O:46:\"Laravel\\SerializableClosure\\Serializers\\Native\":5:{s:3:\"use\";a:1:{s:4:\"next\";O:46:\"Filament\\Actions\\Exports\\Jobs\\ExportCompletion\":7:{s:11:\"\0*\0exporter\";O:44:\"App\\Filament\\Exports\\TransactionItemExporter\":3:{s:9:\"\0*\0export\";O:38:\"Filament\\Actions\\Exports\\Models\\Export\":32:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";N;s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:1;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:8:{s:7:\"user_id\";i:1;s:8:\"exporter\";s:44:\"App\\Filament\\Exports\\TransactionItemExporter\";s:10:\"total_rows\";i:6;s:9:\"file_disk\";s:5:\"local\";s:10:\"updated_at\";s:19:\"2025-05-17 14:51:49\";s:10:\"created_at\";s:19:\"2025-05-17 14:51:49\";s:2:\"id\";i:2;s:9:\"file_name\";s:26:\"export-2-transaction-items\";}s:11:\"\0*\0original\";a:8:{s:7:\"user_id\";i:1;s:8:\"exporter\";s:44:\"App\\Filament\\Exports\\TransactionItemExporter\";s:10:\"total_rows\";i:6;s:9:\"file_disk\";s:5:\"local\";s:10:\"updated_at\";s:19:\"2025-05-17 14:51:49\";s:10:\"created_at\";s:19:\"2025-05-17 14:51:49\";s:2:\"id\";i:2;s:9:\"file_name\";s:26:\"export-2-transaction-items\";}s:10:\"\0*\0changes\";a:1:{s:9:\"file_name\";s:26:\"export-2-transaction-items\";}s:8:\"\0*\0casts\";a:4:{s:12:\"completed_at\";s:9:\"timestamp\";s:14:\"processed_rows\";s:7:\"integer\";s:10:\"total_rows\";s:7:\"integer\";s:15:\"successful_rows\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:0:{}s:10:\"\0*\0guarded\";a:0:{}}s:12:\"\0*\0columnMap\";a:16:{s:2:\"id\";s:2:\"ID\";s:21:\"transaction.user.name\";s:8:\"Usuário\";s:25:\"transaction.category.name\";s:9:\"Categoria\";s:16:\"transaction.type\";s:4:\"Tipo\";s:23:\"transaction.description\";s:11:\"Descrição\";s:18:\"transaction.method\";s:18:\"Forma de pagamento\";s:24:\"transaction.account.name\";s:5:\"Conta\";s:21:\"transaction.card.name\";s:7:\"Cartão\";s:16:\"transaction.date\";s:19:\"Data da transação\";s:24:\"transaction.is_recurring\";s:10:\"Recorrente\";s:31:\"transaction.recurrence_interval\";s:20:\"Intervalo recorrente\";s:18:\"installment_number\";s:7:\"Parcela\";s:6:\"amount\";s:5:\"Valor\";s:8:\"due_date\";s:10:\"Vencimento\";s:12:\"payment_date\";s:9:\"Pagamento\";s:6:\"status\";s:6:\"Status\";}s:10:\"\0*\0options\";a:0:{}}s:9:\"\0*\0export\";O:45:\"Illuminate\\Contracts\\Database\\ModelIdentifier\":5:{s:5:\"class\";s:38:\"Filament\\Actions\\Exports\\Models\\Export\";s:2:\"id\";i:2;s:9:\"relations\";a:0:{}s:10:\"connection\";s:5:\"mysql\";s:15:\"collectionClass\";N;}s:12:\"\0*\0columnMap\";a:16:{s:2:\"id\";s:2:\"ID\";s:21:\"transaction.user.name\";s:8:\"Usuário\";s:25:\"transaction.category.name\";s:9:\"Categoria\";s:16:\"transaction.type\";s:4:\"Tipo\";s:23:\"transaction.description\";s:11:\"Descrição\";s:18:\"transaction.method\";s:18:\"Forma de pagamento\";s:24:\"transaction.account.name\";s:5:\"Conta\";s:21:\"transaction.card.name\";s:7:\"Cartão\";s:16:\"transaction.date\";s:19:\"Data da transação\";s:24:\"transaction.is_recurring\";s:10:\"Recorrente\";s:31:\"transaction.recurrence_interval\";s:20:\"Intervalo recorrente\";s:18:\"installment_number\";s:7:\"Parcela\";s:6:\"amount\";s:5:\"Valor\";s:8:\"due_date\";s:10:\"Vencimento\";s:12:\"payment_date\";s:9:\"Pagamento\";s:6:\"status\";s:6:\"Status\";}s:10:\"\0*\0formats\";a:2:{i:0;E:47:\"Filament\\Actions\\Exports\\Enums\\ExportFormat:Csv\";i:1;E:48:\"Filament\\Actions\\Exports\\Enums\\ExportFormat:Xlsx\";}s:10:\"\0*\0options\";a:0:{}s:7:\"chained\";a:1:{i:0;s:3469:\"O:44:\"Filament\\Actions\\Exports\\Jobs\\CreateXlsxFile\":4:{s:11:\"\0*\0exporter\";O:44:\"App\\Filament\\Exports\\TransactionItemExporter\":3:{s:9:\"\0*\0export\";O:38:\"Filament\\Actions\\Exports\\Models\\Export\":32:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";N;s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:1;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:8:{s:7:\"user_id\";i:1;s:8:\"exporter\";s:44:\"App\\Filament\\Exports\\TransactionItemExporter\";s:10:\"total_rows\";i:6;s:9:\"file_disk\";s:5:\"local\";s:10:\"updated_at\";s:19:\"2025-05-17 14:51:49\";s:10:\"created_at\";s:19:\"2025-05-17 14:51:49\";s:2:\"id\";i:2;s:9:\"file_name\";s:26:\"export-2-transaction-items\";}s:11:\"\0*\0original\";a:8:{s:7:\"user_id\";i:1;s:8:\"exporter\";s:44:\"App\\Filament\\Exports\\TransactionItemExporter\";s:10:\"total_rows\";i:6;s:9:\"file_disk\";s:5:\"local\";s:10:\"updated_at\";s:19:\"2025-05-17 14:51:49\";s:10:\"created_at\";s:19:\"2025-05-17 14:51:49\";s:2:\"id\";i:2;s:9:\"file_name\";s:26:\"export-2-transaction-items\";}s:10:\"\0*\0changes\";a:1:{s:9:\"file_name\";s:26:\"export-2-transaction-items\";}s:8:\"\0*\0casts\";a:4:{s:12:\"completed_at\";s:9:\"timestamp\";s:14:\"processed_rows\";s:7:\"integer\";s:10:\"total_rows\";s:7:\"integer\";s:15:\"successful_rows\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:0:{}s:10:\"\0*\0guarded\";a:0:{}}s:12:\"\0*\0columnMap\";a:16:{s:2:\"id\";s:2:\"ID\";s:21:\"transaction.user.name\";s:8:\"Usuário\";s:25:\"transaction.category.name\";s:9:\"Categoria\";s:16:\"transaction.type\";s:4:\"Tipo\";s:23:\"transaction.description\";s:11:\"Descrição\";s:18:\"transaction.method\";s:18:\"Forma de pagamento\";s:24:\"transaction.account.name\";s:5:\"Conta\";s:21:\"transaction.card.name\";s:7:\"Cartão\";s:16:\"transaction.date\";s:19:\"Data da transação\";s:24:\"transaction.is_recurring\";s:10:\"Recorrente\";s:31:\"transaction.recurrence_interval\";s:20:\"Intervalo recorrente\";s:18:\"installment_number\";s:7:\"Parcela\";s:6:\"amount\";s:5:\"Valor\";s:8:\"due_date\";s:10:\"Vencimento\";s:12:\"payment_date\";s:9:\"Pagamento\";s:6:\"status\";s:6:\"Status\";}s:10:\"\0*\0options\";a:0:{}}s:9:\"\0*\0export\";O:45:\"Illuminate\\Contracts\\Database\\ModelIdentifier\":5:{s:5:\"class\";s:38:\"Filament\\Actions\\Exports\\Models\\Export\";s:2:\"id\";i:2;s:9:\"relations\";a:0:{}s:10:\"connection\";s:5:\"mysql\";s:15:\"collectionClass\";N;}s:12:\"\0*\0columnMap\";a:16:{s:2:\"id\";s:2:\"ID\";s:21:\"transaction.user.name\";s:8:\"Usuário\";s:25:\"transaction.category.name\";s:9:\"Categoria\";s:16:\"transaction.type\";s:4:\"Tipo\";s:23:\"transaction.description\";s:11:\"Descrição\";s:18:\"transaction.method\";s:18:\"Forma de pagamento\";s:24:\"transaction.account.name\";s:5:\"Conta\";s:21:\"transaction.card.name\";s:7:\"Cartão\";s:16:\"transaction.date\";s:19:\"Data da transação\";s:24:\"transaction.is_recurring\";s:10:\"Recorrente\";s:31:\"transaction.recurrence_interval\";s:20:\"Intervalo recorrente\";s:18:\"installment_number\";s:7:\"Parcela\";s:6:\"amount\";s:5:\"Valor\";s:8:\"due_date\";s:10:\"Vencimento\";s:12:\"payment_date\";s:9:\"Pagamento\";s:6:\"status\";s:6:\"Status\";}s:10:\"\0*\0options\";a:0:{}}\";}s:19:\"chainCatchCallbacks\";a:0:{}}}s:8:\"function\";s:266:\"function (\\Illuminate\\Bus\\Batch $batch) use ($next) {\n                if (! $batch->cancelled()) {\n                    \\Illuminate\\Container\\Container::getInstance()->make(\\Illuminate\\Contracts\\Bus\\Dispatcher::class)->dispatch($next);\n                }\n            }\";s:5:\"scope\";s:27:\"Illuminate\\Bus\\ChainedBatch\";s:4:\"this\";N;s:4:\"self\";s:32:\"000000000000098f0000000000000000\";}\";s:4:\"hash\";s:44:\"AU/WrmHQ3GGLdmxkNZn6g8f7lkttm6VSk3xDT7iKTtA=\";}}}}',NULL,1747493509,1747493509),('9eef0351-02cb-4bb7-960c-934811cdb4e1','',2,0,0,'[]','a:2:{s:13:\"allowFailures\";b:1;s:7:\"finally\";a:1:{i:0;O:47:\"Laravel\\SerializableClosure\\SerializableClosure\":1:{s:12:\"serializable\";O:46:\"Laravel\\SerializableClosure\\Serializers\\Signed\":2:{s:12:\"serializable\";s:7636:\"O:46:\"Laravel\\SerializableClosure\\Serializers\\Native\":5:{s:3:\"use\";a:1:{s:4:\"next\";O:46:\"Filament\\Actions\\Exports\\Jobs\\ExportCompletion\":7:{s:11:\"\0*\0exporter\";O:44:\"App\\Filament\\Exports\\TransactionItemExporter\":3:{s:9:\"\0*\0export\";O:38:\"Filament\\Actions\\Exports\\Models\\Export\":32:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";N;s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:1;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:8:{s:7:\"user_id\";i:1;s:8:\"exporter\";s:44:\"App\\Filament\\Exports\\TransactionItemExporter\";s:10:\"total_rows\";i:6;s:9:\"file_disk\";s:5:\"local\";s:10:\"updated_at\";s:19:\"2025-05-17 14:58:19\";s:10:\"created_at\";s:19:\"2025-05-17 14:58:19\";s:2:\"id\";i:3;s:9:\"file_name\";s:26:\"export-3-transaction-items\";}s:11:\"\0*\0original\";a:8:{s:7:\"user_id\";i:1;s:8:\"exporter\";s:44:\"App\\Filament\\Exports\\TransactionItemExporter\";s:10:\"total_rows\";i:6;s:9:\"file_disk\";s:5:\"local\";s:10:\"updated_at\";s:19:\"2025-05-17 14:58:19\";s:10:\"created_at\";s:19:\"2025-05-17 14:58:19\";s:2:\"id\";i:3;s:9:\"file_name\";s:26:\"export-3-transaction-items\";}s:10:\"\0*\0changes\";a:1:{s:9:\"file_name\";s:26:\"export-3-transaction-items\";}s:8:\"\0*\0casts\";a:4:{s:12:\"completed_at\";s:9:\"timestamp\";s:14:\"processed_rows\";s:7:\"integer\";s:10:\"total_rows\";s:7:\"integer\";s:15:\"successful_rows\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:0:{}s:10:\"\0*\0guarded\";a:0:{}}s:12:\"\0*\0columnMap\";a:16:{s:2:\"id\";s:2:\"ID\";s:21:\"transaction.user.name\";s:8:\"Usuário\";s:25:\"transaction.category.name\";s:9:\"Categoria\";s:16:\"transaction.type\";s:4:\"Tipo\";s:23:\"transaction.description\";s:11:\"Descrição\";s:18:\"transaction.method\";s:18:\"Forma de pagamento\";s:24:\"transaction.account.name\";s:5:\"Conta\";s:21:\"transaction.card.name\";s:7:\"Cartão\";s:16:\"transaction.date\";s:19:\"Data da transação\";s:24:\"transaction.is_recurring\";s:10:\"Recorrente\";s:31:\"transaction.recurrence_interval\";s:20:\"Intervalo recorrente\";s:18:\"installment_number\";s:7:\"Parcela\";s:6:\"amount\";s:5:\"Valor\";s:8:\"due_date\";s:10:\"Vencimento\";s:12:\"payment_date\";s:9:\"Pagamento\";s:6:\"status\";s:6:\"Status\";}s:10:\"\0*\0options\";a:0:{}}s:9:\"\0*\0export\";O:45:\"Illuminate\\Contracts\\Database\\ModelIdentifier\":5:{s:5:\"class\";s:38:\"Filament\\Actions\\Exports\\Models\\Export\";s:2:\"id\";i:3;s:9:\"relations\";a:0:{}s:10:\"connection\";s:5:\"mysql\";s:15:\"collectionClass\";N;}s:12:\"\0*\0columnMap\";a:16:{s:2:\"id\";s:2:\"ID\";s:21:\"transaction.user.name\";s:8:\"Usuário\";s:25:\"transaction.category.name\";s:9:\"Categoria\";s:16:\"transaction.type\";s:4:\"Tipo\";s:23:\"transaction.description\";s:11:\"Descrição\";s:18:\"transaction.method\";s:18:\"Forma de pagamento\";s:24:\"transaction.account.name\";s:5:\"Conta\";s:21:\"transaction.card.name\";s:7:\"Cartão\";s:16:\"transaction.date\";s:19:\"Data da transação\";s:24:\"transaction.is_recurring\";s:10:\"Recorrente\";s:31:\"transaction.recurrence_interval\";s:20:\"Intervalo recorrente\";s:18:\"installment_number\";s:7:\"Parcela\";s:6:\"amount\";s:5:\"Valor\";s:8:\"due_date\";s:10:\"Vencimento\";s:12:\"payment_date\";s:9:\"Pagamento\";s:6:\"status\";s:6:\"Status\";}s:10:\"\0*\0formats\";a:2:{i:0;E:47:\"Filament\\Actions\\Exports\\Enums\\ExportFormat:Csv\";i:1;E:48:\"Filament\\Actions\\Exports\\Enums\\ExportFormat:Xlsx\";}s:10:\"\0*\0options\";a:0:{}s:7:\"chained\";a:1:{i:0;s:3469:\"O:44:\"Filament\\Actions\\Exports\\Jobs\\CreateXlsxFile\":4:{s:11:\"\0*\0exporter\";O:44:\"App\\Filament\\Exports\\TransactionItemExporter\":3:{s:9:\"\0*\0export\";O:38:\"Filament\\Actions\\Exports\\Models\\Export\":32:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";N;s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:1;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:8:{s:7:\"user_id\";i:1;s:8:\"exporter\";s:44:\"App\\Filament\\Exports\\TransactionItemExporter\";s:10:\"total_rows\";i:6;s:9:\"file_disk\";s:5:\"local\";s:10:\"updated_at\";s:19:\"2025-05-17 14:58:19\";s:10:\"created_at\";s:19:\"2025-05-17 14:58:19\";s:2:\"id\";i:3;s:9:\"file_name\";s:26:\"export-3-transaction-items\";}s:11:\"\0*\0original\";a:8:{s:7:\"user_id\";i:1;s:8:\"exporter\";s:44:\"App\\Filament\\Exports\\TransactionItemExporter\";s:10:\"total_rows\";i:6;s:9:\"file_disk\";s:5:\"local\";s:10:\"updated_at\";s:19:\"2025-05-17 14:58:19\";s:10:\"created_at\";s:19:\"2025-05-17 14:58:19\";s:2:\"id\";i:3;s:9:\"file_name\";s:26:\"export-3-transaction-items\";}s:10:\"\0*\0changes\";a:1:{s:9:\"file_name\";s:26:\"export-3-transaction-items\";}s:8:\"\0*\0casts\";a:4:{s:12:\"completed_at\";s:9:\"timestamp\";s:14:\"processed_rows\";s:7:\"integer\";s:10:\"total_rows\";s:7:\"integer\";s:15:\"successful_rows\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:0:{}s:10:\"\0*\0guarded\";a:0:{}}s:12:\"\0*\0columnMap\";a:16:{s:2:\"id\";s:2:\"ID\";s:21:\"transaction.user.name\";s:8:\"Usuário\";s:25:\"transaction.category.name\";s:9:\"Categoria\";s:16:\"transaction.type\";s:4:\"Tipo\";s:23:\"transaction.description\";s:11:\"Descrição\";s:18:\"transaction.method\";s:18:\"Forma de pagamento\";s:24:\"transaction.account.name\";s:5:\"Conta\";s:21:\"transaction.card.name\";s:7:\"Cartão\";s:16:\"transaction.date\";s:19:\"Data da transação\";s:24:\"transaction.is_recurring\";s:10:\"Recorrente\";s:31:\"transaction.recurrence_interval\";s:20:\"Intervalo recorrente\";s:18:\"installment_number\";s:7:\"Parcela\";s:6:\"amount\";s:5:\"Valor\";s:8:\"due_date\";s:10:\"Vencimento\";s:12:\"payment_date\";s:9:\"Pagamento\";s:6:\"status\";s:6:\"Status\";}s:10:\"\0*\0options\";a:0:{}}s:9:\"\0*\0export\";O:45:\"Illuminate\\Contracts\\Database\\ModelIdentifier\":5:{s:5:\"class\";s:38:\"Filament\\Actions\\Exports\\Models\\Export\";s:2:\"id\";i:3;s:9:\"relations\";a:0:{}s:10:\"connection\";s:5:\"mysql\";s:15:\"collectionClass\";N;}s:12:\"\0*\0columnMap\";a:16:{s:2:\"id\";s:2:\"ID\";s:21:\"transaction.user.name\";s:8:\"Usuário\";s:25:\"transaction.category.name\";s:9:\"Categoria\";s:16:\"transaction.type\";s:4:\"Tipo\";s:23:\"transaction.description\";s:11:\"Descrição\";s:18:\"transaction.method\";s:18:\"Forma de pagamento\";s:24:\"transaction.account.name\";s:5:\"Conta\";s:21:\"transaction.card.name\";s:7:\"Cartão\";s:16:\"transaction.date\";s:19:\"Data da transação\";s:24:\"transaction.is_recurring\";s:10:\"Recorrente\";s:31:\"transaction.recurrence_interval\";s:20:\"Intervalo recorrente\";s:18:\"installment_number\";s:7:\"Parcela\";s:6:\"amount\";s:5:\"Valor\";s:8:\"due_date\";s:10:\"Vencimento\";s:12:\"payment_date\";s:9:\"Pagamento\";s:6:\"status\";s:6:\"Status\";}s:10:\"\0*\0options\";a:0:{}}\";}s:19:\"chainCatchCallbacks\";a:0:{}}}s:8:\"function\";s:266:\"function (\\Illuminate\\Bus\\Batch $batch) use ($next) {\n                if (! $batch->cancelled()) {\n                    \\Illuminate\\Container\\Container::getInstance()->make(\\Illuminate\\Contracts\\Bus\\Dispatcher::class)->dispatch($next);\n                }\n            }\";s:5:\"scope\";s:27:\"Illuminate\\Bus\\ChainedBatch\";s:4:\"this\";N;s:4:\"self\";s:32:\"0000000000000b6e0000000000000000\";}\";s:4:\"hash\";s:44:\"Z6O6MFGLbdn+SlTXBQhr5Pkzy0v1jHSIAwMtVN7RG4A=\";}}}}',NULL,1747493900,1747493900),('9eef039a-8dbc-4446-99db-5eff53a37a2d','',2,0,0,'[]','a:2:{s:13:\"allowFailures\";b:1;s:7:\"finally\";a:1:{i:0;O:47:\"Laravel\\SerializableClosure\\SerializableClosure\":1:{s:12:\"serializable\";O:46:\"Laravel\\SerializableClosure\\Serializers\\Signed\":2:{s:12:\"serializable\";s:7636:\"O:46:\"Laravel\\SerializableClosure\\Serializers\\Native\":5:{s:3:\"use\";a:1:{s:4:\"next\";O:46:\"Filament\\Actions\\Exports\\Jobs\\ExportCompletion\":7:{s:11:\"\0*\0exporter\";O:44:\"App\\Filament\\Exports\\TransactionItemExporter\":3:{s:9:\"\0*\0export\";O:38:\"Filament\\Actions\\Exports\\Models\\Export\":32:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";N;s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:1;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:8:{s:7:\"user_id\";i:1;s:8:\"exporter\";s:44:\"App\\Filament\\Exports\\TransactionItemExporter\";s:10:\"total_rows\";i:4;s:9:\"file_disk\";s:5:\"local\";s:10:\"updated_at\";s:19:\"2025-05-17 14:59:05\";s:10:\"created_at\";s:19:\"2025-05-17 14:59:05\";s:2:\"id\";i:4;s:9:\"file_name\";s:26:\"export-4-transaction-items\";}s:11:\"\0*\0original\";a:8:{s:7:\"user_id\";i:1;s:8:\"exporter\";s:44:\"App\\Filament\\Exports\\TransactionItemExporter\";s:10:\"total_rows\";i:4;s:9:\"file_disk\";s:5:\"local\";s:10:\"updated_at\";s:19:\"2025-05-17 14:59:05\";s:10:\"created_at\";s:19:\"2025-05-17 14:59:05\";s:2:\"id\";i:4;s:9:\"file_name\";s:26:\"export-4-transaction-items\";}s:10:\"\0*\0changes\";a:1:{s:9:\"file_name\";s:26:\"export-4-transaction-items\";}s:8:\"\0*\0casts\";a:4:{s:12:\"completed_at\";s:9:\"timestamp\";s:14:\"processed_rows\";s:7:\"integer\";s:10:\"total_rows\";s:7:\"integer\";s:15:\"successful_rows\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:0:{}s:10:\"\0*\0guarded\";a:0:{}}s:12:\"\0*\0columnMap\";a:16:{s:2:\"id\";s:2:\"ID\";s:21:\"transaction.user.name\";s:8:\"Usuário\";s:25:\"transaction.category.name\";s:9:\"Categoria\";s:16:\"transaction.type\";s:4:\"Tipo\";s:23:\"transaction.description\";s:11:\"Descrição\";s:18:\"transaction.method\";s:18:\"Forma de pagamento\";s:24:\"transaction.account.name\";s:5:\"Conta\";s:21:\"transaction.card.name\";s:7:\"Cartão\";s:16:\"transaction.date\";s:19:\"Data da transação\";s:24:\"transaction.is_recurring\";s:10:\"Recorrente\";s:31:\"transaction.recurrence_interval\";s:20:\"Intervalo recorrente\";s:18:\"installment_number\";s:7:\"Parcela\";s:6:\"amount\";s:5:\"Valor\";s:8:\"due_date\";s:10:\"Vencimento\";s:12:\"payment_date\";s:9:\"Pagamento\";s:6:\"status\";s:6:\"Status\";}s:10:\"\0*\0options\";a:0:{}}s:9:\"\0*\0export\";O:45:\"Illuminate\\Contracts\\Database\\ModelIdentifier\":5:{s:5:\"class\";s:38:\"Filament\\Actions\\Exports\\Models\\Export\";s:2:\"id\";i:4;s:9:\"relations\";a:0:{}s:10:\"connection\";s:5:\"mysql\";s:15:\"collectionClass\";N;}s:12:\"\0*\0columnMap\";a:16:{s:2:\"id\";s:2:\"ID\";s:21:\"transaction.user.name\";s:8:\"Usuário\";s:25:\"transaction.category.name\";s:9:\"Categoria\";s:16:\"transaction.type\";s:4:\"Tipo\";s:23:\"transaction.description\";s:11:\"Descrição\";s:18:\"transaction.method\";s:18:\"Forma de pagamento\";s:24:\"transaction.account.name\";s:5:\"Conta\";s:21:\"transaction.card.name\";s:7:\"Cartão\";s:16:\"transaction.date\";s:19:\"Data da transação\";s:24:\"transaction.is_recurring\";s:10:\"Recorrente\";s:31:\"transaction.recurrence_interval\";s:20:\"Intervalo recorrente\";s:18:\"installment_number\";s:7:\"Parcela\";s:6:\"amount\";s:5:\"Valor\";s:8:\"due_date\";s:10:\"Vencimento\";s:12:\"payment_date\";s:9:\"Pagamento\";s:6:\"status\";s:6:\"Status\";}s:10:\"\0*\0formats\";a:2:{i:0;E:47:\"Filament\\Actions\\Exports\\Enums\\ExportFormat:Csv\";i:1;E:48:\"Filament\\Actions\\Exports\\Enums\\ExportFormat:Xlsx\";}s:10:\"\0*\0options\";a:0:{}s:7:\"chained\";a:1:{i:0;s:3469:\"O:44:\"Filament\\Actions\\Exports\\Jobs\\CreateXlsxFile\":4:{s:11:\"\0*\0exporter\";O:44:\"App\\Filament\\Exports\\TransactionItemExporter\":3:{s:9:\"\0*\0export\";O:38:\"Filament\\Actions\\Exports\\Models\\Export\":32:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";N;s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:1;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:8:{s:7:\"user_id\";i:1;s:8:\"exporter\";s:44:\"App\\Filament\\Exports\\TransactionItemExporter\";s:10:\"total_rows\";i:4;s:9:\"file_disk\";s:5:\"local\";s:10:\"updated_at\";s:19:\"2025-05-17 14:59:05\";s:10:\"created_at\";s:19:\"2025-05-17 14:59:05\";s:2:\"id\";i:4;s:9:\"file_name\";s:26:\"export-4-transaction-items\";}s:11:\"\0*\0original\";a:8:{s:7:\"user_id\";i:1;s:8:\"exporter\";s:44:\"App\\Filament\\Exports\\TransactionItemExporter\";s:10:\"total_rows\";i:4;s:9:\"file_disk\";s:5:\"local\";s:10:\"updated_at\";s:19:\"2025-05-17 14:59:05\";s:10:\"created_at\";s:19:\"2025-05-17 14:59:05\";s:2:\"id\";i:4;s:9:\"file_name\";s:26:\"export-4-transaction-items\";}s:10:\"\0*\0changes\";a:1:{s:9:\"file_name\";s:26:\"export-4-transaction-items\";}s:8:\"\0*\0casts\";a:4:{s:12:\"completed_at\";s:9:\"timestamp\";s:14:\"processed_rows\";s:7:\"integer\";s:10:\"total_rows\";s:7:\"integer\";s:15:\"successful_rows\";s:7:\"integer\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:0:{}s:10:\"\0*\0guarded\";a:0:{}}s:12:\"\0*\0columnMap\";a:16:{s:2:\"id\";s:2:\"ID\";s:21:\"transaction.user.name\";s:8:\"Usuário\";s:25:\"transaction.category.name\";s:9:\"Categoria\";s:16:\"transaction.type\";s:4:\"Tipo\";s:23:\"transaction.description\";s:11:\"Descrição\";s:18:\"transaction.method\";s:18:\"Forma de pagamento\";s:24:\"transaction.account.name\";s:5:\"Conta\";s:21:\"transaction.card.name\";s:7:\"Cartão\";s:16:\"transaction.date\";s:19:\"Data da transação\";s:24:\"transaction.is_recurring\";s:10:\"Recorrente\";s:31:\"transaction.recurrence_interval\";s:20:\"Intervalo recorrente\";s:18:\"installment_number\";s:7:\"Parcela\";s:6:\"amount\";s:5:\"Valor\";s:8:\"due_date\";s:10:\"Vencimento\";s:12:\"payment_date\";s:9:\"Pagamento\";s:6:\"status\";s:6:\"Status\";}s:10:\"\0*\0options\";a:0:{}}s:9:\"\0*\0export\";O:45:\"Illuminate\\Contracts\\Database\\ModelIdentifier\":5:{s:5:\"class\";s:38:\"Filament\\Actions\\Exports\\Models\\Export\";s:2:\"id\";i:4;s:9:\"relations\";a:0:{}s:10:\"connection\";s:5:\"mysql\";s:15:\"collectionClass\";N;}s:12:\"\0*\0columnMap\";a:16:{s:2:\"id\";s:2:\"ID\";s:21:\"transaction.user.name\";s:8:\"Usuário\";s:25:\"transaction.category.name\";s:9:\"Categoria\";s:16:\"transaction.type\";s:4:\"Tipo\";s:23:\"transaction.description\";s:11:\"Descrição\";s:18:\"transaction.method\";s:18:\"Forma de pagamento\";s:24:\"transaction.account.name\";s:5:\"Conta\";s:21:\"transaction.card.name\";s:7:\"Cartão\";s:16:\"transaction.date\";s:19:\"Data da transação\";s:24:\"transaction.is_recurring\";s:10:\"Recorrente\";s:31:\"transaction.recurrence_interval\";s:20:\"Intervalo recorrente\";s:18:\"installment_number\";s:7:\"Parcela\";s:6:\"amount\";s:5:\"Valor\";s:8:\"due_date\";s:10:\"Vencimento\";s:12:\"payment_date\";s:9:\"Pagamento\";s:6:\"status\";s:6:\"Status\";}s:10:\"\0*\0options\";a:0:{}}\";}s:19:\"chainCatchCallbacks\";a:0:{}}}s:8:\"function\";s:266:\"function (\\Illuminate\\Bus\\Batch $batch) use ($next) {\n                if (! $batch->cancelled()) {\n                    \\Illuminate\\Container\\Container::getInstance()->make(\\Illuminate\\Contracts\\Bus\\Dispatcher::class)->dispatch($next);\n                }\n            }\";s:5:\"scope\";s:27:\"Illuminate\\Bus\\ChainedBatch\";s:4:\"this\";N;s:4:\"self\";s:32:\"0000000000000b300000000000000000\";}\";s:4:\"hash\";s:44:\"9oGyB2Rr6m/0EJ8GyzepLTIaH/elhllwg3wgwFP9YDE=\";}}}}',NULL,1747493948,1747493948);
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2025_05_12_220025_create_banks_table',1),(5,'2025_05_12_220206_create_accounts_table',1),(6,'2025_05_12_220258_create_cards_table',1),(7,'2025_05_12_220422_create_categories_table',1),(8,'2025_05_12_220458_create_transactions_table',1),(9,'2025_05_12_231732_add_logo_and_due_date_to_cards_table',1),(10,'2025_05_13_112136_create_brand_cards_table',1),(11,'2025_05_13_113148_add_brand_card_to_card_table',1),(12,'2025_05_13_153311_create_transaction_items_table',1),(13,'2025_05_14_162959_create_notifications_table',1),(14,'2025_05_14_191630_create_permission_tables',1),(15,'2025_05_16_182107_create_action_logs_table',2),(16,'2025_05_17_143540_create_imports_table',3),(17,'2025_05_17_143541_create_exports_table',3),(18,'2025_05_17_143542_create_failed_import_rows_table',3);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES ('362d6ce4-68c2-42e9-a3af-5e41b49bb0fe','Filament\\Notifications\\DatabaseNotification','App\\Models\\User',1,'{\"actions\":[{\"name\":\"download_csv\",\"color\":null,\"event\":null,\"eventData\":[],\"dispatchDirection\":false,\"dispatchToComponent\":null,\"extraAttributes\":[],\"icon\":null,\"iconPosition\":\"before\",\"iconSize\":null,\"isOutlined\":false,\"isDisabled\":false,\"label\":\"Baixar .csv\",\"shouldClose\":false,\"shouldMarkAsRead\":true,\"shouldMarkAsUnread\":false,\"shouldOpenUrlInNewTab\":true,\"size\":\"sm\",\"tooltip\":null,\"url\":\"\\/filament\\/exports\\/4\\/download?format=csv\",\"view\":\"filament-actions::link-action\"},{\"name\":\"download_xlsx\",\"color\":null,\"event\":null,\"eventData\":[],\"dispatchDirection\":false,\"dispatchToComponent\":null,\"extraAttributes\":[],\"icon\":null,\"iconPosition\":\"before\",\"iconSize\":null,\"isOutlined\":false,\"isDisabled\":false,\"label\":\"Baixar .xlsx\",\"shouldClose\":false,\"shouldMarkAsRead\":true,\"shouldMarkAsUnread\":false,\"shouldOpenUrlInNewTab\":true,\"size\":\"sm\",\"tooltip\":null,\"url\":\"\\/filament\\/exports\\/4\\/download?format=xlsx\",\"view\":\"filament-actions::link-action\"}],\"body\":\"Your transaction item export has completed and 4 rows exported.\",\"color\":null,\"duration\":\"persistent\",\"icon\":\"heroicon-o-check-circle\",\"iconColor\":\"success\",\"status\":\"success\",\"title\":\"Exporta\\u00e7\\u00e3o completa\",\"view\":\"filament-notifications::notification\",\"viewData\":[],\"format\":\"filament\"}',NULL,'2025-05-17 14:59:08','2025-05-17 14:59:08'),('4467fb2f-e27d-49c4-ba89-3333f520093e','Filament\\Notifications\\DatabaseNotification','App\\Models\\User',1,'{\"actions\":[{\"name\":\"download_csv\",\"color\":null,\"event\":null,\"eventData\":[],\"dispatchDirection\":false,\"dispatchToComponent\":null,\"extraAttributes\":[],\"icon\":null,\"iconPosition\":\"before\",\"iconSize\":null,\"isOutlined\":false,\"isDisabled\":false,\"label\":\"Baixar .csv\",\"shouldClose\":false,\"shouldMarkAsRead\":true,\"shouldMarkAsUnread\":false,\"shouldOpenUrlInNewTab\":true,\"size\":\"sm\",\"tooltip\":null,\"url\":\"\\/filament\\/exports\\/1\\/download?format=csv\",\"view\":\"filament-actions::link-action\"},{\"name\":\"download_xlsx\",\"color\":null,\"event\":null,\"eventData\":[],\"dispatchDirection\":false,\"dispatchToComponent\":null,\"extraAttributes\":[],\"icon\":null,\"iconPosition\":\"before\",\"iconSize\":null,\"isOutlined\":false,\"isDisabled\":false,\"label\":\"Baixar .xlsx\",\"shouldClose\":false,\"shouldMarkAsRead\":true,\"shouldMarkAsUnread\":false,\"shouldOpenUrlInNewTab\":true,\"size\":\"sm\",\"tooltip\":null,\"url\":\"\\/filament\\/exports\\/1\\/download?format=xlsx\",\"view\":\"filament-actions::link-action\"}],\"body\":\"Your transaction item export has completed and 4 rows exported.\",\"color\":null,\"duration\":\"persistent\",\"icon\":\"heroicon-o-check-circle\",\"iconColor\":\"success\",\"status\":\"success\",\"title\":\"Exporta\\u00e7\\u00e3o completa\",\"view\":\"filament-notifications::notification\",\"viewData\":[],\"format\":\"filament\"}','2025-05-17 14:46:51','2025-05-17 14:46:43','2025-05-17 14:46:51'),('65ec9113-9d9a-4ad7-a62b-513001bbaac1','Filament\\Notifications\\DatabaseNotification','App\\Models\\User',1,'{\"actions\":[],\"body\":\"Valor: R$ 35,33\\nProduto: Seguro do carro\\nNova data: 16\\/05\\/2025\\nM\\u00e9todo: Indefinido\\nStatus: Agendado\",\"color\":null,\"duration\":\"persistent\",\"icon\":\"heroicon-o-pencil-square\",\"iconColor\":\"warning\",\"status\":null,\"title\":\"Transa\\u00e7\\u00e3o atualizada (Agendado)\",\"view\":\"filament-notifications::notification\",\"viewData\":[],\"format\":\"filament\"}',NULL,'2025-05-16 19:44:04','2025-05-16 19:44:04'),('68237aa1-4b52-4ab6-9995-5173ae9f451d','Filament\\Notifications\\DatabaseNotification','App\\Models\\User',1,'{\"actions\":[{\"name\":\"download_csv\",\"color\":null,\"event\":null,\"eventData\":[],\"dispatchDirection\":false,\"dispatchToComponent\":null,\"extraAttributes\":[],\"icon\":null,\"iconPosition\":\"before\",\"iconSize\":null,\"isOutlined\":false,\"isDisabled\":false,\"label\":\"Baixar .csv\",\"shouldClose\":false,\"shouldMarkAsRead\":true,\"shouldMarkAsUnread\":false,\"shouldOpenUrlInNewTab\":true,\"size\":\"sm\",\"tooltip\":null,\"url\":\"\\/filament\\/exports\\/3\\/download?format=csv\",\"view\":\"filament-actions::link-action\"},{\"name\":\"download_xlsx\",\"color\":null,\"event\":null,\"eventData\":[],\"dispatchDirection\":false,\"dispatchToComponent\":null,\"extraAttributes\":[],\"icon\":null,\"iconPosition\":\"before\",\"iconSize\":null,\"isOutlined\":false,\"isDisabled\":false,\"label\":\"Baixar .xlsx\",\"shouldClose\":false,\"shouldMarkAsRead\":true,\"shouldMarkAsUnread\":false,\"shouldOpenUrlInNewTab\":true,\"size\":\"sm\",\"tooltip\":null,\"url\":\"\\/filament\\/exports\\/3\\/download?format=xlsx\",\"view\":\"filament-actions::link-action\"}],\"body\":\"Your transaction item export has completed and 6 rows exported.\",\"color\":null,\"duration\":\"persistent\",\"icon\":\"heroicon-o-check-circle\",\"iconColor\":\"success\",\"status\":\"success\",\"title\":\"Exporta\\u00e7\\u00e3o completa\",\"view\":\"filament-notifications::notification\",\"viewData\":[],\"format\":\"filament\"}','2025-05-17 14:59:10','2025-05-17 14:58:20','2025-05-17 14:59:10'),('87c2823b-e548-4edb-9b55-7ef62727fdd1','Filament\\Notifications\\DatabaseNotification','App\\Models\\User',1,'{\"actions\":[],\"body\":\"Valor: R$ 35,33\\nProduto: Seguro do carro\\nNova data: 16\\/05\\/2025\\nM\\u00e9todo: Indefinido\\nStatus: Pago\",\"color\":null,\"duration\":\"persistent\",\"icon\":\"heroicon-o-pencil-square\",\"iconColor\":\"warning\",\"status\":null,\"title\":\"Transa\\u00e7\\u00e3o atualizada (Pago)\",\"view\":\"filament-notifications::notification\",\"viewData\":[],\"format\":\"filament\"}',NULL,'2025-05-16 19:44:10','2025-05-16 19:44:10'),('a861f208-259e-4b78-a5d5-de6bdb7c9df3','Filament\\Notifications\\DatabaseNotification','App\\Models\\User',1,'{\"actions\":[],\"body\":\"Valor: R$ 35,33\\nProduto: Seguro do carro\\nNova data: 16\\/05\\/2025\\nM\\u00e9todo: Indefinido\\nStatus: D\\u00e9bito autom\\u00e1tico\",\"color\":null,\"duration\":\"persistent\",\"icon\":\"heroicon-o-pencil-square\",\"iconColor\":\"warning\",\"status\":null,\"title\":\"Transa\\u00e7\\u00e3o atualizada (D\\u00e9bito autom\\u00e1tico)\",\"view\":\"filament-notifications::notification\",\"viewData\":[],\"format\":\"filament\"}',NULL,'2025-05-16 19:41:04','2025-05-16 19:41:04'),('e612fb27-0dde-42fd-bdf2-bb7484788915','Filament\\Notifications\\DatabaseNotification','App\\Models\\User',1,'{\"actions\":[],\"body\":\"Valor: R$ 39,83\\nProduto: Conta de \\u00e1gua\\nNova data: 21\\/05\\/2025\\nM\\u00e9todo: Indefinido\\nStatus: Agendado\",\"color\":null,\"duration\":\"persistent\",\"icon\":\"heroicon-o-pencil-square\",\"iconColor\":\"warning\",\"status\":null,\"title\":\"Transa\\u00e7\\u00e3o atualizada (Agendado)\",\"view\":\"filament-notifications::notification\",\"viewData\":[],\"format\":\"filament\"}',NULL,'2025-05-19 13:58:34','2025-05-19 13:58:34'),('fb2b01a5-baba-4a05-94ea-a9b73cce6cfd','Filament\\Notifications\\DatabaseNotification','App\\Models\\User',1,'{\"actions\":[{\"name\":\"download_csv\",\"color\":null,\"event\":null,\"eventData\":[],\"dispatchDirection\":false,\"dispatchToComponent\":null,\"extraAttributes\":[],\"icon\":null,\"iconPosition\":\"before\",\"iconSize\":null,\"isOutlined\":false,\"isDisabled\":false,\"label\":\"Baixar .csv\",\"shouldClose\":false,\"shouldMarkAsRead\":true,\"shouldMarkAsUnread\":false,\"shouldOpenUrlInNewTab\":true,\"size\":\"sm\",\"tooltip\":null,\"url\":\"\\/filament\\/exports\\/2\\/download?format=csv\",\"view\":\"filament-actions::link-action\"},{\"name\":\"download_xlsx\",\"color\":null,\"event\":null,\"eventData\":[],\"dispatchDirection\":false,\"dispatchToComponent\":null,\"extraAttributes\":[],\"icon\":null,\"iconPosition\":\"before\",\"iconSize\":null,\"isOutlined\":false,\"isDisabled\":false,\"label\":\"Baixar .xlsx\",\"shouldClose\":false,\"shouldMarkAsRead\":true,\"shouldMarkAsUnread\":false,\"shouldOpenUrlInNewTab\":true,\"size\":\"sm\",\"tooltip\":null,\"url\":\"\\/filament\\/exports\\/2\\/download?format=xlsx\",\"view\":\"filament-actions::link-action\"}],\"body\":\"Your transaction item export has completed and 6 rows exported.\",\"color\":null,\"duration\":\"persistent\",\"icon\":\"heroicon-o-check-circle\",\"iconColor\":\"success\",\"status\":\"success\",\"title\":\"Exporta\\u00e7\\u00e3o completa\",\"view\":\"filament-notifications::notification\",\"viewData\":[],\"format\":\"filament\"}','2025-05-17 14:51:56','2025-05-17 14:51:49','2025-05-17 14:51:56');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'view transactions','web','2025-05-16 15:48:30','2025-05-16 15:48:30'),(2,'create transactions','web','2025-05-16 15:48:30','2025-05-16 15:48:30'),(3,'edit transactions','web','2025-05-16 15:48:30','2025-05-16 15:48:30'),(4,'delete transactions','web','2025-05-16 15:48:30','2025-05-16 15:48:30'),(5,'view transaction_items','web','2025-05-16 15:48:30','2025-05-16 15:48:30'),(6,'create transaction_items','web','2025-05-16 15:48:30','2025-05-16 15:48:30'),(7,'edit transaction_items','web','2025-05-16 15:48:30','2025-05-16 15:48:30'),(8,'delete transaction_items','web','2025-05-16 15:48:30','2025-05-16 15:48:30'),(9,'view accounts','web','2025-05-16 15:48:30','2025-05-16 15:48:30'),(10,'create accounts','web','2025-05-16 15:48:30','2025-05-16 15:48:30'),(11,'edit accounts','web','2025-05-16 15:48:30','2025-05-16 15:48:30'),(12,'delete accounts','web','2025-05-16 15:48:30','2025-05-16 15:48:30'),(13,'view users','web','2025-05-16 15:48:30','2025-05-16 15:48:30'),(14,'create users','web','2025-05-16 15:48:30','2025-05-16 15:48:30'),(15,'edit users','web','2025-05-16 15:48:30','2025-05-16 15:48:30'),(16,'delete users','web','2025-05-16 15:48:30','2025-05-16 15:48:30'),(17,'view roles','web','2025-05-16 15:48:30','2025-05-16 15:48:30'),(18,'create roles','web','2025-05-16 15:48:30','2025-05-16 15:48:30'),(19,'edit roles','web','2025-05-16 15:48:30','2025-05-16 15:48:30'),(20,'delete roles','web','2025-05-16 15:48:30','2025-05-16 15:48:30'),(21,'access dashboard','web','2025-05-16 15:48:30','2025-05-16 15:48:30');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1),(16,1),(17,1),(18,1),(19,1),(20,1),(21,1);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'ADMIN','web','2025-05-16 15:48:30','2025-05-16 15:48:30');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('hAxZdSYtYcZDY509KSpmL2g0W0FojF2fO6GolPbR',1,'192.168.1.18','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36','YToyOntzOjY6Il90b2tlbiI7czo0MDoiZ2YwSWVtenNQcEo5OXF3bjdGMm1yUng0QUF1dkpHUDhYdkpwb1pCRiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1747746608),('kUDOmpi8kf552MpryLLiZEChwXRRke3KODruiPh2',1,'192.168.1.18','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36','YTo4OntzOjY6Il90b2tlbiI7czo0MDoiODJlMUFKU0poaGV5MTJMQktDdXBNMndBUlVQUjlKTDloSVZiQ3dJMyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2MDoiJDJ5JDEyJHJhVXB3ajdrWE9HdnpUS3dtaWJnUE94Q01SU21jdExtdlkwOXVzQ1lFdzVBUlJvcUlTOVJ1IjtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo0MToiaHR0cDovLzE5Mi4xNjguMS4xODo4MDAwL2FkbWluL2NhdGVnb3JpZXMiO31zOjQwOiI2M2E2ZTY0NTIzYzA1ODBhZjhkYjc1MzYxYjRjNTNiZF9maWx0ZXJzIjtOO3M6NjoidGFibGVzIjthOjM6e3M6NDA6IjYzYTZlNjQ1MjNjMDU4MGFmOGRiNzUzNjFiNGM1M2JkX2ZpbHRlcnMiO2E6MTp7czoxODoicGF5bWVudF9kYXRlX3JhbmdlIjthOjI6e3M6MTA6InN0YXJ0X2RhdGUiO3M6MTA6IjIwMjUtMDUtMDEiO3M6ODoiZW5kX2RhdGUiO3M6MTA6IjIwMjUtMDctMzEiO319czo0MToiNjNhNmU2NDUyM2MwNTgwYWY4ZGI3NTM2MWI0YzUzYmRfcGVyX3BhZ2UiO3M6MjoiMjUiO3M6NDE6IjU3OWVlMzBiMWRhODUzZWVmZjAzNzE2M2RlMTcwMDg4X3Blcl9wYWdlIjtzOjM6ImFsbCI7fXM6ODoiZmlsYW1lbnQiO2E6MDp7fX0=',1747678891);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaction_items`
--

DROP TABLE IF EXISTS `transaction_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaction_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` bigint unsigned NOT NULL,
  `installment_number` int unsigned NOT NULL DEFAULT '1',
  `due_date` date NOT NULL COMMENT 'Data de vencimento',
  `amount` decimal(15,2) NOT NULL,
  `payment_date` date DEFAULT NULL COMMENT 'Data de pagamento',
  `status` enum('PAID','SCHEDULED','DEBIT','PENDING') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transaction_items_transaction_id_foreign` (`transaction_id`),
  CONSTRAINT `transaction_items_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction_items`
--

LOCK TABLES `transaction_items` WRITE;
/*!40000 ALTER TABLE `transaction_items` DISABLE KEYS */;
INSERT INTO `transaction_items` VALUES (1,1,1,'2025-01-21',893.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(2,2,1,'2025-01-27',513.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(3,3,1,'2025-01-01',691.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(4,4,1,'2025-02-08',947.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(5,5,1,'2025-02-22',530.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(6,6,1,'2025-02-15',281.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(7,7,1,'2025-03-01',763.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(8,8,1,'2025-03-08',102.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(9,9,1,'2025-03-07',133.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(10,10,1,'2025-04-26',900.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(11,11,1,'2025-04-24',462.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(12,12,1,'2025-04-07',884.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(13,13,1,'2025-05-09',516.00,'2025-05-16','PAID','2025-05-16 15:48:30','2025-05-16 19:27:12'),(14,14,1,'2025-05-12',373.00,'2025-05-14','PAID','2025-05-16 15:48:30','2025-05-16 18:25:46'),(15,15,1,'2025-05-17',298.00,'2025-05-14','PAID','2025-05-16 15:48:30','2025-05-16 18:26:44'),(16,16,1,'2025-06-22',330.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(17,17,1,'2025-06-13',778.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(18,18,1,'2025-06-14',361.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(19,19,1,'2025-07-21',603.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(20,20,1,'2025-07-17',268.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(21,21,1,'2025-07-19',868.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(22,22,1,'2025-08-20',614.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(23,23,1,'2025-08-04',266.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(24,24,1,'2025-08-23',883.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(25,25,1,'2025-09-09',996.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(26,26,1,'2025-09-04',398.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(27,27,1,'2025-09-25',393.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(28,28,1,'2025-10-25',316.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(29,29,1,'2025-10-02',237.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(30,30,1,'2025-10-05',587.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(31,31,1,'2025-11-19',413.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(32,32,1,'2025-11-06',359.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(33,33,1,'2025-11-25',419.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(34,34,1,'2025-12-03',209.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(35,35,1,'2025-12-19',481.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(36,36,1,'2025-12-05',453.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(37,37,1,'2025-02-20',256.33,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(38,37,2,'2025-03-20',256.33,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(39,37,3,'2025-04-20',256.34,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(40,38,1,'2025-04-03',35.33,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(41,38,2,'2025-05-03',35.33,'2025-05-16','PAID','2025-05-16 15:48:30','2025-05-16 19:44:10'),(42,38,3,'2025-06-03',35.33,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(43,38,4,'2025-07-03',35.33,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(44,38,5,'2025-08-03',35.33,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(45,38,6,'2025-09-03',35.35,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(46,39,1,'2025-06-28',44.20,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(47,39,2,'2025-07-28',44.20,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(48,39,3,'2025-08-28',44.20,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(49,39,4,'2025-09-28',44.20,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(50,39,5,'2025-10-28',44.20,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(51,40,1,'2025-01-05',65.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(52,40,2,'2025-02-05',65.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(53,40,3,'2025-03-05',65.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(54,40,4,'2025-04-05',65.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(55,40,5,'2025-05-05',65.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(56,40,6,'2025-06-05',65.00,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(57,41,1,'2025-01-01',39.83,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(58,41,2,'2025-02-01',39.83,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(59,41,3,'2025-03-01',39.83,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(60,41,4,'2025-04-01',39.83,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30'),(61,41,5,'2025-05-01',39.83,'2025-05-21','SCHEDULED','2025-05-16 15:48:30','2025-05-19 13:58:31'),(62,41,6,'2025-06-01',39.85,NULL,'PENDING','2025-05-16 15:48:30','2025-05-16 15:48:30');
/*!40000 ALTER TABLE `transaction_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `account_id` bigint unsigned DEFAULT NULL,
  `card_id` bigint unsigned DEFAULT NULL,
  `category_id` bigint unsigned NOT NULL,
  `type` enum('INCOME','EXPENSE') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `method` enum('CASH','ACCOUNT','CARD') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_recurring` tinyint(1) NOT NULL DEFAULT '0',
  `recurrence_interval` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transactions_user_id_foreign` (`user_id`),
  KEY `transactions_account_id_foreign` (`account_id`),
  KEY `transactions_card_id_foreign` (`card_id`),
  KEY `transactions_category_id_foreign` (`category_id`),
  CONSTRAINT `transactions_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`),
  CONSTRAINT `transactions_card_id_foreign` FOREIGN KEY (`card_id`) REFERENCES `cards` (`id`),
  CONSTRAINT `transactions_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (1,1,2,NULL,19,'EXPENSE',893.00,'ACCOUNT','2025-01-21','Seguro do carro',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(2,1,2,NULL,20,'INCOME',513.00,'ACCOUNT','2025-01-27','Lucros de negócio próprio',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(3,1,1,NULL,20,'INCOME',691.00,'ACCOUNT','2025-01-01','Freelance de design',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(4,1,1,NULL,11,'EXPENSE',947.00,'ACCOUNT','2025-02-08','Seguro do carro',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(5,1,2,NULL,23,'EXPENSE',530.00,'ACCOUNT','2025-02-22','Seguro do carro',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(6,1,1,NULL,22,'INCOME',281.00,'ACCOUNT','2025-02-15','Aluguel recebido',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(7,1,2,NULL,7,'INCOME',763.00,'ACCOUNT','2025-03-01','Lucros de negócio próprio',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(8,1,2,NULL,5,'EXPENSE',102.00,'ACCOUNT','2025-03-08','Conta de luz',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(9,1,1,NULL,11,'INCOME',133.00,'ACCOUNT','2025-03-07','Aluguel recebido',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(10,1,1,NULL,2,'EXPENSE',900.00,'ACCOUNT','2025-04-26','Prestação do carro',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(11,1,2,NULL,25,'INCOME',462.00,'ACCOUNT','2025-04-24','Rendimento de investimentos',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(12,1,2,NULL,20,'INCOME',884.00,'ACCOUNT','2025-04-07','Lucros de negócio próprio',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(13,1,2,NULL,13,'INCOME',516.00,'ACCOUNT','2025-05-09','Salário',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(14,1,2,NULL,2,'EXPENSE',373.00,'ACCOUNT','2025-05-12','Prestação do carro',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(15,1,2,NULL,6,'EXPENSE',298.00,'ACCOUNT','2025-05-17','Assinatura Netflix',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(16,1,1,NULL,20,'INCOME',330.00,'ACCOUNT','2025-06-22','Freelance de design',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(17,1,2,NULL,10,'INCOME',778.00,'ACCOUNT','2025-06-13','Rendimento de investimentos',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(18,1,2,NULL,8,'EXPENSE',361.00,'ACCOUNT','2025-06-14','Plano de saúde',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(19,1,1,NULL,8,'INCOME',603.00,'ACCOUNT','2025-07-21','Lucros de negócio próprio',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(20,1,1,NULL,10,'EXPENSE',268.00,'ACCOUNT','2025-07-17','Supermercado',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(21,1,2,NULL,4,'EXPENSE',868.00,'ACCOUNT','2025-07-19','Manutenção do carro',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(22,1,2,NULL,7,'INCOME',614.00,'ACCOUNT','2025-08-20','Lucros de negócio próprio',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(23,1,1,NULL,19,'INCOME',266.00,'ACCOUNT','2025-08-04','Reembolso da empresa',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(24,1,1,NULL,8,'EXPENSE',883.00,'ACCOUNT','2025-08-23','Conta de luz',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(25,1,2,NULL,15,'EXPENSE',996.00,'ACCOUNT','2025-09-09','Mensalidade da escola',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(26,1,1,NULL,26,'INCOME',398.00,'ACCOUNT','2025-09-04','Rendimento de investimentos',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(27,1,2,NULL,15,'EXPENSE',393.00,'ACCOUNT','2025-09-25','Mensalidade da escola',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(28,1,1,NULL,19,'INCOME',316.00,'ACCOUNT','2025-10-25','Salário',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(29,1,1,NULL,23,'EXPENSE',237.00,'ACCOUNT','2025-10-02','Fatura do cartão de crédito',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(30,1,2,NULL,26,'INCOME',587.00,'ACCOUNT','2025-10-05','Lucros de negócio próprio',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(31,1,2,NULL,10,'INCOME',413.00,'ACCOUNT','2025-11-19','Salário',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(32,1,1,NULL,18,'INCOME',359.00,'ACCOUNT','2025-11-06','Reembolso da empresa',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(33,1,1,NULL,14,'EXPENSE',419.00,'ACCOUNT','2025-11-25','Assinatura Netflix',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(34,1,1,NULL,25,'EXPENSE',209.00,'ACCOUNT','2025-12-03','Plano de saúde',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(35,1,2,NULL,13,'EXPENSE',481.00,'ACCOUNT','2025-12-19','Manutenção do carro',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(36,1,2,NULL,9,'EXPENSE',453.00,'ACCOUNT','2025-12-05','Plano de saúde',0,1,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(37,1,NULL,1,18,'EXPENSE',769.00,'CARD','2025-02-20','IPVA',1,3,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(38,1,NULL,1,8,'EXPENSE',212.00,'CARD','2025-04-03','Seguro do carro',1,6,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(39,1,NULL,1,4,'EXPENSE',221.00,'CARD','2025-06-28','Conta de luz',1,5,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(40,1,NULL,2,17,'EXPENSE',390.00,'CARD','2025-01-05','Assinatura Netflix',1,6,'2025-05-16 15:48:30','2025-05-16 15:48:30'),(41,1,NULL,1,18,'EXPENSE',239.00,'CARD','2025-01-01','Conta de água',1,6,'2025-05-16 15:48:30','2025-05-16 15:48:30');
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin','admin@admin.com',NULL,'$2y$12$raUpwj7kXOGvzTKwmibgPOxCMRSmctLmvY09usCYEw5ARRoqIS9Ru','AXT03y3POAqBKGrBqgvHLBij2SD6sOJZbPdJMw1x48XK95mUIyANkGGGGrOw','2025-05-16 15:48:30','2025-05-16 15:48:30');
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

-- Dump completed on 2025-05-20 14:25:54
