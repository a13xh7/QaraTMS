// Tree Item Functions
function toggleChildren(documentId) {
    const container = document.getElementById(`children-${documentId}`);
    if (!container) return;
    
    const button = container.previousElementSibling.querySelector('.toggle-btn');
    if (!button) return;
    
    if (container.classList.contains('show')) {
        container.classList.remove('show');
        button.setAttribute('aria-expanded', 'false');
    } else {
        container.classList.add('show');
        button.setAttribute('aria-expanded', 'true');
    }
}

// Document Selection
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize tree items if they exist
    const treeItems = document.querySelectorAll('.tree-item-title');
    if (treeItems.length) {
        treeItems.forEach(item => {
            item.addEventListener('click', () => {
                document.querySelectorAll('.tree-item-title').forEach(el => el.classList.remove('selected'));
                item.classList.add('selected');
            });
        });
    }

    // Only initialize form validation if the form exists
    const documentForm = document.getElementById('documentForm');
    if (documentForm) {
        initializeFormValidation(documentForm);
    }
});

// Alert Functions
function showCustomAlert(message, documentId) {
    // Create overlay
    const overlay = document.createElement('div');
    overlay.className = 'alert-overlay';
    document.body.appendChild(overlay);

    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = 'custom-alert';
    
    // Create message
    const messageP = document.createElement('p');
    messageP.textContent = message;
    alertDiv.appendChild(messageP);

    // Create actions
    const actions = document.createElement('div');
    actions.className = 'alert-actions';
    
    // Edit button
    const editBtn = document.createElement('button');
    editBtn.className = 'btn-edit';
    editBtn.textContent = 'Edit Document';
    editBtn.onclick = function() {
        window.location.href = `/project/${projectId}/documents/${documentId}/edit`;
    };
    
    // Cancel button
    const cancelBtn = document.createElement('button');
    cancelBtn.className = 'btn-cancel';
    cancelBtn.textContent = 'Cancel';
    cancelBtn.onclick = function() {
        overlay.classList.remove('show');
        alertDiv.classList.remove('show');
        setTimeout(() => {
            overlay.remove();
            alertDiv.remove();
        }, 300);
    };
    
    actions.appendChild(editBtn);
    actions.appendChild(cancelBtn);
    alertDiv.appendChild(actions);
    
    document.body.appendChild(alertDiv);
    
    // Show with animation
    setTimeout(() => {
        overlay.classList.add('show');
        alertDiv.classList.add('show');
    }, 100);
}

function showDeleteAlert(documentId) {
    // Create overlay
    const overlay = document.createElement('div');
    overlay.className = 'alert-overlay';
    document.body.appendChild(overlay);

    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = 'custom-alert';
    
    // Create message
    const messageP = document.createElement('p');
    messageP.textContent = 'Are you sure you want to delete this document?';
    alertDiv.appendChild(messageP);

    // Create actions
    const actions = document.createElement('div');
    actions.className = 'alert-actions';
    
    // Delete button
    const deleteBtn = document.createElement('button');
    deleteBtn.className = 'btn-delete';
    deleteBtn.textContent = 'Delete';
    deleteBtn.onclick = function() {
        document.getElementById('deleteForm_' + documentId).submit();
    };
    
    // Cancel button
    const cancelBtn = document.createElement('button');
    cancelBtn.className = 'btn-cancel';
    cancelBtn.textContent = 'Cancel';
    cancelBtn.onclick = function() {
        overlay.classList.remove('show');
        alertDiv.classList.remove('show');
        setTimeout(() => {
            overlay.remove();
            alertDiv.remove();
        }, 300);
    };
    
    actions.appendChild(deleteBtn);
    actions.appendChild(cancelBtn);
    alertDiv.appendChild(actions);
    
    document.body.appendChild(alertDiv);
    
    // Show with animation
    setTimeout(() => {
        overlay.classList.add('show');
        alertDiv.classList.add('show');
    }, 100);
}

// TOC Functions
function toggleTOC() {
    const toc = document.querySelector('.toc-sidebar');
    toc.classList.toggle('show');
}

// Delete document confirmation flow
async function confirmDelete(documentId) {
    try {
        // Fix the URL to match the route in web.php
        const response = await fetch(`/check-children/${documentId}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.hasChildren) {
            // Show warning for documents with children
            showEditWarningAlert(
                "This document has sub-documents. Please move or delete the sub-documents first, or edit the document to reorganize its structure.",
                documentId
            );
        } else {
            // Show delete confirmation if no children
            showDeleteConfirmAlert(documentId);
        }
        
    } catch (error) {
        console.error('Error checking document children:', error);
        showErrorAlert('An error occurred while checking the document structure. Please try again.');
    }
}

// Alert for documents with children
function showEditWarningAlert(message, documentId) {
    // Create overlay
    const overlay = document.createElement('div');
    overlay.className = 'alert-overlay';
    document.body.appendChild(overlay);

    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = 'custom-alert';
    
    // Create message
    const messageP = document.createElement('p');
    messageP.textContent = message;
    alertDiv.appendChild(messageP);

    // Create actions
    const actions = document.createElement('div');
    actions.className = 'alert-actions';
    
    // Edit button
    const editBtn = document.createElement('button');
    editBtn.className = 'btn-edit';
    editBtn.textContent = 'Edit Document';
    editBtn.onclick = function() {
        // Get project_id from the current URL
        const currentPath = window.location.pathname;
        const matches = currentPath.match(/\/project\/(\d+)/);
        const projectId = matches ? matches[1] : null;
        
        if (projectId) {
            window.location.href = `/project/${projectId}/documents/${documentId}/edit`;
        } else {
            console.error('Could not determine project ID from URL');
            showErrorAlert('Error navigating to edit page. Please try refreshing the page.');
        }
    };
    
    // Cancel button
    const cancelBtn = document.createElement('button');
    cancelBtn.className = 'btn-cancel';
    cancelBtn.textContent = 'Cancel';
    cancelBtn.onclick = function() {
        closeAlert(overlay, alertDiv);
    };
    
    actions.appendChild(editBtn);
    actions.appendChild(cancelBtn);
    alertDiv.appendChild(actions);
    
    showAlert(overlay, alertDiv);
}

// Delete confirmation alert
function showDeleteConfirmAlert(documentId) {
    // Create overlay
    const overlay = document.createElement('div');
    overlay.className = 'alert-overlay';
    document.body.appendChild(overlay);

    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = 'custom-alert';
    
    // Create message
    const messageP = document.createElement('p');
    messageP.textContent = 'Are you sure you want to delete this document?';
    alertDiv.appendChild(messageP);

    // Create actions
    const actions = document.createElement('div');
    actions.className = 'alert-actions';
    
    // Delete button
    const deleteBtn = document.createElement('button');
    deleteBtn.className = 'btn-delete';
    deleteBtn.textContent = 'Delete';
    deleteBtn.onclick = function() {
        // Submit the delete form
        const form = document.getElementById(`deleteForm_${documentId}`);
        if (form) {
            form.submit();
        }
    };
    
    // Cancel button
    const cancelBtn = document.createElement('button');
    cancelBtn.className = 'btn-cancel';
    cancelBtn.textContent = 'Cancel';
    cancelBtn.onclick = function() {
        closeAlert(overlay, alertDiv);
    };
    
    actions.appendChild(deleteBtn);
    actions.appendChild(cancelBtn);
    alertDiv.appendChild(actions);
    
    showAlert(overlay, alertDiv);
}

// Error alert
function showErrorAlert(message) {
    // Create overlay
    const overlay = document.createElement('div');
    overlay.className = 'alert-overlay';
    document.body.appendChild(overlay);

    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = 'custom-alert';
    
    // Create message
    const messageP = document.createElement('p');
    messageP.textContent = message;
    alertDiv.appendChild(messageP);

    // Create actions
    const actions = document.createElement('div');
    actions.className = 'alert-actions';
    
    // OK button
    const okBtn = document.createElement('button');
    okBtn.className = 'btn-cancel';
    okBtn.textContent = 'OK';
    okBtn.onclick = function() {
        closeAlert(overlay, alertDiv);
    };
    
    actions.appendChild(okBtn);
    alertDiv.appendChild(actions);
    
    showAlert(overlay, alertDiv);
}

// Helper functions for alerts
function showAlert(overlay, alertDiv) {
    document.body.appendChild(alertDiv);
    
    // Show with animation
    setTimeout(() => {
        overlay.classList.add('show');
        alertDiv.classList.add('show');
    }, 100);
}

function closeAlert(overlay, alertDiv) {
    overlay.classList.remove('show');
    alertDiv.classList.remove('show');
    setTimeout(() => {
        overlay.remove();
        alertDiv.remove();
    }, 300);
}

// Add to existing docs.js
function initializeSummernote() {
    $(document).ready(function() {
        // Check if Summernote is available
        if (typeof $.fn.summernote === 'undefined') {
            console.error('Summernote is not loaded properly');
            return;
        }

        try {
            $('#content').summernote({
                height: 400,
                placeholder: 'Describe the content of your document here...',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'italic', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'codeview']]
                ]
            });
        } catch (e) {
            console.error('Error initializing Summernote:', e);
        }
    });
}

// Form validation and submission
function initializeFormValidation(form) {
    let isShowingAlert = false;
    let submitTimeout = null;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (submitTimeout) {
            return false;
        }

        const btn = document.getElementById('submit_btn');
        const title = form.querySelector('input[name="title"]');
        const content = form.querySelector('textarea[name="content"]');
        
        let errorMessages = [];
        let isValid = true;
        
        if (!title.value.trim()) {
            errorMessages.push('Please enter a document title');
            isValid = false;
        }

        if (!content.value.trim()) {
            errorMessages.push('Please enter document content');
            isValid = false;
        }

        if (!isValid) {
            showCustomAlert(errorMessages);
            
            submitTimeout = setTimeout(() => {
                submitTimeout = null;
            }, 3000);
            
            return false;
        }

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Creating...';
        }
        form.submit();
    });
}

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeSummernote();
    const documentForm = document.getElementById('documentForm');
    if (documentForm) {
        initializeFormValidation(documentForm);
    }
}); 

 // Store filter value in localStorage when typing
document.getElementById('documentFilter').addEventListener('input', function () {
    const filterValue = this.value.toLowerCase().trim();
    localStorage.setItem('documentFilter', filterValue);
    applyFilter(filterValue);
});

// Apply filter when page loads
document.addEventListener('DOMContentLoaded', function () {
    const savedFilter = localStorage.getItem('documentFilter') || '';
    const filterInput = document.getElementById('documentFilter');
    filterInput.value = savedFilter;
    applyFilter(savedFilter);
});

// Extract filter logic into separate function
function applyFilter(filterValue) {
    const treeItems = document.querySelectorAll('.document-tree-item');

    treeItems.forEach(treeItem => {
        const titles = treeItem.querySelectorAll('span.document-title');
        let shouldShow = false;

        titles.forEach(titleElement => {
            const title = titleElement.textContent.toLowerCase();
            if (title.includes(filterValue)) {
                shouldShow = true;
            }
        });

        treeItem.style.display = shouldShow ? '' : 'none';
    });
}