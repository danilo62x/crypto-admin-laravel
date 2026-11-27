<?php

namespace App\Filament\Resources\ExchangeOrderResource\Pages;

use App\Filament\Resources\ExchangeOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewExchangeOrder extends ViewRecord
{
    protected static string $resource = ExchangeOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
