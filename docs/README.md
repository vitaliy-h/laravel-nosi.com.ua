# Laravel Filament Starter — Project Overview

This document describes the **core stack, environment setup, and infrastructure** of the starter project.

---

# 🚀 Tech Stack

## Backend

* PHP **8.3**
* Laravel **13.x**
* Filament **v5.4** (Admin Panel)
* Spatie Laravel Permission **^7.2** (Roles & Permissions)
* Laravel Tinker **^3.0**

---

## Development Tools

* Laravel Sail (Docker-based local environment)
* Laravel Debugbar (dev profiling)
* FakerPHP (test data generation)
* Laravel Pint (code style fixer)
* Laravel Pail (log streaming)
* Mockery (testing)
* PHPUnit **^12.5**
* Collision (CLI error handling)

---

# 🐳 Local Environment (Laravel Sail)

The project uses **Laravel Sail** for local development.

Sail provides a Docker-based environment for consistent development setup.

---

# 📦 Docker Services

## 🧱 Application Container (laravel.test)

Runs PHP + Laravel application:

* Base image: Sail PHP 8.4 runtime
* Exposes ports:

    * `80` → HTTP (Laravel app)
    * `5173` → Vite dev server

### Key features:

* Volume bind: `./:/var/www/html`
* Xdebug support (optional)
* Host networking enabled

---

## 🐬 MySQL Database

* Image: `mysql:8.4`
* Default port: `3316`
* Persistent volume: `sail-mysql`

### Features:

* Auto DB creation
* Testing database init script included
* Healthcheck enabled

---

## ⚡ Redis

* Image: `redis:alpine`
* Default port: `6379`
* Persistent volume: `sail-redis`

Used for:

* Cache
* Queues
* Sessions (optional)

---

## 🧭 phpMyAdmin

* Image: `phpmyadmin/phpmyadmin`
* Port: `8080`

Used for:

* Database management UI
* Quick debugging of SQL data

Connection:

* Host: `mysql`
* Credentials: from `.env`

---

# ⚙️ Docker Compose Structure

## Services

* `laravel.test` → main application
* `mysql` → database
* `redis` → cache/queue
* `phpmyadmin` → DB UI

## Networks

* `sail` bridge network for internal communication

## Volumes

* `sail-mysql` → persistent DB storage
* `sail-redis` → persistent Redis storage

---

# 🧪 Development Workflow

## Start project

```bash
./vendor/bin/sail up -d
```

## Install dependencies

```bash
./vendor/bin/sail composer install
./vendor/bin/sail npm install
```

## Run migrations

```bash
./vendor/bin/sail artisan migrate
```

## Build assets

```bash
./vendor/bin/sail npm run dev
```

---

# 🔐 Authentication & Permissions

The project uses:

* Spatie Laravel Permission

    * Roles
    * Permissions
    * Middleware protection

Used across:

* Filament Admin Panel
* API (if extended)

---

# 🧩 Admin Panel (Filament v5)

* Fully separated admin panel
* Built with Filament v5.4
* Uses Laravel localization system
* Role-based access control via Spatie

---

# 📌 Key Principles

* Laravel-first architecture
* Docker-based reproducible environment
* Modular and scalable structure
* Clean separation of Admin / App logic
* Translation system based on Laravel native localization

---

# 📎 Related Documentation

* [Translations Guide](./translations.md)
* [Filament Admin Panel](./filament.md)
* [Architecture Overview](./architecture.md)
* [local Setup](./local_setup.md)

---

# 🧭 Summary

This starter is designed as a **production-ready base for Laravel + Filament projects**, with:

* Dockerized development via Sail
* Scalable admin panel (Filament v5)
* Role & permission system (Spatie)
* Clean localization approach
* Ready-to-extend architecture
