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
        <div class="vf-step is-done">
            <div class="vf-step-num"><i class="bi bi-check-lg"></i></div>
            <span class="vf-step-label">Client Profiling</span>
        </div>
        <div class="vf-step-connector">
            <div class="vf-step-connector-fill" style="width: 100%"></div>
        </div>
        <div class="vf-step is-active">
            <div class="vf-step-num">2</div>
            <span class="vf-step-label">ID Verification</span>
        </div>
    </div>

    {{-- ── Progress Bar ────────────────────────────────────── --}}
    <div class="vf-progress-bar-wrap">
        <div class="vf-progress-bar" style="width: 100%"></div>
    </div>

    {{-- ── Main Card ───────────────────────────────────────── --}}
    <div class="vf-card">
        <div class="vf-card-body">

            <div class="vf-section-head">
                <div class="vf-section-icon vf-section-icon-green">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div>
                    <h5 class="vf-section-title">ID Verification</h5>
                    <p class="vf-section-sub">Upload your identification document to complete setup</p>
                </div>
            </div>

            <form id="verificationForm" method="POST" action="{{ route('client.verification.store') }}" novalidate enctype="multipart/form-data">
                @csrf

                {{-- ID Type + Number --}}
                <div class="vf-row vf-form-group">
                    <div>
                        <label class="vf-label" for="id_type">ID Type <span class="req">*</span></label>
                        <div class="vf-input-wrap vf-select-wrap">
                            <span class="vf-input-icon"><i class="bi bi-card-list"></i></span>
                            <select class="vf-select" id="id_type" name="id_type" required>
                                <option value="">Choose ID type…</option>
                                <optgroup label="Government-Issued IDs">
                                    <option value="passport">Passport</option>
                                    <option value="national">National ID (PhilSys)</option>
                                    <option value="drivers">Driver's License</option>
                                    <option value="voters">Voter's ID</option>
                                </optgroup>
                                <optgroup label="Other Valid IDs">
                                    <option value="sss">SSS ID</option>
                                    <option value="umid">UMID</option>
                                    <option value="philhealth">PhilHealth ID</option>
                                    <option value="postal">Postal ID</option>
                                    <option value="prc">PRC ID</option>
                                    <option value="tin">TIN ID</option>
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

                {{-- ID Image Upload --}}
                <div class="vf-form-group">
                    <label class="vf-label">ID Image <span class="req">*</span></label>

                    <div class="upload-area-compact" id="frontUploadArea">
                        <input type="file"
                               id="id_front_image"
                               name="id_front_image"
                               accept="image/jpeg,image/png,image/jpg"
                               required>

                        <label for="id_front_image" class="upload-label-compact" id="uploadLabel">
                            <i class="bi bi-file-earmark-image fs-3"></i>
                            <p class="mb-0 mt-1">Click to upload your ID</p>
                            <small class="text-muted">JPG, PNG — Max 5 MB</small>
                        </label>

                        <div class="preview-container d-none" id="frontPreviewContainer">
                            <img id="frontPreview"
                                 alt="ID Preview"
                                 style="max-width:100%; max-height:200px; border-radius:8px; object-fit:contain;">
                            <button type="button"
                                    class="remove-image mt-2 btn btn-sm btn-outline-danger"
                                    id="removeImageBtn">
                                <i class="bi bi-x-lg me-1"></i> Remove
                            </button>
                        </div>

                        <div class="invalid-feedback">Please upload your ID image.</div>
                    </div>
                </div>

                {{-- Consent --}}
                <div class="vf-consent">
                    <input class="form-check-input" type="checkbox" id="verificationConsent" required>
                    <label class="vf-consent-text" for="verificationConsent">
                        I certify that the information provided is <strong>true and accurate</strong>.
                        I understand that false information may result in account suspension.
                        <span style="color: var(--vf-danger)">*</span>
                    </label>
                </div>

                {{-- Actions --}}
                <div class="vf-actions vf-actions-between">
                    <a href="{{ route('client.profile.index') }}" class="vf-btn vf-btn-ghost">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                    <button type="submit" class="vf-btn vf-btn-success" id="saveClientVerification">
                        <i class="bi bi-shield-check"></i> Submit for Verification
                    </button>
                </div>

                {{-- Upload progress --}}
                <div class="vf-upload-progress d-none" id="uploadProgressContainer">
                    <div class="vf-upload-progress-head">
                        <span>Uploading document…</span>
                        <span id="uploadProgressText">0%</span>
                    </div>
                    <div class="vf-upload-bar-wrap">
                        <div class="vf-upload-bar" id="uploadProgressBar" style="width: 0%"></div>
                    </div>
                </div>

            </form>

        </div>
    </div>

</div>
</div>
</div>
</div>

{{-- Load SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Load verification script --}}
@push('scripts')
<script src="{{ asset('assets/js/client/verification/verification.js') }}"></script>
@endpush

</x-app-layout>