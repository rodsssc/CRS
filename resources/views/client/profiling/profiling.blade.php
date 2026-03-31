<x-app-layout>
<div class="verification-container">
<div class="container">
<div class="row justify-content-center">
<div class="col-12 col-lg-10 col-xl-8">

    {{-- ── Page Title ──────────────────────────────────────── --}}
    <h1 class="vf-page-title">Identity Verification</h1>
    <p class="vf-page-sub">Complete both steps to unlock full access to the platform</p>

    {{-- ── Stepper ─────────────────────────────────────────── --}}
    <div class="vf-stepper">
        <div class="vf-step is-active">
            <div class="vf-step-num">1</div>
            <span class="vf-step-label">Client Profiling</span>
        </div>
        <div class="vf-step-connector">
            <div class="vf-step-connector-fill"></div>
        </div>
        <div class="vf-step">
            <div class="vf-step-num">2</div>
            <span class="vf-step-label">ID Verification</span>
        </div>
    </div>

    {{-- ── Progress Bar ────────────────────────────────────── --}}
    <div class="vf-progress-bar-wrap">
        <div class="vf-progress-bar" style="width: 50%"></div>
    </div>

    {{-- ── Main Card ───────────────────────────────────────── --}}
    <div class="vf-card">
        <div class="vf-card-body">

            <div class="vf-section-head">
                <div class="vf-section-icon vf-section-icon-blue">
                    <i class="bi bi-person-circle"></i>
                </div>
                <div>
                    <h5 class="vf-section-title">Client Information</h5>
                    <p class="vf-section-sub">Please provide your basic personal information</p>
                </div>
            </div>

            <form id="profilingForm" method="POST" action="{{ route('client.profile.store') }}" novalidate>
                @csrf
                <input type="hidden" name="client_id" value="{{ $client->id }}">

                {{-- Name row --}}
                <div class="vf-row vf-form-group">
                    <div>
                        <label class="vf-label" for="firstName">First Name <span class="req">*</span></label>
                        <div class="vf-input-wrap">
                            <span class="vf-input-icon"><i class="bi bi-person"></i></span>
                            <input type="text" class="vf-input" id="firstName" name="firstName"
                                   required placeholder="Enter first name"
                                   value="{{ old('firstName', optional($client->clientProfile)->first_name) }}">
                        </div>
                        <div class="invalid-feedback">Please enter your first name.</div>
                    </div>
                    <div>
                        <label class="vf-label" for="lastName">Last Name <span class="req">*</span></label>
                        <div class="vf-input-wrap">
                            <span class="vf-input-icon"><i class="bi bi-person"></i></span>
                            <input type="text" class="vf-input" id="lastName" name="lastName"
                                   required placeholder="Enter last name"
                                   value="{{ old('lastName', optional($client->clientProfile)->last_name) }}">
                        </div>
                        <div class="invalid-feedback">Please enter your last name.</div>
                    </div>
                </div>

                {{-- DOB + Nationality --}}
                <div class="vf-row vf-form-group">
                    <div>
                        <label class="vf-label" for="dateBirth">Date of Birth <span class="req">*</span></label>
                        <div class="vf-input-wrap">
                            <span class="vf-input-icon"><i class="bi bi-calendar"></i></span>
                            <input type="date" class="vf-input" id="dateBirth" name="dateBirth"
                                   required
                                   value="{{ old('dateBirth', optional($client->clientProfile)->date_birth) }}">
                        </div>
                        <div class="invalid-feedback">Please select your date of birth.</div>
                    </div>
                    <div>
                        <label class="vf-label" for="nationality">Nationality <span class="req">*</span></label>
                        <div class="vf-input-wrap">
                            <span class="vf-input-icon"><i class="bi bi-globe"></i></span>
                            <input type="text" class="vf-input" id="nationality" name="nationality"
                                   required placeholder="e.g., Filipino"
                                   value="{{ old('nationality', optional($client->clientProfile)->nationality) }}">
                        </div>
                        <div class="invalid-feedback">Please enter your nationality.</div>
                    </div>
                </div>

                {{-- Address --}}
                <div class="vf-form-group">
                    <label class="vf-label" for="address">Address <span class="req">*</span></label>
                    <div class="vf-input-wrap" style="align-items: flex-start">
                        <span class="vf-input-icon" style="padding-top: 0.625rem"><i class="bi bi-house"></i></span>
                        <textarea class="vf-textarea" id="address" name="address"
                                  required placeholder="Enter your complete address">{{ old('address', optional($client->clientProfile)->address) }}</textarea>
                    </div>
                    <div class="invalid-feedback">Please enter your address.</div>
                </div>

                {{-- Facebook --}}
                <div class="vf-form-group">
                    <label class="vf-label" for="facebook_name">Facebook Name</label>
                    <div class="vf-input-wrap">
                        <span class="vf-input-icon"><i class="bi bi-facebook"></i></span>
                        <input type="text" class="vf-input" id="facebook_name" name="facebook_name"
                               placeholder="Your Facebook profile name"
                               value="{{ old('facebook_name', optional($client->clientProfile)->facebook_name) }}">
                    </div>
                </div>

                {{-- Emergency contact alert --}}
                <div class="vf-alert vf-alert-info">
                    <i class="bi bi-shield-exclamation vf-alert-icon"></i>
                    <div>
                        <strong>Emergency Contact</strong>
                        <p class="mb-0" style="margin-top: .15rem">Please provide someone we can contact in case of emergency.</p>
                    </div>
                </div>

                {{-- Emergency contact --}}
                <div class="vf-row vf-form-group">
                    <div>
                        <label class="vf-label" for="emergencyContactName">Contact Name <span class="req">*</span></label>
                        <div class="vf-input-wrap">
                            <span class="vf-input-icon"><i class="bi bi-person-lines-fill"></i></span>
                            <input type="text" class="vf-input" id="emergencyContactName"
                                   name="emergencyContactName" required placeholder="Full name"
                                   value="{{ old('emergencyContactName', optional($client->clientProfile)->emergency_contact_name) }}">
                        </div>
                        <div class="invalid-feedback">Please enter emergency contact name.</div>
                    </div>
                    <div>
                        <label class="vf-label" for="emergencyContactPhone">Contact Phone <span class="req">*</span></label>
                        <div class="vf-input-wrap">
                            <span class="vf-input-icon"><i class="bi bi-telephone"></i></span>
                            <input type="tel" class="vf-input" id="emergencyContactPhone"
                                   name="emergencyContactPhone" required placeholder="+63 XXX XXX XXXX"
                                   value="{{ old('emergencyContactPhone', optional($client->clientProfile)->emergency_contact_phone) }}">
                        </div>
                        <div class="invalid-feedback">Please enter a valid phone number.</div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="vf-actions">
                    @if ($client->clientProfile)
                        <button type="submit" class="vf-btn vf-btn-success">
                            <i class="bi bi-check-circle"></i> Update & Continue
                        </button>
                    @else
                        <button type="submit" class="vf-btn vf-btn-primary">
                            <i class="bi bi-arrow-right-circle"></i> Continue to Verification
                        </button>
                    @endif
                </div>
            </form>

        </div>
    </div>

</div>
</div>
</div>
</div>

{{-- Load SweetAlert2 FIRST --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



<script src="{{ asset('assets/js/client/profiling/profiling.js') }}"></script>

</x-app-layout>
