// approve.js


document.addEventListener('DOMContentLoaded', function() {

    const approveBtn = document.getElementById('approveVerificationBtn');

    if (approveBtn) {
        approveBtn.addEventListener('click', function() {

            const verificationId = this.getAttribute('data-verification-id');

            if (!verificationId) {
                console.log('No verification ID found');
                return;
            }

            Swal.fire({
                title: 'Approve Verification?',
                text: 'This action will mark this client as verified.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Approve',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    approveVerification(verificationId);
                }
            });

        });
    }

});


// ===============================
// APPROVE FUNCTION
// ===============================

async function approveVerification(verificationId) {

    Swal.fire({
        title: 'Approving...',
        text: 'Please wait while we process the verification.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    try {

        const response = await fetch(`/admin/verification/${verificationId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw result;
        }

        // Update status badge immediately (optional UI improvement)
        updateStatusBadge('approved');

        // Update verified info if returned
        if (result.data.verified_at) {
            const verifiedAt = document.getElementById('viewVerifiedAt');
            if (verifiedAt) verifiedAt.textContent = result.data.verified_at;
        }

        if (result.data.verified_by) {
            const verifiedBy = document.getElementById('viewVerifiedBy');
            if (verifiedBy) verifiedBy.textContent = result.data.verified_by;
        }

        Swal.fire({
            icon: 'success',
            title: 'Approved!',
            text: result.message || 'Verification approved successfully.',
            toast: true,
            position: 'top-end',
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false
        }).then(() => {
            window.location.reload();
        });

    } catch (error) {

        Swal.fire({
            icon: 'error',
            title: 'Approval Failed',
            text: error.message || 'Something went wrong. Please try again.',
            confirmButtonColor: '#dc3545'
        });

    }
}


// ===============================
// UPDATE STATUS BADGE
// ===============================

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


// ===============================
// CLOSE VIEW MODAL
// ===============================

function closeViewModal() {

    const viewModalElement =
        document.getElementById('viewVerificationModal');

    if (!viewModalElement) return;

    const viewModal =
        bootstrap.Modal.getInstance(viewModalElement);

    if (viewModal) {
        viewModal.hide();
    }
}