/**
 * Advanced Analytics Module
 * Provides advanced visualization and analysis tools for contribution data
 */

// Performance tuning constants
const TOOLTIP_BATCH_SIZE = 50;
const TOOLTIP_BATCH_DELAY = 100;
const MODAL_RENDER_DELAY = 500;
const TOOLTIP_SHOW_DELAY = 200;
const TOOLTIP_HIDE_DELAY = 100;

// Debounce helper function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Check if we're on a mobile device for UI optimizations
const isMobile = window.matchMedia('(max-width: 767px)').matches;

// Constants
const FORCED_YEARS = [2023, 2024, 2025]; // Years to always include
const INCLUDED_SQUADS = ['Core', 'Grape', 'Ops', 'PC', 'Shopex']; // Squads for "Show Selected Squad Only"
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
const MONTH_NAMES = {
    short: Array.from({length: 12}, (_, i) => 
        new Date(2000, i, 1).toLocaleString("default", { month: "short" })),
    long: Array.from({length: 12}, (_, i) => 
        new Date(2000, i, 1).toLocaleString("default", { month: "long" }))
};

/**
 * Advanced Analytics Module
 * Provides advanced visualization and analysis tools for contribution data
 */

const AdvancedAnalytics = {
    // Chart instances
    charts: {
        yearlyOverview: null,
    },
    
    // Data cache
    data: {
        contributions: [],
        processedData: null,
        teams: [],
    },
    
    // Initialization flags
    _globalClickInit: false,
    _yearSelectorInit: false,
    
    // Initialize the module
    init: function (contributionData) {
        if (
            !contributionData ||
            !Array.isArray(contributionData) ||
            contributionData.length === 0
        ) {
            console.warn("No data available for advanced analytics");
            return;
        }
        
        // Store the data without overwriting existing properties
        if (!this.data) this.data = {};
        Object.assign(this.data, { contributions: contributionData });
        
        // Extract teams/squads from data
        this.extractTeams();

        // Initialize charts object to store chart instances
        this.charts = {
            yearlyOverview: null,
        };
        
        // Set up event listeners
        this.setupEventListeners();
    },
    
    // Set up event listeners
    setupEventListeners: function () {
        // Advanced analysis button
        const advancedBtn = document.getElementById("advancedAnalysisBtn");
        if (advancedBtn) {
            advancedBtn.addEventListener("click", () => {
                this.showAdvancedAnalysisModal();
            });
        }
        
        // Add event delegation for modal button clicks
        document.addEventListener("click", (e) => {
            if (e.target && e.target.matches("#manageSquadsBtn")) {
                console.log("Manage Squads button clicked through delegation");
                this.openSquadManagementModal();
                e.stopPropagation();
            }
        });
    },
    
    // Extract teams/squads from data
    extractTeams: function () {
        const teams = new Set();
        
        this.data.contributions.forEach((item) => {
            if (item.squad) {
                teams.add(item.squad);
            }
        });
        
        this.data.teams = Array.from(teams);
    },
    
    // Show advanced analysis modal
    showAdvancedAnalysisModal: function (options = {}) {
        console.log("Opening advanced analysis modal");
        
        // Create modal if it doesn't exist yet
        if (!document.getElementById("advancedAnalysisModal")) {
            this.createAdvancedAnalysisModal();
        }
        
        // Ensure all raw contribution data has proper year values as integers
        this.data.contributions.forEach((item) => {
            if (item.year && typeof item.year === "string") {
                item.year = parseInt(item.year);
            }
        });
        
        // Log raw contributions to debug
        const contributionsByYear = {};
        this.data.contributions.forEach((item) => {
            const year = parseInt(item.year);
            if (!isNaN(year)) {
                if (!contributionsByYear[year]) {
                    contributionsByYear[year] = 0;
                }
                contributionsByYear[year]++;
            }
        });
        console.log("Contributions by year:", contributionsByYear);
        
        // Process the data for yearly view
        this.processDataForYearlyView();
        
        // Show the modal
        const modal = new bootstrap.Modal(
            document.getElementById("advancedAnalysisModal"),
        );
        modal.show();
        
        // Create the chart (after modal is shown to ensure proper rendering)
        setTimeout(() => {
            // Make sure "Show All Years" is checked
            document.getElementById("showAllYears").checked = true;
            
            // Get all years for display - FORCE include all years
            let allYears = this.getAllYears();
            console.log("Years for initial display:", allYears);
            
            // Create the chart with all years - log the years being passed
            console.log("Rendering table with years:", allYears);
            this.renderYearlyOverviewTable("totalEvents", allYears); // Default to total events
            
            // Initialize tooltips with performance optimization
            if (!isMobile && window.bootstrap && window.bootstrap.Tooltip) {
                setTimeout(() => {
                    const tooltipTriggerList = [].slice.call(
                        document.querySelectorAll('#yearlyOverviewData td:not(.filtered) [data-bs-toggle="tooltip"]')
                    );
                    
                    let currentBatch = 0;
                    
                    const initBatch = () => {
                        const start = currentBatch * TOOLTIP_BATCH_SIZE;
                        const end = Math.min(start + TOOLTIP_BATCH_SIZE, tooltipTriggerList.length);
                        
                        if (start < tooltipTriggerList.length) {
                            for (let i = start; i < end; i++) {
                                new bootstrap.Tooltip(tooltipTriggerList[i], {
                                    container: "body",
                                    html: true,
                                    trigger: "hover focus",
                                    delay: { 
                                        show: TOOLTIP_SHOW_DELAY, 
                                        hide: TOOLTIP_HIDE_DELAY 
                                    },
                                });
                            }
                            
                            currentBatch++;
                            
                            if (currentBatch * TOOLTIP_BATCH_SIZE < tooltipTriggerList.length) {
                                setTimeout(initBatch, TOOLTIP_BATCH_DELAY);
                            }
                        }
                    };
                    
                    initBatch();
                }, MODAL_RENDER_DELAY);
            }
        }, 500);
    },
    
    // Create advanced analysis modal
    createAdvancedAnalysisModal: function () {
        const modalHtml = `
            <div class="modal fade" id="advancedAnalysisModal" tabindex="-1" aria-labelledby="advancedAnalysisModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-fullscreen">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="advancedAnalysisModalLabel">Advanced Contribution Analysis</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="btn-group" role="group" aria-label="Metric Selection">
                                        <button type="button" class="btn btn-outline-primary active" data-metric="totalEvents">Total Events</button>
                                        <button type="button" class="btn btn-outline-primary" data-metric="mrCreated">MR Created</button>
                                        <button type="button" class="btn btn-outline-primary" data-metric="mrApproved">MR Approved</button>
                                        <button type="button" class="btn btn-outline-primary" data-metric="repoPushes">Repo Pushes</button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="showTrends" checked>
                                        <label class="form-check-label" for="showTrends">Show Trends</label>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary ms-2" id="manageSquadsBtn">
                                        Manage Squads
                                    </button>
                                </div>
                                <!-- Year selector (multi‑select) -->
                                <div class="col-md-4 text-end">
                                  <div class="year-selector-wrapper position-relative d-inline-block">
                                    <button type="button" class="btn btn-outline-secondary" id="yearSelectorToggle">
                                      <span id="yearSelectorLabel">Show All Years</span>
                                      <i class="bi bi-caret-down-fill ms-1"></i>
                                        </button>
                                    
                                    <div class="year-selector-dropdown border rounded shadow position-absolute bg-white mt-1 p-3 d-none"
                                         id="yearSelectorDropdown" style="min-width: 200px; z-index: 100;">
                                      <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="showAllYears" checked>
                                        <label class="form-check-label" for="showAllYears">Show All Years</label>
                                      </div>
                                      <hr class="my-2">
                                      <div id="yearCheckboxContainer"><!-- filled by JS --></div>
                                    </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Contributor Filter Section -->
                            <div class="row mb-3 filter-section">
                                <div class="col-md-4">
                                    <label for="squadFilter" class="form-label">Select Squad</label>
                                    <select id="squadFilter" class="form-select" style="cursor: pointer;" onchange="AdvancedAnalytics.directFilterToggle()">
                                        <option value="All">All</option>
                                        <option value="Bot">Bot</option>
                                        <option value="Core">Core</option>
                                        <option value="Data">Data</option>
                                        <option value="Design">Design</option>
                                        <option value="EM">EM</option>
                                        <option value="Grape">Grape</option>
                                        <option value="Infra">Infra</option>
                                        <option value="Ops">Ops</option>
                                        <option value="PC">PC</option>
                                        <option value="PM">PM</option>
                                        <option value="QA">QA</option>
                                        <option value="Resign">Resign</option>
                                        <option value="Security">Security</option>
                                        <option value="Shopex">Shopex</option>
                                        <option value="TE">TE</option>
                                        <option value="Unknown">Unknown</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-end h-100">
                                        <div class="form-check me-3">
                                            <input class="form-check-input" type="checkbox" id="excludeSquads" onclick="AdvancedAnalytics.directFilterToggle()">
                                            <label class="form-check-label" for="excludeSquads">
                                                Show Squads Only
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table id="yearlyOverviewTable" class="table table-bordered table-sm">
                                    <thead id="yearlyOverviewTableHead">
                                        <!-- Headers will be added dynamically -->
                                    </thead>
                                    <tbody id="yearlyOverviewData">
                                        <!-- Data will be populated here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="filter-status-container me-auto">
                                <!-- Filter status will be added here dynamically -->
                            </div>
                            <div class="footer-buttons">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" id="exportYearlyData">
                                    <i class="bi bi-file-excel me-1"></i> Export to Excel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Squad Management Modal -->
            <div class="modal fade" id="squadManagementModal" tabindex="-1" aria-labelledby="squadManagementModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="squadManagementModalLabel">Manage Squad Assignments</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body squad-management-container">
                            <!-- Content will be added dynamically -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Add the modal to the document
        const modalContainer = document.createElement("div");
        modalContainer.innerHTML = modalHtml;
        document.body.appendChild(modalContainer);
        
        // Get a reference to the Squad Management modal element
        const squadModal = document.getElementById("squadManagementModal");
        
        // Ensure we properly dispose Bootstrap modals when they're hidden
        if (squadModal) {
            squadModal.addEventListener("hidden.bs.modal", function () {
                // Force dispose the modal instance to prevent issues with subsequent openings
                try {
                    const modalInstance =
                        bootstrap.Modal.getInstance(squadModal);
                    if (modalInstance) {
                        modalInstance.dispose();
                    }
                } catch (e) {
                    console.warn("Error disposing modal:", e);
                }
                
                // Ensure the body doesn't have modal-open class stuck on it
                document.body.classList.remove("modal-open");
                
                // Remove any lingering backdrop
                const backdrops = document.querySelectorAll(".modal-backdrop");
                backdrops.forEach((backdrop) => backdrop.remove());
                
                console.log("Squad modal fully cleaned up");
            });
        }
        
        // Add event listeners for the metric buttons
        document
            .querySelectorAll("#advancedAnalysisModal button[data-metric]")
            .forEach((button) => {
                button.addEventListener("click", (e) => {
                // Remove active class from all buttons
                    document
                        .querySelectorAll(
                            "#advancedAnalysisModal button[data-metric]",
                        )
                        .forEach((btn) => {
                            btn.classList.remove("active");
                });
                
                // Add active class to clicked button
                    e.target.classList.add("active");
                
                // Update chart
                    const metric = e.target.getAttribute("data-metric");
                this.updateYearlyOverviewChart(metric);
            });
        });
        
        // Add event listener for export button
        document
            .getElementById("exportYearlyData")
            .addEventListener("click", () => {
            this.exportYearlyData();
        });
        
        // Add event listener for trend toggle
        document
            .getElementById("showTrends")
            .addEventListener("change", (e) => {
            this.updateYearlyOverviewChart(
                    document
                        .querySelector(
                            "#advancedAnalysisModal button[data-metric].active",
                        )
                        .getAttribute("data-metric"),
            );
        });
        
        // Add event listener for show inactive toggle
        document
            .getElementById("showInactive")
            .addEventListener("change", (e) => {
            // Re-process the data for yearly view, which will filter based on this setting
            this.processDataForYearlyView();
            
            // Update the display
            this.updateYearlyOverviewChart(
                    document
                        .querySelector(
                            "#advancedAnalysisModal button[data-metric].active",
                        )
                        .getAttribute("data-metric"),
            );
        });
        
        // Set up the year selector
        this.setupYearSelector();
        
        // Add event listener for year selector toggle
        const yearSelectorToggle =
            document.getElementById("yearSelectorToggle");
        const yearSelectorDropdown = document.getElementById(
            "yearSelectorDropdown",
        );
        
        if (yearSelectorToggle && yearSelectorDropdown) {
            // Toggle dropdown visibility when clicking the toggle button
            yearSelectorToggle.addEventListener("click", (e) => {
                yearSelectorDropdown.classList.toggle("d-none");
                e.stopPropagation();
            });
            
            // Close dropdown when clicking outside
            document.addEventListener("click", (e) => {
                if (
                    !yearSelectorDropdown.contains(e.target) &&
                    !e.target.closest("#yearSelectorToggle")
                ) {
                    yearSelectorDropdown.classList.add("d-none");
                }
            });
            
            // Prevent dropdown from closing when clicking inside it
            yearSelectorDropdown.addEventListener("click", (e) => {
                e.stopPropagation();
            });
        }
        
        // Keep the original event listener for backward compatibility
        document
            .getElementById("showAllYears")
            .addEventListener("change", (e) => {
            if (e.target.checked) {
                console.log("Show all years checkbox checked");
                // Get the active metric
                    const activeMetric = document
                        .querySelector(
                            "#advancedAnalysisModal button[data-metric].active",
                        )
                        .getAttribute("data-metric");
                
                // Get all available years and render the table
                const years = this.getAllYears();
                console.log("All available years:", years);
                this.renderYearlyOverviewTable(activeMetric, years);
            } else {
                this.updateYearlyOverviewForSelectedYears();
            }
        });
        
        // Set up the contributor filters
        this.setupContributorFilters();
        
        // Use a more robust approach for the manage squads button
        const manageSquadsBtn = document.getElementById("manageSquadsBtn");
        if (manageSquadsBtn) {
            // Completely replace the button to remove any stale event listeners
            const newBtn = manageSquadsBtn.cloneNode(true);
            manageSquadsBtn.parentNode.replaceChild(newBtn, manageSquadsBtn);
            
            // Add fresh event listener
            newBtn.addEventListener("click", () => {
                console.log("Manage Squads button clicked");
                
                // Don't close the parent modal - this was causing issues
                // Just open the squad management modal directly
                this.openSquadManagementModal();
                
                // Prevent any default actions or event bubbling
                return false;
            });
            
            console.log("Manage Squads button event listener attached");
        } else {
            console.error("Manage Squads button not found in the DOM");
        }
        
        // Set up squad management functionality
        this.setupSquadManagement();
    },
    
    // Set up the year selector
    setupYearSelector: function () {
        // Only initialize once
        if (this._yearSelectorInit) return;
        
        const dd = document.getElementById("yearSelectorDropdown");
        if (!dd) return;

        // Guard global click listener
        if (!this._globalClickInit) {
            document.addEventListener("click", (e) => {
                if (!dd.contains(e.target) && !e.target.closest("#yearSelectorToggle")) {
                    dd.classList.add("d-none");
                }
            });
            this._globalClickInit = true;
        }

        /* ---------- populate checkboxes ---------- */
        dd.innerHTML = "";
        this.getAllYears()
            .sort((a, b) => b - a)
            .forEach((yr) => {
                dd.insertAdjacentHTML(
                    "beforeend",
                    `<div class="form-check">
             <input class="form-check-input year-cbx" type="checkbox"
                    id="year_${yr}" data-year="${yr}">
             <label class="form-check-label" for="year_${yr}">${yr}</label>
           </div>`,
                );
            });

        /* ---------- UI behaviour ---------- */
        document
            .getElementById("yearSelectorToggle")
            .addEventListener("click", (e) => {
                e.stopPropagation();
                dd.classList.toggle("d-none");
            });

        // "Show all" checkbox
        document.getElementById("showAllYears").addEventListener("change", () => {
            const on = document.getElementById("showAllYears").checked;
            document.querySelectorAll(".year-cbx").forEach((c) => {
                c.checked = false;
                c.disabled = on;
            });
            this.updateYearlyOverviewChart(
                document.querySelector(
                    "#advancedAnalysisModal button[data-metric].active",
                ).dataset.metric,
            );
        });

        // individual year boxes
        dd.addEventListener("change", (e) => {
            if (!e.target.classList.contains("year-cbx")) return;

            if (e.target.checked) document.getElementById("showAllYears").checked = false; // un‑tick "all"
            if (![...document.querySelectorAll(".year-cbx:checked")].length)
                document.getElementById("showAllYears").checked = true; // nothing left → all

            // label text
            const yrs = [...document.querySelectorAll(".year-cbx:checked")].map(
                (c) => c.dataset.year,
            );
            document.getElementById("yearSelectorLabel").textContent = document.getElementById("showAllYears").checked
                ? "Show All Years"
                : yrs.length === 1
                  ? yrs[0]
                  : `${yrs.length} Years Selected`;

            this.updateYearlyOverviewChart(
                document.querySelector(
                    "#advancedAnalysisModal button[data-metric].active",
                ).dataset.metric,
            );
        });
        
        // Mark as initialized
        this._yearSelectorInit = true;
    },
    
    // Update yearly overview for all years
    updateYearlyOverviewForAllYears: function () {
        // Get the active metric
        const activeMetric = document
            .querySelector("#advancedAnalysisModal button[data-metric].active")
            .getAttribute("data-metric");
        
        // Get all available years and render the table
        const years = this.getAllYears();
        console.log("Showing all years:", years);
        this.renderYearlyOverviewTable(activeMetric, years);
    },
    
    // Update yearly overview for selected years
    updateYearlyOverviewForSelectedYears: function () {
        // Get the active metric
        const activeMetric = document
            .querySelector("#advancedAnalysisModal button[data-metric].active")
            .getAttribute("data-metric");

        // Get selected years using the new approach
        let years;
        if (document.getElementById("showAllYears").checked) {
            years = this.getAllYears();
        } else {
            years = [...document.querySelectorAll(".year-cbx:checked")].map(
                (c) => +c.dataset.year,
            );
        }
        
        // If no years selected, select the current year
        if (years.length === 0) {
            const currentYear = new Date().getFullYear();
            document.getElementById(`year_${currentYear}`).checked = true;
            years = [currentYear];
        }
        
        this.renderYearlyOverviewTable(activeMetric, years);
    },
    
    // Get all available years
    getAllYears: function () {
        // Get unique years from data
        let years = [];
        
        // Explicitly check each contribution record and parse year correctly
        this.data.contributions.forEach((item) => {
            const year = parseInt(item.year);
            if (!isNaN(year) && years.indexOf(year) === -1) {
                years.push(year);
            }
        });
        
        console.log("Raw unique years extracted:", years);
        
        // Force include specific years in the years list
        FORCED_YEARS.forEach((year) => {
            if (years.indexOf(year) === -1) {
                years.push(year);
            }
        });
        
        // Sort years in ascending order
        years = years.sort((a, b) => a - b);
        console.log("Final years to display (with forced inclusion):", years);
        return years;
    },
    
    // Process data for yearly view
    processDataForYearlyView: function () {
        // Check if we should show inactive users
        const showInactive = document.getElementById("showInactive") && 
                             document.getElementById("showInactive").checked;
        
        // Use cached data if available
        if (!this._cache) this._cache = {};
        const key = showInactive ? 'withInactive' : 'activeOnly';
        
        if (this._cache[key]) {
            console.log("Using cached processed data for", key);
            this.data.processedData = this._cache[key];
            return;
        }
        
        console.log("Processing real data from contributions:", this.data.contributions);
        
        // Get unique contributors and their squads
        const contributors = new Map();
        
        // First pass: Collect all unique contributors and their squads
        this.data.contributions.forEach((item) => {
            // Skip inactive users if not showing inactive and they have an 'active' property set to false
            if (!showInactive && item.active === false) {
                return;
            }
            
            const name = item.name;
            const squad = item.squad || "Unknown";
            
            if (!contributors.has(name)) {
                contributors.set(name, {
                    name: name,
                    squad: squad,
                    active: item.active !== false, // Default to active if not specified
                    data: {},
                });
            }
        });
        
        // Get all years we need to process
        const years = this.getAllYears();
        
        // Initialize all year/month combinations for all contributors with zeros
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
        this.data.contributions.forEach((item) => {
            // Skip inactive users if not showing inactive and they have an 'active' property set to false
            if (!showInactive && item.active === false) {
                return;
            }
            
            const name = item.name;
            const year = parseInt(item.year);
            const month = parseInt(item.month);
            
            if (contributors.has(name) && !isNaN(year) && !isNaN(month)) {
                const contributor = contributors.get(name);
                
                // Add data - use the actual values
                contributor.data[year][month].totalEvents =
                    parseInt(item.totalEvents) || 0;
                contributor.data[year][month].mrCreated =
                    parseInt(item.mrCreated) || 0;
                contributor.data[year][month].mrApproved =
                    parseInt(item.mrApproved) || 0;
                contributor.data[year][month].repoPushes =
                    parseInt(item.repoPushes) || 0;
            }
        });
        
        // Convert to array and sort by squad then name
        const processed = Array.from(contributors.values()).sort((a, b) => {
            if (a.squad !== b.squad) {
                return a.squad.localeCompare(b.squad);
            }
            return a.name.localeCompare(b.name);
        });
        
        // Cache the result for future use
        this._cache[key] = processed;
        
        // Set the processed data
        this.data.processedData = processed;
        
        console.log("Processed data for display:", this.data.processedData);
    },
    
    // Create yearly overview chart
    createYearlyOverviewChart: function (metric) {
        // Destroy existing chart
        this.destroyCharts();
        
        // Render the table for all years by default
        this.renderYearlyOverviewTable(metric, this.getAllYears());
    },
    
    // Update yearly overview chart
    updateYearlyOverviewChart: function (metric) {
        let years;
        if (document.getElementById("showAllYears").checked) {
            years = this.getAllYears();
        } else {
            years = [...document.querySelectorAll(".year-cbx:checked")].map(
                (c) => +c.dataset.year,
            );
        }
        this.renderYearlyOverviewTable(metric, years);
    },
    
    // Render yearly overview table
    renderYearlyOverviewTable: function (metric, years) {
        if (!Array.isArray(years) || years.length === 0) {
            console.error("No years specified for rendering");
            return;
        }
        
        // Check for mobile device to optimize rendering
        const isMobile = window.innerWidth < 768;
        
        // On mobile, limit to current year only for better performance
        if (isMobile) {
            const currentYear = new Date().getFullYear();
            years = years.filter((year) => year === currentYear);
            if (years.length === 0) {
                years = [currentYear];
            }
            console.log("Mobile device detected - limiting to current year:", years);
        } else {
            // Only force include years if "Show All Years" is checked
            const showAllYears =
                document.getElementById("showAllYears") &&
                document.getElementById("showAllYears").checked;
            
            if (showAllYears) {
                // When "Show All Years" is checked, use all years
                years = this.getAllYears();
                console.log(
                    "Show All Years enabled - using all available years:",
                    years,
                );
            } else {
                // When specific years are selected, respect that selection
                console.log("Using selected years:", years);
            }
        }
        
        const tbody = document.getElementById("yearlyOverviewData");
        const thead = document.getElementById("yearlyOverviewTableHead");
        if (!tbody || !thead) return;
        
        // Sort years in ascending order
        years.sort((a, b) => a - b);
        
        // Clear existing data
        tbody.innerHTML = "";
        thead.innerHTML = "";
        
        // Create header rows
        const headerRow1 = document.createElement("tr");
        headerRow1.innerHTML =
            '<th rowspan="2" class="sticky-col sticky-col-1" style="background-color: #fff;">Squad</th>' +
                              '<th rowspan="2" class="sticky-col sticky-col-2" style="background-color: #fff;">Name</th>';
        
        const headerRow2 = document.createElement("tr");
        
        // Add year and month columns
        years.forEach((year) => {
            // Add year column with colspan for all months
            const yearHeader = document.createElement("th");
            yearHeader.className = "year-column";
            yearHeader.setAttribute("colspan", "12");
            yearHeader.textContent = year;
            yearHeader.style.textAlign = "center";
            yearHeader.style.borderBottom = "2px solid #dee2e6";
            headerRow1.appendChild(yearHeader);
            
            // Add month columns for this year
            for (let month = 1; month <= 12; month++) {
                // Use short month names from the constant
                const monthName = MONTH_NAMES.short[month - 1];
                const monthHeader = document.createElement("th");
                monthHeader.textContent = monthName;
                monthHeader.style.position = "sticky";
                monthHeader.style.top = "40px";
                monthHeader.style.zIndex = "2";
                monthHeader.style.backgroundColor = "#f8f9fa";
                headerRow2.appendChild(monthHeader);
            }
        });
        
        // Add headers to the table
        thead.appendChild(headerRow1);
        thead.appendChild(headerRow2);
        
        // Helper for color scaling
        const getHeatmapColor = (value, max) => {
            if (value === 0) return "#f8f9fa";
            const intensity = Math.min(Math.max(value / max, 0.1), 1);
            
            // Different color schemes for different metrics
            let r, g, b;
            switch (metric) {
                case "totalEvents":
                    // Blue gradient for total events
                    r = 255 - Math.round(intensity * 255);
                    g = 255 - Math.round(intensity * 155);
                    b = 255;
                    break;
                case "mrCreated":
                    // Green gradient for MRs created
                    r = 255 - Math.round(intensity * 200);
                    g = 255;
                    b = 255 - Math.round(intensity * 200);
                    break;
                case "mrApproved":
                    // Purple gradient for MRs approved
                    r = 255 - Math.round(intensity * 100);
                    g = 255 - Math.round(intensity * 200);
                    b = 255;
                    break;
                case "repoPushes":
                    // Orange gradient for repo pushes
                    r = 255;
                    g = 255 - Math.round(intensity * 150);
                    b = 255 - Math.round(intensity * 240);
                    break;
                default:
                    // Default blue gradient
                    r = 255 - Math.round(intensity * 255);
                    g = 255 - Math.round(intensity * 155);
                    b = 255;
            }
            
            return `rgba(${r}, ${g}, ${b}, ${intensity})`;
        };
        
        // Find maximum value for color scaling
        let maxValue = 0;
        this.data.processedData.forEach((contributor) => {
            years.forEach((year) => {
                if (contributor.data[year]) {
                    for (let month = 1; month <= 12; month++) {
                        if (
                            contributor.data[year][month] &&
                            contributor.data[year][month][metric]
                        ) {
                            maxValue = Math.max(
                                maxValue,
                                contributor.data[year][month][metric],
                            );
                        }
                    }
                }
            });
        });
        
        // Helper function to get previous month value
        const getPreviousMonthValue = (contributor, year, month, metric) => {
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
            };
            
        // Helper function to calculate trend
            const calculateTrend = (current, previous) => {
            if (previous === 0) 
                return { percentage: 0, direction: "neutral" };
                
                const diff = current - previous;
                const percentage = Math.round((diff / previous) * 100);
            const direction = diff > 0 ? "up" : diff < 0 ? "down" : "neutral";
                
                return { percentage, direction };
            };
            
        // Sort the processedData by squad for visual grouping
        this.data.processedData.sort((a, b) => {
            if (a.squad !== b.squad) {
                return a.squad.localeCompare(b.squad);
            }
            return a.name.localeCompare(b.name);
        });

        // Create a document fragment to build the table more efficiently
        const fragment = document.createDocumentFragment();
        
        // Helper function to build a table row
        const buildRow = (contributor) => {
            // Create the contributor row
            const row = document.createElement("tr");
            row.setAttribute("data-contributor-name", contributor.name);
            row.setAttribute("data-contributor-squad", contributor.squad || "Unknown");
            
            // Add squad cell
            const squadCell = document.createElement("td");
            squadCell.className = "sticky-col sticky-col-1";
            squadCell.textContent = contributor.squad || "Unknown";
            
            // Apply visual grouping - add a top border if this is the first row for a new squad
            const lastRowSquad = fragment.lastElementChild?.querySelector?.("td:first-child")?.textContent;
            if (lastRowSquad && lastRowSquad !== contributor.squad) {
                squadCell.style.borderTop = "2px solid #ddd";
                squadCell.style.backgroundColor = "#f8f9fa";
            }
            
            row.appendChild(squadCell);
            
            // Add name cell
            const nameCell = document.createElement("td");
            nameCell.className = "sticky-col sticky-col-2";
            nameCell.textContent = contributor.name;
            row.appendChild(nameCell);
            
            // Add data cells for each year/month
            years.forEach((year) => {
                for (let month = 1; month <= 12; month++) {
                    const cell = document.createElement("td");
                let value = 0;
                
                // Check if this is the current month
                const currentDate = new Date();
                const currentYear = currentDate.getFullYear();
                const currentMonth = currentDate.getMonth() + 1;
                    const isCurrentMonth = year === currentYear && month === currentMonth;
                
                // Don't show future months
                    const isCurrentOrPastMonth =
                        year < currentDate.getFullYear() ||
                        (year === currentDate.getFullYear() &&
                            month <= currentDate.getMonth() + 1);
                
                // Check for inactive periods (either future dates or marked inactive)
                const isInactivePeriod = !isCurrentOrPastMonth || 
                            (contributor.inactivePeriods &&
                                contributor.inactivePeriods.some(
                                    (period) => period.year === year && period.month === month
                                ));
                        
                if (isInactivePeriod) {
                            cell.classList.add("disabled-cell");
                            cell.setAttribute("aria-label", "Inactive period");
                            cell.textContent = "-";
                            row.appendChild(cell);
                            continue;
                }
                
                // Highlight current month
                if (isCurrentMonth) {
                            cell.classList.add("current-month");
                }
                
                    if (
                        contributor.data[year] &&
                    contributor.data[year][month] && 
                        contributor.data[year][month][metric] !== undefined
                    ) {
                    value = contributor.data[year][month][metric];
                }
                    
                    // Check for drop from previous month
                    const prevValue = getPreviousMonthValue(contributor, year, month, metric);
                    const percentChange = prevValue > 0 ? ((value - prevValue) / prevValue) * 100 : 0;
                    const isSignificantDrop = percentChange < 0 && prevValue > 0; // Any drop
                    
                    // Set background color based on value and whether it's a drop
                    if (isSignificantDrop) {
                        // Use red gradient for drops - intensity based on percentage drop
                        const dropIntensity = Math.min(Math.max(Math.abs(percentChange) / 100, 0.2), 1);
                        
                        // Red color for drops
                        const r = 255;
                        const g = 255 - Math.round(dropIntensity * 230);
                        const b = 255 - Math.round(dropIntensity * 230);
                        
                        cell.style.backgroundColor = `rgba(${r}, ${g}, ${b}, 0.8)`;
                        cell.classList.add("value-drop");
                    } else {
                        // Use light gray for all other values
                        cell.style.backgroundColor = "#f8f9fa";
                    }
                
                // Different styling for zero vs positive values
                if (value > 0) {
                        if (isSignificantDrop) {
                            // Add drop class for styling
                            cell.classList.add("value-drop");
                        }
                } else {
                        // For zero values, use a light style
                        cell.classList.add("zero-value");
                }
                
                // Enable Bootstrap tooltip
                    cell.setAttribute("data-bs-toggle", "tooltip");
                    cell.setAttribute("data-bs-html", "true");
                
                // Create a wrapper div for content (to allow flexbox alignment)
                    const wrapper = document.createElement("div");
                    wrapper.style.display = "inline-flex";
                    wrapper.style.alignItems = "center";
                    wrapper.style.justifyContent = "center";
                
                // Add the value
                    const valueSpan = document.createElement("span");
                valueSpan.textContent = value;
                wrapper.appendChild(valueSpan);
                
                // Calculate and prepare tooltip content
                    const monthName = MONTH_NAMES.long[month-1];
                let tooltipContent = `<strong>${contributor.name}</strong> (${contributor.squad})<br>`;
                tooltipContent += `${monthName} ${year}<br>`;
                        tooltipContent += `${
                            metric === "totalEvents"
                                ? "Total Events"
                                : metric === "mrCreated"
                                    ? "MRs Created"
                                    : metric === "mrApproved"
                                        ? "MRs Approved"
                                        : "Repository Pushes"
                        }: <strong>${value}</strong><br>`;
                
                // Get previous month's value for trend
                if (month > 1 || year > Math.min(...years)) {
                            const prevValue = getPreviousMonthValue(
                                contributor,
                                year,
                                month,
                                metric
                            );
                    const trend = calculateTrend(value, prevValue);
                    
                    if (prevValue > 0) {
                        tooltipContent += `Previous Month: ${prevValue}<br>`;
                        tooltipContent += `Change: ${trend.percentage}% `;
                        
                                if (trend.direction === "up") {
                            tooltipContent += '<span style="color:#28a745">▲</span>';
                                } else if (trend.direction === "down") {
                            tooltipContent += '<span style="color:#dc3545">▼</span>';
                        }
                    }
                }
                
                // Set the tooltip content
                    cell.setAttribute("title", tooltipContent);
                
                // Calculate and show trend if enabled
                    if (
                        document.getElementById("showTrends") &&
                        document.getElementById("showTrends").checked &&
                        month > 1
                    ) {
                        const prevValue = getPreviousMonthValue(
                            contributor,
                            year,
                            month,
                            metric
                        );
                    const trend = calculateTrend(value, prevValue);
                    
                    // Only show trend indicators for non-zero values
                    if (value > 0 && prevValue > 0) {
                            const trendSpan = document.createElement("span");
                            trendSpan.classList.add("trend-indicator");
                            trendSpan.style.marginLeft = "4px";
                        
                        // Add arrow based on trend direction
                            if (trend.direction === "up") {
                                trendSpan.innerHTML = "&#x25B2;"; // Up arrow
                                trendSpan.classList.add("trend-up");
                                trendSpan.setAttribute(
                                    "aria-label",
                                    `${trend.percentage}% increase from previous month`
                                );
                            } else if (trend.direction === "down") {
                                trendSpan.innerHTML = "&#x25BC;"; // Down arrow
                                trendSpan.classList.add("trend-down");
                                trendSpan.setAttribute(
                                    "aria-label",
                                    `${trend.percentage}% decrease from previous month`
                                );
                            }
                            
                            // Add the trend indicator to the wrapper
                            wrapper.appendChild(trendSpan);
                    }
                }
                
                cell.appendChild(wrapper);
                    row.appendChild(cell);
                }
            });
            
            return row;
        };

        // Generate table rows
        this.data.processedData.forEach((contributor) => {
            const row = buildRow(contributor);
            fragment.appendChild(row);
        });
        
        // Add all rows to the table at once
        tbody.appendChild(fragment);
        
        // Initialize tooltips with performance optimization
        if (!isMobile && window.bootstrap && window.bootstrap.Tooltip) {
            // Use a delayed initialization on desktop to improve initial rendering speed
            setTimeout(() => {
                const tooltipTriggerList = [].slice.call(
                    document.querySelectorAll('[data-bs-toggle="tooltip"]')
                );
                
                // Batch initialize tooltips in smaller groups to prevent UI freezing
                const batchSize = 50;
                let currentBatch = 0;
                
                const initBatch = () => {
                    const start = currentBatch * batchSize;
                    const end = Math.min(start + batchSize, tooltipTriggerList.length);
                    
                    if (start < tooltipTriggerList.length) {
                        for (let i = start; i < end; i++) {
                            new bootstrap.Tooltip(tooltipTriggerList[i], {
                                container: "body",
                                html: true,
                                trigger: "hover focus", // Only show on hover/focus for better performance
                                delay: { show: 200, hide: 100 }, // Add slight delay to prevent flickering
                            });
                        }
                        
                        currentBatch++;
                        
                        // Schedule next batch
                        if (currentBatch * batchSize < tooltipTriggerList.length) {
                            setTimeout(initBatch, 100);
                        }
                    }
                };
                
                // Start initializing tooltips in batches
                initBatch();
                
                console.log("Tooltips initialized in batches");
            }, 500); // Wait for table to be fully rendered
        }
        
        // Apply any active filters after rendering
        this.applyContributorFilters();
    },
    
    // Export yearly data to Excel
    exportYearlyData: function () {
        // Get currently selected metric
        const activeMetric = document
            .querySelector("#advancedAnalysisModal button[data-metric].active")
            .getAttribute("data-metric");
        
        // Determine the years we're exporting
        let years = document.getElementById("showAllYears").checked
            ? this.getAllYears()
            : [...document.querySelectorAll(".year-cbx:checked")].map(
                  (c) => +c.dataset.year,
              );
        
        // Get metric display name
        const metricDisplayName =
            {
                totalEvents: "Total Events",
                mrCreated: "MRs Created",
                mrApproved: "MRs Approved",
                repoPushes: "Repository Pushes",
        }[activeMetric] || activeMetric;
        
        // Create CSV data
        const table = document.getElementById("yearlyOverviewTable");
        const rows = table.querySelectorAll("tr");
        const csv = [];
        
        // Add metadata headers
        csv.push(
            [
                "Advanced Contribution Analysis",
            `Metric: ${metricDisplayName}`,
                `Years: ${years.join(", ")}`,
                `Generated: ${new Date().toLocaleString()}`,
            ].join(","),
        );
        csv.push([]); // Empty row for spacing
        
        // Add table data
        for (let i = 0; i < rows.length; i++) {
            const row = [],
                cols = rows[i].querySelectorAll("td, th");
            
            for (let j = 0; j < cols.length; j++) {
                // Clean the data and escape double quotes
                let data = cols[j].textContent
                    .replace(/(\r\n|\n|\r)/gm, "")
                    .replace(/(\s\s)/gm, " ");
                data = data.replace(/"/g, '""').replace(/▲|▼|■/g, ""); // Remove trend icons
                row.push('"' + data + '"');
            }
            
            csv.push(row.join(","));
        }
        
        // Create and download the CSV file
        const csvContent = csv.join("\n");
        const encodedUri =
            "data:text/csv;charset=utf-8," + encodeURIComponent(csvContent);
        const yearRange =
            years.length > 1
                ? `${Math.min(...years)}-${Math.max(...years)}`
                : years[0];
        const fileName = `contribution_analysis_${metricDisplayName.replace(/\s+/g, "_").toLowerCase()}_${yearRange}_${new Date().toISOString().slice(0, 10)}.csv`;

        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", fileName);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    },
    
    // Destroy charts
    destroyCharts: function () {
        if (this.charts.yearlyOverview) {
            this.charts.yearlyOverview.destroy();
            this.charts.yearlyOverview = null;
        }
    },
    
    // Set up squad management functionality
    setupSquadManagement: function () {
        // Add event listener for save all squad assignments
        const saveAllBtn = document.getElementById("saveAllSquadChanges");
        if (saveAllBtn) {
            saveAllBtn.addEventListener("click", () => {
                this.saveAllSquadChanges();
            });
        }
    },
    
    // Save all squad changes
    saveAllSquadChanges: function () {
        // Show confirmation about the changes
        const confirmation = confirm(
            "Are you sure you want to save all squad changes?",
        );
        if (!confirmation) return;
        
        // Show success message
        const alertMessage = document.createElement("div");
        alertMessage.className =
            "alert alert-success alert-dismissible fade show";
        alertMessage.setAttribute("role", "alert");
        alertMessage.innerHTML = `
            <strong>Success!</strong> All squad assignments have been saved.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Add the alert to the squad management modal
        const squadManagementContainer = document.querySelector(
            ".squad-management-container",
        );
        if (squadManagementContainer) {
            squadManagementContainer.prepend(alertMessage);
        }
        
        // Close the modal after a delay
        setTimeout(() => {
            try {
                const squadModal = document.getElementById(
                    "squadManagementModal",
                );
                if (squadModal) {
                    const bsModal = bootstrap.Modal.getInstance(squadModal);
                    if (bsModal) {
                        bsModal.hide();
                    }
                }
            } catch (e) {
                console.warn("Error closing modal:", e);
            }
        }, 2000);
    },
    
    // Open squad management modal
    openSquadManagementModal: function () {
        console.log("Opening squad management modal");
        
        // Get or create the modal element
        let squadModal = document.getElementById("squadManagementModal");
        
        // If no modal exists, create one
        if (!squadModal) {
            squadModal = document.createElement("div");
            squadModal.id = "squadManagementModal";
            squadModal.className = "modal fade";
            squadModal.setAttribute("tabindex", "-1");
            squadModal.setAttribute("role", "dialog");
            squadModal.setAttribute(
                "aria-labelledby",
                "squadManagementModalLabel",
            );
            squadModal.innerHTML = `
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="squadManagementModalLabel">Manage Squad Assignments</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body squad-management-container">
                            <!-- Content will be added dynamically -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-success" id="saveAllSquadChanges">Save All Changes</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(squadModal);
        }

        // Mark this as a nested modal since we're opening it from within another modal
        squadModal.classList.add("nested-modal");
        
        // Apply the modal content
        this.populateSquadManagementModal(squadModal);
        
        // Create and show the Bootstrap modal
        const bsModal = new bootstrap.Modal(squadModal);
        
        // Mark body as having double modal
        document.body.classList.add("double-modal");
        
        // Show the modal
        bsModal.show();
        
        // Clean up properly when modal is hidden
        squadModal.addEventListener("hidden.bs.modal", function (event) {
            // Clean up classes
            document.body.classList.remove("double-modal");
            squadModal.classList.remove("nested-modal");
            
            // Dispose the modal instance
            try {
                bsModal.dispose();
            } catch (e) {
                console.warn("Error disposing modal:", e);
            }
            
            // Clean up any lingering backdrops
            const backdrops = document.querySelectorAll(".modal-backdrop");
            backdrops.forEach((backdrop, index) => {
                if (index > 0) backdrop.remove();
            });
        });
    },
    
    // Function to populate the squad management modal content
    populateSquadManagementModal: function (modalElement) {
        // Sort contributors by squad
        const contributorsBySquad = {};
        this.data.processedData.forEach((contributor) => {
            const squad = contributor.squad || "Unknown";
            if (!contributorsBySquad[squad]) {
                contributorsBySquad[squad] = [];
            }
            contributorsBySquad[squad].push(contributor);
        });

        // Get modal container
        const modalContainer = modalElement.querySelector(
            ".squad-management-container",
        );
        if (!modalContainer) {
            console.error("Modal container not found");
            return;
        }

        // Clean all children from the modal container except unknownSquadTable, noUnknownMessage and alert-info
        Array.from(modalContainer.children).forEach((child) => {
            if (
                !child.classList.contains("unknownSquadTable") &&
                !child.classList.contains("noUnknownMessage") &&
                !child.classList.contains("alert-info")
            ) {
                modalContainer.removeChild(child);
            }
        });

        // Find or create the table container
        let tableContainer = modalContainer.querySelector(".unknownSquadTable");
        if (!tableContainer) {
            tableContainer = document.createElement("div");
            tableContainer.className = "unknownSquadTable";
            modalContainer.appendChild(tableContainer);
        } else {
            // Clear existing table content
            tableContainer.innerHTML = "";
        }

        // Find or create the no unknown message
        let noUnknownMessage =
            modalContainer.querySelector(".noUnknownMessage");
        if (!noUnknownMessage) {
            noUnknownMessage = document.createElement("div");
            noUnknownMessage.className = "noUnknownMessage alert alert-success";
            noUnknownMessage.style.display = "none";
            noUnknownMessage.innerHTML =
                "Great! All contributors have been assigned to squads.";
            modalContainer.appendChild(noUnknownMessage);
        }

        // Show or hide the no unknown message
        const hasUnknownContributors =
            contributorsBySquad["Unknown"] &&
            contributorsBySquad["Unknown"].length > 0;
        noUnknownMessage.style.display = hasUnknownContributors
            ? "none"
            : "block";

        // Get all unique squads
        let allSquads = Object.keys(contributorsBySquad);
        
        // Make sure default squads are included
        const defaultSquads = [
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
        defaultSquads.forEach((squad) => {
            if (!allSquads.includes(squad)) {
                allSquads.push(squad);
            }
        });
        
        // Sort squads alphabetically, but keep "Unknown" at the end
        allSquads = allSquads.filter((squad) => squad !== "Unknown").sort();
        if (contributorsBySquad["Unknown"]) {
            allSquads.push("Unknown");
        }

        // Create a filter header with a unique ID
        const filterHeaderId = "squadFilterHeader-" + Date.now();
        const filterHeader = document.createElement("div");
        filterHeader.id = filterHeaderId;
        filterHeader.className = "filter-header squad-filter-header";
        filterHeader.innerHTML = `<h5>Filter by Squad:</h5>`;
        modalContainer.insertBefore(filterHeader, modalContainer.firstChild);

        // Create a squad list with a unique ID
        const squadListId = "squadList-" + Date.now();
        const squadList = document.createElement("div");
        squadList.id = squadListId;
        squadList.className = "squad-list-container";
        modalContainer.insertBefore(squadList, filterHeader.nextSibling);

        // Add squad tags
        const squadTags = document.createElement("div");
        squadTags.className = "squad-tags";
        allSquads.forEach((squad) => {
            const tag = document.createElement("span");
            tag.className = "squad-tag";
            tag.dataset.squad = squad;
            tag.textContent = squad;
            
            // Add delete button for squads (except "Unknown" and default squads)
            if (squad !== "Unknown" && !defaultSquads.includes(squad)) {
                const deleteBtn = document.createElement("i");
                deleteBtn.className = "fas fa-times delete-squad";
                deleteBtn.addEventListener("click", (e) => {
                    e.stopPropagation();
                    const currentSquad = squad; // Capture the current squad in closure
                    if (
                        confirm(
                            `Are you sure you want to delete the squad "${currentSquad}"? Contributors in this squad will be moved to "Unknown".`,
                        )
                    ) {
                        // Move contributors to "Unknown" squad
                        if (contributorsBySquad[currentSquad]) {
                            contributorsBySquad[currentSquad].forEach(
                                (contributor) => {
                                contributor.squad = "Unknown";
                                if (!contributorsBySquad["Unknown"]) {
                                    contributorsBySquad["Unknown"] = [];
                                }
                                    contributorsBySquad["Unknown"].push(
                                        contributor,
                                    );
                                },
                            );
                            delete contributorsBySquad[currentSquad];
                            
                            // Remove from teams list
                            const index = this.data.teams.indexOf(currentSquad);
                            if (index > -1) {
                                this.data.teams.splice(index, 1);
                            }
                            
                            // Show success message
                            const alertMessage = document.createElement("div");
                            alertMessage.className =
                                "alert alert-success alert-dismissible fade show";
                            alertMessage.setAttribute("role", "alert");
                            alertMessage.innerHTML = `
                                <strong>Success!</strong> Squad "${currentSquad}" has been deleted.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            `;
                            
                            // Add the alert to the squad management modal
                            modalContainer.prepend(alertMessage);
                            
                            // Refresh the modal
                            this.populateSquadManagementModal(modalElement);
                        }
                    }
                });
                tag.appendChild(deleteBtn);
            }
            
            tag.addEventListener("click", () => {
                // Filter the table to show only contributors in this squad
                const rows = tableContainer.querySelectorAll("tr");
                rows.forEach((row) => {
                    const rowSquad = row.dataset.squad;
                    if (rowSquad === squad || squad === "All") {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                });
                
                // Highlight the active squad
                const tags = squadTags.querySelectorAll(".squad-tag");
                tags.forEach((t) => t.classList.remove("active"));
                tag.classList.add("active");
            });
            squadTags.appendChild(tag);
        });
        
        // Add "Add New Squad" button
        const addNewSquadBtn = document.createElement("button");
        addNewSquadBtn.className = "btn btn-primary add-new-squad";
        addNewSquadBtn.style.marginLeft = "10px";
        addNewSquadBtn.style.marginBottom = "10px";
        addNewSquadBtn.innerHTML = '<i class="fas fa-plus"></i> Add New Squad';
        addNewSquadBtn.addEventListener("click", (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.addNewSquad.bind(this)();
        });
        squadTags.appendChild(addNewSquadBtn);
        
        squadList.appendChild(squadTags);
        
        // Create table for all contributors, not just unknown ones
        const table = document.createElement("table");
        table.className = "table table-striped";
        table.innerHTML = `
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Current Squad</th>
                    <th>Assign To</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        `;

        const tbody = table.querySelector("tbody");

        // Add rows for all contributors - not just unknown ones
        this.data.processedData.forEach((contributor) => {
            const tr = document.createElement("tr");
            tr.dataset.squad = contributor.squad || "Unknown";
            tr.dataset.contributorId = contributor.id;
            
            const tdName = document.createElement("td");
            tdName.textContent = contributor.name;
            tr.appendChild(tdName);
            
            const tdSquad = document.createElement("td");
            tdSquad.textContent = contributor.squad || "Unknown";
            tr.appendChild(tdSquad);
            
            const tdAssignTo = document.createElement("td");
            const selectSquad = document.createElement("select");
            selectSquad.className = "form-control squad-select";
            selectSquad.innerHTML = `
                <option value="">Select Squad</option>
                ${defaultSquads
                    .filter((squad) => squad !== "Unknown")
                    .map(
                        (squad) => `<option value="${squad}">${squad}</option>`,
                    )
                    .join("")}
            `;
            tdAssignTo.appendChild(selectSquad);
            tr.appendChild(tdAssignTo);
            
            const tdAction = document.createElement("td");
            const applyBtn = document.createElement("button");
            applyBtn.className = "btn btn-sm btn-primary";
            applyBtn.textContent = "Apply";
            applyBtn.addEventListener("click", () => {
                const squadValue = selectSquad.value;
                if (squadValue) {
                    // Save original squad for tracking
                    const originalSquad = contributor.squad || "Unknown";
                    
                    // Update contributor data
                    contributor.squad = squadValue;
                    tdSquad.textContent = squadValue;
                    tr.dataset.squad = squadValue;
                    
                    // Update in our data structure - move from original squad to new squad
                    // Remove from original squad
                    if (contributorsBySquad[originalSquad]) {
                        const index =
                            contributorsBySquad[originalSquad].indexOf(
                                contributor,
                            );
                        if (index > -1) {
                            contributorsBySquad[originalSquad].splice(index, 1);
                        }
                    }
                    
                    // Add to new squad
                    if (!contributorsBySquad[squadValue]) {
                        contributorsBySquad[squadValue] = [];
                    }
                    contributorsBySquad[squadValue].push(contributor);
                    
                    // Show success indicator
                    const successIcon = document.createElement("span");
                    successIcon.innerHTML =
                        ' <i class="fas fa-check text-success"></i>';
                    successIcon.style.opacity = "1";
                    tdAction.appendChild(successIcon);
                    
                    // Fade out after 2 seconds
                    setTimeout(() => {
                        successIcon.style.transition = "opacity 1s";
                        successIcon.style.opacity = "0";
                        setTimeout(() => {
                            tdAction.removeChild(successIcon);
                        }, 1000);
                    }, 2000);
                }
            });
            
            tdAction.appendChild(applyBtn);
            tr.appendChild(tdAction);
            
            tbody.appendChild(tr);
        });

        tableContainer.appendChild(table);
    },
    
    // Add new squad
    addNewSquad: function () {
        console.log("Opening new squad modal");
        
        // Close any existing new squad modal first
        try {
            const existingModal = document.getElementById("newSquadModal");
            if (existingModal) {
                const bsModal = bootstrap.Modal.getInstance(existingModal);
                if (bsModal) {
                    bsModal.hide();
                }
                document.body.removeChild(existingModal);
            }
        } catch (e) {
            console.warn("Error closing existing modal:", e);
        }
        
        // Create a fresh modal element
        const squadModal = document.createElement("div");
        squadModal.id = "newSquadModal";
        squadModal.className = "modal fade";
        squadModal.setAttribute("tabindex", "-1");
        squadModal.setAttribute("role", "dialog");
        squadModal.setAttribute("aria-labelledby", "newSquadModalLabel");
        squadModal.innerHTML = `
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="newSquadModalLabel">Add New Squad</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label for="newSquadName" class="form-label">Squad Name:</label>
                            <input type="text" class="form-control" id="newSquadName" placeholder="Enter squad name">
                            <div id="squadNameFeedback" class="invalid-feedback"></div>
                            <div id="similarSquadsContainer" class="mt-2" style="display:none;">
                                <p class="text-muted">Similar squads:</p>
                                <div id="similarSquads" class="squad-tags small"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="newSquadDescription" class="form-label">Description (optional):</label>
                            <textarea class="form-control" id="newSquadDescription" placeholder="Enter squad description"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="confirmAddSquadBtn">Add Squad</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(squadModal);
        
        // Mark this as a nested modal
        squadModal.classList.add("nested-modal");
        squadModal.style.zIndex = "1070"; // Higher than squadManagementModal
        
        // Add event listener for the squad name input
        const newSquadNameInput = squadModal.querySelector("#newSquadName");
        if (newSquadNameInput) {
            newSquadNameInput.addEventListener(
                "input",
                this.showSimilarSquads.bind(this),
            );
            
            // Focus the input after the modal is shown
            setTimeout(() => {
                newSquadNameInput.focus();
            }, 500);
        }
        
        // Add event listener for the confirm button
        const confirmButton = squadModal.querySelector("#confirmAddSquadBtn");
        if (confirmButton) {
            confirmButton.addEventListener("click", () => {
                // Get input values
                const squadName = newSquadNameInput.value.trim();
                const squadDescription = squadModal
                    .querySelector("#newSquadDescription")
                    .value.trim();
                
                // Validate input
                if (!squadName) {
                    const feedback =
                        squadModal.querySelector("#squadNameFeedback");
                    if (feedback) {
                        feedback.textContent = "Please enter a squad name";
                        feedback.style.display = "block";
                    }
                    return;
                }
                
                // Check if squad already exists
                if (this.data.teams.includes(squadName)) {
                    const feedback =
                        squadModal.querySelector("#squadNameFeedback");
                    if (feedback) {
                        feedback.textContent = "This squad already exists";
                        feedback.style.display = "block";
                    }
                    return;
                }
                
                // Add new squad
                this.data.teams.push(squadName);
                
                // Close modal
                const bsModal = bootstrap.Modal.getInstance(squadModal);
                if (bsModal) {
                    bsModal.hide();
                }
                
                // Show success message
                const alertMessage = document.createElement("div");
                alertMessage.className =
                    "alert alert-success alert-dismissible fade show";
                alertMessage.setAttribute("role", "alert");
                alertMessage.innerHTML = `
                    <strong>Success!</strong> Squad "${squadName}" has been added.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                
                // Find squad management container and prepend message
                const managementContainer = document.querySelector(
                    ".squad-management-container",
                );
                if (managementContainer) {
                    managementContainer.prepend(alertMessage);
                }
                
                // Refresh squad management modal
                this.populateSquadManagementModal(
                    document.getElementById("squadManagementModal"),
                );
            });
        }
        
        // Create and show Bootstrap modal
        const bsModal = new bootstrap.Modal(squadModal, {
            backdrop: "static", // Don't close when clicking outside
            keyboard: false, // Don't close when pressing escape
        });
        
        // Add special class to body for styling purposes
        document.body.classList.add("new-squad-modal-open");
        
        // Show the modal
        bsModal.show();
        
        // Clean up when modal is hidden
        squadModal.addEventListener("hidden.bs.modal", function () {
            document.body.classList.remove("new-squad-modal-open");
            
            // Clean up modal
            try {
                // Dispose the modal instance
                bsModal.dispose();
                
                // Remove modal element
                if (squadModal.parentNode) {
                    squadModal.parentNode.removeChild(squadModal);
                }
            } catch (e) {
                console.warn("Error cleaning up modal:", e);
            }
        });
    },
    
    // Show similar squads as suggestions
    showSimilarSquads: function (event) {
        const input = event.target.value.trim().toLowerCase();
        const similarSquadsContainer = document.getElementById(
            "similarSquadsContainer",
        );
        const similarSquadsElement = document.getElementById("similarSquads");
        const feedbackElement = document.getElementById("squadNameFeedback");
        
        // Clear previous content
        similarSquadsElement.innerHTML = "";
        feedbackElement.textContent = "";
        
        // Enable confirm button by default
        const confirmButton = document.getElementById("confirmAddSquadBtn");
        if (confirmButton) {
            confirmButton.disabled = false;
        }
        
        if (input.length < 2) {
            similarSquadsContainer.style.display = "none";
            return;
        }
        
        // Get all existing squads
        const existingSquads = this.data.teams || [];
        
        // Check for exact match
        const exactMatch = existingSquads.find(
            (squad) => squad.toLowerCase() === input,
        );
        if (exactMatch) {
            feedbackElement.textContent = "This squad already exists.";
            feedbackElement.style.display = "block";
            if (confirmButton) {
                confirmButton.disabled = true;
            }
            similarSquadsContainer.style.display = "none";
            return;
        }
        
        // Find similar squads
        const similarSquads = existingSquads.filter(
            (squad) =>
            squad.toLowerCase().includes(input) || 
                input.includes(squad.toLowerCase()),
        );
        
        if (similarSquads.length > 0) {
            // Display similar squads
            similarSquadsContainer.style.display = "block";

            similarSquads.forEach((squad) => {
                const tag = document.createElement("span");
                tag.className = "squad-tag clickable";
                tag.textContent = squad;
                tag.addEventListener("click", () => {
                    // Fill the input with the clicked squad name
                    document.getElementById("newSquadName").value = squad;
                    similarSquadsContainer.style.display = "none";
                    
                    // Show feedback that this squad already exists
                    feedbackElement.textContent = "This squad already exists.";
                    feedbackElement.style.display = "block";
                    if (confirmButton) {
                        confirmButton.disabled = true;
                    }
                });
                similarSquadsElement.appendChild(tag);
            });
        } else {
            similarSquadsContainer.style.display = "none";
        }
    },
    
    // Delete a squad
    deleteSquad: function (squadName, elementToRemove = null) {
        if (!squadName) return;
        
        // First check if any contributors belong to this squad
        const contributorsInSquad = this.data.processedData.filter(
            (c) => c.squad === squadName,
        );
        
        if (contributorsInSquad.length > 0) {
            // If there are contributors in this squad, ask for confirmation
            if (
                !confirm(
                    `The squad "${squadName}" has ${contributorsInSquad.length} contributor${contributorsInSquad.length > 1 ? "s" : ""}. These will be moved to "Unknown" if you delete this squad. Continue?`,
                )
            ) {
                return;
            }
            
            // Update contributors to Unknown
            this.data.processedData.forEach((contributor) => {
                if (contributor.squad === squadName) {
                    contributor.squad = "Unknown";
                }
            });
            
            // Update in raw contributions data
            this.data.contributions.forEach((item) => {
                if (item.squad === squadName) {
                    item.squad = "Unknown";
                }
            });
        } else {
            // If no contributors, simple confirmation
            if (
                !confirm(
                    `Are you sure you want to delete the squad "${squadName}"?`,
                )
            ) {
                return;
            }
        }
        
        // Remove from teams list
        const index = this.data.teams.indexOf(squadName);
        if (index > -1) {
            this.data.teams.splice(index, 1);
        }
        
        // Remove from UI if element provided
        if (elementToRemove) {
            elementToRemove.remove();
        }
        
        // Show confirmation
        alert(`Squad "${squadName}" has been deleted.`);
        
        // If we're in the squad management modal, refresh it
        if (
            document
                .getElementById("squadManagementModal")
                .classList.contains("show")
        ) {
            // Refresh the squad management modal
            this.openSquadManagementModal();
        }
        
        // Update the main table view if needed
        const activeMetric = document
            .querySelector("#advancedAnalysisModal button[data-metric].active")
            ?.getAttribute("data-metric");
        if (activeMetric) {
            // Get years based on selection
            let years;
            if (document.getElementById("showAllYears")?.checked) {
                years = this.getAllYears();
            } else {
                years = [...document.querySelectorAll('.year-cbx:checked')].map(c => +c.dataset.year);
            }
            
            // Re-render the table with updated data
            this.renderYearlyOverviewTable(activeMetric, years);
        }
    },
    
    // Set up contributor filters
    setupContributorFilters: function () {
        const squadFilterSelect = document.getElementById("squadFilter");
        const nameFilterInput = document.getElementById("nameFilter");
        const excludeSquadsCheckbox = document.getElementById("excludeSquads");
        const resetFiltersBtn = document.getElementById("resetFiltersBtn");
        
        if (!squadFilterSelect || !nameFilterInput || !excludeSquadsCheckbox) {
            console.warn("Filter elements not found");
            return;
        }
        
        // Clear previous options
        squadFilterSelect.innerHTML = "";

        // Use the DEFAULT_SQUADS constant instead of hardcoding
        console.log(
            "Populating squad filter with DEFAULT_SQUADS options:",
            DEFAULT_SQUADS,
        );
        
        // Add all squads to dropdown
        DEFAULT_SQUADS.forEach((squad) => {
            const option = document.createElement("option");
            option.value = squad;
            option.textContent = squad;

            // Select 'All' by default
            if (squad === "All") {
                option.selected = true;
            }

            squadFilterSelect.appendChild(option);
        });
        
        // Add event listeners for filters - each applies independently
        squadFilterSelect.addEventListener("change", (e) => {
            console.log("Squad filter changed to:", e.target.value);
            // Apply the squad filter immediately without waiting
            this.applyContributorFilters();
        });
        
        // Use debounced function for name filter to avoid too many updates
        nameFilterInput.addEventListener(
            "input",
            debounce(() => {
            // Only apply the name filter, don't reset other filters
            this.applyContributorFilters();
            }, 300),
        );

        // Add direct event handler for the excludeSquads checkbox
        if (excludeSquadsCheckbox) {
            excludeSquadsCheckbox.addEventListener("change", () => {
                console.log(
                    "Checkbox changed! Current checked state:",
                    excludeSquadsCheckbox.checked,
                );
                // Apply filter immediately when checkbox is changed
            this.applyContributorFilters();
        });
        }
        
        // Add event listener for reset filters button
        if (resetFiltersBtn) {
            resetFiltersBtn.addEventListener("click", () => {
                // Reset all filters to default values
                squadFilterSelect.value = "All";
                nameFilterInput.value = "";
                excludeSquadsCheckbox.checked = false;
                
                // Apply the reset filters
                this.applyContributorFilters();
            });
        }
        
        // To clear the search, add a small x button that appears when text is entered
        const nameFilterContainer = nameFilterInput.parentElement;
        if (nameFilterContainer) {
            // Add position relative to the container for absolute positioning of the button
            nameFilterContainer.style.position = "relative";
            
            // Create clear button
            const clearButton = document.createElement("button");
            clearButton.type = "button";
            clearButton.className = "btn btn-sm text-muted clear-search-btn";
            clearButton.innerHTML = "&times;";
            clearButton.style.position = "absolute";
            clearButton.style.right = "10px";
            clearButton.style.top = "50%";
            clearButton.style.transform = "translateY(-50%)";
            clearButton.style.display = "none";
            clearButton.style.background = "none";
            clearButton.style.border = "none";
            clearButton.style.fontSize = "1.5rem";
            clearButton.style.cursor = "pointer";
            clearButton.style.zIndex = "5";

            clearButton.addEventListener("click", () => {
                nameFilterInput.value = "";
                clearButton.style.display = "none";
                // Just clear the name filter without affecting other filters
                this.applyContributorFilters();
                nameFilterInput.focus();
            });
            
            nameFilterContainer.appendChild(clearButton);
            
            // Show/hide clear button based on input content
            nameFilterInput.addEventListener("input", () => {
                clearButton.style.display = nameFilterInput.value
                    ? "block"
                    : "none";
            });
        }
        
        // Apply filters initially to set the initial state
        this.applyContributorFilters();
    },
    
    // Apply contributor filters to the table
    applyContributorFilters: function () {
        const squadFilter =
            document.getElementById("squadFilter")?.value || "All";
        const nameFilter =
            document
                .getElementById("nameFilter")
                ?.value?.toLowerCase()
                .trim() || "";
        const excludeSquads =
            document.getElementById("excludeSquads")?.checked || false;

        console.log("Applying filters:", {
            squadFilter,
            nameFilter,
            excludeSquads,
        });
        
        // Update visual indication of active filters in the filter section
        const filterSection = document.querySelector(".filter-section");
        if (filterSection) {
            if (squadFilter !== "All" || nameFilter || excludeSquads === true) {
                filterSection.classList.add("active-filtering");
            } else {
                filterSection.classList.remove("active-filtering");
            }
        }
        
        // Get all rows in the table
        const rows = document.querySelectorAll("#yearlyOverviewData tr");
        
        // Keep track of how many rows are visible for reporting
        let visibleRows = 0;
        
        // Squads to INCLUDE when "Show Selected Squad Only" is checked
        const includedSquads = ["Core", "Grape", "Ops", "PC", "Shopex"];

        rows.forEach((row) => {
            // Get squad and name values from the row
            const squadCell = row.querySelector("td.sticky-col-1");
            const nameCell = row.querySelector("td.sticky-col-2");
            
            if (!squadCell || !nameCell) return;
            
            const rowSquad = squadCell.textContent.trim();
            const rowName = nameCell.textContent.trim();
            
            // Store original name text content for restoring after filtering
            if (!row.dataset.originalName) {
                row.dataset.originalName = rowName;
            }
            const originalName = row.dataset.originalName;
            
            // Apply each filter independently
            let shouldShowSquad = true;
            let shouldShowName = true;
            let shouldShowSquadOnly = true;
            
            // Apply squad filter
            if (squadFilter !== "All" && rowSquad !== squadFilter) {
                shouldShowSquad = false;
            }
            
            // Apply name filter - use case-insensitive search on the original name
            if (
                nameFilter &&
                !originalName.toLowerCase().includes(nameFilter.toLowerCase())
            ) {
                shouldShowName = false;
            }
            
            // Apply the "Show Selected Squad Only" filter
            if (excludeSquads && !INCLUDED_SQUADS.includes(rowSquad)) {
                shouldShowSquadOnly = false;
            }
            
            // Only show the row if it passes ALL filters
            const shouldShow =
                shouldShowSquad && shouldShowName && shouldShowSquadOnly;
            
            // Apply the filter
            if (shouldShow) {
                row.classList.remove("filtered");
                visibleRows++;
                
                // Highlight the match in the name if there's a name filter
                if (nameFilter) {
                    // Reset content first
                    nameCell.textContent = originalName;
                    
                    // Highlight the matched text
                    const matchIndex = originalName
                        .toLowerCase()
                        .indexOf(nameFilter.toLowerCase());
                    if (matchIndex >= 0) {
                        const beforeMatch = originalName.substring(
                            0,
                            matchIndex,
                        );
                        const match = originalName.substring(
                            matchIndex,
                            matchIndex + nameFilter.length,
                        );
                        const afterMatch = originalName.substring(
                            matchIndex + nameFilter.length,
                        );
                        
                        nameCell.innerHTML = `${beforeMatch}<span class="highlight-match">${match}</span>${afterMatch}`;
                    }
                } else {
                    // If no name filter, ensure original name is shown
                    nameCell.textContent = originalName;
                }
            } else {
                row.classList.add("filtered");
            }
        });
        
        console.log(
            `Filter applied: ${visibleRows} rows visible out of ${rows.length}`,
        );
        
        // Update filter indicators
        this.updateFilterIndicators(
            squadFilter,
            nameFilter,
            excludeSquads,
            visibleRows,
        );
    },
    
    // Update filter indicators to show active filters
    updateFilterIndicators: function (
        squadFilter,
        nameFilter,
        excludeSquads,
        visibleRows,
    ) {
        const squadFilterEl = document.getElementById("squadFilter");
        const nameFilterEl = document.getElementById("nameFilter");
        const excludeSquadsEl = document.getElementById("excludeSquads");
        
        // Highlight active filters
        if (squadFilter !== "All") {
            squadFilterEl?.classList.add("filter-active");
        } else {
            squadFilterEl?.classList.remove("filter-active");
        }
        
        if (nameFilter) {
            nameFilterEl?.classList.add("filter-active");
        } else {
            nameFilterEl?.classList.remove("filter-active");
        }
        
        if (excludeSquads) {
            excludeSquadsEl
                ?.closest(".form-check")
                ?.classList.add("text-primary");
        } else {
            excludeSquadsEl
                ?.closest(".form-check")
                ?.classList.remove("text-primary");
        }
        
        // Update filter status in the modal footer
        let modalFooter = document.querySelector(
            "#advancedAnalysisModal .modal-footer",
        );
        if (!modalFooter) return;
        
        // Get or create the filter status container
        let filterStatusContainer = modalFooter.querySelector(
            ".filter-status-container",
        );
        if (!filterStatusContainer) {
            filterStatusContainer = document.createElement("div");
            filterStatusContainer.className = "filter-status-container me-auto";
            modalFooter.insertBefore(
                filterStatusContainer,
                modalFooter.firstChild,
            );
        }
        
        // Clear existing content
        filterStatusContainer.innerHTML = "";
        
        // Check if we have any active filters
        const hasActiveFilters =
            squadFilter !== "All" || nameFilter || excludeSquads === true;
        
        // Create the filter status element
        const filterInfo = document.createElement("div");
        filterInfo.className =
            "filter-info d-flex align-items-center flex-wrap";
        
        // Create showing count
        const showingCount = document.createElement("div");
        showingCount.className = "showing-count me-2";
        
        // Change text based on filtering status
        const totalRows = document.querySelectorAll(
            "#yearlyOverviewData tr",
        ).length;
        if (hasActiveFilters) {
            showingCount.innerHTML = `<span class="filter-indicator">Showing <strong>${visibleRows}</strong> of ${totalRows} contributors</span>`;
        } else {
            showingCount.innerHTML = `<span class="filter-indicator">Showing all ${totalRows} contributors</span>`;
        }
        
        filterInfo.appendChild(showingCount);
        
        // Only add badges if we have active filters
        if (hasActiveFilters) {
            // Create badges container
            const badgesContainer = document.createElement("div");
            badgesContainer.className =
                "filter-badges d-flex align-items-center flex-wrap";
            
            // Add squad filter badge if active
            if (squadFilter !== "All") {
                const badge = document.createElement("span");
                badge.className = "filter-badge bg-primary me-1 mb-1";
                badge.innerHTML = `<span>Squad: ${squadFilter}</span>`;
                
                // Add clear button to badge
                const clearBtn = document.createElement("button");
                clearBtn.className = "badge-clear-btn ms-1";
                clearBtn.innerHTML = "&times;";
                clearBtn.setAttribute("aria-label", "Clear squad filter");
                clearBtn.addEventListener("click", () => {
                    if (squadFilterEl) {
                        squadFilterEl.value = "All";
                        this.applyContributorFilters();
                    }
                });
                
                badge.appendChild(clearBtn);
                badgesContainer.appendChild(badge);
            }
            
            // Add name filter badge if active
            if (nameFilter) {
                const badge = document.createElement("span");
                badge.className = "filter-badge bg-info me-1 mb-1";
                badge.innerHTML = `<span>Name: ${nameFilter}</span>`;
                
                // Add clear button to badge
                const clearBtn = document.createElement("button");
                clearBtn.className = "badge-clear-btn ms-1";
                clearBtn.innerHTML = "&times;";
                clearBtn.setAttribute("aria-label", "Clear name filter");
                clearBtn.addEventListener("click", () => {
                    if (nameFilterEl) {
                        nameFilterEl.value = "";
                        // Also hide the clear button in the input
                        const clearSearchBtn =
                            nameFilterEl.parentElement.querySelector(
                                ".clear-search-btn",
                            );
                        if (clearSearchBtn) {
                            clearSearchBtn.style.display = "none";
                        }
                        this.applyContributorFilters();
                    }
                });
                
                badge.appendChild(clearBtn);
                badgesContainer.appendChild(badge);
            }
            
            // Add exclude badge if active (since default is now false)
            if (excludeSquads === true) {
                const badge = document.createElement("span");
                badge.className = "filter-badge bg-primary me-1 mb-1";
                badge.innerHTML = "<span>Showing: Core Squads Only</span>";
                
                // Add clear button to badge
                const clearBtn = document.createElement("button");
                clearBtn.className = "badge-clear-btn ms-1";
                clearBtn.innerHTML = "&times;";
                clearBtn.setAttribute("aria-label", "Show all squads");
                clearBtn.addEventListener("click", () => {
                    if (excludeSquadsEl) {
                        excludeSquadsEl.checked = false;
                        this.applyContributorFilters();
                    }
                });
                
                badge.appendChild(clearBtn);
                badgesContainer.appendChild(badge);
            }
            
            // Add a clear all filters button
            const clearAllBtn = document.createElement("button");
            clearAllBtn.className =
                "btn btn-sm btn-outline-secondary mb-1 ms-2";
            clearAllBtn.innerHTML =
                '<i class="bi bi-x-circle me-1"></i> Clear All';
            clearAllBtn.addEventListener("click", () => {
                // Reset all filters to default values
                if (squadFilterEl) squadFilterEl.value = "All";
                if (nameFilterEl) nameFilterEl.value = "";
                if (excludeSquadsEl) excludeSquadsEl.checked = false;
                
                // Apply the reset filters
                this.applyContributorFilters();
            });
            badgesContainer.appendChild(clearAllBtn);
            
            filterInfo.appendChild(badgesContainer);
        }
        
        filterStatusContainer.appendChild(filterInfo);
        
        // Add or remove active class based on filter status
        if (hasActiveFilters) {
            filterStatusContainer.classList.add("has-filters");
            // Add subtle background to indicate filtering is active
            modalFooter.classList.add("has-active-filters");
        } else {
            filterStatusContainer.classList.remove("has-filters");
            modalFooter.classList.remove("has-active-filters");
        }
    },

    refreshYearlyOverview: function() {
        // Get the active metric
        const activeMetric = document.querySelector('#advancedAnalysisModal button[data-metric].active')
            ?.getAttribute('data-metric');
        if (activeMetric) {
            // Get years based on selection
            let years;
            if (document.getElementById('showAllYears')?.checked) {
                years = this.getAllYears();
            } else {
                years = [...document.querySelectorAll('.year-cbx:checked')].map(c => +c.dataset.year);
            }

            // Re-render the table with updated data
            this.renderYearlyOverviewTable(activeMetric, years);
        }
    },
};

// Add a static method for direct HTML onclick access
AdvancedAnalytics.directFilterToggle = function () {
    AdvancedAnalytics.applyContributorFilters.call(AdvancedAnalytics);
};

// Export the module
window.AdvancedAnalytics = AdvancedAnalytics; 
