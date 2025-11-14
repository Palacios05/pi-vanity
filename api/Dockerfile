# ------------------------------------------
# Etapa 1: Construcción con Composer
# ------------------------------------------
FROM php:8.2-fpm AS builder

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo_pgsql pdo_mysql mbstring zip gd

# Instalar Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Copiar el código
WORKDIR /var/www/html
COPY . .

# Instalar dependencias de Laravel
RUN composer install --no-dev --optimize-autoloader

# Generar APP_KEY si no existe
RUN php artisan key:generate --ansi

# ------------------------------------------
# Etapa 2: Imagen final con Nginx + PHP-FPM
# ------------------------------------------
FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y nginx libpq-dev \
    && docker-php-ext-install pdo_pgsql

# Copiar la aplicación construida
COPY --from=builder /var/www/html /var/www/html

# Copiar configuración de Nginx
COPY api/nginx.conf /etc/nginx/conf.d/default.conf

# Permisos para storage y cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD service nginx start && php-fpm
