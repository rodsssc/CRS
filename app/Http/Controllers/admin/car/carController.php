<?php

namespace App\Http\Controllers\admin\car;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Exception;

class carController extends Controller
{
    /**
     * Display a listing of cars with search, filter, and pagination
     */
    public function index(Request $request)
    {
        $q            = trim((string) $request->query('q', ''));
        $status       = $request->query('status');
        $transmission = $request->query('transmission_type');
        $fuelType     = $request->query('fuel_type');
        $perPage      = (int) $request->query('per_page', 10);
        $perPage      = max(5, min(100, $perPage));

        $query = Car::with('owner');

        // Search by plate number, brand, model, or owner name
        if ($q !== '') {
            $query->where(function ($carQuery) use ($q) {
                $carQuery
                    ->where('plate_number', 'like', "%{$q}%")
                    ->orWhere('brand',       'like', "%{$q}%")
                    ->orWhere('model',       'like', "%{$q}%")
                    ->orWhere('color',       'like', "%{$q}%")
                    ->orWhereHas('owner', function ($ownerQuery) use ($q) {
                        $ownerQuery->where('name', 'like', "%{$q}%");
                    });
            });
        }

        // Filter by status
        $allowedStatuses = ['available', 'unavailable', 'rented', 'maintenance'];
        if ($status && in_array($status, $allowedStatuses, true)) {
            $query->where('status', $status);
        }

        // Filter by transmission type
        $allowedTransmissions = ['manual', 'automatic', 'cvt'];
        if ($transmission && in_array($transmission, $allowedTransmissions, true)) {
            $query->where('transmission_type', $transmission);
        }

        // Filter by fuel type
        $allowedFuelTypes = ['gasoline', 'diesel', 'electric', 'hybrid'];
        if ($fuelType && in_array($fuelType, $allowedFuelTypes, true)) {
            $query->where('fuel_type', $fuelType);
        }

        $cars = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();

        // Stats always from full table (unaffected by search/filter)
        $allCars = Car::all();
        $stats = [
            'total'       => $allCars->count(),
            'available'   => $allCars->where('status', 'available')->count(),
            'rented'      => $allCars->where('status', 'rented')->count(),
            'maintenance' => $allCars->where('status', 'maintenance')->count(),
            'unavailable' => $allCars->where('status', 'unavailable')->count(),
        ];

        // Owners for add/edit dropdowns
        $owners = User::where('role', 'owner')->get();

        return view('admin.cars.car', compact(
            'cars',
            'owners',
            'stats',
            'q',
            'status',
            'transmission',
            'fuelType',
            'perPage'
        ));
    }

    /**
     * Store a newly created car (AJAX)
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'owner_id'             => 'required|integer|exists:users,id',
                'plate_number'         => 'required|string|max:20|unique:cars,plate_number|regex:/^[A-Z0-9\s\-]+$/i',
                'brand'                => 'required|string|max:50',
                'model'                => 'required|string|max:50',
                'year'                 => 'required|integer|min:1900|max:' . (date('Y') + 1),
                'color'                => 'required|string|max:30',
                'capacity'             => 'required|integer|min:1|max:50',
                'transmission_type'    => ['required', Rule::in(['manual', 'automatic', 'cvt'])],
                'fuel_type'            => ['required', Rule::in(['gasoline', 'diesel', 'electric', 'hybrid'])],
                'rental_price_per_day' => 'required|numeric|min:0|max:999999.99',
                'image'                => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            ]);

            if ($request->hasFile('image')) {
                $validated['image_path'] = $request->file('image')->store('cars', 'public');
            }

            $validated['plate_number'] = strtoupper($validated['plate_number']);

            $car = Car::create($validated);

            Cache::flush();

            return response()->json([
                'success' => true,
                'message' => 'Car added successfully!',
                'data'    => $car,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while adding the car.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show a single car (AJAX)
     */
    public function show($id)
    {
        try {
            $car = Car::with('owner')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data'    => $car,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Car not found',
            ], 404);
        }
    }

    /**
     * Return car data for edit modal (AJAX)
     */
    public function edit($id)
    {
        try {
            $car = Car::with('owner')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data'    => $car,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Car not found',
            ], 404);
        }
    }

    /**
     * Update the specified car (AJAX)
     */
    public function update(Request $request, $id)
    {
        try {
            Log::info('Car update request received', [
                'car_id' => $id,
                'data'   => $request->all(),
            ]);

            $car = Car::findOrFail($id);

            $rules = [
                'owner_id'     => 'required|integer|exists:users,id',
                'plate_number' => [
                    'required', 'string', 'max:20',
                    'regex:/^[A-Z0-9\s\-]+$/i',
                    Rule::unique('cars')->ignore($id),
                ],
                'brand'                => 'required|string|max:50',
                'model'                => 'required|string|max:50',
                'year'                 => 'required|integer|min:1900|max:' . (date('Y') + 1),
                'color'                => 'required|string|max:30',
                'capacity'             => 'required|integer|min:1|max:50',
                'transmission_type'    => ['required', Rule::in(['manual', 'automatic', 'cvt'])],
                'fuel_type'            => ['required', Rule::in(['gasoline', 'diesel', 'electric', 'hybrid'])],
                'rental_price_per_day' => 'required|numeric|min:0|max:999999.99',
                'status'               => ['required', Rule::in(['available', 'unavailable', 'rented', 'maintenance'])],
                'image'                => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            ];

            $validated = $request->validate($rules);

            $validated['plate_number'] = strtoupper($validated['plate_number']);

            if ($request->hasFile('image')) {
                if ($car->image_path) {
                    Storage::delete('public/' . $car->image_path);
                }
                $validated['image_path'] = $request->file('image')->store('cars', 'public');
            } else {
                unset($validated['image_path']);
            }

            $car->update($validated);

            Cache::flush();

            Log::info('Car updated successfully', ['car_id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Car updated successfully',
                'data'    => $car,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Car validation failed', [
                'car_id' => $id,
                'errors' => $e->errors(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Car not found',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Car update failed', [
                'car_id' => $id,
                'error'  => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update car: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified car (AJAX)
     */
    public function destroy($id)
    {
        try {
            $car = Car::findOrFail($id);

            if ($car->image_path) {
                Storage::delete('public/' . $car->image_path);
            }

            $car->delete();

            Cache::flush();

            return response()->json([
                'success' => true,
                'message' => 'Car deleted successfully',
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Car not found',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Car deletion failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete car',
            ], 500);
        }
    }
}