<?php

namespace App\Filament\Widgets;

use App\Models\Coin;
use App\Models\ExchangeOrder;
use App\Models\Transaction;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CryptoStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $volume24h = (float) Transaction::query()
            ->where('status', 'concluida')
            ->where('created_at', '>=', now()->subDay())
            ->sum('amount');

        $pendingWithdrawals = Transaction::query()
            ->where('type', 'saque')
            ->where('status', 'pendente')
            ->count();

        $pendingKyc = User::query()
            ->where('kyc_status', 'pendente')
            ->count();

        $activeCoins = Coin::query()
            ->where('is_active', true)
            ->count();

        return [
            Stat::make('Volume 24h', number_format($volume24h, 8))
                ->description('Transações concluídas')
                ->descriptionIcon('heroicon-m-arrows-right-left')
                ->color('success'),
            Stat::make('Moedas ativas', (string) $activeCoins)
                ->description('Disponíveis para trade')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),
            Stat::make('Saques pendentes', (string) $pendingWithdrawals)
                ->description('Aguardando aprovação')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingWithdrawals > 0 ? 'warning' : 'gray'),
            Stat::make('KYC pendente', (string) $pendingKyc)
                ->description('Usuários aguardando verificação')
                ->descriptionIcon('heroicon-m-identification')
                ->color($pendingKyc > 0 ? 'danger' : 'gray'),
        ];
    }
}
