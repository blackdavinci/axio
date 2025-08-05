<x-filament-panels::page>
    <div class="space-y-4">
        @forelse($this->notifications as $notification)
            <x-filament::card @class([
                'flex items-start p-4',
                'bg-gray-50 dark:bg-gray-800' => $notification->read_at, // Lire
                'bg-white dark:bg-gray-900 border-l-4 border-primary-500' => !$notification->read_at, // Non lu
            ])>
                <div class="flex-shrink-0 mr-4">
                    @if(isset($notification->data['icon']))
                        @svg($notification->data['icon'], 'w-8 h-8 text-' . ($notification->data['color'] ?? 'primary') . '-500')
                    @else
                        @svg('heroicon-o-bell', 'w-8 h-8 text-gray-500')
                    @endif
                </div>
                <div class="flex-grow">
                    <h3 class="font-bold text-lg mb-1">{{ $notification->data['title'] ?? 'Notification' }}</h3>
                    <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">{{ $notification->data['message'] ?? 'Détail de la notification.' }}</p>
                    @if(isset($notification->data['url']))
                        <a href="{{ $notification->data['url'] }}" class="text-sm text-primary-600 hover:underline block">
                            Voir les détails
                        </a>
                    @endif
                    <span class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                </div>
                <div class="flex-shrink-0 ml-4">
                    @if(!$notification->read_at)
                        <x-filament::icon-button
                            icon="heroicon-o-check-circle"
                            label="Marquer comme lu"
                            tooltip="Marquer comme lu"
                            wire:click="markAsRead('{{ $notification->id }}')"
                            size="md"
                            color="success"
                        />
                    @else
                        <x-filament::badge color="success" icon="heroicon-o-check">Lu</x-filament::badge>
                    @endif
                </div>
            </x-filament::card>
        @empty
            <div class="p-6 text-center text-gray-500">
                <p>Aucune notification à afficher.</p>
            </div>
        @endforelse

        <div class="mt-4">
            {{ $this->notifications->links() }}
        </div>
    </div>
</x-filament-panels::page>
