<?php
// admin/suporte.php
// A equipe vê as conversas dos clientes e responde (RF25, RF26).
// As mensagens aparecem e são enviadas pelo main.js (a cada 4 segundos ele busca as novas).

$raiz = "../";
require_once "../includes/funcoes.php";
exigir_equipe($raiz);

// Lista de clientes que já mandaram mensagem (o mais recente primeiro)
$sql = "SELECT u.id, u.nome, MAX(m.criado_em) AS ultima
        FROM mensagens_suporte m
        JOIN usuarios u ON u.id = m.usuario_id
        GROUP BY u.id, u.nome
        ORDER BY ultima DESC";
$clientes_com_mensagem = $pdo->query($sql)->fetchAll();

// Para cada cliente, conta quantas mensagens ainda não foram lidas
$conversas = array();
foreach ($clientes_com_mensagem as $c) {
    $st = $pdo->prepare("SELECT COUNT(*) FROM mensagens_suporte
                         WHERE usuario_id = ? AND remetente = 'cliente' AND lida = 0");
    $st->execute(array($c["id"]));
    $nao_lidas = $st->fetchColumn();

    $conversas[] = array(
        "id"        => $c["id"],
        "nome"      => $c["nome"],
        "nao_lidas" => $nao_lidas
    );
}

// Conversa escolhida na URL (?cliente=3)
$cliente_id = (int) pegar_get("cliente");
$cliente = null;
if ($cliente_id > 0) {
    $st = $pdo->prepare("SELECT id, nome FROM usuarios WHERE id = ? AND tipo = 'cliente'");
    $st->execute(array($cliente_id));
    $cliente = $st->fetch();
    if (!$cliente) {
        $cliente = null;
    }
}

$titulo_pagina = "Suporte";
$area_admin = true;
require_once "../includes/header.php";
?>
<section class="secao">
    <div class="container">
        <h1>Suporte aos clientes</h1>

        <div class="suporte-grade">

            <!-- Lista de conversas -->
            <aside aria-label="Conversas">
                <h2 class="titulo-pequeno">Conversas</h2>
                <?php if (count($conversas) == 0): ?>
                    <p class="vazio">Nenhum cliente escreveu ainda.</p>
                <?php endif; ?>
                <ul class="lista-conversas">
                    <?php foreach ($conversas as $c): ?>
                        <li>
                            <a class="<?php if ($cliente != null && $cliente["id"] == $c["id"]) { echo "ativo"; } ?>" href="suporte.php?cliente=<?= (int) $c["id"] ?>">
                                <?= e($c["nome"]) ?>
                                <?php if ($c["nao_lidas"] > 0): ?>
                                    <span class="selo selo-pendente"><?= (int) $c["nao_lidas"] ?> <?php if ($c["nao_lidas"] > 1) { echo "novas"; } else { echo "nova"; } ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </aside>

            <!-- Conversa escolhida -->
            <section class="cartao cartao-corpo" aria-label="Conversa selecionada">
                <?php if ($cliente == null): ?>
                    <p class="vazio">Escolha uma conversa ao lado para responder.</p>
                <?php else: ?>
                    <h2 class="titulo-pequeno">Conversa com <?= e($cliente["nome"]) ?></h2>

                    <div id="chat-suporte"
                         data-papel="equipe"
                         data-cliente="<?= (int) $cliente["id"] ?>"
                         data-url-buscar="<?= $raiz ?>chat/buscar_mensagens.php"
                         data-url-enviar="<?= $raiz ?>chat/enviar_mensagem.php">
                        <div id="chat-mensagens" class="chat-mensagens" role="log" aria-live="polite" aria-label="Conversa com <?= e($cliente["nome"]) ?>">
                            <p class="chat-vazio">Nenhuma mensagem nessa conversa.</p>
                        </div>
                        <p id="chat-erro" class="aviso-campo" role="alert"></p>
                        <form id="chat-form" class="chat-form">
                            <?= csrf_campo() ?>
                            <input type="hidden" name="cliente_id" value="<?= (int) $cliente["id"] ?>">
                            <label for="chat-texto" class="so-leitor">Sua resposta</label>
                            <input type="text" id="chat-texto" name="mensagem" maxlength="1000" placeholder="Escreva a resposta" autocomplete="off" required>
                            <button type="submit" class="botao">Responder</button>
                        </form>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</section>
<?php require_once "../includes/footer.php"; ?>
