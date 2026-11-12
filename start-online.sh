#!/bin/bash

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$DIR"

echo "=========================================================="
echo "    INICIANDO MENTES OUSADAS - MODO VIRTUAL & ONLINE      "
echo "=========================================================="

fuser -k 8085/tcp 8000/tcp 2>/dev/null || true
pkill -f "artisan reverb" 2>/dev/null || true
pkill -f "artisan serve" 2>/dev/null || true
pkill -f "cloudflared" 2>/dev/null || true
sleep 1

echo "[1/3] Iniciando WebSockets (Reverb)..."
php artisan reverb:start --port=8085 --host=0.0.0.0 > /dev/null 2>&1 &
REVERB_PID=$!

echo "[2/3] Iniciando Servidor Web HTTP..."
php artisan serve --host=0.0.0.0 --port=8000 > /dev/null 2>&1 &
SERVE_PID=$!

# Forçamos IPv4 e HTTP2 para evitar que rotas de IPv6 caiam na sua rede
echo "[3/3] Conectando à Internet..."
rm -f /tmp/cf_tunnel.log
./cloudflared tunnel --edge-ip-version 4 --protocol http2 --url http://127.0.0.1:8000 > /tmp/cf_tunnel.log 2>&1 &
TUNNEL_PID=$!

cleanup() {
    echo ""
    echo "Encerrando servidores..."
    kill $REVERB_PID 2>/dev/null || true
    kill $SERVE_PID 2>/dev/null || true
    kill $TUNNEL_PID 2>/dev/null || true
    fuser -k 8085/tcp 8000/tcp 2>/dev/null || true
    exit 0
}

trap cleanup SIGINT SIGTERM

echo -n "Gerando link público seguro..."
PUBLIC_URL=""
for i in {1..20}; do
    sleep 1
    echo -n "."
    PUBLIC_URL=$(grep -o 'https://[-a-zA-Z0-9.]*\.trycloudflare\.com' /tmp/cf_tunnel.log 2>/dev/null | head -n 1)
    if [ -n "$PUBLIC_URL" ]; then
        break
    fi
done

echo ""
echo ""
echo "=========================================================="
echo "🎉 O JOGO ESTÁ 100% ONLINE E PRONTO PARA JOGAR!"
echo "=========================================================="
if [ -n "$PUBLIC_URL" ]; then
    echo "👉 ACESSE ESTE LINK NA TV E NOS CELULARES:"
    echo ""
    echo "   $PUBLIC_URL"
    echo ""
else
    echo "👉 Link Local: http://localhost:8000"
fi
echo "📱 Qualquer pessoa em qualquer lugar agora consegue entrar!"
echo "Pressione Ctrl+C para parar os servidores quando terminar."
echo "=========================================================="

wait
