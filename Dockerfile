# Setup Dockerfile para Render / Railway / Fly.io / VPS
FROM php:8.3-fpm-alpine

# Dependências do sistema (incluindo linux-headers que contém o sock_diag.h)
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    git \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    bash \
    icu-dev \
    mysql-client \
    linux-headers \
    && docker-php-ext-install pdo pdo_mysql intl pcntl bcmath sockets

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copia arquivos do projeto
COPY . .

# Instala dependências sem dev
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permissões do storage e cache do Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Configuração do Nginx
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Configuração do Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Script de entrada
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80 8085

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
