# 🚀 Laravel Project Setup (Sail + MySQL)

## 📋 Requirements

Make sure you have installed:

- Docker
- Docker Compose
- Git

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

## Local install sail

This will create a temporary container, connect the current directory under which the command is run as a web folder,
install the necessary dependencies, and then you can run sail up as usual.

```bash 
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```

---

## 🐳 Start Sail

The project uses Laravel Sail for local development.

```bash
./vendor/bin/sail up -d
```

---

## 📦 Install dependencies

```bash
./vendor/bin/sail composer install
```

---

## 🔑 Generate application key

```bash
./vendor/bin/sail artisan key:generate
```

---

## 📁 Storage link

```bash
./vendor/bin/sail artisan storage:link
```

---

## 🗄 Run migrations

```bash
./vendor/bin/sail artisan migrate
```

---

## 📦 Run Seeder

```bash
./vendor/bin/sail artisan db:seed
```

---

# 🛠 Services

## 🌐 Application

- URL: http://localhost

---

## 🗄 phpMyAdmin

phpMyAdmin is included in the project for database management.

- URL: http://localhost:8080

### Login credentials:

- **Server:** `mysql`
- **Username:** value from `DB_USERNAME`
- **Password:** value from `DB_PASSWORD`

---


## 🔐 1. Default Roles

| Role  | Permissions                  |
|-------|-----------------------------|
| admin | admin (admin panel access)  |
| user  | user                        |

---

## 👤 2. Default Admin User

After running the seeder:

- **Email:** `admin@example.com`
- **Password:** `password`
- **Role:** `admin`

---

## 🧱 Useful Commands

### ▶️ Artisan

```bash
./vendor/bin/sail artisan <command>
```

---

### 🔄 Reset permissions cache

```bash
./vendor/bin/sail artisan permission:cache-reset
```

---

### ⛔ Stop Sail

```bash
./vendor/bin/sail down
```

---

### 🔁 Rebuild containers

```bash
./vendor/bin/sail up -d --build
```

---

### 🧹 Full reset (with volumes)

```bash
./vendor/bin/sail down -v
./vendor/bin/sail up -d
```

---

# 💡 Recommendation

Add an alias to simplify commands:

```bash
alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'
```

Then you can use:

```bash
sail up -d
sail artisan migrate
```
