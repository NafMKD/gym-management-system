<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Models\User;
use App\Support\PhoneNumber;
use App\Exceptions\NoUpdateNeededException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class StaffController extends Controller
{
    /** Roles that appear in staff management (non-member). */
    public const STAFF_ROLES = ['admin', 'trainer', 'reception'];

    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function index(): View|RedirectResponse
    {
        try {
            $base = User::query()->whereIn('role', self::STAFF_ROLES);
            $staffCounts = [
                'all' => (clone $base)->count(),
                'admin' => User::where('role', 'admin')->count(),
                'trainer' => User::where('role', 'trainer')->count(),
                'reception' => User::where('role', 'reception')->count(),
            ];

            return view(self::ADMIN_.'staffs.list', compact('staffCounts'));
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    public function create(): View|RedirectResponse
    {
        try {
            return view(self::ADMIN_.'staffs.add');
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $normalizedPhone = PhoneNumber::normalize($request->string('phone')->toString());
        if ($normalizedPhone === null || ! PhoneNumber::isValid($normalizedPhone)) {
            return redirect()->back()
                ->withErrors(['phone' => __('Enter a valid mobile number (07 or 09 plus 8 digits).')])
                ->withInput();
        }
        $request->merge(['phone' => $normalizedPhone]);

        $validator = Validator::make($request->all(), [
            'email' => PhoneNumber::optionalEmailRules(),
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'regex:'.PhoneNumber::REGEX_VALIDATION, Rule::unique('users', 'phone')],
            'gender' => 'required|in:Female,Male',
            'role' => 'required|in:admin,trainer,reception',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $attributes = $request->only(['first_name', 'last_name', 'email', 'phone', 'gender', 'role']);
        $attributes['password'] = $request->input('password', '12345678');
        if (($attributes['email'] ?? '') === '') {
            $attributes['email'] = null;
        }
        try {
            $this->userRepository->store($attributes);
            return redirect()->route('admin.staffs.add')->with(self::SUCCESS_, 'Staff'.self::SUCCESS_STORE);
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     * @return View|RedirectResponse
     */
    public function show(User $user): View|RedirectResponse
    {
        try {
            return view(self::ADMIN_.'staffs.view', compact('user'));
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    public function edit(User $user): View|RedirectResponse
    {
        try {
            return view(self::ADMIN_.'staffs.edit', compact('user'));
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $normalizedPhone = PhoneNumber::normalize($request->string('phone')->toString());
        if ($normalizedPhone === null || ! PhoneNumber::isValid($normalizedPhone)) {
            return redirect()->back()
                ->withErrors(['phone' => __('Enter a valid mobile number (07 or 09 plus 8 digits).')])
                ->withInput();
        }
        $request->merge(['phone' => $normalizedPhone]);

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => PhoneNumber::optionalEmailRules($user->id),
            'password' => 'sometimes|string|min:8',
            'phone' => ['required', 'string', 'regex:'.PhoneNumber::REGEX_VALIDATION, Rule::unique('users', 'phone')->ignore($user->id)],
            'role' => 'required|in:admin,trainer,reception',
            'gender' => 'sometimes|in:Female,Male',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $attributes = $request->only(['first_name', 'last_name', 'email', 'password', 'phone', 'role', 'gender']);
        if (array_key_exists('email', $attributes) && $attributes['email'] === '') {
            $attributes['email'] = null;
        }

        try {
            $this->userRepository->update($user, $attributes);
            return redirect()->back()->withInput()->with(self::SUCCESS_, 'Staff'.self::SUCCESS_UPDATE);
        } catch (NoUpdateNeededException $e) {
            return redirect()->back()->withInput()->with(self::SUCCESS_, self::SUCCESS_NO_UPDATE);
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    public function destroy(User $user): RedirectResponse
    {
        try {
            $this->userRepository->destroy($user);
            return redirect()->back()->withInput()->with(self::SUCCESS_, self::SUCCESS_DELETE);
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Retrieves staff data from the database.
     *
     * @return JsonResponse
     */
    public function getStaffData(Request $request): JsonResponse
    {
        $roleFilter = $request->input('role_filter');
        if (! is_string($roleFilter) || ! in_array($roleFilter, self::STAFF_ROLES, true)) {
            $roleFilter = null;
        }

        $query = User::query()->whereIn('role', self::STAFF_ROLES);
        if ($roleFilter !== null) {
            $query->where('role', $roleFilter);
        }

        return DataTables::of($query)
            ->editColumn('name', function ($row) {
                return $row?->getName() ?? 'N/A';
            })
            ->editColumn('role', function ($row) {
                return $row->role ? __(ucfirst($row->role)) : '—';
            })
            ->editColumn('email', function ($row) {
                return $row->email ?: '—';
            })
            ->editColumn('phone', function ($row) {
                return $row->phone;
            })
            ->addColumn('action', function ($row) {
                return '
                    <a href="' . route('admin.staffs.view', $row->id) . '" class="btn btn-info btn-xs btn-flat">
                        <i class="fas fa-eye"></i> View
                    </a>
                    <a href="' . route('admin.staffs.edit', $row->id) . '" class="btn btn-primary btn-xs btn-flat">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="' . route('admin.staffs.delete', $row->id) . '" 
                        onclick="if(confirm(\'Are you sure you want to delete ' . $row->getName() . '?\') == false){event.preventDefault()}" 
                        class="btn btn-danger btn-xs btn-flat">
                        <i class="fas fa-trash"></i> Delete
                    </a>
                ';
            })
            ->filterColumn('name', function ($query, $keyword) {
                $query->whereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%{$keyword}%"]);
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
