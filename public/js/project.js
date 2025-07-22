document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    } else {
        console.error('Bootstrap JavaScript is not loaded. Tooltips will not work.');
        
        // Fallback to title attribute for basic browser tooltips
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
            if (el.getAttribute('data-bs-original-title')) {
                el.setAttribute('title', el.getAttribute('data-bs-original-title'));
            }
        });
    }
    
    // Repository search functionality
    const repositorySearch = document.getElementById('repositorySearch');
    if (repositorySearch) {
        const repositoryCards = document.querySelectorAll('.repository-card');
        
        repositorySearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            
            repositoryCards.forEach(card => {
                const title = card.querySelector('.card-title').textContent.toLowerCase();
                const description = card.querySelector('.card-text') ? 
                    card.querySelector('.card-text').textContent.toLowerCase() : '';
                
                if (title.includes(searchTerm) || description.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
    
    // Search functionality
    const searchInput = document.getElementById('projectSearch');
    const clearSearchBtn = document.getElementById('clearSearch');
    const resetSearchBtn = document.getElementById('resetSearch');
    const projectGrid = document.getElementById('projectGrid');
    const noSearchResults = document.getElementById('noSearchResults');
    const projectItems = document.querySelectorAll('.project-item');
    
    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;
        
        projectItems.forEach(item => {
            const title = item.querySelector('.project-title').textContent.toLowerCase();
            const description = item.querySelector('.project-description').textContent.toLowerCase();
            
            if (title.includes(searchTerm) || description.includes(searchTerm)) {
                item.classList.remove('d-none');
                visibleCount++;
            } else {
                item.classList.add('d-none');
            }
        });
        
        if (visibleCount === 0 && searchTerm !== '') {
            projectGrid.classList.add('d-none');
            noSearchResults.classList.remove('d-none');
        } else {
            projectGrid.classList.remove('d-none');
            noSearchResults.classList.add('d-none');
        }
    }
    
    if (searchInput) {
        searchInput.addEventListener('input', performSearch);
    }
    
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            performSearch();
        });
    }
    
    if (resetSearchBtn) {
        resetSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            performSearch();
        });
    }
    
    // Delete modal functionality
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const projectId = button.getAttribute('data-project-id');
            const projectTitle = button.getAttribute('data-project-title');
            
            document.getElementById('deleteProjectId').value = projectId;
            document.getElementById('deleteProjectName').textContent = projectTitle;
        });
    }

    // Form validation
    const form = document.getElementById('createProjectForm') || document.getElementById('editProjectForm');
    if (form) {
        form.addEventListener('submit', function(event) {
            let isValid = true;
            
            // Validate title
            const titleInput = document.getElementById('title');
            if (!titleInput.value.trim()) {
                titleInput.classList.add('is-invalid');
                isValid = false;
                
                // Create error message if it doesn't exist
                if (!document.getElementById('title-error')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.id = 'title-error';
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = 'Project name is required';
                    titleInput.parentNode.appendChild(errorDiv);
                }
            } else {
                titleInput.classList.remove('is-invalid');
            }
            
            if (!isValid) {
                event.preventDefault();
            }
        });
        
        // Clear validation errors when typing
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('is-invalid');
            });
        });
    }

    // Description character counter
    const descriptionTextarea = document.getElementById('description');
    if (descriptionTextarea) {
        const maxLength = descriptionTextarea.getAttribute('maxlength');
        
        // Create counter element if it doesn't exist
        if (!document.getElementById('description-counter')) {
            const counterDiv = document.createElement('div');
            counterDiv.id = 'description-counter';
            counterDiv.className = 'text-muted small text-end mt-1';
            descriptionTextarea.parentNode.appendChild(counterDiv);
        }
        
        function updateCounter() {
            const counter = document.getElementById('description-counter');
            const remaining = maxLength - descriptionTextarea.value.length;
            counter.textContent = `${remaining} characters remaining`;
        }
        
        descriptionTextarea.addEventListener('input', updateCounter);
        
        // Initialize counter
        updateCounter();
    }

    // Project template selection
    const templateRadios = document.querySelectorAll('input[name="project_template"]');
    const createDefaultRepoCheckbox = document.getElementById('create_default_repository');
    
    if (templateRadios.length && createDefaultRepoCheckbox) {
        templateRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value !== 'empty') {
                    createDefaultRepoCheckbox.checked = true;
                    createDefaultRepoCheckbox.disabled = true;
                } else {
                    createDefaultRepoCheckbox.disabled = false;
                }
            });
        });
    }
});
