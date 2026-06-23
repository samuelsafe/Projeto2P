
CREATE DATABASE  login_sistema
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE login_sistema;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
/*
INSERT INTO usuarios (nome, email, senha)
VALUES (
    'Usuário Teste',
    'teste@email.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'

);