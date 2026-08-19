#!/bin/sh
set -e

echo "Running Laravel Artisan commands ..."

php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# ✅ Production-safe: applies new migrations only
 php artisan migrate --force

echo "Fixing storage permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

exec /init
