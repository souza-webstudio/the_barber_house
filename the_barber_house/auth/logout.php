<?php
// auth/logout.php
// Encerra a sessão do usuário (RF03). O botão "Sair" do menu envia um formulário para cá.

$raiz = "../";
require_once "../includes/funcoes.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && csrf_valido()) {
    // tira o usuário da sessão e troca o id da sessão
    unset($_SESSION["usuario"]);
    session_regenerate_id(true);

    mensagem("sucesso", "Você saiu da sua conta. Até a próxima!");
}

header("Location: ../index.php");
exit;
