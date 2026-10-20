#!/usr/bin/env sh
set -e

# Default PORT to 8080 if not supplied by Render or Railway
export PORT="${PORT:-8080}"

echo "[Entrypoint] Configuring Nginx to listen on port ${PORT}..."
mkdir -p /etc/nginx/sites-available /etc/nginx/sites-enabled
sed "s/PORT_PLACEHOLDER/${PORT}/g" /etc/nginx/templates/nginx.conf.template > /etc/nginx/sites-available/default
ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Detect Render external domain if APP_URL is not manually set
if [ -n "$RENDER_EXTERNAL_URL" ] && [ -z "$APP_URL" ]; then
    echo "[Entrypoint] Detected Render URL: ${RENDER_EXTERNAL_URL}"
    export APP_URL="${RENDER_EXTERNAL_URL}"
fi

# Ensure storage symlink exists
if [ ! -d "public/storage" ]; then
    echo "[Entrypoint] Creating storage symlink..."
    php artisan storage:link --force || true
fi

# Ensure an application key exists
if [ -z "$APP_KEY" ]; then
    if [ ! -f ".env" ]; then
        touch .env
    fi
    if ! grep -q "^APP_KEY=" .env 2>/dev/null; then
        echo "[Entrypoint] Generating application key..."
        php artisan key:generate --force || true
    fi
fi

# Clear stale caches before caching
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Cache Laravel optimizations
echo "[Entrypoint] Caching Laravel configuration, routes, and views..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Prepare SQLite database file if DB_CONNECTION is sqlite (or not set)
DB_CONN="${DB_CONNECTION:-sqlite}"
if [ "$DB_CONN" = "sqlite" ]; then
    if [ ! -f "database/database.sqlite" ]; then
        echo "[Entrypoint] Initializing SQLite database file..."
        touch database/database.sqlite
    fi
    chown -R www-data:www-data database storage bootstrap/cache || true
    chmod -R 777 database storage bootstrap/cache || true
fi

# Run database migrations with retry (useful while cloud Postgres is spinning up)
echo "[Entrypoint] Running database migrations..."
MAX_TRIES=5
COUNT=0
until php artisan migrate --force || [ $COUNT -ge $MAX_TRIES ]; do
    COUNT=$((COUNT+1))
    if [ $COUNT -lt $MAX_TRIES ]; then
        echo "[Entrypoint] Database migration failed. Retrying in 3 seconds ($COUNT/$MAX_TRIES)..."
        sleep 3
    fi
done

# Ensure the admin user exists
echo "[Entrypoint] Ensuring Admin user exists..."
php artisan db:seed --class=AdminUserSeeder --force || true

# If SEED_DATABASE is true, run complete database seeder
if [ "${SEED_DATABASE:-false}" = "true" ]; then
    echo "[Entrypoint] Seeding full database with demo data..."
    php artisan db:seed --force || true
fi

# Ensure runtime write permissions
chown -R www-data:www-data database storage bootstrap/cache || true
chmod -R 777 database storage bootstrap/cache || true

echo "[Entrypoint] Starting PHP-FPM..."
php-fpm -D

echo "[Entrypoint] Starting Nginx on port ${PORT}..."
exec nginx -g 'daemon off;'
