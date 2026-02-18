<x-filament-panels::page>
    {{-- Render ONLY main widgets here. Header widgets are auto-rendered by Filament. --}}
    <x-filament-widgets::widgets
        :widgets="$this->getWidgets()"
        :columns="1"
    />
</x-filament-panels::page>
