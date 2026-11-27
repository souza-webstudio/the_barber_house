-- The Barber House - preços promocionais dos produtos
-- Execute UMA VEZ no phpMyAdmin, selecionando o banco the_barber_house.
-- Não apaga produtos, usuários, agendamentos nem avaliações.

ALTER TABLE produtos
    ADD COLUMN preco_promocional DECIMAL(10,2) NULL DEFAULT NULL AFTER preco;

UPDATE produtos SET preco_promocional = CASE
    WHEN nome = 'Creme para pentear' THEN 23.90
    WHEN nome = 'Gel modelador' THEN 24.90
    WHEN nome = 'Óleo para barba' THEN 28.90
    WHEN nome = 'Pomada premium' THEN 29.90
    ELSE NULL
END;
