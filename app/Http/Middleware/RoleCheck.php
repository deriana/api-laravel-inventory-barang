<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        // Ambil user yang sedang login
        $currentUser = Auth::user();

        // Pastikan pengguna sudah login
        if (!$currentUser) {
            return response()->json(['error' => 'Unauthorized. Please log in first.'], 401);
        }

        // Cek role yang diberikan melalui parameter route middleware
        if ($role === 'Sd' && $currentUser->user_hak !== 'Sd') {
            return response()->json(['error' => 'Unauthorized. Only super admins can access this resource.'], 403);
        }

        if ($role === 'Ad' && $currentUser->user_hak !== 'Ad') {
            return response()->json(['error' => 'Unauthorized. Only admins can access this resource.'], 403);
        }

        if ($role === 'Us' && $currentUser->user_hak !== 'Us') {
            return response()->json(['error' => 'Unauthorized. Only users can access this resource.'], 403);
        }

        // Lanjutkan ke request berikutnya jika role cocok
        return $next($request);
    }
}
