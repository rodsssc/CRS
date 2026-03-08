/**
 * ============================================================================
 * CREATE CAR SCRIPT
 * ============================================================================
 * 
 * Purpose: Handles creating new cars when "Add Vehicle" button is clicked
 * 
 * Flow:
 * 1. Validate form using HTML5 validation
 * 2. Collect form data (including image file)
 * 3. Send POST request to server with FormData
 * 4. Show success/error message
 * 5. Reset form and close modal on success
 * 6. Reload page to show new car
 * 
 * Features:
 * - HTML5 form validation
 * - File upload handling
 * - Server-side validation error display
 * 
 * Dependencies: Bootstrap 5, SweetAlert2
 * ============================================================================
 */

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Get CSRF token from meta tag
 * @returns {string} CSRF token value
 */

/**
 * Close the add car modal and clean up Bootstrap artifacts
 * Removes backdrop and resets body styles
 */
function closeAddCarModal() {
    const modalElement = document.getElementById('addCarModal');

    if (modalElement) {
        // Get Bootstrap modal instance
        const modal = bootstrap.Modal.getInstance(modalElement);

        if (modal) {
            modal.hide();
        } else {
            // Fallback: create new instance and hide
            const bsModal = new bootstrap.Modal(modalElement);
            bsModal.hide();
        }
    }

    // Clean up Bootstrap modal artifacts
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
}

/**
 * Validate required form fields
 * @param {Object} data - Form data object
 * @returns {boolean} True if valid, false otherwise
 */
function validateCarFormData(data) {
    const requiredFields = [
        'owner_id',
        'plate_number',
        'brand',
        'model',
        'year',
        'color',
        'capacity',
        'transmission_type',
        'fuel_type',
        'rental_price_per_day',
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
    return true;
}

/**
 * Display validation errors from server
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
function resetCarForm(form) {
    // Reset form to initial state
    form.reset();

    // Remove validation classes
    form.classList.remove('was-validated');

    // Clear all field validation states
    form.querySelectorAll('.is-invalid, .is-valid').forEach(el => {
        el.classList.remove('is-invalid', 'is-valid');
    });

    // Clear custom validity messages
    form.querySelectorAll('input, select').forEach(input => {
        input.setCustomValidity('');
    });
}

// ============================================================================
// MAIN CREATE HANDLER
// ============================================================================

document.addEventListener('DOMContentLoaded', () => {
    // ========================================================================
    // DOM ELEMENT REFERENCES
    // ========================================================================
    const saveCarBtn = document.getElementById('saveCarBtn');
    const addCarForm = document.getElementById('addCarForm');
    const addCarModal = document.getElementById('addCarModal');

    // Exit if required elements don't exist
    if (!saveCarBtn) {
        console.error('Save car button not found');
        return;
    }

    if (!addCarForm) {
        console.error('Add car form not found');
        return;
    }

    if (!addCarModal) {
        console.error('Add car modal not found');
        return;
    }

    // ========================================================================
    // CREATE CAR BUTTON HANDLER
    // ========================================================================

    /**
     * Handle create car button click
     */
    saveCarBtn.addEventListener('click', async function(e) {
        e.preventDefault();

        // ====================================================================
        // STEP 1: Collect Form Data
        // ====================================================================
        const formData = {
            owner_id: document.getElementById('owner_id').value || '',
            plate_number: document.getElementById('plate_number').value.trim() || '',
            brand: document.getElementById('brand').value.trim() || '',
            model: document.getElementById('model').value.trim() || '',
            year: document.getElementById('year').value || '',
            color: document.getElementById('color').value.trim() || '',
            capacity: document.getElementById('capacity').value || '',
            transmission_type: document.getElementById('transmission_type').value || '',
            fuel_type: document.getElementById('fuel_type').value || '',
            rental_price_per_day: document.getElementById('rental_price_per_day').value || '',

        };



        // ====================================================================
        // STEP 2: Validate Form Data
        // ====================================================================
        if (!validateCarFormData(formData)) {
            return;
        }

        // ====================================================================
        // STEP 3: Build FormData for File Upload
        // ====================================================================
        const carFormData = new FormData();

        // Append all form fields
        Object.keys(formData).forEach(key => {
            carFormData.append(key, formData[key]);
        });

        // Append image file if selected
        const imageInput = document.getElementById('image');
        if (imageInput && imageInput.files.length > 0) {
            carFormData.append('image', imageInput.files[0]);
        }

        console.log('Request payload prepared with FormData');

        // ====================================================================
        // STEP 4: Close Modal and Show Loading
        // ====================================================================
        closeAddCarModal();

        // Wait for modal to fully close before showing loading
        setTimeout(async() => {

            Swal.fire({
                title: 'Creating car...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
                showConfirmButton: false
            });

            try {
                // ============================================================
                // STEP 5: Send Create Request
                // ============================================================
                const response = await fetch('/admin/cars', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest'
                            // Note: Don't set Content-Type for FormData - browser sets it automatically with boundary
                    },
                    body: carFormData
                });

                // Parse server response
                const data = await response.json();
                console.log('Server response:', data);

                // ============================================================
                // STEP 6: Handle Server Response
                // ============================================================
                if (response.ok && data.success) {
                    // SUCCESS: Show success message and reload
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: data.message || 'Car added successfully',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // Reset form before reload
                        resetCarForm(addCarForm);
                        window.location.reload();
                    });

                } else {
                    // ERROR: Display validation or server errors
                    if (data.errors) {
                        console.error('Validation errors:', data.errors);
                        displayValidationErrors(data.errors);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to create car',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                }

            } catch (error) {
                // ============================================================
                // STEP 7: Handle Network/System Errors
                // ============================================================
                Swal.close();
                console.error('Create car error:', error);

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
    addCarModal.addEventListener('hidden.bs.modal', function() {
        resetCarForm(addCarForm);
    });

});