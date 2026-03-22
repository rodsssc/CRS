/**
 * Admin Payment Management — index.js
 *
 * Responsibilities:
 *  1. Add Payment modal — booking select auto-fills remaining balance,
 *     validates amount vs remaining, validates full_payment type,
 *     submits via fetch and shows SweetAlert2 feedback.
 *  2. View Payment detail modal — populated from data-* attributes.
 *  3. Delete Payment — SweetAlert2 confirm before submitting.
 *  4. Mark Completed / Failed — SweetAlert2 confirm before submitting.
 *  5. Auto-open Add Payment modal when redirected with booking_id flag.
 */
(function() {
    'use strict';

    /* ─────────────────────────────────────────────────────────────────────────
       UTILITY HELPERS
    ───────────────────────────────────────────────────────────────────────── */

    /**
     * Format a number as Philippine Peso string.
     * @param {number|string} value
     * @returns {string}
     */
    function formatMoney(value) {
        return '₱' + parseFloat(value || 0).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    /**
     * Set element text content. Falls back to '—' for blank values.
     * @param {HTMLElement|null} el
     * @param {string|number} value
     */
    function setText(el, value) {
        if (!el) return;
        el.textContent = (value !== null && value !== undefined && String(value).trim() !== '') ?
            value :
            '—';
    }

    /**
     * Set element innerHTML.
     * @param {HTMLElement|null} el
     * @param {string} html
     */
    function setHtml(el, html) {
        if (el) el.innerHTML = html || '—';
    }

    /**
     * Toggle a helper element's visibility.
     * @param {HTMLElement|null} el
     * @param {boolean} visible
     */
    function toggle(el, visible) {
        if (el) el.classList.toggle('d-none', !visible);
    }

    /**
     * Build a Bootstrap badge for a payment status string.
     * @param {string} status
     * @returns {string} HTML string
     */
    function buildStatusBadge(status) {
        var map = {
            completed: 'bg-success-soft text-success',
            pending: 'bg-warning-soft text-warning',
            failed: 'bg-danger-soft text-danger',
        };
        var cls = map[status] || 'bg-secondary text-secondary';
        var label = status ? (status.charAt(0).toUpperCase() + status.slice(1)) : '—';
        return '<span class="badge ' + cls + '">' + label + '</span>';
    }


    /* ─────────────────────────────────────────────────────────────────────────
       ADD PAYMENT MODAL
    ───────────────────────────────────────────────────────────────────────── */

    var addModal = document.getElementById('addPaymentModal');
    var addForm = document.getElementById('addPaymentForm');
    var rentalSelect = document.getElementById('addPaymentRentalId');
    var amountInput = document.getElementById('addPaymentAmount');
    var paymentType = document.getElementById('addPaymentType');
    var submitBtn = document.getElementById('addPaymentSubmitBtn');

    // Balance banner elements
    var balanceBanner = document.getElementById('balanceInfoBanner');
    var bannerTotalDue = document.getElementById('bannerTotalDue');
    var bannerAlreadyPaid = document.getElementById('bannerAlreadyPaid');
    var bannerRemaining = document.getElementById('bannerRemaining');

    // Inline error / hint
    var amountError = document.getElementById('amountError');
    var paymentTypeHint = document.getElementById('paymentTypeHint');
    var paymentTypeHintTx = document.getElementById('paymentTypeHintText');

    /** Return the parsed data from the currently selected rental option. */
    function getSelectedRentalData() {
        if (!rentalSelect || !rentalSelect.value) return null;
        var opt = rentalSelect.options[rentalSelect.selectedIndex];
        return {
            finalAmount: parseFloat(opt.getAttribute('data-final-amount') || 0),
            paid: parseFloat(opt.getAttribute('data-paid') || 0),
            remaining: parseFloat(opt.getAttribute('data-remaining') || 0),
        };
    }

    /** Refresh the balance info banner whenever the rental changes. */
    function refreshBalanceBanner() {
        var data = getSelectedRentalData();

        if (!data) {
            toggle(balanceBanner, false);
            return;
        }

        setText(bannerTotalDue, formatMoney(data.finalAmount));
        setText(bannerAlreadyPaid, formatMoney(data.paid));
        setText(bannerRemaining, formatMoney(data.remaining));

        // Colour-code remaining
        if (bannerRemaining) {
            bannerRemaining.className = data.remaining <= 0 ?
                'text-success fw-bold' :
                'text-warning fw-bold';
        }

        toggle(balanceBanner, true);

        // Pre-fill amount with remaining balance (only when it is positive)
        if (amountInput && data.remaining > 0) {
            amountInput.value = data.remaining.toFixed(2);
        }

        validatePaymentTypeHint();
    }

    /** Show/hide a hint when "Full Payment" is selected. */
    function validatePaymentTypeHint() {
        if (!paymentType || !paymentTypeHint || !paymentTypeHintTx) return;

        var data = getSelectedRentalData();
        if (!data || paymentType.value !== 'full_payment') {
            toggle(paymentTypeHint, false);
            return;
        }

        var enteredAmount = parseFloat(amountInput ? amountInput.value : 0) || 0;
        var diff = data.remaining - enteredAmount;

        if (diff > 0.009) {
            // Amount entered is less than what's needed to fully pay
            paymentTypeHintTx.textContent =
                'Full Payment requires ₱' + data.remaining.toLocaleString('en-PH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }) + ' to settle the remaining balance.';
            paymentTypeHint.className = 'text-danger small mt-1';
        } else if (diff < -0.009) {
            // Amount exceeds remaining balance
            paymentTypeHintTx.textContent =
                'Amount exceeds the remaining balance of ' + formatMoney(data.remaining) + '.';
            paymentTypeHint.className = 'text-danger small mt-1';
        } else {
            // Perfect full payment
            paymentTypeHintTx.textContent = 'This will fully settle the booking balance.';
            paymentTypeHint.className = 'text-success small mt-1';
        }

        toggle(paymentTypeHint, true);
    }

    /** Clear inline amount error. */
    function clearAmountError() {
        if (amountError) {
            amountError.textContent = '';
            toggle(amountError, false);
        }
    }

    /** Show inline amount error. */
    function showAmountError(message) {
        if (amountError) {
            amountError.textContent = message;
            toggle(amountError, false); // ensure hidden class is removed below
            amountError.classList.remove('d-none');
        }
    }

    /**
     * Client-side validation before submitting.
     * Returns true if valid, false otherwise (and shows feedback).
     */
    function validateForm() {
        clearAmountError();

        var data = getSelectedRentalData();
        if (!data) return true; // server will catch missing rental_id

        var amount = parseFloat(amountInput ? amountInput.value : 0) || 0;
        var type = paymentType ? paymentType.value : '';

        // 1. Amount must not exceed remaining balance
        if (amount > data.remaining + 0.009) {
            showAmountError(
                'Amount (₱' + amount.toLocaleString('en-PH', { minimumFractionDigits: 2 }) +
                ') exceeds the remaining balance of ' + formatMoney(data.remaining) + '.'
            );
            return false;
        }

        // 2. If full_payment is selected, amount must equal the remaining balance
        if (type === 'full_payment' && Math.abs(amount - data.remaining) > 0.009) {
            showAmountError(
                '"Full Payment" requires the exact remaining balance of ' +
                formatMoney(data.remaining) + '. ' +
                'Use "Down Payment" for a partial payment.'
            );
            return false;
        }

        return true;
    }

    // ── Event listeners ──────────────────────────────────────────────────────

    if (rentalSelect) {
        rentalSelect.addEventListener('change', refreshBalanceBanner);
    }

    if (amountInput) {
        amountInput.addEventListener('input', function() {
            clearAmountError();
            validatePaymentTypeHint();
        });
    }

    if (paymentType) {
        paymentType.addEventListener('change', validatePaymentTypeHint);
    }

    // Move modal to <body> so the backdrop renders correctly
    if (addModal) {
        addModal.addEventListener('show.bs.modal', function() {
            document.body.appendChild(addModal);
        });
    }

    // Auto-open when redirected from booking with ?booking_id= query string
    if (window.ADMIN_PAYMENT_OPEN_WITH_BOOKING && addModal) {
        document.body.appendChild(addModal);
        var autoModal = new bootstrap.Modal(addModal, { backdrop: true, keyboard: true });
        autoModal.show();
        // Trigger balance banner for pre-selected booking
        refreshBalanceBanner();
    }

    // Form submit — validate then submit via fetch, show SweetAlert result
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            e.preventDefault();

            if (!validateForm()) return;

            var formData = new FormData(addForm);

            // Disable submit button to prevent double-click
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing…';
            }

            fetch(addForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                })
                .then(function(response) {
                    return response.json().then(function(data) {
                        return { status: response.status, data: data };
                    });
                })
                .then(function(result) {
                    var data = result.data;

                    if (result.status === 201 && data.success) {
                        // Close the modal first
                        var modalInstance = bootstrap.Modal.getInstance(addModal);
                        if (modalInstance) modalInstance.hide();

                        Swal.fire({
                            icon: 'success',
                            title: 'Payment Recorded!',
                            text: data.message || 'Payment has been recorded successfully.',
                            confirmButtonColor: '#0d6efd',
                            timer: 2500,
                            timerProgressBar: true,
                        }).then(function() {
                            window.location.reload();
                        });

                    } else {
                        // Validation or business rule errors
                        var errorMessages = '';

                        if (data.errors) {
                            var errList = Object.values(data.errors).flat();
                            errorMessages = errList.join('\n');
                        } else {
                            errorMessages = data.message || 'Something went wrong.';
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Failed to Record Payment',
                            text: errorMessages,
                            confirmButtonColor: '#dc3545',
                        });
                    }
                })
                .catch(function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Network Error',
                        text: 'Could not connect to the server. Please try again.',
                        confirmButtonColor: '#dc3545',
                    });
                })
                .finally(function() {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-check me-1"></i>Record Payment';
                    }
                });
        });
    }


    /* ─────────────────────────────────────────────────────────────────────────
       VIEW PAYMENT DETAIL MODAL
    ───────────────────────────────────────────────────────────────────────── */

    /** Populate the detail modal from a view-button's data-* attributes. */
    function populateViewModal(btn) {
        var d = btn.dataset;
        var due = parseFloat(d.totalDue || 0);
        var paid = parseFloat(d.totalPaid || 0);
        var remaining = due - paid;

        // Header
        setText(document.getElementById('pdTitle'), 'Payment #' + (d.paymentId || ''));
        setText(document.getElementById('pdSubtitle'), 'Booking #' + (d.rentalId || '') + ' · ' + (d.method || ''));

        // Balance summary
        setText(document.getElementById('pdTotalDue'), formatMoney(due));
        setText(document.getElementById('pdTotalPaid'), formatMoney(paid));

        var elRemaining = document.getElementById('pdRemaining');
        var elRemBadge = document.getElementById('pdRemainingBadge');

        if (remaining <= 0) {
            setText(elRemaining, '₱0.00');
            if (elRemBadge) {
                elRemBadge.textContent = 'Paid';
                elRemBadge.className = 'badge bg-success-soft text-success ms-1';
                elRemBadge.style.display = '';
            }
        } else {
            setText(elRemaining, formatMoney(remaining));
            if (elRemBadge) elRemBadge.style.display = 'none';
        }

        // This payment
        setText(document.getElementById('pdAmount'), formatMoney(d.amount));
        setText(document.getElementById('pdMethod'), d.method || '—');
        setText(document.getElementById('pdType'), d.type || '—');
        setText(document.getElementById('pdDate'), d.date || '—');
        setHtml(document.getElementById('pdStatus'), buildStatusBadge(d.status));

        // Booking & client
        setText(document.getElementById('pdBookingId'), '#BK' + (d.rentalId || ''));
        setText(document.getElementById('pdClientName'), d.clientName || '—');
        setText(document.getElementById('pdClientEmail'), d.clientEmail || '—');
        setText(document.getElementById('pdClientPhone'), d.clientPhone || '—');
        setText(document.getElementById('pdVehicle'), d.vehicle || '—');
        setText(document.getElementById('pdPlate'), d.plate || '—');

        // Notes — hide wrapper when empty
        var elNotesWrap = document.getElementById('pdNotesWrap');
        var elNotes = document.getElementById('pdNotes');
        var hasNotes = d.notes && d.notes.trim() !== '';

        setText(elNotes, hasNotes ? d.notes : '');
        if (elNotesWrap) elNotesWrap.style.display = hasNotes ? '' : 'none';
    }


    /* ─────────────────────────────────────────────────────────────────────────
       DELEGATED CLICK HANDLER
       Handles: view button, delete button
    ───────────────────────────────────────────────────────────────────────── */

    document.addEventListener('click', function(e) {

        // ── View Payment ──────────────────────────────────────────────────────
        var viewBtn = e.target.closest('.btn-view-payment');
        if (viewBtn) {
            var viewModal = document.getElementById('paymentDetailModal');
            if (!viewModal) return;

            populateViewModal(viewBtn);

            var instance = bootstrap.Modal.getInstance(viewModal) ||
                new bootstrap.Modal(viewModal, { backdrop: true, keyboard: true });
            instance.show();
            return;
        }

        // ── Delete Payment ────────────────────────────────────────────────────
        var deleteBtn = e.target.closest('.btn-delete-payment');
        if (deleteBtn) {
            var action = deleteBtn.getAttribute('data-action');
            if (!action) return;

            Swal.fire({
                icon: 'warning',
                title: 'Delete Payment?',
                text: 'This action cannot be undone.',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
            }).then(function(result) {
                if (!result.isConfirmed) return;

                var deleteForm = document.getElementById('deletePaymentForm');
                if (!deleteForm) return;

                deleteForm.action = action;
                deleteForm.submit();
            });

            return;
        }

        // ── Mark Completed ────────────────────────────────────────────────────
        var completedForm = e.target.closest('.mark-completed-form');
        if (completedForm) {
            e.preventDefault();
            Swal.fire({
                icon: 'question',
                title: 'Mark as Completed?',
                text: 'This will update the payment status to completed.',
                showCancelButton: true,
                confirmButtonText: 'Yes, mark it',
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
            }).then(function(result) {
                if (result.isConfirmed) completedForm.submit();
            });
            return;
        }

        // ── Mark Failed ───────────────────────────────────────────────────────
        var failedForm = e.target.closest('.mark-failed-form');
        if (failedForm) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Mark as Failed?',
                text: 'This will update the payment status to failed.',
                showCancelButton: true,
                confirmButtonText: 'Yes, mark it',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
            }).then(function(result) {
                if (result.isConfirmed) failedForm.submit();
            });
        }

    });

})();