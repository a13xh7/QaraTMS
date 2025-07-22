/**************************************************
 * LOAD SCRIPTS
 *************************************************/

$.getScript("/js/repo/tree.js", function () {
    $.getScript("/js/repo/suites_tree_and_crud.js", function () {
        $.getScript("/js/repo/case_crud.js", function () {
            /**************************************************
             * RENDER SUITES TREE
             * and select first available suite
             * when all scripts are loaded
             *************************************************/

            $.getScript("/js/repo/case_editor.js", function () {
                try {
                    loadSuitesTree();
                } catch (e) {
                    setTimeout(function () {
                        loadSuitesTree();
                    }, 1000);
                }
            });
        });
    });
});

/**************************************************
 * Click on test suite - load suite test cases
 *************************************************/

function loadCasesList(id) {
    activeTreeSuiteItem.setId(id);

    Cookies.set('lastSelectedSuite', id);

    // Add selected class
    $('#tree .branch-wrapper').removeClass("selected");
    activeTreeSuiteItem.addSelectedClass();

    $('#test_cases_list_site_title').text(activeTreeSuiteItem.getTitle()); // set title in test cases list area
    $('#test_cases_list').load(`/tscl/${activeTreeSuiteItem.getId()}`, function () {
    }); // load test cases
}

/**************************************************
 * Collapse / expand test cases list
 **************************************************/
function expandCasesList() {
    $('#test_cases_list_col').addClass('col-9').removeClass('col')
}

function collapseCasesList() {
    $('#test_cases_list_col').addClass('col').removeClass('col-9')
}

/**************************************************
 *  BLOCK ANY BUTTON AFTER CLICK
 *  to prevent ajax errors, double input
 **************************************************/

$("body").on('click', 'button', function () {
    let button = $(this).prop('disabled', true);
    setTimeout(function () {
        button.prop('disabled', false);
    }, 250);
});

/**************************************************
 * Collapse / expand children
 **************************************************/

$('body').on("click", "#toogle_collaple_expand", function (e) {
    let suite_id = $(this).parent().parent().parent().parent().data('mid');

    rec(suite_id)
});

function rec(suite_id) {
    let child_li = $(`li[data-pid='${suite_id}']`);

    if (child_li.is(":visible")) {
        child_li.hide();
    } else {
        child_li.show();
    }

    if ($(`li[data-pid='${child_li.attr('data-mid')}']`).length > 0) {
        rec(child_li.attr('data-mid'))
    }
}

/**************************************************
 * Repository Form Handling
 *************************************************/
document.addEventListener('DOMContentLoaded', function() {
    initializeRepositoryForm('createRepositoryForm');
    initializeRepositoryForm('editRepositoryForm');
});

function initializeRepositoryForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return;

    const prefixInput = document.getElementById('prefix');
    const prefixDisplay = document.getElementById('prefixDisplay');
    
    if (prefixInput && prefixDisplay) {
        // Update prefix preview
        prefixInput.addEventListener('input', function() {
            const prefix = this.value.toUpperCase();
            prefixDisplay.textContent = prefix || 'PRE';
        });
    }
    
    // Handle template selection (only for create form)
    if (formId === 'createRepositoryForm') {
        const structureTemplate = document.getElementById('structureTemplate');
        const structureEmpty = document.getElementById('structureEmpty');
        const templateOptions = document.querySelector('.template-options');
        const templateSelect = document.getElementById('templateSelect');
        
        if (structureTemplate && structureEmpty && templateOptions && templateSelect) {
            structureTemplate.addEventListener('change', function() {
                if (this.checked) {
                    templateOptions.classList.remove('d-none');
                    templateSelect.disabled = false;
                }
            });
            
            structureEmpty.addEventListener('change', function() {
                if (this.checked) {
                    templateOptions.classList.add('d-none');
                    templateSelect.disabled = true;
                }
            });
        }
    }
    
    // Form validation
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
                errorDiv.textContent = 'Repository name is required';
                titleInput.parentNode.appendChild(errorDiv);
            }
        } else {
            titleInput.classList.remove('is-invalid');
        }
        
        // Validate prefix
        if (!prefixInput.value.trim()) {
            prefixInput.classList.add('is-invalid');
            isValid = false;
            
            // Create error message if it doesn't exist
            if (!document.getElementById('prefix-error')) {
                const errorDiv = document.createElement('div');
                errorDiv.id = 'prefix-error';
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = 'Prefix is required';
                prefixInput.parentNode.appendChild(errorDiv);
            }
        } else if (prefixInput.value.includes(' ')) {
            prefixInput.classList.add('is-invalid');
            isValid = false;
            
            // Create error message if it doesn't exist
            if (!document.getElementById('prefix-error')) {
                const errorDiv = document.createElement('div');
                errorDiv.id = 'prefix-error';
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = 'Prefix cannot contain spaces';
                prefixInput.parentNode.appendChild(errorDiv);
            }
        } else {
            prefixInput.classList.remove('is-invalid');
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

/**************************************************
 * Repository List Page Functionality
 *************************************************/
function initializeRepositoryList() {
    // Search functionality
    const searchInput = document.getElementById('repositorySearch');
    const clearSearchBtn = document.getElementById('clearSearch');
    const resetSearchBtn = document.getElementById('resetSearch');
    const repositoryGrid = document.getElementById('repositoryGrid');
    const noSearchResults = document.getElementById('noSearchResults');
    const repositoryItems = document.querySelectorAll('.repository-item');
    
    if (searchInput && repositoryItems.length) {
        function performSearch() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            let visibleCount = 0;
            
            repositoryItems.forEach(item => {
                const title = item.querySelector('.repository-title').textContent.toLowerCase();
                const description = item.querySelector('.repository-description').textContent.toLowerCase();
                
                if (title.includes(searchTerm) || description.includes(searchTerm)) {
                    item.classList.remove('d-none');
                    visibleCount++;
                } else {
                    item.classList.add('d-none');
                }
            });
            
            if (visibleCount === 0 && searchTerm !== '') {
                repositoryGrid.classList.add('d-none');
                noSearchResults.classList.remove('d-none');
            } else {
                repositoryGrid.classList.remove('d-none');
                noSearchResults.classList.add('d-none');
            }
        }
        
        searchInput.addEventListener('input', performSearch);
        
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
    }
    
    // Delete modal functionality
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const repositoryId = button.getAttribute('data-repository-id');
            const repositoryTitle = button.getAttribute('data-repository-title');
            
            document.getElementById('deleteRepositoryId').value = repositoryId;
            document.getElementById('deleteRepositoryName').textContent = repositoryTitle;
        });
    }

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
}

/**************************************************
 * Repository Show Page Functionality
 *************************************************/
function initializeShowPage() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    
    // View toggle functionality
    initializeViewToggle();
    
    // Search functionality
    initializeTestCaseSearch();
    initializeSuiteSearch();
    
    // Tree functionality
    initializeTreeControls();
    
    // Test case filtering
    initializeTestCaseFilters();
    
    // Import functionality
    initializeImportModal();
    
    // Permission-based button removal
    handlePermissionBasedButtons();
    
    // Initialize sortable test cases
    initializeSortableTestCases();
}

function initializeViewToggle() {
    const viewToggleBtn = document.getElementById('viewToggleBtn');
    const testCaseCol = document.getElementById('test_case_col');
    
    if (viewToggleBtn && testCaseCol) {
        viewToggleBtn.addEventListener('click', function() {
            if (testCaseCol.classList.contains('d-none')) {
                testCaseCol.classList.remove('d-none');
                viewToggleBtn.innerHTML = '<i class="bi bi-layout-three-columns"></i>';
                viewToggleBtn.title = 'Three Column View';
            } else {
                testCaseCol.classList.add('d-none');
                viewToggleBtn.innerHTML = '<i class="bi bi-layout-split"></i>';
                viewToggleBtn.title = 'Two Column View';
            }
        });
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.repository-header')) {
        initializeShowPage();
    }
});

// Utility function for notifications
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 150);
    }, 5000);
}

// Initialize functionality based on page
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('repositoryGrid')) {
        initializeRepositoryList();
    }
});

/**************************************************
 * Repository Edit Page Functionality
 *************************************************/
function initializeRepositoryEdit() {
    // Form validation
    const form = document.getElementById('editRepositoryForm');
    const prefixInput = document.getElementById('prefix');
    const prefixDisplay = document.getElementById('prefixDisplay');
    
    if (form && prefixInput && prefixDisplay) {
        // Update prefix preview
        prefixInput.addEventListener('input', function() {
            const prefix = this.value.toUpperCase();
            prefixDisplay.textContent = prefix || 'PRE';
        });
        
        // Form validation
        form.addEventListener('submit', function(event) {
            let isValid = true;
            
            // Validate title
            const titleInput = document.getElementById('title');
            if (!titleInput.value.trim()) {
                titleInput.classList.add('is-invalid');
                isValid = false;
                
                if (!document.getElementById('title-error')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.id = 'title-error';
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = 'Repository name is required';
                    titleInput.parentNode.appendChild(errorDiv);
                }
            } else {
                titleInput.classList.remove('is-invalid');
            }
            
            // Validate prefix
            if (!prefixInput.value.trim()) {
                prefixInput.classList.add('is-invalid');
                isValid = false;
                
                if (!document.getElementById('prefix-error')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.id = 'prefix-error';
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = 'Prefix is required';
                    prefixInput.parentNode.appendChild(errorDiv);
                }
            } else if (prefixInput.value.includes(' ')) {
                prefixInput.classList.add('is-invalid');
                isValid = false;
                
                if (!document.getElementById('prefix-error')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.id = 'prefix-error';
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = 'Prefix cannot contain spaces';
                    prefixInput.parentNode.appendChild(errorDiv);
                }
            } else {
                prefixInput.classList.remove('is-invalid');
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
}

// Initialize functionality based on page
document.addEventListener('DOMContentLoaded', function() {
    // ... existing initialization code ...
    
    if (document.getElementById('editRepositoryForm')) {
        initializeRepositoryEdit();
    }
});

/**************************************************
 * Repository Create Page Functionality
 *************************************************/
function initializeRepositoryCreate() {
    // Form validation
    const form = document.getElementById('createRepositoryForm');
    const prefixInput = document.getElementById('prefix');
    const prefixDisplay = document.getElementById('prefixDisplay');
    
    if (form && prefixInput && prefixDisplay) {
        // Update prefix preview
        prefixInput.addEventListener('input', function() {
            const prefix = this.value.toUpperCase();
            prefixDisplay.textContent = prefix || 'PRE';
        });
        
        // Handle template selection
        const structureTemplate = document.getElementById('structureTemplate');
        const structureEmpty = document.getElementById('structureEmpty');
        const templateOptions = document.querySelector('.template-options');
        const templateSelect = document.getElementById('templateSelect');
        
        if (structureTemplate && structureEmpty && templateOptions && templateSelect) {
            structureTemplate.addEventListener('change', function() {
                if (this.checked) {
                    templateOptions.classList.remove('d-none');
                    templateSelect.disabled = false;
                }
            });
            
            structureEmpty.addEventListener('change', function() {
                if (this.checked) {
                    templateOptions.classList.add('d-none');
                    templateSelect.disabled = true;
                }
            });
        }
        
        // Form validation
        form.addEventListener('submit', function(event) {
            let isValid = true;
            
            // Validate title
            const titleInput = document.getElementById('title');
            if (!titleInput.value.trim()) {
                titleInput.classList.add('is-invalid');
                isValid = false;
                
                if (!document.getElementById('title-error')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.id = 'title-error';
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = 'Repository name is required';
                    titleInput.parentNode.appendChild(errorDiv);
                }
            } else {
                titleInput.classList.remove('is-invalid');
            }
            
            // Validate prefix
            if (!prefixInput.value.trim()) {
                prefixInput.classList.add('is-invalid');
                isValid = false;
                
                if (!document.getElementById('prefix-error')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.id = 'prefix-error';
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = 'Prefix is required';
                    prefixInput.parentNode.appendChild(errorDiv);
                }
            } else if (prefixInput.value.includes(' ')) {
                prefixInput.classList.add('is-invalid');
                isValid = false;
                
                if (!document.getElementById('prefix-error')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.id = 'prefix-error';
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = 'Prefix cannot contain spaces';
                    prefixInput.parentNode.appendChild(errorDiv);
                }
            } else {
                prefixInput.classList.remove('is-invalid');
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
}

// Initialize functionality based on page
document.addEventListener('DOMContentLoaded', function() {
    // ... existing initialization code ...
    
    if (document.getElementById('createRepositoryForm')) {
        initializeRepositoryCreate();
    }
});

