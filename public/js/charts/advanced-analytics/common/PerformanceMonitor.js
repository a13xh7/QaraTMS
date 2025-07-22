/**
 * PerformanceMonitor.js
 * Utility for tracking and measuring performance of key operations
 */

import logger from './logger.js';

// Map of current measurements in progress
const activeMeasurements = new Map();

// History of completed measurements
const measurementHistory = {
    rendering: [],
    dataProcessing: [],
    events: [],
    other: []
};

// Maximum history items per category
const MAX_HISTORY_ITEMS = 50;

/**
 * Start a performance measurement
 * @param {string} name - Name of the operation to measure
 * @param {string} [category='other'] - Category of the operation
 * @returns {string} The measurement ID
 */
export function startMeasurement(name, category = 'other') {
    const id = `${category}_${name}_${Date.now()}`;
    
    // Create measurement entry
    activeMeasurements.set(id, {
        id,
        name,
        category,
        startTime: performance.now(),
        marksAdded: []
    });
    
    // Add a mark to the performance timeline if available
    if (window.performance && window.performance.mark) {
        const markName = `start_${id}`;
        window.performance.mark(markName);
        activeMeasurements.get(id).marksAdded.push(markName);
    }
    
    return id;
}

/**
 * Add a mark in the middle of a measurement
 * @param {string} id - Measurement ID
 * @param {string} markName - Name of the mark
 */
export function addMark(id, markName) {
    if (!activeMeasurements.has(id)) return;
    
    const measurement = activeMeasurements.get(id);
    const fullMarkName = `${id}_${markName}`;
    
    if (window.performance && window.performance.mark) {
        window.performance.mark(fullMarkName);
        measurement.marksAdded.push(fullMarkName);
    }
    
    // Add elapsed time to the measurement object
    measurement[markName] = performance.now() - measurement.startTime;
}

/**
 * End a performance measurement
 * @param {string} id - Measurement ID
 * @param {Object} [additionalData={}] - Additional data to store with the measurement
 * @returns {Object} The completed measurement
 */
export function endMeasurement(id, additionalData = {}) {
    if (!activeMeasurements.has(id)) {
        return null;
    }
    
    const endTime = performance.now();
    const measurement = activeMeasurements.get(id);
    const duration = endTime - measurement.startTime;
    
    // Add end mark to the performance timeline if available
    if (window.performance && window.performance.mark) {
        const endMarkName = `end_${id}`;
        window.performance.mark(endMarkName);
        measurement.marksAdded.push(endMarkName);
        
        // Create performance measure if supported
        if (window.performance.measure) {
            try {
                window.performance.measure(
                    `${measurement.name}_duration`,
                    `start_${id}`,
                    endMarkName
                );
            } catch (e) {
                // Some browsers may throw if marks aren't found
                logger.debug('Error creating performance measure', e);
            }
        }
    }
    
    // Complete the measurement
    const completedMeasurement = {
        ...measurement,
        ...additionalData,
        endTime,
        duration
    };
    
    // Store in history
    const category = measurement.category;
    if (measurementHistory[category]) {
        measurementHistory[category].push(completedMeasurement);
        
        // Trim history if needed
        if (measurementHistory[category].length > MAX_HISTORY_ITEMS) {
            measurementHistory[category].shift();
        }
    } else {
        measurementHistory.other.push(completedMeasurement);
        
        // Trim history if needed
        if (measurementHistory.other.length > MAX_HISTORY_ITEMS) {
            measurementHistory.other.shift();
        }
    }
    
    // Log slow operations
    if (duration > 100) {
        logger.debug(`Slow operation detected: ${measurement.name} took ${Math.round(duration)}ms`, completedMeasurement);
    }
    
    // Remove from active measurements
    activeMeasurements.delete(id);
    
    return completedMeasurement;
}

/**
 * Wrap a function with performance measurement
 * @param {Function} fn - Function to measure
 * @param {string} name - Name of the operation
 * @param {string} [category='other'] - Category of the operation
 * @returns {Function} Wrapped function
 */
export function measureFunction(fn, name, category = 'other') {
    return function measuredFunction(...args) {
        const id = startMeasurement(name, category);
        try {
            const result = fn.apply(this, args);
            
            // Handle promises
            if (result instanceof Promise) {
                return result.then(value => {
                    endMeasurement(id, { args });
                    return value;
                }).catch(error => {
                    endMeasurement(id, { args, error: error.message });
                    throw error;
                });
            }
            
            endMeasurement(id, { args });
            return result;
        } catch (error) {
            endMeasurement(id, { args, error: error.message });
            throw error;
        }
    };
}

/**
 * Get statistics for a category of measurements
 * @param {string} [category='rendering'] - Category to get stats for
 * @returns {Object} Statistics for the category
 */
export function getStatistics(category = 'rendering') {
    const measurements = measurementHistory[category] || [];
    
    if (measurements.length === 0) {
        return {
            count: 0,
            average: 0,
            min: 0,
            max: 0,
            median: 0,
            p95: 0,
            total: 0
        };
    }
    
    // Extract durations and sort
    const durations = measurements.map(m => m.duration).sort((a, b) => a - b);
    
    // Calculate statistics
    const count = durations.length;
    const min = durations[0];
    const max = durations[count - 1];
    const total = durations.reduce((sum, duration) => sum + duration, 0);
    const average = total / count;
    const median = durations[Math.floor(count / 2)];
    const p95 = durations[Math.floor(count * 0.95)];
    
    return {
        count,
        average,
        min,
        max,
        median,
        p95,
        total
    };
}

/**
 * Get all measurements for a category
 * @param {string} [category] - Category to get measurements for, or all if omitted
 * @returns {Array} Array of measurements
 */
export function getMeasurements(category) {
    if (category && measurementHistory[category]) {
        return [...measurementHistory[category]];
    }
    
    // Return all measurements if no category specified
    return Object.values(measurementHistory).flat();
}

/**
 * Clear measurement history
 * @param {string} [category] - Category to clear, or all if omitted
 */
export function clearMeasurements(category) {
    if (category && measurementHistory[category]) {
        measurementHistory[category] = [];
    } else {
        Object.keys(measurementHistory).forEach(key => {
            measurementHistory[key] = [];
        });
    }
}

export default {
    startMeasurement,
    addMark,
    endMeasurement,
    measureFunction,
    getStatistics,
    getMeasurements,
    clearMeasurements
}; 