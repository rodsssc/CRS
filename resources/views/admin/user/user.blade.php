<x-app-layout>
    <div class="container-fluid px-4 py-3">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2 class="page-title mb-0">User Management</h2>
            </div>
            <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-plus me-1"></i>Add User
            </button>
        </div>
       
        <!-- Stats Cards -->
        <div class="row g-2 mb-3">
            <div class="col-6 col-lg">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-soft">
                        <i class="fas fa-users text-primary"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Users</div>
                        <div class="stat-value">{{ $stats['total'] }}</div>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-lg">
                <div class="stat-card">
                    <div class="stat-icon bg-danger-soft">
                        <i class="fas fa-user-shield text-danger"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Admins</div>
                        <div class="stat-value">{{ $stats['admin'] }}</div>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-lg">
                <div class="stat-card">
                    <div class="stat-icon bg-warning-soft">
                        <i class="fas fa-crown text-warning"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Owners</div>
                        <div class="stat-value">{{ $stats['owner'] }}</div>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-lg">
                <div class="stat-card">
                    <div class="stat-icon bg-success-soft">
                        <i class="fas fa-user-tie text-success"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Staff</div>
                        <div class="stat-value">{{ $stats['staff'] }}</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg">
                <div class="stat-card">
                    <div class="stat-icon bg-info-soft">
                        <i class="fas fa-user text-info"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Clients</div>
                        <div class="stat-value">{{ $stats['client'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="table-card">
            <div class="table-controls">
                <form method="GET" action="{{ route('admin.user.index') }}" class="row g-2 align-items-center">

                    <!-- Search Bar -->
                    <div class="col-md-6 col-lg-5">
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text"
                                class="form-control form-control-sm"
                                name="q"
                                value="{{ $q ?? '' }}"
                                placeholder="Search users..."
                                autocomplete="off">
                        </div>
                    </div>

                    <!-- Search & Clear Buttons -->
                    <div class="col-auto d-flex gap-2">
                        <button class="btn btn-primary btn-sm" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.user.index') }}">Clear</a>
                    </div>

                    <!-- Role Filter -->
                    <div class="col-md-3 col-lg-2">
                        <select class="form-select form-select-sm" name="role" onchange="this.form.submit()">
                            <option value="">All Roles</option>
                            <option value="admin"  @selected(($role ?? '') === 'admin')>Admin</option>
                            <option value="owner"  @selected(($role ?? '') === 'owner')>Owner</option>
                            <option value="staff"  @selected(($role ?? '') === 'staff')>Staff</option>
                            <option value="client" @selected(($role ?? '') === 'client')>Client</option>
                        </select>
                    </div>

                    <!-- Entries Per Page -->
                    <div class="col-md-2 col-lg-2 ms-lg-auto">
                        <select class="form-select form-select-sm" name="per_page" onchange="this.form.submit()">
                            <option value="10" @selected(($perPage ?? 10) == 10)>10 per page</option>
                            <option value="25" @selected(($perPage ?? 10) == 25)>25 per page</option>
                            <option value="50" @selected(($perPage ?? 10) == 50)>50 per page</option>
                        </select>
                    </div>

                </form>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table custom-table mb-0">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th width="15%">Phone</th>
                            <th width="12%">Role</th>
                            <th width="10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>
                                    <span class="user-name">#00{{ $user->id }}</span>
                                </td>
                                <td>
                                    <span class="user-name">{{ $user->name }}</span>
                                </td>
                                <td class="text-muted">{{ $user->email }}</td>
                                <td class="text-muted">{{ $user->phone }}</td>
                                <td>
                                    @if ($user->role === 'admin')
                                        <span class="role-tag role-admin">{{ $user->role }}</span>
                                    @elseif ($user->role === 'owner')
                                        <span class="role-tag role-owner">{{ $user->role }}</span>
                                    @elseif ($user->role === 'staff')
                                        <span class="role-tag role-staff">{{ $user->role }}</span>
                                    @elseif ($user->role === 'client')
                                        <span class="role-tag role-client">{{ $user->role }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action" title="View" data-user-id="{{ $user->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-action" title="Edit" data-user-id="{{ $user->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-action" title="Delete" data-user-id="{{ $user->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="fas fa-inbox fs-4 mb-2 d-block"></i>
                                    No users found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                    <div class="text-muted small">
                        Showing {{ $users->firstItem() }}–{{ $users->lastItem() }}
                        of {{ $users->total() }} users
                    </div>
                    <div>
                        {{ $users->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @else
                <div class="px-3 py-2 border-top text-muted small">
                    {{ $users->total() }} user(s) found
                </div>
            @endif

        </div>
    </div>
</x-app-layout>


<script src="{{ asset('assets/js/admin/user/user.js') }}"></script>


{{-- Add User Modal --}}
<div class="modal fade" tabindex="-1" id="addUserModal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">

            <div class="modal-header bg-header border-0 px-4 py-3">
                <div>
                    <h6 class="modal-title text-white fw-bold mb-0">
                        <i class="fas fa-user-plus me-2"></i>Add New User
                    </h6>
                    <p class="text-white-50 mb-0" style="font-size:11px;">Fill in the user account details below</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-3 bg-light">
                <form id="addUserForm" novalidate>
                    @csrf

                    <div class="bg-white border rounded-3 p-3 mb-2">
                        <div class="text-muted fw-semibold mb-2" style="font-size:10px; letter-spacing:.06em;">
                            BASIC INFORMATION
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Full Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-sm"
                                       id="name" name="name"
                                       placeholder="Enter full name"
                                       minlength="2" maxlength="50" required>
                                <div class="invalid-feedback">Please enter a valid full name (2-50 characters).</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Email Address <span class="text-danger">*</span>
                                </label>
                                <input type="email" class="form-control form-control-sm"
                                       id="email" name="email"
                                       placeholder="user@example.com"
                                       maxlength="100" required>
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">Phone Number</label>
                                <input type="tel" class="form-control form-control-sm"
                                       id="phone" name="phone"
                                       placeholder="+63 912345678"
                                       minlength="9" maxlength="20">
                                <div class="invalid-feedback">Please enter a valid phone number.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    User Role <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-sm" id="role" name="role" required>
                                    <option value="">Select a role</option>
                                    <option value="admin">Admin</option>
                                    <option value="owner">Owner</option>
                                    <option value="staff">Staff</option>
                                    <option value="client">Client</option>
                                </select>
                                <div class="invalid-feedback">Please select a user role.</div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border rounded-3 p-3">
                        <div class="text-muted fw-semibold mb-2" style="font-size:10px; letter-spacing:.06em;">
                            PASSWORD
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Password <span class="text-danger">*</span>
                                </label>
                                <input type="password" class="form-control form-control-sm"
                                       id="password" name="password"
                                       placeholder="Create a password"
                                       minlength="8"
                                       pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]{8,}$"
                                       required>
                                <div class="text-muted mt-1" style="font-size:10px;">Min 8 characters with letters and numbers.</div>
                                <div class="invalid-feedback">Password must be at least 8 characters with letters and numbers.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Confirm Password <span class="text-danger">*</span>
                                </label>
                                <input type="password" class="form-control form-control-sm"
                                       id="confirmPassword" name="password_confirmation"
                                       placeholder="Confirm your password"
                                       minlength="8" required>
                                <div class="invalid-feedback">Passwords do not match.</div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

            <div class="modal-footer border-0 bg-white px-4 pb-4 pt-2 gap-2">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary btn-sm px-4" id="saveUserBtn">
                    <i class="fas fa-save me-1"></i>Create User
                </button>
            </div>

        </div>
    </div>
</div>


{{-- Edit User Modal --}}
<div class="modal fade" tabindex="-1" id="editUserModal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">

            <div class="modal-header bg-header border-0 px-4 py-3">
                <div>
                    <h6 class="modal-title text-white fw-bold mb-0">
                        <i class="fas fa-user-edit me-2"></i>Edit User
                    </h6>
                    <p class="text-white-50 mb-0" style="font-size:11px;">Update the user account details</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-3 bg-light">
                <form id="editUserForm" novalidate>
                    @method('PUT')
                    @csrf
                    <input type="hidden" id="editUserId" name="id">

                    <div class="bg-white border rounded-3 p-3 mb-2">
                        <div class="text-muted fw-semibold mb-2" style="font-size:10px; letter-spacing:.06em;">
                            BASIC INFORMATION
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Full Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-sm"
                                       id="editName" name="name"
                                       placeholder="Enter full name"
                                       minlength="2" maxlength="50" required>
                                <div class="invalid-feedback">Please enter a valid full name (2-50 characters).</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Email Address <span class="text-danger">*</span>
                                </label>
                                <input type="email" class="form-control form-control-sm"
                                       id="editEmail" name="email"
                                       placeholder="user@example.com"
                                       maxlength="100" required>
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">Phone Number</label>
                                <input type="tel" class="form-control form-control-sm"
                                       id="editPhone" name="phone"
                                       placeholder="+63 912345678"
                                       minlength="9" maxlength="20">
                                <div class="invalid-feedback">Please enter a valid phone number.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    User Role <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-sm" id="editRole" name="role" required>
                                    <option value="">Select a role</option>
                                    <option value="admin">Admin</option>
                                    <option value="owner">Owner</option>
                                    <option value="staff">Staff</option>
                                    <option value="client">Client</option>
                                </select>
                                <div class="invalid-feedback">Please select a user role.</div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border rounded-3 p-3">
                        <div class="text-muted fw-semibold mb-2" style="font-size:10px; letter-spacing:.06em;">
                            PASSWORD
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Password <span class="text-muted fw-normal">(Leave blank to keep current)</span>
                                </label>
                                <input type="password" class="form-control form-control-sm"
                                       id="editPassword" name="password"
                                       placeholder="Enter new password"
                                       minlength="8"
                                       pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]{8,}$">
                                <div class="text-muted mt-1" style="font-size:10px;">Min 8 characters with letters and numbers.</div>
                                <div class="invalid-feedback">Password must be at least 8 characters with letters and numbers.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">Confirm Password</label>
                                <input type="password" class="form-control form-control-sm"
                                       id="editConfirmPassword" name="password_confirmation"
                                       placeholder="Confirm new password"
                                       minlength="8">
                                <div class="invalid-feedback">Passwords do not match.</div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

            <div class="modal-footer border-0 bg-white px-4 pb-4 pt-2 gap-2">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary btn-sm px-4" id="updateUserBtn">
                    <i class="fas fa-save me-1"></i>Update User
                </button>
            </div>

        </div>
    </div>
</div>


{{-- View User Modal --}}
<div class="modal fade" tabindex="-1" id="viewUserModal">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">

            <div class="modal-header bg-header border-0 px-4 py-3">
                <div>
                    <h6 class="modal-title text-white fw-bold mb-0">
                        <i class="fas fa-search-plus me-2"></i>View User Details
                    </h6>
                    <p class="text-white-50 mb-0" style="font-size:11px;">User account information</p>
                </div>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="close"></button>
            </div>

            <div class="modal-body p-3 bg-light">

                <div class="bg-white border rounded-3 p-3 mb-2">
                    <div class="text-muted fw-semibold mb-2" style="font-size:10px; letter-spacing:.06em;">
                        BASIC INFORMATION
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="text-muted" style="font-size:10px;">Full Name</div>
                            <div class="fw-semibold" style="font-size:11px;" id="view-user-name">Loading...</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted" style="font-size:10px;">User Role</div>
                            <div class="fw-semibold" style="font-size:11px;" id="view-user-role">Loading...</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted" style="font-size:10px;">Email Address</div>
                            <div class="fw-semibold" style="font-size:11px;" id="view-user-email">Loading...</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted" style="font-size:10px;">Phone Number</div>
                            <div class="fw-semibold" style="font-size:11px;" id="view-user-phone">Loading...</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white border rounded-3 p-3">
                    <div class="text-muted fw-semibold mb-2" style="font-size:10px; letter-spacing:.06em;">
                        ACCOUNT INFORMATION
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="text-muted" style="font-size:10px;">Account Created</div>
                            <div class="fw-semibold" style="font-size:11px;" id="view-user-created-at">Loading...</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted" style="font-size:10px;">Last Updated</div>
                            <div class="fw-semibold" style="font-size:11px;" id="view-user-updated-at">Loading...</div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer border-0 bg-white px-4 pb-4 pt-2 gap-2">
                <button class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
                <button class="btn btn-primary btn-sm px-4" id="editUserBtn" data-user-id="">
                    <i class="fas fa-edit me-1"></i>Edit User
                </button>
            </div>

        </div>
    </div>
</div>