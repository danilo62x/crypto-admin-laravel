<?php

namespace App\Filament\Resources\WalletResource\Pages;

use App\Filament\Resources\WalletResource;
use App\Models\Wallet;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListWallets extends ListRecords
{
    protected static string $resource = WalletResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }

    public function getTabs(): array
    {
        $countFor = fn (?string $symbol): int => $symbol
            ? Wallet::whereHas('coin', fn (Builder $q) => $q->where('symbol', $symbol))->count()
            : Wallet::count();

        return [
            'all'  => Tab::make('Todas')->badge($countFor(null)),
            'btc'  => Tab::make('BTC')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('coin', fn (Builder $q) => $q->where('symbol', 'BTC')))
                ->badge($countFor('BTC'))->badgeColor('warning'),
            'eth'  => Tab::make('ETH')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('coin', fn (Builder $q) => $q->where('symbol', 'ETH')))
                ->badge($countFor('ETH'))->badgeColor('info'),
            'usdt' => Tab::make('USDT')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('coin', fn (Builder $q) => $q->where('symbol', 'USDT')))
                ->badge($countFor('USDT'))->badgeColor('success'),
        ];
    }
}
