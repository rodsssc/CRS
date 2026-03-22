<x-app-layout>

{{--
    ════════════════════════════════════════════════════════════════
    BjCarRental · Client My Bookings
    ════════════════════════════════════════════════════════════════
    JS:  assets/js/client/booking/index.js
    CSS: assets/css/client/booking/booking.css
    ════════════════════════════════════════════════════════════════
--}}

<div class="bk-page">

    {{-- ══════════════════════════════════════════════════════════
         PAGE HEADER
         ══════════════════════════════════════════════════════════ --}}
    <div class="bk-page-header">
        <div class="bk-page-header-inner">
            <div>
                <h1 class="bk-page-title">My Bookings</h1>
                <p class="bk-page-sub">View and track all your car rental bookings</p>
            </div>
            <a href="{{ route('client.car.index') }}" class="bk-browse-btn">
                <i class="fa-solid fa-plus"></i> New Booking
            </a>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         BOOKING TABLE CARD
         ══════════════════════════════════════════════════════════ --}}
    <div class="bk-body"
         id="clientBookingsRoot"
         data-endpoint="{{ route('client.bookings.data') }}"
         data-cars-url="{{ route('client.car.index') }}"
         data-base-url="{{ url('client/bookings') }}">

        {{-- Status message (hidden after load) --}}
        <p class="bk-state-msg" id="clientBookingsState"></p>

        <div class="bk-card">
            <div class="bk-table-wrap">
                <table class="bk-table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Vehicle</th>
                            <th>Trip</th>
                            <th>Dates</th>
                            <th>Status</th>
                            <th class="text-right">Total</th>
                            <th class="text-right">Details</th>
                        </tr>
                    </thead>
                    <tbody id="clientBookingsBody">
                        {{-- Skeleton rows --}}
                        @for ($i = 0; $i < 5; $i++)
                            <tr class="bk-skel-row">
                                <td><div class="bk-skel bk-skel-sm"></div></td>
                                <td>
                                    <div class="bk-skel bk-skel-md mb-1"></div>
                                    <div class="bk-skel bk-skel-sm"></div>
                                </td>
                                <td>
                                    <div class="bk-skel bk-skel-md mb-1"></div>
                                    <div class="bk-skel bk-skel-sm"></div>
                                </td>
                                <td>
                                    <div class="bk-skel bk-skel-md mb-1"></div>
                                    <div class="bk-skel bk-skel-sm"></div>
                                </td>
                                <td><div class="bk-skel bk-skel-badge"></div></td>
                                <td class="text-right"><div class="bk-skel bk-skel-sm"></div></td>
                                <td class="text-right"><div class="bk-skel bk-skel-btn"></div></td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>

    </div>{{-- /.bk-body --}}
</div>{{-- /.bk-page --}}




@push('scripts')
    <script src="{{ asset('assets/js/client/booking/index.js') }}"></script>
@endpush

</x-app-layout>

{{-- ══════════════════════════════════════════════════════════════
     BOOKING DETAIL MODAL
     ══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="bookingDetailModal" tabindex="-1" aria-labelledby="bookingDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content bk-modal-content">

            <div class="modal-header bk-modal-header">
                <div>
                    <h5 class="modal-title bk-modal-title" id="bookingDetailModalLabel">
                        Booking Details
                    </h5>
                    <p class="bk-modal-ref" id="bdRef">—</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body bk-modal-body">

                {{-- Status banner --}}
                <div class="bk-detail-status-banner" id="bdStatusBanner">
                    <i class="fa-solid fa-circle-dot bk-status-icon" id="bdStatusIcon"></i>
                    <div>
                        <span class="bk-detail-status-label" id="bdStatusLabel">—</span>
                        <span class="bk-detail-status-note" id="bdStatusNote"></span>
                    </div>
                </div>

                <div class="row g-4">

                    {{-- Left: Car info --}}
                    <div class="col-md-5">
                        <div class="bk-detail-car-wrap">
                            <div class="bk-detail-car-img-wrap">
                                <img id="bdCarImg"
                                     src=""
                                     alt="Car"
                                     class="bk-detail-car-img"
                                     loading="lazy"
                                     onerror="this.src='{{ asset('images/default-car.png') }}'">
                            </div>
                            <div class="bk-detail-car-name" id="bdCarName">—</div>
                            <div class="bk-detail-car-price" id="bdCarPrice">—</div>
                        </div>
                    </div>

                    {{-- Right: Booking details --}}
                    <div class="col-md-7">

                        {{-- Trip --}}
                        <div class="bk-detail-section">
                            <div class="bk-detail-section-title">
                                <i class="fa-solid fa-route"></i> Trip
                            </div>
                            <div class="bk-detail-trip">
                                <div class="bk-trip-point bk-trip-from">
                                    <span class="bk-trip-dot"></span>
                                    <div>
                                        <div class="bk-trip-label">Pick-up</div>
                                        <div class="bk-trip-value" id="bdFrom">—</div>
                                    </div>
                                </div>
                                <div class="bk-trip-line"></div>
                                <div class="bk-trip-point bk-trip-to">
                                    <span class="bk-trip-dot bk-trip-dot-end"></span>
                                    <div>
                                        <div class="bk-trip-label">Drop-off</div>
                                        <div class="bk-trip-value" id="bdTo">—</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Dates & Duration --}}
                        <div class="bk-detail-section">
                            <div class="bk-detail-section-title">
                                <i class="fa-solid fa-calendar"></i> Rental Period
                            </div>
                            <div class="bk-detail-dates">
                                <div class="bk-detail-date-item">
                                    <span class="bk-detail-date-lbl">Start</span>
                                    <span class="bk-detail-date-val" id="bdStartDate">—</span>
                                </div>
                                <div class="bk-detail-date-item">
                                    <span class="bk-detail-date-lbl">End</span>
                                    <span class="bk-detail-date-val" id="bdEndDate">—</span>
                                </div>
                            </div>
                            <div class="bk-detail-duration" id="bdDuration"></div>
                        </div>

                        {{-- Cost --}}
                        <div class="bk-detail-section">
                            <div class="bk-detail-section-title">
                                <i class="fa-solid fa-receipt"></i> Cost Summary
                            </div>
                            <div class="bk-detail-cost-rows" id="bdCostRows">—</div>
                        </div>

                    </div>
                </div>

            </div>

            <div class="modal-footer bk-modal-footer">
                <div class="bk-modal-footer-note">
                    <i class="fa-solid fa-info-circle"></i>
                    Payment is collected in person upon vehicle pick-up.
                </div>
                <button type="button" class="bk-btn-close-modal" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>