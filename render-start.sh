#!/usr/bin/env bash
set -e

mkdir -p /var/www/html/database /var/www/html/storage/app/public /var/www/html/storage/app/private
touch "${DB_DATABASE:-/var/www/html/database/database.sqlite}"

chown -R www-data:www-data /var/www/html/database /var/www/html/storage /var/www/html/bootstrap/cache

if [ -n "$AI_SERVICE_URL" ] && [[ "$AI_SERVICE_URL" != http://* ]] && [[ "$AI_SERVICE_URL" != https://* ]]; then
    export AI_SERVICE_URL="http://$AI_SERVICE_URL"
fi

php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan migrate --force
php artisan storage:link || true

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground
