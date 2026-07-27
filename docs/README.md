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
copy .env.example .env
```

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

---

## Setup Steps

```bash
git clone https://github.com/abc-cms/laravel-filament-starter.git
cd laravel-filament-starter
copy .env.example .env
```

Update `.env`:

```
APP_URL=https://laravel-filament-starter
DB_CONNECTION=mysql
DB_HOST="MariaDB-11.8"
DB_PORT=3306
DB_DATABASE=laravel_filament_starter
DB_USERNAME=root
DB_PASSWORD=
```
Update `.osp/project.ini`:
```
web_root = {base_dir}\public
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
