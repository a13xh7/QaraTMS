/**
 * Controller.js
 * Connects data, state, and view layers, and manages event handling
 */

// Import utility for event delegation
const eventDelegation = {
    /**
     * Add event listener with delegation
     * @param {HTMLElement} element - Parent element to attach listener to
     * @param {string} eventType - Event type to listen for
     * @param {string} selector - CSS selector for target elements
     * @param {Function} handler - Event handler function
     */
    addListener(element, eventType, selector, handler) {
        if (!element) return;
        
        element.addEventListener(eventType, function(event) {
            const targetElement = event.target.closest(selector);
            
            if (targetElement && element.contains(targetElement)) {
                handler.call(targetElement, event);
            }
        });
    },
    
    /**
     * Add multiple event listeners with delegation
     * @param {HTMLElement} element - Parent element to attach listeners to
     * @param {Array} events - Array of event objects with type, selector, and handler
     */
    addListeners(element, events) {
        if (!element) return;
        
        events.forEach(event => {
            this.addListener(element, event.type, event.selector, event.handler);
        });
    }
};

/**
 * Controller class to manage application logic and event handling
 */
export default class Controller {
    /**
     * Create a new Controller instance
     * @param {Object} dataService - DataService instance
     * @param {Object} stateStore - StateStore instance
     * @param {Object} viewRenderer - ViewRenderer instance
     */
    constructor(dataService, stateStore, viewRenderer) {
        this.dataService = dataService;
        this.stateStore = stateStore;
        this.viewRenderer = viewRenderer;
        
        // Event handlers storage
        this._eventListeners = [];
        
        // State subscriptions storage
        this._stateSubscriptions = [];
        
        // Bind methods
        this._bindMethods();
        
        // Make sure our table fixes CSS is loaded
        this._loadTableFixesCSS();
    }
    
    /**
     * Bind class methods to maintain 'this' context
     * @private
     */
    _bindMethods() {
        this.initialize = this.initialize.bind(this);
        this.loadContributionData = this.loadContributionData.bind(this);
        this.setupEventListeners = this.setupEventListeners.bind(this);
        this.subscribeToStateChanges = this.subscribeToStateChanges.bind(this);
        this.handleYearSelection = this.handleYearSelection.bind(this);
        this.handleSquadSelection = this.handleSquadSelection.bind(this);
        this.handleMetricSelection = this.handleMetricSelection.bind(this);
        this.handleSearch = this.handleSearch.bind(this);
        this.handleCoreSquadsToggle = this.handleCoreSquadsToggle.bind(this);
        this.handleInactiveToggle = this.handleInactiveToggle.bind(this);
        this.handleExportCSV = this.handleExportCSV.bind(this);
        this.handleContributorClick = this.handleContributorClick.bind(this);
        this.refreshView = this.refreshView.bind(this);
        this.destroy = this.destroy.bind(this);
    }
    
    /**
     * Initialize the controller
     * @param {Object} options - Initialization options
     */
    initialize(options = {}) {
        console.log('Initializing Advanced Analytics Controller');
        
        // Initialize data service with provided data or fetch from API
        const dataInitialized = this.dataService.initialize(options.contributionData || []);
        
        if (!dataInitialized) {
            this.viewRenderer.updateErrorStatus('Failed to initialize data service');
            return false;
        }
        
        // Get all available years
        const years = this.dataService.getAllYears();
        
        // Initialize state store
        this.stateStore.initialize({
            years,
            contributionData: options.contributionData || []
        });
        
        // Initialize view renderer
        this.viewRenderer.initialize();
        
        // Process data for view
        const includeInactive = this.stateStore.getState('filters.includeInactive');
        const processedData = this.dataService.processDataForYearlyView(includeInactive);
        this.stateStore.updateProcessedData(processedData);
        
        // Subscribe to state changes
        this.subscribeToStateChanges();
        
        // Set up event listeners
        this.setupEventListeners();
        
        // Initial render
        this.refreshView();
        
        return true;
    }
    
    /**
     * Load contribution data from API or source
     * @param {string} [url] - Optional URL to load data from
     * @returns {Promise} Promise resolving to loaded data
     */
    async loadContributionData(url) {
        this.viewRenderer.updateLoadingStatus(true);
        
        try {
            // If URL is provided, fetch from there
            if (url) {
                const response = await fetch(url);
                
                if (!response.ok) {
                    throw new Error(`Failed to load data: ${response.status} ${response.statusText}`);
                }
                
                const data = await response.json();
                
                // Initialize data service with fetched data
                const dataInitialized = this.dataService.initialize(data);
                
                if (!dataInitialized) {
                    throw new Error('Failed to initialize data service with fetched data');
                }
                
                // Update state with new data
                const years = this.dataService.getAllYears();
                const includeInactive = this.stateStore.getState('filters.includeInactive');
                const processedData = this.dataService.processDataForYearlyView(includeInactive);
                
                this.stateStore.setState({
                    years,
                    visibleYears: years,
                    contributionData: data,
                });
                
                this.stateStore.updateProcessedData(processedData);
                this.viewRenderer.updateLoadingStatus(false);
                
                return data;
            } else {
                // Use existing data in state
                const data = this.stateStore.getState('contributionData');
                
                if (!data || !Array.isArray(data) || data.length === 0) {
                    throw new Error('No contribution data available');
                }
                
                // Reprocess data
                const includeInactive = this.stateStore.getState('filters.includeInactive');
                const processedData = this.dataService.processDataForYearlyView(includeInactive);
                this.stateStore.updateProcessedData(processedData);
                
                this.viewRenderer.updateLoadingStatus(false);
                return data;
            }
        } catch (error) {
            console.error('Error loading contribution data:', error);
            this.viewRenderer.updateErrorStatus(`Error: ${error.message}`);
            this.viewRenderer.updateLoadingStatus(false);
            return [];
        }
    }
    
    /**
     * Set up event listeners for UI interactions
     */
    setupEventListeners() {
        // Cache DOM elements for event binding
        const container = document.getElementById('advanced-analytics-container');
        
        if (!container) {
            console.error('Advanced analytics container not found');
            return;
        }
        
        // Set up event delegation
        const events = [
            // Year selector events
            {
                type: 'click',
                selector: '.year-selector-option',
                handler: this.handleYearSelection
            },
            
            // Squad selector events
            {
                type: 'click',
                selector: '.squad-selector-option',
                handler: this.handleSquadSelection
            },
            
            // Metric buttons events
            {
                type: 'click',
                selector: '.metric-button',
                handler: this.handleMetricSelection
            },
            
            // Core squads toggle
            {
                type: 'change',
                selector: '#core-squads-toggle',
                handler: this.handleCoreSquadsToggle
            },
            
            // Include inactive toggle
            {
                type: 'change',
                selector: '#include-inactive-toggle',
                handler: this.handleInactiveToggle
            },
            
            // Export button
            {
                type: 'click',
                selector: '#export-csv-button',
                handler: this.handleExportCSV
            },
            
            // Contributor row click
            {
                type: 'click',
                selector: '.contributor-row',
                handler: this.handleContributorClick
            }
        ];
        
        // Add all event listeners with delegation
        eventDelegation.addListeners(container, events);
        
        // Set up search input with debounce
        const searchInput = document.getElementById('contributor-search');
        if (searchInput) {
            // Remove any existing event listeners
            searchInput.removeEventListener('input', this._searchHandler);
            
            // Create debounced search handler
            this._searchHandler = this._debounce(this.handleSearch, 300);
            
            // Add new event listener
            searchInput.addEventListener('input', this._searchHandler);
            
            // Store for cleanup
            this._eventListeners.push({
                element: searchInput,
                type: 'input',
                handler: this._searchHandler
            });
        }
    }
    
    /**
     * Subscribe to state changes
     */
    subscribeToStateChanges() {
        // Subscribe to general state changes
        const unsubscribeGeneral = this.stateStore.subscribe('*', () => {
            this.refreshView();
        });
        
        // Subscribe to filter changes
        const unsubscribeFilter = this.stateStore.subscribe('filter-change', () => {
            this.stateStore.updateFilteredData();
        });
        
        // Store subscriptions for cleanup
        this._stateSubscriptions.push(unsubscribeGeneral, unsubscribeFilter);
    }
    
    /**
     * Handle year selection from dropdown
     * @param {Event} event - Click event
     */
    handleYearSelection(event) {
        const yearValue = this.dataset.value;
        
        // Update state
        this.stateStore.setYearFilter(yearValue);
    }
    
    /**
     * Handle squad selection from dropdown
     * @param {Event} event - Click event
     */
    handleSquadSelection(event) {
        const squadValue = this.dataset.value;
        
        // Update state
        this.stateStore.setSquadFilter(squadValue);
    }
    
    /**
     * Handle metric button click
     * @param {Event} event - Click event
     */
    handleMetricSelection(event) {
        const metricValue = this.dataset.metric;
        
        // Update state
        this.stateStore.setMetric(metricValue);
    }
    
    /**
     * Handle search input changes
     * @param {Event} event - Input event
     */
    handleSearch(event) {
        const searchValue = event.target.value.trim();
        
        // Update state
        this.stateStore.setSearchFilter(searchValue);
    }
    
    /**
     * Handle core squads toggle
     * @param {Event} event - Change event
     */
    handleCoreSquadsToggle(event) {
        // Toggle state
        this.stateStore.toggleCoreSquadsOnly();
    }
    
    /**
     * Handle include inactive toggle
     * @param {Event} event - Change event
     */
    handleInactiveToggle(event) {
        // Toggle state
        this.stateStore.toggleIncludeInactive();
        
        // Reprocess data to include/exclude inactive contributors
        const includeInactive = this.stateStore.getState('filters.includeInactive');
        const processedData = this.dataService.processDataForYearlyView(includeInactive);
        this.stateStore.updateProcessedData(processedData);
    }
    
    /**
     * Handle export to CSV button click
     * @param {Event} event - Click event
     */
    handleExportCSV(event) {
        // Get current state
        const state = this.stateStore.getState();
        const filteredData = state.filteredData;
        const visibleYears = state.visibleYears;
        const metric = state.currentMetric;
        
        // Generate CSV content
        const csvContent = this.dataService.exportToCSV(filteredData, metric, visibleYears);
        
        // Create download link
        const encodedContent = encodeURIComponent(csvContent);
        const dataUri = `data:text/csv;charset=utf-8,${encodedContent}`;
        
        const downloadLink = document.createElement('a');
        downloadLink.setAttribute('href', dataUri);
        downloadLink.setAttribute('download', `contribution_data_${metric}_${new Date().toISOString().slice(0, 10)}.csv`);
        document.body.appendChild(downloadLink);
        
        // Trigger download
        downloadLink.click();
        
        // Clean up
        document.body.removeChild(downloadLink);
    }
    
    /**
     * Handle contributor row click
     * @param {Event} event - Click event
     */
    handleContributorClick(event) {
        // Get contributor data
        const contributorName = this.dataset.name;
        const contributorSquad = this.dataset.squad;
        
        // Show contributor details or allow editing
        if (event.ctrlKey || event.metaKey) {
            // Open edit dialog when Ctrl/Cmd key is pressed
            this._showSquadEditDialog(contributorName, contributorSquad);
        } else {
            // Show details dialog
            this._showContributorDetails(contributorName);
        }
    }
    
    /**
     * Show dialog to edit a contributor's squad
     * @param {string} contributorName - Name of the contributor
     * @param {string} currentSquad - Current squad of the contributor
     * @private
     */
    _showSquadEditDialog(contributorName, currentSquad) {
        // Get available squads
        const squads = this.dataService.teams;
        
        // Create a simple prompt for squad selection
        // This would ideally be replaced with a proper modal dialog
        const newSquad = prompt(
            `Edit squad for ${contributorName} (currently: ${currentSquad})`,
            currentSquad
        );
        
        // Update if a new squad was provided
        if (newSquad && newSquad !== currentSquad) {
            const updated = this.dataService.updateContributorSquad(contributorName, newSquad);
            
            if (updated) {
                // Reprocess data with the updated squad
                const includeInactive = this.stateStore.getState('filters.includeInactive');
                const processedData = this.dataService.processDataForYearlyView(includeInactive);
                this.stateStore.updateProcessedData(processedData);
                
                alert(`Squad updated for ${contributorName}: ${currentSquad} → ${newSquad}`);
            } else {
                alert(`Failed to update squad for ${contributorName}`);
            }
        }
    }
    
    /**
     * Show contributor details
     * @param {string} contributorName - Name of the contributor
     * @private
     */
    _showContributorDetails(contributorName) {
        // Find contributor in data
        const contributors = this.stateStore.getState('filteredData');
        const contributor = contributors.find(c => c.name === contributorName);
        
        if (!contributor) {
            console.warn(`Contributor not found: ${contributorName}`);
            return;
        }
        
        // For now, just log contributor details
        // This would ideally show a detailed modal with charts
        console.log('Contributor details:', contributor);
    }
    
    /**
     * Refresh the view based on current state
     */
    refreshView() {
        const state = this.stateStore.getState();
        
        // Render year selector
        this.viewRenderer.renderYearSelector(state.years, state.filters.year);
        
        // Render squad selector
        this.viewRenderer.renderSquadSelector(['All', ...this.dataService.teams], state.filters.squad);
        
        // Update metric buttons
        this.viewRenderer.updateMetricButtons(state.currentMetric);
        
        // Render contributor table
        this.viewRenderer.renderContributorTable(
            state.filteredData,
            state.visibleYears,
            state.currentMetric,
            this.dataService
        );
        
        // Update search input
        const searchInput = document.getElementById('contributor-search');
        if (searchInput) {
            searchInput.value = state.filters.search;
        }
        
        // Update toggle checkboxes
        const coreSquadsToggle = document.getElementById('core-squads-toggle');
        if (coreSquadsToggle) {
            coreSquadsToggle.checked = state.filters.showCoreSquadsOnly;
        }
        
        const inactiveToggle = document.getElementById('include-inactive-toggle');
        if (inactiveToggle) {
            inactiveToggle.checked = state.filters.includeInactive;
        }
    }
    
    /**
     * Cleanup resources and event listeners
     */
    destroy() {
        // Remove all event listeners
        this._eventListeners.forEach(({ element, type, handler }) => {
            element.removeEventListener(type, handler);
        });
        
        // Clear event listeners array
        this._eventListeners = [];
        
        // Unsubscribe from state changes
        this._stateSubscriptions.forEach(unsubscribe => {
            if (typeof unsubscribe === 'function') {
                unsubscribe();
            }
        });
        
        // Clear subscriptions array
        this._stateSubscriptions = [];
        
        console.log('Advanced Analytics Controller destroyed');
    }
    
    /**
     * Create a debounced function
     * @param {Function} func - Function to debounce
     * @param {number} wait - Wait time in milliseconds
     * @returns {Function} Debounced function
     * @private
     */
    _debounce(func, wait) {
        let timeout;
        
        return function(...args) {
            const context = this;
            
            clearTimeout(timeout);
            
            timeout = setTimeout(() => {
                func.apply(context, args);
            }, wait);
        };
    }
    
    /**
     * Private method to load the table fixes CSS
     * @private
     */
    _loadTableFixesCSS() {
        // Check if our CSS is already loaded
        const cssId = 'advanced-analytics-table-fixes-css';
        if (!document.getElementById(cssId)) {
            const head = document.getElementsByTagName('head')[0];
            const link = document.createElement('link');
            link.id = cssId;
            link.rel = 'stylesheet';
            link.type = 'text/css';
            link.href = '/css/advanced-analytics/components/table-fixes.css';
            link.media = 'all';
            head.appendChild(link);
        }
    }
}

export { Controller, eventDelegation }; 