# Fase 1: Construcción de Laravel + Composer
FROM php:8.2-fpm AS builder

# Dependencias del sistema
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libzip-dev zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql pdo_mysql mbstring zip gd

# Copiar Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo CORRECTO
WORKDIR /var/www/html/api

# Copiar SOLO la carpeta api
COPY api /var/www/html/api

# Instalar dependencias de Laravel
RUN composer install --no-dev --optimize-autoloader

# Generar clave
RUN php artisan key:generate --ansi


# Fase 2: Producción (Laravel + Nginx)
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y nginx libpq-dev \
    && docker-php-ext-install pdo_pgsql

# Copiar Laravel ya construido
COPY --from=builder /var/www/html /var/www/html

# Copiar nginx.conf desde api/
COPY api/nginx.conf /etc/nginx/conf.d/default.conf

# Permisos
RUN chown -R www-data:www-data /var/www/html/api/storage /var/www/html/api/bootstrap/cache

EXPOSE 80

# Iniciar PHP y Nginx
CMD service nginx start && php-fpm
