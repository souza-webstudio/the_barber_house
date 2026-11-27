<?php
// admin/servicos.php
// CRUD de serviços feito pelo administrador (RF15 a RF18):
//   C = cadastrar, R = listar, U = editar, D = excluir

$raiz = "../";
require_once "../includes/funcoes.php";
exigir_admin($raiz);

$categorias = array("Adulto", "Infantil", "Barba e Cuidados");
$erro = "";

// Valores que aparecem no formulário. Vazios = cadastro de um serviço novo.
$id        = "";
$nome      = "";
$descricao = "";
$preco     = "";
$categoria = "Adulto";
$imagem    = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    exigir_csrf();
    $acao = pegar_post("acao");      // "salvar" ou "excluir"

    // ---------- EXCLUIR ----------
    if ($acao == "excluir") {
        $id_excluir = (int) pegar_post("id");

        // Não pode apagar serviço que já tem agendamento, para não perder o histórico (RF18)
        $st = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE servico_id = ?");
        $st->execute(array($id_excluir));
        $quantidade = $st->fetchColumn();

        if ($quantidade > 0) {
            mensagem("erro", "Não dá para excluir: existem agendamentos vinculados a esse serviço.");
        } else {
            $st = $pdo->prepare("DELETE FROM servicos WHERE id = ?");
            $st->execute(array($id_excluir));
            mensagem("sucesso", "Serviço excluído.");
        }
        header("Location: servicos.php");
        exit;
    }

    // ---------- SALVAR (cadastrar ou editar) ----------
    if ($acao == "salvar") {
        $id        = pegar_post("id");
        $nome      = trim(pegar_post("nome"));
        $descricao = trim(pegar_post("descricao"));
        $preco     = trim(pegar_post("preco"));
        $categoria = pegar_post("categoria");
        $imagem    = trim(pegar_post("imagem"));

        // o usuário pode digitar 45,00 - troca a vírgula por ponto para o banco entender
        $valor = str_replace(",", ".", $preco);

        // valida os campos
        if ($nome == "" || mb_strlen($nome) > 120) {
            $erro = "Digite o nome do serviço (até 120 caracteres).";
        } elseif (!in_array($categoria, $categorias)) {
            $erro = "Escolha uma categoria da lista.";
        } elseif (!is_numeric($valor) || $valor < 0 || $valor > 99999) {
            $erro = "Digite um preço válido, por exemplo 45,00.";
        } else {
            // se o campo imagem estiver vazio, guarda NULL
            $imagem_salvar = null;
            if ($imagem != "") {
                $imagem_salvar = $imagem;
            }

            if ($id != "") {
                // tem id: é uma edição (UPDATE)
                $st = $pdo->prepare("UPDATE servicos SET nome = ?, descricao = ?, preco = ?, categoria = ?, imagem = ? WHERE id = ?");
                $st->execute(array($nome, $descricao, $valor, $categoria, $imagem_salvar, (int) $id));
                mensagem("sucesso", "Serviço atualizado.");
            } else {
                // sem id: é um cadastro novo (INSERT)
                $st = $pdo->prepare("INSERT INTO servicos (nome, descricao, preco, categoria, imagem) VALUES (?, ?, ?, ?, ?)");
                $st->execute(array($nome, $descricao, $valor, $categoria, $imagem_salvar));
                mensagem("sucesso", "Serviço cadastrado.");
            }
            header("Location: servicos.php");
            exit;
        }
    }
} elseif (isset($_GET["editar"])) {
    // ---------- Clicou em "Editar": carrega o serviço no formulário ----------
    $st = $pdo->prepare("SELECT * FROM servicos WHERE id = ?");
    $st->execute(array((int) pegar_get("editar")));
    $linha = $st->fetch();

    if ($linha) {
        $id        = $linha["id"];
        $nome      = $linha["nome"];
        $descricao = $linha["descricao"];
        $preco     = number_format($linha["preco"], 2, ",", "");
        $categoria = $linha["categoria"];
        $imagem    = $linha["imagem"];
    }
}

// ---------- Lista de serviços (a categoria é ENUM, então ordena na ordem do banco) ----------
$servicos = $pdo->query("SELECT * FROM servicos ORDER BY categoria, nome")->fetchAll();

$titulo_pagina = "Serviços";
$area_admin = true;
require_once "../includes/header.php";
?>
<section class="secao">
    <div class="container">
        <h1>Serviços</h1>

        <!-- Formulário de cadastro / edição -->
        <section class="cartao cartao-corpo" aria-labelledby="titulo-form">
            <h2 id="titulo-form"><?php if ($id != "") { echo "Editar serviço"; } else { echo "Novo serviço"; } ?></h2>

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
                <input type="text" id="preco" name="preco" value="<?= e($preco) ?>" inputmode="decimal" placeholder="45,00" required>

                <label for="categoria">Categoria</label>
                <select id="categoria" name="categoria" required>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= e($c) ?>" <?php if ($categoria == $c) { echo "selected"; } ?>><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="imagem">Imagem (opcional)</label>
                <input type="text" id="imagem" name="imagem" value="<?= e($imagem) ?>" maxlength="255" placeholder="degrade.jpg (nome do arquivo que está em assets/img)">

                <div class="botoes-linha">
                    <button type="submit" class="botao">Salvar serviço</button>
                    <?php if ($id != ""): ?>
                        <a class="botao botao-contorno" href="servicos.php">Cancelar edição</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <!-- Tabela com os serviços cadastrados -->
        <h2>Serviços cadastrados</h2>
        <div class="tabela-rolagem">
            <table>
                <thead><tr><th>Nome</th><th>Categoria</th><th>Preço</th><th>Ações</th></tr></thead>
                <tbody>
                <?php foreach ($servicos as $s): ?>
                    <tr>
                        <td><?= e($s["nome"]) ?></td>
                        <td><?= e($s["categoria"]) ?></td>
                        <td><?= preco($s["preco"]) ?></td>
                        <td class="acoes">
                            <a class="botao botao-pequeno botao-contorno" href="servicos.php?editar=<?= (int) $s["id"] ?>">Editar<span class="so-leitor"> <?= e($s["nome"]) ?></span></a>

                            <!-- data-confirmar: o main.js pergunta "tem certeza?" antes de enviar -->
                            <form method="post" data-confirmar="Excluir o serviço <?= e($s["nome"]) ?>?">
                                <?= csrf_campo() ?>
                                <input type="hidden" name="acao" value="excluir">
                                <input type="hidden" name="id" value="<?= (int) $s["id"] ?>">
                                <button type="submit" class="botao botao-pequeno botao-perigo">Excluir<span class="so-leitor"> <?= e($s["nome"]) ?></span></button>
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
