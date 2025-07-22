import { CHART_COLORS, getCommonOptions, getDatasetConfig } from '../chart-config.js';

export class TrendChart {
    constructor(elementId) {
        this.elementId = elementId;
        this.chart = null;
    }

    initialize(trendData) {
        const ctx = document.getElementById(this.elementId)?.getContext('2d');
        if (!ctx) {
            console.warn(`Cannot initialize trend chart - missing context for ${this.elementId}`);
            return;
        }

        // Set accessibility attributes
        ctx.canvas.setAttribute('role', 'img');
        ctx.canvas.setAttribute('aria-label', 'Merge Request Lead Time Trend Chart');

        this.destroy();

        this.chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: trendData.labels,
                datasets: [{
                    ...getDatasetConfig('line', CHART_COLORS.primary, 'Average Lead Time (Days)'),
                    data: trendData.data
                }]
            },
            options: getCommonOptions({
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Days' }
                    }
                }
            })
        });
    }

    destroy() {
        if (this.chart) {
            this.chart.destroy();
            this.chart = null;
        }
    }
} 