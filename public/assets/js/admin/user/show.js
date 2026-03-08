document.addEventListener('DOMContentLoaded', function() {
    const viewUserModal = document.getElementById('viewUserModal');

    if (!viewUserModal) {
        console.error('View user modal not found - viewUserModal element missing from HTML');
        return;
    }


    document.addEventListener('click', function(e) {
        // Check if clicked element is a View button
        if (e.target.closest('.btn-action[title="View"]')) {
            const button = e.target.closest('.btn-action[title="View"]');
            const userId = button.getAttribute('data-user-id');

            if (userId) {
                viewUser(userId);
            } else {
                console.error('View button clicked but no user ID found');
            }
        }
    });




    //Para ni sa pag link sa update button padung sa show.js modal
    // NEW: Click listener for Edit button in view modal
    document.addEventListener('click', function(e) {
        // Check if clicked element is the Edit button in the view modal
        if (e.target.closest('#editUserBtn')) {
            const button = e.target.closest('#editUserBtn');
            const userId = button.getAttribute('data-user-id');

            if (userId) {
                // Close the view modal first
                const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewUserModal'));
                if (viewModal) {
                    viewModal.hide();
                }

                // Call the editUser function from edit.js
                editUser(userId);
            } else {
                console.error('Edit button clicked but no user ID found');
                showError('Unable to edit user: User ID not found');
            }
        }
    });


});


async function viewUser(userId) {

    try {
        Swal.fire({
            title: 'Loading ....',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
            showConfirmButton: false
        });


        const response = await fetch(`/admin/user/${userId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        });


        const data = await response.json();
        console.log(data.user)
        Swal.close()

        if (response.ok || data.success) {
            populateUserModal(data.user);
            openViewModal();
        } else {
            showError(data.message || 'Failed to load user data')
        }


    } catch (error) {
        Swal.close();
        console.error('Error loading user:', error);
        showError('An error occurred while loading user data');
        console.log(error)
    }

}

function populateUserModal(userData) {
    document.getElementById('view-user-name').textContent = userData.name || 'Not provided';
    document.getElementById('view-user-email').textContent = userData.email || 'Not provided';
    document.getElementById('view-user-phone').textContent = userData.phone || 'Not provided';
    document.getElementById('view-user-role').textContent = userData.role || 'Not assigned';
    document.getElementById('view-user-created-at').textContent =
        userData.created_at ? new Date(userData.created_at).toLocaleString() : 'Not available';
    document.getElementById('view-user-updated-at').textContent =
        userData.updated_at ? new Date(userData.updated_at).toLocaleString() : 'Not available'; // Fixed: updated_at not _updated_at

    //para e link ang button para sa edit.js
    const editBtn = document.getElementById('editUserBtn');
    if (editBtn && userData.id) {
        editBtn.setAttribute('data-user-id', userData.id);
    }
}

function openViewModal() {
    const viewModalElement = document.getElementById('viewUserModal');

    if (!viewModalElement) {
        console.error('View modal element not found');
        showError('Unable to open view modal form');
        return; // Exit if modal doesn't exist
    }

    // Get or create Bootstrap modal instance
    let viewModal = bootstrap.Modal.getInstance(viewModalElement);

    if (!viewModal) {
        viewModal = new bootstrap.Modal(viewModalElement); // Don't forget "new"!
    }

    viewModal.show();
}

function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message,
        confirmButtonColor: '#3085d6'
    });
}