/**
 * ============================================================================
 * EDIT USER SCRIPT - FIXED VERSION
 * ============================================================================
 * 
 * Purpose: Handles loading user data into the edit modal
 * 
 * Flow:
 * 1. User clicks "Edit" button on table row
 * 2. Fetch user data from server via API
 * 3. Populate edit form with user data
 * 4. Open edit modal
 * 5. Reset form when modal closes
 * 
 * Dependencies: Bootstrap 5, SweetAlert2
 * ============================================================================
 */

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Get CSRF token from meta tag
 * @returns {string} CSRF token value
 */
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

// ============================================================================
// EVENT LISTENERS
// ============================================================================

/**
 * Initialize edit user functionality when DOM is ready
 */
document.addEventListener('DOMContentLoaded', function() {

    // ========================================================================
    // VERIFY REQUIRED ELEMENTS EXIST
    // ========================================================================
    const editUserModal = document.getElementById('editUserModal');
    const editUserForm = document.getElementById('editUserForm');

    if (!editUserModal) {
        console.error('Edit user modal not found - editUserModal element missing from HTML');
        return;
    }

    if (!editUserForm) {
        console.error('Edit user form not found - editUserForm element missing from HTML');
        return;
    }

    console.log('Edit user script initialized successfully');

    // ========================================================================
    // EVENT LISTENER: Edit Button Clicks
    // ========================================================================
    /**
     * Listen for clicks on Edit buttons
     * Uses event delegation for dynamically loaded buttons
     */
    document.addEventListener('click', function(e) {
        // Check if clicked element is an Edit button
        if (e.target.closest('.btn-action[title="Edit"]')) {
            const button = e.target.closest('.btn-action[title="Edit"]');
            const userId = button.getAttribute('data-user-id');

            if (userId) {
                editUser(userId);
            } else {
                console.error('Edit button clicked but no user ID found');
            }
        }
    });

    // ========================================================================
    // EVENT LISTENER: Modal Close
    // ========================================================================
    /**
     * Reset form when edit modal is closed
     * Cleans up validation states and form data
     */
    editUserModal.addEventListener('hidden.bs.modal', function() {
        resetEditForm();
    });
});

// ============================================================================
// MAIN FUNCTIONS
// ============================================================================

/**
 * Fetch user data and populate edit form
 * @param {number} userId - ID of user to edit
 */
async function editUser(userId) {
    try {
        // Show loading indicator
        Swal.fire({
            title: 'Loading user data ....',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
            showConfirmButton: false
        });

        // Fetch user data from API
        const response = await fetch(`/admin/user/${userId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        });

        const data = await response.json();

        // Close loading indicator
        Swal.close();

        // Check if request was successful
        if (response.ok && data.success) {
            populateEditForm(data.user);
            openEditModal();
        } else {
            showError(data.message || 'Failed to load user data');
        }

    } catch (error) {
        Swal.close();
        console.error('Error loading user:', error);
        showError('An error occurred while loading user data');
    }
}

/**
 * Populate edit form with user data
 * @param {Object} user - User object from API
 */
function populateEditForm(user) {
    // Verify all form fields exist before populating
    const fields = {
        editUserId: document.getElementById('editUserId'),
        editName: document.getElementById('editName'),
        editEmail: document.getElementById('editEmail'),
        editPhone: document.getElementById('editPhone'),
        editRole: document.getElementById('editRole'),
        editPassword: document.getElementById('editPassword'),
        editConfirmPassword: document.getElementById('editConfirmPassword')
    };

    // Check if any required fields are missing
    const missingFields = Object.entries(fields)
        .filter(([name, element]) => !element)
        .map(([name]) => name);

    if (missingFields.length > 0) {
        console.error('Missing form fields:', missingFields.join(', '));
        showError('Edit form is incomplete. Please contact administrator.');
        return;
    }

    // Populate basic fields
    fields.editUserId.value = user.id;
    fields.editName.value = user.name;
    fields.editEmail.value = user.email;
    fields.editPhone.value = user.phone || '';
    fields.editRole.value = user.role;

    // Clear password fields (passwords are optional when editing)
    fields.editPassword.value = '';
    fields.editConfirmPassword.value = '';

    // Remove required attributes (password is optional for updates)
    fields.editPassword.removeAttribute('required');
    fields.editConfirmPassword.removeAttribute('required');

    // // Clean up validation states
    // resetEditForm();
}


/**
 * Open the edit modal
 */
function openEditModal() {
    const editModalElement = document.getElementById('editUserModal');

    if (!editModalElement) {
        console.error('Edit modal element not found');
        showError('Unable to open edit form');
        return;
    }

    // Get or create Bootstrap modal instance
    let editModal = bootstrap.Modal.getInstance(editModalElement);

    if (!editModal) {
        editModal = new bootstrap.Modal(editModalElement);
    }

    editModal.show();
}

/**
 * Show error message to user
 * @param {string} message - Error message to display
 */
function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message,
        confirmButtonColor: '#3085d6'
    });
}