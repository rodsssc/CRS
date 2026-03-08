<?php

namespace App\Http\Controllers\admin\verification;

use App\Http\Controllers\Controller;
use App\Models\Client_profile;
use App\Models\Client_verification;
use App\Models\User;
use App\Models\Verification_log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class verificationController extends Controller
{
   public function index()
{
    $clientProfile = Client_profile::with('user.latestVerification')->get();

    $verifiedCount = Client_verification::approved()->count();
    $pendingCount  = Client_verification::pending()->count();
    $rejectedCount = Client_verification::rejected()->count();
    $totalClients  = Client_profile::count();

    return view('admin.verification.verification', compact(
        'clientProfile',
        'verifiedCount',
        'pendingCount',
        'rejectedCount',
        'totalClients'
    ));
}


public function show($id){
    $user = User::with(['clientProfile', 'latestVerification.verifier'])
            ->findOrFail($id);


            return response()->json([
            'success' => true,
            'data' => [
                // User data
                'id' => $user->latestVerification->id ?? null,
                'email' => $user->email,
                'phone' => $user->phone,
                
                // Profile data
                'first_name' => $user->clientProfile->first_name ?? '',
                'last_name' => $user->clientProfile->last_name ?? '',
                'date_birth' => $user->clientProfile->date_birth 
                    ? $user->clientProfile->date_birth->format('M d, Y') 
                    : '',
                'address' => $user->clientProfile->address ?? '',
                'nationality' => $user->clientProfile->nationality ?? '',
                'facebook_name' => $user->clientProfile->facebook_name ?? '',
                'emergency_contact_name' => $user->clientProfile->emergency_contact_name ?? '',
                'emergency_contact_phone' => $user->clientProfile->emergency_contact_phone ?? '',
                
                // Verification data
                'id_type' => $user->latestVerification->formatted_id_type ?? '',
                'id_number' => $user->latestVerification->id_number ?? '',
                'id_front_image' => $user->latestVerification->id_front_image_path 
                    ? asset('storage/' . $user->latestVerification->id_front_image_path) 
                    : 'https://via.placeholder.com/500x300?text=ID+Front',
                'id_back_image' => $user->latestVerification->id_back_image_path 
                    ? asset('storage/' . $user->latestVerification->id_back_image_path) 
                    : 'https://via.placeholder.com/500x300?text=ID+Back',
                'selfie_image' => $user->latestVerification->selfie_with_id_image_path 
                    ? asset('storage/' . $user->latestVerification->selfie_with_id_image_path) 
                    : 'https://via.placeholder.com/500x300?text=Selfie+with+ID',
                'status' => $user->latestVerification->status ?? 'pending',
                'submitted_at' => $user->latestVerification->submitted_at 
                    ? $user->latestVerification->submitted_at->format('M d, Y g:i A') 
                    : '-',
                'verified_at' => $user->latestVerification->verified_at 
                    ? $user->latestVerification->verified_at->format('M d, Y g:i A') 
                    : '-',
                'verified_by' => $user->latestVerification->verifier->name ?? '-',
                'rejection_reason' => $user->latestVerification->rejection_reason ?? null,
            ]
        ]);

}


public function approve($id)
    {
        try {
           
            // Find the verification record
            $verification = Client_verification::findOrFail($id);

            // Check if already approved
            if ($verification->status === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'This verification is already approved.'
                ], 400);
            }

            // Approve the verification
            $verification->approve(Auth::id());

            // Log the action
            Verification_log::create([
                'verification_id' => $verification->id,
                'admin_id' => Auth::id(),
                'action' => 'approved',
                'remarks' => 'Verification approved by admin',
            ]);

           

            return response()->json([
                'success' => true,
                'message' => 'Verification approved successfully!',
                'data' => [
                    'status' => 'approved',
                    'verified_at' => $verification->verified_at->format('M d, Y g:i A'),
                    'verified_by' => $verification->verifier->name ?? 'Admin',
                ]
            ]);

        } catch (\Exception $e) {
        
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve verification: ' . $e->getMessage()
            ], 500);
        }
    }


    public function reject(Request $request, $id)
{
    try {
        // Validate the rejection reason
        $request->validate([
            'rejection_reason' => 'nullable|string|max:500'
        ]);

        $reason = $request->rejection_reason ?: 'Rejected by admin';

        $verification = Client_verification::findOrFail($id);

        // Check if already approved
        if ($verification->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot reject an already approved verification.'
            ], 400);
        }

        // Check if already rejected
        if ($verification->status === 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'This verification is already rejected.'
            ], 400);
        }

        // Reject the verification with reason
        $verification->reject( Auth::id());

        // Log the action
        Verification_log::create([
            'verification_id' => $verification->id,
            'admin_id' => Auth::id(),
            'action' => 'rejected',
            'remarks' => 'Verification rejected: ' . $reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Verification rejected successfully!',
            'data' => [
                'status' => 'rejected',
                'verified_at' => $verification->verified_at->format('M d, Y g:i A'),
                'verified_by' => $verification->verifier->name ?? 'Admin',
                'rejection_reason' => $reason
            ]
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to reject verification: ' . $e->getMessage()
        ], 500);
    }
}

}
