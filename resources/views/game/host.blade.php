@extends('layouts.app')

@section('title', 'Cards Against Humanity - Mesa: ' . $game->title . ' (' . $game->code . ')')

@section('content')
<div class="flex-1 flex flex-col p-4 md:p-6 lg:p-8 max-w-[1600px] w-full mx-auto select-none" id="host-app">
    
    <!-- HEADER MODERNO PREMIUM (Estilo Cards Against Humanity) -->
    <header class="bg-[#141519]/90 border border-white/10 rounded-3xl px-6 py-4 flex flex-wrap items-center justify-between gap-4 mb-6 shadow-2xl backdrop-blur-xl">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-2xl bg-white flex items-center justify-center shadow-lg">
                <svg class="w-5 h-5 fill-current text-black" viewBox="0 0 24 24">
                    <rect x="2" y="4" width="13" height="17" rx="2" fill="black" fill-opacity="0.3"/>
                    <rect x="7" y="2" width="13" height="17" rx="2" fill="black"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl md:text-2xl font-black tracking-tight text-white flex items-center gap-2.5">
                    {{ $game->title }}
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-white/10 text-white/80 font-mono font-bold" id="header-rounds-badge">
                        {{ $game->total_rounds }} Rodadas
                    </span>
                </h1>
                <div class="flex items-center gap-2 mt-0.5">
                    <span class="w-2 h-2 rounded-full bg-appleGreen animate-pulse"></span>
                    <span class="text-xs text-white/50 font-medium">Sincronização ao Vivo</span>
                </div>
            </div>
        </div>

        <!-- CÓDIGO DA SALA E AÇÕES -->
        <div class="flex items-center gap-5">
            <div class="text-right">
                <span class="text-[10px] font-bold uppercase tracking-widest text-white/40 block">Código da Sala</span>
                <div class="text-2xl md:text-3xl font-mono font-black text-white tracking-widest flex items-center gap-2">
                    <span id="room-code-display">{{ $game->code }}</span>
                    <button onclick="navigator.clipboard.writeText('{{ $game->code }}')" title="Copiar código" class="text-white/40 hover:text-white transition-colors p-1">
                        <i data-lucide="copy" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button id="btn-start-round" onclick="startRound()" class="bg-white hover:bg-white/90 active:scale-95 text-black font-black px-6 py-3.5 rounded-2xl shadow-xl transition-all flex items-center gap-2 text-sm">
                    <i data-lucide="play" class="w-4 h-4 fill-black"></i>
                    <span id="btn-start-text">Iniciar Partida</span>
                </button>
                <button id="btn-force-judging" onclick="forceJudging()" class="hidden bg-appleYellow/20 border border-appleYellow/40 hover:bg-appleYellow/30 text-appleYellow font-bold px-5 py-3.5 rounded-2xl shadow-lg transition-all text-sm flex items-center gap-2">
                    <i data-lucide="fast-forward" class="w-4 h-4"></i>
                    Abrir Votação
                </button>
            </div>
        </div>
    </header>

    <!-- GRADE PRINCIPAL -->
    <div class="flex-1 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- CENTRO DA MESA (ÁREA DE JOGO / PERGUNTA / CARTAS JOGADAS) -->
        <div class="lg:col-span-8 xl:col-span-9 flex flex-col justify-center min-h-[550px]">

            <!-- 1. LOBBY DE ESPERA (QR CODE ULTRA MODERNO) -->
            <div id="state-lobby" class="bg-[#141519]/90 border border-white/10 rounded-[36px] p-8 md:p-14 flex flex-col md:flex-row items-center justify-center gap-10 lg:gap-14 shadow-2xl relative overflow-hidden backdrop-blur-xl animate-apple-spring">
                
                <!-- CONTAINER QR CODE ESTILO CARD ELEGANTE -->
                <div class="bg-white p-6 rounded-[32px] shadow-[0_20px_50px_rgba(0,0,0,0.8)] flex flex-col items-center justify-center transform hover:scale-105 transition-transform duration-300">
                    <div class="w-60 h-60 flex items-center justify-center">
                        {!! $qrCodeSvg !!}
                    </div>
                    <div class="flex items-center gap-2 mt-4 text-black/70 text-xs font-bold uppercase tracking-wider">
                        <i data-lucide="camera" class="w-4 h-4"></i>
                        Aponte a câmera do celular
                    </div>
                </div>

                <!-- TEXTO DO LOBBY -->
                <div class="max-w-md text-center md:text-left space-y-4">
                    <span class="px-3.5 py-1.5 rounded-full bg-appleAccent/15 text-appleAccent font-bold text-xs tracking-wide inline-flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-appleAccent animate-ping"></span>
                        Lobby Aberto
                    </span>
                    <h2 class="text-3xl md:text-5xl font-black text-white tracking-tight leading-tight">
                        Escaneie para entrar no jogo.
                    </h2>
                    <p class="text-white/60 text-sm md:text-base leading-relaxed">
                        Aponte a câmera do celular ou entre com o código <span class="font-mono font-bold text-white bg-white/10 px-2 py-0.5 rounded-lg">{{ $game->code }}</span> no site.
                    </p>
                    <div class="pt-2 text-xs text-white/40 flex items-center gap-2 justify-center md:justify-start">
                        <i data-lucide="info" class="w-4 h-4"></i>
                        Novos jogadores podem entrar mesmo com a partida em andamento!
                    </div>
                </div>
            </div>

            <!-- 2. RODADA ATIVA -->
            <div id="state-round" class="hidden flex-col gap-6 animate-apple-spring">
                
                <!-- CARTA PRETA (PERGUNTA OFICIAL CARDS AGAINST HUMANITY) -->
                <div class="flex justify-center">
                    <div id="main-black-card" class="w-full max-w-3xl rounded-[32px] bg-[#111215] text-white p-8 md:p-12 min-h-[260px] flex flex-col justify-between border border-white/15 shadow-[0_30px_70px_rgba(0,0,0,0.95)] relative">
                        <div class="flex items-center justify-between text-xs font-bold text-white/50 uppercase tracking-wider mb-4">
                            <span id="round-number-badge" class="px-3.5 py-1 rounded-full bg-white/10 text-white font-mono">RODADA 1 DE 5</span>
                            <span id="black-card-pick-badge" class="font-mono text-appleAccent font-extrabold text-sm">ESCOLHA 1 CARTA</span>
                        </div>
                        
                        <p id="black-card-text" class="text-2xl md:text-4xl font-black leading-tight tracking-tight my-auto text-white">
                            Carregando pergunta...
                        </p>

                        <div class="flex items-center justify-between text-xs text-white/40 font-medium pt-5 border-t border-white/10 mt-6">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 fill-current text-white/60" viewBox="0 0 24 24">
                                    <rect x="2" y="4" width="13" height="17" rx="2" fill="white" fill-opacity="0.3"/>
                                    <rect x="7" y="2" width="13" height="17" rx="2" fill="white"/>
                                </svg>
                                <span class="font-black tracking-widest uppercase text-[10px] text-white/70">Cards Against Humanity</span>
                            </div>
                            <span class="text-white/60 font-semibold">Votação Comunitária</span>
                        </div>
                    </div>
                </div>

                <!-- PROGRESSO E CARTAS JOGADAS INSTANTANEAMENTE -->
                <div id="submissions-progress-box" class="space-y-6">
                    <div class="bg-[#141519]/90 border border-white/10 rounded-2xl p-5 text-center max-w-lg mx-auto w-full shadow-xl">
                        <div class="flex items-center justify-between text-sm text-white/70 font-semibold mb-2">
                            <span>Respostas enviadas</span>
                            <span id="progress-text" class="font-mono font-black text-white text-base">0 / 0</span>
                        </div>
                        <div class="w-full bg-white/10 rounded-full h-2.5 overflow-hidden">
                            <div id="progress-bar" class="bg-white h-full rounded-full transition-all duration-300 w-0"></div>
                        </div>
                        <p class="text-xs text-white/40 mt-2 font-medium" id="progress-subtitle">As cartas aparecem na mesa assim que forem enviadas!</p>
                    </div>
                </div>

                <!-- SEÇÃO PERSISTENTE DE CARTAS JOGADAS NA MESA (FICA VISÍVEL NA SUBMISSÃO E NA VOTAÇÃO) -->
                <div id="live-revealed-cards-section" class="space-y-3">
                    <div class="flex items-center justify-between px-2">
                        <span class="text-xs font-bold text-white/60 uppercase tracking-wider flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-appleGreen animate-pulse"></span>
                            Cartas Jogadas nesta Rodada (por Jogador)
                        </span>
                        <span class="text-xs font-mono font-bold text-white/40" id="live-cards-count-label">0 jogadas</span>
                    </div>
                    <div id="live-revealed-cards-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                        <!-- Cards ao vivo inseridos via JS -->
                    </div>
                </div>

                <!-- FASE DE VOTAÇÃO (CARTAS ANÔNIMAS PARA ESCOLHER A MELHOR) -->
                <div id="judging-grid-container" class="hidden space-y-5">
                    <div class="text-center py-2">
                        <span class="px-4 py-1.5 rounded-full bg-appleYellow/15 text-appleYellow font-black text-xs tracking-wider uppercase">
                            👑 Hora da Votação
                        </span>
                        <h3 class="text-2xl md:text-3xl font-black text-white mt-2">Escolham a melhor resposta no celular</h3>
                        <p class="text-xs font-mono text-white/50 mt-1" id="votes-counter-display">0 votos computados</p>
                    </div>

                    <div id="submissions-cards-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 pt-2">
                        <!-- Cartas geradas via JS -->
                    </div>
                </div>

                <!-- ANÚNCIO DO VENCEDOR DA RODADA -->
                <div id="round-winner-banner" class="hidden bg-[#16171b]/95 border border-appleYellow/40 rounded-[36px] p-8 md:p-12 text-center animate-apple-spring shadow-2xl space-y-4">
                    <span class="text-5xl inline-block">👑</span>
                    <h3 class="text-2xl md:text-4xl font-black text-white">Resposta Vencedora da Rodada!</h3>
                    <p class="text-2xl font-black text-appleYellow" id="winner-player-name">Jogador (+1 Ponto)</p>
                    <div id="winner-cards-text" class="bg-white/10 max-w-xl mx-auto p-6 rounded-3xl border border-white/15 text-xl font-black text-white shadow-xl"></div>
                    
                    <div class="pt-4 border-t border-white/10">
                        <p class="text-sm font-semibold text-white/70" id="host-ready-status">
                            Aguardando jogadores apertarem "Próxima Rodada"...
                        </p>
                    </div>
                </div>

            </div>

            <!-- FIM DE JOGO -->
            <div id="state-game-finished" class="hidden text-center bg-[#141519]/95 border border-white/15 rounded-[40px] p-12 space-y-6 shadow-2xl">
                <span class="text-6xl inline-block animate-bounce">🏆</span>
                <h2 class="text-4xl md:text-6xl font-black text-white">Temos um Campeão!</h2>
                <p id="champion-announcement" class="text-3xl font-black text-applePink"></p>
                <button onclick="startRound()" class="bg-white hover:bg-white/90 text-black font-black px-10 py-4 rounded-2xl text-lg shadow-2xl transition-all">
                    Iniciar Nova Partida
                </button>
            </div>

        </div>

        <!-- PAINEL LATERAL: RANKING DE PARTICIPANTES (ULTRA ORGANIZADO) -->
        <div class="lg:col-span-4 xl:col-span-3 space-y-4">
            <div class="bg-[#141519]/90 border border-white/10 rounded-3xl p-5 md:p-6 shadow-xl backdrop-blur-xl">
                <div class="flex items-center justify-between pb-3.5 border-b border-white/10 mb-4">
                    <h3 class="font-black text-white text-sm md:text-base flex items-center gap-2">
                        <i data-lucide="trophy" class="w-4 h-4 text-appleYellow"></i>
                        Ranking (<span id="players-count">0</span>)
                    </h3>
                    <span class="text-[10px] font-mono uppercase tracking-widest text-white/40 font-bold">Pontuação</span>
                </div>

                <!-- LISTA DO RANKING COM MEDALHAS -->
                <div id="players-list" class="space-y-2.5 max-h-[500px] overflow-y-auto pr-1">
                    <div class="text-center py-8 text-white/40 text-xs">
                        Nenhum jogador conectado ainda.
                    </div>
                </div>
            </div>

            <!-- CARD DE COMO JOGAR -->
            <div class="bg-[#141519]/70 border border-white/5 rounded-3xl p-5 text-xs text-white/50 space-y-2">
                <p class="font-bold text-white/80 uppercase tracking-wider text-[11px] flex items-center gap-1.5">
                    <i data-lucide="help-circle" class="w-3.5 h-3.5"></i> Regras Rápidas:
                </p>
                <p>1. Todos recebem a pergunta e escolhem a melhor resposta.</p>
                <p>2. A resposta aparece na tela grande na mesma hora.</p>
                <p>3. Todos votam na mais engraçada e a mais votada pontua!</p>
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
            playersList.innerHTML = `<div class="text-center py-8 text-white/40 text-xs">Nenhum jogador conectado ainda.</div>`;
        } else {
            // Ordena jogadores por pontuação decrescente
            const sortedPlayers = [...g.players].sort((a, b) => b.score - a.score);
            
            playersList.innerHTML = sortedPlayers.map((p, idx) => {
                let rankBadge = `<span class="w-5 h-5 rounded-full bg-white/10 text-white/60 flex items-center justify-center text-[10px] font-mono font-bold">${idx + 1}</span>`;
                if (idx === 0 && p.score > 0) rankBadge = `<span class="text-sm">🥇</span>`;
                else if (idx === 1 && p.score > 0) rankBadge = `<span class="text-sm">🥈</span>`;
                else if (idx === 2 && p.score > 0) rankBadge = `<span class="text-sm">🥉</span>`;

                return `
                    <div class="flex items-center justify-between p-3 rounded-2xl bg-[#1b1c22] border border-white/[0.06] transition-all hover:border-white/20">
                        <div class="flex items-center gap-2.5 truncate">
                            ${rankBadge}
                            <span class="w-2 h-2 rounded-full ${p.is_connected ? 'bg-appleGreen' : 'bg-white/20'}"></span>
                            <span class="font-bold text-sm text-white truncate">${p.nickname}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-black text-sm text-white bg-white/10 px-2.5 py-0.5 rounded-xl">${p.score} <span class="text-[10px] text-white/50 font-normal">pts</span></span>
                            <button onclick="removePlayer(${p.id})" title="Remover jogador" class="text-white/30 hover:text-red-400 transition-colors p-1">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
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

            document.getElementById('round-number-badge').innerText = `RODADA ${r.round_number} DE ${g.total_rounds}`;
            document.getElementById('black-card-pick-badge').innerText = `ESCOLHA ${r.black_card.pick_count} CARTA(S)`;
            document.getElementById('black-card-text').innerText = r.black_card.text;

            const subBox = document.getElementById('submissions-progress-box');
            const liveCardsSection = document.getElementById('live-revealed-cards-section');
            const judgingGrid = document.getElementById('judging-grid-container');
            const winnerBanner = document.getElementById('round-winner-banner');

            // Renderiza sempre as cartas enviadas por jogador nesta rodada
            renderLiveSubmissions(r.live_submissions || []);

            if (r.status === 'submitting') {
                subBox.classList.remove('hidden');
                liveCardsSection.classList.remove('hidden');
                judgingGrid.classList.add('hidden');
                winnerBanner.classList.add('hidden');
                btnForceJudging.classList.remove('hidden');

                const percent = r.eligible_count > 0 ? (r.submissions_count / r.eligible_count) * 100 : 0;
                document.getElementById('progress-text').innerText = `${r.submissions_count} / ${r.eligible_count}`;
                document.getElementById('progress-bar').style.width = `${percent}%`;
            } else if (r.status === 'judging') {
                subBox.classList.add('hidden');
                liveCardsSection.classList.remove('hidden');
                judgingGrid.classList.remove('hidden');
                winnerBanner.classList.add('hidden');
                btnForceJudging.classList.add('hidden');

                document.getElementById('votes-counter-display').innerText = `${r.votes_count} de ${r.eligible_count} jogadores votaram`;
                renderJudgingCards(r.anonymized_submissions);
            } else if (r.status === 'finished') {
                subBox.classList.add('hidden');
                liveCardsSection.classList.add('hidden');
                judgingGrid.classList.add('hidden');
                winnerBanner.classList.remove('hidden');
                btnForceJudging.classList.add('hidden');

                document.getElementById('host-ready-status').innerText = `${r.ready_count} de ${r.eligible_count} jogadores prontos para a próxima rodada`;
            }
        }
    }

    function renderLiveSubmissions(submissions) {
        const grid = document.getElementById('live-revealed-cards-grid');
        const countLabel = document.getElementById('live-cards-count-label');
        if (!grid) return;

        countLabel.innerText = `${submissions.length} jogada(s)`;

        if (submissions.length === 0) {
            grid.innerHTML = '<p class="text-white/40 col-span-full text-center py-6 text-xs">Aguardando participantes jogarem suas cartas pelo celular...</p>';
            return;
        }

        grid.innerHTML = submissions.map(sub => {
            const cardsHtml = sub.cards.map(c => `
                <div class="apple-white-card p-5 min-h-[140px] rounded-2xl shadow-xl border border-black/10">
                    <p class="font-extrabold text-black text-base md:text-lg leading-snug">
                        ${c.text}
                    </p>
                    <div class="flex items-center gap-1.5 text-black/50 text-[10px] font-bold uppercase tracking-wider pt-3 mt-3 border-t border-black/5">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                            <rect x="2" y="4" width="13" height="17" rx="2" fill="black" fill-opacity="0.3"/>
                            <rect x="7" y="2" width="13" height="17" rx="2" fill="black"/>
                        </svg>
                        <span>Cards Against Humanity</span>
                    </div>
                </div>
            `).join('');

            return `
                <div class="bg-[#18191f] rounded-3xl p-5 space-y-3 border border-white/10 shadow-lg animate-apple-spring hover:border-white/20 transition-all">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-appleAccent/20 flex items-center justify-center text-appleAccent text-xs font-black">
                                ${sub.player_nickname.substring(0, 1).toUpperCase()}
                            </div>
                            <span class="text-sm font-black text-white">
                                ${sub.player_nickname}
                            </span>
                        </div>
                        <span class="text-[10px] font-mono font-bold text-appleGreen bg-appleGreen/10 px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                            Jogou
                        </span>
                    </div>
                    <div class="space-y-3">
                        ${cardsHtml}
                    </div>
                </div>
            `;
        }).join('');
    }

    function renderJudgingCards(submissions) {
        const grid = document.getElementById('submissions-cards-grid');
        if (!submissions || submissions.length === 0) {
            grid.innerHTML = '<p class="text-white/40 col-span-full text-center">Nenhuma resposta submetida.</p>';
            return;
        }

        grid.innerHTML = submissions.map(sub => {
            const cardsHtml = sub.cards.map(c => `
                <div class="apple-white-card p-5 min-h-[140px] rounded-2xl shadow-xl border border-black/10">
                    <p class="font-extrabold text-black text-base md:text-lg leading-snug">
                        ${c.text}
                    </p>
                    <div class="flex items-center gap-1.5 text-black/50 text-[10px] font-bold uppercase tracking-wider pt-3 mt-3 border-t border-black/5">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                            <rect x="2" y="4" width="13" height="17" rx="2" fill="black" fill-opacity="0.3"/>
                            <rect x="7" y="2" width="13" height="17" rx="2" fill="black"/>
                        </svg>
                        <span>Cards Against Humanity</span>
                    </div>
                </div>
            `).join('');

            return `
                <div class="bg-[#18191f] rounded-3xl p-5 space-y-3 border border-white/10 hover:border-white/25 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-mono font-black text-appleYellow bg-appleYellow/10 px-3 py-1 rounded-full">
                            Opção #${sub.anonymous_number}
                        </span>
                        <span class="text-xs font-mono font-bold text-white/60">
                            ${sub.votes || 0} voto(s)
                        </span>
                    </div>
                    <div class="space-y-3">
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
                .listen('SubmissionReceivedEvent', (e) => {
                    fetchState();
                })
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