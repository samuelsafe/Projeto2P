USE login_sistema;

-- Tabela de livros
CREATE TABLE IF NOT EXISTS livros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    imagem VARCHAR(255) NOT NULL,
    descricao TEXT
);

-- Tabela de favoritos (liga usuário <-> livro)
CREATE TABLE IF NOT EXISTS favoritos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    livro_id INT NOT NULL,
    data_adicionado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (livro_id) REFERENCES livros(id) ON DELETE CASCADE,
    UNIQUE KEY usuario_livro_unico (usuario_id, livro_id)
);

-- Livros de exemplo (ajuste título e nome da imagem conforme seu acervo real)
INSERT INTO livros (titulo, imagem) VALUES
('Livro 1', 'imagem-01.jpeg'),
('Livro 2', 'imagem-02.jpeg'),
('Livro 3', 'imagem-03.jpeg'),
('Livro 4', 'imagem-04.jpeg'),
('Livro 5', 'imagem-05.jpeg'),
('Livro 6', 'imagem-06.jpeg'),
('Livro 7', 'imagem-07.jpeg'),
('Livro 8', 'imagem-08.jpeg');
