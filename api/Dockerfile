
# Fase 1: dependencias de Composer
FROM php:8.2-fpm AS builder

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libpq-dev libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libzip-dev zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql mbstring zip gd \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html/api

COPY api/composer.json api/composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY api/ ./

RUN composer dump-autoload --no-dev --optimize \
    && php artisan package:discover --ansi


# Fase 2: producción
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx libpq-dev libpng16-16 libjpeg62-turbo libfreetype6-dev \
    libzip-dev libonig-dev \
    && docker-php-ext-install pdo_pgsql mbstring zip \
    && rm -f /etc/nginx/sites-enabled/default \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html/api

COPY --from=builder /var/www/html/api /var/www/html/api
COPY api/nginx.conf /etc/nginx/conf.d/default.conf

RUN mkdir -p storage/framework/cache storage/framework/sessions \
    storage/framework/views storage/logs bootstrap/cache \
    && ln -sfn /var/www/html/api/storage/app/public /var/www/html/api/public/storage \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && nginx -t

EXPOSE 80

CMD ["sh", "-c", "php artisan migrate --force && php-fpm -D && exec nginx -g 'daemon off;'"]