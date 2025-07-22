$(document).ready(function () {
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

  createLeadTimeTrendChart();
  createLeadTimeIssueTypeChart()
  createLeadTimeProjectChart();
});

function createLeadTimeTrendChart() {
  const ctx = document.getElementById('jiraLeadTimeTrendChart').getContext('2d');
  const jiraLeadTimeTrendChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: window.chartLabels,
      datasets: [{
        label: 'Average Lead Time',
        data: window.chartTrendData,
        borderColor: 'rgba(54, 162, 235, 1)',
        backgroundColor: 'rgba(54, 162, 235, 0.2)',
        fill: true,
        tension: 0.1
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: true },
        title: {
          display: true,
          text: 'Jira Lead Time Trend'
        }
      },
      scales: {
        y: { beginAtZero: true }
      }
    }
  });
}

function createLeadTimeIssueTypeChart() {
  const ctx = document.getElementById('issueTypeLeadTimeChart').getContext('2d');
  const issueTypeLeadTimeChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: window.chartLabels,
      datasets: window.chartDatasets
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          display: true
        },
        title: {
          display: true,
          text: 'Lead Time by Issue Type'
        }
      },
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
}

function createLeadTimeProjectChart() {
  const ctx = document.getElementById('projectLeadTimeChart').getContext('2d');
  const projectLeadTimeChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: window.chartLabels,
      datasets: window.chartProjectData
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: true },
        title: {
          display: true,
          text: 'Project Lead Time Trend'
        }
      },
      scales: {
        y: { beginAtZero: true }
      }
    }
  });
}
