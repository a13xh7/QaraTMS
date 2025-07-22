/**
 * DOMHelper.js
 * Provides utilities for DOM manipulation with caching and convenience methods
 */

import logger from './logger.js';
import { sanitizeHTML } from './utils.js';

/**
 * DOMHelper class with utilities for DOM operations with performance optimizations
 */
class DOMHelper {
    /**
     * Create a new DOMHelper instance
     */
    constructor() {
        // Cache of queried elements to avoid repetitive DOM queries
        this.cache = new Map();
        
        // Map of event listeners for easy cleanup
        this.eventListeners = new Map();
        
        // Cache invalidation timestamp
        this.cacheTimestamp = Date.now();
    }
    
    /**
     * Get an element by ID with caching
     * @param {string} id - Element ID
     * @param {boolean} [skipCache=false] - Whether to skip the cache
     * @returns {HTMLElement|null} The element or null if not found
     */
    getElementById(id, skipCache = false) {
        if (!skipCache && this.cache.has(id)) {
            return this.cache.get(id);
        }
        
        const element = document.getElementById(id);
        
        if (element) {
            this.cache.set(id, element);
        }
        
        return element;
    }
    
    /**
     * Query selector with caching
     * @param {string} selector - CSS selector
     * @param {HTMLElement} [context=document] - Context element to search within
     * @param {boolean} [skipCache=false] - Whether to skip the cache
     * @returns {HTMLElement|null} The element or null if not found
     */
    querySelector(selector, context = document, skipCache = false) {
        const cacheKey = `${context.id || 'doc'}_${selector}`;
        
        if (!skipCache && this.cache.has(cacheKey)) {
            return this.cache.get(cacheKey);
        }
        
        const element = context.querySelector(selector);
        
        if (element) {
            this.cache.set(cacheKey, element);
        }
        
        return element;
    }
    
    /**
     * Query selector all with caching
     * @param {string} selector - CSS selector
     * @param {HTMLElement} [context=document] - Context element to search within
     * @param {boolean} [skipCache=false] - Whether to skip the cache
     * @returns {Array<HTMLElement>} Array of matched elements
     */
    querySelectorAll(selector, context = document, skipCache = false) {
        const cacheKey = `${context.id || 'doc'}_all_${selector}`;
        
        if (!skipCache && this.cache.has(cacheKey)) {
            return [...this.cache.get(cacheKey)];
        }
        
        const elements = Array.from(context.querySelectorAll(selector));
        this.cache.set(cacheKey, elements);
        
        return elements;
    }
    
    /**
     * Create an HTML element with attributes and content
     * @param {string} tagName - The tag name
     * @param {Object} [attributes={}] - Attributes to set
     * @param {string|HTMLElement|Array} [content=''] - Inner content 
     * @returns {HTMLElement} The created element
     */
    createElement(tagName, attributes = {}, content = '') {
        const element = document.createElement(tagName);
        
        // Set attributes
        Object.entries(attributes).forEach(([key, value]) => {
            if (key === 'className' || key === 'class') {
                element.className = value;
            } else if (key === 'style' && typeof value === 'object') {
                Object.entries(value).forEach(([prop, val]) => {
                    element.style[prop] = val;
                });
            } else if (key.startsWith('data-')) {
                element.setAttribute(key, value);
            } else if (key === 'dataset' && typeof value === 'object') {
                Object.entries(value).forEach(([dataKey, dataVal]) => {
                    element.dataset[dataKey] = dataVal;
                });
            } else {
                element[key] = value;
            }
        });
        
        // Set content
        if (content) {
            if (typeof content === 'string') {
                element.textContent = content;
            } else if (content instanceof HTMLElement) {
                element.appendChild(content);
            } else if (Array.isArray(content)) {
                content.forEach(item => {
                    if (typeof item === 'string') {
                        const textNode = document.createTextNode(item);
                        element.appendChild(textNode);
                    } else if (item instanceof HTMLElement) {
                        element.appendChild(item);
                    }
                });
            }
        }
        
        return element;
    }
    
    /**
     * Set HTML content safely (preventing XSS)
     * @param {HTMLElement} element - The target element
     * @param {string} htmlContent - The HTML content to set
     */
    setHTML(element, htmlContent) {
        if (!element) return;
        
        // Sanitize the HTML content
        const safeHTML = sanitizeHTML(htmlContent);
        element.innerHTML = safeHTML;
    }
    
    /**
     * Set text content
     * @param {HTMLElement} element - The target element
     * @param {string} text - The text content to set
     */
    setText(element, text) {
        if (!element) return;
        element.textContent = text;
    }
    
    /**
     * Add an event listener with tracking for easy cleanup
     * @param {HTMLElement} element - The target element
     * @param {string} eventType - Event type
     * @param {Function} handler - Event handler function
     * @param {Object} [options] - Event listener options
     */
    addEventListener(element, eventType, handler, options) {
        if (!element) return;
        
        // Add the event listener
        element.addEventListener(eventType, handler, options);
        
        // Track it for cleanup
        const eventKey = `${element.id || 'anonymous'}_${eventType}_${handler.name || 'anonymous'}`;
        this.eventListeners.set(eventKey, { element, eventType, handler });
        
        return eventKey;
    }
    
    /**
     * Remove an event listener by key or specific parameters
     * @param {string|Object} keyOrParams - Event key or parameters object
     */
    removeEventListener(keyOrParams) {
        if (typeof keyOrParams === 'string') {
            // Remove by key
            if (this.eventListeners.has(keyOrParams)) {
                const { element, eventType, handler } = this.eventListeners.get(keyOrParams);
                element.removeEventListener(eventType, handler);
                this.eventListeners.delete(keyOrParams);
            }
        } else {
            // Remove by params
            const { element, eventType, handler } = keyOrParams;
            if (element && eventType && handler) {
                element.removeEventListener(eventType, handler);
                
                // Find and remove from tracked listeners
                this.eventListeners.forEach((data, key) => {
                    if (data.element === element && 
                        data.eventType === eventType && 
                        data.handler === handler) {
                        this.eventListeners.delete(key);
                    }
                });
            }
        }
    }
    
    /**
     * Clear all tracked event listeners
     */
    clearEventListeners() {
        this.eventListeners.forEach(({ element, eventType, handler }) => {
            element.removeEventListener(eventType, handler);
        });
        
        this.eventListeners.clear();
    }
    
    /**
     * Add/remove a class based on condition
     * @param {HTMLElement} element - The target element
     * @param {string} className - Class to toggle
     * @param {boolean} condition - Whether to add or remove
     */
    toggleClass(element, className, condition) {
        if (!element) return;
        
        if (condition) {
            element.classList.add(className);
        } else {
            element.classList.remove(className);
        }
    }
    
    /**
     * Empty the contents of an element
     * @param {HTMLElement} element - The element to empty
     */
    empty(element) {
        if (!element) return;
        
        while (element.firstChild) {
            element.removeChild(element.firstChild);
        }
    }
    
    /**
     * Remove an element from the DOM
     * @param {HTMLElement} element - The element to remove
     */
    remove(element) {
        if (!element || !element.parentNode) return;
        element.parentNode.removeChild(element);
    }
    
    /**
     * Set multiple style properties on an element
     * @param {HTMLElement} element - The target element
     * @param {Object} styles - Object with style properties
     */
    setStyles(element, styles) {
        if (!element || !styles) return;
        
        Object.entries(styles).forEach(([property, value]) => {
            element.style[property] = value;
        });
    }
    
    /**
     * Calculate element position relative to the document
     * @param {HTMLElement} element - The element
     * @returns {Object} Position with top, left, right, bottom
     */
    getElementPosition(element) {
        if (!element) return { top: 0, left: 0, right: 0, bottom: 0 };
        
        const rect = element.getBoundingClientRect();
        const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        return {
            top: rect.top + scrollTop,
            left: rect.left + scrollLeft,
            right: rect.right + scrollLeft,
            bottom: rect.bottom + scrollTop,
            width: rect.width,
            height: rect.height
        };
    }
    
    /**
     * Check if an element is in the viewport
     * @param {HTMLElement} element - The element to check
     * @param {number} [offset=0] - Offset to use when determining visibility
     * @returns {boolean} Whether the element is in viewport
     */
    isInViewport(element, offset = 0) {
        if (!element) return false;
        
        const rect = element.getBoundingClientRect();
        
        return (
            rect.top >= 0 - offset &&
            rect.left >= 0 - offset &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) + offset &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth) + offset
        );
    }
    
    /**
     * Get all form input values from a form
     * @param {HTMLFormElement} form - The form element
     * @returns {Object} Form values as an object
     */
    getFormValues(form) {
        if (!form || !(form instanceof HTMLFormElement)) {
            return {};
        }
        
        const formData = new FormData(form);
        const values = {};
        
        for (const [key, value] of formData.entries()) {
            values[key] = value;
        }
        
        return values;
    }
    
    /**
     * Invalidate the element cache
     */
    invalidateCache() {
        this.cache.clear();
        this.cacheTimestamp = Date.now();
        logger.debug('DOM cache invalidated');
    }
    
    /**
     * Clean up all resources
     */
    cleanup() {
        this.clearEventListeners();
        this.invalidateCache();
    }
}

// Export a singleton instance
export default new DOMHelper(); 