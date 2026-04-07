<?php

namespace App\Console\Commands;

use App\Mail\LowStockProductsMail;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotifyLowStockProductsCommand extends Command
{
    protected $signature = 'inventory:notify-low-stock';

    protected $description = 'Email a low-stock summary for active products at or below threshold (see config/inventory.php).';

    public function handle(): int
    {
        if (! config('inventory.low_stock_notify_enabled', true)) {
            $this->info('Low-stock notifications are disabled (config).');

            return self::SUCCESS;
        }

        $to = config('inventory.low_stock_mail_to') ?: config('mail.from.address');
        if (! $to) {
            $this->warn('No recipient configured (INVENTORY_LOW_STOCK_MAIL_TO or mail.from.address). Skipping.');

            return self::SUCCESS;
        }

        $products = Product::query()
            ->where('is_active', true)
            ->lowStock()
            ->orderBy('name')
            ->get();

        if ($products->isEmpty()) {
            $this->info('No low-stock products.');

            return self::SUCCESS;
        }

        try {
            Mail::to($to)->send(new LowStockProductsMail($products));
            $this->info('Sent low-stock notification for '.$products->count().' product(s).');
        } catch (\Throwable $e) {
            $this->error('Failed to send: '.$e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
