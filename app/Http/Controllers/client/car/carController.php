<?php

namespace App\Http\Controllers\client\car;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\Request;

class carController extends Controller
{
    /**
     * Display the car listing page.
     * Initial server-side render with all filter options.
     * JS takes over for live filtering + pagination.
     */
    public function index(Request $request)
    {
        return view('client.car.car');
    }

    /**
     * GET /client/car/data
     * JSON endpoint — search, filter, sort, paginate.
     *
     * Query params:
     *   ?search=toyota
     *   ?status=available|rented|maintenance
     *   ?capacity=5            (exact match)
     *   ?sort=price_asc|price_desc|name_asc|name_desc
     *   ?page=1
     *   ?per_page=12
     */
    public function data(Request $request)
    {
        $search   = trim($request->query('search', ''));
        $status   = $request->query('status', '');
        $capacity = $request->integer('capacity');
        $sort     = $request->query('sort', 'name_asc');
        $perPage  = min($request->integer('per_page', 12), 48);

        $query = Car::query();

        // ── Search ────────────────────────────────────────────────────────
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('brand', 'like', '%' . $search . '%')
                  ->orWhere('model', 'like', '%' . $search . '%');
            });
        }

        // ── Status filter ─────────────────────────────────────────────────
        if ($status !== '') {
            $query->where('status', $status);
        }

        // ── Capacity filter ───────────────────────────────────────────────
        if ($capacity > 0) {
            $query->where('capacity', '>=', $capacity);
        }

        // ── Sort ──────────────────────────────────────────────────────────
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('rental_price_per_day', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('rental_price_per_day', 'desc');
                break;
            case 'name_desc':
                $query->orderBy('brand', 'desc')->orderBy('model', 'desc');
                break;
            default: // name_asc
                $query->orderBy('brand', 'asc')->orderBy('model', 'asc');
        }

        // ── Paginate ──────────────────────────────────────────────────────
        $paginated = $query->paginate($perPage);

        // ── Format cars ───────────────────────────────────────────────────
        $cars = collect($paginated->items())->map(function (Car $car) {
            return [
                'id'                   => $car->id,
                'brand'                => $car->brand,
                'model'                => $car->model,
                'year'                 => $car->year,
                'color'                => $car->color,
                'capacity'             => $car->capacity,
                'status'               => $car->status,
                'transmission_type'    => $car->transmission_type,
                'fuel_type'            => $car->fuel_type,
                'rental_price_per_day' => (float) $car->rental_price_per_day,
                'image_path'           => $car->image_path,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $cars,
            'meta'    => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'from'         => $paginated->firstItem(),
                'to'           => $paginated->lastItem(),
            ],
        ]);
    }

    /**
     * GET /client/car/{id}
     * Returns single car JSON for the View modal.
     */
    public function show($id)
    {
        $car = Car::findOrFail($id);

        return response()->json([
            'cars'    => $car,
            'message' => 'Successfully Retrieved',
        ], 200);
    }
}