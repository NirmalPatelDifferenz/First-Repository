# Project Structure and Architecture Guide

This document explains how the repository is organized today and proposes a gradual target structure for future development.

**Important:** The "Current structure" section describes files observed in the repository. The "Proposed target structure" is a recommendation only; it does not mean those directories or classes already exist. This documentation change does not move application code or alter runtime behavior.

## 1. Architecture at a glance

The application is a conventional Laravel 8 application with two HTTP entry points:

- **Web:** routes in `routes/web.php`, Laravel web/auth middleware, controllers, and Blade views.
- **API:** routes in `routes/api.php`, Sanctum token authentication, API controllers, Form Requests, and JSON responses.

Both entry points use Eloquent models and the configured MySQL database. The product workflow also calls the Stripe REST API and stores uploaded product images through Laravel's filesystem abstraction.

```text
Browser
  |
  v
routes/web.php
  |
  v
Web middleware (auth, PreventBackHistory)
  |
  v
Web controllers
  |-----------------------> Blade views
  |
  +-----------------------> Eloquent models ---> MySQL
  |
  +-----------------------> Stripe REST API (product workflow)

API client
  |
  v
routes/api.php
  |
  v
Sanctum middleware (protected routes)
  |
  v
API AuthController ---> Form Requests
  |
  +-----------------------> User model ---> MySQL
  |
  +-----------------------> JSON response / Sanctum token
```

This is a conceptual view of the observed implementation, not a generated runtime dependency graph.

## 2. Current structure

Important application paths currently present:

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── API/
│   │   │   └── AuthController.php
│   │   ├── Auth/
│   │   ├── HomeController.php
│   │   ├── ProductController.php
│   │   └── SubscriptionController.php
│   ├── Middleware/
│   │   └── PreventBackHistory.php
│   └── Requests/
│       ├── Auth/
│       │   ├── LoginRequest.php
│       │   └── RegisterRequest.php
│       └── Product/
│           ├── StoreProductRequest.php
│           └── DeleteProductRequest.php
├── Models/
│   ├── Product.php
│   ├── SubscriptionPlans.php
│   └── User.php
└── Services/
    └── Payments/
        └── StripeService.php

database/
├── factories/
├── migrations/
└── seeders/

resources/
├── js/
├── sass/
└── views/
    ├── auth/
    ├── layouts/
    ├── product/
    ├── subscription/
    └── shared Blade files (header, footer, sidebar, etc.)

routes/
├── api.php
├── channels.php
├── console.php
└── web.php

tests/
├── Feature/
└── Unit/
```

### Responsibilities of current components

| Component | Current responsibility |
| --- | --- |
| `routes/web.php` | Web login/logout, dashboard, product, and subscription page routes |
| `routes/api.php` | Public register/login endpoints and Sanctum-protected logout |
| `API/AuthController.php` | API user registration, credential verification, token creation, and token revocation |
| `LoginRequest.php` / `RegisterRequest.php` | Validation rules for API authentication requests |
| `ProductController.php` | Product page, DataTables response, validation-backed product orchestration, image storage, and deletion |
| `SubscriptionController.php` | Returns the subscription index view |
| `Product.php` | Eloquent model for product data, Stripe identifiers, and soft deletes |
| `SubscriptionPlans.php` | Eloquent model for subscription plan data |
| `PreventBackHistory.php` | Adds no-cache response headers and currently also sets CORS headers |

## 3. Proposed target structure

As the project grows, organize code by Laravel responsibility first, and by feature/domain where that improves discoverability. The following is a possible destination—not a requirement to create every folder now.

```text
app/
├── Console/
├── Exceptions/
├── Http/
│   ├── Controllers/
│   │   ├── API/
│   │   │   ├── AuthController.php
│   │   │   └── ProductController.php       # if product API endpoints are introduced
│   │   └── Web/
│   │       ├── DashboardController.php
│   │       ├── ProductController.php
│   │       └── SubscriptionController.php
│   ├── Middleware/
│   ├── Requests/
│   │   ├── Auth/
│   │   └── Product/
│   └── Resources/                          # API response transformers, when needed
├── Models/
│   ├── User.php
│   ├── Product.php
│   └── SubscriptionPlan.php
├── Services/
│   ├── Payments/
│   │   └── StripeService.php
│   └── Products/
│       └── ProductImageService.php         # only if image workflow warrants it
├── Events/                                 # when domain events are useful
├── Listeners/                              # handlers for those events
└── Providers/

database/
├── factories/
├── migrations/
└── seeders/

resources/
├── js/
├── sass/
└── views/
    ├── layouts/
    ├── components/
    ├── auth/
    ├── products/
    └── subscriptions/

routes/
├── api.php
└── web.php

tests/
├── Feature/
│   ├── Auth/
│   └── Products/
└── Unit/
    └── Services/

docs/
├── PROJECT_STRUCTURE.md
├── DEVELOPMENT_GUIDE.md                    # future, when useful
└── API_DOCUMENTATION.md                    # future, when API surface grows
```

### Why this structure?

- **Controllers by transport:** web and API controllers can evolve without mixing response formats or middleware expectations.
- **Requests by feature:** validation rules remain close to the feature they protect.
- **Services for integrations:** Stripe communication can be isolated from HTTP request handling and tested with mocked HTTP responses.
- **Models remain simple:** Eloquent models represent persistence and domain relationships; they should not become containers for unrelated workflows.
- **Tests follow behavior:** feature tests describe user-facing flows; unit tests cover isolated logic where isolation is valuable.
- **Documentation stays discoverable:** project onboarding and architecture notes live under `docs/`.

Do not create empty layers just to match this diagram. Add a class or folder when there is real behavior to own.

## 4. Recommended request lifecycle

For a typical web product create request, aim for this flow over time:

1. Route maps the HTTP method and URI to a controller action.
2. Authentication/authorization middleware confirms the user may perform the action.
3. A Product Form Request validates and authorizes the input.
4. The controller coordinates the use case and delegates integration work.
5. A product service or action handles the product workflow; a Stripe service owns Stripe-specific HTTP calls.
6. Eloquent persists local state.
7. The controller returns a consistent redirect or JSON response.

For API requests, keep the same validation and business rules where possible, but return API-appropriate responses and use API Resources if a stable response contract is needed.

## 5. Refactoring principles

Use these principles to avoid overengineering:

### Controllers

- Keep controllers focused on HTTP concerns and orchestration.
- Avoid embedding large external API workflows in controller methods.
- Avoid duplicating the same business rules across web and API endpoints.

### Form Requests

- Create a dedicated Form Request for product creation and deletion/update operations.
- Validate uploaded files by MIME/type and size.
- Validate prices and quantities with explicit numeric bounds.
- Keep authorization rules explicit.

### Services and integrations

- Extract Stripe operations when the workflow needs isolated testing, retries, error mapping, or reuse.
- Keep Stripe credentials in environment/configuration, never in source code.
- Check remote response status and payload before treating an operation as successful.
- Define what should happen when the remote operation succeeds but local persistence fails.
- Use Laravel's HTTP client fake facilities in tests rather than making live Stripe calls.

### Models and persistence

- Use Eloquent relationships, casts, scopes, and traits where they represent model behavior.
- Align the Product model's soft-delete behavior with the existing migration before relying on soft-delete query semantics.
- Avoid mass-assigning request data without validation and an explicit fillable contract.
- Keep schema changes in new migrations; do not rewrite migrations already used by shared environments.

### Repositories and DTOs

Do not introduce a repository for every Eloquent model. Laravel's Eloquent ORM already provides a persistence abstraction. Add a repository only when there is a concrete need such as multiple interchangeable data sources or a complex query boundary.

Use DTOs only when they make a complex input/output contract clearer than validated arrays or typed application objects. Avoid adding interfaces with only one implementation unless they provide a real testing or extension benefit.

## 6. Suggested next refactoring phases

Phase 1 is implemented on the dedicated `refactor/phase-1-product-module` branch. Its scope and operational requirements are documented in [PHASE_1_PRODUCT_MODULE.md](PHASE_1_PRODUCT_MODULE.md).

| Next step | Work | Why it should be separate |
| --- | --- | --- |
| 2 | Define a durable Stripe retry/reconciliation strategy | Handles partial remote success without pretending external calls are transactional |
| 3 | Separate CORS policy from `PreventBackHistory` cache headers | Makes browser security policy explicit |
| 4 | Define stable API response conventions and resources | Avoids breaking current web response contracts |
| 5 | Expand authentication and broader application test coverage | Protects behavior outside the Product module |
| 6 | Plan Laravel/PHP upgrade in its own compatibility project | Avoids mixing framework lifecycle risk with structural work |

Before each step, inspect current behavior, add or update tests, implement the smallest change, and review the diff. Do not rename or move existing classes until references, route bindings, tests, and deployment assumptions have been checked.

## 7. Current review notes

The following notes summarize remaining work after Phase 1.

- Phase 1 adds dedicated Product Form Requests and moves Stripe HTTP calls into `StripeService`; keep these responsibilities separate as the workflow evolves.
- Phase 1 calculates DataTables `recordsTotal` before search and `recordsFiltered` after search, with a fixed ordering allow-list.
- The Product model now uses Laravel's `SoftDeletes` trait to match the existing products migration.
- `PreventBackHistory` sets wildcard CORS headers in addition to cache-control headers. CORS should be configured for the intended API origins rather than coupled to a middleware named for browser history.
- The subscription controller currently only returns its view. Treat subscription billing as incomplete until the intended workflow is implemented and tested.
- Ensure the public storage disk is reachable over HTTPS because Stripe fetches the product image URL. Configure `APP_URL` and the storage link correctly in each environment.\n- Phase 1 compensation for remote Stripe operations is best-effort; use a durable queue/outbox or reconciliation process if this workflow becomes production-critical.

## 8. Definition of done for future refactoring

A refactoring task should be considered ready for review when:

- Existing behavior is understood and the expected behavior is documented.
- Relevant automated tests cover the changed behavior.
- Validation and authorization are handled at the correct boundary.
- External service failures are handled without silently corrupting local state.
- No credentials or environment-specific configuration are committed.
- The change is scoped to one concern and includes an understandable PR description.
- The README and this guide are updated if setup or architecture has materially changed.
