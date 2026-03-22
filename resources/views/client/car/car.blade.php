<x-app-layout>

{{--
    ════════════════════════════════════════════════════════════════
    BjCarRental · Client Car Listing
    ════════════════════════════════════════════════════════════════
    All filtering, search, pagination, view + booking are handled
    by assets/js/client/car/car.js — no page reload needed.
    ════════════════════════════════════════════════════════════════
--}}

<div class="cr-page">

    {{-- ══════════════════════════════════════════════════════════
         PAGE HEADER
         ══════════════════════════════════════════════════════════ --}}
    <div class="cr-page-header">
        <div class="cr-page-header-inner">
            <div>
                <h1 class="cr-page-title">Browse Fleet</h1>
                <p class="cr-page-sub">Find the perfect vehicle for your next journey</p>
            </div>
            <div class="cr-result-badge" id="crResultBadge">
                <i class="fa-solid fa-car"></i>
                <span id="crTotalCount">—</span> vehicles
            </div>
        </div>
    </div>

    <div class="cr-body">

        {{-- ══════════════════════════════════════════════════════
             FILTER BAR
             ══════════════════════════════════════════════════════ --}}
        <div class="cr-filter-bar" id="crFilterBar">

            {{-- Row 1: Search + Status + Sort + Reset --}}
            <div class="cr-filter-row">

                {{-- Search (always full-width on mobile, flex:1 on desktop) --}}
                <div class="cr-search-wrap">
                    <i class="fa-solid fa-magnifying-glass cr-search-icon"></i>
                    <input type="text"
                           class="cr-search"
                           id="crSearch"
                           placeholder="Search brand or model…"
                           autocomplete="off">
                    <button class="cr-search-clear" id="crSearchClear" type="button" aria-label="Clear search">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                {{--
                    On mobile: .cr-filter-selects is a 2-col grid
                    (Status | Sort)
                    (Reset ——————— )
                    On tablet+: display:contents dissolves the wrapper
                    so Status, Sort, Reset flow directly into .cr-filter-row
                --}}
                <div class="cr-filter-selects">

                    {{-- Status --}}
                    <div class="cr-select-wrap">
                        <label class="cr-select-label" for="crStatus">Status</label>
                        <select class="cr-select" id="crStatus">
                            <option value="">All Status</option>
                            <option value="available">Available</option>
                            <option value="rented">Rented</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>

                    {{-- Sort --}}
                    <div class="cr-select-wrap">
                        <label class="cr-select-label" for="crSort">Sort By</label>
                        <select class="cr-select" id="crSort">
                            <option value="name_asc">Name A → Z</option>
                            <option value="name_desc">Name Z → A</option>
                            <option value="price_asc">Price: Low → High</option>
                            <option value="price_desc">Price: High → Low</option>
                        </select>
                    </div>

                    {{-- Reset --}}
                    <button class="cr-reset-btn" id="crReset" type="button">
                        <i class="fa-solid fa-rotate-left"></i> Reset Filters
                    </button>

                </div>{{-- /.cr-filter-selects --}}

            </div>{{-- /.cr-filter-row --}}

            {{-- Row 2: Seater capacity chips --}}
            <div class="cr-chips-row">
                <span class="cr-chips-label">
                    <i class="fa-solid fa-users"></i> Capacity:
                </span>

                <div class="cr-chips">
                    <input type="radio" class="cr-chip-input" name="crCapacity" id="crCap0" value="" checked>
                    <label class="cr-chip" for="crCap0">All</label>

                    <input type="radio" class="cr-chip-input" name="crCapacity" id="crCap2" value="2">
                    <label class="cr-chip" for="crCap2"><i class="fa-solid fa-user"></i> 2+</label>

                    <input type="radio" class="cr-chip-input" name="crCapacity" id="crCap4" value="4">
                    <label class="cr-chip" for="crCap4"><i class="fa-solid fa-user"></i> 4+</label>

                    <input type="radio" class="cr-chip-input" name="crCapacity" id="crCap5" value="5">
                    <label class="cr-chip" for="crCap5"><i class="fa-solid fa-user"></i> 5+</label>

                    <input type="radio" class="cr-chip-input" name="crCapacity" id="crCap6" value="6">
                    <label class="cr-chip" for="crCap6"><i class="fa-solid fa-user"></i> 6+</label>

                    <input type="radio" class="cr-chip-input" name="crCapacity" id="crCap7" value="7">
                    <label class="cr-chip" for="crCap7"><i class="fa-solid fa-user"></i> 7+</label>

                    <input type="radio" class="cr-chip-input" name="crCapacity" id="crCap10" value="10">
                    <label class="cr-chip" for="crCap10"><i class="fa-solid fa-user"></i> 10+</label>
                </div>
            </div>

        </div>{{-- /.cr-filter-bar --}}

        {{-- ══════════════════════════════════════════════════════
             CAR GRID  (populated by JS)
             ══════════════════════════════════════════════════════ --}}
        <div class="cr-grid" id="crGrid">
            {{-- 12 skeleton placeholders shown on first load --}}
            @for ($i = 0; $i < 12; $i++)
                <div class="cr-skel">
                    <div class="cr-skel-img"></div>
                    <div class="cr-skel-body">
                        <div class="cr-skel-line cr-sl-w"></div>
                        <div class="cr-skel-line cr-sl-m"></div>
                        <div class="cr-skel-line cr-sl-n"></div>
                        <div class="cr-skel-line cr-sl-btn"></div>
                    </div>
                </div>
            @endfor
        </div>

        {{-- ══════════════════════════════════════════════════════
             PAGINATION  (built by JS)
             ══════════════════════════════════════════════════════ --}}
        <div class="cr-pagination" id="crPagination"></div>

    </div>{{-- /.cr-body --}}
</div>{{-- /.cr-page --}}

{{-- ══════════════════════════════════════════════════════════════
     JS config + script
     ══════════════════════════════════════════════════════════════ --}}
<script>
    window.CAR_CONFIG = {
        dataUrl:    "{{ route('client.car.data') }}",
        showUrl:    "{{ route('client.car.show', ['id' => '__ID__']) }}",
        bookingUrl: "{{ route('client.bookings.store') }}",
        clientId:   {{ auth()->id() }},
    };
</script>
@push('scripts')
<script src="{{ asset('assets/js/client/car/car.js') }}"></script>
@endpush

</x-app-layout>

{{-- ══════════════════════════════════════════════════════════════
     BOOKING MODAL
     ══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content cr-modal-content">

            <div class="modal-header cr-modal-header">
                <h5 class="modal-title cr-modal-title" id="bookingModalLabel">
                    <i class="fa-solid fa-calendar-check me-2"></i> New Booking
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body cr-modal-body">
                <form id="bookingForm" novalidate>
                    @csrf

                    {{-- Hidden Fields --}}
                    <input type="hidden" id="carId"     name="carId"     value="">
                    <input type="hidden" id="client_id" name="client_id" value="{{ auth()->id() }}">

                    {{-- Selected Car Banner --}}
                    <div class="cr-booking-car-banner" id="crBookingCarBanner">
                        <i class="fa-solid fa-car"></i>
                        <span id="crBookingCarName">—</span>
                    </div>

                    {{-- Pick-up Location --}}
                    <div class="cr-form-group">
                        <label class="cr-form-label" for="destinationFrom">
                            <i class="fa-solid fa-location-dot"></i> Pick-up Location
                        </label>
                        <input type="text"
                               class="cr-form-control"
                               id="destinationFrom"
                               name="destinationFrom"
                               placeholder="Enter pick-up location"
                               required>
                    </div>

                    {{-- Drop-off Location --}}
                    <div class="cr-form-group">
                        <label class="cr-form-label" for="destinationTo">
                            <i class="fa-solid fa-location-dot"></i> Drop-off Location
                        </label>
                        <input type="text"
                               class="cr-form-control"
                               id="destinationTo"
                               name="destinationTo"
                               placeholder="Enter drop-off location"
                               required>
                    </div>

                    {{-- Dates — stack on mobile --}}
                    <div class="row g-2">
                        <div class="col-12 col-sm-6">
                            <div class="cr-form-group">
                                <label class="cr-form-label" for="rental_start_date">
                                    <i class="fa-solid fa-calendar-plus"></i> Start Date
                                </label>
                                <input type="datetime-local"
                                       class="cr-form-control"
                                       id="rental_start_date"
                                       name="rental_start_date"
                                       required>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="cr-form-group">
                                <label class="cr-form-label" for="rental_end_date">
                                    <i class="fa-solid fa-calendar-minus"></i> End Date
                                </label>
                                <input type="datetime-local"
                                       class="cr-form-control"
                                       id="rental_end_date"
                                       name="rental_end_date"
                                       required>
                            </div>
                        </div>
                    </div>

                    {{-- Duration Summary (auto-calculated) --}}
                    <div class="cr-duration-summary" id="crDurationSummary" style="display:none">
                        <div class="cr-duration-item">
                            <span class="cr-duration-num" id="total_days_display">0</span>
                            <span class="cr-duration-lbl">Days</span>
                        </div>
                        <div class="cr-duration-sep">·</div>
                        <div class="cr-duration-item">
                            <span class="cr-duration-num" id="total_hours_display">0</span>
                            <span class="cr-duration-lbl">Hours</span>
                        </div>
                        <div class="cr-duration-sep">·</div>
                        <div class="cr-duration-item">
                            <span class="cr-duration-num" id="total_cost_display">—</span>
                            <span class="cr-duration-lbl">Est. Total</span>
                        </div>
                    </div>

                    {{-- Hidden fields for form submission --}}
                    <input type="hidden" id="total_days"  name="total_days">
                    <input type="hidden" id="total_hours" name="total_hours">

                </form>
            </div>

            <div class="modal-footer cr-modal-footer">
                <button type="button" class="cr-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="cr-btn-primary" id="saveBookingBtn">
                    <i class="fa-solid fa-check"></i> Submit Booking
                </button>
            </div>

        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     VIEW CAR MODAL
     ══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="viewCarModal" tabindex="-1" aria-labelledby="viewCarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content cr-modal-content">

            <div class="modal-header cr-modal-header">
                <h5 class="modal-title cr-modal-title" id="viewCarModalLabel">
                    <i class="fa-solid fa-car me-2"></i> Car Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body cr-modal-body">
                <div class="row g-3">

                    {{-- Image --}}
                    <div class="col-12 col-md-6">
                        <div class="cr-modal-img-wrap">
                            <img id="viewCarImage"
                                 src=""
                                 class="cr-modal-img"
                                 alt="Car Image"
                                 loading="lazy"
                                 onerror="this.src='{{ asset('images/default-car.png') }}'">
                            <span class="cr-modal-status-badge" id="viewCarStatusBadge"></span>
                        </div>
                    </div>

                    {{-- Info --}}
                    <div class="col-12 col-md-6">
                        <h4 class="cr-modal-car-title" id="viewCarTitle">—</h4>
                        <p class="cr-modal-price" id="viewCarPrice">—</p>

                        <hr class="cr-modal-divider">

                        <div class="cr-modal-specs">
                            <div class="cr-spec-item">
                                <span class="cr-spec-label"><i class="fa-solid fa-copyright"></i> Brand</span>
                                <span class="cr-spec-value" id="viewCarBrand">—</span>
                            </div>
                            <div class="cr-spec-item">
                                <span class="cr-spec-label"><i class="fa-solid fa-tag"></i> Model</span>
                                <span class="cr-spec-value" id="viewCarModel">—</span>
                            </div>
                            <div class="cr-spec-item">
                                <span class="cr-spec-label"><i class="fa-solid fa-calendar"></i> Year</span>
                                <span class="cr-spec-value" id="viewCarYear">—</span>
                            </div>
                            <div class="cr-spec-item">
                                <span class="cr-spec-label"><i class="fa-solid fa-palette"></i> Color</span>
                                <span class="cr-spec-value" id="viewCarColor">—</span>
                            </div>
                            <div class="cr-spec-item">
                                <span class="cr-spec-label"><i class="fa-solid fa-users"></i> Capacity</span>
                                <span class="cr-spec-value" id="viewCarCapacity">—</span>
                            </div>
                            <div class="cr-spec-item">
                                <span class="cr-spec-label"><i class="fa-solid fa-gears"></i> Transmission</span>
                                <span class="cr-spec-value" id="viewCarTransmission">—</span>
                            </div>
                            <div class="cr-spec-item">
                                <span class="cr-spec-label"><i class="fa-solid fa-gas-pump"></i> Fuel</span>
                                <span class="cr-spec-value" id="viewCarFuel">—</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer cr-modal-footer">
                <button type="button" class="cr-btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button"
                        class="cr-btn-primary"
                        id="bookNowBtn"
                        data-bs-target="#bookingModal"
                        data-bs-toggle="modal">
                    <i class="fa-solid fa-calendar-check"></i> Book Now
                </button>
            </div>

        </div>
    </div>
</div>