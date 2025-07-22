/**
 * ModalManager.js
 * Utility for creating and managing Bootstrap modals to replace alerts and confirms
 */

import logger from './logger.js';
import { sanitizeHTML } from './utils.js';

/**
 * Modal Manager class for creating and managing non-blocking modals
 */
class ModalManager {
    constructor() {
        // Track active modals
        this.activeModals = new Map();
        this.modalCounter = 0;
    }
    
    /**
     * Show an alert modal (replacement for window.alert)
     * @param {string} message - Message to display
     * @param {Object} options - Modal options
     * @param {string} [options.title='Notice'] - Modal title
     * @param {string} [options.buttonText='OK'] - Button text
     * @param {string} [options.buttonClass='btn-primary'] - Button class
     * @param {Function} [options.onClose] - Callback when modal is closed
     * @returns {string} Modal ID
     */
    alert(message, options = {}) {
        const modalId = this._generateModalId('alert');
        const {
            title = 'Notice',
            buttonText = 'OK',
            buttonClass = 'btn-primary',
            onClose = null
        } = options;
        
        // Create modal HTML
        const modalHTML = `
            <div class="modal fade" id="${modalId}" tabindex="-1" aria-labelledby="${modalId}-title" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="${modalId}-title">${sanitizeHTML(title)}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            ${sanitizeHTML(message)}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn ${buttonClass}" data-bs-dismiss="modal">${sanitizeHTML(buttonText)}</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Add to DOM and display
        this._createAndShowModal(modalId, modalHTML, onClose);
        return modalId;
    }
    
    /**
     * Show a confirmation modal (replacement for window.confirm)
     * @param {string} message - Message to display
     * @param {Object} options - Modal options
     * @param {string} [options.title='Confirm'] - Modal title
     * @param {string} [options.confirmButtonText='Confirm'] - Confirm button text
     * @param {string} [options.cancelButtonText='Cancel'] - Cancel button text
     * @param {string} [options.confirmButtonClass='btn-primary'] - Confirm button class
     * @param {string} [options.cancelButtonClass='btn-secondary'] - Cancel button class
     * @param {Function} [options.onConfirm] - Callback when confirmed
     * @param {Function} [options.onCancel] - Callback when canceled
     * @returns {string} Modal ID
     */
    confirm(message, options = {}) {
        const modalId = this._generateModalId('confirm');
        const {
            title = 'Confirm',
            confirmButtonText = 'Confirm',
            cancelButtonText = 'Cancel',
            confirmButtonClass = 'btn-primary',
            cancelButtonClass = 'btn-secondary',
            onConfirm = null,
            onCancel = null
        } = options;
        
        // Create modal HTML
        const modalHTML = `
            <div class="modal fade" id="${modalId}" tabindex="-1" aria-labelledby="${modalId}-title" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="${modalId}-title">${sanitizeHTML(title)}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            ${sanitizeHTML(message)}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn ${cancelButtonClass} cancel-btn" data-bs-dismiss="modal">${sanitizeHTML(cancelButtonText)}</button>
                            <button type="button" class="btn ${confirmButtonClass} confirm-btn">${sanitizeHTML(confirmButtonText)}</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Add to DOM and setup handlers
        const modalElement = this._createModalElement(modalId, modalHTML);
        document.body.appendChild(modalElement);
        
        // Get Bootstrap modal instance
        const modalInstance = new bootstrap.Modal(modalElement);
        
        // Store in active modals
        this.activeModals.set(modalId, {
            element: modalElement,
            instance: modalInstance
        });
        
        // Add event listeners for buttons
        const confirmBtn = modalElement.querySelector('.confirm-btn');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', () => {
                if (onConfirm) onConfirm();
                modalInstance.hide();
            });
        }
        
        // Handle hidden event (cleanup)
        modalElement.addEventListener('hidden.bs.modal', () => {
            this._cleanupModal(modalId);
        });
        
        // Handle cancel event
        modalElement.addEventListener('hide.bs.modal', (e) => {
            // If hide was triggered by the cancel button or close button
            if (e.target.querySelector('.confirm-btn:hover') !== document.activeElement) {
                if (onCancel) onCancel();
            }
        });
        
        // Show the modal
        modalInstance.show();
        
        return modalId;
    }
    
    /**
     * Show a toast notification
     * @param {string} message - Message to display
     * @param {Object} options - Toast options
     * @param {string} [options.title='Notification'] - Toast title
     * @param {string} [options.variant='primary'] - Toast variant (primary, success, danger, warning, info)
     * @param {number} [options.autohide=true] - Whether to automatically hide
     * @param {number} [options.delay=5000] - Delay before hiding (ms)
     * @returns {string} Toast ID
     */
    toast(message, options = {}) {
        const toastId = this._generateModalId('toast');
        const {
            title = 'Notification',
            variant = 'primary',
            autohide = true,
            delay = 5000
        } = options;
        
        // Define toast container if it doesn't exist
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            toastContainer.style.zIndex = '5000';
            document.body.appendChild(toastContainer);
        }
        
        // Create toast HTML
        const toastHTML = `
            <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true" ${autohide ? `data-bs-delay="${delay}"` : ''}>
                <div class="toast-header bg-${variant} bg-opacity-10">
                    <strong class="me-auto text-${variant}">${sanitizeHTML(title)}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    ${sanitizeHTML(message)}
                </div>
            </div>
        `;
        
        // Add to DOM
        toastContainer.insertAdjacentHTML('beforeend', toastHTML);
        
        // Get toast element and create Bootstrap instance
        const toastElement = document.getElementById(toastId);
        const toastInstance = new bootstrap.Toast(toastElement, {
            autohide: autohide,
            delay: delay
        });
        
        // Store in active modals
        this.activeModals.set(toastId, {
            element: toastElement,
            instance: toastInstance
        });
        
        // Handle hidden event (cleanup)
        toastElement.addEventListener('hidden.bs.toast', () => {
            this._cleanupModal(toastId);
        });
        
        // Show the toast
        toastInstance.show();
        
        return toastId;
    }
    
    /**
     * Close a modal by ID
     * @param {string} modalId - ID of the modal to close
     */
    close(modalId) {
        if (this.activeModals.has(modalId)) {
            const { instance } = this.activeModals.get(modalId);
            if (instance) {
                instance.hide();
            }
        }
    }
    
    /**
     * Helper to generate unique modal IDs
     * @private
     * @param {string} prefix - Prefix for the ID
     * @returns {string} Unique ID
     */
    _generateModalId(prefix) {
        this.modalCounter++;
        return `${prefix}-modal-${Date.now()}-${this.modalCounter}`;
    }
    
    /**
     * Helper to create and display a modal
     * @private
     * @param {string} modalId - Modal ID
     * @param {string} modalHTML - Modal HTML
     * @param {Function} [onClose] - Callback when modal is closed
     * @returns {HTMLElement} Modal element
     */
    _createAndShowModal(modalId, modalHTML, onClose) {
        const modalElement = this._createModalElement(modalId, modalHTML);
        document.body.appendChild(modalElement);
        
        // Get Bootstrap modal instance
        const modalInstance = new bootstrap.Modal(modalElement);
        
        // Store in active modals
        this.activeModals.set(modalId, {
            element: modalElement,
            instance: modalInstance
        });
        
        // Handle hidden event (cleanup)
        modalElement.addEventListener('hidden.bs.modal', () => {
            this._cleanupModal(modalId);
            if (onClose) onClose();
        });
        
        // Show the modal
        modalInstance.show();
        
        return modalElement;
    }
    
    /**
     * Helper to create a modal element from HTML
     * @private
     * @param {string} modalId - Modal ID
     * @param {string} modalHTML - Modal HTML
     * @returns {HTMLElement} Modal element
     */
    _createModalElement(modalId, modalHTML) {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = modalHTML.trim();
        return tempDiv.firstChild;
    }
    
    /**
     * Helper to cleanup a modal when closed
     * @private
     * @param {string} modalId - Modal ID
     */
    _cleanupModal(modalId) {
        if (this.activeModals.has(modalId)) {
            const { element, instance } = this.activeModals.get(modalId);
            
            // Dispose Bootstrap instance if available
            if (instance && typeof instance.dispose === 'function') {
                try {
                    instance.dispose();
                } catch (error) {
                    logger.warn('Error disposing modal', error);
                }
            }
            
            // Remove from DOM
            if (element && element.parentNode) {
                element.parentNode.removeChild(element);
            }
            
            // Remove from tracked modals
            this.activeModals.delete(modalId);
        }
    }
}

// Export singleton instance
export default new ModalManager(); 