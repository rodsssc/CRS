document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('verificationForm');
    const fileInput = document.getElementById('id_front_image');
    const uploadLabel = document.getElementById('uploadLabel');
    const previewWrap = document.getElementById('frontPreviewContainer');
    const previewImg = document.getElementById('frontPreview');
    const removeBtn = document.getElementById('removeImageBtn');
    const submitBtn = document.getElementById('saveClientVerification');
    const consent = document.getElementById('verificationConsent');

    if (!form || !fileInput) return;

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]').content || '';
    }

    // ── Image preview ────────────────────────────────────────────
    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = e => {
            previewImg.src = e.target.result;
            uploadLabel.classList.add('d-none');
            previewWrap.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    });

    // ── Remove image ─────────────────────────────────────────────
    removeBtn.addEventListener('click', function() {
        fileInput.value = '';
        previewImg.src = '';
        previewWrap.classList.add('d-none');
        uploadLabel.classList.remove('d-none');
    });

    // ── Form submit ──────────────────────────────────────────────
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const idType = document.getElementById('id_type').value;
        const idNumber = document.getElementById('idNumber').value.trim();
        const file = fileInput.files[0];

        // Client-side guards
        if (!idType) {
            return Swal.fire({ icon: 'error', title: 'Missing Field', text: 'Please select an ID type.' });
        }
        if (!idNumber) {
            return Swal.fire({ icon: 'error', title: 'Missing Field', text: 'Please enter your ID number.' });
        }
        if (!file) {
            return Swal.fire({ icon: 'error', title: 'Missing Document', text: 'Please upload your ID image.' });
        }
        if (!consent.checked) {
            return Swal.fire({ icon: 'warning', title: 'Consent Required', text: 'Please certify that your information is accurate.' });
        }

        submitBtn.disabled = true;

        Swal.fire({
            title: 'Submitting…',
            text: 'Please wait while we upload your document.',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading(),
        });

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
            });

            const data = await response.json();
            Swal.close();

            if (!response.ok) {
                // Highlight invalid fields
                if (data.errors) {
                    if (data.errors.id_type) document.getElementById('id_type').classList.add('is-invalid');
                    if (data.errors.id_number) document.getElementById('idNumber').classList.add('is-invalid');
                    if (data.errors.id_front_image) fileInput.classList.add('is-invalid');
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Submission Failed',
                    text: data.message || 'Please fix the errors and try again.',
                });

                submitBtn.disabled = false;
                return;
            }

            // ── Success → redirect ───────────────────────────────
            Swal.fire({
                icon: 'success',
                title: 'Verification Submitted!',
                text: data.message || 'Your documents have been sent for review.',
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false,
            }).then(() => {
                window.location.href = data.redirect || '/client/home';
            });

        } catch (err) {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Connection Error',
                text: 'Please check your connection and try again.',
            });

            submitBtn.disabled = false;
        }
    });
});