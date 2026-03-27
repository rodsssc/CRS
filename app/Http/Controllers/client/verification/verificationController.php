<?php

namespace App\Http\Controllers\client\verification;

use App\Http\Controllers\Controller;
use App\Models\Client_verification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class verificationController extends Controller
{
    /**
     * Hostinger public storage base path
     */
    private string $hostingerPublicStorage = '/home/u503987723/domains/bgcar-rental.org/public_html/storage/';

    public function index()
    {
        $client = Auth::user()->load('clientProfile');

        return view('client.verification.verification', compact('client'));
    }

    public function store(Request $request)
    {
        try {
            Log::info('Verification store started', ['user_id' => Auth::id()]);
            
            // First validate the basic fields
            $validated = $request->validate([
                'id_type'        => 'required|string|in:passport,drivers_license,national_id,sss,umid,philhealth,voters_id,postal_id,prc_id,tin_id',
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

            // Check if ID number is already used by another user (not the current user)
            $existingVerification = Client_verification::where('id_number', $validated['id_number'])
                ->where('client_id', '!=', $clientId)
                ->first();
                
            if ($existingVerification) {
                return response()->json([
                    'success' => false,
                    'message' => 'This ID number is already registered with another account. Please use a different ID or contact support.',
                    'errors' => [
                        'id_number' => ['This ID number is already in use by another user.']
                    ]
                ], 422);
            }

            // Check if this specific user already submitted the same ID number
            $userExistingVerification = Client_verification::where('client_id', $clientId)
                ->where('id_number', $validated['id_number'])
                ->where('id_type', $validated['id_type'])
                ->first();
                
            if ($userExistingVerification) {
                $status = $userExistingVerification->status;
                $message = '';
                
                if ($status === 'approved') {
                    $message = 'This ID has already been verified. Your account is already approved.';
                } elseif ($status === 'pending') {
                    $message = 'You already have a pending verification with this ID. Please wait for admin review.';
                } elseif ($status === 'rejected') {
                    $message = 'This ID was previously rejected. Please contact support for assistance.';
                } else {
                    $message = 'You have already submitted this ID for verification.';
                }
                
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => [
                        'id_number' => [$message]
                    ]
                ], 422);
            }

            // Block if already approved
            $approvedExists = Client_verification::where('client_id', $clientId)
                ->where('status', 'approved')
                ->exists();
                
            if ($approvedExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is already verified. No need to submit again.',
                    'errors' => [
                        'general' => ['Account already verified.']
                    ]
                ], 422);
            }

            // Block if already pending (but with different ID)
            $pendingExists = Client_verification::where('client_id', $clientId)
                ->where('status', 'pending')
                ->exists();
                
            if ($pendingExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have a pending verification. Please wait for admin review.',
                    'errors' => [
                        'general' => ['Pending verification exists.']
                    ]
                ], 422);
            }

            // Store image to Laravel public disk
            Log::info('Storing image', ['user_id' => $clientId]);
            
            $frontImagePath = $request->file('id_front_image')->store('verifications/id-front', 'public');
            
            if (!$frontImagePath) {
                throw new \Exception('Failed to store image');
            }
            
            Log::info('Image stored', ['path' => $frontImagePath]);

            // Sync to Hostinger public storage if on production
            if (app()->environment('production')) {
                $source = storage_path('app/public/' . $frontImagePath);
                $destination = $this->hostingerPublicStorage . $frontImagePath;
                $destDir = dirname($destination);

                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }

                if (file_exists($source)) {
                    copy($source, $destination);
                    Log::info('Copied to Hostinger storage', ['destination' => $destination]);
                } else {
                    Log::warning('Verification image source not found after store()', [
                        'source' => $source,
                    ]);
                }
            }

            // Save to database
            try {
                $verification = Client_verification::create([
                    'client_id'           => $clientId,
                    'id_type'             => $validated['id_type'],
                    'id_number'           => $validated['id_number'],
                    'id_front_image_path' => $frontImagePath,
                    'status'              => 'pending',
                    'submitted_at'        => now(),
                    'rejection_reason'    => null,
                    'verified_at'         => null,
                    'verified_by'         => null,
                ]);
                
                Log::info('Verification saved to database', ['verification_id' => $verification->id]);
                
            } catch (\Exception $dbException) {
                Log::error('Database error', ['error' => $dbException->getMessage()]);
                
                // Delete from both storage locations so nothing is orphaned
                Storage::disk('public')->delete($frontImagePath);
                
                if (app()->environment('production')) {
                    $publicFile = $this->hostingerPublicStorage . $frontImagePath;
                    if (file_exists($publicFile)) {
                        unlink($publicFile);
                    }
                }
                
                throw $dbException;
            }

            return response()->json([
                'success'  => true,
                'message'  => 'Your verification was submitted successfully. Please wait for admin approval.',
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
            Log::error('Verification store error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request. Please try again.',
                'debug' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }
}