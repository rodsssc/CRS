/**
 * Admin Payment Management
 * - Add Payment modal: move to body so inputs work above backdrop
 * - Pre-fill amount when selecting a booking
 * - Auto-open modal when redirected from booking with ?booking_id=
 */
(function () {
    'use strict';

    var addModal = document.getElementById('addPaymentModal');
    if (!addModal) return;

    // Ensure modal stacks above backdrop so inputs are clickable
    addModal.addEventListener('show.bs.modal', function () {
        document.body.appendChild(addModal);
    });

    // When booking selection changes, fill amount from data-final-amount
    var rentalSelect = document.getElementById('addPaymentRentalId');
    var amountInput = document.getElementById('addPaymentAmount');
    if (rentalSelect && amountInput) {
        rentalSelect.addEventListener('change', function () {
            var opt = this.options[this.selectedIndex];
            var amt = opt && opt.getAttribute('data-final-amount');
            if (amt) amountInput.value = parseFloat(amt) || '';
        });
    }

    // If page was opened with ?booking_id=, open modal and pre-fill (set by Blade)
    if (window.ADMIN_PAYMENT_OPEN_WITH_BOOKING) {
        document.addEventListener('DOMContentLoaded', function () {
            document.body.appendChild(addModal);
            var modal = new bootstrap.Modal(addModal, { backdrop: true, keyboard: true });
            modal.show();
        });
    }
})();
