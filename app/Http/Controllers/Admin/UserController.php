<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Models\User;
use App\Exceptions\NoUpdateNeededException;
use App\Support\PhoneNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function __construct(
        protected UserRepository $userRepository
        )
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return View|RedirectResponse
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
     *
     * @return View|RedirectResponse
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
     *
     * @param Request $request
     * @return RedirectResponse
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
            'role' => 'nullable|in:admin,trainer,reception,member',
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
     *
     * @param User $user
     * @return View|RedirectResponse
     */
    public function show(User $user): View|RedirectResponse
    {
        try {
            return view(self::ADMIN_.'users.view', compact('user'));
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param User $user
     * @return View|RedirectResponse
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
     *
     * @param Request $request
     * @param User $user
     * @return RedirectResponse
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
            'role' => 'sometimes|in:admin,trainer,reception,member',
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
     *
     * @param User $user
     * @return RedirectResponse
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
     * Retrieves user data from the database.
     *
     * @return JsonResponse
     */
    public function getUsersData(): JsonResponse
    {
        $query = User::where('role', 'member'); 

        return DataTables::of($query)
            ->addIndexColumn() 
            ->editColumn('name', function ($row) {
                return $row?->getName() ?? 'N/A';
            })
            ->editColumn('email', function ($row) {
                return $row->email ?: '—';
            })
            ->editColumn('phone', function ($row) {
                return $row->phone; 
            })
            ->addColumn('action', function ($row) {
                return '
                    <a href="' . route('admin.users.view', $row->id) . '" class="btn btn-info btn-xs btn-flat">
                        <i class="fas fa-eye"></i> View
                    </a>
                    <a href="' . route('admin.users.edit', $row->id) . '" class="btn btn-primary btn-xs btn-flat">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="' . route('admin.users.delete', $row->id) . '" 
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
