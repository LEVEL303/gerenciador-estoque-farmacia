CREATE DATABASE estoque_farmacia;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(150) NOT NULL
);

CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cod_barras VARCHAR(50) NOT NULL UNIQUE,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT,
    grupo ENUM('medicamento', 'perfumaria', 'diversos') NOT NULL,
    classificacao ENUM('generico', 'etico', 'similar'),
    fabricante VARCHAR(150) NOT NULL,
    validade DATE NOT NULL,
    quantidade INT NOT NULL DEFAULT 0 CHECK (quantidade >= 0),
    medicamento_controlado BOOLEAN,
    principio_ativo VARCHAR(150),
    registro_ms VARCHAR(50),
    preco DECIMAL(10,2) NOT NULL,
    id_usuario INT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);
