<?php

namespace App\Repositories;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class InvoiceRepository extends BaseRepository
{
    /**
     * Store a new invoice in the database.
     *
     * @param array $attributes
     * @return mixed
     * @throws \Exception
     */
    public function store(array $attributes): mixed
    {
        try {
            return DB::transaction(function () use ($attributes) {
                $validatedAttributes = [
                    'membership_id' => $attributes['membership_id'] ?? null,
                    'amount' => $attributes['amount'] ?? null
                ];

                if (!isset($validatedAttributes['membership_id'], $validatedAttributes['amount'])) {
                    throw new \Exception("Missing required attributes.");
                }

                $year = now()->format('y'); 
                $lastInvoice = Invoice::where('invoice_number', 'like', "INV-$year-%")->latest('id')->first();
                $nextNumber = $lastInvoice
                    ? intval(substr($lastInvoice->invoice_number, -5)) + 1
                    : 1; 
                $validatedAttributes['invoice_number'] = 'INV-' . $year . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
                
                $validatedAttributes['status'] = 'unpaid';
                $validatedAttributes['issued_date'] = now();
                $validatedAttributes['due_date'] = now()->addDays(7);

                return Invoice::create($validatedAttributes);
            });
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Update an invoice in the database.
     *
     * @param mixed $invoice
     * @param array $attributes
     * @return mixed
     * @throws \Exception
     */
    public function update(mixed $invoice, array $attributes): mixed
    {
        return false;
    }

    /**
     * Mark an invoice as paid.
     *
     * @param mixed $invoice
     * @return mixed
     * @throws \Exception
     */
    public function markAsPaid(mixed $invoice): mixed
    {
        return $invoice->update(['status' => 'paid']);
    }

    /**
     * Retrieve invoices by membership ID.
     *
     * @param int $membershipId
     * @return mixed
     */
    public function getByMembershipId(int $membershipId): mixed
    {
        return Invoice::where('membership_id', $membershipId)->get();
    }
    
    /**
     * Check if the invoice is paid.
     * 
     * @param mixed $invoice
     * @return bool
     */
    public function isInvoicePaid(mixed $invoice): bool
    {
        $totalPaid = $invoice->payments()
            ->where('status', 'completed')
            ->sum('amount');

    return $totalPaid >= $invoice->amount;
    }
}
