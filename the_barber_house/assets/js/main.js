// main.js
// JavaScript usado em todas as páginas do site:
//   1) menu do celular
//   2) pergunta "tem certeza?" antes de excluir/cancelar
//   3) aviso quando a data escolhida cai num dia fechado
//   4) chat de suporte (cliente e equipe)


// ---------- 1) Menu do celular ----------
// No celular o menu fica escondido. O botão "Menu" abre e fecha.
var botaoMenu = document.querySelector(".menu-botao");
var menu = document.getElementById("menu-principal");

if (botaoMenu && menu) {
    botaoMenu.addEventListener("click", function () {
        // liga ou desliga a classe "aberto" (o CSS mostra o menu quando ela existe)
        menu.classList.toggle("aberto");

        // aria-expanded avisa o leitor de tela se o menu está aberto
        if (menu.classList.contains("aberto")) {
            botaoMenu.setAttribute("aria-expanded", "true");
        } else {
            botaoMenu.setAttribute("aria-expanded", "false");
        }
    });
}


// ---------- 2) Confirmação antes de enviar ----------
// Os formulários que têm o atributo data-confirmar mostram uma pergunta antes de enviar.
var formsComConfirmacao = document.querySelectorAll("form[data-confirmar]");

for (var i = 0; i < formsComConfirmacao.length; i++) {
    formsComConfirmacao[i].addEventListener("submit", function (evento) {
        // this = o formulário que foi enviado
        var pergunta = this.getAttribute("data-confirmar");

        // se a pessoa clicar em "Cancelar", o envio é interrompido
        if (!confirm(pergunta)) {
            evento.preventDefault();
        }
    });
}


// ---------- 3) Aviso de dia fechado ----------
// Se a data cair no domingo ou na segunda, mostra um aviso na hora.
// (O PHP também confere isso na hora de salvar.)
var campoData = document.getElementById("data");
var avisoData = document.getElementById("aviso-data");

if (campoData && avisoData) {
    campoData.addEventListener("change", function () {
        if (campoData.value == "") {
            avisoData.textContent = "";
            return;
        }

        // a data vem no formato AAAA-MM-DD, então separamos nos hífens
        var partes = campoData.value.split("-");
        var ano = Number(partes[0]);
        var mes = Number(partes[1]) - 1;      // em JavaScript janeiro é o mês 0
        var dia = Number(partes[2]);

        var diaDaSemana = new Date(ano, mes, dia).getDay();   // 0 = domingo, 1 = segunda

        if (diaDaSemana == 0 || diaDaSemana == 1) {
            avisoData.textContent = "Nesse dia a barbearia fecha. Escolha uma data de terça a sábado.";
        } else {
            avisoData.textContent = "";
        }
    });
}


// ---------- 4) Chat de suporte ----------
// O mesmo código serve para o cliente e para a equipe.
// A cada 4 segundos ele pergunta ao servidor se tem mensagem nova (sem WebSocket).

var chat = document.getElementById("chat-suporte");   // só existe nas páginas que têm chat
var ultimoId = 0;                                     // id da última mensagem que já foi mostrada

// Desenha uma mensagem na tela
function mostrarMensagem(m) {
    var caixa = document.getElementById("chat-mensagens");

    // tira o texto "Nenhuma mensagem ainda" quando chega a primeira mensagem
    var vazio = caixa.querySelector(".chat-vazio");
    if (vazio) {
        vazio.remove();
    }

    // minha mensagem fica à direita, a da outra pessoa fica à esquerda
    var bolha = document.createElement("div");
    if (m.remetente == chat.getAttribute("data-papel")) {
        bolha.className = "bolha minha";
    } else {
        bolha.className = "bolha deles";
    }

    // textContent coloca o texto sem interpretar HTML (mais seguro)
    var texto = document.createElement("p");
    texto.textContent = m.mensagem;

    var hora = document.createElement("span");
    if (m.remetente == "equipe") {
        hora.textContent = "Equipe - " + m.hora;
    } else {
        hora.textContent = "Cliente - " + m.hora;
    }

    bolha.appendChild(texto);
    bolha.appendChild(hora);
    caixa.appendChild(bolha);
}

// Pergunta ao servidor se existem mensagens novas
function buscarMensagens() {
    var url = chat.getAttribute("data-url-buscar") + "?depois=" + ultimoId;

    // a equipe também precisa dizer de qual cliente é a conversa
    if (chat.getAttribute("data-cliente")) {
        url = url + "&cliente_id=" + chat.getAttribute("data-cliente");
    }

    fetch(url)
        .then(function (resposta) {
            return resposta.json();          // transforma o JSON em objeto
        })
        .then(function (dados) {
            if (!dados.mensagens) {
                return;
            }

            var caixa = document.getElementById("chat-mensagens");
            for (var i = 0; i < dados.mensagens.length; i++) {
                var m = dados.mensagens[i];

                // só mostra se for mais nova que a última (evita mensagem repetida)
                if (m.id > ultimoId) {
                    mostrarMensagem(m);
                    ultimoId = m.id;
                }
            }

            // rola a conversa para a última mensagem
            caixa.scrollTop = caixa.scrollHeight;
        })
        .catch(function () {
            // se falhar (ex.: sem internet), tenta de novo na próxima vez
        });
}

// Envia a mensagem que a pessoa digitou
function enviarMensagem(evento) {
    evento.preventDefault();              // impede a página de recarregar

    var formulario = document.getElementById("chat-form");
    var campo = document.getElementById("chat-texto");
    var erro = document.getElementById("chat-erro");
    erro.textContent = "";

    // FormData pega todos os campos do formulário (inclusive o token CSRF)
    fetch(chat.getAttribute("data-url-enviar"), {
        method: "POST",
        body: new FormData(formulario)
    })
        .then(function (resposta) {
            return resposta.json();
        })
        .then(function (dados) {
            if (dados.erro) {
                erro.textContent = dados.erro;
                return;
            }
            campo.value = "";             // limpa o campo
            buscarMensagens();            // mostra a mensagem enviada
        })
        .catch(function () {
            erro.textContent = "Não foi possível enviar a mensagem. Confira sua internet.";
        });
}

// Liga tudo, mas só nas páginas que têm o chat
if (chat) {
    document.getElementById("chat-form").addEventListener("submit", enviarMensagem);
    buscarMensagens();                    // carrega as mensagens ao abrir a página
    setInterval(buscarMensagens, 4000);   // depois repete a cada 4 segundos (4000 ms)
}
