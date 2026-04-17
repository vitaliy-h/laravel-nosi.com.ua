# 🚀 Laravel Project Setup (Docker + Mysql)

## 📋 Requirements

Make sure you have installed:

* Docker
* Docker Compose
* Git

---

## 📥 Clone the repository

```bash
git clone <your-repo-url>
cd <project-folder>
```

---

## ⚙️ Environment setup

Copy `.env` file:

```bash
cp .env.example .env
```

---

## 🐳 Start containers

```bash
docker compose up -d
```

---

## 📦 Install dependencies

Run inside container:

```bash
docker compose exec web composer install
```

---

## 🔑 Generate application key

```bash
docker compose exec web php artisan key:generate
```

---

##  Storage link

```bash
docker compose exec web php artisan storage:link
```

---

## 🗄 Run migrations

```bash
docker compose exec web php artisan migrate
```

---

## Change the hosts file to point the domain to your server.

Windows: `c:\Windows\System32\Drivers\etc\hosts`

Linux: `/etc/hosts`

MacOS: `sudo nano /etc/hosts` then ˆ+O and ˆ+X

Add the following lines:

```
127.0.0.1   lv-bigjol.local
```

URL: [http://lv-bigjol.local](http://lv-bigjol.local).
Admin URL: [http://lv-bigjol.local/admin](http://lv-bigjol.local/admin).


---

# 🌱 Roles & Permissions Seeder + Admin Access

This guide explains how to run the roles and permissions seeder and access the admin panel.

---

## 📦 1. Run Seeder

To create default roles, permissions, and admin user, run:

```bash id="run-seeder"
docker compose exec web php artisan db:seed --class=RolesAndPermissionsSeeder
```

Or, if starting fresh with migration:

```bash id="migrate-seed"
docker compose exec web php artisan migrate:fresh --seed
```

---

## 🔐 2. Default Roles

| Role    | Permissions        |
| ------- |--------------------|
| admin   | admin(full access) |
| user    | user               |

---

## 👤 3. Default Admin User

After running the seeder:

* **Email:** `admin@example.com`
* **Password:** `password`
* **Role:** `admin` (full access)

This user can log in to the Filament admin panel.

---

## 🧱 6. Useful Commands

* **Reset permissions cache:**

```bash
docker compose exec web php artisan permission:cache-reset
```

* **Run artisan commands inside container:**

```bash
docker compose exec web php artisan <command>
```

* **Stop Docker containers:**

```bash
docker compose down
```

* **Rebuild containers:**

```bash
docker compose up -d --build
```
