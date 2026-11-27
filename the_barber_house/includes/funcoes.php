<?php
// funcoes.php
// Funções e dados usados em várias páginas do site.
// Quase todas as páginas incluem este arquivo logo no começo.

// __DIR__ é a pasta onde este arquivo está (includes)
require_once __DIR__ . "/conexao.php";

// ---------- Dados da barbearia ----------
// Aparecem no rodapé, na página de contato e no assistente rápido
$endereco_barbearia = "Rua Conde de Bonfim, 480, Loja B - Tijuca, Rio de Janeiro - RJ";
$telefone_barbearia = "(21) 99418-3320";
$horario_barbearia  = "Terça a sábado, das 9h às 19h30";

// ---------- Sessão ----------
// HttpOnly: o JavaScript não consegue ler o cookie da sessão (mais segurança)
ini_set("session.cookie_httponly", 1);
session_start();


// ---------- Funções básicas ----------

// Protege o texto antes de mostrar na tela.
// Sem isso, alguém poderia digitar código HTML/JavaScript num campo e ele rodaria na página.
function e($texto) {
    return htmlspecialchars((string) $texto, ENT_QUOTES, "UTF-8");
}

// Pega um valor enviado por formulário (POST). Se não veio nada, devolve "".
function pegar_post($campo) {
    if (isset($_POST[$campo]) && is_string($_POST[$campo])) {
        return $_POST[$campo];
    }
    return "";
}

// Pega um valor que veio na URL (GET). Se não veio nada, devolve "".
function pegar_get($campo) {
    if (isset($_GET[$campo]) && is_string($_GET[$campo])) {
        return $_GET[$campo];
    }
    return "";
}


// ---------- Mensagens de sucesso e erro ----------
// A mensagem fica guardada na sessão e aparece uma vez só, na próxima página.

function mensagem($tipo, $texto) {
    $_SESSION["mensagem_tipo"]  = $tipo;    // "sucesso" ou "erro"
    $_SESSION["mensagem_texto"] = $texto;
}

function mostrar_mensagem() {
    if (isset($_SESSION["mensagem_texto"])) {
        $tipo = $_SESSION["mensagem_tipo"];

        // role="alert" faz o leitor de tela avisar o erro na hora
        $papel = "status";
        if ($tipo == "erro") {
            $papel = "alert";
        }

        echo '<div class="aviso aviso-' . e($tipo) . '" role="' . $papel . '">';
        echo e($_SESSION["mensagem_texto"]);
        echo '</div>';

        // apaga a mensagem para não aparecer de novo
        unset($_SESSION["mensagem_tipo"]);
        unset($_SESSION["mensagem_texto"]);
    }
}


// ---------- Segurança: token CSRF ----------
// Todo formulário que altera dados leva um código secreto (token).
// Assim, outro site não consegue enviar o formulário em nome do usuário.

function csrf_token() {
    if (!isset($_SESSION["csrf"])) {
        // cria um código aleatório e guarda na sessão
        $_SESSION["csrf"] = bin2hex(random_bytes(16));
    }
    return $_SESSION["csrf"];
}

// Gera o campo escondido que vai dentro de cada formulário
function csrf_campo() {
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

// Confere se o token enviado é igual ao da sessão
function csrf_valido() {
    return isset($_POST["csrf"]) && $_POST["csrf"] === csrf_token();
}

// Se o token estiver errado, para tudo com erro 403
function exigir_csrf() {
    if (!csrf_valido()) {
        http_response_code(403);
        die("Não foi possível confirmar a requisição (token de segurança inválido). Volte à página anterior, atualize e tente de novo.");
    }
}


// ---------- Login e permissões ----------

// Devolve os dados de quem está logado, ou null se ninguém está logado
function usuario_logado() {
    if (isset($_SESSION["usuario"])) {
        return $_SESSION["usuario"];
    }
    return null;
}

// Funcionário ou administrador?
function eh_equipe() {
    $u = usuario_logado();
    if ($u == null) {
        return false;
    }
    return $u["tipo"] == "funcionario" || $u["tipo"] == "admin";
}

// Administrador?
function eh_admin() {
    $u = usuario_logado();
    if ($u == null) {
        return false;
    }
    return $u["tipo"] == "admin";
}

// As funções "exigir" protegem as páginas.
// $raiz é o caminho até a pasta principal do site ("" ou "../").

// A página exige que a pessoa esteja logada
function exigir_login($raiz) {
    if (usuario_logado() == null) {
        mensagem("erro", "Entre na sua conta para continuar.");
        header("Location: " . $raiz . "auth/login.php");
        exit;
    }
}

// A página é só para clientes
function exigir_cliente($raiz) {
    exigir_login($raiz);
    $u = usuario_logado();
    if ($u["tipo"] != "cliente") {
        header("Location: " . $raiz . "admin/dashboard.php");
        exit;
    }
}

// A página é para funcionário ou administrador
function exigir_equipe($raiz) {
    exigir_login($raiz);
    if (!eh_equipe()) {
        mensagem("erro", "Você não tem permissão para acessar essa área.");
        header("Location: " . $raiz . "index.php");
        exit;
    }
}

// A página é só para o administrador
function exigir_admin($raiz) {
    exigir_login($raiz);
    if (!eh_admin()) {
        mensagem("erro", "Essa área é só para o administrador.");
        if (eh_equipe()) {
            header("Location: " . $raiz . "admin/dashboard.php");
        } else {
            header("Location: " . $raiz . "index.php");
        }
        exit;
    }
}


// ---------- Formatação de textos ----------

// 45 vira "R$ 45,00"
function preco($valor) {
    return "R$ " . number_format($valor, 2, ",", ".");
}

// "2026-11-25" vira "25/11/2026"
function data_br($data) {
    return date("d/m/Y", strtotime($data));
}

// Devolve o nome do dia da semana de uma data
function dia_semana($data) {
    $nomes = array("domingo", "segunda", "terça", "quarta", "quinta", "sexta", "sábado");
    $numero = date("w", strtotime($data));   // 0 = domingo ... 6 = sábado
    return $nomes[$numero];
}

// "Marcos Oliveira" vira "Marcos"
function primeiro_nome($nome) {
    $partes = explode(" ", $nome);
    return $partes[0];
}

// "Marcos Oliveira Souza" vira "Marcos O." (usado nos depoimentos)
function nome_curto($nome) {
    $partes = explode(" ", trim($nome));
    if (count($partes) < 2) {
        return $partes[0];
    }
    $inicial = mb_strtoupper(mb_substr($partes[1], 0, 1));
    return $partes[0] . " " . $inicial . ".";
}

// Texto bonito para cada status do agendamento
function status_texto($status) {
    $nomes = array(
        "pendente"   => "Pendente",
        "confirmado" => "Confirmado",
        "concluido"  => "Concluído",
        "cancelado"  => "Cancelado"
    );
    if (isset($nomes[$status])) {
        return $nomes[$status];
    }
    return $status;
}

// Texto da situação do estoque (RN25)
function estoque_texto($quantidade) {
    if ($quantidade <= 0) {
        return "Esgotado";
    }
    if ($quantidade <= 5) {
        return "Poucas unidades";
    }
    return "Em estoque";
}

// Classe do CSS para a cor do selo de estoque
function estoque_classe($quantidade) {
    if ($quantidade <= 0) {
        return "esgotado";
    }
    if ($quantidade <= 5) {
        return "pouco";
    }
    return "ok";
}

// Mostra a foto. Se não tiver foto, mostra um quadrado com a primeira letra do nome.
function imagem_ou_inicial($raiz, $arquivo, $nome, $classe) {
    if ($arquivo != "") {
        echo '<img class="' . $classe . '" src="' . $raiz . 'assets/img/' . e($arquivo) . '" alt="' . e($nome) . '" loading="lazy">';
    } else {
        $inicial = mb_strtoupper(mb_substr($nome, 0, 1));
        echo '<div class="sem-imagem ' . $classe . '" aria-hidden="true">' . e($inicial) . '</div>';
    }
}


// ---------- Busca no banco usada em mais de uma página ----------

// Devolve os serviços de uma categoria (Adulto, Infantil ou Barba e Cuidados)
function servicos_da_categoria($pdo, $categoria) {
    $st = $pdo->prepare("SELECT * FROM servicos WHERE categoria = ? ORDER BY preco");
    $st->execute(array($categoria));
    return $st->fetchAll();
}

// Conta quantos registros uma consulta devolve (usada no painel do admin)
function contar($pdo, $sql) {
    return $pdo->query($sql)->fetchColumn();
}


// ---------- Regras do agendamento ----------

// Horários de atendimento: de 30 em 30 minutos, das 09:00 às 19:30 (RN18)
function horarios_atendimento() {
    return array(
        "09:00", "09:30", "10:00", "10:30", "11:00", "11:30",
        "12:00", "12:30", "13:00", "13:30", "14:00", "14:30",
        "15:00", "15:30", "16:00", "16:30", "17:00", "17:30",
        "18:00", "18:30", "19:00", "19:30"
    );
}

// Confere se a data e o horário podem ser usados.
// Devolve o texto do erro, ou "" (vazio) se estiver tudo certo.
function validar_data_horario($data, $horario) {

    // 1) A data existe? O formato esperado é AAAA-MM-DD
    $partes = explode("-", $data);
    if (count($partes) != 3 || !checkdate((int) $partes[1], (int) $partes[2], (int) $partes[0])) {
        return "Escolha uma data válida.";
    }

    // 2) O horário está na lista de horários de atendimento?
    if (!in_array($horario, horarios_atendimento())) {
        return "Escolha um horário entre 09:00 e 19:30, de 30 em 30 minutos.";
    }

    // 3) A data já passou? (no formato AAAA-MM-DD dá para comparar como texto)
    if ($data < date("Y-m-d")) {
        return "Não dá para agendar em uma data que já passou.";
    }

    // 4) A barbearia abre nesse dia? (date("w"): 0 = domingo, 1 = segunda)
    $dia = date("w", strtotime($data));
    if ($dia == 0 || $dia == 1) {
        return "Não atendemos nesse dia. Funcionamos de terça a sábado, das 9h às 19h30.";
    }

    // 5) Se for hoje, o horário já passou?
    if ($data == date("Y-m-d") && $horario <= date("H:i")) {
        return "Esse horário de hoje já passou. Escolha um horário mais tarde.";
    }

    return "";
}

// Confere se o horário está ocupado (RN07, RN08, RN20).
// - Com barbeiro escolhido: ele não pode ter outro agendamento (não cancelado) no mesmo dia e hora.
// - Sem barbeiro: só está ocupado se TODOS os barbeiros já tiverem agendamento nesse horário.
// $ignorar_id serve para não contar o próprio agendamento quando ele está sendo reativado.
function horario_ocupado($pdo, $data, $horario, $profissional_id, $ignorar_id = 0) {

    // o campo horario do banco é TIME (ex.: 09:00:00)
    $hora = substr($horario, 0, 5) . ":00";

    if ($profissional_id) {
        // caso 1: barbeiro escolhido
        $sql = "SELECT COUNT(*) FROM agendamentos
                WHERE profissional_id = ? AND data_agendamento = ? AND horario = ?
                AND status <> 'cancelado' AND id <> ?";
        $st = $pdo->prepare($sql);
        $st->execute(array($profissional_id, $data, $hora, $ignorar_id));
        $quantidade = $st->fetchColumn();
        return $quantidade > 0;
    }

    // caso 2: sem preferência de barbeiro
    $total_barbeiros = $pdo->query("SELECT COUNT(*) FROM profissionais")->fetchColumn();

    $sql = "SELECT COUNT(*) FROM agendamentos
            WHERE data_agendamento = ? AND horario = ?
            AND status <> 'cancelado' AND id <> ?";
    $st = $pdo->prepare($sql);
    $st->execute(array($data, $hora, $ignorar_id));
    $quantidade = $st->fetchColumn();

    return $total_barbeiros > 0 && $quantidade >= $total_barbeiros;
}
