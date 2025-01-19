<?php

namespace App\Repositories;

use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentRepository extends BaseRepository {

    /**
     * Store a new payment in the database.
     *
     * @param array $attributes
     * @return bool
     */
    public function store(array $attributes): mixed
    {
        try {
            DB::transaction(callback: function () use ($attributes) {
                $validatedAttributes = [
                    'invoice_id' => $attributes['invoice_id'] ?? null,
                    'membership_id' => $attributes['membership_id'] ?? null,
                    'amount' => $attributes['amount'] ?? null,
                    'payment_date' => $attributes['payment_date'] ?? null,
                    'payment_method' => $attributes['payment_method'] ?? null,
                    'payment_bank' => $attributes['payment_bank'] ?? null,
                    'bank_transaction_number' => $attributes['bank_transaction_number'] ?? null,
                    'status' => $attributes['status'] ?? 'pending', 
                ];

                if (!isset($validatedAttributes['invoice_id'], $validatedAttributes['membership_id'], $validatedAttributes['amount'], $validatedAttributes['payment_date'], $validatedAttributes['payment_method'], $validatedAttributes['status'])) {
                    throw new \Exception("Missing required attributes.");
                }

                Payment::create($validatedAttributes);
            });

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
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
                throw new \Exception("Only payments with a pending status can be updated to completed.");
            }
    
            $invoice = $model->invoice;
            $totalCompletedPayments = $invoice->payments()
                ->where('status', 'completed')
                ->sum('amount');
    
            if (($totalCompletedPayments + $model->amount) > $invoice->amount) {
                throw new \Exception("Marking this payment as completed will exceed the total invoice amount.");
            }
    
            $model->update(['status' => 'completed']);

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
     * Get filtered payments query for DataTables.
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getFilteredPaymentsQuery(array $filters)
    {
        return Payment::query()
            ->when(isset($filters['start_date']) && isset($filters['end_date']), function ($query) use ($filters) {
                $query->whereBetween('payment_date', [$filters['start_date'], $filters['end_date']]);
            })
            ->when(isset($filters['payment_method']), function ($query) use ($filters) {
                $query->where('payment_method', $filters['payment_method']);
            })
            ->when(isset($filters['status']), function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            });
    }


}
