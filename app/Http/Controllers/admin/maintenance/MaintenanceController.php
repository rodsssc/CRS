<?php

namespace App\Http\Controllers\admin\maintenance;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status');
        $carId = $request->query('car_id');
        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(5, min(100, $perPage));

        $query = Maintenance::with(['car.owner', 'creator']);

        if ($q !== '') {
            $query->where(function ($maintenanceQuery) use ($q) {
                $maintenanceQuery
                    ->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhereHas('car', function ($carQuery) use ($q) {
                        $carQuery
                            ->where('plate_number', 'like', "%{$q}%")
                            ->orWhere('brand', 'like', "%{$q}%")
                            ->orWhere('model', 'like', "%{$q}%");
                    })
                    ->orWhereHas('creator', function ($userQuery) use ($q) {
                        $userQuery->where('name', 'like', "%{$q}%");
                    });
            });
        }

        $allowedStatuses = ['scheduled', 'in_progress', 'completed', 'cancelled'];
        if ($status && in_array($status, $allowedStatuses, true)) {
            $query->where('status', $status);
        } else {
            $status = null;
        }

        if ($carId) {
            $query->where('car_id', $carId);
        }

        $maintenances = $query
            ->orderByDesc('service_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $cars = Car::orderBy('plate_number')->get();

        $stats = [
            'total' => Maintenance::count(),
            'scheduled' => Maintenance::where('status', 'scheduled')->count(),
            'in_progress' => Maintenance::where('status', 'in_progress')->count(),
            'completed' => Maintenance::where('status', 'completed')->count(),
            'cancelled' => Maintenance::where('status', 'cancelled')->count(),
        ];

        return view('admin.maintenance.maintenance', compact('maintenances', 'cars', 'stats', 'q', 'status', 'carId', 'perPage'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'car_id' => 'required|exists:cars,id',
                'title' => 'required|string|max:120',
                'description' => 'nullable|string|max:5000',
                'service_date' => 'required|date',
                'cost' => 'nullable|numeric|min:0',
                'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            ]);

            $validated['created_by'] = Auth::id();
            $validated['cost'] = $validated['cost'] ?? 0;

            $maintenance = Maintenance::create($validated);

            // Update related car status based on maintenance status
            if ($maintenance->car) {
                if (in_array($maintenance->status, ['scheduled', 'in_progress'], true)) {
                    $maintenance->car->update(['status' => 'maintenance']);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Maintenance record created successfully.',
                'data' => $maintenance,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create maintenance record.',
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $maintenance = Maintenance::with(['car.owner', 'creator'])->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $maintenance,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Maintenance record not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to load maintenance record.'], 500);
        }
    }

    public function edit($id)
    {
        try {
            $maintenance = Maintenance::with(['car.owner', 'creator'])->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $maintenance,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Maintenance record not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to load maintenance record.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $maintenance = Maintenance::findOrFail($id);

            $validated = $request->validate([
                'car_id' => 'required|exists:cars,id',
                'title' => 'required|string|max:120',
                'description' => 'nullable|string|max:5000',
                'service_date' => 'required|date',
                'cost' => 'nullable|numeric|min:0',
                'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            ]);

            $validated['cost'] = $validated['cost'] ?? 0;

            $maintenance->update($validated);

            // Keep car status in sync with maintenance status
            $maintenance->load('car');
            if ($maintenance->car) {
                if (in_array($maintenance->status, ['scheduled', 'in_progress'], true)) {
                    $maintenance->car->update(['status' => 'maintenance']);
                } elseif (in_array($maintenance->status, ['completed', 'cancelled'], true) && $maintenance->car->status === 'maintenance') {
                    $maintenance->car->update(['status' => 'available']);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Maintenance record updated successfully.',
                'data' => $maintenance,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Maintenance record not found.'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update maintenance record.'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $maintenance = Maintenance::findOrFail($id);
            $maintenance->delete();

            return response()->json([
                'success' => true,
                'message' => 'Maintenance record deleted successfully.',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Maintenance record not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete maintenance record.'], 500);
        }
    }
}

