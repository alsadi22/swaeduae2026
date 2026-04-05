#!/usr/bin/env bash
# Deploy production over SSH (Option B). Requires ~/.ssh/config Host "swaed-prod".
#
# One-time: export your Laravel root on the server, e.g. in ~/.bashrc:
#   export SWAED_REMOTE_PATH=/var/www/swaeduae2026
#
# Then from this repo (app/):
#   ./scripts/deploy-via-ssh.sh
#
set -euo pipefail
: "${SWAED_REMOTE_PATH:?Set SWAED_REMOTE_PATH to the Laravel root on the server (directory that contains artisan)}"

exec ssh -o BatchMode=yes swaed-prod "set -euo pipefail; cd '${SWAED_REMOTE_PATH}' && git pull origin master && chmod +x scripts/deploy-on-server.sh && ./scripts/deploy-on-server.sh"
