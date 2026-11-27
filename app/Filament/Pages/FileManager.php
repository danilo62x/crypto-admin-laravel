<?php

namespace App\Filament\Pages;

use App\Filament\BaseContentPage;

class FileManager extends BaseContentPage
{
    protected static ?string $navigationIcon = 'heroicon-o-folder';
    protected static ?string $navigationGroup = 'Aplicações';
    protected static ?string $navigationLabel = 'Arquivos';
    protected static ?int $navigationSort = 32;
    protected static ?string $slug = 'file-manager';
    protected static string $view = 'filament.pages.content.file-manager';
}
