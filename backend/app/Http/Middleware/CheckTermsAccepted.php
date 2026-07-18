<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckTermsAccepted
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = auth()->user();

        if (! $user) {
            return $next($request);
        }

        if ($user->terms_version !== config('terms.version')) {
            return response()->json([
                'message' => 'É necessário aceitar os novos Termos de Uso para continuar.',
                'code'    => 'terms_reacceptance_required',
            ], 403);
        }

        return $next($request);
    }
}
