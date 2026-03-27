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
     * Display a listing of users with search, filter, and pagination
     */
    public function index(Request $request)
    {
        $q       = trim((string) $request->query('q', ''));
        $role    = $request->query('role');
        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(5, min(100, $perPage));

        $query = User::query();

        // Search by name, email, or phone
        if ($q !== '') {
            $query->where(function ($userQuery) use ($q) {
                $userQuery
                    ->where('name',  'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        // Filter by role
        $allowedRoles = ['admin', 'owner', 'staff', 'client'];
        if ($role && in_array($role, $allowedRoles, true)) {
            $query->where('role', $role);
        }

        $users = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();

        // Stats always from full table (unaffected by search/filter)
        $stats = [
            'total'  => User::count(),
            'admin'  => User::where('role', 'admin')->count(),
            'owner'  => User::where('role', 'owner')->count(),
            'staff'  => User::where('role', 'staff')->count(),
            'client' => User::where('role', 'client')->count(),
        ];

        return view('admin.user.user', compact(
            'users',
            'stats',
            'q',
            'role',
            'perPage'
        ));
    }

    /**
     * Show a single user (AJAX)
     */
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'User found',
                'user'    => $user,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }
    }

    /**
     * Return user data for edit modal (AJAX)
     */
    public function edit($id)
    {
        try {
            $user = User::findOrFail($id);

            return response()->json([
                'success' => true,
                'user'    => $user,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }
    }

    /**
     * Store a newly created user (AJAX)
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'     => 'required|string|min:2|max:50',
                'email'    => 'required|email|unique:users,email|max:100',
                'phone'    => 'nullable|string|min:9|max:20',
                'role'     => 'required|in:admin,owner,staff,client',
                'password' => 'required|min:8|confirmed',
            ]);

            $validated['password'] = Hash::make($validated['password']);

            $user = User::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user'    => $user,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('User creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified user (AJAX)
     */
    public function update(Request $request, $id)
    {
        try {
            Log::info('Update request received', [
                'user_id' => $id,
                'data'    => $request->all(),
            ]);

            $user = User::findOrFail($id);

            $rules = [
                'name'  => 'required|string|min:2|max:50',
                'email' => [
                    'required',
                    'email',
                    'max:100',
                    Rule::unique('users')->ignore($id),
                ],
                'phone' => 'nullable|string|min:9|max:20',
                'role'  => 'required|in:admin,owner,staff,client',
            ];

            // Only validate password if provided
            if ($request->filled('password')) {
                $rules['password'] = 'required|min:8|confirmed';
            }

            $validated = $request->validate($rules);

            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            unset($validated['password_confirmation']);

            $user->update($validated);

            Log::info('User updated successfully', ['user_id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'user'    => $user,
            ]);

        } catch (ValidationException $e) {
            Log::warning('Validation failed', [
                'user_id' => $id,
                'errors'  => $e->errors(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);

        } catch (\Exception $e) {
            Log::error('User update failed', [
                'user_id' => $id,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified user (AJAX)
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully',
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);

        } catch (\Exception $e) {
            Log::error('User deletion failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
            ], 500);
        }
    }
}