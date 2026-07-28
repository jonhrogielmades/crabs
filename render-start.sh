#!/usr/bin/env bash
set -e

mkdir -p \
    "$(dirname "${DB_DATABASE:-/var/www/html/database/database.sqlite}")" \
    "${LOCAL_FILESYSTEM_ROOT:-/var/www/html/storage/app/private}" \
    "${PUBLIC_FILESYSTEM_ROOT:-/var/www/html/storage/app/public}" \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs
touch "${DB_DATABASE:-/var/www/html/database/database.sqlite}"

chown -R www-data:www-data \
    "$(dirname "${DB_DATABASE:-/var/www/html/database/database.sqlite}")" \
    "${LOCAL_FILESYSTEM_ROOT:-/var/www/html/storage/app/private}" \
    "${PUBLIC_FILESYSTEM_ROOT:-/var/www/html/storage/app/public}" \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

if [ -n "$PORT" ]; then
    sed -ri -e "s/^Listen 80$/Listen $PORT/" /etc/apache2/ports.conf
    sed -ri -e "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/" /etc/apache2/sites-available/*.conf
fi

if [ -n "$RENDER_EXTERNAL_HOSTNAME" ]; then
    export APP_URL="${APP_URL:-https://$RENDER_EXTERNAL_HOSTNAME}"
    export ASSET_URL="${ASSET_URL:-https://$RENDER_EXTERNAL_HOSTNAME}"
fi

if [ -z "$APP_KEY" ]; then
    export APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
elif [[ "$APP_KEY" != base64:* ]]; then
    export RENDER_RAW_APP_KEY="$APP_KEY"
    export APP_KEY="base64:$(php -r 'echo base64_encode(hash("sha256", getenv("RENDER_RAW_APP_KEY"), true));')"
    unset RENDER_RAW_APP_KEY
fi

if [ -n "$AI_SERVICE_URL" ] && [[ "$AI_SERVICE_URL" != http://* ]] && [[ "$AI_SERVICE_URL" != https://* ]]; then
    export AI_SERVICE_URL="http://$AI_SERVICE_URL"
fi

php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan migrate --force
php artisan db:seed --force
php artisan storage:link || true

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground
