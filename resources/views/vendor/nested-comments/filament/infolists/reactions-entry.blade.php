<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <x-nested-comments::reactions :record="$getRecord()" />
</x-dynamic-component>
