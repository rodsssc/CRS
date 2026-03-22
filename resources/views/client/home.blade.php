<x-app-layout>

{{--
    ════════════════════════════════════════════════════════════════
    BjCarRental · Landing Page
    ════════════════════════════════════════════════════════════════
    JS:  assets/js/home.js   (all dynamic behaviour)
    CSS: assets/css/home.css (all landing-page styles)
    ════════════════════════════════════════════════════════════════
--}}

{{-- ══════════════════════════════════════════════════════════════
     HERO
     ══════════════════════════════════════════════════════════════ --}}
<section class="lp-hero">
    <div class="lp-hero-inner">

        {{-- Left: copy --}}
        <div class="lp-hero-copy">
            <div class="lp-eyebrow">Premium Car Rentals by BjCarRental · Est. 2025</div>

            <h1 class="lp-h1">
                Find Your<br>Perfect <span>Ride</span>
            </h1>

            <p class="lp-sub">
                Discover the freedom to travel on your terms. Choose from our wide range of
                well-maintained vehicles and enjoy a smooth, hassle-free booking experience.
                Affordable prices, trusted service, and flexible rental options—everything you need for a comfortable journey.
            </p>

            <div class="lp-btns">
                <a href="{{ route('client.car.index') }}" class="lp-btn-dark">
                    <i class="fa-solid fa-search"></i> Browse Fleet
                </a>
                <a href="#lp-why" class="lp-btn-outline">
                    <i class="fa-solid fa-circle-info"></i> How It Works
                </a>
            </div>
        </div>

        {{-- Right: Bootstrap Carousel (populated by JS) --}}
        <div class="lp-hero-panel">
            <div class="lp-hero-spotlight">

                {{-- Skeleton shown until JS populates the carousel --}}
                <div class="lp-hp-skeleton" id="lpCarouselSkeleton">
                    <div class="lp-hps-box"></div>
                    <div class="lp-hps-grid">
                        <div class="lp-hps-cell"></div>
                        <div class="lp-hps-cell"></div>
                    </div>
                </div>

                {{-- Carousel shell — JS fills .carousel-inner --}}
                <div id="lpHeroCarousel"
                     class="carousel slide d-none"
                     data-bs-ride="carousel">

                    <div class="carousel-inner" id="lpCarouselInner">
                        {{-- Items injected by HeroPanel.render() --}}
                    </div>

                    <button class="carousel-control-prev" type="button"
                            data-bs-target="#lpHeroCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button"
                            data-bs-target="#lpHeroCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>

                </div>

            </div>
        </div>

    </div>
</section>

{{-- ══════════════════════════════════════════════════════════════
     STATS STRIP
     ══════════════════════════════════════════════════════════════ --}}
<div class="lp-strip">
    <div class="lp-strip-inner">

        <div class="lp-stat lp-r">
            <span class="lp-stat-icon" style="color:var(--orange)">
                <i class="fa-solid fa-car"></i>
            </span>
            <span class="lp-stat-num" id="lpStatAvail">—</span>
            <span class="lp-stat-lbl">Cars Available</span>
        </div>

        <div class="lp-stat lp-r lp-rd1">
            <span class="lp-stat-icon" style="color:var(--success)">
                <i class="fa-solid fa-users"></i>
            </span>
            <span class="lp-stat-num" id="lpStatCustomers">1,000+</span>
            <span class="lp-stat-lbl">Happy Customers</span>
        </div>

        <div class="lp-stat lp-r lp-rd2">
            <span class="lp-stat-icon" style="color:#6366f1">
                <i class="fa-solid fa-shield"></i>
            </span>
            <span class="lp-stat-num">24/7</span>
            <span class="lp-stat-lbl">Customer Support</span>
        </div>

        <div class="lp-stat lp-r lp-rd3">
            <span class="lp-stat-icon" style="color:var(--warning)">
                <i class="fa-solid fa-medal"></i>
            </span>
            <span class="lp-stat-num">15 yrs</span>
            <span class="lp-stat-lbl">Industry Experience</span>
        </div>

    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     FEATURED VEHICLES
     ══════════════════════════════════════════════════════════════ --}}
<section class="lp-section lp-section-white" id="lp-featured">
    <div class="lp-inner">

        <div class="lp-sec-head lp-r">
            <div class="lp-sec-tag">Top Picks</div>
            <h2 class="lp-sec-title">Featured Vehicles</h2>
            <p class="lp-sec-sub">Our most popular cars, hand-picked for you</p>
        </div>

        <div class="lp-grid" id="lpFeatGrid">
            @for ($i = 0; $i < 4; $i++)
                <div class="lp-skel">
                    <div class="lp-skel-img"></div>
                    <div class="lp-skel-body">
                        <div class="lp-skel-line lp-sl-w"></div>
                        <div class="lp-skel-line lp-sl-m"></div>
                        <div class="lp-skel-line lp-sl-n"></div>
                    </div>
                </div>
            @endfor
        </div>

        <div class="lp-sec-footer lp-r">
            <a href="{{ route('client.car.index') }}" class="lp-btn-outline">
                View All Cars <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

    </div>
</section>

{{-- ══════════════════════════════════════════════════════════════
     CTA BAND
     ══════════════════════════════════════════════════════════════ --}}
<div class="lp-cta">
    <div class="lp-cta-inner">
        <div>
            <h2>Ready to Hit the Road?</h2>
            <p>Browse our full fleet and book in minutes. Easy, fast, and reliable.</p>
        </div>
        <a href="{{ route('client.car.index') }}" class="lp-cta-btn">
            <i class="fa-solid fa-calendar-check"></i> Book Now
        </a>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     FULL FLEET (with filters)
     ══════════════════════════════════════════════════════════════ --}}
<section class="lp-section lp-section-gray" id="lp-fleet">
    <div class="lp-inner">

        <div class="lp-sec-head lp-r">
            <div class="lp-sec-tag">Full Fleet</div>
            <h2 class="lp-sec-title">All Available Vehicles</h2>
            <p class="lp-sec-sub">Browse our complete lineup and filter by your needs</p>
        </div>

        <div class="lp-filters lp-r" role="search" aria-label="Fleet filters">

            <div class="lp-fg">
                <label class="lp-flbl" for="lpStatus">Status</label>
                <select class="lp-fsel" id="lpStatus" name="status">
                    <option value="available">Available</option>
                    <option value="rented">Rented</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="unavailable">Unavailable</option>
                </select>
            </div>

            <div class="lp-fg">
                <label class="lp-flbl" for="lpCapacity">Capacity</label>
                <select class="lp-fsel" id="lpCapacity" name="capacity">
                    <option value="">All Seats</option>
                    <option value="2">2+ Seater</option>
                    <option value="4">4+ Seater</option>
                    <option value="5">5+ Seater</option>
                    <option value="7">7+ Seater</option>
                </select>
            </div>

            <div class="lp-fg">
                <label class="lp-flbl" for="lpSort">Sort By</label>
                <select class="lp-fsel" id="lpSort" name="sort">
                    <option value="">Default</option>
                    <option value="price_asc">Price: Low → High</option>
                    <option value="price_desc">Price: High → Low</option>
                    <option value="capacity">Capacity</option>
                </select>
            </div>

            <button class="lp-fbtn" id="lpApply" type="button">
                <i class="fa-solid fa-sliders"></i> Apply
            </button>

            <button class="lp-fbtn-reset" id="lpReset" type="button">
                <i class="fa-solid fa-rotate-left"></i> Reset
            </button>

        </div>

        <div class="lp-grid" id="lpAllGrid">
            @for ($i = 0; $i < 6; $i++)
                <div class="lp-skel">
                    <div class="lp-skel-img"></div>
                    <div class="lp-skel-body">
                        <div class="lp-skel-line lp-sl-w"></div>
                        <div class="lp-skel-line lp-sl-m"></div>
                        <div class="lp-skel-line lp-sl-n"></div>
                    </div>
                </div>
            @endfor
        </div>

    </div>
</section>

{{-- ══════════════════════════════════════════════════════════════
     WHY CHOOSE US
     ══════════════════════════════════════════════════════════════ --}}
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

{{-- ══════════════════════════════════════════════════════════════
     FOOTER
     ══════════════════════════════════════════════════════════════ --}}
<footer class="lp-foot">
    <div class="lp-foot-inner">

        <div class="lp-foot-brand">
            <h3><span>Bj</span>Car Rental</h3>
            <p>Premium car rentals made simple. Reliable vehicles, competitive pricing,
               and a fleet for every journey.</p>
            <div class="lp-foot-socials">
                <a href="https://www.facebook.com/jhong.daigan"
                   class="lp-soc"
                   target="_blank"
                   rel="noopener noreferrer"
                   aria-label="BjCarRental on Facebook">
                    <i class="fa-brands fa-facebook-f"></i>
                </a>
            </div>
        </div>

        <div class="lp-foot-cols-mobile">

            <div class="lp-foot-col">
                <h4>Quick Links</h4>
                <ul>
                    <li>
                        <a href="{{ route('client.car.index') }}">
                            <i class="fa-solid fa-chevron-right"></i> Browse Fleet
                        </a>
                    </li>
                    <li>
                        <a href="#lp-why">
                            <i class="fa-solid fa-chevron-right"></i> How It Works
                        </a>
                    </li>
                    <li>
                        <a href="#lp-featured">
                            <i class="fa-solid fa-chevron-right"></i> Featured Cars
                        </a>
                    </li>
                    <li>
                        <a href="#lp-foot-contact">
                            <i class="fa-solid fa-chevron-right"></i> Contact
                        </a>
                    </li>
                </ul>
            </div>

            <div class="lp-foot-col" id="lp-foot-contact">
                <h4>Contact</h4>
                <ul>
                    <li>
                        <a href="tel:+639518330354">
                            <i class="fa-solid fa-phone"></i> 09518330354
                        </a>
                    </li>
                    <li>
                        <a href="mailto:bg@gmail.com">
                            <i class="fa-solid fa-envelope"></i> bg@gmail.com
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fa-solid fa-location-dot"></i>
                            Hubang, San Francisco, Agusan del Sur
                        </a>
                    </li>
                    <li>
                        <a href="https://www.facebook.com/jhong.daigan"
                           target="_blank"
                           rel="noopener noreferrer">
                            <i class="fa-brands fa-facebook"></i> BJ Daigan
                        </a>
                    </li>
                </ul>
            </div>

        </div>

    </div>

    <div class="lp-foot-bottom">
        <span>© {{ date('Y') }} BJ Car Rental. All rights reserved.</span>
    </div>
</footer>

<script>
    window.LP_CONFIG = {
        carsUrl:  "{{ route('public.cars.index') }}",
        statsUrl: "{{ route('public.cars.stats') }}",
        carUrl:   "{{ route('client.car.index') }}",
    };
</script>
<script defer src="{{ asset('assets/js/home.js') }}"></script>

</x-app-layout>