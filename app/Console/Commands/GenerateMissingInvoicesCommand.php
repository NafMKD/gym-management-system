<?php

namespace App\Console\Commands;

use App\Models\Membership;
use App\Repositories\InvoiceRepository;
use Illuminate\Console\Command;

class GenerateMissingInvoicesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate-missing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate invoices for memberships that do not have invoices.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating missing invoices...');

        $memberships = Membership::whereDoesntHave('invoices')->get();

        foreach ($memberships as $membership) {
            $invoiceRepository = new InvoiceRepository();

            $invoice = $invoiceRepository->store([
                'membership_id' => $membership->id,
                'amount' => $membership->price, 
            ]);
            
            $this->info("Invoice {$invoice->invoice_number} generated for membership {$membership->id}.");
        }

        $this->info('Missing invoices generated successfully.');
    }
}
