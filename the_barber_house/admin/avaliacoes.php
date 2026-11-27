<?php
// admin/avaliacoes.php
// O administrador vê as avaliações e pode ocultar ou aprovar cada uma (RF37, RN24).

$raiz = "../";
require_once "../includes/funcoes.php";
exigir_admin($raiz);

// ---------- Quando o administrador clica em "Ocultar" ou "Aprovar" ----------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    exigir_csrf();

    $id   = (int) pegar_post("id");
    $acao = pegar_post("acao");

    // aprovado = 1 aparece no site, aprovado = 0 fica oculto
    $aprovado = 0;
    if ($acao == "aprovar") {
        $aprovado = 1;
    }

    $st = $pdo->prepare("UPDATE avaliacoes SET aprovado = ? WHERE id = ?");
    $st->execute(array($aprovado, $id));

    if ($aprovado == 1) {
        mensagem("sucesso", "Depoimento aprovado. Ele aparece na página inicial.");
    } else {
        mensagem("sucesso", "Depoimento ocultado da página inicial.");
    }
    header("Location: avaliacoes.php");
    exit;
}

// Busca todas as avaliações, com o nome do cliente e do serviço
$sql = "SELECT v.*, u.nome AS cliente, s.nome AS servico
        FROM avaliacoes v
        JOIN usuarios u ON u.id = v.usuario_id
        JOIN agendamentos a ON a.id = v.agendamento_id
        JOIN servicos s ON s.id = a.servico_id
        ORDER BY v.criado_em DESC";
$avaliacoes = $pdo->query($sql)->fetchAll();

$titulo_pagina = "Avaliações";
$area_admin = true;
require_once "../includes/header.php";
?>
<section class="secao">
    <div class="container">
        <h1>Avaliações dos clientes</h1>
        <p class="subtitulo">Só os depoimentos aprovados aparecem na página inicial.</p>

        <?php if (count($avaliacoes) == 0): ?>
            <p class="vazio">Ainda não há avaliações.</p>
        <?php endif; ?>

        <div class="lista-agendamentos">
            <?php foreach ($avaliacoes as $v): ?>
                <article class="cartao cartao-corpo">
                    <div class="agendamento-topo">
                        <h2 class="titulo-pequeno"><?= e($v["cliente"]) ?> - <?= e($v["servico"]) ?></h2>
                        <?php if ($v["aprovado"]): ?>
                            <span class="selo selo-confirmado">Visível no site</span>
                        <?php else: ?>
                            <span class="selo selo-cancelado">Oculto</span>
                        <?php endif; ?>
                    </div>
                    <p class="estrelas" aria-label="Nota <?= (int) $v["nota"] ?> de 5"><?= str_repeat("★", $v["nota"]) . str_repeat("☆", 5 - $v["nota"]) ?></p>
                    <p><?= e($v["comentario"]) ?></p>

                    <form method="post">
                        <?= csrf_campo() ?>
                        <input type="hidden" name="id" value="<?= (int) $v["id"] ?>">
                        <?php if ($v["aprovado"]): ?>
                            <button type="submit" name="acao" value="ocultar" class="botao botao-pequeno botao-contorno">Ocultar do site</button>
                        <?php else: ?>
                            <button type="submit" name="acao" value="aprovar" class="botao botao-pequeno">Aprovar e mostrar no site</button>
                        <?php endif; ?>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php require_once "../includes/footer.php"; ?>
