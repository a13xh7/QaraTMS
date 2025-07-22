// Chart Colors
export const CHART_COLORS = {
    primary: {
        base: 'rgba(0, 123, 255, 1)',
        light: 'rgba(0, 123, 255, 0.6)',
        lighter: 'rgba(0, 123, 255, 0.1)'
    },
    success: {
        base: 'rgba(40, 167, 69, 1)',
        light: 'rgba(40, 167, 69, 0.6)'
    },
    warning: {
        base: 'rgba(255, 193, 7, 1)',
        light: 'rgba(255, 193, 7, 0.6)'
    }
};

// Color palette for doughnut/pie charts
export const CHART_COLOR_PALETTE = [
    { base: 'rgba(0, 123, 255, 1)', light: 'rgba(0, 123, 255, 0.6)' },
    { base: 'rgba(40, 167, 69, 1)', light: 'rgba(40, 167, 69, 0.6)' },
    { base: 'rgba(255, 193, 7, 1)', light: 'rgba(255, 193, 7, 0.6)' },
    { base: 'rgba(220, 53, 69, 1)', light: 'rgba(220, 53, 69, 0.6)' },
    { base: 'rgba(111, 66, 193, 1)', light: 'rgba(111, 66, 193, 0.6)' },
    { base: 'rgba(23, 162, 184, 1)', light: 'rgba(23, 162, 184, 0.6)' },
    { base: 'rgba(102, 16, 242, 1)', light: 'rgba(102, 16, 242, 0.6)' },
    { base: 'rgba(253, 126, 20, 1)', light: 'rgba(253, 126, 20, 0.6)' },
    { base: 'rgba(108, 117, 125, 1)', light: 'rgba(108, 117, 125, 0.6)' },
    { base: 'rgba(32, 201, 151, 1)', light: 'rgba(32, 201, 151, 0.6)' }
];

// Common chart options
export const getCommonOptions = (additionalOptions = {}) => {
    const baseOptions = {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 400 },
        plugins: {
            legend: {
                position: 'top',
                labels: { font: { size: 11 }, boxWidth: 12 }
            },
            tooltip: {
                enabled: true,
                mode: 'index',
                intersect: false
            }
        }
    };
    return _.merge({}, baseOptions, additionalOptions);
};

// Dataset configurations
export const getDatasetConfig = (type, color, label) => {
    const configs = {
        bar: {
            label,
            backgroundColor: color.light,
            borderColor: color.base,
            borderWidth: 1
        },
        line: {
            label,
            borderColor: color.base,
            backgroundColor: color.lighter,
            fill: true,
            tension: 0.4
        }
    };
    return configs[type] || configs.bar;
};

// Axis configurations
export const getAxisConfig = (title, position = 'left') => ({
    type: 'linear',
    display: true,
    position,
    title: { display: true, text: title },
    grid: {
        drawOnChartArea: position === 'left'
    }
}); 