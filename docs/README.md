# Server Requirements & Local Setup

This project supports multiple local environments depending on your OS and preferences.

> **Artisan command convention:** examples across the docs default to `php artisan …`
> (OpenServer / local host). If you run the project through **Sail** (Docker), prefix
> every command with `./vendor/bin/sail`, e.g. `./vendor/bin/sail artisan migrate`.

---

## Server Requirements

* PHP >= 8.4 (minimum)
* Composer
* Node.js
* MySQL 8+ / MariaDB
* Redis (optional)

---

## Installation Options

1. [🐧 Linux / macOS (Laravel Sail)](#linux-macos) — recommended
2. [🪟 Windows (Laravel Sail via WSL2)](#windows-wsl2)
3. [🪟 Windows (OpenServer — no Docker)](#windows-openserver)

---

## Default Admin Credentials

```
Login: admin
Password: password
```

---

<a id="admin-path"></a>

## ⚠️ Admin Panel URL — unique for every project

The admin panel URL prefix comes from `ADMIN_PATH` in `.env` (default `admin`, i.e.
`https://site.com/admin`). **Every time you deploy a new project from this starter, set a
unique, hard-to-guess value** — do not leave the default `admin` and do not reuse the value
from another project:

```
ADMIN_PATH=cp-7f3k9q
```

The panel then lives at `https://site.com/cp-7f3k9q`. Pick a value that does not clash with a
frontend page slug. After changing it on a server with cached config, run
`php artisan optimize:clear` (or `php artisan config:cache` again).

Tests always run with `ADMIN_PATH=admin` (forced in `phpunit.xml`), so the local value does not
affect them.

---

<a id="linux-macos"></a>

# 1. 🐧 Linux / macOS (Laravel Sail)

## 📋 Requirements

- Docker
- Docker Compose
- Git

---

## Clone the repository

```bash
git clone https://github.com/abc-cms/laravel-filament-starter.git
cd <project-folder>
```

---

## Environment setup

Copy `.env` file:

```bash
cp .env.example .env
```

Then set a unique `ADMIN_PATH` in `.env` — see [Admin Panel URL](#admin-path).

---

## Local install sail

This will create a temporary container, connect the current directory under which the command is run as a web folder,
install the necessary dependencies, and then you can run sail up as usual.

```bash 
docker run --rm \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```

---

## Setup

```bash
./vendor/bin/sail up -d
./vendor/bin/sail composer install
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan storage:link
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
```

---

<a id="windows-wsl2"></a>

# 2. 🪟 Windows (Laravel Sail via WSL2)

## Requirements

* WSL2 (Ubuntu recommended)
* Docker Desktop
* Git

---

## ⚠️ Important Performance Recommendation

For best performance with Docker + Laravel + WSL2:

✅ Store the project inside the Linux filesystem (WSL)

Example:

```bash
~/projects/my-project
```

❌ Do NOT store the project inside Windows mounted directories such as:

```bash
/mnt/c/...
```

---

## Installation & Setup

### 1. Install required software

* Install Docker Desktop
* Install Git

---

### 2. Install WSL2

Open PowerShell as Administrator:

```bash
wsl --install
```

After installation:

* Restart your PC
* Create Linux user and password in Ubuntu

---

### 3. Configure Docker for WSL2

Open Docker Desktop:

* Settings → General

    * Enable: **Use the WSL 2 based engine**

* Settings → Resources → WSL Integration

    * Enable Ubuntu

Click: **Apply & Restart**

---

### 4. Open Ubuntu (WSL)

Launch Ubuntu from the Start menu.

---

### 5. Create projects directory inside WSL

```bash
mkdir -p ~/projects
cd ~/projects
```

---

### 6. Clone project

Clone the repository inside the Linux filesystem:

```bash
git clone <repo-url>
cd <project-folder>
```

---

### 7. Create .env file

```bash
cp .env.example .env
```

Then set a unique `ADMIN_PATH` in `.env` — see [Admin Panel URL](#admin-path).

---

### 8. Install dependencies (via Docker)

```bash
docker run --rm \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```

---

### 9. Allow Docker access inside WSL

```bash
sudo usermod -aG docker $USER
newgrp docker
```

Check Docker access:

```bash
docker ps
```

---

### 10. Start Sail

```bash
./vendor/bin/sail up -d
```

---

### 11. Finish Laravel setup

```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan storage:link
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
```

---

## Access

* App: [http://localhost](http://localhost)
* phpMyAdmin: [http://localhost:8080](http://localhost:8080)

---

<a id="windows-openserver"></a>

# 3. 🪟 Windows (OpenServer - no Docker)

## Requirements

* OpenServer (or similar stack: PHP 8.4, MySQL, Redis optional)
* Composer
* Node.js

### OPcache

OpenServer ships `opcache.memory_consumption = 32` (in its own block near the end of
`modules/PHP-8.4/PHP/php.ini`, overriding the 128 set higher up). 32 MB does not hold
this app: once the shared cache is full, OPcache falls back to `opcache.file_cache`
and re-loads every op_array into *process* memory on each include, never freeing it.
A big Filament form (the Language dictionary builds ~215 components) then costs
~160 MB of retained memory per render, and `php artisan test` dies with
`Allowed memory size of 1073741824 bytes exhausted` somewhere in the Filament suite.

Raise it and restart OpenServer:

```ini
opcache.memory_consumption      = 256
opcache.interned_strings_buffer = 32
```

With that the whole suite peaks at ~180 MB instead of blowing through 1 GB.

---

## Setup Steps

```cmd
git clone https://github.com/abc-cms/laravel-filament-starter.git
cd laravel-filament-starter
copy .env.example .env
```

Update `.env`:

```
APP_URL=https://laravel-filament-starter
ADMIN_PATH=<unique-admin-path>
DB_CONNECTION=mysql
DB_HOST="MariaDB-11.8"
DB_PORT=3306
DB_DATABASE=laravel_filament_starter
DB_USERNAME=root
DB_PASSWORD=
```
Update `.osp/project.ini`:
```
http_engine       = Apache
php_engine        = PHP-8.4
web_root          = {base_dir}\public
```

Install dependencies:

```bash
composer install
npm install
```

Finish Laravel setup:

```bash
php artisan key:generate
php artisan storage:link
php artisan migrate
php artisan db:seed
```

## 🧱 Useful Commands

### Artisan

```bash
./vendor/bin/sail artisan <command>
```

---

### Reset permissions cache

```bash
./vendor/bin/sail artisan permission:cache-reset
```

---

### Stop Sail

```bash
./vendor/bin/sail down
```

---

### Rebuild containers

```bash
./vendor/bin/sail up -d --build
```

---

### Full reset (with volumes)

```bash
./vendor/bin/sail down -v
./vendor/bin/sail up -d
```

---

# Project environment variables

Beyond the stock Laravel keys, this project reads the following. All of them have working
defaults — `.env.example` only lists the OAuth ones, because the rest are meant to be
changed rarely.

## Logging

| Key | Default | What it does |
|---|---|---|
| `LOG_ENABLED` | `true` | Master switch for the model-change journal (`logs` table). Off = nothing is recorded — see [Logs](starter/logs.md) |
| `LOG_RETENTION_DAYS` | `365` | How long journal rows survive `php artisan logs:prune` |
| `AUTH_LOG_ENABLED` | `true` | Whether sign-in / social sign-in / password events go to `log_auth` |
| `AUTH_COOLDOWN_SECONDS` | `5` | How long a login is blocked after a failed attempt; `0` disables the cooldown |

## Media and messaging

| Key | Default | What it does |
|---|---|---|
| `IMAGE_DRIVER` | GD driver | Intervention Image driver; switch to the Imagick driver class for better quality — see [Images](starter/images.md) |
| `SMS_PROVIDER` | `log` | Which SMS provider sends messages. `log` writes them to the journal instead of sending — see [SMS](starter/sms.md) |

## OAuth / social login

Listed in `.env.example`. Empty client ids simply leave that provider unavailable; the
redirect URIs default to `/auth/social/{provider}/callback` under `APP_URL`. Details in
[OAuth](starter/oauth.md).

| Key | Provider |
|---|---|
| `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI` | Google |
| `FACEBOOK_CLIENT_ID`, `FACEBOOK_CLIENT_SECRET`, `FACEBOOK_REDIRECT_URI` | Facebook |
| `TELEGRAM_BOT_ID`, `TELEGRAM_BOT_TOKEN`, `TELEGRAM_REDIRECT_URI` | Telegram |

Module switches are **not** environment variables — a module is turned off in
`config/modules.php`, so the setting lives in the repository rather than per-server. See
[Disabling Modules](starter/modules.md).

---

# Recommendation

* Use **Sail** for all team development
* Use **OpenServer** only if Docker is not available

Add an alias to simplify commands:

```bash
alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'
```

Then you can use:

```bash
sail up -d
sail artisan migrate
```
