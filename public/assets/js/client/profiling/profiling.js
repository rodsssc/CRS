document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('profilingForm');
    const submitBtn = form.querySelector('button[type="submit"]');

    if (!form || !submitBtn) return;

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]').content || '';
    }

    function clearErrors() {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
        });
    }

    function showFieldErrors(errors) {
        clearErrors();

        const fieldMap = {
            firstName: 'firstName',
            lastName: 'lastName',
            dateBirth: 'dateBirth',
            nationality: 'nationality',
            address: 'address',
            facebook_name: 'facebook_name',
            emergencyContactName: 'emergencyContactName',
            emergencyContactPhone: 'emergencyContactPhone',
            // Laravel snake_case versions
            first_name: 'firstName',
            last_name: 'lastName',
            date_birth: 'dateBirth',
            emergency_contact_name: 'emergencyContactName',
            emergency_contact_phone: 'emergencyContactPhone',
        };

        for (const [key, id] of Object.entries(fieldMap)) {
            if (!errors[key]) continue;

            const field = document.getElementById(id);
            if (!field) continue;

            field.classList.add('is-invalid');

            const wrapper = field.closest('div');
            const feedback = wrapper.querySelector('.invalid-feedback');

            if (feedback) {
                feedback.textContent = errors[key][0];
                feedback.style.display = 'block';
            }
        }
    }

    // Manual check for required fields before sending to server
    function validateForm() {
        const required = ['firstName', 'lastName', 'dateBirth', 'nationality', 'address'];
        let isValid = true;

        clearErrors();

        required.forEach(id => {
            const field = document.getElementById(id);
            if (!field) return;

            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;

                const wrapper = field.closest('div');
                const feedback = wrapper.querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.style.display = 'block';
                }
            }
        });

        return isValid;
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Client-side validation first
        if (!validateForm()) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Fields',
                text: 'Please fill in all required fields before continuing.',
            });
            return;
        }

        const originalHtml = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';

        Swal.fire({
            title: 'Saving profile…',
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
                if (data.errors) showFieldErrors(data.errors);

                Swal.fire({
                    icon: 'error',
                    title: 'Oops!',
                    text: data.message || 'Please fix the errors and try again.',
                });

                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHtml;
                return;
            }

            // Success → redirect
            Swal.fire({
                icon: 'success',
                title: 'Profile Saved!',
                text: 'Redirecting to ID verification…',
                timer: 1500,
                timerProgressBar: true,
                showConfirmButton: false,
            }).then(() => {
                window.location.href = data.redirect || '/client/verification';
            });

        } catch (err) {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Network Error',
                text: 'Please check your connection and try again.',
            });

            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHtml;
        }
    });
});