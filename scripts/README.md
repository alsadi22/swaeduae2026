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
