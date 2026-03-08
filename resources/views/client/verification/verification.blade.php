<x-app-layout>
    <div class="verification-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-10 col-xl-8">
                    
                    <!-- Progress Indicator -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center step-indicator active" id="step1-indicator">
                                <div class="step-number">1</div>
                                <span class="ms-2 fw-medium">Client Profiling</span>
                            </div>
                            <div class="progress-connector"></div>
                            <div class="d-flex align-items-center step-indicator" id="step2-indicator">
                                <div class="step-number">2</div>
                                <span class="ms-2 fw-medium">ID Verification</span>
                            </div>
                        </div>
                        <div class="progress" style="height: 4px;">
                            <div class="progress-bar" role="progressbar" id="formProgress" style="width: 50%;" 
                                 aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    <!-- Main Card -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-4">
                            <div class="tab-content" id="clientTabsContent">
                                
                                <!-- PROFILING FORM -->
                                <div class="tab-pane fade show active" id="profiling" role="tabpanel">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="icon-circle bg-primary-subtle me-3">
                                            <i class="bi bi-person-circle text-primary fs-5"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-0">Client Information</h5>
                                            <p class="text-muted mb-0 small">Please provide your basic information</p>
                                        </div>
                                    </div>
                                   
                                    <form id="profilingForm" method="POST" novalidate>
                                        <input type="hidden" id="client_id" name="client_id" value="{{ $client->id }}">
                                        @csrf
                                        
                                        <!-- Name Fields -->
                                        <div class="row g-2 mb-3">
                                            <div class="col-md-6">
                                                <label for="firstName" class="form-label form-label-sm">
                                                    First Name <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                                    <input type="text" class="form-control form-control-sm" id="firstName" 
                                                           name="firstName" required placeholder="Enter first name"
                                                           value="{{ old('firstName', optional($client->clientProfile)->first_name) }}">
                                                    <div class="invalid-feedback">Please enter your first name.</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="lastName" class="form-label form-label-sm">
                                                    Last Name <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                                    <input type="text" class="form-control form-control-sm" id="lastName" 
                                                           name="lastName" required placeholder="Enter last name"
                                                           value="{{ old('lastName', optional($client->clientProfile)->last_name) }}">
                                                    <div class="invalid-feedback">Please enter your last name.</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Date of Birth & Nationality -->
                                        <div class="row g-2 mb-3">
                                            <div class="col-md-6">
                                                <label for="dateBirth" class="form-label form-label-sm">
                                                    Date of Birth <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                                    <input type="date" class="form-control form-control-sm" id="dateBirth" 
                                                           name="dateBirth" required
                                                           value="{{ old('dateBirth', optional($client->clientProfile)->date_birth) }}">
                                                    <div class="invalid-feedback">Please select your date of birth.</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="nationality" class="form-label form-label-sm">
                                                    Nationality <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text"><i class="bi bi-globe"></i></span>
                                                    <input type="text" class="form-control form-control-sm" id="nationality" 
                                                           name="nationality" required placeholder="e.g., Filipino"
                                                           value="{{ old('nationality', optional($client->clientProfile)->nationality) }}">
                                                    <div class="invalid-feedback">Please enter your nationality.</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Address -->
                                        <div class="mb-3">
                                            <label for="address" class="form-label form-label-sm">
                                                Address <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text"><i class="bi bi-house"></i></span>
                                                <textarea class="form-control form-control-sm" id="address" name="address" 
                                                          rows="2" required placeholder="Enter your complete address">{{ old('address', optional($client->clientProfile)->address) }}</textarea>
                                                <div class="invalid-feedback">Please enter your address.</div>
                                            </div>
                                        </div>

                                        <!-- Facebook Name -->
                                        <div class="mb-3">
                                            <label for="facebook_name" class="form-label form-label-sm">
                                                Facebook Name <span class="text-muted small">(Optional)</span>
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text"><i class="bi bi-facebook"></i></span>
                                                <input type="text" class="form-control form-control-sm" id="facebook_name" 
                                                       name="facebookName" placeholder="Your Facebook profile name"
                                                       value="{{ old('facebookName', optional($client->clientProfile)->facebook_name) }}">
                                            </div>
                                        </div>

                                        <!-- Emergency Contact Alert -->
                                        <div class="alert alert-info py-2 px-3 d-flex align-items-start mb-3" role="alert">
                                            <i class="bi bi-info-circle-fill me-2 mt-1"></i>
                                            <div>
                                                <strong class="small">Emergency Contact</strong><br>
                                                <small>Please provide someone we can contact in case of emergency</small>
                                            </div>
                                        </div>

                                        <!-- Emergency Contact Fields -->
                                        <div class="row g-2 mb-3">
                                            <div class="col-md-6">
                                                <label for="emergencyContactName" class="form-label form-label-sm">
                                                    Emergency Contact Name <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text"><i class="bi bi-person-lines-fill"></i></span>
                                                    <input type="text" class="form-control form-control-sm" id="emergencyContactName" 
                                                           name="emergencyContactName" required placeholder="Full name"
                                                           value="{{ old('emergencyContactName', optional($client->clientProfile)->emergency_contact_name) }}">
                                                    <div class="invalid-feedback">Please enter emergency contact name.</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="emergencyContactPhone" class="form-label form-label-sm">
                                                    Emergency Contact Phone <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                                    <input type="tel" class="form-control form-control-sm" id="emergencyContactPhone" 
                                                           name="emergencyContactPhone" required placeholder="+63 XXX XXX XXXX"
                                                           pattern="[0-9+\s\-()]+">
                                                    <div class="invalid-feedback">Please enter a valid phone number.</div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        

                                        <!-- Submit Button -->
                                        <div class="d-flex justify-content-end mt-3">
                                             <!-- Save Button - For creating new profile -->
                                                <button type="button" id="saveClientProfiling" class="btn btn-primary">
                                                    <i class="bi bi-arrow-right-circle me-1"></i>Continue to Verification
                                                </button>
                                                
                                                <!-- Update Button - Hidden by default, shown when editing -->
                                                <button type="button" id="updateClientProfiling" class="btn btn-success d-none">
                                                    <i class="bi bi-check-circle me-1"></i>Update & Continue
                                                </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- VERIFICATION FORM -->
                                <div class="tab-pane fade" id="verification" role="tabpanel">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="icon-circle bg-success-subtle me-3">
                                            <i class="bi bi-shield-check text-success fs-5"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-0">ID Verification</h5>
                                            <p class="text-muted mb-0 small">Upload your identification documents</p>
                                        </div>
                                    </div>

                                    <form id="verificationForm" novalidate enctype="multipart/form-data">
                                        @csrf

                                        <!-- Guidelines -->
                                        <div class="alert alert-info py-2 px-3 d-flex align-items-start mb-3" role="alert">
                                            <i class="bi bi-lightbulb-fill me-2 mt-1 flex-shrink-0"></i>
                                            <div class="small">
                                                <strong>Verification Tips:</strong>
                                                <ul class="mb-0 mt-1 ps-3">
                                                    <li>Ensure images are clear, bright, and in focus</li>
                                                    <li>All four corners of your ID must be visible</li>
                                                    <li>For selfie: Hold ID next to your face</li>
                                                    <li>Accepted: JPG, PNG (Max 5MB per file)</li>
                                                </ul>
                                            </div>
                                        </div>

                                        <!-- ID Information -->
                                        <div class="mb-3">
                                            <h6 class="mb-2 small text-uppercase text-muted">
                                                <i class="bi bi-card-checklist me-1"></i>ID Information
                                            </h6>
                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <label for="id_type" class="form-label form-label-sm">
                                                        ID Type <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text"><i class="bi bi-card-list"></i></span>
                                                        <select class="form-select form-select-sm" id="id_type" name="id_type" required>
                                                            <option value="">Choose ID type...</option>
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
                                                        <div class="invalid-feedback">Please select your ID type.</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="idNumber" class="form-label form-label-sm">
                                                        ID Number <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text"><i class="bi bi-hash"></i></span>
                                                        <input type="text" class="form-control form-control-sm" id="idNumber" 
                                                            name="id_number" required placeholder="Enter ID number">
                                                        <div class="invalid-feedback">Please enter your ID number.</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Document Upload -->
                                        <div class="mb-3">
                                            <h6 class="mb-2 small text-uppercase text-muted">
                                                <i class="bi bi-cloud-upload me-1"></i>Document Upload
                                            </h6>
                                            
                                            <div class="row g-2 mb-2">
                                                <!-- ID Front -->
                                                <div class="col-md-6">
                                                    <label class="form-label form-label-sm fw-semibold">
                                                        <i class="bi bi-1-circle-fill text-primary me-1"></i>
                                                        ID Front <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="upload-area-compact" id="frontUploadArea" data-upload-type="front">
                                                        <input type="file" class="form-control d-none" id="id_front_image" 
                                                            name="id_front_image" accept="image/jpeg,image/png,image/jpg" required>
                                                        <label for="id_front_image" class="upload-label-compact">
                                                            <i class="bi bi-file-earmark-image fs-3 text-muted"></i>
                                                            <p class="mb-0 mt-1 small fw-semibold">Upload Front</p>
                                                            <small class="text-muted" style="font-size: 0.7rem;">JPG, PNG (Max 5MB)</small>
                                                        </label>
                                                        <div class="preview-container d-none">
                                                            <img id="frontPreview" class="img-fluid rounded" alt="ID Front">
                                                            <button type="button" class="btn btn-sm btn-danger remove-image" data-target="front">
                                                                <i class="bi bi-x-lg"></i>
                                                            </button>
                                                            <span class="badge bg-success position-absolute top-0 start-0 m-1" style="font-size: 0.65rem;">
                                                                <i class="bi bi-check-circle-fill"></i> Uploaded
                                                            </span>
                                                        </div>
                                                        <div class="invalid-feedback">Please upload the front side.</div>
                                                    </div>
                                                </div>

                                                <!-- ID Back -->
                                                <div class="col-md-6">
                                                    <label class="form-label form-label-sm fw-semibold">
                                                        <i class="bi bi-2-circle-fill text-primary me-1"></i>
                                                        ID Back <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="upload-area-compact" id="backUploadArea" data-upload-type="back">
                                                        <input type="file" class="form-control d-none" id="id_back_image" 
                                                            name="id_back_image" accept="image/jpeg,image/png,image/jpg" required>
                                                        <label for="id_back_image" class="upload-label-compact">
                                                            <i class="bi bi-file-earmark-image fs-3 text-muted"></i>
                                                            <p class="mb-0 mt-1 small fw-semibold">Upload Back</p>
                                                            <small class="text-muted" style="font-size: 0.7rem;">JPG, PNG (Max 5MB)</small>
                                                        </label>
                                                        <div class="preview-container d-none">
                                                            <img id="backPreview" class="img-fluid rounded" alt="ID Back">
                                                            <button type="button" class="btn btn-sm btn-danger remove-image" data-target="back">
                                                                <i class="bi bi-x-lg"></i>
                                                            </button>
                                                            <span class="badge bg-success position-absolute top-0 start-0 m-1" style="font-size: 0.65rem;">
                                                                <i class="bi bi-check-circle-fill"></i> Uploaded
                                                            </span>
                                                        </div>
                                                        <div class="invalid-feedback">Please upload the back side.</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Selfie with ID -->
                                            <div class="mb-0">
                                                <label class="form-label form-label-sm fw-semibold">
                                                    <i class="bi bi-3-circle-fill text-primary me-1"></i>
                                                    Selfie with ID <span class="text-danger">*</span>
                                                </label>
                                                <div class="row g-2">
                                                    <div class="col-md-8">
                                                        <div class="upload-area-compact" id="selfieUploadArea" data-upload-type="selfie">
                                                            <input type="file" class="form-control d-none" id="selfie_with_id" 
                                                                name="selfie_with_id" accept="image/jpeg,image/png,image/jpg" required>
                                                            <label for="selfie_with_id" class="upload-label-compact">
                                                                <i class="bi bi-camera-fill fs-3 text-muted"></i>
                                                                <p class="mb-0 mt-1 small fw-semibold">Upload Selfie</p>
                                                                <small class="text-muted d-block" style="font-size: 0.7rem;">Hold ID next to your face</small>
                                                            </label>
                                                            <div class="preview-container d-none">
                                                                <img id="selfiePreview" class="img-fluid rounded" alt="Selfie">
                                                                <button type="button" class="btn btn-sm btn-danger remove-image" data-target="selfie">
                                                                    <i class="bi bi-x-lg"></i>
                                                                </button>
                                                                <span class="badge bg-success position-absolute top-0 start-0 m-1" style="font-size: 0.65rem;">
                                                                    <i class="bi bi-check-circle-fill"></i> Uploaded
                                                                </span>
                                                            </div>
                                                            <div class="invalid-feedback">Please upload a selfie with ID.</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="card border h-100 bg-light">
                                                            <div class="card-body p-2">
                                                                <p class="small text-muted mb-1">
                                                                    <i class="bi bi-info-circle-fill text-info"></i> <strong>Guide:</strong>
                                                                </p>
                                                                <ul class="small mb-0 ps-3" style="font-size: 0.75rem; line-height: 1.4;">
                                                                    <li>Face clearly visible</li>
                                                                    <li>Hold ID beside face</li>
                                                                    <li>Good lighting</li>
                                                                    <li>No filters/edits</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Consent -->
                                        <div class="card border-0 bg-light mb-3">
                                            <div class="card-body p-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="verificationConsent" required>
                                                    <label class="form-check-label small" for="verificationConsent">
                                                        I certify that the information provided is <strong>true and accurate</strong>. 
                                                        I understand false information may result in account suspension.
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="invalid-feedback">You must agree to continue.</div>
                                                </div>
                                            </div>
                                        </div>

                                       <!-- Action Buttons -->
                                        <div class="d-flex justify-content-between align-items-center">
                                            <button type="button" 
                                                    class="btn btn-outline-secondary btn-sm" 
                                                    id="backToProfiling">
                                                <i class="bi bi-arrow-left me-1"></i>Back
                                            </button>
                                            <button type="submit" 
                                                    class="btn btn-success btn-sm" 
                                                    id="saveClientVerification">
                                                <i class="bi bi-shield-check me-1"></i>Submit for Verification
                                            </button>
                                        </div>

                                        <!-- Upload Progress -->
                                        <div class="mt-3 d-none" id="uploadProgressContainer">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <small class="text-muted">Uploading documents...</small>
                                                <small class="text-muted" id="uploadProgressText">0%</small>
                                            </div>
                                            <div class="progress" style="height: 4px;">
                                                <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                                                     role="progressbar" style="width: 0%" id="uploadProgressBar"></div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Success Modal -->
                    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body text-center p-4">
                                    <div class="success-checkmark mb-3">
                                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                                    </div>
                                    <h5 class="mb-2">Submission Successful!</h5>
                                    <p class="text-muted small mb-3">Your information has been submitted. We'll review within 24-48 hours.</p>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            
        </div>
    </div>
</x-app-layout>

<script src="{{asset('assets/js/client/verification/profiling_save_update.js')}}"></script>
<script src="{{asset('assets/js/client/verification/verification_store.js')}}"></script>
<script src="{{asset('assets/js/client/verification/index.js')}}"></script>