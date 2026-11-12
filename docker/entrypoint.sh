#!/bin/sh
set -e

echo "==> Executando cache de configuração e rotas..."
php /var/www/artisan config:clear || true
php /var/www/artisan view:clear || true

# Inicia o Supervisor imediatamente para abrir a porta 80 e evitar o timeout do Render
echo "==> Iniciando Nginx, PHP-FPM e Reverb via Supervisord..."
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf &
SUPERVISOR_PID=$!

# Aguarda 3 segundos para garantir que o Nginx subiu e o Render detectou a porta aberta
sleep 3

# Executa migrações e seeders em segundo plano após o Nginx já estar escutando
echo "==> Executando migrations e seeders do banco de dados na nuvem..."
php /var/www/artisan migrate --force || true
php /var/www/artisan db:seed --class=GameDeckSeeder --force || true

# Mantém o processo principal no Supervisor
wait $SUPERVISOR_PID
