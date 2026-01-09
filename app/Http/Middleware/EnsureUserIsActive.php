<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }
        
        // Check if user is active
        if (!$request->user()->isActive()) {
            // Log inactive user access attempt
            ActivityLog::logActivity(
                $request->user()->id,
                'inactive_user_access',
                'Inactive user attempted to access the API',
                [
                    'route' => $request->path(),
                    'method' => $request->method(),
                ]
            );
            
            // Revoke all tokens
            $request->user()->tokens()->delete();
            
            return response()->json([
                'message' => 'Your account has been deactivated. Please contact support.',
            ], 403);
        }
        
        return $next($request);
    }
}
