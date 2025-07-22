/**
 * DataTransformer.js
 * Utility for transforming contribution data without mutating original objects
 */

import logger from '../common/logger.js';
import { deepClone } from '../common/utils.js';

/**
 * Process raw contribution data into a format suitable for yearly view rendering
 * @param {Array<import('../types').ContributionData>} contributionData - Raw contribution data
 * @returns {Array<import('../types').Contributor>} Processed contributor data objects
 */
function processDataForYearlyView(contributionData) {
    if (!Array.isArray(contributionData) || contributionData.length === 0) {
        logger.warn('No contribution data provided');
        return [];
    }
    
    // Create a map to group by contributor name
    const contributorMap = new Map();
    
    // Process each data point
    contributionData.forEach(item => {
        // Create deep clones to avoid mutations
        const dataPoint = deepClone(item);
        
        // Normalize values
        const year = parseInt(dataPoint.year, 10);
        const month = parseInt(dataPoint.month, 10);
        const totalEvents = parseInt(dataPoint.totalEvents, 10) || 0;
        const mrCreated = parseInt(dataPoint.mrCreated, 10) || 0;
        const mrApproved = parseInt(dataPoint.mrApproved, 10) || 0;
        const repoPushes = parseInt(dataPoint.repoPushes, 10) || 0;
        
        // Skip invalid data
        if (isNaN(year) || isNaN(month) || month < 1 || month > 12) {
            logger.warn('Invalid data point skipped', dataPoint);
            return;
        }
        
        const name = dataPoint.name || 'Unknown';
        const squad = dataPoint.squad || 'Unknown';
        
        // Get or create contributor entry
        if (!contributorMap.has(name)) {
            contributorMap.set(name, {
                name,
                squad,
                data: {},
                active: dataPoint.active !== false // Default to true if not specified
            });
        }
        
        const contributor = contributorMap.get(name);
        
        // Update squad if different (use most recent)
        if (contributor.squad !== squad) {
            contributor.squad = squad;
        }
        
        // Ensure year and month objects exist
        if (!contributor.data[year]) {
            contributor.data[year] = {};
        }
        
        if (!contributor.data[year][month]) {
            contributor.data[year][month] = {};
        }
        
        // Store the metrics
        contributor.data[year][month] = {
            totalEvents,
            mrCreated,
            mrApproved,
            repoPushes
        };
    });
    
    // Convert map to array
    return Array.from(contributorMap.values());
}

/**
 * Get all unique years from the processed data
 * @param {Array<import('../types').Contributor>} processedData - Processed contributor data
 * @returns {Array<number>} Array of years sorted in ascending order
 */
function getAllYears(processedData) {
    if (!Array.isArray(processedData) || processedData.length === 0) {
        return [];
    }
    
    const yearsSet = new Set();
    
    processedData.forEach(contributor => {
        if (contributor.data) {
            Object.keys(contributor.data).forEach(year => {
                yearsSet.add(parseInt(year, 10));
            });
        }
    });
    
    return Array.from(yearsSet).sort((a, b) => a - b);
}

/**
 * Mark inactive periods for contributors
 * @param {Array<import('../types').Contributor>} processedData - Processed contributor data
 * @param {Array<number>} years - Years to check
 * @returns {Array<import('../types').Contributor>} Updated contributor data with inactive periods
 */
function markInactivePeriods(processedData, years) {
    if (!Array.isArray(processedData) || processedData.length === 0) {
        return [];
    }
    
    // Create a deep copy to avoid mutating the original
    const result = deepClone(processedData);
    
    result.forEach(contributor => {
        // Initialize inactivePeriods array if not exists
        if (!contributor.inactivePeriods) {
            contributor.inactivePeriods = [];
        }
        
        // Check each year/month combination
        years.forEach(year => {
            for (let month = 1; month <= 12; month++) {
                // Mark as inactive if no data for this month and the contributor has data in other months
                if (
                    contributor.data && 
                    Object.keys(contributor.data).length > 0 && 
                    (!contributor.data[year] || !contributor.data[year][month])
                ) {
                    contributor.inactivePeriods.push({ year, month });
                }
            }
        });
    });
    
    return result;
}

/**
 * Get maximum value for a given metric across all contributors
 * @param {Array<import('../types').Contributor>} processedData - Processed contributor data
 * @param {string} metric - Metric name
 * @returns {number} Maximum value
 */
function getMaxValueForMetric(processedData, metric) {
    if (!Array.isArray(processedData) || processedData.length === 0) {
        return 0;
    }
    
    let maxValue = 0;
    
    processedData.forEach(contributor => {
        if (!contributor.data) return;
        
        Object.values(contributor.data).forEach(yearData => {
            Object.values(yearData).forEach(monthData => {
                if (monthData[metric] && monthData[metric] > maxValue) {
                    maxValue = monthData[metric];
                }
            });
        });
    });
    
    return maxValue;
}

export default {
    processDataForYearlyView,
    getAllYears,
    markInactivePeriods,
    getMaxValueForMetric
}; 