# Chapter 2 — Build plan (phased checklist)

This document turns the gap analysis in [`features.md`](./features.md) into an ordered **build plan**. Phases are sequenced so **nothing in an earlier phase requires work from a later phase** (dependencies only flow downward).

**Do this first (before Phase 1 bulk work):** skim `app/Http/Controllers/Admin/*`, `app/Repositories/*`, `routes/web/admin.php`, and `resources/views/pages/admin/*` so every new file matches layout, naming, and error handling already in use.

---

## Conventions to preserve (non-negotiable)

Follow these so new code is indistinguishable from existing code.

| Area | Pattern in this codebase |
|------|---------------------------|
| **Controllers** | Namespace `App\Http\Controllers\Admin` for admin UI; extend `App\Http\Controllers\Controller`; inject repositories via `__construct()`; use `self::ADMIN_` for view names; flash keys `self::SUCCESS_`, `self::ERROR_`; wrap actions in `try` / `catch (Throwable $e)` where other admin actions do; docblocks on public methods (`@return View\|RedirectResponse` etc.) like `UserController`, `MembershipController`. |
| **Repositories** | One repository per aggregate where the project already uses them (`UserRepository`, `MembershipRepository`, …); extend `BaseRepository`; keep `DB::transaction` for multi-step writes; throw or return consistently with existing repos (see `InvoiceRepository`, `MembershipRepository`). |
| **Routes** | Register admin routes inside `routes/web/admin.php` under the existing `admin` prefix group from `routes/web.php`; nested `Route::group` with `'prefix'` + `'as'` for each resource; name routes `admin.{section}.{action}`. |
| **Views** | Blade under `resources/views/pages/admin/{area}/`; extend `pages.admin.inc.app`; reuse `@section('header')`, `content-header`, `content` like current list/add/view pages; AdminLTE assets from `public/assets/` as today. |
| **Lists** | Server-side DataTables: action method `getXData()` on the controller, `Yajra\DataTables\Facades\DataTables`, same column/badge patterns as `getMembershipsData` / `getInvoicesData`. |
| **Validation** | `Validator::make` or `$request->validate` as the nearest similar controller already does for that resource; keep messages and rules in the same style. |
| **Models** | Eloquent models in `app/Models`; `SoftDeletes` / `HelperTrait` where siblings use them; `$fillable` and relation methods named like existing models. |
| **Audit trail** | If a model is auditable today, new auditable models should use `AuditTrailObserver` — register in `App\Providers\AppServiceProvider::boot()` the same way as `Membership`, `Payment`, etc. |
| **Comments** | PHPDoc blocks on classes and public methods; short inline comments only where the existing files use them (avoid essay-style comments). |

Do **not** introduce new architectural layers (e.g. service classes, DTOs, API resources) unless the whole module is migrated together; that would break consistency with the rest of the app.

---

## Phase 1 — Foundation: auth, roles, and routing integrity

**Goal:** Remove broken paths and undefined routes so every role has a predictable outcome. Later phases assume login and redirects work and the `users` schema is the single source of truth.

**Does not depend on:** any feature phase below.

| # | Task | Check |
|---|------|-------|
| 1.1 | Align Laravel Breeze registration with `User` model fields (`first_name`, `last_name`, `phone`, `gender`, `role`) and hashed `password`; remove or replace invalid `User::create(['name' => ...])` usage in `RegisteredUserController`. | [x] |
| 1.2 | Either implement named routes `trainer.home`, `reception.home`, `member.home` with minimal Blade shells under `resources/views/pages/...`, **or** change `AuthenticatedSessionController` to redirect non-admin roles to a documented fallback (e.g. login with message) until portals exist — avoid `RouteNotFoundException`. | [x] |
| 1.3 | If public self-registration is out of scope, disable `register` routes/middleware consistently and document; if in scope, match validation to `UserController@store` conventions. | [x] |
| 1.4 | Reconcile `UserAccess` middleware with actual route names after 1.2 (each role must land somewhere valid). | [x] |
| 1.5 | Smoke-test: admin login → `admin.home`; each role path resolves without 500/404. | [x] |

**Phase 1 implementation notes:** Registration stays **enabled**; `RegisteredUserController` delegates to `UserRepository` (same hashing/fields as admin user creation), default role `member`, redirect to `dashboard`. Named route `dashboard` (`GET /dashboard`) forwards by role to `admin.home`, `trainer.home`, `reception.home`, or `member.home`. Portal UI uses `layouts.portal` and `pages.staff.home` / `pages.member.home` (“Coming soon.”). `routes/web/portal.php` holds trainer/reception/member route groups. `UserFactory` matches the real `users` columns. Auth tests: `verification.send` in verify-email view; `NewPasswordController` passes `token`/`email` into the reset Blade.

---

## Phase 2 — Scheduler, membership time rules, and notifications plumbing

**Goal:** Time-based behaviour (expiry, reminders) runs on the server without manual clicks. Billing email later reuses the same mail/queue setup.

**Depends on:** Phase 1 (stable auth not required for cron, but user records must be consistent).

**Does not depend on:** classes, inventory, or dashboard UI.

| # | Task | Check |
|---|------|-------|
| 2.1 | Add Laravel scheduler entry in `routes/console.php` (or `bootstrap/app.php` schedule callback per Laravel 11 docs) — keep style minimal like existing file. | [x] |
| 2.2 | Implement a command (e.g. `php artisan memberships:process-expiry`) that: finds memberships past `end_date` or with `remaining_days`/status rules per business choice; sets status to `inactive` or `cancelled` as appropriate; uses a small repository or query scope method, not fat controllers. | [x] |
| 2.3 | Decide and document: **calendar expiry** vs **visit-based `remaining_days` only**; align `AttendanceController` and expiry job so rules do not contradict each other. | [x] |
| 2.4 | Configure mail (`.env`); add `Mailable` classes only if they follow project naming and are called from jobs/commands, not from controllers directly for heavy work. | [x] |
| 2.5 | Queue driver: ensure `jobs` table migration matches deployment; document `queue:work` for production. | [x] |
| 2.6 | Optional: `Notification` or simple mail for “expiring in X days” using the same user email fields as the rest of the app. | [x] |

**Phase 2 implementation notes:** `bootstrap/app.php` uses `withSchedule`: `memberships:process-expiry` daily **00:05**, `memberships:notify-expiring` daily **08:00**. `MembershipRepository::processExpiry()` / `getActiveMembershipsEndingInDays()` hold the queries. Commands live under `App\Console\Commands` (same style as `GenerateMissingInvoicesCommand`). Reminder email: `App\Mail\MembershipExpiringSoonMail` + `resources/views/emails/membership-expiring-soon.blade.php`, sent only from `memberships:notify-expiring`. Config `config/membership.php` (`MEMBERSHIP_EXPIRING_NOTIFY_DAYS`). Rules documented in [`membership-expiry-rules.md`](./membership-expiry-rules.md). `.env.example` comments for cron (`schedule:run`) and queue worker. `AttendanceController` uses the same calendar rule as the batch job (date-string comparison) and replaces `dd()` with log + JSON 500.

---

## Phase 3 — Membership lifecycle (admin): renewals, upgrades, cancellation policy

**Goal:** SRS membership management beyond single create/cancel: controlled renewals/upgrades and the optional 10-day extension with reason and approval.

**Depends on:** Phase 1 (users/memberships), Phase 2 optional for automated emails on status change.

**Does not depend on:** classes, inventory, reporting UI.

| # | Task | Check |
|---|------|-------|
| 3.1 | Design migrations for extension requests (reason, requested days, status `pending|approved|rejected`, approver `user_id`, timestamps) — mirror enum/string style of existing tables. | [x] |
| 3.2 | Add repository methods for create/update extension; keep transactions where multiple rows change (`Membership` + extension row). | [x] |
| 3.3 | Admin UI: list + approve/reject using DataTables pattern; only touch `Membership` dates/`remaining_days` in one place to avoid drift. | [x] |
| 3.4 | Renewal / upgrade flow: new membership from existing member or extend `end_date` — pick one strategy and implement in `MembershipController` + repository with validation consistent with `store`. | [x] |
| 3.5 | Register new models on `AuditTrailObserver` if they should appear in audit trail like other core entities. | [x] |

**Phase 3 implementation notes:** Table `membership_extension_requests` (`MembershipExtensionRequest` model). `MembershipExtensionRequestRepository` handles `store` / `approve` / `reject`; calendar + visit extension applies only via `MembershipRepository::extendActiveMembership()` (1–10 days). Admin UI: `MembershipExtensionRequestController`, routes `admin.memberships.extension_requests.*`, views under `pages/admin/membership_extension_requests/`. **Renewal:** `GET admin/memberships/renew/{membership}` → add form with `renew_from` + preselected member/package. **Upgrade:** `MembershipRepository::applyPackageUpgrade()` + `memberships/upgrade` Blade. Sidebar **Memberships** submenu lists Add, List, Extension requests. `MembershipExtensionRequest` is observed for audit trail. Feature test: `tests/Feature/MembershipLifecycleTest.php`. Pest `afterEach` resets Carbon test time for Feature tests.

---

## Phase 4 — Billing: invoice delivery, PDF, refunds

**Goal:** Close SRS billing gaps while staying on the existing invoice/payment model.

**Depends on:** Phase 1; Phase 3 only if refunds must reference cancelled memberships with extensions (can be minimal at first).

**Does not depend on:** classes, inventory, dashboard.

| # | Task | Check |
|---|------|-------|
| 4.1 | Invoice email: send after invoice creation or on button from invoice view — use `Mailable` + existing invoice Blade or a dedicated mail view mirroring print layout. | [x] |
| 4.2 | PDF export using already-installed `barryvdh/laravel-dompdf`: add controller action from invoice show, reuse invoice HTML, match branding already in `invoices/view.blade.php`. | [x] |
| 4.3 | Refunds: define whether refund is negative `Payment`, new `refunds` table, or invoice status — implement in `PaymentRepository` / `InvoiceRepository` with same transaction style as `store`. | [x] |
| 4.4 | Payment UI: ensure bank fields (`payment_bank`, `bank_transaction_number`) match validation in `PaymentController@store` and forms. | [x] |

**Phase 4 implementation notes:** Refunds are **`payments` rows** with `payment_type = refund` and **negative `amount`**; `InvoiceRepository::syncInvoiceStatusFromPayments()` keeps `paid` / `unpaid` aligned with net completed payments. `PaymentRepository::recordRefund()` + `POST admin/payments/refund`. Invoice email: `InvoiceMail` + `emails/invoice-mail` (Markdown); **send on membership create** (try/catch + log) and **“Email invoice”** on invoice detail. PDF: `InvoiceController@downloadPdf`, shared `invoices/partials/invoice-body.blade.php`, DomPDF-friendly logo. Add payment form: bank requires `payment_bank` (`telebirr` / `cbe` / `boa`), optional `bank_transaction_number`. **`routes/web.php`** uses `require` (not `require_once`) for `web/admin.php` / `web/portal.php` so route names stay available when the test app is refreshed. Tests: `BillingRefundTest`, `BillingEmailTest`, `BillingPdfTest`.

---

## Phase 5 — Reporting and dashboard

**Goal:** Replace dashboard placeholder with KPIs and lists that match SRS reporting (membership trends, financial summaries) using existing queries first.

**Depends on:** Phases 1–4 for meaningful data (Phase 4 optional for revenue accuracy).

**Does not depend on:** classes or inventory.

| # | Task | Check |
|---|------|-------|
| 5.1 | `DashboardController@index`: pass aggregates (counts by membership status, new memberships in period, revenue from `Payment` with `completed` status) — thin controller, calculations in repository or query scopes. | [x] |
| 5.2 | Replace “Comming soon” in `pages/admin/index.blade.php` with cards/tables consistent with AdminLTE markup used elsewhere. | [x] |
| 5.3 | Optional: simple chart JS only if the same asset pipeline (Vite vs public assets) is respected; avoid a new JS framework. | [x] |
| 5.4 | Export CSV for membership/payment reports optional; if added, follow DataTables export pattern already used or a single dedicated download action. | [x] |

**Phase 5 implementation notes:** `DashboardRepository` (read-only, not extending `BaseRepository`) supplies `getDashboardSummary()`: membership counts by `active` / `inactive` / `cancelled`, new memberships **this calendar month** (`created_at`), unpaid invoice count, **completed** payment revenue (this month + all time; net of refunds), last 6 months revenue series, recent memberships/payments. `DashboardController` passes `summary` to `pages/admin/index.blade.php`: AdminLTE **info-box** KPI row, **Chart.js** doughnut + bar (`public/assets/chart.js`, already in `layouts/script.blade.php`), small tables with links. CSV: `GET admin/export/memberships-csv` and `admin/export/payments-csv` streaming UTF-8 with BOM. Tests: `tests/Feature/DashboardTest.php`.

---

## Phase 6 — Classes, schedules, capacity, bookings (admin-first)

**Goal:** SRS class and schedule module: entities, admin CRUD, conflict avoidance, capacity. Member self-service can be added only after Phase 1 member routes exist.

**Depends on:** Phase 1 for trainer/member `User` references; **does not** depend on inventory or commissions.

**Does not depend on:** Phase 7–8.

| # | Task | Check |
|---|------|-------|
| 6.1 | Migrations: classes, schedules, bookings (or equivalent names) with foreign keys to `users` for trainer and member where applicable; indexes for time-range queries. | [x] |
| 6.2 | Models + relationships; register observers for audit if tables are auditable. | [x] |
| 6.3 | Repositories + admin controllers + `routes/web/admin.php` groups following existing naming. | [x] |
| 6.4 | Validation: no overlapping trainer assignments for the same slot (resolve conflicts in repository layer or Form Request if project adopts them consistently). | [x] |
| 6.5 | Admin UI: list/add/edit/view Blades under `pages/admin/...` with DataTables for lists. | [x] |
| 6.6 | If member booking is required: use Phase 1 member home + booking actions; otherwise admin-only booking for members. | [x] |

**Phase 6 implementation notes:** Tables `gym_classes` (class catalogue: capacity, duration, active flag), `class_schedules` (trainer `users.id`, `starts_at`/`ends_at`, optional `capacity_override`), `class_bookings` (`membership_id`, optional `booked_by_user_id`, status `pending|confirmed|cancelled|attended`). Models `GymClass`, `ClassSchedule`, `ClassBooking`; overlap enforced in `ClassScheduleRepository` (trainer double-booking); capacity + active membership enforced in `ClassBookingRepository`. Admin: `GymClassController`, `ClassScheduleController`, `ClassBookingController` + DataTables + sidebar **Classes**. Member: `GymClassBookingController`, routes `member.classes.*`, button from `member.home`. Audit observers registered for all three models. Tests: `tests/Feature/ClassScheduleTrainerOverlapTest.php`.

---

## Phase 7 — Inventory and merchandise

**Goal:** Stock, low-stock alerts, sales linkage to billing.

**Depends on:** Phase 4 if merchandise charges must create invoices/payments like memberships.

**Does not depend on:** Phase 6 or 8 (inventory is its own aggregate).

| # | Task | Check |
|---|------|-------|
| 7.1 | Migrations: products, stock movements or quantities, sales lines. | [x] |
| 7.2 | Repositories and admin CRUD consistent with `PackageController` / `UserController` patterns. | [x] |
| 7.3 | Low-stock threshold flag or scheduled check notification (reuse Phase 2 mail/queue). | [x] |
| 7.4 | Integrate merchandise checkout with `Invoice`/`Payment` models or document separate cash path — avoid parallel payment systems. | [x] |

**Phase 7 implementation notes:** Tables `products` (SKU, pricing, `stock_quantity`, `low_stock_threshold`, soft deletes), `merchandise_sale_lines` (per-invoice lines), `stock_movements` (`restock` / `sale` / `adjustment`). Invoices gained nullable `membership_id`, `user_id` (customer), `invoice_source` (`membership` \| `merchandise`); payments `membership_id` nullable. **Single billing path:** merchandise checkout uses `InvoiceRepository` + `PaymentRepository` (completed payment) in `MerchandiseRepository::checkout()`; stock decremented and sale movements recorded in one transaction. Admin: `ProductController` (DataTables, CRUD, restock/adjust on product view), `MerchandiseCheckoutController` (POS-style sale to a member user), sidebar **Inventory**. Low stock: `inventory:notify-low-stock` daily **07:30** in `bootstrap/app.php`, `LowStockProductsMail`, `config/inventory.php` (`INVENTORY_LOW_STOCK_NOTIFY_ENABLED`, `INVENTORY_LOW_STOCK_MAIL_TO`). `PaymentController` / listings resolve customer vs membership for display. Invoice PDF/email bodies support merchandise line items via `invoice-body` partial. Migration `2026_04_09_100001_*` drops FKs by querying `information_schema` when needed (MySQL naming). Tests: `tests/Feature/MerchandiseInventoryTest.php`.

---

## Phase 8 — Trainer profiles and commission (session-based)

**Goal:** Qualifications/specializations and commission from training sessions per SRS.

**Depends on:** Phase 6 for session-level commission accuracy; Phase 1 for staff users.

**Does not depend on:** Phase 7.

| # | Task | Check |
|---|------|-------|
| 8.1 | Extend `users` (trainer-only fields) or `trainer_profiles` table — follow migration style of existing `users` migration. | [ ] |
| 8.2 | Admin UI for trainer profile edit (reuse `StaffController` flows where possible). | [ ] |
| 8.3 | Commission calculation from completed PT sessions or manual admin entry — repository + report; reuse DataTables for commission list. | [ ] |
| 8.4 | Optional: feedback storage if still in scope; keep same audit/soft-delete conventions. | [ ] |

---

## What to do now (immediate focus)

These items unblock production confidence and everything else:

1. **Phase 1** — Fix registration vs `User` schema and undefined role routes (`trainer.home`, `reception.home`, `member.home`). Nothing else is safe to demo if login throws or data is inconsistent.
2. **Phase 2.1–2.3** — Scheduler + expiry rules aligned with attendance logic — otherwise membership state drifts in production.
3. **Phase 5.1–5.2** — Minimal dashboard counts — quick win for stakeholders without new domains.

Defer **Phase 6–8** until the foundation and billing/reporting baseline above are stable unless the client prioritizes classes before inventory.

---

## Phase dependency diagram (forward only)

```
Phase 1 (auth/routes)
    ↓
Phase 2 (scheduler / expiry / mail plumbing)
    ↓
Phase 3 (membership lifecycle & extensions)     Phase 4 (billing polish) ──┐
    ↓                                                                         │
Phase 5 (dashboard/reporting) ←────────────────────────────────────────────┘
    ↓
Phase 6 (classes/schedules) ──→ Phase 8 (trainer commission & profiles)
    |
    └── independent of Phase 7

Phase 7 (inventory) — may follow Phase 4 for unified invoicing
```

---

*This plan should be updated when a phase is completed or scope changes.*
