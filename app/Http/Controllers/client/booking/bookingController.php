<?php

namespace App\Http\Controllers\client\booking;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Exception;

class bookingController extends Controller
{
    public function index()
    {
        return view('client.booking.booking');
    }

    /**
     * Return the authenticated client's bookings as JSON.
     *
     * This is used by the client bookings page to render a dynamic list with
     * loading and error states on the frontend.
     */
   public function list(Request $request)
{
    $user = $request->user();

    $bookings = Rental::with('car')
        ->where('client_id', $user->id)
        ->orderByDesc('created_at')
        ->get()
        ->map(function (Rental $rental) {
            $car = $rental->car;

            return [
                'id'               => $rental->id,
                'status'           => $rental->status,
                'destination_from' => $rental->destinationFrom,
                'destination_to'   => $rental->destinationTo,
                'rental_start_date'=> optional($rental->rental_start_date)->toDateTimeString(),
                'rental_end_date'  => optional($rental->rental_end_date)->toDateTimeString(),
                'total_days'       => $rental->total_days,
                'total_hours'      => $rental->total_hours,
                'car_amount'       => (float) ($rental->car_amount        ?? 0),
                'destination_amount'=> (float) ($rental->destination_amount ?? 0),
                'discount_amount'  => (float) ($rental->discount_amount   ?? 0),
                'final_amount'     => (float) ($rental->final_amount      ?? 0),
                'car' => $car ? [
                    'id'           => $car->id,
                    'brand'        => $car->brand,
                    'model'        => $car->model,
                    'plate_number' => $car->plate_number,
                    'image_path'   => $car->image_path,
                ] : null,
            ];
        })
        ->all();

    return response()->json([
        'success' => true,
        'data'    => $bookings,
    ]);
}
    
    public function store(Request $request)
    {
        try {
            // Validate incoming request
            $car = Car::find($request->input('carId'));

            if (!$car) {
                return response()->json(['success' => false, 'message' => 'Car not found.'], 404);
            }

            // Optional: prevent double booking
            if ($car->status !== 'available') {
                return response()->json(['success' => false, 'message' => 'Car is not available for booking.'], 409);
            }
            
            $validated = $request->validate([
                'carId' => 'required|exists:cars,id',
                'client_id' => 'required|exists:users,id',
                'destinationFrom' => 'required|string|max:255',
                'destinationTo' => 'required|string|max:255',
                'rental_start_date' => 'required|date|after_or_equal:today',
                'rental_end_date' => 'required|date|after:rental_start_date',
                'total_days' => 'nullable|integer|min:1',
                'total_hours' => 'nullable|integer|min:1',
                'car_amount' => 'nullable|integer|min:1', // Changed to nullable since we'll calculate it
            ]);

            // Calculate rental duration
            $startDate = Carbon::parse($validated['rental_start_date']);
            $endDate = Carbon::parse($validated['rental_end_date']);

            // Calculate total hours between dates
            $totalHours = $startDate->diffInHours($endDate);
            
            // Calculate total days (rounded up for partial days)
            // If hours are exactly divisible by 24, use exact days, otherwise round up
            $totalDays = ceil($totalHours / 24);
            
            // Store calculated values
            $validated['total_hours'] = $totalHours;
            $validated['total_days'] = $totalDays;

            // Calculate car amount based on 24-hour pricing
            // Using rental_price_per_day from the car model
            $carAmount = $totalDays * $car->rental_price_per_day;
            
            // Alternative calculation if you want to charge by the hour:
            // $carAmount = $totalHours * ($car->rental_price_per_day / 24);

            $bookingData = [
                'car_id' => $validated['carId'],
                'client_id' => $validated['client_id'],
                'destinationFrom' => $validated['destinationFrom'],
                'destinationTo' => $validated['destinationTo'],
                'rental_start_date' => $validated['rental_start_date'],
                'rental_end_date' => $validated['rental_end_date'],
                'total_days' => $validated['total_days'],
                'total_hours' => $validated['total_hours'],
                'car_amount' => $carAmount, // Add the calculated amount
                'status' => 'pending'
            ];

            $booking = null;
            \DB::transaction(function () use ($bookingData, $car, &$booking) {
                $booking = Rental::create($bookingData);
                $car->update(['status' => 'rented']);
            });

            return response()->json([
                'success' => true,
                'message' => 'Booking request submitted successfully. Waiting for confirmation.',
                'data' => $booking,
                'amount_details' => [
                    'total_days' => $totalDays,
                    'total_hours' => $totalHours,
                    'price_per_day' => $car->rental_price_per_day,
                    'total_amount' => $carAmount
                ]
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            // Log the error for debugging
            \Log::error('Booking creation error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while submitting the booking.',
                'error' => config('app.debug') ? $e->getMessage() : 'Server error'
            ], 500);
        }
    }
}