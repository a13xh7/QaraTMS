/**
 * Template loader for the advanced analytics module
 * Handles loading the HTML template into the DOM
 */

// HTML template for the advanced analytics UI
const HTML_TEMPLATE = `
<div class="advanced-analytics-container">
    <!-- Status messages -->
    <div class="status-message-container" aria-live="polite"></div>
    
    <!-- Controls container -->
    <div class="controls-container">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="year-selector">Select Year</label>
                    <select id="year-selector" class="form-control">
                        <option value="all">All Years</option>
                        <!-- Years will be added dynamically -->
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="squad-selector">Select Squad</label>
                    <select id="squad-selector" class="form-control">
                        <option value="all">All Squads</option>
                        <!-- Squads will be added dynamically -->
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="contributor-search">Search Contributor</label>
                    <input type="text" id="contributor-search" class="form-control" placeholder="Enter name">
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="metric-button-group" role="group" aria-label="Select metric">
                    <button type="button" class="metric-button active" data-metric="totalEvents">Total Events</button>
                    <button type="button" class="metric-button" data-metric="mrCreated">MRs Created</button>
                    <button type="button" class="metric-button" data-metric="mrApproved">MRs Approved</button>
                    <button type="button" class="metric-button" data-metric="repoPushes">Repo Pushes</button>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <div class="form-check form-switch d-inline-block me-3">
                    <input class="form-check-input" type="checkbox" id="show-trends">
                    <label class="form-check-label" for="show-trends">Show Trends</label>
                </div>
                <div class="form-check form-switch d-inline-block me-3">
                    <input class="form-check-input" type="checkbox" id="exclude-squads">
                    <label class="form-check-label" for="exclude-squads">Show Squads Only</label>
                </div>
                <button type="button" class="btn btn-outline-primary export-btn">
                    <i class="bi bi-download"></i> Export
                </button>
            </div>
        </div>
    </div>
    
    <!-- Table container -->
    <div class="table-responsive mt-3">
        <table class="contributor-table table">
            <thead>
                <!-- Year and month headers will be added dynamically -->
            </thead>
            <tbody>
                <!-- Data rows will be added dynamically -->
            </tbody>
        </table>
    </div>
    
    <!-- Loading overlay -->
    <div class="loading-overlay">
        <div class="loading-spinner"></div>
        <div class="loading-message">Loading data...</div>
    </div>
    
    <!-- ARIA descriptions for accessibility -->
    <div class="sr-only" id="table-description">
        This table shows contributor metrics by month and year. Use arrow keys to navigate between cells.
    </div>
    <div class="sr-only" id="metric-description">
        Select a metric to view different data types: total events, MRs created, MRs approved, or repository pushes.
    </div>
</div>
`;

/**
 * TemplateLoader class for managing the HTML template
 */
export default class TemplateLoader {
    /**
     * Load the HTML template into the specified container
     * @param {string} containerId - ID of the container element
     * @returns {boolean} Success status
     */
    static loadTemplate(containerId) {
        const container = document.getElementById(containerId);
        if (!container) {
            console.error(`Container element with ID "${containerId}" not found`);
            return false;
        }
        
        container.innerHTML = HTML_TEMPLATE;
        return true;
    }
    
    /**
     * Initialize the template with accessibility attributes
     * @param {string} containerId - ID of the container element
     * @returns {boolean} Success status
     */
    static initializeTemplate(containerId) {
        // Load the template into the container
        const success = this.loadTemplate(containerId);
        if (!success) return false;
        
        // Set up accessibility attributes
        this._setupAccessibility(containerId);
        
        return true;
    }
    
    /**
     * Set up ARIA attributes for accessibility
     * @param {string} containerId - ID of the container element
     * @private
     */
    static _setupAccessibility(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        // Set up table accessibility
        const table = container.querySelector('.contributor-table');
        const tableDescription = container.querySelector('#table-description');
        
        if (table && tableDescription) {
            table.setAttribute('aria-describedby', 'table-description');
            table.setAttribute('role', 'grid');
        }
        
        // Set up metrics accessibility
        const metricButtons = container.querySelector('.metric-button-group');
        const metricDescription = container.querySelector('#metric-description');
        
        if (metricButtons && metricDescription) {
            metricButtons.setAttribute('aria-describedby', 'metric-description');
        }
    }
}