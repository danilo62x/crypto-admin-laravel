<?php

namespace App\Filament\Pages;

use App\Filament\BaseContentPage;

class Invoice extends BaseContentPage
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Aplicações';
    protected static ?string $navigationLabel = 'Faturas';
    protected static ?int $navigationSort = 34;
    protected static ?string $slug = 'invoice';
    protected static string $view = 'filament.pages.content.invoice';
}
