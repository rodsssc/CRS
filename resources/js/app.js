import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();


// ============================================================================
// GLOBAL HELPERS
// ============================================================================

/**
 * Get CSRF token from meta tag
 */