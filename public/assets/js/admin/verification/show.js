// assets/js/admin/verification/show.js

document.addEventListener('DOMContentLoaded', function() {
    const viewButtons = document.querySelectorAll('.btn-action[title="View"]');
    const modal = new bootstrap.Modal(document.getElementById('viewVerificationModal'));

    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const verificationId = this.getAttribute('data-verification-id');

            // Fetch verification data
            fetch(`/admin/verification/${verificationId}`)
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        populateModal(result.data);
                        modal.show();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load verification details');
                });
        });
    });

    function populateModal(data) {
        // User info
        document.getElementById('viewClientEmail').textContent = data.email;
        document.getElementById('viewClientPhone').textContent = data.phone;

        // Profile info
        document.getElementById('viewClientFirstName').textContent = data.first_name;
        document.getElementById('viewClientLastName').textContent = data.last_name;
        document.getElementById('viewClientDateBirth').textContent = data.date_birth;
        document.getElementById('viewClientAddress').textContent = data.address;
        document.getElementById('viewClientNationality').textContent = data.nationality;
        document.getElementById('viewClientFacebook').textContent = data.facebook_name || 'Not Provided';
        document.getElementById('viewEmergencyName').textContent = data.emergency_contact_name;
        document.getElementById('viewEmergencyPhone').textContent = data.emergency_contact_phone;

        // Verification info
        document.getElementById('viewIdType').textContent = data.id_type;
        document.getElementById('viewIdNumber').textContent = data.id_number;
        document.getElementById('viewSubmittedAt').textContent = data.submitted_at;
        document.getElementById('viewVerifiedAt').textContent = data.verified_at;
        document.getElementById('viewVerifiedBy').textContent = data.verified_by;

        // Images
        document.getElementById('viewIdFrontImage').src = data.id_front_image;
        document.getElementById('viewIdBackImage').src = data.id_back_image;
        document.getElementById('viewSelfieImage').src = data.selfie_image;




        // Status
        const statusBadge = document.getElementById('viewVerificationStatus');
        statusBadge.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
        statusBadge.className = 'status-tag';

        if (data.status === 'approved') {
            statusBadge.classList.add('status-approved');
        } else if (data.status === 'pending') {
            statusBadge.classList.add('status-pending');
        } else if (data.status === 'rejected') {
            statusBadge.classList.add('status-rejected');
        }

        // Rejection reason
        const rejectionSection = document.getElementById('rejectionReasonSection');
        if (data.rejection_reason) {
            document.getElementById('viewRejectionReason').textContent = data.rejection_reason;
            rejectionSection.style.display = 'block';
        } else {
            rejectionSection.style.display = 'none';
        }
    }
});