<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ValidToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!Auth::guard('api')->check()){
            return response()->json(['message' => 'Usuário não autenticado'], 401);
        }

        if (!$token) {
            return response()->json(['message' => 'Token Inválido'], 401);
        }

        $this->user = Auth::guard('api')->user();
        return $next($request);
    }
}
