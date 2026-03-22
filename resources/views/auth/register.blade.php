<x-guest-layout>
    <div class="form-container register-container">

        <!-- Logo overlapping top of card -->
        <div class="card-logo">
            <img
                src="{{ asset('assets/images/logo.png') }}"
                alt="{{ config('app.name') }}"
                class="card-logo-image"
            >
        </div>

        <div class="form-header">
            <p class="form-subtitle">Fill in the details to get started</p>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Name + Phone (2 columns) -->
            <div class="form-row">
                <div class="form-group">
                    <label for="name" class="form-label">Full Name</label>
                    <input
                        id="name"
                        class="input {{ $errors->has('name') ? 'input-error' : '' }}"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        required autofocus
                        autocomplete="name"
                        placeholder="Juan Dela Cruz"
                    >
                    @if ($errors->has('name'))
                        <span class="error-message">{{ $errors->first('name') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Phone</label>
                    <div class="input-wrapper">
                        <span class="input-prefix">+63</span>
                        <input
                            id="phone"
                            class="input input-with-prefix {{ $errors->has('phone') ? 'input-error' : '' }}"
                            type="tel"
                            name="phone"
                            value="{{ old('phone') }}"
                            required
                            autocomplete="tel"
                            placeholder="9XX XXX XXXX"
                            maxlength="10"
                            pattern="[0-9]{10}"
                        >
                    </div>
                    @if ($errors->has('phone'))
                        <span class="error-message">{{ $errors->first('phone') }}</span>
                    @endif
                </div>
            </div>

            <!-- Email (full width) -->
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input
                    id="email"
                    class="input {{ $errors->has('email') ? 'input-error' : '' }}"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="username"
                    placeholder="you@example.com"
                >
                @if ($errors->has('email'))
                    <span class="error-message">{{ $errors->first('email') }}</span>
                @endif
            </div>

            <!-- Password + Confirm (2 columns) -->
            <div class="form-row">
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-wrapper">
                        <input
                            id="password"
                            class="input {{ $errors->has('password') ? 'input-error' : '' }}"
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            placeholder="••••••••"
                        >
                        <span class="toggle-password" onclick="togglePassword('password', this)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="eye-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </span>
                    </div>
                    @if ($errors->has('password'))
                        <span class="error-message">{{ $errors->first('password') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirm</label>
                    <div class="input-wrapper">
                        <input
                            id="password_confirmation"
                            class="input {{ $errors->has('password_confirmation') ? 'input-error' : '' }}"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="••••••••"
                        >
                        <span class="toggle-password" onclick="togglePassword('password_confirmation', this)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="eye-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </span>
                    </div>
                    @if ($errors->has('password_confirmation'))
                        <span class="error-message">{{ $errors->first('password_confirmation') }}</span>
                    @endif
                </div>
            </div>

            <button type="submit" class="login-btn">Create Account</button>

            <p class="register-link">
                Already have an account? <a href="{{ route('login') }}">Sign in</a>
            </p>
        </form>
    </div>
</x-guest-layout>