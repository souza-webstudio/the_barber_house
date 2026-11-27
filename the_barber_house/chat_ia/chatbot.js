// chatbot.js
// Assistente rápido da barbearia (RF27).
// Ele responde por palavras-chave. NÃO usa inteligência artificial:
// o código procura palavras na pergunta e devolve uma resposta pronta.
//
// O HTML do assistente está no includes/footer.php.
// As variáveis raiz, servicos, enderecoBarbearia, telefoneBarbearia e horarioBarbearia
// também são criadas lá pelo PHP.

var botaoChatbot = document.getElementById("chatbot-botao");
var janelaChatbot = document.getElementById("chatbot-janela");
var mensagensChatbot = document.getElementById("chatbot-mensagens");
var formChatbot = document.getElementById("chatbot-form");
var campoChatbot = document.getElementById("chatbot-campo");
var botaoFechar = document.getElementById("chatbot-fechar");


// ---------- Funções que ajudam a entender a pergunta ----------

// Deixa o texto em minúsculas, sem acentos e sem pontuação.
// Assim "Preços?" e "preco" ficam iguais na hora de comparar.
function limpar(texto) {
    texto = texto.toLowerCase();
    texto = texto.replace(/[áàâãä]/g, "a");
    texto = texto.replace(/[éèêë]/g, "e");
    texto = texto.replace(/[íìîï]/g, "i");
    texto = texto.replace(/[óòôõö]/g, "o");
    texto = texto.replace(/[úùûü]/g, "u");
    texto = texto.replace(/ç/g, "c");
    texto = texto.replace(/[^a-z0-9 ]/g, " ");    // troca pontuação por espaço
    return " " + texto + " ";                     // espaços nas pontas ajudam na busca
}

// Diz se o texto tem alguma das palavras da lista
function contem(texto, palavras) {
    for (var i = 0; i < palavras.length; i++) {
        var palavra = palavras[i];

        if (palavra.length <= 4) {
            // palavra curta (oi, gel, fica) só vale se estiver sozinha
            if (texto.indexOf(" " + palavra + " ") != -1) {
                return true;
            }
        } else {
            if (texto.indexOf(palavra) != -1) {
                return true;
            }
        }
    }
    return false;
}

// 45 vira "R$ 45,00"
function reais(valor) {
    return "R$ " + Number(valor).toFixed(2).replace(".", ",");
}

// Monta o texto com os preços. Se passar uma categoria, mostra só ela.
function listaDePrecos(categoria) {
    var lista = [];
    for (var i = 0; i < servicos.length; i++) {
        if (categoria == "" || servicos[i].categoria == categoria) {
            lista.push(servicos[i].nome + ": " + reais(servicos[i].preco));
        }
    }
    return lista.join(" | ");
}


// ---------- As respostas ----------
// A ordem dos "if" importa: a primeira regra que combinar é a que responde.
// Cada resposta é um objeto com o texto e, se quiser, um link.

function responder(pergunta) {
    var texto = limpar(pergunta);

    if (contem(texto, ["crianca", "filho", "filha", "kids", "infantil", "menino", "bebe", "pequeno"])) {
        return {
            texto: "Cortamos cabelo de criança com bastante paciência. Ao agendar, use o campo \"Observações do corte\" para contar como seu filho gosta do cabelo ou se ele tem medo de máquina. Preços: " + listaDePrecos("Infantil") + ".",
            linkUrl: raiz + "cliente/painel.php#agendar",
            linkTexto: "Agendar horário"
        };
    }

    if (contem(texto, ["agendar", "marcar", "reservar", "agendamento"])) {
        return {
            texto: "É só criar sua conta, escolher o serviço, o dia e o horário no seu painel. O agendamento fica pendente até a equipe confirmar. Atendemos " + horarioBarbearia.toLowerCase() + ".",
            linkUrl: raiz + "cliente/painel.php#agendar",
            linkTexto: "Agendar horário"
        };
    }

    if (contem(texto, ["cancelar", "desmarcar"])) {
        return {
            texto: "Para cancelar, entre no seu painel e clique em \"Cancelar agendamento\" no horário que você marcou. Vale para agendamentos pendentes ou confirmados.",
            linkUrl: raiz + "cliente/painel.php#meus-agendamentos",
            linkTexto: "Meus agendamentos"
        };
    }

    if (contem(texto, ["preco", "valor", "quanto", "custa", "tabela", "cobra"])) {
        return {
            texto: "Nossos preços: " + listaDePrecos("") + ".",
            linkUrl: raiz + "index.php#servicos",
            linkTexto: "Ver serviços"
        };
    }

    if (contem(texto, ["horario", "funciona", "aberto", "abre", "fecha", "domingo", "segunda", "sabado", "expediente"])) {
        return {
            texto: "Atendemos " + horarioBarbearia.toLowerCase() + ", com horários de 30 em 30 minutos. Aos domingos e segundas a barbearia fica fechada.",
            linkUrl: "",
            linkTexto: ""
        };
    }

    if (contem(texto, ["endereco", "onde", "localizacao", "chegar", "fica", "local"])) {
        return {
            texto: "Estamos na " + enderecoBarbearia + ".",
            linkUrl: raiz + "contato.php",
            linkTexto: "Ver contato"
        };
    }

    if (contem(texto, ["barba", "navalha", "degrade", "corte", "cabelo"])) {
        return {
            texto: "Fazemos corte clássico, degradê, barba modelada e combos. Valores: " + listaDePrecos("") + ".",
            linkUrl: raiz + "index.php#servicos",
            linkTexto: "Ver serviços"
        };
    }

    if (contem(texto, ["produto", "pomada", "gel", "oleo", "creme", "estoque"])) {
        return {
            texto: "Temos pomada, gel, creme para pentear e óleo para barba. Na página inicial você vê o que tem em estoque. A compra é feita na barbearia.",
            linkUrl: raiz + "index.php#produtos",
            linkTexto: "Ver produtos"
        };
    }

    if (contem(texto, ["barbeiro", "profissional", "equipe", "quem atende"])) {
        return {
            texto: "Nossa equipe tem barbeiros especialistas em cortes clássicos, degradê e corte infantil. Você pode escolher o barbeiro ao agendar ou deixar sem preferência.",
            linkUrl: raiz + "index.php#equipe",
            linkTexto: "Conhecer a equipe"
        };
    }

    if (contem(texto, ["telefone", "contato", "whatsapp", "ligar", "falar", "atendente", "humano"])) {
        return {
            texto: "Você pode ligar para " + telefoneBarbearia + " ou falar com a equipe pelo chat de suporte, que fica no seu painel depois de entrar na conta.",
            linkUrl: raiz + "cliente/painel.php#suporte",
            linkTexto: "Chat com a equipe"
        };
    }

    if (contem(texto, ["obrigado", "obrigada", "valeu"])) {
        return { texto: "Por nada! Qualquer outra dúvida, é só chamar.", linkUrl: "", linkTexto: "" };
    }

    if (contem(texto, ["oi", "ola", "bom dia", "boa tarde", "boa noite"])) {
        return { texto: "Olá! Posso ajudar com horário, preços, corte infantil, endereço e como agendar.", linkUrl: "", linkTexto: "" };
    }

    // se nenhuma regra combinou
    return {
        texto: "Não entendi muito bem. Posso responder sobre horário, preços, corte infantil, endereço ou como agendar. Se preferir, fale com a equipe pelo chat de suporte.",
        linkUrl: raiz + "cliente/painel.php#suporte",
        linkTexto: "Chat com a equipe"
    };
}


// ---------- Mostrar as mensagens na janelinha ----------

// quem = "bot" ou "usuario" (muda a cor e o lado da mensagem)
function adicionarMensagem(quem, texto, linkUrl, linkTexto) {
    var bolha = document.createElement("div");
    bolha.className = "chatbot-bolha " + quem;

    var paragrafo = document.createElement("p");
    paragrafo.textContent = texto;
    bolha.appendChild(paragrafo);

    // se a resposta tem link, coloca embaixo do texto
    if (linkUrl != "" && linkUrl != undefined) {
        var link = document.createElement("a");
        link.href = linkUrl;
        link.textContent = linkTexto;
        bolha.appendChild(link);
    }

    mensagensChatbot.appendChild(bolha);
    mensagensChatbot.scrollTop = mensagensChatbot.scrollHeight;   // rola para o final
}

// Mostra a pergunta e a resposta do assistente
function perguntar(pergunta) {
    adicionarMensagem("usuario", pergunta, "", "");

    var resposta = responder(pergunta);
    adicionarMensagem("bot", resposta.texto, resposta.linkUrl, resposta.linkTexto);
}


// ---------- Abrir, fechar e enviar ----------

function abrirChatbot() {
    janelaChatbot.hidden = false;
    botaoChatbot.setAttribute("aria-expanded", "true");

    // na primeira vez que abre, o assistente se apresenta
    if (mensagensChatbot.children.length == 0) {
        adicionarMensagem("bot", "Olá! Sou o assistente da The Barber House. Pergunte sobre horário, preços, corte infantil ou endereço.", "", "");
    }
    campoChatbot.focus();
}

function fecharChatbot() {
    janelaChatbot.hidden = true;
    botaoChatbot.setAttribute("aria-expanded", "false");
    botaoChatbot.focus();
}

// Botão "Dúvidas?": abre se estiver fechado, fecha se estiver aberto
botaoChatbot.addEventListener("click", function () {
    if (janelaChatbot.hidden) {
        abrirChatbot();
    } else {
        fecharChatbot();
    }
});

botaoFechar.addEventListener("click", fecharChatbot);

// Botões de perguntas prontas (Horário, Preços...)
var atalhos = janelaChatbot.querySelectorAll("[data-pergunta]");
for (var i = 0; i < atalhos.length; i++) {
    atalhos[i].addEventListener("click", function () {
        perguntar(this.getAttribute("data-pergunta"));
    });
}

// Quando a pessoa digita uma pergunta e envia
formChatbot.addEventListener("submit", function (evento) {
    evento.preventDefault();

    var pergunta = campoChatbot.value.trim();
    if (pergunta != "") {
        perguntar(pergunta);
        campoChatbot.value = "";
    }
});
