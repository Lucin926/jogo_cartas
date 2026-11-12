@extends('layouts.app')

@section('title', 'Mesa: ' . $game->title . ' (' . $game->code . ')')

@section('content')
<div class="flex-1 flex flex-col p-4 md:p-8 max-w-[1500px] w-full mx-auto" id="host-app">
    <!-- Header Apple HIG (Glass Navigation Bar) -->
    <header class="apple-glass rounded-3xl p-4 md:px-8 md:py-5 flex flex-wrap items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-bold tracking-tight text-white flex items-center gap-3">
                    {{ $game->title }}
                    <span class="text-xs px-2.5 py-1 rounded-full bg-white/10 text-white/70 font-mono font-medium" id="header-rounds-badge">
                        {{ $game->total_rounds }} Rodadas
                    </span>
                </h1>
                <div class="flex items-center gap-2 mt-0.5">
                    <span class="w-2 h-2 rounded-full bg-appleGreen animate-pulse"></span>
                    <span class="text-xs text-white/50 font-medium">Sincronização em Tempo Real</span>
                </div>
            </div>
        </div>

        <!-- Código da Sala em Destaque Minimalista Apple -->
        <div class="flex items-center gap-6">
            <div class="text-right">
                <span class="text-[11px] font-semibold uppercase tracking-widest text-white/40 block">Código da Sala</span>
                <div class="text-2xl md:text-3xl font-mono font-bold text-white tracking-wider flex items-center gap-2">
                    <span id="room-code-display">{{ $game->code }}</span>
                    <button onclick="navigator.clipboard.writeText('{{ $game->code }}')" title="Copiar código" class="text-white/40 hover:text-white transition-colors p-1">
                        <i data-lucide="copy" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button id="btn-start-round" onclick="startRound()" class="bg-white hover:bg-white/90 active:scale-95 text-black font-bold px-6 py-3 rounded-2xl shadow-xl transition-all flex items-center gap-2 text-sm">
                    <i data-lucide="play" class="w-4 h-4 fill-black"></i>
                    <span id="btn-start-text">Iniciar Partida</span>
                </button>
                <button id="btn-force-judging" onclick="forceJudging()" class="hidden bg-appleYellow/20 border border-appleYellow/40 hover:bg-appleYellow/30 text-appleYellow font-bold px-5 py-3 rounded-2xl shadow-lg transition-all text-sm flex items-center gap-2">
                    <i data-lucide="fast-forward" class="w-4 h-4"></i>
                    Abrir Votação
                </button>
            </div>
        </div>
    </header>

    <!-- Grade Principal -->
    <div class="flex-1 grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Centro da Mesa -->
        <div class="lg:col-span-9 flex flex-col justify-center min-h-[500px]">

            <!-- LOBBY DE ESPERA (QR Code Apple Style) -->
            <div id="state-lobby" class="apple-glass-card rounded-[36px] p-8 md:p-14 flex flex-col md:flex-row items-center justify-center gap-12 relative overflow-hidden animate-apple-spring">
                <div class="bg-white p-6 rounded-[28px] shadow-2xl flex flex-col items-center justify-center">
                    <div class="w-56 h-56 flex items-center justify-center">
                        {!! $qrCodeSvg !!}
                    </div>
                    <span class="text-[11px] font-bold text-black/60 uppercase tracking-widest mt-4">Aponte a câmera do iPhone/Android</span>
                </div>

                <div class="max-w-md text-center md:text-left space-y-4">
                    <span class="px-3.5 py-1.5 rounded-full bg-appleAccent/15 text-appleAccent font-semibold text-xs tracking-wide">
                        Lobby da Partida
                    </span>
                    <h2 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight leading-tight">
                        Conecte-se para jogar.
                    </h2>
                    <p class="text-white/60 text-sm leading-relaxed">
                        Aponte a câmera do seu celular para o QR Code ao lado para entrar na sala.
                    </p>
                    <p class="text-white/40 text-xs">
                        Todos recebem a pergunta, todos enviam respostas e todos votam na melhor!
                    </p>
                </div>
            </div>

            <!-- RODADA ATIVA -->
            <div id="state-round" class="hidden flex-col gap-8 animate-apple-spring">
                <!-- Carta Preta Apple HIG -->
                <div class="flex justify-center">
                    <div id="main-black-card" class="apple-black-card p-8 md:p-14 max-w-2xl w-full min-h-[280px] flex flex-col justify-between relative">
                        <div class="flex items-center justify-between text-xs font-semibold text-white/50 uppercase tracking-wider mb-4">
                            <span id="round-number-badge" class="px-3 py-1 rounded-full bg-white/10 text-white">RODADA 1</span>
                            <span id="black-card-pick-badge" class="font-mono text-appleAccent font-bold">ESCOLHA 1 CARTA</span>
                        </div>
                        
                        <p id="black-card-text" class="text-2xl md:text-4xl font-bold leading-tight tracking-tight my-auto text-white">
                            Carregando carta...
                        </p>

                        <div class="flex items-center justify-between text-xs text-white/40 font-medium pt-6 border-t border-white/10 mt-6">
                            <span>MENTES OUSADAS</span>
                            <span class="text-white/60">Votação Coletiva</span>
                        </div>
                    </div>
                </div>

                <!-- Progresso de Respostas -->
                <div id="submissions-progress-box" class="apple-glass rounded-2xl p-6 text-center max-w-lg mx-auto w-full">
                    <div class="flex items-center justify-between text-sm text-white/60 mb-2">
                        <span>Respostas enviadas</span>
                        <span id="progress-text" class="font-mono font-bold text-white text-base">0 / 0</span>
                    </div>
                    <div class="w-full bg-white/10 rounded-full h-2.5 overflow-hidden">
                        <div id="progress-bar" class="bg-appleAccent h-full rounded-full transition-all duration-300 w-0"></div>
                    </div>
                    <p class="text-xs text-white/40 mt-2.5">Aguardando participantes enviarem suas cartas pelo celular...</p>
                </div>

                <!-- FASE DE VOTAÇÃO (Cartas Reveladas) -->
                <div id="judging-grid-container" class="hidden space-y-4">
                    <div class="text-center">
                        <span class="px-3.5 py-1 rounded-full bg-appleYellow/15 text-appleYellow font-semibold text-xs tracking-wide">
                            Votação Coletiva
                        </span>
                        <h3 class="text-2xl font-bold text-white mt-2">Escolham a melhor resposta no celular</h3>
                        <p class="text-xs font-mono text-white/50 mt-1" id="votes-counter-display">0 votos computados</p>
                    </div>

                    <div id="submissions-cards-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 pt-2">
                        <!-- Cartas geradas via JS -->
                    </div>
                </div>

                <!-- ANÚNCIO DO VENCEDOR -->
                <div id="round-winner-banner" class="hidden apple-glass-card rounded-[32px] p-8 md:p-12 text-center animate-apple-spring border-appleYellow/30 space-y-4">
                    <span class="text-4xl inline-block">👑</span>
                    <h3 class="text-2xl md:text-3xl font-extrabold text-white">Resposta Vencedora da Rodada</h3>
                    <p class="text-xl font-bold text-appleYellow" id="winner-player-name">Fulano (+1 Ponto)</p>
                    <div id="winner-cards-text" class="bg-white/10 max-w-xl mx-auto p-6 rounded-2xl border border-white/15 text-lg font-bold text-white shadow-xl"></div>
                    
                    <div class="pt-4 border-t border-white/10">
                        <p class="text-sm text-white/60" id="host-ready-status">
                            Aguardando jogadores apertarem "Próxima Rodada"...
                        </p>
                    </div>
                </div>

            </div>

            <!-- FIM DE JOGO -->
            <div id="state-game-finished" class="hidden text-center apple-glass-card rounded-[36px] p-12 space-y-6">
                <span class="text-6xl inline-block animate-bounce">🏆</span>
                <h2 class="text-4xl md:text-5xl font-extrabold text-white">Temos um Campeão!</h2>
                <p id="champion-announcement" class="text-2xl font-semibold text-applePink"></p>
                <button onclick="startRound()" class="bg-white hover:bg-white/90 text-black font-bold px-8 py-4 rounded-2xl text-base shadow-xl transition-all">
                    Iniciar Nova Partida
                </button>
            </div>

        </div>

        <!-- Painel Lateral de Jogadores Apple HIG -->
        <div class="lg:col-span-3 space-y-6">
            <div class="apple-glass rounded-3xl p-6">
                <div class="flex items-center justify-between pb-4 border-b border-white/10 mb-4">
                    <h3 class="font-bold text-white text-base flex items-center gap-2">
                        <i data-lucide="users" class="w-4 h-4 text-appleAccent"></i>
                        Participantes (<span id="players-count">0</span>)
                    </h3>
                </div>

                <div id="players-list" class="space-y-2 max-h-[500px] overflow-y-auto pr-1">
                    <div class="text-center py-8 text-white/40 text-sm">
                        Nenhum jogador conectado ainda.
                    </div>
                </div>
            </div>

            <div class="apple-glass rounded-3xl p-5 text-xs text-white/50 space-y-2">
                <p class="font-semibold text-white/80 uppercase tracking-wider">Como Jogar:</p>
                <p>1. Todos recebem a pergunta e escolhem suas respostas.</p>
                <p>2. Todos votam na mais engraçada pelo celular.</p>
                <p>3. A mais votada pontua e todos avançam juntos!</p>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    const gameCode = "{{ $game->code }}";

    function renderState(data) {
        if (!data || !data.game) return;
        const g = data.game;
        const r = data.current_round;

        const playersList = document.getElementById('players-list');
        const playersCount = document.getElementById('players-count');
        playersCount.innerText = g.players.length;

        if (g.players.length === 0) {
            playersList.innerHTML = `<div class="text-center py-8 text-white/40 text-sm">Nenhum jogador conectado ainda.</div>`;
        } else {
            playersList.innerHTML = g.players.map(p => `
                <div class="flex items-center justify-between p-3 rounded-2xl bg-white/5 border border-white/10 transition-all hover:bg-white/10">
                    <div class="flex items-center gap-2.5 truncate">
                        <span class="w-2 h-2 rounded-full ${p.is_connected ? 'bg-appleGreen' : 'bg-white/20'}"></span>
                        <span class="font-semibold text-sm text-white truncate">${p.nickname}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-bold text-sm text-appleAccent">${p.score} pts</span>
                        <button onclick="removePlayer(${p.id})" title="Remover jogador" class="text-white/30 hover:text-red-400 transition-colors p-1">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>
            `).join('');
            if (window.lucide) window.lucide.createIcons();
        }

        const stateLobby = document.getElementById('state-lobby');
        const stateRound = document.getElementById('state-round');
        const stateGameFinished = document.getElementById('state-game-finished');
        const btnStartRound = document.getElementById('btn-start-round');
        const btnStartText = document.getElementById('btn-start-text');
        const btnForceJudging = document.getElementById('btn-force-judging');

        if (g.status === 'finished') {
            stateLobby.classList.add('hidden');
            stateRound.classList.add('hidden');
            stateGameFinished.classList.remove('hidden');
            document.getElementById('champion-announcement').innerText = g.winner ? `${g.winner.nickname} venceu o jogo!` : 'Fim de jogo!';
            return;
        }

        if (!r) {
            stateLobby.classList.remove('hidden');
            stateRound.classList.add('hidden');
            stateGameFinished.classList.add('hidden');
            btnStartText.innerText = 'Iniciar Partida';
            btnForceJudging.classList.add('hidden');
        } else {
            stateLobby.classList.add('hidden');
            stateRound.classList.remove('hidden');
            stateGameFinished.classList.add('hidden');
            btnStartText.innerText = 'Forçar Próxima Rodada';

            document.getElementById('round-number-badge').innerText = `RODADA ${r.round_number} de ${g.total_rounds}`;
            document.getElementById('black-card-pick-badge').innerText = `ESCOLHA ${r.black_card.pick_count} CARTA(S)`;
            document.getElementById('black-card-text').innerText = r.black_card.text;

            const subBox = document.getElementById('submissions-progress-box');
            const judgingGrid = document.getElementById('judging-grid-container');
            const winnerBanner = document.getElementById('round-winner-banner');

            if (r.status === 'submitting') {
                subBox.classList.remove('hidden');
                judgingGrid.classList.add('hidden');
                winnerBanner.classList.add('hidden');
                btnForceJudging.classList.remove('hidden');

                const percent = r.eligible_count > 0 ? (r.submissions_count / r.eligible_count) * 100 : 0;
                document.getElementById('progress-text').innerText = `${r.submissions_count} / ${r.eligible_count}`;
                document.getElementById('progress-bar').style.width = `${percent}%`;
            } else if (r.status === 'judging') {
                subBox.classList.add('hidden');
                judgingGrid.classList.remove('hidden');
                winnerBanner.classList.add('hidden');
                btnForceJudging.classList.add('hidden');

                document.getElementById('votes-counter-display').innerText = `${r.votes_count} de ${r.eligible_count} jogadores votaram`;
                renderJudgingCards(r.anonymized_submissions);
            } else if (r.status === 'finished') {
                subBox.classList.add('hidden');
                judgingGrid.classList.add('hidden');
                winnerBanner.classList.remove('hidden');
                btnForceJudging.classList.add('hidden');

                document.getElementById('host-ready-status').innerText = `${r.ready_count} de ${r.eligible_count} jogadores prontos para a próxima rodada`;
            }
        }
    }

    function renderJudgingCards(submissions) {
        const grid = document.getElementById('submissions-cards-grid');
        if (!submissions || submissions.length === 0) {
            grid.innerHTML = '<p class="text-white/40 col-span-full text-center">Nenhuma resposta submetida.</p>';
            return;
        }

        grid.innerHTML = submissions.map(sub => {
            const cardsHtml = sub.cards.map(c => `
                <div class="apple-white-card p-4 font-bold text-sm">
                    ${c.text}
                </div>
            `).join('');

            return `
                <div class="apple-glass rounded-2xl p-4 space-y-3 border-white/10 hover:border-white/25 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-mono font-bold text-appleYellow bg-appleYellow/10 px-2.5 py-1 rounded-full">
                            Opção #${sub.anonymous_number}
                        </span>
                        <span class="text-xs font-mono font-medium text-white/50">
                            ${sub.votes || 0} voto(s)
                        </span>
                    </div>
                    <div class="space-y-2">
                        ${cardsHtml}
                    </div>
                </div>
            `;
        }).join('');
    }

    async function startRound() {
        const res = await fetch(`/api/games/${gameCode}/start-round`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        if (!data.success) alert(data.message || 'Erro ao iniciar rodada');
        fetchState();
    }

    async function forceJudging() {
        if (!confirm('Deseja abrir para votação agora?')) return;
        await fetch(`/api/games/${gameCode}/force-judging`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        fetchState();
    }

    async function removePlayer(playerId) {
        if (!confirm('Deseja remover este jogador?')) return;
        await fetch(`/api/games/${gameCode}/players/${playerId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        fetchState();
    }

    async function fetchState() {
        try {
            const res = await fetch(`/api/games/${gameCode}/host-state`);
            const data = await res.json();
            renderState(data);
        } catch (e) {
            console.error(e);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        fetchState();

        if (window.Echo) {
            window.Echo.channel(`game.${gameCode}`)
                .listen('PlayerJoinedEvent', () => fetchState())
                .listen('RoundStartedEvent', () => fetchState())
                .listen('SubmissionReceivedEvent', () => fetchState())
                .listen('JudgingPhaseStartedEvent', () => fetchState())
                .listen('VoteReceivedEvent', () => fetchState())
                .listen('RoundWinnerAnnouncedEvent', (e) => {
                    document.getElementById('winner-player-name').innerText = `${e.winner.nickname} (+1 Ponto)`;
                    document.getElementById('winner-cards-text').innerHTML = e.winning_cards.join(' <span class="text-appleAccent">+</span> ');
                    fetchState();
                })
                .listen('NextRoundReadyEvent', (e) => {
                    document.getElementById('host-ready-status').innerText = `${e.ready_count} de ${e.total_players} jogadores prontos para a próxima rodada`;
                    fetchState();
                });
        }
    });
</script>
@endpush
