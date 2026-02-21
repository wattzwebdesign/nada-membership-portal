<x-filament-panels::page>
    <x-filament-widgets::widgets
        :widgets="$this->getVisibleFooterWidgets()"
        :columns="$this->getFooterWidgetsColumns()"
    />
</x-filament-panels::page>
