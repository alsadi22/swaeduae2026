# SwaedUAE — Laravel application

This directory is the **Laravel codebase** (Composer, Vite, `artisan`, `routes/`, `app/`, etc.).

## Repository & production deploy

- **GitHub:** [github.com/alsadi22/swaeduae2026](https://github.com/alsadi22/swaeduae2026) (branch **`master`**).
- **On the server** (after `git pull` in this directory): `./scripts/deploy-on-server.sh` — see comments in that script and **[../Documents/PRODUCTION-ENV.md](../Documents/PRODUCTION-ENV.md)** (also: optional GitHub Actions **Deploy to production** in `.github/workflows/deploy.yml`).

## Documentation

All product specs, architecture, design notes, and the full developer guide live in one place:

**[../Documents/README.md](../Documents/README.md)**

Quick links from there:

- [docs/STAKEHOLDER-READINESS.md](docs/STAKEHOLDER-READINESS.md) — short readiness snapshot (in-repo)
- [docs/POST-DEPLOY-SMOKE.md](docs/POST-DEPLOY-SMOKE.md) — post-deploy checks + Node 22 build note
- [Developer-README.md](../Documents/Developer-README.md) — setup, tests, stack, where UI tokens live (run commands from this `app/` folder)
- [PRD-swaeduae-bilingual-volunteer-attendance.md](../Documents/PRD-swaeduae-bilingual-volunteer-attendance.md)
- [TechnicalArchitecture-swaeduae-laravel-monolith.md](../Documents/TechnicalArchitecture-swaeduae-laravel-monolith.md)
- [PageDesign-swaeduae-mobile-first.md](../Documents/PageDesign-swaeduae-mobile-first.md)

## Quick start

```bash
cd app
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

## Quality checks (before merge / deploy)

From **`app/`** (Postgres + `swaeduae_testing` must exist — see **[Developer-README.md](../Documents/Developer-README.md)**):

```bash
composer ci:full        # full mirror: validate + audits + Pint + tests + npm ci/audit/build
composer ci             # validate + composer audit + Pint + php artisan test
composer check-composer # composer.json / lock only
composer composer-audit # Composer advisory check only
```

---

*The former `docs/` folder only contains a redirect to `Documents/` for old bookmarks.*
