# 🃏 Mentes Ousadas - Jogo Multiplayer de Cartas em Tempo Real

Plataforma web multiplayer inspirada na mecânica de *Cards Against Humanity*, com identidade visual sofisticada, cartas originais em português, suporte a múltiplos celulares conectados simultaneamente e tela principal para projeção em Computadores, Notebooks ou TVs.

---

## 🛠️ Tecnologias Utilizadas
- **Backend:** PHP 8.3+ / Laravel 11
- **Tempo Real:** Laravel Reverb + Laravel Echo + WebSockets
- **Banco de Dados:** MySQL
- **Frontend:** Blade, Tailwind CSS, JavaScript Vanilla
- **QR Code:** BaconQrCode (SVG vetorial nativo em PHP)

---

## 🚀 Como Iniciar o Jogo

Na pasta do projeto (`cards-game`), execute o script de inicialização única:

```bash
./start-game.sh
```

Esse script inicializa automaticamente:
1. **Laravel Reverb (WebSockets)** na porta `8085`
2. **Laravel HTTP Server** na porta `8000`

---

## 🎮 Como Jogar

1. Abra no navegador do computador/TV:
   ```
   http://localhost:8000
   ```
2. Clique em **"Criar Nova Partida"**.
3. A **Mesa Principal** será aberta exibindo o código da sala e o **QR Code**.
4. Cada participante pega seu celular conectado na mesma rede Wi-Fi e aponta a câmera para o QR Code (ou acessa a URL informada).
5. Cada um digita seu apelido e entra na sala.
6. Quando houver pelo menos 3 participantes, o organizador clica em **"Iniciar Partida"**.

---

## 🃏 Dinâmica das Rodadas

- **Juiz da Rodada:** A cada rodada, o papel de Juiz alterna automaticamente entre os jogadores.
- **Cartas Pretas:** Podem exigir 1, 2 ou 3 cartas brancas (detectadas automaticamente a partir das lacunas `______`).
- **Mão do Jogador:** Cada jogador recebe 10 cartas brancas. Suas cartas são 100% confidenciais e nunca visíveis para outros jogadores.
- **Seleção e Ordem:** O jogador clica nas cartas desejadas e badges numeradas (1ª, 2ª, 3ª) indicam a ordem da resposta.
- **Julgamento:** As cartas são embaralhadas e anonimizadas. O Juiz escolhe a combinação vencedora pelo próprio celular.
- **Pontuação e Reposição:** O autor da resposta vencedora recebe 1 ponto, as cartas jogadas são repostas até 10 cartas e a próxima rodada é preparada.
- **Vitória:** O primeiro participante a atingir a meta de pontos (padrão: 5 pontos) é consagrado campeão.

---

## 🛠️ Painel Administrativo de Baralhos
Acesse `http://localhost:8000/admin/decks` para:
- Cadastrar novas cartas pretas (com detecção automática de lacunas).
- Cadastrar novas cartas brancas.
- Visualizar e excluir cartas existentes.

---

## 🧪 Testes Automatizados
Para rodar a suíte de testes ponta a ponta do ciclo multiplayer:
```bash
php artisan test --filter=MultiplayerGameFlowTest
```
