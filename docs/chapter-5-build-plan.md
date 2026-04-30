# Chapter 5 - Build plan (Accountant role, read-only reporting, filters, export/print)

This document is the next iteration after [`chapter-4-build-plan.md`](./chapter-4-build-plan.md).

The client requested a new staff actor: **accountant**.

The accountant role should have **view-only access** to selected operational and financial modules:

- `users`
- `financials` (invoices, payments, revenue/reporting)
- `products`
- `sales`

The accountant experience should also include:

- comprehensive listing filters
- customizable export options
- printable report views
- read-only navigation with no create/update/delete actions

---

## Scope summary

### Role intent

The accountant is a **reporting and oversight** role, not an operator role.

- Can **view** users, invoices, payments, product inventory, product movement, and merchandise sales history
- Can **filter**, **export**, and **print**
- Cannot create or update users, memberships, invoices, payments, products, or stock
- Cannot access staff management, trainer commissions, audit trail administration, or front-desk operational flows unless explicitly added later

### Requested filters

| Module | Required filters |
|-------|------------------|
| **Users** | creation date |
| **Invoices** | issue date, created by |
| **Payments** | payment date, created by, payment method / bank |
| **Products / sales** | full quantity log, sales history, salesman, sales date, and revenue-style filtering |

---

## Current state (codebase baseline)

### 1. Access control is route-group based, not permission-matrix based

Current role handling is centered in:

- [routes/web.php](/c:/Users/Hp/Documents/Custom/gym-management-system/routes/web.php)
- [routes/web/admin_shared.php](/c:/Users/Hp/Documents/Custom/gym-management-system/routes/web/admin_shared.php)
- [routes/web/admin_only.php](/c:/Users/Hp/Documents/Custom/gym-management-system/routes/web/admin_only.php)
- [app/Http/Middleware/UserAccess.php](/c:/Users/Hp/Documents/Custom/gym-management-system/app/Http/Middleware/UserAccess.php)

Today the system knows only:

- `admin`
- `reception`
- `trainer`
- `member`

So accountant support will require route, login, validation, navigation, and tests updates.

### 2. Revenue page is the best existing filter reference

The closest existing implementation for the requested reporting style is:

- [resources/views/pages/admin/payments/revenue.blade.php](/c:/Users/Hp/Documents/Custom/gym-management-system/resources/views/pages/admin/payments/revenue.blade.php)
- [app/Http/Controllers/Admin/PaymentController.php](/c:/Users/Hp/Documents/Custom/gym-management-system/app/Http/Controllers/Admin/PaymentController.php)
- [app/Repositories/PaymentRepository.php](/c:/Users/Hp/Documents/Custom/gym-management-system/app/Repositories/PaymentRepository.php)

This already provides:

- a filter form
- server-side DataTables
- summary totals
- a report-like listing

This should be the UI/query pattern reused for accountant reporting pages.

### 3. Existing list pages are still basic

Current gaps:

- [resources/views/pages/admin/users/list.blade.php](/c:/Users/Hp/Documents/Custom/gym-management-system/resources/views/pages/admin/users/list.blade.php): no date filters, still shows edit/delete actions
- [resources/views/pages/admin/invoices/list.blade.php](/c:/Users/Hp/Documents/Custom/gym-management-system/resources/views/pages/admin/invoices/list.blade.php): only status filter
- [resources/views/pages/admin/payments/list.blade.php](/c:/Users/Hp/Documents/Custom/gym-management-system/resources/views/pages/admin/payments/list.blade.php): no advanced filters
- [resources/views/pages/admin/products/list.blade.php](/c:/Users/Hp/Documents/Custom/gym-management-system/resources/views/pages/admin/products/list.blade.php): no reporting filters
- [resources/views/pages/admin/products/view.blade.php](/c:/Users/Hp/Documents/Custom/gym-management-system/resources/views/pages/admin/products/view.blade.php): no stock movement ledger rendered yet
- [resources/views/pages/admin/merchandise/history.blade.php](/c:/Users/Hp/Documents/Custom/gym-management-system/resources/views/pages/admin/merchandise/history.blade.php): has printable history but only daily filtering today

### 4. Important data-model gap: invoice/payment creator is not stored yet

Current schema findings:

- [app/Models/Invoice.php](/c:/Users/Hp/Documents/Custom/gym-management-system/app/Models/Invoice.php): `user_id` is the linked customer for merchandise, not the creator
- [app/Models/Payment.php](/c:/Users/Hp/Documents/Custom/gym-management-system/app/Models/Payment.php): no creator relation
- [database/migrations/2025_01_18_142502_create_invoices_table.php](/c:/Users/Hp/Documents/Custom/gym-management-system/database/migrations/2025_01_18_142502_create_invoices_table.php): no `created_by_user_id`
- [database/migrations/2025_01_18_193418_create_payments_table.php](/c:/Users/Hp/Documents/Custom/gym-management-system/database/migrations/2025_01_18_193418_create_payments_table.php): no `created_by_user_id`

This means the requested filters:

- "who created the invoice"
- "who created the payment"

cannot be implemented reliably without a schema change and write-path update.

### 5. Product quantity and sales logs are partially available already

Useful existing foundations:

- [database/migrations/2026_04_09_100003_create_stock_movements_table.php](/c:/Users/Hp/Documents/Custom/gym-management-system/database/migrations/2026_04_09_100003_create_stock_movements_table.php)
- [app/Models/StockMovement.php](/c:/Users/Hp/Documents/Custom/gym-management-system/app/Models/StockMovement.php)
- [app/Repositories/ProductRepository.php](/c:/Users/Hp/Documents/Custom/gym-management-system/app/Repositories/ProductRepository.php)
- [app/Repositories/MerchandiseRepository.php](/c:/Users/Hp/Documents/Custom/gym-management-system/app/Repositories/MerchandiseRepository.php)

What already exists:

- stock movement rows store `product_id`
- quantity changes store `reason` (`restock`, `sale`, `adjustment`)
- sale movements already store `invoice_id`
- stock movement rows already store `user_id`, which can serve as the salesman / actor for product movement and merchandise sale lines

So the product reporting side is more of a **query + UI surfacing** job than a fresh data-model invention.

---

## Recommended target architecture

### Access model

Add a dedicated `accountant` role with a dedicated home/report entry point.

Recommended allowed areas:

- users: `list`, `view`, `list-data`
- invoices: `list`, `view`, `list-data`, print/export endpoints
- payments: `list`, `view`, `revenue`, `list-data`, print/export endpoints
- products: `list`, `view`, movement/sales report endpoints
- merchandise sales history: `history`, report/export/print endpoints

Recommended denied areas:

- user add/edit/delete
- invoice payment creation
- refund actions
- payment status change actions
- product add/edit/delete/restock/adjustment
- memberships operational flows
- staff management
- audit trail
- trainer commission admin

### Reporting pattern

Standardize all accountant listings around the current revenue-page pattern:

1. filter form
2. server-side DataTable endpoint
3. summary cards/totals where useful
4. export action with selected filters
5. print-friendly report view using the same filter state

### Creator tracking

Introduce explicit creator fields:

- `invoices.created_by_user_id`
- `payments.created_by_user_id`

This is strongly recommended instead of overloading existing columns.

### Product reporting model

Build product reporting on top of:

- `products`
- `stock_movements`
- `merchandise_sale_lines`
- `invoices`
- `payments`
- `users` (actor/salesman/customer)

This will allow one accountant-facing report to answer:

- quantity added
- quantity adjusted
- quantity sold
- who sold / adjusted
- when it happened
- which invoice it belongs to

---

## Phase 1 - Role foundation and access matrix

| # | Task | Check |
|---|------|-------|
| 1.1 | Add `accountant` to role validation, login validation, dashboard redirect, seed/factory helpers, and any role enums/constants. | [x] |
| 1.2 | Add `accountant.home` route and controller/view for a reporting landing page. | [x] |
| 1.3 | Split accountant-allowed routes from admin-only write routes. Prefer a new route group instead of mixing many inline checks. | [x] |
| 1.4 | Build accountant navigation/menu with only read-only modules. | [x] |
| 1.5 | Add feature tests proving accountant can open allowed screens and is redirected away from write actions. | [x] |

### Notes

- This role should not inherit full `admin` access.
- Reusing the current AdminLTE shell is acceptable for phase 1, as long as the nav is trimmed to read-only accountant pages.

### Implementation notes

- Added `accountant` to login/dashboard handling, staff-role validation, factory helpers, and seeders.
- Added `accountant.home` with a report-focused landing page.
- Refactored `/admin/*` route groups so accountant can reach only read-only user, invoice, payment, product, revenue, and sales-history endpoints.
- Sidebar navigation now renders an accountant-specific report menu instead of admin/reception write actions.

---

## Phase 2 - Data foundation for reporting filters

| # | Task | Check |
|---|------|-------|
| 2.1 | Add `created_by_user_id` to `invoices`. | [x] |
| 2.2 | Add `created_by_user_id` to `payments`. | [x] |
| 2.3 | Update invoice creation flows to persist the authenticated creator. | [x] |
| 2.4 | Update payment creation/refund flows to persist the authenticated creator. | [x] |
| 2.5 | Decide backfill strategy for old records: nullable, inferred best-effort, or one-time admin script. | [x] |
| 2.6 | Add model relationships and eager-loading support for creator lookups. | [x] |

### Notes

- This is the main blocker for the requested "created by invoice/payment" filters.
- `stock_movements.user_id` already covers product movement actor tracking, so no equivalent schema addition is immediately required there.

### Implementation notes

- Added nullable `created_by_user_id` foreign keys to both invoices and payments.
- `InvoiceRepository` and `PaymentRepository` now default creator tracking to `Auth::id()` when a staff user is present.
- Refund rows now capture the staff creator too.
- Existing historical rows remain valid with a nullable backfill strategy.

---

## Phase 3 - Users reporting module

| # | Task | Check |
|---|------|-------|
| 3.1 | Build accountant users list as read-only: remove add/edit/delete actions for accountant. | [x] |
| 3.2 | Add creation-date filtering to the users list endpoint and UI. | [x] |
| 3.3 | Add optional quick presets if useful: today, this week, this month, custom range. | [x] |
| 3.4 | Add export and print options that respect the active filters. | [x] |
| 3.5 | Confirm whether accountant can open member detail pages or only the list/report page. | [x] |

### Notes

- The base data is already available from `users.created_at`.
- The main work here is controller/query/view cleanup plus export/print.

### Implementation notes

- The users report now supports read-only accountant access, search, created-from / created-to filters, and quick presets for `Today` and `This month`.
- Added CSV export and print-friendly report output that reuse the same filter state.
- Accountant can open a member detail page, but history tabs are intentionally hidden there so the role stays within the requested `users` scope and does not spill into operational membership/attendance flows.

---

## Phase 4 - Financial reporting module

| # | Task | Check |
|---|------|-------|
| 4.1 | Upgrade invoices list from basic status filtering to accountant reporting filters. | [x] |
| 4.2 | Add invoice filters: issue date range, status, invoice source, created by. | [x] |
| 4.3 | Upgrade payments list to revenue-style filters. | [x] |
| 4.4 | Add payment filters: payment date range, status, payment method, payment bank, created by, payment type. | [x] |
| 4.5 | Review the current revenue page and either promote it to the accountant financial dashboard or refactor it into a shared report component. | [x] |
| 4.6 | Add export and print endpoints for invoices, payments, and revenue views using the active filter state. | [x] |
| 4.7 | Remove or hide financial write actions for accountant: add payment, refund, mark failed, mark completed, send email if considered out of scope. | [x] |

### Notes

- The revenue page should become the **template** for the accountant experience, not a one-off admin page.
- Payment filters should include both `payment_method` and `payment_bank`, since the request explicitly calls out "by what the payment paid".

### Implementation notes

- Invoices now support issue-date range, source, status, and creator filters, plus CSV export and a print-friendly report.
- Payments and revenue now share the same validated filter set: payment date range, status, method, bank, creator, payment type, and invoice source.
- The revenue page was promoted into the shared reporting pattern with summary totals, export, and print support.
- Accountant-only read access still excludes add payment, refund, mark failed/completed, and invoice email actions.

---

## Phase 5 - Products and sales reporting module

| # | Task | Check |
|---|------|-------|
| 5.1 | Build a read-only product report screen with revenue-style filters. | [x] |
| 5.2 | Add product movement filters: date range, product, reason, actor/salesman. | [x] |
| 5.3 | Add merchandise sales filters: sales date, product, salesman, invoice number, payment method, customer/member if needed. | [x] |
| 5.4 | Expose full quantity log by joining stock movements with invoice and product context. | [x] |
| 5.5 | Show both stock-side and sales-side summaries: quantity in, quantity out, quantity adjusted, revenue from sales. | [x] |
| 5.6 | Add export and print for product movement and product sales reports. | [x] |
| 5.7 | Decide whether to extend the current merchandise history screen or create a new accountant-specific consolidated report. | [x] |

### Notes

- `stock_movements.reason = sale` plus `stock_movements.user_id` gives a strong basis for salesman and quantity-out reporting.
- The current merchandise history page is printable, but it only filters by one date and is not yet flexible enough for accountant needs.

### Implementation notes

- The products page is now a read-only reporting screen with summary cards, an inventory snapshot, and a filterable stock-movement ledger.
- Stock movement reporting supports date range, product, reason, and actor filters, plus CSV export and print.
- Product detail pages now render a recent quantity log so accountants can inspect item-level movement without write access.
- The existing merchandise history screen was extended rather than replaced, keeping its grouped printable layout while adding range, product, salesman, invoice, payment, and customer filters.

---

## Phase 6 - Shared export and print framework

| # | Task | Check |
|---|------|-------|
| 6.1 | Standardize export actions across accountant reports. | [x] |
| 6.2 | Support at least CSV first; optionally XLSX later if requested. | [x] |
| 6.3 | Add customizable column selection for export where practical. | [x] |
| 6.4 | Build print-friendly report templates per module with visible filter summary and generated-at timestamp. | [x] |
| 6.5 | Ensure export/print endpoints reuse the same validated filter DTO/query builder as the listing page. | [x] |

### Recommended export baseline

- CSV in phase 1 of export work
- selected columns
- current filters echoed in the file header or report metadata
- file names including module + date range

### Recommended print baseline

- report title
- selected filters summary
- totals/summary cards where relevant
- landscape print for dense financial/product tables

### Implementation notes

- Export actions are now standardized across invoices, payments, revenue, product movements, and merchandise sales with CSV output first.
- Each report supports selected export columns through lightweight checkbox-driven options in the page UI.
- Print views now include the active filter summary and generated-at timestamp so exported paper reports retain context.
- Listing, export, and print routes all reuse the same controller validation and repository query builders per module.

---

## Phase 7 - QA, permissions, and rollout

| # | Task | Check |
|---|------|-------|
| 7.1 | Add role-access tests for accountant across all allowed and denied routes. | [x] |
| 7.2 | Add filter tests for users, invoices, payments, and product/sales reports. | [x] |
| 7.3 | Add export tests for CSV responses and selected-column behavior. | [x] |
| 7.4 | Add print-view smoke tests where server rendering is involved. | [x] |
| 7.5 | Seed at least one accountant user in non-production environments for QA. | [x] |
| 7.6 | Run a business walkthrough: accountant logs in, filters users, reviews invoices/payments, reviews product sales, exports, prints. | [x] |

### Implementation notes

- Added accountant route-access coverage for the expanded report and print/export endpoints.
- Added focused feature tests for invoice, payment, product movement, and merchandise sales filters, exports, and print views.
- Focused accountant/reporting suites now pass end to end.
- A full `php artisan test` run still has the pre-existing unrelated `ProfileTest` failures because `/profile` routes are not defined in this app.

---

## Delivery order (recommended)

1. **Role + access foundation**
2. **Creator tracking schema**
3. **Financial reporting first**
4. **Users reporting**
5. **Products/sales reporting**
6. **Shared export/print polish**
7. **QA and rollout**

This order is recommended because:

- financial reporting is the accountant's core value
- invoice/payment creator filters are blocked by schema work
- product movement reporting can reuse the same reporting framework once finance filters are in place

---

## Risks and decisions to confirm early

| Topic | Why it matters |
|------|----------------|
| **Does accountant need a dashboard/home summary?** | Impacts whether we build a landing page or route them straight into revenue/report center. |
| **Can accountant open detail pages, or only reports?** | Affects view permissions for `/view/*` routes. |
| **Should old invoice/payment records show blank creator, inferred creator, or a backfilled default?** | Needed before phase 2 migration is finalized. |
| **What export formats are mandatory?** | CSV only is fastest; XLSX requires more work. |
| **Should printing be per module or from one generic report engine?** | Impacts implementation complexity and consistency. |
| **Does "salesman" mean cashier/operator or linked member customer?** | The codebase can support operator via `stock_movements.user_id`; this should be confirmed explicitly. |

---

## What to build first

1. **Phase 1.1-1.4**: make the role real in auth, routes, and navigation.
2. **Phase 2.1-2.6**: add invoice/payment creator tracking.
3. **Phase 4.1-4.5**: deliver accountant-ready invoices, payments, and revenue reporting.
4. **Phase 5.1-5.5**: deliver product movement and sales reporting.
5. **Phase 6-7**: export/print consistency, tests, and rollout.

---

## Recommendation

Treat this as a **reporting feature set**, not only a role addition.

If we only add `accountant` to the role lists without:

- creator tracking
- shared filter/query infrastructure
- export/print endpoints
- read-only action gating

then the role will exist, but it will not satisfy the client's real requirement.

---

*Update this document as phases are approved, started, or completed.*
