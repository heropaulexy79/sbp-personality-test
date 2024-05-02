<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganisationHasActiveSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $org = $user->organisation;

        if ($org && $user->account_type === 'ORG' && !$org->hasActiveSubscription()) {
            abort('401', "You don't have an active subscription");
        }

        return $next($request);
    }
}
