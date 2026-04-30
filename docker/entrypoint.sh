#!/usr/bin/env bash
set -e

cd /var/www/html

# Discover service providers (skipped in build because env vars weren't set yet)
php artisan package:discover --ansi || true

# Cache config / routes / views (idempotent)
php artisan config:cache
php artisan route:cache || true
php artisan view:cache || true

# Run migrations on container start. Cloud Run will run this for the first
# revision; subsequent revisions will see no pending migrations.
php artisan migrate --force || echo "Migrations failed; continuing so the container still serves traffic."

exec "$@"
