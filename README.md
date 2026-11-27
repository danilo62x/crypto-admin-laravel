# CoinPanel: Crypto & Web3 / Laravel

[Leia em português](./README.pt-BR.md)

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](./LICENSE) ![Free](https://img.shields.io/badge/price-free-brightgreen)

An admin panel for crypto & web3 built with Laravel 11 and Filament v3. Exchange/Web3 admin: users with KYC, coins, wallets with transactions (relation manager), trade orders and an approve-withdrawal action, volume/withdrawal widgets.

Demo data is created by the database seeders, so you can log in and use every screen right after installing.

## Highlights

- Responsive layout from phone to desktop
- Seeded demo data in Portuguese (Brazil)

## Tech stack

- Laravel 11
- PHP 8.4
- Filament v3
- Livewire
- Alpine.js
- Tailwind CSS 4
- Vite

## Requirements

- PHP 8.2 or newer with Composer
- Node.js 20 or newer and npm
- MySQL/MariaDB, or SQLite for a quick local run

## Getting started

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

`.env.example` points to MySQL. Create the database and fill in `DB_*` before migrating, or switch to SQLite by setting `DB_CONNECTION=sqlite` and removing the other `DB_*` lines (the `database/database.sqlite` file is already in the repository).

Use `npm run dev` instead of `npm run build` while editing, to get hot reload of the assets.

Demo account:

- `admin@example.com` / `password`

## Project structure

```
app/
├── Filament/  Auth, Pages, Resources, Widgets
├── Http/  Controllers
├── Models/
├── Providers/  Filament
resources/  css/, js/, views/
routes/  console.php, web.php
```

## Other stacks

This template also exists in 4 other stacks. All of them are free:

- React 19: https://github.com/danilo62x/crypto-admin-react
- Vue 3: https://github.com/danilo62x/crypto-admin-vue3
- Angular 19: https://github.com/danilo62x/crypto-admin-angular
- Static HTML: https://github.com/danilo62x/crypto-admin-html

More free templates (admin dashboards, vertical systems and Flutter apps): https://github.com/danilo62x?tab=repositories

## License

[MIT](./LICENSE), copyright 2026 Danilo Quinelato.
