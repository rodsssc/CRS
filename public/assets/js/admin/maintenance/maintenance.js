// Admin Maintenance CRUD (single file for support)

console.log('[admin/maintenance/maintenance.js] loaded');

document.addEventListener('DOMContentLoaded', () => {
  console.log('[admin/maintenance/maintenance.js] DOMContentLoaded');
  const addModalEl = document.getElementById('addMaintenanceModal');
  const editModalEl = document.getElementById('editMaintenanceModal');
  const viewModalEl = document.getElementById('viewMaintenanceModal');

  const addForm = document.getElementById('addMaintenanceForm');
  const editForm = document.getElementById('editMaintenanceForm');

  const saveBtn = document.getElementById('saveMaintenanceBtn');
  const updateBtn = document.getElementById('updateMaintenanceBtn');

  const addModal = addModalEl ? (bootstrap.Modal.getInstance(addModalEl) || new bootstrap.Modal(addModalEl)) : null;
  const editModal = editModalEl ? (bootstrap.Modal.getInstance(editModalEl) || new bootstrap.Modal(editModalEl)) : null;
  const viewModal = viewModalEl ? (bootstrap.Modal.getInstance(viewModalEl) || new bootstrap.Modal(viewModalEl)) : null;

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
    let data = {};
    try {
      const text = await res.text();
      data = text ? JSON.parse(text) : {};
    } catch (_) {
      data = { success: false, message: 'Invalid response from server.' };
    }
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
      text: message || 'Something went wrong. Please try again.',
      confirmButtonColor: '#3085d6',
    });
  }

  function getIdFromButton(e, attr) {
    const btn = e.target.closest(`.btn-action[${attr}]`);
    return btn ? btn.getAttribute(attr) : null;
  }

  // CREATE
  if (saveBtn && addForm) {
    saveBtn.addEventListener('click', async (e) => {
      e.preventDefault();
      if (!addForm.checkValidity()) {
        addForm.classList.add('was-validated');
        return;
      }

      const formData = new FormData(addForm);
      const payload = Object.fromEntries(formData.entries());

      showLoading('Creating...');
      const { res, data } = await apiRequest('/admin/maintenance', { method: 'POST', body: payload });

      if (res.ok && data.success) {
        addForm.reset();
        addForm.classList.remove('was-validated');
        if (addModal) addModal.hide();
        await toastSuccess(data.message);
        window.location.reload();
        return;
      }

      Swal.close();
      if (data && data.errors && typeof displayValidationErrors === 'function') {
        displayValidationErrors(data.errors);
      } else {
        toastError(data.message || 'Failed to create maintenance record.');
      }
    });
  }

  // VIEW / EDIT / DELETE (delegated)
  document.addEventListener('click', async (e) => {
    const viewId = getIdFromButton(e, 'data-maintenance-id');
    if (!viewId) return;

    // VIEW
    if (e.target.closest('.btn-action[title="View"]')) {
      showLoading('Loading...');
      const { res, data } = await apiRequest(`/admin/maintenance/${viewId}`, { method: 'GET' });
      Swal.close();
      if (!res.ok || !data.success) return toastError(data.message || 'Failed to load.');

      const m = data.data;
      document.getElementById('viewMaintenanceCar').textContent = `${m.car?.brand ?? ''} ${m.car?.model ?? ''}`.trim() || '—';
      document.getElementById('viewMaintenancePlate').textContent = m.car?.plate_number ?? '—';
      document.getElementById('viewMaintenanceDate').textContent = m.service_date ?? '—';
      document.getElementById('viewMaintenanceStatus').textContent = (m.status || '—').replace('_', ' ');
      document.getElementById('viewMaintenanceTitle').textContent = m.title ?? '—';
      document.getElementById('viewMaintenanceDescription').textContent = m.description ?? '—';
      document.getElementById('viewMaintenanceCost').textContent = `₱${Number(m.cost || 0).toFixed(2)}`;
      document.getElementById('viewMaintenanceCreator').textContent = m.creator?.name ?? '—';

      if (viewModal) viewModal.show();
      return;
    }

    // EDIT
    if (e.target.closest('.btn-action[title="Edit"]')) {
      showLoading('Loading...');
      const { res, data } = await apiRequest(`/admin/maintenance/${viewId}/edit`, { method: 'GET' });
      Swal.close();
      if (!res.ok || !data.success) return toastError(data.message || 'Failed to load.');

      const m = data.data;
      document.getElementById('editMaintenanceId').value = m.id;
      document.getElementById('editMaintenanceCarId').value = m.car_id;
      document.getElementById('editMaintenanceServiceDate').value = m.service_date;
      document.getElementById('editMaintenanceTitle').value = m.title ?? '';
      document.getElementById('editMaintenanceCost').value = m.cost ?? 0;
      document.getElementById('editMaintenanceStatus').value = m.status ?? 'scheduled';
      document.getElementById('editMaintenanceDescription').value = m.description ?? '';

      if (editForm) editForm.classList.remove('was-validated');
      if (editModal) editModal.show();
      return;
    }

    // DELETE
    if (e.target.closest('.btn-action[title="Delete"]')) {
      const result = await Swal.fire({
        title: 'Delete maintenance record?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Delete',
      });

      if (!result.isConfirmed) return;

      showLoading('Deleting...');
      const { res, data } = await apiRequest(`/admin/maintenance/${viewId}`, { method: 'DELETE' });

      if (res.ok && data.success) {
        await toastSuccess(data.message);
        window.location.reload();
        return;
      }

      Swal.close();
      toastError(data.message || 'Failed to delete.');
    }
  });

  // UPDATE
  if (updateBtn && editForm) {
    updateBtn.addEventListener('click', async (e) => {
      e.preventDefault();
      if (!editForm.checkValidity()) {
        editForm.classList.add('was-validated');
        return;
      }

      const id = document.getElementById('editMaintenanceId').value;
      const formData = new FormData(editForm);
      const payload = Object.fromEntries(formData.entries());

      showLoading('Updating...');
      const { res, data } = await apiRequest(`/admin/maintenance/${id}`, { method: 'PUT', body: payload });

      if (res.ok && data.success) {
        if (editModal) editModal.hide();
        await toastSuccess(data.message);
        window.location.reload();
        return;
      }

      Swal.close();
      if (data && data.errors && typeof displayValidationErrors === 'function') {
        displayValidationErrors(data.errors);
      } else {
        toastError(data.message || 'Failed to update.');
      }
    });
  }
});

