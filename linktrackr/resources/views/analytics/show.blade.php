<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Analytics Dashboard
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $link->title ?? $link->short_url }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('links.show', $link) }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm">← Back</a>
                <a href="{{ route('analytics.export', $link) }}" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">
                    Export CSV
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Clicks</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $analytics['total_clicks'] }}</p>
                        </div>
                        <div class="bg-blue-100 rounded-full p-3">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">QR Code Scans</p>
                            <p class="text-3xl font-bold text-purple-600 mt-2">{{ $analytics['qr_vs_direct']['qr'] }}</p>
                        </div>
                        <div class="bg-purple-100 rounded-full p-3">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Direct Clicks</p>
                            <p class="text-3xl font-bold text-green-600 mt-2">{{ $analytics['qr_vs_direct']['direct'] }}</p>
                        </div>
                        <div class="bg-green-100 rounded-full p-3">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Countries</p>
                            <p class="text-3xl font-bold text-orange-600 mt-2">{{ count($analytics['clicks_by_country']) }}</p>
                        </div>
                        <div class="bg-orange-100 rounded-full p-3">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 1 -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Clicks Over Time -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Clicks Over Time</h3>
                    <canvas id="clicksChart" height="250"></canvas>
                </div>

                <!-- Device Breakdown -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Device Breakdown</h3>
                    <canvas id="deviceChart" height="250"></canvas>
                </div>
            </div>

            <!-- Charts Row 2 -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top Countries -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Countries</h3>
                    <canvas id="countryChart" height="250"></canvas>
                </div>

                <!-- Top Referrers -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Referrers</h3>
                    <canvas id="referrerChart" height="250"></canvas>
                </div>
            </div>

            <!-- Detailed Tables -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Countries Table -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Geographic Distribution</h3>
                    <div class="overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Country</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Clicks</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">%</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($analytics['clicks_by_country'] as $country)
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $country['country_name'] }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $country['count'] }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ number_format(($country['count'] / $analytics['total_clicks']) * 100, 1) }}%</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-center text-sm text-gray-500">No data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Device Types Table -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Device Types</h3>
                    <div class="overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Device</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Clicks</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">%</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($analytics['clicks_by_device'] as $device)
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                        @if($device['device_type'] === 'mobile')
                                            📱 Mobile
                                        @elseif($device['device_type'] === 'tablet')
                                            📱 Tablet
                                        @else
                                            💻 Desktop
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $device['count'] }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ number_format(($device['count'] / $analytics['total_clicks']) * 100, 1) }}%</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-center text-sm text-gray-500">No data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
// Prepare data from PHP
const analyticsData = @json($analytics);

// Chart colors
const colors = {
    primary: 'rgb(59, 130, 246)',
    success: 'rgb(34, 197, 94)',
    warning: 'rgb(251, 146, 60)',
    danger: 'rgb(239, 68, 68)',
    purple: 'rgb(168, 85, 247)',
    gray: 'rgb(107, 114, 128)',
};

// 1. Clicks Over Time Chart
const clicksData = analyticsData.clicks_by_date.reverse();
const clicksCtx = document.getElementById('clicksChart').getContext('2d');
new Chart(clicksCtx, {
    type: 'line',
    data: {
        labels: clicksData.map(d => new Date(d.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })),
        datasets: [{
            label: 'Clicks',
            data: clicksData.map(d => d.count),
            borderColor: colors.primary,
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { precision: 0 }
            }
        }
    }
});

// 2. Device Breakdown Chart (Doughnut)
const deviceData = analyticsData.clicks_by_device;
const deviceCtx = document.getElementById('deviceChart').getContext('2d');
new Chart(deviceCtx, {
    type: 'doughnut',
    data: {
        labels: deviceData.map(d => d.device_type.charAt(0).toUpperCase() + d.device_type.slice(1)),
        datasets: [{
            data: deviceData.map(d => d.count),
            backgroundColor: [colors.primary, colors.success, colors.warning],
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});

// 3. Top Countries Chart (Horizontal Bar)
const countryData = analyticsData.clicks_by_country.slice(0, 5);
const countryCtx = document.getElementById('countryChart').getContext('2d');
new Chart(countryCtx, {
    type: 'bar',
    data: {
        labels: countryData.map(d => d.country_name),
        datasets: [{
            label: 'Clicks',
            data: countryData.map(d => d.count),
            backgroundColor: colors.success,
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            x: {
                beginAtZero: true,
                ticks: { precision: 0 }
            }
        }
    }
});

// 4. Top Referrers Chart (Horizontal Bar)
const referrerData = analyticsData.clicks_by_referrer.slice(0, 5);
const referrerCtx = document.getElementById('referrerChart').getContext('2d');
new Chart(referrerCtx, {
    type: 'bar',
    data: {
        labels: referrerData.map(d => d.referrer_domain || 'Direct'),
        datasets: [{
            label: 'Clicks',
            data: referrerData.map(d => d.count),
            backgroundColor: colors.purple,
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            x: {
                beginAtZero: true,
                ticks: { precision: 0 }
            }
        }
    }
});
</script>