<?php

namespace App\Filament\Pages;

use App\Filament\BaseContentPage;

class Forms extends BaseContentPage
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Dados';
    protected static ?string $navigationLabel = 'Formulários';
    protected static ?int $navigationSort = 21;
    protected static ?string $slug = 'forms';
    protected static string $view = 'filament.pages.content.forms';
}
