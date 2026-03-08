// ============================================================================
// UPDATE USER SCRIPT
// ============================================================================

function validateUserForm(data) {
    if (!data.name || !data.email || !data.role) {
        Swal.fire('Validation Error', 'Please fill in required fields', 'error');
        return false;
    }

    if (data.password && data.password !== data.confirm) {
        Swal.fire('Validation Error', 'Passwords do not match', 'error');
        return false;
    }

    return true;
}

document.addEventListener('DOMContentLoaded', () => {
    const updateUserBtn = document.getElementById('updateUserBtn');
    if (!updateUserBtn) return;

    updateUserBtn.addEventListener('click', async() => {

        // ====================================================================
        // STEP 1: Collect Form Data
        // ====================================================================
        const form = {
            userId: document.getElementById('editUserId').value,
            name: document.getElementById('editName').value.trim(),
            email: document.getElementById('editEmail').value.trim(),
            phone: document.getElementById('editPhone').value.trim(),
            role: document.getElementById('editRole').value,
            password: document.getElementById('editPassword').value,
            confirm: document.getElementById('editConfirmPassword').value
        };

        if (!validateUserForm(form)) return;

        // ====================================================================
        // STEP 2: Build Payload
        // ====================================================================
        const payload = {
            name: form.name,
            email: form.email,
            phone: form.phone || null,
            role: form.role
        };

        if (form.password) {
            payload.password = form.password;
            payload.password_confirmation = form.confirm;
        }

        // ====================================================================
        // STEP 3: Close Modal & Show Loader
        // ====================================================================
        closeModal('editUserModal');

        setTimeout(async() => {
            Swal.fire({
                title: 'Updating user...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
                showConfirmButton: false
            });

            try {
                const response = await fetch(`/admin/user/${form.userId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    Swal.fire('Success', data.message || 'User updated', 'success')
                        .then(() => location.reload());
                } else {
                    data.errors ?
                        displayValidationErrors(data.errors) :
                        Swal.fire('Error', data.message || 'Update failed', 'error');
                }

            } catch (err) {
                Swal.fire('Server Error', 'Please try again.', 'error');
                console.error(err);
            }
        }, 300);
    });
});