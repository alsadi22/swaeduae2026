#!/usr/bin/env bash
# Sync this repo to the nginx document-root app (default /var/www/swaeduae/app) and run deploy-on-server.sh.
# Preserves the live .env on the target. Run on the server after git pull in your working copy.
#
#   SWAED_SOURCE=/home/swaeduae/app SWAED_WWW_ROOT=/var/www/swaeduae/app ./scripts/publish-to-docroot.sh
#
set -euo pipefail
SOURCE="${SWAED_SOURCE:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}"
TARGET="${SWAED_WWW_ROOT:-/var/www/swaeduae/app}"

if [[ ! -f "${SOURCE}/artisan" ]]; then
  echo "publish-to-docroot: source missing artisan: ${SOURCE}" >&2
  exit 1
fi

echo "==> rsync ${SOURCE} -> ${TARGET}"
rsync -a --delete \
  --exclude '.git/' \
  --exclude '.env' \
  --exclude 'storage/logs/' \
  --exclude 'node_modules/' \
  --exclude '.phpunit.cache' \
  "${SOURCE}/" "${TARGET}/"

echo "==> web-writable dirs (PHP-FPM typically runs as www-data)"
chmod -f a+rw "${TARGET}/database/database.sqlite" 2>/dev/null || true
chmod -R a+rwX "${TARGET}/storage/framework" "${TARGET}/bootstrap/cache" 2>/dev/null || true

rm -f "${TARGET}/public/hot"

echo "==> deploy-on-server in target"
( cd "${TARGET}" && chmod +x scripts/deploy-on-server.sh && ./scripts/deploy-on-server.sh )

echo "publish-to-docroot: done. Reload php8.3-fpm if needed: sudo systemctl reload php8.3-fpm"
