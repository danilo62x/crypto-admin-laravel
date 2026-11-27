<?php

namespace App\Filament\Pages;

use App\Filament\BaseContentPage;

class Components extends BaseContentPage
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = 'UI';
    protected static ?string $navigationLabel = 'Componentes';
    protected static ?int $navigationSort = 41;
    protected static ?string $slug = 'components';
    protected static string $view = 'filament.pages.content.components';
}
