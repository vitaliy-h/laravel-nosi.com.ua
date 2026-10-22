# Deploy (FTP-only hosting)

The hosting gives FTP but no SSH, so nothing can be run on the server by hand.
`.github/workflows/deploy.yml` does the work instead, on every push to `main` / `master`
(or by hand: Actions → Deploy → Run workflow):

1. installs the Composer dependencies on GitHub (`--no-dev`);
2. uploads the project over FTP — only changed files, the state is kept in
   `.ftp-deploy-sync-state.json` on the server. Files the action did not upload itself
   (the server `.env`, logs, images uploaded through the admin panel) are never touched;
3. calls `POST /api/deploy` with the `X-Deploy-Token` header, which runs on the server
   (`Core\Services\PostDeployService`): `optimize:clear`, `migrate --force`,
   `storage:link` (if `public/storage` is missing), `modules:cache`, `route:cache`,
   `view:cache`. The response lists every command with its output; a failed step fails
   the workflow.

`DEPLOY_TOKEN` empty in `.env` — the endpoint answers 404.

---

## GitHub secrets

Settings → Secrets and variables → Actions → New repository secret:

| Secret | Value |
|---|---|
| `FTP_SERVER` | FTP host from cPanel, e.g. `ftp.nosi.com.ua` |
| `FTP_USERNAME` | FTP login |
| `FTP_PASSWORD` | FTP password |
| `FTP_SERVER_DIR` | Folder of the project on the FTP, ending with `/` — the one that will hold `artisan`, e.g. `/public_html/` |
| `DEPLOY_URL` | Site address without a trailing slash: `https://nosi.com.ua` |
| `DEPLOY_TOKEN` | Long random string, the same as `DEPLOY_TOKEN` in the server `.env` |

The workflow connects with FTPS (`protocol: ftps`). If the hosting has no FTPS, change it
to `ftp` in `deploy.yml`.

---

## First deploy, once

1. **Database.** Create it in cPanel with collation `utf8mb4_unicode_ci`.
2. **`.env` on the server.** Upload it over FTP into the project folder (next to `artisan`):
   `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://nosi.com.ua`, `APP_KEY` (any
   `base64:` key, e.g. from local `php artisan key:generate --show`), `ADMIN_PATH`,
   `DEPLOY_TOKEN`, `DB_*` from cPanel.
3. **Document root.** Point the domain at the project's `public/` folder in cPanel
   (Domains → Document Root). If cPanel does not allow it for the main domain, upload the
   project into `public_html` itself — the root `.htaccess` then sends every request into
   `public/`.
4. **Push** (or Run workflow). The first upload takes long — the whole `vendor/` goes up.
5. **Demo data** — the seeders are not part of the deploy (they truncate tables). Either
   import a dump of the local database, or seed once: temporarily add `db:seed` to
   `PostDeployService::COMMANDS`, deploy, and remove it again.

If `storage:link` reports a failure (the hosting forbids symlinks), images uploaded through
the admin panel will not open — ask the hosting support to create the link
`public/storage` → `storage/app/public`.
