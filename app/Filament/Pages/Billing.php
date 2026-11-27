<?php

namespace App\Filament\Pages;

use App\Filament\BaseContentPage;

class Billing extends BaseContentPage
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Aplicações';
    protected static ?string $navigationLabel = 'Cobrança';
    protected static ?int $navigationSort = 35;
    protected static ?string $slug = 'billing';
    protected static string $view = 'filament.pages.content.billing';
}
