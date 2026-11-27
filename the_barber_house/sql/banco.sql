-- The Barber House - banco de dados
-- Importe este arquivo no phpMyAdmin (aba Importar) ou rode no HeidiSQL.
-- ATENÇÃO: ele apaga e recria as tabelas, então os dados atuais são perdidos.

CREATE DATABASE IF NOT EXISTS the_barber_house CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE the_barber_house;

-- Desliga a conferência das chaves estrangeiras só para conseguir apagar as tabelas em qualquer ordem
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS mensagens_suporte;
DROP TABLE IF EXISTS avaliacoes;
DROP TABLE IF EXISTS agendamentos;
DROP TABLE IF EXISTS produtos;
DROP TABLE IF EXISTS servicos;
DROP TABLE IF EXISTS profissionais;
DROP TABLE IF EXISTS usuarios;
SET FOREIGN_KEY_CHECKS = 1;

-- usuarios: guarda clientes, funcionários e administradores (o campo tipo diz quem é quem)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    tipo ENUM('cliente', 'funcionario', 'admin') NOT NULL DEFAULT 'cliente',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- profissionais: os barbeiros que aparecem no site e podem ser escolhidos no agendamento
CREATE TABLE profissionais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    especialidade VARCHAR(150) NOT NULL,
    bio TEXT,
    imagem VARCHAR(255) NULL
) ENGINE=InnoDB;

-- servicos: cortes, barba e combos, com preço e categoria
CREATE TABLE servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    categoria ENUM('Adulto', 'Infantil', 'Barba e Cuidados') NOT NULL,
    imagem VARCHAR(255) NULL
) ENGINE=InnoDB;

-- produtos: catálogo da vitrine, com quantidade em estoque (não existe venda online)
CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    preco_promocional DECIMAL(10,2) NULL DEFAULT NULL,
    estoque INT NOT NULL DEFAULT 0,
    imagem VARCHAR(255) NULL
) ENGINE=InnoDB;

-- agendamentos: pode ligar um usuário cadastrado ou guardar nome/telefone de visitante; serviço e profissional
-- ON DELETE CASCADE  = apagou o usuário, apaga os agendamentos dele
-- ON DELETE RESTRICT = não deixa apagar um serviço que já tem agendamento
-- ON DELETE SET NULL = apagou o profissional, o agendamento fica sem profissional
CREATE TABLE agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    cliente_nome VARCHAR(120) NULL,
    cliente_telefone VARCHAR(20) NULL,
    servico_id INT NOT NULL,
    profissional_id INT NULL,
    data_agendamento DATE NOT NULL,
    horario TIME NOT NULL,
    observacoes_corte TEXT NULL,
    status ENUM('pendente', 'confirmado', 'concluido', 'cancelado') NOT NULL DEFAULT 'pendente',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_agendamento_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_agendamento_servico FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE RESTRICT,
    CONSTRAINT fk_agendamento_profissional FOREIGN KEY (profissional_id) REFERENCES profissionais(id) ON DELETE SET NULL,
    INDEX idx_agenda (data_agendamento, horario)
) ENGINE=InnoDB;

-- avaliacoes: nota e comentário do cliente sobre um agendamento concluído
-- agendamento_id é UNIQUE: cada agendamento só pode ter uma avaliação
CREATE TABLE avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agendamento_id INT NOT NULL UNIQUE,
    usuario_id INT NOT NULL,
    nota INT NOT NULL,
    comentario TEXT,
    aprovado TINYINT NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_nota CHECK (nota BETWEEN 1 AND 5),
    CONSTRAINT fk_avaliacao_agendamento FOREIGN KEY (agendamento_id) REFERENCES agendamentos(id) ON DELETE CASCADE,
    CONSTRAINT fk_avaliacao_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- mensagens_suporte: conversa entre o cliente e a equipe (remetente diz quem escreveu)
CREATE TABLE mensagens_suporte (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    remetente ENUM('cliente', 'equipe') NOT NULL,
    mensagem TEXT NOT NULL,
    lida TINYINT NOT NULL DEFAULT 0,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_mensagem_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- Dados iniciais ----------
-- Senhas de teste: admin123 (admin), func123 (funcionário), cliente123 (clientes)

INSERT INTO usuarios (nome, email, senha_hash, tipo) VALUES
('Administrador', 'admin@thebarberhouse.com', '$2y$10$HOr2GuYyRXV82.4ALHFQse7lVrPPksJoTP8zpWPutD9m9gz0xv5tG', 'admin'),
('Carla Menezes', 'funcionario@thebarberhouse.com', '$2y$10$elgGRQXAiKHwd4oHajdKgeXSmQBPJB07J4fUmqjHAzsb5j1OW6eZ.', 'funcionario'),
('Marcos Oliveira', 'marcos@email.com', '$2y$10$SBeZyioY6/t5NDbj0lWx.edIpYWjY39qA4Fl02Wo5LkxJRiDOaaYa', 'cliente'),
('Felipe Santos', 'felipe@email.com', '$2y$10$SBeZyioY6/t5NDbj0lWx.edIpYWjY39qA4Fl02Wo5LkxJRiDOaaYa', 'cliente');

INSERT INTO profissionais (nome, especialidade, bio, imagem) VALUES
('Bruno Alves', 'Cortes clássicos e barba', 'Bruno é um dos fundadores da casa e tem mais de 12 anos de experiência. Gosta de corte social bem feito e de barba alinhada, sempre com calma e conversa boa.', 'bruno.jpg'),
('Rafael Mendes', 'Degradê e navalha', 'Rafael é a referência da casa em degradê e acabamento com navalha. Antes de começar, pergunta o tamanho de cada lado para o resultado sair como você imaginou.', 'rafael.jpg'),
('Thiago Ramos', 'Corte infantil', 'Thiago é o nosso especialista em corte infantil. Explica cada passo para a criança, deixa ela escolher o estilo e para a máquina quando ela pedir.', 'thiago.jpg');

INSERT INTO servicos (nome, descricao, preco, categoria, imagem) VALUES
('Corte clássico', 'Corte social na tesoura e máquina, com acabamento no pescoço e nas laterais.', 45.00, 'Adulto', 'corte-classico.jpg'),
('Degradê', 'Degradê com acabamento na navalha. Leve uma foto de referência se quiser.', 50.00, 'Adulto', 'degrade.jpg'),
('Corte kids', 'Corte para crianças até 10 anos, com paciência e no ritmo da criança.', 35.00, 'Infantil', 'corte-kids.jpg'),
('Degradê kids', 'Degradê para crianças, com máquina silenciosa para quem se assusta com o barulho.', 40.00, 'Infantil', 'degrade-kids.jpg'),
('Barba modelada', 'Barba alinhada e modelada na navalha, com toalha quente.', 35.00, 'Barba e Cuidados', 'barba-modelada.jpg'),
('Corte e barba', 'Corte clássico ou degradê junto com a barba modelada, no mesmo horário.', 75.00, 'Barba e Cuidados', 'corte-e-barba.jpg');

INSERT INTO produtos (nome, descricao, preco, preco_promocional, estoque, imagem) VALUES
('Creme para pentear', 'Define e disciplina os fios sem pesar.', 26.00, 23.90, 12, 'creme-pentear.jpg'),
('Gel modelador', 'Fixação forte com brilho natural.', 28.00, 24.90, 8, 'gel-modelador.jpg'),
('Óleo para barba', 'Hidrata e amacia os fios da barba.', 32.00, 28.90, 4, 'oleo-barba.jpg'),
('Pomada premium', 'Modelagem com efeito fosco de longa duração.', 34.00, 29.90, 10, 'pomada.jpg');

-- Agendamentos de exemplo (os 3 primeiros já foram concluídos, para testar as avaliações)
INSERT INTO agendamentos (usuario_id, servico_id, profissional_id, data_agendamento, horario, observacoes_corte, status) VALUES
(3, 3, 3, '2026-10-06', '10:00:00', 'Filho de 6 anos. Máquina 2 nas laterais e tesoura em cima. Ele tem medo do barulho da máquina.', 'concluido'),
(4, 2, 2, '2026-10-10', '15:30:00', 'Degradê baixo, deixar mais comprido em cima.', 'concluido'),
(4, 5, 1, '2026-10-16', '17:00:00', NULL, 'concluido'),
(3, 1, 1, '2026-11-25', '14:00:00', NULL, 'pendente');

-- Depoimentos de exemplo para testar a página inicial. Troque pelos de clientes de verdade.
INSERT INTO avaliacoes (agendamento_id, usuario_id, nota, comentario, aprovado) VALUES
(1, 3, 5, 'Levei meu filho de 6 anos, que morre de medo de máquina, e o Thiago teve uma paciência enorme. Explicou tudo antes, deixou ele escolher o corte e agora ele pede para voltar.', 1),
(2, 4, 5, 'O degradê ficou do jeito que eu mostrei na foto. O Rafael perguntou o tamanho de cada lado antes de começar, coisa que nunca tinha visto em outra barbearia.', 1),
(3, 4, 5, 'Barba bem alinhada, toalha quente e sem pressa. Marquei pelo site e o horário estava me esperando, sem ficar trocando mensagem.', 1);