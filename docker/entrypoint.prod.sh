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

# Iniciar php-fpm en segundo plano
php-fpm -D

# Iniciar Nginx en primer plano
nginx -g "daemon off;"
