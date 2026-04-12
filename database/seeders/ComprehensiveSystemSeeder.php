<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AuditTrail;
use App\Models\ClassBooking;
use App\Models\ClassSchedule;
use App\Models\GymClass;
use App\Models\Invoice;
use App\Models\Membership;
use App\Models\MembershipExtensionRequest;
use App\Models\MerchandiseSaleLine;
use App\Models\Package;
use App\Models\Payment;
use App\Models\PrintBatch;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\TrainerCommissionEntry;
use App\Models\TrainerProfile;
use App\Models\TrainerSessionFeedback;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Large, varied demo dataset for deep manual / QA testing across the whole domain model.
 *
 * Recommended: `php artisan migrate:fresh --seed` (DatabaseSeeder calls this class).
 * Uses Model::withoutEvents to avoid audit observer cascades; audit_trails rows are inserted explicitly.
 */
class ComprehensiveSystemSeeder extends Seeder
{
    /** Tunable scale knobs */
    private const MEMBER_COUNT = 280;

    private const TRAINER_COUNT = 12;

    private const RECEPTION_COUNT = 3;

    private const ADMIN_COUNT = 2;

    private const PACKAGE_COUNT = 28;

    private const PRODUCT_COUNT = 72;

    private const GYM_CLASS_COUNT = 32;

    /** Approximate target membership rows */
    private const MEMBERSHIP_COUNT = 420;

    /** Merchandise-only invoices */
    private const MERCHANDISE_INVOICE_COUNT = 140;

    /** Class schedules (past + future spread) */
    private const SCHEDULE_COUNT = 520;

    /** Max class bookings to create */
    private const BOOKING_TARGET = 2800;

    private const ATTENDANCE_ROWS = 1100;

    private const EXTENSION_REQUEST_ROWS = 95;

    private const PRINT_BATCH_ROWS = 96;

    private const MANUAL_AUDIT_ROWS = 420;

    private Carbon $epochStart;

    public function run(): void
    {
        if (! app()->environment('production')) {
            set_time_limit(0);
        }

        fake()->seed(random_int(1, 1_000_000_000));
        $this->epochStart = Carbon::create(2023, 1, 1, 0, 0, 0, config('app.timezone'));
        $passwordHash = Hash::make('12345678');

        Model::withoutEvents(function () use ($passwordHash): void {
            DB::transaction(function () use ($passwordHash): void {
                $this->seedFixedAccounts($passwordHash);
                $adminIds = User::query()->where('role', 'admin')->pluck('id')->all();
                $trainerIds = User::query()->where('role', 'trainer')->pluck('id')->all();
                $receptionIds = User::query()->where('role', 'reception')->pluck('id')->all();
                $memberIds = User::query()->where('role', 'member')->pluck('id')->all();

                $primaryAdminId = $adminIds[0] ?? 1;

                $packageIds = $this->seedPackages();
                $productIds = $this->seedProducts();
                $gymClassIds = $this->seedGymClasses();

                $this->seedTrainerProfiles($trainerIds, $primaryAdminId);
                $membershipIds = $this->seedMemberships($memberIds, $packageIds);
                $this->seedMembershipInvoicesAndPayments($membershipIds, $primaryAdminId);
                $this->seedMerchandiseFlow($productIds, $memberIds, $primaryAdminId);
                $scheduleRows = $this->seedClassSchedules($gymClassIds, $trainerIds);
                $bookingIds = $this->seedClassBookings($scheduleRows, $membershipIds, $receptionIds, $trainerIds);
                $this->seedTrainerCommissionsAndFeedback($bookingIds, $trainerIds, $primaryAdminId);
                $this->seedExtensionRequests($membershipIds, $memberIds, $adminIds);
                $this->seedAttendances($membershipIds);
                $this->seedPrintBatches($membershipIds);
                $this->seedExtraStockMovements($productIds, $adminIds);
                $this->seedManualAuditTrails($adminIds, $memberIds, $membershipIds);
            });
        });

        $this->command?->info('ComprehensiveSystemSeeder finished.');
        $this->command?->table(
            ['Login (password: 12345678)', 'Phone'],
            [
                ['admin@gmail.com', '0911111111'],
                ['trainer@gmail.com', '0922222222'],
                ['reception@gmail.com', '0933333333'],
                ['member@gmail.com', '0944444444'],
            ]
        );
    }

    private function seedFixedAccounts(string $passwordHash): void
    {
        $fixed = [
            ['Admin', 'User', 'admin@gmail.com', '0911111111', 'admin', 'Male'],
            ['Trainer', 'User', 'trainer@gmail.com', '0922222222', 'trainer', 'Female'],
            ['Reception', 'User', 'reception@gmail.com', '0933333333', 'reception', 'Female'],
            ['Member', 'User', 'member@gmail.com', '0944444444', 'member', 'Male'],
        ];

        foreach ($fixed as [$fn, $ln, $email, $phone, $role, $gender]) {
            User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'first_name' => $fn,
                    'last_name' => $ln,
                    'password' => $passwordHash,
                    'phone' => $phone,
                    'role' => $role,
                    'gender' => $gender,
                    'email_verified_at' => $this->randomDateTime(),
                    'created_at' => $this->randomDateTime(),
                    'updated_at' => $this->randomDateTime(),
                ]
            );
        }

        $n = 100;
        for ($i = 0; $i < self::MEMBER_COUNT; $i++) {
            $phone = sprintf('09%08d', $n++);
            while (User::query()->where('phone', $phone)->exists()) {
                $phone = sprintf('09%08d', $n++);
            }
            $created = $this->randomDateTime();
            User::query()->create([
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'email' => fake()->boolean(70) ? fake()->unique()->safeEmail() : null,
                'password' => $passwordHash,
                'phone' => $phone,
                'role' => 'member',
                'gender' => fake()->randomElement(['Female', 'Male']),
                'email_verified_at' => fake()->boolean(75) ? $created : null,
                'created_at' => $created,
                'updated_at' => (clone $created)->addMinutes(random_int(0, 500_000)),
            ]);
        }

        for ($t = 0; $t < self::TRAINER_COUNT; $t++) {
            $phone = sprintf('09%08d', $n++);
            $created = $this->randomDateTime();
            User::query()->create([
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'email' => fake()->unique()->safeEmail(),
                'password' => $passwordHash,
                'phone' => $phone,
                'role' => 'trainer',
                'gender' => fake()->randomElement(['Female', 'Male']),
                'email_verified_at' => $created,
                'created_at' => $created,
                'updated_at' => (clone $created)->addHours(random_int(1, 2000)),
            ]);
        }

        for ($r = 0; $r < self::RECEPTION_COUNT; $r++) {
            $phone = sprintf('09%08d', $n++);
            $created = $this->randomDateTime();
            User::query()->create([
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'email' => fake()->unique()->safeEmail(),
                'password' => $passwordHash,
                'phone' => $phone,
                'role' => 'reception',
                'gender' => fake()->randomElement(['Female', 'Male']),
                'email_verified_at' => $created,
                'created_at' => $created,
                'updated_at' => (clone $created)->addMinutes(random_int(10, 50_000)),
            ]);
        }

        for ($a = 0; $a < self::ADMIN_COUNT; $a++) {
            $phone = sprintf('09%08d', $n++);
            $created = $this->randomDateTime();
            User::query()->create([
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'email' => fake()->unique()->safeEmail(),
                'password' => $passwordHash,
                'phone' => $phone,
                'role' => 'admin',
                'gender' => fake()->randomElement(['Female', 'Male']),
                'email_verified_at' => $created,
                'created_at' => $created,
                'updated_at' => (clone $created)->addDays(random_int(1, 400)),
            ]);
        }
    }

    /**
     * @return list<int>
     */
    private function seedPackages(): array
    {
        $ids = [];
        $templates = [
            ['Bronze', 30, 30, 49.99],
            ['Silver', 60, 60, 89.00],
            ['Gold', 90, 90, 129.50],
            ['Platinum Annual', 365, 365, 899.00],
            ['Student Flex', 14, 14, 29.00],
            ['Corporate', 180, 150, 499.99],
        ];

        for ($i = 0; $i < self::PACKAGE_COUNT; $i++) {
            $tpl = $templates[$i % count($templates)];
            $noise = fake()->randomFloat(2, 0.5, 40);
            $created = $this->randomDateTime();
            $p = Package::query()->create([
                'name' => $tpl[0].' — '.Str::upper(fake()->lexify('??')).'-'.$i,
                'duration' => $tpl[1] + random_int(-3, 10),
                'granted_days' => max(1, $tpl[2] + random_int(-5, 5)),
                'price' => round($tpl[3] + $noise, 2),
                'description' => fake()->boolean(80) ? fake()->paragraphs(random_int(1, 3), true) : null,
                'created_at' => $created,
                'updated_at' => (clone $created)->addHours(random_int(1, 9000)),
            ]);
            $ids[] = $p->id;
        }

        return $ids;
    }

    /**
     * @return list<int>
     */
    private function seedProducts(): array
    {
        $ids = [];
        for ($i = 0; $i < self::PRODUCT_COUNT; $i++) {
            $stock = random_int(5, 800);
            $created = $this->randomDateTime();
            $p = Product::query()->create([
                'name' => fake()->words(3, true).' '.fake()->randomElement(['Shaker', 'Belt', 'Bar', 'Mat', 'Towel', 'Drink', 'Bar']),
                'sku' => 'SKU-'.strtoupper(fake()->bothify('??##??')).'-'.$i,
                'description' => fake()->boolean(60) ? fake()->text(200) : null,
                'unit_price' => fake()->randomFloat(2, 3, 220),
                'stock_quantity' => $stock,
                'low_stock_threshold' => random_int(2, min(25, $stock)),
                'is_active' => fake()->boolean(92),
                'created_at' => $created,
                'updated_at' => (clone $created)->addMinutes(random_int(1, 200_000)),
            ]);
            $ids[] = $p->id;
        }

        return $ids;
    }

    /**
     * @param  list<int>  $trainerIds
     */
    private function seedTrainerProfiles(array $trainerIds, int $primaryAdminId): void
    {
        foreach ($trainerIds as $tid) {
            $created = $this->randomDateTime();
            TrainerProfile::query()->updateOrCreate(
                ['user_id' => $tid],
                [
                    'qualifications' => fake()->boolean(85) ? fake()->paragraph() : null,
                    'specializations' => fake()->boolean(90) ? implode(', ', fake()->words(random_int(2, 8))) : null,
                    'bio' => fake()->boolean(70) ? fake()->paragraphs(2, true) : null,
                    'commission_per_session' => fake()->randomFloat(2, 0, 45),
                    'created_at' => $created,
                    'updated_at' => (clone $created)->addDays(random_int(0, 200)),
                ]
            );
        }
    }

    /**
     * @return list<int>
     */
    private function seedGymClasses(): array
    {
        $types = ['HIIT', 'Yoga', 'Spin', 'Pilates', 'CrossFit', 'Aqua', 'Boxing', 'Stretch', 'Core', 'Dance'];
        $ids = [];
        for ($i = 0; $i < self::GYM_CLASS_COUNT; $i++) {
            $created = $this->randomDateTime();
            $g = GymClass::query()->create([
                'name' => fake()->randomElement($types).' '.fake()->city().' '.($i + 1),
                'description' => fake()->boolean(75) ? fake()->paragraph() : null,
                'capacity' => random_int(8, 35),
                'duration_minutes' => fake()->randomElement([30, 45, 50, 60, 75, 90]),
                'is_active' => fake()->boolean(88),
                'created_at' => $created,
                'updated_at' => (clone $created)->addHours(random_int(1, 5000)),
            ]);
            $ids[] = $g->id;
        }

        return $ids;
    }

    /**
     * @param  list<int>  $memberIds
     * @param  list<int>  $packageIds
     * @return list<int>
     */
    private function seedMemberships(array $memberIds, array $packageIds): array
    {
        $membershipIds = [];
        shuffle($memberIds);
        $slice = array_slice($memberIds, 0, min(self::MEMBERSHIP_COUNT, count($memberIds)));

        foreach ($slice as $idx => $userId) {
            $pkgId = fake()->randomElement($packageIds);
            $pkg = Package::query()->findOrFail($pkgId);
            $start = Carbon::instance(fake()->dateTimeBetween($this->epochStart, Carbon::now()->subDays(10)));
            $end = (clone $start)->addDays(max(1, $pkg->granted_days + random_int(-2, 8)));
            $remaining = max(0, min((int) $end->diffInDays(Carbon::today(), false), (int) $pkg->granted_days));
            $statusRoll = fake()->randomElement(['active', 'active', 'active', 'inactive', 'cancelled']);
            if ($end->isPast() && $statusRoll === 'active') {
                $statusRoll = fake()->randomElement(['active', 'inactive']);
            }

            $created = $this->randomDateTimeBetween($start->copy()->subDays(30), $start->copy()->addDays(5));
            $m = Membership::query()->create([
                'user_id' => $userId,
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'package_id' => $pkgId,
                'remaining_days' => $remaining,
                'status' => $statusRoll,
                'price' => $pkg->price,
                'qr_code' => 'QR-'.Str::upper(Str::random(10)).'-'.$idx,
                'created_at' => $created,
                'updated_at' => (clone $created)->addMinutes(random_int(10, 80_000)),
            ]);
            $membershipIds[] = $m->id;
        }

        return $membershipIds;
    }

    /**
     * @param  list<int>  $membershipIds
     */
    private function seedMembershipInvoicesAndPayments(array $membershipIds, int $primaryAdminId): void
    {
        $year = Carbon::now()->format('y');
        $seq = 1;

        foreach ($membershipIds as $mid) {
            $m = Membership::query()->findOrFail($mid);
            $invNo = sprintf('INV-%s-%06d', $year, $seq++);
            $issued = $this->randomDateTimeBetween($this->epochStart, Carbon::now());
            $paid = fake()->boolean(82);

            $inv = Invoice::query()->create([
                'membership_id' => $mid,
                'user_id' => null,
                'invoice_number' => $invNo,
                'amount' => $m->price,
                'status' => $paid ? 'paid' : fake()->randomElement(['paid', 'unpaid']),
                'issued_date' => $issued,
                'due_date' => (clone $issued)->addDays(random_int(1, 21)),
                'invoice_source' => 'membership',
                'created_at' => $issued,
                'updated_at' => (clone $issued)->addHours(random_int(1, 200)),
            ]);

            if ($inv->status === 'paid') {
                $payDate = Carbon::instance(fake()->dateTimeBetween(Carbon::parse($inv->issued_date), Carbon::now()));
                $method = fake()->randomElement(['cash', 'bank']);
                Payment::query()->create([
                    'invoice_id' => $inv->id,
                    'membership_id' => $mid,
                    'amount' => $m->price,
                    'payment_date' => $payDate,
                    'payment_method' => $method,
                    'payment_bank' => $method === 'bank' ? fake()->randomElement(['telebirr', 'cbe', 'boa']) : null,
                    'bank_transaction_number' => $method === 'bank' && fake()->boolean(60) ? fake()->numerify('########') : null,
                    'status' => 'completed',
                    'payment_type' => 'payment',
                    'notes' => fake()->boolean(25) ? fake()->sentence() : null,
                    'created_at' => $payDate,
                    'updated_at' => (clone $payDate)->addMinutes(random_int(1, 120)),
                ]);
            }
        }
    }

    /**
     * @param  list<int>  $productIds
     * @param  list<int>  $memberIds
     */
    private function seedMerchandiseFlow(array $productIds, array $memberIds, int $primaryAdminId): void
    {
        $year = Carbon::now()->format('y');
        $seq = 50_000;

        for ($k = 0; $k < self::MERCHANDISE_INVOICE_COUNT; $k++) {
            $lines = random_int(1, 4);
            $total = 0.0;
            $lineRows = [];

            for ($l = 0; $l < $lines; $l++) {
                $pid = fake()->randomElement($productIds);
                $product = Product::query()->findOrFail($pid);
                if ($product->stock_quantity < 1) {
                    continue;
                }
                $qty = random_int(1, min(5, (int) $product->stock_quantity));
                $unit = (float) $product->unit_price;
                $lineTot = round($unit * $qty, 2);
                $total += $lineTot;
                $lineRows[] = [$pid, $qty, $unit, $lineTot];
            }

            $total = round($total, 2);
            if ($lineRows === [] || $total <= 0) {
                continue;
            }

            $walkIn = fake()->boolean(35);
            $userId = $walkIn ? null : fake()->randomElement($memberIds);
            $issued = $this->randomDateTime();
            $invNo = sprintf('INV-%s-%06d', $year, $seq++);

            $inv = Invoice::query()->create([
                'membership_id' => null,
                'user_id' => $userId,
                'invoice_number' => $invNo,
                'amount' => $total,
                'status' => 'paid',
                'issued_date' => $issued,
                'due_date' => (clone $issued)->addDays(3),
                'invoice_source' => 'merchandise',
                'created_at' => $issued,
                'updated_at' => (clone $issued)->addMinutes(random_int(5, 400)),
            ]);

            foreach ($lineRows as [$pid, $qty, $unit, $lineTot]) {
                $product = Product::query()->findOrFail($pid);
                if ($product->stock_quantity < $qty) {
                    $qty = (int) $product->stock_quantity;
                }
                if ($qty < 1) {
                    continue;
                }

                MerchandiseSaleLine::query()->create([
                    'invoice_id' => $inv->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unit,
                    'line_total' => round($unit * $qty, 2),
                    'created_at' => $issued,
                    'updated_at' => (clone $issued)->addSecond(),
                ]);

                $product->decrement('stock_quantity', $qty);

                StockMovement::query()->create([
                    'product_id' => $product->id,
                    'quantity_change' => -$qty,
                    'reason' => 'sale',
                    'invoice_id' => $inv->id,
                    'user_id' => fake()->randomElement([$primaryAdminId, null]),
                    'notes' => null,
                    'created_at' => $issued,
                    'updated_at' => $issued,
                ]);
            }

            $method = fake()->randomElement(['cash', 'bank']);
            $payDate = Carbon::instance(fake()->dateTimeBetween($issued, Carbon::now()));
            Payment::query()->create([
                'invoice_id' => $inv->id,
                'membership_id' => null,
                'amount' => $total,
                'payment_date' => $payDate,
                'payment_method' => $method,
                'payment_bank' => $method === 'bank' ? fake()->randomElement(['telebirr', 'cbe', 'boa']) : null,
                'bank_transaction_number' => $method === 'bank' ? fake()->optional(0.5)->numerify('TXN#######') : null,
                'status' => 'completed',
                'payment_type' => 'payment',
                'notes' => fake()->boolean(20) ? fake()->sentence() : null,
                'created_at' => $payDate,
                'updated_at' => (clone $payDate)->addMinute(),
            ]);
        }
    }

    /**
     * @param  list<int>  $gymClassIds
     * @param  list<int>  $trainerIds
     * @return list<object{class:int,gym_class_id:int,trainer_id:int,starts_at:Carbon,ends_at:Carbon,capacity:?int}>
     */
    private function seedClassSchedules(array $gymClassIds, array $trainerIds): array
    {
        $rows = [];
        $day = Carbon::now()->subDays(75)->startOfDay();

        for ($i = 0; $i < self::SCHEDULE_COUNT; $i++) {
            $trainerId = fake()->randomElement($trainerIds);
            $gymClassId = fake()->randomElement($gymClassIds);
            $gc = GymClass::query()->findOrFail($gymClassId);

            $hour = random_int(6, 20);
            $minute = fake()->randomElement([0, 15, 30, 45]);
            $starts = (clone $day)->setTime($hour, $minute, random_int(0, 59));
            $dur = (int) $gc->duration_minutes;
            $ends = (clone $starts)->addMinutes($dur + random_int(0, 5));

            $created = $this->randomDateTimeBetween($this->epochStart, $starts);
            $sch = ClassSchedule::query()->create([
                'gym_class_id' => $gymClassId,
                'trainer_id' => $trainerId,
                'starts_at' => $starts,
                'ends_at' => $ends,
                'capacity_override' => fake()->boolean(25) ? random_int((int) $gc->capacity, (int) $gc->capacity + 15) : null,
                'notes' => fake()->boolean(30) ? fake()->sentence() : null,
                'created_at' => $created,
                'updated_at' => (clone $created)->addHours(random_int(1, 48)),
            ]);

            $rows[] = (object) [
                'id' => $sch->id,
                'gym_class_id' => $gymClassId,
                'trainer_id' => $trainerId,
                'starts_at' => $starts,
                'ends_at' => $ends,
                'capacity' => $sch->capacity_override ?? $gc->capacity,
            ];

            if ($i % 3 === 0) {
                $day->addDay();
            }
        }

        return $rows;
    }

    /**
     * @param  list<object>  $scheduleRows
     * @param  list<int>  $membershipIds
     * @param  list<int>  $receptionIds
     * @param  list<int>  $trainerIds
     * @return list<int>
     */
    private function seedClassBookings(array $scheduleRows, array $membershipIds, array $receptionIds, array $trainerIds): array
    {
        $bookingIds = [];
        $activeMemberships = Membership::query()
            ->where('status', 'active')
            ->whereDate('end_date', '>=', Carbon::today())
            ->pluck('id')
            ->all();

        if ($activeMemberships === []) {
            $activeMemberships = $membershipIds;
        }

        shuffle($scheduleRows);
        $cap = min(self::BOOKING_TARGET, count($scheduleRows) * 8);
        $created = 0;

        foreach ($scheduleRows as $sch) {
            if ($created >= $cap) {
                break;
            }
            $bookingsThisSession = random_int(0, min((int) $sch->capacity, 12));
            for ($b = 0; $b < $bookingsThisSession; $b++) {
                $mid = fake()->randomElement($activeMemberships);
                $ts = $this->randomDateTimeBetween($sch->starts_at->copy()->subDays(14), $sch->starts_at);
                $status = fake()->randomElement(['pending', 'confirmed', 'confirmed', 'confirmed', 'cancelled', 'attended']);

                $cb = ClassBooking::query()->create([
                    'class_schedule_id' => $sch->id,
                    'membership_id' => $mid,
                    'booked_by_user_id' => fake()->boolean(70) ? fake()->randomElement($receptionIds) : null,
                    'status' => $status,
                    'created_at' => $ts,
                    'updated_at' => (clone $ts)->addMinutes(random_int(1, 400)),
                ]);
                $bookingIds[] = $cb->id;
                $created++;
                if ($created >= $cap) {
                    break 2;
                }
            }
        }

        return $bookingIds;
    }

    /**
     * @param  list<int>  $bookingIds
     * @param  list<int>  $trainerIds
     */
    private function seedTrainerCommissionsAndFeedback(array $bookingIds, array $trainerIds, int $primaryAdminId): void
    {
        shuffle($bookingIds);
        $subset = array_slice($bookingIds, 0, min(600, count($bookingIds)));

        foreach ($subset as $bid) {
            $booking = ClassBooking::query()->with('schedule')->find($bid);
            if (! $booking || ! $booking->schedule) {
                continue;
            }

            if (fake()->boolean(55) && $booking->status === 'attended') {
                $amount = fake()->randomFloat(2, 5, 60);
                $earned = $booking->schedule->starts_at instanceof Carbon
                    ? $booking->schedule->starts_at->toDateString()
                    : Carbon::parse($booking->schedule->starts_at)->toDateString();

                TrainerCommissionEntry::query()->updateOrCreate(
                    ['class_booking_id' => $bid],
                    [
                        'trainer_id' => $booking->schedule->trainer_id,
                        'source' => TrainerCommissionEntry::SOURCE_SESSION,
                        'amount' => $amount,
                        'earned_at' => $earned,
                        'notes' => fake()->boolean(40) ? fake()->sentence() : null,
                        'recorded_by_user_id' => fake()->randomElement([$primaryAdminId, null]),
                        'created_at' => $this->randomDateTime(),
                        'updated_at' => $this->randomDateTime(),
                    ]
                );
            }

            if (fake()->boolean(35)) {
                TrainerSessionFeedback::query()->updateOrCreate(
                    ['class_booking_id' => $bid],
                    [
                        'rating' => random_int(1, 5),
                        'comment' => fake()->boolean(70) ? fake()->paragraph() : null,
                        'created_at' => $this->randomDateTime(),
                        'updated_at' => $this->randomDateTime(),
                    ]
                );
            }
        }

        for ($m = 0; $m < 80; $m++) {
            $t = fake()->randomElement($trainerIds);
            $created = $this->randomDateTime();
            TrainerCommissionEntry::query()->create([
                'trainer_id' => $t,
                'source' => TrainerCommissionEntry::SOURCE_MANUAL,
                'class_booking_id' => null,
                'amount' => fake()->randomFloat(2, 10, 200),
                'earned_at' => fake()->dateTimeBetween($this->epochStart, Carbon::now())->format('Y-m-d'),
                'notes' => fake()->sentence(),
                'recorded_by_user_id' => $primaryAdminId,
                'created_at' => $created,
                'updated_at' => $created,
            ]);
        }
    }

    /**
     * @param  list<int>  $membershipIds
     * @param  list<int>  $memberIds
     * @param  list<int>  $adminIds
     */
    private function seedExtensionRequests(array $membershipIds, array $memberIds, array $adminIds): void
    {
        shuffle($membershipIds);
        $slice = array_slice($membershipIds, 0, min(self::EXTENSION_REQUEST_ROWS, count($membershipIds)));

        foreach ($slice as $mid) {
            $m = Membership::query()->find($mid);
            if (! $m) {
                continue;
            }
            $status = fake()->randomElement(['pending', 'pending', 'approved', 'rejected']);
            $created = $this->randomDateTime();

            MembershipExtensionRequest::query()->create([
                'membership_id' => $mid,
                'requested_by' => $m->user_id,
                'reason' => fake()->paragraph(),
                'requested_days' => random_int(1, 21),
                'status' => $status,
                'approved_by' => $status === 'approved' ? fake()->randomElement($adminIds) : null,
                'rejection_reason' => $status === 'rejected' ? fake()->sentence() : null,
                'created_at' => $created,
                'updated_at' => (clone $created)->addHours(random_int(1, 200)),
            ]);
        }
    }

    /**
     * @param  list<int>  $membershipIds
     */
    private function seedAttendances(array $membershipIds): void
    {
        for ($i = 0; $i < self::ATTENDANCE_ROWS; $i++) {
            $mid = fake()->randomElement($membershipIds);
            $entry = fake()->dateTimeBetween($this->epochStart, Carbon::now());
            Attendance::query()->create([
                'membership_id' => $mid,
                'entry_date' => $entry,
                'created_at' => Carbon::instance($entry)->addMinutes(random_int(0, 5)),
                'updated_at' => Carbon::instance($entry)->addMinutes(random_int(1, 30)),
            ]);
        }
    }

    /**
     * @param  list<int>  $membershipIds
     */
    private function seedPrintBatches(array $membershipIds): void
    {
        for ($i = 0; $i < self::PRINT_BATCH_ROWS; $i++) {
            $created = $this->randomDateTime();
            PrintBatch::query()->create([
                'position' => random_int(1, 8),
                'membership_id' => fake()->randomElement($membershipIds),
                'is_printed' => fake()->boolean(70),
                'created_at' => $created,
                'updated_at' => (clone $created)->addMinutes(random_int(1, 500)),
            ]);
        }
    }

    /**
     * @param  list<int>  $productIds
     * @param  list<int>  $adminIds
     */
    private function seedExtraStockMovements(array $productIds, array $adminIds): void
    {
        for ($i = 0; $i < 180; $i++) {
            $pid = fake()->randomElement($productIds);
            $reason = fake()->randomElement(['restock', 'adjustment', 'adjustment']);
            $delta = $reason === 'restock' ? random_int(10, 200) : random_int(-15, 40);
            $ts = $this->randomDateTime();
            StockMovement::query()->create([
                'product_id' => $pid,
                'quantity_change' => $delta,
                'reason' => $reason,
                'invoice_id' => null,
                'user_id' => fake()->randomElement($adminIds),
                'notes' => fake()->boolean(50) ? fake()->sentence() : null,
                'created_at' => $ts,
                'updated_at' => $ts,
            ]);

            if ($delta !== 0) {
                Product::query()->whereKey($pid)->increment('stock_quantity', $delta);
            }
        }
    }

    /**
     * @param  list<int>  $adminIds
     * @param  list<int>  $memberIds
     * @param  list<int>  $membershipIds
     */
    private function seedManualAuditTrails(array $adminIds, array $memberIds, array $membershipIds): void
    {
        $tables = ['users', 'memberships', 'invoices', 'payments', 'packages', 'products'];

        for ($i = 0; $i < self::MANUAL_AUDIT_ROWS; $i++) {
            $table = fake()->randomElement($tables);
            $recordId = match ($table) {
                'users' => fake()->randomElement($memberIds),
                'memberships' => fake()->randomElement($membershipIds),
                'packages' => fake()->randomElement(Package::query()->pluck('id')->all()),
                'products' => fake()->randomElement(Product::query()->pluck('id')->all()),
                'invoices' => fake()->randomElement(Invoice::query()->pluck('id')->all()),
                'payments' => fake()->randomElement(Payment::query()->pluck('id')->all()),
                default => 1,
            };

            $action = fake()->randomElement(['insert', 'update', 'delete']);
            $ts = $this->randomDateTime();

            AuditTrail::query()->create([
                'table_name' => $table,
                'record_id' => $recordId,
                'user_id' => fake()->randomElement($adminIds),
                'action' => $action,
                'changed_data' => [
                    'seed' => true,
                    'batch' => $i,
                    'payload' => fake()->words(random_int(4, 20)),
                ],
                'created_at' => $ts,
                'updated_at' => (clone $ts)->addMinutes(random_int(1, 120)),
            ]);
        }
    }

    private function randomDateTime(): Carbon
    {
        $end = Carbon::now();
        if ($this->epochStart->greaterThan($end)) {
            return $end->copy();
        }

        return Carbon::instance(fake()->dateTimeBetween($this->epochStart, $end));
    }

    private function randomDateTimeBetween(Carbon $start, Carbon $end): Carbon
    {
        if ($start->greaterThan($end)) {
            [$start, $end] = [$end, $start];
        }
        if ($start->equalTo($end)) {
            return $start->copy();
        }

        return Carbon::instance(fake()->dateTimeBetween($start, $end));
    }
}
