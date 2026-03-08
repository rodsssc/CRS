document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('clientBookingsRoot');
    const stateEl = document.getElementById('clientBookingsState');
    const tbody = document.getElementById('clientBookingsBody');

    if (!root || !stateEl || !tbody) {
        console.error('[ClientBookings] Required elements not found');
        return;
    }

    const endpoint = root.dataset.endpoint;
    if (!endpoint) {
        console.error('[ClientBookings] Endpoint not provided');
        return;
    }

    loadBookings(endpoint, { stateEl, tbody });
});

async function loadBookings(endpoint, { stateEl, tbody }) {
    setState(stateEl, 'Loading your bookings…');
    setBodyLoading(tbody);

    try {
        const response = await fetch(endpoint, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const payload = await response.json();
        if (!payload.success) {
            throw new Error(payload.message || 'Failed to load bookings');
        }

        const bookings = payload.data || [];
        if (bookings.length === 0) {
            setState(stateEl, 'You have no bookings yet.');
            setBodyEmpty(tbody);
            return;
        }

        setState(stateEl, `${bookings.length} booking(s) found.`);
        renderBookings(tbody, bookings);
    } catch (e) {
        console.error('[ClientBookings] Failed to load bookings', e);
        setState(stateEl, 'Unable to load bookings. Please try again later.');
        setBodyError(tbody);
    }
}

function setState(el, message) {
    el.textContent = message;
}

function setBodyLoading(tbody) {
    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center text-muted py-4">
                Loading bookings…
            </td>
        </tr>
    `;
}

function setBodyEmpty(tbody) {
    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center text-muted py-4">
                You have no bookings yet.
            </td>
        </tr>
    `;
}

function setBodyError(tbody) {
    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center text-danger py-4">
                Unable to load bookings right now.
            </td>
        </tr>
    `;
}

function renderBookings(tbody, bookings) {
    const rows = bookings.map(buildBookingRow).join('');
    tbody.innerHTML = rows;
}

function buildBookingRow(booking) {
    const ref = booking.id ? `#BK-${booking.id}` : '—';
    const car = booking.car || {};
    const carLabel = buildCarLabel(car);
    const trip = buildTripLabel(booking);
    const dates = buildDatesLabel(booking);
    const statusBadge = buildStatusBadge(booking.status);
    const total = formatMoney(booking.final_amount || 0);

    return `
        <tr>
            <td>${ref}</td>
            <td>${carLabel}</td>
            <td>${trip}</td>
            <td>${dates}</td>
            <td>${statusBadge}</td>
            <td class="text-end">₱${total}</td>
        </tr>
    `;
}

function buildCarLabel(car) {
    const name = `${car.brand || ''} ${car.model || ''}`.trim() || 'N/A';
    const plate = car.plate_number || '';
    if (!plate) {
        return name;
    }
    return `
        <div class="fw-semibold">${name}</div>
        <div class="text-muted small">${plate}</div>
    `;
}

function buildTripLabel(booking) {
    const from = booking.destination_from || 'N/A';
    const to = booking.destination_to || 'N/A';
    return `
        <div>${from}</div>
        <div class="text-muted small">to ${to}</div>
    `;
}

function buildDatesLabel(booking) {
    const start = formatDate(booking.rental_start_date);
    const end = formatDate(booking.rental_end_date);
    const days = booking.total_days != null ? `${booking.total_days} day(s)` : null;

    return `
        <div>${start}</div>
        <div class="text-muted small">${end}${days ? ` • ${days}` : ''}</div>
    `;
}

function buildStatusBadge(status) {
    const s = (status || '').toLowerCase();
    const map = {
        pending: { label: 'Pending', className: 'badge bg-warning text-dark' },
        ongoing: { label: 'Ongoing', className: 'badge bg-info text-dark' },
        completed: { label: 'Completed', className: 'badge bg-success' },
        cancelled: { label: 'Cancelled', className: 'badge bg-danger' },
    };

    const conf = map[s] || { label: s || 'Unknown', className: 'badge bg-secondary' };
    return `<span class="${conf.className}">${conf.label}</span>`;
}

function formatMoney(amount) {
    return Number(amount || 0).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function formatDate(value) {
    if (!value) return 'N/A';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return value;
    }
    return date.toLocaleString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true,
    });
}

