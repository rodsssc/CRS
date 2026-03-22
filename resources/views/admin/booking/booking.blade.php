<x-app-layout>
    <div class="container-fluid px-4 py-3">

        {{-- ── HEADER ──────────────────────────────────────────────────────────── --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="page-title mb-0">Booking Management</h2>
        </div>

        {{-- ── STATS CARDS ─────────────────────────────────────────────────────── --}}
        <div class="row g-2 mb-3">
            <div class="col-6 col-lg">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-soft">
                        <i class="fas fa-users text-primary"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Rental</div>
                        <div class="stat-value">{{ $totalBookings }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg">
                <div class="stat-card">
                    <div class="stat-icon bg-info-soft">
                        <i class="fas fa-user-check text-info"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Ongoing</div>
                        <div class="stat-value">{{ $ongoingCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg">
                <div class="stat-card">
                    <div class="stat-icon bg-warning-soft">
                        <i class="fas fa-clock text-warning"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Pending</div>
                        <div class="stat-value">{{ $pendingCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg">
                <div class="stat-card">
                    <div class="stat-icon bg-success-soft">
                        <i class="fas fa-check-circle text-success"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Completed</div>
                        <div class="stat-value">{{ $completedCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg">
                <div class="stat-card">
                    <div class="stat-icon bg-danger-soft">
                        <i class="fas fa-ban text-danger"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Cancelled</div>
                        <div class="stat-value">{{ $cancelledCount }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── TABLE CARD ──────────────────────────────────────────────────────── --}}
        <div class="table-card">

            {{-- Search & Filters --}}
            <div class="table-controls">
                <form method="GET" action="{{ route('admin.bookings.index') }}"
                      class="row g-2 align-items-center">

                    <div class="col-md-6 col-lg-5">
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text"
                                   class="form-control form-control-sm"
                                   name="q"
                                   value="{{ $q ?? '' }}"
                                   placeholder="Search name, plate, destination..."
                                   autocomplete="off">
                        </div>
                    </div>

                    <div class="col-md-3 col-lg-2 d-flex gap-2">
                        <button class="btn btn-primary btn-sm" type="submit">Search</button>
                        <a class="btn btn-outline-secondary btn-sm"
                           href="{{ route('admin.bookings.index') }}">Clear</a>
                    </div>

                    <div class="col-md-3 col-lg-2">
                        <select class="form-select form-select-sm" name="status"
                                onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="pending"   @selected(($status ?? '') === 'pending')>Pending</option>
                            <option value="ongoing"   @selected(($status ?? '') === 'ongoing')>Ongoing</option>
                            <option value="completed" @selected(($status ?? '') === 'completed')>Completed</option>
                            <option value="cancelled" @selected(($status ?? '') === 'cancelled')>Cancelled</option>
                        </select>
                    </div>

                    <div class="col-md-3 col-lg-1">
                        <select class="form-select form-select-sm" name="per_page"
                                onchange="this.form.submit()">
                            @foreach([10, 25, 50, 100] as $pp)
                                <option value="{{ $pp }}" @selected(($perPage ?? 10) == $pp)>{{ $pp }}</option>
                            @endforeach
                        </select>
                    </div>

                </form>
            </div>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table custom-table mb-0">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Name</th>
                            <th>Phone No.</th>
                            <th>Plate No.</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Rental Start</th>
                            <th>Rental End</th>
                            <th>Hours</th>
                            <th>Days</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bookings as $booking)
                            <tr>
                                <td class="fw-semibold text-muted">#BK{{ $booking->id }}</td>

                                <td>
                                    <div class="fw-semibold">{{ $booking->client->name ?? 'N/A' }}</div>
                                </td>

                                <td class="text-muted">{{ $booking->client->phone ?? 'N/A' }}</td>

                                <td class="text-muted">{{ $booking->car->plate_number ?? 'N/A' }}</td>

                                <td class="text-muted">{{ $booking->destinationFrom ?? '—' }}</td>
                                <td class="text-muted">{{ $booking->destinationTo ?? '—' }}</td>

                                <td class="text-muted small">
                                    {{ $booking->rental_start_date?->format('M d, Y h:i a') ?? '—' }}
                                </td>
                                <td class="text-muted small">
                                    {{ $booking->rental_end_date?->format('M d, Y h:i a') ?? '—' }}
                                </td>

                                <td class="text-muted">{{ $booking->total_hours ?? '—' }}</td>
                                <td class="text-muted">{{ $booking->total_days ?? '—' }}</td>

                                <td>
                                    @php $s = $booking->status; @endphp
                                    @if($s === 'pending')
                                        <span class="status-tag status-pending">Pending</span>
                                    @elseif($s === 'ongoing')
                                        <span class="status-tag status-ongoing">Ongoing</span>
                                    @elseif($s === 'completed')
                                        <span class="status-tag status-completed">Completed</span>
                                    @elseif($s === 'cancelled')
                                        <span class="status-tag status-cancelled">Cancelled</span>
                                    @else
                                        <span class="status-tag">{{ $s }}</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action btn-view-booking"
                                                title="View"
                                                data-booking-id="{{ $booking->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        @if(in_array($booking->status, ['pending', 'ongoing']))
                                            <button class="btn-action btn-quick-reject"
                                                    title="Reject / Cancel"
                                                    data-booking-id="{{ $booking->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fs-4 mb-2 d-block"></i>
                                    No bookings found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($bookings->hasPages())
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                    <div class="text-muted small">
                        Showing {{ $bookings->firstItem() }}–{{ $bookings->lastItem() }}
                        of {{ $bookings->total() }} bookings
                    </div>
                    <div>
                        {{ $bookings->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @else
                <div class="px-3 py-2 border-top text-muted small">
                    {{ $bookings->total() }} booking(s) found
                </div>
            @endif

        </div>
    </div>
</x-app-layout>

<script src="{{ asset('assets/js/admin/booking/booking.js') }}"></script>


{{-- ═══════════════════════════════════════════════════════════════════════════
     VIEW BOOKING MODAL
     ══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" tabindex="-1" id="viewBookingModal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">

            <div class="modal-header bg-header border-0 px-4 py-3">
                <div>
                    <h6 class="modal-title text-white fw-bold mb-0">
                        <i class="fas fa-file-contract me-2"></i>Booking Details
                    </h6>
                    <p class="text-white-50 mb-0" style="font-size:11px;">
                        View booking and rental information
                    </p>
                </div>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-3 bg-light">

                {{-- CLIENT + CAR QUICK INFO --}}
                <div class="bg-white border rounded-3 p-3 mb-3
                            d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-bold small" id="viewClientName">—</div>
                        <div class="text-muted" style="font-size:11px;" id="viewClientEmail">—</div>
                        <div class="text-muted" style="font-size:11px;">
                            <i class="fas fa-phone me-1"></i>
                            <span id="viewClientPhone">—</span>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold small" id="viewCarName">—</div>
                        <div class="text-muted" style="font-size:11px;">
                            <i class="fas fa-hashtag me-1"></i>
                            <span id="viewPlateNumber">—</span>
                        </div>
                        <span id="viewStatusBadge" class="badge rounded-pill mt-1"></span>
                    </div>
                </div>

                <div class="row g-3">

                    {{-- LEFT --}}
                    <div class="col-md-5 d-flex flex-column gap-4">
                        <img id="viewCarImage"
                             src="https://via.placeholder.com/400x160?text=No+Image"
                             alt="Car"
                             class="img-fluid rounded-3 border w-100"
                             style="height:140px; object-fit:cover;"
                             onerror="this.src='https://via.placeholder.com/400x160?text=No+Image'">

                        <div class="row g-2">
                            <div class="col-6">
                                <div class="bg-white border rounded-3 text-center py-2">
                                    <div class="text-muted" style="font-size:10px;">Transmission</div>
                                    <div class="fw-bold" style="font-size:11px;"
                                         id="viewCarTransmission">—</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-white border rounded-3 text-center py-2">
                                    <div class="text-muted" style="font-size:10px;">Fuel</div>
                                    <div class="fw-bold" style="font-size:11px;"
                                         id="viewCarFuel">—</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-white border rounded-3 text-center py-2">
                                    <div class="text-muted" style="font-size:10px;">Capacity</div>
                                    <div class="fw-bold" style="font-size:11px;">
                                        <span id="viewCarCapacity">—</span> Seats
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-white border rounded-3 text-center py-2">
                                    <div class="text-muted" style="font-size:10px;">Color</div>
                                    <div class="fw-bold" style="font-size:11px;"
                                         id="viewCarColor">—</div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-dark rounded-3 text-center py-2">
                            <div class="text-white-50" style="font-size:10px;">PRICE / DAY</div>
                            <div class="fw-bold text-info small">
                                ₱<span id="viewCarPrice">0.00</span>
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT --}}
                    <div class="col-md-7 d-flex flex-column gap-2">

                        {{-- Rental Period --}}
                        <div class="bg-white border rounded-3 p-3">
                            <div class="text-muted fw-semibold mb-2"
                                 style="font-size:10px; letter-spacing:.06em;">
                                <i class="fas fa-calendar me-1"></i>RENTAL PERIOD
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <div class="bg-light rounded-3 text-center py-2">
                                        <div class="text-muted" style="font-size:10px;">Start</div>
                                        <div id="viewStartDate" class="fw-bold"
                                             style="font-size:11px;">—</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-light rounded-3 text-center py-2">
                                        <div class="text-muted" style="font-size:10px;">Return</div>
                                        <div id="viewEndDate" class="fw-bold"
                                             style="font-size:11px;">—</div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-3">
                                <div style="font-size:11px;">
                                    <span class="text-muted">Days:</span>
                                    <span class="fw-bold" id="viewTotalDays">—</span>
                                </div>
                                <div style="font-size:11px;">
                                    <span class="text-muted">Hours:</span>
                                    <span class="fw-bold" id="viewTotalHours">—</span>
                                </div>
                            </div>
                        </div>

                        {{-- Destinations --}}
                        <div class="bg-white border rounded-3 p-3">
                            <div class="text-muted fw-semibold mb-2"
                                 style="font-size:10px; letter-spacing:.06em;">
                                <i class="fas fa-route me-1"></i>DESTINATIONS
                            </div>
                            <div class="d-flex flex-column gap-1">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-success" style="font-size:9px;">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </span>
                                    <div>
                                        <div class="text-muted" style="font-size:10px;">FROM</div>
                                        <div class="fw-semibold" style="font-size:11px;"
                                             id="viewDestinationFrom">—</div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-danger" style="font-size:9px;">
                                        <i class="fas fa-flag-checkered"></i>
                                    </span>
                                    <div>
                                        <div class="text-muted" style="font-size:10px;">TO</div>
                                        <div class="fw-semibold" style="font-size:11px;"
                                             id="viewDestinationTo">—</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Payment Summary --}}
                        <div class="bg-white border rounded-3 p-3">
                            <div class="text-muted fw-semibold mb-2"
                                 style="font-size:10px; letter-spacing:.06em;">
                                <i class="fas fa-receipt me-1"></i>PAYMENT SUMMARY
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted" style="font-size:11px;">Car Amount</span>
                                <span class="fw-semibold" style="font-size:11px;"
                                      id="viewCarAmount">—</span>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted" style="font-size:11px;">Destination Fee</span>
                                <span class="fw-semibold" style="font-size:11px;"
                                      id="viewDestinationAmount">—</span>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted" style="font-size:11px;">Discount</span>
                                <span class="fw-semibold text-success" style="font-size:11px;"
                                      id="viewDiscountAmount">—</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center
                                        rounded-3 px-3 py-2 bg-dark mt-2">
                                <span class="text-white-50 fw-semibold"
                                      style="font-size:11px;">TOTAL</span>
                                <span class="fw-bold text-info" id="viewFinalAmount">—</span>
                            </div>
                        </div>

                        {{-- Timestamps --}}
                        <div class="d-flex justify-content-between px-1">
                            <small class="text-muted" style="font-size:10px;">
                                <i class="fas fa-clock me-1"></i>Created:
                                <span id="viewCreatedAt">—</span>
                            </small>
                            <small class="text-muted" style="font-size:10px;">
                                <i class="fas fa-edit me-1"></i>Updated:
                                <span id="viewUpdatedAt">—</span>
                            </small>
                        </div>

                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 bg-white px-4 pb-4 pt-2 gap-2"
                 id="viewModalFooter">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>

                {{-- Pending only --}}
                <button type="button"
                        class="btn btn-success btn-approve-booking d-none"
                        id="approveBookingBtn" data-id="">
                    <i class="fas fa-check me-1"></i>Approve
                </button>

                {{-- Ongoing only --}}
                <button type="button"
                        class="btn btn-primary btn-complete-booking d-none"
                        id="completeBookingBtn" data-id="">
                    <i class="fas fa-car me-1"></i>Car Returned
                </button>

                {{-- Pending or Ongoing --}}
                <button type="button"
                        class="btn btn-danger btn-reject-booking d-none"
                        id="rejectBookingBtn" data-id="">
                    <i class="fas fa-times me-1"></i>Reject
                </button>
            </div>

        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════════════════════════════════
     APPROVE BOOKING MODAL
     ══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" tabindex="-1" id="approveModal"
     aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">

            <div class="modal-header bg-header border-0 px-4 pt-4 pb-2">
                <div>
                    <h5 class="modal-title text-white fw-bold mb-0" id="approveModalLabel">
                        <i class="fas fa-clipboard-check me-2"></i>Approve Booking
                    </h5>
                    <p class="text-white-50 small mb-0">Set fees before proceeding to payment</p>
                </div>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                <input type="hidden" id="approveBookingId" value="">

                <div class="d-flex align-items-center gap-3 p-3 rounded-3 mb-4 bg-white border">
                    <div class="flex-grow-1">
                        <div class="fw-bold small text-dark" id="approveClientName">—</div>
                        <div class="text-muted" style="font-size:11px;">
                            <span id="approveCarName">—</span>
                            &nbsp;·&nbsp;
                            <span id="approvePlate">—</span>
                        </div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <div class="text-muted" style="font-size:10px;">Base Rate / Day</div>
                        <div class="fw-bold text-success small">
                            ₱<span id="approveCarRate">0.00</span>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted mb-1">
                        Car Rental Amount
                    </label>
                    <input type="number" class="form-control form-control-sm bg-light"
                           id="approveCarAmount" value="0.00" readonly>
                    <div class="text-muted mt-1" style="font-size:10px;">
                        Auto-computed:
                        <span id="approveDays">0</span> day(s)
                        × ₱<span id="approveRateDisplay">0.00</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted mb-1">
                        Destination Fee
                    </label>
                    <input type="number" class="form-control form-control-sm"
                           id="approveDestinationAmount" min="0" step="0.01"
                           placeholder="0.00">
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold text-muted mb-1">Discount</label>
                    <input type="number" class="form-control form-control-sm"
                           id="approveDiscountAmount" min="0" step="0.01"
                           placeholder="0.00">
                </div>

                <div class="d-flex justify-content-between align-items-center
                            rounded-3 px-4 py-3 bg-dark">
                    <div>
                        <div class="text-white fw-semibold" style="font-size:11px;">
                            TOTAL AMOUNT
                        </div>
                        <div class="text-white-50" style="font-size:10px;">
                            Car + Destination − Discount
                        </div>
                    </div>
                    <span class="fw-bold text-info fs-4">
                        ₱<span id="approveTotalAmount">0.00</span>
                    </span>
                </div>

                <input type="hidden" id="approveFinalAmountInput" value="0.00">
            </div>

            <div class="modal-footer border-0 bg-white px-4 pb-4 pt-2 gap-2">
                <button type="button" class="btn btn-secondary btn-sm px-4"
                        data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-sm px-4"
                        id="proceedToPaymentBtn">
                    Proceed to Payment
                </button>
            </div>

        </div>
    </div>
</div>