/**
 * admin_verification_actions.js
 *
 * Works with modal HTML already defined in verification.blade.php.
 * Pure vanilla JS — no optional chaining (?.), no nullish coalescing (??).
 * Depends on: Bootstrap 5, SweetAlert2 (both loaded by the layout).
 *
 * Physical path:
 *   public/assets/js/admin/verification/admin_verification_actions.js
 *
 * Load via @push('scripts') in the Blade view so Bootstrap is available.
 */

(function() {

    /* =========================================================================
       GUARD — run only once per page load
    ========================================================================= */
    var BOUND_KEY = '__ADMIN_VERIFICATION_ACTIONS_BOUND__';
    if (window[BOUND_KEY]) { return; }
    window[BOUND_KEY] = true;

    /* =========================================================================
       ENTRY POINT
    ========================================================================= */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    /* =========================================================================
       BOOT
    ========================================================================= */
    function boot() {
        var viewModalEl = document.getElementById('viewVerificationModal');
        var rejectModalEl = document.getElementById('rejectVerificationModal');

        /* Only run on the verification page */
        if (!viewModalEl && !rejectModalEl) { return; }

        bindCharCounter();
        bindClickHandlers();
    }

    /* =========================================================================
       CHARACTER COUNTER
    ========================================================================= */
    function bindCharCounter() {
        var textarea = document.getElementById('rejectionReasonInput');
        var charCount = document.getElementById('rejectionCharCount');
        if (!textarea || !charCount) { return; }

        charCount.textContent = textarea.value.length;
        textarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }

    /* =========================================================================
       DELEGATED CLICK HANDLER
    ========================================================================= */
    function bindClickHandlers() {
        document.addEventListener('click', function(e) {
            var target = e.target;

            /* Table: View button */
            var viewBtn = closest(target, 'button[title="View"][data-user-id]');
            if (viewBtn) {
                e.preventDefault();
                loadVerificationDetails(viewBtn.getAttribute('data-user-id'));
                return;
            }

            /* Table: Quick-reject button */
            var tableRejectBtn = closest(target, 'button[title="Reject"][data-verification-id]');
            if (tableRejectBtn) {
                e.preventDefault();
                openRejectModal(tableRejectBtn.getAttribute('data-verification-id'));
                return;
            }

            /* View modal: Approve button */
            if (target && target.id === 'approveVerificationBtn') {
                e.preventDefault();
                approveVerification(target.getAttribute('data-verification-id'));
                return;
            }

            /* View modal: Reject button — hide view modal, open reject modal */
            if (target && target.id === 'rejectVerificationBtn') {
                e.preventDefault();
                var viewModalEl = document.getElementById('viewVerificationModal');
                hideModal(viewModalEl);
                var vid = target.getAttribute('data-verification-id');
                setTimeout(function() { openRejectModal(vid); }, 350);
                return;
            }

            /* Reject modal: Confirm button */
            if (target && target.id === 'confirmRejectBtn') {
                e.preventDefault();

                var hiddenInput = document.getElementById('rejectionVerificationId');
                if (!hiddenInput || !hiddenInput.value) {
                    alertError('Missing verification ID.');
                    return;
                }

                var reasonInput = document.getElementById('rejectionReasonInput');
                var reason = reasonInput ? reasonInput.value.trim() : '';

                var rejectModalEl = document.getElementById('rejectVerificationModal');
                hideModal(rejectModalEl);

                var capturedId = hiddenInput.value;
                setTimeout(function() {
                    rejectVerification(capturedId, reason);
                }, 350);
                return;
            }
        });
    }

    /* =========================================================================
       OPEN REJECT MODAL
    ========================================================================= */
    function openRejectModal(verificationId) {
        var rejectModalEl = document.getElementById('rejectVerificationModal');
        if (!rejectModalEl) { return; }

        if (!verificationId) {
            alertError('Missing verification ID — cannot open the reject dialog.');
            return;
        }

        var hiddenInput = document.getElementById('rejectionVerificationId');
        if (hiddenInput) { hiddenInput.value = verificationId; }

        var textarea = document.getElementById('rejectionReasonInput');
        var charCount = document.getElementById('rejectionCharCount');
        if (textarea) { textarea.value = ''; }
        if (charCount) { charCount.textContent = '0'; }

        showModal(rejectModalEl);
    }

    /* =========================================================================
       LOAD VERIFICATION DETAILS → VIEW MODAL
    ========================================================================= */
    function loadVerificationDetails(userId) {
        var viewModalEl = document.getElementById('viewVerificationModal');
        if (!viewModalEl || !userId) { return; }

        alertLoading('Loading...');

        fetchJson('/admin/verification/' + userId, 'GET', null)
            .then(function(result) {
                Swal.close();

                if (!result.ok || !result.data || !result.data.success) {
                    var msg = (result.data && result.data.message) ?
                        result.data.message :
                        'Failed to load verification details.';
                    alertError(msg);
                    return;
                }

                var d = result.data.data || {};

                /* ID image */
                setIdImage(
                    'viewIdFrontImage',
                    'viewIdFrontPlaceholder',
                    d.id_front_image || ''
                );

                /* Status badge */
                var statusEl = document.getElementById('viewVerificationStatus');
                if (statusEl) {
                    var s = d.status || 'none';
                    statusEl.textContent = s.charAt(0).toUpperCase() + s.slice(1);
                    statusEl.className = 'status-tag ' + statusCssClass(s) + ' d-inline-block';
                }

                /* Text fields */
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
                setText('viewIdType', d.id_type);
                setText('viewIdNumber', d.id_number);
                setText('viewSubmittedAt', d.submitted_at);
                setText('viewVerifiedAt', d.verified_at);
                setText('viewVerifiedBy', d.verified_by);
                setText('viewRejectionReason', d.rejection_reason);

                /* Rejection reason section */
                var rejSection = document.getElementById('rejectionReasonSection');
                if (rejSection) {
                    rejSection.style.display = (d.status === 'rejected') ? '' : 'none';
                }

                /* Wire Approve / Reject buttons */
                var isPending = (d.status === 'pending');
                var verificationId = d.id ? String(d.id) : '';

                var approveBtn = document.getElementById('approveVerificationBtn');
                var rejectBtn = document.getElementById('rejectVerificationBtn');

                if (approveBtn) {
                    approveBtn.setAttribute('data-verification-id', verificationId);
                    approveBtn.style.display = isPending ? '' : 'none';
                }
                if (rejectBtn) {
                    rejectBtn.setAttribute('data-verification-id', verificationId);
                    rejectBtn.style.display = isPending ? '' : 'none';
                }

                showModal(viewModalEl);
            })
            .catch(function(err) {
                Swal.close();
                console.error('[Verification] loadVerificationDetails:', err);
                alertError('Failed to load verification details. Please try again.');
            });
    }

    /* =========================================================================
       APPROVE VERIFICATION
    ========================================================================= */
    function approveVerification(verificationId) {
        if (!verificationId) { return; }

        alertConfirm(
            'Approve this verification?',
            'The client will be marked as verified.',
            'Yes, Approve',
            function() {
                alertLoading('Approving...');

                fetchJson(
                    '/admin/verification/' + verificationId + '/approve',
                    'POST', {}
                ).then(function(result) {
                    if (!result.ok || !result.data || !result.data.success) {
                        Swal.close();
                        var msg = (result.data && result.data.message) ?
                            result.data.message : 'Approval failed.';
                        alertError(msg);
                        return;
                    }
                    alertSuccess('Verification approved successfully.');
                    setTimeout(function() { window.location.reload(); }, 1600);
                }).catch(function(err) {
                    Swal.close();
                    console.error('[Verification] approveVerification:', err);
                    alertError('Approval failed. Please try again.');
                });
            }
        );
    }

    /* =========================================================================
       REJECT VERIFICATION
    ========================================================================= */
    function rejectVerification(verificationId, rejectionReason) {
        if (!verificationId) { return; }

        alertLoading('Rejecting...');

        fetchJson(
            '/admin/verification/' + verificationId + '/reject',
            'POST', { rejection_reason: rejectionReason || null }
        ).then(function(result) {
            if (!result.ok || !result.data || !result.data.success) {
                Swal.close();
                var msg = (result.data && result.data.message) ?
                    result.data.message : 'Rejection failed.';
                alertError(msg);
                return;
            }
            alertSuccess('Verification rejected successfully.');
            setTimeout(function() { window.location.reload(); }, 1600);
        }).catch(function(err) {
            Swal.close();
            console.error('[Verification] rejectVerification:', err);
            alertError('Rejection failed. Please try again.');
        });
    }

    /* =========================================================================
       HELPERS — DOM
    ========================================================================= */

    /** Walk up the tree to find a matching ancestor (Element.closest polyfill). */
    function closest(el, selector) {
        if (!el) { return null; }
        if (el.closest) { return el.closest(selector); }
        var current = el;
        while (current && current !== document.documentElement) {
            if (current.matches && current.matches(selector)) { return current; }
            current = current.parentElement;
        }
        return null;
    }

    /** Write textContent; em-dash when value is blank/null/undefined. */
    function setText(id, value) {
        var el = document.getElementById(id);
        if (!el) { return; }
        el.textContent = (value === null || value === undefined || value === '') ?
            '\u2014' :
            value;
    }

    /**
     * Show the real <img> when a storage URL is returned;
     * show the fallback <div> when src is empty — never hits an external URL.
     */
    function setIdImage(imgId, placeholderId, src) {
        var imgEl = document.getElementById(imgId);
        var divEl = document.getElementById(placeholderId);
        if (!imgEl || !divEl) { return; }

        if (src && src.trim() !== '') {
            imgEl.setAttribute('src', src);
            imgEl.classList.remove('d-none');
            divEl.classList.add('d-none');
        } else {
            imgEl.setAttribute('src', '');
            imgEl.classList.add('d-none');
            divEl.classList.remove('d-none');
        }
    }

    function statusCssClass(status) {
        var s = status ? status.toLowerCase() : '';
        if (s === 'approved') { return 'status-approved'; }
        if (s === 'rejected') { return 'status-rejected'; }
        return 'status-pending';
    }

    function showModal(el) {
        if (!el) { return; }
        var m = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
        m.show();
    }

    function hideModal(el) {
        if (!el) { return; }
        var m = bootstrap.Modal.getInstance(el);
        if (m) { m.hide(); }
    }

    /* =========================================================================
       HELPERS — FETCH
    ========================================================================= */
    function getCsrf() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    /** Returns a Promise resolving to { ok: bool, data: object }. */
    function fetchJson(url, method, body) {
        var headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCsrf()
        };
        var init = { method: method || 'GET', headers: headers };

        if (body !== null && body !== undefined) {
            headers['Content-Type'] = 'application/json';
            init.body = JSON.stringify(body);
        }

        return fetch(url, init).then(function(res) {
            var ok = res.ok;
            return res.json()
                .catch(function() { return {}; })
                .then(function(data) { return { ok: ok, data: data }; });
        });
    }

    /* =========================================================================
       HELPERS — SWEETALERT2
    ========================================================================= */
    function alertLoading(title) {
        Swal.fire({
            title: title || 'Please wait...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: function() { Swal.showLoading(); }
        });
    }

    function alertError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message || 'Something went wrong.'
        });
    }

    function alertSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: 'Done!',
            text: message || 'Action completed.',
            timer: 1500,
            showConfirmButton: false
        });
    }

    function alertConfirm(title, text, confirmLabel, onConfirm) {
        Swal.fire({
            icon: 'warning',
            title: title,
            text: text,
            showCancelButton: true,
            confirmButtonText: confirmLabel || 'Yes',
            confirmButtonColor: '#dc3545',
            cancelButtonText: 'Cancel'
        }).then(function(result) {
            if (result.isConfirmed) { onConfirm(); }
        });
    }

})();