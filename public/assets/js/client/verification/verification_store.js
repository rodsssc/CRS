document.addEventListener('DOMContentLoaded', function() {
    // ─── Elements ────────────────────────────────────────────────────────────────
    const frontUploadArea = document.getElementById('frontUploadArea');
    const frontEl = document.getElementById('id_front_image');
    const frontPreview = document.getElementById('frontPreview');
    const frontPreviewContainer = document.getElementById('frontPreviewContainer');
    const uploadLabel = document.getElementById('uploadLabel');
    const removeImageBtn = document.getElementById('removeImageBtn');
    const verificationForm = document.getElementById('verificationForm');

    // Safely get the submit URL from meta tag or use default
    let submitUrl = '/client/verification/submit';
    const metaUrl = document.querySelector('meta[name="verification-submit-url"]');
    if (metaUrl && metaUrl.content) {
        submitUrl = metaUrl.content;
    }

    // Safely get CSRF token
    let csrfToken = '';
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (csrfMeta && csrfMeta.content) {
        csrfToken = csrfMeta.content;
    }

    // Check if we're on the verification page (if upload area doesn't exist, exit)
    if (!frontUploadArea) return;

    // ─── Clear previous errors on input focus ─────────────────────────────────────
    function clearFieldError(fieldId) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.classList.remove('is-invalid');
            const feedback = field.parentElement.querySelector('.invalid-feedback');
            if (feedback) feedback.style.display = 'none';
        }
    }

    // Add focus event listeners to clear errors
    const idTypeEl = document.getElementById('id_type');
    const idNumberEl = document.getElementById('idNumber');

    if (idTypeEl) {
        idTypeEl.addEventListener('focus', () => clearFieldError('id_type'));
    }
    if (idNumberEl) {
        idNumberEl.addEventListener('focus', () => clearFieldError('idNumber'));
    }
    if (frontEl) {
        frontEl.addEventListener('focus', () => {
            frontEl.classList.remove('is-invalid');
            const feedback = frontUploadArea.querySelector('.invalid-feedback');
            if (feedback) feedback.style.display = 'none';
        });
    }

    // ─── Upload Area Click ───────────────────────────────────────────────────────
    frontUploadArea.addEventListener('click', (e) => {
        // Don't re-trigger if clicking the remove button
        if (e.target.closest('#removeImageBtn')) return;
        // Don't re-trigger if preview is visible and click is on the image
        if (!frontPreviewContainer.classList.contains('d-none')) return;
        frontEl.click();
    });

    // ─── Drag & Drop ─────────────────────────────────────────────────────────────
    frontUploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        frontUploadArea.classList.add('drag-over');
    });

    frontUploadArea.addEventListener('dragleave', () => {
        frontUploadArea.classList.remove('drag-over');
    });

    frontUploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        frontUploadArea.classList.remove('drag-over');
        const file = e.dataTransfer.files[0];
        if (file) handleImageFile(file);
    });

    // ─── File Input Change ───────────────────────────────────────────────────────
    frontEl.addEventListener('change', () => {
        const file = frontEl.files[0];
        if (file) handleImageFile(file);
    });

    // ─── Remove Image ─────────────────────────────────────────────────────────────
    if (removeImageBtn) {
        removeImageBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            resetUpload();
        });
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────────
    function handleImageFile(file) {
        const allowed = ['image/jpeg', 'image/jpg', 'image/png'];

        if (!allowed.includes(file.type)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid File',
                text: 'Only JPG and PNG images are allowed.'
            });
            resetUpload();
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            Swal.fire({
                icon: 'error',
                title: 'File Too Large',
                text: 'Image must be under 5MB.'
            });
            resetUpload();
            return;
        }

        // If file came from drag-drop, transfer it to the real input
        if (!frontEl.files[0] || frontEl.files[0] !== file) {
            const dt = new DataTransfer();
            dt.items.add(file);
            frontEl.files = dt.files;
        }

        // Show preview, hide label
        const reader = new FileReader();
        reader.onload = (e) => {
            frontPreview.src = e.target.result;
            frontPreviewContainer.classList.remove('d-none');
            uploadLabel.classList.add('d-none');
            frontUploadArea.classList.add('has-image');
        };
        reader.readAsDataURL(file);

        // Clear any previous file error
        frontEl.classList.remove('is-invalid');
    }

    function resetUpload() {
        frontEl.value = '';
        frontPreview.src = '';
        frontPreviewContainer.classList.add('d-none');
        uploadLabel.classList.remove('d-none');
        frontUploadArea.classList.remove('has-image');
    }

    // ─── Display Field Errors ────────────────────────────────────────────────────
    function displayFieldErrors(errors) {
        // Clear all existing errors first
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        document.querySelectorAll('.invalid-feedback').forEach(el => {
            el.style.display = 'none';
        });

        // Display new errors
        if (errors.id_type) {
            const field = document.getElementById('id_type');
            if (field) {
                field.classList.add('is-invalid');
                const feedback = field.parentElement.querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.textContent = errors.id_type[0];
                    feedback.style.display = 'block';
                }
            }
        }

        if (errors.id_number) {
            const field = document.getElementById('idNumber');
            if (field) {
                field.classList.add('is-invalid');
                const feedback = field.parentElement.querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.textContent = errors.id_number[0];
                    feedback.style.display = 'block';
                }
            }
        }

        if (errors.id_front_image) {
            const field = document.getElementById('id_front_image');
            if (field) {
                field.classList.add('is-invalid');
                const feedback = frontUploadArea.querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.textContent = errors.id_front_image[0];
                    feedback.style.display = 'block';
                }
            }
        }

        if (errors.general) {
            // General errors are shown in Swal
            return errors.general[0];
        }

        return null;
    }

    // ─── Form Submit ──────────────────────────────────────────────────────────────────
    if (verificationForm) {
        verificationForm.addEventListener('submit', async(e) => {
            e.preventDefault();

            const idTypeEl = document.getElementById('id_type');
            const idNumberEl = document.getElementById('idNumber');
            const consentEl = document.getElementById('verificationConsent');

            // Guard: all elements must exist
            if (!idTypeEl || !idNumberEl || !frontEl || !consentEl) {
                console.error('One or more form elements not found. Check your input IDs.');
                Swal.fire({
                    icon: 'error',
                    title: 'Form Error',
                    text: 'Form elements missing. Please refresh the page.'
                });
                return;
            }

            const idType = idTypeEl.value.trim();
            const idNumber = idNumberEl.value.trim();
            const front = frontEl.files[0];
            const consent = consentEl.checked;

            // ── Validation ──
            let hasError = false;

            if (!idType) {
                Swal.fire({ icon: 'error', title: 'Missing Field', text: 'Please select an ID type.' });
                hasError = true;
            } else if (!idNumber) {
                Swal.fire({ icon: 'error', title: 'Missing Field', text: 'Please enter your ID number.' });
                hasError = true;
            } else if (!front) {
                Swal.fire({ icon: 'error', title: 'Missing Document', text: 'Please upload your ID image.' });
                hasError = true;
            } else if (!consent) {
                Swal.fire({ icon: 'warning', title: 'Consent Required', text: 'Please agree to the terms before submitting.' });
                hasError = true;
            }

            if (hasError) return;

            const formData = new FormData();
            formData.append('id_type', idType);
            formData.append('id_number', idNumber);
            formData.append('id_front_image', front);

            // ── Show progress bar ──
            const progressContainer = document.getElementById('uploadProgressContainer');
            const progressBar = document.getElementById('uploadProgressBar');
            const progressText = document.getElementById('uploadProgressText');

            if (progressContainer) progressContainer.classList.remove('d-none');

            // Simulate progress while uploading
            let progress = 0;
            const progressInterval = setInterval(() => {
                if (progress < 85) {
                    progress += Math.random() * 12;
                    progress = Math.min(progress, 85);
                    if (progressBar) progressBar.style.width = progress + '%';
                    if (progressText) progressText.textContent = Math.round(progress) + '%';
                }
            }, 200);

            Swal.fire({
                title: 'Submitting...',
                text: 'Please wait while we upload your documents.',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading(),
            });

            try {
                const response = await fetch(submitUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await response.json();

                // Complete progress bar
                clearInterval(progressInterval);
                if (progressBar) progressBar.style.width = '100%';
                if (progressText) progressText.textContent = '100%';

                Swal.close();

                if (!response.ok) {
                    if (progressContainer) progressContainer.classList.add('d-none');

                    // Check if there are field-specific errors
                    if (data.errors) {
                        // Display field-specific errors
                        const generalError = displayFieldErrors(data.errors);

                        // Get the main error message
                        let errorMessage = data.message || 'Please check the form for errors.';

                        // If there's a specific field error with a message, use that
                        if (data.errors.id_number) {
                            errorMessage = data.errors.id_number[0];
                        } else if (data.errors.id_type) {
                            errorMessage = data.errors.id_type[0];
                        } else if (data.errors.id_front_image) {
                            errorMessage = data.errors.id_front_image[0];
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Submission Failed',
                            text: errorMessage,
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    // General error without field specifics
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Something went wrong. Please try again.'
                    });
                    return;
                }

                // Success - clear any existing errors
                displayFieldErrors({});

                Swal.fire({
                    icon: 'success',
                    title: 'Verification Submitted!',
                    text: data.message || 'Your documents have been sent. Please wait for admin approval.',
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: true,
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed || result.dismiss === Swal.DismissReason.timer) {
                        if (data.redirect) {
                            document.body.classList.add('fade-out');
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 500);
                        } else {
                            // Reload the page to show updated status
                            window.location.reload();
                        }
                    }
                });

            } catch (err) {
                clearInterval(progressInterval);
                if (progressContainer) progressContainer.classList.add('d-none');
                Swal.close();

                console.error('Verification submit error:', err);

                let errorMessage = 'An unexpected error occurred. Please check your connection and try again.';

                if (err.message) {
                    errorMessage = err.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Connection Error',
                    text: errorMessage,
                    confirmButtonText: 'Try Again'
                });
            }
        });
    }
});