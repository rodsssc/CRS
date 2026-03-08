// Payment Processing for Bookings

document.addEventListener('DOMContentLoaded', function() {

    // ── ELEMENT REFERENCES ──────────────────────────────────────────────────
    const paymentModal = document.getElementById('paymentModal');
    const paymentMethod = document.getElementById('paymentMethod');
    const paymentType = document.getElementById('paymentType');
    const paymentNotes = document.getElementById('paymentNotes');
    const completePaymentBtn = document.getElementById('completePaymentBtn');
    const paymentBookingId = document.getElementById('paymentBookingId');

    let paymentData = {
        bookingId: null,
        carAmount: 0,
        destinationAmount: 0,
        discountAmount: 0,
        finalAmount: 0
    };


    // ── POPULATE PAYMENT MODAL ──────────────────────────────────────────────
    function populatePaymentModal() {
        // Get data from the view modal (which is already populated from the booking)
        document.getElementById('paymentClientName').textContent =
            document.getElementById('viewClientName').textContent || '—';
        document.getElementById('paymentCarName').textContent =
            document.getElementById('viewCarName').textContent || '—';
        document.getElementById('paymentPlate').textContent =
            document.getElementById('viewPlateNumber').textContent || '—';
        document.getElementById('paymentPlateDisplay').textContent =
            document.getElementById('viewPlateNumber').textContent || '—';

        // Get payment amounts from the approve modal
        const carAmountText = (document.getElementById('approveCarAmount').value || '0').trim();
        const destinationText = (document.getElementById('approveTotalAmount').textContent || '0')
            .replace(/[₱,]/g, '')
            .trim();
        const totalText = (document.getElementById('approveTotalAmount').textContent || '0')
            .replace(/[₱,]/g, '')
            .trim();

        paymentData.carAmount = parseFloat(carAmountText) || 0;
        paymentData.destinationAmount = parseFloat(
            (document.getElementById('approveDestinationAmount').value || '0')
        ) || 0;
        paymentData.discountAmount = parseFloat(
            (document.getElementById('approveDiscountAmount').value || '0')
        ) || 0;
        paymentData.finalAmount = parseFloat(totalText) || 0;
        paymentData.bookingId = document.getElementById('viewBookingId').value;

        // Update payment modal display
        document.getElementById('paymentCarAmount').textContent =
            formatMoney(paymentData.carAmount);
        document.getElementById('paymentDestinationAmount').textContent =
            formatMoney(paymentData.destinationAmount);
        document.getElementById('paymentDiscountAmount').textContent =
            formatMoney(paymentData.discountAmount);
        document.getElementById('paymentTotalAmount').textContent =
            formatMoney(paymentData.finalAmount);

        paymentBookingId.value = paymentData.bookingId;
    }


    // ── VALIDATE PAYMENT FORM ──────────────────────────────────────────────
    function validatePaymentForm() {
        if (!paymentMethod.value) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please select a payment method.',
                confirmButtonColor: '#3085d6'
            });
            return false;
        }

        if (!paymentType.value) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please select a payment type.',
                confirmButtonColor: '#3085d6'
            });
            return false;
        }

        if (paymentData.finalAmount <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Final amount must be greater than 0.',
                confirmButtonColor: '#3085d6'
            });
            return false;
        }

        return true;
    }


    // ── INTERCEPT PROCEED TO PAYMENT ────────────────────────────────────────
    // This is in the approve.js file - it shows this payment modal
    // We just need to handle when the payment modal is shown
    if (paymentModal) {
        paymentModal.addEventListener('show.bs.modal', function() {
            populatePaymentModal();
        });
    }


    // ── COMPLETE PAYMENT ────────────────────────────────────────────────────
    completePaymentBtn.addEventListener('click', async function() {
        if (!validatePaymentForm()) return;

        const bookingId = paymentBookingId.value;
        if (!bookingId) {
            Swal.fire('Error', 'No booking selected.', 'error');
            return;
        }

        const paymentPayload = {
            rental_id: bookingId,
            payment_method: paymentMethod.value,
            payment_type: paymentType.value,
            amount: paymentData.finalAmount,
            status: 'pending', // Will be changed to completed or failed separately
            notes: paymentNotes.value || null
        };

        // Close payment modal
        const modalInstance = bootstrap.Modal.getInstance(paymentModal);
        if (modalInstance) modalInstance.hide();

        setTimeout(async function() {
            Swal.fire({
                title: 'Processing Payment...',
                text: 'Please wait while we process your payment',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
                showConfirmButton: false
            });

            try {
                const response = await fetch('/admin/payment', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify(paymentPayload)
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Mark booking as completed after payment
                    await updateBookingStatus(bookingId, 'completed');
                } else {
                    data.errors ?
                        displayValidationErrors(data.errors) :
                        Swal.fire('Error', data.message || 'Payment processing failed.', 'error');
                }

            } catch (err) {
                Swal.fire('Server Error', 'An error occurred while processing payment. Please try again.', 'error');
                console.error('Error processing payment:', err);
            }
        }, 300);
    });


    // ── UPDATE BOOKING STATUS ──────────────────────────────────────────────
    async function updateBookingStatus(bookingId, status) {
        // For now, we just mark the booking as ongoing when approved
        // This can be expanded to handle status updates later
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'Payment processed and booking finalized.',
            timer: 2000,
            showConfirmButton: false
        }).then(() => {
            // Reload the page to reflect changes
            location.reload();
        });
    }


    // ── RESET FORM WHEN PAYMENT MODAL IS CLOSED ─────────────────────────────
    paymentModal.addEventListener('hidden.bs.modal', function() {
        paymentMethod.value = '';
        paymentType.value = '';
        paymentNotes.value = '';
        paymentBookingId.value = '';
        paymentData = {
            bookingId: null,
            carAmount: 0,
            destinationAmount: 0,
            discountAmount: 0,
            finalAmount: 0
        };
    });

});


// ── HELPERS ─────────────────────────────────────────────────────────────
function formatMoney(amount) {
    return parseFloat(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}