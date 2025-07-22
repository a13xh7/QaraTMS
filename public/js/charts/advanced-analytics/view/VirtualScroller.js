/**
 * VirtualScroller.js
 * Implements virtual scrolling for large tables to improve performance
 */

import { debounce, throttle } from '../common/utils.js';
import logger from '../common/logger.js';

/**
 * VirtualScroller - Handles efficient rendering of large data sets by only rendering
 * visible elements and a small buffer around them.
 */
class VirtualScroller {
    /**
     * Create a new VirtualScroller instance
     * @param {Object} options - Configuration options
     * @param {HTMLElement} options.container - The scrollable container element
     * @param {HTMLElement} options.content - The content element that will contain rendered items
     * @param {Function} options.renderItem - Function to render a single item (receives item, index)
     * @param {Array} options.items - The data items to render
     * @param {number} [options.itemHeight=30] - Average height of each item in pixels
     * @param {number} [options.overscan=5] - Number of items to render above/below visible area
     * @param {number} [options.batchSize=20] - Number of items to render in each batch
     * @param {boolean} [options.dynamic=false] - Whether items have variable heights
     */
    constructor(options) {
        // Required options
        this.container = options.container;
        this.content = options.content;
        this.renderItem = options.renderItem;
        this.items = options.items || [];
        
        // Optional settings with defaults
        this.itemHeight = options.itemHeight || 30;
        this.overscan = options.overscan || 5;
        this.batchSize = options.batchSize || 20;
        this.dynamic = options.dynamic || false;
        
        // Internal state
        this.visibleStartIndex = 0;
        this.visibleEndIndex = 0;
        this.renderedStartIndex = 0;
        this.renderedEndIndex = 0;
        this.totalHeight = 0;
        this.scrollTop = 0;
        this.renderPromise = null;
        this.resizeObserver = null;
        this.heightCache = new Map();
        this.pendingMeasurements = false;
        
        // Bind methods to preserve context
        this._bindMethods();
        
        // Initialize
        this.initialize();
    }
    
    /**
     * Bind methods to preserve 'this' context
     * @private
     */
    _bindMethods() {
        this.initialize = this.initialize.bind(this);
        this.updateItems = this.updateItems.bind(this);
        this.handleScroll = this.handleScroll.bind(this);
        this.handleResize = this.handleResize.bind(this);
        this.calculateVisibleRange = this.calculateVisibleRange.bind(this);
        this.renderVisibleItems = this.renderVisibleItems.bind(this);
        this.measureItemHeights = this.measureItemHeights.bind(this);
        this.updateTotalHeight = this.updateTotalHeight.bind(this);
        this.cleanup = this.cleanup.bind(this);
    }
    
    /**
     * Initialize the virtual scroller
     */
    initialize() {
        // Set up the container
        if (this.container.style.position !== 'relative' && 
            this.container.style.position !== 'absolute') {
            this.container.style.position = 'relative';
        }
        this.container.style.overflow = 'auto';
        
        // Create spacers for correct scrolling
        this.topSpacer = document.createElement('div');
        this.topSpacer.className = 'virtual-scroller-top-spacer';
        this.topSpacer.style.height = '0px';
        
        this.bottomSpacer = document.createElement('div');
        this.bottomSpacer.className = 'virtual-scroller-bottom-spacer';
        
        // Append spacers
        this.content.parentNode.insertBefore(this.topSpacer, this.content);
        this.content.parentNode.appendChild(this.bottomSpacer);
        
        // Set initial total height
        this.updateTotalHeight();
        
        // Add scroll event listener with throttle
        this.container.addEventListener('scroll', throttle(this.handleScroll, 16)); // ~60fps
        
        // Add resize observer
        this.resizeObserver = new ResizeObserver(debounce(this.handleResize, 100));
        this.resizeObserver.observe(this.container);
        
        // Initial render
        this.calculateVisibleRange();
        this.renderVisibleItems();
        
        logger.debug('VirtualScroller initialized', {
            items: this.items.length,
            container: this.container,
        });
    }
    
    /**
     * Update the data items and re-render
     * @param {Array} items - New data items
     */
    updateItems(items) {
        this.items = items || [];
        this.heightCache.clear();
        this.pendingMeasurements = this.dynamic;
        this.updateTotalHeight();
        this.calculateVisibleRange();
        this.renderVisibleItems(true); // Force re-render
        
        logger.debug('VirtualScroller items updated', {
            itemCount: this.items.length,
        });
    }
    
    /**
     * Handle scroll events
     */
    handleScroll() {
        const newScrollTop = this.container.scrollTop;
        
        // Only recalculate if scroll position has changed enough
        if (Math.abs(newScrollTop - this.scrollTop) > this.itemHeight / 2) {
            this.scrollTop = newScrollTop;
            
            const oldStart = this.visibleStartIndex;
            const oldEnd = this.visibleEndIndex;
            
            this.calculateVisibleRange();
            
            // Only re-render if visible range has changed
            if (oldStart !== this.visibleStartIndex || oldEnd !== this.visibleEndIndex) {
                this.renderVisibleItems();
            }
        }
    }
    
    /**
     * Handle resize events
     */
    handleResize() {
        this.calculateVisibleRange();
        this.renderVisibleItems();
        
        logger.debug('VirtualScroller container resized');
    }
    
    /**
     * Calculate the range of visible items
     */
    calculateVisibleRange() {
        const containerHeight = this.container.clientHeight;
        const scrollTop = this.container.scrollTop;
        
        if (this.dynamic && this.pendingMeasurements) {
            // If we have dynamic heights and they haven't been measured yet,
            // use rough estimation based on average item height
            this.visibleStartIndex = Math.floor(scrollTop / this.itemHeight);
            this.visibleEndIndex = Math.min(
                this.items.length - 1,
                Math.ceil((scrollTop + containerHeight) / this.itemHeight)
            );
        } else if (this.dynamic) {
            // Use cached height measurements for more accurate calculations
            let currentHeight = 0;
            let startIndex = 0;
            
            // Find start index
            for (let i = 0; i < this.items.length; i++) {
                const height = this.heightCache.get(i) || this.itemHeight;
                if (currentHeight + height > scrollTop) {
                    startIndex = i;
                    break;
                }
                currentHeight += height;
            }
            
            // Find end index
            let endIndex = startIndex;
            let heightSum = 0;
            
            while (endIndex < this.items.length && heightSum < containerHeight) {
                heightSum += this.heightCache.get(endIndex) || this.itemHeight;
                endIndex++;
            }
            
            this.visibleStartIndex = Math.max(0, startIndex - this.overscan);
            this.visibleEndIndex = Math.min(this.items.length - 1, endIndex + this.overscan);
        } else {
            // Fixed height items - simple calculation
            this.visibleStartIndex = Math.max(0, Math.floor(scrollTop / this.itemHeight) - this.overscan);
            this.visibleEndIndex = Math.min(
                this.items.length - 1,
                Math.ceil((scrollTop + containerHeight) / this.itemHeight) + this.overscan
            );
        }
    }
    
    /**
     * Render the currently visible items
     * @param {boolean} [force=false] - Whether to force a full render
     */
    renderVisibleItems(force = false) {
        // If another render is in progress, cancel it
        if (this.renderPromise && !force) {
            return;
        }
        
        // Check if we need to render new items
        const startChanged = this.visibleStartIndex !== this.renderedStartIndex;
        const endChanged = this.visibleEndIndex !== this.renderedEndIndex;
        
        if (!force && !startChanged && !endChanged) {
            return;
        }
        
        // Update rendered range
        this.renderedStartIndex = this.visibleStartIndex;
        this.renderedEndIndex = this.visibleEndIndex;
        
        // Clear the content
        while (this.content.firstChild) {
            this.content.removeChild(this.content.firstChild);
        }
        
        // Update spacer heights
        this.updateSpacerHeights();
        
        // Render in batches for smoother UX
        const renderBatch = (start, end) => {
            for (let i = start; i <= end; i++) {
                if (i >= 0 && i < this.items.length) {
                    const item = this.items[i];
                    const element = this.renderItem(item, i);
                    
                    if (element) {
                        this.content.appendChild(element);
                        
                        // Add data attribute to track index
                        element.dataset.virtualIndex = i;
                        
                        // If using dynamic heights, measure this item
                        if (this.dynamic && !this.heightCache.has(i)) {
                            this.pendingMeasurements = true;
                        }
                    }
                }
            }
        };
        
        // Split rendering into batches
        const totalItemsToRender = this.renderedEndIndex - this.renderedStartIndex + 1;
        const batchCount = Math.ceil(totalItemsToRender / this.batchSize);
        
        let batch = 0;
        
        const processBatch = () => {
            if (batch < batchCount) {
                const batchStart = this.renderedStartIndex + (batch * this.batchSize);
                const batchEnd = Math.min(batchStart + this.batchSize - 1, this.renderedEndIndex);
                
                renderBatch(batchStart, batchEnd);
                batch++;
                
                // Schedule next batch
                this.renderPromise = setTimeout(processBatch, 0);
            } else {
                this.renderPromise = null;
                
                // Measure heights if needed
                if (this.dynamic && this.pendingMeasurements) {
                    this.measureItemHeights();
                }
            }
        };
        
        // Start batch processing
        processBatch();
    }
    
    /**
     * Measure actual heights of rendered items
     */
    measureItemHeights() {
        const elements = this.content.children;
        let heightsChanged = false;
        
        for (let i = 0; i < elements.length; i++) {
            const element = elements[i];
            const index = parseInt(element.dataset.virtualIndex, 10);
            
            if (!isNaN(index) && element.offsetHeight > 0) {
                const prevHeight = this.heightCache.get(index) || 0;
                const newHeight = element.offsetHeight;
                
                if (newHeight !== prevHeight) {
                    this.heightCache.set(index, newHeight);
                    heightsChanged = true;
                }
            }
        }
        
        if (heightsChanged) {
            this.updateTotalHeight();
            this.updateSpacerHeights();
        }
        
        this.pendingMeasurements = false;
    }
    
    /**
     * Update spacer heights based on current item positions
     */
    updateSpacerHeights() {
        if (this.renderedStartIndex === 0) {
            this.topSpacer.style.height = '0px';
        } else {
            let topHeight = 0;
            
            if (this.dynamic) {
                // Calculate exact height based on cached values
                for (let i = 0; i < this.renderedStartIndex; i++) {
                    topHeight += this.heightCache.get(i) || this.itemHeight;
                }
            } else {
                // Simple calculation for fixed heights
                topHeight = this.renderedStartIndex * this.itemHeight;
            }
            
            this.topSpacer.style.height = `${topHeight}px`;
        }
        
        if (this.renderedEndIndex >= this.items.length - 1) {
            this.bottomSpacer.style.height = '0px';
        } else {
            let bottomHeight = 0;
            
            if (this.dynamic) {
                // Calculate exact height based on cached values
                for (let i = this.renderedEndIndex + 1; i < this.items.length; i++) {
                    bottomHeight += this.heightCache.get(i) || this.itemHeight;
                }
            } else {
                // Simple calculation for fixed heights
                bottomHeight = (this.items.length - this.renderedEndIndex - 1) * this.itemHeight;
            }
            
            this.bottomSpacer.style.height = `${bottomHeight}px`;
        }
    }
    
    /**
     * Update the total height of all items
     */
    updateTotalHeight() {
        if (this.dynamic) {
            // Calculate from cached heights when available
            let totalHeight = 0;
            
            for (let i = 0; i < this.items.length; i++) {
                totalHeight += this.heightCache.get(i) || this.itemHeight;
            }
            
            this.totalHeight = totalHeight;
        } else {
            // Simple calculation for fixed heights
            this.totalHeight = this.items.length * this.itemHeight;
        }
    }
    
    /**
     * Cleanup event listeners and observers
     */
    cleanup() {
        this.container.removeEventListener('scroll', this.handleScroll);
        
        if (this.resizeObserver) {
            this.resizeObserver.disconnect();
        }
        
        if (this.renderPromise) {
            clearTimeout(this.renderPromise);
            this.renderPromise = null;
        }
        
        // Remove spacers
        if (this.topSpacer && this.topSpacer.parentNode) {
            this.topSpacer.parentNode.removeChild(this.topSpacer);
        }
        
        if (this.bottomSpacer && this.bottomSpacer.parentNode) {
            this.bottomSpacer.parentNode.removeChild(this.bottomSpacer);
        }
        
        logger.debug('VirtualScroller cleaned up');
    }
}

export default VirtualScroller; 