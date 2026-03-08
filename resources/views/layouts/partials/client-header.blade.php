<nav class="client-nav shadow-sm">
    <div class="container h-100 d-flex align-items-center justify-content-between">

        @php
            /** @var \App\Models\User|null $user */
            $user = Auth::user();
            $isVerified = $user?->hasVerifiedIdentity() ?? false;
            $isPending  = $user?->hasPendingVerification() ?? false;
            // Treat both "/" (home) and "/client/home" as landing for section links
            $onHomeLanding = request()->routeIs('home') || request()->routeIs('client.home');
        @endphp

        <!-- Brand + Primary Nav -->
        <div class="d-flex align-items-center gap-4">
            <x-application-logo class="client-header-logo"/>

            <!-- Nav Links (desktop) -->
            <div class="d-none d-md-flex gap-3">

                <!-- Home Page Section Links -->
                @if($onHomeLanding)
                    <a href="#lp-featured"
                       class="text-secondary text-decoration-none small hover-dark">Featured
                    </a>
                    <a href="#lp-fleet"
                       class="text-secondary text-decoration-none small hover-dark">Fleet
                    </a>
                    <a href="#lp-why"
                       class="text-secondary text-decoration-none small hover-dark">Why Us
                    </a>
                    <a href="#lp-foot-contact"
                       class="text-secondary text-decoration-none small hover-dark">Contact
                    </a>
                @endif

                @auth
                    @if($isVerified)
                        <a href="{{ route('home') }}"
                           class="text-secondary text-decoration-none small hover-dark {{ request()->routeIs('home') ? 'fw-bold text-dark' : '' }}">
                            Home
                        </a>
                        <a href="{{ route('client.bookings.index') }}"
                           class="text-secondary text-decoration-none small hover-dark {{ request()->routeIs('client.bookings.*') ? 'fw-bold text-dark' : '' }}">
                            My Bookings
                        </a>
                        <a href="{{ route('client.car.index') }}"
                           class="text-secondary text-decoration-none small hover-dark {{ request()->routeIs('client.car.*') ? 'fw-bold text-dark' : '' }}">
                            Cars
                        </a>
                    @else
                        <span class="text-muted small" style="cursor: not-allowed;" title="Complete verification to access">
                            <i class="bi bi-lock-fill me-1"></i>My Bookings
                        </span>
                        <span class="text-muted small" style="cursor: not-allowed;" title="Complete verification to access">
                            <i class="bi bi-lock-fill me-1"></i>Available Cars
                        </span>
                    @endif
                @endauth
                {{-- Guests see nothing here --}}

            </div>
        </div>

        <!-- Right Side (user state) -->
        <div class="d-flex align-items-center gap-3">

            @auth
                <!-- Verification Badge -->
                @if($isPending)
                    <span class="badge bg-warning text-dark small">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>Waiting for Approval
                    </span>
                @elseif(!$isVerified)
                    <span class="badge bg-warning text-dark small">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>Verification Required
                    </span>
                @else
                    <span class="badge bg-success small">
                        <i class="bi bi-check-circle-fill me-1"></i>Verified
                    </span>
                @endif

                <!-- User Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-light d-flex align-items-center gap-2"
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                             style="width: 28px; height: 28px; font-size: 0.75rem;">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <span class="d-none d-sm-inline small">{{ $user->name }}</span>
                        <i class="bi bi-chevron-down" style="font-size: 0.75rem;"></i>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end">
                        @if($isPending)
                            <li>
                                <span class="dropdown-item text-warning">
                                    <i class="bi bi-shield-check me-2"></i>Waiting for Approval
                                </span>
                            </li>
                        @elseif(!$isVerified)
                            <li>
                                <a class="dropdown-item text-warning" href="{{ route('client.verification.index') }}">
                                    <i class="bi bi-shield-check me-2"></i>Complete Verification
                                </a>
                            </li>
                        @else
                            <li>
                                <a class="dropdown-item" href="{{ route('client.bookings.index') }}">
                                    <i class="bi bi-calendar-check me-2"></i>My Bookings
                                </a>
                            </li>
                        @endif

                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Log Out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>

            @else
                <!-- Guest: Login + Register only -->
                <a href="{{ route('login') }}" class="btn btn-sm btn-outline-dark">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Login
                </a>
                <a href="{{ route('register') }}" class="btn btn-sm btn-dark">
                    <i class="bi bi-person-plus me-1"></i>Register
                </a>
            @endauth

        </div>
    </div>
</nav>