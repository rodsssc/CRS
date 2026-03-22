/**
 * ════════════════════════════════════════════════════════════════
 * BjCarRental · Landing Page — home.js
 * ════════════════════════════════════════════════════════════════
 *
 * Architecture
 * ────────────
 *  Config      – URL base injected by Blade via window.LP_CONFIG
 *  Api         – thin fetch wrappers for /get-cars and /get-cars/stats
 *  Mock        – offline fallback data (mirrors real API shape)
 *  Format      – pure formatting helpers (currency, price range)
 *  Animate     – count-up, progress-bar, shimmer skeletons
 *  Card        – builds a single car card HTML string
 *  HeroPanel   – renders the Bootstrap carousel in the hero panel
 *  StatsStrip  – updates the 4-stat strip
 *  Fleet       – loads + filters the All Cars grid
 *  Featured    – loads the Featured Cars grid
 *  Reveal      – IntersectionObserver scroll-reveal (.lp-r -> .lp-v)
 *  Init        – DOMContentLoaded bootstrap
 */

'use strict';

/* ─── Config ─────────────────────────────────────────────────────────────── */

window.LP_CONFIG = window.LP_CONFIG || {
    carsUrl: '/get-cars',
    statsUrl: '/get-cars/stats',
    carUrl: '/client/car',
};

var LP = {
    url: window.LP_CONFIG,
    ALL_LIMIT: 9,
    HERO_LIMIT: 12, // max slides in the hero carousel
    FEATURED_LIMIT: 4,
};

/* ─── Api ────────────────────────────────────────────────────────────────── */

var Api = (function() {

    function getCars(params) {
        params = params || {};
        var url = new URL(LP.url.carsUrl, location.origin);
        if (params.status) url.searchParams.set('status', params.status);
        if (params.capacity) url.searchParams.set('capacity', params.capacity);
        if (params.limit) url.searchParams.set('limit', params.limit);

        return fetch(url)
            .then(function(res) { return res.json(); })
            .then(function(json) {
                if (!json.success) throw new Error('API returned success:false');
                return json.data || [];
            });
    }

    function getStats() {
        return fetch(LP.url.statsUrl)
            .then(function(res) { return res.json(); })
            .then(function(json) {
                if (!json.success) throw new Error('Stats API returned success:false');
                return json.data || {};
            });
    }

    return { getCars: getCars, getStats: getStats };

})();

/* ─── Mock ───────────────────────────────────────────────────────────────── */

var Mock = (function() {

    var CARS = [
        { id: 1, brand: 'Toyota', model: 'Vios', capacity: 5, status: 'available', rental_price_per_day: 1800, image_path: null },
        { id: 2, brand: 'Honda', model: 'City', capacity: 5, status: 'available', rental_price_per_day: 1950, image_path: null },
        { id: 3, brand: 'Mitsubishi', model: 'Montero', capacity: 7, status: 'rented', rental_price_per_day: 3200, image_path: null },
        { id: 4, brand: 'Ford', model: 'EcoSport', capacity: 5, status: 'available', rental_price_per_day: 2400, image_path: null },
        { id: 5, brand: 'Hyundai', model: 'Accent', capacity: 5, status: 'maintenance', rental_price_per_day: 1600, image_path: null },
        { id: 6, brand: 'Nissan', model: 'Navara', capacity: 5, status: 'available', rental_price_per_day: 2800, image_path: null },
        { id: 7, brand: 'Suzuki', model: 'Ertiga', capacity: 7, status: 'available', rental_price_per_day: 2200, image_path: null },
        { id: 8, brand: 'Kia', model: 'Sportage', capacity: 5, status: 'rented', rental_price_per_day: 2900, image_path: null },
    ];

    function getCars(params) {
        params = params || {};
        var list = params.status ?
            CARS.filter(function(c) { return c.status === params.status; }) :
            CARS;
        return list.slice(0, params.limit || CARS.length);
    }

    function getStats() {
        var available = CARS.filter(function(c) { return c.status === 'available'; }).length;
        var rented = CARS.filter(function(c) { return c.status === 'rented'; }).length;
        var unavailable = CARS.filter(function(c) { return c.status === 'unavailable'; }).length;
        var maintenance = CARS.filter(function(c) { return c.status === 'maintenance'; }).length;
        var total = CARS.length;
        var prices = CARS.filter(function(c) { return c.status === 'available'; })
            .map(function(c) { return c.rental_price_per_day; });

        return {
            total: total,
            available: available,
            rented: rented,
            unavailable: unavailable,
            maintenance: maintenance,
            available_pct: Math.round((available / total) * 100),
            price_min: Math.min.apply(null, prices),
            price_max: Math.max.apply(null, prices),
            happy_customers: 1000,
        };
    }

    return { getCars: getCars, getStats: getStats };

})();

/* ─── Format ─────────────────────────────────────────────────────────────── */

var Format = {

    price: function(value) {
        return parseFloat(value || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });
    },

    priceRange: function(min, max) {
        if (!min && !max) return '&#8212;';
        if (min === max) return '&#8369;' + this.price(min);
        return '&#8369;' + this.price(min) + '<br><small>to &#8369;' + this.price(max) + '</small>';
    },
};

/* ─── Animate ────────────────────────────────────────────────────────────── */

var Animate = {

    countUp: function(el, target, suffix) {
        if (!el || isNaN(target)) return;
        suffix = suffix || '';
        var DURATION = 1400;
        var start = performance.now();

        function tick(now) {
            var t = Math.min((now - start) / DURATION, 1);
            var eased = 1 - Math.pow(1 - t, 3);
            el.textContent = Math.round(target * eased).toLocaleString('en-PH') + suffix;
            if (t < 1) { requestAnimationFrame(tick); }
        }
        requestAnimationFrame(tick);
    },

    progressBar: function(barEl, pctEl, pct) {
        if (!barEl) return;
        var DURATION = 1000;
        setTimeout(function() {
            barEl.style.width = pct + '%';
            var start = performance.now();

            function tick(now) {
                var t = Math.min((now - start) / DURATION, 1);
                if (pctEl) { pctEl.textContent = Math.round(pct * t) + '%'; }
                if (t < 1) { requestAnimationFrame(tick); }
            }
            requestAnimationFrame(tick);
        }, 100);
    },

    skeletons: function(n) {
        var one = '<div class="lp-skel">' +
            '<div class="lp-skel-img"></div>' +
            '<div class="lp-skel-body">' +
            '<div class="lp-skel-line lp-sl-w"></div>' +
            '<div class="lp-skel-line lp-sl-m"></div>' +
            '<div class="lp-skel-line lp-sl-n"></div>' +
            '</div></div>';
        var html = '';
        for (var i = 0; i < n; i++) { html += one; }
        return html;
    },
};

/* ─── Card ───────────────────────────────────────────────────────────────── */

var Card = {

    STATUS: {
        available: { cls: 'lp-badge-av', icon: 'fa-circle-check', label: 'Available' },
        rented: { cls: 'lp-badge-re', icon: 'fa-lock', label: 'Rented' },
        maintenance: { cls: 'lp-badge-mn', icon: 'fa-wrench', label: 'Maintenance' },
        unavailable: { cls: 'lp-badge-un', icon: 'fa-circle-xmark', label: 'Unavailable' },
    },

    build: function(car) {
        var badge = this.STATUS[car.status] || this.STATUS.maintenance;
        var imgSrc = car.image_path ? '/storage/' + car.image_path : null;
        var isAvail = car.status === 'available';

        var imgHtml = imgSrc ?
            '<img src="' + imgSrc + '" alt="' + car.brand + ' ' + car.model + '" loading="lazy" decoding="async">' :
            '<div class="lp-card-ph">' +
            '<i class="fa-solid fa-car"></i>' +
            '<span>' + car.brand + ' ' + car.model + '</span>' +
            '</div>';

        var bookClass = isAvail ? 'lp-book' : 'lp-book lp-unavail';
        var bookIcon = isAvail ? 'fa-calendar-check' : 'fa-ban';
        var bookLabel = isAvail ? 'Book Now' : 'Unavailable';

        return '<div class="lp-card">' +
            '<div class="lp-card-img">' + imgHtml +
            '<span class="lp-badge ' + badge.cls + '">' +
            '<i class="fa-solid ' + badge.icon + '"></i> ' + badge.label +
            '</span></div>' +
            '<div class="lp-card-body">' +
            '<div class="lp-car-name">' + car.brand + ' ' + car.model + '</div>' +
            '<div class="lp-car-meta"><span><i class="fa-solid fa-users"></i> ' + car.capacity + ' Seater</span></div>' +
            '<div class="lp-price">&#8369;' + Format.price(car.rental_price_per_day) + '<small> /day</small></div>' +
            '<a href="' + LP.url.carUrl + '?car_id=' + car.id + '" class="' + bookClass + '">' +
            '<i class="fa-solid ' + bookIcon + '"></i> ' + bookLabel +
            '</a></div></div>';
    },

    renderGrid: function(gridEl, cars) {
        var html = '';
        for (var i = 0; i < cars.length; i++) { html += this.build(cars[i]); }
        gridEl.innerHTML = html;
    },
};

/* ─── HeroPanel — Bootstrap Carousel ─────────────────────────────────────── */

var HeroPanel = {

    STATUS_BADGE: {
        available: { cls: 'lp-carousel-badge-av', icon: 'fa-circle-check', label: 'Available' },
        rented: { cls: 'lp-carousel-badge-re', icon: 'fa-lock', label: 'Rented' },
        maintenance: { cls: 'lp-carousel-badge-mn', icon: 'fa-wrench', label: 'Maintenance' },
        unavailable: { cls: 'lp-carousel-badge-un', icon: 'fa-circle-xmark', label: 'Unavailable' },
    },

    /**
     * Fetch all cars (any status), then build Bootstrap carousel slides.
     * Each slide shows the car image + a status badge + name + price overlay.
     */
    render: function() {
        var self = this;

        Api.getCars({ limit: LP.HERO_LIMIT })
            .then(function(cars) {
                self._build(cars.length ? cars : Mock.getCars({ limit: LP.HERO_LIMIT }));
            })
            .catch(function() {
                self._build(Mock.getCars({ limit: LP.HERO_LIMIT }));
            });
    },

    /** @private — builds carousel HTML and reveals it */
    _build: function(cars) {
        var innerEl = document.getElementById('lpCarouselInner');
        var carousel = document.getElementById('lpHeroCarousel');
        var skeleton = document.getElementById('lpCarouselSkeleton');

        if (!innerEl || !carousel) return;

        var html = '';

        for (var i = 0; i < cars.length; i++) {
            var car = cars[i];
            var badge = this.STATUS_BADGE[car.status] || this.STATUS_BADGE.maintenance;
            var imgSrc = car.image_path ? '/storage/' + car.image_path : null;
            var active = i === 0 ? ' active' : '';
            // Auto-advance: 4 s for first slide, 3 s for the rest
            var interval = i === 0 ? '4000' : '3000';

            var imgContent = imgSrc ?
                '<img src="' + imgSrc + '" class="d-block w-100 lp-carousel-img" alt="' + car.brand + ' ' + car.model + '" loading="lazy">' :
                '<div class="lp-carousel-placeholder">' +
                '<i class="fa-solid fa-car"></i>' +
                '<span>' + car.brand + ' ' + car.model + '</span>' +
                '</div>';

            html += '<div class="carousel-item' + active + '" data-bs-interval="' + interval + '">' +
                imgContent +
                // Dark gradient overlay
                '<div class="lp-carousel-overlay"></div>' +
                // Status badge (top-left)
                '<span class="lp-carousel-badge ' + badge.cls + '">' +
                '<i class="fa-solid ' + badge.icon + '"></i> ' + badge.label +
                '</span>' +
                // Caption (bottom)
                '<div class="lp-carousel-caption">' +
                '<div class="lp-carousel-name">' + car.brand + ' ' + car.model + '</div>' +
                '<div class="lp-carousel-price">&#8369;' + Format.price(car.rental_price_per_day) + '<small>/day</small></div>' +
                '</div>' +
                '</div>';
        }

        innerEl.innerHTML = html;

        // Hide skeleton, show carousel
        if (skeleton) { skeleton.style.display = 'none'; }
        carousel.classList.remove('d-none');
    },
};

/* ─── StatsStrip ─────────────────────────────────────────────────────────── */

var StatsStrip = {

    setAvailable: function(count) {
        var el = document.getElementById('lpStatAvail');
        if (el) { Animate.countUp(el, count); }
    },

    setCustomers: function(count) {
        var el = document.getElementById('lpStatCustomers');
        if (el) { Animate.countUp(el, count, '+'); }
    },
};

/* ─── Fleet ──────────────────────────────────────────────────────────────── */

var Fleet = {

    _readFilters: function() {
        var statusEl = document.getElementById('lpStatus');
        var capacityEl = document.getElementById('lpCapacity');
        var sortEl = document.getElementById('lpSort');
        return {
            status: statusEl ? statusEl.value : '',
            capacity: capacityEl ? capacityEl.value : '',
            sort: sortEl ? sortEl.value : '',
        };
    },

    _sort: function(cars, sort) {
        var list = cars.slice();
        if (sort === 'price_asc') { list.sort(function(a, b) { return a.rental_price_per_day - b.rental_price_per_day; }); }
        if (sort === 'price_desc') { list.sort(function(a, b) { return b.rental_price_per_day - a.rental_price_per_day; }); }
        if (sort === 'capacity') { list.sort(function(a, b) { return a.capacity - b.capacity; }); }
        return list;
    },

    load: function() {
        var self = this;
        var filters = this._readFilters();
        var gridEl = document.getElementById('lpAllGrid');
        if (!gridEl) return;

        gridEl.innerHTML = Animate.skeletons(6);

        Api.getCars({ status: filters.status, capacity: filters.capacity, limit: LP.ALL_LIMIT })
            .then(function(raw) {
                var cars = self._sort(raw, filters.sort);
                cars.length ? Card.renderGrid(gridEl, cars) : (gridEl.innerHTML = self._emptyState());
            })
            .catch(function() {
                Card.renderGrid(gridEl, self._sort(Mock.getCars({ status: filters.status, limit: LP.ALL_LIMIT }), filters.sort));
            });
    },

    reset: function() {
        ['lpStatus', 'lpCapacity', 'lpSort'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) { el.value = ''; }
        });
        this.load();
    },

    _emptyState: function() {
        return '<div class="lp-empty" style="grid-column:1/-1;text-align:center;padding:3rem 1rem;color:var(--text-muted)">' +
            '<i class="fa-solid fa-car-burst" style="font-size:2.5rem;margin-bottom:.75rem;display:block;opacity:.3"></i>' +
            '<p style="font-weight:500">No vehicles match your filters.</p>' +
            '<p style="font-size:.85rem;margin-top:.25rem">Try adjusting your search criteria.</p>' +
            '</div>';
    },
};

/* ─── Featured ───────────────────────────────────────────────────────────── */

var Featured = {

    load: function() {
        var gridEl = document.getElementById('lpFeatGrid');
        if (!gridEl) return;

        Api.getCars({ status: 'available', limit: LP.FEATURED_LIMIT })
            .then(function(cars) {
                Card.renderGrid(gridEl, cars.length ? cars : Mock.getCars({ status: 'available', limit: LP.FEATURED_LIMIT }));
            })
            .catch(function() {
                Card.renderGrid(gridEl, Mock.getCars({ status: 'available', limit: LP.FEATURED_LIMIT }));
            });
    },
};

/* ─── Reveal ─────────────────────────────────────────────────────────────── */

var Reveal = {

    init: function() {
        var obs = new IntersectionObserver(function(entries) {
            for (var i = 0; i < entries.length; i++) {
                if (entries[i].isIntersecting) { entries[i].target.classList.add('lp-v'); }
            }
        }, { threshold: 0.08 });

        var targets = document.querySelectorAll('.lp-r');
        for (var i = 0; i < targets.length; i++) { obs.observe(targets[i]); }
    },
};

/* ─── Init ───────────────────────────────────────────────────────────────── */

document.addEventListener('DOMContentLoaded', function() {

    Reveal.init();

    // Stats strip — no longer drives the hero panel
    Api.getStats()
        .then(function(stats) {
            StatsStrip.setAvailable(stats.available || 0);
            StatsStrip.setCustomers(stats.happy_customers || 0);
        })
        .catch(function() {
            var stats = Mock.getStats();
            StatsStrip.setAvailable(stats.available);
            StatsStrip.setCustomers(stats.happy_customers);
        });

    // Hero carousel — fetches all cars independently
    HeroPanel.render();

    // Car grids
    Featured.load();
    Fleet.load();

    // Filter controls
    var applyBtn = document.getElementById('lpApply');
    var resetBtn = document.getElementById('lpReset');
    if (applyBtn) { applyBtn.addEventListener('click', function() { Fleet.load(); }); }
    if (resetBtn) { resetBtn.addEventListener('click', function() { Fleet.reset(); }); }

    ['lpStatus', 'lpCapacity', 'lpSort'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) { el.addEventListener('change', function() { Fleet.load(); }); }
    });
});