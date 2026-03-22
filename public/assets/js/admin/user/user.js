// Admin User Management (self-contained, no external helper required)

console.log('[admin/user/user.js] loaded');

document.addEventListener('DOMContentLoaded', () => {
    console.log('[admin/user/user.js] DOMContentLoaded');
    const addModalEl = document.getElementById('addUserModal');
    const editModalEl = document.getElementById('editUserModal');
    const viewModalEl = document.getElementById('viewUserModal');

    const addForm = document.getElementById('addUserForm');
    const editForm = document.getElementById('editUserForm');

    const createBtn = document.getElementById('saveUserBtn');
    const updateBtn = document.getElementById('updateUserBtn');

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

    function serializeForm(form) {
        const fd = new FormData(form);
        return Object.fromEntries(fd.entries());
    }

    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = (value !== null && value !== undefined) ? value : '—';
    }

    // CREATE
    if (createBtn && addForm) {
        createBtn.addEventListener('click', async(e) => {
            e.preventDefault();
            if (!addForm.checkValidity()) {
                addForm.classList.add('was-validated');
                return;
            }

            const payload = serializeForm(addForm);
            showLoading('Creating user...');
            const { res, data } = await apiRequest('/admin/user', { method: 'POST', body: payload });

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
                toastError(data.message || 'Failed to create user.');
            }
        });
    }

    // UPDATE
    if (updateBtn && editForm) {
        updateBtn.addEventListener('click', async(e) => {
            e.preventDefault();
            if (!editForm.checkValidity()) {
                editForm.classList.add('was-validated');
                return;
            }

            const userId = document.getElementById('editUserId').value;
            if (!userId) return CRS_ADMIN.toastError('Missing user id.');

            const payload = serializeForm(editForm);

            // If password empty, remove it to keep current password
            if (!payload.password) {
                delete payload.password;
                delete payload.password_confirmation;
            }

            showLoading('Updating user...');
            const { res, data } = await apiRequest(`/admin/user/${userId}`, { method: 'PUT', body: payload });

            if (res.ok && data.success) {
                if (editModal) editModal.hide();
                await toastSuccess(data.message);
                window.location.reload();
                return;
            }

            Swal.close();
            if (data.errors && typeof displayValidationErrors === 'function') {
                displayValidationErrors(data.errors);
            } else {
                toastError(data.message || 'Failed to update user.');
            }
        });
    }

    // Delegated actions: view/edit/delete
    document.addEventListener('click', async(e) => {
        const viewBtn = e.target.closest('.btn-action[title="View"]');
        const editBtn = e.target.closest('.btn-action[title="Edit"]');
        const delBtn = e.target.closest('.btn-action[title="Delete"]');

        const btn = viewBtn || editBtn || delBtn;
        if (!btn) return;

        const userId = btn.getAttribute('data-user-id');
        if (!userId) return;

        // VIEW
        if (viewBtn) {
            showLoading('Loading user...');
            const { res, data } = await apiRequest(`/admin/user/${userId}`, { method: 'GET' });
            Swal.close();
            if (!res.ok) return toastError(data.message || 'Failed to load user.');

            const user = data.user || data.data || data;

            setText('view-user-name', user.name || '—');
            setText('view-user-role', user.role || '—');
            setText('view-user-email', user.email || '—');
            setText('view-user-phone', user.phone || '—');
            setText('view-user-created-at', user.created_at ? new Date(user.created_at).toLocaleString() : '—');
            setText('view-user-updated-at', user.updated_at ? new Date(user.updated_at).toLocaleString() : '—');

            const editFromView = document.getElementById('editUserBtn');
            if (editFromView) editFromView.setAttribute('data-user-id', userId);

            if (viewModal) viewModal.show();
            return;
        }

        // EDIT
        if (editBtn) {
            showLoading('Loading user...');
            const { res, data } = await apiRequest(`/admin/user/${userId}/edit`, { method: 'GET' });
            Swal.close();
            if (!res.ok || !data.success) return toastError(data.message || 'Failed to load user.');

            const user = data.user || data.data;
            document.getElementById('editUserId').value = user.id;
            document.getElementById('editName').value = user.name || '';
            document.getElementById('editEmail').value = user.email || '';
            document.getElementById('editPhone').value = user.phone || '';
            document.getElementById('editRole').value = user.role || '';
            document.getElementById('editPassword').value = '';
            document.getElementById('editConfirmPassword').value = '';

            editForm.classList.remove('was-validated');
            if (editModal) editModal.show();
            return;
        }

        // DELETE
        if (delBtn) {
            const userName = btn.closest('tr').querySelector('.user-name').textContent || 'this user';
            const result = await Swal.fire({
                title: 'Delete user?',
                html: `You are about to delete <strong>${userName}</strong>. This cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Delete',
            });
            if (!result.isConfirmed) return;

            showLoading('Deleting...');
            const { res, data } = await apiRequest(`/admin/user/${userId}`, { method: 'DELETE' });
            if (res.ok && data.success) {
                await toastSuccess(data.message);
                window.location.reload();
                return;
            }

            Swal.close();
            toastError(data.message || 'Failed to delete user.');
        }
    });

    // Edit from view modal button
    document.addEventListener('click', async(e) => {
        const btn = e.target.closest('#editUserBtn');
        if (!btn) return;
        const userId = btn.getAttribute('data-user-id');
        if (!userId) return;
        if (viewModal) viewModal.hide();

        showLoading('Loading user...');
        const { res, data } = await apiRequest(`/admin/user/${userId}/edit`, { method: 'GET' });
        Swal.close();
        if (!res.ok || !data.success) return toastError(data.message || 'Failed to load user.');

        const user = data.user || data.data;
        document.getElementById('editUserId').value = user.id;
        document.getElementById('editName').value = user.name || '';
        document.getElementById('editEmail').value = user.email || '';
        document.getElementById('editPhone').value = user.phone || '';
        document.getElementById('editRole').value = user.role || '';
        document.getElementById('editPassword').value = '';
        document.getElementById('editConfirmPassword').value = '';

        editForm.classList.remove('was-validated');
        if (editModal) editModal.show();
    });
});