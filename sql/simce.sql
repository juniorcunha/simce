-- MySQL dump 10.13  Distrib 5.6.23-72.1, for Linux (x86_64)
--
-- Host: localhost    Database: simce
-- ------------------------------------------------------
-- Server version	5.6.23-72.1

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
-- Current Database: `simce`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `simce` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `simce`;

--
-- Table structure for table `alocacoes`
--

DROP TABLE IF EXISTS `alocacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alocacoes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `inicio` date DEFAULT NULL,
  `fim` date DEFAULT NULL,
  `identificacao` varchar(255) DEFAULT NULL,
  `oficio` varchar(255) DEFAULT NULL,
  `operacoes_id` int(11) unsigned DEFAULT NULL,
  `alvos_id` int(11) unsigned DEFAULT NULL,
  `recursos_id` int(11) unsigned DEFAULT NULL,
  `status` varchar(255) DEFAULT 'free',
  `desvio_para` tinyint(3) unsigned DEFAULT NULL,
  `desvio_via` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index_foreignkey_alocacoes_operacoes` (`operacoes_id`),
  KEY `index_foreignkey_alocacoes_alvos` (`alvos_id`),
  KEY `index_foreignkey_alocacoes_recursos` (`recursos_id`),
  CONSTRAINT `cons_fk_alocacoes_alvos_id_id` FOREIGN KEY (`alvos_id`) REFERENCES `alvos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `cons_fk_alocacoes_operacoes_id_id` FOREIGN KEY (`operacoes_id`) REFERENCES `operacoes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `cons_fk_alocacoes_recursos_id_id` FOREIGN KEY (`recursos_id`) REFERENCES `recursos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=718 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `alvos`
--

DROP TABLE IF EXISTS `alvos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alvos` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `cidade` varchar(255) DEFAULT NULL,
  `estado` varchar(255) DEFAULT NULL,
  `rg` varchar(255) DEFAULT NULL,
  `cpf` varchar(255) DEFAULT NULL,
  `telefone` varchar(255) DEFAULT NULL,
  `unidades_id` int(11) unsigned DEFAULT NULL,
  `apelido` varchar(255) DEFAULT NULL,
  `observacoes` varchar(255) DEFAULT NULL,
  `operacoes_id` int(11) unsigned DEFAULT NULL,
  `sexo` varchar(255) DEFAULT NULL,
  `foto` longtext,
  `nascimento` varchar(255) DEFAULT NULL,
  `estado_civil` varchar(255) DEFAULT NULL,
  `empresa` varchar(255) DEFAULT NULL,
  `cargo` varchar(255) DEFAULT NULL,
  `end_empresa` varchar(255) DEFAULT NULL,
  `tel_empresa` varchar(255) DEFAULT NULL,
  `escolaridade` varchar(255) DEFAULT NULL,
  `veiculo1_tipo` varchar(255) DEFAULT NULL,
  `veiculo1_fabricante` varchar(255) DEFAULT NULL,
  `veiculo1_modelo` varchar(255) DEFAULT NULL,
  `veiculo1_ano` int(11) unsigned DEFAULT NULL,
  `veiculo1_placa` varchar(255) DEFAULT NULL,
  `veiculo2_tipo` varchar(255) DEFAULT NULL,
  `veiculo2_fabricante` varchar(255) DEFAULT NULL,
  `veiculo2_modelo` varchar(255) DEFAULT NULL,
  `veiculo2_ano` int(11) unsigned DEFAULT NULL,
  `veiculo2_placa` varchar(255) DEFAULT NULL,
  `veiculo3_tipo` varchar(255) DEFAULT NULL,
  `veiculo3_fabricante` varchar(255) DEFAULT NULL,
  `veiculo3_modelo` varchar(255) DEFAULT NULL,
  `veiculo3_ano` int(11) unsigned DEFAULT NULL,
  `veiculo3_placa` varchar(255) DEFAULT NULL,
  `imovel1_tipo` varchar(255) DEFAULT NULL,
  `imovel1_situacao` varchar(255) DEFAULT NULL,
  `imovel1_endereco` varchar(255) DEFAULT NULL,
  `imovel1_cidade` varchar(255) DEFAULT NULL,
  `imovel1_estado` varchar(255) DEFAULT NULL,
  `imovel2_tipo` varchar(255) DEFAULT NULL,
  `imovel2_situacao` varchar(255) DEFAULT NULL,
  `imovel2_endereco` varchar(255) DEFAULT NULL,
  `imovel2_cidade` varchar(255) DEFAULT NULL,
  `imovel2_estado` varchar(255) DEFAULT NULL,
  `imovel3_tipo` varchar(255) DEFAULT NULL,
  `imovel3_situacao` varchar(255) DEFAULT NULL,
  `imovel3_endereco` varchar(255) DEFAULT NULL,
  `imovel3_cidade` varchar(255) DEFAULT NULL,
  `imovel3_estado` varchar(255) DEFAULT NULL,
  `bancario1_banco` varchar(255) DEFAULT NULL,
  `bancario1_agencia` varchar(255) DEFAULT NULL,
  `bancario1_conta` varchar(255) DEFAULT NULL,
  `bancario1_correntista` varchar(255) DEFAULT NULL,
  `bancario1_tipo` varchar(255) DEFAULT NULL,
  `bancario1_cpfcnpj` varchar(255) DEFAULT NULL,
  `bancario2_banco` varchar(255) DEFAULT NULL,
  `bancario2_agencia` int(11) unsigned DEFAULT NULL,
  `bancario2_conta` varchar(255) DEFAULT NULL,
  `bancario2_correntista` varchar(255) DEFAULT NULL,
  `bancario2_tipo` varchar(255) DEFAULT NULL,
  `bancario2_cpfcnpj` varchar(255) DEFAULT NULL,
  `bancario3_banco` varchar(255) DEFAULT NULL,
  `bancario3_agencia` varchar(255) DEFAULT NULL,
  `bancario3_conta` int(11) unsigned DEFAULT NULL,
  `bancario3_correntista` varchar(255) DEFAULT NULL,
  `bancario3_tipo` varchar(255) DEFAULT NULL,
  `bancario3_cpfcnpj` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index_foreignkey_alvos_unidades` (`unidades_id`),
  KEY `index_foreignkey_alvos_operacoes` (`operacoes_id`),
  CONSTRAINT `cons_fk_alvos_operacoes_id_id` FOREIGN KEY (`operacoes_id`) REFERENCES `operacoes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `cons_fk_alvos_unidades_id_id` FOREIGN KEY (`unidades_id`) REFERENCES `unidades` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=413 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `audit`
--

DROP TABLE IF EXISTS `audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `data` datetime DEFAULT NULL,
  `ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `acao` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `param` longtext COLLATE utf8_unicode_ci,
  `usuarios_id` int(11) unsigned DEFAULT NULL,
  `unidades_id` int(11) unsigned DEFAULT NULL,
  `unidades` tinyint(1) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index_foreignkey_audit_usuarios` (`usuarios_id`),
  KEY `index_foreignkey_audit_unidades` (`unidades_id`),
  CONSTRAINT `cons_fk_audit_unidades_id_id` FOREIGN KEY (`unidades_id`) REFERENCES `unidades` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `cons_fk_audit_usuarios_id_id` FOREIGN KEY (`usuarios_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=538398 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cargos`
--

DROP TABLE IF EXISTS `cargos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cargos` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) DEFAULT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `acao` text,
  `unidades_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index_foreignkey_cargos_unidades` (`unidades_id`),
  CONSTRAINT `cons_fk_cargos_unidades_id_id` FOREIGN KEY (`unidades_id`) REFERENCES `unidades` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `configuracoes`
--

DROP TABLE IF EXISTS `configuracoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuracoes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `classificacao` tinyint(3) unsigned DEFAULT NULL,
  `timeline` tinyint(3) unsigned DEFAULT NULL,
  `notificar_resumo` tinyint(3) unsigned DEFAULT NULL,
  `notificar_registros` tinyint(3) unsigned DEFAULT NULL,
  `notificar_erro_e1` tinyint(3) unsigned DEFAULT NULL,
  `notificar_erro_internet` tinyint(3) unsigned DEFAULT NULL,
  `notificar_erro_gsm` tinyint(3) unsigned DEFAULT NULL,
  `notificar_erro_infra` tinyint(3) unsigned DEFAULT NULL,
  `relatorio_cabecalho` longtext COLLATE utf8_unicode_ci,
  `relatorio_rodape` text COLLATE utf8_unicode_ci,
  `relatorio_usuario` tinyint(3) unsigned DEFAULT NULL,
  `unidades_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index_foreignkey_configuracoes_unidades` (`unidades_id`),
  CONSTRAINT `cons_fk_configuracoes_unidades_id_id` FOREIGN KEY (`unidades_id`) REFERENCES `unidades` (`id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contatos`
--

DROP TABLE IF EXISTS `contatos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contatos` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `operacoes_id` int(11) unsigned DEFAULT NULL,
  `alvo` int(11) unsigned DEFAULT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `apelido` varchar(255) DEFAULT NULL,
  `sexo` varchar(255) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `cidade` varchar(255) DEFAULT NULL,
  `estado` varchar(255) DEFAULT NULL,
  `rg` varchar(255) DEFAULT NULL,
  `cpf` varchar(255) DEFAULT NULL,
  `telefone` varchar(255) DEFAULT NULL,
  `observacoes` varchar(255) DEFAULT NULL,
  `foto` longtext,
  `genero` varchar(255) DEFAULT NULL,
  `nascimento` varchar(255) DEFAULT NULL,
  `estado_civil` varchar(255) DEFAULT NULL,
  `empresa` varchar(255) DEFAULT NULL,
  `cargo` varchar(255) DEFAULT NULL,
  `end_empresa` varchar(255) DEFAULT NULL,
  `tel_empresa` varchar(255) DEFAULT NULL,
  `escolaridade` varchar(255) DEFAULT NULL,
  `veiculo1_tipo` varchar(255) DEFAULT NULL,
  `veiculo1_fabricante` varchar(255) DEFAULT NULL,
  `veiculo1_modelo` varchar(255) DEFAULT NULL,
  `veiculo1_ano` int(11) unsigned DEFAULT NULL,
  `veiculo1_placa` varchar(255) DEFAULT NULL,
  `veiculo2_tipo` varchar(255) DEFAULT NULL,
  `veiculo2_fabricante` varchar(255) DEFAULT NULL,
  `veiculo2_modelo` varchar(255) DEFAULT NULL,
  `veiculo2_ano` int(11) unsigned DEFAULT NULL,
  `veiculo2_placa` varchar(255) DEFAULT NULL,
  `veiculo3_tipo` varchar(255) DEFAULT NULL,
  `veiculo3_fabricante` varchar(255) DEFAULT NULL,
  `veiculo3_modelo` varchar(255) DEFAULT NULL,
  `veiculo3_ano` int(11) unsigned DEFAULT NULL,
  `veiculo3_placa` varchar(255) DEFAULT NULL,
  `imovel1_tipo` varchar(255) DEFAULT NULL,
  `imovel1_situacao` varchar(255) DEFAULT NULL,
  `imovel1_endereco` varchar(255) DEFAULT NULL,
  `imovel1_cidade` varchar(255) DEFAULT NULL,
  `imovel1_estado` varchar(255) DEFAULT NULL,
  `imovel2_tipo` varchar(255) DEFAULT NULL,
  `imovel2_situacao` varchar(255) DEFAULT NULL,
  `imovel2_endereco` varchar(255) DEFAULT NULL,
  `imovel2_cidade` varchar(255) DEFAULT NULL,
  `imovel2_estado` varchar(255) DEFAULT NULL,
  `imovel3_tipo` varchar(255) DEFAULT NULL,
  `imovel3_situacao` varchar(255) DEFAULT NULL,
  `imovel3_endereco` varchar(255) DEFAULT NULL,
  `imovel3_cidade` varchar(255) DEFAULT NULL,
  `imovel3_estado` varchar(255) DEFAULT NULL,
  `bancario1_banco` varchar(255) DEFAULT NULL,
  `bancario1_agencia` varchar(255) DEFAULT NULL,
  `bancario1_conta` varchar(255) DEFAULT NULL,
  `bancario1_correntista` varchar(255) DEFAULT NULL,
  `bancario1_tipo` varchar(255) DEFAULT NULL,
  `bancario1_cpfcnpj` double DEFAULT NULL,
  `bancario2_banco` varchar(255) DEFAULT NULL,
  `bancario2_agencia` int(11) unsigned DEFAULT NULL,
  `bancario2_conta` int(11) unsigned DEFAULT NULL,
  `bancario2_correntista` varchar(255) DEFAULT NULL,
  `bancario2_tipo` varchar(255) DEFAULT NULL,
  `bancario2_cpfcnpj` int(11) unsigned DEFAULT NULL,
  `bancario3_banco` varchar(255) DEFAULT NULL,
  `bancario3_agencia` int(11) unsigned DEFAULT NULL,
  `bancario3_conta` int(11) unsigned DEFAULT NULL,
  `bancario3_correntista` varchar(255) DEFAULT NULL,
  `bancario3_tipo` varchar(255) DEFAULT NULL,
  `bancario3_cpfcnpj` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index_foreignkey_contatos_operacoes` (`operacoes_id`),
  CONSTRAINT `cons_fk_contatos_operacoes_id_id` FOREIGN KEY (`operacoes_id`) REFERENCES `operacoes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=549 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `e1stats`
--

DROP TABLE IF EXISTS `e1stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `e1stats` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `data` datetime DEFAULT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` tinyint(3) unsigned DEFAULT NULL,
  `info` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=373749 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `internetstats`
--

DROP TABLE IF EXISTS `internetstats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `internetstats` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `data` datetime DEFAULT NULL,
  `status` tinyint(3) unsigned DEFAULT NULL,
  `latencia` double DEFAULT NULL,
  `perda_pacotes` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=233592 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mobilestats`
--

DROP TABLE IF EXISTS `mobilestats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mobilestats` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `data` datetime DEFAULT NULL,
  `sinal` tinyint(3) unsigned DEFAULT NULL,
  `recursos_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index_foreignkey_mobilestats_recursos` (`recursos_id`),
  CONSTRAINT `cons_fk_mobilestats_recursos_id_id` FOREIGN KEY (`recursos_id`) REFERENCES `recursos` (`id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=115467 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `operacoes`
--

DROP TABLE IF EXISTS `operacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `operacoes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) DEFAULT NULL,
  `inicio` date DEFAULT NULL,
  `fim` date DEFAULT NULL,
  `vara` varchar(255) DEFAULT NULL,
  `autos` varchar(255) DEFAULT NULL,
  `unidades_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index_foreignkey_operacoes_unidades` (`unidades_id`),
  CONSTRAINT `cons_fk_operacoes_unidades_id_id` FOREIGN KEY (`unidades_id`) REFERENCES `unidades` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `permissoes`
--

DROP TABLE IF EXISTS `permissoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissoes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `alvos_id` int(11) unsigned DEFAULT NULL,
  `usuarios_id` int(11) unsigned DEFAULT NULL,
  `cargos_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index_foreignkey_permissoes_alvos` (`alvos_id`),
  KEY `index_foreignkey_permissoes_usuarios` (`usuarios_id`),
  KEY `index_foreignkey_permissoes_cargos` (`cargos_id`),
  CONSTRAINT `cons_fk_permissoes_alvos_id_id` FOREIGN KEY (`alvos_id`) REFERENCES `alvos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `cons_fk_permissoes_cargos_id_id` FOREIGN KEY (`cargos_id`) REFERENCES `cargos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `cons_fk_permissoes_usuarios_id_id` FOREIGN KEY (`usuarios_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7219 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `recursos`
--

DROP TABLE IF EXISTS `recursos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recursos` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tipo` enum('A','G','D') NOT NULL COMMENT 'A - Audio, G - GSM, D - Dados',
  `nome` varchar(32) NOT NULL,
  `link` varchar(32) NOT NULL,
  `unidades_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`),
  KEY `index_foreignkey_recursos_unidades` (`unidades_id`),
  CONSTRAINT `cons_fk_recursos_unidades_id_id` FOREIGN KEY (`unidades_id`) REFERENCES `unidades` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=206 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registros`
--

DROP TABLE IF EXISTS `registros`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registros` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `data` datetime DEFAULT NULL,
  `tipo` varchar(255) DEFAULT NULL,
  `identificador` varchar(255) DEFAULT NULL,
  `classificacao` tinyint(3) unsigned DEFAULT NULL,
  `estado` tinyint(3) unsigned DEFAULT NULL,
  `tamanho` double DEFAULT NULL,
  `unidades_id` int(11) unsigned DEFAULT NULL,
  `operacoes_id` int(11) unsigned DEFAULT NULL,
  `alvos_id` int(11) unsigned DEFAULT NULL,
  `recursos_id` int(11) unsigned DEFAULT NULL,
  `observacoes` text,
  `attr` text,
  `relato` text,
  `timeline` tinyint(3) unsigned DEFAULT NULL,
  `xplico_tabela` varchar(255) DEFAULT NULL,
  `xplico_id` int(11) unsigned DEFAULT NULL,
  `dial_status` varchar(255) DEFAULT NULL,
  `dial_time` tinyint(3) unsigned DEFAULT NULL,
  `answer_time` tinyint(3) unsigned DEFAULT NULL,
  `dial_number` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `xplico_tabela` (`xplico_tabela`,`xplico_id`),
  KEY `index_foreignkey_registros_unidades` (`unidades_id`),
  KEY `index_foreignkey_registros_operacoes` (`operacoes_id`),
  KEY `index_foreignkey_registros_alvos` (`alvos_id`),
  KEY `index_foreignkey_registros_recursos` (`recursos_id`),
  CONSTRAINT `cons_fk_registros_alvos_id_id` FOREIGN KEY (`alvos_id`) REFERENCES `alvos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `cons_fk_registros_operacoes_id_id` FOREIGN KEY (`operacoes_id`) REFERENCES `operacoes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `cons_fk_registros_recursos_id_id` FOREIGN KEY (`recursos_id`) REFERENCES `recursos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `cons_fk_registros_unidades_id_id` FOREIGN KEY (`unidades_id`) REFERENCES `unidades` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=723688 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reljudicial`
--

DROP TABLE IF EXISTS `reljudicial`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reljudicial` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `conteudo` longtext COLLATE utf8_unicode_ci,
  `operacoes_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index_foreignkey_reljudicial_operacoes` (`operacoes_id`),
  CONSTRAINT `cons_fk_reljudicial_operacoes_id_id` FOREIGN KEY (`operacoes_id`) REFERENCES `operacoes` (`id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `segmentos`
--

DROP TABLE IF EXISTS `segmentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `segmentos` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `inicio` varchar(255) DEFAULT NULL,
  `final` varchar(255) DEFAULT NULL,
  `transcricao` text,
  `registros_id` int(11) unsigned DEFAULT NULL,
  `voiceid_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index_foreignkey_segmentos_registros` (`registros_id`),
  KEY `index_foreignkey_segmentos_voiceid` (`voiceid_id`),
  KEY `index_transcricao` (`transcricao`(255)),
  CONSTRAINT `cons_fk_segmentos_registros_id_id` FOREIGN KEY (`registros_id`) REFERENCES `registros` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `cons_fk_segmentos_voiceid_id_id` FOREIGN KEY (`voiceid_id`) REFERENCES `voiceid` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2871179 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sysstats`
--

DROP TABLE IF EXISTS `sysstats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sysstats` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `data` datetime DEFAULT NULL,
  `cpu` tinyint(3) unsigned DEFAULT NULL,
  `memoria` tinyint(3) unsigned DEFAULT NULL,
  `disco` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=233597 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `timeline`
--

DROP TABLE IF EXISTS `timeline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timeline` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data` date DEFAULT NULL,
  `descricao` text COLLATE utf8_unicode_ci,
  `arquivo` longtext COLLATE utf8_unicode_ci,
  `operacoes_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index_foreignkey_timeline_operacoes` (`operacoes_id`),
  CONSTRAINT `cons_fk_timeline_operacoes_id_id` FOREIGN KEY (`operacoes_id`) REFERENCES `operacoes` (`id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `unidades`
--

DROP TABLE IF EXISTS `unidades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unidades` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `cidade` varchar(255) DEFAULT NULL,
  `estado` varchar(255) DEFAULT NULL,
  `telefone` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`(128))
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) DEFAULT NULL,
  `login` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `tipo` enum('S','A','O') NOT NULL,
  `unidades_id` int(11) unsigned DEFAULT NULL,
  `online` tinyint(3) unsigned DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `last_ip` varchar(255) DEFAULT NULL,
  `telefone` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`(128)),
  UNIQUE KEY `login` (`login`(128)),
  KEY `index_foreignkey_usuarios_unidades` (`unidades_id`),
  CONSTRAINT `cons_fk_usuarios_unidades_id_id` FOREIGN KEY (`unidades_id`) REFERENCES `unidades` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=174 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `voiceid`
--

DROP TABLE IF EXISTS `voiceid`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `voiceid` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `speaker` varchar(255) DEFAULT NULL,
  `unidades_id` int(11) unsigned DEFAULT NULL,
  `operacoes_id` int(11) unsigned DEFAULT NULL,
  `contatos_id` int(11) unsigned DEFAULT NULL,
  `voicedb` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index_foreignkey_voiceid_unidades` (`unidades_id`),
  KEY `index_foreignkey_voiceid_operacoes` (`operacoes_id`),
  KEY `index_foreignkey_voiceid_contatos` (`contatos_id`),
  CONSTRAINT `cons_fk_voiceid_contatos_id_id` FOREIGN KEY (`contatos_id`) REFERENCES `contatos` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `cons_fk_voiceid_operacoes_id_id` FOREIGN KEY (`operacoes_id`) REFERENCES `operacoes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `cons_fk_voiceid_unidades_id_id` FOREIGN KEY (`unidades_id`) REFERENCES `unidades` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=754697 DEFAULT CHARSET=utf8;

insert into usuarios(nome,login,email,password,tipo) values ('SiMCE - Super Administrador','admin','suporte@dad.eng.br',md5('qa01pl10'),'S');

/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-12-21 15:40:41
