<?php

namespace App\Http\Controllers\client\verification;

use App\Http\Controllers\Controller;
use App\Models\Client_verification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
            $validated = $request->validate([
                'id_type'        => 'required|in:passport,drivers_license,national_id,sss,umid,philhealth,voters_id,postal_id,prc_id,tin_id',
                'id_number'      => 'required|string|max:50',
                'id_front_image' => 'required|image|mimes:jpeg,jpg,png|max:5120',
            ]);

            $clientId = Auth::id();

            // Block if already approved
            if (Client_verification::where('client_id', $clientId)
                ->where('status', 'approved')
                ->exists()
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is already verified.',
                ], 422);
            }

            // Block if already pending
            if (Client_verification::where('client_id', $clientId)
                ->where('status', 'pending')
                ->exists()
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have a pending verification. Please wait for admin review.',
                ], 422);
            }

            // Store image to Laravel public disk
            $frontImagePath = $request->file('id_front_image')
                ->store('verifications/id-front', 'public');

            // Sync to Hostinger public storage (same pattern as carController)
            $source      = storage_path('app/public/' . $frontImagePath);
            $destination = $this->hostingerPublicStorage . $frontImagePath;
            $destDir     = dirname($destination);

            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }

            if (file_exists($source)) {
                copy($source, $destination);
            } else {
                Log::warning('Verification image source not found after store()', [
                    'source' => $source,
                ]);
            }

            // If DB write fails after this point, clean up the orphaned file
            try {
                $verification = Client_verification::updateOrCreate(
                    ['client_id' => $clientId],
                    [
                        'id_type'             => $validated['id_type'],
                        'id_number'           => $validated['id_number'],
                        'id_front_image_path' => $frontImagePath,
                        'status'              => 'pending',
                        'submitted_at'        => now(),
                        'rejection_reason'    => null,
                        'verified_at'         => null,
                        'verified_by'         => null,
                    ]
                );
            } catch (\Exception $dbException) {
                // Delete from both storage locations so nothing is orphaned
                Storage::disk('public')->delete($frontImagePath);

                $publicFile = $this->hostingerPublicStorage . $frontImagePath;
                if (file_exists($publicFile)) {
                    unlink($publicFile);
                }

                throw $dbException;
            }

            return response()->json([
                'success'  => true,
                'message'  => 'Your verification was submitted successfully. Please wait for admin approval.',
                'data'     => $verification,
                'redirect' => route('client.home'),
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Verification store error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

            return response()->json([
                'success' => false,
                'message' => 'Processing failed. Please try again.',
            ], 500);
        }
    }
}