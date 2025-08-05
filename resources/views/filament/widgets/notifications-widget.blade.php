<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-bell class="h-5 w-5 text-gray-500" />
                    <span>Notifications</span>
                    @if($this->getUnreadCount() > 0)
                        <x-filament::badge size="sm" color="danger">
                            {{ $this->getUnreadCount() }}
                        </x-filament::badge>
                    @endif
                </div>

                @if($this->getUnreadCount() > 0)
                    <x-filament::button
                        size="xs"
                        color="gray"
                        wire:click="markAllAsRead"
                    >
                        Tout marquer comme lu
                    </x-filament::button>
                @endif
            </div>
        </x-slot>

        <div class="space-y-3">
            @forelse($this->getNotifications() as $notification)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            @php
                                $iconColor = match($notification['color']) {
                                    'danger' => 'text-red-500',
                                    'warning' => 'text-yellow-500',
                                    'success' => 'text-green-500',
                                    'info' => 'text-blue-500',
                                    default => 'text-gray-500'
                                };
                            @endphp
                            <x-dynamic-component
                                :component="$notification['icon']"
                                class="h-6 w-6 {{ $iconColor }}"
                            />
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $notification['title'] }}
                                    </h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        {{ $notification['message'] }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                                        {{ $notification['time_ago'] }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-2 ml-4">
                                    @if($notification['url'])
                                        <x-filament::button
                                            tag="a"
                                            :href="$notification['url']"
                                            size="xs"
                                            color="primary"
                                            icon="heroicon-o-eye"
                                        >
                                            Voir
                                        </x-filament::button>
                                    @endif

                                    <x-filament::button
                                        wire:click="markAsRead('{{ $notification['id'] }}')"
                                        size="xs"
                                        color="gray"
                                        icon="heroicon-o-check"
                                    >
                                        Lu
                                    </x-filament::button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <x-heroicon-o-bell-slash class="h-10 w-10 text-gray-400 mx-auto mb-3" />
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Aucune notification non lue
                    </p>
                </div>
            @endforelse
        </div>

        @if($this->getNotifications()->count() >= 10)
            <div class="text-center mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <x-filament::button
                    tag="a"
                    href="/admin/notifications"
                    size="sm"
                    color="gray"
                >
                    Voir toutes les notifications
                </x-filament::button>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
