<?php

namespace App\Filament\Pages;

use App\Filament\BaseContentPage;

class Settings extends BaseContentPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Conta';
    protected static ?string $navigationLabel = 'Configurações';
    protected static ?int $navigationSort = 62;
    protected static ?string $slug = 'settings';
    protected static string $view = 'filament.pages.content.settings';
}
