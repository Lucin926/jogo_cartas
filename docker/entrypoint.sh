#!/bin/sh
set -e

# Garante permissões de escrita nos diretórios de cache e logs
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Garante que a chave da aplicação exista
if [ -z "$APP_KEY" ]; then
    export APP_KEY="base64:3x9OLFKU17D8FqpiT16CkaVMUjwGZTFTYL4X/uXr3QQ="
fi

echo "==> Executando cache de configuração e rotas..."
php /var/www/artisan config:clear || true
php /var/www/artisan view:clear || true

# Inicia o Supervisor imediatamente para o Nginx abrir a porta 80
echo "==> Iniciando Nginx, PHP-FPM e Reverb via Supervisord..."
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf &
SUPERVISOR_PID=$!

sleep 2

# Executa migrações e seeders
echo "==> Executando migrations e seeders do banco de dados na nuvem..."
php /var/www/artisan migrate --force || true
php /var/www/artisan db:seed --class=GameDeckSeeder --force || true

wait $SUPERVISOR_PID
