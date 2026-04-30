<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductRepository extends BaseRepository
{
    /**
     * Store a new product.
     *
     * @param array<string, mixed> $attributes
     */
    public function store(array $attributes): mixed
    {
        return DB::transaction(function () use ($attributes) {
            $validatedAttributes = [
                'name' => $attributes['name'] ?? null,
                'sku' => $attributes['sku'] ?? null,
                'description' => $attributes['description'] ?? null,
                'unit_price' => $attributes['unit_price'] ?? null,
                'stock_quantity' => isset($attributes['stock_quantity']) ? (int) $attributes['stock_quantity'] : 0,
                'low_stock_threshold' => isset($attributes['low_stock_threshold']) ? (int) $attributes['low_stock_threshold'] : 5,
                'is_active' => array_key_exists('is_active', $attributes) ? (bool) $attributes['is_active'] : true,
            ];

            if (! isset($validatedAttributes['name'], $validatedAttributes['unit_price'])) {
                throw new \Exception('Missing required attributes.');
            }

            return Product::create($validatedAttributes);
        });
    }

    /**
     * Update a product.
     *
     * @param array<string, mixed> $attributes
     */
    public function update(mixed $model, array $attributes): mixed
    {
        return DB::transaction(function () use ($model, $attributes) {
            /** @var Product $model */
            $originalAttributes = $model->only([
                'name', 'sku', 'description', 'unit_price', 'stock_quantity', 'low_stock_threshold', 'is_active',
            ]);

            $updateData = array_filter(
                $attributes,
                fn ($value, $key) => array_key_exists($key, $originalAttributes) && $value != $originalAttributes[$key],
                ARRAY_FILTER_USE_BOTH
            );

            if (empty($updateData)) {
                throw new \App\Exceptions\NoUpdateNeededException;
            }

            $model->update($updateData);

            return $model;
        });
    }

    /**
     * Apply a stock change (restock or adjustment) and record a movement row.
     *
     * @param  'adjustment'|'restock'  $reason
     */
    public function applyStockChange(Product $product, int $quantityChange, string $reason, ?string $notes = null): void
    {
        DB::transaction(function () use ($product, $quantityChange, $reason, $notes) {
            if ($reason === 'restock' && $quantityChange <= 0) {
                throw new \Exception(__('Restock quantity must be at least 1.'));
            }
            if ($reason === 'adjustment' && $quantityChange === 0) {
                throw new \Exception(__('Adjustment cannot be zero.'));
            }

            $product->refresh();
            $locked = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();
            $newQty = $locked->stock_quantity + $quantityChange;
            if ($newQty < 0) {
                throw new \Exception(__('Insufficient stock for this adjustment.'));
            }

            $locked->update(['stock_quantity' => $newQty]);

            StockMovement::create([
                'product_id' => $locked->id,
                'quantity_change' => $quantityChange,
                'reason' => $reason,
                'invoice_id' => null,
                'user_id' => Auth::id(),
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Product snapshot query for the reporting page.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getFilteredProductsQuery(array $filters): Builder
    {
        return Product::query()
            ->when(! empty($filters['product_id']), function (Builder $query) use ($filters) {
                $query->whereKey((int) $filters['product_id']);
            });
    }

    /**
     * Stock movement query for accountant/admin reporting.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getFilteredStockMovementsQuery(array $filters): Builder
    {
        return StockMovement::query()
            ->with(['product', 'user', 'invoice.customer'])
            ->when(! empty($filters['start_date']), function (Builder $query) use ($filters) {
                $query->where('created_at', '>=', $this->reportDateStart((string) $filters['start_date']));
            })
            ->when(! empty($filters['end_date']), function (Builder $query) use ($filters) {
                $query->where('created_at', '<=', $this->reportDateEnd((string) $filters['end_date']));
            })
            ->when(! empty($filters['product_id']), function (Builder $query) use ($filters) {
                $query->where('product_id', (int) $filters['product_id']);
            })
            ->when(! empty($filters['reason']), function (Builder $query) use ($filters) {
                $query->where('reason', $filters['reason']);
            })
            ->when(! empty($filters['actor_id']), function (Builder $query) use ($filters) {
                $query->where('user_id', (int) $filters['actor_id']);
            });
    }

    /**
     * Aggregated product movement metrics for the filtered report.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, int|float>
     */
    public function getMovementSummary(array $filters): array
    {
        $movementQuery = $this->getFilteredStockMovementsQuery($filters);
        $productQuery = $this->getFilteredProductsQuery($filters);

        return [
            'product_count' => (int) (clone $productQuery)->count(),
            'stock_on_hand' => (int) (clone $productQuery)->sum('stock_quantity'),
            'low_stock_count' => (int) (clone $productQuery)->lowStock()->count(),
            'quantity_in' => (int) (clone $movementQuery)->where('quantity_change', '>', 0)->sum('quantity_change'),
            'quantity_out' => abs((int) (clone $movementQuery)->where('quantity_change', '<', 0)->sum('quantity_change')),
            'restock_quantity' => (int) (clone $movementQuery)->where('reason', 'restock')->sum('quantity_change'),
            'sale_quantity' => abs((int) (clone $movementQuery)->where('reason', 'sale')->sum('quantity_change')),
            'adjustment_net' => (int) (clone $movementQuery)->where('reason', 'adjustment')->sum('quantity_change'),
        ];
    }

    private function reportDateStart(string $date): Carbon
    {
        return Carbon::parse($date, 'Africa/Addis_Ababa')->startOfDay()->utc();
    }

    private function reportDateEnd(string $date): Carbon
    {
        return Carbon::parse($date, 'Africa/Addis_Ababa')->endOfDay()->utc();
    }
}
