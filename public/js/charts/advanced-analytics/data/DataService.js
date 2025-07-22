/**
 * DataService.js
 * Handles loading, caching, and transformations of contribution data
 */

// Constants that relate to data processing
const FORCED_YEARS = [2023, 2024, 2025]; // Years to always include
const DEFAULT_SQUADS = [
    "All",
    "Bot",
    "Core",
    "Data",
    "Design",
    "EM",
    "Grape",
    "Infra",
    "Ops",
    "PC",
    "PM",
    "QA",
    "Resign",
    "Security",
    "Shopex",
    "TE",
    "Unknown",
];
const INCLUDED_SQUADS = ['Core', 'Grape', 'Ops', 'PC', 'Shopex']; // Squads for "Show Selected Squad Only"
const MONTH_NAMES = {
    short: Array.from({length: 12}, (_, i) => 
        new Date(2000, i, 1).toLocaleString("default", { month: "short" })),
    long: Array.from({length: 12}, (_, i) => 
        new Date(2000, i, 1).toLocaleString("default", { month: "long" }))
};

/**
 * DataService class for handling contribution data
 */
class DataService {
    constructor() {
        this.rawData = [];
        this.processedData = null;
        this.teams = [];
        this.yearsCache = null;
        this._dataCache = {};
    }

    /**
     * Initialize the data service with contribution data
     * @param {Array} contributionData - Raw contribution data
     */
    initialize(contributionData) {
        if (!contributionData || !Array.isArray(contributionData) || contributionData.length === 0) {
            console.warn("No data available for data service");
            return false;
        }
        
        // Store the raw data
        this.rawData = this._normalizeData(contributionData);
        
        // Extract teams/squads from data
        this._extractTeams();
        
        return true;
    }
    
    /**
     * Normalize the raw data (ensure proper types, etc.)
     * @param {Array} data - Raw data to normalize
     * @returns {Array} Normalized data
     * @private
     */
    _normalizeData(data) {
        return data.map(item => ({
            ...item,
            year: parseInt(item.year) || new Date().getFullYear(),
            month: parseInt(item.month) || 1,
            totalEvents: parseInt(item.totalEvents) || 0,
            mrCreated: parseInt(item.mrCreated) || 0,
            mrApproved: parseInt(item.mrApproved) || 0,
            repoPushes: parseInt(item.repoPushes) || 0,
            squad: item.squad || "Unknown",
            active: item.active !== false, // Default to active if not specified
        }));
    }
    
    /**
     * Extract unique teams/squads from the data
     * @private
     */
    _extractTeams() {
        const teams = new Set();
        
        this.rawData.forEach((item) => {
            if (item.squad) {
                teams.add(item.squad);
            }
        });
        
        this.teams = Array.from(teams);
    }
    
    /**
     * Get all available years, with forced inclusions
     * @returns {Array} List of years
     */
    getAllYears() {
        // Use cached value if available
        if (this.yearsCache) {
            return [...this.yearsCache]; // Return a copy to prevent mutation
        }
        
        // Get unique years from data
        const years = new Set();
        
        this.rawData.forEach((item) => {
            if (!isNaN(item.year)) {
                years.add(item.year);
            }
        });
        
        // Add forced years
        FORCED_YEARS.forEach(year => years.add(year));
        
        // Convert to array, sort, and cache
        this.yearsCache = Array.from(years).sort((a, b) => a - b);
        
        return [...this.yearsCache]; // Return a copy
    }
    
    /**
     * Process data for yearly view
     * @param {boolean} includeInactive - Whether to include inactive contributors
     * @returns {Array} Processed data
     */
    processDataForYearlyView(includeInactive = false) {
        // Use cached data if available
        const cacheKey = includeInactive ? 'withInactive' : 'activeOnly';
        
        if (this._dataCache[cacheKey]) {
            return this._dataCache[cacheKey];
        }
        
        // Get unique contributors
        const contributors = new Map();
        
        // First pass: Collect unique contributors and their squads
        this.rawData.forEach((item) => {
            // Skip inactive users if not showing inactive
            if (!includeInactive && item.active === false) {
                return;
            }
            
            const name = item.name;
            
            if (!contributors.has(name)) {
                contributors.set(name, {
                    name: name,
                    squad: item.squad,
                    active: item.active,
                    data: {},
                });
            }
        });
        
        // Get all years
        const years = this.getAllYears();
        
        // Initialize all year/month combinations with zeros
        contributors.forEach((contributor) => {
            years.forEach((year) => {
                if (!contributor.data[year]) {
                    contributor.data[year] = {};
                }
                
                for (let month = 1; month <= 12; month++) {
                    if (!contributor.data[year][month]) {
                        contributor.data[year][month] = {
                            totalEvents: 0,
                            mrCreated: 0,
                            mrApproved: 0,
                            repoPushes: 0,
                        };
                    }
                }
            });
        });
        
        // Second pass: Fill in actual data values
        this.rawData.forEach((item) => {
            // Skip inactive users
            if (!includeInactive && item.active === false) {
                return;
            }
            
            const name = item.name;
            const year = item.year;
            const month = item.month;
            
            if (contributors.has(name)) {
                const contributor = contributors.get(name);
                
                // Make sure the year and month exist
                if (!contributor.data[year]) {
                    contributor.data[year] = {};
                }
                
                if (!contributor.data[year][month]) {
                    contributor.data[year][month] = {
                        totalEvents: 0,
                        mrCreated: 0,
                        mrApproved: 0,
                        repoPushes: 0,
                    };
                }
                
                // Add data values
                contributor.data[year][month].totalEvents = item.totalEvents;
                contributor.data[year][month].mrCreated = item.mrCreated;
                contributor.data[year][month].mrApproved = item.mrApproved;
                contributor.data[year][month].repoPushes = item.repoPushes;
            }
        });
        
        // Convert to array and sort by squad then name
        const processed = Array.from(contributors.values()).sort((a, b) => {
            if (a.squad !== b.squad) {
                return a.squad.localeCompare(b.squad);
            }
            return a.name.localeCompare(b.name);
        });
        
        // Cache the result
        this._dataCache[cacheKey] = processed;
        
        return processed;
    }
    
    /**
     * Get the previous month's value for a contributor
     * @param {Object} contributor - Contributor data
     * @param {number} year - Current year
     * @param {number} month - Current month
     * @param {string} metric - Metric to get (totalEvents, mrCreated, etc.)
     * @returns {number} Value for the previous month
     */
    getPreviousMonthValue(contributor, year, month, metric) {
        let prevYear = year;
        let prevMonth = month - 1;
        
        if (prevMonth === 0) {
            prevMonth = 12;
            prevYear--;
        }
        
        if (
            contributor.data[prevYear] &&
            contributor.data[prevYear][prevMonth] && 
            contributor.data[prevYear][prevMonth][metric] !== undefined
        ) {
            return contributor.data[prevYear][prevMonth][metric];
        }
        
        return 0;
    }
    
    /**
     * Calculate trend between current and previous values
     * @param {number} current - Current value
     * @param {number} previous - Previous value
     * @returns {Object} Trend object with percentage and direction
     */
    calculateTrend(current, previous) {
        if (previous === 0) {
            return { percentage: 0, direction: "neutral" };
        }
        
        const diff = current - previous;
        const percentage = Math.round((diff / previous) * 100);
        const direction = diff > 0 ? "up" : diff < 0 ? "down" : "neutral";
        
        return { percentage, direction };
    }
    
    /**
     * Find maximum value for a metric across all data
     * @param {Array} processedData - Processed contributor data
     * @param {Array} years - Years to include
     * @param {string} metric - Metric to find max for
     * @returns {number} Maximum value
     */
    findMaxValueForMetric(processedData, years, metric) {
        let maxValue = 0;
        
        processedData.forEach((contributor) => {
            years.forEach((year) => {
                if (contributor.data[year]) {
                    for (let month = 1; month <= 12; month++) {
                        if (
                            contributor.data[year][month] &&
                            contributor.data[year][month][metric] !== undefined
                        ) {
                            maxValue = Math.max(
                                maxValue,
                                contributor.data[year][month][metric]
                            );
                        }
                    }
                }
            });
        });
        
        return maxValue;
    }
    
    /**
     * Clear the data cache
     */
    clearCache() {
        this._dataCache = {};
        this.yearsCache = null;
    }
    
    /**
     * Update a contributor's squad
     * @param {string} contributorName - Name of the contributor
     * @param {string} newSquad - New squad name
     * @returns {boolean} Success or failure
     */
    updateContributorSquad(contributorName, newSquad) {
        // Update in raw data
        let updated = false;
        this.rawData.forEach(item => {
            if (item.name === contributorName) {
                item.squad = newSquad;
                updated = true;
            }
        });
        
        // Clear cache if updated
        if (updated) {
            this.clearCache();
            this._extractTeams(); // Re-extract teams
        }
        
        return updated;
    }
    
    /**
     * Add a new squad
     * @param {string} squadName - Name of the new squad
     * @returns {boolean} Success or failure
     */
    addSquad(squadName) {
        if (!squadName || this.teams.includes(squadName)) {
            return false;
        }
        
        this.teams.push(squadName);
        return true;
    }
    
    /**
     * Delete a squad and move its contributors to "Unknown"
     * @param {string} squadName - Name of the squad to delete
     * @returns {number} Number of contributors affected
     */
    deleteSquad(squadName) {
        if (!squadName || !this.teams.includes(squadName)) {
            return 0;
        }
        
        // Count affected contributors
        let count = 0;
        
        // Update raw data
        this.rawData.forEach(item => {
            if (item.squad === squadName) {
                item.squad = "Unknown";
                count++;
            }
        });
        
        // Remove from teams
        const index = this.teams.indexOf(squadName);
        if (index > -1) {
            this.teams.splice(index, 1);
        }
        
        // Clear cache
        this.clearCache();
        
        return count;
    }
    
    /**
     * Filter contributors based on criteria
     * @param {Array} processedData - Processed contributor data
     * @param {Object} filters - Filter criteria
     * @returns {Array} Filtered data
     */
    filterContributors(processedData, filters) {
        const { squad, name, showCoreSquadsOnly } = filters;
        
        return processedData.filter(contributor => {
            // Squad filter
            if (squad !== "All" && contributor.squad !== squad) {
                return false;
            }
            
            // Name filter
            if (name && !contributor.name.toLowerCase().includes(name.toLowerCase())) {
                return false;
            }
            
            // Core squads only filter
            if (showCoreSquadsOnly && !INCLUDED_SQUADS.includes(contributor.squad)) {
                return false;
            }
            
            return true;
        });
    }
    
    /**
     * Export data to CSV format
     * @param {Array} data - Processed data
     * @param {string} metric - Current metric
     * @param {Array} years - Years to include
     * @returns {string} CSV content
     */
    exportToCSV(data, metric, years) {
        const metricDisplayName = {
            totalEvents: "Total Events",
            mrCreated: "MRs Created",
            mrApproved: "MRs Approved",
            repoPushes: "Repository Pushes",
        }[metric] || metric;
        
        // Header rows
        const csv = [
            [
                "Advanced Contribution Analysis",
                `Metric: ${metricDisplayName}`,
                `Years: ${years.join(", ")}`,
                `Generated: ${new Date().toLocaleString()}`,
            ].join(","),
            "", // Empty row for spacing
        ];
        
        // Column headers
        const headers = ["Squad", "Name"];
        years.forEach(year => {
            MONTH_NAMES.short.forEach(month => {
                headers.push(`${year} ${month}`);
            });
        });
        csv.push(headers.join(","));
        
        // Data rows
        data.forEach(contributor => {
            const row = [
                `"${contributor.squad || 'Unknown'}"`,
                `"${contributor.name}"`,
            ];
            
            years.forEach(year => {
                for (let month = 1; month <= 12; month++) {
                    let value = 0;
                    if (
                        contributor.data[year] &&
                        contributor.data[year][month] &&
                        contributor.data[year][month][metric] !== undefined
                    ) {
                        value = contributor.data[year][month][metric];
                    }
                    row.push(value);
                }
            });
            
            csv.push(row.join(","));
        });
        
        return csv.join("\n");
    }
}

// Export constants and DataService class
export { 
    FORCED_YEARS, 
    DEFAULT_SQUADS, 
    INCLUDED_SQUADS, 
    MONTH_NAMES,
    DataService
}; 