// profiling_save_update.js - SIMPLIFIED

function validateClientProfileFormData(data) {
    const requiredFields = [
        'clientId',
        'firstName',
        'lastName',
        'dateBirth',
        'facebook_name'
    ];

    for (const field of requiredFields) {
        if (!data[field] || data[field].trim() === '') {
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

document.addEventListener('DOMContentLoaded', function() {
    const profilingForm = document.getElementById('profilingForm');
    const clientId = document.getElementById('client_id');
    const saveClientProfiling = document.getElementById('saveClientProfiling');
    const backToProfiling = document.getElementById('backToProfiling');

    // if (!clientId) {
    //     console.log('no client id found');
    // }

    // if (!profilingForm) {
    //     console.log('no profiling form');
    // }

    // ============================================================
    // BACK TO PROFILING - Load existing data
    // ============================================================
    if (backToProfiling) {
        backToProfiling.addEventListener('click', async function(e) {
            e.preventDefault();

            if (!clientId || !clientId.value) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Client ID not found',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            const originalHtml = backToProfiling.innerHTML;
            backToProfiling.disabled = true;
            backToProfiling.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Loading...';

            setTimeout(async() => {
                Swal.fire({
                    title: 'Loading profiling data...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                    showConfirmButton: false
                });

                try {
                    const response = await fetch(`/client/verification/profile/${clientId.value}`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken(),
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        Swal.close();
                        populateProfilingForm(data.data);
                        switchBackToProfilingTab();

                        Swal.fire({
                            icon: 'success',
                            title: 'Data Loaded',
                            text: 'You can now edit your profile',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });

                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to load profiling data',
                            confirmButtonColor: '#3085d6'
                        });

                        backToProfiling.disabled = false;
                        backToProfiling.innerHTML = originalHtml;
                    }

                } catch (error) {
                    Swal.close();
                    // console.error('Error fetching profiling data:', error);

                    Swal.fire({
                        icon: 'error',
                        title: 'Server Error',
                        text: 'Failed to load profiling data.',
                        confirmButtonColor: '#3085d6'
                    });

                    backToProfiling.disabled = false;
                    backToProfiling.innerHTML = originalHtml;
                }

            }, 300);
        });
    }

    // ============================================================
    // SAVE BUTTON - Always uses POST (backend handles create/update)
    // ============================================================
    if (saveClientProfiling) {
        saveClientProfiling.addEventListener('click', async function(e) {
            e.preventDefault();

            const formData = {
                clientId: clientId.value,
                firstName: document.getElementById('firstName').value || '',
                lastName: document.getElementById('lastName').value || '',
                dateBirth: document.getElementById('dateBirth').value || '',
                address: document.getElementById('address').value || '',
                facebook_name: document.getElementById('facebook_name').value || '',
                nationality: document.getElementById('nationality').value || '',
                emergencyContactPhone: document.getElementById('emergencyContactPhone').value || '',
                emergencyContactName: document.getElementById('emergencyContactName').value || '',
            };

            // console.log('Saving profile with data:', formData);

            if (!validateClientProfileFormData(formData)) {
                return;
            }

            setTimeout(async() => {
                Swal.fire({
                    icon: 'loading',
                    title: 'Saving profile...',
                    toast: true,
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true
                });

                try {
                    // ✅ Always POST - backend uses updateOrCreate
                    const response = await fetch('/client/verification/profile', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken(),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            client_id: clientId.value,
                            first_name: firstName.value,
                            last_name: lastName.value,
                            date_birth: dateBirth.value,
                            address: address.value,
                            facebook_name: facebook_name.value,
                            nationality: nationality.value,
                            emergency_contact_name: emergencyContactName.value,
                            emergency_contact_phone: emergencyContactPhone.value,
                        })
                    });

                    const data = await response.json();
                    // console.log('Server response:', data);

                    if (response.ok && data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: data.message, // Backend message: "created" or "updated"
                            toast: true,
                            position: 'top-end',
                            timerProgressBar: true,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            switchToVerificationTab();
                        });

                    } else {
                        if (data.errors) {
                            console.error('Validation errors:', data.errors);
                            displayValidationErrors(data.errors);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'Failed to save profile',
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    }

                } catch (error) {
                    Swal.close();
                    console.error('Save profile error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Server Error',
                        toast: true,
                        text: 'Something went wrong. Please try again.',
                        confirmButtonColor: '#3085d6'
                    });
                }

            }, 300);
        });
    }
});

// Helper Functions
function populateProfilingForm(data) {
    const fields = {
        'firstName': data.first_name,
        'lastName': data.last_name,
        'dateBirth': data.date_birth,
        'address': data.address,
        'nationality': data.nationality,
        'emergencyContactName': data.emergency_contact_name,
        'emergencyContactPhone': data.emergency_contact_phone
    };

    for (const [fieldId, value] of Object.entries(fields)) {
        if (value) {
            const element = document.getElementById(fieldId);
            if (element) element.value = value;
        }
    }
}

function switchBackToProfilingTab() {
    const profilingTab = document.getElementById('profiling');
    const verificationTab = document.getElementById('verification');

    if (profilingTab && verificationTab) {
        verificationTab.classList.remove('show', 'active');
        profilingTab.classList.add('show', 'active');

        const progressBar = document.getElementById('formProgress');
        if (progressBar) {
            progressBar.style.width = '50%';
            progressBar.setAttribute('aria-valuenow', '50');
        }

        const step1 = document.getElementById('step1-indicator');
        const step2 = document.getElementById('step2-indicator');

        if (step1) {
            step1.classList.add('active');
            step1.classList.remove('completed');
        }
        if (step2) {
            step2.classList.remove('active');
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

function switchToVerificationTab() {
    const profilingTab = document.getElementById('profiling');
    const verificationTab = document.getElementById('verification');

    if (profilingTab && verificationTab) {
        profilingTab.classList.remove('show', 'active');
        verificationTab.classList.add('show', 'active');

        const progressBar = document.getElementById('formProgress');
        if (progressBar) {
            progressBar.style.width = '100%';
            progressBar.setAttribute('aria-valuenow', '100');
        }

        const step1 = document.getElementById('step1-indicator');
        const step2 = document.getElementById('step2-indicator');

        if (step1) step1.classList.add('completed');
        if (step2) step2.classList.add('active');

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

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