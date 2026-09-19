# ResFlow — Demo Guide

This guide is for quickly trying ResFlow (resource & fuel management) as a reviewer or recruiter. It tells you exactly what to do and what to expect.

## 1. Run it (fastest path — SQLite, no Docker)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Open **http://localhost:8000**.

## 2. Log in

| User | Password | Role |
|------|----------|------|
| admin@admin.com | secret | Administrator |

## 3. Suggested flows (5 minutes)

### Flow A — The core loop: issue a voucher and see accounting move
1. Go to **Centro de Costos** → create one (e.g. "Logística").
2. Go to **Recursos** → create a fuel resource for that cost center.
3. Go to **Tickets** → issue a ticket (voucher) for a quantity of liters against the resource.
4. **Expected**: the resource's available liters decrease by the voucher amount.

### Flow B — The safety net: cancel a voucher
1. Open the ticket you just created → **cancel it**.
2. **Expected**: the liters are returned to the resource, and the **activity timeline** records the change.

### Flow C — Bulk: generate a batch (Lote)
1. Go to **Lotes** → generate a batch of tickets.
2. **Expected**: multiple vouchers are created in one operation.

### Flow D — The audit trail
1. After any action, open the **activity timeline**.
2. **Expected**: every create/edit/cancel is logged with who and when — this is the Open/Closed, event-driven design working.

## 4. What this project demonstrates

- **DDD in practice**: entities, value objects, repository contracts, domain events in `src/Modules/`.
- **Concurrency safety**: pessimistic locking prevents negative fuel stock.
- **Clean separation**: business logic in the domain layer, not in controllers.
- **Testing**: 31 tests covering tickets, accounting, batches, and exports.

## 5. If something fails

- Run `php artisan migrate:fresh --seed` to reset the database and re-seed.
- The frontend assets are prebuilt; if styles look broken, run `npm install && npm run build`.
- Known limitation: `migrate:rollback` is not supported (schema is consolidated).

## Notes for a production deployment

- The demo uses SQLite; production uses MySQL 8 (see `.env`).
- Change the default credentials before any real deployment.