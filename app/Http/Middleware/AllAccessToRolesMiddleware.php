<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AllAccessToRolesMiddleware
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
        if (
            $user && ($user->roles->contains('roleName', 'Admin') ||
                $user->roles->contains('roleName', 'Inventory') ||
                $user->roles->contains('roleName', 'Front Desk') ||
                $user->roles->contains('roleName', 'House Keeping'))
        ) {
            return $next($request);
        }


        // If not, return a 403 Forbidden response
        return response()->json(['error' => 'Unauthorized. Only personnel can access.'], 403);
    }
}
