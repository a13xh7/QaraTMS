/**
 * Advanced Analytics Module
 * Entry point for the modular advanced analytics component
 */

// Import modules from layers
import { DataService } from './data/DataService.js';
import { StateStore } from './state/StateStore.js';
import ViewRenderer from './view/ViewRenderer.js';
import Controller from './controller/Controller.js';
import DataProcessor from './model/DataProcessor.js';
import { sanitizeHTML } from './common/sanitize.js';

/**
 * AdvancedAnalytics class - Main entry point
 */
export default class AdvancedAnalytics {
    constructor() {
        // Create instances of component layers
        this.dataService = new DataService();
        this.stateStore = new StateStore();
        this.viewRenderer = null;
        this.controller = null;
        this.dataProcessor = null;
        
        // Initialized flag
        this.initialized = false;
    }
    
    /**
     * Initialize the advanced analytics module
     * @param {Object} options - Configuration options
     * @param {Array} options.contributionData - Contribution data to analyze
     * @param {string} options.containerId - ID of the container element (default: 'advanced-analytics-container')
     * @returns {boolean} Success status
     */
    initialize(options = {}) {
        console.log('Initializing Advanced Analytics');
        
        // Check if already initialized
        if (this.initialized) {
            console.warn('Advanced Analytics already initialized');
            return true;
        }
        
        // Validate options
        const contributionData = options.contributionData || [];
        if (!Array.isArray(contributionData) || contributionData.length === 0) {
            console.error('Invalid or empty contribution data');
            return false;
        }
        
        // Set up container ID
        const containerId = options.containerId || 'advanced-analytics-container';
        
        try {
            // Initialize components
            this.viewRenderer = new ViewRenderer();
            const viewInitialized = this.viewRenderer.initialize(containerId);
            
            if (!viewInitialized) {
                console.error('Failed to initialize view renderer');
                return false;
            }
            
            this.dataProcessor = new DataProcessor(contributionData);
            this.controller = new Controller(this.viewRenderer, this.dataProcessor);
            
            // Set up event handlers
            this.setupEventHandlers();
            
            this.initialized = true;
            
            // Initial render
            this.viewRenderer.updateLoadingStatus(true);
            setTimeout(() => {
                this.controller.loadInitialData();
                this.viewRenderer.updateLoadingStatus(false);
            }, 100);
            
            console.log('Advanced Analytics initialized successfully');
            return true;
        } catch (error) {
            console.error('Error initializing advanced analytics:', error);
            return false;
        }
    }
    
    /**
     * Set up event handlers for user interactions
     */
    setupEventHandlers() {
        // Event handlers will be set up here
    }
    
    /**
     * Show the advanced analytics view
     * @param {Object} [options={}] - Display options
     * @returns {Object} The AdvancedAnalytics instance
     */
    show(options = {}) {
        if (!this.initialized) {
            console.error('Advanced analytics not initialized');
            return this;
        }
        
        // Additional show logic can be added here
        
        return this;
    }
    
    /**
     * Refresh the analytics with new data
     * @param {Array} contributionData - Updated contribution data
     * @returns {boolean} Success status
     */
    refresh(contributionData) {
        if (!this.initialized) {
            console.warn('Advanced Analytics not initialized, call initialize() first');
            return false;
        }
        
        if (!contributionData || !Array.isArray(contributionData)) {
            console.error('Invalid contribution data for refresh');
            return false;
        }
        
        // Update data service with new data
        const dataInitialized = this.dataService.initialize(contributionData);
        
        if (!dataInitialized) {
            console.error('Failed to update data service with new data');
            return false;
        }
        
        // Get all available years
        const years = this.dataService.getAllYears();
        
        // Update state with new data and years
        this.stateStore.setState({
            years,
            visibleYears: this.stateStore.getState('filters.year') === 'all' ? years : [parseInt(this.stateStore.getState('filters.year'))],
            contributionData
        });
        
        // Process data for view
        const includeInactive = this.stateStore.getState('filters.includeInactive');
        const processedData = this.dataService.processDataForYearlyView(includeInactive);
        this.stateStore.updateProcessedData(processedData);
        
        console.log('Advanced Analytics refreshed successfully');
        return true;
    }
    
    /**
     * Destroy the advanced analytics module and clean up resources
     */
    destroy() {
        if (!this.initialized) {
            return;
        }
        
        // Clean up controller (which cleans up event listeners)
        this.controller.cleanup();
        
        // Reset initialized flag
        this.initialized = false;
        
        this.viewRenderer = null;
        this.controller = null;
        this.dataProcessor = null;
        
        console.log('Advanced Analytics destroyed');
    }
} 