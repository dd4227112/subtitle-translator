<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// API Version 1
Route::prefix('v1')->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | Public Routes (No Authentication Required)
    |--------------------------------------------------------------------------
    */
    
    // Login endpoint with rate limiting (5 attempts per minute)
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('api.v1.login');
    
    /*
    |--------------------------------------------------------------------------
    | Protected Routes (Require Authentication)
    |--------------------------------------------------------------------------
    */
    
    Route::middleware(['auth:sanctum', 'active'])->group(function () {
        
        // Logout endpoint
        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('api.v1.logout');
        
        // User profile (accessible by all authenticated users)
        Route::get('/profile', [ProfileController::class, 'show'])
            ->name('api.v1.profile');
        
        /*
        |--------------------------------------------------------------------------
        | Admin Only Routes (Require Admin Role)
        |--------------------------------------------------------------------------
        */
        
        Route::middleware(['role:admin'])->group(function () {
            
            // User management endpoints (CRUD)
            Route::get('/users', [UserController::class, 'index'])
                ->name('api.v1.users.index');
            
            Route::post('/users', [UserController::class, 'store'])
                ->name('api.v1.users.store');
            
            Route::put('/users/{user}', [UserController::class, 'update'])
                ->name('api.v1.users.update');
            
            Route::delete('/users/{user}', [UserController::class, 'destroy'])
                ->name('api.v1.users.destroy');
        });
    });
});
