<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Expiry reminder lead time (days)
    |--------------------------------------------------------------------------
    |
    | `memberships:notify-expiring` selects active memberships whose end_date is
    | exactly this many days after today, and emails the member.
    |
    */

    'notify_days_before_end' => (int) env('MEMBERSHIP_EXPIRING_NOTIFY_DAYS', 7),

];
