<?php

namespace App\Http\Controllers\admin\report;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Simple sales report (revenue + commission) for a date range.
     */
    public function sales(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');

        // Default: last 30 days
        $end = $to ? Carbon::parse($to)->endOfDay() : now()->endOfDay();
        $start = $from ? Carbon::parse($from)->startOfDay() : (clone $end)->subDays(29)->startOfDay();

        $payments = Payment::with(['rental.client', 'rental.car'])
            ->where('status', 'completed')
            ->whereBetween('payment_date', [$start, $end])
            ->orderByDesc('payment_date')
            ->paginate(20)
            ->withQueryString();

        $totalRevenue = (float) $payments->getCollection()->sum('amount');
        $totalCommission = (float) $payments->getCollection()->sum('commission');

        // Group by date for chart
        $raw = Payment::where('status', 'completed')
            ->whereBetween('payment_date', [$start, $end])
            ->selectRaw('DATE(payment_date) as day, SUM(amount) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->all();

        $labels = [];
        $revenueSeries = [];
        $commissionSeries = [];

        $period = new \DatePeriod(
            $start->copy()->startOfDay(),
            new \DateInterval('P1D'),
            $end->copy()->addDay()->startOfDay()
        );

        foreach ($period as $date) {
            $key = $date->format('Y-m-d');
            $value = (float) ($raw[$key] ?? 0);
            $labels[] = $date->format('M d');
            $revenueSeries[] = round($value, 2);
            $commissionSeries[] = round($value * 0.20, 2);
        }

        return view('admin.reports.sales', [
            'payments' => $payments,
            'from' => $start,
            'to' => $end,
            'totalRevenue' => $totalRevenue,
            'totalCommission' => $totalCommission,
            'chartLabels' => $labels,
            'chartRevenue' => $revenueSeries,
            'chartCommission' => $commissionSeries,
        ]);
    }

    /**
     * Simple commissions report – reuses sales data, focuses on commission column.
     */
    public function commissions(Request $request)
    {
        // For now, commissions view is same as sales; controller kept simple.
        return $this->sales($request);
    }
}

