// Update your existing validateClientVerificationFormData function
function validateClientVerificationFormData(data) {
    const requiredFields = [
        'clientId',
        'idType',
        'idNumber'
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

    // Validate image uploads
    if (!window.areAllImagesUploaded()) {
        Swal.fire({
            icon: 'error',
            title: 'Missing Documents',
            text: 'Please upload all required documents (ID Front, ID Back, and Selfie with ID)',
            confirmButtonColor: '#3085d6'
        });
        return false;
    }

    return true;
}

document.getElementById('saveClientVerification').addEventListener('click', async(e) => {
    e.preventDefault();

    const idType = document.getElementById('id_type').value;
    const idNumber = document.getElementById('idNumber').value;

    const front = document.getElementById('id_front_image').files[0];
    const back = document.getElementById('id_back_image').files[0];
    const selfie = document.getElementById('selfie_with_id').files[0];

    if (!idType || !idNumber || !front || !back || !selfie) {
        Swal.fire('Error', 'All fields are required', 'error');
        return;
    }

    if (!document.getElementById('verificationConsent').checked) {
        Swal.fire('Consent Required', 'Please agree before submitting', 'warning');
        return;
    }

    const formData = new FormData();
    formData.append('id_type', idType);
    formData.append('id_number', idNumber);
    formData.append('id_front_image', front);
    formData.append('id_back_image', back);
    formData.append('selfie_with_id', selfie);



    try {
        const response = await fetch('/client/verification/verification', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await response.json();
        Swal.close();

        if (!response.ok) throw data;

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Verification Submitted',
                text: 'Your documents have been sent. Please wait for admin approval.',
                toast: true,
                position: 'top-end',
                timer: 1500,
                timerProgressBar: true,
                showConfirmButton: false,


            }).then(() => {
                document.body.classList.add('fade-out');

                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 800);
            });
        }




    } catch (err) {
        Swal.fire(
            'Error',
            err.message || Object.values(err.errors || {}).flat().join('\n'),
            'error'
        );
    }
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