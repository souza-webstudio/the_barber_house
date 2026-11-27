<?php
// conexao.php
// Conecta o PHP ao banco de dados MySQL usando PDO.
// No XAMPP o usuário padrão é "root" e a senha é vazia.

$host       = "localhost";
$banco      = "the_barber_house";
$usuario_db = "root";
$senha_db   = "";

// Fuso horário de Brasília, para as datas e horas ficarem certas
date_default_timezone_set("America/Sao_Paulo");

// Se acontecer um erro inesperado em qualquer página, esta função é chamada.
// O usuário vê uma mensagem simples e o detalhe técnico vai só para o log do servidor.
function tratar_erro($erro) {
    error_log("Erro no sistema: " . $erro->getMessage());
    http_response_code(500);
    echo "Ops! Aconteceu um erro inesperado. Tente de novo em instantes.";
}
set_exception_handler("tratar_erro");

// Tenta conectar. Se não conseguir, mostra o que provavelmente está errado.
try {
    $pdo = new PDO("mysql:host=$host;dbname=$banco;charset=utf8mb4", $usuario_db, $senha_db);

    // Faz o PDO lançar um erro quando uma consulta SQL estiver errada
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Faz o fetch() devolver arrays com o nome das colunas (ex.: $linha["nome"])
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Usa prepared statements de verdade (mais seguro contra SQL Injection)
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $erro) {
    error_log("Erro de conexão: " . $erro->getMessage());
    http_response_code(500);
    die("Não foi possível conectar ao banco de dados. Confira se o MySQL está ligado no XAMPP e se o arquivo sql/banco.sql foi importado.");
}
