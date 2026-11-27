<?php
// chat/enviar_mensagem.php
// Salva uma mensagem do chat de suporte (RF25, RN15) e responde em JSON.
// O main.js envia o formulário do chat para cá, sem recarregar a página.

$raiz = "../";
require_once "../includes/funcoes.php";

header("Content-Type: application/json; charset=utf-8");

// Precisa estar logado
$u = usuario_logado();
if ($u == null) {
    echo json_encode(array("erro" => "Entre na sua conta para enviar mensagens."));
    exit;
}

// Só aceita envio de formulário (POST)
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(array("erro" => "Método não permitido."));
    exit;
}

// Confere o token de segurança (CSRF)
if (!csrf_valido()) {
    http_response_code(403);
    echo json_encode(array("erro" => "Sessão expirada. Atualize a página e tente de novo."));
    exit;
}

// Confere o texto da mensagem
$texto = trim(pegar_post("mensagem"));
if ($texto == "" || mb_strlen($texto) > 1000) {
    echo json_encode(array("erro" => "Escreva uma mensagem de até 1000 caracteres."));
    exit;
}

if ($u["tipo"] == "cliente") {
    // o cliente escreve na própria conversa
    $dono = $u["id"];
    $remetente = "cliente";
} else {
    // a equipe responde na conversa de um cliente
    $dono = (int) pegar_post("cliente_id");
    $remetente = "equipe";

    // confere se esse cliente existe
    $st = $pdo->prepare("SELECT id FROM usuarios WHERE id = ? AND tipo = 'cliente'");
    $st->execute(array($dono));
    if (!$st->fetch()) {
        echo json_encode(array("erro" => "Cliente não encontrado."));
        exit;
    }
}

// Salva a mensagem
$st = $pdo->prepare("INSERT INTO mensagens_suporte (usuario_id, remetente, mensagem) VALUES (?, ?, ?)");
$st->execute(array($dono, $remetente, $texto));

echo json_encode(array("ok" => true));
