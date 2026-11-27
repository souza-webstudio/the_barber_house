<?php
// header.php
// Parte de cima de TODAS as páginas: logo, menu e aviso de mensagens.
// Antes de incluir este arquivo, cada página define:
//   $raiz           -> caminho até a pasta principal ("" na raiz, "../" nas subpastas)
//   $titulo_pagina  -> título que aparece na aba do navegador
//   $area_admin     -> true nas páginas do painel administrativo (mostra o submenu)
require_once __DIR__ . "/funcoes.php";

if (!isset($titulo_pagina)) {
    $titulo_pagina = "";
}
if (!isset($area_admin)) {
    $area_admin = false;
}

// Dados de quem está logado (ou null)
$u = usuario_logado();

// Para onde vai o link "Olá, fulano": cliente vai para o painel, equipe vai para o resumo
$link_painel = "";
if ($u != null) {
    if ($u["tipo"] == "cliente") {
        $link_painel = $raiz . "cliente/painel.php";
    } else {
        $link_painel = $raiz . "admin/dashboard.php";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <!-- deixa o site responsivo: a página se ajusta à largura da tela -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php if ($titulo_pagina != "") { echo e($titulo_pagina) . " | "; } ?>The Barber House</title>
    <meta name="description" content="The Barber House: barbearia para adultos e crianças. Veja os serviços, os preços e marque seu horário pelo site.">
    <link rel="stylesheet" href="<?= $raiz ?>assets/css/style.css">
</head>
<body>
<!-- link para quem navega pelo teclado pular direto para o conteúdo -->
<a class="pular-conteudo" href="#conteudo">Pular para o conteúdo</a>

<header class="site-header">
    <div class="container cabecalho">
        <a class="logo" href="<?= $raiz ?>index.php">
            <img src="<?= $raiz ?>assets/img/logo-branca.png" alt="The Barber House - página inicial" width="56" height="100">
        </a>

        <!-- botão que abre e fecha o menu no celular (quem controla é o main.js) -->
        <button class="menu-botao" type="button" aria-expanded="false" aria-controls="menu-principal">Menu</button>

        <nav id="menu-principal" class="menu" aria-label="Menu principal">
            <a href="<?= $raiz ?>index.php">Início</a>
            <a href="<?= $raiz ?>index.php#servicos">Serviços</a>
            <a href="<?= $raiz ?>index.php#equipe">Equipe</a>
            <a href="<?= $raiz ?>index.php#produtos">Produtos</a>
            <a href="<?= $raiz ?>sobre.php">Sobre</a>
            <a href="<?= $raiz ?>contato.php">Contato</a>

            <?php if ($u != null): ?>
                <!-- usuário logado: mostra o nome e o botão Sair -->
                <a class="menu-usuario" href="<?= $link_painel ?>">Olá, <?= e(primeiro_nome($u["nome"])) ?></a>
                <form method="post" action="<?= $raiz ?>auth/logout.php" class="form-sair">
                    <?= csrf_campo() ?>
                    <button type="submit" class="botao botao-pequeno">Sair</button>
                </form>
            <?php else: ?>
                <!-- visitante: pode agendar sem cadastro ou entrar/criar conta -->
                <a class="botao botao-pequeno" href="<?= $raiz ?>agendar.php">Agendar</a>
                <a class="botao botao-contorno botao-pequeno" href="<?= $raiz ?>auth/login.php">Entrar</a>
                <a class="botao botao-pequeno" href="<?= $raiz ?>auth/cadastro.php">Criar conta</a>
            <?php endif; ?>
        </nav>
    </div>

    <?php if ($area_admin && eh_equipe()): ?>
        <!-- submenu do painel administrativo -->
        <nav class="submenu-admin" aria-label="Menu da administração">
            <div class="container">
                <a href="<?= $raiz ?>admin/dashboard.php">Resumo</a>
                <a href="<?= $raiz ?>admin/agendamentos.php">Agendamentos</a>
                <a href="<?= $raiz ?>admin/suporte.php">Suporte</a>
                <?php if (eh_admin()): ?>
                    <!-- estes links só o administrador vê -->
                    <a href="<?= $raiz ?>admin/servicos.php">Serviços</a>
                    <a href="<?= $raiz ?>admin/produtos.php">Produtos</a>
                    <a href="<?= $raiz ?>admin/profissionais.php">Profissionais</a>
                    <a href="<?= $raiz ?>admin/avaliacoes.php">Avaliações</a>
                <?php endif; ?>
            </div>
        </nav>
    <?php endif; ?>
</header>

<main id="conteudo">
<?php if (isset($_SESSION["mensagem_texto"])): ?>
    <!-- mensagem de sucesso ou erro (aparece uma vez só) -->
    <div class="container area-aviso"><?php mostrar_mensagem(); ?></div>
<?php endif; ?>
