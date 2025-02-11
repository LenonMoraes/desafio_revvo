
-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS desafio_revvo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Usar o banco de dados
USE desafio_revvo;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    telefone VARCHAR(20),
    data_nascimento DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Índices para melhorar performance
CREATE INDEX idx_nome ON usuarios(nome);
CREATE INDEX idx_email ON usuarios(email);

-- Usuário de exemplo
INSERT INTO usuarios (nome, email, telefone, data_nascimento) VALUES 
(
    'Administrador', 
    'admin@revvo.com', 
    '(11) 99999-9999', 
    '1990-01-01'
) ON DUPLICATE KEY UPDATE nome = nome;

-- Criar usuário de acesso ao banco de dados
CREATE USER IF NOT EXISTS 'revvo_user'@'localhost' IDENTIFIED BY 'revvo_password';
GRANT ALL PRIVILEGES ON desafio_revvo.* TO 'revvo_user'@'localhost';
FLUSH PRIVILEGES;

-- Comentários para documentação
ALTER TABLE usuarios 
COMMENT = 'Tabela de usuários do sistema de cadastro';

-- Verificação da criação
SELECT 'Banco de dados e tabelas criados com sucesso!' AS mensagem;