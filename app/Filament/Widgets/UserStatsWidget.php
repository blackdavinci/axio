<?php

namespace App\Filament\Widgets;

use App\Models\Structure;
use App\Models\User;
use App\Models\Service;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalUsers = User::count();
        $activeUsers = User::where('statut', true)->count();
        $totalStrutures = Structure::count();

        return [
            Stat::make('Utilisateurs totaux', $totalUsers)
                ->description('Tous les utilisateurs')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),

            Stat::make('Utilisateurs actifs', $activeUsers)
                ->description(($totalUsers - $activeUsers) . ' inactifs')
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('success')
                ->chart([3, 3, 4, 5, 6, 7, 8, 5]),

            Stat::make('Services', $totalStrutures)
                ->description('Départements organisés')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info')
                ->chart([2, 1, 3, 2, 4, 3, 5, 4]),
        ];
    }

    protected static ?string $pollingInterval = '30s';
}
