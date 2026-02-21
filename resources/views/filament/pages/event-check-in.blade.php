<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Event Selector --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select Event</label>
                    <select wire:model.live="selectedEventId" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 shadow-sm">
                        <option value="">All Events</option>
                        @foreach ($this->getEvents() as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Stats Cards --}}
        @php $stats = $this->getStats(); @endphp
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6 text-center">
                <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</div>
                <div class="text-sm text-gray-500">Total Registrations</div>
            </div>
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6 text-center">
                <div class="text-3xl font-bold text-green-600">{{ $stats['checked_in'] }}</div>
                <div class="text-sm text-gray-500">Checked In</div>
            </div>
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6 text-center">
                <div class="text-3xl font-bold text-yellow-600">{{ $stats['remaining'] }}</div>
                <div class="text-sm text-gray-500">Remaining</div>
            </div>
        </div>

        {{-- Scan Result --}}
        @if ($scanResult)
            <div class="fi-section rounded-xl shadow-sm ring-1 p-6 {{ $scanResult['success'] ? 'bg-green-50 ring-green-500/20 dark:bg-green-900/20' : 'bg-red-50 ring-red-500/20 dark:bg-red-900/20' }}">
                @if ($scanResult['success'])
                    <div class="flex items-center gap-3">
                        <x-heroicon-o-check-circle class="w-8 h-8 text-green-600" />
                        <div>
                            <p class="text-lg font-semibold text-green-800 dark:text-green-200">{{ $scanResult['name'] }} checked in!</p>
                            <p class="text-sm text-green-600">{{ $scanResult['registration_number'] }} - {{ $scanResult['event'] }}</p>
                        </div>
                    </div>
                @else
                    <div class="flex items-center gap-3">
                        <x-heroicon-o-x-circle class="w-8 h-8 text-red-600" />
                        <div>
                            <p class="text-lg font-semibold text-red-800 dark:text-red-200">{{ $scanResult['message'] }}</p>
                            @if (isset($scanResult['name']))
                                <p class="text-sm text-red-600">{{ $scanResult['name'] }}</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- QR Scanner --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <h3 class="text-lg font-semibold mb-4">QR Code Scanner</h3>
            <div id="qr-reader" style="width: 100%; max-width: 500px; margin: 0 auto;"></div>
            <p class="text-center text-sm text-gray-500 mt-2">Point your camera at an attendee's QR code</p>
        </div>

        {{-- Manual Search --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <h3 class="text-lg font-semibold mb-4">Manual Search</h3>
            <div class="flex gap-2">
                <input type="text" wire:model="searchQuery" wire:keydown.enter="search"
                    placeholder="Search by name, email, or registration number..."
                    class="flex-1 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 shadow-sm">
                <button wire:click="search" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    Search
                </button>
            </div>

            @if (count($searchResults))
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Reg #</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($searchResults as $result)
                                <tr>
                                    <td class="px-4 py-2 text-sm">{{ $result['registration_number'] }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $result['first_name'] }} {{ $result['last_name'] }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $result['email'] }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        @if ($result['checked_in_at'])
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Checked In
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Not Checked In
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        @if (!$result['checked_in_at'])
                                            <button wire:click="manualCheckIn({{ $result['id'] }})"
                                                class="text-sm text-primary-600 hover:text-primary-800 font-medium">
                                                Check In
                                            </button>
                                        @else
                                            <span class="text-gray-400">--</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const qrReader = document.getElementById('qr-reader');
            if (!qrReader) return;

            const html5QrCode = new Html5Qrcode("qr-reader");

            html5QrCode.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                (decodedText) => {
                    html5QrCode.stop();
                    // Extract token from URL or use as-is
                    const url = new URL(decodedText, window.location.origin);
                    const token = url.searchParams.get('scan') || decodedText;
                    // Redirect to process the scan
                    window.location.href = window.location.pathname + '?scan=' + encodeURIComponent(token);
                },
                (errorMessage) => {
                    // Ignore scan errors
                }
            ).catch((err) => {
                console.log('Camera not available:', err);
                qrReader.innerHTML = '<p class="text-center text-gray-500 py-8">Camera not available. Use manual search below.</p>';
            });
        });
    </script>
    @endpush
</x-filament-panels::page>
