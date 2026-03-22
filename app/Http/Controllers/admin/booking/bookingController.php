<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    // =========================================================================
    // INDEX — search, status filter, pagination
    // =========================================================================

    public function index(Request $request): \Illuminate\View\View
    {
        $q       = trim((string) $request->query('q', ''));
        $status  = $request->query('status');
        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(5, min(100, $perPage));

        $query = Rental::with(['client', 'car'])->latest();

        // Search by client name / phone / email, car plate / brand, or destination
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->orWhereHas('client', function ($c) use ($q) {
                        $c->where('name',  'like', "%{$q}%")
                          ->orWhere('phone', 'like', "%{$q}%")
                          ->orWhere('email', 'like', "%{$q}%");
                    })
                    ->orWhereHas('car', function ($c) use ($q) {
                        $c->where('plate_number', 'like', "%{$q}%")
                          ->orWhere('brand',       'like', "%{$q}%");
                    })
                    ->orWhere('destinationFrom', 'like', "%{$q}%")
                    ->orWhere('destinationTo',   'like', "%{$q}%");
            });
        }

        // Status filter
        $allowedStatuses = ['pending', 'ongoing', 'completed', 'cancelled'];
        if ($status && in_array($status, $allowedStatuses, true)) {
            $query->where('status', $status);
        }

        $bookings = $query->paginate($perPage)->withQueryString();

        return view('admin.booking.booking', [
            'bookings'       => $bookings,
            'q'              => $q,
            'status'         => $status,
            'perPage'        => $perPage,
            'totalBookings'  => Rental::count(),
            'pendingCount'   => Rental::pending()->count(),
            'ongoingCount'   => Rental::ongoing()->count(),
            'completedCount' => Rental::completed()->count(),
            'cancelledCount' => Rental::cancelled()->count(),
        ]);
    }

    // =========================================================================
    // SHOW — JSON for view modal
    // =========================================================================

    public function show(int $id): JsonResponse
    {
        $booking = Rental::with(['client', 'car', 'payments'])->findOrFail($id);
        $booking->payment_status = $this->resolvePaymentStatus($booking);

        return response()->json($booking);
    }

    // =========================================================================
    // APPROVE — pending → ongoing
    // =========================================================================

    public function approve(Request $request, int $id): JsonResponse
    {
        try {
            $booking = Rental::findOrFail($id);

            if ($booking->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending bookings can be approved.',
                ], 422);
            }

            $validated = $request->validate([
                'destination_amount' => 'required|numeric|min:0',
                'discount_amount'    => 'nullable|numeric|min:0',
                'final_amount'       => 'required|numeric|min:0',
            ]);

            $booking->update([
                'destination_amount' => $validated['destination_amount'],
                'discount_amount'    => $validated['discount_amount'] ?? 0,
                'final_amount'       => $validated['final_amount'],
                'status'             => 'ongoing',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking approved successfully.',
                'data'    => $booking,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // COMPLETE — ongoing → completed (admin confirms car returned)
    //
    //  Guards: booking must be "ongoing" AND fully paid.
    //  Car is released automatically by Rental::boot() on status change.
    // =========================================================================

    public function complete(int $id): JsonResponse
    {
        try {
            $booking = Rental::with(['payments', 'car'])->findOrFail($id);

            if ($booking->status !== 'ongoing') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only ongoing bookings can be marked as completed.',
                ], 422);
            }

            $paymentStatus = $this->resolvePaymentStatus($booking);

            if (! $paymentStatus['is_paid']) {
                return response()->json([
                    'success'        => false,
                    'message'        => $paymentStatus['reason'],
                    'payment_status' => $paymentStatus,
                ], 422);
            }

            $booking->update(['status' => 'completed', 'returned_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Booking completed. Car has been returned and is now available.',
                'data'    => $booking,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Booking not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // REJECT — pending or ongoing → cancelled
    //  Car is released automatically by Rental::boot() on status change.
    // =========================================================================

    public function reject(int $id): JsonResponse
    {
        try {
            $booking = Rental::with('car')->findOrFail($id);

            if (! in_array($booking->status, ['pending', 'ongoing'], true)) {
                return response()->json(['success' => false, 'message' => 'This booking cannot be rejected.'], 422);
            }

            $booking->update(['status' => 'cancelled']);

            return response()->json([
                'success' => true,
                'message' => 'Booking has been rejected and cancelled. Car is now available.',
                'data'    => $booking,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Booking not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // PRIVATE — build payment status summary for a booking
    // =========================================================================

    private function resolvePaymentStatus(Rental $booking): array
    {
        $finalAmount = (float) $booking->final_amount;
        $totalPaid   = (float) $booking->payments->where('status', 'completed')->sum('amount');
        $remaining   = max(0, $finalAmount - $totalPaid);

        if ($finalAmount > 0 && $totalPaid >= $finalAmount) {
            return [
                'is_paid'      => true,
                'type'         => 'full_payment',
                'amount_paid'  => $totalPaid,
                'final_amount' => $finalAmount,
                'remaining'    => 0,
                'reason'       => null,
            ];
        }

        if ($totalPaid > 0) {
            return [
                'is_paid'      => false,
                'type'         => 'partial',
                'amount_paid'  => $totalPaid,
                'final_amount' => $finalAmount,
                'remaining'    => $remaining,
                'reason'       => 'There is a remaining balance of ₱' . number_format($remaining, 2)
                                . '. Full payment must be settled before the car can be returned.',
            ];
        }

        return [
            'is_paid'      => false,
            'type'         => 'none',
            'amount_paid'  => 0,
            'final_amount' => $finalAmount,
            'remaining'    => $finalAmount,
            'reason'       => 'No completed payment found. Full payment of ₱'
                            . number_format($finalAmount, 2)
                            . ' must be settled before the car can be returned.',
        ];
    }


    public function pendingCount()
    {
        $count = \App\Models\Rental::where('status', 'pending')->count();
    
        return response()->json(['count' => $count]);
    }
 
}