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

class UserController extends Controller
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return View|RedirectResponse
     */
    public function index(): View|RedirectResponse
    {
        try {
            $users = User::all();
            return view('users.index', ['users' => $users]);
        } catch (Throwable $e) {
            return redirect()->route('home')->with('error', 'Failed to load users.');
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
            return view('users.create');
        } catch (Throwable $e) {
            return redirect()->route('users.index')->with('error', 'Failed to load creation form.');
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|numeric',
            'role' => 'required|in:admin,trainer,reception,member',
            'gender' => 'required|in:Female,Male',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $attributes = $request->only(['name', 'email', 'password', 'phone', 'role', 'gender']);

        try {
            $this->userRepository->store($attributes);
            return redirect()->route('users.index')->with('success', 'User created successfully.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Failed to create user.')->withInput();
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
            return view('users.show', ['user' => $user]);
        } catch (Throwable $e) {
            return redirect()->route('users.index')->with('error', 'Failed to load user details.');
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
            return view('users.edit', ['user' => $user]);
        } catch (Throwable $e) {
            return redirect()->route('users.index')->with('error', 'Failed to load edit form.');
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
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8',
            'phone' => 'nullable|numeric',
            'role' => 'sometimes|in:admin,trainer,reception,member',
            'gender' => 'sometimes|in:Female,Male',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $attributes = $request->only(['name', 'email', 'password', 'phone', 'role', 'gender']);

        try {
            $this->userRepository->update($user, $attributes);
            return redirect()->route('users.index')->with('success', 'User updated successfully.');
        } catch (NoUpdateNeededException $e) {
            return redirect()->back()->with('warning', $e->getMessage())->withInput();
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Failed to update user.')->withInput();
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
            return redirect()->route('users.index')->with('success', 'User deleted successfully.');
        } catch (Throwable $e) {
            return redirect()->route('users.index')->with('error', 'Failed to delete user.');
        }
    }
}
