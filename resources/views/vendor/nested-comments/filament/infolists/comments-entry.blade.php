<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <livewire:nested-comments::comments :record="$getRecord()" />
</x-dynamic-component>
