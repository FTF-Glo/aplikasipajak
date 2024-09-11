-- MySQL dump 10.13  Distrib 5.7.34, for Linux (x86_64)
--
-- Host: localhost    Database: db_pajak_mobile
-- ------------------------------------------------------
-- Server version	5.7.34

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
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `hp` varchar(30) NOT NULL,
  `jabatan` varchar(100) NOT NULL,
  `is_active` int(1) NOT NULL,
  `is_admin` int(11) NOT NULL,
  `branch` int(11) NOT NULL,
  `del` int(1) NOT NULL,
  `remember_token` varchar(100) NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime NOT NULL,
  `lastlogin` datetime NOT NULL,
  `images` varchar(255) NOT NULL,
  `tokenid` varchar(15) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
INSERT INTO `admin` VALUES (1,'admin','$2y$10$XHttzH995XIxvH6koxPmBOJYt1ShCQH4Ya9Y2/53AcZLfSBkqYcgG','admin','aldiyan@kotabiru.com','081546010777','Super Admin',1,1,0,0,'','2019-09-13 08:34:14','0000-00-00 00:00:00','2021-04-19 12:44:02','',''),(2,'user1','$2y$10$CtsTDzP6JDdy6TpUowv7EuruniQV78g8BPyQzEIZHlzgXPlJyagAO','user1','','','',0,0,0,0,'','2019-09-13 10:35:57','0000-00-00 00:00:00','2019-09-13 11:04:24','',''),(5,'user2','$2y$10$QwqAf.zGhHNpBlK9P0ipCu/OFW1/EU4aznsr7caPqv3SnmJ6O.R1G','User 2','','','',0,0,0,0,'','2019-09-19 09:37:52','2019-09-19 10:24:26','2019-09-20 19:13:39','',''),(6,'admin2','$2y$10$EFKChfPqXaXc23nQ.aRRD.pSlzFDCGbPlehUDgykNN2swhT0i5YKW','test','test@gmail.com','te234243','test',0,0,0,0,'','2020-02-14 17:13:15','2020-02-14 17:13:15','0000-00-00 00:00:00','','');
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_access`
--

DROP TABLE IF EXISTS `admin_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) NOT NULL,
  `id_menu` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_access`
--

LOCK TABLES `admin_access` WRITE;
/*!40000 ALTER TABLE `admin_access` DISABLE KEYS */;
INSERT INTO `admin_access` VALUES (1,2,1),(16,5,1),(17,5,2),(18,5,13),(19,5,3);
/*!40000 ALTER TABLE `admin_access` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `badan_usaha`
--

DROP TABLE IF EXISTS `badan_usaha`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `badan_usaha` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `badan_usaha`
--

LOCK TABLES `badan_usaha` WRITE;
/*!40000 ALTER TABLE `badan_usaha` DISABLE KEYS */;
INSERT INTO `badan_usaha` VALUES (1,'Perseroaan Terbatas (PT)',''),(2,'Yayasan','');
/*!40000 ALTER TABLE `badan_usaha` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `config`
--

DROP TABLE IF EXISTS `config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(100) NOT NULL,
  `value` varchar(250) NOT NULL,
  `satuan` varchar(100) NOT NULL,
  `sort` int(5) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `config`
--

LOCK TABLES `config` WRITE;
/*!40000 ALTER TABLE `config` DISABLE KEYS */;
INSERT INTO `config` VALUES (1,'Title','c_title','Sistem Informasi Pajak Daerah','',1),(13,'Meta Description','c_meta_desc','Sistem Informasi Pajak Daerah','',2),(19,'API Key','api_key','RetriX369','',3),(20,'Nomor WA','contact_whatsapp','6283898989898','',4),(21,'Isi Pesan WA','contact_whatsapp_msg','Halo, saya ingin bertanya mengenai ABC','',5),(22,'Aktifkan Pesan WA (0, 1)','contact_whatsapp_enabled','1','',6),(23,'Link Pendaftaran WP','external_link_daftar_wp','http://36.92.151.83:2000/registrasi/registrasi.php','',7),(24,'Link Pendaftaran Notaris','external_link_daftar_notaris','http://36.92.151.83:2030/registrasi/registrasi.php','',8),(25,'Link Pelaporan 1','external_link_pelaporan_1','http://36.92.151.83:2000/','',9),(26,'Link Pelaporan 2','external_link_pelaporan_2','http://36.92.151.83:2030/','',10),(27,'API Gateway Pajak','api_gateway_pajak','http://127.0.0.1:2020/GetBillingPajak','',9),(28,'API Gateway PBB','api_gateway_pbb','http://127.0.0.1:2020/GetBillingPBB','',10),(29,'API Gateway Auth','api_gateway_auth','ftfuser:lalaland2020','',11),(30,'Nama Wilayah','wilayah_nama','Lampung Selatan','',12),(31,'Alamat Wilayah','wilayah_alamat','Jl. Mustafa Kemal No. 45 Kalianda Kode Pos 35513','',13),(32,'Nomor Telp','contact_telp','0727-322242','',14),(33,'Alamat Email','contact_email','email@email.com','',15);
/*!40000 ALTER TABLE `config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_pbb`
--

DROP TABLE IF EXISTS `data_pbb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_pbb` (
  `id` int(11) NOT NULL,
  `id_users_usaha` int(11) NOT NULL,
  `nop` int(11) NOT NULL,
  `address` int(11) NOT NULL,
  `rt` int(11) NOT NULL,
  `rw` int(11) NOT NULL,
  `kel` int(11) NOT NULL,
  `kec` int(11) NOT NULL,
  `kab` int(11) NOT NULL,
  `prov` int(11) NOT NULL,
  `luas_bumi` int(11) NOT NULL,
  `kelas_bumi` varchar(5) NOT NULL,
  `luas_bangunan` int(11) NOT NULL,
  `kelas_bangunan` varchar(5) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_pbb`
--

LOCK TABLES `data_pbb` WRITE;
/*!40000 ALTER TABLE `data_pbb` DISABLE KEYS */;
/*!40000 ALTER TABLE `data_pbb` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_pbb_masa`
--

DROP TABLE IF EXISTS `data_pbb_masa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_pbb_masa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_pbb` int(11) NOT NULL,
  `tahun` int(11) NOT NULL,
  `njop` int(11) NOT NULL,
  `njoptkp` int(11) NOT NULL,
  `njop_pbb` int(11) NOT NULL,
  `njkp_percent` int(11) NOT NULL,
  `obb_deb_percent` int(11) NOT NULL,
  `stimulus` int(11) NOT NULL,
  `expired_date` date NOT NULL,
  `print_date` date NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_pbb_masa`
--

LOCK TABLES `data_pbb_masa` WRITE;
/*!40000 ALTER TABLE `data_pbb_masa` DISABLE KEYS */;
/*!40000 ALTER TABLE `data_pbb_masa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_pbb_objek`
--

DROP TABLE IF EXISTS `data_pbb_objek`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_pbb_objek` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_pbb_masa` int(11) NOT NULL,
  `luas` float NOT NULL,
  `kelas` varchar(5) NOT NULL,
  `njop` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_pbb_objek`
--

LOCK TABLES `data_pbb_objek` WRITE;
/*!40000 ALTER TABLE `data_pbb_objek` DISABLE KEYS */;
/*!40000 ALTER TABLE `data_pbb_objek` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_pbb_total`
--

DROP TABLE IF EXISTS `data_pbb_total`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_pbb_total` (
  `id` int(11) NOT NULL,
  `id_pbb_masa` int(11) NOT NULL,
  `njop` int(11) NOT NULL,
  `njoptkp` int(11) NOT NULL,
  `njkp_percent` int(11) NOT NULL,
  `debt_percent` int(11) NOT NULL,
  `stimulus` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_pbb_total`
--

LOCK TABLES `data_pbb_total` WRITE;
/*!40000 ALTER TABLE `data_pbb_total` DISABLE KEYS */;
/*!40000 ALTER TABLE `data_pbb_total` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu`
--

DROP TABLE IF EXISTS `menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `module` varchar(255) NOT NULL,
  `menu_categories_id` int(11) NOT NULL,
  `icon` varchar(50) NOT NULL,
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu`
--

LOCK TABLES `menu` WRITE;
/*!40000 ALTER TABLE `menu` DISABLE KEYS */;
INSERT INTO `menu` VALUES (1,'Pengguna','tools_users/view',1,'fa fa-users',1),(2,'Konfigurasi','tools_config/view',1,'fa fa-cogs',2),(42,'Provinsi','master_loc/prov',4,'',1),(43,'Kabupaten','master_loc/kab',4,'',2),(44,'Kecamatan','master_loc/kec',4,'',3),(45,'Kelurahan','master_loc/kel',4,'',4),(46,'Badan Usaha','master/badan_usaha',3,'',2),(47,'Jenis Pajak','master/pajak_type',3,'',1),(48,'Kelas Usaha','master/kelas_usaha',3,'',4),(49,'Info Jenis Pajak','master/info',3,'',4),(50,'Hiburan','parameter/hiburan',5,'',0),(51,'Minerba','parameter/minerba',5,'',0),(52,'Bidang Usaha','master/usaha',3,'',3),(53,'Air Tanah','parameter/airtanah',5,'',0),(54,'List','wp/listing',6,'',1),(55,'Validasi','wp/validasi_usaha',6,'',3),(56,'Usaha','wp/usaha',6,'',2),(57,'Penetapan','target/penetapan',7,'',1),(58,'List','target/listing',7,'',2),(60,'Terbayar','sppt/done',8,'',1),(61,'Belum Terbayar','sppt/pending',8,'',2),(62,'Halaman Informasi','page/listing\r\n',1,'icon-info',3),(63,'Verifikasi','sppt/verification',8,'',0),(64,'Ditolak','sppt/reject',8,'',4);
/*!40000 ALTER TABLE `menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_categories`
--

DROP TABLE IF EXISTS `menu_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `icon` varchar(100) NOT NULL,
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_categories`
--

LOCK TABLES `menu_categories` WRITE;
/*!40000 ALTER TABLE `menu_categories` DISABLE KEYS */;
INSERT INTO `menu_categories` VALUES (1,'Tools','fa fa-cogs',200),(3,'Master','fa fa-database',198),(4,'Master Lokasi','fa fa-map-marker',199),(5,'Parameter Pajak','fa fa-tasks',100),(6,'Wajib Pajak','fa fa-users',50),(7,'Target','fa fa-bullseye',197),(8,'Transaksi','fa fa-qrcode',49);
/*!40000 ALTER TABLE `menu_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page`
--

DROP TABLE IF EXISTS `page`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `code` varchar(100) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page`
--

LOCK TABLES `page` WRITE;
/*!40000 ALTER TABLE `page` DISABLE KEYS */;
INSERT INTO `page` VALUES (1,'Tentang Online Pajak','Tentang Online Pajak - Tools > Halaman Informasi','about'),(2,'Syarat dan Ketentuan','Syarat dan Ketentuan - Tools > Halaman Informasi','term');
/*!40000 ALTER TABLE `page` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pajak_airtanah`
--

DROP TABLE IF EXISTS `pajak_airtanah`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pajak_airtanah` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_class` int(11) NOT NULL,
  `ranges` varchar(100) NOT NULL,
  `nilai` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pajak_airtanah`
--

LOCK TABLES `pajak_airtanah` WRITE;
/*!40000 ALTER TABLE `pajak_airtanah` DISABLE KEYS */;
INSERT INTO `pajak_airtanah` VALUES (1,1,'0,100',1853),(2,1,'101,500',1937),(3,1,'501,1000',2021),(4,1,'1001,2500',2106),(5,2,'0,100',1516),(6,2,'101,500',1567),(7,3,'0,100',1684),(8,3,'101,500',1752),(9,4,'0,100',1348),(10,4,'101,500',1381),(11,4,'501,1000',1415),(12,4,'1001,2500',1449),(13,5,'0,100',1179),(14,5,'101,500',1196),(15,5,'501,1000',1213),(16,5,'1001,2500',1230),(17,1,'2500,',2190),(18,5,'2500,',1247),(19,2,'501,1000',1617),(20,2,'1001,2500',1668),(21,2,'2500,',1718),(22,3,'501,1000',1819),(23,3,'1001,2500',1887),(24,3,'2500,',1954),(25,4,'2500,',1482),(26,6,'0,100',2021),(27,6,'101,500',4717),(28,6,'501,1000',7412),(29,6,'1001,2500',10107),(30,6,'2500,',12802);
/*!40000 ALTER TABLE `pajak_airtanah` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pajak_content`
--

DROP TABLE IF EXISTS `pajak_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pajak_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_type` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `icon` varchar(100) NOT NULL,
  `sort` int(11) NOT NULL,
  `hide` smallint(1) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pajak_content`
--

LOCK TABLES `pajak_content` WRITE;
/*!40000 ALTER TABLE `pajak_content` DISABLE KEYS */;
INSERT INTO `pajak_content` VALUES (1,1,'Bayar Pajak Air Tanah','bayar','tax-payment.png',1,1),(2,1,'Info Pajak Air Tanah','info','tax-info.png',2,0),(3,1,'Status Pembayaran Pajak Air Tanah','kalkulator','tax-calculator.png',3,0),(4,2,'Bayar Pajak Hiburan','bayar','tax-payment.png',1,1),(5,2,'Info Pajak Hiburan','info','tax-info.png',2,0),(6,2,'Status Pembayaran Pajak Hiburan','kalkulator','tax-calculator.png',3,0),(7,3,'Bayar Pajak Hotel','bayar','tax-payment.png',1,1),(8,3,'Info Pajak Hotel','info','tax-info.png',2,0),(9,3,'Status Pembayaran Pajak Hotel','kalkulator','tax-calculator.png',3,0),(10,4,'Bayar Pajak Minerba','bayar','tax-payment.png',1,1),(11,4,'Info Pajak Minerba','info','tax-info.png',2,0),(12,4,'Status Pembayaran Pajak Minerba','kalkulator','tax-calculator.png',3,0),(13,5,'Bayar Pajak Parkir','bayar','tax-payment.png',1,1),(14,5,'Info Pajak Parkir','info','tax-info.png',2,0),(15,5,'Status Pembayaran Pajak Parkir','kalkulator','tax-calculator.png',3,0),(16,6,'Bayar Pajak PBB','bayar','tax-payment.png',1,1),(17,6,'Info Pajak PBB','info','tax-info.png',2,0),(19,7,'Bayar Pajak Reklame','bayar','tax-payment.png',1,1),(20,7,'Info Pajak Reklame','info','tax-info.png',2,0),(21,7,'Status Pembayaran Pajak Reklame','kalkulator','tax-calculator.png\r\n',3,0),(22,8,'Bayar Pajak Restaurant','bayar','tax-payment.png',1,1),(23,8,'Info Pajak Restaurant','info','tax-info.png',2,0),(24,8,'Status Pembayaran Pajak Restoran','kalkulator','tax-calculator.png',3,0),(25,9,'Bayar Pajak BPHTB','bayar','tax-payment.png',1,1),(26,9,'Info Pajak BPHTB','info','tax-info.png',2,0),(27,9,'Status Pembayaran Pajak BPHTB','kalkulator','tax-calculator.png',3,0),(28,6,'Status Pembayaran Pajak PBB','kalkulator','tax-calculator.png',3,0);
/*!40000 ALTER TABLE `pajak_content` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pajak_hiburan_omzet`
--

DROP TABLE IF EXISTS `pajak_hiburan_omzet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pajak_hiburan_omzet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_hiburan` int(11) NOT NULL,
  `range_min` int(11) NOT NULL,
  `range_max` int(11) NOT NULL,
  `nilai` float NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pajak_hiburan_omzet`
--

LOCK TABLES `pajak_hiburan_omzet` WRITE;
/*!40000 ALTER TABLE `pajak_hiburan_omzet` DISABLE KEYS */;
INSERT INTO `pajak_hiburan_omzet` VALUES (1,1,0,10000000,0.5),(2,1,10000001,50000000,0.75),(3,1,50000001,100000000,1),(4,1,100000001,0,2),(5,2,0,10000000,0.5),(6,2,10000001,50000000,0.75),(7,2,50000001,100000000,1),(8,2,100000001,0,2),(9,3,0,10000000,0.5),(10,3,10000001,50000000,0.75),(11,3,50000001,100000000,1),(12,3,100000001,0,2),(13,4,0,10000000,0.5),(14,4,10000001,50000000,0.75),(15,4,50000001,100000000,1),(16,4,100000001,0,2),(17,5,0,10000000,0.5),(18,5,10000001,50000000,0.75),(19,5,50000001,100000000,1),(20,5,100000001,0,2),(21,6,0,10000000,0.5),(22,6,10000001,50000000,0.75),(23,6,50000001,100000000,1),(24,6,100000001,0,2),(25,7,0,10000000,0.5),(26,7,10000001,50000000,0.75),(27,7,50000001,100000000,1),(28,7,100000001,0,2),(29,8,0,10000000,0.5),(30,8,10000001,50000000,0.75),(31,8,50000001,100000000,1),(32,8,100000001,0,2),(33,9,0,10000000,0.5),(34,9,10000001,50000000,0.75),(35,9,50000001,100000000,1),(36,9,100000001,0,2),(37,10,0,10000000,0.5),(38,10,10000001,50000000,0.75),(39,10,50000001,100000000,1),(40,10,100000001,0,2),(41,11,0,10000000,0.5),(42,11,10000001,50000000,0.75),(43,11,50000001,100000000,1),(44,11,100000001,0,2);
/*!40000 ALTER TABLE `pajak_hiburan_omzet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pajak_hiburan_type`
--

DROP TABLE IF EXISTS `pajak_hiburan_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pajak_hiburan_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `tax` float NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pajak_hiburan_type`
--

LOCK TABLES `pajak_hiburan_type` WRITE;
/*!40000 ALTER TABLE `pajak_hiburan_type` DISABLE KEYS */;
INSERT INTO `pajak_hiburan_type` VALUES (1,'Film',11,'-'),(2,'Pagelaran Musik, Tari',15,'-'),(3,'Kontes Kecantikan, Binaraga',15,'-'),(4,'Pameran',10,'-'),(5,'Diskotik, Karaoke',25,'-'),(6,'Golf, Billiard, Bowling',10,'-'),(7,'Sirkus, Akrobat, Sulap',10,'-'),(8,'Pacuan Kuda & Kendaraan Bermotor',15,'-'),(9,'Permainan Ketangkasan',10,'-'),(10,'Panti Pijat, Refleksi DLL',10,'-'),(11,'Pertandingan Olahraga',5,'-');
/*!40000 ALTER TABLE `pajak_hiburan_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pajak_info`
--

DROP TABLE IF EXISTS `pajak_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pajak_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_type` int(11) NOT NULL,
  `page` varchar(20) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pajak_info`
--

LOCK TABLES `pajak_info` WRITE;
/*!40000 ALTER TABLE `pajak_info` DISABLE KEYS */;
INSERT INTO `pajak_info` VALUES (1,1,'info','<p><strong>Nama, Objek, Subjek dan Wajib Pajak</strong></p>\r\n\r\n<p>Setiap pengambilan dan/atau pemanfaatan air tanah dipungut pajak dengan nama Pajak Air Tanah</p>\r\n\r\n<ol style=\"list-style-type: none\">\r\n	<li>1. Objek Pajak Air Tanah adalah pengambilan dan/atau pemanfaatan air tanah</li>\r\n	<li>2. Dikecualikan dari objek Pajak Air Tanah adalah pengambilan dan/atau pemanfaatan air tanah untuk keperluan dasar rumah tangga, pengairan pertanian dan perikanan rakyat, serta peribadatan</li>\r\n	<li>3. Subjek Pajak Air Tanah adalah orang pribadi atau Badan yang melakukan pengambilan dan/atau pemanfaatan air tanah</li>\r\n	<li>4. Wajib Pajak Air Tanah adalah orang pribadi atau Badan yang melakukan pengambilan dan/atau pemanfaatan air tanah</li>\r\n</ol>\r\n\r\n<p><strong>Dasar Pengenaan, Tarif dan Cara Perhitungan Pajak</strong></p>\r\n\r\n<p>Dasar pengenaan Pajak Air Tanah adalah Nilai Perolehan Air Tanah. Adapun Nilai Perolehan Air Tanah dinyatakan dalam rupiah yang dihitung dengan mempertimbangkan sebagian atau seluruh faktor-faktor berikut</p>\r\n\r\n<ol style=\"list-style-type: none\">\r\n	<li>1. jenis sumber air</li>\r\n	<li>2. lokasi sumber air</li>\r\n	<li>3. tujuan pengambilan dan/atau pemanfaatan air</li>\r\n	<li>4. volume air yang diambil dan/atau dimanfaatkan</li>\r\n	<li>5. kualitas air; dan</li>\r\n	<li>6. tingkat kerusakan lingkungan yang diakibatkan oleh pengambilan dan/atau pemanfaatan air</li>\r\n</ol>\r\n\r\n<p>Penggunaan faktor-faktor sebagaimana dimaksud di atas akan disesuaikan dengan kondisi Daerah. Sedangkan besar Nilai Perolehan Air Tanah akan diatur lebih lanjut dengan Peraturan Bupati</p>\r\n\r\n<p>Tarif Pajak Air Tanah ditetapkan sebesar 20% (dua puluh perseratus). Besarnya pokok Pajak Air Tanah yang terutang dihitung dengan cara mengalikan tarif sebagaimana dimaksud dalam Pasal 49 dengan dasar pengenaan pajak sebagaimana dimaksud dalam Pasal 48</p>'),(2,2,'info','<p>Pajak Hiburan dipungut pajak atas setiap penyelenggaraan hiburan. Objek Pajak Hiburan adalah jasa penyelenggaraan Hiburan dengan dipungut bayaran.</p>\r\n\r\n<p>Hiburan sebagaimana dimaksud meliputi :</p>\r\n\r\n<ol>\r\n	<li>tontonan film;</li>\r\n	<li>pagelaran kesenian, musik, tari dan/atau busana;</li>\r\n	<li>kontes kecantikan, binaraga dan sejenisnya;</li>\r\n	<li>pameran, yang meliputi pula wisat alam, kolam renang;</li>\r\n	<li>diskotik, karaoke, klab malam dan sejenisnya;</li>\r\n	<li>sirkus, akrobat dan sulap;</li>\r\n	<li>permainan bilyar, golf, dan boling;</li>\r\n	<li>pacuan kuda, kendaraan bermotor dan permainan ketangkasan;</li>\r\n	<li>panti pijat, refleksi, mandi uap/spa dan pusat kebugaran (fitness center); dan</li>\r\n	<li>pertandingan olahraga.</li>\r\n</ol>\r\n\r\n<p>Tidak termasuk objek pajak sebagaimana dimaksud pada ayat (2) adalah pagelaran kesenian rakyat/tradisional dalam rangka usaha pelestarian kesenian dan budaya tradisional Daerah.</p>\r\n\r\n<ol>\r\n	<li>Subjek Pajak Hiburan adalah orang pribadi atau Badan yang menikmati hiburan.</li>\r\n	<li>Wajib Pajak Hiburan adalah orang pribadi atau Badan yang menyelenggarakan hiburan.</li>\r\n</ol>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><strong>Dasar Pengenaan, Tarif, Cara Penghitungan, dan Masa Pajak Hiburan</strong></p>\r\n\r\n<ol>\r\n	<li>Dasar pengenaan Pajak Hiburan adalah jumlah uang yang diterima atau yang seharusnya diterima oleh penyelenggara Hiburan.</li>\r\n	<li>Jumlah uang yang seharusnya diterima sebagaimana dimaksud&nbsp; termasuk potongan harga dan tiket cuma-cuma diberikan kepada penerima jasa&nbsp; Hiburan.</li>\r\n</ol>\r\n\r\n<p>Tarif Pajak Hiburan ditetapkan sebagai berikut :</p>\r\n\r\n<ol>\r\n	<li>tontonan film sebesar 10 % ( Sepuluh per seratus );</li>\r\n	<li>pagelaran kesenian, musik, tari dan/atau busana sebesar 20% ( Dua puluh per seratus );</li>\r\n	<li>kontes kecantikan, binaraga dan sejenisnya sebesar 10% ( Sepuluh per seratus );</li>\r\n	<li>pameran sebesar 10% ( Sepuluh per seratus );</li>\r\n	<li>diskotik, karaoke, klab malam dan sejenisnya sebesar 30 % ( Tiga puluh per seratus );</li>\r\n	<li>sirkus, akrobat dan sulap sebesar 10% ( Sepuluh per seratus );</li>\r\n	<li>permainan bilyard, golf, dan bolling sebesar 20% ( Dua puluh per seratus );</li>\r\n	<li>pacuan kuda dan kendaraan bermotor sebesar 20% ( Dua puluh per seratus );</li>\r\n	<li>permainan ketangkasan sebesar 15% ( Lima belas per seratus );</li>\r\n	<li>panti pijat, refleksi, mandi uap/spa dan pusat kebugaran (fitnes center) sebesar 25 % ( Dua puluh lima per seratus); dan</li>\r\n	<li>pertandingan olah raga sebesar 10% ( Sepuluh per seratus ).</li>\r\n</ol>\r\n\r\n<p>Besaran pokok Pajak Hiburan yang terutang dihitung dengan cara mengalikan tarif dengan dasar pengenaan pajak</p>\r\n\r\n<p>Masa Pajak Hiburan adalah jangka waktu yang lamanya 1 (satu) bulan takwim, untuk penyelenggaraan hiburan dalam jangka waktu 1 (satu) bulan atau lebih, atau jangka waktu yang lamanya sama dengan jangka waktu penyelenggaraan hiburan, untuk penyelenggaraan hiburan yang kurang dari 1 (satu) bulan.</p>\r\n'),(3,3,'info','<p><strong>Objek Pajak Hotel</strong></p>\r\n\r\n<p>Objek Pajak Hotel adalah pelayanan yang disediakan oleh hotel dengan pembayaran, termasuk jasa penunjang sebagai kelengkapan hotel yang sifatnya memberikan kemudahan dan kenyamanan, termasuk fasilitas olahraga dan hiburan</p>\r\n\r\n<p>Jasa penunjang meliputi fasilitas telepon, faksimile, teleks, internet, fotokopi, pelayanan cuci, seterika, transportasi, dan fasilitas sejenis lainnya yang disediakan atau dikelola hotel</p>\r\n\r\n<p>Adapun yang tidak termasuk objek Pajak Hotel adalah sebagai berikut:</p>\r\n\r\n<ol>\r\n	<li>1. jasa tempat tinggal asrama yang diselenggarakan oleh Pemerintah atau Pemerintah Daerah</li>\r\n	<li>2. jasa sewa apartemen, kondominium, dan sejenisnya</li>\r\n	<li>3. jasa tempat tinggal di pusat pendidikan atau kegiatan keagamaan</li>\r\n	<li>4. jasa tempat tinggal di rumah sakit, asrama perawat, panti jompo, panti asuhan, dan panti sosial lainnya yang sejenis; dan</li>\r\n	<li>5. jasa biro perjalanan atau perjalanan wisata yang diselenggarakan oleh hotel yang dapat dimanfaatkan oleh umum</li>\r\n</ol>\r\n\r\n<p><strong>Subjek Pajak Hotel</strong></p>\r\n\r\n<p>Subjek Pajak Hotel adalah orang pribadi atau Badan yang melakukan pembayaran kepada orang pribadi atau Badan yang mengusahakan hotel</p>\r\n\r\n<p><strong>Wajib Pajak Hotel</strong></p>\r\n\r\n<p>Wajib Pajak Hotel adalah orang pribadi atau Badan yang mengusahakan hotel</p>\r\n\r\n<p><strong>Dasar Pengenaan, Tarif dan Cara Perhitungan Pajak</strong></p>\r\n\r\n<p>Dasar pengenaan Pajak Hotel adalah jumlah pembayaran atau yang seharusnya dibayar kepada Hotel</p>\r\n\r\n<p>Adapun Tarif Pajak Hotel ditetapkan dan berlaku di Kabupaten Boyolali adalah sebesar 10% (sepuluh perseratus).</p>\r\n\r\n<p>Besaran pokok Pajak Hotel yang terutang dihitung dengan cara mengalikan tarif dengan dasar pengenaan pajak</p>\r\n'),(4,4,'info','<p><strong>Pengertian</strong></p>\r\n\r\n<p>Pajak Mineral Bukan Logam dan Batuan adalah pajak atas kegiatan pengambilan mineral bukan logam dan batuan, baik dari sumber alam di dalam dan/atau permukaan bumi untuk dimanfaatkan</p>\r\n\r\n<p>Mineral Bukan Logam dan Batuan adalah mineral bukan logam dan batuan sebagaimana dimaksud di dalam peraturan perundang-undangan di bidang mineral dan batubara</p>\r\n\r\n<p><strong>Objek Pajak</strong></p>\r\n\r\n<p>Objek Pajak Mineral Bukan Logam dan Batuan adalah kegiatan pengambilan mineral bukan logam dan batuan</p>\r\n\r\n<p>Objek pajak yang termasuk mineral bukan logam meliputi:</p>\r\n\r\n<p>1. asbes; 2. bentonit; 3. dolomit; 4. feldspar; 5. garam batu (halite); 6. grafit; 7. gips; 8. kalsit; 9. kaolin; 10. magnesit; 11. mika; 12. marmer; 13. nitrat; 14. opsidien; 15. oker; 16. pasir kuarsa; 17. perlit; 18. phospat; 19. talk; 20. tawas (alum); 21. yarosif; 22. zeolit; 23. Mineral Bukan Logam lainnya sesuai dengan ketentuan peraturan perundang-undangan</p>\r\n\r\n<p>Objek pajak yang termasuk batuan meliputi:</p>\r\n\r\n<p>1. batu tulis; 2. batu setengah permata; 3. batu kapur; 4. batu apung; 5. batu permata; 6. granit/andesit; 7. leusit; 8. pasir dan kerikil; 9. tanah serap (fullers earth); 10. tanah diatome; 11. tanah liat; 12. tras; 13. basal; 14. trakkit; dan 15. Batuan lainnya sesuai dengan ketentuan peraturan perundang-undangan</p>\r\n\r\n<p><strong>Subjek Pajak</strong></p>\r\n\r\n<p>Subjek Pajak Mineral Bukan Logam dan Batuan adalah orang pribadi atau Badan yang dapat mengambil mineral bukan logam dan batuan</p>\r\n\r\n<p><strong>Wajib Pajak</strong></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>Wajib Pajak Mineral Bukan Logam dan Batuan adalah orang pribadi atau Badan yang mengambil mineral bukan logam dan batuan</p>\r\n\r\n<p><strong>Dasar Pengenaan Pajak</strong></p>\r\n\r\n<p>Dasar pengenaan Pajak Mineral Bukan Logam dan Batuan adalah Nilai Jual Hasil Pengambilan Mineral Bukan Logam dan Batuan</p>\r\n\r\n<p>Nilai jual yang dihitung dengan mengalikan volume/tonase hasil pengambilan dengan nilai pasar atau harga standar masing-masing jenis mineral bukan logam dan batuan</p>\r\n\r\n<p>Nilai pasar adalah harga rata-rata yang berlaku di lokasi setempat di wilayah daerah yang bersangkutan</p>\r\n\r\n<p>Dalam hal nilai pasar mineral bukan logam ditetapkan dengan keputusan gubernur.</p>\r\n\r\n<p><strong>Tarif Pajak</strong></p>\r\n\r\n<p>Tarif pajak untuk mineral bukan logam ditetapkan sebesar 25% (dua puluh lima persen)</p>\r\n'),(5,5,'info','<p>Pajak Parkir dipungut pajak atas penyelenggaraan tempat parkir diluar badan jalan.</p>\r\n\r\n<p>Objek Pajak Parkir adalah penyelenggaraan tempat Parkir di luar badan jalan, baik yang disediakan berkaitan dengan pokok usaha maupun yang disediakan sebagai suatu usaha, termasuk penyediaan tempat penitipan kendaraan bermotor.</p>\r\n\r\n<p>Tidak termasuk objek pajak parkir sebagaimana dimaksud adalah :</p>\r\n\r\n<ol>\r\n	<li>Penyelenggaraan tempat parkir oleh Pemerintah dan Pemerintah Daerah;</li>\r\n	<li>Penyelenggaraan tempat parkir oleh perkantoran yang hanya digunakan untuk karyawannya sendiri; dan</li>\r\n	<li>Penyelenggaraan tempat parkir oleh kedutaan, konsulat dan perwakilan Negara asing dengan asas timbal balik.</li>\r\n</ol>\r\n\r\n<p><strong>Subjek Pajak Parkir</strong>&nbsp;adalah orang pribadi atau Badan yang melakukan parkir kendaraan bermotor.</p>\r\n\r\n<p><strong>Wajib Pajak Parkir</strong>&nbsp;adalah orang pribadi atau Badan yang menyelenggarakan tempat</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><strong>Dasar Pengenaan, Tarif, Cara Penghitungan dan Masa Pajak Parkir</strong></p>\r\n\r\n<p>Dasar pengenaan Pajak Parkir adalah jumlah pembayaran atau yang seharusnya dibayar kepada penyelenggara tempat parkir.</p>\r\n\r\n<p>Jumlah yang seharusnya dibayar sebagaimana dimaksud termasuk potongan harga parkir dan parkir cuma-cuma yang diberikan kepada penerima jasa parkir.</p>\r\n\r\n<p>Tarif Pajak Parkir ditetapkan sebesar 25% (dua puluh persen).</p>\r\n\r\n<p>Masa Pajak Parkir adalah jangka waktu yang lamanya 1 (satu) bulan takwim.</p>\r\n'),(6,6,'info','Pajak PBB adalah Pajak yang dipungut atas tanah dan bangunan karena adanya keuntungan dan/atau kedudukan sosial ekonomi yang lebih baik bagi orang atau badan yang mempunyai suatu hak atasnya atau memperoleh manfaat dari padanya'),(7,7,'info','<p>Pajak Reklame dipungut pajak atas setiap penyelenggaraan Reklame.</p>\r\n\r\n<ol>\r\n	<li>Objek Pajak Reklame adalah semua penyelenggaraan reklame.</li>\r\n	<li>Objek Pajak Reklame sebagaimana dimaksud pada ayat (1) meliputi:\r\n	<ol>\r\n		<li>reklame papan/ billboard/videotron/megatron dan sejenisnya;</li>\r\n		<li>reklame kain;</li>\r\n		<li>reklame melekat, stiker;</li>\r\n		<li>reklame selebaran;</li>\r\n		<li>reklame berjalan, termasuk pada kendaraan;</li>\r\n		<li>reklame udara;</li>\r\n		<li>reklame apung;</li>\r\n		<li>reklame suara;</li>\r\n		<li>reklame film/ slide; dan</li>\r\n		<li>reklame peragaan.</li>\r\n	</ol>\r\n	</li>\r\n	<li>Tidak termasuk sebagai objek Pajak Reklame adalah:\r\n	<ol>\r\n		<li>penyelenggaraan reklame melalui internet, televisi, radio, warta harian, warta mingguan, warta bulanan dan sejenisnya;</li>\r\n		<li>label/merek produk yang melekat pada barang yang diperdagangkan, yang berfungsi untuk membedakan dari produk sejenis lainnya;</li>\r\n		<li>nama pengenal usaha atau profesi, tempat ibadah, panti asuhan dan/atau sosial yang dipasang melekat pada bangunan tempat usaha atau profesi diselenggarakan sesuai dengan ketentuan luasnya tidak melebihi ukuran 2 m2;</li>\r\n		<li>reklame yang diselenggarakan oleh Pemerintah atau Pemerintah Daerah;</li>\r\n		<li>reklame yang diselenggarakan oleh organisasi Sosial, kemasyarakatan, dan keagamaan yang bersifat tidak komersial;</li>\r\n		<li>reklame yang diselenggarakan untuk tujuan politik.</li>\r\n	</ol>\r\n	</li>\r\n</ol>\r\n\r\n<p><strong>Subjek Pajak Reklame</strong>&nbsp;adalah orang pribadi atau Badan yang menggunakan reklame.</p>\r\n\r\n<p><strong>Wajib Pajak Reklame</strong>&nbsp;adalah orang pribadi atau Badan yang menyelenggarakan reklame. Dalam hal reklame diselenggarakan sendiri secara langsung oleh orang pribadi atau Badan, Wajib Pajak Reklame adalah orang pribadi atau Badan tersebut.&nbsp; Dalam hal reklame diselenggarakan melalui pihak ketiga, pihak ketiga tersebut menjadi Wajib Pajak Reklame.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><strong>Dasar Pengenaan, Tarif, Cara Penghitungan, dan Masa Pajak Reklame :</strong></p>\r\n\r\n<p>Dasar pengenaan Pajak Reklame adalah Nilai Sewa Reklame dihitung berdasarkan pemasangan, faktor jenis, bahan yang digunakan, lokasi penempatan, waktu, jangka waktu penyelenggaraan, jumlah dan ukuran media Reklame;</p>\r\n\r\n<p>Dalam hal reklame diselenggarakan oleh pihak ketiga, Nilai Sewa Reklame sebagaimana dimaksud ditetapkan berdasarkan nilai kontrak reklame.&nbsp; Dalam hal reklame diselenggarakan sendiri, Nilai Sewa Reklame sebagaimana dimaksud dihitung dengan memperhatikan faktor jenis, bahan yang digunakan, lokasi penempatan, waktu, jangka waktu penyelenggaraan, jumlah dan ukuran media Reklame.</p>\r\n\r\n<p>Cara perhitungan Nilai Sewa Reklame yaitu dengan menjumlahkan nilai jenis bahan yang digunakan, lokasi penempatan, waktu, jangka waktu penyelenggaraan, jumlah dan ukuran media Reklame.</p>\r\n\r\n<p>Tarif Pajak Reklame ditetapkan sebesar 25% (dua puluh lima persen).</p>\r\n\r\n<p>Masa Pajak Reklame adalah jangka waktu yang lamanya sama dengan 1 (satu) bulan takwim.</p>\r\n'),(8,8,'info','<p>Pajak Restoran dipungut pajak atas setiap pelayanan yang disediakan oleh Restoran. Pajak Restoran meliputi : rumah makan, kafe, kantin, warteg, bar, catering, jasa boga</p>\r\n\r\n<ol>\r\n	<li>Objek Pajak Restoran adalah pelayanan yang disediakan oleh Restoran</li>\r\n	<li>Pelayanan yang disediakan Restoran sebagaimana dimaksud meliputi pelayanan penjualan makanan dan/atau minuman yang dikonsumsi oleh pembeli, baik dikonsumsi di tempat pelayanan maupun di tempat lain.</li>\r\n	<li>Tidak termasuk objek pajak restoran adalah pelayanan yang disediakan oleh restoran yang nilai penjualannya tidak melebihi dari Rp 5.000.000,- ( lima juta rupiah ) perbulan.</li>\r\n</ol>\r\n\r\n<p><strong>Subjek Pajak Restoran</strong>&nbsp;adalah orang pribadi atau badan yang membeli makanan dan/atau minuman dari Restoran.</p>\r\n\r\n<p><strong>Wajib Pajak Restoran</strong>&nbsp;adalah orang pribadi atau Badan yang mengusahakan Restoran.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><strong>Dasar Pengenaan, Tarif, Cara Penghitungan dan Masa Pajak :</strong></p>\r\n\r\n<p>Dasar pengenaan Pajak Restoran adalah jumlah pembayaran yang diterima atau yang seharusnya diterima restoran. Tarif Pajak Restoran ditetapkan berdasarkan hasil penjualan perbulan sebagai berikut :</p>\r\n\r\n<ol>\r\n	<li>Penjualan / Omzet Minimal Rp 5.000.000,- (lima juta rupiah) sebesar 10% ( Sepuluh Persen).</li>\r\n</ol>\r\n\r\n<p>Besaran pokok Pajak Restoran yang terutang dihitung dengan cara mengalikan tarif dengan dasar pengenaan pajak</p>\r\n\r\n<p>Masa Pajak Restoran adalah jangka waktu yang lamanya 1 (satu) bulan takwim.</p>\r\n'),(9,9,'info','Bea Perolehan Hak atas Tanah dan Bangunan (BPHTB) merupakan salah satu jenis biaya provisi atau pajak jual beli yang harus dibayarkan saat seseorang membeli sebuah rumah. Besaran BPHTB yaitu 5 persen dari harga beli dikurangi Nilai Jual Objek Pajak Tidak Kena Pajak (NJOPTKP)'),(10,1,'kalkulator','<ul>\r\n    <li>Pastikan ada memiliki <strong>Surat Setoran Pajak Daerah (SSPD)</strong></li>\r\n    <li>Di dalam surat tersebut terdapat <strong>kode bayar</strong></li>\r\n    <li>Silahkan anda masukkan kode bayar tersebut di kolom yang telah disediakan</li>\r\n    <li>Selanjutnya anda hanya perlu menekan tombol <strong>Periksa</strong></li>\r\n    <li>Jika proses periksa sudah selesai, maka akan muncul keterangan dibawahnya</li>\r\n</ul>'),(11,2,'kalkulator','<ul>\r\n    <li>Pastikan ada memiliki <strong>Surat Setoran Pajak Daerah (SSPD)</strong></li>\r\n    <li>Di dalam surat tersebut terdapat <strong>kode bayar</strong></li>\r\n    <li>Silahkan anda masukkan kode bayar tersebut di kolom yang telah disediakan</li>\r\n    <li>Selanjutnya anda hanya perlu menekan tombol <strong>Periksa</strong></li>\r\n    <li>Jika proses periksa sudah selesai, maka akan muncul keterangan dibawahnya</li>\r\n</ul>'),(12,3,'kalkulator','<ul>\r\n    <li>Pastikan ada memiliki <strong>Surat Setoran Pajak Daerah (SSPD)</strong></li>\r\n    <li>Di dalam surat tersebut terdapat <strong>kode bayar</strong></li>\r\n    <li>Silahkan anda masukkan kode bayar tersebut di kolom yang telah disediakan</li>\r\n    <li>Selanjutnya anda hanya perlu menekan tombol <strong>Periksa</strong></li>\r\n    <li>Jika proses periksa sudah selesai, maka akan muncul keterangan dibawahnya</li>\r\n</ul>'),(13,4,'kalkulator','<ul>\r\n    <li>Pastikan ada memiliki <strong>Surat Setoran Pajak Daerah (SSPD)</strong></li>\r\n    <li>Di dalam surat tersebut terdapat <strong>kode bayar</strong></li>\r\n    <li>Silahkan anda masukkan kode bayar tersebut di kolom yang telah disediakan</li>\r\n    <li>Selanjutnya anda hanya perlu menekan tombol <strong>Periksa</strong></li>\r\n    <li>Jika proses periksa sudah selesai, maka akan muncul keterangan dibawahnya</li>\r\n</ul>'),(14,5,'kalkulator','<ul>\r\n    <li>Pastikan ada memiliki <strong>Surat Setoran Pajak Daerah (SSPD)</strong></li>\r\n    <li>Di dalam surat tersebut terdapat <strong>kode bayar</strong></li>\r\n    <li>Silahkan anda masukkan kode bayar tersebut di kolom yang telah disediakan</li>\r\n    <li>Selanjutnya anda hanya perlu menekan tombol <strong>Periksa</strong></li>\r\n    <li>Jika proses periksa sudah selesai, maka akan muncul keterangan dibawahnya</li>\r\n</ul>'),(15,6,'kalkulator','<ul>\r\n    <li>Silahkan anda masukkan <strong>Nomor Objek Pajak (NOP)</strong> tersebut di kolom yang telah disediakan</li>\r\n    <li>Selanjutnya anda dapat memasukkan tahun pajak yang ingin anda periksa, contoh: 2021</li>\r\n    <li>Selanjutnya anda hanya perlu menekan tombol <strong>Periksa</strong></li>\r\n    <li>Jika proses periksa sudah selesai, maka akan muncul keterangan dibawahnya</li>\r\n</ul>'),(16,7,'kalkulator','<ul>\r\n    <li>Pastikan ada memiliki <strong>Surat Setoran Pajak Daerah (SSPD)</strong></li>\r\n    <li>Di dalam surat tersebut terdapat <strong>kode bayar</strong></li>\r\n    <li>Silahkan anda masukkan kode bayar tersebut di kolom yang telah disediakan</li>\r\n    <li>Selanjutnya anda hanya perlu menekan tombol <strong>Periksa</strong></li>\r\n    <li>Jika proses periksa sudah selesai, maka akan muncul keterangan dibawahnya</li>\r\n</ul>'),(17,8,'kalkulator','<ul>\r\n    <li>Pastikan ada memiliki <strong>Surat Setoran Pajak Daerah (SSPD)</strong></li>\r\n    <li>Di dalam surat tersebut terdapat <strong>kode bayar</strong></li>\r\n    <li>Silahkan anda masukkan kode bayar tersebut di kolom yang telah disediakan</li>\r\n    <li>Selanjutnya anda hanya perlu menekan tombol <strong>Periksa</strong></li>\r\n    <li>Jika proses periksa sudah selesai, maka akan muncul keterangan dibawahnya</li>\r\n</ul>'),(18,9,'kalkulator','<ul>\r\n    <li>Pastikan ada memiliki <strong>Surat Setoran Pajak Daerah (SSPD)</strong></li>\r\n    <li>Di dalam surat tersebut terdapat <strong>kode bayar</strong></li>\r\n    <li>Silahkan anda masukkan kode bayar tersebut di kolom yang telah disediakan</li>\r\n    <li>Selanjutnya anda hanya perlu menekan tombol <strong>Periksa</strong></li>\r\n    <li>Jika proses periksa sudah selesai, maka akan muncul keterangan dibawahnya</li>\r\n</ul>'),(19,1,'bayar','info bayar'),(20,2,'bayar','info bayar'),(21,3,'bayar','info bayar'),(22,4,'bayar','info bayar'),(23,5,'bayar','info bayar'),(24,6,'bayar','info bayar'),(25,7,'bayar','info bayar'),(26,8,'bayar','info bayar'),(27,9,'bayar','info bayar');
/*!40000 ALTER TABLE `pajak_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pajak_minerba`
--

DROP TABLE IF EXISTS `pajak_minerba`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pajak_minerba` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_type` int(11) NOT NULL,
  `ranges` varchar(100) NOT NULL,
  `nilai` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pajak_minerba`
--

LOCK TABLES `pajak_minerba` WRITE;
/*!40000 ALTER TABLE `pajak_minerba` DISABLE KEYS */;
INSERT INTO `pajak_minerba` VALUES (1,1,'0,100',1),(2,1,'1001,',2),(3,2,'0,1000',1000),(4,2,'1001,',1500),(5,3,'0,1000',1000),(6,3,'1001,',1500),(7,4,'0,1000',1000),(8,4,'1001,',1500),(9,5,'0,1000',1000),(10,5,'1001,',1500),(11,6,'0,1000',1000),(12,6,'1001,',1500),(13,7,'0,1000',1000),(14,7,'1001,',1500),(15,8,'0,1000',1000),(16,8,'1001,',1500);
/*!40000 ALTER TABLE `pajak_minerba` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pajak_minerba_type`
--

DROP TABLE IF EXISTS `pajak_minerba_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pajak_minerba_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `harga_dasar` int(11) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pajak_minerba_type`
--

LOCK TABLES `pajak_minerba_type` WRITE;
/*!40000 ALTER TABLE `pajak_minerba_type` DISABLE KEYS */;
INSERT INTO `pajak_minerba_type` VALUES (1,'Batu Gamping (Batu Kapur)',17500,''),(2,'Zeolit',25000,''),(3,'Andesit, Basal',30000,''),(4,'Pasir dan Kerikil',25000,'kerikil Sungai, Batu Kali, Kerikil sungai ayak tanpa pasir, Pasir Uruk, Pasir Pasang, Pasir Bangunan'),(5,'Tanah Uruk',15000,''),(6,'Marmer',34000,'');
/*!40000 ALTER TABLE `pajak_minerba_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pajak_omzet`
--

DROP TABLE IF EXISTS `pajak_omzet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pajak_omzet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_type` int(11) NOT NULL,
  `range_min` int(11) NOT NULL,
  `range_max` int(11) NOT NULL,
  `nilai` float NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pajak_omzet`
--

LOCK TABLES `pajak_omzet` WRITE;
/*!40000 ALTER TABLE `pajak_omzet` DISABLE KEYS */;
INSERT INTO `pajak_omzet` VALUES (2,3,10000001,50000000,0.75),(3,3,50000001,100000000,1),(4,3,100000001,0,2),(5,5,0,10000000,0.5),(6,5,10000001,50000000,0.75),(7,5,50000001,100000000,1),(8,5,100000001,0,2),(9,8,0,10000000,0.5),(10,8,10000001,50000000,0.75),(11,8,50000001,100000000,1),(12,8,100000001,0,2),(13,9,0,10000000,0.5),(14,9,10000001,50000000,0.75),(15,9,50000001,100000000,1),(16,9,100000001,0,2),(17,3,0,10000000,0.5);
/*!40000 ALTER TABLE `pajak_omzet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pajak_target`
--

DROP TABLE IF EXISTS `pajak_target`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pajak_target` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_pajak_type` int(11) NOT NULL,
  `year` int(4) NOT NULL,
  `value` int(20) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pajak_target`
--

LOCK TABLES `pajak_target` WRITE;
/*!40000 ALTER TABLE `pajak_target` DISABLE KEYS */;
INSERT INTO `pajak_target` VALUES (1,1,2020,10000000),(2,2,2020,0),(3,3,2020,0),(4,4,2020,2),(5,5,2020,0),(6,6,2020,0),(7,7,2020,0),(8,8,2020,0),(9,9,2020,0),(10,1,2018,10000),(11,2,2018,0),(12,3,2018,0),(13,4,2018,2),(14,5,2018,0),(15,6,2018,0),(16,7,2018,0),(17,8,2018,0),(18,9,2018,0);
/*!40000 ALTER TABLE `pajak_target` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pajak_type`
--

DROP TABLE IF EXISTS `pajak_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pajak_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `link` varchar(255) NOT NULL,
  `icon` varchar(200) NOT NULL,
  `omzet` int(1) NOT NULL,
  `penetapan` int(1) NOT NULL,
  `tax` float NOT NULL,
  `tax_fine` float NOT NULL,
  `special_tax_label` varchar(100) DEFAULT NULL,
  `special_tax` int(11) DEFAULT NULL,
  `parameter` int(1) NOT NULL,
  `status` int(11) NOT NULL,
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pajak_type`
--

LOCK TABLES `pajak_type` WRITE;
/*!40000 ALTER TABLE `pajak_type` DISABLE KEYS */;
INSERT INTO `pajak_type` VALUES (1,'airtanah','Air Tanah','jenis/airtanah','airtanah.png',0,0,20,2,NULL,0,0,1,1),(2,'hiburan','Hiburan','jenis/hiburan','hiburan.png',1,0,0,2,NULL,0,1,1,2),(3,'hotel','Hotel','jenis/hotel','hotel.png',1,0,10,0,NULL,0,0,1,3),(4,'minerba','Minerba','jenis/minerba','minerba.png',0,0,20,2,NULL,0,1,1,4),(5,'parkir','Parkir','jenis/parkir','parkir.png',1,0,10,2,'Pelabuhan, Bandara',30,0,1,5),(6,'pbb','PBB','jenis/pbb','pbb.png',0,1,0,2,NULL,0,0,1,6),(7,'reklame','Reklame','jenis/reklame','reklame.png',0,1,25,2,NULL,0,0,1,7),(8,'restaurant','Restaurant','jenis/restaurant','restaurant.png',1,0,10,2,NULL,0,0,1,8),(9,'bphtb','BPHTB','jenis/bphtb','bphtb.png',1,0,25,2,NULL,0,0,1,9);
/*!40000 ALTER TABLE `pajak_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `place_kab`
--

DROP TABLE IF EXISTS `place_kab`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `place_kab` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_prov` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `place_kab`
--

LOCK TABLES `place_kab` WRITE;
/*!40000 ALTER TABLE `place_kab` DISABLE KEYS */;
INSERT INTO `place_kab` VALUES (1,1,'Lampung Selatan'),(2,1,'Bandar Lampung'),(3,1,'Lampung Tengah');
/*!40000 ALTER TABLE `place_kab` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `place_kec`
--

DROP TABLE IF EXISTS `place_kec`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `place_kec` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_prov` int(11) NOT NULL,
  `id_kab` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `place_kec`
--

LOCK TABLES `place_kec` WRITE;
/*!40000 ALTER TABLE `place_kec` DISABLE KEYS */;
INSERT INTO `place_kec` VALUES (1,1,1,'Bakauheni'),(2,1,1,'Candipuro'),(3,1,1,'Jati Agung');
/*!40000 ALTER TABLE `place_kec` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `place_kel`
--

DROP TABLE IF EXISTS `place_kel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `place_kel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_kec` int(11) NOT NULL,
  `id_kab` int(11) NOT NULL,
  `id_prov` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `place_kel`
--

LOCK TABLES `place_kel` WRITE;
/*!40000 ALTER TABLE `place_kel` DISABLE KEYS */;
INSERT INTO `place_kel` VALUES (1,1,1,1,'Bakauheni'),(2,1,1,1,'Hatta'),(3,1,1,1,'Kelawi'),(4,1,1,1,'Semanak'),(5,1,1,1,'Totoharjo'),(6,2,1,1,'Batuliman'),(7,2,1,1,'Banyumas'),(8,2,1,1,'Beringin Kencana'),(9,2,1,1,'Bumi Jaya'),(10,3,1,1,'Banjar Agung'),(11,3,1,1,'Fajar Baru'),(12,3,1,1,'Gedung Agung'),(13,3,1,1,'Gedung Harapan');
/*!40000 ALTER TABLE `place_kel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `place_prov`
--

DROP TABLE IF EXISTS `place_prov`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `place_prov` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `place_prov`
--

LOCK TABLES `place_prov` WRITE;
/*!40000 ALTER TABLE `place_prov` DISABLE KEYS */;
INSERT INTO `place_prov` VALUES (1,'Lampung\r\n',''),(2,'Palembang','');
/*!40000 ALTER TABLE `place_prov` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reklame_jenis`
--

DROP TABLE IF EXISTS `reklame_jenis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reklame_jenis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `id_masa` int(11) NOT NULL,
  `harga_dasar` int(11) NOT NULL,
  `harga_tinggi` int(11) NOT NULL,
  `id_satuan` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reklame_jenis`
--

LOCK TABLES `reklame_jenis` WRITE;
/*!40000 ALTER TABLE `reklame_jenis` DISABLE KEYS */;
INSERT INTO `reklame_jenis` VALUES (1,'Reklame Papan/Billboard/Baliho/Neonbox/VideoTron/Megatron dan Sejenisnya',1,175000,75000,1),(2,'Reklame Kain/Spanduk/Umbul-umbul, Tenda Reklame, Banner dan sejenisnya',3,5000,75000,1),(3,'Reklame Cahaya dan sejenisnya',1,0,75000,1);
/*!40000 ALTER TABLE `reklame_jenis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reklame_masa`
--

DROP TABLE IF EXISTS `reklame_masa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reklame_masa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reklame_masa`
--

LOCK TABLES `reklame_masa` WRITE;
/*!40000 ALTER TABLE `reklame_masa` DISABLE KEYS */;
INSERT INTO `reklame_masa` VALUES (1,'Per Tahun'),(2,'Per Bulan'),(3,'Per Minggu'),(4,'Per Penyelenggara');
/*!40000 ALTER TABLE `reklame_masa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reklame_nfj`
--

DROP TABLE IF EXISTS `reklame_nfj`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reklame_nfj` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `value` float NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reklame_nfj`
--

LOCK TABLES `reklame_nfj` WRITE;
/*!40000 ALTER TABLE `reklame_nfj` DISABLE KEYS */;
INSERT INTO `reklame_nfj` VALUES (1,'Arteri Primer / Jalan Nasional',1.8),(2,'Arteri Sekunder/Jalan Provinsi',1.5),(3,'Kolektor Lokasi/Lingkungan',1);
/*!40000 ALTER TABLE `reklame_nfj` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reklame_nfr`
--

DROP TABLE IF EXISTS `reklame_nfr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reklame_nfr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `value` float NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reklame_nfr`
--

LOCK TABLES `reklame_nfr` WRITE;
/*!40000 ALTER TABLE `reklame_nfr` DISABLE KEYS */;
INSERT INTO `reklame_nfr` VALUES (1,'Kawasan Pelabuhan Kawasan Selektif',6),(2,'Kawasan Perdagangan Kawasan Industri',3.6),(3,'Kawasan Perkantoran Kawasan Perumahan',1.5);
/*!40000 ALTER TABLE `reklame_nfr` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reklame_nilai_strategis`
--

DROP TABLE IF EXISTS `reklame_nilai_strategis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reklame_nilai_strategis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `size_range` varchar(50) NOT NULL,
  `value` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reklame_nilai_strategis`
--

LOCK TABLES `reklame_nilai_strategis` WRITE;
/*!40000 ALTER TABLE `reklame_nilai_strategis` DISABLE KEYS */;
INSERT INTO `reklame_nilai_strategis` VALUES (1,'0,2.99',150000),(2,'3,9.99',350000),(3,'10,49.99',1200000),(4,'50,',2000000);
/*!40000 ALTER TABLE `reklame_nilai_strategis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reklame_nsp`
--

DROP TABLE IF EXISTS `reklame_nsp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reklame_nsp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `value` float NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reklame_nsp`
--

LOCK TABLES `reklame_nsp` WRITE;
/*!40000 ALTER TABLE `reklame_nsp` DISABLE KEYS */;
INSERT INTO `reklame_nsp` VALUES (1,'4 Arah',2.5),(2,'3 Arah',2),(3,'2 Arah',1.5),(4,'1 Arah (Indoor)',1);
/*!40000 ALTER TABLE `reklame_nsp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reklame_satuan`
--

DROP TABLE IF EXISTS `reklame_satuan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reklame_satuan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reklame_satuan`
--

LOCK TABLES `reklame_satuan` WRITE;
/*!40000 ALTER TABLE `reklame_satuan` DISABLE KEYS */;
INSERT INTO `reklame_satuan` VALUES (1,'M2'),(2,'Lembar'),(3,'Lokasi'),(4,'Detik');
/*!40000 ALTER TABLE `reklame_satuan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `slider`
--

DROP TABLE IF EXISTS `slider`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `slider` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(160) NOT NULL,
  `image` varchar(255) NOT NULL,
  `link` varchar(200) NOT NULL,
  `status` int(1) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `slider`
--

LOCK TABLES `slider` WRITE;
/*!40000 ALTER TABLE `slider` DISABLE KEYS */;
INSERT INTO `slider` VALUES (1,'Slider 1','slider1.jpg','',0),(2,'Slider 2','slider2.jpg','',0),(3,'Slider 3','slider3.jpg','',0);
/*!40000 ALTER TABLE `slider` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usaha`
--

DROP TABLE IF EXISTS `usaha`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usaha` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_class` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usaha`
--

LOCK TABLES `usaha` WRITE;
/*!40000 ALTER TABLE `usaha` DISABLE KEYS */;
INSERT INTO `usaha` VALUES (1,6,'Air Dalam Kemasan',''),(2,1,'Cat',''),(3,1,'Garmen',''),(4,1,'Industri Tekstil',''),(5,1,'Keramik',''),(6,1,'Kertas',''),(7,1,'Kosmetik',''),(8,1,'Makanan',''),(9,1,'Minuman',''),(10,1,'Peleburan Besi',''),(11,1,'Pengelolahan',''),(12,1,'Printing',''),(13,1,'Rokok',''),(14,2,'Furniture',''),(15,2,'Industri Rumah Tangga',''),(16,2,'Karoseri',''),(17,2,'Pabrik Es',''),(18,2,'Pengecoran Logam',''),(19,2,'Pengepakan',''),(20,2,'Perakitan',''),(21,2,'Percetakan',''),(22,3,'Hotel Berbintang',''),(23,3,'Jalan Tol',''),(24,3,'Mall/Pasaraya',''),(25,3,'Motel',''),(26,3,'Pelabuhan angkutan kereta api',''),(27,3,'Pengelolaan Bandara',''),(28,3,'Restoran',''),(29,4,'Bar',''),(30,4,'Bengkel',''),(31,4,'Kantor Swasta',''),(32,4,'Kolam Renang',''),(33,4,'Laboratorium',''),(34,4,'Lapangan Golf',''),(35,4,'Night Club',''),(36,4,'Panti Pijat',''),(37,4,'Pasar Tradisional',''),(38,4,'Penginapan/mes/apartment',''),(39,4,'Pergudangan',''),(40,4,'Perikanan',''),(41,4,'Poliklinik',''),(42,4,'Rumah Sakit Swasta',''),(43,4,'Salon',''),(44,4,'Service Station',''),(45,4,'Tambak',''),(46,4,'Tempat Hiburan',''),(47,4,'Usaha Pertanian/Peternakan/Kehutanan',''),(48,4,'Warung Air',''),(49,4,'Warung/Rumah Makan',''),(50,5,'Asrama',''),(51,5,'Lembaga Pendidikan',''),(52,5,'Pasar',''),(53,5,'Real Estate',''),(54,5,'Rumah Sakit Pemerintah',''),(55,5,'Terminal Bus','');
/*!40000 ALTER TABLE `usaha` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usaha_class`
--

DROP TABLE IF EXISTS `usaha_class`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usaha_class` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usaha_class`
--

LOCK TABLES `usaha_class` WRITE;
/*!40000 ALTER TABLE `usaha_class` DISABLE KEYS */;
INSERT INTO `usaha_class` VALUES (1,'Industri Besar',''),(2,'Industri Kecil Menengah',''),(3,'Niaga Besar',''),(4,'Niaga Kecil',''),(5,'Non Niaga (Pemakaian)',''),(6,'Air Minum Dalam Kemasan','');
/*!40000 ALTER TABLE `usaha_class` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `users_id` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(255) NOT NULL,
  `ktp` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(150) NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` datetime NOT NULL,
  `activation_key` varchar(100) DEFAULT NULL,
  `reset_key` varchar(255) NOT NULL,
  `reset_date` datetime NOT NULL,
  `status` int(1) NOT NULL,
  PRIMARY KEY (`users_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (12,'Aldiyan','555555555','aldiyan3@yahoo.com','$2y$10$/ZXHZunpaZAc2g7/Ip2h3uI0Szfcp4zH2X/C6xLLgdpFmrRMy3Eje','2020-01-10 12:47:35','2020-02-13 16:34:51','kIVXKeMDbv53','','0000-00-00 00:00:00',1),(14,'deny k','242423423535345','denykurniawan1983.dk@gmail.com','$2y$10$STEaLqLt63EBvbAl27pKReZhR.MnXo9MSrUsbbW8mHu2u231ssyBy','2020-02-14 16:53:33','2020-02-14 16:53:52','wPjWsUuGb5la','','0000-00-00 00:00:00',0),(15,'admin','342353534534','admin@admin.com','$2y$10$4/VOZ09eYHK5wvL79OuxcuisJsR6VHZEm4W6gtnXoda2YSm9SWB/i','2020-02-14 16:55:37','2020-02-17 15:41:45','xE6tm4lq2gN1','','0000-00-00 00:00:00',0),(16,'imam','12345678890','yangmanamam@gmail.com','$2y$10$pOR78cC1y7ByV4zn4di5rOC800UglxSu78NBO1imHRfYl8qqQJVfy','2020-02-17 16:23:03','2020-02-17 16:23:18','hGDSTujVFMyw','','0000-00-00 00:00:00',0),(17,'Badan Pengelola Pajak','18711111','bpprd.ls@gmail.com','$2y$10$8dbBXvXYd//GgGkUTKs2YOVjy1v5CwoaYhRMIuhRoI9.tewHHODZK','2020-02-18 09:36:10','2020-02-18 15:12:57','dGOoKeynS3NI','','0000-00-00 00:00:00',0),(18,'Mubarok','123456789','fauzijm@gmail.com','$2y$10$Q3Gzi6tPnM7wsS2.iQzltuFm87ILOL9OmCoBoW16s/yMEwF3kXtLe','2020-05-08 11:36:41','2020-05-10 23:49:50','VAfiIzypdEYq','','0000-00-00 00:00:00',0),(19,'aldes','21212121','aldesrahim2@gmail.com','$2y$10$A3jqLIjgJVE45OfzL27c5ObGsNg5Nhekj6bIvgbY8lk4f7Yg8tgFO','2021-04-13 18:03:21','2021-04-13 18:03:32','7TvecWq61NaX','','0000-00-00 00:00:00',0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_detail`
--

DROP TABLE IF EXISTS `users_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_detail` (
  `id_users` int(11) NOT NULL,
  `birth_place` varchar(200) NOT NULL,
  `dob` date NOT NULL,
  `phone` varchar(50) NOT NULL,
  `address` text NOT NULL,
  PRIMARY KEY (`id_users`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_detail`
--

LOCK TABLES `users_detail` WRITE;
/*!40000 ALTER TABLE `users_detail` DISABLE KEYS */;
INSERT INTO `users_detail` VALUES (12,'Bandar Lampung','1981-03-20','06556565','KOta Wisata cibubur'),(13,'Bandar Lampung','1981-03-20','0898552655','TelukBetung'),(14,'klaten','1983-02-21','083423423','-'),(15,'jkt','2020-02-05','234535354','-'),(16,'natar','1993-12-13','0987654321','natar'),(17,'Kalianda','2017-11-01','0725','Kalianda'),(18,'11','2020-05-08','123456789','Jalan taman flamboyan'),(19,'2121','2021-04-14','121','21');
/*!40000 ALTER TABLE `users_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_objek_pajak`
--

DROP TABLE IF EXISTS `users_objek_pajak`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_objek_pajak` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_users_usaha` int(11) NOT NULL,
  `id_pajak_type` int(11) NOT NULL,
  `last_payment` varchar(7) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_objek_pajak`
--

LOCK TABLES `users_objek_pajak` WRITE;
/*!40000 ALTER TABLE `users_objek_pajak` DISABLE KEYS */;
INSERT INTO `users_objek_pajak` VALUES (1,11,1,''),(2,12,8,''),(3,13,1,''),(4,13,2,''),(5,13,3,''),(6,13,4,''),(7,13,5,''),(8,13,7,''),(9,13,8,'');
/*!40000 ALTER TABLE `users_objek_pajak` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_sppt`
--

DROP TABLE IF EXISTS `users_sppt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_sppt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nop` varchar(20) DEFAULT NULL,
  `value` int(11) NOT NULL,
  `tax` int(20) NOT NULL,
  `fine` int(20) NOT NULL,
  `masa_date` varchar(7) DEFAULT NULL,
  `created_date` datetime NOT NULL,
  `payment_date` datetime NOT NULL,
  `token` varchar(20) NOT NULL,
  `id_users` int(11) NOT NULL,
  `id_usaha` int(11) NOT NULL,
  `id_objek_pajak` int(11) NOT NULL,
  `id_pajak_type` int(11) NOT NULL,
  `id_users_usaha_loc` int(11) NOT NULL,
  `id_pbb` int(11) NOT NULL,
  `description` text NOT NULL,
  `status` int(1) NOT NULL,
  `dpp` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_sppt`
--

LOCK TABLES `users_sppt` WRITE;
/*!40000 ALTER TABLE `users_sppt` DISABLE KEYS */;
INSERT INTO `users_sppt` VALUES (1,NULL,0,1000000,12040000,'','2020-05-11 00:16:41','0000-00-00 00:00:00','1879038652',18,13,2,2,1,0,'Pajak Hiburan kategori <strong></strong>',1,NULL),(2,NULL,0,909091,0,'','2020-05-11 00:29:53','0000-00-00 00:00:00','1826873401',18,13,3,3,2,0,'-',2,NULL),(3,NULL,0,909091,0,'2020-05','2020-05-11 00:32:40','0000-00-00 00:00:00','1834597820',18,13,3,3,2,0,'-',1,NULL),(4,NULL,0,7000,140,'2020-05','2020-05-11 00:50:41','0000-00-00 00:00:00','1808762431',18,13,4,4,3,0,'',0,NULL),(5,NULL,0,100000,2000,'2020-05','2020-05-11 00:55:06','0000-00-00 00:00:00','1878925630',18,13,5,5,4,0,'-',0,NULL),(6,NULL,0,100000,2000,'2020-05','2020-05-11 00:56:49','0000-00-00 00:00:00','1858723140',18,13,8,8,5,0,'-',0,NULL);
/*!40000 ALTER TABLE `users_sppt` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_sppt_bphtb`
--

DROP TABLE IF EXISTS `users_sppt_bphtb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_sppt_bphtb` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_pbb` int(11) NOT NULL,
  `id_users_sppt` int(11) NOT NULL,
  `luas_tanah` float DEFAULT NULL,
  `luas_bangunan` float DEFAULT NULL,
  `njop_tanah` int(11) DEFAULT NULL,
  `njop_bangunan` int(11) DEFAULT NULL,
  `warisan` int(1) NOT NULL,
  `harga_jual` int(11) NOT NULL,
  `npoptkp` int(11) NOT NULL,
  `npopkp` int(11) NOT NULL,
  `bphtb` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_sppt_bphtb`
--

LOCK TABLES `users_sppt_bphtb` WRITE;
/*!40000 ALTER TABLE `users_sppt_bphtb` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_sppt_bphtb` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_sppt_minerba`
--

DROP TABLE IF EXISTS `users_sppt_minerba`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_sppt_minerba` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `harga_dasar` int(11) NOT NULL,
  `pemakaian` float NOT NULL,
  `total` int(11) NOT NULL,
  `id_sppt` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_sppt_minerba`
--

LOCK TABLES `users_sppt_minerba` WRITE;
/*!40000 ALTER TABLE `users_sppt_minerba` DISABLE KEYS */;
INSERT INTO `users_sppt_minerba` VALUES (1,'Batu Gamping (Batu Kapur)',17500,2,35000,4);
/*!40000 ALTER TABLE `users_sppt_minerba` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_sppt_pbb`
--

DROP TABLE IF EXISTS `users_sppt_pbb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_sppt_pbb` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_users_sppt` int(11) NOT NULL,
  `nop` varchar(30) NOT NULL,
  `loc_street` varchar(255) NOT NULL,
  `loc_rt` int(5) NOT NULL,
  `loc_rw` int(5) NOT NULL,
  `loc_kel` varchar(150) NOT NULL,
  `loc_kec` varchar(150) NOT NULL,
  `loc_kab` varchar(150) NOT NULL,
  `loc_prov` varchar(150) NOT NULL,
  `wp_name` varchar(200) NOT NULL,
  `wp_street` varchar(255) NOT NULL,
  `wp_rt` int(5) NOT NULL,
  `wp_rw` int(5) NOT NULL,
  `wp_kel` varchar(150) NOT NULL,
  `wp_kec` varchar(150) NOT NULL,
  `wp_kab` varchar(150) NOT NULL,
  `wp_prov` varchar(150) NOT NULL,
  `op_luas_bumi` int(11) NOT NULL,
  `op_luas_bangunan` int(11) NOT NULL,
  `op_kelas_bumi` varchar(5) NOT NULL,
  `op_kelas_bangunan` varchar(5) NOT NULL,
  `op_price_bumi` int(11) NOT NULL,
  `op_price_bangunan` int(11) NOT NULL,
  `op_total_bumi` int(15) NOT NULL,
  `op_total_bangunan` int(15) NOT NULL,
  `njop` int(15) NOT NULL,
  `njoptkp` int(15) NOT NULL,
  `njop_pbb` int(15) NOT NULL,
  `njkp_percent` float NOT NULL,
  `njkp` int(15) NOT NULL,
  `pbb_percent` float NOT NULL,
  `pbb` int(11) NOT NULL,
  `jatuh_tempo` date NOT NULL,
  `masa` int(4) NOT NULL,
  `status` int(1) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_sppt_pbb`
--

LOCK TABLES `users_sppt_pbb` WRITE;
/*!40000 ALTER TABLE `users_sppt_pbb` DISABLE KEYS */;
INSERT INTO `users_sppt_pbb` VALUES (1,26,'123456789','Kebun Singkong',11,12,'Kelawi','Bakauheni','Lampung Selatan','Lampung','Wendy','Kebun Kacang',12,13,'Kelawi','Bakauheni','Lampung Selatan','Lampung',972,1064,'B49','A02',3745000,968000,0,0,0,12000000,0,40,0,0.5,0,'2020-03-13',2019,0),(2,26,'123456789','Kebun Singkong',11,12,'Kelawi','Bakauheni','Lampung Selatan','Lampung','Wendy','Kebun Kacang',12,13,'Kelawi','Bakauheni','Lampung Selatan','Lampung',972,1064,'B49','A02',3745000,968000,0,0,0,12000000,0,40,0,0.5,0,'2020-03-13',2020,0);
/*!40000 ALTER TABLE `users_sppt_pbb` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_sppt_reklame`
--

DROP TABLE IF EXISTS `users_sppt_reklame`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_sppt_reklame` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_users_sppt` int(11) NOT NULL,
  `id_type_reklame` int(11) NOT NULL,
  `panjang` float NOT NULL,
  `lebar` float NOT NULL,
  `tinggi` float NOT NULL,
  `sisi` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `nfr_id` int(11) NOT NULL,
  `nfr_value` float NOT NULL,
  `nfj_id` int(11) NOT NULL,
  `nfj_value` float NOT NULL,
  `nsp_id` int(11) NOT NULL,
  `nsp_value` float NOT NULL,
  `total_pajak` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_sppt_reklame`
--

LOCK TABLES `users_sppt_reklame` WRITE;
/*!40000 ALTER TABLE `users_sppt_reklame` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_sppt_reklame` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_usaha`
--

DROP TABLE IF EXISTS `users_usaha`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_usaha` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_users` int(11) NOT NULL,
  `npwpd` varchar(30) NOT NULL,
  `name` varchar(255) NOT NULL,
  `id_badan_usaha` int(11) NOT NULL,
  `phone` varchar(40) NOT NULL,
  `address` text NOT NULL,
  `rt` varchar(5) NOT NULL,
  `rw` varchar(5) NOT NULL,
  `prov` varchar(50) NOT NULL,
  `kab` varchar(50) NOT NULL,
  `kec` varchar(50) NOT NULL,
  `kel` varchar(50) NOT NULL,
  `last_payment` varchar(7) NOT NULL,
  `created_date` datetime NOT NULL,
  `verification_date` datetime NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_usaha`
--

LOCK TABLES `users_usaha` WRITE;
/*!40000 ALTER TABLE `users_usaha` DISABLE KEYS */;
INSERT INTO `users_usaha` VALUES (11,16,'','Natar',1,'0987654321','natar','08','04','Lampung\r\n','Lampung Selatan','Jati Agung','Fajar Baru','','0000-00-00 00:00:00','0000-00-00 00:00:00',0),(12,17,'','Warkop',1,'089','Kalianda','001','002','Lampung\r\n','Lampung Selatan','Bakauheni','Bakauheni','','0000-00-00 00:00:00','0000-00-00 00:00:00',0),(13,18,'0987654321','PT. FLAMBOYAN INDAH',1,'123456','Jalan flamboyan 1','02','14','Lampung\r\n','Lampung Selatan','Bakauheni','Bakauheni','','0000-00-00 00:00:00','0000-00-00 00:00:00',1);
/*!40000 ALTER TABLE `users_usaha` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_usaha_loc`
--

DROP TABLE IF EXISTS `users_usaha_loc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_usaha_loc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nop` int(11) NOT NULL,
  `id_users_usaha` int(11) NOT NULL,
  `id_users` int(11) NOT NULL,
  `id_pajak_type` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `coordinates` varchar(255) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_usaha_loc`
--

LOCK TABLES `users_usaha_loc` WRITE;
/*!40000 ALTER TABLE `users_usaha_loc` DISABLE KEYS */;
INSERT INTO `users_usaha_loc` VALUES (1,0,13,18,2,'Hiburan',',','jalan Hiburan'),(2,0,13,18,3,'HOTEL BINTANG',',','Jalan Bingtang'),(3,0,13,18,4,'Minerba Asoy',',','Jalan Minerba'),(4,0,13,18,5,'Parkiran Depan',',','Jalan Depan'),(5,0,13,18,8,'RESTO SATE',',','Jalan Sate'),(6,0,0,18,1,'PAT LAMPUNG',',','Jalan Lampung');
/*!40000 ALTER TABLE `users_usaha_loc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_usaha_minerba`
--

DROP TABLE IF EXISTS `users_usaha_minerba`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_usaha_minerba` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_users_usaha` int(11) NOT NULL,
  `id_pajak_minerba_type` int(11) NOT NULL,
  `id_users_usaha_loc` int(11) NOT NULL,
  `id_users_sppt` int(11) NOT NULL,
  `tax` int(11) NOT NULL,
  `fine` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_usaha_minerba`
--

LOCK TABLES `users_usaha_minerba` WRITE;
/*!40000 ALTER TABLE `users_usaha_minerba` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_usaha_minerba` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `zones`
--

DROP TABLE IF EXISTS `zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `zones` (
  `zone_id` int(11) NOT NULL AUTO_INCREMENT,
  `zone_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`zone_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=216 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `zones`
--

LOCK TABLES `zones` WRITE;
/*!40000 ALTER TABLE `zones` DISABLE KEYS */;
INSERT INTO `zones` VALUES (182,'Nanggroe Aceh Darussalam (NAD)'),(183,'Sumatera Utara'),(184,'Sumatera Barat'),(185,'Riau'),(186,'Kepulauan Riau'),(187,'Sumatera Selatan'),(188,'Bengkulu'),(189,'Jambi'),(190,'Lampung'),(191,'Bangka Belitung'),(192,'DKI Jakarta'),(193,'Jawa Barat'),(194,'Jawa Tengah'),(195,'DI Yogyakarta'),(196,'Jawa Timur'),(197,'Banten'),(198,'Bali'),(199,'Kalimantan Barat'),(200,'Kalimantan Selatan'),(201,'Kalimantan Tengah'),(202,'Kalimantan Timur'),(203,'Sulawesi Utara'),(204,'Sulawesi Tengah'),(205,'Sulawesi Tenggara'),(206,'Sulawesi Selatan'),(207,'Gorontalo'),(208,'Nusa Tenggara Barat (NTB)'),(209,'Nusa Tenggara Timur (NTT)'),(210,'Maluku'),(211,'Maluku Utara'),(212,'Papua'),(213,'Papua Barat'),(214,'Sulawesi Barat'),(215,'Kalimantan Utara');
/*!40000 ALTER TABLE `zones` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-07-20  2:25:54
