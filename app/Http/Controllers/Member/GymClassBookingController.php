<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\ClassBooking;
use App\Models\ClassSchedule;
use App\Models\Membership;
use App\Repositories\ClassBookingRepository;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Throwable;

class GymClassBookingController extends Controller
{
    public function __construct(
        protected ClassBookingRepository $classBookingRepository
    ) {
    }

    /**
     * Upcoming sessions the member can book.
     */
    public function index(): View|RedirectResponse
    {
        try {
            $schedules = ClassSchedule::query()
                ->with(['gymClass', 'trainer'])
                ->withCount([
                    'bookings as bookings_active_count' => fn ($q) => $q->whereIn('status', ['pending', 'confirmed', 'attended']),
                ])
                ->where('ends_at', '>', Carbon::now())
                ->orderBy('starts_at')
                ->paginate(15);

            $membership = Membership::query()
                ->where('user_id', Auth::id())
                ->where('status', 'active')
                ->first();

            $memberBookings = collect();
            if ($membership && $schedules->isNotEmpty()) {
                $scheduleIds = collect($schedules->items())->pluck('id')->all();
                $memberBookings = ClassBooking::query()
                    ->where('membership_id', $membership->id)
                    ->whereIn('status', ['pending', 'confirmed', 'attended'])
                    ->whereIn('class_schedule_id', $scheduleIds)
                    ->get()
                    ->keyBy('class_schedule_id');
            }

            return view(self::MEMBER_.'classes.index', compact('schedules', 'membership', 'memberBookings'));
        } catch (Throwable $e) {
            return redirect()->route('member.home')->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'class_schedule_id' => 'required|exists:class_schedules,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $membership = Membership::query()
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->first();

        if (! $membership) {
            return redirect()->back()->with(self::ERROR_, __('You need an active membership to book.'));
        }

        try {
            $this->classBookingRepository->store([
                'class_schedule_id' => (int) $request->input('class_schedule_id'),
                'membership_id' => $membership->id,
                'booked_by_user_id' => Auth::id(),
                'status' => 'confirmed',
            ]);

            return redirect()->route('member.classes.index')->with(self::SUCCESS_, __('Booking confirmed.'));
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, $e->getMessage());
        }
    }

    public function cancel(ClassBooking $classBooking): RedirectResponse
    {
        try {
            if ((int) $classBooking->membership->user_id !== (int) Auth::id()) {
                abort(403);
            }
            if ($classBooking->status === 'cancelled') {
                return redirect()->back()->with(self::ERROR_, __('Already cancelled.'));
            }
            $this->classBookingRepository->cancelBooking($classBooking);

            return redirect()->route('member.classes.index')->with(self::SUCCESS_, __('Booking cancelled.'));
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, $e->getMessage());
        }
    }
}
