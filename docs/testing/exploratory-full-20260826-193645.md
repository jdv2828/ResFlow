# Exploratory Functional Test — FuelPass (ResourceFlow)

**Scope:** Full functional exploration · **Date:** 2026-08-26 · **Artifact ID:** exploratory-full-20260826-193645

---

## 1. Executive Summary

An end-to-end functional exploration of the FuelPass (GR — Gestión de Recursos) Laravel 10 + Blade + Alpine.js + Tailwind application was executed against a freshly migrated and seeded database, using a full-admin profile (35 permissions). All 11 approved flows were executed and **11/11 completed successfully (OK)**. No HTTP >= 400 responses, no page/console runtime errors were observed during the entire session.

The application's core business loop — creating inventory (Recursos), emitting consumption vouchers (Tickets/Vales), generating voucher batches from employee lots (Lotes), and auditing the resulting activity — works end to end. Three cross-cutting issues were surfaced: (a) domain validation limits that are invisible in the UI (`litros > 999,99` is rejected with an unlocalized English message on the full vuelta of a fuel purchase), (b) a misleading `Inactivo` status shown for newly created Recursos that are not yet counted in the stock accumulators, and (c) several UX/l10n inconsistencies. None of them blocks the primary user journeys; all have non-invasive remediation paths.

## 2. Approved Context and Scope

| Item | Detail |
|---|---|
| App under test | FuelPass / ResourceFlow — Laravel 10 + Blade + Alpine.js + Tailwind (routes in `routes/web.php`) |
| Target URL | http://127.0.0.1:8000 (was down at preflight; restarted with `php artisan serve --host=127.0.0.1 --port=8000`) |
| Test profile | admin@admin.com / secret — full admin role, all 35 permissions |
| Database | Fresh `migrate:fresh --seed` baseline: Centros 4, Combustibles 28, Personal 5, Vehículos 6, TicketStatus 5, 0 transaction rows |
| Output root | `docs/testing/` |
| Constraint | No app source code modified; only evidence artifacts produced in `docs/testing/` |
| Approved flows | 11 (login, dashboard, centros, personal, vehículos, recursos, tickets, lotes, reportes, auditoría, logout) |

**Scope notes**
- Transactional records (Recursos, Tickets, Lotes, plus the CRUD-editable Centros/Personal/Vehículos) were **created through the UI** during the test; they are normal test data, identifiable by the `MTAO`/`MTANY` token suffixes (e.g. facturas `FT-<RUNTOK>-01`, patentes `TX<RUNTOK>`, emails `explo.<run>@example.com`).
- A negative login case was executed. For "unauthorized action": the admin profile has all permissions, so a permission-denied (403) state is not reachable with this profile; the equivalent unauthenticated guard was instead verified (direct access to `/centro_costos` after logout redirects to `/login`).

## 3. Environment and Assumptions

| Aspect | Value |
|---|---|
| Runtime | PHP 8.3.6, Laravel 10 (Fortify auth, spatie/laravel-permission, Sanctum) |
| DB | MySQL on 127.0.0.1:3307, database `vales_2` |
| Server | Started locally via `php artisan serve` (port 8000) after preflight showed no listener |
| Browser automation | Playwright (Node), external Chrome (`google-chrome-stable`, headed, 1440×900), fallback headless Chromium |
| Preflight | `GET /login` → 200 before testing |
| Evidence | 37 screenshots under `docs/testing/screenshots/<flow>/`; composed in a single coherent run on a fresh seed |

**Assumptions**
- The seeded data (centros/combustibles/personal/vehículos) is the intended baseline; numbers observed (e.g. 4 centros, 5 personal) match the documented seed.
- "Recursos" accumulators were validated by creating 2 resources (500 L + 300 L) and confirming the "Bolsas de Combustible" card and table respond with the correct disponibilidad before and after emission.

## 4. Executed Flows

| # | Flow | Positive path | Negative path | Screenshots |
|---|---|---|---|---|
| 1 | Login | admin credentials → dashboard | invalid password rejected with error | 3 |
| 2 | Dashboard/Home | loads, sidebar lists all modules | — | 1 |
| 3 | Centros de Costo | list → create → edit | — | 5 |
| 4 | Personal | list → create (with centro) → edit | — | 5 |
| 5 | Vehículos | list → create (patente + chofer datalist) → edit | — | 5 |
| 6 | Recursos | list (empty) → create ×2 → verify accumulators → edit | — | 7 |
| 7 | Tickets | list → create (empleado datalist + litros + combustible) → edit | — | 5 |
| 8 | Lotes | list → create (centro + empleados + litros) → Generar tickets | — | 5 |
| 9 | Reportes | view form; both downloads (Vales Consumidos / Emitidos) | — | 2 |
| 10 | Auditoría | activity timeline shows records created in this session | — | 1 |
| 11 | Logout | closes session | unauthenticated access to `/centro_costos` → redirected to `/login` | 2 |

## 5. Flow-by-Flow Result

| # | Flow | Result | Notes |
|---|---|---|---|
| 1 | Login | **OK** | Invalid creds stay on `/login` with error; valid creds → dashboard; Post-login sidebar renders all 11 modules (Tickets, Gestión Tickets, Recursos, Lotes, Informes, Personal, Vehículos, Centros de Costo, Roles y Permisos, Auditoría, Roles). |
| 2 | Dashboard | **OK** | "Bienvenido" + user name/email rendered in header. |
| 3 | Centros de Costo | **OK** | Create persisted; edit persisted and reflects new name in list. |
| 4 | Personal | **OK** | Create with centro de costo persisted; edit (rename) persisted. |
| 5 | Vehículos | **OK** | Create with marca/modelo/patente/chofer(datalist)/centro persisted; edit persisted. |
| 6 | Recursos | **OK** | Two resources created (500 + 300 L); accumulator card updates (see findings: status/accumulator caveat); edit via direct URL updates litros (500 → 600 L). |
| 7 | Tickets | **OK** | Create with empleado datalist (hidden `personal_id` populated), litros 100, combustible + centro; shows "Asignado 100,00 L / Disponible decrements"; edit (100 → 120 L) persisted. |
| 8 | Lotes | **OK** | Lote create loads employees by centro (2 rows), saves; "Generar" produced flash: *"Lote generado exitosamente. Se crearon 1 tickets."* |
| 9 | Reportes | **OK** | `GET /reporte/consumidos` and `/reporte/emitidos` → **200** with `text/csv` attachment (`reporte-consumidos.csv`, `reporte-emitidos.csv`). |
| 10 | Auditoría | **OK** | Timeline lists the session's created records (centro, personal, vehicle, resources, tickets, lotes). |
| 11 | Logout | **OK** | Session closed; direct `/centro_costos` access redirects to `/login`. |

**No Blocked flows.** Every approved flow completed.

## 6. Key Evidence

- Invalid login shows the Laravel/Fortify default message **"These credentials do not match our records."** (in English) — screenshot `01-login/02-login-invalid.png` (OCR-confirmed).
- Recurso creation with `litros = 5000` is **rejected** with *"The litros field must not be greater than 999.99."* (server validation `StoreRecursoRequest`), while `499,99`-class values succeed — no client-side hint exists.
- Ticket emission decrements inventory live: after creating a 100 L ticket, the card showed `Disponible 400,00 L / Asignado 100,00 L`.
- Lote generation created real tickets and flashed a success message.
- Newly created recurso `FT-<run>-02` (300 L) is stored with `activo = 0` and is labeled **"Inactivo"** in the list and excluded from the "Bolsas de Combustible" totals, while its sibling with emissions (`activo = 1`) is counted. Observed in UI and confirmed in DB (`Recreto 1 activo=1 emit=130`, `Recreto 2 activo=0 emit=0`).
- Reports downloads respond with HTTP 200 and a CSV attachment for both Vales Consumidos and Vales Emitidos.
- 0 console errors, 0 page errors, 0 HTTP ≥ 400 across the whole session.

## 7. Flow Evidence Gallery

### 7.1 Login
![login](screenshots/01-login/01-login-start.png)
![invalid-credentials](screenshots/01-login/02-login-invalid.png)
![dashboard-after-login](screenshots/01-login/03-login-ok-dashboard.png)

### 7.2 Dashboard / Home
![home](screenshots/02-dashboard/01-header-home.png)

### 7.3 Centros de Costo
![list](screenshots/03-centros/01-list.png)
![create-filled](screenshots/03-centros/02-create-filled.png)
![created](screenshots/03-centros/03-created.png)
![edit-form](screenshots/03-centros/04-edit-form.png)
![edited](screenshots/03-centros/05-edited.png)

### 7.4 Personal
![list](screenshots/04-personal/01-list.png)
![create-filled](screenshots/04-personal/02-create-filled.png)
![created](screenshots/04-personal/03-created.png)
![edit-form](screenshots/04-personal/04-edit-form.png)
![edited](screenshots/04-personal/05-edited.png)

### 7.5 Vehículos
![list](screenshots/05-vehiculos/01-list.png)
![create-filled](screenshots/05-vehiculos/02-create-filled.png)
![created](screenshots/05-vehiculos/03-created.png)
![edit-form](screenshots/05-vehiculos/04-edit-form.png)
![edited](screenshots/05-vehiculos/05-edited.png)

### 7.6 Recursos
![empty-list](screenshots/06-recursos/01-list-empty.png)
![create-1](screenshots/06-recursos/02-create-01-filled.png)
![recurso-1-created](screenshots/06-recursos/03-recurso-1-created.png)
![create-2](screenshots/06-recursos/02-create-02-filled.png)
![recurso-2-totals](screenshots/06-recursos/04-recurso-2-totals.png)
![edit-form](screenshots/06-recursos/05-edit-form.png)
![edited](screenshots/06-recursos/06-edited.png)

### 7.7 Tickets
![list](screenshots/07-tickets/01-list.png)
![create-filled](screenshots/07-tickets/02-create-filled.png)
![created](screenshots/07-tickets/03-created.png)
![edit-form](screenshots/07-tickets/04-edit-form.png)
![edited](screenshots/07-tickets/05-edited.png)

### 7.8 Lotes
![list](screenshots/08-lotes/01-list.png)
![empleados-loaded](screenshots/08-lotes/02-empleados-loaded.png)
![form-filled](screenshots/08-lotes/03-form-filled.png)
![lote-created](screenshots/08-lotes/04-lote-created.png)
![generate-result](screenshots/08-lotes/05-generate-result.png)

### 7.9 Reportes
![form](screenshots/09-reportes/01-form-view.png)
![after-downloads](screenshots/09-reportes/02-reportes-after.png)

### 7.10 Auditoría
![timeline](screenshots/10-auditoria/01-timeline.png)

### 7.11 Logout
![logged-out](screenshots/11-logout/01-logged-out.png)
![unauth-redirect](screenshots/11-logout/02-unauth-redirect.png)

## 8. Prioritized Findings

### High
- **H-01 — None.** No critical-path blocker was found in the 11 flows.

### Medium
- **M-01 — New Recursos are marked "Inactivo" and excluded from stock accumulators.** A recurso created through the UI (`FT-<run>-02`, 300 L) appears with the `Inactivo` badge while its sibling with emissions shows `Activo`. `RecursoController@index` builds "Bolsas de Combustible" only from `activo=true` recursos, so freshly registered stock is invisible in the card until something marks it active (DB evidence: recurso 1 `activo=1 emit=130`, recurso 2 `activo=0 emit=0`). Risk: operators cannot see newly loaded fuel in the accumulator. *Evidence: `06-recursos` screenshots; DB rows after session.*
- **M-02 — Invisible domain limit: `litros` > 999,99 on a Recurso is rejected.** `StoreRecursoRequest` caps `litros` at **999.99** with no client-side feedback; a realistic bulk purchase entry (e.g. 5000 L) bounces a full form with an English validation message. If purchases routinely exceed 1,000 L this breaks the primary intake workflow and forces workarounds (splitting facturas).
- **M-03 — Recurso editing is not reachable from the list.** The Recurso index "Acciones" column offers only `Timeline` and `Eliminar`; there is no `Editar` action, unlike every other CRUD module. Editing requires guessing the `/recursos/{id}/edit` URL.

### Low
- **L-01 — Unlocalized validation messages.** Fortify's "These credentials do not match our records." and "The litros field must not be greater than 999.99." are shown in English inside a Spanish-only UI.
- **L-02 — Silent datalist selection failure.** The empleado/chofer fields (Alpine handler) clear the hidden `personal_id`/`chofer_id` if the typed text does not exactly match an option value; the form then fails later server-side with a generic error with no inline hint that the person wasn't selected.
- **L-03 — Multiple `[type=submit]` buttons per page.** Sidebar "Cerrar sesión" and header "Salir" are both submit buttons, so a stray Enter inside a form hits a logout rather than the intended action; also duplicated logout affordances.
- **L-04 — Inconsistent create-button labels and input types.** "Crear Vale" vs "Crear nuevo personal" vs "Crear Nuevo Vehículo"; the Personal form uses the non-standard `type="telefono"` (treated as plain text).
- **L-05 — Low automability of data rows.** Lists expose no `data-testid`; flows were scripted via text/href selectors (system `href` resolves to absolute URLs, so attribute selectors on `<a href>` require substring matching).

## 9. Non-invasive Recommendations

- **M-01:** On the Recurso index, render the accumulator from *all* resources with `disponible > 0` (or surface stock independently of the `activo` flag), or review where `activo` is flipped so a freshly created recurso is counted immediately. This is a display/data-aggregation change, no schema impact.
- **M-02:** Mirror the `999.99` cap client-side (`max` attribute + helper text under "Litros") so operators see the limit before submitting; pair with a Spanish message.
- **M-03:** Add an "Editar" action to the Recurso index row actions, mirroring the other modules.
- **L-01:** Localize error/validation strings (Lang/es or `Lang::setLocale`) — lowest effort: translate the shared validation resource and Fortify messages.
- **L-02:** When the datalist match is empty, show inline feedback (e.g. border error + "Seleccione una opción de la lista") instead of failing later.
- **L-03:** Scope submit handling: give the content form's submit button a distinct `name`/`id` and/or add `keydown` guard; consider removing the duplicate header logout.
- **L-05:** Add `data-testid` attributes to row actions and create buttons to stabilize future automation (improves a11y-oriented semantics too).

## 10. Residual Risks and Next Steps

- **Residual risks**
  - Permission-denied (403) flows were not exercised: the admin profile has every permission, so role-restricted paths (e.g. a `puede_ver_informes=false` user hitting `/reporte`) remain untested. Consider a second profile with limited permissions.
  - Responsive/mobile sidebar behavior (hamburger overlay) was not exercised at small viewports.
  - Report exports were validated at HTTP level (200 + attachment); file *content* correctness (CSV columns) not deeply verified.
  - Recurso `activo` lifecycle deserves a focused review of `RebalanceRecursosService` to confirm intended semantics.
- **Next steps**
  - Optionally add one negative NFR check: permission-restricted profile + `/reporte` → expect 403.
  - Repeat on a small viewport to cover the mobile navigation overlay.
  - Decide on M-01/M-02/M-03 fixes (they are behavior changes → require a product decision, not a test artifact).

---

*Evidence under `docs/testing/screenshots/` is self-contained; all image links are relative to this report file.*