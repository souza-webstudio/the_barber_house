-- The Barber House — atualização segura das fotos
-- NÃO apaga tabelas. NÃO altera preço, nome, descrição, estoque ou agendamentos.
-- Rode uma vez em bases que já existem.

USE the_barber_house;

-- Profissionais
UPDATE profissionais SET imagem = 'bruno.jpg'  WHERE nome = 'Bruno Alves'  AND (imagem IS NULL OR imagem = '' OR imagem <> 'bruno.jpg');
UPDATE profissionais SET imagem = 'rafael.jpg' WHERE nome = 'Rafael Mendes' AND (imagem IS NULL OR imagem = '' OR imagem <> 'rafael.jpg');
UPDATE profissionais SET imagem = 'thiago.jpg' WHERE nome = 'Thiago Ramos' AND (imagem IS NULL OR imagem = '' OR imagem <> 'thiago.jpg');

-- Serviços
UPDATE servicos SET imagem = 'corte-classico.jpg' WHERE nome = 'Corte clássico' AND (imagem IS NULL OR imagem = '' OR imagem <> 'corte-classico.jpg');
UPDATE servicos SET imagem = 'degrade.jpg'         WHERE nome = 'Degradê'         AND (imagem IS NULL OR imagem = '' OR imagem <> 'degrade.jpg');
UPDATE servicos SET imagem = 'corte-kids.jpg'      WHERE nome = 'Corte kids'      AND (imagem IS NULL OR imagem = '' OR imagem <> 'corte-kids.jpg');
UPDATE servicos SET imagem = 'degrade-kids.jpg'    WHERE nome = 'Degradê kids'    AND (imagem IS NULL OR imagem = '' OR imagem <> 'degrade-kids.jpg');
UPDATE servicos SET imagem = 'barba-modelada.jpg'  WHERE nome = 'Barba modelada'   AND (imagem IS NULL OR imagem = '' OR imagem <> 'barba-modelada.jpg');
UPDATE servicos SET imagem = 'corte-e-barba.jpg'   WHERE nome = 'Corte e barba'    AND (imagem IS NULL OR imagem = '' OR imagem <> 'corte-e-barba.jpg');

-- Produtos
UPDATE produtos SET imagem = 'creme-pentear.jpg' WHERE nome = 'Creme para pentear' AND (imagem IS NULL OR imagem = '' OR imagem <> 'creme-pentear.jpg');
UPDATE produtos SET imagem = 'gel-modelador.jpg' WHERE nome = 'Gel modelador'      AND (imagem IS NULL OR imagem = '' OR imagem <> 'gel-modelador.jpg');
UPDATE produtos SET imagem = 'oleo-barba.jpg'    WHERE nome = 'Óleo para barba'    AND (imagem IS NULL OR imagem = '' OR imagem <> 'oleo-barba.jpg');
UPDATE produtos SET imagem = 'pomada.jpg'        WHERE nome = 'Pomada premium'     AND (imagem IS NULL OR imagem = '' OR imagem <> 'pomada.jpg');

-- Conferência
SELECT 'profissionais' AS tabela, id, nome, imagem FROM profissionais
UNION ALL
SELECT 'servicos', id, nome, imagem FROM servicos
UNION ALL
SELECT 'produtos', id, nome, imagem FROM produtos
ORDER BY tabela, id;