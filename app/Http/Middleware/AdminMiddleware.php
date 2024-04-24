<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Retrieve the user with roles relationship
        $user = User::with('roles')->find(auth()->user()->id);

        // Check if the user is authenticated and has the 'admin' role
        if ($user && $user->roles->contains('roleName', 'Admin')) {
            return $next($request);
        }


        // If not, return a 403 Forbidden response
        return response()->json(['error' => 'Unauthorized. Admin access required.'], 403);
    }
}
