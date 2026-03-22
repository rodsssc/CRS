<nav class="client-nav" id="clientNav">
    <div class="cn-inner">

        @php
            /** @var \App\Models\User|null $user */
            $user = Auth::user();
            $isVerified = $user?->hasVerifiedIdentity() ?? false;
            $isPending  = $user?->hasPendingVerification() ?? false;
            $onHomeLanding = request()->routeIs('home') || request()->routeIs('client.home');
        @endphp

        {{-- Left: Hamburger + Logo --}}
        <div class="cn-left">
            <button class="cn-hamburger" id="cnToggle" aria-label="Toggle navigation" aria-expanded="false">
                <span class="cn-ham-line"></span>
                <span class="cn-ham-line"></span>
                <span class="cn-ham-line"></span>
            </button>

            <a href="{{ route('home') }}" class="cn-brand" aria-label="Home">
                <x-application-logo class="cn-logo" />
            </a>
        </div>

        {{-- Desktop Nav Links --}}
        <div class="cn-links-desktop">
            @if($onHomeLanding)
                <a href="#lp-featured" class="cn-link">Featured</a>
                <a href="#lp-fleet" class="cn-link">Fleet</a>
                <a href="#lp-why" class="cn-link">Why Us</a>
                <a href="#lp-foot-contact" class="cn-link">Contact</a>
            @endif

            @auth
                @if($isVerified)
                    <a href="{{ route('home') }}" class="cn-link {{ request()->routeIs('home') ? 'cn-link--active' : '' }}">Home</a>
                    <a href="{{ route('client.bookings.index') }}" class="cn-link {{ request()->routeIs('client.bookings.*') ? 'cn-link--active' : '' }}">My Bookings</a>
                    <a href="{{ route('client.car.index') }}" class="cn-link {{ request()->routeIs('client.car.*') ? 'cn-link--active' : '' }}">Cars</a>
                @else
                    <span class="cn-link cn-link--locked" title="Complete verification to access">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        My Bookings
                    </span>
                    <span class="cn-link cn-link--locked" title="Complete verification to access">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Cars
                    </span>
                @endif
            @endauth
        </div>

        {{-- Right: Status + User --}}
        <div class="cn-right">
            @auth
                {{-- Verification Status Badge --}}
                @if($isPending)
                    <span class="cn-badge cn-badge--warning">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L1 21h22L12 2zm0 3.5L20.5 19h-17L12 5.5zM11 10v4h2v-4h-2zm0 6v2h2v-2h-2z"/></svg>
                        <span class="cn-badge-label">Pending</span>
                    </span>
                @elseif(!$isVerified)
                    <a href="{{ route('client.verification.index') }}" class="cn-badge cn-badge--warning cn-badge--link">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L1 21h22L12 2zm0 3.5L20.5 19h-17L12 5.5zM11 10v4h2v-4h-2zm0 6v2h2v-2h-2z"/></svg>
                        <span class="cn-badge-label">Verify</span>
                    </a>
                @else
                    <span class="cn-badge cn-badge--success">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg>
                        <span class="cn-badge-label">Verified</span>
                    </span>
                @endif

                {{-- User Avatar Dropdown --}}
                <div class="cn-user-wrap" id="cnUserWrap">
                    <button class="cn-avatar-btn" id="cnUserToggle" aria-expanded="false" aria-label="User menu">
                        <div class="cn-avatar">{{ substr($user->name, 0, 1) }}</div>
                        <span class="cn-username">{{ explode(' ', $user->name)[0] }}</span>
                        <svg class="cn-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
                    </button>

                    <div class="cn-dropdown" id="cnDropdown">
                        <div class="cn-dropdown-header">
                            <div class="cn-dropdown-avatar">{{ substr($user->name, 0, 1) }}</div>
                            <div>
                                <p class="cn-dropdown-name">{{ $user->name }}</p>
                                <p class="cn-dropdown-email">{{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="cn-dropdown-divider"></div>

                        @if($isPending)
                            <span class="cn-dropdown-item cn-dropdown-item--muted">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                Waiting for Approval
                            </span>
                        @elseif(!$isVerified)
                            <a class="cn-dropdown-item cn-dropdown-item--warning" href="{{ route('client.verification.index') }}">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                Complete Verification
                            </a>
                        @else
                            <a class="cn-dropdown-item" href="{{ route('client.bookings.index') }}">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                My Bookings
                            </a>
                            <a class="cn-dropdown-item" href="{{ route('client.car.index') }}">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 17H3a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h1l2-4h10l2 4h1a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2h-2"/><circle cx="7.5" cy="17" r="1.5"/><circle cx="16.5" cy="17" r="1.5"/></svg>
                                Available Cars
                            </a>
                        @endif

                        <div class="cn-dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="cn-dropdown-item cn-dropdown-item--danger">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>

            @else
                <a href="{{ route('login') }}" class="cn-btn cn-btn--ghost">Login</a>
                <a href="{{ route('register') }}" class="cn-btn cn-btn--solid">Register</a>
            @endauth
        </div>
    </div>

    {{-- Mobile Drawer --}}
    <div class="cn-drawer" id="cnDrawer" aria-hidden="true">
        <div class="cn-drawer-inner">
            @if($onHomeLanding)
                <p class="cn-drawer-section-label">Explore</p>
                <a href="#lp-featured" class="cn-drawer-link" data-close-drawer>Featured</a>
                <a href="#lp-fleet" class="cn-drawer-link" data-close-drawer>Fleet</a>
                <a href="#lp-why" class="cn-drawer-link" data-close-drawer>Why Us</a>
                <a href="#lp-foot-contact" class="cn-drawer-link" data-close-drawer>Contact</a>
            @endif

            @auth
                <p class="cn-drawer-section-label">Navigation</p>
                @if($isVerified)
                    <a href="{{ route('home') }}" class="cn-drawer-link {{ request()->routeIs('home') ? 'cn-drawer-link--active' : '' }}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/></svg>
                        Home
                    </a>
                    <a href="{{ route('client.bookings.index') }}" class="cn-drawer-link {{ request()->routeIs('client.bookings.*') ? 'cn-drawer-link--active' : '' }}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                        My Bookings
                    </a>
                    <a href="{{ route('client.car.index') }}" class="cn-drawer-link {{ request()->routeIs('client.car.*') ? 'cn-drawer-link--active' : '' }}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 17H3a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h1l2-4h10l2 4h1a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2h-2"/><circle cx="7.5" cy="17" r="1.5"/><circle cx="16.5" cy="17" r="1.5"/></svg>
                        Available Cars
                    </a>
                @else
                    <a href="{{ route('client.verification.index') }}" class="cn-drawer-link cn-drawer-link--cta">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        Complete Verification
                    </a>
                @endif

                <div class="cn-drawer-divider"></div>
                <p class="cn-drawer-section-label">Account</p>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="cn-drawer-link cn-drawer-link--danger">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                        Log Out
                    </button>
                </form>
            @else
                <p class="cn-drawer-section-label">Get Started</p>
                <a href="{{ route('login') }}" class="cn-drawer-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
                    Login
                </a>
                <a href="{{ route('register') }}" class="cn-drawer-link cn-drawer-link--cta">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM19 8v6M22 11h-6"/></svg>
                    Create Account
                </a>
            @endauth
        </div>
    </div>

    {{-- Backdrop --}}
    <div class="cn-backdrop" id="cnBackdrop"></div>
</nav>

<script>
(function () {
    const toggle   = document.getElementById('cnToggle');
    const drawer   = document.getElementById('cnDrawer');
    const backdrop = document.getElementById('cnBackdrop');
    const userToggle = document.getElementById('cnUserToggle');
    const dropdown   = document.getElementById('cnDropdown');
    const userWrap   = document.getElementById('cnUserWrap');

    function openDrawer() {
        toggle.classList.add('is-open');
        toggle.setAttribute('aria-expanded', 'true');
        drawer.classList.add('is-open');
        drawer.setAttribute('aria-hidden', 'false');
        backdrop.classList.add('is-visible');
        document.body.style.overflow = 'hidden';
    }

    function closeDrawer() {
        toggle.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        drawer.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
        backdrop.classList.remove('is-visible');
        document.body.style.overflow = '';
    }

    if (toggle && drawer) {
        toggle.addEventListener('click', () => {
            drawer.classList.contains('is-open') ? closeDrawer() : openDrawer();
        });
        backdrop.addEventListener('click', closeDrawer);
        document.querySelectorAll('[data-close-drawer]').forEach(el => {
            el.addEventListener('click', closeDrawer);
        });
    }

    if (userToggle && dropdown) {
        userToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = dropdown.classList.contains('is-open');
            dropdown.classList.toggle('is-open', !isOpen);
            userToggle.setAttribute('aria-expanded', String(!isOpen));
        });
        document.addEventListener('click', (e) => {
            if (!userWrap.contains(e.target)) {
                dropdown.classList.remove('is-open');
                userToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // Scroll shadow
    const nav = document.getElementById('clientNav');
    window.addEventListener('scroll', () => {
        nav.classList.toggle('is-scrolled', window.scrollY > 8);
    }, { passive: true });
})();
</script>