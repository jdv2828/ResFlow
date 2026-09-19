# ResFlow — Resource & Fuel Management System

> Institutional resource management: cost centers, personnel, vehicles, fuel inventory, and controlled voucher (ticket) flows with strict accounting, pessimistic locking, and domain events.

## What this project demonstrates

- **Hybrid Clean Architecture**: classic Laravel MVC for simple CRUD (personnel, vehicles, cost centers) + real DDD modules for the complex domains (Tickets, Recurso, Lote) — entities, value objects, repository contracts, domain events.
- **Pessimistic locking** (`SELECT ... FOR UPDATE`) that prevents negative fuel stock when multiple users issue vouchers concurrently.
- **Domain events** decoupling audit and automatic fuel rebalancing (Open/Closed Principle) — every action is recorded in an activity timeline without controllers touching logs.
- **Architecture Decision Records (ADRs)** documenting the real tradeoffs (cost center as root entity, pessimistic vs optimistic locking, event-based audit).
- **31 tests** across feature, unit, and browser (Dusk) layers.

## Stack

| Layer | Technology |
|-------|------------|
| Language | PHP 8.1+ |
| Framework | Laravel 10 |
| Auth | Fortify + Sanctum + Spatie Permission |
| Frontend | Blade + Alpine.js + Tailwind CSS + Vite (assets prebuilt) |
| Database | SQLite (quick demo) / MySQL 8 (production) |
| PDF/Excel | DOMPDF, Laravel Excel |

## Quick start (fastest demo — SQLite, no Docker needed)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Open `http://localhost:8000` and log in with the demo credentials below. Frontend assets are already built and committed, so no `npm install` is required to run the demo.

### Alternative: Docker

```bash
cp .env.example .env
docker compose up -d
php artisan key:generate
php artisan migrate --seed
```

## Demo credentials

| User | Password | Role |
|------|----------|------|
| admin@admin.com | secret | Administrator |

## What you can try

1. **Log in** as `admin@admin.com` / `secret`.
2. **Create a cost center** and assign personnel / vehicles to it.
3. **Create a fuel resource** for the cost center.
4. **Issue a ticket (voucher)** — watch the liters deduct from the resource.
5. **Cancel a ticket** — watch the liters return and the activity timeline record it.
6. **Generate a batch (Lote)** of tickets in bulk.

Every action is audited in the activity timeline — that is the system's core guarantee.

## Architecture overview

The domain logic lives in a modular kernel under `src/Modules/`:

```
src/Modules/
├── ActivityLog/    → Audit & activity timeline
├── Bolsa/          → Fuel accounting per resource (liters)
├── Lote/           → Bulk voucher generation
├── Recurso/        → Fuel inventory management
├── Tickets/        → Voucher issuance, editing, cancellation
└── ServicesProviders/ → EventServiceProvider
```

Design decisions (see the ADRs in the repo for full context):

- **Cost center as root entity** — `centro_costo_id` appears across personnel, vehicles, resources, and tickets; a vehicle may use another center's fuel intentionally.
- **Pessimistic locking for accounting** — `SELECT ... FOR UPDATE` inside a transaction prevents negative liters under concurrency; optimistic locking was rejected as overkill for this concurrency profile.
- **Domain events for audit** — one `RecordActivityListener` handles all events; services don't know about the audit trail (Open/Closed).

## Tests

```bash
php artisan test                # all tests
php artisan test --coverage     # with coverage
php artisan test tests/Feature/TicketFeatureTest.php
```

Coverage: ticket issuance/editing/cancellation/expiry + accounting, resource creation/rebalancing, batch generation, CSV export, migration integrity.

## Known limitations

- **`migrate:rollback` is not supported** — the schema is consolidated in the original `CREATE` migrations; use `migrate:fresh` for clean reinstalls.
- **Tests run against a dedicated test schema** — verify it exists before running `php artisan test`.
- Only migrations added in recent changes are reversible.

## License

Demo/portfolio project.