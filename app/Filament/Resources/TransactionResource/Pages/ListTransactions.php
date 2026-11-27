<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Models\Transaction;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }

    public function getTabs(): array
    {
        $countFor = fn (?string $status): int => $status
            ? Transaction::where('status', $status)->count()
            : Transaction::count();

        return [
            'all'       => Tab::make('Todas')->badge($countFor(null)),
            'pendente'  => Tab::make('Pendentes')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pendente'))
                ->badge($countFor('pendente'))->badgeColor('warning'),
            'concluida' => Tab::make('Concluídas')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'concluida'))
                ->badge($countFor('concluida'))->badgeColor('success'),
            'falha'     => Tab::make('Falhas')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'falha'))
                ->badge($countFor('falha'))->badgeColor('danger'),
        ];
    }
}
