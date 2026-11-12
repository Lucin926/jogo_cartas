@extends('layouts.app')

@section('title', 'Gerenciador de Baralhos - Mentes Ousadas')

@section('content')
<div class="flex-1 flex flex-col p-4 md:p-8 max-w-6xl w-full mx-auto">
    <div class="flex items-center justify-between pb-6 border-b border-cardBorder mb-6">
        <div>
            <h1 class="text-3xl font-black text-white">Painel de Baralhos</h1>
            <p class="text-sm text-gray-400">Cadastre, edite e gerencie cartas pretas (perguntas) e cartas brancas (respostas).</p>
        </div>
        <a href="{{ route('home') }}" class="bg-cardDark border border-cardBorder hover:border-gray-500 text-white px-4 py-2 rounded-xl text-sm font-bold transition-all flex items-center gap-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Voltar ao Jogo
        </a>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-xl mb-6 text-sm font-bold flex items-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- FORMULÁRIO E LISTA DE CARTAS PRETAS -->
        <div class="bg-cardDark border border-cardBorder rounded-3xl p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-black text-white flex items-center gap-2">
                    <span class="w-3 h-3 bg-white rounded-full"></span>
                    Cartas Pretas (Perguntas)
                </h2>
                <span class="text-xs font-mono text-gray-400">{{ $selectedDeck ? $selectedDeck->blackCards()->count() : 0 }} cartas</span>
            </div>

            <!-- Adicionar Carta Preta -->
            <form action="{{ route('admin.black-cards.store') }}" method="POST" class="space-y-3 mb-6 bg-dark/60 p-4 rounded-2xl border border-cardBorder">
                @csrf
                <input type="hidden" name="deck_id" value="{{ $selectedDeck?->id }}">
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400">Nova Pergunta (Use ______ para lacunas)</label>
                <textarea name="text" required rows="2" placeholder="Ex: A verdadeira razão da demissão do chefe foi ______."
                    class="w-full bg-dark border border-cardBorder rounded-xl p-3 text-white text-sm focus:outline-none focus:border-rose-500"></textarea>
                <button type="submit" class="w-full bg-black text-white border border-white/20 hover:bg-neutral-900 font-bold py-2.5 rounded-xl text-xs uppercase tracking-wider transition-all">
                    + Adicionar Carta Preta
                </button>
            </form>

            <!-- Lista de Cartas Pretas -->
            <div class="flex-1 space-y-2 max-h-[400px] overflow-y-auto pr-1">
                @foreach($blackCards as $bCard)
                    <div class="bg-dark/80 border border-cardBorder p-3.5 rounded-xl flex items-start justify-between gap-3 text-sm">
                        <div>
                            <p class="font-bold text-white text-xs leading-relaxed">{{ $bCard->text }}</p>
                            <span class="text-[10px] font-mono text-rose-400 mt-1 inline-block">Exige {{ $bCard->pick_count }} carta(s)</span>
                        </div>
                        <form action="{{ route('admin.black-cards.destroy', $bCard->id) }}" method="POST" onsubmit="return confirm('Excluir carta?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-gray-500 hover:text-red-400 p-1">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">
                {{ $blackCards->links() }}
            </div>
        </div>

        <!-- FORMULÁRIO E LISTA DE CARTAS BRANCAS -->
        <div class="bg-cardDark border border-cardBorder rounded-3xl p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-black text-white flex items-center gap-2">
                    <span class="w-3 h-3 bg-gray-400 rounded-full"></span>
                    Cartas Brancas (Respostas)
                </h2>
                <span class="text-xs font-mono text-gray-400">{{ $selectedDeck ? $selectedDeck->whiteCards()->count() : 0 }} cartas</span>
            </div>

            <!-- Adicionar Carta Branca -->
            <form action="{{ route('admin.white-cards.store') }}" method="POST" class="space-y-3 mb-6 bg-dark/60 p-4 rounded-2xl border border-cardBorder">
                @csrf
                <input type="hidden" name="deck_id" value="{{ $selectedDeck?->id }}">
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400">Nova Resposta</label>
                <input type="text" name="text" required placeholder="Ex: Um estagiário chorando no banheiro."
                    class="w-full bg-dark border border-cardBorder rounded-xl p-3 text-white text-sm focus:outline-none focus:border-rose-500">
                <button type="submit" class="w-full bg-white text-dark hover:bg-gray-100 font-extrabold py-2.5 rounded-xl text-xs uppercase tracking-wider transition-all">
                    + Adicionar Carta Branca
                </button>
            </form>

            <!-- Lista de Cartas Brancas -->
            <div class="flex-1 space-y-2 max-h-[400px] overflow-y-auto pr-1">
                @foreach($whiteCards as $wCard)
                    <div class="bg-white text-dark p-3.5 rounded-xl flex items-center justify-between gap-3 text-sm card-shadow-white">
                        <p class="font-bold text-xs leading-relaxed">{{ $wCard->text }}</p>
                        <form action="{{ route('admin.white-cards.destroy', $wCard->id) }}" method="POST" onsubmit="return confirm('Excluir carta?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-gray-400 hover:text-red-500 p-1">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">
                {{ $whiteCards->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
