/**
 * Contribution Charts
 * Provides visualization of contribution data using Chart.js
 */

const ContributionCharts = {
    // Chart instances
    charts: {
        contributionTrend: null,
        topContributors: null
    },

    // Initialize charts
    init: function (data) {
        if (!data || !Array.isArray(data) || data.length === 0) {
            console.warn('No data available for charts');
            return;
        }

        // Create charts if the container elements exist
        if (document.getElementById('contributionTrendChart')) {
            this.createTrendChart(data);
        }

        if (document.getElementById('topContributorsChart')) {
            this.createTopContributorsChart(data);
        }
    },

    // Destroy existing charts
    destroyCharts: function () {
        // Destroy existing charts to prevent memory leaks
        if (this.charts.contributionTrend) {
            this.charts.contributionTrend.destroy();
            this.charts.contributionTrend = null;
        }

        if (this.charts.topContributors) {
            this.charts.topContributors.destroy();
            this.charts.topContributors = null;
        }
    },

    // Create trend chart
    createTrendChart: function (data) {
        const ctx = document.getElementById('contributionTrendChart').getContext('2d');
        const canvas = ctx.canvas;
        // Sort data by year and month
        data = [...data].sort((a, b) => {
            if (a.year !== b.year) return a.year - b.year;
            return a.month - b.month;
        });

        // Process data to aggregate by month
        const monthlyData = this.aggregateDataByMonth(data);

        if (this.charts && this.charts.contributionTrend && this.charts.contributionTrend.canvas === canvas) {
            console.log("Destroying existing Contribution Trend chart instance before creating a new one...");
            this.charts.contributionTrend.destroy(); // Destroy the old instance
            console.log("Existing instance destroyed.");
            // Set the reference to null or undefined after destroying is often a good practice
            this.charts.contributionTrend = null;
        } else {
            // This block is optional, mostly for initial creation log
            console.log("No existing Contribution Trend chart instance found or found on a different canvas.");
        }

        // Create the chart
        this.charts.contributionTrend = new Chart(ctx, {
            type: 'line',
            data: {
                labels: monthlyData.labels,
                datasets: [
                    {
                        label: 'Total Contributions',
                        data: monthlyData.totalEvents,
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1,
                        fill: false
                    },
                    {
                        label: 'MRs Created',
                        data: monthlyData.mrCreated,
                        borderColor: 'rgb(255, 99, 132)',
                        tension: 0.1,
                        fill: false
                    },
                    {
                        label: 'MRs Approved',
                        data: monthlyData.mrApproved,
                        borderColor: 'rgb(54, 162, 235)',
                        tension: 0.1,
                        fill: false
                    },
                    {
                        label: 'Repo Pushes',
                        data: monthlyData.repoPushes,
                        borderColor: 'rgb(255, 159, 64)',
                        tension: 0.1,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Monthly Contribution Trends'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Count'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                }
            }
        });
    },

    // Create top contributors chart
    createTopContributorsChart: function (data) {
        const ctx = document.getElementById('topContributorsChart').getContext('2d');
        const canvas = ctx.canvas;

        // Process data to aggregate by contributor
        const contributorData = this.aggregateDataByContributor(data);

        // Take top 5 contributors
        const topCount = 5;
        const labels = contributorData.labels.slice(0, topCount);
        const totalEvents = contributorData.totalEvents.slice(0, topCount);
        const mrCreated = contributorData.mrCreated.slice(0, topCount);
        const mrApproved = contributorData.mrApproved.slice(0, topCount);
        const repoPushes = contributorData.repoPushes.slice(0, topCount);

        if (this.charts && this.charts.topContributors && this.charts.topContributors.canvas === canvas) {
            console.log("Destroying existing Contribution Trend chart instance before creating a new one...");
            this.charts.topContributors.destroy(); // Destroy the old instance
            console.log("Existing instance destroyed.");
            // Set the reference to null or undefined after destroying is often a good practice
            this.charts.topContributors = null;
        } else {
            // This block is optional, mostly for initial creation log
            console.log("No existing Contribution Trend chart instance found or found on a different canvas.");
        }

        // Create the chart
        this.charts.topContributors = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'MRs Created',
                        data: mrCreated,
                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                        borderColor: 'rgb(255, 99, 132)',
                        borderWidth: 1
                    },
                    {
                        label: 'MRs Approved',
                        data: mrApproved,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgb(54, 162, 235)',
                        borderWidth: 1
                    },
                    {
                        label: 'Repo Pushes',
                        data: repoPushes,
                        backgroundColor: 'rgba(255, 159, 64, 0.5)',
                        borderColor: 'rgb(255, 159, 64)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Top Contributors'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Count'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Contributor'
                        }
                    }
                }
            }
        });
    },

    // Aggregate data by month
    aggregateDataByMonth: function (data) {
        const monthNames = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        // Create a map of year-month to aggregated data
        const monthMap = new Map();

        data.forEach(item => {
            const key = `${item.year}-${item.month}`;
            const label = `${monthNames[item.month - 1]} ${item.year}`;

            if (!monthMap.has(key)) {
                monthMap.set(key, {
                    label: label,
                    totalEvents: 0,
                    mrCreated: 0,
                    mrApproved: 0,
                    repoPushes: 0,
                    timestamp: new Date(item.year, item.month - 1, 1).getTime()
                });
            }

            const monthData = monthMap.get(key);
            monthData.totalEvents += parseInt(item.totalEvents) || 0;
            monthData.mrCreated += parseInt(item.mrCreated) || 0;
            monthData.mrApproved += parseInt(item.mrApproved) || 0;
            monthData.repoPushes += parseInt(item.repoPushes) || 0;
        });

        // Convert map to arrays for Chart.js
        const sortedMonths = Array.from(monthMap.values())
            .sort((a, b) => a.timestamp - b.timestamp);

        return {
            labels: sortedMonths.map(item => item.label),
            totalEvents: sortedMonths.map(item => item.totalEvents),
            mrCreated: sortedMonths.map(item => item.mrCreated),
            mrApproved: sortedMonths.map(item => item.mrApproved),
            repoPushes: sortedMonths.map(item => item.repoPushes)
        };
    },

    // Aggregate data by contributor
    aggregateDataByContributor: function (data) {
        // Create a map of contributor to aggregated data
        const contributorMap = new Map();

        data.forEach(item => {
            if (!contributorMap.has(item.name)) {
                contributorMap.set(item.name, {
                    name: item.name,
                    totalEvents: 0,
                    mrCreated: 0,
                    mrApproved: 0,
                    repoPushes: 0
                });
            }

            const contributorData = contributorMap.get(item.name);
            contributorData.totalEvents += parseInt(item.totalEvents) || 0;
            contributorData.mrCreated += parseInt(item.mrCreated) || 0;
            contributorData.mrApproved += parseInt(item.mrApproved) || 0;
            contributorData.repoPushes += parseInt(item.repoPushes) || 0;
        });

        // Convert map to arrays for Chart.js, sorted by total events
        const sortedContributors = Array.from(contributorMap.values())
            .sort((a, b) => b.totalEvents - a.totalEvents);

        return {
            labels: sortedContributors.map(item => item.name),
            totalEvents: sortedContributors.map(item => item.totalEvents),
            mrCreated: sortedContributors.map(item => item.mrCreated),
            mrApproved: sortedContributors.map(item => item.mrApproved),
            repoPushes: sortedContributors.map(item => item.repoPushes)
        };
    }
};

// Export the controller
window.ContributionCharts = ContributionCharts; 
