/**
 * ============================================
 * VERIFICATION UPLOAD HANDLER
 * Handles file uploads, previews, and validation
 * ============================================
 */

document.addEventListener('DOMContentLoaded', function() {

    // Configuration
    const uploadConfig = {
        maxSize: 5 * 1024 * 1024, // 5MB
        allowedTypes: ['image/jpeg', 'image/jpg', 'image/png'],
        allowedExtensions: ['.jpg', '.jpeg', '.png']
    };

    // Upload areas
    const uploadAreas = {
        front: {
            area: document.getElementById('frontUploadArea'),
            input: document.getElementById('id_front_image'),
            preview: document.getElementById('frontPreview'),
            label: null,
            previewContainer: null
        },
        back: {
            area: document.getElementById('backUploadArea'),
            input: document.getElementById('id_back_image'),
            preview: document.getElementById('backPreview'),
            label: null,
            previewContainer: null
        },
        selfie: {
            area: document.getElementById('selfieUploadArea'),
            input: document.getElementById('selfie_with_id'),
            preview: document.getElementById('selfiePreview'),
            label: null,
            previewContainer: null
        }
    };

    // Initialize upload areas
    Object.keys(uploadAreas).forEach(key => {
        const upload = uploadAreas[key];
        if (upload.area && upload.input) {
            upload.label = upload.area.querySelector('.upload-label-compact');
            upload.previewContainer = upload.area.querySelector('.preview-container');
            initializeUploadArea(upload, key);
        }
    });

    /**
     * Initialize upload area with all event listeners
     */
    function initializeUploadArea(upload, type) {
        // Click to upload
        upload.area.addEventListener('click', (e) => {
            if (!e.target.closest('.remove-image')) {
                upload.input.click();
            }
        });

        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            upload.area.addEventListener(eventName, preventDefaults, false);
        });

        // Highlight on drag over
        ['dragenter', 'dragover'].forEach(eventName => {
            upload.area.addEventListener(eventName, () => {
                upload.area.classList.add('drag-over');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            upload.area.addEventListener(eventName, () => {
                upload.area.classList.remove('drag-over');
            }, false);
        });

        // Handle dropped files
        upload.area.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;

            if (files.length > 0) {
                // Create a new FileList-like object
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(files[0]);
                upload.input.files = dataTransfer.files;

                handleFileSelect(upload.input, type);
            }
        }, false);

        // Handle file input change
        upload.input.addEventListener('change', function() {
            handleFileSelect(this, type);
        });

        // Remove image button
        const removeBtn = upload.area.querySelector('.remove-image');
        if (removeBtn) {
            removeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                removeImage(upload);
            });
        }
    }

    /**
     * Prevent default drag behaviors
     */
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    /**
     * Handle file selection and validation
     */
    function handleFileSelect(input, type) {
        const file = input.files[0];

        if (!file) return;

        // Get upload object
        const upload = uploadAreas[type];

        // Validate file
        const validation = validateFile(file);
        if (!validation.valid) {
            showValidationError(validation.message, input);
            input.value = '';
            return;
        }

        // Show preview
        displayPreview(file, upload, input);
    }

    /**
     * Validate file type and size
     */
    function validateFile(file) {
        // Check file type
        if (!uploadConfig.allowedTypes.includes(file.type)) {
            return {
                valid: false,
                message: 'Invalid file type. Please upload JPG or PNG images only.'
            };
        }

        // Check file size
        if (file.size > uploadConfig.maxSize) {
            const sizeMB = (uploadConfig.maxSize / (1024 * 1024)).toFixed(0);
            return {
                valid: false,
                message: `File size exceeds ${sizeMB}MB. Please upload a smaller image.`
            };
        }

        return { valid: true };
    }

    /**
     * Display image preview
     */
    function displayPreview(file, upload, input) {
        // Check if all required elements exist
        if (!upload.preview || !upload.label || !upload.previewContainer) {
            console.error('Missing required upload elements', upload);
            showValidationError('Upload configuration error. Please refresh the page.', input);
            return;
        }

        const reader = new FileReader();

        reader.onload = function(e) {
            try {
                upload.preview.src = e.target.result;
                upload.label.classList.add('d-none');
                upload.previewContainer.classList.remove('d-none');

                // Mark as valid
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');

                // Update upload area styling
                upload.area.classList.add('has-image');
            } catch (error) {
                console.error('Error displaying preview:', error);
                showValidationError('Failed to display preview. Please try again.', input);
                input.value = '';
            }
        };

        reader.onerror = function(error) {
            console.error('FileReader error:', error);
            showValidationError('Failed to read file. Please try again.', input);
            input.value = '';
        };

        reader.readAsDataURL(file);
    }

    /**
     * Remove uploaded image
     */
    function removeImage(upload) {
        upload.input.value = '';
        upload.input.classList.remove('is-valid', 'is-invalid');
        upload.preview.src = '';
        upload.label.classList.remove('d-none');
        upload.previewContainer.classList.add('d-none');
        upload.area.classList.remove('has-image');
    }

    /**
     * Show validation error
     */
    function showValidationError(message, input) {
        // Use SweetAlert if available
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Upload Error',
                text: message,
                confirmButtonColor: '#3085d6',
                timer: 3000
            });
        } else {
            alert(message);
        }

        // Mark input as invalid
        if (input) {
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
        }
    }

    /**
     * Get file info for display
     */
    function getFileInfo(file) {
        return {
            name: file.name,
            size: formatFileSize(file.size),
            type: file.type
        };
    }

    /**
     * Format file size
     */
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    /**
     * Check if all required images are uploaded
     */
    window.areAllImagesUploaded = function() {
        return uploadAreas.front.input.files.length > 0 &&
            uploadAreas.back.input.files.length > 0 &&
            uploadAreas.selfie.input.files.length > 0;
    };

    /**
     * Get upload status for debugging
     */
    window.getUploadStatus = function() {
        return {
            front: uploadAreas.front.input.files.length > 0,
            back: uploadAreas.back.input.files.length > 0,
            selfie: uploadAreas.selfie.input.files.length > 0
        };
    };

    /**
     * Clear all uploads (useful for reset)
     */
    window.clearAllUploads = function() {
        Object.values(uploadAreas).forEach(upload => {
            removeImage(upload);
        });
    };

    // Inject additional styles if not already present
    injectUploadStyles();

    // Log initialization
    console.log('Verification upload handler initialized');
});

/**
 * Inject additional CSS styles for upload areas
 */
function injectUploadStyles() {
    if (document.getElementById('upload-handler-styles')) {
        return; // Styles already injected
    }

    const styleElement = document.createElement('style');
    styleElement.id = 'upload-handler-styles';
    styleElement.textContent = `
        .upload-area-compact.has-image {
            border-color: #198754;
            background: #f0fff4;
        }

        .upload-area-compact .preview-container {
            position: relative;
            padding: 10px;
        }

        .upload-area-compact .preview-container img {
            max-width: 100%;
            max-height: 200px;
            object-fit: contain;
            border-radius: 8px;
        }

        .upload-area-compact .remove-image {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 10;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .upload-area-compact.drag-over {
            border-color: #0d6efd;
            background: #e7f3ff;
            transform: scale(1.02);
            transition: all 0.2s ease;
        }

        .upload-area-compact .badge {
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
        }

        /* File input validation states */
        .upload-area-compact:has(input.is-invalid) {
            border-color: #dc3545;
            background: #fff5f5;
        }

        .upload-area-compact:has(input.is-valid) {
            border-color: #198754;
            background: #f0fff4;
        }

        /* Preview overlay on hover */
        .preview-container:hover .remove-image {
            opacity: 1;
        }

        .remove-image {
            opacity: 0.8;
            transition: opacity 0.2s ease;
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .upload-area-compact .preview-container img {
                max-height: 150px;
            }
        }
    `;

    document.head.appendChild(styleElement);
}