#!/usr/bin/env bash
# Run on the production server from the Laravel app root (same directory as artisan),
# after the latest code is checked out (e.g. git pull).
#
# Usage:
#   chmod +x scripts/deploy-on-server.sh
#   ./scripts/deploy-on-server.sh
#
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

if [[ ! -f artisan ]]; then
  echo "deploy-on-server: artisan not found in $ROOT — run this script from the Laravel app root." >&2
  exit 1
fi

echo "==> Composer (production)"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Migrations"
php artisan migrate --force --no-interaction

echo "==> Clear and rebuild caches"
php artisan optimize:clear --no-interaction
php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction

echo "==> Optional: restart queue workers (uncomment if you use queues)"
# php artisan queue:restart --no-interaction

echo "deploy-on-server: done. Reload PHP-FPM or your app runtime if your host requires it."
