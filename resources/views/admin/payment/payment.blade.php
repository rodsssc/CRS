<x-app-layout>
    <style>
        #addPaymentModal.modal { z-index: 1060 !important; }
    </style>

    <div class="container-fluid px-4 py-3">

        {{-- ── HEADER ──────────────────────────────────────────────────────────── --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="page-title mb-0">Payment Management</h2>
            <button class="btn btn-primary btn-sm px-3"
                    data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                <i class="fas fa-plus me-1"></i>Record Payment
            </button>
        </div>

        {{-- ── STATS CARDS ─────────────────────────────────────────────────────── --}}
        <div class="row g-2 mb-3">
            <div class="col-6 col-lg">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-soft">
                        <i class="fas fa-receipt text-primary"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Payments</div>
                        <div class="stat-value">{{ $stats['total'] ?? 0 }}</div>
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
                        <div class="stat-value">{{ $stats['completed'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg">
                <div class="stat-card">
                    <div class="stat-icon bg-warning-soft">
                        <i class="fas fa-hourglass-half text-warning"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Pending</div>
                        <div class="stat-value">{{ $stats['pending'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg">
                <div class="stat-card">
                    <div class="stat-icon bg-danger-soft">
                        <i class="fas fa-times-circle text-danger"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Failed</div>
                        <div class="stat-value">{{ $stats['failed'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg">
                <div class="stat-card">
                    <div class="stat-icon bg-info-soft">
                        <i class="fas fa-coins text-info"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Amount</div>
                        <div class="stat-value">₱{{ number_format($stats['total_amount'] ?? 0, 0) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── TABLE CARD ──────────────────────────────────────────────────────── --}}
        <div class="table-card">

            {{-- Search & Filters --}}
            <div class="table-controls">
                <form method="GET" class="row g-2 align-items-center">
                    <div class="col-md-6 col-lg-5">
                        <div class="search-box">
                            <input type="text"
                                   class="form-control"
                                   name="q"
                                   value="{{ $q ?? '' }}"
                                   placeholder="Search client, car, plate..."
                                   autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-3 col-lg-2 d-flex gap-2">
                        <button class="btn btn-primary btn-sm" type="submit">Search</button>
                        <a class="btn btn-outline-secondary btn-sm"
                           href="{{ route('admin.payment.index') }}">Clear</a>
                    </div>
                    <div class="col-md-3 col-lg-2">
                        <select class="form-select form-select-sm" name="status"
                                onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="pending"   @selected(($status ?? '') === 'pending')>Pending</option>
                            <option value="completed" @selected(($status ?? '') === 'completed')>Completed</option>
                            <option value="failed"    @selected(($status ?? '') === 'failed')>Failed</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-lg-2">
                        <select class="form-select form-select-sm" name="payment_method"
                                onchange="this.form.submit()">
                            <option value="">All Methods</option>
                            @foreach($paymentMethods as $key => $label)
                                <option value="{{ $key }}"
                                        @selected(($paymentMethod ?? '') === $key)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Per page --}}
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
                            <th>Client</th>
                            <th>Amount</th>
                            <th>Remaining</th>
                            <th>Method</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            @php
                                $rental          = $payment->rental;
                                $totalDue        = (float) ($rental->final_amount ?? 0);
                                $totalPaidRental = (float) ($totalsPaid[$payment->rental_id] ?? 0);
                                $remaining       = $totalDue - $totalPaidRental;
                                $methodLabel     = ucfirst(str_replace('_', ' ', $payment->payment_method));
                                $typeLabel       = ucfirst(str_replace('_', ' ', $payment->payment_type));
                                $vehicle         = trim(($rental->car->brand ?? '') . ' ' . ($rental->car->model ?? ''));
                            @endphp
                            <tr>
                                <td class="text-muted fw-semibold">#BK{{ $payment->rental_id }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $rental->client->name ?? '—' }}</div>
                                    <div class="text-muted small">{{ $rental->client->email ?? '—' }}</div>
                                </td>
                                <td class="fw-semibold text-success">
                                    ₱{{ number_format($payment->amount, 2) }}
                                </td>
                                <td>
                                    @if($remaining <= 0)
                                        <span class="text-success small fw-semibold">₱0.00</span>
                                        <span class="d-block text-muted" style="font-size:10px;">Paid</span>
                                    @else
                                        <span class="text-warning small fw-semibold">
                                            ₱{{ number_format($remaining, 2) }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary-soft text-primary">{{ $methodLabel }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-info-soft text-info">{{ $typeLabel }}</span>
                                </td>
                                <td>
                                    @if($payment->status === 'completed')
                                        <span class="badge bg-success-soft text-success">Completed</span>
                                    @elseif($payment->status === 'pending')
                                        <span class="badge bg-warning-soft text-warning">Pending</span>
                                    @else
                                        <span class="badge bg-danger-soft text-danger">Failed</span>
                                    @endif
                                </td>
                                <td class="text-muted small">
                                    {{ $payment->payment_date?->format('M d, Y') ?? '—' }}
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        {{-- Mark Completed / Failed — pending only --}}
                                        @if($payment->status === 'pending')
                                            <form action="{{ route('admin.payment.markCompleted', $payment->id) }}"
                                                  method="POST" class="d-inline mark-completed-form">
                                                @csrf
                                                <button type="submit" class="btn-action" title="Mark as Completed">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.payment.markFailed', $payment->id) }}"
                                                  method="POST" class="d-inline mark-failed-form">
                                                @csrf
                                                <button type="submit" class="btn-action" title="Mark as Failed">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        @endif

                                        {{-- View --}}
                                        <button type="button"
                                                class="btn-action btn-view-payment"
                                                title="View Details"
                                                data-payment-id="{{ $payment->id }}"
                                                data-rental-id="{{ $payment->rental_id }}"
                                                data-total-due="{{ $totalDue }}"
                                                data-total-paid="{{ $totalPaidRental }}"
                                                data-amount="{{ $payment->amount }}"
                                                data-method="{{ $methodLabel }}"
                                                data-type="{{ $typeLabel }}"
                                                data-date="{{ $payment->payment_date?->format('M d, Y h:i A') ?? '' }}"
                                                data-status="{{ $payment->status }}"
                                                data-client-name="{{ $rental->client->name ?? '' }}"
                                                data-client-email="{{ $rental->client->email ?? '' }}"
                                                data-client-phone="{{ $rental->client->phone ?? '' }}"
                                                data-vehicle="{{ $vehicle }}"
                                                data-plate="{{ $rental->car->plate_number ?? '' }}"
                                                data-notes="{{ $payment->notes ?? '' }}">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        {{-- Delete — pending only --}}
                                        @if($payment->status === 'pending')
                                            <button type="button"
                                                    class="btn-action btn-delete-payment"
                                                    title="Delete"
                                                    data-action="{{ route('admin.payment.destroy', $payment->id) }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fs-4 mb-2 d-block"></i>
                                    No payments found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($payments->hasPages())
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                    <div class="text-muted small">
                        Showing {{ $payments->firstItem() }}–{{ $payments->lastItem() }}
                        of {{ $payments->total() }} payments
                    </div>
                    <div>
                        {{ $payments->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- Flag for JS auto-open --}}
    @if(!empty($preselectedBookingId))
        <script>window.ADMIN_PAYMENT_OPEN_WITH_BOOKING = true;</script>
    @endif

    {{-- Pass rentals paid totals to JS --}}
    <script>
        window.RENTALS_PAID = @json($rentalsPaidMap ?? []);
    </script>

    {{-- SweetAlert2 CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Flash success via SweetAlert --}}
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '{{ session('success') }}',
                    confirmButtonColor: '#0d6efd',
                    timer: 3000,
                    timerProgressBar: true,
                });
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: '{{ session('error') }}',
                    confirmButtonColor: '#dc3545',
                });
            });
        </script>
    @endif

    <script src="{{ asset('assets/js/admin/payment/payment.js') }}"></script>

</x-app-layout>


{{-- ═══════════════════════════════════════════════════════════════════════════
     ADD PAYMENT MODAL
     ══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="addPaymentModal" tabindex="-1"
     aria-labelledby="addPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">

            <div class="modal-header bg-header border-0 px-4 py-3">
                <div>
                    <h5 class="modal-title text-white fw-bold mb-0" id="addPaymentModalLabel">
                        <i class="fas fa-receipt me-2"></i>Record Payment
                    </h5>
                    <p class="text-white-50 mb-0" style="font-size:11px;">
                        Select booking and enter payment details
                    </p>
                </div>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 bg-light">

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                        <ul class="mb-0 small">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form id="addPaymentForm" action="{{ route('admin.payment.store') }}" method="POST" novalidate>
                    @csrf

                    {{-- Booking Select --}}
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted mb-1">
                            Select Booking
                        </label>
                        <select name="rental_id" id="addPaymentRentalId"
                                class="form-select form-select-sm" required>
                            <option value="">-- Select Booking --</option>
                            @foreach($rentals as $rental)
                                @php
                                    $paidSoFar   = (float) ($rentalsPaidMap[$rental->id] ?? 0);
                                    $remainingAmt = (float) ($rental->final_amount ?? 0) - $paidSoFar;
                                @endphp
                                <option value="{{ $rental->id }}"
                                        data-final-amount="{{ $rental->final_amount ?? 0 }}"
                                        data-paid="{{ $paidSoFar }}"
                                        data-remaining="{{ $remainingAmt }}"
                                        @selected(old('rental_id', $preselectedBookingId ?? null) == $rental->id)>
                                    #BK{{ $rental->id }} – {{ $rental->client->name ?? 'N/A' }}
                                    (₱{{ number_format($rental->final_amount ?? 0, 2) }})
                                </option>
                            @endforeach
                        </select>
                        @error('rental_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Balance Info Banner — shown after booking is selected --}}
                    <div id="balanceInfoBanner" class="alert alert-info py-2 px-3 mb-3 small d-none">
                        <div class="d-flex justify-content-between">
                            <span>Total Due:</span>
                            <strong id="bannerTotalDue">—</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Already Paid:</span>
                            <strong id="bannerAlreadyPaid">—</strong>
                        </div>
                        <hr class="my-1">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold">Remaining Balance:</span>
                            <strong id="bannerRemaining" class="text-warning">—</strong>
                        </div>
                    </div>

                    {{-- Amount --}}
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted mb-1">Amount</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">₱</span>
                            <input type="number" name="amount" id="addPaymentAmount"
                                   class="form-control" step="0.01" min="0.01"
                                   value="{{ old('amount') }}"
                                   placeholder="0.00" required>
                        </div>
                        <div id="amountError" class="text-danger small mt-1 d-none"></div>
                        @error('amount')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Payment Method --}}
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted mb-1">
                            Payment Method
                        </label>
                        <select name="payment_method" class="form-select form-select-sm" required>
                            <option value="">-- Select Method --</option>
                            @foreach($paymentMethods as $key => $label)
                                <option value="{{ $key }}"
                                        @selected(old('payment_method') == $key)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('payment_method')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Payment Type --}}
                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-muted mb-1">
                            Payment Type
                        </label>
                        <select name="payment_type" id="addPaymentType"
                                class="form-select form-select-sm" required>
                            <option value="downpayment"
                                    @selected(old('payment_type') == 'downpayment')>
                                Down Payment
                            </option>
                            <option value="full_payment"
                                    @selected(old('payment_type') == 'full_payment')>
                                Full Payment
                            </option>
                        </select>
                        <div id="paymentTypeHint" class="text-muted small mt-1 d-none">
                            <i class="fas fa-info-circle me-1"></i>
                            <span id="paymentTypeHintText"></span>
                        </div>
                        @error('payment_type')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Notes --}}
                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-muted mb-1">
                            Notes <span class="text-muted">(optional)</span>
                        </label>
                        <textarea name="notes" class="form-control form-control-sm"
                                  rows="2" placeholder="Add any notes...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" id="addPaymentSubmitBtn"
                                class="btn btn-primary btn-sm flex-grow-1">
                            <i class="fas fa-check me-1"></i>Record Payment
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm"
                                data-bs-dismiss="modal">Cancel</button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════════════════════════════════
     PAYMENT DETAIL MODAL
     ══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" tabindex="-1" id="paymentDetailModal"
     aria-labelledby="pdTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">

            <div class="modal-header border-0 px-4 py-3 bg-header">
                <div>
                    <h5 class="modal-title text-white fw-bold mb-0" id="pdTitle">
                        <i class="fas fa-receipt me-2"></i>Payment
                    </h5>
                    <p class="text-white-50 mb-0" style="font-size:11px;" id="pdSubtitle"></p>
                </div>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                <div class="row g-3">

                    {{-- LEFT --}}
                    <div class="col-md-6 d-flex flex-column gap-3">
                        <div class="bg-white border rounded-3 p-3">
                            <div class="text-muted fw-semibold mb-2"
                                 style="font-size:10px; letter-spacing:.06em;">BALANCE SUMMARY</div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted small">Total due</span>
                                <span class="fw-semibold" id="pdTotalDue">—</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted small">Total paid</span>
                                <span class="fw-semibold text-success" id="pdTotalPaid">—</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2">
                                <span class="fw-semibold small">Remaining</span>
                                <div class="d-flex align-items-center gap-1">
                                    <span class="fw-bold" id="pdRemaining">—</span>
                                    <span id="pdRemainingBadge"
                                          class="badge bg-success-soft text-success ms-1"
                                          style="display:none;">Paid</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white border rounded-3 p-3">
                            <div class="text-muted fw-semibold mb-2"
                                 style="font-size:10px; letter-spacing:.06em;">THIS PAYMENT</div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">Amount</span>
                                <span class="fw-bold text-success fs-5" id="pdAmount">—</span>
                            </div>
                            <div class="d-flex justify-content-between small py-1 border-top">
                                <span class="text-muted">Method</span>
                                <span class="fw-semibold" id="pdMethod">—</span>
                            </div>
                            <div class="d-flex justify-content-between small py-1 border-top">
                                <span class="text-muted">Type</span>
                                <span class="fw-semibold" id="pdType">—</span>
                            </div>
                            <div class="d-flex justify-content-between small py-1 border-top">
                                <span class="text-muted">Date</span>
                                <span id="pdDate">—</span>
                            </div>
                            <div class="d-flex justify-content-between small py-1 border-top">
                                <span class="text-muted">Status</span>
                                <span id="pdStatus">—</span>
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT --}}
                    <div class="col-md-6 d-flex flex-column gap-3">
                        <div class="bg-white border rounded-3 p-3">
                            <div class="text-muted fw-semibold mb-2"
                                 style="font-size:10px; letter-spacing:.06em;">BOOKING & CLIENT</div>
                            <div class="d-flex justify-content-between small py-1 border-bottom">
                                <span class="text-muted">Booking ID</span>
                                <span class="fw-semibold" id="pdBookingId">—</span>
                            </div>
                            <div class="d-flex justify-content-between small py-1 border-bottom">
                                <span class="text-muted">Client</span>
                                <span class="fw-semibold text-end" id="pdClientName">—</span>
                            </div>
                            <div class="d-flex justify-content-between small py-1 border-bottom">
                                <span class="text-muted">Email</span>
                                <span class="text-end" id="pdClientEmail">—</span>
                            </div>
                            <div class="d-flex justify-content-between small py-1 border-bottom">
                                <span class="text-muted">Phone</span>
                                <span id="pdClientPhone">—</span>
                            </div>
                            <div class="d-flex justify-content-between small py-1 border-bottom">
                                <span class="text-muted">Vehicle</span>
                                <span class="fw-semibold text-end" id="pdVehicle">—</span>
                            </div>
                            <div class="d-flex justify-content-between small py-1">
                                <span class="text-muted">Plate</span>
                                <span id="pdPlate">—</span>
                            </div>
                        </div>

                        <div class="bg-white border rounded-3 p-3" id="pdNotesWrap" style="display:none;">
                            <div class="text-muted fw-semibold mb-1"
                                 style="font-size:10px; letter-spacing:.06em;">NOTES</div>
                            <p class="mb-0 small text-muted" id="pdNotes"></p>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

{{-- Hidden delete form (reused for all delete actions) --}}
<form id="deletePaymentForm" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>