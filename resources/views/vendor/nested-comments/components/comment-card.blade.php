@props([
    'comment' => null,
])
@if ($comment?->getKey())
<livewire:nested-comments::comment-card
        :key="$comment->getKey()"
        :comment="$comment"
/>
    @else
    <p class="text-gray-500 dark:text-gray-400">
        {{ __('No comment provided.') }}
    </p>
@endif