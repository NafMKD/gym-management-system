<?php

namespace App\Console\Commands;

use App\Mail\MembershipExpiringSoonMail;
use App\Repositories\MembershipRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotifyExpiringMembershipsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'memberships:notify-expiring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Email members whose membership calendar end date is N days from today (see config membership.notify_days_before_end).';

    /**
     * Execute the console command.
     */
    public function handle(MembershipRepository $membershipRepository): int
    {
        $days = (int) config('membership.notify_days_before_end', 7);

        $this->info("Notifying memberships ending in {$days} day(s)...");

        $memberships = $membershipRepository->getActiveMembershipsEndingInDays($days);

        $sent = 0;
        foreach ($memberships as $membership) {
            $user = $membership->user;
            if (! $user || ! $user->email) {
                continue;
            }

            try {
                Mail::to($user->email)->send(new MembershipExpiringSoonMail($membership));
                $sent++;
            } catch (\Throwable $e) {
                $this->error("Failed to notify membership {$membership->id}: {$e->getMessage()}");
            }
        }

        $this->info("Sent {$sent} notification(s).");

        return self::SUCCESS;
    }
}
