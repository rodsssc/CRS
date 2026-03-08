<x-app-layout>

    <div class="container py-4" id="clientBookingsRoot"
         data-endpoint="{{ route('client.bookings.data') }}">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 mb-0">My Bookings</h1>
            <span id="clientBookingsState" class="text-muted small">
                Loading your bookings…
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th scope="col">Reference</th>
                    <th scope="col">Car</th>
                    <th scope="col">Trip</th>
                    <th scope="col">Dates</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="text-end">Total</th>
                </tr>
                </thead>
                <tbody id="clientBookingsBody">
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        Loading bookings…
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
        <script defer src="{{ asset('assets/js/client/booking/index.js') }}"></script>
    @endpush

</x-app-layout>