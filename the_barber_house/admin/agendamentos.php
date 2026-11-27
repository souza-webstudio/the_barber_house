<?php
// admin/agendamentos.php
// A equipe vê todos os agendamentos, filtra por status (RF40) e muda o status (RF24).

$raiz = "../";
require_once "../includes/funcoes.php";
exigir_equipe($raiz);

$status_validos = array("pendente", "confirmado", "concluido", "cancelado");

// Filtro da URL (?status=pendente). Se vier algo estranho, ignora.
$filtro = pegar_get("status");
if (!in_array($filtro, $status_validos)) {
    $filtro = "";
}

// ---------- Quando a equipe salva um novo status ----------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    exigir_csrf();

    $id   = (int) pegar_post("id");
    $novo = pegar_post("status");

    // para onde voltar depois de salvar (mantém o filtro escolhido)
    $voltar = "agendamentos.php";
    if ($filtro != "") {
        $voltar = "agendamentos.php?status=" . $filtro;
    }

    // busca o agendamento que será alterado
    $st = $pdo->prepare("SELECT * FROM agendamentos WHERE id = ?");
    $st->execute(array($id));
    $ag = $st->fetch();

    if (!$ag || !in_array($novo, $status_validos)) {
        mensagem("erro", "Agendamento ou status inválido.");
    } elseif ($ag["status"] == "cancelado" && $novo != "cancelado"
              && horario_ocupado($pdo, $ag["data_agendamento"], $ag["horario"], $ag["profissional_id"], $ag["id"])) {
        // Reativar um cancelado pode bater com outro agendamento que ocupou o horário (RN19)
        mensagem("erro", "Não dá para reativar: esse horário já foi ocupado por outro agendamento.");
    } else {
        $st = $pdo->prepare("UPDATE agendamentos SET status = ? WHERE id = ?");
        $st->execute(array($novo, $id));
        mensagem("sucesso", "Status atualizado para " . strtolower(status_texto($novo)) . ".");
    }

    header("Location: " . $voltar);
    exit;
}

// ---------- Busca os agendamentos para mostrar na tabela ----------
$sql = "SELECT a.*, COALESCE(NULLIF(a.cliente_nome, ''), u.nome, 'Cliente') AS cliente,
               COALESCE(a.cliente_telefone, '') AS telefone, s.nome AS servico, p.nome AS profissional
        FROM agendamentos a
        LEFT JOIN usuarios u ON u.id = a.usuario_id
        JOIN servicos s ON s.id = a.servico_id
        LEFT JOIN profissionais p ON p.id = a.profissional_id";

if ($filtro != "") {
    // com filtro: só os agendamentos daquele status
    $sql = $sql . " WHERE a.status = ? ORDER BY a.data_agendamento DESC, a.horario DESC";
    $st = $pdo->prepare($sql);
    $st->execute(array($filtro));
} else {
    // sem filtro: todos
    $sql = $sql . " ORDER BY a.data_agendamento DESC, a.horario DESC";
    $st = $pdo->prepare($sql);
    $st->execute();
}
$agendamentos = $st->fetchAll();

$titulo_pagina = "Agendamentos";
$area_admin = true;
require_once "../includes/header.php";
?>
<section class="secao">
    <div class="container">
        <h1>Agendamentos</h1>

        <!-- botões de filtro por status -->
        <nav class="filtros" aria-label="Filtrar por status">
            <a class="<?php if ($filtro == "") { echo "ativo"; } ?>" href="agendamentos.php">Todos</a>
            <?php foreach ($status_validos as $s): ?>
                <a class="<?php if ($filtro == $s) { echo "ativo"; } ?>" href="agendamentos.php?status=<?= $s ?>"><?= e(status_texto($s)) ?></a>
            <?php endforeach; ?>
        </nav>

        <?php if (count($agendamentos) == 0): ?>
            <p class="vazio">Nenhum agendamento encontrado.</p>
        <?php else: ?>
            <div class="tabela-rolagem">
                <table>
                    <thead>
                        <tr><th>Data</th><th>Hora</th><th>Cliente / contato</th><th>Serviço</th><th>Barbeiro</th><th>Observações do corte</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($agendamentos as $a): ?>
                        <tr>
                            <td><?= data_br($a["data_agendamento"]) ?><br><small><?= e(dia_semana($a["data_agendamento"])) ?></small></td>
                            <td><?= substr($a["horario"], 0, 5) ?></td>
                            <td><?= e($a["cliente"]) ?><?php if ($a["telefone"] !== ""): ?><br><small><?= e($a["telefone"]) ?> · visitante</small><?php elseif ($a["usuario_id"]): ?><br><small>cliente cadastrado</small><?php endif; ?></td>
                            <td><?= e($a["servico"]) ?></td>
                            <td><?php if ($a["profissional"]) { echo e($a["profissional"]); } else { echo "sem preferência"; } ?></td>
                            <td class="coluna-obs"><?php if ($a["observacoes_corte"]) { echo e($a["observacoes_corte"]); } else { echo "-"; } ?></td>
                            <td>
                                <!-- cada linha tem seu formulário para trocar o status -->
                                <form method="post" class="form-status">
                                    <?= csrf_campo() ?>
                                    <input type="hidden" name="id" value="<?= (int) $a["id"] ?>">
                                    <label for="status-<?= (int) $a["id"] ?>" class="so-leitor">Status do agendamento de <?= e($a["cliente"]) ?></label>
                                    <select id="status-<?= (int) $a["id"] ?>" name="status">
                                        <?php foreach ($status_validos as $s): ?>
                                            <option value="<?= $s ?>" <?php if ($a["status"] == $s) { echo "selected"; } ?>><?= e(status_texto($s)) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="botao botao-pequeno">Salvar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require_once "../includes/footer.php"; ?>
