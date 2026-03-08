// car/show.js
document.addEventListener('DOMContentLoaded', function() {
    const viewCarModal = document.getElementById('viewCarModal');

    if (!viewCarModal) {
        console.error('View car modal not found - viewCarModal element missing from HTML');
        return;
    }
    console.log('View car script initialized successfully');

    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-action[title="View"]')) {
            const button = e.target.closest('.btn-action[title="View"]');
            const carId = button.getAttribute('data-car-id');

            if (carId) {
                viewCar(carId);
            } else {
                console.error('View button clicked but no car ID found');
            }
        }
    });
});

async function viewCar(carId) {
    try {
        Swal.fire({
            title: 'Loading....',
            text: 'Please wait while we fetch the car details',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
            showConfirmButton: false
        });

        const response = await fetch(`/admin/cars/${carId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log('Car data received:', data);


        Swal.close();

        if (data.success && data.data) {
            populateCarModal(data.data);
            openViewModal();


        } else {
            showError(data.message || 'Failed to load car data');
        }

    } catch (error) {
        Swal.close();
        console.error('Error loading car:', error);
        showError('An error occurred while loading car data. Please try again.');
    }
}

function populateCarModal(carData) {
    console.log('Populating modal with data:', carData);

    // Car Image
    const carImage = document.getElementById('viewCarImage');
    if (carImage) {
        carImage.src = carData.image_path ?
            `/storage/${carData.image_path}` :
            'https://via.placeholder.com/500x350?text=No+Image';
        carImage.alt = `${carData.brand || 'Unknown'} ${carData.model || 'Car'}`;
    }

    // Status Badge
    const statusBadge = document.getElementById('viewStatus');
    if (statusBadge) {
        const status = carData.status || 'unknown';
        statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        statusBadge.className = 'status-tag shadow-sm';

        switch (status.toLowerCase()) {
            case 'available':
                statusBadge.classList.add('status-available');
                break;
            case 'rented':
                statusBadge.classList.add('status-rented');
                break;
            case 'maintenance':
                statusBadge.classList.add('status-maintenance');
                break;
            default:
                statusBadge.classList.add('status-available');
        }
    }

    // Quick Stats - Year (Left Column)
    setElementText('viewYear', carData.year);

    // Quick Stats - Capacity (Left Column)
    setElementText('viewCapacity', carData.capacity);

    // Quick Stats - Transmission (Left Column)
    setElementText('viewTransmission', carData.transmission_type);

    // Quick Stats - Fuel Type (Left Column)
    setElementText('viewFuelType', carData.fuel_type);


    // Rental Price - FIXED
    const priceElement = document.getElementById('viewRentalPrice');


    if (priceElement) {
        const rentalPrice = carData.rental_price_per_day ?
            parseFloat(carData.rental_price_per_day).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) :
            '0.00';
        priceElement.textContent = rentalPrice;
        console.log("Price inserted:", rentalPrice);
    }

    // Vehicle Header (Right Column)
    setElementText('viewBrand', carData.brand);
    setElementText('viewModel', carData.model);
    setElementText('viewPlateNumber', carData.plate_number);

    // Owner Information - FIXED with optional chaining
    setElementText('viewOwnerName', carData.owner.name);



    // Vehicle Specifications (Right Column)
    setElementText('viewPlateNumber2', carData.plate_number);
    setElementText('viewBrand2', carData.brand);
    setElementText('viewModel2', carData.model);
    setElementText('viewColor', carData.color);
    setElementText('viewYear2', carData.year);
    setElementText('viewCapacity2', carData.capacity);
    setElementText('viewTransmission2', carData.transmission_type);
    setElementText('viewFuelType2', carData.fuel_type);

    // Link Edit Button with car ID
    const editBtn = document.getElementById('editCarBtn');
    if (editBtn && carData.id) {
        editBtn.setAttribute('data-car-id', carData.id);
        console.log('Edit button linked with car ID:', carData.id);
    }
}

// Helper function to set element text content
function setElementText(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = value || 'N/A';
    }
}

function openViewModal() {
    const viewModalElement = document.getElementById('viewCarModal');

    if (!viewModalElement) {
        console.error('View modal element not found');
        showError('Unable to open view modal');
        return;
    }

    let viewModal = bootstrap.Modal.getInstance(viewModalElement);

    if (!viewModal) {
        viewModal = new bootstrap.Modal(viewModalElement, {
            backdrop: 'static',
            keyboard: true
        });
    }

    viewModal.show();
}

function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: message,
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'OK'
    });
}