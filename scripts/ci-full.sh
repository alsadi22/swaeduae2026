#!/usr/bin/env bash
# Full local gate: matches GitHub Tests workflow (PHP lint + tests + Vite build).
# Requires Node 22+ (see app/.node-version and .npmrc engine-strict).
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"
composer ci
npm ci --no-progress
npm run build
echo "ci-full: OK"
