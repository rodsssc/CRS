// ============================================================================
// VIEW CAR SCRIPT
// Dependencies: Bootstrap 5, SweetAlert2
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {

    // ── GUARD ───────────────────────────────────────────────────────────────
    const viewCarModal = document.getElementById('viewCarModal');
    if (!viewCarModal) {
        console.error('viewCarModal not found');
        return;
    }

    // ── OPEN VIEW MODAL ON CLICK ────────────────────────────────────────────
    document.addEventListener('click', function(e) {
        const button = e.target.closest('.btn-show-action');
        if (!button) return;

        const carId = button.getAttribute('data-car-id');
        if (carId) viewCar(carId);
    });

});


// ── FETCH CAR DATA ──────────────────────────────────────────────────────────
async function viewCar(carId) {
    try {
        // ====================================================================
        // STEP 1: Clear Old Data & Show Loader
        // ====================================================================
        clearCarModal();

        Swal.fire({
            title: 'Loading...',
            text: 'Fetching car details',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
            showConfirmButton: false
        });

        // ====================================================================
        // STEP 2: Fetch Data (with slight delay for clean transition)
        // ====================================================================
        await new Promise(resolve => setTimeout(resolve, 300));

        const response = await fetch(`/client/car/${carId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        });

        const data = await response.json();

        // ====================================================================
        // STEP 3: Handle Response
        // ====================================================================
        if (response.ok && data.cars) {
            populateCarModal(data.cars);

            Swal.fire({
                icon: 'success',
                title: 'Done!',
                timer: 800,
                showConfirmButton: false,
                allowOutsideClick: false,
                didClose: () => openViewModal()
            });

        } else {
            Swal.close();
            showCarError(data.message || 'Failed to load car data.');
        }

    } catch (error) {
        Swal.close();
        console.error('viewCar error:', error);
        showCarError('An error occurred while loading car data.');
    }
}


// ── CLEAR MODAL DATA ────────────────────────────────────────────────────────
function clearCarModal() {
    const carImage = document.getElementById('viewCarImage');
    if (carImage) carImage.src = 'https://via.placeholder.com/400x300?text=Loading...';

    setElText('viewCarTitle', '—');
    setElText('viewCarBrand', '—');
    setElText('viewCarModel', '—');
    setElText('viewCarYear', '—');
    setElText('viewCarColor', '—');
    setElText('viewCarCapacity', '—');
    setElText('viewCarTransmission', '—');
    setElText('viewCarFuel', '—');

    const priceEl = document.getElementById('viewCarPrice');
    if (priceEl) priceEl.innerHTML = '—';

    const statusBadge = document.getElementById('viewCarStatus');
    if (statusBadge) {
        statusBadge.textContent = '—';
        statusBadge.className = 'badge bg-secondary';
    }
}


// ── POPULATE MODAL ──────────────────────────────────────────────────────────
function populateCarModal(car) {

    // Image
    const carImage = document.getElementById('viewCarImage');
    if (carImage) {
        carImage.src = car.image_path ?
            `/storage/${car.image_path}` :
            'https://via.placeholder.com/400x300?text=No+Image';
        carImage.alt = `${car.brand || ''} ${car.model || ''}`.trim();
    }

    // Title
    setElText('viewCarTitle', `${car.brand || ''} ${car.model || ''} ${car.year || ''}`.trim());

    // Price
    const priceEl = document.getElementById('viewCarPrice');
    if (priceEl) {
        const price = car.rental_price_per_day ?
            parseFloat(car.rental_price_per_day).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) :
            '0.00';
        priceEl.innerHTML = `₱${price} <small class="text-muted">/ day</small>`;
    }

    // Specs
    setElText('viewCarBrand', car.brand || 'N/A');
    setElText('viewCarModel', car.model || 'N/A');
    setElText('viewCarYear', car.year || 'N/A');
    setElText('viewCarColor', capitalize(car.color) || 'N/A');
    setElText('viewCarCapacity', car.capacity ? `${car.capacity} Seater` : 'N/A');
    setElText('viewCarTransmission', capitalize(car.transmission_type) || 'N/A');
    setElText('viewCarFuel', capitalize(car.fuel_type) || 'N/A');

    // Status Badge
    const statusBadge = document.getElementById('viewCarStatus');
    if (statusBadge) {
        const status = (car.status || '').toLowerCase();
        const statusMap = {
            available: 'bg-success',
            rented: 'bg-danger',
            maintenance: 'bg-warning text-dark',
        };
        statusBadge.textContent = capitalize(status);
        statusBadge.className = `badge ${statusMap[status] || 'bg-secondary'}`;
    }

    // Book Now button
    const bookNowBtn = document.getElementById('bookNowBtn');
    if (bookNowBtn) bookNowBtn.dataset.carId = car.id;
}


// ── OPEN MODAL ──────────────────────────────────────────────────────────────
function openViewModal() {
    const el = document.getElementById('viewCarModal');
    if (!el) return;

    let modal = bootstrap.Modal.getInstance(el);
    if (!modal) modal = new bootstrap.Modal(el, { backdrop: 'static' });
    modal.show();
}


// ── HELPERS ─────────────────────────────────────────────────────────────────
function setElText(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value || 'N/A';
}

function capitalize(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function showCarError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: message,
        confirmButtonColor: '#3085d6'
    });
}