<div x-data wire:poll.15s>
    <div class="my-4 p-8 bg-gray-50 rounded-lg ring-gray-100 dark:bg-gray-950">
        <div class="flex flex-wrap items-center justify-between">
            <div x-data="{showFullDate: false}" class="flex items-center space-x-2">
                <x-filament::avatar
                        :src="$this->getAvatar()"
                        :alt="$this->getCommentator()"
                        :name="$this->getCommentator()"
                        size="md"
                        :circular="false"
                />
                <div x-on:mouseover="showFullDate = true" x-on:mouseout="showFullDate = false" class="cursor-pointer">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $this->getCommentator() }}
                    </p>
                    <p x-show="!showFullDate"
                       class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $this->comment->created_at?->diffForHumans() }}
                    </p>
                    <p x-show="showFullDate"
                       class="text-xs text-gray-500 dark:text-gray-400"
                    >{{ $this->comment->created_at->format('F j Y h:i:s A') }}</p>
                </div>
            </div>
            
            {{-- Actions de modification et suppression --}}
            @if(auth()->check())
                <div class="flex items-center space-x-2">
                    @can('update', $this->comment)
                        <x-filament::link
                            size="xs"
                            icon="heroicon-o-pencil"
                            class="text-blue-600 hover:text-blue-800"
                            wire:click.prevent="editComment"
                        >
                            Modifier
                        </x-filament::link>
                    @endcan
                    
                    @can('delete', $this->comment)
                        <x-filament::link
                            size="xs"
                            icon="heroicon-o-trash"
                            class="text-red-600 hover:text-red-800"
                            wire:click.prevent="deleteComment"
                            wire:confirm="Êtes-vous sûr de vouloir supprimer ce commentaire ?"
                        >
                            Supprimer
                        </x-filament::link>
                    @endcan
                </div>
            @endif
        </div>
        <div class="prose my-4 max-w-none dark:prose-invert">
            {!! e(new \Illuminate\Support\HtmlString($this->comment?->body)) !!}
        </div>
        <div class="flex flex-wrap items-center md:space-x-4 gap-2">
            <x-filament::link
                    size="xs"
                    class="cursor-pointer"
                    icon="heroicon-s-chat-bubble-left-right"
                    wire:click.prevent="toggleReplies">
                @if($this->comment->replies_count > 0)
                    <span title="{{ \Illuminate\Support\Number::format($this->comment->replies_count) }}">
                        {{\Illuminate\Support\Number::forHumans($this->comment->replies_count, maxPrecision: 3, abbreviate: true)}} {{ str('Reply')->plural($this->comment->replies_count) }}
                    </span>
                @else
                    <span title="{{__('No replies yet')}}">
                        Reply
                    </span>
                @endif
            </x-filament::link>
            <livewire:nested-comments::reaction-panel :record="$this->comment"/>
        </div>
    </div>
    @if($showReplies)
        <div x-ref="repliesContainer" class="pl-8 border-l pb-4 border-b rounded-bl-xl my-2">
            @foreach($this->comment->children as $reply)
                <livewire:nested-comments::comment-card
                        :key="$reply->getKey()"
                        :comment="$reply" />
            @endforeach
            <livewire:nested-comments::add-comment
                    :key="$comment->getKey()"
                    :commentable="$comment->commentable"
                    :reply-to="$comment"
                    :adding-comment="false"
                    wire:loading.attr="disabled"
            />
            <x-filament::icon-button
                    x-on:click="
                        if ($refs.repliesContainer && $refs.repliesContainer.offsetHeight && $refs.repliesContainer.style.display !== 'none') {
                            const offset = $refs.repliesContainer.offsetHeight;
                            window.scrollBy({ top: -offset, behavior: 'smooth' });
                        }
                    "
                    type="button"
                    label="Hide replies" icon="heroicon-o-minus-circle" class="absolute -left-8 -bottom-4" wire:click.prevent="toggleReplies"/>
        </div>
    @endif
</div>

@script
<script>
  document.querySelectorAll('[data-mention-id]').forEach(element => {
    // add an @ before using a pseudo-element
    element.classList.add(['comment-mention']);
  });
</script>
@endscript