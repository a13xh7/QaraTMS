document.addEventListener('DOMContentLoaded', function() {
    // Initialize success rate trend chart
    initSimpleChart();
    
    // Table sorting functionality
    initTableSorting();
    
    // Pagination page size handler
    const pageSizeSelect = document.getElementById('page-size-select');
    if (pageSizeSelect) {
        pageSizeSelect.addEventListener('change', function() {
            // Get the selected number of items per page
            const perPage = this.value;
            
            // Get current URL and update the parameters
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('per_page', perPage);
            currentUrl.searchParams.set('page', 1); // Reset to first page when changing page size
            
            // Show loading indicator
            this.disabled = true;
            const loadingIndicator = document.getElementById('page-size-loading');
            if (loadingIndicator) {
                loadingIndicator.classList.remove('d-none');
            }
            
            console.log("Changing to " + perPage + " items per page. Navigating to: " + currentUrl.toString());
            
            // Navigate to the updated URL
            window.location.href = currentUrl.toString();
        });
    }

    // JQUERY UI DATEPICKER IMPLEMENTATION
    $(document).ready(function() {
        console.log("Document ready, initializing jQuery UI date pickers...");
        
        // Remove Bootstrap datepicker if it was initialized
        if ($.fn.datepicker && typeof $.fn.datepicker.Constructor === 'function') {
            console.log("Destroying Bootstrap datepicker instances if they exist");
            try {
                $('#start_date_display, #end_date_display').datepicker('destroy');
            } catch (e) {
                console.log("No Bootstrap datepicker to destroy");
            }
        }
        
        // Configure jQuery UI datepicker
        $.datepicker.setDefaults({
            dateFormat: 'yy-mm-dd',
            showOtherMonths: true,
            selectOtherMonths: true,
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            closeText: "Close",
            currentText: "Today"
        });
        
        try {
            // Initialize the datepickers using jQuery UI
            $("#start_date_display").datepicker({
                onSelect: function(dateText) {
                    console.log("Start date selected:", dateText);
                    $("#start_date").val(dateText);
                }
            });
            
            $("#end_date_display").datepicker({
                onSelect: function(dateText) {
                    console.log("End date selected:", dateText);
                    $("#end_date").val(dateText);
                }
            });
            
            console.log("jQuery UI datepickers initialized");
            
            // Calendar button click handlers
            $("#from-calendar-btn").on("click", function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log("From calendar button clicked");
                $("#start_date_display").datepicker("show");
            });
            
            $("#to-calendar-btn").on("click", function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log("To calendar button clicked");
                $("#end_date_display").datepicker("show");
            });
            
            // Apply button handler
            $("#apply-date-range").on("click", function() {
                const startDate = $("#start_date_display").val();
                const endDate = $("#end_date_display").val();
                
                console.log("Apply button clicked with dates:", startDate, endDate);
                
                if (startDate && endDate) {
                    $("#start_date").val(startDate);
                    $("#end_date").val(endDate);
                    $("#dashboard-filters").submit();
                }
            });
            
            // Quick date range selection
            $(".quick-range").on("click", function(e) {
                e.preventDefault();
                const days = $(this).data("days");
                const range = $(this).data("range");
                
                console.log("Quick range selected:", days ? `Last ${days} days` : range);
                
                let fromDate, toDate;
                
                if (days) {
                    toDate = new Date();
                    fromDate = new Date();
                    fromDate.setDate(fromDate.getDate() - days);
                } else if (range) {
                    const now = new Date();
                    
                    if (range === "this-month") {
                        fromDate = new Date(now.getFullYear(), now.getMonth(), 1);
                        toDate = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                    } else if (range === "last-month") {
                        fromDate = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                        toDate = new Date(now.getFullYear(), now.getMonth(), 0);
                    }
                }
                
                if (fromDate && toDate) {
                    const fromStr = formatDate(fromDate);
                    const toStr = formatDate(toDate);
                    
                    console.log("Setting date range:", fromStr, "to", toStr);
                    
                    // Update display and hidden inputs
                    $("#start_date_display").val(fromStr);
                    $("#end_date_display").val(toStr);
                    $("#start_date").val(fromStr);
                    $("#end_date").val(toStr);
                    
                    // Submit form
                    $("#dashboard-filters").submit();
                }
            });
            
            // Manual input change handlers
            $("#start_date_display, #end_date_display").on("change", function() {
                const id = $(this).attr("id");
                const value = $(this).val();
                
                console.log(`Manual entry in ${id}:`, value);
                
                if (id === "start_date_display") {
                    $("#start_date").val(value);
                } else {
                    $("#end_date").val(value);
                }
            });
            
            // Show datepicker on focus
            $("#start_date_display, #end_date_display").on("focus", function() {
                const id = $(this).attr("id");
                console.log(`${id} focused, showing datepicker`);
                $(this).datepicker("show");
            });
            
            // Display current field values for debugging
            console.log("Current date values:", {
                startDisplay: $("#start_date_display").val(),
                endDisplay: $("#end_date_display").val(),
                startHidden: $("#start_date").val(),
                endHidden: $("#end_date").val()
            });
            
            // Try to show a datepicker after page load to see if it works
            setTimeout(function() {
                console.log("Attempting to force show datepicker for testing...");
                try {
                    $("#start_date_display").datepicker("show");
                } catch (e) {
                    console.error("Failed to show datepicker:", e);
                }
            }, 1000);
            
        } catch (error) {
            console.error("Error initializing jQuery UI datepickers:", error);
        }
    });
});

// Format date helper
function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

// SIMPLIFIED CHART FUNCTION - Creates a basic chart with test data
function initSimpleChart() {
    console.log("Initializing simplified chart");
    
    const ctx = document.getElementById('successRateChart');
    if (!ctx) {
        console.error("Chart canvas element not found!");
        return;
    }
    
    try {
        // Simple test data to verify chart functionality
        const labels = ['Mar 3', 'Mar 10', 'Mar 17', 'Mar 24', 'Mar 31'];
        const successData = [75, 82, 90, 68, 79];
        const failureData = labels.map((_, i) => 100 - successData[i]);
        
        console.log("Creating chart with static test data");
        console.log("Labels:", labels);
        console.log("Success rates:", successData);
        
        // Create a simple line chart
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Success Rate (%)',
                        data: successData,
                        backgroundColor: 'rgba(40, 167, 69, 0.2)',
                        borderColor: 'rgb(40, 167, 69)',
                        borderWidth: 2,
                        tension: 0.1,
                        fill: true
                    },
                    {
                        label: 'Failure Rate (%)',
                        data: failureData,
                        backgroundColor: 'rgba(220, 53, 69, 0.2)',
                        borderColor: 'rgb(220, 53, 69)',
                        borderWidth: 2,
                        tension: 0.1,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
        
        console.log("Chart created successfully:", chart);
        
        // Add event listener for period change
        const trendPeriod = document.getElementById('trend-period');
        if (trendPeriod) {
            trendPeriod.addEventListener('change', function() {
                // Simplified period change handler that just updates with new test data
                const period = parseInt(this.value);
                console.log("Period changed to:", period);
                
                // Generate different test data based on selected period
                let newLabels, newSuccessData;
                
                if (period === 7) {
                    newLabels = ['Mar 25', 'Mar 26', 'Mar 27', 'Mar 28', 'Mar 29', 'Mar 30', 'Mar 31'];
                    newSuccessData = [82, 78, 85, 90, 75, 80, 88];
                } else if (period === 14) {
                    newLabels = ['Mar 18', 'Mar 20', 'Mar 22', 'Mar 24', 'Mar 26', 'Mar 28', 'Mar 30'];
                    newSuccessData = [70, 82, 65, 88, 75, 90, 78];
                } else { // 30 days
                    newLabels = ['Mar 3', 'Mar 8', 'Mar 13', 'Mar 18', 'Mar 23', 'Mar 28', 'Mar 31'];
                    newSuccessData = [68, 75, 82, 90, 85, 78, 80];
                }
                
                const newFailureData = newSuccessData.map(val => 100 - val);
                
                // Update chart with new data
                chart.data.labels = newLabels;
                chart.data.datasets[0].data = newSuccessData;
                chart.data.datasets[1].data = newFailureData;
                chart.update();
            });
        }
    } catch (error) {
        console.error("Error creating chart:", error);
        
        // Display error directly in the chart area for debugging
        const chartContainer = ctx.parentElement;
        if (chartContainer) {
            chartContainer.innerHTML = `
                <div class="alert alert-danger">
                    <strong>Chart Error:</strong> ${error.message}
                </div>
            `;
        }
    }
}

// Table sorting functionality
function initTableSorting() {
    const sortableHeaders = document.querySelectorAll('.sortable');
    
    if (sortableHeaders.length > 0) {
        sortableHeaders.forEach(header => {
            header.addEventListener('click', function() {
                const sortField = this.dataset.sort;
                if (!sortField) return;
                
                // Get current URL and parameters
                const url = new URL(window.location.href);
                const currentSort = url.searchParams.get('sort') || '';
                const currentDirection = url.searchParams.get('direction') || 'asc';
                
                // Determine new sort direction
                let newDirection = 'asc';
                if (currentSort === sortField) {
                    newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
                }
                
                // Update URL parameters
                url.searchParams.set('sort', sortField);
                url.searchParams.set('direction', newDirection);
                
                // Navigate to the new URL
                window.location.href = url.toString();
            });
        });
    }
}

// Function to set quick date ranges - moved outside for global access
function setQuickRange(range) {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    const today = new Date();
    let startDate = new Date();
    let endDate = new Date();
    
    if (typeof range === 'number') {
        // For a number of days
        startDate.setDate(today.getDate() - range);
        endDate = today;
    } else if (range === 'this-month') {
        startDate = new Date(today.getFullYear(), today.getMonth(), 1);
        endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    } else if (range === 'last-month') {
        startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
        endDate = new Date(today.getFullYear(), today.getMonth(), 0);
    }
    
    // Format dates as YYYY-MM-DD
    startDateInput.value = formatDate(startDate);
    endDateInput.value = formatDate(endDate);
    
    // Submit the form
    document.getElementById('dashboard-filters').submit();
}
