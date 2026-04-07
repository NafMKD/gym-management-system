<?php

namespace App\Console\Commands;

use App\Repositories\MembershipRepository;
use Illuminate\Console\Command;

class ProcessMembershipExpiryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'memberships:process-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set active memberships to inactive when the calendar end date has passed or visit allowance is exhausted.';

    /**
     * Execute the console command.
     */
    public function handle(MembershipRepository $membershipRepository): int
    {
        $this->info('Processing membership expiry...');

        $count = $membershipRepository->processExpiry();

        $this->info("Updated {$count} membership(s).");

        return self::SUCCESS;
    }
}
