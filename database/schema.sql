CREATE DATABASE IF NOT EXISTS lessence
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE lessence;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('cliente', 'admin') DEFAULT 'cliente'
);

CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NOT NULL,
    nome VARCHAR(150) NOT NULL,
    marca VARCHAR(100) NOT NULL,
    descricao TEXT NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    estoque INT NOT NULL DEFAULT 0,
    imagem VARCHAR(255) NOT NULL,
    FOREIGN KEY (categoria_id)
        REFERENCES categorias(id)
);

CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    data_pedido DATETIME DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) NOT NULL,
    pagamento ENUM('PIX', 'Boleto', 'Cartão') NOT NULL,
    FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
);

CREATE TABLE IF NOT EXISTS itens_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id)
        REFERENCES pedidos(id),
    FOREIGN KEY (produto_id)
        REFERENCES produtos(id)
);

INSERT IGNORE INTO categorias (nome) VALUES
('Nacionais Femininos'),
('Nacionais Masculinos'),
('Internacionais Femininos'),
('Internacionais Masculinos'),
('Perfumes Árabes');

-- NACIONAIS FEMININOS

INSERT INTO produtos
(categoria_id, nome, marca, descricao, preco, estoque, imagem)
VALUES

(1, 'Lily', 'O Boticário',
'Eau de Parfum 75 ml. Fragrância floral que combina lírio, jasmim e um fundo amadeirado com almíscar.',
329.90, 10, 'lily.jpg'),

(1, 'Floratta Red', 'O Boticário',
'Desodorante Colônia 75 ml. Floral frutal inspirado na maçã vermelha, com flores e nuances adocicadas.',
154.90, 15, 'floratta.jpg'),

(1, 'Egeo Dolce', 'O Boticário',
'Desodorante Colônia 90 ml. Fragrância gourmand com algodão-doce, marshmallow, framboesa e baunilha.',
113.90, 12, 'egeo.jpg'),

-- Preço estimado
(1, 'Luna', 'Natura',
'Desodorante Colônia 75 ml. Fragrância chipre que combina frutas vermelhas, flores e notas amadeiradas.',
185.90, 10, 'luna.jpg'),

(1, 'Ilía', 'Natura',
'Deo Parfum 50 ml. Fragrância floral com frutas, flores brancas e um fundo adocicado.',
139.90, 8, 'ilia.jpg'),

-- Preço estimado
(1, 'Meu Primeiro Humor', 'Natura',
'Desodorante Colônia 75 ml. Fragrância frutal com pera, cassis e nuances florais.',
113.00, 15, 'humor.jpg'),

-- Preço estimado
(1, 'Eudora Rouge', 'Eudora',
'Eau de Parfum 75 ml. Fragrância chipre frutal com notas de ameixa, flores e madeiras.',
179.90, 10, 'rouge.jpg');


-- NACIONAIS MASCULINOS

INSERT INTO produtos
(categoria_id, nome, marca, descricao, preco, estoque, imagem)
VALUES

(2, 'Malbec', 'O Boticário',
'Desodorante Colônia 100 ml. Fragrância amadeirada inspirada no universo dos vinhos, com notas frutadas e madeiras.',
153.70, 15, 'malbec.jpg'),

-- Preço estimado
(2, 'Kaiak', 'Natura',
'Desodorante Colônia 100 ml. Fragrância aromática aquática com notas cítricas, verdes e refrescantes.',
189.90, 12, 'kaiak.jpg'),

-- Preço estimado
(2, 'Zaad', 'O Boticário',
'Eau de Parfum 95 ml. Fragrância amadeirada aromática que combina notas verdes, especiarias e madeiras.',
329.90, 8, 'zaad.jpg'),

-- Preço estimado
(2, 'Essencial', 'Natura',
'Deo Parfum 100 ml. Fragrância amadeirada intensa, com especiarias e notas aromáticas.',
259.90, 10, 'essencial.jpg'),

(2, 'Quasar', 'O Boticário',
'Desodorante Colônia 100 ml. Fragrância fougère fresca com bergamota, limão e fundo amadeirado.',
130.00, 12, 'quasar.jpg');


-- INTERNACIONAIS FEMININOS
-- Alguns preços correspondem ao menor frasco anunciado.

INSERT INTO produtos
(categoria_id, nome, marca, descricao, preco, estoque, imagem)
VALUES

(3, 'Good Girl', 'Carolina Herrera',
'Eau de Parfum. Fragrância floral ambarada que combina jasmim, tuberosa, cacau e fava tonka.',
388.00, 8, 'goodgirl.jpg'),

(3, 'La Vie Est Belle', 'Lancôme',
'Eau de Parfum. Fragrância floral gourmand com íris, patchouli, baunilha e pralinê.',
414.90, 10, 'lavie.jpg'),

(3, 'Black Opium', 'Yves Saint Laurent',
'Eau de Parfum 90 ml. Fragrância gourmand marcada pelo café, pelas flores brancas e pela baunilha.',
1043.42, 7, 'blackopium.jpg'),

(3, '212 VIP Rosé', 'Carolina Herrera',
'Eau de Parfum. Fragrância floral frutal com acorde de champagne rosé e flor de pessegueiro.',
396.80, 10, '212rose.jpg'),

(3, 'J''adore', 'Dior',
'Eau de Parfum 100 ml. Composição floral com ylang-ylang, rosa e jasmim.',
686.40, 6, 'jadore.jpg'),

(3, 'Libre', 'Yves Saint Laurent',
'Eau de Parfum. Fragrância floral com lavanda, flor de laranjeira e baunilha.',
453.00, 8, 'libre.jpg'),

(3, 'Paradoxe', 'Prada',
'Eau de Parfum. Fragrância floral ambarada com néroli, âmbar e almíscar.',
548.26, 7, 'paradoxe.jpg');


-- INTERNACIONAIS MASCULINOS

INSERT INTO produtos
(categoria_id, nome, marca, descricao, preco, estoque, imagem)
VALUES

(4, 'Sauvage', 'Dior',
'Eau de Toilette. Fragrância aromática com bergamota da Calábria, pimenta e ambroxan.',
686.00, 10, 'sauvage.jpg'),

-- Preço estimado
(4, 'Bleu de Chanel', 'Chanel',
'Eau de Toilette 100 ml. Fragrância amadeirada aromática com notas cítricas, cedro e incenso.',
950.00, 6, 'bleu.jpg'),

(4, '1 Million', 'Rabanne',
'Eau de Toilette. Fragrância amadeirada especiada com mandarina, canela, âmbar e couro.',
332.94, 10, '1million.jpg'),

(4, 'Invictus', 'Rabanne',
'Eau de Toilette. Fragrância que combina notas marinhas, grapefruit, louro e madeiras.',
648.00, 12, 'invictus.jpg'),

(4, 'Acqua di Giò', 'Giorgio Armani',
'Eau de Toilette. Fragrância aromática aquática com notas marinhas, bergamota e fundo amadeirado.',
519.00, 8, 'acqua.jpg'),

-- Preço estimado
(4, 'Eros', 'Versace',
'Eau de Toilette 100 ml. Fragrância aromática com menta, maçã verde, limão, fava tonka e baunilha.',
599.90, 10, 'eros.jpg'),

(4, '212 Men NYC', 'Carolina Herrera',
'Eau de Toilette. Fragrância amadeirada floral com notas verdes, especiarias e almíscar.',
431.00, 9, '212men.jpg');


-- PERFUMES ÁRABES

INSERT INTO produtos
(categoria_id, nome, marca, descricao, preco, estoque, imagem)
VALUES

(5, '9 PM', 'Afnan',
'Eau de Parfum 100 ml. Fragrância ambarada com maçã, canela, lavanda, baunilha e fava tonka.',
299.00, 12, '9pm.jpg'),

(5, 'Asad', 'Lattafa',
'Eau de Parfum 100 ml. Fragrância ambarada especiada com pimenta-preta, tabaco, café e baunilha.',
299.00, 15, 'asad.jpg'),

-- Preço estimado
(5, 'Khamrah', 'Lattafa',
'Eau de Parfum 100 ml. Fragrância gourmand com canela, noz-moscada, tâmaras, pralinê e baunilha.',
429.90, 10, 'khamrah.jpg'),

-- Preço estimado
(5, 'Yara', 'Lattafa',
'Eau de Parfum 100 ml. Fragrância ambarada com orquídea, heliotrópio, frutas tropicais, baunilha e almíscar.',
339.90, 15, 'yara.jpg');