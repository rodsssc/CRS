function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

/**
 * Close a Bootstrap modal safely and clean artifacts
 */
function closeModal(modalId) {
    const modalElement = document.getElementById(modalId);

    if (modalElement) {
        const modal = bootstrap.Modal.getInstance(modalElement) ||
            new bootstrap.Modal(modalElement);
        modal.hide();
    }

    // Cleanup Bootstrap leftovers
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
}

/**
 * Show validation errors from Laravel
 */
function displayValidationErrors(errors) {
    const errorList = Object.entries(errors)
        .map(([field, messages]) => `${field}: ${messages.join(', ')}`)
        .join('\n');

    Swal.fire({
        icon: 'error',
        title: 'Validation Error',
        html: `<pre style="text-align:left;font-size:14px">${errorList}</pre>`,
        confirmButtonColor: '#3085d6'
    });
}


function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message,
        confirmButtonColor: '#3085d6'
    });
}