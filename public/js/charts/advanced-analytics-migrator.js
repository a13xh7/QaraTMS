/**
 * Advanced Analytics Migrator
 * Helps migrate from the original monolithic implementation to the new modular structure
 */

import AdvancedAnalytics from './advanced-analytics/index.js';

// Import supporting modules
import ModalIntegration from './advanced-analytics-modal-integration.js';
import { sanitizeHTML, sanitizeAttribute } from './advanced-analytics/common/sanitize.js';
import logger from './advanced-analytics/common/logger.js';
import KeyboardNavigation from './advanced-analytics/view/KeyboardNavigation.js';

// Module state
let instance = null;
let keyboardNav = null;
let featureFlags = {
    useModularImplementation: false,
    useAccessibleUI: true,
    useNonBlockingModals: true,
    useKeyboardNavigation: true,
    useSanitization: true,
    usePerformanceOptimizations: true,
    useToasts: true,
    useTableFixes: true
};

/**
 * Initialize feature flags based on the page or localStorage settings
 * @private
 */
function _initializeFeatureFlags() {
    try {
        // Check for localStorage overrides (for development/testing)
        const storedFlags = localStorage.getItem('advancedAnalyticsFlags');
        if (storedFlags) {
            const parsedFlags = JSON.parse(storedFlags);
            featureFlags = { ...featureFlags, ...parsedFlags };
            logger.info('Using feature flags from localStorage:', featureFlags);
        }
        
        // Check for flags in URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('aa-modular')) {
            featureFlags.useModularImplementation = urlParams.get('aa-modular') === 'true';
        }
        
        // Check for flags in meta tags
        const featureFlagsMeta = document.querySelector('meta[name="aa-feature-flags"]');
        if (featureFlagsMeta) {
            try {
                const metaFlags = JSON.parse(featureFlagsMeta.content);
                featureFlags = { ...featureFlags, ...metaFlags };
                logger.info('Using feature flags from meta tag:', featureFlags);
            } catch (e) {
                logger.warn('Failed to parse feature flags from meta tag:', e);
            }
        }
    } catch (e) {
        logger.warn('Error initializing feature flags:', e);
    }
}

/**
 * Check if a specific feature flag is enabled
 * @param {string} flagName - Name of the feature flag to check
 * @returns {boolean} True if the feature is enabled
 */
function isFeatureEnabled(flagName) {
    return featureFlags[flagName] === true;
}

// Initialize feature flags
_initializeFeatureFlags();

// Load CSS fixes
loadTableFixesCSS();

// Load table fixes CSS for both implementations
function loadTableFixesCSS() {
    if (isFeatureEnabled('useTableFixes')) {
        // Load the table fixes CSS
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
            logger.info('Table fixes CSS loaded');
        }
        
        // Load the fixed headers CSS to fix the year headers freeze
        const fixedHeadersCssId = 'advanced-analytics-fixed-headers-css';
        if (!document.getElementById(fixedHeadersCssId)) {
            const head = document.getElementsByTagName('head')[0];
            const link = document.createElement('link');
            link.id = fixedHeadersCssId;
            link.rel = 'stylesheet';
            link.type = 'text/css';
            link.href = '/css/advanced-analytics/components/fixed-headers.css';
            link.media = 'all';
            head.appendChild(link);
            logger.info('Fixed headers CSS loaded to resolve year header freeze issue');
        }
    }
}

// Create global reference to maintain compatibility with existing code
window.AdvancedAnalytics = {
    // Store instance of new modular implementation
    _instance: null,
    
    /**
     * Initialize the advanced analytics component
     * @param {Array} contributionData - Contribution data to analyze
     * @param {string} containerId - Container ID for the analytics component
     * @returns {Object} Instance of AdvancedAnalytics
     */
    initialize(contributionData, containerId = 'advanced-analytics-container') {
        console.log('Initializing Advanced Analytics via migration layer');
        
        // Create new instance if not already created
        if (!this._instance) {
            this._instance = new AdvancedAnalytics();
        }
        
        // Initialize with contribution data and container ID
        this._instance.initialize({ 
            contributionData, 
            containerId 
        });
        
        // Return the instance for chaining
        return this._instance;
    },
    
    /**
     * Compatibility method for original event setup
     * Legacy code expects this function to exist
     */
    setupEvents() {
        console.log('Events are now handled internally by the Controller');
        // No action needed as events are now handled by the Controller
    },
    
    /**
     * Map of legacy function names to new instance methods
     * This allows old code to continue working while transitioning
     */
    _legacyMethodMap: {
        getAllYears: function() {
            return this._instance.dataService.getAllYears();
        },
        processDataForYearlyView: function(includeInactive) {
            return this._instance.dataService.processDataForYearlyView(includeInactive);
        },
        setupYearSelector: function() {
            // No action needed as this is handled by ViewRenderer
            console.log('Year selector is now managed by ViewRenderer');
        },
        setupSquadSelector: function() {
            // No action needed as this is handled by ViewRenderer
            console.log('Squad selector is now managed by ViewRenderer');
        },
        filterContributors: function(data, filters) {
            return this._instance.dataService.filterContributors(data, filters);
        },
        renderContributorsTable: function() {
            // Trigger a state update to refresh the view
            this._instance.stateStore.setState({}, 'manual-render-trigger');
        },
        exportToCSV: function() {
            // Simulate a click on the export button
            document.getElementById('export-csv-button')?.click();
        }
    }
};

// Set up proxy to handle legacy method calls
Object.keys(window.AdvancedAnalytics._legacyMethodMap).forEach(method => {
    window.AdvancedAnalytics[method] = function(...args) {
        if (!window.AdvancedAnalytics._instance) {
            console.error(`Cannot call ${method} before initialization`);
            return null;
        }
        
        // Call the mapped method
        return window.AdvancedAnalytics._legacyMethodMap[method].apply(window.AdvancedAnalytics, args);
    };
});

/**
 * Function to check if a page should use the new implementation
 * This allows for gradual rollout to specific pages
 * @returns {boolean} Whether to use new implementation
 */
function shouldUseNewImplementation() {
    // Check for feature flag in URL or localStorage
    const urlParams = new URLSearchParams(window.location.search);
    const featureFlagInUrl = urlParams.get('useModularAnalytics') === 'true';
    const featureFlagInStorage = localStorage.getItem('useModularAnalytics') === 'true';
    
    return featureFlagInUrl || featureFlagInStorage;
}

/**
 * Function to check if we should auto-initialize on the page
 * @returns {boolean} Whether to auto-initialize
 */
function shouldAutoInitialize() {
    // Check for auto-init flag in URL or localStorage
    const urlParams = new URLSearchParams(window.location.search);
    const autoInitInUrl = urlParams.get('autoInitAnalytics') !== 'false'; // default to true
    const autoInitInStorage = localStorage.getItem('autoInitAnalytics') !== 'false'; // default to true
    
    return autoInitInUrl && autoInitInStorage;
}

/**
 * Function to load the appropriate implementation
 */
async function loadImplementation() {
    if (shouldUseNewImplementation()) {
        console.log('Using new modular Advanced Analytics implementation');
        // New implementation is already loaded via import
        
        // Auto-initialize if the container exists and auto-init is enabled
        if (shouldAutoInitialize() && document.getElementById('advanced-analytics-container')) {
            // Wait for data to be available
            window.addEventListener('contributionDataReady', (event) => {
                if (event.detail && event.detail.data) {
                    window.AdvancedAnalytics.initialize(event.detail.data);
                }
            });
            
            // If data is already available in a known location, initialize with it
            if (window.contributionData) {
                window.AdvancedAnalytics.initialize(window.contributionData);
            }
        }
    } else {
        console.log('Using legacy Advanced Analytics implementation');
        // Load the legacy implementation
        try {
            // Import the original monolithic implementation
            await import('./advanced-analytics.js');
            console.log('Legacy implementation loaded successfully');
        } catch (error) {
            console.error('Failed to load legacy implementation, falling back to new implementation', error);
            // The new implementation is already loaded as a fallback
        }
    }
}

// Load the appropriate implementation on page load
document.addEventListener('DOMContentLoaded', loadImplementation);

// Export for module usage
export { loadImplementation, shouldUseNewImplementation, shouldAutoInitialize };

// Create a proxy object that forwards calls to either the legacy or modular implementation
const AdvancedAnalyticsMigrator = {
    /**
     * Initialize the advanced analytics component
     * @param {Array} contributionData - Contribution data to analyze
     * @param {string} [containerId='advanced-analytics-container'] - Container ID
     * @returns {boolean} Success status
     */
    init: function(contributionData, containerId = 'advanced-analytics-container') {
        // Validate contributionData
        if (!contributionData || !Array.isArray(contributionData)) {
            logger.error('Invalid contribution data provided to advanced analytics');
            return false;
        }
        
        // Check if we should use the modular implementation
        if (isFeatureEnabled('useModularImplementation')) {
            logger.info('Using modular advanced analytics implementation');
            
            // Create an instance if it doesn't exist
            if (!instance) {
                instance = new AdvancedAnalytics();
            }
            
            // Initialize with the provided data and container ID
            const success = instance.initialize({
                contributionData,
                containerId
            });
            
            // Initialize keyboard navigation if enabled
            if (success && isFeatureEnabled('useKeyboardNavigation')) {
                setTimeout(() => {
                    keyboardNav = new KeyboardNavigation({
                        tableSelector: '#contributor-table',
                        cellSelector: '.data-cell',
                        activeClass: 'keyboard-focus'
                    });
                    keyboardNav.enable();
                    logger.info('Keyboard navigation enabled');
                }, 1000); // Delay to ensure table is fully loaded
            }
            
            return success;
        } else {
            logger.info('Using legacy advanced analytics implementation');
            
            // Apply CSS fixes for the legacy table
            this._applyLegacyTableFixes();
            
            // Use legacy analytics
            const success = window.AdvancedAnalytics.init(contributionData);
            
            // Set up keyboard navigation even in legacy mode if enabled
            if (success && isFeatureEnabled('useKeyboardNavigation')) {
                setTimeout(() => {
                    keyboardNav = new KeyboardNavigation({
                        tableSelector: '#yearlyOverviewTable',
                        cellSelector: 'td:not(.sticky-col)',
                        activeClass: 'keyboard-focus'
                    });
                    keyboardNav.enable();
                    logger.info('Keyboard navigation enabled for legacy implementation');
                }, 1000); // Delay to ensure table is fully loaded
            }
            
            return success;
        }
    },
    
    /**
     * Show the advanced analytics view
     * @param {Object} [options={}] - Display options
     * @returns {Object} The advanced analytics instance
     */
    show: function(options = {}) {
        if (instance && isFeatureEnabled('useModularImplementation')) {
            return instance.show(options);
        } else {
            if (window.AdvancedAnalytics.showAdvancedAnalysisModal) {
                window.AdvancedAnalytics.showAdvancedAnalysisModal(options);
            }
            return window.AdvancedAnalytics;
        }
    },
    
    /**
     * Clean up the advanced analytics component
     */
    destroy: function() {
        if (keyboardNav) {
            keyboardNav.disable();
            keyboardNav = null;
        }
        
        if (instance && isFeatureEnabled('useModularImplementation')) {
            instance.destroy();
            instance = null;
        }
    },
    
    /**
     * Get the current instance (for testing/debugging)
     * @returns {Object|null} Current instance or null
     */
    getInstance: function() {
        return instance || window.AdvancedAnalytics;
    },
    
    /**
     * Update feature flags at runtime
     * @param {Object} flags - Feature flags to update
     */
    updateFeatureFlags: function(flags) {
        if (flags && typeof flags === 'object') {
            featureFlags = { ...featureFlags, ...flags };
            logger.info('Updated feature flags:', featureFlags);
            
            // Store in localStorage for persistence
            try {
                localStorage.setItem('advancedAnalyticsFlags', JSON.stringify(featureFlags));
            } catch (e) {
                logger.warn('Failed to store feature flags in localStorage:', e);
            }
        }
    },
    
    /**
     * Get current feature flags
     * @returns {Object} Current feature flags
     */
    getFeatureFlags: function() {
        return { ...featureFlags };
    },
    
    /**
     * Apply CSS fixes to the legacy implementation table
     * @private
     */
    _applyLegacyTableFixes: function() {
        if (!isFeatureEnabled('useTableFixes')) return;
        
        // Observe the DOM for when the table is created
        const observer = new MutationObserver((mutations) => {
            const yearlyTable = document.getElementById('yearlyOverviewTable');
            if (yearlyTable) {
                // Table found, apply fixes
                
                // 1. Add necessary classes to year headers
                const headerRows = yearlyTable.querySelectorAll('thead tr');
                if (headerRows.length >= 2) {
                    // First row with years
                    headerRows[0].classList.add('year-header');
                    headerRows[0].querySelectorAll('th.year-column').forEach(th => {
                        th.classList.add('year-column-group');
                        
                        // Critical fix: force year headers to NOT be horizontally sticky
                        th.style.left = 'auto !important';
                    });
                    
                    // Second row with months
                    headerRows[1].classList.add('month-header');
                }
                
                // 2. Apply fixed width to cells for consistent alignment
                const styleTag = document.createElement('style');
                styleTag.textContent = `
                    #yearlyOverviewTable td:not(.sticky-col),
                    #yearlyOverviewTable th:not(.sticky-col) {
                        min-width: 60px !important;
                        max-width: 60px !important;
                        width: 60px !important;
                    }
                    
                    /* Ensure sticky columns have higher z-index */
                    #yearlyOverviewTable .sticky-col {
                        position: sticky !important;
                        z-index: 10 !important;
                    }
                    
                    #yearlyOverviewTable .sticky-col-1 {
                        z-index: 20 !important;
                        left: 0 !important;
                    }
                    
                    #yearlyOverviewTable .sticky-col-2 {
                        z-index: 19 !important;
                        left: 100px !important;
                    }
                    
                    /* Ensure corner header cells have highest z-index */
                    #yearlyOverviewTable thead tr:first-child th.sticky-col-1,
                    #yearlyOverviewTable thead tr:first-child th.sticky-col-2 {
                        z-index: 25 !important;
                        position: sticky !important;
                        top: 0 !important;
                        background-color: #fff !important;
                    }
                    
                    #yearlyOverviewTable thead tr:nth-child(2) th.sticky-col-1,
                    #yearlyOverviewTable thead tr:nth-child(2) th.sticky-col-2 {
                        z-index: 24 !important;
                        position: sticky !important;
                        top: 40px !important;
                        background-color: #f8f9fa !important;
                    }
                    
                    /* Critical fix: force year headers to NOT freeze horizontally */
                    #yearlyOverviewTable thead tr:first-child th.year-column {
                        left: auto !important;
                        border-right: 2px solid #aaa;
                        border-bottom: 2px solid #aaa;
                        background-color: #f0f0f8;
                        z-index: 3 !important;
                    }
                    
                    #yearlyOverviewTable tr:not(:first-child) th:not(.sticky-col) {
                        position: sticky !important;
                        top: 40px !important;
                        z-index: 3 !important;
                        background-color: #f8f9fa !important;
                    }
                    
                    /* Year boundary indicator */
                    #yearlyOverviewTable tbody tr td:nth-child(14n+14) {
                        border-right: 2px solid #aaa !important;
                    }
                `;
                document.head.appendChild(styleTag);
                
                // Stop observing once fixes are applied
                observer.disconnect();
                logger.info('Legacy table fixes applied with fix for year header freeze');
            }
        });
        
        // Start observing
        observer.observe(document.body, { 
            childList: true, 
            subtree: true 
        });
    }
};

// Check for auto-initialization
document.addEventListener('DOMContentLoaded', () => {
    const autoInitEl = document.querySelector('[data-auto-init="advanced-analytics"]');
    if (autoInitEl) {
        try {
            const options = JSON.parse(autoInitEl.dataset.options || '{}');
            const contributionData = window.contributionData || [];
            
            if (contributionData.length > 0) {
                const containerId = options.containerId || 'advanced-analytics-container';
                AdvancedAnalyticsMigrator.init(contributionData, containerId);
                
                if (options.autoShow) {
                    setTimeout(() => {
                        AdvancedAnalyticsMigrator.show();
                    }, options.delay || 0);
                }
            }
        } catch (e) {
            logger.error('Error auto-initializing advanced analytics:', e);
        }
    }
});

// Export the migrator
window.AdvancedAnalyticsMigrator = AdvancedAnalyticsMigrator;
export default AdvancedAnalyticsMigrator; 