<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }
        
        // Check if user has required role
        if (!$request->user()->hasRole($role)) {
            // Log unauthorized access attempt
            ActivityLog::logActivity(
                $request->user()->id,
                'unauthorized_access',
                "User attempted to access resource requiring '{$role}' role",
                [
                    'required_role' => $role,
                    'user_roles' => $request->user()->getRoleNames(),
                    'route' => $request->path(),
                    'method' => $request->method(),
                ]
            );
            
            return response()->json([
                'message' => 'Forbidden. You do not have permission to access this resource.',
            ], 403);
        }
        
        return $next($request);
    }
}
