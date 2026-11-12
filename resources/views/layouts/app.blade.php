<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Mentes Ousadas - Jogo de Cartas')</title>
    <!-- Fonte Inter & SF Pro Display via Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        appleDark: '#000000',
                        appleSurface: 'rgba(28, 28, 30, 0.75)',
                        appleBorder: 'rgba(255, 255, 255, 0.12)',
                        appleSubtle: 'rgba(255, 255, 255, 0.05)',
                        appleAccent: '#0A84FF',
                        applePink: '#FF375F',
                        appleYellow: '#FFD60A',
                        appleGreen: '#30D158',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '-apple-system', 'BlinkMacSystemFont', '"SF Pro Display"', '"SF Pro Text"', 'sans-serif'],
                        mono: ['"Space Grotesk"', 'ui-monospace', 'monospace'],
                    }
                }
            }
        }
    </script>
    <!-- Pusher & Echo -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        * {
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }

        body {
            background-color: #000000;
            color: #F5F5F7;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "SF Pro Display", sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
            letter-spacing: -0.015em;
        }

        .apple-glass {
            background: rgba(28, 28, 30, 0.65);
            backdrop-filter: blur(24px) saturate(190%);
            -webkit-backdrop-filter: blur(24px) saturate(190%);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .apple-glass-card {
            background: rgba(20, 20, 22, 0.75);
            backdrop-filter: blur(30px) saturate(200%);
            -webkit-backdrop-filter: blur(30px) saturate(200%);
            border: 1px solid rgba(255, 255, 255, 0.14);
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.8), inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .apple-white-card {
            background: #FFFFFF;
            color: #1D1D1F;
            border-radius: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(0, 0, 0, 0.05);
            transition: all 0.28s cubic-bezier(0.25, 1, 0.5, 1);
            position: relative;
        }

        .apple-white-card:hover {
            transform: translateY(-4px) scale(1.015);
            box-shadow: 0 16px 32px -8px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(0, 0, 0, 0.08);
        }

        .apple-white-card:active {
            transform: scale(0.97);
        }

        .card-selected-apple {
            transform: translateY(-8px) scale(1.02) !important;
            box-shadow: 0 18px 36px -6px rgba(10, 132, 255, 0.4), 0 0 0 3px #0A84FF !important;
        }

        .apple-black-card {
            background: linear-gradient(145deg, #18181A 0%, #0A0A0B 100%);
            color: #FFFFFF;
            border-radius: 28px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.9), inset 0 1px 0 rgba(255, 255, 255, 0.15);
        }

        @keyframes appleSpring {
            0% { transform: scale(0.92); opacity: 0; }
            60% { transform: scale(1.02); opacity: 1; }
            100% { transform: scale(1); }
        }

        .animate-apple-spring {
            animation: appleSpring 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        ::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
    </style>
    @stack('styles')
</head>
<body class="antialiased flex flex-col selection:bg-appleAccent selection:text-white">
    <div class="fixed top-0 left-1/4 w-[500px] h-[500px] bg-appleAccent/10 rounded-full blur-[140px] pointer-events-none -z-10"></div>
    <div class="fixed bottom-0 right-1/4 w-[600px] h-[600px] bg-applePink/8 rounded-full blur-[160px] pointer-events-none -z-10"></div>

    <main class="flex-1 flex flex-col">
        @yield('content')
    </main>

    <script>
        const reverbKey = "{{ config('reverb.apps.apps.0.key', env('REVERB_APP_KEY', 'llmuwx2zn3reu9yaiy83')) }}";
        const isHttps = window.location.protocol === 'https:';
        const isTunnel = window.location.hostname.includes('trycloudflare.com');

        // Configuração adaptativa automática (local vs nuvem/túnel seguro)
        const wsHost = isTunnel ? window.location.hostname : window.location.hostname;
        const wsPort = isTunnel ? (isHttps ? 443 : 80) : {{ env('REVERB_PORT', 8085) }};
        const forceTls = isHttps || isTunnel;

        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: reverbKey,
            wsHost: wsHost,
            wsPort: wsPort,
            wssPort: wsPort,
            forceTLS: forceTls,
            enabledTransports: ['ws', 'wss'],
        });

        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) window.lucide.createIcons();
        });
    </script>
    @stack('scripts')
</body>
</html>
