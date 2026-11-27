<?php
// chat/buscar_mensagens.php
// Devolve as mensagens do chat de suporte em formato JSON.
// O main.js chama esta página a cada 4 segundos (RNF17).
// Parâmetros na URL:
//   depois     -> só devolve mensagens com id maior que este número (as novas)
//   cliente_id -> usado pela equipe para escolher a conversa

$raiz = "../";
require_once "../includes/funcoes.php";

// avisa o navegador que a resposta é JSON
header("Content-Type: application/json; charset=utf-8");

$u = usuario_logado();
if ($u == null) {
    echo json_encode(array("erro" => "Entre na sua conta."));
    exit;
}

// O cliente só vê a própria conversa. A equipe escolhe o cliente.
if ($u["tipo"] == "cliente") {
    $dono = $u["id"];
    $outra_parte = "equipe";        // as mensagens da equipe serão marcadas como lidas
} else {
    $dono = (int) pegar_get("cliente_id");
    $outra_parte = "cliente";       // as mensagens do cliente serão marcadas como lidas
}
$depois = (int) pegar_get("depois");

// Busca as mensagens novas da conversa
$st = $pdo->prepare("SELECT id, remetente, mensagem, criado_em
                     FROM mensagens_suporte
                     WHERE usuario_id = ? AND id > ?
                     ORDER BY id");
$st->execute(array($dono, $depois));
$linhas = $st->fetchAll();

// Monta a lista que vai para o JavaScript
$mensagens = array();
foreach ($linhas as $m) {
    $mensagens[] = array(
        "id"        => (int) $m["id"],
        "remetente" => $m["remetente"],
        "mensagem"  => $m["mensagem"],
        "hora"      => date("d/m H:i", strtotime($m["criado_em"]))
    );
}

// Marca como lidas as mensagens que vieram da outra pessoa
$st = $pdo->prepare("UPDATE mensagens_suporte SET lida = 1 WHERE usuario_id = ? AND remetente = ? AND lida = 0");
$st->execute(array($dono, $outra_parte));

echo json_encode(array("mensagens" => $mensagens));
