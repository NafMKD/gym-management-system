# Reception shell — information architecture (navigation)

Reception users use **`layouts.reception`** (`config/reception.php` + `layouts/partials/reception-topnav.blade.php`). URLs stay under existing **`/admin/*`** shared routes (`user-access:admin,reception`) except the desk entry point.

## Entry

| Label (UI) | Route name | Path (typical) |
|------------|------------|----------------|
| **Front desk** (brand) | `reception.home` | `GET /reception/home` |

## Top navigation

Primary bar: **Home** (`reception.home`) + **Desk** dropdown (`config/reception.php`). Smaller type, no icons; full list lives under **Desk**.

| Label | Route name | Notes |
|-------|------------|--------|
| Home | `reception.home` | Today’s bookings + quick actions |
| Members | `admin.users.list` | Under **Desk** · `admin.users.*` |
| Memberships | `admin.memberships.list` | Under **Desk** · `admin.memberships.*`, extension requests |
| POS | `admin.merchandise.checkout` | Under **Desk** · `admin.merchandise.*` |
| Bookings | `admin.class_bookings.list` | Under **Desk** |
| Packages | `admin.packages.list` | Under **Desk** · read-only list/view (`admin.packages.*`) |
| Classes | `admin.gym_classes.list` | Under **Desk** |
| Schedule | `admin.class_schedules.list` | Under **Desk** |
| Attendance | `admin.attendance.scan` | Under **Desk** · `admin.attendance.*` |

## Explicitly excluded from this nav

Invoice, payment, revenue, staff, inventory admin, and other **`user-access:admin`**-only routes (see `routes/web/admin_only.php`). Reception is redirected away if they hit those URLs.

## Phase 2 (implemented)

Shared **`/admin/*`** desk views use a **view composer** (`AppServiceProvider`) to set **`$shellLayout`** and **`$deskShellTitlePrefix`**: reception users get **`layouts.reception`** and **Front desk** in the HTML `<title>`; admins keep **`pages.admin.inc.app`**. **`pages.admin.attendance.scan`** uses **`layouts.reception`** for reception and **`layouts.auth`** for admin.

## Later

Further pages can opt into the same composer patterns; see `chapter-4-build-plan.md` Phase 3.
