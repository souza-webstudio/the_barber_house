<?php
// auth/cadastro.php
// Cria a conta de um novo cliente (RF01, RN01).

$raiz = "../";              // esta página está dentro de uma subpasta
require_once "../includes/funcoes.php";

// Quem já está logado não precisa criar conta
if (usuario_logado() != null) {
    header("Location: ../cliente/painel.php");
    exit;
}

$erro  = "";
$nome  = "";
$email = "";

// Só entra aqui quando o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    exigir_csrf();

    // pega o que a pessoa digitou
    $nome      = trim(pegar_post("nome"));
    $email     = strtolower(trim(pegar_post("email")));
    $senha     = pegar_post("senha");
    $confirmar = pegar_post("confirmar");

    // confere cada campo (se achar um erro, guarda a mensagem)
    if (mb_strlen($nome) < 3 || mb_strlen($nome) > 120) {
        $erro = "Digite seu nome completo (de 3 a 120 letras).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        // filter_var confere se o e-mail tem formato válido
        $erro = "Digite um e-mail válido.";
    } elseif (strlen($senha) < 6) {
        $erro = "A senha precisa ter pelo menos 6 caracteres.";
    } elseif ($senha != $confirmar) {
        $erro = "As duas senhas não são iguais.";
    } else {
        // procura se já existe alguém com esse e-mail (o e-mail é único)
        $st = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $st->execute(array($email));
        $ja_existe = $st->fetch();

        if ($ja_existe) {
            $erro = "Já existe uma conta com esse e-mail. Tente entrar.";
        } else {
            // guarda só o hash da senha, nunca a senha de verdade
            $hash = password_hash($senha, PASSWORD_DEFAULT);

            $st = $pdo->prepare("INSERT INTO usuarios (nome, email, senha_hash, tipo) VALUES (?, ?, ?, 'cliente')");
            $st->execute(array($nome, $email, $hash));

            mensagem("sucesso", "Conta criada! Agora é só entrar com seu e-mail e senha.");
            header("Location: login.php");
            exit;
        }
    }
}

$titulo_pagina = "Criar conta";
require_once "../includes/header.php";
?>
<section class="secao">
    <div class="container container-estreito">
        <h1>Criar conta</h1>
        <p class="subtitulo">Com a conta você marca o horário pelo site e acompanha seus agendamentos. Pais e mães também usam para marcar o corte dos filhos.</p>

        <?php if ($erro != ""): ?>
            <div class="aviso aviso-erro" role="alert"><?= e($erro) ?></div>
        <?php endif; ?>

        <form method="post" class="formulario cartao">
            <?= csrf_campo() ?>

            <label for="nome">Nome completo</label>
            <input type="text" id="nome" name="nome" value="<?= e($nome) ?>" maxlength="120" autocomplete="name" required>

            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" value="<?= e($email) ?>" maxlength="150" autocomplete="email" required>

            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" minlength="6" autocomplete="new-password" required>
            <p class="ajuda">Use pelo menos 6 caracteres.</p>

            <label for="confirmar">Repita a senha</label>
            <input type="password" id="confirmar" name="confirmar" minlength="6" autocomplete="new-password" required>

            <button type="submit" class="botao">Criar conta</button>
            <p class="ajuda">Já tem conta? <a href="login.php">Entrar</a></p>
        </form>
    </div>
</section>
<?php require_once "../includes/footer.php"; ?>
