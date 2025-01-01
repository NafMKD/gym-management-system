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
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param string $user_type
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $user_type): Response|RedirectResponse
    {
        if (Auth::user()->role === $user_type) {
            return $next($request);
        }

        return redirect()->route(Auth::user()->role.'.home');
    }
}
