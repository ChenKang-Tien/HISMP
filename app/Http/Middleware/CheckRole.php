<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthenticated'], 401);
        }
        
        // Get role name from the role relationship
        $userRole = $user->role ? $user->role->name : null;
        
        foreach ($roles as $role) {
            if ($userRole === $role) {
                return $next($request);
            }
        }
        
        return response()->json(['status' => 403, 'message' => 'Forbidden: insufficient role'], 403);
    }
}
