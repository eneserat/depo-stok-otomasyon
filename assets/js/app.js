document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('show');
        });
    }

    const dataTables = document.querySelectorAll('[data-table]');
    dataTables.forEach(table => {
        new DataTable(table, {
            pageLength: 10,
            order: [],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/tr.json'
            }
        });
    });

    const chartCanvas = document.getElementById('stockChart');
    if (chartCanvas && window.stockChartData) {
        const ctx = chartCanvas.getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: window.stockChartData.labels,
                datasets: [
                    {
                        label: 'Stok Girişi',
                        data: window.stockChartData.entries,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        tension: 0.4,
                        borderWidth: 3,
                        fill: true,
                    },
                    {
                        label: 'Stok Çıkışı',
                        data: window.stockChartData.exits,
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.4,
                        borderWidth: 3,
                        fill: true,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                    }
                }
            }
        });
    }
});

