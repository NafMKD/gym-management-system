<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $input = $request->all(['email', 'password']);
        
        if (Auth::attempt($input)) {
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
