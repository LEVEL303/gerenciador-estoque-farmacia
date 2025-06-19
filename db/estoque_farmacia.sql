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

INSERT INTO produtos (cod_barras, nome, descricao, grupo, classificacao, fabricante, validade, quantidade, medicamento_controlado, principio_ativo, registro_ms, preco, id_usuario) VALUES
('7891010100018', 'Paracetamol 500mg', 'Analgésico e antitérmico para dores leves e febre', 'medicamento', 'generico', 'MedGen', '2026-12-31', 150, FALSE, 'Paracetamol', '1.0001.0001.001-1', 12.50, 1),
('7891010100025', 'Shampoo Anticaspa 200ml', 'Shampoo para tratamento de caspa com piritionato de zinco', 'perfumaria', NULL, 'Beleza Pura', '2027-06-15', 80, FALSE, NULL, NULL, 25.99, 2),
('7891010100032', 'Band-aid Padrão', 'Curativo adesivo para pequenos cortes e arranhões', 'diversos', NULL, 'Primeiros Socorros S.A.', '2028-03-01', 300, FALSE, NULL, NULL, 8.75, 3),
('7891010100049', 'Amoxicilina 500mg', 'Antibiótico de amplo espectro', 'medicamento', 'etico', 'PharmaGrand', '2026-09-20', 75, FALSE, 'Amoxicilina', '1.0002.0005.002-8', 45.00, 1),
('7891010100056', 'Creme Hidratante Facial', 'Hidratante com ácido hialurônico para todos os tipos de pele', 'perfumaria', NULL, 'DermaCare', '2027-11-10', 120, FALSE, NULL, NULL, 59.90, 2),
('7891010100063', 'Fio Dental 50m', 'Fio dental encerado com menta', 'diversos', NULL, 'Higiene Total', '2029-01-05', 200, FALSE, NULL, NULL, 6.20, 3),
('7891010100070', 'Omeprazol 20mg', 'Redutor de acidez estomacal', 'medicamento', 'similar', 'GenFarma', '2026-07-25', 100, FALSE, 'Omeprazol', '1.0003.0010.003-5', 28.30, 1),
('7891010100087', 'Protetor Solar FPS 50', 'Protetor solar facial e corporal com alta proteção', 'perfumaria', NULL, 'Sol & Pele', '2027-08-01', 90, FALSE, NULL, NULL, 75.00, 2),
('7891010100094', 'Álcool em Gel 70% 500ml', 'Antisséptico para as mãos', 'diversos', NULL, 'Limpeza Já', '2028-04-20', 250, FALSE, NULL, NULL, 15.00, 3),
('7891010100100', 'Clonazepam 2mg', 'Ansiolítico e anticonvulsivante', 'medicamento', 'etico', 'NeuroPharma', '2026-10-01', 30, TRUE, 'Clonazepam', '1.0004.0015.004-2', 95.00, 1);
