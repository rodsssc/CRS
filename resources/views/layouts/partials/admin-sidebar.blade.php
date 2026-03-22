<!-- Admin Sidebar for Admin/Staff/Owner -->
<aside class="sidebar-container" id="sidebar" data-collapsed="false">
    <!-- Sidebar Brand -->
    <div class="sidebar-brand text-center py-2">
        <img src="{{ asset('assets/images/logo.png') }}"
             alt="Logo"
             style="height: 70px; border-radius:40px">
    </div>

    <div class="sidebar-nav-wrapper">
        <!-- Navigation Sections -->
        <div class="sidebar-section">
            <h6 class="sidebar-section-title">Main Menu</h6>
            <ul class="sidebar-nav">

                <li class="sidebar-nav-item">
                    <a href="{{ route('admin.dashboard') }}" title="Dashboard"
                       class="nav-link-minimal {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                </li>

                @if(Auth::user()->role === 'admin')
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.user.index') }}" title="User Management"
                           class="nav-link-minimal {{ request()->routeIs('admin.user.*') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <span>User Management</span>
                        </a>
                    </li>
                @endif

                @if(Auth::user()->role === 'admin' || Auth::user()->role === 'staff')
                    <li class="sidebar-nav-item">
                        <a href="{{ route('admin.verification.index') }}" title="Verification"
                           class="nav-link-minimal {{ request()->routeIs('admin.verification.*') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                            <span>Verification</span>
                            <span class="sb-badge" id="sbVerificationBadge" style="display:none"></span>
                        </a>
                    </li>
                @endif

                <li class="sidebar-nav-item">
                    <a href="{{ route('admin.cars.index') }}" title="Cars"
                       class="nav-link-minimal {{ request()->routeIs('admin.cars.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 17H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-1"></path>
                            <polygon points="12 15 17 21 7 21 12 15"></polygon>
                        </svg>
                        <span>Cars</span>
                    </a>
                </li>

                {{-- ── Bookings with pending badge ── --}}
                <li class="sidebar-nav-item">
                    <a href="{{ route('admin.bookings.index') }}" title="Bookings"
                       class="nav-link-minimal {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span>Bookings</span>
                        {{-- Badge — hidden until JS populates it --}}
                        <span class="sb-badge" id="sbPendingBadge" style="display:none"></span>
                    </a>
                </li>

                <li class="sidebar-nav-item">
                    <a href="{{ route('admin.maintenance.index') }}" title="Maintenance"
                       class="nav-link-minimal {{ request()->routeIs('admin.maintenance.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                        </svg>
                        <span>Maintenance</span>
                    </a>
                </li>

                <li class="sidebar-nav-item">
                    <a href="{{ route('admin.payment.index') }}" title="Payments"
                       class="nav-link-minimal {{ request()->routeIs('admin.payment.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                            <line x1="1" y1="10" x2="23" y2="10"></line>
                        </svg>
                        <span>Payments</span>
                    </a>
                </li>

            </ul>
        </div>
    </div>

    <div class="sidebar-section">
        <h6 class="sidebar-section-title">Analytics</h6>
        <ul class="sidebar-nav">
            <li class="sidebar-nav-item">
                <a href="{{ route('admin.reports.sales') }}" title="Sales & Reports"
                   class="nav-link-minimal {{ request()->routeIs('admin.reports.sales') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="20" x2="18" y2="10"></line>
                        <line x1="12" y1="20" x2="12" y2="4"></line>
                        <line x1="6" y1="20" x2="6" y2="14"></line>
                    </svg>
                    <span>Sales & Reports</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- User Profile Footer -->
    <div class="sidebar-footer">
        <div class="user-profile dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" role="button">
            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&color=ffffff&background=3b82f6"
                 alt="User Avatar" class="user-avatar">
            <div class="user-info">
                <div class="user-name">{{ Auth::user()->name }}</div>
                <div class="user-email">{{ Auth::user()->email }}</div>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="opacity:0.5">
                <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/>
            </svg>
        </div>

        <ul class="dropdown-menu dropdown-menu-dark">
            <li><a class="dropdown-item" href="#">Profile Settings</a></li>
            <li><a class="dropdown-item" href="#">Preferences</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                    @csrf
                    <button type="submit" class="dropdown-item">Log Out</button>
                </form>
            </li>
        </ul>
    </div>
</aside>

<!-- Mobile Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Mobile Toggle Button -->
<button class="mobile-menu-toggle" id="sidebarToggle" aria-label="Toggle sidebar menu">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
    </svg>
</button>

<!-- Desktop Sidebar Collapse Toggle -->
<button class="sidebar-collapse-toggle d-none d-lg-flex" id="sidebarCollapseToggle" aria-label="Collapse sidebar">
    <svg class="collapse-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
    </svg>
</button>

{{-- ── Pending badges script (bookings + verification) ── --}}
<script>
(function () {
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    var headers = {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken,
    };

    function setBadge(id, count) {
        var el = document.getElementById(id);
        if (!el) return;
        if (count > 0) {
            el.textContent    = count > 99 ? '99+' : count;
            el.style.display  = 'inline-flex';
        } else {
            el.style.display  = 'none';
        }
    }

    function updateBookings() {
        fetch('{{ route("admin.bookings.pending") }}', { headers: headers })
            .then(function (res) { return res.ok ? res.json() : Promise.reject(); })
            .then(function (data) { setBadge('sbPendingBadge', parseInt(data.count || 0, 10)); })
            .catch(function () { setBadge('sbPendingBadge', 0); });
    }

    function updateVerification() {
        fetch('{{ route("admin.verification.pending") }}', { headers: headers })
            .then(function (res) { return res.ok ? res.json() : Promise.reject(); })
            .then(function (data) { setBadge('sbVerificationBadge', parseInt(data.count || 0, 10)); })
            .catch(function () { setBadge('sbVerificationBadge', 0); });
    }

    function updateAll() {
        updateBookings();
        updateVerification();
    }

    // Run on load then refresh every 60 seconds
    updateAll();
    setInterval(updateAll, 60000);
})();
</script>