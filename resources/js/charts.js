// Charts for the backoffice finance overview. ApexCharts is bundled only into
// this entry, so no other page downloads it, and it is tree-shaken: the bare
// core plus the chart types and the legend this page actually configures.
import ApexCharts from 'apexcharts/core';
import 'apexcharts/area';
import 'apexcharts/bar';
import 'apexcharts/donut';
import 'apexcharts/features/legend';

const data = window.financeCharts;

if (data) {
    const FONT = "'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif";
    const BRAND = '#F95D0B';
    const LEAF = '#159A4C';
    const INK = '#1A130D';
    const MUTED = '#7A6C5E';
    const GRID = 'rgba(26, 19, 13, .08)';

    const rupiah = (value) => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(value));
    const shortRupiah = (value) =>
        'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact', maximumFractionDigits: 1 }).format(Math.round(value));
    const shorten = (label, max = 22) => (label.length > max ? label.slice(0, max - 1).trimEnd() + '…' : label);

    // ApexCharts sizes the category axis by measuring the label font, so render
    // only once the web font is in: measuring a fallback font can clip labels.
    const fontsReady = document.fonts ? document.fonts.ready : Promise.resolve();

    const baseChart = {
        fontFamily: FONT,
        foreColor: MUTED,
        toolbar: { show: false },
        parentHeightOffset: 0,
        animations: { enabled: true, speed: 300 },
    };

    const grid = { borderColor: GRID, strokeDashArray: 4, padding: { left: 4, right: 8 } };

    const axis = {
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: { style: { fontWeight: 700 } },
    };

    const tooltip = { theme: 'light', y: { formatter: rupiah } };

    const mount = (id, options) => {
        const el = document.getElementById(id);

        if (el) {
            fontsReady.then(() => new ApexCharts(el, options).render());
        }
    };

    // Money arriving day by day, from verified transfer proofs.
    mount('chart-inflow', {
        chart: { ...baseChart, type: 'area', height: 260 },
        series: [{ name: 'Uang masuk', data: data.inflow.series }],
        xaxis: { categories: data.inflow.categories, ...axis },
        yaxis: { labels: { formatter: shortRupiah, style: { fontWeight: 700 } } },
        colors: [BRAND],
        stroke: { curve: 'smooth', width: 3 },
        fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.02, stops: [0, 95, 100] } },
        markers: { size: 0, hover: { size: 5 } },
        dataLabels: { enabled: false },
        grid,
        tooltip,
    });

    // Where the money the platform holds is currently sitting.
    mount('chart-position', {
        chart: { ...baseChart, type: 'donut', height: 268 },
        series: data.position.series,
        labels: data.position.labels,
        colors: [INK, LEAF, BRAND],
        stroke: { width: 2, colors: ['#FFFFFF'] },
        dataLabels: { enabled: false },
        legend: { position: 'bottom', fontSize: '12px', fontWeight: 700, itemMargin: { horizontal: 8, vertical: 2 } },
        plotOptions: {
            pie: {
                expandOnClick: false,
                donut: {
                    size: '66%',
                    labels: {
                        show: true,
                        value: { fontSize: '17px', fontWeight: 800, formatter: rupiah },
                        total: {
                            show: true,
                            label: 'Total dipegang',
                            fontSize: '11px',
                            fontWeight: 700,
                            formatter: (w) => rupiah(w.globals.seriesTotals.reduce((sum, value) => sum + value, 0)),
                        },
                    },
                },
            },
        },
        tooltip,
    });

    // Which warungs bring the most finished-order value.
    mount('chart-stores', {
        chart: {
            ...baseChart,
            type: 'bar',
            height: Math.max(240, data.stores.categories.length * 46),
        },
        series: [{ name: 'Nilai pesanan selesai', data: data.stores.series }],
        xaxis: { categories: data.stores.categories.map((name) => shorten(name)), labels: { formatter: shortRupiah, style: { fontWeight: 700 } }, axisBorder: { show: false }, axisTicks: { show: false } },
        // Wide enough that warung names are never clipped on the category axis.
        yaxis: { labels: { maxWidth: 168, style: { fontWeight: 700 } } },
        colors: [BRAND],
        plotOptions: { bar: { horizontal: true, barHeight: '52%', borderRadius: 6, borderRadiusApplication: 'end' } },
        dataLabels: { enabled: false },
        grid: { ...grid, yaxis: { lines: { show: false } } },
        tooltip,
    });

    // Order funnel across every workflow state.
    mount('chart-statuses', {
        chart: { ...baseChart, type: 'donut', height: 268 },
        series: data.statuses.series,
        labels: data.statuses.labels,
        colors: ['#FFA566', '#F59E0B', LEAF, '#0A2E18', '#9CA3AF'],
        stroke: { width: 2, colors: ['#FFFFFF'] },
        dataLabels: { enabled: false },
        legend: { position: 'bottom', fontSize: '12px', fontWeight: 700, itemMargin: { horizontal: 8, vertical: 2 } },
        plotOptions: {
            pie: {
                expandOnClick: false,
                donut: {
                    size: '66%',
                    labels: {
                        show: true,
                        value: { fontSize: '17px', fontWeight: 800, formatter: (value) => `${Math.round(value)} pesanan` },
                        total: {
                            show: true,
                            label: 'Total pesanan',
                            fontSize: '11px',
                            fontWeight: 700,
                            formatter: (w) => `${w.globals.seriesTotals.reduce((sum, value) => sum + value, 0)} pesanan`,
                        },
                    },
                },
            },
        },
        tooltip: { theme: 'light', y: { formatter: (value) => `${Math.round(value)} pesanan` } },
    });
}
