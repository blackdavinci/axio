<div class="max-w-full mx-auto space-y-6">
    {{-- Messages flash --}}
    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            {{ session('error') }}
        </div>
    @endif

    {{-- Formulaire d'ajout de commentaire --}}
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Ajouter un commentaire</h3>
        
        <form wire:submit="addComment">
            <div class="relative">
                <label for="newComment" class="sr-only">Votre commentaire</label>
                <textarea 
                    wire:model="newComment"
                    id="newComment"
                    rows="4" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                    placeholder="Écrivez votre commentaire... (Utilisez @nom pour mentionner un utilisateur)"
                ></textarea>
                @error('newComment') 
                    <span class="text-red-500 text-sm">{{ $message }}</span> 
                @enderror
                
                {{-- Liste des utilisateurs pour mentions --}}
                @if($showUsersList && $mentionableUsers->count() > 0)
                    <div class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">
                        @foreach($mentionableUsers as $user)
                            <button 
                                type="button" 
                                wire:click="addMention('{{ $user->name }}')"
                                class="w-full px-4 py-2 text-left hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
                            >
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center mr-3">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <span>{{ $user->name }}</span>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
            
            <div class="flex justify-between items-center mt-4">
                <div class="text-sm text-gray-500">
                    Vous pouvez utiliser @nom pour mentionner un utilisateur
                </div>
                <button 
                    type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove>Publier</span>
                    <span wire:loading>Publication...</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Liste des commentaires --}}
    <div class="space-y-4">
        @forelse($comments as $comment)
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                {{-- Commentaire principal --}}
                <div class="flex items-start space-x-4">
                    {{-- Avatar --}}
                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-medium">
                        {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                    </div>
                    
                    {{-- Contenu du commentaire --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <h4 class="text-sm font-medium text-gray-900">{{ $comment->user->name }}</h4>
                                <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                @if($comment->is_edited)
                                    <span class="text-xs text-gray-400">(modifié)</span>
                                @endif
                            </div>
                            
                            {{-- Actions du commentaire --}}
                            @if($comment->canBeEditedBy(auth()->user()) || $comment->canBeDeletedBy(auth()->user()))
                                <div class="flex items-center space-x-2">
                                    @if($comment->canBeEditedBy(auth()->user()))
                                        <button 
                                            wire:click="editComment({{ $comment->id }})"
                                            class="text-xs text-blue-600 hover:text-blue-800"
                                        >
                                            Modifier
                                        </button>
                                    @endif
                                    @if($comment->canBeDeletedBy(auth()->user()))
                                        <button 
                                            wire:click="deleteComment({{ $comment->id }})"
                                            wire:confirm="Êtes-vous sûr de vouloir supprimer ce commentaire ?"
                                            class="text-xs text-red-600 hover:text-red-800"
                                        >
                                            Supprimer
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                        
                        {{-- Contenu du commentaire --}}
                        @if($editingComment === $comment->id)
                            {{-- Mode édition --}}
                            <form wire:submit="updateComment" class="mt-2">
                                <textarea 
                                    wire:model="editContent"
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                ></textarea>
                                @error('editContent') 
                                    <span class="text-red-500 text-sm">{{ $message }}</span> 
                                @enderror
                                <div class="flex justify-end space-x-2 mt-2">
                                    <button 
                                        type="button"
                                        wire:click="cancelEdit"
                                        class="px-3 py-1 text-sm text-gray-600 border border-gray-300 rounded hover:bg-gray-50"
                                    >
                                        Annuler
                                    </button>
                                    <button 
                                        type="submit"
                                        class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
                                    >
                                        Sauvegarder
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="mt-2 text-sm text-gray-700">
                                {!! nl2br(e($comment->content)) !!}
                            </div>
                        @endif
                        
                        {{-- Actions de réponse --}}
                        <div class="mt-3 flex items-center space-x-4">
                            <button 
                                wire:click="replyToComment({{ $comment->id }})"
                                class="text-xs text-blue-600 hover:text-blue-800 font-medium"
                            >
                                Répondre
                            </button>
                            @if($comment->replies->count() > 0)
                                <span class="text-xs text-gray-500">
                                    {{ $comment->replies->count() }} 
                                    {{ $comment->replies->count() > 1 ? 'réponses' : 'réponse' }}
                                </span>
                            @endif
                        </div>
                        
                        {{-- Formulaire de réponse --}}
                        @if($replyTo === $comment->id)
                            <form wire:submit="submitReply" class="mt-4">
                                <div class="flex items-start space-x-3">
                                    <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center text-gray-600 text-sm">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </div>
                                    <div class="flex-1">
                                        <textarea 
                                            wire:model="replyContent"
                                            rows="2"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                            placeholder="Écrivez votre réponse..."
                                        ></textarea>
                                        @error('replyContent') 
                                            <span class="text-red-500 text-sm">{{ $message }}</span> 
                                        @enderror
                                        <div class="flex justify-end space-x-2 mt-2">
                                            <button 
                                                type="button"
                                                wire:click="cancelReply"
                                                class="px-3 py-1 text-sm text-gray-600 border border-gray-300 rounded hover:bg-gray-50"
                                            >
                                                Annuler
                                            </button>
                                            <button 
                                                type="submit"
                                                class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
                                            >
                                                Répondre
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        @endif
                        
                        {{-- Réponses --}}
                        @if($comment->replies->count() > 0)
                            <div class="mt-4 space-y-3">
                                @foreach($comment->replies as $reply)
                                    <div class="flex items-start space-x-3 pl-4 border-l-2 border-gray-200">
                                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white text-sm">
                                            {{ strtoupper(substr($reply->user->name, 0, 1)) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-2">
                                                    <h5 class="text-sm font-medium text-gray-900">{{ $reply->user->name }}</h5>
                                                    <span class="text-xs text-gray-500">{{ $reply->created_at->diffForHumans() }}</span>
                                                    @if($reply->is_edited)
                                                        <span class="text-xs text-gray-400">(modifié)</span>
                                                    @endif
                                                </div>
                                                
                                                {{-- Actions de la réponse --}}
                                                @if($reply->canBeEditedBy(auth()->user()) || $reply->canBeDeletedBy(auth()->user()))
                                                    <div class="flex items-center space-x-2">
                                                        @if($reply->canBeEditedBy(auth()->user()))
                                                            <button 
                                                                wire:click="editComment({{ $reply->id }})"
                                                                class="text-xs text-blue-600 hover:text-blue-800"
                                                            >
                                                                Modifier
                                                            </button>
                                                        @endif
                                                        @if($reply->canBeDeletedBy(auth()->user()))
                                                            <button 
                                                                wire:click="deleteComment({{ $reply->id }})"
                                                                wire:confirm="Êtes-vous sûr de vouloir supprimer cette réponse ?"
                                                                class="text-xs text-red-600 hover:text-red-800"
                                                            >
                                                                Supprimer
                                                            </button>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            {{-- Contenu de la réponse --}}
                                            @if($editingComment === $reply->id)
                                                {{-- Mode édition --}}
                                                <form wire:submit="updateComment" class="mt-1">
                                                    <textarea 
                                                        wire:model="editContent"
                                                        rows="2"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                                    ></textarea>
                                                    @error('editContent') 
                                                        <span class="text-red-500 text-sm">{{ $message }}</span> 
                                                    @enderror
                                                    <div class="flex justify-end space-x-2 mt-2">
                                                        <button 
                                                            type="button"
                                                            wire:click="cancelEdit"
                                                            class="px-3 py-1 text-sm text-gray-600 border border-gray-300 rounded hover:bg-gray-50"
                                                        >
                                                            Annuler
                                                        </button>
                                                        <button 
                                                            type="submit"
                                                            class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
                                                        >
                                                            Sauvegarder
                                                        </button>
                                                    </div>
                                                </form>
                                            @else
                                                <div class="mt-1 text-sm text-gray-700">
                                                    {!! nl2br(e($reply->content)) !!}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun commentaire</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Soyez le premier à commenter sur ce courrier.
                </p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($comments->hasPages())
        <div class="mt-6">
            {{ $comments->links() }}
        </div>
    @endif
</div>
