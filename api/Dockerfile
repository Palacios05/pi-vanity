# Fase 1: Construcción de Laravel + Composer
FROM php:8.2-fpm AS builder

RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libzip-dev zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql pdo_mysql mbstring zip gd

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html/api

COPY api /var/www/html/api

RUN composer install --no-dev --optimize-autoloader \
    && php artisan storage:link || true


# Fase 2: Producción
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y nginx libpq-dev \
    && docker-php-ext-install pdo_pgsql

COPY --from=builder /var/www/html /var/www/html

COPY api/nginx.conf /etc/nginx/conf.d/default.conf

RUN chmod -R 755 /var/www/html/api/public \
    && chmod -R 775 /var/www/html/api/storage \
    && chmod -R 775 /var/www/html/api/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD service nginx start && php-fpm
