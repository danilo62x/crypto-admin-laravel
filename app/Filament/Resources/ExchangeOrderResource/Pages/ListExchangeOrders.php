<?php

namespace App\Filament\Resources\ExchangeOrderResource\Pages;

use App\Filament\Resources\ExchangeOrderResource;
use App\Models\ExchangeOrder;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListExchangeOrders extends ListRecords
{
    protected static string $resource = ExchangeOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }

    public function getTabs(): array
    {
        $countFor = fn (?string $status): int => $status
            ? ExchangeOrder::where('status', $status)->count()
            : ExchangeOrder::count();

        return [
            'all'       => Tab::make('Todas')->badge($countFor(null)),
            'pendente'  => Tab::make('Pendentes')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pendente'))
                ->badge($countFor('pendente'))->badgeColor('warning'),
            'executada' => Tab::make('Executadas')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'executada'))
                ->badge($countFor('executada'))->badgeColor('success'),
            'cancelada' => Tab::make('Canceladas')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'cancelada'))
                ->badge($countFor('cancelada'))->badgeColor('danger'),
        ];
    }
}
