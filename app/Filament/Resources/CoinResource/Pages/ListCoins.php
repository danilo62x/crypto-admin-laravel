<?php

namespace App\Filament\Resources\CoinResource\Pages;

use App\Filament\Resources\CoinResource;
use App\Models\Coin;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCoins extends ListRecords
{
    protected static string $resource = CoinResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Todas')
                ->badge(Coin::count()),
            'active' => Tab::make('Ativas')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(Coin::where('is_active', true)->count())
                ->badgeColor('success'),
            'inactive' => Tab::make('Suspensas')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge(Coin::where('is_active', false)->count())
                ->badgeColor('danger'),
        ];
    }
}
