<x-app-layout>
<div class="verification-container">
<div class="container">
<div class="row justify-content-center">
<div class="col-12 col-lg-10 col-xl-8">

    {{-- ── Page Title ─────────────────────────────────────── --}}
    <h1 class="vf-page-title">Identity Verification</h1>
    <p class="vf-page-sub">Complete both steps to unlock full access to the platform</p>

    {{-- ── Stepper ──────────────────────────────────────────── --}}
    <div class="vf-stepper">
        <div class="vf-step is-active" id="step1-indicator">
            <div class="vf-step-num" id="step1-num">1</div>
            <span class="vf-step-label">Client Profiling</span>
        </div>
        <div class="vf-step-connector">
            <div class="vf-step-connector-fill" id="stepConnectorFill"></div>
        </div>
        <div class="vf-step" id="step2-indicator">
            <div class="vf-step-num">2</div>
            <span class="vf-step-label">ID Verification</span>
        </div>
    </div>

    {{-- ── Progress Bar ─────────────────────────────────────── --}}
    <div class="vf-progress-bar-wrap">
        <div class="vf-progress-bar" id="formProgress" style="width:50%"></div>
    </div>

    {{-- ── Main Card ────────────────────────────────────────── --}}
    <div class="vf-card">
        <div class="vf-card-body">
            <div class="tab-content" id="clientTabsContent">

                {{-- ══════════════════════════════════════════
                     STEP 1 · PROFILING
                ══════════════════════════════════════════ --}}
                <div class="tab-pane fade show active" id="profiling" role="tabpanel">

                    {{-- Section header --}}
                    <div class="vf-section-head">
                        <div class="vf-section-icon vf-section-icon-blue">
                            <i class="bi bi-person-circle"></i>
                        </div>
                        <div>
                            <h5 class="vf-section-title">Client Information</h5>
                            <p class="vf-section-sub">Please provide your basic personal information</p>
                        </div>
                    </div>

                    <form id="profilingForm" method="POST" novalidate>
                        <input type="hidden" id="client_id" name="client_id" value="{{ $client->id }}">
                        @csrf

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
                            <div class="vf-input-wrap" style="align-items:flex-start">
                                <span class="vf-input-icon" style="padding-top:0.625rem"><i class="bi bi-house"></i></span>
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
                                <p class="mb-0" style="margin-top:.15rem">Please provide someone we can contact in case of emergency.</p>
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
                                           pattern="[0-9+\s\-()]+">
                                </div>
                                <div class="invalid-feedback">Please enter a valid phone number.</div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="vf-actions">
                            <button type="button" id="saveClientProfiling" class="vf-btn vf-btn-primary">
                                <i class="bi bi-arrow-right-circle"></i> Continue to Verification
                            </button>
                            <button type="button" id="updateClientProfiling" class="vf-btn vf-btn-success d-none">
                                <i class="bi bi-check-circle"></i> Update & Continue
                            </button>
                        </div>
                    </form>
                </div>

                {{-- ══════════════════════════════════════════
                     STEP 2 · ID VERIFICATION
                ══════════════════════════════════════════ --}}
                <div class="tab-pane fade" id="verification" role="tabpanel">

                    {{-- Section header --}}
                    <div class="vf-section-head">
                        <div class="vf-section-icon vf-section-icon-green">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div>
                            <h5 class="vf-section-title">ID Verification</h5>
                            <p class="vf-section-sub">Upload your identification documents to complete setup</p>
                        </div>
                    </div>

                    <form id="verificationForm" novalidate enctype="multipart/form-data">
                        @csrf

                        {{-- Tips --}}
                        <div class="vf-alert vf-alert-info">
                            <i class="bi bi-lightbulb-fill vf-alert-icon"></i>
                            <div>
                                <strong>Photo Tips</strong>
                                <ul>
                                    <li>Clear, bright, and in focus — all four corners visible</li>
                                    <li>For selfie: hold ID next to your face</li>
                                    <li>Accepted: JPG, PNG · Max 5 MB per file</li>
                                </ul>
                            </div>
                        </div>

                        {{-- ID info --}}
                        <p class="vf-sub-heading"><i class="bi bi-card-checklist"></i> ID Information</p>

                        <div class="vf-row vf-form-group">
                            <div>
                                <label class="vf-label" for="id_type">ID Type <span class="req">*</span></label>
                                <div class="vf-input-wrap vf-select-wrap">
                                    <span class="vf-input-icon"><i class="bi bi-card-list"></i></span>
                                    <select class="vf-select" id="id_type" name="id_type" required>
                                        <option value="">Choose ID type…</option>
                                        <optgroup label="Government-Issued IDs">
                                            <option value="passport">Passport</option>
                                            <option value="national_id">National ID (PhilSys)</option>
                                            <option value="drivers_license">Driver's License</option>
                                            <option value="voters_id">Voter's ID</option>
                                        </optgroup>
                                        <optgroup label="Other Valid IDs">
                                            <option value="sss">SSS ID</option>
                                            <option value="umid">UMID</option>
                                            <option value="philhealth">PhilHealth ID</option>
                                            <option value="postal_id">Postal ID</option>
                                            <option value="prc_id">PRC ID</option>
                                            <option value="tin_id">TIN ID</option>
                                        </optgroup>
                                    </select>
                                </div>
                                <div class="invalid-feedback">Please select your ID type.</div>
                            </div>
                            <div>
                                <label class="vf-label" for="idNumber">ID Number <span class="req">*</span></label>
                                <div class="vf-input-wrap">
                                    <span class="vf-input-icon"><i class="bi bi-hash"></i></span>
                                    <input type="text" class="vf-input" id="idNumber"
                                           name="id_number" required placeholder="Enter ID number">
                                </div>
                                <div class="invalid-feedback">Please enter your ID number.</div>
                            </div>
                        </div>

                        {{-- Document upload --}}
                        <p class="vf-sub-heading"><i class="bi bi-cloud-upload"></i> Document Upload</p>

                        {{-- ID Front + Back --}}
                        <div class="vf-upload-grid">

                            {{-- Front --}}
                            <div>
                                <div class="vf-upload-label-text">
                                    <span class="vf-upload-num">1</span>
                                    ID Front <span class="req">*</span>
                                </div>
                                <div class="upload-area-compact" id="frontUploadArea" data-upload-type="front">
                                    <input type="file" class="form-control d-none" id="id_front_image"
                                           name="id_front_image" accept="image/jpeg,image/png,image/jpg" required>
                                    <label for="id_front_image" class="upload-label-compact">
                                        <i class="bi bi-file-earmark-image"></i>
                                        <p>Upload Front</p>
                                        <small>JPG, PNG (Max 5MB)</small>
                                    </label>
                                    <div class="preview-container d-none">
                                        <img id="frontPreview" alt="ID Front">
                                        <button type="button" class="remove-image" data-target="front">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                        <span class="badge bg-success position-absolute top-0 start-0 m-1"
                                              style="font-size:0.6rem">
                                            <i class="bi bi-check-circle-fill"></i> Uploaded
                                        </span>
                                    </div>
                                    <div class="invalid-feedback">Please upload the front side.</div>
                                </div>
                            </div>

                            {{-- Back --}}
                            <div>
                                <div class="vf-upload-label-text">
                                    <span class="vf-upload-num vf-upload-num-2">2</span>
                                    ID Back <span class="req">*</span>
                                </div>
                                <div class="upload-area-compact" id="backUploadArea" data-upload-type="back">
                                    <input type="file" class="form-control d-none" id="id_back_image"
                                           name="id_back_image" accept="image/jpeg,image/png,image/jpg" required>
                                    <label for="id_back_image" class="upload-label-compact">
                                        <i class="bi bi-file-earmark-image"></i>
                                        <p>Upload Back</p>
                                        <small>JPG, PNG (Max 5MB)</small>
                                    </label>
                                    <div class="preview-container d-none">
                                        <img id="backPreview" alt="ID Back">
                                        <button type="button" class="remove-image" data-target="back">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                        <span class="badge bg-success position-absolute top-0 start-0 m-1"
                                              style="font-size:0.6rem">
                                            <i class="bi bi-check-circle-fill"></i> Uploaded
                                        </span>
                                    </div>
                                    <div class="invalid-feedback">Please upload the back side.</div>
                                </div>
                            </div>

                        </div>{{-- /.vf-upload-grid --}}

                        {{-- Selfie --}}
                        <div class="vf-form-group">
                            <div class="vf-upload-label-text">
                                <span class="vf-upload-num vf-upload-num-3">3</span>
                                Selfie with ID <span class="req">*</span>
                            </div>
                            <div class="vf-selfie-row">
                                <div class="upload-area-compact vf-selfie-upload" id="selfieUploadArea" data-upload-type="selfie">
                                    <input type="file" class="form-control d-none" id="selfie_with_id"
                                           name="selfie_with_id" accept="image/jpeg,image/png,image/jpg" required>
                                    <label for="selfie_with_id" class="upload-label-compact">
                                        <i class="bi bi-camera-fill"></i>
                                        <p>Upload Selfie</p>
                                        <small>Hold ID next to your face</small>
                                    </label>
                                    <div class="preview-container d-none">
                                        <img id="selfiePreview" alt="Selfie with ID">
                                        <button type="button" class="remove-image" data-target="selfie">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                        <span class="badge bg-success position-absolute top-0 start-0 m-1"
                                              style="font-size:0.6rem">
                                            <i class="bi bi-check-circle-fill"></i> Uploaded
                                        </span>
                                    </div>
                                    <div class="invalid-feedback">Please upload a selfie with ID.</div>
                                </div>

                                <div class="vf-selfie-guide">
                                    <p class="vf-selfie-guide-title">
                                        <i class="bi bi-info-circle-fill" style="color:var(--vf-primary)"></i>
                                        Selfie Guide
                                    </p>
                                    <ul>
                                        <li>Face clearly visible</li>
                                        <li>Hold ID beside face</li>
                                        <li>Good lighting</li>
                                        <li>No filters or edits</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Consent --}}
                        <div class="vf-consent">
                            <input class="form-check-input" type="checkbox" id="verificationConsent" required>
                            <label class="vf-consent-text" for="verificationConsent">
                                I certify that the information provided is <strong>true and accurate</strong>.
                                I understand that false information may result in account suspension.
                                <span style="color:var(--vf-danger)">*</span>
                            </label>
                        </div>

                        {{-- Actions --}}
                        <div class="vf-actions vf-actions-between">
                            <button type="button" class="vf-btn vf-btn-ghost" id="backToProfiling">
                                <i class="bi bi-arrow-left"></i> Back
                            </button>
                            <button type="submit" class="vf-btn vf-btn-success" id="saveClientVerification">
                                <i class="bi bi-shield-check"></i> Submit for Verification
                            </button>
                        </div>

                        {{-- Upload progress --}}
                        <div class="vf-upload-progress d-none" id="uploadProgressContainer">
                            <div class="vf-upload-progress-head">
                                <span>Uploading documents…</span>
                                <span id="uploadProgressText">0%</span>
                            </div>
                            <div class="vf-upload-bar-wrap">
                                <div class="vf-upload-bar" id="uploadProgressBar" style="width:0%"></div>
                            </div>
                        </div>

                    </form>
                </div>{{-- /#verification --}}

            </div>{{-- /.tab-content --}}
        </div>{{-- /.vf-card-body --}}
    </div>{{-- /.vf-card --}}

    {{-- ── Success Modal ──────────────────────────────────── --}}
    <div class="modal fade vf-success-modal" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="vf-success-body">
                    <i class="bi bi-check-circle-fill vf-success-icon"></i>
                    <h5 class="vf-success-title">Submission Successful!</h5>
                    <p class="vf-success-sub">
                        Your information has been submitted for review.<br>
                        We'll verify your documents within <strong>24–48 hours</strong>.
                    </p>
                    <button type="button" class="vf-btn vf-btn-primary" data-bs-dismiss="modal">
                        Got it
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
</div>
</div>
</div>
</x-app-layout>

<script src="{{ asset('assets/js/client/verification/profiling_save_update.js') }}"></script>
<script src="{{ asset('assets/js/client/verification/verification_store.js') }}"></script>
<script src="{{ asset('assets/js/client/verification/index.js') }}"></script>