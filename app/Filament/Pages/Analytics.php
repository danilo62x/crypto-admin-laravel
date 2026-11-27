<?php

namespace App\Filament\Pages;

use App\Filament\BaseContentPage;

class Analytics extends BaseContentPage
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Menu';
    protected static ?string $navigationLabel = 'Analytics';
    protected static ?int $navigationSort = 12;
    protected static ?string $slug = 'analytics';
    protected static string $view = 'filament.pages.content.analytics';
}
