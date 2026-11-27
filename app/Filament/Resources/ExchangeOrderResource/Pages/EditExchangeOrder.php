<?php

namespace App\Filament\Resources\ExchangeOrderResource\Pages;

use App\Filament\Resources\ExchangeOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExchangeOrder extends EditRecord
{
    protected static string $resource = ExchangeOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
