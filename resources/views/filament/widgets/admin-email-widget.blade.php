<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Admin Notification Settings
        </x-slot>

        <form wire:submit="save">
            {{ $this->form }}

            <div class="mt-3">
                <x-filament::button type="submit" size="sm">
                    Save
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</x-filament-widgets::widget>
