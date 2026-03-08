// ════════════════════════════════════════════════════════════════
// Landing Page - Car Gallery & Filters  
// Clean, modular JavaScript
// ════════════════════════════════════════════════════════════════

// Scroll Reveal
const lpObs = new IntersectionObserver(
    entries => entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('lp-v'); }), { threshold: 0.08 }
);
document.querySelectorAll('.lp-r').forEach(el => lpObs.observe(el));

// Card Builder
function lpCard(car) {
    const bc = car.status === 'available' ? 'lp-badge-av' : car.status === 'rented' ? 'lp-badge-re' : 'lp-badge-mn';
    const bi = car.status === 'available' ? 'fa-circle-check' : car.status === 'rented' ? 'fa-lock' : 'fa-wrench';
    const bt = car.status.charAt(0).toUpperCase() + car.status.slice(1);
    const px = parseFloat(car.rental_price_per_day || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });
    const img = car.image_path ? `/storage/${car.image_path}` : null;
    return `<div class="lp-card">
    <div class="lp-card-img">
      ${img ? `<img src="${img}" alt="${car.brand} ${car.model}" loading="lazy">` : `<div class="lp-card-ph"><i class="fa-solid fa-car"></i><span>${car.brand} ${car.model}</span></div>`}
      <span class="lp-badge ${bc}"><i class="fa-solid ${bi}"></i> ${bt}</span>
    </div>
    <div class="lp-card-body">
      <div class="lp-car-name">${car.brand} ${car.model}</div>
      <div class="lp-car-meta"><span><i class="fa-solid fa-users"></i> ${car.capacity} Seater</span></div>
      <div class="lp-price">₱${px}<small> /day</small></div>
      <a href="/client/car?car_id=${car.id}" class="lp-book ${car.status !== 'available' ? 'lp-unavail' : ''}">
        <i class="fa-solid fa-${car.status === 'available' ? 'calendar-check' : 'ban'}"></i>
        ${car.status === 'available' ? 'Book Now' : 'Unavailable'}
      </a>
    </div>
  </div>`;
}

// Skeleton Loader
function lpSkels(n) {
  return Array(n).fill(`<div class="lp-skel"><div class="lp-skel-img"></div><div class="lp-skel-body"><div class="lp-skel-line lp-sl-w"></div><div class="lp-skel-line lp-sl-m"></div><div class="lp-skel-line lp-sl-n"></div></div></div>`).join('');
}

// Update Stats
function lpSetAvail(n) {
  document.getElementById('lpStatAvail').textContent = n;
  document.getElementById('lpHeroAvail').textContent = n;
}

// Load Featured
function lpLoadFeatured() {
  fetch('/api/cars?limit=4&status=available')
    .then(r => r.json())
    .then(d => {
      document.getElementById('lpFeatGrid').innerHTML =
        (d.success && d.data?.length) ? d.data.map(lpCard).join('') : lpMock(4, 'available');
    })
    .catch(() => { document.getElementById('lpFeatGrid').innerHTML = lpMock(4, 'available'); });
}

// Load All Cars
function lpLoadAll() {
  const status = document.getElementById('lpStatus').value;
  const capacity = document.getElementById('lpCapacity').value;
  const sort = document.getElementById('lpSort').value;
  let url = '/api/cars?';
  if (status) url += `status=${status}&`;
  if (capacity) url += `capacity=${capacity}&`;

  const el = document.getElementById('lpAllGrid');
  el.innerHTML = lpSkels(6);

  fetch(url)
    .then(r => r.json())
    .then(d => {
      if (d.success && d.data?.length) {
        let cars = [...d.data];
        if (sort === 'price_asc') cars.sort((a, b) => a.rental_price_per_day - b.rental_price_per_day);
        if (sort === 'price_desc') cars.sort((a, b) => b.rental_price_per_day - a.rental_price_per_day);
        if (sort === 'capacity') cars.sort((a, b) => a.capacity - b.capacity);
        lpSetAvail(cars.filter(c => c.status === 'available').length);
        el.innerHTML = cars.map(lpCard).join('');
      } else {
        el.innerHTML = lpMock(8);
      }
    })
    .catch(() => { el.innerHTML = lpMock(8); });
}

// Mock Data
function lpMock(count, forceStatus) {
  const data = [
    { id: 1, brand: 'Toyota', model: 'Vios', capacity: 5, status: 'available', rental_price_per_day: 1800 },
    { id: 2, brand: 'Honda', model: 'City', capacity: 5, status: 'available', rental_price_per_day: 1950 },
    { id: 3, brand: 'Mitsubishi', model: 'Montero', capacity: 7, status: 'rented', rental_price_per_day: 3200 },
    { id: 4, brand: 'Ford', model: 'EcoSport', capacity: 5, status: 'available', rental_price_per_day: 2400 },
    { id: 5, brand: 'Hyundai', model: 'Accent', capacity: 5, status: 'maintenance', rental_price_per_day: 1600 },
    { id: 6, brand: 'Nissan', model: 'Navara', capacity: 5, status: 'available', rental_price_per_day: 2800 },
    { id: 7, brand: 'Suzuki', model: 'Ertiga', capacity: 7, status: 'available', rental_price_per_day: 2200 },
    { id: 8, brand: 'Kia', model: 'Sportage', capacity: 5, status: 'rented', rental_price_per_day: 2900 },
  ];
  let cars = (forceStatus ? data.filter(c => c.status === forceStatus) : data).slice(0, count);
  lpSetAvail(cars.filter(c => c.status === 'available').length);
  return cars.map(lpCard).join('');
}

// Hero Search
function lpDoSearch() {
  const q = document.getElementById('lpSearch').value.trim().toLowerCase();
  if (!q) return;
  document.getElementById('lp-fleet').scrollIntoView({ behavior: 'smooth' });
  setTimeout(() => {
    document.querySelectorAll('#lpAllGrid .lp-card').forEach(card => {
      const name = card.querySelector('.lp-car-name')?.textContent.toLowerCase() || '';
      const match = name.includes(q);
      card.style.opacity = match ? '1' : '0.28';
      card.style.transform = match ? 'none' : 'scale(0.97)';
      card.style.transition = 'opacity .3s, transform .3s';
    });
  }, 700);
}

// Events
document.getElementById('lpSearch').addEventListener('keydown', e => { if (e.key === 'Enter') lpDoSearch(); });
document.getElementById('lpReset').addEventListener('click', () => {
  ['lpStatus', 'lpCapacity', 'lpSort'].forEach(id => document.getElementById(id).value = '');
  document.querySelectorAll('#lpAllGrid .lp-card').forEach(c => { c.style.opacity = '1'; c.style.transform = 'none'; });
  lpLoadAll();
});

// Init
document.addEventListener('DOMContentLoaded', () => {
  lpLoadFeatured();
  lpLoadAll();
  document.getElementById('lpApply').addEventListener('click', lpLoadAll);
  document.getElementById('lpStatus').addEventListener('change', lpLoadAll);
  document.getElementById('lpCapacity').addEventListener('change', lpLoadAll);
  document.getElementById('lpSort').addEventListener('change', lpLoadAll);
});