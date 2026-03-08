<x-app-layout>
    @php
        $isPayEnabled = $booking->status === 'ongoing' && (float) $booking->final_amount > 0;
        $totalDue = (float) $booking->final_amount > 0 ? (float) $booking->final_amount : (float) $booking->car_amount;
    @endphp
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Header -->
                <div class="mb-5">
                    <h1 class="fw-bold mb-2">Complete Your Payment</h1>
                    <p class="text-muted">Booking Reference: <strong>#BK-{{ $booking->id }}</strong></p>
                </div>

                @unless($isPayEnabled)
                    <div class="alert alert-warning d-flex align-items-start mb-4" role="alert">
                        <i class="bi bi-hourglass-split me-2 mt-1"></i>
                        <div>
                            <strong>Waiting for approval.</strong>
                            <div class="small">Payment will be enabled once admin approves your booking and sets the final amount.</div>
                        </div>
                    </div>
                @endunless

                <!-- Booking Summary Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light border-0 p-4">
                        <h5 class="fw-bold mb-0">Booking Summary</h5>
                    </div>
                    <div class="card-body p-4">
                        <!-- Car Info -->
                        <div class="d-flex align-items-center gap-3 mb-4 pb-4 border-bottom">
                            <div class="flex-grow-1">
                                <p class="small text-muted mb-1">Vehicle</p>
                                <h6 class="fw-bold mb-0">{{ $booking->car->brand }} {{ $booking->car->model }} ({{ $booking->car->year }})</h6>
                                <p class="text-muted small mb-0">Plate: <strong>{{ $booking->car->plate_number }}</strong></p>
                            </div>
                            <div class="text-end">
                                @if($booking->car->image_path)
                                    <img src="/storage/{{ $booking->car->image_path }}" 
                                         alt="Car" 
                                         class="img-fluid rounded"
                                         style="height: 80px; width: 120px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded p-3" style="height: 80px; width: 120px; display: flex; align-items: center; justify-content: center;">
                                        <small class="text-muted">No Image</small>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Rental Details -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p class="small text-muted mb-1">Pick-up Location</p>
                                <p class="fw-semibold mb-3">{{ $booking->destinationFrom }}</p>

                                <p class="small text-muted mb-1">Start Date & Time</p>
                                <p class="fw-semibold mb-0">{{ $booking->rental_start_date->format('M d, Y h:i A') }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="small text-muted mb-1">Drop-off Location</p>
                                <p class="fw-semibold mb-3">{{ $booking->destinationTo }}</p>

                                <p class="small text-muted mb-1">End Date & Time</p>
                                <p class="fw-semibold mb-0">{{ $booking->rental_end_date->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>

                        <!-- Duration -->
                        <div class="row mb-4 pb-4 border-bottom">
                            <div class="col-md-6">
                                <p class="small text-muted mb-1">Total Days</p>
                                <p class="fw-semibold">{{ $booking->total_days }} day(s)</p>
                            </div>
                            <div class="col-md-6">
                                <p class="small text-muted mb-1">Total Hours</p>
                                <p class="fw-semibold">{{ $booking->total_hours }} hours</p>
                            </div>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <p class="small text-muted mb-1">Daily Rate</p>
                                <p class="fw-semibold">₱{{ number_format($booking->car->rental_price_per_day, 2) }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="small text-muted mb-1">Car Rental Amount</p>
                                <p class="fw-semibold">₱{{ number_format($booking->car_amount, 2) }}</p>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="bg-light p-4 rounded-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted small mb-1">Total Amount to Pay</p>
                                    <p class="fw-bold">{{ (float) $booking->final_amount > 0 ? 'Final Amount' : 'Car Rental' }}</p>
                                </div>
                                <div class="text-end">
                                    <h5 class="text-primary fw-bold mb-0">₱{{ number_format($totalDue, 2) }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Form Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light border-0 p-4">
                        <h5 class="fw-bold mb-0">Payment Information</h5>
                    </div>
                    <div class="card-body p-4">
                        <form id="paymentForm">
                            @csrf

                            <!-- Hidden rental ID -->
                            <input type="hidden" name="rental_id" value="{{ $booking->id }}">

                            <!-- Payment Method -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Payment Method</label>
                                <select class="form-select form-select-lg" id="paymentMethod" name="payment_method" required {{ $isPayEnabled ? '' : 'disabled' }}>
                                    <option value="">-- Select a payment method --</option>
                                    <option value="gcash">GCash</option>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cash">Cash Payment</option>
                                </select>
                            </div>

                            <!-- Payment Type -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Payment Type</label>
                                <select class="form-select form-select-lg" id="paymentType" name="payment_type" required {{ $isPayEnabled ? '' : 'disabled' }}>
                                    <option value="">-- Select payment type --</option>
                                    <option value="full_payment">Full Payment - ₱{{ number_format($totalDue, 2) }}</option>
                                    <option value="downpayment">Partial Payment (Down Payment)</option>
                                </select>
                            </div>

                            <!-- Partial Amount (shown only if partial is selected) -->
                            <div class="mb-4" id="partialAmountDiv" style="display: none;">
                                <label class="form-label fw-semibold">Partial Payment Amount</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="partialAmount" 
                                           name="amount_partial" 
                                           placeholder="0.00"
                                           step="0.01"
                                           min="0.01"
                                           {{ $isPayEnabled ? '' : 'disabled' }}>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    Maximum: ₱{{ number_format($totalDue, 2) }}
                                </small>
                            </div>

                            <!-- Full Amount Input (hidden) -->
                            <input type="hidden" id="fullAmount" name="amount_full" value="{{ $totalDue }}">

                            <!-- Additional Notes -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Additional Notes (Optional)</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any additional information..." {{ $isPayEnabled ? '' : 'disabled' }}></textarea>
                            </div>

                            <!-- Terms Checkbox -->
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="termsCheck" required>
                                <label class="form-check-label" for="termsCheck">
                                    I agree to the terms and conditions of this booking and payment.
                                </label>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg fw-semibold" id="submitPaymentBtn" {{ $isPayEnabled ? '' : 'disabled' }}>
                                    <i class="fas fa-credit-card me-2"></i> Complete Payment
                                </button>
                                <a href="{{ route('client.car.index') }}" class="btn btn-outline-secondary btn-lg">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const paymentTypeSelect = document.getElementById('paymentType');
            const partialAmountDiv = document.getElementById('partialAmountDiv');
            const partialAmountInput = document.getElementById('partialAmount');
            const paymentForm = document.getElementById('paymentForm');
            const submitBtn = document.getElementById('submitPaymentBtn');

            // Show/hide partial amount input based on payment type selection
            paymentTypeSelect.addEventListener('change', function() {
                if (this.value === 'downpayment') {
                    partialAmountDiv.style.display = 'block';
                    partialAmountInput.required = true;
                } else {
                    partialAmountDiv.style.display = 'none';
                    partialAmountInput.required = false;
                    partialAmountInput.value = '';
                }
            });

            // Handle form submission
            paymentForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                // Validate form
                if (!document.getElementById('paymentMethod').value) {
                    alert('Please select a payment method');
                    return;
                }

                if (!document.getElementById('paymentType').value) {
                    alert('Please select a payment type');
                    return;
                }

                // Build payment amount
                let paymentAmount = {{ $totalDue }};
                if (document.getElementById('paymentType').value === 'downpayment') {
                    const amount = parseFloat(partialAmountInput.value);
                    const maxAmount = {{ $totalDue }};

                    if (!amount || amount <= 0) {
                        alert('Please enter a valid partial amount');
                        return;
                    }

                    if (amount > maxAmount) {
                        alert('Partial amount cannot exceed ₱' + maxAmount.toFixed(2));
                        return;
                    }
                    
                    paymentAmount = amount;
                }

                // Show loading
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Processing...';

                try {
                    const paymentData = {
                        rental_id: document.querySelector('input[name="rental_id"]').value,
                        payment_method: document.getElementById('paymentMethod').value,
                        payment_type: document.getElementById('paymentType').value,
                        amount: paymentAmount,
                        notes: document.getElementById('notes').value || null
                    };

                    const response = await fetch('{{ route("client.bookings.payment.store", $booking->id) }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken()
                        },
                        body: JSON.stringify(paymentData)
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Payment Successful',
                            text: data.message || 'Your payment has been submitted.',
                            confirmButtonColor: '#3085d6'
                        }).then(() => {
                            window.location.href = '{{ route("client.bookings.index") }}';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Payment Failed',
                            text: data.message || 'An error occurred while processing your payment.',
                            confirmButtonColor: '#3085d6'
                        });
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-credit-card me-2"></i> Complete Payment';
                    }

                } catch (error) {
                    console.error('Payment error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Server Error',
                        text: 'Something went wrong. Please try again.',
                        confirmButtonColor: '#3085d6'
                    });
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-credit-card me-2"></i> Complete Payment';
                }
            });
        });
    </script>
</x-app-layout>
