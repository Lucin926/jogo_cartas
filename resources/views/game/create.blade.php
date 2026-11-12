@extends('layouts.app')

@section('title', 'Criar Nova Partida - Mentes Ousadas')

@section('content')
<div class="flex-1 flex flex-col items-center justify-center p-6 relative">
    <div class="max-w-md w-full apple-glass-card rounded-[36px] p-8 md:p-10 text-center animate-apple-spring">
        <span class="inline-block px-3.5 py-1.5 rounded-full bg-appleAccent/15 text-appleAccent font-semibold text-xs tracking-wide mb-3">
            Multiplayer em Tempo Real
        </span>
        <h1 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight">MENTES OUSADAS</h1>
        <p class="text-white/60 text-sm mt-2 mb-8 leading-relaxed">
            O jogo de cartas sem filtros. Projete na TV e jogue pelo celular.
        </p>

        <form action="{{ route('game.store') }}" method="POST" id="create-game-form" onsubmit="handleCreateGame(event)" class="space-y-5 text-left">
            @csrf
            <div>
                <label for="title" class="block text-xs font-semibold text-white/50 uppercase tracking-wider mb-2">Nome da Sala</label>
                <input type="text" name="title" id="title" value="Mentes Ousadas"
                    class="w-full bg-white/5 border border-white/10 rounded-2xl px-4 py-3.5 text-white placeholder-white/30 focus:outline-none focus:border-appleAccent transition-all text-sm font-medium">
            </div>

            <div>
                <label for="total_rounds" class="block text-xs font-semibold text-white/50 uppercase tracking-wider mb-2">Quantidade de Rodadas (e Cartas na Mão)</label>
                <select name="total_rounds" id="total_rounds" class="w-full bg-black border border-white/10 rounded-2xl px-4 py-3.5 text-white focus:outline-none focus:border-appleAccent transition-all text-sm font-medium">
                    <option value="10" selected>10 Rodadas (10 cartas por jogador - Mínimo)</option>
                    <option value="12">12 Rodadas (12 cartas por jogador)</option>
                    <option value="15">15 Rodadas (15 cartas por jogador)</option>
                    <option value="20">20 Rodadas (20 cartas por jogador)</option>
                    <option value="25">25 Rodadas (25 cartas por jogador)</option>
                </select>
                <p class="text-[11px] text-white/40 mt-1.5">Conforme você vai jogando, a sua mão vai diminuindo rodada a rodada (10, 9, 8...).</p>
            </div>

            <button type="submit" id="btn-create-submit" class="w-full bg-white hover:bg-white/90 active:scale-95 text-black font-bold py-4 rounded-2xl shadow-xl transition-all flex items-center justify-center gap-2 text-base mt-2">
                <i data-lucide="play" class="w-5 h-5 fill-black"></i>
                <span id="btn-create-text">Criar Partida</span>
            </button>
        </form>

        <script>
            function handleCreateGame(e) {
                const btn = document.getElementById('btn-create-submit');
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
                document.getElementById('btn-create-text').innerText = 'Criando sala...';
            }
        </script>

        <div class="mt-8 pt-6 border-t border-white/10 flex items-center justify-between text-xs text-white/40">
            <a href="{{ route('admin.decks.index') }}" class="hover:text-white transition-colors flex items-center gap-1.5">
                <i data-lucide="layers" class="w-3.5 h-3.5"></i> Baralhos
            </a>
            <span class="font-mono">Apple HIG Design</span>
        </div>
    </div>
</div>
@endsection
