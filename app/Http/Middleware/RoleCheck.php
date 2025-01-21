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

        // Cek apakah pengguna sudah login dan memiliki role yang sesuai
        if (!$currentUser || $currentUser->user_hak !== $role) {
            return response()->json(['error' => 'Unauthorized. Only users with role ' . $role . ' can access this resource.'], 403);
        }

        return $next($request);
    }
}
