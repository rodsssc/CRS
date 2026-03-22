/**
 * dashboard.js
 * Handles: Revenue chart with live data from /admin/dashboard/revenue-data
 */

(function() {
    'use strict';

    const canvas = document.getElementById('dashboardRevenueChart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let chartInstance = null;

    // ── Build / rebuild chart ───────────────────────────────────────────────
    function buildChart(labels, revenue, commission, bookings) {
        if (chartInstance) chartInstance.destroy();

        chartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                        type: 'bar',
                        label: 'Bookings',
                        data: bookings,
                        backgroundColor: 'rgba(99,102,241,0.15)',
                        borderColor: 'rgb(79,70,229)',
                        borderWidth: 1,
                        borderRadius: 4,
                        yAxisID: 'y1',
                    },
                    {
                        type: 'line',
                        label: 'Revenue (₱)',
                        data: revenue,
                        borderColor: 'rgb(59,130,246)',
                        backgroundColor: 'rgba(59,130,246,0.10)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: 'rgb(59,130,246)',
                        pointBorderWidth: 2,
                        yAxisID: 'y',
                    },
                    {
                        type: 'line',
                        label: 'Commission (₱)',
                        data: commission,
                        borderColor: 'rgb(34,197,94)',
                        backgroundColor: 'rgba(34,197,94,0.08)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: 'rgb(34,197,94)',
                        pointBorderWidth: 2,
                        yAxisID: 'y',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { size: 11 }, boxWidth: 12, padding: 16 },
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15,23,42,.9)',
                        padding: 12,
                        titleColor: '#fff',
                        bodyColor: 'rgba(255,255,255,.8)',
                        borderColor: 'rgba(255,255,255,.1)',
                        borderWidth: 1,
                        callbacks: {
                            label: function(item) {
                                if (item.dataset.yAxisID === 'y') {
                                    return item.dataset.label + ': ₱' +
                                        parseFloat(item.parsed.y).toLocaleString('en-PH', {
                                            minimumFractionDigits: 2,
                                        });
                                }
                                return item.dataset.label + ': ' + item.parsed.y;
                            },
                        },
                    },
                },
                scales: {
                    y: {
                        type: 'linear',
                        position: 'left',
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: {
                            color: '#64748b',
                            font: { size: 11 },
                            callback: v => '₱' + v.toLocaleString('en-PH'),
                        },
                    },
                    y1: {
                        type: 'linear',
                        position: 'right',
                        beginAtZero: true,
                        grid: { drawOnChartArea: false },
                        ticks: { color: '#64748b', font: { size: 11 }, precision: 0 },
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b', font: { size: 11 } },
                    },
                },
            },
        });
    }

    // ── Placeholder while fetching ──────────────────────────────────────────
    function renderPlaceholder() {
        buildChart(
            ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], [0, 0, 0, 0, 0, 0, 0], [0, 0, 0, 0, 0, 0, 0], [0, 0, 0, 0, 0, 0, 0]
        );
    }

    // ── Fetch live data ─────────────────────────────────────────────────────
    // The revenue-data URL is injected by the blade via a data attribute:
    // <canvas id="dashboardRevenueChart" data-url="{{ route('admin.dashboard.revenue-data') }}">
    function loadChart() {
        const url = canvas.dataset.url;

        if (!url) {
            console.warn('[Dashboard] data-url missing on canvas element.');
            renderPlaceholder();
            return;
        }

        renderPlaceholder(); // show immediately while loading

        fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content || '',
                },
            })
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(data => {
                if (!data.success) throw new Error('API error');
                buildChart(
                    data.labels || [],
                    data.revenue || [],
                    data.commission || [],
                    data.bookings || []
                );
            })
            .catch(err => {
                console.warn('[Dashboard chart] Live data failed, showing placeholder:', err);
            });
    }

    // ── Init ────────────────────────────────────────────────────────────────
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadChart);
    } else {
        loadChart();
    }

})();