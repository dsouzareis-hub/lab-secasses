CREATE DATABASE IF NOT EXISTS senai_db;
USE senai_db;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- =========================
-- TABELA: usuarios
-- =========================
CREATE TABLE usuarios (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL,
  data TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  user_id VARCHAR(36) NOT NULL UNIQUE,

  INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- TABELA: itens
-- =========================
CREATE TABLE itens (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(255) NOT NULL,
  descricao VARCHAR(255) NOT NULL,

  user_id VARCHAR(36) NOT NULL,

  INDEX idx_user_id (user_id),

  CONSTRAINT fk_user_uuid
    FOREIGN KEY (user_id) REFERENCES usuarios(user_id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- TABELA: login_attempts
-- =========================
CREATE TABLE login_attempts (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  ip VARCHAR(45) NOT NULL,
  email VARCHAR(255) NOT NULL,
  tentativas INT DEFAULT 0,
  bloqueado_ate INT DEFAULT 0,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  UNIQUE KEY unique_combo (email, ip)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- DADOS INICIAIS
-- =========================

INSERT INTO usuarios (nome, email, senha, user_id)
VALUES (

);

INSERT INTO itens (nome, descricao, user_id)
VALUES (

);

COMMIT;