# Chapter 4 — Build plan (Front desk / reception shell & UX)

This document is the **next iteration** after [`chapter-3-build-plan.md`](./chapter-3-build-plan.md). Chapter 3 delivered **role scopes** and a **reception** role that can use selected `/admin/*` routes **without** invoices, payments, or revenue UI—but those flows still render inside **AdminLTE** (`pages.admin.inc.app`: sidebar + top bar).  

**Chapter 4** focuses on **reception-only experience**: a **calm, top-navigation shell** (like the **portal** layout members/trainers see at first login)—**no traditional sidebar**—so front-desk work feels **simple, fast, and glanceable**.

---

## Goals (summary)

| Theme | Intent |
|-------|--------|
| **One shell for reception** | Reception users work in a **dedicated layout** (`layout-top-nav` style): **top bar only** + main content—**no AdminLTE sidebar** for their shift. |
| **Align with “first dashboard” feel** | Match the **portal** pattern (`layouts.portal`): brand, minimal chrome, **horizontal wayfinding**, large actions where it helps. |
| **No duplicate business rules** | Keep **same controllers / repositories / validation** as today; change **presentation layer** (layouts, Blade structure, route grouping) unless a technical blocker forces a thin wrapper. |
| **Progressive rollout** | Ship **shell + home** first, then migrate **high-traffic desk pages** into the shell; keep `/admin/*` URLs working during transition if needed (or introduce `/reception/*` aliases). |

---

## UX & product principles (senior UI/UX)

These guide all Chapter 4 design decisions.

### 1. **Reduce cognitive load**

- Front desk is **interrupt-driven** (phone, queue, walk-ins). The UI should **not** compete with the gym floor.
- **One primary layout pattern** per session: reception never “jumps” between full admin chrome and minimal portal without a clear reason.
- **Hide complexity**: financial ledgers, exports, and admin-only tools stay **out** of reception shell (already enforced by Chapter 3 routes).

### 2. **Wayfinding: horizontal, not hierarchical**

- **Sidebar = deep hierarchy** (good for admins who live in the tool). **Reception = task switching** → prefer **top nav**: Home · Members · Memberships · POS · Bookings · (optional overflow “More”).
- Limit **5–7 primary nav items** at full width; use **dropdowns** only for true secondary items.

### 3. **Scanning & speed**

- **F-pattern**: title + short subtitle + **primary actions** (buttons) in the first screenful.
- **Tables**: readable row height, clear **time / name / action** columns; avoid dense DataTables styling on the home surface—link out to **list views** when power tools are needed.

### 4. **Touch-friendly where relevant**

- Many desks use **tablets**. Minimum **44×44px** tap targets for primary actions; avoid relying on tiny icon-only controls for core tasks.

### 5. **Consistency with existing stack**

- Stay on **AdminLTE + Bootstrap 4** assets already in the repo—**no new SPA framework**. Polish via **layout**, **spacing**, **typography**, and **component reuse** (`card`, `btn-lg`, `container`).

### 6. **Accessibility & clarity**

- Keep **logout** visible; show **who is logged in** (name/role).
- **Errors/success**: reuse existing flash patterns (`session('error')` / `success`) in the new shell.

---

## Current state (baseline)

| Area | Today |
|------|--------|
| **Reception “home”** | `reception.home` → **`layouts.reception`** + `pages/staff/reception-home.blade.php` — top-nav shell, **Front desk** branding; no sidebar. |
| **Desk work** | Links go to **`/admin/...`** pages using **`pages.admin.inc.app`** — **sidebar + `layouts.navigation`** (includes extra chrome). |
| **Mismatch** | First screen feels **light**; deeper tasks feel like **“admin app”** — confusing for a dedicated reception role. |

**Chapter 4 closes that gap** by moving reception-facing pages into the **same family** as `reception.home`.

---

## Target architecture (high level)

| Piece | Direction |
|-------|-----------|
| **Layout** | New `layouts.reception` (or extend `layouts.portal` with `@yield('desk_nav')`) — **single column**, `layout-top-nav`, **no sidebar**. |
| **Top navigation** | Links to reception tasks: **Desk home**, **Members**, **Memberships**, **POS**, **Bookings**, **Schedules** (read-only), etc.—mirror Chapter 3 **allowed** routes only. |
| **Routes** | **Phase 1:** reuse existing **`/admin/*`** shared routes (`admin_shared.php`); reception shell is **layout-only** on `reception.home`. Later phases may add **`/reception/...`** aliases or parameterized `@extends` for desk views. |
| **Views** | Either: (a) **parameterize** layout in existing admin views (`@extends($layout ?? 'pages.admin.inc.app')`), or (b) **thin reception Blade** copies that `@include` shared form partials. Prefer **(a)** for forms that are identical; use **(b)** only when markup diverges. |
| **Dashboard entry** | After login, reception already lands on `reception.home` — keep; ensure **all** desk pages feel like **extensions** of that home, not a different product. |

---

## Phase 1 — Reception shell + navigation map

| # | Task | Check |
|---|------|-------|
| 1.1 | Add **`layouts/reception.blade.php`**: portal-like top nav, container, flash alerts, footer scripts; **no** `@include('pages.admin.inc.nav')`. | [x] |
| 1.2 | Define **reception top-nav items** (config array or Blade partial) aligned with Chapter 3 **allowed** routes—no invoice/payment/staff links. | [x] |
| 1.3 | Wire **`reception.home`** to use the new layout (or confirm portal is alias; unify branding: “Front desk” vs app name). | [x] |
| 1.4 | Document **IA** in this file or `docs/reception-nav.md` (one page: list of links + route names). | [x] |

**Phase 1 implementation notes:** **`layouts.reception`** mirrors **`layouts.portal`** (flash, `layouts.footer`, `layouts.script`) but uses **`config/reception.php`** for brand (**Front desk** + logo → `reception.home`) and **`layouts/partials/reception-topnav`** for allowed **`admin.*`** desk routes. **`reception.home`** extends **`layouts.reception`**. **`layouts.portal`** no longer branches for reception (unused). **Route strategy for Phase 1:** keep existing **`/admin/*`** URLs from `admin_shared.php`; no duplicate `/reception/*` resource routes yet. IA: [`docs/reception-nav.md`](./reception-nav.md).

---

## Phase 2 — Move high-traffic desk pages into the shell

Priority order (typical shift):

1. **Users** (list / add / view / edit members)  
2. **Memberships** (list / add / view / renew flows as allowed)  
3. **Merchandise POS** (checkout)  
4. **Class bookings** (list / view / actions)  
5. **Packages** (read-only list/view), **Gym classes** & **Schedules** (read-only)

| # | Task | Check |
|---|------|-------|
| 2.1 | For each priority area: render with **`layouts.reception`** (via layout variable, `@extends`, or `/reception/*` wrapper views). | [x] |
| 2.2 | Replace **breadcrumb** / page titles with **short, desk-friendly** copy (avoid “Admin \| …” in `<title>` for reception). | [x] |
| 2.3 | Ensure **mobile width** doesn’t break tables (responsive wrappers, optional card layout for tiny screens). | [x] |

**Phase 2 implementation notes:** **`AppServiceProvider`** registers **`View::composer`** for desk view patterns (`pages.admin.users.*`, `memberships.*`, `membership_extension_requests.*`, `merchandise.*`, `class_bookings.*`, `packages.*`, `gym_classes.*`, `class_schedules.*`) setting **`$shellLayout`** to **`layouts.reception`** when `role === reception`, else **`pages.admin.inc.app`**. **`pages.admin.attendance.*`** uses **`layouts.reception`** vs **`layouts.auth`** (admin scan keeps minimal chrome). Desk blades use **`@extends($shellLayout ?? …)`** and **`$deskShellTitlePrefix`** in **`layouts.header`** titles (**Front desk** instead of **Admin** for reception). **DataTables** list pages wrap **`<table>`** in **`.table-responsive`**; POS checkout already had a responsive lines table. **`reception-home`** refactored to **`content-header`** + **`content`** to match the shell’s single **`content-wrapper`**. See also [`docs/reception-nav.md`](./reception-nav.md).

---

## Phase 3 — Cleanup & consistency

| # | Task | Check |
|---|------|-------|
| 3.1 | Remove or **gate** `layouts.navigation` **Scan ID** / duplicate chrome if reception no longer loads `pages.admin.inc.app`. | [ ] |
| 3.2 | **Reception user** should not need **AdminLTE sidebar** anywhere in normal work; **admin** users unchanged. | [ ] |
| 3.3 | Smoke test: full **shift script** (add member → membership → POS → booking) only using reception shell. | [ ] |

**Phase 3 implementation notes:** _(fill when done.)_

---

## Optional (later)

| Item | Note |
|------|------|
| **Deep links** | Bookmarkable `/reception/memberships/...` URLs for supervisors. |
| **Keyboard shortcuts** | Optional focus on search fields (if global search added). |
| **Offline / PWA** | Out of scope unless explicitly requested. |

---

## Dependency diagram

```
Chapter 3 (reception role + route split + desk home)
    ↓
Chapter 4 (reception shell + migrate desk UI off AdminLTE sidebar)
```

---

## What to do first (recommended)

1. **Phase 1.1–1.2** — One **reception layout** + **nav map** (small, testable).  
2. **Phase 2.1** — Migrate **one** critical flow end-to-end (e.g. **Members list**) to validate layout switching approach.  
3. Roll remaining pages in **Phase 2** priority order; **Phase 3** cleanup last.

---

*Update this document when a phase is completed or scope changes.*
