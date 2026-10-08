<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Users created (or reset) by an administrator get a temporary password and
 * must choose their own before using the rest of the app.
 */
class EnsurePasswordIsChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->must_change_password) {
            return $next($request);
        }

        if ($request->routeIs('account.password.*', 'logout')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'You must change your temporary password before using the API.');
        }

        return redirect()->route('account.password.edit')
            ->with('status', 'Please choose a new password to continue.');
    }
}
