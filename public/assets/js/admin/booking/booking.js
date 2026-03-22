/**
 * booking.js
 *
 * Handles all Booking Management interactions:
 *  1. View Booking modal  — fetch & populate via AJAX
 *  2. Approve modal       — pre-fill fees, recalc total, submit
 *  3. Complete (Car Returned) — payment guard, confirm via Swal
 *  4. Reject / Cancel     — confirm via Swal, works from table row or modal
 */

(function() {
    'use strict';

    /* ─────────────────────────────────────────────────────────────────────────
       UTILITIES
    ───────────────────────────────────────────────────────────────────────── */

    /** Set text content of an element by ID. */
    function setText(id, value) {
        var el = document.getElementById(id);
        if (el) el.textContent = (value !== null && value !== undefined && String(value).trim() !== '') ?
            value : '—';
    }

    /** Title-case a string. */
    function capitalize(str) {
        return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
    }

    /** Format a number as Philippine Peso (no ₱ symbol — caller adds it). */
    function formatMoney(n) {
        return parseFloat(n || 0).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    /** Format an ISO date string into a readable locale string. */
    function formatDate(d) {
        if (!d) return 'N/A';
        return new Date(d).toLocaleString('en-US', {
            month: 'short',
            day: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true,
        });
    }

    /** Read CSRF token from the meta tag. */
    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    /** Show a SweetAlert2 loading spinner. */
    function swalLoading(title) {
        Swal.fire({
            title: title || 'Please wait…',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: function() { Swal.showLoading(); },
        });
    }

    /** Show a simple SweetAlert2 error. */
    function showError(message) {
        Swal.fire({ icon: 'error', title: 'Oops…', text: message, confirmButtonColor: '#3085d6' });
    }

    /** Show a SweetAlert2 error for validation error objects. */
    function showValidationErrors(errors) {
        var messages = Object.values(errors).flat().join('\n');
        Swal.fire({ icon: 'error', title: 'Validation Error', text: messages });
    }

    /**
     * Remove all Bootstrap modal artefacts from <body>.
     * Call this after a modal flow ends and before reload.
     */
    function cleanupModals() {
        document.body.classList.remove('modal-open');
        document.querySelectorAll('.modal-backdrop').forEach(function(el) { el.remove(); });
        document.body.style.removeProperty('padding-right');
        document.body.style.removeProperty('overflow');
    }

    /**
     * Hide a Bootstrap modal gracefully and resolve when fully hidden.
     * @param {string} modalId
     * @returns {Promise<void>}
     */
    function hideModal(modalId) {
        return new Promise(function(resolve) {
            var el = document.getElementById(modalId);
            if (!el) return resolve();

            var instance = bootstrap.Modal.getInstance(el);
            if (!instance || !el.classList.contains('show')) return resolve();

            el.addEventListener('hidden.bs.modal', resolve, { once: true });
            instance.hide();
        });
    }

    /**
     * Show a Bootstrap modal and resolve when fully shown.
     * @param {string} modalId
     * @param {object} [options]
     * @returns {Promise<void>}
     */
    function showModal(modalId, options) {
        return new Promise(function(resolve) {
            var el = document.getElementById(modalId);
            if (!el) return resolve();

            var instance = bootstrap.Modal.getInstance(el) ||
                new bootstrap.Modal(el, Object.assign({ backdrop: 'static' }, options || {}));

            el.addEventListener('shown.bs.modal', resolve, { once: true });
            instance.show();
        });
    }

    /**
     * Wrapper around fetch for JSON API calls.
     * @param {string} url
     * @param {string} [method='GET']
     * @param {object|null} [body]
     * @returns {Promise<Response>}
     */
    function apiRequest(url, method, body) {
        method = method || 'GET';
        var opts = {
            method: method,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
        };
        if (body) opts.body = JSON.stringify(body);
        return fetch(url, opts);
    }

    /** Status → Bootstrap badge class map. */
    var STATUS_CLASS = {
        pending: 'bg-warning text-dark',
        ongoing: 'bg-info text-dark',
        completed: 'bg-success',
        cancelled: 'bg-danger',
    };


    /* ─────────────────────────────────────────────────────────────────────────
       VIEW BOOKING MODAL
    ───────────────────────────────────────────────────────────────────────── */

    /**
     * Fetch booking JSON and open the view modal.
     * @param {string|number} bookingId
     */
    async function openViewModal(bookingId) {
        swalLoading('Loading booking…');
        try {
            var res = await apiRequest('/admin/bookings/' + bookingId);
            var data = await res.json();
            Swal.close();

            if (!res.ok) return showError(data.message || 'Failed to load booking.');

            populateViewModal(data);
            setFooterButtons(data);
            await showModal('viewBookingModal');

        } catch (err) {
            Swal.close();
            showError('An error occurred while loading booking data.');
        }
    }

    /** Fill all view-modal fields from a booking object. */
    function populateViewModal(b) {
        var car = b.car || {};
        var client = b.client || {};
        var status = (b.status || '').toLowerCase();

        // Client
        setText('viewClientName', client.name || 'N/A');
        setText('viewClientEmail', client.email || 'N/A');
        setText('viewClientPhone', client.phone || 'N/A');

        // Car
        setText('viewCarName', [car.brand, car.model, car.year].filter(Boolean).join(' ') || 'N/A');
        setText('viewPlateNumber', car.plate_number || 'N/A');
        setText('viewCarTransmission', capitalize(car.transmission_type));
        setText('viewCarFuel', capitalize(car.fuel_type));
        setText('viewCarCapacity', car.capacity || 'N/A');
        setText('viewCarColor', capitalize(car.color));
        setText('viewCarPrice', car.rental_price_per_day ? formatMoney(car.rental_price_per_day) : '0.00');

        var img = document.getElementById('viewCarImage');
        if (img) {
            img.src = car.image_path ?
                '/storage/' + car.image_path :
                'https://via.placeholder.com/400x160?text=No+Image';
        }

        // Rental period
        setText('viewStartDate', formatDate(b.rental_start_date));
        setText('viewEndDate', formatDate(b.rental_end_date));
        setText('viewTotalDays', b.total_days != null ? b.total_days + ' Day(s)' : 'N/A');
        setText('viewTotalHours', b.total_hours != null ? b.total_hours + ' Hr(s)' : 'N/A');

        // Destinations
        setText('viewDestinationFrom', b.destinationFrom || 'N/A');
        setText('viewDestinationTo', b.destinationTo || 'N/A');

        // Payment summary
        setText('viewCarAmount', b.car_amount != null ? '₱' + formatMoney(b.car_amount) : '₱0.00');
        setText('viewDestinationAmount', b.destination_amount != null ? '₱' + formatMoney(b.destination_amount) : '₱0.00');
        setText('viewDiscountAmount', b.discount_amount != null ? '-₱' + formatMoney(b.discount_amount) : '₱0.00');
        setText('viewFinalAmount', b.final_amount != null ? '₱' + formatMoney(b.final_amount) : '₱0.00');

        // Timestamps
        setText('viewCreatedAt', formatDate(b.created_at));
        setText('viewUpdatedAt', formatDate(b.updated_at));

        // Status badge
        var badge = document.getElementById('viewStatusBadge');
        if (badge) {
            badge.textContent = capitalize(status);
            badge.className = 'badge rounded-pill ' + (STATUS_CLASS[status] || 'bg-secondary');
        }
    }

    /** Show/hide footer action buttons based on booking status. */
    function setFooterButtons(booking) {
        var status = (booking.status || '').toLowerCase();
        var id = String(booking.id);

        var btnApprove = document.getElementById('approveBookingBtn');
        var btnComplete = document.getElementById('completeBookingBtn');
        var btnReject = document.getElementById('rejectBookingBtn');

        // Hide all first
        [btnApprove, btnComplete, btnReject].forEach(function(btn) {
            if (btn) btn.classList.add('d-none');
        });

        if (status === 'pending') {
            revealBtn(btnApprove, id);
            revealBtn(btnReject, id);
        }

        if (status === 'ongoing') {
            revealBtn(btnComplete, id);
            revealBtn(btnReject, id);
        }
    }

    function revealBtn(btn, id) {
        if (!btn) return;
        btn.dataset.id = id;
        btn.classList.remove('d-none');
    }


    /* ─────────────────────────────────────────────────────────────────────────
       APPROVE MODAL
    ───────────────────────────────────────────────────────────────────────── */

    /** Copy data from the view modal into the approve modal fields. */
    function populateApproveModal() {
        var get = function(id) {
            var el = document.getElementById(id);
            return el ? el.textContent.trim() : '';
        };

        setText('approveClientName', get('viewClientName'));
        setText('approveCarName', get('viewCarName'));
        setText('approvePlate', get('viewPlateNumber'));

        // Raw price (strip ₱ and commas)
        var rawPrice = get('viewCarPrice').replace(/[₱,\s]/g, '');
        setText('approveCarRate', rawPrice);
        setText('approveRateDisplay', rawPrice);

        // Raw days (digits only)
        var rawDays = get('viewTotalDays').replace(/[^\d]/g, '');
        setText('approveDays', rawDays || '0');

        // Car amount (strip ₱, commas, minus)
        var rawAmount = get('viewCarAmount').replace(/[₱,\-\s]/g, '');
        var carAmtEl = document.getElementById('approveCarAmount');
        if (carAmtEl) carAmtEl.value = parseFloat(rawAmount) || 0;

        recalcTotal();
    }

    /** Recalculate and display the total amount in the approve modal. */
    function recalcTotal() {
        var car = parseFloat(document.getElementById('approveCarAmount').value) || 0;
        var destination = parseFloat(document.getElementById('approveDestinationAmount').value) || 0;
        var discount = parseFloat(document.getElementById('approveDiscountAmount').value) || 0;
        var total = Math.max(0, car + destination - discount);

        setText('approveTotalAmount', total.toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }));

        var finalInput = document.getElementById('approveFinalAmountInput');
        if (finalInput) finalInput.value = total.toFixed(2);
    }

    /** Client-side guard before submitting approval. */
    function validateApproveForm(data) {
        if (data.final_amount <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Final amount must be greater than 0.',
                confirmButtonColor: '#3085d6',
            });
            return false;
        }
        return true;
    }


    /* ─────────────────────────────────────────────────────────────────────────
       COMPLETE (CAR RETURNED)
    ───────────────────────────────────────────────────────────────────────── */

    /**
     * Send the complete request and handle the payment-guard response.
     * @param {string|number} bookingId
     */
    async function handleComplete(bookingId) {
        swalLoading('Checking payment status…');
        try {
            var res = await apiRequest('/admin/bookings/' + bookingId + '/complete', 'PUT');
            var data = await res.json();

            if (res.ok && data.success) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Completed!',
                    text: data.message || 'Booking marked as completed.',
                    timer: 1500,
                    showConfirmButton: false,
                });
                cleanupModals();
                location.reload();
                return;
            }

            // Payment not yet settled — show balance breakdown
            if (res.status === 422 && data.payment_status) {
                var ps = data.payment_status;
                var amountPaid = formatMoney(ps.amount_paid || 0);
                var finalAmt = formatMoney(ps.final_amount || 0);
                var remaining = formatMoney(ps.remaining || 0);

                Swal.fire({
                    icon: 'warning',
                    title: 'Payment Not Settled',
                    confirmButtonText: 'Got it',
                    confirmButtonColor: '#3085d6',
                    html: [
                        '<div style="text-align:left;font-size:14px;line-height:1.9;">',
                        '<p style="margin-bottom:10px;">', data.message, '</p>',
                        '<hr style="margin:8px 0;">',
                        '<div style="display:flex;justify-content:space-between;">',
                        '<span style="color:#888;">Amount Paid</span>',
                        '<strong>₱', amountPaid, '</strong>',
                        '</div>',
                        '<div style="display:flex;justify-content:space-between;">',
                        '<span style="color:#888;">Total Due</span>',
                        '<strong style="color:#16a34a;">₱', finalAmt, '</strong>',
                        '</div>',
                        '<div style="display:flex;justify-content:space-between;margin-top:6px;">',
                        '<span style="color:#888;">Remaining Balance</span>',
                        '<strong style="color:#dc2626;">₱', remaining, '</strong>',
                        '</div>',
                        '</div>',
                    ].join(''),
                });
                return;
            }

            Swal.fire('Error', data.message || 'Could not complete booking.', 'error');

        } catch (err) {
            Swal.fire('Server Error', 'Please try again.', 'error');
        }
    }


    /* ─────────────────────────────────────────────────────────────────────────
       REJECT / CANCEL
    ───────────────────────────────────────────────────────────────────────── */

    /**
     * Confirm then reject a booking.
     * @param {string|number} bookingId
     * @param {string|null}   [closeModalId] — modal to close before the request
     */
    async function handleReject(bookingId, closeModalId) {
        var confirmed = await Swal.fire({
            icon: 'warning',
            title: 'Reject Booking?',
            text: 'This will cancel the booking and release the car.',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Reject',
        });

        if (!confirmed.isConfirmed) return;

        if (closeModalId) await hideModal(closeModalId);

        swalLoading('Rejecting booking…');
        try {
            var res = await apiRequest('/admin/bookings/' + bookingId + '/reject', 'PUT');
            var data = await res.json();

            if (res.ok && data.success) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Rejected!',
                    text: data.message || 'Booking has been cancelled.',
                    timer: 1500,
                    showConfirmButton: false,
                });
                cleanupModals();
                location.reload();
            } else {
                Swal.fire('Error', data.message || 'Could not reject booking.', 'error');
            }
        } catch (err) {
            Swal.fire('Server Error', 'Please try again.', 'error');
        }
    }


    /* ─────────────────────────────────────────────────────────────────────────
       BOOT — wire up all event listeners on DOMContentLoaded
    ───────────────────────────────────────────────────────────────────────── */

    document.addEventListener('DOMContentLoaded', function() {

        var approveModal = document.getElementById('approveModal');
        var destinationInput = document.getElementById('approveDestinationAmount');
        var discountInput = document.getElementById('approveDiscountAmount');
        var proceedBtn = document.getElementById('proceedToPaymentBtn');

        // Live recalc in approve modal
        if (destinationInput) destinationInput.addEventListener('input', recalcTotal);
        if (discountInput) discountInput.addEventListener('input', recalcTotal);

        // ── Delegated click handler ───────────────────────────────────────────
        document.addEventListener('click', async function(e) {

            // 1. View button (eye icon in table row)
            var viewBtn = e.target.closest('.btn-view-booking');
            if (viewBtn) {
                e.preventDefault();
                return openViewModal(viewBtn.dataset.bookingId);
            }

            // 2. Quick Reject (trash icon in table row)
            var quickRejectBtn = e.target.closest('.btn-quick-reject');
            if (quickRejectBtn) {
                e.preventDefault();
                var id = quickRejectBtn.dataset.bookingId;
                if (!id) return showError('Could not read booking ID.');
                return handleReject(id); // no modal to close
            }

            // 3. Approve (inside view modal footer)
            var approveBtn = e.target.closest('.btn-approve-booking');
            if (approveBtn) {
                e.preventDefault();
                var bookingId = approveBtn.dataset.id;
                if (!bookingId) return showError('Could not read booking ID. Please close and try again.');

                document.getElementById('approveBookingId').value = bookingId;

                swalLoading('Preparing approval form…');
                await hideModal('viewBookingModal');
                populateApproveModal();
                Swal.close();
                await showModal('approveModal');
                return;
            }

            // 4. Complete / Car Returned (inside view modal footer)
            var completeBtn = e.target.closest('.btn-complete-booking');
            if (completeBtn) {
                e.preventDefault();
                var id = completeBtn.dataset.id;

                var ok = await Swal.fire({
                    icon: 'question',
                    title: 'Mark as Completed?',
                    text: 'Confirm the car has been returned by the client.',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Car Returned',
                });

                if (!ok.isConfirmed) return;

                await hideModal('viewBookingModal');
                await handleComplete(id);
                return;
            }

            // 5. Reject (inside view modal footer)
            var rejectBtn = e.target.closest('.btn-reject-booking');
            if (rejectBtn) {
                e.preventDefault();
                var id = rejectBtn.dataset.id;
                if (!id) return showError('Could not read booking ID.');
                return handleReject(id, 'viewBookingModal');
            }

        });

        // ── Proceed to Payment (approve modal submit) ─────────────────────────
        if (proceedBtn) {
            proceedBtn.addEventListener('click', async function() {
                var bookingId = document.getElementById('approveBookingId').value;

                if (!bookingId) {
                    return Swal.fire('Error', 'No booking selected. Please try again.', 'error');
                }

                var form = {
                    destination_amount: parseFloat(destinationInput.value) || 0,
                    discount_amount: parseFloat(discountInput.value) || 0,
                    final_amount: parseFloat(document.getElementById('approveFinalAmountInput').value) || 0,
                };

                if (!validateApproveForm(form)) return;

                await hideModal('approveModal');
                swalLoading('Processing approval…');

                try {
                    var res = await apiRequest('/admin/bookings/' + bookingId + '/approve', 'PUT', form);
                    var data = await res.json();

                    if (res.ok && data.success) {
                        await Swal.fire({
                            icon: 'success',
                            title: 'Approved!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false,
                        });
                        cleanupModals();
                        location.reload();
                    } else {
                        data.errors ?
                            showValidationErrors(data.errors) :
                            Swal.fire('Error', data.message || 'Approval failed.', 'error');
                    }
                } catch (err) {
                    Swal.fire('Server Error', 'Please try again.', 'error');
                }
            });
        }

        // ── Reset approve modal fields when it closes ─────────────────────────
        if (approveModal) {
            approveModal.addEventListener('hidden.bs.modal', function() {
                var carAmtEl = document.getElementById('approveCarAmount');
                var finalInput = document.getElementById('approveFinalAmountInput');
                var bookingId = document.getElementById('approveBookingId');

                if (destinationInput) destinationInput.value = '';
                if (discountInput) discountInput.value = '';
                if (carAmtEl) carAmtEl.value = '0.00';
                if (finalInput) finalInput.value = '0.00';
                if (bookingId) bookingId.value = '';
                setText('approveTotalAmount', '0.00');
            });
        }

    });

})();