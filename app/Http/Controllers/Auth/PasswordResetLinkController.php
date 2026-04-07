<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.passwords.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
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

        $request->validate([
            'phone' => ['required', 'string', 'regex:'.PhoneNumber::REGEX_VALIDATION],
        ]);

        $user = User::query()->where('phone', $normalizedPhone)->first();

        if ($user === null) {
            return back()
                ->withInput($request->only('phone'))
                ->with('status', __('If a matching account was found, we have emailed the password reset link.'));
        }

        if ($user->email === null || trim((string) $user->email) === '') {
            return back()
                ->withInput($request->only('phone'))
                ->withErrors(['phone' => __('This account has no email on file. Please contact the gym administrator to reset your password.')]);
        }

        $status = Password::sendResetLink(
            ['email' => $user->email]
        );

        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('phone'))
                ->withErrors(['phone' => __($status)]);
    }
}
