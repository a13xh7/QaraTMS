/**
 * ViewRendererHelpers.js
 * Helper functions for ViewRenderer component to break down long methods
 */

import { 
    COLORS, 
    CSS_CLASSES, 
    METRIC_DISPLAY_NAMES, 
    MONTH_NAMES,
    METRIC_COLORS 
} from '../common/constants.js';
import DOMHelper from '../common/DOMHelper.js';
import { sanitizeHTML, calculatePercentChange, isNumeric, getColorForMetric } from '../common/utils.js';
import logger from '../common/logger.js';
import TooltipBuilder from './TooltipBuilder.js';

/**
 * Calculate trend information from current and previous values
 * @param {number} current - Current value
 * @param {number} previous - Previous value
 * @returns {Object} Trend object with direction and percentage
 */
export function calculateTrend(current, previous) {
    if (!isNumeric(current) || !isNumeric(previous)) {
        return { direction: 'neutral', percentage: 0 };
    }
    
    if (previous === 0) {
        return { 
            direction: current > 0 ? 'up' : 'neutral', 
            percentage: current > 0 ? 100 : 0 
        };
    }
    
    const percentChange = calculatePercentChange(current, previous);
    const direction = percentChange > 0 ? 'up' : percentChange < 0 ? 'down' : 'neutral';
    
    return {
        direction,
        percentage: Math.round(Math.abs(percentChange))
    };
}

/**
 * Get color for cell based on value and metric
 * @param {number} value - Cell value
 * @param {number} maxValue - Maximum value for scaling
 * @param {string} metric - Metric type
 * @param {boolean} isSignificantDrop - Whether this is a significant drop
 * @param {number} dropIntensity - Drop intensity value 0-1
 * @returns {string} CSS color
 */
export function getHeatmapColor(value, maxValue, metric, isSignificantDrop, dropIntensity = 0.5) {
    // Use the memoized function for better performance
    return getColorForMetric(metric, value, maxValue, isSignificantDrop, dropIntensity);
}

/**
 * Get metric display name
 * @param {string} metric - Metric key
 * @returns {string} Display name
 */
export function getMetricDisplayName(metric) {
    return METRIC_DISPLAY_NAMES[metric] || metric;
}

/**
 * Create tooltip content for a cell
 * @param {Object} data - Data for tooltip
 * @returns {string} HTML content for tooltip
 */
export function createTooltipContent(data) {
    return TooltipBuilder.buildTooltipHTML({
        contributor: data.contributor,
        year: data.year,
        month: data.month,
        metric: data.metric,
        metricDisplayName: getMetricDisplayName(data.metric),
        value: data.value,
        prevValue: data.prevValue,
        trend: data.trend
    });
}

/**
 * Check if a date is in the future
 * @param {number} year - Year
 * @param {number} month - Month (1-12)
 * @returns {boolean} Whether date is in the future
 */
export function isDateInFuture(year, month) {
    const now = new Date();
    const currentYear = now.getFullYear();
    const currentMonth = now.getMonth() + 1; // JS months are 0-based
    
    return (year > currentYear) || 
           (year === currentYear && month > currentMonth);
}

/**
 * Get previous month/year values
 * @param {number} year - Current year
 * @param {number} month - Current month (1-12)
 * @returns {Object} Previous month and year
 */
export function getPreviousMonth(year, month) {
    if (month === 1) {
        return { year: year - 1, month: 12 };
    }
    return { year, month: month - 1 };
}

/**
 * Get contributor value for specific year/month/metric
 * @param {Object} contributor - Contributor data object
 * @param {number} year - Year
 * @param {number} month - Month (1-12)
 * @param {string} metric - Metric name
 * @returns {number} Value for the metric
 */
export function getContributorValue(contributor, year, month, metric) {
    try {
        if (
            contributor.data && 
            contributor.data[year] && 
            contributor.data[year][month] && 
            contributor.data[year][month][metric] !== undefined
        ) {
            return parseInt(contributor.data[year][month][metric], 10) || 0;
        }
    } catch (error) {
        logger.error('Error getting contributor value', { contributor, year, month, metric });
    }
    
    return 0;
}

/**
 * Create a data object for rendering a table row
 * @param {Object} contributor - Contributor data
 * @param {Array} years - Years to include
 * @param {string} metric - Metric name
 * @param {Function} getPreviousMonthValue - Function to get previous month value
 * @returns {Object} Row data for rendering
 */
export function createRowData(contributor, years, metric, getPreviousMonthValue) {
    const rowData = {
        contributor,
        cells: [],
        squadClass: contributor.squad ? `squad-${contributor.squad.toLowerCase().replace(/\s+/g, '-')}` : ''
    };
    
    // For each year and month, calculate the cell data
    years.forEach(year => {
        for (let month = 1; month <= 12; month++) {
            // Get the value for this month
            const value = getContributorValue(contributor, year, month, metric);
            
            // Check if this is a future month
            const isInactivePeriod = isDateInFuture(year, month) || 
                                    (contributor.inactivePeriods && 
                                     contributor.inactivePeriods.some(
                                         period => period.year === year && period.month === month
                                     ));
            
            // Check if this is the current month
            const now = new Date();
            const isCurrentMonth = year === now.getFullYear() && month === now.getMonth() + 1;
            
            // Get previous month value for trend calculation
            const prevMonth = getPreviousMonth(year, month);
            const prevValue = getPreviousMonthValue(
                contributor, 
                prevMonth.year, 
                prevMonth.month, 
                metric
            );
            
            // Calculate drop intensity and trend
            const percentChange = calculatePercentChange(value, prevValue);
            const isSignificantDrop = percentChange < 0 && prevValue > 0;
            const dropIntensity = Math.min(
                Math.max(Math.abs(percentChange) / COLORS.DROP_INTENSITY_DIVISOR, COLORS.MIN_DROP_INTENSITY),
                COLORS.MAX_INTENSITY
            );
            
            const trend = calculateTrend(value, prevValue);
            
            // Create cell data
            rowData.cells.push({
                year,
                month,
                value,
                prevValue,
                isCurrentMonth,
                isInactivePeriod,
                isSignificantDrop,
                dropIntensity,
                trend,
                percentChange
            });
        }
    });
    
    return rowData;
}

/**
 * Render a table row cell
 * @param {Object} cellData - Cell data object
 * @param {string} metric - Metric name
 * @param {number} maxValue - Maximum value for scaling
 * @returns {HTMLElement} Table cell element
 */
export function renderTableCell(cellData, metric, maxValue) {
    const {
        year, 
        month, 
        value, 
        prevValue,
        isCurrentMonth,
        isInactivePeriod,
        isSignificantDrop,
        dropIntensity,
        trend,
        percentChange
    } = cellData;
    
    // Create the cell element
    const cell = DOMHelper.createElement('td');
    
    // Handle inactive period
    if (isInactivePeriod) {
        cell.className = CSS_CLASSES.DISABLED_CELL;
        cell.setAttribute('aria-label', 'Inactive period');
        cell.textContent = '-';
        return cell;
    }
    
    // Add appropriate classes
    if (isCurrentMonth) {
        cell.classList.add(CSS_CLASSES.CURRENT_MONTH);
    }
    
    if (isSignificantDrop) {
        cell.classList.add(CSS_CLASSES.DROP);
        
        // Add visually hidden description for screen readers
        const srElement = document.createElement('span');
        srElement.className = 'sr-only';
        srElement.textContent = `Drop of ${Math.abs(percentChange).toFixed(0)}% from previous month`;
        cell.appendChild(srElement);
    } else if (value === 0) {
        cell.classList.add(CSS_CLASSES.ZERO);
    }
    
    // Set background color
    const bgColor = getHeatmapColor(value, maxValue, metric, isSignificantDrop, dropIntensity);
    cell.style.backgroundColor = bgColor;
    
    // Add tooltip using TooltipBuilder for security
    TooltipBuilder.applyTooltip(cell, {
        contributor: cellData.contributor, 
        year,
        month,
        metric,
        metricDisplayName: getMetricDisplayName(metric),
        value,
        prevValue,
        trend
    });
    
    // Create a wrapper div for content alignment
    const wrapper = DOMHelper.createElement('div', {
        style: {
            display: 'inline-flex',
            alignItems: 'center',
            justifyContent: 'center'
        }
    });
    
    // Add the value
    const valueSpan = DOMHelper.createElement('span', {}, value.toString());
    wrapper.appendChild(valueSpan);
    
    // Add trend indicator if needed
    if (trend && trend.direction !== 'neutral' && value > 0 && prevValue > 0) {
        const trendSpan = DOMHelper.createElement('span', {
            className: `trend-indicator ${CSS_CLASSES[`TREND_${trend.direction.toUpperCase()}`] || ''}`,
            style: { marginLeft: '4px' },
            'aria-label': `${trend.percentage}% ${trend.direction === 'up' ? 'increase' : 'decrease'} from previous month`
        });
        
        // Add arrow based on trend direction
        const arrowEntity = trend.direction === 'up' ? '▲' : '▼';
        trendSpan.textContent = arrowEntity; // Use text content instead of innerHTML
        wrapper.appendChild(trendSpan);
    }
    
    cell.appendChild(wrapper);
    return cell;
}

/**
 * Create a batch rendering function for table rows
 * @param {Array} data - Data items to render
 * @param {Function} renderItem - Function to render a single item
 * @param {Function} onComplete - Callback when all batches complete
 * @param {number} batchSize - Size of each batch
 * @param {number} batchDelay - Delay between batches in ms
 * @returns {Function} Function to start batch rendering
 */
export function createBatchRenderer(data, renderItem, onComplete, batchSize = 20, batchDelay = 16) {
    let currentBatch = 0;
    const batchCount = Math.ceil(data.length / batchSize);
    
    const renderBatch = () => {
        if (currentBatch >= batchCount) {
            if (onComplete) onComplete();
            return;
        }
        
        const start = currentBatch * batchSize;
        const end = Math.min(start + batchSize, data.length);
        
        for (let i = start; i < end; i++) {
            renderItem(data[i], i);
        }
        
        currentBatch++;
        
        // Schedule next batch
        setTimeout(renderBatch, batchDelay);
    };
    
    return renderBatch;
}

/**
 * Create a filter function for contributor data
 * @param {Object} filters - Filter criteria
 * @returns {Function} Filter function
 */
export function createFilterFunction(filters) {
    return (contributor) => {
        // Apply squad filter
        if (filters.squad && filters.squad !== 'All' && contributor.squad !== filters.squad) {
            return false;
        }
        
        // Apply name filter
        if (filters.name && !contributor.name.toLowerCase().includes(filters.name.toLowerCase())) {
            return false;
        }
        
        // Apply "show squads only" filter
        if (filters.squadsOnly && !filters.includedSquads.includes(contributor.squad)) {
            return false;
        }
        
        return true;
    };
}

export default {
    calculateTrend,
    getHeatmapColor,
    getMetricDisplayName,
    createTooltipContent,
    isDateInFuture,
    getPreviousMonth,
    getContributorValue,
    createRowData,
    renderTableCell,
    createBatchRenderer,
    createFilterFunction
}; 