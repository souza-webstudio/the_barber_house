# The Barber House

Projeto final do curso Técnico em Informática para Internet.
Empresa fictícia: **ByteCut Solutions** (Lucas Vitor, Késia Alves, Samuel Xavier e Kelton Bryan).

## Como rodar no XAMPP

1. Copie a pasta `the_barber_house` inteira para `C:\xampp\htdocs\`.
2. Abra o painel do XAMPP e ligue o **Apache** e o **MySQL**.
3. Abra `http://localhost/phpmyadmin`, vá na aba **Importar**, escolha o arquivo `sql/banco.sql` e clique em **Importar**.
   Isso cria o banco `the_barber_house` com as 7 tabelas e os dados de exemplo.
   (Importar de novo apaga tudo e recomeça do zero.)
4. Acesse `http://localhost/the_barber_house/`.

Os links do site são relativos, então a pasta pode ter outro nome ou ficar dentro de outra pasta.
Se o MySQL tiver senha no usuário root, ajuste `includes/conexao.php`.

### Preços promocionais (banco já existente)

Se você já tem o banco `the_barber_house` criado e quer preservar seus dados, **não importe `banco.sql` novamente**, pois esse arquivo recria as tabelas. Em vez disso, abra o phpMyAdmin, selecione o banco `the_barber_house`, entre na aba SQL e execute uma única vez o arquivo `sql/atualizar_precos_promocionais.sql`. Ele adiciona o campo de preço promocional e atualiza os quatro produtos de exemplo sem apagar os demais dados.

Os preços normais ficam guardados; o catálogo mostra o valor antigo riscado e o valor promocional. No painel administrativo, é possível alterar o preço promocional ou deixar o campo vazio para remover a oferta.


### Agendamento sem cadastro (banco já existente)

O site permite que visitantes agendem sem criar conta. Para atualizar um banco que já existe, faça backup e execute uma única vez `sql/atualizar_agendamento_visitante.sql` no phpMyAdmin. Esse script não apaga dados: apenas permite `usuario_id` nulo e adiciona nome/telefone do visitante. A página pública é `agendar.php`; os pedidos aparecem no painel administrativo em **Agendamentos**, identificados como visitantes.

## Contas de teste

| Perfil | E-mail | Senha |
|---|---|---|
| Administrador | admin@thebarberhouse.com | admin123 |
| Funcionário | funcionario@thebarberhouse.com | func123 |
| Cliente | marcos@email.com | cliente123 |
| Cliente | felipe@email.com | cliente123 |

Clientes novos se cadastram em `auth/cadastro.php`. Contas de funcionário e administrador são criadas alterando o campo `tipo` na tabela `usuarios`.

## Regras de agendamento

- Atendimento de terça a sábado, das 09:00 às 19:30, de 30 em 30 minutos.
- Não aceita datas passadas, domingo, segunda, horário fora da faixa ou horário de hoje que já passou.
- Com barbeiro escolhido: ele não pode ter outro agendamento ativo no mesmo dia e horário.
- Sem preferência: só recusa se todos os barbeiros já estiverem ocupados naquele horário.
- Agendamento cancelado libera o horário. Reativar só é permitido se não houver conflito.
- Serviço com agendamentos não pode ser excluído.

## Segurança

- PDO com prepared statements nas consultas com dados do usuário.
- Senhas com `password_hash()` e `password_verify()`.
- Cookie de sessão HttpOnly e novo ID de sessão a cada login.
- Token CSRF em todo formulário que grava, altera ou exclui dados, inclusive o chat.
- Saída protegida com `htmlspecialchars` (função `e()`).
- Erros inesperados mostram mensagem amigável e o detalhe vai para o log do servidor.

## Estrutura das pastas

```
/                index.php, agendar.php, sobre.php, contato.php
auth/            login.php, cadastro.php, logout.php
cliente/         painel.php, cancelar_agendamento.php, avaliar.php
admin/           dashboard, servicos, produtos, profissionais, agendamentos, avaliacoes, suporte
includes/        conexao.php, funcoes.php, header.php, footer.php
chat/            enviar_mensagem.php, buscar_mensagens.php
chat_ia/         chatbot.js (respostas por palavras-chave)
empresa/         index.php (site da ByteCut Solutions)
assets/          css/style.css, js/main.js, img/
sql/             banco.sql
```

## Como o código está organizado

- Cada página define `$raiz` no começo (`""` na pasta principal, `"../"` nas subpastas). Ela serve para montar os links e os caminhos dos arquivos.
- `includes/funcoes.php` guarda as funções usadas em várias páginas (login, CSRF, formatação, regras de horário).
- `includes/header.php` e `includes/footer.php` são a parte de cima e a de baixo de todas as páginas.
