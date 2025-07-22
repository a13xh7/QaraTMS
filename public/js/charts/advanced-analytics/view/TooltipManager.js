/**
 * TooltipManager.js
 * Optimizes tooltip initialization using IntersectionObserver
 */

import logger from '../common/logger.js';
import { TOOLTIP_BATCH_SIZE, TOOLTIP_INIT_DELAY } from '../common/constants.js';

/**
 * TooltipManager - Lazily initializes tooltips as they enter the viewport
 * using IntersectionObserver for improved performance.
 */
class TooltipManager {
    /**
     * Create a new TooltipManager
     * @param {Object} options - Configuration options
     * @param {string} [options.selector='[data-bs-toggle="tooltip"]'] - Selector for tooltip elements
     * @param {Object} [options.tooltipOptions={}] - Options to pass to Bootstrap Tooltip
     * @param {string} [options.rootMargin='100px'] - Root margin for IntersectionObserver
     * @param {number} [options.batchSize=20] - Number of tooltips to initialize in each batch
     * @param {number} [options.batchDelay=50] - Delay between batches in milliseconds
     */
    constructor(options = {}) {
        this.selector = options.selector || '[data-bs-toggle="tooltip"]';
        this.tooltipOptions = options.tooltipOptions || {
            container: 'body',
            html: true,
            trigger: 'hover focus',
            delay: { show: 200, hide: 100 }
        };
        this.rootMargin = options.rootMargin || '100px';
        this.batchSize = options.batchSize || TOOLTIP_BATCH_SIZE;
        this.batchDelay = options.batchDelay || TOOLTIP_INIT_DELAY / 10;
        
        // Internal state
        this.observer = null;
        this.observedElements = new Map();  // Map of element -> { initialized, tooltip }
        this.initQueue = [];
        this.processingQueue = false;
        
        // Initialize
        this.initialize();
    }
    
    /**
     * Initialize the TooltipManager
     */
    initialize() {
        // Check if IntersectionObserver is available
        if (!window.IntersectionObserver) {
            logger.warn('IntersectionObserver not supported, falling back to standard initialization');
            this.initFallback();
            return;
        }
        
        // Create observer
        this.observer = new IntersectionObserver(this.handleIntersection.bind(this), {
            rootMargin: this.rootMargin,
            threshold: 0.1
        });
        
        logger.debug('TooltipManager initialized with IntersectionObserver');
    }
    
    /**
     * Handle intersection events
     * @param {IntersectionObserverEntry[]} entries - Intersection entries
     */
    handleIntersection(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                this.queueElementForInitialization(entry.target);
                this.observer.unobserve(entry.target);
            }
        });
        
        // Process the queue if not already processing
        if (this.initQueue.length > 0 && !this.processingQueue) {
            this.processInitQueue();
        }
    }
    
    /**
     * Queue an element for tooltip initialization
     * @param {HTMLElement} element - Element to initialize tooltip on
     */
    queueElementForInitialization(element) {
        const elementInfo = this.observedElements.get(element);
        
        if (!elementInfo || !elementInfo.initialized) {
            this.initQueue.push(element);
        }
    }
    
    /**
     * Process the queue of elements waiting for tooltip initialization
     */
    processInitQueue() {
        if (this.initQueue.length === 0) {
            this.processingQueue = false;
            return;
        }
        
        this.processingQueue = true;
        
        // Take a batch from the queue
        const batchSize = Math.min(this.batchSize, this.initQueue.length);
        const batch = this.initQueue.splice(0, batchSize);
        
        // Initialize tooltips for this batch
        batch.forEach(element => {
            this.initializeTooltip(element);
        });
        
        // Schedule next batch if there are more elements
        if (this.initQueue.length > 0) {
            setTimeout(() => this.processInitQueue(), this.batchDelay);
        } else {
            this.processingQueue = false;
        }
    }
    
    /**
     * Initialize tooltip on an element
     * @param {HTMLElement} element - Element to initialize tooltip on
     */
    initializeTooltip(element) {
        try {
            if (!element) return;
            
            // Skip if already initialized
            if (
                this.observedElements.has(element) && 
                this.observedElements.get(element).initialized
            ) {
                return;
            }
            
            // Create the tooltip
            if (window.bootstrap && window.bootstrap.Tooltip) {
                const tooltip = new window.bootstrap.Tooltip(element, this.tooltipOptions);
                
                // Store reference
                this.observedElements.set(element, {
                    initialized: true,
                    tooltip: tooltip
                });
            }
        } catch (error) {
            logger.error('Error initializing tooltip', error);
        }
    }
    
    /**
     * Fallback method when IntersectionObserver is not available
     */
    initFallback() {
        // Get all tooltip elements
        const tooltipElements = document.querySelectorAll(this.selector);
        
        if (tooltipElements.length === 0) {
            return;
        }
        
        logger.info(`Initializing ${tooltipElements.length} tooltips with fallback method`);
        
        // Split into batches
        const batchCount = Math.ceil(tooltipElements.length / this.batchSize);
        
        for (let i = 0; i < batchCount; i++) {
            const start = i * this.batchSize;
            const end = Math.min(start + this.batchSize, tooltipElements.length);
            
            // Use setTimeout to spread out the initialization
            setTimeout(() => {
                for (let j = start; j < end; j++) {
                    if (window.bootstrap && window.bootstrap.Tooltip) {
                        try {
                            new window.bootstrap.Tooltip(tooltipElements[j], this.tooltipOptions);
                        } catch (error) {
                            logger.error('Error initializing tooltip in fallback', error);
                        }
                    }
                }
                
                logger.debug(`Initialized tooltips batch ${i + 1}/${batchCount}`);
            }, i * this.batchDelay);
        }
    }
    
    /**
     * Observe elements matching the selector within a container
     * @param {HTMLElement} container - Container element to search within
     */
    observeContainer(container) {
        if (!container) {
            logger.warn('Cannot observe tooltips: No container provided');
            return;
        }
        
        if (!this.observer) {
            this.initFallback();
            return;
        }
        
        // Find all tooltip elements in the container
        const tooltipElements = container.querySelectorAll(this.selector);
        
        if (tooltipElements.length === 0) {
            logger.debug('No tooltip elements found in container');
            return;
        }
        
        logger.debug(`Found ${tooltipElements.length} tooltip elements to observe`);
        
        // Observe each element
        tooltipElements.forEach(element => {
            // Skip if already observed
            if (this.observedElements.has(element)) {
                return;
            }
            
            // Track the element
            this.observedElements.set(element, {
                initialized: false,
                tooltip: null
            });
            
            // Start observing
            this.observer.observe(element);
        });
    }
    
    /**
     * Force initialization of all tooltips without waiting for intersection
     * @param {HTMLElement} [container=document] - Container to search within
     */
    initializeAll(container = document) {
        // Find all tooltip elements
        const tooltipElements = container.querySelectorAll(this.selector);
        
        if (tooltipElements.length === 0) {
            return;
        }
        
        logger.info(`Force initializing ${tooltipElements.length} tooltips`);
        
        // Add all to the queue
        Array.from(tooltipElements).forEach(element => {
            this.queueElementForInitialization(element);
        });
        
        // Start processing
        if (!this.processingQueue) {
            this.processInitQueue();
        }
    }
    
    /**
     * Refresh tooltips after DOM updates
     * @param {HTMLElement} [container=document] - Container to refresh tooltips in
     */
    refresh(container = document) {
        // Find all tooltip elements
        const tooltipElements = container.querySelectorAll(this.selector);
        
        if (tooltipElements.length === 0) {
            return;
        }
        
        logger.debug(`Refreshing tooltips in container`);
        
        // Temporarily stop observing and clear queues
        if (this.observer) {
            this.observer.disconnect();
        }
        
        this.initQueue = [];
        this.processingQueue = false;
        
        // Clean up existing tooltips if any
        this.observedElements.forEach((info, element) => {
            if (info.initialized && info.tooltip && typeof info.tooltip.dispose === 'function') {
                try {
                    info.tooltip.dispose();
                } catch (error) {
                    // Ignore disposal errors
                }
            }
        });
        
        this.observedElements.clear();
        
        // Start observing again
        this.observeContainer(container);
    }
    
    /**
     * Cleanup and dispose all tooltips
     */
    dispose() {
        // Stop observing
        if (this.observer) {
            this.observer.disconnect();
            this.observer = null;
        }
        
        // Clear queues
        this.initQueue = [];
        this.processingQueue = false;
        
        // Dispose all tooltips
        this.observedElements.forEach((info, element) => {
            if (info.initialized && info.tooltip && typeof info.tooltip.dispose === 'function') {
                try {
                    info.tooltip.dispose();
                } catch (error) {
                    // Ignore disposal errors
                }
            }
        });
        
        this.observedElements.clear();
        
        logger.debug('TooltipManager disposed');
    }
}

export default TooltipManager; 