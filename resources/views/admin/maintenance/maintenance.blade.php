<x-app-layout>
    <div class="container-fluid px-4 py-3">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2 class="page-title mb-0">Maintenance</h2>
            </div>
            <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addMaintenanceModal">
                <i class="fas fa-plus me-1"></i>Add Maintenance
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="row g-2 mb-3">
            <div class="col-6 col-lg">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-soft">
                        <i class="fas fa-wrench text-primary"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total</div>
                        <div class="stat-value">{{ $stats['total'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg">
                <div class="stat-card">
                    <div class="stat-icon bg-warning-soft">
                        <i class="fas fa-calendar-alt text-warning"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Scheduled</div>
                        <div class="stat-value">{{ $stats['scheduled'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg">
                <div class="stat-card">
                    <div class="stat-icon bg-info-soft">
                        <i class="fas fa-tools text-info"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">In Progress</div>
                        <div class="stat-value">{{ $stats['in_progress'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg">
                <div class="stat-card">
                    <div class="stat-icon bg-success-soft">
                        <i class="fas fa-check-circle text-success"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Completed</div>
                        <div class="stat-value">{{ $stats['completed'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg">
                <div class="stat-card">
                    <div class="stat-icon bg-danger-soft">
                        <i class="fas fa-ban text-danger"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Cancelled</div>
                        <div class="stat-value">{{ $stats['cancelled'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="table-card">
            <div class="table-controls">
            <form method="GET" class="row g-2 align-items-center">

                {{-- Search --}}
                <div class="col-12 col-md-5 col-lg-4">
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text"
                            class="form-control form-control-sm"
                            name="q"
                            value="{{ $q ?? '' }}"
                            placeholder="Search plate, title, creator..."
                            autocomplete="off">
                    </div>
                </div>

                {{-- Search & Clear --}}
                <div class="col-auto d-flex gap-2">
                    <button class="btn btn-primary btn-sm" type="submit">Search</button>
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.payment.index') }}">Clear</a>
                </div>

                {{-- Status Filter --}}
                <div class="col-12 col-md-3 col-lg-2">
                    <select class="form-select form-select-sm" name="status" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="scheduled"   @selected(($status ?? '') === 'scheduled')>Scheduled</option>
                        <option value="in_progress" @selected(($status ?? '') === 'in_progress')>In Progress</option>
                        <option value="completed"   @selected(($status ?? '') === 'completed')>Completed</option>
                        <option value="cancelled"   @selected(($status ?? '') === 'cancelled')>Cancelled</option>
                    </select>
                </div>

                {{-- Car Filter --}}
                <div class="col-12 col-md-3 col-lg-3">
                    <select class="form-select form-select-sm" name="car_id" onchange="this.form.submit()">
                        <option value="">All Cars</option>
                        @foreach($cars as $car)
                            <option value="{{ $car->id }}" @selected(($carId ?? null) == $car->id)>
                                {{ $car->plate_number }} — {{ $car->brand }} {{ $car->model }}
                            </option>
                        @endforeach
                    </select>
                </div>

              
            </form>
        </div>

            <div class="table-responsive">
                <table class="table custom-table mb-0">
                    <thead>
                        
                        <tr>
                            <th>Car ID</th>
                            <th>Car</th>
                            <th>Plate</th>
                            <th>Service Date</th>
                            <th>Title</th>
                            <th>Cost</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($maintenances as $m)
                            <tr>
                                <td class="text-muted">#00{{ $m->car?->id }} </td>
                                <td class="text-muted">{{ $m->car?->brand }} {{ $m->car?->model }}</td>
                                <td class="text-muted">{{ $m->car?->plate_number ?? '—' }}</td>
                                <td class="text-muted">{{ $m->service_date?->format('M d, Y') ?? '—' }}</td>
                                <td class="fw-semibold">{{ $m->title }}</td>
                                <td class="text-muted">₱{{ number_format((float) $m->cost, 2) }}</td>
                                <td>
                                    @php
                                        $statusClass = match($m->status) {
                                            'scheduled' => 'status-pending',
                                            'in_progress' => 'status-ongoing',
                                            'completed' => 'status-approved',
                                            'cancelled' => 'status-rejected',
                                            default => 'status-pending',
                                        };
                                    @endphp
                                    <span class="status-tag {{ $statusClass }}">{{ str_replace('_',' ', ucfirst($m->status)) }}</span>
                                </td>
                                <td class="text-muted">{{ $m->creator?->name ?? '—' }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action" title="View" data-maintenance-id="{{ $m->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-action" title="Edit" data-maintenance-id="{{ $m->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-action" title="Delete" data-maintenance-id="{{ $m->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No maintenance records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <div class="showing-entries">
                    @if ($maintenances->total() > 0)
                        Showing {{ $maintenances->firstItem() }}-{{ $maintenances->lastItem() }} of {{ $maintenances->total() }}
                    @else
                        Showing 0 of 0
                    @endif
                </div>
                <div>
                    {{ $maintenances->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    window.__MAINTENANCE_CARS__ = @json($cars->map(fn($c) => [
        'id' => $c->id,
        'label' => trim(($c->plate_number ?? '') . ' — ' . ($c->brand ?? '') . ' ' . ($c->model ?? '')),
    ]));
</script>
<script src="{{ asset('assets/js/admin/maintenance/maintenance.js') }}"></script>

{{-- Add Maintenance Modal --}}
<div class="modal fade" tabindex="-1" id="addMaintenanceModal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">
            <div class="modal-header bg-header border-0 px-4 py-3">
                <div>
                    <h6 class="modal-title text-white fw-bold mb-0">
                        <i class="fas fa-wrench me-2"></i>Add Maintenance
                    </h6>
                    <p class="text-white-50 mb-0" style="font-size:11px;">Create a maintenance record</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-3 bg-light">
                <form id="addMaintenanceForm" novalidate>
                    @csrf
                    <div class="bg-white border rounded-3 p-3">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">Car <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" name="car_id" id="maintenanceCarId" required>
                                    <option value="">Select a car</option>
                                    @foreach($cars as $car)
                                        <option value="{{ $car->id }}">{{ $car->plate_number }} — {{ $car->brand }} {{ $car->model }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">Service Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-sm" name="service_date" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small fw-semibold text-muted mb-1">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="title" maxlength="120" required placeholder="Oil change, tire rotation...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted mb-1">Cost</label>
                                <input type="number" class="form-control form-control-sm" name="cost" min="0" step="0.01" placeholder="0.00">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">Status <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" name="status" required>
                                    <option value="scheduled">Scheduled</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold text-muted mb-1">Description</label>
                                <textarea class="form-control form-control-sm" name="description" rows="3" placeholder="Optional details..."></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer border-0 bg-white px-4 pb-4 pt-2 gap-2">
                <button type="button" class="btn btn-secondary " data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-bg-color " id="saveMaintenanceBtn">Create</button>
            </div>
        </div>
    </div>
</div>

{{-- Edit Maintenance Modal --}}
<div class="modal fade" tabindex="-1" id="editMaintenanceModal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">
            <div class="modal-header bg-header border-0 px-4 py-3">
                <div>
                    <h6 class="modal-title text-white fw-bold mb-0">
                        <i class="fas fa-edit me-2"></i>Edit Maintenance
                    </h6>
                    <p class="text-white-50 mb-0" style="font-size:11px;">Update maintenance record</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-3 bg-light">
                <form id="editMaintenanceForm" novalidate>
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="editMaintenanceId">
                    <div class="bg-white border rounded-3 p-3">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">Car <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" name="car_id" id="editMaintenanceCarId" required>
                                    <option value="">Select a car</option>
                                    @foreach($cars as $car)
                                        <option value="{{ $car->id }}">{{ $car->plate_number }} — {{ $car->brand }} {{ $car->model }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">Service Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-sm" name="service_date" id="editMaintenanceServiceDate" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small fw-semibold text-muted mb-1">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="title" id="editMaintenanceTitle" maxlength="120" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted mb-1">Cost</label>
                                <input type="number" class="form-control form-control-sm" name="cost" id="editMaintenanceCost" min="0" step="0.01">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1">Status <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" name="status" id="editMaintenanceStatus" required>
                                    <option value="scheduled">Scheduled</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold text-muted mb-1">Description</label>
                                <textarea class="form-control form-control-sm" name="description" id="editMaintenanceDescription" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer border-0 bg-white px-4 pb-4 pt-2 gap-2">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm px-4" id="updateMaintenanceBtn">Update</button>
            </div>
        </div>
    </div>
</div>

{{-- View Maintenance Modal --}}
<div class="modal fade" tabindex="-1" id="viewMaintenanceModal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">
            <div class="modal-header bg-header border-0 px-4 py-3">
                <div>
                    <h6 class="modal-title text-white fw-bold mb-0">
                        <i class="fas fa-eye me-2"></i>Maintenance Details
                    </h6>
                    <p class="text-white-50 mb-0" style="font-size:11px;">View maintenance record</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-3 bg-light">
                <div class="bg-white border rounded-3 p-3">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="text-muted" style="font-size:10px;">CAR</div>
                            <div class="fw-semibold" id="viewMaintenanceCar">—</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted" style="font-size:10px;">PLATE</div>
                            <div class="fw-semibold" id="viewMaintenancePlate">—</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted" style="font-size:10px;">SERVICE DATE</div>
                            <div class="fw-semibold" id="viewMaintenanceDate">—</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted" style="font-size:10px;">STATUS</div>
                            <div class="fw-semibold" id="viewMaintenanceStatus">—</div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted" style="font-size:10px;">TITLE</div>
                            <div class="fw-semibold" id="viewMaintenanceTitle">—</div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted" style="font-size:10px;">DESCRIPTION</div>
                            <div class="text-muted" id="viewMaintenanceDescription">—</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted" style="font-size:10px;">COST</div>
                            <div class="fw-semibold" id="viewMaintenanceCost">—</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted" style="font-size:10px;">CREATED BY</div>
                            <div class="fw-semibold" id="viewMaintenanceCreator">—</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 bg-white px-4 pb-4 pt-2 gap-2">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

