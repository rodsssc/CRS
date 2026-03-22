{{-- dashboard.blade.php --}}
<x-app-layout>

<div class="dash-wrap">

    {{-- ── HEADER ──────────────────────────────────────────────────────────── --}}
    <div class="dash-header">
        <div>
            <h1 class="dash-title">Dashboard</h1>
            <p class="dash-subtitle">Welcome back — here's your car rental overview.</p>
        </div>
        <div class="dash-date">
            <i class="fas fa-calendar-alt me-1"></i>{{ now()->format('F d, Y') }}
        </div>
    </div>

    {{-- ── METRIC CARDS ────────────────────────────────────────────────────── --}}
    <div class="metrics-grid">

        <div class="metric-card">
            <div class="metric-icon" style="background:rgba(59,130,246,.1);">
                <i class="fas fa-users" style="color:#3b82f6;"></i>
            </div>
            <div class="metric-body">
                <div class="metric-label">Total Users</div>
                <div class="metric-value">{{ number_format($totalUsers ?? 0) }}</div>
                <div class="metric-sub">All registered users</div>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon" style="background:rgba(34,197,94,.1);">
                <i class="fas fa-car" style="color:#22c55e;"></i>
            </div>
            <div class="metric-body">
                <div class="metric-label">Available Cars</div>
                <div class="metric-value">{{ number_format($availableCars ?? 0) }}</div>
                <div class="metric-sub">Ready for booking</div>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon" style="background:rgba(245,158,11,.1);">
                <i class="fas fa-calendar-check" style="color:#f59e0b;"></i>
            </div>
            <div class="metric-body">
                <div class="metric-label">Active Bookings</div>
                <div class="metric-value">{{ number_format($activeBookings ?? 0) }}</div>
                <div class="metric-sub">Pending + ongoing</div>
            </div>
        </div>

        <div class="metric-card metric-card--dark">
            <div class="metric-icon" style="background:rgba(255,255,255,.1);">
                <i class="fas fa-peso-sign" style="color:#fff;"></i>
            </div>
            <div class="metric-body">
                <div class="metric-label" style="color:rgba(255,255,255,.55);">Total Revenue</div>
                <div class="metric-value" style="color:#fff;">₱{{ number_format((float)($totalRevenue ?? 0), 2) }}</div>
                <div class="metric-sub" style="color:rgba(255,255,255,.4);">Completed payments</div>
            </div>
        </div>

    </div>

    {{-- ── CHART + FLEET ────────────────────────────────────────────────────── --}}
    <div class="mid-grid">

        {{-- Revenue Chart --}}
        <div class="dash-card chart-card">
            <div class="dash-card__head">
                <div>
                    <div class="dash-card__title">Revenue Overview</div>
                    <div class="dash-card__sub">Last 7 days</div>
                </div>
                <span class="live-badge">
                    <span class="live-dot"></span> Live
                </span>
            </div>
            <div class="dash-card__body">
                <div class="chart-wrap">
                    <canvas id="dashboardRevenueChart"
                            data-url="{{ route('admin.dashboard.revenue-data') }}">
                    </canvas>
                </div>
            </div>
        </div>

        {{-- Fleet Overview --}}
        <div class="dash-card fleet-card">
            <div class="dash-card__head">
                <div>
                    <div class="dash-card__title">Fleet Overview</div>
                    <div class="dash-card__sub">{{ $totalCars ?? 0 }} total vehicles</div>
                </div>
            </div>
            <div class="dash-card__body">
                @php
                    $available   = (int)($carStatusCounts['available']   ?? 0);
                    $rented      = (int)($carStatusCounts['rented']      ?? 0);
                    $maintenance = (int)($carStatusCounts['maintenance'] ?? 0);
                    $safe        = max(1, (int)($totalCars ?? 0));
                @endphp

                <div class="fleet-row">
                    <div class="fleet-row__meta">
                        <span class="fleet-dot" style="background:#22c55e;"></span>
                        <span class="fleet-label">Available</span>
                        <span class="fleet-count">{{ $available }}<span class="fleet-total">/{{ $totalCars ?? 0 }}</span></span>
                    </div>
                    <div class="fleet-track">
                        <div class="fleet-fill" style="width:{{ round(($available/$safe)*100) }}%;background:#22c55e;"></div>
                    </div>
                </div>

                <div class="fleet-row">
                    <div class="fleet-row__meta">
                        <span class="fleet-dot" style="background:#3b82f6;"></span>
                        <span class="fleet-label">Rented</span>
                        <span class="fleet-count">{{ $rented }}<span class="fleet-total">/{{ $totalCars ?? 0 }}</span></span>
                    </div>
                    <div class="fleet-track">
                        <div class="fleet-fill" style="width:{{ round(($rented/$safe)*100) }}%;background:#3b82f6;"></div>
                    </div>
                </div>

                <div class="fleet-row">
                    <div class="fleet-row__meta">
                        <span class="fleet-dot" style="background:#f59e0b;"></span>
                        <span class="fleet-label">Maintenance</span>
                        <span class="fleet-count">{{ $maintenance }}<span class="fleet-total">/{{ $totalCars ?? 0 }}</span></span>
                    </div>
                    <div class="fleet-track">
                        <div class="fleet-fill" style="width:{{ round(($maintenance/$safe)*100) }}%;background:#f59e0b;"></div>
                    </div>
                </div>

                {{-- Booking breakdown --}}
                <div class="booking-grid">
                    <div class="booking-chip booking-chip--warning">
                        <div class="booking-chip__val">{{ $pendingBookings ?? 0 }}</div>
                        <div class="booking-chip__lbl">Pending</div>
                    </div>
                    <div class="booking-chip booking-chip--info">
                        <div class="booking-chip__val">{{ $ongoingBookings ?? 0 }}</div>
                        <div class="booking-chip__lbl">Ongoing</div>
                    </div>
                    <div class="booking-chip booking-chip--success">
                        <div class="booking-chip__val">{{ $completedBookings ?? 0 }}</div>
                        <div class="booking-chip__lbl">Completed</div>
                    </div>
                    <div class="booking-chip booking-chip--danger">
                        <div class="booking-chip__val">{{ $cancelledBookings ?? 0 }}</div>
                        <div class="booking-chip__lbl">Cancelled</div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    {{-- ── RECENT BOOKINGS ──────────────────────────────────────────────────── --}}
    <div class="dash-card bookings-card">
        <div class="dash-card__head">
            <div>
                <div class="dash-card__title">Recent Bookings</div>
                <div class="dash-card__sub">Latest rental activity</div>
            </div>
            <a href="{{ route('admin.bookings.index') }}" class="view-all-btn">
                View all <i class="fas fa-arrow-right ms-1" style="font-size:10px;"></i>
            </a>
        </div>
        <div class="table-responsive">
            <table class="bookings-table">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer</th>
                        <th>Car</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($recentBookings ?? collect()) as $booking)
                        @php
                            $amount = (float)$booking->final_amount > 0
                                ? (float)$booking->final_amount
                                : (float)$booking->car_amount;
                        @endphp
                        <tr>
                            <td><span class="booking-id">#BK-{{ $booking->id }}</span></td>
                            <td>
                                <div class="customer-cell">
                                    <div class="customer-avatar">
                                        {{ strtoupper(substr($booking->client?->name ?? '?', 0, 1)) }}
                                    </div>
                                    <span>{{ $booking->client?->name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="text-muted">{{ $booking->car?->brand }} {{ $booking->car?->model }}</td>
                            <td class="text-muted">{{ optional($booking->created_at)->format('M d, Y') }}</td>
                            <td>
                                @php
                                    $sc = ['pending'=>'status-pending','ongoing'=>'status-ongoing','completed'=>'status-completed','cancelled'=>'status-cancelled'];
                                @endphp
                                <span class="status-pill {{ $sc[$booking->status] ?? 'status-cancelled' }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                            <td class="text-end amount-cell">₱{{ number_format($amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-row">
                                <i class="fas fa-inbox"></i>
                                <span>No bookings found</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>{{-- /dash-wrap --}}

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('assets/js/admin/dashboard.js') }}"></script>
@endpush

</x-app-layout>