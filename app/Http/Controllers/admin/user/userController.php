<?php

namespace App\Http\Controllers\admin\user;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index()
    {
        $users = User::all();
            
        return view('admin.user.user', compact('users'));
    }

    


    public function show($id){
        try{
            $user = User::findOrfail($id);

            return response()->json([
                "message" => "User Found",
                "user" => $user

            ]);
        }catch(\Exception $e){
            return response()->json([
                "message" => "User not found",
                "success" => false

            ],404);
        }
    }

    /**
     * Display the specified user
     */
    public function edit($id)
    {
        try {
            $user = User::findOrFail($id);

            return response()->json([
                'success' => true,
                'user' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|min:2|max:50',
                'email' => 'required|email|unique:users,email|max:100',
                'phone' => 'nullable|string|min:9|max:20',
                'role' => 'required|in:admin,owner,staff,client',
                'password' => 'required|min:8|confirmed',
            ]);

            $validated['password'] = Hash::make($validated['password']);

            $user = User::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user' => $user
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('User creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        try {
            // Log incoming request for debugging
            Log::info('Update request received', [
                'user_id' => $id,
                'data' => $request->all()
            ]);

            $user = User::findOrFail($id);

            // Build validation rules
            $rules = [
                'name' => 'required|string|min:2|max:50',
                'email' => [
                    'required',
                    'email',
                    'max:100',
                    Rule::unique('users')->ignore($id)
                ],
                'phone' => 'nullable|string|min:9|max:20',
                'role' => 'required|in:admin,owner,staff,client',
            ];

            // Only validate password if it's provided
            if ($request->filled('password')) {
                $rules['password'] = 'required|min:8|confirmed';
            }

            $validated = $request->validate($rules);

            // Only update password if provided
            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            // Remove password_confirmation from validated data
            unset($validated['password_confirmation']);

            $user->update($validated);

            Log::info('User updated successfully', ['user_id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'user' => $user
            ]);

        } catch (ValidationException $e) {
            Log::warning('Validation failed', [
                'user_id' => $id,
                'errors' => $e->errors()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('User update failed', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified user
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user'
            ], 500);
        }
    }
}