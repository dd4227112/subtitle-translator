<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * List all users with pagination (Admin only)
     * 
     * @param Request $request
     * @return UserCollection
     */
    public function index(Request $request): UserCollection
    {
        $perPage = $request->get('per_page', 15);
        $perPage = min($perPage, 100); // Maximum 100 per page
        
        $users = User::with('roles')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        
        return new UserCollection($users);
    }
    
    /**
     * Create a new user (Admin only)
     * 
     * @param StoreUserRequest $request
     * @return JsonResponse
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_active' => $request->is_active,
            ]);
            
            // Assign role
            $user->assignRole($request->role);
            
            // Log activity
            ActivityLog::logActivity(
                auth()->id(),
                'user_created',
                'Admin created a new user',
                [
                    'created_user_id' => $user->id,
                    'created_user_email' => $user->email,
                    'assigned_role' => $request->role,
                ]
            );
            
            DB::commit();
            
            return response()->json([
                'message' => 'User created successfully',
                'user' => new UserResource($user->load('roles')),
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to create user',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred',
            ], 500);
        }
    }
    
    /**
     * Update an existing user (Admin only)
     * 
     * @param UpdateUserRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            
            DB::beginTransaction();
            
            // Prevent admin from deactivating themselves
            if ($user->id === auth()->id() && $request->has('is_active') && !$request->is_active) {
                return response()->json([
                    'message' => 'You cannot deactivate your own account',
                ], 422);
            }
            
            $changes = [];
            
            // Update basic fields
            if ($request->has('name')) {
                $changes['name'] = ['old' => $user->name, 'new' => $request->name];
                $user->name = $request->name;
            }
            
            if ($request->has('email')) {
                $changes['email'] = ['old' => $user->email, 'new' => $request->email];
                $user->email = $request->email;
            }
            
            if ($request->has('password')) {
                $changes['password'] = 'updated';
                $user->password = Hash::make($request->password);
                
                // Revoke all tokens when password changes
                $user->tokens()->delete();
            }
            
            if ($request->has('is_active')) {
                $changes['is_active'] = ['old' => $user->is_active, 'new' => $request->is_active];
                $user->is_active = $request->is_active;
            }
            
            $user->save();
            
            // Update role if provided
            if ($request->has('role')) {
                $oldRoles = $user->getRoleNames()->toArray();
                $user->syncRoles([$request->role]);
                $changes['role'] = ['old' => $oldRoles, 'new' => $request->role];
            }
            
            // Log activity
            ActivityLog::logActivity(
                auth()->id(),
                'user_updated',
                'Admin updated user information',
                [
                    'updated_user_id' => $user->id,
                    'updated_user_email' => $user->email,
                    'changes' => $changes,
                ]
            );
            
            DB::commit();
            
            return response()->json([
                'message' => 'User updated successfully',
                'user' => new UserResource($user->load('roles')),
            ], 200);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to update user',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred',
            ], 500);
        }
    }
    
    /**
     * Delete a user (Admin only)
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            
            // Prevent admin from deleting themselves
            if ($user->id === auth()->id()) {
                return response()->json([
                    'message' => 'You cannot delete your own account',
                ], 422);
            }
            
            // Soft delete the user
            $user->delete();
            
            // Revoke all tokens
            $user->tokens()->delete();
            
            // Log activity
            ActivityLog::logActivity(
                auth()->id(),
                'user_deleted',
                'Admin deleted a user',
                [
                    'deleted_user_id' => $user->id,
                    'deleted_user_email' => $user->email,
                ]
            );
            
            return response()->json([
                'message' => 'User deleted successfully',
            ], 200);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete user',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred',
            ], 500);
        }
    }
}
