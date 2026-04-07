# Post-deploy smoke checklist

Run after **`git pull`** and **`./scripts/deploy-on-server.sh`** (from the Laravel app root). Confirm **HTTP 200** and no obvious errors in the browser console.

| Check | URL / action |
|-------|----------------|
| Home | `/` |
| Volunteer opportunities | `/volunteer/opportunities` |
| Public IA sample | `/about`, `/programs`, `/events`, `/legal/terms`, `/sitemap.xml`, `/robots.txt` |
| Login | `/login` (or locale-prefixed equivalent) |
| Admin login surface | `/admin/login` (expect **200**) |
| One public org event | Open any published event detail from listings |
| One checkpoint link | Open a volunteer checkpoint URL (QR / token flow) as documented in the app |
| Queue worker | **`systemctl --user status laravel-queue-swaeduae.service`** (or your unit) shows **active (running)** when `QUEUE_CONNECTION` is not `sync` |

**Automated subset (PHPUnit):** from `app/`, run `php artisan test --filter=EndToEndFourPhaseChecklist` — covers many public routes and baseline security headers; it does **not** replace browser checks above.

**Automated subset (real browser):** Playwright hits the same core URLs as the table (except event detail, checkpoint, and queue — still manual). From `app/` with **Node 22+**:

```bash
# Staging or production (set your URL)
export PLAYWRIGHT_BASE_URL=https://your.hostname
npm ci
npx playwright install chromium
npm run test:e2e
```

```bash
# Local (separate terminal: php artisan serve, then)
npm run test:e2e
```

GitHub Actions runs this after the **Frontend** job via the **E2E browser smoke (Playwright)** job (Chromium, migrated DB, `php artisan serve`).

**Queue health (when not `sync`):** run `php artisan queue:failed` — should be empty after a clean deploy; see **`Documents/PRODUCTION-ENV.md`** § Queue failures.

## Build machines (Node)

Use **Node 22+** for **`npm ci`** / **`npm run build`** so it matches **`package.json` `engines`**, **`.node-version`**, **`.npmrc`** (`engine-strict=true`), and the **Frontend** / **E2E** jobs in **`.github/workflows/tests.yml`**. Commit updated **`public/build`** when Vite assets change.

Full production `.env` and ops notes: **[../Documents/PRODUCTION-ENV.md](../Documents/PRODUCTION-ENV.md)** (when this repo sits next to `Documents/`).
