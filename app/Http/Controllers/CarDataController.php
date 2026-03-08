<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CarDataController extends Controller
{
    /**
     * Return a filtered list of cars for the landing page.
     *
     * This endpoint is consumed by public JS on the home page and is designed
     * to be:
     * - Fast: uses Laravel's cache layer (typically Redis when configured).
     * - Safe: falls back to a direct database query if the cache store fails.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $capacity = $request->integer('capacity');
        $limit = $request->integer('limit');

        $filters = [
            'status' => $status,
            'capacity' => $capacity,
            'limit' => $limit,
        ];

        ksort($filters);

        $cacheKey = 'cars:list:' . md5(json_encode($filters));
        $ttlSeconds = 300; // 5 minutes

        try {
            $cars = Cache::tags(['cars'])->remember(
                $cacheKey,
                $ttlSeconds,
                function () use ($status, $capacity, $limit) {
                    return $this->buildCarQuery($status, $capacity, $limit)
                        ->get()
                        ->map(function (Car $car) {
                            return [
                                'id' => $car->id,
                                'brand' => $car->brand,
                                'model' => $car->model,
                                'year' => $car->year,
                                'capacity' => $car->capacity,
                                'status' => $car->status,
                                'rental_price_per_day' => (float) $car->rental_price_per_day,
                                'image_path' => $car->image_path,
                            ];
                        })
                        ->all();
                }
            );
        } catch (\Throwable $e) {
            Log::warning('Car cache unavailable, falling back to DB', [
                'error' => $e->getMessage(),
            ]);

            $cars = $this->buildCarQuery($status, $capacity, $limit)
                ->get()
                ->map(function (Car $car) {
                    return [
                        'id' => $car->id,
                        'brand' => $car->brand,
                        'model' => $car->model,
                        'year' => $car->year,
                        'capacity' => $car->capacity,
                        'status' => $car->status,
                        'rental_price_per_day' => (float) $car->rental_price_per_day,
                        'image_path' => $car->image_path,
                    ];
                })
                ->all();
        }

        return response()->json([
            'success' => true,
            'data' => $cars,
        ]);
    }

    /**
     * Build the base query for car listings used by this endpoint.
     */
    protected function buildCarQuery(?string $status, ?int $capacity, ?int $limit)
    {
        $query = Car::query();

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($capacity)) {
            $query->where('capacity', '>=', $capacity);
        }

        if (!empty($limit) && $limit > 0) {
            $query->limit($limit);
        }

        return $query->orderBy('brand')->orderBy('model');
    }
}

