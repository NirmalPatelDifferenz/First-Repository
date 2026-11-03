# Phase 1: Product module

This document records the behavior and boundaries introduced by the Phase 1 Product module refactor.

## Scope

Phase 1 keeps the existing Laravel 8 application, web routes, Blade page, DataTables response keys, product database columns, USD currency, and Stripe product/price workflow. It does not introduce a repository layer, change API routes, redesign the UI, or alter the database schema.

## Request validation

- Product name: required string, up to 255 characters.
- Price: required numeric value from 0.01 through 999999.99 (the existing `decimal(8, 2)` column).
- Description: required string.
- Quantity: required non-negative integer within the existing unsigned integer column range.
- Image: required JPG/JPEG/PNG image, maximum 5 MB.
- Delete: required product ID that exists and has not already been soft-deleted.

The browser retains its existing jQuery validation. Server-side Form Requests are authoritative.

## Application structure

```text
app/
├── Http/
│   ├── Controllers/ProductController.php
│   └── Requests/Product/
│       ├── StoreProductRequest.php
│       └── DeleteProductRequest.php
├── Models/Product.php
└── Services/Payments/StripeService.php

tests/Feature/Products/
├── ProductCreationTest.php
├── ProductDeletionTest.php
└── ProductListingTest.php
```

The controller coordinates the HTTP workflow. Stripe-specific HTTP calls and response checks live in `StripeService`. Eloquent remains the persistence abstraction; no repository or interface is added without a demonstrated need.

## Storage and Stripe

Product images are written to the Laravel `public` disk under `images/`. The database continues to store the image filename, preserving the existing column format. The listing constructs its URL through the configured public disk instead of hardcoding a filesystem URL.

Stripe's product image field is sent as a URL, not as a PHP uploaded-file object. **The public disk URL must be reachable by Stripe from the internet.** In production, set `APP_URL` to the application's public HTTPS origin and ensure the storage link/CDN route serves the image. A localhost URL is not fetchable by Stripe.

Stripe credentials are read through `config('services.stripe.secret')`, configured by `STRIPE_SECRET_KEY`. Do not commit the key.

The create sequence remains: upload image, create Stripe product, create Stripe price, then persist the local product. If a later step fails, the code attempts to deactivate any Stripe resources already created and removes the uploaded image. These are best-effort compensating actions; a failed compensation must be reviewed in Stripe and logs.

Deletion deactivates the Stripe price and product before soft-deleting the local row and removing the image. If Stripe deactivation fails, the local row is retained so the operation can be retried. A partial Stripe response can still require manual reconciliation; this workflow does not provide a durable retry queue or distributed transaction.

## DataTables contract

- `recordsTotal` counts all non-deleted products before search.
- `recordsFiltered` counts non-deleted products after search.
- Search remains limited to product name and description.
- Ordering uses a fixed allow-list of database columns; direction is normalized to `asc` or `desc`.
- Pagination offsets are non-negative and page length is capped at 100.

The JSON keys consumed by the current DataTables page are retained.

## Test coverage

The feature tests cover:
- Correct total and filtered listing counts.
- Exclusion of soft-deleted rows.
- Valid product creation with mocked Stripe product/price responses.
- Invalid input rejection before any Stripe request.
- Stripe price failure and best-effort product compensation.
- Successful soft deletion and image cleanup.
- Stripe deletion failure leaving the local row active.

Run locally:

```bash
composer install
php artisan test
```

The PHPUnit configuration uses an in-memory SQLite database for tests. The SQLite PDO extension must be enabled in the PHP environment running the suite. Tests use Laravel's HTTP fakes and do not require live Stripe credentials.

## Operational checks before production use

1. Confirm the public disk URL resolves to the uploaded image over HTTPS from outside the application network.
2. Confirm `STRIPE_SECRET_KEY` is set in the deployment environment and configuration cache is refreshed after changing it.
3. Confirm the web-server process can write to the configured public disk.
4. Test create and delete in Stripe test mode first.
5. Review Stripe dashboard state and application logs after deliberately simulating a failed request.
6. Run the full test suite in CI and against the project's supported PHP version before deployment.

No database migration is required by Phase 1.
