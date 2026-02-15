/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.13-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: iso_platform
-- ------------------------------------------------------
-- Server version	10.11.13-MariaDB-0ubuntu0.24.04.1

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
-- Table structure for table `acciones`
--

DROP TABLE IF EXISTS `acciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `acciones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gap_id` int(10) unsigned NOT NULL,
  `descripcion` text NOT NULL,
  `responsable` varchar(255) DEFAULT NULL,
  `fecha_compromiso` date NOT NULL,
  `fecha_completado` date DEFAULT NULL,
  `estado` enum('pendiente','en_proceso','completada','vencida') NOT NULL DEFAULT 'pendiente',
  `estado_accion` enum('activo','eliminado') NOT NULL DEFAULT 'activo',
  `notas` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_gap` (`gap_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_estado_accion` (`estado_accion`),
  KEY `idx_fecha_compromiso` (`fecha_compromiso`),
  CONSTRAINT `acciones_ibfk_1` FOREIGN KEY (`gap_id`) REFERENCES `gap_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned DEFAULT NULL,
  `usuario_id` int(10) unsigned DEFAULT NULL,
  `tabla` varchar(100) NOT NULL,
  `accion` varchar(50) NOT NULL,
  `registro_id` int(10) unsigned DEFAULT NULL,
  `datos_previos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_previos`)),
  `datos_nuevos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_nuevos`)),
  `ip` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_empresa` (`empresa_id`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_tabla` (`tabla`),
  KEY `idx_accion` (`accion`),
  KEY `idx_fecha` (`created_at`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `audit_logs_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auditorias_programadas`
--

DROP TABLE IF EXISTS `auditorias_programadas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `auditorias_programadas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo` enum('interna','externa','seguimiento','certificacion') NOT NULL DEFAULT 'interna',
  `auditor_responsable` varchar(255) DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` enum('programada','en_proceso','completada','cancelada') NOT NULL DEFAULT 'programada',
  `controles_alcance` text DEFAULT NULL,
  `hallazgos` text DEFAULT NULL,
  `conclusiones` text DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_empresa` (`empresa_id`),
  KEY `idx_fecha` (`fecha_inicio`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `auditorias_programadas_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `auditorias_programadas_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `controles`
--

DROP TABLE IF EXISTS `controles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `controles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dominio_id` int(10) unsigned NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `objetivo` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `idx_dominio` (`dominio_id`),
  KEY `idx_codigo` (`codigo`),
  CONSTRAINT `controles_ibfk_1` FOREIGN KEY (`dominio_id`) REFERENCES `controles_dominio` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=187 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `controles_dominio`
--

DROP TABLE IF EXISTS `controles_dominio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `controles_dominio` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(10) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `empresa_configuraciones`
--

DROP TABLE IF EXISTS `empresa_configuraciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `empresa_configuraciones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned NOT NULL,
  `clave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `es_cifrado` tinyint(1) NOT NULL DEFAULT 0,
  `descripcion` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_empresa_clave` (`empresa_id`,`clave`),
  KEY `idx_conf_empresa` (`empresa_id`),
  CONSTRAINT `fk_conf_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `empresa_requerimientos`
--

DROP TABLE IF EXISTS `empresa_requerimientos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `empresa_requerimientos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned NOT NULL,
  `requerimiento_id` int(10) unsigned NOT NULL,
  `estado` enum('pendiente','en_proceso','completado') NOT NULL DEFAULT 'pendiente',
  `fecha_inicio` date DEFAULT NULL,
  `fecha_completado` date DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_empresa_req` (`empresa_id`,`requerimiento_id`),
  KEY `idx_empresa` (`empresa_id`),
  KEY `idx_requerimiento` (`requerimiento_id`),
  KEY `idx_estado` (`estado`),
  CONSTRAINT `empresa_requerimientos_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `empresa_requerimientos_ibfk_2` FOREIGN KEY (`requerimiento_id`) REFERENCES `requerimientos_base` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `empresas`
--

DROP TABLE IF EXISTS `empresas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `empresas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `ruc` varchar(20) NOT NULL,
  `contacto` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `sector` varchar(100) DEFAULT NULL,
  `logo_path` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ruc` (`ruc`),
  KEY `idx_ruc` (`ruc`),
  KEY `idx_deleted` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `evidencia_historial`
--

DROP TABLE IF EXISTS `evidencia_historial`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `evidencia_historial` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `evidencia_id` int(10) unsigned NOT NULL,
  `estado_anterior` varchar(50) DEFAULT NULL,
  `estado_nuevo` varchar(50) NOT NULL,
  `comentarios` text DEFAULT NULL,
  `usuario_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_evidencia` (`evidencia_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `evidencia_historial_ibfk_1` FOREIGN KEY (`evidencia_id`) REFERENCES `evidencias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evidencia_historial_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `evidencia_versiones`
--

DROP TABLE IF EXISTS `evidencia_versiones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `evidencia_versiones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `evidencia_id` int(10) unsigned NOT NULL,
  `version` int(11) NOT NULL DEFAULT 1,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta_archivo` varchar(500) NOT NULL,
  `tipo_mime` varchar(100) DEFAULT NULL,
  `tamano` int(10) unsigned DEFAULT NULL,
  `hash_sha256` varchar(64) DEFAULT NULL,
  `comentarios` text DEFAULT NULL,
  `subido_por` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_evidencia` (`evidencia_id`),
  KEY `subido_por` (`subido_por`),
  CONSTRAINT `evidencia_versiones_ibfk_1` FOREIGN KEY (`evidencia_id`) REFERENCES `evidencias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evidencia_versiones_ibfk_2` FOREIGN KEY (`subido_por`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `evidencias`
--

DROP TABLE IF EXISTS `evidencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `evidencias` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned NOT NULL,
  `control_id` int(10) unsigned NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta_archivo` varchar(500) NOT NULL,
  `tipo_mime` varchar(100) NOT NULL,
  `tamano` int(10) unsigned NOT NULL,
  `hash_sha256` varchar(64) NOT NULL,
  `estado_validacion` enum('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
  `comentarios` text DEFAULT NULL,
  `subido_por` int(10) unsigned NOT NULL,
  `validado_por` int(10) unsigned DEFAULT NULL,
  `fecha_validacion` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_empresa` (`empresa_id`),
  KEY `idx_control` (`control_id`),
  KEY `idx_estado` (`estado_validacion`),
  KEY `idx_hash` (`hash_sha256`),
  KEY `subido_por` (`subido_por`),
  KEY `validado_por` (`validado_por`),
  CONSTRAINT `evidencias_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `evidencias_ibfk_2` FOREIGN KEY (`control_id`) REFERENCES `controles` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `evidencias_ibfk_3` FOREIGN KEY (`subido_por`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `evidencias_ibfk_4` FOREIGN KEY (`validado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gap_items`
--

DROP TABLE IF EXISTS `gap_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gap_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `soa_id` int(10) unsigned NOT NULL,
  `empresa_id` int(10) unsigned NOT NULL,
  `brecha` text NOT NULL,
  `impacto` enum('critico','alto','medio','bajo') NOT NULL,
  `prioridad` enum('alta','media','baja') NOT NULL,
  `avance` decimal(5,2) DEFAULT 0.00 CHECK (`avance` between 0 and 100),
  `estado_certificacion` enum('abierto','en_validacion','cerrado','rechazado','pendiente_evidencia') DEFAULT 'abierto',
  `fecha_objetivo` date DEFAULT NULL,
  `fecha_real_cierre` date DEFAULT NULL,
  `estado_gap` enum('activo','eliminado') NOT NULL DEFAULT 'activo',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_soa` (`soa_id`),
  KEY `idx_empresa` (`empresa_id`),
  KEY `idx_prioridad` (`prioridad`),
  KEY `idx_estado` (`estado_gap`),
  FULLTEXT KEY `idx_brecha` (`brecha`),
  CONSTRAINT `gap_items_ibfk_1` FOREIGN KEY (`soa_id`) REFERENCES `soa_entries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `gap_items_ibfk_2` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `password_history`
--

DROP TABLE IF EXISTS `password_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `password_history_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rate_limits`
--

DROP TABLE IF EXISTS `rate_limits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rate_limits` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `rate_key` varchar(255) NOT NULL,
  `attempts` int(10) unsigned NOT NULL DEFAULT 1,
  `last_attempt` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_rate_key` (`rate_key`),
  KEY `idx_expires` (`expires_at`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `requerimientos_base`
--

DROP TABLE IF EXISTS `requerimientos_base`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `requerimientos_base` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `numero` tinyint(3) unsigned NOT NULL,
  `identificador` varchar(50) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero` (`numero`),
  UNIQUE KEY `identificador` (`identificador`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `requerimientos_controles`
--

DROP TABLE IF EXISTS `requerimientos_controles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `requerimientos_controles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `requerimiento_base_id` int(10) unsigned NOT NULL,
  `control_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_req_control` (`requerimiento_base_id`,`control_id`),
  KEY `idx_requerimiento` (`requerimiento_base_id`),
  KEY `idx_control` (`control_id`),
  CONSTRAINT `requerimientos_controles_ibfk_1` FOREIGN KEY (`requerimiento_base_id`) REFERENCES `requerimientos_base` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `requerimientos_controles_ibfk_2` FOREIGN KEY (`control_id`) REFERENCES `controles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `soa_entries`
--

DROP TABLE IF EXISTS `soa_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `soa_entries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned NOT NULL,
  `control_id` int(10) unsigned NOT NULL,
  `aplicable` tinyint(1) NOT NULL DEFAULT 1,
  `estado` enum('no_implementado','parcial','implementado') NOT NULL DEFAULT 'no_implementado',
  `justificacion` text DEFAULT NULL,
  `responsable` int(10) unsigned DEFAULT NULL,
  `fecha_evaluacion` date DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_empresa_control` (`empresa_id`,`control_id`),
  KEY `idx_empresa` (`empresa_id`),
  KEY `idx_control` (`control_id`),
  KEY `idx_aplicable` (`aplicable`),
  KEY `idx_estado` (`estado`),
  KEY `fk_soa_responsable` (`responsable`),
  CONSTRAINT `fk_soa_responsable` FOREIGN KEY (`responsable`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `soa_entries_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `soa_entries_ibfk_2` FOREIGN KEY (`control_id`) REFERENCES `controles` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('super_admin','admin_empresa','auditor','consultor') NOT NULL DEFAULT 'consultor',
  `estado` enum('activo','inactivo','bloqueado') NOT NULL DEFAULT 'activo',
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `intentos_login` tinyint(3) unsigned DEFAULT 0,
  `bloqueado_hasta` timestamp NULL DEFAULT NULL,
  `debe_cambiar_password` tinyint(1) NOT NULL DEFAULT 0,
  `password_updated_at` timestamp NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_email_empresa` (`email`,`empresa_id`),
  KEY `idx_empresa` (`empresa_id`),
  KEY `idx_email` (`email`),
  KEY `idx_rol` (`rol`),
  KEY `idx_estado` (`estado`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-15 22:22:21
