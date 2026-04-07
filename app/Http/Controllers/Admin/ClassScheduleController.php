<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use App\Models\GymClass;
use App\Models\User;
use App\Repositories\ClassScheduleRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class ClassScheduleController extends Controller
{
    public function __construct(
        protected ClassScheduleRepository $classScheduleRepository
    ) {
    }

    public function index(): View|RedirectResponse
    {
        try {
            return view(self::ADMIN_.'class_schedules.list');
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    public function create(): View|RedirectResponse
    {
        try {
            $gymClasses = GymClass::query()->where('is_active', true)->orderBy('name')->get();
            $trainers = User::query()->where('role', 'trainer')->orderBy('first_name')->orderBy('last_name')->get();

            return view(self::ADMIN_.'class_schedules.add', compact('gymClasses', 'trainers'));
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'gym_class_id' => 'required|exists:gym_classes,id',
            'trainer_id' => 'required|exists:users,id',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'capacity_override' => 'nullable|integer|min:1|max:500',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $this->classScheduleRepository->store($request->only([
                'gym_class_id', 'trainer_id', 'starts_at', 'ends_at', 'capacity_override', 'notes',
            ]));

            return redirect()->route('admin.class_schedules.list')->with(self::SUCCESS_, __('Schedule').self::SUCCESS_STORE);
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, $e->getMessage());
        }
    }

    public function show(ClassSchedule $classSchedule): View|RedirectResponse
    {
        try {
            $classSchedule->load(['gymClass', 'trainer', 'bookings' => fn ($q) => $q->orderBy('id'), 'bookings.membership.user']);

            return view(self::ADMIN_.'class_schedules.view', compact('classSchedule'));
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    public function edit(ClassSchedule $classSchedule): View|RedirectResponse
    {
        try {
            $gymClasses = GymClass::query()->orderBy('name')->get();
            $trainers = User::query()->where('role', 'trainer')->orderBy('first_name')->orderBy('last_name')->get();

            return view(self::ADMIN_.'class_schedules.edit', compact('classSchedule', 'gymClasses', 'trainers'));
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    public function update(Request $request, ClassSchedule $classSchedule): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'gym_class_id' => 'required|exists:gym_classes,id',
            'trainer_id' => 'required|exists:users,id',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'capacity_override' => 'nullable|integer|min:1|max:500',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $this->classScheduleRepository->update($classSchedule, $request->only([
                'gym_class_id', 'trainer_id', 'starts_at', 'ends_at', 'capacity_override', 'notes',
            ]));

            return redirect()->route('admin.class_schedules.view', $classSchedule)->with(self::SUCCESS_, self::SUCCESS_UPDATE);
        } catch (\App\Exceptions\NoUpdateNeededException $e) {
            return redirect()->back()->withInput()->with(self::SUCCESS_, self::SUCCESS_NO_UPDATE);
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, $e->getMessage());
        }
    }

    public function destroy(ClassSchedule $classSchedule): RedirectResponse
    {
        try {
            $this->classScheduleRepository->destroy($classSchedule);

            return redirect()->route('admin.class_schedules.list')->with(self::SUCCESS_, self::SUCCESS_DELETE);
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, $e->getMessage());
        }
    }

    public function getListData(): JsonResponse
    {
        $query = ClassSchedule::query()->with(['gymClass', 'trainer']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('class_name', fn ($row) => e($row->gymClass?->name ?? '—'))
            ->orderColumn('class_name', false)
            ->addColumn('trainer_name', fn ($row) => e($row->trainer?->getName() ?? '—'))
            ->orderColumn('trainer_name', false)
            ->editColumn('starts_at', fn ($row) => Carbon::parse($row->starts_at)->format('d/m/Y H:i'))
            ->editColumn('ends_at', fn ($row) => Carbon::parse($row->ends_at)->format('d/m/Y H:i'))
            ->addColumn('action', function ($row) {
                return '
                    <a href="'.route('admin.class_schedules.view', $row->id).'" class="btn btn-info btn-xs btn-flat">
                        <i class="fas fa-eye"></i> '.__('View').'
                    </a>
                    <a href="'.route('admin.class_schedules.edit', $row->id).'" class="btn btn-primary btn-xs btn-flat">
                        <i class="fas fa-edit"></i> '.__('Edit').'
                    </a>
                    <a href="'.route('admin.class_schedules.delete', $row->id).'"
                        onclick="if(!confirm(\''.__('Are you sure?').'\')){event.preventDefault()}"
                        class="btn btn-danger btn-xs btn-flat">
                        <i class="fas fa-trash"></i> '.__('Delete').'
                    </a>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
