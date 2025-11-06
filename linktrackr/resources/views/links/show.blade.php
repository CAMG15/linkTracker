<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Link Details
            </h2>
            <a href="{{ route('dashboard') }}" class="text-sm text-blue-600 hover:text-blue-800">← Back to Dashboard</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Link Information -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Link Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Short URL</label>
                            <div class="flex items-center gap-2">
                                <input type="text" readonly value="{{ $link->short_url }}" class="flex-1 bg-gray-50 border border-gray-300 rounded-md px-3 py-2 text-sm" id="shortUrl">
                                <button onclick="copyToClipboard('shortUrl')" class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">Copy</button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Original URL</label>
                            <a href="{{ $link->original_url }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm break-all">
                                {{ $link->original_url }}
                            </a>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Created</label>
                            <p class="text-sm text-gray-900">{{ $link->created_at->format('M d, Y g:i A') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Total Clicks</label>
                            <p class="text-2xl font-bold text-gray-900">{{ $link->clicks_count }}</p>
                            <p class="text-xs text-gray-500">QR: {{ $link->qr_clicks }} | Direct: {{ $link->clicks_count - $link->qr_clicks }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- QR Codes -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">QR Codes</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="text-center">
                            <img src="{{ asset('storage/' . $link->qr_code_png) }}" alt="QR Code PNG" class="mx-auto border border-gray-200 rounded-lg p-4 bg-white" style="max-width: 250px;">
                            <a href="{{ route('links.qr.download', [$link, 'png']) }}" class="mt-3 inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 text-sm">
                                Download PNG
                            </a>
                        </div>

                        <div class="text-center">
                            <img src="{{ asset('storage/' . $link->qr_code_svg) }}" alt="QR Code SVG" class="mx-auto border border-gray-200 rounded-lg p-4 bg-white" style="max-width: 250px;">
                            <a href="{{ route('links.qr.download', [$link, 'svg']) }}" class="mt-3 inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 text-sm">
                                Download SVG
                            </a>
                        </div>
                    </div>

                    <p class="mt-4 text-sm text-gray-500 text-center">
                        Scan with a QR code reader to visit: <span class="font-mono">{{ $link->short_url }}?qr=1</span>
                    </p>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
                        <a href="{{ route('analytics.show', $link) }}" class="text-sm text-blue-600 hover:text-blue-800">View Full Analytics →</a>
                    </div>

                    @if($recentClicks->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Device</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($recentClicks as $click)
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $click->clicked_at->diffForHumans() }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $click->country_name ?? 'Unknown' }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $click->device_icon }} {{ ucfirst($click->device_type) }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                            @if($click->is_qr_code)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">QR Code</span>
                                            @else
                                                {{ $click->referrer_source }}
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center text-gray-500 py-8">No clicks yet. Share your link to start tracking!</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    document.execCommand('copy');
    alert('Copied to clipboard!');
}
</script>