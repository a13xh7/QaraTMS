$(document).ready(function () {
    $('#exportCSV').on('click', function () {
        downloadCSV()
    });
    loadTopContributors();
    loadTrend();
    setupPaginationTable(window.initialData);
});


let originalData = [];
const itemsPerPage = 10;
let currentPage = 1;
let totalItems = 0;
let totalPages = 0;

const table = $('#contributionsTable');
const tableBody = $('#contributionsData');

const contributorSearchInput = $('#contributorSearch');

const prevPageBtn = $('#prevPage');
const nextPageBtn = $('#nextPage');
const paginationStartSpan = $('#paginationStart');
const paginationEndSpan = $('#paginationEnd');
const paginationTotalSpan = $('#paginationTotal');

function loadTopContributors() {

    if (!window.topContributionData) {
        console.error("Chart data or top contributor data not available for large chart.");
        return;
    }

    const canvas = document.getElementById('topContributorsChart');
    if (!canvas) {
        console.error("Canvas element with ID 'topContributorsChart' not found for large chart rendering.");
        return;
    }

    const existingChart = Chart.getChart(canvas);
    if (existingChart) {
        existingChart.destroy();
        console.log("Existing chart on 'topContributorsChart' destroyed before new one created.");
    }

    const ctxLarge = canvas.getContext('2d');

    window.topContributors = new Chart(ctxLarge, {
        type: 'bar',
        data: {
            labels: window.topContributionData.original.labels,
            datasets: [
                {
                    label: 'MRs Created',
                    data: window.topContributionData.original.mrCreated,
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    borderColor: 'rgb(255, 99, 132)',
                    borderWidth: 1
                },
                {
                    label: 'MRs Approved',
                    data: window.topContributionData.original.mrApproved,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgb(54, 162, 235)',
                    borderWidth: 1
                },
                {
                    label: 'Repo Pushes',
                    data: window.topContributionData.original.repoPushes,
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
}

function loadTrend() {
    if (!window.trendData) {
        console.error("Chart data or trendData data not available for large chart.");
        return;
    }

    const canvas = document.getElementById('contributionTrendChart');
    if (!canvas) {
        console.error("Canvas element with ID 'contributionTrendChart' not found for large chart rendering.");
        return;
    }

    const existingChart = Chart.getChart(canvas);
    if (existingChart) {
        existingChart.destroy();
        console.log("Existing chart on 'contributionTrendChart' destroyed before new one created.");
    }

    const ctxLarge = canvas.getContext('2d');

    window.authorMergeAvgChartLarge = new Chart(ctxLarge, {
        type: 'line',
        data: {
            labels: window.trendData.original.labels,
            datasets: [
                {
                    label: 'Total Contributions',
                    data: window.trendData.original.totalEvents,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1,
                    fill: false
                },
                {
                    label: 'MRs Created',
                    data: window.trendData.original.mrCreated,
                    borderColor: 'rgb(255, 99, 132)',
                    tension: 0.1,
                    fill: false
                },
                {
                    label: 'MRs Approved',
                    data: window.trendData.original.mrApproved,
                    borderColor: 'rgb(54, 162, 235)',
                    tension: 0.1,
                    fill: false
                },
                {
                    label: 'Repo Pushes',
                    data: window.trendData.original.repoPushes,
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
}

function downloadCSV() {
    const table = document.getElementById('contributionsTable');
    if (!table) return;

    const rows = table.querySelectorAll('tr');
    const csv = [];

    // Get current filters for filename
    const year = document.getElementById('year').value;
    const month = document.getElementById('month').value;

    const timestamp = new Date().toISOString().slice(0, 19).replace(/:/g, '-');

    // Create a dynamic filename with the filters and timestamp
    filename = `contributions_${year}_${month === 'all' ? 'all_months' : month}_${timestamp}.csv`;

    // Add export metadata as first row
    const metadataRow = [
        '"Export Date"',
        `"${new Date().toLocaleString()}"`,
        '"Filters"',
        `"Year: ${year}, Month: ${month === 'all' ? 'All Months' : month}"`
    ];
    csv.push(metadataRow.join(','));
    csv.push([]);

    // Add table data
    for (let i = 0; i < rows.length; i++) {
        const row = [], cols = rows[i].querySelectorAll('td, th');

        for (let j = 0; j < cols.length; j++) {
            // Clean the data and escape double quotes
            let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s)/gm, ' ');
            data = data.replace(/"/g, '""');
            row.push('"' + data + '"');
        }

        csv.push(row.join(','));
    }

    // Create and download the CSV file
    const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
    const downloadLink = document.createElement('a');

    downloadLink.download = filename;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';

    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

function displayTablePage(pageNumber) {
    const startIndex = (pageNumber - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;

    const itemsForPage = window.initialData.slice(startIndex, endIndex);

    tableBody.empty();

    if (itemsForPage.length > 0) {
        itemsForPage.forEach(data => {
            const tr = document.createElement('tr');

            tr.innerHTML = `
                <td>${data.year}</td>
                <td>${data.monthName}</td>
                <td>${data.name}</td>
                <td>${data.mrCreated}</td>
                <td>${data.mrApproved}</td>
                <td>${data.repoPushes}</td>
                <td>${data.totalEvents}</td>
            `;

            tableBody.append(tr);
        });
    } else {
        tableBody.html('<tr><td colspan="7" class="text-center">No data available.</td></tr>'); // Adjust colspan if needed
    }

    updatePaginationControls();
    updatePaginationInfo(startIndex, itemsForPage.length);
}

function updatePaginationControls() {
    prevPageBtn.prop('disabled', currentPage === 1);

    nextPageBtn.prop('disabled', currentPage === totalPages);
}

function updatePaginationInfo(startIndex, itemsOnCurrentPage) {
    if (totalItems === 0) {
        paginationStartSpan.text(0);
        paginationEndSpan.text(0);
    } else {
        paginationStartSpan.text(startIndex + 1);
        paginationEndSpan.text(startIndex + itemsOnCurrentPage);
    }
    paginationTotalSpan.text(totalItems);
}

function setupPaginationTable(initialFullData) {
    if (initialFullData && Array.isArray(initialFullData)) {
        window.initialData = initialFullData.slice();
        originalData = window.initialData;

        tableBody.html('<tr><td colspan="7" class="text-center">No data available.</td></tr>');

        createTableHeader();

        displayTablePage(currentPage);

        contributorSearchInput.off('input').on('input', function () {
            const query = $(this).val();
            filterTableData(query);
        });


        totalItems = window.initialData.length;
        totalPages = Math.ceil(totalItems / itemsPerPage);

        prevPageBtn.off('click').on('click', function (e) {
            e.preventDefault();
            if (currentPage > 1) {
                currentPage--;
                displayTablePage(currentPage);
            }
        });

        nextPageBtn.off('click').on('click', function (e) {
            e.preventDefault();
            if (currentPage < totalPages) {
                currentPage++;
                displayTablePage(currentPage);
            }
        });

        if (totalItems > 0) {
            updatePaginationControls();
            updatePaginationInfo((currentPage - 1) * itemsPerPage, totalItems > 0 ? itemsPerPage : 0);
        }

    } else {
        tableBody.html('<tr><td colspan="7" class="text-center">Failed to load initial data.</td></tr>');
        paginationStartSpan.text(0);
        paginationEndSpan.text(0);
        paginationTotalSpan.text(0);
        prevPageBtn.prop('disabled', true);
        nextPageBtn.prop('disabled', true);
        table.find('thead').remove();
        contributorSearchInput.prop('disabled', true);
    }
}

function createTableHeader() {

    let tableHead = table.find('thead');
    if (!tableHead.length) {
        tableHead = $('<thead>').prependTo(table);
    }

    tableHead.addClass('table-light');
    tableHead.empty();

    const headerRow = $('<tr>');

    const headers = [
        { text: 'Year', sortable: true, sortData: 'year' },
        { text: 'Month', sortable: true, sortData: 'monthName' },
        { text: 'Name', sortable: true, sortData: 'name' },
        { text: 'MR Created', sortable: true, sortData: 'mrCreated' },
        { text: 'MR Approved', sortable: true, sortData: 'mrApproved' },
        { text: 'Repo Pushes', sortable: true, sortData: 'repoPushes' },
        { text: 'Total Events', sortable: true, sortData: 'totalEvents' }
    ];

    headers.forEach(header => {
        const th = $('<th>')
            .attr('scope', 'col')
            .text(header.text);

        headerRow.append(th);
    });

    tableHead.append(headerRow);
}


function filterTableData(query) {
    const lowerCaseQuery = query.toLowerCase();

    if (lowerCaseQuery === '') {
        window.initialData = originalData.slice();
    } else {
        window.initialData = originalData.filter(item => {
            return item.name && String(item.name).toLowerCase().includes(lowerCaseQuery);
        });
    }

    totalItems = window.initialData.length;
    totalPages = Math.ceil(totalItems / itemsPerPage);

    if (totalItems === 0) {
        totalPages = 1;
    }

    currentPage = 1;

    displayTablePage(currentPage);
}
