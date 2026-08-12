#!/bin/bash

# Ensure storage link exists
php artisan storage:link --quiet

# Clear and cache configurations for production
php artisan config:clear
php artisan route:clear
php artisan view:clear

# We do not run migrate:fresh here to protect production data.
# Run safe migrations.
php artisan migrate --force

# Start php-fpm in background
php-fpm -D

# Start Nginx in foreground
nginx -g "daemon off;"
