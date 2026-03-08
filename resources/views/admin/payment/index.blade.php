
    <x-app-layout>
        {{-- Keep modal above backdrop so inputs work when opened --}}
        <style>
            #addPaymentModal.modal { z-index: 1060 !important; }
        </style>
        <div class="container-fluid px-4 py-3">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="page-title mb-0">Payment Management</h2>
                </div>
                <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                    <i class="fas fa-plus me-1"></i>Record Payment
                </button>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Stats Cards -->
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

            <!-- Table Card -->
            <div class="table-card">
                <!-- Search and Controls -->
                <div class="table-controls">
                    <form method="GET" class="row g-2 align-items-center">
                        <!-- Search Bar -->
                        <div class="col-md-6 col-lg-5">
                            <div class="search-box">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" 
                                       class="form-control form-control-sm" 
                                       name="q"
                                       value="{{ $q ?? '' }}"
                                       placeholder="Search client, car, plate..."
                                       autocomplete="off">
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div class="col-md-3 col-lg-2">
                            <select class="form-select form-select-sm" name="status" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="pending" @selected(($status ?? '') === 'pending')>Pending</option>
                                <option value="completed" @selected(($status ?? '') === 'completed')>Completed</option>
                                <option value="failed" @selected(($status ?? '') === 'failed')>Failed</option>
                            </select>
                        </div>

                        <!-- Payment Method Filter -->
                        <div class="col-md-3 col-lg-2">
                            <select class="form-select form-select-sm" name="payment_method" onchange="this.form.submit()">
                                <option value="">All Methods</option>
                                @foreach($paymentMethods as $key => $label)
                                    <option value="{{ $key }}" @selected(($paymentMethod ?? '') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Per Page -->
                        <div class="col-md-5 col-lg-2">
                            <select class="form-select form-select-sm" name="per_page" onchange="this.form.submit()">
                                <option value="10" @selected(($perPage ?? 10) == 10)>10 / page</option>
                                <option value="25" @selected(($perPage ?? 10) == 25)>25 / page</option>
                                <option value="50" @selected(($perPage ?? 10) == 50)>50 / page</option>
                            </select>
                        </div>

                        <!-- Submit -->
                        <div class="col-md-3 col-lg-2 d-flex gap-2 justify-content-md-end">
                            <button class="btn btn-primary btn-sm px-3" type="submit">Search</button>
                            <a class="btn btn-outline-secondary btn-sm px-3" href="{{ route('admin.payment.index') }}">Clear</a>
                        </div>
                    </form>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table custom-table mb-0">
                        <thead>
                            <tr>
                                <th width="7%">Booking ID</th>
                                <th width="18%">Client</th>
                                <th width="10%">Amount</th>
                                <th width="10%">Remaining</th>
                                <th width="10%">Method</th>
                                <th width="10%">Type</th>
                                <th width="8%">Status</th>
                                <th width="10%">Date</th>
                                <th width="17%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($payments as $payment)
                                @php
                                    $rental = $payment->rental;
                                    $totalDue = (float) ($rental->final_amount ?? 0);
                                    $totalPaidForRental = (float) ($totalsPaid[$payment->rental_id] ?? 0);
                                    $remaining = $totalDue - $totalPaidForRental;
                                @endphp
                                <tr>
                                    <td class="text-muted fw-semibold">#{{ $payment->rental_id }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $payment->rental->client->name ?? '—' }}</div>
                                        <div class="text-muted small">{{ $payment->rental->client->email ?? '—' }}</div>
                                    </td>
                                    <td class="fw-semibold text-success">₱{{ number_format($payment->amount, 2) }}</td>
                                    <td>
                                        @if($remaining <= 0)
                                            <span class="text-success small fw-semibold">₱0.00</span>
                                            <span class="d-block text-muted" style="font-size:10px;">Paid</span>
                                        @else
                                            <span class="text-warning small fw-semibold">₱{{ number_format($remaining, 2) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-soft text-primary">
                                            {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-soft text-info">
                                            {{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}
                                        </span>
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
                                    <td class="text-muted small">{{ $payment->payment_date?->format('M d, Y') ?? '—' }}</td>
                                    <td>
                                        <div class="action-buttons">
                                            @if($payment->status === 'pending')
                                                <form action="{{ route('admin.payment.markCompleted', $payment->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn-action" title="Mark as Completed">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.payment.markFailed', $payment->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn-action" title="Mark as Failed">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <button type="button" class="btn-action" title="View Details" data-bs-toggle="modal" data-bs-target="#paymentDetail{{ $payment->id }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if($payment->status === 'pending')
                                                <form action="{{ route('admin.payment.destroy', $payment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this payment?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-action" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>

                                <!-- Payment detail view (centered modal) -->
                                <div class="modal fade" tabindex="-1" id="paymentDetail{{ $payment->id }}" aria-labelledby="paymentDetailLabel{{ $payment->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-md">
                                        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">
                                            <div class="modal-header border-0 px-4 py-3 bg-header">
                                                <div>
                                                    <h5 class="modal-title text-white fw-bold mb-0" id="paymentDetailLabel{{ $payment->id }}">
                                                        <i class="fas fa-receipt me-2"></i>Payment #{{ $payment->id }}
                                                    </h5>
                                                    <p class="text-white-50 mb-0" style="font-size:11px;">Booking #{{ $payment->rental_id }} · {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</p>
                                                </div>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4 bg-light">
                                        @php
                                            $r = $payment->rental;
                                            $due = (float) ($r->final_amount ?? 0);
                                            $paid = (float) ($totalsPaid[$payment->rental_id] ?? 0);
                                            $rem = $due - $paid;
                                        @endphp
                                        {{-- Balance summary --}}
                                        <div class="bg-white border rounded-3 p-3 mb-4">
                                            <div class="text-muted fw-semibold mb-2" style="font-size:10px; letter-spacing:.06em;">BALANCE SUMMARY</div>
                                            <div class="d-flex justify-content-between py-2 border-bottom">
                                                <span class="text-muted small">Total due (booking)</span>
                                                <span class="fw-semibold">₱{{ number_format($due, 2) }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between py-2 border-bottom">
                                                <span class="text-muted small">Total paid</span>
                                                <span class="fw-semibold text-success">₱{{ number_format($paid, 2) }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center pt-2">
                                                <span class="fw-semibold small">Remaining balance</span>
                                                @if($rem <= 0)
                                                    <span class="fw-bold text-success">₱0.00</span>
                                                    <span class="badge bg-success-soft text-success ms-1">Paid</span>
                                                @else
                                                    <span class="fw-bold text-warning">₱{{ number_format($rem, 2) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        {{-- This payment --}}
                                        <div class="bg-white border rounded-3 p-3 mb-4">
                                            <div class="text-muted fw-semibold mb-2" style="font-size:10px; letter-spacing:.06em;">THIS PAYMENT</div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-muted small">Amount</span>
                                                <span class="fw-bold text-success fs-5">₱{{ number_format($payment->amount, 2) }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mt-2 small">
                                                <span class="text-muted">Method</span>
                                                <span class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between small">
                                                <span class="text-muted">Type</span>
                                                <span class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between small">
                                                <span class="text-muted">Date</span>
                                                <span>{{ $payment->payment_date ? $payment->payment_date->format('M d, Y h:i A') : '—' }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between small mt-1">
                                                <span class="text-muted">Status</span>
                                                @if($payment->status === 'completed')
                                                    <span class="badge bg-success-soft text-success">Completed</span>
                                                @elseif($payment->status === 'pending')
                                                    <span class="badge bg-warning-soft text-warning">Pending</span>
                                                @else
                                                    <span class="badge bg-danger-soft text-danger">Failed</span>
                                                @endif
                                            </div>
                                        </div>
                                        {{-- Booking & client --}}
                                        <div class="bg-white border rounded-3 p-3 mb-3">
                                            <div class="text-muted fw-semibold mb-2" style="font-size:10px; letter-spacing:.06em;">BOOKING & CLIENT</div>
                                            <div class="mb-2">
                                                <span class="text-muted small">Booking ID</span>
                                                <p class="fw-semibold mb-0">#{{ $payment->rental_id }}</p>
                                            </div>
                                            <div class="mb-2">
                                                <span class="text-muted small">Client</span>
                                                <p class="fw-semibold mb-0">{{ $payment->rental->client->name ?? '—' }}</p>
                                                <p class="text-muted small mb-0">{{ $payment->rental->client->email ?? '—' }}<br>{{ $payment->rental->client->phone ?? '—' }}</p>
                                            </div>
                                            <div>
                                                <span class="text-muted small">Vehicle</span>
                                                <p class="fw-semibold mb-0">{{ $payment->rental->car->brand ?? '' }} {{ $payment->rental->car->model ?? '' }}</p>
                                                <p class="text-muted small mb-0">{{ $payment->rental->car->plate_number ?? '—' }}</p>
                                            </div>
                                        </div>
                                        @if($payment->notes)
                                            <div class="bg-white border rounded-3 p-3">
                                                <div class="text-muted fw-semibold mb-1" style="font-size:10px;">Notes</div>
                                                <p class="mb-0 small">{{ $payment->notes }}</p>
                                            </div>
                                        @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
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

                <!-- Table footer (same pattern as User, Booking) -->
                <div class="table-footer">
                    <div class="showing-entries">
                        @if ($payments->total() > 0)
                            Showing {{ $payments->firstItem() }}-{{ $payments->lastItem() }} of {{ $payments->total() }}
                        @else
                            Showing 0 of 0
                        @endif
                    </div>
                    <div>
                        {{ $payments->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Payment Modal (same style as User/Booking modals) -->
        <div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">
                    <div class="modal-header bg-header border-0 px-4 py-3">
                        <div>
                            <h5 class="modal-title text-white fw-bold mb-0" id="addPaymentModalLabel">
                                <i class="fas fa-receipt me-2"></i>Record Payment
                            </h5>
                            <p class="text-white-50 mb-0" style="font-size:11px;">Select booking and enter payment details</p>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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

                        <form action="{{ route('admin.payment.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted mb-1">Select Booking</label>
                                <select name="rental_id" id="addPaymentRentalId" class="form-select form-select-sm" required>
                                    <option value="">-- Select Booking --</option>
                                    @foreach($rentals as $rental)
                                        <option value="{{ $rental->id }}" data-final-amount="{{ $rental->final_amount ?? 0 }}" @selected(old('rental_id', $preselectedBookingId ?? null) == $rental->id)>
                                            #{{ $rental->id }} - {{ $rental->client->name ?? 'N/A' }} (₱{{ number_format($rental->final_amount ?? 0, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('rental_id') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted mb-1">Amount</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" name="amount" id="addPaymentAmount" class="form-control" step="0.01" min="0.01" value="{{ old('amount', $preselectedRental ? $preselectedRental->final_amount : '') }}" placeholder="0.00" required>
                                </div>
                                @error('amount') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted mb-1">Payment Method</label>
                                <select name="payment_method" class="form-select form-select-sm" required>
                                    <option value="">-- Select Method --</option>
                                    @foreach($paymentMethods as $key => $label)
                                        <option value="{{ $key }}" @selected(old('payment_method') == $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('payment_method') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted mb-1">Payment Type</label>
                                <select name="payment_type" class="form-select form-select-sm" required>
                                    <option value="downpayment" @selected(old('payment_type') == 'downpayment')>Down Payment</option>
                                    <option value="full_payment" @selected(old('payment_type') == 'full_payment')>Full Payment</option>
                                </select>
                                @error('payment_type') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-semibold text-muted mb-1">Notes (Optional)</label>
                                <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Add any notes...">{{ old('notes') }}</textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                                    <i class="fas fa-check me-1"></i>Record Payment
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($preselectedBookingId))
        <script>window.ADMIN_PAYMENT_OPEN_WITH_BOOKING = true;</script>
        @endif
        <script src="{{ asset('assets/js/admin/payment/index.js') }}"></script>
    </x-app-layout>


