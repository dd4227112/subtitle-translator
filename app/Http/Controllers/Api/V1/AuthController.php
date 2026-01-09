<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user and issue Sanctum token
     * 
     * @param LoginRequest $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        // Check rate limiting
        $key = 'login-attempt:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            
            ActivityLog::logActivity(
                null,
                'login_rate_limited',
                'Too many login attempts',
                ['email' => $request->email]
            );
            
            return response()->json([
                'message' => 'Too many login attempts. Please try again in ' . $seconds . ' seconds.',
            ], 429);
        }
        
        // Find user by email
        $user = User::where('email', $request->email)->first();
        
        // Validate credentials
        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 60); // Lock for 60 seconds
            
            ActivityLog::logActivity(
                $user?->id,
                'login_failed',
                'Invalid credentials',
                ['email' => $request->email]
            );
            
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        
        // Check if user is active
        if (!$user->isActive()) {
            ActivityLog::logActivity(
                $user->id,
                'login_failed',
                'Account is inactive',
                ['email' => $request->email]
            );
            
            return response()->json([
                'message' => 'Your account has been deactivated. Please contact support.',
            ], 403);
        }
        
        // Clear rate limiter on successful login
        RateLimiter::clear($key);
        
        // Revoke all previous tokens (token rotation)
        $user->tokens()->delete();
        
        // Create new token
        $token = $user->createToken('mobile-app', ['*'], now()->addDays(30))->plainTextToken;
        
        // Log successful login
        ActivityLog::logActivity(
            $user->id,
            'login_success',
            'User logged in successfully'
        );
        
        return response()->json([
            'message' => 'Login successful',
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }
    
    /**
     * Logout user and revoke current token
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke current access token
        $request->user()->currentAccessToken()->delete();
        
        // Log logout
        ActivityLog::logActivity(
            $request->user()->id,
            'logout',
            'User logged out successfully'
        );
        
        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }
}
