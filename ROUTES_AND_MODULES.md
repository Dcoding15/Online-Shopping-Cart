# Routes and Modules

## 1. Public Routes

| Method | URI | Route name | Purpose |
|---|---|---|---|
| GET | `/` | — | Redirect to `/products` |
| GET | `/register` | `register` | Registration form |
| POST | `/register` | — | Create buyer/seller account and sign in |
| GET | `/login` | `login` | Login form |
| POST | `/login` | — | Authenticate with phone number and password |
| POST | `/logout` | `logout` | Sign out; requires authentication |
| GET | `/products` | `products.index` | Catalogue with search/filter/sort/pagination |
| GET | `/products/{product}` | `products.show` | Product details |
| GET | `/products/{product}/picture` | `products.picture` | Return product image bytes |

## 2. Authenticated Dashboard

| Method | URI | Route name | Middleware | Purpose |
|---|---|---|---|---|
| GET | `/dashboard` | `dashboard` | `auth` | Standard dashboard; redirects admins to admin dashboard |
| GET | `/profile/picture` | `profile.picture` | `auth` | Return the signed-in user's profile image |

The standard dashboard displays the user's uploaded profile picture or an initial-based placeholder when no picture exists. The profile image route is available only to authenticated users.

## 3. Seller Product Management

All routes below require `auth` and `seller`. The seller middleware permits seller and administrator roles.

| Method | URI | Route name | Purpose |
|---|---|---|---|
| GET | `/seller/dashboard` | `seller.dashboard` | Seller dashboard |
| GET | `/seller/products` | `seller.products` | List the current seller's products |
| GET | `/seller/products/create` | `seller.products.create` | Product creation form |
| POST | `/seller/products` | `seller.products.store` | Create a product |
| GET | `/seller/products/{id}/edit` | `seller.products.edit` | Edit form for an owned product |
| PUT | `/seller/products/{id}` | `seller.products.update` | Update an owned product |
| DELETE | `/seller/products/{id}` | `seller.products.destroy` | Delete an owned product |

## 4. Buyer Cart and Orders

All routes below require `auth` and `buyer`.

| Method | URI | Route name | Purpose |
|---|---|---|---|
| GET | `/cart` | `cart.index` | Show current buyer's cart and totals |
| POST | `/cart` | `cart.store` | Add product/quantity to cart |
| PUT | `/cart/{id}` | `cart.update` | Update cart line quantity |
| DELETE | `/cart/{id}` | `cart.destroy` | Remove a cart line owned by buyer |
| DELETE | `/cart` | `cart.clear` | Clear current buyer's cart |
| GET | `/checkout` | `checkout` | Show checkout summary and wallet balance |
| POST | `/checkout` | `orders.store` | Process wallet payment and create order |
| GET | `/orders` | `orders.index` | Show current buyer's order history |
| GET | `/orders/{id}` | `orders.show` | Show invoice for an order owned by buyer |

## 5. Administrator Routes

All routes below require `auth` and `admin`. The URI prefix is `/admin` and route names use `admin.`.

| Method | URI | Route name | Purpose |
|---|---|---|---|
| GET | `/admin/dashboard` | `admin.dashboard` | Platform metrics |
| GET | `/admin/users` | `admin.users.index` | List/search/filter users |
| GET | `/admin/users/{id}/edit` | `admin.users.edit` | Edit user form |
| PUT | `/admin/users/{id}` | `admin.users.update` | Update user fields/role/wallet |
| DELETE | `/admin/users/{id}` | `admin.users.destroy` | Delete user subject to safeguards |
| GET | `/admin/sellers` | `admin.sellers.index` | List sellers |
| GET | `/admin/buyers` | `admin.buyers.index` | List buyers |
| GET | `/admin/products` | `admin.products.index` | List/filter all products |
| DELETE | `/admin/products/{id}` | `admin.products.destroy` | Delete a product |
| GET | `/admin/orders` | `admin.orders.index` | List platform orders |
| GET | `/admin/orders/{id}` | `admin.orders.show` | Show administrator order invoice |

## 6. Separate Admin Seller CRUD Routes

These routes also require `auth` and `admin` but use `/sellers` rather than `/admin/sellers`.

| Method | URI | Route name | Purpose |
|---|---|---|---|
| GET | `/sellers` | `seller.index` | List sellers for management |
| GET | `/sellers/create` | `seller.create` | Create-seller form |
| POST | `/sellers` | `seller.store` | Create seller account |
| GET | `/sellers/{id}/edit` | `seller.edit` | Edit seller form |
| PUT | `/sellers/{id}` | `seller.update` | Update seller account |
| DELETE | `/sellers/{id}` | `seller.destroy` | Delete seller if no products are owned |

The route listing also includes Laravel's health endpoint `/up` and framework/vendor routes. The analyzed environment returned 43 registered routes in total.

## 7. Module Responsibilities

### Authentication

`AuthController` handles registration, login, and logout. Phone number is the unique login identifier. Passwords are hashed with Laravel's `Hash` facade. Successful login regenerates the session ID; logout invalidates the session and regenerates the CSRF token.

### Product catalogue

`ProductController` loads seller information and supports search by product name/description, category, type, price bounds, stock-only filter, sort order, and 12-item pagination. Search uses PostgreSQL `ILIKE`. Product image data is returned from `/products/{product}/picture` with a MIME type inferred by `finfo`.

### Cart

`CartController` scopes cart lines to the logged-in buyer, rejects out-of-stock items and self-purchases, enforces available stock, updates quantities, and recalculates `user_cart_quantity` after changes.

### Checkout and orders

`OrderController` validates shipping address, checks cart and stock, checks wallet funds, and uses a transaction to update buyer/seller wallet balances, decrement inventory, create order/order-item rows, and clear the cart. Order history and invoice lookups are scoped to the current buyer.

### Seller management

`SellerController` has admin-facing seller CRUD and seller-facing product CRUD. Product edits/deletes use both product ID and current seller ID to enforce ownership.

### Administration

`AdminController` provides system metrics and user, seller, product, and order management. The source includes protections against an admin deleting their own account and against deleting a seller who still owns products.

## 8. Role and Ownership Controls

| Resource/action | Control found in source |
|---|---|
| Admin screens | `auth` + `admin` middleware |
| Seller product screens | `auth` + `seller` middleware |
| Buyer cart/checkout/order screens | `auth` + `buyer` middleware |
| Seller product updates/deletes | Query is scoped by current seller's `user_id` |
| Cart line updates/deletes | Query is scoped by current buyer's `user_id` |
| Buyer order invoice | Query is scoped by current buyer's `user_id` |
| Seller deleting own listing from cart | Adding own product is rejected |

## 9. Validation and Error Handling

The controllers use Laravel request validation for registration fields, product fields, cart quantity, product IDs, and shipping address. Most business-rule rejections redirect back or to the cart with a flash message. The checkout transaction's `RuntimeException` paths for concurrent balance/stock changes currently lack an explicit local catch, which should be addressed to ensure a controlled response.
