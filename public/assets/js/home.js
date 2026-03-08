// ════════════════════════════════════════════════════════════════
// BjCarRental - Landing Page (public/assets)
// - Loads cars for featured + full fleet
// - Rotates a hero image spotlight (available cars)
// ════════════════════════════════════════════════════════════════

const API_URL = '/get-cars';

// Logging helpers
const log = (msg, data) => console.log(`%c[BjCarRental] ${msg}`, 'color: #e85d26; font-weight: bold;', data || '');
const err = (msg, e) => console.error(`%c[BjCarRental] ${msg}`, 'color: #dc2626; font-weight: bold;', e || '');

// ───────────────────────────────────────────────────────────────
// Card Builder
// ───────────────────────────────────────────────────────────────
function buildCarCard(car) {
    const status = car.status || 'available';
    const statusClass = status === 'available' ? 'lp-badge-av' : status === 'rented' ? 'lp-badge-re' : 'lp-badge-mn';
    const statusIcon = status === 'available' ? 'fa-circle-check' : status === 'rented' ? 'fa-lock' : 'fa-wrench';
    const statusText = status.charAt(0).toUpperCase() + status.slice(1);
    const price = parseFloat(car.rental_price_per_day || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });
    const image = car.image_path ? `/storage/${car.image_path}` : null;
    const carQuery = encodeURIComponent(`${car.brand || ''} ${car.model || ''}`.trim());

    return `
        <div class="lp-card">
            <div class="lp-card-img">
                ${image
                    ? `<img src="${image}" alt="${car.brand} ${car.model}" loading="lazy">`
                    : `<div class="lp-card-ph"><i class="fa-solid fa-car"></i><span>${car.brand} ${car.model}</span></div>`
                }
                <span class="lp-badge ${statusClass}"><i class="fa-solid ${statusIcon}"></i> ${statusText}</span>
            </div>
            <div class="lp-card-body">
                <div class="lp-car-name">${car.brand} ${car.model}</div>
                <div class="lp-car-meta"><span><i class="fa-solid fa-users"></i> ${car.capacity} Seater</span></div>
                <div class="lp-price">₱${price}<small> /day</small></div>
                <a href="/client/car?q=${carQuery}" class="lp-book ${status !== 'available' ? 'lp-unavail' : ''}">
                    <i class="fa-solid fa-${status === 'available' ? 'calendar-check' : 'ban'}"></i>
                    ${status === 'available' ? 'Book Now' : 'Unavailable'}
                </a>
            </div>
        </div>
    `;
}

// ───────────────────────────────────────────────────────────────
// Skeleton Loader
// ───────────────────────────────────────────────────────────────
function createSkeletons(count) {
    let html = '';
    for (let i = 0; i < count; i++) {
        html += `<div class="lp-skel"><div class="lp-skel-img"></div><div class="lp-skel-body"><div class="lp-skel-line lp-sl-w"></div><div class="lp-skel-line lp-sl-m"></div><div class="lp-skel-line lp-sl-n"></div></div></div>`;
    }
    return html;
}

// ───────────────────────────────────────────────────────────────
// Update Available Count (header + hero)
// ───────────────────────────────────────────────────────────────
function updateAvailableCount(cars) {
    const available = (cars || []).filter(c => c.status === 'available').length;
    const statEls = document.querySelectorAll('#lpStatAvail, #lpHeroAvail');
    statEls.forEach(el => el.textContent = available);
    return available;
}

// ───────────────────────────────────────────────────────────────
// Fetch Cars
// ───────────────────────────────────────────────────────────────
async function fetchCars(params = {}) {
    const queryString = new URLSearchParams(params).toString();
    const url = queryString ? `${API_URL}?${queryString}` : API_URL;

    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const data = await response.json();

        if (!data.success) throw new Error('Invalid response');

        return {
            cars: data.data || [],
            error: null,
        };
    } catch (e) {
        err('Failed to fetch cars', e);
        return {
            cars: [],
            error: e,
        };
    }
}

// ───────────────────────────────────────────────────────────────
// Hero Spotlight (image rotation)
// ───────────────────────────────────────────────────────────────
let heroCars = [];
let heroIndex = 0;
let heroTimer = null;

function setHeroCar(car) {
    const spotlight = document.getElementById('lpHeroSpotlight');
    const img = document.getElementById('lpHeroCarImg');
    const nameEl = document.getElementById('lpHeroCarName');
    const metaEl = document.getElementById('lpHeroCarMeta');
    const priceEl = document.getElementById('lpHeroCarPrice');
    const linkEl = document.getElementById('lpHeroCarLink');

    if (!spotlight || !img || !nameEl || !metaEl || !priceEl || !linkEl) return;
    if (!car) {
        spotlight.style.display = 'none';
        return;
    }

    spotlight.style.display = '';

    const title = `${car.brand || ''} ${car.model || ''}`.trim() || 'Available Car';
    const year = car.year ? `${car.year}` : '';
    const seats = car.capacity ? `${car.capacity} seater` : '';
    const meta = [year, seats].filter(Boolean).join(' • ') || 'Ready for booking';
    const price = parseFloat(car.rental_price_per_day || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });
    const imgSrc = car.image_path ? `/storage/${car.image_path}` : '';

    nameEl.textContent = title;
    metaEl.textContent = meta;
    priceEl.textContent = `₱${price} / day`;

    const carQuery = encodeURIComponent(title);
    linkEl.href = `/client/car?q=${carQuery}`;

    if (imgSrc) {
        img.style.opacity = '0.25';
        img.src = imgSrc;
        img.onload = () => { img.style.opacity = '1'; };
        img.onerror = () => { img.style.opacity = '1'; };
    }
}

function startHeroRotation(cars) {
    heroCars = (cars || []).filter(c => c.status === 'available' && c.image_path);
    heroIndex = 0;

    if (heroTimer) {
        clearInterval(heroTimer);
        heroTimer = null;
    }

    if (heroCars.length === 0) {
        setHeroCar(null);
        return;
    }

    setHeroCar(heroCars[0]);

    // Rotate every 8 seconds (simple + readable)
    heroTimer = setInterval(() => {
        heroIndex = (heroIndex + 1) % heroCars.length;
        setHeroCar(heroCars[heroIndex]);
    }, 8000);
}

// ───────────────────────────────────────────────────────────────
// Load Featured Cars
// ───────────────────────────────────────────────────────────────
async function loadFeatured() {
    const featGrid = document.getElementById('lpFeatGrid');
    if (!featGrid) return;

    log('Loading featured cars...');
    featGrid.innerHTML = createSkeletons(4);

    const { cars, error } = await fetchCars({ limit: 4, status: 'available' });

    if (error) {
        featGrid.innerHTML = '<p style="text-align:center;color:#dc2626;">Unable to load featured cars right now. Please try again in a moment.</p>';
        return;
    }

    if (cars.length > 0) {
        featGrid.innerHTML = cars.map(buildCarCard).join('');
        log('Featured cars loaded', cars.length);
    } else {
        featGrid.innerHTML = '<p style="text-align:center;color:#999;">No featured cars available</p>';
    }
}

// ───────────────────────────────────────────────────────────────
// Load All Cars
// ───────────────────────────────────────────────────────────────
async function loadAllCars() {
    const allGrid = document.getElementById('lpAllGrid');
    if (!allGrid) return;

    const status = document.getElementById('lpStatus')?.value || '';
    const capacity = document.getElementById('lpCapacity')?.value || '';
    const sort = document.getElementById('lpSort')?.value || '';

    log('Loading all cars', { status, capacity, sort });
    allGrid.innerHTML = createSkeletons(6);

    const params = {};
    if (status) params.status = status;
    if (capacity) params.capacity = capacity;

    const { cars, error } = await fetchCars(params);

    if (error) {
        allGrid.innerHTML = '<p style="text-align:center;color:#dc2626;">Unable to load cars. Please check your connection and try again.</p>';
        updateAvailableCount([]);
        return;
    }

    if (cars.length === 0) {
        allGrid.innerHTML = '<p style="text-align:center;color:#999;">No cars match your filters</p>';
        updateAvailableCount([]);
        return;
    }

    if (sort === 'price_asc') {
        cars.sort((a, b) => (a.rental_price_per_day || 0) - (b.rental_price_per_day || 0));
    } else if (sort === 'price_desc') {
        cars.sort((a, b) => (b.rental_price_per_day || 0) - (a.rental_price_per_day || 0));
    } else if (sort === 'capacity') {
        cars.sort((a, b) => (a.capacity || 0) - (b.capacity || 0));
    }

    allGrid.innerHTML = cars.map(buildCarCard).join('');
    updateAvailableCount(cars);
    log('All cars loaded', cars.length);
}

// ───────────────────────────────────────────────────────────────
// Search (used by hero search bar)
// ───────────────────────────────────────────────────────────────
function doSearch() {
    const query = document.getElementById('lpSearch')?.value.trim().toLowerCase() || '';
    if (!query) return;

    const fleetSection = document.getElementById('lp-fleet');
    if (fleetSection) {
        fleetSection.scrollIntoView({ behavior: 'smooth' });
    }

    setTimeout(() => {
        const cards = document.querySelectorAll('#lpAllGrid .lp-card');
        cards.forEach(card => {
            const name = card.querySelector('.lp-car-name')?.textContent.toLowerCase() || '';
            const match = name.includes(query);
            card.style.opacity = match ? '1' : '0.3';
            card.style.transform = match ? 'none' : 'scale(0.97)';
            card.style.transition = 'opacity 0.3s, transform 0.3s';
        });
    }, 600);
}

// Blade uses onclick="lpDoSearch()"
window.lpDoSearch = doSearch;

// ───────────────────────────────────────────────────────────────
// Event Listeners
// ───────────────────────────────────────────────────────────────
function setupEventListeners() {
    const searchInput = document.getElementById('lpSearch');
    if (searchInput) {
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') doSearch();
        });
    }

    const applyBtn = document.getElementById('lpApply');
    const resetBtn = document.getElementById('lpReset');
    const statusSel = document.getElementById('lpStatus');
    const capacitySel = document.getElementById('lpCapacity');
    const sortSel = document.getElementById('lpSort');

    if (applyBtn) applyBtn.addEventListener('click', loadAllCars);
    if (statusSel) statusSel.addEventListener('change', loadAllCars);
    if (capacitySel) capacitySel.addEventListener('change', loadAllCars);
    if (sortSel) sortSel.addEventListener('change', loadAllCars);

    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            if (statusSel) statusSel.value = '';
            if (capacitySel) capacitySel.value = '';
            if (sortSel) sortSel.value = '';
            document.querySelectorAll('#lpAllGrid .lp-card').forEach(c => {
                c.style.opacity = '1';
                c.style.transform = 'none';
            });
            loadAllCars();
        });
    }
}

// ───────────────────────────────────────────────────────────────
// Scroll Reveal Animation
// ───────────────────────────────────────────────────────────────
function setupScrollReveal() {
    const observer = new IntersectionObserver(
        entries => entries.forEach(e => {
            if (e.isIntersecting) e.target.classList.add('lp-v');
        }),
        { threshold: 0.08 }
    );

    document.querySelectorAll('.lp-r').forEach(el => observer.observe(el));
}

// ───────────────────────────────────────────────────────────────
// Initialize
// ───────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', async () => {
    log('Initializing landing page...');

    setupEventListeners();
    setupScrollReveal();

    await loadFeatured();
    await loadAllCars();

    // Hero spotlight: only available cars (with images) rotate
    const { cars: availableForHero } = await fetchCars({ status: 'available', limit: 12 });
    startHeroRotation(availableForHero);

    log('Landing page ready');
});

