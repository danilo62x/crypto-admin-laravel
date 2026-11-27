<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CryptoStatsOverviewWidget;
use App\Filament\Widgets\RecentTransactionsWidget;
use App\Filament\Widgets\VolumeChartWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Enums\MaxWidth;
use Filament\Widgets\AccountWidget;

/**
 * Dashboard com largura própria. O painel usa maxContentWidth(Full) para as
 * páginas de conteúdo (tabelas/gráficos ocuparem a largura), mas o grid de
 * widgets do dashboard fica com colunas quebradas em telas largas no modo Full.
 * Aqui mantemos o dashboard num container centralizado, como era antes.
 */
class Dashboard extends BaseDashboard
{
    public function getMaxContentWidth(): MaxWidth | string | null
    {
        return MaxWidth::SevenExtraLarge;
    }

    /**
     * Lista explícita de widgets do dashboard. Evita que widgets auto-descobertos
     * de uso específico apareçam aqui duplicando métricas.
     */
    public function getWidgets(): array
    {
        return [
            AccountWidget::class,
            CryptoStatsOverviewWidget::class,
            VolumeChartWidget::class,
            RecentTransactionsWidget::class,
        ];
    }
}
