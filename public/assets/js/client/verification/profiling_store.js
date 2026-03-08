// Profiling and verification Form


function validateClientProfileFormData(data) {
    const requiredFields = [
        'clientId',
        'firstName',
        'lastName',
        'dateBirth'
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
    const clientId = document.getElementById('client_id')
    const saveClientProfiling = document.getElementById('saveClientProfiling');

    if (!clientId) {
        console.log('no client id found')
    }

    if (!profilingForm) {
        console.log('no profileing form')
    }


    saveClientProfiling.addEventListener('click', async function(e) {
        e.preventDefault();

        const formData = {
            clientId: clientId.value,
            firstName: document.getElementById('firstName').value || '',
            lastName: document.getElementById('lastName').value || '',
            dateBirth: document.getElementById('dateBirth').value || '',
            address: document.getElementById('address').value || '',
            nationality: document.getElementById('nationality').value || '',
            emergencyContactPhone: document.getElementById('emergencyContactPhone').value || '',
            emergencyContactName: document.getElementById('emergencyContactName').value || '',

        }

        console.log('Creating profile with data:', formData);
        if (!validateClientProfileFormData(formData)) {
            return;
        }




        setTimeout(async() => {

            Swal.fire({
                icon: 'loading',
                title: 'Creating profile...',
                toast: true,
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true

            });

            try {
                // ============================================================
                // STEP 5: Send Create Request
                // ============================================================
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
                        nationality: nationality.value,
                        emergency_contact_name: emergencyContactName.value,
                        emergency_contact_phone: emergencyContactPhone.value,
                    })
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
                        text: data.message || 'Creating profile  successfully',
                        toast: true,
                        position: 'top-end',
                        timerProgressBar: true,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {

                        switchToVerificationTab();
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
                            text: data.message || 'Failed to create profile',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                }

            } catch (error) {
                // ============================================================
                // STEP 7: Handle Network/System Errors
                // ============================================================
                Swal.close();
                console.error('Create profile error:', error);

                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    toast: true,
                    text: 'Something went wrong. Please try again.',
                    confirmButtonColor: '#3085d6'
                });
            }

        }, 300); // 300ms delay ensures smooth modal transition

    })




});

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



function switchToVerificationTab() {
    // Hide profiling tab
    const profilingTab = document.getElementById('profiling');
    const verificationTab = document.getElementById('verification');

    if (profilingTab && verificationTab) {
        profilingTab.classList.remove('show', 'active');
        verificationTab.classList.add('show', 'active');

        // Update progress bar
        const progressBar = document.getElementById('formProgress');
        if (progressBar) {
            progressBar.style.width = '100%';
            progressBar.setAttribute('aria-valuenow', '100');
        }

        // Update step indicators
        const step1 = document.getElementById('step1-indicator');
        const step2 = document.getElementById('step2-indicator');

        if (step1) step1.classList.add('completed');
        if (step2) step2.classList.add('active');

        // Scroll to top of form
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}