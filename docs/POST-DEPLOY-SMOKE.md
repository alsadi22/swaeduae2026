# Post-deploy smoke checklist

Run after **`git pull`** and **`./scripts/deploy-on-server.sh`** (from the Laravel app root). Confirm **HTTP 200** and no obvious errors in the browser console.

| Check | URL / action |
|-------|----------------|
| Home | `/` |
| Volunteer opportunities | `/volunteer/opportunities` |
| Login | `/login` (or locale-prefixed equivalent) |
| One public org event | Open any published event detail from listings |
| One checkpoint link | Open a volunteer checkpoint URL (QR / token flow) as documented in the app |
| Queue worker | **`systemctl --user status laravel-queue-swaeduae.service`** (or your unit) shows **active (running)** when `QUEUE_CONNECTION` is not `sync` |

## Build machines (Node)

Use **Node 22+** for **`npm ci`** / **`npm run build`** so it matches **`package.json` `engines`**, **`.node-version`**, and the **Frontend** job in **`.github/workflows/tests.yml`**. Commit updated **`public/build`** when Vite assets change.

Full production `.env` and ops notes: **[../Documents/PRODUCTION-ENV.md](../Documents/PRODUCTION-ENV.md)** (when this repo sits next to `Documents/`).
