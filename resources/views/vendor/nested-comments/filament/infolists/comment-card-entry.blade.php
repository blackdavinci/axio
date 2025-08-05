<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <livewire:nested-comments::comment-card
        :key="$entry->getKey()"
        :comment="$getRecord()"
        :user-avatar="$evaluateUserAvatar()"
        :user-name="$evaluateUserName()"
    />
</x-dynamic-component>
