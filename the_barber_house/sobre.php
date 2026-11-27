<?php
// sobre.php
// Página "Sobre": conta como a barbearia atende e como funciona o agendamento.

$raiz = "";
$titulo_pagina = "Sobre";
require_once "includes/header.php";
?>
<section class="secao">
    <div class="container container-estreito">
        <h1>Sobre a The Barber House</h1>
        <p>A The Barber House é uma barbearia pensada para a família toda. Aqui chega o cliente que quer o degradê bem feito, o que prefere o corte clássico de sempre e também o pai que traz o filho para o primeiro corte.</p>

        <h2>Como a gente atende</h2>
        <p>Antes de começar, o barbeiro pergunta o que você quer e confirma o tamanho do corte. Se você escreveu algo no campo de observações do agendamento, ele já leu antes de você chegar.</p>
        <p>Com as crianças a gente vai no ritmo delas. Se precisar parar para respirar, a gente para.</p>

        <h2>Por que agendar pelo site</h2>
        <p>Cada barbeiro tem a sua agenda, então dois clientes nunca ficam marcados no mesmo horário com o mesmo barbeiro. Você vê na hora se o horário foi aceito e acompanha o status do agendamento no seu painel.</p>

        <h2>Horário de funcionamento</h2>
        <p><?= e($horario_barbearia) ?>. Fechamos aos domingos e às segundas-feiras.</p>

        <a class="botao" href="agendar.php">Agendar horário</a>
    </div>
</section>
<?php require_once "includes/footer.php"; ?>
