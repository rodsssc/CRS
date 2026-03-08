// reject.js


document.addEventListener('DOMContentLoaded', function() {
    console.log('Reject Verification script initialized successfully');

    const rejectBtn = document.getElementById('rejectVerificationBtn');

    if (rejectBtn) {
        rejectBtn.addEventListener('click', function() {
            const verificationId = this.getAttribute('data-verification-id');

            if (!verificationId) {
                console.error('No verification ID found');
                return;
            }

            confirmRejectVerification(verificationId);
        });
    }
});

// ========================================
// CONFIRM MODAL (NO INPUT FIELD)
// ========================================

async function confirmRejectVerification(verificationId) {
    const result = await Swal.fire({
        title: 'Reject Verification',
        text: 'Are you sure you want to reject this client verification?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-times-circle"></i> Reject',
        cancelButtonText: 'Cancel',
        focusConfirm: false,
        showLoaderOnConfirm: true,
        preConfirm: async() => {
            try {
                const response = await fetch(`/admin/verification/${verificationId}/reject`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    // No rejection_reason sent — backend sets default
                    body: JSON.stringify({})
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Rejection failed');
                }

                return data;
            } catch (error) {
                Swal.showValidationMessage(`Request failed: ${error.message}`);
            }
        },
        allowOutsideClick: () => !Swal.isLoading()
    });

    // ==============================
    // AFTER CONFIRMATION SUCCESS
    // ==============================

    if (result.isConfirmed && result.value) {
        const data = result.value;

        // Update badge
        updateStatusBadge('rejected');

        // Show rejection reason in modal section (backend default)
        const rejectionSection = document.getElementById('rejectionReasonSection');
        const rejectionReasonText = document.getElementById('viewRejectionReason');

        if (rejectionSection && rejectionReasonText) {
            rejectionReasonText.textContent = data.data.rejection_reason || 'Rejected by admin';
            rejectionSection.style.display = 'block';
        }

        // Update verified info if returned
        if (data.data.verified_at) {
            const verifiedAt = document.getElementById('viewVerifiedAt');
            if (verifiedAt) verifiedAt.textContent = data.data.verified_at;
        }

        if (data.data.verified_by) {
            const verifiedBy = document.getElementById('viewVerifiedBy');
            if (verifiedBy) verifiedBy.textContent = data.data.verified_by;
        }

        // Fade out rejected row (like delete car)
        removeVerificationRow(verificationId);

        // Success Toast
        Swal.fire({
            icon: 'success',
            title: 'Rejected!',
            text: data.message || 'Verification has been rejected.',
            toast: true,
            position: 'top-end',
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false,
            didClose: () => {
                window.location.reload();
            }
        });
    }
}

// ========================================
// STATUS BADGE UPDATE
// ========================================

function updateStatusBadge(status) {
    const statusBadge = document.getElementById('viewVerificationStatus');
    if (!statusBadge) return;

    statusBadge.textContent =
        status.charAt(0).toUpperCase() + status.slice(1);

    statusBadge.className = 'status-tag';

    switch (status) {
        case 'approved':
            statusBadge.classList.add('status-approved');
            break;
        case 'pending':
            statusBadge.classList.add('status-pending');
            break;
        case 'rejected':
            statusBadge.classList.add('status-rejected');
            break;
    }
}

// ========================================
// REMOVE ROW WITH FADE OUT
// ========================================

function removeVerificationRow(verificationId) {
    const rejectButton = document.querySelector(
        `.btn-action[title="Reject"][data-verification-id="${verificationId}"]`
    );

    if (rejectButton) {
        const row = rejectButton.closest('tr');
        if (row) {
            row.style.transition = 'opacity 0.5s ease';
            row.style.opacity = '0';

            setTimeout(() => {
                row.remove();
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

// ========================================
// CLOSE VIEW MODAL
// ========================================

function closeViewModal() {
    const viewModalElement = document.getElementById('viewVerificationModal');
    if (!viewModalElement) return;

    const viewModal = bootstrap.Modal.getInstance(viewModalElement);
    if (viewModal) {
        viewModal.hide();
    }
}