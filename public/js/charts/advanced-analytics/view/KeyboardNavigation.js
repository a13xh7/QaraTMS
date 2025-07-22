/**
 * KeyboardNavigation.js
 * Enhances keyboard navigation for tables and interactive elements
 */

import logger from '../common/logger.js';

/**
 * KeyboardNavigation - Adds keyboard navigation support to data tables
 * Allows users to navigate using arrow keys, Enter, Space, Home, End, etc.
 */
class KeyboardNavigation {
    /**
     * Create a new KeyboardNavigation instance
     * @param {Object} options - Configuration options
     * @param {string} [options.tableSelector='#contributor-table'] - Selector for the data table
     * @param {string} [options.cellSelector='.data-cell'] - Selector for navigable cells
     * @param {string} [options.activeClass='keyboard-focus'] - Class added to focused cell
     * @param {Function} [options.onCellFocus=null] - Callback when a cell gets focus
     * @param {Function} [options.onCellActivate=null] - Callback when cell is activated (Enter/Space)
     */
    constructor(options = {}) {
        this.options = {
            tableSelector: options.tableSelector || '#contributor-table',
            cellSelector: options.cellSelector || '.data-cell',
            activeClass: options.activeClass || 'keyboard-focus',
            onCellFocus: options.onCellFocus || null,
            onCellActivate: options.onCellActivate || null
        };
        
        // State for current focus
        this.state = {
            enabled: false,
            table: null,
            currentCell: null,
            grid: [],
            rows: 0,
            cols: 0
        };
        
        // Bind methods
        this._bindMethods();
    }
    
    /**
     * Bind class methods to maintain 'this' context
     * @private
     */
    _bindMethods() {
        this.enable = this.enable.bind(this);
        this.disable = this.disable.bind(this);
        this.updateGrid = this.updateGrid.bind(this);
        this._handleKeyDown = this._handleKeyDown.bind(this);
        this._handleTableFocus = this._handleTableFocus.bind(this);
        this._handleTableBlur = this._handleTableBlur.bind(this);
        this._focusCell = this._focusCell.bind(this);
    }
    
    /**
     * Enable keyboard navigation
     * @returns {boolean} True if successfully enabled
     */
    enable() {
        if (this.state.enabled) {
            return true;
        }
        
        // Find the table element
        const table = document.querySelector(this.options.tableSelector);
        if (!table) {
            logger.warn(`Keyboard navigation: Table not found (${this.options.tableSelector})`);
            return false;
        }
        
        this.state.table = table;
        
        // Build the navigation grid
        this.updateGrid();
        
        // Add keyboard event listeners
        table.addEventListener('keydown', this._handleKeyDown);
        
        // Make table focusable if it isn't already
        if (!table.hasAttribute('tabindex')) {
            table.setAttribute('tabindex', '0');
        }
        
        // Handle focus events on the table
        table.addEventListener('focus', this._handleTableFocus);
        table.addEventListener('blur', this._handleTableBlur);
        
        // Mark as enabled
        this.state.enabled = true;
        
        logger.info('Keyboard navigation enabled');
        return true;
    }
    
    /**
     * Disable keyboard navigation
     */
    disable() {
        if (!this.state.enabled || !this.state.table) {
            return;
        }
        
        // Remove event listeners
        this.state.table.removeEventListener('keydown', this._handleKeyDown);
        this.state.table.removeEventListener('focus', this._handleTableFocus);
        this.state.table.removeEventListener('blur', this._handleTableBlur);
        
        // Clear current focus
        if (this.state.currentCell) {
            this.state.currentCell.classList.remove(this.options.activeClass);
            this.state.currentCell = null;
        }
        
        // Reset state
        this.state.enabled = false;
        this.state.table = null;
        this.state.grid = [];
        this.state.rows = 0;
        this.state.cols = 0;
        
        logger.info('Keyboard navigation disabled');
    }
    
    /**
     * Update navigation grid after table data changes
     */
    updateGrid() {
        if (!this.state.table) {
            return;
        }
        
        // Find all navigable cells
        const rows = this.state.table.querySelectorAll('tr');
        const grid = [];
        
        rows.forEach(row => {
            const cellsInRow = Array.from(row.querySelectorAll(this.options.cellSelector));
            if (cellsInRow.length > 0) {
                grid.push(cellsInRow);
            }
        });
        
        this.state.grid = grid;
        this.state.rows = grid.length;
        this.state.cols = grid.length > 0 ? Math.max(...grid.map(row => row.length)) : 0;
        
        logger.debug(`Navigation grid updated: ${this.state.rows} rows, ${this.state.cols} columns`);
        
        // If we had a focused cell, try to find its new position
        if (this.state.currentCell) {
            const stillExists = this.state.grid.some(row => row.includes(this.state.currentCell));
            if (!stillExists) {
                this.state.currentCell.classList.remove(this.options.activeClass);
                this.state.currentCell = null;
            }
        }
    }
    
    /**
     * Handle keydown events for keyboard navigation
     * @private
     * @param {KeyboardEvent} event - Keyboard event
     */
    _handleKeyDown(event) {
        if (!this.state.enabled || this.state.grid.length === 0) {
            return;
        }
        
        // Get current position
        let currentRow = -1;
        let currentCol = -1;
        
        if (this.state.currentCell) {
            for (let i = 0; i < this.state.grid.length; i++) {
                const j = this.state.grid[i].indexOf(this.state.currentCell);
                if (j !== -1) {
                    currentRow = i;
                    currentCol = j;
                    break;
                }
            }
        }
        
        // If no cell is focused yet, default to first cell
        if (currentRow === -1 || currentCol === -1) {
            this._focusCell(0, 0);
            return;
        }
        
        // Navigate based on key
        switch (event.key) {
            case 'ArrowUp':
                if (currentRow > 0) {
                    this._focusCell(currentRow - 1, currentCol);
                    event.preventDefault();
                }
                break;
                
            case 'ArrowDown':
                if (currentRow < this.state.rows - 1) {
                    this._focusCell(currentRow + 1, currentCol);
                    event.preventDefault();
                }
                break;
                
            case 'ArrowLeft':
                if (currentCol > 0) {
                    this._focusCell(currentRow, currentCol - 1);
                    event.preventDefault();
                }
                break;
                
            case 'ArrowRight':
                if (currentCol < this.state.grid[currentRow].length - 1) {
                    this._focusCell(currentRow, currentCol + 1);
                    event.preventDefault();
                }
                break;
                
            case 'Home':
                if (event.ctrlKey) {
                    // Go to first cell of first row
                    this._focusCell(0, 0);
                } else {
                    // Go to first cell of current row
                    this._focusCell(currentRow, 0);
                }
                event.preventDefault();
                break;
                
            case 'End':
                if (event.ctrlKey) {
                    // Go to last cell of last row
                    this._focusCell(this.state.rows - 1, this.state.grid[this.state.rows - 1].length - 1);
                } else {
                    // Go to last cell of current row
                    this._focusCell(currentRow, this.state.grid[currentRow].length - 1);
                }
                event.preventDefault();
                break;
                
            case 'PageUp':
                // Go up 5 rows or to the first row
                const newRow = Math.max(0, currentRow - 5);
                this._focusCell(newRow, currentCol);
                event.preventDefault();
                break;
                
            case 'PageDown':
                // Go down 5 rows or to the last row
                const newRowDown = Math.min(this.state.rows - 1, currentRow + 5);
                this._focusCell(newRowDown, currentCol);
                event.preventDefault();
                break;
                
            case 'Enter':
            case ' ': // Space key
                // Activate the current cell
                if (this.options.onCellActivate && typeof this.options.onCellActivate === 'function') {
                    this.options.onCellActivate(this.state.currentCell, currentRow, currentCol);
                    
                    // If cell has a tooltip, trigger it
                    if (this.state.currentCell.hasAttribute('data-bs-toggle') && 
                        this.state.currentCell.getAttribute('data-bs-toggle') === 'tooltip') {
                        const tooltipInstance = bootstrap.Tooltip.getInstance(this.state.currentCell);
                        if (tooltipInstance) {
                            tooltipInstance.toggle();
                        }
                    }
                }
                event.preventDefault();
                break;
                
            case 'Escape':
                // Clear focus
                if (this.state.currentCell) {
                    this.state.currentCell.classList.remove(this.options.activeClass);
                    this.state.currentCell = null;
                    
                    // Return focus to the table itself
                    this.state.table.focus();
                }
                event.preventDefault();
                break;
        }
    }
    
    /**
     * Handle focus events on the table
     * @private
     */
    _handleTableFocus() {
        // If no cell is currently focused, focus the first cell
        if (!this.state.currentCell && this.state.grid.length > 0 && this.state.grid[0].length > 0) {
            this._focusCell(0, 0);
        }
    }
    
    /**
     * Handle blur events on the table
     * @private
     */
    _handleTableBlur() {
        // Remove focus style but keep track of the cell
        if (this.state.currentCell) {
            this.state.currentCell.classList.remove(this.options.activeClass);
        }
    }
    
    /**
     * Focus a specific cell by coordinates
     * @private
     * @param {number} rowIndex - Row index
     * @param {number} colIndex - Column index
     */
    _focusCell(rowIndex, colIndex) {
        // Validate indices
        if (rowIndex < 0 || rowIndex >= this.state.rows || 
            colIndex < 0 || colIndex >= this.state.grid[rowIndex].length) {
            return;
        }
        
        // Remove focus from current cell
        if (this.state.currentCell) {
            this.state.currentCell.classList.remove(this.options.activeClass);
        }
        
        // Focus the new cell
        const newCell = this.state.grid[rowIndex][colIndex];
        newCell.classList.add(this.options.activeClass);
        this.state.currentCell = newCell;
        
        // Scroll cell into view if needed
        newCell.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest',
            inline: 'nearest'
        });
        
        // Call the focus callback if provided
        if (this.options.onCellFocus && typeof this.options.onCellFocus === 'function') {
            this.options.onCellFocus(newCell, rowIndex, colIndex);
        }
    }
    
    /**
     * Manually focus a specific cell
     * @param {number} rowIndex - Row index
     * @param {number} colIndex - Column index
     */
    focusCell(rowIndex, colIndex) {
        if (this.state.enabled) {
            this._focusCell(rowIndex, colIndex);
        }
    }
    
    /**
     * Get the current focused cell
     * @returns {Object|null} Cell info or null if no cell is focused
     */
    getCurrentFocus() {
        if (!this.state.currentCell) {
            return null;
        }
        
        // Find cell coordinates
        for (let rowIndex = 0; rowIndex < this.state.grid.length; rowIndex++) {
            const colIndex = this.state.grid[rowIndex].indexOf(this.state.currentCell);
            if (colIndex !== -1) {
                return {
                    element: this.state.currentCell,
                    row: rowIndex,
                    col: colIndex
                };
            }
        }
        
        return null;
    }
}

// Export the class
export default KeyboardNavigation; 