#!/bin/bash

# Install dependencies if vendor directory doesn't exist (useful for volumes)
if [ ! -d "/var/www/html/vendor" ]; then
    echo "Instalando dependencias de Composer..."
    composer install --no-interaction --no-progress
fi

if [ ! -d "/var/www/html/node_modules" ] || [ ! -d "/var/www/html/public/build" ]; then
    echo "Instalando dependencias de NPM y compilando assets..."
    npm install
    npm run build
fi

# Ensure storage link exists
php artisan storage:link --quiet

# Clear and cache configurations for production
php artisan config:clear
php artisan route:clear
php artisan view:clear

# We do not run migrate:fresh here to protect production data.
# Run safe migrations.
php artisan migrate --force

# Start php-fpm in foreground
php-fpm

