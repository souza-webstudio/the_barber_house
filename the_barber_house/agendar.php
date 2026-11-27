<?php
// Agendamento público: permite marcar um horário sem criar conta ou entrar no sistema.
$raiz = "";
require_once "includes/funcoes.php";

$erro = "";
$servico_id = pegar_get("servico");
$profissional_id = "";
$nome = "";
$telefone = "";
$data = "";
$horario = "";
$observacoes = "";
$categorias = array("Adulto", "Infantil", "Barba e Cuidados");
$profissionais = $pdo->query("SELECT id, nome, especialidade FROM profissionais ORDER BY nome")->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    exigir_csrf();
    $nome = trim(pegar_post("nome"));
    $telefone = trim(pegar_post("telefone"));
    $telefone_limpo = preg_replace('/\\D+/', '', $telefone);
    $servico_id = pegar_post("servico_id");
    $profissional_id = pegar_post("profissional_id");
    $data = pegar_post("data");
    $horario = pegar_post("horario");
    $observacoes = trim(pegar_post("observacoes"));

    $st = $pdo->prepare("SELECT id FROM servicos WHERE id = ?");
    $st->execute(array((int) $servico_id));
    $servico_existe = $st->fetch();

    $profissional_num = null;
    $profissional_existe = true;
    if ($profissional_id !== "") {
        $profissional_num = (int) $profissional_id;
        $st = $pdo->prepare("SELECT id FROM profissionais WHERE id = ?");
        $st->execute(array($profissional_num));
        $profissional_existe = $st->fetch();
    }

    $erro_data = validar_data_horario($data, $horario);

    if ($nome === "" || mb_strlen($nome) > 120) {
        $erro = "Informe seu nome (até 120 caracteres).";
    } elseif (strlen($telefone_limpo) < 10 || strlen($telefone_limpo) > 13) {
        $erro = "Informe um telefone/WhatsApp válido com DDD.";
    } elseif (!$servico_existe) {
        $erro = "Escolha um serviço da lista.";
    } elseif (!$profissional_existe) {
        $erro = "O profissional escolhido não existe. Escolha outro ou deixe sem preferência.";
    } elseif ($erro_data !== "") {
        $erro = $erro_data;
    } elseif (mb_strlen($observacoes) > 500) {
        $erro = "As observações podem ter no máximo 500 caracteres.";
    } elseif (horario_ocupado($pdo, $data, $horario, $profissional_num)) {
        $erro = $profissional_num
            ? "Esse profissional já tem alguém nesse horário. Escolha outro horário ou outro profissional."
            : "Todos os profissionais estão ocupados nesse horário. Escolha outro horário.";
    } else {
        // Evita que o mesmo telefone faça dois agendamentos ativos no mesmo horário.
        $st = $pdo->prepare("SELECT COUNT(*) FROM agendamentos
                             WHERE cliente_telefone = ? AND data_agendamento = ? AND horario = ?
                             AND status <> 'cancelado'");
        $st->execute(array($telefone_limpo, $data, $horario . ":00"));
        $ja_tem = (int) $st->fetchColumn();

        if ($ja_tem > 0) {
            $erro = "Este telefone já possui um agendamento nesse dia e horário.";
        } else {
            $obs_salvar = ($observacoes === "") ? null : $observacoes;
            $sql = "INSERT INTO agendamentos
                    (usuario_id, cliente_nome, cliente_telefone, servico_id, profissional_id,
                     data_agendamento, horario, observacoes_corte, status)
                    VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, 'pendente')";
            $st = $pdo->prepare($sql);
            $st->execute(array($nome, $telefone_limpo, (int) $servico_id, $profissional_num,
                               $data, $horario . ":00", $obs_salvar));
            $novo_id = (int) $pdo->lastInsertId();

            $st = $pdo->prepare("SELECT s.nome AS servico, p.nome AS profissional
                                 FROM agendamentos a
                                 JOIN servicos s ON s.id = a.servico_id
                                 LEFT JOIN profissionais p ON p.id = a.profissional_id
                                 WHERE a.id = ?");
            $st->execute(array($novo_id));
            $detalhes = $st->fetch();
            $_SESSION["agendamento_visitante_confirmado"] = array(
                "id" => $novo_id,
                "nome" => $nome,
                "servico" => $detalhes["servico"],
                "profissional" => $detalhes["profissional"],
                "data" => $data,
                "horario" => $horario
            );
            header("Location: agendar.php?confirmado=1");
            exit;
        }
    }
}

$confirmacao = null;
if (pegar_get("confirmado") === "1" && isset($_SESSION["agendamento_visitante_confirmado"])) {
    $confirmacao = $_SESSION["agendamento_visitante_confirmado"];
    unset($_SESSION["agendamento_visitante_confirmado"]);
}

$titulo_pagina = "Agendar sem cadastro";
require_once "includes/header.php";
?>
<section class="secao">
    <div class="container container-estreito">
        <h1>Agende seu horário sem cadastro</h1>
        <p class="subtitulo">Escolha o serviço, informe seus dados e marque seu atendimento. Não é necessário criar uma conta ou senha.</p>

        <?php if ($confirmacao): ?>
            <div class="aviso aviso-sucesso" role="status">
                <h2>Agendamento enviado com sucesso!</h2>
                <p>Obrigado, <?= e($confirmacao["nome"]) ?>. Seu pedido está <strong>pendente de confirmação pela equipe</strong>.</p>
                <p><strong>Protocolo:</strong> #<?= (int) $confirmacao["id"] ?><br>
                   <strong>Serviço:</strong> <?= e($confirmacao["servico"]) ?><br>
                   <strong>Data:</strong> <?= data_br($confirmacao["data"]) ?> às <?= e($confirmacao["horario"]) ?><br>
                   <strong>Profissional:</strong> <?= $confirmacao["profissional"] ? e($confirmacao["profissional"]) : "Sem preferência" ?></p>
                <p>A equipe poderá usar o telefone informado para entrar em contato e confirmar o horário.</p>
            </div>
        <?php endif; ?>

        <?php if ($erro !== ""): ?>
            <div class="aviso aviso-erro" role="alert"><?= e($erro) ?></div>
        <?php endif; ?>

        <section class="cartao cartao-corpo" aria-labelledby="titulo-form-visitante">
            <h2 id="titulo-form-visitante">Seus dados e horário</h2>
            <form method="post" action="agendar.php#formulario-visitante" class="formulario" id="formulario-visitante">
                <?= csrf_campo() ?>
                <label for="nome">Nome completo</label>
                <input type="text" id="nome" name="nome" maxlength="120" autocomplete="name" value="<?= e($nome) ?>" required>

                <label for="telefone">Telefone / WhatsApp com DDD</label>
                <input type="tel" id="telefone" name="telefone" maxlength="20" placeholder="(21) 99999-9999" autocomplete="tel" value="<?= e($telefone) ?>" required>

                <label for="servico_id">Serviço</label>
                <select id="servico_id" name="servico_id" required>
                    <option value="">Escolha um serviço</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <optgroup label="<?= e($categoria) ?>">
                            <?php foreach (servicos_da_categoria($pdo, $categoria) as $s): ?>
                                <option value="<?= (int) $s["id"] ?>" <?php if ((string) $servico_id === (string) $s["id"]) { echo "selected"; } ?>>
                                    <?= e($s["nome"]) ?> - <?= preco($s["preco"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>

                <label for="profissional_id">Profissional (opcional)</label>
                <select id="profissional_id" name="profissional_id">
                    <option value="">Sem preferência</option>
                    <?php foreach ($profissionais as $p): ?>
                        <option value="<?= (int) $p["id"] ?>" <?php if ((string) $profissional_id === (string) $p["id"]) { echo "selected"; } ?>>
                            <?= e($p["nome"]) ?> - <?= e($p["especialidade"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="data">Data</label>
                <input type="date" id="data" name="data" min="<?= date("Y-m-d") ?>" value="<?= e($data) ?>" required>
                <p id="aviso-data" class="aviso-campo" aria-live="polite"></p>

                <label for="horario">Horário</label>
                <select id="horario" name="horario" required>
                    <option value="">Escolha um horário</option>
                    <?php foreach (horarios_atendimento() as $h): ?>
                        <option value="<?= e($h) ?>" <?php if ($horario === $h) { echo "selected"; } ?>><?= e($h) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="ajuda">Atendemos de terça a sábado, das 9h às 19h30, de 30 em 30 minutos.</p>

                <label for="observacoes">Observações do corte (opcional)</label>
                <textarea id="observacoes" name="observacoes" rows="4" maxlength="500" placeholder="Conte como deseja o corte ou alguma necessidade especial."><?= e($observacoes) ?></textarea>

                <button type="submit" class="botao">Enviar agendamento</button>
            </form>
        </section>
        <p class="ajuda">Já tem cadastro? <a href="auth/login.php">Entre na sua conta</a> para acompanhar seus horários pelo painel.</p>
        <p class="ajuda">Ao enviar, seus dados serão usados pela equipe somente para organizar e confirmar o atendimento.</p>
    </div>
</section>
<?php require_once "includes/footer.php"; ?>
