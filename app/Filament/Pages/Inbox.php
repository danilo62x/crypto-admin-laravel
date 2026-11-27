<?php

namespace App\Filament\Pages;

use App\Filament\BaseContentPage;

class Inbox extends BaseContentPage
{
    protected static ?string $navigationIcon = 'heroicon-o-inbox';
    protected static ?string $navigationGroup = 'Aplicações';
    protected static ?string $navigationLabel = 'Caixa de entrada';
    protected static ?int $navigationSort = 31;
    protected static ?string $slug = 'inbox';
    protected static string $view = 'filament.pages.content.inbox';
}
