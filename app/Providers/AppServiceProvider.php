<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Bind <x-icon> ao componente anônimo de SVG inline. O conteúdo portado
        // do tailwind/html (resources/views/filament/pages/content/*) usa <x-icon>.
        Blade::component('components.icon', 'icon');

        // Painel Filament: carrega os overrides do tema (.fi-*) como CSS puro.
        // Publicado por `php artisan filament:assets` em public/css/ e injetado
        // em todas as páginas do painel. (app.css + icons.js entram via render
        // hook no AdminPanelProvider.)
        FilamentAsset::register([
            Css::make('tx-admin-theme', resource_path('css/filament/admin/overrides.css')),
        ]);
    }
}
