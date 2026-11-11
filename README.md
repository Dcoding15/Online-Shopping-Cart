# Online Shopping Cart

A Laravel-based multi-seller marketplace where visitors can browse products, buyers can manage a cart and place wallet-funded orders, sellers can manage their own listings, and administrators can oversee marketplace activity.

## Table of Contents

- [Project Overview](#project-overview)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Architecture](#architecture)
- [Roles and Access](#roles-and-access)
- [Database Overview](#database-overview)
- [Project Structure](#project-structure)
- [Requirements](#requirements)
- [Setup in WSL](#setup-in-wsl)
- [Running the Application](#running-the-application)
- [Demo Data](#demo-data)
- [Tests](#tests)
- [Important Notes](#important-notes)

## Project Overview

The application follows Laravel's server-rendered Model–View–Controller (MVC) pattern. Routes map HTTP requests to controllers. Controllers validate input and implement workflows. Eloquent models represent database entities and relationships, and Blade templates render the pages returned to the browser.

The project is intended as a marketplace demonstration and does not provide external payment processing, shipping integrations, or real-money settlement.

## Features

### Public product catalogue

- Browse products and view product details without signing in.
- Search by product name or description.
- Filter by category, product type, price range, or stock availability.
- Sort by newest, price, or name, and navigate paginated results.
- View product images and seller information.

### Accounts and authorization

- Register as a buyer or seller and sign in using a unique phone number and password.
- Store passwords as Laravel hashes.
- Use authentication and role middleware to protect restricted routes.
- Serve an authenticated user's profile picture when one is available.

### Seller tools

- Create, list, update, and delete seller-owned product listings.
- Manage price, inventory, category, type, description, metadata, and optional pictures.
- Scope listing changes to the signed-in seller.

### Buyer shopping and orders

- Add products to a buyer-specific cart, change quantities, remove items, or clear the cart.
- Validate stock and prevent sellers from purchasing their own products.
- Review order total and wallet balance before checkout.
- Submit a shipping address and pay using the application's internal wallet.
- View order history and itemized invoices.

### Administration

- Review dashboard metrics for users, sellers, buyers, products, stock, orders, and paid-order revenue.
- Manage users, sellers, products, and platform orders.
- Apply admin-only access checks to administrative routes.

## Technology Stack

| Area | Technology |
|---|---|
| Backend | PHP 8.3+ and Laravel 13 |
| Database | PostgreSQL |
| Data access | Laravel Eloquent ORM |
| Templates | Blade |
| Frontend assets | CSS, Vite, and Tailwind CSS |
| Tests | Pest and PHPUnit |
| Dependency managers | Composer and npm |

PostgreSQL is required for the complete application. Catalogue searches use PostgreSQL `ILIKE`, and the schema includes PostgreSQL-specific features such as JSONB and database constraints. SQLite is not a drop-in replacement.

## Architecture

```text
Browser
  -> routes/web.php
  -> authentication and role middleware
  -> controller
  -> Eloquent models
  -> PostgreSQL
  -> Blade view
  -> Browser
```

Checkout is handled as a database transaction. It rechecks buyer funds and product stock under row locks, then debits the buyer wallet, credits seller wallets, decrements inventory, creates the order and its item records, and clears the cart. A transaction keeps these database changes together.

The wallet is an internal application balance. It is not connected to a bank, card processor, or external payment gateway.

## Roles and Access

| Role | Main capabilities |
|---|---|
| Visitor | Browse, search, and view products |
| Buyer | Manage own cart, check out, and view own orders |
| Seller | Manage own products; may also browse the public catalogue |
| Administrator | Manage platform users, sellers, products, and orders |

Registration allows buyer and seller roles; it does not provide public admin registration. Role middleware is defined under `app/Http/Middleware/`, aliased in `bootstrap/app.php`, and applied to route groups in `routes/web.php`. Cart and order queries are scoped to the signed-in buyer; seller product updates and deletions are scoped to the listing owner.

## Database Overview

| Table | Purpose |
|---|---|
| `users` | User identity, role, profile, wallet, and cart quantity |
| `products` | Seller-owned product details, price, inventory, and metadata |
| `cart` | Buyer/product cart lines and quantities |
| `orders` | Buyer, order total, payment status, and shipping address |
| `order_items` | Product-name and price snapshots for each purchased item |

The primary business relationships are:

- A user can own many products, cart lines, and orders.
- Each cart line belongs to a user and a product.
- An order belongs to a buyer and contains order items.
- An order item can refer to its original product and seller while retaining purchase-time details.

Migrations also create Laravel support tables for sessions, cache, and queued jobs. Database structure is maintained in `database/migrations/`; Eloquent relationships are in `app/Models/`.

## Project Structure

```text
app/
  Http/Controllers/   Request handling and application workflows
  Http/Middleware/    Role-based access checks
  Models/             Eloquent models and relationships
bootstrap/            Laravel application and middleware configuration
database/
  factories/          Test and demo model factories
  migrations/         Database schema
  seeders/            Demo data
resources/views/      Blade templates for each application area
resources/css/        Frontend stylesheet sources
resources/js/         Frontend JavaScript entry point
routes/
  web.php             Browser routes
  console.php         Console routes
tests/
  Feature/            HTTP and business workflow tests
  Unit/               Unit tests
```

The principal controllers are `AuthController`, `ProductController`, `CustomerController`, `CartController`, `OrderController`, `SellerController`, and `AdminController`.

## Requirements

- PHP 8.3 or newer with PostgreSQL PDO support (`pdo_pgsql`) and the extensions required by Laravel and image handling.
- Composer.
- PostgreSQL server and a database/user for this application.
- Node.js and npm.

For PHP-based testing, enable the XML/DOM extension required by PHPUnit. Check the CLI PHP installation with:

```bash
php -v
php -m
```

## Setup in WSL

Run these commands from the WSL project directory:

```bash
cd /home/dbbcs/Online-Shopping-Cart
composer install
npm ci
```

Create a local `.env` from `.env.example` if one does not already exist. Configure it for PostgreSQL, for example:

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

Use your own local credentials. Do not commit or share `.env`, and do not use production credentials for development or tests. Generate the application key and prepare the database:

```bash
php artisan key:generate
php artisan migrate
```

The PostgreSQL service and configured database must be available before running migrations.

## Running the Application

Start the Laravel development server in WSL:

```bash
php artisan serve
```

Open the address printed by Artisan, usually `http://localhost:8000`. WSL localhost forwarding normally makes the server accessible from a Windows browser.

The storefront includes a public stylesheet at `public/css/market.css`. For frontend source development, run Vite in another terminal:

```bash
npm run dev
```

To create a frontend build:

```bash
npm run build
```

## Demo Data

Optionally create sample users and products:

```bash
php artisan db:seed
```

The seeders create 5 admins, 15 sellers, 30 buyers, and 50 products. Seeded users share the factory password `password`; generated phone numbers, wallet balances, and product values vary. Use these accounts only in local development.

Seeding is additive: running it again adds more records. Before demonstrating checkout, select a buyer whose wallet covers the chosen purchase and a product with enough stock.

## Tests

Configure a dedicated PostgreSQL test database before running the suite. The PHPUnit configuration selects PostgreSQL. Never point tests at a production or valuable database.

Run the suite with:

```bash
php artisan test --compact
```

Feature tests cover authentication, catalogue browsing, carts, checkout, seller tools, administration, and database seeding. To run one test file:

```bash
php artisan test --compact tests/Feature/OrderTest.php
```

Useful application checks:

```bash
php artisan route:list
php artisan migrate:status
```

## Important Notes

- **Secrets:** Keep `.env` and all credentials private. Remove secrets from any archive or public repository and rotate them if they have been exposed.
- **Database reset:** `php artisan migrate:fresh --seed` drops all tables in the configured database. Use it only with a disposable local database after verifying the active connection.
- **Demo payment:** Checkout changes internal wallet balances only. No external payment gateway is integrated.
- **Seeder behavior:** Seeders add records and are not idempotent by default.
- **Production readiness:** Before deployment, configure secure production environment settings, review concurrency error handling, validate all incoming values, and run tests against a properly configured PostgreSQL environment.
