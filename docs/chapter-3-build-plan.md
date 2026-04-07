# Chapter 3 — Build plan (phone-first identity, role dashboards, POS & invoice ledger)

This document defines the **next major iteration** after [`chapter-2-build-plan.md`](./chapter-2-build-plan.md). Chapter 3 assumes Chapter 2 features are in place (billing, classes, inventory, trainer commissions, etc.) and introduces **breaking or wide-ranging changes** to identity, authentication, role experiences, and how sales are captured in the UI—while keeping the **invoice + payment** model as the system ledger for revenue.

**Goals (summary)**

| Theme | Intent |
|-------|--------|
| **Phone as primary identity** | Every user is keyed by **local mobile number** (10 digits: `07` or `09` + 8 digits). Email is **optional**. **Login identifier is phone**, not email. |
| **Storage rules** | Store **national format without country code** — exactly **10 characters** as **VARCHAR(10)** (leading `0` preserved: e.g. `0912345678`). |
| **Role scope & dashboards** | Explicit **scopes per role** (what each role may see/do); replace placeholder portals with **real dashboards** aligned to those scopes. |
| **POS merchandise** | **POS-like** checkout UI; **customer (member) optional** (walk-ins); every completed sale still creates a **paid invoice** + completed **payment** in the background. |
| **Invoice as source of truth** | **All** billable flows (membership fees, merchandise, and any future retail) **record through `Invoice` + `Payment`** so reporting and audit stay unified. |

---

## Conventions to preserve (carry forward from Chapter 2)

Chapter 2 non-negotiables still apply unless explicitly superseded below:

| Area | Chapter 3 addition / override |
|------|-------------------------------|
| **Identity** | **Phone** is the canonical login and lookup key for users; **email** optional and not required for sign-in. |
| **Repositories / transactions** | Keep `DB::transaction` for multi-step writes; merchandise and membership flows that create money movement must still end in **invoice + payment** rows. |
| **No parallel ledgers** | Do not add a separate “cash book” for POS; **invoice + payment** remains the only monetary ledger for app-recorded sales. |
| **Architecture** | Avoid new layers (DTOs, separate service layer) unless migrating a whole module; extend existing controllers/repos like Chapter 2. |

**Superseded by Chapter 3**

| Old assumption | New rule |
|----------------|----------|
| Email is primary for login / uniqueness | **Phone** is primary for login; validate and normalize to 10-digit local format. |
| Merchandise requires a member `user_id` | **Optional**; allow walk-in / anonymous retail with `invoices.user_id` null (or a dedicated “walk-in” convention—see Phase 3). |

---

## Phone number specification (normative)

These rules should be enforced in **validation**, **model mutators/accessors if needed**, and **migration** comments so behaviour stays consistent.

| Rule | Detail |
|------|--------|
| **Format** | Exactly **10** characters: either `07` + **8** digits, or `09` + **8** digits (`[0-9]`). |
| **Storage** | Column type **`VARCHAR(10)`** (not integer—leading zero matters). |
| **Uniqueness** | **Unique** among non-deleted users (soft-deleted users may need the same rules as today for `email` uniqueness). |
| **No country code in DB** | Do not store `+251` in this column; if the UI collects international format, **normalize to local 10-digit** before save. |
| **Display** | Format for display is a presentation concern; DB always holds the canonical 10-digit string. |

**Regex (illustrative):** `^(07|09)\d{8}$`

**Login:** Authenticate using this field (e.g. `phone` credential) with Laravel’s `Auth::attempt` adapted via custom user provider or `retrieveByCredentials` / Fortify/Breeze customization—implementation detail belongs in Phase 1 tasks.

---

## Role scopes & dashboards (definition task)

Before building UIs, **document and implement** a single source of truth for permissions—either:

- **Route middleware + named abilities** aligned with existing `user-access` style, **or**
- A small **config map** (`config/roles.php`) listing allowed route prefixes per role,

…plus a one-page **matrix** in this doc or `docs/roles-and-scopes.md` (recommended): rows = roles, columns = feature areas (memberships, invoices, POS, classes, attendance, reports, …).

**Roles in scope (current codebase)**

| Role | Chapter 3 dashboard intent (high level) |
|------|----------------------------------------|
| **admin** | Full gym ops: users, billing, inventory POS, classes, commissions, reports—extend existing admin home. |
| **trainer** | Sessions assigned, commission summary, profile; minimal noise. |
| **reception** | Check-in, bookings, quick member lookup, possibly POS handoff—define explicitly. |
| **member** | Own membership, class bookings, history—no staff tools. |

Each phase below should **list concrete routes/pages** per role so “dashboard” is not vague.

---

## Phase 1 — Schema & migration: phone-first `users`

**Goal:** `users.phone` is **VARCHAR(10)**, validated format, **unique**; `email` **nullable**; indexes updated; data migration for existing rows.

**Depends on:** backup / staging strategy for production.

| # | Task | Check |
|---|------|-------|
| 1.1 | Migration: alter `users` — `phone` → `VARCHAR(10)`; enforce **nullable(false)** for phone after backfill (or keep nullable only during migration script). | [x] |
| 1.2 | Migration / artisan command: **normalize existing** `phone` values to 10-digit local format where possible; flag or admin-review rows that fail. | [x] |
| 1.3 | `email` **nullable**; unique index on `email` only where `email` IS NOT NULL (MySQL 8+ functional / partial unique, or app-level uniqueness validation—document choice). | [x] |
| 1.4 | Update `User` model: `$fillable`, casts, factories, seeders, Breeze registration, `UserRepository` to match. | [x] |
| 1.5 | Update **all** forms and DataTables that show or edit phone/email validation messages consistently. | [x] |

**Phase 1 implementation notes:**

- **Migration:** `database/migrations/2026_04_11_100000_chapter3_phase1_users_phone_string_email_nullable.php` — `phone` as `VARCHAR(10)` NOT NULL UNIQUE; `email` nullable; drops/re-adds unique on `email` after backfill; `down()` is intentionally non-reversible.
- **Backfill:** migration maps legacy bigint `phone` to 10-char strings; `app/Console/Commands/UsersNormalizePhonesCommand.php` (`users:normalize-phones {--fix}`) validates and optionally normalizes rows post-deploy.
- **Email uniqueness:** Laravel’s `unique:users,email` treats multiple `NULL` as distinct in MySQL; no partial index required for typical installs.
- **Validation / helpers:** `app/Support/PhoneNumber.php` — normalize, `^(07|09)\d{8}$` validation, optional email rules for registration/admin.
- **Login:** Phase 2 uses **phone + password** on `auth/login`; registration and admin CRUD remain phone + optional email as above.

---

## Phase 2 — Authentication: sign in (and register) with phone

**Goal:** Login uses **phone + password**; registration collects phone as required; email optional; password reset flow decided when email absent.

| # | Task | Check |
|---|------|-------|
| 2.1 | Replace email-based login in Breeze views/controllers with **phone** credential; `AuthenticatedSessionController` / `RegisteredUserController` / `PasswordReset` as needed. | [x] |
| 2.2 | `User` model: implement `Fortify`/`Authenticatable` contract requirements if using custom `findForPassport` / `retrieveByCredentials` pattern appropriate for Laravel version. | [x] |
| 2.3 | **Password reset:** define behaviour if `email` is null (e.g. admin-only reset, or future SMS—document “out of scope” if deferred). | [x] |
| 2.4 | Tests: login success/failure by phone; registration with/without email. | [x] |

**Phase 2 implementation notes:**

- **Login:** `AuthenticatedSessionController` validates and normalizes `phone`, then `Auth::attempt(['phone' => …, 'password' => …])`. The default Eloquent user provider queries `users.phone`; no Fortify / custom provider class.
- **Views:** `auth/login` uses `phone`; `auth/passwords/forgot-password` uses `phone` and resolves the user before calling `Password::sendResetLink(['email' => …])`.
- **Reset token flow:** Email in the mailed link is unchanged (Laravel broker). `auth/passwords/reset-password` form action corrected to `route('password.store')` (was wrongly pointing at authenticated `password.update`).
- **No email on account:** Forgot-password shows a clear error asking the member to contact the gym administrator; SMS reset is out of scope.
- **Tests:** `AuthenticationTest` and `PasswordResetTest` use phone for login / forgot-password; registration with/without email remains in `RegistrationTest`.

---

## Phase 3 — Merchandise POS UI + optional customer + invoice guarantee

**Goal:** POS-like UX; **customer optional**; every completed sale creates **`Invoice` (merchandise, paid) + `Payment` (completed)**; align with unified ledger.

| # | Task | Check |
|---|------|-------|
| 3.1 | Repository: extend `MerchandiseRepository::checkout()` (or equivalent) to allow **`user_id` null**; set `invoice_source` / `user_id` consistently; ensure `InvoiceRepository` / validation updated. | [x] |
| 3.2 | Migration if needed: confirm `invoices.user_id` nullable (already true from Ch.2—recheck); document “walk-in” display label in UI. | [x] |
| 3.3 | **POS UI:** redesign checkout Blade + JS—quick line items, optional customer picker, large tap targets, minimal steps (match AdminLTE assets, no new SPA framework). | [x] |
| 3.4 | Remove mandatory “Customer (member) *” from validation; optional select or “Walk-in” default. | [x] |
| 3.5 | Regression tests: checkout with member, checkout walk-in, stock + invoice + payment assertions. | [x] |

**Phase 3 implementation notes:**

- **`invoices.user_id`:** Already nullable (`2026_04_09_100001_add_merchandise_fields_to_invoices_and_payments.php`); no new migration.
- **`InvoiceRepository::store`:** Merchandise invoices no longer require `user_id`; membership invoices still require `membership_id`.
- **`MerchandiseRepository::checkout`:** Accepts nullable `user_id`; `MerchandiseCheckoutController` validates `user_id` as optional `exists:users,id` scoped to `role = member`.
- **UI:** `resources/views/pages/admin/merchandise/checkout.blade.php` — default **Walk-in**, `form-control-lg` / large buttons, **Quick add** product chips + line table, member labels show phone (and email when present).
- **Display:** Invoice PDF/HTML partial, invoice DataTables name column, and merchandise email greeting use **Walk-in** when no member is linked.

---

## Phase 4 — Role scopes & per-role dashboards

**Goal:** Defined scopes; each role lands on a **real dashboard** (not “Coming soon”) with navigation limited to allowed areas.

| # | Task | Check |
|---|------|-------|
| 4.1 | Authoritative **role → allowed routes** map (config or middleware); align `UserAccess` / `routes/web/portal.php`. | [x] |
| 4.2 | **Admin** dashboard: reinforce KPIs + shortcuts (POS, memberships, invoices) as needed. | [x] |
| 4.3 | **Trainer** dashboard: upcoming sessions, commission snapshot, link to profile. | [x] |
| 4.4 | **Reception** dashboard: attendance, today’s bookings, member search—scoped to reception role. | [x] |
| 4.5 | **Member** dashboard: membership status, book class, read-only history. | [x] |
| 4.6 | Smoke tests: each role logs in and hits dashboard without 404/403 loops. | [x] |

**Phase 4 implementation notes:**

- **Middleware:** `UserAccess` accepts **multiple roles** (`user-access:admin,reception`). Routes split into `routes/web/admin_shared.php` (desk + ops, no ledger UI) and `routes/web/admin_only.php` (dashboard KPIs, invoices, payments, revenue, products CRUD, staff, audit, trainer commissions, package/class **admin** mutations).
- **Reception:** Uses AdminLTE `pages.admin.inc.app` with sidebar from `nav.blade.php` — **Front desk** branding; hidden: dashboard, invoices, payments, staff, audit, inventory admin, package/class **add** & trainer commission admin menus. Can use users, memberships, packages **list/view**, gym classes & schedules **list/view**, class bookings, attendance scan, POS checkout.
- **Config:** `config/roles.php` documents scope; enforcement is route-level.
- **Trainer portal:** `trainer.home` shows upcoming sessions + monthly commission total; `trainer/profile` (GET/POST) edits trainer profile via `Trainer\ProfileController` (portal layout).
- **Reception portal:** `reception.home` shows today’s bookings table + large buttons to users, memberships, POS, attendance.
- **Member portal:** `member.home` shows active membership summary, link to group classes, recent booking history table.
- **Tests:** `tests/Feature/RoleAccessTest.php` — admin vs reception vs trainer/member smoke checks.

---

## Phase 5 — Reporting & consistency: invoices as the transaction ledger

**Goal:** Reports that speak “revenue” or “sales” prefer **`Invoice` + `Payment`** (completed) as already designed; membership and merchandise both visible; no duplicate definitions.

| # | Task | Check |
|---|------|-------|
| 5.1 | Dashboard / export: include merchandise invoices in revenue breakdown where appropriate (`invoice_source` filter). | [ ] |
| 5.2 | Documentation: one short paragraph in [`features.md`](./features.md) or here—**all recorded sales → invoice + payment**. | [ ] |
| 5.3 | Optional: reconcile report for “unpaid merchandise” if any flow allows it—should be none for POS cash-style completion. | [ ] |

**Phase 5 implementation notes:** _(fill when done.)_

---

## Optional follow-ups (not required to close Chapter 3)

| Item | Note |
|------|------|
| SMS OTP for login / reset | Out of band; requires provider and budget. |
| Barcode / SKU scanner in POS | UI hook only; hardware integration later. |
| Dedicated “walk-in customer” user record | Only if business requires attributed analytics; otherwise `user_id` null is enough. |

---

## Phase dependency diagram (Chapter 3)

```
Phase 1 (users schema: phone VARCHAR, email optional)
    ↓
Phase 2 (login/register with phone)
    ↓
Phase 3 (POS UI + optional customer + invoice/payment unchanged as ledger)
    ↓
Phase 4 (role scopes + dashboards)
    ↓
Phase 5 (reporting & ledger consistency)
```

**Parallelism:** Phase 4 can start after Phase 2 if routes are stable; Phase 3 (POS) can overlap Phase 4 **only** if walk-in invoice rules are merged first (Phase 3.1–3.2 before heavy dashboard work).

---

## What to do first (recommended order)

1. **Phase 1.1–1.2** — Schema + backfill; without clean phone data, auth and POS reports will drift.
2. **Phase 2** — Phone login; otherwise staff cannot dogfood Chapter 3.
3. **Phase 3.1–3.4** — POS behaviour and UX; quick win for operations.
4. **Phase 4** — Dashboards per role; stakeholder-visible.
5. **Phase 5** — Polish numbers and docs.

---

*Update this document when a phase is completed or scope changes.*
