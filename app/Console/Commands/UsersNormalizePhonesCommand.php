<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Console\Command;

/**
 * Re-validate stored phones and log rows that do not match the Chapter 3 local format.
 */
class UsersNormalizePhonesCommand extends Command
{
    protected $signature = 'users:normalize-phones {--fix : Attempt to normalize phones in place where possible}';

    protected $description = 'Validate users.phone against 07/09 + 8 digits; optional --fix applies PhoneNumber::normalize.';

    public function handle(): int
    {
        $fix = (bool) $this->option('fix');
        $invalid = 0;
        $fixed = 0;

        User::query()->orderBy('id')->chunk(200, function ($users) use ($fix, &$invalid, &$fixed) {
            foreach ($users as $user) {
                $raw = (string) $user->phone;
                if (PhoneNumber::isValid($raw)) {
                    continue;
                }

                $invalid++;
                $this->warn("Invalid phone for user {$user->id}: {$raw}");

                if ($fix) {
                    $n = PhoneNumber::normalize($raw);
                    if ($n !== null && PhoneNumber::isValid($n)) {
                        $exists = User::query()->where('phone', $n)->where('id', '!=', $user->id)->exists();
                        if (! $exists) {
                            $user->update(['phone' => $n]);
                            $fixed++;
                            $this->info("  -> updated to {$n}");
                        } else {
                            $this->error("  -> cannot fix: {$n} already taken");
                        }
                    }
                }
            }
        });

        $this->info("Checked; invalid: {$invalid}".($fix ? ", fixed: {$fixed}" : ''));

        return self::SUCCESS;
    }
}
