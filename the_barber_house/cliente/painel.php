<?php
// cliente/painel.php
// Painel do cliente: marcar horário (RF09 a RF13, RF34), ver seus agendamentos (RF14)
// e conversar com a equipe pelo chat (RF25).

$raiz = "../";
require_once "../includes/funcoes.php";
exigir_cliente($raiz);               // só cliente logado entra aqui
$usuario = usuario_logado();

$erro = "";

// Valores do formulário. Ficam guardados para a pessoa não perder o que digitou se der erro.
$servico_id      = pegar_get("servico");     // vem do botão "Agendar" da página inicial
$profissional_id = "";
$data            = "";
$horario         = "";
$observacoes     = "";

// ---------- Quando o formulário de agendamento é enviado ----------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    exigir_csrf();

    $servico_id      = pegar_post("servico_id");
    $profissional_id = pegar_post("profissional_id");
    $data            = pegar_post("data");
    $horario         = pegar_post("horario");
    $observacoes     = trim(pegar_post("observacoes"));

    // 1) O serviço escolhido existe no banco? (RN04)
    $st = $pdo->prepare("SELECT id FROM servicos WHERE id = ?");
    $st->execute(array((int) $servico_id));
    $servico_existe = $st->fetch();

    // 2) O barbeiro é opcional (RN06). Se foi escolhido, ele existe?
    $profissional_existe = true;
    $profissional_num = null;               // null = sem preferência
    if ($profissional_id != "") {
        $profissional_num = (int) $profissional_id;
        $st = $pdo->prepare("SELECT id FROM profissionais WHERE id = ?");
        $st->execute(array($profissional_num));
        $profissional_existe = $st->fetch();
    }

    // 3) A data e o horário são válidos? (dia de funcionamento, horário, data passada)
    $erro_data = validar_data_horario($data, $horario);

    // Vai conferindo um erro de cada vez
    if (!$servico_existe) {
        $erro = "Escolha um serviço da lista.";
    } elseif (!$profissional_existe) {
        $erro = "O barbeiro escolhido não existe. Escolha outro ou deixe sem preferência.";
    } elseif ($erro_data != "") {
        $erro = $erro_data;
    } elseif (mb_strlen($observacoes) > 500) {
        $erro = "As observações podem ter no máximo 500 caracteres.";
    } elseif (horario_ocupado($pdo, $data, $horario, $profissional_num)) {
        // conflito de horário (RN07 e RN20)
        if ($profissional_num) {
            $erro = "Esse barbeiro já tem alguém nesse horário. Escolha outro horário ou outro barbeiro.";
        } else {
            $erro = "Todos os barbeiros estão ocupados nesse horário. Escolha outro horário.";
        }
    } else {
        // 4) O mesmo cliente não pode ter dois agendamentos no mesmo horário (RN21)
        $st = $pdo->prepare("SELECT COUNT(*) FROM agendamentos
                             WHERE usuario_id = ? AND data_agendamento = ? AND horario = ? AND status <> 'cancelado'");
        $st->execute(array($usuario["id"], $data, $horario . ":00"));
        $ja_tem = $st->fetchColumn();

        if ($ja_tem > 0) {
            $erro = "Você já tem um agendamento nesse mesmo dia e horário.";
        } else {
            // Tudo certo: salva o agendamento com status "pendente" (RN09)
            // Se as observações estiverem vazias, guarda NULL no banco
            $obs_salvar = null;
            if ($observacoes != "") {
                $obs_salvar = $observacoes;
            }

            $sql = "INSERT INTO agendamentos (usuario_id, servico_id, profissional_id, data_agendamento, horario, observacoes_corte, status)
                    VALUES (?, ?, ?, ?, ?, ?, 'pendente')";
            $st = $pdo->prepare($sql);
            $st->execute(array($usuario["id"], (int) $servico_id, $profissional_num, $data, $horario . ":00", $obs_salvar));

            mensagem("sucesso", "Agendamento enviado! Ele aparece como pendente até a equipe confirmar.");
            header("Location: painel.php#meus-agendamentos");
            exit;
        }
    }
}

// ---------- Dados para mostrar na tela ----------

$categorias = array("Adulto", "Infantil", "Barba e Cuidados");

// Barbeiros para a lista de escolha
$profissionais = $pdo->query("SELECT id, nome, especialidade FROM profissionais ORDER BY nome")->fetchAll();

// Agendamentos deste cliente (junta com serviço e profissional para mostrar os nomes)
$sql = "SELECT a.*, s.nome AS servico, s.preco, p.nome AS profissional, v.id AS avaliacao_id
        FROM agendamentos a
        JOIN servicos s ON s.id = a.servico_id
        LEFT JOIN profissionais p ON p.id = a.profissional_id
        LEFT JOIN avaliacoes v ON v.agendamento_id = a.id
        WHERE a.usuario_id = ?
        ORDER BY a.data_agendamento DESC, a.horario DESC";
$st = $pdo->prepare($sql);
$st->execute(array($usuario["id"]));
$agendamentos = $st->fetchAll();

$titulo_pagina = "Meu painel";
require_once "../includes/header.php";
?>
<section class="secao">
    <div class="container">
        <h1>Olá, <?= e(primeiro_nome($usuario["nome"])) ?>!</h1>
        <p class="subtitulo">Aqui você marca um horário, acompanha seus agendamentos e fala com a equipe.</p>

        <div class="duas-colunas">

            <!-- Formulário de agendamento -->
            <section id="agendar" class="cartao cartao-corpo" aria-labelledby="titulo-agendar">
                <h2 id="titulo-agendar">Marcar horário</h2>

                <?php if ($erro != ""): ?>
                    <div class="aviso aviso-erro" role="alert"><?= e($erro) ?></div>
                <?php endif; ?>

                <form method="post" class="formulario" action="painel.php#agendar">
                    <?= csrf_campo() ?>

                    <label for="servico_id">Serviço</label>
                    <select id="servico_id" name="servico_id" required>
                        <option value="">Escolha um serviço</option>
                        <!-- os serviços ficam agrupados por categoria -->
                        <?php foreach ($categorias as $categoria): ?>
                            <optgroup label="<?= e($categoria) ?>">
                                <?php foreach (servicos_da_categoria($pdo, $categoria) as $s): ?>
                                    <option value="<?= (int) $s["id"] ?>" <?php if ($servico_id == $s["id"]) { echo "selected"; } ?>>
                                        <?= e($s["nome"]) ?> - <?= preco($s["preco"]) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>

                    <label for="profissional_id">Barbeiro (opcional)</label>
                    <select id="profissional_id" name="profissional_id">
                        <option value="">Sem preferência</option>
                        <?php foreach ($profissionais as $p): ?>
                            <option value="<?= (int) $p["id"] ?>" <?php if ($profissional_id == $p["id"]) { echo "selected"; } ?>>
                                <?= e($p["nome"]) ?> - <?= e($p["especialidade"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="data">Data</label>
                    <!-- min impede escolher datas que já passaram -->
                    <input type="date" id="data" name="data" min="<?= date("Y-m-d") ?>" value="<?= e($data) ?>" required>
                    <!-- o main.js escreve aqui um aviso se a data cair no domingo ou segunda -->
                    <p id="aviso-data" class="aviso-campo" aria-live="polite"></p>

                    <label for="horario">Horário</label>
                    <select id="horario" name="horario" required>
                        <option value="">Escolha um horário</option>
                        <?php foreach (horarios_atendimento() as $h): ?>
                            <option value="<?= $h ?>" <?php if ($horario == $h) { echo "selected"; } ?>><?= $h ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="ajuda">Atendemos de terça a sábado, das 9h às 19h30, de 30 em 30 minutos.</p>

                    <label for="observacoes">Observações do corte</label>
                    <textarea id="observacoes" name="observacoes" rows="4" maxlength="500" placeholder="Ex.: corte infantil, passar máquina 2 nas laterais. Ele tem medo do barulho da máquina."><?= e($observacoes) ?></textarea>
                    <p class="ajuda">Se o corte é de uma criança, conte aqui como ela gosta e se tem algum receio.</p>

                    <button type="submit" class="botao">Enviar agendamento</button>
                </form>
            </section>

            <!-- Chat com a equipe (o main.js busca e envia as mensagens) -->
            <section id="suporte" class="cartao cartao-corpo" aria-labelledby="titulo-suporte">
                <h2 id="titulo-suporte">Chat com a equipe</h2>
                <p class="ajuda">Tire dúvidas sobre horário, corte ou agendamento. A equipe responde por aqui.</p>

                <div id="chat-suporte"
                     data-papel="cliente"
                     data-url-buscar="<?= $raiz ?>chat/buscar_mensagens.php"
                     data-url-enviar="<?= $raiz ?>chat/enviar_mensagem.php">
                    <div id="chat-mensagens" class="chat-mensagens" role="log" aria-live="polite" aria-label="Conversa com a equipe">
                        <p class="chat-vazio">Nenhuma mensagem ainda. Escreva a primeira!</p>
                    </div>
                    <p id="chat-erro" class="aviso-campo" role="alert"></p>
                    <form id="chat-form" class="chat-form">
                        <?= csrf_campo() ?>
                        <label for="chat-texto" class="so-leitor">Sua mensagem</label>
                        <input type="text" id="chat-texto" name="mensagem" maxlength="1000" placeholder="Escreva sua mensagem" autocomplete="off" required>
                        <button type="submit" class="botao">Enviar</button>
                    </form>
                </div>
            </section>
        </div>

        <!-- Lista de agendamentos do cliente -->
        <section id="meus-agendamentos" aria-labelledby="titulo-meus">
            <h2 id="titulo-meus">Meus agendamentos</h2>

            <?php if (count($agendamentos) == 0): ?>
                <p class="vazio">Você ainda não tem nenhum agendamento. Use o formulário acima para marcar o primeiro.</p>
            <?php endif; ?>

            <div class="lista-agendamentos">
                <?php foreach ($agendamentos as $a): ?>
                    <article class="cartao cartao-corpo agendamento">
                        <div class="agendamento-topo">
                            <h3><?= e($a["servico"]) ?></h3>
                            <span class="selo selo-<?= e($a["status"]) ?>"><?= e(status_texto($a["status"])) ?></span>
                        </div>
                        <p><strong><?= data_br($a["data_agendamento"]) ?></strong> (<?= e(dia_semana($a["data_agendamento"])) ?>) às <strong><?= substr($a["horario"], 0, 5) ?></strong></p>
                        <p>Barbeiro: <?php if ($a["profissional"]) { echo e($a["profissional"]); } else { echo "sem preferência"; } ?> | Valor: <?= preco($a["preco"]) ?></p>

                        <?php if ($a["observacoes_corte"]): ?>
                            <p class="observacao">Observações: <?= e($a["observacoes_corte"]) ?></p>
                        <?php endif; ?>

                        <div class="agendamento-acoes">
                            <?php if ($a["status"] == "pendente" || $a["status"] == "confirmado"): ?>
                                <!-- só dá para cancelar se estiver pendente ou confirmado (RN16) -->
                                <form method="post" action="cancelar_agendamento.php" data-confirmar="Cancelar esse agendamento?">
                                    <?= csrf_campo() ?>
                                    <input type="hidden" name="id" value="<?= (int) $a["id"] ?>">
                                    <button type="submit" class="botao botao-contorno botao-pequeno">Cancelar agendamento</button>
                                </form>
                            <?php elseif ($a["status"] == "concluido" && !$a["avaliacao_id"]): ?>
                                <!-- atendimento concluído e ainda sem avaliação -->
                                <a class="botao botao-pequeno" href="avaliar.php?id=<?= (int) $a["id"] ?>">Avaliar atendimento</a>
                            <?php elseif ($a["status"] == "concluido"): ?>
                                <p class="ajuda">Atendimento avaliado. Obrigado!</p>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</section>
<?php require_once "../includes/footer.php"; ?>
