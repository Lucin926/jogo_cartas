#!/bin/sh
set -e

echo "==> Executando cache de configuração e rotas..."
php /var/www/artisan config:clear || true
php /var/www/artisan view:clear || true

echo "==> Executando migrations e seeders do banco de dados na nuvem..."
php /var/www/artisan migrate --force || true
php /var/www/artisan db:seed --class=GameDeckSeeder --force || true

echo "==> Iniciando Nginx, PHP-FPM e Reverb via Supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
