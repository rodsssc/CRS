(function () {
    const BOUND_KEY = '__ADMIN_VERIFICATION_ACTIONS_BOUND__';
    if (window[BOUND_KEY]) return;
    window[BOUND_KEY] = true;

    document.addEventListener('DOMContentLoaded', function () {
        const viewModalEl = document.getElementById('viewVerificationModal');
        const rejectModalEl = document.getElementById('rejectVerificationModal');

        // Only run if we are on the admin verification page.
        if (!viewModalEl && !rejectModalEl) return;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

        function getJson(url, options = {}) {
            const init = {
                method: options.method || 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
            };

            if (options.body !== undefined) {
                init.headers['Content-Type'] = 'application/json';
                init.body = JSON.stringify(options.body);
            }

            return fetch(url, init).then(async (res) => {
                const data = await res.json().catch(() => ({}));
                return { res, data };
            });
        }

        function showToastError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message || 'Something went wrong.',
            });
        }

        function setText(id, value, fallback = '—') {
            const el = document.getElementById(id);
            if (!el) return;
            const v = value === null || value === undefined || value === '' ? fallback : value;
            el.textContent = v;
        }

        function showModal(el) {
            if (!el) return;
            const instance = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el, {
                backdrop: true,
                keyboard: true
            });
            instance.show();
        }

        function hideModal(el) {
            if (!el) return;
            const instance = bootstrap.Modal.getInstance(el);
            if (instance) instance.hide();
        }

        function statusClass(status) {
            switch ((status || '').toLowerCase()) {
                case 'pending':
                    return 'status-pending';
                case 'approved':
                    return 'status-approved';
                case 'rejected':
                    return 'status-rejected';
                default:
                    return 'status-pending';
            }
        }

        // Live character count in reject modal
        const rejectionReasonInput = document.getElementById('rejectionReasonInput');
        const rejectionCharCount = document.getElementById('rejectionCharCount');
        if (rejectionReasonInput && rejectionCharCount) {
            rejectionCharCount.textContent = rejectionReasonInput.value.length;
            rejectionReasonInput.addEventListener('input', function () {
                rejectionCharCount.textContent = rejectionReasonInput.value.length;
            });
        }

        function openRejectModal(verificationId) {
            const hiddenId = document.getElementById('rejectionVerificationId');
            if (!hiddenId || !rejectModalEl) return;
            if (!verificationId) {
                showToastError('Missing verification id.');
                return;
            }

            hiddenId.value = verificationId || '';
            if (rejectionReasonInput) rejectionReasonInput.value = '';
            if (rejectionCharCount && rejectionReasonInput) rejectionCharCount.textContent = rejectionReasonInput.value.length;

            showModal(rejectModalEl);
        }

        async function loadVerificationDetails(userId) {
            if (!viewModalEl) return;
            if (!userId) return;

            Swal.fire({
                title: 'Loading...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
                showConfirmButton: false,
            });

            try {
                const { res, data } = await getJson(`/admin/verification/${userId}`);
                Swal.close();

                if (!res.ok || !data?.success) {
                    return showToastError(data?.message || 'Failed to load verification.');
                }

                const d = data.data || {};

                // Images
                const imgFront = document.getElementById('viewIdFrontImage');
                if (imgFront) imgFront.src = d.id_front_image || imgFront.getAttribute('src');

                // Status
                const statusEl = document.getElementById('viewVerificationStatus');
                if (statusEl) {
                    const s = d.status || 'none';
                    statusEl.textContent = s.charAt(0).toUpperCase() + s.slice(1);
                    statusEl.className = `status-tag ${statusClass(s)}`;
                }

                // Basic client info
                setText('viewClientFirstName', d.first_name);
                setText('viewClientLastName', d.last_name);
                setText('viewClientEmail', d.email);

                setText('viewClientPhone', d.phone);
                setText('viewClientDateBirth', d.date_birth);
                setText('viewClientNationality', d.nationality);
                setText('viewClientFacebook', d.facebook_name);
                setText('viewClientAddress', d.address);

                setText('viewEmergencyName', d.emergency_contact_name);
                setText('viewEmergencyPhone', d.emergency_contact_phone);

                // ID details
                setText('viewIdType', d.id_type);
                setText('viewIdNumber', d.id_number);
                setText('viewSubmittedAt', d.submitted_at);
                setText('viewVerifiedAt', d.verified_at);
                setText('viewVerifiedBy', d.verified_by);

                setText('viewRejectionReason', d.rejection_reason);

                // Show/hide rejection reason section
                const rejectionSection = document.getElementById('rejectionReasonSection');
                if (rejectionSection) {
                    if ((d.status || '') === 'rejected') {
                        rejectionSection.style.display = '';
                    } else {
                        rejectionSection.style.display = 'none';
                    }
                }

                // Approve/Reject buttons visibility & verification id wiring
                const approveBtnEl = document.getElementById('approveVerificationBtn');
                const rejectBtnEl = document.getElementById('rejectVerificationBtn');

                const verificationId = d.id || '';

                if (approveBtnEl) {
                    approveBtnEl.dataset.verificationId = verificationId;
                    approveBtnEl.style.display = (d.status || '') === 'pending' ? '' : 'none';
                }
                if (rejectBtnEl) {
                    rejectBtnEl.dataset.verificationId = verificationId;
                    rejectBtnEl.style.display = (d.status || '') === 'pending' ? '' : 'none';
                }

                showModal(viewModalEl);
            } catch (err) {
                Swal.close();
                showToastError('Failed to load verification details.');
            }
        }

        async function approveVerification(verificationId) {
            if (!verificationId) return;

            Swal.fire({
                title: 'Approving...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
                showConfirmButton: false,
            });

            try {
                const { res, data } = await getJson(`/admin/verification/${verificationId}/approve`, {
                    method: 'POST',
                    body: {},
                });

                if (!res.ok || !data?.success) {
                    return showToastError(data?.message || 'Approval failed.');
                }

                Swal.close();
                window.location.reload();
            } catch (err) {
                Swal.close();
                showToastError('Approval failed.');
            }
        }

        async function rejectVerification(verificationId, rejectionReason) {
            if (!verificationId) return;

            Swal.fire({
                title: 'Rejecting...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
                showConfirmButton: false,
            });

            try {
                const { res, data } = await getJson(`/admin/verification/${verificationId}/reject`, {
                    method: 'POST',
                    body: { rejection_reason: rejectionReason || null },
                });

                if (!res.ok || !data?.success) {
                    return showToastError(data?.message || 'Rejection failed.');
                }

                Swal.close();
                window.location.reload();
            } catch (err) {
                Swal.close();
                showToastError('Rejection failed.');
            }
        }

        // Table: View / Reject buttons
        document.addEventListener('click', function (e) {
            const viewBtn = e.target.closest('button[title="View"][data-user-id]');
            if (viewBtn) {
                e.preventDefault();
                const userId = viewBtn.getAttribute('data-user-id');
                loadVerificationDetails(userId);
                return;
            }

            const tableRejectBtn = e.target.closest('button[title="Reject"][data-verification-id]');
            if (tableRejectBtn) {
                e.preventDefault();
                const verificationId = tableRejectBtn.getAttribute('data-verification-id');
                openRejectModal(verificationId);
                return;
            }
        });

        // View modal: Approve / Reject buttons
        const approveBtnEl = document.getElementById('approveVerificationBtn');
        const rejectBtnEl = document.getElementById('rejectVerificationBtn');
        const confirmRejectBtn = document.getElementById('confirmRejectBtn');

        if (approveBtnEl) {
            approveBtnEl.addEventListener('click', function (e) {
                e.preventDefault();
                approveVerification(approveBtnEl.getAttribute('data-verification-id') || approveBtnEl.dataset.verificationId);
            });
        }

        if (rejectBtnEl) {
            rejectBtnEl.addEventListener('click', function (e) {
                e.preventDefault();
                openRejectModal(rejectBtnEl.getAttribute('data-verification-id') || rejectBtnEl.dataset.verificationId);
            });
        }

        if (confirmRejectBtn) {
            confirmRejectBtn.addEventListener('click', async function (e) {
                e.preventDefault();
                const hiddenId = document.getElementById('rejectionVerificationId');
                if (!hiddenId) return;

                const verificationId = hiddenId.value;
                const reason = rejectionReasonInput ? rejectionReasonInput.value.trim() : '';
                await rejectVerification(verificationId, reason);
            });
        }
    });
})();

