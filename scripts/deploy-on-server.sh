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

# Vite: if public/hot exists, @vite points at the dev server (e.g. 127.0.0.1:5173) and the site loads with no CSS/JS.
rm -f public/hot

if [[ ! -f public/build/manifest.json ]]; then
  echo "deploy-on-server: ERROR — public/build/manifest.json is missing." >&2
  echo "  Without it, Laravel cannot emit correct asset URLs (page will look unstyled)." >&2
  echo "  Fix: on a machine with Node 22+, run: npm ci && npm run build" >&2
  echo "  Then commit and push public/build/ (this repo tracks built assets for production)." >&2
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

echo "==> Signal queue workers to restart after code deploy (graceful)"
php artisan queue:restart --no-interaction 2>/dev/null || true

echo "deploy-on-server: done. Reload PHP-FPM or your app runtime if your host requires it."
