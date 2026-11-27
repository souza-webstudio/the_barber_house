<?php
// admin/produtos.php
// CRUD de produtos feito pelo administrador (RF19 a RF22):
//   C = cadastrar, R = listar, U = editar, D = excluir
// Os produtos só aparecem como catálogo (não existe venda online, RN25).

$raiz = "../";
require_once "../includes/funcoes.php";
exigir_admin($raiz);

$erro = "";

// Valores que aparecem no formulário. Vazios = cadastro de um produto novo.
$id        = "";
$nome      = "";
$descricao = "";
$preco     = "";
$preco_promocional = "";
$estoque   = "0";
$imagem    = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    exigir_csrf();
    $acao = pegar_post("acao");      // "salvar" ou "excluir"

    // ---------- EXCLUIR ----------
    if ($acao == "excluir") {
        $st = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
        $st->execute(array((int) pegar_post("id")));
        mensagem("sucesso", "Produto excluído.");
        header("Location: produtos.php");
        exit;
    }

    // ---------- SALVAR (cadastrar ou editar) ----------
    if ($acao == "salvar") {
        $id        = pegar_post("id");
        $nome      = trim(pegar_post("nome"));
        $descricao = trim(pegar_post("descricao"));
        $preco     = trim(pegar_post("preco"));
        $preco_promocional = trim(pegar_post("preco_promocional"));
        $estoque   = trim(pegar_post("estoque"));
        $imagem    = trim(pegar_post("imagem"));

        // troca a vírgula por ponto (28,00 vira 28.00)
        $valor = str_replace(",", ".", $preco);
        $valor_promocional = null;
        if ($preco_promocional != "") {
            $valor_promocional = str_replace(",", ".", $preco_promocional);
        }

        // valida os campos
        if ($nome == "" || mb_strlen($nome) > 120) {
            $erro = "Digite o nome do produto (até 120 caracteres).";
        } elseif (!is_numeric($valor) || $valor < 0 || $valor > 99999) {
            $erro = "Digite um preço válido, por exemplo 28,00.";
        } elseif ($valor_promocional !== null && (!is_numeric($valor_promocional) || $valor_promocional <= 0 || $valor_promocional >= $valor)) {
            $erro = "O preço promocional deve ser válido e menor que o preço normal.";
        } elseif (!ctype_digit($estoque) || (int) $estoque > 100000) {
            // ctype_digit confere se o texto tem só números (0 a 9)
            $erro = "O estoque precisa ser um número inteiro, de 0 para cima.";
        } else {
            $imagem_salvar = null;
            if ($imagem != "") {
                $imagem_salvar = $imagem;
            }

            if ($id != "") {
                // tem id: edição (UPDATE)
                $st = $pdo->prepare("UPDATE produtos SET nome = ?, descricao = ?, preco = ?, preco_promocional = ?, estoque = ?, imagem = ? WHERE id = ?");
                $st->execute(array($nome, $descricao, $valor, $valor_promocional, (int) $estoque, $imagem_salvar, (int) $id));
                mensagem("sucesso", "Produto atualizado.");
            } else {
                // sem id: cadastro novo (INSERT)
                $st = $pdo->prepare("INSERT INTO produtos (nome, descricao, preco, preco_promocional, estoque, imagem) VALUES (?, ?, ?, ?, ?, ?)");
                $st->execute(array($nome, $descricao, $valor, $valor_promocional, (int) $estoque, $imagem_salvar));
                mensagem("sucesso", "Produto cadastrado.");
            }
            header("Location: produtos.php");
            exit;
        }
    }
} elseif (isset($_GET["editar"])) {
    // ---------- Clicou em "Editar": carrega o produto no formulário ----------
    $st = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
    $st->execute(array((int) pegar_get("editar")));
    $linha = $st->fetch();

    if ($linha) {
        $id        = $linha["id"];
        $nome      = $linha["nome"];
        $descricao = $linha["descricao"];
        $preco     = number_format($linha["preco"], 2, ",", "");
        $preco_promocional = $linha["preco_promocional"] !== null ? number_format($linha["preco_promocional"], 2, ",", "") : "";
        $estoque   = $linha["estoque"];
        $imagem    = $linha["imagem"];
    }
}

$produtos = $pdo->query("SELECT * FROM produtos ORDER BY nome")->fetchAll();

$titulo_pagina = "Produtos";
$area_admin = true;
require_once "../includes/header.php";
?>
<section class="secao">
    <div class="container">
        <h1>Produtos</h1>
        <p class="subtitulo">Os produtos aparecem na vitrine da página inicial só como catálogo. Não existe venda online.</p>

        <!-- Formulário de cadastro / edição -->
        <section class="cartao cartao-corpo" aria-labelledby="titulo-form">
            <h2 id="titulo-form"><?php if ($id != "") { echo "Editar produto"; } else { echo "Novo produto"; } ?></h2>

            <?php if ($erro != ""): ?>
                <div class="aviso aviso-erro" role="alert"><?= e($erro) ?></div>
            <?php endif; ?>

            <form method="post" class="formulario formulario-largo">
                <?= csrf_campo() ?>
                <input type="hidden" name="acao" value="salvar">
                <input type="hidden" name="id" value="<?= e($id) ?>">

                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" value="<?= e($nome) ?>" maxlength="120" required>

                <label for="descricao">Descrição</label>
                <textarea id="descricao" name="descricao" rows="3"><?= e($descricao) ?></textarea>

                <label for="preco">Preço (R$)</label>
                <input type="text" id="preco" name="preco" value="<?= e($preco) ?>" inputmode="decimal" placeholder="28,00" required>

                <label for="preco_promocional">Preço promocional (R$) — opcional</label>
                <input type="text" id="preco_promocional" name="preco_promocional" value="<?= e($preco_promocional) ?>" inputmode="decimal" placeholder="Ex.: 23,90">
                <small>Deixe em branco para não oferecer desconto. O valor promocional precisa ser menor que o preço normal.</small>

                <label for="estoque">Quantidade em estoque</label>
                <input type="number" id="estoque" name="estoque" min="0" step="1" value="<?= e($estoque) ?>" required>

                <label for="imagem">Imagem (opcional)</label>
                <input type="text" id="imagem" name="imagem" value="<?= e($imagem) ?>" maxlength="255" placeholder="pomada.jpg (nome do arquivo que está em assets/img)">

                <div class="botoes-linha">
                    <button type="submit" class="botao">Salvar produto</button>
                    <?php if ($id != ""): ?>
                        <a class="botao botao-contorno" href="produtos.php">Cancelar edição</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <!-- Tabela com os produtos cadastrados -->
        <h2>Produtos cadastrados</h2>
        <div class="tabela-rolagem">
            <table>
                <thead><tr><th>Nome</th><th>Preço normal / promocional</th><th>Estoque</th><th>Ações</th></tr></thead>
                <tbody>
                <?php foreach ($produtos as $p): ?>
                    <tr>
                        <td><?= e($p["nome"]) ?></td>
                        <td>
                            <?php if (!empty($p["preco_promocional"]) && (float) $p["preco_promocional"] > 0 && (float) $p["preco_promocional"] < (float) $p["preco"]): ?>
                                <del><?= preco($p["preco"]) ?></del><br>
                                <strong><?= preco($p["preco_promocional"]) ?></strong>
                            <?php else: ?>
                                <?= preco($p["preco"]) ?>
                            <?php endif; ?>
                        </td>
                        <td><?= (int) $p["estoque"] ?> <span class="selo selo-<?= estoque_classe($p["estoque"]) ?>"><?= estoque_texto($p["estoque"]) ?></span></td>
                        <td class="acoes">
                            <a class="botao botao-pequeno botao-contorno" href="produtos.php?editar=<?= (int) $p["id"] ?>">Editar<span class="so-leitor"> <?= e($p["nome"]) ?></span></a>

                            <form method="post" data-confirmar="Excluir o produto <?= e($p["nome"]) ?>?">
                                <?= csrf_campo() ?>
                                <input type="hidden" name="acao" value="excluir">
                                <input type="hidden" name="id" value="<?= (int) $p["id"] ?>">
                                <button type="submit" class="botao botao-pequeno botao-perigo">Excluir<span class="so-leitor"> <?= e($p["nome"]) ?></span></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require_once "../includes/footer.php"; ?>
