<?php
// footer.php
// Parte de baixo de TODAS as páginas: rodapé, assistente rápido (chatbot) e scripts.

// Lista de serviços para o chatbot responder os preços
$lista_servicos = $pdo->query("SELECT nome, preco, categoria FROM servicos ORDER BY preco")->fetchAll();
?>
</main>

<footer class="rodape">
    <div class="container rodape-grade">
        <div>
            <h2 class="rodape-titulo">The Barber House</h2>
            <p>Barbearia para adultos e crianças.</p>
        </div>
        <div>
            <h2 class="rodape-titulo">Onde estamos</h2>
            <p><?= e($endereco_barbearia) ?></p>
            <!-- preg_replace tira tudo que não é número, para o link tel: funcionar -->
            <p>Telefone: <a href="tel:<?= preg_replace("/[^0-9]/", "", $telefone_barbearia) ?>"><?= e($telefone_barbearia) ?></a></p>
        </div>
        <div>
            <h2 class="rodape-titulo">Horário</h2>
            <p><?= e($horario_barbearia) ?></p>
            <p>Atendimento a cada 30 minutos.</p>
        </div>
        <div>
            <h2 class="rodape-titulo">Links</h2>
            <p><a href="<?= $raiz ?>sobre.php">Sobre a barbearia</a></p>
            <p><a href="<?= $raiz ?>contato.php">Contato</a></p>
            <p><a href="<?= $raiz ?>empresa/index.php">Feito pela ByteCut Solutions</a></p>
        </div>
    </div>
    <p class="rodape-final">The Barber House - projeto final do curso Técnico em Informática para Internet</p>
</footer>

<!-- Assistente rápido (chatbot por palavras-chave). Quem faz ele funcionar é o chat_ia/chatbot.js -->
<button type="button" id="chatbot-botao" class="chatbot-botao" aria-expanded="false" aria-controls="chatbot-janela">Dúvidas?</button>

<div id="chatbot-janela" class="chatbot-janela" role="dialog" aria-label="Assistente rápido da The Barber House" hidden>
    <div class="chatbot-titulo">
        <strong>Assistente rápido</strong>
        <button type="button" id="chatbot-fechar" class="chatbot-fechar">Fechar</button>
    </div>

    <!-- aqui aparecem as mensagens -->
    <div id="chatbot-mensagens" class="chatbot-mensagens" role="log" aria-live="polite"></div>

    <!-- botões de perguntas prontas -->
    <div class="chatbot-atalhos">
        <button type="button" data-pergunta="horário">Horário</button>
        <button type="button" data-pergunta="preços">Preços</button>
        <button type="button" data-pergunta="corte infantil">Corte infantil</button>
        <button type="button" data-pergunta="endereço">Endereço</button>
        <button type="button" data-pergunta="agendar">Como agendar</button>
    </div>

    <form id="chatbot-form" class="chatbot-form">
        <label for="chatbot-campo" class="so-leitor">Digite sua dúvida</label>
        <input type="text" id="chatbot-campo" placeholder="Digite sua dúvida" autocomplete="off" maxlength="200">
        <button type="submit" class="botao botao-pequeno">Enviar</button>
    </form>
</div>

<!-- Dados do PHP que o JavaScript do chatbot vai usar -->
<script>
    var raiz = <?= json_encode($raiz) ?>;
    var servicos = <?= json_encode($lista_servicos, JSON_HEX_TAG) ?>;
    var enderecoBarbearia = <?= json_encode($endereco_barbearia, JSON_HEX_TAG) ?>;
    var telefoneBarbearia = <?= json_encode($telefone_barbearia, JSON_HEX_TAG) ?>;
    var horarioBarbearia = <?= json_encode($horario_barbearia, JSON_HEX_TAG) ?>;
</script>
<script src="<?= $raiz ?>assets/js/main.js"></script>
<script src="<?= $raiz ?>chat_ia/chatbot.js"></script>
</body>
</html>
