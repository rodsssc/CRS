<x-app-layout>
    <div class="container-fluid px-4 py-3">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2 class="page-title mb-0">Car Management</h2>
            </div>
            <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addCarModal">
                <i class="fas fa-plus me-1"></i>Add Car
            </button>
        </div>
       
        <!-- Stats Cards -->
        <div class="row g-2 mb-3">
            <div class="col-6 col-lg">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-soft">
                        <i class="fas fa-car text-primary"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Car</div>
                        <div class="stat-value">{{$cars->count()}}</div>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-lg">
                <div class="stat-card">
                    <div class="stat-icon  bg-info-soft">
                        <i class="fas fa-car-side text-secondary"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Available</div>
                        <div class="stat-value">{{$cars->where('status','available')->count()}}</div>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-lg">
                <div class="stat-card">
                    <div class="stat-icon bg-warning-soft">
                        <i class="fas fa-crown text-warning"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Owners</div>
                        <div class="stat-value">{{$owners->count()}}</div>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-lg">
                <div class="stat-card">
                    <div class="stat-icon bg-danger-soft">
                        <i class="fas fa-wrench text-danger"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Maintenance</div>
                        <div class="stat-value">{{$cars->where('status','maintenance')->count()}}</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg">
                <div class="stat-card">
                    <div class="stat-icon bg-success-soft">
                        <i class="fas fa-taxi text-success"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Rented</div>
                        <div class="stat-value">{{$cars->where('status','rented')->count()}}</div>
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
                            <option selected>All Roles</option>
                            <option>Admin</option>
                            <option>Owner</option>
                            <option>Staff</option>
                            <option>Client</option>
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
                            <th >Owner</th>
                            <th >Plate no.</th>
                            <th >Brand</th>
                            <th >Model</th>
                            <th>Yearmodel</th>
                            <th>Color</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody> 
                       @foreach($cars as $car)
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox">
                                    </div>
                                </td>
                                <td>
                                    <div class="user-info">
                                        <span class="user-name">{{$car->owner->name}}</span>
                                    </div>
                                </td>
                                <td class="text-muted ">
                                    <div class="car-plate-number">
                                        <span>{{$car->plate_number}}</span>
                                    </div>
                                </td>
                                <td class="text-muted">{{$car->brand}}</td>
                                 <td class="text-muted">{{$car->model}}</td>
                                 <td class="text-muted">{{$car->year}}</td>
                                 <td class="text-muted">{{$car->color}}</td>
                                 <td class="text-muted">{{$car->rental_price_per_day}}</td>

                                <td>
                                    @if($car->status === "available")
                                        <span class="status-tag status-available">{{ $car->status}}</span>
                                    @elseif($car->status === "maintenance")
                                         <span class="status-tag status-maintenance">{{ $car->status}}</span>
                                    @elseif($car->status === "rented")
                                         <span class="status-tag status-rented">{{ $car->status}}</span>
                                    @endif
                                    

                                    
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action" title="View" data-car-id="{{$car->id}}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-action" title="Edit" data-car-id="{{$car->id}}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                       
                                        <button class="btn-action" title="Delete" data-car-id="{{$car->id}}">
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

<script src="{{asset('assets/js/admin/car/store.js')}}"></script>
<script src="{{asset('assets/js/admin/car/edit.js')}}"></script>
<script src="{{asset('assets/js/admin/car/show.js')}}"></script>
<script src="{{asset('assets/js/admin/car/update.js')}}"></script>
<script src="{{asset('assets/js/admin/car/delete.js')}}"></script>
{{-- Add Car Modal --}}
<div class="modal fade" tabindex="-1" id="addCarModal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">

            {{-- HEADER --}}
            <div class="modal-header bg-header border-0 px-4 py-3">
                <div>
                    <h6 class="modal-title text-white fw-bold mb-0">
                        <i class="fas fa-car me-2"></i>Add New Car
                    </h6>
                    <p class="text-white-50 mb-0" style="font-size:11px;">Fill in the vehicle details below</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- BODY --}}
            <div class="modal-body p-3 bg-light">
                <form id="addCarForm" novalidate>
                    @csrf

                    {{-- OWNER & PLATE --}}
                    <div class="bg-white border rounded-3 p-3 mb-2">
                        <div class="text-muted fw-semibold mb-2" style="font-size:10px; letter-spacing:.06em;">
                           OWNER & PLATE
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Owner <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-sm" id="owner_id" name="owner_id" required>
                                    <option value="">Select an owner</option>
                                    @foreach($owners ?? [] as $owner)
                                        <option value="{{ $owner->id }}">{{ $owner->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Please select an owner.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Plate Number <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control form-control-sm text-uppercase"
                                       id="plate_number"
                                       name="plate_number"
                                       placeholder="ABC 1234"
                                       maxlength="20"
                                       required>
                                <div class="invalid-feedback">Please enter a valid plate number.</div>
                            </div>
                        </div>
                    </div>

                    {{-- VEHICLE INFORMATION --}}
                    <div class="bg-white border rounded-3 p-3 mb-2">
                        <div class="text-muted fw-semibold mb-2" style="font-size:10px; letter-spacing:.06em;">
                            VEHICLE INFORMATION
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Brand <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control form-control-sm"
                                       id="brand"
                                       name="brand"
                                       placeholder="Toyota, Honda, etc."
                                       maxlength="50"
                                       required>
                                <div class="invalid-feedback">Please enter the car brand.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Model <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control form-control-sm"
                                       id="model"
                                       name="model"
                                       placeholder="Camry, Civic, etc."
                                       maxlength="50"
                                       required>
                                <div class="invalid-feedback">Please enter the car model.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Year <span class="text-danger">*</span>
                                </label>
                                <input type="number"
                                       class="form-control form-control-sm"
                                       id="year"
                                       name="year"
                                       placeholder="2024"
                                       min="1900"
                                       max="2030"
                                       required>
                                <div class="invalid-feedback">Please enter a valid year.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Color <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control form-control-sm"
                                       id="color"
                                       name="color"
                                       placeholder="Red, Blue, etc."
                                       maxlength="30"
                                       required>
                                <div class="invalid-feedback">Please enter the car color.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Capacity <span class="text-danger">*</span>
                                </label>
                                <input type="number"
                                       class="form-control form-control-sm"
                                       id="capacity"
                                       name="capacity"
                                       placeholder="5"
                                       min="1"
                                       max="50"
                                       required>
                                <div class="invalid-feedback">Please enter the passenger capacity.</div>
                            </div>
                        </div>
                    </div>

                    {{-- SPECS & PRICING --}}
                    <div class="bg-white border rounded-3 p-3 mb-2">
                        <div class="text-muted fw-semibold mb-2" style="font-size:10px; letter-spacing:.06em;">
                            SPECS & PRICING
                        </div>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Transmission <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-sm" id="transmission_type" name="transmission_type" required>
                                    <option value="">Select transmission</option>
                                    <option value="manual">Manual</option>
                                    <option value="automatic">Automatic</option>
                                    <option value="cvt">CVT</option>
                                </select>
                                <div class="invalid-feedback">Please select a transmission type.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Fuel Type <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-sm" id="fuel_type" name="fuel_type" required>
                                    <option value="">Select fuel type</option>
                                    <option value="gasoline">Gasoline</option>
                                    <option value="diesel">Diesel</option>
                                    <option value="electric">Electric</option>
                                    <option value="hybrid">Hybrid</option>
                                </select>
                                <div class="invalid-feedback">Please select a fuel type.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Rental Price / Day <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light fw-bold">₱</span>
                                    <input type="number"
                                           class="form-control"
                                           id="rental_price_per_day"
                                           name="rental_price_per_day"
                                           placeholder="1500.00"
                                           min="0"
                                           step="0.01"
                                           required>
                                </div>
                                <div class="invalid-feedback">Please enter a valid rental price.</div>
                            </div>
                        </div>
                    </div>

                    {{-- CAR IMAGE --}}
                    <div class="bg-white border rounded-3 p-3">
                        <div class="text-muted fw-semibold mb-2" style="font-size:10px; letter-spacing:.06em;">
                            CAR IMAGE
                        </div>
                        <input type="file"
                               class="form-control form-control-sm"
                               id="image"
                               name="image"
                               accept="image/png, image/jpeg, image/jpg">
                        <div class="text-muted mt-1" style="font-size:10px;">
                            Accepted: JPG, JPEG, PNG (Max 2MB)
                        </div>
                        <div class="invalid-feedback">Please upload a valid image file.</div>
                    </div>

                </form>
            </div>

            {{-- FOOTER --}}
            <div class="modal-footer border-0 bg-white px-4 pb-4 pt-2 gap-2">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary btn-sm px-4" id="saveCarBtn">
                    <i class="fas fa-save me-1"></i>Add Vehicle
                </button>
            </div>

        </div>
    </div>
</div>
{{-- View Car Modal --}}
<div class="modal fade" tabindex="-1" id="viewCarModal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">

            {{-- HEADER --}}
            <div class="modal-header bg-header border-0 px-4 py-3">
                <div>
                    <h6 class="modal-title text-white fw-bold mb-0">
                        <i class="fas fa-car me-2"></i>Vehicle Details
                    </h6>
                    <p class="text-white-50 mb-0" style="font-size:11px;">View car specifications and information</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            {{-- BODY --}}
            <div class="modal-body p-3 bg-light">
                <div class="row g-3">

                    {{-- LEFT --}}
                    <div class="col-md-5 d-flex flex-column gap-2">

                        {{-- Car Image + Status --}}
                        <div class="position-relative">
                            <img id="viewCarImage"
                                 src="https://via.placeholder.com/400x160?text=No+Image"
                                 alt="Car Image"
                                 class="img-fluid rounded-3 border w-100"
                                 style="height:150px; object-fit:cover;">
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="status-tag status-available shadow-sm" id="viewStatus">Available</span>
                            </div>
                        </div>

                        {{-- Quick Stats --}}
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="bg-white border rounded-3 text-center py-2">
                                    <div class="text-muted" style="font-size:10px;">Year</div>
                                    <div class="fw-bold" style="font-size:11px;" id="viewYear">—</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-white border rounded-3 text-center py-2">
                                    <div class="text-muted" style="font-size:10px;">Capacity</div>
                                    <div class="fw-bold" style="font-size:11px;"><span id="viewCapacity">—</span> Seats</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-white border rounded-3 text-center py-2">
                                    <div class="text-muted" style="font-size:10px;">Transmission</div>
                                    <div class="fw-bold text-capitalize" style="font-size:11px;" id="viewTransmission">—</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-white border rounded-3 text-center py-2">
                                    <div class="text-muted" style="font-size:10px;">Fuel Type</div>
                                    <div class="fw-bold text-capitalize" style="font-size:11px;" id="viewFuelType">—</div>
                                </div>
                            </div>
                        </div>

                        {{-- Price --}}
                        <div class="bg-dark rounded-3 text-center py-2">
                            <div class="text-white-50" style="font-size:10px;">RENTAL PRICE / DAY</div>
                            <div class="fw-bold text-info small">₱<span id="viewRentalPrice">0.00</span></div>
                        </div>

                    </div>

                    {{-- RIGHT --}}
                    <div class="col-md-7 d-flex flex-column gap-2">

                        {{-- Car Name + Plate --}}
                        <div class="bg-white border rounded-3 p-3">
                            <div class="fw-bold" id="viewBrand">—</div>
                            <div class="text-muted" style="font-size:11px;">
                                <i class="fas fa-hashtag me-1"></i><span id="viewPlateNumber">—</span>
                            </div>
                        </div>

                        {{-- Owner --}}
                        <div class="bg-white border rounded-3 p-3">
                            <div class="text-muted fw-semibold mb-2" style="font-size:10px; letter-spacing:.06em;">
                                <i class="fas fa-user me-1"></i>OWNER
                            </div>
                            <div class="fw-bold small" id="viewOwnerName">—</div>
                        </div>

                        {{-- Specifications --}}
                        <div class="bg-white border rounded-3 p-3">
                            <div class="text-muted fw-semibold mb-2" style="font-size:10px; letter-spacing:.06em;">
                                <i class="fas fa-list me-1"></i>SPECIFICATIONS
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="text-muted" style="font-size:10px;">Plate Number</div>
                                    <div class="fw-semibold" style="font-size:11px;" id="viewPlateNumber2">—</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted" style="font-size:10px;">Brand</div>
                                    <div class="fw-semibold" style="font-size:11px;" id="viewBrand2">—</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted" style="font-size:10px;">Model</div>
                                    <div class="fw-semibold" style="font-size:11px;" id="viewModel2">—</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted" style="font-size:10px;">Color</div>
                                    <div class="fw-semibold" style="font-size:11px;" id="viewColor">—</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted" style="font-size:10px;">Year Model</div>
                                    <div class="fw-semibold" style="font-size:11px;" id="viewYear2">—</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted" style="font-size:10px;">Capacity</div>
                                    <div class="fw-semibold" style="font-size:11px;"><span id="viewCapacity2">—</span> Passengers</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted" style="font-size:10px;">Transmission</div>
                                    <div class="fw-semibold text-capitalize" style="font-size:11px;" id="viewTransmission2">—</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted" style="font-size:10px;">Fuel Type</div>
                                    <div class="fw-semibold text-capitalize" style="font-size:11px;" id="viewFuelType2">—</div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- FOOTER --}}
            <div class="modal-footer border-0 bg-white px-4 pb-4 pt-2">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
            </div>

        </div>
    </div>
</div>

{{-- Update Car Modal --}}
<div class="modal fade" tabindex="-1" id="updateCarModal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">

            {{-- HEADER --}}
            <div class="modal-header bg-header border-0 px-4 py-3">
                <div>
                    <h6 class="modal-title text-white fw-bold mb-0">
                        <i class="fas fa-edit me-2"></i>Update Car
                    </h6>
                    <p class="text-white-50 mb-0" style="font-size:11px;">Edit vehicle information and details</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- BODY --}}
            <div class="modal-body p-3 bg-light">
                <form id="updateCarForm" novalidate>
                    @method('PUT')
                    @csrf
                    <input type="hidden" id="update_car_id" name="id">

                    {{-- OWNER & PLATE --}}
                    <div class="bg-white border rounded-3 p-3 mb-2">
                        <div class="text-muted fw-semibold mb-2" style="font-size:10px; letter-spacing:.06em;">
                            OWNER & PLATE
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Owner <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-sm" id="updateOwnerId" name="owner_id" required>
                                    <option value="">Select an owner</option>
                                    @foreach($owners ?? [] as $owner)
                                        <option value="{{ $owner->id }}">{{ $owner->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Please select an owner.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Plate Number <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control form-control-sm text-uppercase"
                                       id="update_plate_number"
                                       name="plate_number"
                                       placeholder="ABC 1234"
                                       maxlength="20"
                                       required>
                                <div class="invalid-feedback">Please enter a valid plate number.</div>
                            </div>
                        </div>
                    </div>

                    {{-- VEHICLE INFO --}}
                    <div class="bg-white border rounded-3 p-3 mb-2">
                        <div class="text-muted fw-semibold mb-2" style="font-size:10px; letter-spacing:.06em;">
                            VEHICLE INFORMATION
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Brand <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control form-control-sm"
                                       id="updateBrand"
                                       name="brand"
                                       placeholder="Toyota, Honda, etc."
                                       maxlength="50"
                                       required>
                                <div class="invalid-feedback">Please enter the car brand.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Model <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control form-control-sm"
                                       id="updateModel"
                                       name="model"
                                       placeholder="Camry, Civic, etc."
                                       maxlength="50"
                                       required>
                                <div class="invalid-feedback">Please enter the car model.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Year <span class="text-danger">*</span>
                                </label>
                                <input type="number"
                                       class="form-control form-control-sm"
                                       id="updateYear"
                                       name="year"
                                       placeholder="2024"
                                       min="1900"
                                       max="2030"
                                       required>
                                <div class="invalid-feedback">Please enter a valid year.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Color <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control form-control-sm"
                                       id="updateColor"
                                       name="color"
                                       placeholder="Red, Blue, etc."
                                       maxlength="30"
                                       required>
                                <div class="invalid-feedback">Please enter the car color.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Capacity <span class="text-danger">*</span>
                                </label>
                                <input type="number"
                                       class="form-control form-control-sm"
                                       id="updateCapacity"
                                       name="capacity"
                                       placeholder="5"
                                       min="1"
                                       max="50"
                                       required>
                                <div class="invalid-feedback">Please enter the passenger capacity.</div>
                            </div>
                        </div>
                    </div>

                    {{-- SPECS & PRICING --}}
                    <div class="bg-white border rounded-3 p-3 mb-2">
                        <div class="text-muted fw-semibold mb-2" style="font-size:10px; letter-spacing:.06em;">
                            SPECS & PRICING
                        </div>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Transmission <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-sm" id="updateTransmissionType" name="transmission_type" required>
                                    <option value="">Select transmission</option>
                                    <option value="manual">Manual</option>
                                    <option value="automatic">Automatic</option>
                                    <option value="cvt">CVT</option>
                                </select>
                                <div class="invalid-feedback">Please select a transmission type.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Fuel Type <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-sm" id="updateFuelType" name="fuel_type" required>
                                    <option value="">Select fuel type</option>
                                    <option value="gasoline">Gasoline</option>
                                    <option value="diesel">Diesel</option>
                                    <option value="electric">Electric</option>
                                    <option value="hybrid">Hybrid</option>
                                </select>
                                <div class="invalid-feedback">Please select a fuel type.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Status <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-sm" id="update_status" name="status" required>
                                    <option value="available">Available</option>
                                    <option value="rented">Rented</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                                <div class="invalid-feedback">Please select a status.</div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Rental Price Per Day <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light fw-bold">₱</span>
                                    <input type="number"
                                           class="form-control"
                                           id="updateRentalPricePerDay"
                                           name="rental_price_per_day"
                                           placeholder="1500.00"
                                           min="0"
                                           step="0.01"
                                           required>
                                </div>
                                <div class="invalid-feedback">Please enter a valid rental price.</div>
                            </div>
                        </div>
                    </div>

                    {{-- CAR IMAGE --}}
                    <div class="bg-white border rounded-3 p-3">
                        <div class="text-muted fw-semibold mb-2" style="font-size:10px; letter-spacing:.06em;">
                           CAR IMAGE
                        </div>
                        <input type="file"
                               class="form-control form-control-sm"
                               id="update_image"
                               name="image"
                               accept="image/png, image/jpeg, image/jpg">
                        <div class="text-muted mt-1" style="font-size:10px;">
                            Accepted: JPG, JPEG, PNG (Max 2MB). Leave empty to keep current image.
                        </div>
                        <div class="mt-2">
                            <img id="current_image_preview"
                                 src=""
                                 alt="Current Image"
                                 class="img-thumbnail rounded-3"
                                 style="max-height:120px; display:none;">
                        </div>
                        <div class="invalid-feedback">Please upload a valid image file.</div>
                    </div>

                </form>
            </div>

            {{-- FOOTER --}}
            <div class="modal-footer border-0 bg-white px-4 pb-4 pt-2 gap-2">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary btn-sm px-4" id="updateCarBtn">
                    <i class="fas fa-save me-1"></i>Update Vehicle
                </button>
            </div>

        </div>
    </div>
</div>