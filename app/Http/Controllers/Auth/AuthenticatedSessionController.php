<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
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

        $request->validate([
            'phone' => ['required', 'string', 'regex:'.PhoneNumber::REGEX_VALIDATION],
            'password' => ['required'],
        ]);

        $credentials = [
            'phone' => $normalizedPhone,
            'password' => $request->string('password')->toString(),
        ];

        if (Auth::attempt($credentials)) {
            $role = Auth::user()->role;

            if (! in_array($role, ['admin', 'trainer', 'reception', 'member'], true)) {
                Auth::logout();

                return redirect()->back()->withInput()->with('error', 'Invalid Role Type, please contact admin!');
            }

            return redirect()->route('dashboard');
        }

        // login failed
        return redirect()->back()->withInput()->with('error', 'Invalid Credentials!');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
