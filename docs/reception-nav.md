# Reception shell — information architecture (navigation)

Reception users use **`layouts.reception`** (`config/reception.php` + `layouts/partials/reception-topnav.blade.php`). URLs stay under existing **`/admin/*`** shared routes (`user-access:admin,reception`) except the desk entry point.

## Entry

| Label (UI) | Route name | Path (typical) |
|------------|------------|----------------|
| **Front desk** (brand) | `reception.home` | `GET /reception/home` |

## Top navigation

| Label | Route name | Notes |
|-------|------------|--------|
| Desk home | `reception.home` | Today’s bookings + quick actions |
| Members | `admin.users.list` | User list; add/view/edit via `admin.users.*` |
| Memberships | `admin.memberships.list` | `admin.memberships.*`, extension requests |
| POS | `admin.merchandise.checkout` | Merchandise checkout (`admin.merchandise.*`) |
| Bookings | `admin.class_bookings.list` | Class bookings |
| Packages | `admin.packages.list` | Read-only list/view (`admin.packages.*`) |
| Classes | `admin.gym_classes.list` | Gym classes reference |
| Schedule | `admin.class_schedules.list` | Class schedules |
| Attendance | `admin.attendance.scan` | Scan / record attendance |

## Explicitly excluded from this nav

Invoice, payment, revenue, staff, inventory admin, and other **`user-access:admin`**-only routes (see `routes/web/admin_only.php`). Reception is redirected away if they hit those URLs.

## Phase 2+

Additional desk pages should **`@extends('layouts.reception')`** (or a shared variable) so list/detail views match the shell; see `chapter-4-build-plan.md` Phase 2.
