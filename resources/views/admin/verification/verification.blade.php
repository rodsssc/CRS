<x-app-layout>
    <div class="container-fluid px-4 py-3">

        {{-- ── Header ──────────────────────────────────────────────────────── --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="page-title mb-0">Verification Management</h2>
        </div>

        {{-- ── Stats Cards ─────────────────────────────────────────────────── --}}
        <div class="row g-2 mb-3">
            <div class="col-6 col-lg">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-soft">
                        <i class="fas fa-users text-primary"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Clients</div>
                        <div class="stat-value">{{ $totalClients ?: 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg">
                <div class="stat-card">
                    <div class="stat-icon bg-info-soft">
                        <i class="fas fa-user-check text-info"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Verified</div>
                        <div class="stat-value">{{ $verifiedCount ?: 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg">
                <div class="stat-card">
                    <div class="stat-icon bg-warning-soft">
                        <i class="fas fa-clock text-warning"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Pending</div>
                        <div class="stat-value">{{ $pendingCount ?: 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg">
                <div class="stat-card">
                    <div class="stat-icon bg-danger-soft">
                        <i class="fas fa-user-times text-danger"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Rejected</div>
                        <div class="stat-value">{{ $rejectedCount ?: 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Table Card ──────────────────────────────────────────────────── --}}
        <div class="table-card">

            {{-- Search & Controls --}}
            <div class="table-controls">
                <form method="GET" action="{{ route('admin.verification.index') }}"
                      class="row g-2 align-items-center">

                    <div class="col-md-6 col-lg-5">
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text"
                                   class="form-control form-control-sm"
                                   name="q"
                                   value="{{ $q ?? '' }}"
                                   placeholder="Search name, email, phone..."
                                   autocomplete="off">
                        </div>
                    </div>

                    <div class="col-auto d-flex gap-2">
                        <button class="btn btn-primary btn-sm" type="submit">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                        <a class="btn btn-outline-secondary btn-sm"
                           href="{{ route('admin.verification.index') }}">Clear</a>
                    </div>

                    <div class="col-md-3 col-lg-2">
                        <select class="form-select form-select-sm" name="status"
                                onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="approved" @selected(($status ?? '') === 'approved')>Verified</option>
                            <option value="pending"  @selected(($status ?? '') === 'pending')>Pending</option>
                            <option value="rejected" @selected(($status ?? '') === 'rejected')>Rejected</option>
                        </select>
                    </div>

                    <div class="col-md-2 col-lg-2 ms-lg-auto">
                        <select class="form-select form-select-sm" name="per_page"
                                onchange="this.form.submit()">
                            <option value="10" @selected(($perPage ?? 10) == 10)>10 per page</option>
                            <option value="25" @selected(($perPage ?? 10) == 25)>25 per page</option>
                            <option value="50" @selected(($perPage ?? 10) == 50)>50 per page</option>
                        </select>
                    </div>

                </form>
            </div>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table custom-table mb-0">
                    <thead>
                        <tr>
                            <th>Client ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Phone No.</th>
                            <th>Date of Birth</th>
                            <th>Address</th>
                            <th>Facebook Name</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($clientProfile as $client)
                            @php $verification = $client->user->latestVerification; @endphp
                            <tr>
                                <td><span class="user-name">#00{{ $client->id }}</span></td>
                                <td><span class="user-name">{{ $client->first_name }}</span></td>
                                <td class="text-muted">{{ $client->last_name }}</td>
                                <td class="text-muted">{{ $client->user->phone ?? '—' }}</td>
                                <td class="text-muted">
                                    {{ $client->date_birth ? $client->date_birth->format('M d, Y') : 'N/A' }}
                                </td>
                                <td class="text-muted">{{ $client->address ?? '—' }}</td>
                                <td class="text-muted">{{ $client->facebook_name ?: 'Not Provided' }}</td>
                                <td>
                                    @if ($verification && $verification->status === 'pending')
                                        <span class="status-tag status-pending">Pending</span>
                                    @elseif ($verification && $verification->status === 'approved')
                                        <span class="status-tag status-approved">Approved</span>
                                    @elseif ($verification && $verification->status === 'rejected')
                                        <span class="status-tag status-rejected">Rejected</span>
                                    @else
                                        <span class="text-muted">Not Submitted</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        {{-- View: always visible --}}
                                        <button class="btn-action" title="View"
                                                data-user-id="{{ $client->client_id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        {{-- Quick-reject: only for pending rows so data-verification-id is never null --}}
                                        @if ($verification && $verification->status === 'pending')
                                            <button type="button" class="btn-action" title="Reject"
                                                    data-verification-id="{{ $verification->id }}">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="fas fa-inbox fs-4 mb-2 d-block"></i>
                                    No records found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($clientProfile->hasPages())
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                    <div class="text-muted small">
                        Showing {{ $clientProfile->firstItem() }}–{{ $clientProfile->lastItem() }}
                        of {{ $clientProfile->total() }} record(s)
                    </div>
                    <div>
                        {{ $clientProfile->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @else
                <div class="px-3 py-2 border-top text-muted small">
                    {{ $clientProfile->total() }} record(s) found
                </div>
            @endif

        </div>{{-- /table-card --}}
    </div>{{-- /container-fluid --}}
</x-app-layout>

{{-- ═══════════════════════════════════════════════════════════════════════
     VIEW VERIFICATION MODAL
     Modal HTML lives here in Blade. JS reads it from the DOM — no injection.
═══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" tabindex="-1" id="viewVerificationModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">

            {{-- Header --}}
            <div class="modal-header bg-header border-0 px-4 py-3">
                <div>
                    <h6 class="modal-title text-white fw-bold mb-0">
                        <i class="fas fa-user-check me-2"></i>Verification Details
                    </h6>
                    <p class="text-white-50 mb-0" style="font-size:11px;">
                        Review client identity and submitted documents
                    </p>
                </div>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body p-3 bg-light">
                <div class="row g-3">

                    {{-- LEFT — image + status --}}
                    <div class="col-lg-5 d-flex flex-column gap-3">

                        <div class="bg-white border rounded-3 p-3 d-flex flex-column"
                             style="min-height:240px;">
                            <div class="text-muted fw-semibold mb-2 text-center"
                                 style="font-size:10px;letter-spacing:.06em;">ID FRONT</div>
                            <div class="d-flex justify-content-center align-items-center flex-grow-1">
                                {{-- Real image — JS sets src and removes d-none when a path is returned --}}
                                <img id="viewIdFrontImage"
                                     src=""
                                     alt="ID Front"
                                     class="rounded-3 border d-none"
                                     style="width:100%;max-width:320px;height:190px;object-fit:cover;">
                                {{-- Fallback shown when no image is uploaded (no external URL) --}}
                                <div id="viewIdFrontPlaceholder"
                                     class="rounded-3 border d-flex flex-column
                                            align-items-center justify-content-center text-muted"
                                     style="width:100%;max-width:320px;height:190px;
                                            background:#f8f9fa;font-size:11px;">
                                    <i class="fas fa-id-card fs-4 mb-1 opacity-25"></i>
                                    No image uploaded
                                </div>
                            </div>
                        </div>

                        <div class="bg-white border rounded-3 text-center py-3">
                            <div class="text-muted fw-semibold mb-1"
                                 style="font-size:10px;letter-spacing:.06em;">
                                VERIFICATION STATUS
                            </div>
                            <span class="status-tag status-pending d-inline-block"
                                  id="viewVerificationStatus">Pending</span>
                        </div>

                    </div>

                    {{-- RIGHT — detail fields --}}
                    <div class="col-lg-7 d-flex flex-column gap-2">

                        <div class="bg-white border rounded-3 p-3">
                            <div class="fw-bold small">
                                <span id="viewClientFirstName">—</span>
                                <span id="viewClientLastName">—</span>
                            </div>
                            <div class="text-muted" style="font-size:11px;">
                                <span id="viewClientEmail">—</span>
                            </div>
                        </div>

                        <div class="bg-white border rounded-3 p-3">
                            <div class="text-muted fw-semibold mb-2"
                                 style="font-size:10px;letter-spacing:.06em;">CLIENT PROFILE</div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="text-muted" style="font-size:10px;">Phone Number</div>
                                    <div class="fw-semibold" style="font-size:11px;"
                                         id="viewClientPhone">—</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted" style="font-size:10px;">Date of Birth</div>
                                    <div class="fw-semibold" style="font-size:11px;"
                                         id="viewClientDateBirth">—</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted" style="font-size:10px;">Nationality</div>
                                    <div class="fw-semibold" style="font-size:11px;"
                                         id="viewClientNationality">—</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted" style="font-size:10px;">Facebook Name</div>
                                    <div class="fw-semibold" style="font-size:11px;"
                                         id="viewClientFacebook">—</div>
                                </div>
                                <div class="col-12">
                                    <div class="text-muted" style="font-size:10px;">Address</div>
                                    <div class="fw-semibold" style="font-size:11px;"
                                         id="viewClientAddress">—</div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white border rounded-3 p-3">
                            <div class="text-muted fw-semibold mb-2"
                                 style="font-size:10px;letter-spacing:.06em;">EMERGENCY CONTACT</div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="text-muted" style="font-size:10px;">Contact Name</div>
                                    <div class="fw-semibold" style="font-size:11px;"
                                         id="viewEmergencyName">—</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted" style="font-size:10px;">Contact Phone</div>
                                    <div class="fw-semibold" style="font-size:11px;"
                                         id="viewEmergencyPhone">—</div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white border rounded-3 p-3">
                            <div class="text-muted fw-semibold mb-2"
                                 style="font-size:10px;letter-spacing:.06em;">ID VERIFICATION DETAILS</div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="text-muted" style="font-size:10px;">ID Type</div>
                                    <div class="fw-semibold text-capitalize" style="font-size:11px;"
                                         id="viewIdType">—</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted" style="font-size:10px;">ID Number</div>
                                    <div class="fw-semibold" style="font-size:11px;"
                                         id="viewIdNumber">—</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted" style="font-size:10px;">Submitted At</div>
                                    <div class="fw-semibold" style="font-size:11px;"
                                         id="viewSubmittedAt">—</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted" style="font-size:10px;">Verified At</div>
                                    <div class="fw-semibold" style="font-size:11px;"
                                         id="viewVerifiedAt">—</div>
                                </div>
                                <div class="col-12">
                                    <div class="text-muted" style="font-size:10px;">Verified By</div>
                                    <div class="fw-semibold" style="font-size:11px;"
                                         id="viewVerifiedBy">—</div>
                                </div>
                            </div>

                            {{-- Rejection reason (hidden until status === rejected) --}}
                            <div class="col-12 mt-2" id="rejectionReasonSection" style="display:none;">
                                <div class="alert alert-danger mb-0 py-2 px-3">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="fas fa-exclamation-triangle text-danger mt-1"
                                           style="font-size:11px;"></i>
                                        <div>
                                            <div class="fw-semibold" style="font-size:10px;">
                                                Rejection Reason
                                            </div>
                                            <div style="font-size:11px;"
                                                 id="viewRejectionReason">—</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>{{-- /col-lg-7 --}}
                </div>{{-- /row --}}
            </div>{{-- /modal-body --}}

            {{-- Footer --}}
            <div class="modal-footer border-0 bg-white px-4 pb-4 pt-2 gap-2">
                <button type="button" class="btn btn-secondary btn-sm px-4"
                        data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
                <button type="button" class="btn btn-danger btn-sm px-4"
                        id="rejectVerificationBtn" data-verification-id=""
                        style="display:none;">
                    <i class="fas fa-times me-1"></i>Reject
                </button>
                <button type="button" class="btn btn-bg-color px-4"
                        id="approveVerificationBtn" data-verification-id=""
                        style="display:none;">
                    <i class="fas fa-check me-1"></i>Approve
                </button>
            </div>

        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════════════════════════════
     REJECT VERIFICATION MODAL
═══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="rejectVerificationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">

            <div class="modal-header bg-header text-white border-0 px-4 py-3">
                <div>
                    <h5 class="modal-title fw-bold mb-0">
                        <i class="fas fa-times-circle me-2"></i>Reject Verification
                    </h5>
                    <p class="text-white-50 mb-0" style="font-size:11px;">
                        Provide a reason for rejection (optional)
                    </p>
                </div>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                <input type="hidden" id="rejectionVerificationId">

                <div class="mb-3">
                    <label for="rejectionReasonInput"
                           class="form-label small fw-semibold text-muted mb-2">
                        Rejection Reason <span class="text-muted">(optional)</span>
                    </label>
                    <textarea class="form-control"
                              id="rejectionReasonInput"
                              rows="4"
                              placeholder="e.g. Blurry ID image, mismatched information, incomplete documents..."
                              maxlength="500"></textarea>
                    <div class="d-flex justify-content-end mt-1">
                        <small class="text-muted">
                            <span id="rejectionCharCount">0</span>/500
                        </small>
                    </div>
                </div>

                <div class="alert alert-warning bg-warning-soft border-0 small mb-0">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    This action cannot be undone. The client will be notified of the rejection.
                </div>
            </div>

            <div class="modal-footer border-0 bg-light px-4 py-3">
                <button type="button" class="btn btn-secondary btn-sm"
                        data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="confirmRejectBtn">
                    <i class="fas fa-check me-1"></i>Confirm Rejection
                </button>
            </div>

        </div>
    </div>
</div>


    <script src="{{ asset('assets/js/admin/verification/admin_verification_actions.js') }}"></script>
