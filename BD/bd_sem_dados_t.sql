/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE DATABASE IF NOT EXISTS `restaurantedb` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `restaurantedb`;

CREATE TABLE IF NOT EXISTS `avaliacoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_restaurante` int NOT NULL,
  `comida` int NOT NULL,
  `servico` int NOT NULL,
  `valor` int NOT NULL,
  `ambiente` int NOT NULL,
  `comentario` text,
  `data_avaliacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_restaurante` (`id_restaurante`),
  CONSTRAINT `avaliacoes_chk_1` CHECK ((`comida` between 0 and 5)),
  CONSTRAINT `avaliacoes_chk_2` CHECK ((`servico` between 0 and 5)),
  CONSTRAINT `avaliacoes_chk_3` CHECK ((`valor` between 0 and 5)),
  CONSTRAINT `avaliacoes_chk_4` CHECK ((`ambiente` between 0 and 5))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `categoria` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `descricao` text,
  `id_restaurante` int DEFAULT NULL,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_restaurante` (`id_restaurante`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `despesas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo` enum('lucro','despesa') NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `empregado` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `cargo` enum('empregado','cozinheiro','gerente') NOT NULL,
  `id_restaurante` int NOT NULL,
  `utilizador_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_restaurante` (`id_restaurante`),
  KEY `utilizador_id` (`utilizador_id`),
  CONSTRAINT `empregado_ibfk_1` FOREIGN KEY (`id_restaurante`) REFERENCES `restaurante` (`id`),
  CONSTRAINT `empregado_ibfk_2` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizador` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `fornecedor` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome_representante` varchar(255) NOT NULL,
  `telefone_representante` varchar(20) DEFAULT NULL,
  `email_representante` varchar(191) DEFAULT NULL,
  `nif_empresa` varchar(20) NOT NULL,
  `morada_sede` varchar(255) DEFAULT NULL,
  `codigo_postal` varchar(20) DEFAULT NULL,
  `distrito` varchar(255) DEFAULT NULL,
  `pais` varchar(255) DEFAULT NULL,
  `iban` varchar(34) DEFAULT NULL,
  `id_proprietario` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nif_empresa` (`nif_empresa`),
  KEY `fornecedor_ibfk_1` (`id_proprietario`),
  CONSTRAINT `fornecedor_ibfk_1` FOREIGN KEY (`id_proprietario`) REFERENCES `utilizador` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `imagem_restaurante` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_restaurante` int NOT NULL,
  `caminho_imagem` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_restaurante` (`id_restaurante`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `ingrediente_prato` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_prato` int DEFAULT NULL,
  `id_produto` int DEFAULT NULL,
  `quantidade_necessaria` decimal(10,2) NOT NULL,
  `unidade_medida` enum('Kg','Gr','L','Ml','unidade') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_prato` (`id_prato`),
  KEY `id_produto` (`id_produto`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `pedidos` (
  `id` int NOT NULL,
  `id_restaurante` int NOT NULL,
  `id_mesa` int NOT NULL,
  `id_prato` int NOT NULL,
  `quantidade` int NOT NULL,
  `id_pedido` int NOT NULL AUTO_INCREMENT,
  `data_pedido` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('Pendente','Em Preparacao','Pronto','Entregue','Pago','Cancelado') DEFAULT 'Pendente',
  `preco_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id_pedido`),
  KEY `id_restaurante` (`id_restaurante`),
  KEY `id_prato` (`id_prato`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `pedidos_arquivados` (
  `id` int NOT NULL DEFAULT '0',
  `id_restaurante` int NOT NULL,
  `id_mesa` int NOT NULL,
  `id_prato` int NOT NULL,
  `quantidade` int NOT NULL,
  `id_pedido` int NOT NULL,
  `data_pedido` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('Pendente','Em Preparacao','Pronto','Entregue','Pago','Cancelado') DEFAULT 'Pendente',
  `preco_total` decimal(10,2) NOT NULL DEFAULT '0.00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `pedido_itens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_pedido` int NOT NULL,
  `id_prato` int NOT NULL,
  `quantidade` int NOT NULL,
  `id_restaurante` int NOT NULL,
  `preco_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `id_pedido` (`id_pedido`),
  KEY `id_prato` (`id_prato`),
  KEY `id_restaurante` (`id_restaurante`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `pratos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_restaurante` int NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text,
  `preco` decimal(10,2) NOT NULL,
  `data_criacao` datetime DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_id_restaurante` (`id_restaurante`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `produto` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `descricao` text,
  `quantidade` decimal(10,2) NOT NULL,
  `unidade_medida` enum('Kg','Gr','L','Ml','Unidade') NOT NULL,
  `id_categoria` int DEFAULT NULL,
  `id_restaurante` int DEFAULT NULL,
  `id_fornecedor` int DEFAULT NULL,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_categoria` (`id_categoria`),
  KEY `id_restaurante` (`id_restaurante`),
  KEY `id_fornecedor` (`id_fornecedor`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `reserva` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome_completo` varchar(255) NOT NULL,
  `telefone` varchar(15) NOT NULL,
  `email` varchar(255) NOT NULL,
  `preferencia_contato` enum('telefone','whatsapp','email') NOT NULL,
  `data_reserva` date NOT NULL,
  `hora_reserva` time NOT NULL,
  `num_pessoas` int NOT NULL,
  `id_restaurante` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_restaurante` (`id_restaurante`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `restaurante` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome_empresa` varchar(255) NOT NULL,
  `nif` varchar(20) NOT NULL,
  `designacao_legal` varchar(255) DEFAULT NULL,
  `morada` varchar(255) DEFAULT NULL,
  `codigo_postal` varchar(20) DEFAULT NULL,
  `distrito` varchar(255) DEFAULT NULL,
  `pais` varchar(255) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email_contato` varchar(255) NOT NULL,
  `numero_contato` varchar(25) NOT NULL,
  `nome_banco` varchar(255) DEFAULT NULL,
  `iban` varchar(34) DEFAULT NULL,
  `titular_conta` varchar(255) DEFAULT NULL,
  `id_proprietario` int DEFAULT NULL,
  `status` enum('pendente','ativo','reprovado') DEFAULT 'pendente',
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `capacidade` int NOT NULL,
  `preco_medio` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nif` (`nif`),
  KEY `id_proprietario` (`id_proprietario`),
  CONSTRAINT `restaurante_ibfk_1` FOREIGN KEY (`id_proprietario`) REFERENCES `utilizador` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `restaurante_tipocozinha` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_restaurante` int NOT NULL,
  `id_tipo_cozinha` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_restaurante` (`id_restaurante`),
  KEY `id_tipo_cozinha` (`id_tipo_cozinha`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `stock_restaurante` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_produto` int DEFAULT NULL,
  `id_restaurante` int DEFAULT NULL,
  `quantidade` decimal(10,2) DEFAULT NULL,
  `alerta_critico` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_produto` (`id_produto`),
  KEY `id_restaurante` (`id_restaurante`),
  CONSTRAINT `stock_restaurante_ibfk_1` FOREIGN KEY (`id_produto`) REFERENCES `produto` (`id`),
  CONSTRAINT `stock_restaurante_ibfk_2` FOREIGN KEY (`id_restaurante`) REFERENCES `restaurante` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `tipocozinha` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `utilizador` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `email` varchar(191) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('cliente','proprietario','fornecedor','admin') NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `nif` varchar(15) DEFAULT NULL,
  `pais` varchar(50) DEFAULT NULL,
  `distrito` varchar(50) DEFAULT NULL,
  `morada` varchar(100) DEFAULT NULL,
  `codigo_postal` varchar(10) DEFAULT NULL,
  `id_restaurante` int DEFAULT NULL,
  `id_fornecedor` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_id_restaurante` (`id_restaurante`),
  KEY `fk_id_fornecedor` (`id_fornecedor`),
  CONSTRAINT `fk_utilizador_fornecedor` FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedor` (`id`),
  CONSTRAINT `fk_utilizador_restaurante` FOREIGN KEY (`id_restaurante`) REFERENCES `restaurante` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
