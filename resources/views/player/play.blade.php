@extends('layouts.app')

@section('title', 'Suas Cartas - ' . $player->nickname)

@section('content')
<div class="flex-1 flex flex-col max-w-xl w-full mx-auto p-4 select-none pb-32" id="player-app">
    <!-- Topo Apple HIG: Barra de Status Minimalista -->
    <header class="apple-glass rounded-2xl px-5 py-3.5 flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-appleAccent/20 border border-appleAccent/40 flex items-center justify-center font-bold text-appleAccent text-sm">
                {{ substr($player->nickname, 0, 1) }}
            </div>
            <div>
                <h2 class="font-bold text-white text-sm leading-tight">{{ $player->nickname }}</h2>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-appleGreen animate-pulse"></span>
                    <span class="text-[11px] text-white/50 font-mono">Sala: {{ $game->code }}</span>
                </div>
            </div>
        </div>

        <div class="text-right">
            <span class="text-[10px] font-semibold text-white/40 uppercase tracking-widest block">Pontos</span>
            <span class="font-mono font-bold text-lg text-white" id="player-score">{{ $player->score }}</span>
        </div>
    </header>

    <!-- ETAPA 1: LOBBY DE ESPERA -->
    <div id="waiting-lobby-alert" class="apple-glass-card rounded-[28px] p-8 text-center space-y-3 mb-4 animate-apple-spring">
        <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center mx-auto text-appleAccent">
            <i data-lucide="clock" class="w-6 h-6 animate-spin"></i>
        </div>
        <h3 class="text-xl font-bold text-white">Aguardando a Rodada</h3>
        <p class="text-xs text-white/60 leading-relaxed max-w-xs mx-auto">
            O organizador iniciará a partida na tela principal assim que todos estiverem prontos.
        </p>
    </div>

    <!-- CARTA PRETA ATUAL (Apple Black Card) -->
    <div id="active-black-card-box" class="hidden apple-black-card p-6 mb-4 animate-apple-spring">
        <div class="flex items-center justify-between text-[11px] font-semibold text-white/50 uppercase tracking-wider mb-2">
            <span id="player-round-badge" class="px-2.5 py-0.5 rounded-full bg-white/10 text-white">RODADA 1</span>
            <span id="player-pick-requirement" class="font-mono text-appleAccent font-bold">SELECIONE 1 CARTA</span>
        </div>
        <p id="player-black-text" class="text-lg md:text-xl font-bold text-white leading-snug">
            Carregando pergunta...
        </p>
    </div>

    <!-- ETAPA 2: MÃO DE CARTAS BRANCAS DO JOGADOR -->
    <div id="hand-section" class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <h3 class="font-semibold text-white/70 text-xs uppercase tracking-wider flex items-center gap-1.5">
                <i data-lucide="layers" class="w-3.5 h-3.5 text-appleAccent"></i>
                Sua Mão (<span id="hand-count">0</span>)
            </h3>
            <span id="selection-status" class="text-xs font-mono text-white/50">0 selecionadas</span>
        </div>

        <div id="hand-cards-grid" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <!-- Cartas geradas via JS -->
        </div>
    </div>

    <!-- ETAPA 3: VOTAÇÃO COLETIVA APPLE HIG -->
    <div id="voting-section" class="hidden space-y-4 mb-6 animate-apple-spring">
        <div class="apple-glass rounded-2xl p-4 text-center border-appleYellow/30">
            <span class="text-2xl inline-block mb-1">🗳️</span>
            <h3 class="text-base font-bold text-appleYellow">Hora da Votação!</h3>
            <p class="text-xs text-white/60">Toque na resposta que achar mais engraçada:</p>
        </div>

        <div id="voting-submissions-list" class="space-y-3">
            <!-- Opções anônimas para votação -->
        </div>
    </div>

    <!-- ETAPA 4: VENCEDOR & BOTÃO "PRÓXIMA RODADA" -->
    <div id="winner-round-section" class="hidden space-y-4 mb-6 animate-apple-spring">
        <div class="apple-glass-card rounded-[28px] p-6 text-center border-appleYellow/30 space-y-2">
            <span class="text-4xl inline-block mb-1">👑</span>
            <h3 class="text-lg font-bold text-white">Mais Votada da Rodada</h3>
            <p class="text-base font-semibold text-appleYellow" id="round-winner-player-label">Vencedor (+1 Ponto)</p>
            <div id="round-winner-cards-box" class="bg-white/10 p-4 rounded-2xl border border-white/10 text-sm font-semibold text-white mt-2"></div>
        </div>

        <!-- Botão Próxima Rodada (Apple Action Pill) -->
        <div class="apple-glass rounded-2xl p-4 text-center space-y-3">
            <p class="text-xs text-white/50">
                Progresso: <strong id="ready-status-text" class="text-appleAccent font-mono">0 / 0 prontos</strong>
            </p>
            <button id="btn-ready-next" onclick="markReadyNext()" 
                class="w-full bg-appleAccent hover:bg-appleAccent/90 active:scale-95 text-white font-bold py-3.5 rounded-2xl shadow-lg shadow-appleAccent/20 transition-all flex items-center justify-center gap-2 text-sm">
                <i data-lucide="arrow-right-circle" class="w-4 h-4"></i>
                <span id="btn-ready-text">Próxima Rodada</span>
            </button>
        </div>
    </div>

    <!-- BARRA INFERIOR FIXA APPLE DOCK -->
    <div id="action-bar-bottom" class="fixed bottom-0 left-0 right-0 p-4 pb-6 apple-glass border-t border-white/10 max-w-xl mx-auto flex items-center gap-3">
        <button id="btn-submit-cards" onclick="submitSelectedCards()" disabled
            class="w-full bg-white/10 text-white/40 font-bold py-4 rounded-2xl transition-all flex items-center justify-center gap-2 text-sm disabled:cursor-not-allowed">
            <i data-lucide="send" class="w-4 h-4"></i>
            <span id="btn-submit-text">Selecione suas cartas</span>
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const playerToken = "{{ $player->session_token }}";
    const gameCode = "{{ $game->code }}";

    let state = {
        player: null,
        currentRound: null,
        selectedCardIds: [],
        pickCount: 1,
    };

    function renderPlayer(data) {
        if (!data) return;
        state.player = data.player;
        state.currentRound = data.current_round;

        document.getElementById('player-score').innerText = state.player.score;

        const waitingLobby = document.getElementById('waiting-lobby-alert');
        const activeBlackBox = document.getElementById('active-black-card-box');
        const handSection = document.getElementById('hand-section');
        const votingSection = document.getElementById('voting-section');
        const winnerSection = document.getElementById('winner-round-section');
        const bottomBar = document.getElementById('action-bar-bottom');

        if (!state.currentRound) {
            waitingLobby.classList.remove('hidden');
            activeBlackBox.classList.add('hidden');
            handSection.classList.remove('hidden');
            votingSection.classList.add('hidden');
            winnerSection.classList.add('hidden');
            bottomBar.classList.add('hidden');
            renderHand(state.player.hand || [], false);
            return;
        }

        waitingLobby.classList.add('hidden');
        activeBlackBox.classList.remove('hidden');
        const r = state.currentRound;
        state.pickCount = r.black_card.pick_count;

        document.getElementById('player-round-badge').innerText = `RODADA ${r.round_number}`;
        document.getElementById('player-pick-requirement').innerText = `SELECIONE ${state.pickCount} CARTA(S)`;
        document.getElementById('player-black-text').innerText = r.black_card.text;

        // FASE 1: SUBMISSÃO
        if (r.status === 'submitting') {
            votingSection.classList.add('hidden');
            winnerSection.classList.add('hidden');
            handSection.classList.remove('hidden');
            bottomBar.classList.remove('hidden');

            if (r.has_submitted) {
                const btn = document.getElementById('btn-submit-cards');
                btn.disabled = true;
                btn.className = "w-full bg-appleGreen/15 text-appleGreen border border-appleGreen/30 font-bold py-4 rounded-2xl flex items-center justify-center gap-2 text-sm";
                document.getElementById('btn-submit-text').innerText = "Resposta Enviada! Aguardando os demais...";
                renderHand(state.player.hand || [], false);
            } else {
                renderHand(state.player.hand || [], true);
                updateSubmitButton();
            }
        } 
        // FASE 2: VOTAÇÃO
        else if (r.status === 'judging') {
            handSection.classList.add('hidden');
            bottomBar.classList.add('hidden');
            winnerSection.classList.add('hidden');
            votingSection.classList.remove('hidden');

            renderVotingOptions(r.anonymized_submissions, r.has_voted);
        } 
        // FASE 3: RESULTADO
        else if (r.status === 'finished') {
            handSection.classList.add('hidden');
            bottomBar.classList.add('hidden');
            votingSection.classList.add('hidden');
            winnerSection.classList.remove('hidden');

            document.getElementById('ready-status-text').innerText = `${r.ready_count} / ${r.total_players} prontos`;
            
            const btnReady = document.getElementById('btn-ready-next');
            if (r.is_ready_next) {
                btnReady.disabled = true;
                btnReady.className = "w-full bg-white/10 text-white/40 font-bold py-3.5 rounded-2xl text-sm";
                document.getElementById('btn-ready-text').innerText = "Aguardando os outros jogadores...";
            } else {
                btnReady.disabled = false;
                btnReady.className = "w-full bg-appleAccent hover:bg-appleAccent/90 active:scale-95 text-white font-bold py-3.5 rounded-2xl shadow-lg transition-all text-sm";
                document.getElementById('btn-ready-text').innerText = "Próxima Rodada";
            }
        }
    }

    function renderHand(cards, canSelect) {
        const grid = document.getElementById('hand-cards-grid');
        document.getElementById('hand-count').innerText = cards.length;

        grid.innerHTML = cards.map(c => {
            const selectedIndex = state.selectedCardIds.indexOf(c.id);
            const isSelected = selectedIndex !== -1;
            const badgeNumber = isSelected ? selectedIndex + 1 : '';

            return `
                <div onclick="${canSelect ? `toggleCard(${c.id})` : ''}" 
                    class="apple-white-card p-4 min-h-[110px] flex flex-col justify-between cursor-pointer select-none ${isSelected ? 'card-selected-apple' : ''}">
                    <p class="font-bold text-sm leading-snug">${c.text}</p>
                    <div class="flex items-center justify-between text-[10px] font-mono font-semibold text-black/40 pt-2 border-t border-black/5">
                        <span>MENTES OUSADAS</span>
                        ${isSelected ? `<span class="bg-appleAccent text-white font-bold w-5 h-5 rounded-full flex items-center justify-center text-xs shadow-md">${badgeNumber}</span>` : ''}
                    </div>
                </div>
            `;
        }).join('');
    }

    function toggleCard(cardId) {
        const index = state.selectedCardIds.indexOf(cardId);
        if (index !== -1) {
            state.selectedCardIds.splice(index, 1);
        } else {
            if (state.selectedCardIds.length >= state.pickCount) {
                state.selectedCardIds.shift();
            }
            state.selectedCardIds.push(cardId);
        }

        renderHand(state.player.hand || [], true);
        updateSubmitButton();
    }

    function updateSubmitButton() {
        const btn = document.getElementById('btn-submit-cards');
        const count = state.selectedCardIds.length;
        const required = state.pickCount;

        document.getElementById('selection-status').innerText = `${count} de ${required} selecionada(s)`;

        if (count === required) {
            btn.disabled = false;
            btn.className = "w-full bg-white hover:bg-white/90 text-black font-bold py-4 rounded-2xl shadow-xl active:scale-95 transition-all flex items-center justify-center gap-2 text-sm";
            document.getElementById('btn-submit-text').innerText = `Enviar Resposta (${count}/${required})`;
        } else {
            btn.disabled = true;
            btn.className = "w-full bg-white/10 text-white/40 font-bold py-4 rounded-2xl transition-all flex items-center justify-center gap-2 text-sm opacity-60";
            document.getElementById('btn-submit-text').innerText = `Selecione mais ${required - count} carta(s)`;
        }
    }

    function renderVotingOptions(submissions, hasVoted) {
        const container = document.getElementById('voting-submissions-list');
        if (!submissions || submissions.length === 0) {
            container.innerHTML = '<p class="text-xs text-white/40 text-center">Nenhuma resposta submetida.</p>';
            return;
        }

        container.innerHTML = submissions.map(sub => {
            const cardsHtml = sub.cards.map(c => `
                <div class="apple-white-card p-3.5 font-bold text-sm mb-2">
                    ${c.text}
                </div>
            `).join('');

            let actionButton = '';
            // Se estiver jogando sozinho (1 jogador), permite votar na sua própria resposta para testar o fluxo completo
            const isSoloPlayer = (state.currentRound && state.currentRound.total_players <= 1);

            if (sub.is_own && !isSoloPlayer) {
                actionButton = `
                    <div class="text-center py-2 text-xs font-semibold text-white/40 bg-white/5 rounded-xl">
                        Sua resposta (você não pode votar nela)
                    </div>
                `;
            } else if (hasVoted) {
                actionButton = `
                    <div class="text-center py-2 text-xs font-semibold text-appleGreen bg-appleGreen/10 rounded-xl">
                        Voto computado!
                    </div>
                `;
            } else {
                actionButton = `
                    <button onclick="castVote(${sub.submission_id})" class="w-full bg-appleYellow hover:bg-appleYellow/90 active:scale-95 text-black font-bold py-3 rounded-xl text-xs uppercase tracking-wider transition-all flex items-center justify-center gap-1.5 shadow-lg">
                        <i data-lucide="thumbs-up" class="w-3.5 h-3.5"></i>
                        Votar Nesta Resposta
                    </button>
                `;
            }

            return `
                <div class="apple-glass rounded-2xl p-4 transition-all space-y-2 border-white/10">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-mono font-bold text-appleYellow bg-appleYellow/10 px-2.5 py-0.5 rounded-full">
                            Opção #${sub.anonymous_number}
                        </span>
                    </div>
                    <div>
                        ${cardsHtml}
                    </div>
                    ${actionButton}
                </div>
            `;
        }).join('');
        if (window.lucide) window.lucide.createIcons();
    }

    async function submitSelectedCards() {
        if (state.selectedCardIds.length !== state.pickCount) return;
        const btn = document.getElementById('btn-submit-cards');
        btn.disabled = true;
        btn.innerText = 'Enviando...';

        try {
            const res = await fetch(`/api/players/${playerToken}/submit`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    round_id: state.currentRound.id,
                    card_ids: state.selectedCardIds
                })
            });

            const data = await res.json();
            if (data.success) {
                state.selectedCardIds = [];
                syncPlayer();
            } else {
                alert(data.message || 'Erro ao enviar cartas');
                btn.disabled = false;
            }
        } catch (e) {
            console.error(e);
            btn.disabled = false;
        }
    }

    async function castVote(submissionId) {
        try {
            const res = await fetch(`/api/players/${playerToken}/vote`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    round_id: state.currentRound.id,
                    submission_id: submissionId
                })
            });

            const data = await res.json();
            if (data.success) {
                syncPlayer();
            } else {
                alert(data.message || 'Erro ao votar');
            }
        } catch (e) {
            console.error(e);
        }
    }

    async function markReadyNext() {
        try {
            const res = await fetch(`/api/players/${playerToken}/ready-next`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    round_id: state.currentRound.id
                })
            });

            const data = await res.json();
            if (data.success) syncPlayer();
        } catch (e) {
            console.error(e);
        }
    }

    async function syncPlayer() {
        try {
            const res = await fetch(`/api/players/${playerToken}/sync`);
            const data = await res.json();
            renderPlayer(data);
        } catch (e) {
            console.error(e);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        syncPlayer();

        if (window.Echo) {
            window.Echo.channel(`game.${gameCode}`)
                .listen('RoundStartedEvent', () => {
                    state.selectedCardIds = [];
                    syncPlayer();
                })
                .listen('JudgingPhaseStartedEvent', () => syncPlayer())
                .listen('VoteReceivedEvent', () => syncPlayer())
                .listen('RoundWinnerAnnouncedEvent', (e) => {
                    document.getElementById('round-winner-player-label').innerText = `${e.winner.nickname} (+1 Ponto)`;
                    document.getElementById('round-winner-cards-box').innerHTML = e.winning_cards.join(' <span class="text-appleAccent">+</span> ');
                    syncPlayer();
                })
                .listen('NextRoundReadyEvent', (e) => {
                    document.getElementById('ready-status-text').innerText = `${e.ready_count} / ${e.total_players} prontos`;
                });

            window.Echo.channel(`player.${playerToken}`)
                .listen('PlayerHandUpdatedEvent', (e) => {
                    if (state.player) {
                        state.player.hand = e.cards;
                        state.player.score = e.score;
                        renderHand(e.cards, state.currentRound && state.currentRound.status === 'submitting' && !state.currentRound.has_submitted);
                    }
                });
        }
    });
</script>
@endpush
