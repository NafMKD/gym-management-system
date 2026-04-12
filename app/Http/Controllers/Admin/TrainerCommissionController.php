<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainerCommissionEntry;
use App\Models\User;
use App\Repositories\TrainerCommissionRepository;
use App\Support\DataTables\UserNameSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class TrainerCommissionController extends Controller
{
    public function __construct(
        protected TrainerCommissionRepository $trainerCommissionRepository
    ) {
    }

    /**
     * @return View|RedirectResponse
     */
    public function index(): View|RedirectResponse
    {
        try {
            $trainers = User::query()->where('role', 'trainer')->orderBy('first_name')->orderBy('last_name')->get();

            return view(self::ADMIN_.'trainer_commissions.list', compact('trainers'));
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * @return View|RedirectResponse
     */
    public function create(): View|RedirectResponse
    {
        try {
            $trainers = User::query()->where('role', 'trainer')->orderBy('first_name')->orderBy('last_name')->get();

            return view(self::ADMIN_.'trainer_commissions.add', compact('trainers'));
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'trainer_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'earned_at' => 'required|date',
            'notes' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $trainer = User::find($request->input('trainer_id'));
        if ($trainer->role !== 'trainer') {
            return redirect()->back()->withInput()->with(self::ERROR_, __('Selected user must be a trainer.'));
        }

        try {
            $this->trainerCommissionRepository->storeManual($request->only([
                'trainer_id', 'amount', 'earned_at', 'notes',
            ]));

            return redirect()->route('admin.trainer_commissions.list')->with(self::SUCCESS_, __('Commission entry').self::SUCCESS_STORE);
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, $e->getMessage());
        }
    }

    /**
     * @return JsonResponse
     */
    public function getListData(Request $request): JsonResponse
    {
        $filters = $request->only(['trainer_id', 'start_date', 'end_date']);
        $query = $this->trainerCommissionRepository->getFilteredQuery($filters)->orderByDesc('earned_at');

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('earned_at', fn ($row) => $row->earned_at ? $row->earned_at->format('d/m/Y') : '—')
            ->addColumn('trainer', fn ($row) => e($row->trainer?->getName() ?? '—'))
            ->orderColumn('trainer', false)
            ->editColumn('source', function ($row) {
                return $row->source === TrainerCommissionEntry::SOURCE_SESSION
                    ? '<span class="badge badge-info">'.e(__('Session')).'</span>'
                    : '<span class="badge badge-secondary">'.e(__('Manual')).'</span>';
            })
            ->editColumn('amount', fn ($row) => number_format((float) $row->amount, 2))
            ->addColumn('session', function ($row) {
                $b = $row->classBooking;
                if (! $b) {
                    return '—';
                }
                $name = $b->schedule?->gymClass?->name ?? __('Session');

                return e($name).' #'.$b->id;
            })
            ->orderColumn('session', false)
            ->addColumn('recorded_by', fn ($row) => e($row->recordedBy?->getName() ?? '—'))
            ->orderColumn('recorded_by', false)
            ->filterColumn('trainer', function ($query, $keyword) {
                $query->whereHas('trainer', function ($q) use ($keyword) {
                    UserNameSearch::applyToUserQuery($q, $keyword);
                });
            })
            ->filterColumn('session', function ($query, $keyword) {
                $kw = '%'.UserNameSearch::escapeLike($keyword).'%';
                $query->where(function ($q) use ($kw, $keyword) {
                    $q->whereHas('classBooking.schedule.gymClass', fn ($gc) => $gc->where('name', 'like', $kw))
                        ->orWhereHas('classBooking.schedule', fn ($s) => $s->where('starts_at', 'like', $kw));
                    if (ctype_digit(trim((string) $keyword))) {
                        $q->orWhere('class_booking_id', $keyword);
                    }
                });
            })
            ->filterColumn('recorded_by', function ($query, $keyword) {
                $query->whereHas('recordedBy', function ($q) use ($keyword) {
                    UserNameSearch::applyToUserQuery($q, $keyword);
                });
            })
            ->rawColumns(['source'])
            ->make(true);
    }
}
