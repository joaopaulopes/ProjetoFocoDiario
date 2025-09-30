



-- Configurações iniciais
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- Use o banco de dados focodiario (crie caso nao exista )
CREATE DATABASE IF NOT EXISTS focodiario;
USE focodiario;

-- -----------------------------------------------------
-- Tabela `administrador`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `administrador` (
  `id_admin` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `senha` VARCHAR(255) NOT NULL,
  `nivel_acesso` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id_admin`),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC));

-- -----------------------------------------------------
-- Tabela `noticias`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `noticias` (
  `id_noticia` INT NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(255) NOT NULL,
  `resumo` TEXT NOT NULL,
  `link_fonte` VARCHAR(255) NOT NULL,
  `nome_fonte` VARCHAR(100) NOT NULL,
  `data_publicacao` DATETIME NOT NULL,
  `editoria` VARCHAR(100) NOT NULL,
  `cliques` INT NULL DEFAULT 0,
  PRIMARY KEY (`id_noticia`));

-- -----------------------------------------------------
-- Tabela `usuario`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuario` (
  `id_usuario` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `senha` VARCHAR(255) NOT NULL,
  `data_cadastro` DATE NOT NULL,
  `status_conta` VARCHAR(50) NULL DEFAULT 'Pendente de validação',
  PRIMARY KEY (`id_usuario`),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC));

-- -----------------------------------------------------
-- Tabela `comentarios`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `comentarios` (
  `id_comentario` INT NOT NULL AUTO_INCREMENT,
  `id_noticia` INT NULL DEFAULT NULL,
  `id_usuario` INT NULL DEFAULT NULL,
  `texto_comentario` TEXT NOT NULL,
  `data_comentario` DATETIME NOT NULL,
  PRIMARY KEY (`id_comentario`),
  INDEX `id_noticia_idx` (`id_noticia` ASC),
  INDEX `id_usuario_idx` (`id_usuario` ASC),
  CONSTRAINT `fk_comentarios_noticias`
    FOREIGN KEY (`id_noticia`)
    REFERENCES `noticias` (`id_noticia`),
  CONSTRAINT `fk_comentarios_usuario`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `usuario` (`id_usuario`));

-- -----------------------------------------------------
-- Tabela `favoritos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `favoritos` (
  `id_favorito` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NULL DEFAULT NULL,
  `id_noticia` INT NULL DEFAULT NULL,
  `data_adicao` DATETIME NOT NULL,
  PRIMARY KEY (`id_favorito`),
  UNIQUE INDEX `id_usuario_noticia_UNIQUE` (`id_usuario` ASC, `id_noticia` ASC),
  INDEX `id_noticia_idx` (`id_noticia` ASC),
  CONSTRAINT `fk_favoritos_usuario`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `usuario` (`id_usuario`),
  CONSTRAINT `fk_favoritos_noticias`
    FOREIGN KEY (`id_noticia`)
    REFERENCES `noticias` (`id_noticia`));

-- -----------------------------------------------------
-- Tabela `feedback`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `feedback` (
  `id_feedback` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NULL DEFAULT NULL,
  `assunto` VARCHAR(255) NULL DEFAULT NULL,
  `mensagem` TEXT NOT NULL,
  `data_envio` DATETIME NOT NULL,
  `status` VARCHAR(50) NULL DEFAULT 'Novo',
  PRIMARY KEY (`id_feedback`),
  INDEX `id_usuario_idx` (`id_usuario` ASC),
  CONSTRAINT `fk_feedback_usuario`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `usuario` (`id_usuario`));

-- -----------------------------------------------------
-- Tabela `fontes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fontes` (
  `id_fonte` INT NOT NULL AUTO_INCREMENT,
  `nome_fonte` VARCHAR(100) NOT NULL,
  `url_base` VARCHAR(255) NOT NULL,
  `url_feed` VARCHAR(255) NOT NULL,
  `status_coleta` VARCHAR(50) NULL DEFAULT 'Ativa',
  PRIMARY KEY (`id_fonte`),
  UNIQUE INDEX `nome_fonte_UNIQUE` (`nome_fonte` ASC));

-- -----------------------------------------------------
-- Tabela `log_coleta`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `log_coleta` (
  `id_log` INT NOT NULL AUTO_INCREMENT,
  `data_hora` DATETIME NOT NULL,
  `status` VARCHAR(50) NOT NULL,
  `mensagem` TEXT NULL DEFAULT NULL,
  `fonte_id` INT NULL DEFAULT NULL,
  PRIMARY KEY (`id_log`),
  INDEX `fonte_id_idx` (`fonte_id` ASC),
  CONSTRAINT `fk_log_coleta_fontes`
    FOREIGN KEY (`fonte_id`)
    REFERENCES `fontes` (`id_fonte`));

-- -----------------------------------------------------
-- Tabela `tags`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `tags` (
  `id_tag` INT NOT NULL AUTO_INCREMENT,
  `nome_tag` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id_tag`),
  UNIQUE INDEX `nome_tag_UNIQUE` (`nome_tag` ASC));

-- -----------------------------------------------------
-- Tabela `noticias_tags`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `noticias_tags` (
  `id_noticia_tag` INT NOT NULL AUTO_INCREMENT,
  `id_noticia` INT NULL DEFAULT NULL,
  `id_tag` INT NULL DEFAULT NULL,
  PRIMARY KEY (`id_noticia_tag`),
  INDEX `id_noticia_idx` (`id_noticia` ASC),
  INDEX `id_tag_idx` (`id_tag` ASC),
  CONSTRAINT `fk_noticias_tags_noticias`
    FOREIGN KEY (`id_noticia`)
    REFERENCES `noticias` (`id_noticia`),
  CONSTRAINT `fk_noticias_tags_tags`
    FOREIGN KEY (`id_tag`)
    REFERENCES `tags` (`id_tag`));

-- Finaliza as configurações
SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
