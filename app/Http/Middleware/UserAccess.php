<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\RedirectResponse;

class UserAccess
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  One or more allowed roles (e.g. admin, reception).
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response|RedirectResponse
    {
        $userRole = Auth::user()->role;

        foreach ($roles as $role) {
            if ($userRole === $role) {
                return $next($request);
            }
        }

        return redirect()->route($userRole.'.home')
            ->with('error', __('You do not have permission to access this page!'));
    }
}
