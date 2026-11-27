<?php

namespace App\Filament\Pages;

use App\Filament\BaseContentPage;

class Pricing extends BaseContentPage
{
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'UI';
    protected static ?string $navigationLabel = 'Planos & Preços';
    protected static ?int $navigationSort = 45;
    protected static ?string $slug = 'pricing';
    protected static string $view = 'filament.pages.content.pricing';
}
