# Installation Guide

This guide is based on the project's `INSTALLATION.txt` and the inspected dependency/configuration files. PostgreSQL is required for the complete application because the source uses PostgreSQL-specific query and schema features.

## 1. Prerequisites

- PHP 8.3 or newer with `pdo_pgsql`, `pgsql`, `fileinfo`, `mbstring`, `openssl`, and `zip` enabled. The test runner also needs the XML/DOM extension used by PHPUnit.
- Composer.
- PostgreSQL server and a dedicated database/user.
- Node.js and npm.

Verify PHP and extensions:

```bash
php -v
php -m
```

## 2. Install Dependencies

From the project root:

```bash
composer install
npm ci
```

If the installed frontend dependencies came from another operating system, recreate `node_modules` on the target machine rather than copying it across platforms.

## 3. Configure Environment

Create `.env` from `.env.example` if no local environment file exists. Configure the app key, URL, and PostgreSQL credentials. Example values are placeholders; use your own local database credentials:

```dotenv
APP_NAME="Online Shopping Cart"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=online_shopping_cart
DB_USERNAME=your_local_database_user
DB_PASSWORD=your_local_database_password
```

Then generate the application key:

```bash
php artisan key:generate
```

Do not commit or share `.env`. Never use real production credentials in a development/test environment. The archive supplied for analysis contains a `.env` file; remove it before redistributing the archive and rotate any genuine credentials or keys that may have been exposed.

## 4. Create Schema and Optional Demo Data

```bash
php artisan migrate
php artisan db:seed
```

The repository's installation notes say the seeders create 5 admin accounts, 15 seller accounts, 30 buyer accounts, and 50 products. Seeder runs are additive; running them repeatedly creates additional records. Factory-generated demo accounts use the password `password` according to the repository notes. Use demo credentials only in local development and never expose those accounts in a public deployment.

## 5. Run the Application

In one terminal:

```bash
php artisan serve
```

For frontend development, in another terminal:

```bash
npm run dev
```

For a compiled frontend build:

```bash
npm run build
```

Open the local URL printed by `php artisan serve`, normally `http://localhost:8000`.

## 6. Run Tests

Configure a dedicated PostgreSQL test database before running tests. The test configuration sets `DB_CONNECTION=pgsql`; ensure tests cannot connect to production or a database containing valuable data.

```bash
php artisan test
```

Useful checks:

```bash
php artisan route:list
php artisan migrate:status
```

## 7. Destructive Database Reset Warning

The command below drops all tables in the configured database before recreating them:

```bash
php artisan migrate:fresh --seed
```

Use it only with a disposable local/test database after confirming the active database connection and database name. Never run it against shared, staging, production, or valuable data.

## 8. Common Problems

- **`could not find driver`:** enable `pdo_pgsql` for the PHP CLI environment.
- **PHPUnit `DOMDocument` missing:** install/enable the PHP XML/DOM extension.
- **PostgreSQL connection refused:** confirm PostgreSQL is running and host/port are correct.
- **Migration/check-constraint error:** verify PostgreSQL is used and migrations run in order.
- **Vite/Rolldown native binding error:** reinstall npm dependencies on the target OS using `npm ci`.
- **Frontend assets missing:** run `npm run dev` or `npm run build`.
