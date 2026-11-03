# Laravel Product Management & API Starter

A Laravel 8 application used to explore and demonstrate common backend development workflows: web authentication, token-based API authentication, product management, image uploads, server-side product listing, and Stripe product/price integration.

This repository is also a practical learning project. The documentation describes the implementation that currently exists and records a gradual, low-risk direction for improving its architecture.

> **Project status:** This is an existing Laravel 8 codebase, not a fresh Laravel installation. Some areas (particularly subscription management) are currently scaffolding or incomplete. Read the implementation notes before treating every menu or model as a complete feature.

## Contents

- [Project overview](#project-overview)
- [Technology stack](#technology-stack)
- [Current features](#current-features)
- [Repository structure](#repository-structure)
- [Requirements](#requirements)
- [Local setup](#local-setup)
- [Environment configuration](#environment-configuration)
- [Database setup](#database-setup)
- [Frontend assets](#frontend-assets)
- [Running the application](#running-the-application)
- [Authentication and API](#authentication-and-api)
- [Product and Stripe workflow](#product-and-stripe-workflow)
- [Testing](#testing)
- [Development guidelines](#development-guidelines)
- [Known limitations and improvement areas](#known-limitations-and-improvement-areas)
- [Documentation](#documentation)

## Project overview

The application provides a small authenticated web interface for managing products and a separate JSON API for registering, signing in, and signing out users with Laravel Sanctum personal access tokens.

The product workflow stores product details in MySQL, uploads product images to Laravel's public storage disk, and uses a dedicated Stripe service to create or deactivate corresponding Stripe products and prices.

The repository also contains subscription-plan model, migration, controller, and view scaffolding. The presence of these files should not be interpreted as a complete subscription purchasing or billing flow.

## Technology stack

| Area | Current implementation |
| --- | --- |
| Backend framework | Laravel 8 (`laravel/framework ^8.75`) |
| Language/runtime | PHP (Composer constraint: `^7.3|^8.0`; verify the exact PHP version against the installed dependencies and deployment environment) |
| Database | MySQL, configured through Laravel's database environment variables |
| API authentication | Laravel Sanctum 2 |
| Web authentication | Laravel UI authentication scaffolding |
| HTTP / external API | Laravel HTTP client and Guzzle |
| Frontend | Blade, Bootstrap 5, jQuery/DataTables usage in the product interface |
| Asset build | Laravel Mix 6, Webpack, Sass |
| Payment integration | Stripe REST API calls from the product controller |
| Automated tests | PHPUnit 9 |

The versions above are based on the repository's Composer and npm manifests. They are not a statement that every dependency version is currently supported or secure. Review dependency support and plan framework/runtime upgrades separately.

## Current features

### Web application

- Login and registration routes provided through Laravel UI.
- Authenticated dashboard route.
- Authenticated product page.
- Product listing endpoint used by the DataTables interface.
- Product creation and deletion actions.
- Subscription page route and view scaffold.

### JSON API

| Method | Endpoint | Purpose | Authentication |
| --- | --- | --- | --- |
| POST | `/api/register` | Register a user | Public |
| POST | `/api/login` | Authenticate a user and issue a Sanctum token | Public |
| POST | `/api/logout` | Revoke the current access token | Sanctum bearer token |

The API routes are defined in `routes/api.php`. Authentication requests use dedicated Form Request classes under `app/Http/Requests/Auth`.

### Product management

- Product details are persisted in the `products` table.
- Product images are stored using Laravel's public filesystem disk.
- Stripe product and price identifiers are stored with the local product record.
- Product deletion currently marks the local record as deleted and attempts to deactivate the associated Stripe price/product.

**Important:** The current Stripe workflow is implemented directly in `ProductController`. It should be treated as an integration that needs further validation and failure-handling review before production use.

## Repository structure

The following is a guide to the important directories currently present in the repository. It is intentionally not a complete listing of every Laravel framework file.

```text
.
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── API/
│   │   │   ├── Auth/
│   │   │   ├── HomeController.php
│   │   │   ├── ProductController.php
│   │   │   └── SubscriptionController.php
│   │   ├── Middleware/
│   │   └── Requests/
│   │       └── Auth/
│   └── Models/
│       ├── Product.php
│       ├── SubscriptionPlans.php
│       └── User.php
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/
├── resources/
│   ├── js/
│   ├── sass/
│   └── views/
│       ├── auth/
│       ├── layouts/
│       ├── product/
│       └── subscription/
├── routes/
│   ├── api.php
│   └── web.php
├── tests/
│   ├── Feature/
│   └── Unit/
├── .env.example
├── composer.json
├── package.json
└── webpack.mix.js
```

For a detailed explanation of responsibilities, current implementation, and a proposed future structure, see [docs/PROJECT_STRUCTURE.md](docs/PROJECT_STRUCTURE.md).

## Requirements

Install the following before setting up the application:

- PHP version compatible with the locked Composer dependencies.
- Composer 2.
- MySQL.
- Node.js and npm versions compatible with the project's Laravel Mix 6 / Webpack toolchain.
- A web server such as Apache/Nginx, or PHP's local development server.
- Stripe test-mode credentials if you want to exercise the Stripe product workflow.

Required PHP extensions depend on the locked dependencies and Laravel configuration. Common requirements include OpenSSL, PDO (with the MySQL driver), Mbstring, Tokenizer, XML, Ctype, JSON, and Fileinfo. If Composer reports a missing extension, install the matching extension for the PHP version used by Composer.

## Local setup

Run these commands from the repository root.

### 1. Clone the repository

```bash
git clone https://github.com/NirmalPatelDifferenz/First-Repository.git
cd First-Repository
```

### 2. Install PHP dependencies

```bash
composer install
```

Use `composer install` to respect `composer.lock`. Do not run `composer update` as part of routine setup because it can change dependency versions.

### 3. Create the local environment file

Linux/macOS:

```bash
cp .env.example .env
```

Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

### 4. Generate the application key

```bash
php artisan key:generate
```

### 5. Configure the database and integrations

Edit `.env` with your local database connection and any integration credentials you intend to use. See [Environment configuration](#environment-configuration).

### 6. Run database migrations

After creating the configured database:

```bash
php artisan migrate
```

This applies the migrations included in the repository. Review migrations before running them against any shared or production database.

### 7. Create the public storage link

```bash
php artisan storage:link
```

The product workflow stores images on Laravel's public disk. The symbolic link allows files on that disk to be served through the public storage path.

### 8. Install and compile frontend dependencies

```bash
npm install
npm run dev
```

For a production asset build:

```bash
npm run production
```

### 9. Start the local application

```bash
php artisan serve
```

Open the URL printed by Artisan (usually `http://127.0.0.1:8000`).

## Environment configuration

The tracked `.env.example` contains the standard Laravel application, MySQL, mail, cache, queue, filesystem, and optional AWS/Pusher settings.

At minimum, configure the following values for local development:

```dotenv
APP_NAME="Product Management"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_local_database
DB_USERNAME=your_local_user
DB_PASSWORD=your_local_password
```

### Stripe configuration

Stripe credentials are read through `config/services.php` from `STRIPE_SECRET_KEY`. Add the following to your local, untracked `.env` file when testing the Stripe workflow:

```dotenv
STRIPE_SECRET_KEY=sk_test_replace_with_your_test_secret
```

Use Stripe test-mode credentials for local development. Never commit real API keys, tokens, passwords, or production secrets. The current `.env.example` does not define the Stripe key, so adding a documented placeholder to that template is a separate small maintenance improvement.

## Database setup

The repository includes migrations for:

- `users` and password reset data.
- Failed jobs.
- Sanctum personal access tokens.
- `products`, including Stripe identifiers and soft-delete timestamp.
- `subscription_plans`, including Stripe product/price metadata and soft deletes.

Inspect the migration files in `database/migrations` for the authoritative schema. Do not manually create tables that are already managed by migrations.

Useful commands:

```bash
# Apply outstanding migrations
php artisan migrate

# See migration status
php artisan migrate:status

# Roll back the latest migration batch (use with care)
php artisan migrate:rollback
```

Avoid `migrate:fresh` on databases containing data: it drops all tables before re-running migrations.

## Frontend assets

The project uses Laravel Mix. Its entry points are configured in `webpack.mix.js`:

- `resources/js/app.js` builds to `public/js`.
- `resources/sass/app.scss` builds to `public/css`.

Common commands:

```bash
npm run dev
npm run watch
npm run production
```

## Running the application

The main web routes are defined in `routes/web.php`. The dashboard and product pages are grouped behind the `auth` middleware and the custom `PreventBackHistory` middleware.

The API routes are defined separately in `routes/api.php`. Use the route list to inspect the routes registered by the installed application:

```bash
php artisan route:list
php artisan route:list --path=api
```

## Authentication and API

The API authentication flow uses Laravel Sanctum personal access tokens.

1. Send user details to `POST /api/register` to create an account.
2. Send email and password to `POST /api/login`.
3. Store the returned token securely on the client.
4. Send the token on protected requests using the `Authorization: Bearer <token>` header.
5. Send `POST /api/logout` with the bearer token to revoke the current token.

Example login request:

```http
POST /api/login
Content-Type: application/json
Accept: application/json

{
  "email": "developer@example.com",
  "password": "your-password"
}
```

The API returns a user object and a plain-text access token on successful login. Treat the token as a secret: do not place it in source control or logs.

## Product and Stripe workflow

The product controller handles the web page, DataTables response, and product create/delete orchestration. Dedicated Product Form Requests validate input, and `app/Services/Payments/StripeService.php` owns Stripe HTTP requests and response checks.

At a high level, product creation currently:

1. Reads product fields and the uploaded image from the request.
2. Stores the image using Laravel's public storage disk.
3. Calls Stripe to create a product and then a price.
4. Saves the local product record and the Stripe identifiers.

Deletion currently attempts to deactivate the Stripe price and product, removes the image file, and marks the local product as deleted.

The Phase 1 implementation adds server-side validation, Stripe response checks, best-effort compensation when creation fails partway through, and soft deletion only after Stripe deactivation succeeds. Stripe must be able to fetch the public image URL: configure `APP_URL` as the externally reachable HTTPS origin and verify the public storage route. Compensation is best-effort; durable retries and distributed transaction handling remain future work.

## Testing

Run the existing PHPUnit suite with:

```bash
vendor/bin/phpunit
```

Or use Laravel's Artisan test command if supported by the installed application:

```bash
php artisan test
```

The repository currently contains the Laravel example feature test scaffold. Add focused feature tests for registration, login/logout, product validation, product listing, and Stripe failure scenarios as those areas are improved.

Tests have not been executed as part of this documentation-only change.

## Development guidelines

To keep future changes understandable and safe:

- Keep controllers focused on HTTP concerns: accept requests, coordinate application work, and return responses.
- Use Form Request classes for request validation and authorization rules.
- Keep Eloquent models focused on persistence, relationships, casts, and model-level behavior.
- Extract external Stripe communication into a dedicated service when the integration grows or needs isolated testing.
- Use API Resources when the API needs a stable, deliberate response contract.
- Add tests around behavior before refactoring existing workflows.
- Prefer Laravel conventions and simple code over abstractions that do not solve a demonstrated problem.
- Do not introduce repositories, DTOs, interfaces, or extra layers automatically. Add them only when they provide a clear benefit.
- Never commit `.env`, credentials, generated secrets, or real customer data.
- Do not use broad permissions such as `chmod -R 777` for Laravel storage. Configure the correct web-server user/group ownership and grant only the access required by the deployment environment.

## Known limitations and improvement areas

These points are observations from the current source tree and should be addressed in separate, reviewable changes:

1. **Framework lifecycle:** the application is based on Laravel 8. Plan a separate compatibility-led framework and PHP upgrade; do not combine a major runtime upgrade with structural refactoring.
2. **Product request validation:** product creation and deletion currently use the generic request object. Add dedicated Form Requests and validate numeric values, strings, IDs, and image types/sizes.
3. **Stripe failure handling:** inspect HTTP status and error payloads for every Stripe operation. Consider compensating actions or a retryable workflow when Stripe succeeds but the local database write fails (or vice versa).
4. **Image handling:** use a consistent Storage-disk path and URL strategy. Validate uploads and handle cleanup when a remote operation fails.
5. **DataTables counts:** calculate total records before applying search and filtered records after applying search; the current implementation reuses the filtered count for both response fields.
6. **Soft deletes:** the products migration has a soft-delete column, but the Product model does not currently use Laravel's `SoftDeletes` trait. Align model behavior and controller queries in a separately tested change.
7. **Middleware responsibility:** `PreventBackHistory` also sets permissive CORS response headers. Separate browser cache headers from CORS policy and configure allowed origins deliberately.
8. **Subscription scope:** the subscription controller currently returns a view. Define the expected plan management and billing behavior before implementing it.
9. **Automated coverage:** add feature tests for the critical authentication and product flows, including external-service failure cases.

## Documentation

- [Project structure and architecture guide](docs/PROJECT_STRUCTURE.md)\n- [Phase 1 Product module behavior and operational notes](docs/PHASE_1_PRODUCT_MODULE.md)

This README is intended as the starting point for developers. Keep setup commands, supported runtime requirements, feature descriptions, and known limitations aligned with the actual code as the project evolves.
