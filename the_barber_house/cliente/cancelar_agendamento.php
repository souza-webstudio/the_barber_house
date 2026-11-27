<?php
// cliente/cancelar_agendamento.php
// O cliente cancela um agendamento seu (RF29, RN16).
// Esta página não mostra nada: só atualiza o banco e volta para o painel.

$raiz = "../";
require_once "../includes/funcoes.php";
exigir_cliente($raiz);

// Só aceita o envio do formulário (POST), não aceita abrir o endereço direto
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: painel.php");
    exit;
}
exigir_csrf();

$id = (int) pegar_post("id");
$usuario = usuario_logado();

// O WHERE garante que o agendamento é do próprio cliente
// e que ainda está pendente ou confirmado
$sql = "UPDATE agendamentos SET status = 'cancelado'
        WHERE id = ? AND usuario_id = ? AND status IN ('pendente', 'confirmado')";
$st = $pdo->prepare($sql);
$st->execute(array($id, $usuario["id"]));

// rowCount() diz quantas linhas foram alteradas
if ($st->rowCount() > 0) {
    mensagem("sucesso", "Agendamento cancelado.");
} else {
    mensagem("erro", "Não foi possível cancelar esse agendamento.");
}

header("Location: painel.php#meus-agendamentos");
exit;
