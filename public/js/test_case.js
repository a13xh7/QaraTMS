// Initialize tooltips
const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

document.addEventListener('DOMContentLoaded', function() {
    // Initialize label input event listener
    const labelInput = document.getElementById('tce_label_input');
    if (labelInput) {
        labelInput.addEventListener('blur', function() {
            const labelContainer = document.getElementById('tce_labels');
            if (labelContainer && labelContainer.children.length === 0) {
                addDefaultLabel(labelContainer);
            }
        });
    }

    // Initialize autocomplete inputs
    document.querySelectorAll('.autocomplete').forEach(function(input) {
        input.setAttribute('autocomplete', 'one-time-code');
    });

    // Initialize checkbox handlers
    if (typeof selectedOrder === 'undefined') {
        var selectedOrder = [];
    }

    document.querySelectorAll('#testCaseSelectForm input[type=checkbox]').forEach(cb => {
        cb.addEventListener('change', function() {
            console.log('Checkbox change event triggered for ID:', this.value, 'Checked:', this.checked);
            if (this.checked) {
                if (!selectedOrder.includes(this.value)) {
                    selectedOrder.push(this.value);
                    console.log('Added to selectedOrder:', this.value, 'Current selectedOrder:', selectedOrder);
                }
            } else {
                selectedOrder = selectedOrder.filter(id => id !== this.value);
                console.log('Removed from selectedOrder:', this.value, 'Current selectedOrder:', selectedOrder);
            }
        });
    });
});

function addDefaultLabel(container) {
    const defaultLabel = document.createElement('span');
    defaultLabel.className = 'badge badge-none me-1';
    defaultLabel.id = 'default_label';
    defaultLabel.textContent = 'None';
    container.appendChild(defaultLabel);
}

function showRemoveButtons() {
    const labelContainer = document.getElementById('tce_labels');
    const badges = document.querySelectorAll('#tce_labels .badge');
    badges.forEach(badge => {
        const removeBtn = badge.querySelector('.btn-close');
        if (removeBtn) {
            removeBtn.style.display = 'inline'; // Show the button
        } else {
            // Create and append a remove button if it doesn't exist
            const removeBtn = document.createElement('button');
            removeBtn.className = 'btn-close btn-sm ms-2';
            removeBtn.onclick = function() {
                badge.parentNode.removeChild(badge);

                // If no labels remain, re-add the default "None" label
                if (labelContainer.children.length === 0) {
                    addDefaultLabel(labelContainer);
                }
            };
            badge.appendChild(removeBtn);
            badge.appendChild(removeBtn);
        }
    });
}

function focusInput() {
    const input = document.getElementById('tce_label_input');
    const labelContainer = document.getElementById('tce_labels');

    // Remove the default label if it exists
    const defaultLabel = document.getElementById('default_label');
    if (defaultLabel) {
        labelContainer.removeChild(defaultLabel);
        input.value = '';
        input.placeholder = 'Enter a label...';
    }

    input.style.display = 'inline-block';
    input.focus();

    // Show remove buttons for all badges
    showRemoveButtons();

    // Show suggestions
    showSuggestions();
}

function addLabel(event) {
    if (event.key === 'Enter') {
        event.preventDefault(); // Prevent form submission
        const input = document.getElementById('tce_label_input');
        const labelContainer = document.getElementById('tce_labels');

        const labelText = input.value.trim();
        if (labelText) {
            // Remove the default "None" label if it exists
            const defaultLabel = document.getElementById('default_label');
            if (defaultLabel) {
                labelContainer.removeChild(defaultLabel);
            }

            // Create a new badge for the label
            const label = document.createElement('span');
            label.className = 'badge me-1';
            label.textContent = labelText;
            labelContainer.appendChild(label);
            input.value = '';
            input.placeholder = '';
            input.focus();

            // Show remove buttons for all badges
            showRemoveButtons();
        }
    }
}

function showSuggestions() {
    const suggestionBox = document.getElementById('tce_label_suggestion');
    const labelInput = document.getElementById('tce_label_input');
    const input = document.getElementById('label_input_container');

    fetch(`/test-case/labels`)
        .then(response => response.json())
        .then(allLabels => {
            // Filter labels based on input
            const suggestions = allLabels.filter(label => 
                label.toLowerCase().includes(labelInput.value.toLowerCase())
            );

            suggestionBox.innerHTML = '';
            suggestions.forEach(suggestion => {
                const suggestionItem = document.createElement('div');
                suggestionItem.textContent = suggestion;
                suggestionItem.onclick = function() {
                    addLabelFromSuggestion(suggestion);
                    suggestionBox.style.display = 'none';
                };
                suggestionBox.appendChild(suggestionItem);
            });

            if (suggestions.length > 0) {
                const inputRect = input.getBoundingClientRect();

                suggestionBox.style.display = 'block';
                suggestionBox.style.position = 'fixed';
                suggestionBox.style.top = `${inputRect.bottom + window.scrollY}px`;
                suggestionBox.style.left = `${inputRect.left}px`;
                suggestionBox.style.width = `${inputRect.width}px`;
            } else {
                suggestionBox.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error fetching labels:', error);
        });
}

function addLabelFromSuggestion(labelText) {
    const labelContainer = document.getElementById('tce_labels');
    const input = document.getElementById('tce_label_input');

    const defaultLabel = document.getElementById('default_label');
    if (defaultLabel) {
        labelContainer.removeChild(defaultLabel);
    }

    // Add suggestion list as a new badge for the label
    const label = document.createElement('span');
    label.className = 'badge me-1';
    label.textContent = labelText;
    labelContainer.appendChild(label);
    input.value = '';
    input.placeholder = '';
    input.focus();

    // Show remove buttons for all badges
    showRemoveButtons();
}

document.addEventListener('click', function(event) {
    const inputContainer = document.getElementById('label_input_container');
    const labelContainer = document.getElementById('tce_labels');
    const input = document.getElementById('tce_label_input');
    const suggestionBox = document.getElementById('tce_label_suggestion');

    if (inputContainer && !inputContainer.contains(event.target)) {
        const badges = document.querySelectorAll('#tce_labels .badge');
        badges.forEach(badge => {
            const removeBtn = badge.querySelector('.btn-close');
            if (removeBtn) {
                removeBtn.style.display = 'none'; // Hide the button
            }
        });

        if (labelContainer.children.length === 0) {
            addDefaultLabel(labelContainer);
        }

        input.style.display = 'none';
        suggestionBox.style.display = 'none'; // Hide suggestion box
    }
});

document.getElementById('tce_label_input')?.addEventListener('blur', function() {
    const labelContainer = document.getElementById('tce_labels');
    if (labelContainer.children.length === 0) {
        addDefaultLabel(labelContainer);
    }
});

// Initialize with default label
window.onload = function() {
    // Only initialize if we're in the edit form
    if (document.getElementById('test_case_editor')) {
        const labelContainer = document.getElementById('tce_labels');
        addDefaultLabel(labelContainer);
    }
};

// Function to handle precondition type change
function handlePreconditionTypeChange(type) {
    const textareaWrap = document.getElementById('precond_textarea_wrap');
    const selectCasesWrap = document.getElementById('precond_select_cases_wrap');
    const selectedTestCasesDiv = document.getElementById('selected_test_cases');

    if (type === 'free_text') {
        textareaWrap.style.display = 'block';
        selectCasesWrap.style.display = 'none';
        if (selectedTestCasesDiv) {
            selectedTestCasesDiv.style.display = 'none';
        }
    } else {
        textareaWrap.style.display = 'none';
        selectCasesWrap.style.display = 'block';
        if (selectedTestCasesDiv && selectedTestCasesDiv.innerHTML.trim() !== '') {
            selectedTestCasesDiv.style.display = 'block';
        }
    }
}

function openTestCasePopup() {
    const modal = document.getElementById('testCaseModal');
    modal.style.display = 'block';
    modal.classList.add('show');
    document.body.classList.add('modal-open');

    // Hide the error message when the modal opens
    const errorDiv = document.getElementById('testCaseSelectionError');
    if (errorDiv) {
        errorDiv.style.display = 'none';
    }

    // Reset selectedOrder array
    selectedOrder = [];

    // Get currently selected test cases from the display
    const selectedTestCasesDiv = document.getElementById('selected_test_cases');
    const selectedLinks = selectedTestCasesDiv.querySelectorAll('a');

    // Clear any existing selections
    document.querySelectorAll('#testCaseSelectForm input[type=checkbox]').forEach(cb => {
        cb.checked = false;
    });

    // Pre-select the checkboxes for existing preconditions and add to selectedOrder
    selectedLinks.forEach(link => {
        const testCaseId = link.getAttribute('href').split('/').pop();
        console.log('Processing test case ID:', testCaseId); // Debug log
        const checkbox = document.querySelector(`#testCaseSelectForm input[value="${testCaseId}"]`);
        if (checkbox) {
            checkbox.checked = true;
            selectedOrder.push(testCaseId);
        }
    });

    console.log('Initial selectedOrder:', selectedOrder); // Debug log

    // Re-attach event listeners to all checkboxes in the form whenever the modal is opened
    document.querySelectorAll('#testCaseSelectForm input[type=checkbox]').forEach(cb => {
        // Remove any existing listeners to prevent duplicates
        cb.removeEventListener('change', handleCheckboxChange);
        // Add the event listener
        cb.addEventListener('change', handleCheckboxChange);
    });
}

// Define the handler function separately so it can be removed and re-added
function handleCheckboxChange() {
    console.log('Checkbox change event triggered for ID:', this.value, 'Checked:', this.checked);
    if (this.checked) {
        if (!selectedOrder.includes(this.value)) {
            selectedOrder.push(this.value);
            console.log('Added to selectedOrder:', this.value, 'Current selectedOrder:', selectedOrder);
        }
    } else {
        selectedOrder = selectedOrder.filter(id => id !== this.value);
        console.log('Removed from selectedOrder:', this.value, 'Current selectedOrder:', selectedOrder);
    }
}

function closeTestCasePopup() {
    const modal = document.getElementById('testCaseModal');
    modal.style.display = 'none';
    modal.classList.remove('show');
    document.body.classList.remove('modal-open');
}

function submitTestCaseSelection() {
    const selected = [];
    // Use selectedOrder array to maintain the order of selection
    selectedOrder.forEach(id => {
        // Find the checkbox element for this ID
        const checkbox = document.querySelector(`#testCaseSelectForm input[value="${id}"]`);
        // If the checkbox exists (it should if the ID is in selectedOrder)
        if (checkbox) {
            selected.push({
                id: checkbox.value,
                title: checkbox.getAttribute('data-title'),
                prefix: checkbox.getAttribute('data-prefix')
            });
        }
    });

    console.log('Selected test cases for submission validation:', selected);

    const errorDiv = document.getElementById('testCaseSelectionError');

    const hasSelected = selected.length > 0;

    if (hasSelected) {
        // Hide error message if previously shown
        errorDiv.style.display = 'none';
    } else {
        // Show error message and prevent closing
        errorDiv.style.display = 'block';
        return;
    }

    // Update the display immediately
    const precondSelectCasesWrap = document.getElementById('precond_select_cases_wrap');
    const selectedTestCasesDiv = document.getElementById('selected_test_cases');

    // Generate HTML for the selected test cases list
    const selectedCasesHtml = hasSelected ?
        '<ol id="selected_precond">' + selected.map(tc =>
            `<li><a href="/test-case/${tc.id}" target="_blank"><b>${tc.prefix}-${tc.id}</b>: ${tc.title}</a></li>`
        ).join('') + '</ol>' : '';

    if (selectedTestCasesDiv) {
        selectedTestCasesDiv.innerHTML = selectedCasesHtml;
        selectedTestCasesDiv.style.display = hasSelected ? 'block' : 'none';
    }

    if (precondSelectCasesWrap) {
        precondSelectCasesWrap.style.display = hasSelected ? 'block' : 'none';
    }

    // Update the preconditions textarea with the selected test cases
    const preconditionsInput = document.getElementById('tce_preconditions_input');
    if (preconditionsInput) {
        preconditionsInput.value = selected.map(tc =>
            `${tc.prefix}-${tc.id}: ${tc.title}`
        ).join('\n');
    }

    // Store the selected test cases in a hidden input for the main form
    const mainForm = document.querySelector('form');
    if (mainForm) {
        let hiddenInput = mainForm.querySelector('input[name="selected_test_cases"]');
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'selected_test_cases';
            mainForm.appendChild(hiddenInput);
        }
        hiddenInput.value = JSON.stringify(selected);
    }

    closeTestCasePopup();
}

function filterTestCases() {
    const input = document.getElementById('testCaseSearch').value.toLowerCase();
    document.querySelectorAll('.test-case-item').forEach(function(item) {
        item.style.display = item.innerText.toLowerCase().includes(input) ? '' : 'none';
    });
}
