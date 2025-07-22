/**
 * ViewRenderer.js
 * Handles UI updates, DOM manipulation, and visual components 
 */

import { TemplateLoader } from './TemplateLoader.js';
import { sanitizeHTML, setTextContent } from '../common/sanitize.js';

// Constants for rendering performance
const RENDER_BATCH_SIZE = 50; // Number of rows to render at once
const TOOLTIP_BATCH_SIZE = 20; // Number of tooltips to initialize at once
const RENDER_DEBOUNCE_TIME = 100; // Debounce time for render updates in ms

// Colors and visual settings
const METRIC_COLORS = {
    totalEvents: ['#e3f2fd', '#90caf9', '#42a5f5', '#1e88e5', '#0d47a1'],
    mrCreated: ['#e8f5e9', '#a5d6a7', '#66bb6a', '#43a047', '#1b5e20'],
    mrApproved: ['#f3e5f5', '#ce93d8', '#ab47bc', '#8e24aa', '#4a148c'],
    repoPushes: ['#fff3e0', '#ffcc80', '#ffa726', '#f57c00', '#e65100'],
};

// Helper functions
const debounce = (func, wait) => {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

/**
 * ViewRenderer class to manage UI rendering
 */
class ViewRenderer {
    constructor(options = {}) {
        this.options = options;
        this.initialized = false;
        this.elements = {
            container: null,
            tableContainer: null,
            table: null,
            tableHeader: null,
            tableBody: null,
            controlsContainer: null,
            loadingOverlay: null
        };
        
        // Constants for month names
        this.MONTH_NAMES = {
            short: Array.from({length: 12}, (_, i) => 
                new Date(2000, i, 1).toLocaleString("default", { month: "short" })),
            long: Array.from({length: 12}, (_, i) => 
                new Date(2000, i, 1).toLocaleString("default", { month: "long" }))
        };
        
        // Templates cache
        this.templates = {};
        
        // Rendering state
        this.renderQueue = [];
        this.isRendering = false;
        
        // Tooltip initialization queue and state
        this.tooltipQueue = [];
        this.isInitializingTooltips = false;
        
        // Bind methods to preserve context
        this._bindMethods();
    }
    
    /**
     * Bind class methods to maintain 'this' context
     * @private
     */
    _bindMethods() {
        this.initialize = this.initialize.bind(this);
        this.cacheElements = this.cacheElements.bind(this);
        this.compileTemplates = this.compileTemplates.bind(this);
        this.renderYearSelector = this.renderYearSelector.bind(this);
        this.renderSquadSelector = this.renderSquadSelector.bind(this);
        this.renderContributorTable = this.renderContributorTable.bind(this);
        this.renderTableHeader = this.renderTableHeader.bind(this);
        this.renderTableRows = this.renderTableRows.bind(this);
        this.processRenderQueue = this.processRenderQueue.bind(this);
        this.initializeTooltips = this.initializeTooltips.bind(this);
        this.processTooltipQueue = this.processTooltipQueue.bind(this);
        this.updateLoadingStatus = this.updateLoadingStatus.bind(this);
        this.updateErrorStatus = this.updateErrorStatus.bind(this);
        this.getHeatmapColor = this.getHeatmapColor.bind(this);
        this.showMessage = this.showMessage.bind(this);
        this.clearMessage = this.clearMessage.bind(this);
    }
    
    /**
     * Initialize the view renderer with DOM elements and templates
     * @param {string} containerId - ID of the container element
     * @returns {boolean} Success status
     */
    initialize(containerId) {
        // Load the HTML template into the specified container
        const templateLoaded = TemplateLoader.initializeTemplate(containerId);
        if (!templateLoaded) {
            console.error('Failed to load template for advanced analytics');
            return false;
        }
        
        // Cache DOM elements
        this.elements.container = document.getElementById(containerId);
        this.elements.tableContainer = this.elements.container.querySelector('.table-responsive');
        this.elements.table = this.elements.container.querySelector('.contributor-table');
        this.elements.tableHeader = this.elements.table.querySelector('thead');
        this.elements.tableBody = this.elements.table.querySelector('tbody');
        this.elements.controlsContainer = this.elements.container.querySelector('.controls-container');
        this.elements.loadingOverlay = this.elements.container.querySelector('.loading-overlay');
        
        this.initialized = true;
        
        // Set up debounced render
        this.debouncedRender = debounce(this.processRenderQueue, RENDER_DEBOUNCE_TIME);
        
        return true;
    }
    
    /**
     * Cache DOM elements for faster access
     */
    cacheElements() {
        this.elements = {
            container: document.getElementById('advanced-analytics-container'),
            yearSelector: document.getElementById('year-selector'),
            squadSelector: document.getElementById('squad-selector'),
            searchInput: document.getElementById('contributor-search'),
            metricButtons: document.querySelectorAll('.metric-button'),
            contributorTable: document.getElementById('contributor-table'),
            tableBody: document.querySelector('#contributor-table tbody'),
            tableHeader: document.querySelector('#contributor-table thead'),
            statusMessage: document.getElementById('status-message'),
            loadingOverlay: document.querySelector('.loading-overlay'),
        };
    }
    
    /**
     * Compile HTML templates used for rendering
     */
    compileTemplates() {
        // Year selector option template
        this.templates.yearOption = (year, isChecked) => `
            <div class="year-selector-option ${isChecked ? 'checked' : ''}" data-value="${year}">
                ${year === 'all' ? 'Show All Years' : year}
            </div>
        `;
        
        // Squad selector option template
        this.templates.squadOption = (squad, isChecked) => `
            <div class="squad-selector-option ${isChecked ? 'checked' : ''}" data-value="${squad}">
                ${squad}
            </div>
        `;
        
        // Table header cell template
        this.templates.headerCell = (year, month) => `
            <th class="month-header" data-year="${year}" data-month="${month}">
                ${month}
            </th>
        `;
        
        // Table row template
        this.templates.tableRow = (contributor, squadClass) => `
            <tr class="contributor-row ${squadClass}" data-name="${contributor.name}" data-squad="${contributor.squad}">
                <td class="contributor-info">
                    <div class="contributor-name">${contributor.name}</div>
                    <div class="contributor-squad">${contributor.squad}</div>
                </td>
            </tr>
        `;
        
        // Table cell template for data
        this.templates.dataCell = (value, bgColor, tooltipContent, additionalClasses = '') => `
            <td class="data-cell ${additionalClasses}" 
                style="background-color: ${bgColor}" 
                data-bs-toggle="tooltip" 
                data-bs-html="true"
                data-bs-custom-class="custom-tooltip"
                title="${tooltipContent}">
                ${value}
            </td>
        `;
        
        // Empty/zero cell template
        this.templates.emptyCell = () => `
            <td class="data-cell empty"></td>
        `;
        
        // Tooltip content template
        this.templates.tooltipContent = (contributor, year, month, metric, value, prevValue, trend) => {
            const monthName = new Date(2000, month - 1, 1).toLocaleString("default", { month: "long" });
            let content = `
                <strong>${contributor.name}</strong><br>
                Squad: ${contributor.squad}<br>
                ${monthName} ${year}<br>
                <span class="metric-name">${this._getMetricDisplayName(metric)}</span>: ${value}
            `;
            
            // Add trend information if available
            if (trend && prevValue !== undefined) {
                const trendIcon = trend.direction === 'up' ? '▲' : trend.direction === 'down' ? '▼' : '';
                const trendClass = trend.direction === 'up' ? 'trend-up' : trend.direction === 'down' ? 'trend-down' : 'trend-neutral';
                
                content += `<br><span class="${trendClass}">
                    ${trendIcon} ${Math.abs(trend.percentage)}% ${trend.direction === 'up' ? 'increase' : 'decrease'} from previous month (${prevValue})
                </span>`;
            }
            
            return content;
        };
        
        // Status message template
        this.templates.statusMessage = (message, type = 'info') => `
            <div class="alert alert-${type}" role="alert">
                ${message}
            </div>
        `;
    }
    
    /**
     * Render the year selector dropdown
     * @param {Array} years - Available years
     * @param {string|number} selectedYear - Currently selected year
     */
    renderYearSelector(years, selectedYear = 'all') {
        if (!this.elements.yearSelector || !years || !years.length) {
            return;
        }
        
        // Clear existing options
        this.elements.yearSelector.querySelectorAll('.year-selector-option').forEach(el => {
            el.remove();
        });
        
        // Add "All Years" option
        const allYearsOption = document.createElement('div');
        allYearsOption.className = `year-selector-option all-years-option ${selectedYear === 'all' ? 'checked' : ''}`;
        allYearsOption.dataset.value = 'all';
        allYearsOption.textContent = 'Show All Years';
        this.elements.yearSelector.appendChild(allYearsOption);
        
        // Add year options
        years.forEach(year => {
            const option = document.createElement('div');
            option.className = `year-selector-option ${selectedYear == year ? 'checked' : ''}`;
            option.dataset.value = year;
            option.textContent = year;
            this.elements.yearSelector.appendChild(option);
        });
    }
    
    /**
     * Render the squad selector dropdown
     * @param {Array} squads - Available squads
     * @param {string} selectedSquad - Currently selected squad
     */
    renderSquadSelector(squads, selectedSquad = 'All') {
        if (!this.elements.squadSelector || !squads || !squads.length) {
            return;
        }
        
        // Clear existing options
        this.elements.squadSelector.querySelectorAll('.squad-selector-option').forEach(el => {
            el.remove();
        });
        
        // Add squad options
        squads.forEach(squad => {
            const option = document.createElement('div');
            option.className = `squad-selector-option ${selectedSquad === squad ? 'checked' : ''}`;
            option.dataset.value = squad;
            option.textContent = squad;
            this.elements.squadSelector.appendChild(option);
        });
    }
    
    /**
     * Render the contributor table
     * @param {Array} contributors - Filtered contributors data
     * @param {Array} years - Years to display
     * @param {string} metric - Current metric
     * @param {Object} dataService - DataService instance for data operations
     */
    renderContributorTable(contributors, years, metric, dataService) {
        if (!this.elements.contributorTable || !contributors) {
            return;
        }
        
        // Render table header with year and month columns
        this.renderTableHeader(years);
        
        // Clear the render queue
        this.renderQueue = [];
        
        // Set maximum value for heatmap color scaling
        const maxValue = dataService.findMaxValueForMetric(contributors, years, metric);
        
        // Prepare rows for rendering
        this.renderTableRows(contributors, years, metric, maxValue, dataService);
        
        // Start processing the render queue
        this.processRenderQueue();
    }
    
    /**
     * Render the table header with year and month columns
     * @param {Array} years - Years to display
     */
    renderTableHeader(years) {
        if (!this.elements.tableHeader) {
            return;
        }
        
        // Create header row
        const headerRow = document.createElement('tr');
        
        // Add contributor info header
        const infoHeader = document.createElement('th');
        infoHeader.className = 'contributor-info-header';
        infoHeader.textContent = 'Contributor';
        headerRow.appendChild(infoHeader);
        
        // Add month headers for each year
        years.forEach(year => {
            for (let month = 1; month <= 12; month++) {
                const monthName = new Date(2000, month - 1, 1).toLocaleString("default", { month: "short" });
                const th = document.createElement('th');
                th.className = 'month-header';
                th.dataset.year = year;
                th.dataset.month = month;
                th.textContent = monthName;
                
                // Check if this is a future month/year (to style differently)
                const now = new Date();
                const currentYear = now.getFullYear();
                const currentMonth = now.getMonth() + 1;
                
                if (year > currentYear || (year === currentYear && month > currentMonth)) {
                    th.classList.add('future-month');
                }
                
                headerRow.appendChild(th);
            }
        });
        
        // Clear existing header and add new one
        this.elements.tableHeader.innerHTML = '';
        this.elements.tableHeader.appendChild(headerRow);
    }
    
    /**
     * Prepare rows for the contributor table and add to render queue
     * @param {Array} contributors - Filtered contributors data
     * @param {Array} years - Years to display
     * @param {string} metric - Current metric
     * @param {number} maxValue - Maximum value for color scaling
     * @param {Object} dataService - DataService instance for data operations
     */
    renderTableRows(contributors, years, metric, maxValue, dataService) {
        if (!this.elements.tableBody) {
            return;
        }
        
        // Clear existing rows
        this.elements.tableBody.innerHTML = '';
        
        // Create document fragment for better performance
        const fragment = document.createDocumentFragment();
        
        // Track current squad for visual grouping
        let currentSquad = null;
        
        // Process each contributor
        contributors.forEach((contributor, index) => {
            // Add class for first row of each squad for visual grouping
            const isFirstInSquad = contributor.squad !== currentSquad;
            currentSquad = contributor.squad;
            const squadClass = isFirstInSquad ? 'first-in-squad' : '';
            
            // Create row
            const row = document.createElement('tr');
            row.className = `contributor-row ${squadClass}`;
            row.dataset.name = contributor.name;
            row.dataset.squad = contributor.squad;
            
            // Add contributor info cell
            const infoCell = document.createElement('td');
            infoCell.className = 'contributor-info';
            infoCell.innerHTML = `
                <div class="contributor-name">${contributor.name}</div>
                <div class="contributor-squad">${contributor.squad}</div>
            `;
            row.appendChild(infoCell);
            
            // Add to render queue instead of direct DOM manipulation
            this.renderQueue.push({
                contributor,
                row,
                years,
                metric,
                maxValue,
                dataService,
            });
            
            // Add row to fragment
            fragment.appendChild(row);
        });
        
        // Append all rows to table body
        this.elements.tableBody.appendChild(fragment);
        
        // Start processing the render queue if not already processing
        if (!this.isRendering) {
            this.processRenderQueue();
        }
    }
    
    /**
     * Process the render queue in batches
     */
    processRenderQueue() {
        if (this.renderQueue.length === 0) {
            this.isRendering = false;
            return;
        }
        
        this.isRendering = true;
        
        // Process a batch of items
        const batch = this.renderQueue.splice(0, RENDER_BATCH_SIZE);
        const tooltipsToInitialize = [];
        
        // Process each item in the batch
        batch.forEach(item => {
            const { contributor, row, years, metric, maxValue, dataService } = item;
            
            // Add data cells for each year and month
            years.forEach(year => {
                for (let month = 1; month <= 12; month++) {
                    // Check if this is a future month/year
                    const now = new Date();
                    const currentYear = now.getFullYear();
                    const currentMonth = now.getMonth() + 1;
                    const isInactivePeriod = year > currentYear || (year === currentYear && month > currentMonth);
                    
                    // Skip adding cells for future periods
                    if (isInactivePeriod) {
                        const emptyCell = document.createElement('td');
                        emptyCell.className = 'data-cell future-period';
                        row.appendChild(emptyCell);
                        continue;
                    }
                    
                    // Get data value for this period
                    let value = 0;
                    if (
                        contributor.data[year] &&
                        contributor.data[year][month] &&
                        contributor.data[year][month][metric] !== undefined
                    ) {
                        value = contributor.data[year][month][metric];
                    }
                    
                    // Create data cell
                    const cell = document.createElement('td');
                    cell.className = 'data-cell';
                    
                    // Only add styling if there is a value
                    if (value > 0) {
                        // Get background color based on value intensity
                        const bgColor = this.getHeatmapColor(value, maxValue, metric);
                        cell.style.backgroundColor = bgColor;
                        
                        // Get previous month value and calculate trend
                        const prevValue = dataService.getPreviousMonthValue(contributor, year, month, metric);
                        const trend = dataService.calculateTrend(value, prevValue);
                        
                        // Set cell content with trend indicator
                        let displayValue = value;
                        if (trend.direction === 'up') {
                            displayValue = `${value}▲`;
                            cell.classList.add('trend-up');
                        } else if (trend.direction === 'down') {
                            displayValue = `${value}▼`;
                            cell.classList.add('trend-down');
                            
                            // Add special class for drops
                            cell.classList.add('value-drop');
                        }
                        
                        cell.textContent = displayValue;
                        
                        // Check if the value has dropped from the previous month
                        if (prevValue > 0 && value < prevValue) {
                            cell.classList.add('value-drop');
                        }
                        
                        // Add tooltip attributes
                        const tooltipContent = this.templates.tooltipContent(
                            contributor,
                            year,
                            month,
                            metric,
                            value,
                            prevValue,
                            trend
                        );
                        
                        cell.setAttribute('data-bs-toggle', 'tooltip');
                        cell.setAttribute('data-bs-html', 'true');
                        cell.setAttribute('data-bs-custom-class', 'custom-tooltip');
                        cell.setAttribute('title', tooltipContent);
                        
                        // Add to tooltip initialization queue
                        tooltipsToInitialize.push(cell);
                    }
                    
                    // Add cell to row
                    row.appendChild(cell);
                }
            });
        });
        
        // Add tooltips to initialization queue
        if (tooltipsToInitialize.length > 0) {
            this.tooltipQueue = [...this.tooltipQueue, ...tooltipsToInitialize];
            
            // Start processing tooltip queue if not already processing
            if (!this.isInitializingTooltips) {
                this.processTooltipQueue();
            }
        }
        
        // Continue processing the queue if there are more items
        if (this.renderQueue.length > 0) {
            setTimeout(() => {
                this.processRenderQueue();
            }, 0);
        } else {
            this.isRendering = false;
        }
    }
    
    /**
     * Process the tooltip initialization queue in batches
     */
    processTooltipQueue() {
        if (this.tooltipQueue.length === 0) {
            this.isInitializingTooltips = false;
            return;
        }
        
        this.isInitializingTooltips = true;
        
        // Process a batch of tooltips
        const batch = this.tooltipQueue.splice(0, TOOLTIP_BATCH_SIZE);
        
        // Initialize tooltips in this batch
        batch.forEach(element => {
            // Check if Bootstrap is available
            if (window.bootstrap && window.bootstrap.Tooltip) {
                try {
                    new window.bootstrap.Tooltip(element);
                } catch (error) {
                    console.warn('Error initializing tooltip:', error);
                }
            }
        });
        
        // Continue processing the queue if there are more items
        if (this.tooltipQueue.length > 0) {
            setTimeout(() => {
                this.processTooltipQueue();
            }, 10); // Slight delay to prevent UI blocking
        } else {
            this.isInitializingTooltips = false;
        }
    }
    
    /**
     * Get color for heatmap based on value
     * @param {number} value - Data value
     * @param {number} maxValue - Maximum value for scaling
     * @param {string} metric - Current metric
     * @returns {string} CSS color
     */
    getHeatmapColor(value, maxValue, metric = 'totalEvents') {
        // Get color palette for the metric
        const colors = METRIC_COLORS[metric] || METRIC_COLORS.totalEvents;
        
        // Check for drop from previous month
        const hasDropClass = document.querySelector('.data-cell.value-drop');
        
        // If this is a value drop, use a red gradient
        if (hasDropClass) {
            return '#f8d7da'; // Light red for drops
        }
        
        // Calculate intensity (0-1)
        const intensity = maxValue === 0 ? 0 : Math.min(value / maxValue, 1);
        
        // Map intensity to color index
        const index = Math.min(Math.floor(intensity * colors.length), colors.length - 1);
        
        return colors[index];
    }
    
    /**
     * Update loading status in the UI
     * @param {boolean} isLoading - Whether data is loading
     */
    updateLoadingStatus(isLoading) {
        if (!this.elements.loadingOverlay) {
            return;
        }
        
        if (isLoading) {
            this.elements.loadingOverlay.style.display = 'flex';
        } else {
            this.elements.loadingOverlay.style.display = 'none';
        }
    }
    
    /**
     * Update error status in the UI
     * @param {string} errorMessage - Error message to display
     */
    updateErrorStatus(errorMessage) {
        if (errorMessage) {
            this.showMessage(errorMessage, 'danger');
        } else {
            this.clearMessage();
        }
    }
    
    /**
     * Show message in the status area
     * @param {string} message - Message to display
     * @param {string} type - Message type (info, success, warning, danger)
     */
    showMessage(message, type = 'info') {
        if (!this.elements.statusMessage) {
            return;
        }
        
        this.elements.statusMessage.innerHTML = this.templates.statusMessage(message, type);
        this.elements.statusMessage.style.display = 'block';
    }
    
    /**
     * Clear message from the status area
     */
    clearMessage() {
        if (!this.elements.statusMessage) {
            return;
        }
        
        this.elements.statusMessage.innerHTML = '';
        this.elements.statusMessage.style.display = 'none';
    }
    
    /**
     * Get display name for a metric
     * @param {string} metric - Metric key
     * @returns {string} Display name
     * @private
     */
    _getMetricDisplayName(metric) {
        const metricNames = {
            totalEvents: 'Total Events',
            mrCreated: 'MRs Created',
            mrApproved: 'MRs Approved',
            repoPushes: 'Repository Pushes',
        };
        
        return metricNames[metric] || metric;
    }
    
    /**
     * Update the active state of metric buttons
     * @param {string} activeMetric - Currently active metric
     */
    updateMetricButtons(activeMetric) {
        if (!this.elements.metricButtons) {
            return;
        }
        
        this.elements.metricButtons.forEach(button => {
            const metric = button.dataset.metric;
            if (metric === activeMetric) {
                button.classList.add('active');
            } else {
                button.classList.remove('active');
            }
        });
    }
}

// Export constants and ViewRenderer class
export {
    RENDER_BATCH_SIZE,
    TOOLTIP_BATCH_SIZE,
    RENDER_DEBOUNCE_TIME,
    METRIC_COLORS,
    debounce,
    ViewRenderer
}; 