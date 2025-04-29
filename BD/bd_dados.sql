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
  `id_utilizador` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_restaurante` (`id_restaurante`),
  KEY `id_utilizador` (`id_utilizador`),
  CONSTRAINT `avaliacoes_chk_1` CHECK ((`comida` between 0 and 5)),
  CONSTRAINT `avaliacoes_chk_2` CHECK ((`servico` between 0 and 5)),
  CONSTRAINT `avaliacoes_chk_3` CHECK ((`valor` between 0 and 5)),
  CONSTRAINT `avaliacoes_chk_4` CHECK ((`ambiente` between 0 and 5))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*!40000 ALTER TABLE `avaliacoes` DISABLE KEYS */;
INSERT INTO `avaliacoes` (`id`, `id_restaurante`, `comida`, `servico`, `valor`, `ambiente`, `comentario`, `data_avaliacao`, `id_utilizador`) VALUES
	(1, 1, 5, 5, 5, 5, 'Muito Bom', '2024-12-21 10:10:18', 2),
	(2, 1, 4, 4, 4, 4, 'Bom', '2024-12-21 10:10:44', 2),
	(3, 1, 4, 5, 3, 4, 'aW', '2024-12-21 10:11:17', 3),
	(4, 1, 5, 4, 4, 3, 'uy', '2025-01-09 14:52:26', 4),
	(5, 1, 5, 4, 4, 3, 'uy', '2025-01-09 14:53:00', 5),
	(6, 1, 4, 3, 2, 2, 's', '2025-03-21 19:31:23', 2),
	(7, 1, 5, 5, 5, 5, 'Bom', '2025-03-21 19:57:57', 1);
/*!40000 ALTER TABLE `avaliacoes` ENABLE KEYS */;

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

/*!40000 ALTER TABLE `categoria` DISABLE KEYS */;
INSERT INTO `categoria` (`id`, `nome`, `descricao`, `id_restaurante`, `data_criacao`, `data_atualizacao`) VALUES
	(2, 'Carnes', 'Carnes', 1, '2025-02-04 08:27:05', '2025-02-06 12:14:33'),
	(3, 'Suremesas', '', 1, '2025-02-06 12:14:58', '2025-02-06 12:18:07'),
	(4, 'Vegetariano', 'Vegetariano', 1, '2025-02-06 12:15:38', '2025-02-06 12:17:53'),
	(5, 'Peixe', 'Peixe', 1, '2025-02-06 12:15:48', '2025-02-06 12:17:38'),
	(6, 'Sopa', 'Sopas', 1, '2025-02-06 12:18:35', '2025-02-06 12:18:35');
/*!40000 ALTER TABLE `categoria` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `fornecedor` (
  `id` int NOT NULL AUTO_INCREMENT,
  `empresa` varchar(255) NOT NULL,
  `email_empresa` varchar(191) DEFAULT NULL,
  `telefone_empresa` varchar(20) DEFAULT NULL,
  `nif_empresa` varchar(20) NOT NULL,
  `morada_sede` varchar(255) DEFAULT NULL,
  `codigo_postal` varchar(20) DEFAULT NULL,
  `distrito` varchar(255) DEFAULT NULL,
  `pais` varchar(255) DEFAULT NULL,
  `iban` varchar(34) DEFAULT NULL,
  `status` enum('pendente','ativo','reprovado') DEFAULT 'pendente',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nif_empresa` (`nif_empresa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `fornecedor` (`id`, `empresa`, `email_empresa`, `telefone_empresa`, `nif_empresa`, `morada_sede`, `codigo_postal`, `distrito`, `pais`, `iban`, `status`) VALUES
	(1, 'Makro', 'makro@gmail.com', '963090803', '262831976', 'Rua de Alfragide', '1050-211', 'Lisboa', 'Portugal', '76436736347735', 'ativo'),
	(2, 'Continente', 'continente@gmail.com', '923982222', '221212112', 'Rua da Flor', '1232-123', 'Lisboa', 'Portugal', 'PT50323232323', 'ativo');

CREATE TABLE IF NOT EXISTS `funcionarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cargo` enum('Gerente','Chefe de Cozinha','Cozinheiro','Ajudante de Cozinha','Empregado de Mesa') NOT NULL,
  `id_utilizador` int DEFAULT NULL,
  `id_restaurante` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_utilizador` (`id_utilizador`),
  KEY `id_restaurante` (`id_restaurante`),
  CONSTRAINT `fk_funcionarios_restaurante` FOREIGN KEY (`id_restaurante`) REFERENCES `restaurante` (`id`),
  CONSTRAINT `funcionarios_ibfk_1` FOREIGN KEY (`id_utilizador`) REFERENCES `utilizador` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `funcionarios` (`id`, `cargo`, `id_utilizador`, `id_restaurante`) VALUES
	(7, 'Gerente', 6, 1),
	(8, 'Chefe de Cozinha', 9, 1),
	(9, 'Empregado de Mesa', 10, 1);

CREATE TABLE IF NOT EXISTS `imagem_restaurante` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_restaurante` int NOT NULL,
  `caminho_imagem` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_restaurante` (`id_restaurante`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*!40000 ALTER TABLE `imagem_restaurante` DISABLE KEYS */;
INSERT INTO `imagem_restaurante` (`id`, `id_restaurante`, `caminho_imagem`) VALUES
	(2, 2, 'uploads/img_673f1fef72647.jpg'),
	(3, 3, 'uploads/img_673f469b85f2c.jpg'),
	(71, 4, '/PAP/geral/uploads/1742593188_0_1741634361_83f986f4-1bbd-4fd3-9521-b2267d37fe2d.jpg'),
	(58, 1, '/PAP/geral/uploads/1741634322_82c82411-6649-4f6b-b2e7-6d2f4c666891.jpg'),
	(59, 1, '/PAP/geral/uploads/1741634361_83f986f4-1bbd-4fd3-9521-b2267d37fe2d.jpg'),
	(60, 1, '/PAP/geral/uploads/1741634373_66774581-a260-4890-9805-2f496faa9b85_1.jpg'),
	(64, 1, '/PAP/geral/uploads/1741634620_1_5ebbddc0-105c-4466-8d4b-135e75519df8.jpg'),
	(65, 1, '/PAP/geral/uploads/1741634620_2_82c82411-6649-4f6b-b2e7-6d2f4c666891.jpg'),
	(66, 1, '/PAP/geral/uploads/1741634620_3_83f986f4-1bbd-4fd3-9521-b2267d37fe2d.jpg'),
	(67, 1, '/PAP/geral/uploads/1741634620_5_66774581-a260-4890-9805-2f496faa9b85_1.jpg'),
	(68, 1, '/PAP/geral/uploads/1741634620_6_1738097732_3015961_stock-photo-lonely-weathered-bench-at-the-jungle-forest-park.jpg'),
	(69, 1, '/PAP/geral/uploads/1741634620_7_img_66f4ac89b2bc5.jpg'),
	(62, 1, '/PAP/geral/uploads/1741634409_img_678a72337bd64.jpg'),
	(63, 1, '/PAP/geral/uploads/1741634419_img_678a454bcbe85.jpg'),
	(61, 1, '/PAP/geral/uploads/1741634398_5ebbddc0-105c-4466-8d4b-135e75519df8.jpg');
/*!40000 ALTER TABLE `imagem_restaurante` ENABLE KEYS */;

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

/*!40000 ALTER TABLE `ingrediente_prato` DISABLE KEYS */;
INSERT INTO `ingrediente_prato` (`id`, `id_prato`, `id_produto`, `quantidade_necessaria`, `unidade_medida`) VALUES
	(1, 1, 1, 0.00, 'Kg'),
	(2, 2, 2, 99.00, 'Gr'),
	(3, 3, 2, 32.00, 'Gr'),
	(4, 3, 4, 211.00, 'Gr'),
	(5, 4, 4, 1.00, 'unidade'),
	(6, 5, 2, 100.00, 'Gr'),
	(7, 5, 4, 200.00, 'Gr'),
	(8, 6, 4, 100.00, 'Gr'),
	(9, 7, 2, 1.00, 'Kg'),
	(10, 8, 2, 1.00, 'Gr'),
	(11, 8, 4, 21.00, 'Ml'),
	(12, 8, 2, 12.00, 'unidade'),
	(13, 9, 2, 12.00, 'Gr'),
	(14, 10, 3, 6.00, 'Gr'),
	(15, 11, 2, 100.00, 'Gr'),
	(16, 1, 2, 150.00, 'Gr'),
	(17, 1, 4, 222.00, 'Gr');
/*!40000 ALTER TABLE `ingrediente_prato` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `pedidos` (
  `id_pedido` int NOT NULL AUTO_INCREMENT,
  `id_restaurante` int NOT NULL,
  `id_mesa` int NOT NULL,
  `id_prato` int NOT NULL,
  `quantidade` int NOT NULL,
  `data_pedido` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('Pendente','Em Preparacao','Pronto','Entregue','Pago','Cancelado') DEFAULT 'Pendente',
  `preco_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `observacoes` text,
  PRIMARY KEY (`id_pedido`),
  KEY `id_restaurante` (`id_restaurante`),
  KEY `id_prato` (`id_prato`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `pedidos` (`id_pedido`, `id_restaurante`, `id_mesa`, `id_prato`, `quantidade`, `data_pedido`, `status`, `preco_total`, `observacoes`) VALUES
	(2, 2, 2, 1, 1, '2025-02-20 12:25:00', 'Em Preparacao', 21.00, 'rfre'),
	(4, 1, 2, 1, 1, '2025-02-20 12:26:02', 'Em Preparacao', 21.00, 'rfre'),
	(5, 1, 1, 1, 1, '2025-02-20 12:28:51', 'Pronto', 84.00, 'a'),
	(6, 1, 2, 1, 55, '2025-03-14 11:52:44', 'Pendente', 1155.00, 'a'),
	(7, 1, 6, 1, 1, '2025-03-14 11:53:00', 'Pendente', 21.00, '');

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

/*!40000 ALTER TABLE `pedidos_arquivados` DISABLE KEYS */;
INSERT INTO `pedidos_arquivados` (`id`, `id_restaurante`, `id_mesa`, `id_prato`, `quantidade`, `id_pedido`, `data_pedido`, `status`, `preco_total`) VALUES
	(1, 1, 2, 1, 1, 10, '2025-02-20 12:25:55', 'Pago', 21.00),
	(1, 1, 1, 1, 2, 8, '2025-03-21 00:28:25', 'Cancelado', 30.00);
/*!40000 ALTER TABLE `pedidos_arquivados` ENABLE KEYS */;

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

/*!40000 ALTER TABLE `pedido_itens` DISABLE KEYS */;
INSERT INTO `pedido_itens` (`id`, `id_pedido`, `id_prato`, `quantidade`, `id_restaurante`, `preco_total`) VALUES
	(1, 5, 1, 4, 1, 84.00);
/*!40000 ALTER TABLE `pedido_itens` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `pratos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_restaurante` int NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text,
  `preco` decimal(10,2) NOT NULL,
  `data_criacao` datetime DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id_categoria` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_id_restaurante` (`id_restaurante`),
  KEY `fk_id_categoria` (`id_categoria`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*!40000 ALTER TABLE `pratos` DISABLE KEYS */;
INSERT INTO `pratos` (`id`, `id_restaurante`, `nome`, `descricao`, `preco`, `data_criacao`, `data_atualizacao`, `id_categoria`) VALUES
	(1, 1, 'Carne de Vaca', 'Carne de Vaca', 15.00, '2025-03-21 00:27:17', '2025-03-21 00:27:17', 2);
/*!40000 ALTER TABLE `pratos` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `produtos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `descricao` text,
  `quantidade` decimal(10,2) NOT NULL,
  `unidade_medida` enum('Kg','Gr','L','Ml','Unidade') NOT NULL,
  `id_categoria` int DEFAULT NULL,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id_restaurante` int DEFAULT NULL,
  `id_fornecedor` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_categoria` (`id_categoria`),
  KEY `id_restaurante` (`id_restaurante`),
  KEY `id_fornecedor` (`id_fornecedor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `produtos` (`id`, `nome`, `descricao`, `quantidade`, `unidade_medida`, `id_categoria`, `data_criacao`, `data_atualizacao`, `id_restaurante`, `id_fornecedor`) VALUES
	(2, 'Carne de Vacas', 'Carnes', 122.00, 'Gr', 2, '2025-02-05 23:20:24', '2025-03-20 22:46:04', 1, 1),
	(3, 'Carne de Vaca', 'Carne', 12.00, 'Kg', 2, '2025-02-05 23:21:04', '2025-03-20 22:08:07', 1, 1),
	(4, 'Massa', 'Mass', 5.00, 'Gr', 2, '2025-02-06 10:57:32', '2025-02-06 10:57:32', 1, 2),
	(5, 'Carne de Vaca', '', 2.00, 'Gr', 2, '2025-03-14 11:53:25', '2025-03-14 11:53:25', 1, 2);

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

/*!40000 ALTER TABLE `reserva` DISABLE KEYS */;
INSERT INTO `reserva` (`id`, `nome_completo`, `telefone`, `email`, `preferencia_contato`, `data_reserva`, `hora_reserva`, `num_pessoas`, `id_restaurante`) VALUES
	(1, 'Steve Santos', '929270951', 'luis.teixeira.rodrigues30@gmail.com', 'telefone', '2332-11-16', '12:01:00', 54, 1),
	(2, 'Steve Santos', '929270951', 'luis.teixeira.rodrigues30@gmail.com', 'telefone', '2332-11-16', '12:01:00', 54, 1),
	(3, 'Steve Santos', '929270951', 'luis.teixeira.rodrigues30@gmail.com', 'telefone', '2332-11-16', '12:01:00', 54, 1),
	(4, 'Steve Santos', '929270951', 'luis.teixeira.rodrigues30@gmail.com', 'telefone', '2332-11-16', '12:01:00', 54, 1),
	(5, 'Steve Santos', '929270951', 'luis.teixeira.rodrigues30@gmail.com', 'telefone', '2332-11-16', '12:01:00', 54, 1),
	(6, 'Steve Santos', '929270951', 'luis.teixeira.rodrigues30@gmail.com', 'telefone', '2332-11-16', '12:01:00', 54, 1),
	(12, 'Teixeira', '35', 'LEWLD@gmail.com', 'email', '2233-03-23', '12:32:00', 3, 1),
	(14, 'Luis Teixeira', '963090803', 'luis.teixeira.rodrigues30@gmail.com', 'telefone', '2025-03-19', '19:00:00', 4, 1),
	(13, 'Carla Rodrigues', '967055520', 'karla.p.rodrigues@gmail.com', 'telefone', '2025-03-19', '12:00:00', 1, 1),
	(10, 'Steve Santos', '929270951', 'luis.teixeira.rodrigues30@gmail.com', 'whatsapp', '2025-03-10', '21:11:00', 34, 1),
	(11, 'Carla Rodrigues', '967055520', 'karla.p.rodrigues@gmail.com', 'email', '2025-03-11', '21:11:00', 221, 2),
	(15, 'A', '22323232233', 'luis.teixeira.rodrigues30@gmail.com', 'telefone', '2025-03-23', '19:30:00', 10, 1),
	(16, 'Luis Teixeira', '963090803', 'luis.teixeira.rodrigues@gmail.com', 'email', '2025-03-20', '12:00:00', 1, 1);
/*!40000 ALTER TABLE `reserva` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `restaurante` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome_empresa` varchar(255) NOT NULL,
  `nif` varchar(20) NOT NULL,
  `designacao_legal` varchar(255) DEFAULT NULL,
  `morada` varchar(255) DEFAULT NULL,
  `codigo_postal` varchar(20) DEFAULT NULL,
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
  `intervalo_precos` int DEFAULT NULL,
  `distrito` enum('Açores','Aveiro','Beja','Braga','Bragança','Castelo Branco','Coimbra','Évora','Faro','Guarda','Leiria','Lisboa','Madeira','Portalegre','Porto','Santarém','Setúbal','Viana do Castelo','Vila Real','Viseu') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nif` (`nif`),
  KEY `id_proprietario` (`id_proprietario`),
  CONSTRAINT `restaurante_ibfk_1` FOREIGN KEY (`id_proprietario`) REFERENCES `utilizador` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `restaurante` (`id`, `nome_empresa`, `nif`, `designacao_legal`, `morada`, `codigo_postal`, `pais`, `telefone`, `email_contato`, `numero_contato`, `nome_banco`, `iban`, `titular_conta`, `id_proprietario`, `status`, `criado_em`, `capacidade`, `intervalo_precos`, `distrito`) VALUES
	(1, 'Cozy', '262831953', 'Cozy.Lda', 'Largo Ratos', '1250-122', 'Portugal', '963090803', 'contacto@cozy.com', '923234344', 'CTT', '3243243553', 'Luis', 1, 'ativo', '2024-11-21 20:17:09', 20, 10, 'Lisboa'),
	(2, 'Tasca do Jorge', '4376436436', 'Taska.Lda', 'Av liberdade', '1500-023', 'Portugal', '926657522', 'contacto@tascajorge.com', '926657522', 'Caixa Geral', '76532767622323', 'Jorge', 2, 'ativo', '2024-11-21 20:17:09', 0, 15, 'Faro'),
	(3, 'Italian Republic', '7437347347', 'ItalianRepublic.Lda', 'Rua Augusto Aguiar', '1050-100', 'Portugal', '935318234', 'contact@italianrepublic.com', '935318234', 'Montepio', '232632737272', 'Maria', 3, 'ativo', '2024-11-21 20:17:09', 0, 20, 'Madeira'),
	(4, 'Japones&chines', '42222224', 'Japa.Lda', 'Picoas', '1050-082', 'Portugal', '926657522', 'contacto@japa.com', '926657522', 'Caixa', '754743399383', 'David', 5, 'ativo', '2024-11-26 12:00:00', 0, 25, 'Beja');

CREATE TABLE IF NOT EXISTS `restaurante_fornecedor` (
  `id_restaurante` int NOT NULL,
  `id_fornecedor` int NOT NULL,
  PRIMARY KEY (`id_restaurante`,`id_fornecedor`),
  KEY `id_fornecedor` (`id_fornecedor`),
  CONSTRAINT `restaurante_fornecedor_ibfk_1` FOREIGN KEY (`id_restaurante`) REFERENCES `restaurante` (`id`),
  CONSTRAINT `restaurante_fornecedor_ibfk_2` FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedor` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `restaurante_fornecedor` (`id_restaurante`, `id_fornecedor`) VALUES
	(1, 1);

CREATE TABLE IF NOT EXISTS `restaurante_tipocozinha` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_restaurante` int NOT NULL,
  `id_tipo_cozinha` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_restaurante` (`id_restaurante`),
  KEY `id_tipo_cozinha` (`id_tipo_cozinha`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*!40000 ALTER TABLE `restaurante_tipocozinha` DISABLE KEYS */;
INSERT INTO `restaurante_tipocozinha` (`id`, `id_restaurante`, `id_tipo_cozinha`) VALUES
	(1, 1, 1),
	(3, 3, 3),
	(4, 4, 4),
	(2, 2, 2),
	(5, 5, 5);
/*!40000 ALTER TABLE `restaurante_tipocozinha` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `tipocozinha` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `foto_categoria_link` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*!40000 ALTER TABLE `tipocozinha` DISABLE KEYS */;
INSERT INTO `tipocozinha` (`id`, `nome`, `foto_categoria_link`) VALUES
	(1, 'Americana', 'assets/images/categories/americana.png'),
	(2, 'Asas de frango', 'assets/images/categories/asas_de_frango.png'),
	(3, 'Asiática', 'assets/images/categories/asiatica.png'),
	(4, 'Batidos', 'assets/images/categories/batidos.png'),
	(5, 'Bubble tea', 'assets/images/categories/bubble_tea.png'),
	(6, 'Chinesa', 'assets/images/categories/chinesa.png'),
	(7, 'Churrasco', 'assets/images/categories/churrasco.png'),
	(8, 'Comida de rua', 'assets/images/categories/comida_de_rua.png'),
	(9, 'Coreana', 'assets/images/categories/coreana.png'),
	(10, 'Fast food', 'assets/images/categories/fast_food.png'),
	(11, 'Gelado', 'assets/images/categories/gelado.png'),
	(12, 'Grega', 'assets/images/categories/grega.png'),
	(13, 'Halal', 'assets/images/categories/halal.png'),
	(14, 'Havaiana', 'assets/images/categories/havaiana.png'),
	(15, 'Indiana', 'assets/images/categories/indiana.png'),
	(16, 'Italiana', 'assets/images/categories/italiana.png'),
	(17, 'Japonesa', 'assets/images/categories/japonesa.png'),
	(18, 'Marisco', 'assets/images/categories/marisco.png'),
	(19, 'Mexicana', 'assets/images/categories/mexicana.png'),
	(20, 'Padaria', 'assets/images/categories/padaria.png'),
	(21, 'Pequeno almoço', 'assets/images/categories/pequeno_almoco.png'),
	(22, 'Pizza', 'assets/images/categories/pizza.png'),
	(23, 'Poke', 'assets/images/categories/poke.png'),
	(24, 'Saladas', 'assets/images/categories/saladas.png'),
	(25, 'Sanduíche', 'assets/images/categories/sanduiche.png'),
	(26, 'Saudável', 'assets/images/categories/saudavel.png'),
	(27, 'Sobremesas', 'assets/images/categories/sobremesas.png'),
	(28, 'Soul food', 'assets/images/categories/soul_food.png'),
	(29, 'Sopa', 'assets/images/categories/sopa.png'),
	(30, 'Sushi', 'assets/images/categories/sushi.png'),
	(31, 'Tailandesa', 'assets/images/categories/tailandesa.png'),
	(32, 'Taiwanesa', 'assets/images/categories/taiwanesa.png'),
	(33, 'Vegan', 'assets/images/categories/vegan.png'),
	(34, 'Vietnamita', 'assets/images/categories/vietnamita.png');
/*!40000 ALTER TABLE `tipocozinha` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `utilizador` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `email` varchar(191) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('cliente','associado','proprietario','fornecedor','admin') NOT NULL,
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

INSERT INTO `utilizador` (`id`, `nome`, `email`, `senha`, `tipo`, `telefone`, `data_nascimento`, `nif`, `pais`, `distrito`, `morada`, `codigo_postal`, `id_restaurante`, `id_fornecedor`) VALUES
	(1, 'Luis Teixeira', 'luis@gmail.com', '$2y$10$8pCAjqhQmBUhcyfjnVJPeO5S675dHwJ5MQTM7mWO8maZ4czPdJRki', 'proprietario', '963090803', '1998-11-16', '262831953', 'Portugal', 'Lisboa', 'Largo Rato', '1250-122', 1, NULL),
	(2, 'Jorge Teixeira', 'jorge@gmail.com', '$2y$10$ASuZqLghC.JGVNBc5HR81.5yk6qaOdYHvYceXGQBCiPP8VweEsWAq', 'proprietario', '961632940', '1964-06-09', '4376437643', 'Portugal', 'Lisboa', 'Rato', '1250-125', 2, NULL),
	(3, 'Maria Viana', 'maria@gmail.com', '$2y$10$6igzJ09SG2LbVoQjKSL25OZTAe/dChIpjf65ecxpQANKWdJ1PhQ.G', 'proprietario', '935318234', '2006-07-16', '323242324', 'Portugal', 'Lisboa', 'Praça de Espanha', '1050-072', 3, NULL),
	(4, 'José Santos', 'jose@gmail.com', '$2y$10$GO/U2H4s5BtYLhT59cAK5ejheRp5S5TQjCFniqqrZv6MvmRIQgxv6', 'admin', '934879023', '1993-02-09', '233232332', 'Portugal', 'Faro', 'Rua do Jose', '8000-211', NULL, NULL),
	(5, 'David Teixeira', 'z', '$2y$10$YwMOBIoI951U8M6g9I8CJefFpPM6OsetDNe0NOaJlfEDtid.KMrfG', 'proprietario', '926657522', '2004-04-23', '4222223333', 'Portugal', 'Lisboa', 'Rua de Campolide', '1070-084', 4, NULL),
	(6, 'Carla Rodrigue', 'carla@gmail.com', '$2y$10$0vZDH8Aanvg0/5Rm7i79lOuVP7EetCWQ.T5XaF2u1y9vsSgfqY6SC', 'associado', '961112223', '1967-08-08', '223831974', 'Portugal', 'Lisboa', 'Rua do Carlos', '1070-084', NULL, NULL),
	(7, 'Forncedor', 'fornecedor@gmail.com', '$2y$10$SOp.EENju.SORJMZ1EF5IuZCO6Ivd6WFqNW.ORFWs6vOiLRqMTtAy', 'fornecedor', '98326112', '1998-12-12', '123456789', 'Portugal', 'Lisboa', 'Largo Hintze Ribeiro', '1250-122', NULL, 1),
	(8, 'Salvador Dias', 'salvador@gmail.com', '$2y$10$O8lXjTNQA4t1sGW4FmXI1.fD3VGIt0bIQA4Vka1wk7zK18T/P2BGO', 'cliente', '96121121221', '2099-02-12', '22123111', 'Portugal', 'Lisboa', 'Rua do Salvador', '2650-185', NULL, NULL),
	(9, 'Joao Rodrigues', 'joao@gmail.com', '$2y$10$Ar4HsVESou/q7FMzr2Qme.bu0pgAuavuUbetuI6rqi/x4vxUAxK/i', 'associado', '961422980', '1963-01-01', '262897541', 'Portugal', 'Lisboa', 'Rua de Odivelas', '1685-038', NULL, NULL),
	(10, 'Joaquim Chaves', 'joaquim@gmail.com', '$2y$10$3Lh2rmwO5oazitqSv8Aiq.ClJOIEY9QT06BeQNidhTwQwhlWW4Io6', 'associado', '976243098', '2003-12-31', '126787935', 'Portugal', 'Lisboa', 'Rua das Olaias', '1675-088', NULL, NULL),
	(11, 'Bernardo Maria', 'bernardo@gmail.com', '$2y$10$EB1hrqzwmjc0sR08jrC5P.EiVEUIgG4E.rxiR5yxaHBNuB1pHdSKq', 'fornecedor', '974562453', '1986-03-23', '142503843', 'Portugal', 'Lisboa', 'Rua das Flores', '1675-089', NULL, 2),
	(12, 'Martim Paz', 'martim@gmail.com', '$2y$10$8OmHShcD87LS1LgtnHp9Vu00nA5v/zizZS/grQGvQxx1LRJM3Kmny', 'cliente', '973 344 222', '2002-02-11', '323245444', 'Portugal', 'Lisboa', 'Rua do Martim', '1250-122', NULL, NULL);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
