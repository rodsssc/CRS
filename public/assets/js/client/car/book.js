/**
 * ============================================================================
 * BOOK CAR SCRIPT
 * ============================================================================
 *
 * Purpose: Handles car booking when "Book Now" button is clicked
 *
 * Flow:
 * 1. Capture car ID from triggering button when modal opens
 * 2. Auto-calculate rental duration when dates change
 * 3. Validate form fields before submission
 * 4. Send POST request to server with booking data
 * 5. Show success/error message
 * 6. Reset form and redirect to bookings page on success
 *
 * Dependencies: Bootstrap 5, SweetAlert2
 * ============================================================================
 */

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Close the booking modal and clean up Bootstrap artifacts
 * Removes backdrop and resets body styles
 */
function closeBookingModal() {
    const modalElement = document.getElementById('bookingModal');

    if (modalElement) {
        const modal = bootstrap.Modal.getInstance(modalElement);

        if (modal) {
            modal.hide();
        } else {
            const bsModal = new bootstrap.Modal(modalElement);
            bsModal.hide();
        }
    }

    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
}

/**
 * Calculate rental duration between two datetime strings
 * @param {string} startDate - Start datetime value
 * @param {string} endDate - End datetime value
 * @returns {{ days: number, hours: number }}
 */
function calculateRentalDuration(startDate, endDate) {
    if (!startDate || !endDate) {
        return { days: 0, hours: 0 };
    }

    const start = new Date(startDate);
    const end = new Date(endDate);
    const diffMs = end - start;

    if (diffMs < 0) {
        return { days: 0, hours: 0 };
    }

    return {
        days: Math.ceil(diffMs / (1000 * 60 * 60 * 24)),
        hours: Math.floor(diffMs / (1000 * 60 * 60))
    };
}

/**
 * Update total_days and total_hours fields based on selected dates
 */
function updateRentalDuration() {
    const startDate = document.getElementById('rental_start_date').value;
    const endDate = document.getElementById('rental_end_date').value;

    const duration = calculateRentalDuration(startDate, endDate);

    document.getElementById('total_days').value = duration.days;
    document.getElementById('total_hours').value = duration.hours;

    console.log('Duration updated:', duration);
}

/**
 * Validate required booking form fields
 * @param {Object} data - Form data object
 * @returns {boolean} True if valid, false otherwise
 */
function validateBookingFormData(data) {
    const requiredFields = [
        'carId',
        'client_id',
        'destinationFrom',
        'destinationTo',
        'rental_start_date',
        'rental_end_date',
    ];

    for (const field of requiredFields) {
        if (!data[field] || data[field] === '') {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please fill in all required fields',
                confirmButtonColor: '#3085d6'
            });
            return false;
        }
    }

    const startDate = new Date(data.rental_start_date);
    const endDate = new Date(data.rental_end_date);

    if (endDate <= startDate) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Dates',
            text: 'End date must be after start date',
            confirmButtonColor: '#3085d6'
        });
        return false;
    }

    return true;
}

/**
 * Display server-side validation errors
 * @param {Object} errors - Errors object from server response
 */
function displayValidationErrors(errors) {
    const errorList = Object.entries(errors)
        .map(([field, messages]) => `${field}: ${messages.join(', ')}`)
        .join('\n');

    Swal.fire({
        icon: 'error',
        title: 'Validation Error',
        html: `<pre style="text-align: left; font-size: 14px;">${errorList}</pre>`,
        confirmButtonColor: '#3085d6'
    });
}

/**
 * Reset form and clear all validation states
 * @param {HTMLFormElement} form - Form element to reset
 */
function resetBookingForm(form) {
    form.reset();
    form.classList.remove('was-validated');

    form.querySelectorAll('.is-invalid, .is-valid').forEach(el => {
        el.classList.remove('is-invalid', 'is-valid');
    });

    form.querySelectorAll('input, select').forEach(input => {
        input.setCustomValidity('');
    });

    document.getElementById('total_days').value = '';
    document.getElementById('total_hours').value = '';
}

// ============================================================================
// MAIN BOOKING HANDLER
// ============================================================================

document.addEventListener('DOMContentLoaded', () => {
    // ========================================================================
    // DOM ELEMENT REFERENCES
    // ========================================================================
    const saveBookingBtn = document.getElementById('saveBookingBtn');
    const bookingForm = document.getElementById('bookingForm');
    const bookingModal = document.getElementById('bookingModal');
    const carIdInput = document.getElementById('carId');

    if (!saveBookingBtn) {
        console.error('Save booking button not found');
        return;
    }

    if (!bookingForm) {
        console.error('Booking form not found');
        return;
    }

    if (!bookingModal) {
        console.error('Booking modal not found');
        return;
    }

    if (!carIdInput) {
        console.error('Car ID input not found');
        return;
    }

    // ========================================================================
    // CAPTURE CAR ID WHEN MODAL OPENS
    // ========================================================================

    /**
     * Set car ID from the triggering "Book Now" button
     */
    bookingModal.addEventListener('show.bs.modal', function(event) {
        const triggerButton = event.relatedTarget;
        const carId = triggerButton ? triggerButton.getAttribute('data-car-id') : '';
        carIdInput.value = carId;
        console.log('Car ID set:', carId);
    });

    // ========================================================================
    // AUTO-CALCULATE DURATION ON DATE CHANGE
    // ========================================================================
    const startDateInput = document.getElementById('rental_start_date');
    const endDateInput = document.getElementById('rental_end_date');

    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', updateRentalDuration);
        endDateInput.addEventListener('change', updateRentalDuration);
    }

    // ========================================================================
    // SUBMIT BOOKING BUTTON HANDLER
    // ========================================================================

    /**
     * Handle submit booking button click
     */
    saveBookingBtn.addEventListener('click', async function(e) {
        e.preventDefault();

        // ====================================================================
        // STEP 1: Collect Form Data
        // ====================================================================
        updateRentalDuration();

        const formData = {
            carId: carIdInput.value || '',
            client_id: document.getElementById('client_id').value || '',
            destinationFrom: document.getElementById('destinationFrom').value.trim() || '',
            destinationTo: document.getElementById('destinationTo').value.trim() || '',
            rental_start_date: document.getElementById('rental_start_date').value || '',
            rental_end_date: document.getElementById('rental_end_date').value || '',
            total_days: document.getElementById('total_days').value || '',
            total_hours: document.getElementById('total_hours').value || '',
        };

        console.log('Creating booking with data:', formData);

        // ====================================================================
        // STEP 2: Validate Form Data
        // ====================================================================
        if (!validateBookingFormData(formData)) {
            return;
        }

        // ====================================================================
        // STEP 3: Build FormData for Submission
        // ====================================================================
        const bookingFormData = new FormData();

        Object.keys(formData).forEach(key => {
            bookingFormData.append(key, formData[key]);
        });

        console.log('Request payload prepared with FormData');

        // ====================================================================
        // STEP 4: Close Modal and Show Loading
        // ====================================================================
        closeBookingModal();

        setTimeout(async() => {

            Swal.fire({
                title: 'Submitting booking...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
                showConfirmButton: false
            });

            try {
                // ============================================================
                // STEP 5: Send Create Request
                // ============================================================
                const response = await fetch('/client/bookings', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest'
                            // Note: Don't set Content-Type for FormData - browser sets it automatically with boundary
                    },
                    body: bookingFormData
                });

                const data = await response.json();
                console.log('Server response:', data);

                // ============================================================
                // STEP 6: Handle Server Response
                // ============================================================
                if (response.ok && data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        toast: true,
                        position: "top-end",
                        text: data.message || 'Waiting for confirmation...',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        resetBookingForm(bookingForm);
                        window.location.replace('/client/bookings');
                    });

                } else {
                    if (data.errors) {
                        console.error('Validation errors:', data.errors);
                        displayValidationErrors(data.errors);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to submit booking',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                }

            } catch (error) {
                // ============================================================
                // STEP 7: Handle Network/System Errors
                // ============================================================
                Swal.close();
                console.error('Submit booking error:', error);

                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: 'Something went wrong. Please try again.',
                    confirmButtonColor: '#3085d6'
                });
            }

        }, 300); // 300ms delay ensures smooth modal transition
    });

    // ========================================================================
    // MODAL CLEANUP
    // ========================================================================

    /**
     * Reset form when modal is closed
     * This ensures clean state when modal is reopened
     */
    bookingModal.addEventListener('hidden.bs.modal', function() {
        resetBookingForm(bookingForm);
    });

});