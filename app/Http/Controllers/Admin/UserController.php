<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Models\User;
use App\Exceptions\NoUpdateNeededException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
            $users = User::paginate(10);
            return view(self::ADMIN_.'users.list', compact('users'));
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
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            // 'email' => 'required|string|email|max:255|unique:users',
            'password' => 'nullable|string|min:8',
            'phone' => 'required|numeric|digits:10',
            'role' => 'nullable|in:admin,trainer,reception,member',
            'gender' => 'required|in:Female,Male',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $id = User::latest('id')->first();

        
        $attributes = $request->only(['first_name', 'last_name', 'email', 'phone', 'gender']);
        $attributes['role'] = $request->input('role', 'member');
        $attributes['password'] = $request->input('password', '12345678');
        $attributes['email'] = 'admin'. $id->id + 1 .'@gmail.com';

        try {
            $this->userRepository->store($attributes);
            return redirect()->route('admin.memberships.add')->with(self::SUCCESS_, 'User'.self::SUCCESS_STORE);
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
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8',
            'phone' => 'required|numeric|digits:10',
            'role' => 'sometimes|in:admin,trainer,reception,member',
            'gender' => 'sometimes|in:Female,Male',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $attributes = $request->only(['first_name', 'last_name', 'email', 'password', 'phone', 'role', 'gender']);

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
     * 
     */
    public function getUsersData()
    {
        $query = User::query(); 

        return DataTables::of($query)
            ->addIndexColumn() 
            ->editColumn('name', function ($row) {
                return $row->getName();
            })
            ->editColumn('email', function ($row) {
                return $row->email;
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
