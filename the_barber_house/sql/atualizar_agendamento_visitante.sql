-- The Barber House: permite agendamentos sem conta.
-- Execute uma única vez no banco the_barber_house pelo phpMyAdmin.
-- Não apaga nem recria tabelas ou registros existentes.
USE the_barber_house;

ALTER TABLE agendamentos
    MODIFY usuario_id INT NULL,
    ADD COLUMN cliente_nome VARCHAR(120) NULL AFTER usuario_id,
    ADD COLUMN cliente_telefone VARCHAR(20) NULL AFTER cliente_nome;

-- Índice para evitar agendamentos ativos repetidos pelo mesmo telefone no mesmo horário.
CREATE INDEX idx_agenda_visitante ON agendamentos (cliente_telefone, data_agendamento, horario, status);
