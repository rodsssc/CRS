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
     * Display all payments with filtering and search
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status');
        $paymentMethod = $request->query('payment_method');
        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(5, min(100, $perPage));

        $query = Payment::with(['rental.client', 'rental.car', 'processedBy']);

        // Search
        if ($q !== '') {
            $query->where(function ($paymentQuery) use ($q) {
                $paymentQuery
                    ->orWhereHas('rental.client', function ($clientQuery) use ($q) {
                        $clientQuery
                            ->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%")
                            ->orWhere('phone', 'like', "%{$q}%");
                    })
                    ->orWhereHas('rental.car', function ($carQuery) use ($q) {
                        $carQuery
                            ->where('plate_number', 'like', "%{$q}%")
                            ->orWhere('brand', 'like', "%{$q}%");
                    });
            });
        }

        // Filter by status
        $allowedStatuses = ['pending', 'completed', 'failed'];
        if ($status && in_array($status, $allowedStatuses, true)) {
            $query->where('status', $status);
        }

        // Filter by payment method
        $allowedMethods = ['credit_card', 'gcash', 'maya', 'bank_transfer', 'cash'];
        if ($paymentMethod && in_array($paymentMethod, $allowedMethods, true)) {
            $query->where('payment_method', $paymentMethod);
        }

        $payments = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();

        // Remaining balance per rental (for table and detail view)
        $rentalIds = $payments->pluck('rental_id')->unique()->filter()->values();
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

        // Stats
        $allPayments = Payment::all();
        $stats = [
            'total' => $allPayments->count(),
            'completed' => $allPayments->where('status', 'completed')->count(),
            'pending' => $allPayments->where('status', 'pending')->count(),
            'failed' => $allPayments->where('status', 'failed')->count(),
            'total_amount' => $allPayments->sum('amount'),
        ];

        $rentals = Rental::where('status', 'pending')
            ->orWhere('status', 'ongoing')
            ->with(['client', 'car'])
            ->get();

        $paymentMethods = [
            'cash' => 'Cash',
            'gcash' => 'GCash',
            'maya' => 'Maya',
        ];

        $preselectedBookingId = $request->query('booking_id');
        $preselectedRental = null;
        if ($preselectedBookingId) {
            $preselectedRental = Rental::with(['client', 'car'])->find($preselectedBookingId);
        }

        return view('admin.payment.index', compact(
            'payments',
            'stats',
            'rentals',
            'paymentMethods',
            'status',
            'paymentMethod',
            'q',
            'perPage',
            'preselectedBookingId',
            'preselectedRental',
            'totalsPaid'
        ));
    }

    /**
     * Store a newly created payment
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'rental_id' => 'required|exists:rentals,id',
                'amount' => 'required|numeric|min:0.01',
                'payment_method' => 'required|in:cash,gcash,credit_card,bank_transfer,maya',
                'payment_type' => 'required|in:downpayment,full_payment,full,partial',
                'status' => 'nullable|in:pending,completed,failed',
                'notes' => 'nullable|string|max:500',
            ]);

            $rental = Rental::findOrFail($validated['rental_id']);

            if (! in_array($rental->status, ['pending', 'ongoing'], true)) {
                $msg = 'Payments can only be recorded for pending or ongoing bookings. This booking is ' . $rental->status . '.';
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 403);
                }
                return redirect()->back()->withErrors(['rental_id' => $msg]);
            }

            $totalPaid = Payment::where('rental_id', $rental->id)
                ->where('status', 'completed')
                ->sum('amount');

            $remainingAmount = $rental->final_amount - $totalPaid;

            if ($validated['amount'] > $remainingAmount) {
                $msg = 'Amount exceeds remaining balance of ₱' . number_format($remainingAmount, 2);
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $msg,
                        'errors' => ['amount' => ['Amount exceeds remaining balance']]
                    ], 422);
                }
                return redirect()->back()->withErrors(['amount' => $msg])->withInput();
            }

            $paymentStatus = $validated['status'] ?? 'completed';

            $payment = Payment::create([
                'rental_id' => $validated['rental_id'],
                'processed_by' => Auth::id(),
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'payment_type' => $validated['payment_type'],
                'status' => $paymentStatus,
                'payment_date' => $paymentStatus === 'completed' ? now() : null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $newTotalPaid = $totalPaid + $validated['amount'];
            if ($newTotalPaid >= $rental->final_amount) {
                $rental->update(['status' => 'completed']);
            }

            $message = 'Payment recorded successfully!';
            if (! $request->expectsJson() && ! $request->ajax()) {
                return redirect()->route('admin.payment.index')->with('success', $message)->withInput();
            }
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $payment->load(['rental', 'processedBy'])
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            if (! $request->expectsJson() && ! $request->ajax()) {
                return redirect()->back()->withErrors($e->errors())->withInput();
            }
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            $msg = 'Something went wrong: ' . $e->getMessage();
            if (! $request->expectsJson() && ! $request->ajax()) {
                return redirect()->back()->withErrors(['error' => $msg])->withInput();
            }
            return response()->json(['success' => false, 'message' => $msg], 500);
        }
    }

    /**
     * Update payment status to completed
     */
    public function markCompleted($id)
    {
        $payment = Payment::findOrFail($id);

        $payment->update([
            'status' => 'completed',
            'payment_date' => now(),
        ]);

        $rental = $payment->rental;
        $totalPaid = Payment::where('rental_id', $rental->id)
            ->where('status', 'completed')
            ->sum('amount');

        if ($totalPaid >= $rental->final_amount) {
            $rental->update(['status' => 'completed']);
        }

        return back()->with('success', 'Payment marked as completed!');
    }

    /**
     * Update payment status to failed
     */
    public function markFailed($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->update(['status' => 'failed']);

        return back()->with('success', 'Payment marked as failed!');
    }

    /**
     * Delete a payment
     */
    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);

        if ($payment->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending payments can be deleted.']);
        }

        $payment->delete();

        return redirect()->route('admin.payment.index')->with('success', 'Payment deleted successfully!');
    }
}

