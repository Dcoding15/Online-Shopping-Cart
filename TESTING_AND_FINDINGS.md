# Testing, Static Analysis, and Findings

## 1. Verification Summary

The archive was inspected statically, and safe non-destructive commands were attempted. Results below describe this analysis environment, not every developer machine.

| Check | Result | Interpretation |
|---|---|---|
| Archive extraction and source inspection | Passed | The ZIP extracted and key project files were readable |
| `php artisan route:list --except-vendor` | Passed | Laravel booted sufficiently to register/list 43 routes |
| PHP syntax lint over `app`, `routes`, `database`, and `tests` | Passed | No syntax errors were reported by `php -l` |
| `php artisan test` | Blocked | PHPUnit stopped because PHP extension class `DOMDocument` is unavailable |
| `npm run build` | Blocked | Shell reported permission denied when invoking the Vite executable |
| `node node_modules/vite/bin/vite.js build` | Blocked | Rolldown could not load a platform-specific native binding from the archived `node_modules` |
| Composer command | Unavailable | Composer is not installed in the analysis environment |
| Database-backed runtime verification | Not performed | The available PHP modules did not include a PostgreSQL or SQLite PDO driver; no database tests were run |

The project `TESTING.txt` says a prior full run completed with 40 tests and 162 assertions. That is a repository note, not a result independently reproduced in this analysis session.

## 2. Existing Automated Test Coverage

| Test file | Coverage indicated by source |
|---|---|
| `tests/Feature/AuthTest.php` | Registration with phone number, buyer role, persistence, authentication |
| `tests/Feature/BuyerProductTest.php` | Catalogue, search, category/type filters, stock filter, product details and out-of-stock message |
| `tests/Feature/CartTest.php` | Add/update/remove/clear cart, stock limits, totals, ownership boundaries |
| `tests/Feature/OrderTest.php` | Checkout, empty cart, successful wallet/stock/order updates, insufficient balance, invoice ownership |
| `tests/Feature/SellerTest.php` | Seller administration, seller deletion safeguards, access controls, seller product page details |
| `tests/Feature/AdminTest.php` | Admin dashboard, access controls, user updates, own-account deletion protection, orders and product deletion |
| `tests/Feature/DatabaseSeederTest.php` | Seeded user roles, product count, seller ownership |
| `tests/Feature/ExampleTest.php` | Root redirect smoke test |
| `tests/Unit/ExampleTest.php` | Placeholder assertion (`true`) |

## 3. Findings and Recommended Actions

### High priority: `.env` included in the archive

A real `.env` file exists in the uploaded ZIP. Environment files can include `APP_KEY`, database credentials, mail credentials, or service secrets. The `.gitignore` excludes `.env`, but that does not prevent a manually created ZIP from including it.

**Action:** Remove `.env` from any archive shared with reviewers or uploaded publicly. Include only `.env.example` with placeholder values. If genuine secrets were present in a shared archive, rotate them. Do not paste secret values into documentation.

### High priority: PostgreSQL requirement conflicts with example configuration

`INSTALLATION.txt` states PostgreSQL is required and points out PostgreSQL-specific queries/migrations. However, `.env.example` sets `DB_CONNECTION=sqlite`, and `phpunit.xml` sets `DB_CONNECTION=pgsql` for tests.

**Action:** Make the default example configuration consistent with the documented supported database, or explicitly document that SQLite is only for limited development paths and cannot run the full application.

### High priority: tests could not run in this environment

`php artisan test` failed before tests executed because `DOMDocument` is unavailable. PHP PDO is present but neither a PostgreSQL nor SQLite PDO driver was listed by `php -m`.

**Action:** Enable/install the PHP XML/DOM extension and `pdo_pgsql`, configure a dedicated empty PostgreSQL test database, and rerun `php artisan test`. Do not use a production or valuable database for tests.

### Medium priority: frontend build is not reproducible from bundled dependencies

The direct Vite command reported permission denied. Invoking the Vite JavaScript entry directly then failed because Rolldown's platform-specific native binding was missing. This commonly occurs when `node_modules` was copied from a different platform or optional dependencies were omitted.

**Action:** On the target machine, install dependencies from the lock file (`npm ci`) and run `npm run build`. Avoid packaging `node_modules`; recreate it for the target operating system.

### Medium priority: concurrent checkout exception handling

`OrderController` performs row locks and throws `RuntimeException` if wallet balance or stock becomes insufficient during the transaction. The controller does not locally catch those runtime exceptions to redirect with a friendly message.

**Action:** Catch expected business-rule exceptions around the transaction and return a controlled response; add tests that assert no partial wallet, stock, order, or cart changes occur.

### Medium priority: product filters should validate inputs

The catalogue casts `min_price` and `max_price` to floats but does not visibly validate that supplied values are numeric, non-negative, or that the minimum is not greater than the maximum.

**Action:** Add request validation and tests for invalid ranges, negative values, and malformed input. Validate category/type against supported values when that matches the desired UX.

### Medium priority: duplicated cart quantity state

`users.user_cart_quantity` stores a total that can be calculated from cart lines. `User::updateCartQuantity()` recalculates it after the current cart operations, but duplicated state can drift if another path modifies cart rows without invoking the method.

**Action:** Add a reconciliation test or derive the value directly from cart rows when practical.

### Medium priority: image storage and content validation

Profile/product images are read as binary and stored in database columns. The product image endpoint infers MIME type from bytes, but the suite does not comprehensively cover malformed image content, very large files, or every MIME edge case.

**Action:** Add upload/retrieval tests and consider object/file storage for larger deployments. Keep upload validation and response content types strict.

### Lower priority: project README remains the Laravel starter README

The root `README.md` describes the generic Laravel framework rather than this marketplace. The archive has separate installation/testing text files, but the main entry point does not direct new developers to them.

**Action:** Replace the starter README with a project-specific overview, quick start, roles, test instructions, and links to the generated documentation.

## 4. Additional Tests Recommended

- Fresh PostgreSQL migration and rollback test.
- Registration validation and duplicate phone number tests.
- Login success/failure, session regeneration, logout invalidation, and role redirect tests.
- Authorization matrix for every protected route and role.
- Product ownership tests for seller update/delete and self-purchase restrictions.
- Cart quantity boundary tests (zero, negative, above stock, repeated add).
- Checkout rollback tests for insufficient stock and wallet balance at transaction time.
- Concurrent checkout tests to verify no overselling or double-spending.
- Product/profile image upload and retrieval tests.
- Product filter validation tests.
- Seeder idempotency expectations (current seeders are additive and create additional records on each run).
- Browser-level responsive/accessibility tests if the UI is intended for public use.

## 5. Safe Re-verification Checklist

1. Remove secrets from distributable archives and configure a local `.env`.
2. Install PHP dependencies using Composer and frontend dependencies using `npm ci`.
3. Enable required PHP extensions, including DOM/XML and `pdo_pgsql`.
4. Create a dedicated PostgreSQL test database with no valuable data.
5. Confirm the active database target before running migrations or tests.
6. Run `php artisan route:list`, `php artisan migrate:status`, `php artisan test`, and `npm run build`.
7. If testing a clean schema, use `php artisan migrate:fresh` only against a disposable database; it drops all tables in the selected database.
