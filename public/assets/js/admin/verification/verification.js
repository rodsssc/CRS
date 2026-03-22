// assets/js/admin/verification/verification.js
// Merged: show.js + approve.js + reject.js - With Bootstrap Reject Modal

document.addEventListener('DOMContentLoaded', function() {

    // =========================================================
    // CSRF TOKEN HELPER
    // =========================================================
    window.getCsrfToken = function() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    };

    // =========================================================
    // BOOTSTRAP MODAL SETUP
    // =========================================================
    const modalEl = document.getElementById('viewVerificationModal');
    const modal = new bootstrap.Modal(modalEl);
    const approveBtn = document.getElementById('approveVerificationBtn');
    const rejectBtn = document.getElementById('rejectVerificationBtn');

    // Reject Modal Elements
    const rejectModalEl = document.getElementById('rejectVerificationModal');
    const rejectModal = rejectModalEl ? new bootstrap.Modal(rejectModalEl) : null;
    const confirmRejectBtn = document.getElementById('confirmRejectBtn');
    const cancelRejectBtn = document.getElementById('cancelRejectBtn');
    const rejectionReasonInput = document.getElementById('rejectionReasonInput');
    const rejectionVerificationId = document.getElementById('rejectionVerificationId');

    // =========================================================
    // VIEW BUTTON — delegated click
    // =========================================================
    document.addEventListener('click', function(e) {
        const viewBtn = e.target.closest('.btn-action[title="View"]');
        if (!viewBtn) return;

        const userId = viewBtn.getAttribute('data-user-id');
        if (!userId) return;

        fetch('/admin/verification/' + userId, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(function(response) {
                if (!response.ok) {
                    return response.text().then(function(text) { throw new Error(text); });
                }
                return response.json();
            })
            .then(function(result) {
                if (!result.success) {
                    alert(result.message || 'Failed to load verification details.');
                    return;
                }

                populateModal(result.data);

                var verificationId = result.data.id || '';

                if (approveBtn) approveBtn.setAttribute('data-verification-id', verificationId);
                if (rejectBtn) rejectBtn.setAttribute('data-verification-id', verificationId);

                var noVerification = !verificationId || result.data.status === 'none';

                if (approveBtn) {
                    approveBtn.style.display = (noVerification || result.data.status === 'approved') ? 'none' : '';
                }
                if (rejectBtn) {
                    rejectBtn.style.display = (noVerification || result.data.status === 'rejected') ? 'none' : '';
                }

                modal.show();
            })
            .catch(function(error) {
                console.error('Verification fetch error:', error.message);
                alert('Failed to load verification details. Check console for details.');
            });
    });

    // =========================================================
    // TABLE REJECT BUTTON HANDLER
    // =========================================================
    document.addEventListener('click', function(e) {
        const tableRejectBtn = e.target.closest('.btn-action[title="Reject"]');
        if (tableRejectBtn) {
            e.preventDefault();
            const verificationId = tableRejectBtn.getAttribute('data-verification-id');
            if (verificationId && rejectModal) {
                // Clear previous input
                if (rejectionReasonInput) rejectionReasonInput.value = '';
                // Set the verification ID
                if (rejectionVerificationId) rejectionVerificationId.value = verificationId;
                // Show the modal
                rejectModal.show();
            }
        }
    });

    // =========================================================
    // APPROVE BUTTON
    // =========================================================
    if (approveBtn) {
        approveBtn.addEventListener('click', function() {
            var verificationId = this.getAttribute('data-verification-id');
            if (!verificationId) {
                console.error('No verification ID on approve button');
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
            }).then(function(result) {
                if (result.isConfirmed) {
                    approveVerification(verificationId);
                }
            });
        });
    }

    // =========================================================
    // REJECT BUTTON (in view modal)
    // =========================================================
    if (rejectBtn) {
        rejectBtn.addEventListener('click', function() {
            var verificationId = this.getAttribute('data-verification-id');
            if (!verificationId) {
                console.error('No verification ID on reject button');
                return;
            }

            if (rejectModal) {
                // Clear previous input
                if (rejectionReasonInput) rejectionReasonInput.value = '';
                // Set the verification ID
                if (rejectionVerificationId) rejectionVerificationId.value = verificationId;
                // Hide the view modal and show reject modal
                modal.hide();
                setTimeout(() => {
                    rejectModal.show();
                }, 500);
            }
        });
    }

    // =========================================================
    // CONFIRM REJECT BUTTON
    // =========================================================
    if (confirmRejectBtn) {
        confirmRejectBtn.addEventListener('click', function() {
            const verificationId = rejectionVerificationId ? rejectionVerificationId.value : null;
            const rejectionReason = rejectionReasonInput ? rejectionReasonInput.value : '';

            if (!verificationId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No verification ID found'
                });
                return;
            }

            // Close reject modal
            if (rejectModal) rejectModal.hide();

            // Process rejection
            processRejection(verificationId, rejectionReason);
        });
    }

    // =========================================================
    // CANCEL REJECT BUTTON
    // =========================================================
    if (cancelRejectBtn) {
        cancelRejectBtn.addEventListener('click', function() {
            if (rejectModal) rejectModal.hide();
        });
    }

    // =========================================================
    // PROCESS REJECTION
    // =========================================================
    async function processRejection(verificationId, rejectionReason) {
        Swal.fire({
            title: 'Rejecting...',
            text: 'Please wait while we process the rejection.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => { Swal.showLoading(); }
        });

        try {
            const response = await fetch('/admin/verification/' + verificationId + '/reject', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ rejection_reason: rejectionReason }),
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Rejection failed');
            }

            // Update UI if view modal is open
            updateStatusBadge('rejected');

            const rejectionSection = document.getElementById('rejectionReasonSection');
            const rejectionReasonText = document.getElementById('viewRejectionReason');

            if (rejectionSection && rejectionReasonText) {
                rejectionReasonText.textContent = result.data.rejection_reason || rejectionReason || 'Rejected by admin';
                rejectionSection.style.display = 'block';
            }

            const verifiedAtEl = document.getElementById('viewVerifiedAt');
            const verifiedByEl = document.getElementById('viewVerifiedBy');

            if (verifiedAtEl && result.data.verified_at) verifiedAtEl.textContent = result.data.verified_at;
            if (verifiedByEl && result.data.verified_by) verifiedByEl.textContent = result.data.verified_by;

            // Hide buttons in view modal
            if (approveBtn) approveBtn.style.display = 'none';
            if (rejectBtn) rejectBtn.style.display = 'none';

            // Show success message
            await Swal.fire({
                icon: 'success',
                title: 'Rejected!',
                text: result.message || 'Verification has been rejected.',
                toast: true,
                position: 'top-end',
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false
            });

            // Reload page after toast closes
            setTimeout(() => {
                window.location.reload();
            }, 2000);

        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Rejection Failed',
                text: error.message || 'Something went wrong. Please try again.',
                confirmButtonColor: '#dc3545'
            });
        }
    }

    // =========================================================
    // POPULATE MODAL FIELDS
    // =========================================================
    function populateModal(data) {
        setText('viewClientFirstName', data.first_name);
        setText('viewClientLastName', data.last_name);
        setText('viewClientEmail', data.email);
        setText('viewClientPhone', data.phone);
        setText('viewClientDateBirth', data.date_birth);
        setText('viewClientAddress', data.address);
        setText('viewClientNationality', data.nationality);
        setText('viewClientFacebook', data.facebook_name);
        setText('viewEmergencyName', data.emergency_contact_name);
        setText('viewEmergencyPhone', data.emergency_contact_phone);
        setText('viewIdType', data.id_type);
        setText('viewIdNumber', data.id_number);
        setText('viewSubmittedAt', data.submitted_at);
        setText('viewVerifiedAt', data.verified_at);
        setText('viewVerifiedBy', data.verified_by);

        setImage('viewIdFrontImage', data.id_front_image);
        setImage('viewIdBackImage', data.id_back_image);
        setImage('viewSelfieImage', data.selfie_image);

        var statusBadge = document.getElementById('viewVerificationStatus');
        if (statusBadge) {
            var s = data.status || 'none';
            statusBadge.textContent = s.charAt(0).toUpperCase() + s.slice(1);
            statusBadge.className = 'status-tag status-' + s;
        }

        var rejectionSection = document.getElementById('rejectionReasonSection');
        if (rejectionSection) {
            if (data.rejection_reason) {
                setText('viewRejectionReason', data.rejection_reason);
                rejectionSection.style.display = 'block';
            } else {
                rejectionSection.style.display = 'none';
            }
        }
    }

    // =========================================================
    // HELPERS
    // =========================================================
    function setText(id, value) {
        var el = document.getElementById(id);
        if (!el) return;
        el.textContent = (value !== null && value !== undefined && value !== '') ? value : '—';
    }

    function setImage(id, src) {
        var el = document.getElementById(id);
        if (el && src) el.src = src;
    }

    // =========================================================
    // APPROVE VERIFICATION FUNCTION
    // =========================================================
    async function approveVerification(verificationId) {
        Swal.fire({
            title: 'Approving...',
            text: 'Please wait while we process the verification.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: function() { Swal.showLoading(); }
        });

        try {
            var response = await fetch('/admin/verification/' + verificationId + '/approve', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            var result = await response.json();

            if (!response.ok || !result.success) {
                throw result;
            }

            updateStatusBadge('approved');

            var verifiedAtEl = document.getElementById('viewVerifiedAt');
            var verifiedByEl = document.getElementById('viewVerifiedBy');
            if (verifiedAtEl && result.data.verified_at) verifiedAtEl.textContent = result.data.verified_at;
            if (verifiedByEl && result.data.verified_by) verifiedByEl.textContent = result.data.verified_by;

            var approveBtn = document.getElementById('approveVerificationBtn');
            var rejectBtn = document.getElementById('rejectVerificationBtn');
            if (approveBtn) approveBtn.style.display = 'none';
            if (rejectBtn) rejectBtn.style.display = 'none';

            Swal.fire({
                icon: 'success',
                title: 'Approved!',
                text: result.message || 'Verification approved successfully.',
                toast: true,
                position: 'top-end',
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(function() { window.location.reload(); });

        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Approval Failed',
                text: error.message || 'Something went wrong. Please try again.',
                confirmButtonColor: '#dc3545'
            });
        }
    }

    // =========================================================
    // UPDATE STATUS BADGE
    // =========================================================
    function updateStatusBadge(status) {
        var badge = document.getElementById('viewVerificationStatus');
        if (!badge) return;
        badge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        badge.className = 'status-tag status-' + status;
    }

});