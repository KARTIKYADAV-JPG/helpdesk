# Stage 1: Build Frontend Assets using Node.js
FROM node:20-slim AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Stage 2: Production PHP-FPM + Nginx Environment
FROM php:8.4-fpm-bookworm

# Install Nginx and system utilities
RUN apt-get update && apt-get install -y \
    nginx \
    gettext-base \
    zip \
    unzip \
    git \
    curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install official PHP extension installer helper
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Install PHP extensions required by Laravel & webklex/laravel-imap
RUN install-php-extensions \
    pdo_pgsql \
    pdo_mysql \
    bcmath \
    gd \
    zip \
    opcache \
    imap

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy codebase
COPY . .

# Copy compiled frontend assets from Node stage
COPY --from=assets /app/public/build ./public/build

# Ensure storage and cache directories exist and are writable before composer package discovery
RUN mkdir -p bootstrap/cache storage/framework/cache storage/framework/sessions storage/framework/views database \
    && chmod -R 777 bootstrap/cache storage database

# Install PHP dependencies without dev packages
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Copy Nginx template configuration
COPY docker/nginx.conf.template /etc/nginx/templates/nginx.conf.template

# Copy and grant execute permissions to entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Set correct permissions for Laravel storage and cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Default port exposed by Railway
EXPOSE 8080

ENTRYPOINT ["docker-entrypoint.sh"]

