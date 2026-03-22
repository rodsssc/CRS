<x-app-layout>

<div class="dash-wrap">

    {{-- ── HEADER ──────────────────────────────────────────────────────────── --}}
    <div class="dash-header">
        <div>
            <h1 class="dash-title">Sales & Commission Report</h1>
            <p class="dash-subtitle">
                Completed payments from {{ $from->format('M d, Y') }} to {{ $to->format('M d, Y') }}
            </p>
        </div>
        <div class="dash-date">
            <i class="fas fa-calendar-alt me-1"></i>{{ now()->format('F d, Y') }}
        </div>
    </div>

    {{-- ── SUMMARY CARDS ───────────────────────────────────────────────────── --}}
    <div class="metrics-grid">

        <div class="metric-card metric-card--dark">
            <div class="metric-icon" style="background:rgba(255,255,255,.1);">
                <i class="fas fa-peso-sign" style="color:#fff;"></i>
            </div>
            <div class="metric-body">
                <div class="metric-label" style="color:rgba(255,255,255,.55);">Total Revenue</div>
                <div class="metric-value" style="color:#fff;">₱{{ number_format($totalRevenue, 2) }}</div>
                <div class="metric-sub" style="color:rgba(255,255,255,.4);">Completed payments</div>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon" style="background:rgba(34,197,94,.1);">
                <i class="fas fa-percent" style="color:#22c55e;"></i>
            </div>
            <div class="metric-body">
                <div class="metric-label">Admin Commission (20%)</div>
                <div class="metric-value">₱{{ number_format($totalCommission, 2) }}</div>
                <div class="metric-sub">20% of total revenue</div>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon" style="background:rgba(59,130,246,.1);">
                <i class="fas fa-receipt" style="color:#3b82f6;"></i>
            </div>
            <div class="metric-body">
                <div class="metric-label">Completed Payments</div>
                <div class="metric-value">{{ number_format($payments->total()) }}</div>
                <div class="metric-sub">In selected period</div>
            </div>
        </div>

    </div>

    {{-- ── CHART + FILTER ───────────────────────────────────────────────────── --}}
    <div class="mid-grid">

        {{-- Revenue vs Commission Chart --}}
        <div class="dash-card chart-card">
            <div class="dash-card__head">
                <div>
                    <div class="dash-card__title">Revenue vs Commission</div>
                    <div class="dash-card__sub">Daily breakdown for selected period</div>
                </div>
                <span class="live-badge">
                    <span class="live-dot"></span> Live
                </span>
            </div>
            <div class="dash-card__body">
                <div class="chart-wrap">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Filter --}}
        <div class="dash-card fleet-card">
            <div class="dash-card__head">
                <div>
                    <div class="dash-card__title">Filter Period</div>
                    <div class="dash-card__sub">Narrow down the date range</div>
                </div>
            </div>
            <div class="dash-card__body">
                <form method="GET" class="d-flex flex-column gap-2">
                    <div>
                        <label class="metric-label d-block mb-1">From</label>
                        <input type="date" name="from"
                               class="form-control form-control-sm"
                               value="{{ request('from', $from->toDateString()) }}">
                    </div>
                    <div>
                        <label class="metric-label d-block mb-1">To</label>
                        <input type="date" name="to"
                               class="form-control form-control-sm"
                               value="{{ request('to', $to->toDateString()) }}">
                    </div>
                    <div class="d-flex gap-2 mt-1">
                        <button type="submit" class="view-all-btn"
                                style="background:#0f172a;color:#fff;border-color:#0f172a;">
                            <i class="fas fa-sliders me-1"></i> Apply
                        </button>
                        <a href="{{ route('admin.reports.sales') }}" class="view-all-btn">
                            <i class="fas fa-rotate-left me-1"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

    </div>

    {{-- ── PAYMENTS TABLE ───────────────────────────────────────────────────── --}}
    <div class="dash-card bookings-card">
        <div class="dash-card__head">
            <div>
                <div class="dash-card__title">Completed Payments</div>
                <div class="dash-card__sub">{{ number_format($payments->total()) }} records in selected period</div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="bookings-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Booking</th>
                        <th>Client</th>
                        <th>Car</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Commission (20%)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td class="text-muted">
                                {{ optional($payment->payment_date)->format('M d, Y H:i') }}
                            </td>
                            <td>
                                @if($payment->rental)
                                    <span class="booking-id">#BK{{ $payment->rental->id }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="customer-cell">
                                    <div class="customer-avatar">
                                        {{ strtoupper(substr($payment->rental?->client?->name ?? '?', 0, 1)) }}
                                    </div>
                                    <span>{{ $payment->rental?->client?->name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="text-muted">
                                @if($payment->rental && $payment->rental->car)
                                    {{ $payment->rental->car->brand }} {{ $payment->rental->car->model }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-end amount-cell">
                                ₱{{ number_format($payment->amount, 2) }}
                            </td>
                            <td class="text-end">
                                <span class="status-pill status-completed">
                                    ₱{{ number_format($payment->commission, 2) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-row">
                                <i class="fas fa-inbox"></i>
                                <span>No completed payments in this range.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: .5rem 1rem .75rem;">
            {{ $payments->links() }}
        </div>
    </div>

</div>{{-- /dash-wrap --}}

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    (function () {
        'use strict';

        const canvas = document.getElementById('salesChart');
        if (!canvas) return;

        const ctx      = canvas.getContext('2d');
        const labels   = @json($chartLabels);
        const revenue  = @json($chartRevenue);
        const commission = @json($chartCommission);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Revenue (₱)',
                        data: revenue,
                        backgroundColor: 'rgba(59,130,246,0.15)',
                        borderColor: 'rgb(59,130,246)',
                        borderWidth: 1,
                        borderRadius: 4,
                        yAxisID: 'y',
                    },
                    {
                        type: 'line',
                        label: 'Commission (₱)',
                        data: commission,
                        borderColor: 'rgb(34,197,94)',
                        backgroundColor: 'rgba(34,197,94,0.08)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: 'rgb(34,197,94)',
                        pointBorderWidth: 2,
                        yAxisID: 'y',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { size: 11 }, boxWidth: 12, padding: 16 },
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15,23,42,.9)',
                        padding: 12,
                        titleColor: '#fff',
                        bodyColor: 'rgba(255,255,255,.8)',
                        borderColor: 'rgba(255,255,255,.1)',
                        borderWidth: 1,
                        callbacks: {
                            label: function (item) {
                                return item.dataset.label + ': ₱' +
                                    parseFloat(item.parsed.y).toLocaleString('en-PH', {
                                        minimumFractionDigits: 2,
                                    });
                            },
                        },
                    },
                },
                scales: {
                    y: {
                        type: 'linear',
                        position: 'left',
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: {
                            color: '#64748b',
                            font: { size: 11 },
                            callback: v => '₱' + v.toLocaleString('en-PH'),
                        },
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b', font: { size: 11 } },
                    },
                },
            },
        });
    })();
    </script>
@endpush

</x-app-layout>