/**
 * constants.js
 * Central repository for all constants used throughout the advanced analytics module
 */

// ===== DATA CONSTANTS =====
// Years that should always be included regardless of data
export const FORCED_YEARS = [2023, 2024, 2025];

// Squads organization
export const DEFAULT_SQUADS = [
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

// Squads included in the "Show Selected Squad Only" filter
export const INCLUDED_SQUADS = ['Core', 'Grape', 'Ops', 'PC', 'Shopex'];

// Month name mappings for display
export const MONTH_NAMES = {
    short: Array.from({length: 12}, (_, i) => 
        new Date(2000, i, 1).toLocaleString("default", { month: "short" })),
    long: Array.from({length: 12}, (_, i) => 
        new Date(2000, i, 1).toLocaleString("default", { month: "long" }))
};

// ===== RENDERING CONSTANTS =====
// Batch sizes for performance optimizations
export const RENDER_BATCH_SIZE = 50;
export const TOOLTIP_BATCH_SIZE = 20;
export const TOOLTIP_INIT_DELAY = 500;
export const RENDER_DEBOUNCE_TIME = 100;

// Delays for various operations
export const TOOLTIP_SHOW_DELAY = 200;
export const TOOLTIP_HIDE_DELAY = 100;
export const MODAL_RENDER_DELAY = 500;
export const ANIMATION_DURATION = 300;
export const FADE_OUT_DURATION = 2000;

// Visual style constants
export const COLORS = {
    TABLE_HEADER_BG: '#f8f9fa',
    TABLE_BORDER: '#dee2e6',
    TABLE_GROUP_BORDER: '#ddd',
    LIGHT_GRAY: '#f8f9fa',
    SQUAD_ROW_DIVIDER: '2px solid #ddd',
    DROP_OPACITY: 0.8,
    TOOLTIP_FADE_START: 0.1,
    MIN_DROP_INTENSITY: 0.2,
    MAX_INTENSITY: 1.0,
    DROP_INTENSITY_DIVISOR: 100,
};

// Breakpoints for responsive design
export const BREAKPOINTS = {
    MOBILE: 767,
};

// Metric color schemes
export const METRIC_COLORS = {
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

// CSS Classes
export const CSS_CLASSES = {
    DROP: 'value-drop',
    ZERO: 'zero-value',
    CURRENT_MONTH: 'current-month',
    DISABLED_CELL: 'disabled-cell',
    ACTIVE: 'active',
    FILTERED: 'filtered',
    HIGHLIGHT_MATCH: 'highlight-match',
    TREND_UP: 'trend-up',
    TREND_DOWN: 'trend-down',
    TREND_NEUTRAL: 'trend-neutral',
};

// ===== DOM ELEMENT IDs =====
export const DOM_IDS = {
    CONTAINER: 'advanced-analytics-container',
    YEAR_SELECTOR: 'year-selector',
    SQUAD_SELECTOR: 'squad-selector',
    CONTRIBUTOR_SEARCH: 'contributor-search',
    CONTRIBUTOR_TABLE: 'contributor-table',
    STATUS_MESSAGE: 'status-message',
    LOADING_OVERLAY: '.loading-overlay',
    SHOW_ALL_YEARS: 'show-all-years',
    RESET_FILTERS: 'reset-filters',
    EXPORT_DATA: 'export-data',
};

// ===== METRIC DISPLAY NAMES =====
export const METRIC_DISPLAY_NAMES = {
    totalEvents: 'Total Events',
    mrCreated: 'MRs Created',
    mrApproved: 'MRs Approved',
    repoPushes: 'Repository Pushes',
}; 