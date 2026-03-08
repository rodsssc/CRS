{{-- dashboard.blade.php --}}
<x-app-layout>
    <div class="p-6">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-foreground font-weight-bold">Dashboard</h1>
                <p class="text-muted-foreground small">Welcome back to your car rental management overview.</p>
            </div>
           
        </div>

        <!-- Metric Cards -->
        <div class="row g-4 mb-4">
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card bg-card border-border shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted-foreground small font-weight-medium">Total Users</span>
                            <div class="bg-primary/10 p-2 rounded-circle">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="var(--color-primary)" viewBox="0 0 16 16">
                                    <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                                    <path fill-rule="evenodd" d="M5.216 14A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216z"/>
                                    <path d="M4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z"/>
                                </svg>
                            </div>
                        </div>
                        <h2 class="h4 mb-1 font-weight-bold">{{ number_format($totalUsers ?? 0) }}</h2>
                        <span class="text-muted small">All registered users</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card bg-card border-border shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted-foreground small font-weight-medium">Available Cars</span>
                            <div class="bg-primary/10 p-2 rounded-circle">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="var(--color-primary)" viewBox="0 0 16 16">
                                    <path d="M0 2.5A.5.5 0 0 1 .5 2H2a.5.5 0 0 1 .485.379L2.89 4H14.5a.5.5 0 0 1 .485.621l-1.5 6A.5.5 0 0 1 13 11H4a.5.5 0 0 1-.485-.379L1.61 3H.5a.5.5 0 0 1-.5-.5z"/>
                                </svg>
                            </div>
                        </div>
                        <h2 class="h4 mb-1 font-weight-bold">{{ number_format($availableCars ?? 0) }}</h2>
                        <span class="text-muted small">Cars ready for booking</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card bg-card border-border shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted-foreground small font-weight-medium">Active Bookings</span>
                            <div class="bg-primary/10 p-2 rounded-circle">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="var(--color-primary)" viewBox="0 0 16 16">
                                    <path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1z"/>
                                    <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
                                </svg>
                            </div>
                        </div>
                        <h2 class="h4 mb-1 font-weight-bold">{{ number_format($activeBookings ?? 0) }}</h2>
                        <span class="text-muted small">Pending + ongoing bookings</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card bg-card border-border shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted-foreground small font-weight-medium">Total Revenue</span>
                            <div class="bg-primary/10 p-2 rounded-circle">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="var(--color-primary)" viewBox="0 0 16 16">
                                    <path d="M12.136.326A1.5 1.5 0 0 1 14 1.78V3h.5a.5.5 0 0 1 0 1H14v2h.5a.5.5 0 0 1 0 1H14v2h.5a.5.5 0 0 1 0 1H14v2h.5a.5.5 0 0 1 0 1H14v1.22a1.5 1.5 0 0 1-1.864 1.454L3.5 14.5a.5.5 0 0 1 0-1L12.136 12.326z"/>
                                </svg>
                            </div>
                        </div>
                        <h2 class="h4 mb-1 font-weight-bold">₱{{ number_format((float)($totalRevenue ?? 0), 2) }}</h2>
                        <span class="text-muted small">Completed payments</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Revenue / Commission / Bookings Chart --}}
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card bg-card border-border shadow-sm">
                    <div class="card-header bg-transparent border-border p-4 d-flex justify-content-between align-items-center">
                        <h3 class="h5 mb-0 text-foreground font-weight-bold">
                            Revenue, Commission &amp; Bookings (Last 7 Days)
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        <div style="position: relative; height: 260px;">
                            <canvas id="dashboardRevenueChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Recent Bookings Table -->
            <div class="col-12 col-xl-8">
                <div class="card bg-card border-border shadow-sm h-100">
                    <div class="card-header bg-transparent border-border p-4 d-flex justify-content-between align-items-center">
                        <h3 class="h5 mb-0 text-foreground font-weight-bold">Recent Bookings</h3>
                        <a href="{{ route('admin.bookings.index') }}" class="text-primary small font-weight-medium">View all</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-muted/50">
                                    <tr>
                                        <th class="border-0 px-4 py-3 text-muted-foreground small text-uppercase">Booking ID</th>
                                        <th class="border-0 px-4 py-3 text-muted-foreground small text-uppercase">Customer</th>
                                        <th class="border-0 px-4 py-3 text-muted-foreground small text-uppercase">Car Model</th>
                                        <th class="border-0 px-4 py-3 text-muted-foreground small text-uppercase">Date</th>
                                        <th class="border-0 px-4 py-3 text-muted-foreground small text-uppercase">Status</th>
                                        <th class="border-0 px-4 py-3 text-muted-foreground small text-uppercase text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(($recentBookings ?? collect()) as $booking)
                                        @php
                                            $amount = (float) $booking->final_amount > 0 ? (float) $booking->final_amount : (float) $booking->car_amount;
                                        @endphp
                                        <tr>
                                            <td class="px-4 py-3 align-middle font-weight-medium">#BK-{{ $booking->id }}</td>
                                            <td class="px-4 py-3 align-middle">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="bg-secondary p-1 rounded-circle" style="width: 24px; height: 24px;"></div>
                                                    <span>{{ $booking->client?->name ?? '—' }}</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 align-middle text-muted-foreground">
                                                {{ $booking->car?->brand }} {{ $booking->car?->model }}
                                            </td>
                                            <td class="px-4 py-3 align-middle text-muted-foreground">
                                                {{ optional($booking->created_at)->format('M d, Y') }}
                                            </td>
                                            <td class="px-4 py-3 align-middle">
                                                @if($booking->status === 'pending')
                                                    <span class="badge rounded-pill bg-primary-subtle text-primary px-3 py-1">Pending</span>
                                                @elseif($booking->status === 'ongoing')
                                                    <span class="badge rounded-pill bg-success-subtle text-success px-3 py-1">Ongoing</span>
                                                @elseif($booking->status === 'completed')
                                                    <span class="badge rounded-pill bg-secondary-subtle text-secondary px-3 py-1">Completed</span>
                                                @else
                                                    <span class="badge rounded-pill bg-danger-subtle text-danger px-3 py-1">Cancelled</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 align-middle text-right font-weight-bold">₱{{ number_format($amount, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="px-4 py-4 text-center text-muted" colspan="6">No bookings found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fleet Status List -->
            <div class="col-12 col-xl-4">
                <div class="card bg-card border-border shadow-sm h-100">
                    <div class="card-header bg-transparent border-border p-4">
                        <h3 class="h5 mb-0 text-foreground font-weight-bold">Fleet Overview</h3>
                    </div>
                    <div class="card-body p-4">
                        @php
                            $available = (int) (($carStatusCounts['available'] ?? 0));
                            $rented = (int) (($carStatusCounts['rented'] ?? 0));
                            $maintenance = (int) (($carStatusCounts['maintenance'] ?? 0));
                            $totalCarsSafe = max(1, (int) ($totalCars ?? 0));
                            $pAvail = round(($available / $totalCarsSafe) * 100);
                            $pRented = round(($rented / $totalCarsSafe) * 100);
                            $pMaint = round(($maintenance / $totalCarsSafe) * 100);
                        @endphp

                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted-foreground small">Available</span>
                                <span class="text-foreground small font-weight-bold">{{ $available }} / {{ $totalCars ?? 0 }}</span>
                            </div>
                            <div class="progress bg-muted" style="height: 6px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $pAvail }}%" aria-valuenow="{{ $pAvail }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted-foreground small">Rented</span>
                                <span class="text-foreground small font-weight-bold">{{ $rented }} / {{ $totalCars ?? 0 }}</span>
                            </div>
                            <div class="progress bg-muted" style="height: 6px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $pRented }}%" aria-valuenow="{{ $pRented }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted-foreground small">Maintenance</span>
                                <span class="text-foreground small font-weight-bold">{{ $maintenance }} / {{ $totalCars ?? 0 }}</span>
                            </div>
                            <div class="progress bg-muted" style="height: 6px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $pMaint }}%" aria-valuenow="{{ $pMaint }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-muted small">
                            Total cars: <strong>{{ $totalCars ?? 0 }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            (function () {
                const canvas = document.getElementById('dashboardRevenueChart');
                if (!canvas || !window.CRS_ADMIN) return;

                const ctx = canvas.getContext('2d');
                const apiUrl = "{{ route('admin.dashboard.revenue-data') }}";

                CRS_ADMIN.requestJson(apiUrl)
                    .then(({ res, data }) => {
                        if (!res.ok || !data || !data.success) {
                            throw data;
                        }

                        const labels = data.labels || [];
                        const revenue = data.revenue || [];
                        const commission = data.commission || [];
                        const bookings = data.bookings || [];

                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels,
                                datasets: [
                                    {
                                        type: 'bar',
                                        label: 'Bookings',
                                        data: bookings,
                                        backgroundColor: 'rgba(99, 102, 241, 0.15)',
                                        borderColor: 'rgb(79, 70, 229)',
                                        borderWidth: 1,
                                        yAxisID: 'y1',
                                    },
                                    {
                                        type: 'line',
                                        label: 'Revenue (₱)',
                                        data: revenue,
                                        borderColor: 'rgb(59, 130, 246)',
                                        backgroundColor: 'rgba(59, 130, 246, 0.12)',
                                        borderWidth: 2,
                                        tension: 0.35,
                                        fill: true,
                                        yAxisID: 'y',
                                    },
                                    {
                                        type: 'line',
                                        label: 'Commission (₱)',
                                        data: commission,
                                        borderColor: 'rgb(34, 197, 94)',
                                        backgroundColor: 'rgba(34, 197, 94, 0.08)',
                                        borderWidth: 2,
                                        tension: 0.35,
                                        fill: true,
                                        yAxisID: 'y',
                                    },
                                ],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: {
                                    mode: 'index',
                                    intersect: false,
                                },
                                scales: {
                                    y: {
                                        type: 'linear',
                                        position: 'left',
                                        beginAtZero: true,
                                        ticks: {
                                            callback: (v) => '₱' + v,
                                        },
                                    },
                                    y1: {
                                        type: 'linear',
                                        position: 'right',
                                        beginAtZero: true,
                                        grid: {
                                            drawOnChartArea: false,
                                        },
                                        ticks: {
                                            precision: 0,
                                        },
                                    },
                                },
                                plugins: {
                                    legend: {
                                        position: 'top',
                                    },
                                },
                            },
                        });
                    })
                    .catch((err) => {
                        console.error('[Dashboard] Failed to load chart data', err);
                    });
            })();
        </script>
    @endpush
</x-app-layout>
