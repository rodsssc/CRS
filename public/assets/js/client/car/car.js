/**
 * ════════════════════════════════════════════════════════════════
 * BjCarRental · Client Car Listing — car.js
 * ════════════════════════════════════════════════════════════════
 *
 * Architecture
 * ────────────
 *  Config      – URLs injected by Blade via window.CAR_CONFIG
 *  State       – single object holds current filter + page state
 *  Api         – fetch wrappers (data list, single car, book)
 *  Render      – builds card HTML, skeleton, empty state
 *  Pagination  – builds page-button row from meta object
 *  Filter      – reads filter bar values, debounces search input
 *  ViewModal   – loads + populates car detail modal
 *  BookModal   – captures car ID, calculates duration, submits booking
 *  Utils       – shared helpers (format, capitalize, CSRF, close modal)
 *  Init        – DOMContentLoaded bootstrap
 */

'use strict';

/* ─── Config ─────────────────────────────────────────────────────────────── */

window.CAR_CONFIG = window.CAR_CONFIG || {
    dataUrl: '/client/car/data',
    showUrl: '/client/car/__ID__',
    bookingUrl: '/client/bookings',
    clientId: 0,
};

var CFG = window.CAR_CONFIG;

/* ─── State ──────────────────────────────────────────────────────────────── */

var State = {
    search: '',
    status: '',
    capacity: '',
    sort: 'name_asc',
    page: 1,
    perPage: 12,
    loading: false,

    // car price cache for cost estimate in booking modal
    currentCarPrice: 0,
};

/* ─── Utils ──────────────────────────────────────────────────────────────── */

var Utils = {

    /** Get CSRF token from meta tag or cookie */
    csrf: function() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) return meta.getAttribute('content');
        var match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
        return match ? decodeURIComponent(match[1]) : '';
    },

    /** Capitalize first letter */
    capitalize: function(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    },

    /** Format number as Philippine peso */
    peso: function(value) {
        return parseFloat(value || 0).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    },

    /** Set text content of element by ID */
    setText: function(id, value) {
        var el = document.getElementById(id);
        if (el) el.textContent = value || 'N/A';
    },

    /** Simple debounce */
    debounce: function(fn, delay) {
        var timer;
        return function() {
            var args = arguments;
            var ctx = this;
            clearTimeout(timer);
            timer = setTimeout(function() { fn.apply(ctx, args); }, delay);
        };
    },

    /** Close a Bootstrap modal cleanly */
    closeModal: function(id) {
        var el = document.getElementById(id);
        if (!el) return;

        var instance = bootstrap.Modal.getInstance(el);
        if (instance) {
            instance.hide();
        } else {
            var m = new bootstrap.Modal(el);
            m.hide();
        }

        // clean up Bootstrap remnants
        document.querySelectorAll('.modal-backdrop').forEach(function(b) { b.remove(); });
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
    },
};

/* ─── Api ────────────────────────────────────────────────────────────────── */

var Api = {

    /**
     * Fetch paginated + filtered car list.
     * @returns {Promise}
     */
    list: function() {
        var url = new URL(CFG.dataUrl, location.origin);
        if (State.search) url.searchParams.set('search', State.search);
        if (State.status) url.searchParams.set('status', State.status);
        if (State.capacity) url.searchParams.set('capacity', State.capacity);
        if (State.sort) url.searchParams.set('sort', State.sort);
        url.searchParams.set('page', State.page);
        url.searchParams.set('per_page', State.perPage);

        return fetch(url)
            .then(function(r) { return r.json(); })
            .then(function(json) {
                if (!json.success) throw new Error('Car list API error');
                return json;
            });
    },

    /**
     * Fetch single car by ID.
     * @param {number|string} id
     * @returns {Promise}
     */
    show: function(id) {
        var url = CFG.showUrl.replace('__ID__', id);
        return fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': Utils.csrf(),
                },
            })
            .then(function(r) { return r.json(); });
    },

    /**
     * Submit a new booking.
     * @param {FormData} formData
     * @returns {Promise}
     */
    book: function(formData) {
        return fetch(CFG.bookingUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': Utils.csrf(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            })
            .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); });
    },
};

/* ─── Render ─────────────────────────────────────────────────────────────── */

var Render = {

    STATUS: {
        available: { cls: 'cr-badge-av', icon: 'fa-circle-check', label: 'Available' },
        rented: { cls: 'cr-badge-re', icon: 'fa-lock', label: 'Rented' },
        maintenance: { cls: 'cr-badge-mn', icon: 'fa-wrench', label: 'Maintenance' },
    },

    /**
     * Build one car card HTML string.
     * @param {object} car
     * @param {number} index  used to give first 4 cards eager loading
     * @returns {string}
     */
    card: function(car, index) {
        var badge = this.STATUS[car.status] || this.STATUS.maintenance;
        var imgSrc = car.image_path ? '/storage/' + car.image_path : null;
        var isAvail = car.status === 'available';

        // First 4 cards load eagerly (above the fold); rest are lazy
        var loadAttr = index < 4 ? 'eager' : 'lazy';
        var prioAttr = index === 0 ? ' fetchpriority="high"' : '';

        var imgHtml = imgSrc ?
            '<img class="cr-card-img" src="' + imgSrc + '" alt="' + car.brand + ' ' + car.model + '" loading="' + loadAttr + '" decoding="async"' + prioAttr + '>' :
            '<div class="cr-card-no-img"><i class="fa-solid fa-car"></i><span>' + car.brand + ' ' + car.model + '</span></div>';

        var bookDisabled = isAvail ? '' : ' disabled';

        return '<div class="cr-card">' +
            '<div class="cr-card-img-wrap">' +
            imgHtml +
            '<span class="cr-card-badge ' + badge.cls + '">' +
            '<i class="fa-solid ' + badge.icon + '"></i> ' + badge.label +
            '</span>' +
            '</div>' +
            '<div class="cr-card-body">' +
            '<div class="cr-card-name">' + car.brand + ' ' + car.model + '</div>' +
            '<div class="cr-card-meta">' +
            '<span><i class="fa-solid fa-users"></i> ' + car.capacity + ' Seater</span>' +
            '<span><i class="fa-solid fa-gears"></i> ' + Utils.capitalize(car.transmission_type || '') + '</span>' +
            '</div>' +
            '<div class="cr-card-price">&#8369;' + Utils.peso(car.rental_price_per_day) + '<small> /day</small></div>' +
            '<div class="cr-card-actions">' +
            '<button class="cr-btn-view btn-show-action" data-car-id="' + car.id + '" type="button">' +
            '<i class="fa-solid fa-eye"></i> View' +
            '</button>' +
            '<button class="cr-btn-book" data-car-id="' + car.id + '" data-car-name="' + car.brand + ' ' + car.model + '" data-car-price="' + car.rental_price_per_day + '" data-bs-toggle="modal" data-bs-target="#bookingModal" type="button"' + bookDisabled + '>' +
            '<i class="fa-solid fa-' + (isAvail ? 'calendar-check' : 'ban') + '"></i> ' +
            (isAvail ? 'Book' : 'Unavailable') +
            '</button>' +
            '</div>' +
            '</div>' +
            '</div>';
    },

    /** Build skeleton HTML for n cards */
    skeletons: function(n) {
        var one = '<div class="cr-skel">' +
            '<div class="cr-skel-img"></div>' +
            '<div class="cr-skel-body">' +
            '<div class="cr-skel-line cr-sl-w"></div>' +
            '<div class="cr-skel-line cr-sl-m"></div>' +
            '<div class="cr-skel-line cr-sl-n"></div>' +
            '<div class="cr-skel-line cr-sl-btn"></div>' +
            '</div>' +
            '</div>';
        var html = '';
        for (var i = 0; i < n; i++) { html += one; }
        return html;
    },

    /** Empty state HTML */
    empty: function() {
        return '<div class="cr-empty">' +
            '<i class="fa-solid fa-car-burst cr-empty-icon"></i>' +
            '<h4>No vehicles found</h4>' +
            '<p>Try adjusting your filters or search term.</p>' +
            '</div>';
    },

    /**
     * Render cars into the grid.
     * @param {Array}  cars
     * @param {object} meta  pagination meta from API
     */
    grid: function(cars, meta) {
        var grid = document.getElementById('crGrid');
        if (!grid) return;

        if (!cars || cars.length === 0) {
            grid.innerHTML = this.empty();
        } else {
            var html = '';
            for (var i = 0; i < cars.length; i++) {
                html += this.card(cars[i], i);
            }
            grid.innerHTML = html;
        }

        // Update result count badge
        var countEl = document.getElementById('crTotalCount');
        if (countEl) countEl.textContent = meta ? meta.total : 0;
    },
};

/* ─── Pagination ─────────────────────────────────────────────────────────── */

var Pagination = {

    /**
     * Build and inject pagination controls.
     * @param {object} meta  { current_page, last_page, from, to, total }
     */
    render: function(meta) {
        var container = document.getElementById('crPagination');
        if (!container) return;

        if (!meta || meta.last_page <= 1) {
            container.innerHTML = '';
            return;
        }

        var current = meta.current_page;
        var last = meta.last_page;
        var html = '';

        // Prev button
        html += '<button class="cr-page-btn" data-page="' + (current - 1) + '"' +
            (current <= 1 ? ' disabled' : '') + '>' +
            '<i class="fa-solid fa-chevron-left"></i></button>';

        // Page number buttons with ellipsis
        var pages = this._pages(current, last);
        for (var i = 0; i < pages.length; i++) {
            if (pages[i] === '...') {
                html += '<span class="cr-page-ellipsis">…</span>';
            } else {
                html += '<button class="cr-page-btn' + (pages[i] === current ? ' active' : '') + '" data-page="' + pages[i] + '">' +
                    pages[i] + '</button>';
            }
        }

        // Next button
        html += '<button class="cr-page-btn" data-page="' + (current + 1) + '"' +
            (current >= last ? ' disabled' : '') + '>' +
            '<i class="fa-solid fa-chevron-right"></i></button>';

        container.innerHTML = html;

        // Wire up clicks
        var buttons = container.querySelectorAll('.cr-page-btn:not(:disabled)');
        for (var j = 0; j < buttons.length; j++) {
            buttons[j].addEventListener('click', function() {
                var page = parseInt(this.getAttribute('data-page'), 10);
                if (page && page !== State.page) {
                    State.page = page;
                    Fleet.load();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
        }
    },

    /**
     * Generate page number array with ellipsis.
     * @private
     */
    _pages: function(current, last) {
        var pages = [];
        var delta = 2;

        for (var i = 1; i <= last; i++) {
            if (
                i === 1 ||
                i === last ||
                (i >= current - delta && i <= current + delta)
            ) {
                pages.push(i);
            } else if (
                pages[pages.length - 1] !== '...'
            ) {
                pages.push('...');
            }
        }

        return pages;
    },
};

/* ─── Fleet (load + filter) ─────────────────────────────────────────────── */

var Fleet = {

    /** Fetch cars with current State and render. */
    load: function() {
        if (State.loading) return;
        State.loading = true;

        var grid = document.getElementById('crGrid');
        if (grid) grid.innerHTML = Render.skeletons(State.perPage);

        var pagContainer = document.getElementById('crPagination');
        if (pagContainer) pagContainer.innerHTML = '';

        Api.list()
            .then(function(json) {
                Render.grid(json.data, json.meta);
                Pagination.render(json.meta);
            })
            .catch(function(err) {
                console.error('[BjCarRental] Fleet.load failed', err);
                var grid2 = document.getElementById('crGrid');
                if (grid2) grid2.innerHTML = Render.empty();
            })
            .then(function() {
                State.loading = false;
            });
    },

    /** Reset all filters and reload page 1. */
    reset: function() {
        State.search = '';
        State.status = '';
        State.capacity = '';
        State.sort = 'name_asc';
        State.page = 1;

        var searchEl = document.getElementById('crSearch');
        var statusEl = document.getElementById('crStatus');
        var sortEl = document.getElementById('crSort');

        if (searchEl) searchEl.value = '';
        if (statusEl) statusEl.value = '';
        if (sortEl) sortEl.value = 'name_asc';

        // Reset capacity chips to "All"
        var allChip = document.getElementById('crCap0');
        if (allChip) allChip.checked = true;

        // Hide clear button
        var clearBtn = document.getElementById('crSearchClear');
        if (clearBtn) clearBtn.classList.remove('visible');

        this.load();
    },
};

/* ─── Filter (wire up all filter controls) ──────────────────────────────── */

var Filter = {

    init: function() {
        var self = this;

        // Search — debounced
        var searchEl = document.getElementById('crSearch');
        if (searchEl) {
            searchEl.addEventListener('input', Utils.debounce(function() {
                State.search = searchEl.value.trim();
                State.page = 1;

                var clearBtn = document.getElementById('crSearchClear');
                if (clearBtn) {
                    if (State.search) {
                        clearBtn.classList.add('visible');
                    } else {
                        clearBtn.classList.remove('visible');
                    }
                }

                Fleet.load();
            }, 380));
        }

        // Clear search button
        var clearBtn = document.getElementById('crSearchClear');
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                if (searchEl) searchEl.value = '';
                State.search = '';
                State.page = 1;
                clearBtn.classList.remove('visible');
                Fleet.load();
            });
        }

        // Status select
        var statusEl = document.getElementById('crStatus');
        if (statusEl) {
            statusEl.addEventListener('change', function() {
                State.status = statusEl.value;
                State.page = 1;
                Fleet.load();
            });
        }

        // Sort select
        var sortEl = document.getElementById('crSort');
        if (sortEl) {
            sortEl.addEventListener('change', function() {
                State.sort = sortEl.value;
                State.page = 1;
                Fleet.load();
            });
        }

        // Capacity chips
        var chips = document.querySelectorAll('.cr-chip-input[name="crCapacity"]');
        for (var i = 0; i < chips.length; i++) {
            chips[i].addEventListener('change', function() {
                State.capacity = this.value;
                State.page = 1;
                Fleet.load();
            });
        }

        // Reset button
        var resetBtn = document.getElementById('crReset');
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                Fleet.reset();
            });
        }
    },
};

/* ─── ViewModal ──────────────────────────────────────────────────────────── */

var ViewModal = {

    init: function() {
        // Delegate — works even after grid is re-rendered
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.btn-show-action');
            if (!btn) return;
            var carId = btn.getAttribute('data-car-id');
            if (carId) ViewModal.open(carId);
        });
    },

    open: function(carId) {
        this._clear();

        Swal.fire({
            title: 'Loading…',
            text: 'Fetching car details',
            allowOutsideClick: false,
            didOpen: function() { Swal.showLoading(); },
            showConfirmButton: false,
        });

        Api.show(carId)
            .then(function(data) {
                if (data && data.cars) {
                    ViewModal._populate(data.cars);

                    Swal.fire({
                        icon: 'success',
                        title: 'Done!',
                        timer: 600,
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        didClose: function() { ViewModal._show(); },
                    });
                } else {
                    Swal.close();
                    ViewModal._error(data.message || 'Failed to load car details.');
                }
            })
            .catch(function(err) {
                Swal.close();
                console.error('[BjCarRental] ViewModal.open error', err);
                ViewModal._error('An error occurred while loading car data.');
            });
    },

    _clear: function() {
        var img = document.getElementById('viewCarImage');
        if (img) img.src = '';

        var fields = [
            'viewCarTitle', 'viewCarBrand', 'viewCarModel',
            'viewCarYear', 'viewCarColor', 'viewCarCapacity',
            'viewCarTransmission', 'viewCarFuel',
        ];
        for (var i = 0; i < fields.length; i++) {
            Utils.setText(fields[i], '—');
        }

        var priceEl = document.getElementById('viewCarPrice');
        if (priceEl) priceEl.textContent = '—';

        var badge = document.getElementById('viewCarStatusBadge');
        if (badge) { badge.textContent = '—';
            badge.className = 'cr-modal-status-badge'; }
    },

    _populate: function(car) {
        var img = document.getElementById('viewCarImage');
        if (img) {
            img.src = car.image_path ? '/storage/' + car.image_path : '';
            img.alt = (car.brand || '') + ' ' + (car.model || '');
        }

        Utils.setText('viewCarTitle', (car.brand || '') + ' ' + (car.model || '') + (car.year ? ' ' + car.year : ''));
        Utils.setText('viewCarBrand', car.brand);
        Utils.setText('viewCarModel', car.model);
        Utils.setText('viewCarYear', car.year);
        Utils.setText('viewCarColor', Utils.capitalize(car.color));
        Utils.setText('viewCarCapacity', car.capacity ? car.capacity + ' Seater' : 'N/A');
        Utils.setText('viewCarTransmission', Utils.capitalize(car.transmission_type));
        Utils.setText('viewCarFuel', Utils.capitalize(car.fuel_type));

        var priceEl = document.getElementById('viewCarPrice');
        if (priceEl) {
            priceEl.innerHTML = car.rental_price_per_day ?
                '&#8369;' + Utils.peso(car.rental_price_per_day) + ' <small style="font-size:.75rem;font-weight:400;color:#6b7280">/ day</small>' :
                '—';
        }

        // Status badge
        var statusBadge = document.getElementById('viewCarStatusBadge');
        if (statusBadge) {
            var status = (car.status || '').toLowerCase();
            var statusMap = {
                available: 'background:rgba(5,150,105,0.12);color:#059669',
                rented: 'background:rgba(220,38,38,0.1);color:#dc2626',
                maintenance: 'background:rgba(217,119,6,0.1);color:#d97706',
            };
            statusBadge.textContent = Utils.capitalize(status);
            statusBadge.setAttribute('style', statusMap[status] || 'background:#f3f4f6;color:#6b7280');
        }

        // Wire Book Now button in footer to carry car ID
        var bookNowBtn = document.getElementById('bookNowBtn');
        if (bookNowBtn) {
            bookNowBtn.setAttribute('data-car-id', car.id);
            bookNowBtn.setAttribute('data-car-name', (car.brand || '') + ' ' + (car.model || ''));
            bookNowBtn.setAttribute('data-car-price', car.rental_price_per_day || 0);
            bookNowBtn.disabled = car.status !== 'available';
        }
    },

    _show: function() {
        var el = document.getElementById('viewCarModal');
        if (!el) return;
        var m = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el, { backdrop: 'static' });
        m.show();
    },

    _error: function(msg) {
        Swal.fire({ icon: 'error', title: 'Oops…', text: msg, confirmButtonColor: '#111827' });
    },
};

/* ─── BookModal ──────────────────────────────────────────────────────────── */

var BookModal = {

    init: function() {
        var modal = document.getElementById('bookingModal');
        if (!modal) return;

        // ── Capture car ID when booking modal opens ──────────────────────
        modal.addEventListener('show.bs.modal', function(event) {
            var trigger = event.relatedTarget;
            if (!trigger) return;

            var carId = trigger.getAttribute('data-car-id') || '';
            var carName = trigger.getAttribute('data-car-name') || '—';
            var carPrice = trigger.getAttribute('data-car-price') || 0;

            var carIdInput = document.getElementById('carId');
            if (carIdInput) carIdInput.value = carId;

            State.currentCarPrice = parseFloat(carPrice) || 0;

            var banner = document.getElementById('crBookingCarBanner');
            var nameEl = document.getElementById('crBookingCarName');
            if (banner) banner.style.display = 'flex';
            if (nameEl) nameEl.textContent = carName;
        });

        // ── Reset form when modal closes ─────────────────────────────────
        modal.addEventListener('hidden.bs.modal', function() {
            BookModal._resetForm();
        });

        // ── Date change → recalculate duration ───────────────────────────
        var startEl = document.getElementById('rental_start_date');
        var endEl = document.getElementById('rental_end_date');

        if (startEl) startEl.addEventListener('change', function() { BookModal._calcDuration(); });
        if (endEl) endEl.addEventListener('change', function() { BookModal._calcDuration(); });

        // ── Submit button ─────────────────────────────────────────────────
        var saveBtn = document.getElementById('saveBookingBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', function(e) {
                e.preventDefault();
                BookModal._submit();
            });
        }
    },

    /** Calculate and display rental duration + estimated cost. */
    _calcDuration: function() {
        var startVal = document.getElementById('rental_start_date').value;
        var endVal = document.getElementById('rental_end_date').value;

        if (!startVal || !endVal) return;

        var start = new Date(startVal);
        var end = new Date(endVal);
        var diffMs = end - start;

        if (diffMs <= 0) {
            document.getElementById('crDurationSummary').style.display = 'none';
            return;
        }

        var days = Math.ceil(diffMs / (1000 * 60 * 60 * 24));
        var hours = Math.floor(diffMs / (1000 * 60 * 60));
        var cost = (days * State.currentCarPrice);

        document.getElementById('total_days').value = days;
        document.getElementById('total_hours').value = hours;

        Utils.setText('total_days_display', days);
        Utils.setText('total_hours_display', hours);
        Utils.setText('total_cost_display', '&#8369;' + Utils.peso(cost));

        // Use innerHTML for peso sign
        var costEl = document.getElementById('total_cost_display');
        if (costEl) costEl.innerHTML = '&#8369;' + Utils.peso(cost);

        document.getElementById('crDurationSummary').style.display = 'flex';
    },

    /** Validate and submit the booking form. */
    _submit: function() {
        this._calcDuration();

        var data = {
            carId: document.getElementById('carId').value,
            client_id: document.getElementById('client_id').value,
            destinationFrom: document.getElementById('destinationFrom').value.trim(),
            destinationTo: document.getElementById('destinationTo').value.trim(),
            rental_start_date: document.getElementById('rental_start_date').value,
            rental_end_date: document.getElementById('rental_end_date').value,
            total_days: document.getElementById('total_days').value,
            total_hours: document.getElementById('total_hours').value,
        };

        if (!this._validate(data)) return;

        // Build FormData
        var fd = new FormData();
        var keys = Object.keys(data);
        for (var i = 0; i < keys.length; i++) {
            fd.append(keys[i], data[keys[i]]);
        }

        Utils.closeModal('bookingModal');

        setTimeout(function() {
            Swal.fire({
                title: 'Submitting booking…',
                allowOutsideClick: false,
                didOpen: function() { Swal.showLoading(); },
                showConfirmButton: false,
            });

            Api.book(fd)
                .then(function(result) {
                    if (result.ok && result.data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Booking Submitted!',
                            text: result.data.message || 'Waiting for confirmation…',
                            toast: true,
                            position: 'top-end',
                            timer: 2000,
                            showConfirmButton: false,
                        }).then(function() {
                            window.location.replace('/client/bookings');
                        });
                    } else {
                        if (result.data.errors) {
                            BookModal._showErrors(result.data.errors);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: result.data.message || 'Failed to submit booking.',
                                confirmButtonColor: '#111827',
                            });
                        }
                    }
                })
                .catch(function(err) {
                    Swal.close();
                    console.error('[BjCarRental] BookModal._submit error', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Server Error',
                        text: 'Something went wrong. Please try again.',
                        confirmButtonColor: '#111827',
                    });
                });
        }, 300);
    },

    _validate: function(data) {
        var required = [
            'carId', 'client_id', 'destinationFrom',
            'destinationTo', 'rental_start_date', 'rental_end_date',
        ];

        for (var i = 0; i < required.length; i++) {
            if (!data[required[i]]) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please fill in all required fields.',
                    confirmButtonColor: '#111827',
                });
                return false;
            }
        }

        var start = new Date(data.rental_start_date);
        var end = new Date(data.rental_end_date);

        if (end <= start) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Dates',
                text: 'End date must be after start date.',
                confirmButtonColor: '#111827',
            });
            return false;
        }

        return true;
    },

    _showErrors: function(errors) {
        var lines = '';
        var keys = Object.keys(errors);
        for (var i = 0; i < keys.length; i++) {
            lines += keys[i] + ': ' + errors[keys[i]].join(', ') + '\n';
        }
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            html: '<pre style="text-align:left;font-size:13px">' + lines + '</pre>',
            confirmButtonColor: '#111827',
        });
    },

    _resetForm: function() {
        var form = document.getElementById('bookingForm');
        if (form) form.reset();

        var summary = document.getElementById('crDurationSummary');
        if (summary) summary.style.display = 'none';

        document.getElementById('total_days').value = '';
        document.getElementById('total_hours').value = '';

        State.currentCarPrice = 0;
    },
};

/* ─── Init ───────────────────────────────────────────────────────────────── */

document.addEventListener('DOMContentLoaded', function() {
    console.log('%c[BjCarRental]', 'color:#e05c1a;font-weight:bold', 'Car listing initializing…');

    Filter.init();
    ViewModal.init();
    BookModal.init();
    Fleet.load();

    console.log('%c[BjCarRental]', 'color:#e05c1a;font-weight:bold', 'Ready');
});