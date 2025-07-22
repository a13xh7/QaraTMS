function handleAddDocument() {
    // Set the category based on the current page
    const categoryMap = {
        'Compliance': 'compliance',
        'SOP & QA Docs': 'sop_qa',
        'Test Exceptions': 'test_exceptions',
        'Audit Readiness': 'audit_readiness',
        'Knowledge Transfers': 'knowledge_transfers'
    };
    
    const currentCategory = categoryMap[window.pageTitle] || '';
    if (currentCategory) {
        document.getElementById('document_category').value = currentCategory;
    }
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('addDocumentModal'));
    modal.show();
}

function handleAddRootDocument() {
    // Clear the form
    document.getElementById('addDocumentForm').reset();
    
    // Clear Quill editor if it exists
    if (typeof quill !== 'undefined') {
        quill.setContents([]);
    }
    
    // Set the category based on current page
    const categoryMap = {
        'Compliance': 'compliance',
        'SOP & QA Docs': 'sop_qa',
        'Test Exceptions': 'test_exceptions',
        'Audit Readiness': 'audit_readiness',
        'Knowledge Transfers': 'knowledge_transfers'
    };
    
    const currentCategory = categoryMap[window.pageTitle] || '';
    if (currentCategory) {
        document.getElementById('document_category').value = currentCategory;
    }
    
    // Force parent to be empty (root level)
    const parentSelect = document.getElementById('document_parent');
    if (parentSelect) {
        parentSelect.value = '';
        parentSelect.disabled = true; // Disable parent selection for root documents
    }
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('addDocumentModal'));
    modal.show();
    
    // Re-enable parent select when modal is hidden (for regular add document)
    const modalElement = document.getElementById('addDocumentModal');
    modalElement.addEventListener('hidden.bs.modal', function() {
        if (parentSelect) {
            parentSelect.disabled = false;
        }
    }, { once: true });
}

// Content Table Sidebar Functions
function toggleContentSidebar() {
    const sidebar = document.getElementById('contentTableSidebar');
    const mainContent = document.getElementById('mainContent');
    
    if (window.innerWidth <= 768) {
        // Mobile: slide in/out
        sidebar.classList.toggle('mobile-open');
    } else {
        // Desktop: show/hide with margin adjustment
        const isCurrentlyHidden = sidebar.style.display === 'none' || 
                                  getComputedStyle(sidebar).display === 'none';
        
        if (isCurrentlyHidden) {
            // Show sidebar
            sidebar.style.display = 'block';
            mainContent.classList.add('main-content-with-sidebar');
        } else {
            // Hide sidebar
            sidebar.style.display = 'none';
            mainContent.classList.remove('main-content-with-sidebar');
        }
    }
}

// Initialize sidebar visibility on page load
function initSidebarVisibility() {
    const sidebar = document.getElementById('contentTableSidebar');
    const mainContent = document.getElementById('mainContent');
    
    if (window.innerWidth > 768) {
        // Show sidebar by default on desktop
        sidebar.style.display = 'block';
        mainContent.classList.add('main-content-with-sidebar');
    } else {
        // Hide sidebar on mobile
        sidebar.style.display = 'none';
        mainContent.classList.remove('main-content-with-sidebar');
    }
}

function toggleChildren(event, toggle) {
    event.stopPropagation();
    const chevron = toggle.querySelector('i');
    const parentLi = toggle.closest('li');
    const children = parentLi.querySelector('.content-table-children');
    
    if (children) {
        if (children.classList.contains('expanded')) {
            children.classList.remove('expanded');
            chevron.className = 'bi bi-chevron-right';
        } else {
            children.classList.add('expanded');
            chevron.className = 'bi bi-chevron-down';
        }
    }
}

function selectDocument(documentId) {
    // Remove active class from all items
    document.querySelectorAll('.content-table-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Add active class to selected item
    const selectedItem = document.querySelector(`[data-id="${documentId}"] .content-table-item`);
    if (selectedItem) {
        selectedItem.classList.add('active');
    }
    
    // Scroll to document in main content or open modal
    viewDocument(documentId);
}

// Content table search functionality
function initContentTableSearch() {
    const searchInput = document.getElementById('contentTableSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const contentTable = document.getElementById('contentTable');
            const items = contentTable.querySelectorAll('li[data-id]');
            
            items.forEach(item => {
                const text = item.querySelector('.content-table-text').textContent.toLowerCase();
                const shouldShow = text.includes(searchTerm) || searchTerm === '';
                
                if (shouldShow) {
                    item.style.display = 'block';
                    // Also show parent items
                    let parent = item.parentElement.closest('li[data-id]');
                    while (parent) {
                        parent.style.display = 'block';
                        parent = parent.parentElement.closest('li[data-id]');
                    }
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
}

// Initialize drag and drop for content table
function initContentTableDragDrop() {
    // Load SortableJS if not already loaded
    if (typeof Sortable === 'undefined') {
        console.warn('SortableJS not loaded. Please include SortableJS library.');
        return;
    }

    function makeSortable(el) {
        new Sortable(el, {
            group: 'nested',
            animation: 150,
            fallbackOnBody: true,
            swapThreshold: 0.65,
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: function (evt) {
                // Gather the new structure
                const structure = getTreeStructure(document.getElementById('contentTable'));
                // Send to backend
                updateTreeStructure(structure);
            }
        });

        // Recursively make children sortable
        Array.from(el.children).forEach(li => {
            const childUl = li.querySelector('ul.content-table-children');
            if (childUl) {
                makeSortable(childUl);
            }
        });
    }

    // Helper to get the tree structure
    function getTreeStructure(ul) {
        return Array.from(ul.children).map((li, index) => {
            const id = li.getAttribute('data-id');
            const childrenUl = li.querySelector(':scope > ul.content-table-children');
            return {
                id: parseInt(id),
                position: index,
                children: childrenUl ? getTreeStructure(childrenUl) : []
            };
        });
    }

    // Send structure to backend
    function updateTreeStructure(structure) {
        fetch('/documents/update-tree', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({tree: structure})
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showNotification('Content order updated!', 'success');
            } else {
                showNotification('Failed to update order: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error updating tree:', error);
            showNotification('An error occurred while updating the content order', 'error');
        });
    }

    const contentTable = document.getElementById('contentTable');
    if (contentTable) {
        makeSortable(contentTable);
    }
}

// Handle form submission
document.addEventListener('DOMContentLoaded', function() {
    if (typeof quill !== 'undefined') {
        document.getElementById('addDocumentForm').addEventListener('submit', function() {
            var html = quill.root.innerHTML;
            document.getElementById('document_content').value = html;
            // Let the browser submit the form natively
        });
    }

    // Search functionality for document cards
    var searchInput = document.getElementById('documentSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.modern-document-card');
            cards.forEach(card => {
                // Title
                const title = card.querySelector('.modern-document-title')?.textContent.toLowerCase() || '';
                // Author
                const author = card.querySelector('.modern-document-meta .fw-bold')?.textContent.toLowerCase() || '';
                // Tags (if present)
                let tags = '';
                const tagElements = card.querySelectorAll('.modern-tag');
                tagElements.forEach(tagEl => {
                    tags += tagEl.textContent.toLowerCase() + ' ';
                });

                // Match if any field contains the search term
                if (
                    title.includes(searchTerm) ||
                    author.includes(searchTerm) ||
                    tags.includes(searchTerm)
                ) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    // Add some interactive effects
    const card = document.querySelector('.modern-empty-card');
    if (card) {
        card.style.opacity = '0';
        setTimeout(() => {
            card.style.transition = 'opacity 0.6s ease-out';
            card.style.opacity = '1';
        }, 100);
    }

    // Initialize content table features
    initSidebarVisibility();
    initContentTableSearch();
    initContentTableDragDrop();

    // Advanced Search Toggle
    const toggleBtn = document.getElementById('toggleAdvancedSearch');
    const advFilters = document.getElementById('advancedSearchFilters');
    if (toggleBtn && advFilters) {
        toggleBtn.addEventListener('click', function() {
            if (advFilters.style.display === 'none' || advFilters.style.display === '') {
                advFilters.style.display = 'flex';
            } else {
                advFilters.style.display = 'none';
            }
        });
    }
});

// Notification function
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// View document function
function viewDocument(documentId) {
    // Show loading state
    const viewButton = event.target ? event.target.closest('button') : null;
    let originalText = '';
    if (viewButton) {
        originalText = viewButton.innerHTML;
        viewButton.innerHTML = '<i class="bi bi-hourglass-split"></i> Loading...';
        viewButton.disabled = true;
    }
    
    fetch(`/documents/get-document/${documentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const docData = data.document;
                
                // Populate modal with document data
                document.getElementById('viewDocumentTitle').textContent = docData.title;
                document.getElementById('viewDocumentCategory').textContent = docData.category.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                document.getElementById('viewDocumentCreated').textContent = docData.created_at;
                document.getElementById('viewDocumentContent').innerHTML = autoLink(docData.content);
                
                // Handle tags
                const tagsContainer = document.getElementById('viewDocumentTags');
                const tagsList = document.getElementById('viewDocumentTagsList');
                if (docData.tags && docData.tags.length > 0) {
                    tagsList.innerHTML = docData.tags.map(tag => `<span class="badge bg-primary me-1">${tag}</span>`).join('');
                    tagsContainer.style.display = 'block';
                } else {
                    tagsContainer.style.display = 'none';
                }
                
                // Handle updated date
                const updatedContainer = document.getElementById('viewDocumentUpdated');
                const updatedDate = document.getElementById('viewDocumentUpdatedDate');
                if (docData.is_updated) {
                    updatedDate.textContent = docData.updated_at;
                    updatedContainer.style.display = 'inline';
                } else {
                    updatedContainer.style.display = 'none';
                }
                
                // Set up edit button
                document.getElementById('editDocumentBtn').onclick = function() {
                    editDocument(documentId);
                };
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('viewDocumentModal'));
                modal.show();
                
            } else {
                showNotification(data.message || 'Error loading document', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while loading the document', 'error');
        })
        .finally(() => {
            // Reset button state
            if (viewButton) {
                viewButton.innerHTML = originalText;
                viewButton.disabled = false;
            }
        });
}

// Edit document function
function editDocument(documentId) {
    // Show loading state if called from a button
    const editButton = event.target ? event.target.closest('button') : null;
    let originalText = '';
    if (editButton) {
        originalText = editButton.innerHTML;
        editButton.innerHTML = '<i class="bi bi-hourglass-split"></i> Loading...';
        editButton.disabled = true;
    }
    
    // Fetch document data
    fetch(`/documents/get-document/${documentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const docData = data.document;
                
                // Populate edit form with document data
                document.getElementById('edit_document_id').value = docData.id;
                document.getElementById('edit_document_title').value = docData.title;
                document.getElementById('edit_document_category').value = docData.category;
                document.getElementById('edit_state').value = docData.state;
                
                // Set hidden state for non-admin users
                const hiddenStateInput = document.getElementById('edit_state_hidden');
                if (hiddenStateInput) {
                    hiddenStateInput.value = docData.state;
                }
                
                // Handle parent selection
                const parentSelect = document.getElementById('edit_document_parent');
                if (parentSelect) {
                    parentSelect.value = docData.parent_id || '';
                    
                    // Disable options that would create circular references
                    const currentDocId = docData.id;
                    const options = parentSelect.querySelectorAll('option');
                    options.forEach(option => {
                        option.disabled = false; // Reset all options first
                        if (option.value == currentDocId) {
                            option.disabled = true; // Disable self
                        }
                    });
                    
                    // TODO: Also disable descendant options - would need descendant info from server
                }
                
                // Handle tags
                if (docData.tags && docData.tags.length > 0) {
                    document.getElementById('edit_document_tags').value = docData.tags.join(', ');
                } else {
                    document.getElementById('edit_document_tags').value = '';
                }
                
                // Set Quill content
                if (typeof editQuill !== 'undefined') {
                    editQuill.root.innerHTML = docData.content;
                }
                
                // Handle reviewers - first clear all checkboxes
                const reviewerCheckboxes = document.querySelectorAll('#edit_reviewers input[name="reviewers[]"]');
                reviewerCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                
                // Then check the ones that are reviewers
                if (docData.reviewers && docData.reviewers.length > 0) {
                    docData.reviewers.forEach(reviewerId => {
                        const checkbox = document.getElementById(`edit_reviewer_${reviewerId}`);
                        if (checkbox) {
                            checkbox.checked = true;
                        }
                    });
                }
                
                // Set form action
                document.getElementById('editDocumentForm').action = `/documents/update-document/${docData.id}`;
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('editDocumentModal'));
                modal.show();
                
            } else {
                showNotification(data.message || 'Error loading document for editing', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while loading the document for editing', 'error');
        })
        .finally(() => {
            // Reset button state
            if (editButton) {
                editButton.innerHTML = originalText;
                editButton.disabled = false;
            }
        });
}

// Delete document function
function deleteDocument(documentId) {
    if (confirm('Are you sure you want to delete this document? This action cannot be undone.')) {
        // Show loading state
        const deleteButton = event.target.closest('button');
        const originalText = deleteButton.innerHTML;
        deleteButton.innerHTML = '<i class="bi bi-hourglass-split"></i> Deleting...';
        deleteButton.disabled = true;
        
        fetch(`/documents/delete-document/${documentId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Document deleted successfully!', 'success');
                
                // Remove the document card from the DOM
                const documentCard = document.querySelector(`[data-document-id="${documentId}"]`);
                if (documentCard) {
                    documentCard.remove();
                    
                    // Check if there are any documents left
                    const remainingCards = document.querySelectorAll('.modern-document-card');
                    if (remainingCards.length === 0) {
                        // Reload page to show empty state
                        window.location.reload();
                    }
                }
            } else {
                showNotification(data.message || 'Error deleting document', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while deleting the document', 'error');
        })
        .finally(() => {
            // Reset button state
            deleteButton.innerHTML = originalText;
            deleteButton.disabled = false;
        });
    }
}

// Toggle full content view
function toggleFullContent(button) {
    const documentCard = button.closest('.modern-document-card');
    const excerpt = documentCard.querySelector('.modern-document-excerpt');
    const fullContent = documentCard.querySelector('.modern-document-full-content');
    
    if (fullContent.style.display === 'none') {
        excerpt.style.display = 'none';
        fullContent.style.display = 'block';
        button.innerHTML = '<i class="bi bi-chevron-up"></i> Show less';
    } else {
        excerpt.style.display = 'block';
        fullContent.style.display = 'none';
        button.innerHTML = '<i class="bi bi-chevron-down"></i> Show more';
    }
}

function filterDocuments() {
    const searchTerm = document.getElementById('documentSearch').value.toLowerCase();
    const state = document.getElementById('filterState').value;
    const tagsInput = document.getElementById('filterTags').value.toLowerCase();
    const dateStart = document.getElementById('filterDateStart').value;
    const dateEnd = document.getElementById('filterDateEnd').value;
    const userId = document.getElementById('filterUser').value;
    const updated = document.getElementById('filterUpdated').value;

    const cards = document.querySelectorAll('.modern-document-card');
    cards.forEach(card => {
        // Title
        const title = card.querySelector('.modern-document-title')?.textContent.toLowerCase() || '';
        // Author
        const author = card.querySelector('.modern-document-meta .fw-bold')?.textContent.toLowerCase() || '';
        // State
        const stateText = card.querySelector('.modern-document-meta .badge')?.textContent.toLowerCase() || '';
        // Tags
        let tags = '';
        const tagElements = card.querySelectorAll('.modern-tag');
        tagElements.forEach(tagEl => {
            tags += tagEl.textContent.toLowerCase() + ' ';
        });
        // User (author id, if available as data attribute)
        const cardUserId = card.getAttribute('data-author-id') || '';
        // Date (created)
        const meta = card.querySelector('.modern-document-meta')?.textContent || '';
        const createdMatch = meta.match(/Created:\\s*([A-Za-z]{3} \\d{2}, \\d{4})/);
        let createdDate = '';
        if (createdMatch) {
            createdDate = new Date(createdMatch[1]);
        }
        // Last updated filter (not implemented in this snippet, but you can add logic)

        // Filter logic
        let matches = true;

        // Search term (title, author, tags)
        if (searchTerm && !(title.includes(searchTerm) || author.includes(searchTerm) || tags.includes(searchTerm))) {
            matches = false;
        }
        // State
        if (state && !stateText.includes(state)) {
            matches = false;
        }
        // Tags (comma separated, all must match)
        if (tagsInput) {
            const tagList = tagsInput.split(',').map(t => t.trim()).filter(Boolean);
            for (let tag of tagList) {
                if (!tags.includes(tag)) {
                    matches = false;
                    break;
                }
            }
        }
        // Date period
        if (dateStart && createdDate && createdDate < new Date(dateStart)) {
            matches = false;
        }
        if (dateEnd && createdDate && createdDate > new Date(dateEnd)) {
            matches = false;
        }
        // User
        if (userId && cardUserId !== userId) {
            matches = false;
        }

        card.style.display = matches ? '' : 'none';
    });
}

// Attach only to the Search button
document.getElementById('applyFiltersBtn').addEventListener('click', filterDocuments);

function autoLink(text) {
    // Regex to match URLs
    const urlPattern = /((https?:\/\/)[^\s<]+)/g;
    return text.replace(urlPattern, function(url) {
        return `<a href="${url}" target="_blank" rel="noopener noreferrer" class="long-link">${url}</a>`;
    });
} 