@extends('layouts.app')

@section('title', 'Entrar na Sala ' . $game->code)

@section('content')
<div class="flex-1 flex flex-col items-center justify-center p-6">
    <div class="max-w-md w-full apple-glass-card rounded-[36px] p-8 md:p-10 text-center animate-apple-spring">
        <span class="inline-block px-3.5 py-1.5 rounded-full bg-appleAccent/15 text-appleAccent font-semibold text-xs tracking-wide mb-3">
            Sala: {{ $game->code }}
        </span>
        <h1 class="text-3xl font-extrabold text-white tracking-tight">{{ $game->title }}</h1>
        <p class="text-white/60 text-sm mt-2 mb-8">Digite seu apelido para receber suas cartas.</p>

        <form id="join-form" onsubmit="handleJoin(event)" class="space-y-6 text-left">
            @csrf
            <div>
                <label for="nickname" class="block text-xs font-semibold text-white/50 uppercase tracking-wider mb-2">Seu Apelido</label>
                <input type="text" name="nickname" id="nickname" required autofocus placeholder="Ex: Zé"
                    class="w-full bg-white/5 border border-white/10 rounded-2xl px-4 py-4 text-white placeholder-white/30 focus:outline-none focus:border-appleAccent transition-all text-lg font-bold">
            </div>

            <button type="submit" id="btn-submit-join" class="w-full bg-white hover:bg-white/90 active:scale-95 text-black font-bold py-4 rounded-2xl shadow-xl transition-all flex items-center justify-center gap-2 text-base">
                <i data-lucide="log-in" class="w-5 h-5"></i>
                Entrar no Jogo
            </button>
        </form>
    </div>
</div>

<script>
    const gameCode = "{{ $game->code }}";

    const savedToken = localStorage.getItem(`player_token_${gameCode}`);
    if (savedToken) {
        window.location.href = `/play/${savedToken}`;
    }

    async function handleJoin(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit-join');
        const nickname = document.getElementById('nickname').value.trim();
        if (!nickname) return;

        btn.disabled = true;
        btn.innerText = 'Entrando...';

        try {
            const res = await fetch(window.location.pathname, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ nickname })
            });

            const data = await res.json();
            if (data.success && data.player) {
                localStorage.setItem(`player_token_${gameCode}`, data.player.session_token);
                window.location.href = data.redirect_url;
            } else {
                alert(data.message || 'Erro ao entrar na sala.');
                btn.disabled = false;
                btn.innerText = 'Entrar no Jogo';
            }
        } catch (err) {
            alert('Falha na conexão.');
            btn.disabled = false;
            btn.innerText = 'Entrar no Jogo';
        }
    }
</script>
@endsection
