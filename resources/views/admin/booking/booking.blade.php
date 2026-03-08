<x-app-layout>
    <div class="container-fluid px-4 py-3">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2 class="page-title mb-0">Booking Management</h2>
            </div>
           
        </div>

 
            <!-- Stats Cards -->
            <div class="row g-2 mb-3">
                <div class="col-6 col-lg">
                    <div class="stat-card">
                        <div class="stat-icon bg-primary-soft">
                            <!-- Users / Clients -->
                            <i class="fas fa-users text-primary"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">Total Rental</div>
                            <div class="stat-value">
                                {{ $totalBookings }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-6 col-lg">
                    <div class="stat-card">
                        <div class="stat-icon bg-info-soft">
                            <!-- Verified / Check -->
                            <i class="fas fa-user-check text-info"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">onGoing</div>
                            <div class="stat-value">
                                {{ $ongoingCount }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-6 col-lg">
                    <div class="stat-card">
                        <div class="stat-icon bg-warning-soft">
                            <!-- Pending / Clock -->
                            <i class="fas fa-clock text-warning"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">Pending</div>
                            <div class="stat-value">{{$pendingCount}}</div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg">
                    <div class="stat-card">
                        <div class="stat-icon bg-danger-soft">
                            <!-- Rejected / X -->
                            <i class="fas fa-user-times text-danger"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">Completed</div>
                            <div class="stat-value">{{$completedCount}}</div>
                        </div>
                    </div>
                </div>
            
                <div class="col-6 col-lg">
                    <div class="stat-card">
                        <div class="stat-icon bg-danger-soft">
                            <!-- Rejected / X -->
                            <i class="fas fa-user-times text-danger"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">Cancelled</div>
                            <div class="stat-value">{{$cancelledCount}}</div>
                        </div>
                    </div>
                </div>


        
            </div>
 


        <!-- Table Card -->
        <div class="table-card">
            <!-- Search and Controls -->
            <div class="table-controls">
                <div class="row g-2 align-items-center">
                    <!-- Search Bar -->
                    <div class="col-md-6 col-lg-5">
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" 
                                   class="form-control form-control-sm" 
                                   placeholder="Search users...">
                        </div>
                    </div>

                    <!-- Role Filter -->
                    <div class="col-md-3 col-lg-3">
                        <select class="form-select form-select-sm">
                            <option selected>All </option>
                            <option>Verified</option>
                            <option>Pending</option>
                            <option>Reject</option>
                            
                        </select>
                    </div>

                    <!-- Entries Per Page -->
                    <div class="col-md-5 col-lg-2">
                        <select class="form-select form-select-sm">
                            <option>10 per page</option>
                            <option>25 per page</option>
                            <option>50 per page</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table custom-table mb-0">
                    <thead>
                        <tr>
                            <th width="5%">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                </div>
                            </th>
                            <th >Name</th>
                            <th>Phone No.</th>
                            <th >Plate No.</th>
                            <th >Destination From</th>
                            <th >Destination To</th>
                            <th>Rental Start </th>
                            <th>Rental End</th>
                            <th>Total Hours</th>
                            <th>Total Days</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody> 
                        @foreach ($bookings as $booking)                       
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox">
                                    </div>
                                </td>
                                <td>
                                    <div class="user-info">
                                        <span class="client-name">{{ $booking->client->name}}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="user-info">
                                        <span class="client-name">{{ $booking->client->phone }}</span>
                                    </div>
                                </td>
                                <td class="text-muted ">
                                    <div class="car-plate">
                                        <span>{{ $booking->car->plate_number ?? 'N/A' }}</span>
                                    </div>
                                </td>
                               
                                 <td class="text-muted">{{ $booking->destinationFrom }}</td>
                                 <td class="text-muted">{{ $booking->destinationTo }}</td>
                                 <td class="text-muted">{{ $booking->rental_start_date->format('M d, Y h:i a') }}</td>
                                <td class="text-muted">{{ $booking->rental_end_date->format('M d, Y h:i a') }}</td>

                                 <td class="text-muted">{{ $booking->total_hours }}</td>
                                 <td class="text-muted">{{ $booking->total_days }}</td>

                           
                                    <td>
                                        
                                        <span class="status-tag status-pending">{{ $booking->status }}</span>
                                       
                                    </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action" title="View" data-booking-id="{{$booking->id}}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                      
                                        <button class="btn-action" title="Delete"  data-verification-id="" id="rejectVerificationBtn"">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                              
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="table-footer">
                <div class="showing-entries">
                    Showing 1-10
                </div>
                <nav>
                    <ul class="pagination mb-0">
                        <li class="page-item disabled">
                            <a class="page-link" href="#">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

</x-app-layout>


<script src="{{asset('assets/js/admin/booking/show.js')}}"></script>
<script src="{{asset('assets/js/admin/booking/approve.js')}}"></script>


{{-- View Booking Modal --}}
<div class="modal fade" tabindex="-1" id="viewBookingModal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">

            {{-- HEADER --}}
            <div class="modal-header bg-header border-0 px-4 py-3">
                <div>
                    <h6 class="modal-title text-white fw-bold mb-0">
                        <i class="fas fa-file-contract me-2"></i>Booking Details
                    </h6>
                    <p class="text-white-50 mb-0" style="font-size:11px;">View booking and rental information</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            {{-- BODY --}}
            <div class="modal-body p-3 bg-light">

                {{-- CLIENT + CAR QUICK INFO --}}
                <div class="bg-white border rounded-3 p-3 mb-3 d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-bold small" id="viewClientName">—</div>
                        <div class="text-muted" style="font-size:11px;" id="viewClientEmail">—</div>
                        <div class="text-muted" style="font-size:11px;">
                            <i class="fas fa-phone me-1"></i><span id="viewClientPhone">—</span>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold small" id="viewCarName">—</div>
                        <div class="text-muted" style="font-size:11px;">
                            <i class="fas fa-hashtag me-1"></i><span id="viewPlateNumber">—</span>
                        </div>
                        <span id="viewStatusBadge" class="badge rounded-pill mt-1"></span>
                    </div>
                </div>

                <div class="row g-3">

                    {{-- LEFT --}}
                    <div class="col-md-5 d-flex flex-column gap-4">

                        {{-- Car Image --}}
                        <img id="viewCarImage"
                             src="https://via.placeholder.com/400x160?text=No+Image"
                             alt="Car" class="img-fluid rounded-3 border w-100"
                             style="height:140px; object-fit:cover;"
                             onerror="this.src='https://via.placeholder.com/400x160?text=No+Image'">

                        {{-- Car Stats --}}
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="bg-white border rounded-3 text-center py-2">
                                    <div class="text-muted" style="font-size:10px;">Transmission</div>
                                    <div class="fw-bold" style="font-size:11px;" id="viewCarTransmission">—</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-white border rounded-3 text-center py-2">
                                    <div class="text-muted" style="font-size:10px;">Fuel</div>
                                    <div class="fw-bold" style="font-size:11px;" id="viewCarFuel">—</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-white border rounded-3 text-center py-2">
                                    <div class="text-muted" style="font-size:10px;">Capacity</div>
                                    <div class="fw-bold" style="font-size:11px;"><span id="viewCarCapacity">—</span> Seats</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-white border rounded-3 text-center py-2">
                                    <div class="text-muted" style="font-size:10px;">Color</div>
                                    <div class="fw-bold" style="font-size:11px;" id="viewCarColor">—</div>
                                </div>
                            </div>
                        </div>

                        {{-- Price --}}
                        <div class="bg-dark rounded-3 text-center py-2">
                            <div class="text-white-50" style="font-size:10px;">PRICE / DAY</div>
                            <div class="fw-bold text-info small">₱<span id="viewCarPrice">0.00</span></div>
                        </div>

                    </div>

                    {{-- RIGHT --}}
                    <div class="col-md-7 d-flex flex-column gap-2">

                        {{-- Rental Period --}}
                        <div class="bg-white border rounded-3 p-3">
                            <div class="text-muted fw-semibold mb-2" style="font-size:10px; letter-spacing:.06em;">
                                <i class="fas fa-calendar me-1"></i>RENTAL PERIOD
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <div class="bg-light rounded-3 text-center py-2">
                                        <div class="text-muted" style="font-size:10px;">Start</div>
                                        <div id="viewStartDate" class="fw-bold" style="font-size:11px;">—</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-light rounded-3 text-center py-2">
                                        <div class="text-muted" style="font-size:10px;">Return</div>
                                        <div id="viewEndDate" class="fw-bold" style="font-size:11px;">—</div>
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
                            <div class="text-muted fw-semibold mb-2" style="font-size:10px; letter-spacing:.06em;">
                                <i class="fas fa-route me-1"></i>DESTINATIONS
                            </div>
                            <div class="d-flex flex-column gap-1">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-success" style="font-size:9px;"><i class="fas fa-map-marker-alt"></i></span>
                                    <div>
                                        <div class="text-muted" style="font-size:10px;">FROM</div>
                                        <div class="fw-semibold" style="font-size:11px;" id="viewDestinationFrom">—</div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-danger" style="font-size:9px;"><i class="fas fa-flag-checkered"></i></span>
                                    <div>
                                        <div class="text-muted" style="font-size:10px;">TO</div>
                                        <div class="fw-semibold" style="font-size:11px;" id="viewDestinationTo">—</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Payment Summary --}}
                        <div class="bg-white border rounded-3 p-3">
                            <div class="text-muted fw-semibold mb-2" style="font-size:10px; letter-spacing:.06em;">
                                <i class="fas fa-receipt me-1"></i>PAYMENT SUMMARY
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted" style="font-size:11px;">Car Amount</span>
                                <span class="fw-semibold" style="font-size:11px;" id="viewCarAmount">—</span>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted" style="font-size:11px;">Destination Fee</span>
                                <span class="fw-semibold" style="font-size:11px;" id="viewDestinationAmount">—</span>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted" style="font-size:11px;">Discount</span>
                                <span class="fw-semibold text-success" style="font-size:11px;" id="viewDiscountAmount">—</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center rounded-3 px-3 py-2 bg-dark mt-2">
                                <span class="text-white-50 fw-semibold" style="font-size:11px;">TOTAL</span>
                                <span class="fw-bold text-info" id="viewFinalAmount">—</span>
                            </div>
                        </div>

                        {{-- Timestamps --}}
                        <div class="d-flex justify-content-between px-1">
                            <small class="text-muted" style="font-size:10px;">
                                <i class="fas fa-clock me-1"></i>Created: <span id="viewCreatedAt">—</span>
                            </small>
                            <small class="text-muted" style="font-size:10px;">
                                <i class="fas fa-edit me-1"></i>Updated: <span id="viewUpdatedAt">—</span>
                            </small>
                        </div>

                    </div>
                </div>
            </div>

            {{-- FOOTER --}}
            
            <div class="modal-footer border-0 bg-white px-4 pb-4 pt-2 gap-2">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
                <button type="button" class="btn btn-success btn-sm px-4 btn-approve-booking">
                    <i class="fas fa-check me-1"></i>Approve
                </button>
                <button type="button" class="btn btn-danger btn-sm px-4">
                    <i class="fas fa-times me-1"></i>Reject
                </button>
            </div>

        </div>
    </div>
</div>


{{-- Approve Booking Modal --}}
<div class="modal fade" tabindex="-1" id="approveModal" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">

            {{-- HEADER --}}
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

            {{-- BODY --}}
            <div class="modal-body p-4 bg-light">viewBookingId

                {{-- BOOKING QUICK INFO --}}
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
                        <div class="fw-bold text-success small">₱<span id="approveCarRate">0.00</span></div>
                    </div>
                </div>

                {{-- READONLY: Car Amount --}}
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted mb-1">
                        Car Rental Amount
                    </label>
                    <div class="input-group input-group-sm">
                        
                        <input type="number"
                               class="form-control bg-light"
                               name="car_amount"
                               id="approveCarAmount"
                               value="0.00"
                               readonly>
                    </div>
                    <div class="text-muted mt-1" style="font-size:10px;">
                        Auto-computed: <span id="approveDays">0</span> day(s) × ₱<span id="approveRateDisplay">0.00</span>
                    </div>
                </div>

                {{-- EDITABLE: Destination Fee --}}
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted mb-1">
                        Destination Fee
                    </label>
                    <div class="input-group input-group-sm">
                       
                        <input type="number"
                               class="form-control"
                               name="destination_amount"
                               id="approveDestinationAmount"
                               min="0"
                               step="0.01"
                               placeholder="0.00"
                               >
                    </div>
                </div>

                {{-- EDITABLE: Discount --}}
                <div class="mb-4">
                    <label class="form-label small fw-semibold text-muted mb-1">
                        Discount
                    </label>
                    <div class="input-group input-group-sm">
                       
                        <input type="number"
                               class="form-control"
                               name="discount_amount"
                               id="approveDiscountAmount"
                               min="0"
                               step="0.01"
                               placeholder="0.00"
                               >
                    </div>
                </div>

                {{-- TOTAL --}}
                <div class="d-flex justify-content-between align-items-center rounded-3 px-4 py-3 bg-dark">
                    <div>
                        <div class="text-white fw-semibold" style="font-size:11px;">TOTAL AMOUNT</div>
                        <div class="text-white-50" style="font-size:10px;">Car + Destination − Discount</div>
                    </div>
                    <span class="fw-bold text-info fs-4">₱<span id="approveTotalAmount">0.00</span></span>
                </div>

                <input type="hidden" name="final_amount" id="approveFinalAmountInput" value="0.00">

            </div>

            {{-- FOOTER --}}
            <div class="modal-footer border-0 bg-white px-4 pb-4 pt-2 gap-2">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button" class="btn btn-success btn-sm px-4" id="proceedToPaymentBtn">
                    Proceed to Payment
                </button>
            </div>

        </div>
    </div>
</div>


{{-- Payment Modal --}}
<div class="modal fade" tabindex="-1" id="paymentModal" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">

            {{-- HEADER --}}
            <div class="modal-header bg-header border-0 px-4 pt-4 pb-2">
                <div>
                    <h5 class="modal-title text-white fw-bold mb-0" id="paymentModalLabel">
                        <i class="fas fa-cash-register me-2"></i>Payment
                    </h5>
                    <p class="text-white-50 small mb-0">Confirm and record payment for this booking</p>
                </div>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- BODY --}}
            <div class="modal-body p-4 bg-light">



                {{-- PAYMENT TYPE --}}
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted mb-1">
                        Payment Type <span class="text-danger">*</span>
                    </label>
                    <select class="form-select form-select-sm" id="paymentType" name="payment_type">
                        <option value="" selected disabled>Select payment type</option>
                        <option value="full_payment">Full Payment</option>
                        <option value="downpayment">Downpayment</option>
                    </select>
                </div>

                {{-- PAYMENT METHOD --}}
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted mb-1">
                        Payment Method <span class="text-danger">*</span>
                    </label>
                    <select class="form-select form-select-sm" id="paymentMethod" name="payment_method">
                        <option value="" selected disabled>Select payment method</option>
                        <option value="cash">Cash</option>
                        <option value="gcash">GCash</option>
                        <option value="maya">Maya</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="credit_card">Credit Card</option>
                    </select>
                </div>

                {{-- AMOUNT TENDERED --}}
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted mb-1">
                        Amount Tendered <span class="text-danger">*</span>
                    </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">₱</span>
                        <input type="number"
                               class="form-control"
                               id="paymentAmountTendered"
                               name="amount"
                               min="0"
                               step="0.01"
                               placeholder="0.00">
                    </div>
                </div>

                {{-- PAYMENT DATE --}}
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted mb-1">
                        Payment Date <span class="text-danger">*</span>
                    </label>
                    <input type="datetime-local"
                           class="form-control form-control-sm"
                           id="paymentDate"
                           name="payment_date">
                </div>

                {{-- NOTES --}}
                <div class="mb-4">
                    <label class="form-label small fw-semibold text-muted mb-1">
                        Notes <span class="text-muted">(optional)</span>
                    </label>
                    <textarea class="form-control form-control-sm"
                              id="paymentNotes"
                              name="notes"
                              rows="2"
                              placeholder="Add any notes..."></textarea>
                </div>

                {{-- CHANGE --}}
                <div class="d-flex justify-content-between align-items-center rounded-3 px-4 py-3 bg-white border">
                    <div>
                        <div class="fw-semibold" style="font-size:11px;">CHANGE</div>
                        <div class="text-muted" style="font-size:10px;">Amount Tendered − Total Due</div>
                    </div>
                    <span class="fw-bold text-success fs-5">₱<span id="paymentChange">0.00</span></span>
                </div>

            </div>

            {{-- FOOTER --}}
            <div class="modal-footer border-0 bg-white px-4 pb-4 pt-2 gap-2">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button" class="btn btn-success btn-sm px-4" id="confirmPaymentBtn">
                    <i class="fas fa-check me-1"></i>Confirm Payment
                </button>
            </div>

        </div>
    </div>
</div>