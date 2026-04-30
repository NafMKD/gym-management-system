<?php

namespace App\Repositories;

use App\Models\MerchandiseSaleLine;
use App\Models\StockMovement;
use App\Models\Invoice;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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
     * @param  array{user_id?:int|null,lines:array<int,array{product_id:int,quantity:int}>,payment_method:string,payment_bank?:string|null,bank_transaction_number?:string|null,notes?:string|null}  $attributes
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

            $userId = $attributes['user_id'] ?? null;
            if ($userId === '' || $userId === false) {
                $userId = null;
            } elseif ($userId !== null) {
                $userId = (int) $userId;
            }

            /** @var Invoice $invoice */
            $invoice = $this->invoiceRepository->store([
                'user_id' => $userId,
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

    /**
     * Filtered merchandise sale lines with invoice and payment context.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getFilteredSalesLinesQuery(array $filters): Builder
    {
        return MerchandiseSaleLine::query()
            ->with([
                'product',
                'invoice' => function ($query) {
                    $query->with([
                        'customer',
                        'createdBy',
                        'payments' => fn ($paymentQuery) => $paymentQuery->orderBy('payment_date')->orderBy('id'),
                    ]);
                },
            ])
            ->whereHas('invoice', function (Builder $query) use ($filters) {
                $query->where('invoice_source', 'merchandise')
                    ->when(! empty($filters['start_date']), function (Builder $invoiceQuery) use ($filters) {
                        $invoiceQuery->where('issued_date', '>=', $this->reportDateStart((string) $filters['start_date']));
                    })
                    ->when(! empty($filters['end_date']), function (Builder $invoiceQuery) use ($filters) {
                        $invoiceQuery->where('issued_date', '<=', $this->reportDateEnd((string) $filters['end_date']));
                    })
                    ->when(! empty($filters['salesman_id']), function (Builder $invoiceQuery) use ($filters) {
                        $invoiceQuery->where('created_by_user_id', (int) $filters['salesman_id']);
                    })
                    ->when(! empty($filters['invoice_number']), function (Builder $invoiceQuery) use ($filters) {
                        $invoiceQuery->where('invoice_number', 'like', '%'.$this->escapeLike((string) $filters['invoice_number']).'%');
                    })
                    ->when(! empty($filters['customer_id']), function (Builder $invoiceQuery) use ($filters) {
                        $invoiceQuery->where('user_id', (int) $filters['customer_id']);
                    })
                    ->when(! empty($filters['payment_method']) || ! empty($filters['payment_bank']), function (Builder $invoiceQuery) use ($filters) {
                        $invoiceQuery->whereHas('payments', function (Builder $paymentQuery) use ($filters) {
                            $paymentQuery
                                ->where('status', 'completed')
                                ->when(! empty($filters['payment_method']), function (Builder $filteredPaymentQuery) use ($filters) {
                                    $filteredPaymentQuery->where('payment_method', $filters['payment_method']);
                                })
                                ->when(! empty($filters['payment_bank']), function (Builder $filteredPaymentQuery) use ($filters) {
                                    $filteredPaymentQuery->where('payment_bank', $filters['payment_bank']);
                                });
                        });
                    });
            })
            ->when(! empty($filters['product_id']), function (Builder $query) use ($filters) {
                $query->where('product_id', (int) $filters['product_id']);
            });
    }

    /**
     * Aggregated sales figures for the filtered merchandise report.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, int|float>
     */
    public function getSalesSummary(array $filters): array
    {
        $query = $this->getFilteredSalesLinesQuery($filters);

        return [
            'invoice_count' => (int) (clone $query)->distinct('invoice_id')->count('invoice_id'),
            'line_count' => (int) (clone $query)->count(),
            'item_quantity' => (int) (clone $query)->sum('quantity'),
            'gross_total' => (float) (clone $query)->sum('line_total'),
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

    private function escapeLike(string $value): string
    {
        return addcslashes($value, '\%_');
    }
}
