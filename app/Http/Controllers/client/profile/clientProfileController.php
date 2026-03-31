<?php

namespace App\Http\Controllers\client\profile;

use App\Http\Controllers\Controller;
use App\Models\Client_profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class clientProfileController extends Controller
{
    public function index()
    {
        $client = Auth::user()->load('clientProfile');
        return view('client.profiling.profiling', compact('client'));
    }

    public function store(Request $request)
    {
        // Always return JSON for AJAX requests
        if (!$request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request.'
            ], 400);
        }

        try {
            Log::info('Profile store started', ['user_id' => Auth::id()]);

            $validated = $request->validate([
                'firstName'            => 'required|string|max:255',
                'lastName'             => 'required|string|max:255',
                'dateBirth'            => 'required|date',
                'address'              => 'required|string',
                'nationality'          => 'required|string',
                'facebook_name'        => 'nullable|string|max:255',
                'emergencyContactName' => 'nullable|string|max:255',
                'emergencyContactPhone'=> 'nullable|string|max:20',
            ]);

            $profile = Client_profile::updateOrCreate(
                ['client_id' => Auth::id()],
                [
                    'client_id'               => Auth::id(),
                    'first_name'              => $validated['firstName'],
                    'last_name'               => $validated['lastName'],
                    'date_birth'              => $validated['dateBirth'],
                    'address'                 => $validated['address'],
                    'nationality'             => $validated['nationality'],
                    'facebook_name'           => $validated['facebook_name']        ?? null,
                    'emergency_contact_name'  => $validated['emergencyContactName'] ?? null,
                    'emergency_contact_phone' => $validated['emergencyContactPhone']?? null,
                ]
            );

            Log::info('Profile saved', ['user_id' => Auth::id()]);

            return response()->json([
                'success'  => true,
                'message'  => $profile->wasRecentlyCreated ? 'Profile created successfully' : 'Profile updated successfully',
                'redirect' => route('client.verification.index'),
            ], 200);

        } catch (ValidationException $e) {
            Log::warning('Profile validation error', ['errors' => $e->errors()]);

            return response()->json([
                'success' => false,
                'message' => 'Please fill in all required fields.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Profile error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving your profile.',
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $profile = Client_profile::where('client_id', $id)->first();

            if (!$profile) {
                return response()->json(['success' => false, 'message' => 'Profile not found'], 404);
            }

            return response()->json(['success' => true, 'data' => $profile]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error fetching profile'], 500);
        }
    }
}