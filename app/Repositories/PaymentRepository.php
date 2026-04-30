<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentRepository extends BaseRepository
{
    public function __construct(
        protected InvoiceRepository $invoiceRepository
    ) {
    }

    /**
     * Store a new payment in the database.
     *
     * @param array $attributes
     * @return Payment
     */
    public function store(array $attributes): Payment
    {
        return DB::transaction(function () use ($attributes) {
            $validatedAttributes = [
                'invoice_id' => $attributes['invoice_id'] ?? null,
                'membership_id' => $attributes['membership_id'] ?? null,
                'created_by_user_id' => $attributes['created_by_user_id'] ?? Auth::id(),
                'amount' => $attributes['amount'] ?? null,
                'payment_date' => Carbon::now(),
                'payment_method' => $attributes['payment_method'] ?? null,
                'payment_bank' => $attributes['payment_bank'] ?? null,
                'bank_transaction_number' => $attributes['bank_transaction_number'] ?? null,
                'status' => $attributes['status'] ?? 'pending',
                'payment_type' => $attributes['payment_type'] ?? 'payment',
                'notes' => $attributes['notes'] ?? null,
            ];

            if (! isset($validatedAttributes['invoice_id'], $validatedAttributes['amount'], $validatedAttributes['payment_date'], $validatedAttributes['payment_method'], $validatedAttributes['status'])) {
                throw new \Exception('Missing required attributes.');
            }

            /** @var Invoice $invoice */
            $invoice = Invoice::query()->findOrFail($validatedAttributes['invoice_id']);
            if ($validatedAttributes['membership_id'] === null || $validatedAttributes['membership_id'] === '') {
                $validatedAttributes['membership_id'] = $invoice->membership_id;
            }

            $payment = Payment::create($validatedAttributes);
            $this->invoiceRepository->syncInvoiceStatusFromPayments($payment->invoice);

            return $payment;
        });
    }

    /**
     * Record a completed refund as a negative payment row.
     *
     * @param array $attributes Expects invoice_id, amount (positive), payment_method, optional payment_bank, bank_transaction_number, notes
     */
    public function recordRefund(array $attributes): Payment
    {
        return DB::transaction(function () use ($attributes) {
            /** @var Invoice $invoice */
            $invoice = Invoice::query()->findOrFail($attributes['invoice_id']);
            $refundAmount = (float) $attributes['amount'];
            $netPaid = (float) $invoice->payments()->where('status', 'completed')->sum('amount');

            if ($refundAmount <= 0 || $refundAmount > $netPaid) {
                throw new \Exception(__('Refund amount must be between :min and :max.', [
                    'min' => '0.01',
                    'max' => number_format($netPaid, 2),
                ]));
            }

            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'membership_id' => $invoice->membership_id,
                'created_by_user_id' => $attributes['created_by_user_id'] ?? Auth::id(),
                'amount' => -$refundAmount,
                'payment_date' => Carbon::now(),
                'payment_method' => $attributes['payment_method'],
                'payment_bank' => $attributes['payment_bank'] ?? null,
                'bank_transaction_number' => $attributes['bank_transaction_number'] ?? null,
                'status' => 'completed',
                'payment_type' => 'refund',
                'notes' => $attributes['notes'] ?? null,
            ]);

            $this->invoiceRepository->syncInvoiceStatusFromPayments($invoice);

            return $payment;
        });
    }

    /**
     * Update an existing payment in the database.
     *
     * @param mixed $model
     * @param array $attributes
     * @return bool
     */
    public function update(mixed $model, array $attributes): bool
    {
        return false;
    }

    /**
     * Mark the status of a payment as completed.
     *
     * @param mixed $model
     * @return mixed
     */
    public function makeAsComplete(mixed $model): mixed
    {
        try {
            if ($model->status !== 'pending') {
                throw new \Exception('Only payments with a pending status can be updated to completed.');
            }

            if ($model->payment_type === 'refund') {
                throw new \Exception('Refund rows cannot be completed from this action.');
            }

            $invoice = $model->invoice;
            $totalCompletedPayments = $invoice->payments()
                ->where('status', 'completed')
                ->sum('amount');

            if (($totalCompletedPayments + $model->amount) > $invoice->amount) {
                throw new \Exception('Marking this payment as completed will exceed the total invoice amount.');
            }

            $model->update(['status' => 'completed']);
            $this->invoiceRepository->syncInvoiceStatusFromPayments($invoice);

            return $model;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Mark the status of a payment as failed.
     *
     * @param mixed $model
     * @return mixed
     */
    public function makeAsFailed(mixed $model): mixed
    {
        try {
            if ($model->status !== 'pending') {
                throw new \Exception("Only payments with a pending status can be updated to failed.");
            }

            $model->update(['status' => 'failed']);

            return $model;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get filtered payments query for accountant/admin reporting.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getFilteredPaymentsQuery(array $filters): Builder
    {
        return Payment::query()
            ->with(['invoice.customer', 'invoice.createdBy', 'membership.user', 'createdBy'])
            ->when(! empty($filters['start_date']), function (Builder $query) use ($filters) {
                $query->where('payment_date', '>=', $this->reportDateStart((string) $filters['start_date']));
            })
            ->when(! empty($filters['end_date']), function (Builder $query) use ($filters) {
                $query->where('payment_date', '<=', $this->reportDateEnd((string) $filters['end_date']));
            })
            ->when(! empty($filters['payment_method']), function (Builder $query) use ($filters) {
                $query->where('payment_method', $filters['payment_method']);
            })
            ->when(! empty($filters['payment_bank']), function (Builder $query) use ($filters) {
                $query->where('payment_bank', $filters['payment_bank']);
            })
            ->when(! empty($filters['status']), function (Builder $query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when(! empty($filters['payment_type']), function (Builder $query) use ($filters) {
                $query->where('payment_type', $filters['payment_type']);
            })
            ->when(! empty($filters['created_by_user_id']), function (Builder $query) use ($filters) {
                $query->where('created_by_user_id', (int) $filters['created_by_user_id']);
            })
            ->when(! empty($filters['invoice_source']), function (Builder $query) use ($filters) {
                $query->whereHas('invoice', function (Builder $invoiceQuery) use ($filters) {
                    $invoiceQuery->where('invoice_source', $filters['invoice_source']);
                });
            });
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
