<?php

namespace App\Repositories;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
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
                    'user_id' => $attributes['user_id'] ?? null,
                    'created_by_user_id' => $attributes['created_by_user_id'] ?? Auth::id(),
                    'amount' => $attributes['amount'] ?? null,
                    'invoice_source' => $attributes['invoice_source'] ?? 'membership',
                ];

                if (! isset($validatedAttributes['amount'])) {
                    throw new \Exception('Missing required attributes.');
                }

                $source = $validatedAttributes['invoice_source'];
                if ($source === 'membership' && empty($validatedAttributes['membership_id'])) {
                    throw new \Exception('Membership is required for membership invoices.');
                }
                // Merchandise: user_id optional (walk-in / retail without linked member).

                $year = now()->format('y');
                $lastInvoice = Invoice::where('invoice_number', 'like', "INV-$year-%")->latest('id')->first();
                $nextNumber = $lastInvoice
                    ? intval(substr($lastInvoice->invoice_number, -5)) + 1
                    : 1;
                $validatedAttributes['invoice_number'] = 'INV-'.$year.'-'.str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);

                $validatedAttributes['status'] = 'unpaid';
                $validatedAttributes['issued_date'] = Carbon::now();
                $validatedAttributes['due_date'] = Carbon::now()->addDays(7);

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

        return (float) $totalPaid >= (float) $invoice->amount;
    }

    /**
     * Set invoice paid/unpaid from net completed payments (payments minus refunds).
     */
    public function syncInvoiceStatusFromPayments(Invoice $invoice): void
    {
        $invoice->refresh();
        $net = (float) $invoice->payments()->where('status', 'completed')->sum('amount');
        $due = (float) $invoice->amount;

        if ($net >= $due && $due > 0) {
            if ($invoice->status !== 'paid') {
                $this->markAsPaid($invoice);
            }

            return;
        }

        if ($invoice->status === 'paid') {
            $invoice->update(['status' => 'unpaid']);
        }
    }

    /**
     * Build the accountant/admin invoice reporting query.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getFilteredInvoicesQuery(array $filters): Builder
    {
        return Invoice::query()
            ->with(['membership.user', 'membership.package', 'customer', 'createdBy'])
            ->when(! empty($filters['issued_from']), function (Builder $query) use ($filters) {
                $query->where('issued_date', '>=', $this->reportDateStart((string) $filters['issued_from']));
            })
            ->when(! empty($filters['issued_to']), function (Builder $query) use ($filters) {
                $query->where('issued_date', '<=', $this->reportDateEnd((string) $filters['issued_to']));
            })
            ->when(! empty($filters['status']), function (Builder $query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when(! empty($filters['invoice_source']), function (Builder $query) use ($filters) {
                $query->where('invoice_source', $filters['invoice_source']);
            })
            ->when(! empty($filters['created_by_user_id']), function (Builder $query) use ($filters) {
                $query->where('created_by_user_id', (int) $filters['created_by_user_id']);
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
