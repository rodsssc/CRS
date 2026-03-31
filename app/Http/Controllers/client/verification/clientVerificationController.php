<?php

namespace App\Http\Controllers\client\verification;

use App\Http\Controllers\Controller;
use App\Models\Client_verification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class clientVerificationController extends Controller
{
    public function index()
    {
        $client = Auth::user()->load('clientProfile');
        return view('client.verification.verification', compact('client'));
    }

    public function store(Request $request)
    {
        try {
            Log::info('Verification store started', ['user_id' => Auth::id()]);
            
            $validated = $request->validate([
                'id_type'        => 'required|string|max:100',
                'id_number'      => 'required|string|max:50',
                'id_front_image' => 'required|image|mimes:jpeg,jpg,png|max:5120',
            ]);
            
            $clientId = Auth::id();
            
            if (!$clientId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.',
                ], 401);
            }

            // Check if ID number is already used by another user
            $existingVerification = Client_verification::where('id_number', $validated['id_number'])
                ->where('client_id', '!=', $clientId)
                ->first();
                
            if ($existingVerification) {
                return response()->json([
                    'success' => false,
                    'message' => 'This ID number is already registered with another account.',
                    'errors' => [
                        'id_number' => ['This ID number is already in use by another user.']
                    ]
                ], 422);
            }

            // Check if user already submitted this ID
            $userExistingVerification = Client_verification::where('client_id', $clientId)
                ->where('id_number', $validated['id_number'])
                ->where('id_type', $validated['id_type'])
                ->first();
                
            if ($userExistingVerification) {
                $status = $userExistingVerification->status;
                $message = '';
                
                if ($status === 'approved') {
                    $message = 'This ID has already been verified.';
                } elseif ($status === 'pending') {
                    $message = 'You already have a pending verification with this ID.';
                } elseif ($status === 'rejected') {
                    $message = 'This ID was previously rejected. Please contact support.';
                }
                
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => ['id_number' => [$message]]
                ], 422);
            }

            // Check if already approved
            $approvedExists = Client_verification::where('client_id', $clientId)
                ->where('status', 'approved')
                ->exists();
                
            if ($approvedExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is already verified.'
                ], 422);
            }

            // Check if already pending
            $pendingExists = Client_verification::where('client_id', $clientId)
                ->where('status', 'pending')
                ->exists();
                
            if ($pendingExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have a pending verification.'
                ], 422);
            }

            // Store image
            Log::info('Storing image', ['user_id' => $clientId]);
            
            $frontImagePath = $request->file('id_front_image')->store('verifications/id-front', 'public');
            
            if (!$frontImagePath) {
                throw new \Exception('Failed to store image');
            }
            
            Log::info('Image stored', ['path' => $frontImagePath]);

            // Save to database
            $verification = Client_verification::create([
                'client_id'           => $clientId,
                'id_type'             => $validated['id_type'],
                'id_number'           => $validated['id_number'],
                'id_front_image_path' => $frontImagePath,
                'status'              => 'pending',
                'submitted_at'        => now(),
            ]);
            
            Log::info('Verification saved', ['verification_id' => $verification->id]);

            return response()->json([
                'success'  => true,
                'message'  => 'Your verification was submitted successfully!',
                'data'     => $verification,
                'redirect' => route('client.home'),
            ], 201);

        } catch (ValidationException $e) {
            Log::warning('Validation error', ['errors' => $e->errors()]);
            
            return response()->json([
                'success' => false,
                'errors'  => $e->errors(),
                'message' => 'Please check the form for errors.',
            ], 422);

        } catch (\Exception $e) {
            Log::error('Verification error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again.',
            ], 500);
        }
    }
}