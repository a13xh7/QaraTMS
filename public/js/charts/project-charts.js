import { CHART_COLORS, getCommonOptions, getDatasetConfig, getAxisConfig } from '../chart-config.js';

export class ProjectCharts {
    constructor(leadTimeElementId, detailedElementId) {
        this.leadTimeElementId = leadTimeElementId;
        this.detailedElementId = detailedElementId;
        this.charts = {
            leadTime: null,
            detailed: null
        };
    }

    initialize(projectData) {
        this.initializeLeadTimeChart(projectData);
        this.initializeDetailedChart(projectData);
    }

    initializeLeadTimeChart(projectData) {
        const ctx = document.getElementById(this.leadTimeElementId)?.getContext('2d');
        if (!ctx) {
            console.warn(`Cannot initialize project lead time chart - missing context for ${this.leadTimeElementId}`);
            return;
        }

        // Set accessibility attributes
        ctx.canvas.setAttribute('role', 'img');
        ctx.canvas.setAttribute('aria-label', 'Lead Time by Project Chart');

        this.destroyChart('leadTime');

        this.charts.leadTime = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: projectData.labels,
                datasets: [{
                    ...getDatasetConfig('bar', CHART_COLORS.success, 'Average Lead Time (Days)'),
                    data: projectData.leadTimes
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

    initializeDetailedChart(projectData) {
        const ctx = document.getElementById(this.detailedElementId)?.getContext('2d');
        if (!ctx) {
            console.warn(`Cannot initialize project detailed chart - missing context for ${this.detailedElementId}`);
            return;
        }

        // Set accessibility attributes
        ctx.canvas.setAttribute('role', 'img');
        ctx.canvas.setAttribute('aria-label', 'Project Detailed Analysis Chart');

        this.destroyChart('detailed');

        this.charts.detailed = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: projectData.labels,
                datasets: [
                    {
                        ...getDatasetConfig('bar', CHART_COLORS.warning, 'Lead Time (Days)'),
                        data: projectData.leadTimes,
                        yAxisID: 'y'
                    },
                    {
                        ...getDatasetConfig('bar', CHART_COLORS.primary, 'MR Count'),
                        data: projectData.counts,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: getCommonOptions({
                scales: {
                    y: getAxisConfig('Lead Time (Days)', 'left'),
                    y1: getAxisConfig('MR Count', 'right')
                }
            })
        });
    }

    destroyChart(chartKey) {
        if (this.charts[chartKey]) {
            this.charts[chartKey].destroy();
            this.charts[chartKey] = null;
        }
    }

    destroy() {
        Object.keys(this.charts).forEach(key => this.destroyChart(key));
    }
} 