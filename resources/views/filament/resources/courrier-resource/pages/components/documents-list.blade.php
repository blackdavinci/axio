@if($getRecord()->media->count() > 0)
    <div class="space-y-4">
        @foreach($getRecord()->media as $media)
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
                <div class="flex items-start justify-between">
                    <div class="flex items-center space-x-3 flex-1">
                        <div class="flex-shrink-0">
                            @php
                                $iconName = match($media->mime_type) {
                                    'application/pdf' => 'heroicon-o-document',
                                    'image/jpeg', 'image/png', 'image/gif' => 'heroicon-o-photo',
                                    'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'heroicon-o-document-text',
                                    default => 'heroicon-o-document',
                                };
                                $iconColor = match($media->mime_type) {
                                    'application/pdf' => 'text-red-500',
                                    'image/jpeg', 'image/png', 'image/gif' => 'text-green-500',
                                    'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'text-blue-500',
                                    default => 'text-gray-500',
                                };
                                $typeLabel = match($media->mime_type) {
                                    'application/pdf' => 'PDF',
                                    'image/jpeg' => 'JPEG',
                                    'image/png' => 'PNG',
                                    'application/msword' => 'DOC',
                                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'DOCX',
                                    default => strtoupper(pathinfo($media->name, PATHINFO_EXTENSION)),
                                };
                            @endphp
                            <x-filament::icon :icon="$iconName" class="w-8 h-8 {{ $iconColor }}" />
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-2 mb-1">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                    {{ $media->file_name ?? $media->name }}
                                </h4>
                                <x-filament::badge 
                                    :color="match(true) {
                                        $media->mime_type === 'application/pdf' => 'danger',
                                        str_starts_with($media->mime_type, 'image/') => 'success',
                                        str_contains($media->mime_type, 'document') => 'primary',
                                        default => 'gray',
                                    }"
                                >
                                    {{ $typeLabel }}
                                </x-filament::badge>
                            </div>
                            
                            <div class="flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
                                <span class="flex items-center">
                                    <x-filament::icon icon="heroicon-o-scale" class="w-3 h-3 mr-1" />
                                    {{ $media->human_readable_size }}
                                </span>
                                <span class="flex items-center">
                                    <x-filament::icon icon="heroicon-o-clock" class="w-3 h-3 mr-1" />
                                    {{ $media->created_at->format('d/m/Y à H:i') }}
                                </span>
                                <span class="text-gray-400">•</span>
                                <span>{{ $media->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-2 ml-4">
                        <x-filament::button
                            color="primary"
                            size="sm"
                            icon="heroicon-o-arrow-down-tray"
                            href="{{ url('media/' . $media->id . '/download') }}"
                            tooltip="Télécharger le document"
                        >
                            Télécharger
                        </x-filament::button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="text-center py-12">
        <x-filament::icon icon="heroicon-o-document" class="w-16 h-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
            Aucun document joint
        </h3>
        <p class="text-gray-500 dark:text-gray-400">
            Ce courrier n'a pas encore de documents joints.
        </p>
    </div>
@endif