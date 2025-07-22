/**
 * TooltipBuilder.js
 * Creates secure tooltips using DOM manipulation instead of string concatenation
 */

import { MONTH_NAMES } from '../common/constants.js';
import { sanitizeHTML } from '../common/utils.js';

/**
 * TooltipBuilder - Creates secure tooltips to avoid XSS vulnerabilities
 */
class TooltipBuilder {
    /**
     * Create a tooltip for a contributor cell
     * @param {Object} options - Tooltip data
     * @param {Object} options.contributor - Contributor data
     * @param {number} options.year - The year
     * @param {number} options.month - The month (1-12)
     * @param {string} options.metric - The metric name
     * @param {string} options.metricDisplayName - Human-readable metric name
     * @param {number} options.value - The current value
     * @param {number} [options.prevValue] - Previous month's value
     * @param {Object} [options.trend] - Trend information
     * @returns {HTMLElement} A tooltip element
     */
    buildTooltipElement(options) {
        const {
            contributor,
            year,
            month,
            metric,
            metricDisplayName,
            value,
            prevValue,
            trend
        } = options;
        
        // Create tooltip container
        const container = document.createElement('div');
        container.className = 'tooltip-content';
        
        // Add contributor name in strong
        const nameElement = document.createElement('strong');
        nameElement.textContent = contributor.name;
        container.appendChild(nameElement);
        
        // Add squad information
        container.appendChild(document.createElement('br'));
        const squadText = document.createTextNode(`Squad: ${contributor.squad}`);
        container.appendChild(squadText);
        
        // Add date information
        container.appendChild(document.createElement('br'));
        const monthName = MONTH_NAMES.long[month - 1];
        const dateText = document.createTextNode(`${monthName} ${year}`);
        container.appendChild(dateText);
        
        // Add metric value
        container.appendChild(document.createElement('br'));
        const metricSpan = document.createElement('span');
        metricSpan.className = 'metric-name';
        metricSpan.textContent = metricDisplayName;
        container.appendChild(metricSpan);
        container.appendChild(document.createTextNode(`: ${value}`));
        
        // Add trend information if available
        if (trend && prevValue !== undefined && prevValue > 0) {
            container.appendChild(document.createElement('br'));
            container.appendChild(document.createTextNode(`Previous Month: ${prevValue}`));
            
            container.appendChild(document.createElement('br'));
            
            // Create trend container
            const trendSpan = document.createElement('span');
            trendSpan.className = trend.direction === 'up' ? 'trend-up' : 'trend-down';
            
            // Add text
            trendSpan.appendChild(document.createTextNode(`Change: ${Math.abs(trend.percentage)}% `));
            
            // Add arrow
            const arrow = document.createElement('span');
            arrow.textContent = trend.direction === 'up' ? '▲' : '▼';
            trendSpan.appendChild(arrow);
            
            container.appendChild(trendSpan);
        }
        
        return container;
    }
    
    /**
     * Create HTML string for a tooltip (safe alternative to string concatenation)
     * @param {Object} options - Tooltip data
     * @returns {string} Sanitized HTML string
     */
    buildTooltipHTML(options) {
        const tooltipElement = this.buildTooltipElement(options);
        return tooltipElement.innerHTML;
    }
    
    /**
     * Set tooltip content directly to an element's title attribute
     * @param {HTMLElement} element - The target element
     * @param {Object} options - Tooltip data 
     */
    setTooltipContent(element, options) {
        if (!element) return;
        
        const tooltipHTML = this.buildTooltipHTML(options);
        element.setAttribute('title', tooltipHTML);
    }
    
    /**
     * Apply tooltip attributes to an element
     * @param {HTMLElement} element - The target element
     * @param {Object} options - Tooltip data
     */
    applyTooltip(element, options) {
        if (!element) return;
        
        // Set Bootstrap tooltip attributes
        element.setAttribute('data-bs-toggle', 'tooltip');
        element.setAttribute('data-bs-html', 'true');
        
        // Set content
        this.setTooltipContent(element, options);
        
        // Add accessibility attributes
        element.setAttribute('tabindex', '0');
        
        // Create aria-label for screen readers (plain text version)
        const tooltipElement = this.buildTooltipElement(options);
        element.setAttribute('aria-label', tooltipElement.textContent);
    }
}

// Export singleton instance
export default new TooltipBuilder(); 