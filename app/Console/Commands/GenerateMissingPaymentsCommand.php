<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateMissingPaymentsCommand extends Command
{
    // Define the command signature
    protected $signature = 'payments:generate-missing';

    // Command description
    protected $description = 'Generate payments for invoices that do not have payments';

    public function handle()
    {
        $this->info('Generating missing payments...');

        // Find invoices that do not have payments
        $invoices = Invoice::whereDoesntHave('payments')->get();

        if ($invoices->isEmpty()) {
            $this->info('No missing payments found.');
            return;
        }

        DB::beginTransaction();
        try {
            foreach ($invoices as $invoice) {
                $payment = Payment::create([
                    'invoice_id' => $invoice->id,
                    'membership_id' => $invoice->membership_id,
                    'amount' => $invoice->amount,
                    'payment_date' => $invoice->issued_date,
                    'payment_method' => 'bank',
                    'status' => 'completed'
                ]);

                // Mark the invoice as paid
                $invoice->update(['status' => 'paid']);

                $this->info("Payment {$payment->id} generated for invoice {$invoice->invoice_number}.");
            }

            DB::commit();
            $this->info('Missing payments generated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error generating payments: ' . $e->getMessage());
        }
    }
}
