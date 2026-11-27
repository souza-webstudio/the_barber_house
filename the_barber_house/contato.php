<?php
// contato.php
// Página de contato: endereço, telefone e horário de funcionamento.

$raiz = "";
$titulo_pagina = "Contato";
require_once "includes/header.php";
?>
<section class="secao">
    <div class="container">
        <h1>Contato</h1>
        <p class="subtitulo">Venha nos visitar ou tire suas dúvidas pelo site.</p>

        <div class="grade grade-3">
            <article class="cartao cartao-corpo">
                <h2>Endereço</h2>
                <p><?= e($endereco_barbearia) ?></p>
            </article>
            <article class="cartao cartao-corpo">
                <h2>Telefone</h2>
                <p><a href="tel:<?= preg_replace("/[^0-9]/", "", $telefone_barbearia) ?>"><?= e($telefone_barbearia) ?></a></p>
            </article>
            <article class="cartao cartao-corpo">
                <h2>Horário</h2>
                <p><?= e($horario_barbearia) ?>.</p>
                <p>Domingo e segunda: fechado.</p>
            </article>
        </div>

        <div class="cartao cartao-corpo bloco-contato">
            <h2>Dúvidas rápidas</h2>
            <p>O assistente no canto da tela responde sobre horário, preços, corte infantil e endereço. Para falar com a equipe, entre na sua conta e use o chat de suporte no seu painel.</p>
            <a class="botao" href="cliente/painel.php#suporte">Falar com a equipe</a>
        </div>
    </div>
</section>
<?php require_once "includes/footer.php"; ?>
