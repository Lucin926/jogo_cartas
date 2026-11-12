#!/bin/bash

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$DIR"

echo "=========================================================="
echo "      INICIANDO SERVIDORES DO JOGO 'MENTES OUSADAS'       "
echo "=========================================================="

# Libera as portas 8085 e 8000 se houver algum processo antigo preso
fuser -k 8085/tcp 8000/tcp 2>/dev/null || true
pkill -f "artisan reverb" 2>/dev/null || true
pkill -f "artisan serve" 2>/dev/null || true
sleep 1

echo "[1/2] Iniciando WebSocket Server Laravel Reverb na porta 8085..."
php artisan reverb:start --port=8085 --host=0.0.0.0 &
REVERB_PID=$!

echo "[2/2] Iniciando Servidor Web HTTP Laravel na porta 8000..."
php artisan serve --host=0.0.0.0 --port=8000 &
SERVE_PID=$!

cleanup() {
    echo ""
    echo "Encerrando servidores..."
    kill $REVERB_PID 2>/dev/null || true
    kill $SERVE_PID 2>/dev/null || true
    fuser -k 8085/tcp 8000/tcp 2>/dev/null || true
    exit 0
}

trap cleanup SIGINT SIGTERM

echo ""
echo "🚀 Jogo pronto e rodando!"
echo "👉 Acesse no seu computador ou TV: http://localhost:8000"
echo "👉 Acesse no celular via mesma rede Wi-Fi substituindo 'localhost' pelo IP da sua máquina"
echo "👉 Pressione Ctrl+C para parar os servidores."
echo "=========================================================="

wait
