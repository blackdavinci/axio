<x-filament-widgets::widget>
    @if($this->record)
        <livewire:nested-comments::comments :record="$this->record" />
    @else
        <x-filament::section>
            No Commentable record found. Please pass a record to the widget.
        </x-filament::section>
    @endif
</x-filament-widgets::widget>
