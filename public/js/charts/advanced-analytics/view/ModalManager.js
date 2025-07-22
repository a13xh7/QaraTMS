/**
 * ModalManager.js
 * Replaces blocking alerts/confirms with Bootstrap modals for better UX and accessibility
 */

/**
 * ModalManager - Handles all modal interactions in the application
 * Replaces native alert(), confirm() and prompt() with Bootstrap modals
 */
class ModalManager {
    /**
     * Create a new ModalManager
     */
    constructor() {
        this.activeModals = [];
        this.modalCounter = 0;
        this.modalContainer = null;
        
        // Initialize the container where modals will be inserted
        this._initializeContainer();
    }
    
    /**
     * Initialize modal container
     * @private
     */
    _initializeContainer() {
        // Create container if it doesn't exist
        if (!document.getElementById('aa-modal-container')) {
            this.modalContainer = document.createElement('div');
            this.modalContainer.id = 'aa-modal-container';
            document.body.appendChild(this.modalContainer);
        } else {
            this.modalContainer = document.getElementById('aa-modal-container');
        }
    }
    
    /**
     * Show an alert modal (replacement for alert())
     * @param {string} message - Message to display
     * @param {Object} [options={}] - Additional options
     * @param {string} [options.title='Alert'] - Modal title
     * @param {string} [options.buttonText='OK'] - Text for the OK button
     * @param {Function} [options.onClose=null] - Callback when modal is closed
     * @returns {Promise<void>} Resolves when modal is closed
     */
    alert(message, options = {}) {
        const title = options.title || 'Alert';
        const buttonText = options.buttonText || 'OK';
        
        return new Promise((resolve) => {
            const modalId = `aa-alert-modal-${this.modalCounter++}`;
            
            const modalHTML = `
                <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog" aria-labelledby="${modalId}-title" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="${modalId}-title">${this._escapeHTML(title)}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                ${this._escapeHTML(message)}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">${this._escapeHTML(buttonText)}</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            this.modalContainer.insertAdjacentHTML('beforeend', modalHTML);
            
            const modalElement = document.getElementById(modalId);
            
            // Set up event handlers
            modalElement.addEventListener('hidden.bs.modal', () => {
                // Remove modal from DOM after it's hidden
                modalElement.remove();
                
                // Call onClose callback if provided
                if (options.onClose && typeof options.onClose === 'function') {
                    options.onClose();
                }
                
                resolve();
            });
            
            // Show the modal
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
            
            // Add to active modals
            this.activeModals.push({ id: modalId, instance: modal });
        });
    }
    
    /**
     * Show a confirmation modal (replacement for confirm())
     * @param {string} message - Message to display
     * @param {Object} [options={}] - Additional options
     * @param {string} [options.title='Confirm'] - Modal title
     * @param {string} [options.confirmText='OK'] - Text for confirm button
     * @param {string} [options.cancelText='Cancel'] - Text for cancel button
     * @param {string} [options.confirmButtonClass='btn-primary'] - Class for confirm button
     * @param {Function} [options.onConfirm=null] - Callback when confirmed
     * @param {Function} [options.onCancel=null] - Callback when canceled
     * @returns {Promise<boolean>} Resolves to true if confirmed, false if canceled
     */
    confirm(message, options = {}) {
        const title = options.title || 'Confirm';
        const confirmText = options.confirmText || 'OK';
        const cancelText = options.cancelText || 'Cancel';
        const confirmButtonClass = options.confirmButtonClass || 'btn-primary';
        
        return new Promise((resolve) => {
            const modalId = `aa-confirm-modal-${this.modalCounter++}`;
            
            const modalHTML = `
                <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog" aria-labelledby="${modalId}-title" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="${modalId}-title">${this._escapeHTML(title)}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                ${this._escapeHTML(message)}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary cancel-btn" data-bs-dismiss="modal">${this._escapeHTML(cancelText)}</button>
                                <button type="button" class="btn ${confirmButtonClass} confirm-btn">${this._escapeHTML(confirmText)}</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            this.modalContainer.insertAdjacentHTML('beforeend', modalHTML);
            
            const modalElement = document.getElementById(modalId);
            const confirmBtn = modalElement.querySelector('.confirm-btn');
            
            // Set up event handlers
            modalElement.addEventListener('hidden.bs.modal', () => {
                // Remove modal from DOM after it's hidden
                modalElement.remove();
            });
            
            confirmBtn.addEventListener('click', () => {
                // Call onConfirm callback if provided
                if (options.onConfirm && typeof options.onConfirm === 'function') {
                    options.onConfirm();
                }
                
                // Close the modal
                const modalInstance = bootstrap.Modal.getInstance(modalElement);
                if (modalInstance) {
                    modalInstance.hide();
                }
                
                resolve(true);
            });
            
            // Handle cancel
            modalElement.querySelector('.cancel-btn').addEventListener('click', () => {
                // Call onCancel callback if provided
                if (options.onCancel && typeof options.onCancel === 'function') {
                    options.onCancel();
                }
                
                resolve(false);
            });
            
            // Also handle the X button and backdrop clicks
            modalElement.addEventListener('hidden.bs.modal', (event) => {
                if (!event.currentTarget.classList.contains('confirmed')) {
                    // Call onCancel callback if provided
                    if (options.onCancel && typeof options.onCancel === 'function') {
                        options.onCancel();
                    }
                    
                    resolve(false);
                }
            });
            
            // Show the modal
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
            
            // Add to active modals
            this.activeModals.push({ id: modalId, instance: modal });
        });
    }
    
    /**
     * Show a prompt modal (replacement for prompt())
     * @param {string} message - Message to display
     * @param {string} [defaultValue=''] - Default value for the input
     * @param {Object} [options={}] - Additional options
     * @param {string} [options.title='Prompt'] - Modal title
     * @param {string} [options.confirmText='OK'] - Text for confirm button
     * @param {string} [options.cancelText='Cancel'] - Text for cancel button
     * @param {string} [options.inputType='text'] - Type of input field
     * @param {Function} [options.validator=null] - Input validation function
     * @returns {Promise<string|null>} Resolves to input value or null if canceled
     */
    prompt(message, defaultValue = '', options = {}) {
        const title = options.title || 'Prompt';
        const confirmText = options.confirmText || 'OK';
        const cancelText = options.cancelText || 'Cancel';
        const inputType = options.inputType || 'text';
        
        return new Promise((resolve) => {
            const modalId = `aa-prompt-modal-${this.modalCounter++}`;
            
            const modalHTML = `
                <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog" aria-labelledby="${modalId}-title" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="${modalId}-title">${this._escapeHTML(title)}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>${this._escapeHTML(message)}</p>
                                <div class="form-group">
                                    <input type="${inputType}" class="form-control prompt-input" value="${this._escapeHTML(defaultValue)}">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary cancel-btn" data-bs-dismiss="modal">${this._escapeHTML(cancelText)}</button>
                                <button type="button" class="btn btn-primary confirm-btn">${this._escapeHTML(confirmText)}</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            this.modalContainer.insertAdjacentHTML('beforeend', modalHTML);
            
            const modalElement = document.getElementById(modalId);
            const confirmBtn = modalElement.querySelector('.confirm-btn');
            const inputField = modalElement.querySelector('.prompt-input');
            const invalidFeedback = modalElement.querySelector('.invalid-feedback');
            
            // Focus the input field when modal is shown
            modalElement.addEventListener('shown.bs.modal', () => {
                inputField.focus();
                inputField.select();
            });
            
            // Handle Enter key in input field
            inputField.addEventListener('keyup', (event) => {
                if (event.key === 'Enter') {
                    confirmBtn.click();
                }
            });
            
            // Set up event handlers
            modalElement.addEventListener('hidden.bs.modal', () => {
                // Remove modal from DOM after it's hidden
                modalElement.remove();
            });
            
            // Submit handler
            const handleSubmit = () => {
                const value = inputField.value;
                
                // Validate input if validator provided
                if (options.validator && typeof options.validator === 'function') {
                    const validationResult = options.validator(value);
                    if (validationResult !== true) {
                        inputField.classList.add('is-invalid');
                        invalidFeedback.textContent = validationResult || 'Invalid input';
                        return;
                    }
                }
                
                // Close the modal
                const modalInstance = bootstrap.Modal.getInstance(modalElement);
                if (modalInstance) {
                    modalInstance.hide();
                }
                
                resolve(value);
            };
            
            confirmBtn.addEventListener('click', handleSubmit);
            
            // Handle cancel
            modalElement.querySelector('.cancel-btn').addEventListener('click', () => {
                resolve(null);
            });
            
            // Also handle the X button and backdrop clicks
            modalElement.addEventListener('hidden.bs.modal', (event) => {
                if (!event.currentTarget.classList.contains('confirmed')) {
                    resolve(null);
                }
            });
            
            // Show the modal
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
            
            // Add to active modals
            this.activeModals.push({ id: modalId, instance: modal });
        });
    }
    
    /**
     * Close all active modals
     */
    closeAll() {
        this.activeModals.forEach(modal => {
            if (modal.instance) {
                modal.instance.hide();
            }
        });
        
        this.activeModals = [];
    }
    
    /**
     * Create a custom modal with specified content
     * @param {Object} options - Modal options
     * @param {string} [options.title=''] - Modal title
     * @param {string|HTMLElement} options.content - Modal content
     * @param {string} [options.size=''] - Modal size ('sm', 'lg', 'xl')
     * @param {boolean} [options.backdrop=true] - Whether modal has a backdrop
     * @param {boolean} [options.keyboard=true] - Whether modal can be closed with keyboard
     * @param {Array<Object>} [options.buttons=[]] - Array of button configs {text, class, handler}
     * @returns {Object} Modal controller with show/hide methods
     */
    custom(options) {
        const modalId = `aa-custom-modal-${this.modalCounter++}`;
        const title = options.title || '';
        const size = options.size ? `modal-${options.size}` : '';
        const backdrop = options.backdrop !== false;
        const keyboard = options.keyboard !== false;
        
        // Generate footer with buttons
        let footerContent = '';
        if (options.buttons && options.buttons.length > 0) {
            footerContent = options.buttons.map((btn, index) => {
                return `<button type="button" class="btn ${btn.class || 'btn-secondary'}" data-btn-index="${index}">${this._escapeHTML(btn.text)}</button>`;
            }).join('');
        }
        
        const modalHTML = `
            <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog" aria-labelledby="${modalId}-title" aria-hidden="true">
                <div class="modal-dialog ${size}" role="document">
                    <div class="modal-content">
                        ${title ? `
                            <div class="modal-header">
                                <h5 class="modal-title" id="${modalId}-title">${this._escapeHTML(title)}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                        ` : ''}
                        <div class="modal-body">
                            ${typeof options.content === 'string' ? options.content : ''}
                        </div>
                        ${footerContent ? `<div class="modal-footer">${footerContent}</div>` : ''}
                    </div>
                </div>
            </div>
        `;
        
        this.modalContainer.insertAdjacentHTML('beforeend', modalHTML);
        
        const modalElement = document.getElementById(modalId);
        
        // If content is an HTMLElement, append it
        if (options.content instanceof HTMLElement) {
            modalElement.querySelector('.modal-body').appendChild(options.content);
        }
        
        // Set up button handlers
        if (options.buttons && options.buttons.length > 0) {
            const footer = modalElement.querySelector('.modal-footer');
            if (footer) {
                footer.addEventListener('click', (event) => {
                    const buttonEl = event.target.closest('[data-btn-index]');
                    if (buttonEl) {
                        const index = parseInt(buttonEl.dataset.btnIndex, 10);
                        const buttonConfig = options.buttons[index];
                        
                        if (buttonConfig && buttonConfig.handler) {
                            buttonConfig.handler(modalController);
                        }
                    }
                });
            }
        }
        
        // Create the Bootstrap modal
        const bsModal = new bootstrap.Modal(modalElement, {
            backdrop: backdrop,
            keyboard: keyboard,
            focus: true
        });
        
        // Set up event handlers
        modalElement.addEventListener('hidden.bs.modal', () => {
            // Remove this modal from active modals
            this.activeModals = this.activeModals.filter(m => m.id !== modalId);
            
            // Remove modal from DOM after it's hidden
            modalElement.remove();
        });
        
        // Create controller object
        const modalController = {
            id: modalId,
            element: modalElement,
            instance: bsModal,
            show: () => bsModal.show(),
            hide: () => bsModal.hide(),
            toggle: () => bsModal.toggle(),
            update: (newContent) => {
                const body = modalElement.querySelector('.modal-body');
                if (body) {
                    if (typeof newContent === 'string') {
                        body.innerHTML = newContent;
                    } else if (newContent instanceof HTMLElement) {
                        body.innerHTML = '';
                        body.appendChild(newContent);
                    }
                }
            }
        };
        
        // Add to active modals
        this.activeModals.push({ 
            id: modalId, 
            instance: bsModal,
            controller: modalController
        });
        
        return modalController;
    }
    
    /**
     * Escape HTML to prevent XSS
     * @private
     * @param {string} unsafe - Unsafe string
     * @returns {string} Safe HTML string
     */
    _escapeHTML(unsafe) {
        if (typeof unsafe !== 'string') {
            return '';
        }
        
        return unsafe
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
}

// Export a singleton instance
const modalManager = new ModalManager();
export default modalManager; 