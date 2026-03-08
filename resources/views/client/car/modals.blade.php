<!-- View Car Details Modal -->
<div class="modal fade" id="viewCarModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Car Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <!-- Image -->
                    <div class="col-md-6 mb-3 mb-md-0">
                        <img id="viewCarImage"
                             src=""
                             class="img-fluid rounded"
                             alt="Car Image"
                             style="height: 250px; width: 100%; object-fit: cover;">
                    </div>

                    <!-- Info -->
                    <div class="col-md-6">
                        <h4 id="viewCarTitle" class="mb-2">—</h4>
                        <h5 class="text-primary fw-bold mb-3" id="viewCarPrice">—</h5>

                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">Status</small>
                            <span id="viewCarStatus" class="badge">—</span>
                        </div>

                        <hr>

                        <div class="row g-3 small">
                            <div class="col-6">
                                <p class="text-muted mb-1">Capacity</p>
                                <p class="fw-semibold mb-0" id="viewCarCapacity">—</p>
                            </div>
                            <div class="col-6">
                                <p class="text-muted mb-1">Transmission</p>
                                <p class="fw-semibold mb-0" id="viewCarTransmission">—</p>
                            </div>
                            <div class="col-6">
                                <p class="text-muted mb-1">Fuel Type</p>
                                <p class="fw-semibold mb-0" id="viewCarFuel">—</p>
                            </div>
                            <div class="col-6">
                                <p class="text-muted mb-1">Color</p>
                                <p class="fw-semibold mb-0" id="viewCarColor">—</p>
                            </div>
                            <div class="col-12">
                                <p class="text-muted mb-1">Plate Number</p>
                                <p class="fw-semibold mb-0" id="viewCarPlate">—</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-dark" id="bookNowBtn" data-bs-toggle="modal" data-bs-target="#bookingModal">
                    <i class="fa-solid fa-calendar me-1"></i> Book Now
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Booking Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="bookingForm" method="POST" action="{{ route('client.bookings.store') }}">
                    @csrf
                    
                    <input type="hidden" id="carId" name="car_id">
                    <input type="hidden" id="clientId" name="client_id" value="{{ auth()->id() }}">

                    <!-- Car Info Display -->
                    <div class="alert alert-light border mb-3">
                        <strong id="bookingCarName">—</strong>
                        <p class="text-muted small mb-0 mt-1">₱<span id="bookingCarPrice">0</span>/day</p>
                    </div>

                    <!-- Pick-up Location -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pick-up Location</label>
                        <input type="text" 
                               class="form-control" 
                               name="destinationFrom" 
                               placeholder="Enter pick-up location"
                               required>
                    </div>

                    <!-- Drop-off Location -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Drop-off Location</label>
                        <input type="text" 
                               class="form-control" 
                               name="destinationTo" 
                               placeholder="Enter drop-off location"
                               required>
                    </div>

                    <!-- Start Date -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Start Date & Time</label>
                        <input type="datetime-local" 
                               class="form-control" 
                               name="rental_start_date" 
                               id="rentalStartDate"
                               required>
                    </div>

                    <!-- End Date -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">End Date & Time</label>
                        <input type="datetime-local" 
                               class="form-control" 
                               name="rental_end_date"
                               id="rentalEndDate"
                               required>
                    </div>

                    <!-- Duration -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Total Days</label>
                            <input type="number" 
                                   class="form-control bg-light" 
                                   id="totalDays"
                                   name="total_days"
                                   readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Total Hours</label>
                            <input type="number" 
                                   class="form-control bg-light" 
                                   id="totalHours"
                                   name="total_hours"
                                   readonly>
                        </div>
                    </div>

                    <!-- Price Breakdown -->
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Car Rental:</span>
                                <span id="carAmount">₱0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Destination Fee:</span>
                                <span id="destAmount">₱0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 text-muted">
                                <span>Discount:</span>
                                <span id="discountAmount">-₱0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Total Amount:</span>
                                <span id="finalAmount" class="text-primary">₱0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBookingBtn">
                            <i class="fa-solid fa-check me-2"></i> Confirm Booking
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View Car Modal
    document.querySelectorAll('[data-bs-target="#viewCarModal"]').forEach(btn => {
        btn.addEventListener('click', function() {
            const carId = this.getAttribute('data-car-id');
            fetch(`/client/car/${carId}`)
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        const car = data.data;
                        document.getElementById('viewCarImage').src = car.image_path ? 
                            `/storage/${car.image_path}` : '/images/default-car.png';
                        document.getElementById('viewCarTitle').textContent = `${car.brand} ${car.model}`;
                        document.getElementById('viewCarPrice').textContent = `₱${parseFloat(car.rental_price_per_day).toLocaleString('en-PH', {minimumFractionDigits: 2})}`;
                        document.getElementById('viewCarCapacity').textContent = `${car.capacity} Seater`;
                        document.getElementById('viewCarTransmission').textContent = car.transmission_type;
                        document.getElementById('viewCarFuel').textContent = car.fuel_type;
                        document.getElementById('viewCarColor').textContent = car.color;
                        document.getElementById('viewCarPlate').textContent = car.plate_number;
                        
                        let statusBadge = document.getElementById('viewCarStatus');
                        statusBadge.className = 'badge ';
                        if(car.status === 'available') {
                            statusBadge.classList.add('bg-success');
                        } else if(car.status === 'rented') {
                            statusBadge.classList.add('bg-danger');
                        } else {
                            statusBadge.classList.add('bg-warning');
                        }
                        statusBadge.textContent = car.status.charAt(0).toUpperCase() + car.status.slice(1);

                        // Setup booking button
                        document.getElementById('bookNowBtn').setAttribute('data-car-id', carId);
                        if(car.status !== 'available') {
                            document.getElementById('bookNowBtn').disabled = true;
                        } else {
                            document.getElementById('bookNowBtn').disabled = false;
                        }
                    }
                })
                .catch(err => console.error('Error:', err));
        });
    });

    // Booking Modal
    document.querySelectorAll('[data-bs-target="#bookingModal"]').forEach(btn => {
        btn.addEventListener('click', function() {
            const carId = this.getAttribute('data-car-id');
            fetch(`/client/car/${carId}`)
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        const car = data.data;
                        document.getElementById('carId').value = carId;
                        document.getElementById('bookingCarName').textContent = `${car.brand} ${car.model}`;
                        document.getElementById('bookingCarPrice').textContent = parseFloat(car.rental_price_per_day).toFixed(2);
                    }
                })
                .catch(err => console.error('Error:', err));
        });
    });

    // Calculate duration
    const startDateInput = document.getElementById('rentalStartDate');
    const endDateInput = document.getElementById('rentalEndDate');

    if(startDateInput && endDateInput) {
        function calculateDuration() {
            const start = new Date(startDateInput.value);
            const end = new Date(endDateInput.value);
            
            if(start && end && end > start) {
                const diff = end - start;
                const hours = Math.ceil(diff / (1000 * 60 * 60));
                const days = Math.ceil(hours / 24);
                
                document.getElementById('totalHours').value = hours;
                document.getElementById('totalDays').value = days;
            }
        }

        startDateInput.addEventListener('change', calculateDuration);
        endDateInput.addEventListener('change', calculateDuration);
    }
});
</script>
