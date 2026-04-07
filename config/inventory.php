<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Low-stock email notifications
    |--------------------------------------------------------------------------
    |
    | When enabled, the scheduled command emails this address with products at or
    | below their low_stock_threshold. Leave null to use mail.from.address.
    |
    */
    'low_stock_notify_enabled' => env('INVENTORY_LOW_STOCK_NOTIFY_ENABLED', true),

    'low_stock_mail_to' => env('INVENTORY_LOW_STOCK_MAIL_TO'),

];
