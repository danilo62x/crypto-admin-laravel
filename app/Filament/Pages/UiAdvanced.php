<?php

namespace App\Filament\Pages;

use App\Filament\BaseContentPage;

class UiAdvanced extends BaseContentPage
{
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationGroup = 'UI';
    protected static ?string $navigationLabel = 'Componentes avançados';
    protected static ?int $navigationSort = 42;
    protected static ?string $slug = 'ui-advanced';
    protected static string $view = 'filament.pages.content.ui-advanced';
}
