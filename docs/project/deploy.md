# Deploy (FTP-only hosting)

The hosting gives FTP but no SSH, and its network blocks FTP from GitHub's runners, so
the deploy runs **from the developer's machine** with one command:

```bash
php artisan deploy:ftp
```

`Core\Console\Commands\DeployFtpCommand`:

1. reads on the server (`storage/app/.deploy-revision`) which commit was deployed last;
2. uploads the files changed between that commit and `HEAD` (`git diff`), one FTP
   connection at a time — the hosting allows at most **5 connections per IP** and silently
   drops the rest. A dropped connection is reopened and the file retried;
3. writes `HEAD` into `.deploy-revision` — only after every file went up, so an
   interrupted deploy is just run again;
4. calls `POST /api/deploy` with the `X-Deploy-Token` header, which runs on the server
   (`Core\Services\PostDeployService`): `config:clear`, `route:clear`, `view:clear`,
   `event:clear`, `migrate --force`, `cache:clear`, `storage:link` (if `public/storage`
   is missing), `modules:cache`, `route:cache`, `view:cache`, and prints every step.

`migrate` needs the database user to have the structure privileges: cPanel → MySQL
Databases → the user's privileges → CREATE, ALTER, DROP, INDEX, REFERENCES (and LOCK
TABLES for importing dumps through phpMyAdmin). Without them a migration fails with
`1142 CREATE command denied`.

Only committed files are deployed: with uncommitted changes the command stops (`--force`
overrides). Files removed or renamed in the repository are deleted on the server after
the upload (`--keep-removed` keeps them).
Never uploaded: `vendor/`, `.env`, anything untracked, and the prefixes in
`config('deploy.exclude')` (`tests/`, `docs/`, `design/`, `.github/`, …).

The one untracked exception is `config('deploy.assets')` — the admin panel assets composer
publishes into `public/css|js|fonts/filament` and `public/css|js/app`. They are compared by
md5 with the last upload (remembered in `storage/app/.deploy-assets.json` on the server)
and only the changed ones go up — after `composer update` they change on their own.

Options: `--dry-run` (only list the files), `--full` (every tracked file),
`--since=<commit>` (changes since that commit), `--skip-post` (no `/api/deploy`).

`DEPLOY_TOKEN` empty in the server `.env` — `/api/deploy` answers 404.

---

## Local `.env`

```
DEPLOY_FTP_HOST=46.4.4.3
DEPLOY_FTP_PORT=21
DEPLOY_FTP_USERNAME=noziAuto@nosi.com.ua
DEPLOY_FTP_PASSWORD=
DEPLOY_FTP_ROOT=/
DEPLOY_URL=https://nosi.com.ua
DEPLOY_REMOTE_TOKEN=
```

`DEPLOY_FTP_ROOT` — the project folder relative to the FTP login (the one holding
`artisan`); for an FTP user whose home is `public_html` it is `/`.
`DEPLOY_REMOTE_TOKEN` — the `DEPLOY_TOKEN` of the **server** `.env`. The local
`DEPLOY_TOKEN` stays empty, so the local site has no deploy endpoint.

---

## vendor/ — uploaded by hand

FTP uploads file by file, and `vendor/` is tens of thousands of files. It is not in git,
so `deploy:ftp` never uploads it; it goes up as one archive:

1. Locally, in the project root, pack `vendor/` into `vendor.zip` (the Filament assets in
   `public/` are uploaded by `deploy:ftp` itself).
2. Upload `vendor.zip` over FTP into the project folder (next to `artisan`).
3. cPanel → File Manager → right click on `vendor.zip` → Extract, then delete the archive.

Do it once on the first deploy, and again **whenever `composer.lock` changes** —
`deploy:ftp` warns about it.

---

## First deploy, once

1. **Database.** Create it in cPanel with collation `utf8mb4_unicode_ci`.
2. **`.env` on the server.** Upload it over FTP into the project folder (next to `artisan`):
   `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://nosi.com.ua`,
   `APP_CANONICAL_URL=https://nosi.com.ua`, `LOG_LEVEL=error`, `APP_KEY` (from local
   `php artisan key:generate --show`), `ADMIN_PATH`, `DEPLOY_TOKEN`, `DB_*` from cPanel.
   `APP_CANONICAL_URL` makes every other address of the site (http, www, `/index.php`,
   `/public/…`, a trailing slash) answer with a 301 to it
   (`App\Modules\Core\Http\Middleware\RedirectToCanonicalUrl`), and canonical / og:url /
   sitemap links use it. Without it these duplicates are served as separate pages.
3. **Document root.** Point the domain at the project's `public/` folder in cPanel
   (Domains → Document Root). If cPanel does not allow it for the main domain, upload the
   project into `public_html` itself — the root `.htaccess` then sends every request into
   `public/`.
4. **vendor/** — upload the archive by hand (see above).
5. `php artisan deploy:ftp` — the server has no revision yet, so everything is uploaded.
6. **Demo data** — import a dump of the local database through phpMyAdmin, upload
   `storage/app/public/` (the images the seeders created), then run `deploy:ftp` again:
   the route cache has to be rebuilt once the `pages` table is filled.

If `storage:link` reports a failure (the hosting forbids symlinks), images uploaded through
the admin panel will not open — ask the hosting support to create the link
`public/storage` → `storage/app/public`.
