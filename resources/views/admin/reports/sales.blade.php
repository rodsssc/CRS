<x-app-layout>
    <div class="p-4 p-md-5">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h1 class="h4 mb-1 text-foreground fw-bold">Sales & Commission Report</h1>
                <p class="text-muted small mb-0">
                    Completed payments from {{ $from->format('M d, Y') }} to {{ $to->format('M d, Y') }}.
                </p>
            </div>
        </div>

        <!-- Summary cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="card bg-card border-border shadow-sm">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small fw-medium">Total Revenue</span>
                            <span class="badge bg-primary-subtle text-primary small">₱</span>
                        </div>
                        <h2 class="h4 mb-0 fw-bold">₱{{ number_format($totalRevenue, 2) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card bg-card border-border shadow-sm">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small fw-medium">Admin Commission (20%)</span>
                            <span class="badge bg-success-subtle text-success small">20%</span>
                        </div>
                        <h2 class="h4 mb-0 fw-bold">₱{{ number_format($totalCommission, 2) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card bg-card border-border shadow-sm">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small fw-medium">Completed Payments</span>
                        </div>
                        <h2 class="h4 mb-0 fw-bold">{{ number_format($payments->total()) }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Date filter + chart -->
        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-7">
                <div class="card bg-card border-border shadow-sm h-100">
                    <div class="card-header bg-transparent border-border p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h2 class="h6 mb-0 text-foreground fw-bold">Revenue vs Commission</h2>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div style="position: relative; height: 260px;">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-5">
                <div class="card bg-card border-border shadow-sm h-100">
                    <div class="card-header bg-transparent border-border p-3">
                        <h2 class="h6 mb-0 text-foreground fw-bold">Filter</h2>
                    </div>
                    <div class="card-body p-3">
                        <form method="GET" class="row g-2">
                            <div class="col-12">
                                <label class="form-label small text-muted mb-1">From</label>
                                <input type="date" name="from" class="form-control form-control-sm"
                                       value="{{ request('from', $from->toDateString()) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted mb-1">To</label>
                                <input type="date" name="to" class="form-control form-control-sm"
                                       value="{{ request('to', $to->toDateString()) }}">
                            </div>
                            <div class="col-12 d-flex gap-2 mt-2">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    Apply
                                </button>
                                <a href="{{ route('admin.reports.sales') }}" class="btn btn-sm btn-outline-secondary">
                                    Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payments table -->
        <div class="card bg-card border-border shadow-sm">
            <div class="card-header bg-transparent border-border p-3 d-flex justify-content-between align-items-center">
                <h2 class="h6 mb-0 text-foreground fw-bold">Completed Payments</h2>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-3 py-2 small text-muted text-uppercase">Date</th>
                                <th class="px-3 py-2 small text-muted text-uppercase">Booking</th>
                                <th class="px-3 py-2 small text-muted text-uppercase">Client</th>
                                <th class="px-3 py-2 small text-muted text-uppercase">Car</th>
                                <th class="px-3 py-2 small text-muted text-uppercase text-end">Amount</th>
                                <th class="px-3 py-2 small text-muted text-uppercase text-end">Commission (20%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                                <tr>
                                    <td class="px-3 py-2 small text-muted">
                                        {{ optional($payment->payment_date)->format('M d, Y H:i') }}
                                    </td>
                                    <td class="px-3 py-2 small">
                                        @if($payment->rental)
                                            #BK-{{ $payment->rental->id }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 small">
                                        {{ $payment->rental?->client?->name ?? '—' }}
                                    </td>
                                    <td class="px-3 py-2 small text-muted">
                                        @if($payment->rental && $payment->rental->car)
                                            {{ $payment->rental->car->brand }} {{ $payment->rental->car->model }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 small text-end fw-semibold">
                                        ₱{{ number_format($payment->amount, 2) }}
                                    </td>
                                    <td class="px-3 py-2 small text-end text-success fw-semibold">
                                        ₱{{ number_format($payment->commission, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-3 text-center text-muted small">
                                        No completed payments in this range.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 py-2">
                <div class="px-3">
                    {{ $payments->links() }}
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            (function () {
                const ctx = document.getElementById('salesChart');
                if (!ctx) return;

                const labels = @json($chartLabels);
                const revenue = @json($chartRevenue);
                const commission = @json($chartCommission);

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [
                            {
                                label: 'Revenue (₱)',
                                data: revenue,
                                borderColor: 'rgb(59,130,246)',
                                backgroundColor: 'rgba(59,130,246,0.12)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.35,
                            },
                            {
                                label: 'Commission (₱)',
                                data: commission,
                                borderColor: 'rgb(34,197,94)',
                                backgroundColor: 'rgba(34,197,94,0.08)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.35,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top' },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: (v) => '₱' + v
                                }
                            }
                        }
                    }
                });
            })();
        </script>
    @endpush
</x-app-layout>

