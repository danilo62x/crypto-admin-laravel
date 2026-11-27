<?php

namespace App\Filament\Pages;

use App\Filament\BaseContentPage;

class Tables extends BaseContentPage
{
    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static ?string $navigationGroup = 'Dados';
    protected static ?string $navigationLabel = 'Tabelas';
    protected static ?int $navigationSort = 22;
    protected static ?string $slug = 'tables';
    protected static string $view = 'filament.pages.content.tables';
}
