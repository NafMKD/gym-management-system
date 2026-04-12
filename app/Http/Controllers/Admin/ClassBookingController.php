<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassBooking;
use App\Models\ClassSchedule;
use App\Models\Membership;
use App\Models\TrainerSessionFeedback;
use App\Repositories\ClassBookingRepository;
use App\Support\DataTables\UserNameSearch;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class ClassBookingController extends Controller
{
    public function __construct(
        protected ClassBookingRepository $classBookingRepository
    ) {
    }

    public function index(): View|RedirectResponse
    {
        try {
            return view(self::ADMIN_.'class_bookings.list');
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    public function create(Request $request): View|RedirectResponse
    {
        try {
            $schedules = ClassSchedule::query()
                ->with('gymClass')
                ->where('ends_at', '>', Carbon::now())
                ->orderBy('starts_at')
                ->get();
            $memberships = Membership::query()
                ->with('user')
                ->where('status', 'active')
                ->orderBy('id', 'desc')
                ->limit(500)
                ->get();

            $selectedScheduleId = $request->query('class_schedule_id');

            return view(self::ADMIN_.'class_bookings.add', compact('schedules', 'memberships', 'selectedScheduleId'));
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'class_schedule_id' => 'required|exists:class_schedules,id',
            'membership_id' => 'required|exists:memberships,id',
            'status' => 'nullable|in:pending,confirmed,cancelled,attended',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $attributes = [
            'class_schedule_id' => $request->input('class_schedule_id'),
            'membership_id' => $request->input('membership_id'),
            'status' => $request->input('status', 'confirmed'),
            'booked_by_user_id' => auth()->id(),
        ];

        try {
            $this->classBookingRepository->store($attributes);

            return redirect()->route('admin.class_bookings.list')->with(self::SUCCESS_, __('Booking').self::SUCCESS_STORE);
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, $e->getMessage());
        }
    }

    public function show(ClassBooking $classBooking): View|RedirectResponse
    {
        try {
            $classBooking->load([
                'schedule.gymClass',
                'schedule.trainer',
                'membership.user',
                'bookedBy',
                'trainerSessionFeedback',
                'trainerCommissionEntry',
            ]);

            return view(self::ADMIN_.'class_bookings.view', compact('classBooking'));
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    public function cancel(ClassBooking $classBooking): RedirectResponse
    {
        try {
            if ($classBooking->status === 'cancelled') {
                return redirect()->back()->with(self::ERROR_, __('Already cancelled.'));
            }
            $this->classBookingRepository->cancelBooking($classBooking);

            return redirect()->back()->with(self::SUCCESS_, __('Booking cancelled.'));
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, $e->getMessage());
        }
    }

    /**
     * Mark booking as attended (triggers session commission when trainer profile rate &gt; 0).
     *
     * @return RedirectResponse
     */
    public function markAttended(ClassBooking $classBooking): RedirectResponse
    {
        try {
            if ($classBooking->status === 'cancelled') {
                return redirect()->back()->with(self::ERROR_, __('Cancelled bookings cannot be marked attended.'));
            }
            if ($classBooking->status === 'attended') {
                return redirect()->back()->with(self::ERROR_, __('Already marked as attended.'));
            }

            $this->classBookingRepository->update($classBooking, ['status' => 'attended']);

            return redirect()->back()->with(self::SUCCESS_, __('Marked as attended.'));
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, $e->getMessage());
        }
    }

    /**
     * Optional member feedback for this session (Phase 8.4).
     *
     * @return RedirectResponse
     */
    public function storeFeedback(Request $request, ClassBooking $classBooking): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'nullable|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            TrainerSessionFeedback::updateOrCreate(
                ['class_booking_id' => $classBooking->id],
                [
                    'rating' => $request->input('rating'),
                    'comment' => $request->input('comment'),
                ]
            );

            return redirect()->back()->with(self::SUCCESS_, __('Feedback saved.'));
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, $e->getMessage());
        }
    }

    public function getListData(): JsonResponse
    {
        $query = ClassBooking::query()->with(['schedule.gymClass', 'membership.user']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('session', function ($row) {
                $s = $row->schedule;

                return $s ? e($s->gymClass?->name).' · '.Carbon::parse($s->starts_at)->format('d/m/Y H:i') : '—';
            })
            ->orderColumn('session', false)
            ->addColumn('member', fn ($row) => e($row->membership?->user?->getName() ?? '—'))
            ->orderColumn('member', false)
            ->editColumn('status', function ($row) {
                $map = [
                    'pending' => 'warning',
                    'confirmed' => 'success',
                    'cancelled' => 'secondary',
                    'attended' => 'info',
                ];
                $c = $map[$row->status] ?? 'light';

                return '<span class="badge badge-'.$c.'">'.e(ucfirst($row->status)).'</span>';
            })
            ->addColumn('action', function ($row) {
                $html = '<a href="'.route('admin.class_bookings.view', $row->id).'" class="btn btn-info btn-xs btn-flat"><i class="fas fa-eye"></i> '.__('View').'</a> ';
                if ($row->status !== 'cancelled') {
                    $html .= '<form action="'.route('admin.class_bookings.cancel', $row->id).'" method="post" class="d-inline" onsubmit="return confirm(\''.__('Cancel this booking?').'\')">'
                        .csrf_field()
                        .'<button type="submit" class="btn btn-warning btn-xs btn-flat">'.__('Cancel').'</button></form>';
                }

                return $html;
            })
            ->filterColumn('member', function ($query, $keyword) {
                $query->whereHas('membership.user', function ($q) use ($keyword) {
                    UserNameSearch::applyToUserQuery($q, $keyword);
                });
            })
            ->filterColumn('session', function ($query, $keyword) {
                $kw = '%'.UserNameSearch::escapeLike($keyword).'%';
                $query->where(function ($q) use ($kw, $keyword) {
                    $q->whereHas('schedule.gymClass', fn ($gc) => $gc->where('name', 'like', $kw))
                        ->orWhereHas('schedule', fn ($s) => $s->where('starts_at', 'like', $kw));
                    if (ctype_digit(trim((string) $keyword))) {
                        $q->orWhere('id', $keyword);
                    }
                });
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }
}
