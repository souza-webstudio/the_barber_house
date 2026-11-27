<?php
// admin/dashboard.php
// Resumo da barbearia: números e próximos atendimentos.
// Funcionário e administrador entram aqui, mas só o admin vê os cartões de gerenciamento.

$raiz = "../";
require_once "../includes/funcoes.php";
exigir_equipe($raiz);

// Números do resumo (a função contar() está no funcoes.php)
$n_servicos      = contar($pdo, "SELECT COUNT(*) FROM servicos");
$n_produtos      = contar($pdo, "SELECT COUNT(*) FROM produtos");
$n_profissionais = contar($pdo, "SELECT COUNT(*) FROM profissionais");
$n_agendamentos  = contar($pdo, "SELECT COUNT(*) FROM agendamentos");
$n_pendentes     = contar($pdo, "SELECT COUNT(*) FROM agendamentos WHERE status = 'pendente'");
$n_clientes      = contar($pdo, "SELECT COUNT(*) FROM usuarios WHERE tipo = 'cliente'");
$n_avaliacoes    = contar($pdo, "SELECT COUNT(*) FROM avaliacoes");
$n_nao_lidas     = contar($pdo, "SELECT COUNT(*) FROM mensagens_suporte WHERE remetente = 'cliente' AND lida = 0");

// Próximos 8 atendimentos (de hoje em diante, pendentes ou confirmados)
$sql = "SELECT a.id, a.data_agendamento, a.horario, a.status,
               COALESCE(NULLIF(a.cliente_nome, ''), u.nome, 'Cliente') AS cliente, s.nome AS servico, p.nome AS profissional
        FROM agendamentos a
        LEFT JOIN usuarios u ON u.id = a.usuario_id
        JOIN servicos s ON s.id = a.servico_id
        LEFT JOIN profissionais p ON p.id = a.profissional_id
        WHERE a.data_agendamento >= CURDATE() AND a.status IN ('pendente', 'confirmado')
        ORDER BY a.data_agendamento, a.horario
        LIMIT 8";
$proximos = $pdo->query($sql)->fetchAll();

$titulo_pagina = "Resumo";
$area_admin = true;                  // mostra o submenu do painel
require_once "../includes/header.php";
?>
<section class="secao">
    <div class="container">
        <h1>Resumo da barbearia</h1>
        <?php $u = usuario_logado(); ?>
        <p class="subtitulo">Olá, <?= e(primeiro_nome($u["nome"])) ?>. Aqui está o que está acontecendo hoje.</p>

        <div class="grade grade-4">
            <!-- estes dois cartões aparecem para a equipe toda -->
            <article class="cartao cartao-corpo numero">
                <p class="numero-valor"><?= $n_pendentes ?></p>
                <p>agendamentos pendentes (de <?= $n_agendamentos ?> no total)</p>
                <a class="botao botao-pequeno" href="agendamentos.php">Ver agendamentos</a>
            </article>
            <article class="cartao cartao-corpo numero">
                <p class="numero-valor"><?= $n_nao_lidas ?></p>
                <p>mensagens de clientes sem leitura</p>
                <a class="botao botao-pequeno" href="suporte.php">Abrir suporte</a>
            </article>

            <?php if (eh_admin()): ?>
                <!-- os cartões abaixo só o administrador vê -->
                <article class="cartao cartao-corpo numero">
                    <p class="numero-valor"><?= $n_servicos ?></p>
                    <p>serviços cadastrados</p>
                    <a class="botao botao-pequeno" href="servicos.php">Gerenciar</a>
                </article>
                <article class="cartao cartao-corpo numero">
                    <p class="numero-valor"><?= $n_produtos ?></p>
                    <p>produtos na vitrine</p>
                    <a class="botao botao-pequeno" href="produtos.php">Gerenciar</a>
                </article>
                <article class="cartao cartao-corpo numero">
                    <p class="numero-valor"><?= $n_profissionais ?></p>
                    <p>profissionais na equipe</p>
                    <a class="botao botao-pequeno" href="profissionais.php">Gerenciar</a>
                </article>
                <article class="cartao cartao-corpo numero">
                    <p class="numero-valor"><?= $n_avaliacoes ?></p>
                    <p>avaliações recebidas</p>
                    <a class="botao botao-pequeno" href="avaliacoes.php">Moderar</a>
                </article>
                <article class="cartao cartao-corpo numero">
                    <p class="numero-valor"><?= $n_clientes ?></p>
                    <p>clientes cadastrados</p>
                </article>
            <?php endif; ?>
        </div>

        <h2>Próximos atendimentos</h2>
        <?php if (count($proximos) == 0): ?>
            <p class="vazio">Nenhum atendimento pendente ou confirmado para os próximos dias.</p>
        <?php else: ?>
            <div class="tabela-rolagem">
                <table>
                    <thead>
                        <tr><th>Data</th><th>Hora</th><th>Cliente</th><th>Serviço</th><th>Barbeiro</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($proximos as $a): ?>
                        <tr>
                            <td><?= data_br($a["data_agendamento"]) ?></td>
                            <td><?= substr($a["horario"], 0, 5) ?></td>
                            <td><?= e($a["cliente"]) ?></td>
                            <td><?= e($a["servico"]) ?></td>
                            <td><?php if ($a["profissional"]) { echo e($a["profissional"]); } else { echo "sem preferência"; } ?></td>
                            <td><span class="selo selo-<?= e($a["status"]) ?>"><?= e(status_texto($a["status"])) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require_once "../includes/footer.php"; ?>
