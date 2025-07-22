/**
 * logger.js
 * Centralized logging utility to standardize logs and control output
 */

// Log levels
const LOG_LEVELS = {
    ERROR: 0,
    WARN: 1,
    INFO: 2,
    DEBUG: 3,
    TRACE: 4
};

// Logger configuration
const config = {
    // Current log level - can be changed at runtime
    level: LOG_LEVELS.INFO,
    
    // Whether to include timestamps
    showTimestamp: true,
    
    // Whether to include the module name
    showModule: true,
    
    // Production mode disables all but ERROR and WARN
    isProduction: window.location.hostname !== 'localhost' && 
                 !window.location.hostname.includes('dev') &&
                 !window.location.hostname.includes('staging')
};

// Set production mode automatically if not on a development domain
if (config.isProduction) {
    config.level = LOG_LEVELS.WARN;
}

/**
 * Logger class for consistent logging
 */
class Logger {
    /**
     * Create a new logger instance
     * @param {string} moduleName - Name of the module using this logger
     */
    constructor(moduleName = 'AdvancedAnalytics') {
        this.moduleName = moduleName;
    }
    
    /**
     * Format a log message with optional timestamp and module name
     * @private
     * @param {string} message - The message to format
     * @returns {string} Formatted message
     */
    _formatMessage(message) {
        const parts = [];
        
        if (config.showTimestamp) {
            parts.push(`[${new Date().toISOString()}]`);
        }
        
        if (config.showModule) {
            parts.push(`[${this.moduleName}]`);
        }
        
        parts.push(message);
        return parts.join(' ');
    }
    
    /**
     * Log an error message (always displayed)
     * @param {string} message - The error message
     * @param {Error|Object} [error] - Optional error object
     */
    error(message, error) {
        if (config.level >= LOG_LEVELS.ERROR) {
            console.error(this._formatMessage(message), error || '');
        }
    }
    
    /**
     * Log a warning message
     * @param {string} message - The warning message
     * @param {Object} [data] - Optional data to include
     */
    warn(message, data) {
        if (config.level >= LOG_LEVELS.WARN) {
            console.warn(this._formatMessage(message), data || '');
        }
    }
    
    /**
     * Log an info message
     * @param {string} message - The info message
     * @param {Object} [data] - Optional data to include
     */
    info(message, data) {
        if (config.level >= LOG_LEVELS.INFO) {
            console.log(this._formatMessage(message), data || '');
        }
    }
    
    /**
     * Log a debug message (more detailed than info)
     * @param {string} message - The debug message
     * @param {Object} [data] - Optional data to include
     */
    debug(message, data) {
        if (config.level >= LOG_LEVELS.DEBUG) {
            console.log(this._formatMessage(`DEBUG: ${message}`), data || '');
        }
    }
    
    /**
     * Log a trace message (most detailed level)
     * @param {string} message - The trace message
     * @param {Object} [data] - Optional data to include
     */
    trace(message, data) {
        if (config.level >= LOG_LEVELS.TRACE) {
            console.log(this._formatMessage(`TRACE: ${message}`), data || '');
        }
    }
    
    /**
     * Log the time taken for a function to execute
     * @param {string} label - Label for the timing
     * @param {Function} fn - Function to time
     * @returns {*} The result of the function
     */
    time(label, fn) {
        if (config.level >= LOG_LEVELS.DEBUG) {
            console.time(this._formatMessage(label));
            const result = fn();
            console.timeEnd(this._formatMessage(label));
            return result;
        } else {
            return fn();
        }
    }
    
    /**
     * Group related log messages together
     * @param {string} label - Group label
     * @param {Function} fn - Function containing grouped logs
     */
    group(label, fn) {
        if (config.level >= LOG_LEVELS.INFO) {
            console.group(this._formatMessage(label));
            fn();
            console.groupEnd();
        } else {
            fn();
        }
    }
}

/**
 * Set the current log level
 * @param {number} level - Log level to set
 */
export function setLogLevel(level) {
    if (Object.values(LOG_LEVELS).includes(level)) {
        config.level = level;
    }
}

/**
 * Enable or disable timestamps in logs
 * @param {boolean} show - Whether to show timestamps
 */
export function showTimestamps(show) {
    config.showTimestamp = !!show;
}

/**
 * Get a logger instance for a specific module
 * @param {string} moduleName - Name of the module
 * @returns {Logger} Logger instance
 */
export function getLogger(moduleName) {
    return new Logger(moduleName);
}

// Export log levels for external use
export { LOG_LEVELS };

// Default logger
export default new Logger(); 