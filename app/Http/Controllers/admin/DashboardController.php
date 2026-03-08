<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\User;
use DateInterval;
use DatePeriod;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $totalUsers = User::count();
        $availableCars = Car::where('status', 'available')->count();
        $activeBookings = Rental::whereIn('status', ['pending', 'ongoing'])->count();
        $totalRevenue = Payment::where('status', 'completed')->sum('amount');

        $pendingBookings = Rental::where('status', 'pending')->count();
        $ongoingBookings = Rental::where('status', 'ongoing')->count();
        $completedBookings = Rental::where('status', 'completed')->count();
        $cancelledBookings = Rental::where('status', 'cancelled')->count();

        $recentBookings = Rental::with(['client', 'car'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $carStatusCounts = Car::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $totalCars = array_sum($carStatusCounts);

        return view('dashboard', compact(
            'totalUsers',
            'availableCars',
            'activeBookings',
            'totalRevenue',
            'recentBookings',
            'carStatusCounts',
            'totalCars',
            'pendingBookings',
            'ongoingBookings',
            'completedBookings',
            'cancelledBookings'
        ));
    }

    /**
     * JSON daily stats for the dashboard chart (last 7 days).
     */
    public function revenueData(Request $request)
    {
        [$labels, $revenue, $commission, $bookings] = $this->buildDailyStats(7);

        return response()->json([
            'success' => true,
            'labels' => $labels,
            'revenue' => $revenue,
            'commission' => $commission,
            'bookings' => $bookings,
        ]);
    }

    /**
     * Build daily revenue, commission, and booking counts for the last N days.
     */
    protected function buildDailyStats(int $days = 7): array
    {
        $chartEnd = now()->endOfDay();
        $chartStart = $chartEnd->copy()->subDays($days - 1)->startOfDay();

        $rawRevenue = Payment::where('status', 'completed')
            ->whereBetween('payment_date', [$chartStart, $chartEnd])
            ->selectRaw('DATE(payment_date) as day, SUM(amount) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->all();

        $rawBookings = Rental::whereBetween('created_at', [$chartStart, $chartEnd])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->all();

        $labels = [];
        $revenueSeries = [];
        $commissionSeries = [];
        $bookingSeries = [];

        $rate = (float) config('app.platform_commission_rate', 0.20);

        $period = new DatePeriod(
            $chartStart->copy()->startOfDay(),
            new DateInterval('P1D'),
            $chartEnd->copy()->addDay()->startOfDay()
        );

        foreach ($period as $date) {
            $dayKey = $date->format('Y-m-d');
            $value = (float) ($rawRevenue[$dayKey] ?? 0);
            $bookingsCount = (int) ($rawBookings[$dayKey] ?? 0);

            $labels[] = $date->format('M d');
            $revenueSeries[] = round($value, 2);
            $commissionSeries[] = round($value * $rate, 2);
            $bookingSeries[] = $bookingsCount;
        }

        return [$labels, $revenueSeries, $commissionSeries, $bookingSeries];
    }
}

