<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\NoUpdateNeededException;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Membership;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Support\DataTables\UserNameSearch;
use App\Support\PhoneNumber;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function __construct(
        protected UserRepository $userRepository
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View|RedirectResponse
    {
        try {
            return view(self::ADMIN_.'users.list');
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View|RedirectResponse
    {
        try {
            return view(self::ADMIN_.'users.add');
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => PhoneNumber::optionalEmailRules(),
            'password' => 'nullable|string|min:8',
            'phone' => ['required', 'string', 'regex:'.PhoneNumber::REGEX_VALIDATION, Rule::unique('users', 'phone')],
            'role' => 'nullable|in:admin,accountant,trainer,reception,member',
            'gender' => 'required|in:Female,Male',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $attributes = $request->only(['first_name', 'last_name', 'email', 'phone', 'gender']);
        $attributes['role'] = $request->input('role', 'member');
        $attributes['password'] = $request->input('password', '12345678');
        if (($attributes['email'] ?? '') === '') {
            $attributes['email'] = null;
        }

        try {
            $this->userRepository->store($attributes);

            return redirect()->route('admin.memberships.add')->with(self::SUCCESS_, 'User'.self::SUCCESS_STORE);
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): View|RedirectResponse
    {
        try {
            $showHistory = Auth::user()?->role !== 'accountant';
            $membershipOptions = collect();

            if ($showHistory) {
                $membershipOptions = $user->memberships()
                    ->with('package:id,name')
                    ->orderByDesc('start_date')
                    ->orderByDesc('id')
                    ->get(['id', 'user_id', 'package_id', 'start_date', 'end_date', 'status']);
            }

            return view(self::ADMIN_.'users.view', compact('user', 'membershipOptions', 'showHistory'));
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Membership history for a single user.
     */
    public function getMembershipHistoryData(User $user): JsonResponse
    {
        $query = Membership::query()
            ->with('package:id,name')
            ->where('user_id', $user->id)
            ->orderByDesc('start_date')
            ->orderByDesc('id');

        return DataTables::of($query)
            ->editColumn('id', function (Membership $membership) {
                return '<a href="' . route('admin.memberships.view', $membership) . '" class="font-weight-bold">#' . $membership->id . '</a>';
            })
            ->addColumn('package_name', function (Membership $membership) {
                return $membership->package?->name ?? __('Custom');
            })
            ->editColumn('price', function (Membership $membership) {
                return number_format((float) $membership->price, 2);
            })
            ->editColumn('status', function (Membership $membership) {
                return $this->membershipStatusBadge((string) $membership->status);
            })
            ->editColumn('created_at', function (Membership $membership) {
                return $membership->created_detail;
            })
            ->editColumn('updated_at', function (Membership $membership) {
                return $membership->updated_detail;
            })
            ->rawColumns(['id', 'status'])
            ->make(true);
    }

    /**
     * Attendance history for a single user, optionally filtered by membership.
     */
    public function getAttendanceHistoryData(Request $request, User $user): JsonResponse
    {
        $membershipId = (int) $request->input('membership_id');

        $query = Attendance::query()
            ->with(['membership.package:id,name'])
            ->whereHas('membership', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->when($membershipId > 0, function ($query) use ($membershipId) {
                $query->where('membership_id', $membershipId);
            })
            ->orderByDesc('entry_date')
            ->orderByDesc('id');

        return DataTables::of($query)
            ->editColumn('id', function (Attendance $attendance) {
                return '<span class="font-weight-bold">#' . $attendance->id . '</span>';
            })
            ->editColumn('membership_id', function (Attendance $attendance) {
                return '<a href="' . route('admin.memberships.view', $attendance->membership_id) . '" class="font-weight-bold">#' . $attendance->membership_id . '</a>';
            })
            ->addColumn('package_name', function (Attendance $attendance) {
                return $attendance->membership?->package?->name ?? __('Custom');
            })
            ->editColumn('entry_date', function (Attendance $attendance) {
                return Carbon::parse($attendance->entry_date)->setTimezone('Africa/Addis_Ababa')->format('d/m/Y H:i');
            })
            ->addColumn('membership_status', function (Attendance $attendance) {
                return $this->membershipStatusBadge((string) ($attendance->membership?->status ?? 'inactive'));
            })
            ->addColumn('record_status', function () {
                return '<span class="badge badge-success">' . __('Recorded') . '</span>';
            })
            ->rawColumns(['id', 'membership_id', 'membership_status', 'record_status'])
            ->make(true);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View|RedirectResponse
    {
        try {
            return view(self::ADMIN_.'users.edit', compact('user'));
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Update the specified resource in storage.
     */
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
            'role' => 'sometimes|in:admin,accountant,trainer,reception,member',
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

            return redirect()->back()->withInput()->with(self::SUCCESS_, self::SUCCESS_UPDATE);
        } catch (NoUpdateNeededException $e) {
            return redirect()->back()->withInput()->with(self::SUCCESS_, self::SUCCESS_NO_UPDATE);
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
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
     * Retrieves filtered users data for DataTables.
     */
    public function getUsersData(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'created_from' => 'nullable|date',
            'created_to' => 'nullable|date|after_or_equal:created_from',
            'search' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('Invalid input values.'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $filters = $validator->validated();
        $query = $this->userRepository->getFilteredMembersQuery($filters);
        $canManageUsers = Auth::user()?->role !== 'accountant';

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('name', function (User $row) {
                return $row->getName() ?? 'N/A';
            })
            ->editColumn('email', function (User $row) {
                return $row->email ?: '—';
            })
            ->editColumn('phone', function (User $row) {
                return $row->phone;
            })
            ->editColumn('created_at', function (User $row) {
                return $row->created_detail;
            })
            ->addColumn('action', function (User $row) use ($canManageUsers) {
                $action = '
                    <a href="' . route('admin.users.view', $row->id) . '" class="btn btn-info btn-xs btn-flat">
                        <i class="fas fa-eye"></i> View
                    </a>
                ';

                if (! $canManageUsers) {
                    return $action;
                }

                return $action . '
                    <a href="' . route('admin.users.edit', $row->id) . '" class="btn btn-primary btn-xs btn-flat">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="' . route('admin.users.delete', $row->id) . '"
                        onclick="if(confirm(\'Are you sure you want to delete ' . e($row->getName()) . '?\') == false){event.preventDefault()}"
                        class="btn btn-danger btn-xs btn-flat">
                        <i class="fas fa-trash"></i> Delete
                    </a>
                ';
            })
            ->filterColumn('name', function ($query, $keyword) {
                UserNameSearch::applyToUserQuery($query, $keyword);
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Export filtered member users as CSV.
     */
    public function exportUsersCsv(Request $request): StreamedResponse|RedirectResponse
    {
        $filters = $request->validate([
            'created_from' => 'nullable|date',
            'created_to' => 'nullable|date|after_or_equal:created_from',
            'search' => 'nullable|string|max:100',
        ]);

        try {
            $filename = 'users-report-'.now()->format('Y-m-d-His').'.csv';
            $query = $this->userRepository
                ->getFilteredMembersQuery($filters)
                ->orderByDesc('created_at')
                ->orderByDesc('id');

            return response()->streamDownload(function () use ($query) {
                $out = fopen('php://output', 'w');
                fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($out, [
                    'ID',
                    __('Full name'),
                    __('Email'),
                    __('Phone'),
                    __('Created at'),
                ]);

                $query->chunk(200, function ($chunk) use ($out) {
                    foreach ($chunk as $user) {
                        fputcsv($out, [
                            $user->id,
                            $user->getName(),
                            $user->email ?? '',
                            $user->phone ?? '',
                            Carbon::parse($user->created_at)->setTimezone('Africa/Addis_Ababa')->format('Y-m-d H:i:s'),
                        ]);
                    }
                });
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        } catch (Throwable $e) {
            return redirect()->route('admin.users.list')->with(self::ERROR_, $e->getMessage());
        }
    }

    /**
     * Print-friendly filtered member users report.
     */
    public function printUsersReport(Request $request): View|RedirectResponse
    {
        $filters = $request->validate([
            'created_from' => 'nullable|date',
            'created_to' => 'nullable|date|after_or_equal:created_from',
            'search' => 'nullable|string|max:100',
        ]);

        try {
            $users = $this->userRepository
                ->getFilteredMembersQuery($filters)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get();

            return view(self::ADMIN_.'users.print', [
                'users' => $users,
                'filters' => $filters,
                'reportGeneratedAt' => now('Africa/Addis_Ababa'),
            ]);
        } catch (Throwable $e) {
            return redirect()->route('admin.users.list')->with(self::ERROR_, $e->getMessage());
        }
    }

    private function membershipStatusBadge(string $status): string
    {
        $badgeClass = match ($status) {
            'active' => 'badge-success',
            'inactive' => 'badge-warning',
            'cancelled' => 'badge-danger',
            default => 'badge-secondary',
        };

        return '<span class="badge ' . $badgeClass . '">' . ucfirst($status) . '</span>';
    }
}
