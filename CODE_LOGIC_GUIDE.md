# Application Logic Guide

This guide maps the website's main behavior to the source files where its logic is implemented. It is an index to the code, not a replacement for it.

## Request flow

```text
Browser request
  -> routes/web.php
  -> authentication / role middleware
  -> app/Http/Controllers/
  -> app/Models/ and database/
  -> resources/views/
  -> Browser response
```

## Main logic locations

| Feature | Entry point | Logic |
|---|---|---|
| Routes and access control | [`routes/web.php`](routes/web.php) | Maps URLs to controllers and groups routes by authentication, buyer, seller, and admin roles. |
| Login and registration | [`AuthController.php`](app/Http/Controllers/AuthController.php) | Validates new accounts, hashes passwords, authenticates users, and logs users out. |
| Profile editing | [`CustomerController.php`](app/Http/Controllers/CustomerController.php) | Shows and updates the signed-in user's name, phone, address, birth date, password, and profile picture. |
| Product catalogue | [`ProductController.php`](app/Http/Controllers/ProductController.php) | Searches, filters, sorts, and displays products and product pictures. |
| Cart | [`CartController.php`](app/Http/Controllers/CartController.php) | Adds, updates, removes, or clears the current buyer's cart; checks stock and prevents sellers from buying their own products. |
| Checkout and payment | [`OrderController.php`](app/Http/Controllers/OrderController.php) | Creates pending orders, starts Razorpay checkout, verifies payment, checks stock, fulfills orders, and requests refunds when stock is unavailable. |
| Seller operations | [`SellerController.php`](app/Http/Controllers/SellerController.php) | Manages sellers and limits seller product changes to products they own. |
| Administrator operations | [`AdminController.php`](app/Http/Controllers/AdminController.php) | Manages users, sellers, products, and orders; blocks deletion of purchased products. |
| Role checks | [`app/Http/Middleware/`](app/Http/Middleware/) | Restricts buyer-, seller-, and admin-only routes. Middleware aliases are configured in [`bootstrap/app.php`](bootstrap/app.php). |

## Important workflows

### Accounts and profiles

1. Registration and login are routed through `AuthController`.
2. Registration permits buyer and seller roles; admin accounts are not created through public registration.
3. Signed-in users can open the profile edit page and submit changes through the profile routes.
4. Server validation restricts names to letters, phone numbers to exactly 10 digits, and addresses to letters, digits, spaces, commas, hyphens, and parentheses.
5. Passwords are hashed before saving. Profile pictures are validated and served through an authenticated route.

Related files:

- Routes: [`routes/web.php`](routes/web.php)
- Profile page: [`resources/views/profile/edit.blade.php`](resources/views/profile/edit.blade.php)
- Dashboard link: [`resources/views/dashboard.blade.php`](resources/views/dashboard.blade.php)
- Input filters: [`public/js/name-validation.js`](public/js/name-validation.js) and [`public/js/address-validation.js`](public/js/address-validation.js)

### Shopping and payment

1. Public catalogue requests are handled by `ProductController`.
2. A buyer's cart is scoped to the signed-in user in `CartController`.
3. Checkout validates the shipping address, locks cart and product rows, verifies stock, and creates a pending order with purchase-time item snapshots.
4. The server creates a Razorpay order. After checkout, it verifies the signature and payment status before marking an order as paid.
5. Successful fulfillment reduces stock and clears matching cart quantities. If stock is no longer available, the order is marked for refund instead of fulfillment.

Related files:

- Cart page: [`resources/views/cart/index.blade.php`](resources/views/cart/index.blade.php)
- Checkout page: [`resources/views/orders/checkout.blade.php`](resources/views/orders/checkout.blade.php)
- Order history and details: [`resources/views/orders/index.blade.php`](resources/views/orders/index.blade.php) and [`resources/views/orders/show.blade.php`](resources/views/orders/show.blade.php)
- Payment configuration: [`config/services.php`](config/services.php)

### Products and management

- Sellers create and edit product details in `SellerController`; product metadata is stored as JSON and displayed as specification tags.
- Admin product deletion checks paid/purchased order items and refuses deletion if a purchase exists.
- Product and order relationships are defined on [`Product.php`](app/Models/Product.php), [`Order.php`](app/Models/Order.php), and [`OrderItem.php`](app/Models/OrderItem.php).

Related views:

- Product details: [`resources/views/products/show.blade.php`](resources/views/products/show.blade.php)
- Seller product forms: [`resources/views/seller/products/create.blade.php`](resources/views/seller/products/create.blade.php) and [`resources/views/seller/products/edit.blade.php`](resources/views/seller/products/edit.blade.php)
- Admin product management: [`resources/views/admin/products/index.blade.php`](resources/views/admin/products/index.blade.php)

## Data and seeders

| Data | Source |
|---|---|
| Database schema | [`database/migrations/`](database/migrations/) |
| User model and role helpers | [`app/Models/User.php`](app/Models/User.php) |
| Product model and categories/types | [`app/Models/Product.php`](app/Models/Product.php) |
| Generated user data | [`database/factories/UserFactory.php`](database/factories/UserFactory.php) |
| Generated product data, including Indian brand metadata | [`database/factories/ProductFactory.php`](database/factories/ProductFactory.php) |
| User seed counts | [`database/seeders/UserSeeder.php`](database/seeders/UserSeeder.php) |
| Product seed count | [`database/seeders/ProductSeeder.php`](database/seeders/ProductSeeder.php) |
| Seeder order | [`database/seeders/DatabaseSeeder.php`](database/seeders/DatabaseSeeder.php) |

Run all seeders with `php artisan db:seed`, or just product seeds with `php artisan db:seed --class=ProductSeeder`.

## Tests

Feature tests for authentication, profile updates, products, cart, checkout, seller tools, and admin actions live in [`tests/Feature/`](tests/Feature/). Run the suite with:

```powershell
php artisan test
```
