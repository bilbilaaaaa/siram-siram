import {
    CategoryScale,
    Chart,
    Filler,
    LineController,
    LineElement,
    LinearScale,
    PointElement,
    Tooltip,
} from 'chart.js';

Chart.register(
    CategoryScale,
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    Filler,
    Tooltip,
);

const canvas = document.querySelector('[data-soil-moisture-chart]');

if (canvas) {
    const datasets = JSON.parse(canvas.dataset.periods);
    const rootStyles = getComputedStyle(document.documentElement);
    const primary = rootStyles.getPropertyValue('--color-primary').trim();
    const primarySoft = rootStyles.getPropertyValue('--color-primary-soft').trim();
    const muted = rootStyles.getPropertyValue('--color-muted').trim();
    const border = rootStyles.getPropertyValue('--color-border').trim();
    const summary = document.querySelector('[data-chart-summary]');
    const dateRange = document.querySelector('[data-date-range]');
    const rangeBadge = document.querySelector('[data-moisture-range]');
    const readingCount = document.querySelector('[data-reading-count]');
    const average = document.querySelector('[data-average-moisture]');
    const minimumTemperature = document.querySelector('[data-minimum-temperature]');
    const maximumTemperature = document.querySelector('[data-maximum-temperature]');
    const periodButtons = document.querySelectorAll('[data-statistics-period]');
    const chart = new Chart(canvas, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                data: [],
                borderColor: primary,
                backgroundColor: primarySoft,
                fill: true,
                borderWidth: 2,
                pointRadius: 0,
                pointHoverRadius: 4,
                pointBackgroundColor: primary,
                tension: 0,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    displayColors: false,
                    callbacks: {
                        label: (context) => ` ${context.parsed.y}%`,
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { color: muted, maxRotation: 0, autoSkip: true },
                },
                y: {
                    min: 30,
                    max: 70,
                    ticks: {
                        stepSize: 20,
                        color: muted,
                        callback: (value) => `${value}%`,
                    },
                    grid: { color: border },
                    border: { display: false },
                },
            },
        },
    });

    const updatePeriod = (period) => {
        const dataset = datasets[period];

        if (!dataset) {
            return;
        }

        chart.data.labels = dataset.soil_moisture.labels;
        chart.data.datasets[0].data = dataset.soil_moisture.values;
        chart.update();

        dateRange.textContent = dataset.label;
        rangeBadge.textContent = dataset.soil_moisture_range;
        readingCount.textContent = dataset.reading_count;
        average.textContent = dataset.average_soil_moisture === null
            ? '—'
            : `${dataset.average_soil_moisture}%`;
        minimumTemperature.textContent = dataset.minimum_temperature === null
            ? '—'
            : `${dataset.minimum_temperature}°C`;
        maximumTemperature.textContent = dataset.maximum_temperature === null
            ? '—'
            : `${dataset.maximum_temperature}°C`;

        const values = dataset.soil_moisture.values.filter((value) => value !== null);
        summary.textContent = values.length === 0
            ? 'Belum ada pembacaan sensor pada periode ini.'
            : `Kelembapan tanah ${dataset.reading_count} pembacaan: minimum ${Math.min(...values)}%, maksimum ${Math.max(...values)}%, rata-rata ${dataset.average_soil_moisture}%.`;

        periodButtons.forEach((button) => {
            const isActive = button.dataset.statisticsPeriod === period;
            button.setAttribute('aria-pressed', String(isActive));
            button.classList.toggle('bg-primary', isActive);
            button.classList.toggle('text-surface', isActive);
            button.classList.toggle('text-primary', !isActive);
        });
    };

    periodButtons.forEach((button) => {
        button.addEventListener('click', () => updatePeriod(button.dataset.statisticsPeriod));
    });

    updatePeriod('hari_ini');
}
