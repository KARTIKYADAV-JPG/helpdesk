#!/usr/bin/env sh
set -e

# Default PORT to 8080 if not supplied by Railway
export PORT="${PORT:-8080}"

echo "[Entrypoint] Configuring Nginx to listen on port ${PORT}..."
sed "s/PORT_PLACEHOLDER/${PORT}/g" /etc/nginx/templates/nginx.conf.template > /etc/nginx/sites-available/default

# Create storage symlink if missing
if [ ! -d "public/storage" ]; then
    echo "[Entrypoint] Creating storage symlink..."
    php artisan storage:link --force || true
fi

# Run Laravel optimizations
echo "[Entrypoint] Caching Laravel config, routes, and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Prepare SQLite database file and permissions
if [ ! -f "database/database.sqlite" ]; then
    touch database/database.sqlite
fi
chown -R www-data:www-data database storage bootstrap/cache || true
chmod -R 777 database storage bootstrap/cache || true

# Run database migrations automatically
echo "[Entrypoint] Running database migrations..."
php artisan migrate --force || {
    echo "[Entrypoint] Warning: Database migration failed. Continuing startup..."
}

# Ensure write permissions for SQLite database, storage, and cache for www-data user
chown -R www-data:www-data database storage bootstrap/cache || true
chmod -R 777 database storage bootstrap/cache || true

echo "[Entrypoint] Starting PHP-FPM..."
php-fpm -D

echo "[Entrypoint] Starting Nginx on port ${PORT}..."
exec nginx -g 'daemon off;'
