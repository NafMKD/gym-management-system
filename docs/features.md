# Gym Management System — Dependencies & Feature Status

This document maps the **Software Requirements Specification** ([`Gym_Management_System_SRS.pdf`](./Gym_Management_System_SRS.pdf), dated December 28, 2024) to the **current codebase**, records a **dependency analysis**, and lists **extra capabilities** already built beyond the SRS bullets. **Mobile app integration** (SRS §2.1 item 6) is called out but not analyzed in depth per project direction.

**Status legend**

| Status | Meaning |
|--------|---------|
| **Done** | Implemented end-to-end for the web admin flow (or clearly wired in code). |
| **Partial** | Exists in a limited form, placeholder UI, or only part of the requirement is met. |
| **Not implemented** | No meaningful code/schema/routes for this requirement. |

---

## 1. Architecture snapshot

| Layer | Technology |
|-------|------------|
| Backend | **PHP 8.2+**, **Laravel 11** (`laravel/framework` ^11.31) |
| Auth UI | **Laravel Breeze** (Blade + Tailwind) |
| Frontend build | **Vite 6**, **Tailwind CSS 3**, **Alpine.js 3** |
| HTTP server | Typical **PHP-FPM** + **Nginx/Apache** (not pinned in repo) |
| Database | **MySQL/MariaDB/SQLite** via Laravel (see `.env`; migrations are database-agnostic SQL) |

**Application shape**

- Primary product surface is the **`/admin/*`** area, protected by `auth` + `user-access:admin` (`routes/web.php`, `App\Http\Middleware\UserAccess`).
- **No `api.php`** routes file; there is **no REST API** for a mobile client in this tree.
- **Role-based redirects** after login (`AuthenticatedSessionController`) send `admin` → `admin.home`, and `trainer` / `reception` / `member` to `trainer.home`, `reception.home`, `member.home` — but **those named routes are not registered** in `routes/`. Only the admin route group is required from `web.php`. This is a **functional gap** for non-admin users until routes and views are added or redirects are changed.

---

## 2. Dependency analysis

### 2.1 PHP (Composer) — direct dependencies

Versions below are from `composer show --direct` on this project (resolved patch versions may drift slightly after `composer update`).

| Package | Constraint / resolved | Role in this project |
|---------|----------------------|----------------------|
| `php` | ^8.2 | Runtime |
| `laravel/framework` | ^11.31 → **v11.36.x** | Core framework: routing, Eloquent, queues, mail, etc. |
| `laravel/breeze` | ^2.3 → **v2.3.0** | Login, registration scaffolding, password reset views |
| `laravel/tinker` | ^2.9 | REPL (`php artisan tinker`) |
| `barryvdh/laravel-dompdf` | ^3.0 → **v3.0.0** | **PDF generation** (package published/config present); **not referenced in application code** yet — invoices are HTML Blade views only today |
| `endroid/qr-code` | ^6.0 → **v6.0.3** | **Membership QR images** written under `public/qr_codes/`; payload encodes **membership ID** for attendance |
| `yajra/laravel-datatables-oracle` | ^11.1 → **v11.1.5** | **Server-side DataTables** JSON for admin lists (users, packages, memberships, invoices, payments, staff, audit trail) |

**Development / quality (not runtime on production unless you deploy dev tools)**

| Package | Role |
|---------|------|
| `pestphp/pest`, `pestphp/pest-plugin-laravel` | Tests |
| `laravel/pint` | Code style |
| `fakerphp/faker` | Test/demo data |
| `mockery/mockery` | Mocking in tests |
| `nunomaduro/collision` | CLI error rendering |
| `laravel/pail` | Log tailing (see `composer.json` `dev` script) |
| `laravel/sail` | Docker-based dev environment (optional) |

**Transitive highlights (implicit)** — Laravel pulls Symfony components, Monolog, Guzzle, etc.; DomPDF pulls `dompdf/dompdf`. No separate payment SDK (Stripe, Chapa, etc.) appears in `composer.json`.

### 2.2 JavaScript (npm) — direct dependencies

From `package.json` (versions are ranges; lockfile pins exact versions if `package-lock.json` is committed).

| Package | Role |
|---------|------|
| `vite` ^6.0 | Asset bundling, HMR |
| `laravel-vite-plugin` ^1.0 | Bridges Laravel ↔ Vite |
| `tailwindcss` ^3.1.0 | Utility CSS |
| `@tailwindcss/forms` ^0.5.2 | Form styling plugin |
| `postcss`, `autoprefixer` | CSS pipeline |
| `alpinejs` ^3.4.2 | Lightweight JS in Blade (if used in layouts) |
| `axios` ^1.7.4 | HTTP client (typical Breeze/Vite setup) |
| `concurrently` ^9.0.1 | Runs multiple processes in `composer.json` `dev` script |

**Runtime note:** Admin UI loads **AdminLTE-style** assets from `public/assets/` (jQuery, Bootstrap, etc.) in Blade layouts — that stack is **not** managed by npm in `package.json`; it is vendored under `public/`.

### 2.3 Infrastructure expectations

| Concern | Project state |
|---------|----------------|
| Queue | `database` queue tables migration exists; **no custom jobs** observed for renewals, mail, or billing |
| Scheduler | `routes/console.php` only registers the default `inspire` hourly command — **no** membership expiry cron, **no** renewal reminders |
| Mail | Laravel mail can be configured; **no** application `Mailable` classes found for invoices — **email notifications for invoices are not implemented** in app code |
| File storage | QR codes on **local disk** under `public/qr_codes/` |

---

## 3. SRS feature checklist (web scope; mobile deferred)

Aligned with SRS §2.1 **Functional Requirements** and **Additional Features**. Mobile-specific bullets are summarized as **out of scope** for this document.

### 3.1 Membership management

| # | Requirement | Status | Evidence / notes |
|---|-------------|--------|------------------|
| M1 | Member registration with personal details and membership plans | **Partial** | **Members** are created via **Admin → Users** (`UserController`: role `member`) with `first_name`, `last_name`, `email`, `phone`, `gender`. **Membership** is tied to a **package** (or custom dates) in **Admin → Memberships** (`MembershipController@store`). Public **Breeze registration** (`RegisteredUserController`) still uses a `name` field and `User::create([...])`, which does **not** match `users` table columns (`first_name`, `last_name`, …) — self-service registration is **likely broken** until aligned with the `User` model. |
| M2 | Subscription management: renewals, upgrades, cancellations | **Partial** | **Cancellation**: `MembershipController@cancel` sets `status` to `cancelled`. **Renewals / upgrades**: no dedicated flows (no new membership from an existing one, no proration). Workaround: operational/manual DB or new membership record outside dedicated UX. |
| M3 | Automatic alerts for upcoming renewals and expired memberships | **Not implemented** | No scheduled commands, notifications, or mail classes for renewals/expiry. |
| M4 | Reports on active, inactive, and cancelled memberships | **Partial** | **List views** with status badges via DataTables (`getMembershipsData`). No export, charts, or dedicated “report” page beyond listing. |

### 3.2 Billing and payment management

| # | Requirement | Status | Evidence / notes |
|---|-------------|--------|------------------|
| B1 | Multiple payment methods (cash and online) | **Partial** | **Cash** and **bank** transfer with optional `payment_bank` enum (`telebirr`, `cbe`, `boa`) and `bank_transaction_number` (`payments` migration). **No** card or third-party **online gateway** integration in Composer or routes. |
| B2 | Invoice generation and email notifications | **Partial** | **Invoices** auto-created with membership (`InvoiceRepository::store`): `invoice_number`, `amount`, `issued_date`, `due_date`, `paid`/`unpaid`. **HTML** detail view: `resources/views/pages/admin/invoices/view.blade.php`. **Email** of invoice: **not implemented** (no invoice `Mailable` / mail send). |
| B3 | Track subscription payments and pending dues | **Done** | `Payment` model links `invoice_id` + `membership_id`; partial payments supported; invoice marked paid when sum of completed payments ≥ amount (`InvoiceRepository::isInvoicePaid`). Unpaid invoices surfaced in UI. |
| B4 | Refund processing for cancellations | **Not implemented** | No refund model, negative payment, or reversal workflow. |

### 3.3 Class and schedule management

| # | Requirement | Status | Evidence / notes |
|---|-------------|--------|------------------|
| C1 | Class creation and scheduling with capacity limits | **Not implemented** | No `classes`, `schedules`, or bookings tables/migrations/controllers. |
| C2 | Booking and cancellation by members (through the app) | **Not implemented** | No member portal routes; no booking entities. (SRS “app” assumed mobile — still absent on web.) |
| C3 | Trainer availability tracking for scheduling | **Not implemented** | No availability model. |
| C4 | Conflict resolution for overlapping schedules | **Not implemented** | N/A without scheduling data. |

### 3.4 Trainer and staff management

| # | Requirement | Status | Evidence / notes |
|---|-------------|--------|------------------|
| T1 | Trainer profiles with qualifications and specializations | **Not implemented** | `users` table has basic fields only. **Staff** CRUD (`StaffController`) sets `role` to `trainer`, `reception`, or `admin` — no extra profile schema. |
| T2 | Scheduling for personal training sessions | **Not implemented** | No PT session entities. |
| T3 | Performance tracking and feedback collection | **Not implemented** | No feedback or rating models. |
| T4 | Calculation and tracking of income percentages from training sessions | **Not implemented** | No commission fields or calculations. |

### 3.5 Reporting and analytics

| # | Requirement | Status | Evidence / notes |
|---|-------------|--------|------------------|
| R1 | Membership trend analysis (new vs. renewals) | **Not implemented** | Dashboard is placeholder text (“Comming soon…”, `resources/views/pages/admin/index.blade.php`). No trend queries/charts. |
| R2 | Financial performance reports (income vs. expenses) | **Partial** | **Revenue**: `PaymentController@revenueOverview` + `getTotalRevenue` with filters (date range, method, status). **Expenses**: not modeled. |
| R3 | Inventory and equipment usage statistics | **Not implemented** | No inventory/equipment modules (see §3.7). |

### 3.6 Mobile app integration (SRS §2.1 item 6)

Per stakeholder direction, **this section is not expanded** into delivery tasks here. At a high level: there is **no** mobile app codebase in this repository and **no** JSON API for member self-service; push notifications and app-side booking are **Not implemented** from a product standpoint.

### 3.7 Inventory management (and SRS scope: facility / equipment)

| # | Requirement | Status | Evidence / notes |
|---|-------------|--------|------------------|
| I1 | Tracking gym merchandise | **Not implemented** | No products/stock tables. |
| I2 | Stock alerts for low inventory | **Not implemented** | — |
| I3 | Sales tracking and reporting | **Not implemented** | — |
| I4 | Integration with billing for merchandise purchases | **Not implemented** | Invoices are membership-scoped today. |

The SRS **introduction** also mentions **Facility and Equipment Management**; there are **no** equipment/facility entities in migrations or admin routes.

### 3.8 Additional features (SRS)

| # | Requirement | Status | Evidence / notes |
|---|-------------|--------|------------------|
| A1 | Staff commission tracking based on training sessions | **Not implemented** | — |
| A2 | Time-based subscriptions with automatic expiration | **Partial** | **Packages** have `duration` / `granted_days`; membership has `start_date`, `end_date`, `remaining_days`. **Expiration behavior**: `remaining_days` decrements on **each attendance check-in** (`AttendanceController@recordAttendance`), not via calendar cron. If `remaining_days` hits 0, status → `inactive`. **Calendar-day auto-expiry** without visit is **not** implemented as a scheduled job. |
| A3 | Optional 10-day extension with valid reason submission and approval | **Not implemented** | No extension request model or approval workflow. |

---

## 4. Extra capabilities present in code (not spelled out as SRS sub-bullets)

These are **implemented** (or wired) but are either operational extras or only loosely implied by the SRS.

| Capability | Status | Notes |
|------------|--------|-------|
| **QR-based attendance** | **Done** | Admin scan page `AttendanceController@showScanPage` / `recordAttendance`; QR encodes membership ID; validates active membership and dates. |
| **Membership ID card printing** | **Done** | `MembershipController@printIdCard`, `PrintBatch` model, Blade `memberships/id_card.blade.php`. |
| **Audit trail** | **Done** | `AuditTrail` + `AuditTrailObserver` on `User`, `Membership`, `Package`, `Invoice`, `Payment`, `Attendance`; admin list/detail. |
| **Admin dashboard** | **Partial** | Route exists (`DashboardController@index`); **content is placeholder** only. |
| **PDF export (DomPDF)** | **Not used in app** | Package installed; **no** controller call to generate PDF invoices yet. |

---

## 5. Database entities (implemented)

Rough map of **core tables** vs SRS modules:

| Table / area | SRS alignment |
|--------------|----------------|
| `users` | Members + staff (role enum) |
| `packages` | Membership plans |
| `memberships` | Subscriptions |
| `invoices`, `payments` | Billing |
| `attendances` | Check-ins (extends membership usage, not in SRS class list) |
| `print_batches` | ID card print batch positions |
| `audit_trails` | Change history |
| `jobs`, `cache`, `sessions` | Laravel infrastructure |

### Revenue ledger

All recorded sales follow one pattern: an **Invoice** (`invoice_source` is **membership** or **merchandise**) plus **Payment** rows. **Completed** payments drive revenue in the admin dashboard and CSV exports; membership fees and POS merchandise share this single ledger (no duplicate cash book).

---

## 6. Suggested priority order when resuming development

1. **Stabilize auth**: Fix or remove public registration vs `User` schema; define **trainer/reception/member** home routes or redirect strategy.  
2. **SRS gaps by business value**: Renewals/upgrades, email invoices, renewal/expiry **scheduler** + notifications, refunds if required.  
3. **Reporting**: Replace dashboard placeholder with KPIs promised in SRS.  
4. **Classes / trainers / inventory**: New schema + modules from greenfield.  
5. **Mobile/API**: Separate API design if a client app returns to scope.

---

*Generated from repository analysis and [`docs/Gym_Management_System_SRS.pdf`](./Gym_Management_System_SRS.pdf). Re-run this comparison after major merges.*
