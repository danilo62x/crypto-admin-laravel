<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('')
            ->login(\App\Filament\Auth\Login::class)
            ->registration()
            ->passwordReset()
            ->default()
            ->brandName('CoinPanel')
            ->colors([
                'primary' => Color::hex('#465fff'),
                'gray'    => Color::hex('#667085'),
            ])
            ->font('Outfit')
            // NOTE: os overrides visuais (.fi-*) são carregados como CSS puro via
            // FilamentAsset::register() em AppServiceProvider — NÃO via ->viteTheme(),
            // porque o projeto usa Tailwind 4 e o custom theme do Filament v3 é
            // Tailwind 3 (incompatível no mesmo build Vite).
            ->favicon(asset('favicon.svg'))
            // Conteúdo em largura cheia (como o tailwind/html) — sem o max-w-7xl
            // centralizado padrão do Filament, que deixava o miolo estreito.
            ->maxContentWidth(\Filament\Support\Enums\MaxWidth::Full)
            ->navigationGroups(['Menu', 'Dados', 'Aplicações', 'UI', 'Exchange', 'Cadastros', 'Conta'])
            // Carrega no painel: app.css (utilities TW4 + tokens --tx-*), icons.js
            // (window.icon) e panel.js (UIState/tema/notify/sidebar resize/header).
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): string => '<link rel="stylesheet" href="'.\Illuminate\Support\Facades\Vite::asset('resources/css/app.css').'">'
                    .'<script src="'.asset('js/icons.js').'"></script>'
                    .'<script src="'.asset('js/panel.js?v=5').'"></script>',
            )
            // Busca: usa o GLOBAL SEARCH nativo do Filament (real, funcional — os
            // resources têm getGloballySearchableAttributes()) em vez de um input
            // decorativo. Aparece sozinho no topbar.
            // Cluster idioma/dark/notificações via USER_MENU_BEFORE (não TOPBAR_END)
            // p/ a ordem do tailwind/html:
            // idioma, dark, notificações e SÓ DEPOIS o avatar/menu do usuário.
            ->renderHook(
                \Filament\View\PanelsRenderHook::USER_MENU_BEFORE,
                fn (): string => view('filament.topbar-end')->render(),
            )
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([\App\Filament\Pages\Dashboard::class])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                \App\Filament\Widgets\CryptoStatsOverviewWidget::class,
                \App\Filament\Widgets\VolumeChartWidget::class,
                \App\Filament\Widgets\RecentTransactionsWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
