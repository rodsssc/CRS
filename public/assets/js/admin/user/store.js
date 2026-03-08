/**
 * ============================================================================
 * CREATE USER SCRIPT
 * ============================================================================
 * 
 * Purpose: Handles creating new users when "Create User" button is clicked
 * 
 * Flow:
 * 1. Validate form using HTML5 validation
 * 2. Collect form data
 * 3. Send POST request to server
 * 4. Show success/error message
 * 5. Reset form and close modal on success
 * 6. Reload page to show new user
 * 
 * Features:
 * - HTML5 form validation
 * - Real-time password validation
 * - Password visibility toggle
 * - Password confirmation matching
 * - Server-side validation error display
 * 
 * Dependencies: Bootstrap 5, SweetAlert2
 * ============================================================================
 */

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================
//user.store.js
/**
 * Get CSRF token from meta tag
 * @returns {string} CSRF token value
 */


/**
 * Close the add user modal and clean up Bootstrap artifacts
 * Removes backdrop and resets body styles
 */
function closeAddUserModal() {
    const modalElement = document.getElementById('addUserModal');

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
function validateFormData(data) {
    if (!data.name || !data.email || !data.password || !data.role) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Please fill in all required fields',
            confirmButtonColor: '#3085d6'
        });
        return false;
    }
    return true;
}

/**
 * Validate password meets requirements
 * @param {string} password - Password value
 * @returns {boolean} True if password is valid
 */
function validatePassword(password) {
    const hasLetters = /[a-zA-Z]/.test(password);
    const hasNumbers = /[0-9]/.test(password);
    const isLongEnough = password.length >= 8;

    if (!isLongEnough || !hasLetters || !hasNumbers) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Password must be at least 8 characters with letters and numbers',
            confirmButtonColor: '#3085d6'
        });
        return false;
    }
    return true;
}

/**
 * Validate password match
 * @param {string} password - Password value
 * @param {string} confirm - Confirmation password value
 * @returns {boolean} True if passwords match
 */
function validatePasswordMatch(password, confirm) {
    if (password !== confirm) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Passwords do not match',
            confirmButtonColor: '#3085d6'
        });
        return false;
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
function resetForm(form) {
    // Reset form to initial state
    form.reset();

    // Remove validation classes
    form.classList.remove('was-validated');

    // Clear all field validation states
    form.querySelectorAll('.is-invalid, .is-valid').forEach(el => {
        el.classList.remove('is-invalid', 'is-valid');
    });

    // Clear custom validity messages
    form.querySelectorAll('input').forEach(input => {
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
    const saveUserBtn = document.getElementById('saveUserBtn');
    const addUserForm = document.getElementById('addUserForm');
    const addUserModal = document.getElementById('addUserModal');


    if (!addUserForm) {
        console.error('Add user form not found');
        return;
    }

    if (!addUserModal) {
        console.error('Add user modal not found');
        return;
    }

    // ========================================================================
    // REAL-TIME PASSWORD VALIDATION
    // ========================================================================

    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');

    /**
     * Validate password as user types
     * Checks for minimum length and character requirements
     */
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const value = this.value;
            const hasLetters = /[a-zA-Z]/.test(value);
            const hasNumbers = /[0-9]/.test(value);
            const isLongEnough = value.length >= 8;

            if (isLongEnough && hasLetters && hasNumbers) {
                // Password meets requirements
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                // Password doesn't meet requirements
                this.setCustomValidity('Password must be at least 8 characters with letters and numbers');
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            }
        });
    }

    /**
     * Validate password confirmation as user types
     * Checks if confirmation matches password
     */
    if (confirmPasswordInput && passwordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            if (this.value !== passwordInput.value) {
                // Passwords don't match
                this.setCustomValidity('Passwords do not match');
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else {
                // Passwords match
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    }

    // ========================================================================
    // CREATE USER BUTTON HANDLER
    // ========================================================================

    /**
     * Handle create user button click
     */
    saveUserBtn.addEventListener('click', async function(e) {
        e.preventDefault();

        // ====================================================================
        // STEP 1: Collect Form Data
        // ====================================================================
        const formData = {
            name: document.getElementById('name').value.trim() || '',
            email: document.getElementById('email').value.trim() || '',
            phone: document.getElementById('phone').value.trim() || '',
            password: document.getElementById('password').value || '',
            confirm: document.getElementById('confirmPassword').value || '',
            role: document.getElementById('role').value || ''
        };

        console.log('Creating user with data:', {...formData, password: '***', confirm: '***' });

        // ====================================================================
        // STEP 2: Validate Form Data
        // ====================================================================

        // Validate required fields
        if (!validateFormData(formData)) {
            return;
        }

        // Validate password strength
        if (!validatePassword(formData.password)) {
            return;
        }

        // Validate password match
        if (!validatePasswordMatch(formData.password, formData.confirm)) {
            return;
        }

        // ====================================================================
        // STEP 3: Build Request Payload
        // ====================================================================
        const userData = {
            name: formData.name,
            email: formData.email,
            phone: formData.phone || null,
            password: formData.password,
            password_confirmation: formData.confirm,
            role: formData.role
        };

        console.log('Request payload:', {...userData, password: '***', password_confirmation: '***' });

        // ====================================================================
        // STEP 4: Close Modal and Show Loading
        // ====================================================================
        closeAddUserModal();

        // Wait for modal to fully close before showing loading
        setTimeout(async() => {

            Swal.fire({
                title: 'Creating user...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
                showConfirmButton: false
            });

            try {
                // ============================================================
                // STEP 5: Send Create Request
                // ============================================================
                const response = await fetch('/admin/user', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(userData)
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
                        text: data.message || 'User created successfully',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // Reset form before reload
                        resetForm(addUserForm);
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
                            text: data.message || 'Failed to create user',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                }

            } catch (error) {
                // ============================================================
                // STEP 7: Handle Network/System Errors
                // ============================================================
                Swal.close();
                console.error('Create user error:', error);

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
    addUserModal.addEventListener('hidden.bs.modal', function() {
        resetForm(addUserForm);
    });

    console.log('User creation script initialized successfully');
});