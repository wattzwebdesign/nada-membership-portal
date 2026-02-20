<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Certificates') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Certification Progress</h3>
                <x-certificate-progress :progress="$progress" />
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-brand-primary">Certificates</h3>
                        <span class="text-sm text-gray-500">{{ $certificates->total() }} total</span>
                    </div>

                    @if ($certificates->count() > 0)
                        {{-- Desktop Table --}}
                        <div class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Certificate Code</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Issued</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiration</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($certificates as $certificate)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-sm font-medium text-gray-900 font-mono">{{ $certificate->certificate_code }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $certificate->date_issued->format('M j, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $certificate->expiration_date ? $certificate->expiration_date->format('M j, Y') : 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if ($certificate->status === 'active')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Active
                                                    </span>
                                                @elseif ($certificate->status === 'expired')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        Expired
                                                    </span>
                                                @elseif ($certificate->status === 'revoked')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        Revoked
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <a href="{{ route('certificates.download', $certificate) }}" data-umami-event="Certificate Download" class="inline-flex items-center px-3 py-1.5 border text-xs font-medium rounded-md hover:bg-gray-50 transition border-brand-primary text-brand-primary">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                    Download PDF
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Mobile Cards --}}
                        <div class="md:hidden space-y-3">
                            @foreach ($certificates as $certificate)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-900 font-mono">{{ $certificate->certificate_code }}</span>
                                        @if ($certificate->status === 'active')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                                        @elseif ($certificate->status === 'expired')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Expired</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ ucfirst($certificate->status) }}</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500">Issued: {{ $certificate->date_issued->format('M j, Y') }}</p>
                                    <p class="text-xs text-gray-500">Expires: {{ $certificate->expiration_date ? $certificate->expiration_date->format('M j, Y') : 'N/A' }}</p>
                                    <div class="mt-3">
                                        <a href="{{ route('certificates.download', $certificate) }}" data-umami-event="Certificate Download" class="inline-flex items-center px-3 py-1.5 border text-xs font-medium rounded-md border-brand-primary text-brand-primary">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            Download PDF
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if ($certificates->hasPages())
                            <div class="mt-6">
                                {{ $certificates->links() }}
                            </div>
                        @endif
                    @else
                        @php
                            if (! ($progress['has_active_membership'] ?? false)) {
                                $emptyMessage = 'Get an active membership to start your certification journey.';
                                $emptyAction = 'View Plans';
                                $emptyUrl = route('membership.plans');
                            } elseif (! ($progress['has_training_registration'] ?? false)) {
                                $emptyMessage = 'Register for a training to begin your certification.';
                                $emptyAction = 'Browse Trainings';
                                $emptyUrl = route('trainings.index');
                            } elseif (! ($progress['has_completed_training'] ?? false)) {
                                $emptyMessage = 'Complete your training to continue toward your certificate.';
                                $emptyAction = 'My Registrations';
                                $emptyUrl = route('trainings.my-registrations');
                            } elseif (! ($progress['has_approved_clinical'] ?? false)) {
                                $emptyMessage = 'Submit your 40 hours of clinicals to continue toward your certificate.';
                                $emptyAction = 'Submit Clinicals';
                                $emptyUrl = route('clinicals.create');
                            } else {
                                $emptyMessage = 'Your clinical hours have been approved. Your certificate will be issued shortly.';
                                $emptyAction = null;
                                $emptyUrl = null;
                            }
                        @endphp
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            <h3 class="mt-3 text-sm font-medium text-gray-900">No Certificates Yet</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ $emptyMessage }}</p>
                            @if ($emptyAction)
                                <div class="mt-6">
                                    <a href="{{ $emptyUrl }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-primary">
                                        {{ $emptyAction }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
