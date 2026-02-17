@props(['progress'])

@php
    $steps = [
        ['key' => 'has_active_membership', 'label' => 'Active Membership', 'icon' => 'shield'],
        ['key' => 'has_training_registration', 'label' => 'Register for Training', 'icon' => 'ticket'],
        ['key' => 'has_completed_training', 'label' => 'Training Completed', 'icon' => 'academic-cap'],
        ['key' => 'has_approved_clinical', 'label' => 'Clinical Approved', 'icon' => 'clipboard-check'],
        ['key' => 'has_certificate', 'label' => 'Certificate Issued', 'icon' => 'document-check'],
    ];

    // Determine state of each step
    $states = [];
    $foundCurrent = false;
    foreach ($steps as $i => $step) {
        $completed = $progress[$step['key']] ?? false;
        if ($completed) {
            $states[$i] = 'completed';
        } elseif (!$foundCurrent) {
            $states[$i] = 'current';
            $foundCurrent = true;
        } else {
            $states[$i] = 'pending';
        }
    }
@endphp

{{-- Desktop: horizontal --}}
<div class="hidden sm:block">
    <div class="flex items-center justify-between">
        @foreach ($steps as $i => $step)
            <div class="flex items-center {{ $i < count($steps) - 1 ? 'flex-1' : '' }}">
                {{-- Step circle + label --}}
                <div class="flex flex-col items-center">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full
                        @if ($states[$i] === 'completed')
                            text-white
                        @elseif ($states[$i] === 'current')
                            text-white
                        @else
                            bg-gray-300 text-gray-500
                        @endif
                    " @if ($states[$i] === 'completed') style="background-color: #374269;" @elseif ($states[$i] === 'current') style="background-color: #d39c27;" @endif>
                        @if ($states[$i] === 'completed')
                            {{-- Checkmark --}}
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        @else
                            {{-- Step icon --}}
                            @if ($step['icon'] === 'shield')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                            @elseif ($step['icon'] === 'ticket')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z"/></svg>
                            @elseif ($step['icon'] === 'academic-cap')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>
                            @elseif ($step['icon'] === 'clipboard-check')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 011.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C8.003 4.025 7 5.089 7 6.358V18a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0019 18V6.358c0-1.269-1.003-2.333-2.226-2.442-.374-.03-.748-.057-1.124-.08m-5.8 0c-.376.023-.75.05-1.124.08C7.547 4.025 6.5 5.089 6.5 6.358V18A2.25 2.25 0 008.75 20.25M15 9l-4.5 4.5L9 12"/></svg>
                            @elseif ($step['icon'] === 'document-check')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9zm3.75 11.625l-2.625 2.625L9.75 15"/></svg>
                            @endif
                        @endif
                    </div>
                    <span class="mt-2 text-xs font-medium text-center
                        @if ($states[$i] === 'completed')
                        @elseif ($states[$i] === 'current')
                        @else
                            text-gray-400
                        @endif
                    " @if ($states[$i] === 'completed') style="color: #374269;" @elseif ($states[$i] === 'current') style="color: #d39c27;" @endif>{{ $step['label'] }}</span>
                </div>

                {{-- Connector line --}}
                @if ($i < count($steps) - 1)
                    <div class="flex-1 mx-3 mt-[-1.25rem]">
                        @if ($states[$i] === 'completed' && $states[$i + 1] === 'completed')
                            <div class="h-0.5" style="background-color: #374269;"></div>
                        @else
                            <div class="h-0.5 border-t-2 border-dashed border-gray-300"></div>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

{{-- Mobile: vertical stack --}}
<div class="sm:hidden space-y-3">
    @foreach ($steps as $i => $step)
        <div class="flex items-center gap-3">
            {{-- Circle --}}
            <div class="flex items-center justify-center w-8 h-8 rounded-full flex-shrink-0
                @if ($states[$i] === 'completed')
                    text-white
                @elseif ($states[$i] === 'current')
                    text-white
                @else
                    bg-gray-300 text-gray-500
                @endif
            " @if ($states[$i] === 'completed') style="background-color: #374269;" @elseif ($states[$i] === 'current') style="background-color: #d39c27;" @endif>
                @if ($states[$i] === 'completed')
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                @else
                    <span class="text-xs font-bold">{{ $i + 1 }}</span>
                @endif
            </div>

            {{-- Label --}}
            <span class="text-sm font-medium
                @if ($states[$i] === 'completed')
                @elseif ($states[$i] === 'current')
                @else
                    text-gray-400
                @endif
            " @if ($states[$i] === 'completed') style="color: #374269;" @elseif ($states[$i] === 'current') style="color: #d39c27;" @endif>{{ $step['label'] }}</span>
        </div>

        {{-- Vertical connector --}}
        @if ($i < count($steps) - 1)
            <div class="ml-4 pl-px">
                @if ($states[$i] === 'completed' && $states[$i + 1] === 'completed')
                    <div class="w-0.5 h-2" style="background-color: #374269;"></div>
                @else
                    <div class="w-0.5 h-2 border-l-2 border-dashed border-gray-300"></div>
                @endif
            </div>
        @endif
    @endforeach
</div>
