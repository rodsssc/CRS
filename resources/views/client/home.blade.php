<x-app-layout>

{{-- ══ HERO ══ --}}
  <section class="lp-hero">
    <div class="lp-hero-inner">
      <div>
        <div class="lp-eyebrow">Premium Car Rentals by BjCarRental · Est. 2025</div>
        <h1 class="lp-h1">Find Your<br>Perfect <span>Ride</span></h1>
        <p class="lp-sub">Explore our extensive collection of vehicles and book your next journey with ease. Affordable rates, reliable service, flexible options.</p>
        <div class="lp-btns">
          <a href="{{ route('client.car.index') }}" class="lp-btn-dark">
            <i class="fa-solid fa-search"></i> Browse Fleet
          </a>
          <a href="#lp-why" class="lp-btn-outline">
            <i class="fa-solid fa-circle-info"></i> How It Works
          </a>
        </div>
        <div class="lp-search">
          <input type="text" id="lpSearch" placeholder="Search by brand or model…">
          <button onclick="lpDoSearch()"><i class="fa-solid fa-magnifying-glass" style="margin-right:.3rem"></i>Search</button>
        </div>
      </div>
      <div class="lp-hero-panel">
        <div class="lp-hero-spotlight" id="lpHeroSpotlight">
          <div class="lp-hero-imgwrap">
            <img id="lpHeroCarImg" alt="Available car" loading="lazy">
            <div class="lp-hero-overlay">
              <div class="lp-hero-kicker">
                <span class="lp-hero-pill"><i class="fa-solid fa-circle-check"></i> Available Now</span>
                <span class="lp-hero-pill lp-hero-pill-muted"><span id="lpHeroAvail">—</span> cars</span>
              </div>
              <div class="lp-hero-title" id="lpHeroCarName">—</div>
              <div class="lp-hero-meta" id="lpHeroCarMeta">—</div>
              <div class="lp-hero-price" id="lpHeroCarPrice">—</div>
              <a class="lp-hero-cta" id="lpHeroCarLink" href="{{ route('client.car.index') }}">
                Browse This Car <i class="fa-solid fa-arrow-right"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ══ STATS STRIP ══ --}}
  <div class="lp-strip">
    <div class="lp-strip-inner">
      <div class="lp-stat lp-r">
        <span class="lp-stat-icon" style="color:var(--orange)"><i class="fa-solid fa-car"></i></span>
        <span class="lp-stat-num" id="lpStatAvail">—</span>
        <span class="lp-stat-lbl">Cars Available</span>
      </div>
      <div class="lp-stat lp-r lp-rd1">
        <span class="lp-stat-icon" style="color:var(--success)"><i class="fa-solid fa-users"></i></span>
        <span class="lp-stat-num">1,000+</span>
        <span class="lp-stat-lbl">Happy Customers</span>
      </div>
      <div class="lp-stat lp-r lp-rd2">
        <span class="lp-stat-icon" style="color:#6366f1"><i class="fa-solid fa-shield"></i></span>
        <span class="lp-stat-num">24/7</span>
        <span class="lp-stat-lbl">Customer Support</span>
      </div>
      <div class="lp-stat lp-r lp-rd3">
        <span class="lp-stat-icon" style="color:var(--warning)"><i class="fa-solid fa-medal"></i></span>
        <span class="lp-stat-num">15 yrs</span>
        <span class="lp-stat-lbl">Industry Experience</span>
      </div>
    </div>
  </div>

  {{-- ══ FEATURED ══ --}}
  <section class="lp-section lp-section-white" id="lp-featured">
    <div class="lp-inner">
      <div class="lp-sec-head lp-r">
        <div class="lp-sec-tag">Top Picks</div>
        <h2 class="lp-sec-title">Featured Vehicles</h2>
        <p class="lp-sec-sub">Our most popular cars, hand-picked for you</p>
      </div>
      <div class="lp-grid" id="lpFeatGrid">
        @for($i=0;$i<4;$i++)
        <div class="lp-skel"><div class="lp-skel-img"></div><div class="lp-skel-body"><div class="lp-skel-line lp-sl-w"></div><div class="lp-skel-line lp-sl-m"></div><div class="lp-skel-line lp-sl-n"></div></div></div>
        @endfor
      </div>
      <div style="text-align:center;margin-top:2rem" class="lp-r">
        <a href="{{ route('client.car.index') }}" class="lp-btn-outline" style="display:inline-flex;gap:.45rem;align-items:center">
          View All Cars <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>
    </div>
  </section>

  {{-- ══ CTA BAND ══ --}}
  <div class="lp-cta">
    <div class="lp-cta-inner">
      <div>
        <h2>Ready to Hit the Road?</h2>
        <p>Browse our full fleet and book in minutes. Easy, fast, and reliable.</p>
      </div>
      <a href="{{ route('client.car.index') }}" class="lp-cta-btn">
        <i class="fa-solid fa-calendar-check" style="margin-right:.4rem"></i> Book Now
      </a>
    </div>
  </div>

  {{-- ══ ALL CARS ══ --}}
  <section class="lp-section lp-section-gray" id="lp-fleet">
    <div class="lp-inner">
      <div class="lp-sec-head lp-r">
        <div class="lp-sec-tag">Full Fleet</div>
        <h2 class="lp-sec-title">All Available Vehicles</h2>
        <p class="lp-sec-sub">Browse our complete lineup and filter by your needs</p>
      </div>
      <div class="lp-filters lp-r">
        <div class="lp-fg">
          <label class="lp-flbl">Status</label>
          <select class="lp-fsel" id="lpStatus">
            <option value="">All Status</option>
            <option value="available">Available</option>
            <option value="rented">Rented</option>
            <option value="maintenance">Maintenance</option>
          </select>
        </div>
        <div class="lp-fg">
          <label class="lp-flbl">Capacity</label>
          <select class="lp-fsel" id="lpCapacity">
            <option value="">All Seats</option>
            <option value="2">2+ Seater</option>
            <option value="4">4+ Seater</option>
            <option value="5">5+ Seater</option>
            <option value="7">7+ Seater</option>
          </select>
        </div>
        <div class="lp-fg">
          <label class="lp-flbl">Sort By</label>
          <select class="lp-fsel" id="lpSort">
            <option value="">Default</option>
            <option value="price_asc">Price: Low → High</option>
            <option value="price_desc">Price: High → Low</option>
            <option value="capacity">Capacity</option>
          </select>
        </div>
        <button class="lp-fbtn" id="lpApply"><i class="fa-solid fa-sliders" style="margin-right:.35rem"></i>Apply</button>
        <button class="lp-fbtn-reset" id="lpReset"><i class="fa-solid fa-rotate-left" style="margin-right:.35rem"></i>Reset</button>
      </div>
      <div class="lp-grid" id="lpAllGrid">
        @for($i=0;$i<6;$i++)
        <div class="lp-skel"><div class="lp-skel-img"></div><div class="lp-skel-body"><div class="lp-skel-line lp-sl-w"></div><div class="lp-skel-line lp-sl-m"></div><div class="lp-skel-line lp-sl-n"></div></div></div>
        @endfor
      </div>
    </div>
  </section>

  {{-- ══ WHY US ══ --}}
  <section class="lp-section lp-section-white" id="lp-why">
    <div class="lp-inner">
      <div class="lp-sec-head lp-r" style="text-align:center">
        <div class="lp-sec-tag" style="justify-content:center">Our Advantage</div>
        <h2 class="lp-sec-title">Why Choose Us?</h2>
        <p class="lp-sec-sub">Everything you need for a seamless rental experience</p>
      </div>
      <div class="lp-why-grid">
        <div class="lp-why-card lp-r">
          <div class="lp-why-ico"><i class="fa-solid fa-car"></i></div>
          <h4>Wide Selection</h4>
          <p>From compact sedans to spacious SUVs — a vehicle for every need and budget.</p>
        </div>
        <div class="lp-why-card lp-r lp-rd1">
          <div class="lp-why-ico"><i class="fa-solid fa-tag"></i></div>
          <h4>Best Rates</h4>
          <p>Transparent pricing, no hidden fees. Get the best value every single time.</p>
        </div>
        <div class="lp-why-card lp-r lp-rd2">
          <div class="lp-why-ico"><i class="fa-solid fa-bolt"></i></div>
          <h4>Instant Booking</h4>
          <p>Book online in minutes. No paperwork, no waiting — just drive and go.</p>
        </div>
        <div class="lp-why-card lp-r lp-rd3">
          <div class="lp-why-ico"><i class="fa-solid fa-headset"></i></div>
          <h4>24/7 Support</h4>
          <p>Our team is always here whenever you need assistance, day or night.</p>
        </div>
      </div>
    </div>
  </section>

  {{-- ══ FOOTER INFO ══ --}}
  <footer class="lp-foot">
    <div class="lp-foot-inner">
      <div class="lp-foot-brand">
        <h3><span>Drive</span>Ease</h3>
        <p>Premium car rentals made simple. Reliable vehicles, competitive pricing, and a fleet for every journey.</p>
        <div class="lp-foot-socials">
          <a href="#" class="lp-soc"><i class="fa-brands fa-facebook-f"></i></a>
          <a href="#" class="lp-soc"><i class="fa-brands fa-instagram"></i></a>
          <a href="#" class="lp-soc"><i class="fa-brands fa-twitter"></i></a>
        </div>
      </div>
      <div class="lp-foot-col">
        <h4>Quick Links</h4>
        <ul>
          <li><a href="{{ route('client.car.index') }}"><i class="fa-solid fa-chevron-right"></i>Browse Fleet</a></li>
          <li><a href="#lp-why"><i class="fa-solid fa-chevron-right"></i>How It Works</a></li>
          <li><a href="#lp-featured"><i class="fa-solid fa-chevron-right"></i>Featured Cars</a></li>
          <li><a href="#lp-foot-contact"><i class="fa-solid fa-chevron-right"></i>Contact</a></li>
        </ul>
      </div>
      <div class="lp-foot-col" id="lp-foot-contact">
        <h4>Contact</h4>
        <ul>
          <li><a href="tel:+1234567890"><i class="fa-solid fa-phone"></i>+1 (234) 567-890</a></li>
          <li><a href="mailto:info@driveease.com"><i class="fa-solid fa-envelope"></i>info@driveease.com</a></li>
          <li><a href="#"><i class="fa-solid fa-map-pin"></i>123 Main Street, City</a></li>
        </ul>
      </div>
    </div>
    <div class="lp-foot-bottom">
      <span>© {{ date('Y') }} DriveEase. All rights reserved.</span>
      <span>Built with ♥ for drivers</span>
    </div>
  </footer>

</div>

<script defer src="{{ asset('assets/js/home.js') }}"></script>

</x-app-layout>