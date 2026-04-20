<?php

namespace App\Repositories;

use App\Models\Membership;
use App\Models\Package;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\RoundBlockSizeMode;

class MembershipRepository extends BaseRepository {

    /**
     * Store a new membership in the database.
     *
     * @param array $attributes
     * @return mixed
     */
    public function store(array $attributes): mixed
    {
        try {
            return DB::transaction(function () use ($attributes) {
                $validatedAttributes = [
                    'user_id' => $attributes['user_id'] ?? null,
                    'start_date' => $attributes['start_date'] ?? null,
                    'end_date' => $attributes['end_date'] ?? null,
                    'package_id' => $attributes['package_id'] ?? null,
                    'remaining_days' => $attributes['remaining_days'] ?? null,
                    'status' => $attributes['status'] ?? 'inactive',
                    'price' => $attributes['price'] ?? null,
                ];

                
                if (!isset($validatedAttributes['user_id'], $validatedAttributes['start_date'], $validatedAttributes['end_date'], $validatedAttributes['remaining_days'], $validatedAttributes['status'], $validatedAttributes['price'])) {
                    throw new \Exception("Missing required attributes.");
                }

                $user = User::find($validatedAttributes['user_id']);

                if ($user->memberships()->where('status', 'active')->exists()) {
                    throw new \Exception("User already has an active membership.");
                }

                $membership = Membership::create($validatedAttributes);

                $membershipId = $membership->id;
                $timestamp = now()->format('Y_m_d_H_i_s_u');
                $fileName = "qr_code_{$timestamp}.png";

                $writer = new PngWriter();

                $qrCode = new QrCode(
                    data: $membershipId, 
                    encoding: new Encoding('UTF-8'),
                    errorCorrectionLevel: ErrorCorrectionLevel::Low,
                    size: 300,
                    margin: 10,
                    roundBlockSizeMode: RoundBlockSizeMode::Margin,
                    foregroundColor: new Color(0, 0, 0), 
                    backgroundColor: new Color(255, 255, 255) 
                );

                $logoPath = public_path('assets/dist/img/logo.jpg'); 
                $logo = file_exists($logoPath)
                    ? new Logo(
                        path: $logoPath,
                        resizeToWidth: 50,
                        punchoutBackground: true
                    )
                    : null;

                
                $label = new Label(
                    text: 'Membership QR',
                    textColor: new Color(255, 0, 0) 
                );

                $result = $writer->write($qrCode, $logo, $label);

                $filePath = public_path("qr_codes/{$fileName}");

                if (!file_exists(public_path('qr_codes'))) {
                    mkdir(public_path('qr_codes'), 0777, true); 
                }

                file_put_contents($filePath, $result->getString());

                $membership->update(['qr_code' => $fileName]);
                return $membership;
            });

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Update an existing membership in the database.
     *
     * @param mixed $model
     * @param array $attributes
     * @return mixed
     */
    public function update(mixed $model, array $attributes): mixed
    {
        return null;
    }

    /**
     * Deactivate memberships that are past the calendar end date or have no visits left.
     * Calendar rule: inactive the day after `end_date` (same as scheduled daily check).
     *
     * @return int Number of memberships updated
     */
    public function processExpiry(): int
    {
        return (int) DB::transaction(function () {
            $today = Carbon::today()->toDateString();

            $memberships = Membership::query()
                ->where('status', 'active')
                ->where(function ($query) use ($today) {
                    $query->whereDate('end_date', '<', $today)
                        ->orWhere('remaining_days', '<=', 0);
                })
                ->get();

            foreach ($memberships as $membership) {
                $membership->status = 'inactive';
                $membership->save();
            }

            return $memberships->count();
        });
    }

    /**
     * Active memberships whose calendar end date is exactly $daysFromToday days from today.
     *
     * @param  int  $daysFromToday  e.g. 7 means "one week before end_date"
     * @return Collection<int, Membership>
     */
    public function getActiveMembershipsEndingInDays(int $daysFromToday): Collection
    {
        $target = Carbon::today()->addDays($daysFromToday)->toDateString();

        return Membership::query()
            ->where('status', 'active')
            ->whereDate('end_date', $target)
            ->with('user')
            ->get();
    }

    /**
     * Extend an active membership by adding days to the calendar end date and visit allowance.
     * All calendar updates for extensions go through this method.
     *
     * @throws \Exception
     */
    public function extendActiveMembership(Membership $membership, int $days): void
    {
        if ($days < 1 || $days > 10) {
            throw new \Exception(__('Extension must be between 1 and 10 days.'));
        }

        if ($membership->status !== 'active') {
            throw new \Exception(__('Only active memberships can be extended.'));
        }

        DB::transaction(function () use ($membership, $days) {
            $membership->end_date = Carbon::parse($membership->end_date)->addDays($days)->toDateString();
            $membership->remaining_days = ($membership->remaining_days ?? 0) + $days;
            $membership->save();
        });
    }

    /**
     * Switch an active membership to a new package (upgrade / downgrade): new price, duration, and visit grant from the package.
     *
     * @throws \Exception
     */
    public function applyPackageUpgrade(Membership $membership, Package $package): void
    {
        if ($membership->status !== 'active') {
            throw new \Exception(__('Only active memberships can be upgraded.'));
        }

        DB::transaction(function () use ($membership, $package) {
            $membership->package_id = $package->id;
            $membership->price = $package->price;
            $membership->remaining_days = $package->granted_days;

            $start = Carbon::parse($membership->start_date)->startOfDay();
            $base = Carbon::today()->gt($start) ? Carbon::today() : $start;
            $membership->end_date = $base->copy()->addDays($package->duration)->toDateString();
            $membership->save();
        });
    }
}
