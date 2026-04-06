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

## `publish-to-docroot.sh` (same server: Git working copy → nginx app root)

When nginx’s **`root`** is **`/var/www/.../public`** but you develop in **`/home/.../app`**, run this **on the server** after `git pull` in your working copy so the live docroot matches the repo (your **`/var/www/.../.env` is not overwritten**):

```bash
./scripts/publish-to-docroot.sh
```

Optional: `SWAED_SOURCE` and `SWAED_WWW_ROOT` override paths. Then reload PHP-FPM if your host caches opcode (`sudo systemctl reload php8.3-fpm`).

## Zoho SMTP (production `.env` on the server only)

1. Create / use the **`noreply@swaeduae.ae`** mailbox in Zoho and an **app-specific password** (or SMTP password Zoho shows).
2. On the server, edit **`/var/www/.../.env`**: set **`MAIL_PASSWORD=`** to that secret, then **`MAIL_MAILER=smtp`** (keep **`MAIL_HOST=smtp.zoho.com`**, **`MAIL_PORT=587`**, **`MAIL_USERNAME=noreply@swaeduae.ae`**).
3. Run **`php artisan config:cache`** in the app root.

Until then, **`MAIL_MAILER=log`** keeps mail in **`storage/logs`** (no outbound delivery).

## Queue worker (`QUEUE_CONNECTION=redis`)

- **Recommended (system, survives reboot, runs as `www-data`):**  
  `sudo cp scripts/systemd/laravel-queue-swaeduae.service /etc/systemd/system/` → `daemon-reload` → `enable --now` (see comments in that file).
- **User service (no sudo):** a unit under **`~/.config/systemd/user/`** works while you stay logged in; for 24/7 without login run **`sudo loginctl enable-linger $USER`**.

After each deploy, **`deploy-on-server.sh`** runs **`php artisan queue:restart`** so workers pick up new code.

## Laravel scheduler (hourly RSS fetch, etc.)

The app registers tasks in **`bootstrap/app.php`** (`withSchedule`). To run them in production you need **one** of:

- **systemd timer (recommended):** copy **`scripts/systemd/laravel-schedule-swaeduae.service`** and **`.timer`** to **`/etc/systemd/system/`**, then `daemon-reload`, `enable --now laravel-schedule-swaeduae.timer`. Adjust **`WorkingDirectory`** if your deploy path is not **`/var/www/swaeduae/app`**.
- **Cron:** `* * * * * cd /var/www/swaeduae/app && php artisan schedule:run >> /dev/null 2>&1`
