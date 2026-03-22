'use strict';

/* ─── Config ─────────────────────────────────────────────────────────────── */

var BK = {
    endpoint: '',
    carsUrl: '/client/car',
    baseUrl: '',
};

/* ─── Format ─────────────────────────────────────────────────────────────── */

var Format = {

    money: function(amount) {
        return Number(amount || 0).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    },

    date: function(value) {
        if (!value) return 'N/A';
        var d = new Date(value);
        if (isNaN(d.getTime())) return value;
        return d.toLocaleString('en-PH', {
            year: 'numeric',
            month: 'short',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true,
        });
    },

    cap: function(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    },
};

/* ─── StatusMap ──────────────────────────────────────────────────────────── */

var StatusMap = {
    pending: { cls: 'bk-badge-pending', icon: 'fa-clock', label: 'Pending', note: 'Awaiting admin confirmation' },
    approved: { cls: 'bk-badge-approved', icon: 'fa-circle-check', label: 'Approved', note: 'Your booking has been confirmed' },
    ongoing: { cls: 'bk-badge-ongoing', icon: 'fa-car', label: 'Ongoing', note: 'Your rental is currently active' },
    completed: { cls: 'bk-badge-completed', icon: 'fa-flag-checkered', label: 'Completed', note: 'Rental has ended' },
    cancelled: { cls: 'bk-badge-cancelled', icon: 'fa-ban', label: 'Cancelled', note: 'This booking was cancelled' },

    get: function(status) {
        var s = (status || '').toLowerCase();
        return this[s] || { cls: 'bk-badge-default', icon: 'fa-question', label: Format.cap(s) || 'Unknown', note: '' };
    },
};

/* ─── Render ─────────────────────────────────────────────────────────────── */

var Render = {

    loading: function(tbody) {
        var stateEl = document.getElementById('clientBookingsState');
        if (stateEl) stateEl.textContent = 'Loading your bookings…';
    },

    empty: function(tbody) {
        tbody.innerHTML =
            '<tr class="bk-state-row">' +
            '<td colspan="7">' +
            '<i class="fa-solid fa-calendar-xmark bk-state-icon"></i>' +
            '<p class="bk-state-title">No bookings yet</p>' +
            '<p class="bk-state-sub">You haven\'t made any bookings. Start by browsing our fleet.</p>' +
            '<a href="' + BK.carsUrl + '" class="bk-state-action">' +
            '<i class="fa-solid fa-car"></i> Browse Fleet' +
            '</a>' +
            '</td>' +
            '</tr>';
    },

    error: function(tbody) {
        tbody.innerHTML =
            '<tr class="bk-state-row">' +
            '<td colspan="7">' +
            '<i class="fa-solid fa-triangle-exclamation bk-state-icon" style="color:#dc2626;opacity:1"></i>' +
            '<p class="bk-state-title">Unable to load bookings</p>' +
            '<p class="bk-state-sub">Something went wrong. Please refresh the page to try again.</p>' +
            '</td>' +
            '</tr>';
    },

    rows: function(tbody, bookings) {
        var html = '';
        for (var i = 0; i < bookings.length; i++) {
            html += this._row(bookings[i]);
        }
        tbody.innerHTML = html;
    },

    _row: function(b) {
        var ref = b.id ? '#BK00' + b.id : '—';
        var car = b.car || {};
        var status = StatusMap.get(b.status);

        var carName = ((car.brand || '') + ' ' + (car.model || '')).trim() || 'N/A';
        var carHtml =
            '<div class="bk-car-name">' + carName + '</div>' +
            (car.plate_number ? '<div class="bk-car-plate">' + car.plate_number + '</div>' : '');

        var tripHtml =
            '<div class="bk-trip-from">' + (b.destination_from || 'N/A') + '</div>' +
            '<div class="bk-trip-to">to ' + (b.destination_to || 'N/A') + '</div>';

        var datesHtml =
            '<div class="bk-date-start">' + Format.date(b.rental_start_date) + '</div>' +
            '<div class="bk-date-sub">' +
            Format.date(b.rental_end_date) +
            (b.total_days != null ? ' &bull; ' + b.total_days + ' day(s)' : '') +
            '</div>';

        var badgeHtml =
            '<span class="bk-badge ' + status.cls + '">' +
            '<i class="fa-solid ' + status.icon + '"></i> ' + status.label +
            '</span>';

        var amountHtml = '<span class="bk-amount">&#8369;' + Format.money(b.final_amount || 0) + '</span>';

        var rowDataAttr = ' data-booking=\'' + JSON.stringify(b).replace(/'/g, '&#39;') + '\'';

        return '<tr>' +
            '<td><span class="bk-ref">' + ref + '</span></td>' +
            '<td>' + carHtml + '</td>' +
            '<td>' + tripHtml + '</td>' +
            '<td>' + datesHtml + '</td>' +
            '<td>' + badgeHtml + '</td>' +
            '<td class="text-right">' + amountHtml + '</td>' +
            '<td class="text-right"><button class="bk-view-btn bk-open-detail" type="button"' +
            ' data-booking-id="' + b.id + '"' +
            rowDataAttr +
            '><i class="fa-solid fa-eye"></i> Details</button></td>' +
            '</tr>';
    },
};

/* ─── Api ────────────────────────────────────────────────────────────────── */

var Api = {
    list: function() {
        return fetch(BK.endpoint, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
            .then(function(res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function(json) {
                if (!json.success) throw new Error(json.message || 'Failed to load bookings');
                return json.data || [];
            });
    },
};

/* ─── DetailModal ────────────────────────────────────────────────────────── */

var DetailModal = {

    init: function() {
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.bk-open-detail');
            if (!btn) return;

            var raw = btn.getAttribute('data-booking');
            if (!raw) return;

            var booking;
            try {
                booking = JSON.parse(raw.replace(/&#39;/g, "'"));
            } catch (err) {
                console.error('[ClientBookings] Failed to parse booking data', err);
                return;
            }

            DetailModal.open(booking);
        });
    },

    open: function(b) {
        this._populate(b);
        var el = document.getElementById('bookingDetailModal');
        if (!el) return;
        var m = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
        m.show();
    },

    _populate: function(b) {
        var car = b.car || {};
        var status = StatusMap.get(b.status);
        var carName = ((car.brand || '') + ' ' + (car.model || '')).trim() || 'N/A';

        /* ── Header ref ── */
        var refEl = document.getElementById('bdRef');
        if (refEl) refEl.textContent = b.id ? '#BK00' + b.id : '—';

        /* ── Status banner ── */
        var banner = document.getElementById('bdStatusBanner');
        if (banner) {
            var colorMap = {
                pending: { bg: 'rgba(217,119,6,0.08)', bdr: 'rgba(217,119,6,0.2)', fg: '#d97706' },
                approved: { bg: 'rgba(2,132,199,0.08)', bdr: 'rgba(2,132,199,0.2)', fg: '#0284c7' },
                ongoing: { bg: 'rgba(124,58,237,0.08)', bdr: 'rgba(124,58,237,0.2)', fg: '#7c3aed' },
                completed: { bg: 'rgba(5,150,105,0.08)', bdr: 'rgba(5,150,105,0.2)', fg: '#059669' },
                cancelled: { bg: 'rgba(220,38,38,0.08)', bdr: 'rgba(220,38,38,0.2)', fg: '#dc2626' },
            };
            var s = (b.status || '').toLowerCase();
            var color = colorMap[s] || { bg: '#f3f4f6', bdr: '#e5e7eb', fg: '#6b7280' };
            banner.style.background = color.bg;
            banner.style.borderColor = color.bdr;
            banner.style.color = color.fg;
        }

        var iconEl = document.getElementById('bdStatusIcon');
        if (iconEl) iconEl.className = 'fa-solid fa-' + status.icon + ' bk-status-icon';

        var labelEl = document.getElementById('bdStatusLabel');
        if (labelEl) labelEl.textContent = status.label;

        var noteEl = document.getElementById('bdStatusNote');
        if (noteEl) noteEl.textContent = status.note ? '— ' + status.note : '';

        /* ── Car image ── */
        var img = document.getElementById('bdCarImg');
        if (img) { img.src = car.image_path ? '/storage/' + car.image_path : '';
            img.alt = carName; }

        var nameEl = document.getElementById('bdCarName');
        if (nameEl) nameEl.textContent = carName;

        var priceEl = document.getElementById('bdCarPrice');
        if (priceEl) {
            var carAmt = parseFloat(b.car_amount || 0);
            var days = parseInt(b.total_days || 0, 10);
            if (days > 0 && carAmt > 0) {
                priceEl.innerHTML = '&#8369;' + Format.money(carAmt / days) + ' / day';
            } else {
                priceEl.textContent = '—';
            }
        }

        /* ── Trip ── */
        var fromEl = document.getElementById('bdFrom');
        if (fromEl) fromEl.textContent = b.destination_from || 'N/A';

        var toEl = document.getElementById('bdTo');
        if (toEl) toEl.textContent = b.destination_to || 'N/A';

        /* ── Dates ── */
        var startEl = document.getElementById('bdStartDate');
        if (startEl) startEl.textContent = Format.date(b.rental_start_date);

        var endEl = document.getElementById('bdEndDate');
        if (endEl) endEl.textContent = Format.date(b.rental_end_date);

        /* ── Duration pills ── */
        var durEl = document.getElementById('bdDuration');
        if (durEl) {
            var pills = '';
            if (b.total_days != null) {
                pills += '<span class="bk-duration-pill"><i class="fa-solid fa-calendar-days"></i> ' + b.total_days + ' Day(s)</span>';
            }
            if (b.total_hours != null) {
                pills += '<span class="bk-duration-pill" style="background:#374151"><i class="fa-solid fa-clock"></i> ' + b.total_hours + ' Hour(s)</span>';
            }
            durEl.innerHTML = pills || '—';
        }

        /* ── Cost summary ── */
        var costEl = document.getElementById('bdCostRows');
        if (costEl) {
            var totalDays = parseInt(b.total_days || 0, 10);
            var carAmount = parseFloat(b.car_amount || 0);
            var destFee = parseFloat(b.destination_amount || 0);
            var discount = parseFloat(b.discount_amount || 0);
            var finalAmount = parseFloat(b.final_amount || 0);

            // Derive price per day from car_amount if available, else fall back
            var pricePerDay = (totalDays > 0 && carAmount > 0) ? (carAmount / totalDays) : 0;

            var html = '';

            /* Rate per day */
            if (pricePerDay > 0) {
                html +=
                    '<div class="bk-cost-row">' +
                    '<span class="bk-cost-row-label">' +
                    '<i class="fa-solid fa-car" style="margin-right:5px;font-size:.75em;opacity:.6"></i>' +
                    'Rate per day' +
                    '</span>' +
                    '<span class="bk-cost-row-value">&#8369;' + Format.money(pricePerDay) + '</span>' +
                    '</div>';
            }

            /* Duration */
            if (totalDays > 0) {
                html +=
                    '<div class="bk-cost-row">' +
                    '<span class="bk-cost-row-label">' +
                    '<i class="fa-solid fa-calendar-days" style="margin-right:5px;font-size:.75em;opacity:.6"></i>' +
                    'Duration' +
                    '</span>' +
                    '<span class="bk-cost-row-value">' + totalDays + ' day(s)</span>' +
                    '</div>';
            }

            /* Car rental subtotal */
            if (carAmount > 0) {
                html +=
                    '<div class="bk-cost-row">' +
                    '<span class="bk-cost-row-label">' +
                    '<i class="fa-solid fa-receipt" style="margin-right:5px;font-size:.75em;opacity:.6"></i>' +
                    'Rental subtotal' +
                    '</span>' +
                    '<span class="bk-cost-row-value">&#8369;' + Format.money(carAmount) + '</span>' +
                    '</div>';
            }

            /* Destination fee — only show when > 0 */
            if (destFee > 0) {
                html +=
                    '<div class="bk-cost-row">' +
                    '<span class="bk-cost-row-label">' +
                    '<i class="fa-solid fa-location-dot" style="margin-right:5px;font-size:.75em;opacity:.6"></i>' +
                    'Destination fee' +
                    '</span>' +
                    '<span class="bk-cost-row-value">+&#8369;' + Format.money(destFee) + '</span>' +
                    '</div>';
            }

            /* Discount — only show when > 0, styled green with minus sign */
            if (discount > 0) {
                html +=
                    '<div class="bk-cost-row">' +
                    '<span class="bk-cost-row-label">' +
                    '<i class="fa-solid fa-tag" style="margin-right:5px;font-size:.75em;opacity:.6"></i>' +
                    'Discount' +
                    '</span>' +
                    '<span class="bk-cost-row-value bk-cost-discount">&#8722;&#8369;' + Format.money(discount) + '</span>' +
                    '</div>';
            }

            /* Total — always shown */
            html +=
                '<div class="bk-cost-row bk-cost-total">' +
                '<span class="bk-cost-row-label">Total Amount</span>' +
                '<span class="bk-cost-row-value">&#8369;' + Format.money(finalAmount) + '</span>' +
                '</div>';

            costEl.innerHTML = html;
        }
    },
};

/* ─── Init ───────────────────────────────────────────────────────────────── */

document.addEventListener('DOMContentLoaded', function() {
    var root = document.getElementById('clientBookingsRoot');
    if (!root) {
        console.error('[ClientBookings] #clientBookingsRoot not found');
        return;
    }

    BK.endpoint = root.getAttribute('data-endpoint') || '';
    BK.carsUrl = root.getAttribute('data-cars-url') || '/client/car';
    BK.baseUrl = root.getAttribute('data-base-url') || '';

    if (!BK.endpoint) {
        console.error('[ClientBookings] data-endpoint not set');
        return;
    }

    var stateEl = document.getElementById('clientBookingsState');
    var tbody = document.getElementById('clientBookingsBody');

    if (!tbody) {
        console.error('[ClientBookings] #clientBookingsBody not found');
        return;
    }

    DetailModal.init();

    if (stateEl) stateEl.textContent = 'Loading your bookings…';

    Api.list()
        .then(function(bookings) {
            if (stateEl) {
                stateEl.textContent = bookings.length > 0 ?
                    bookings.length + ' booking(s) found' : '';
            }
            if (bookings.length === 0) {
                Render.empty(tbody);
            } else {
                Render.rows(tbody, bookings);
            }
        })
        .catch(function(err) {
            console.error('[ClientBookings] Failed to load', err);
            if (stateEl) stateEl.textContent = 'Unable to load bookings.';
            Render.error(tbody);
        });
});