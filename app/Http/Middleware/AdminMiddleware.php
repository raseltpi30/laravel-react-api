<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        // ✅ Check if user is authenticated and has the admin role
        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        return $next($request);
    }
}
