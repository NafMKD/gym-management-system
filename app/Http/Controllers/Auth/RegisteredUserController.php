<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Support\PhoneNumber;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(
        protected UserRepository $userRepository
    ) {
    }

    /**
     * Display the registration view.
     *
     * @return View
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
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

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => PhoneNumber::optionalEmailRules(),
            'phone' => ['required', 'string', 'regex:'.PhoneNumber::REGEX_VALIDATION, Rule::unique('users', 'phone')],
            'gender' => ['required', 'in:Female,Male'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $this->userRepository->store([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'],
            'gender' => $validated['gender'],
            'role' => 'member',
            'password' => $validated['password'],
        ]);

        $user = User::where('phone', $validated['phone'])->firstOrFail();

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
