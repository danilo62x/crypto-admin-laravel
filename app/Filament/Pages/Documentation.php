<?php

namespace App\Filament\Pages;

use App\Filament\BaseContentPage;

class Documentation extends BaseContentPage
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Aplicações';
    protected static ?string $navigationLabel = 'Documentação';
    protected static ?int $navigationSort = 36;
    protected static ?string $slug = 'documentation';
    protected static string $view = 'filament.pages.content.documentation';
}
