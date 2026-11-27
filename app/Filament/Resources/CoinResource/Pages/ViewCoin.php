<?php

namespace App\Filament\Resources\CoinResource\Pages;

use App\Filament\Resources\CoinResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCoin extends ViewRecord
{
    protected static string $resource = CoinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
