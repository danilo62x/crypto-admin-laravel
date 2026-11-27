# CoinPanel — Cripto & Web3 / Laravel

[Read in English](./README.md)

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](./LICENSE) ![Free](https://img.shields.io/badge/price-free-brightgreen)

Exchange/Web3 admin em Laravel 11 + Filament v3. Usuários com KYC, moedas, carteiras com transações (relation manager), ordens de troca e ação aprovar saque, widgets de volume/saques.

## Destaques

- Carteiras com transações (relation manager)
- Ordens de troca e moedas
- Ação "aprovar saque" condicional
- KYC por status
- Widgets: volume 24h, saques pendentes

## Telas em destaque

- Dashboard: KPIs e widgets
- CRUD: Forms, tabelas, filtros
- Detalhes: Infolist + relation managers

## Stack

- Laravel 11
- PHP 8.4
- Filament v3
- Livewire
- Alpine.js
- Tailwind CSS 4
- Vite

## Requisitos

- PHP 8.2 ou mais novo, com Composer
- Node.js 20 ou mais novo, com npm
- MySQL/MariaDB, ou SQLite para rodar rápido localmente

## Como rodar

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

O `.env.example` aponta para MySQL. Crie o banco e preencha as variáveis `DB_*` antes do migrate, ou troque para SQLite com `DB_CONNECTION=sqlite` removendo as outras linhas `DB_*` (o arquivo `database/database.sqlite` já vem no repositório).

Durante o desenvolvimento, use `npm run dev` no lugar de `npm run build` para ter hot reload dos assets.

Conta de demonstração:

- `admin@example.com` / `password`

## Estrutura do projeto

```
app/
├── Filament/  Auth, Pages, Resources, Widgets
├── Http/  Controllers
├── Models/
├── Providers/  Filament
resources/  css/, js/, views/
routes/  console.php, web.php
```

## Outras tecnologias

Este template também existe em outras 4 tecnologias. Todas são gratuitas:

- React 19: https://github.com/danilo62x/crypto-admin-react
- Vue 3: https://github.com/danilo62x/crypto-admin-vue3
- Angular 19: https://github.com/danilo62x/crypto-admin-angular
- HTML estático: https://github.com/danilo62x/crypto-admin-html

Mais templates gratuitos (painéis admin, sistemas por segmento e apps Flutter): https://github.com/danilo62x?tab=repositories

## Licença

[MIT](./LICENSE), copyright 2026 Danilo Quinelato.
