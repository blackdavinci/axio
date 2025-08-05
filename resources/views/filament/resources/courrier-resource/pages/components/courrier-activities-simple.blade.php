<div class="space-y-4">
    @if($this->record->activities->count() > 0)
        <div class="relative">
            <!-- Timeline line -->
            <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>
            
            <div class="space-y-6">
                @foreach($this->record->activities->sortByDesc('created_at') as $activity)
                    <div class="relative flex items-start space-x-3">
                        <!-- Timeline dot -->
                        <div class="relative flex h-8 w-8 flex-none items-center justify-center rounded-full
                            @switch($activity->event)
                                @case('created')
                                    bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400
                                    @break
                                @case('updated')
                                    bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400
                                    @break
                                @case('deleted')
                                    bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400
                                    @break
                                @default
                                    bg-gray-100 text-gray-600 dark:bg-gray-900/30 dark:text-gray-400
                            @endswitch
                        ">
                            @switch($activity->event)
                                @case('created')
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                                    </svg>
                                    @break
                                @case('updated')
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                    </svg>
                                    @break
                                @case('deleted')
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9zM4 5a2 2 0 012-2h8a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                                    </svg>
                                    @break
                                @default
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                            @endswitch
                        </div>
                        
                        <!-- Activity content -->
                        <div class="min-w-0 flex-1">
                            <div class="rounded-lg bg-white p-4 shadow ring-1 ring-gray-900/5 dark:bg-gray-800 dark:ring-white/10">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        @switch($activity->event)
                                            @case('created')
                                                Courrier créé
                                                @break
                                            @case('updated')
                                                Courrier modifié
                                                @break
                                            @case('deleted')
                                                Courrier supprimé
                                                @break
                                            @default
                                                {{ ucfirst($activity->event) }}
                                        @endswitch
                                    </h4>
                                    <time class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $activity->created_at->diffForHumans() }}
                                    </time>
                                </div>
                                
                                @if($activity->causer)
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                                        Par {{ $activity->causer->fullName() ?? $activity->causer->name ?? 'Système' }}
                                    </p>
                                @endif
                                
                                @if($activity->description)
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                        {{ $activity->description }}
                                    </p>
                                @endif
                                
                                @if($activity->event === 'updated' && isset($activity->properties['old']) && isset($activity->properties['attributes']))
                                    <div class="mt-3 space-y-2">
                                        <h5 class="text-xs font-medium text-gray-700 dark:text-gray-300">Modifications :</h5>
                                        <div class="space-y-1">
                                            @foreach($activity->properties['attributes'] as $key => $newValue)
                                                @if(isset($activity->properties['old'][$key]) && $activity->properties['old'][$key] != $newValue)
                                                    <div class="flex items-center text-xs">
                                                        <span class="font-medium text-gray-600 dark:text-gray-400 w-20">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                        <span class="text-red-600 dark:text-red-400 mx-2">{{ $activity->properties['old'][$key] ?? 'vide' }}</span>
                                                        <span class="text-gray-400">→</span>
                                                        <span class="text-green-600 dark:text-green-400 mx-2">{{ $newValue ?? 'vide' }}</span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                
                                <div class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                                    {{ $activity->created_at->format('d/m/Y à H:i:s') }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Aucun historique</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Aucune activité n'a été enregistrée pour ce courrier.
            </p>
        </div>
    @endif
</div>