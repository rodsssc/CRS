<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\SalesReport;
use App\Models\Rental;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * SalesReportController
 * 
 * Manages sales reports and commission tracking for admin.
 * Only admin users can access this controller.
 * 
 * Features:
 * - View all sales reports with filters
 * - Generate sales reports from rentals
 * - Track admin commission (20% per transaction)
 * - Filter by date range, status, admin
 * - Export reports
 * - View sales analytics dashboard
 * 
 * Commission Calculation:
 * - Admin Commission = Gross Amount * 20%
 * - Net Amount = Gross Amount - Admin Commission
 * 
 * @group Admin Features
 */
class SalesReportController extends Controller
{
    /**
     * Middleware to ensure only admin can access
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin']); // Assumes admin middleware exists
    }

    /**
     * Display all sales reports with filtering
     * 
     * GET /admin/sales-reports
     * 
     * Query Parameters:
     * - q: Search by rental/customer/car details
     * - status: Filter by status (pending, completed, cancelled)
     * - date_from: Start date (YYYY-MM-DD)
     * - date_to: End date (YYYY-MM-DD)
     * - admin_id: Filter by admin user
     * - per_page: Items per page (default 15, max 100)
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Validate and sanitize input
        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $adminId = $request->query('admin_id');
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(5, min(100, $perPage));

        // Build query
        $query = SalesReport::with(['rental.client', 'rental.car', 'admin', 'payment'])
            ->withTrashed();

        // Search across relations
        if ($q !== '') {
            $query->where(function ($q) use ($q) {
                $q->whereHas('rental.client', function ($subQuery) use ($q) {
                    $subQuery->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%");
                })
                    ->orWhereHas('rental.car', function ($subQuery) use ($q) {
                        $subQuery->where('brand', 'like', "%{$q}%")
                            ->orWhere('model', 'like', "%{$q}%")
                            ->orWhere('plate_number', 'like', "%{$q}%");
                    })
                    ->orWhere('id', 'like', "%{$q}%");
            });
        }

        // Filter by status
        $allowedStatuses = ['pending', 'completed', 'cancelled'];
        if ($status && in_array($status, $allowedStatuses, true)) {
            $query->where('status', $status);
        }

        // Filter by date range
        if ($dateFrom) {
            $query->where('report_date', '>=', Carbon::parse($dateFrom)->startOfDay());
        }
        if ($dateTo) {
            $query->where('report_date', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        // Filter by admin
        if ($adminId) {
            $query->where('admin_id', $adminId);
        }

        // Get paginated results
        $reports = $query->orderByDesc('report_date')->paginate($perPage)->withQueryString();

        // Calculate statistics
        $stats = $this->calculateStatistics();
        
        // Get admin users for filter dropdown
        $admins = DB::table('users')->where('role', 'admin')->select('id', 'name')->get();

        return view('admin.sales.index', compact(
            'reports',
            'stats',
            'admins',
            'q',
            'status',
            'dateFrom',
            'dateTo',
            'adminId',
            'perPage'
        ));
    }

    /**
     * Show sales report analytics dashboard
     * 
     * GET /admin/sales-reports/analytics
     * 
     * Displays:
     * - Sales trend chart (last 30 days)
     * - Commission distribution
     * - Top performing rentals
     * - Admin commission breakdown
     * 
     * @return \Illuminate\View\View
     */
    public function analytics()
    {
        $thirtyDaysAgo = now()->subDays(30);

        // Daily sales data for chart
        $dailySales = SalesReport::where('report_date', '>=', $thirtyDaysAgo)
            ->select(
                DB::raw('DATE(report_date) as date'),
                DB::raw('SUM(gross_amount) as gross'),
                DB::raw('SUM(admin_commission) as commission'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Commission by admin
        $commissionByAdmin = SalesReport::where('status', 'completed')
            ->with('admin')
            ->select(
                'admin_id',
                DB::raw('SUM(admin_commission) as total_commission'),
                DB::raw('COUNT(*) as report_count'),
                DB::raw('SUM(gross_amount) as total_gross')
            )
            ->groupBy('admin_id')
            ->orderByDesc('total_commission')
            ->get();

        // Top 10 rentals by commission
        $topRentals = SalesReport::with(['rental.client', 'rental.car'])
            ->orderByDesc('admin_commission')
            ->limit(10)
            ->get();

        // Summary statistics
        $stats = $this->calculateStatistics();

        return view('admin.sales.analytics', compact(
            'dailySales',
            'commissionByAdmin',
            'topRentals',
            'stats'
        ));
    }

    /**
     * Create a new sales report from a rental
     * 
     * POST /admin/sales-reports
     * 
     * Request Body:
     * - rental_id: ID of the rental
     * - payment_id: (optional) Payment ID associated
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'rental_id' => 'required|exists:rentals,id|unique:sales_reports,rental_id',
            'payment_id' => 'nullable|exists:payments,id',
        ]);

        try {
            DB::beginTransaction();

            $rental = Rental::findOrFail($validated['rental_id']);
            $payment = isset($validated['payment_id']) ? Payment::find($validated['payment_id']) : null;

            // Generate the sales report
            $report = SalesReport::generateFromRental($rental, $payment, Auth::user());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sales report created successfully',
                'data' => $report,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create sales report: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Show details of a specific sales report
     * 
     * GET /admin/sales-reports/{id}
     * 
     * @param SalesReport $report
     * @return \Illuminate\View\View
     */
    public function show(SalesReport $report)
    {
        $report->load(['rental.client', 'rental.car', 'admin', 'payment']);

        return view('admin.sales.show', compact('report'));
    }

    /**
     * Update a sales report (admin only)
     * 
     * PUT /admin/sales-reports/{id}
     * 
     * Request Body:
     * - status: pending|completed|cancelled
     * - remarks: Optional notes
     * 
     * @param Request $request
     * @param SalesReport $report
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, SalesReport $report)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,completed,cancelled',
            'remarks' => 'nullable|string|max:500',
        ]);

        try {
            $report->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Sales report updated successfully',
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update sales report: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete a sales report (soft delete)
     * 
     * DELETE /admin/sales-reports/{id}
     * 
     * @param SalesReport $report
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(SalesReport $report)
    {
        try {
            $report->delete();

            return response()->json([
                'success' => true,
                'message' => 'Sales report deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete sales report: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Generate reports for multiple completed rentals
     * 
     * POST /admin/sales-reports/bulk-generate
     * 
     * Request Body:
     * - status: Filter rentals by status
     * - date_from: Only rentals after this date
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkGenerate(Request $request)
    {
        $request->validate([
            'status' => 'nullable|in:pending,ongoing,completed,cancelled',
            'date_from' => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();

            $query = Rental::whereDoesntHave('salesReports');

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('date_from')) {
                $query->where('created_at', '>=', Carbon::parse($request->date_from));
            }

            $rentals = $query->get();
            $createdCount = 0;

            foreach ($rentals as $rental) {
                // Find completed payment if exists
                $payment = $rental->payments()->where('status', 'completed')->first();
                SalesReport::generateFromRental($rental, $payment, Auth::user());
                $createdCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$createdCount} sales reports generated successfully",
                'count' => $createdCount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Bulk generation failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Export sales reports to CSV
     * 
     * GET /admin/sales-reports/export/csv
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportCsv(Request $request)
    {
        $status = $request->query('status');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $query = SalesReport::with(['rental.client', 'rental.car', 'admin']);

        if ($status) {
            $query->where('status', $status);
        }
        if ($dateFrom) {
            $query->where('report_date', '>=', Carbon::parse($dateFrom));
        }
        if ($dateTo) {
            $query->where('report_date', '<=', Carbon::parse($dateTo));
        }

        $reports = $query->get();

        // Generate CSV
        $filename = 'sales_reports_' . now()->format('Y-m-d_His') . '.csv';
        $handle = fopen('php://temp', 'r+');

        // Header
        fputcsv($handle, [
            'Report ID',
            'Rental ID',
            'Customer Name',
            'Car',
            'Gross Amount',
            'Commission (20%)',
            'Net Amount',
            'Status',
            'Report Date',
            'Admin',
        ]);

        // Data
        foreach ($reports as $report) {
            fputcsv($handle, [
                $report->id,
                $report->rental_id,
                $report->rental?->client?->name ?? '—',
                ($report->rental?->car?->brand ?? '—') . ' ' . ($report->rental?->car?->model ?? '—'),
                number_format($report->gross_amount, 2),
                number_format($report->admin_commission, 2),
                number_format($report->net_amount, 2),
                ucfirst($report->status),
                $report->report_date?->format('Y-m-d H:i') ?? '—',
                $report->admin?->name ?? '—',
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Calculate overall statistics
     * 
     * @return array
     */
    private function calculateStatistics()
    {
        $totalReports = SalesReport::count();
        $completedReports = SalesReport::where('status', 'completed')->count();
        $pendingReports = SalesReport::where('status', 'pending')->count();
        $cancelledReports = SalesReport::where('status', 'cancelled')->count();

        $totalGross = SalesReport::sum('gross_amount') ?? 0;
        $totalCommission = SalesReport::sum('admin_commission') ?? 0;
        $completedCommission = SalesReport::where('status', 'completed')->sum('admin_commission') ?? 0;

        return [
            'total_reports' => $totalReports,
            'completed_reports' => $completedReports,
            'pending_reports' => $pendingReports,
            'cancelled_reports' => $cancelledReports,
            'total_gross_amount' => (float) $totalGross,
            'total_commission' => (float) $totalCommission,
            'completed_commission' => (float) $completedCommission,
            'average_commission' => $totalReports > 0 ? round((float) $totalCommission / $totalReports, 2) : 0,
        ];
    }
}
