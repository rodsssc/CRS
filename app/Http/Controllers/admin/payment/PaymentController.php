<?php

namespace App\Http\Controllers\admin\payment;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Display all payments with filtering, search, and pagination.
     */
    public function index(Request $request)
    {
        $q             = trim((string) $request->query('q', ''));
        $status        = $request->query('status');
        $paymentMethod = $request->query('payment_method');
        $perPage       = (int) $request->query('per_page', 10);
        $perPage       = max(5, min(100, $perPage));

        $query = Payment::with(['rental.client', 'rental.car', 'processedBy']);

        if ($q !== '') {
            $query->where(function ($q2) use ($q) {
                $q2->orWhereHas('rental.client', function ($c) use ($q) {
                        $c->where('name',  'like', "%{$q}%")
                          ->orWhere('email', 'like', "%{$q}%")
                          ->orWhere('phone', 'like', "%{$q}%");
                    })
                    ->orWhereHas('rental.car', function ($c) use ($q) {
                        $c->where('plate_number', 'like', "%{$q}%")
                          ->orWhere('brand',       'like', "%{$q}%");
                    });
            });
        }

        $allowedStatuses = ['pending', 'completed', 'failed'];
        if ($status && in_array($status, $allowedStatuses, true)) {
            $query->where('status', $status);
        }

        $allowedMethods = ['credit_card', 'gcash', 'maya', 'bank_transfer', 'cash'];
        if ($paymentMethod && in_array($paymentMethod, $allowedMethods, true)) {
            $query->where('payment_method', $paymentMethod);
        }

        $payments = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();

        // Remaining balance per rental for the table column
        $rentalIds  = $payments->pluck('rental_id')->unique()->filter()->values();
        $totalsPaid = [];
        if ($rentalIds->isNotEmpty()) {
            $totalsPaid = Payment::whereIn('rental_id', $rentalIds)
                ->where('status', 'completed')
                ->selectRaw('rental_id, sum(amount) as total')
                ->groupBy('rental_id')
                ->pluck('total', 'rental_id')
                ->map(fn ($v) => (float) $v)
                ->all();
        }

        // Global stats
        $allPayments = Payment::all();
        $stats = [
            'total'        => $allPayments->count(),
            'completed'    => $allPayments->where('status', 'completed')->count(),
            'pending'      => $allPayments->where('status', 'pending')->count(),
            'failed'       => $allPayments->where('status', 'failed')->count(),
            'total_amount' => $allPayments->sum('amount'),
        ];

        // Rentals available for payment recording (pending or ongoing only)
        $rentals = Rental::whereIn('status', ['pending', 'ongoing'])
            ->with(['client', 'car'])
            ->get();

        // Map of rental_id => total paid — used by the Add Payment modal balance banner
        $rentalsPaidMap = Payment::whereIn('rental_id', $rentals->pluck('id'))
            ->where('status', 'completed')
            ->selectRaw('rental_id, sum(amount) as total')
            ->groupBy('rental_id')
            ->pluck('total', 'rental_id')
            ->map(fn ($v) => (float) $v)
            ->all();

        $paymentMethods = [
            'cash'  => 'Cash',
            'gcash' => 'GCash',
            'maya'  => 'Maya',
        ];

        $preselectedBookingId = $request->query('booking_id');
        $preselectedRental    = null;
        if ($preselectedBookingId) {
            $preselectedRental = Rental::with(['client', 'car'])->find($preselectedBookingId);
        }

        return view('admin.payment.payment', compact(
            'payments', 'stats', 'rentals', 'paymentMethods',
            'status', 'paymentMethod', 'q', 'perPage',
            'preselectedBookingId', 'preselectedRental',
            'totalsPaid', 'rentalsPaidMap'
        ));
    }

    /**
     * Store a newly created payment.
     *
     * IMPORTANT: Even when a payment fully settles the balance, the booking
     * status stays as-is ("pending" or "ongoing"). Booking completion happens
     * exclusively when the admin clicks "Car Returned" in BookingController.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'rental_id'      => 'required|exists:rentals,id',
                'amount'         => 'required|numeric|min:0.01',
                'payment_method' => 'required|in:cash,gcash,credit_card,bank_transfer,maya',
                'payment_type'   => 'required|in:downpayment,full_payment,full,partial',
                'status'         => 'nullable|in:pending,completed,failed',
                'notes'          => 'nullable|string|max:500',
            ]);

            $rental = Rental::findOrFail($validated['rental_id']);

            if (! in_array($rental->status, ['pending', 'ongoing'], true)) {
                $msg = 'Payments can only be recorded for pending or ongoing bookings. '
                     . 'This booking is currently "' . $rental->status . '".';
                return $this->errorResponse($request, $msg, ['rental_id' => [$msg]], 403);
            }

            $remainingBalance = $rental->remainingBalance();
            $enteredAmount    = (float) $validated['amount'];

            if ($enteredAmount > $remainingBalance + 0.009) {
                $msg = 'Amount exceeds the remaining balance of ₱' . number_format($remainingBalance, 2) . '.';
                return $this->errorResponse($request, $msg, ['amount' => [$msg]], 422);
            }

            if ($validated['payment_type'] === 'full_payment'
                && abs($enteredAmount - $remainingBalance) > 0.009
            ) {
                $msg = '"Full Payment" requires ₱' . number_format($remainingBalance, 2)
                     . ' to settle the remaining balance. '
                     . 'Use "Down Payment" for a partial payment.';
                return $this->errorResponse($request, $msg, ['amount' => [$msg]], 422);
            }

            $paymentStatus = $validated['status'] ?? 'completed';

            $payment = Payment::create([
                'rental_id'      => $validated['rental_id'],
                'processed_by'   => Auth::id(),
                'amount'         => $enteredAmount,
                'payment_method' => $validated['payment_method'],
                'payment_type'   => $validated['payment_type'],
                'status'         => $paymentStatus,
                'payment_date'   => $paymentStatus === 'completed' ? now() : null,
                'notes'          => $validated['notes'] ?? null,
            ]);

            // ── DO NOT touch booking status here ─────────────────────────────
            // Booking moves to "completed" ONLY when admin confirms car return.
            // See BookingController@complete.
            // ─────────────────────────────────────────────────────────────────

            $message = 'Payment of ₱' . number_format($enteredAmount, 2) . ' recorded successfully!';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data'    => $payment->load(['rental', 'processedBy']),
                ], 201);
            }

            return redirect()->route('admin.payment.index')->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            $msg = 'Something went wrong: ' . $e->getMessage();
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 500);
            }
            return redirect()->back()->withErrors(['error' => $msg])->withInput();
        }
    }

    /**
     * Mark a payment as completed.
     * Does NOT alter booking status — only the admin can complete a booking.
     */
    public function markCompleted($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->update(['status' => 'completed', 'payment_date' => now()]);

        return back()->with('success', 'Payment marked as completed!');
    }

    /**
     * Mark a payment as failed.
     */
    public function markFailed($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->update(['status' => 'failed']);

        return back()->with('success', 'Payment marked as failed.');
    }

    /**
     * Delete a pending payment.
     */
    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);

        if ($payment->status !== 'pending') {
            return back()->with('error', 'Only pending payments can be deleted.');
        }

        $payment->delete();

        return redirect()->route('admin.payment.index')->with('success', 'Payment deleted successfully.');
    }

    // =========================================================================
    // PRIVATE
    // =========================================================================

    private function errorResponse(Request $request, string $message, array $errors, int $status)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'message' => $message, 'errors' => $errors], $status);
        }
        return redirect()->back()->withErrors($errors)->withInput();
    }
}