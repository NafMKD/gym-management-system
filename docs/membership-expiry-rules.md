# Membership expiry rules

These rules align the **attendance scan** (`AttendanceController`), the **daily command** `memberships:process-expiry`, and **reminder email** `memberships:notify-expiring`.

## Calendar end date (`end_date`)

- A membership is **valid on its `end_date` day** (inclusive).
- It becomes **invalid the next calendar day** (first moment after that day).
- **Attendance:** a scan is rejected when **today’s date** is **after** `end_date` (string compare on `Y-m-d`).
- **Batch expiry:** `memberships:process-expiry` sets `status` to `inactive` when `end_date` **&lt; today** (so the morning after the last valid day).

## Visit allowance (`remaining_days`)

- Each successful check-in **decrements** `remaining_days` by 1.
- When `remaining_days` reaches **0**, status is set to **`inactive`** immediately (visit quota used up), even if `end_date` is still in the future.
- **Batch expiry** also sets `inactive` when `remaining_days &lt;= 0` while status is still `active` (safety net for inconsistent data).

## Reminder email

- Config: `config/membership.php` → `notify_days_before_end` (env `MEMBERSHIP_EXPIRING_NOTIFY_DAYS`, default **7**).
- Command `memberships:notify-expiring` selects **active** memberships whose `end_date` is **exactly** that many days **after** today, and sends one email per membership.

## Scheduler

- Registered in `bootstrap/app.php`: `memberships:process-expiry` daily at **00:05**, `memberships:notify-expiring` daily at **08:00** (app timezone).
