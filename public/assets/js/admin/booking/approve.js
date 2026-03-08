function validateApproveForm(data) {
    if (!data.destination_amount || data.destination_amount <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Destination fee is required and must be greater than 0.',
            confirmButtonColor: '#3085d6'
        });
        return false;
    }

    if (!data.final_amount || data.final_amount <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Final amount is required and must be greater than 0.',
            confirmButtonColor: '#3085d6'
        });
        return false;
    }

    return true;
}

document.addEventListener('DOMContentLoaded', function() {

    // ── ELEMENT REFERENCES ──────────────────────────────────────────────────
    const approveModal = document.getElementById('approveModal');
    const destinationInput = document.getElementById('approveDestinationAmount');
    const discountInput = document.getElementById('approveDiscountAmount');
    const carAmountInput = document.getElementById('approveCarAmount');
    const totalDisplay = document.getElementById('approveTotalAmount');
    const finalAmountInput = document.getElementById('approveFinalAmountInput');
    const proceedBtn = document.getElementById('proceedToPaymentBtn');

    let activeBookingId = null;


    // ── RECALCULATE TOTAL ───────────────────────────────────────────────────
    function recalcTotal() {
        const car = parseFloat(carAmountInput.value) || 0;
        const destination = parseFloat(destinationInput.value) || 0;
        const discount = parseFloat(discountInput.value) || 0;
        const total = Math.max(0, car + destination - discount);

        totalDisplay.textContent = total.toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        finalAmountInput.value = total.toFixed(2);
    }

    destinationInput.addEventListener('input', recalcTotal);
    discountInput.addEventListener('input', recalcTotal);


    // ── POPULATE APPROVE MODAL ──────────────────────────────────────────────
    function populateApproveModal() {
        document.getElementById('approveClientName').textContent = document.getElementById('viewClientName').textContent || '—';
        document.getElementById('approveCarName').textContent = document.getElementById('viewCarName').textContent || '—';
        document.getElementById('approvePlate').textContent = document.getElementById('viewPlateNumber').textContent || '—';
        document.getElementById('approveCarRate').textContent = document.getElementById('viewCarPrice').textContent || '0.00';
        document.getElementById('approveRateDisplay').textContent = document.getElementById('viewCarPrice').textContent || '0.00';
        document.getElementById('approveDays').textContent = document.getElementById('viewTotalDays').textContent || '0';

        const carAmtRaw = (document.getElementById('viewCarAmount').textContent || '0')
            .replace(/[₱,]/g, '')
            .trim();
        carAmountInput.value = parseFloat(carAmtRaw) || 0;

        recalcTotal();
    }


    // ── OPEN APPROVE MODAL ──────────────────────────────────────────────────
    document.addEventListener('click', function(e) {
        const approveBtn = e.target.closest('.btn-approve-booking');
        if (!approveBtn) return;

        activeBookingId = approveBtn.dataset.id;

        Swal.fire({
            title: 'Please wait...',
            text: 'Preparing approval form',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
            showConfirmButton: false
        });

        const viewModalInstance = bootstrap.Modal.getInstance(document.getElementById('viewBookingModal'));
        if (viewModalInstance) viewModalInstance.hide();

        setTimeout(function() {
            populateApproveModal();
            Swal.close();
            new bootstrap.Modal(approveModal).show();
        }, 300);
    });


    // ── PROCEED TO PAYMENT ──────────────────────────────────────────────────
    proceedBtn.addEventListener('click', async function() {


        const form = {
            destination_amount: parseFloat(destinationInput.value) || 0,
            discount_amount: parseFloat(discountInput.value) || 0,
            final_amount: parseFloat(finalAmountInput.value) || 0,
        };

        if (!validateApproveForm(form)) return;

        if (!activeBookingId) {
            Swal.fire('Error', 'No booking selected.', 'error');
            return;
        }


        bootstrap.Modal.getInstance(approveModal).hide();

        setTimeout(async function() {
            Swal.fire({
                title: 'Processing approval...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
                showConfirmButton: false
            });

            try {

                const response = await fetch(`/admin/bookings/${activeBookingId}/approve`, {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify(form)
                });

                const data = await response.json();


                if (response.ok && data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Approved!',
                        text: data.message || 'Booking approved successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        document.body.classList.remove('modal-open');
                        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                        new bootstrap.Modal(document.getElementById('paymentModal')).show();
                    });
                } else {
                    data.errors ?
                        displayValidationErrors(data.errors) :
                        Swal.fire('Error', data.message || 'Approval failed.', 'error');
                }

            } catch (err) {
                Swal.fire('Server Error', 'Please try again.', 'error');
                console.error(err);
            }
        }, 300);
    });


    // ── RESET FORM WHEN APPROVE MODAL IS CLOSED ─────────────────────────────
    approveModal.addEventListener('hidden.bs.modal', function() {
        carAmountInput.value = '0.00';
        totalDisplay.textContent = '0.00';
        finalAmountInput.value = '0.00';
        activeBookingId = null;
    });

});