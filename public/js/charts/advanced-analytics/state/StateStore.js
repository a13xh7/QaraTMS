/**
 * StateStore.js
 * Centralized store for application state, filters, and user preferences
 */

/**
 * StateStore class to manage application state
 */
class StateStore {
    constructor() {
        // Initialize with default state
        this.state = {
            // UI state
            isLoading: false,
            errorMessage: null,
            
            // Data state
            contributionData: [],
            processedData: [],
            filteredData: [],
            
            // Filter state
            filters: {
                year: "all",      // Selected year filter
                squad: "All",     // Selected squad filter
                search: "",       // Search filter for contributor names
                showCoreSquadsOnly: false, // Show only core squads
                includeInactive: false,     // Include inactive contributors
            },
            
            // Display state
            currentMetric: "totalEvents", // Current metric to display
            years: [],           // Available years
            visibleYears: [],    // Years currently visible
            
            // Other state
            lastUpdate: null,    // Timestamp of last data update
            darkMode: false,     // Dark mode preference
        };
        
        // Callbacks for state changes
        this._listeners = {};
        
        // Bind methods
        this._bindMethods();
    }
    
    /**
     * Bind class methods to maintain 'this' context
     * @private
     */
    _bindMethods() {
        this.getState = this.getState.bind(this);
        this.setState = this.setState.bind(this);
        this.subscribe = this.subscribe.bind(this);
        this.unsubscribe = this.unsubscribe.bind(this);
        this.setFilter = this.setFilter.bind(this);
        this.resetFilters = this.resetFilters.bind(this);
        this.setMetric = this.setMetric.bind(this);
        this.setYearFilter = this.setYearFilter.bind(this);
        this.setSquadFilter = this.setSquadFilter.bind(this);
        this.setSearchFilter = this.setSearchFilter.bind(this);
        this.toggleCoreSquadsOnly = this.toggleCoreSquadsOnly.bind(this);
        this.toggleIncludeInactive = this.toggleIncludeInactive.bind(this);
        this.setVisibleYears = this.setVisibleYears.bind(this);
        this.updateProcessedData = this.updateProcessedData.bind(this);
        this.updateFilteredData = this.updateFilteredData.bind(this);
        this.persistUserPreferences = this.persistUserPreferences.bind(this);
        this.loadUserPreferences = this.loadUserPreferences.bind(this);
    }
    
    /**
     * Get the current state or a specific part of it
     * @param {string} [path] - Optional path to a specific state property
     * @returns {*} State or state property
     */
    getState(path = null) {
        if (!path) {
            return { ...this.state }; // Return a copy to prevent direct mutation
        }
        
        // Handle dot notation paths
        const parts = path.split('.');
        let current = this.state;
        
        for (const part of parts) {
            if (current[part] === undefined) {
                return undefined;
            }
            current = current[part];
        }
        
        return current;
    }
    
    /**
     * Set state with partial state object
     * @param {Object} partialState - Partial state to update
     * @param {string} [source] - Source of state change for event name
     */
    setState(partialState, source = 'general') {
        // Check if there are actual changes
        const hasChanges = Object.entries(partialState).some(([key, value]) => {
            // Handle complex objects by comparing JSON strings
            return JSON.stringify(this.state[key]) !== JSON.stringify(value);
        });
        
        if (!hasChanges) {
            return; // Skip if no changes
        }
        
        // Update state
        this.state = {
            ...this.state,
            ...partialState,
            lastUpdate: new Date(), // Update timestamp
        };
        
        // Notify listeners
        this._notifyListeners(source);
    }
    
    /**
     * Subscribe to state changes
     * @param {string} event - Event name
     * @param {Function} callback - Callback function
     * @returns {Function} Unsubscribe function
     */
    subscribe(event, callback) {
        if (!this._listeners[event]) {
            this._listeners[event] = [];
        }
        
        this._listeners[event].push(callback);
        
        // Return unsubscribe function
        return () => {
            this.unsubscribe(event, callback);
        };
    }
    
    /**
     * Unsubscribe from state changes
     * @param {string} event - Event name
     * @param {Function} callback - Callback function
     */
    unsubscribe(event, callback) {
        if (!this._listeners[event]) {
            return;
        }
        
        this._listeners[event] = this._listeners[event].filter(
            (cb) => cb !== callback
        );
    }
    
    /**
     * Notify listeners of state changes
     * @param {string} event - Event that triggered the change
     * @private
     */
    _notifyListeners(event) {
        // Notify event-specific listeners
        if (this._listeners[event]) {
            this._listeners[event].forEach((callback) => {
                callback(this.state);
            });
        }
        
        // Notify general listeners
        if (this._listeners['*']) {
            this._listeners['*'].forEach((callback) => {
                callback(this.state);
            });
        }
    }
    
    /**
     * Set a specific filter
     * @param {string} filterName - Name of the filter
     * @param {*} value - Filter value
     */
    setFilter(filterName, value) {
        if (!Object.keys(this.state.filters).includes(filterName)) {
            console.warn(`Unknown filter: ${filterName}`);
            return;
        }
        
        // Skip if value hasn't changed
        if (this.state.filters[filterName] === value) {
            return;
        }
        
        this.setState({
            filters: {
                ...this.state.filters,
                [filterName]: value,
            },
        }, 'filter-change');
        
        // Persist user preferences for persistent filters
        this.persistUserPreferences();
    }
    
    /**
     * Reset all filters to default values
     */
    resetFilters() {
        this.setState({
            filters: {
                year: "all",
                squad: "All",
                search: "",
                showCoreSquadsOnly: false,
                includeInactive: false,
            },
        }, 'filters-reset');
        
        // Persist the reset preferences
        this.persistUserPreferences();
    }
    
    /**
     * Set current metric
     * @param {string} metric - New metric
     */
    setMetric(metric) {
        if (this.state.currentMetric === metric) {
            return;
        }
        
        this.setState({ currentMetric: metric }, 'metric-change');
        this.persistUserPreferences();
    }
    
    /**
     * Set year filter
     * @param {string|number} year - Year filter (can be "all" or year number)
     */
    setYearFilter(year) {
        this.setFilter('year', year);
        
        // Update visible years
        if (year === 'all') {
            this.setState({ visibleYears: [...this.state.years] });
        } else {
            this.setState({ visibleYears: [parseInt(year)] });
        }
    }
    
    /**
     * Set squad filter
     * @param {string} squad - Squad filter
     */
    setSquadFilter(squad) {
        this.setFilter('squad', squad);
    }
    
    /**
     * Set search filter for contributor names
     * @param {string} search - Search query
     */
    setSearchFilter(search) {
        this.setFilter('search', search);
    }
    
    /**
     * Toggle filter to show only core squads
     */
    toggleCoreSquadsOnly() {
        this.setFilter('showCoreSquadsOnly', !this.state.filters.showCoreSquadsOnly);
    }
    
    /**
     * Toggle filter to include inactive contributors
     */
    toggleIncludeInactive() {
        this.setFilter('includeInactive', !this.state.filters.includeInactive);
    }
    
    /**
     * Set visible years based on current filters
     * @param {Array} years - Years to show
     */
    setVisibleYears(years) {
        this.setState({ visibleYears: years }, 'years-visibility-change');
    }
    
    /**
     * Update processed data and calculate derived data
     * @param {Array} processedData - New processed data
     */
    updateProcessedData(processedData) {
        this.setState({ 
            processedData,
            isLoading: false
        }, 'data-processed');
        
        // Update filtered data based on current filters
        this.updateFilteredData();
    }
    
    /**
     * Update filtered data based on current filters
     */
    updateFilteredData() {
        const { processedData, filters } = this.state;
        
        // Apply filters
        let filtered = processedData;
        
        // Filter by squad
        if (filters.squad !== 'All') {
            filtered = filtered.filter(
                (contributor) => contributor.squad === filters.squad
            );
        }
        
        // Filter by search
        if (filters.search) {
            const searchLower = filters.search.toLowerCase();
            filtered = filtered.filter((contributor) =>
                contributor.name.toLowerCase().includes(searchLower)
            );
        }
        
        // Filter by core squads only
        if (filters.showCoreSquadsOnly) {
            // This requires the INCLUDED_SQUADS constant from DataService
            // For now, we'll assume it's passed or defined elsewhere
            // Would need to be refactored if INCLUDED_SQUADS is not available
            filtered = filtered.filter((contributor) =>
                ['Core', 'Grape', 'Ops', 'PC', 'Shopex'].includes(contributor.squad)
            );
        }
        
        this.setState({ filteredData: filtered }, 'data-filtered');
    }
    
    /**
     * Persist user preferences to local storage
     */
    persistUserPreferences() {
        // Save user preferences
        const preferences = {
            currentMetric: this.state.currentMetric,
            filters: {
                year: this.state.filters.year,
                squad: this.state.filters.squad,
                showCoreSquadsOnly: this.state.filters.showCoreSquadsOnly,
                includeInactive: this.state.filters.includeInactive,
            },
            darkMode: this.state.darkMode,
        };
        
        try {
            localStorage.setItem('advancedAnalyticsPreferences', JSON.stringify(preferences));
        } catch (error) {
            console.warn('Failed to save user preferences:', error);
        }
    }
    
    /**
     * Load user preferences from local storage
     */
    loadUserPreferences() {
        try {
            const savedPreferences = localStorage.getItem('advancedAnalyticsPreferences');
            
            if (savedPreferences) {
                const preferences = JSON.parse(savedPreferences);
                
                this.setState({
                    currentMetric: preferences.currentMetric || this.state.currentMetric,
                    filters: {
                        ...this.state.filters,
                        ...preferences.filters,
                    },
                    darkMode: preferences.darkMode || false,
                }, 'preferences-loaded');
            }
        } catch (error) {
            console.warn('Failed to load user preferences:', error);
        }
    }
    
    /**
     * Initialize the state store with data and configuration
     * @param {Object} config - Configuration options
     */
    initialize(config) {
        const { years, contributionData } = config;
        
        this.setState({
            years: years || [],
            visibleYears: years || [],
            contributionData: contributionData || [],
            isLoading: false,
        }, 'initialized');
        
        // Load user preferences
        this.loadUserPreferences();
        
        return true;
    }
}

export { StateStore }; 