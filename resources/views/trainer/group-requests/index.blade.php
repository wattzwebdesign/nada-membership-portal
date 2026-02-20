<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Group Training Requests') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if ($requests->count() > 0)
                    {{-- Desktop Table --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Training Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Members</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($requests as $req)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <a href="{{ route('trainer.group-requests.show', $req) }}" class="text-sm font-medium hover:underline text-brand-primary">{{ $req->training_name }}</a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $req->company_full_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $req->training_date->format('M j, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $req->training_city }}, {{ $req->training_state }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $req->members_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($req->training)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Training Created</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Needs Training</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                                            <a href="{{ route('trainer.group-requests.show', $req) }}" class="font-medium text-brand-primary">View</a>
                                            @if (!$req->training)
                                                <a href="{{ route('trainer.trainings.create', ['from_request' => $req->id]) }}" class="font-medium text-green-700 hover:text-green-900">Create Training</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile Cards --}}
                    <div class="md:hidden divide-y divide-gray-200">
                        @foreach ($requests as $req)
                            <div class="p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <a href="{{ route('trainer.group-requests.show', $req) }}" class="text-sm font-medium hover:underline truncate text-brand-primary">{{ $req->training_name }}</a>
                                    @if ($req->training)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 flex-shrink-0 ml-2">Training Created</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 flex-shrink-0 ml-2">Needs Training</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500">{{ $req->company_full_name }} &middot; {{ $req->training_date->format('M j, Y') }}</p>
                                <p class="text-xs text-gray-400">{{ $req->training_city }}, {{ $req->training_state }} &middot; {{ $req->members_count }} members</p>
                                <div class="mt-2 flex space-x-3">
                                    <a href="{{ route('trainer.group-requests.show', $req) }}" class="text-xs font-medium text-brand-primary">View</a>
                                    @if (!$req->training)
                                        <a href="{{ route('trainer.trainings.create', ['from_request' => $req->id]) }}" class="text-xs font-medium text-green-700">Create Training</a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($requests->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200">
                            {{ $requests->links() }}
                        </div>
                    @endif
                @else
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                        <h3 class="mt-3 text-sm font-medium text-gray-900">No Group Training Requests</h3>
                        <p class="mt-1 text-sm text-gray-500">Paid group training requests from companies will appear here.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
