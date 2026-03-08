<x-app-layout>
    <div class="container">
        <div class="header-container mb-4">
            <div class="filter-bar">

                <!-- Search + Controls Row -->
                <div class="row g-2 align-items-center mb-3">
                    <div class="col-12 col-md">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fa-solid fa-magnifying-glass text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0 ps-0" placeholder="Search by brand, model...">
                        </div>
                    </div>
                    
                    <div class="col-auto">
                        <button class="btn btn-dark px-4">
                            <i class="fa-solid fa-magnifying-glass me-1"></i> Search
                        </button>
                    </div>
                    <div class="col-12 col-md-auto">
                        <select class="form-select" aria-label="Select availability">
                            <option selected value="">All Status</option>
                            <option value="available">Available</option>
                            <option value="unavailable">Unavailable</option>
                        </select>
                    </div>
                </div>

                <!-- Seater Capacity Row -->
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="text-muted small me-1">
                        <i class="bi bi-people-fill me-1"></i>Seater:
                    </span>

                    <input type="radio" class="btn-check" name="seater" id="seater-all" value="" checked>
                    <label class="seater-card" for="seater-all">All</label>

                    <input type="radio" class="btn-check" name="seater" id="seater-2" value="2">
                    <label class="seater-card" for="seater-2">
                        <i class="bi bi-person-fill"></i> 2
                    </label>

                    <input type="radio" class="btn-check" name="seater" id="seater-4" value="4">
                    <label class="seater-card" for="seater-4">
                        <i class="bi bi-person-fill"></i> 4
                    </label>

                    <input type="radio" class="btn-check" name="seater" id="seater-5" value="5">
                    <label class="seater-card" for="seater-5">
                        <i class="bi bi-person-fill"></i> 5
                    </label>

                    <input type="radio" class="btn-check" name="seater" id="seater-6" value="6">
                    <label class="seater-card" for="seater-6">
                        <i class="bi bi-person-fill"></i> 6
                    </label>

                    <input type="radio" class="btn-check" name="seater" id="seater-7" value="7">
                    <label class="seater-card" for="seater-7">
                        <i class="bi bi-person-fill"></i> 7
                    </label>

                    <input type="radio" class="btn-check" name="seater" id="seater-8" value="8">
                    <label class="seater-card" for="seater-8">
                        <i class="bi bi-person-fill"></i> 8
                    </label>

                    <input type="radio" class="btn-check" name="seater" id="seater-10" value="10">
                    <label class="seater-card" for="seater-10">
                        <i class="bi bi-person-fill"></i> 10+
                    </label>

                </div>

            </div>
        </div>

        <div class="body-container">
            <div class="row g-4">
                <!-- Repeat cards as needed -->
                @foreach($cars as $car)
                 
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="card h-100">
                        <div class="position-relative">
                            <img src="{{ $car->image_path ? asset('storage/' . $car->image_path) : asset('images/default-car.png') }}" 
                                 class="card-img-top" 
                                 alt="{{ $car->brand }} {{ $car->model }}"
                                 style="height: 200px; object-fit: cover;">
                            <span class="badge {{ $car->status === 'available' ? 'bg-success' : ($car->status === 'rented' ? 'bg-danger' : 'bg-warning') }} position-absolute top-0 end-0 m-2">
                                {{ ucfirst($car->status) }}
                            </span>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">{{ $car->brand }} {{ $car->model }}</h5>
                            <p class="card-text">
                                <i class="fa-solid fa-users"></i> {{ $car->capacity }} Seater • 
                                <i class="fa-solid fa-gears"></i> {{ ucfirst($car->transmission_type) }}
                            </p>
                            <p class="fw-bold text-primary">₱{{ number_format($car->rental_price_per_day, 2) }}/day</p>
                            <div class="mt-auto d-flex gap-2">

                                <!-- View modal footer button -->
                                <button type="button" 
                                    class="btn btn-show-action" 
                                    data-bs-target="#viewCarModal" 
                                    data-bs-toggle="modal"
                                    data-car-id="{{$car->id}}">View</button>

                                <!-- Card button -->
                                <button type="button" 
                                    {{ $car->status !== 'available' ? 'disabled' : '' }}
                                    class="btn btn-bg-color"
                                    data-bs-target="#bookingModal"
                                    data-bs-toggle="modal"
                                    data-car-id="{{ $car->id }}">Book Now</button>

                                
                            </div>
                        </div>
                    </div>
                </div>

                

                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
<script src="{{asset('assets/js/client/car/show.js')}}"></script>
<script src="{{asset('assets/js/client/car/book.js')}}"></script>
<!-- Static Car Details Modal -->
<div class="modal fade" id="viewCarModal" tabindex="-1" aria-labelledby="viewCarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-header">
                <h5 class="modal-title" id="viewCarModalLabel">Car Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row">

                    <!-- Car Image -->
                    <div class="col-md-6 mb-3 mb-md-0">
                        <img id="viewCarImage"
                             src="https://via.placeholder.com/400x300"
                             class="img-fluid rounded"
                             alt="Car Image"
                             style="max-height: 300px; object-fit: cover; width: 100%;"
                             onerror="this.src='https://via.placeholder.com/400x300?text=No+Image'">
                    </div>

                    <!-- Car Info -->
                    <div class="col-md-6">
                        <h4 class="mb-3" id="viewCarTitle">—</h4>

                        <div class="mb-3">
                            <h5 class="text-primary mb-0" id="viewCarPrice">—</h5>
                        </div>

                        <hr>

                        <div class="row g-3">

                            <div class="col-6">
                                <p class="mb-1 text-muted small">Brand</p>
                                <p class="mb-0 fw-semibold" id="viewCarBrand">—</p>
                            </div>

                            <div class="col-6">
                                <p class="mb-1 text-muted small">Model</p>
                                <p class="mb-0 fw-semibold" id="viewCarModel">—</p>
                            </div>

                            <div class="col-6">
                                <p class="mb-1 text-muted small">Year</p>
                                <p class="mb-0 fw-semibold" id="viewCarYear">—</p>
                            </div>

                            <div class="col-6">
                                <p class="mb-1 text-muted small">Color</p>
                                <p class="mb-0 fw-semibold" id="viewCarColor">—</p>
                            </div>

                            <div class="col-6">
                                <p class="mb-1 text-muted small">Capacity</p>
                                <p class="mb-0 fw-semibold" id="viewCarCapacity">—</p>
                            </div>

                            <div class="col-6">
                                <p class="mb-1 text-muted small">Transmission</p>
                                <p class="mb-0 fw-semibold" id="viewCarTransmission">—</p>
                            </div>

                            <div class="col-6">
                                <p class="mb-1 text-muted small">Fuel Type</p>
                                <p class="mb-0 fw-semibold" id="viewCarFuel">—</p>
                            </div>

                            <div class="col-12">
                                <p class="mb-1 text-muted small">Status</p>
                                <span class="badge" id="viewCarStatus">—</span>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                
                <button type="button" class="btn btn-bg-color" {{ $car->status !== 'available' ? 'disabled' : '' }} id="bookNowBtn" data-bs-target="#bookingModal" data-bs-toggle="modal">
                    Book Now
                </button>
            </div>

        </div>
    </div>
</div>

<!-- Add this to your booking modal form -->
<!-- Make sure your form has these exact IDs -->

<div class="modal fade" id="bookingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bookingForm">
                    @csrf
                    
                    <!-- Hidden Fields -->
                    <input type="hidden" id="carId" name="carId" value="{{{ $car->id ?? '' }}}">
                    <input type="hidden" id="client_id" name="client_id" value="{{ auth()->id() }}">
                    
                    <!-- Destination From -->
                    <div class="mb-3">
                        <label for="destinationFrom" class="form-label">Pick-up Location</label>
                        <input type="text" 
                               class="form-control" 
                               id="destinationFrom" 
                               name="destinationFrom" 
                               placeholder="Enter pick-up location"
                               required>
                    </div>

                    <!-- Destination To -->
                    <div class="mb-3">
                        <label for="destinationTo" class="form-label">Drop-off Location</label>
                        <input type="text" 
                               class="form-control" 
                               id="destinationTo" 
                               name="destinationTo" 
                               placeholder="Enter drop-off location"
                               required>
                    </div>

                    <!-- Start Date -->
                    <div class="mb-3">
                        <label for="rental_start_date" class="form-label">Start Date & Time</label>
                        <input type="datetime-local" 
                               class="form-control" 
                               id="rental_start_date" 
                               name="rental_start_date" 
                               required>
                    </div>

                    <!-- End Date -->
                    <div class="mb-3">
                        <label for="rental_end_date" class="form-label">End Date & Time</label>
                        <input type="datetime-local" 
                               class="form-control" 
                               id="rental_end_date" 
                               name="rental_end_date" 
                               required>
                    </div>

                    <!-- Auto-calculated Duration (Read-only) -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="total_days" class="form-label">Total Days</label>
                            <input type="number" 
                                   class="form-control bg-light" 
                                   id="total_days" 
                                   name="total_days" 
                                   readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="total_hours" class="form-label">Total Hours</label>
                            <input type="number" 
                                   class="form-control bg-light" 
                                   id="total_hours" 
                                   name="total_hours" 
                                   readonly>
                        </div>
                    </div>

                    

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveBookingBtn">
                    <i class="fas fa-save me-1"></i> Submit Booking
                </button>
            </div>
        </div>
    </div>
</div>
