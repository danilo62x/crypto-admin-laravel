<?php

namespace App\Filament\Pages;

use App\Filament\BaseContentPage;

class Integrations extends BaseContentPage
{
    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';
    protected static ?string $navigationGroup = 'UI';
    protected static ?string $navigationLabel = 'Integrações & Libs';
    protected static ?int $navigationSort = 44;
    protected static ?string $slug = 'integrations';
    protected static string $view = 'filament.pages.content.integrations';
}
