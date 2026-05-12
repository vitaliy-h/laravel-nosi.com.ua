# Laravel Filament Starter

Production-ready starter based on Laravel + Filament.

---

## Stack

* PHP 8.4
* Laravel 13.7
* Filament v5.6 (Admin Panel)
* MySQL 8.4
* Redis
* Spatie Laravel Permission
* Laravel Sail (Docker)

## Services

* laravel.test
* mysql
* redis
* phpmyadmin

## Setup

```bash
  ./vendor/bin/sail up -d
  ./vendor/bin/sail composer install
  ./vendor/bin/sail npm install
  ./vendor/bin/sail artisan migrate
  ./vendor/bin/sail npm run dev
```  

## Features

* Filament admin panel
* Roles & permissions
* Laravel localization
* Docker-based development environment

# 📎 Documentation

* [General README](./README.md)
* [local Setup](./local_setup.md)
* [Architecture Overview](./architecture.md)
    * [images](images.md)
    * [translations](translations.md)
    * [logs](logs.md)
* [Filament Admin Panel](./filament.md)
    * [Resource Generation](filament/resource-generation.md)
    * [Translations Guide](filament/translations.md)


