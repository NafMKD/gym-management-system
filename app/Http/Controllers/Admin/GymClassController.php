<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GymClass;
use App\Repositories\GymClassRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class GymClassController extends Controller
{
    public function __construct(
        protected GymClassRepository $gymClassRepository
    ) {
    }

    public function index(): View|RedirectResponse
    {
        try {
            return view(self::ADMIN_.'gym_classes.list');
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    public function create(): View|RedirectResponse
    {
        try {
            return view(self::ADMIN_.'gym_classes.add');
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'capacity' => 'required|integer|min:1|max:500',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $attributes = $request->only(['name', 'description', 'capacity', 'duration_minutes', 'is_active']);

        try {
            $this->gymClassRepository->store($attributes);

            return redirect()->route('admin.gym_classes.list')->with(self::SUCCESS_, __('Class').self::SUCCESS_STORE);
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, $e->getMessage());
        }
    }

    public function show(GymClass $gymClass): View|RedirectResponse
    {
        try {
            $gymClass->loadCount('schedules');

            return view(self::ADMIN_.'gym_classes.view', compact('gymClass'));
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    public function edit(GymClass $gymClass): View|RedirectResponse
    {
        try {
            return view(self::ADMIN_.'gym_classes.edit', compact('gymClass'));
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    public function update(Request $request, GymClass $gymClass): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'capacity' => 'required|integer|min:1|max:500',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $attributes = $request->only(['name', 'description', 'capacity', 'duration_minutes', 'is_active']);

        try {
            $this->gymClassRepository->update($gymClass, $attributes);

            return redirect()->route('admin.gym_classes.view', $gymClass)->with(self::SUCCESS_, self::SUCCESS_UPDATE);
        } catch (\App\Exceptions\NoUpdateNeededException $e) {
            return redirect()->back()->withInput()->with(self::SUCCESS_, self::SUCCESS_NO_UPDATE);
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, $e->getMessage());
        }
    }

    public function destroy(GymClass $gymClass): RedirectResponse
    {
        try {
            $this->gymClassRepository->destroy($gymClass);

            return redirect()->route('admin.gym_classes.list')->with(self::SUCCESS_, self::SUCCESS_DELETE);
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, $e->getMessage());
        }
    }

    public function getListData(): JsonResponse
    {
        $query = GymClass::query();

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('name', fn ($row) => e($row->name))
            ->editColumn('capacity', fn ($row) => $row->capacity)
            ->editColumn('duration_minutes', fn ($row) => $row->duration_minutes)
            ->editColumn('is_active', function ($row) {
                return $row->is_active
                    ? '<span class="badge badge-success">'.__('Yes').'</span>'
                    : '<span class="badge badge-secondary">'.__('No').'</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <a href="'.route('admin.gym_classes.view', $row->id).'" class="btn btn-info btn-xs btn-flat">
                        <i class="fas fa-eye"></i> '.__('View').'
                    </a>
                    <a href="'.route('admin.gym_classes.edit', $row->id).'" class="btn btn-primary btn-xs btn-flat">
                        <i class="fas fa-edit"></i> '.__('Edit').'
                    </a>
                    <a href="'.route('admin.gym_classes.delete', $row->id).'"
                        onclick="if(!confirm(\''.__('Are you sure?').'\')){event.preventDefault()}"
                        class="btn btn-danger btn-xs btn-flat">
                        <i class="fas fa-trash"></i> '.__('Delete').'
                    </a>
                ';
            })
            ->rawColumns(['action', 'is_active'])
            ->make(true);
    }
}
