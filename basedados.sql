-- --------------------------------------------------------
-- Anfitrião:                    127.0.0.1
-- Versão do servidor:           8.0.30 - MySQL Community Server - GPL
-- SO do servidor:               Win64
-- HeidiSQL Versão:              12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- A despejar estrutura da base de dados para flexifun_db
CREATE DATABASE IF NOT EXISTS `flexifun_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `flexifun_db`;

-- A despejar estrutura para tabela flexifun_db.fisioterapeuta
CREATE TABLE IF NOT EXISTS `fisioterapeuta` (
  `user_id` bigint NOT NULL,
  `nome` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `especialidade` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fisioterapeuta_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A despejar dados para tabela flexifun_db.fisioterapeuta: ~0 rows (aproximadamente)
INSERT INTO `fisioterapeuta` (`user_id`, `nome`, `especialidade`, `telefone`, `notas`) VALUES
	(2, 'João Silva', 'Reabilitação Pediátrica', '912345678', 'Fisio de teste para o projeto.');

-- A despejar estrutura para tabela flexifun_db.jogo
CREATE TABLE IF NOT EXISTS `jogo` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A despejar dados para tabela flexifun_db.jogo: ~0 rows (aproximadamente)
INSERT INTO `jogo` (`id`, `nome`, `descricao`, `ativo`) VALUES
	(1, 'FlexiFun', 'Jogo interativo para reabilitação', 1);

-- A despejar estrutura para tabela flexifun_db.nivel
CREATE TABLE IF NOT EXISTS `nivel` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `jogo_id` bigint NOT NULL,
  `numero_nivel` int NOT NULL,
  `nome` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tipo_objetivo` enum('forca','flexao','rotacao','tempo','combo') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor_alvo` float NOT NULL,
  `unidade` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `limite_tempo_s` int DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `jogo_id` (`jogo_id`),
  CONSTRAINT `nivel_ibfk_1` FOREIGN KEY (`jogo_id`) REFERENCES `jogo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A despejar dados para tabela flexifun_db.nivel: ~0 rows (aproximadamente)

-- A despejar estrutura para tabela flexifun_db.paciente
CREATE TABLE IF NOT EXISTS `paciente` (
  `user_id` bigint NOT NULL,
  `nome` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_nascimento` date DEFAULT NULL,
  `sexo` enum('M','F','Outro','NA') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'NA',
  `diagnostico` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nif` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_utente` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `morada` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fisioterapeuta_id` bigint DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  KEY `fisioterapeuta_id` (`fisioterapeuta_id`),
  CONSTRAINT `paciente_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `paciente_ibfk_2` FOREIGN KEY (`fisioterapeuta_id`) REFERENCES `fisioterapeuta` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A despejar dados para tabela flexifun_db.paciente: ~5 rows (aproximadamente)
INSERT INTO `paciente` (`user_id`, `nome`, `data_nascimento`, `sexo`, `diagnostico`, `nif`, `numero_utente`, `telefone`, `morada`, `notas`, `fisioterapeuta_id`) VALUES
	(5, 'Maria Silva', '2015-04-12', 'F', 'Hemiparesia esquerda', NULL, NULL, NULL, NULL, NULL, NULL),
	(6, 'Ana Ribeiro', '2016-02-11', 'F', 'Atraso motor global', NULL, NULL, NULL, NULL, NULL, NULL),
	(7, 'Catarina Faria', '2005-11-23', 'F', 'Baixa mobilidade da mão direita.', '232232434', '1617000', '919191444', 'Avenida das Plantas, 92', '', 2),
	(10, 'Filipe Coelho', '2007-02-01', 'M', 'Lesão neuromuscular.', '23123455', '1617999', '919191499', 'Rua 1, Bairro 9, Lisboa', '', 2),
	(11, 'Matilde Ricardo', '2005-10-28', 'F', 'Reabilitação', '23123455', '1617999', '919191444', 'Rua das Flores', '', 2),
	(12, 'Marta Pereira', '2004-12-23', 'F', 'Rutura ligamento do indicador', '265265265', '123', '912345678', 'Rua A, 123', '', 2);

-- A despejar estrutura para tabela flexifun_db.progresso_paciente
CREATE TABLE IF NOT EXISTS `progresso_paciente` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `paciente_id` bigint NOT NULL,
  `nivel_id` bigint NOT NULL,
  `estado` enum('nao_iniciado','em_progresso','concluido') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'nao_iniciado',
  `melhor_score` float DEFAULT NULL,
  `tentativas` int NOT NULL DEFAULT '0',
  `ultima_vez` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_progresso` (`paciente_id`,`nivel_id`),
  KEY `nivel_id` (`nivel_id`),
  CONSTRAINT `progresso_paciente_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `paciente` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `progresso_paciente_ibfk_2` FOREIGN KEY (`nivel_id`) REFERENCES `nivel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A despejar dados para tabela flexifun_db.progresso_paciente: ~0 rows (aproximadamente)

-- A despejar estrutura para tabela flexifun_db.resultado_nivel
CREATE TABLE IF NOT EXISTS `resultado_nivel` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `sessao_id` bigint NOT NULL,
  `nivel_id` bigint NOT NULL,
  `passou` tinyint(1) DEFAULT NULL,
  `score` float DEFAULT NULL,
  `forca_max` float DEFAULT NULL,
  `flexao_max` float DEFAULT NULL,
  `tempo_reacao_ms` int DEFAULT NULL,
  `repeticoes` int DEFAULT NULL,
  `raw_data` json DEFAULT NULL,
  `criado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `sessao_id` (`sessao_id`),
  KEY `nivel_id` (`nivel_id`),
  CONSTRAINT `resultado_nivel_ibfk_1` FOREIGN KEY (`sessao_id`) REFERENCES `sessao` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `resultado_nivel_ibfk_2` FOREIGN KEY (`nivel_id`) REFERENCES `nivel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A despejar dados para tabela flexifun_db.resultado_nivel: ~0 rows (aproximadamente)

-- A despejar estrutura para tabela flexifun_db.sessao
CREATE TABLE IF NOT EXISTS `sessao` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `paciente_id` bigint NOT NULL,
  `fisioterapeuta_id` bigint DEFAULT NULL,
  `iniciou_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `terminou_em` datetime DEFAULT NULL,
  `notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `paciente_id` (`paciente_id`),
  KEY `fisioterapeuta_id` (`fisioterapeuta_id`),
  CONSTRAINT `sessao_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `paciente` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `sessao_ibfk_2` FOREIGN KEY (`fisioterapeuta_id`) REFERENCES `fisioterapeuta` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A despejar dados para tabela flexifun_db.sessao: ~0 rows (aproximadamente)

-- A despejar estrutura para tabela flexifun_db.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('paciente','fisioterapeuta','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A despejar dados para tabela flexifun_db.users: ~8 rows (aproximadamente)
INSERT INTO `users` (`id`, `email`, `password_hash`, `role`, `created_at`, `last_login_at`, `is_active`) VALUES
	(1, 'admin@flexifun.com', '$2y$10$vTJeQZSUe5Bqmw8st9VtUObS5HPn/ikStSXcIRnX.ZbKsII/4ut4W', 'admin', '2025-12-07 16:53:44', NULL, 1),
	(2, 'fisio@flexifun.com', '$2y$10$myhM2sv/ab7DnlY54XCnhOyrZNKpBzSZVYQJz/DEO/8i8NaFprGkC', 'fisioterapeuta', '2025-12-07 16:53:44', '2026-02-10 01:35:38', 1),
	(4, 'pacA@flexifun.com', '$2y$10$scN/F/8COHQzz2V0ffHpv.E/skAMtQ7d.2TfRD24QveGh11iRQBbi', 'paciente', '2025-12-07 19:36:13', NULL, 1),
	(5, 'paciente1@flexifun.com', '$2y$10$7WhWckY5yYBcrSNcKpmQdu0mDu/pLp149WC/m7d32O1y70cYytc7W', 'paciente', '2025-12-07 19:39:15', NULL, 1),
	(6, 'paciente2@flexifun.com', '$2y$10$i3NPVghd95SiLFDUZVQh4.hxoa959NLI4kFY06PYafvtz11q7/67G', 'paciente', '2025-12-07 19:39:15', NULL, 1),
	(7, 'cat@gmail.com', '$2y$10$4/Hac2jW1kqi7CiFaOoJ5.hmnDlC6XNLnD3apyCTC7TlEkX0gEDmq', 'paciente', '2025-12-10 15:07:22', NULL, 1),
	(10, 'filipec11@gmail.com', '$2y$10$x5uNOMHJtF5MGnBe4uZzr.jaX5g8Q.O0QYpnf9VrTtFko1vj4q/Lu', 'paciente', '2026-02-08 18:37:59', '2026-02-11 01:01:24', 1),
	(11, 'mric@sapo.pt', '$2y$10$JjC4C.JJfHYnbdvuVVMfMOMRLPAjfHsA0cARA4EYYLUxdxvDSq2Wm', 'paciente', '2026-02-08 23:53:11', NULL, 1),
	(12, 'marta.pereira2004@gmail.com', '$2y$10$MKGF1pQpof9521lfQuss5.CCzv1ApcAOcS.uJ.g7dVE5xZgCVwoKS', 'paciente', '2026-02-10 01:38:48', NULL, 1);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
