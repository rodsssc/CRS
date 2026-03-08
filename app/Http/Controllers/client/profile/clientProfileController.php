<?php

namespace App\Http\Controllers\client\profile;

use App\Http\Controllers\Controller;
use App\Models\Client_profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class clientProfileController extends Controller
{
    /**
     * Store OR Update - Handles both create and update
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'date_birth' => 'required|date',
                'address' => 'required|string',
                'nationality' => 'required|string',
                'facebook_name' => 'nullable|string|max:255',
                'emergency_contact_name' => 'nullable|string|max:255',
                'emergency_contact_phone' => 'nullable|string|max:20',
            ]);

            $clientId = Auth::id();
            $validated['client_id'] = $clientId;

            // Create or update profile in one place to avoid duplicates
            $clientProfile = Client_profile::updateOrCreate(
                ['client_id' => $clientId],
                $validated
            );

            $action = $clientProfile->wasRecentlyCreated ? 'created' : 'updated';
            $message = $action === 'created'
                ? 'Profile created successfully'
                : 'Profile updated successfully';

            return response()->json([
                'success' => true,
                'message' => $message,
                'clientProfile' => $clientProfile,
                'action' => $action
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error creating profile: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating profile'
            ], 500);
        }
    }

    /**
     * Update existing profile
     */
    public function update(Request $request, $id = null)
    {
        try {
            // Use client_id from request or route parameter
            $clientId = $id ?? $request->input('client_id');

            // Verify the authenticated user matches the client_id
            if (Auth::id() != $clientId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Validate incoming data
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'date_birth' => 'required|date',
                'address' => 'required|string',
                'nationality' => 'required|string',
                'facebook_name' => 'nullable|string|max:255',
                'emergency_contact_name' => 'nullable|string|max:255',
                'emergency_contact_phone' => 'nullable|string|max:20',
            ]);

            // Find the profile
            $profile = Client_profile::where('client_id', $clientId)->first();

            if (!$profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profile not found for this client'
                ], 404);
            }

            // Update the profile
            $profile->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'id' => $profile->id,
                    'client_id' => $profile->client_id,
                    'first_name' => $profile->first_name,
                    'last_name' => $profile->last_name,
                    'date_birth' => $profile->date_birth,
                    'address' => $profile->address,
                    'nationality' => $profile->nationality,
                    'emergency_contact_name' => $profile->emergency_contact_name,
                    'emergency_contact_phone' => $profile->emergency_contact_phone,
                ],
                'action' => 'updated'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error updating profile: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating profile'
            ], 500);
        }
    }

    /**
     * Show/Get profile
     */
    public function show($id)
    {
        try {
            // Verify user exists and is a client
            $client = User::where('id', $id)
                ->where('role', 'client')
                ->firstOrFail();

            // Get profiling data
            $profile = Client_profile::where('client_id', $id)->first();

            if (!$profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'No profiling data found for this client'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Profiling data retrieved successfully',
                'data' => [
                    'id' => $profile->id,
                    'client_id' => $profile->client_id,
                    'first_name' => $profile->first_name,
                    'last_name' => $profile->last_name,
                    'date_birth' => $profile->date_birth,
                    'address' => $profile->address,
                    'nationality' => $profile->nationality,
                    'emergency_contact_name' => $profile->emergency_contact_name,
                    'emergency_contact_phone' => $profile->emergency_contact_phone,
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error fetching profiling data: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching profiling data'
            ], 500);
        }
    }
}