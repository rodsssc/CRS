document.addEventListener('DOMContentLoaded', function() {
    console.log('Delete user script initialized successfully');

    // Event listener for Delete buttons
    document.addEventListener('click', function(e) {
        // Fix: Changed from .btn_action to .btn-action (with dash, not underscore)
        if (e.target.closest('.btn-action[title="Delete"]')) {
            const button = e.target.closest('.btn-action[title="Delete"]');
            const userId = button.getAttribute('data-user-id');
            const userName = button.closest('tr').querySelector('.user-name').textContent || 'this user';

            if (userId) {
                confirmDeleteUser(userId, userName);
            } else {
                console.error('Delete button clicked but no user ID found');
            }
        }
    });
});

/**
 * Show confirmation dialog and delete user
 * @param {string} userId - ID of user to delete
 * @param {string} userName - Name of user for display
 */
async function confirmDeleteUser(userId, userName) {
    try {
        const result = await Swal.fire({
            title: 'Are you sure?',
            html: `You are about to delete <strong>${userName}</strong>. This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            showLoaderOnConfirm: true,
            preConfirm: async() => {
                try {
                    const response = await fetch(`/admin/user/${userId}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': getCsrfToken()
                        }
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Failed to delete user');
                    }

                    return data;
                } catch (error) {
                    Swal.showValidationMessage(`Delete failed: ${error.message}`);
                }
            },
            allowOutsideClick: () => !Swal.isLoading()
        });

        // Handle the result
        if (result.isConfirmed) {
            if (result.value && result.value.success) {
                // Success - remove the row from table
                removeUserRow(userId);

                Swal.fire({
                    title: 'Deleted!',
                    text: result.value.message || 'User has been deleted successfully.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                showError(result.value.message || 'Failed to delete user');
            }
        }

    } catch (error) {
        console.error('Error in delete process:', error);
        showError('An unexpected error occurred');
    }
}

/**
 * Remove user row from table after successful deletion
 * @param {string} userId - ID of user to remove
 */
function removeUserRow(userId) {
    // Find the table row containing this user ID
    const deleteButton = document.querySelector(`.btn-action[title="Delete"][data-user-id="${userId}"]`);

    if (deleteButton) {
        const row = deleteButton.closest('tr');
        if (row) {
            // Add fade out animation
            row.style.transition = 'opacity 0.3s ease';
            row.style.opacity = '0';

            // Remove row after animation
            setTimeout(() => {
                row.remove();

                // Update the "Showing X of X" text
                updateEntriesCount();


            }, 300);
        }
    }
}

/**
 * Update the entries count in table footer
 */
function updateEntriesCount() {
    const rows = document.querySelectorAll('tbody tr');
    const showingElement = document.querySelector('.showing-entries');

    if (showingElement && rows.length > 0) {
        const totalRows = rows.length;
        showingElement.textContent = `Showing 1-${totalRows} of ${totalRows}`;
    }
}