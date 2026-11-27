<?php

namespace App\Filament\Pages;

use App\Filament\BaseContentPage;

class Gallery extends BaseContentPage
{
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationGroup = 'Aplicações';
    protected static ?string $navigationLabel = 'Galeria';
    protected static ?int $navigationSort = 33;
    protected static ?string $slug = 'gallery';
    protected static string $view = 'filament.pages.content.gallery';
}
