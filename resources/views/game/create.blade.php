@extends('layouts.app')

@section('title', 'Cards Against Humanity - O jogo de cartas da humanidade')

@section('content')
<div class="flex-1 flex flex-col justify-between items-center px-4 py-8 md:py-12 max-w-7xl mx-auto w-full select-none">
    
    <!-- CONTEÚDO PRINCIPAL (LAYOUT DA IMAGEM 4) -->
    <div class="w-full flex-1 flex flex-col lg:flex-row items-center justify-center gap-10 lg:gap-16 my-auto">
        
        <!-- LADO ESQUERDO: Título, Descrição e Carta Preta Inclinada -->
        <div class="flex flex-col items-center lg:items-start text-center lg:text-left max-w-md">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white tracking-tight leading-none mb-3">
                Cards<br>Against<br>Humanity<span class="text-xs font-normal align-super text-white/50">™</span>
            </h1>
            <p class="text-white/60 text-sm md:text-base leading-relaxed mb-8 flex items-center justify-center lg:justify-start gap-1.5">
                O jogo onde a humanidade mostra seu lado mais criativo. 😂
            </p>

            <!-- CARTA PRETA INCLINADA 3D (IGUAL À IMAGEM 4) -->
            <div class="hidden sm:block relative -rotate-6 transition-transform hover:rotate-0 duration-300">
                <div class="apple-black-card w-64 h-72 p-6 flex flex-col justify-between rounded-3xl shadow-2xl border border-white/15">
                    <p class="text-lg font-extrabold text-white leading-snug">
                        A solução para todos os problemas é ________.
                    </p>
                    <div class="flex items-center gap-2 text-white/60 text-xs font-bold pt-4 border-t border-white/10">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                            <rect x="2" y="4" width="13" height="17" rx="2" fill="white" fill-opacity="0.3"/>
                            <rect x="7" y="2" width="13" height="17" rx="2" fill="white"/>
                        </svg>
                        <span class="text-[10px] tracking-wider uppercase font-bold">Cards Against Humanity</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- CENTRO: CARD DE CONTROLE COM ABAS (CRIAR SALA / ENTRAR NA SALA) -->
        <div class="w-full max-w-[420px] bg-[#141416]/95 backdrop-blur-2xl border border-white/10 rounded-[32px] p-7 md:p-8 shadow-2xl relative z-10">
            
            <!-- ABAS SUPERIORES (CRIAR SALA / ENTRAR NA SALA) -->
            <div class="flex items-center justify-center border-b border-white/10 pb-4 mb-6 gap-6">
                <button type="button" onclick="switchTab('create')" id="tab-create" class="flex items-center gap-2 text-sm font-bold text-white border-b-2 border-white pb-1 transition-all">
                    <i data-lucide="play" class="w-4 h-4 fill-white text-white"></i> Criar Sala
                </button>
                <button type="button" onclick="switchTab('join')" id="tab-join" class="flex items-center gap-2 text-sm font-semibold text-white/50 hover:text-white transition-all pb-1">
                    <i data-lucide="link" class="w-4 h-4"></i> Entrar na Sala
                </button>
            </div>

            <!-- FORMULÁRIO 1: CRIAR SALA -->
            <form action="{{ route('game.store') }}" method="POST" id="form-create-room" onsubmit="handleCreateGame(event)" class="space-y-5">
                @csrf
                <div>
                    <label for="title" class="flex items-center gap-1.5 text-xs font-semibold text-white/60 mb-2">
                        <i data-lucide="home" class="w-3.5 h-3.5"></i> Nome da Sala
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-white/30">
                            <i data-lucide="users" class="w-4 h-4"></i>
                        </span>
                        <input type="text" name="title" id="title" placeholder="Ex: Festa do Luciano"
                            class="w-full bg-[#1C1C1E] border border-white/10 rounded-2xl pl-10 pr-4 py-3.5 text-white placeholder-white/30 focus:outline-none focus:border-white transition-all text-sm font-medium">
                    </div>
                </div>

                <div>
                    <label class="flex items-center gap-1.5 text-xs font-semibold text-white/60 mb-2">
                        <i data-lucide="help-circle" class="w-3.5 h-3.5"></i> Quantidade de Rodadas
                    </label>
                    <input type="hidden" name="total_rounds" id="input_total_rounds" value="5">
                    <div class="grid grid-cols-4 gap-2">
                        <button type="button" onclick="selectRounds(3, this)" class="rounds-btn py-2.5 rounded-xl text-sm font-bold bg-[#1C1C1E] text-white/60 hover:text-white border border-white/5 transition-all">3</button>
                        <button type="button" onclick="selectRounds(5, this)" class="rounds-btn active py-2.5 rounded-xl text-sm font-bold bg-white text-black shadow-lg transition-all">5</button>
                        <button type="button" onclick="selectRounds(7, this)" class="rounds-btn py-2.5 rounded-xl text-sm font-bold bg-[#1C1C1E] text-white/60 hover:text-white border border-white/5 transition-all">7</button>
                        <button type="button" onclick="selectRounds(10, this)" class="rounds-btn py-2.5 rounded-xl text-sm font-bold bg-[#1C1C1E] text-white/60 hover:text-white border border-white/5 transition-all">10</button>
                    </div>
                </div>

                <button type="submit" id="btn-create-submit" class="w-full bg-white hover:bg-white/90 active:scale-98 text-black font-extrabold py-4 rounded-2xl shadow-xl transition-all flex items-center justify-center gap-2 text-base mt-2">
                    <i data-lucide="play" class="w-4 h-4 fill-black text-black"></i>
                    <span id="btn-create-text">Criar Sala</span>
                </button>

                <div class="relative flex items-center justify-center my-4">
                    <div class="border-t border-white/10 w-full"></div>
                    <span class="bg-[#141416] px-3 text-[11px] text-white/30 uppercase font-mono tracking-wider absolute">ou</span>
                </div>

                <button type="button" onclick="switchTab('join')" class="w-full bg-transparent hover:bg-white/5 text-white/70 hover:text-white font-semibold py-3 rounded-2xl border border-white/10 transition-all flex items-center justify-center gap-2 text-xs">
                    <i data-lucide="link" class="w-3.5 h-3.5"></i>
                    Entrar com um Código
                </button>
            </form>

            <!-- FORMULÁRIO 2: ENTRAR COM CÓDIGO DA SALA -->
            <form id="form-join-code" onsubmit="handleDirectCodeJoin(event)" class="space-y-5 hidden">
                <div>
                    <label for="room_code_input" class="flex items-center gap-1.5 text-xs font-semibold text-white/60 mb-2">
                        <i data-lucide="key" class="w-3.5 h-3.5"></i> Código da Sala (6 Letras)
                    </label>
                    <input type="text" id="room_code_input" maxlength="6" uppercase required placeholder="Ex: ZF6DY8"
                        class="w-full bg-[#1C1C1E] border border-white/10 rounded-2xl px-4 py-3.5 text-white placeholder-white/30 text-center tracking-widest font-mono text-xl uppercase font-bold focus:outline-none focus:border-white transition-all">
                </div>

                <button type="submit" class="w-full bg-white hover:bg-white/90 active:scale-98 text-black font-extrabold py-4 rounded-2xl shadow-xl transition-all flex items-center justify-center gap-2 text-base">
                    <i data-lucide="arrow-right" class="w-4 h-4 text-black"></i>
                    Acessar Sala
                </button>

                <button type="button" onclick="switchTab('create')" class="w-full text-center text-xs text-white/40 hover:text-white pt-2">
                    ← Voltar para criar sala
                </button>
            </form>

        </div>

        <!-- LADO DIREITO: CARTA BRANCA INCLINADA 3D (IGUAL À IMAGEM 4) -->
        <div class="hidden lg:block relative rotate-6 transition-transform hover:rotate-0 duration-300">
            <div class="apple-white-card w-64 h-80 p-6 flex flex-col justify-between rounded-3xl shadow-2xl border border-black/10">
                <p class="text-xl font-black text-black leading-snug">
                    Uma resposta inesperada.
                </p>
                <div class="flex items-center gap-2 text-black/60 text-xs font-bold pt-4 border-t border-black/5">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                        <rect x="2" y="4" width="13" height="17" rx="2" fill="black" fill-opacity="0.3"/>
                        <rect x="7" y="2" width="13" height="17" rx="2" fill="black"/>
                    </svg>
                    <span class="text-[10px] tracking-wider uppercase font-extrabold">Cards Against Humanity</span>
                </div>
            </div>
        </div>

    </div>

    <!-- RODAPÉ DA TELA INICIAL (3 COLUNAS IDÊNTICAS À IMAGEM 4) -->
    <div class="w-full max-w-4xl mx-auto mt-12 pt-6 border-t border-white/10 grid grid-cols-1 md:grid-cols-3 gap-6 text-center md:text-left">
        <div class="flex items-center justify-center md:justify-start gap-3.5">
            <div class="w-10 h-10 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-white">
                <i data-lucide="zap" class="w-5 h-5 fill-white"></i>
            </div>
            <div>
                <h4 class="font-bold text-white text-sm">Rápido e Divertido</h4>
                <p class="text-xs text-white/50">Jogue em segundos</p>
            </div>
        </div>

        <div class="flex items-center justify-center md:justify-start gap-3.5">
            <div class="w-10 h-10 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-white">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="font-bold text-white text-sm">Com Amigos</h4>
                <p class="text-xs text-white/50">Em qualquer lugar</p>
            </div>
        </div>

        <div class="flex items-center justify-center md:justify-start gap-3.5">
            <div class="w-10 h-10 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-appleYellow">
                <i data-lucide="trophy" class="w-5 h-5 fill-appleYellow"></i>
            </div>
            <div>
                <h4 class="font-bold text-white text-sm">Seja o Mais Engraçado</h4>
                <p class="text-xs text-white/50">Vença a rodada</p>
            </div>
        </div>
    </div>

</div>

<script>
    function selectRounds(val, btn) {
        document.getElementById('input_total_rounds').value = val;
        document.querySelectorAll('.rounds-btn').forEach(b => {
            b.className = 'rounds-btn py-2.5 rounded-xl text-sm font-bold bg-[#1C1C1E] text-white/60 hover:text-white border border-white/5 transition-all';
        });
        btn.className = 'rounds-btn active py-2.5 rounded-xl text-sm font-bold bg-white text-black shadow-lg transition-all';
    }

    function switchTab(tab) {
        const createTab = document.getElementById('tab-create');
        const joinTab = document.getElementById('tab-join');
        const formCreate = document.getElementById('form-create-room');
        const formJoin = document.getElementById('form-join-code');

        if (tab === 'create') {
            createTab.className = 'flex items-center gap-2 text-sm font-bold text-white border-b-2 border-white pb-1 transition-all';
            joinTab.className = 'flex items-center gap-2 text-sm font-semibold text-white/50 hover:text-white transition-all pb-1';
            formCreate.classList.remove('hidden');
            formJoin.classList.add('hidden');
        } else {
            joinTab.className = 'flex items-center gap-2 text-sm font-bold text-white border-b-2 border-white pb-1 transition-all';
            createTab.className = 'flex items-center gap-2 text-sm font-semibold text-white/50 hover:text-white transition-all pb-1';
            formJoin.classList.remove('hidden');
            formCreate.classList.add('hidden');
            setTimeout(() => document.getElementById('room_code_input').focus(), 100);
        }
    }

    function handleDirectCodeJoin(e) {
        e.preventDefault();
        const code = document.getElementById('room_code_input').value.trim().toUpperCase();
        if (code) {
            window.location.href = `/join/${code}`;
        }
    }

    function handleCreateGame(e) {
        const btn = document.getElementById('btn-create-submit');
        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed');
        document.getElementById('btn-create-text').innerText = 'Criando sala...';
    }
</script>
@endsection