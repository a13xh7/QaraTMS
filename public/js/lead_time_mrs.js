$(document).ready(function () {
    $('#authorZoomIcon').on('click', function () {
        // Show modal
        $('#authorChartModal').modal('show');

    });
    $('#authorChartModal').on('shown.bs.modal', function () {
        renderLargeAuthorChart();
        console.log("Modal shown, rendering large chart."); // Log untuk debugging
    });

    $('#authorAvgZoomIcon').on('click', function () {
        // Show modal
        $('#authorAvgChartModal').modal('show');

    });
    $('#authorAvgChartModal').on('shown.bs.modal', function () {
        renderLargeAuthorAvgChart();
        console.log("Modal shown, rendering large chart."); // Log untuk debugging
    });

    validateDates();
    setDateRange(document.getElementById('date-range').value);

    document.getElementById('date-range').addEventListener('change', function () {
        setDateRange(this.value);
    });

    document.getElementById('start_date').addEventListener('change', function () {
        document.getElementById('date-range').value = 'custom';
        validateDates();
    });

    document.getElementById('end_date').addEventListener('change', function () {
        document.getElementById('date-range').value = 'custom';
        validateDates();
    });

    // Load chart kecil MR Count Author
    loadTopContributor();
    loadAvgContributor()

    // Load chart-chart lainnya
    loadLeadTimeTrendChart();
    loadProjectLeadTimeChart();
});

function renderLargeAuthorChart() {

    if (!window.chartData || !window.chartData.authors) {
        console.error("Chart data or author data not available for large chart.");
        return;
    }

    const canvas = document.getElementById('authorMergeCountChartLarge');
    if (!canvas) {
        console.error("Canvas element with ID 'authorMergeCountChartLarge' not found for large chart rendering.");
        return;
    }

    const existingChart = Chart.getChart(canvas);
    if (existingChart) {
        existingChart.destroy();
        console.log("Existing chart on 'authorMergeCountChartLarge' destroyed before new one created.");
    }

    const ctxLarge = canvas.getContext('2d');

    window.authorMergeCountChartLarge = new Chart(ctxLarge, {
        type: 'bar',
        data: {
            labels: window.chartData.authors.labels,
            datasets: [{
                label: 'MR Count',
                data: window.chartData.authors.mrCounts,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                title: {
                    display: true,
                    text: 'MR Count per Author'
                }
            },
            scales: {
                x: {
                    ticks: {
                        font: { size: 14 },
                        maxRotation: 30,
                        minRotation: 0,
                        autoSkip: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 20
                    }
                }
            }
        }
    });
}

function renderLargeAuthorAvgChart() {
    if (!window.chartData || !window.chartData.authors) {
        console.error("Chart data or author data not available for large chart.");
        return;
    }

    const canvas = document.getElementById('authorMergeAvgChartLarge');
    if (!canvas) {
        console.error("Canvas element with ID 'authorMergeAvgChartLarge' not found for large chart rendering.");
        return;
    }

    const existingChart = Chart.getChart(canvas);
    if (existingChart) {
        existingChart.destroy();
        console.log("Existing chart on 'authorMergeAvgChartLarge' destroyed before new one created.");
    }

    const ctxLarge = canvas.getContext('2d');

    window.authorMergeAvgChartLarge = new Chart(ctxLarge, {
        type: 'bar',
        data: {
            labels: window.chartData.authors.labels,
            datasets: [{
                label: 'MR Count',
                data: window.chartData.authors.leadTimes,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                title: {
                    display: true,
                    text: 'MR Lead Time per Author'
                }
            },
            scales: {
                x: {
                    ticks: {
                        font: { size: 14 },
                        maxRotation: 30,
                        minRotation: 0,
                        autoSkip: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 2
                    }
                }
            }
        }
    });
}

function loadTopContributor() {
    if (!window.chartData || !window.chartData.authors) {
        console.warn("Chart data or author data not available for small chart.");
        return;
    }

    const canvasSmall = document.getElementById('authorMergeCountChart');
    if (!canvasSmall) {
        console.warn("Canvas element for small chart ('authorMergeCountChart') not found yet.");
        return;
    }
    const ctx = canvasSmall.getContext('2d');

    const existingChartSmall = Chart.getChart(canvasSmall);
    if (existingChartSmall) {
        existingChartSmall.destroy();
        console.log("Existing chart on 'authorMergeCountChart' destroyed before new one created.");
    }

    const authorMergeCountChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: window.chartData.authors.labels,
            datasets: [{
                label: 'MR Count',
                data: window.chartData.authors.mrCounts,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 20
                    }
                }
            }
        }
    });
}

function loadAvgContributor() {
    if (!window.chartData || !window.chartData.authors) {
        console.warn("Chart data or author data not available for small chart.");
        return;
    }

    const canvasSmall = document.getElementById('authorAvgMergeCountChart');
    if (!canvasSmall) {
        console.warn("Canvas element for small chart ('authorAvgMergeCountChart') not found yet.");
        return;
    }
    const ctx = canvasSmall.getContext('2d');

    const existingChartSmall = Chart.getChart(canvasSmall);
    if (existingChartSmall) {
        existingChartSmall.destroy();
        console.log("Existing chart on 'authorAvgergeCountChart' destroyed before new one created.");
    }

    const authorAvgMergeCountChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: window.chartData.authors.labels,
            datasets: [{
                label: 'MR Avg Lead Time',
                data: window.chartData.authors.leadTimes,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 2
                    }
                }
            }
        }
    });
}

function loadLeadTimeTrendChart() {
    if (!window.chartData || !window.chartData.trend) {
        console.warn("Chart data or trend data not available.");
        return;
    }
    const canvas = document.getElementById('leadTimeTrendChart');
    if (!canvas) {
        console.warn("Canvas element for trend chart ('leadTimeTrendChart') not found yet.");
        return;
    }
    const ctx = canvas.getContext('2d');

    const existingChart = Chart.getChart(canvas);
    if (existingChart) {
        existingChart.destroy();
    }

    new Chart(ctx, {
        type: 'line',
        data: window.chartData.trend,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true },
                title: {
                    display: true,
                    text: 'Merge Request Lead Time Trend'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                    }
                }
            }
        }
    });
}

function loadProjectLeadTimeChart() {
    if (!window.chartData || !window.chartData.projects) {
        console.warn("Chart data or project data not available.");
        return;
    }
    const canvas = document.getElementById('projectLeadTimeChart');
    if (!canvas) {
        console.warn("Canvas element for project chart ('projectLeadTimeChart') not found yet.");
        return;
    }
    const ctx = canvas.getContext('2d');

    const existingChart = Chart.getChart(canvas);
    if (existingChart) {
        existingChart.destroy();
    }

    new Chart(ctx, {
        type: 'bar',
        data: window.chartData.projects,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true },
                title: {
                    display: true,
                    text: 'Lead Time by Project'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                    }
                }
            }
        }
    });
}
