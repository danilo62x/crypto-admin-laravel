<?php

namespace App\Filament\Pages;

use App\Filament\BaseContentPage;

class Profile extends BaseContentPage
{
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationGroup = 'Conta';
    protected static ?string $navigationLabel = 'Perfil';
    protected static ?int $navigationSort = 61;
    protected static ?string $slug = 'profile';
    protected static string $view = 'filament.pages.content.profile';
}
