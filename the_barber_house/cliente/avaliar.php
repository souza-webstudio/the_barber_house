<?php
// cliente/avaliar.php
// O cliente avalia um atendimento concluído com nota e comentário (RF35, RN23).

$raiz = "../";
require_once "../includes/funcoes.php";
exigir_cliente($raiz);
$usuario = usuario_logado();

// O id do agendamento vem na URL (quando abre a página) ou no formulário (quando envia)
$id = (int) pegar_get("id");
if ($id == 0) {
    $id = (int) pegar_post("agendamento_id");
}

// Busca o agendamento. Só serve se for deste cliente e estiver concluído.
// O LEFT JOIN mostra se já existe avaliação para ele.
$sql = "SELECT a.id, a.data_agendamento, s.nome AS servico, v.id AS avaliacao_id
        FROM agendamentos a
        JOIN servicos s ON s.id = a.servico_id
        LEFT JOIN avaliacoes v ON v.agendamento_id = a.id
        WHERE a.id = ? AND a.usuario_id = ? AND a.status = 'concluido'";
$st = $pdo->prepare($sql);
$st->execute(array($id, $usuario["id"]));
$agendamento = $st->fetch();

if (!$agendamento) {
    mensagem("erro", "Esse atendimento não está disponível para avaliação.");
    header("Location: painel.php#meus-agendamentos");
    exit;
}
if ($agendamento["avaliacao_id"]) {
    mensagem("erro", "Você já avaliou esse atendimento.");
    header("Location: painel.php#meus-agendamentos");
    exit;
}

$erro       = "";
$nota       = 0;
$comentario = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    exigir_csrf();

    $nota       = (int) pegar_post("nota");
    $comentario = trim(pegar_post("comentario"));

    if ($nota < 1 || $nota > 5) {
        $erro = "Escolha uma nota de 1 a 5.";
    } elseif (mb_strlen($comentario) < 5 || mb_strlen($comentario) > 500) {
        $erro = "Conte como foi o atendimento em 5 a 500 caracteres.";
    } else {
        $st = $pdo->prepare("INSERT INTO avaliacoes (agendamento_id, usuario_id, nota, comentario) VALUES (?, ?, ?, ?)");
        $st->execute(array($agendamento["id"], $usuario["id"], $nota, $comentario));

        // A avaliação já entra aprovada (RN24). O administrador pode ocultar depois.
        mensagem("sucesso", "Obrigado pela avaliação! Ela já aparece na página inicial.");
        header("Location: painel.php#meus-agendamentos");
        exit;
    }
}

$titulo_pagina = "Avaliar atendimento";
require_once "../includes/header.php";
?>
<section class="secao">
    <div class="container container-estreito">
        <h1>Avaliar atendimento</h1>
        <p class="subtitulo"><?= e($agendamento["servico"]) ?> em <?= data_br($agendamento["data_agendamento"]) ?></p>

        <?php if ($erro != ""): ?>
            <div class="aviso aviso-erro" role="alert"><?= e($erro) ?></div>
        <?php endif; ?>

        <form method="post" class="formulario cartao">
            <?= csrf_campo() ?>
            <input type="hidden" name="agendamento_id" value="<?= (int) $agendamento["id"] ?>">

            <fieldset class="notas">
                <legend>Sua nota</legend>
                <!-- cria as opções de 5 estrelas até 1 estrela -->
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <label class="nota-opcao">
                        <input type="radio" name="nota" value="<?= $i ?>" <?php if ($nota == $i) { echo "checked"; } ?> required>
                        <span><?= $i ?> <?php if ($i == 1) { echo "estrela"; } else { echo "estrelas"; } ?></span>
                    </label>
                <?php endfor; ?>
            </fieldset>

            <label for="comentario">Como foi o atendimento?</label>
            <textarea id="comentario" name="comentario" rows="5" maxlength="500" required><?= e($comentario) ?></textarea>

            <button type="submit" class="botao">Enviar avaliação</button>
            <p class="ajuda"><a href="painel.php#meus-agendamentos">Voltar para meus agendamentos</a></p>
        </form>
    </div>
</section>
<?php require_once "../includes/footer.php"; ?>
