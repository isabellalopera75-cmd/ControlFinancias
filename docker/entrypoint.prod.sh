#!/bin/bash

# Asegurar que el enlace simbólico de storage exista
php artisan storage:link --quiet

# Limpiar y cachear configuraciones para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# (Opcional) Ejecutar migraciones automáticamente de forma segura
php artisan migrate --force

# Arreglar permisos en caso de que algún comando de artisan (como migraciones) haya creado archivos como root
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Iniciar php-fpm en segundo plano
php-fpm -D

# Iniciar Nginx en primer plano
nginx -g "daemon off;"
