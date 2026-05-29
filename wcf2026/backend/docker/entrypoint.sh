#!/usr/bin/env bash
set -euo pipefail

if [ "$#" -gt 0 ]; then
    exec "$@"
fi

php artisan config:cache
php artisan route:cache
php artisan storage:link || true

nginx -g 'daemon off;' &
exec php-fpm
