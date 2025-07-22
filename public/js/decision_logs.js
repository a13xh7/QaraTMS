console.log('Decision Logs script loaded!');

function editDecisionLog(id) {
    fetch(`/documents/decision-logs/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('editDecisionLogForm').action = `/documents/decision-logs/${id}`;
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_title').value = data.title;
            document.getElementById('edit_context').value = data.context;
            document.getElementById('edit_decision').value = data.decision;
            document.getElementById('edit_impact_risk').value = data.impact_risk;
            document.getElementById('edit_status').value = data.status;
            document.getElementById('edit_decision_type').value = data.decision_type;
            document.getElementById('edit_decision_owner').value = data.decision_owner;
            document.getElementById('edit_involved_qa').value = data.involved_qa;
            document.getElementById('edit_decision_date').value = data.decision_date;
            document.getElementById('edit_sprint_release').value = data.sprint_release;
            document.getElementById('edit_tags').value = data.tags ? data.tags.join(', ') : '';
            new bootstrap.Modal(document.getElementById('editDecisionLogModal')).show();
        });
}

function deleteDecisionLog(id) {
    if (confirm('Are you sure you want to delete this decision log?')) {
        fetch(`/documents/decision-logs/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        }).then(() => {
            window.location.reload();
        });
    }
}

function exportAllDecisions() {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('export', 'pdf');
    window.open(currentUrl.toString(), '_blank');
}

function toggleExpand(elementId) {
    const element = document.getElementById(elementId);
    const toggle = document.getElementById('toggle-' + elementId);
    if (element.classList.contains('expanded')) {
        element.classList.remove('expanded');
        toggle.textContent = 'Show more';
    } else {
        element.classList.add('expanded');
        toggle.textContent = 'Show less';
    }
}

function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.log-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.querySelector('.selected-count');
    if (checkboxes.length > 0) {
        bulkActions.classList.add('active');
        selectedCount.textContent = `${checkboxes.length} item${checkboxes.length > 1 ? 's' : ''} selected`;
    } else {
        bulkActions.classList.remove('active');
    }
}

function exportSelected() {
    const selectedIds = Array.from(document.querySelectorAll('.log-checkbox:checked')).map(cb => cb.value);
    if (selectedIds.length === 0) {
        alert('Please select at least one decision log to export.');
        return;
    }
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/documents/decision-logs/export-bulk';
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const idsInput = document.createElement('input');
    idsInput.type = 'hidden';
    idsInput.name = 'ids';
    idsInput.value = JSON.stringify(selectedIds);
    form.appendChild(csrfToken);
    form.appendChild(idsInput);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function deleteSelected() {
    const selectedIds = Array.from(document.querySelectorAll('.log-checkbox:checked')).map(cb => cb.value);
    if (selectedIds.length === 0) {
        alert('Please select at least one decision log to delete.');
        return;
    }
    if (confirm(`Are you sure you want to delete ${selectedIds.length} decision log${selectedIds.length > 1 ? 's' : ''}?`)) {
        fetch('/documents/decision-logs/bulk-delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ ids: selectedIds })
        }).then(() => {
            window.location.reload();
        });
    }
}

function clearSelection() {
    document.querySelectorAll('.log-checkbox').forEach(cb => cb.checked = false);
    updateBulkActions();
}

function filterByTag(tag) {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('search', tag);
    window.location.href = currentUrl.toString();
}

function selectAll() {
    const checkboxes = document.querySelectorAll('.log-checkbox');
    const selectAllCheckbox = document.getElementById('selectAll');
    checkboxes.forEach(cb => {
        cb.checked = selectAllCheckbox.checked;
    });
    updateBulkActions();
}

document.addEventListener('DOMContentLoaded', function() {
    // Add select all checkbox to the bulk actions
    const bulkActions = document.getElementById('bulkActions');
    if (bulkActions) {
        const selectAllDiv = document.createElement('div');
        selectAllDiv.innerHTML = `
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="selectAll" onchange="selectAll()">
                <label class="form-check-label" for="selectAll">
                    Select All
                </label>
            </div>
        `;
        bulkActions.insertBefore(selectAllDiv, bulkActions.firstChild);
    }
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    // Load saved view mode
    const savedViewMode = localStorage.getItem('decisionLogsViewMode') || 'full';
    setViewMode(savedViewMode);
    // Attach event listeners
    document.getElementById('fullViewBtn').addEventListener('click', function(e) {
        e.preventDefault();
        setViewMode('full');
    });
    document.getElementById('compactViewBtn').addEventListener('click', function(e) {
        e.preventDefault();
        setViewMode('compact');
    });
    // Thumbnail preview functionality
    const thumbnails = document.querySelectorAll('.artifact-thumbnail');
    thumbnails.forEach(thumbnail => {
        const imageSrc = thumbnail.getAttribute('data-src');
        thumbnail.style.setProperty('--thumbnail-src', `url('${imageSrc}')`);
    });
});

function setViewMode(mode) {
    const cards = document.querySelectorAll('.decision-log-card');
    const fullBtn = document.getElementById('fullViewBtn');
    const compactBtn = document.getElementById('compactViewBtn');
    if (mode === 'compact') {
        cards.forEach(card => {
            card.classList.add('compact');
            const content = card.querySelector('.card-content');
            if (content) content.style.display = 'none';
        });
        fullBtn.classList.remove('active');
        compactBtn.classList.add('active');
    } else {
        cards.forEach(card => {
            card.classList.remove('compact');
            const content = card.querySelector('.card-content');
            if (content) content.style.display = 'block';
        });
        fullBtn.classList.add('active');
        compactBtn.classList.remove('active');
    }
    localStorage.setItem('decisionLogsViewMode', mode);
}

function toggleCardDetails(logId) {
    const content = document.getElementById('content-' + logId);
    const toggle = document.getElementById('toggle-' + logId);
    const icon = toggle.querySelector('i');
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.className = 'fas fa-chevron-down';
    } else {
        content.style.display = 'none';
        icon.className = 'fas fa-chevron-right';
    }
} 