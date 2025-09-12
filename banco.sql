CREATE DATABASE IF NOT EXISTS `portal_noticias`;
USE `portal_noticias`;


DROP TABLE IF EXISTS comentarios;
DROP TABLE IF EXISTS noticias;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS feedback;


CREATE TABLE usuarios (
    id_usuario INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE,
    senha VARCHAR(255) NOT NULL,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
    tipo_usuario ENUM('admin', 'comum') DEFAULT 'comum',
    PRIMARY KEY (id_usuario)
) ENGINE=InnoDB;


CREATE TABLE categorias (
    id_categoria INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    descricao VARCHAR(255),
    PRIMARY KEY (id_categoria)
) ENGINE=InnoDB;


CREATE TABLE noticias (
    id_noticia INT NOT NULL AUTO_INCREMENT,
    titulo VARCHAR(200) NOT NULL,
    conteudo TEXT NOT NULL,
    imagem_url VARCHAR(255),          
    data_publicacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_usuario INT NOT NULL,
    id_categoria INT NOT NULL,
    views INT DEFAULT 0,              
    PRIMARY KEY (id_noticia),
    CONSTRAINT fk_noticias_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_noticias_categoria
        FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;


CREATE TABLE comentarios (
    id_comentario INT NOT NULL AUTO_INCREMENT,
    conteudo TEXT NOT NULL,
    data_comentario DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_usuario INT NOT NULL,
    id_noticia INT NOT NULL,
    PRIMARY KEY (id_comentario),
    CONSTRAINT fk_comentarios_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_comentarios_noticia
        FOREIGN KEY (id_noticia) REFERENCES noticias(id_noticia)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;


CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    feedback TEXT NOT NULL,
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
