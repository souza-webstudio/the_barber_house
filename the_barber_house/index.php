<?php
// index.php
// Página inicial: apresenta serviços, equipe, produtos e depoimentos dos clientes.

$raiz = "";                 // esta página está na pasta principal
$titulo_pagina = "";
require_once "includes/header.php";

// Categorias de serviço, na ordem em que aparecem na tela
$categorias = array("Adulto", "Infantil", "Barba e Cuidados");

// Frase que aparece embaixo do nome de cada categoria
$descricoes = array(
    "Adulto"           => "Cortes clássicos e degradê, feitos com calma e do jeito que você pediu.",
    "Infantil"         => "Corte kids com paciência. Se a criança tem medo de máquina, a gente vai devagar.",
    "Barba e Cuidados" => "Barba modelada na navalha e combos para sair daqui inteiro alinhado."
);

// Busca os serviços de cada categoria e guarda numa lista
$servicos_por_categoria = array();
foreach ($categorias as $categoria) {
    $servicos_por_categoria[$categoria] = servicos_da_categoria($pdo, $categoria);
}

// Busca a equipe e os produtos
$profissionais = $pdo->query("SELECT * FROM profissionais ORDER BY id")->fetchAll();
$produtos = $pdo->query("SELECT * FROM produtos ORDER BY id")->fetchAll();

// Busca os depoimentos aprovados (as 6 avaliações mais recentes)
$sql = "SELECT v.nota, v.comentario, u.nome, s.nome AS servico
        FROM avaliacoes v
        JOIN usuarios u ON u.id = v.usuario_id
        JOIN agendamentos a ON a.id = v.agendamento_id
        JOIN servicos s ON s.id = a.servico_id
        WHERE v.aprovado = 1
        ORDER BY v.criado_em DESC
        LIMIT 6";
$depoimentos = $pdo->query($sql)->fetchAll();
?>

<!-- Faixa principal -->
<section class="hero">
    <div class="container hero-conteudo">
        <h1>Seu corte, do jeito que você pediu.</h1>
        <p>Barbearia para adultos e crianças. Cortes clássicos, degradê, barba e um atendimento com paciência para os pequenos.</p>
        <div class="hero-botoes">
            <a class="botao botao-grande" href="agendar.php">Agendar sem cadastro</a>
            <a class="botao botao-grande botao-contorno" href="#servicos">Ver serviços e preços</a>
        </div>
        <p class="hero-horario">Atendemos de terça a sábado, das 9h às 19h30.</p>
    </div>
</section>

<!-- Como marcar o horário -->
<section class="secao secao-clara" aria-labelledby="como-agendar">
    <div class="container">
        <h2 id="como-agendar">Como marcar seu horário</h2>
        <ol class="passos">
            <li><strong>Escolha como deseja agendar.</strong> Você pode entrar na sua conta ou continuar como visitante, sem cadastro.</li>
            <li><strong>Escolha o serviço, o dia e a hora.</strong> Informe seu nome e telefone para a equipe confirmar o atendimento.</li>
            <li><strong>Aguarde a confirmação.</strong> A equipe recebe seu pedido no painel administrativo.</li>
        </ol>
    </div>
</section>

<!-- Serviços e preços, separados por categoria -->
<section id="servicos" class="secao" aria-labelledby="titulo-servicos">
    <div class="container">
        <h2 id="titulo-servicos">Serviços e preços</h2>
        <p class="subtitulo">Os valores abaixo são os mesmos para todo mundo. Sem surpresa na hora de pagar.</p>

        <?php foreach ($categorias as $categoria): ?>
            <?php $lista = $servicos_por_categoria[$categoria]; ?>
            <?php if (count($lista) > 0): ?>
                <h3 class="categoria"><?= e($categoria) ?></h3>
                <p class="categoria-texto"><?= e($descricoes[$categoria]) ?></p>

                <div class="grade grade-3">
                    <!-- um cartão para cada serviço da categoria -->
                    <?php foreach ($lista as $s): ?>
                        <article class="cartao cartao-servico">
                            <?php imagem_ou_inicial($raiz, $s["imagem"], $s["nome"], "cartao-foto"); ?>
                            <div class="cartao-corpo">
                                <h4><?= e($s["nome"]) ?></h4>
                                <p><?= e($s["descricao"]) ?></p>
                                <p class="preco"><?= preco($s["preco"]) ?></p>
                                <a class="botao botao-pequeno" href="agendar.php?servico=<?= (int) $s["id"] ?>">Agendar <span class="so-leitor"><?= e($s["nome"]) ?></span></a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</section>

<!-- Explicação do corte infantil -->
<section class="secao secao-clara" aria-labelledby="titulo-kids">
    <div class="container container-estreito">
        <h2 id="titulo-kids">Corte infantil com paciência</h2>
        <p>Cada criança tem o seu tempo. Na hora de agendar existe o campo <strong>Observações do corte</strong>, onde você pode avisar se seu filho tem medo de máquina, se é o primeiro corte ou como ele gosta do cabelo. Assim o barbeiro já sabe antes de a criança sentar na cadeira.</p>
        <p>Pai, mãe ou responsável pode criar a conta e marcar o horário no nome da criança.</p>
        <a class="botao" href="agendar.php">Marcar corte infantil</a>
    </div>
</section>

<!-- Equipe de barbeiros -->
<section id="equipe" class="secao" aria-labelledby="titulo-equipe">
    <div class="container">
        <h2 id="titulo-equipe">Nossa equipe</h2>
        <div class="grade grade-3">
            <?php foreach ($profissionais as $p): ?>
                <article class="cartao cartao-pessoa">
                    <?php imagem_ou_inicial($raiz, $p["imagem"], $p["nome"], "cartao-foto"); ?>
                    <div class="cartao-corpo">
                        <h3><?= e($p["nome"]) ?></h3>
                        <p class="destaque"><?= e($p["especialidade"]) ?></p>
                        <p><?= e($p["bio"]) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Produtos (só catálogo, sem venda online) -->
<section id="produtos" class="secao secao-clara" aria-labelledby="titulo-produtos">
    <div class="container">
        <h2 id="titulo-produtos">Produtos da casa</h2>
        <p class="subtitulo">Os mesmos que usamos na cadeira. A compra é feita na barbearia, aqui você só confere o que tem em estoque.</p>
        <div class="grade grade-4">
            <?php foreach ($produtos as $p): ?>
                <article class="cartao cartao-produto">
                    <?php imagem_ou_inicial($raiz, $p["imagem"], $p["nome"], "cartao-foto"); ?>
                    <div class="cartao-corpo">
                        <h3><?= e($p["nome"]) ?></h3>
                        <p><?= e($p["descricao"]) ?></p>
                        <?php if (!empty($p["preco_promocional"]) && (float) $p["preco_promocional"] > 0 && (float) $p["preco_promocional"] < (float) $p["preco"]): ?>
                            <?php $desconto = (int) round((1 - ((float) $p["preco_promocional"] / (float) $p["preco"])) * 100); ?>
                            <div class="preco-oferta">
                                <span class="preco-antigo"><?= preco($p["preco"]) ?></span>
                                <span class="selo selo-oferta">Economize <?= $desconto ?>%</span>
                                <p class="preco preco-promocional"><?= preco($p["preco_promocional"]) ?></p>
                            </div>
                        <?php else: ?>
                            <p class="preco"><?= preco($p["preco"]) ?></p>
                        <?php endif; ?>
                        <p><span class="selo selo-<?= estoque_classe($p["estoque"]) ?>"><?= estoque_texto($p["estoque"]) ?></span></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Depoimentos: só aparece se existir pelo menos um -->
<?php if (count($depoimentos) > 0): ?>
<section id="depoimentos" class="secao" aria-labelledby="titulo-depoimentos">
    <div class="container">
        <h2 id="titulo-depoimentos">O que os clientes dizem</h2>
        <div class="grade grade-3">
            <?php foreach ($depoimentos as $d): ?>
                <figure class="cartao depoimento">
                    <!-- estrelas cheias (★) e vazias (☆) -->
                    <p class="estrelas" aria-label="Nota <?= (int) $d["nota"] ?> de 5"><?= str_repeat("★", $d["nota"]) . str_repeat("☆", 5 - $d["nota"]) ?></p>
                    <blockquote><?= e($d["comentario"]) ?></blockquote>
                    <figcaption><?= e(nome_curto($d["nome"])) ?> - <?= e($d["servico"]) ?></figcaption>
                </figure>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="secao secao-clara">
    <div class="container container-estreito texto-centro">
        <h2>Pronto para marcar?</h2>
        <p>Escolha o dia e o horário em poucos cliques. Qualquer dúvida, o assistente no canto da tela responde na hora.</p>
        <a class="botao botao-grande" href="agendar.php">Agendar sem cadastro</a>
    </div>
</section>

<?php require_once "includes/footer.php"; ?>
