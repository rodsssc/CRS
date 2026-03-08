/**
 * ============================================================================
 * OPEN UPDATE MODAL
 * ============================================================================
 * 
 * Purpose: Populate and display the update modal when edit button is clicked
 * ============================================================================
 */

document.addEventListener('DOMContentLoaded', () => {

    // Attach click listeners to all edit buttons
    document.querySelectorAll('.btn-action[title="Edit"]').forEach(button => {
        button.addEventListener('click', function() {
            const carId = this.getAttribute('data-car-id');
            console.log('Edit button clicked for car ID:', carId);

            if (!carId) {
                console.error('No car ID found');
                return;
            }

            // Show loading state
            Swal.fire({
                title: 'Loading vehicle data...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
                showConfirmButton: false
            });

            // Fetch car data
            fetch(`/admin/cars/${carId}/edit`)
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Server response:', data);
                    Swal.close();

                    // Check if response is successful and has data
                    if (data && data.success && data.data) {
                        const car = data.data;
                        console.log('Car data:', car);

                        // ✅ POPULATE FORM - MATCHING YOUR EXACT HTML IDs:

                        // Hidden field - snake_case
                        document.getElementById('update_car_id').value = car.id;

                        // Input/Select fields - camelCase
                        document.getElementById('updateOwnerId').value = car.owner_id;
                        document.getElementById('updateBrand').value = car.brand;
                        document.getElementById('updateModel').value = car.model;
                        document.getElementById('updateYear').value = car.year;
                        document.getElementById('updateColor').value = car.color;
                        document.getElementById('updateCapacity').value = car.capacity;
                        document.getElementById('updateTransmissionType').value = car.transmission_type;
                        document.getElementById('updateFuelType').value = car.fuel_type;
                        document.getElementById('updateRentalPricePerDay').value = car.rental_price_per_day;

                        // Exception fields - snake_case
                        document.getElementById('update_plate_number').value = car.plate_number;
                        document.getElementById('update_status').value = car.status;

                        // Show current image if exists
                        const imagePreview = document.getElementById('current_image_preview');
                        if (car.image_path) {
                            imagePreview.src = `/storage/${car.image_path}`;
                            imagePreview.style.display = 'block';
                        } else {
                            imagePreview.style.display = 'none';
                        }

                        // Open the update modal
                        const updateModal = new bootstrap.Modal(document.getElementById('updateCarModal'));
                        updateModal.show();

                    } else {
                        // Handle case where data structure is unexpected
                        console.error('Unexpected response structure:', data);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to load vehicle data - unexpected response format',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                })
                .catch(error => {
                    Swal.close();
                    console.error('Error fetching car data:', error);

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load vehicle data. Please try again.',
                        confirmButtonColor: '#3085d6'
                    });
                });
        });
    });
});