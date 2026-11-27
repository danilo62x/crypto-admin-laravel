<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Todos')
                ->badge(User::count()),
            'active' => Tab::make('Ativos')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(User::where('is_active', true)->count())
                ->badgeColor('success'),
            'inactive' => Tab::make('Inativos')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge(User::where('is_active', false)->count())
                ->badgeColor('danger'),
        ];
    }
}
