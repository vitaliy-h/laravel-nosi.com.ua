# Deploy (FTP-only hosting)

The hosting gives FTP but no SSH, so nothing can be run on the server by hand.
`.github/workflows/deploy.yml` does the work instead, on every push to `main` / `master`
(or by hand: Actions → Deploy → Run workflow):

1. uploads the project with `lftp mirror --reverse --only-newer`: file times are restored
   from git, so only files changed since the last upload go up. lftp reconnects when the
   hosting drops the connection, and a rerun continues where the previous one stopped.
   Nothing is deleted on the server (the server `.env`, `vendor/`, logs, images uploaded
   through the admin panel stay); files removed from the repository just stay unused;
2. calls `POST /api/deploy` with the `X-Deploy-Token` header, which runs on the server
   (`Core\Services\PostDeployService`): `optimize:clear`, `migrate --force`,
   `storage:link` (if `public/storage` is missing), `modules:cache`, `route:cache`,
   `view:cache`. The response lists every command with its output; a failed step fails
   the workflow.

`DEPLOY_TOKEN` empty in `.env` — the endpoint answers 404.

---

## vendor/ — uploaded by hand

FTP uploads file by file, and `vendor/` is tens of thousands of files — hours per deploy.
So the workflow skips it (and the Filament assets `composer` publishes into `public/`),
and they go up by hand as one archive:

1. Locally, in the project root, pack `vendor/`, `public/js/filament/`,
   `public/css/filament/`, `public/fonts/filament/` into `vendor.zip`.
2. Upload `vendor.zip` over FTP into the project folder (next to `artisan`).
3. cPanel → File Manager → right click on `vendor.zip` → Extract, then delete the archive.

Do it once on the first deploy, and again **whenever `composer.lock` changes**
(`composer require` / `composer update`) — otherwise the server runs the old packages.

---

## GitHub secrets

Settings → Secrets and variables → Actions → New repository secret:

| Secret | Value |
|---|---|
| `FTP_SERVER` | FTP host, e.g. `46.4.4.3` |
| `FTP_USERNAME` | FTP login, e.g. `noziAuto@nosi.com.ua` |
| `FTP_PASSWORD` | FTP password |
| `FTP_SERVER_DIR` | Folder of the project relative to the FTP login, ending with `/` — the one that will hold `artisan`. For an FTP user whose home is `public_html` — `/` |
| `DEPLOY_URL` | Site address without a trailing slash: `https://nosi.com.ua` |
| `DEPLOY_TOKEN` | Long random string, the same as `DEPLOY_TOKEN` in the server `.env` |

lftp uses FTPS when the server offers it (`ftp:ssl-allow true`). If the upload fails on
TLS, set `ftp:ssl-allow false` in `deploy.yml`.

---

## First deploy, once

1. **Database.** Create it in cPanel with collation `utf8mb4_unicode_ci`.
2. **`.env` on the server.** Upload it over FTP into the project folder (next to `artisan`):
   `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://nosi.com.ua`,
   `LOG_LEVEL=error`, `APP_KEY` (from local `php artisan key:generate --show`),
   `ADMIN_PATH`, `DEPLOY_TOKEN`, `DB_*` from cPanel.
3. **Document root.** Point the domain at the project's `public/` folder in cPanel
   (Domains → Document Root). If cPanel does not allow it for the main domain, upload the
   project into `public_html` itself — the root `.htaccess` then sends every request into
   `public/`.
4. **vendor/** — upload the archive by hand (see above).
5. **Push** (or Run workflow).
6. **Demo data** — the seeders are not part of the deploy (they truncate tables). Either
   import a dump of the local database, or seed once: temporarily add `db:seed` to
   `PostDeployService::COMMANDS`, deploy, and remove it again.

If `storage:link` reports a failure (the hosting forbids symlinks), images uploaded through
the admin panel will not open — ask the hosting support to create the link
`public/storage` → `storage/app/public`.
