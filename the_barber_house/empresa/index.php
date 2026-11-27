<?php
// empresa/index.php
// Site institucional da ByteCut Solutions (a empresa fictícia da equipe).
// Não usa banco de dados, então abre mesmo com o MySQL desligado.

// Lista com os integrantes da equipe (cada integrante é um grupo de dados)
$equipe = array(
    array(
        "iniciais"          => "LV",
        "nome"              => "Lucas Vitor",
        "papel"             => "Analista / Product Owner",
        "responsabilidades" => "Levantamento do problema, definição dos requisitos, organização do 5W2H e alinhamento do escopo do projeto.",
        "atuacao"           => "Conduziu a análise de negócio e fez a ponte entre as dores da persona e as funcionalidades do sistema."
    ),
    array(
        "iniciais"          => "KA",
        "nome"              => "Késia Alves",
        "papel"             => "Desenvolvedora Front-end",
        "responsabilidades" => "Construção das interfaces, layout, CSS responsivo e experiência de uso nas telas.",
        "atuacao"           => "Desenvolveu a identidade visual e as páginas, e cuidou para que tudo funcione bem no celular, no tablet e no computador."
    ),
    array(
        "iniciais"          => "SX",
        "nome"              => "Samuel Xavier",
        "papel"             => "Desenvolvedor Back-end",
        "responsabilidades" => "Programação em PHP, regras de negócio, autenticação e sistema de login com áreas restritas.",
        "atuacao"           => "Implementou os CRUDs, as validações no servidor e a ligação das páginas com o banco de dados."
    ),
    array(
        "iniciais"          => "KB",
        "nome"              => "Kelton Bryan",
        "papel"             => "Banco de Dados / QA",
        "responsabilidades" => "Modelagem do banco no MySQL, scripts SQL, testes funcionais e documentação técnica.",
        "atuacao"           => "Estruturou as tabelas, cuidou da integridade dos dados e testou os fluxos de agendamento."
    )
);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ByteCut Solutions</title>
    <meta name="description" content="ByteCut Solutions: empresa fictícia criada para o projeto final do curso Técnico em Informática para Internet. Criadora do sistema The Barber House.">
    <!-- usa o mesmo CSS do sistema; a classe "empresa" no body troca as cores para azul -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="empresa">
<a class="pular-conteudo" href="#conteudo">Pular para o conteúdo</a>

<header class="site-header">
    <div class="container cabecalho">
        <a class="logo logo-empresa" href="#inicio">
            <img src="../assets/img/logo-bytecut.png" alt="ByteCut Solutions">
        </a>
        <button class="menu-botao" type="button" aria-expanded="false" aria-controls="menu-principal">Menu</button>
        <nav id="menu-principal" class="menu" aria-label="Menu principal">
            <a href="#inicio">Início</a>
            <a href="#sobre">Sobre nós</a>
            <a href="#equipe">Equipe</a>
            <a href="#produto">Nosso produto</a>
            <a class="botao botao-pequeno" href="#contato">Contato</a>
        </nav>
    </div>
</header>

<main id="conteudo">

    <!-- Página inicial: nome, slogan e apresentação -->
    <section id="inicio" class="hero">
        <div class="container hero-conteudo">
            <!-- o título abaixo é o slogan da empresa -->
            <h1>Tecnologia simples para problemas do dia a dia.</h1>
            <p>A ByteCut Solutions cria sistemas web fáceis de usar para pequenos negócios que ainda organizam tudo no papel e no WhatsApp.</p>
            <div class="hero-botoes">
                <a class="botao botao-grande" href="#produto">Conhecer o The Barber House</a>
                <a class="botao botao-grande botao-contorno" href="#equipe">Ver a equipe</a>
            </div>
        </div>
    </section>

    <!-- Sobre nós: quem somos, missão, visão e valores -->
    <section id="sobre" class="secao" aria-labelledby="titulo-sobre">
        <div class="container">
            <h2 id="titulo-sobre">Sobre nós</h2>
            <p class="subtitulo"><strong>Quem somos:</strong> uma empresa de tecnologia criada por quatro estudantes do curso Técnico em Informática para Internet (FAETEC). Nosso foco é transformar processos manuais em rotinas digitais simples.</p>

            <div class="grade grade-3">
                <article class="cartao cartao-corpo">
                    <h3>Missão</h3>
                    <p>Desenvolver sistemas web funcionais e fáceis de entender, que resolvam problemas reais de pequenas empresas e economizem o tempo de quem usa.</p>
                </article>
                <article class="cartao cartao-corpo">
                    <h3>Visão</h3>
                    <p>Ser lembrada como uma empresa que entrega software acessível, com qualidade técnica, boa usabilidade e impacto positivo no dia a dia dos clientes.</p>
                </article>
                <article class="cartao cartao-corpo">
                    <h3>Valores</h3>
                    <p>Compromisso com o problema real, clareza na comunicação, simplicidade de uso, organização técnica e atenção à experiência de quem usa o sistema.</p>
                </article>
            </div>
        </div>
    </section>

    <!-- Equipe: um cartão para cada integrante -->
    <section id="equipe" class="secao secao-clara" aria-labelledby="titulo-equipe">
        <div class="container">
            <h2 id="titulo-equipe">Nossa equipe</h2>
            <p class="subtitulo">Cada integrante tem um papel principal, mas as decisões do projeto foram tomadas em grupo.</p>

            <div class="grade grade-4">
                <?php foreach ($equipe as $m): ?>
                    <article class="cartao cartao-corpo cartao-membro">
                        <div class="avatar" aria-hidden="true"><?= htmlspecialchars($m["iniciais"]) ?></div>
                        <h3><?= htmlspecialchars($m["nome"]) ?></h3>
                        <p class="destaque"><?= htmlspecialchars($m["papel"]) ?></p>
                        <h4>Responsabilidades</h4>
                        <p><?= htmlspecialchars($m["responsabilidades"]) ?></p>
                        <h4>Participação no projeto</h4>
                        <p><?= htmlspecialchars($m["atuacao"]) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Produto: problema, público, como funciona, funcionalidades e diferenciais -->
    <section id="produto" class="secao" aria-labelledby="titulo-produto">
        <div class="container container-estreito">
            <h2 id="titulo-produto">Nosso produto: The Barber House</h2>

            <h3>Qual problema resolve</h3>
            <p>Barbearias pequenas costumam marcar horário por WhatsApp e caderno. Quando mais de uma pessoa responde, é fácil marcar dois clientes no mesmo horário, e o cliente fica sem saber o preço ou se o horário foi confirmado.</p>

            <h3>Para quem foi feito</h3>
            <p>Para donos e barbeiros de barbearias pequenas e para os clientes, incluindo pais e mães que marcam o corte dos filhos.</p>

            <h3>Como funciona</h3>
            <ol class="lista-simples">
                <li>O cliente cria uma conta e entra no site.</li>
                <li>Ele escolhe o serviço, o dia, o horário e, se quiser, o barbeiro e as observações do corte.</li>
                <li>O sistema confere o dia, o horário e se existe conflito. Se estiver tudo certo, o agendamento fica pendente.</li>
                <li>A equipe confirma ou conclui o atendimento pelo painel administrativo, e o cliente acompanha o status no painel dele.</li>
            </ol>

            <h3>Principais funcionalidades</h3>
            <ul class="lista-simples">
                <li>Agendamento com escolha de serviço, barbeiro (opcional), data, horário e observações do corte.</li>
                <li>Bloqueio automático de conflito de horário e de dias fechados.</li>
                <li>Catálogo de serviços adultos, infantis e de barba, com preços claros.</li>
                <li>Painel do cliente para acompanhar, cancelar e avaliar atendimentos.</li>
                <li>Painel administrativo para serviços, produtos, profissionais, agendamentos e depoimentos.</li>
                <li>Chat de suporte com a equipe e assistente rápido para dúvidas comuns.</li>
            </ul>

            <h3>Diferenciais</h3>
            <ul class="lista-simples">
                <li>Campo "Observações do corte": o pedido do cliente fica registrado e a equipe vê antes do atendimento.</li>
                <li>Atenção ao público infantil, com categoria própria de serviços e barbeiro especialista em corte infantil.</li>
                <li>Preços iguais para todos, mostrados antes do cliente sair de casa.</li>
                <li>Sistema feito sob medida para o fluxo da barbearia, sem depender de aplicativo pronto.</li>
            </ul>

            <h3>Tecnologias</h3>
            <p>PHP 8 com PDO, MySQL, HTML5, CSS3 responsivo e JavaScript. Senhas protegidas com hash e proteção contra CSRF nos formulários.</p>

            <a class="botao botao-grande" href="../index.php">Abrir o The Barber House</a>
        </div>
    </section>

    <section id="contato" class="secao secao-clara" aria-labelledby="titulo-contato">
        <div class="container container-estreito texto-centro">
            <h2 id="titulo-contato">Quer ver o sistema funcionando?</h2>
            <p>O The Barber House é o projeto final do curso Técnico em Informática para Internet. Abra o sistema, crie uma conta de cliente e marque um horário de teste.</p>
            <a class="botao" href="../auth/cadastro.php">Criar conta de teste</a>
        </div>
    </section>
</main>

<footer class="rodape rodape-empresa">
    <p class="rodape-final">ByteCut Solutions - empresa fictícia criada para o projeto final do curso Técnico em Informática para Internet</p>
</footer>

<script src="../assets/js/main.js"></script>
</body>
</html>
