# Online Shopping Cart System — Academic Project Report

## Title

**Design and Implementation of an Online Shopping Cart System Using Laravel and PostgreSQL**

## Abstract

The Online Shopping Cart System is a web application for a small multi-seller marketplace. The inspected implementation supports public product discovery, phone-number-based account registration and login, role-based access for administrators, sellers, and buyers, seller-owned product management, buyer cart management, checkout through an application wallet, and order/invoice history. Administrators can review users, sellers, products, and orders. Product stock, buyer wallet balances, seller wallet balances, orders, and order items are updated during checkout using a database transaction and row locks.

The application is implemented with Laravel 13, PHP 8.3 or later, Blade templates, Vite, Tailwind CSS 4, and PostgreSQL-specific database behavior. The repository includes migrations, factories, seeders, and Pest feature tests. This report distinguishes source-code implementation from runtime verification: the available analysis environment could list Laravel routes and lint PHP files, but could not run the test suite or frontend build because required local tooling/extensions were missing.

## 1. Introduction

Online commerce systems need a consistent way to present products, manage stock, maintain shopping carts, record purchases, and restrict administrative operations to authorized users. This project implements those responsibilities in a Laravel web application. It follows a server-rendered MVC approach: routes map HTTP requests to controllers, controllers apply business rules, Eloquent models represent persisted entities, and Blade views render HTML pages.

The system uses a role field (`user_type`) to distinguish `admin`, `seller`, and `buyer` accounts. It is a marketplace rather than a single-store catalogue because products are associated with seller accounts and checkout credits the corresponding seller wallet.

## 2. Problem Statement

A small marketplace requires a centralized application that lets buyers discover products and place orders, lets sellers maintain their own listings, and lets administrators supervise platform activity. The application must also protect account-specific data, prevent invalid cart quantities, check available stock, and preserve order/payment records when a purchase is processed.

## 3. Objectives

- Provide registration and login using a unique phone number and password.
- Enforce role-based access to buyer, seller, and administrator functions.
- Display a searchable and filterable product catalogue.
- Allow sellers to create, edit, and remove their own product listings.
- Maintain buyer-specific cart items and quantities.
- Validate stock and wallet balance during checkout.
- Record orders and item-level purchase details.
- Decrease product stock and transfer purchase value from buyer wallet balance to seller wallet balance.
- Provide order history and invoice views.
- Provide administrators with user, seller, product, and order management screens.
- Maintain a repeatable database setup through migrations, factories, and seeders.

## 4. Scope

### Included in the inspected code

- Phone-number authentication and logout.
- Public catalogue, product detail, image retrieval, keyword search, category/type filters, price range filters, stock filtering, sorting, and pagination.
- Admin dashboard and administration of users, sellers, products, and orders.
- Seller dashboard and CRUD operations for seller-owned products.
- Buyer cart, checkout, wallet-based payment, order history, and invoice.
- Database migrations, factories, seeders, and feature tests.

### Not established as implemented

- External payment gateway integration or real-money settlement.
- Shipment tracking, carrier integration, refunds, coupons, tax calculation, or customer reviews.
- Email-based verification or password reset.
- A JSON REST API (the inspected web routes are server-rendered routes; no `routes/api.php` was found in the archive).
- Production deployment or load testing.

These are outside the verified scope unless later code is added.

## 5. Technology Stack

| Layer | Technology / evidence |
|---|---|
| Server-side framework | Laravel `^13.17` (`composer.json`) |
| Language | PHP `^8.3` (`composer.json`) |
| Frontend templates | Laravel Blade (`resources/views`) |
| CSS | Tailwind CSS `^4.0` |
| Frontend build | Vite `^8.0` |
| Persistence | PostgreSQL is required by the installation notes and PostgreSQL-specific queries/migrations |
| ORM | Eloquent models and relationships |
| Authentication | Laravel session authentication, phone number as the credential identifier |
| Automated tests | Pest `^5.3` with Laravel integration; PHPUnit configuration is present |
| Dependency management | Composer and npm |

## 6. User Roles

### Buyer

A buyer can browse products without logging in. After authentication, the buyer can manage their own cart, proceed to checkout, pay using the application wallet balance, view order history, and access only their own order invoices. Buyer-only cart and checkout routes are protected by `auth` and `buyer` middleware.

### Seller

A seller can maintain product listings associated with their user ID, including name, price, quantity, type, category, description, optional image, and metadata. The seller middleware also permits administrators to access the seller route group. The source checks product ownership when editing or deleting seller products.

### Administrator

An administrator can view platform metrics and manage users, sellers, products, and orders. Administrative routes use `auth` and `admin` middleware. The seller-management routes are also admin-protected.

## 7. Functional Requirements

1. The system shall register a buyer or seller using a unique phone number and a confirmed password.
2. The system shall authenticate users and maintain a session.
3. The system shall list products and support search, filters, sorting, and pagination.
4. The system shall allow authenticated sellers to manage only their own products.
5. The system shall allow buyers to add, update, remove, and clear cart items.
6. The system shall reject cart quantities that are below one or exceed available stock.
7. The system shall reject checkout when the cart is empty, stock is insufficient, or the buyer wallet balance is inadequate.
8. A successful checkout shall create an order and order items, deduct the buyer wallet, credit the relevant sellers, reduce stock, and clear the cart.
9. Buyers shall only be able to retrieve their own order invoices.
10. Administrators shall be able to inspect platform users, products, and orders.

## 8. Non-Functional Requirements

- **Security:** use password hashing, session regeneration after login, session invalidation at logout, authentication middleware, role middleware, and ownership checks.
- **Data integrity:** use foreign keys, a unique user/product pair in the cart, and a transaction for checkout mutations.
- **Maintainability:** separate route definitions, controllers, models, migrations, views, and tests.
- **Usability:** use server-rendered pages, validation feedback, success/error messages, and pagination.
- **Portability:** PHP and Node dependencies are declared, but database behavior is PostgreSQL-specific; SQLite cannot be assumed compatible.
- **Reliability:** checkout uses a database transaction and row-level locking; concurrency failure handling should be tested further.

## 9. System Workflow

### 9.1 Registration and login

Registration validates the submitted name, phone number, account role, password confirmation, and optional profile image. Passwords are hashed before persistence. The user is logged in after registration. Login authenticates using phone number and password, regenerates the session, and redirects to `/dashboard`. The dashboard controller redirects administrators to the administrator dashboard; other authenticated users receive the standard dashboard view. Logout invalidates the session and regenerates the CSRF token.

### 9.2 Product discovery

The public `/products` route invokes `ProductController@index`. It supports keyword search across product name and description, category/type filters, minimum and maximum price, in-stock filtering, sort choices, and pagination. Search uses PostgreSQL `ILIKE`, which is one reason PostgreSQL is required.

### 9.3 Cart management

Cart records are scoped to the authenticated buyer. When a product is added, the controller checks that the product exists, has stock, is not owned by the buyer, and the requested quantity does not exceed availability. Re-adding a product increments the existing cart line. Updates, removals, and clearing operations recalculate the user's stored cart quantity.

### 9.4 Checkout and order creation

Checkout requires a non-empty cart and checks stock. The checkout POST requires a shipping address and rechecks stock and wallet balance. The database transaction locks the buyer row, product rows, and seller rows as they are processed. It deducts the buyer's wallet, credits seller wallets, decrements stock, creates an order with item records, stores the shipping address, and clears the cart. Payment is an internal wallet-balance operation, not a payment gateway integration.

### 9.5 Administration

Administrators can inspect platform metrics and manage accounts, sellers, products, and orders. The source includes safeguards such as preventing an administrator from deleting their own account and preventing deletion of sellers who still own products.

## 10. Database Summary

The main business tables are `users`, `products`, `cart`, `orders`, and `order_items`. Supporting Laravel tables include cache, jobs, and sessions. See [Database Documentation](DATABASE_DOCUMENTATION.md) for field descriptions and the entity-relationship diagram.

## 11. Testing and Verification

The repository contains feature tests for authentication, catalogue behavior, cart operations, checkout, seller management, administration, and seeders. The project's own `TESTING.txt` records a prior successful run of 40 tests and 162 assertions; this historical result was not reproducible in the current analysis environment because PHPUnit stopped with `Class "DOMDocument" not found`. PHP syntax checks passed and Laravel successfully listed 43 routes. The Vite build could not complete because the archived `node_modules` lacked a platform-specific Rolldown native binding; the normal `npm run build` also reported a permission error for the Vite executable.

These are environment-blocked checks, not proof that application tests or the frontend build fail on a correctly provisioned development machine.

## 12. Limitations and Recommendations

- Align `.env.example` with the documented PostgreSQL requirement instead of defaulting to SQLite.
- Remove the real `.env` from distributable archives and rotate any genuine secrets exposed by sharing it.
- Add explicit exception handling for concurrent checkout stock/balance failures so users receive a controlled response rather than an unhandled server error.
- Add tests for migration-from-empty-database, image MIME/content edge cases, authorization across every route, and concurrent purchases.
- Verify numeric validation for price filter parameters and validate product type/category values against the supported lists.
- Consider storing product images in object/file storage rather than binary database columns if image volume grows.
- Add a deployment checklist covering debug mode, HTTPS, secure cookies, database backups, least-privilege credentials, and dependency audits.
- Replace the generic Laravel README with project-specific installation and usage information.

## 13. Conclusion

The inspected code implements the core of a role-based multi-seller shopping cart application with product catalogue, seller product management, cart, wallet-based checkout, orders, and administration. Its clearest architectural strengths are the separation of Laravel components, route middleware for roles, user-specific data scoping, foreign-key relationships, and transactional checkout. Before production use, the project needs a reproducible environment, full test execution on PostgreSQL, improved concurrency error handling, and careful management of environment secrets.
