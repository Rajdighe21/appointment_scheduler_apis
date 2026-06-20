<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;

class ApiTokenMiddleware
{
  public function handle(Request $request, Closure $next)
{
    $token = $request->bearerToken();

    if (!$token) {
        return response()->json([
            'status' => false,
            'message' => 'Token not provided'
        ], 401);
    }

    $user = User::where('api_token', hash('sha256', $token))->first();

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid Token'
        ], 401);
    }

    auth()->login($user);

    return $next($request);
}
}
