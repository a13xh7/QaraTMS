import { CHART_COLORS, CHART_COLOR_PALETTE, getCommonOptions, getDatasetConfig } from '../chart-config.js';

// Chart type constants
const CHART_TYPES = {
    MERGE_COUNT: 'bar',
    LEAD_TIME: 'doughnut'
};

export class AuthorCharts {
    constructor(mergeCountElementId, leadTimeElementId, ChartConstructor = Chart, options = {}) {
        this.mergeCountElementId = mergeCountElementId;
        this.leadTimeElementId = leadTimeElementId;
        this.Chart = ChartConstructor;
        this.charts = {
            mergeCount: null,
            leadTime: null
        };
        this.resizeObserver = null;
        this.options = {
            onChartInitSuccess: options.onChartInitSuccess || (() => {}),
            onChartInitError: options.onChartInitError || ((err) => console.error(err)),
            showLoadingState: options.showLoadingState || false
        };
    }

    initialize(authorData) {
        this.initializeMergeCountChart(authorData);
        this.initializeLeadTimeChart(authorData);
        this.setupResizeObserver();
    }

    setupResizeObserver() {
        const container = document.getElementById(this.mergeCountElementId)?.parentElement;
        if (!container) return;

        this.resizeObserver = new ResizeObserver(() => {
            this.charts.mergeCount?.resize();
            this.charts.leadTime?.resize();
        });
        this.resizeObserver.observe(container);
    }

    showLoadingState(elementId) {
        if (!this.options.showLoadingState) return;
        
        const container = document.getElementById(elementId)?.parentElement;
        if (!container) return;

        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'chart-loading-state';
        loadingDiv.innerHTML = `
            <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <span class="ms-2">Loading chart data...</span>
        `;
        container.appendChild(loadingDiv);
    }

    removeLoadingState(elementId) {
        const container = document.getElementById(elementId)?.parentElement;
        if (!container) return;

        const loadingState = container.querySelector('.chart-loading-state');
        if (loadingState) {
            loadingState.remove();
        }
    }

    initializeMergeCountChart(authorData) {
        const canvas = document.getElementById(this.mergeCountElementId);
        const ctx = canvas?.getContext('2d');
        if (!ctx) {
            console.warn(`Cannot initialize author merge count chart - missing context for ${this.mergeCountElementId}`);
            return;
        }

        // Set accessibility attributes
        ctx.canvas.setAttribute('role', 'img');
        ctx.canvas.setAttribute('aria-label', 'Author Merge Request Count Chart');

        this.destroyChart('mergeCount');
        this.showLoadingState(this.mergeCountElementId);

        try {
            this.charts.mergeCount = new this.Chart(ctx, {
                type: CHART_TYPES.MERGE_COUNT,
                data: {
                    labels: authorData.labels,
                    datasets: [{
                        ...getDatasetConfig('bar', CHART_COLORS.primary, 'Number of MRs'),
                        data: authorData.mrCounts
                    }]
                },
                options: getCommonOptions({
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Number of MRs' }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                })
            });
            this.removeLoadingState(this.mergeCountElementId);
            this.options.onChartInitSuccess('mergeCount');
        } catch (err) {
            console.error('Failed to initialize merge count chart:', err);
            this.removeLoadingState(this.mergeCountElementId);
            this.showChartError(this.mergeCountElementId);
            this.options.onChartInitError(err);
        }
    }

    initializeLeadTimeChart(authorData) {
        const canvas = document.getElementById(this.leadTimeElementId);
        const ctx = canvas?.getContext('2d');
        if (!ctx) {
            console.warn(`Cannot initialize author lead time chart - missing context for ${this.leadTimeElementId}`);
            return;
        }

        // Set accessibility attributes
        ctx.canvas.setAttribute('role', 'img');
        ctx.canvas.setAttribute('aria-label', 'Author Lead Time Distribution Chart');

        this.destroyChart('leadTime');
        this.showLoadingState(this.leadTimeElementId);

        try {
            this.charts.leadTime = new this.Chart(ctx, {
                type: CHART_TYPES.LEAD_TIME,
                data: {
                    labels: authorData.labels,
                    datasets: [{
                        data: authorData.avgLeadTimes,
                        backgroundColor: authorData.labels.map((_, i) => 
                            CHART_COLOR_PALETTE[i % CHART_COLOR_PALETTE.length].light
                        ),
                        borderColor: authorData.labels.map((_, i) => 
                            CHART_COLOR_PALETTE[i % CHART_COLOR_PALETTE.length].base
                        ),
                        borderWidth: 1
                    }]
                },
                options: getCommonOptions({
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: { 
                                font: { size: 11 },
                                generateLabels: function(chart) {
                                    const data = chart.data;
                                    if (data.labels.length && data.datasets.length) {
                                        return data.labels.map((label, i) => ({
                                            text: `${label} (${data.datasets[0].data[i]} days)`,
                                            fillStyle: CHART_COLOR_PALETTE[i % CHART_COLOR_PALETTE.length].light,
                                            strokeStyle: CHART_COLOR_PALETTE[i % CHART_COLOR_PALETTE.length].base,
                                            lineWidth: 1,
                                            hidden: false,
                                            index: i
                                        }));
                                    }
                                    return [];
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.label}: ${context.raw} days`;
                                }
                            }
                        }
                    }
                })
            });
            this.removeLoadingState(this.leadTimeElementId);
            this.options.onChartInitSuccess('leadTime');
        } catch (err) {
            console.error('Failed to initialize lead time chart:', err);
            this.removeLoadingState(this.leadTimeElementId);
            this.showChartError(this.leadTimeElementId);
            this.options.onChartInitError(err);
        }
    }

    updateMergeCountChart(authorData) {
        if (!this.charts.mergeCount) {
            this.initializeMergeCountChart(authorData);
            return;
        }

        try {
            this.charts.mergeCount.data.labels = authorData.labels;
            this.charts.mergeCount.data.datasets[0].data = authorData.mrCounts;
            this.charts.mergeCount.update();
        } catch (err) {
            console.error('Failed to update merge count chart:', err);
            this.showChartError(this.mergeCountElementId);
        }
    }

    updateLeadTimeChart(authorData) {
        if (!this.charts.leadTime) {
            this.initializeLeadTimeChart(authorData);
            return;
        }

        try {
            this.charts.leadTime.data.labels = authorData.labels;
            this.charts.leadTime.data.datasets[0].data = authorData.avgLeadTimes;
            this.charts.leadTime.data.datasets[0].backgroundColor = authorData.labels.map((_, i) => 
                CHART_COLOR_PALETTE[i % CHART_COLOR_PALETTE.length].light
            );
            this.charts.leadTime.data.datasets[0].borderColor = authorData.labels.map((_, i) => 
                CHART_COLOR_PALETTE[i % CHART_COLOR_PALETTE.length].base
            );
            this.charts.leadTime.update();
        } catch (err) {
            console.error('Failed to update lead time chart:', err);
            this.showChartError(this.leadTimeElementId);
        }
    }

    showChartError(elementId) {
        const container = document.getElementById(elementId)?.parentElement;
        if (!container) return;

        // Prevent duplicate error messages
        if (container.querySelector('.chart-error-alert')) return;

        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-danger chart-error-alert';
        errorDiv.textContent = 'Failed to load chart data. Please try refreshing the page.';
        container.appendChild(errorDiv);
    }

    destroyChart(chartKey) {
        if (this.charts[chartKey]) {
            this.charts[chartKey].destroy();
            this.charts[chartKey] = null;
        }
    }

    destroy() {
        Object.keys(this.charts).forEach(key => this.destroyChart(key));
        if (this.resizeObserver) {
            this.resizeObserver.disconnect();
            this.resizeObserver = null;
        }
    }
} 