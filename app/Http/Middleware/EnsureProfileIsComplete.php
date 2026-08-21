<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileIsComplete
{
    /**
     * A user created via the unified login flow has no name until they
     * complete onboarding. Everything else in the app assumes a name
     * exists (directory listings, nav, letter headers) — so we park
     * them on the onboarding form until it's set.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->name === null
            && ! $request->routeIs('onboarding.*')
            && ! $request->routeIs('logout')) {
            return redirect()->route('onboarding.name');
        }

        return $next($request);
    }
}
