<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = auth()->user();

        if (! $user) {
            return $next($request);
        }

        $subscription = $user->subscription ?? $user->subscription()->create(['status' => 'trial']);

        $request->attributes->set('subscription', $subscription);

        if ($subscription->isExpired() && ! $request->isMethod('GET')) {
            return response()->json([
                'message' => 'Sua assinatura expirou. Renove o plano para continuar.',
                'code'    => 'subscription_expired',
            ], 403);
        }

        return $next($request);
    }
}
