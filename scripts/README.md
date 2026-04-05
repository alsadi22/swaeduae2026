# Scripts

## `ci-full.sh`

Runs the same checks as **GitHub Actions** `tests.yml` (from the `app/` root):

1. `composer ci` — `composer validate --strict`, `composer audit`, Laravel Pint (`--test`), `php artisan test`
2. `npm ci --no-progress`, `npm audit`, and `npm run build`

Invoke via **`composer ci:full`** or:

```bash
./scripts/ci-full.sh
```

Requires **bash**, **PostgreSQL** with **`swaeduae_testing`** (see `Documents/Developer-README.md`).

## `deploy-on-server.sh`

Run **on the production server** from the Laravel app root **after** `git pull` (see `.github/workflows/deploy.yml` for an optional GitHub Actions SSH deploy):

```bash
./scripts/deploy-on-server.sh
```

Runs `composer install --no-dev`, `php artisan migrate --force`, clears and rebuilds Laravel caches. `public/build` is tracked in git, so you do not need Node on the server unless you change that policy.

## `deploy-via-ssh.sh` (Option B — from your laptop / Cursor)

Needs **`Host swaed-prod`** in **`~/.ssh/config`** (with your real server IP and SSH user) and your **`~/.ssh/id_ed25519_swaeduae2026.pub`** in that user’s **`authorized_keys`** on the server.

```bash
export SWAED_REMOTE_PATH=/full/path/to/laravel-on-server
./scripts/deploy-via-ssh.sh
```

Runs `ssh swaed-prod`, then `git pull origin master` and **`deploy-on-server.sh`** on the server.
