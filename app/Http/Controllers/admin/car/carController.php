<?php

namespace App\Http\Controllers\admin\car;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Exception;

class carController extends Controller
{
    public function index()
    {
        $cars = Car::all();

        // Fetch only users with 'owner' role
       $owners = User::where('role', 'owner')->get();
        return view('admin.cars.car',compact('owners','cars'));
    }

    public function store(Request $request)
    {
        
        try {
            $validated = $request->validate([
                'owner_id' => 'required|integer|exists:users,id',
                'plate_number' => 'required|string|max:20|unique:cars,plate_number|regex:/^[A-Z0-9\s\-]+$/i',
                'brand' => 'required|string|max:50',
                'model' => 'required|string|max:50',
                'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
                'color' => 'required|string|max:30',
                'capacity' => 'required|integer|min:1|max:50',
                'transmission_type' => ['required', Rule::in(['manual', 'automatic', 'cvt'])],
                'fuel_type' => ['required', Rule::in(['gasoline', 'diesel', 'electric', 'hybrid'])],
                'rental_price_per_day' => 'required|numeric|min:0|max:999999.99',
                'image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048'
            ]);

            // Handle image upload
            if ($request->hasFile('image')) {
                $validated['image_path'] = $request->file('image')->store('cars', 'public');
            }

            // Uppercase plate number
            $validated['plate_number'] = strtoupper($validated['plate_number']);

            // Create car
            $car = Car::create($validated);

            // Invalidate cached car listings so the landing page stays fresh
            Cache::tags(['cars'])->flush();

            return response()->json([
                'success' => true,
                'message' => 'Car added successfully!',
                'data' => $car
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while adding the car.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function show($id)
{
    try {
        $car = Car::with('owner')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $car
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Car not found'
        ], 404);
    }
}

    public function edit($id){
            try {
        $car = Car::with('owner')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $car
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Car not found'
        ], 404);
    }
    }

    public function update(Request $request, $id)
    {
    try {
        // Log incoming request for debugging
        \Illuminate\Support\Facades\Log::info('Car update request received', [
            'car_id' => $id,
            'data' => $request->all()
        ]);

        $car = Car::findOrFail($id);

        // Build validation rules
        $rules = [
            'owner_id' => 'required|integer|exists:users,id',
            'plate_number' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Z0-9\s\-]+$/i',
                Rule::unique('cars')->ignore($id)
            ],
            'brand' => 'required|string|max:50',
            'model' => 'required|string|max:50',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'color' => 'required|string|max:30',
            'capacity' => 'required|integer|min:1|max:50',
            'transmission_type' => ['required', Rule::in(['manual', 'automatic', 'cvt'])],
            'fuel_type' => ['required', Rule::in(['gasoline', 'diesel', 'electric', 'hybrid'])],
            'rental_price_per_day' => 'required|numeric|min:0|max:999999.99',
            'status' => ['required', Rule::in(['available', 'rented', 'maintenance'])],
            'image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048'
        ];

        $validated = $request->validate($rules);

        // Uppercase plate number
        $validated['plate_number'] = strtoupper($validated['plate_number']);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($car->image_path) {
                \Illuminate\Support\Facades\Storage::delete('public/' . $car->image_path);
            }
            
            // Store new image
            $validated['image_path'] = $request->file('image')->store('cars', 'public');
        } else {
            // Keep existing image if no new image uploaded
            unset($validated['image_path']);
        }

        $car->update($validated);

        // Invalidate cached car listings so the landing page stays fresh
        Cache::tags(['cars'])->flush();

        \Illuminate\Support\Facades\Log::info('Car updated successfully', ['car_id' => $id]);

        return response()->json([
            'success' => true,
            'message' => 'Car updated successfully',
            'data' => $car
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        \Illuminate\Support\Facades\Log::warning('Car validation failed', [
            'car_id' => $id,
            'errors' => $e->errors()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Car not found'
        ], 404);

    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Car update failed', [
            'car_id' => $id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to update car: ' . $e->getMessage()
        ], 500);
    }
}

public function destroy($id)
    {
        try {
            $car = Car::findOrFail($id);
            $car->delete();

            // Invalidate cached car listings so the landing page stays fresh
            Cache::tags(['cars'])->flush();

            return response()->json([
                'success' => true,
                'message' => 'Car deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete car'
            ], 500);
        }
    }
}