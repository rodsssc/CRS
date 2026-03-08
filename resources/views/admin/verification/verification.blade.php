
<x-app-layout>
    <div class="container-fluid px-4 py-3">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2 class="page-title mb-0">Verification Management</h2>
            </div>
           
        </div>

 
            <!-- Stats Cards -->
            <div class="row g-2 mb-3">
                <div class="col-6 col-lg">
                    <div class="stat-card">
                        <div class="stat-icon bg-primary-soft">
                            <!-- Users / Clients -->
                            <i class="fas fa-users text-primary"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">Total Client</div>
                            <div class="stat-value">
                                {{ $totalClients ?: 0 }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-6 col-lg">
                    <div class="stat-card">
                        <div class="stat-icon bg-info-soft">
                            <!-- Verified / Check -->
                            <i class="fas fa-user-check text-info"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">Verified</div>
                            <div class="stat-value">
                                {{ $verifiedCount ?: 0 }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-6 col-lg">
                    <div class="stat-card">
                        <div class="stat-icon bg-warning-soft">
                            <!-- Pending / Clock -->
                            <i class="fas fa-clock text-warning"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">Pending</div>
                            <div class="stat-value">{{$pendingCount ?: 0}}</div>
                        </div>
                    </div>
                </div>
            
                <div class="col-6 col-lg">
                    <div class="stat-card">
                        <div class="stat-icon bg-danger-soft">
                            <!-- Rejected / X -->
                            <i class="fas fa-user-times text-danger"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">Reject</div>
                            <div class="stat-value">{{$rejectedCount ?: 0}}</div>
                        </div>
                    </div>
                </div>


        
            </div>
 


        <!-- Table Card -->
        <div class="table-card">
            <!-- Search and Controls -->
            <div class="table-controls">
                <div class="row g-2 align-items-center">
                    <!-- Search Bar -->
                    <div class="col-md-6 col-lg-5">
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" 
                                   class="form-control form-control-sm" 
                                   placeholder="Search users...">
                        </div>
                    </div>

                    <!-- Role Filter -->
                    <div class="col-md-3 col-lg-3">
                        <select class="form-select form-select-sm">
                            <option selected>All </option>
                            <option>Verified</option>
                            <option>Pending</option>
                            <option>Reject</option>
                            
                        </select>
                    </div>

                    <!-- Entries Per Page -->
                    <div class="col-md-5 col-lg-2">
                        <select class="form-select form-select-sm">
                            <option>10 per page</option>
                            <option>25 per page</option>
                            <option>50 per page</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table custom-table mb-0">
                    <thead>
                        <tr>
                            <th width="5%">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                </div>
                            </th>
                            <th >First name</th>
                            <th >Last name</th>
                            <th >Phone no.</th>
                            <th >Date birth</th>
                            <th>Address</th>
                            <th>facebook name</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody> 
                        @foreach ($clientProfile as $client)
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox">
                                    </div>
                                </td>
                                <td>
                                    <div class="user-info">
                                        <span class="client-first-name">{{$client->first_name}}</span>
                                    </div>
                                </td>
                                <td class="text-muted ">
                                    <div class="car-last-name">
                                        <span>{{$client->last_name}}</span>
                                    </div>
                                </td>
                               
                                 <td class="text-muted">{{$client->user->phone}}</td>
                                 <td class="text-muted">{{ $client->date_birth ? $client->date_birth->format('M d, Y') : 'N/A' }}</td>
                                 <td class="text-muted">{{$client->address}}</td>
                                 <td class="text-muted">{{$client->facebook_name?: "Not Provided"}}</td>

                                 @php
                                    $verification = $client->user->latestVerification;
                                @endphp

                                    <td>
                                        @if($verification->status === 'pending')
                                            <span class="status-tag status-pending">Pending</span>
                                        @elseif($verification?->status === 'approved')
                                            <span class="status-tag status-approved">Approved</span>
                                        @elseif($verification?->status === 'rejected')
                                            <span class="status-tag status-rejected">Rejected</span>
                                        @else
                                            <span class="text-muted">Not Submitted</span>
                                        @endif
                                    </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action" title="View" data-verification-id="{{$client->client_id}}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                      
                                        <button class="btn-action" title="Delete"  data-verification-id="{{$verification->id}}" id="rejectVerificationBtn"">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                              
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="table-footer">
                <div class="showing-entries">
                    Showing 1-10
                </div>
                <nav>
                    <ul class="pagination mb-0">
                        <li class="page-item disabled">
                            <a class="page-link" href="#">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

</x-app-layout>
<script src="{{asset('assets/js/admin/verification/show.js')}}"></script>
<script src="{{asset('assets/js/admin/verification/reject.js')}}"></script>
<script src="{{asset('assets/js/admin/verification/approve.js')}}"></script>

{{-- View Verification Modal --}}
<div class="modal fade" tabindex="-1" id="viewVerificationModal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">

            {{-- HEADER --}}
            <div class="modal-header bg-header border-0 px-4 py-3">
                <div>
                    <h6 class="modal-title text-white fw-bold mb-0">
                        <i class="fas fa-user-check me-2"></i>Verification Details
                    </h6>
                    <p class="text-white-50 mb-0" style="font-size:11px;">Review client identity and submitted documents</p>
                </div>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- BODY --}}
            <div class="modal-body p-3 bg-light">
                <div class="row g-3">

                    {{-- LEFT - ID Images --}}
                    <div class="col-lg-5 d-flex flex-column gap-3">

                        <div>
                            <div class="text-muted fw-semibold mb-1" style="font-size:10px; letter-spacing:.06em;">ID FRONT</div>
                            <img id="viewIdFrontImage"
                                 src="https://via.placeholder.com/500x300?text=ID+Front"
                                 alt="ID Front"
                                 class="img-fluid rounded-3 border w-100"
                                 style="height:120px; object-fit:cover;">
                        </div>

                        <div>
                            <div class="text-muted fw-semibold mb-1" style="font-size:10px; letter-spacing:.06em;">ID BACK</div>
                            <img id="viewIdBackImage"
                                 src="https://via.placeholder.com/500x300?text=ID+Back"
                                 alt="ID Back"
                                 class="img-fluid rounded-3 border w-100"
                                 style="height:120px; object-fit:cover;">
                        </div>

                        <div>
                            <div class="text-muted fw-semibold mb-1" style="font-size:10px; letter-spacing:.06em;">SELFIE WITH ID</div>
                            <img id="viewSelfieImage"
                                 src="https://via.placeholder.com/500x300?text=Selfie+with+ID"
                                 alt="Selfie with ID"
                                 class="img-fluid rounded-3 border w-100"
                                 style="height:120px; object-fit:cover;">
                        </div>

                        <div class=" rounded-3 text-center py-2">
                            <div class="text-white-50" style="font-size:10px;">VERIFICATION STATUS</div>
                            <span class="status-tag status-pending mt-1 d-inline-block" id="viewVerificationStatus">Pending</span>
                        </div>

                    </div>

                    {{-- RIGHT - Details --}}
                    <div class="col-lg-7 d-flex flex-column gap-2">

                        {{-- Client Name --}}
                        <div class="bg-white border rounded-3 p-3">
                            <div class="fw-bold small">
                                <span id="viewClientFirstName">John</span> <span id="viewClientLastName">Doe</span>
                            </div>
                            <div class="text-muted" style="font-size:11px;">
                                <span id="viewClientEmail">john.doe@example.com</span>
                            </div>
                        </div>

                        {{-- Client Profile --}}
                        <div class="bg-white border rounded-3 p-3">
                            <div class="text-muted fw-semibold mb-2" style="font-size:10px; letter-spacing:.06em;">
                                CLIENT PROFILE
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="text-muted" style="font-size:10px;">Phone Number</div>
                                    <div class="fw-semibold" style="font-size:11px;" id="viewClientPhone">+63 912 345 6789</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted" style="font-size:10px;">Date of Birth</div>
                                    <div class="fw-semibold" style="font-size:11px;" id="viewClientDateBirth">Jan 15, 1990</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted" style="font-size:10px;">Nationality</div>
                                    <div class="fw-semibold" style="font-size:11px;" id="viewClientNationality">Filipino</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted" style="font-size:10px;">Facebook Name</div>
                                    <div class="fw-semibold" style="font-size:11px;" id="viewClientFacebook">John Doe FB</div>
                                </div>
                                <div class="col-12">
                                    <div class="text-muted" style="font-size:10px;">Address</div>
                                    <div class="fw-semibold" style="font-size:11px;" id="viewClientAddress">123 Main St, City, Province</div>
                                </div>
                            </div>
                        </div>

                        {{-- Emergency Contact --}}
                        <div class="bg-white border rounded-3 p-3">
                            <div class="text-muted fw-semibold mb-2" style="font-size:10px; letter-spacing:.06em;">
                                EMERGENCY CONTACT
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="text-muted" style="font-size:10px;">Contact Name</div>
                                    <div class="fw-semibold" style="font-size:11px;" id="viewEmergencyName">Jane Doe</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted" style="font-size:10px;">Contact Phone</div>
                                    <div class="fw-semibold" style="font-size:11px;" id="viewEmergencyPhone">+63 998 765 4321</div>
                                </div>
                            </div>
                        </div>

                        {{-- ID Verification Details --}}
                        <div class="bg-white border rounded-3 p-3">
                            <div class="text-muted fw-semibold mb-2" style="font-size:10px; letter-spacing:.06em;">
                                ID VERIFICATION DETAILS
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="text-muted" style="font-size:10px;">ID Type</div>
                                    <div class="fw-semibold text-capitalize" style="font-size:11px;" id="viewIdType">National ID</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted" style="font-size:10px;">ID Number</div>
                                    <div class="fw-semibold" style="font-size:11px;" id="viewIdNumber">1234-5678-9012</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted" style="font-size:10px;">Submitted At</div>
                                    <div class="fw-semibold" style="font-size:11px;" id="viewSubmittedAt">Feb 10, 2026 3:45 PM</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted" style="font-size:10px;">Verified At</div>
                                    <div class="fw-semibold" style="font-size:11px;" id="viewVerifiedAt">-</div>
                                </div>
                                <div class="col-12">
                                    <div class="text-muted" style="font-size:10px;">Verified By</div>
                                    <div class="fw-semibold" style="font-size:11px;" id="viewVerifiedBy">-</div>
                                </div>
                            </div>

                            {{-- Rejection Reason --}}
                            <div class="col-12 mt-2" id="rejectionReasonSection" style="display:none;">
                                <div class="alert alert-danger mb-0 py-2 px-3">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="fas fa-exclamation-triangle text-danger mt-1" style="font-size:11px;"></i>
                                        <div>
                                            <div class="fw-semibold" style="font-size:10px;">Rejection Reason</div>
                                            <div style="font-size:11px;" id="viewRejectionReason">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- FOOTER --}}
            <div class="modal-footer border-0 bg-white px-4 pb-4 pt-2 gap-2">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
                <button type="button" class="btn btn-primary btn-sm px-4"
                        data-verification-id="{{$verification->id}}" id="approveVerificationBtn">
                    <i class="fas fa-check me-1"></i>Approve
                </button>
            </div>

        </div>
    </div>
</div>