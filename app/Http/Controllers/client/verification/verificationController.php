<?php

namespace App\Http\Controllers\client\verification;

use App\Http\Controllers\Controller;
use App\Models\Client_profile;
use App\Models\Client_verification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class verificationController extends Controller
{
    public function index()
    {
        $client = Auth::user()->load('clientProfile');

        return view('client.verification.verification', compact('client'));
    }

   public function store(Request $request)
{
    try {
        $validated = $request->validate([
            
            'id_type' => 'required|in:passport,drivers_license,national_id,sss,umid,philhealth,voters_id,postal_id,prc_id,tin_id',
            'id_number' => 'required|string|max:50',
            'id_front_image' => 'required|image|mimes:jpeg,jpg,png|max:5120',
            'id_back_image' => 'required|image|mimes:jpeg,jpg,png|max:5120',
            'selfie_with_id' => 'required|image|mimes:jpeg,jpg,png|max:5120',
        ]);
        $validated['client_id'] = Auth::id();

        // Check if already verified (approved by admin)
        if (Client_verification::where('client_id', $validated['client_id'])
            ->where('status', 'approved')
            ->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Client already verified.'
            ], 422);
        }

        // Upload images
        $verification = Client_verification::updateOrCreate(
            ['client_id' => $validated['client_id']],
            [
                'id_type' => $validated['id_type'],
                'id_number' => $validated['id_number'],
                'id_front_image_path' => $request->file('id_front_image')->store('verifications/id-front', 'public'),
                'id_back_image_path' => $request->file('id_back_image')->store('verifications/id-back', 'public'),
                'selfie_with_id_image_path' => $request->file('selfie_with_id')->store('verifications/selfies', 'public'),
                'status' => 'pending',
                'submitted_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message'  => 'Your verification was submitted successfully. Please wait for admin approval.',
            'data' => $verification,
            'redirect' => route('client.home'),


        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        \Log::error('Verification error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Processing failed. Please try again.',
            
        ], 500);
    }
}

}
