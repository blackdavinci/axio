<div class="space-y-4">
    @if($this->record->activities->count() > 0)
        @foreach($this->record->activities->sortByDesc('created_at') as $activity)
            <div class="flex items-start space-x-3 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center
                        @switch($activity->event)
                            @case('created')
                                bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400
                                @break
                            @case('updated')
                                bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400
                                @break
                            @case('deleted')
                                bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-400
                                @break
                            @default
                                bg-gray-100 text-gray-600 dark:bg-gray-900 dark:text-gray-400
                        @endswitch
                    ">
                        @switch($activity->event)
                            @case('created')
                                <x-heroicon-o-plus class="w-4 h-4" />
                                @break
                            @case('updated')
                                <x-heroicon-o-pencil class="w-4 h-4" />
                                @break
                            @case('deleted')
                                <x-heroicon-o-trash class="w-4 h-4" />
                                @break
                            @default
                                <x-heroicon-o-document class="w-4 h-4" />
                        @endswitch
                    </div>
                </div>
                
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
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
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $activity->created_at->format('d/m/Y à H:i') }}
                        </p>
                    </div>
                    
                    @if($activity->causer)
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Par {{ $activity->causer->fullName() ?? $activity->causer->name ?? 'Système' }}
                        </p>
                    @endif
                    
                    @if($activity->description)
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            {{ $activity->description }}
                        </p>
                    @endif
                    
                    @if($activity->properties && $activity->properties->count() > 0)
                        <div class="mt-2">
                            @if($activity->event === 'updated' && isset($activity->properties['old']) && isset($activity->properties['attributes']))
                                <div class="text-xs space-y-1">
                                    @foreach($activity->properties['attributes'] as $key => $newValue)
                                        @if(isset($activity->properties['old'][$key]) && $activity->properties['old'][$key] != $newValue)
                                            <div class="grid grid-cols-3 gap-2 text-gray-600 dark:text-gray-400">
                                                <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                <span class="text-red-600 dark:text-red-400">{{ $activity->properties['old'][$key] ?? 'vide' }}</span>
                                                <span class="text-green-600 dark:text-green-400">→ {{ $newValue ?? 'vide' }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <div class="text-center py-8">
            <x-heroicon-o-clock class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600" />
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Aucun historique</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Aucune activité n'a été enregistrée pour ce courrier.
            </p>
        </div>
    @endif
</div>