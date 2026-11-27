<?php
// admin/profissionais.php
// CRUD de profissionais (barbeiros) feito pelo administrador (RF30 a RF33):
//   C = cadastrar, R = listar, U = editar, D = excluir

$raiz = "../";
require_once "../includes/funcoes.php";
exigir_admin($raiz);

$erro = "";

// Valores que aparecem no formulário. Vazios = cadastro de um profissional novo.
$id            = "";
$nome          = "";
$especialidade = "";
$bio           = "";
$imagem        = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    exigir_csrf();
    $acao = pegar_post("acao");      // "salvar" ou "excluir"

    // ---------- EXCLUIR ----------
    if ($acao == "excluir") {
        // Os agendamentos dele continuam no sistema, só ficam sem barbeiro definido.
        // Quem faz isso é o banco de dados (ON DELETE SET NULL, regra RN14).
        $st = $pdo->prepare("DELETE FROM profissionais WHERE id = ?");
        $st->execute(array((int) pegar_post("id")));
        mensagem("sucesso", "Profissional excluído. Os agendamentos dele continuam, sem barbeiro definido.");
        header("Location: profissionais.php");
        exit;
    }

    // ---------- SALVAR (cadastrar ou editar) ----------
    if ($acao == "salvar") {
        $id            = pegar_post("id");
        $nome          = trim(pegar_post("nome"));
        $especialidade = trim(pegar_post("especialidade"));
        $bio           = trim(pegar_post("bio"));
        $imagem        = trim(pegar_post("imagem"));

        // valida os campos
        if ($nome == "" || mb_strlen($nome) > 120) {
            $erro = "Digite o nome do profissional (até 120 caracteres).";
        } elseif ($especialidade == "" || mb_strlen($especialidade) > 150) {
            $erro = "Digite a especialidade (até 150 caracteres).";
        } else {
            $imagem_salvar = null;
            if ($imagem != "") {
                $imagem_salvar = $imagem;
            }

            if ($id != "") {
                // tem id: edição (UPDATE)
                $st = $pdo->prepare("UPDATE profissionais SET nome = ?, especialidade = ?, bio = ?, imagem = ? WHERE id = ?");
                $st->execute(array($nome, $especialidade, $bio, $imagem_salvar, (int) $id));
                mensagem("sucesso", "Profissional atualizado.");
            } else {
                // sem id: cadastro novo (INSERT)
                $st = $pdo->prepare("INSERT INTO profissionais (nome, especialidade, bio, imagem) VALUES (?, ?, ?, ?)");
                $st->execute(array($nome, $especialidade, $bio, $imagem_salvar));
                mensagem("sucesso", "Profissional cadastrado.");
            }
            header("Location: profissionais.php");
            exit;
        }
    }
} elseif (isset($_GET["editar"])) {
    // ---------- Clicou em "Editar": carrega o profissional no formulário ----------
    $st = $pdo->prepare("SELECT * FROM profissionais WHERE id = ?");
    $st->execute(array((int) pegar_get("editar")));
    $linha = $st->fetch();

    if ($linha) {
        $id            = $linha["id"];
        $nome          = $linha["nome"];
        $especialidade = $linha["especialidade"];
        $bio           = $linha["bio"];
        $imagem        = $linha["imagem"];
    }
}

$profissionais = $pdo->query("SELECT * FROM profissionais ORDER BY nome")->fetchAll();

$titulo_pagina = "Profissionais";
$area_admin = true;
require_once "../includes/header.php";
?>
<section class="secao">
    <div class="container">
        <h1>Profissionais</h1>

        <!-- Formulário de cadastro / edição -->
        <section class="cartao cartao-corpo" aria-labelledby="titulo-form">
            <h2 id="titulo-form"><?php if ($id != "") { echo "Editar profissional"; } else { echo "Novo profissional"; } ?></h2>

            <?php if ($erro != ""): ?>
                <div class="aviso aviso-erro" role="alert"><?= e($erro) ?></div>
            <?php endif; ?>

            <form method="post" class="formulario formulario-largo">
                <?= csrf_campo() ?>
                <input type="hidden" name="acao" value="salvar">
                <input type="hidden" name="id" value="<?= e($id) ?>">

                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" value="<?= e($nome) ?>" maxlength="120" required>

                <label for="especialidade">Especialidade</label>
                <input type="text" id="especialidade" name="especialidade" value="<?= e($especialidade) ?>" maxlength="150" placeholder="Ex.: Degradê e navalha" required>

                <label for="bio">Biografia</label>
                <textarea id="bio" name="bio" rows="4"><?= e($bio) ?></textarea>

                <label for="imagem">Foto (opcional)</label>
                <input type="text" id="imagem" name="imagem" value="<?= e($imagem) ?>" maxlength="255" placeholder="bruno.jpg (nome do arquivo que está em assets/img)">

                <div class="botoes-linha">
                    <button type="submit" class="botao">Salvar profissional</button>
                    <?php if ($id != ""): ?>
                        <a class="botao botao-contorno" href="profissionais.php">Cancelar edição</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <!-- Tabela com a equipe cadastrada -->
        <h2>Equipe cadastrada</h2>
        <div class="tabela-rolagem">
            <table>
                <thead><tr><th>Nome</th><th>Especialidade</th><th>Ações</th></tr></thead>
                <tbody>
                <?php foreach ($profissionais as $p): ?>
                    <tr>
                        <td><?= e($p["nome"]) ?></td>
                        <td><?= e($p["especialidade"]) ?></td>
                        <td class="acoes">
                            <a class="botao botao-pequeno botao-contorno" href="profissionais.php?editar=<?= (int) $p["id"] ?>">Editar<span class="so-leitor"> <?= e($p["nome"]) ?></span></a>

                            <form method="post" data-confirmar="Excluir <?= e($p["nome"]) ?>? Os agendamentos dele ficam sem barbeiro definido.">
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
