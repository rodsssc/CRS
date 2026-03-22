<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * CarDataController
 *
 * Provides public JSON endpoints consumed by the landing page.
 *
 * Routes (web.php):
 *   GET /get-cars         → index()   list / filter cars
 *   GET /get-cars/stats   → stats()   fleet-wide aggregate numbers
 */
class CarDataController extends Controller
{
    // ─── Constants ────────────────────────────────────────────────────────────

    /** Rows returned when ?limit= is omitted. */
    private const DEFAULT_LIMIT = 9;

    /** Hard ceiling — requests above this are silently clamped. */
    private const MAX_LIMIT = 50;

    /** Cache TTL in seconds (5 minutes). */
    private const CACHE_TTL = 300;

    // ─── Public Endpoints ─────────────────────────────────────────────────────

    /**
     * GET /get-cars
     *
     * Returns a filtered list of cars for the landing page fleet grid.
     *
     * Query params:
     *   ?status=available|rented|maintenance
     *   ?capacity=5          minimum seat count (>=)
     *   ?limit=9             max rows; capped at MAX_LIMIT
     */
    public function index(Request $request): JsonResponse
    {
        $status   = $request->query('status');
        $capacity = $request->integer('capacity');
        $limit    = $this->resolveLimit($request->integer('limit'));

        $cars = $this->remember(
            key:      $this->cacheKey('list', compact('status', 'capacity', 'limit')),
            callback: fn () => $this->queryCars($status, $capacity, $limit),
        );

        return response()->json([
            'success' => true,
            'data'    => $cars,
            'meta'    => [
                'count' => count($cars),
                'limit' => $limit,
            ],
        ]);
    }

    /**
     * GET /get-cars/stats
     *
     * Returns fleet-wide aggregate numbers for the hero panel and stats strip.
     * Response is cached independently so it does not vary with filter params.
     *
     * Response shape:
     * {
     *   "success": true,
     *   "data": {
     *     "total":           12,
     *     "available":       8,
     *     "rented":          3,
     *     "maintenance":     1,
     *     "available_pct":   67,
     *     "price_min":       1600.00,
     *     "price_max":       3200.00,
     *     "happy_customers": 1240
     *   }
     * }
     */
    public function stats(): JsonResponse
    {
        $data = $this->remember(
            key:      'cars:stats',
            callback: fn () => $this->aggregateStats(),
        );

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    /**
     * Clamp the requested limit between 1 and MAX_LIMIT.
     */
    private function resolveLimit(int $raw): int
    {
        return $raw > 0
            ? min($raw, self::MAX_LIMIT)
            : self::DEFAULT_LIMIT;
    }

    /**
     * Build a deterministic cache key from a tag and a params array.
     */
    private function cacheKey(string $tag, array $params): string
    {
        ksort($params);

        return "cars:{$tag}:" . md5(json_encode($params));
    }

    /**
     * Attempt a tagged cache remember; fall back to a direct DB call on failure
     * (e.g. when the cache driver does not support tags).
     */
    private function remember(string $key, callable $callback): mixed
    {
        try {
            return Cache::tags(['cars'])->remember($key, self::CACHE_TTL, $callback);
        } catch (\Throwable $e) {
            Log::warning('Car cache miss — falling back to DB', [
                'key'   => $key,
                'error' => $e->getMessage(),
            ]);

            return $callback();
        }
    }

    /**
     * Query cars with optional status / capacity filters.
     *
     * @return array<int, array<string, mixed>>
     */
    private function queryCars(?string $status, int $capacity, int $limit): array
    {
        $query = Car::query();

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if ($capacity > 0) {
            $query->where('capacity', '>=', $capacity);
        }

        return $query
            ->orderBy('brand')
            ->orderBy('model')
            ->limit($limit)
            ->get()
            ->map(fn (Car $car) => $this->formatCar($car))
            ->all();
    }

    /**
     * Compute fleet-wide aggregate stats.
     *
     * @return array<string, mixed>
     */
    private function aggregateStats(): array
    {
        $all = Car::query()
            ->select(['status', 'rental_price_per_day', 'capacity'])
            ->get();

        $total       = $all->count();
        $available   = $all->where('status', 'available')->count();
        $rented      = $all->where('status', 'rented')->count();
        $maintenance = $all->where('status', 'maintenance')->count();

        $availPrices = $all
            ->where('status', 'available')
            ->pluck('rental_price_per_day')
            ->filter(fn ($p) => $p > 0);

        return [
            'total'           => $total,
            'available'       => $available,
            'rented'          => $rented,
            'maintenance'     => $maintenance,
            'available_pct'   => $total > 0 ? (int) round(($available / $total) * 100) : 0,
            'price_min'       => $availPrices->isNotEmpty() ? (float) $availPrices->min() : 0,
            'price_max'       => $availPrices->isNotEmpty() ? (float) $availPrices->max() : 0,
            'happy_customers' => $this->happyCustomers(),
        ];
    }

    /**
     * A simple proxy for the "happy customers" count.
     * Replace the body with a real query / config value as needed.
     *
     * @return int
     */
    private function happyCustomers(): int
    {
        // Example: return Booking::distinct('user_id')->count('user_id');
        return 1000;
    }

    /**
     * Serialize a Car model to the shape expected by the frontend.
     *
     * @return array<string, mixed>
     */
    private function formatCar(Car $car): array
    {
        return [
            'id'                   => $car->id,
            'brand'                => $car->brand,
            'model'                => $car->model,
            'year'                 => $car->year,
            'capacity'             => $car->capacity,
            'status'               => $car->status,
            'rental_price_per_day' => (float) $car->rental_price_per_day,
            'image_path'           => $car->image_path,
        ];
    }
}