<x-public-layout>
    <x-slot name="title">Group Training Registration — NADA</x-slot>

    <div class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold" style="color: #374269;">Group Training Registration</h1>
            <p class="mt-2 text-gray-600">Register your team for a NADA training session. Fill in the details below and complete payment to confirm your booking.</p>
        </div>

        @if (session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                <p class="font-medium">Please correct the following errors:</p>
                <ul class="mt-2 list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('group-training.store') }}"
              x-data="groupTrainingForm()"
              x-init="init()"
              class="space-y-8">
            @csrf

            {{-- Company Contact --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h2 class="text-lg font-semibold mb-4" style="color: #374269;">Company Contact</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="company_first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                        <input type="text" name="company_first_name" id="company_first_name" value="{{ old('company_first_name') }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="company_last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                        <input type="text" name="company_last_name" id="company_last_name" value="{{ old('company_last_name') }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="company_email" class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" name="company_email" id="company_email" value="{{ old('company_email') }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                </div>
            </div>

            {{-- Training Details --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h2 class="text-lg font-semibold mb-4" style="color: #374269;">Training Details</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label for="training_name" class="block text-sm font-medium text-gray-700">Training Name</label>
                        <input type="text" name="training_name" id="training_name" value="{{ old('training_name') }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="training_date" class="block text-sm font-medium text-gray-700">Training Date</label>
                        <input type="date" name="training_date" id="training_date" value="{{ old('training_date') }}" required
                               min="{{ now()->addDay()->format('Y-m-d') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="trainer_id" class="block text-sm font-medium text-gray-700">Trainer</label>
                        <select name="trainer_id" id="trainer_id" required x-model="trainerId"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Select a trainer...</option>
                            @foreach ($trainers as $trainer)
                                <option value="{{ $trainer->id }}" {{ old('trainer_id', $prefillTrainer) == $trainer->id ? 'selected' : '' }}>
                                    {{ $trainer->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="training_city" class="block text-sm font-medium text-gray-700">City</label>
                        <input type="text" name="training_city" id="training_city" value="{{ old('training_city') }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="training_state" class="block text-sm font-medium text-gray-700">State</label>
                        <select name="training_state" id="training_state" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Select...</option>
                            @foreach (['AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY','DC'] as $st)
                                <option value="{{ $st }}" {{ old('training_state') === $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Pricing --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h2 class="text-lg font-semibold mb-4" style="color: #374269;">Pricing</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="cost_per_ticket_display" class="block text-sm font-medium text-gray-700">Cost Per Ticket ($)</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" id="cost_per_ticket_display" step="0.01" min="1.00" required
                                   x-model="costPerTicketDollars"
                                   @input="updatePricing()"
                                   class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        <input type="hidden" name="cost_per_ticket_cents" :value="costPerTicketCents">
                    </div>
                    <div>
                        <label for="number_of_tickets" class="block text-sm font-medium text-gray-700">Number of Tickets</label>
                        <input type="number" name="number_of_tickets" id="number_of_tickets" min="1" max="500" required
                               x-model="numberOfTickets"
                               @input="syncMembers(); updatePricing()"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                </div>

                {{-- Price Summary --}}
                <div class="mt-4 bg-gray-50 rounded-md p-4" x-show="costPerTicketCents > 0 && numberOfTickets > 0">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal (<span x-text="numberOfTickets"></span> tickets &times; $<span x-text="costPerTicketDollars"></span>)</span>
                            <span class="font-medium" x-text="'$' + subtotalFormatted"></span>
                        </div>
                        <div class="flex justify-between" x-show="feeCents > 0">
                            <span class="text-gray-600">Transaction Fee</span>
                            <span class="font-medium" x-text="'$' + feeFormatted"></span>
                        </div>
                        <div class="flex justify-between border-t border-gray-200 pt-2">
                            <span class="font-semibold" style="color: #374269;">Total</span>
                            <span class="font-semibold" style="color: #374269;" x-text="'$' + totalFormatted"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Members Repeater --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold" style="color: #374269;">Team Members</h2>
                    <span class="text-sm text-gray-500" x-text="members.length + ' of ' + numberOfTickets + ' members'"></span>
                </div>
                <p class="text-sm text-gray-500 mb-4">Enter the details for each team member who will attend the training.</p>

                <template x-for="(member, index) in members" :key="index">
                    <div class="border border-gray-200 rounded-md p-4 mb-3">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-medium text-gray-700" x-text="'Member ' + (index + 1)"></span>
                            <button type="button" @click="removeMember(index)" x-show="members.length > 1"
                                    class="text-red-500 hover:text-red-700 text-sm">Remove</button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <input type="text" :name="'members[' + index + '][first_name]'" placeholder="First Name" required
                                       x-model="member.first_name"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <input type="text" :name="'members[' + index + '][last_name]'" placeholder="Last Name" required
                                       x-model="member.last_name"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <input type="email" :name="'members[' + index + '][email]'" placeholder="Email" required
                                       x-model="member.email"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>
                    </div>
                </template>

                <button type="button" @click="addMember()"
                        x-show="members.length < numberOfTickets"
                        class="mt-2 inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Member
                </button>
            </div>

            {{-- Submit --}}
            <div class="flex justify-end">
                <button type="submit"
                        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white shadow-sm hover:opacity-90 transition"
                        style="background-color: #374269;">
                    Proceed to Payment
                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function groupTrainingForm() {
            return {
                trainerId: '{{ old('trainer_id', $prefillTrainer ?? '') }}',
                costPerTicketDollars: '{{ old('cost_per_ticket_cents') ? number_format(old('cost_per_ticket_cents') / 100, 2, '.', '') : ($prefillPrice ? number_format($prefillPrice / 100, 2, '.', '') : '') }}',
                numberOfTickets: {{ old('number_of_tickets', $prefillTickets ?? 1) }},
                feeType: '{{ $feeType }}',
                feeValue: parseFloat('{{ $feeValue }}'),
                members: [],

                get costPerTicketCents() {
                    return Math.round(parseFloat(this.costPerTicketDollars || 0) * 100);
                },

                get subtotalCents() {
                    return this.costPerTicketCents * parseInt(this.numberOfTickets || 0);
                },

                get feeCents() {
                    if (this.feeValue <= 0) return 0;
                    if (this.feeType === 'percentage') {
                        return Math.round(this.subtotalCents * this.feeValue / 100);
                    }
                    return Math.round(this.feeValue * 100);
                },

                get totalCents() {
                    return this.subtotalCents + this.feeCents;
                },

                get subtotalFormatted() {
                    return (this.subtotalCents / 100).toFixed(2);
                },

                get feeFormatted() {
                    return (this.feeCents / 100).toFixed(2);
                },

                get totalFormatted() {
                    return (this.totalCents / 100).toFixed(2);
                },

                init() {
                    // Restore old members from validation errors, or initialize from ticket count
                    @if(old('members'))
                        this.members = @json(old('members'));
                    @else
                        this.syncMembers();
                    @endif
                },

                syncMembers() {
                    const count = parseInt(this.numberOfTickets) || 1;
                    while (this.members.length < count) {
                        this.members.push({ first_name: '', last_name: '', email: '' });
                    }
                    while (this.members.length > count) {
                        this.members.pop();
                    }
                },

                addMember() {
                    if (this.members.length < parseInt(this.numberOfTickets)) {
                        this.members.push({ first_name: '', last_name: '', email: '' });
                    }
                },

                removeMember(index) {
                    if (this.members.length > 1) {
                        this.members.splice(index, 1);
                        this.numberOfTickets = this.members.length;
                    }
                },

                updatePricing() {
                    // Reactive getters handle this automatically
                },
            };
        }
    </script>
    @endpush
</x-public-layout>
