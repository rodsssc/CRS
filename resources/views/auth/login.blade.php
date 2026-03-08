<x-guest-layout>
    <div class="form-container">

        <!-- Logo overlapping top of card -->
        <div class="card-logo">
            <x-application-logo class="card-logo-image"  />
        </div>

        <div class="form-header">
           
            <p class="form-subtitle">Sign in to your account</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Email -->
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input
                    id="email"
                    class="input {{ $errors->has('email') ? 'input-error' : '' }}"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required autofocus
                    autocomplete="username"
                    placeholder="you@example.com"
                >
                @if ($errors->has('email'))
                    <span class="error-message">{{ $errors->first('email') }}</span>
                @endif
            </div>

            <!-- Password -->
            <div class="form-group">
                <div class="label-row">
                    <label for="password" class="form-label">Password</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                    @endif
                </div>
                <input
                    id="password"
                    class="input {{ $errors->has('password') ? 'input-error' : '' }}"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                >
                @if ($errors->has('password'))
                    <span class="error-message">{{ $errors->first('password') }}</span>
                @endif
            </div>

            <!-- Remember Me -->
            <div class="form-group remember-row">
                <label for="remember_me" class="checkbox-label">
                    <input id="remember_me" type="checkbox" name="remember" class="checkbox">
                    <span>Remember me</span>
                </label>
            </div>

            <button type="submit" class="login-btn">Sign In</button>
            <p class="register-link">
                Don't have an account? <a href="{{ route('register') }}">Create Account</a>
            </p>
        </form>
    </div>
</x-guest-layout>