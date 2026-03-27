// assets/js/client/verification/verification_store.js

document.addEventListener('DOMContentLoaded', function() {

    // =========================================================
    // ELEMENT REFS
    // =========================================================
    const fileInput = document.getElementById('id_front_image');
    const uploadLabel = document.getElementById('uploadLabel');
    const previewContainer = document.getElementById('frontPreviewContainer');
    const previewImg = document.getElementById('frontPreview');
    const removeBtn = document.getElementById('removeImageBtn');
    const saveBtn = document.getElementById('saveClientVerification');

    // =========================================================
    // IMAGE PREVIEW
    // =========================================================
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;

            // Validate type
            const allowed = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!allowed.includes(file.type)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid File Type',
                    text: 'Please upload a JPG or PNG image only.',
                    confirmButtonColor: '#3085d6',
                });
                this.value = '';
                return;
            }

            // Validate size (5 MB)
            if (file.size > 5 * 1024 * 1024) {
                Swal.fire({
                    icon: 'warning',
                    title: 'File Too Large',
                    text: 'Please upload an image under 5 MB.',
                    confirmButtonColor: '#3085d6',
                });
                this.value = '';
                return;
            }

            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                if (uploadLabel) uploadLabel.classList.add('d-none');
                if (previewContainer) previewContainer.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        });
    }

    // Remove image
    if (removeBtn) {
        removeBtn.addEventListener('click', function() {
            if (fileInput) fileInput.value = '';
            if (previewImg) previewImg.src = '';
            if (previewContainer) previewContainer.classList.add('d-none');
            if (uploadLabel) uploadLabel.classList.remove('d-none');
        });
    }

    // =========================================================
    // FORM SUBMIT
    // =========================================================
    if (!saveBtn) return;

    saveBtn.addEventListener('click', async function(e) {
        e.preventDefault();

        // Safe element reads — no optional chaining on .value
        const idTypeEl = document.getElementById('id_type');
        const idNumberEl = document.getElementById('idNumber');
        const consentEl = document.getElementById('verificationConsent');

        const idType = idTypeEl ? idTypeEl.value.trim() : '';
        const idNumber = idNumberEl ? idNumberEl.value.trim() : '';
        const front = fileInput ? fileInput.files[0] : null;
        const consent = consentEl ? consentEl.checked : false;

        // Validate
        if (!idType || !idNumber || !front) {
            Swal.fire({
                icon: 'error',
                title: 'Incomplete Form',
                text: 'Please fill in all required fields and upload your ID image.',
                confirmButtonColor: '#3085d6',
            });
            return;
        }

        if (!consent) {
            Swal.fire({
                icon: 'warning',
                title: 'Consent Required',
                text: 'Please agree to the declaration before submitting.',
                confirmButtonColor: '#3085d6',
            });
            return;
        }

        // Build FormData
        const formData = new FormData();
        formData.append('id_type', idType);
        formData.append('id_number', idNumber);
        formData.append('id_front_image', front);

        // Show loading — NOT a toast, so allowOutsideClick is valid here
        Swal.fire({
            title: 'Submitting…',
            text: 'Please wait while we upload your document.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: function() {
                Swal.showLoading();
            },
        });

        try {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const response = await fetch('/client/verification/verification', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfMeta ? csrfMeta.content : '',
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const data = await response.json();
            Swal.close();

            if (!response.ok) {
                throw data;
            }

            if (data.success) {
                // Success toast — no allowOutsideClick on toasts
                await Swal.fire({
                    icon: 'success',
                    title: 'Verification Submitted',
                    text: 'Your documents have been sent. Please wait for admin approval.',
                    toast: true,
                    position: 'top-end',
                    timer: 1500,
                    timerProgressBar: true,
                    showConfirmButton: false,
                });

                document.body.classList.add('fade-out');
                setTimeout(function() {
                    window.location.href = data.redirect;
                }, 800);
            }

        } catch (err) {
            Swal.close();
            const message = err.message ||
                (err.errors ? Object.values(err.errors).flat().join('\n') : '') ||
                'Something went wrong. Please try again.';

            Swal.fire({
                icon: 'error',
                title: 'Submission Failed',
                text: message,
                confirmButtonColor: '#dc3545',
            });
        }
    });

});