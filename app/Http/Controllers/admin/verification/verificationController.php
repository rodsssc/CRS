<?php

namespace App\Http\Controllers\Admin\Verification;

use App\Http\Controllers\Controller;
use App\Models\Client_profile;
use App\Models\Client_verification;
use App\Models\User;
use App\Models\Verification_log;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    // =========================================================================
    // INDEX — Load verification management page
    //         Results are ordered by latest submitted_at first
    // =========================================================================

    public function index(Request $request): \Illuminate\View\View
    {
        $search  = trim((string) $request->query('q', ''));
        $q       = $search; // keep backward compatible name used in Blade
        $status  = $request->query('status');
        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(5, min(100, $perPage));

        // ── Base query ───────────────────────────────────────────────────────────
        // Avoid join-based ordering to keep pagination COUNT queries stable at
        // scale (the join + correlated latest-verification lookup can cause
        // heavy SQL / inflated result counts).
        $query = Client_profile::with('user.latestVerification')
            ->orderByRaw(
                "COALESCE((
                    SELECT cv.submitted_at
                    FROM client_verifications cv
                    WHERE cv.client_id = client_profiles.client_id
                    ORDER BY cv.submitted_at DESC
                    LIMIT 1
                ), client_profiles.created_at) DESC"
            );

        // ── Search by name, email, or phone ──────────────────────────────────
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('client_profiles.first_name', 'like', "%{$search}%")
                  ->orWhere('client_profiles.last_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // ── Filter by verification status ─────────────────────────────────────
        $allowedStatuses = ['pending', 'approved', 'rejected'];
        if ($status && in_array($status, $allowedStatuses, true)) {
            $query->whereHas('user.latestVerification', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }

        $clientProfile = $query->paginate($perPage)->withQueryString();

        // ── Stats (unaffected by search/filter) ───────────────────────────────
        $totalClients  = Client_profile::count();
        $verifiedCount = Client_verification::approved()->count();
        $pendingCount  = Client_verification::pending()->count();
        $rejectedCount = Client_verification::rejected()->count();

        return view('admin.verification.verification', compact(
            'clientProfile',
            'totalClients',
            'verifiedCount',
            'pendingCount',
            'rejectedCount',
            'search',
            'q',
            'status',
            'perPage'
        ));
    }

    // =========================================================================
    // SHOW — Return client verification details as JSON (used by view modal)
    // =========================================================================

    public function show(int $id): JsonResponse
    {
        $user = User::with(['clientProfile', 'latestVerification.verifier'])
            ->findOrFail($id);

        $profile      = $user->clientProfile;
        $verification = $user->latestVerification;

        return response()->json([
            'success' => true,
            'data'    => [
                // Verification record ID (used for approve/reject actions)
                'id'    => $verification->id ?? null,
                'email' => $user->email      ?? '—',
                'phone' => $user->phone      ?? '—',

                // Profile details
                'first_name'              => $profile->first_name              ?? '—',
                'last_name'               => $profile->last_name               ?? '—',
                'date_birth'              => $profile?->date_birth
                                                ? $profile->date_birth->format('M d, Y')
                                                : '—',
                'address'                 => $profile->address                 ?? '—',
                'nationality'             => $profile->nationality             ?? '—',
                'facebook_name'           => $profile->facebook_name           ?? '—',
                'emergency_contact_name'  => $profile->emergency_contact_name  ?? '—',
                'emergency_contact_phone' => $profile->emergency_contact_phone ?? '—',

                // Verification details
                'id_type'   => $verification->formatted_id_type ?? '—',
                'id_number' => $verification->id_number         ?? '—',

                // ID images — fallback to placeholder if not uploaded
                'id_front_image' => $verification?->id_front_image_path
                                        ? asset('storage/' . $verification->id_front_image_path)
                                        : 'https://via.placeholder.com/500x300?text=ID+Front',
                'id_back_image'  => $verification?->id_back_image_path
                                        ? asset('storage/' . $verification->id_back_image_path)
                                        : 'https://via.placeholder.com/500x300?text=ID+Back',
                'selfie_image'   => $verification?->selfie_with_id_image_path
                                        ? asset('storage/' . $verification->selfie_with_id_image_path)
                                        : 'https://via.placeholder.com/500x300?text=Selfie+with+ID',

                // Status and timestamps
                'status'           => $verification->status ?? 'none',
                'submitted_at'     => $verification?->submitted_at
                                        ? $verification->submitted_at->format('M d, Y g:i A')
                                        : '—',
                'verified_at'      => $verification?->verified_at
                                        ? $verification->verified_at->format('M d, Y g:i A')
                                        : '—',
                'verified_by'      => $verification->verifier->name ?? '—',
                'rejection_reason' => $verification->rejection_reason ?? null,
            ],
        ]);
    }

    // =========================================================================
    // APPROVE — Approve a client verification
    // =========================================================================

    public function approve(int $id): JsonResponse
    {
        try {
            $verification = Client_verification::findOrFail($id);

            // Guard: skip if already approved
            if ($verification->status === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'This verification is already approved.',
                ], 400);
            }

            $verification->approve(Auth::id());

            Verification_log::create([
                'verification_id' => $verification->id,
                'admin_id'        => Auth::id(),
                'action'          => 'approved',
                'remarks'         => 'Verification approved by admin.',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Verification approved successfully.',
                'data'    => [
                    'status'      => 'approved',
                    'verified_at' => $verification->verified_at->format('M d, Y g:i A'),
                    'verified_by' => $verification->verifier->name ?? 'Admin',
                ],
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Verification record not found.',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve verification: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // REJECT — Reject a client verification with an optional reason
    // =========================================================================

    public function reject(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'rejection_reason' => 'nullable|string|max:500',
            ]);

            $verification = Client_verification::findOrFail($id);

            // Guard: cannot reject an already approved verification
            if ($verification->status === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot reject an already approved verification.',
                ], 400);
            }

            // Guard: skip if already rejected
            if ($verification->status === 'rejected') {
                return response()->json([
                    'success' => false,
                    'message' => 'This verification is already rejected.',
                ], 400);
            }

            $reason = $request->rejection_reason ?: 'Rejected by admin.';

            $verification->reject($reason, Auth::id());

            Verification_log::create([
                'verification_id' => $verification->id,
                'admin_id'        => Auth::id(),
                'action'          => 'rejected',
                'remarks'         => 'Verification rejected: ' . $reason,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Verification rejected successfully.',
                'data'    => [
                    'status'           => 'rejected',
                    'verified_at'      => $verification->verified_at
                                            ? $verification->verified_at->format('M d, Y g:i A')
                                            : '—',
                    'verified_by'      => $verification->verifier->name ?? 'Admin',
                    'rejection_reason' => $reason,
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Verification record not found.',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject verification: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function pendingCount(): \Illuminate\Http\JsonResponse
{
    $count = \App\Models\Client_verification::where('status', 'pending')->count();
 
    return response()->json(['count' => $count]);
}
 
}