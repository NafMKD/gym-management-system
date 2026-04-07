<?php

namespace App\Repositories;

use App\Models\MerchandiseSaleLine;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MerchandiseRepository
{
    public function __construct(
        protected InvoiceRepository $invoiceRepository,
        protected PaymentRepository $paymentRepository
    ) {
    }

    /**
     * Sell merchandise: invoice, sale lines, stock movements, completed payment.
     *
     * @param  array{user_id:int,lines:array<int,array{product_id:int,quantity:int}>,payment_method:string,payment_bank?:string|null,bank_transaction_number?:string|null,notes?:string|null}  $attributes
     */
    public function checkout(array $attributes): Invoice
    {
        return DB::transaction(function () use ($attributes) {
            $lines = $attributes['lines'] ?? [];
            if (! is_array($lines) || count($lines) < 1) {
                throw new \Exception(__('Add at least one product line.'));
            }

            $normalized = [];
            foreach ($lines as $line) {
                $pid = (int) ($line['product_id'] ?? 0);
                $qty = (int) ($line['quantity'] ?? 0);
                if ($pid < 1 || $qty < 1) {
                    continue;
                }
                if (! isset($normalized[$pid])) {
                    $normalized[$pid] = 0;
                }
                $normalized[$pid] += $qty;
            }

            if (count($normalized) < 1) {
                throw new \Exception(__('Add at least one valid product line.'));
            }

            ksort($normalized);

            $prepared = [];
            $total = 0.0;

            foreach ($normalized as $productId => $qty) {
                $product = Product::query()->whereKey($productId)->lockForUpdate()->first();
                if (! $product || ! $product->is_active) {
                    throw new \Exception(__('Product :id is not available.', ['id' => $productId]));
                }
                if ($product->stock_quantity < $qty) {
                    throw new \Exception(__('Not enough stock for :name (available :avail).', [
                        'name' => $product->name,
                        'avail' => $product->stock_quantity,
                    ]));
                }

                $unit = (float) $product->unit_price;
                $lineTotal = round($unit * $qty, 2);
                $total += $lineTotal;

                $prepared[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'unit_price' => $unit,
                    'line_total' => $lineTotal,
                ];
            }

            $total = round($total, 2);
            if ($total <= 0) {
                throw new \Exception(__('Invoice total must be greater than zero.'));
            }

            /** @var Invoice $invoice */
            $invoice = $this->invoiceRepository->store([
                'user_id' => (int) $attributes['user_id'],
                'amount' => $total,
                'invoice_source' => 'merchandise',
            ]);

            foreach ($prepared as $row) {
                /** @var Product $product */
                $product = $row['product'];
                $qty = $row['quantity'];

                MerchandiseSaleLine::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $row['unit_price'],
                    'line_total' => $row['line_total'],
                ]);

                $product->refresh();
                $product->decrement('stock_quantity', $qty);

                StockMovement::create([
                    'product_id' => $product->id,
                    'quantity_change' => -$qty,
                    'reason' => 'sale',
                    'invoice_id' => $invoice->id,
                    'user_id' => Auth::id(),
                    'notes' => null,
                ]);
            }

            $paymentPayload = [
                'invoice_id' => $invoice->id,
                'amount' => $total,
                'payment_method' => $attributes['payment_method'],
                'payment_bank' => $attributes['payment_bank'] ?? null,
                'bank_transaction_number' => $attributes['bank_transaction_number'] ?? null,
                'status' => 'completed',
                'notes' => $attributes['notes'] ?? null,
            ];

            $this->paymentRepository->store($paymentPayload);

            return $invoice->fresh(['merchandiseSaleLines.product', 'customer', 'payments']);
        });
    }
}
