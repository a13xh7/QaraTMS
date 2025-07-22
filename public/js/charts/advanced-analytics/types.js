/**
 * types.js
 * JSDoc type definitions to enhance type safety
 */

/**
 * @typedef {Object} Contributor
 * @property {string} name - Contributor's name
 * @property {string} squad - Squad/team name
 * @property {boolean} [active] - Whether the contributor is active
 * @property {Object<number, Object<number, ContributionMonth>>} [data] - Nested data by year and month
 * @property {Array<InactivePeriod>} [inactivePeriods] - Periods of inactivity
 */

/**
 * @typedef {Object} ContributionMonth
 * @property {number} totalEvents - Total GitLab events in the month
 * @property {number} mrCreated - Number of merge requests created
 * @property {number} mrApproved - Number of merge requests approved
 * @property {number} repoPushes - Number of repository pushes
 */

/**
 * @typedef {Object} InactivePeriod
 * @property {number} year - Year of inactivity
 * @property {number} month - Month of inactivity (1-12)
 */

/**
 * @typedef {Object} ContributionData
 * @property {string} name - Contributor's name
 * @property {string} squad - Squad/team name
 * @property {number} year - Year of the contribution
 * @property {number} month - Month of the contribution (1-12)
 * @property {number} totalEvents - Total GitLab events in the month
 * @property {number} mrCreated - Number of merge requests created
 * @property {number} mrApproved - Number of merge requests approved
 * @property {number} repoPushes - Number of repository pushes
 * @property {boolean} [active=true] - Whether the contributor is active
 */

/**
 * @typedef {Object} Trend
 * @property {'up'|'down'|'neutral'} direction - Direction of trend
 * @property {number} percentage - Percentage change (absolute value)
 */

/**
 * @typedef {Object} CellData
 * @property {number} year - Year for this cell
 * @property {number} month - Month for this cell (1-12)
 * @property {number} value - Value for this cell
 * @property {number} [prevValue] - Previous month's value
 * @property {boolean} isCurrentMonth - Whether this is the current month
 * @property {boolean} isInactivePeriod - Whether this is a period of inactivity
 * @property {boolean} isSignificantDrop - Whether this is a significant drop
 * @property {number} dropIntensity - Intensity of the drop (0-1)
 * @property {Trend} [trend] - Trend information
 * @property {number} percentChange - Percentage change from previous month
 * @property {Contributor} contributor - Reference to contributor data
 */

/**
 * @typedef {Object} RowData
 * @property {Contributor} contributor - Contributor this row represents
 * @property {Array<CellData>} cells - Cells in this row
 * @property {string} squadClass - CSS class for squad styling
 */

/**
 * @typedef {Object} FilterOptions
 * @property {string} [squad] - Squad filter
 * @property {string} [name] - Name filter
 * @property {boolean} [squadsOnly] - Whether to show only included squads
 * @property {Array<string>} [includedSquads] - Squads to include when squadsOnly is true
 * @property {boolean} [showInactive] - Whether to show inactive contributors
 */

/**
 * @typedef {Object} ViewOptions
 * @property {string} containerId - ID of the container element
 * @property {Array<number>} years - Years to display
 * @property {boolean} showAllYears - Whether to show all years
 * @property {string} currentMetric - Currently selected metric
 * @property {FilterOptions} filters - Current filters
 */

/**
 * @typedef {Object} TooltipOptions
 * @property {Contributor} contributor - Contributor data
 * @property {number} year - Year 
 * @property {number} month - Month (1-12)
 * @property {string} metric - Metric name
 * @property {string} metricDisplayName - Human-readable metric name
 * @property {number} value - Current value
 * @property {number} [prevValue] - Previous month's value
 * @property {Trend} [trend] - Trend information
 */

/**
 * @typedef {Object} StatusMessage
 * @property {'info'|'success'|'warning'|'error'} type - Message type
 * @property {string} text - Message text
 * @property {number} [duration] - Auto-hide duration in ms (0 for no auto-hide)
 */

// Export empty object - these are just type definitions
export default {}; 