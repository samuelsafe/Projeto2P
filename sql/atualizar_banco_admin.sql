USE login_sistema;

ALTER TABLE usuarios ADD COLUMN admin TINYINT(1) NOT NULL DEFAULT 0;

INSERT INTO usuarios (nome, email, senha, admin) VALUES (
    'Administrador',
    'admin',
    '$2y$10$97Mmv7mP1rgAD0ROKdUo9.ct59d5CSa7CVdiz2qvKghhVd/xEt1eu',
    1
);
