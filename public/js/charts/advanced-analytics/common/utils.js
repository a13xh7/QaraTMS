/**
 * utils.js
 * Common utility functions used throughout the advanced analytics module
 */

/**
 * Debounce a function call to avoid rapid repeated executions
 * @param {Function} func - The function to debounce
 * @param {number} wait - The milliseconds to wait before executing
 * @param {boolean} [immediate=false] - Whether to execute immediately on first call
 * @returns {Function} Debounced function
 */
export function debounce(func, wait, immediate = false) {
    let timeout;
    
    return function executedFunction(...args) {
        const context = this;
        
        const later = () => {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        
        const callNow = immediate && !timeout;
        
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        
        if (callNow) func.apply(context, args);
    };
}

/**
 * Throttle a function call to limit execution frequency
 * @param {Function} func - The function to throttle
 * @param {number} limit - The milliseconds to limit execution frequency
 * @returns {Function} Throttled function
 */
export function throttle(func, limit) {
    let inThrottle;
    let lastFunc;
    let lastRan;
    
    return function throttledFunction(...args) {
        const context = this;
        
        if (!inThrottle) {
            func.apply(context, args);
            lastRan = Date.now();
            inThrottle = true;
        } else {
            clearTimeout(lastFunc);
            
            lastFunc = setTimeout(() => {
                if (Date.now() - lastRan >= limit) {
                    func.apply(context, args);
                    lastRan = Date.now();
                }
            }, limit - (Date.now() - lastRan));
        }
    };
}

/**
 * Memoize a function to cache results for identical inputs
 * @param {Function} fn - The function to memoize
 * @returns {Function} Memoized function
 */
export function memoize(fn) {
    const cache = new Map();
    
    return function memoizedFunction(...args) {
        const key = JSON.stringify(args);
        
        if (cache.has(key)) {
            return cache.get(key);
        }
        
        const result = fn.apply(this, args);
        cache.set(key, result);
        
        return result;
    };
}

/**
 * Deep clone an object without reference
 * @param {Object} obj - The object to clone
 * @returns {Object} Cloned object
 */
export function deepClone(obj) {
    if (obj === null || typeof obj !== 'object') {
        return obj;
    }
    
    // Handle Date
    if (obj instanceof Date) {
        return new Date(obj.getTime());
    }
    
    // Handle Array
    if (Array.isArray(obj)) {
        return obj.map(item => deepClone(item));
    }
    
    // Handle Object
    const cloned = {};
    for (const key in obj) {
        if (Object.prototype.hasOwnProperty.call(obj, key)) {
            cloned[key] = deepClone(obj[key]);
        }
    }
    
    return cloned;
}

/**
 * Get a safe value from an object with nested path support
 * @param {Object} obj - The object to get value from
 * @param {string} path - The path to the property, using dot notation
 * @param {*} defaultValue - Default value if path doesn't exist
 * @returns {*} The value at path or defaultValue
 */
export function getNestedValue(obj, path, defaultValue = undefined) {
    if (!path || !obj) return defaultValue;
    
    const pathParts = path.split('.');
    let current = obj;
    
    for (const part of pathParts) {
        if (current === null || current === undefined || typeof current !== 'object') {
            return defaultValue;
        }
        
        current = current[part];
    }
    
    return current === undefined ? defaultValue : current;
}

/**
 * Format a date to a standard string
 * @param {Date|string|number} date - The date to format
 * @param {string} [format='full'] - Format type: 'full', 'short', 'year-month'
 * @returns {string} Formatted date string
 */
export function formatDate(date, format = 'full') {
    const d = date instanceof Date ? date : new Date(date);
    
    if (isNaN(d.getTime())) {
        return 'Invalid Date';
    }
    
    switch (format) {
        case 'full':
            return d.toLocaleDateString(undefined, { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
        case 'short':
            return d.toLocaleDateString(undefined, { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        case 'year-month':
            return d.toLocaleDateString(undefined, { 
                year: 'numeric', 
                month: 'long' 
            });
        default:
            return d.toLocaleDateString();
    }
}

/**
 * Sanitize a string for safe HTML insertion to prevent XSS
 * @param {string} str - The string to sanitize
 * @returns {string} Sanitized string safe for insertion
 */
export function sanitizeHTML(str) {
    if (!str) return '';
    
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

/**
 * Check if a value is a number or can be parsed as a number
 * @param {*} value - The value to check
 * @returns {boolean} Whether the value is a number
 */
export function isNumeric(value) {
    return !isNaN(parseFloat(value)) && isFinite(value);
}

/**
 * Calculate percentage change between two values
 * @param {number} currentValue - Current value
 * @param {number} previousValue - Previous value
 * @returns {number} Percentage change
 */
export function calculatePercentChange(currentValue, previousValue) {
    if (!isNumeric(currentValue) || !isNumeric(previousValue)) {
        return 0;
    }
    
    if (previousValue === 0) {
        return currentValue > 0 ? 100 : 0;
    }
    
    return ((currentValue - previousValue) / Math.abs(previousValue)) * 100;
}

/**
 * Memoized function to generate color scale for a metric
 * @param {string} metric - Metric name
 * @param {number} value - The value to generate color for
 * @param {number} maxValue - Maximum value for scaling
 * @param {boolean} isDrop - Whether this is a value drop
 * @param {number} [dropIntensity=0.5] - Drop intensity value 0-1
 * @returns {string} CSS color
 */
export const getColorForMetric = memoize((metric, value, maxValue, isDrop, dropIntensity = 0.5) => {
    // Import from constants to avoid circular dependency
    const COLORS = {
        DROP_OPACITY: 0.8,
        MIN_DROP_INTENSITY: 0.2,
        MAX_INTENSITY: 1.0,
        LIGHT_GRAY: '#f8f9fa',
    };
    
    // Metric colors (normally would be imported from constants)
    const METRIC_COLORS = {
        totalEvents: {
            gradient: ['#e3f2fd', '#90caf9', '#42a5f5', '#1e88e5', '#0d47a1'],
            drop: {r: 255, g: 25, b: 25}
        },
        mrCreated: {
            gradient: ['#e8f5e9', '#a5d6a7', '#66bb6a', '#43a047', '#1b5e20'],
            drop: {r: 255, g: 25, b: 25}
        },
        mrApproved: {
            gradient: ['#f3e5f5', '#ce93d8', '#ab47bc', '#8e24aa', '#4a148c'],
            drop: {r: 255, g: 25, b: 25}
        },
        repoPushes: {
            gradient: ['#fff3e0', '#ffcc80', '#ffa726', '#f57c00', '#e65100'],
            drop: {r: 255, g: 25, b: 25}
        },
    };
    
    if (value === 0) {
        return COLORS.LIGHT_GRAY;
    }
    
    if (isDrop) {
        // Red color for drops with intensity based on dropIntensity
        const r = 255;
        const g = 255 - Math.round(dropIntensity * 230);
        const b = 255 - Math.round(dropIntensity * 230);
        
        return `rgba(${r}, ${g}, ${b}, ${COLORS.DROP_OPACITY})`;
    }
    
    // For positive values, use appropriate color scheme
    const metricConfig = METRIC_COLORS[metric] || METRIC_COLORS.totalEvents;
    const intensity = Math.min(Math.max(value / maxValue, 0.1), COLORS.MAX_INTENSITY);
    const gradientIndex = Math.min(
        Math.floor(intensity * metricConfig.gradient.length), 
        metricConfig.gradient.length - 1
    );
    
    return metricConfig.gradient[gradientIndex];
}, (metric, value, maxValue, isDrop, dropIntensity) => {
    // Cache key generator
    return `${metric}_${value}_${maxValue}_${isDrop}_${dropIntensity}`;
}); 