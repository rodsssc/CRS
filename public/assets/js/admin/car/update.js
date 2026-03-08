// ============================================================================
// UPDATE CAR SCRIPT
// Dependencies: Bootstrap 5, SweetAlert2
// ============================================================================

/**
 * Validate required car fields
 */
function validateCarForm(data) {
    const requiredFields = [
        'owner_id', 'plate_number', 'brand', 'model', 'year',
        'color', 'capacity', 'transmission_type', 'fuel_type',
        'rental_price_per_day'
    ];

    const isValid = requiredFields.every(
        field => data[field] !== null && data[field] !== ''
    );

    if (!isValid) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Please fill in all required fields',
            confirmButtonColor: '#3085d6'
        });
    }

    return isValid;
}

document.addEventListener('DOMContentLoaded', () => {
    const updateCarBtn = document.getElementById('updateCarBtn');
    if (!updateCarBtn) return;

    updateCarBtn.addEventListener('click', async() => {

        // ====================================================================
        // STEP 1: Collect Form Data
        // ====================================================================
        const carId = document.getElementById('update_car_id').value;
        const imageFile = document.getElementById('update_image').files[0];

        const form = {
            owner_id: document.getElementById('updateOwnerId').value,
            plate_number: document.getElementById('update_plate_number').value,
            brand: document.getElementById('updateBrand').value,
            model: document.getElementById('updateModel').value,
            year: document.getElementById('updateYear').value,
            color: document.getElementById('updateColor').value,
            capacity: document.getElementById('updateCapacity').value,
            transmission_type: document.getElementById('updateTransmissionType').value,
            fuel_type: document.getElementById('updateFuelType').value,
            rental_price_per_day: document.getElementById('updateRentalPricePerDay').value,
            status: document.getElementById('update_status').value
        };

        if (!validateCarForm(form)) return;

        // ====================================================================
        // STEP 2: Build FormData Payload
        // ====================================================================
        const payload = new FormData();
        Object.entries(form).forEach(([key, value]) => payload.append(key, value));

        if (imageFile) payload.append('image', imageFile);

        payload.append('_method', 'PUT'); // Laravel requirement

        // ====================================================================
        // STEP 3: Close Modal & Show Loader
        // ====================================================================
        closeModal('updateCarModal');

        setTimeout(async() => {
            Swal.fire({
                title: 'Updating car...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
                showConfirmButton: false
            });

            try {
                // ============================================================
                // STEP 4: Send Request
                // ============================================================
                const response = await fetch(`/admin/cars/${carId}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: payload
                });

                const data = await response.json();

                // ============================================================
                // STEP 5: Handle Response
                // ============================================================
                if (response.ok && data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: data.message || 'Car updated successfully',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    data.errors ?
                        displayValidationErrors(data.errors) :
                        Swal.fire('Error', data.message || 'Update failed', 'error');
                }

            } catch (err) {
                Swal.fire('Server Error', 'Please try again.', 'error');
                console.error(err);
            }
        }, 300);
    });
});