<?php
// auth/login.php
// Tela de login (RF02). Confere e-mail e senha e guarda o usuário na sessão.

$raiz = "../";
require_once "../includes/funcoes.php";

// Quem já está logado vai direto para o seu painel
if (usuario_logado() != null) {
    if (eh_equipe()) {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../cliente/painel.php");
    }
    exit;
}

$erro  = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    exigir_csrf();

    $email = strtolower(trim(pegar_post("email")));
    $senha = pegar_post("senha");

    // procura o usuário pelo e-mail
    $st = $pdo->prepare("SELECT id, nome, senha_hash, tipo FROM usuarios WHERE email = ?");
    $st->execute(array($email));
    $usuario = $st->fetch();

    // password_verify compara a senha digitada com o hash guardado no banco
    if ($usuario && password_verify($senha, $usuario["senha_hash"])) {

        // cria um novo id de sessão a cada login (evita roubo de sessão)
        session_regenerate_id(true);

        // guarda na sessão só o necessário (nunca a senha)
        $_SESSION["usuario"] = array(
            "id"   => $usuario["id"],
            "nome" => $usuario["nome"],
            "tipo" => $usuario["tipo"]
        );

        // cada tipo de usuário vai para uma área diferente
        if ($usuario["tipo"] == "cliente") {
            header("Location: ../cliente/painel.php");
        } else {
            header("Location: ../admin/dashboard.php");
        }
        exit;
    }

    // a mensagem é a mesma para e-mail errado e senha errada (não revela qual foi)
    $erro = "E-mail ou senha incorretos.";
}

$titulo_pagina = "Entrar";
require_once "../includes/header.php";
?>
<section class="secao">
    <div class="container container-estreito">
        <h1>Entrar</h1>
        <p class="subtitulo">Acesse sua conta para marcar e acompanhar seus horários.</p>

        <?php if ($erro != ""): ?>
            <div class="aviso aviso-erro" role="alert"><?= e($erro) ?></div>
        <?php endif; ?>

        <form method="post" class="formulario cartao">
            <?= csrf_campo() ?>

            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" value="<?= e($email) ?>" autocomplete="email" required>

            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" autocomplete="current-password" required>

            <button type="submit" class="botao">Entrar</button>
            <p class="ajuda">Ainda não tem conta? <a href="cadastro.php">Criar conta</a></p>
        </form>
    </div>
</section>
<?php require_once "../includes/footer.php"; ?>
