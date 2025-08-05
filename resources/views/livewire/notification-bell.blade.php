<div class="relative" x-data="{ open: @entangle('showDropdown') }">
    <!-- Cloche de notification -->
    <button 
        @click="open = !open"
        class="relative flex items-center justify-center w-8 h-8 text-gray-500 hover:text-gray-700 focus:outline-none focus:text-gray-700 transition-colors"
        aria-label="Notifications"
    >
        <x-heroicon-o-bell class="w-6 h-6" />
        
        <!-- Badge de compteur -->
        @if($this->unreadCount > 0)
            <span class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-500 rounded-full min-w-[1.25rem]">
                {{ $this->unreadCount > 99 ? '99+' : $this->unreadCount }}
            </span>
        @endif
    </button>

    <!-- Dropdown des notifications -->
    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.away="open = false"
        class="absolute right-0 sm:right-0 z-50 mt-2 w-80 sm:w-96 max-w-[90vw] bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none dark:bg-gray-800 dark:ring-gray-700 origin-top-right"
        style="display: none;"
        x-init="$watch('open', value => {
            if (value) {
                $nextTick(() => {
                    const rect = $el.getBoundingClientRect();
                    const viewportWidth = window.innerWidth;
                    if (rect.right > viewportWidth) {
                        $el.style.right = '0px';
                        $el.style.left = 'auto';
                        $el.style.transform = 'translateX(0)';
                    }
                });
            }
        })"
    >
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                    Notifications
                </h3>
                @if($this->unreadCount > 0)
                    <button 
                        wire:click="markAllAsRead"
                        class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400"
                    >
                        Tout marquer comme lu
                    </button>
                @endif
            </div>
        </div>

        <!-- Liste des notifications -->
        <div class="max-h-80 overflow-y-auto">
            @forelse($this->notifications as $notification)
                <div class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-start space-x-3">
                        <!-- Icône -->
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
                                class="w-5 h-5 {{ $iconColor }}" 
                            />
                        </div>
                        
                        <!-- Contenu -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $notification['title'] }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                {{ $notification['message'] }}
                            </p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                {{ $notification['time_ago'] }}
                            </p>
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex-shrink-0 flex items-center space-x-1">
                            @if($notification['url'])
                                <a 
                                    href="{{ $notification['url'] }}" 
                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400"
                                    @click="open = false"
                                >
                                    <x-heroicon-o-eye class="w-4 h-4" />
                                </a>
                            @endif
                            <button 
                                wire:click="markAsRead('{{ $notification['id'] }}')"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                            >
                                <x-heroicon-o-check class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-center">
                    <x-heroicon-o-bell-slash class="w-8 h-8 text-gray-400 mx-auto mb-2" />
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Aucune notification
                    </p>
                </div>
            @endforelse
        </div>

        <!-- Footer -->
        @if($this->notifications->count() > 0)
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                <a 
                    href="/admin" 
                    class="block text-center text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400"
                    @click="open = false"
                >
                    Voir toutes les notifications
                </a>
            </div>
        @endif
    </div>
</div>
