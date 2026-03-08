document.addEventListener('DOMContentLoaded', function() {
    console.log('Delete car script initialized successfully');

    // Event listener for Delete buttons
    document.addEventListener('click', function(e) {
        // Fix: Changed from .btn_action to .btn-action (with dash, not underscore)
        if (e.target.closest('.btn-action[title="Delete"]')) {
            const button = e.target.closest('.btn-action[title="Delete"]');
            const carId = button.getAttribute('data-car-id');
            const carPlateNumber = button.closest('tr').querySelector('.car-plate-number').textContent || 'this car';

            if (carId) {
                confirmDeleteCar(carId, carPlateNumber);
            } else {
                console.error('Delete button clicked but no car ID found');
            }

            console.log(carId)
        }
    });
});

async function confirmDeleteCar(carId, carPlateNumber) {
    try {
        const result = await Swal.fire({
            title: 'Are you sure?',
            html: `You are about to delete <strong>${carPlateNumber}</strong>. This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            showLoaderOnConfirm: true,
            preConfirm: async() => {
                try {
                    const response = await fetch(`/admin/cars/${carId}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': getCsrfToken()
                        }
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Failed to delete car');
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
                removeCarRow(carId);

                Swal.fire({
                    title: 'Deleted!',
                    text: result.value.message || 'Car has been deleted successfully.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    didClose: () => {
                        location.reload(); // 🔄 reload page
                    }
                });

            } else {
                showError(result.value.message || 'Failed to delete car');
            }
        }

    } catch (error) {
        console.error('Error in delete process:', error);
        showError('An unexpected error occurred');
    }
}

function removeCarRow(carId) {
    // Find the table row containing this user ID
    const deleteButton = document.querySelector(`.btn-action[title="Delete"][data-car-id="${carId}"]`);

    if (deleteButton) {
        const row = deleteButton.closest('tr');
        if (row) {
            // Add fade out animation
            row.style.transition = 'opacity 0.5s ease';
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

function updateEntriesCount() {
    const rows = document.querySelectorAll('tbody tr');
    const showingElement = document.querySelector('.showing-entries');

    if (showingElement && rows.length > 0) {
        const totalRows = rows.length;
        showingElement.textContent = `Showing 1-${totalRows} of ${totalRows}`;
    }
}