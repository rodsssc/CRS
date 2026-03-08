// Admin Car Management (self-contained)

console.log('[admin/car/car.js] loaded');

document.addEventListener('DOMContentLoaded', () => {
    console.log('[admin/car/car.js] DOMContentLoaded');
    const addForm = document.getElementById('addCarForm');
    const updateForm = document.getElementById('updateCarForm');

    const addModalEl = document.getElementById('addCarModal');
    const updateModalEl = document.getElementById('updateCarModal');
    const viewModalEl = document.getElementById('viewCarModal');

    const addModal = addModalEl ? (bootstrap.Modal.getInstance(addModalEl) || new bootstrap.Modal(addModalEl)) : null;
    const updateModal = updateModalEl ? (bootstrap.Modal.getInstance(updateModalEl) || new bootstrap.Modal(updateModalEl)) : null;
    const viewModal = viewModalEl ? (bootstrap.Modal.getInstance(viewModalEl) || new bootstrap.Modal(viewModalEl)) : null;

    const saveBtn = document.getElementById('saveCarBtn');
    const updateBtn = document.getElementById('updateCarBtn');

    async function apiRequest(url, { method = 'GET', body } = {}) {
        const init = {
            method,
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': typeof getCsrfToken === 'function' ? getCsrfToken() : '',
            },
        };

        if (body instanceof FormData) {
            init.body = body;
        } else if (body) {
            init.body = JSON.stringify(body);
            init.headers['Content-Type'] = 'application/json';
        }

        const res = await fetch(url, init);
        const data = await res.json().catch(() => ({}));
        return { res, data };
    }

    function showLoading(title) {
        Swal.fire({
            title: title || 'Processing...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
            showConfirmButton: false,
        });
    }

    function toastSuccess(message) {
        return Swal.fire({
            icon: 'success',
            title: 'Success',
            text: message || 'Done.',
            timer: 1500,
            showConfirmButton: false,
        });
    }

    function toastError(message) {
        return Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message || 'Something went wrong.',
        });
    }

    function setText(id, value, fallback = '—') {
        const el = document.getElementById(id);
        if (el) el.textContent = (value === null || value === undefined || value === '') ? fallback : value;
    }

    function formatMoney(value) {
        const n = Number(value || 0);
        return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function statusTagClass(status) {
        switch ((status || '').toLowerCase()) {
            case 'available':
                return 'status-available';
            case 'rented':
                return 'status-rented';
            case 'maintenance':
                return 'status-maintenance';
            default:
                return 'status-available';
        }
    }

    // CREATE
    if (saveBtn && addForm) {
        saveBtn.addEventListener('click', async(e) => {
            e.preventDefault();
            if (!addForm.checkValidity()) {
                addForm.classList.add('was-validated');
                return;
            }

            const fd = new FormData(addForm);
            showLoading('Adding car...');
            const { res, data } = await apiRequest('/admin/cars', { method: 'POST', body: fd });

            if (res.ok && data.success) {
                addForm.reset();
                addForm.classList.remove('was-validated');
                if (addModal) addModal.hide();
                await toastSuccess(data.message);
                window.location.reload();
                return;
            }

            Swal.close();
            if (data.errors && typeof displayValidationErrors === 'function') {
                displayValidationErrors(data.errors);
            } else {
                toastError(data.message || 'Failed to add car.');
            }
        });
    }

    // UPDATE
    if (updateBtn && updateForm) {
        updateBtn.addEventListener('click', async(e) => {
            e.preventDefault();
            if (!updateForm.checkValidity()) {
                updateForm.classList.add('was-validated');
                return;
            }

            const carId = document.getElementById('update_car_id').value;
            if (!carId) return toastError('Missing car id.');

            const fd = new FormData(updateForm);
            fd.append('_method', 'PUT'); // keep compatibility with existing server expectations

            showLoading('Updating car...');
            const { res, data } = await apiRequest(`/admin/cars/${carId}`, { method: 'POST', body: fd });

            if (res.ok && data.success) {
                if (updateModal) updateModal.hide();
                await toastSuccess(data.message);
                window.location.reload();
                return;
            }

            Swal.close();
            if (data.errors && typeof displayValidationErrors === 'function') {
                displayValidationErrors(data.errors);
            } else {
                toastError(data.message || 'Failed to update car.');
            }
        });
    }

    // Delegated view/edit/delete
    document.addEventListener('click', async(e) => {
        const viewBtn = e.target.closest('.btn-action[title="View"]');
        const editBtn = e.target.closest('.btn-action[title="Edit"]');
        const delBtn = e.target.closest('.btn-action[title="Delete"]');
        const btn = viewBtn || editBtn || delBtn;
        if (!btn) return;

        const carId = btn.getAttribute('data-car-id');
        if (!carId) return;

        // VIEW
        if (viewBtn) {
            showLoading('Loading...');
            const { res, data } = await apiRequest(`/admin/cars/${carId}`, { method: 'GET' });
            Swal.close();
            if (!res.ok || !data.success) return toastError(data.message || 'Failed to load car.');

            const car = data.data;

            const img = document.getElementById('viewCarImage');
            if (img) {
                img.src = car.image_path ? `/storage/${car.image_path}` : 'https://via.placeholder.com/500x350?text=No+Image';
            }

            const statusEl = document.getElementById('viewStatus');
            if (statusEl) {
                statusEl.textContent = (car.status || 'unknown').replace(/^./, (c) => c.toUpperCase());
                statusEl.className = `status-tag shadow-sm ${statusTagClass(car.status)}`;
            }

            setText('viewYear', car.year, 'N/A');
            setText('viewCapacity', car.capacity, 'N/A');
            setText('viewTransmission', car.transmission_type, 'N/A');
            setText('viewFuelType', car.fuel_type, 'N/A');
            setText('viewRentalPrice', formatMoney(car.rental_price_per_day), '0.00');

            setText('viewBrand', car.brand, 'N/A');
            setText('viewPlateNumber', car.plate_number, 'N/A');
            setText('viewOwnerName', car.owner.name ? 'N/A' : 'N/A');

            setText('viewPlateNumber2', car.plate_number, 'N/A');
            setText('viewBrand2', car.brand, 'N/A');
            setText('viewModel2', car.model, 'N/A');
            setText('viewColor', car.color, 'N/A');
            setText('viewYear2', car.year, 'N/A');
            setText('viewCapacity2', car.capacity, 'N/A');
            setText('viewTransmission2', car.transmission_type, 'N/A');
            setText('viewFuelType2', car.fuel_type, 'N/A');

            if (viewModal) viewModal.show();
            return;
        }

        // EDIT
        if (editBtn) {
            showLoading('Loading...');
            const { res, data } = await apiRequest(`/admin/cars/${carId}/edit`, { method: 'GET' });
            Swal.close();
            if (!res.ok || !data.success) return toastError(data.message || 'Failed to load car.');

            const car = data.data;

            document.getElementById('update_car_id').value = car.id;
            document.getElementById('updateOwnerId').value = car.owner_id;
            document.getElementById('updateBrand').value = car.brand || '';
            document.getElementById('updateModel').value = car.model || '';
            document.getElementById('updateYear').value = car.year || '';
            document.getElementById('updateColor').value = car.color || '';
            document.getElementById('updateCapacity').value = car.capacity || '';
            document.getElementById('updateTransmissionType').value = car.transmission_type || '';
            document.getElementById('updateFuelType').value = car.fuel_type || '';
            document.getElementById('updateRentalPricePerDay').value = car.rental_price_per_day || '';
            document.getElementById('update_plate_number').value = car.plate_number || '';
            document.getElementById('update_status').value = car.status || 'available';

            const preview = document.getElementById('current_image_preview');
            if (preview) {
                if (car.image_path) {
                    preview.src = `/storage/${car.image_path}`;
                    preview.style.display = 'block';
                } else {
                    preview.style.display = 'none';
                }
            }

            updateForm.classList.remove('was-validated');
            if (updateModal) updateModal.show();
            return;
        }

        // DELETE
        if (delBtn) {
            const plate = btn.closest('tr').querySelector('.car-plate-number').textContent.trim() || 'this car';
            const result = await Swal.fire({
                title: 'Delete car?',
                html: `You are about to delete <strong>${plate}</strong>. This cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Delete',
            });
            if (!result.isConfirmed) return;

            showLoading('Deleting...');
            const { res, data } = await apiRequest(`/admin/cars/${carId}`, { method: 'DELETE' });
            if (res.ok && data.success) {
                await toastSuccess(data.message);
                window.location.reload();
                return;
            }
            Swal.close();
            toastError(data.message || 'Failed to delete car.');
        }
    });
});