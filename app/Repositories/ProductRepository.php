<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\StockMovement;
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
}
