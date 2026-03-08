// booking / show.js

document.addEventListener('DOMContentLoaded', function() {
    const viewBookingModal = document.getElementById('viewBookingModal');

    if (!viewBookingModal) {
        console.error('viewBookingModal not found');
        return;
    }

    document.addEventListener('click', function(e) {
        const button = e.target.closest('.btn-action');
        if (button) {
            const bookingId = button.getAttribute('data-booking-id');
            if (bookingId) viewBooking(bookingId);
        }
    });
});

async function viewBooking(bookingId) {
    try {
        Swal.fire({
            title: 'Loading...',
            text: 'Please wait while we fetch the booking details',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
            showConfirmButton: false
        });

        const response = await fetch(`/admin/bookings/${bookingId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        });

        const data = await response.json();
        Swal.close();

        if (response.ok && data) {
            populateBookingModal(data);
            openBookingModal();
        } else {
            showError(data.message || 'Failed to load booking data');
        }

    } catch (error) {
        Swal.close();
        console.error('Error loading booking:', error);
        showError('An error occurred while loading booking data.');
    }
}

function populateBookingModal(booking) {
    const car = booking.car || {};
    const client = booking.client || {};

    const approveBtn = document.querySelector('.btn-approve-booking');
    if (approveBtn) approveBtn.dataset.id = booking.id;

    // Booking ID
    setElText('viewBookingId', `#BK-${booking.id || ''}`);

    // Booking Status Badge
    const statusBadge = document.getElementById('viewStatusBadge');
    if (statusBadge) {
        const status = (booking.status || '').toLowerCase();
        statusBadge.textContent = capitalize(status);
        statusBadge.className = `badge rounded-pill ${getStatusClass(status)}`;
    }

    // Car Image
    const carImage = document.getElementById('viewCarImage');
    if (carImage) {
        carImage.src = car.image_path ?
            `/storage/${car.image_path}` :
            'https://via.placeholder.com/500x200?text=No+Image';
        carImage.alt = `${car.brand || ''} ${car.model || ''}`.trim();
    }



    // Car Details
    setElText('viewCarName', `${car.brand || 'N/A'} ${car.model || ''} ${car.year || ''}`.trim());
    setElText('viewPlateNumber', car.plate_number || 'N/A');
    setElText('viewCarColor', capitalize(car.color) || 'N/A');
    setElText('viewCarTransmission', capitalize(car.transmission_type) || 'N/A');
    setElText('viewCarFuel', capitalize(car.fuel_type) || 'N/A');
    setElText('viewCarCapacity', car.capacity || 'N/A');
    setElText('viewCarPrice', car.rental_price_per_day ?
        formatMoney(car.rental_price_per_day) : '0.00');

    // Client
    const clientName = client.name || 'N/A';
    setElText('viewClientName', clientName);
    setElText('viewClientEmail', client.email || 'N/A');
    setElText('viewClientPhone', client.phone || 'N/A');



    // Dates
    setElText('viewStartDate', formatDate(booking.rental_start_date));
    setElText('viewEndDate', formatDate(booking.rental_end_date));
    setElText('viewTotalDays', booking.total_days != null ? `${booking.total_days} Day(s)` : 'N/A');
    setElText('viewTotalHours', booking.total_hours != null ? `${booking.total_hours} Hr(s)` : 'N/A');

    // Destinations
    setElText('viewDestinationFrom', booking.destinationFrom || 'N/A');
    setElText('viewDestinationTo', booking.destinationTo || 'N/A');

    // Payment
    setElText('viewCarAmount', booking.car_amount != null ? `₱${formatMoney(booking.car_amount)}` : 'N/A');
    setElText('viewDestinationAmount', booking.destination_amount != null ? `₱${formatMoney(booking.destination_amount)}` : '₱0.00');
    setElText('viewDiscountAmount', booking.discount_amount != null ? `-₱${formatMoney(booking.discount_amount)}` : '₱0.00');
    setElText('viewFinalAmount', booking.total_amount != null ? `₱${formatMoney(booking.total_amount)}` : 'N/A');

    // Timestamps
    setElText('viewCreatedAt', formatDate(booking.created_at));
    setElText('viewUpdatedAt', formatDate(booking.updated_at));
}

function openBookingModal() {
    const el = document.getElementById('viewBookingModal');
    if (!el) return showError('Unable to open booking modal');

    let modal = bootstrap.Modal.getInstance(el);
    if (!modal) modal = new bootstrap.Modal(el, { backdrop: 'static', keyboard: true });
    modal.show();
}

// ── Helpers ───────────────────────────────────────────────────────────
function setElText(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
}

function capitalize(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function formatMoney(amount) {
    return parseFloat(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    return new Date(dateStr).toLocaleString('en-US', {
        month: 'short',
        day: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
}

function getStatusClass(status) {
    const map = {
        pending: 'bg-warning text-dark',
        ongoing: 'bg-info text-dark',
        completed: 'bg-success',
        cancelled: 'bg-danger',
        available: 'bg-success',
        rented: 'bg-danger',
        maintenance: 'bg-warning text-dark',
    };
    return map[status] || 'bg-secondary';
}



function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: message,
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'OK'
    });
}