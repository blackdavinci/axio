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
                        @if(!$isEditing)
                            <x-filament::link
                                size="xs"
                                icon="heroicon-o-pencil"
                                class="text-blue-600 hover:text-blue-800"
                                wire:click.prevent="editComment"
                            >
                                Modifier
                            </x-filament::link>
                        @endif
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

        {{-- Contenu du commentaire ou formulaire d'édition --}}
        @if($isEditing)
            <div class="my-4">
                <textarea 
                    wire:model="editContent"
                    rows="4"
                    class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"
                    placeholder="Modifiez votre commentaire..."
                ></textarea>
                @error('editContent')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                
                <div class="flex justify-end space-x-2 mt-3">
                    <x-filament::button
                        size="sm"
                        color="gray"
                        wire:click="cancelEdit"
                    >
                        Annuler
                    </x-filament::button>
                    <x-filament::button
                        size="sm"
                        wire:click="saveEdit"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove wire:target="saveEdit">Sauvegarder</span>
                        <span wire:loading wire:target="saveEdit">Sauvegarde...</span>
                    </x-filament::button>
                </div>
            </div>
        @else
            <div class="prose my-4 max-w-none dark:prose-invert">
                {!! e(new \Illuminate\Support\HtmlString($this->comment?->body)) !!}
            </div>
        @endif

        {{-- Actions de réponse et réactions --}}
        @if(!$isEditing)
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
        @endif
    </div>
    
    {{-- Réponses --}}
    @if($showReplies)
        <div x-ref="repliesContainer" class="pl-8 border-l pb-4 border-b rounded-bl-xl my-2">
            @foreach($this->comment->children as $reply)
                <livewire:custom-comment-card
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
