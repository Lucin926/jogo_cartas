@extends('layouts.app')

@section('title', 'Cards Against Humanity - O jogo onde a humanidade mostra seu lado criativo')

@section('content')
<div class="relative min-h-screen w-full flex flex-col justify-between items-center px-4 py-8 md:py-12 select-none overflow-hidden" 
     style="background: radial-gradient(circle at 50% 20%, #1c1d22 0%, #0d0e11 60%, #050608 100%);">

    <!-- Efeitos de iluminação sutil do mockup -->
    <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[900px] h-[450px] bg-white/[0.03] rounded-full blur-[140px] pointer-events-none"></div>

    <!-- CONTEÚDO PRINCIPAL -->
    <div class="w-full max-w-6xl flex-1 flex flex-col lg:flex-row items-center justify-center gap-8 lg:gap-14 my-auto relative z-10">

        <!-- LADO ESQUERDO: Título, Descrição e Carta Preta Inclinada -->
        <div class="flex flex-col items-center lg:items-start text-center lg:text-left max-w-sm">
            <h1 class="text-4xl md:text-5xl lg:text-[58px] font-black text-white tracking-tight leading-[0.98] mb-3">
                Cards<br>Against<br>Humanity<span class="text-xs font-normal align-super text-white/50 ml-0.5">™</span>
            </h1>
            <p class="text-white/60 text-sm md:text-[15px] font-medium leading-relaxed mb-8 flex items-center justify-center lg:justify-start gap-1.5">
                O jogo onde a humanidade<br class="hidden lg:block"> mostra seu lado mais criativo. 😂
            </p>

            <!-- CARTA PRETA INCLINADA COM PERSPECTIVA 3D (IGUAL À IMAGEM) -->
            <div class="hidden sm:block relative -rotate-6 transition-all duration-300 hover:rotate-0 hover:scale-105 cursor-default mt-2">
                <div class="w-60 h-72 p-6 rounded-[26px] bg-[#121316] text-white flex flex-col justify-between border border-white/10 shadow-[0_25px_50px_-12px_rgba(0,0,0,0.9),0_0_0_1px_rgba(255,255,255,0.05)]">
                    <p class="text-lg font-black leading-snug tracking-tight">
                        A solução para todos os problemas é ________.
                    </p>
                    <div class="flex items-center gap-2 pt-4 border-t border-white/10 text-white/40">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <rect x="2" y="4" width="13" height="17" rx="2" fill="white" fill-opacity="0.3"/>
                            <rect x="7" y="2" width="13" height="17" rx="2" fill="white"/>
                        </svg>
                        <span class="text-[9px] font-black tracking-widest uppercase">Cards Against Humanity</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- CENTRO: CARD DE CONTROLE PRINCIPAL (IDÊNTICO AO MOCKUP) -->
        <div class="w-full max-w-[440px] bg-[#16171b]/90 backdrop-blur-2xl border border-white/[0.12] rounded-[36px] p-7 md:p-9 shadow-[0_30px_70px_-15px_rgba(0,0,0,0.95)] relative z-20">
            
            <!-- ABAS SUPERIORES COM INDICADOR ATIVO -->
            <div class="flex items-center justify-center border-b border-white/[0.08] pb-4 mb-7 gap-8">
                <button type="button" onclick="switchTab('create')" id="tab-create" class="flex items-center gap-2 text-sm font-extrabold text-white border-b-2 border-white pb-2 transition-all">
                    <i data-lucide="play" class="w-4 h-4 fill-white text-white"></i> Criar Sala
                </button>
                <button type="button" onclick="switchTab('join')" id="tab-join" class="flex items-center gap-2 text-sm font-semibold text-white/40 hover:text-white transition-all pb-2">
                    <i data-lucide="link" class="w-4 h-4"></i> Entrar na Sala
                </button>
            </div>

            <!-- FORMULÁRIO: CRIAR SALA -->
            <form action="{{ route('game.store') }}" method="POST" id="form-create-room" onsubmit="handleCreateGame(event)" class="space-y-6">
                @csrf
                <div>
                    <label for="title" class="flex items-center gap-1.5 text-xs font-bold text-white/50 uppercase tracking-wider mb-2.5">
                        <i data-lucide="home" class="w-3.5 h-3.5 text-white/60"></i> Nome da Sala
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-white/30">
                            <i data-lucide="users" class="w-4 h-4"></i>
                        </span>
                        <input type="text" name="title" id="title" placeholder="Ex: Festa do Luciano"
                            class="w-full bg-[#1e2025] border border-white/[0.08] rounded-2xl pl-11 pr-4 py-3.5 text-white placeholder-white/30 focus:outline-none focus:border-white/40 focus:bg-[#23252b] transition-all text-sm font-semibold">
                    </div>
                </div>

                <div>
                    <label class="flex items-center gap-1.5 text-xs font-bold text-white/50 uppercase tracking-wider mb-2.5">
                        <i data-lucide="help-circle" class="w-3.5 h-3.5 text-white/60"></i> Quantidade de Rodadas
                    </label>
                    <input type="hidden" name="total_rounds" id="input_total_rounds" value="5">
                    <div class="grid grid-cols-4 gap-2.5">
                        <button type="button" onclick="selectRounds(3, this)" class="rounds-btn py-3 rounded-2xl text-sm font-extrabold bg-[#1e2025] text-white/50 hover:text-white border border-white/[0.06] transition-all">3</button>
                        <button type="button" onclick="selectRounds(5, this)" class="rounds-btn active py-3 rounded-2xl text-sm font-extrabold bg-white text-black shadow-lg border border-white transition-all">5</button>
                        <button type="button" onclick="selectRounds(7, this)" class="rounds-btn py-3 rounded-2xl text-sm font-extrabold bg-[#1e2025] text-white/50 hover:text-white border border-white/[0.06] transition-all">7</button>
                        <button type="button" onclick="selectRounds(10, this)" class="rounds-btn py-3 rounded-2xl text-sm font-extrabold bg-[#1e2025] text-white/50 hover:text-white border border-white/[0.06] transition-all">10</button>
                    </div>
                </div>

                <button type="submit" id="btn-create-submit" class="w-full bg-white hover:bg-white/95 active:scale-[0.98] text-black font-black py-4 rounded-2xl shadow-[0_15px_30px_rgba(255,255,255,0.15)] transition-all flex items-center justify-center gap-2 text-base mt-3">
                    <i data-lucide="play" class="w-4 h-4 fill-black text-black"></i>
                    <span id="btn-create-text">Criar Sala</span>
                </button>

                <div class="relative flex items-center justify-center my-4">
                    <div class="border-t border-white/[0.08] w-full"></div>
                    <span class="bg-[#16171b] px-3 text-[10px] text-white/30 uppercase font-mono tracking-widest absolute">ou</span>
                </div>

                <button type="button" onclick="switchTab('join')" class="w-full bg-transparent hover:bg-white/[0.04] text-white/60 hover:text-white font-bold py-3.5 rounded-2xl border border-white/[0.08] transition-all flex items-center justify-center gap-2 text-xs uppercase tracking-wider">
                    <i data-lucide="link" class="w-3.5 h-3.5"></i>
                    Entrar com um Código
                </button>
            </form>

            <!-- FORMULÁRIO: ENTRAR COM CÓDIGO DA SALA -->
            <form id="form-join-code" onsubmit="handleDirectCodeJoin(event)" class="space-y-6 hidden">
                <div>
                    <label for="room_code_input" class="flex items-center gap-1.5 text-xs font-bold text-white/50 uppercase tracking-wider mb-2.5">
                        <i data-lucide="key" class="w-3.5 h-3.5 text-white/60"></i> Código da Sala (6 Letras)
                    </label>
                    <input type="text" id="room_code_input" maxlength="6" uppercase required placeholder="Ex: ZF6DY8"
                        class="w-full bg-[#1e2025] border border-white/[0.08] rounded-2xl px-4 py-4 text-white placeholder-white/30 text-center tracking-[0.25em] font-mono text-2xl uppercase font-black focus:outline-none focus:border-white transition-all">
                </div>

                <button type="submit" class="w-full bg-white hover:bg-white/95 active:scale-[0.98] text-black font-black py-4 rounded-2xl shadow-xl transition-all flex items-center justify-center gap-2 text-base">
                    <i data-lucide="arrow-right" class="w-4 h-4 text-black"></i>
                    Acessar Sala
                </button>

                <button type="button" onclick="switchTab('create')" class="w-full text-center text-xs text-white/40 hover:text-white pt-2 font-medium">
                    ← Voltar para criar sala
                </button>
            </form>

        </div>

        <!-- LADO DIREITO: CARTA BRANCA COM TRACINHOS DE LUZ E CARTA PRETA ATRÁS (IGUAL AO MOCKUP) -->
        <div class="hidden lg:flex relative items-center justify-center pl-6">
            
            <!-- Tracinhos de impacto/foco em cima da carta branca -->
            <div class="absolute -top-7 left-12 flex gap-1 z-20">
                <span class="w-1 h-3.5 bg-white/70 rounded-full rotate-[-25deg]"></span>
                <span class="w-1 h-4 bg-white/90 rounded-full"></span>
                <span class="w-1 h-3.5 bg-white/70 rounded-full rotate-[25deg]"></span>
            </div>

            <!-- CARTA PRETA DE FUNDO INCLINADA -->
            <div class="absolute -top-6 -right-10 rotate-12 z-0 opacity-85">
                <div class="w-56 h-72 p-5 rounded-[24px] bg-[#111215] text-white flex flex-col justify-between border border-white/10 shadow-2xl">
                    <p class="text-base font-black leading-snug tracking-tight">
                        O segredo para dominar o mundo é...
                    </p>
                    <div class="flex items-center gap-1.5 text-white/30 text-[9px] font-bold">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                            <rect x="2" y="4" width="13" height="17" rx="2" fill="white" fill-opacity="0.3"/>
                            <rect x="7" y="2" width="13" height="17" rx="2" fill="white"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- CARTA BRANCA PRINCIPAL NA FRENTE -->
            <div class="relative rotate-[4deg] transition-all duration-300 hover:rotate-0 hover:scale-105 cursor-default z-10">
                <div class="w-60 h-80 p-6 rounded-[28px] bg-white text-black flex flex-col justify-between shadow-[0_30px_60px_-10px_rgba(0,0,0,0.8),0_10px_20px_-5px_rgba(0,0,0,0.3)] border border-black/10">
                    <p class="text-2xl font-black text-black leading-snug tracking-tight">
                        Uma resposta inesperada.
                    </p>
                    <div class="flex items-center gap-2 text-black/60 pt-4 border-t border-black/5">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                            <rect x="2" y="4" width="13" height="17" rx="2" fill="black" fill-opacity="0.3"/>
                            <rect x="7" y="2" width="13" height="17" rx="2" fill="black"/>
                        </svg>
                        <span class="text-[9px] font-black tracking-widest uppercase">Cards Against Humanity</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- RODAPÉ DA TELA INICIAL (3 COLUNAS ALINHADAS AO MOCKUP) -->
    <div class="w-full max-w-4xl mx-auto mt-8 pt-6 border-t border-white/[0.08] grid grid-cols-1 md:grid-cols-3 gap-6 text-center md:text-left relative z-10">
        <div class="flex items-center justify-center md:justify-start gap-3.5">
            <div class="w-10 h-10 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-white">
                <i data-lucide="zap" class="w-5 h-5 fill-white"></i>
            </div>
            <div>
                <h4 class="font-extrabold text-white text-sm">Rápido e Divertido</h4>
                <p class="text-xs text-white/50 font-medium">Jogue em segundos</p>
            </div>
        </div>

        <div class="flex items-center justify-center md:justify-start gap-3.5">
            <div class="w-10 h-10 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-white">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="font-extrabold text-white text-sm">Com Amigos</h4>
                <p class="text-xs text-white/50 font-medium">Em qualquer lugar</p>
            </div>
        </div>

        <div class="flex items-center justify-center md:justify-start gap-3.5">
            <div class="w-10 h-10 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-appleYellow">
                <i data-lucide="trophy" class="w-5 h-5 fill-appleYellow"></i>
            </div>
            <div>
                <h4 class="font-extrabold text-white text-sm">Seja o Mais Engraçado</h4>
                <p class="text-xs text-white/50 font-medium">Vença a rodada</p>
            </div>
        </div>
    </div>

</div>

<script>
    function selectRounds(val, btn) {
        document.getElementById('input_total_rounds').value = val;
        document.querySelectorAll('.rounds-btn').forEach(b => {
            b.className = 'rounds-btn py-3 rounded-2xl text-sm font-extrabold bg-[#1e2025] text-white/50 hover:text-white border border-white/[0.06] transition-all';
        });
        btn.className = 'rounds-btn active py-3 rounded-2xl text-sm font-extrabold bg-white text-black shadow-lg border border-white transition-all';
    }

    function switchTab(tab) {
        const createTab = document.getElementById('tab-create');
        const joinTab = document.getElementById('tab-join');
        const formCreate = document.getElementById('form-create-room');
        const formJoin = document.getElementById('form-join-code');

        if (tab === 'create') {
            createTab.className = 'flex items-center gap-2 text-sm font-extrabold text-white border-b-2 border-white pb-2 transition-all';
            joinTab.className = 'flex items-center gap-2 text-sm font-semibold text-white/40 hover:text-white transition-all pb-2';
            formCreate.classList.remove('hidden');
            formJoin.classList.add('hidden');
        } else {
            joinTab.className = 'flex items-center gap-2 text-sm font-extrabold text-white border-b-2 border-white pb-2 transition-all';
            createTab.className = 'flex items-center gap-2 text-sm font-semibold text-white/40 hover:text-white transition-all pb-2';
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