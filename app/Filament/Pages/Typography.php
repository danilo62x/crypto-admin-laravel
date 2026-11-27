<?php

namespace App\Filament\Pages;

use App\Filament\BaseContentPage;

class Typography extends BaseContentPage
{
    protected static ?string $navigationIcon = 'heroicon-o-language';
    protected static ?string $navigationGroup = 'UI';
    protected static ?string $navigationLabel = 'Tipografia';
    protected static ?int $navigationSort = 33;
    protected static ?string $slug = 'typography';
    protected static string $view = 'filament.pages.content.typography';
}
