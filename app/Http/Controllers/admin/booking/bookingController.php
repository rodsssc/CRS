<?php

namespace App\Http\Controllers\admin\booking;

use App\Http\Controllers\Controller;
use App\Models\Rental;
use Illuminate\Http\Request;

class bookingController extends Controller
{
    public function index(){
    $bookings = Rental::all();
    $pendingCount = Rental::pending()->count();
    $ongoingCount = Rental::ongoing()->count();
    $completedCount = Rental::completed()->count();
    $cancelledCount = Rental::cancelled()->count();
    $totalBookings = Rental::count();
    
    return view('admin.booking.booking', compact('bookings', 'pendingCount', 'ongoingCount', 'completedCount', 'cancelledCount', 'totalBookings'));
    }


   public function show($id)
    {
        $booking = Rental::with(['client', 'car'])->findOrFail($id);
       
        return response()->json($booking);
    }


    public function approve(Request $request , $id){
       

        try{
             $booking = Rental::findOrFail($id);

             $validated = $request->validate([
                'destination_amount' => 'required|numeric|min:0',
                'discount_amount' => 'nullable|numeric|min:0',
                'final_amount' => 'required|numeric|min:0',
             ]);


             $booking->update([
            'destination_amount' => $validated['destination_amount'],
            'discount_amount'    => $validated['discount_amount'] ?? 0,
            'final_amount'       => $validated['final_amount'],
            'status'             => 'ongoing',
            
            ]);

            return response() -> json([
                'data' => $booking,
                'message' => 'Booking approved successfully.',
                'success' => true
            ],202);
            

        }catch(\Illuminate\Validation\ValidationException $e){

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ],422);


        }catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e){
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
                
            ],404);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
    }
    }
    



}
