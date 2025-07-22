/**
 * advanced-analytics-modal-integration.js
 * Integrates the new modular ModalManager with the legacy codebase
 */

// Import the ModalManager from the modular structure
import modalManager from './advanced-analytics/view/ModalManager.js';
import { sanitizeHTML } from './advanced-analytics/common/sanitize.js';

// Store original alert and confirm functions
const originalAlert = window.alert;
const originalConfirm = window.confirm;

// Create a legacy wrapper that makes the modal manager available globally
const AdvancedAnalyticsModal = {
    // Reference to the modal manager
    _manager: modalManager,
    
    /**
     * Shows an alert dialog
     * @param {string} message - Message to display
     * @param {Object} [options={}] - Additional options
     * @returns {Promise<void>} Resolves when dialog is closed
     */
    alert: function(message, options = {}) {
        return modalManager.alert(message, options);
    },
    
    /**
     * Shows a confirmation dialog
     * @param {string} message - Message to display
     * @param {Object} [options={}] - Additional options
     * @returns {Promise<boolean>} Resolves to true if confirmed, false otherwise
     */
    confirm: function(message, options = {}) {
        return modalManager.confirm(message, options);
    },
    
    /**
     * Creates a toast notification
     * @param {string} message - Message to display
     * @param {string} [type='info'] - Notification type: 'success', 'error', 'warning', 'info'
     * @param {Object} [options={}] - Additional options
     */
    notify: function(message, type = 'info', options = {}) {
        const title = options.title || {
            'success': 'Success!',
            'error': 'Error',
            'warning': 'Warning',
            'info': 'Information'
        }[type] || 'Notice';
        
        const bgClass = {
            'success': 'bg-success',
            'error': 'bg-danger',
            'warning': 'bg-warning',
            'info': 'bg-info'
        }[type] || 'bg-info';
        
        const toastId = `analytics-toast-${Date.now()}`;
        const toastHTML = `
            <div id="${toastId}" class="toast align-items-center ${bgClass} text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ${sanitizeHTML(message)}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        // Find or create toast container
        let toastContainer = document.getElementById('analytics-toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'analytics-toast-container';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '1090'; // Above modals
            document.body.appendChild(toastContainer);
        }
        
        // Add toast to container
        toastContainer.insertAdjacentHTML('beforeend', toastHTML);
        
        // Initialize and show toast
        const toastElement = document.getElementById(toastId);
        const toastInstance = new bootstrap.Toast(toastElement, {
            autohide: options.autohide !== false,
            delay: options.delay || 5000
        });
        
        toastInstance.show();
        
        // Auto-remove element when hidden
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    },
    
    /**
     * Shows a success message
     * @param {string} message - Success message
     * @param {Object} [options={}] - Additional options
     */
    showSuccess: function(message, options = {}) {
        this.notify(message, 'success', options);
    },
    
    /**
     * Shows an error message
     * @param {string} message - Error message
     * @param {Object} [options={}] - Additional options
     */
    showError: function(message, options = {}) {
        this.notify(message, 'error', options);
    }
};

// Export the module
window.AdvancedAnalyticsModal = AdvancedAnalyticsModal;

// Optionally patch the global alert and confirm functions when in the analytics context
if (window.location.pathname.includes('analytics') || 
    document.getElementById('advanced-analytics-container')) {
    
    // Replace global alert with modal when appropriate
    window.alert = function(message) {
        // Only intercept in advanced analytics context
        if (document.getElementById('advanced-analytics-container') || 
            document.getElementById('advancedAnalysisModal')) {
            return AdvancedAnalyticsModal.alert(message);
        }
        // Fall back to original alert
        return originalAlert.call(window, message);
    };
    
    // Replace global confirm with modal when appropriate
    window.confirm = function(message) {
        // Only intercept in advanced analytics context
        if (document.getElementById('advanced-analytics-container') || 
            document.getElementById('advancedAnalysisModal')) {
            return AdvancedAnalyticsModal.confirm(message);
        }
        // Fall back to original confirm
        return originalConfirm.call(window, message);
    };
}

export default AdvancedAnalyticsModal; 